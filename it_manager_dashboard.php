<?php
session_start();
require 'db_connection.php';

$displayName = isset($_SESSION['FName'], $_SESSION['LName']) ? $_SESSION['FName'] . ' ' . $_SESSION['LName'] : 'IT Manager';
$avatar = strtoupper(substr($displayName, 0, 1));

// Initialize message variables
$successMessage = '';
$errorMessage = '';

// Initialize view variables
$showManageUserAccess = false;
$showHandleDataEncryption = false;
$showTroubleshootIssues = false;
$showMaintainEmployeeDB = false;
$showMonitorSystemPerformance = false;
$showEnsureDataPrivacy = false;
$showMonitorNetworkPerformance = false;

// Determine which view to show based on GET parameter
if (isset($_GET['view'])) {
    $showManageUserAccess = ($_GET['view'] === 'manage_user_access');
    $showHandleDataEncryption = ($_GET['view'] === 'handle_data_encryption');
    $showTroubleshootIssues = ($_GET['view'] === 'troubleshoot_issues');
    $showMaintainEmployeeDB = ($_GET['view'] === 'maintain_employee_db');
    $showMonitorSystemPerformance = ($_GET['view'] === 'monitor_system_performance');
    $showEnsureDataPrivacy = ($_GET['view'] === 'ensure_data_privacy');
    $showMonitorNetworkPerformance = ($_GET['view'] === 'monitor_network_performance');
} else {
    // Default view: show summary cards
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Manage User Access
        if (isset($_POST['manage_user_access'])) {
            $EMP_ID = $_POST['EMP_ID'];
            $role = $_POST['role'];
            if (empty($EMP_ID) || empty($role)) {
                $errorMessage = "All fields are required.";
                $showManageUserAccess = true;
            } else {
                $stmt = $pdo->prepare("INSERT INTO user_access (EMP_ID, role, updated_at) VALUES (?, ?, NOW()) ON DUPLICATE KEY UPDATE role = ?, updated_at = NOW()");
                $success = $stmt->execute([$EMP_ID, $role, $role]);
                if ($success) {
                    $successMessage = "User access updated successfully!";
                    $showManageUserAccess = true;
                } else {
                    $errorMessage = "Error updating user access.";
                    $showManageUserAccess = true;
                }
            }
        }
        // Handle Data Encryption
        if (isset($_POST['handle_data_encryption'])) {
            $data_type = $_POST['data_type'];
            $encryption_method = $_POST['encryption_method'];
            if (empty($data_type) || empty($encryption_method)) {
                $errorMessage = "All fields are required.";
                $showHandleDataEncryption = true;
            } else {
                $stmt = $pdo->prepare("INSERT INTO encryption_logs (data_type, encryption_method, logged_at) VALUES (?, ?, NOW())");
                $success = $stmt->execute([$data_type, $encryption_method]);
                if ($success) {
                    $successMessage = "Encryption task logged successfully!";
                    $showHandleDataEncryption = true;
                } else {
                    $errorMessage = "Error logging encryption task.";
                    $showHandleDataEncryption = true;
                }
            }
        }
        // Troubleshoot Technical Issues
        if (isset($_POST['troubleshoot_issues'])) {
            $description = $_POST['description'];
            if (empty($description)) {
                $errorMessage = "Description is required.";
                $showTroubleshootIssues = true;
            } else {
                $stmt = $pdo->prepare("INSERT INTO technical_issues (description, status, reported_at) VALUES (?, 'Open', NOW())");
                $success = $stmt->execute([$description]);
                if ($success) {
                    $successMessage = "Technical issue reported successfully!";
                    $showTroubleshootIssues = true;
                } else {
                    $errorMessage = "Error reporting technical issue.";
                    $showTroubleshootIssues = true;
                }
            }
        }
        // Resolve Technical Issue
        if (isset($_POST['resolve_issue'])) {
            $issue_id = $_POST['issue_id'];
            $stmt = $pdo->prepare("UPDATE technical_issues SET status = 'Resolved', resolved_at = NOW() WHERE issue_id = ?");
            $success = $stmt->execute([$issue_id]);
            if ($success) {
                $successMessage = "Issue resolved successfully!";
                $showTroubleshootIssues = true;
            } else {
                $errorMessage = "Error resolving issue.";
                $showTroubleshootIssues = true;
            }
        }
        // Maintain Employee Database
        if (isset($_POST['maintain_employee_db'])) {
            $EMP_ID = $_POST['EMP_ID'];
            $username = $_POST['username'];
            if (empty($EMP_ID) || empty($username)) {
                $errorMessage = "All fields are required.";
                $showMaintainEmployeeDB = true;
            } else {
                $stmt = $pdo->prepare("INSERT INTO employee_db (EMP_ID, username, last_updated) VALUES (?, ?, NOW()) ON DUPLICATE KEY UPDATE username = ?, last_updated = NOW()");
                $success = $stmt->execute([$EMP_ID, $username, $username]);
                if ($success) {
                    $successMessage = "Employee database updated successfully!";
                    $showMaintainEmployeeDB = true;
                } else {
                    $errorMessage = "Error updating employee database.";
                    $showMaintainEmployeeDB = true;
                }
            }
        }
        // Monitor System Performance
        if (isset($_POST['monitor_system_performance'])) {
            $metric_type = $_POST['metric_type'];
            $value = $_POST['value'];
            if (empty($metric_type) || empty($value)) {
                $errorMessage = "All fields are required.";
                $showMonitorSystemPerformance = true;
            } elseif (!is_numeric($value) || $value < 0) {
                $errorMessage = "Value must be a non-negative number.";
                $showMonitorSystemPerformance = true;
            } else {
                $stmt = $pdo->prepare("INSERT INTO system_performance (metric_type, value, logged_at) VALUES (?, ?, NOW())");
                $success = $stmt->execute([$metric_type, $value]);
                if ($success) {
                    $successMessage = "System performance metric logged successfully!";
                    $showMonitorSystemPerformance = true;
                } else {
                    $errorMessage = "Error logging system performance metric.";
                    $showMonitorSystemPerformance = true;
                }
            }
        }
        // Ensure Data Privacy
        if (isset($_POST['ensure_data_privacy'])) {
            $audit_type = $_POST['audit_type'];
            $status = $_POST['status'];
            if (empty($audit_type) || empty($status)) {
                $errorMessage = "All fields are required.";
                $showEnsureDataPrivacy = true;
            } else {
                $stmt = $pdo->prepare("INSERT INTO data_privacy_audits (audit_type, status, logged_at) VALUES (?, ?, NOW())");
                $success = $stmt->execute([$audit_type, $status]);
                if ($success) {
                    $successMessage = "Data privacy audit logged successfully!";
                    $showEnsureDataPrivacy = true;
                } else {
                    $errorMessage = "Error logging data privacy audit.";
                    $showEnsureDataPrivacy = true;
                }
            }
        }
        // Monitor Network Performance
        if (isset($_POST['monitor_network_performance'])) {
            $metric_type = $_POST['metric_type'];
            $value = $_POST['value'];
            if (empty($metric_type) || empty($value)) {
                $errorMessage = "All fields are required.";
                $showMonitorNetworkPerformance = true;
            } elseif (!is_numeric($value) || $value < 0) {
                $errorMessage = "Value must be a non-negative number.";
                $showMonitorNetworkPerformance = true;
            } else {
                $stmt = $pdo->prepare("INSERT INTO network_performance (metric_type, value, logged_at) VALUES (?, ?, NOW())");
                $success = $stmt->execute([$metric_type, $value]);
                if ($success) {
                    $successMessage = "Network performance metric logged successfully!";
                    $showMonitorNetworkPerformance = true;
                } else {
                    $errorMessage = "Error logging network performance metric.";
                    $showMonitorNetworkPerformance = true;
                }
            }
        }
    } catch (PDOException $e) {
        $errorMessage = "Database error: " . $e->getMessage();
        if (isset($_POST['manage_user_access'])) $showManageUserAccess = true;
        elseif (isset($_POST['handle_data_encryption'])) $showHandleDataEncryption = true;
        elseif (isset($_POST['troubleshoot_issues']) || isset($_POST['resolve_issue'])) $showTroubleshootIssues = true;
        elseif (isset($_POST['maintain_employee_db'])) $showMaintainEmployeeDB = true;
        elseif (isset($_POST['monitor_system_performance'])) $showMonitorSystemPerformance = true;
        elseif (isset($_POST['ensure_data_privacy'])) $showEnsureDataPrivacy = true;
        elseif (isset($_POST['monitor_network_performance'])) $showMonitorNetworkPerformance = true;
    }
}

// Fetch data for views
if ($showManageUserAccess) {
    $stmt = $pdo->query("SELECT u.access_id, u.EMP_ID, u.role, u.updated_at, e.FName, e.LName 
                         FROM user_access u 
                         JOIN employees e ON u.EMP_ID = e.EMP_ID 
                         ORDER BY u.updated_at DESC");
    $user_access = $stmt->fetchAll();
    $stmt = $pdo->query("SELECT EMP_ID, FName, LName FROM employees");
    $employees = $stmt->fetchAll();
}
if ($showHandleDataEncryption) {
    $stmt = $pdo->query("SELECT * FROM encryption_logs ORDER BY logged_at DESC");
    $encryption_logs = $stmt->fetchAll();
}
if ($showTroubleshootIssues) {
    $stmt = $pdo->query("SELECT * FROM technical_issues ORDER BY reported_at DESC");
    $technical_issues = $stmt->fetchAll();
}
if ($showMaintainEmployeeDB) {
    $stmt = $pdo->query("SELECT e.EMP_ID, e.FName, e.LName, d.username, d.last_updated 
                         FROM employees e 
                         LEFT JOIN employee_db d ON e.EMP_ID = d.EMP_ID 
                         ORDER BY e.EMP_ID DESC");
    $employee_db = $stmt->fetchAll();
}
if ($showMonitorSystemPerformance) {
    $stmt = $pdo->query("SELECT * FROM system_performance ORDER BY logged_at DESC");
    $system_performance = $stmt->fetchAll();
}
if ($showEnsureDataPrivacy) {
    $stmt = $pdo->query("SELECT * FROM data_privacy_audits ORDER BY logged_at DESC");
    $data_privacy_audits = $stmt->fetchAll();
}
if ($showMonitorNetworkPerformance) {
    $stmt = $pdo->query("SELECT * FROM network_performance ORDER BY logged_at DESC");
    $network_performance = $stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IT Manager Dashboard | Digiscan</title>
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
        
        .resolve-btn {
            background: var(--success-color);
            color: white;
        }
        
        .resolve-btn:hover {
            background: #27ae60;
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
                <h3>IT Manager Dashboard</h3>
                <p><?= htmlspecialchars($displayName) ?></p>
            </div>
            <div class="sidebar-menu">
                <a href="?view=manage_user_access" class="menu-item <?= $showManageUserAccess ? 'active' : '' ?>"><i class="fas fa-user-shield"></i> Manage User Access</a>
                <a href="?view=handle_data_encryption" class="menu-item <?= $showHandleDataEncryption ? 'active' : '' ?>"><i class="fas fa-lock"></i> Handle Data Encryption</a>
                <a href="?view=troubleshoot_issues" class="menu-item <?= $showTroubleshootIssues ? 'active' : '' ?>"><i class="fas fa-tools"></i> Troubleshoot Technical Issues</a>
                <a href="?view=maintain_employee_db" class="menu-item <?= $showMaintainEmployeeDB ? 'active' : '' ?>"><i class="fas fa-database"></i> Maintain Employee Database</a>
                <a href="?view=monitor_system_performance" class="menu-item <?= $showMonitorSystemPerformance ? 'active' : '' ?>"><i class="fas fa-tachometer-alt"></i> Monitor System Performance</a>
                <a href="?view=ensure_data_privacy" class="menu-item <?= $showEnsureDataPrivacy ? 'active' : '' ?>"><i class="fas fa-user-secret"></i> Ensure Data Privacy</a>
                <a href="?view=monitor_network_performance" class="menu-item <?= $showMonitorNetworkPerformance ? 'active' : '' ?>"><i class="fas fa-network-wired"></i> Monitor Network Performance</a>
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
                <?php if ($showManageUserAccess): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Manage User Access</span>
                        <div class="card-icon bg-primary"><i class="fas fa-user-shield"></i></div>
                    </div>
                    <?php if ($errorMessage): ?>
                        <div class="message error"><?= htmlspecialchars($errorMessage) ?></div>
                    <?php endif; ?>
                    <?php if ($successMessage): ?>
                        <div class="message success"><?= htmlspecialchars($successMessage) ?></div>
                    <?php endif; ?>
                    <form action="" method="POST" class="form-table">
                        <input type="hidden" name="manage_user_access" value="1">
                        <table>
                            <tr>
                                <th><label for="EMP_ID">Employee</label></th>
                                <td>
                                    <select id="EMP_ID" name="EMP_ID" required>
                                        <?php foreach ($employees as $employee): ?>
                                        <option value="<?= $employee['EMP_ID'] ?>"><?= htmlspecialchars($employee['FName'] . ' ' . $employee['LName']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="role">Role</label></th>
                                <td>
                                    <select id="role" name="role" required>
                                        <option value="Admin">Admin</option>
                                        <option value="User">User</option>
                                    </select>
                                </td>
                            </tr>
                        </table>
                        <button type="submit" class="btn"><i class="fas fa-save"></i> Update Access</button>
                    </form>
                    <?php if ($user_access): ?>
                    <table class="data-table">
                        <tr>
                            <th>Employee</th>
                            <th>Role</th>
                            <th>Updated At</th>
                        </tr>
                        <?php foreach ($user_access as $access): ?>
                        <tr>
                            <td><?= htmlspecialchars($access['FName'] . ' ' . $access['LName']) ?></td>
                            <td><?= htmlspecialchars($access['role']) ?></td>
                            <td><?= htmlspecialchars($access['updated_at']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                    <?php endif; ?>
                </div>
                <?php elseif ($showHandleDataEncryption): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Handle Data Encryption</span>
                        <div class="card-icon bg-secondary"><i class="fas fa-lock"></i></div>
                    </div>
                    <?php if ($errorMessage): ?>
                        <div class="message error"><?= htmlspecialchars($errorMessage) ?></div>
                    <?php endif; ?>
                    <?php if ($successMessage): ?>
                        <div class="message success"><?= htmlspecialchars($successMessage) ?></div>
                    <?php endif; ?>
                    <form action="" method="POST" class="form-table">
                        <input type="hidden" name="handle_data_encryption" value="1">
                        <table>
                            <tr>
                                <th><label for="data_type">Data Type</label></th>
                                <td><input type="text" id="data_type" name="data_type" required></td>
                            </tr>
                            <tr>
                                <th><label for="encryption_method">Encryption Method</label></th>
                                <td><input type="text" id="encryption_method" name="encryption_method" required></td>
                            </tr>
                        </table>
                        <button type="submit" class="btn"><i class="fas fa-save"></i> Log Encryption</button>
                    </form>
                    <?php if ($encryption_logs): ?>
                    <table class="data-table">
                        <tr>
                            <th>Data Type</th>
                            <th>Encryption Method</th>
                            <th>Logged At</th>
                        </tr>
                        <?php foreach ($encryption_logs as $log): ?>
                        <tr>
                            <td><?= htmlspecialchars($log['data_type']) ?></td>
                            <td><?= htmlspecialchars($log['encryption_method']) ?></td>
                            <td><?= htmlspecialchars($log['logged_at']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                    <?php endif; ?>
                </div>
                <?php elseif ($showTroubleshootIssues): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Troubleshoot Technical Issues</span>
                        <div class="card-icon bg-accent"><i class="fas fa-tools"></i></div>
                    </div>
                    <?php if ($errorMessage): ?>
                        <div class="message error"><?= htmlspecialchars($errorMessage) ?></div>
                    <?php endif; ?>
                    <?php if ($successMessage): ?>
                        <div class="message success"><?= htmlspecialchars($successMessage) ?></div>
                    <?php endif; ?>
                    <form action="" method="POST" class="form-table">
                        <input type="hidden" name="troubleshoot_issues" value="1">
                        <table>
                            <tr>
                                <th><label for="description">Issue Description</label></th>
                                <td><textarea id="description" name="description" required></textarea></td>
                            </tr>
                        </table>
                        <button type="submit" class="btn"><i class="fas fa-save"></i> Report Issue</button>
                    </form>
                    <?php if ($technical_issues): ?>
                    <table class="data-table">
                        <tr>
                            <th>Description</th>
                            <th>Status</th>
                            <th>Reported At</th>
                            <th>Resolved At</th>
                            <th>Action</th>
                        </tr>
                        <?php foreach ($technical_issues as $issue): ?>
                        <tr>
                            <td><?= htmlspecialchars($issue['description']) ?></td>
                            <td><?= htmlspecialchars($issue['status']) ?></td>
                            <td><?= htmlspecialchars($issue['reported_at']) ?></td>
                            <td><?= htmlspecialchars($issue['resolved_at'] ?: '-') ?></td>
                            <td>
                                <?php if ($issue['status'] === 'Open'): ?>
                                <form action="" method="POST" style="display: inline;">
                                    <input type="hidden" name="issue_id" value="<?= $issue['issue_id'] ?>">
                                    <button type="submit" name="resolve_issue" class="action-btn resolve-btn"><i class="fas fa-check"></i> Resolve</button>
                                </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                    <?php endif; ?>
                </div>
                <?php elseif ($showMaintainEmployeeDB): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Maintain Employee Database</span>
                        <div class="card-icon bg-success"><i class="fas fa-database"></i></div>
                    </div>
                    <?php if ($errorMessage): ?>
                        <div class="message error"><?= htmlspecialchars($errorMessage) ?></div>
                    <?php endif; ?>
                    <?php if ($successMessage): ?>
                        <div class="message success"><?= htmlspecialchars($successMessage) ?></div>
                    <?php endif; ?>
                    <form action="" method="POST" class="form-table">
                        <input type="hidden" name="maintain_employee_db" value="1">
                        <table>
                            <tr>
                                <th><label for="EMP_ID">Employee</label></th>
                                <td>
                                    <select id="EMP_ID" name="EMP_ID" required>
                                        <?php foreach ($employee_db as $employee): ?>
                                        <option value="<?= $employee['EMP_ID'] ?>"><?= htmlspecialchars($employee['FName'] . ' ' . $employee['LName']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="username">Username</label></th>
                                <td><input type="text" id="username" name="username" required></td>
                            </tr>
                        </table>
                        <button type="submit" class="btn"><i class="fas fa-save"></i> Update Record</button>
                    </form>
                    <?php if ($employee_db): ?>
                    <table class="data-table">
                        <tr>
                            <th>Employee</th>
                            <th>Username</th>
                            <th>Last Updated</th>
                        </tr>
                        <?php foreach ($employee_db as $record): ?>
                        <tr>
                            <td><?= htmlspecialchars($record['FName'] . ' ' . $record['LName']) ?></td>
                            <td><?= htmlspecialchars($record['username'] ?: '-') ?></td>
                            <td><?= htmlspecialchars($record['last_updated'] ?: '-') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                    <?php endif; ?>
                </div>
                <?php elseif ($showMonitorSystemPerformance): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Monitor System Performance</span>
                        <div class="card-icon bg-primary"><i class="fas fa-tachometer-alt"></i></div>
                    </div>
                    <?php if ($errorMessage): ?>
                        <div class="message error"><?= htmlspecialchars($errorMessage) ?></div>
                    <?php endif; ?>
                    <?php if ($successMessage): ?>
                        <div class="message success"><?= htmlspecialchars($successMessage) ?></div>
                    <?php endif; ?>
                    <form action="" method="POST" class="form-table">
                        <input type="hidden" name="monitor_system_performance" value="1">
                        <table>
                            <tr>
                                <th><label for="metric_type">Metric Type</label></th>
                                <td>
                                    <select id="metric_type" name="metric_type" required>
                                        <option value="CPU">CPU Usage (%)</option>
                                        <option value="Memory">Memory Usage (%)</option>
                                        <option value="Disk">Disk Usage (%)</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="value">Value</label></th>
                                <td><input type="number" id="value" name="value" step="0.01" required></td>
                            </tr>
                        </table>
                        <button type="submit" class="btn"><i class="fas fa-save"></i> Log Metric</button>
                    </form>
                    <?php if ($system_performance): ?>
                    <table class="data-table">
                        <tr>
                            <th>Metric Type</th>
                            <th>Value</th>
                            <th>Logged At</th>
                        </tr>
                        <?php foreach ($system_performance as $metric): ?>
                        <tr>
                            <td><?= htmlspecialchars($metric['metric_type']) ?></td>
                            <td><?= number_format($metric['value'], 2) ?></td>
                            <td><?= htmlspecialchars($metric['logged_at']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                    <?php endif; ?>
                </div>
                <?php elseif ($showEnsureDataPrivacy): ?>
                <div class="card">
                    <div class="card-header">
                        <span-need-html>="card-title">Ensure Data Privacy</span></div>
                    <div class="card-icon" id="user-secret"><i class="fas fa"></i></div>
                    <?php if ($errorMessage): ?>
                        <div class="message error"><?php echo htmlspecialchars($errorMessage); ?></div>
                    <?php endif; ?>
                    <?php if ($successMessage): ?>
                        <div class="message success"><?php echo htmlspecialchars($successMessage); ?></div>
                    <?php endif; ?>
                    <form action="" method="POST" class="form-table">
                        <input type="hidden" name="ensure_data_privacy" value="1">
                        <table>
                            <tr>
                                <th><label for="audit_type">Audit Type</label></th>
                                <td><input type="audit_type" id="audit_type" name="audit_type" required></td>
                            </tr>
                            <tr>
                                <th><label for="status">Status</label></th>
                                <td>
                                    <select id="status" name="status" required>
                                        <option value="Compliant">Compliant</option>
                                        <option value="Non-compliant">Non-compliant</option>
                                    </select>
                                </td>
                            </tr>
                        </table>
                        <button type="submit" class="btn"><i class="fas fa-save"></i> Log Audit</button>
                    </form>
                    <?php if ($data_privacy_audits): ?>
                    <table class="data-table">
                        <tr>
                            <th>Audit Type</th>
                            <th>Status</th>
                            <th>Logged At</th>
                        </tr>
                        <?php foreach ($data_privacy_audits as $audit): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($audit['audit_type']); ?></td>
                            <td><?php echo htmlspecialchars($audit['status']); ?></td>
                            <td><?php echo htmlspecialchars($audit['logged_at']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                    <?php endif; ?>
                </div>
                <?php elseif ($showMonitorNetworkPerformance): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Monitor Network Performance</span>
                        <div class="card-icon bg-secondary"><i class="fas fa-network-wired"></i></div>
                    </div>
                    <?php if ($errorMessage): ?>
                        <div class="message error"><?= htmlspecialchars($errorMessage) ?></div>
                    <?php endif; ?>
                    <?php if ($successMessage): ?>
                        <div class="message success"><?= htmlspecialchars($successMessage) ?></div>
                    <?php endif; ?>
                    <form action="" method="POST" class="form-table">
                        <input type="hidden" name="monitor_network_performance" value="1">
                        <table>
                            <tr>
                                <th><label for="metric_type">Metric Type</label></th>
                                <td>
                                    <select id="metric_type" name="metric_type" required>
                                        <option value="Bandwidth">Bandwidth (Mbps)</option>
                                        <option value="Latency">Latency (ms)</option>
                                        <option value="Packet Loss">Packet Loss (%)</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="value">Value</label></th>
                                <td><input type="number" id="value" name="value" step="0.01" required></td>
                            </tr>
                        </table>
                        <button type="submit" class="btn"><i class="fas fa-save"></i> Log Metric</button>
                    </form>
                    <?php if ($network_performance): ?>
                    <table class="data-table">
                        <tr>
                            <th>Metric Type</th>
                            <th>Value</th>
                            <th>Logged At</th>
                        </tr>
                        <?php foreach ($network_performance as $metric): ?>
                        <tr>
                            <td><?= htmlspecialchars($metric['metric_type']) ?></td>
                            <td><?= number_format($metric['value'], 2) ?></td>
                            <td><?= htmlspecialchars($metric['logged_at']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                    <?php endif; ?>
                </div>
                <?php else: ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Total Users</span>
                        <div class="card-icon bg-primary"><i class="fas fa-users"></i></div>
                    </div>
                    <div class="card-value"><?php
                        $stmt = $pdo->query("SELECT COUNT(*) FROM user_access");
                        echo $stmt->fetchColumn();
                    ?></div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Open Technical Issues</span>
                        <div class="card-icon bg-accent"><i class="fas fa-tools"></i></div>
                    </div>
                    <div class="card-value"><?php
                        $stmt = $pdo->query("SELECT COUNT(*) FROM technical_issues WHERE status = 'Open'");
                        echo $stmt->fetchColumn();
                    ?></div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Recent Privacy Audits</span>
                        <div class="card-icon bg-secondary"><i class="fas fa-user-secret"></i></div>
                    </div>
                    <div class="card-value"><?php
                        $stmt = $pdo->query("SELECT COUNT(*) FROM data_privacy_audits WHERE logged_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
                        echo $stmt->fetchColumn();
                    ?></div>
                </div>
                <?php endif; ?>
            </div>

            <div class="recent-activity">
                <h3 class="section-title">Recent Activity</h3>
                <div class="activity-item">
                    <div class="activity-icon"><i class="fas fa-tools"></i></div>
                    <div class="activity-content">
                        <div class="activity-title">Technical issue resolved</div>
                        <div class="activity-time">2 hours ago</div>
                    </div>
                </div>
                <div class="activity-item">
                    <div class="activity-icon"><i class="fas fa-user-secret"></i></div>
                    <div class="activity-content">
                        <div class="activity-title">Data privacy audit completed</div>
                        <div class="activity-time">Yesterday</div>
                    </div>
                </div>
                <div class="activity-item">
                    <div class="activity-icon"><i class="fas fa-user-shield"></i></div>
                    <div class="activity-content">
                        <div class="activity-title">User access updated</div>
                        <div class="activity-time">Yesterday</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>