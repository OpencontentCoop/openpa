<?php
require 'autoload.php';

$script = eZScript::instance(array('description' => ("OpenPA Crea istanza\n\n"),
    'use-session' => false,
    'use-modules' => true,
    'use-extensions' => false));

$script->startup();

$options = $script->getOptions();
$script->initialize();
$script->setUseDebugAccumulators(true);
$output = new ezcConsoleOutput();
$cli = eZCLI::instance();

try {

    $current = OpenPABase::getCurrentSiteaccessIdentifier();
    if ($current != 'prototipo' && $current != 'biblioteca' && $current != 'opencity' && $current != 'openagenda') {
        throw new Exception("La creazione di una nuova istanza al momento è possibile solo dal prototipo o da biblioteca (hai usato: $current)");
    }

    $identifier = ezcConsoleDialogViewer::displayDialog(new ezcConsoleQuestionDialog($output, new ezcConsoleQuestionDialogOptions(array(
        'text' => "Inserisci identificatore",
        'showResults' => true
    ))));
    if (file_exists("settings/siteaccess/{$identifier}_backend")) {
        throw new Exception("Esiste già un'istanza con identificatore $identifier");
    }

    $siteAccessIdentifierList = array();
    foreach (eZDir::findSubdirs('settings/siteaccess') as $item) {
        if (strpos($item, "{$current}_") !== false) {
            $siteAccessIdentifier = str_replace("{$current}_", '', $item);
            if ($siteAccessIdentifier != 'backend' && $siteAccessIdentifier != 'debug') {
                $siteAccessIdentifierList[] = $siteAccessIdentifier;
            }
        }
    }
    sort($siteAccessIdentifierList);

    class OpenPAMultiChoiceMenuDialogValidator extends ezcConsoleMenuDialogDefaultValidator
    {
        public function validate($result)
        {
            $result = explode(' ', $result);
            $result = array_map('trim', $result);
            foreach ($result as $item) {
                if (!isset($this->elements[$item])) {
                    return false;
                }
            }

            return true;
        }
    }

    $menu = new ezcConsoleMenuDialog($output);
    $menu->options = new ezcConsoleMenuDialogOptions();
    $menu->options->text = "Seleziona gli ambienti da attivare (separandoli con uno spazio):\n";
    $menu->options->validator = new OpenPAMultiChoiceMenuDialogValidator($siteAccessIdentifierList);
    $envChoice = ezcConsoleDialogViewer::displayDialog($menu);
    $envChoice = explode(' ', $envChoice);
    $envChoice = array_map('trim', $envChoice);
    $siteAccessSelectedList = array();
    foreach ($envChoice as $env) {
        $siteAccessSelectedList[] = $siteAccessIdentifierList[$env];
    }
    $siteAccessIdentifierList[] = 'debug';
    if (!empty($siteAccessSelectedList)) {
        $siteAccessSelectedList[] = 'backend';
        if (in_array('frontend', $siteAccessSelectedList)) {
            $siteAccessSelectedList[] = 'debug';
        }
    } else {
        throw new Exception("Selezione ambienti da installare non corretta");
    }


    $nomeAmministrazioneAfferente = ezcConsoleDialogViewer::displayDialog(new ezcConsoleQuestionDialog($output, new ezcConsoleQuestionDialogOptions(array(
        'text' => "Inserisci il nome dell'amministrazione afferente",
        'showResults' => true
    ))));
    $urlAmministrazioneAfferente = ezcConsoleDialogViewer::displayDialog(new ezcConsoleQuestionDialog($output, new ezcConsoleQuestionDialogOptions(array(
        'text' => "Inserisci l'url completo dell'amministrazione afferente",
        'showResults' => true
    ))));

    $siteUrl = $urlSuffix = $siteName = $tempSiteUrl = array();
    $suggestions = array();
    foreach ($siteAccessSelectedList as $siteAccessSelected) {
        $cli->warning("Impostazioni per $siteAccessSelected");
        $suggestions['siteUrl'] = $siteUrl[$siteAccessSelected] = ezcConsoleDialogViewer::displayDialog(new ezcConsoleQuestionDialog($output, new ezcConsoleQuestionDialogOptions(array(
            'text' => "[$siteAccessSelected] Inserisci dominio di produzione (esempio: www.example.com)",
            'showResults' => true,
            'validator' => new ezcConsoleQuestionDialogTypeValidator(ezcConsoleQuestionDialogTypeValidator::TYPE_STRING, isset($suggestions['siteUrl']) ? $suggestions['siteUrl'] : '')
        ))));

        $suggestions['tempSiteUrl'] = $tempSiteUrl[$siteAccessSelected] = ezcConsoleDialogViewer::displayDialog(new ezcConsoleQuestionDialog($output, new ezcConsoleQuestionDialogOptions(array(
            'text' => "[$siteAccessSelected] Inserisci il dominio temporaneo (esempio: example.opencontent.it)",
            'showResults' => true,
            'validator' => new ezcConsoleQuestionDialogTypeValidator(ezcConsoleQuestionDialogTypeValidator::TYPE_STRING, isset($suggestions['tempSiteUrl']) ? $suggestions['tempSiteUrl'] : '')
        ))));

        $urlSuffix[$siteAccessSelected] = ezcConsoleDialogViewer::displayDialog(new ezcConsoleQuestionDialog($output, new ezcConsoleQuestionDialogOptions(array(
            'text' => "[$siteAccessSelected] Inserisci il suffisso al dominio di produzione (esempio: backend)",
            'showResults' => true,
            'validator' => new ezcConsoleQuestionDialogTypeValidator(ezcConsoleQuestionDialogTypeValidator::TYPE_STRING, '')
        ))));

        $suggestions['siteName'] = $siteName[$siteAccessSelected] = ezcConsoleDialogViewer::displayDialog(new ezcConsoleQuestionDialog($output, new ezcConsoleQuestionDialogOptions(array(
            'text' => "[$siteAccessSelected] Inserisci titolo del sito (esempio: Comune di Tornimparte)",
            'showResults' => true,
            'validator' => new ezcConsoleQuestionDialogTypeValidator(ezcConsoleQuestionDialogTypeValidator::TYPE_STRING, isset($suggestions['siteName']) ? $suggestions['siteName'] : '')
        ))));
        $cli->output();
    }

    foreach ($siteAccessSelectedList as $suffix) {

        $dirPath = "settings/siteaccess/{$current}_{$suffix}";

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

        $originalOpenpaIni = file_get_contents($directory . '/' . 'openpa.ini.append.php');
        $newOpenpaIni = $originalOpenpaIni;
        $openpaIni = new eZINI('openpa.ini.append.php', $dirPath, false, false, false, true, false);
        $originalNomeAmministrazioneAfferente = $openpaIni->variable('InstanceSettings', 'NomeAmministrazioneAfferente');
        $originalUrlAmministrazioneAfferente = $openpaIni->variable('InstanceSettings', 'UrlAmministrazioneAfferente');
        $newOpenpaIni = str_replace(
            "NomeAmministrazioneAfferente={$originalNomeAmministrazioneAfferente}",
            "NomeAmministrazioneAfferente=" . $nomeAmministrazioneAfferente,
            $newOpenpaIni
        );
        $newOpenpaIni = str_replace(
            "UrlAmministrazioneAfferente={$originalUrlAmministrazioneAfferente}",
            "UrlAmministrazioneAfferente=" . $urlAmministrazioneAfferente,
            $newOpenpaIni
        );
        if (file_put_contents($directory . '/' . 'openpa.ini.append.php', $newOpenpaIni)) {
            $cli->output("Modificato file openpa.ini.append.php");
        }

        foreach (array('file.ini.append.php', 'solr.ini.append.php', 'site.ini.append.php') as $file) {
            $originalFileIni = file_get_contents($directory . '/' . $file);
            $newFileIni = str_replace($current, $identifier, $originalFileIni);
            if ($file == 'site.ini.append.php') {
                $siteIni = new eZINI('site.ini.append.php', $dirPath, false, false, false, true, false);
                $originalSiteName = $siteIni->variable('SiteSettings', 'SiteName');
                $originalSiteUrl = $siteIni->variable('SiteSettings', 'SiteURL');
                $originalSiteUrl = str_replace($current, $identifier, $originalSiteUrl);

                $originalAdditionalLoginFormActionURL = $siteIni->variable('SiteSettings', 'AdditionalLoginFormActionURL');
                $originalAdditionalLoginFormActionURL = str_replace($current, $identifier, $originalAdditionalLoginFormActionURL);

                $newFileIni = str_replace(
                    "SiteName={$originalSiteName}",
                    "SiteName=" . $siteName[$suffix],
                    $newFileIni
                );

                $siteUrlSuffixed = !empty($siteUrl[$suffix]) ? $siteUrl[$suffix] : $tempSiteUrl[$suffix];
                if (!empty($urlSuffix[$suffix]))
                    $siteUrlSuffixed .= '/' . $urlSuffix[$suffix];
                $newFileIni = str_replace(
                    "SiteURL={$originalSiteUrl}",
                    "SiteURL={$siteUrlSuffixed}",
                    $newFileIni
                );

                $backendUrl = $tempSiteUrl['backend'];
                if (!empty($urlSuffix['backend']))
                    $backendUrl .= '/' . $urlSuffix['backend'];
                $additionalLoginFormActionURL = 'https://' . $backendUrl . '/user/login';
                $newFileIni = str_replace(
                    "AdditionalLoginFormActionURL={$originalAdditionalLoginFormActionURL}",
                    "AdditionalLoginFormActionURL={$additionalLoginFormActionURL}",
                    $newFileIni
                );

                foreach ($siteAccessIdentifierList as $siteAccessIdentifier) {
                    if (!in_array($siteAccessIdentifier, $siteAccessSelectedList)) {
                        $newFileIni = str_replace(
                            "RelatedSiteAccessList[]={$identifier}_{$siteAccessIdentifier}\n",
                            "",
                            $newFileIni
                        );
                    }
                }
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

    $reorderSiteAccessSelectedList = array();
    foreach ($siteAccessSelectedList as $suffix) {
        if (!empty($urlSuffix[$suffix])) {
            $reorderSiteAccessSelectedList[] = $suffix;
        }
    }
    foreach ($siteAccessSelectedList as $suffix) {
        if (!in_array($suffix, $reorderSiteAccessSelectedList)) {
            $reorderSiteAccessSelectedList[] = $suffix;
        }
    }
    $siteAccessSelectedList = $reorderSiteAccessSelectedList;

    $newFileList = '';
    $newAvailableSiteAccessList = '';
    $newHostUriMatchMapItems = '';
    $newHostUriMatchMapItems_temp = '';
    foreach ($siteAccessSelectedList as $suffix) {
        $newFileList .= "SiteList[]={$identifier}_{$suffix}\n";
        $newAvailableSiteAccessList .= "AvailableSiteAccessList[]={$identifier}_{$suffix}\n";
        if (!empty($siteUrl[$suffix])) {
            $newHostUriMatchMapItems .= "HostUriMatchMapItems[]=" . $siteUrl[$suffix] . ";" . $urlSuffix[$suffix] . ";{$identifier}_{$suffix}\n";
        }
        if (!empty($tempSiteUrl[$suffix])) {
            $newHostUriMatchMapItems_temp .= "HostUriMatchMapItems[]=" . $tempSiteUrl[$suffix] . ";" . $urlSuffix[$suffix] . ";{$identifier}_{$suffix}\n";
        }
    }
    $newFileList .= '#SiteList';
    $newAvailableSiteAccessList .= '#AvailableSiteAccessList';
    $newHostUriMatchMapItems .= "#HostUriMatchMapItems";
    $newHostUriMatchMapItems_temp .= "#TempHostUriMatchMapItems";

    $override = file_get_contents("settings/override/site.ini.append.php");
    $override = str_replace('#SiteList', $newFileList, $override);
    $override = str_replace('#AvailableSiteAccessList', $newAvailableSiteAccessList, $override);
    $override = str_replace('#HostUriMatchMapItems', $newHostUriMatchMapItems, $override);
    $override = str_replace('#TempHostUriMatchMapItems', $newHostUriMatchMapItems_temp, $override);

    if (file_put_contents("settings/override/site.ini.append.php", $override)) {
        $cli->output("Modificato override/site.ini");
    }

    $script->shutdown();
} catch
(Exception $e) {
    $errCode = $e->getCode();
    $errCode = $errCode != 0 ? $errCode : 1; // If an error has occured, script must terminate with a status other than 0
    $script->shutdown($errCode, $e->getMessage());
}