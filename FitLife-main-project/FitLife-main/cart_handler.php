<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST["id"];
    $name = $_POST["name"];
    $price = $_POST["price"];

    if (!isset($_SESSION["cart"])) {
        $_SESSION["cart"] = [];
    }

    // Check if product is already in the cart
    if (isset($_SESSION["cart"][$id])) {
        $_SESSION["cart"][$id]["quantity"] += 1;
    } else {
        $_SESSION["cart"][$id] = [
            "name" => $name,
            "price" => $price,
            "quantity" => 1
        ];
    }

    echo "Product added to cart!";
}
?>
