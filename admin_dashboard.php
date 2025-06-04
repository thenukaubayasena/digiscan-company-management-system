<?php
session_start();
require 'db_connection.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: admin_login.php');
    exit;
}

$displayName = isset($_SESSION['full_name']) ? $_SESSION['full_name'] : 'Admin';
$avatar = strtoupper(substr($displayName, 0, 1));

// Initialize message variables
$successMessage = '';
$errorMessage = '';

// Initialize view variables
$showUserAccounts = false;
$showUpdateUser = false;
$showRegisterCompany = false;
$showAssignRoles = false;
$showNotifications = false;
$showBackup = false;
$showRestore = false;
$showConfig = false;
$showReports = false;
$showOrders = false;

// Determine which view to show based on GET parameter
if (isset($_GET['view'])) {
    $showUserAccounts = ($_GET['view'] === 'user_accounts');
    $showUpdateUser = ($_GET['view'] === 'update_user');
    $showRegisterCompany = ($_GET['view'] === 'register_company');
    $showAssignRoles = ($_GET['view'] === 'assign_roles');
    $showNotifications = ($_GET['view'] === 'notifications');
    $showBackup = ($_GET['view'] === 'backup');
    $showRestore = ($_GET['view'] === 'restore');
    $showConfig = ($_GET['view'] === 'config');
    $showReports = ($_GET['view'] === 'reports');
    $showOrders = ($_GET['view'] === 'orders');
} else {
    $showUserAccounts = true; // Default view
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Update User
        if (isset($_POST['update_user'])) {
            $emp_id = $_POST['emp_id'];
            $fname = $_POST['fname'];
            $lname = $_POST['lname'];
            $empnic = $_POST['empnic'];
            $email = $_POST['email'];
            $designation = $_POST['designation'];
            if (empty($fname) || empty($lname) || empty($empnic) || empty($email) || empty($designation)) {
                $errorMessage = "All fields are required.";
                $showUpdateUser = true;
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errorMessage = "Invalid email format.";
                $showUpdateUser = true;
            } else {
                $stmt = $pdo->prepare("UPDATE employees SET FName = ?, LName = ?, EmpNIC = ?, Emp_Email = ?, designation = ? WHERE EMP_ID = ?");
                $success = $stmt->execute([$fname, $lname, $empnic, $email, $designation, $emp_id]);
                if ($success) {
                    $successMessage = "User updated successfully!";
                    $showUpdateUser = true;
                } else {
                    $errorMessage = "Error updating user.";
                    $showUpdateUser = true;
                }
            }
        }
        // Register Client Company
        if (isset($_POST['register_company'])) {
            $comp_name = $_POST['comp_name'];
            $address = $_POST['address'];
            $contact = $_POST['contact'];
            $industry = $_POST['industry'];
            $comp_email = $_POST['comp_email'];
            if (empty($comp_name) || empty($address) || empty($contact) || empty($industry) || empty($comp_email)) {
                $errorMessage = "All fields are required.";
                $showRegisterCompany = true;
            } elseif (!filter_var($comp_email, FILTER_VALIDATE_EMAIL)) {
                $errorMessage = "Invalid email format.";
                $showRegisterCompany = true;
            } else {
                $stmt = $pdo->prepare("INSERT INTO client_company (comp_name, address, contact, industry, comp_email) VALUES (?, ?, ?, ?, ?)");
                $success = $stmt->execute([$comp_name, $address, $contact, $industry, $comp_email]);
                 // Check if the company already exists
                if ($success) {
                    $successMessage = "Company registered successfully!";
                    $showRegisterCompany = true;
                } else {
                    $errorMessage = "Error registering company.";
                    $showRegisterCompany = true;
                }
            }
        }
        // Assign System Roles
        if (isset($_POST['assign_roles'])) {
            $user_id = $_POST['user_id'];
            $role = $_POST['role'];
            if (empty($user_id) || empty($role)) {
                $errorMessage = "User and role are required.";
                $showAssignRoles = true;
            } else {
                $stmt = $pdo->prepare("UPDATE employees SET designation = ? WHERE EMP_ID = ?");
                $success = $stmt->execute([$role, $user_id]);
                if ($success) {
                    $successMessage = "Role assigned successfully!";
                    $showAssignRoles = true;
                } else {
                    $errorMessage = "Error assigning role.";
                    $showAssignRoles = true;
                }
            }
        }
        // Send Notifications
        if (isset($_POST['notifications'])) {
            $recipient_id = $_POST['recipient_id'];
            $message = $_POST['message'];
            if (empty($recipient_id) || empty($message)) {
                $errorMessage = "Recipient and message are required.";
                $showNotifications = true;
            } else {
                $stmt = $pdo->prepare("INSERT INTO notifications (recipient_id, message, sent_at) VALUES (?, ?, NOW())");
                $success = $stmt->execute([$recipient_id, $message]);
                if ($success) {
                    $successMessage = "Notification sent successfully!";
                    $showNotifications = true;
                } else {
                    $errorMessage = "Error sending notification.";
                    $showNotifications = true;
                }
            }
        }
        // Configure System
        if (isset($_POST['config'])) {
            $config_key = $_POST['config_key'];
            $config_value = $_POST['config_value'];
            if (empty($config_key) || empty($config_value)) {
                $errorMessage = "Key and value are required.";
                $showConfig = true;
            } else {
                $stmt = $pdo->prepare("INSERT INTO system_config (config_key, config_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE config_value = ?");
                $success = $stmt->execute([$config_key, $config_value, $config_value]);
                if ($success) {
                    $successMessage = "Configuration updated successfully!";
                    $showConfig = true;
                } else {
                    $errorMessage = "Error updating configuration.";
                    $showConfig = true;
                }
            }
        }
        // Backup Data (Placeholder)
        if (isset($_POST['backup'])) {
            $backup_file = 'backup_' . date('Ymd_His') . '.sql';
            $stmt = $pdo->prepare("INSERT INTO backup_log (backup_file, created_at) VALUES (?, NOW())");
            $success = $stmt->execute([$backup_file]);
            if ($success) {
                $successMessage = "Backup initiated successfully! File: $backup_file";
                $showBackup = true;
            } else {
                $errorMessage = "Error initiating backup.";
                $showBackup = true;
            }
        }
        // Restore Data (Placeholder)
        if (isset($_POST['restore'])) {
            $backup_file = $_POST['backup_file'];
            if (empty($backup_file)) {
                $errorMessage = "Backup file is required.";
                $showRestore = true;
            } else {
                $stmt = $pdo->prepare("INSERT INTO backup_log (backup_file, created_at) VALUES (?, NOW())");
                $success = $stmt->execute(["restore_$backup_file"]);
                if ($success) {
                    $successMessage = "Restore initiated successfully for $backup_file!";
                    $showRestore = true;
                } else {
                    $errorMessage = "Error initiating restore.";
                    $showRestore = true;
                }
            }
        }
    } catch (PDOException $e) {
        $errorMessage = "Database error: " . $e->getMessage();
        if (isset($_POST['update_user'])) $showUpdateUser = true;
        elseif (isset($_POST['register_company'])) $showRegisterCompany = true;
        elseif (isset($_POST['assign_roles'])) $showAssignRoles = true;
        elseif (isset($_POST['notifications'])) $showNotifications = true;
        elseif (isset($_POST['config'])) $showConfig = true;
        elseif (isset($_POST['backup'])) $showBackup = true;
        elseif (isset($_POST['restore'])) $showRestore = true;
    }
}

// Fetch data for views
if ($showUserAccounts) {
    $stmt = $pdo->query("SELECT EMP_ID, FName, LName, EmpNIC, Emp_Email, designation FROM employees");
    $users = $stmt->fetchAll();
}
if ($showUpdateUser && isset($_GET['user_id'])) {
    $stmt = $pdo->prepare("SELECT EMP_ID, FName, LName, EmpNIC, Emp_Email, designation FROM employees WHERE EMP_ID = ?");
    $stmt->execute([$_GET['user_id']]);
    $selectedUser = $stmt->fetch();
}
if ($showAssignRoles || $showNotifications) {
    $stmt = $pdo->query("SELECT EMP_ID, FName, LName FROM employees");
    $users = $stmt->fetchAll();
}
if ($showOrders) {
    $stmt = $pdo->query("SELECT o.*, c.comp_name AS company_name FROM client_order o JOIN client_company c ON o.client_id = c.cl_id");
    if (!$stmt) {
        die("Query failed: " . print_r($pdo->errorInfo(), true));
    }
    $orders = $stmt->fetchAll();
    
    // Debug output - remove this after testing
    error_log("Orders fetched: " . print_r($orders, true));
}
if ($showReports) {
    $stmt = $pdo->query("SELECT * FROM reports ORDER BY generated_at DESC");
    $reports = $stmt->fetchAll();
}
if ($showRestore) {
    $stmt = $pdo->query("SELECT * FROM backup_log WHERE backup_file NOT LIKE 'restore_%' ORDER BY created_at DESC");
    $backups = $stmt->fetchAll();
}
if ($showConfig) {
    $stmt = $pdo->query("SELECT * FROM system_config");
    $configs = $stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Digiscan</title>
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
            background: var(--secondary-color);
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        
        .action-btn:hover {
            background: #2980b9;
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

        .status-badge {
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .status-pending {
            background-color: #f39c12;
            color: white;
        }

        .status-processing {
            background-color: #3498db;
            color: white;
        }

        .status-completed {
            background-color: #2ecc71;
            color: white;
        }

        .status-cancelled {
            background-color: #e74c3c;
            color: white;
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
                <h3>Admin Dashboard</h3>
                <p><?= htmlspecialchars($displayName) ?></p>
            </div>
            <div class="sidebar-menu">
                <a href="?view=user_accounts" class="menu-item <?= $showUserAccounts || $showUpdateUser ? 'active' : '' ?>"><i class="fas fa-tachometer-alt"></i> View User Accounts</a>
                <a href="?view=register_company" class="menu-item <?= $showRegisterCompany ? 'active' : '' ?>"><i class="fas fa-users"></i> Register Client Companies</a>
                <a href="?view=assign_roles" class="menu-item <?= $showAssignRoles ? 'active' : '' ?>"><i class="fas fa-cog"></i> Assign System Roles</a>
                <a href="?view=notifications" class="menu-item <?= $showNotifications ? 'active' : '' ?>"><i class="fas fa-chart-bar"></i> Send Notifications</a>
                <a href="?view=backup" class="menu-item <?= $showBackup ? 'active' : '' ?>"><i class="fas fa-bell"></i> Backup Data</a>
                <a href="?view=restore" class="menu-item <?= $showRestore ? 'active' : '' ?>"><i class="fas fa-question-circle"></i> Restore Data</a>
                <a href="?view=config" class="menu-item <?= $showConfig ? 'active' : '' ?>"><i class="fas fa-question-circle"></i> Configure System</a>
                <a href="?view=reports" class="menu-item <?= $showReports ? 'active' : '' ?>"><i class="fas fa-question-circle"></i> View Reports</a>
                <a href="?view=orders" class="menu-item <?= $showOrders ? 'active' : '' ?>"><i class="fas fa-question-circle"></i> View Orders</a>
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
                <?php if ($showUserAccounts && $users): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">User Accounts</span>
                        <div class="card-icon bg-secondary"><i class="fas fa-tachometer-alt"></i></div>
                    </div>
                    <table class="data-table">
                        <tr>
                            <th>Employee ID</th>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>NIC No.</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Action</th>
                        </tr>
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= htmlspecialchars($user['EMP_ID']) ?></td>
                            <td><?= htmlspecialchars($user['FName']) ?></td>
                            <td><?= htmlspecialchars($user['LName']) ?></td>
                            <td><?= htmlspecialchars($user['EmpNIC']) ?></td>
                            <td><?= htmlspecialchars($user['Emp_Email']) ?></td>
                            <td><?= htmlspecialchars($user['designation']) ?></td>
                            <td>
                                <a href="?view=update_user&user_id=<?= $user['EMP_ID'] ?>" class="action-btn"><i class="fas fa-edit"></i> Update</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
                <?php elseif ($showUpdateUser && $selectedUser): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Update User Account</span>
                        <div class="card-icon bg-secondary"><i class="fas fa-edit"></i></div>
                    </div>
                    <?php if ($errorMessage): ?>
                        <div class="message error"><?= htmlspecialchars($errorMessage) ?></div>
                    <?php endif; ?>
                    <?php if ($successMessage): ?>
                        <div class="message success"><?= htmlspecialchars($successMessage) ?></div>
                    <?php endif; ?>
                    <form action="" method="POST" class="form-table">
                        <input type="hidden" name="update_user" value="1">
                        <input type="hidden" name="emp_id" value="<?= htmlspecialchars($selectedUser['EMP_ID']) ?>">
                        <table class="form-table">
                            <tr>
                                <th><label for="fname">First Name</label></th>
                                <td><input type="text" id="fname" name="fname" value="<?= htmlspecialchars($selectedUser['FName']) ?>" required></td>
                            </tr>
                            <tr>
                                <th><label for="lname">Last Name</label></th>
                                <td><input type="text" id="lname" name="lname" value="<?= htmlspecialchars($selectedUser['LName']) ?>" required></td>
                            </tr>
                            <tr>
                                <th><label for="empnic">NIC No.</label></th>
                                <td><input type="text" id="empnic" name="empnic" value="<?= htmlspecialchars($selectedUser['EmpNIC']) ?>" required></td>
                            </tr>
                            <tr>
                                <th><label for="email">Email</label></th>
                                <td><input type="email" id="email" name="email" value="<?= htmlspecialchars($selectedUser['Emp_Email']) ?>" required></td>
                            </tr>
                            <tr>
                                <th><label for="designation">Role</label></th>
                                <td>
                                    <select id="designation" name="designation" required>
                                        <option value="Admin" <?= $selectedUser['designation'] === 'Admin' ? 'selected' : '' ?>>Admin</option>
                                        <option value="Manager" <?= $selectedUser['designation'] === 'Manager' ? 'selected' : '' ?>>Manager</option>
                                        <option value="CEO" <?= $selectedUser['designation'] === 'CEO' ? 'selected' : '' ?>>CEO</option>
                                    </select>
                                </td>
                            </tr>
                        </table>
                        <button type="submit" class="btn"><i class="fas fa-save"></i> Save Changes</button>
                        <a href="?view=user_accounts" class="btn" style="background-color: #666; margin-left: 10px;"><i class="fas fa-times"></i> Cancel</a>
                    </form>
                </div>
                <?php elseif ($showRegisterCompany): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Register Client Company</span>
                        <div class="card-icon bg-accent"><i class="fas fa-users"></i></div>
                    </div>
                    <?php if ($errorMessage): ?>
                        <div class="message error"><?= htmlspecialchars($errorMessage) ?></div>
                    <?php endif; ?>
                    <?php if ($successMessage): ?>
                        <div class="message success"><?= htmlspecialchars($successMessage) ?></div>
                    <?php endif; ?>
                    <form action="" method="POST" class="form-table">
                        <input type="hidden" name="register_company" value="1">
                        <table class="form-table">
                            <tr>
                                <th><label for="comp_name">Company Name</label></th>
                                <td><input type="text" id="comp_name" name="comp_name" required></td>
                            </tr>
                            <tr>
                                <th><label for="address">Address</label></th>
                                <td><input type="text" id="address" name="address" required></td>
                            </tr>
                            <tr>
                                <th><label for="contact">Contact</label></th>
                                <td><input type="text" id="contact" name="contact" required></td>
                            </tr>
                            <tr>
                                <th><label for="industry">Industry</label></th>
                                <td><input type="text" id="industry" name="industry" required></td>
                            </tr>
                            <tr>
                                <th><label for="comp_email">Email</label></th>
                                <td><input type="text" id="comp_email" name="comp_email" required></td>
                            </tr>
                        </table>
                        <button type="submit" class="btn"><i class="fas fa-save"></i> Register Company</button>
                    </form>
                </div>
                <?php elseif ($showAssignRoles && $users): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Assign System Roles</span>
                        <div class="card-icon bg-primary"><i class="fas fa-cog"></i></div>
                    </div>
                    <?php if ($errorMessage): ?>
                        <div class="message error"><?= htmlspecialchars($errorMessage) ?></div>
                    <?php endif; ?>
                    <?php if ($successMessage): ?>
                        <div class="message success"><?= htmlspecialchars($successMessage) ?></div>
                    <?php endif; ?>
                    <form action="" method="POST" class="form-table">
                        <input type="hidden" name="assign_roles" value="1">
                        <table class="form-table">
                            <tr>
                                <th><label for="user_id">User</label></th>
                                <td>
                                    <select id="user_id" name="user_id" required>
                                        <option value="" disabled selected>Select User</option>
                                        <?php foreach ($users as $user): ?>
                                        <option value="<?= $user['EMP_ID'] ?>"><?= htmlspecialchars($user['FName'] . ' ' . $user['LName']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="role">Role</label></th>
                                <td>
                                    <select id="role" name="role" required>
                                        <option value="Admin">Admin</option>
                                        <option value="Manager">Manager</option>
                                        <option value="CEO">CEO</option>
                                    </select>
                                </td>
                            </tr>
                        </table>
                        <button type="submit" class="btn"><i class="fas fa-save"></i> Assign Role</button>
                    </form>
                </div>
                <?php elseif ($showNotifications && $users): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Send Notifications</span>
                        <div class="card-icon bg-success"><i class="fas fa-chart-bar"></i></div>
                    </div>
                    <?php if ($errorMessage): ?>
                        <div class="message error"><?= htmlspecialchars($errorMessage) ?></div>
                    <?php endif; ?>
                    <?php if ($successMessage): ?>
                        <div class="message success"><?= htmlspecialchars($successMessage) ?></div>
                    <?php endif; ?>
                    <form action="" method="POST" class="form-table">
                        <input type="hidden" name="notifications" value="1">
                        <table class="form-table">
                            <tr>
                                <th><label for="recipient_id">Recipient</label></th>
                                <td>
                                    <select id="recipient_id" name="recipient_id" required>
                                        <option value="" disabled selected>Select User</option>
                                        <?php foreach ($users as $user): ?>
                                        <option value="<?= $user['EMP_ID'] ?>"><?= htmlspecialchars($user['FName'] . ' ' . $user['LName']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="message">Message</label></th>
                                <td><textarea id="message" name="message" required></textarea></td>
                            </tr>
                        </table>
                        <button type="submit" class="btn"><i class="fas fa-save"></i> Send Notification</button>
                    </form>
                </div>
                <?php elseif ($showBackup): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Backup Data</span>
                        <div class="card-icon bg-accent"><i class="fas fa-bell"></i></div>
                    </div>
                    <?php if ($errorMessage): ?>
                        <div class="message error"><?= htmlspecialchars($errorMessage) ?></div>
                    <?php endif; ?>
                    <?php if ($successMessage): ?>
                        <div class="message success"><?= htmlspecialchars($successMessage) ?></div>
                    <?php endif; ?>
                    <form action="backup_download.php" method="post">
                        <button type="submit" class="btn">
                            <i class="fas fa-download"></i> Initiate & Download Backup
                        </button>
                    </form>
                </div>
                <?php elseif ($showRestore && $backups): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Restore Data</span>
                        <div class="card-icon bg-secondary"><i class="fas fa-question-circle"></i></div>
                    </div>
                    <?php if ($errorMessage): ?>
                        <div class="message error"><?= htmlspecialchars($errorMessage) ?></div>
                    <?php endif; ?>
                    <?php if ($successMessage): ?>
                        <div class="message success"><?= htmlspecialchars($successMessage) ?></div>
                    <?php endif; ?>
                    <form action="" method="POST" class="form-table">
                        <input type="hidden" name="restore" value="1">
                        <table class="form-table">
                            <tr>
                                <th><label for="backup_file">Backup File</label></th>
                                <td>
                                    <select id="backup_file" name="backup_file" required>
                                        <option value="" disabled selected>Select Backup</option>
                                        <?php foreach ($backups as $backup): ?>
                                        <option value="<?= htmlspecialchars($backup['backup_file']) ?>"><?= htmlspecialchars($backup['backup_file']) ?> (<?= $backup['created_at'] ?>)</option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                        </table>
                        <button type="submit" class="btn"><i class="fas fa-save"></i> Initiate Restore</button>
                    </form>
                </div>
                <?php elseif ($showConfig): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Configure System</span>
                        <div class="card-icon bg-primary"><i class="fas fa-cog"></i></div>
                    </div>
                    <?php if ($errorMessage): ?>
                        <div class="message error"><?= htmlspecialchars($errorMessage) ?></div>
                    <?php endif; ?>
                    <?php if ($successMessage): ?>
                        <div class="message success"><?= htmlspecialchars($successMessage) ?></div>
                    <?php endif; ?>
                    
                    <h3 style="margin: 15px 0 10px;">Add/Update Configuration</h3>
                    <form action="" method="POST" class="form-table">
                        <input type="hidden" name="config" value="1">
                        <table class="form-table">
                            <tr>
                                <th><label for="config_key">Configuration Key</label></th>
                                <td>
                                    <input type="text" id="config_key" name="config_key" required>
                                    <small style="color: #666;">Example: site_name, maintenance_mode, etc.</small>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="config_value">Value</label></th>
                                <td>
                                    <input type="text" id="config_value" name="config_value" required>
                                    <small style="color: #666;">Enter the value for this configuration</small>
                                </td>
                            </tr>
                        </table>
                        <button type="submit" class="btn"><i class="fas fa-save"></i> Save Configuration</button>
                    </form>

                    <?php if (!empty($configs)): ?>
                        <h3 style="margin: 25px 0 10px;">Current System Configurations</h3>
                        <table class="data-table">
                            <tr>
                                <th>Key</th>
                                <th>Value</th>
                            </tr>
                            <?php foreach ($configs as $config): ?>
                            <tr>
                                <td><?= htmlspecialchars($config['config_key']) ?></td>
                                <td><?= htmlspecialchars($config['config_value']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </table>
                    <?php else: ?>
                        <div style="margin-top: 20px; padding: 15px; background-color: #f8f9fa; border-radius: 4px;">
                            No system configurations found.
                        </div>
                    <?php endif; ?>
                </div>
                <?php elseif ($showReports): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">View Reports</span>
                        <div class="card-icon bg-success"><i class="fas fa-file-alt"></i></div>
                    </div>
                    
                    <?php if (!empty($reports)): ?>
                        <table class="data-table">
                            <tr>
                                <th>Report Type</th>
                                <th>Generated At</th>
                                <th>File Path</th>
                            </tr>
                            <?php foreach ($reports as $report): ?>
                            <tr>
                                <td><?= htmlspecialchars($report['report_type']) ?></td>
                                <td><?= htmlspecialchars($report['generated_at']) ?></td>
                                <td><?= htmlspecialchars($report['file_path']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </table>
                    <?php else: ?>
                        <div style="padding: 20px; text-align: center; color: #666;">
                            No reports found in the system.
                        </div>
                    <?php endif; ?>
                </div>
                <?php elseif ($showOrders): ?>
                    <div class="card">
                        <div class="card-header">
                            <span class="card-title">View Orders</span>
                            <div class="card-icon bg-accent"><i class="fas fa-shopping-cart"></i></div>
                        </div>
                        
                        <?php if (!empty($orders)): ?>
                            <table class="data-table">
                                <tr>
                                    <th>Order ID</th>
                                    <th>Client</th>
                                    <th>Order Name</th>
                                    <th>Order Date</th>
                                    <th>Due Date</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                </tr>
                                <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td><?= htmlspecialchars($order['order_id']) ?></td>
                                    <td><?= htmlspecialchars($order['company_name']) ?></td>
                                    <td><?= htmlspecialchars($order['order_name']) ?></td>
                                    <td><?= htmlspecialchars($order['order_date']) ?></td>
                                    <td><?= htmlspecialchars($order['due_date']) ?></td>
                                    <td><?= number_format($order['amount'], 2) ?></td>
                                    <td>
                                        <span class="status-badge status-<?= htmlspecialchars($order['status']) ?>">
                                            <?= ucfirst(htmlspecialchars($order['status'])) ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </table>
                        <?php else: ?>
                            <div style="padding: 20px; text-align: center; color: #666;">
                                No orders found in the system.
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>