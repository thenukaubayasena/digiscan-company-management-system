<?php
session_start();
require 'db_connection.php';

$displayName = isset($_SESSION['FName'], $_SESSION['LName']) ? $_SESSION['FName'] . ' ' . $_SESSION['LName'] : 'Production Supervisor';
$avatar = strtoupper(substr($displayName, 0, 1));

// Initialize message variables
$successMessage = '';
$errorMessage = '';

// Initialize view variables
$showViewAllocatedEquipment = false;
$showManageProductionTasks = false;
$showMonitorMachinePerformance = false;
$showCheckProductionOutput = false;
$showApproveQualityStandards = false;
$showSubmitPerformanceReports = false;

// Determine which view to show based on GET parameter
if (isset($_GET['view'])) {
    $showViewAllocatedEquipment = ($_GET['view'] === 'view_allocated_equipment');
    $showManageProductionTasks = ($_GET['view'] === 'manage_production_tasks');
    $showMonitorMachinePerformance = ($_GET['view'] === 'monitor_machine_performance');
    $showCheckProductionOutput = ($_GET['view'] === 'check_production_output');
    $showApproveQualityStandards = ($_GET['view'] === 'approve_quality_standards');
    $showSubmitPerformanceReports = ($_GET['view'] === 'submit_performance_reports');
} else {
    // Default view: show summary cards
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Add Production Task
        if (isset($_POST['add_task'])) {
            $description = trim($_POST['description']);
            $assigned_to = $_POST['assigned_to'];
            $deadline = $_POST['deadline'];
            if (empty($description) || empty($assigned_to) || empty($deadline)) {
                $errorMessage = "All fields are required.";
                $showManageProductionTasks = true;
            } else {
                $stmt = $pdo->prepare("INSERT INTO production_tasks (description, assigned_to, status, deadline, created_at) VALUES (?, ?, 'Pending', ?, NOW())");
                $success = $stmt->execute([$description, $assigned_to, $deadline]);
                if ($success) {
                    $successMessage = "Task added successfully!";
                    $showManageProductionTasks = true;
                } else {
                    $errorMessage = "Error adding task.";
                    $showManageProductionTasks = true;
                }
            }
        }
        // Approve/Reject Quality Check
        if (isset($_POST['approve_check']) || isset($_POST['reject_check'])) {
            $check_id = $_POST['check_id'];
            $status = isset($_POST['approve_check']) ? 'Approved' : 'Rejected';
            $stmt = $pdo->prepare("UPDATE quality_checks SET status = ? WHERE check_id = ?");
            $success = $stmt->execute([$status, $check_id]);
            if ($success) {
                $successMessage = "Quality check $status successfully!";
                $showApproveQualityStandards = true;
            } else {
                $errorMessage = "Error updating quality check status.";
                $showApproveQualityStandards = true;
            }
        }
        // Submit Performance Report
        if (isset($_POST['submit_report'])) {
            $title = trim($_POST['title']);
            $file_path = $_POST['file_path']; // Placeholder; actual file upload requires server-side handling
            if (empty($title) || empty($file_path)) {
                $errorMessage = "All fields are required.";
                $showSubmitPerformanceReports = true;
            } else {
                $stmt = $pdo->prepare("INSERT INTO performance_reports (title, file_path, submission_date) VALUES (?, ?, NOW())");
                $success = $stmt->execute([$title, $file_path]);
                if ($success) {
                    $successMessage = "Report submitted successfully!";
                    $showSubmitPerformanceReports = true;
                } else {
                    $errorMessage = "Error submitting report.";
                    $showSubmitPerformanceReports = true;
                }
            }
        }
    } catch (PDOException $e) {
        $errorMessage = "Database error: " . $e->getMessage();
        if (isset($_POST['add_task'])) $showManageProductionTasks = true;
        elseif (isset($_POST['approve_check']) || isset($_POST['reject_check'])) $showApproveQualityStandards = true;
        elseif (isset($_POST['submit_report'])) $showSubmitPerformanceReports = true;
    }
}

// Fetch data for views
if ($showViewAllocatedEquipment) {
    $stmt = $pdo->query("SELECT ea.allocation_id, ea.equipment_id, ea.equipment_name, ea.allocation_date, e.FName, e.LName 
                         FROM equipment_allocation ea 
                         JOIN employees e ON ea.assigned_to = e.EMP_ID 
                         ORDER BY ea.allocation_date DESC");
    $allocations = $stmt->fetchAll();
}
if ($showManageProductionTasks) {
    $stmt = $pdo->query("SELECT t.task_id, t.description, t.status, t.deadline, t.created_at, e.FName, e.LName 
                         FROM production_tasks t 
                         JOIN employees e ON t.assigned_to = e.EMP_ID 
                         ORDER BY t.created_at DESC");
    $tasks = $stmt->fetchAll();
    $stmt = $pdo->query("SELECT EMP_ID, FName, LName FROM employees WHERE designation = 'Production Worker'");
    $workers = $stmt->fetchAll();
}
if ($showMonitorMachinePerformance) {
    $stmt = $pdo->query("SELECT * FROM machine_performance ORDER BY recorded_at DESC");
    $performances = $stmt->fetchAll();
}
if ($showCheckProductionOutput) {
    $stmt = $pdo->query("SELECT * FROM production_output ORDER BY production_date DESC");
    $outputs = $stmt->fetchAll();
}
if ($showApproveQualityStandards) {
    $stmt = $pdo->query("SELECT * FROM quality_checks WHERE status = 'Pending' ORDER BY created_at DESC");
    $quality_checks = $stmt->fetchAll();
}
if ($showSubmitPerformanceReports) {
    $stmt = $pdo->query("SELECT * FROM performance_reports ORDER BY submission_date DESC");
    $reports = $stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Production Supervisor Dashboard | Digiscan</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #e74c3c;
            --light-color: #ecf0f1;
            --dark-color: #2c3e50;
            --success-color: #2ecc71;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f5f7fa;
            color: var(--dark-color);
        }
        
        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }
        
        .sidebar {
            width: 250px;
            background-color: var(--primary-color);
            color: white;
            padding: 1.5rem 0;
            position: fixed;
            height: 100%;
        }
        
        .sidebar-header {
            padding: 0 1.5rem 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .sidebar-header h3 {
            color: white;
            margin-bottom: 0.5rem;
        }
        
        .sidebar-header p {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.9rem;
        }
        
        .sidebar-menu {
            padding: 1.5rem 0;
        }
        
        .menu-item {
            padding: 0.8rem 1.5rem;
            display: flex;
            align-items: center;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .menu-item:hover, .menu-item.active {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
        }
        
        .menu-item i {
            margin-right: 0.8rem;
            width: 20px;
            text-align: center;
        }
        
        .main-content {
            flex: 1;
            margin-left: 250px;
            padding: 2rem;
        }
        
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #eee;
        }
        
        .user-info {
            display: flex;
            align-items: center;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: var(--secondary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            font-weight: bold;
        }
        
        .logout-btn {
            background-color: var(--accent-color);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .logout-btn:hover {
            background-color: #c0392b;
        }
        
        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(550px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .card {
            background: white;
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .card-title {
            font-size: 1rem;
            color: #666;
        }
        
        .card-value {
            font-size: 1.8rem;
            font-weight: bold;
            color: var(--primary-color);
        }
        
        .card-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
        }
        
        .bg-primary {
            background-color: var(--primary-color);
        }
        
        .bg-success {
            background-color: var(--success-color);
        }
        
        .bg-accent {
            background-color: var(--accent-color);
        }
        
        .bg-secondary {
            background-color: var(--secondary-color);
        }
        
        .data-table, .form-table {
            width: 100%;
        }
        
        .data-table td, .form-table td {
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .data-table th, .form-table th {
            text-align: left;
            padding: 10px;
            color: var(--secondary-color);
        }
        
        .form-table input, .form-table select, .form-table textarea {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }
        
        .form-table select {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23333' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><polyline points='6 9 12 15 18 9'></polyline></svg>");
            background-repeat: no-repeat;
            background-position: right 0.5rem center;
            background-size: 1rem;
        }
        
        .form-table textarea {
            resize: vertical;
            min-height: 100px;
        }
        
        .btn {
            margin-top: 1rem;
            padding: 0.6rem 1rem;
            background: var(--secondary-color);
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn:hover {
            background: #2980b9;
        }
        
        .action-btn {
            padding: 0.4rem 0.8rem;
            font-size: 0.9rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 0.5rem;
        }
        
        .approve-btn {
            background: var(--success-color);
            color: white;
        }
        
        .approve-btn:hover {
            background: #27ae60;
        }
        
        .reject-btn {
            background: var(--accent-color);
            color: white;
        }
        
        .reject-btn:hover {
            background: #c0392b;
        }
        
        .message {
            padding: 0.75rem;
            margin-bottom: 1rem;
            border-radius: 4px;
            text-align: center;
        }
        
        .success {
            background-color: var(--success-color);
            color: white;
        }
        
        .error-message {
            background-color: var(--accent-color);
            color: white;
        }
        
        .recent-activity {
            background: white;
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        
        .section-title {
            font-size: 1.2rem;
            margin-bottom: 1rem;
            color: var(--primary-color);
        }
        
        .activity-item {
            display: flex;
            padding: 0.8rem 0;
            border-bottom: 1px solid #eee;
        }
        
        .activity-item:last-child {
            border-bottom: none;
        }
        
        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: rgba(52, 152, 219, 0.1);
            color: var(--secondary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
        }
        
        .activity-content {
            flex: 1;
        }
        
        .activity-title {
            font-weight: 600;
            margin-bottom: 0.2rem;
        }
        
        .activity-time {
            font-size: 0.8rem;
            color: #666;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                position: relative;
                height: auto;
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .dashboard-container {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h3>Production Supervisor Dashboard</h3>
                <p><?php echo htmlspecialchars($displayName); ?></p>
            </div>
            <div class="sidebar-menu">
                <a href="?view=view_allocated_equipment" class="menu-item <?php echo $showViewAllocatedEquipment ? 'active' : ''; ?>"><i class="fas fa-tools"></i> View Allocated Equipment</a>
                <a href="?view=manage_production_tasks" class="menu-item <?php echo $showManageProductionTasks ? 'active' : ''; ?>"><i class="fas fa-tasks"></i> Manage Production Tasks</a>
                <a href="?view=monitor_machine_performance" class="menu-item <?php echo $showMonitorMachinePerformance ? 'active' : ''; ?>"><i class="fas fa-cogs"></i> Monitor Machine Performance</a>
                <a href="?view=check_production_output" class="menu-item <?php echo $showCheckProductionOutput ? 'active' : ''; ?>"><i class="fas fa-industry"></i> Check Production Output</a>
                <a href="?view=approve_quality_standards" class="menu-item <?php echo $showApproveQualityStandards ? 'active' : ''; ?>"><i class="fas fa-clipboard-check"></i> Approve Quality Standards</a>
                <a href="?view=submit_performance_reports" class="menu-item <?php echo $showSubmitPerformanceReports ? 'active' : ''; ?>"><i class="fas fa-file-upload"></i> Submit Performance Reports</a>
                <a href="logout.php" class="menu-item" style="margin-top: 2rem; color: #e74c3c;"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="top-bar">
                <h2>Welcome back, <?php echo htmlspecialchars($displayName); ?>!</h2>
                <div class="user-info">
                    <div class="user-avatar"><?php echo $avatar; ?></div>
                </div>
            </div>

            <div class="dashboard-cards">
                <?php if ($showViewAllocatedEquipment): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">View Allocated Equipment</span>
                        <div class="card-icon bg-primary"><i class="fas fa-tools"></i></div>
                    </div>
                    <?php if ($allocations): ?>
                    <table class="data-table">
                        <tr>
                            <th>Allocation ID</th>
                            <th>Equipment ID</th>
                            <th>Equipment Name</th>
                            <th>Assigned To</th>
                            <th>Allocation Date</th>
                        </tr>
                        <?php foreach ($allocations as $allocation): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($allocation['allocation_id']); ?></td>
                            <td><?php echo htmlspecialchars($allocation['equipment_id']); ?></td>
                            <td><?php echo htmlspecialchars($allocation['equipment_name']); ?></td>
                            <td><?php echo htmlspecialchars($allocation['FName'] . ' ' . $allocation['LName']); ?></td>
                            <td><?php echo htmlspecialchars($allocation['allocation_date']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                    <?php else: ?>
                    <p>No equipment allocations found.</p>
                    <?php endif; ?>
                </div>
                <?php elseif ($showManageProductionTasks): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Manage Production Tasks</span>
                        <div class="card-icon bg-secondary"><i class="fas fa-tasks"></i></div>
                    </div>
                    <?php if ($errorMessage): ?>
                        <div class="message error-message"><?php echo htmlspecialchars($errorMessage); ?></div>
                    <?php endif; ?>
                    <?php if ($successMessage): ?>
                        <div class="message success"><?php echo htmlspecialchars($successMessage); ?></div>
                    <?php endif; ?>
                    <form action="" method="POST" class="form-table">
                        <input type="hidden" name="add_task" value="1">
                        <table>
                            <tr>
                                <th><label for="description">Task Description</label></th>
                                <td><input type="text" id="description" name="description" required></td>
                            </tr>
                            <tr>
                                <th><label for="assigned_to">Assigned To</label></th>
                                <td>
                                    <select id="assigned_to" name="assigned_to" required>
                                        <option value="">Select Worker</option>
                                        <?php foreach ($workers as $worker): ?>
                                        <option value="<?php echo $worker['EMP_ID']; ?>">
                                            <?php echo htmlspecialchars($worker['FName'] . ' ' . $worker['LName']); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="deadline">Deadline</label></th>
                                <td><input type="date" id="deadline" name="deadline" required></td>
                            </tr>
                        </table>
                        <button type="submit" class="btn"><i class="fas fa-save"></i> Add Task</button>
                    </form>
                    <?php if ($tasks): ?>
                    <table class="data-table">
                        <tr>
                            <th>Task ID</th>
                            <th>Description</th>
                            <th>Assigned To</th>
                            <th>Status</th>
                            <th>Deadline</th>
                            <th>Created At</th>
                        </tr>
                        <?php foreach ($tasks as $task): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($task['task_id']); ?></td>
                            <td><?php echo htmlspecialchars($task['description']); ?></td>
                            <td><?php echo htmlspecialchars($task['FName'] . ' ' . $task['LName']); ?></td>
                            <td><?php echo htmlspecialchars($task['status']); ?></td>
                            <td><?php echo htmlspecialchars($task['deadline']); ?></td>
                            <td><?php echo htmlspecialchars($task['created_at']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                    <?php else: ?>
                    <p>No tasks found.</p>
                    <?php endif; ?>
                </div>
                <?php elseif ($showMonitorMachinePerformance): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Monitor Machine Performance</span>
                        <div class="card-icon bg-accent"><i class="fas fa-cogs"></i></div>
                    </div>
                    <?php if ($performances): ?>
                    <table class="data-table">
                        <tr>
                            <th>Performance ID</th>
                            <th>Machine ID</th>
                            <th>Machine Name</th>
                            <th>Uptime (hrs)</th>
                            <th>Downtime (hrs)</th>
                            <th>Last Maintenance</th>
                            <th>Recorded At</th>
                        </tr>
                        <?php foreach ($performances as $performance): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($performance['performance_id']); ?></td>
                            <td><?php echo htmlspecialchars($performance['machine_id']); ?></td>
                            <td><?php echo htmlspecialchars($performance['machine_name']); ?></td>
                            <td><?php echo number_format($performance['uptime'], 2); ?></td>
                            <td><?php echo number_format($performance['downtime'], 2); ?></td>
                            <td><?php echo htmlspecialchars($performance['last_maintenance']); ?></td>
                            <td><?php echo htmlspecialchars($performance['recorded_at']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                    <?php else: ?>
                    <p>No machine performance records found.</p>
                    <?php endif; ?>
                </div>
                <?php elseif ($showCheckProductionOutput): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Check Production Output</span>
                        <div class="card-icon bg-success"><i class="fas fa-industry"></i></div>
                    </div>
                    <?php if ($outputs): ?>
                    <table class="data-table">
                        <tr>
                            <th>Output ID</th>
                            <th>Product Name</th>
                            <th>Quantity</th>
                            <th>Production Date</th>
                            <th>Recorded At</th>
                        </tr>
                        <?php foreach ($outputs as $output): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($output['output_id']); ?></td>
                            <td><?php echo htmlspecialchars($output['product_name']); ?></td>
                            <td><?php echo htmlspecialchars($output['quantity']); ?></td>
                            <td><?php echo htmlspecialchars($output['production_date']); ?></td>
                            <td><?php echo htmlspecialchars($output['recorded_at']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                    <?php else: ?>
                    <p>No production output records found.</p>
                    <?php endif; ?>
                </div>
                <?php elseif ($showApproveQualityStandards): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Approve Quality Standards</span>
                        <div class="card-icon bg-primary"><i class="fas fa-clipboard-check"></i></div>
                    </div>
                    <?php if ($errorMessage): ?>
                        <div class="message error-message"><?php echo htmlspecialchars($errorMessage); ?></div>
                    <?php endif; ?>
                    <?php if ($successMessage): ?>
                        <div class="message success"><?php echo htmlspecialchars($successMessage); ?></div>
                    <?php endif; ?>
                    <?php if ($quality_checks): ?>
                    <table class="data-table">
                        <tr>
                            <th>Check ID</th>
                            <th>Product Name</th>
                            <th>Status</th>
                            <th>Check Date</th>
                            <th>Created At</th>
                            <th>Action</th>
                        </tr>
                        <?php foreach ($quality_checks as $check): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($check['check_id']); ?></td>
                            <td><?php echo htmlspecialchars($check['product_name']); ?></td>
                            <td><?php echo htmlspecialchars($check['status']); ?></td>
                            <td><?php echo htmlspecialchars($check['check_date']); ?></td>
                            <td><?php echo htmlspecialchars($check['created_at']); ?></td>
                            <td>
                                <form action="" method="POST" style="display: inline;">
                                    <input type="hidden" name="check_id" value="<?php echo $check['check_id']; ?>">
                                    <button type="submit" name="approve_check" class="action-btn approve-btn"><i class="fas fa-check"></i> Approve</button>
                                    <button type="submit" name="reject_check" class="action-btn reject-btn"><i class="fas fa-times"></i> Reject</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                    <?php else: ?>
                    <p>No pending quality checks found.</p>
                    <?php endif; ?>
                </div>
                <?php elseif ($showSubmitPerformanceReports): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Submit Performance Reports</span>
                        <div class="card-icon bg-secondary"><i class="fas fa-file-upload"></i></div>
                    </div>
                    <?php if ($errorMessage): ?>
                        <div class="message error-message"><?php echo htmlspecialchars($errorMessage); ?></div>
                    <?php endif; ?>
                    <?php if ($successMessage): ?>
                        <div class="message success"><?php echo htmlspecialchars($successMessage); ?></div>
                    <?php endif; ?>
                    <form action="" method="POST" class="form-table">
                        <input type="hidden" name="submit_report" value="1">
                        <table>
                            <tr>
                                <th><label for="title">Report Title</label></th>
                                <td><input type="text" id="title" name="title" required></td>
                            </tr>
                            <tr>
                                <th><label for="file_path">File Path</label></th>
                                <td><input type="text" id="file_path" name="file_path" placeholder="Enter file URL" required></td>
                            </tr>
                        </table>
                        <button type="submit" class="btn"><i class="fas fa-upload"></i> Submit Report</button>
                    </form>
                    <?php if ($reports): ?>
                    <table class="data-table">
                        <tr>
                            <th>Report ID</th>
                            <th>Title</th>
                            <th>Submission Date</th>
                            <th>File</th>
                        </tr>
                        <?php foreach ($reports as $report): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($report['report_id']); ?></td>
                            <td><?php echo htmlspecialchars($report['title']); ?></td>
                            <td><?php echo htmlspecialchars($report['submission_date']); ?></td>
                            <td><a href="<?php echo htmlspecialchars($report['file_path']); ?>" class="btn"><i class="fas fa-download"></i> View</a></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                    <?php else: ?>
                    <p>No reports found.</p>
                    <?php endif; ?>
                </div>
                <?php else: ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Allocated Equipment</span>
                        <div class="card-icon bg-primary"><i class="fas fa-tools"></i></div>
                    </div>
                    <div class="card-value"><?php
                        $stmt = $pdo->query("SELECT COUNT(*) FROM equipment_allocation");
                        echo $stmt->fetchColumn();
                    ?></div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Active Tasks</span>
                        <div class="card-icon bg-secondary"><i class="fas fa-tasks"></i></div>
                    </div>
                    <div class="card-value"><?php
                        $stmt = $pdo->query("SELECT COUNT(*) FROM production_tasks WHERE status IN ('Pending', 'In Progress')");
                        echo $stmt->fetchColumn();
                    ?></div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Pending Quality Checks</span>
                        <div class="card-icon bg-accent"><i class="fas fa-clipboard-check"></i></div>
                    </div>
                    <div class="card-value"><?php
                        $stmt = $pdo->query("SELECT COUNT(*) FROM quality_checks WHERE status = 'Pending'");
                        echo $stmt->fetchColumn();
                    ?></div>
                </div>
                <?php endif; ?>
            </div>

            <div class="recent-activity">
                <h3 class="section-title">Recent Activity</h3>
                <div class="activity-item">
                    <div class="activity-icon"><i class="fas fa-clipboard-check"></i></div>
                    <div class="activity-content">
                        <div class="activity-title">Quality check approved</div>
                        <div class="activity-time">2 hours ago</div>
                    </div>
                </div>
                <div class="activity-item">
                    <div class="activity-icon"><i class="fas fa-tasks"></i></div>
                    <div class="activity-content">
                        <div class="activity-title">New production task assigned</div>
                        <div class="activity-time">Yesterday</div>
                    </div>
                </div>
                <div class="activity-item">
                    <div class="activity-icon"><i class="fas fa-file-upload"></i></div>
                    <div class="activity-content">
                        <div class="activity-title">Performance report submitted</div>
                        <div class="activity-time">Yesterday</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>