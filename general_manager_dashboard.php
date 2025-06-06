<?php
session_start();
require 'db_connection.php';

$displayName = isset($_SESSION['FName'], $_SESSION['LName']) ? $_SESSION['FName'] . ' ' . $_SESSION['LName'] : 'General Manager';
$avatar = strtoupper(substr($displayName, 0, 1));

// Initialize message variables
$successMessage = '';
$errorMessage = '';

// Initialize view variables
$showLeaveRequests = false;
$showViewEmployees = false;
$showUpdateEmployees = false;
$showReports = false;
$showCustomerFeedback = false;
$showDepartmentalBudgets = false;

// Determine which view to show based on GET parameter
if (isset($_GET['view'])) {
    $showLeaveRequests = ($_GET['view'] === 'leave_requests');
    $showViewEmployees = ($_GET['view'] === 'view_employees');
    $showUpdateEmployees = ($_GET['view'] === 'update_employees');
    $showReports = ($_GET['view'] === 'reports');
    $showCustomerFeedback = ($_GET['view'] === 'customer_feedback');
    $showDepartmentalBudgets = ($_GET['view'] === 'departmental_budgets');
} else {
    // Default view: show summary cards
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Approve/Reject Leave Request
        if (isset($_POST['approve_leave']) || isset($_POST['reject_leave'])) {
            $leave_id = $_POST['leave_id'];
            $status = isset($_POST['approve_leave']) ? 'Approved' : 'Rejected';
            $stmt = $pdo->prepare("UPDATE leave_requests SET status = ? WHERE leave_id = ?");
            $success = $stmt->execute([$status, $leave_id]);
            if ($success) {
                $successMessage = "Leave request $status successfully!";
                $showLeaveRequests = true;
            } else {
                $errorMessage = "Error updating leave request.";
                $showLeaveRequests = true;
            }
        }
        // Update Employee
        if (isset($_POST['update_employee'])) {
            $emp_id = $_POST['emp_id'];
            $fname = $_POST['fname'];
            $lname = $_POST['lname'];
            $empnic = $_POST['empnic'];
            $email = $_POST['email'];
            $contact_no = $_POST['contact_no'];
            $dob = $_POST['dob'];
            if (empty($fname) || empty($lname) || empty($empnic) || empty($email) || empty($contact_no) || empty($dob)) {
                $errorMessage = "All fields are required.";
                $showUpdateEmployees = true;
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errorMessage = "Invalid email format.";
                $showUpdateEmployees = true;
            } elseif (strtotime($dob) > time()) {
                $errorMessage = "Date of birth cannot be in the future.";
                $showUpdateEmployees = true;
            } else {
                $stmt = $pdo->prepare("UPDATE employees SET FName = ?, LName = ?, EmpNIC = ?, Emp_Email = ?, EmpContact_No = ?, DOB = ? WHERE EMP_ID = ?");
                $success = $stmt->execute([$fname, $lname, $empnic, $email, $contact_no, $dob, $emp_id]);
                if ($success) {
                    $successMessage = "Employee updated successfully!";
                    $showUpdateEmployees = true;
                } else {
                    $errorMessage = "Error updating employee.";
                    $showUpdateEmployees = true;
                }
            }
        }
    } catch (PDOException $e) {
        $errorMessage = "Database error: " . $e->getMessage();
        if (isset($_POST['approve_leave']) || isset($_POST['reject_leave'])) $showLeaveRequests = true;
        elseif (isset($_POST['update_employee'])) $showUpdateEmployees = true;
    }
}

// Fetch data for views
if ($showLeaveRequests) {
    $stmt = $pdo->query("SELECT lr.leave_id, lr.EMP_ID, lr.start_date, lr.end_date, lr.reason, lr.status, lr.submitted_at, e.FName, e.LName 
                         FROM leave_requests lr 
                         JOIN employees e ON lr.EMP_ID = e.EMP_ID 
                         WHERE lr.status = 'Pending' 
                         ORDER BY lr.submitted_at DESC");
    $leave_requests = $stmt->fetchAll();
}
if ($showViewEmployees) {
    $stmt = $pdo->query("SELECT EMP_ID, FName, LName, EmpNIC, Emp_Email, designation FROM employees");
    $employees = $stmt->fetchAll();
}
if ($showUpdateEmployees && isset($_GET['emp_id'])) {
    $stmt = $pdo->prepare("SELECT EMP_ID, FName, LName, EmpNIC, Emp_Email, EmpContact_No, DOB FROM employees WHERE EMP_ID = ?");
    $stmt->execute([$_GET['emp_id']]);
    $selectedEmployee = $stmt->fetch();
}
if ($showReports) {
    $stmt = $pdo->query("SELECT report_id, title, generated_at, file_path FROM reports ORDER BY generated_at DESC");
    $reports = $stmt->fetchAll();
}
if ($showCustomerFeedback) {
    $stmt = $pdo->query("SELECT feedback_id, customer_name, feedback, rating, submitted_at FROM customer_feedback ORDER BY submitted_at DESC");
    $feedbacks = $stmt->fetchAll();
}
if ($showDepartmentalBudgets) {
    $stmt = $pdo->query("SELECT budget_id, department, allocated_amount, spent_amount, fiscal_year FROM departmental_budgets ORDER BY fiscal_year DESC");
    $budgets = $stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>General Manager Dashboard | Digiscan</title>
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
                <h3>General Manager Dashboard</h3>
                <p><?= htmlspecialchars($displayName) ?></p>
            </div>
            <div class="sidebar-menu">
                <a href="?view=view_employees" class="menu-item <?= $showViewEmployees || $showUpdateEmployees ? 'active' : '' ?>"><i class="fas fa-users"></i> View Employees</a>
                <a href="?view=reports" class="menu-item <?= $showReports ? 'active' : '' ?>"><i class="fas fa-file-alt"></i> View Reports</a>
                <a href="?view=customer_feedback" class="menu-item <?= $showCustomerFeedback ? 'active' : '' ?>"><i class="fas fa-comment-dots"></i> Review Customer Feedback</a>
                <a href="?view=leave_requests" class="menu-item <?= $showLeaveRequests ? 'active' : '' ?>"><i class="fas fa-calendar-times"></i> View Leave Requests</a>
                <a href="?view=departmental_budgets" class="menu-item <?= $showDepartmentalBudgets ? 'active' : '' ?>"><i class="fas fa-dollar-sign"></i> View Departmental Budgets</a>
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
                <?php if ($showLeaveRequests && $leave_requests): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Leave Requests</span>
                        <div class="card-icon bg-primary"><i class="fas fa-calendar-times"></i></div>
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
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Reason</th>
                            <th>Submitted At</th>
                            <th>Action</th>
                        </tr>
                        <?php foreach ($leave_requests as $request): ?>
                        <tr>
                            <td><?= htmlspecialchars($request['FName'] . ' ' . $request['LName']) ?></td>
                            <td><?= htmlspecialchars($request['start_date']) ?></td>
                            <td><?= htmlspecialchars($request['end_date']) ?></td>
                            <td><?= htmlspecialchars($request['reason']) ?></td>
                            <td><?= htmlspecialchars($request['submitted_at']) ?></td>
                            <td>
                                <form action="" method="POST" style="display: inline;">
                                    <input type="hidden" name="leave_id" value="<?= $request['leave_id'] ?>">
                                    <button type="submit" name="approve_leave" class="action-btn approve-btn"><i class="fas fa-check"></i> Approve</button>
                                    <button type="submit" name="reject_leave" class="action-btn reject-btn"><i class="fas fa-times"></i> Reject</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
                <?php elseif ($showViewEmployees && $employees): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Employees</span>
                        <div class="card-icon bg-secondary"><i class="fas fa-users"></i></div>
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
                        <?php foreach ($employees as $employee): ?>
                        <tr>
                            <td><?= htmlspecialchars($employee['EMP_ID']) ?></td>
                            <td><?= htmlspecialchars($employee['FName']) ?></td>
                            <td><?= htmlspecialchars($employee['LName']) ?></td>
                            <td><?= htmlspecialchars($employee['EmpNIC']) ?></td>
                            <td><?= htmlspecialchars($employee['Emp_Email']) ?></td>
                            <td><?= htmlspecialchars($employee['designation']) ?></td>
                            <td>
                                <a href="?view=update_employees&emp_id=<?= $employee['EMP_ID'] ?>" class="action-btn"><i class="fas fa-edit"></i> Update</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
                <?php elseif ($showUpdateEmployees && $selectedEmployee): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Update Employee</span>
                        <div class="card-icon bg-accent"><i class="fas fa-user-edit"></i></div>
                    </div>
                    <?php if ($errorMessage): ?>
                        <div class="message error"><?= htmlspecialchars($errorMessage) ?></div>
                    <?php endif; ?>
                    <?php if ($successMessage): ?>
                        <div class="message success"><?= htmlspecialchars($successMessage) ?></div>
                    <?php endif; ?>
                    <form action="" method="POST" class="form-table">
                        <input type="hidden" name="update_employee" value="1">
                        <input type="hidden" name="emp_id" value="<?= htmlspecialchars($selectedEmployee['EMP_ID']) ?>">
                        <table class="form-table">
                            <tr>
                                <th><label for="fname">First Name</label></th>
                                <td><input type="text" id="fname" name="fname" value="<?= htmlspecialchars($selectedEmployee['FName']) ?>" required></td>
                            </tr>
                            <tr>
                                <th><label for="lname">Last Name</label></th>
                                <td><input type="text" id="lname" name="lname" value="<?= htmlspecialchars($selectedEmployee['LName']) ?>" required></td>
                            </tr>
                            <tr>
                                <th><label for="empnic">NIC No.</label></th>
                                <td><input type="text" id="empnic" name="empnic" value="<?= htmlspecialchars($selectedEmployee['EmpNIC']) ?>" required></td>
                            </tr>
                            <tr>
                                <th><label for="email">Email</label></th>
                                <td><input type="email" id="email" name="email" value="<?= htmlspecialchars($selectedEmployee['Emp_Email']) ?>" required></td>
                            </tr>
                            <tr>
                                <th><label for="contact_no">Contact No.</label></th>
                                <td><input type="text" id="contact_no" name="contact_no" value="<?= htmlspecialchars($selectedEmployee['EmpContact_No']) ?>" required></td>
                            </tr>
                            <tr>
                                <th><label for="dob">Date of Birth</label></th>
                                <td><input type="date" id="dob" name="dob" value="<?= htmlspecialchars($selectedEmployee['DOB']) ?>" required></td>
                            </tr>
                        </table>
                        <button type="submit" class="btn"><i class="fas fa-save"></i> Save Changes</button>
                        <a href="?view=view_employees" class="btn" style="background-color: #666; margin-left: 10px;"><i class="fas fa-times"></i> Cancel</a>
                    </form>
                </div>
                <?php elseif ($showReports && $reports): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Reports</span>
                        <div class="card-icon bg-primary"><i class="fas fa-file-alt"></i></div>
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
                <?php elseif ($showCustomerFeedback && $feedbacks): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Customer Feedback</span>
                        <div class="card-icon bg-success"><i class="fas fa-comment-dots"></i></div>
                    </div>
                    <table class="data-table">
                        <tr>
                            <th>Customer Name</th>
                            <th>Feedback</th>
                            <th>Rating</th>
                            <th>Submitted At</th>
                        </tr>
                        <?php foreach ($feedbacks as $feedback): ?>
                        <tr>
                            <td><?= htmlspecialchars($feedback['customer_name']) ?></td>
                            <td><?= htmlspecialchars($feedback['feedback']) ?></td>
                            <td><?= htmlspecialchars($feedback['rating']) ?>/5</td>
                            <td><?= htmlspecialchars($feedback['submitted_at']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
                <?php elseif ($showDepartmentalBudgets && $budgets): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Departmental Budgets</span>
                        <div class="card-icon bg-accent"><i class="fas fa-dollar-sign"></i></div>
                    </div>
                    <table class="data-table">
                        <tr>
                            <th>Department</th>
                            <th>Allocated Amount</th>
                            <th>Spent Amount</th>
                            <th>Fiscal Year</th>
                        </tr>
                        <?php foreach ($budgets as $budget): ?>
                        <tr>
                            <td><?= htmlspecialchars($budget['department']) ?></td>
                            <td>$<?= number_format($budget['allocated_amount'], 2) ?></td>
                            <td>$<?= number_format($budget['spent_amount'], 2) ?></td>
                            <td><?= htmlspecialchars($budget['fiscal_year']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
                <?php else: ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Pending Leave Requests</span>
                        <div class="card-icon bg-primary"><i class="fas fa-calendar-times"></i></div>
                    </div>
                    <div class="card-value"><?php
                        $stmt = $pdo->query("SELECT COUNT(*) FROM leave_requests WHERE status = 'Pending'");
                        echo $stmt->fetchColumn();
                    ?></div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Total Employees</span>
                        <div class="card-icon bg-secondary"><i class="fas fa-users"></i></div>
                    </div>
                    <div class="card-value"><?php
                        $stmt = $pdo->query("SELECT COUNT(*) FROM employees");
                        echo $stmt->fetchColumn();
                    ?></div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Total Reports</span>
                        <div class="card-icon bg-primary"><i class="fas fa-file-alt"></i></div>
                    </div>
                    <div class="card-value"><?php
                        $stmt = $pdo->query("SELECT COUNT(*) FROM reports");
                        echo $stmt->fetchColumn();
                    ?></div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Customer Feedback</span>
                        <div class="card-icon bg-success"><i class="fas fa-comment-dots"></i></div>
                    </div>
                    <div class="card-value"><?php
                        $stmt = $pdo->query("SELECT COUNT(*) FROM customer_feedback");
                        echo $stmt->fetchColumn();
                    ?></div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>