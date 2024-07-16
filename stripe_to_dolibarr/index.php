<!doctype html>
<html lang="fr">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Dolibarr Tool</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
	<style>
		.form-block {
			display: inline-block;
			width: 40%;
			padding-right: 20px;
			vertical-align: top;
		}
	</style>
</head>

<?php

use App\DoliApi;
use App\WFDoliCBPayment;
use Config\Config;

error_reporting(E_ALL);
 ini_set("display_errors", 1);

require_once('vendor/autoload.php');

$message = '';
$error = '';
$pwd = '';


$name = '';
$email = '';
$phone = '';
$address = '';
$zip = '';
$town = '';
$invoice_desc = '';
$invoice_price_ut = '';
$invoice_tva = '';
$invoice_qty = '';
$invoice_date = '';
$payment_mode = '6';
$cb_provider = '';
$payment_comment = '';
$payment_num_payment = '';
$payment_chqemetteur = '';
$payment_chqbank = '';

if (count($_POST) > 2)
{
	$pwd =  $_POST['pwd'] ?? '';
	
	if ($pwd != Config::PAGE_PASSWORD)
	{
	    $error = "Mot de passe invalide";
	}
	else {
    	$name = $_POST['name'];
    	$email = $_POST['email'];
    	$phone = $_POST['phone'];
    	$address = $_POST['address'];
    	$zip = $_POST['zip'];
    	$town = $_POST['town'];
    	$invoice_desc = $_POST['invoice_desc'];
    	$invoice_price_ut = $_POST['invoice_price_ut'];
    	$invoice_tva = $_POST['invoice_tva'];
    	$invoice_qty = $_POST['invoice_qty'];
    	$invoice_date = $_POST['invoice_date'];
    	$payment_mode = $_POST['payment_mode'];
		$cb_provider =  $_POST['cb_provider'];
		
    	$payment_comment = $_POST['payment_comment'];
    	$payment_num_payment = $_POST['payment_num_payment'];
    	$payment_chqemetteur = $_POST['payment_chqemetteur'];
    	$payment_chqbank = $_POST['payment_chqbank'];

		$cb_provider_def = Config::CB_PROVIDERS[$cb_provider] ?? null;
    	
    	if (trim($name) != '' && trim($email) != '' && trim($invoice_desc) != '')
    	{
        	$timestamp = (new \DateTime($invoice_date))->getTimestamp();
        	$tiers = DoliApi::getOrCreateClient($name, $email, $phone, $address, $zip, $town);

			$invoice_qty = intval($invoice_qty);
			$invoice_tva = floatval($invoice_tva);
			$invoice_price_ut = floatval($invoice_price_ut);

			if ($invoice_qty == 0) $invoice_qty = 1;

			if ($payment_mode == '6' && $cb_provider_def !== null)
			{
				$invoiceLines = [];
				$invoiceLines[] = [
					'desc' => $invoice_desc,
					'subprice' => $invoice_price_ut,
					'tva_tx' => $invoice_tva,
					'qty' => $invoice_qty
				];

				$price = $invoice_qty * round($invoice_price_ut + ($invoice_price_ut * $invoice_tva / 100), 2);
        
				WFDoliCBPayment::processDoliInvoiceForCBPayment($name, $email, $invoiceLines, $price, $timestamp, $payment_num_payment, $cb_provider_def, null);
			}
			else
			{
				$invoice = DoliApi::createSimpleInvoices($tiers->id, $invoice_desc, $invoice_price_ut, $invoice_tva, $invoice_qty, $timestamp);
				$payment = DoliApi::createPayment($invoice->id, $timestamp, $payment_mode, $payment_comment, $payment_num_payment, $payment_chqemetteur, $payment_chqbank);
			}

			$message = "C'est bon !";
    	}
    }
}


?>

<body class="p-4">

	<div class="container">
		<div class="row">

			<?php if ($message != '') { ?> <div class="alert alert-success"><?php echo $message; ?></div> <?php } ?>
			<?php if ($error != '') { ?> <div class="alert alert-danger"><?php echo $error; ?></div> <?php } ?>

			<h2>Ajouter une facture</h2>

			<form method="POST" class="mt-3">
			    
			    <div class="mb-3 form-block">
					<label class="form-label">Mot de passe</label>
					<input type="password" name="pwd" class="form-control" value="<?php echo htmlentities($pwd); ?>">
				</div>
				
				<h4>Client</h4>
				<div class="mb-3 form-block">
					<label class="form-label">Nom</label>
					<input type="text" name="name" value="<?php echo htmlentities($name); ?>" class="form-control">
				</div>
				<div class="mb-3 form-block">
					<label class="form-label">Email</label>
					<input type="email" name="email" value="<?php echo htmlentities($email); ?>" class="form-control">
				</div>
				<div class="mb-3 form-block">
					<label class="form-label">Téléphone</label>
					<input type="text" name="phone" value="<?php echo htmlentities($phone); ?>" class="form-control">
				</div>
				<div class="mb-3 form-block">
					<label class="form-label">Adresse</label>
					<textarea name="address" value="<?php echo htmlentities($address); ?>" class="form-control"></textarea>
				</div>
				<div class="mb-3 form-block">
					<label class="form-label">Code postal</label>
					<input type="text" name="zip" value="<?php echo htmlentities($zip); ?>" class="form-control">
				</div>
				<div class="mb-3 form-block">
					<label class="form-label">Ville</label>
					<input type="text" name="town" value="<?php echo htmlentities($town); ?>" class="form-control">
				</div>

				<h4>Facture</h4>
				<div class="mb-3 form-block">
					<label class="form-label">Service</label>
					<input type="text" name="invoice_desc" value="<?php echo htmlentities($invoice_desc); ?>" class="form-control" value="Séance de Reiki">
				</div>

				<div class="mb-3 form-block">
					<label class="form-label">Prix</label>
					<input type="text" name="invoice_price_ut" value="<?php echo htmlentities($invoice_price_ut); ?>" class="form-control" value="60">
				</div>

				<div class="mb-3 form-block">
					<label class="form-label">TVA</label>
					<input type="text" name="invoice_tva" value="<?php echo htmlentities($invoice_tva); ?>" class="form-control" value="0">
				</div>

				<div class="mb-3 form-block">
					<label class="form-label">Quantité</label>
					<input type="text" name="invoice_qty" value="<?php echo htmlentities($invoice_qty); ?>" class="form-control" value="1">
				</div>
				<div class="mb-3 form-block">
					<label class="form-label">Date</label>
					<input type="date" name="invoice_date" value="<?php echo htmlentities($invoice_date); ?>" class="form-control">
				</div>

				<h4>Paiement</h4>
				<div class="mb-3 form-block">
					<label class="form-label">Mode de paiement</label>
					<select name="payment_mode" class="form-control">
						<option value="4" <?php if ($payment_mode == '4') echo 'selected' ?>>Espèce</option>
						<option value="6" <?php if ($payment_mode == '6') echo 'selected' ?>>Carte bleue</option>
						<option value="7" <?php if ($payment_mode == '7') echo 'selected' ?>>Chèque</option>
						<option value="2" <?php if ($payment_mode == '2') echo 'selected' ?>>Virement</option>
						<option value="3" <?php if ($payment_mode == '3') echo 'selected' ?>>Prélèvement</option>
					</select>
				</div>

				<div class="mb-3 form-block">
					<label class="form-label">Fournisseur de CB</label>
					<select name="cb_provider" class="form-control">
						<option name="none" value="none">Aucun</option>
						<?php foreach(Config::CB_PROVIDERS as $name => $provider ) {?>
							<option 
								name="<?php echo $name; ?>"
								value="<?php echo $name; ?>"
								<?php if ($cb_provider == $name) echo 'selected' ?>
								>
								<?php echo $provider['name']; ?></option>
						<?php } ?>
					</select>
				</div>

				<div class="mb-3 form-block">
					<label class="form-label">Commentaire</label>
					<input type="text" name="payment_comment" value="<?php echo htmlentities($payment_comment); ?>" class="form-control">
				</div>

				<div class="mb-3 form-block">
					<label class="form-label">Numéro</label>
					<input type="text" name="payment_num_payment" value="<?php echo htmlentities($payment_num_payment); ?>" class="form-control">
				</div>

				<div class="mb-3 form-block">
					<label class="form-label">Chèque Emeteur</label>
					<input type="text" name="payment_chqemetteur" value="<?php echo htmlentities($payment_chqemetteur); ?>" class="form-control">
				</div>

				<div class="mb-3 form-block">
					<label class="form-label">Chèque Banque</label>
					<input type="text" name="payment_chqbank" value="<?php echo htmlentities($payment_chqbank); ?>" class="form-control">
				</div>


				<div>
					<button type="submit" class="btn btn-primary">Enregistrer</button>
				</div>
			</form>
		</div>
	</div>
</body>

</html>

