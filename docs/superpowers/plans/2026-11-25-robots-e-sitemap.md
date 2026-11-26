# robots.txt e sitemap coerenti, complete e configurabili — Implementation Plan

> **Stato (2026-11-25):** Fasi 1–3 implementate sul branch `feature/robots-sitemap-crawl-policy` (non committate). Scelte fatte in implementazione: `FullSiteMap=auto` (sitemap completa solo se il DB ha l'indice `*_pattern_ops` su `path_string`, nessuna configurazione per tenant); riga `Sitemap:` omessa se `ShowSitemap` è disabilitato; audit del campo robots dentro `bin/php/check_crawl.php` invece di uno script separato; `Disallow` statici `/Classificazioni/` e `/Media/` mantenuti accanto a quelli da remote id per i siti con alberi non standard. Fase 4 (lingue) non implementata. Sostituisce e assorbe `2026-08-31-sitemap-paginata-e-log.md`.

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** ogni sito OpenCity/OpenPA espone un `robots.txt` con una sola riga `Sitemap:` assoluta https verso `/sitemap.xml`, regole calcolate dal contenuto (non path hardcoded) e una sitemap (`sitemapindex`) completa per i contenuti pubblici, con `lastmod` affidabile e **nessun URL vietato dal robots.txt**. Vale per tutti gli enti; le differenze sono configurazione, non codice.

**Architecture:** una nuova classe `OpenPACrawlPolicy` è l'unica fonte delle regole (Allow/Disallow, prefissi siteaccess, URL base). `robots.php` la rende in testo; `sitemap.php` la usa per escludere i sottoalberi vietati in SQL e come guardia finale (`isAllowed()`) su ogni `<loc>`. La coerenza robots/sitemap è quindi per costruzione. La sitemap diventa sempre un `sitemapindex` con sitemap figlie per sezione, paginate per keyset su `node_id`.

**Tech Stack:** PHP procedurale/classi eZ Publish Legacy, PostgreSQL via `eZDB`, `DOMDocument`, `eZClusterFileHandler` per la cache, `eZLog` per i log, script CLI `eZScript` in `bin/php/`.

## Contesto e cause (riassunto dell'analisi)

- **Pisa dichiara `Sitemap: …/backend/sitemap.xml`:** il ciclo su `HostUriMatchMapItems` in `modules/robots.txt/robots.php` emette una riga per ogni siteaccess "fratello"; la guardia del fix `7793bfb` confronta `$accessSuffix` (`'/backend'`) con `'backend'` e non scatta mai (stesso bug sull'`Allow` calendar). Il frontend di Pisa probabilmente è mappato via `HostMatchMapItems` e non compare nel ciclo, quindi resta solo la riga del backend, che risponde 200 con la pagina di login.
- **Vicopisano/Bugliano senza Sitemap:** nessuna voce `HostUriMatchMapItems` per il loro host → il ciclo non emette nulla. La riga Sitemap dipende dalla mappatura dei siteaccess, mentre deve dipendere solo dall'host.
- **Livorno in http:** `eZURI::transformURI(…, 'full')` usa `eZSys::isSSLNow()`, che dipende da `X-Forwarded-Proto`.
- **Sitemap da 284 URL:** `SitemapSettings/FullSiteMap` è `disabled` di default → solo albero dei menu. Con `FullSiteMap` attivo: `LIMIT 5000` silenzioso, filtro fisso `section_id = 1`, `INNER JOIN` obbligatoria sullo stato `privacy/public`, collocazioni secondarie duplicate.
- **Sitemap con URL vietati:** la sitemap espande `faq_system` (sotto Media, nodo 43 → `/Media/Domande-frequenti`) mentre il robots vieta `/Media/` e consente solo `/Media/FAQ` (hardcoded, nome non standard). Segnalati anche URL di Classificazioni: via da individuare (vedi Domande aperte), il filtro per sottoalbero li esclude comunque.
- **`/api` bloccato in blocco:** il crawler ha bisogno di `/api/opendata/v2/*` e `/opendata/api/*` (endpoint in sola lettura, comunque soggetti ai permessi eZ).
- **Altri difetti di `robots.php`:** `array_unique` sulle righe (può togliere righe da gruppi diversi), direttive non standard commentate (`# Request-rate`, `# Visit-time`), regole frontend/backend mescolate, righe `Sitemap:` arbitrarie nel testo personalizzato dell'ente.
- **Bug minore in `sitemap.php`:** `ShowSitemap` è letto dal gruppo `SiteMapSettings`, tutte le altre chiavi da `SitemapSettings` (da verificare se `eZINI` è case-sensitive sui gruppi: se sì sono due gruppi diversi).

## Vincoli del crawler interno (open-city-crawler 1.2.0)

- Legge le sitemap dichiarate in robots.txt e prova `/sitemap.xml` **solo se il robots non ne dichiara nessuna** → una riga sbagliata fa perdere quella giusta. **Priorità massima.**
- Segue `sitemapindex` ricorsivamente (anti-ciclo), legge `.gz`, max 50 MB per sitemap (anche decompressa), max 1000 sitemap per scansione.
- Ignora le estensioni Google (image, video, news, hreflang): contano solo i `<loc>` figli diretti di `<url>`/`<sitemap>`.
- Scansiona prima gli URL con `lastmod` più recente.
- Applica robots.txt anche agli URL della sitemap.
- I limiti anti spider trap valgono solo per i link trovati nelle pagine, non per le voci di sitemap → la sitemap completa rende i siti grandi indipendenti da quei limiti.

## Riferimenti dall'installer (`vendor/opencity-labs/opencity-installer`)

| Nodo | Identificazione | Posizione | Regola |
|---|---|---|---|
| Classificazioni | remote id `11ee98a24f1921081236b6fb080eea24` | figlio della radice, `privacy.private` | Disallow + fuori sitemap |
| Argomenti | remote id `topics` | figlio della radice | indicizzato (radice sitemap) |
| Domande frequenti | remote id `faq_system` | figlio di Media (nodo 43) | Allow + in sitemap |
| Media | `content.ini [NodeSettings] MediaRootNode` (43) | radice Media | Disallow |
| Radici menu | `management`, `news`, `all-services`, `live` | figli della radice | indicizzati |

## Global Constraints

- **Nessuna soluzione per singolo ente:** tutto in `openpa.ini` (default) con override ini per tenant dove serve.
- **Continuità UX:** nessun cambio all'interfaccia redazionale nelle fasi 1–3. Il campo "robots" dell'ente resta; cambia solo come viene interpretato (vedi Task 1.3).
- **Compatibilità chiavi ini:** non rinominare `SitemapSettings/*` esistenti (`TrasaprenzaRemoteId` compreso, refuso incluso). `ContentNodesLimit` cambia semantica in "dimensione di pagina" come da piano precedente.
- **PostgreSQL only** (`DISTINCT ON` già in uso): nessuna compatibilità MySQL.
- **Nessun test automatico esistente per i moduli:** verifica con `curl` sullo stack Docker locale (`sito-comunale-dev`) e con lo script CLI introdotto al Task 2.5. La logica pura di `OpenPACrawlPolicy` (matching RFC 9309) è testabile in isolamento: si aggiunge un test PHPUnit se l'harness di `tests/` lo consente, altrimenti uno script di verifica.
- **Nuove classi:** dopo averle aggiunte rigenerare gli autoload (`php bin/php/ezpgenerateautoloads.php -e`) e verificare che `var/autoload/ezp_extension.php` le includa.
- **Niente commit/push/deploy** finché il piano non è approvato.

---

## Fase 1 — robots.txt corretto (rilascio immediato, nessun prerequisito)

Effetto atteso: tutti gli enti dichiarano `Sitemap: https://<host>/sitemap.xml`; FAQ consentite al loro path reale; Classificazioni e Media vietati per nodo; API opendata accessibili; sitemap attuale (anche parziale) coerente con il robots.

### Task 1.1: configurazione di default in `openpa.ini`

**Files:** Modify `settings/openpa.ini`

- [ ] **Step 1:** aggiungere il gruppo `[CrawlPolicy]`:

```ini
[CrawlPolicy]
# Schema usato per URL assoluti in robots.txt e sitemap (indipendente da X-Forwarded-Proto)
Scheme=https
# Prefissi di siteaccess (terza colonna di HostUriMatchMapItems, seconda = uri) da considerare non pubblici
PrivateUriList[]
PrivateUriList[]=backend
PrivateUriList[]=debug
# Regole statiche del gruppo User-agent: * (ripetute per ogni prefisso pubblico dello stesso host)
DisallowPaths[]
DisallowPaths[]=/api/
DisallowPaths[]=/opendata/
DisallowPaths[]=/user
DisallowPaths[]=/layout/
DisallowPaths[]=/content/search
DisallowPaths[]=/content/advancedsearch
DisallowPaths[]=/content/view
DisallowPaths[]=/content/keyword
DisallowPaths[]=/*/(tag)/
AllowPaths[]
AllowPaths[]=/api/opendata/v2/
AllowPaths[]=/opendata/api/
# Regole calcolate dai nodi (path = URL alias del nodo, in tutte le lingue del sito)
DisallowSubtreeRemoteIds[]
DisallowSubtreeRemoteIds[]=11ee98a24f1921081236b6fb080eea24
# true = aggiunge content.ini [NodeSettings] MediaRootNode
DisallowMediaRoot=true
AllowSubtreeRemoteIds[]
AllowSubtreeRemoteIds[]=faq_system
# Rate limiting: solo Crawl-delay (Request-rate e Visit-time non sono standard)
CrawlDelay=3
```

- [ ] **Step 2:** verificare che la sintassi `Name[]` vuota azzeri l'array anche negli override per tenant (convenzione eZ già usata in `[Seo] DisabledDomainList[]`).

### Task 1.2: classe `OpenPACrawlPolicy`

**Files:** Create `classes/openpacrawlpolicy.php`

Responsabilità:

- `instance()`: singleton per richiesta.
- `isRobotsEnabled(): bool`: `OpenPAINI::variable('Seo', 'EnableRobots') === 'enabled'` (include già `DisabledDomainList`).
- `getPublicPrefixes(): string[]`: `''` + gli uri di `HostUriMatchMapItems` con **host uguale a `eZSys::hostname()`**, siteaccess che inizia con `OpenPABase::getCurrentSiteaccessIdentifier() . '_'` e uri non in `PrivateUriList`.
- `getPrivatePrefixes(): string[]`: idem ma uri in `PrivateUriList` (confronto su `trim($uri, '/')`, **senza barra**: è il bug di oggi).
- `getGroupRules(): array<[type, path]>`: regole del gruppo `*` nell'ordine di emissione:
  1. per ogni prefisso privato: `Disallow: <prefix>/` (es. `/backend/`, `/debug/`);
  2. per ogni prefisso pubblico: `DisallowPaths`, `AllowPaths`, poi le regole da nodo (`Disallow: <prefix><alias>/` e `<prefix><alias>$` per i sottoalberi vietati, `Allow: <prefix><alias>` per quelli consentiti);
  3. archivio se `Seo/DisableArchive` (`/archivio`, `/archivio_backend`, `/archive`, `/old`, come oggi);
  4. regole Allow/Disallow aggiuntive dell'ente (Task 1.3).
  Deduplica **solo dentro il gruppo** (chiave `type|path`).
- `getExtraGroups(): string`: gruppi `User-agent` diversi da `*` scritti dall'ente, copiati così come sono (Task 1.3).
- `isAllowed(string $path): bool`: matching RFC 9309 sulle regole del gruppo `*`: tra le regole che fanno match vince la più lunga (lunghezza del pattern), a parità vince Allow; `*` = qualunque sequenza, `$` finale = fine path; nessun match → consentito.
- `getExcludedSubtreePathStrings(): string[]` e `getIncludedSubtreePathStrings(): string[]`: `path_string` dei nodi da `DisallowSubtreeRemoteIds`/Media e da `AllowSubtreeRemoteIds`, per i filtri SQL della sitemap.
- `absoluteUrl(string $path): string`: `Scheme . '://' . eZSys::hostname() . $path`.
- `getSitemapUrl(): string`: `absoluteUrl('/sitemap.xml')`.

Calcolo degli alias per nodo:

```php
// $node: eZContentObjectTreeNode; restituisce i path (es. '/Media/Domande-frequenti') in tutte le lingue del sito
private function nodePaths(eZContentObjectTreeNode $node): array
{
    $ids = array_values(array_filter(explode('/', $node->attribute('path_string'))));
    array_shift($ids); // nodo 1
    $paths = [];
    foreach (eZContentLanguage::fetchList() as $language) {
        $path = eZURLAliasML::fetchPathByActionList('eznode', $ids, $language->attribute('locale'));
        if ($path) {
            $paths['/' . trim($path, '/')] = true;
        }
    }
    return array_keys($paths);
}
```

- [ ] **Step 1:** scrivere la classe; il risultato (regole e path_string) va in cache per richiesta; valutare cache su file con chiave host+lingue se la misura del Task 1.5 lo richiede.
- [ ] **Step 2:** verificare il comportamento di `fetchPathByActionList` sulla radice swappata (`swap_with: $root_node` nell'installer): il path di `faq_system` deve uscire `Media/Domande-frequenti`, quello di Classificazioni `Classificazioni`.
- [ ] **Step 3:** percent-encoding degli alias con caratteri non ASCII (in genere gli alias eZ sono già normalizzati; controllare comunque `rawurlencode` per segmento).
- [ ] **Step 4:** test di `isAllowed()` con casi fissi:
  - `/Media/Domande-frequenti/Tributi` → true; `/Media/Immagini/x` → false
  - `/Classificazioni` e `/Classificazioni/Orari` → false; `/Argomenti/Ambiente` → true
  - `/api/opendata/v2/content/search/…` → true; `/api/openapi` → false; `/api/opendata/v1/…` → false
  - `/opendata/api/calendar/…` → true; `/opendata/console` → false
  - `/backend/…` → false; `/en/Media/Domande-frequenti` → true (con prefisso `en` pubblico)

### Task 1.3: testo personalizzato dell'ente

**Files:** `classes/openpacrawlpolicy.php`

Oggi `Seo/RobotsText` **sostituisce** il default (file `html/robots.txt`). Nuova interpretazione, senza cambiare la UI:

- righe `Allow`/`Disallow` prima di qualunque `User-agent`, o sotto `User-agent: *` → aggiunte in coda al gruppo `*` (entrano in `isAllowed()`, quindi anche nel filtro sitemap);
- gruppi con altri `User-agent` → copiati in `getExtraGroups()`;
- `Sitemap:` → **ignorate** (unica riga generata);
- `Crawl-delay`, `Request-rate`, `Visit-time` → ignorate (governate da `Seo/RateLimitingRules`);
- righe vuote e commenti → scartati.

Il file `html/robots.txt` della distribuzione non viene più letto (`Seo::DefaultRobotsText` resta per compatibilità ma non è usato): le sue regole sono ora in `[CrawlPolicy]`.

- [ ] **Step 1:** implementare il parser.
- [ ] **Step 2:** script di audit in sola lettura `bin/php/robots_audit.php`: per il siteaccess corrente stampa `RobotsText` dell'ente, le righe ignorate e il robots risultante. Da lanciare su tutti i tenant **prima** del rilascio per trovare enti con regole personalizzate che cambierebbero significato.
- [ ] **Step 3:** verificare nella configurazione del web server che `/robots.txt` sia sempre instradato al modulo e mai servito come file statico da `html/robots.txt`.

### Task 1.4: riscrittura di `robots.php`

**Files:** Modify `modules/robots.txt/robots.php`

```php
<?php

$policy = OpenPACrawlPolicy::instance();
$lines = [];

if (!$policy->isRobotsEnabled()) {
    $lines[] = 'User-agent: *';
    $lines[] = 'Disallow: /';
} else {
    $lines[] = 'User-agent: *';
    foreach ($policy->getGroupRules() as [$type, $path]) {
        $lines[] = "$type: $path";
    }
    if (OpenPAINI::variable('Seo', 'RateLimitingRules', 'disabled') === 'enabled') {
        $lines[] = 'Crawl-delay: ' . (int)OpenPAINI::variable('CrawlPolicy', 'CrawlDelay', 3);
    }
    $extra = $policy->getExtraGroups();
    if ($extra !== '') {
        $lines[] = '';
        $lines[] = $extra;
    }
    $lines[] = '';
    $lines[] = '# The Ultimate robots.txt Bot and User-Agent Blocker Version: V3.2019.12.1186';
    $lines[] = '# Copyright: https://github.com/mitchellkrogza/apache-ultimate-bad-bot-blocker';
    foreach (OpenPACrawlPolicy::badBotUserAgents() as $userAgent) {
        $lines[] = "User-agent: $userAgent";
        $lines[] = 'Disallow: /';
    }
    $lines[] = '';
    $lines[] = 'Sitemap: ' . $policy->getSitemapUrl();
}

header('Content-Type: text/plain; charset=utf-8');
echo implode("\n", $lines) . "\n";
eZExecution::cleanExit();
```

- [ ] **Step 1:** spostare la lista bad bot in `OpenPACrawlPolicy::badBotUserAgents()` (invariata).
- [ ] **Step 2:** verificare che lo user-agent di open-city-crawler **non** faccia match con nessuna voce della lista (i crawler applicano il gruppo con il token più specifico che fa match). La lista contiene `Scrapy`, `Nutch`, `heritrix`, `crawler4j`.
- [ ] **Step 3:** se `SitemapSettings/ShowSitemap` è disabilitato, valutare se omettere la riga `Sitemap:` (il crawler proverebbe comunque `/sitemap.xml`, che sarebbe un `urlset` vuoto: comportamento equivalente; scelta da confermare).

Output atteso (frontend su host con prefissi `''`, `en`, `backend`):

```
User-agent: *
Disallow: /backend/
Disallow: /api/
Disallow: /opendata/
…
Allow: /api/opendata/v2/
Allow: /opendata/api/
Disallow: /Classificazioni/
Disallow: /Classificazioni$
Disallow: /Media/
Disallow: /Media$
Allow: /Media/Domande-frequenti
Disallow: /en/api/
…
Disallow: /archivio
…

# The Ultimate robots.txt Bot …
User-agent: 360Spider
Disallow: /
…

Sitemap: https://www.comune.example.it/sitemap.xml
```

### Task 1.5: coerenza minima della sitemap attuale

**Files:** Modify `modules/sitemap.xml/sitemap.php`

Senza ancora ristrutturare (Fase 2), la sitemap di oggi deve smettere di elencare URL vietati.

- [ ] **Step 1:** nella query dei contenuti aggiungere `AND NOT (t.path_string LIKE '<escluso>%' AND NOT t.path_string LIKE '<incluso>%' …)` usando `getExcludedSubtreePathStrings()` / `getIncludedSubtreePathStrings()`.
- [ ] **Step 2:** guardia finale: prima di scrivere ogni `<url>` (sia voci di menu sia contenuti) `if (!$policy->isAllowed($path)) continue;`.
- [ ] **Step 3:** costruire i `<loc>` con `$policy->absoluteUrl($path)` invece di `eZURI::transformURI(…, 'full')` (https garantito).
- [ ] **Step 4:** leggere `ShowSitemap` da `SitemapSettings` con fallback su `SiteMapSettings`.

### Verifica Fase 1

- [ ] `curl -s https://<sito-locale>/robots.txt`: una sola riga `Sitemap:`, in https, in fondo; nessuna riga commentata di rate limiting; nessuna `Sitemap:` verso `/backend/`.
- [ ] `curl -s https://<sito-locale>/backend/robots.txt` (se raggiungibile): stessa riga Sitemap del frontend.
- [ ] `curl -sI https://<sito-locale>/sitemap.xml`: 200, `application/xml` o `text/xml`; nessun `<loc>` sotto `/Classificazioni` o `/Media/` diverso dalle FAQ.
- [ ] Con un tenant configurato come Pisa (frontend in `HostMatchMapItems`, backend in `HostUriMatchMapItems`) e uno come Bugliano (nessuna voce `HostUriMatchMapItems`): entrambi devono avere la riga Sitemap.
- [ ] Ente con `RobotsText` personalizzato: le sue regole compaiono in coda al gruppo `*` e le sue righe `Sitemap:` spariscono.

---

## Fase 2 — sitemapindex per sezione

Prerequisito: Fase 1. `FullSiteMap` resta configurabile.

### Task 2.1: struttura

**Files:** Create `classes/openpasitemapbuilder.php`; Modify `modules/sitemap.xml/sitemap.php` (diventa un dispatcher sottile)

```
/sitemap.xml                            → <sitemapindex>
/sitemap.xml?section=pagine             → home, voci di menu, Argomenti
/sitemap.xml?section=trasparenza        → sottoalbero TrasaprenzaRemoteId
/sitemap.xml?section=faq                → sottoalbero faq_system
/sitemap.xml?section=contenuti&page=N   → il resto (solo se FullSiteMap)
```

- Parametri in query string sullo stesso script, come `?source`/`?debug` di oggi (nessun nuovo modulo).
- L'indice elenca solo le sezioni non vuote; `contenuti` elenca tante pagine quante servono.
- Ogni `<sitemap>` dell'indice ha `<lastmod>` = massimo dei `lastmod` della sitemap figlia.
- `section`/`page` non validi → 404.

- [ ] **Step 1:** estrarre la costruzione dell'albero menu (`OpenPAMenuTool::getTreeMenu`) e la query contenuti in metodi del builder.
- [ ] **Step 2:** sezione `pagine` = home + albero menu (`NodiCustomMenu`) + albero `topics`; sezione `trasparenza` = albero menu + **tutti** i contenuti del sottoalbero Trasparenza (se `FullSiteMap`); sezione `faq` = sottoalbero `faq_system`; sezione `contenuti` = resto dei sottoalberi delle radici, esclusi quelli già coperti dalle altre sezioni.

### Task 2.2: query contenuti

- [ ] **Step 1:** paginazione keyset: `ORDER BY t.node_id`, pagina N calcolata con `node_id > <ultimo della pagina N-1>`. I confini delle pagine vanno messi in cache insieme al conteggio (una query `SELECT node_id … ORDER BY node_id` con `row_number()` filtrata ogni `PageSize`), così `?page=N` non rifà la scansione delle pagine precedenti.
- [ ] **Step 2:** `ContentNodesLimit` = dimensione di pagina, default 10000 (≈2 MB per pagina: niente `.gz`, basta la compressione HTTP).
- [ ] **Step 3:** solo collocazione principale: `AND t.node_id = t.main_node_id`.
- [ ] **Step 4:** filtro privacy condizionale: `JOIN` sullo stato `privacy/public` solo se `eZContentObjectStateGroup::fetchByIdentifier('privacy')` esiste.
- [ ] **Step 5:** sezioni configurabili: nuova chiave `SitemapSettings/SectionIdentifiers[]` (default `standard`) al posto di `o.section_id = 1`.
- [ ] **Step 6:** esclusione sottoalberi da `OpenPACrawlPolicy` (come Task 1.5) + guardia `isAllowed()` su ogni `<loc>`.
- [ ] **Step 7:** query alias invariata (batch `DISTINCT ON`), già efficiente.

### Task 2.3: `lastmod`

- [ ] contenuti: `o.modified`;
- [ ] voci di menu ed elenchi: `modified_subnode`;
- [ ] home: massimo tra tutti;
- [ ] mai la data di generazione;
- [ ] formato `date('c', …)`;
- [ ] togliere `changefreq` (e non introdurre `priority`).

Limite noto da documentare: pagine di Trasparenza che mostrano elenchi costruiti da ricerche (non dai figli) non aggiornano il proprio `lastmod`; le singole voci sì.

### Task 2.4: HTTP, cache, log

- [ ] `Content-Type: application/xml; charset=utf-8`; header cache come oggi (`CacheTTL`).
- [ ] cache lato server per `(host, section, page)` con `eZClusterFileHandler` e TTL `CacheTTL`, così una scansione non rifà le query a ogni richiesta.
- [ ] 404 se il siteaccess corrente ha uri in `PrivateUriList` (oggi `/backend/sitemap.xml` restituisce la pagina di login con 200).
- [ ] logging durata/volumi query come Task 2 del piano `2026-08-31-sitemap-paginata-e-log.md` (riusarlo così com'è, adattando i nomi delle sezioni).
- [ ] mantenere `?source` e `?debug` per utenti con accesso `setup`.

### Task 2.5: script di verifica `bin/php/check_crawl.php`

In sola lettura, per il siteaccess corrente (lanciabile in batch su tutti i tenant):

- [ ] genera il robots.txt e verifica: esattamente una riga `Sitemap:`, URL assoluto https, nessun path in `PrivateUriList`;
- [ ] scarica l'URL dichiarato (HTTP reale, per includere proxy e cache) e verifica: 200, XML, radice `sitemapindex` o `urlset`;
- [ ] segue l'indice, valida ogni figlia contro lo XSD ufficiale (`sitemap.xsd`/`siteindex.xsd`), conta URL per sezione, verifica ≤ 50.000 URL e ≤ 50 MB per file;
- [ ] verifica `isAllowed()` su ogni `<loc>` e segnala gli URL vietati (atteso: zero);
- [ ] output sintetico per tenant: `OK/KO`, URL per sezione, anomalie.

Questo è il controllo che avrebbe intercettato il caso Pisa.

---

## Fase 3 — sitemap completa di default

Prerequisito: indice `varchar_pattern_ops` su `ezcontentobject_tree.path_string` (piano `ocinstaller/docs/superpowers/plans/2026-09-01-index-path-string-autoinstallante.md`) applicato sui tenant.

- [ ] `SitemapSettings/FullSiteMap=enabled` come default in `openpa.ini`.
- [ ] rilascio a lotti con override ini per tenant (prima QA, poi un lotto di enti grandi con monitoraggio dei log del Task 2.4, poi tutti).
- [ ] lanciare `check_crawl.php` su ogni lotto.

Impatto: URL in sitemap da centinaia a migliaia per ente (Pisa da 284 a qualche migliaio); carico DB contenuto da indice + cache. Nessun impatto su redazione, cittadini, manuali.

## Fase 4 — lingue

- [ ] una sitemap figlia per ogni prefisso pubblico in lingua (`/en`, `/de`), con solo i contenuti tradotti e alias nella lingua del siteaccess; elencate nello stesso indice.
- [ ] niente `xhtml:link`/hreflang in sitemap (il crawler le ignora; eventualmente nell'HTML).

---

## Impatto e rischi

| Cambio | Chi è toccato | Rischio |
|---|---|---|
| Riga Sitemap unica https | tutti | basso: oggi è assente o sbagliata in vari enti |
| Regole da remote id | tutti | basso; da verificare enti con alberi non standard (`check_crawl.php`) |
| FAQ consentite al path reale | tutti | basso: oggi bloccate per errore |
| Allow `/api/opendata/v2/`, `/opendata/api/` | tutti | medio-basso: endpoint in sola lettura; `search` può pesare se un bot lo martella → rate limit al proxy se serve |
| Nuova interpretazione di `RobotsText` | enti con testo personalizzato | medio: audit (Task 1.3 step 2) prima del rilascio |
| Sitemap completa | tutti, a lotti | medio sul DB: mitigato da indice, cache, lotti |

Compliance: si espongono solo contenuti già pubblici (stato `privacy/public`, sezioni configurate); nessun dato personale aggiuntivo; nessun impatto QC2/GDPR/NIS2 atteso. Nessuna nuova tecnologia né servizio esterno.

## Domande aperte

1. **Sezione eZ dei contenuti di Trasparenza:** se non tutti in `standard`, aggiungerla al default di `SectionIdentifiers`.
2. **Classificazioni oggi in sitemap:** serve un URL di esempio e l'ente, oppure `sitemap.xml?source`, per capire la via d'ingresso (voce in `NodiCustomMenu`? contenuti redazionali con stato `public`?). Il filtro per sottoalbero li esclude comunque.
3. **User-agent di open-city-crawler:** verifica contro la lista bad bot (Task 1.4 step 2).
4. **Bot AI** (GPTBot, ClaudeBot, CCBot…): oggi CCBot è bloccato per caso dalla lista bad bot, gli altri no. Serve una scelta esplicita e configurabile (`[CrawlPolicy]`), fuori da questo piano.
5. **Siteaccess `old`/`new`** presenti in alcune mappature (es. Bugliano QA): pubblici o da aggiungere a `PrivateUriList`? `/old` è già coperto da `DisableArchive`.
6. **`ShowSitemap` disabilitato:** omettere o mantenere la riga `Sitemap:` (Task 1.4 step 3).
7. **Livorno http:** verificare che il proxy passi `X-Forwarded-Proto`; con `Scheme=https` il robots e la sitemap sono comunque corretti, ma altri URL assoluti generati da eZ restano esposti al problema.
