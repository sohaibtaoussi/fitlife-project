<?php
session_start();
require 'config.php';
require_once 'payment_function.php';
require_once 'checkout.php';

if(!$_SESSION["user_id"]){
    header("Location : login.php");
}

//get cart item and total from session 
$cart_total= isset($_SESSION["cart_total"]) ? $_SESSION["cart_total"] : 0 ;
$user_id=$_SESSION["user_id"];

//get user information 
$query= "SELECT username , email FROM users WHERE id = ? ";
$stmt=$conn->prepare($query);
$stmt->bind_param("i" , $user_id);
$stmt->execute();
$result=$stmt->get_result();
$user =$result->fetch_assoc();

$payment_message ='';
$payment_success = false ;


if($_SERVER["REQUEST_METHOD"] == "POST"){
    $cardNumber = trim($_POST['card_number']);
    $cardHolder =trim($_POST["card_holder"]);
    $expirationDate=trim($_POST["expiration_date"]);
    $Cvv =trim($_POST['Csvv']);

    //details card validation 
    if(validateCardDetails($cardNumber,$cardHolder,$expirationDate , $Cvv)){
        //$order_id=createOrder($conn,$user_id,$cart_total)
        $order_id=$_SESSION["order_id"];
        
        if($order_id){
            //process payment
            $payment_id = processPayment($conn,$order_id,$cardNumber);

            if($payment_id){
                 $payment_success = true ;
                 $payment_message = "Payment successful !" ;


                 // clear the cart 
                 unset($_SESSION['cart']);
                 unset($_SESSION['cart_total']);

                 //redirect to confirmation page 
                 $_SESSION["order_id"] = $order_id;
                 header("Location : payment_confirmation.php");
                 exit();
            }else {
                $payment_message = "Payment processing failed . Please try again.";
            }
                 
        }else {
            $payment_message = "Failed to create order . retry again " ;
        }
    }else {
        $payment_message = "Invalid payment details . Please check and try again ";
    }
    
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - Your E-commerce Store</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>  !!!!!!!!!!!!!!!!!!!
    
    <div class="container">
        <h1>Payment</h1>
        
        <?php if ($payment_message): ?>
            <div class="alert <?php echo $payment_success ? 'success' : 'error'; ?>">
                <?php echo $payment_message; ?>
            </div>
        <?php endif; ?>
        
        <div class="payment-summary">
            <h2>Order Summary</h2>
            <p><strong>Total Amount:</strong> $<?php echo number_format($cart_total, 2); ?></p>
        </div>
        
        <form action="payment.php" method="post" id="payment-form">
            <h2>Payment Details</h2>
            
            <div class="form-group">
                <label for="card_holder">Card Holder Name</label>
                <input type="text" id="card_holder" name="card_holder" value="<?php echo htmlspecialchars($user['username']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="card_number">Card Number</label>
                <input type="text" id="card_number" name="card_number" placeholder="XXXX XXXX XXXX XXXX" maxlength="19" required>
            </div>
            
            <div class="form-row">
                <div class="form-group half">
                    <label for="expiry_date">Expiry Date</label>
                    <input type="text" id="expiry_date" name="expiry_date" placeholder="MM/YY" maxlength="5" required>
                </div>
                
                <div class="form-group half">
                    <label for="cvv">CVV</label>
                    <input type="text" id="cvv" name="cvv" placeholder="XXX" maxlength="4" required>
                </div>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn btn-primary">Complete Payment</button>
            </div>
        </form>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="js/payment.js"></script> !!!!!!!!!!!!!!!!!!!!!
</body>
</html>