<?php
//prod.manage.php

session_start();

include_once(__DIR__ . "/../connections/connection.php");
include_once(__DIR__ . "/../connections/head.php");
$con = connection();

if (!isset($_SESSION['UserLogin'])) {
    header("Location: /shoepee/admin/auth.admin.php");
    exit();
}

$id = $_SESSION['id'] ?? NULL;

if ($id) {
    $userSql = "SELECT * FROM tbl_admins WHERE id = ?";
    $stmtUser = $con->prepare($userSql);
    $stmtUser->bind_param("i", $id);
    $stmtUser->execute();
    $user = $stmtUser->get_result()->fetch_assoc();

    // $prodSql = "SELECT * FROM tbl_products ORDER BY prod_id DESC";
    $prodSql = "SELECT * FROM tbl_products WHERE product_archive = 'TRUE' ORDER BY prod_id DESC";
    $result = $con->query($prodSql) or die($con->error);

    if ($result->num_rows > 0) {
        $products = $result->fetch_all(MYSQLI_ASSOC);
    } else {
        $products = [];
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php echo $headContent; ?>
    <title>SHOEPEE | MANAGE PRODUCTS</title>
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
            <span class="material-symbols-outlined">right_panel_open</span>
        </button>
        <?php if (isset($_SESSION['UserLogin'])) { ?>
            <?php if ($_SESSION['access'] === 'admin') { ?>
                <div class="nav-menu-container" type="mobile">
                    <ul class="nav-menu">
                        <li class="nav-item">
                            <a href="/shoepee/products/prod.manage.php" class="nav-links" title="All Products">
                                <span class="material-symbols-outlined">view_cozy</span> All Products
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/shoepee/products/prod.add.php" class="nav-links" title="Add Products">
                                <span class="material-symbols-outlined">library_add</span> Add Item
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/shoepee/products/prod.archive.list.php" class="nav-links" title="Archived Products">
                                <span class="material-symbols-outlined">archive</span> Archived Products
                            </a>
                        </li>
                    </ul>
                    <div class="account-section" title="Account">
                        <div class="user-account">
                            <div class="account-icon">
                                <span class="material-symbols-outlined">shield_person</span>
                            </div>
                            <div class="account-name">
                                <p>
                                    <?php echo $user['username']; ?>
                                </p>
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
                header("Location: /shoepee/admin/auth.admin.php");
            } ?>
        <?php } ?>
        <div class="nav-menu-container" type="desktop">
            <ul class="nav-menu">
                <?php if (isset($_SESSION['UserLogin'])) { ?>
                    <?php if ($_SESSION['access'] === 'admin') { ?>
                        <li class="nav-item">
                            <a href="/shoepee/products/prod.manage.php" class="nav-links" title="All Products">
                                <span class="material-symbols-outlined">view_cozy</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/shoepee/products/prod.add.php" class="nav-links" title="Add Item">
                                <span class="material-symbols-outlined">library_add</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/shoepee/products/prod.archive.list.php" class="nav-links" title="Archived Products">
                                <span class="material-symbols-outlined">archive</span>
                            </a>
                        </li>
                        <li class="nav-item account" title="Account">
                            <div class="account-icon">
                                <?php if (!empty($user['profile_img'])) { ?>
                                    <img src="/shoepee/assets/images/users/<?php echo $user['profile_img']; ?>" alt="">
                                <?php } else { ?>
                                    <span class="material-symbols-outlined">shield_person</span>
                                <?php } ?>
                            </div>
                            <div class="account-name">
                                <p>
                                    <?php echo $user['username']; ?>
                                </p>
                            </div>
                        </li>
                    <?php } else { ?>
                        <?php header("Location: /shoepee/admin/auth.admin.php"); ?>
                    <?php } ?>
                <?php } else { ?>
                    <li class="nav-item">
                        <span class="material-symbols-outlined no-account">shield_person</span>
                    </li>
                <?php } ?>
                <?php if (isset($_SESSION['UserLogin'])) { ?>
                    <?php if ($_SESSION['access'] === 'admin') { ?>
                        <div class="account-link-container" card-type="with-account">
                            <div class="account-link">
                                <a href="/shoepee/auth/signout.php" class="nav-links" target="_self">
                                    Log out
                                </a>
                            </div>
                        </div>
                    <?php } ?>
                <?php } else { ?>
                    <div class="account-link-container" card-type="no-account">
                        <div class="account-link">
                            <a href="/shoepee/admin/auth.admin.php" class="nav-links" target="_self">
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
            <div class="prod-card" <?php echo $product['product_archive'] === 'TRUE' ? 'style="opacity: 90%; animation: archived-pulse 1s linear infinite;"' : ''; ?><?php echo $product['stock_quantity'] < 10 ? 'style="animation: stock-warning-pulse 1s linear infinite;"' : ''; ?>>
                <div class="brand-icon">
                    <?php if ($product['brand'] === 'Nike') { ?>
                        <img class="brand-icon-img" src="/shoepee/assets/images/src/brand_logos/Nike_logo.png" alt="<?php echo $product['brand']; ?> logo">
                    <?php } else if ($product['brand'] === 'Adidas') { ?>
                            <img class="brand-icon-img" src="/shoepee/assets/images/src/brand_logos/Adidas_logo.png"
                                alt="<?php echo $product['brand']; ?> logo">
                    <?php } ?>
                </div>
                <img class="prod-img" src="/shoepee/assets/uploads/<?php echo $product['img_url']; ?>"
                    alt="<?php echo $product['model_name']; ?>">
                <div class="prod-card-description">
                    <h4>
                        <?php echo $product['brand']; ?>
                        <?php echo $product['model_name']; ?>
                    </h4>
                    <p>
                        <?php echo "$" . $product['price']; ?>
                    </p>
                </div>
                <a href="/shoepee/products/prod.edit.php?prod_id=<?php echo $product['prod_id'] ?>"></a>
            </div>
        <?php endforeach; ?>
    </div>
    <script type="module" src="/shoepee/assets/JS/script.js"></script>
    <script type="module" src="/shoepee/assets/JS/nav.js"></script>
</body>

</html>