<?php

session_start();

include_once(__DIR__ . "/../connections/connection.php");
include_once(__DIR__ . "/../connections/head.php");
$con = connection();

$error = $errorUsername = $errorEmail = $errorPassword = $usernameOrEmail = "";
$username = $email = $password = "";

if (isset($_POST['signin'])) {
    [$username, $email, $password] = [$_POST['username'], $_POST['email'], $_POST['password']];

    $userSql = "SELECT * FROM tbl_users WHERE username = ? AND email = ?";
    $stmt = $con->prepare($userSql);
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    $emailSql = "SELECT * FROM tbl_users WHERE email = ?";
    $emailstmt = $con->prepare($emailSql);
    $emailstmt->bind_param("s", $email);
    $emailstmt->execute();
    $emailResult = $emailstmt->get_result();

    $usernameSql = "SELECT * FROM tbl_users WHERE username = ?";
    $usernamestmt = $con->prepare($usernameSql);
    $usernamestmt->bind_param("s", $username);
    $usernamestmt->execute();
    $usernameResult = $usernamestmt->get_result();

    if ($usernameResult->num_rows == 0) {
        $errorUsername = "Invalid username";
    } 

    if ($emailResult->num_rows == 0) {
        $errorEmail = "Invalid email";
    }
    
    $hashedPassword = $user['password'] ?? null;

    if (!password_verify($password, $hashedPassword)) {
    $errorPassword = "Invalid password";
    }
    
    if (password_verify($password, $hashedPassword) && empty($errorUsername) && empty($errorEmail))
    {
        if ($user && $user['account_status'] === 'active')
        {
            $_SESSION['id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['UserLogin'] = $user['password'];
            $_SESSION['access'] = $user['access'];
    
            $redirect = ($_SESSION['access'] === 'user' ? '/shoepee/home/index.php' : '/shoepee/products/prod.manage.php');
            header("Location: $redirect");
            exit();
        } else {
            $error = "Account is not active";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php echo $headContent; ?>
    <link rel="stylesheet" href="/shoepee/assets/CSS/signin-signup.css">
    <title>SHOEPEE | SIGN IN</title>
</head>

<body>
    <div class="signin-body">
        <div class="left-container">
            <!-- <img src="../assets/images/shoepee_logo.png" alt=""> -->
        </div>
        <div class="right-container">
            <div class="form-wrapper">
                <img class="form-logo" src="/shoepee/assets/images/shoepee_logo.png" alt="">
                <h1>LOG IN</h1>
                <form class="signin" action="" method="POST">
                    <?php echo $error; ?>
                    <label for="username" class="form-lbl">Username</label>
                    <span class="error-text">
                        <?php echo $errorUsername; ?>
                    </span>
                    <input type="username" class="input" name="username" maxlength="20" minlength="3" autocomplete="off"
                        required spellcheck="false" value="<?php echo $username; ?>">

                    <label for="email" class="form-lbl">Email</label>
                    <span class="error-text">
                        <?php echo $errorEmail; ?>
                    </span>
                    <input type="email" class="input" name="email" maxlength="30" minlength="10" autocomplete="off"
                        required spellcheck="false" value="<?php echo $email; ?>">

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

                    <button class="btn" value="signin" name="signin" type="submit">Log in</button>
                    <span class="signup-link">
                        Doesn't have an account? <a class="btn-link" href="/shoepee/auth/signup.php">sign up</a>
                    </span>
                </form>
            </div>
        </div>
    </div>

</body>
<script src="/shoepee/assets/JS/script.js"></script>

</html>