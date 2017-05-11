<?php

use Opencontent\Opendata\Api\ContentSearch;
use Opencontent\Opendata\Api\ClassRepository;
use Opencontent\Opendata\Api\EnvironmentSettings;
use Opencontent\Opendata\Api\Values\SearchResults;
use Opencontent\Opendata\Api\Values\ContentClass;
use Opencontent\Opendata\Api\AttributeConverterLoader;

class DataHandlerChart implements OpenPADataHandlerInterface
{
    const FACETS_LIMIT = 1000;

    private $subtree;

    private $parameters;

    /**
     * @var ContentClass
     */
    private $class;

    private $options = array();

    private $baseQuery;

    /**
     * @var ContentSearch
     */
    private $contentSearch;

    /**
     * @var EnvironmentSettings
     */
    private $currentEnvironment;

    /**
     * @var ClassRepository
     */
    protected $classRepository;

    private $headers;

    private $rows;

    private $columns;

    private $rowField;

    private $columnFields = array();

    private $facetsSearchResults;

    private $allSearchResults;
    private $allSearchCount;

    private $language = 'ita-IT';

    public function __construct(array $Params)
    {
        $this->currentEnvironment = new DefaultEnvironmentSettings();
        $this->contentSearch = new ContentSearch();
        $this->contentSearch->setEnvironment($this->currentEnvironment);
        $this->classRepository = new ClassRepository();

        $this->subtree = (array)eZHTTPTool::instance()->getVariable('subtree',
            eZINI::instance('content.ini')->variable('NodeSettings', 'RootNode'));
        $this->parameters = eZHTTPTool::instance()->getVariable('params', '');
    }

    public function getData()
    {
        if (isset( $_GET['debug'] )) {
            $this->displayDebug();
        }else{
            try {
                $this->parseRequest();
                $data = array();
                $rows = $this->getRows();
                $columns = $this->getColumns();
                for ($i = 0; $i < count($rows); $i++) {
                    $item = array($rows[$i]);
                    foreach($columns as $column){
                        $item[] = $column[$i];
                    }
                    $data[] = $item;
                }

                $delimiter = ',';
                $enclosure = '"';

                header('Content-Type: text/plain; charset=utf-8');
                $output = fopen('php://output', 'w');

                if (!empty( $this->headers )) {
                    $this->fputcsv($output, $this->headers, $delimiter, $enclosure);
                }
                $countBaseZero = count($data) - 1;
                foreach ($data as $index => $row) {
                    $this->fputcsv($output, $row, $delimiter, $enclosure, $index == $countBaseZero);
                }
            }catch(Exception $e){
                echo $e->getMessage();
            }
        }
        eZExecution::cleanExit();
    }

    public function displayDebug()
    {
        echo '<pre>';
        try {
            $this->parseRequest();

            $rows = $this->getRows();
            $columns = $this->getColumns();

            echo '<table>';
            if (!empty( $this->headers )) {
                echo '<tr>';
                foreach($this->headers as $header) {
                    echo '<td>' . $header . '</td>';
                }
                echo '</tr>';
            }
            foreach($rows as $index => $row){
                echo '<tr>';
                echo '<td>' . $row . '</td>';
                foreach($columns as $column){
                    echo '<td>' . $column[$index] . '</td>';
                }
                echo '</tr>';
            }
            echo '</table>';
        }catch(Exception $e){
            echo '<h1>'.$e->getMessage().'</h1>';
            print_r($e);
        }
        echo '</pre>';
        eZDisplayDebug();
    }

    private function parseRequest()
    {
        $parts = $this->explode('|', $this->parameters);
        $this->class = $this->classRepository->load($parts[0]);

        $this->rowField = isset( $parts[1] ) ? $this->getField(trim($parts[1])) : null;
        if (!$this->rowField) {
            throw new Exception("Attributo per la riga non specificato");
        }

        $columnFields = isset( $parts[2] ) ? $this->explode(',', trim($parts[2])) : null;
        if (!$columnFields) {
            throw new Exception("Attributo per la colonna non specificato");
        }

        $this->headers = array('');

        foreach ($columnFields as $field) {
            $columnField = $this->getField($field);
            if (!in_array($columnField['dataType'],
                array(eZIntegerType::DATA_TYPE_STRING, eZStringType::DATA_TYPE_STRING))
            ) {
                throw new Exception("Colonna {$columnField['identifier']}: tipo {$columnField['dataType']} non gestito");
            }

            $this->headers[] = $columnField['expose_as'];

            $this->columnFields[] = $columnField;
        }

        $this->options = isset( $parts[3] ) ? $this->explode(',', $parts[3]) : array();

        $queryParts = array();
        $queryParts[] = "subtree [" . implode(',', $this->subtree) . "]";
        $queryParts[] = "classes [" . $this->class->identifier . "]";
        $queryParts[] = "sort [name => asc]";
        $this->baseQuery = implode(' and ', $queryParts);
    }

    private function getField($field)
    {
        $parts = $this->explode('-', $field);
        $identifierAndName = $this->explode(':', $parts[0]);
        $identifier = $identifierAndName[0];
        $name = isset($identifierAndName[1]) ? $identifierAndName[1] : null;
        $options = isset($parts[1]) ? $this->explode(':', $parts[1]) : array();
        foreach ($this->class->fields as $field) {
            if ($field['identifier'] == $identifier) {

                $field['options'] = $options;

                $field['expose_as'] = $name ? $name : $field['name'][$this->language];

                if ($field['dataType'] == eZStringType::DATA_TYPE_STRING) {
                    $field['query_field'] = "raw[" . OpenPASolr::generateSolrField($identifier, 'string') . "]";
                } else {
                    $field['query_field'] = $identifier;
                }

                $field['is_facet'] = false;
                if ($field['dataType'] == eZObjectRelationListType::DATA_TYPE_STRING) {
                    $field['is_facet'] = true;
                    $field['query_facet'] = $field['query_field'] . ".id|alpha|" . self::FACETS_LIMIT;

                } elseif ($field['dataType'] == eZStringType::DATA_TYPE_STRING) {
                    $field['query_facet'] = "raw[" . OpenPASolr::generateSolrField($identifier,
                            'string') . "]|alpha|" . self::FACETS_LIMIT;
                } else {
                    $field['query_facet'] = $field['query_field'] . "|alpha|" . self::FACETS_LIMIT;
                }

                $field['converter'] = AttributeConverterLoader::load(
                    $this->class->identifier,
                    $field['identifier'],
                    $field['dataType']
                );

                return $field;
            }
        }
        throw new Exception("Attribute $identifier non trovato");
    }

    private function getRows()
    {
        if ($this->rows === null) {
            if ($this->rowField['is_facet']) {
                $this->rows = $this->getFacetedRows();
            } else {
                $this->rows = $this->getAllRows();
            }

            if ($this->hasOption('total')) {
                $this->rows[] = 'Totale';
            }
        }

        return $this->rows;
    }

    private function getAllRows()
    {
        $identifier = $this->rowField['identifier'];
        /** @var \Opencontent\Opendata\Api\AttributeConverter\Base $converter */
        $converter = $this->rowField['converter'];

        $data = array();
        foreach ($this->getAllSearchResults() as $result) {
            $data[] = isset( $result['data'][$this->language][$identifier] ) ?
                $this->toString(
                    $converter->toCSVString($result['data'][$this->language][$identifier]),
                    $this->rowField
                ) : '';
        }

        return $data;
    }

    private function getFacetedRows()
    {
        $data = array();
        $query = $this->baseQuery . ' limit 1 facets [' . $this->rowField['query_facet'] . ']';
        $this->facetsSearchResults = $this->search($query);
        foreach ($this->facetsSearchResults->facets as $facet) {
            $data = array_keys($facet['data']);
            $areIntegers = array_reduce($data, function ($carry, $item) {
                if ($carry === null || $carry) {
                    return is_numeric($item);
                }

                return $carry;
            });
            if ($areIntegers) {
                $data = $this->getNameFromId(array_keys($facet['data']));
            }else{
                // toString su data;
            }
        }

        return $data;
    }

    private function getColumns()
    {
        if ($this->columns === null) {
            $this->columns = array();
            foreach($this->columnFields as $columnField) {
                if ($this->rowField['dataType'] == eZObjectRelationListType::DATA_TYPE_STRING) {
                    $this->columns[] = $this->getFacetedColumns($columnField);
                } else {
                    $this->columns[] = $this->getAllColumns($columnField);
                }
            }
        }

        return $this->columns;
    }

    private function getAllColumns($columnField)
    {
        $identifier = $columnField['identifier'];
        /** @var \Opencontent\Opendata\Api\AttributeConverter\Base $converter */
        $converter = $columnField['converter'];
        $data = array();
        foreach ($this->getAllSearchResults() as $result) {
            $data[] = isset( $result['data'][$this->language][$identifier] ) ?
                $this->toNumber(
                    $converter->toCSVString($result['data'][$this->language][$identifier]),
                    $columnField)
                : 0;
        }

        if ($this->hasOption('total')) {
            $count = $this->getAllSearchCount();
            if ($this->hasOption('avg', $columnField)) {
                $data[] = number_format(array_sum($data) / $count, 2);
            }else{
                $data[] = $count;
            }
        }

        return $data;
    }

    private function getFacetedColumns($columnField)
    {
        $this->getRows();
        $data = array();

        foreach ($this->facetsSearchResults->facets as $facet) {
            foreach (array_keys($facet['data']) as $value) {
                $filter = "{$facet['name']} in ['{$value}']";
                $data[] = $this->getFacetedColumnFiltered($filter, $columnField);
            }
        }

        $query = "$this->baseQuery limit 1 facets [{$columnField['query_facet']}]";
        $facetsSearchResults = $this->search($query);
        $total = 0;
        $count = 1;
        foreach ($facetsSearchResults->facets as $facet) {
            $count = array_sum($facet['data']);
            foreach ($facet['data'] as $key => $value) {
                $key = $this->toNumber($key, $columnField);
                $total += $key * $value;
            }
        }
        if ($this->hasOption('avg', $columnField)) {
            $total = number_format($total / $count, 2);;
        }
        if ($this->hasOption('total')) {
            $data[] = $total;
        }

        return $data;
    }

    private function getFacetedColumnFiltered($filter, $columnField)
    {
        $query = "$filter and $this->baseQuery limit 1 facets [{$columnField['query_facet']}]";
        $facetsSearchResults = $this->search($query);
        $data = 0;
        $count = 1;
        foreach ($facetsSearchResults->facets as $facet) {
            $count = array_sum($facet['data']);
            foreach ($facet['data'] as $key => $value) {
                $key = $this->toNumber($key, $columnField);
                $data += $key * $value;
            }
        }
        if ($this->hasOption('avg', $columnField)) {
            $data = number_format($data / $count, 2);
        }

        return $data;
    }

    private function getAllSearchResults()
    {
        if ($this->allSearchResults === null) {
            $query = $this->baseQuery;
            $this->allSearchResults = $this->searchAll($query);
        }

        return $this->allSearchResults;
    }

    private function getAllSearchCount()
    {
        if ($this->allSearchCount === null) {
            $query = $this->baseQuery;
            $this->allSearchCount = $this->count($query);
        }

        return $this->allSearchCount;
    }

    private function getNameFromId(array $idList)
    {
        $data = array();
        $hits = $this->searchAll("id in [" . implode(',', $idList) . "]");
        foreach ($hits as $hit) {
            $data[$hit['metadata']['id']] = trim($hit['metadata']['name'][$this->language]);
        }
        $result = array();
        foreach ($idList as $id) {
            $result[] = isset( $data[$id] ) ? $data[$id] : '?';
        }

        return $result;
    }

    private function toNumber($value, $field)
    {
        $converted = $value;
        if ($field['dataType'] == eZStringType::DATA_TYPE_STRING) {
            if ($this->hasOption('force', $field)) {
                $converted = number_format(floatval($value), 2);
            }else{
                $converted = 1;
            }
        }
        return $converted;
    }

    private function toString($value, $field)
    {
        return trim($value);
    }

    private function hasOption($value, $field = null)
    {
        $haystack = $field ? $field['options'] : $this->options;
        return in_array($value, $haystack);
    }

    private function searchAll($query, array $limitation = null) {
        $hits = array();
        $query .= ' and limit ' . $this->currentEnvironment->getMaxSearchLimit();
        while ($query) {
            $results = $this->search($query, $limitation);
            $hits = array_merge($hits, $results->searchHits);
            $query = $results->nextPageQuery;
        }

        return $hits;
    }

    private function count($query, array $limitation = null) {
        $query .= ' and limit 1';
        $results = $this->search($query, $limitation);

        return $results->totalCount;
    }

    private function search($query, array $limitation = null) {
        try {
            eZDebug::writeDebug($query, __METHOD__);

            return $this->contentSearch->search($query, $limitation);
        } catch (Exception $e) {
            eZDebug::writeError($e->getMessage() . "\n" . $e->getTraceAsString(), __METHOD__);
            $error = new SearchResults();
            $error->nextPageQuery = null;
            $error->searchHits = array();
            $error->totalCount = 0;
            $error->facets = array();
            $error->query = $query;

            return $error;
        }
    }

    private function fputcsv(
        &$handle,
        $fields = array(),
        $delimiter = ',',
        $enclosure = '"',
        $last = false
    ) {
        $str = '';
        $escape_char = '\\';
        foreach ($fields as $value) {
            if (strpos($value, $delimiter) !== false
                || strpos($value, $enclosure) !== false
                || strpos($value, "\n") !== false
                || strpos($value, "\r") !== false
                || strpos($value, "\t") !== false
                || strpos($value, ' ') !== false
            ) {
                $str2 = $enclosure;
                $escaped = 0;
                $len = strlen($value);
                for ($i = 0; $i < $len; $i++) {
                    if ($value[$i] == $escape_char) {
                        $escaped = 1;
                    } else if (!$escaped && $value[$i] == $enclosure) {
                        $str2 .= $enclosure;
                    } else {
                        $escaped = 0;
                    }
                    $str2 .= $value[$i];
                }
                $str2 .= $enclosure;
                $str .= $str2 . $delimiter;
            } else {
                $str .= $value . $delimiter;
            }
        }
        $str = substr($str, 0, -1);
        if (!$last) {
            $str .= "\n"; // questo è il motivo per cui non ho usato la funzione originale
        }

        return fwrite($handle, $str);
    }

    private function explode($delimiter, $string)
    {
        $data = explode($delimiter, $string);
        $data = array_map('trim', $data);
        return $data;
    }
}
