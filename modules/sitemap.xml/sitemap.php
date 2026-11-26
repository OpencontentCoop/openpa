<?php

/**
 * Sitemap: indice e sitemap figlie per sezione (vedi OpenPASitemapBuilder).
 *
 *   /sitemap.xml                          <sitemapindex>
 *   /sitemap.xml?section=<nome>[&page=N]  <urlset>
 *   ?source  (utenti con accesso setup)   albero dei menu in JSON
 *   ?debug   (utenti con accesso setup)   elenco degli URL, query e URL scartati da robots.txt
 */

$notFound = function () {
    header('HTTP/1.1 404 Not Found');
    header('Content-Type: text/plain; charset=utf-8');
    echo "Not Found\n";
    eZExecution::cleanExit();
};

if (!OpenPASitemapBuilder::isEnabled() || OpenPASitemapBuilder::isPrivateSiteaccess()) {
    $notFound();
}

$builder = new OpenPASitemapBuilder(OpenPACrawlPolicy::instance());
$isAdmin = eZUser::currentUser()->hasAccessTo('setup');
$isAdmin = isset($isAdmin['accessWord']) && $isAdmin['accessWord'] !== 'no';

if ($isAdmin && isset($_GET['source'])) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($builder->getMenuTrees());
    eZExecution::cleanExit();
}

$section = isset($_GET['section']) ? (string)$_GET['section'] : null;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

if ($section === null) {
    $xml = $builder->getIndexXml();
} else {
    $xml = $builder->getSectionXml($section, $page);
    if ($xml === null) {
        $notFound();
    }
}

if ($isAdmin && isset($_GET['debug'])) {
    $dom = new DOMDocument();
    $dom->loadXML($xml);
    $urls = [];
    foreach ($dom->getElementsByTagName('loc') as $loc) {
        $urls[] = $loc->nodeValue;
    }
    header('Content-Type: text/html; charset=utf-8');
    echo '<pre>';
    echo htmlspecialchars(print_r([
        'full_sitemap' => OpenPASitemapBuilder::isFullSitemapEnabled(),
        'stats' => $builder->getStats(),
        'urls' => $urls,
    ], true));
    echo '</pre>';
    eZDisplayDebug();
    eZExecution::cleanExit();
}

$ttl = OpenPASitemapBuilder::getCacheTTL();
header('Content-Type: application/xml; charset=utf-8');
header('Cache-Control: public, s-maxage=' . $ttl . ', max-age=' . $ttl);
header('Vary: Accept-Encoding');
echo $xml;
eZExecution::cleanExit();
