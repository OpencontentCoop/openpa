<?php
require 'autoload.php';

$script = eZScript::instance(array(
    'description' => ( "OpenPA Controllo Ckan tools\n\n" ),
    'use-session' => false,
    'use-modules' => true,
    'use-extensions' => true
));

$script->startup();

$options = $script->getOptions(
    '[dry-run][remove_old_dataset][fix_area_remote_ids][add_class_descriptions][fix_footer_link_remote_id][areatematica_sync][check_org][parse_indicepa][find_codiceipa][generate_from_classes]',
    '',
    array(
        'dry-run' => 'Non esegue azioni e mostra eventuali errori'
    )
);
$script->initialize();
$script->setUseDebugAccumulators(true);

OpenPALog::setOutputLevel($script->isQuiet() ? OpenPALog::ERROR : OpenPALog::ALL);

$user = eZUser::fetchByName( 'admin' );
eZUser::setCurrentlyLoggedInUser( $user , $user->attribute( 'contentobject_id' ) );

$db = eZDB::instance();

try {

    $footerRemoteId = 'opendata_footer_link';

    if ($options['generate_from_classes']){

        $count = 0;

        $classes = array(
            'accordo',
            'convenzione',
            'concessioni',
            'event',
            'procedimento',
            'tasso_assenza',
            'area_tematica',
            'associazione',
            'ente',
            'ente_controllato',
            'interpellanza',
            'interrogazione',
            'mozione',
            'sovvenzione_contributo',
            'seduta_consiglio',
            'rapporto',
            'albo_elenco',
            'avviso',
            'bando',
            'bilancio_di_previsione',
            'concorso',
            'conferimento_incarico',
            'consulenza',
            'decreto_sindacale',
            'dipendente',
            'disciplinare',
            'documento',
            'gruppo_consiliare',
            'modulo',
            'organo_politico',
            'piano_progetto',
            'politico',
            'pubblicazione',
            'luogo',
            'regolamento',
            'rendiconto',
            'sala_pubblica',
            'servizio',
            'ufficio',
            'servizio_sul_territorio',
            'statuto'
        );
        $tools = new OCOpenDataTools();
        $tools->pushOrganization();
        $generator = $tools->getDatasetGenerator();
        if ($generator instanceof OcOpendataDatasetGeneratorInterface) {
            foreach( $classes as $class ) {
                $logs = array( OpenPAInstance::current()->getIdentifier(), $class);
                try {
                    $object = $generator->createFromClassIdentifier(
                        $class,
                        array(),
                        $options['dry-run'] !== null
                    );
                    $count++;
                    if (!$options['dry-run']) {
                        $logs[] = '#' . $object->attribute('id');
                        OpenPALog::output("Generato/aggiornato oggetto " . $object->attribute('id'));
                        try {
                            $tools->pushObject($object);
                            $logs[] = 'ok';
                        }catch( Exception $e ){
                            $logs[] =  $e->getMessage();
                            OpenPALog::error( $e->getMessage() );
                        }
                    }
                }catch( Exception $e ){
                    $logs[] =  $e->getMessage();
                    OpenPALog::error( $e->getMessage() );
                }
                eZLog::write( implode( ' ', $logs ), 'ckan_generate.log' );
            }
            OpenPALog::warning( "Totale: " . $count );
        } else {
            throw new Exception('Generator not found');
        }
    }

    if ($options['parse_indicepa']){
        $sourceTextFilePath = eZSys::rootDir() . '/extension/openpa/data/amministrazioni.txt';
        if ( file_exists( $sourceTextFilePath ) ) {
            $textContent = file_get_contents( $sourceTextFilePath );
            $rows = explode("\n", $textContent);
            $data = array();
            foreach ($rows as $index => $row) {
                if ($index == 0) {
                    $headers = explode("\t", $row);
                } elseif ($index > 1) {
                    $data[$index] = explode("\t", $row);
                }
            }
            array_walk($headers, function (&$value, $key) {
                $value = trim($value);
            });
            $result = array();
            foreach ($data as $d) {
                array_walk($d, function (&$value, $key) {
                    $value = trim($value);
                });
                $addToResult = false;
                if (is_array($d) && count($d) == count($headers)) {
                    $addToResult = array_combine($headers, $d);
                }
                if ( $addToResult && $addToResult['Provincia'] == 'TN' ){
                    $result[] = $addToResult;
                }
            }
            if ( count( $result ) > 0 ){
                eZFile::create('amministrazioni.php', eZSys::rootDir() . '/extension/openpa/data/', '<?php $data=' . var_export($result,1) . ';' );
            }
        }else{
            OpenPALog::error( "File $sourceTextFilePath non trovato" );
        }
    }

    if ($options['find_codiceipa']){
        include eZSys::rootDir() . '/extension/openpa/data/amministrazioni.php';
        $siteName = eZINI::instance()->variable( 'SiteSettings', 'SiteName' );
        if ( strpos( strtolower( $siteName ), 'comune di' ) === false ){
            $siteName = false;
        }

        if ( $siteName ){
            $found = false;
            $siteNameMatch = str_replace( '-', ' ', $siteName );
            $siteNameMatch = str_replace( array( 'é', 'è' ), 'e\'', $siteNameMatch );
            $siteNameMatch = str_replace( array( 'ò' ), 'o\'', $siteNameMatch );
            OpenPALog::notice("Ricerca di $siteName ($siteNameMatch)");
            foreach( $data as $item ){
                if ( strpos( strtolower( $siteNameMatch ), strtolower( $item['des_amm'] ) ) !== false ){
                    $found = $item;
                    break;
                }
            }

            if ( $found ){
                $homePage = OpenPaFunctionCollection::fetchHome();
                $homeObject = $homePage->attribute( 'object' );
                if ( $homeObject instanceof eZContentObject )
                {
                    /** @var eZContentObjectAttribute[] $dataMap */
                    $dataMap = $homeObject->attribute( 'data_map' );
                    if ( isset( $dataMap['contacts'] )
                         && $dataMap['contacts'] instanceof eZContentObjectAttribute
                         && $dataMap['contacts']->attribute( 'data_type_string' ) == 'ezmatrix' )
                    {
                        $contacts = $dataMap['contacts'];
                        $fullContacts = array();
                        if ( $dataMap['contacts']->attribute( 'has_content' ) ){
                            $trans = eZCharTransform::instance();
                            $matrix = $dataMap['contacts']->attribute( 'content' )->attribute( 'matrix' );
                            foreach( $matrix['rows']['sequential'] as $row )
                            {
                                $columns = $row['columns'];
                                $name = $columns[0];
                                if ( !empty( $columns[1] ) )
                                {
                                    $fullContacts[$name] = $columns[1];
                                }
                            }
                        }

                        //print_r($fullContacts);

                        //print_r($found);

                        $fullContacts["Codice iPA"] = $found['cod_amm'];

                        if ( !isset($fullContacts["Codice fiscale"] ) && $found['Cf'] != 'null' ){
                            if ( $found['cf_validato'] == 'S' ){
                                $fullContacts["Codice fiscale"] = $found['Cf'];
                            }
                        }

                        //@todo riempire tutti?

                        $storeContacts = array();
                        foreach( OpenPAPageData::$contactsMatrixFields as $id ){
                            if ( !isset($fullContacts[$id]) ){
                                $storeContacts[$id] = '';
                            }else{
                                $storeContacts[$id] = $fullContacts[$id];
                            }
                        }

                        //print_r($storeContacts);

                        $stringArray = array();
                        foreach($storeContacts as $key => $value){
                            $stringArray[] = $key . '|' . $value;
                        }
                        $string = implode('&', $stringArray);
                        OpenPALog::warning("Salvo codice Ipa ({$storeContacts['Codice iPA']}) nei contatti");
                        $contacts->fromString($string);
                        $contacts->store();

                    }
                }
            }
        }
    }

    if ($options['check_org']){

        $dict = array(
            '_telefono' => "Telefono",
            '_fax' => "Fax",
            '_email' => "Email",
            '_pec' => "PEC",
            '_indirizzo' => "Indirizzo",
            '_facebook' => "Facebook",
            '_twitter' => "Twitter",
            '_web' => "Web",
            '_cf' => "Codice fiscale",
            '_pi' => "Partita IVA"
        );

        $fullContacts = array();
        $homePage = OpenPaFunctionCollection::fetchHome();
        $homeObject = $homePage->attribute( 'object' );
        $contacts = false;
        if ( $homeObject instanceof eZContentObject )
        {
            /** @var eZContentObjectAttribute[] $dataMap */
            $dataMap = $homeObject->attribute( 'data_map' );
            if ( isset( $dataMap['contacts'] )
                 && $dataMap['contacts'] instanceof eZContentObjectAttribute
                 && $dataMap['contacts']->attribute( 'data_type_string' ) == 'ezmatrix' )
            {
                $contacts = $dataMap['contacts'];
                if ( $dataMap['contacts']->attribute( 'has_content' ) ){
                    $trans = eZCharTransform::instance();
                    $matrix = $dataMap['contacts']->attribute( 'content' )->attribute( 'matrix' );
                    foreach( $matrix['rows']['sequential'] as $row )
                    {
                        $columns = $row['columns'];
                        $name = $columns[0];
                        if ( !empty( $columns[1] ) )
                        {
                            $fullContacts[$name] = $columns[1];
                        }
                    }
                }
            }
        }

        if ( count( $fullContacts ) < 3)
        {
            $data = array();
            $parts = array();
            $notes = OpenPaFunctionCollection::fetchFooterNotes();
            $attribute = $notes['result'];
            if ( $attribute instanceof eZContentObjectAttribute ){
                $string = $attribute->toString();
                $dom = new DOMDocument( '1.0', 'utf-8' );
                $string = preg_replace( array('/\s{2,}/', '/[\t\n]/'), ' ', $string );
                $string = preg_replace("/>s+</", "><", $string );
                $string = str_replace("> <", "><", $string );
                $string = str_replace( array( '&amp;nbsp;', "\xC2\xA0" ), ' ', $string ); // from ezxhtmlxmloutput.php
                $success = $dom->loadXML( $string );
                if ( $success )
                {
                    $xpath = new DOMXPath( $dom );
                    foreach( $xpath->query( '//paragraph' ) as $element ){
                        $parts[] = $element->nodeValue;
                    }
                }

                foreach( $parts as $part ){
                    $subParts = explode( ' - ', $part );
                    foreach( $subParts as $subPart ){

                        if ( !isset( $data['_indirizzo'] ) ){
                            if ( strpos( strtolower( $subPart ), 'piazza' ) !== false
                                 || strpos( strtolower( $subPart ), 'via' ) !== false
                                 || strpos( strtolower( $subPart ), 'strada' ) !== false
                                 || strpos( strtolower( $subPart ), 'corso' ) !== false
                                 || strpos( strtolower( $subPart ), 'frazione' ) !== false
                                 || strpos( strtolower( $subPart ), 'località' ) !== false
                                 || strpos( strtolower( $subPart ), 'strèda' ) !== false ){
                                $siteName = eZINI::instance()->variable( 'SiteSettings', 'SiteName' );
                                if ( strpos( strtolower( $siteName ), 'comune di' ) !== false ){
                                    $siteName = trim( str_ireplace( 'comune di', '', $siteName ) );
                                }else{
                                    $siteName = false;
                                }
                                $indirizzo = trim( $subPart );
                                $siteNameMatch = str_replace( array( 'é', 'è' ), 'e', $siteName );
                                $indirizzoMatch = str_replace( array( 'é', 'è' ), 'e', $indirizzo );
                                if ( strpos( strtolower( $indirizzoMatch ), strtolower( $siteNameMatch ) ) === false ){
                                    $indirizzo .= ' ' . $siteName;
                                }
                                $data['_indirizzo'] = $indirizzo;
                            }
                        }

                        if ( !isset( $data['_telefono'] ) ){
                            if ( strpos( strtolower( $subPart ), 'telefono' ) !== false ){
                                $data['_telefono'] = trim( str_replace( ':', '', str_ireplace( 'telefono', '', $subPart ) ) );
                            }elseif ( strpos( strtolower( $subPart ), ' tel ' ) !== false ){
                                $data['_telefono'] = trim( str_replace( ':', '', str_ireplace( 'tel', '', $subPart ) ) );
                            }elseif ( strpos( strtolower( $subPart ), ' tel:' ) !== false ){
                                $data['_telefono'] = trim( str_replace( ':', '', str_ireplace( 'tel', '', $subPart ) ) );
                            }elseif ( strpos( $subPart, 'Tel ' ) !== false ){
                                $data['_telefono'] = trim( str_replace( ':', '', str_ireplace( 'tel', '', $subPart ) ) );
                            }elseif ( strpos( strtolower( $subPart ), 'centralino' ) !== false ){
                                $data['_telefono'] = trim( str_replace( ':', '', str_ireplace( 'centralino', '', $subPart ) ) );
                            }
                        }

                        if ( !isset( $data['_fax'] ) ){
                            if ( strpos( strtolower( $subPart ), 'fax' ) !== false ){
                                $data['_fax'] = trim( str_replace( ':', '', str_ireplace( 'fax', '', $subPart ) ) );
                            }
                        }

                        if ( !isset( $data['_email'] ) ){
                            if ( strpos( strtolower( $subPart ), 'email:' ) !== false ){
                                $data['_email'] = trim( str_replace( ':', '', str_ireplace( 'email:', '', $subPart ) ) );
                            }elseif ( strpos( strtolower( $subPart ), 'e-mail:' ) !== false ){
                                $data['_email'] = trim( str_replace( ':', '', str_ireplace( 'e-mail:', '', $subPart ) ) );
                            }elseif ( strpos( strtolower( $subPart ), 'e-mail' ) !== false ){
                                $data['_email'] = trim( str_replace( ':', '', str_ireplace( 'e-mail', '', $subPart ) ) );
                            }elseif ( strpos( strtolower( $subPart ), 'posta elettronica' ) !== false ){
                                $data['_email'] = trim( str_replace( ':', '', str_ireplace( 'posta elettronica', '', $subPart ) ) );
                            }elseif ( strpos( strtolower( $subPart ), 'mail:' ) !== false ){
                                $data['_email'] = trim( str_replace( ':', '', str_ireplace( 'mail:', '', $subPart ) ) );
                            }
                        }

                        if ( !isset( $data['_pec'] ) ){
                            if ( strpos( strtolower( $subPart ), 'pec:' ) !== false ){
                                $data['_pec'] = trim( str_replace( ':', '', str_ireplace( 'pec:', '', $subPart ) ) );
                            }elseif ( strpos( strtolower( $subPart ), 'pec ' ) !== false ){
                                $data['_pec'] = trim( str_replace( ':', '', str_ireplace( 'pec ', '', $subPart ) ) );
                            }elseif ( strpos( strtolower( $subPart ), 'posta elettronica certificata' ) !== false ){
                                $data['_pec'] = trim( str_replace( ':', '', str_ireplace( 'posta elettronica certificata', '', $subPart ) ) );
                            }
                        }
                    }
                }



                foreach( $data as $identifier => $value ){
                    $id = $dict[$identifier];
                    if ( !isset( $fullContacts[$id] ) ){
                        $fullContacts[$id] = $value;
                    }
                }
            }
        }

        $storeContacts = array();
        foreach( $dict as $key => $id ){
            if ( !isset($fullContacts[$id]) ){
                $storeContacts[$id] = '';
            }else{
                $storeContacts[$id] = $fullContacts[$id];
            }
        }

        if( !empty($fullContacts) && $contacts){
            $stringArray = array();
            foreach($storeContacts as $key => $value){
                $stringArray[] = $key . '|' . $value;
            }
            $string = implode('&', $stringArray);
            OpenPALog::warning("Salvo contatti");
            $contacts->fromString($string);
            $contacts->store();
        }


    }

    if ($options['areatematica_sync']) {

        $footerObject = eZContentObject::fetchByRemoteID($footerRemoteId);

        if ( $footerObject ) {

            $classi = array(
                'pagina_sito'
            );

            foreach( $classi as $identifier )
            {
                OpenPALog::warning( 'Sincronizzo classe ' . $identifier );
                $tools = new OpenPAClassTools( $identifier, true ); // creo se non esiste
                $tools->sync( true, false ); // forzo e rimuovo attributi in più
            }


            $remotes = array(
                'opendata_link',
                'opendata_datasetcontainer',
                'opendata_amministrazione',
                'opendata_iniziativa',
                'opendata_normativa',
                'opendata_info',
                'opendata_global_info',
                'opendata_area'
            );

            $areaObject = eZContentObject::fetchByRemoteID('opendata_area');
            if ( !$areaObject ) {
                $apiNode = OpenPAApiNode::fromLink('http://openpa.opencontent.it/api/opendata/v1/content/object/opendata_area');
                OpenPALog::warning("Create area");
                $areaObject = $apiNode->createContentObject($footerObject->attribute('main_node')->attribute('parent_node_id'));
            }

            $globalObject = eZContentObject::fetchByRemoteID('opendata_global_info');
            if ( !$globalObject ) {
                $apiNode = OpenPAApiNode::fromLink('http://openpa.opencontent.it/api/opendata/v1/content/object/opendata_global_info');
                OpenPALog::warning("Create global");
                $apiNode->createContentObject($areaObject->attribute('main_node_id'));
            }


            foreach ($remotes as $remote) {
                $link = 'http://openpa.opencontent.it/api/opendata/v1/content/object/' . $remote;
                $apiNode = OpenPAApiNode::fromLink($link);
                if ( !OpenPAObjectTools::syncObjectFormRemoteApiNode($apiNode) )
                {
                    OpenPALog::warning("Create $remote");
                    $apiNode->createContentObject($areaObject->attribute('main_node_id'));
                }

                $data = json_decode(OpenPABase::getDataByURL('http://openpa.opencontent.it/api/opendata/v2/content/read/' . $remote), true);
                if (isset( $data['data']['ita-IT']['layout'] )||isset( $data['data']['ita-IT']['page'] )) {
                    $object = eZContentObject::fetchByRemoteID($remote);
                    $dataMap = $object->dataMap();
                    $layoutAttribute = isset( $data['data']['ita-IT']['layout'] ) ? $dataMap['layout'] : $dataMap['page'];

                    $zones = isset( $data['data']['ita-IT']['layout'] ) ? $data['data']['ita-IT']['layout'] : $data['data']['ita-IT']['page'];
                    $page = new eZPage();
                    $pools = array();

                    if ( isset($zones['zone_layout']) ) {

                        $page->setAttribute('zone_layout', $zones['zone_layout']);
                        unset( $zones['zone_layout'] );

                        foreach ($zones as $zoneIdentifier => $zone) {
                            $newZone = $page->addZone(new eZPageZone());
                            $newZone->setAttribute('id', $zone['zone_id']);
                            $newZone->setAttribute('zone_identifier', $zoneIdentifier);

                            $zoneBlocksIds = array();
                            foreach ($zone['blocks'] as $block) {
                                $zoneBlocksIds[] = $block['block_id'];
                            }

                            $db->query("DELETE from ezm_block WHERE zone_id = '" . $db->escapeString($zone['zone_id']) . "' AND id NOT IN ('" . implode("', '",
                                    $zoneBlocksIds) . "')");

                            foreach ($zone['blocks'] as $block) {

                                $dbBlock = new eZFlowBlock(array(
                                    'id' => $block['block_id'],
                                    'zone_id' => $newZone->attribute('id'),
                                    'name' => $block['name'],
                                    'node_id' => $object->attribute('main_node_id'),
                                    'block_type' => $block['type']
                                ));
                                $dbBlock->store();

                                $newBlock = $newZone->addBlock(new eZPageBlock($block['name']));
                                $newBlock->setAttribute('action', 'add');
                                $newBlock->setAttribute('id', $block['block_id']);
                                $newBlock->setAttribute('zone_id', $newZone->attribute('id'));
                                $newBlock->setAttribute('type', $block['type']);
                                $newBlock->setAttribute('view', $block['view']);
                                if (is_array($block['custom_attributes'])) {
                                    $newBlock->setAttribute('custom_attributes', $block['custom_attributes']);
                                }
                                foreach ($block['valid_items'] as $index => $item) {
                                    $itemObject = eZContentObject::fetchByRemoteID($item);
                                    if ($itemObject instanceof eZContentObject) {

                                        $timestamp = time();

                                        $db->query("DELETE from ezm_pool WHERE block_id = '" . $db->escapeString($block['block_id']) . "'");
                                        $pools[] = array(
                                            'blockID' => $block['block_id'],
                                            'objectID' => $itemObject->attribute('id'),
                                            'nodeID' => $itemObject->attribute('main_node_id'),
                                            'priority' => ++$index,
                                            'timestamp' => $timestamp
                                        );

                                        $newItem = $newBlock->addItem(new eZPageBlockItem());
                                        $newItem->setAttribute('object_id', $itemObject->attribute('id'));
                                        $newItem->setAttribute('node_id', $itemObject->attribute('main_node_id'));
                                        $newItem->setAttribute('priority', $index);
                                        $newItem->setAttribute('ts_publication', $timestamp);
                                    }
                                }
                            }
                        }
                    }else{
                        $page->setAttribute('zone_layout', '0ZonesLayoutFolder');
                        $newZone = $page->addZone(new eZPageZone());
                        $newZone->setAttribute('id', 'nessuna');
                        $newZone->setAttribute('zone_identifier', 'nessuna');
                    }
                    if (count($pools) > 0) {
                        $db->lock( 'ezm_pool' );

                        foreach ( $pools as $item )
                        {
                            $escapedBlockID = $db->escapeString( $item['blockID'] );

                            $itemCount = $db->arrayQuery(
                                "SELECT COUNT( * ) as count " .
                                "FROM ezm_pool " .
                                "WHERE block_id='$escapedBlockID' AND object_id=" . (int)$item['objectID'] );

                            if ( $itemCount[0]['count'] == 0 )
                            {
                                $db->query( "INSERT INTO ezm_pool ( block_id, object_id, node_id, priority, ts_publication, ts_visible ) " .
                                            "VALUES ( '$escapedBlockID', " . (int)$item['objectID'] . ", " . (int)$item['nodeID'] . ", " . (int)$item['priority'] . ", " . (int)$item['timestamp'] . ", 1 )" );
                            }
                        }

                        $db->unlock();

                    }
                    $layoutAttribute->setContent($page);
                    $layoutAttribute->store();
                    eZFlowOperations::update( array($object->attribute('main_node_id')) );
                }
            }

            OpenPAMenuTool::generateAllMenus();
            eZCache::clearByTag( 'template' );
        }
    }

    if ($options['fix_footer_link_remote_id']) {
        $footerObject = eZContentObject::fetchByRemoteID($footerRemoteId);
        if ( !$footerObject instanceof eZContentObject){
            $remoteId = '2d2cb247ff71140e26db4858eec90462';
            $object = eZContentObject::fetchByRemoteID($remoteId);
            if (!$object instanceof eZContentObject) {
                $solr = new eZSolr();
                $searchResults = $solr->search( '', array(
                    'Filter' => array(
                        'meta_class_identifier_ms:pagina_sito',
                        'attr_name_t:"linked open data"'
                    ),
                    'SearchLimit' => 1
                ) );
                if( $searchResults['SearchCount'] > 0 ){
                    $object = eZContentObject::fetch( $searchResults['SearchResult'][0]->attribute( 'contentobject_id' ) );
                }
            }

            if ($object instanceof eZContentObject) {
                OpenPALog::warning("Fix " . $object->attribute('name'));
                if (!$options['dry-run']) {
                    $object->setAttribute('remote_id', $footerRemoteId);
                    $object->setAttribute('modified', time());
                    $object->store();
                }
            }
        }
        else
        {
            OpenPALog::warning("Found " . $footerObject->attribute('name'));
        }
    }

    if ($options['remove_old_dataset']) {
        $datasetRemoteIdList = array(
            "ckan_eb74838a-d480-4a76-83b8-946c60b5279f",
            "ckan_293441da-4ca0-4356-bbca-e0aad2f84ba4",
            "8507a59a06c251c7ea4b8b47dd18164e",
            "67f54ef1e0daf7fb051629c850eabf22",
            "ckan_419dc9aa-0e66-4e30-b887-cb11c1b0f2b6",
            "ckan_1add78f1-20fb-45e2-a99d-a1ccbd7d47e6",
            "ckan_83277421-9c0f-459b-8e0c-cd1585341fb0",
            "71374d090e998ddddaa8aee867de9631",
            "ckan_380badcc-ba48-4fa4-9308-e811ec4c2642",
            "ckan_727f4e15-15ef-4960-b11b-4095fd193f21",
            "ckan_3ab94394-ed76-486f-99b4-90287f4c2f7c",
            "f208ab93873ecf89db5f978172c869c0",
            "01c49f2af3190650b09835e79dabf9c9",
            "ckan_957a7822-403b-4f24-bc3b-906d578ea503",
            "ckan_b052caab-6bc9-48c7-99d2-6baae124bc17",
            "ckan_b7740369-5644-4185-b74f-83b39e3691ba",
            "ckan_eb19fcf4-b2d5-4dd4-b24c-5eab2d2d453c",
            "ckan_3045e37a-89cc-4cd7-b17f-8a812e038bc6",
            "ckan_b12738c2-d9de-4ff3-aa9c-d43e3ab89e98",
            "9f81113b9a9958e9c05be355a94d3e39",
            "70ca2b227f21be5e244bc6b0fa575971",
            "ckan_7cae0f78-d2d1-47f4-9c9c-76ed9dcc843e",
            "ckan_5c83843f-7d51-42c4-87ba-ab631a9f0d40",
            "039afd43bef1667a158a9b14b43e7fc2",
            "193b492053510457456cc26140812cd3",
            "73118e20a220010dea571d81601b59e6",
            "88b3a8792cefc9de9926f050160512eb",
            "1020596e184ac34461fdabd0ea0fca5d",
            "61ffa52038c7604b4129c70d139dd1a3",
            "46d1dba0fef085d07fa6f5fe597b304d",
            "79f69f253c7b371d62cbe86078ccb1d9",
            "3f83e35d0de0b030914fef2dd0f75de4",
            "d910facbec5f1f45840e12e8f566d057",
            "805ad84695db8e73b4c0bc095c3e680d",
            "6a356f1903bdc9023142d64c709594b8",
            "21bb91514240d7ca5c24b3a453c0807e",
            "53cf015d9c67f44d69edd0454e10f683",
            "836cdf908fae03f98f59bfe2d4ec52f9",
            "6d619ff41c78f4e6c5d0ae361190eba8",
            "0545e8ac42b0eb8190a93272c5b9c13d",
            "46b000530c1984ba7056b11192be12e1",
            "70421b6723955486a567cfb6883a34f8",
            "00700a2526ae145a65ae69b838996559",
            "d7878758edd977c596a00182421f2568",
            "e6025edd2e8d27ffeaa24e00aa273d10",
            "aeb444a6cfe1ab1c1b70ae529fa85310",
            "d7fcf26260b23425d230bc37ad4e7f56",
            "ad682fac0d2a99838cf987ff51ef1f2c",
            "e3fe176b3abeddb8092c662e91a840c6",
            "cadc0ad943cf872ef9127631fb67c7e1",
            "dcfcb34c5c3fb58a7c58e7a31dab5fd5",
            "28a7872a5096025daa2bebc5e0671def",
            "3761161c950972f38ea6f0147f52235c",
            "6b43392fc40a0dd9ae5d9bb96208304c",
            "62d8d93bfc5253f88cf17a84b51ac3bf",
            "94860a71b346b00c1a61935239e2cdf2",
            "d4f1bdf9eb198d20605aab43106b1109",
            "1fbaeeed7b08487d3b4baba0dc6f87b7",
            "412da8d0286fcbb6ee9fc6f0f1ae8b7a",
            "b97fa9701b9f1436f80475be0176e4e5",
            "1e0e3d70e38a0cf789473b51aa7b63df",
            "acf1cd37c7e7c442656f6230a96ac27c",

            "74c52b1af7b47536ee0200c27563b842"
        );

        foreach ($datasetRemoteIdList as $remoteId) {
            $object = eZContentObject::fetchByRemoteID($remoteId);
            if ($object instanceof eZContentObject) {
                OpenPALog::warning("Remove " . $object->attribute('name'));
                if (!$options['dry-run']) {
                    if (!eZContentObjectOperations::remove($object->attribute('id'), false)) {
                        OpenPALog::error("Problem deleting object #" . $object->attribute('id'));
                    }
                }
            }
        }
    }

    if ($options['fix_area_remote_ids']) {
        $matches = array(
            'c62f589eb338057627de6f62d08b48ac' => 'opendata_area',
            '0a79408f805dcc52bc402411b34bb95e' => 'opendata_global_info',
            'e419816bf6a5fb323931fade9eb44a8b' => 'opendata_link',
            '8aa799d9883f6ab7d1d1f35346d670cf' => 'opendata_datasetcontainer',
            '03298100280d2e69bffa279ae3ecef54' => 'opendata_amministrazione',
            'fe4b6d6e7aa51736573ec77adc69593c' => 'opendata_iniziativa',
            'a7a7c676012d54d87b5bc6b7551c0df6' => 'opendata_normativa',
            'e62e8239a4b7bfa44c9336822a2e8622' => 'opendata_info',
        );

        foreach ($matches as $old => $new) {
            $object = eZContentObject::fetchByRemoteID($old);
            if ($object instanceof eZContentObject) {
                if (!eZContentObject::fetchByRemoteID($new)) {
                    OpenPALog::warning("Fix remote " . $new);
                    if (!$options['dry-run']) {
                        $object->setAttribute('remote_id', $new);
                        $object->setAttribute('modified', time());
                        $object->store();
                    }
                } else {
                    OpenPALog::error("Remote $new already exists: can not fix $old");
                }
            }
        }
    }

    if ($options['add_class_descriptions']) {
        $infos = array(
            'accordo' =>
                array(
                    'titolo' => 'Titolo dell\'accordo',
                    'servizio' => 'Servizio che si occupa dell\'accordo',
                    'abstract' => 'Breve descrizione dell\'accordo',
                    'file' => 'Atto',
                    'descrizione' => 'Descrizione più approfondita dell\'accordo',
                    'argomento' => 'Materia dell\'accordo',
                    'data_inizio_validita' => 'Data a partire dalla quale l\'accordo è valido',
                    'data_fine_validita' => 'Data di scadenza dell\'accordo',
                    'data_iniziopubblicazione' => 'Data in cui l\'accordo è pubblicato',
                    'data_archiviazione' => 'Data di archiviazione dell\'accordo',
                    'documento' => 'Oggetti correlati con l\'accordo',
                    'parola_chiave' => 'Concetti più significativi riguardanti il contenuto',
                    'ID' => 'Codice identificativo unico dell\'accordo',
                ),
            'albo_elenco' =>
                array(
                    'titolo' => 'Titolo dell\'elenco',
                    'abstract' => 'Breve descrizione dell\'elenco',
                    'servizio' => 'Servizio che si occupa dell\'elenco',
                    'ufficio' => 'Ufficio che si occupa dell\'elenco',
                    'argomento' => 'Materia dell\'elenco',
                    'file' => 'Documento dell\'elenco',
                    'descrizione' => 'Descrizione completa dell\'elenco',
                    'data_inizio_validita' => 'Data a partire dalla quale l\'elenco è valido',
                    'data_fine_validita' => 'Data di scadenza dell\'elenco',
                    'data_iniziopubblicazione' => 'Data in cui l\'elenco è pubblicato',
                    'data_archiviazione' => 'Data di archiviazione dell\'elenco',
                    'link' => 'Collegamento al portale esterno dell\'albo o dell\'elenco',
                    'documento' => 'Oggetti correlati con l\'elenco',
                    'parola_chiave' => 'Concetti più significativi riguardanti il contenuto',
                    'image' => 'Immagine principale dell\'elenco',
                    'ID' => 'Codice identificativo unico dell\'elenco',
                ),
            'area' =>
                array(
                    'titolo' => 'Nome dell\'Area',
                    'abstract' => 'Sintetica descrizione dell\'Area',
                    'descrizione' => 'Descrizione completa dell\'Area',
                    'image' => 'Immagine principale dell\'Area',
                    'codice' => 'Codice identificativo dell\'Area',
                    'ID' => 'Codice identificativo unico dell\'Area',
                ),
            'articolo' =>
                array(
                    'titolo' => 'Titolo dell\'articolo di giornale',
                    'testata' => 'Testata giornalistica che ha pubblicato l\'articolo',
                    'autore' => 'Autore dell\'articolo di giornale',
                    'pagina' => 'Numero della pagina in cui si trova l\'articolo di giornale',
                    'pagina_continuazione' => 'Numero della pagina dove continua l\'articolo di giornale',
                    'argomento_articolo' => 'Materia dell\'articolo di giornale',
                    'descrizione' => 'Testo completo dell\'articolo di giornale',
                    'tags' => 'Concetti più significativi riguardanti il contenuto dell\'articolo di giornale',
                    'anno' => 'Anno in cui è stato scritto l\'articolo di giornale',
                    'image' => 'Immagine principale dell\'articolo di giornale',
                    'file' => 'File pdf dell\'articolo di giornale',
                    'data_iniziopubblicazione' => 'Data in cui l\'articolo di giornale è pubblicato',
                    'data_archiviazione' => 'Data in cui l\'articolo di giornale è archiviato',
                    'riferimento' => 'Oggetti in relazione con l\'articolo di giornale',
                    'ID' => 'Codice identificativo unico dell\'articolo di giornale',
                ),
            'associazione' =>
                array(
                    'titolo' => 'Titolo dell\'associazione',
                    'abstract' => 'Breve descrizione dell\'associazione',
                    'indirizzo' => 'Indirizzo dell\'associazione',
                    'presso' => 'Edificio in cui ha sede l\'Associazione',
                    'cap' => 'Codice di avviamento postale del Comune in cui si trova l\'associazione',
                    'località' => 'Località in cui si trova l\'associazione',
                    'telefono' => 'Numero di telefono dell\'associazione',
                    'numero_telefono1' => 'Numero di telefono alternativo dell\'associazione',
                    'fax' => 'numero di fax dell\'associazione',
                    'casella_postale' => 'Casella postale dell\'associazione',
                    'email' => 'Indirizzo e-mail dell\'associazione',
                    'url' => 'Sito internet dell\'associazione',
                    'url_facebook' => 'Pagina facebook dell\'associazione',
                    'circoscrizione' => 'Organo politico di riferimento per l\'associazione',
                    'categoria' => 'Categoria dell\'associazione',
                    'argomento' => 'Materia dell\'associazione',
                    'contatti' => 'Contatti dell\'associazione',
                    'referente_nome' => 'Nome del referente dell\'associazione',
                    'referente_ruolo' => 'Ruolo ricoperto dal referente dell\'associazione',
                    'referente_indirizzo' => 'Indirizzo del referente dell\'associazione',
                    'referente_telefono' => 'Numero di telefono del referente dell\'associazione',
                    'referente_fax' => 'Numero di fax del referente dell\'associazione',
                    'scheda' => 'Scheda descrittiva dell\'associzione',
                    'image' => 'Immagine dell\'associazione',
                    'gps' => 'Georeferenziazione dell\'associazione',
                    'cod_associazione' => 'Codice dell\'associazione',
                    'data_inizio_validita' => 'Data di istituzione dell\'associazione',
                    'data_archiviazione' => 'Data di scioglimento dell\'associazione',
                    'ID' => 'Codice identificativo unico dell\'associazione',
                ),
            'avviso' =>
                array(
                    'titolo' => 'Titolo dell\'avviso',
                    'abstract' => 'Breve descrizione dell\'avviso',
                    'descrizione' => 'Testo completo dell\'avviso',
                    'file' => 'File pdf dell\'avviso',
                    'servizio' => 'Servizio che si occupa di quanto esposto nell\'avviso',
                    'ufficio' => 'Ufficio che si occupa di quanto esposto nell\'avviso',
                    'argomento' => 'Materia dell\'avviso',
                    'data' => 'Data dell\'avviso',
                    'data_iniziopubblicazione' => 'Data in cui l\'avviso è pubblicato',
                    'data_archiviazione' => 'Data in cui cessa la pubblicazione dell\'avviso',
                    'image' => 'Immagine principale dell\'avviso',
                    'riferimento' => 'Oggetti correlati con l\'avviso',
                    'url' => 'Collegamento al portale esterno di riferimento per l\'avviso',
                    'evento_vita' => 'Evento della vita a cui si riferisce l\'avviso',
                    'gps' => 'Georeferenziazione del luogo citato nell\'avviso',
                    'ID' => 'Codice identificativo unico dell\'avviso',
                ),
            'bando' =>
                array(
                    'titolo' => 'Titolo del bando',
                    'abstract' => 'Breve descrizione del bando',
                    'descrizione' => 'Descrizione completa del bando di gara',
                    'servizio' => 'Servizio che si occupa del bando',
                    'ufficio' => 'Ufficio che si occupa del bando',
                    'argomento' => 'Materia del bando di gara',
                    'file' => 'Documento del bando',
                    'data_inizio_validita' => 'Data a partire dalla quale il bando è valido',
                    'data_fine_validita' => 'Data e ora di fine validità del bando',
                    'file_avviso' => 'Documento di aggiudicazione del bando di gara',
                    'data_iniziopubblicazione' => 'Data in cui il bando è pubblicato',
                    'data_archiviazione' => 'Data di archiviazione del bando',
                    'link' => 'Collegamento al portale esterno di riferimento per il bando',
                    'documento' => 'Oggetti correlati con il bando',
                    'numero_protocollo_bando' => 'Numero di protocollo del bando di gara',
                    'anno_protocollo_bando' => 'Anno di protocollo del bando di gara',
                    'image' => 'Immagine principale del bando',
                    'tipologia_bando' => 'Tipo di bando',
                    'fase' => 'Fase del bando',
                    'ID' => 'Codice identificativo unico del bando di gara',
                ),
            'bilancio_di_previsione' =>
                array(
                    'anno' => 'Anno a cui si riferisce il bilancio',
                    'abstract' => 'Breve descrizione del bilancio di previsione',
                    'servizio' => 'Servizio che si occupa della redazione del bilancio',
                    'bilancio_di_previsione' => 'File del bilancio di previsione',
                    'data_iniziopubblicazione' => 'Data in cui il bilancio è reso pubblico',
                    'data_archiviazione' => 'Data in cui il bilancio di previsione è archiviato',
                    'argomento' => 'Materia del bilancio',
                    'bilancio_pluriennale' => 'File del bilancio di previsione pluriennale',
                    'relazione_previsionale_programmatica' => 'File della relazione previsionale e programmatica',
                    'programma_generale_opere_pubbliche' => 'File del programma generale delle opere pubbliche',
                    'prospetto_legge_67' => 'File del prospetto Legge 67/1987 art. 6',
                    'image' => 'Immagine principale del bilancio di previsione',
                    'ID' => 'Codice identificativo unico del bilancio di previsione',
                ),
            'circolare' =>
                array(
                    'titolo' => 'Titolo della circolare',
                    'abstract' => 'Breve descrizione della circolare',
                    'descrizione' => 'Descrizione completa della circolare',
                    'ente' => 'Ente che emana la circolare',
                    'servizio' => 'Servizio che propone la circolare',
                    'ufficio' => 'Ufficio che propone la circolare',
                    'argomento' => 'Materia della circolare',
                    'file' => 'Documento della circolare',
                    'data_inizio_validita' => 'Data a partire dalla quale la circolare è valida',
                    'data_fine_validita' => 'Data di fine validità della circolare',
                    'modulistica' => 'Modulistica collegata con la circolare',
                    'riferimento' => 'Oggetti correlati con la circolare',
                    'data_iniziopubblicazione' => 'Data in cui la circolare è pubblicata',
                    'data_archiviazione' => 'Data di archiviazione della circolare',
                    'parola_chiave' => 'Concetti più significativi riguardanti il contenuto della circolare',
                    'image' => 'Immagine principale della circolare',
                    'ID' => 'Codice identificativo unico della circolare',
                ),
            'comunicato_stampa' =>
                array(
                    'titolo' => 'Titolo del comunicato stampa',
                    'abstract' => 'Breve descrizione del comunicato stampa',
                    'file' => 'Documento del comunicato stampa',
                    'servizio' => 'Servizio che si occupa della materia del comunicato stampa',
                    'ufficio' => 'Ufficio che si occupa della materia del comunicato stampa',
                    'argomento' => 'Materia del comunicato stampa',
                    'data_inizio_validita' => 'Data a partire dalla quale il comunicato stampa è valido',
                    'data_fine_validita' => 'Data di scadenza del comunicato stampa',
                    'data_iniziopubblicazione' => 'Data in cui il comunicato stampa è pubblicato',
                    'data_finepubblicazione' => 'Data in cui cessa la pubblicazione del comunicato stampa',
                    'tipo' => 'Tipologia del documento',
                    'link' => 'Collegamento al portale esterno di riferimento per il comunicato stampa',
                    'ID' => 'Codice identificativo unico del comunicato stampa',
                ),
            'concorso' =>
                array(
                    'titolo' => 'Titolo del concorso',
                    'abstract' => 'Oggetto del concorso',
                    'descrizione' => 'Descrizione completa del concorso',
                    'servizio' => 'Servizio che si occupa del concorso',
                    'ufficio' => 'Ufficio che si occupa del concorso',
                    'argomento' => 'Materia del concorso',
                    'file' => 'Documento del concorso',
                    'data_inizio_validita' => 'Data a partire dalla quale il concorso è valido',
                    'data_fine_validita' => 'Data entro cui occorre presentare quanto indicato per la partecipazione al concorso',
                    'data_iniziopubblicazione' => 'Data in cui il concorso è pubblicato',
                    'data_archiviazione' => 'Data di archiviazione del concorso',
                    'link' => 'Collegamento al portale esterno del concorso',
                    'documento' => 'Oggetti correlati con il concorso',
                    'image' => 'Immagine principale del concorso',
                    'ID' => 'Codice identificativo unico del concorso',
                ),
            'conferimento_incarico' =>
                array(
                    'dipendente' => 'Dipendente comunale a cui è affidato l\'incarico',
                    'from_time' => 'Data in cui ha inizio l\'incarico',
                    'to_time' => 'Data di cessazione dell\'incarico',
                    'oggetto' => 'Descrizione dell\'incarico',
                    'organismo_conferente' => 'Organismo che conferisce l\'incarico',
                    '' => 'Compenso in euro spettante al dipendente a cui è conferito l\'incarico',
                    'data_archiviazione' => 'Data in cui viene archiviato il conferimento di incarico',
                    'ID' => 'Codice identificativo unico del conferimento di incarico',
                ),
            'consulenza' =>
                array(
                    'soggetto_percettore' => 'Cognome e Nome o Ragione Sociale del soggetto percettore della consulenza',
                    'partita_iva' => 'Codice Fiscale o Partita IVA del soggetto percettore della consulenza',
                    'ragione_incarico' => 'Ragione dell\'incarico di consulenza',
                    'ammontare' => 'Corrispettivo previsto per la consulenza (IVA inclusa)',
                    'erogato' => 'Corrispettivo erogato per la consulenza (IVA inclusa)',
                    'provvedimento' => 'Provvedimento correlato alla consulenza',
                    'provvedimento_testo' => 'Testo del provvedimento della consulenza',
                    'data_archiviazione' => 'Data in cui la consulenza è archiviata',
                    'durata' => 'Durata dell\'incarico di consulenza',
                    'curriculum' => 'Curriculum vitae del consulente a cui è affidato l\'incarico',
                    'incarichi_svolgimento' => '',
                    'ID' => 'Codice identificativo unico della consulenza',
                ),
            'convenzione' =>
                array(
                    'titolo' => 'Titolo della convenzione',
                    'servizio' => 'Servizio che si occupa della convenzione',
                    'short_description' => 'Breve descrizione della convenzione',
                    'file' => 'Atto della convenzione',
                    'descrizione' => 'Descrizione completa della convenzione',
                    'argomento' => 'Materia della convenzione',
                    'numero_protocollo' => 'Numero di protocollo della convenzione',
                    'anno_protocollo' => 'Anno di protocollo della convenzione',
                    'data_inizio_validita' => 'Data a partire dalla quale la convenzione è valida',
                    'data_fine_validita' => 'Data di scadenza della convenzione',
                    'data_iniziopubblicazione' => 'Data in cui la convenzione è pubblicata',
                    'data_finepubblicazione' => 'Data in cui cessa la pubblicazione della convenzione',
                    'documento' => 'Oggetti correlati con la convenzione',
                    'parola_chiave' => 'Concetti più significativi riguardanti il contenuto della convenzione',
                    'ID' => 'Codice identificativo unico della convenzione',
                ),
            'decreto_sindacale' =>
                array(
                    'oggetto' => 'Oggetto del decreto sindacale',
                    'numero' => 'Numero del decreto sindacale',
                    'anno' => 'Anno del decreto sindacale',
                    'file' => 'Atto del decreto sindacale',
                    'data' => 'Data del decreto sindacale',
                    'data_iniziopubblicazione' => 'Data in cui il decreto sindacale è pubblicato',
                    'data_finepubblicazione' => 'Data in cui cessa la pubblicazione del decreto sindacale',
                    'data_archiviazione' => 'Data in cui il decreto sindacale viene archiviato',
                    'servizio' => '',
                    'numero_protocollo' => 'Numero di protocollo del decreto sindacale',
                    'anno_protocollo' => 'Anno di protocollo del decreto sindacale',
                    'allegati' => 'Oggetti allegati al decreto sindacale',
                    'ID' => 'Codice identificativo unico del decreto sindacale',
                ),
            'deliberazione' =>
                array(
                    'oggetto' => 'Oggetto della delibera',
                    'numero' => 'Numero della delibera',
                    'competenza' => 'Area di competenza della delibera',
                    'anno' => 'Anno della delibera',
                    'file' => 'Atto della delibera',
                    'data' => 'Data di adozione della delibera',
                    'servizio' => 'Servizio di riferimento per la delibera',
                    'argomento' => 'Materia della delibera',
                    'data_iniziopubblicazione' => 'Data in cui la delibera è pubblicata',
                    'data_finepubblicazione' => 'Data in cui cessa la pubblicazione della delibera',
                    'data_esecutivita' => 'Data a partire dalla quale la delibera è esecutiva',
                    'informazioni_esecutivita' => 'Informazioni riguardanti l\'esecutività della delibera',
                    'data_archiviazione' => 'Data di archiviazione della delibera',
                    'stato' => 'Stato in cui si trova la delibera',
                    'pubblicazione' => 'Informazioni sulla pubblicazione della delibera',
                    'pareri' => 'Pareri riguardanti la delibera',
                    'iter' => 'Iter della delibera',
                    'allegati' => 'Oggetti allegati alla delibera',
                    'esito' => 'Esito della delibera',
                    'numero_protocollo' => 'Numero di protocollo della delibera',
                    'anno_protocollo' => 'Anno di protocollo della delibera',
                    'ID' => 'Codice identificativo unico della delibera',
                ),
            'determinazione' =>
                array(
                    'oggetto' => 'Oggetto della determina',
                    'numero' => 'Numero della determina',
                    'anno' => 'Anno della determina',
                    'file' => 'Atto della determina',
                    'data_firma' => 'Data in cui è firmata la determina',
                    'data_efficacia' => 'Data a partire dalla quale è efficacie la determina',
                    'ID' => 'Codice identificativo unico della determina',
                ),
            'dipendente' =>
                array(
                    'cognome' => 'Cognome del dipendente',
                    'nome' => 'Nome del dipendente',
                    'ruolo' => 'Ruolo del dipendente',
                    'abstract' => 'Breve descrizione del dipendente',
                    'image' => 'Fotografia del dipendente',
                    'area' => 'Area di appartenenza del dipendente',
                    'servizio' => 'Servizio di appartenenza del dipendente',
                    'ufficio' => 'Ufficio di appartenenza del dipendente',
                    'struttura' => 'Struttura di appartenenza del dipendente',
                    'userid' => 'Account utente riservato al dipendente',
                    'telefono' => 'Numero di telefono del dipendente',
                    'cellulare' => 'Numero di cellulare del dipendente',
                    'fax' => 'Numero di fax del dipendente',
                    'email' => 'Indirizzo email del dipendente',
                    'curriculum_vitae' => 'Curriculum Vitae del dipendente',
                    'dichiarazione_incompatibilita' => 'Dichiarazione di incompatibilità e inconferibilità del dipendente',
                    'incarico_dirigenziale' => 'Incarico dirigenziale del dipendente',
                    'compensi' => 'Compensi del dipendente',
                    'atti' => 'Atti relativi al conferimento dell\'incarico del dipendente',
                    'atti_testo' => 'Estremi dell\'atto di conferimento del dipendente',
                    'matricola' => 'Numero di matricola del dipendente',
                    'ID' => 'Codice identificativo unico del dipendente',
                ),
            'disciplinare' =>
                array(
                    'titolo' => 'Titolo del disciplinare',
                    'servizio' => 'Servizio che si occupa del disciplinare',
                    'abstract' => 'Breve descrizione del disciplinare',
                    'description' => 'Testo completo del codice disciplinare',
                    'file' => 'File del codice disciplinare',
                    'argomento' => 'Materia del disciplinare',
                    'data_inizio_validita' => 'Data a partire dalla quale il disciplinare è valido',
                    'data_fine_validita' => 'Data di scadenza del disciplinare',
                    'data_iniziopubblicazione' => 'Data in cui il disciplinare è pubblicato',
                    'data_finepubblicazione' => 'Data in cui cessa la pubblicazione del disciplinare',
                    'riferimento' => 'Oggetti correlati con il disciplinare',
                    'ID' => 'Codice identificativo unico del disciplinare',
                ),
            'documento' =>
                array(
                    'titolo' => 'Titolo del documento',
                    'abstract' => 'Breve descrizione del documento',
                    'image' => 'Immagine principale del documento',
                    'servizio' => 'Servizio che si occupa del documento',
                    'ufficio' => 'Ufficio che si occupa del documento',
                    'argomento' => 'Materia del documento',
                    'file' => 'File del documento',
                    'descrizione' => 'Testo completo del documento',
                    'data_inizio_validita' => 'Data a partire dalla quale in documento è valido',
                    'data_fine_validita' => 'Data di scadenza del documento',
                    'data_iniziopubblicazione' => 'Data in cui il documento è pubblicato',
                    'data_archiviazione' => 'Data in cui il documento è archiviato',
                    'link' => 'Collegamento ipertestuale al documento',
                    'riferimento' => 'Oggetti correlati con il documento',
                    'parola_chiave' => 'Concetti più significativi riguardanti il contenuto del documento',
                    'ID' => 'Codice identificativo unico del documento',
                ),
            'ente_controllato' =>
                array(
                    'name' => 'Nome dell\'ente controllato',
                    'piva' => 'P.IVA o Codice Fiscale dell\'ente',
                    'descrizione' => 'Descrizione dell\'ente controllato',
                    'indirizzo_e_recapiti' => 'Contatti dell\'ente controllato',
                    'file' => 'Scheda descrittiva dell\'ente controllato',
                    'tipologia' => 'Tipologia di ente controllato',
                    'funzioni_attribuite' => 'Funzioni spettanti all\'ente controllato',
                    'ragione_sociale' => 'Ragione sociale dell\'ente controllato',
                    'percentuale_partecipazione' => 'Percentuale di partecipazione da parte del Comune',
                    'partecipazione' => 'Tipologia di partecipazione da parte del Comune',
                    'durata_dell_impegno' => 'Durata dell\'impegno da parte del Comune',
                    'onere_complessivo' => 'Onere complessivo a qualsiasi titolo gravante per l\'anno sul bilancio dell\'amministrazione dovuto alla partecipazione nell\'ente',
                    'numero_rappresentanti' => 'Numero dei rappresentanti dell\'amministrazione negli organi di governo dell\'ente controllato',
                    'amministratori_ente_locale' => 'Trattamento economico complessivo spettante a ciascun rappresentante dell\'amministrazione perchè facente parte dell\'ente controllato',
                    'rappresentante' => 'Rappresentante dell\'amministrazione che riceve compensi perché partecipa all\'attività dell\'ente controllato',
                    'amministratori_societa' => 'Dati relativi agli incarichi di amministratore dell\'ente (Società) e il relativo trattamento economico complessivo',
                    'bilanci_recenti' => 'Risultati di bilancio degli ultimi tre esercizi finanziari dell\'ente controllato',
                    'url' => 'Link al sito dell\'ente controllato',
                    'data_di_inizio_partecipazione' => 'Data di inizio della partecipazione comunale nell\'ente',
                    'data_di_fine_partecipazione' => 'Data in cui la partecipazione del Comune nell\'ente finisce',
                    'ID' => 'Codice identificativo unico dell\'ente controllato',
                ),
            'ristorante' =>
                array(
                    'titolo' => 'Nome dell\'esercizio di ristorazione',
                    'progressivo' => 'Codice del locale',
                    'abstract' => 'Breve descrizione dell\'esercizio di ristorazione',
                    'descrizione' => 'Descrizione completa del locale',
                    'image' => 'Logo del locale',
                    'tipo_locale' => 'Tipologia di locale',
                    'indirizzo' => 'Indirizzo dell\'esercizio di ristorazione',
                    'localita' => 'Località in cui si trova l\'esercizio di ristorazione',
                    'cap' => 'Codice di avviamento postale del Comune in cui si trova il locale',
                    'gps' => 'Georeferenziazione dell\'esercizio di ristorazione',
                    'circoscrizione' => 'Organo politico di riferimento',
                    'telefono' => 'Numero di telefono del locale',
                    'fax' => 'Numero di fax del locale',
                    'email' => 'Indirizzo e-mail del locale ',
                    'url' => 'Sito internet dell\'esercizio di ristorazione',
                    'orario' => 'Orario di apertura dell\'esercizio di ristorazione',
                    'riposo' => 'Giorno di riposo dell\'esercizio di ristorazione',
                    'numero_coperti' => 'Numero di coperti totali del locale',
                    'numero_coperti_esterni' => 'Numero di coperti all\'esterno del locale',
                    'prezzo_medio_in_euro' => 'Prezzo medio in euro delle portate dell\'esercizio di ristorazione',
                    'prezzo_medio_in_euro_bambini' => 'Prezzo medio (in euro) del menu per bambini offerto dal locale',
                    'servizi_offerti' => 'Servizi disponibile nell\'esercizio di ristorazione',
                    'menu' => 'Menù disponibili nell\'esercizio di ristorazione',
                    'specialita' => 'Specialità della casa del locale',
                    'data_inizio_validita' => 'Data a partire dalla quale l\'esercizio di ristorazione è attivo',
                    'data_archiviazione' => 'Data di archiviazione dell\'esercizio di ristorazione',
                    'altri_menu_particolari' => 'Menù particolari offerti dal locale',
                    'servizi_famiglie_con_bambini' => 'Servizi per famiglie con bambini offerti dal locale',
                    'offerte_famiglie' => 'Riduzioni/offerte per famiglie previste dal locale',
                    'servizi_diversamente_abili' => 'Servizi per diversamente abili previsti dal locale',
                    'carte_credito' => 'Carte di credito accettate nell\'esercizio di ristorazione',
                    'ticket' => 'Ticket accettati nel locale',
                    'convenzioni' => 'Convenzioni particolari previste dall\'esercizio di ristorazione',
                    'note' => 'Ulteriori informazioni sul locale',
                    'chiave' => 'Concetti più significativi riguardanti il contenuto',
                    'posizione' => 'Posizione dell\'esercizio di ristorazione',
                    'ID' => 'Codice identificativo unico dell\'esercizio di ristorazione',
                ),
            'event' =>
                array(
                    'titolo' => 'Nome dell\'evento',
                    'short_title' => 'Titolo sintetico dell\'evento',
                    'image' => 'Immagine principale dell\'evento',
                    'text' => 'Descrizione completa dell\'evento',
                    'file' => 'Locandina/manifesto',
                    'from_time' => 'Data e ora di inizio dell\'evento',
                    'to_time' => 'Data e ora di termine dell\'evento',
                    'durata' => 'Durata dell\'evento',
                    'periodo_svolgimento' => 'Periodo in cui si svolge l\'evento',
                    'luogo_svolgimento' => 'Descrizione del luogo dell\'evento',
                    'informazioni' => 'Informazioni generali sull\'evento',
                    'destinatari' => 'Target utenti dell\'evento',
                    'costi' => 'Costi dell\'evento',
                    'stato' => '',
                    'materia' => 'Concetti più significativi riguardanti il contenuto',
                    'circoscrizione' => '',
                    'argomento' => 'Categoria in cui rientra l\'evento',
                    'tipo_evento' => 'Tipologia di evento (evento singolo o manifestazione)',
                    'iniziativa' => 'Evento o manifestazione correlata',
                    'associazione' => 'Associazione/i che organizzano e promuovono l\'evento',
                    'progressivo' => '',
                    'abstract' => 'Sottotitolo dell\'evento',
                    'orario_svolgimento' => 'Orario in cui si svolge l\'evento',
                    'geo' => 'Georeferenziazione del luogo in cui si svolge l\'evento',
                    'fonte' => 'Fonte delle informazioni riportate',
                    'ID' => 'Codice identificativo unico dell\'evento',
                ),
            'file_pdf' =>
                array(
                    'name' => 'Titolo del file',
                    'file' => 'File in formato pdf',
                    'ID' => 'Codice identificativo unico del file',
                ),
            'gemellaggio' =>
                array(
                    'titolo' => 'Nome del gemellaggio',
                    'titolo_breve' => 'Titolo sintetico del gemellaggio',
                    'abstract' => 'breve descrizione del gemellaggio',
                    'descrizione' => 'Descrizione completa del gemellaggio',
                    'image' => 'Immagine principale del gemellaggio',
                    'circoscrizione' => 'Organo politico di riferimento per il gemellaggio',
                    'url' => 'Sito internet del Comune con cui è attivo il gemellaggio',
                    'email' => 'Indirizzo di posta elettronica di riferimento per il gemellaggio',
                    'testo_protocollo_intesa' => 'Testo completo del protocollo di intesa del gemellaggio',
                    'testo_documento_amicizia' => 'Testo completo del documento di amicizia del gemellaggio',
                    'programma_gemellaggio' => 'Calendario delle attività previste riguardanti il gemellaggio',
                    'ID' => 'Codice identificativo unico del gemellaggio',
                ),
            'graduatoria' =>
                array(
                    'titolo' => 'Titolo della graduatoria',
                    'servizio' => 'Servizio che si occupa della pubblicazione della graduatoria',
                    'short_description' => 'Breve descrizione della graduatoria',
                    'ufficio' => 'Ufficio che si occupa della pubblicazione della graduatoria',
                    'argomento' => 'Materia della graduatoria',
                    'file' => 'File della graduatoria',
                    'data_inizio_validita' => 'Data a partire dalla quale la graduatoria è valida',
                    'data_fine_validita' => 'Data di scadenza della graduatoria',
                    'data_iniziopubblicazione' => 'Data in cui è pubblicata la graduatoria',
                    'data_archiviazione' => 'Data in cui la graduatoria è archiviata',
                    'link' => 'Collegamento ipertestuale alla graduatoria',
                    'ID' => 'Codice identificativo unico della graduatoria',
                ),
            'gruppo_consiliare' =>
                array(
                    'titolo' => 'Nome del gruppo consiliare',
                    'descrizione' => 'Descrizione completa del Gruppo consiliare',
                    'contatti' => 'Numero di telefono, fax, indirizzo e-mail del Gruppo consiliare',
                    'image' => 'Simbolo del Gruppo consiliare',
                    'capogruppo' => 'Nominativo del politico a capo del Gruppo consiliare',
                    'vicecapogruppo' => 'Nominativo del politico nel ruolo di vicecapogruppo',
                    'ID' => 'Codice identificativo unico del Gruppo consiliare',
                ),
            'image' =>
                array(
                    'name' => 'Titolo dell\'immagine',
                    'caption' => 'Scritta esplicativa dell\'imnmagine situata al disotto della figura',
                    'image' => 'File dell\'immagine',
                    'tags' => 'Parole chiave riguardanti l\'immagine',
                    'ID' => 'Codice identificativo unico dell\'immagine',
                ),
            'iniziativa' =>
                array(
                    'titolo' => 'Nome dell\'iniziativa',
                    'abstract' => 'Breve descrizione dell\'iniziativa',
                    '' => '',
                    'descrizione' => 'Descrizione completa dell\'iniziativa',
                    'image' => 'Immagine principale dell\'iniziativa',
                    'servizio' => 'Servizio di competenza dell\'iniziativa',
                    'ufficio' => 'Ufficio di competenza dell\'iniziativa',
                    'argomento' => 'Materia dell\'iniziativa',
                    'data' => 'Data in cui di svolge l\'iniziativa',
                    'data_iniziopubblicazione' => 'Data in cui l\'iniziativa è pubblicata',
                    'data_archiviazione' => 'Data in cui cessa la pubblicazione dell\'iniziativa',
                    'url' => 'Collegamento al portale esterno dedicato all\'iniziativa',
                    'file' => 'Documento relativo all\'iniziativa',
                    'documento' => 'Oggetti correlati all\'iniziativa',
                    'evento_vita' => 'Evento della vita collegato all\'iniziativa',
                    'geo' => 'Georeferenziazione del luogo in cui si svolge l\'iniziativa',
                    'periodo_svolgimento' => 'Periodo in cui si svolge l\'iniziativa',
                    'luogo_svolgimento' => 'Luogo in cui si svolge l\'iniziativa',
                    'email' => 'Indirizzo di posta elettronica di riferimento dell\'iniziativa',
                    'telefono' => 'Numero di telefono di chi organizza l\'iniziativa',
                    'ID' => 'Codice identificativo unico dell\'iniziativa',
                ),
            'interpellanza' =>
                array(
                    'oggetto' => 'Oggetto dell\'interpellanze',
                    'numero' => 'Numero dell\'interpellanza',
                    'tipo_risposta' => 'Tipologia di risposta dell\'interpellanza',
                    'interroganti' => 'Politico che effettua l\'interpellanza',
                    'gruppo_politico' => 'Gruppo consiliare di cui fa parte il politico che effettua l\'interpellanza',
                    'legislatura' => 'Legislatura in cui avviene l\'interpellanza',
                    'anno_protocollo' => 'Anno di protocollo dell\'interpellanza',
                    'data_protocollo' => 'Data in cui l\'interpellanza è protocollata',
                    'data_invio_uffici' => 'Data in cui l\'interpellanza viene inviata agli uffici',
                    'data_giunta' => 'Data di arrivo dell\'interpellanza in Giunta',
                    'data_risposta_consigliere' => 'Data in cui il consigliere risponde all\'interpellanza',
                    'giorni_interrogazione' => 'Numero di giorni necessari per rispondere all\'interpellanza',
                    'data_consiglio' => 'Data in cui l\'interpellanza è discussa in Consiglio',
                    'note' => 'Annotazioni riguardanti l\'interpellanza',
                    'giorni_adozione' => 'Numero di giorni necessari affinché l\'interpellanza sia adottata',
                    'testo' => 'Atto dell\'interpellanza',
                    'risposta' => 'Atto della risposta dell\'interpellanza',
                    'pdf_allegati' => 'Oggetti allegati all\'interpellanza',
                    'soggetti' => 'Soggetti che hanno proposto l\'interpellanza',
                    'ID' => 'Codice identificativo unico delliinterpellanza',
                ),
            'interrogazione' =>
                array(
                    'oggetto' => 'Oggetto dell\'interrogazione',
                    'numero' => 'Numero dell\'interrogazione',
                    'tipo_risposta' => 'Tipologia di risposta dell\'interrogazione',
                    'interroganti' => 'Politico che effettua l\'interrogazione',
                    'gruppo_politico' => 'Gruppo consiliare di cui fa parte il politico che effettua l\'interrogazione',
                    'legislatura' => 'Legislatura in cui avviene l\'interrogazione',
                    'anno_protocollo' => 'Anno di protocollo dell\'interrogazione',
                    'data_protocollo' => 'Data in cui l\'interrogazione è protocollata',
                    'data_invio_uffici' => 'Data in cui l\'interrogazione è stata inviata agli uffici',
                    'data_giunta' => 'Data di arrivo dell\'interrogazione in Giunta',
                    'data_risposta_consigliere' => 'Data in cui il consigliere riceve la risposta all\'interrogazione',
                    'giorni_interrogazione' => 'Numero di giorni necessari per rispondere all\'interrogazione',
                    'data_consiglio' => 'Data in cui viene data risposta all\'interrogazione in Consiglio',
                    'note' => 'Annotazioni riguardanti l\'interrogazione',
                    'giorni_adozione' => 'Numero di giorni necessari affinché l\'interrogazione sia adottata',
                    'testo' => 'Atto dell\'interrogazione',
                    'risposta' => 'Atto della risposta dell\'interrogazione',
                    'soggetti' => 'Soggetti che hanno proposto l\'interrogazione',
                    'pdf_allegati' => 'Oggetti allegati all\'interrogazione',
                    'ID' => 'Codice identificativo unico dell\'interrogazione',
                ),
            'itinerario' =>
                array(
                    'titolo' => 'Nome dell\'itinerario',
                    'sottotitolo' => 'Descrizione sintetica dell\'itinerario',
                    'image' => 'Immagine principale dell\'itinerario',
                    'steps' => 'Punti di interesse visitabili percorrendo l\'itinerario',
                    'lunghezza' => 'Lunghezza del percorso espressa in metri',
                    'durata' => 'Tempo di percorrenza dell\'itinerario espresso in ore',
                    'difficolta' => 'Livello di difficoltà del percorso',
                    'info' => 'Informazioni utili sull\'itinerario',
                    'descrizione' => 'Descrizione completa dell\'itinerario',
                    'ID' => 'Codice identificativo unico dell\'itinerario',
                ),
            'link' =>
                array(
                    'name' => 'Nome del link',
                    'short_name' => 'Nome sintetico del link',
                    'abstract' => 'Breve descrizione del link',
                    'location' => 'Indirizzo del sito web a cui rimanda il link',
                    'descrizione' => 'Descrizione completa del link',
                    'image' => 'Immagine principale del link',
                    'ID' => 'Codice identificativo unico del link',
                ),
            'modulo' =>
                array(
                    'titolo' => 'Titolo del modulo',
                    'abstract' => 'Breve descrizione del modulo',
                    'descrizione' => 'Testo completo del modulo',
                    'codice' => 'Codice identificativo del modulo',
                    'servizio' => 'Servizio che si occupa della pratica a cui si riferisce il modulo',
                    'ufficio' => 'Ufficio che si occupa della pratica a cui si riferisce il modulo',
                    'argomento' => 'Materia del modulo',
                    'data' => 'Data del modulo',
                    'file' => 'File del modulo',
                    'link' => 'Collegamento ipertestuale al modulo',
                    'parola_chiave' => 'Concetti più significativi riguardanti il contenuto del modulo',
                    'riferimento' => 'Oggetti correlati con il modulo',
                    'riferimenti_normativi' => 'Normativa di riferimento per il modulo',
                    'ID' => 'Codice identificativo unico del modulo',
                ),
            'mozione' =>
                array(
                    'oggetto' => 'Oggetto della mozione',
                    'numero' => 'Numero della mozione',
                    'tipo_risposta' => 'Tipologia di risposta della mozione',
                    'interroganti' => 'Politico che effettua la mozione',
                    'gruppo_politico' => 'Gruppo consiliare di cui fa parte il politico che presenta la mozione',
                    'legislatura' => 'Legislatura in cui avviene la mozione',
                    'anno_protocollo' => 'Anno di protocollo della mozione',
                    'data_protocollo' => 'Data in cui la mozione è protocollata',
                    'data_invio_uffici' => 'Data in cui la mozione è stata inviata agli uffici',
                    'data_giunta' => 'Data di arrivo della mozione in Giunta',
                    'data_risposta_consigliere' => 'Data in cui il consigliere riceve risposta alla mozione',
                    'giorni_interrogazione' => 'Numero di giorni necessari per rispondere alla mozione',
                    'data_consiglio' => 'Data in cui la mozione viene discussa in Consiglio',
                    'note' => 'Risultato della mozione',
                    'documento' => 'Documento collegato all\'esito della mozione',
                    'giorni_adozione' => 'Numero di giorni necessari affinché la mozione sia adottata',
                    'testo' => 'Atto della mozione',
                    'risposta' => 'Atto della risposta della mozione',
                    'pdf_allegati' => 'Oggetti allegati alla mozione',
                    'soggetti' => 'Soggetti che hanno proposto la mozione',
                    'note_aggiuntive' => 'Annotazioni riguardanti la mozione',
                    'ID' => 'Codice identificativo unico della mozione',
                ),
            'nota_trasparenza' =>
                array(
                    'titolo' => 'Titolo della nota sulla trasparenza',
                    'testo_nota' => 'Testo della nota sulla trasparenza',
                    'ID' => 'Codice identificativo unico della nota sulla trasparenza',
                ),
            'ordinanza' =>
                array(
                    'oggetto' => 'Oggetto dell\'ordinanza',
                    'numero' => 'Numero dell\'ordinanza',
                    'anno' => 'Anno dell\'ordinanza',
                    'file' => 'Atto dell\'ordinanza',
                    'competenza' => 'Area di competenza dell\'ordinanza',
                    'data' => 'Data dell\'ordinanza',
                    'data_iniziopubblicazione' => 'Data in cui l\'ordinanza è pubblicata',
                    'data_finepubblicazione' => 'Data in cui cessa la pubblicazione dell\'ordinanza',
                    'data_archiviazione' => 'Data in cui l\'ordinanza è archiviata',
                    'servizio' => 'Servizio comunale che presenta l\'ordinanza',
                    'urgenza' => 'Ordinanza emanata in deroga alla legislazione vigente',
                    'motivo_non_pubblicazione' => 'Ragione della mancata pubblicazione dell\'ordinanza',
                    'numero_protocollo' => 'Numero di protocollo dell\'ordinanza',
                    'anno_protocollo' => 'Anno in cui l\'ordinanza è protocollata',
                    'allegati' => 'Oggetti allegati all\'ordinanza',
                    'ID' => 'Codice identificativo unico dell\'ordinanza',
                ),
            'organigramma' =>
                array(
                    'name' => 'Nome dell\'organigramma',
                    'short_name' => 'Nome breve dell\'organigramma',
                    'abstract' => 'Descrizione breve dell\'\'organigramma',
                    'description' => 'Descrizione completa dell\'organigramma',
                    'image' => 'Immagine principale dell\'organigramma',
                    'ID' => 'Codice identificativo unico dell\'organigramma',
                ),
            'organo_politico' =>
                array(
                    'titolo' => 'Nome dell\'organo politico',
                    'abstract' => 'Sintetica descrizione dell\'organo politico',
                    'descrizione' => 'Descrizione completa dell\'organo politico',
                    'competenze' => 'Elenco/descrizione delle competenze assegnate all\'organo politico',
                    'image' => 'Immagine principale dell\'organo politico',
                    'presidente' => 'Presidente dell\'organo politico',
                    'vicepresidente' => 'Vicepresidente dell\'organo politico',
                    'membri' => 'Soggetti che compongono l\'organo politico',
                    'atto_nomina' => 'Atto della nomina dell\'organo politico',
                    'data_iniziomandato' => 'Data in cui è iniziato il mandato dell\'organo politico',
                    'data_finemandato' => 'Data in cui cesserà il mandato dell\'organo politico',
                    'contatti' => 'Modalità di ricevimento dell\'organo politico: contatti e orari',
                    'struttura' => 'Ufficio di riferimento dell\'organo politco',
                    'tipo_commissione' => 'Tipologia dei commissione',
                    'segretario' => 'Segretario dell\'organo politico',
                    'curriculum' => 'Curriculum vitae dell\'organo',
                    'documento_istitutivo' => 'Atto di istituzione dell\'organo politico',
                    'presidente_testo' => 'Nome del Presidente dell\'organo politico',
                    'vicepresidente_testo' => 'Nome del Vice presidente dell\'organo politico',
                    'data_archiviazione' => 'Data in cui l\'organo politico è archiviato',
                    'ID' => 'Codice identificativo unico dell\'organo politico',
                ),
            'parere' =>
                array(
                    'title' => 'Titolo del parere',
                    'abstract' => 'Breve descrizione del parere',
                    'description' => 'Descrizione completa del parere',
                    'file' => 'File del parere',
                    'area' => 'Area di riferimento per il parere',
                    'firma' => 'Sottoscrizione di chi ha espresso il parere',
                    'ente' => 'Nome dell\'ente che ha richiesto il parere',
                    'ID' => 'Codice identificativo unico del parere',
                ),
            'piano_progetto' =>
                array(
                    'titolo' => 'Titolo del piano/progetto',
                    'abstract' => 'Breve descrizione del piano/progetto',
                    'descrizione' => 'Testo completo del piano/progetto',
                    'image' => 'Immagine principale del piano/progetto',
                    'file' => 'Documento del piano/progetto',
                    'servizio' => 'Servizio che si occupa del piano/progetto',
                    'ufficio' => 'Ufficio che si occupa del piano/progetto',
                    'argomento' => 'Materia del piano/progetto',
                    'data_inizio_validita' => 'Data a partire dalla quale il piano/progetto è valido',
                    'data_fine_validita' => 'Data di scadenza del piano/progetto',
                    'data_archiviazione' => 'Data in cui il piano/progetto è archiviato',
                    'documento' => 'Oggetti correlati con il piano/progetto',
                    'parola_chiave' => 'Concetti più significativi riguardanti il contenuto del piano/progetto',
                    'evento_vita' => 'Evento della vita connesso con il piano/progetto',
                    'ID' => 'Codice identificativo unico del piano/progetto',
                ),
            'politico' =>
                array(
                    'cognome' => 'Cognome del politico',
                    'nome' => 'Nome del politico',
                    'userid' => 'Credenziali di accesso  del politico per accedere alle aree riservate',
                    'ruolo' => 'Ruolo assegnato al politico',
                    'abstract' => 'Breve descrizione del politico',
                    'descrizione' => 'Descrizione completa del politico',
                    'competenze' => 'Elenco/descrizione delle competenze assegnate al politico',
                    'ricevimento' => 'Modalità di ricevimento del politico: contatti e orari',
                    'ruolo2' => 'Altri ruoli ricoperti dal politico',
                    'decorrenza_carica' => 'Decorrenza della carica del politico',
                    'email' => 'Indirizzo di posta elettronica del politico',
                    'curriculum' => 'Curriculum vitae del politico',
                    'voti' => 'Numero di voti ricevuti dal politico',
                    'image' => 'Fotografia del politico',
                    'nota' => 'Annotazioni riguardanti il politico',
                    'compensi' => 'Compensi di qualsiasi natura connessi all\'assunzione della carica del politico',
                    'importi' => 'Importi e i viaggi di servizio e missioni del politico pagati con fondi pubblici',
                    'assunzione_cariche' => 'Dati relativi all\'assunzione di altre cariche, presso enti pubblici o privati, ed i relativi compensi a qualsiasi titolo corrisposti al politico',
                    'eventuali_incarichi' => 'Altri eventuali incarichi con oneri a carico della finanza pubblica e l\'indicazione dei compensi spettanti al politico',
                    'atto_nomina' => 'Atto di nomina del politico',
                    'lista_elettorale' => 'Lista elettorale di cui fa parte il politico',
                    'gruppo_politico' => 'Gruppo politico di cui fa parte il politico',
                    'maggioranza_minoranza' => 'Parte del Consiglio comunale a cui appartiene il politico',
                    'servizio' => 'Servizio di riferimento per l\'attività svolta dal politico',
                    'data_iniziomandato' => 'Data in cui ha inizio il mandato del politico',
                    'data_finemandato' => 'Data in cui cessa il mandato del politico',
                    'ID' => 'Codice identificativo unico del politico',
                ),
            'prenotazione_sala' =>
                array(
                    'text' => 'Motivo della prenotazione della sala',
                    'from_time' => 'Data e ora di inizio della prenotazione',
                    'to_time' => 'Data e ora in cui finisce la prenotazione',
                    'stuff' => 'Servizi disponibili presso la sala di cui si usufruisce durante la prenotazione',
                    'destinatari' => 'Soggetti che utilizzano la sala',
                    'associazione' => 'Associazione che prenota la sala',
                    'sala' => 'Sala prenotata',
                    'servizio' => 'Prezzo per ogni ora di utilizzo della sala pubblica',
                    'order_id' => 'Codice identificativo dell\'ordine',
                    'ID' => 'Codice identificativo unico della prenotazione',
                ),
            'protocollo' =>
                array(
                    'titolo' => 'Titolo del protocollo',
                    'abstract' => 'Breve descrizione del protocollo',
                    'descrizione' => 'Descrizione completa del protocollo',
                    'servizio' => 'Servizio che si occupa del protocollo',
                    'file' => 'File del protocollo',
                    'argomento' => 'Materia del protocollo',
                    'numero_protocollo' => 'Numero di protocollo',
                    'anno_protocollo' => 'Anno di protocollo',
                    'data_inizio_validita' => 'Data a partire dalla quale il protocollo è valido',
                    'data_fine_validita' => 'Data di scadenza del protocollo',
                    'data_iniziopubblicazione' => 'Data in cui il protocollo è pubblicato',
                    'data_finepubblicazione' => 'Data in cui cessa la pubblicazione del protocollo',
                    'documento' => 'Documento correlato al protocollo',
                    'parola_chiave' => 'Concetti più significativi riguardanti il contenuto del protocollo',
                    'ID' => 'Codice identificativo unico del protocollo',
                ),
            'pubblicazione' =>
                array(
                    'titolo' => 'Titolo della pubblicazione',
                    'abstract' => 'Breve descrizione della pubblicazione',
                    'descrizione' => 'Descrizione completa della pubblicazione',
                    'anno' => 'Anno della pubblicazione',
                    'file' => 'File della pubblicazione',
                    'image' => 'Immagine principale della pubblicazione',
                    'servizio' => 'Servizio che si occupa della pubblicazione',
                    'ufficio' => 'Ufficio che si occupa della pubblicazione',
                    'argomento' => 'Materia della pubblicazione',
                    'data_inizio_validita' => 'Data a partire dalla quale la pubblicazione è valida',
                    'data_fine_validita' => 'Data di scadenza della pubblicazione',
                    'data_iniziopubblicazione' => 'Data in cui la pubblicazione è resa pubblica',
                    'data_archiviazione' => 'Data in cui la pubblicazione è archiviata',
                    'circoscrizione' => 'Organo politico di riferimento per la pubblicazione',
                    'ID' => 'Codice identificativo unico della pubblicazione',
                ),
            'luogo' =>
                array(
                    'title' => 'Titolo del punto di interesse',
                    'abstract' => 'Breve descrizione del punto di interesse',
                    'descrizione' => 'Descrizione completa del punto di interesse',
                    'tipo_luogo' => 'Categoria di punto di interesse',
                    'geo' => 'Georeferenziazione del punto di interesse',
                    'image' => 'Immagine principale del punto di interesse',
                    'comune' => 'Comune in cui si trova il punto di interesse',
                    'tags' => 'Concetti più significativi riguardanti il contenuto del punto di interesse',
                    'fonte' => 'Fonte delle informazioni riportate',
                    'indirizzo' => 'Indirizzo del punto di interesse',
                    'url' => 'Indirizzo web con informazioni riguardanti il punto di interesse/ Portale web ufficiale dedicato al punto di interesse',
                    'email' => 'Indirizzo di posta elettronica per ricevere informazioni sul punto di interesse',
                    'telefono' => 'Numero di telefono del punto di interesse',
                    'galleria' => 'Galleria fotografica dedicata al punto di interesse',
                    'ID' => 'Codice identificativo unico del punto di interesse',
                ),
            'regolamento' =>
                array(
                    'titolo' => 'Titolo del regolamento',
                    'abstract' => 'Breve descrizione del regolamento',
                    'file' => 'Documento del regolamento',
                    'area' => 'Area che si occupa del regolamento',
                    'data_iniziopubblicazione' => 'Data in cui il regolamento è pubblicato',
                    'data_archiviazione' => 'Data in cui il regolamento è archiviato',
                    'codice' => 'Codice identificativo del regolamento',
                    'servizio' => 'Servizi che si occupano del regolamento',
                    'argomento' => 'Materia del regolamento',
                    'data_inizio_validita' => 'Data a partire dalla quale il regolamento è valido',
                    'image' => 'Immagine principale del regolamento',
                    'data_fine_validita' => 'Data di scadenza del regolamento',
                    'iter_approvazione' => 'Informazioni riguardanti il procedimento di approvazione del regolamento',
                    'documento' => 'Documenti in relazione con il regolamento',
                    'parola_chiave' => 'Concetti più significativi riguardanti il contenuto del regolamento',
                    'ID' => 'Codice identificativo unico del regolamento',
                ),
            'rendiconto' =>
                array(
                    'anno' => 'Anno del rendiconto',
                    'abstract' => 'Breve descrizione del rendiconto',
                    'file' => 'File del rendiconto',
                    'data_iniziopubblicazione' => 'Data in cui il rendiconto è reso pubblico',
                    'data_archiviazione' => 'Data in cui il rendiconto è archiviato',
                    'servizio' => 'Servizio di competenza del rendiconto',
                    'argomento' => 'Materia del rendiconto',
                    'relazione_illustrativa' => 'Relazione illustrativa del rendiconto',
                    'image' => 'Immagine principale del rendiconto',
                    'nota' => 'Annotazione sul rendiconto',
                    'ID' => 'Codice identificativo unico del rendiconto',
                ),
            'sala_pubblica' =>
                array(
                    'titolo' => 'Nome della sala pubblica',
                    'abstract' => 'Breve descrizione della sala pubblica',
                    'indirizzo' => 'Indirizzo della sala pubblica',
                    'localita2' => 'Località in cui si trova la sala pubblica',
                    'localita' => 'Città in cui si trova la sala pubblica',
                    'cap' => 'Codice di avviamento postale del luogo in cui si trova la sala pubblica',
                    'codice_stradario' => 'Codice stradario del luogo in cui si trova la sala pubblica',
                    'referente' => 'Soggetto a cui bisogna rivolgersi per ricevere informazioni o prenotare la sala pubblica',
                    'telefono' => 'Numero di telefono a cui rivolgersi per informazioni o per la prenotazione della sala pubblica',
                    'fax' => 'numero di fax di riferimento per la sala pubblica',
                    'email' => 'Indirizzo di posta elettronica per ricevere informazioni sulla sala pubblica',
                    'destinazione_uso' => 'Tipo di utilizzo della sala pubblica',
                    'image' => 'Immagine principale della sala pubblica',
                    'descrizione' => 'Descrizione completa della sala pubblica con foto',
                    'numero_posti' => 'Numero di posti disponibili nella sala pubblica',
                    'disponibilita_posti' => 'Range di posti disponibili presso la sala pubblica',
                    'dimensione' => 'Grandezza della sala pubblica espressa in metri quadri',
                    'dimensione_nota' => 'Annotazione sulla grandezza della sala',
                    'palco' => 'Informazioni riguardanti il palco della sala pubblica',
                    'dotazioni_tecniche' => 'Attrezzatura a disposizione presso la sala pubblica',
                    'servizi' => 'Servizi disponibili presso la sala pubblica (ad esempio videoproiettore, maxischermo, cucina)',
                    'circoscrizione_testo' => 'Organo politico di riferimento per la sala pubblica',
                    'circoscrizione' => 'Organo politico di riferimento per la sala pubblica',
                    'periodo_utilizzo' => 'Periodo durante il quale è possibile richiedere la sala pubblica',
                    'periodo_chiusura' => 'Periodo durante il quale la sala pubblica rimane chiusa',
                    'vincoli' => 'Obblighi e limiti per l\'utilizzo della sala pubblica',
                    'modalita_richiesta' => 'Come richiedere la sala pubblica',
                    'costi' => 'Tariffe della sala pubblica',
                    'altre_info' => 'Ulteriori informazioni sulla sala pubblica',
                    'gps' => 'Georeferenziazione della sala pubblica',
                    'price' => 'Prezzo della sala pubblica per ogni ora di utilizzo',
                    'manual_price' => 'Possibilità di scegliere di impostare il prezzo a mano',
                    'reservation_manager' => 'Responsabili dell prenotazioni della sala pubblica effettuate online',
                    'ID' => 'Codice identificativo unico della sala pubblica',
                ),
            'procedimento' =>
                array(
                    'titolo' => 'Titolo del procedimento',
                    'abstract' => 'Breve descrizione del procedimento',
                    'image' => 'Immagine principale del procedimento',
                    'numero' => 'Numero del procedimento',
                    'termine' => 'Scadenza del procedimento',
                    'decorrenza' => '',
                    'data_iniziopubblicazione' => 'Data in cui il procedimento è reso pubblico',
                    'servizio' => 'Servizio coinvolto nel procedimento',
                    'ufficio' => 'Ufficio coinvolto nel procedimento',
                    'struttura' => 'Struttura coinvolto nel procedimento',
                    'ambito' => 'Tema del procedimento',
                    'argomento' => 'Materia del procedimento',
                    'io_sono' => 'Soggetti per cui il procedimento è utile',
                    'macroevento_vita' => 'Attività per cui il procedimento è utile',
                    'evento_vita' => 'Attività specifiche per cui il procedimento è utile',
                    'descrizione' => 'Descrizione completa del procedimento',
                    'richiedente' => 'Soggetti che possono attivare il procedimento',
                    'dove_rivolgersi_testuale' => 'Ufficio che si occupa del procedimento',
                    'numero_telefono_specifico' => 'Numero di telefono da chiamare per ulteriori chiarimenti riguardanti il procedimento',
                    'costo' => 'Costo del procedimento',
                    'tempi_attesa' => 'Tempistiche per lo svolgimento del processo da parte dell\'ufficio di riferimento',
                    'come_fare_cosa_fare' => 'Spiegazione di ciò che deve fare il soggetto richiedente del procedimento',
                    'note' => 'Annotazioni riguardanti il procedimento',
                    'riferimenti_normativi' => 'Norme che regolano il procedimento',
                    'descrizione_aggiuntiva' => 'Ulteriore descrizione del procedimento',
                    'modulistica' => 'Moduli del procedimento',
                    'informazioni_modulistica_collegata' => 'Informazioni relative ai moduli del procedimento',
                    'parola_chiave' => 'Concetti più significativi riguardanti il contenuto del procedimento',
                    'riferimento' => 'Oggetti correlati con il procedimento',
                    'gps' => 'Georeferenziazione del procedimento',
                    'ID' => 'Codice identificativo unico del procedimento',
                ),
            'seduta_consiglio' =>
                array(
                    'data_convocazione' => 'Data di convocazione della seduta del Consiglio',
                    'data' => 'Data in cui si svolge la seduta del Consiglio',
                    'ora' => 'Ora in cui si svolge la seduta del Consiglio',
                    'abstract' => 'Breve descrizione della seduta del Consiglio che indica orario e luogo di svolgimento',
                    'testo' => 'Atto contenente l\'ordine del giorno della seduta',
                    'descrizione' => 'Descrizione dell\'ordine del giorno',
                    'prosecuzioni' => 'Descrizione delle prosecuzioni della seduta',
                    'riferimento' => 'Prosecuzioni della seduta del Consiglio in oggetto',
                    'verbale' => 'Atto del verbale della seduta',
                    'organo_seduta' => 'Organo politico a cui si riferisce la seduta del Consiglio',
                    'circoscrizione' => 'Organo politico di riferimento della seduta',
                    'presenti' => 'Politici presenti alla seduta',
                    'mozioni' => 'Mozioni discusse durante la seduta',
                    'interrogazioni' => 'Interrogazioni discusse durante la seduta',
                    'interpellanze' => 'Interpellanze discusse durante la seduta',
                    'mp3' => 'Registrazione audio della seduta',
                    'codice_video' => 'Link al video della seduta pubblicato nel sistema WebTV del Consorzio Comuni',
                    'video_index' => 'Indice video dell\'ordine del giorno',
                    'ID' => 'Codice identificativo unico della seduta del Consiglio',
                ),
            'servizio' =>
                array(
                    'titolo' => 'Nome del Servizio',
                    'abstract' => 'Sintetica descrizione del Servizio',
                    'descrizione' => 'Descrizione completa del Servizio',
                    'competenze' => 'Elenco/descrizione dei compiti assegnati al Servizio',
                    'file' => 'Documento che descrive il Servizio',
                    'orario' => 'Orario di apertura al pubblico del Servizio',
                    'image' => 'Immagine principale del Servizio',
                    'riferimenti_utili' => 'Informazioni utili sul Servizio',
                    'struttura_attivita_cessato' => 'Struttura che sostituisce il Servizio',
                    'tipo_struttura' => 'Tipo di struttura per cui è attivo il Servizio',
                    'area' => 'Area di cui fa parte i Sevizio',
                    'contatti' => 'Modalità di ricevimento dell\'organo politico: contatti e orari',
                    'data_inizio_validita' => 'Data in cui è stato attivato il Servizio',
                    'data_fine_validita' => 'Data in cui il Servizio cesserà la sua attività',
                    'responsabile' => 'Dipendente comunale responsabile del Servizio',
                    'responsabile2' => 'Nome del responsabile del Servizio',
                    'telefoni' => 'Numero di telefono del Servizio',
                    'fax' => 'Numero di fax del Servizio',
                    'email' => 'Indirizzo di posta elettronica del Servizio',
                    'sede' => 'Luogo in cui si trova il Servizio',
                    'indirizzo' => 'Indirizzo del Servizio',
                    'cap' => 'Codice di avviamento postale del luogo in cui di trova il Servizio',
                    'gps' => 'Georeferenziazione del Servizio',
                    'ID' => 'Codice identificativo unico del Servizio',
                ),
            'applicativo' =>
                array(
                    'titolo' => 'Nome del servizio online',
                    'abstract' => 'Breve descrizione del servizio online',
                    'descrizione' => 'Descrizione completa del servizio online',
                    'servizio' => 'Servizio di competenza dell\'applicativo',
                    'ufficio' => 'Ufficio di competenza dell\'applicativo',
                    'argomento' => 'Materia/Categoria di servizio online',
                    'location_applicativo' => 'Collegamento all\'applicativo',
                    'file' => 'Documento contenente le istruzioni per l\'utilizzo dell\'applicativo',
                    'image' => 'Immagine principale dell\'applicativo',
                    'data' => 'Data a partire dalla quale è possibile utilizzare il servizio online',
                    'data_archiviazione' => 'Data in cui l\'applicativo è archiviato',
                    'parola_chiave' => 'Concetti più significativi riguardanti il contenuto dell\'applicativo',
                    'ID' => 'Codice identificativo unico del servizio online',
                ),
            'sindaco' =>
                array(
                    'abstract' => 'Breve descrizione del sindaco',
                    'descrizione' => 'Descrizione completa del sindaco',
                    'competenze' => 'Elenco/descrizione delle competenze assegnate al sindaco',
                    'atto_nomina' => 'Atto di nomina del sindaco',
                    'data_iniziomandato' => 'Data in cui ha avuto inizio la carica di sindaco',
                    'data_finemandato' => 'Data in cui finisce il mandato del sindaco',
                    'contatti' => 'Modalità di ricevimento del sindaco: contatti e orari',
                    'curriculum' => 'Curriculum vitae del sindaco',
                    'documento_istitutivo' => 'Documento istitutivo della carica di sindaco',
                    'sindaco' => 'Politico che copre la carica di sindaco',
                    'booking_start' => 'Inizio del periodo di prenotazione appuntamenti del sindaco',
                    'booking_end' => 'Fine del periodo di prenotazione appuntamenti del sindaco',
                    'booking_timetable' => 'Giorni di ricevimento del sindaco',
                    'booking_slot' => 'Tempo mimino di ricevimento del sindaco',
                    'approvers' => 'Utenti che gestiscono l\'agenda del sindaco',
                    'ID' => 'Codice identificativo unico del sindaco',
                ),
            'sovvenzione_contributo' =>
                array(
                    'titolo' => 'Titolo della sovvenzione',
                    'nome_e_cognome_del_beneficiario' => 'Nome e cognome del beneficiario della sovvenzione',
                    'dati_fiscali_del_beneficiario' => 'Dati fiscali del beneficiario della sovvenzione',
                    'importo' => 'Importo della sovvenzione',
                    'norma_o_titolo_alla_base_dell_attribuzione' => 'Norma o titolo alla base dell\'attribuzione della sovvenzione',
                    'responsabile_procedimento' => 'Struttura, dirigente o funzionario responsabile del procedimento amministrativo riguardante la sovvenzione',
                    'modalita_indiv_beneficiario' => 'Modalità seguita per l\'individuazione del beneficiario della sovvenzione',
                    'estremi_del_provvedimento' => 'Estremi della sovvenzione',
                    'provvedimenti' => 'Provvedimento riguardanti la sovvenzione',
                    'data_archiviazione' => 'Data in cui la sovvenzione è archiviata',
                    'ID' => 'Codice identificativo unico della sovvenzione',
                ),
            'statuto' =>
                array(
                    'name' => 'Titolo dello statuto',
                    'abstract' => 'Breve descrizione dello statuto',
                    'descrizione' => 'Testo completo dello statuto',
                    'file' => 'File dello statuto',
                    'image' => 'Immagine dello statuto',
                    'ID' => 'Codice identificativo unico dello statuto',
                ),
            'struttura' =>
                array(
                    'titolo' => 'Nome della struttura',
                    'abstract' => 'Sintetica descrizione della struttura',
                    'descrizione' => 'Descrizione completa della struttura',
                    'competenze' => 'Elenco/descrizione dei compiti assegnati alla struttura',
                    'orario' => 'Orario di apertura al pubblico della struttura',
                    'file' => 'Documento che descrive la struttura',
                    'image' => 'Immagine principale della struttura',
                    'tipo_struttura' => 'Tipo di struttura',
                    'area' => 'Area di cui fa parte la struttura',
                    'servizio' => 'Servizio da cui dipende la struttura',
                    'ufficio' => 'Ufficio da cui dipende la struttura',
                    'struttura' => '',
                    'incarico' => 'Incarico assegnato alla struttura',
                    'circoscrizione' => 'Organo politico di riferimento per la struttura',
                    'telefoni' => 'Numero di telefono della struttura',
                    'fax' => 'Numero di fax della struttura',
                    'email' => 'Indirizzo di posta elettronica della struttura',
                    'email2' => 'Ulteriore indirizzo di posta elettronica della struttura',
                    'email_certificata' => 'Indirizzo di posta elettronica certificata della struttura',
                    'sede' => 'Sede della struttura',
                    'indirizzo' => 'Indirizzo della struttura',
                    'cap' => 'Codice di avviamento postale del luogo in cui di trova la struttura',
                    'id_struttura' => '',
                    'cod_servizio' => '',
                    'data_inizio_validita' => 'Data a partire dalla quale la struttura è attiva',
                    'data_fine_validita' => 'Data di fine attività della struttura',
                    'gps' => 'Georeferenziazione della struttura',
                    'ID' => 'Codice identificativo unico della struttura',
                ),
            'accomodation' =>
                array(
                    'title' => 'Nome della struttura ricettiva',
                    'abstract' => 'Breve descrizione della struttura ricettiva',
                    'description' => 'Descrizione completa della struttura ricettiva',
                    'image' => 'Immagine principale della struttura ricettiva',
                    'indirizzo' => 'Indirizzo della struttura ricettiva',
                    'gps' => 'Georeferenziazione della struttura ricettiva',
                    'telefono' => 'Numero di telefono della struttura ricettiva',
                    'telefono2' => 'Numero di cellulare della struttura ricettiva',
                    'fax' => 'Numero di fax della struttura ricettiva',
                    'email' => 'Indirizzo di posta elettronica della struttura ricettiva',
                    'url' => 'Sito internet della struttura ricettiva',
                    'tipologia_hotel' => 'Tipo di struttura ricettiva',
                    'stars' => 'Stelle della struttura ricettiva',
                    'ID' => 'Codice identificativo unico della struttura ricettiva',
                ),
            'tariffa' =>
                array(
                    'titolo' => 'Titolo della tariffa',
                    'abstract' => 'Breve descrizione della tariffa',
                    'description' => 'Descrizione completa della tariffa',
                    'file' => 'File della tariffa',
                    'file_aliquota' => 'File di determinazione della tariffa',
                    'data_iniziopubblicazione' => 'Data in cui la tariffa è pubblicata',
                    'servizio' => 'Servizio che si occupa della tariffa',
                    'ufficio' => 'Ufficio che si occupa della tariffa',
                    'riferimento' => 'Oggetti correlati con la tariffa',
                    'ID' => 'Codice identificativo unico della tariffa',
                ),
            'tasso_assenza' =>
                array(
                    'titolo' => 'Titolo del tasso di assenza',
                    'servizio' => 'Servizio/Ufficio che si occupa del tasso di assenza',
                    'giorni_lavorativi' => 'Giorni lavorativi',
                    'giorni_assenza' => 'Giorni di assenza',
                    'tasso_assenza' => 'Tasso di assenza',
                    'anno' => 'Anno di riferimento del tasso di assenza',
                    'data_archiviazione' => 'Data in cui il tasso di assenza è archiviato',
                    'ID' => 'Codice identificativo unico del tasso di assenza',
                ),
            'ufficio' =>
                array(
                    'titolo' => 'Nome dell\'Ufficio    ',
                    'abstract' => 'Sintetica descrizione dell\'Ufficio',
                    'competenze' => 'Elenco/descrizione dei compiti assegnati all\'Ufficio',
                    'file' => 'Documento che descrive l\'Ufficio',
                    'riferimenti_utili' => 'Informazioni utili riguardanti l\'Ufficio',
                    'orario' => 'Orario di apertura al pubblico dell\'Ufficio',
                    'image' => 'Immagine principale dell\'Ufficio',
                    'ubicazione' => 'Piantina del luogo dove si trova l\'Ufficio',
                    'tipo_struttura' => 'Tipo di struttura per cui è attivo l\'Ufficio',
                    'area' => 'Area di cui fa parte l\'Ufficio',
                    'servizio' => 'Servizio di cui fa parte l\'Ufficio',
                    'incarico' => 'Incarico ricevuto dall\'Ufficio',
                    'telefoni' => 'Numero di telefono dell\'Ufficio',
                    'fax' => 'Numero di fax dell\'Ufficio',
                    'email' => 'Indirizzo di posta elettronica dell\'Ufficio',
                    'email2' => 'Ulteriore indirizzo di posta elettronica dell\'Ufficio',
                    'email_certificata' => 'Indirizzo di posta elettronica certificata dell\'Ufficio',
                    'sede' => 'Luogo in cui si trova l\'Ufficio',
                    'indirizzo' => 'Indirizzo dell\'Ufficio',
                    'cap' => 'Codice di avviamento postale del luogo in cui di trova l\'Ufficio',
                    'responsabile' => 'Dipendente comunale responsabile dell\'Ufficio',
                    'data_inizio_validita' => 'Data a partire dalla quale l\'Ufficio è attivo',
                    'data_fine_validita' => 'Data in cui l\'Ufficio cesserà la sua attività',
                    'gps' => 'Georeferenziazione dell\'Ufficio',
                    'ID' => 'Codice identificativo unico dell\'Ufficio',
                ),
            'ezflowmedia' =>
                array(
                    'name' => 'Titolo del video',
                    'sottotitolo' => 'Sottotitolo del video',
                    'abstract' => 'Breve descrizione del video',
                    'durata' => 'Lunghezza del video in termini di tempo',
                    'data_iniziopubblicazione' => 'Data in cui il video è pubblicato',
                    'data_archiviazione' => 'Data in cui cessa la pubblicazione del video',
                    'image' => 'Immagineche funge da icona del video',
                    'cover' => 'Immagine di copertina del video',
                    'ezflowmedia' => 'File del video',
                    'descrizione' => 'Descrizione completa del video',
                    'argomento' => 'Materia trattata nel video',
                    'sottotitoli' => 'File contenente i sottotitoli del video',
                    'ID' => 'Codice identificativo unico del video',
                ),
        );

        foreach( $infos as $classIdentifier => $attributes ){
            $class = eZContentClass::fetchByIdentifier( $classIdentifier );
            if ( $class instanceof eZContentClass){
                OpenPALog::error( $classIdentifier );
                /** @var eZContentClassAttribute[] $dataMap */
                $dataMap = $class->dataMap();
                foreach( $attributes as $identifier => $description ){
                    if ( isset( $dataMap[$identifier] ) ){
                        OpenPALog::warning( ' - ' . $identifier);
                        if (!$options['dry-run']) {
                            $dataMap[$identifier]->setDescription( $description );
                            $dataMap[$identifier]->store();
                        }
                    }
                }
            }
        }

    }


    $script->shutdown();
} catch (Exception $e) {
    $errCode = $e->getCode();
    $errCode = $errCode != 0 ? $errCode : 1; // If an error has occured, script must terminate with a status other than 0
    $script->shutdown($errCode, $e->getMessage());
}