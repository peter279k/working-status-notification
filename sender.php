<?php

use Lee\WorkHomeSchedule;

$autoloadFilePath = __DIR__ . '/vendor/autoload.php';

if (file_exists($autoloadFilePath) === false) {
    $autoloadFilePath = './notifier.php';
}

require_once $autoloadFilePath;

$filePath = getenv('FILE_PATH');
if ($filePath === false) {
    die('FILE_PATH environment variable is not set');
}

$workingStatus = getenv('WORKING_STATUS');
if ($workingStatus === false) {
    die('WORKING_STATUS environment variable is not set');
}

$timezone = getenv('TIMEZONE');
if ($timezone === false) {
    die('TIMEZONE environment variable is not set');
}

$startDate = getenv('START_DATE');
if ($startDate === false) {
    die('START_DATE environment variable is not set');
}

$workingHomeSchedule = new WorkHomeSchedule();
$workingHomeSchedule->startDateStatus = $workingStatus;
$workingHomeSchedule->csvPath = $filePath;
$workingHomeSchedule->csvHead = true;

$workingDate = getTomorrowWorkingDate($timezone, $startDate, $workingHomeSchedule);

$workingStatus = $workingDate['status'];
$workingDateString = (string)$workingDate['date'];

$mailContents = sprintf('<h3>Tomorrow is "%s" and working status is "%s".</h3>', $workingDateString, $workingStatus);

$senderEmailAddress = getenv('SENDER_EMAIL');
$receivedEmailAddress = getenv('RECEIVED_EMAIL');

if ($senderEmailAddress === false) {
    die('SENDER_EMAIL environment variable is not set');
}

if ($receivedEmailAddress === false) {
    die('RECEIVED_EMAIL environment variable is not set');
}

echo sendNotificationMail($mailContents, $senderEmailAddress, $receivedEmailAddress), PHP_EOL;
