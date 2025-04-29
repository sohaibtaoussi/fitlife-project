<?php 
session_start();
include 'config.php';
require_once 'payment_process.php';
require_once 'payment_function.php';


$order_success = false ;
$order_id = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["Chekout"])){
    $first_name=trim($_POST['fisrt_name']);
    $last_name=trim($_POST['last_name']);
    $email=trim($_POST['email']);
    $address=trim($_POST["address"]);
    $city=trim($_POST["city"]);
    $postal_code = trim($_POST['postal_code']);
    $country = trim($_POST['country']);
    $payment_method = $_POST['payment_method'];

    if(empty($first_name)){
        echo "First name is required";
    }
    if(empty($last_name)){
        echo "last name is required";
    }
    if(empty($email) || filter_var($email,FILTER_VALIDATE_EMAIL)){
        echo " email invalid";
    }
    if(empty($address)){
        echo "Address is required";
    }
    if(empty($city)){
        echo "city is required";
    }
    if(empty($postal_code)){
        echo "postal code is required";
    }
    if(empty($country) ){
        echo "country is required";
    }
    if(empty($payment_method)){
        echo "paymentmethod is required";
    }

    $conn->begin_transaction();
    if($conn){
        //insert customer data 
        $query="INSERT INTO customers (first_name, last_name, email, address, city, postal_code, country) VALUES (?,?,?,?,?,?,?) ";
        $stmt=$conn->prepare($query);
        $stmt->bind_param("sssssss",$first_name,$last_name,$email,$address,$city,$postal_code,$country);
        $stmt->execute();
        $customer_id =$conn->insert_id;

        // Create the orders table if it doesn't exist
        $sql = "CREATE TABLE IF NOT EXISTS orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        order_date DATETIME NOT NULL,
        total_amount DECIMAL(10,2) NOT NULL,
        status VARCHAR(50) NOT NULL DEFAULT ,
        FOREIGN KEY (user_id) REFERENCES users(id)
        )";
    
        if (!$conn->query($sql)) {
        error_log("Error creating orders table: " . $conn->error);
        return false;
        }

        //insert order data
        $order_status = 'pending';
        $order_query ="INSERT into orders (customer_id,order_date ,total_amount ,status,payment_method) VALUES (?,NOW(),?,?,?)";
        $stmt=$comm->prepare($order_query);
        $stmt->bind_param("isds",$customer_id,$cart_total,$order_date,$payment_method);
        $stmt->execute();
        $order_id=$conn->insert_id;

        // Insert order items 
        foreach ($_SESSION['cart '] as $product_id => $item ){
            $item_query="INSERT INTO order_items (order_id,product_id,quantity,price) VALUES (?,?,?,?)";
            $stmt=$conn->prepare($item_query);
            $stmt->bind_param("iiid", $order_id,$product_id,$item['quantity'],$item['price']);
            $stmt->execute();

        }
        if ($order_success) {
            // Store order ID in session
            $_SESSION['order_id'] = $order_id;
            
            // Redirect to payment page
            header("Location: payment_process.php");
            exit;
        }
    }else {
        $conn->rollback();
        die("Order processing failed");
    }
}
// Get cart items from session
if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    // Prepare query to get product details
    $product_ids = array_keys($_SESSION['cart']);
    $ids_str = implode(',', array_map('intval', $product_ids));
    
    $sql = "SELECT id, name, price, image FROM products WHERE id IN ($ids_str)";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $product_id = $row['id'];
            $quantity = $_SESSION['cart'][$product_id]['quantity'];
            $item_total = $row['price'] * $quantity;
            
            $cart_items[] = [
                'id' => $product_id,
                'name' => $row['name'],
                'price' => $row['price'],
                'quantity' => $quantity,
                'image' => $row['image'],
                'total' => $item_total
            ];
            
            $cart_total += $item_total;
        }
    }
}


// Close connection
$conn->close();
    


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Your E-commerce Store</title>
    <link rel="stylesheet" href="css/styles.css">
    <script src="https://js.stripe.com/v3/"></script>
</head>
<body>
    <header>
        <div class="container">
            <div class="logo">
                <a href="index.php">Your E-commerce Store</a>
            </div>
            <nav>
                <ul>
                    <li><a href="index.html">Home</a></li>
                    <li><a href="products.php">Products</a></li>
                    <li><a href="cart.php">Cart</a></li>
                    <li><a href="profilr.php">My Account</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="container">
        <h1>Checkout</h1>
        
        <?php if ($order_success): ?>
            <div class="success-message">
                <h2>Thank you for your order!</h2>
                <p>Your order #<?php echo $order_id; ?> has been successfully placed.</p>
                <p>You will receive a confirmation email shortly.</p>
                <a href="index.html" class="btn">Continue Shopping</a>
            </div>
        <?php elseif (empty($cart_items)): ?>
            <div class="empty-cart">
                <p>Your cart is empty. Please add some products before checking out.</p>
                <a href="products.php" class="btn">Browse Products</a>
            </div>
        <?php else: ?>
            <?php if (!empty($errors)): ?>
                <div class="error-messages">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <div class="checkout-container">
                <div class="checkout-form">
                    <h2>Shipping & Billing Information</h2>
                    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                        <div class="form-group">
                            <label for="first_name">First Name*</label>
                            <input type="text" id="first_name" name="first_name" required 
                                   value="<?php echo isset($first_name) ? htmlspecialchars($first_name) : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="last_name">Last Name*</label>
                            <input type="text" id="last_name" name="last_name" required
                                   value="<?php echo isset($last_name) ? htmlspecialchars($last_name) : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email*</label>
                            <input type="email" id="email" name="email" required
                                   value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="address">Address*</label>
                            <input type="text" id="address" name="address" required
                                   value="<?php echo isset($address) ? htmlspecialchars($address) : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="city">City*</label>
                            <input type="text" id="city" name="city" required
                                   value="<?php echo isset($city) ? htmlspecialchars($city) : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="postal_code">Postal Code*</label>
                            <input type="text" id="postal_code" name="postal_code" required
                                   value="<?php echo isset($postal_code) ? htmlspecialchars($postal_code) : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="country">Country*</label>
                            <select id="country" name="country" required>
                                <option value="">Select a country</option>
                                <option value="USA" <?php echo (isset($country) && $country == 'USA') ? 'selected' : ''; ?>>United States</option>
                                <option value="Canada" <?php echo (isset($country) && $country == 'Canada') ? 'selected' : ''; ?>>Canada</option>
                                <option value="UK" <?php echo (isset($country) && $country == 'UK') ? 'selected' : ''; ?>>United Kingdom</option>
                                <option value="France" <?php echo (isset($country) && $country == 'France') ? 'selected' : ''; ?>>France</option>
                                <option value="Morocco" <?php echo (isset($country) && $country == 'Morocco') ? 'selected' : ''; ?>>Morocco</option>
                                <!-- Add more countries as needed -->
                                <!-- Add more countries as needed -->
                            </select>
                        </div>
                        
                        <h2>Payment Method</h2>
                        <div class="payment-methods">
                            <div class="payment-option">
                                <input type="radio" id="credit_card" name="payment_method" value="credit_card" 
                                       <?php echo (isset($payment_method) && $payment_method == 'credit_card') ? 'checked' : ''; ?> required>
                                <label for="credit_card">Credit Card</label>
                                
                                <div class="payment-details" id="credit_card_details">
                                    <div class="form-group">
                                        <label for="card_number">Card Number</label>
                                        <input type="text" id="card_number" name="card_number" placeholder="1234 5678 9012 3456">
                                    </div>
                                    
                                    <div class="form-row">
                                        <div class="form-group half">
                                            <label for="expiry_date">Expiry Date</label>
                                            <input type="text" id="expiry_date" name="expiry_date" placeholder="MM/YY">
                                        </div>
                                        
                                        <div class="form-group half">
                                            <label for="cvv">CVV</label>
                                            <input type="text" id="cvv" name="cvv" placeholder="123">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="payment-option">
                                <input type="radio" id="paypal" name="payment_method" value="paypal"
                                       <?php echo (isset($payment_method) && $payment_method == 'paypal') ? 'checked' : ''; ?>>
                                <label for="paypal">PayPal</label>
                            </div>
                        </div>
                        
                        <div class="checkout-actions">
                            <a href="cart.php" class="btn secondary">Back to Cart</a>
                            <button type="submit" name="checkout" class="btn primary">Complete Order</button>
                        </div>
                    </form>
                </div>
                
                <div class="order-summary">
                    <h2>Order Summary</h2>
                    <div class="cart-items">
                        <?php foreach ($cart_items as $item): ?>
                            <div class="cart-item">
                                <div class="item-image">
                                    <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                                </div>
                                <div class="item-details">
                                    <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                                    <p>Quantity: <?php echo $item['quantity']; ?></p>
                                    <p class="item-price">$<?php echo number_format($item['price'], 2); ?></p>
                                </div>
                                <div class="item-total">
                                    $<?php echo number_format($item['total'], 2); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="order-totals">
                        <div class="subtotal">
                            <span>Subtotal</span>
                            <span>$<?php echo number_format($cart_total, 2); ?></span>
                        </div>
                        
                        <div class="shipping">
                            <span>Shipping</span>
                            <span>$<?php echo number_format(10.00, 2); ?></span>
                        </div>
                        
                        <div class="tax">
                            <span>Tax (10%)</span>
                            <span>$<?php echo number_format($cart_total * 0.1, 2); ?></span>
                        </div>
                        
                        <div class="total">
                            <span>Total</span>
                            <span>$<?php echo number_format($cart_total + 10.00 + ($cart_total * 0.1), 2); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>About Us</h3>
                    <p>Your trusted online shopping destination for quality products.</p>
                </div>
                
                <div class="footer-section">
                    <h3>Customer Service</h3>
                    <ul>
                        <li><a href="contact.php">Contact Us</a></li>
                        <li><a href="faq.php">FAQ</a></li>
                        <li><a href="shipping.php">Shipping & Returns</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h3>My Account</h3>
                    <ul>
                        <li><a href="account.php">Account Details</a></li>
                        <li><a href="orders.php">Order History</a></li>
                        <li><a href="wishlist.php">Wishlist</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h3>Follow Us</h3>
                    <div class="social-links">
                        <a href="#"><img src="images/facebook.png" alt="Facebook"></a>
                        <a href="#"><img src="images/twitter.png" alt="Twitter"></a>
                        <a href="#"><img src="images/instagram.png" alt="Instagram"></a>
                    </div>
                </div>
            </div>
            
            <div class="copyright">
                <p>&copy; <?php echo date('Y'); ?> Your E-commerce Store. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        // Toggle payment details based on selected payment method
        document.addEventListener('DOMContentLoaded', function() {
            const paymentMethods = document.querySelectorAll('input[name="payment_method"]');
            const creditCardDetails = document.getElementById('credit_card_details');
            
            paymentMethods.forEach(function(method) {
                method.addEventListener('change', function() {
                    if (this.value === 'credit_card') {
                        creditCardDetails.style.display = 'block';
                    } else {
                        creditCardDetails.style.display = 'none';
                    }
                });
            });
            
            // Trigger the change event on page load
            document.querySelector('input[name="payment_method"]:checked')?.dispatchEvent(new Event('change'));
        });
    </script>
</body>
</html>