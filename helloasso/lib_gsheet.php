<?php

use Google\Service\Sheets;
use Google\Service\Sheets\BatchUpdateSpreadsheetRequest;
use Google\Service\Sheets\CellData;
use Google\Service\Sheets\ExtendedValue;
use Google\Service\Sheets\GridCoordinate;
use Google\Service\Sheets\GridRange;
use Google\Service\Sheets\InsertRangeRequest;
use Google\Service\Sheets\Request;
use Google\Service\Sheets\RowData;
use Google\Service\Sheets\UpdateCellsRequest;
use Google\Service\Sheets\ValueRange;

$client = new \Google_Client();
$client->setApplicationName('Google Sheets API');
$client->setScopes([Sheets::SPREADSHEETS]);
$client->setAccessType('offline');

$client->setAuthConfig(GOOGLE_CONF_AUTH);

// Utilisation de la connexion
$service = new Sheets($client);

function backupComptaSheet(string $sfx = ''){
	global $service;

	return backupSheet(SPREADSHEET_COMPTA_ID, __DIR__ . '/compta', $sfx);
}

function backupAdhesionSheet(string $sfx = ''){
	global $service;

	return backupSheet(SPREADSHEET_ADHESION_ID, __DIR__ . '/adhesions', $sfx);
}

function backupSheet(string $sheetId, string $dir, string $sfx = ''){
	global $service;

	// Get all
	$response = $service->spreadsheets_values->get($sheetId, "A1:Z10000");
	$rows = $response->getValues();

	$fp = fopen($dir . '/bak-' . str_replace(':', '-', date('c')) . $sfx . '.csv', 'w'); 
	
	try { foreach ($rows as $fields) fputcsv($fp, $fields); }
	finally { fclose($fp); }

	return $rows;
}

function buildRowFromValues($values){

	$_row = new RowData();
	$vals = [];
	foreach($values as $value)
	{
		$_cell = new CellData();
		$_val = new ExtendedValue();
		if ($value instanceof ExtendedValue) $_val = $value;
		else if (is_bool($value))$_val->setBoolValue($value);
		else if (is_float($value)) $_val->setNumberValue($value);
		else $_val->setStringValue($value);
		
		$_cell->setUserEnteredValue($_val);
		$vals[] = $_cell;
	}
	$_row->setValues($vals);

	return $_row;
}

function stringValue($value){
	$_val = new ExtendedValue();
	$_val->setStringValue($value);
	return $_val;
}

function formulaValue($value){
	$_val = new ExtendedValue();
	$_val->setFormulaValue($value);
	return $_val;
}

function sheetInsertLine($spreadsheetId, $sheetId, $rowIndexStart, array $line){
	global $service;

	// Prepare insert Row
	$insertRangeRequest = new InsertRangeRequest();
	$gr = new GridRange();
	$gr->setSheetId($sheetId);
	$gr->setStartRowIndex($rowIndexStart);
	$gr->setEndRowIndex($rowIndexStart + 1);
	$gr->setStartColumnIndex(0);
	$gr->setEndColumnIndex(20);

	$insertRangeRequest->setRange($gr);
	$insertRangeRequest->setShiftDimension('ROWS');

	$insertLineRequest = new Request();
	$insertLineRequest->setInsertRange($insertRangeRequest);

	
	$updateRequest = new UpdateCellsRequest();

	$updateRequest->setRows([ buildRowFromValues($line) ]);

	$updateRequest->setFields('*');

	$_start = new GridCoordinate();
	$_start->setSheetId($sheetId);
	$_start->setRowIndex($rowIndexStart);
	$_start->setColumnIndex(0);
	$updateRequest->setStart($_start);


	$requestUpdateCells = new Request();

	$requestUpdateCells->setUpdateCells($updateRequest);

	$r = new BatchUpdateSpreadsheetRequest();
	$r->setRequests([
		$insertLineRequest,
		$requestUpdateCells
	]);

	$service->spreadsheets->batchUpdate($spreadsheetId, $r);
}



function addBillingLine(\DateTime $date, string $operationTypeName, string $designation, string $tiersName, string $email, float $debit, float $credit)
{
	sheetInsertLine(SPREADSHEET_COMPTA_ID, SHEET_COMPTA_ID, COMPTA_LINE_START, [
		$date->format("d/m/Y"), $operationTypeName, $designation, $tiersName, $email,
		$debit == 0 ? '' : $debit,
	 	$credit == 0 ? '' : $credit
	]);
}

function addAdhesionLine(\DateTime $date, string $firstName, string $lastName, string $email, float $price, string $paymentInfos, string $comment)
{
	global $service;

	$rows = backupAdhesionSheet('before');

	sheetInsertLine(SPREADSHEET_ADHESION_ID, SHEET_ADHESION_ID, ADHESION_LINE_START, [
		stringValue($firstName),
		stringValue($lastName),
		stringValue($email),
		false,
		false,
		stringValue($date->format("d/m/Y")),
		formulaValue('=IF(F5 = ""; ""; IF(EDATE(F5; 12) <= TODAY(); "Expiré"; "Actif"))'),
		$price,
		stringValue($paymentInfos)
	]);
	
}