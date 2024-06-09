<?php

namespace Config;

// Copy File into "Config.php"
// Rename Config____Sample  => Config


class Config____Sample
{
	public const DEBUG = true;

	
	public const DOLI_URL = 'https://www.mydolibarr.fr';

	public const DOLI_API_KEY = 'xxxxx';
	public const DOLI_Authorization = 'Basic xxxx';
	public const DOLI_DEFAULT_MODEL_PDF = 'generic_invoice_odt:/home/xxxxxxxx/template_be.odt';
	public const DOLI_AccountId = 1;
	public const DOLI_STRIPE_STP_ID = 20;

	public const STRIPE_SECRET_KEY = 'xxxx';
	public const STRIPE_ENDPOINT_SECRET = 'xxxxx';

	public const NOTIF_DISCORD_USERNAME = 'Cron job';
	public const NOTIF_DISCORD_WEBHOOK_URL = null;
	public const NOTIF_ERROR_DISCORD_WEBHOOK_URL = null;

	public const NOTIF_SLACK_WEBHOOK_URL = null;
	public const NOTIF_ERROR_SLACK_WEBHOOK_URL = null;
}

