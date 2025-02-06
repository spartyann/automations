<?php


// https://apps.lavoixduzen.fr/automation/helloasso.php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once(__DIR__ . '/vendor/autoload.php');
require_once(__DIR__ . '/conf.php');
require_once(__DIR__ . '/lib.php');

// Get HelloAsso Data
$body_content = file_get_contents('php://input');
if($body_content == null || $body_content == '') $body_content = file_get_contents(__DIR__ . '/sample_course.json');


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
        $firstName = $hellAssoData->data->payer->firstName;
        $lastName = $hellAssoData->data->payer->lastName;
        $email = $hellAssoData->data->payer->email;
        
        
        foreach ($hellAssoData->data->items as $item)
        {
            
            if ($item->type == 'Product')
            {
                $name = formatCourseName($item->name);
            
                if (isset($coursesIdByTitle[$name]))
                {
                    $courseId = $coursesIdByTitle[$name];
                    $price = $item->amount/100;
                    $orderPaymentId = 'helloasso';
                    
                    $invoiceId = 'helloasso';
                    
                    foreach ($item->payments as $payment) {
                        $invoiceId .= '-' . $payment->id;
                        $orderPaymentId .= '-' . $payment->id;
                    }
                    
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
            }
            
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