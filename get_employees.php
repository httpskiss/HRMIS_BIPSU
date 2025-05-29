<?php
session_start();
require 'auth/db.php';

// Validate and sanitize input
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = isset($_GET['per_page']) ? max(1, (int)$_GET['per_page']) : 5;
$offset = ($page - 1) * $perPage;

try {
    // Get total count
    $total = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    
    // Get paginated results - using question mark placeholders for better compatibility
    $stmt = $pdo->prepare("
        SELECT id, first_name, last_name, email, employee_id, department, role, category 
        FROM users 
        ORDER BY created_at DESC 
        LIMIT ?, ?
    ");
    $stmt->bindValue(1, $offset, PDO::PARAM_INT);
    $stmt->bindValue(2, $perPage, PDO::PARAM_INT);
    $stmt->execute();
    
    $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'employees' => $employees,
        'total' => (int)$total,
        'page' => $page,
        'per_page' => $perPage
    ]);
} catch (PDOException $e) {
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}