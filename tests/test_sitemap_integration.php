<?php
/**
 * Test di integrazione di sitemap e cache (richiede il kernel eZ e un DB installato).
 * Svuota la cache della sitemap del sito su cui gira: non lanciarlo in produzione.
 *
 * Run (dalla root di eZ):
 *   php extension/openpa/tests/test_sitemap_integration.php -s<siteaccess_frontend>
 */
require 'autoload.php';

$script = eZScript::instance([
    'description' => "Test di integrazione della sitemap\n\n",
    'use-session' => false,
    'use-modules' => true,
    'use-extensions' => true,
]);
$script->startup();
$script->getOptions();
$script->initialize();

$failures = 0;
$assert = function ($label, $condition, $detail = '') use (&$failures) {
    if ($condition) {
        echo "ok   $label\n";
        return;
    }
    $failures++;
    echo "FAIL $label" . ($detail !== '' ? "\n     $detail" : '') . "\n";
};

// voci di cache della sitemap ancora valide, qualunque sia il cluster handler
$countCacheEntries = function () {
    $dir = eZSys::cacheDirectory() . '/' . OpenPASitemapBuilder::CACHE_DIR;
    if (eZClusterFileHandler::instance() instanceof eZDFSFileHandler) {
        $db = eZDB::instance();
        $rows = $db->arrayQuery(
            "SELECT COUNT(*) AS total FROM ezdfsfile_cache WHERE name LIKE '" . $db->escapeString($dir) . "/%' AND expired = 0"
        );
        return (int)$rows[0]['total'];
    }
    $files = glob($dir . '/*/*.xml');
    return is_array($files) ? count($files) : 0;
};

$ini = eZINI::instance('openpa.ini');
$ini->setVariable('SitemapSettings', 'CacheTTL', '3600');

// host: solo quelli configurati, altrimenti quello di SiteURL
$siteHost = OpenPACrawlPolicy::siteUrlHost();
$assert('host di SiteURL presente', $siteHost !== '', 'SiteSettings/SiteURL vuoto');
$assert('host configurato accettato', OpenPACrawlPolicy::resolveRequestHost(strtoupper($siteHost) . ':443') === $siteHost);
$assert('host sconosciuto sostituito da SiteURL', OpenPACrawlPolicy::resolveRequestHost('attacker.example') === $siteHost);

OpenPASitemapBuilder::clearServerCache();
$assert('cache vuota dopo clearServerCache', $countCacheEntries() === 0, 'voci: ' . $countCacheEntries());

$policy = new OpenPACrawlPolicy($siteHost);
$builder = new OpenPASitemapBuilder($policy);

$index = $builder->getIndexXml();
$assert('indice generato', strpos($index, '<sitemapindex') !== false);
$afterIndex = $countCacheEntries();
$assert('indice in cache (indice + elenco sezioni)', $afterIndex === 2, "voci: $afterIndex");
$assert('indice uguale dalla cache', (new OpenPASitemapBuilder($policy))->getIndexXml() === $index);

$locations = $builder->getSectionLocations();
$assert('almeno una sitemap figlia', count($locations) > 0);
foreach ($locations as $location) {
    $assert("indice elenca {$location['loc']}", strpos($index, htmlspecialchars($location['loc'], ENT_XML1)) !== false);
}

$first = $locations[0];
$xml = $builder->getSectionXml($first['section'], $first['page']);
$assert("sezione {$first['section']} pagina {$first['page']} generata", is_string($xml) && strpos($xml, '<urlset') !== false);
$assert('sezione uguale dalla cache', (new OpenPASitemapBuilder($policy))->getSectionXml($first['section'], $first['page']) === $xml);
$afterSection = $countCacheEntries();
$assert('sezione in cache', $afterSection === $afterIndex + 1, "voci: $afterSection");

// pagine e sezioni che non sono nell'indice: 404 senza query ne' voci di cache
$assert('pagina inesistente → null', $builder->getSectionXml($first['section'], 999999) === null);
$assert('pagina 0 → null', $builder->getSectionXml($first['section'], 0) === null);
$assert('sezione sconosciuta → null', $builder->getSectionXml('sconosciuta', 1) === null);
$assert('nessuna voce di cache per richieste non valide', $countCacheEntries() === $afterSection, 'voci: ' . $countCacheEntries());

OpenPASitemapBuilder::clearCache();
$assert('cache vuota dopo clearCache', $countCacheEntries() === 0, 'voci: ' . $countCacheEntries());
$assert('indice rigenerato dopo clearCache', (new OpenPASitemapBuilder($policy))->getIndexXml() === $index);

echo $failures === 0 ? "\nOK\n" : "\n$failures test falliti\n";
$script->shutdown($failures === 0 ? 0 : 1);
