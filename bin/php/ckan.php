<?php
require 'autoload.php';

$script = eZScript::instance( array( 'description' => ( "OpenPA Controllo Ckan tools\n\n" ),
                                     'use-session' => false,
                                     'use-modules' => true,
                                     'use-extensions' => true ) );

$script->startup();

$options = $script->getOptions(
    '[dry-run][remove_old_dataset][fix_area_remote_ids]',
    '',
    array(
        'dry-run' => 'Non esegue azioni e mostra eventuali errori',
        'remove_old_dataset'  => 'Rimuove i dataset preinstallati nel prototipo (secondo una lista hardcoded)',
        'fix_area_remote_ids' => 'Rende leggibili i remote ids dell\'area opendata e quindi aggiornabili'
    )
);
$script->initialize();
$script->setUseDebugAccumulators( true );

OpenPALog::setOutputLevel( $script->isQuiet() ? OpenPALog::ERROR : OpenPALog::ALL );

try
{
    if ( $options['remove_old_dataset'] ){
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
            "acf1cd37c7e7c442656f6230a96ac27c"
        );

        foreach( $datasetRemoteIdList as $remoteId ){
            $object = eZContentObject::fetchByRemoteID($remoteId);
            if ($object instanceof eZContentObject){
                OpenPALog::warning( "Remove " . $object->attribute( 'name' ) );
                if ( !$options['dry-run'] ){
                    if (!eZContentObjectOperations::remove( $object->attribute('id'), false )){
                        OpenPALog::error( "Problem deleting object #" . $object->attribute('id'));
                    }
                }
            }
        }
    }

    if ( $options['fix_area_remote_ids'] ){
        $matches = array(
            'c62f589eb338057627de6f62d08b48ac' => 'opendata_area',
            '74c52b1af7b47536ee0200c27563b842' => 'opendata_presentazione',
            '8aa799d9883f6ab7d1d1f35346d670cf' => 'opendata_datasetcontainer',
            '03298100280d2e69bffa279ae3ecef54' => 'opendata_amministrazione',
            'fe4b6d6e7aa51736573ec77adc69593c' => 'opendata_iniziativa',
            'a7a7c676012d54d87b5bc6b7551c0df6' => 'opendata_normativa',
            'e62e8239a4b7bfa44c9336822a2e8622' => 'opendata_info',
        );

        foreach( $matches as $old => $new ){
            $object = eZContentObject::fetchByRemoteID($old);
            if ($object instanceof eZContentObject){
                if ( ! eZContentObject::fetchByRemoteID($new) ){
                    OpenPALog::warning( "Fix remote " . $new );
                    if ( !$options['dry-run'] ) {
                        $object->setAttribute('remote_id', $new);
                        $object->setAttribute('modified', time());
                        $object->store();
                    }
                }else{
                    OpenPALog::error( "Remote $new already exists: can not fix $old" );
                }
            }
        }
    }


    $script->shutdown();
}
catch( Exception $e )
{
    $errCode = $e->getCode();
    $errCode = $errCode != 0 ? $errCode : 1; // If an error has occured, script must terminate with a status other than 0
    $script->shutdown( $errCode, $e->getMessage() );
}
