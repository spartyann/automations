<?php

require_once(__DIR__ . '/vendor/autoload.php');
require_once(__DIR__ . '/conf.php');

use SergiX44\Nutgram\Nutgram;

function sendNotif($msg) {
	try {
		sendTelegramNotif($msg);
	} catch(\Throwable $ex)
	{ }
}

function sendTelegramNotif($msg) {

	foreach (TELEGRAM_NOTIFS as $apiKey => $chatIds)
	{
		$bot = new Nutgram($apiKey);
		foreach($chatIds as $chatId)$bot->sendMessage($msg, $chatId);
	}
	
}

