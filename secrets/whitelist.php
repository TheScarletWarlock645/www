<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$file_path = '/var/www/secrets/whitelist.json';

// Load existing data from JSON file
if (file_exists($file_path)) {
    $amp_cert = json_decode(file_get_contents($file_path), true);
    // If JSON is invalid or empty, use default
    if (!is_array($amp_cert)) {
        $amp_cert = ["1062993", "1012809", "1015769", "1008756", "1011516", "992817", "1035007"];
    }
} else {
    // Initialize with default values if file doesn't exist
    $amp_cert = ["1062993", "1012809", "1015769", "1008756", "1011516", "992817", "1035007"];
    // Create the file with initial data
    file_put_contents($file_path, json_encode($amp_cert, JSON_PRETTY_PRINT));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['new_whitelist_id']) && strtolower($_POST['submit']) === "add") {
        $new_id = $_POST['new_whitelist_id'];

        // Check if ID already exists before adding
        if (!in_array($new_id, $amp_cert)) {
            array_push($amp_cert, $new_id);
            // Save to file
            file_put_contents($file_path, json_encode($amp_cert, JSON_PRETTY_PRINT));
            header("Location: http://localhost/entries.php?success=1");
        } else {
            header("Location: http://localhost/entries.php?error=3"); // ID already exists
            exit;
        }

    } elseif (!empty($_POST['new_whitelist_id']) && strtolower($_POST['submit']) === "remove") {
        $remove_id = $_POST['new_whitelist_id'];
        $key = array_search($remove_id, $amp_cert);
        if ($key !== false) {
            unset($amp_cert[$key]);
            $amp_cert = array_values($amp_cert);
            // Save to file
            file_put_contents($file_path, json_encode($amp_cert, JSON_PRETTY_PRINT));
            header("Location: http://localhost/entries.php?success=2");
        } else {
            header("Location: http://localhost/entries.php?error=2");
            exit;
        }
    } else {
        header("Location: http://localhost/entries.php?error=1");
        exit;
    }
}
?>
