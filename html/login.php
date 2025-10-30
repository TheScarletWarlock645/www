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
    header("Location: http://100.119.133.29/entries.php");
    exit();
} else {
    header("Location: http://100.119.133.29/login_page.php?error=1");
}

?>