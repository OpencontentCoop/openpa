<?php

$productionUrl = $instance->getUrl( OpenPAInstance::PRODUCTION );

$output = new ezcConsoleOutput();
$question = ezcConsoleQuestionDialog::YesNoQuestion(
    $output,
    "Url di produzione: $productionUrl",
    "y"
);

if ( ezcConsoleDialogViewer::displayDialog( $question ) == "n" )
{
    $opts = new ezcConsoleQuestionDialogOptions();
    $opts->text = "Inserisci nuovo url di produzione";
    $opts->showResults = true;
    $question = new ezcConsoleQuestionDialog( $output, $opts );
    $productionUrl = ezcConsoleDialogViewer::displayDialog( $question );

    $confirm = "Salvo nuovo url '{$productionUrl}'' in [SiteSettings]SiteName di {$instance->getSiteAccessBaseName()}_*/site.ini.append.php ?";
    $question = ezcConsoleQuestionDialog::YesNoQuestion( $output, "$confirm", "y" );

    if ( ezcConsoleDialogViewer::displayDialog( $question ) == "y" )
    {
        //salva i site.ini
    }
    else
    {
        $productionUrl = $instance->getUrl( OpenPAInstance::PRODUCTION );
        throw new Exception( "Qualcosa non va nella tua capacità decisionale..." );
    }
}
