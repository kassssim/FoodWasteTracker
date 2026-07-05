<?php
session_start();

$host = "localhost";
$user = "root";       // default XAMPP/phpMyAdmin username
$pass = "";            // default is empty, change if yours is different
$dbname = "food_waste_tracker";

$conn = mysqli_connect($host, $user, $pass, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>