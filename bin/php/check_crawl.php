<?php
/**
 * Verifica robots.txt e sitemap del siteaccess (sola lettura).
 *
 *   php extension/openpa/bin/php/check_crawl.php -s<siteaccess_frontend> [--host=www.comune.example.it] [--http] [--verbose]
 *
 * Senza --http genera robots.txt e sitemap in locale (come li genererebbe il sito).
 * Con --http li scarica dall'host, attraverso proxy e cache.
 * Esce con codice 1 se trova errori.
 */
require 'autoload.php';

$script = eZScript::instance([
    'description' => "OpenPA verifica robots.txt e sitemap\n\n",
    'use-session' => false,
    'use-modules' => true,
    'use-extensions' => true,
]);
$script->startup();
$options = $script->getOptions(
    '[host:][http][verbose]',
    '',
    [
        'host' => 'Host pubblico (default: SiteURL del siteaccess)',
        'http' => 'Scarica robots.txt e sitemap via HTTP invece di generarli',
        'verbose' => 'Stampa anche robots.txt e gli URL scartati',
    ]
);
$script->initialize();

$cli = eZCLI::instance();
$errors = [];
$warnings = [];

$host = $options['host'];
if (!$host) {
    $siteUrl = (string)eZINI::instance()->variable('SiteSettings', 'SiteURL');
    $host = preg_replace('#^https?://#', '', rtrim($siteUrl, '/'));
    $host = explode('/', $host)[0];
}
if (!$host) {
    $cli->error('Host non determinabile: usa --host');
    $script->shutdown(1);
}

$_SERVER['HTTP_HOST'] = $host;
$policy = new OpenPACrawlPolicy($host);
$fetch = function ($url) {
    $context = stream_context_create(['http' => [
        'timeout' => 30,
        'ignore_errors' => true,
        'header' => "User-Agent: OpenPA-check-crawl\r\n",
    ]]);
    $body = @file_get_contents($url, false, $context);
    $status = 0;
    $contentType = '';
    // valorizzata da file_get_contents solo se la connessione e' riuscita
    $responseHeaders = get_defined_vars()['http_response_header'] ?? [];
    foreach ((array)$responseHeaders as $header) {
        if (preg_match('#^HTTP/\S+\s+(\d+)#', $header, $m)) {
            $status = (int)$m[1];
        }
        if (stripos($header, 'content-type:') === 0) {
            $contentType = trim(substr($header, 13));
        }
    }
    return [$status, $contentType, $body === false ? '' : $body];
};

$cli->output("== $host");

// robots.txt
if ($options['http']) {
    list($status, $contentType, $robots) = $fetch($policy->absoluteUrl('/robots.txt'));
    if ($status !== 200) {
        $errors[] = "robots.txt: HTTP $status";
    }
} else {
    $robots = $policy->renderRobotsTxt();
}
if ($options['verbose']) {
    $cli->output($robots);
}

preg_match_all('/^\s*sitemap\s*:\s*(\S+)\s*$/im', $robots, $matches);
$sitemapUrls = $matches[1];
$expectedSitemaps = OpenPASitemapBuilder::isEnabled() ? $policy->getSitemapUrls() : [];
if (!$policy->isRobotsEnabled()) {
    $warnings[] = 'robots.txt disabilitato (Disallow: / per tutti)';
} elseif (empty($expectedSitemaps)) {
    $warnings[] = 'sitemap disattivata (ShowSitemap=disabled): nessuna riga Sitemap attesa';
    if (!empty($sitemapUrls)) {
        $errors[] = 'robots.txt: righe Sitemap presenti con la sitemap disattivata';
    }
} elseif ($sitemapUrls !== $expectedSitemaps) {
    $errors[] = 'robots.txt: righe Sitemap ' . implode(', ', $sitemapUrls) . ' (attese ' . implode(', ', $expectedSitemaps) . ')';
}
foreach ($sitemapUrls as $sitemapUrl) {
    if (strpos($sitemapUrl, 'https://') !== 0) {
        $errors[] = "robots.txt: Sitemap non https: $sitemapUrl";
    }
    $sitemapPath = (string)parse_url($sitemapUrl, PHP_URL_PATH);
    foreach ($policy->getPrivatePrefixes() as $prefix) {
        if (strpos($sitemapPath, $prefix . '/') === 0) {
            $errors[] = "robots.txt: Sitemap sotto un siteaccess non pubblico: $sitemapUrl";
        }
    }
    if (!$policy->isAllowed($sitemapPath)) {
        $errors[] = "robots.txt: Sitemap in un percorso vietato: $sitemapUrl";
    }
}

$ignored = OpenPACrawlPolicy::ignoredRobotsTextLines(OpenPAINI::variable('Seo', 'RobotsText', ''));
if (!empty($ignored)) {
    $warnings[] = "campo robots dell'ente: righe ignorate: " . implode(' | ', $ignored);
}

// sitemap
$builder = new OpenPASitemapBuilder($policy);
$load = function ($url) use ($options, $fetch, $builder, &$errors) {
    if ($options['http']) {
        list($status, $contentType, $body) = $fetch($url);
        if ($status !== 200) {
            $errors[] = "$url: HTTP $status";
            return null;
        }
        if (stripos($contentType, 'xml') === false) {
            $errors[] = "$url: Content-Type $contentType";
        }
        return $body;
    }
    $query = [];
    parse_str((string)parse_url($url, PHP_URL_QUERY), $query);
    if (!isset($query['section'])) {
        return $builder->getIndexXml();
    }
    return $builder->getSectionXml($query['section'], isset($query['page']) ? (int)$query['page'] : 1);
};

$totalUrls = 0;
$perSitemap = [];
$skipped = [];
// via HTTP si seguono tutte le sitemap dichiarate (una per lingua); in locale si genera quella
// del siteaccess -s (per una lingua: -s<siteaccess_lingua>, oppure --http)
if ($options['http']) {
    $queue = $sitemapUrls ? $sitemapUrls : $expectedSitemaps;
} else {
    $queue = empty($expectedSitemaps) ? [] : [$policy->getSitemapUrl()];
}
$seen = [];
while ($url = array_shift($queue)) {
    if (isset($seen[$url])) {
        continue;
    }
    $seen[$url] = true;
    $xml = $load($url);
    if ($xml === null || $xml === '') {
        $errors[] = "$url: vuota o non trovata";
        continue;
    }
    if (strlen($xml) > 50 * 1024 * 1024) {
        $errors[] = "$url: oltre 50 MB";
    }
    $dom = new DOMDocument();
    if (!@$dom->loadXML($xml)) {
        $errors[] = "$url: XML non valido";
        continue;
    }
    $root = $dom->documentElement;
    if ($root->namespaceURI !== OpenPASitemapBuilder::SITEMAP_NS) {
        $errors[] = "$url: namespace inatteso " . $root->namespaceURI;
    }
    if ($root->localName === 'sitemapindex') {
        foreach ($root->getElementsByTagNameNS(OpenPASitemapBuilder::SITEMAP_NS, 'loc') as $loc) {
            $queue[] = trim($loc->nodeValue);
        }
        continue;
    }
    if ($root->localName !== 'urlset') {
        $errors[] = "$url: elemento radice inatteso: " . $root->localName;
        continue;
    }
    $count = 0;
    foreach ($root->getElementsByTagNameNS(OpenPASitemapBuilder::SITEMAP_NS, 'url') as $urlNode) {
        $loc = $urlNode->getElementsByTagNameNS(OpenPASitemapBuilder::SITEMAP_NS, 'loc')->item(0);
        if (!$loc) {
            $errors[] = "$url: <url> senza <loc>";
            continue;
        }
        $count++;
        $locUrl = trim($loc->nodeValue);
        $parts = parse_url($locUrl);
        if (!isset($parts['scheme'], $parts['host']) || $parts['scheme'] !== 'https' || strtolower($parts['host']) !== strtolower(explode(':', $host)[0])) {
            $errors[] = "$url: URL non https o di altro host: $locUrl";
        }
        $path = isset($parts['path']) ? $parts['path'] : '/';
        if (!$policy->isAllowed($path)) {
            $skipped[] = $locUrl;
        }
        $lastmod = $urlNode->getElementsByTagNameNS(OpenPASitemapBuilder::SITEMAP_NS, 'lastmod')->item(0);
        if ($lastmod && strtotime($lastmod->nodeValue) > time() + 86400) {
            $warnings[] = "$locUrl: lastmod nel futuro";
        }
    }
    if ($count > 50000) {
        $errors[] = "$url: $count URL (massimo 50000)";
    }
    $perSitemap[$url] = $count;
    $totalUrls += $count;
}

if (!empty($skipped)) {
    $errors[] = count($skipped) . ' URL in sitemap vietati da robots.txt';
}

$cli->output('sitemap completa: ' . (OpenPASitemapBuilder::isFullSitemapEnabled() ? 'si' : 'no'));
foreach ($perSitemap as $url => $count) {
    $cli->output("  $count\t$url");
}
$cli->output("  $totalUrls\ttotale");
if ($options['verbose']) {
    foreach ($skipped as $locUrl) {
        $cli->output("  vietato: $locUrl");
    }
}
foreach ($warnings as $warning) {
    $cli->warning($warning);
}
foreach ($errors as $error) {
    $cli->error($error);
}
$cli->output(empty($errors) ? 'OK' : 'KO');

$script->shutdown(empty($errors) ? 0 : 1);
