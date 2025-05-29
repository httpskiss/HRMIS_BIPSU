<?php
$host = 'localhost';
$db = 'hrmis_db';
$user = 'root'; // default for XAMPP
$pass = '';     // default for XAMPP

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>


