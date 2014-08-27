<?php
require 'autoload.php';

$script = eZScript::instance( array( 'description' => ( "OpenPA Cambio password\n\n" ),
                                     'use-session' => false,
                                     'use-modules' => true,
                                     'use-extensions' => true ) );

$script->startup();

$options = $script->getOptions( '[user:][new:][remove_user:]',
                                '',
                                array( 'user'  => 'Id o nome utente o email degli utenti da aggiornare',
                                       'new' => 'Nuova password',
                                       'remove_user' => 'Disabilita l\'utenza (specificare id o nome utente o email)' )
);
$script->initialize();
$script->setUseDebugAccumulators( true );

OpenPALog::setOutputLevel( OpenPALog::ALL );


try
{    
    $output = new ezcConsoleOutput();
    
    $ini = eZINI::instance();
    $userToChange = eZUser::fetch( $options['user'] ) ? eZUser::fetch( $options['user'] ) :
        eZUser::fetchByName( $options['user'] ) ?  eZUser::fetchByName( $options['user'] ) :
        eZUser::fetchByEmail( $options['user'] ) ? eZUser::fetchByEmail( $options['user'] ) : false;

    $newPassword = $options['new'];

    $userToRemove = eZUser::fetch( $options['remove_user'] ) ? eZUser::fetch( $options['remove_user'] ) :
        eZUser::fetchByName( $options['remove_user'] ) ?  eZUser::fetchByName( $options['remove_user'] ) :
        eZUser::fetchByEmail( $options['remove_user'] ) ? eZUser::fetchByEmail( $options['remove_user'] ) : false;    
    
    if ( $userToChange instanceof eZUser )
    {        
        $minPasswordLength = $ini->hasVariable( 'UserSettings', 'MinPasswordLength' ) ? $ini->variable( 'UserSettings', 'MinPasswordLength' ) : 3;
        if ( strlen( $newPassword ) < $minPasswordLength )
        {
            throw new Exception( "Password troppo breve" );
        }
        
        $question = ezcConsoleQuestionDialog::YesNoQuestion(
            $output,
            "Cambio la password per l'utente " . $userToChange->attribute( 'contentobject' )->attribute( 'name' ),
            "y"
        );
        if ( ezcConsoleDialogViewer::displayDialog( $question ) == "y" )
        {
            //eZUserOperationCollection::password( $userToChange->attribute( 'contentobject_id' ), $newPassword );
        }        
    }
    
    if ( $userToRemove instanceof eZUser )
    {
        $question = ezcConsoleQuestionDialog::YesNoQuestion(
            $output,
            "Disabilito l'utente " . $userToRemove->attribute( 'contentobject' )->attribute( 'name' ),
            "y"
        );
        if ( ezcConsoleDialogViewer::displayDialog( $question ) == "y" )
        {
            //eZUserOperationCollection::setSettings( $userToRemove->attribute( 'contentobject_id' ), 0, 0 );
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
