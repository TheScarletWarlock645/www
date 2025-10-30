<?php
$host = 'localhost';
$db = "guitar_twenty_five";
$user = "gtrtech";
$pass = "guitar4ever!";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
	die("connection failed: " . mysqli_connect_error());
}
?>
