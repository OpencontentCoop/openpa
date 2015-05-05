<?php
require 'autoload.php';

$cli = eZCLI::instance();
$script = eZScript::instance( array( 'description' => ( "OpenPA class report" ),
                                     'use-session' => false,
                                     'use-modules' => true,
                                     'use-extensions' => true ) );

$script->startup();
$options = $script->getOptions( "[simple][generate-csv][class:]", "",
                                array( "simple" => "genera il report solo del numero di contenuti per classe",
                                       "generate-csv" => "genera i file csv",
                                       'class' => "genera il report solo della classe selezionata" ) );
$script->initialize();
try
{
    $siteaccess = eZSiteAccess::current();
    $SiteName = str_replace( '_backend', '', $siteaccess['name'] );
    
    $simple = $options['simple'];
    
    $user = eZUser::fetchByName( 'admin' );
    if ( $user )
    {
        eZUser::setCurrentlyLoggedInUser( $user, $user->attribute( 'contentobject_id' ) );
        //$cli->notice( "Eseguo lo script da utente {$user->attribute( 'contentobject' )->attribute( 'name' )} in $SiteName" );
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
    
    if ( !empty( $options['class'] ) )
    {
        $class = eZContentClass::fetchByIdentifier( $options['class'], false );
        if ( !$class )
        {
            throw new Exception( 'Class ' . $options['class'] . ' not found' );
        }
        $classes = array( $class );
    }
    else
    {
        $classes = eZContentClass::fetchAllClasses( false, true, $groupIDs );
    }
    
    if ( $options['generate-csv'] )
    {
        if ( $simple )
        {
            $filename = 'all_classes.json';
            $CSVfilename = 'all_classes.csv';
            $cli->notice( "Creo $CSVfilename" );
            $json = $dir . $filename;
            $jsonData = file_get_contents( $json );
            if ( $jsonData )
            {
                $data = json_decode( $jsonData, true );
                $headers = array( ' ' );            
                foreach( $data as $sitename => $values )
                {
                    $headers[] = $sitename;
                }                
                $identifiers = array();                
                foreach( $headers as $header )
                {                
                    foreach( $data[$header] as $identifier => $count )
                    {
                        $identifiers[] = $identifier;                        
                    }                
                }
                $identifiers = array_unique( $identifiers );
                sort( $headers );
                sort( $identifiers );
                $tocsv = array( $headers );
                foreach( $identifiers as $identifier )
                {
                    $item = array( $identifier );                    
                    foreach( $headers as $header )
                    {                         
                        if ( $header == ' ' ) continue;                        
                        if ( isset( $data[$header][$identifier] ) )
                        {
                            $item[] = $data[$header][$identifier];
                        }
                        else
                        {                            
                            $item[] = 0;
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
        else
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
    }
    else
    {   
        if ( $simple )
        {
            foreach ( $classes as $class )
            {
                $class = eZContentClass::fetch( $class['id'] );
                $filename = 'all_classes.json';
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
                $data[$SiteName][$class->attribute( 'identifier' )] = $class->objectCount();
                eZFile::create( $filename, $dir, json_encode( $data ) );
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
    }

    $script->shutdown();
}
catch( Exception $e )
{
    $errCode = $e->getCode();
    $errCode = $errCode != 0 ? $errCode : 1; // If an error has occured, script must terminate with a status other than 0
    $script->shutdown( $errCode, $e->getMessage() );
}