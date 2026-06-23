<?php

session_start();

include_once(__DIR__ . "/../connections/connection.php");
include_once(__DIR__ . "/../connections/head.php");
$con = connection();

if (isset($_SESSION['id'])) {
    $id = $_SESSION['id'];
}

if (!isset($_SESSION['UserLogin'])) {
    header("Location: /shoepee/admin/auth.admin.php");
    exit();
}

$status = $_SESSION['status'] ?? NULL;

if (isset($_POST["done-success"])) {
    unset($_SESSION['status']);
    $status = '';
}

$userSql = "SELECT * FROM tbl_admins WHERE id = ?";
$stmt = $con->prepare($userSql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (isset($_GET['prod_id'])) {
    $id = $_GET['prod_id'];

    $prodSql = "SELECT * FROM tbl_products WHERE prod_id = ?";
    $stmt = $con->prepare($prodSql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $products = $result->fetch_assoc();
    } else {
        $products = [];
    }
}

if (isset($_POST['archive'])) {
    $id = $_POST['prod_id'];
    $prod_archive = $_POST['product_archive'];

    $archiveQuery = "UPDATE tbl_products 
        SET product_archive = ?
        WHERE prod_id = ?";
    $stmt = $con->prepare($archiveQuery);
    $stmt->bind_param("ss", $prod_archive, $id);
    $stmt->execute();

    if ($prod_archive === "TRUE")
    {
        $success = "Archive item Successful!";
        
    } else {
        $success = "Unarchive item Successful!";    
    }
    
    $_SESSION['status'] = $success;
    header("Location: /shoepee/products/prod.edit.php?prod_id=$id&status=success&type=archive");
    exit();
}

if (isset($_POST['submit']))
{

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
    $description = $_POST['description'];
    $status = $_POST['prod_status'];
    $price = $_POST['price'];
    $stock = $_POST['stock_quantity'];
    $id = $_POST['prod_id'];
    /*
    
    analyze

    - selected a new file
        - validate file type
            - check file size
                - has current file or $img is !empty
                    - retrieve the current file location
                    ==> $path = "../assets/uploads/";
                    
                    - delete currentFile and replace the $_FILES['upload']['name'];
                    ==> unlink($currentFile)
                    - insert the new file
                    ==> $file_name = $path . time() . "-" . $_FILES['upload']['name'];
                    
                    UPDATE
                - else if doesn't have current file
                    ==> $file_name = $path . time() . "-" . $_FILES['upload']['name'];
                    UPDATE
            - if file too large
            ==> EXIT
        - else file type not matched
        ==> EXIT
    
    - else if no selected file
        - has current file or $img !empty
        ==> $file_name = $img;
        UPDATE
        else doesn't have current file or $img is empty
            ==> $file_name = "";
            
    - else UPDATE and EXIT
        ==> $file_name = "";
    */
    $img = $_POST['img_url'];
    $newFileSelected = !empty($_FILES['upload']['name']);   // BOOLEAN
    $path = '../assets/uploads/';

    // if new file selected is true
    if ($newFileSelected) {
        // set allowed file types
        $allowedExtensions = ['jpg', 'jpeg', 'webp'];
        $uploadedFileExtension = strtolower(pathinfo($_FILES['upload']['name'], PATHINFO_EXTENSION));
        
        // Validate file type
        // check if the uploaded file's name meets the allowed extensions
        if (in_array($uploadedFileExtension, $allowedExtensions) && getimagesize($_FILES['upload']['tmp_name'])) {
            
            $maxSize = 2 * 1024 * 1024; // set the max file size to 2MB
            // if matched, check the file size
            $uploadedFileSize = $_FILES['upload']['size'];

            // if the file extension is true then check the file size next
            if ($uploadedFileSize <= $maxSize) {
                // if file type matched to the standard
                // check if it has already image
                if (!empty($img)) {
                    // check if the directory is not exists in the given path
                    if (!file_exists($path)) {
                        // if file directory is not exists then create a directory
                        mkdir($path, 0755, true);
                    }
                    $temp_file = $_FILES['upload']['tmp_name'];
                    $new_file_path = $path . time() . '-' . $_FILES['upload']['name'];
                    $file_name = time() . '-' . $_FILES['upload']['name'];
                    
                    // retrieve the current image file location
                    $currentImagePath = $path . $img;
                    
                    // if the image already exists in the uploads folder
                    if (file_exists($currentImagePath)) {
                        unlink($currentImagePath);
                        $new_file_path = $path . time() . '-' . $_FILES['upload']['name'];
                    }
                    // else if the product doesn't have image
                } else if (empty($img)) {
                    $new_file_path = $path . time() . '-' . $_FILES['upload']['name'];
                    $file_name = time() . '-' . $_FILES['upload']['name'];
                }

                // check if the image file is inserted
                if (move_uploaded_file($temp_file, $new_file_path)) {
                    echo "<script>console.log('Success Inserting Image!')</script>";
                } else {
                    echo "<script>console.log('Error: Temp File')</script>";
                }
            } else {
                // if the size is too large EXIT
                header("Location: /shoepee/products/prod.edit.php?prod_id=$id&status=fatal_error&type=file_size");
                exit();
            }            
        } else {
            // if the file type is not matched EXIT
            $file_name = "";
            header("Location: /shoepee/products/prod.edit.php?prod_id=$id&status=fatal_error&type=file_type");
            exit();
        }
    } else if (empty($newFileSelected)) {
        if (!empty($img)) {
            // if it has already an image, remain it's value
            $file_name = $img;

        } else if (empty($img)) {
            // update the file_name as NULL
            $file_name = "";
        }
    } else {
        $file_name = "";
    }
        // Update product details
        $insertQuery = "UPDATE tbl_products SET 
                            brand = ?, 
                            model_name = ?,
                            size_8 = ?, 
                            size_85 = ?, 
                            size_9 = ?, 
                            size_95 = ?, 
                            size_10 = ?, 
                            size_105 = ?, 
                            size_11 = ?, 
                            size_115 = ?, 
                            price = ?, 
                            stock_quantity = ?, 
                            description = ?, 
                            img_url = ?, 
                            prod_status = ? 
                            WHERE prod_id = ?";
    $stmt = $con->prepare($insertQuery);
    $stmt->bind_param("sssssssssssssssi", $brand, $model, $size8, $size85, $size9, $size95, $size10, $size105, $size11, $size115, $price, $stock, $description, $file_name, $status, $id);
    $stmt->execute();

    $success = "Update Successful!";

    $_SESSION['status'] = $success;
    header("Location: /shoepee/products/prod.edit.php?prod_id=$id&status=success");
    exit();

}

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
        <form class="prod-form" id="updateForm" action="" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="prod_id" value="<?php echo $products['prod_id']; ?>">
            <div class="container1">
                <div class="container1-chld1">
                    <div class="product-image-file-container">
                        <div class="product-image">
                            <img src="/shoepee/assets/uploads/<?php echo $products['img_url']; ?>" alt="">
                            <a href="" id="uploadLink"><span class="material-symbols-outlined">edit</span> Edit</a>
                        </div>
                        <input type="hidden" name="img_url" value="<?php echo $products['img_url']; ?>">
                        <input type="file" name="upload" id="upload" style="display: none;">
                        <span id="selectedFileName"></span>
                        <span id="errorHandler"></span>
                    </div>
                </div>
                <div class="container1-chld2">
                    <div class="container1-chld2-chld1">
                        <div class="brand-container">
                            <h4>Brand</h4>
                            <!-- <input type="text" class="input" name="brand" maxlength="20" minlength="3" pattern="[^\d]+" autocomplete="off" value="<?php $products['brand']; ?>"> -->
                            <select class="input" name="brand" id="">
                                <option value="" <?php if ($products['brand'] === '') echo 'selected'; ?>></option>
                                <option value="Adidas" <?php if ($products['brand'] === 'Adidas') echo 'selected'; ?>>Adidas</option>
                                <option value="Nike" <?php if ($products['brand'] === 'Nike') echo 'selected'; ?>>Nike</option>
                            </select>
                        </div>
                        <div class="model-name-container">
                            <h4>Model</h4>
                            <input class="input" type="text" name="model_name" minlength="3" maxlength="50"
                                autocomplete="off" value="<?php echo $products['model_name']; ?>" required>
                        </div>
                    </div>
                    <div class="container1-chld2-chld2">
                        <div class="status-container">
                            <h4>Status</h4>
                            <select name="prod_status" id="" class="input">
                                <option value=""></option>
                                <option value="Just In" <?php if ($products['prod_status'] === 'Just In') echo 'selected'; ?>>Just In</option>
                                <option value="Bestseller" <?php if ($products['prod_status'] === 'Bestseller') echo 'selected'; ?>>Bestseller</option>
                            </select>
                        </div>
                        <div class="stock-quantity-container">
                            <h4>Stock Qty.</h4>
                            <input type="number" class="input" name="stock_quantity" min="0" max="500"
                                autocomplete="off" value="<?php echo $products['stock_quantity']; ?>" required <?php echo $products['stock_quantity'] <= 50 ? 'title="Restock item" style="animation: stock-warning-pulse 1s linear infinite;"' : ''; ?>>
                        </div>
                    </div>
                    <div class="container1-chld2-chld3">
                        <div class="price-container">
                            <h4>Price</h4>
                            <input type="text" class="input" name="price" autocomplete="off"
                                value="<?php echo $products['price']; ?>" maxlength="9" required>
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
                            <input class="checkbox-input" type="checkbox" name="size_8" value="1"
                                <?php echo $products['size_8'] == '1' ? 'checked' : '' ; ?>>
                                <span class="checkbox-tile">
                                    <span class="checkbox-label">US 8</span>
                                </span>
                            </label>
                        </div>
                        <div class="checkbox-wrapper">
                            <label class="checkbox-wrapper">
                                <input class="checkbox-input" type="checkbox" name="size_85" value="1"
                                <?php echo $products['size_85'] == '1' ? 'checked' : '' ; ?>>
                                <span class="checkbox-tile">
                                    <span class="checkbox-label">US 8.5</span>
                                </span>
                            </label>
                        </div>
                        <div class="checkbox-wrapper">
                            <label class="checkbox-wrapper">
                                <input class="checkbox-input" type="checkbox" name="size_9" value="1"
                                <?php echo $products['size_9'] == '1' ? 'checked' : '' ; ?>>
                                <span class="checkbox-tile">
                                    <span class="checkbox-label">US 9</span>
                                </span>
                            </label>
                        </div>
                        <div class="checkbox-wrapper">
                            <label class="checkbox-wrapper">
                                <input class="checkbox-input" type="checkbox" name="size_95" value="1"
                                <?php echo $products['size_95'] == '1' ? 'checked' : '' ; ?>>
                                <span class="checkbox-tile">
                                    <span class="checkbox-label">US 9.5</span>
                                </span>
                            </label>
                        </div>
                        <div class="checkbox-wrapper">
                            <label class="checkbox-wrapper">
                                <input class="checkbox-input" type="checkbox" name="size_10" value="1"
                                <?php echo $products['size_10'] == '1' ? 'checked' : '' ; ?>>
                                <span class="checkbox-tile">
                                    <span class="checkbox-label">US 10</span>
                                </span>
                            </label>
                        </div>
                        <div class="checkbox-wrapper">
                            <label class="checkbox-wrapper">
                                <input class="checkbox-input" type="checkbox" name="size_105" value="1"
                                <?php echo $products['size_105'] == '1' ? 'checked' : '' ; ?>>
                                <span class="checkbox-tile">
                                    <span class="checkbox-label">US 10.5</span>
                                </span>
                            </label>
                        </div>
                        <div class="checkbox-wrapper">
                            <label class="checkbox-wrapper">
                                <input class="checkbox-input" type="checkbox" name="size_11" value="1"
                                <?php echo $products['size_11'] == '1' ? 'checked' : '' ; ?>>
                                <span class="checkbox-tile">
                                    <span class="checkbox-label">US 11</span>
                                </span>
                            </label>
                        </div>
                        <div class="checkbox-wrapper">
                            <label class="checkbox-wrapper">
                                <input class="checkbox-input" type="checkbox" name="size_115" value="1"
                                <?php echo $products['size_115'] == '1' ? 'checked' : '' ; ?>>
                                <span class="checkbox-tile">
                                    <span class="checkbox-label">US 11.5</span>
                                </span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="description-container">
                    <h4>Description</h4>
                    <input type="hidden" name="description" id="hiddenInput" value="<?php // Prevent interferance of speacial characters for HTML structure
                        $encodedDescription = htmlspecialchars($products['description'], ENT_QUOTES, 'UTF-8'); echo $encodedDescription; ?>">
                    <textArea type="text" class="input description" id="sourceTextArea" minlength="0" maxlength="5000"
                        required cols="4" rows="8" spellcheck="false"
                        oninput="updateValue()"><?php echo $products['description']; ?></textArea>
                </div>
            </div>
            <div class="prod-edit-action">
                <button id="updateBtn" class="btn" type="submit" name="submit" kind="update" title="Update Item"><span
                        class="material-symbols-outlined">upload</span></button>
                <?php if ($products['product_archive'] == 'FALSE') { ?>
                    <input type="hidden" name="product_archive" value="TRUE">
                    <button class="btn" type="subimt" name="archive" kind="archive" title="Archive Item"><span
                            class="material-symbols-outlined">archive</span> 
                    </button>
                <?php } else { ?>
                    <input type="hidden" name="product_archive" value="FALSE">
                    <button class="btn" type="subimt" name="archive" kind="archive" title="Unarchive Item"><span
                            class="material-symbols-outlined">unarchive</span>
                    </button>
                <?php } ?>
                <!-- <span> -->
                <div class="loader" style="display: none;">Please wait...</div>
            </div>
        </form>
        <!-- <div class="confirm-dialog" id="confirmDialog" style="display: none;" animate="fadeIn"> -->
            <!-- <div class="pop-overlay" id="popOverlay" style="display: none;" animate="fadeIn"></div> -->
            <div class="pop-overlay" <?php echo !empty($status) ? 'style="display: block;"' : 'style="display: none;"' ; ?> animate="fadeIn"></div>
        <div class="confirm-dialog" <?php echo !empty($status) ? 'style="display: flex;"' : 'style="display: none;"' ; ?> animate="fadeIn">
            <div class="status-icon">
                <span class="material-symbols-outlined">check</span>
            </div>
            <div class="status-message">
                <h4>SUCCESS!</h4>
                <p><?php if (isset($_SESSION["status"])) {echo $status;} ?></p>
            </div>
            <form class="confirm-button" action="" method= "POST">
                <button class="" id="doneSuccess" name="done-success" type="submit">Done</button>
            </form>
        </div>
    </div>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>
    <script type="module" src="/shoepee/assets/JS/script.js"></script>
    <script type="module" src="/shoepee/assets/JS/nav.js"></script>
    <script>
        document.getElementById('sourceTextArea').value = document.getElementById('hiddenInput').value;
        function updateValue()
        {
            var textAreaValue = document.getElementById('sourceTextArea').value;
            document.getElementById('hiddenInput').value = textAreaValue;
        }

        
        document.addEventListener('DOMContentLoaded', function ()
        {
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
            const maxFileSize = 2 * 1024 * 1024;

            uploadInput.addEventListener('change', function ()
            {
                const selectedFile = this.files[0];
                
                if (selectedFile)
                {
                    const fileType = selectedFile.type;

                    if (!allowedFileTypes.includes(fileType))
                    {    
                        const allowedExtensions = allowedFileTypes.map(type => type.split("/")[1].toUpperCase());
                        const allowedTypesString = allowedExtensions.join(', ');

                        errorHandler.textContent = 'We only support ' + allowedTypesString + '.';
                        selectedFileName.textContent = '';
                        this.value = null;
                        previewImage.src = '../assets/uploads/' + '<?php echo $products['img_url']; ?>';

                    }
                        else if (selectedFile.size >= maxFileSize)
                        {
                            errorHandler.textContent = 'Please upload a picture smaller than 2 MB';
                            selectedFileName.textContent = '';
                            this.value = null;
                            previewImage.src = '../assets/uploads/' + '<?php echo $products['img_url']; ?>';
                            }
                                else
                                {
                                    const fileName = selectedFile.name;
                                    selectedFileName.textContent = 'Choosed file ' + fileName;
                                    const reader = new FileReader();
                                    reader.onload = function (e)
                                    {
                                        previewImage.src = e.target.result;
                                    };
                                    reader.readAsDataURL(selectedFile);
                                }
                }
                    else 
                    {
                        selectedFileName.textContent = '';
                        errorHandler.textContent = '';
                        previewImage.src = '../assets/uploads/' + '<?php echo $products['img_url']; ?>';
                    
                    }
            });
        });
    </script>
</body>

</html>