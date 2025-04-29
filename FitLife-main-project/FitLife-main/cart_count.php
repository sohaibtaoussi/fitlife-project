<?php
session_start();
$cart_count = isset($_SESSION['cart']) ? array_sum(array_map(fn($p) => $p['quantity'], $_SESSION['cart'])) : 0;
echo $cart_count;
?>
