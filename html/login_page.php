<!DOCTYPE html>
<html lang="en">
<head>
    <style></style>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="./style.css">
    <title>Log in</title>
</head>
<body>
    <nav><a href="./index.php">Home</a></nav>
    <div class="content">
        <form action="./login.php" method="post">
            <input type="password" name="password" placeholder="Password" required><br>
            <input type="submit" value="Enter">
        </form>
        <?php
        if (isset($_GET['error'])) {
            echo '<p class="error">Invalid password!</p>';
        }
        ?>
    </div>
</body>
</html>