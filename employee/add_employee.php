<?php
session_start();
require 'auth/db.php';

$data = $_POST;

try {
    $stmt = $pdo->prepare("
        INSERT INTO users 
        (first_name, last_name, email, password, employee_id, department, role, category) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $success = $stmt->execute([
        $data['first_name'],
        $data['last_name'],
        $data['email'],
        password_hash($data['password'], PASSWORD_DEFAULT),
        $data['employee_id'],
        $data['department'],
        $data['role'],
        $data['category']
    ]);
    
    echo json_encode(['success' => $success]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}