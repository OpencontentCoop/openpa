<?php

function handleLogoDownload(
    $contentObject,
    $contentObjectAttribute
) {
    $version = $contentObject->attribute('current_version');
    $fileInfo = $contentObjectAttribute->storedFileInformation(
        $contentObject,
        $version,
        $contentObjectAttribute->attribute('language_code')
    );
    $fileName = $fileInfo['filepath'];

    $file = eZClusterFileHandler::instance($fileName);

    if ($fileName != "" and $file->exists()) {
        $fileSize = $file->size();
        $fileOffset = 0;
        $contentLength = $fileSize;
        $mimeInfo = $file->dataType();
        $mtime = $file->mtime();
        header("HTTP/1.1 200 OK");
        header('Cache-Control: public, must-revalidate, max-age=259200, s-maxage=259200');
        header("Content-Type: {$mimeInfo}");
        header("Content-Disposition: inline");
        header("Content-Length: $fileSize");
        header('Content-Transfer-Encoding: binary');
        header('Accept-Ranges: bytes');
        header("Pragma: ");
        header("Last-Modified: " . gmdate('D, d M Y H:i:s', $mtime) . ' GMT');
        header("Expires: " . gmdate('D, d M Y H:i:s', time() + 86400) . ' GMT');
        header( "ETag: \"$mtime-$fileSize\"" );

        if (isset($serverVariables['HTTP_IF_MODIFIED_SINCE'])) {
            $value = $serverVariables['HTTP_IF_MODIFIED_SINCE'];
            // strip the garbage prepended by a semicolon used by some browsers
            if (($pos = strpos($value, ';')) !== false) {
                $value = substr($value, 0, $pos);
            }
            if (strtotime($value) <= $mtime) {
                header( "HTTP/1.1 304 Not Modified" );
                eZExecution::cleanExit();
            }
        }

        try {
            header("HTTP/1.1 200 OK");
            header('Cache-Control: public, must-revalidate, max-age=259200, s-maxage=259200');
            $file->passthrough($fileOffset, $contentLength);
        } catch (Exception $e) {
            eZDebug::writeError($e->getMessage(), __METHOD__);
            header($_SERVER["SERVER_PROTOCOL"] . ' 404 Not Found');
        }
        eZExecution::cleanExit();
    }
    return eZBinaryFileHandler::RESULT_UNAVAILABLE;
}

eZSession::stop();
ob_end_clean();

$result = eZBinaryFileHandler::RESULT_UNAVAILABLE;
$home = OpenPaFunctionCollection::fetchHome();
if ($home instanceof eZContentObjectTreeNode) {
    $dataMap = $home->dataMap();
    if (isset($dataMap['logo']) && $dataMap['logo']->hasContent()) {
        $fileHandler = eZBinaryFileHandler::instance();
        $result = handleLogoDownload($home->object(), $dataMap['logo']);
    }
}

if ($result == eZBinaryFileHandler::RESULT_UNAVAILABLE) {
    header("HTTP/1.1 200 OK");
    header('Cache-Control: public, must-revalidate, max-age=259200, s-maxage=259200');
    $fallback = 'AAABAAEAEBAQAAEABAAoAQAAFgAAACgAAAAQAAAAIAAAAAEABAAAAAAAgAAAAAAAAAAAAAAAEAAAAAAAAAD///8AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA';
    header("Content-Type: image/png");
    echo base64_decode($fallback);
}

eZExecution::cleanExit();
