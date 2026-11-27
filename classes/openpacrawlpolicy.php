<?php

/**
 * Unica fonte delle regole di crawling del sito: le usa robots.txt per scriverle
 * e sitemap.xml per non elencare URL vietati (coerenza per costruzione).
 *
 * Configurazione: openpa.ini [CrawlPolicy]. Le regole sui nodi sono ricavate dai remote id
 * (URL alias reale, in tutte le lingue), non da path scritti a mano.
 */
class OpenPACrawlPolicy
{
    const ALLOW = 'Allow';

    const DISALLOW = 'Disallow';

    private static $instance;

    private $host;

    private $publicPrefixes;

    private $privatePrefixes;

    private $groupRules;

    private $extraGroups;

    private $excludedSubtrees;

    private $includedSubtrees;

    private $compiledRules;

    /**
     * @param string|null $host per i test; di default l'host della richiesta
     */
    public function __construct($host = null)
    {
        $this->host = $host !== null ? $host : self::resolveRequestHost(eZSys::hostname());
    }

    /**
     * Host da usare negli URL assoluti e nelle chiavi di cache: quello della richiesta solo se
     * e' un host configurato del sito (SiteURL, HostMatchMapItems, HostUriMatchMapItems),
     * altrimenti l'host di SiteURL. L'header Host e' scelto dal client e non deve entrare
     * nelle sitemap ne' moltiplicare le voci di cache.
     *
     * @param string $requestHost
     * @return string
     */
    public static function resolveRequestHost($requestHost)
    {
        $siteHost = self::siteUrlHost();
        $allowed = self::configuredHosts();
        $normalized = self::normalizeHost($requestHost);
        if ($normalized !== '' && isset($allowed[$normalized])) {
            return $normalized;
        }
        return $siteHost !== '' ? $siteHost : $normalized;
    }

    /**
     * @return string host di SiteSettings/SiteURL del siteaccess corrente, senza schema, porta e path
     */
    public static function siteUrlHost()
    {
        $siteUrl = trim((string)eZINI::instance()->variable('SiteSettings', 'SiteURL'));
        $siteUrl = preg_replace('#^[a-z]+://#i', '', $siteUrl);
        $siteUrl = explode('/', $siteUrl)[0];
        return self::normalizeHost($siteUrl);
    }

    /**
     * @return array<string, true>
     */
    private static function configuredHosts()
    {
        $hosts = [];
        $siteHost = self::siteUrlHost();
        if ($siteHost !== '') {
            $hosts[$siteHost] = true;
        }
        $ini = eZINI::instance();
        foreach ((array)$ini->variableArray('SiteAccessSettings', 'HostUriMatchMapItems') as $item) {
            if (is_array($item) && isset($item[0])) {
                $hosts[self::normalizeHost($item[0])] = true;
            }
        }
        foreach ((array)$ini->variableArray('SiteAccessSettings', 'HostMatchMapItems') as $item) {
            if (is_array($item) && isset($item[0])) {
                $hosts[self::normalizeHost($item[0])] = true;
            }
        }
        unset($hosts['']);
        return $hosts;
    }

    /**
     * @return OpenPACrawlPolicy
     */
    public static function instance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function resetInstance()
    {
        self::$instance = null;
    }

    public function isRobotsEnabled()
    {
        return OpenPAINI::variable('Seo', 'EnableRobots') === 'enabled';
    }

    public function getHost()
    {
        return $this->host;
    }

    public function getScheme()
    {
        $scheme = strtolower(trim((string)self::iniVariable('Scheme', 'https')));
        return in_array($scheme, ['http', 'https']) ? $scheme : 'https';
    }

    /**
     * @param string $path path assoluto (es. /Argomenti/Ambiente)
     * @return string
     */
    public function absoluteUrl($path)
    {
        return $this->getScheme() . '://' . $this->host . '/' . ltrim($path, '/');
    }

    public function getSitemapUrl()
    {
        return $this->absoluteUrl('/sitemap.xml');
    }

    /**
     * Uri dei siteaccess non pubblici (backend, debug): sempre vietati, anche se non mappati.
     * @return string[] es. ['/backend', '/debug']
     */
    public function getPrivatePrefixes()
    {
        if ($this->privatePrefixes === null) {
            $this->loadPrefixes();
        }
        return $this->privatePrefixes;
    }

    /**
     * Prefissi pubblici dello stesso host: '' (radice) piu' gli uri dei siteaccess in lingua.
     * @return string[] es. ['', '/en', '/de']
     */
    public function getPublicPrefixes()
    {
        if ($this->publicPrefixes === null) {
            $this->loadPrefixes();
        }
        return $this->publicPrefixes;
    }

    private function loadPrefixes()
    {
        $privateUris = array_map(function ($uri) {
            return strtolower(trim($uri, '/ '));
        }, (array)self::iniVariable('PrivateUriList', ['backend', 'debug']));
        $privateUris = array_values(array_filter($privateUris));

        $private = [];
        foreach ($privateUris as $uri) {
            $private['/' . $uri] = true;
        }
        $public = ['' => true];

        $currentHost = self::normalizeHost($this->host);
        $items = (array)eZINI::instance()->variableArray('SiteAccessSettings', 'HostUriMatchMapItems');
        foreach ($items as $item) {
            if (!is_array($item) || count($item) < 3) {
                continue;
            }
            if (self::normalizeHost($item[0]) !== $currentHost) {
                continue;
            }
            $uri = strtolower(trim($item[1], '/ '));
            if ($uri === '') {
                continue;
            }
            $siteaccess = strtolower($item[2]);
            $isPrivate = in_array($uri, $privateUris);
            foreach ($privateUris as $privateUri) {
                if (substr($siteaccess, -strlen('_' . $privateUri)) === '_' . $privateUri) {
                    $isPrivate = true;
                }
            }
            if ($isPrivate) {
                $private['/' . $uri] = true;
            } else {
                $public['/' . $uri] = true;
            }
        }

        $this->privatePrefixes = array_keys($private);
        $this->publicPrefixes = array_map('strval', array_keys($public));
    }

    private static function normalizeHost($host)
    {
        $host = strtolower(trim((string)$host));
        $colon = strpos($host, ':');
        return $colon === false ? $host : substr($host, 0, $colon);
    }

    /**
     * Regole del gruppo "User-agent: *", nell'ordine in cui vanno scritte.
     * @return array<int, array{0: string, 1: string}>
     */
    public function getGroupRules()
    {
        if ($this->groupRules !== null) {
            return $this->groupRules;
        }

        $rules = [];
        foreach ($this->getPrivatePrefixes() as $prefix) {
            $rules[] = [self::DISALLOW, $prefix . '/'];
            $rules[] = [self::DISALLOW, $prefix . '$'];
        }

        $disallowPaths = (array)self::iniVariable('DisallowPaths', []);
        $allowPaths = (array)self::iniVariable('AllowPaths', []);
        list($excludedPaths, $includedPaths) = $this->getSubtreeUrlPaths();
        $archivePaths = [];
        if (OpenPAINI::variable('Seo', 'DisableArchive', 'enabled') === 'enabled') {
            $archivePaths = (array)self::iniVariable('ArchivePaths', ['/archivio', '/archivio_backend', '/archive', '/old']);
        }

        foreach ($this->getPublicPrefixes() as $prefix) {
            foreach ($disallowPaths as $path) {
                $rules[] = [self::DISALLOW, $prefix . self::normalizeRulePath($path)];
            }
            foreach ($allowPaths as $path) {
                $rules[] = [self::ALLOW, $prefix . self::normalizeRulePath($path)];
            }
            foreach ($excludedPaths as $path) {
                $rules[] = [self::DISALLOW, $prefix . $path . '/'];
                $rules[] = [self::DISALLOW, $prefix . $path . '$'];
            }
            foreach ($includedPaths as $path) {
                $rules[] = [self::ALLOW, $prefix . $path];
            }
            foreach ($archivePaths as $path) {
                $rules[] = [self::DISALLOW, $prefix . self::normalizeRulePath($path)];
            }
        }

        list($customRules, $extraGroups) = $this->parseCustomRobotsText();
        foreach ($customRules as $rule) {
            $rules[] = $rule;
        }
        $this->extraGroups = $extraGroups;

        $unique = [];
        foreach ($rules as $rule) {
            if ($rule[1] === '' || $rule[1] === '$') {
                continue;
            }
            $unique[$rule[0] . '|' . $rule[1]] = $rule;
        }
        $this->groupRules = array_values($unique);

        return $this->groupRules;
    }

    /**
     * Gruppi per user-agent specifici scritti dall'ente nel campo robots (copiati cosi' come sono).
     * @return string
     */
    public function getExtraGroups()
    {
        if ($this->extraGroups === null) {
            $this->getGroupRules();
        }
        return $this->extraGroups;
    }

    private static function normalizeRulePath($path)
    {
        $path = trim((string)$path);
        if ($path === '') {
            return '';
        }
        return '/' . ltrim($path, '/');
    }

    /**
     * Interpreta il campo robots dell'ente (Seo/RobotsText) come regole aggiuntive:
     * - Allow/Disallow fuori da un gruppo o sotto "User-agent: *" vanno in coda al gruppo *;
     * - gruppi per altri user-agent sono copiati in coda;
     * - Sitemap, Crawl-delay, Request-rate, Visit-time e righe non riconosciute sono ignorate.
     *
     * @return array{0: array, 1: string}
     */
    private function parseCustomRobotsText()
    {
        $text = (string)OpenPAINI::variable('Seo', 'RobotsText', '');
        return self::parseRobotsText($text);
    }

    /**
     * @param string $text
     * @return array{0: array, 1: string}
     */
    public static function parseRobotsText($text)
    {
        $rules = [];
        $extraGroups = [];
        $currentAgents = ['*'];
        $currentExtra = null;
        $lastWasAgent = false;

        foreach (preg_split('/\r\n|\r|\n/', $text) as $line) {
            $line = trim(preg_replace('/#.*$/', '', $line));
            if ($line === '' || strpos($line, ':') === false) {
                continue;
            }
            list($key, $value) = array_map('trim', explode(':', $line, 2));
            $key = strtolower($key);

            if ($key === 'user-agent') {
                if (!$lastWasAgent) {
                    if ($currentExtra !== null) {
                        $extraGroups[] = $currentExtra;
                    }
                    $currentAgents = [];
                    $currentExtra = null;
                }
                $currentAgents[] = $value;
                $lastWasAgent = true;
                continue;
            }
            $lastWasAgent = false;

            if (!in_array($key, ['allow', 'disallow'])) {
                continue;
            }
            $type = $key === 'allow' ? self::ALLOW : self::DISALLOW;

            if (in_array('*', $currentAgents)) {
                if ($value !== '') {
                    $rules[] = [$type, self::normalizeRulePath($value)];
                }
            }
            $others = array_values(array_diff($currentAgents, ['*']));
            if (!empty($others)) {
                if ($currentExtra === null) {
                    $currentExtra = [];
                    foreach ($others as $agent) {
                        $currentExtra[] = 'User-agent: ' . $agent;
                    }
                }
                $currentExtra[] = $type . ': ' . $value;
            }
        }
        if ($currentExtra !== null) {
            $extraGroups[] = $currentExtra;
        }

        $extra = implode("\n\n", array_map(function ($group) {
            return implode("\n", $group);
        }, $extraGroups));

        return [$rules, $extra];
    }

    /**
     * Path (senza prefisso) dei sottoalberi vietati e di quelli consentiti, in tutte le lingue.
     * @return array{0: string[], 1: string[]}
     */
    private function getSubtreeUrlPaths()
    {
        $excluded = [];
        foreach ($this->getExcludedSubtreeNodes() as $node) {
            foreach (self::nodeUrlPaths($node) as $path) {
                $excluded[$path] = true;
            }
        }
        $included = [];
        foreach ($this->getIncludedSubtreeNodes() as $node) {
            foreach (self::nodeUrlPaths($node) as $path) {
                $included[$path] = true;
            }
        }
        return [array_keys($excluded), array_keys($included)];
    }

    /**
     * path_string dei sottoalberi vietati (per i filtri SQL della sitemap).
     * @return string[]
     */
    public function getExcludedSubtreePathStrings()
    {
        return array_map(function (eZContentObjectTreeNode $node) {
            return $node->attribute('path_string');
        }, $this->getExcludedSubtreeNodes());
    }

    /**
     * path_string dei sottoalberi consentiti anche se dentro un sottoalbero vietato (es. FAQ in Media).
     * @return string[]
     */
    public function getIncludedSubtreePathStrings()
    {
        return array_map(function (eZContentObjectTreeNode $node) {
            return $node->attribute('path_string');
        }, $this->getIncludedSubtreeNodes());
    }

    /**
     * @return eZContentObjectTreeNode[]
     */
    private function getExcludedSubtreeNodes()
    {
        if ($this->excludedSubtrees === null) {
            $nodes = self::fetchNodesByRemoteIdList((array)self::iniVariable('DisallowSubtreeRemoteIds', []));
            if (self::iniVariable('DisallowMediaRoot', 'true') === 'true') {
                $mediaRootNodeId = (int)eZINI::instance('content.ini')->variable('NodeSettings', 'MediaRootNode');
                $media = $mediaRootNodeId > 0 ? eZContentObjectTreeNode::fetch($mediaRootNodeId) : null;
                if ($media instanceof eZContentObjectTreeNode) {
                    $nodes[] = $media;
                }
            }
            $this->excludedSubtrees = $nodes;
        }
        return $this->excludedSubtrees;
    }

    /**
     * @return eZContentObjectTreeNode[]
     */
    private function getIncludedSubtreeNodes()
    {
        if ($this->includedSubtrees === null) {
            $this->includedSubtrees = self::fetchNodesByRemoteIdList((array)self::iniVariable('AllowSubtreeRemoteIds', []));
        }
        return $this->includedSubtrees;
    }

    /**
     * @param string[] $remoteIdList
     * @return eZContentObjectTreeNode[]
     */
    private static function fetchNodesByRemoteIdList($remoteIdList)
    {
        $nodes = [];
        foreach ($remoteIdList as $remoteId) {
            $remoteId = trim($remoteId);
            if ($remoteId === '') {
                continue;
            }
            $object = eZContentObject::fetchByRemoteID($remoteId);
            if ($object instanceof eZContentObject) {
                $node = $object->mainNode();
                if ($node instanceof eZContentObjectTreeNode) {
                    $nodes[] = $node;
                }
            }
        }
        return $nodes;
    }

    /**
     * URL alias del nodo in tutte le lingue del sito, gia' codificati per l'uso in un URL.
     * @return string[] es. ['/Media/Domande-frequenti']
     */
    private static function nodeUrlPaths(eZContentObjectTreeNode $node)
    {
        $ids = array_values(array_filter(explode('/', $node->attribute('path_string'))));
        array_shift($ids); // nodo 1, radice di sistema
        if (empty($ids)) {
            return [];
        }
        $paths = [];
        foreach (eZContentLanguage::fetchList() as $language) {
            $path = eZURLAliasML::fetchPathByActionList('eznode', $ids, $language->attribute('locale'));
            if (is_string($path) && trim($path, '/') !== '') {
                $paths[self::encodePath('/' . trim($path, '/'))] = true;
            }
        }
        $fallback = $node->urlAlias();
        if (is_string($fallback) && trim($fallback, '/') !== '') {
            $paths[self::encodePath('/' . trim($fallback, '/'))] = true;
        }
        return array_keys($paths);
    }

    /**
     * Codifica ogni segmento del path lasciando intatti i caratteri ammessi nei path (RFC 3986).
     * @param string $path
     * @return string
     */
    public static function encodePath($path)
    {
        $segments = explode('/', $path);
        foreach ($segments as $index => $segment) {
            $segments[$index] = strtr(rawurlencode(rawurldecode($segment)), [
                '%28' => '(', '%29' => ')', '%21' => '!', '%2A' => '*', '%27' => "'",
                '%2C' => ',', '%3B' => ';', '%3A' => ':', '%40' => '@', '%26' => '&',
                '%3D' => '=', '%2B' => '+', '%24' => '$',
            ]);
        }
        return implode('/', $segments);
    }

    /**
     * Verifica un path contro le regole del gruppo * secondo RFC 9309:
     * vince la regola piu' lunga che fa match, a parita' vince Allow, senza match e' consentito.
     *
     * @param string $path
     * @return bool
     */
    public function isAllowed($path)
    {
        if ($this->compiledRules === null) {
            $this->compiledRules = self::compileRules($this->getGroupRules());
        }
        return self::matchRules($this->compiledRules, $path);
    }

    /**
     * @param array $rules lista di [type, pattern]
     * @return array
     */
    public static function compileRules(array $rules)
    {
        $compiled = [];
        foreach ($rules as $rule) {
            list($type, $pattern) = $rule;
            $anchored = substr($pattern, -1) === '$';
            $body = $anchored ? substr($pattern, 0, -1) : $pattern;
            $regex = '#^' . str_replace('\*', '.*', preg_quote($body, '#')) . ($anchored ? '$' : '') . '#';
            $compiled[] = [$type, strlen($pattern), $regex];
        }
        return $compiled;
    }

    /**
     * @param array $compiledRules
     * @param string $path
     * @return bool
     */
    public static function matchRules(array $compiledRules, $path)
    {
        $path = '/' . ltrim((string)$path, '/');
        $bestLength = -1;
        $allowed = true;
        foreach ($compiledRules as $rule) {
            list($type, $length, $regex) = $rule;
            if (!preg_match($regex, $path)) {
                continue;
            }
            if ($length > $bestLength || ($length === $bestLength && $type === self::ALLOW)) {
                $bestLength = $length;
                $allowed = $type === self::ALLOW;
            }
        }
        return $allowed;
    }

    /**
     * Testo completo del robots.txt.
     * @return string
     */
    public function renderRobotsTxt()
    {
        $lines = ['User-agent: *'];
        if (!$this->isRobotsEnabled()) {
            $lines[] = 'Disallow: /';
            return implode("\n", $lines) . "\n";
        }

        foreach ($this->getGroupRules() as $rule) {
            $lines[] = $rule[0] . ': ' . $rule[1];
        }
        $crawlDelay = $this->getCrawlDelay();
        if ($crawlDelay !== null) {
            $lines[] = 'Crawl-delay: ' . $crawlDelay;
        }

        $extraGroups = $this->getExtraGroups();
        if ($extraGroups !== '') {
            $lines[] = '';
            $lines[] = $extraGroups;
        }

        $lines[] = '';
        $lines[] = '# The Ultimate robots.txt Bot and User-Agent Blocker Version: V3.2019.12.1186';
        $lines[] = '# Copyright: https://github.com/mitchellkrogza/apache-ultimate-bad-bot-blocker';
        foreach (self::badBotUserAgents() as $userAgent) {
            $lines[] = 'User-agent: ' . $userAgent;
            $lines[] = 'Disallow: /';
        }

        if (OpenPASitemapBuilder::isEnabled()) {
            $lines[] = '';
            $lines[] = 'Sitemap: ' . $this->getSitemapUrl();
        }

        return implode("\n", $lines) . "\n";
    }

    /**
     * Righe del campo robots dell'ente che non vengono piu' usate (per l'audit).
     * @return string[]
     */
    public static function ignoredRobotsTextLines($text)
    {
        $ignored = [];
        foreach (preg_split('/\r\n|\r|\n/', (string)$text) as $line) {
            $clean = trim(preg_replace('/#.*$/', '', $line));
            if ($clean === '') {
                continue;
            }
            $key = strtolower(trim(strtok($clean, ':')));
            if (strpos($clean, ':') === false || !in_array($key, ['user-agent', 'allow', 'disallow'])) {
                $ignored[] = trim($line);
            }
        }
        return $ignored;
    }

    public function getCrawlDelay()
    {
        if (OpenPAINI::variable('Seo', 'RateLimitingRules', 'disabled') !== 'enabled') {
            return null;
        }
        return max(1, (int)self::iniVariable('CrawlDelay', 3));
    }

    /**
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    private static function iniVariable($name, $default)
    {
        $ini = eZINI::instance('openpa.ini');
        if ($ini->hasVariable('CrawlPolicy', $name)) {
            return $ini->variable('CrawlPolicy', $name);
        }
        return $default;
    }

    /**
     * @see https://github.com/mitchellkrogza/apache-ultimate-bad-bot-blocker/blob/master/robots.txt/robots.txt
     * @return string[]
     */
    public static function badBotUserAgents()
    {
        return ['360Spider', '404checker', '404enemy', '80legs', 'Abonti', 'Aboundex', 'Aboundexbot', 'Acunetix', 'ADmantX', 'AfD-Verbotsverfahren', 'AhrefsBot', 'AIBOT', 'AiHitBot', 'Aipbot', 'Alexibot', 'Alligator', 'AllSubmitter', 'AlphaBot', 'Anarchie', 'Apexoo', 'archive.org_bot', 'arquivo.pt', 'arquivo-web-crawler', 'ASPSeek', 'Asterias', 'Attach', 'autoemailspider', 'AwarioRssBot', 'AwarioSmartBot', 'BackDoorBot', 'Backlink-Ceck', 'backlink-check', 'BacklinkCrawler', 'BackStreet', 'BackWeb', 'Badass', 'Bandit', 'Barkrowler', 'BatchFTP', 'Battleztar Bazinga', 'BBBike', 'BDCbot', 'BDFetch', 'BetaBot', 'Bigfoot', 'Bitacle', 'Blackboard', 'Black Hole', 'BlackWidow', 'BLEXBot', 'Blow', 'BlowFish', 'Boardreader', 'Bolt', 'BotALot', 'Brandprotect', 'Brandwatch', 'Buddy', 'BuiltBotTough', 'BuiltWith', 'Bullseye', 'BunnySlippers', 'BuzzSumo', 'Calculon', 'CATExplorador', 'CazoodleBot', 'CCBot', 'Cegbfeieh', 'CheeseBot', 'CherryPicker', 'CheTeam', 'ChinaClaw', 'Chlooe', 'Claritybot', 'Cliqzbot', 'Cloud mapping', 'coccocbot-web', 'Cogentbot', 'cognitiveseo', 'Collector', 'com.plumanalytics', 'Copier', 'CopyRightCheck', 'Copyscape', 'Cosmos', 'Craftbot', 'crawler4j', 'crawler.feedback', 'crawl.sogou.com', 'CrazyWebCrawler', 'Crescent', 'CrunchBot', 'CSHttp', 'Curious', 'Custo', 'DatabaseDriverMysqli', 'DataCha0s', 'DBLBot', 'demandbase-bot', 'Demon', 'Deusu', 'Devil', 'Digincore', 'DigitalPebble', 'DIIbot', 'Dirbuster', 'Disco', 'Discobot', 'Discoverybot', 'Dispatch', 'DittoSpyder', 'DnyzBot', 'DomainAppender', 'DomainCrawler', 'DomainSigmaCrawler', 'DomainStatsBot', 'Dotbot', 'Download Wonder', 'Dragonfly', 'Drip', 'DSearch', 'DTS Agent', 'EasyDL', 'Ebingbong', 'eCatch', 'ECCP/1.0', 'Ecxi', 'EirGrabber', 'EMail Siphon', 'EMail Wolf', 'EroCrawler', 'evc-batch', 'Evil', 'Exabot', 'Express WebPictures', 'ExtLinksBot', 'Extractor', 'ExtractorPro', 'Extreme Picture Finder', 'EyeNetIE', 'Ezooms', 'facebookscraper', 'FDM', 'FemtosearchBot', 'FHscan', 'Fimap', 'Firefox/7.0', 'FlashGet', 'Flunky', 'Foobot', 'Freeuploader', 'FrontPage', 'FyberSpider', 'Fyrebot', 'GalaxyBot', 'Genieo', 'GermCrawler', 'Getintent', 'GetRight', 'GetWeb', 'Gigablast', 'Gigabot', 'G-i-g-a-b-o-t', 'Go-Ahead-Got-It', 'Gotit', 'GoZilla', 'Go!Zilla', 'Grabber', 'GrabNet', 'Grafula', 'GrapeFX', 'GrapeshotCrawler', 'GridBot', 'GT::WWW', 'Haansoft', 'HaosouSpider', 'Harvest', 'Havij', 'HEADMasterSEO', 'heritrix', 'Heritrix', 'Hloader', 'HMView', 'HTMLparser', 'HTTP::Lite', 'HTTrack', 'Humanlinks', 'HybridBot', 'Iblog', 'IDBot', 'Id-search', 'IlseBot', 'Image Fetch', 'Image Sucker', 'IndeedBot', 'Indy Library', 'InfoNaviRobot', 'InfoTekies', 'instabid', 'Intelliseek', 'InterGET', 'Internet Ninja', 'InternetSeer', 'internetVista monitor', 'ips-agent', 'Iria', 'IRLbot', 'Iskanie', 'IstellaBot', 'JamesBOT', 'Jbrofuzz', 'JennyBot', 'JetCar', 'Jetty', 'JikeSpider', 'JOC Web Spider', 'Joomla', 'Jorgee', 'JustView', 'Jyxobot', 'Kenjin Spider', 'Keyword Density', 'Kozmosbot', 'Lanshanbot', 'Larbin', 'LeechFTP', 'LeechGet', 'LexiBot', 'Lftp', 'LibWeb', 'Libwhisker', 'Lightspeedsystems', 'Likse', 'Linkdexbot', 'LinkextractorPro', 'LinkpadBot', 'LinkScan', 'LinksManager', 'LinkWalker', 'LinqiaMetadataDownloaderBot', 'LinqiaRSSBot', 'LinqiaScrapeBot', 'Lipperhey', 'Lipperhey Spider', 'Litemage_walker', 'Lmspider', 'LNSpiderguy', 'Ltx71', 'lwp-request', 'LWP::Simple', 'lwp-trivial', 'Magnet', 'Mag-Net', 'magpie-crawler', 'Mail.RU_Bot', 'Majestic12', 'Majestic SEO', 'Majestic-SEO', 'MarkMonitor', 'MarkWatch', 'Masscan', 'Mass Downloader', 'Mata Hari', 'MauiBot', 'meanpathbot', 'Meanpathbot', 'MeanPath Bot', 'Mediatoolkitbot', 'mediawords', 'MegaIndex.ru', 'Metauri', 'MFC_Tear_Sample', 'Microsoft Data Access', 'Microsoft URL Control', 'MIDown tool', 'MIIxpc', 'Mister PiX', 'MJ12bot', 'Mojeek', 'Mojolicious', 'Morfeus Fucking Scanner', 'Mr.4x3', 'MSFrontPage', 'MSIECrawler', 'Msrabot', 'muhstik-scan', 'Musobot', 'Name Intelligence', 'Nameprotect', 'Navroad', 'NearSite', 'Needle', 'Nessus', 'NetAnts', 'Netcraft', 'netEstate NE Crawler', 'NetLyzer', 'NetMechanic', 'NetSpider', 'Nettrack', 'Net Vampire', 'Netvibes', 'NetZIP', 'NextGenSearchBot', 'Nibbler', 'NICErsPRO', 'Niki-bot', 'Nikto', 'NimbleCrawler', 'Nimbostratus', 'Ninja', 'Nmap', 'NPbot', 'Nutch', 'oBot', 'Octopus', 'Offline Explorer', 'Offline Navigator', 'OnCrawl', 'Openfind', 'OpenLinkProfiler', 'Openvas', 'OpenVAS', 'OrangeBot', 'OrangeSpider', 'OutclicksBot', 'OutfoxBot', 'PageAnalyzer', 'Page Analyzer', 'PageGrabber', 'page scorer', 'PageScorer', 'Pandalytics', 'Panscient', 'Papa Foto', 'Pavuk', 'pcBrowser', 'PECL::HTTP', 'PeoplePal', 'PHPCrawl', 'Picscout', 'Picsearch', 'PictureFinder', 'Pimonster', 'Pi-Monster', 'Pixray', 'PleaseCrawl', 'plumanalytics', 'Pockey', 'POE-Component-Client-HTTP', 'Probethenet', 'ProPowerBot', 'ProWebWalker', 'Psbot', 'Pump', 'PxBroker', 'PyCurl', 'QueryN Metasearch', 'Quick-Crawler', 'RankActive', 'RankActiveLinkBot', 'RankFlex', 'RankingBot', 'RankingBot2', 'Rankivabot', 'RankurBot', 'RealDownload', 'Reaper', 'RebelMouse', 'Recorder', 'RedesScrapy', 'ReGet', 'RepoMonkey', 'Ripper', 'RocketCrawler', 'Rogerbot', 'RSSingBot', 's1z.ru', 'SalesIntelligent', 'SBIder', 'ScanAlert', 'Scanbot', 'scan.lol', 'ScoutJet', 'Scrapy', 'Screaming', 'ScreenerBot', 'Searchestate', 'SearchmetricsBot', 'Semrush', 'SemrushBot', 'SEOkicks', 'SEOkicks-Robot', 'SEOlyticsCrawler', 'Seomoz', 'SEOprofiler', 'seoscanners', 'SeoSiteCheckup', 'SEOstats', 'serpstatbot', 'sexsearcher', 'Shodan', 'Siphon', 'SISTRIX', 'Sitebeam', 'SiteExplorer', 'Siteimprove', 'SiteLockSpider', 'SiteSnagger', 'SiteSucker', 'Site Sucker', 'Sitevigil', 'SlySearch', 'SmartDownload', 'SMTBot', 'Snake', 'Snapbot', 'Snoopy', 'SocialRankIOBot', 'Sociscraper', 'sogouspider', 'Sogou web spider', 'Sosospider', 'Sottopop', 'SpaceBison', 'Spammen', 'SpankBot', 'Spanner', 'sp_auditbot', 'Spbot', 'Spinn3r', 'SputnikBot', 'spyfu', 'Sqlmap', 'Sqlworm', 'Sqworm', 'Steeler', 'Stripper', 'Sucker', 'Sucuri', 'SuperBot', 'SuperHTTP', 'Surfbot', 'SurveyBot', 'Suzuran', 'Swiftbot', 'sysscan', 'Szukacz', 'T0PHackTeam', 'T8Abot', 'tAkeOut', 'Teleport', 'TeleportPro', 'Telesoft', 'Telesphoreo', 'Telesphorep', 'The Intraformant', 'TheNomad', 'Thumbor', 'TightTwatBot', 'Titan', 'Toata', 'Toweyabot', 'Tracemyfile', 'Trendiction', 'Trendictionbot', 'trendiction.com', 'trendiction.de', 'True_Robot', 'Turingos', 'Turnitin', 'TurnitinBot', 'TwengaBot', 'Twice', 'Typhoeus', 'UnisterBot', 'Upflow', 'URLy Warning', 'URLy.Warning', 'Vacuum', 'Vagabondo', 'VB Project', 'VCI', 'VeriCiteCrawler', 'VidibleScraper', 'Virusdie', 'VoidEYE', 'Voil', 'Voltron', 'Wallpapers/3.0', 'WallpapersHD', 'WASALive-Bot', 'WBSearchBot', 'Webalta', 'WebAuto', 'Web Auto', 'WebBandit', 'WebCollage', 'Web Collage', 'WebCopier', 'WEBDAV', 'WebEnhancer', 'Web Enhancer', 'WebFetch', 'Web Fetch', 'WebFuck', 'Web Fuck', 'WebGo IS', 'WebImageCollector', 'WebLeacher', 'WebmasterWorldForumBot', 'webmeup-crawler', 'WebPix', 'Web Pix', 'WebReaper', 'WebSauger', 'Web Sauger', 'Webshag', 'WebsiteExtractor', 'WebsiteQuester', 'Website Quester', 'Webster', 'WebStripper', 'WebSucker', 'Web Sucker', 'WebWhacker', 'WebZIP', 'WeSEE', 'Whack', 'Whacker', 'Whatweb', 'Who.is Bot', 'Widow', 'WinHTTrack', 'WiseGuys Robot', 'WISENutbot', 'Wonderbot', 'Woobot', 'Wotbox', 'Wprecon', 'WPScan', 'WWW-Collector-E', 'WWW-Mechanize', 'WWW::Mechanize', 'WWWOFFLE', 'x09Mozilla', 'x22Mozilla', 'Xaldon WebSpider', 'Xaldon_WebSpider', 'Xenu', 'xpymep1.exe', 'YoudaoBot', 'Zade', 'Zauba', 'zauba.io', 'Zermelo', 'Zeus', 'zgrab', 'Zitebot', 'ZmEu', 'ZumBot', 'ZyBorg'];
    }
}
