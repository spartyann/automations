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

	
		self::processDoliInvoiceForStripePayment($name, $email, $invoiceLines, $price, $timestamp,  $paymentIntent->id, $fee);

		SlackHelper::sendMessage("Facture ajoutee", "$name: $price €, Frais: $fee €");
	}


	public static function processDoliInvoiceForStripePayment($name, $email, $invoiceLines, $price, $timestamp, $paymentId, $fee)
	{
		// Create 
		$tiers = DoliApi::getOrCreateClient($name, $email, '', '', '', '');
		$invoice = DoliApi::createInvoices($tiers->id, $invoiceLines, $timestamp);
		$payment = DoliApi::createPayment($invoice->id, $timestamp, 6, 'Paiement Stripe - ' . $paymentId . ' - Frais: ' . $fee, $paymentId);

		// Stripe Invoice
		$inv = DoliApi::getSupplierInvoicesForTP(Config::DOLI_STRIPE_STP_ID);
		$last = $inv[0] ?? null;
		$now = new \DateTime();

		if ($last == null || $last->status != "0")  // Si pas brouillon on créé une nouvelle
		{
			// Create new
			$last = DoliApi::createSupplierInvoices(Config::DOLI_STRIPE_STP_ID, time(), $now->format('Y-m--') . rand(1000, 9999));
		}
		else if ($last->date->format('m') != $now->format('m'))
		{
			// Validate
			DoliApi::supplierInvoicesValidate($last->id);

			// Pay
			DoliApi::createSupplierInvoicePayment($last->id, time(), 3, 'Retenus sur les paiements Stripe');

			// Create new
			$last = DoliApi::createSupplierInvoices(Config::DOLI_STRIPE_STP_ID, time(), $now->format('Y-m--') . rand(1000, 9999));

			SlackHelper::sendMessage("Facture Stripe validée", "Création d'une nouvelle facture Stripe.");
		}

		$dateString = $now->format('Y-m-d');
		
		DoliApi::supplierInvoicesAddLine($last->id, "Frais de $fee € sur $price € de $name le $dateString - $paymentId", $fee, 0, 1);

	}

}