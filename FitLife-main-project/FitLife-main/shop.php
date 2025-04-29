<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include 'config.php';

// Check if the user is logged in
$isLoggedIn = isset($_SESSION['user_id']);

// Get cart count safely
$cart_count = isset($_SESSION['cart']) ? array_sum(array_map(fn($p) => $p['quantity'], $_SESSION['cart'])) : 0;
// Fetch products from database
$result = mysqli_query($conn, "SELECT * FROM products");
if (!$result) {
    die("Database query failed: " . mysqli_error($conn));
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FitLife - Shop</title>
    <link rel="stylesheet" href="shop.css">
    <!-- SweetAlert2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        .cart-link {
            text-decoration: none;
            font-size: 18px;
            font-weight: bold;
            color: white;
            background-color: #ff5733;
            padding: 8px 12px;
            border-radius: 8px;
            position: absolute;
            top: 10px;
            right: 20px;
        }
        .cart-link:hover {
            background-color: #e74c3c;
        }
    </style>
    <script src="script.js"></script>
    <script src="cart.js"></script>
</head>
<script>
function updateCartCount() {
    fetch('cart_count.php')
    .then(response => response.text())
    .then(count => {
        console.log("Cart count:", count); // Debug
        document.getElementById('cart-count').innerText = count;
    })
    .catch(error => console.error("Error fetching cart count:", error));
}
// Mettre à jour le compteur dès le chargement de la page
document.addEventListener('DOMContentLoaded', updateCartCount);
</script>
<body>
    <!-- Show Cart Only If User is Logged In -->
<?php if ($isLoggedIn): ?>
    <a href="cart.php" class="cart-link">🛒 Cart (<span id="cart-count"><?= $cart_count ?></span>)</a>
<?php endif; ?>
    <header>
        <div class="logo">FitLife</div>
        <nav>
            <a href="index.html">Home</a>
            <a href="shop.php">Shop</a>
            <a href="blog.html">Blog</a>
            
            <?php if ($isLoggedIn): ?>
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a href="login.php" id="login-btn">Login</a>
            <?php endif; ?>
        </nav>
        <button class="toggle-btn" onclick="toggleSidebar()" aria-controls="sidebar" aria-expanded="false">☰ Categories</button>
        <section class="sidebar" id="sidebar" aria-hidden="true">
            <div class="sidebar-content">
                <h3>Sports</h3>
                <ul>
                    <li><input type="checkbox" id="running" name="sport" value="Running" onchange="updateSelections()"> <label for="running">Running</label></li>
                    <li><input type="checkbox" id="gym" name="sport" value="Gym" onchange="updateSelections()"> <label for="gym">Gym</label></li>
                    <li><input type="checkbox" id="football" name="sport" value="Football" onchange="updateSelections()"> <label for="football">Football</label></li>
                    <li><input type="checkbox" id="biking" name="sport" value="Biking" onchange="updateSelections()"> <label for="biking">Biking</label></li>
                    <li><input type="checkbox" id="basketball" name="sport" value="Basketball" onchange="updateSelections()"> <label for="basketball">Basketball</label></li>
                </ul>
                <h3>Brands</h3>
                <ul>
                    <li><input type="checkbox" id="adidas" name="brand" value="Adidas" onchange="updateSelections()"> <label for="adidas">Adidas</label></li>
                    <li><input type="checkbox" id="nike" name="brand" value="Nike" onchange="updateSelections()"> <label for="nike">Nike</label></li>
                    <li><input type="checkbox" id="under-armour" name="brand" value="Under Armour" onchange="updateSelections()"> <label for="under-armour">Under Armour</label></li>
                    <li><input type="checkbox" id="scrapper" name="brand" value="Scrapper" onchange="updateSelections()"> <label for="scrapper">Scrapper</label></li>
                    <li><input type="checkbox" id="puma" name="brand" value="Puma" onchange="updateSelections()"> <label for="puma">Puma</label></li>
                </ul>
            </div>
        </section>
        
        <div class="sidebar-overlay"></div>
    </header>

<div id="loading-spinner" style="display: none; text-align: center;">
    <i class="fas fa-spinner fa-spin fa-3x"></i> <!-- Spinner Font Awesome -->
</div>

<section id="product1" class="section-p1">
    <div class="gradient-header">
        <h2>Discover Our Top Picks</h2>
        <p><span>Explore our</span> curated selection of premium products designed to elevate your lifestyle.</p>
    </div>
    <div class="pro-container">
        <?php
        // Vérifier si des produits existent
        if (mysqli_num_rows($result) > 0) {
            while ($product = mysqli_fetch_assoc($result)) {
                $name = htmlspecialchars($product['name']);
                $brand = htmlspecialchars($product['brand']);
                $sport = htmlspecialchars($product['sport']);
                $price = number_format($product['price'], 2, ',', ' '); // Format en MAD
                $image = htmlspecialchars($product['image']);
        ?>
                <div class="pro" 
                data-id="<?= $product['id'] ?>" 
    data-sport="<?= $sport ?>" 
    data-brand="<?= $brand ?>" 
    data-name="<?= $name ?>" 
    data-price="<?= $price ?>" 
    data-image="<?= $image ?>" 
    onclick="redirectToProduct(this)">
                    
                    <img src="<?= $image ?>" alt="<?= $name ?>">
                    <div class="des">
                        <span><?= $brand ?></span>
                        <h5><?= $name ?></h5>
                        <h4><?= $price ?> MAD</h4>
                    </div>
                    <button class="add-to-cart" data-id="<?= $product['id'] ?>" data-name="<?= $name ?>" data-price="<?= $product['price'] ?>">
    Add
</button>


                </div>
        <?php
            }
        } else {
            echo "<p>Aucun produit disponible.</p>";
        }
        ?>
    </div>
</section>

<section id="new-arrivals" class="section-p1">
    <div class="product-container">
        <?php
        
        if (mysqli_num_rows($result) > 0) {
            while ($product = mysqli_fetch_assoc($result)) {
                $name = htmlspecialchars($product['name']);
                $brand = htmlspecialchars($product['brand']);
                $sport = htmlspecialchars($product['sport']);
                $price = number_format($product['price'], 2, ',', ' '); // Format en MAD
                $image = htmlspecialchars($product['image']);
        ?>
                <div class="product" 
                data-id="<?= $product['id'] ?>" 
    data-sport="<?= $sport ?>" 
    data-brand="<?= $brand ?>" 
    data-name="<?= $name ?>" 
    data-price="<?= $price ?>" 
    data-image="<?= $image ?>" 
    onclick="redirectToProduct(this)">
                    
                    <img src="<?= $image ?>" alt="<?= $name ?>">
                    <div class="description">
                        <span><?= $brand ?></span>
                        <h5><?= $name ?></h5>
                        <h4><?= $price ?> MAD</h4>
                    </div>
                    <button class="add-to-cart" data-id="<?= $product['id'] ?>" data-name="<?= $name ?>" data-price="<?= $product['price'] ?>">
    Add
</button>

                </div>
        <?php
            }
        } else {
            echo "<p>Aucun nouveau produit disponible.</p>";
        }
        ?>
    </div>
</section>

<section id="pagination">
    <div class="pagination">
        <a href="#">&laquo;</a>
        <a href="#" class="active">1</a>
        <a href="#">2</a>
        <a href="#">3</a>
        <a href="#">&raquo;</a>
    </div>
</section>

<section id="newsletter">
    <div class="container">
        <div class="logo">
            <h2>FITLIFE</h2>
        </div>
        <div class="newsletter-content">
            <h3>Subscribe to our Newsletter</h3>
            <p>Get the latest news and <span>special offers</span></p>
        </div>
        <form>
            <input type="email" placeholder="Enter your email">
            <button>Subscribe</button>
        </form>
    </div>
    <div class="footer-links">
        <div class="social-media">
            <h3>Follow Us</h3>
            <div class="social-icons">
                <a href="#" class="social-icon"><i class="fab fa-instagram"></i></a>
                <a href="#" class="social-icon"><i class="fab fa-twitter"></i></a>
                <a href="#" class="social-icon"><i class="fab fa-facebook-f"></i></a>
            </div>
        </div>
        <div class="separator"></div>
        <div class="contact-info">
            <h3>Contact Us</h3>
            <p>Email: info-fitlife@gmail.com<br>Phone: +212 600 123 456</p>
        </div>
    </div>
</section>

<section>
    <div class="features">
        <div class="shipping-payment">
            <img src="male_and_female_hands_holding_credit_card.jpg" alt="Cash on Delivery">
            <h4>Cash on Delivery</h4>
            <p>Pay when you receive your order.</p>
        </div>
        <div class="payment-card">
            <img src="visa-logo.png" alt="Secure Payment">
            <h4>Secure Payment by Credit Card</h4>
            <p>Secure transactions</p>
        </div>
        <div class="fast-shipping">
            <img src="ctm.png" alt="Fast Shipping">
            <h4>Delivery Across Morocco</h4>
            <p>Fast and reliable delivery nationwide.</p>
        </div>
        <div class="original-items">
            <img src="freepik__upload__6106.jpg" alt="Original Products">
            <h4>Original Products</h4>
            <p>Guaranteed 100% authentic products.</p>
        </div>
    </div>
</section>


<footer>
    <p>&copy; 2025 FitLife. All Rights Reserved.</p>
</footer>

</body>
</html>