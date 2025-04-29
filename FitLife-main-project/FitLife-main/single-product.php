<?php
// Include database connection
include('config.php');

// Check if 'id' parameter is provided in the URL
if (isset($_GET['id'])) {
    $productId = $_GET['id'];

    // Prepare and execute query to fetch product details from the database using MySQLi
    $query = "SELECT * FROM products WHERE id = ?";
    $stmt = $conn->prepare($query);  // Use MySQLi for preparation
    $stmt->bind_param("i", $productId);  // Bind the product ID as integer
    $stmt->execute();
    
    // Fetch product details
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();

    // Check if the product exists
    if (!$product) {
        die("Product not found!");
    }
} else {
    die("Product ID not specified!");
}

// Pass data to the frontend
$productData = [
    'id' => $product['id'],
    'name' => htmlspecialchars($product['name']),
    'brand' => htmlspecialchars($product['brand']),
    'price' => number_format($product['price'], 2, ',', ' ') . ' MAD',
    'image' => htmlspecialchars($product['image'])
];

// Convert to JSON for JavaScript usage
$productJSON = json_encode($productData);
?>

<script>
    // Pass PHP data to JavaScript
    const product = <?php echo $productJSON; ?>;
    
    // Store product data in local storage
    localStorage.setItem("selectedProduct", JSON.stringify(product));

    // Redirect to the HTML page
    window.location.href = "single-product.html";
</script>
