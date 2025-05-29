<?php
require 'db.php';

$firstName = $_POST['firstName'] ?? '';
$lastName = $_POST['lastName'] ?? '';
$email = $_POST['registerEmail'] ?? '';
$password = $_POST['registerPassword'] ?? '';
$employeeId = $_POST['employeeId'] ?? '';
$department = $_POST['department'] ?? '';
$role = $_POST['role'] ?? 'employee';

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo "Invalid email format.";
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        echo "Email already registered.";
        exit;
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, email, password, employee_id, department, role) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$firstName, $lastName, $email, $hashedPassword, $employeeId, $department, $role]);

    echo "success";
} catch (Exception $e) {
    echo "Registration error: " . $e->getMessage();
}
?>
