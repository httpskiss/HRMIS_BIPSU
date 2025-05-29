<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit;
}

require 'auth/db.php'; // Adjust path as needed

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

$facultyCount = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'employee'")->fetchColumn();

$total_users = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'employee'")->fetchColumn();

// Fetch employee distribution by department
$departmentDistribution = $pdo->query("
    SELECT department, COUNT(*) as count 
    FROM users 
    GROUP BY department
")->fetchAll(PDO::FETCH_ASSOC);

// Fetch unique schools for the filter dropdown
$schools = $pdo->query("
    SELECT DISTINCT department as school 
    FROM users 
    ORDER BY department
")->fetchAll(PDO::FETCH_COLUMN);

// Prepare data for chart with color mapping
$deptLabels = [];
$deptData = [];
$colorMap = []; // We'll use this to maintain consistent colors

$colorPalette = [
    'rgba(54, 162, 235, 0.6)',
    'rgba(255, 99, 132, 0.6)',
    'rgba(255, 206, 86, 0.6)',
    'rgba(75, 192, 192, 0.6)',
    'rgba(153, 102, 255, 0.6)',
    'rgba(255, 159, 64, 0.6)',
    'rgba(199, 199, 199, 0.6)',
    'rgba(83, 102, 255, 0.6)'
];

$borderPalette = [
    'rgba(54, 162, 235, 1)',
    'rgba(255, 99, 132, 1)',
    'rgba(255, 206, 86, 1)',
    'rgba(75, 192, 192, 1)',
    'rgba(153, 102, 255, 1)',
    'rgba(255, 159, 64, 1)',
    'rgba(199, 199, 199, 1)',
    'rgba(83, 102, 255, 1)'
];

foreach ($departmentDistribution as $index => $dept) {
    $deptLabels[] = $dept['department'];
    $deptData[] = $dept['count'];
    $colorIndex = $index % count($colorPalette); // Cycle through colors
    $colorMap[$dept['department']] = [
        'background' => $colorPalette[$colorIndex],
        'border' => $borderPalette[$colorIndex]
    ];
}


$employees = $pdo->query("
    SELECT id, first_name, last_name, email, employee_id, department, role, category, created_at 
    FROM users 
    ORDER BY created_at DESC
    LIMIT 0, 5  
")->fetchAll(PDO::FETCH_ASSOC);


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
                            <select id="schoolFilter" class="border rounded px-3 py-1 text-sm">
                                <option value="all">All Departments</option>
                                <?php foreach ($schools as $school): ?>
                                    <option value="<?= htmlspecialchars($school) ?>"><?= htmlspecialchars($school) ?></option>
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
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($employees as $employee): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= htmlspecialchars($employee['employee_id']) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <img class="h-10 w-10 rounded-full" src="https://ui-avatars.com/api/?name=<?= urlencode($employee['first_name'] . '+' . $employee['last_name']) ?>&background=random" alt="">
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']) ?></div>
                                                <div class="text-sm text-gray-500"><?= htmlspecialchars($employee['email']) ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($employee['department']) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($employee['role']) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($employee['category']) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <button class="text-blue-600 hover:text-blue-900 mr-3 edit-employee" data-id="<?= $employee['id'] ?>">Edit</button>
                                        <button class="text-red-600 hover:text-red-900 delete-employee" data-id="<?= $employee['id'] ?>">Delete</button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
            
                    <div class="bg-gray-50 px-6 py-3 flex items-center justify-between border-t border-gray-200 pagination-container">
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
                        <!-- Basic Information -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
                            <input type="text" name="first_name" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
                            <input type="text" name="last_name" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input type="email" name="email" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                            <input type="password" name="password" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500" required>
                        </div>
                        
                        <!-- Work Information -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Employee ID</label>
                            <input type="text" name="employee_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Department</label>
                            <select name="department" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500" required>
                                 <option value="STCS">STCS</option>
                                    <option value="SOE">SOE</option>
                                    <option value="STED">STED</option>
                                    <option value="SNHS">SNHS</option>
                                    <option value="SCJE">SCJE</option>
                                    <option value="SME">SME</option>
                                    <option value="SAS">SAS</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                            <select name="role" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500" required>
                                <option value="">Select Role</option>
                                <option value="employee">Employee</option>
                                <option value="manager">Manager</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                            <select name="category" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
                                <option value="staff">Staff</option>
                                <option value="faculty">Faculty</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Additional Fields -->
                    <div class="mt-6">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                        <input type="tel" name="phone" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
                    </div>
                    
                    <div class="mt-6">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                        <textarea name="address" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500"></textarea>
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

        // Initialize chart with color mapping from PHP
const colorMap = <?= json_encode($colorMap) ?>;
const allDepartmentsData = {
    labels: <?= json_encode($deptLabels) ?>,
    data: <?= json_encode($deptData) ?>,
    backgroundColors: <?= json_encode($deptLabels) ?>.map(label => colorMap[label].background),
    borderColors: <?= json_encode($deptLabels) ?>.map(label => colorMap[label].border)
};

const departmentCtx = document.getElementById('departmentChart').getContext('2d');
const departmentChart = new Chart(departmentCtx, {
    type: 'bar',
    data: {
        labels: allDepartmentsData.labels,
        datasets: [{
            label: 'Employees',
            data: allDepartmentsData.data,
            backgroundColor: allDepartmentsData.backgroundColors,
            borderColor: allDepartmentsData.borderColors,
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
                    precision: 0
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



// Handle school filter change with AJAX
document.getElementById('schoolFilter').addEventListener('change', function() {
    const selectedSchool = this.value;
    
    if (selectedSchool === 'all') {
        // Reset to show all departments with original colors
        departmentChart.data.labels = allDepartmentsData.labels;
        departmentChart.data.datasets[0].data = allDepartmentsData.data;
        departmentChart.data.datasets[0].backgroundColor = allDepartmentsData.backgroundColors;
        departmentChart.data.datasets[0].borderColor = allDepartmentsData.borderColors;
        departmentChart.update();
    } else {
        // Filter for selected school
        fetch(`get_department_data.php?school=${encodeURIComponent(selectedSchool)}`)
            .then(response => response.json())
            .then(data => {
                // Update chart data while maintaining colors
                departmentChart.data.labels = data.labels;
                departmentChart.data.datasets[0].data = data.data;
                departmentChart.data.datasets[0].backgroundColor = data.labels.map(label => colorMap[label]?.background || 'rgba(54, 162, 235, 0.6)');
                departmentChart.data.datasets[0].borderColor = data.labels.map(label => colorMap[label]?.border || 'rgba(54, 162, 235, 1)');
                departmentChart.update();
            });
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
        
        
        document.addEventListener('DOMContentLoaded', function() {
    // Toast notification function
    function showToast(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = `fixed top-4 right-4 px-4 py-2 rounded-md shadow-md text-white ${
            type === 'success' ? 'bg-green-500' : 'bg-red-500'
        } z-50`;
        toast.textContent = message;
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.classList.add('opacity-0', 'transition-opacity', 'duration-300');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    // Handle form submission for adding/editing employee
    const employeeForm = document.getElementById('employeeForm');
    if (employeeForm) {
        employeeForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const isEdit = formData.has('id');
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn.textContent;

            try {
                // Show loading state
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Processing...';

                const response = await fetch(isEdit ? 'update_employee.php' : 'add_employee.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();

                if (data.success) {
                    showToast(data.message || (isEdit ? "Employee updated successfully!" : "Employee added successfully!"), 'success');
                    
                    // Close modal if exists
                    const modal = document.getElementById('addEmployeeModal');
                    if (modal) modal.classList.add('hidden');
                    
                    // Refresh after 1 second
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToast('Error: ' + (data.message || 'Operation failed'), 'error');
                }
            } catch (error) {
                showToast('Network error: ' + error.message, 'error');
            } finally {
                // Reset button state
                submitBtn.disabled = false;
                submitBtn.textContent = originalBtnText;
            }
        });
    }

    // Handle delete employee
    document.querySelectorAll('.delete-employee').forEach(button => {
        button.addEventListener('click', async function() {
            if (confirm('Are you sure you want to delete this employee?')) {
                const employeeId = this.getAttribute('data-id');
                const button = this;
                
                try {
                    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                    
                    const response = await fetch(`delete_employee.php?id=${employeeId}`);
                    const data = await response.json();

                    if (data.success) {
                        showToast("Employee deleted successfully!", 'success');
                        button.closest('tr').remove();
                    } else {
                        showToast('Error: ' + data.message, 'error');
                    }
                } catch (error) {
                    showToast('Network error: ' + error.message, 'error');
                } finally {
                    button.innerHTML = '<i class="fas fa-trash"></i>';
                }
            }
        });
    });

    // Handle edit employee
    document.querySelectorAll('.edit-employee').forEach(button => {
        button.addEventListener('click', async function() {
            const employeeId = this.getAttribute('data-id');
            const button = this;
            
            try {
                button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                
                const response = await fetch(`get_employee.php?id=${employeeId}`);
                const data = await response.json();

                if (data.success && data.employee) {
                    const emp = data.employee;
                    const form = document.getElementById('employeeForm');
                    const modal = document.getElementById('addEmployeeModal');

                    // Fill form
                    form.querySelector('[name="first_name"]').value = emp.first_name || '';
                    form.querySelector('[name="last_name"]').value = emp.last_name || '';
                    form.querySelector('[name="email"]').value = emp.email || '';
                    form.querySelector('[name="employee_id"]').value = emp.employee_id || '';
                    form.querySelector('[name="department"]').value = emp.department || '';
                    form.querySelector('[name="role"]').value = emp.role || '';
                    form.querySelector('[name="category"]').value = emp.category || 'staff';
                    
                    // Add hidden ID field if editing
                    if (!form.querySelector('[name="id"]')) {
                        const idInput = document.createElement('input');
                        idInput.type = 'hidden';
                        idInput.name = 'id';
                        form.appendChild(idInput);
                    }
                    form.querySelector('[name="id"]').value = emp.id;

                    // Update modal title
                    modal.querySelector('h3').textContent = 'Edit Employee';

                    // Show modal
                    modal.classList.remove('hidden');
                } else {
                    showToast('Error: ' + (data.message || 'Employee not found'), 'error');
                }
            } catch (error) {
                showToast('Network error: ' + error.message, 'error');
            } finally {
                button.innerHTML = '<i class="fas fa-edit"></i>';
            }
        });
    });

    // Reset form when adding new employee
    const addEmployeeBtn = document.getElementById('addEmployeeBtn');
    if (addEmployeeBtn) {
        addEmployeeBtn.addEventListener('click', function() {
            const form = document.getElementById('employeeForm');
            const modal = document.getElementById('addEmployeeModal');
            
            form.reset();
            modal.querySelector('h3').textContent = 'Add New Employee';
            
            // Remove ID field if exists
            const idField = form.querySelector('[name="id"]');
            if (idField) idField.remove();
            
            modal.classList.remove('hidden');
        });
    }

    // Close modal handler
    const closeModalBtn = document.getElementById('closeModal');
    if (closeModalBtn) {
        closeModalBtn.addEventListener('click', function() {
            document.getElementById('addEmployeeModal').classList.add('hidden');
        });
    }
});

document.addEventListener('DOMContentLoaded', function() {
    // Current page tracking
    let currentPage = 1;
    const itemsPerPage = 5;
    
    // Toast notification function
    function showToast(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = `fixed top-4 right-4 px-4 py-2 rounded-md shadow-md text-white ${
            type === 'success' ? 'bg-green-500' : 'bg-red-500'
        } z-50`;
        toast.textContent = message;
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.classList.add('opacity-0', 'transition-opacity', 'duration-300');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    // Load employees for a specific page
    async function loadEmployees(page = 1) {
        try {
            // Show loading state
            const tableBody = document.querySelector('tbody');
            tableBody.innerHTML = '<tr><td colspan="7" class="text-center py-4"><i class="fas fa-spinner fa-spin"></i> Loading employees...</td></tr>';
            
            const response = await fetch(`get_employees.php?page=${page}&per_page=${itemsPerPage}`);
            const data = await response.json();
            
            if (data.success) {
                renderEmployees(data.employees);
                updatePagination(data.total, page);
                currentPage = page;
            } else {
                showToast('Error loading employees: ' + data.message, 'error');
            }
        } catch (error) {
            showToast('Network error: ' + error.message, 'error');
        }
    }

    // Render employees in the table
    function renderEmployees(employees) {
        const tableBody = document.querySelector('tbody');
        tableBody.innerHTML = '';
        
        if (employees.length === 0) {
            tableBody.innerHTML = '<tr><td colspan="7" class="text-center py-4">No employees found</td></tr>';
            return;
        }
        
        employees.forEach(emp => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${emp.employee_id}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 h-10 w-10">
                            <img class="h-10 w-10 rounded-full" src="https://ui-avatars.com/api/?name=${encodeURIComponent(emp.first_name + '+' + emp.last_name)}&background=random" alt="">
                        </div>
                        <div class="ml-4">
                            <div class="text-sm font-medium text-gray-900">${emp.first_name} ${emp.last_name}</div>
                            <div class="text-sm text-gray-500">${emp.email}</div>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${emp.department}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${emp.role}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${emp.category || 'staff'}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <button class="text-blue-600 hover:text-blue-900 mr-3 edit-employee" data-id="${emp.id}">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="text-red-600 hover:text-red-900 delete-employee" data-id="${emp.id}">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            `;
            tableBody.appendChild(row);
        });
        
        // Reattach event listeners
        attachEventListeners();
    }

    // Update pagination controls
    function updatePagination(totalItems, currentPage) {
        const totalPages = Math.ceil(totalItems / itemsPerPage);
        const paginationContainer = document.querySelector('.pagination-container');
        
        if (!paginationContainer) {
            console.error('Pagination container not found');
            return;
        }
        
        let paginationHTML = `
            <div class="flex-1 flex justify-between sm:hidden">
                <button onclick="window.loadEmployees(${currentPage - 1})" ${currentPage === 1 ? 'disabled' : ''} class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    Previous
                </button>
                <button onclick="window.loadEmployees(${currentPage + 1})" ${currentPage === totalPages ? 'disabled' : ''} class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    Next
                </button>
            </div>
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm text-gray-700">
                        Showing <span class="font-medium">${(currentPage - 1) * itemsPerPage + 1}</span>
                        to <span class="font-medium">${Math.min(currentPage * itemsPerPage, totalItems)}</span>
                        of <span class="font-medium">${totalItems}</span> employees
                    </p>
                </div>
                <div>
                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                        <button onclick="window.loadEmployees(${currentPage - 1})" ${currentPage === 1 ? 'disabled' : ''} class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                            <span class="sr-only">Previous</span>
                            <i class="fas fa-chevron-left"></i>
                        </button>
        `;
        
        // Show limited page numbers with ellipsis
        const maxVisiblePages = 5;
        let startPage = Math.max(1, currentPage - Math.floor(maxVisiblePages / 2));
        let endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);
        
        if (startPage > 1) {
            paginationHTML += `
                <button onclick="window.loadEmployees(1)" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                    1
                </button>
                ${startPage > 2 ? '<span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">...</span>' : ''}
            `;
        }
        
        for (let i = startPage; i <= endPage; i++) {
            paginationHTML += `
                <button onclick="window.loadEmployees(${i})" class="${i === currentPage ? 'z-10 bg-blue-50 border-blue-500 text-blue-600' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'} relative inline-flex items-center px-4 py-2 border text-sm font-medium">
                    ${i}
                </button>
            `;
        }
        
        if (endPage < totalPages) {
            paginationHTML += `
                ${endPage < totalPages - 1 ? '<span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">...</span>' : ''}
                <button onclick="window.loadEmployees(${totalPages})" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                    ${totalPages}
                </button>
            `;
        }
        
        paginationHTML += `
                        <button onclick="window.loadEmployees(${currentPage + 1})" ${currentPage === totalPages ? 'disabled' : ''} class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                            <span class="sr-only">Next</span>
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </nav>
                </div>
            </div>
        `;
        
        paginationContainer.innerHTML = paginationHTML;
    }

    // Attach event listeners to dynamic elements
    function attachEventListeners() {
        // Delete employee
        document.querySelectorAll('.delete-employee').forEach(button => {
            button.addEventListener('click', async function() {
                if (confirm('Are you sure you want to delete this employee?')) {
                    const employeeId = this.getAttribute('data-id');
                    const button = this;
                    
                    try {
                        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                        
                        const response = await fetch(`delete_employee.php?id=${employeeId}`);
                        const data = await response.json();

                        if (data.success) {
                            showToast("Employee deleted successfully!", 'success');
                            loadEmployees(currentPage);
                        } else {
                            showToast('Error: ' + data.message, 'error');
                        }
                    } catch (error) {
                        showToast('Network error: ' + error.message, 'error');
                    }
                }
            });
        });

        // Edit employee
        document.querySelectorAll('.edit-employee').forEach(button => {
            button.addEventListener('click', async function() {
                const employeeId = this.getAttribute('data-id');
                const button = this;
                
                try {
                    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                    
                    const response = await fetch(`get_employee.php?id=${employeeId}`);
                    const data = await response.json();

                    if (data.success && data.employee) {
                        const emp = data.employee;
                        const form = document.getElementById('employeeForm');
                        const modal = document.getElementById('addEmployeeModal');

                        // Fill form
                        form.querySelector('[name="first_name"]').value = emp.first_name || '';
                        form.querySelector('[name="last_name"]').value = emp.last_name || '';
                        form.querySelector('[name="email"]').value = emp.email || '';
                        form.querySelector('[name="employee_id"]').value = emp.employee_id || '';
                        form.querySelector('[name="department"]').value = emp.department || '';
                        form.querySelector('[name="role"]').value = emp.role || '';
                        form.querySelector('[name="category"]').value = emp.category || 'staff';
                        
                        // Add hidden ID field if editing
                        if (!form.querySelector('[name="id"]')) {
                            const idInput = document.createElement('input');
                            idInput.type = 'hidden';
                            idInput.name = 'id';
                            form.appendChild(idInput);
                        }
                        form.querySelector('[name="id"]').value = emp.id;

                        // Update modal title
                        modal.querySelector('h3').textContent = 'Edit Employee';

                        // Show modal
                        modal.classList.remove('hidden');
                    } else {
                        showToast('Error: ' + (data.message || 'Employee not found'), 'error');
                    }
                } catch (error) {
                    showToast('Network error: ' + error.message, 'error');
                } finally {
                    button.innerHTML = '<i class="fas fa-edit"></i>';
                }
            });
        });
    }

    // Initial load
    loadEmployees(currentPage);

    // Make loadEmployees available globally
    window.loadEmployees = loadEmployees;
});
        
</script>

    
</body>
</html>