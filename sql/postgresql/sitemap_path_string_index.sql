-- Indice per i LIKE 'prefisso%' su path_string usati dalla sitemap completa
-- (OpenPASitemapBuilder, SitemapSettings.FullSiteMap=auto la attiva se l'indice esiste e valido).
-- CONCURRENTLY: non blocca le scritture su ezcontentobject_tree; non va eseguito in una transazione.
-- Se la creazione fallisce resta un indice non valido: eliminarlo con DROP INDEX CONCURRENTLY e rieseguire.
CREATE INDEX CONCURRENTLY IF NOT EXISTS ezcontentobject_tree_path_string_pattern ON ezcontentobject_tree (path_string varchar_pattern_ops);
