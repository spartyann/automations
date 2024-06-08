<?php


require_once('vendor/autoload.php');
require_once('tools.php');
require_once('vars.php');
require_once('html_to_text.php');
require_once('doli_api.php');

use App\Notifications\SlackHelper;


if (isCommandLineInterface() == false) 
{
	echo "Only on CLI";
	exit(0);
}

SlackHelper::sendMessage("testdf gsdg", "msg dfgsdfgsdfg sdfg sd");

exit(0);
//$stripe = new \Stripe\StripeClient(STRIPE_SECRET_KEY);

$inv = doliGetSupplierInvoicesForTP(DOLI_STRIPE_TP_ID);


dd($inv);



//dd(doliGetInvoicesById());
//$tps = doliGetClientsByEmail();
$tiers = getOrCreateClient('Api Test', 'test@mail.com');
$date = (new \DateTime('2024-06-05'))->getTimestamp();
$i = doliCreateInvoices($tiers->id, "Séance de Reiki", 60, 0, 1, $date);
$i = doliCreatePayment($i->id, $date, 7, "COMMENT", '$numPayment', '1111', '2222');

dd($i);



