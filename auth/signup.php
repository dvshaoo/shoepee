<?php
session_start();

include_once(__DIR__ . "/../connections/connection.php");
include_once(__DIR__ . "/../connections/head.php");
$con = connection();

$error = $errorUsername = $errorEmail = $errorPassword = $errorConfirmPassword = $errorUsernameOrEmail = "";
$username = $email = $password = $confirmPassword = "";

function generateUsernameSuggestions($originalUsername)
{
    $suggestions = [];

    for ($i = 0; $i < 3; $i++) {
        $suggestions[] = modifyUsername($originalUsername, ceil(strlen($originalUsername) * 0.2));
    }

    return $suggestions;
}

function modifyUsername($originalUsername, $charactersToChange)
{
    $modifiedUsername = $originalUsername;

    for ($i = 0; $i < $charactersToChange; $i++) {
        $randomIndex = rand(0, strlen($originalUsername) - 1);
        $modifiedUsername[$randomIndex] = generateRandomCharacter();
    }

    return $modifiedUsername;
}

function generateRandomCharacter()
{
    $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    return $characters[rand(0, strlen($characters) - 1)];
}

if (isset($_POST['signup'])) {
    [
        $username,
        $email,
        $password,
        $confirmPassword
    ] = [
        $_POST['username'],
        $_POST['email'],
        $_POST['password'],
        $_POST['confirm_password']
    ];
    $access = "user";
    $accountStatus = "active";

    if (empty($username) || empty($email) || empty($password) || empty($confirmPassword)) {
        $error = "All fields are required.";
    } else {

        $stmtUsername = $con->prepare("SELECT * FROM tbl_users WHERE username = ?");
        $stmtUsername->bind_param("s", $username);
        $stmtUsername->execute();

        if ($stmtUsername->get_result()->num_rows > 0) {
            $suggestedUsernames = generateUsernameSuggestions($username);
            $errorUsername = "Username already used. Try one of these: " . implode(', ', $suggestedUsernames);
        }

        $stmtEmail = $con->prepare("SELECT * FROM tbl_users WHERE email = ?");
        $stmtEmail->bind_param("s", $email);
        $stmtEmail->execute();

        if ($stmtEmail->get_result()->num_rows > 0) {
            $errorEmail = "Email already used";
        }

        if ($password !== $confirmPassword) {
            $errorConfirmPassword = "Passwords do not match!";
        }

        if (empty($errorPassword) && empty($errorConfirmPassword) && empty($errorEmail) && empty($errorUsername)) {
            
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $stmtInsert = $con->prepare("INSERT INTO tbl_users (username, email, password, access, account_status) VALUES (?, ?, ?, ?, ?)");
            $stmtInsert->bind_param("sssss", $username, $email, $hashedPassword, $access, $accountStatus);
            $stmtInsert->execute();

            $_SESSION['id'] = $stmtInsert->insert_id;
            $_SESSION['username'] = $username;
            $_SESSION['UserLogin'] = $hashedPassword;
            $_SESSION['access'] = $access;

            header("Location: /shoepee/home/index.php");
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
    <title>SHOEPEE | SIGN UP</title>
</head>

<body>
    <div class="signup-body">
        <div class="left-container">
            <!-- <img src="../assets/images/shoepee_logo.png" alt=""> -->
        </div>
        <div class="right-container">
            <div class="form-wrapper">
                <img class="form-logo" src="/shoepee/assets/images/shoepee_logo.png" alt="">
                <h1>CREATE ACCOUNT</h1>
                <form class="signup" action="" method="POST">
                    <span class="error-text">
                        <?php echo $error; ?>
                    </span>
                    <label for="username" class="form-lbl">Username</label>
                    <span class="error-text"><?php echo $errorUsername; ?></span>
                    <input type="username" class="input" name="username" maxlength="20" minlength="3" autocomplete="off"
                        required spellcheck="false" value="<?php echo $username; ?>"
                        <?php echo !empty($errorUsername) ? 'style="border: 2px solid #ff000084;"' : ''; ?>>

                    <label for="email" class="form-lbl">Email</label>
                    <span class="error-text"><?php echo $errorEmail; ?></span>
                    <input type="email" class="input" name="email" maxlength="30" minlength="10" autocomplete="off"
                        required spellcheck="false" value="<?php echo $email; ?>"
                        <?php echo !empty($errorEmail) ? 'style="border: 2px solid #ff000084;"' : ''; ?>>

                    <label for="password" class="form-lbl">Password</label>
                    <span class="error-text"><?php echo $errorPassword; ?></span>
                    <div class="password-wrapper">
                        <input type="password" class="input" name="password" maxlength="20" minlength="8"
                            autocomplete="off" required spellcheck="false" value="<?php echo $password; ?>"
                            <?php echo !empty($errorPassword) ? 'style="border: 2px solid #ff000084;"' : ''; ?>>
                        <span class="material-symbols-outlined hide-pass">visibility_off</span>
                        <span class="material-symbols-outlined show-pass">visibility</span>
                    </div>

                    <label for="confirm_password" class="form-lbl">Confirm Password</label>
                    <span class="error-text"><?php echo $errorConfirmPassword; ?></span>
                    <input type="password" class="input" name="confirm_password" maxlength="20" minlength="8"
                        autocomplete="off" required spellcheck="false" value="<?php echo $confirmPassword; ?>"
                        <?php echo !empty($errorConfirmPassword) ? 'style="border: 2px solid #ff000084;"' : ''; ?>>

                    <button class="btn" value="signup" name="signup" type="submit">Create my account</button>
                    <span class="signin-link">
                        Already have an account? <a class="btn-link" href="/shoepee/auth/signin.php">sign in</a>
                    </span>
                </form>
            </div>
        </div>
    </div>
</body>
<script type="module" src="/shoepee/assets/JS/script.js"></script>

</html>