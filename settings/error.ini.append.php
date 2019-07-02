<?php /* #?ini charset="utf-8"?

# Imposta il codice http di risposta a 410 Gone per i contenuti non accessibili all'anonimo affinché i motori di ricerca non ne tengano traccia

[ErrorSettings-kernel]
HTTPError[1]=410

[HTTPError-410]
HTTPName=Gone

*/ ?>