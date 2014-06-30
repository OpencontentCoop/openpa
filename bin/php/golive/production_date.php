<?php

$productionDate = $instance->getProductionDate();

$output = new ezcConsoleOutput();
$question = ezcConsoleQuestionDialog::YesNoQuestion(
    $output,
    "Data di produzione: $productionDate",
    "y"
);

if ( ezcConsoleDialogViewer::displayDialog( $question ) == "n" )
{
    $opts = new ezcConsoleQuestionDialogOptions();
    $opts->text = "Inserisci nuovo data di produzione";
    $opts->showResults = true;
    $question = new ezcConsoleQuestionDialog( $output, $opts );
    $productionDate = ezcConsoleDialogViewer::displayDialog( $question );

    $confirm = "Confermi $productionDate?";
    $question = ezcConsoleQuestionDialog::YesNoQuestion( $output, "$confirm", "y" );

    if ( ezcConsoleDialogViewer::displayDialog( $question ) == "n" )
    {
        throw new Exception( "Ok meglio ricominciare" );
    }
}
