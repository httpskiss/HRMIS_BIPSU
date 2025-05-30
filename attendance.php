<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit;
}

require 'auth/db.php';
require 'models/Attendance.php';

$attendanceModel = new Attendance();

// Get current date and month info
$currentDate = date('Y-m-d');
$month = date('m');
$year = date('Y');

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'mark':
                $result = $attendanceModel->markAttendance(
                    $_POST['user_id'],
                    $_POST['date'],
                    $_POST['status'],
                    $_POST['check_in'],
                    $_POST['check_out'],
                    $_POST['notes'] ?? null
                );
                break;
            case 'update':
                $result = $attendanceModel->updateAttendance(
                    $_POST['id'],
                    $_POST['status'],
                    $_POST['check_in'],
                    $_POST['check_out'],
                    $_POST['notes'] ?? null
                );
                break;
            case 'delete':
                $result = $attendanceModel->deleteAttendance($_POST['id']);
                break;
        }
        
        if ($result) {
            $_SESSION['message'] = 'Attendance record updated successfully';
            header("Location: attendance.php");
            exit;
        }
    }
}

// Get data for display
$dailyAttendance = $attendanceModel->getDailyAttendance($currentDate);
$monthlySummary = $attendanceModel->getMonthlySummary($month, $year);

// Get all employees for dropdown
$employees = $pdo->query("SELECT id, CONCAT(first_name, ' ', last_name) as name, employee_id FROM users ORDER BY first_name")->fetchAll();

// Calculate stats for cards
$totalEmployees = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$presentToday = $pdo->query("SELECT COUNT(*) FROM attendance WHERE date = '$currentDate' AND status = 'Present'")->fetchColumn();
$absentToday = $pdo->query("SELECT COUNT(*) FROM attendance WHERE date = '$currentDate' AND status = 'Absent'")->fetchColumn();
$onLeaveToday = $pdo->query("SELECT COUNT(*) FROM attendance WHERE date = '$currentDate' AND status = 'On Leave'")->fetchColumn();
$lateToday = $pdo->query("SELECT COUNT(*) FROM attendance WHERE date = '$currentDate' AND status = 'Late'")->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HRMIS - Attendance</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .sidebar {
            transition: all 0.3s ease;
        }
        .sidebar.collapsed {
            width: 70px;
        }
        .sidebar.collapsed .nav-text {
            display: none;
        }
        .sidebar.collapsed .logo-text {
            display: none;
        }
        .sidebar.collapsed ~ .main-content {
            margin-left: 70px;
        }
        .main-content {
            transition: all 0.3s ease;
            margin-left: 250px;
        }
        .chart-container {
            position: relative;
            height: 300px;
        }
        .attendance-badge {
            font-size: 10px;
            padding: 3px 6px;
        }
        .dropdown:hover .dropdown-menu {
            display: block;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
        .modal {
            transition: opacity 0.3s ease;
        }
        .attendance-calendar .day {
            position: relative;
        }
        .attendance-calendar .day .status {
            position: absolute;
            bottom: 2px;
            right: 2px;
            width: 6px;
            height: 6px;
            border-radius: 50%;
        }
        .attendance-calendar .day.present .status {
            background-color: #10B981;
        }
        .attendance-calendar .day.absent .status {
            background-color: #EF4444;
        }
        .attendance-calendar .day.leave .status {
            background-color: #F59E0B;
        }
        .attendance-calendar .day.holiday .status {
            background-color: #6366F1;
        }
        .attendance-calendar .day.today {
            background-color: #EFF6FF;
            border: 1px solid #3B82F6;
        }
        .sidebar {
            scrollbar-width: thin;
            scrollbar-color: transparent transparent;
            transition: scrollbar-color 0.3s ease;
        }
        .sidebar:hover {
            scrollbar-color: rgba(255,255,255,0.3) transparent;
        }
        .sidebar::-webkit-scrollbar {
            width: 6px;
            background: transparent;
        }
        .sidebar::-webkit-scrollbar-thumb {
            background: transparent;
            border-radius: 3px;
        }
        .sidebar:hover::-webkit-scrollbar-thumb {
            background: rgba(255,255,255,0.3);
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar (unchanged from your original) -->
        <div class="sidebar bg-blue-800 text-white w-64 fixed h-full overflow-y-auto">
            <div class="p-4 flex items-center space-x-3">
                <img src="assets/images/uni_logo.png" alt="Uni Logo" class="h-auto max-h-12 w-auto max-w-full object-contain">
                <span class="logo-text text-xl font-bold">BiPSU HRMIS</span>
            </div>
            <nav class="mt-6">
                <div class="px-4 py-2">
                    <a href="index.php" class="flex items-center px-4 py-3 rounded-lg hover:bg-blue-700">
                        <i class="fas fa-tachometer-alt mr-3"></i>
                        <span class="nav-text">Dashboard</span>
                    </a>
                </div>
                <div class="px-4 py-2">
                    <a href="employees.php" class="flex items-center px-4 py-3 rounded-lg hover:bg-blue-700">
                        <i class="fas fa-users mr-3"></i>
                        <span class="nav-text">Employees</span>
                    </a>
                </div>
                <div class="px-4 py-2">
                    <a href="attendance.php" class="flex items-center px-4 py-3 rounded-lg hover:bg-blue-700">
                        <i class="fas fa-calendar-alt mr-3"></i>
                        <span class="nav-text">Attendance</span>
                    </a>
                </div>
                <div class="px-4 py-2">
                    <a href="payroll.php" class="flex items-center px-4 py-3 rounded-lg hover:bg-blue-700">
                        <i class="fas fa-money-bill-wave mr-3"></i>
                        <span class="nav-text">Payroll</span>
                    </a>
                </div>
                <div class="px-4 py-2">
                    <a href="leave.php" class="flex items-center px-4 py-3 rounded-lg hover:bg-blue-700">
                        <i class="fas fa-calendar-minus mr-3"></i>
                        <span class="nav-text">Leave</span>
                    </a>
                </div>
                <div class="px-4 py-2">
                    <a href="travel.php" class="flex items-center px-4 py-3 rounded-lg hover:bg-blue-700">
                        <i class="fas fa-plane mr-3"></i>
                        <span class="nav-text">Travel</span>
                    </a>
                </div>
                <div class="px-4 py-2">
                    <a href="reports.php" class="flex items-center px-4 py-3 rounded-lg hover:bg-blue-700">
                        <i class="fas fa-chart-line mr-3"></i>
                        <span class="nav-text">Reports</span>
                    </a>
                </div>
                <div class="px-4 py-2">
                    <a href="settings.php" class="flex items-center px-4 py-3 rounded-lg hover:bg-blue-700">
                        <i class="fas fa-cog mr-3"></i>
                        <span class="nav-text">Settings</span>
                    </a>
                </div>
            </nav>
            <div class="p-4">
                <button id="toggleSidebar" class="w-full flex items-center justify-center py-2 bg-blue-700 rounded-lg">
                    <i class="fas fa-chevron-left"></i>
                    <span class="nav-text ml-2">Collapse</span>
                </button>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="main-content flex-1 overflow-auto">
            <!-- Top Navigation -->
            <header class="bg-white shadow-sm">
                <div class="flex justify-between items-center p-4">
                    <h1 class="text-2xl font-semibold text-gray-800">Attendance Management</h1>
                    <div class="flex items-center space-x-4">
                        <div class="relative">
                            <i class="fas fa-bell text-gray-600 text-xl cursor-pointer"></i>
                            <span class="absolute -top-1 -right-1 bg-red-500 text-white rounded-full w-4 h-4 flex items-center justify-center text-xs">3</span>
                        </div>
                        <div class="dropdown relative">
                            <div class="flex items-center cursor-pointer">
                                <img src="https://randomuser.me/api/portraits/women/44.jpg" alt="Profile" class="w-8 h-8 rounded-full">
                                <span class="ml-2 text-gray-700">Admin</span>
                                <i class="fas fa-chevron-down ml-1 text-gray-600 text-xs"></i>
                            </div>
                            <div class="dropdown-menu absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50 hidden">
                                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Profile</a>
                                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Settings</a>
                                <div class="border-t border-gray-200"></div>
                                <a href="logout.php" id="logoutBtn" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Logout</a>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Attendance Content -->
            <main class="p-6">
                <?php if (isset($_SESSION['message'])): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
                        <span class="block sm:inline"><?= $_SESSION['message'] ?></span>
                        <span class="absolute top-0 bottom-0 right-0 px-4 py-3">
                            <button class="close-message" aria-label="Close">
                                <i class="fas fa-times"></i>
                            </button>
                        </span>
                    </div>
                    <?php unset($_SESSION['message']); ?>
                <?php endif; ?>

                <!-- Filters and Actions -->
                <div class="bg-white rounded-lg shadow p-6 mb-6">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div class="flex items-center space-x-4">
                            <div class="relative">
                                <select class="appearance-none bg-white border border-gray-300 rounded-md pl-3 pr-8 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
                                    <option>All Departments</option>
                                    <?php 
                                    $departments = $pdo->query("SELECT DISTINCT department FROM users")->fetchAll(PDO::FETCH_COLUMN);
                                    foreach($departments as $dept): ?>
                                        <option><?= htmlspecialchars($dept) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                                    <i class="fas fa-chevron-down text-xs"></i>
                                </div>
                            </div>
                            <div class="relative">
                                <select class="appearance-none bg-white border border-gray-300 rounded-md pl-3 pr-8 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
                                    <option>All Status</option>
                                    <option>Present</option>
                                    <option>Absent</option>
                                    <option>On Leave</option>
                                    <option>Late</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                                    <i class="fas fa-chevron-down text-xs"></i>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center space-x-3">
                            <div class="relative">
                                <input type="text" placeholder="Search employees..." class="pl-10 pr-4 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
                                <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                            </div>
                            <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded flex items-center text-sm">
                                <i class="fas fa-filter mr-2"></i> Filter
                            </button>
                            <button id="markAttendanceBtn" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded flex items-center text-sm">
                                <i class="fas fa-calendar-check mr-2"></i> Mark Attendance
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-blue-100 text-blue-600 mr-4">
                                <i class="fas fa-calendar-day text-xl"></i>
                            </div>
                            <div>
                                <p class="text-gray-500">Today's Attendance</p>
                                <h3 class="text-2xl font-bold"><?= $presentToday ?>/<?= $totalEmployees ?></h3>
                                <p class="text-green-500 text-sm"><?= round(($presentToday/$totalEmployees)*100) ?>% present</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-green-100 text-green-600 mr-4">
                                <i class="fas fa-user-check text-xl"></i>
                            </div>
                            <div>
                                <p class="text-gray-500">Present Today</p>
                                <h3 class="text-2xl font-bold"><?= $presentToday ?></h3>
                                <p class="text-red-500 text-sm"><?= $absentToday ?> absent</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-purple-100 text-purple-600 mr-4">
                                <i class="fas fa-bed text-xl"></i>
                            </div>
                            <div>
                                <p class="text-gray-500">On Leave</p>
                                <h3 class="text-2xl font-bold"><?= $onLeaveToday ?></h3>
                                <p class="text-yellow-500 text-sm"><?= round(($onLeaveToday/$totalEmployees)*100) ?>% of staff</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-yellow-100 text-yellow-600 mr-4">
                                <i class="fas fa-clock text-xl"></i>
                            </div>
                            <div>
                                <p class="text-gray-500">Late Arrivals</p>
                                <h3 class="text-2xl font-bold"><?= $lateToday ?></h3>
                                <p class="text-red-500 text-sm"><?= $presentToday > 0 ? round(($lateToday/$presentToday)*100) : 0 ?>% of present</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabs -->
                <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
                    <div class="border-b border-gray-200">
                        <nav class="flex -mb-px">
                            <button data-tab="daily" class="tab-btn py-4 px-6 text-center border-b-2 font-medium text-sm border-blue-500 text-blue-600">
                                <i class="fas fa-calendar-day mr-2"></i> Daily Attendance
                            </button>
                            <button data-tab="monthly" class="tab-btn py-4 px-6 text-center border-b-2 font-medium text-sm border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                                <i class="fas fa-calendar-alt mr-2"></i> Monthly Summary
                            </button>
                            <button data-tab="reports" class="tab-btn py-4 px-6 text-center border-b-2 font-medium text-sm border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                                <i class="fas fa-chart-pie mr-2"></i> Reports
                            </button>
                            <button data-tab="settings" class="tab-btn py-4 px-6 text-center border-b-2 font-medium text-sm border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                                <i class="fas fa-cog mr-2"></i> Settings
                            </button>
                        </nav>
                    </div>
                    
                    <!-- Daily Attendance Tab -->
                    <div id="daily" class="tab-content active p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-lg font-semibold">Today's Attendance - <?= date('F j, Y') ?></h2>
                            <div class="flex items-center space-x-2">
                                <button class="px-3 py-1 border border-gray-300 rounded-md text-sm bg-white hover:bg-gray-50">
                                    <i class="fas fa-print mr-1"></i> Print
                                </button>
                                <button class="px-3 py-1 border border-gray-300 rounded-md text-sm bg-white hover:bg-gray-50">
                                    <i class="fas fa-file-export mr-1"></i> Export
                                </button>
                            </div>
                        </div>
                        
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee ID</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Check-in</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Check-out</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php foreach($dailyAttendance as $record): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= 'EMP-'.$record['user_id'] ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10">
                                                    <img class="h-10 w-10 rounded-full" src="https://ui-avatars.com/api/?name=<?= urlencode($record['first_name'].'+'.$record['last_name']) ?>" alt="">
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900"><?= $record['first_name'].' '.$record['last_name'] ?></div>
                                                    <div class="text-sm text-gray-500"><?= $record['department'] ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= $record['check_in'] ? date('h:i A', strtotime($record['check_in'])) : '-' ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= $record['check_out'] ? date('h:i A', strtotime($record['check_out'])) : '-' ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php 
                                            $statusClasses = [
                                                'Present' => 'bg-green-100 text-green-800',
                                                'Absent' => 'bg-red-100 text-red-800',
                                                'On Leave' => 'bg-yellow-100 text-yellow-800',
                                                'Late' => 'bg-blue-100 text-blue-800',
                                                'Half Day' => 'bg-purple-100 text-purple-800'
                                            ];
                                            ?>
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $statusClasses[$record['status']] ?>">
                                                <?= $record['status'] ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <button class="text-blue-600 hover:text-blue-900 mr-3 edit-btn" 
                                                    data-id="<?= $record['id'] ?>"
                                                    data-status="<?= $record['status'] ?>"
                                                    data-checkin="<?= $record['check_in'] ?>"
                                                    data-checkout="<?= $record['check_out'] ?>"
                                                    data-notes="<?= htmlspecialchars($record['notes'] ?? '') ?>">
                                                Edit
                                            </button>
                                            <button class="text-red-600 hover:text-red-900 delete-btn" data-id="<?= $record['id'] ?>">Delete</button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="bg-gray-50 px-6 py-3 flex items-center justify-between border-t border-gray-200">
                            <div class="flex-1 flex justify-between sm:hidden">
                                <a href="#" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">Previous</a>
                                <a href="#" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">Next</a>
                            </div>
                            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                                <div>
                                    <p class="text-sm text-gray-700">
                                        Showing <span class="font-medium">1</span> to <span class="font-medium"><?= count($dailyAttendance) ?></span> of <span class="font-medium"><?= $totalEmployees ?></span> records
                                    </p>
                                </div>
                                <div>
                                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                                        <a href="#" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                            <span class="sr-only">Previous</span>
                                            <i class="fas fa-chevron-left"></i>
                                        </a>
                                        <a href="#" aria-current="page" class="z-10 bg-blue-50 border-blue-500 text-blue-600 relative inline-flex items-center px-4 py-2 border text-sm font-medium">1</a>
                                        <a href="#" class="bg-white border-gray-300 text-gray-500 hover:bg-gray-50 relative inline-flex items-center px-4 py-2 border text-sm font-medium">2</a>
                                        <a href="#" class="bg-white border-gray-300 text-gray-500 hover:bg-gray-50 relative inline-flex items-center px-4 py-2 border text-sm font-medium">3</a>
                                        <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">...</span>
                                        <a href="#" class="bg-white border-gray-300 text-gray-500 hover:bg-gray-50 relative inline-flex items-center px-4 py-2 border text-sm font-medium">8</a>
                                        <a href="#" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                            <span class="sr-only">Next</span>
                                            <i class="fas fa-chevron-right"></i>
                                        </a>
                                    </nav>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Monthly Summary Tab -->
                    <div id="monthly" class="tab-content p-6">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
                            <h2 class="text-lg font-semibold">Monthly Attendance Summary - <?= date('F Y') ?></h2>
                            <div class="flex items-center space-x-4 mt-4 md:mt-0">
                                <div class="flex items-center">
                                    <button id="prevMonth" class="p-2 rounded-full hover:bg-gray-100">
                                        <i class="fas fa-chevron-left"></i>
                                    </button>
                                    <span class="mx-2 text-gray-700"><?= date('F Y') ?></span>
                                    <button id="nextMonth" class="p-2 rounded-full hover:bg-gray-100">
                                        <i class="fas fa-chevron-right"></i>
                                    </button>
                                </div>
                                <button class="px-3 py-1 border border-gray-300 rounded-md text-sm bg-white hover:bg-gray-50">
                                    <i class="fas fa-file-export mr-1"></i> Export Report
                                </button>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                            <div class="bg-white rounded-lg shadow p-6 lg:col-span-2">
                                <div class="flex justify-between items-center mb-4">
                                    <h3 class="text-md font-medium">Attendance Overview</h3>
                                    <select class="border rounded px-3 py-1 text-sm">
                                        <option>All Departments</option>
                                        <?php foreach($departments as $dept): ?>
                                            <option><?= htmlspecialchars($dept) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="chart-container">
                                    <canvas id="attendanceTrendChart"></canvas>
                                </div>
                            </div>
                            
                            <div class="bg-white rounded-lg shadow p-6">
                                <h3 class="text-md font-medium mb-4">Attendance Statistics</h3>
                                <?php
                                $totalDays = count($monthlySummary);
                                $totalPresent = array_sum(array_column($monthlySummary, 'present'));
                                $totalAbsent = array_sum(array_column($monthlySummary, 'absent'));
                                $totalLeave = array_sum(array_column($monthlySummary, 'on_leave'));
                                $totalLate = array_sum(array_column($monthlySummary, 'late'));
                                $totalHalfDay = array_sum(array_column($monthlySummary, 'half_day'));
                                
                                $presentPercentage = $totalDays > 0 ? round(($totalPresent / ($totalDays * $totalEmployees)) * 100, 1) : 0;
                                $absentPercentage = $totalDays > 0 ? round(($totalAbsent / ($totalDays * $totalEmployees)) * 100, 1) : 0;
                                $leavePercentage = $totalDays > 0 ? round(($totalLeave / ($totalDays * $totalEmployees)) * 100, 1) : 0;
                                $latePercentage = $totalDays > 0 ? round(($totalLate / ($totalDays * $totalEmployees)) * 100, 1) : 0;
                                ?>
                                <div class="space-y-4">
                                    <div>
                                        <div class="flex justify-between mb-1">
                                            <span class="text-sm font-medium text-gray-700">Present</span>
                                            <span class="text-sm font-medium"><?= $presentPercentage ?>%</span>
                                        </div>
                                        <div class="w-full bg-gray-200 rounded-full h-2">
                                            <div class="bg-green-600 h-2 rounded-full" style="width: <?= $presentPercentage ?>%"></div>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="flex justify-between mb-1">
                                            <span class="text-sm font-medium text-gray-700">Absent</span>
                                            <span class="text-sm font-medium"><?= $absentPercentage ?>%</span>
                                        </div>
                                        <div class="w-full bg-gray-200 rounded-full h-2">
                                            <div class="bg-red-600 h-2 rounded-full" style="width: <?= $absentPercentage ?>%"></div>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="flex justify-between mb-1">
                                            <span class="text-sm font-medium text-gray-700">On Leave</span>
                                            <span class="text-sm font-medium"><?= $leavePercentage ?>%</span>
                                        </div>
                                        <div class="w-full bg-gray-200 rounded-full h-2">
                                            <div class="bg-yellow-500 h-2 rounded-full" style="width: <?= $leavePercentage ?>%"></div>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="flex justify-between mb-1">
                                            <span class="text-sm font-medium text-gray-700">Late Arrivals</span>
                                            <span class="text-sm font-medium"><?= $latePercentage ?>%</span>
                                        </div>
                                        <div class="w-full bg-gray-200 rounded-full h-2">
                                            <div class="bg-purple-600 h-2 rounded-full" style="width: <?= $latePercentage ?>%"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Rest of the monthly tab content remains similar but uses $monthlySummary data -->
                        <!-- Department-wise table and calendar would go here -->
                         <div class="bg-white rounded-lg shadow p-6 mb-6">
                            <h3 class="text-md font-medium mb-4">Department-wise Attendance</h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employees</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Present</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Absent</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">On Leave</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Attendance %</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Computer Science</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">185</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">160</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">20</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">5</td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="text-sm font-medium">86.5%</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Mathematics</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">132</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">120</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">8</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">4</td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="text-sm font-medium">90.9%</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Physics</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">98</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">75</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">20</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">3</td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="text-sm font-medium">76.5%</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Chemistry</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">76</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">70</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">4</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">2</td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="text-sm font-medium">92.1%</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Administration</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">342</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">320</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">15</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">7</td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="text-sm font-medium">93.6%</span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                          <div class="bg-white rounded-lg shadow p-6">
                            <h3 class="text-md font-medium mb-4">Monthly Calendar</h3>
                            <div class="attendance-calendar">
                                <div class="grid grid-cols-7 gap-1 mb-2">
                                    <div class="text-center text-xs font-medium text-gray-500 py-1">Sun</div>
                                    <div class="text-center text-xs font-medium text-gray-500 py-1">Mon</div>
                                    <div class="text-center text-xs font-medium text-gray-500 py-1">Tue</div>
                                    <div class="text-center text-xs font-medium text-gray-500 py-1">Wed</div>
                                    <div class="text-center text-xs font-medium text-gray-500 py-1">Thu</div>
                                    <div class="text-center text-xs font-medium text-gray-500 py-1">Fri</div>
                                    <div class="text-center text-xs font-medium text-gray-500 py-1">Sat</div>
                                </div>
                                <div class="grid grid-cols-7 gap-1">
                                    <div class="day h-12 border rounded flex items-center justify-center text-sm">28</div>
                                    <div class="day h-12 border rounded flex items-center justify-center text-sm">29</div>
                                    <div class="day h-12 border rounded flex items-center justify-center text-sm">30</div>
                                    <div class="day h-12 border rounded flex items-center justify-center text-sm">31</div>
                                    <div class="day present h-12 border rounded flex items-center justify-center text-sm">1</div>
                                    <div class="day present h-12 border rounded flex items-center justify-center text-sm">2</div>
                                    <div class="day holiday h-12 border rounded flex items-center justify-center text-sm">3</div>
                                    <div class="day holiday h-12 border rounded flex items-center justify-center text-sm">4</div>
                                    <div class="day present h-12 border rounded flex items-center justify-center text-sm">5</div>
                                    <div class="day present h-12 border rounded flex items-center justify-center text-sm">6</div>
                                    <div class="day present h-12 border rounded flex items-center justify-center text-sm">7</div>
                                    <div class="day present h-12 border rounded flex items-center justify-center text-sm">8</div>
                                    <div class="day present h-12 border rounded flex items-center justify-center text-sm">9</div>
                                    <div class="day holiday h-12 border rounded flex items-center justify-center text-sm">10</div>
                                    <div class="day present h-12 border rounded flex items-center justify-center text-sm">11</div>
                                    <div class="day present h-12 border rounded flex items-center justify-center text-sm">12</div>
                                    <div class="day present h-12 border rounded flex items-center justify-center text-sm">13</div>
                                    <div class="day present h-12 border rounded flex items-center justify-center text-sm">14</div>
                                    <div class="day today present h-12 border rounded flex items-center justify-center text-sm font-medium text-blue-700">15</div>
                                    <div class="day h-12 border rounded flex items-center justify-center text-sm">16</div>
                                    <div class="day holiday h-12 border rounded flex items-center justify-center text-sm">17</div>
                                    <div class="day h-12 border rounded flex items-center justify-center text-sm">18</div>
                                    <div class="day h-12 border rounded flex items-center justify-center text-sm">19</div>
                                    <div class="day h-12 border rounded flex items-center justify-center text-sm">20</div>
                                    <div class="day h-12 border rounded flex items-center justify-center text-sm">21</div>
                                    <div class="day h-12 border rounded flex items-center justify-center text-sm">22</div>
                                    <div class="day holiday h-12 border rounded flex items-center justify-center text-sm">23</div>
                                    <div class="day h-12 border rounded flex items-center justify-center text-sm">24</div>
                                    <div class="day leave h-12 border rounded flex items-center justify-center text-sm">25</div>
                                    <div class="day h-12 border rounded flex items-center justify-center text-sm">26</div>
                                    <div class="day h-12 border rounded flex items-center justify-center text-sm">27</div>
                                    <div class="day h-12 border rounded flex items-center justify-center text-sm">28</div>
                                    <div class="day h-12 border rounded flex items-center justify-center text-sm">29</div>
                                    <div class="day holiday h-12 border rounded flex items-center justify-center text-sm">30</div>
                                    <div class="day h-12 border rounded flex items-center justify-center text-sm">1</div>
                                    <div class="day h-12 border rounded flex items-center justify-center text-sm">2</div>
                                    <div class="day h-12 border rounded flex items-center justify-center text-sm">3</div>
                                    <div class="day h-12 border rounded flex items-center justify-center text-sm">4</div>
                                    <div class="day h-12 border rounded flex items-center justify-center text-sm">5</div>
                                    <div class="day h-12 border rounded flex items-center justify-center text-sm">6</div>
                                    <div class="day h-12 border rounded flex items-center justify-center text-sm">7</div>
                                </div>
                            </div>
                            <div class="mt-4 flex flex-wrap items-center justify-center gap-2">
                                <div class="flex items-center">
                                    <div class="w-3 h-3 rounded-full bg-green-500 mr-1"></div>
                                    <span class="text-xs">Present</span>
                                </div>
                                <div class="flex items-center">
                                    <div class="w-3 h-3 rounded-full bg-red-500 mr-1"></div>
                                    <span class="text-xs">Absent</span>
                                </div>
                                <div class="flex items-center">
                                    <div class="w-3 h-3 rounded-full bg-yellow-500 mr-1"></div>
                                    <span class="text-xs">On Leave</span>
                                </div>
                                <div class="flex items-center">
                                    <div class="w-3 h-3 rounded-full bg-indigo-500 mr-1"></div>
                                    <span class="text-xs">Holiday</span>
                                </div>
                                <div class="flex items-center">
                                    <div class="w-3 h-3 rounded-full bg-blue-100 border border-blue-500 mr-1"></div>
                                    <span class="text-xs">Today</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Reports and Settings tabs would follow the same pattern -->
                      <!-- Settings Tab -->
                    <div id="settings" class="tab-content p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-lg font-semibold">Attendance Settings</h2>
                            <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded flex items-center text-sm">
                                <i class="fas fa-save mr-2"></i> Save Settings
                            </button>
                        </div>
                        
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                            <div class="bg-white rounded-lg shadow p-6">
                                <h3 class="text-md font-medium mb-4">General Settings</h3>
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Work Week</label>
                                        <div class="flex flex-wrap gap-3">
                                            <label class="inline-flex items-center">
                                                <input type="checkbox" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" checked>
                                                <span class="ml-2 text-sm">Sun</span>
                                            </label>
                                            <label class="inline-flex items-center">
                                                <input type="checkbox" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" checked>
                                                <span class="ml-2 text-sm">Mon</span>
                                            </label>
                                            <label class="inline-flex items-center">
                                                <input type="checkbox" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" checked>
                                                <span class="ml-2 text-sm">Tue</span>
                                            </label>
                                            <label class="inline-flex items-center">
                                                <input type="checkbox" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" checked>
                                                <span class="ml-2 text-sm">Wed</span>
                                            </label>
                                            <label class="inline-flex items-center">
                                                <input type="checkbox" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" checked>
                                                <span class="ml-2 text-sm">Thu</span>
                                            </label>
                                            <label class="inline-flex items-center">
                                                <input type="checkbox" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                                <span class="ml-2 text-sm">Fri</span>
                                            </label>
                                            <label class="inline-flex items-center">
                                                <input type="checkbox" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                                <span class="ml-2 text-sm">Sat</span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Work Start Time</label>
                                            <input type="time" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500" value="08:00">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Work End Time</label>
                                            <input type="time" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500" value="17:00">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Late Arrival Threshold (minutes)</label>
                                        <input type="number" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500" value="15">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Half Day Threshold (hours)</label>
                                        <input type="number" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500" value="4">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="bg-white rounded-lg shadow p-6">
                                <h3 class="text-md font-medium mb-4">Holidays</h3>
                                <div class="mb-4">
                                    <div class="flex justify-between items-center mb-2">
                                        <h4 class="text-sm font-medium text-gray-700">Upcoming Holidays</h4>
                                        <button class="text-blue-600 hover:text-blue-800 text-sm flex items-center">
                                            <i class="fas fa-plus mr-1"></i> Add Holiday
                                        </button>
                                    </div>
                                    <div class="space-y-2">
                                        <div class="flex justify-between items-center p-2 border rounded-lg">
                                            <div>
                                                <p class="text-sm font-medium">Eid al-Adha</p>
                                                <p class="text-xs text-gray-500">June 28, 2023</p>
                                            </div>
                                            <button class="text-red-600 hover:text-red-800">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                        <div class="flex justify-between items-center p-2 border rounded-lg">
                                            <div>
                                                <p class="text-sm font-medium">Independence Day</p>
                                                <p class="text-xs text-gray-500">July 4, 2023</p>
                                            </div>
                                            <button class="text-red-600 hover:text-red-800">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                        <div class="flex justify-between items-center p-2 border rounded-lg">
                                            <div>
                                                <p class="text-sm font-medium">Labor Day</p>
                                                <p class="text-xs text-gray-500">September 5, 2023</p>
                                            </div>
                                            <button class="text-red-600 hover:text-red-800">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <h4 class="text-sm font-medium text-gray-700 mb-2">Import Holidays</h4>
                                    <div class="flex items-center space-x-2">
                                        <select class="border rounded px-3 py-1 text-sm">
                                            <option>Select Country</option>
                                            <option>United States</option>
                                            <option>United Kingdom</option>
                                            <option>Canada</option>
                                            <option>Australia</option>
                                        </select>
                                        <button class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-sm">
                                            Import
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-white rounded-lg shadow p-6">
                            <h3 class="text-md font-medium mb-4">Attendance Methods</h3>
                            <div class="space-y-4">
                                <div class="flex items-start">
                                    <div class="flex items-center h-5">
                                        <input id="biometric" name="biometric" type="checkbox" class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded" checked>
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label for="biometric" class="font-medium text-gray-700">Biometric Attendance</label>
                                        <p class="text-gray-500">Allow employees to check-in/out using fingerprint or facial recognition</p>
                                    </div>
                                </div>
                                <div class="flex items-start">
                                    <div class="flex items-center h-5">
                                        <input id="mobile" name="mobile" type="checkbox" class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded" checked>
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label for="mobile" class="font-medium text-gray-700">Mobile App Attendance</label>
                                        <p class="text-gray-500">Allow employees to check-in/out using the mobile application</p>
                                    </div>
                                </div>
                                <div class="flex items-start">
                                    <div class="flex items-center h-5">
                                        <input id="web" name="web" type="checkbox" class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded">
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label for="web" class="font-medium text-gray-700">Web Check-in</label>
                                        <p class="text-gray-500">Allow employees to check-in/out using the web portal</p>
                                    </div>
                                </div>
                                <div class="flex items-start">
                                    <div class="flex items-center h-5">
                                        <input id="geo" name="geo" type="checkbox" class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded" checked>
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label for="geo" class="font-medium text-gray-700">Geolocation Verification</label>
                                        <p class="text-gray-500">Require employees to be within campus boundaries when checking in/out</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                     
                    <!-- Reports Tab -->
                    <div id="reports" class="tab-content p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-lg font-semibold">Attendance Reports</h2>
                            <div class="flex items-center space-x-2">
                                <button class="px-3 py-1 border border-gray-300 rounded-md text-sm bg-white hover:bg-gray-50">
                                    <i class="fas fa-print mr-1"></i> Print
                                </button>
                                <button class="px-3 py-1 border border-gray-300 rounded-md text-sm bg-white hover:bg-gray-50">
                                    <i class="fas fa-file-export mr-1"></i> Export
                                </button>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                            <div class="bg-white rounded-lg shadow p-6">
                                <div class="flex justify-between items-center mb-4">
                                    <h3 class="text-md font-medium">Attendance by Department</h3>
                                    <select class="border rounded px-3 py-1 text-sm">
                                        <option>Last 30 Days</option>
                                        <option>This Month</option>
                                        <option>Last Month</option>
                                        <option>This Year</option>
                                    </select>
                                </div>
                                <div class="chart-container">
                                    <canvas id="departmentAttendanceChart"></canvas>
                                </div>
                            </div>
                            
                            <div class="bg-white rounded-lg shadow p-6">
                                <div class="flex justify-between items-center mb-4">
                                    <h3 class="text-md font-medium">Attendance Trends</h3>
                                    <select class="border rounded px-3 py-1 text-sm">
                                        <option>Last 12 Months</option>
                                        <option>Last 6 Months</option>
                                        <option>This Year</option>
                                        <option>Last Year</option>
                                    </select>
                                </div>
                                <div class="chart-container">
                                    <canvas id="attendanceTrendsChart"></canvas>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-white rounded-lg shadow p-6">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-md font-medium">Employee Attendance Summary</h3>
                                <div class="flex items-center space-x-2">
                                    <div class="relative">
                                        <input type="text" placeholder="Search employee..." class="pl-10 pr-4 py-1 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
                                        <i class="fas fa-search absolute left-3 top-2 text-gray-400"></i>
                                    </div>
                                    <button class="px-3 py-1 border border-gray-300 rounded-md text-sm bg-white hover:bg-gray-50">
                                        <i class="fas fa-filter mr-1"></i> Filter
                                    </button>
                                </div>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Present</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Absent</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Leave</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Late</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">%</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="flex-shrink-0 h-10 w-10">
                                                        <img class="h-10 w-10 rounded-full" src="https://randomuser.me/api/portraits/men/32.jpg" alt="">
                                                    </div>
                                                    <div class="ml-4">
                                                        <div class="text-sm font-medium text-gray-900">Dr. John Smith</div>
                                                        <div class="text-sm text-gray-500">EMP-1001</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Computer Science</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">18</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">1</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">0</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">2</td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="text-sm font-medium">94.7%</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="flex-shrink-0 h-10 w-10">
                                                        <img class="h-10 w-10 rounded-full" src="https://randomuser.me/api/portraits/women/44.jpg" alt="">
                                                    </div>
                                                    <div class="ml-4">
                                                        <div class="text-sm font-medium text-gray-900">Dr. Sarah Johnson</div>
                                                        <div class="text-sm text-gray-500">EMP-1002</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Mathematics</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">19</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">0</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">0</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">1</td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="text-sm font-medium">100%</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="flex-shrink-0 h-10 w-10">
                                                        <img class="h-10 w-10 rounded-full" src="https://randomuser.me/api/portraits/men/75.jpg" alt="">
                                                    </div>
                                                    <div class="ml-4">
                                                        <div class="text-sm font-medium text-gray-900">Prof. Michael Brown</div>
                                                        <div class="text-sm text-gray-500">EMP-1003</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Physics</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">15</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">2</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">2</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">1</td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="text-sm font-medium">78.9%</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="flex-shrink-0 h-10 w-10">
                                                        <img class="h-10 w-10 rounded-full" src="https://randomuser.me/api/portraits/women/63.jpg" alt="">
                                                    </div>
                                                    <div class="ml-4">
                                                        <div class="text-sm font-medium text-gray-900">Dr. Lisa Ray</div>
                                                        <div class="text-sm text-gray-500">EMP-1004</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Chemistry</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">17</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">1</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">1</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">1</td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="text-sm font-medium">89.5%</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="flex-shrink-0 h-10 w-10">
                                                        <img class="h-10 w-10 rounded-full" src="https://randomuser.me/api/portraits/men/42.jpg" alt="">
                                                    </div>
                                                    <div class="ml-4">
                                                        <div class="text-sm font-medium text-gray-900">Dr. Robert Wilson</div>
                                                        <div class="text-sm text-gray-500">EMP-1005</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Administration</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">18</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">1</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">0</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">0</td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="text-sm font-medium">94.7%</span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="bg-gray-50 px-6 py-3 flex items-center justify-between border-t border-gray-200">
                                <div class="flex-1 flex justify-between sm:hidden">
                                    <a href="#" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">Previous</a>
                                    <a href="#" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">Next</a>
                                </div>
                                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                                    <div>
                                        <p class="text-sm text-gray-700">
                                            Showing <span class="font-medium">1</span> to <span class="font-medium">5</span> of <span class="font-medium">1,254</span> records
                                        </p>
                                    </div>
                                    <div>
                                        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                                            <a href="#" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                                <span class="sr-only">Previous</span>
                                                <i class="fas fa-chevron-left"></i>
                                            </a>
                                            <a href="#" aria-current="page" class="z-10 bg-blue-50 border-blue-500 text-blue-600 relative inline-flex items-center px-4 py-2 border text-sm font-medium">1</a>
                                            <a href="#" class="bg-white border-gray-300 text-gray-500 hover:bg-gray-50 relative inline-flex items-center px-4 py-2 border text-sm font-medium">2</a>
                                            <a href="#" class="bg-white border-gray-300 text-gray-500 hover:bg-gray-50 relative inline-flex items-center px-4 py-2 border text-sm font-medium">3</a>
                                            <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">...</span>
                                            <a href="#" class="bg-white border-gray-300 text-gray-500 hover:bg-gray-50 relative inline-flex items-center px-4 py-2 border text-sm font-medium">8</a>
                                            <a href="#" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                                <span class="sr-only">Next</span>
                                                <i class="fas fa-chevron-right"></i>
                                            </a>
                                        </nav>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Mark Attendance Modal -->
    <div id="markAttendanceModal" class="modal fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-3xl shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center pb-3 border-b">
                <h3 class="text-xl font-semibold">Mark Attendance</h3>
                <button class="close-modal text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="mt-4">
                <form id="markAttendanceForm" method="POST">
                    <input type="hidden" name="action" value="mark">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Select Employee</label>
                        <select name="user_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500" required>
                            <option value="">Select Employee</option>
                            <?php foreach($employees as $emp): ?>
                                <option value="<?= $emp['id'] ?>"><?= $emp['name'] ?> (<?= $emp['employee_id'] ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                            <input type="date" name="date" value="<?= date('Y-m-d') ?>" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500" required>
                                <option value="Present">Present</option>
                                <option value="Absent">Absent</option>
                                <option value="On Leave">On Leave</option>
                                <option value="Late">Late</option>
                                <option value="Half Day">Half Day</option>
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Check-in Time</label>
                            <input type="time" name="check_in" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Check-out Time</label>
                            <input type="time" name="check_out" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                        <textarea name="notes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500"></textarea>
                    </div>
                    <div class="flex justify-end">
                        <button type="button" class="close-modal mr-3 px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                            Save Attendance
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Attendance Modal -->
    <div id="editAttendanceModal" class="modal fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-3xl shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center pb-3 border-b">
                <h3 class="text-xl font-semibold">Edit Attendance Record</h3>
                <button class="close-modal text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="mt-4">
                <form id="editAttendanceForm" method="POST">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id" id="editAttendanceId">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select name="status" id="editStatus" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500" required>
                                <option value="Present">Present</option>
                                <option value="Absent">Absent</option>
                                <option value="On Leave">On Leave</option>
                                <option value="Late">Late</option>
                                <option value="Half Day">Half Day</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                            <input type="date" name="date" id="editDate" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500" required>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Check-in Time</label>
                            <input type="time" name="check_in" id="editCheckIn" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Check-out Time</label>
                            <input type="time" name="check_out" id="editCheckOut" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                        <textarea name="notes" id="editNotes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500"></textarea>
                    </div>
                    <div class="flex justify-end">
                        <button type="button" class="close-modal mr-3 px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                            Update Attendance
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3 text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                    <i class="fas fa-exclamation-triangle text-red-600"></i>
                </div>
                <h3 class="text-lg leading-6 font-medium text-gray-900 mt-2">Delete Attendance Record</h3>
                <div class="mt-2 px-7 py-3">
                    <p class="text-sm text-gray-500">Are you sure you want to delete this attendance record? This action cannot be undone.</p>
                </div>
                <div class="items-center px-4 py-3">
                    <form id="deleteForm" method="POST">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" id="deleteId">
                        <button type="button" class="close-modal mr-2 px-4 py-2 bg-white text-gray-700 border border-gray-300 rounded-md text-sm font-medium hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-md hover:bg-red-700">
                            Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Toggle Sidebar
        document.getElementById('toggleSidebar').addEventListener('click', function() {
            const sidebar = document.querySelector('.sidebar');
            sidebar.classList.toggle('collapsed');
            
            const icon = this.querySelector('i');
            if (sidebar.classList.contains('collapsed')) {
                icon.classList.remove('fa-chevron-left');
                icon.classList.add('fa-chevron-right');
                this.querySelector('.nav-text').textContent = 'Expand';
            } else {
                icon.classList.remove('fa-chevron-right');
                icon.classList.add('fa-chevron-left');
                this.querySelector('.nav-text').textContent = 'Collapse';
            }
        });

        // Tab switching
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const tabId = this.dataset.tab;
                
                // Update active tab button
                document.querySelectorAll('.tab-btn').forEach(tb => {
                    tb.classList.remove('border-blue-500', 'text-blue-600');
                    tb.classList.add('border-transparent', 'text-gray-500');
                });
                this.classList.add('border-blue-500', 'text-blue-600');
                this.classList.remove('border-transparent', 'text-gray-500');
                
                // Update active tab content
                document.querySelectorAll('.tab-content').forEach(content => {
                    content.classList.remove('active');
                });
                document.getElementById(tabId).classList.add('active');
            });
        });

        // Modal handling
        const markAttendanceBtn = document.getElementById('markAttendanceBtn');
        const markAttendanceModal = document.getElementById('markAttendanceModal');
        const editAttendanceModal = document.getElementById('editAttendanceModal');
        const deleteModal = document.getElementById('deleteModal');
        const closeModalButtons = document.querySelectorAll('.close-modal');
        
        if (markAttendanceBtn) {
            markAttendanceBtn.addEventListener('click', () => {
                markAttendanceModal.classList.remove('hidden');
            });
        }
        
        closeModalButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                markAttendanceModal.classList.add('hidden');
                editAttendanceModal.classList.add('hidden');
                deleteModal.classList.add('hidden');
            });
        });
        
        // Close modal when clicking outside
        window.addEventListener('click', (e) => {
            if (e.target === markAttendanceModal) markAttendanceModal.classList.add('hidden');
            if (e.target === editAttendanceModal) editAttendanceModal.classList.add('hidden');
            if (e.target === deleteModal) deleteModal.classList.add('hidden');
        });

        // Edit button handling
        document.querySelectorAll('.edit-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.getElementById('editAttendanceId').value = this.dataset.id;
                document.getElementById('editStatus').value = this.dataset.status;
                document.getElementById('editCheckIn').value = this.dataset.checkin;
                document.getElementById('editCheckOut').value = this.dataset.checkout;
                document.getElementById('editNotes').value = this.dataset.notes;
                document.getElementById('editDate').value = '<?= $currentDate ?>';
                editAttendanceModal.classList.remove('hidden');
            });
        });

        // Delete button handling
        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.getElementById('deleteId').value = this.dataset.id;
                deleteModal.classList.remove('hidden');
            });
        });

        // Close message alert
        document.querySelector('.close-message')?.addEventListener('click', function() {
            this.closest('.bg-green-100').remove();
        });

        // Initialize charts
        document.addEventListener('DOMContentLoaded', function() {
            // Monthly trend chart
            const monthlyData = <?= json_encode($monthlySummary) ?>;
            const trendCtx = document.getElementById('attendanceTrendChart')?.getContext('2d');
            
            if (trendCtx) {
                new Chart(trendCtx, {
                    type: 'line',
                    data: {
                        labels: monthlyData.map(item => new Date(item.date).getDate()),
                        datasets: [
                            {
                                label: 'Present',
                                data: monthlyData.map(item => item.present),
                                borderColor: '#10B981',
                                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                                tension: 0.3
                            },
                            {
                                label: 'Absent',
                                data: monthlyData.map(item => item.absent),
                                borderColor: '#EF4444',
                                backgroundColor: 'rgba(239, 68, 68, 0.1)',
                                tension: 0.3
                            },
                            {
                                label: 'On Leave',
                                data: monthlyData.map(item => item.on_leave),
                                borderColor: '#F59E0B',
                                backgroundColor: 'rgba(245, 158, 11, 0.1)',
                                tension: 0.3
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0
                                }
                            }
                        }
                    }
                });
            }
            
            // Month navigation
            document.getElementById('prevMonth')?.addEventListener('click', function() {
                // Implement month navigation with AJAX
                console.log('Previous month clicked');
            });
            
            document.getElementById('nextMonth')?.addEventListener('click', function() {
                // Implement month navigation with AJAX
                console.log('Next month clicked');
            });
        });
    </script>
</body>
</html>