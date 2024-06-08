<?php

namespace Config;

// Copy File into "Config.php"
// Rename Config____Sample  => Config

/*
define('DOLI_API_KEY', 'xxxxx');
define('DOLI_Authorization', 'Basic xxxx');
define('DOLI_DEFAULT_MODEL_PDF', 'generic_invoice_odt:/home/xxxxxxxx/template_be.odt');
define('DOLI_AccountId', 1);
define('DOLI_STRIPE_TP_ID', 20);

// Test
define('STRIPE_SECRET_KEY', 'xxxx');
define('STRIPE_ENDPOINT_SECRET', 'xxxxx'); // Test
*/

class Config____Sample
{
	public const DEBUG = true;

	public const NOTIF_DISCORD_USERNAME = 'Cron job';
	public const NOTIF_DISCORD_WEBHOOK_URL = null;
	public const NOTIF_ERROR_DISCORD_WEBHOOK_URL = null;

	public const NOTIF_SLACK_WEBHOOK_URL = null;
	public const NOTIF_ERROR_SLACK_WEBHOOK_URL = null;
}

