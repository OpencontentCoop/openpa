<?php

if (OpenPAINI::variable('Seo', 'EnableRobots') == 'enabled') {
    $result = file_get_contents('robots.txt');
}else{
    $result = "User-agent: * \nDisallow: /";
}

header('Content-Type: text/plain');
echo $result;
eZExecution::cleanExit();
