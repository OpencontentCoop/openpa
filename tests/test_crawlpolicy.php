<?php
/**
 * Test della logica pura di OpenPACrawlPolicy (matching RFC 9309, parsing del campo robots, codifica path).
 * Non richiede il kernel eZ.
 *
 * Run:
 *   php extension/openpa/tests/test_crawlpolicy.php
 *   docker run --rm -v "$PWD":/app -w /app php:7.4-cli php tests/test_crawlpolicy.php   (dalla cartella openpa)
 */

require_once __DIR__ . '/../classes/openpacrawlpolicy.php';

$failures = 0;
$assert = function ($label, $actual, $expected) use (&$failures) {
    if ($actual === $expected) {
        echo "ok   $label\n";
        return;
    }
    $failures++;
    echo "FAIL $label\n     atteso: " . var_export($expected, true) . "\n     ottenuto: " . var_export($actual, true) . "\n";
};

// Regole come le genera la policy con i default di openpa.ini, prefissi '' ed '/en', backend privato
$rules = [['Disallow', '/backend/'], ['Disallow', '/backend$'], ['Disallow', '/debug/'], ['Disallow', '/debug$']];
foreach (['', '/en'] as $prefix) {
    foreach (['/api/', '/opendata/', '/user', '/layout/', '/content/search', '/content/advancedsearch',
                 '/content/view', '/content/keyword', '/*/(tag)/', '/Classificazioni/', '/Media/'] as $path) {
        $rules[] = ['Disallow', $prefix . $path];
    }
    foreach (['/api/opendata/v2/', '/opendata/api/'] as $path) {
        $rules[] = ['Allow', $prefix . $path];
    }
    foreach (['/Classificazioni', '/Media'] as $path) {
        $rules[] = ['Disallow', $prefix . $path . '/'];
        $rules[] = ['Disallow', $prefix . $path . '$'];
    }
    $rules[] = ['Allow', $prefix . '/Media/Domande-frequenti'];
    foreach (['/archivio', '/archivio_backend', '/archive', '/old'] as $path) {
        $rules[] = ['Disallow', $prefix . $path];
    }
}
$compiled = OpenPACrawlPolicy::compileRules($rules);
$allowed = function ($path) use ($compiled) {
    return OpenPACrawlPolicy::matchRules($compiled, $path);
};

$cases = [
    '/' => true,
    '/Argomenti/Ambiente' => true,
    '/Amministrazione/Amministrazione-trasparente' => true,
    '/Media/Domande-frequenti' => true,
    '/Media/Domande-frequenti/Tributi/Come-pago-la-TARI' => true,
    '/Media/Immagini/foto' => false,
    '/Media' => false,
    '/Classificazioni' => false,
    '/Classificazioni/Orari-strutture' => false,
    '/api/opendata/v2/content/search/classes%20%5Bevent%5D' => true,
    '/api/opendata/v2/classes/event' => true,
    '/api/opendata/v1/content/node/2' => false,
    '/api/openapi' => false,
    '/api/sensor/posts' => false,
    '/opendata/api/calendar/search/x' => true,
    '/opendata/api/content/read/12' => true,
    '/opendata/console' => false,
    '/backend/user/login' => false,
    '/debug' => false,
    '/backend' => false,
    '/backend-office-informazioni' => true,
    '/user/login' => false,
    '/content/search?SearchText=x' => false,
    '/Novita/Notizie/(tag)/eventi' => false,
    '/en/Media/Domande-frequenti' => true,
    '/en/Media/Images' => false,
    '/en/api/opendata/v2/content/read/1' => true,
    '/archivio/vecchia-pagina' => false,
    '/Novita' => true,
];
foreach ($cases as $path => $expected) {
    $assert("isAllowed $path", $allowed($path), $expected);
}

// a parita' di lunghezza vince Allow; '$' ancora la fine
$tie = OpenPACrawlPolicy::compileRules([['Disallow', '/a'], ['Allow', '/a']]);
$assert('parita: vince Allow', OpenPACrawlPolicy::matchRules($tie, '/a/b'), true);
$anchored = OpenPACrawlPolicy::compileRules([['Disallow', '/*.pdf$']]);
$assert('$ e * : /x/y.pdf vietato', OpenPACrawlPolicy::matchRules($anchored, '/x/y.pdf'), false);
$assert('$ e * : /x/y.pdf?a consentito', OpenPACrawlPolicy::matchRules($anchored, '/x/y.pdf?a'), true);

// parsing del campo robots dell'ente
list($parsedRules, $extra) = OpenPACrawlPolicy::parseRobotsText(
    "User-agent: *\nDisallow: /privato\nCrawl-delay: 10\nRequest-rate: 1/10\nSitemap: https://altro/sitemap.xml\n" .
    "# commento\n\nUser-agent: Googlebot\nUser-agent: Bingbot\nDisallow: /solo-google\n\nUser-agent: *\nAllow: /privato/pubblico\n"
);
$assert('parse: regole del gruppo *', $parsedRules, [['Disallow', '/privato'], ['Allow', '/privato/pubblico']]);
$assert('parse: gruppi extra', $extra, "User-agent: Googlebot\nUser-agent: Bingbot\nDisallow: /solo-google");

list($parsedRules, $extra) = OpenPACrawlPolicy::parseRobotsText("Disallow: /senza-gruppo\nDisallow:\n");
$assert('parse: righe senza User-agent vanno nel gruppo *', $parsedRules, [['Disallow', '/senza-gruppo']]);
$assert('parse: nessun gruppo extra', $extra, '');

$assert(
    'ignoredRobotsTextLines',
    OpenPACrawlPolicy::ignoredRobotsTextLines("User-agent: *\nDisallow: /x\nSitemap: https://h/s.xml\n# Crawl-delay: 10\nCrawl-delay: 10\nVisit-time: 0000-0600"),
    ['Sitemap: https://h/s.xml', 'Crawl-delay: 10', 'Visit-time: 0000-0600']
);

// classificazione dei siteaccess mappati sullo stesso host
$private = ['backend', 'debug'];
$related = ['comune_frontend' => true, 'comune_eng_frontend' => true, 'comune_backend' => true];
$sameDb = function () {
    return true;
};
$otherDb = function () {
    return false;
};
$classify = function ($uri, $siteaccess, $related, $sharesDatabase) use ($private) {
    return OpenPACrawlPolicy::classifyMappedSiteaccess($uri, $siteaccess, $private, $related, $sharesDatabase);
};
$assert('prefisso: backend per uri', $classify('backend', 'comune_backend', $related, $sameDb), OpenPACrawlPolicy::PREFIX_PRIVATE);
$assert('prefisso: backend per suffisso del nome', $classify('admin', 'comune_backend', $related, $sameDb), OpenPACrawlPolicy::PREFIX_PRIVATE);
$assert('prefisso: uri privato in maiuscolo', $classify('Debug', 'comune_frontend', $related, $sameDb), OpenPACrawlPolicy::PREFIX_PRIVATE);
$assert('prefisso: lingua nella RelatedSiteAccessList', $classify('en', 'Comune_Eng_Frontend', $related, $sameDb), OpenPACrawlPolicy::PREFIX_LANGUAGE);
$assert('prefisso: duplicato stesso database (/new)', $classify('new', 'comune_new', $related, $sameDb), OpenPACrawlPolicy::PREFIX_PRIVATE);
$assert('prefisso: altro sito, altro database (/agenda)', $classify('agenda', 'comune_ita_agenda', $related, $otherDb), OpenPACrawlPolicy::PREFIX_OTHER);
$assert('prefisso: RelatedSiteAccessList vuota = tutto pubblico', $classify('new', 'comune_new', [], $sameDb), OpenPACrawlPolicy::PREFIX_LANGUAGE);
$assert('prefisso: privato anche con RelatedSiteAccessList vuota', $classify('backend', 'comune_backend', [], $sameDb), OpenPACrawlPolicy::PREFIX_PRIVATE);
$called = false;
$classify('en', 'comune_eng_frontend', $related, function () use (&$called) {
    $called = true;
    return true;
});
$assert('prefisso: database letto solo per i siteaccess fuori lista', $called, false);

// codifica dei path
$assert('encodePath spazi', OpenPACrawlPolicy::encodePath('/Media/Domande frequenti'), '/Media/Domande%20frequenti');
$assert('encodePath idempotente', OpenPACrawlPolicy::encodePath('/Media/Domande%20frequenti'), '/Media/Domande%20frequenti');
$assert('encodePath parentesi', OpenPACrawlPolicy::encodePath('/Novita/(tag)/x'), '/Novita/(tag)/x');
$assert('encodePath accenti', OpenPACrawlPolicy::encodePath('/Novità'), '/Novit%C3%A0');

echo $failures === 0 ? "\nOK\n" : "\n$failures FALLITI\n";
exit($failures === 0 ? 0 : 1);
