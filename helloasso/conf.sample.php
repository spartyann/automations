<?php


define('SITE_TOKEN', 'xxxxxxxxxxxxx');

define('SPREADSHEET_COMPTA_ID', 'xxxxxx');
define('SPREADSHEET_ADHESION_ID', 'xxxxx');
define('SPREADSHEET_BILLETTERIE_ID', 'xxxxx');

define('SHEET_COMPTA_ID', 0);
define('SHEET_ADHESION_ID', 0);
define('SHEET_BILLETTERIE_ID', 0);

define('COMPTA_LINE_START', 4);
define('ADHESION_LINE_START', 4);
define('BILLETTERIE_LINE_START', 4);

define('TELEGRAM_NOTIFS', [
	// Bot Api Key => [ '<Chat IDS>' ]
	'<api Key>' => [ '00000', '00001']
	]
);

define('GOOGLE_CONF_AUTH', [
	"type" => "service_account",
	"project_id" => "xxxxxx",
	"private_key_id" => "xxxxxx",
	"private_key" => "-----BEGIN PRIVATE KEY-----\nxxxxxxxxxxxxxx\n-----END PRIVATE KEY-----\n",
	"client_email" => "xxxxxxxxx",
	"client_id" => "xxxxx",
	"auth_uri" => "https://accounts.google.com/o/oauth2/auth",
	"token_uri" => "https://oauth2.googleapis.com/token",
	"auth_provider_x509_cert_url" => "https://www.googleapis.com/oauth2/v1/certs",
	"client_x509_cert_url" => "xxxxxxxxxxxxxxxxxxx",
	"universe_domain" => "googleapis.com"
]);
