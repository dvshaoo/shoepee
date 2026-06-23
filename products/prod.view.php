<?php

session_start();

include_once(__DIR__ . "/../connections/connection.php");
include_once(__DIR__ . "/../connections/head.php");
$con = connection();

if (isset($_SESSION['UserLogin'])) {
    $id = $_SESSION['id'];
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

$success = "";

if (isset($_GET['prod_id'])) {
    $prod_id = $_GET['prod_id'];

    $prodSql = "SELECT * FROM tbl_products WHERE prod_id = ?";
    $stmt = $con->prepare($prodSql);
    $stmt->bind_param("i", $prod_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $products = $result->fetch_assoc();
    } else {
        $products = [];
    }

    $bagSql = "SELECT p.*, b.* FROM tbl_products p
            JOIN tbl_bag b ON p.prod_id = b.prod_id
            WHERE b.user_id = ? ORDER BY b.id DESC";
    $stmtBag = $con->prepare($bagSql);
    $stmtBag->bind_param("i", $id);
    $stmtBag->execute();
    $resultBag = $stmtBag->get_result();
    
    // Return to index if the product is ARCHIVE OR stock quantity is less than zero from changing the value of prod_id in URL
    if ($products['product_archive'] === 'TRUE' || $products['stock_quantity'] < 20) {
        header("Location: /shoepee/home/index.php");
    }
}

$status = $_SESSION['status'] ?? NULL;

if (isset($_POST["done"])) {
    unset($_SESSION['status']);
    $status = '';
}
    else if (isset($_POST['done-success']))
    {
        unset($_SESSION['status']);
        $status = '';
        header("Location: /shoepee/products/cart/bag.php");
        exit();
    }

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_to_bag') {
    $prod_id = $_GET['prod_id'];
    $id = $_SESSION['id'];
    $selected_size = $_POST['selected_size'];

    $addToBagSQL = "INSERT INTO tbl_bag (user_id, prod_id, selected_size) VALUES (?, ?, ?)";
    $stmt = $con->prepare($addToBagSQL);
    $stmt->bind_param("iii", $id, $prod_id, $selected_size);
    $stmt->execute();

    $success = $products['model_name'] . " Added";
    
    $_SESSION['status'] = $success;
    header("Location: /shoepee/products/prod.view.php?prod_id=$prod_id");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_to_favorites') {
    $prod_id = $_GET['prod_id'];
    $id = $_SESSION['id'];

    $addToFavSQL = "INSERT INTO tbl_favorites (user_id, prod_id) VALUES (?, ?)";
    $stmt = $con->prepare($addToFavSQL);
    $stmt->bind_param("ii", $id, $prod_id);
    $stmt->execute();
    
    header("Location: /shoepee/products/prod.view.php?prod_id=$prod_id");
    exit();

} else if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'remove_to_favorites') {
    $removeToFavSQL = "DELETE FROM tbl_favorites WHERE user_id = ? AND prod_id = ?";
    $stmt = $con->prepare($removeToFavSQL);
    $stmt->bind_param("ii", $id, $prod_id);
    $stmt->execute();

    header("Location: /shoepee/products/prod.view.php?prod_id=$prod_id");
    exit();
}

$checkFavSQL = "SELECT * FROM tbl_favorites WHERE user_id = ? AND prod_id = ?";
$checkFavstmt = $con->prepare($checkFavSQL);
$checkFavstmt->bind_param("ii", $id, $_GET['prod_id']);
$checkFavstmt->execute();
$favResult = $checkFavstmt->get_result();

$checkBagSQL = "SELECT * FROM tbl_bag WHERE user_id = ? AND prod_id = ?";
$checkBagstmt = $con->prepare($checkBagSQL);
$checkBagstmt->bind_param("ii", $id, $_GET['prod_id']);
$checkBagstmt->execute();
$bagResult = $checkBagstmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php echo $headContent; ?>
    <title>SHOEPEE | <?php echo $products['model_name']; ?></title>
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
                        <a href="/shoepee/home/index.php" class="nav-links" title="Shop">
                            <span class="material-symbols-outlined">storefront</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <span class="material-symbols-outlined no-account">no_accounts</span>
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
    <div class="flex-wrapper">
        <div class="pop-overlay" <?php echo !empty($status) ? 'style="display: block;"' : 'style="display: none;"' ; ?> animate="fadeIn"></div>
        <div class="bag-item-add-card" <?php echo !empty($status) ? 'style="display: flex;"' : 'style="display: none;"' ; ?> animate="fadeIn">
            <div class="recent-added-bag-item">
                <p><?php if (isset($_SESSION["status"])) {echo $status;} ?></p>
            </div>
            <div class="status-message">
                <?php 
                    $resultBag->data_seek(0);
                    while ($bagItem = $resultBag->fetch_assoc()) { ?>
                        <input type="hidden" name="" value="<?php echo $bagItem['prod_id']; ?>" step="1">
                        <div class="asd">
                            <p><?php echo $bagItem['model_name']; ?></p>
                            <p><?php echo $bagItem['selected_size']; ?></p>
                        </div>
                <?php } ?>
            </div>
            <form class="bag-item-add-card-action" action="" method="POST">
                <button class="" name="done" type="submit">Okay</button>
                <button class="" name="done-success" type="submit">Show Bag</button>
            </form>
        </div>
        <div class="product-content">
            <div class="product-container-left">
                <div class="product-img">
                    <img src="/shoepee/assets/uploads/<?php echo $products['img_url']; ?>" alt="">
                </div>
            </div>
            <div class="product-container-right">
                <div class="product-name">
                    <p>
                        <?php echo $products['brand']; ?>
                        <?php echo $products['model_name']; ?>
                    </p>
                </div>
                <div class="product-description">
                    <p>
                        <?php echo $products['description']; ?>
                    </p>
                </div>
                <?php if (isset($_SESSION['UserLogin'])) { ?>
                    <?php if ($_SESSION['access'] == 'user') { ?>
                        <div class="product-actions">
                            <div class="product-action-container1">
                                <div class="product-price">
                                    <p>
                                        <?php echo "$" . $products['price']; ?>
                                    </p>
                                </div>
                                <div class="product-stock-count">
                                    <p>
                                        <?php echo $products['stock_quantity'] < 50 ? 'Just few items left. Order now.' : ''; ?>
                                    </p>
                                </div>
                            </div>
                            <div class="product-action-container2">
                            <?php if ($bagResult->num_rows == 0) { ?>
                                <form class="add-bag" action="" method="POST">
                                    <div class="size-list">
                                        <?php
                                        $sizes = ['size_8', 'size_85', 'size_9', 'size_95', 'size_10', 'size_105', 'size_11', 'size_115'];
                                        foreach ($sizes as $size) {
                                            $sizeValue = $products[$size];
                                            $disabled = $sizeValue === '' ? 'disabled' : '';
                                            $sizeLabel = "";
                                            switch ($size) {
                                                case "size_8":
                                                    $sizeLabel = "8";
                                                    $selectedValue = "8";
                                                    break;
                                                case "size_85":
                                                    $sizeLabel = "8.5";
                                                    $selectedValue = "85";
                                                    break;
                                                case "size_9":
                                                    $sizeLabel = "9";
                                                    $selectedValue = "9";
                                                    break;
                                                case "size_95":
                                                    $sizeLabel = "9.5";
                                                    $selectedValue = "95";
                                                    break;
                                                case "size_10":
                                                    $sizeLabel = "10";
                                                    $selectedValue = "10";
                                                    break;
                                                case "size_105":
                                                    $sizeLabel = "10.5";
                                                    $selectedValue = "105";
                                                    break;
                                                case "size_11":
                                                    $sizeLabel = "11";
                                                    $selectedValue = "11";
                                                    break;
                                                case "size_115":
                                                    $sizeLabel = "11.5";
                                                    $selectedValue = "115";
                                                    break;
                                                default:
                                                    $sizeLabel = "";
                                                    $selectedValue = "";
                                            }
                                            ?>
                                            <div class="checkbox-wrapper">
                                                <label class="checkbox-wrapper">
                                                    <input class="checkbox-input" type="radio" value="<?php echo $selectedValue; ?>"
                                                        name="selected_size" <?php echo !empty($sizeValue) ? 'checked' : ''; ?><?php echo empty($sizeValue) ? 'disabled' : ''; ?>>
                                                    <span class="checkbox-tile" <?php echo !empty($sizeValue) ? '' : 'title="Size not available" style="border: 2px solid #b1b1b15e;"'; ?>>
                                                        <span class="checkbox-label" <?php echo !empty($sizeValue) ? '' : 'title="Size not available" style="text-decoration:line-through; color: #b1b1b15e;"'; ?>>US
                                                            <?php echo $sizeLabel; ?>
                                                        </span>
                                                    </span>
                                                </label>
                                            </div>
                                        <?php } ?>
                                    </div>
                                    <input type="hidden" name="action" value="add_to_bag">
                                    <input type="hidden" name="prod_id" value="<?php echo $products['prod_id']; ?>">
                                    <button class="btn" type="submit" title="Add item to bag">Add to bag</button>
                                </form>
                            <?php } else { ?>
                                <div class="size-list">
                                    <?php
                                    $sizes = ['size_8', 'size_85', 'size_9', 'size_95', 'size_10', 'size_105', 'size_11', 'size_115'];
                                    foreach ($sizes as $size) {
                                        $sizeValue = $products[$size];
                                        $sizeLabel = "";
                                        switch ($size) {
                                            case "size_8":
                                                $sizeLabel = "8";
                                                $selectedValue = "8";
                                                break;
                                            case "size_85":
                                                $sizeLabel = "8.5";
                                                $selectedValue = "85";
                                                break;
                                            case "size_9":
                                                $sizeLabel = "9";
                                                $selectedValue = "9";
                                                break;
                                            case "size_95":
                                                $sizeLabel = "9.5";
                                                $selectedValue = "95";
                                                break;
                                            case "size_10":
                                                $sizeLabel = "10";
                                                $selectedValue = "10";
                                                break;
                                            case "size_105":
                                                $sizeLabel = "10.5";
                                                $selectedValue = "105";
                                                break;
                                            case "size_11":
                                                $sizeLabel = "11";
                                                $selectedValue = "11";
                                                break;
                                            case "size_115":
                                                $sizeLabel = "11.5";
                                                $selectedValue = "115";
                                                break;
                                            default:
                                                $sizeLabel = "";
                                                $selectedValue = "";
                                        }
                                        ?>
                                        <div class="checkbox-wrapper">
                                            <label class="checkbox-wrapper">
                                                <input class="checkbox-input" type="radio" value="<?php echo $selectedValue; ?>"
                                                    name="selected_size" disabled>
                                                <span class="checkbox-tile" <?php echo !empty($sizeValue) ? '' : 'title="Size not available" style="border: 2px solid #b1b1b15e;"'; ?>>
                                                    <span class="checkbox-label" <?php echo !empty($sizeValue) ? '' : 'title="Size not available" style="text-decoration:line-through; color: #b1b1b15e;"'; ?>>US
                                                        <?php echo $sizeLabel; ?>
                                                    </span>
                                                </span>
                                            </label>
                                        </div>
                                    <?php } ?>
                                </div>
                                <div class="add-bag">
                                    <button class="btn dynamic-button" data-location="../products/cart/bag.php" title="Show item in bag">Show bag</button>
                                </div>
                            <?php } ?>
                            <?php if ($favResult->num_rows == 0) { ?>
                                <form class="add-fav" action="" method="POST">
                                    <input type="hidden" name="action" value="add_to_favorites">
                                    <input type="hidden" name="prod_id" value="<?php echo $products['prod_id']; ?>">
                                    <button class="btn" type="submit" title="Add item to Favorites">
                                        Add to Favorites <span class="material-symbols-outlined">heart_plus</span>
                                    </button>
                                </form>
                            <?php } else { ?>
                                <form class="add-fav" action="" method="POST">
                                    <input type="hidden" name="action" value="remove_to_favorites">
                                    <input type="hidden" name="prod_id" value="<?php echo $products['prod_id']; ?>">
                                    <button class="btn" type="submit" title="Remove item from Favorites">
                                        Favorite <span class="material-symbols-outlined" style="color: red;">heart_check</span>
                                    </button>
                                </form>
                            <?php } ?>
                            </div>
                        </div>
                    <?php } ?>
                <?php } else { ?>
                    <div class="product-actions">
                        <div class="product-action-container1">
                            <div class="product-price">
                                <p>
                                    <?php echo "$" . $products['price']; ?>
                                </p>
                            </div>
                            <div class="product-stock-count">
                                <p>
                                    <?php echo $products['stock_quantity'] < 50 ? 'Just few items left. Order now.' : ''; ?>
                                </p>
                            </div>
                        </div>
                        <div class="product-action-container2">
                        <?php if ($bagResult->num_rows == 0) { ?>
                            <div class="size-list">
                                <?php
                                $sizes = ['size_8', 'size_85', 'size_9', 'size_95', 'size_10', 'size_105', 'size_11', 'size_115'];
                                foreach ($sizes as $size) {
                                    $sizeValue = $products[$size];
                                    $sizeLabel = "";
                                    switch ($size) {
                                        case "size_8":
                                            $sizeLabel = "8";
                                            $selectedValue = "8";
                                            break;
                                        case "size_85":
                                            $sizeLabel = "8.5";
                                            $selectedValue = "85";
                                            break;
                                        case "size_9":
                                            $sizeLabel = "9";
                                            $selectedValue = "9";
                                            break;
                                        case "size_95":
                                            $sizeLabel = "9.5";
                                            $selectedValue = "95";
                                            break;
                                        case "size_10":
                                            $sizeLabel = "10";
                                            $selectedValue = "10";
                                            break;
                                        case "size_105":
                                            $sizeLabel = "10.5";
                                            $selectedValue = "105";
                                            break;
                                        case "size_11":
                                            $sizeLabel = "11";
                                            $selectedValue = "11";
                                            break;
                                        case "size_115":
                                            $sizeLabel = "11.5";
                                            $selectedValue = "115";
                                            break;
                                        default:
                                            $sizeLabel = "";
                                            $selectedValue = "";
                                    }
                                    ?>
                                    <div class="checkbox-wrapper">
                                        <label class="checkbox-wrapper">
                                            <input class="checkbox-input" type="radio" value="<?php echo $selectedValue; ?>"
                                                name="selected_size" disabled>
                                            <span class="checkbox-tile" <?php echo !empty($sizeValue) ? '' : 'title="Size not available" style="border: 2px solid #b1b1b15e;"'; ?>>
                                                <span class="checkbox-label" <?php echo !empty($sizeValue) ? '' : 'title="Size not available" style="text-decoration:line-through; color: #b1b1b15e;"'; ?>>US
                                                    <?php echo $sizeLabel; ?>
                                                </span>
                                            </span>
                                        </label>
                                    </div>
                                <?php } ?>
                            </div>
                        <?php } ?>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>
    <script type="module" src="/shoepee/assets/JS/script.js"></script>
    <script type="module" src="/shoepee/assets/JS/nav.js"></script>
    <script>
    
    $(document).ready(function () {
        $("button").click(function () {
            var search = $('#search').val();
            $.ajax({
                url: 'fetch.php',
                type: 'post',
                data: { search: search },
                beforeSend: function () {
                    $(".loader").show();
                },
                success: function(response) {
                    $('.response').empty();
                    $('.response').append(response);
                },
                complete: function (data) {
                    $(".loader").hide();
                }
            });
        });
    });
    
    </script>
</body>

</html>