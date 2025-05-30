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
        .travel-status-pending {
            background-color: #fef3c7;
            color: #92400e;
        }
        .travel-status-approved {
            background-color: #d1fae5;
            color: #065f46;
        }
        .travel-status-rejected {
            background-color: #fee2e2;
            color: #991b1b;
        }
        .travel-status-completed {
            background-color: #e0e7ff;
            color: #4338ca;
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
                    <h1 class="text-2xl font-semibold text-gray-800">Travel Management</h1>
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

            <!-- Travel Content -->
            <main class="p-6">
                <!-- Travel Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-blue-100 text-blue-600 mr-4">
                                <i class="fas fa-plane-departure text-xl"></i>
                            </div>
                            <div>
                                <p class="text-gray-500">Total Travel Requests</p>
                                <h3 class="text-2xl font-bold">124</h3>
                                <p class="text-green-500 text-sm">+8% from last quarter</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-green-100 text-green-600 mr-4">
                                <i class="fas fa-check-circle text-xl"></i>
                            </div>
                            <div>
                                <p class="text-gray-500">Approved Requests</p>
                                <h3 class="text-2xl font-bold">89</h3>
                                <p class="text-sm text-gray-500">72% approval rate</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-yellow-100 text-yellow-600 mr-4">
                                <i class="fas fa-clock text-xl"></i>
                            </div>
                            <div>
                                <p class="text-gray-500">Pending Requests</p>
                                <h3 class="text-2xl font-bold">22</h3>
                                <p class="text-sm text-gray-500">18% pending rate</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-purple-100 text-purple-600 mr-4">
                                <i class="fas fa-dollar-sign text-xl"></i>
                            </div>
                            <div>
                                <p class="text-gray-500">Travel Budget</p>
                                <h3 class="text-2xl font-bold">$42,500</h3>
                                <p class="text-sm text-gray-500">$12,800 utilized</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Travel Management Section -->
                <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
                    <div class="flex justify-between items-center p-6 border-b">
                        <h2 class="text-lg font-semibold">Travel Requests</h2>
                        <div class="flex space-x-3">
                            <button id="filterTravelBtn" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded flex items-center">
                                <i class="fas fa-filter mr-2"></i> Filter
                            </button>
                            <button id="newTravelBtn" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded flex items-center">
                                <i class="fas fa-plus mr-2"></i> New Request
                            </button>
                        </div>
                    </div>
                    
                    <!-- Travel Tabs -->
                    <div class="border-b border-gray-200">
                        <nav class="flex -mb-px">
                            <button data-tab="all" class="tab-btn py-4 px-6 text-center border-b-2 font-medium text-sm border-blue-500 text-blue-600">
                                All Requests
                            </button>
                            <button data-tab="pending" class="tab-btn py-4 px-6 text-center border-b-2 font-medium text-sm border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                                Pending
                            </button>
                            <button data-tab="approved" class="tab-btn py-4 px-6 text-center border-b-2 font-medium text-sm border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                                Approved
                            </button>
                            <button data-tab="rejected" class="tab-btn py-4 px-6 text-center border-b-2 font-medium text-sm border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                                Rejected
                            </button>
                            <button data-tab="completed" class="tab-btn py-4 px-6 text-center border-b-2 font-medium text-sm border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                                Completed
                            </button>
                        </nav>
                    </div>
                    
                    <!-- Travel Table -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Request ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Purpose</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Destination</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dates</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Budget</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <!-- Pending Request -->
                                <tr class="tab-content all pending">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">TR-2023-001</td>
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
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">International Conference on Computer Science</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Tokyo, Japan</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <div>Oct 15 - Oct 20, 2023</div>
                                        <div class="text-xs text-gray-400">5 days</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">$2,500</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full travel-status-pending">
                                            Pending
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <button class="text-blue-600 hover:text-blue-900 mr-3">View</button>
                                        <button class="text-green-600 hover:text-green-900 mr-3">Approve</button>
                                        <button class="text-red-600 hover:text-red-900">Reject</button>
                                    </td>
                                </tr>
                                
                                <!-- Approved Request -->
                                <tr class="tab-content all approved">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">TR-2023-045</td>
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
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Research Collaboration Meeting</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Singapore</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <div>Sep 5 - Sep 10, 2023</div>
                                        <div class="text-xs text-gray-400">5 days</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">$1,800</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full travel-status-approved">
                                            Approved
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <button class="text-blue-600 hover:text-blue-900 mr-3">View</button>
                                        <button class="text-purple-600 hover:text-purple-900">Complete</button>
                                    </td>
                                </tr>
                                
                                <!-- Rejected Request -->
                                <tr class="tab-content all rejected">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">TR-2023-078</td>
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
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Workshop on Quantum Computing</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Zurich, Switzerland</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <div>Nov 20 - Nov 25, 2023</div>
                                        <div class="text-xs text-gray-400">5 days</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">$3,200</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full travel-status-rejected">
                                            Rejected
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <button class="text-blue-600 hover:text-blue-900 mr-3">View</button>
                                        <button class="text-gray-600 hover:text-gray-900">Archive</button>
                                    </td>
                                </tr>
                                
                                <!-- Completed Request -->
                                <tr class="tab-content all completed">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">TR-2023-012</td>
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
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">International Chemistry Symposium</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Berlin, Germany</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <div>Aug 10 - Aug 15, 2023</div>
                                        <div class="text-xs text-gray-400">5 days</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">$2,100</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full travel-status-completed">
                                            Completed
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <button class="text-blue-600 hover:text-blue-900 mr-3">View</button>
                                        <button class="text-gray-600 hover:text-gray-900">Archive</button>
                                    </td>
                                </tr>
                                
                                <!-- Another Pending Request -->
                                <tr class="tab-content all pending">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">TR-2023-089</td>
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
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">University Partnership Meeting</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Seoul, South Korea</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <div>Dec 5 - Dec 9, 2023</div>
                                        <div class="text-xs text-gray-400">4 days</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">$2,800</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full travel-status-pending">
                                            Pending
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <button class="text-blue-600 hover:text-blue-900 mr-3">View</button>
                                        <button class="text-green-600 hover:text-green-900 mr-3">Approve</button>
                                        <button class="text-red-600 hover:text-red-900">Reject</button>
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
                                    Showing <span class="font-medium">1</span> to <span class="font-medium">5</span> of <span class="font-medium">124</span> requests
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
                
                <!-- Travel Budget Summary -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Budget Utilization -->
                    <div class="bg-white rounded-lg shadow overflow-hidden">
                        <div class="p-6 border-b">
                            <h2 class="text-lg font-semibold">Travel Budget Utilization</h2>
                        </div>
                        <div class="p-6">
                            <div class="mb-4">
                                <div class="flex justify-between mb-2">
                                    <span class="text-sm font-medium text-gray-700">Annual Budget: $42,500</span>
                                    <span class="text-sm font-medium">Utilized: $12,800 (30%)</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2.5">
                                    <div class="bg-blue-600 h-2.5 rounded-full" style="width: 30%"></div>
                                </div>
                            </div>
                            <div class="grid grid-cols-3 gap-4 text-center">
                                <div class="p-3 border rounded-lg">
                                    <div class="text-2xl font-bold text-blue-600">$8,200</div>
                                    <div class="text-xs text-gray-500">Conference Travel</div>
                                </div>
                                <div class="p-3 border rounded-lg">
                                    <div class="text-2xl font-bold text-green-600">$3,500</div>
                                    <div class="text-xs text-gray-500">Research Travel</div>
                                </div>
                                <div class="p-3 border rounded-lg">
                                    <div class="text-2xl font-bold text-purple-600">$1,100</div>
                                    <div class="text-xs text-gray-500">Administrative</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Upcoming Travel -->
                    <div class="bg-white rounded-lg shadow overflow-hidden">
                        <div class="p-6 border-b">
                            <h2 class="text-lg font-semibold">Upcoming Travel</h2>
                        </div>
                        <div class="p-6">
                            <div class="space-y-4">
                                <div class="flex items-center justify-between p-3 border rounded-lg hover:bg-gray-50">
                                    <div class="flex items-center">
                                        <div class="p-2 rounded-full bg-blue-100 text-blue-600 mr-3">
                                            <i class="fas fa-plane"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium">Dr. Sarah Johnson</p>
                                            <p class="text-xs text-gray-500">Singapore • Sep 5-10, 2023</p>
                                        </div>
                                    </div>
                                    <button class="text-blue-600 hover:text-blue-800 text-sm">Details</button>
                                </div>
                                <div class="flex items-center justify-between p-3 border rounded-lg hover:bg-gray-50">
                                    <div class="flex items-center">
                                        <div class="p-2 rounded-full bg-green-100 text-green-600 mr-3">
                                            <i class="fas fa-plane"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium">Prof. David Lee</p>
                                            <p class="text-xs text-gray-500">London, UK • Sep 15-20, 2023</p>
                                        </div>
                                    </div>
                                    <button class="text-blue-600 hover:text-blue-800 text-sm">Details</button>
                                </div>
                                <div class="flex items-center justify-between p-3 border rounded-lg hover:bg-gray-50">
                                    <div class="flex items-center">
                                        <div class="p-2 rounded-full bg-purple-100 text-purple-600 mr-3">
                                            <i class="fas fa-plane"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium">Dr. Maria Garcia</p>
                                            <p class="text-xs text-gray-500">Paris, France • Oct 2-7, 2023</p>
                                        </div>
                                    </div>
                                    <button class="text-blue-600 hover:text-blue-800 text-sm">Details</button>
                                </div>
                            </div>
                            <div class="mt-4 text-center">
                                <button class="text-blue-600 hover:text-blue-800 text-sm font-medium">View All Upcoming Travel</button>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- New Travel Request Modal -->
    <div id="newTravelModal" class="modal fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-4xl shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center pb-3 border-b">
                <h3 class="text-xl font-semibold">New Travel Request</h3>
                <button id="closeTravelModal" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="mt-4">
                <form id="travelForm">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Employee</label>
                            <select class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500" required>
                                <option value="">Select Employee</option>
                                <option>Dr. John Smith (Computer Science)</option>
                                <option>Dr. Sarah Johnson (Mathematics)</option>
                                <option>Prof. Michael Brown (Physics)</option>
                                <option>Dr. Lisa Ray (Chemistry)</option>
                                <option>Dr. Robert Wilson (Administration)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Travel Type</label>
                            <select class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500" required>
                                <option value="">Select Type</option>
                                <option>Conference</option>
                                <option>Research</option>
                                <option>Workshop</option>
                                <option>Administrative</option>
                                <option>Other</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Purpose</label>
                            <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500" placeholder="Brief purpose of travel" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Destination</label>
                            <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500" placeholder="City, Country" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                            <input type="date" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                            <input type="date" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Estimated Budget (USD)</label>
                            <input type="number" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500" placeholder="0.00" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Funding Source</label>
                            <select class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500" required>
                                <option value="">Select Source</option>
                                <option>University Travel Grant</option>
                                <option>Department Budget</option>
                                <option>Research Grant</option>
                                <option>External Funding</option>
                                <option>Personal</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Detailed Description</label>
                        <textarea rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500" placeholder="Provide detailed information about the travel purpose, expected outcomes, etc."></textarea>
                    </div>
                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Supporting Documents</label>
                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                            <div class="space-y-1 text-center">
                                <div class="flex text-sm text-gray-600">
                                    <label for="file-upload" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none">
                                        <span>Upload files</span>
                                        <input id="file-upload" name="file-upload" type="file" class="sr-only" multiple>
                                    </label>
                                    <p class="pl-1">or drag and drop</p>
                                </div>
                                <p class="text-xs text-gray-500">PDF, DOC, JPG up to 10MB</p>
                            </div>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end space-x-3">
                        <button type="button" id="cancelTravelRequest" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Submit Request
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Travel Filter Modal -->
    <div id="filterTravelModal" class="modal fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-2xl shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center pb-3 border-b">
                <h3 class="text-xl font-semibold">Filter Travel Requests</h3>
                <button id="closeFilterModal" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="mt-4">
                <form id="filterForm">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
                                <option value="">All Statuses</option>
                                <option>Pending</option>
                                <option>Approved</option>
                                <option>Rejected</option>
                                <option>Completed</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Travel Type</label>
                            <select class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
                                <option value="">All Types</option>
                                <option>Conference</option>
                                <option>Research</option>
                                <option>Workshop</option>
                                <option>Administrative</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Department</label>
                            <select class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
                                <option value="">All Departments</option>
                                <option>Computer Science</option>
                                <option>Mathematics</option>
                                <option>Physics</option>
                                <option>Chemistry</option>
                                <option>Biology</option>
                                <option>Administration</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Funding Source</label>
                            <select class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
                                <option value="">All Sources</option>
                                <option>University Travel Grant</option>
                                <option>Department Budget</option>
                                <option>Research Grant</option>
                                <option>External Funding</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Date Range</label>
                            <div class="flex space-x-2">
                                <input type="date" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500" placeholder="From">
                                <input type="date" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500" placeholder="To">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Budget Range</label>
                            <div class="flex space-x-2">
                                <input type="number" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500" placeholder="Min">
                                <input type="number" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500" placeholder="Max">
                            </div>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end space-x-3">
                        <button type="button" id="resetFilters" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Reset
                        </button>
                        <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Apply Filters
                        </button>
                    </div>
                </form>
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

        // Tab functionality
        const tabButtons = document.querySelectorAll('.tab-btn');
        tabButtons.forEach(button => {
            button.addEventListener('click', () => {
                // Update active tab styling
                tabButtons.forEach(btn => {
                    btn.classList.remove('border-blue-500', 'text-blue-600');
                    btn.classList.add('border-transparent', 'text-gray-500');
                });
                button.classList.add('border-blue-500', 'text-blue-600');
                button.classList.remove('border-transparent', 'text-gray-500');
                
                // Show/hide table rows based on tab
                const tabName = button.getAttribute('data-tab');
                if (tabName === 'all') {
                    document.querySelectorAll('.tab-content.all').forEach(row => {
                        row.style.display = '';
                    });
                } else {
                    document.querySelectorAll('.tab-content').forEach(row => {
                        row.style.display = 'none';
                    });
                    document.querySelectorAll(`.tab-content.${tabName}`).forEach(row => {
                        row.style.display = '';
                    });
                }
            });
        });

        // New Travel Request Modal
        const newTravelBtn = document.getElementById('newTravelBtn');
        const newTravelModal = document.getElementById('newTravelModal');
        const closeTravelModal = document.getElementById('closeTravelModal');
        const cancelTravelRequest = document.getElementById('cancelTravelRequest');

        newTravelBtn.addEventListener('click', () => {
            newTravelModal.classList.remove('hidden');
        });

        closeTravelModal.addEventListener('click', () => {
            newTravelModal.classList.add('hidden');
        });

        cancelTravelRequest.addEventListener('click', () => {
            newTravelModal.classList.add('hidden');
        });

        // Filter Travel Modal
        const filterTravelBtn = document.getElementById('filterTravelBtn');
        const filterTravelModal = document.getElementById('filterTravelModal');
        const closeFilterModal = document.getElementById('closeFilterModal');
        const resetFilters = document.getElementById('resetFilters');

        filterTravelBtn.addEventListener('click', () => {
            filterTravelModal.classList.remove('hidden');
        });

        closeFilterModal.addEventListener('click', () => {
            filterTravelModal.classList.add('hidden');
        });

        resetFilters.addEventListener('click', () => {
            document.getElementById('filterForm').reset();
        });

        // Close modals when clicking outside
        window.addEventListener('click', (e) => {
            if (e.target === newTravelModal) {
                newTravelModal.classList.add('hidden');
            }
            if (e.target === filterTravelModal) {
                filterTravelModal.classList.add('hidden');
            }
        });

        // Dropdown functionality
        const dropdowns = document.querySelectorAll('.dropdown');
        dropdowns.forEach(dropdown => {
            dropdown.addEventListener('mouseenter', () => {
                dropdown.querySelector('.dropdown-menu').classList.remove('hidden');
            });
            dropdown.addEventListener('mouseleave', () => {
                dropdown.querySelector('.dropdown-menu').classList.add('hidden');
            });
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