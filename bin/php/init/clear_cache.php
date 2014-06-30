<?php

$cli->output( 'Svuoto tutte le cache' );
eZContentCacheManager::clearAllContentCache( true );