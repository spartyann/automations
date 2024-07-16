<?php


require_once('vendor/autoload.php');

use App\DoliApi;
use App\Notifications\SlackHelper;
use App\Tools;
use App\WFDoliCBPayment;
use App\WFDoliStripe;
use Config\Config;

if (Tools::isCommandLineInterface() == false) 
{
	echo "Only on CLI";
	exit(0);
}

$invoiceLines= [
	[
		'desc' => "Acompte Formation Reiki HF",
		'subprice' => 300,
		'tva_tx' => 0,
		'qty' => 1
	],
	[
		'desc' => "Solde Formation Reiki HF",
		'subprice' => 600,
		'tva_tx' => 0,
		'qty' => 1
	],
];

try{

	WFDoliCBPayment::processDoliInvoiceForCBPayment("Yann Tassy", 'tassy.yann@gmail.com', $invoiceLines, 900, time(),  "CODE STRIPE", Config::CB_PROVIDERS['stripe'], 10.25);
} catch (\Throwable $ex)
{

	dd($ex);
}

//SlackHelper::sendError("testdf gsdg", "msg dfgsdfgsdfg sdfg sd");
//$stripe = new \Stripe\StripeClient(Config::STRIPE_SECRET_KEY);


/*
//dd(getInvoicesById());
//$tps = getClientsByEmail();
$tiers = DoliApi::getOrCreateClient('Api Test', 'test@mail.com');
$date = (new \DateTime('2024-06-05'))->getTimestamp();
$i = DoliApi::createInvoices($tiers->id, "Séance de Reiki", 60, 0, 1, $date);
$i = DoliApi::createPayment($i->id, $date, 7, "COMMENT", '$numPayment', '1111', '2222');
*/

dd($i);



