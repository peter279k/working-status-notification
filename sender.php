<?php

use Carbon\Carbon;
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

$logFilePath = './mail_log.txt';

if (file_exists($logFilePath) === true) {
    $contents = file_get_contents($logFilePath);
    $contents = str_replace(PHP_EOL, '', $contents);

    $nowDateString = (string)Carbon::now($timezone);

    if ($contents === $nowDateString) {
        exit('Today mail has been sent');
    }
}

$workingHomeSchedule = new WorkHomeSchedule();
$workingHomeSchedule->startDateStatus = $workingStatus;
$workingHomeSchedule->csvPath = $filePath;
$workingHomeSchedule->csvHead = true;

$workingDate = getTomorrowWorkingDate($timezone, $startDate, $workingHomeSchedule);

$workingStatus = $workingDate['status'];
$workingDateString = (string)$workingDate['date'];

$mailContents = sprintf('<h3>The next working date is "%s" and working status is "%s".</h3>', $workingDateString, $workingStatus);

$senderEmailAddress = getenv('SENDER_EMAIL');
$receivedEmailAddress = getenv('RECEIVED_EMAIL');

if ($senderEmailAddress === false) {
    die('SENDER_EMAIL environment variable is not set');
}

if ($receivedEmailAddress === false) {
    die('RECEIVED_EMAIL environment variable is not set');
}

$sendResultMessage = sendNotificationMail($mailContents, $senderEmailAddress, $receivedEmailAddress);
$sendResultMessage = str_replace(PHP_EOL, '', $sendResultMessage);

if ($sendResultMessage === 'Email sent :)') {
    file_put_contents($logFilePath, (string)Carbon::now($timezone));
}
