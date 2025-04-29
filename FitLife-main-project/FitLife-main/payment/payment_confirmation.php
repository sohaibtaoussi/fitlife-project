<?php 
session_start();
require_once 'config.php';

if(!isset($_SESSION['order_id'])){
    header("Location : ----------");
    exit();
}

$order_id=$_SESSION['order_id'];
$user_id=$_SESSION['user_id'];

//GET order details 
$query="SELECT o.*, p.transaction_id, p.card_last_four 
                       FROM orders o 
                       LEFT JOIN payments p ON o.id = p.order_id 
                       WHERE o.id = ? AND o.user_id = ?" ;
$stmt =$conn->prepare($query);
$stmt->bind_param("ii" ,$order_id , $user_id);
$stmt->execute();
$results =$stmt->get_result();
$order= $results->fetch_assoc();

//if order not found or doesn't belong to user 
if(!$order){
    header("Location : -------");
    exit();
}

unset($_SESSION["order_id"]);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Confirmation - Your E-commerce Store</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <div class="confirmation-box">
            <h1>Payment Confirmation</h1>
            
            <div class="success-message">
                <svg class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                    <circle class="checkmark-circle" cx="26" cy="26" r="25" fill="none"/>
                    <path class="checkmark-check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
                </svg>
                <h2>Thank You for Your Order!</h2>
            </div>
            
            <div class="order-details">
                <h3>Order Summary</h3>
                <ul>
                    <li><strong>Order Number:</strong> #<?php echo $order_id; ?></li>
                    <li><strong>Date:</strong> <?php echo date('F j, Y, g:i a', strtotime($order['order_date'])); ?></li>
                    <li><strong>Total Amount:</strong> $<?php echo number_format($order['total_amount'], 2); ?></li>
                    <li><strong>Payment Method:</strong> Credit Card (ending in <?php echo $order['card_last_four']; ?>)</li>
                    <li><strong>Transaction ID:</strong> <?php echo $order['transaction_id']; ?></li>
                </ul>
            </div>
            
            <p>A confirmation email has been sent to your registered email address.</p>
            
            <div class="actions">
                <a href="index.php" class="btn">Continue Shopping</a>
                <a href="profile.php?tab=orders" class="btn btn-secondary">View Your Orders</a>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>


