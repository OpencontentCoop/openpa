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

    private $strategy;

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

    private $columnField;

    private $facetsSearchResults;

    private $allSearchResults;

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

        $this->parseRequest();
    }

    private function parseRequest()
    {
        $parts = explode('|', $this->parameters);
        $this->class = $this->classRepository->load($parts[0]);

        $this->rowField = isset( $parts[1] ) ? $this->getField(trim($parts[1])) : array();
        $this->columnField = isset( $parts[2] ) ? $this->getField(trim($parts[2])) : array();

        if (!in_array($this->columnField['dataType'], array(eZIntegerType::DATA_TYPE_STRING, eZStringType::DATA_TYPE_STRING))){
            throw new Exception("Colonna {$this->columnField['identifier']}: tipo {$this->columnField['dataType']} non gestito");
        }

        $this->headers = array('', $this->columnField['name'][$this->language]);

        $this->strategy = isset( $parts[3] ) ? trim($parts[3]) : null;

        $queryParts = array();
        $queryParts[] = "subtree [" . implode(',', $this->subtree) . "]";
        $queryParts[] = "classes [" . $this->class->identifier . "]";
        $this->baseQuery = implode(' and ', $queryParts);
    }

    private function getField($identifier)
    {
        foreach ($this->class->fields as $field) {
            if ($field['identifier'] == $identifier){
                return $field;
            }
        }
        throw new Exception("Attribute $identifier non trovato");
    }

    private function getRows()
    {
        if ($this->rows === null) {
            if ($this->rowField['dataType'] == eZObjectRelationListType::DATA_TYPE_STRING) {
                $this->rows = $this->getFacetedRows($this->rowField['identifier'], $this->rowField['dataType']);
            } else {
                $this->rows = $this->getAllRows($this->rowField['identifier'], $this->rowField['dataType']);
            }
        }
        return $this->rows;
    }

    private function getAllRows($identifier, $datatype)
    {
        $data = array();
        foreach($this->getAllSearchResults() as $result){
            $converter = AttributeConverterLoader::load(
                $this->class->identifier,
                $identifier,
                $datatype
            );
            $data[] = isset($result['data'][$this->language][$identifier]) ? $converter->toCSVString($result['data'][$this->language][$identifier]) : '';
        }
        return $data;
    }

    private function getFacetedRows($identifier, $datatype)
    {
        $data = array();
        if ($this->rowField['dataType'] == eZObjectRelationListType::DATA_TYPE_STRING){
            $facets = "$identifier.id|count|" . self::FACETS_LIMIT;;
        }else{
            $facets = "$identifier|count|" . self::FACETS_LIMIT;
        }
        $query = $this->baseQuery . ' limit 1 facets [' . $facets . ']';
        $this->facetsSearchResults = $this->search($query);
        foreach($this->facetsSearchResults->facets as $facet){
            $data = $this->getNameFromId(array_keys($facet['data']));
            //$data = array_keys($facet['data']);
        }
        if ($this->strategy == 'total')
            $data[] = 'Totale';
        return $data;
    }

    private function getColumns()
    {
        if ($this->columns === null) {
            if ($this->rowField['dataType'] == eZObjectRelationListType::DATA_TYPE_STRING) {
                $this->columns = $this->getFacetedColumns($this->columnField['identifier'], $this->columnField['dataType']);
            } else {
                $this->columns = $this->getAllColumns($this->columnField['identifier'], $this->columnField['dataType']);
            }
        }

        return $this->columns;
    }

    private function getAllColumns($identifier, $datatype)
    {
        $data = array();
        foreach($this->getAllSearchResults() as $result){
            $converter = AttributeConverterLoader::load(
                $this->class->identifier,
                $identifier,
                $datatype
            );
            $data[] = isset($result['data'][$this->language][$identifier]) ? $converter->toCSVString($result['data'][$this->language][$identifier]) : 0;
        }
        return $data;
    }

    private function getFacetedColumns($identifier, $datatype)
    {
        $this->getRows();
        $data = array();

        foreach($this->facetsSearchResults->facets as $facet){
            foreach(array_keys($facet['data']) as $value){
                $filter = "{$facet['name']} in ['{$value}']";
                $data[] = $this->getFacetedColumnFiltered($filter, $identifier, $datatype);
            }
        }

        $facets = "$identifier|count|" . self::FACETS_LIMIT;
        $query = "$this->baseQuery limit 1 facets [$facets]";
        $facetsSearchResults = $this->search($query);
        $total = 0;
        foreach($facetsSearchResults->facets as $facet) {
            foreach($facet['data'] as $key => $value){
                if ($datatype == eZStringType::DATA_TYPE_STRING){
                    $key = floatval($value);
                }
                $total += $key * $value;
            }
        }
        if ($this->strategy == 'total')
            $data[] = $total;

        if ($this->strategy == 'avg'){
            $avgData = array_map(function($n) use($total){
                return number_format($n * 100 / $total, 2);
            }, $data);

            $data = $avgData;
        }

        return $data;
    }

    private function getFacetedColumnFiltered($filter, $identifier, $datatype)
    {
        $facets = "$identifier|count|" . self::FACETS_LIMIT;
        $query = "$filter and $this->baseQuery limit 1 facets [$facets]";
        $facetsSearchResults = $this->search($query);
        $data = 0;
        foreach($facetsSearchResults->facets as $facet) {
            foreach($facet['data'] as $key => $value){
                if ($datatype == eZStringType::DATA_TYPE_STRING){
                    $key = floatval($value);
                }
                $data += $key * $value;
            }
        }
        return $data;
    }

    private function getAllSearchResults()
    {
        if ($this->allSearchResults === null){
            $query = $this->baseQuery;
            $this->allSearchResults = $this->searchAll($query);
        }

        return $this->allSearchResults;
    }

    private function getNameFromId(array $idList)
    {
        $data = array();
        $hits = $this->searchAll("id in [". implode(',', $idList) . "]");
        foreach($hits as $hit){
            $data[$hit['metadata']['id']] = trim($hit['metadata']['name'][$this->language]);
        }
        $result = array();
        foreach($idList as $id){
            $result[] = isset($data[$id]) ? $data[$id] : '?';
        }
        return $result;
    }

    public function getData()
    {

        if (isset($_GET['debug'])) {
            echo '<pre>';
            print_r(
                array_combine($this->getRows(), $this->getColumns())
            );
            echo '</pre>';
            eZDisplayDebug();
            eZExecution::cleanExit();
        }

        $data = array();
        $rows = $this->getRows();
        $columns = $this->getColumns();
        for ($i=0; $i<count($rows); $i++) {
            $data[] = array(
                $rows[$i],
                $columns[$i]
            );
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
        eZExecution::cleanExit();
    }

    private function searchAll($query, array $limitation = null)
    {
        $hits = array();
        $query .= ' and limit ' . $this->currentEnvironment->getMaxSearchLimit();
        while ($query) {
            $results = $this->search($query, $limitation);
            $hits = array_merge($hits, $results->searchHits);
            $query = $results->nextPageQuery;
        }

        return $hits;
    }

    private function search($query, array $limitation = null)
    {
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

    private function fputcsv(&$handle, $fields = array(), $delimiter = ',', $enclosure = '"', $last = false)
    {
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
}
