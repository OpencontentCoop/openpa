<?php

$googleId = $instance->getGoogleId();

$output = new ezcConsoleOutput();
$question = ezcConsoleQuestionDialog::YesNoQuestion(
    $output,
    "Google Analytics ID: $googleId",
    "y"
);

if ( ezcConsoleDialogViewer::displayDialog( $question ) == "n" )
{
    $opts = new ezcConsoleQuestionDialogOptions();
    $opts->text = "Inserisci nuovo Google Analytics ID";
    $opts->showResults = true;
    $question = new ezcConsoleQuestionDialog( $output, $opts );
    $googleId = ezcConsoleDialogViewer::displayDialog( $question );

    $confirm = "Salvo nuovo Google Analytics ID '{$googleId}'' in [Seo]GoogleAnalyticsAccountID di {$instance->getSiteAccessBaseName()}_frontend/openpa.ini.append.php ?";
    $question = ezcConsoleQuestionDialog::YesNoQuestion( $output, "$confirm", "y" );

    if ( ezcConsoleDialogViewer::displayDialog( $question ) == "y" )
    {
        //salva i site.ini
        throw new Exception( "Qualcosa non ha funzionato nel salvataggio del file ini. Forse lo script non è finito??" );
    }
    else
    {
        $googleId = $instance->getGoogleId();
        throw new Exception( "Qualcosa non va nella tua capacità decisionale..." );
    }
}
