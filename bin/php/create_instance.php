<?php
require 'autoload.php';

$script = eZScript::instance(array('description' => ("OpenPA Crea istanza\n\n"),
    'use-session' => false,
    'use-modules' => true,
    'use-extensions' => true));

$script->startup();

$options = $script->getOptions();
$script->initialize();
$script->setUseDebugAccumulators(true);
$output = new ezcConsoleOutput();
$cli = eZCLI::instance();
try {

    $current = OpenPABase::getCurrentSiteaccessIdentifier();
    if ($current != 'prototipo' && $current != 'biblioteca') {
        throw new Exception("La creazione di una nuova istanza al momento è possibile solo dal prototipo o da biblioteca (hai usato: $current)");
    }

    $identifier = ezcConsoleDialogViewer::displayDialog(new ezcConsoleQuestionDialog($output, new ezcConsoleQuestionDialogOptions(array(
        'text' => "Inserisci identificatore",
        'showResults' => true
    ))));
    if (file_exists("settings/siteaccess/{$identifier}_backend")) {
        throw new Exception("Esiste già un'istanza con identificatore $identifier");
    }

    $siteUrl = ezcConsoleDialogViewer::displayDialog(new ezcConsoleQuestionDialog($output, new ezcConsoleQuestionDialogOptions(array(
        'text' => "Inserisci dominio di produzione",
        'showResults' => true
    ))));

    $siteName = ezcConsoleDialogViewer::displayDialog(new ezcConsoleQuestionDialog($output, new ezcConsoleQuestionDialogOptions(array(
        'text' => "Inserisci titolo del sito (es. Comune di Trento)",
        'showResults' => true
    ))));

    $tempDomainSuffix = ezcConsoleDialogViewer::displayDialog(new ezcConsoleQuestionDialog($output, new ezcConsoleQuestionDialogOptions(array(
        'text' => "Inserisci suffiso del dominio temporaneo (es. comunitatrentina.it)",
        'showResults' => true
    ))));


    foreach (array('frontend', 'debug', 'backend') as $suffix) {
        $originalDirectory = "settings/siteaccess/{$current}_{$suffix}";
        $directory = "settings/siteaccess/{$identifier}_{$suffix}";
        eZDir::mkdir($directory);
        if (eZDir::copy(
            $originalDirectory,
            $directory,
            false
        )) {
            $cli->output("Creata cartella $directory");
        }

        foreach (array('file.ini.append.php', 'solr.ini.append.php', 'site.ini.append.php') as $file) {
            $originalFileIni = file_get_contents($directory . '/' . $file);
            $newFileIni = str_replace($current, $identifier, $originalFileIni);
            if ($file == 'site.ini.append.php') {
                $dirPath = "settings/siteaccess/{$current}_{$suffix}";
                $siteIni = new eZINI('site.ini.append.php', $dirPath, false, false, false, true, false);
                $originalSiteName = $siteIni->variable('SiteSettings', 'SiteName');
                $originalSiteUrl = $siteIni->variable('SiteSettings', 'SiteURL');
                $newFileIni = str_replace(
                    "SiteName={$originalSiteName}",
                    "SiteName={$siteName}",
                    $newFileIni
                );
                $siteUrlSuffixed = $siteUrl;
                if ($suffix != 'frontend') {
                    $siteUrlSuffixed .= '/' . $suffix;
                }
                $newFileIni = str_replace(
                    "SiteURL={$originalSiteUrl}",
                    "SiteURL={$siteUrlSuffixed}",
                    $newFileIni
                );
            }
            if (file_put_contents($directory . '/' . $file, $newFileIni)) {
                $cli->output("Modificato file $file");
            }
        }
    }

    $originalConfigClusterPath = "settings/cluster-config/config_cluster_{$current}.php";
    $configClusterPath = "settings/cluster-config/config_cluster_{$identifier}.php";
    if (file_exists($originalConfigClusterPath)) {
        $originalConfigCluster = file_get_contents($originalConfigClusterPath);
        $configCluster = str_replace($current, $identifier, $originalConfigCluster);
        if (file_put_contents($configClusterPath, $configCluster)) {
            $cli->output("Creato file $configClusterPath");
        }
    }

    $newFileList = "SiteList[]={$identifier}_frontend\nSiteList[]={$identifier}_debug\nSiteList[]={$identifier}_backend\n#SiteList";
    $newAvailableSiteAccessList = "AvailableSiteAccessList[]={$identifier}_frontend\nAvailableSiteAccessList[]={$identifier}_debug\nAvailableSiteAccessList[]={$identifier}_backend\n#AvailableSiteAccessList";
    $newHostUriMatchMapItems = "HostUriMatchMapItems[]={$siteUrl};backend;{$identifier}_backend\nHostUriMatchMapItems[]={$siteUrl};debug;{$identifier}_debug\nHostUriMatchMapItems[]={$siteUrl};;{$identifier}_frontend\n#HostUriMatchMapItems";
    $newHostUriMatchMapItems_temp = "HostUriMatchMapItems[]={$identifier}.{$tempDomainSuffix};backend;{$identifier}_backend\nHostUriMatchMapItems[]={$identifier}.{$tempDomainSuffix};debug;{$identifier}_debug\nHostUriMatchMapItems[]={$identifier}.{$tempDomainSuffix};;{$identifier}_frontend\n#TempHostUriMatchMapItems";
    $override = file_get_contents("settings/override/site.ini.append.php");
    $override = str_replace('#SiteList', $newFileList, $override);
    $override = str_replace('#AvailableSiteAccessList', $newAvailableSiteAccessList, $override);
    $override = str_replace('#HostUriMatchMapItems', $newHostUriMatchMapItems, $override);
    $override = str_replace('#TempHostUriMatchMapItems', $newHostUriMatchMapItems_temp, $override);
    if (file_put_contents("settings/override/site.ini.append.php", $override)) {
        $cli->output("Modificato override/site.ini");
    }

    $script->shutdown();
} catch (Exception $e) {
    $errCode = $e->getCode();
    $errCode = $errCode != 0 ? $errCode : 1; // If an error has occured, script must terminate with a status other than 0
    $script->shutdown($errCode, $e->getMessage());
}