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
        .leave-status-pending {
            background-color: #fef3c7;
            color: #92400e;
        }
        .leave-status-approved {
            background-color: #d1fae5;
            color: #065f46;
        }
        .leave-status-rejected {
            background-color: #fee2e2;
            color: #991b1b;
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
                    <h1 class="text-2xl font-semibold text-gray-800">Leave Management</h1>
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

            <!-- Leave Management Content -->
            <main class="p-6">
                <!-- Leave Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-blue-100 text-blue-600 mr-4">
                                <i class="fas fa-calendar-day text-xl"></i>
                            </div>
                            <div>
                                <p class="text-gray-500">Annual Leave Balance</p>
                                <h3 class="text-2xl font-bold">21 days</h3>
                                <p class="text-green-500 text-sm">+5 days from last year</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-green-100 text-green-600 mr-4">
                                <i class="fas fa-check-circle text-xl"></i>
                            </div>
                            <div>
                                <p class="text-gray-500">Approved Leaves</p>
                                <h3 class="text-2xl font-bold">45</h3>
                                <p class="text-green-500 text-sm">This year</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-yellow-100 text-yellow-600 mr-4">
                                <i class="fas fa-hourglass-half text-xl"></i>
                            </div>
                            <div>
                                <p class="text-gray-500">Pending Requests</p>
                                <h3 class="text-2xl font-bold">12</h3>
                                <p class="text-red-500 text-sm">Require action</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-red-100 text-red-600 mr-4">
                                <i class="fas fa-times-circle text-xl"></i>
                            </div>
                            <div>
                                <p class="text-gray-500">Rejected Leaves</p>
                                <h3 class="text-2xl font-bold">8</h3>
                                <p class="text-gray-500 text-sm">This year</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Leave Management Tabs -->
                <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
                    <div class="border-b border-gray-200">
                        <nav class="flex -mb-px">
                            <button data-tab="pending" class="tab-button py-4 px-6 text-center border-b-2 font-medium text-sm border-blue-500 text-blue-600">
                                Pending Requests
                            </button>
                            <button data-tab="approved" class="tab-button py-4 px-6 text-center border-b-2 font-medium text-sm border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                                Approved Leaves
                            </button>
                            <button data-tab="rejected" class="tab-button py-4 px-6 text-center border-b-2 font-medium text-sm border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                                Rejected Leaves
                            </button>
                            <button data-tab="all" class="tab-button py-4 px-6 text-center border-b-2 font-medium text-sm border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                                All Leaves
                            </button>
                            <div class="ml-auto p-4">
                                <button id="applyLeaveBtn" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded flex items-center">
                                    <i class="fas fa-plus mr-2"></i> Apply for Leave
                                </button>
                            </div>
                        </nav>
                    </div>

                    <!-- Pending Requests Tab Content -->
                    <div id="pending" class="tab-content active p-6">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Leave Type</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dates</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Duration</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reason</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
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
                                                    <div class="text-sm text-gray-500">Computer Science</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Annual Leave</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <div>Jul 10 - Jul 15, 2023</div>
                                            <div class="text-xs text-gray-400">Applied on Jun 28</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">5 days</td>
                                        <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate">Family vacation planned for summer break</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <button class="text-green-600 hover:text-green-900 mr-3 approve-btn" data-id="1">Approve</button>
                                            <button class="text-red-600 hover:text-red-900 reject-btn" data-id="1">Reject</button>
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
                                                    <div class="text-sm text-gray-500">Mathematics</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Sick Leave</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <div>Jul 5 - Jul 7, 2023</div>
                                            <div class="text-xs text-gray-400">Applied on Jul 3</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">3 days</td>
                                        <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate">Medical procedure scheduled</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <button class="text-green-600 hover:text-green-900 mr-3 approve-btn" data-id="2">Approve</button>
                                            <button class="text-red-600 hover:text-red-900 reject-btn" data-id="2">Reject</button>
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
                                                    <div class="text-sm text-gray-500">Physics</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Conference Leave</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <div>Aug 12 - Aug 18, 2023</div>
                                            <div class="text-xs text-gray-400">Applied on Jun 30</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">7 days</td>
                                        <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate">International Physics Conference in Berlin</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <button class="text-green-600 hover:text-green-900 mr-3 approve-btn" data-id="3">Approve</button>
                                            <button class="text-red-600 hover:text-red-900 reject-btn" data-id="3">Reject</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Approved Leaves Tab Content -->
                    <div id="approved" class="tab-content p-6">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Leave Type</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dates</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Duration</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10">
                                                    <img class="h-10 w-10 rounded-full" src="https://randomuser.me/api/portraits/women/63.jpg" alt="">
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">Dr. Lisa Ray</div>
                                                    <div class="text-sm text-gray-500">Chemistry</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Maternity Leave</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <div>Jun 1 - Sep 30, 2023</div>
                                            <div class="text-xs text-gray-400">Approved on May 15</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">90 days</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full leave-status-approved">Approved</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <button class="text-blue-600 hover:text-blue-900 mr-3">View</button>
                                            <button class="text-yellow-600 hover:text-yellow-900">Revoke</button>
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
                                                    <div class="text-sm text-gray-500">Administration</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Annual Leave</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <div>May 1 - May 14, 2023</div>
                                            <div class="text-xs text-gray-400">Approved on Apr 10</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">14 days</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full leave-status-approved">Approved</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <button class="text-blue-600 hover:text-blue-900 mr-3">View</button>
                                            <button class="text-yellow-600 hover:text-yellow-900">Revoke</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Rejected Leaves Tab Content -->
                    <div id="rejected" class="tab-content p-6">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Leave Type</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dates</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reason</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
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
                                                    <div class="text-sm text-gray-500">Computer Science</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Sabbatical</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <div>Sep 1 - Dec 31, 2023</div>
                                            <div class="text-xs text-gray-400">Rejected on Jun 15</div>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-500 max-w-xs">Research sabbatical request - department needs coverage</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full leave-status-rejected">Rejected</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <button class="text-blue-600 hover:text-blue-900 mr-3">View</button>
                                            <button class="text-green-600 hover:text-green-900">Reconsider</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- All Leaves Tab Content -->
                    <div id="all" class="tab-content p-6">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Leave Type</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dates</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Duration</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10">
                                                    <img class="h-10 w-10 rounded-full" src="https://randomuser.me/api/portraits/women/63.jpg" alt="">
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">Dr. Lisa Ray</div>
                                                    <div class="text-sm text-gray-500">Chemistry</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Maternity Leave</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <div>Jun 1 - Sep 30, 2023</div>
                                            <div class="text-xs text-gray-400">Approved on May 15</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">90 days</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full leave-status-approved">Approved</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <button class="text-blue-600 hover:text-blue-900 mr-3">View</button>
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
                                                    <div class="text-sm text-gray-500">Administration</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Annual Leave</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <div>May 1 - May 14, 2023</div>
                                            <div class="text-xs text-gray-400">Approved on Apr 10</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">14 days</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full leave-status-approved">Approved</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <button class="text-blue-600 hover:text-blue-900 mr-3">View</button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10">
                                                    <img class="h-10 w-10 rounded-full" src="https://randomuser.me/api/portraits/men/32.jpg" alt="">
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">Dr. John Smith</div>
                                                    <div class="text-sm text-gray-500">Computer Science</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Sabbatical</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <div>Sep 1 - Dec 31, 2023</div>
                                            <div class="text-xs text-gray-400">Rejected on Jun 15</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">120 days</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full leave-status-rejected">Rejected</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <button class="text-blue-600 hover:text-blue-900 mr-3">View</button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10">
                                                    <img class="h-10 w-10 rounded-full" src="https://randomuser.me/api/portraits/men/32.jpg" alt="">
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">Dr. John Smith</div>
                                                    <div class="text-sm text-gray-500">Computer Science</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Annual Leave</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <div>Jul 10 - Jul 15, 2023</div>
                                            <div class="text-xs text-gray-400">Pending</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">5 days</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full leave-status-pending">Pending</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <button class="text-blue-600 hover:text-blue-900 mr-3">View</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Leave Calendar -->
                <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
                    <div class="p-6 border-b">
                        <h2 class="text-lg font-semibold">Leave Calendar</h2>
                    </div>
                    <div class="p-6">
                        <div id="leaveCalendar" class="calendar-container"></div>
                    </div>
                </div>

                <!-- Leave Balance Summary -->
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="p-6 border-b">
                        <h2 class="text-lg font-semibold">Leave Balance Summary</h2>
                    </div>
                    <div class="p-6">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Annual Leave</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sick Leave</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Maternity/Paternity</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Study Leave</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Used</th>
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
                                                    <div class="text-sm text-gray-500">Computer Science</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <div class="flex items-center">
                                                <div class="w-full bg-gray-200 rounded-full h-2.5 mr-2">
                                                    <div class="bg-blue-600 h-2.5 rounded-full" style="width: 60%"></div>
                                                </div>
                                                <span>12/21 days</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <div class="flex items-center">
                                                <div class="w-full bg-gray-200 rounded-full h-2.5 mr-2">
                                                    <div class="bg-green-600 h-2.5 rounded-full" style="width: 20%"></div>
                                                </div>
                                                <span>2/10 days</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <div class="flex items-center">
                                                <div class="w-full bg-gray-200 rounded-full h-2.5 mr-2">
                                                    <div class="bg-purple-600 h-2.5 rounded-full" style="width: 0%"></div>
                                                </div>
                                                <span>0/90 days</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <div class="flex items-center">
                                                <div class="w-full bg-gray-200 rounded-full h-2.5 mr-2">
                                                    <div class="bg-yellow-600 h-2.5 rounded-full" style="width: 30%"></div>
                                                </div>
                                                <span>3/10 days</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">17 days</td>
                                    </tr>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10">
                                                    <img class="h-10 w-10 rounded-full" src="https://randomuser.me/api/portraits/women/44.jpg" alt="">
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">Dr. Sarah Johnson</div>
                                                    <div class="text-sm text-gray-500">Mathematics</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <div class="flex items-center">
                                                <div class="w-full bg-gray-200 rounded-full h-2.5 mr-2">
                                                    <div class="bg-blue-600 h-2.5 rounded-full" style="width: 40%"></div>
                                                </div>
                                                <span>8/21 days</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <div class="flex items-center">
                                                <div class="w-full bg-gray-200 rounded-full h-2.5 mr-2">
                                                    <div class="bg-green-600 h-2.5 rounded-full" style="width: 50%"></div>
                                                </div>
                                                <span>5/10 days</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <div class="flex items-center">
                                                <div class="w-full bg-gray-200 rounded-full h-2.5 mr-2">
                                                    <div class="bg-purple-600 h-2.5 rounded-full" style="width: 0%"></div>
                                                </div>
                                                <span>0/90 days</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <div class="flex items-center">
                                                <div class="w-full bg-gray-200 rounded-full h-2.5 mr-2">
                                                    <div class="bg-yellow-600 h-2.5 rounded-full" style="width: 10%"></div>
                                                </div>
                                                <span>1/10 days</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">14 days</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Apply Leave Modal -->
    <div id="applyLeaveModal" class="modal fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-3xl shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center pb-3 border-b">
                <h3 class="text-xl font-semibold">Apply for Leave</h3>
                <button id="closeApplyLeaveModal" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="mt-4">
                <form id="leaveForm">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Leave Type</label>
                            <select class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500" required>
                                <option value="">Select Leave Type</option>
                                <option>Annual Leave</option>
                                <option>Sick Leave</option>
                                <option>Maternity Leave</option>
                                <option>Paternity Leave</option>
                                <option>Study Leave</option>
                                <option>Conference Leave</option>
                                <option>Sabbatical</option>
                                <option>Unpaid Leave</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Employee</label>
                            <select class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500" required>
                                <option value="">Select Employee</option>
                                <option>Dr. John Smith</option>
                                <option>Dr. Sarah Johnson</option>
                                <option>Prof. Michael Brown</option>
                                <option>Dr. Lisa Ray</option>
                                <option>Dr. Robert Wilson</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                            <input type="date" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                            <input type="date" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500" required>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Reason</label>
                            <textarea rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500" required></textarea>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Attachment (if any)</label>
                            <div class="mt-1 flex items-center">
                                <label for="file-upload" class="cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                    <span>Upload a file</span>
                                    <input id="file-upload" name="file-upload" type="file" class="sr-only">
                                </label>
                                <p class="pl-1 text-sm text-gray-500">or drag and drop</p>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">PDF, DOC, JPG up to 5MB</p>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end space-x-3">
                        <button type="button" id="cancelApplyLeave" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Submit Leave Request
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Leave Details Modal -->
    <div id="viewLeaveModal" class="modal fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-2xl shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center pb-3 border-b">
                <h3 class="text-xl font-semibold">Leave Request Details</h3>
                <button id="closeViewLeaveModal" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="mt-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div>
                        <h4 class="text-sm font-medium text-gray-500">Employee</h4>
                        <p class="text-lg">Dr. John Smith</p>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-500">Department</h4>
                        <p class="text-lg">Computer Science</p>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-500">Leave Type</h4>
                        <p class="text-lg">Annual Leave</p>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-500">Status</h4>
                        <p class="text-lg"><span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full leave-status-pending">Pending</span></p>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-500">Start Date</h4>
                        <p class="text-lg">July 10, 2023</p>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-500">End Date</h4>
                        <p class="text-lg">July 15, 2023</p>
                    </div>
                    <div class="md:col-span-2">
                        <h4 class="text-sm font-medium text-gray-500">Duration</h4>
                        <p class="text-lg">5 days</p>
                    </div>
                </div>
                <div class="mb-6">
                    <h4 class="text-sm font-medium text-gray-500">Reason</h4>
                    <p class="text-gray-700 mt-1">Family vacation planned for summer break. We have booked flights and accommodation already.</p>
                </div>
                <div class="mb-6">
                    <h4 class="text-sm font-medium text-gray-500">Attachment</h4>
                    <div class="mt-2 flex items-center">
                        <i class="fas fa-file-pdf text-red-500 text-xl mr-2"></i>
                        <span class="text-blue-600 hover:text-blue-800 cursor-pointer">vacation_booking.pdf</span>
                    </div>
                </div>
                <div class="flex justify-end space-x-3">
                    <button id="approveLeaveBtn" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        Approve
                    </button>
                    <button id="rejectLeaveBtn" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        Reject
                    </button>
                    <button id="closeViewLeaveBtn" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css">
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

        // Tab Switching
        document.querySelectorAll('.tab-button').forEach(button => {
            button.addEventListener('click', function() {
                // Remove active class from all tabs and buttons
                document.querySelectorAll('.tab-button').forEach(btn => {
                    btn.classList.remove('border-blue-500', 'text-blue-600');
                    btn.classList.add('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
                });
                
                // Add active class to clicked button
                this.classList.remove('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
                this.classList.add('border-blue-500', 'text-blue-600');
                
                // Hide all tab contents
                document.querySelectorAll('.tab-content').forEach(content => {
                    content.classList.remove('active');
                });
                
                // Show the selected tab content
                const tabId = this.getAttribute('data-tab');
                document.getElementById(tabId).classList.add('active');
            });
        });

        // Apply Leave Modal
        const applyLeaveBtn = document.getElementById('applyLeaveBtn');
        const applyLeaveModal = document.getElementById('applyLeaveModal');
        const closeApplyLeaveModal = document.getElementById('closeApplyLeaveModal');
        const cancelApplyLeave = document.getElementById('cancelApplyLeave');
        const leaveForm = document.getElementById('leaveForm');

        applyLeaveBtn.addEventListener('click', () => {
            applyLeaveModal.classList.remove('hidden');
        });

        closeApplyLeaveModal.addEventListener('click', () => {
            applyLeaveModal.classList.add('hidden');
        });

        cancelApplyLeave.addEventListener('click', () => {
            applyLeaveModal.classList.add('hidden');
        });

        leaveForm.addEventListener('submit', (e) => {
            e.preventDefault();
            alert('Leave request submitted successfully!');
            applyLeaveModal.classList.add('hidden');
            leaveForm.reset();
        });

        // View Leave Modal
        const viewLeaveModal = document.getElementById('viewLeaveModal');
        const closeViewLeaveModal = document.getElementById('closeViewLeaveModal');
        const closeViewLeaveBtn = document.getElementById('closeViewLeaveBtn');

        // Simulate opening view modal when clicking on view buttons
        document.querySelectorAll('[data-leave-id]').forEach(btn => {
            btn.addEventListener('click', () => {
                viewLeaveModal.classList.remove('hidden');
            });
        });

        closeViewLeaveModal.addEventListener('click', () => {
            viewLeaveModal.classList.add('hidden');
        });

        closeViewLeaveBtn.addEventListener('click', () => {
            viewLeaveModal.classList.add('hidden');
        });

        // Approve/Reject Leave Buttons
        document.querySelectorAll('.approve-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const leaveId = this.getAttribute('data-id');
                if (confirm('Are you sure you want to approve this leave request?')) {
                    alert(`Leave request #${leaveId} approved successfully!`);
                    // In a real app, you would update the UI here
                }
            });
        });

        document.querySelectorAll('.reject-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const leaveId = this.getAttribute('data-id');
                if (confirm('Are you sure you want to reject this leave request?')) {
                    alert(`Leave request #${leaveId} rejected.`);
                    // In a real app, you would update the UI here
                }
            });
        });

        // Initialize Leave Calendar
        document.addEventListener('DOMContentLoaded', function() {
            const calendarEl = document.getElementById('leaveCalendar');
            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                events: [
                    {
                        title: 'John Smith - Annual Leave',
                        start: '2023-07-10',
                        end: '2023-07-15',
                        color: '#3B82F6'
                    },
                    {
                        title: 'Sarah Johnson - Sick Leave',
                        start: '2023-07-05',
                        end: '2023-07-07',
                        color: '#10B981'
                    },
                    {
                        title: 'Lisa Ray - Maternity Leave',
                        start: '2023-06-01',
                        end: '2023-08-31',
                        color: '#8B5CF6'
                    },
                    {
                        title: 'Michael Brown - Conference',
                        start: '2023-08-12',
                        end: '2023-08-18',
                        color: '#F59E0B'
                    }
                ]
            });
            calendar.render();
        });

        // Close modals when clicking outside
        window.addEventListener('click', (e) => {
            if (e.target === applyLeaveModal) {
                applyLeaveModal.classList.add('hidden');
            }
            if (e.target === viewLeaveModal) {
                viewLeaveModal.classList.add('hidden');
            }
        });
    </script>
</body>
</html>