<?php

use Google\Service\Sheets;
use Google\Service\Sheets\BatchUpdateSpreadsheetRequest;
use Google\Service\Sheets\GridRange;
use Google\Service\Sheets\InsertRangeRequest;
use Google\Service\Sheets\Request;
use Google\Service\Sheets\ValueRange;

function backupSheet(Sheets $service){
	// Get all
	$response = $service->spreadsheets_values->get(SPREADSHEET_ID, "A1:Z10000");
	$rows = $response->getValues();


}

function addBillingLine(\DateTime $date, string $operationTypeName, string $designation, string $tiersName, float $debit, float $credit)
{

	$client = new \Google_Client();
	$client->setApplicationName('Google Sheets API');
	$client->setScopes([Sheets::SPREADSHEETS]);
	$client->setAccessType('offline');

	$client->setAuthConfig(GOOGLE_CONF_AUTH);

	// Utilisation de la connexion
	$service = new Sheets($client);

	// Do a backup of sheet
	backupSheet($service);

	// Prepare insert Row
	$insertRangeRequest = new InsertRangeRequest();
	$gr = new GridRange();
	$gr->setSheetId(0);
	$gr->setStartRowIndex(4);
	$gr->setEndRowIndex(5);
	$gr->setStartColumnIndex(0);
	$gr->setEndColumnIndex(20);

	$insertRangeRequest->setRange($gr);
	$insertRangeRequest->setShiftDimension('ROWS');

	$request = new Request();

	$request->setInsertRange($insertRangeRequest);

	$r = new BatchUpdateSpreadsheetRequest();
	$r->setRequests([$request]);

	$service->spreadsheets->batchUpdate(SPREADSHEET_ID, $r);

	// Fill the new Row
	$valueRange= new ValueRange();
	$valueRange->setValues(["values" => [$date->format("d/M/y"), $operationTypeName, $designation, $tiersName, $debit, $credit]]); // Add two values
	$conf = ["valueInputOption" => "RAW"];
	$service->spreadsheets_values->update(SPREADSHEET_ID, "A5:G5", $valueRange, $conf);

	
}


