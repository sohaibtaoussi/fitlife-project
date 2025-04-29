<?php
session_start();
include 'config.php';
// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php "); // Redirect to login page
    exit();
}

$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Cart</title>
    <style>/* Cart Page Styling */
body {
    font-family: Arial, sans-serif;
    background-color: #f5f5f5;
    margin: 0;
    padding: 0;
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
}

.cart-container {
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    width: 90%;
    max-width: 800px;
    text-align: center;
}

h2 {
    color: #333;
}

.empty-cart {
    font-size: 18px;
    color: #777;
}

.cart-table {
    width: 100%;
    border-collapse: collapse;
    margin: 20px 0;
}

.cart-table th, .cart-table td {
    border: 1px solid #ddd;
    padding: 10px;
    text-align: center;
}

.cart-table th {
    background-color: #009688;
    color: white;
}

.cart-table tr:nth-child(even) {
    background-color: #f9f9f9;
}

.cart-table img {
    border-radius: 5px;
}

.delete-btn {
    display: inline-block;
    padding: 8px 12px;
    background-color: #ff5252;
    color: white;
    text-decoration: none;
    border-radius: 5px;
    font-size: 14px;
}

.delete-btn:hover {
    background-color: #d32f2f;
}

.cart-total {
    font-size: 20px;
    margin: 20px 0;
    color: #333;
}

.checkout-btn {
    display: inline-block;
    padding: 12px 20px;
    background-color: #009688;
    color: white;
    text-decoration: none;
    border-radius: 5px;
    font-size: 16px;
    font-weight: bold;
}

.checkout-btn:hover {
    background-color: #00796b;
}
</style>
</head>
<body>

<div class="cart-container">
    <h2>My Cart</h2>

    <?php if (empty($cart)): ?>
        <p class="empty-cart">Your shopping cart is empty.</p>
    <?php else: ?>
        <table class="cart-table">
            <tr>
                <th>Image</th>
                <th>Name</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Total</th>
                <th>Action</th>
            </tr>
            <?php foreach ($cart as $id => $product): ?>
            <tr>
                <td><img src="<?= $product['image'] ?>" width="50"></td>
                <td><?= htmlspecialchars($product['name']) ?></td>
                <td><?= number_format($product['price'], 2, ',', ' ') ?> MAD</td>
                <td><?= $product['quantity'] ?></td>
                <td><?= number_format($product['price'] * $product['quantity'], 2, ',', ' ') ?> MAD</td>
                <td>
                    <a href="remove_from_cart.php?id=<?= $id ?>" class="delete-btn">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
        <p class="cart-total"><strong>Total: <?= number_format(array_sum(array_map(fn($p) => $p['price'] * $p['quantity'], $cart)), 2, ',', ' ') ?> MAD</strong></p>
        <a href="checkout.php" class="checkout-btn">Buy</a>
    <?php endif; ?>
</div>

</body>
</html>
