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

error_reporting(E_ALL);
 ini_set("display_errors", 1);

require_once('vendor/autoload.php');

$message = '';
$error = '';
$pwd = '';

if (count($_POST) > 2)
{
	$pwd =  $_POST['pwd'] ?? '';
	
	if ($pwd != '6d1gdsfg6n1dfdfg')
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
    	$payment_comment = $_POST['payment_comment'];
    	$payment_num_payment = $_POST['payment_num_payment'];
    	$payment_chqemetteur = $_POST['payment_chqemetteur'];
    	$payment_chqbank = $_POST['payment_chqbank'];
    	
    	if (trim($name) != '' && trim($email) != '' && trim($invoice_desc) != '')
    	{
        	$timestamp = (new \DateTime($invoice_date))->getTimestamp();
        	$tiers = DoliApi::getOrCreateClient($name, $email, $phone, $address, $zip, $town);
        	$invoice = DoliApi::createSimpleInvoices($tiers->id, $invoice_desc, $invoice_price_ut, $invoice_tva, $invoice_qty, $timestamp);
        	$payment = DoliApi::createPayment($invoice->id, $timestamp, $payment_mode, $payment_comment, $payment_num_payment, $payment_chqemetteur, $payment_chqbank);
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
					<input type="text" name="name" class="form-control">
				</div>
				<div class="mb-3 form-block">
					<label class="form-label">Email</label>
					<input type="email" name="email" class="form-control">
				</div>
				<div class="mb-3 form-block">
					<label class="form-label">Téléphone</label>
					<input type="text" name="phone" class="form-control">
				</div>
				<div class="mb-3 form-block">
					<label class="form-label">Adresse</label>
					<textarea name="address" class="form-control"></textarea>
				</div>
				<div class="mb-3 form-block">
					<label class="form-label">Code postal</label>
					<input type="text" name="zip" class="form-control">
				</div>
				<div class="mb-3 form-block">
					<label class="form-label">Ville</label>
					<input type="text" name="town" class="form-control">
				</div>

				<h4>Facture</h4>
				<div class="mb-3 form-block">
					<label class="form-label">Service</label>
					<input type="text" name="invoice_desc" class="form-control" value="Séance de Reiki">
				</div>

				<div class="mb-3 form-block">
					<label class="form-label">Prix</label>
					<input type="text" name="invoice_price_ut" class="form-control" value="60">
				</div>

				<div class="mb-3 form-block">
					<label class="form-label">TVA</label>
					<input type="text" name="invoice_tva" class="form-control" value="0">
				</div>

				<div class="mb-3 form-block">
					<label class="form-label">Quantité</label>
					<input type="text" name="invoice_qty" class="form-control" value="1">
				</div>
				<div class="mb-3 form-block">
					<label class="form-label">Date</label>
					<input type="date" name="invoice_date" class="form-control">
				</div>

				<h4>Paiement</h4>
				<div class="mb-3 form-block">
					<label class="form-label">Mode de paiement</label>
					<select name="payment_mode" class="form-control">
						<option value="4">Espèce</option>
						<option value="6" selected>Carte bleue</option>
						<option value="7">Chèque</option>
						<option value="2">Virement</option>
						<option value="3">Prélèvement</option>
					</select>
				</div>

				<div class="mb-3 form-block">
					<label class="form-label">Commentaire</label>
					<input type="text" name="payment_comment" class="form-control">
				</div>

				<div class="mb-3 form-block">
					<label class="form-label">Numéro</label>
					<input type="text" name="payment_num_payment" class="form-control">
				</div>

				<div class="mb-3 form-block">
					<label class="form-label">Chèque Emeteur</label>
					<input type="text" name="payment_chqemetteur" class="form-control">
				</div>

				<div class="mb-3 form-block">
					<label class="form-label">Chèque Banque</label>
					<input type="text" name="payment_chqbank" class="form-control">
				</div>


				<div>
					<button type="submit" class="btn btn-primary">Enregistrer</button>
				</div>
			</form>
		</div>
	</div>
</body>

</html>

