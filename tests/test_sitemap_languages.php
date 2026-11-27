<?php
/**
 * Test di integrazione per siti con piu' lingue e siteaccess duplicati (richiede il kernel eZ e un DB installato).
 * Non modifica file ne' cache: imposta la mappa dei siteaccess solo in memoria e genera la sitemap senza cache.
 *
 * Copre:
 * - la classificazione dei prefissi mappati sullo stesso host: privato, lingua (RelatedSiteAccessList),
 *   duplicato dello stesso database (es. /new), altro sito con un altro database (es. /agenda);
 * - le righe Sitemap di robots.txt, una per lingua;
 * - il prefisso della lingua nell'indice e negli URL della sitemap, ricavato dalla mappa e non dalla richiesta
 *   (qui il test gira da CLI, dove la richiesta non ha prefisso);
 * - la sitemap non servita sui siteaccess fuori dalla RelatedSiteAccessList.
 *
 * Run (dalla root di eZ):
 *   php extension/openpa/tests/test_sitemap_languages.php -s<siteaccess_frontend>
 */
require 'autoload.php';

$script = eZScript::instance([
    'description' => "Test di integrazione della sitemap per lingua\n\n",
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

$siteIni = eZINI::instance();
$current = eZSiteAccess::current();
$currentName = isset($current['name']) ? $current['name'] : '';
$host = OpenPACrawlPolicy::siteUrlHost();
if ($currentName === '' || $host === '') {
    echo "FAIL serve un siteaccess frontend con SiteURL (-s<siteaccess_frontend>)\n";
    $script->shutdown(1);
}

// impostazioni originali, ripristinate alla fine
$saved = [
    ['site.ini', 'SiteAccessSettings', 'HostUriMatchMapItems'],
    ['site.ini', 'SiteAccessSettings', 'RelatedSiteAccessList'],
    ['site.ini', 'DatabaseSettings', 'Database'],
    ['openpa.ini', 'SitemapSettings', 'CacheTTL'],
];
foreach ($saved as $index => $item) {
    $ini = eZINI::instance($item[0]);
    $saved[$index][] = $ini->hasVariable($item[1], $item[2]) ? $ini->variable($item[1], $item[2]) : null;
}

// Mappa di prova sull'host del sito. Il siteaccess corrente fa da lingua a /zz, cosi' la sitemap generata
// qui deve avere il prefisso /zz. I siteaccess test_* non esistono: per eZ hanno le impostazioni globali,
// quindi lo stesso database del sito.
$siteIni->setVariable('SiteAccessSettings', 'HostUriMatchMapItems', [
    "$host;backend;test_backend",
    "$host;zz;$currentName",
    "$host;dup;test_duplicate",
]);
$siteIni->setVariable('SiteAccessSettings', 'RelatedSiteAccessList', [$currentName, 'test_backend']);
eZINI::instance('openpa.ini')->setVariable('SitemapSettings', 'CacheTTL', '0');

// prefissi: privato, lingua, duplicato dello stesso database
$policy = new OpenPACrawlPolicy($host);
$assert('backend privato', in_array('/backend', $policy->getPrivatePrefixes(), true));
$assert('lingua /zz pubblica', in_array('/zz', $policy->getPublicPrefixes(), true), implode(', ', $policy->getPublicPrefixes()));
$assert('duplicato /dup vietato (stesso database, fuori da RelatedSiteAccessList)', in_array('/dup', $policy->getPrivatePrefixes(), true), implode(', ', $policy->getPrivatePrefixes()));
$assert('duplicato /dup non pubblico', !in_array('/dup', $policy->getPublicPrefixes(), true));
$assert('prefisso del siteaccess corrente: /zz', $policy->getCurrentPrefix() === '/zz', var_export($policy->getCurrentPrefix(), true));

$assert('robots: /dup/ vietato', !$policy->isAllowed('/dup/Amministrazione'));
$assert('robots: /dup vietato', !$policy->isAllowed('/dup'));
$assert('robots: /zz consentito', $policy->isAllowed('/zz/Amministrazione'));
$assert('robots: regole statiche ripetute per /zz', !$policy->isAllowed('/zz/content/search'));

$robots = $policy->renderRobotsTxt();
preg_match_all('/^Sitemap: (\S+)$/m', $robots, $matches);
$expected = ["https://$host/sitemap.xml", "https://$host/zz/sitemap.xml"];
if (!OpenPASitemapBuilder::isEnabled()) {
    $expected = [];
}
$assert('robots: una riga Sitemap per la radice e per /zz', $matches[1] === $expected, implode(', ', $matches[1]));

// altro sito sullo stesso host (altro database): ne' pubblico ne' vietato, nessuna Sitemap.
// Se il database arriva da EZINI_* (immagine docker) setVariable non ha effetto: il caso e' coperto
// da test_crawlpolicy.php (classifyMappedSiteaccess).
if (getenv('EZINI_site__DatabaseSettings__Database') !== false) {
    echo "skip altro sito con altro database: Database impostato da EZINI_site__DatabaseSettings__Database\n";
} else {
    $siteIni->setVariable('DatabaseSettings', 'Database', 'test_altro_database');
    $policy = new OpenPACrawlPolicy($host);
    $assert('altro sito /dup non vietato', !in_array('/dup', $policy->getPrivatePrefixes(), true), implode(', ', $policy->getPrivatePrefixes()));
    $assert('altro sito /dup non pubblico', !in_array('/dup', $policy->getPublicPrefixes(), true));
    $assert('altro sito: nessuna Sitemap per /dup', strpos($policy->renderRobotsTxt(), "https://$host/dup/sitemap.xml") === false);
    $siteIni->setVariable('DatabaseSettings', 'Database', $saved[2][3]);
}

// sitemap della lingua /zz generata da CLI: prefisso dalla mappa
$policy = new OpenPACrawlPolicy($host);
$builder = new OpenPASitemapBuilder($policy);
$index = $builder->getIndexXml();
preg_match_all('#<loc>([^<]+)</loc>#', $index, $matches);
$assert('indice con almeno una sitemap figlia', count($matches[1]) > 0);
$withoutPrefix = array_filter($matches[1], function ($loc) use ($host) {
    return strpos($loc, "https://$host/zz/sitemap.xml?section=") !== 0;
});
$assert('indice: tutte le sitemap figlie sotto /zz', empty($withoutPrefix), implode(', ', $withoutPrefix));

$locations = $builder->getSectionLocations();
$urls = [];
foreach ($locations as $location) {
    $xml = $builder->getSectionXml($location['section'], $location['page']);
    preg_match_all('#<loc>([^<]+)</loc>#', (string)$xml, $matches);
    $urls = array_merge($urls, $matches[1]);
}
$assert('sitemap /zz: almeno un URL', count($urls) > 0);
$outside = array_filter($urls, function ($url) use ($host) {
    return $url !== "https://$host/zz" && strpos($url, "https://$host/zz/") !== 0;
});
$assert('sitemap /zz: tutti gli URL sotto /zz', empty($outside), implode(', ', array_slice($outside, 0, 3)));
$assert('sitemap /zz: home senza slash finale', in_array("https://$host/zz", $urls, true));
$assert('sitemap /zz: nessun URL con doppio slash', empty(preg_grep('#^https://[^/]+/.*//#', $urls)));

// sitemap non servita sui siteaccess fuori dalla RelatedSiteAccessList (es. _new, _old)
$assert('siteaccess nella RelatedSiteAccessList: sitemap servita', !OpenPASitemapBuilder::isPrivateSiteaccess());
$siteIni->setVariable('SiteAccessSettings', 'RelatedSiteAccessList', ['test_backend']);
$assert('siteaccess fuori dalla RelatedSiteAccessList: sitemap non servita', OpenPASitemapBuilder::isPrivateSiteaccess());
$siteIni->setVariable('SiteAccessSettings', 'RelatedSiteAccessList', []);
$assert('RelatedSiteAccessList vuota: sitemap servita', !OpenPASitemapBuilder::isPrivateSiteaccess());

foreach ($saved as $item) {
    if ($item[3] !== null) {
        eZINI::instance($item[0])->setVariable($item[1], $item[2], $item[3]);
    }
}

echo $failures === 0 ? "\nOK\n" : "\n$failures test falliti\n";
$script->shutdown($failures === 0 ? 0 : 1);
