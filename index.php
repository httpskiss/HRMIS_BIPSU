<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit;
}

require 'auth/db.php"'; // Adjust path as needed

// Fetch total employees count
$totalEmployees = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();

// Fetch today's attendance
$today = date('Y-m-d');
$attendanceStats = $pdo->query("
    SELECT 
        SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present,
        SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent,
        SUM(CASE WHEN status = 'on_leave' THEN 1 ELSE 0 END) as on_leave
    FROM attendance 
    WHERE date = '$today'
")->fetch(PDO::FETCH_ASSOC);

// Fetch recent check-ins
$recentCheckins = $pdo->query("
    SELECT u.first_name, u.last_name, u.department, a.check_in
    FROM attendance a
    JOIN users u ON a.user_id = u.id
    WHERE a.date = '$today' AND a.check_in IS NOT NULL
    ORDER BY a.check_in DESC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

// Fetch employee distribution by department
$departmentDistribution = $pdo->query("
    SELECT department, COUNT(*) as count 
    FROM users 
    GROUP BY department
")->fetchAll(PDO::FETCH_ASSOC);

// Prepare data for chart
$deptLabels = [];
$deptData = [];
foreach ($departmentDistribution as $dept) {
    $deptLabels[] = $dept['department'];
    $deptData[] = $dept['count'];
}

// Fetch recent employees
$recentEmployees = $pdo->query("
    SELECT id, first_name, last_name, email, department, role 
    FROM users 
    ORDER BY created_at DESC 
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

$facultyCount = $pdo->query("SELECT COUNT(*) FROM users WHERE category = 'faculty'")->fetchColumn();

// Fetch employee distribution by department
$departmentDistribution = $pdo->query("
    SELECT department, COUNT(*) as count 
    FROM users 
    GROUP BY department
    ORDER BY count DESC
")->fetchAll(PDO::FETCH_ASSOC);

// Prepare data for chart
$deptLabels = [];
$deptData = [];
foreach ($departmentDistribution as $dept) {
    $deptLabels[] = $dept['department'];
    $deptData[] = $dept['count'];
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HRMIS</title>
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
         <!-- Sidebar -->
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
                    <h1 class="text-2xl font-semibold text-gray-800">Dashboard</h1>
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

            <!-- Dashboard Content -->
            <main class="p-6">
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-blue-100 text-blue-600 mr-4">
                            <i class="fas fa-users text-xl"></i>
                        </div>
                        <div>
                            <p class="text-gray-500">Total Employees</p>
                            <h3 class="text-2xl font-bold"><?= number_format($totalEmployees) ?></h3>
                            <p class="text-green-500 text-sm">+12% from last month</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-green-100 text-green-600 mr-4">
                            <i class="fas fa-calendar-check text-xl"></i>
                        </div>
                        <div>
                            <p class="text-gray-500">Present Today</p>
                            <h3 class="text-2xl font-bold"><?= $attendanceStats['present'] ?? 0 ?></h3>
                            <p class="text-sm <?= ($attendanceStats['present'] ?? 0) > ($attendanceStats['present'] ?? 1) ? 'text-green-500' : 'text-red-500' ?>">
                                <?= round(($attendanceStats['present'] ?? 0)/$totalEmployees*100) ?>% of workforce
                            </p>
                        </div>
                    </div>
                </div>
                <!-- Add other stat cards similarly -->
                 <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-purple-100 text-purple-600 mr-4">
                                <i class="fas fa-money-bill-wave text-xl"></i>
                            </div>
                            <div>
                                <p class="text-gray-500">Monthly Payroll</p>
                                <h3 class="text-2xl font-bold">$425,000</h3>
                                <p class="text-green-500 text-sm">+5% from last month</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-yellow-100 text-yellow-600 mr-4">
                                <i class="fas fa-user-graduate text-xl"></i>
                            </div>
                            <div>
                                <p class="text-gray-500">Faculty Members</p>
                                <h3 class="text-2xl font-bold"><?= number_format($facultyCount) ?></h3>
                                <p class="text-green-500 text-sm">+8% from last year</p>
                            </div>
                        </div>
                    </div>

            </div>
                
                <!-- Charts and Tables -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                    <!-- Department Distribution -->
                    <div class="bg-white rounded-lg shadow p-6 lg:col-span-2">
                       <div class="flex justify-between items-center mb-4">
                            <h2 class="text-lg font-semibold">Employee Distribution by Department</h2>
                            <select id="departmentFilter" class="border rounded px-3 py-1 text-sm">
                                <option value="all">All Departments</option>
                                <?php foreach ($deptLabels as $dept): ?>
                                    <option value="<?= htmlspecialchars($dept) ?>"><?= htmlspecialchars($dept) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="chart-container">
                            <canvas id="departmentChart"></canvas>
                        </div>
                    </div>

                    <!-- Recent Activities -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h2 class="text-lg font-semibold mb-4">Recent Activities</h2>
                        <div class="space-y-4">
                            <div class="flex items-start">
                                <div class="p-2 rounded-full bg-blue-100 text-blue-600 mr-3">
                                    <i class="fas fa-user-plus text-sm"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-medium">New employee added</p>
                                    <p class="text-xs text-gray-500">Dr. Sarah Johnson joined as Professor</p>
                                    <p class="text-xs text-gray-400">2 hours ago</p>
                                </div>
                            </div>
                            <div class="flex items-start">
                                <div class="p-2 rounded-full bg-green-100 text-green-600 mr-3">
                                    <i class="fas fa-file-signature text-sm"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-medium">Contract renewed</p>
                                    <p class="text-xs text-gray-500">Prof. Michael Brown's contract extended</p>
                                    <p class="text-xs text-gray-400">5 hours ago</p>
                                </div>
                            </div>
                            <div class="flex items-start">
                                <div class="p-2 rounded-full bg-purple-100 text-purple-600 mr-3">
                                    <i class="fas fa-money-check-alt text-sm"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-medium">Payroll processed</p>
                                    <p class="text-xs text-gray-500">June payroll completed for <?php echo $total_users; ?> employees</p>
                                    <p class="text-xs text-gray-400">1 day ago</p>
                                </div>
                            </div>
                            <div class="flex items-start">
                                <div class="p-2 rounded-full bg-yellow-100 text-yellow-600 mr-3">
                                    <i class="fas fa-calendar-times text-sm"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-medium">Leave request</p>
                                    <p class="text-xs text-gray-500">Dr. Lisa Ray applied for 5 days leave</p>
                                    <p class="text-xs text-gray-400">2 days ago</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                 <!-- Employee Management Section -->
                <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
                    <div class="flex justify-between items-center p-6 border-b">
                        <h2 class="text-lg font-semibold">Employee Management</h2>
                        <button id="addEmployeeBtn" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded flex items-center">
                            <i class="fas fa-plus mr-2"></i> Add Employee
                        </button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Position</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">EMP-1001</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <img class="h-10 w-10 rounded-full" src="https://randomuser.me/api/portraits/men/32.jpg" alt="">
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">Dr. John Smith</div>
                                                <div class="text-sm text-gray-500">john.smith@university.edu</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Computer Science</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Professor</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <button class="text-blue-600 hover:text-blue-900 mr-3">Edit</button>
                                        <button class="text-red-600 hover:text-red-900">Delete</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">EMP-1002</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <img class="h-10 w-10 rounded-full" src="https://randomuser.me/api/portraits/women/44.jpg" alt="">
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">Dr. Sarah Johnson</div>
                                                <div class="text-sm text-gray-500">sarah.j@university.edu</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Mathematics</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Associate Professor</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <button class="text-blue-600 hover:text-blue-900 mr-3">Edit</button>
                                        <button class="text-red-600 hover:text-red-900">Delete</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">EMP-1003</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <img class="h-10 w-10 rounded-full" src="https://randomuser.me/api/portraits/men/75.jpg" alt="">
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">Prof. Michael Brown</div>
                                                <div class="text-sm text-gray-500">michael.b@university.edu</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Physics</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Professor</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">On Leave</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <button class="text-blue-600 hover:text-blue-900 mr-3">Edit</button>
                                        <button class="text-red-600 hover:text-red-900">Delete</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">EMP-1004</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <img class="h-10 w-10 rounded-full" src="https://randomuser.me/api/portraits/women/63.jpg" alt="">
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">Dr. Lisa Ray</div>
                                                <div class="text-sm text-gray-500">lisa.ray@university.edu</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Chemistry</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Assistant Professor</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <button class="text-blue-600 hover:text-blue-900 mr-3">Edit</button>
                                        <button class="text-red-600 hover:text-red-900">Delete</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">EMP-1005</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <img class="h-10 w-10 rounded-full" src="https://randomuser.me/api/portraits/men/42.jpg" alt="">
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">Dr. Robert Wilson</div>
                                                <div class="text-sm text-gray-500">robert.w@university.edu</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Administration</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">HR Manager</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <button class="text-blue-600 hover:text-blue-900 mr-3">Edit</button>
                                        <button class="text-red-600 hover:text-red-900">Delete</button>
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
                                    Showing <span class="font-medium">1</span> to <span class="font-medium">5</span> of <span class="font-medium">1,254</span> employees
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

                
                <!-- Attendance and Payroll Summary -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Attendance Summary -->
                    <div class="bg-white rounded-lg shadow overflow-hidden">
                        <div class="p-6 border-b">
                            <h2 class="text-lg font-semibold">Attendance Summary</h2>
                        </div>
                        <div class="p-6">
                            <div class="mb-4">
                                <div class="flex justify-between mb-2">
                                    <span class="text-sm font-medium text-gray-700">Today's Attendance</span>
                                    <span class="text-sm font-medium">987/1,254</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2.5">
                                    <div class="bg-blue-600 h-2.5 rounded-full" style="width: 78%"></div>
                                </div>
                            </div>
                            <div class="grid grid-cols-3 gap-4 text-center">
                                <div class="p-3 border rounded-lg">
                                    <div class="text-2xl font-bold text-blue-600">987</div>
                                    <div class="text-xs text-gray-500">Present</div>
                                </div>
                                <div class="p-3 border rounded-lg">
                                    <div class="text-2xl font-bold text-yellow-600">45</div>
                                    <div class="text-xs text-gray-500">On Leave</div>
                                </div>
                                <div class="p-3 border rounded-lg">
                                    <div class="text-2xl font-bold text-red-600">222</div>
                                    <div class="text-xs text-gray-500">Absent</div>
                                </div>
                            </div>
                            <div class="mt-6">
                                <h3 class="text-sm font-medium mb-3">Recent Check-ins</h3>
                                <div class="space-y-3">
                                    <div class="flex items-center">
                                        <img class="w-8 h-8 rounded-full mr-3" src="https://randomuser.me/api/portraits/men/32.jpg" alt="John Smith">
                                        <div class="flex-1">
                                            <p class="text-sm font-medium">Dr. John Smith</p>
                                            <p class="text-xs text-gray-500">Computer Science</p>
                                        </div>
                                        <span class="text-xs text-gray-500">08:15 AM</span>
                                    </div>
                                    <div class="flex items-center">
                                        <img class="w-8 h-8 rounded-full mr-3" src="https://randomuser.me/api/portraits/women/44.jpg" alt="Sarah Johnson">
                                        <div class="flex-1">
                                            <p class="text-sm font-medium">Dr. Sarah Johnson</p>
                                            <p class="text-xs text-gray-500">Mathematics</p>
                                        </div>
                                        <span class="text-xs text-gray-500">08:22 AM</span>
                                    </div>
                                    <div class="flex items-center">
                                        <img class="w-8 h-8 rounded-full mr-3" src="https://randomuser.me/api/portraits/women/63.jpg" alt="Lisa Ray">
                                        <div class="flex-1">
                                            <p class="text-sm font-medium">Dr. Lisa Ray</p>
                                            <p class="text-xs text-gray-500">Chemistry</p>
                                        </div>
                                        <span class="text-xs text-gray-500">08:30 AM</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payroll Summary -->
                    <div class="bg-white rounded-lg shadow overflow-hidden">
                        <div class="p-6 border-b">
                            <h2 class="text-lg font-semibold">Payroll Summary</h2>
                        </div>
                        <div class="p-6">
                            <div class="mb-6">
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-sm font-medium text-gray-700">June 2023 Payroll</span>
                                    <span class="text-sm font-medium">$425,000</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2.5">
                                    <div class="bg-green-600 h-2.5 rounded-full" style="width: 65%"></div>
                                </div>
                                <div class="flex justify-between text-xs text-gray-500 mt-1">
                                    <span>Processed: $275,000</span>
                                    <span>Pending: $150,000</span>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-4 mb-6">
                                <div class="p-3 border rounded-lg">
                                    <div class="flex items-center">
                                        <div class="p-2 rounded-full bg-blue-100 text-blue-600 mr-3">
                                            <i class="fas fa-user-tie"></i>
                                        </div>
                                        <div>
                                            <div class="text-xl font-bold">$320,000</div>
                                            <div class="text-xs text-gray-500">Faculty</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="p-3 border rounded-lg">
                                    <div class="flex items-center">
                                        <div class="p-2 rounded-full bg-purple-100 text-purple-600 mr-3">
                                            <i class="fas fa-user-cog"></i>
                                        </div>
                                        <div>
                                            <div class="text-xl font-bold">$105,000</div>
                                            <div class="text-xs text-gray-500">Staff</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <h3 class="text-sm font-medium mb-3">Upcoming Payments</h3>
                                <div class="space-y-3">
                                    <div class="flex items-center justify-between p-2 border rounded-lg">
                                        <div class="flex items-center">
                                            <img class="w-8 h-8 rounded-full mr-3" src="https://randomuser.me/api/portraits/men/75.jpg" alt="Michael Brown">
                                            <div>
                                                <p class="text-sm font-medium">Prof. Michael Brown</p>
                                                <p class="text-xs text-gray-500">Physics</p>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-sm font-medium">$8,500</p>
                                            <p class="text-xs text-gray-500">Due: Jun 30</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between p-2 border rounded-lg">
                                        <div class="flex items-center">
                                            <img class="w-8 h-8 rounded-full mr-3" src="https://randomuser.me/api/portraits/women/63.jpg" alt="Lisa Ray">
                                            <div>
                                                <p class="text-sm font-medium">Dr. Lisa Ray</p>
                                                <p class="text-xs text-gray-500">Chemistry</p>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-sm font-medium">$6,200</p>
                                            <p class="text-xs text-gray-500">Due: Jun 30</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between p-2 border rounded-lg">
                                        <div class="flex items-center">
                                            <img class="w-8 h-8 rounded-full mr-3" src="https://randomuser.me/api/portraits/men/42.jpg" alt="Robert Wilson">
                                            <div>
                                                <p class="text-sm font-medium">Dr. Robert Wilson</p>
                                                <p class="text-xs text-gray-500">Administration</p>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-sm font-medium">$7,800</p>
                                            <p class="text-xs text-gray-500">Due: Jun 30</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Add Employee Modal -->
    <!-- ... (same as before) ... -->

     <!-- Add Employee Modal -->
    <div id="addEmployeeModal" class="modal fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-3xl shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center pb-3 border-b">
                <h3 class="text-xl font-semibold">Add New Employee</h3>
                <button id="closeModal" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="mt-4">
                <form id="employeeForm">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
                            <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
                            <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input type="email" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                            <input type="tel" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Department</label>
                            <select class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500" required>
                                <option value="">Select Department</option>
                                <option>Computer Science</option>
                                <option>Mathematics</option>
                                <option>Physics</option>
                                <option>Chemistry</option>
                                <option>Biology</option>
                                <option>Administration</option>
                                <option>Human Resources</option>
                                <option>Finance</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Position</label>
                            <select class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500" required>
                                <option value="">Select Position</option>
                                <option>Professor</option>
                                <option>Associate Professor</option>
                                <option>Assistant Professor</option>
                                <option>Lecturer</option>
                                <option>Researcher</option>
                                <option>HR Manager</option>
                                <option>Finance Officer</option>
                                <option>Administrative Staff</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Hire Date</label>
                            <input type="date" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Salary</label>
                            <input type="number" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500" required>
                        </div>
                    </div>
                    <div class="mt-6">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                        <textarea rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500"></textarea>
                    </div>
                    <div class="mt-6 flex justify-end space-x-3">
                        <button type="button" id="cancelAddEmployee" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Save Employee
                        </button>
                    </div>
                </form>
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

        // Department Distribution Chart
            const departmentCtx = document.getElementById('departmentChart').getContext('2d');
            const departmentChart = new Chart(departmentCtx, {
                type: 'bar',
                data: {
                    labels: <?= json_encode($deptLabels) ?>,
                    datasets: [{
                        label: 'Employees',
                        data: <?= json_encode($deptData) ?>,
                        backgroundColor: [
                            'rgba(54, 162, 235, 0.6)',
                            'rgba(255, 99, 132, 0.6)',
                            'rgba(255, 206, 86, 0.6)',
                            'rgba(75, 192, 192, 0.6)',
                            'rgba(153, 102, 255, 0.6)',
                            'rgba(255, 159, 64, 0.6)',
                            'rgba(199, 199, 199, 0.6)',
                            'rgba(83, 102, 255, 0.6)'
                        ],
                        borderColor: [
                            'rgba(54, 162, 235, 1)',
                            'rgba(255, 99, 132, 1)',
                            'rgba(255, 206, 86, 1)',
                            'rgba(75, 192, 192, 1)',
                            'rgba(153, 102, 255, 1)',
                            'rgba(255, 159, 64, 1)',
                            'rgba(199, 199, 199, 1)',
                            'rgba(83, 102, 255, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0 // Ensures whole numbers on Y-axis
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.parsed.y + ' employees';
                                }
                            }
                        }
                    }
                }
            });

        // Rest of your JavaScript remains the same
        // ...

        // Close modal when clicking outside
        window.addEventListener('click', (e) => {
            if (e.target === addEmployeeModal) {
                addEmployeeModal.classList.add('hidden');
            }
        });

        // Logout Functionality with confirmation
        document.getElementById('logoutBtn').addEventListener('click', function(e) {
        e.preventDefault();
            if (confirm('Are you sure you want to logout?')) {
                window.location.href = 'logout.php';
            }
        });
    </script>
</body>
</html>