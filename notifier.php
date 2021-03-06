<?php

use Carbon\Carbon;
use Lee\WorkHomeSchedule;
use Mailjet\Resources;
use Mailjet\Client;

function getTomorrowWorkingDate(string $timezone, string $startDate, WorkHomeSchedule $workHomeSchedule) {
    $startDateCarbon = Carbon::parse($startDate, $timezone);
    $days = Carbon::now($timezone)->addDay()->diffInDays($startDateCarbon);
    $workHomeSchedule = $workHomeSchedule->loadCalendarData();
    $calendars = $workHomeSchedule->calendars;

    $dayRange = range(1, $days);
    foreach ($dayRange as $value) {
        $startDateString = $startDateCarbon->format($workHomeSchedule->dateFormat);
        foreach ($calendars as $calendar) {
            $dateString = $calendar['date']->format($workHomeSchedule->dateFormat);
            if ($dateString === $startDateString) {
                if ($calendar['is_holiday'] === '1') {
                    $days -= 1;
                }

                $startDateCarbon->addDay();
                break;
            }
        }
    }

    $workHomeSchedule->workingDays = $days;

    Carbon::mixin($workHomeSchedule);
    $currentDate = Carbon::create($startDate);

    $nextWorkingDates = $currentDate->nextWorkingDates();

    return end($nextWorkingDates);
}

function sendNotificationMail(string $mailContents, string $senderEmail, string $receivedEmail) {
    $apiKey = getenv('MJ_APIKEY_PUBLIC');
    $apiSecret = getenv('MJ_APIKEY_PRIVATE');

    if ($apiKey === false) {
        return 'MAILJET API key is not set';
    }

    if ($apiKey === false) {
        return 'MAILJET API secret key is not set';
    }

    $senderEmailValidation = filter_var($senderEmail, FILTER_VALIDATE_EMAIL);
    $receivedEmailValidation = filter_var($receivedEmail, FILTER_VALIDATE_EMAIL);

    if ($senderEmailValidation === false) {
        return 'invalid email format for sender email';
    }

    if ($receivedEmailValidation === false) {
        return 'invalid email format for received email';
    }

    $mailJet = new Client($apiKey, $apiSecret, true, ['version' => 'v3.1']);

    $body = [
        'Messages' => [
            [
                'From' => [
                    'Email' => $senderEmail,
                    'Name' => explode('@', $senderEmail)[0],
                ],
                'To' => [
                    [
                        'Email' => $receivedEmail,
                        'Name' => explode('@', $receivedEmail)[0],
                    ]
                ],
                'Subject' => "My Working Status Notification",
                'TextPart' => $mailContents,
                'HTMLPart' => $mailContents,
            ]
        ]
    ];

    $response = $mailJet->post(Resources::$Email, ['body' => $body]);

    if ($response->success() === true) {
        return 'Email sent :)' . PHP_EOL;
    }

    return 'Status code: ' . $response->getStatus() . PHP_EOL . 'Reason phrase: ' . $response->getReasonPhrase();
}
