<?php

/**
 * Class to handle all db operations
 * This class will have CRUD methods for database tables
 *
 * @author Ravi Tamada
 * @link URL Tutorial link
 */
class DbHandler {

    private $conn;

    function __construct() {
        require_once dirname(__FILE__) . '/DbConnect.php';
        // opening db connection
        $db = new DbConnect();
        $this->conn = $db->connect();
    }

    /* ------------- `users` table method ------------------ */

    /**
     * Creating new Item
     * @param String $name User full name
     * @param String $email User login email id
     * @param String $password User login password
     */
    public function newItem($item_name, $item_type) {

			$response = array();
			
			// insert query
            $stmt = $this->conn->prepare("INSERT INTO items(item_name,item_type) values (?, ?) ");
            $stmt->bind_param("ss", $item_name,$item_type);
            $result = $stmt->execute();
            $stmt->close();

            // Check for successful insertion
            if ($result) {
                 $last_Item_id = $this->conn->insert_id;
                 session_start();
                 $_SESSION["last_Item_id"] = $last_Item_id;
                //  echo '<pre>'; print_r($_SESSION); echo '</pre>'; die;
                // Item successfully inserted
                return ITEM_INSERTED_SUCCESSFULLY;
            } else {
                // Failed to create user
                return ITEM_INSERTED_FAILED;
            }
        

        return $response;
    }
	
	
	 /**
     * Dispaly All Itesm
     * 
     */
    public function displayItems() {

			$response = array();
			
			$stmt = $this->conn->prepare("SELECT item_id,item_name,item_type FROM items");
			//$stmt->bind_param("s", $userid);
			$stmt->execute();
			$items = $stmt->get_result();
			$stmt->close();
		   //echo '<pre>'; print_r($orders); echo '</pre>'; die;
			return $items;
    }
	
   
}

?>
