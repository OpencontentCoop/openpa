#!/bin/bash
#
# Svuotamento 'hard' della cache
#

VAR_DIR=/home/httpd/openpa.opencontent.it/html/var/
CACHE_DIR=/cache
REMOVECACHE_DIR=/cache_da_rimovere

echo -n "
*** OpenPA Cache Tool ***
Questo script rinomina la cartella cache e la rimuove.
Assicurati che nella configurazione site.ini/FileSettings/VarDir del siteaccess di riferimento, il nome della cartella corrisponda al nome del siteaccess.
Inserisci il nome del siteaccess di cui vuoi rimovere la cache e premi [INVIO]:

*** QUESTO SCRIPT E' IN FASE DI SVILUPPO ***

"
#read siteaccess
#echo -n "Rinomino ${VAR_DIR}$siteaccess${CACHE_DIR} in ${VAR_DIR}$siteaccess${REMOVECACHE_DIR} ? [y/n] "
#read confirm
#echo -n "Rimuovo ${VAR_DIR}$siteaccess${CACHE_DIR} ? [y/n] "
#read remove
