s<?php
session_start();
include 'config.php';

// Check if the POST request contains product ID
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    // Sanitize the product ID and fetch the product from the database
    $productId = intval($_POST['id']);  // Ensure the ID is an integer
    $productName = urldecode($_POST['name']); // Decode the product name

    // Prepare the SQL query to prevent SQL injection
    $query = "SELECT * FROM products WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $productId); 
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();

    if ($product) {
        // Initialize cart session if not set
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        // Check if the product is already in the cart
        if (isset($_SESSION['cart'][$productId])) {
            // If the product is already in the cart, increase its quantity
            $_SESSION['cart'][$productId]['quantity'] += 1;
        } else {
            // Otherwise, add the product to the cart
            $_SESSION['cart'][$productId] = [
                'name' => $product['name'],
                'price' => $product['price'],
                'image' => $product['image'],
                'quantity' => 1
            ];
        }

        // Return success response
        echo json_encode(['success' => true]); 
    } else {
        // If product is not found, return error message
        echo json_encode(['success' => false, 'message' => 'Produit non trouvé']);
    }
} else {
    // If the request method is not POST or ID is missing
    echo json_encode(['success' => false, 'message' => 'Requête invalide']);
}
?>
