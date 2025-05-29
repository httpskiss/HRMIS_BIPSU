<?php
session_start();
require 'auth/db.php';

$school = $_GET['school'] ?? 'all';

if ($school === 'all') {
    $query = "SELECT department, COUNT(*) as count FROM users GROUP BY department";
} else {
    $query = "SELECT department, COUNT(*) as count FROM users WHERE department = :school GROUP BY department";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['school' => $school]);
}

$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

$labels = [];
$data = [];
foreach ($results as $row) {
    $labels[] = $row['department'];
    $data[] = $row['count'];
}

header('Content-Type: application/json');
echo json_encode([
    'labels' => $labels,
    'data' => $data
]);
