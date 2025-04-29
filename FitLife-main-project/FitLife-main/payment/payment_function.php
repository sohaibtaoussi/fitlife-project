<?php 
session_start();
require_once 'checkout.php';
require_once "payment_process.php";


$expirationDate = "12-04-2009";
$cardHolder = "";
$cardNumber = "12345";
$Cvv = "000";
$order_id=$_SESSION["order_id"]; 
/**
 *  @SuppressWarnings(PHPMD.CamelCaseParameterName)
 */
 

function validateCardDetails($cardNumber,$cardHolder,$expirationDate,$Cvv){

   //removes spaces and dashes from card number
    $cardNumber = preg_replace('/\s+|-/','',$cardNumber);

    if(strlen($cardNumber) < 13 || strlen($cardNumber) > 19){
        return false ;
    }

    if(empty($cardHolder) || strlen($cardHolder) < 3) {
        return false ;
    }

     // Check expiry date format (MM/YY)
    if (!preg_match('/^(0[1-9]|1[0-2])\/([0-9]{2})$/', $expirationDate, $matches) ) {
        return false;
    }

     // Validate CVV (3-4 digits)
    if (!preg_match('/^[0-9]{3,4}$/', $Cvv)) {
        return false;
    }
    
}

function processPayment($conn,$order_id,$cardNumber){
   
    // Create the payments table if it doesn't exist
    $sql = "CREATE TABLE IF NOT EXISTS payments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        payment_date DATETIME NOT NULL,
        total_amount DECIMAL(10,2) NOT NULL,
        payment_method VARCHAR(50) NOT NULL,
        card_last_four VARCHAR(4) NOT NULL, 
        status VARCHAR(50) NOT NULL DEFAULT 'completed',
        transaction_id VARCHAR(100),
        FOREIGN KEY (order_id) REFERENCES orders(id)    
    )";

    if(!$conn->query($sql)){
        error_log("Error creating payments table :" .$conn->error);
        return false ;
    }

    //get the order total 
    $query ="SELECT total_amount from orders WHERE id = ? ";
    $stmt =$conn->prepare($query);
    $stmt->bind_param("i",$order_id);
    $results= $stmt->get_result();
    $order =$results->fetch_assoc();
     
    if (!$order) {
        error_log("Order not found: $order_id");
        header("Location : checkout.php");
    }

    
    //mask the card number
    $card_last_four = substr(preg_replace('/\s+|-/' , '' ,$cardNumber), -4);
    
    // Generate a fake transaction ID
    $transaction_id ='TX' .time() .rand(1000,9999);

    

    //Record the payment
    $query="INSERT INTO payments (order_id, payment_date, total_amount, payment_method, card_last_four, transaction_id) VALUES (?,NOW(),?,'credit_card',?,?) ";
    $stmt =$conn->prepare($query);
    $stmt->bind_param("idss",$order_id,$order['total_amount'],$card_last_four,$transaction_id);
    
    if($stmt->execute()){
        //Update the order status
        $updated_query="UPDATE orders SET status ='paid' WHERE id=? ";
        $update_stmt =$conn->prepare($updated_query);
        $update_stmt->bind_param("i",$order_id);
        $update_stmt->execute();
        
        return $conn->insert_id;
    }     
    
}



?>