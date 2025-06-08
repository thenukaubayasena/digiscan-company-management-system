<?php
session_start();
require 'db_connection.php';

$displayName = isset($_SESSION['FName'], $_SESSION['LName']) ? $_SESSION['FName'] . ' ' . $_SESSION['LName'] : 'Legal & HR Manager';
$avatar = strtoupper(substr($displayName, 0, 1));

// Initialize message variables
$successMessage = '';
$errorMessage = '';

// Initialize view variables
$showViewAttendance = false;
$showManageEmployeeRecords = false;
$showGenerateHRReports = false;
$showApproveAppointments = false;
$showApprovePayroll = false;
$showViewLeaveData = false;
$showUpdatePolicies = false;
$showUpdatePerformance = false;

// Determine which view to show based on GET parameter
if (isset($_GET['view'])) {
    $showViewAttendance = ($_GET['view'] === 'view_attendance');
    $showManageEmployeeRecords = ($_GET['view'] === 'manage_employee_records');
    $showGenerateHRReports = ($_GET['view'] === 'generate_hr_reports');
    $showApproveAppointments = ($_GET['view'] === 'approve_appointments');
    $showApprovePayroll = ($_GET['view'] === 'approve_payroll');
    $showViewLeaveData = ($_GET['view'] === 'view_leave_data');
    $showUpdatePolicies = ($_GET['view'] === 'update_policies');
    $showUpdatePerformance = ($_GET['view'] === 'update_performance');
} else {
    // Default view: show summary cards
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Manage Employee Records
        if (isset($_POST['add_employee'])) {
            $FName = $_POST['FName'];
            $LName = $_POST['LName'];
            $Emp_Email = $_POST['Emp_Email'];
            $designation = $_POST['designation'];
            $salary = $_POST['salary'];
            if (empty($FName) || empty($LName) || empty($Emp_Email) || empty($designation) || empty($salary)) {
                $errorMessage = "All fields are required.";
                $showManageEmployeeRecords = true;
            } elseif (!filter_var($Emp_Email, FILTER_VALIDATE_EMAIL)) {
                $errorMessage = "Invalid email format.";
                $showManageEmployeeRecords = true;
            } elseif (!is_numeric($salary) || $salary <= 0) {
                $errorMessage = "Salary must be a positive number.";
                $showManageEmployeeRecords = true;
            } else {
                $stmt = $pdo->prepare("INSERT INTO employees (FName, LName, Emp_Email, designation, salary) VALUES (?, ?, ?, ?, ?)");
                $success = $stmt->execute([$FName, $LName, $Emp_Email, $designation, $salary]);
                if ($success) {
                    $successMessage = "Employee added successfully!";
                    $showManageEmployeeRecords = true;
                } else {
                    $errorMessage = "Error adding employee.";
                    $showManageEmployeeRecords = true;
                }
            }
        }
        // Approve/Reject Appointment
        if (isset($_POST['approve_appointment']) || isset($_POST['reject_appointment'])) {
            $appointment_id = $_POST['appointment_id'];
            $status = isset($_POST['approve_appointment']) ? 'Approved' : 'Rejected';
            $stmt = $pdo->prepare("UPDATE appointments SET status = ? WHERE appointment_id = ?");
            $success = $stmt->execute([$status, $appointment_id]);
            if ($success) {
                $successMessage = "Appointment $status successfully!";
                $showApproveAppointments = true;
            } else {
                $errorMessage = "Error updating appointment.";
                $showApproveAppointments = true;
            }
        }
        // Approve/Reject Payroll
        if (isset($_POST['approve_payroll']) || isset($_POST['reject_payroll'])) {
            $payroll_id = $_POST['payroll_id'];
            $status = isset($_POST['approve_payroll']) ? 'Approved' : 'Rejected';
            $stmt = $pdo->prepare("UPDATE payroll SET status = ? WHERE payroll_id = ?");
            $success = $stmt->execute([$status, $payroll_id]);
            if ($success) {
                $successMessage = "Payroll $status successfully!";
                $showApprovePayroll = true;
            } else {
                $errorMessage = "Error updating payroll.";
                $showApprovePayroll = true;
            }
        }
        // Update Company Policies
        if (isset($_POST['update_policy'])) {
            $title = $_POST['title'];
            $description = $_POST['description'];
            if (empty($title) || empty($description)) {
                $errorMessage = "All fields are required.";
                $showUpdatePolicies = true;
            } else {
                $stmt = $pdo->prepare("INSERT INTO company_policies (title, description, updated_at) VALUES (?, ?, NOW())");
                $success = $stmt->execute([$title, $description]);
                if ($success) {
                    $successMessage = "Policy added successfully!";
                    $showUpdatePolicies = true;
                } else {
                    $errorMessage = "Error adding policy.";
                    $showUpdatePolicies = true;
                }
            }
        }
        // Update Company Performance
        if (isset($_POST['update_performance'])) {
            $metric_name = $_POST['metric_name'];
            $target_value = $_POST['target_value'];
            $current_value = $_POST['value'];
            if (empty($metric_name) || empty($target_value) || empty($current_value)) {
                $errorMessage = "All fields are required.";
                $showUpdatePerformance = true;
            } elseif (!is_numeric($target_value) || !is_numeric($current_value) || $target_value <= 0) {
                $errorMessage = "Target and current values must be positive numbers.";
                $showUpdatePerformance = true;
            } else {
                $stmt = $pdo->prepare("INSERT INTO performance_metrics (metric_name, target_value, value, updated_at) VALUES (?, ?, ?, NOW())");
                $success = $stmt->execute([$metric_name, $target_value, $current_value]);
                if ($success) {
                    $successMessage = "Performance metric added successfully!";
                    $showUpdatePerformance = true;
                } else {
                    $errorMessage = "Error adding performance metric.";
                    $showUpdatePerformance = true;
                }
            }
        }
    } catch (PDOException $e) {
        $errorMessage = "Database error: " . $e->getMessage();
        if (isset($_POST['add_employee'])) $showManageEmployeeRecords = true;
        elseif (isset($_POST['approve_appointment']) || isset($_POST['reject_appointment'])) $showApproveAppointments = true;
        elseif (isset($_POST['approve_payroll']) || isset($_POST['reject_payroll'])) $showApprovePayroll = true;
        elseif (isset($_POST['update_policy'])) $showUpdatePolicies = true;
        elseif (isset($_POST['update_performance'])) $showUpdatePerformance = true;
    }
}

// Fetch data for views
if ($showViewAttendance) {
    $stmt = $pdo->query("SELECT a.attendance_id, a.employee_id, a.date, a.status, a.hours_worked, e.FName, e.LName 
                         FROM attendance a 
                         JOIN employees e ON a.employee_id = e.EMP_ID 
                         ORDER BY a.date DESC");
    $attendance = $stmt->fetchAll();
}
if ($showManageEmployeeRecords) {
    $stmt = $pdo->query("SELECT * FROM employees ORDER BY EMP_ID DESC");
    $employees = $stmt->fetchAll();
}
if ($showGenerateHRReports) {
    $stmt = $pdo->query("SELECT * FROM hr_reports ORDER BY generated_at DESC");
    $reports = $stmt->fetchAll();
}
if ($showApproveAppointments) {
    $stmt = $pdo->query("SELECT * FROM appointments WHERE status = 'Pending' ORDER BY submitted_at DESC");
    $appointments = $stmt->fetchAll();
}
if ($showApprovePayroll) {
    $stmt = $pdo->query("SELECT p.payroll_id, p.EMP_ID, p.salary, p.payment_date, p.status, e.FName, e.LName 
                         FROM payroll p 
                         JOIN employees e ON p.EMP_ID = e.EMP_ID 
                         WHERE p.status = 'Pending' 
                         ORDER BY p.payment_date DESC");
    $payroll = $stmt->fetchAll();
}
if ($showViewLeaveData) {
    $stmt = $pdo->query("SELECT l.lr_id, l.employee_id, l.st_date, l.end_date, l.reason, l.status, e.FName, e.LName 
                         FROM leave_requests l 
                         JOIN employees e ON l.employee_id = e.EMP_ID 
                         ORDER BY l.st_date DESC");
    $leaves = $stmt->fetchAll();
}
if ($showUpdatePolicies) {
    $stmt = $pdo->query("SELECT * FROM company_policies ORDER BY updated_at DESC");
    $policies = $stmt->fetchAll();
}
if ($showUpdatePerformance) {
    $stmt = $pdo->query("SELECT * FROM performance_metrics ORDER BY updated_at DESC");
    $metrics = $stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Legal & HR Manager Dashboard | Digiscan</title>
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
            padding: 8px;
            border-bottom: 1px solid #eee;
        }
        
        .data-table th, .form-table th {
            text-align: left;
            padding: 8px;
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
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23333' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
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
            padding: 0.5rem;
            margin-bottom: 1rem;
            border-radius: 4px;
            text-align: center;
        }
        
        .success {
            background-color: var(--success-color);
            color: white;
        }
        
        .error {
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
                <h3>Legal & HR Manager Dashboard</h3>
                <p><?= htmlspecialchars($displayName) ?></p>
            </div>
            <div class="sidebar-menu">
                <a href="?view=view_attendance" class="menu-item <?= $showViewAttendance ? 'active' : '' ?>"><i class="fas fa-user-check"></i> View Employee Attendance</a>
                <a href="?view=manage_employee_records" class="menu-item <?= $showManageEmployeeRecords ? 'active' : '' ?>"><i class="fas fa-id-badge"></i> Manage Employee Records</a>
                <a href="?view=generate_hr_reports" class="menu-item <?= $showGenerateHRReports ? 'active' : '' ?>"><i class="fas fa-chart-line"></i> Generate HR Reports</a>
                <a href="?view=approve_appointments" class="menu-item <?= $showApproveAppointments ? 'active' : '' ?>"><i class="fas fa-user-plus"></i> Approve Employee Appointments</a>
                <a href="?view=approve_payroll" class="menu-item <?= $showApprovePayroll ? 'active' : '' ?>"><i class="fas fa-file-invoice-dollar"></i> Approve Payroll</a>
                <a href="?view=view_leave_data" class="menu-item <?= $showViewLeaveData ? 'active' : '' ?>"><i class="fas fa-calendar-check"></i> View Leave Data</a>
                <a href="?view=update_policies" class="menu-item <?= $showUpdatePolicies ? 'active' : '' ?>"><i class="fas fa-file-signature"></i> Update Company Policies</a>
                <a href="?view=update_performance" class="menu-item <?= $showUpdatePerformance ? 'active' : '' ?>"><i class="fas fa-chart-bar"></i> Update Company Performance</a>
                <a href="logout.php" class="menu-item" style="margin-top: 2rem; color: #e74c3c;"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="top-bar">
                <h2>Welcome back, <?= htmlspecialchars($displayName) ?>!</h2>
                <div class="user-info">
                    <div class="user-avatar"><?= $avatar ?></div>
                </div>
            </div>

            <div class="dashboard-cards">
                <?php if ($showViewAttendance && $attendance): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Employee Attendance</span>
                        <div class="card-icon bg-primary"><i class="fas fa-user-check"></i></div>
                    </div>
                    <table class="data-table">
                        <tr>
                            <th>Employee</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Hours Worked</th>
                        </tr>
                        <?php foreach ($attendance as $record): ?>
                        <tr>
                            <td><?= htmlspecialchars($record['FName'] . ' ' . $record['LName']) ?></td>
                            <td><?= htmlspecialchars($record['date']) ?></td>
                            <td><?= htmlspecialchars($record['status']) ?></td>
                            <td><?= number_format($record['hours_worked'], 2) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
                <?php elseif ($showManageEmployeeRecords): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Manage Employee Records</span>
                        <div class="card-icon bg-secondary"><i class="fas fa-id-badge"></i></div>
                    </div>
                    <?php if ($errorMessage): ?>
                        <div class="message error"><?= htmlspecialchars($errorMessage) ?></div>
                    <?php endif; ?>
                    <?php if ($successMessage): ?>
                        <div class="message success"><?= htmlspecialchars($successMessage) ?></div>
                    <?php endif; ?>
                    <form action="" method="POST" class="form-table">
                        <input type="hidden" name="add_employee" value="1">
                        <table>
                            <tr>
                                <th><label for="FName">First Name</label></th>
                                <td><input type="text" id="FName" name="FName" required></td>
                            </tr>
                            <tr>
                                <th><label for="LName">Last Name</label></th>
                                <td><input type="text" id="LName" name="LName" required></td>
                            </tr>
                            <tr>
                                <th><label for="Emp_Email">Email</label></th>
                                <td><input type="email" id="Emp_Email" name="Emp_Email" required></td>
                            </tr>
                            <tr>
                                <th><label for="designation">Designation</label></th>
                                <td><input type="text" id="designation" name="designation" required></td>
                            </tr>
                            <tr>
                                <th><label for="salary">Salary</label></th>
                                <td><input type="number" id="salary" name="salary" step="0.01" required></td>
                            </tr>
                        </table>
                        <button type="submit" class="btn"><i class="fas fa-save"></i> Add Employee</button>
                    </form>
                    <?php if ($employees): ?>
                    <table class="data-table">
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Designation</th>
                            <th>Salary</th>
                        </tr>
                        <?php foreach ($employees as $employee): ?>
                        <tr>
                            <td><?= htmlspecialchars($employee['FName'] . ' ' . $employee['LName']) ?></td>
                            <td><?= htmlspecialchars($employee['Emp_Email']) ?></td>
                            <td><?= htmlspecialchars($employee['designation']) ?></td>
                            <td>$<?= number_format($employee['salary'], 2) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                    <?php endif; ?>
                </div>
                <?php elseif ($showGenerateHRReports && $reports): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">HR Reports</span>
                        <div class="card-icon bg-accent"><i class="fas fa-chart-line"></i></div>
                    </div>
                    <table class="data-table">
                        <tr>
                            <th>Title</th>
                            <th>Generated At</th>
                            <th>File Path</th>
                        </tr>
                        <?php foreach ($reports as $report): ?>
                        <tr>
                            <td><?= htmlspecialchars($report['title']) ?></td>
                            <td><?= htmlspecialchars($report['generated_at']) ?></td>
                            <td><a href="<?= htmlspecialchars($report['file_path']) ?>" class="btn">View</a></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
                <?php elseif ($showApproveAppointments && $appointments): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Approve Employee Appointments</span>
                        <div class="card-icon bg-success"><i class="fas fa-user-plus"></i></div>
                    </div>
                    <?php if ($errorMessage): ?>
                        <div class="message error"><?= htmlspecialchars($errorMessage) ?></div>
                    <?php endif; ?>
                    <?php if ($successMessage): ?>
                        <div class="message success"><?= htmlspecialchars($successMessage) ?></div>
                    <?php endif; ?>
                    <table class="data-table">
                        <tr>
                            <th>Candidate Name</th>
                            <th>Position</th>
                            <th>Submitted At</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                        <?php foreach ($appointments as $appointment): ?>
                        <tr>
                            <td><?= htmlspecialchars($appointment['candidate_name']) ?></td>
                            <td><?= htmlspecialchars($appointment['position']) ?></td>
                            <td><?= htmlspecialchars($appointment['submitted_at']) ?></td>
                            <td><?= htmlspecialchars($appointment['status']) ?></td>
                            <td>
                                <form action="" method="POST" style="display: inline;">
                                    <input type="hidden" name="appointment_id" value="<?= $appointment['appointment_id'] ?>">
                                    <button type="submit" name="approve_appointment" class="action-btn approve-btn"><i class="fas fa-check"></i> Approve</button>
                                    <button type="submit" name="reject_appointment" class="action-btn reject-btn"><i class="fas fa-times"></i> Reject</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
                <?php elseif ($showApprovePayroll && $payroll): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Approve Payroll</span>
                        <div class="card-icon bg-primary"><i class="fas fa-file-invoice-dollar"></i></div>
                    </div>
                    <?php if ($errorMessage): ?>
                        <div class="message error"><?= htmlspecialchars($errorMessage) ?></div>
                    <?php endif; ?>
                    <?php if ($successMessage): ?>
                        <div class="message success"><?= htmlspecialchars($successMessage) ?></div>
                    <?php endif; ?>
                    <table class="data-table">
                        <tr>
                            <th>Employee</th>
                            <th>Salary</th>
                            <th>Payment Date</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                        <?php foreach ($payroll as $record): ?>
                        <tr>
                            <td><?= htmlspecialchars($record['FName'] . ' ' . $record['LName']) ?></td>
                            <td>$<?= number_format($record['salary'], 2) ?></td>
                            <td><?= htmlspecialchars($record['payment_date']) ?></td>
                            <td><?= htmlspecialchars($record['status']) ?></td>
                            <td>
                                <form action="" method="POST" style="display: inline;">
                                    <input type="hidden" name="payroll_id" value="<?= $record['payroll_id'] ?>">
                                    <button type="submit" name="approve_payroll" class="action-btn approve-btn"><i class="fas fa-check"></i> Approve</button>
                                    <button type="submit" name="reject_payroll" class="action-btn reject-btn"><i class="fas fa-times"></i> Reject</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
                <?php elseif ($showViewLeaveData && $leaves): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">View Leave Data</span>
                        <div class="card-icon bg-secondary"><i class="fas fa-calendar-check"></i></div>
                    </div>
                    <table class="data-table">
                        <tr>
                            <th>Employee</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Reason</th>
                            <th>Status</th>
                        </tr>
                        <?php foreach ($leaves as $leave): ?>
                        <tr>
                            <td><?= htmlspecialchars($leave['FName'] . ' ' . $leave['LName']) ?></td>
                            <td><?= htmlspecialchars($leave['st_date']) ?></td>
                            <td><?= htmlspecialchars($leave['end_date']) ?></td>
                            <td><?= htmlspecialchars($leave['reason']) ?></td>
                            <td><?= htmlspecialchars($leave['status']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
                <?php elseif ($showUpdatePolicies): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Update Company Policies</span>
                        <div class="card-icon bg-accent"><i class="fas fa-file-signature"></i></div>
                    </div>
                    <?php if ($errorMessage): ?>
                        <div class="message error"><?= htmlspecialchars($errorMessage) ?></div>
                    <?php endif; ?>
                    <?php if ($successMessage): ?>
                        <div class="message success"><?= htmlspecialchars($successMessage) ?></div>
                    <?php endif; ?>
                    <form action="" method="POST" class="form-table">
                        <input type="hidden" name="update_policy" value="1">
                        <table>
                            <tr>
                                <th><label for="title">Policy Title</label></th>
                                <td><input type="text" id="title" name="title" required></td>
                            </tr>
                            <tr>
                                <th><label for="description">Description</label></th>
                                <td><textarea id="description" name="description" required></textarea></td>
                            </tr>
                        </table>
                        <button type="submit" class="btn"><i class="fas fa-save"></i> Add Policy</button>
                    </form>
                    <?php if ($policies): ?>
                    <table class="data-table">
                        <tr>
                            <th>Title</th>
                            <th>Description</th>
                            <th>Updated At</th>
                        </tr>
                        <?php foreach ($policies as $policy): ?>
                        <tr>
                            <td><?= htmlspecialchars($policy['title']) ?></td>
                            <td><?= htmlspecialchars($policy['description']) ?></td>
                            <td><?= htmlspecialchars($policy['updated_at']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                    <?php endif; ?>
                </div>
                <?php elseif ($showUpdatePerformance): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Update Company Performance</span>
                        <div class="card-icon bg-success"><i class="fas fa-chart-bar"></i></div>
                    </div>
                    <?php if ($errorMessage): ?>
                        <div class="message error"><?= htmlspecialchars($errorMessage) ?></div>
                    <?php endif; ?>
                    <?php if ($successMessage): ?>
                        <div class="message success"><?= htmlspecialchars($successMessage) ?></div>
                    <?php endif; ?>
                    <form action="" method="POST" class="form-table">
                        <input type="hidden" name="update_performance" value="1">
                        <table>
                            <tr>
                                <th><label for="metric_name">Metric Name</label></th>
                                <td><input type="text" id="metric_name" name="metric_name" required></td>
                            </tr>
                            <tr>
                                <th><label for="target_value">Target Value</label></th>
                                <td><input type="number" id="target_value" name="target_value" step="0.01" required></td>
                            </tr>
                            <tr>
                                <th><label for="current_value">Current Value</label></th>
                                <td><input type="number" id="current_value" name="current_value" step="0.01" required></td>
                            </tr>
                        </table>
                        <button type="submit" class="btn"><i class="fas fa-save"></i> Add Metric</button>
                    </form>
                    <?php if ($metrics): ?>
                    <table class="data-table">
                        <tr>
                            <th>Metric Name</th>
                            <th>Target Value</th>
                            <th>Current Value</th>
                            <th>Updated At</th>
                        </tr>
                        <?php foreach ($metrics as $metric): ?>
                        <tr>
                            <td><?= htmlspecialchars($metric['metric_name']) ?></td>
                            <td><?= htmlspecialchars($metric['target_value'], 2) ?></td>
                            <td><?= htmlspecialchars($metric['value'], 2) ?></td>
                            <td><?= htmlspecialchars($metric['updated_at']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                    <?php endif; ?>
                </div>
                <?php else: ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Total Employees</span>
                        <div class="card-icon bg-primary"><i class="fas fa-users"></i></div>
                    </div>
                    <div class="card-value"><?php
                        $stmt = $pdo->query("SELECT COUNT(*) FROM employees");
                        echo $stmt->fetchColumn();
                    ?></div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Pending Leaves</span>
                        <div class="card-icon bg-accent"><i class="fas fa-calendar-check"></i></div>
                    </div>
                    <div class="card-value"><?php
                        $stmt = $pdo->query("SELECT COUNT(*) FROM leaves WHERE status = 'Pending'");
                        echo $stmt->fetchColumn();
                    ?></div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Pending Appointments</span>
                        <div class="card-icon bg-secondary"><i class="fas fa-user-plus"></i></div>
                    </div>
                    <div class="card-value"><?php
                        $stmt = $pdo->query("SELECT COUNT(*) FROM appointments WHERE status = 'Pending'");
                        echo $stmt->fetchColumn();
                    ?></div>
                </div>
                <?php endif; ?>
            </div>

            <div class="recent-activity">
                <h3 class="section-title">Recent Activity</h3>
                <div class="activity-item">
                    <div class="activity-icon"><i class="fas fa-user-plus"></i></div>
                    <div class="activity-content">
                        <div class="activity-title">New appointment approved</div>
                        <div class="activity-time">2 hours ago</div>
                    </div>
                </div>
                <div class="activity-item">
                    <div class="activity-icon"><i class="fas fa-file-signature"></i></div>
                    <div class="activity-content">
                        <div class="activity-title">New policy added</div>
                        <div class="activity-time">Yesterday</div>
                    </div>
                </div>
                <div class="activity-item">
                    <div class="activity-icon"><i class="fas fa-file-invoice-dollar"></i></div>
                    <div class="activity-content">
                        <div class="activity-title">Payroll approved</div>
                        <div class="activity-time">Yesterday</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>