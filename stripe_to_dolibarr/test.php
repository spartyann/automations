<?php


require_once('vendor/autoload.php');
require_once('tools.php');

use App\DoliApi;
use App\Notifications\SlackHelper;
use App\Tools;
use Config\Config;

if (Tools::isCommandLineInterface() == false) 
{
	echo "Only on CLI";
	exit(0);
}

//SlackHelper::sendMessage("testdf gsdg", "msg dfgsdfgsdfg sdfg sd");


//$stripe = new \Stripe\StripeClient(Config::STRIPE_SECRET_KEY);

$inv = DoliApi::getSupplierInvoicesForTP(Config::DOLI_STRIPE_TP_ID);


dd($inv);


/*
//dd(getInvoicesById());
//$tps = getClientsByEmail();
$tiers = DoliApi::getOrCreateClient('Api Test', 'test@mail.com');
$date = (new \DateTime('2024-06-05'))->getTimestamp();
$i = DoliApi::createInvoices($tiers->id, "Séance de Reiki", 60, 0, 1, $date);
$i = DoliApi::createPayment($i->id, $date, 7, "COMMENT", '$numPayment', '1111', '2222');
*/

dd($i);



