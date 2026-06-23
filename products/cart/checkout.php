<?php
session_start();

include_once(dirname(__FILE__) . "/../../connections/connection.php");
include_once(dirname(__FILE__) . "/../../connections/head.php");
$con = connection();

if (!isset($_SESSION['UserLogin'])) {
    header("Location: /shoepee/auth/signin.php");
    exit();
}

$id = $_SESSION['id'] ?? NULL;

if ($id) {
    $userSql = "SELECT * FROM tbl_users WHERE id = ?";
    $stmtUser = $con->prepare($userSql);
    $stmtUser->bind_param("i", $id);
    $stmtUser->execute();
    $user = $stmtUser->get_result()->fetch_assoc();

    $countItems = function ($table, $historyArchiveValue = null) use ($con, $id) {
        $condition = ($historyArchiveValue !== null) ? "  AND history_archive = ?" : "";
        $stmt = $con->prepare("SELECT COUNT(*) AS total_items FROM $table WHERE user_id = ?$condition");
        if ($historyArchiveValue !== null)
        {
            $stmt->bind_param("is", $id, $historyArchiveValue);
        }
            else
            {
                $stmt->bind_param("i", $id);
            }

        $stmt->execute();
        return $stmt->get_result()->fetch_assoc()['total_items'];
    };

    $totalItems = $countItems('tbl_bag');
    $totalFavItems = $countItems('tbl_favorites');
    $totalCheckoutItem = $countItems('tbl_checkout_history', 'FALSE');
}

$checkoutSQL = "SELECT ch.*, p.* FROM tbl_checkout_history ch
                JOIN tbl_products p ON ch.prod_id = p.prod_id
                WHERE ch.user_id = ? AND ch.history_archive = 'FALSE'";
$stmtCheckout = $con->prepare($checkoutSQL);
$stmtCheckout->bind_param("i", $id);
$stmtCheckout->execute();
$resultCheckout = $stmtCheckout->get_result();

if (isset($_POST['place_order'])) {
    $id = $_SESSION['id'];
    $totalShippingFee = $_POST['shipping_fee'];
    $totalAmount = $_POST['total_amount'];

    $insertOrderSQL = "INSERT INTO tbl_order_history (user_id, order_date, total_amount, shipping_fee, history_archive) VALUES (?, NOW(), ?, ?, 'FALSE')";
    $stmtInsertOrder = $con->prepare($insertOrderSQL);
    $stmtInsertOrder->bind_param("idd", $id, $totalAmount, $totalShippingFee);
    $stmtInsertOrder->execute();
    $orderID = $stmtInsertOrder->insert_id;

    $bagSql = "SELECT p.*, b.* FROM tbl_products p
            JOIN tbl_bag b ON p.prod_id = b.prod_id
            WHERE b.user_id = ? ORDER BY b.id DESC";
    $stmtBag = $con->prepare($bagSql);
    $stmtBag->bind_param("i", $id);
    $stmtBag->execute();
    $resultBag = $stmtBag->get_result();

    while ($checkoutItem = $resultCheckout->fetch_assoc()) {

        $totalPrice = $checkoutItem['quantity'] * $checkoutItem['price'];

        $insertOrderItemsSQL = "INSERT INTO tbl_order_items (order_id, prod_id, model_name, quantity, total_amount) VALUES (?, ?, ?, ?, ?)";
        $stmtInsertOrderItems = $con->prepare($insertOrderItemsSQL);
        $stmtInsertOrderItems->bind_param("iisid", $orderID, $checkoutItem['prod_id'], $checkoutItem['model_name'], $checkoutItem['quantity'], $totalPrice);
        $stmtInsertOrderItems->execute();

        $deductStockSQL = "UPDATE tbl_products SET stock_quantity = stock_quantity - ? WHERE prod_id = ?";
        $stmtDeductStock = $con->prepare($deductStockSQL);
        $stmtDeductStock->bind_param("ii", $checkoutItem['quantity'], $checkoutItem['prod_id']);
        $stmtDeductStock->execute();
    }

    $cohistory_archive = 'TRUE';

    $archiveCheckoutSQL = "UPDATE tbl_checkout_history SET history_archive = ? WHERE user_id = ?";
    $stmtArchiveCheckout = $con->prepare($archiveCheckoutSQL);
    $stmtArchiveCheckout->bind_param("si", $cohistory_archive, $id);
    $stmtArchiveCheckout->execute();

    header("Location: /shoepee/products/cart/checkout.php");
    exit();
}

if (isset($_POST['cancel_checkout'])) {
    $checkout_id = $_POST['checkout_id'];
    $id = $_SESSION['id'];

    $cancelCheckout = "DELETE FROM tbl_checkout_history WHERE user_id = ? AND checkout_id = ?";
    $stmt = $con->prepare($cancelCheckout);
    $stmt->bind_param("ii", $id, $checkout_id);
    $stmt->execute();

    header("Location: /shoepee/products/cart/checkout.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php echo $headContent; ?>
    <title>SHOEPEE | CHECKOUT</title>
</head>

<body>
    <div class="overlay"></div>
    <nav>
        <div class="logo">
            <div class="logo-icon">
                <img src="/shoepee/assets/images/shoepee_logo.png" alt="">
            </div>
            <div class="logo-text">
                <p>SHOEPEE</p>
            </div>
        </div>
        <button class="toggle-nav-menu">
            <span class="material-symbols-outlined">menu_open</span>
        </button>
        <?php if (isset($_SESSION['UserLogin'])) { ?>
            <?php if ($_SESSION['access'] === 'user') { ?>
                <div class="nav-menu-container" type="mobile">
                    <ul class="nav-menu">
                        <li class="nav-item">
                            <a href="/shoepee/home/index.php" class="nav-links" title="Shop">
                                <span class="material-symbols-outlined">storefront</span> Shop
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/shoepee/products/cart/bag.php" class="nav-links" title="Bag">
                                <span class="material-symbols-outlined">shopping_bag</span> Bag
                                <?php if ($totalItems != 0) { ?>
                                    <span class="badge">
                                        <?php echo $totalItems; ?>
                                    </span>
                                <?php } ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/shoepee/products/favorites/favorites.php" class="nav-links" title="Favorites">
                                <span class="material-symbols-outlined">favorite</span> Favorites
                                <?php if ($totalFavItems != 0) { ?>
                                    <span class="badge">
                                        <?php echo $totalFavItems; ?>
                                    </span>
                                <?php } ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/shoepee/products/cart/checkout.php" class="nav-links" title="Checkout">
                                <span class="material-symbols-outlined">local_shipping</span> Checkout
                                <?php if ($totalCheckoutItem != 0) { ?>
                                    <span class="badge">
                                        <?php echo $totalCheckoutItem; ?>
                                    </span>
                                <?php } ?>
                            </a>
                        </li>
                    </ul>
                    <div class="account-section">
                        <div class="user-account" title="Account">
                            <div class="account-icon">
                                <?php if (!empty($user['profile_img'])) { ?>
                                    <img src="/shoepee/assets/images/users/<?php echo $user['profile_img']; ?>" alt="">
                                <?php } else { ?>
                                    <span class="material-symbols-outlined">person</span>
                                <?php } ?>
                            </div>
                            <div class="account-name">
                                <a href="/shoepee/user/user.account.php" target="_self">
                                    <?php echo $user['username']; ?>
                                </a>
                            </div>
                        </div>
                        <div class="account-action">
                            <a class="log-out" href="/shoepee/auth/signout.php" target="_self">
                                <span class="material-symbols-outlined">logout</span>Log out
                            </a>
                        </div>
                    </div>
                </div>
            <?php } else {
                header("Location: /shoepee/auth/signin.php");
            } ?>
        <?php } else { ?>
            <div class="nav-menu-container" type="mobile">
                <ul class="nav-menu">
                    <li class="nav-item">
                        <a href="/shoepee/home/index.php" class="nav-links" title="Shop">
                            <span class="material-symbols-outlined">storefront</span> Shop
                        </a>
                    </li>
                </ul>
                <div class="account-section">
                    <div class="account-action">
                        <a href="/shoepee/auth/signin.php" target="_self">
                            <span class="material-symbols-outlined">login</span>
                            Sign in
                        </a>
                    </div>
                </div>
            </div>
        <?php } ?>
        <div class="nav-menu-container" type="desktop">
            <ul class="nav-menu">
                <?php if (isset($_SESSION['UserLogin'])) { ?>
                    <?php if ($_SESSION['access'] == 'user') { ?>
                        <li class="nav-item">
                            <a href="/shoepee/home/index.php" class="nav-links" title="Shop">
                                <span class="material-symbols-outlined">storefront</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/shoepee/products/cart/bag.php" class="nav-links" title="Bag">
                                <span class="material-symbols-outlined">shopping_bag</span>
                                <?php if ($totalItems != 0) { ?>
                                    <span class="badge">
                                        <?php echo $totalItems; ?>
                                    </span>
                                <?php } ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/shoepee/products/favorites/favorites.php" class="nav-links" title="Favorites">
                                <span class="material-symbols-outlined">favorite</span>
                                <?php if ($totalFavItems != 0) { ?>
                                    <span class="badge">
                                        <?php echo $totalFavItems; ?>
                                    </span>
                                <?php } ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/shoepee/products/cart/checkout.php" class="nav-links" title="Checkout">
                                <span class="material-symbols-outlined">local_shipping</span>
                                <?php if ($totalCheckoutItem != 0) { ?>
                                    <span class="badge">
                                        <?php echo $totalCheckoutItem; ?>
                                    </span>
                                <?php } ?>
                            </a>
                        </li>
                        <li class="nav-item account" title="Account">
                            <div class="account-icon">
                                <?php if (!empty($user['profile_img'])) { ?>
                                    <img src="/shoepee/assets/images/users/<?php echo $user['profile_img']; ?>" alt="">
                                <?php } else { ?>
                                    <span class="material-symbols-outlined">person</span>
                                <?php } ?>
                            </div>
                            <div class="account-name">
                                <p>
                                    <?php echo $user['username']; ?>
                                </p>
                            </div>
                        </li>
                    <?php } else {
                        header("Location: /shoepee/auth/signin.php");
                    } ?>
                <?php } else { ?>
                    <li class="nav-item">
                        <span class="material-symbols-outlined no-account">person</span>
                    </li>
                <?php } ?>
                <?php if (isset($_SESSION['UserLogin'])) { ?>
                    <?php if ($_SESSION['access'] == 'user') { ?>
                        <div class="account-link-container" card-type="with-account">
                            <div class="account-link">
                                <a href="/shoepee/user/user.account.php" class="nav-links" target="_self">
                                    Profile
                                </a>
                                <a href="/shoepee/auth/signout.php" class="nav-links" target="_self">
                                    Log out
                                </a>
                            </div>
                        </div>
                    <?php } ?>
                <?php } else { ?>
                    <div class="account-link-container" card-type="no-account">
                        <div class="account-link">
                            <a href="/shoepee/auth/signin.php" class="nav-links" target="_self">
                                Sign In
                            </a>
                        </div>
                    </div>
                <?php } ?>
            </ul>
        </div>
    </nav>

    <div class="content-wrapper">
        <?php if ($totalCheckoutItem != 0) { ?>
            <div class="checkout-container">
                <div class="checkout-list">
                    <?php while ($checkoutItem = $resultCheckout->fetch_assoc()) { ?>
                        <div class="checkout-item">
                            <div class="checkout-item-info">
                                <a href="/shoepee/products/prod.view.php?prod_id=<?php echo $checkoutItem['prod_id'] ?>"></a>
                                <div class="checkout-item-image">
                                    <img src="/shoepee/assets/uploads/<?php echo $checkoutItem['img_url']; ?>"
                                        alt="<?php echo $checkoutItem['brand'] . ' ' . $checkoutItem['model_name']; ?>">
                                </div>
                                <div class="checkout-item-details">
                                    <p class="checkout-item-brandname">
                                        <?php echo $checkoutItem['brand'] . " " . $checkoutItem['model_name']; ?>
                                    </p>
                                    <p class="checkout-item-size">
                                        <?php $selectedValue = $checkoutItem['selected_size'];
                                        switch ($selectedValue) {
                                            case "8":
                                                $selected_size = "US 8";
                                                break;
                                            case "85":
                                                $selected_size = "US 8.5";
                                                break;
                                            case "9":
                                                $selected_size = "US 9";
                                                break;
                                            case "95":
                                                $selected_size = "US 9.5";
                                                break;
                                            case "10":
                                                $selected_size = "US 10";
                                                break;
                                            case "105":
                                                $selected_size = "US 10.5";
                                                break;
                                            case "11":
                                                $selected_size = "US 11";
                                                break;
                                            case "115":
                                                $selected_size = "US 11.5";
                                                break;
                                        }
                                        echo $selected_size; ?>
                                    </p>
                                    <div class="checkout-total-amount">
                                        <p class="checkout-item-price">
                                            <?php echo "$" . $checkoutItem['price']; ?>
                                        </p>
                                        <p class="checkout-quantity">
                                            <?php echo "x" . $checkoutItem['quantity']; ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="bag-item-actions">
                                <form class="remove-item" action="" method="POST">
                                    <input type="hidden" name="checkout_id" value="<?php echo $checkoutItem['checkout_id']; ?>">
                                    <button type="submit" name="cancel_checkout" title="cancel order">
                                        <span class="material-symbols-outlined">cancel</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php } ?>
                </div>
                <div class="checkout-summary">
                    <div class="checkout-summary-container">
                        <div class="checkout-summary-header">
                            <h1>Checkout Summary</h1>
                        </div>
                        <div class="checkout-summary-content">
                            <div class="checkout-price-list">
                                <?php
                                $resultCheckout->data_seek(0);

                                $totalAmountPerItem = 0;

                                $shippingFee = 0;
                                $totalAmount = 0;
                                $subtotalAmount = 0;
                                $totalShippingFee = 0;
                                $shippingFeePerItem = 0;

                                while ($checkoutItem = $resultCheckout->fetch_assoc()) {
                                    $qty = $checkoutItem['quantity'];
                                    $itemPrice = $checkoutItem['price'];
                                    $totalAmountPerItem = $qty * $itemPrice;
                                    if ($qty === 1) {
                                        $shippingFee = 5;
                                    } elseif ($qty > 1) {
                                        // add 40% only every item if qty is greater than 1
                                        $shippingFee = 5 + ($qty - 1) * 2;
                                    }
                                    $totalShippingFee += $shippingFee;
                                    $subtotalAmount += $totalAmountPerItem;
                                    $totalAmount = $subtotalAmount + $totalShippingFee;
                                    ?>
                                    <div class="checkout-item-summary">
                                        <div class="bag-modelname-summary">
                                            <p>
                                                <?php echo $checkoutItem['model_name']; ?>
                                            </p>
                                        </div>
                                        <div class="item-price-summary">
                                            <p id="">
                                                <?php echo "$" . $totalAmountPerItem; ?>
                                            </p>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                            <div class="bag-summary-total-price">
                                <p>Order Total (
                                    <?php echo $totalCheckoutItem; ?> Items)
                                </p>
                                <p>
                                    <span id="totalPrice">
                                        <?php echo "$" . $subtotalAmount; ?>
                                    </span>
                                </p>
                            </div>
                            <div class="bag-summary-total-price">
                                <p>Total Shipping Fee</p>
                                <p>
                                    <span>
                                        <?php echo "$" . $totalShippingFee; ?>
                                    </span>
                                </p>
                            </div>
                            <div class="bag-summary-total-price">
                                <p>Total Payment</p>
                                <p>
                                    <span id="totalPrice">
                                        <?php echo "$" . $totalAmount; ?>
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>
                    <form action="" method="POST" id="placeOrderForm">
                        <?php
                        $resultCheckout->data_seek(0);
                        while ($checkoutItem = $resultCheckout->fetch_assoc()) {
                            ?>
                            <input type="hidden" name="prod_id" value="<?php echo $checkoutItem['prod_id']; ?>">
                            <input type="hidden" name="model_name" value="<?php echo $checkoutItem['model_name']; ?>">
                            <input type="hidden" name="quantity" value="<?php echo $checkoutItem['quantity']; ?>">
                            <input type="hidden" name="total_price"
                                value="<?php echo $checkoutItem['quantity'] * $checkoutItem['price']; ?>">

                        <?php } ?>
                        <input type="hidden" name="shipping_fee" value="<?php echo $totalShippingFee; ?>">
                        <input type="hidden" name="total_amount" value="<?php echo $totalAmount; ?>">
                        <button class="btn" name="place_order">Place Order</button>
                    </form>
                </div>
            </div>
        <?php } elseif ($totalCheckoutItem === 0) { ?>

        <?php } ?>
    </div>
    <script type="module" src="/shoepee/assets/JS/script.js"></script>
    <script type="module" src="/shoepee/assets/JS/nav.js"></script>
</body>
</html>