<?php

class OpenPADownloadFilter
{
    public static function addXRobotsTagHeader($params)
    {
        header('X-Robots-Tag: noindex, nofollow, nosnippet, noarchive');
    }
}