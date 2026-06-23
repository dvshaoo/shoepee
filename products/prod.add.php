<?php

session_start();

include_once(__DIR__ . "/../connections/connection.php");
include_once(__DIR__ . "/../connections/head.php");
$con = connection();

if (isset($_SESSION['UserLogin'])) {
    $id = $_SESSION['id'];
} elseif (!isset($_SESSION['UserLogin'])) {
    header("Location: /shoepee/admin/auth.admin.php");
}

$userSql = "SELECT * FROM tbl_admins WHERE id = ?";
$stmt = $con->prepare($userSql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

$success = "";

if (isset($_POST['add_prod'])) {
    $id = $_POST['prod_id'];
    $brand = $_POST['brand'];
    $model = $_POST['model_name'];
    $size8 = isset($_POST['size_8']) ? 1 : 0;
    $size85 = isset($_POST['size_85']) ? 1 : 0;
    $size9 = isset($_POST['size_9']) ? 1 : 0;
    $size95 = isset($_POST['size_95']) ? 1 : 0;
    $size10 = isset($_POST['size_10']) ? 1 : 0;
    $size105 = isset($_POST['size_105']) ? 1 : 0;
    $size11 = isset($_POST['size_11']) ? 1 : 0;
    $size115 = isset($_POST['size_115']) ? 1 : 0;
    $price = $_POST['price'];
    $stock = $_POST['stock_quantity'];
    $description = $_POST['description'];
    $prod_archive = 'FALSE';
    $prod_status = 'Just In';
    $file_name = time() . '-' . $_FILES['upload']['name'];

    $path = '../assets/uploads/';

    // if new file selected is true
    if ($file_name) {
        // set allowed file types
        $allowedExtensions = ['jpg', 'jpeg', 'webp'];
        $uploadedFileExtension = strtolower(pathinfo($_FILES['upload']['name'], PATHINFO_EXTENSION));

        if (in_array($uploadedFileExtension, $allowedExtensions) && getimagesize($_FILES['upload']['tmp_name'])) {
            $maxSize = 5 * 1024 * 1024;
            $uploadedFileSize = $_FILES['upload']['size'];

            if ($uploadedFileSize <= $maxSize) {
                if (!empty($file_name)) {
                    if (!file_exists($path)) {
                        mkdir($path, 0755, true);
                    }
                    $temp_file = $_FILES['upload']['tmp_name'];
                    $file_name = time() . '-' . $_FILES['upload']['name'];
                    $new_file_path = $path . $file_name;

                    move_uploaded_file($temp_file, $new_file_path);

                } else if (empty($img)) {
                    $file_name = time() . '-' . $_FILES['upload']['name'];
                }

            } else {
                header("Location: /shoepee/products/prod.add.php?prod_id=$id&status=fatal_error&type=file_size");
                exit();
            }
        } else {
            header("Location: /shoepee/products/prod.add.php?prod_id=$id&status=fatal_error&type=file_type");
            exit();
        }
    } else if (empty($file_name)) {
        header("Location: /shoepee/products/prod.add.php?prod_id=$id&status=fatal_error&type=empty");
        exit();
    } else {
        $file_name = "";
    }

    $insertQuery = $con->prepare("INSERT INTO tbl_products (brand, model_name, size_8, size_85, size_9, size_95, size_10, size_105, size_11, size_115, price, stock_quantity, description, img_url, product_archive, prod_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $insertQuery->bind_param("ssssssssssssssss", $brand, $model, $size8, $size85, $size9, $size95, $size10, $size105, $size11, $size115, $price, $stock, $description, $file_name, $prod_archive, $prod_status);
    $insertQuery->execute();

    $success = "Item Added Successfully!";

    $_SESSION['success'] = $success;

    header("Location: /shoepee/products/prod.manage.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php echo $headContent; ?>
    <title>SHOEPEE | ADD PRODUCT</title>
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
    <div class="flex-wrapper">
        <form class="prod-form" action="" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="prod_id">
            <div class="container1">
                <div class="container1-chld1">
                    <div class="product-image-file-container">
                        <div class="product-image">
                            <img src="/shoepee/assets/uploads/" alt="">
                            <a id="uploadLink"><span class="material-symbols-outlined">add_photo_alternate</span> Insert
                                image</a>
                        </div>
                        <input type="hidden" name="img_url" value="">
                        <input type="file" name="upload" id="upload" required style="display: none;">
                        <span id="selectedFileName"></span>
                        <span id="errorHandler"></span>
                    </div>
                </div>
                <div class="container1-chld2">
                    <div class="container1-chld2-chld1">
                        <div class="brand-container">
                            <h4>Brand</h4>
                            <select class="input" name="brand" id="">
                                <option value=""></option>
                                <option value="Nike">Nike</option>
                                <option value="Adidas">Adidas</option>
                                <option value="Converse">Converse</option>
                                <option value="Puma">Puma</option>
                            </select>
                        </div>
                        <div class="model-name-container">
                            <h4>Model</h4>
                            <input class="input" type="text" name="model_name" minlength="3" maxlength="50"
                                autocomplete="off" required>
                        </div>
                    </div>
                    <div class="container1-chld2-chld2">
                        <div class="status-container">
                            <h4>Status</h4>
                            <select name="prod_status" id="" class="input">
                                <option value=""></option>
                                <option value="Just In">Just In</option>
                                <option value="Bestseller">Bestseller</option>
                            </select>
                        </div>
                        <div class="stock-quantity-container">
                            <h4>Stock Qty.</h4>
                            <input type="number" class="input" name="stock_quantity" value="0" min="0" max="500"
                                autocomplete="off" required>
                        </div>
                    </div>
                    <div class="container1-chld2-chld3">
                        <div class="price-container">
                            <h4>Price</h4>
                            <input type="text" class="input" name="price" value="0.00" autocomplete="off" required>
                        </div>
                    </div>
                </div>
            </div>
            <div class="container2">
                <div class="size-list-container">
                    <h4>Choose available Sizes</h4>
                    <div class="size-list">
                        <div class="checkbox-wrapper">
                            <label class="checkbox-wrapper">
                                <input class="checkbox-input" type="checkbox" name="size_8" value="1">
                                <span class="checkbox-tile">
                                    <span class="checkbox-label">US 8</span>
                                </span>
                            </label>
                        </div>
                        <div class="checkbox-wrapper">
                            <label class="checkbox-wrapper">
                                <input class="checkbox-input" type="checkbox" name="size_85" value="1">
                                <span class="checkbox-tile">
                                    <span class="checkbox-label">US 8.5</span>
                                </span>
                            </label>
                        </div>
                        <div class="checkbox-wrapper">
                            <label class="checkbox-wrapper">
                                <input class="checkbox-input" type="checkbox" name="size_9" value="1">
                                <span class="checkbox-tile">
                                    <span class="checkbox-label">US 9</span>
                                </span>
                            </label>
                        </div>
                        <div class="checkbox-wrapper">
                            <label class="checkbox-wrapper">
                                <input class="checkbox-input" type="checkbox" name="size_95" value="1">
                                <span class="checkbox-tile">
                                    <span class="checkbox-label">US 9.5</span>
                                </span>
                            </label>
                        </div>
                        <div class="checkbox-wrapper">
                            <label class="checkbox-wrapper">
                                <input class="checkbox-input" type="checkbox" name="size_10" value="1">
                                <span class="checkbox-tile">
                                    <span class="checkbox-label">US 10</span>
                                </span>
                            </label>
                        </div>
                        <div class="checkbox-wrapper">
                            <label class="checkbox-wrapper">
                                <input class="checkbox-input" type="checkbox" name="size_105" value="1">
                                <span class="checkbox-tile">
                                    <span class="checkbox-label">US 10.5</span>
                                </span>
                            </label>
                        </div>
                        <div class="checkbox-wrapper">
                            <label class="checkbox-wrapper">
                                <input class="checkbox-input" type="checkbox" name="size_11" value="1">
                                <span class="checkbox-tile">
                                    <span class="checkbox-label">US 11</span>
                                </span>
                            </label>
                        </div>
                        <div class="checkbox-wrapper">
                            <label class="checkbox-wrapper">
                                <input class="checkbox-input" type="checkbox" name="size_115" value="1">
                                <span class="checkbox-tile">
                                    <span class="checkbox-label">US 11.5</span>
                                </span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="description-container">
                    <h4>Description</h4>
                    <input type="hidden" name="description" id="hiddenInput" value="" required>
                    <textArea type="text" class="input description" id="sourceTextArea" minlength="0" maxlength="2000"
                        required cols="4" rows="8" oninput="updateValue()"></textArea>
                </div>
            </div>
            <div class="prod-add-action">
                <button class="btn" type="submit" name="add_prod" title="Add item in store"><span
                        class="material-symbols-outlined">add</span> Add Item</button>
            </div>
        </form>
    </div>
    <script type="module" src="/shoepee/assets/JS/script.js"></script>
    <script type="module" src="/shoepee/assets/JS/nav.js"></script>
    <script>
        document.getElementById('sourceTextArea').value = document.getElementById('hiddenInput').value;

        function updateValue() {
            var textAreaValue = document.getElementById('sourceTextArea').value;

            document.getElementById('hiddenInput').value = textAreaValue;
        }
        
        document.addEventListener('DOMContentLoaded', function () {

            const uploadLink = document.getElementById('uploadLink');
            const uploadInput = document.getElementById('upload');
            const selectedFileName = document.getElementById('selectedFileName');
            const errorHandler = document.getElementById('errorHandler');
            const previewImage = document.querySelector('.product-image-file-container .product-image img');

            uploadLink.addEventListener('click', function (e) {
                e.preventDefault();
                uploadInput.click();
            });

            const allowedFileTypes = ["image/jpeg", "image/jpg", "image/webp"];
            const maxFileSize = 5 * 1024 * 1024;

            uploadInput.addEventListener('change', function () {
                const selectedFile = this.files[0];

                if (selectedFile) {
                    const fileType = selectedFile.type;

                    if (!allowedFileTypes.includes(fileType)) {

                        const allowedExtensions = allowedFileTypes.map(type => type.split("/")[1].toUpperCase());
                        const allowedTypesString = allowedExtensions.join(', ');

                        errorHandler.textContent = 'We only support ' + allowedTypesString + '.';
                        selectedFileName.textContent = '';
                        this.value = null;

                    } else if (selectedFile.size >= maxFileSize) {

                        errorHandler.textContent = 'Please upload a picture smaller than 5 MB';
                        selectedFileName.textContent = '';
                        this.value = null;

                    } else {

                        const fileName = selectedFile.name;
                        selectedFileName.textContent = 'Choosed file ' + fileName;

                        const reader = new FileReader();
                        reader.onload = function (e) {
                            previewImage.src = e.target.result;
                        };

                        reader.readAsDataURL(selectedFile);
                    }
                } else {

                    selectedFileName.textContent = '';
                    errorHandler.textContent = '';

                }
            });

        });
    </script>
</body>

</html>