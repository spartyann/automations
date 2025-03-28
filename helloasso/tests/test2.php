<?php

use Google\Service\Calendar;


require_once(dirname(__DIR__) . '/vendor/autoload.php');


$client = new \Google_Client();
//$client->setApplicationName('Google Calendar API');
$client->setScopes([Calendar::CALENDAR, Calendar::CALENDAR_CALENDARLIST, Calendar::CALENDAR_CALENDARS, Calendar::CALENDAR_EVENTS]);
//$client->setAccessType('offline');
$path = __DIR__.'/yann-tassy-data-d8e970ac75a0.json'; // PATH = chemin physique vers le fichier contenant la clé du compte de service
$client->setAuthConfig($path);


$service = new Calendar($client);

//$list = $service->calendarList->listCalendarList();
//var_dump($list->getItems());
//var_dump($service->calendarList->list);
var_dump($service->calendarList->get('84a7e9d9fc65417060e2f33c6e79812071c2da5f865dd2915d5bad9f704b5063'));
