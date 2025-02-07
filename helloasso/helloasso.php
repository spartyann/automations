<?php


// https://apps.lavoixduzen.fr/automation/helloasso.php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once(__DIR__ . '/vendor/autoload.php');
require_once(__DIR__ . '/conf.php');
require_once(__DIR__ . '/lib.php');
require_once(__DIR__ . '/lib_gsheet.php');

// Get HelloAsso Data
$body_content = file_get_contents('php://input');
if($body_content == null || $body_content == '')
{
	$body_content = file_get_contents(__DIR__ . '/sample_course.json');
	$body_content = file_get_contents(__DIR__ . '/sample_adhesion.json');
	//$body_content = file_get_contents(__DIR__ . '/sample_flex_paiement.json');
	//$body_content = file_get_contents(__DIR__ . '/sample_products.json');
}


$hellAssoData = json_decode($body_content);

try {
    
    if ($hellAssoData == null) throw new \Exception('Error deconding HelloAsso Data');
    
    // Get all Course and class by Name
    $allCourses = callApi([ 'task' => 'getcourses' ]);
    $coursesIdByTitle = [];
    foreach($allCourses->courses as $course) $coursesIdByTitle[formatCourseName($course->title)] = $course->id;
    
    
    $newcoursepurchaseRes = [];
    $createuserRes = [];
    $createAccount = false;
    
    // Extract data from Hello Asso
    if ($hellAssoData->eventType == 'Order')
    {
		// Do a backup of Compta sheet
		backupComptaSheet('-before');

        $firstName = $hellAssoData->data->payer->firstName;
        $lastName = $hellAssoData->data->payer->lastName;
        $email = $hellAssoData->data->payer->email;

		$tiers = $firstName . ' ' . $lastName;
        
        foreach ($hellAssoData->data->items as $item)
        {
			$itemName = isset($item->name) ? $item->name : '';

			$price = $item->amount/100;
            
            if ($item->type == 'Product')
            {
                $itemName = formatCourseName($item->name);
				
				$designation = 'Achat: ' . $itemName;
				$orderPaymentId = 'helloasso';
				$invoiceId = 'helloasso';
		
				foreach ($item->payments as $payment) {
					$invoiceId .= '-' . $payment->id;
					$orderPaymentId .= '-' . $payment->id;
					$designation .= ' - ' . $payment->id;
				}

                if (isset($coursesIdByTitle[$itemName]))
                {
                    $courseId = $coursesIdByTitle[$itemName];

                    $newcoursepurchaseRes[] = callApi([
                        'task' => 'newcoursepurchase',
                        'course_id' => $courseId,
                        'email' => $email,
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                        'price' => $price,
                        'orderPaymentId' => $orderPaymentId,
                        'invoiceId' => $invoiceId,
                        'orderPaymentMethod' => 'HelloAsso',
                        'orderPaymentPrice' => $price,
                    ]);
                }
                
            }
            elseif ($item->type == 'Membership')  // Adhésion
            {
                $createAccount = true;

				$designation = 'Adhésion: ' . $itemName;
				$paymentInfos = 'Helloasso: '. $itemName;

				foreach ($item->payments as $payment) {
					$designation .= ' - ' . $payment->id;
					$paymentInfos .= ' - ' . $payment->id;
				}

				addAdhesionLine(new \DateTime(), $firstName, $lastName, $email, $price, $paymentInfos, '');
            }
			elseif ($item->type == 'Donation')  // Adhésion
            {
                $createAccount = true;

				$designation = 'Donation';
				foreach ($item->payments as $payment) $designation .= ' - ' . $payment->id;
            }
			else
			{
				$designation = $item->type . ' - ' . $itemName;
				foreach ($item->payments as $payment) $designation .= ' - ' . $payment->id;
			}

			addBillingLine(new \DateTime(), 'Helloasso', $designation, $tiers, $email, 0, $price);
            
        }
        
    }
    else {
        exit(0);
        
        throw new Stop('Event Type not implemented'); // Ne pas stoker l'info
    }
    
    if ($createAccount)
    {
         $createuserRes[] = callApi([
            'task' => 'createuser',
            'email' => $email,
            'first_name' => $firstName,
            'last_name' => $lastName,
        ]);
    }
    
	// Do a backup of Compta sheet
	//backupSheet();

    saveDebug([
        'createAccount' => $createAccount,
        'createuser_result' => $createuserRes,
        'newcoursepurchase_result' => $newcoursepurchaseRes,
        'hello_ass_content' => $hellAssoData
    ]);
    
} catch (Stop $th) {
    saveDebug([
        'message' => $th->getMessage(), 
        'GET' => $_GET,    
        'POST' => $_POST,
        'CONTENT_JSON' => $hellAssoData,
    ]);
    
    echo $th->getMessage() . "\n\n";
   
} catch (\Throwable $th) {
    
    // ========================= TODO ERROR
	
	saveDebug([
        'GET' => $_GET,    
        'POST' => $_POST,
        'CONTENT' => $body_content,
        'CONTENT_JSON' => $hellAssoData,
        'EXCEPTION' => [
            'message' => $th->getMessage(),
            'code' => $th->getCode(),
            'file' => $th->getFile(),
            'line' => $th->getLine(),
            'trace' => $th->getTrace(),
        ]
    ]);
    
    echo $th->getMessage() . "\n\n";
}


echo "\n\nTerminé";