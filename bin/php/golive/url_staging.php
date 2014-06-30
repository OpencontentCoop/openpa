<?php

$stagingUrl = $instance->getUrl( OpenPAInstance::STAGING );

$output = new ezcConsoleOutput();
$question = ezcConsoleQuestionDialog::YesNoQuestion(
    $output,
    "Url di staging: $stagingUrl",
    "y"
);

if ( ezcConsoleDialogViewer::displayDialog( $question ) == "n" )
{
    $opts = new ezcConsoleQuestionDialogOptions();
    $opts->text = "Inserisci nuovo url di staging";
    $opts->showResults = true;
    $question = new ezcConsoleQuestionDialog( $output, $opts );
    $stagingUrl = ezcConsoleDialogViewer::displayDialog( $question );

    $confirm = "Confermi $stagingUrl?";
    $question = ezcConsoleQuestionDialog::YesNoQuestion( $output, "$confirm", "y" );

    if ( ezcConsoleDialogViewer::displayDialog( $question ) == "n" )
    {
        throw new Exception( "Ok meglio ricominciare" );
    }
}
