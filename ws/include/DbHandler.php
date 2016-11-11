<?php
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
			$connection = $this->conn;
			$response = array();
			//Insert
            $insertItem = $connection->prepare("INSERT INTO items(item_name,item_type) values (?, ?) ");
            $insertItem->bind_param("ss", $item_name,$item_type);
            $result = $insertItem->execute();
            $insertItem->close();
            // Check for successful insertion
            if ($result) {
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
			$stmt = $this->conn->prepare("SELECT item_id,item_name,item_type,quantity,retail_rate FROM items");
			//$stmt->bind_param("s", $userid);
			$stmt->execute();
			$items = $stmt->get_result();
			$stmt->close();
		   //echo '<pre>'; print_r($orders); echo '</pre>'; die;
			return $items;
    }
	/**
	*Get Items By Name
	**/
	public function getItemsByName($item_name_str) {
			$stmt = $this->conn->prepare("SELECT item_id,item_name,item_type,quantity,retail_rate FROM items where upper(item_name) like upper(concat('%',?,'%'))");
			$stmt->bind_param("s", $item_name_str);
			$stmt->execute();
			$items = $stmt->get_result();
			$stmt->close();
		   //echo '<pre>'; print_r($orders); echo '</pre>'; die;
			return $items;
    }
	/**
	*Adding stock of items 
	**/
	public function addStock($item_id, $order_number, $date_purchase, $quantity, $purchase_price, $retail_price){
		$response = array();
		
		$connection = $this->conn;
			
			// Insert in stock log table 
			$insertInStockLog = $connection->prepare("INSERT INTO `medic`.`item_purchase_log`
													(`item_purchase_log_item`,
													`item_purchase_log_order_num`,
													`item_purchase_log_date_purchase`,
													`item_purchase_log_quantity`,
													`item_purchase_log_rate`,
													`item_purchase_log_total`)
													VALUES(?,?,?,?,?,?)");
			$total = $purchase_price*$quantity;
            $insertInStockLog->bind_param("ississ", $item_id,$order_number,$date_purchase,$quantity,$purchase_price, $total);
			
			$updateStockOfItem = $connection->prepare("UPDATE `medic`.`items` SET
														`quantity` = ?,
														`retail_rate` = ?
														WHERE `item_id` = ?");
														
			$updateStockOfItem->bind_param("isi",$quantity, $retail_price, $item_id);
			
			// Insert 
            $insertInStockLog->execute();
            $result1 = $insertInStockLog->close();
			$updateStockOfItem->execute();
			$result2 = $updateStockOfItem->close();
			
            // Check for successful insertion
            if ($result1 && $result2) {
                return STOCK_ADDED_SUCCESSFULLY;
            } else {
                // Failed to create user
                return STOCK_ADDED_FAILED;
            }
        return $response;
		
	}
}

?>
