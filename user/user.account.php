<?php
// ../user/user.account.php
session_start();

include_once(__DIR__ . "/../connections/connection.php");
include_once(__DIR__ . "/../connections/head.php");
$con = connection();

if (!isset($_SESSION['UserLogin'])) {
    header("Location: /shoepee/auth/signin.php");
    exit();
}

$id = $_SESSION['id'] ?? NULL;
$status = $_SESSION['status'] ?? NULL;

if (isset($_POST["done-success"])) {
    unset($_SESSION['status']);
    $status = '';
}

if ($id) {
    $userSql = "SELECT * FROM tbl_users WHERE id = ?";
    $stmtUser = $con->prepare($userSql);
    $stmtUser->bind_param("i", $id);
    $stmtUser->execute();
    $user = $stmtUser->get_result()->fetch_assoc();

    $countItems = function ($table, $historyArchiveValue = null) use ($con, $id) {
        $condition = ($historyArchiveValue !== null) ? "  AND history_archive = ?" : "";
        $stmt = $con->prepare("SELECT COUNT(*) AS total_items FROM $table WHERE user_id = ?$condition");
        if ($historyArchiveValue !== null) {
            $stmt->bind_param("is", $id, $historyArchiveValue);
        } else {
            $stmt->bind_param("i", $id);
        }

        $stmt->execute();
        return $stmt->get_result()->fetch_assoc()['total_items'];
    };

    $totalItems = $countItems('tbl_bag');
    $totalFavItems = $countItems('tbl_favorites');
    $totalCheckoutItem = $countItems('tbl_checkout_history', 'FALSE');

    $errorOldPass = $errorNewPass = $errorConfirmNewpass = $errorConfirm = $errorEmail = $errorUsername = $errorConfirmEmail = $errorConfirmVerify = $errorConfirmPassword = "";

    if (isset($_POST['delete_account'])) {
        $checkEmail = $_POST['checkEmail'];
        $verify = $_POST['verify'];
        $confirmPassword = $_POST['checkPassword'];

        $checkEmailQuery = "SELECT * FROM tbl_users WHERE email = ?";
        $stmtEmail = $con->prepare($checkEmailQuery);
        $stmtEmail->bind_param("s", $checkEmail);
        $stmtEmail->execute();
        $resultEmail = $stmtEmail->get_result();

        if ($resultEmail->num_rows === 0) {
            $errorConfirmEmail = "Invalid email";
        }

        if ($verify !== "delete my account") {
            $errorConfirmVerify = "Wrong verification";
        }

        $hashedPassword = $user['password'];

        if (!password_verify($confirmPassword, $hashedPassword)) {
            $errorConfirmPassword = "Invalid password";
        }

        // If email, password, and verification are correct, proceed with deletion
        if (empty($errorConfirmEmail) && empty($errorConfirmPassword) && $verify === 'delete my account') {

            $clearBagSQL = "DELETE FROM tbl_bag WHERE user_id = ?";
            $stmtClearBag = $con->prepare($clearBagSQL);
            $stmtClearBag->bind_param("i", $id);
            $stmtClearBag->execute();

            $removeFromFavSQL = "DELETE FROM tbl_favorites WHERE user_id = ?";
            $stmt = $con->prepare($removeFromFavSQL);
            $stmt->bind_param("i", $id);
            $stmt->execute();

            $historyArchive = "TRUE";
            $softDeleteAccount = "deleted";

            $deleteCheckoutHistory = "UPDATE tbl_checkout_history SET history_archive = ? WHERE user_id = ?";
            $stmtDeleteCheckoutHistory = $con->prepare($deleteCheckoutHistory);
            $stmtDeleteCheckoutHistory->bind_param("si", $historyArchive, $id);
            $stmtDeleteCheckoutHistory->execute();

            $deleteOrderHistory = "UPDATE tbl_order_history SET history_archive = ? WHERE user_id = ?";
            $stmtDeleteOrderHistory = $con->prepare($deleteOrderHistory);
            $stmtDeleteOrderHistory->bind_param("si", $historyArchive, $id);
            $stmtDeleteOrderHistory->execute();

            $deleteUserSQL = "UPDATE tbl_users SET account_status = ? WHERE id = ?";
            $stmtDeleteUser = $con->prepare($deleteUserSQL);
            $stmtDeleteUser->bind_param("si", $softDeleteAccount, $id);
            $stmtDeleteUser->execute();

            header("Location: /shoepee/auth/signout.php");
            exit();
        }
    }

    if (isset($_POST['editUser'])) {
        function generateUsernameSuggestions($originalUsername)
        {
            $suggestions = [];
            $charactersToChange = ceil(strlen($originalUsername) * 0.2);
            for ($i = 0; $i < 3; $i++) {
                $modifiedUsername = modifyUsername($originalUsername, $charactersToChange);
                $suggestions[] = $modifiedUsername;
            }
            return $suggestions;
        }
        function modifyUsername($originalUsername, $charactersToChange)
        {
            $modifiedUsername = $originalUsername;
            for ($i = 0; $i < $charactersToChange; $i++) {
                $randomIndex = rand(0, strlen($originalUsername) - 1);
                $randomCharacter = generateRandomCharacter();
                $modifiedUsername[$randomIndex] = $randomCharacter;
            }

            return $modifiedUsername;
        }
        function generateRandomCharacter()
        {
            $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
            $randomIndex = rand(0, strlen($characters) - 1);

            return $characters[$randomIndex];
        }

        $loggedAccountID = $id;
        $profileImg = $_POST['profile_img'];
        $newFileSelected = !empty($_FILES['upload']['name']);
        $path = '../assets/images/users/';

        $newUsername = $_POST['newUsername'];
        $newEmail = $_POST['newEmail'];

        $checkEmailQuery = "SELECT * FROM tbl_users WHERE email = ? AND id != ?";
        $stmtEmail = $con->prepare($checkEmailQuery);
        $stmtEmail->bind_param("si", $newEmail, $loggedAccountID);
        $stmtEmail->execute();
        $resultEmail = $stmtEmail->get_result();

        $checkUsernameQuery = "SELECT * FROM tbl_users WHERE username = ? AND id != ?";
        $stmtUsername = $con->prepare($checkUsernameQuery);
        $stmtUsername->bind_param("si", $newUsername, $loggedAccountID);
        $stmtUsername->execute();
        $resultUsername = $stmtUsername->get_result();

        if ($resultEmail->num_rows > 0) {
            $errorEmail = "Email already used";
        }

        if ($resultUsername->num_rows > 0) {
            $suggestedUsernames = generateUsernameSuggestions($newUsername);
            $errorUsername = $_POST['newUsername'] . " is already used. Try one of these: " . implode(', ', $suggestedUsernames);
        }

        if (empty($errorUsername) && empty($errorEmail)) {
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
                        if (!empty($profileImg)) {
                            // check if the directory is not exists in the given path
                            if (!file_exists($path)) {
                                // if file directory is not exists then create a directory
                                mkdir($path, 0755, true);
                            }
                            $temp_file = $_FILES['upload']['tmp_name'];
                            $new_file_path = $path . time() . '-' . $_FILES['upload']['name'];
                            $file_name = time() . '-' . $_FILES['upload']['name'];
                            // retrieve the current image file location
                            $currentImagePath = $path . $profileImg;
                            // if the image already exists in the uploads folder
                            if (file_exists($currentImagePath)) {
                                unlink($currentImagePath);
                                $new_file_path = $path . time() . '-' . $_FILES['upload']['name'];
                            }
                            // else if the product doesn't have image
                        } else if (empty($profileImg)) {
                            $new_file_path = $path . time() . '-' . $_FILES['upload']['name'];
                            $file_name = time() . '-' . $_FILES['upload']['name'];
                        }
                        // check if the image file is inserted
                        if (move_uploaded_file($temp_file, $new_file_path)) {
                            echo "<script>alert('Success Inserting Image!')</script>";
                        } else {
                            echo "<script>alert('Error: Temp File')</script>";
                        }
                    } else {
                        // if the size is too large EXIT
                        header("Location: /shoepee/user/user.account.php?status=error&type=file_size");
                        exit();
                    }
                } else {
                    // if the file type is not matched EXIT
                    $file_name = "";
                    header("Location: /shoepee/user/user.account.php?status=error&type=file_type");
                    exit();
                }
            } else if (empty($newFileSelected)) {
                if (!empty($profileImg)) {
                    // if it has already an image, remain it's value
                    $file_name = $profileImg;
                } else if (empty($profileImg)) {
                    // update the file_name as NULL
                    $file_name = "";
                }
            } else {
                $file_name = "";
            }

            $updateUser = "UPDATE tbl_users SET username = ?, email = ?, profile_img = ? WHERE id = ?";
            $stmt = $con->prepare($updateUser);
            $stmt->bind_param("sssi", $newUsername, $newEmail, $file_name, $id);
            $stmt->execute();

            $success = "Update Successful!";
            $_SESSION['status'] = $success;

            header("Location: /shoepee/user/user.account.php?status=success");
            exit();
        }
    }

    if (isset($_POST['changePassword'])) {
        [
            $oldPassword,               // 1
            $newPassword,               // 2
            $confirmPassword            // 3
        ] = [
            $_POST['oldPassword'],      // 1
            $_POST['newPassword'],      // 2
            $_POST['confirmPassword']   // 3
        ];

        $userSql = "SELECT password FROM tbl_users WHERE id = ?";
        $stmt = $con->prepare($userSql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $currentUser = $result->fetch_assoc();

        $userPass = $currentUser["password"];
        // echo $hashedPassword . "<br>";
        if (!password_verify($oldPassword, $userPass)) {
            $errorOldPass = "Invalid old password";
        }

        if ($newPassword === $oldPassword) {
            $errorNewPass = "New password must be different from old password";
        }

        if ($newPassword != $confirmPassword) {
            $errorConfirmNewpass = "Passwords do not match!";
        }

        if (empty($errorOldPass) && empty($errorNewPass) && empty($errorConfirmNewpass)) {
            $newPassword = password_hash($newPassword, PASSWORD_DEFAULT);

            $updatePassword = "UPDATE tbl_users SET password = ? WHERE id = ?";
            $updatestmt = $con->prepare($updatePassword);
            $updatestmt->bind_param("si", $newPassword, $id);
            $updatestmt->execute();

            $redirect = ($_SESSION['id'] === $id ? '../user/user.account.php' : '../auth/signin.php');

            header("Location: $redirect");
            exit();
        }

    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php echo $headContent; ?>
    <title>SHOEPEE |
        <?php echo $user['username']; ?>
    </title>
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
    <div class="flex-wrapper">
        <div class="pop-overlay" <?php echo !empty($status) ? 'style="display: block;"' : 'style="display: none;"'; ?>
            animate="fadeIn"></div>
        <div class="confirm-dialog" <?php echo !empty($status) ? 'style="display: flex;"' : 'style="display: none;"'; ?>
            animate="fadeIn">
            <div class="status-icon">
                <span class="material-symbols-outlined">check</span>
            </div>
            <div class="status-message">
                <h4>SUCCESS!</h4>
                <p>
                    <?php echo isset($_SESSION["status"]) ? $status : ''; ?>
                </p>
            </div>
            <form class="confirm-button" action="" method="POST">
                <button class="" name="done-success" type="submit">Done</button>
            </form>
        </div>
        <div class="user-account-header">
            <div class="user-account-profile-img">
                <img src="/shoepee/assets/images/users/<?php echo $user['profile_img']; ?>" alt="">
            </div>
            <div class="user-account-username-email">
                <span class="username">
                    <a href="/shoepee/user/user.account.php"></a>
                    <?php echo $user['username']; ?>
                </span>
                <span class="email">
                    <?php echo $user['email']; ?>
                </span>
            </div>
        </div>
        <div class="user-account-container">
            <div class="user-account-sidebar">
                <ul class="sidebar-menu">
                    <li class="sidebar-menu-links" menu-for="manage-profile" tabindex="0">
                        <!-- <a class="menu-link"> -->
                        <div class="active-indicator"></div>
                        <span class="material-symbols-outlined">person</span>
                        Profile
                        <!-- </a> -->
                    </li>
                    <li class="sidebar-menu-links" menu-for="manage-account" tabindex="0">
                        <!-- <a class="menu-link"> -->
                        <div class="active-indicator"></div>
                        <span class="material-symbols-outlined">manage_accounts</span>
                        Account
                        <!-- </a> -->
                    </li>
                    <li class="sidebar-menu-links" menu-for="account-password" tabindex="0">
                        <!-- <a class="menu-link"> -->
                        <div class="active-indicator"></div>
                        <span class="material-symbols-outlined">encrypted</span>
                        Password
                        <!-- </a> -->
                    </li>
                    <li class="sidebar-menu-links" menu-for="ordered-history" tabindex="0">
                        <!-- <a class="menu-link"> -->
                        <div class="active-indicator"></div>
                        <span class="material-symbols-outlined">history</span>
                        Order History
                        <!-- </a> -->
                    </li>
                </ul>
            </div>
            <form class="manage-profile" action="" method="POST" enctype="multipart/form-data">
                <div class="profile">
                    <div class="user-account-profile">
                        <div class="user-account-profile-img">
                            <img src="/shoepee/assets/images/users/<?php echo $user['profile_img']; ?>" alt="">
                            <a href="" id="uploadLink">
                                <span class="material-symbols-outlined">edit</span> Edit
                            </a>
                        </div>
                        <input type="hidden" name="profile_img" value="<?php echo $user['profile_img'] ?>">
                        <input class="imgPreview" id="upload" type="file" name="upload" style="display: none;">
                        <span id="selectedFileName"></span>
                        <span id="errorHandler" class="error-text"></span>
                    </div>
                    <div class="user-account-details">
                        <label for="newUsername">Username</label>
                        <span class="error-text"><?php echo $errorUsername; ?></span>
                        <input class="input" type="text"   
                            name="newUsername" 
                            value="<?php echo isset($_POST['newUsername']) ? $_POST['newUsername'] : $user['username']; ?>"
                            <?php echo !empty($errorUsername) ? 'style="border: 2px solid #ff000084;"' : ''; ?>>

                        <label for="newEmail">Email</label>
                        <span class="error-text"><?php echo $errorEmail; ?></span>
                        <input class="input" type="email" 
                            name="newEmail" 
                            value="<?php echo isset($_POST['newEmail']) ? $_POST['newEmail'] : $user['email']; ?>"
                            <?php echo !empty($errorEmail) ? 'style="border: 2px solid #ff000084;"' : ''; ?>>

                        <button class="btn" kind="update" name="editUser" type="submit" id="update-profile">
                            Save
                        </button>
                    </div>
                </div>
            </form>
            <form class="manage-account" action="" method="POST">
                <div class="user-account-delete">
                    <label for="confirmEmail">Confirm email</label>
                    <span class="error-text"><?php echo $errorConfirmEmail; ?></span>
                    <input class="input" type="email" name="checkEmail" 
                        maxlength="30" 
                        minlength="10" 
                        autocomplete="off"
                        required 
                        spellcheck="off"
                        value="<?php echo isset($_POST['checkEmail']) ? $_POST['checkEmail'] : ''; ?>"
                        <?php echo !empty($errorConfirmEmail) ? 'style="border: 2px solid #ff000084;"' : ''; ?>>

                    <label for="verify">Delete Account</label>
                    <span class="error-text"><?php echo $errorConfirmVerify; ?></span>
                    <span class="verify">To verify, type <i>delete my account</i> below.</span>
                    <input class="input" type="text" name="verify" 
                        maxlength="17" 
                        minlength="1" 
                        autocomplete="off"
                        required 
                        spellcheck="off" 
                        value="<?php echo isset($_POST['verify']) ? $_POST['verify'] : ''; ?>"
                        <?php echo !empty($errorConfirmVerify) ? 'style="border: 2px solid #ff000084;"' : ''; ?>>

                    <label for="confirmPassword">Confirm Password</label>
                    <span class="error-text"><?php echo $errorConfirmPassword; ?></span>
                    <input class="input" type="password" name="checkPassword" 
                        maxlength="30" 
                        minlength="8" 
                        autocomplete="off" 
                        required 
                        spellcheck="off"
                        value="<?php echo isset($_POST['checkPassword']) ? $_POST['checkPassword'] : ''; ?>"
                        <?php echo !empty($errorConfirmPassword) ? 'style="border: 2px solid #ff000084;"' : ''; ?>>

                    <button class="btn" kind="delete" name="delete_account" type="submit">Delete Account</button>
                </div>
            </form>
            <form class="account-password" action="" method="POST">
                <div class="user-account-update-password">
                    <label for="confirmEmail" id="label-oldPassword">Old password</label>
                    <span class="error-text"><?php echo $errorOldPass; ?></span>
                    <input type="password" class="input" name="oldPassword" minlength="0" maxlength="72"
                        autocomplete="off" required spellcheck="false"
                        value="<?php echo isset($_POST['oldPassword']) ? $_POST['oldPassword'] : ''; ?>"
                        <?php echo !empty($errorOldPass) ? 'style="border: 2px solid #ff000084;"' : ''; ?>>

                    <label for="newPassword" id="label-newPassword">New password</label>
                    <span class="error-text indicator1"><?php echo $errorNewPass; ?></span>
                    <div class="password-wrapper">
                        <span class="input-indicator-icon material-symbols-outlined"></span>
                        <input type="password" class="input" id="input-password" name="newPassword" minlength="0" maxlength="72" 
                            autocomplete="off" required spellcheck="false"
                            value="<?php echo isset($_POST['newPassword']) ? $_POST['newPassword'] : ''; ?>"
                            <?php echo !empty($errorNewPass) ? 'style="border: 2px solid #ff000084;"' : ''; ?>>
                    </div>

                    <label for="confirmPassword">Confirm new password</label>
                    <span class="error-text indicator2"><?php echo $errorConfirmNewpass; ?></span>
                    <input type="password" class="input" id="input-confirm-password" name="confirmPassword" minlength="0" maxlength="72"
                        autocomplete="off" required spellcheck="false"
                        value="<?php echo isset($_POST['confirmPassword']) ? $_POST['confirmPassword'] : ''; ?>"
                        <?php echo !empty($errorConfirmNewpass) ? 'style="border: 2px solid #ff000084;"' : ''; ?>>

                    <button class="btn" kind="update" value="changePassword" name="changePassword" type="submit">
                        Update
                    </button>
                </div>
            </form>
            <form class="ordered-history">
                <div class="ordered-history-list">
                    <?php
                    $orderHistorySql = "SELECT oh.order_id, oh.order_date, SUM(oi.total_amount) as total_amount, oh.shipping_fee 
                            FROM tbl_order_history oh
                            JOIN tbl_order_items oi ON oh.order_id = oi.order_id
                            WHERE oh.user_id = ? AND oh.history_archive = 'FALSE'
                            GROUP BY oh.order_id
                            ORDER BY oh.order_date DESC";
                    $stmtOrderHistory = $con->prepare($orderHistorySql);
                    $stmtOrderHistory->bind_param("i", $id);
                    $stmtOrderHistory->execute();
                    $resultOrderHistory = $stmtOrderHistory->get_result();

                    $currentOrderID = null;

                    while ($orderHistory = $resultOrderHistory->fetch_assoc()) {
                        $orderID = $orderHistory['order_id'];

                        if ($orderID !== $currentOrderID) {
                            $currentOrderID = $orderID;

                            $orderDate = new DateTime($orderHistory['order_date']);
                            $formattedOrderDate = $orderDate->format('F j, Y, g:i a');
                            ?>
                            <div class="ordered-list-item">

                                <div class="ordered-history-header">
                                    <p class="ordered-header-datetime">
                                        Date purchased: <span>
                                            <?php echo $formattedOrderDate; ?>
                                        </span>
                                    </p>
                                    <p class="ordered-header-shippingfee">
                                        Shipping Fee: <span>
                                            <?php echo "$" . $orderHistory['shipping_fee']; ?>
                                        </span>
                                    </p>
                                    <p class="ordered-header-totalamount">
                                        <?php echo "Total: $" . $orderHistory['total_amount'] + $orderHistory['shipping_fee']; ?>
                                    </p>
                                </div>

                                <?php
                                // Fetch order items for the current order ID
                                $orderItemsSql = "SELECT oi.*, p.* 
                                                FROM tbl_order_items oi 
                                                JOIN tbl_products p 
                                                ON oi.prod_id = p.prod_id 
                                                WHERE oi.order_id = ?";
                                $stmtOrderItems = $con->prepare($orderItemsSql);
                                $stmtOrderItems->bind_param("i", $orderID);
                                $stmtOrderItems->execute();
                                $resultOrderItems = $stmtOrderItems->get_result();
                                ?>

                                <div class="ordered-item-cards">
                                    <?php
                                    while ($orderItem = $resultOrderItems->fetch_assoc()) {
                                        ?>
                                        <div class="ordered-item">
                                            <div class="ordered-item-image" <?php echo $orderItem['stock_quantity'] < 20 || $orderItem['product_archive'] === 'TRUE' ? 'title="Item unavailable"' : '' ?>>
                                                <a href="/shoepee/products/prod.view.php?prod_id=<?php echo $orderItem['prod_id']; ?>"
                                                    <?php echo $orderItem['stock_quantity'] < 20 || $orderItem['product_archive'] === 'TRUE' ? 'style="display: none;"' : '' ?>></a>
                                                <img src="/shoepee/assets/uploads/<?php echo $orderItem['img_url']; ?>" alt=""
                                                    height="100" width="100">
                                            </div>
                                            <div class="ordered-item-details">
                                                <p class="ordered-item-modelname" title="<?php echo $orderItem['model_name']; ?>">
                                                    <?php echo $orderItem['brand'] . " " . $orderItem['model_name']; ?>
                                                </p>
                                                <p class="ordered-item-amount">
                                                    <?php echo "$" . $orderItem['price']; ?>
                                                </p>
                                                <p class="ordered-item-qty">
                                                    <?php echo "Qty: " . $orderItem['quantity'] . " ($" . $orderItem['total_amount'] . ")"; ?>
                                                </p>
                                            </div>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                        <?php } ?>
                    <?php } ?>
                </div>
            </form>
        </div>
    </div>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>
    <script type="module" src="/shoepee/assets/JS/script.js"></script>
    <script type="module" src="/shoepee/assets/JS/nav.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            const uploadLink = document.getElementById('uploadLink');
            const uploadInput = document.getElementById('upload');
            const selectedFileName = document.getElementById('selectedFileName');
            const errorHandler = document.getElementById('errorHandler');
            const previewImage = document.querySelector('.user-account-profile .user-account-profile-img img');

            const types = ["image/jpeg", "image/jpg", "image/webp"];
            const maxSize = 2 * 1024 * 1024;

            uploadLink.addEventListener('click', function (e) {
                e.preventDefault();
                uploadInput.click();
            });

            uploadInput.addEventListener('change', function () {

                const selectedFile = this.files[0];

                if (selectedFile) {

                    const fileType = selectedFile.type;

                    if (!types.includes(fileType)) {

                        const allowedExtensions = types.map(type => type.split("/")[1].toUpperCase());
                        const allowedTypesString = allowedExtensions.join(', ');

                        previewImage.src = '../assets/images/users/' + '<?php echo $user['profile_img'] ?>';
                        errorHandler.textContent = 'We only support ' + allowedTypesString + '.';
                        selectedFileName.textContent = '';
                        this.value = null;

                    } else if (selectedFile.size >= maxSize) {

                        previewImage.src = '../assets/images/users/' + '<?php echo $user['profile_img'] ?>';
                        errorHandler.textContent = 'Please upload a picture smaller than 2 MB';
                        selectedFileName.textContent = '';
                        this.value = null;

                    } else {

                        const fileName = selectedFile.name;

                        selectedFileName.textContent = 'Choosed file ' + fileName;
                        errorHandler.textContent = '';

                        const reader = new FileReader();

                        reader.onload = function (e) {
                            previewImage.src = e.target.result;
                        };

                        reader.readAsDataURL(selectedFile);

                    }

                } else {

                    selectedFileName.textContent = '';
                    errorHandler.textContent = '';
                    previewImage.src = '../assets/images/users/' + '<?php echo $user['profile_img'] ?>';

                }
            });
        });
    </script>
</body>

</html>