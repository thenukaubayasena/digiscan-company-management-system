<?php
session_start();
require 'db_connection.php';

if (!isset($_SESSION['employee_id'])) {
    header("Location: employee_login.php");
    exit;
}

$displayName = isset($_SESSION['employee_name']) ? $_SESSION['employee_name'] : 'Employee';
$avatar = strtoupper(substr($displayName, 0, 1));
$employeeId = $_SESSION['employee_id'];

// Initialize message variables
$successMessage = '';
$errorMessage = '';

// Initialize view variables
$showProfile = false;
$showUpdate = false;
$showTasks = false;
$showUpdateTask = false;
$showAttendance = false;
$showLeaveRequest = false;
$showPerformanceReview = false;
$showFeedback = false;
$showSchedule = false;

// Determine which view to show based on GET parameter
if (isset($_GET['view'])) {
    $showProfile = ($_GET['view'] === 'profile');
    $showUpdate = ($_GET['view'] === 'update');
    $showTasks = ($_GET['view'] === 'tasks');
    $showUpdateTask = ($_GET['view'] === 'update_task');
    $showAttendance = ($_GET['view'] === 'attendance');
    $showLeaveRequest = ($_GET['view'] === 'leave_request');
    $showPerformanceReview = ($_GET['view'] === 'performance_review');
    $showFeedback = ($_GET['view'] === 'feedback');
    $showSchedule = ($_GET['view'] === 'schedule');
} else {
    // Default to profile view
    $showProfile = true;
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Update Personal Info
        if (isset($_POST['update_info'])) {
            $fname = $_POST['fname'];
            $lname = $_POST['lname'];
            $username = $_POST['username'];
            $empnic = $_POST['empnic'];
            $email = $_POST['email'];
            $contact = $_POST['contact'];
            $dob = $_POST['dob'];
            $designation = $_POST['designation'];
            $experience = $_POST['experience'];
            $dependent_name = $_POST['dependent_name'];
            $relationship = $_POST['relationship'];

            if (empty($fname) || empty($lname) || empty($username) || empty($empnic) || empty($email) || empty($contact) || empty($dob) || empty($designation) || empty($experience) || empty($dependent_name) || empty($relationship)) {
                $errorMessage = "All fields are required.";
                $showUpdate = true;
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errorMessage = "Invalid email format.";
                $showUpdate = true;
            } else {
                $stmt = $pdo->prepare("UPDATE employees SET FName = ?, LName = ?, username = ?, EmpNIC = ?, Emp_Email = ?, EmpContact_No = ?, DOB = ?, designation = ?, years_of_experience = ?, Dependent_Name = ?, Relationship = ? WHERE EMP_ID = ?");
                $success = $stmt->execute([$fname, $lname, $username, $empnic, $email, $contact, $dob, $designation, $experience, $dependent_name, $relationship, $employeeId]);
                if ($success) {
                    $_SESSION['employee_name'] = "$fname $lname";
                    $successMessage = "Profile updated successfully!";
                    $showUpdate = true;
                    $showProfile = false;
                } else {
                    $errorMessage = "Error updating profile.";
                    $showUpdate = true;
                }
            }
        }

        // Update Task Status
        if (isset($_POST['update_task'])) {
            $taskId = $_POST['task_id'];
            $status = $_POST['status'];

            if (!empty($taskId) && !empty($status)) {
                $stmt = $pdo->prepare("UPDATE task SET status = ? WHERE task_id = ?");
                $stmt->execute([$status, $taskId]);
                $successMessage = "Task status updated successfully!";
            } else {
                $errorMessage = "Please select a task and status.";
            }
        }

        // Add Attendance
        if (isset($_POST['add_attendance'])) {
            $date = $_POST['date'];
            $status = $_POST['status'];
            if (empty($date) || empty($status)) {
                $errorMessage = "Date and status are required.";
                $showAttendance = true;
            } else {
                $stmt = $pdo->prepare("INSERT INTO attendance (employee_id, date, status) VALUES (?, ?, ?)");
                $success = $stmt->execute([$employeeId, $date, $status]);
                if ($success) {
                    $successMessage = "Attendance recorded successfully!";
                    $showAttendance = true;
                } else {
                    $errorMessage = "Error recording attendance.";
                    $showAttendance = true;
                }
            }
        }

        // Submit Leave Request
        if (isset($_POST['leave_request'])) {
            $req_date = $_POST['req_date'];
            $start_date = $_POST['st_date'];
            $end_date = $_POST['end_date'];
            $reason = $_POST['reason'];
            if (empty($req_date) || empty($start_date) || empty($end_date) || empty($reason)) {
                $errorMessage = "All fields are required.";
                $showLeaveRequest = true;
            } elseif (strtotime($end_date) < strtotime($start_date)) {
                $errorMessage = "End date must be after start date.";
                $showLeaveRequest = true;
            } else {
                $stmt = $pdo->prepare("INSERT INTO leave_requests (employee_id, req_date, st_date, end_date, reason, status) VALUES (?, ?, ?, ?, ?, 'Pending')");
                $success = $stmt->execute([$employeeId, $req_date, $start_date, $end_date, $reason]);
                if ($success) {
                    $successMessage = "Leave request submitted successfully!";
                    $showLeaveRequest = true;
                } else {
                    $errorMessage = "Error submitting leave request.";
                    $showLeaveRequest = true;
                }
            }
        }

        // Submit Performance Review
        if (isset($_POST['performance_review'])) {
            $rating = $_POST['rating'];
            $comments = $_POST['comments'];
            if (empty($rating) || empty($comments)) {
                $errorMessage = "Rating and comments are required.";
                $showPerformanceReview = true;
            } elseif ($rating < 1 || $rating > 5) {
                $errorMessage = "Rating must be between 1 and 5.";
                $showPerformanceReview = true;
            } else {
                $stmt = $pdo->prepare("INSERT INTO performance_reviews (employee_id, rating, comments, submission_date) VALUES (?, ?, ?, CURDATE())");
                $success = $stmt->execute([$employeeId, $rating, $comments]);
                if ($success) {
                    $successMessage = "Performance review submitted successfully!";
                    $showPerformanceReview = true;
                } else {
                    $errorMessage = "Error submitting performance review.";
                    $showPerformanceReview = true;
                }
            }
        }

        // Provide Feedback
        if (isset($_POST['feedback'])) {
            $feedback_text = $_POST['feedback_text'];
            if (empty($feedback_text)) {
                $errorMessage = "Feedback text is required.";
                $showFeedback = true;
            } else {
                $stmt = $pdo->prepare("INSERT INTO feedback (employee_id, feedback_text, submission_date) VALUES (?, ?, CURDATE())");
                $success = $stmt->execute([$employeeId, $feedback_text]);
                if ($success) {
                    $successMessage = "Feedback submitted successfully!";
                    $showFeedback = true;
                } else {
                    $errorMessage = "Error submitting feedback.";
                    $showFeedback = true;
                }
            }
        }
    } catch (PDOException $e) {
        $errorMessage = "Database error: " . $e->getMessage();
        if (isset($_POST['update_info'])) $showUpdate = true;
        elseif (isset($_POST['update_task'])) $showUpdateTask = true;
        elseif (isset($_POST['add_attendance'])) $showAttendance = true;
        elseif (isset($_POST['leave_request'])) $showLeaveRequest = true;
        elseif (isset($_POST['performance_review'])) $showPerformanceReview = true;
        elseif (isset($_POST['feedback'])) $showFeedback = true;
    }
}

// Fetch employee info
$stmt = $pdo->prepare("SELECT * FROM employees WHERE EMP_ID = ?");
$stmt->execute([$employeeId]);
$employee = $stmt->fetch();

// Fetch tasks for View Assigned Tasks and Update Task Status
if ($showTasks || $showUpdateTask) {
    $stmt = $pdo->prepare("SELECT * FROM task WHERE employee_id = ?");
    $stmt->execute([$employeeId]);
    $tasks = $stmt->fetchAll();
}

// Fetch work schedule
if ($showSchedule) {
    $stmt = $pdo->prepare("SELECT * FROM work_schedule WHERE employee_id = ? ORDER BY date ASC");
    $stmt->execute([$employeeId]);
    $schedules = $stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Employee Dashboard | Digiscan</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
        
        .profile-table, .update-form, .tasks-table, .schedule-table {
            width: 100%;
        }
        
        .profile-table td, .update-form td, .tasks-table td, .schedule-table td {
            padding: 8px;
            border-bottom: 1px solid #eee;
        }
        
        .profile-table th, .update-form th, .tasks-table th, .schedule-table th {
            text-align: left;
            padding: 8px;
            color: var(--secondary-color);
        }
        
        .update-form input, .update-form select, .form input, .form select, .form textarea {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }
        
        .update-form select, .form select {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23333' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 0.5rem center;
            background-size: 1rem;
        }
        
        .form textarea {
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
                <h3>Employee Dashboard</h3>
                <p><?= htmlspecialchars($displayName) ?></p>
            </div>
            <div class="sidebar-menu">
                <a href="?view=profile" class="menu-item <?= $showProfile ? 'active' : '' ?>"><i class="fas fa-user"></i> View Personal Info</a>
                <a href="?view=update" class="menu-item <?= $showUpdate ? 'active' : '' ?>"><i class="fas fa-edit"></i> Update Personal Info</a>
                <a href="?view=tasks" class="menu-item <?= $showTasks ? 'active' : '' ?>"><i class="fas fa-tasks"></i> View Assigned Tasks</a>
                <a href="?view=update_task" class="menu-item <?= $showUpdateTask ? 'active' : '' ?>"><i class="fas fa-check-square"></i> Update Task Status</a>
                <a href="?view=attendance" class="menu-item <?= $showAttendance ? 'active' : '' ?>"><i class="fas fa-calendar-check"></i> Add Attendance</a>
                <a href="?view=leave_request" class="menu-item <?= $showLeaveRequest ? 'active' : '' ?>"><i class="fas fa-paper-plane"></i> Submit Leave Request</a>
                <a href="?view=performance_review" class="menu-item <?= $showPerformanceReview ? 'active' : '' ?>"><i class="fas fa-chart-line"></i> Submit Performance Review</a>
                <a href="?view=feedback" class="menu-item <?= $showFeedback ? 'active' : '' ?>"><i class="fas fa-comment-dots"></i> Provide Feedback</a>
                <a href="?view=schedule" class="menu-item <?= $showSchedule ? 'active' : '' ?>"><i class="fas fa-calendar-alt"></i> View Work Schedule</a>
                <a href="employee_register.php" class="menu-item" style="margin-top: 2rem; color: #e74c3c;">
                    <i class="fas fa-sign-out-alt"></i> Logout</a>
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
                <?php if ($showProfile && $employee): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Your Profile</span>
                        <div class="card-icon bg-secondary"><i class="fas fa-user"></i></div>
                    </div>
                    <?php if ($successMessage): ?>
                        <div class="message success"><?= htmlspecialchars($successMessage) ?></div>
                    <?php endif; ?>
                    <table class="profile-table">
                        <tr><th>Full Name</th><td><?= htmlspecialchars($employee['FName'] . ' ' . $employee['LName']) ?></td></tr>
                        <tr><th>Username</th><td><?= htmlspecialchars($employee['username']) ?></td></tr>
                        <tr><th>NIC</th><td><?= htmlspecialchars($employee['EmpNIC']) ?></td></tr>
                        <tr><th>Email</th><td><?= htmlspecialchars($employee['Emp_Email']) ?></td></tr>
                        <tr><th>Contact</th><td><?= htmlspecialchars($employee['EmpContact_No']) ?></td></tr>
                        <tr><th>DOB</th><td><?= htmlspecialchars($employee['DOB']) ?></td></tr>
                        <tr><th>Designation</th><td><?= htmlspecialchars($employee['designation']) ?></td></tr>
                        <tr><th>Experience</th><td><?= htmlspecialchars($employee['years_of_experience']) ?> years</td></tr>
                        <tr><th>Dependent</th><td><?= htmlspecialchars($employee['Dependent_Name'] . ' (' . $employee['Relationship'] . ')') ?></td></tr>
                    </table>
                    <a href="?view=update"><button class="btn"><i class="fas fa-edit"></i> Edit Information</button></a>
                </div>
                <?php elseif ($showUpdate && $employee): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Update Personal Info</span>
                        <div class="card-icon bg-secondary"><i class="fas fa-edit"></i></div>
                    </div>
                    <?php if ($errorMessage): ?>
                        <div class="message error"><?= htmlspecialchars($errorMessage) ?></div>
                    <?php endif; ?>
                    <?php if ($successMessage): ?>
                        <div class="message success"><?= htmlspecialchars($successMessage) ?></div>
                    <?php endif; ?>
                    <form action="" method="POST" class="update-form">
                        <input type="hidden" name="update_info" value="1">
                        <table class="update-form">
                            <tr>
                                <th><label for="fname">First Name</label></th>
                                <td><input type="text" id="fname" name="fname" value="<?= htmlspecialchars($employee['FName']) ?>" required></td>
                            </tr>
                            <tr>
                                <th><label for="lname">Last Name</label></th>
                                <td><input type="text" id="lname" name="lname" value="<?= htmlspecialchars($employee['LName']) ?>" required></td>
                            </tr>
                            <tr>
                                <th><label for="username">Username</label></th>
                                <td><input type="text" id="username" name="username" value="<?= htmlspecialchars($employee['username']) ?>" required></td>
                            </tr>
                            <tr>
                                <th><label for="empnic">NIC</label></th>
                                <td><input type="text" id="empnic" name="empnic" value="<?= htmlspecialchars($employee['EmpNIC']) ?>" required></td>
                            </tr>
                            <tr>
                                <th><label for="email">Email</label></th>
                                <td><input type="email" id="email" name="email" value="<?= htmlspecialchars($employee['Emp_Email']) ?>" required></td>
                            </tr>
                            <tr>
                                <th><label for="contact">Contact Number</label></th>
                                <td><input type="tel" id="contact" name="contact" value="<?= htmlspecialchars($employee['EmpContact_No']) ?>" required></td>
                            </tr>
                            <tr>
                                <th><label for="dob">Date of Birth</label></th>
                                <td><input type="date" id="dob" name="dob" value="<?= htmlspecialchars($employee['DOB']) ?>" required></td>
                            </tr>
                            <tr>
                                <th><label for="designation">Designation</label></th>
                                <td>
                                    <select id="designation" name="designation" required>
                                        <option value="Admin" <?= $employee['designation'] === 'Admin' ? 'selected' : '' ?>>Admin</option>
                                        <option value="Manager" <?= $employee['designation'] === 'Manager' ? 'selected' : '' ?>>Manager</option>
                                        <option value="CEO" <?= $employee['designation'] === 'CEO' ? 'selected' : '' ?>>CEO</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="experience">Years of Experience</label></th>
                                <td><input type="number" id="experience" name="experience" value="<?= htmlspecialchars($employee['years_of_experience']) ?>" min="0" required></td>
                            </tr>
                            <tr>
                                <th><label for="dependent_name">Dependent Name</label></th>
                                <td><input type="text" id="dependent_name" name="dependent_name" value="<?= htmlspecialchars($employee['Dependent_Name']) ?>" required></td>
                            </tr>
                            <tr>
                                <th><label for="relationship">Relationship</label></th>
                                <td><input type="text" id="relationship" name="relationship" value="<?= htmlspecialchars($employee['Relationship']) ?>" required></td>
                            </tr>
                        </table>
                        <button type="submit" class="btn"><i class="fas fa-save"></i> Save Changes</button>
                        <a href="?view=profile" class="btn" style="background-color: #666; margin-left: 10px;"><i class="fas fa-times"></i> Cancel</a>
                    </form>
                </div>
                <?php elseif ($showTasks && $tasks): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Assigned Tasks</span>
                        <div class="card-icon bg-accent"><i class="fas fa-tasks"></i></div>
                    </div>
                    <table class="tasks-table">
                        <tr>
                            <th>Title</th>
                            <th>Description</th>
                            <th>Assigned Date</th>
                            <th>Due Date</th>
                            <th>Status</th>
                        </tr>
                        <?php foreach ($tasks as $task): ?>
                        <tr>
                            <td><?= htmlspecialchars($task['task_title']) ?></td>
                            <td><?= htmlspecialchars($task['description']) ?></td>
                            <td><?= htmlspecialchars($task['assigned_date']) ?></td>
                            <td><?= htmlspecialchars($task['due_date']) ?></td>
                            <td><?= htmlspecialchars($task['status']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
                <?php elseif ($showUpdateTask && $tasks): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Update Task Status</span>
                        <div class="card-icon bg-accent"><i class="fas fa-check-square"></i></div>
                    </div>
                    <?php if ($errorMessage): ?>
                        <div class="message error"><?= htmlspecialchars($errorMessage) ?></div>
                    <?php endif; ?>
                    <?php if ($successMessage): ?>
                        <div class="message success"><?= htmlspecialchars($successMessage) ?></div>
                    <?php endif; ?>
                    <form action="" method="POST" class="form">
                        <input type="hidden" name="update_task" value="1">
                        <table class="update-form">
                            <tr>
                                <th><label for="task_id">Task</label></th>
                                <td>
                                    <select id="task_id" name="task_id" required>
                                        <option value="" disabled selected>Select Task</option>
                                        <?php foreach ($tasks as $task): ?>
                                        <option value="<?= $task['task_id'] ?>"><?= htmlspecialchars($task['task_title']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="status">Status</label></th>
                                <td>
                                    <select id="status" name="status" required>
                                        <option value="Pending" selected>Pending</option>
                                        <option value="In Progress">In Progress</option>
                                        <option value="Completed">Completed</option>
                                    </select>
                                </td>
                            </tr>
                        </table>
                        <button type="submit" class="btn"><i class="fas fa-save"></i> Update Status</button>
                    </form>
                </div>
                <?php elseif ($showAttendance): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Add Attendance</span>
                        <div class="card-icon bg-success"><i class="fas fa-calendar-check"></i></div>
                    </div>
                    <?php if ($errorMessage): ?>
                        <div class="message error"><?= htmlspecialchars($errorMessage) ?></div>
                    <?php endif; ?>
                    <?php if ($successMessage): ?>
                        <div class="message success"><?= htmlspecialchars($successMessage) ?></div>
                    <?php endif; ?>
                    <form action="" method="POST" class="form">
                        <input type="hidden" name="add_attendance" value="1">
                        <table class="update-form">
                            <tr>
                                <th><label for="date">Date</label></th>
                                <td><input type="date" id="date" name="date" value="<?= date('Y-m-d') ?>" required></td>
                            </tr>
                            <tr>
                                <th><label for="status">Status</label></th>
                                <td>
                                    <select id="status" name="status" required>
                                        <option value="Present" selected>Present</option>
                                        <option value="Absent">Absent</option>
                                        <option value="Late">Late</option>
                                    </select>
                                </td>
                            </tr>
                        </table>
                        <button type="submit" class="btn"><i class="fas fa-save"></i> Record Attendance</button>
                    </form>
                </div>
                <?php elseif ($showLeaveRequest): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Submit Leave Request</span>
                        <div class="card-icon bg-secondary"><i class="fas fa-paper-plane"></i></div>
                    </div>
                    <?php if ($errorMessage): ?>
                        <div class="message error"><?= htmlspecialchars($errorMessage) ?></div>
                    <?php endif; ?>
                    <?php if ($successMessage): ?>
                        <div class="message success"><?= htmlspecialchars($successMessage) ?></div>
                    <?php endif; ?>
                    <form action="" method="POST" class="form">
                        <input type="hidden" name="leave_request" value="1">
                        <table class="update-form">
                            <tr>
                                <th><label for="req_date">Requesting Date</label></th>
                                <td><input type="date" id="req_date" name="req_date" required></td>
                            </tr>
                            <tr>
                                <th><label for="st_date">Start Date</label></th>
                                <td><input type="date" id="st_date" name="st_date" required></td>
                            </tr>
                            <tr>
                                <th><label for="end_date">End Date</label></th>
                                <td><input type="date" id="end_date" name="end_date" required></td>
                            </tr>
                            <tr>
                                <th><label for="reason">Reason</label></th>
                                <td><textarea id="reason" name="reason" required></textarea></td>
                            </tr>
                        </table>
                        <button type="submit" class="btn"><i class="fas fa-save"></i> Submit Request</button>
                    </form>
                </div>
                <?php elseif ($showPerformanceReview): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Submit Performance Review</span>
                        <div class="card-icon bg-primary"><i class="fas fa-chart-line"></i></div>
                    </div>
                    <?php if ($errorMessage): ?>
                        <div class="message error"><?= htmlspecialchars($errorMessage) ?></div>
                    <?php endif; ?>
                    <?php if ($successMessage): ?>
                        <div class="message success"><?= htmlspecialchars($successMessage) ?></div>
                    <?php endif; ?>
                    <form action="" method="POST" class="form">
                        <input type="hidden" name="performance_review" value="1">
                        <table class="update-form">
                            <tr>
                                <th><label for="rating">Rating (1-5)</label></th>
                                <td><input type="number" id="rating" name="rating" min="1" max="5" required></td>
                            </tr>
                            <tr>
                                <th><label for="comments">Comments</label></th>
                                <td><textarea id="comments" name="comments" required></textarea></td>
                            </tr>
                        </table>
                        <button type="submit" class="btn"><i class="fas fa-save"></i> Submit Review</button>
                    </form>
                </div>
                <?php elseif ($showFeedback): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Provide Feedback</span>
                        <div class="card-icon bg-secondary"><i class="fas fa-comment-dots"></i></div>
                    </div>
                    <?php if ($errorMessage): ?>
                        <div class="message error"><?= htmlspecialchars($errorMessage) ?></div>
                    <?php endif; ?>
                    <?php if ($successMessage): ?>
                        <div class="message success"><?= htmlspecialchars($successMessage) ?></div>
                    <?php endif; ?>
                    <form action="" method="POST" class="form">
                        <input type="hidden" name="feedback" value="1">
                        <table class="update-form">
                            <tr>
                                <th><label for="feedback_text">Feedback</label></th>
                                <td><textarea id="feedback_text" name="feedback_text" required></textarea></td>
                            </tr>
                        </table>
                        <button type="submit" class="btn"><i class="fas fa-save"></i> Submit Feedback</button>
                    </form>
                </div>
                <?php elseif ($showSchedule && $schedules): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Work Schedule</span>
                        <div class="card-icon bg-primary"><i class="fas fa-calendar-alt"></i></div>
                    </div>
                    <table class="schedule-table">
                        <tr>
                            <th>Date</th>
                            <th>Shift Start</th>
                            <th>Shift End</th>
                        </tr>
                        <?php foreach ($schedules as $schedule): ?>
                        <tr>
                            <td><?= htmlspecialchars($schedule['date']) ?></td>
                            <td><?= htmlspecialchars($schedule['shift_start']) ?></td>
                            <td><?= htmlspecialchars($schedule['shift_end']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
                <?php else: ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>