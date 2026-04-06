<?php
require 'autoload.php';

$cli = eZCLI::instance();
$script = eZScript::instance(array('description' => ("Migrate dfs"),
    'use-session' => false,
    'use-modules' => true,
    'use-extensions' => true));

$script->startup();

$options = $script->getOptions();
$script->initialize();
$script->setUseDebugAccumulators(true);

$user = eZUser::fetchByName('admin');
eZUser::setCurrentlyLoggedInUser($user, $user->attribute('contentobject_id'));

function writeLog($name, $logName)
{
    $ini = eZINI::instance();
    $varDir = $ini->variable('FileSettings', 'VarDir');
    $logDir = $ini->variable('FileSettings', 'LogDir');
    $fileName = $varDir . '/' . $logDir . '/' . $logName;
    $oldumask = @umask(0);

    clearstatcache(true, $fileName);
    $fileExisted = file_exists($fileName);
    if ($fileExisted and
        filesize($fileName) > eZLog::maxLogSize()) {
        if (eZLog::rotateLog($fileName))
            $fileExisted = false;
    } else if (!$fileExisted and !file_exists($varDir . '/' . $logDir)) {
        eZDir::mkdir($varDir . '/' . $logDir, false, true);
    }

    $logFile = @fopen($fileName, "a");
    if ($logFile) {
        $time = strftime("%b %d %Y %H:%M:%S", strtotime("now"));
        $logMessage = "[ " . $time . " ] " . $name . "\n";
        @fwrite($logFile, $logMessage);
        @fclose($logFile);
        if (!$fileExisted) {
            $permissions = octdec($ini->variable('FileSettings', 'LogFilePermissions'));
            @chmod($fileName, $permissions);
        }
        @umask($oldumask);
    } else {
        eZDebug::writeError('Couldn\'t create the log file "' . $fileName . '"', __METHOD__);
    }
}

function filePathForBinaryFile($fileName, $mimeType)
{
    $storageDir = eZSys::storageDirectory();
    list($group, $type) = explode('/', $mimeType);
    $filePath = $storageDir . '/original/' . $group . '/' . $fileName;
    return $filePath;
}

function copyToDFS(OpenPADFSFileHandlerDFSDispatcher $dispatcher, $mountPointPath, $filePath, $message)
{
    global $cli;

    if (!$dispatcher->existsOnDFS($filePath)) {
        if (file_exists($mountPointPath . $filePath)) {
            $cli->warning($message);
            $dispatcher->copyToDFS($mountPointPath . $filePath, $filePath);
        } else {
            $cli->error($message);
            writeLog($filePath, 'cluster_not_found.log');
        }
    } else {
        $cli->output($message);
    }
}

$dispatcher = OpenPADFSFileHandlerDFSDispatcher::build();

$mountPointPath = eZINI::instance('file.ini')->variable('eZDFSClusteringSettings', 'MountPointPath');

if (!$mountPointPath = realpath($mountPointPath))
    throw new eZDFSFileHandlerNFSMountPointNotFoundException($mountPointPath);

if (!is_writeable($mountPointPath))
    throw new eZDFSFileHandlerNFSMountPointNotWriteableException($mountPointPath);

if (substr($mountPointPath, -1) != '/')
    $mountPointPath = "$mountPointPath/";


$db = eZDB::instance();

$cli->output("Migrating images and imagealiases files");
$rows = $db->arrayQuery('select filepath from ezimagefile');
$total = count($rows);
foreach ($rows as $index => $row) {
    if ($row['filepath'] == '') continue;
    $filePath = $row['filepath'];
    $message = "$index/$total - " . $filePath;
    copyToDFS($dispatcher, $mountPointPath, $filePath, $message);
}
$cli->output();

$cli->output("Migrating binary files");
$rows = $db->arrayQuery('select filename, mime_type from ezbinaryfile');
$total = count($rows);
foreach ($rows as $index => $row) {
    if ($row['filename'] == '') continue;
    $filePath = filePathForBinaryFile($row['filename'], $row['mime_type']);
    $message = "$index/$total - " . $filePath;
    copyToDFS($dispatcher, $mountPointPath, $filePath, $message);
}
$cli->output();

$cli->output("Migrating media files");
$rows = $db->arrayQuery('select filename, mime_type from ezmedia');
$total = count($rows);
foreach ($rows as $index => $row) {
    if ($row['filename'] == '') continue;
    $filePath = filePathForBinaryFile($row['filename'], $row['mime_type']);
    $message = "$index/$total - " . $filePath;
    copyToDFS($dispatcher, $mountPointPath, $filePath, $message);
}
$cli->output();

try {
    $cli->output("Migrating ezflowmedia files");
    $rows = $db->arrayQuery('select filename, mime_type from ezflowmedia');
    $total = count($rows);
    foreach ($rows as $index => $row) {
        if ($row['filename'] == '') continue;
        $filePath = filePathForBinaryFile($row['filename'], $row['mime_type']);
        $message = "$index/$total - " . $filePath;
        copyToDFS($dispatcher, $mountPointPath, $filePath, $message);
    }
    $cli->output();
} catch (eZDBException $e) {
}

try {
    $cli->output("Migrating survey files");
    $rows = $db->arrayQuery("SELECT text FROM ezsurveyquestionresult WHERE question_id IN (SELECT id FROM ezsurveyquestion WHERE type = 'File')");
    $total = count($rows);
    foreach ($rows as $index => $row) {
        $filePath = $row['text'];
        $message = "$index/$total - " . $filePath;
        copyToDFS($dispatcher, $mountPointPath, $filePath, $message);
    }
    $cli->output();
} catch (eZDBException $e) {
}

if (class_exists('FlipMegazine')) {
    $cli->output("Migrating ezflip files");
    $var = FlipMegazine::getFlipVarDirectory();
    $fileList = array();
    eZDir::recursiveList($var, $var, $fileList);
    $total = count($fileList);
    foreach ($fileList as $index => $file) {
        if ($file['type'] == 'file') {
            $suffix = eZFile::suffix($file['name']);
            if ($suffix != 'txt' && $suffix != 'pdf') {
                $filePath = $file['path'] . '/' . $file['name'];
                $message = "$index/$total - " . $filePath;
                copyToDFS($dispatcher, $mountPointPath, $filePath, $message);
            }
        }
    }
}

$script->shutdown();