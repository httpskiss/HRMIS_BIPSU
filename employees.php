<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit;
}

require 'auth/db.php'; // Database connection

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                addEmployee($pdo);
                break;
            case 'edit':
                editEmployee($pdo);
                break;
            case 'delete':
                deleteEmployee($pdo);
                break;
        }
    }
}

// Fetch all employees
$employees = $pdo->query("
    SELECT id, first_name, last_name, email, employee_id, department, role, category, created_at 
    FROM users 
    ORDER BY created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);

// Fetch unique departments for filter
$departments = $pdo->query("SELECT DISTINCT department FROM users ORDER BY department")->fetchAll(PDO::FETCH_COLUMN);

function addEmployee($pdo) {
    $required = ['first_name', 'last_name', 'email', 'employee_id', 'department', 'role'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            echo json_encode(['success' => false, 'message' => "$field is required"]);
            return;
        }
    }

    try {
        $stmt = $pdo->prepare("
            INSERT INTO users 
            (first_name, last_name, email, password, employee_id, department, role, category, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $password = password_hash('default123', PASSWORD_DEFAULT); // Default password, should be changed
        $category = $_POST['category'] ?? 'staff';
        
        $stmt->execute([
            $_POST['first_name'],
            $_POST['last_name'],
            $_POST['email'],
            $password,
            $_POST['employee_id'],
            $_POST['department'],
            $_POST['role'],
            $category
        ]);
        
        echo json_encode(['success' => true, 'message' => 'Employee added successfully']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error adding employee: ' . $e->getMessage()]);
    }
}

function editEmployee($pdo) {
    if (empty($_POST['id'])) {
        echo json_encode(['success' => false, 'message' => 'Employee ID is required']);
        return;
    }

    try {
        $stmt = $pdo->prepare("
            UPDATE users SET 
                first_name = ?,
                last_name = ?,
                email = ?,
                employee_id = ?,
                department = ?,
                role = ?,
                category = ?
            WHERE id = ?
        ");
        
        $stmt->execute([
            $_POST['first_name'],
            $_POST['last_name'],
            $_POST['email'],
            $_POST['employee_id'],
            $_POST['department'],
            $_POST['role'],
            $_POST['category'] ?? 'staff',
            $_POST['id']
        ]);
        
        echo json_encode(['success' => true, 'message' => 'Employee updated successfully']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error updating employee: ' . $e->getMessage()]);
    }
}

function deleteEmployee($pdo) {
    if (empty($_POST['id'])) {
        echo json_encode(['success' => false, 'message' => 'Employee ID is required']);
        return;
    }

    try {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$_POST['id']]);
        
        echo json_encode(['success' => true, 'message' => 'Employee deleted successfully']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error deleting employee: ' . $e->getMessage()]);
    }
}

$departments = $pdo->query("SELECT DISTINCT department FROM users WHERE department IS NOT NULL ORDER BY department")->fetchAll(PDO::FETCH_COLUMN);
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
        .employee-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        [x-cloak] { display: none !important; }

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
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>
<body class="bg-gray-100" x-data="employeeModule()" x-cloak>
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
                    <h1 class="text-2xl font-semibold text-gray-800">Employee Management</h1>
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

            <!-- Employees Content -->
            <main class="p-6">
                <!-- Employee Actions -->
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
                    <div class="w-full md:w-auto">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-gray-400"></i>
                            </div>
                            <input 
                                x-model="searchQuery" 
                                @input="filterEmployees()"
                                type="text" 
                                class="block w-full md:w-64 pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" 
                                placeholder="Search employees..."
                            >
                        </div>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-3 w-full md:w-auto">
                        <select 
                            x-model="selectedDepartment" 
                            @change="filterEmployees()"
                            class="block w-full md:w-48 pl-3 pr-10 py-2 text-base border border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
                        >
                            <option value="">All Departments</option>
                            <template x-for="dept in departments" :key="dept">
                                <option x-text="dept" :value="dept"></option>
                            </template>
                        </select>
                        <select 
                            x-model="selectedStatus" 
                            @change="filterEmployees()"
                            class="block w-full md:w-40 pl-3 pr-10 py-2 text-base border border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
                        >
                            <option value="">All Status</option>
                            <option>Active</option>
                            <option>On Leave</option>
                            <option>Terminated</option>
                            <option>Suspended</option>
                        </select>
                        <button 
                            @click="openAddEmployeeModal()"
                            class="w-full md:w-auto bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded flex items-center justify-center"
                        >
                            <i class="fas fa-plus mr-2"></i> Add Employee
                        </button>
                    </div>
                </div>

                <!-- View Toggle -->
                <div class="flex mb-6 border-b border-gray-200">
                    <button 
                        @click="viewMode = 'list'" 
                        :class="{'border-b-2 border-blue-500 text-blue-600': viewMode === 'list'}" 
                        class="mr-4 py-2 px-1 text-sm font-medium"
                    >
                        <i class="fas fa-list mr-2"></i> List View
                    </button>
                    <button 
                        @click="viewMode = 'grid'" 
                        :class="{'border-b-2 border-blue-500 text-blue-600': viewMode === 'grid'}" 
                        class="mr-4 py-2 px-1 text-sm font-medium"
                    >
                        <i class="fas fa-th-large mr-2"></i> Grid View
                    </button>
                </div>

                <!-- Employee List View -->
                <div x-show="viewMode === 'list'" class="bg-white rounded-lg shadow overflow-hidden mb-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" @click="sortEmployees('id')">
                                        Employee ID
                                        <i class="fas fa-sort ml-1" :class="{'fa-sort-up': sortColumn === 'id' && sortDirection === 'asc', 'fa-sort-down': sortColumn === 'id' && sortDirection === 'desc'}"></i>
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" @click="sortEmployees('name')">
                                        Name
                                        <i class="fas fa-sort ml-1" :class="{'fa-sort-up': sortColumn === 'name' && sortDirection === 'asc', 'fa-sort-down': sortColumn === 'name' && sortDirection === 'desc'}"></i>
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" @click="sortEmployees('department')">
                                        Department
                                        <i class="fas fa-sort ml-1" :class="{'fa-sort-up': sortColumn === 'department' && sortDirection === 'asc', 'fa-sort-down': sortColumn === 'department' && sortDirection === 'desc'}"></i>
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" @click="sortEmployees('position')">
                                        Position
                                        <i class="fas fa-sort ml-1" :class="{'fa-sort-up': sortColumn === 'position' && sortDirection === 'asc', 'fa-sort-down': sortColumn === 'position' && sortDirection === 'desc'}"></i>
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" @click="sortEmployees('status')">
                                        Status
                                        <i class="fas fa-sort ml-1" :class="{'fa-sort-up': sortColumn === 'status' && sortDirection === 'asc', 'fa-sort-down': sortColumn === 'status' && sortDirection === 'desc'}"></i>
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <template x-for="employee in filteredEmployees" :key="employee.id">
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900" x-text="employee.id"></td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10">
                                                    <img class="h-10 w-10 rounded-full" :src="employee.photo" :alt="employee.name">
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900" x-text="employee.name"></div>
                                                    <div class="text-sm text-gray-500" x-text="employee.email"></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="employee.department"></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="employee.position"></td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span 
                                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" 
                                                :class="{
                                                    'bg-green-100 text-green-800': employee.status === 'Active',
                                                    'bg-yellow-100 text-yellow-800': employee.status === 'On Leave',
                                                    'bg-red-100 text-red-800': employee.status === 'Terminated',
                                                    'bg-gray-100 text-gray-800': employee.status === 'Suspended'
                                                }"
                                                x-text="employee.status"
                                            ></span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <button 
                                                @click="viewEmployee(employee.id)"
                                                class="text-blue-600 hover:text-blue-900 mr-3"
                                            >
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button 
                                                @click="editEmployee(employee.id)"
                                                class="text-yellow-600 hover:text-yellow-900 mr-3"
                                            >
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button 
                                                @click="confirmDeleteEmployee(employee.id)"
                                                class="text-red-600 hover:text-red-900"
                                            >
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                                <template x-if="filteredEmployees.length === 0">
                                    <tr>
                                        <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                                            No employees found matching your criteria.
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                    <div class="bg-gray-50 px-6 py-3 flex items-center justify-between border-t border-gray-200">
                        <div class="flex-1 flex justify-between sm:hidden">
                            <button 
                                @click="currentPage = Math.max(1, currentPage - 1)"
                                :disabled="currentPage === 1"
                                class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                            >
                                Previous
                            </button>
                            <button 
                                @click="currentPage = Math.min(totalPages, currentPage + 1)"
                                :disabled="currentPage === totalPages"
                                class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                            >
                                Next
                            </button>
                        </div>
                        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm text-gray-700">
                                    Showing <span class="font-medium" x-text="(currentPage - 1) * pageSize + 1"></span> to 
                                    <span class="font-medium" x-text="Math.min(currentPage * pageSize, filteredEmployees.length)"></span> of 
                                    <span class="font-medium" x-text="filteredEmployees.length"></span> employees
                                </p>
                            </div>
                            <div>
                                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                                    <button 
                                        @click="currentPage = 1"
                                        :disabled="currentPage === 1"
                                        class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50"
                                    >
                                        <span class="sr-only">First</span>
                                        <i class="fas fa-angle-double-left"></i>
                                    </button>
                                    <button 
                                        @click="currentPage = Math.max(1, currentPage - 1)"
                                        :disabled="currentPage === 1"
                                        class="relative inline-flex items-center px-2 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50"
                                    >
                                        <span class="sr-only">Previous</span>
                                        <i class="fas fa-chevron-left"></i>
                                    </button>
                                    <template x-for="page in visiblePages" :key="page">
                                        <button 
                                            @click="currentPage = page"
                                            :class="{'z-10 bg-blue-50 border-blue-500 text-blue-600': currentPage === page}"
                                            class="bg-white border-gray-300 text-gray-500 hover:bg-gray-50 relative inline-flex items-center px-4 py-2 border text-sm font-medium"
                                            x-text="page"
                                        ></button>
                                    </template>
                                    <button 
                                        @click="currentPage = Math.min(totalPages, currentPage + 1)"
                                        :disabled="currentPage === totalPages"
                                        class="relative inline-flex items-center px-2 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50"
                                    >
                                        <span class="sr-only">Next</span>
                                        <i class="fas fa-chevron-right"></i>
                                    </button>
                                    <button 
                                        @click="currentPage = totalPages"
                                        :disabled="currentPage === totalPages"
                                        class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50"
                                    >
                                        <span class="sr-only">Last</span>
                                        <i class="fas fa-angle-double-right"></i>
                                    </button>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Employee Grid View -->
                <div x-show="viewMode === 'grid'" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    <template x-for="employee in filteredEmployees" :key="employee.id">
                        <div class="employee-card bg-white rounded-lg shadow overflow-hidden transition duration-300 ease-in-out">
                            <div class="p-4 border-b">
                                <div class="flex items-center">
                                    <img class="w-16 h-16 rounded-full mr-4" :src="employee.photo" :alt="employee.name">
                                    <div>
                                        <h3 class="text-lg font-semibold text-gray-900" x-text="employee.name"></h3>
                                        <p class="text-sm text-gray-500" x-text="employee.position"></p>
                                        <span 
                                            class="mt-1 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" 
                                            :class="{
                                                'bg-green-100 text-green-800': employee.status === 'Active',
                                                'bg-yellow-100 text-yellow-800': employee.status === 'On Leave',
                                                'bg-red-100 text-red-800': employee.status === 'Terminated',
                                                'bg-gray-100 text-gray-800': employee.status === 'Suspended'
                                            }"
                                            x-text="employee.status"
                                        ></span>
                                    </div>
                                </div>
                            </div>
                            <div class="p-4">
                                <div class="flex items-center text-sm text-gray-500 mb-2">
                                    <i class="fas fa-id-card mr-2"></i>
                                    <span x-text="employee.id"></span>
                                </div>
                                <div class="flex items-center text-sm text-gray-500 mb-2">
                                    <i class="fas fa-building mr-2"></i>
                                    <span x-text="employee.department"></span>
                                </div>
                                <div class="flex items-center text-sm text-gray-500 mb-2">
                                    <i class="fas fa-envelope mr-2"></i>
                                    <span x-text="employee.email"></span>
                                </div>
                                <div class="flex items-center text-sm text-gray-500">
                                    <i class="fas fa-phone mr-2"></i>
                                    <span x-text="employee.phone"></span>
                                </div>
                            </div>
                            <div class="bg-gray-50 px-4 py-3 flex justify-end space-x-2">
                                <button 
                                    @click="viewEmployee(employee.id)"
                                    class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded text-blue-700 bg-blue-100 hover:bg-blue-200 focus:outline-none"
                                >
                                    <i class="fas fa-eye mr-1"></i> View
                                </button>
                                <button 
                                    @click="editEmployee(employee.id)"
                                    class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded text-yellow-700 bg-yellow-100 hover:bg-yellow-200 focus:outline-none"
                                >
                                    <i class="fas fa-edit mr-1"></i> Edit
                                </button>
                                <button 
                                    @click="confirmDeleteEmployee(employee.id)"
                                    class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none"
                                >
                                    <i class="fas fa-trash mr-1"></i> Delete
                                </button>
                            </div>
                        </div>
                    </template>
                    <template x-if="filteredEmployees.length === 0">
                        <div class="col-span-full text-center py-10">
                            <i class="fas fa-users-slash text-4xl text-gray-400 mb-3"></i>
                            <p class="text-gray-500">No employees found matching your criteria.</p>
                        </div>
                    </template>
                </div>
            </main>
        </div>
    </div>

    <!-- Add/Edit Employee Modal -->
    <div 
        x-show="isEmployeeModalOpen" 
        @keydown.escape="closeEmployeeModal()"
        class="modal fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50"
    >
        <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-4xl shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center pb-3 border-b">
                <h3 class="text-xl font-semibold" x-text="isEditing ? 'Edit Employee' : 'Add New Employee'"></h3>
                <button @click="closeEmployeeModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="mt-4">
                <form @submit.prevent="saveEmployee()">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Personal Information -->
                        <div class="md:col-span-2">
                            <h4 class="text-lg font-medium text-gray-900 mb-3">Personal Information</h4>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">First Name *</label>
                            <input 
                                x-model="currentEmployee.firstName"
                                type="text" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500" 
                                required
                            >
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Last Name *</label>
                            <input 
                                x-model="currentEmployee.lastName"
                                type="text" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500" 
                                required
                            >
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Gender</label>
                            <select 
                                x-model="currentEmployee.gender"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500"
                            >
                                <option value="">Select Gender</option>
                                <option>Male</option>
                                <option>Female</option>
                                <option>Other</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Date of Birth</label>
                            <input 
                                x-model="currentEmployee.dob"
                                type="date" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500"
                            >
                        </div>
                        
                        <!-- Contact Information -->
                        <div class="md:col-span-2 mt-4">
                            <h4 class="text-lg font-medium text-gray-900 mb-3">Contact Information</h4>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                            <input 
                                x-model="currentEmployee.email"
                                type="email" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500" 
                                required
                            >
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Phone *</label>
                            <input 
                                x-model="currentEmployee.phone"
                                type="tel" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500" 
                                required
                            >
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                            <textarea 
                                x-model="currentEmployee.address"
                                rows="2" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500"
                            ></textarea>
                        </div>
                        
                        <!-- Employment Information -->
                        <div class="md:col-span-2 mt-4">
                            <h4 class="text-lg font-medium text-gray-900 mb-3">Employment Information</h4>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Employee ID *</label>
                            <input 
                                x-model="currentEmployee.id"
                                type="text" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500" 
                                required
                                :readonly="isEditing"
                            >
                        </div>
                       <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Department *</label>
                        <select 
                            x-model="currentEmployee.department"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500" 
                            required
                        >
                            <option value="">Select Department</option>
                            <template x-for="dept in departments" :key="dept">
                                <option x-text="dept" :value="dept"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Position *</label>
                        <select 
                            x-model="currentEmployee.position"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500" 
                            required
                        >
                            <option value="">Select Position</option>
                            <option value="employee">Employee</option>
                            <option value="manager">Manager</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status *</label>
                            <select 
                                x-model="currentEmployee.status"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500" 
                                required
                            >
                                <option value="">Select Status</option>
                                <option>Active</option>
                                <option>On Leave</option>
                                <option>Suspended</option>
                                <option>Terminated</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Hire Date *</label>
                            <input 
                                x-model="currentEmployee.hireDate"
                                type="date" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500" 
                                required
                            >
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Salary ($)</label>
                            <input 
                                x-model="currentEmployee.salary"
                                type="number" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500"
                            >
                        </div>
                        
                        <!-- Emergency Contact -->
                        <div class="md:col-span-2 mt-4">
                            <h4 class="text-lg font-medium text-gray-900 mb-3">Emergency Contact</h4>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Emergency Contact Name</label>
                            <input 
                                x-model="currentEmployee.emergencyContact.name"
                                type="text" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500"
                            >
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Emergency Contact Phone</label>
                            <input 
                                x-model="currentEmployee.emergencyContact.phone"
                                type="tel" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500"
                            >
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Emergency Contact Relationship</label>
                            <input 
                                x-model="currentEmployee.emergencyContact.relationship"
                                type="text" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500"
                            >
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end space-x-3">
                        <button 
                            type="button" 
                            @click="closeEmployeeModal()"
                            class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                        >
                            Cancel
                        </button>
                        <button 
                            type="submit" 
                            class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                        >
                            Save Employee
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Employee Modal -->
    <div 
        x-show="isViewModalOpen" 
        @keydown.escape="closeViewModal()"
        class="modal fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50"
    >
        <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-4xl shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center pb-3 border-b">
                <h3 class="text-xl font-semibold">Employee Details</h3>
                <button @click="closeViewModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="mt-4">
                <div class="flex flex-col md:flex-row gap-6">
                    <div class="w-full md:w-1/3">
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="flex flex-col items-center">
                                <img class="w-32 h-32 rounded-full mb-4" :src="viewedEmployee.photo" :alt="viewedEmployee.name">
                                <h3 class="text-lg font-semibold text-gray-900" x-text="viewedEmployee.name"></h3>
                                <p class="text-sm text-gray-500" x-text="viewedEmployee.position"></p>
                                <span 
                                    class="mt-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" 
                                    :class="{
                                        'bg-green-100 text-green-800': viewedEmployee.status === 'Active',
                                        'bg-yellow-100 text-yellow-800': viewedEmployee.status === 'On Leave',
                                        'bg-red-100 text-red-800': viewedEmployee.status === 'Terminated',
                                        'bg-gray-100 text-gray-800': viewedEmployee.status === 'Suspended'
                                    }"
                                    x-text="viewedEmployee.status"
                                ></span>
                            </div>
                            <div class="mt-6 space-y-3">
                                <div>
                                    <p class="text-xs font-medium text-gray-500 uppercase">Employee ID</p>
                                    <p class="text-sm" x-text="viewedEmployee.id"></p>
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-gray-500 uppercase">Department</p>
                                    <p class="text-sm" x-text="viewedEmployee.department"></p>
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-gray-500 uppercase">Hire Date</p>
                                    <p class="text-sm" x-text="viewedEmployee.hireDate"></p>
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-gray-500 uppercase">Salary</p>
                                    <p class="text-sm" x-text="'$' + viewedEmployee.salary"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="w-full md:w-2/3">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Personal Information -->
                            <div class="md:col-span-2">
                                <h4 class="text-lg font-medium text-gray-900 mb-3 border-b pb-2">Personal Information</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-xs font-medium text-gray-500 uppercase">First Name</p>
                                        <p class="text-sm" x-text="viewedEmployee.firstName"></p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-medium text-gray-500 uppercase">Last Name</p>
                                        <p class="text-sm" x-text="viewedEmployee.lastName"></p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-medium text-gray-500 uppercase">Gender</p>
                                        <p class="text-sm" x-text="viewedEmployee.gender"></p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-medium text-gray-500 uppercase">Date of Birth</p>
                                        <p class="text-sm" x-text="viewedEmployee.dob"></p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Contact Information -->
                            <div class="md:col-span-2">
                                <h4 class="text-lg font-medium text-gray-900 mb-3 border-b pb-2">Contact Information</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-xs font-medium text-gray-500 uppercase">Email</p>
                                        <p class="text-sm" x-text="viewedEmployee.email"></p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-medium text-gray-500 uppercase">Phone</p>
                                        <p class="text-sm" x-text="viewedEmployee.phone"></p>
                                    </div>
                                    <div class="md:col-span-2">
                                        <p class="text-xs font-medium text-gray-500 uppercase">Address</p>
                                        <p class="text-sm" x-text="viewedEmployee.address"></p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Emergency Contact -->
                            <div class="md:col-span-2">
                                <h4 class="text-lg font-medium text-gray-900 mb-3 border-b pb-2">Emergency Contact</h4>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <p class="text-xs font-medium text-gray-500 uppercase">Name</p>
                                        <p class="text-sm" x-text="viewedEmployee.emergencyContact.name"></p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-medium text-gray-500 uppercase">Phone</p>
                                        <p class="text-sm" x-text="viewedEmployee.emergencyContact.phone"></p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-medium text-gray-500 uppercase">Relationship</p>
                                        <p class="text-sm" x-text="viewedEmployee.emergencyContact.relationship"></p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Documents -->
                            <div class="md:col-span-2">
                                <h4 class="text-lg font-medium text-gray-900 mb-3 border-b pb-2">Documents</h4>
                                <div class="space-y-2">
                                    <div class="flex items-center justify-between p-2 border rounded">
                                        <div class="flex items-center">
                                            <i class="fas fa-file-pdf text-red-500 mr-2"></i>
                                            <span class="text-sm">Employment_Contract.pdf</span>
                                        </div>
                                        <button class="text-blue-600 hover:text-blue-800 text-sm">
                                            <i class="fas fa-download mr-1"></i> Download
                                        </button>
                                    </div>
                                    <div class="flex items-center justify-between p-2 border rounded">
                                        <div class="flex items-center">
                                            <i class="fas fa-file-word text-blue-500 mr-2"></i>
                                            <span class="text-sm">CV_Resume.docx</span>
                                        </div>
                                        <button class="text-blue-600 hover:text-blue-800 text-sm">
                                            <i class="fas fa-download mr-1"></i> Download
                                        </button>
                                    </div>
                                    <div class="flex items-center justify-between p-2 border rounded">
                                        <div class="flex items-center">
                                            <i class="fas fa-file-image text-green-500 mr-2"></i>
                                            <span class="text-sm">ID_Proof.jpg</span>
                                        </div>
                                        <button class="text-blue-600 hover:text-blue-800 text-sm">
                                            <i class="fas fa-download mr-1"></i> Download
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-6 flex justify-end">
                <button 
                    @click="closeViewModal()"
                    class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                >
                    Close
                </button>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div 
        x-show="isDeleteModalOpen" 
        @keydown.escape="closeDeleteModal()"
        class="modal fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50"
    >
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center pb-3 border-b">
                <h3 class="text-lg font-semibold">Confirm Deletion</h3>
                <button @click="closeDeleteModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="mt-4">
                <p class="text-gray-700">Are you sure you want to delete employee <span class="font-semibold" x-text="employeeToDeleteName"></span>? This action cannot be undone.</p>
                <div class="mt-6 flex justify-end space-x-3">
                    <button 
                        @click="closeDeleteModal()"
                        class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    >
                        Cancel
                    </button>
                    <button 
                        @click="deleteEmployee()"
                        class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                    >
                        Delete Employee
                    </button>
                </div>
            </div>
        </div>
    </div>

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

        // Dropdown Menu
        document.querySelectorAll('.dropdown').forEach(dropdown => {
            dropdown.addEventListener('mouseenter', function() {
                this.querySelector('.dropdown-menu').classList.remove('hidden');
            });
            dropdown.addEventListener('mouseleave', function() {
                this.querySelector('.dropdown-menu').classList.add('hidden');
            });
        });

        // Employee Module Functionality
        function employeeModule() {
        return {
            // Data
            employees: <?= json_encode(array_map(function($emp) {
                return [
                    'id' => $emp['employee_id'],
                    'firstName' => $emp['first_name'],
                    'lastName' => $emp['last_name'],
                    'name' => $emp['first_name'] . ' ' . $emp['last_name'],
                    'email' => $emp['email'],
                    'phone' => '', // Add if you have phone in DB
                    'gender' => '', // Add if you have gender in DB
                    'photo' => 'https://ui-avatars.com/api/?name=' . urlencode($emp['first_name'] . '+' . $emp['last_name']),
                    'department' => $emp['department'],
                    'position' => $emp['role'],
                    'status' => 'Active', // Add status field if you have it in DB
                    'category' => $emp['category'] ?? 'staff',
                    'hireDate' => $emp['created_at'],
                    'emergencyContact' => [
                        'name' => '',
                        'phone' => '',
                        'relationship' => ''
                    ]
                ];
            }, $employees)) ?>,
            departments: <?= json_encode($departments) ?>,
            filteredEmployees: [],
            currentEmployee: {
                id: '',
                firstName: '',
                lastName: '',
                name: '',
                email: '',
                phone: '',
                gender: '',
                photo: '',
                department: '',
                position: '',
                status: '',
                hireDate: '',
                category: 'staff',
                emergencyContact: {
                    name: '',
                    phone: '',
                    relationship: ''
                }
            },
            viewedEmployee: {},
            employeeToDelete: null,
            employeeToDeleteName: '',
            searchQuery: '',
            selectedDepartment: '',
            selectedStatus: '',
            viewMode: 'list',
            isEmployeeModalOpen: false,
            isViewModalOpen: false,
            isDeleteModalOpen: false,
            isEditing: false,
            sortColumn: 'id',
            sortDirection: 'asc',
            pageSize: 8,
            currentPage: 1,
            visiblePages: [1, 2, 3, 4, 5],


             // Methods
            init() {
                this.filterEmployees();
            },

            filterEmployees() {
                this.filteredEmployees = this.employees.filter(employee => {
                    const matchesSearch = 
                        employee.name.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
                        employee.email.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
                        employee.id.toLowerCase().includes(this.searchQuery.toLowerCase());
                    
                    const matchesDepartment = 
                        !this.selectedDepartment || 
                        employee.department === this.selectedDepartment;
                    
                    const matchesStatus = 
                        !this.selectedStatus || 
                        employee.status === this.selectedStatus;
                    
                    return matchesSearch && matchesDepartment && matchesStatus;
                });

                this.sortEmployees(this.sortColumn, false);
                this.currentPage = 1;
                this.updateVisiblePages();
            },

                generateEmployeePhotos() {
                    // Generate random photos for employees who don't have one
                    this.employees.forEach(employee => {
                        if (!employee.photo) {
                            const gender = employee.gender.toLowerCase();
                            const randomId = Math.floor(Math.random() * 100);
                            employee.photo = `https://randomuser.me/api/portraits/${gender}/${randomId}.jpg`;
                        }
                    });
                },

                filterEmployees() {
                    // Apply filters based on search query and selected filters
                    this.filteredEmployees = this.employees.filter(employee => {
                        const matchesSearch = 
                            employee.name.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
                            employee.email.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
                            employee.id.toLowerCase().includes(this.searchQuery.toLowerCase());
                        
                        const matchesDepartment = 
                            !this.selectedDepartment || 
                            employee.department === this.selectedDepartment;
                        
                        const matchesStatus = 
                            !this.selectedStatus || 
                            employee.status === this.selectedStatus;
                        
                        return matchesSearch && matchesDepartment && matchesStatus;
                    });

                    // Sort the filtered employees
                    this.sortEmployees(this.sortColumn, false);

                    // Reset to first page when filters change
                    this.currentPage = 1;
                    this.updateVisiblePages();
                },

                sortEmployees(column, updateDirection = true) {
                    if (updateDirection) {
                        if (this.sortColumn === column) {
                            this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
                        } else {
                            this.sortColumn = column;
                            this.sortDirection = 'asc';
                        }
                    }

                    this.filteredEmployees.sort((a, b) => {
                        let valueA = a[column];
                        let valueB = b[column];

                        // Special handling for name which is a combination of first and last
                        if (column === 'name') {
                            valueA = `${a.lastName}, ${a.firstName}`;
                            valueB = `${b.lastName}, ${b.firstName}`;
                        }

                        if (valueA < valueB) {
                            return this.sortDirection === 'asc' ? -1 : 1;
                        }
                        if (valueA > valueB) {
                            return this.sortDirection === 'asc' ? 1 : -1;
                        }
                        return 0;
                    });
                },

                get paginatedEmployees() {
                    const start = (this.currentPage - 1) * this.pageSize;
                    const end = start + this.pageSize;
                    return this.filteredEmployees.slice(start, end);
                },

                get totalPages() {
                    return Math.ceil(this.filteredEmployees.length / this.pageSize);
                },

                updateVisiblePages() {
                    const halfRange = 2;
                    let start = Math.max(1, this.currentPage - halfRange);
                    let end = Math.min(this.totalPages, this.currentPage + halfRange);

                    if (this.currentPage - halfRange < 1) {
                        end = Math.min(2 * halfRange + 1, this.totalPages);
                    }

                    if (this.currentPage + halfRange > this.totalPages) {
                        start = Math.max(1, this.totalPages - 2 * halfRange);
                    }

                    this.visiblePages = [];
                    for (let i = start; i <= end; i++) {
                        this.visiblePages.push(i);
                    }
                },

                openAddEmployeeModal() {
                    this.isEditing = false;
                    this.currentEmployee = {
                        id: `EMP-${Math.floor(1000 + Math.random() * 9000)}`,
                        firstName: '',
                        lastName: '',
                        name: '',
                        email: '',
                        phone: '',
                        gender: '',
                        dob: '',
                        photo: '',
                        department: '',
                        position: '',
                        status: '',
                        hireDate: '',
                        salary: '',
                        address: '',
                        emergencyContact: {
                            name: '',
                            phone: '',
                            relationship: ''
                        }
                    };
                    this.isEmployeeModalOpen = true;
                },

                editEmployee(id) {
                    const employee = this.employees.find(e => e.id === id);
                    if (employee) {
                        this.currentEmployee = JSON.parse(JSON.stringify(employee));
                        this.isEditing = true;
                        this.isEmployeeModalOpen = true;
                    }
                },

                  saveEmployee() {
                const formData = new FormData();
                formData.append('action', this.isEditing ? 'edit' : 'add');
                
                // Add all employee data to formData
                formData.append('first_name', this.currentEmployee.firstName);
                formData.append('last_name', this.currentEmployee.lastName);
                formData.append('email', this.currentEmployee.email);
                formData.append('employee_id', this.currentEmployee.id);
                formData.append('department', this.currentEmployee.department);
                formData.append('role', this.currentEmployee.position);
                formData.append('category', this.currentEmployee.category);
                
                if (this.isEditing) {
                    formData.append('id', this.currentEmployee.dbId); // You'll need to add dbId to track the database ID
                }

                fetch('employees.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Refresh the employee list
                        window.location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred');
                });
            },


                closeEmployeeModal() {
                    this.isEmployeeModalOpen = false;
                },

                viewEmployee(id) {
                    const employee = this.employees.find(e => e.id === id);
                    if (employee) {
                        this.viewedEmployee = JSON.parse(JSON.stringify(employee));
                        this.isViewModalOpen = true;
                    }
                },

                closeViewModal() {
                    this.isViewModalOpen = false;
                },

                confirmDeleteEmployee(id) {
                    const employee = this.employees.find(e => e.id === id);
                    if (employee) {
                        this.employeeToDelete = id;
                        this.employeeToDeleteName = employee.name;
                        this.isDeleteModalOpen = true;
                    }
                },

                    deleteEmployee() {
                if (!this.employeeToDelete) return;

                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('id', this.employeeToDelete);

                fetch('employees.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Refresh the employee list
                        window.location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred');
                });
            }
          
            }
        }
    </script>
</body>
</html>