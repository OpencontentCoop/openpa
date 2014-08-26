<?php

$result = file_get_contents( 'robots.txt' );

header('Content-Type: text/plain');
echo $result;
eZExecution::cleanExit();