<?php

if (OpenPAINI::variable('Seo', 'EnableRobots') == 'enabled') {
    $result = OpenPAINI::variable('Seo', 'RobotsText', '');
    if (empty($result)) {
        $result = OpenPAINI::variable('Seo', 'DefaultRobotsText', false);
    }
}else{
    $result = "User-agent: * \nDisallow: /";
}

header('Content-Type: text/plain');
echo $result;
eZExecution::cleanExit();
