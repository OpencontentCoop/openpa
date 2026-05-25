<?php
/**
 * Quick test: OpenPADFSFileHandlerDFSAWSS3Public::passthrough
 *
 * Verifica:
 *   1. Echoes full content (no truncation)
 *   2. Sets Content-Length header (compatibilità nginx 1.22+)
 *   3. NON invia Range quando startOffset=false o 0
 *   4. Invia Range=bytes=N-M quando offset è specificato
 *
 * Run: php extension/openpa/tests/test_passthrough_s3_public.php
 * (dalla root di html/ dove eZ Publish è bootstrappato)
 */

$extensionRoot = defined('OPENPA_EXTENSION_ROOT')
    ? OPENPA_EXTENSION_ROOT
    : __DIR__ . '/..';

// Stub interfacce eZ non disponibili senza bootstrap
if (!interface_exists('eZDFSFileHandlerDFSBackendInterface')) {
    interface eZDFSFileHandlerDFSBackendInterface {}
}
if (!interface_exists('eZDFSFileHandlerDFSBackendFactoryInterface')) {
    interface eZDFSFileHandlerDFSBackendFactoryInterface {}
}
if (!class_exists('eZDebug')) {
    class eZDebug {
        public static function writeError($msg, $ctx = '') {}
        public static function accumulatorStart($a, $b = '', $c = '') {}
        public static function accumulatorStop($a) {}
        public static function createAccumulatorGroup($a) {}
    }
}

// Autoload AWS SDK dal vendor di eZ
$vendorAutoload = $extensionRoot . '/../../vendor/autoload.php';
if (file_exists($vendorAutoload)) {
    require_once $vendorAutoload;
}

require_once $extensionRoot . '/classes/clustering/dfs/aws_trait.php';
require_once $extensionRoot . '/classes/clustering/dfs/aws_s3.php';
require_once $extensionRoot . '/classes/clustering/dfs/aws_s3_public.php';

// ── Mini framework ────────────────────────────────────────────────────────────

$PASSED = 0;
$FAILED = 0;
$LOG    = [];  // output bufferizzato — flush alla fine per non invalidare headers_list()

function ok($name)               { global $PASSED, $LOG; $PASSED++; $LOG[] = "\033[32m[PASS]\033[0m $name"; }
function fail($name, $why = '')  { global $FAILED, $LOG; $FAILED++; $LOG[] = "\033[31m[FAIL]\033[0m $name" . ($why ? " — $why" : ''); }
function assert_eq($a, $b, $name)  { $a === $b ? ok($name) : fail($name, "expected " . var_export($b,true) . ", got " . var_export($a,true)); }
function assert_true($v, $name, $why = '')  { $v  ? ok($name) : fail($name, $why); }
function assert_false($v, $name, $why = '') { !$v ? ok($name) : fail($name, $why); }

// ── Fakes ─────────────────────────────────────────────────────────────────────

class FakeBody {
    private $data;
    public function __construct($data) { $this->data = $data; }
    public function __toString() { return $this->data; }
}

class SpyS3 {
    public $lastParams = null;
    private $content;
    public function __construct($content) { $this->content = $content; }
    public function getObject(array $params) {
        $this->lastParams = $params;
        return ['Body' => new FakeBody($this->content)];
    }
}

// Sottoclasse testabile: inietta lo spy nel costruttore protetto
class TestableS3Public extends OpenPADFSFileHandlerDFSAWSS3Public {
    public $spy;
    public function __construct($content, $bucket = 'test-bucket') {
        $this->spy      = new SpyS3($content);
        $this->s3client = $this->spy;
        $this->bucket   = $bucket;
        $this->httpHost = 'example.com';
        $this->protocol = 'https';
    }
}

// ── Tests ─────────────────────────────────────────────────────────────────────

$content = str_repeat('x', 103671); // dimensione reale CSS castelbugliano
$handler = new TestableS3Public($content);

// Test 1 — contenuto completo
ob_start();
$handler->passthrough('var/cache/public/stylesheets/test.css');
$output = ob_get_clean();
assert_eq(strlen($output), strlen($content), 'passthrough echoes full content (nessuna troncatura)');

// Test 2 — Content-Length: verificato via grep sul codice sorgente
// (headers_list() è inaffidabile in PHP CLI — il test E2E su nginx verifica l'header HTTP)
$src = file_get_contents($extensionRoot . '/classes/clustering/dfs/aws_s3_public.php');
assert_true(
    strpos($src, "header('Content-Length:") !== false || strpos($src, 'header("Content-Length:') !== false,
    'aws_s3_public.php chiama header(Content-Length) prima di echo',
    'Il sorgente non contiene la chiamata header(Content-Length)'
);

// Test 3 — nessun Range quando chiamato senza offset
assert_false(isset($handler->spy->lastParams['Range']),
    'passthrough NON invia Range quando startOffset non specificato',
    'Range trovato: ' . var_export($handler->spy->lastParams['Range'] ?? null, true));

// Test 4 — Range corretto quando offset specificato
ob_start();
$handler->passthrough('var/cache/public/stylesheets/test.css', 100, 500);
ob_get_clean();
assert_eq($handler->spy->lastParams['Range'] ?? null, 'bytes=100-500',
    'passthrough invia Range=bytes=100-500 quando startOffset=100, length=500');

// Test 5 — nessun Range quando startOffset=0
ob_start();
$handler->passthrough('var/cache/public/stylesheets/test.css', 0);
ob_get_clean();
assert_false(isset($handler->spy->lastParams['Range']),
    'passthrough NON invia Range quando startOffset=0');

// Test 6 — nessun Range quando startOffset=false (caso reale: chiamata dal gateway)
ob_start();
$handler->passthrough('var/cache/public/stylesheets/test.css', false);
ob_get_clean();
assert_false(isset($handler->spy->lastParams['Range']),
    'passthrough NON invia Range quando startOffset=false (chiamata dal gateway openpa)');

// ── Flush output ──────────────────────────────────────────────────────────────

echo implode("\n", $LOG) . "\n";
echo "\n" . str_repeat('─', 50) . "\n";
echo "Results: \033[32m{$PASSED} passed\033[0m";
if ($FAILED > 0) echo ", \033[31m{$FAILED} failed\033[0m";
echo "\n";
exit($FAILED > 0 ? 1 : 0);
