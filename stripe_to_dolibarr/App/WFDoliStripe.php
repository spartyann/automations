<?php

namespace App;

use App\Notifications\SlackHelper;
use Config\Config;
use Stripe\Checkout\Session;
use Stripe\StripeClient;

class WFDoliStripe {

	public static function processStripePayment(StripeClient $stripe, Session $session)
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

		WFDoliCBPayment::processDoliInvoiceForCBPayment($name, $email, $invoiceLines, $price, $timestamp, $paymentIntent->id, Config::CB_PROVIDERS['stripe'] , $fee);

		SlackHelper::sendMessage("Facture ajoutee", "$name: $price €, Frais: $fee €");
	}

}