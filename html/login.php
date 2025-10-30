<?php

session_start();

if (isset($_POST['password'])) {
    $password = $_POST['password'];
    echo "Password received: " . htmlspecialchars($password) . "<br>";
} else {"No password in POST data<br>";
}

include('../secrets/password.php');
if ($password == $admin_password) {
    $_SESSION['logged_in'] = TRUE;
    header("Location: http://localhost/entries.php");
    exit();
} else {
    header("Location: http://localhost/login_page.php?error=1");
}

?>
