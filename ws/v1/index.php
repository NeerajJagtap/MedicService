<?php

require_once '../include/DbHandler.php';
require_once '../include/PassHash.php';
require '.././libs/Slim/Slim.php';

\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();

// User id from db - Global Variable
$user_id = NULL;

/**
 * Adding Middle Layer to authenticate every request
 * Checking if the request has valid api key in the 'Authorization' header
 */
function authenticate(\Slim\Route $route) {
    // Getting request headers
    $headers = apache_request_headers();
    $response = array();
    $app = \Slim\Slim::getInstance();

    // Verifying Authorization Header
    if (isset($headers['Authorization'])) {
        $db = new DbHandler();

        // get the api key
        $api_key = $headers['Authorization'];
        // validating api key
        if (!$db->isValidApiKey($api_key)) {
            // api key is not present in users table
            $response["error"] = true;
            $response["message"] = "Access Denied. Invalid Api key";
            echoRespnse(401, $response);
            $app->stop();
        } else {
            global $user_id;
            // get user primary key id
            $user_id = $db->getUserId($api_key);
        }
    } else {
        // api key is missing in header
        $response["error"] = true;
        $response["message"] = "Api key is misssing";
        echoRespnse(400, $response);
        $app->stop();
    }
}


/**
 * ----------- METHODS WITHOUT AUTHENTICATION ---------------------------------
 */
/**
 * Item Insertion
 * url - /item
 * method - POST
 * params - item_name, item_type
 */
$app->post('/item', function() use ($app) {
            // check for required params
            verifyRequiredParams(array('item_name', 'item_type',));

            $response = array();

            //reading post params
            $item_name = $app->request->post('item_name');
            $item_type = $app->request->post('item_type');
           
            $db = new DbHandler();
            $res = $db->newItem($item_name, $item_type);

            if ($res == ITEM_INSERTED_SUCCESSFULLY) {
                $response["error"] = false;
                $response["message"] = "great Jack! Atlast you have inserted! no matter its item.Good job";
            } else if ($res == ITEM_INSERTED_FAILED) {
                $response["error"] = true;
                $response["message"] = "Oops! failed..! leran to insert, you are going to married man.";
            } 
            // echo json response
            echoRespnse(201, $response);
        });
		
		
/**
 * Display Items
 * url - /displayitems
 * method - GET
 * params - item_name, item_type
 */
$app->get('/displayitems', function() use ($app) {
            
            $response = array();
            $db = new DbHandler();
            // fetch items
            $result = $db->displayItems();

            $response["error"] = false;
            $response["items"] = array();

            // looping through result and preparing orders array
            while ($items = $result->fetch_assoc()) {
                $tmp = array();
                $tmp["item_id"] = $items["item_id"];
                $tmp["item_name"] = $items["item_name"];
				$tmp["item_type"] = $items["item_type"];
                array_push($response["items"], $tmp);
            }
            
            echoRespnse(200, $response);
        });




/**
 * Verifying required params posted or not
 */
function verifyRequiredParams($required_fields) {
    $error = false;
    $error_fields = "";
    $request_params = array();
    $request_params = $_REQUEST;
    // Handling PUT request params
    if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
        $app = \Slim\Slim::getInstance();
        parse_str($app->request()->getBody(), $request_params);
    }
    foreach ($required_fields as $field) {
        if (!isset($request_params[$field]) || strlen(trim($request_params[$field])) <= 0) {
            $error = true;
            $error_fields .= $field . ', ';
        }
    }

    if ($error) {
        // Required field(s) are missing or empty
        // echo error json and stop the app
        $response = array();
        $app = \Slim\Slim::getInstance();
        $response["error"] = true;
        $response["message"] = 'Required field(s) ' . substr($error_fields, 0, -2) . ' is missing or empty';
        echoRespnse(400, $response);
        $app->stop();
    }
}



/**
 * Echoing json response to client
 * @param String $status_code Http response code
 * @param Int $response Json response
 */
function echoRespnse($status_code, $response) {
    $app = \Slim\Slim::getInstance();
    // Http response code
    $app->status($status_code);

    // setting response content type to json
    $app->contentType('application/json');

    echo json_encode($response);
}

$app->run();
?>