<?php

/*
php extension/openpa/bin/php/openpa_report_classes.php -samblar_backend;
php extension/openpa/bin/php/openpa_report_classes.php -sbosentino_backend;
php extension/openpa/bin/php/openpa_report_classes.php -scles_backend;
php extension/openpa/bin/php/openpa_report_classes.php -sconsorzio_backend;
php extension/openpa/bin/php/openpa_report_classes.php -sconsorzioinnovazione_backend;
php extension/openpa/bin/php/openpa_report_classes.php -sdrena_backend;
php extension/openpa/bin/php/openpa_report_classes.php -sdro_backend;
php extension/openpa/bin/php/openpa_report_classes.php -sfiave_backend;
php extension/openpa/bin/php/openpa_report_classes.php -sforestale_backend;
php extension/openpa/bin/php/openpa_report_classes.php -smazzin_backend;
php extension/openpa/bin/php/openpa_report_classes.php -snave_backend;
php extension/openpa/bin/php/openpa_report_classes.php -spredazzo_backend;
php extension/openpa/bin/php/openpa_report_classes.php -sprototipo_backend;
php extension/openpa/bin/php/openpa_report_classes.php -srivadelgarda_backend;
php extension/openpa/bin/php/openpa_report_classes.php -srovereto_backend;
php extension/openpa/bin/php/openpa_report_classes.php -ssoraga_backend;
php extension/openpa/bin/php/openpa_report_classes.php -sstenico_backend;
php extension/openpa/bin/php/openpa_report_classes.php -stransacqua_backend;

*/

require 'autoload.php';

$cli = eZCLI::instance();
$script = eZScript::instance( array( 'description' => ( "OpenPA class report\n\n" .
                                                        "\n" .
                                                        "\n" .
                                                        "\n" .
                                                        "\n" .
                                                        "" ),
                                     'use-session' => false,
                                     'use-modules' => true,
                                     'use-extensions' => true ) );

$script->startup();
$options = $script->getOptions( "[generate-csv]", "", array( "generate-csv" => "genera i file csv" ) );
$script->initialize();

$siteaccess = eZSiteAccess::current();
$SiteName = str_replace( '_backend', '', $siteaccess['name'] );

$user = eZUser::fetchByName( 'admin' );
if ( $user )
{
    eZUser::setCurrentlyLoggedInUser( $user, $user->attribute( 'contentobject_id' ) );
    $cli->notice( "Eseguo lo script da utente {$user->attribute( 'contentobject' )->attribute( 'name' )} in $SiteName" );
}
else
{    
    die( "Non esiste un utente con nome utente admin" ); 
}

$dir = "/home/httpd/openpa.opencontent.it/html/extension/openpa/data/class_report/json/";
$CSVdir = "/home/httpd/openpa.opencontent.it/html/extension/openpa/data/class_report/csv/";
    
$groups = eZContentClassGroup::fetchList( false, true );

$groupIDs = array();
foreach( $groups as $group )
{
    $groupIDs[] = $group->attribute( 'id' );
}

$classes = eZContentClass::fetchAllClasses( false, true, $groupIDs );

if ( $options['generate-csv'] )
{
    foreach ( $classes as $class )
    {
        $class = eZContentClass::fetch( $class['id'] );
        $filename = $class->attribute( 'identifier' ) . '.json';
        $CSVfilename = $class->attribute( 'identifier' ) . '.csv';
        $cli->notice( "Creo $CSVfilename" );
        $json = $dir . $filename;
        $jsonData = file_get_contents( $json );
        if ( $jsonData )
        {
            $data = json_decode( $jsonData, true );
            $headers = array();            
            foreach( $data as $sitename => $values )
            {
                $headers[] = $sitename;
            }            
            $values = array();
            $identifiers = array();
            foreach( $headers as $header )
            {                
                foreach( $data[$header] as $identifier => $type )
                {
                    $identifiers[] = $identifier;
                    $values[$header][$identifier] = "$identifier ($type)";
                }                
            }
            $identifiers = array_unique( $identifiers );
            sort( $headers );
            sort( $identifiers );
            $tocsv = array( $headers );
            foreach( $identifiers as $identifier )
            {
                $item = array();
                foreach( $headers as $header )
                {
                    if ( isset( $values[$header][$identifier] ) )
                    {
                        $item[] = $values[$header][$identifier];
                    }
                    else
                    {
                        $item[] = '';
                    }
                }
                $tocsv[] = $item;
            }            
            eZFile::create( $CSVfilename, $CSVdir, '' );   
            $fp = fopen( $CSVdir . $CSVfilename, 'w' );
            foreach ( $tocsv as $fields )
            {
                fputcsv( $fp, $fields );
            }            
            fclose($fp);            
        }
    }
}
else
{   
    foreach ( $classes as $class )
    {
        $class = eZContentClass::fetch( $class['id'] );
        $filename = $class->attribute( 'identifier' ) . '.json';
        $json = $dir . $filename;
        $jsonData = file_get_contents( $json );
        if ( $jsonData )
        {
            $data = json_decode( $jsonData, true );
        }
        else
        {
            $data = array();
        }
        $attributes = $class->fetchAttributes();
        foreach( $attributes as $attribute )
        {
            $contentAttributes = eZContentObjectAttribute::fetchSameClassAttributeIDList( $attribute->attribute( 'id' ), true );
            $contents = array();
            foreach( $contentAttributes as $contentAttribute )
            {
                if ( $contentAttribute->attribute( 'has_content' ) )
                {
                    $contents[$contentAttribute->attribute( 'id' )] = $contentAttribute->attribute( 'contentobject_id' );
                }
            }
            $data[$SiteName][$attribute->attribute( 'identifier' )] = $attribute->attribute( 'data_type_string' ) . ' ' . count( $contents );
        }
        $data[$SiteName]['_NumeroOggetti'] = $class->objectCount();
        
        eZFile::create( $filename, $dir, json_encode( $data ) );        
    }
}


$script->shutdown();