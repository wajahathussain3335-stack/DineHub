<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "dinehub";

$conn = mysqli_connect($host, $user, $pass, $dbname);

if (!$conn) {
    die("Database se rabta toot gaya: " . mysqli_connect_error());
}

// Session shuru karne ke liye taake login state save rahe
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>