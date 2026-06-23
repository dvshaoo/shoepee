<?php
//index.php

session_start();

include_once(__DIR__ . "/../connections/connection.php");
include_once(__DIR__ . "/../connections/head.php");
$con = connection();

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

/*

search: 1
filter: 1

search: 1
filter: 0

search: 0
filter: 1

search: 0
filter: 0

*/

$search = isset($_GET['querysearch']) ? $_GET['querysearch'] : '';
$filter = isset($_GET['queryfilter']) ? $_GET['queryfilter'] : '';

$baseSql = "SELECT * FROM tbl_products WHERE product_archive = 'FALSE' AND stock_quantity > 20";
$conditions = "";
$params = [];
$types = "";

if ($search && $filter) {
    $conditions = " AND brand LIKE ? AND model_name LIKE ?";
    $params[] = "%$filter%";
    $params[] = "%$search%";
    $types = "ss";
} elseif ($search) {
    $conditions = " AND model_name LIKE ?";
    $params[] = "%$search%";
    $types = "s";
} elseif ($filter) {
    $conditions = " AND brand LIKE ?";
    $params[] = "%$filter%";
    $types = "s";
}

$prodSql = $baseSql . $conditions . " ORDER BY RAND()";
$stmtProd = $con->prepare($prodSql);
if (!empty($params)) {
    $stmtProd->bind_param($types, ...$params);
}
$stmtProd->execute();
$products = $stmtProd->get_result()->fetch_all(MYSQLI_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php echo $headContent; ?>
    <title>SHOEPEE</title>
</head>

<body>
    <div class="overlay"></div>
    <nav>
        <div class="logo">
            <div class="logo-icon">
                <img src="../assets/images/shoepee_logo.png" alt="shoepee logo">
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
                            <a href="../home/index.php" class="nav-links" title="Shop">
                                <span class="material-symbols-outlined">storefront</span> Shop
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="../products/cart/bag.php" class="nav-links" title="Bag">
                                <span class="material-symbols-outlined">shopping_bag</span> Bag
                                <?php if ($totalItems != 0) { ?>
                                    <span class="badge">
                                        <?php echo $totalItems; ?>
                                    </span>
                                <?php } ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="../products/favorites/favorites.php" class="nav-links" title="Favorites">
                                <span class="material-symbols-outlined">favorite</span> Favorites
                                <?php if ($totalFavItems != 0) { ?>
                                    <span class="badge">
                                        <?php echo $totalFavItems; ?>
                                    </span>
                                <?php } ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="../products/cart/checkout.php" class="nav-links" title="Checkout">
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
                                    <img src="../assets/images/users/<?php echo $user['profile_img']; ?>" alt="">
                                <?php } else { ?>
                                    <span class="material-symbols-outlined">person</span>
                                <?php } ?>
                            </div>
                            <div class="account-name">
                                <a href="../user/user.account.php" target="_self">
                                    <?php echo $user['username']; ?>
                                </a>
                            </div>
                        </div>
                        <div class="account-action">
                            <a class="log-out" href="../auth/signout.php" target="_self">
                                <span class="material-symbols-outlined">logout</span>Log out
                            </a>
                        </div>
                    </div>
                </div>
            <?php } else {
                header("Location: ../auth/signin.php");
            } ?>
        <?php } else { ?>
            <div class="nav-menu-container" type="mobile">
                <ul class="nav-menu">
                    <li class="nav-item">
                        <a href="../home/index.php" class="nav-links" title="Shop">
                            <span class="material-symbols-outlined">storefront</span> Shop
                        </a>
                    </li>
                </ul>
                <div class="account-section">
                    <div class="account-action">
                        <a href="../auth/signin.php" target="_self">
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
                        <li class="nav-item nav-item-search">
                            <form action="" method="GET">
                                <!-- <label><span class="material-symbols-outlined">search</span>  -->
                                <label>Search
                                    <input type="search" name="querysearch" id="" class="search-input" pattern="[a-zA-Z0-9_\d.\s]*|[1-9][0-9]*" maxlength="30" minlength="" autocomplete="off" value="<?php echo $search; ?>">
                                </label>    
                            </form>
                        </li>
                        <li class="nav-item">
                            <a href="../home/index.php" class="nav-links" title="Shop">
                                <span class="material-symbols-outlined">storefront</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="../products/cart/bag.php" class="nav-links" title="Bag">
                                <span class="material-symbols-outlined">shopping_bag</span>
                                <?php if ($totalItems != 0) { ?>
                                    <span class="badge">
                                        <?php echo $totalItems; ?>
                                    </span>
                                <?php } ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="../products/favorites/favorites.php" class="nav-links" title="Favorites">
                                <span class="material-symbols-outlined">favorite</span>
                                <?php if ($totalFavItems != 0) { ?>
                                    <span class="badge">
                                        <?php echo $totalFavItems; ?>
                                    </span>
                                <?php } ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="../products/cart/checkout.php" class="nav-links" title="Checkout">
                                <span class="material-symbols-outlined">local_shipping</span>
                                <?php if ($totalCheckoutItem != 0) { ?>
                                    <span class="badge">
                                        <?php echo $totalCheckoutItem; ?>
                                    </span>
                                <?php } ?>
                            </a>
                        </li>
                        <li class="nav-item account" title="Account" tabindex="0">
                            <div class="account-icon">
                                <?php if (!empty($user['profile_img'])) { ?>
                                    <img src="../assets/images/users/<?php echo $user['profile_img']; ?>" alt="">
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
                        header("Location: ../auth/signin.php");
                    } ?>
                <?php } else { ?>
                    <li class="nav-item nav-item-search">
                        <form action="" method="GET">
                            <!-- <label><span class="material-symbols-outlined">search</span>  -->
                            <label>Search
                                <input type="search" name="querysearch" id="" class="search-input" pattern="[a-zA-Z0-9_\d.\s]*|[1-9][0-9]*" maxlength="30" minlength="" autocomplete="off" value="<?php echo $search; ?>">
                            </label>    
                        </form>
                    </li>
                    <li class="nav-item">
                        <a href="../home/index.php" class="nav-links" title="Shop">
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
                                <a href="../user/user.account.php" class="nav-links" target="_self">
                                    Profile
                                </a>
                                <a href="../auth/signout.php" class="nav-links" target="_self">
                                    Log out
                                </a>
                            </div>
                        </div>
                    <?php } ?>
                <?php } else { ?>
                    <div class="account-link-container" card-type="no-account">
                        <div class="account-link">
                            <a href="../auth/signin.php" class="nav-links" target="_self">
                                Sign In
                            </a>
                        </div>
                    </div>
                <?php } ?>
            </ul>
        </div>
    </nav>
    <div class="grid-wrapper" animate="fadeIn">
        <?php foreach ($products as $product): ?>
            <div class="prod-card">
                <div class="brand-icon">
                    <?php if ($product['brand'] === 'Nike') { ?>
                        <img class="brand-icon-img" src="../assets/images/src/brand_logos/Nike_logo.png" alt="<?php echo $product['brand']; ?> logo">
                    <?php } else if ($product['brand'] === 'Adidas') { ?>
                        <img class="brand-icon-img" src="../assets/images/src/brand_logos/Adidas_logo.png"
                                alt="<?php echo $product['brand']; ?> logo">
                    <?php } ?>
                </div>
                <img class="prod-img" src="../assets/uploads/<?php echo $product['img_url']; ?>"
                    alt="<?php echo $product['model_name']; ?>">
                <div class="prod-card-description">
                    <span class="prod-status">
                        <?php echo $product['prod_status']; ?>
                    </span>
                    <h4>
                        <?php echo $product['brand']; ?>
                        <?php echo $product['model_name']; ?>
                    </h4>
                    <p>
                        <?php echo "$" . $product['price']; ?>
                    </p>
                </div>
                <a href="../products/prod.view.php?prod_id=<?php echo $product['prod_id'];?>"></a>
            </div>
        <?php endforeach; ?>
    </div>
</body>
<script type="module" src="../assets/JS/script.js"></script>
<script type="module" src="../assets/JS/nav.js"></script>

</html>