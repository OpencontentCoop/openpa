<?php
require 'autoload.php';

$script = eZScript::instance( array( 'description' => ( "OpenPA Rimozione gruppo di classi\n\n" ),
                                     'use-session' => false,
                                     'use-modules' => true,
                                     'use-extensions' => true ) );

$script->startup();

$options = $script->getOptions( '[classgroup:]',
                                '',
                                array( 'classgroup'  => 'Identificatore del gruppo di classe da controllare')
);
$script->initialize();
$script->setUseDebugAccumulators( true );

OpenPALog::setOutputLevel( OpenPALog::ALL );


try
{
    $user = eZUser::fetchByName( 'admin' );
    eZUser::setCurrentlyLoggedInUser( $user , $user->attribute( 'contentobject_id' ) );
    
    $siteaccess = eZSiteAccess::current();
    if ( stripos( $siteaccess['name'], 'prototipo' ) !== false )
    {
        throw new Exception( 'Script non eseguibile sul prototipo' );        
    }
    if ( stripos( $siteaccess['name'], 'consorzio' ) !== false )
    {
        throw new Exception( 'Script non eseguibile sui siti del consorzio' );        
    }
    
    if ( isset( $options['classgroup'] ) )
    {
        $classgroup = $options['classgroup'];
        $classGroup = eZContentClassGroup::fetchByName( $classgroup );
        if ( $classGroup )
        {
            $classes = eZContentClassClassGroup::fetchClassList( eZContentClass::VERSION_STATUS_DEFINED, $classGroup->attribute( 'id' ) );
            if ( count( $classes ) == 0 )
            {
                OpenPALog::notice( "Rimuovo " . $classGroup->attribute( 'name' ) );
                eZPersistentObject::removeObject( eZContentClassGroup::definition(), array( "id" => $classGroup->attribute( 'id' ) ) );
            }
            else
            {
                throw new Exception( "Il gruppo {$classgroup} non è vuoto: non posso eliminarlo" );  
            }
        }
        else
        {
            throw new Exception( "Il gruppo {$classgroup} non esiste" );  
        }
    }
    else
    {
        throw new Exception( 'Specifica il gruppo' );   
    }
    
    
    $script->shutdown();
}
catch( Exception $e )
{    
    $errCode = $e->getCode();
    $errCode = $errCode != 0 ? $errCode : 1; // If an error has occured, script must terminate with a status other than 0
    $script->shutdown( $errCode, $e->getMessage() );
}
