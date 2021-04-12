<?php

class OpenPAAttributeContactsHandler extends OpenPAAttributeHandler
{
    public function __construct(eZContentObjectAttribute $attribute, $params = array())
    {
        parent::__construct($attribute, $params);
        $this->data['data'] = $this->getContactsData($attribute);
    }

    public static function getContactsFields()
    {
        return array(
            "Telefono",
            "Fax",
            "Email",
            "PEC",
            "Indirizzo",
            "Facebook",
            "Twitter",
            "Web",
            "Codice fiscale",
            "Partita IVA",
            "Codice iPA",
            "Via",
            "Numero Civico",
            "CAP",
            "Comune",
            "Latitudine",
            "Longitudine",
            "Linkedin",
            "Instagram",
            "Newsletter",
            "Youtube",
            "WhatsApp",
            "Telegram",
            "Codice SDI",
            "TikTok",
            "Link area personale",
        );
    }

    public static function getContactsData(eZContentObjectAttribute $attribute)
    {
        $data = array();
        $currentErrorReporting = error_reporting();
        error_reporting( 0 );
        $trans = eZCharTransform::instance();
        if ($attribute->attribute('has_content') && $attribute->attribute('data_type_string') == eZMatrixType::DATA_TYPE_STRING) {
            $matrix = $attribute->attribute('content')->attribute('matrix');
            foreach ($matrix['rows']['sequential'] as $row) {
                $columns = $row['columns'];
                $name = $columns[0];
                $identifier = $trans->transformByGroup($name, 'identifier');
                if (!empty($columns[1])) {
                    $data[$identifier] = $columns[1];
                }
            }
        }
        error_reporting( $currentErrorReporting );
        return $data;
    }

    public static function fillContactsData(eZContentObjectAttribute $attribute, array $fields)
    {
        $existingFields = array();
        if ($attribute instanceof eZContentObjectAttribute && $attribute->attribute('data_type_string') == eZMatrixType::DATA_TYPE_STRING) {
            $matrix = $attribute->attribute('content');
            if ($attribute->hasContent()) {
                $rows = $matrix->attribute('rows');
                foreach ($rows['sequential'] as $row) {
                    if (in_array($row['columns'][0], $fields)) {
                        $existingFields[] = $row['columns'][0];
                    }
                }
            }
            foreach ($fields as $field) {
                if (!in_array($field, $existingFields)) {
                    $matrix->addRow();
                }
            }
            if ($matrix->NumRows != count($fields)){
                $matrix->NumRows = count($fields);
            }
            $attribute->setAttribute('data_text', $matrix->xmlString());
            $matrix->decodeXML($attribute->attribute('data_text'));
            $attribute->setContent($matrix);
            $attribute->store();
        }

        return $attribute;
    }
}
