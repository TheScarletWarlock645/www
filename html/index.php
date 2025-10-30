<?php
// Include the password file
require_once '../secrets/password.php';

// Check if whitelist parameter is set
$requirePassword = isset($_GET['whitelist']);
$showSuccess = isset($_GET['success']);

// Handle form submission
$errorMessage = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $isValid = true;
    
    // Check password only if whitelist is set
    if ($requirePassword) {
        $enteredPassword = $_POST['admin_password'] ?? '';
        
        if ($enteredPassword !== $admin_password) {
            $isValid = false;
            $errorMessage = "Incorrect password. Please try again.";
        }
    }
    
    // If validation passed, forward to checkout.php with form data
    if ($isValid) {
        // Forward the POST data to checkout.php
        $_POST['validated'] = 'true';
        include('checkout.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="./style.css">
    <title>Guitar Tech Sign-Out</title>
</head>
<body>
    <nav><a href="login_page.php">Search Entries</a></nav>
    <header>
        <h1>Guitar Tech Sign-Out</h1>
    </header>
    <div class="content">
        <?php if ($showSuccess): ?>
            <p class="success">Successfully submitted!</p>
        <?php endif; ?>
        
        <?php if ($errorMessage): ?>
            <p class="error"><?php echo htmlspecialchars($errorMessage); ?></p>
        <?php endif; ?>
        
        <?php if ($requirePassword): ?>
            <p class="error">You are not amp certified!<br> Please have Mr. King or Guitar Tech enter a password to continue</p>
        <?php endif; ?>
        
        <form action="checkout.php" method="post">
            <input type="radio" id="checkout" name="status" value="gear_checkout" required>
            <label for="checkout">Gear Checkout</label><br>

            <input type="radio" id="return" name="status" value="gear_return" required>
            <label for="return">Gear Return</label><br>
            
            <input type="text" name="stu_id" placeholder="Student ID" required><br>
            <input type="text" name="equipment" placeholder="Equipment you are taking" required><br>
            
            <?php if ($requirePassword): ?>
                <input type="password" name="admin_password" placeholder="Admin Password" required><br>
            <?php endif; ?>
            
            <input type="submit" value="Checkout">
        </form>
        
        <footer>Guitar tech: Angelo Semertsidis, 1062993@apsva.us</footer>
    </div>
</body>
</html>