<?php

// webhook.php
//
// Use this sample code to handle webhook events in your integration.
//
// 1) Paste this code into a new file (webhook.php)
//
// 2) Install dependencies
//   composer require stripe/stripe-php
//
// 3) Run the server on http://localhost:4242
//   php -S localhost:4242


require_once('vendor/autoload.php');
require_once 'vars.php';
require_once('tools.php');
require_once('html_to_text.php');
require_once('doli_api.php');

// The library needs to be configured with your account's secret key.
// Ensure the key is kept out of any version control system you might be using.
$stripe = new \Stripe\StripeClient(STRIPE_SECRET_KEY);

// This is your Stripe CLI webhook secret for testing your endpoint locally.
$endpoint_secret = STRIPE_ENDPOINT_SECRET;

$payload = @file_get_contents('php://input');
$sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
$event = null;

try {
  $event = \Stripe\Webhook::constructEvent(
    $payload, $sig_header, $endpoint_secret
  );
} catch(\UnexpectedValueException $e) {
  // Invalid payload
  http_response_code(400);
  echo $e->getMessage();
  exit();
} catch(\Stripe\Exception\SignatureVerificationException $e) {
  // Invalid signature
  http_response_code(400);
  echo $e->getMessage();
  exit();
}


$session = null;

// Handle the event
switch ($event->type) {
  case 'checkout.session.completed':
    $session = $event->data->object;
    
    if ($session->payment_status == "paid")
    {
    
        $name = $session->customer_details->name;
        $email = $session->customer_details->email;
        $price = $session->amount_total / 100;
        $timestamp = $session->created;
        
		$paymentIntent = $stripe->paymentIntents->retrieve($session->payment_intent);
		$charge = $stripe->charges->retrieve($paymentIntent->latest_charge);
		$balanceTransaction = $stripe->balanceTransactions->retrieve($charge->balance_transaction, []);

		$fee = $balanceTransaction->fee / 100;
        
        //echo "$name\n$email\n$price\n\n";
        
        $sessionItems = $stripe->checkout->sessions->allLineItems($session->id,[]);
        
        $invoiceLines = [];
        foreach($sessionItems as $item)
        {
            $invoiceLines[] = [
                'desc' => $item->description,
                'subprice' => $item->amount_total / 100,
                'tva_tx' => 0,
                'qty' => 1
            ];
        }

		try {
        
			$tiers = getOrCreateClient($name, $email, '', '', '', '');
			$invoice = doliCreateInvoices($tiers->id, $invoiceLines, $timestamp);
			$payment = doliCreatePayment($invoice->id, $timestamp, 6, 'Paiement Stripe - ' . $paymentIntent->id . ' - Frais: ' . $fee, $paymentIntent->id);

			
		} catch (\Throwable $ex)
		{

		}
    }
}

http_response_code(200);

echo 'OK';