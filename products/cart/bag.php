<?php
// Start the session
session_start();

include_once(dirname(__FILE__) . "/../../connections/connection.php");
include_once(dirname(__FILE__) . "/../../connections/head.php");
$con = connection();

// Check if the user is logged in
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

// Retrieve items from the bag
$bagSql = "SELECT p.*, b.* FROM tbl_products p
            JOIN tbl_bag b ON p.prod_id = b.prod_id
            WHERE b.user_id = ? ORDER BY b.id DESC";
$stmtBag = $con->prepare($bagSql);
$stmtBag->bind_param("i", $id);
$stmtBag->execute();
$resultBag = $stmtBag->get_result();

if (isset($_POST['remove_from_bag'])) {
    $prod_id = $_POST['prod_id'];
    $id = $_SESSION['id'];

    $removeToBagSQL = "DELETE FROM tbl_bag WHERE user_id = ? AND prod_id = ?";
    $stmt = $con->prepare($removeToBagSQL);
    $stmt->bind_param("ii", $id, $prod_id);
    $stmt->execute();
    header("Location: /shoepee/products/cart/bag.php");
    exit();
}

// CHECKOUT 
if (isset($_POST['checkout'])) {
    $id = $_SESSION['id'];

    $quantities = $_POST['quantity'];
    $prod_id = $_POST['prod_id'] ?? NULL;
    $history_archive = "FALSE";

    foreach ($quantities as $prod_id => $quantity) {
        $insertCheckoutSQL = "INSERT INTO tbl_checkout_history (user_id, prod_id, selected_size, quantity, history_archive, checkout_datetime)
                            SELECT ?, prod_id, selected_size, ?, ?, CURRENT_TIMESTAMP
                            FROM tbl_bag 
                            WHERE user_id = ? AND prod_id = ?";
        
        $stmtinsertCheckout = $con->prepare($insertCheckoutSQL);
        $stmtinsertCheckout->bind_param("iisii", $id, $quantity, $history_archive, $id, $prod_id);
        $stmtinsertCheckout->execute();
    }

    // Clear the user's bag after checkout
    $clearBagSQL = "DELETE FROM tbl_bag WHERE user_id = ?";
    $stmtClearBag = $con->prepare($clearBagSQL);
    $stmtClearBag->bind_param("i", $id);
    $stmtClearBag->execute();

    header("Location: /shoepee/products/cart/checkout.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php echo $headContent; ?>
    <title>SHOEPEE | BAG</title>
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
        <div class="bag-container">
            <div class="bag-content">
                <?php while ($bagItem = $resultBag->fetch_assoc()) { ?>
                    <div class="bag-item">
                        <div class="bag-item-info">
                            <a href="/shoepee/products/prod.view.php?prod_id=<?php echo $bagItem['prod_id'] ?>"></a>
                            <div class="bag-item-image">
                                <img src="/shoepee/assets/uploads/<?php echo $bagItem['img_url']; ?>"
                                    alt="<?php echo $bagItem['brand'] . ' ' . $bagItem['model_name']; ?>">
                            </div>
                            <div class="bag-item-details">
                                <p class="bag-item-brandname">
                                    <?php echo $bagItem['brand'] . ' ' . $bagItem['model_name']; ?>
                                </p>
                                <p class="bag-item-size">US
                                    <?php $selectedValue = $bagItem['selected_size'];
                                    switch ($selectedValue) {
                                        case "8":
                                            $selected_size = "8";
                                            break;
                                        case "85":
                                            $selected_size = "8.5";
                                            break;
                                        case "9":
                                            $selected_size = "9";
                                            break;
                                        case "95":
                                            $selected_size = "9.5";
                                            break;
                                        case "10":
                                            $selected_size = "10";
                                            break;
                                        case "105":
                                            $selected_size = "10.5";
                                            break;
                                        case "11":
                                            $selected_size = "11";
                                            break;
                                        case "115":
                                            $selected_size = "11.5";
                                            break;
                                    }
                                    echo $selected_size; ?>
                                </p>
                                <p class="bag-item-price">
                                    <?php echo "$" . $bagItem['price']; ?>
                                </p>
                            </div>
                        </div>
                        <div class="bag-item-actions">
                            <div class="quantity">
                                <div class="custom-number-input">
                                    <div class="input-group" title="Quantity">
                                        <button class="quantity-action" onclick="decrement(<?php echo $bagItem['prod_id']; ?>)"><span class="material-symbols-outlined">remove</span></button>
                                        <input type="text" disabled id="quantity_<?php echo $bagItem['prod_id']; ?>" class="quantity-input" name="quantity[<?php echo $bagItem['prod_id']; ?>]" value="1" step="1" oninput="updateTotal(<?php echo $bagItem['prod_id']; ?>)">
                                        <button class="quantity-action" onclick="increment(<?php echo $bagItem['prod_id']; ?>)"><span class="material-symbols-outlined">add</span></button>
                                    </div>
                                </div>
                            </div>
                            <form class="remove-item" action="" method="POST">
                                <input type="hidden" name="prod_id" value="<?php echo $bagItem['prod_id']; ?>">
                                <button type="submit" name="remove_from_bag" title="remove item">
                                    <span class="material-symbols-outlined">delete</span>
                                </button>
                            </form>
                        </div>
                    </div>
                <?php } ?>
            </div>
            <div class="bag-summary">
                <div class="bag-summary-container">
                    <div class="bag-summary-header">
                        <h1>Summary</h1>
                    </div>
                    <div class="bag-summary-content">
                        <div class="bag-summary-price-list">
                            <?php
                            $resultBag->data_seek(0);
                            $totalPrice = 0;

                            while ($bagItem = $resultBag->fetch_assoc()) {
                                $totalPrice += $bagItem['price'];
                                ?>
                                <div class="bag-item-summary">
                                    <div class="bag-modelname-summary">
                                        <p>
                                            <?php echo $bagItem['model_name']; ?>
                                        </p>
                                    </div>
                                    <div class="bag-item-price-summary" data-prod-id="<?php echo $bagItem['prod_id']; ?>">
                                        <input type="hidden" name="" id="productPrice_<?php echo $bagItem['prod_id']; ?>"
                                            value="<?php echo $bagItem['price']; ?>">
                                        <p id="totalPrice_<?php echo $bagItem['prod_id']; ?>">
                                            <?php echo "$" . $bagItem['price']; ?>
                                        </p>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                        <div class="bag-summary-total-price">
                            <p>Sub Total</p>
                            <p>
                                <span id="totalPrice">
                                    <?php echo "$" . $totalPrice; ?>
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
                <form action="" method="POST" id="checkoutForm">
                    <input type="hidden" id="totalPrice1" value="">
                    <?php $resultBag->data_seek(0);
                        while ($bagItem = $resultBag->fetch_assoc()) { ?>
                            <input type="hidden" id="quantity2_<?php echo $bagItem['prod_id']; ?>" name="quantity[<?php echo $bagItem['prod_id']; ?>]" value="1" step="1">
                    <?php } ?>
                    <button class="btn" name="checkout">
                        Checkout (<?php echo $totalItems; ?>)
                    </button>
                </form>
            </div>
        </div>
    </div>
    <script type="module" src="/shoepee/assets/JS/script.js"></script>
    <script type="module" src="/shoepee/assets/JS/nav.js"></script>
    <script>
        document.getElementById('sourceTextArea').value = document.getElementById('hiddenInput').value;
        function updateValue() {
            var textAreaValue = document.getElementById('sourceTextArea').value;

            document.getElementById('hiddenInput').value = textAreaValue;
        }

        function increment(prodId) {
            var input = document.getElementById('quantity_' + prodId);
            var input2 = document.getElementById('quantity2_' + prodId);
            var max = 10;
            currentValue = parseInt(input.value, 10);
            currentValue2 = parseInt(input2.value, 10);
            
            newValue = currentValue + 1;
            newValue2 = currentValue2 + 1;

            if (newValue <= max) {
                input.value = newValue;
                // localStorage.setItem(input.value, newValue);
                updateTotal(prodId);
            }

            if (newValue2 <= max) {
                input2.value = newValue2;
                // localStorage.setItem(input.value, newValue);
                updateTotal(prodId);
            }
        }

        function decrement(prodId) {
            var input = document.getElementById('quantity_' + prodId);
            var input2 = document.getElementById('quantity2_' + prodId);
            
            if (parseInt(input.value, 10) > 1) {
                input.value = parseInt(input.value, 10) - 1;
                updateTotal(prodId);
            }
            
            if (parseInt(input2.value, 10) > 1) {
                input2.value = parseInt(input2.value, 10) - 1;
                updateTotal(prodId);
            }
        }

        function updateTotal(prodId) {
            var quantityInput = document.getElementById('quantity_' + prodId);
            var quantityInput2 = document.getElementById('quantity2_' + prodId);
            var productPriceInput = document.getElementById('productPrice_' + prodId);
            var totalPriceElement = document.getElementById('totalPrice_' + prodId);

            if (prodId && quantityInput && quantityInput2 && productPriceInput && totalPriceElement) {
                var quantity = parseInt(quantityInput.value, 10);
                var quantity2 = parseInt(quantityInput2.value, 10);
                var productPrice = parseFloat(productPriceInput.value);

                var newTotalPrice = quantity * productPrice;
                var newTotalPrice2 = quantity2 * productPrice;

                totalPriceElement.textContent = "$" + newTotalPrice.toFixed(2);
                // totalPriceElement.textContent = "$" + newTotalPrice2.toFixed(2);

                updateOverAllTotal();
            }
        }

        function updateOverAllTotal() {
            var bagItems = document.getElementsByClassName('bag-item-price-summary');
            var total = 0;
            var shippingFee = 0;

            for (var i = 0; i < bagItems.length; i++) {
                var prodId = bagItems[i].getAttribute('data-prod-id');
                var totalPriceElement = document.getElementById('totalPrice_' + prodId);
                var totalPriceElement2 = document.getElementById('totalPrice2_' + prodId);

                if (totalPriceElement) {
                    total += parseFloat(totalPriceElement.textContent.replace('$', ''));
                }

                if (totalPriceElement2) {
                    total += parseFloat(totalPriceElement2.textContent.replace('$', ''));
                }
            }

            var totalElement = document.getElementById('totalPrice');
            var totalElement1 = document.getElementById('totalPrice1');
            if (totalElement) {
                totalElement.textContent = "$" + total.toFixed(2);
                totalElement1.value = total.toFixed(2);
            }
        }

        function submitForm() {
            <?php foreach ($resultBag as $bagItem) { ?>
                var quantityInput = document.getElementById('quantity_<?php echo $bagItem['prod_id']; ?>')
                var updatedValue = parseInt(quantityInput.value, 10);

                updatedValue = Math.max(1, Math.min(10, updatedValue));

                quantityInput.value = updatedValue.value;
            <?php } ?>

            document.getElementById('checkoutForm').submit();
        }
    </script>
</body>

</html>