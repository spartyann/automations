<?php

use Google\Service\Sheets;
use Google\Service\Sheets\AddSheetRequest;
use Google\Service\Sheets\AppendCellsRequest;
use Google\Service\Sheets\BatchUpdateSpreadsheetRequest;
use Google\Service\Sheets\BatchUpdateValuesRequest;
use Google\Service\Sheets\CellData;
use Google\Service\Sheets\ExtendedValue;
use Google\Service\Sheets\GridRange;
use Google\Service\Sheets\InsertRangeRequest;
use Google\Service\Sheets\Request;
use Google\Service\Sheets\RowData;
use Google\Service\Sheets\SheetProperties;
use Google\Service\Sheets\Spreadsheet;
use Google\Service\Sheets\ValueRange;

require_once(dirname(__DIR__) . '/vendor/autoload.php');

// https://www.thanh-nguyen.fr/extraire-donnees-feuille-de-calcul-google-sheets-avec-php/

$client = new \Google_Client();
$client->setApplicationName('Google Sheets API');
$client->setScopes([Sheets::SPREADSHEETS]);
$client->setAccessType('offline');
$path = __DIR__.'/credentials.json'; // PATH = chemin physique vers le fichier contenant la clé du compte de service
$client->setAuthConfig($path);



// Utilisation de la connexion
$service = new Sheets($client);
// Désignation de la feuille de calcul

$spreadsheetId = '1fHkqamvPB2Nw1fQKyDbts9IUXBKk5ofSq44P5wxrONI';


$spreadsheet = $service->spreadsheets->get($spreadsheetId);
$sheet = $spreadsheet->getSheets()[0];

$range = 'A4:G10000';

/*
$appendRequest = new AppendCellsRequest();
$appendRequest->setSheetId(0);
$_row = new RowData();
$_cell = new CellData();
$_val = new ExtendedValue();
$_val->setStringValue('TOTO');
$_cell->setUserEnteredValue($_val);
$_row->setValues([$_cell]);
$appendRequest->setRows([$_row,]);
$appendRequest->setFields('*');*/


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

/*
// https://stackoverflow.com/questions/76632972/google-sheets-php-api-trouble-with-adding-multiple-tabs
$request2 = new AddSheetRequest();
$props = new SheetProperties();
$props->setTitle('Test');
$request2->setProperties($props);
*/

$request->setInsertRange($insertRangeRequest);
//$request->setAppendCells($appendRequest);
//$request->setAddSheet($request2);

$r = new BatchUpdateSpreadsheetRequest();
$r->setRequests([$request]);

$service->spreadsheets->batchUpdate($spreadsheetId, $r);

$response = $service->spreadsheets_values->get($spreadsheetId, $range);
$rows = $response->getValues();



// Create the value range Object
$valueRange= new ValueRange();
$valueRange->setValues(["values" => ["06/02/2025", "Virement", "Toto", "Client", "10"]]); // Add two values
$conf = ["valueInputOption" => "RAW"];
$service->spreadsheets_values->update($spreadsheetId, "A5:G5", $valueRange, $conf);


var_dump($rows);
exit();

// Extraction de l'entête


$headers = array_shift($rows);
// Création du tableau associatif


$array = [];
foreach ($rows as $row) {
    $array[] = array_combine(array_intersect_key($headers, $row), array_intersect_key($row, $headers));
}





function addTabs($sheetId, array $tabNames) {
    $requests = [];
    foreach ($tabNames as $index => $tabName) {
        $addSheet = new \Google\Service\Sheets\Request(); // Added
        $request = new AddSheetRequest();
        $props = new SheetProperties();
        $props->setTitle($tabName);
        $props->setIndex($index);
        $request->setProperties($props);
        $addSheet->setAddSheet($request); // Added
        array_push($requests, $addSheet); // Added
    }
    $batchRequest = new BatchUpdateSpreadsheetRequest();
    $batchRequest->setRequests($requests);
    try {
        $this->service->spreadsheets->batchUpdate($sheetId, $batchRequest);
    } catch (ServiceException $e) {
        dd($e);
    }
}
