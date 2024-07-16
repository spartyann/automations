<?php

namespace Config;

// Copy File into "Config.php"
// Rename Config____Sample  => Config


class Config____Sample
{
	public const DEBUG = true;

	public const PAGE_PASSWORD = '6d1gdsfg6n1dfdfg';
	
	public const DOLI_URL = 'https://www.mydolibarr.fr';

	public const DOLI_API_KEY = 'xxxxx';
	public const DOLI_Authorization = 'Basic xxxx';
	public const DOLI_DEFAULT_MODEL_PDF = 'generic_invoice_odt:/home/xxxxxxxx/template_be.odt';
	public const DOLI_AccountId = 1;

	public const CB_PROVIDERS = [
		'stripe' => [ // Mandatory
			'name' => 'Stripe',
			'fixed_fees' => 0.25,
			'fees_percent' => 1.5,
			'doli_stp' => 20 // Id of Dolibarr provider
		],
		'zettle' => [
			'name' => 'Zettle',
			'fixed_fees' => 0,
			'fees_percent' => 1.75,
			'doli_stp' => 43
		],
	
	];


	public const STRIPE_SECRET_KEY = 'xxxx';
	public const STRIPE_ENDPOINT_SECRET = 'xxxxx';

	public const NOTIF_DISCORD_USERNAME = 'Cron job';
	public const NOTIF_DISCORD_WEBHOOK_URL = null;
	public const NOTIF_ERROR_DISCORD_WEBHOOK_URL = null;

	public const NOTIF_SLACK_WEBHOOK_URL = null;
	public const NOTIF_ERROR_SLACK_WEBHOOK_URL = null;
}

