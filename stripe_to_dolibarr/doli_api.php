<?php

require_once 'vars.php';

function doliCallAPI($method, $url, object|array|bool $data = false)
{
	$url = 'https://erp.tassy.pro/api/index.php/' . $url;

    $curl = curl_init();

	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

    $httpheader = [
		'DOLAPIKEY: ' . DOLI_API_KEY,
		'Authorization: ' . DOLI_Authorization
	];

    switch ($method)
    {
        case "POST":
            curl_setopt($curl, CURLOPT_POST, 1);
            $httpheader[] = "Content-Type:application/json";

            if ($data)
                curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));

            break;
        case "PUT":

	    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PUT');
            $httpheader[] = "Content-Type:application/json";

            if ($data)
                curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));

            break;
        default:
            if ($data)
                $url = sprintf("%s?%s", $url, http_build_query($data));
    }

    // Optional Authentication:
	//    curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
	//    curl_setopt($curl, CURLOPT_USERPWD, "username:password");

    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($curl, CURLOPT_HTTPHEADER, $httpheader);

    $result = curl_exec($curl);

	if ($result === false)
	{
		echo curl_error($curl);
		exit(1);
	}

    curl_close($curl);

    return json_decode($result);
}

//
// *************** Base API
//

function doliApi_GetThirdParties(){
	return doliCallAPI('GET', 'thirdparties' );
}

function doliApi_GetInvoices(){
	return doliCallAPI('GET', 'invoices' );
}



//
// ******************** Toolbox
//

function doliTB_FormatClientThirdParty($tp){
	return (object)[
		'id' => intval($tp->id),
		'ref' => $tp->ref,
		'status' => $tp->status,
		'name' => $tp->name,
		'phone' => $tp->phone,
		'email' => $tp->email,
		'code_client' => $tp->code_client,
		'address' => $tp->address,
		'zip' => $tp->zip,
		'town' => $tp->town,
		'client' => $tp->client == '1',
		'fournisseur' => $tp->fournisseur == '1',
	];
}

function doliTB_FormatClientInvoiceLine($line){
	//return $line;
	//dd($line);
	return (object)[
		'id' => intval($line->id),
		'rowid' => $line->rowid,
		'subprice' => $line->subprice,
		'qty' => $line->qty,
		'tva_tx' => $line->tva_tx,
		'total_ttc' => $line->total_ttc,
		'description' => $line->description,
		'description_text' => Html2Text::getTextFromHtml($line->description),

	];
}

function doliTB_FormatSupplierInvoice($iv){
	//return $line;
	//dd($line);

	$dt = new DateTime();
	$dt->setTimestamp($iv->date);

	return (object)[
		'id' => intval($iv->id),
		'ref' => $iv->ref,
		'timestamp' => $iv->date,
		'status' => $iv->status,
		'socid' => $iv->socid,
		'date' => $dt,
		'total_ttc' => $iv->total_ttc,
		'lines' => array_map("doliTB_FormatSupplierInvoiceLine", $iv->lines),

		'paid' => $iv->paid,
	];
}

function doliTB_FormatSupplierInvoiceLine($line){
	//return $line;
	//dd($line);
	return (object)[
		'id' => intval($line->id),
		'qty' => $line->qty,
		'tva_tx' => $line->tva_tx,
		'pu_ht' => $line->pu_ht,
		'pu_ttc' => $line->pu_ttc,
		'description' => $line->description,
		'description_text' => Html2Text::getTextFromHtml($line->description),
	];
}

function doliTB_FormatClientInvoice($iv){
	//return $iv;
	return (object)[
		'id' => intval($iv->id),
		'ref' => $iv->ref,
		'status' => $iv->status,
		'date' => $iv->date,
		'mode_reglement_id' => $iv->mode_reglement_id,
		'cond_reglement_id' => $iv->cond_reglement_id,
		'total_ttc' => $iv->total_ttc,
		'lines' => array_map("doliTB_FormatClientInvoiceLine", $iv->lines),
		'totalpaid' => $iv->totalpaid,
		'socid' => $iv->socid,

	];
}


//
// ****************** Functions
//

// ************ Clients

function doliGetClientsById(){
	return indexArray(array_map("doliTB_FormatClientThirdParty", doliApi_GetThirdParties()), 'id');
}

function doliGetClientsByEmail(){
	return indexArray(array_map("doliTB_FormatClientThirdParty", doliApi_GetThirdParties()), 'email');
}

function doliCreateClient($name, $email = '', $phone = '', $address = '', $zip = '', $town = ''){

	$last = doliCallAPI('GET', 'thirdparties', [ 'sortfield' => 't.rowid', 'sortorder' => 'DESC'] );
	$lastId = $last[0]->id;

	$codeClient = 'CU-' . (new \DateTime())->format('ym') .'-'. addZeros($lastId + 1, 5);

	return doliCallAPI('POST', 'thirdparties', [
		'name' => $name,
		'client' => "1",
		'email' => strtolower($email), // Lower
		'phone' => $phone,
		'address' => $address,
		'zip' => $zip,
		'town' => $town,
		'code_client' => $codeClient
	] );
}

function doliGetThirdPartyByEmail($email)
{
	$tp = doliCallAPI('GET', 'thirdparties/email/' . rawurlencode($email));
	if (isset($tp->error)) return null;

	return doliTB_FormatClientThirdParty($tp);
}

function doliGetThirdPartyById(int $id)
{
	$tp = doliCallAPI('GET', 'thirdparties/' . $id);
	if (isset($tp->error)) return null;

	return doliTB_FormatClientThirdParty($tp);
}

function getOrCreateClient($name, $email = '', $phone = '', $address = '', $zip = '', $town = '')
{
	$tp = doliGetThirdPartyByEmail($email);

	if ($tp == null) { $id = doliCreateClient($name, $email, $phone, $address, $zip, $town); }
	else return $tp;

	return doliGetThirdPartyById($id);
}


// ************ Invoices


function doliGetInvoicesById(){
	return indexArray(array_map("doliTB_FormatClientInvoice", doliApi_GetInvoices()), 'id');
}

function doliCreateSimpleInvoices($clientId, $description, $priceUT, $tva, $qty, $date, $modelPdf = null){
	$id = doliCallAPI('POST', 'invoices', [
		'mode_reglement_id' => "6",
		'cond_reglement_id' => "0",
		'socid' => $clientId,
		'date' => $date,
		'lines' => [
			[
				'subprice' => $priceUT,
				'qty' => $qty,
				'tva_tx' => $tva,
				'desc' => $description
			]
		],
		'model_pdf' => $modelPdf ?? DOLI_DEFAULT_MODEL_PDF
	] );
	
	$i = doliCallAPI('POST', "invoices/$id/validate");

	return doliTB_FormatClientInvoice($i);
}

function doliCreateInvoices($clientId, array $lines, $date, $modelPdf = null){

	$id = doliCallAPI('POST', 'invoices', [
		//'mode_reglement_id' => "6",
		'cond_reglement_id' => "0",
		'socid' => $clientId,
		'date' => $date,
		'lines' => $lines,
		'model_pdf' => $modelPdf ?? DOLI_DEFAULT_MODEL_PDF
	] );
	
	$i = doliCallAPI('POST', "invoices/$id/validate");

	return doliTB_FormatClientInvoice($i);
}

function doliCreatePayment($invoiceId, $date, $paymentMode = 6, $comment = '', $numPayment = '', $chqemetteur = '', $chqbank = ''){

	//paymentid. 
	// 1= Titre interbancaire de paiment,
	// 2= Virement,
	// 3= Ordre de prélèvement,
	// 4= espèce, 
	// 6= Carte bancaire
	// 7= Chèque

	$id = doliCallAPI('POST', "invoices/$invoiceId/payments", [
		'datepaye' => $date,
		'paymentid' => $paymentMode,
		'closepaidinvoices' => "yes",
		'accountid' => DOLI_AccountId,
		'num_payment' => $numPayment,
		'comment' => $comment,
		'chqemetteur' => $chqemetteur,
		'chqbank' => $chqbank
	] );

	return $id;
}


//
//  Facture Fournisseurs
//

function doliGetSupplierInvoicesForTP($tpId, $limit = 1, $sortorder = 'DESC'){
	$res = doliCallAPI('GET', 'supplierinvoices', [ 
		'sortorder' => $sortorder,
		'limit' => $limit,
		'thirdparty_ids' => $tpId,
	 ] );

	//dd($res);

	return indexArray(array_map("doliTB_FormatSupplierInvoice", $res), 'id');
}



function doliSupplierInvoicesAddLine($inId, $description, $priceUT, $tva = 0, $qty = 1){
	doliCallAPI('POST', "supplierinvoices/$inId/lines", [
		'subprice' => $priceUT,
		'qty' => $qty,
		'tva_tx' => $tva,
		'desc' => $description
	] );
}


function doliSupplierInvoicesValidate($inId){
	doliCallAPI('POST', "supplierinvoices/$inId/validate");
}

function doliCreateSupplierInvoices($clientId, $date){
	$id = doliCallAPI('POST', 'supplierinvoices', [
		//'mode_reglement_id' => "3",
		'cond_reglement_id' => "0",
		'socid' => $clientId,
		'date' => $date,
		'lines' => [ ]
	] );
	
	$i = doliCallAPI('GET', "supplierinvoices/$id");

	return doliTB_FormatSupplierInvoice($i);
}

function doliCreateSupplierInvoicePayment($invoiceId, $date, $paymentMode = 6, $comment = '', $numPayment = '', $chqemetteur = '', $chqbank = ''){

	$id = doliCallAPI('POST', "supplierinvoices/$invoiceId/payments", [
		'datepaye' => $date,
		'paymentid' => $paymentMode,
		'closepaidinvoices' => "yes",
		'accountid' => DOLI_AccountId,
		'num_payment' => $numPayment,
		'comment' => $comment,
		'chqemetteur' => $chqemetteur,
		'chqbank' => $chqbank
	] );

	return $id;
}