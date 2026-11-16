<?php

/**
 * Logica del workflow event_reindexrelations e del cron index_pending.php.
 *
 * Quando cambia il nome di un oggetto (es. una struttura), gli oggetti che lo citano (es. i
 * servizi) hanno il vecchio nome copiato nel proprio documento Solr (campo opendatastorage
 * e faccette dei filtri): vanno reindicizzati. Gli oggetti da reindicizzare si accodano in
 * ezpending_actions (una riga per oggetto) e li processa il cron index_pending.php.
 *
 * Accorgimenti per non appesantire l'istanza:
 * - si accoda solo se il nome (o uno degli attributi elencati in Attributes[]) e' davvero cambiato
 *   rispetto alla versione precedente (un publish "normale", es. il PATCH notturno di un bot che non
 *   cambia nulla, non accoda niente);
 * - gli oggetti correlati si leggono con una query di soli id, con un tetto massimo
 *   (MaxRelatedObjects), senza caricare gli oggetti;
 * - una riga per oggetto e senza duplicati: lo stesso oggetto non e' mai in coda due volte;
 * - il cron lavora a lotti (MaxPerRun), tiene in coda gli oggetti che Solr non ha accettato
 *   e scarta le richieste piu' vecchie di MaxAgeDays.
 *
 * Nessuna ricorsione: la reindicizzazione non ripubblica gli oggetti, quindi non riattiva il workflow.
 */
class OpenPAReindexRelations
{
    const LOGFILE = 'openpa_workflow_reindexrelations.log';

    const DEFAULT_MAX_RELATED_OBJECTS = 2000;

    const DEFAULT_MAX_PER_RUN = 300;

    const DEFAULT_MAX_AGE_DAYS = 7;

    const MAX_CONSECUTIVE_FAILURES = 3;

    /**
     * @param string $name
     * @param int $default
     * @return int
     */
    public static function intSetting($name, $default)
    {
        $ini = eZINI::instance('openpa.ini');
        if ($ini->hasVariable('ReindexRelations', $name)) {
            $value = (int)$ini->variable('ReindexRelations', $name);
            if ($value > 0) {
                return $value;
            }
        }

        return $default;
    }

    /**
     * Identificatori di classe per cui il workflow agisce (vuoto = tutte).
     *
     * @return string[]
     */
    public static function enabledClasses()
    {
        $ini = eZINI::instance('openpa.ini');
        if ($ini->hasVariable('ReindexRelations', 'Classes')) {
            return array_values(array_filter((array)$ini->variable('ReindexRelations', 'Classes')));
        }

        return array();
    }

    /**
     * Identificatori degli attributi, oltre al nome, la cui modifica fa reindicizzare i citanti
     * (servono se Solr cerca/filtra sui sotto-attributi dell'oggetto citato). Vuoto = solo il nome.
     *
     * @return string[]
     */
    public static function trackedAttributes()
    {
        $ini = eZINI::instance('openpa.ini');
        if ($ini->hasVariable('ReindexRelations', 'Attributes')) {
            return array_values(array_filter((array)$ini->variable('ReindexRelations', 'Attributes')));
        }

        return array();
    }

    /**
     * Va reindicizzato chi cita l'oggetto? Si', se e' cambiato il nome o un attributo elencato in Attributes[].
     *
     * @param int $objectID
     * @param int $currentVersion
     * @return bool
     */
    public static function relevantChange($objectID, $currentVersion)
    {
        if (self::nameChanged($objectID, $currentVersion)) {
            return true;
        }

        return self::trackedAttributesChanged($objectID, $currentVersion, self::trackedAttributes());
    }

    /**
     * Il nome (in una qualunque lingua) e' cambiato rispetto alla versione precedente?
     *
     * Se la versione precedente non c'e' piu' (storico versioni potato) si assume "cambiato":
     * meglio reindicizzare una volta in piu' che lasciare dati sfasati.
     *
     * @param int $objectID
     * @param int $currentVersion
     * @return bool
     */
    public static function nameChanged($objectID, $currentVersion)
    {
        $objectID = (int)$objectID;
        $currentVersion = (int)$currentVersion;
        if ($currentVersion <= 1) {
            return false; // oggetto appena creato: nessuno puo' ancora citarlo con un nome vecchio
        }

        $previousVersion = self::previousVersion($objectID, $currentVersion);
        if ($previousVersion <= 0) {
            return true;
        }

        $current = self::namesByLanguage($objectID, $currentVersion);
        $previous = self::namesByLanguage($objectID, $previousVersion);
        if (empty($previous)) {
            return true;
        }

        return $current != $previous;
    }

    /**
     * Ultima versione archiviata precedente a quella indicata (0 se non c'e' piu').
     *
     * @param int $objectID
     * @param int $currentVersion
     * @return int
     */
    private static function previousVersion($objectID, $currentVersion)
    {
        $rows = eZDB::instance()->arrayQuery(
            "SELECT MAX(version) AS version FROM ezcontentobject_version " .
            "WHERE contentobject_id = " . (int)$objectID . " AND version < " . (int)$currentVersion .
            " AND status = " . eZContentObjectVersion::STATUS_ARCHIVED
        );

        return isset($rows[0]['version']) ? (int)$rows[0]['version'] : 0;
    }

    /**
     * Gli attributi indicati (identificatori) sono cambiati rispetto alla versione precedente?
     * Confronta, per lingua, i valori grezzi salvati nel DB (data_text, data_int, data_float):
     * per testo, numeri, date e XML un valore invariato e' identico, quindi niente falsi positivi.
     * Gli identificatori che non esistono nella classe dell'oggetto vengono ignorati.
     *
     * @param int $objectID
     * @param int $currentVersion
     * @param string[] $identifiers
     * @return bool
     */
    public static function trackedAttributesChanged($objectID, $currentVersion, array $identifiers)
    {
        $objectID = (int)$objectID;
        $currentVersion = (int)$currentVersion;
        if (empty($identifiers) || $currentVersion <= 1) {
            return false;
        }

        $db = eZDB::instance();
        $quoted = array();
        foreach ($identifiers as $identifier) {
            $quoted[] = "'" . $db->escapeString($identifier) . "'";
        }
        $attributeRows = $db->arrayQuery(
            "SELECT a.id FROM ezcontentclass_attribute a " .
            "INNER JOIN ezcontentobject o ON o.contentclass_id = a.contentclass_id " .
            "WHERE o.id = $objectID AND a.version = 0 AND a.identifier IN (" . implode(',', $quoted) . ")"
        );
        $attributeIDs = array();
        foreach ($attributeRows as $row) {
            $attributeIDs[] = (int)$row['id'];
        }
        if (empty($attributeIDs)) {
            return false;
        }

        $previousVersion = self::previousVersion($objectID, $currentVersion);
        if ($previousVersion <= 0) {
            return true;
        }

        $current = self::attributeValues($objectID, $currentVersion, $attributeIDs);
        $previous = self::attributeValues($objectID, $previousVersion, $attributeIDs);

        return $current != $previous;
    }

    /**
     * @param int $objectID
     * @param int $version
     * @param int[] $attributeIDs
     * @return array
     */
    private static function attributeValues($objectID, $version, array $attributeIDs)
    {
        $rows = eZDB::instance()->arrayQuery(
            "SELECT contentclassattribute_id, language_code, data_text, data_int, data_float " .
            "FROM ezcontentobject_attribute " .
            "WHERE contentobject_id = " . (int)$objectID . " AND version = " . (int)$version .
            " AND contentclassattribute_id IN (" . implode(',', array_map('intval', $attributeIDs)) . ")"
        );
        $values = array();
        foreach ($rows as $row) {
            $values[$row['contentclassattribute_id'] . '/' . $row['language_code']] =
                $row['data_text'] . '|' . $row['data_int'] . '|' . $row['data_float'];
        }
        ksort($values);

        return $values;
    }

    /**
     * @param int $objectID
     * @param int $version
     * @return array
     */
    private static function namesByLanguage($objectID, $version)
    {
        $rows = eZDB::instance()->arrayQuery(
            "SELECT content_translation, name FROM ezcontentobject_name " .
            "WHERE contentobject_id = " . (int)$objectID . " AND content_version = " . (int)$version
        );
        $names = array();
        foreach ($rows as $row) {
            $names[$row['content_translation']] = $row['name'];
        }
        ksort($names);

        return $names;
    }

    /**
     * Id degli oggetti pubblicati che citano $objectID (qualunque tipo di relazione, come
     * eZContentObject::reverseRelatedObjectList con tutte le relazioni), senza caricarli.
     *
     * @param int $objectID
     * @param int $limit se la lista supera il limite viene troncata (vedi $truncated)
     * @param bool $truncated
     * @return int[]
     */
    public static function reverseRelatedIDs($objectID, $limit, &$truncated = false)
    {
        $objectID = (int)$objectID;
        $limit = max(1, (int)$limit);
        $rows = eZDB::instance()->arrayQuery(
            "SELECT DISTINCT l.from_contentobject_id AS id " .
            "FROM ezcontentobject_link l " .
            "INNER JOIN ezcontentobject o ON o.id = l.from_contentobject_id " .
            "AND o.current_version = l.from_contentobject_version " .
            "AND o.status = " . eZContentObject::STATUS_PUBLISHED . " " .
            "WHERE l.to_contentobject_id = $objectID AND l.from_contentobject_id <> $objectID " .
            "ORDER BY l.from_contentobject_id",
            array('offset' => 0, 'limit' => $limit + 1)
        );
        $ids = array();
        foreach ($rows as $row) {
            $ids[] = (int)$row['id'];
        }
        $truncated = count($ids) > $limit;
        if ($truncated) {
            $ids = array_slice($ids, 0, $limit);
        }

        return $ids;
    }

    /**
     * Accoda gli oggetti da reindicizzare, una riga per oggetto e senza duplicati.
     *
     * @param int[] $ids
     * @return int numero di righe aggiunte
     */
    public static function queue(array $ids)
    {
        $ids = array_values(array_unique(array_map('intval', $ids)));
        if (empty($ids)) {
            return 0;
        }

        $db = eZDB::instance();
        $key = $db->escapeString(OpenPABase::PENDING_ACTION_INDEX_OBJECTS);
        $already = array();
        foreach (array_chunk($ids, 500) as $chunk) {
            $rows = $db->arrayQuery(
                "SELECT param FROM ezpending_actions WHERE action = '$key' AND param IN ('" . implode("','", $chunk) . "')"
            );
            foreach ($rows as $row) {
                $already[(int)$row['param']] = true;
            }
        }

        $now = time();
        $values = array();
        foreach ($ids as $id) {
            if (!isset($already[$id])) {
                $values[] = "('$key', $now, '$id')";
            }
        }
        if (empty($values)) {
            return 0;
        }

        $db->begin();
        foreach (array_chunk($values, 500) as $chunk) {
            $db->query("INSERT INTO ezpending_actions (action, created, param) VALUES " . implode(',', $chunk));
        }
        $db->commit();

        return count($values);
    }

    /**
     * Elabora un lotto di richieste in coda.
     *
     * Una riga si cancella solo dopo che l'oggetto e' stato reindicizzato (o non esiste piu', o
     * non ha un nodo principale e quindi non e' indicizzabile). Se l'indicizzazione fallisce la riga
     * resta in coda per il prossimo giro; dopo MAX_CONSECUTIVE_FAILURES errori di fila il lotto si ferma
     * (Solr probabilmente non risponde: inutile insistere).
     * Le righe nel vecchio formato (piu' id separati da "-") si consumano a pezzi.
     *
     * @param callable $indexer function(int $objectID): bool|null  true = indicizzato, null = niente da indicizzare, false = errore
     * @param int $maxObjects
     * @param int $maxAgeDays
     * @return array indexed, skipped, failed, expired, remaining, stopped
     */
    public static function processPending($indexer, $maxObjects, $maxAgeDays)
    {
        $db = eZDB::instance();
        $key = $db->escapeString(OpenPABase::PENDING_ACTION_INDEX_OBJECTS);
        $stats = array('indexed' => 0, 'skipped' => 0, 'failed' => 0, 'expired' => 0, 'remaining' => 0, 'stopped' => false);

        $limitTime = time() - ((int)$maxAgeDays * 86400);
        $expired = $db->arrayQuery(
            "SELECT COUNT(*) AS count FROM ezpending_actions WHERE action = '$key' AND created IS NOT NULL AND created < $limitTime"
        );
        if (isset($expired[0]['count']) && (int)$expired[0]['count'] > 0) {
            $stats['expired'] = (int)$expired[0]['count'];
            $db->query("DELETE FROM ezpending_actions WHERE action = '$key' AND created IS NOT NULL AND created < $limitTime");
        }

        $entries = $db->arrayQuery(
            "SELECT id, param FROM ezpending_actions WHERE action = '$key' ORDER BY id",
            array('offset' => 0, 'limit' => max(1, (int)$maxObjects))
        );

        $processed = 0;
        $consecutiveFailures = 0;
        foreach ($entries as $entry) {
            $entryID = (int)$entry['id'];
            $pending = array_values(array_filter(explode('-', (string)$entry['param']), 'strlen'));
            $kept = array();

            foreach ($pending as $position => $objectID) {
                if ($processed >= $maxObjects || $stats['stopped']) {
                    $kept = array_merge($kept, array_slice($pending, $position));
                    break;
                }
                $processed++;
                $result = call_user_func($indexer, (int)$objectID);
                if ($result === true) {
                    $stats['indexed']++;
                    $consecutiveFailures = 0;
                } elseif ($result === null) {
                    $stats['skipped']++;
                } else {
                    $stats['failed']++;
                    $kept[] = $objectID;
                    $consecutiveFailures++;
                    if ($consecutiveFailures >= self::MAX_CONSECUTIVE_FAILURES) {
                        $stats['stopped'] = true;
                    }
                }
            }

            if (empty($kept)) {
                $db->query("DELETE FROM ezpending_actions WHERE id = $entryID");
            } elseif (count($kept) != count($pending)) {
                $db->query(
                    "UPDATE ezpending_actions SET param = '" . $db->escapeString(implode('-', $kept)) . "' WHERE id = $entryID"
                );
            }

            if ($stats['stopped']) {
                break;
            }
        }

        $remaining = $db->arrayQuery("SELECT COUNT(*) AS count FROM ezpending_actions WHERE action = '$key'");
        $stats['remaining'] = isset($remaining[0]['count']) ? (int)$remaining[0]['count'] : 0;

        return $stats;
    }

    /**
     * @param string $message
     */
    public static function log($message)
    {
        eZLog::write($message, self::LOGFILE);
    }
}
