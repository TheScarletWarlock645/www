<?php
// Check if whitelist parameter is set
$requirePassword = isset($_GET['whitelist']);

// Define your password (in production, use environment variables or database)
$correctPassword = "your_secure_password_here";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $isValid = true;
    $errorMessage = "";
    
    // Check password only if whitelist is set
    if ($requirePassword) {
        $enteredPassword = $_POST['password'] ?? '';
        
        if ($enteredPassword !== $correctPassword) {
            $isValid = false;
            $errorMessage = "Incorrect password. Please try again.";
        }
    }
    
    // Process form if validation passed
    if ($isValid) {
        // Your form processing logic here
        $name = htmlspecialchars($_POST['name'] ?? '');
        $email = htmlspecialchars($_POST['email'] ?? '');
        
        echo "<div style='padding: 20px; background: #d4edda; border: 1px solid #c3e6cb; color: #155724; border-radius: 5px; margin: 20px;'>
                <h2>Form Submitted Successfully!</h2>
                <p>Name: {$name}</p>
                <p>Email: {$email}</p>
              </div>";
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Conditional Password Form</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 500px;
            margin: 50px auto;
            padding: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        button {
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background-color: #0056b3;
        }
        .error {
            color: #dc3545;
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
        }
        .password-required {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
            color: #856404;
        }
    </style>
</head>
<body>
    <h1>Contact Form</h1>
    
    <?php if ($requirePassword): ?>
        <div class="password-required">
            ⚠️ Password required to submit this form
        </div>
    <?php endif; ?>
    
    <?php if (isset($errorMessage) && $errorMessage): ?>
        <div class="error">
            <?php echo htmlspecialchars($errorMessage); ?>
        </div>
    <?php endif; ?>
    
    <form method="POST" action="">
        <div class="form-group">
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" required>
        </div>
        
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
        </div>
        
        <?php if ($requirePassword): ?>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
        <?php endif; ?>
        
        <button type="submit">Submit</button>
    </form>
    
    <p style="margin-top: 30px; font-size: 14px; color: #666;">
        <strong>Test the form:</strong><br>
        • Without password: <a href="?">Regular form</a><br>
        • With password: <a href="?whitelist">Password protected</a>
    </p>
</body>
</html>