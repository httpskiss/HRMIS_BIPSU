<?php
session_start();
require 'auth/db.php';

$email = $_POST['loginEmail'] ?? '';
$password = $_POST['loginPassword'] ?? '';

try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        echo "success";
    } else {
        echo "Invalid credentials.";
    }
} catch (Exception $e) {
    echo "Login error: " . $e->getMessage();
}
?>
