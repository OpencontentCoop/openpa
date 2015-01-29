<?php
require 'autoload.php';

$script = eZScript::instance( array( 'description' => ( "OpenPA Installa schema.sql\n\n" ),
    'use-session' => false,
    'use-modules' => true,
    'use-extensions' => true ) );

$script->startup();

$options = $script->getOptions( '[file:][run]',
    '',
    array(
        'file'  => 'Percorso dello schema.sql',
        'run'  => 'Esegue le query (altrimenti le stampa solamente)',
    )
);
$script->initialize();
$script->setUseDebugAccumulators( true );

OpenPALog::setOutputLevel( OpenPALog::ALL );


/**
 * Import SQL from file
 *
 * @param string path to sql file
 */


try
{
    $schema = $options['file'];
    $db = eZDB::instance();
    eZDB::setErrorHandling( eZDB::ERROR_HANDLING_EXCEPTIONS );
    if ( file_exists( $schema ) )
    {
        if ( $options['run'] )
        {
            OpenPADBTools::insertFromSqlFile( $db, $schema );
        }
        else
        {
            $queries = OpenPADBTools::insertFromSqlFile( $db, $schema, true );
            foreach( $queries as $query )
            {
                OpenPALog::output( $query );
            }
        }
    }
    else
    {
        throw new Exception( "Schema $schema non trovato" );
    }

    $script->shutdown();
}
catch( Exception $e )
{
    $errCode = $e->getCode();
    $errCode = $errCode != 0 ? $errCode : 1; // If an error has occured, script must terminate with a status other than 0
    $script->shutdown( $errCode, $e->getMessage() );
}
