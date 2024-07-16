<?php

namespace App;

use App\Notifications\SlackHelper;
use Config\Config;

class WFDoliCBPayment {

	public static function processDoliInvoiceForCBPayment($clientName, $clientEmail, $invoiceLines, $price, $timestamp, string|null $paymentId, array $cbProvider, $fee = null)
	{
		// Prepare params
		$paymentIdStringItem = ' - ' . $paymentId;
		if ($paymentId === null || $paymentId == '') { $paymentId = ''; $paymentIdStringItem = '';}
		
		
		$cbProviderName = $cbProvider['name'];
		$cbProviderStp = $cbProvider['doli_stp'];

		if ($fee === null) // Calculate Fee
		{
			$fee = $cbProvider['fixed_fees'] + round($price * $cbProvider['fees_percent'] / 100, 2, $cbProvider['round_type']);
		}

		// Create 
		$tiers = DoliApi::getOrCreateClient($clientName, $clientEmail, '', '', '', '');

		// Check if an existing invoice not paid for the same price exists 
		$lasts = array_merge(
			DoliApi::getInvoicesForTP($tiers->id, 'draft'),
			DoliApi::getInvoicesForTP($tiers->id, 'unpaid')
		) ;
		$last = $lasts[0] ?? null;

		
		if ($last == null || $last->total_ttc != $price) $invoice = DoliApi::createInvoices($tiers->id, $invoiceLines, $timestamp);
		else
		{
			if ($last->status == "0") DoliApi::validateInvoice($last->id);
			$invoice = $last;
		}
	
		$payment = DoliApi::createPayment($invoice->id, $timestamp, 6, "Paiement $cbProviderName $paymentIdStringItem - Frais: $fee", $paymentId);

		// Provider Invoice
		$inv = DoliApi::getSupplierInvoicesForTP($cbProviderStp);
		$last = $inv[0] ?? null;
		$now = new \DateTime();

		if ($last == null || $last->status != "0")  // Si pas brouillon on créé une nouvelle
		{
			// Create new
			$last = DoliApi::createSupplierInvoices($cbProviderStp, time(), $now->format('Y-m--') . rand(1000, 9999));
		}
		else if ($last->date->format('m') != $now->format('m'))
		{
			// Validate
			DoliApi::supplierInvoicesValidate($last->id);

			// Pay
			DoliApi::createSupplierInvoicePayment($last->id, time(), 3, "Retenus sur les paiements $cbProviderName");

			// Create new
			$last = DoliApi::createSupplierInvoices($cbProviderStp, time(), $now->format('Y-m--') . rand(1000, 9999));

			SlackHelper::sendMessage("Facture $cbProviderName validée", "Création d'une nouvelle facture $cbProviderName.");
		}

		$dateString = $now->format('Y-m-d');
		
		DoliApi::supplierInvoicesAddLine($last->id, "Frais de $fee € sur $price € de $clientName le $dateString $paymentIdStringItem", $fee, 0, 1);

	}

}