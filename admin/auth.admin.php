<?php
session_start();

include_once(__DIR__ . "/../connections/connection.php");
include_once(__DIR__ . "/../connections/head.php");
$con = connection();

$error = $errorUsername = $errorPassword = "";
$username = $password = "";

if (isset($_POST['admin_signin'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $adminSql = "SELECT * FROM tbl_admins WHERE username = ?";
    $stmt = $con->prepare($adminSql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $admin = $result->fetch_assoc();

    if (!$admin) {
        $errorUsername = "Invalid username";
    } else {
        $hashedPassword = $admin['password'];
        if (!password_verify($password, $hashedPassword)) {
            $errorPassword = "Invalid password";
        } else {
            $_SESSION['id'] = $admin['id'];
            $_SESSION['username'] = $admin['username'];
            $_SESSION['UserLogin'] = $admin['password'];
            $_SESSION['access'] = 'admin';

            header("Location: ../products/prod.manage.php");
            exit();
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php echo $headContent; ?>
    <link rel="stylesheet" href="/shoepee/assets/CSS/signin-signup.css">
    <title>SHOEPEE | ADMIN LOGIN</title>
</head>

<body>
    <div class="signin-body">
        <div class="left-container">
        </div>
        <div class="right-container">
            <div class="form-wrapper">
                <img class="form-logo" src="/shoepee/assets/images/shoepee_logo.png" alt="">
                <h1>ADMIN LOGIN</h1>
                <form class="signin" action="" method="POST">
                    <?php echo $error; ?>
                    <label for="username" class="form-lbl">Username</label>
                    <span class="error-text">
                        <?php echo $errorUsername; ?>
                    </span>
                    <input type="text" class="input" name="username" maxlength="20" minlength="3" autocomplete="off"
                        required spellcheck="false" value="<?php echo $username; ?>">

                    <label for="password" class="form-lbl">Password</label>
                    <span class="error-text">
                        <?php echo $errorPassword; ?>
                    </span>
                    <div class="password-wrapper">
                        <input type="password" class="input" name="password" maxlength="30" minlength="8" required
                            spellcheck="false" value="<?php echo $password; ?>">
                        <span class="material-symbols-outlined hide-pass">visibility_off</span>
                        <span class="material-symbols-outlined show-pass">visibility</span>
                    </div>

                    <button class="btn" value="admin_signin" name="admin_signin" type="submit">Log in</button>
                    <span class="signup-link">
                        <a class="btn-link" href="/shoepee/auth/signin.php">Back to User Login</a>
                    </span>
                </form>
            </div>
        </div>
    </div>

</body>
<script src="/shoepee/assets/JS/script.js"></script>

</html>
