<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include('../secrets/db_conn.php');
include('../secrets/whitelist_data.php');
date_default_timezone_set('America/New_York');

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stu_id = mysqli_real_escape_string($conn, $_POST['stu_id']);
    $equipment = mysqli_real_escape_string($conn, $_POST['equipment']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $date_time = date('d-m-Y H:i:s');

    // Check if student is whitelisted
    if (!in_array($stu_id, $amp_cert)) {
        mysqli_close($conn);
        header("Location: http://localhost/index.php?whitelist");
        exit();
    }

    // Validate that status is one of the expected table names
    if ($status !== 'gear_checkout' && $status !== 'gear_return') {
        mysqli_close($conn);
        header("Location: http://localhost/index.php?error=invalid_status");
        exit();
    }

    // Insert into database
    $insert_query = "INSERT INTO $status (student_id, equipment, date_time) VALUES ('$stu_id', '$equipment', '$date_time')";
    $sql_result = mysqli_query($conn, $insert_query);

    mysqli_close($conn);
    header("Location: http://localhost/index.php?success=1");
    exit();
} else {
    // If accessed directly without POST, redirect to index
    header("Location: http://localhost/index.php");
    exit();
}

?>
