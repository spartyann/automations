<?php
namespace App;

use Config\Config;


class DoliApi {

	public static function callAPI($method, $url, object|array|bool $data = false)
	{

		// SWAGER: /api/index.php/explorer/
		$url = Config::DOLI_URL . '/api/index.php/' . $url;

		$curl = curl_init();

		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

		$httpheader = [
			'DOLAPIKEY: ' . Config::DOLI_API_KEY,
			'Authorization: ' . Config::DOLI_Authorization
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

		// Error
		if ($result === false) throw new \Exception(curl_error($curl));
		
		curl_close($curl);

		$obj = json_decode($result);

		// Error
		if (isset($obj->error))
		{
			throw new \Exception(json_encode($obj->error));
		}

		return $obj;
	}

	//
	// *************** Base API
	//

	public static function getThirdParties(){
		return self::callAPI('GET', 'thirdparties' );
	}

	public static function getInvoices(){
		return self::callAPI('GET', 'invoices' );
	}



	//
	// ******************** Toolbox
	//

	public static function formatClientThirdParty($tp){
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

	public static function formatClientInvoiceLine($line){
		//return $line;
		//dd($line);
		return (object)[
			'id' => intval($line->id),
			'rowid' => $line->rowid,
			'subprice' => floatval($line->subprice),
			'qty' => intval($line->qty),
			'tva_tx' => $line->tva_tx,
			'total_ttc' => floatval($line->total_ttc),
			'description' => $line->description,
			'description_text' => Html2Text::getTextFromHtml($line->description),

		];
	}

	public static function formatSupplierInvoice($iv){
		//return $line;
		//dd($line);

		$dt = new \DateTime();
		$dt->setTimestamp($iv->date);

		return (object)[
			'id' => intval($iv->id),
			'ref' => $iv->ref,
			'ref_supplier' => $iv->ref,
			'timestamp' => $iv->date,
			'status' => $iv->status,
			'socid' => $iv->socid,
			'date' => $dt,
			'total_ttc' => floatval($iv->total_ttc),
			'lines' => array_map("\App\DoliApi::formatSupplierInvoiceLine", $iv->lines),

			'paid' => $iv->paid,
		];
	}

	public static function formatSupplierInvoiceLine($line){
		//return $line;
		//dd($line);
		return (object)[
			'id' => intval($line->id),
			'qty' => intval($line->qty),
			'tva_tx' => $line->tva_tx,
			'pu_ht' => $line->pu_ht,
			'pu_ttc' => $line->pu_ttc,
			'description' => $line->description,
			'description_text' => Html2Text::getTextFromHtml($line->description),
		];
	}

	public static function formatClientInvoice($iv){

		//return $iv;
		return (object)[
			'id' => intval($iv->id),
			'ref' => $iv->ref,
			'status' => $iv->status,
			'date' => $iv->date,
			'mode_reglement_id' => $iv->mode_reglement_id,
			'cond_reglement_id' => $iv->cond_reglement_id,
			'total_ttc' => floatval($iv->total_ttc),
			'lines' => array_map("\App\DoliApi::formatClientInvoiceLine", $iv->lines),
			'totalpaid' => $iv->totalpaid,
			'socid' => $iv->socid,

		];
	}


	//
	// ****************** Functions
	//

	// ************ Clients

	public static function getClientsById(){
		return Tools::indexArray(array_map("\App\DoliApi::formatClientThirdParty", self::getThirdParties()), 'id');
	}

	public static function getClientsByEmail(){
		return Tools::indexArray(array_map("\App\DoliApi::formatClientThirdParty", self::getThirdParties()), 'email');
	}

	public static function createClient($name, $email = '', $phone = '', $address = '', $zip = '', $town = ''){

		$last = self::callAPI('GET', 'thirdparties', [ 'sortfield' => 't.rowid', 'sortorder' => 'DESC'] );
		$lastId = $last[0]->id;

		$codeClient = 'CU-' . (new \DateTime())->format('ym') .'-'. Tools::addZeros($lastId + 1, 5);

		return self::callAPI('POST', 'thirdparties', [
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

	public static function getThirdPartyByEmail($email)
	{
		$tp = self::callAPI('GET', 'thirdparties/email/' . rawurlencode($email));

		if (isset($tp->error)) return null;

		return self::formatClientThirdParty($tp);
	}

	public static function getThirdPartyById(int $id)
	{
		$tp = self::callAPI('GET', 'thirdparties/' . $id);
		if (isset($tp->error)) return null;

		return self::formatClientThirdParty($tp);
	}

	public static function getOrCreateClient($name, $email = '', $phone = '', $address = '', $zip = '', $town = '')
	{
		$tp = self::getThirdPartyByEmail($email);

		if ($tp == null) { $id = self::createClient($name, $email, $phone, $address, $zip, $town); }
		else return $tp;

		return self::getThirdPartyById($id);
	}


	// ************ Invoices


	public static function getInvoicesById(){
		return Tools::indexArray(array_map("\App\DoliApi::formatClientInvoice", self::getInvoices()), 'id');
	}

	public static function getInvoicesForTP($tpId, string $status = null, $limit = 1, $sortorder = 'DESC'){
		// $status  draft , unpaid, paid , cancelled
		$conds = [ 
			'sortorder' => $sortorder,
			'limit' => $limit,
			'thirdparty_ids' => $tpId,
		];
		if ($status !== null) $conds['status'] = $status;

		$res = self::callAPI('GET', 'invoices',  $conds);

		return array_map("\App\DoliApi::formatClientInvoice", $res);
	}


	public static function createSimpleInvoices($clientId, $description, $priceUT, $tva, $qty, $date, $modelPdf = null){
		$id = self::callAPI('POST', 'invoices', [
			'mode_reglement_id' => "6",
			'cond_reglement_id' => "0",
			'socid' => $clientId,
			'date' => $date,
			'lines' => [
				[
					'subprice' => floatval($priceUT),
					'qty' => $qty,
					'tva_tx' => $tva,
					'desc' => $description
				]
			],
			'model_pdf' => $modelPdf ?? Config::DOLI_DEFAULT_MODEL_PDF
		] );
		
		$i = self::callAPI('POST', "invoices/$id/validate");

		return self::formatClientInvoice($i);
	}

	public static function createInvoices($clientId, array $lines, $date, $modelPdf = null){

		$id = self::callAPI('POST', 'invoices', [
			//'mode_reglement_id' => "6",
			'cond_reglement_id' => "0",
			'socid' => $clientId,
			'date' => $date,
			'lines' => $lines,
			'model_pdf' => $modelPdf ?? Config::DOLI_DEFAULT_MODEL_PDF
		] );
		
		$i = self::callAPI('POST', "invoices/$id/validate");

		return self::formatClientInvoice($i);
	}

	public static function validateInvoice($id){
		
		$i = self::callAPI('POST', "invoices/$id/validate");

		return self::formatClientInvoice($i);
	}

	public static function createPayment($invoiceId, $date, $paymentMode = 6, $comment = '', $numPayment = '', $chqemetteur = '', $chqbank = ''){

		//paymentid. 
		// 1= Titre interbancaire de paiment,
		// 2= Virement,
		// 3= Ordre de prélèvement,
		// 4= espèce, 
		// 6= Carte bancaire
		// 7= Chèque

		$id = self::callAPI('POST', "invoices/$invoiceId/payments", [
			'datepaye' => $date,
			'paymentid' => $paymentMode,
			'closepaidinvoices' => "yes",
			'accountid' => Config::DOLI_AccountId,
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

	public static function getSupplierInvoicesForTP($tpId, $limit = 1, $sortorder = 'DESC'){
		$res = self::callAPI('GET', 'supplierinvoices', [ 
			'sortorder' => $sortorder,
			'limit' => $limit,
			'thirdparty_ids' => $tpId,
		] );

		//dd($res);

		return array_map("\App\DoliApi::formatSupplierInvoice", $res);
	}



	public static function supplierInvoicesAddLine($inId, $description, $priceUT, $tva = 0, $qty = 1){
		self::callAPI('POST', "supplierinvoices/$inId/lines", [
			'pu_ht' => $priceUT,
			'qty' => $qty,
			'tva_tx' => $tva,
			'description' => $description
		] );
	}


	public static function supplierInvoicesValidate($inId){
		self::callAPI('POST', "supplierinvoices/$inId/validate");
	}

	public static function createSupplierInvoices($clientId, $date, $refSupplier){
		$id = self::callAPI('POST', 'supplierinvoices', [
			//'mode_reglement_id' => "3",
			'ref_supplier' => $refSupplier,
			'cond_reglement_id' => "0",
			'socid' => $clientId,
			'date' => $date,
			'lines' => [ ]
		] );
		
		$i = self::callAPI('GET', "supplierinvoices/$id");

		return self::formatSupplierInvoice($i);
	}

	public static function createSupplierInvoicePayment($invoiceId, $date, $paymentMode = 6, $comment = '', $numPayment = '', $chqemetteur = '', $chqbank = ''){

		$id = self::callAPI('POST', "supplierinvoices/$invoiceId/payments", [
			'datepaye' => $date,
			'payment_mode_id' => $paymentMode,
			'closepaidinvoices' => "yes",
			'accountid' => Config::DOLI_AccountId,
			'num_payment' => $numPayment,
			'comment' => $comment,
			'chqemetteur' => $chqemetteur,
			'chqbank' => $chqbank
		] );

		return $id;
	}
}