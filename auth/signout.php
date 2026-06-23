<?php
session_start();

$redirect = (isset($_SESSION['access']) && $_SESSION['access'] === 'admin') ? '/shoepee/admin/auth.admin.php' : '/shoepee/auth/signin.php';

session_unset();
session_destroy();

header("Location: $redirect");
exit();
?>
