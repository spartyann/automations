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

use App\DoliApi;
use App\Notifications\SlackHelper;
use App\WFDoliStripe;
use Config\Config;

require_once('vendor/autoload.php');

// The library needs to be configured with your account's secret key.
// Ensure the key is kept out of any version control system you might be using.
$stripe = new \Stripe\StripeClient(Config::STRIPE_SECRET_KEY);

// This is your Stripe CLI webhook secret for testing your endpoint locally.
$endpoint_secret = Config::STRIPE_ENDPOINT_SECRET;

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
		try {
			WFDoliStripe::processStripePayment($stripe, $session);
		} catch (\Throwable $ex)
		{
			SlackHelper::sendError('Erreur', $ex->getMessage());
		}
    }
}

http_response_code(200);

echo 'OK';
