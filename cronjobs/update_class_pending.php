<?php
if (!$isQuiet) {
    $cli->output("Starting processing pending search engine modifications");
}

$eZSolr = eZSearch::getEngine();
if (!( $eZSolr instanceof eZSolr )) {
    $script->shutdown(1, 'The current search engine plugin is not eZSolr');
}

$contentObjects = array();
$db = eZDB::instance();

$key = OpenPAClassTools::ACTION_UPDATE_CLASS;


$entries = $db->arrayQuery("SELECT param FROM ezpending_actions WHERE action = '$key'");

if (is_array($entries) && count($entries) != 0) {
    foreach ($entries as $entry) {
        $classIdentifier = $entry['param'];
        $cli->output("Reindex class $classIdentifier");

        $class = eZContentClass::fetchByIdentifier($classIdentifier);
        if ($class instanceof eZContentClass) {


            $objects = eZPersistentObject::fetchObjectList(eZContentObject::definition(),
                array('id'),
                array('contentclass_id' => $class->attribute('id')),
                null,
                null,
                false);
            $ids = array();
            foreach ($objects as $object) {
                $ids[] = $object['id'];
            }

            if (count($ids) > 0) {
                $count = count($ids);
                $output = new ezcConsoleOutput();
                $progressBarOptions = array('emptyChar' => ' ', 'barChar' => '=');
                $progressBarOptions['minVerbosity'] = 10;
                $progressBar = new ezcConsoleProgressbar($output, intval($count), $progressBarOptions);
                $progressBar->start();

                foreach ($ids as $id) {
                    $progressBar->advance();
                    eZDB::instance()->query( "INSERT INTO ezpending_actions( action, param ) VALUES ( 'index_object', '$id' )" );
                }
                $progressBar->finish();
            }
        }


        $db->begin();
        $db->query("DELETE FROM ezpending_actions WHERE action = '$key' AND param = '$classIdentifier'");
        $db->commit();
    }
    $eZSolr->commit();

}


if (!$isQuiet) {
    $cli->output("Done");
}
