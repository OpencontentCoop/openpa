<?php

/**
 * robots.txt generato da OpenPACrawlPolicy (openpa.ini [CrawlPolicy]).
 * Struttura fissa: gruppo "User-agent: *", gruppi personalizzati dell'ente, bad bot,
 * una sola riga Sitemap assoluta verso la sitemap pubblica di questo host.
 */

header('Content-Type: text/plain; charset=utf-8');
echo OpenPACrawlPolicy::instance()->renderRobotsTxt();
eZExecution::cleanExit();
