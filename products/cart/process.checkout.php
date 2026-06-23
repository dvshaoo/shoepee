<?php
// process.checkout.php
session_start();

include_once(dirname(__FILE__) . "/../../connections/connection.php");
include_once(dirname(__FILE__) . "/../../connections/head.php");
$con = connection();

// Check if the user is logged in
if (!isset($_SESSION['UserLogin'])) {
    header("Location: /shoepee/auth/signin.php");
    exit();
}

// CHECKOUT 
if (isset($_POST['checkout'])) {
    $id = $_SESSION['id'];
    $quantities = $_POST['quantity'];

    foreach ($quantities as $prod_id => $quantity) {
        $addQuantityToCheckoutSQL = "INSERT INTO tbl_checkout_history (user_id, prod_id, quantity) VALUES (?, ?, ?)";
        $stmt = $con->prepare($addQuantityToCheckoutSQL);
        $stmt->bind_param("iii", $id, $prod_id, $quantity);
        $stmt->execute();
    }
    
    header("Location: /shoepee/products/cart/bag.php");
    exit();
}

$insertCheckoutSQL = "INSERT INTO tbl_checkout_history (user_id, prod_id, selected_size) SELECT user_id, prod_id, selected_size FROM tbl_bag WHERE user_id = ?";
$stmtInsertCheckout = $con->prepare($insertCheckoutSQL);
$stmtInsertCheckout->bind_param("i", $id);
$stmtInsertCheckout->execute();

?>