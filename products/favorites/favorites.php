<?php
// Start the session
session_start();

// Check if the user is logged in
// include_once(__DIR__ . "/../connections/connection.php");
// include_once(__DIR__ . "/../connections/head.php");
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

// Retrieve items from the 
$favSql = "SELECT p.*, f.* FROM tbl_products p
            JOIN tbl_favorites f ON p.prod_id = f.prod_id
            WHERE f.user_id = ? ORDER BY f.fav_id DESC";
$stmtFav = $con->prepare($favSql);
$stmtFav->bind_param("i", $id);
$stmtFav->execute();
$resultFav = $stmtFav->get_result();

if (isset($_POST['remove_from_favorites'])) {
    $prod_id = $_POST['prod_id'];
    $id = $_SESSION['id'];

    $removeFromFavSQL = "DELETE FROM tbl_favorites WHERE user_id = ? AND prod_id = ?";
    $stmt = $con->prepare($removeFromFavSQL);
    $stmt->bind_param("ii", $id, $prod_id);
    $stmt->execute();

    header("Location: /shoepee/products/favorites/favorites.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php echo $headContent; ?>
    <title>SHOEPEE | FAVORITES</title>
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
                <?php while ($favItem = $resultFav->fetch_assoc()) { ?>
                    <div class="bag-item">
                        <div class="bag-item-info">
                            <div class="bag-item-image">
                                <img src="/shoepee/assets/uploads/<?php echo $favItem['img_url']; ?>"
                                    alt="<?php echo $favItem['brand'] . ' ' . $favItem['model_name']; ?>">
                            </div>
                            <div class="bag-item-details">
                                <a href="/shoepee/products/prod.view.php?prod_id=<?php echo $favItem['prod_id'] ?>"></a> 
                                <p class="bag-item-brandname">
                                    <?php echo $favItem['brand'] . ' ' . $favItem['model_name']; ?>
                                </p>
                                <p class="bag-item-price">
                                    <?php echo "$" . $favItem['price']; ?>
                                </p>
                            </div>
                        </div>
                        <form class="remove-item" action="" method="POST">
                            <input type="hidden" name="prod_id" value="<?php echo $favItem['prod_id']; ?>">
                            <button type="submit" name="remove_from_favorites">
                                <span class="material-symbols-outlined">heart_minus</span>
                            </button>
                        </form>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
    <script type="module" src="/shoepee/assets/JS/script.js"></script>
    <script type="module" src="/shoepee/assets/JS/nav.js"></script>
</body>

</html>