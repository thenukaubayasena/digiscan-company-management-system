<?php
session_start();
require 'db_connection.php';

// // Check if user is logged in and is CEO
// if (!isset($_SESSION['EMP_ID']) || $_SESSION['designation'] !== 'CEO') {
//     header('Location: login.php');
//     exit;
// }

$displayName = isset($_SESSION['FName'], $_SESSION['LName']) ? $_SESSION['FName'] . ' ' . $_SESSION['LName'] : 'CEO';
$avatar = strtoupper(substr($displayName, 0, 1));

// Initialize message variables
$successMessage = '';
$errorMessage = '';

// Initialize view variables
$showReports = false;
$showPolicies = false;
$showTransactions = false;
$showDecisions = false;
$showPerformance = false;
$showGoals = false;

// Determine which view to show based on GET parameter
if (isset($_GET['view'])) {
    $showReports = ($_GET['view'] === 'reports');
    $showPolicies = ($_GET['view'] === 'add_policies');
    $showTransactions = ($_GET['view'] === 'approve_transactions');
    $showDecisions = ($_GET['view'] === 'approve_decisions');
    $showPerformance = ($_GET['view'] === 'performance_review');
    $showGoals = ($_GET['view'] === 'company_goals');
} else {
    // Default view: show summary cards
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Add Company Policy
        if (isset($_POST['add_policy'])) {
            $title = $_POST['title'];
            $description = $_POST['description'];
            if (empty($title) || empty($description)) {
                $errorMessage = "All fields are required.";
                $showPolicies = true;
            } else {
                $stmt = $pdo->prepare("INSERT INTO company_policies (title, description, created_at) VALUES (?, ?, NOW())");
                $success = $stmt->execute([$title, $description]);
                if ($success) {
                    $successMessage = "Policy added successfully!";
                    $showPolicies = true;
                } else {
                    $errorMessage = "Error adding policy.";
                    $showPolicies = true;
                }
            }
        }
        // Approve/Reject Transaction
        if (isset($_POST['approve_transaction']) || isset($_POST['reject_transaction'])) {
            $transaction_id = $_POST['transaction_id'];
            $status = isset($_POST['approve_transaction']) ? 'Approved' : 'Rejected';
            $stmt = $pdo->prepare("UPDATE transactions SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE transaction_id = ?");
            $success = $stmt->execute([$status, $transaction_id]);
            if ($success) {
                $successMessage = "Transaction $status successfully!";
                $showTransactions = true;
            } else {
                $errorMessage = "Error updating transaction.";
                $showTransactions = true;
            }
        }
        // Approve/Reject Decision
        if (isset($_POST['approve_decision']) || isset($_POST['reject_decision'])) {
            $decision_id = $_POST['decision_id'];
            $status = isset($_POST['approve_decision']) ? 'Approved' : 'Rejected';
            $stmt = $pdo->prepare("UPDATE decisions SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE decision_id = ?");
            $success = $stmt->execute([$status, $decision_id]);
            if ($success) {
                $successMessage = "Decision $status successfully!";
                $showDecisions = true;
            } else {
                $errorMessage = "Error updating decision.";
                $showDecisions = true;
            }
        }
        // Set Company Goal
        if (isset($_POST['set_goal'])) {
            $title = $_POST['title'];
            $description = $_POST['description'];
            $target_date = $_POST['target_date'];
            if (empty($title) || empty($description) || empty($target_date)) {
                $errorMessage = "All fields are required.";
                $showGoals = true;
            } elseif (strtotime($target_date) < time()) {
                $errorMessage = "Target date must be in the future.";
                $showGoals = true;
            } else {
                $stmt = $pdo->prepare("INSERT INTO company_goals (title, description, target_date, status) VALUES (?, ?, ?, 'Active')");
                $success = $stmt->execute([$title, $description, $target_date]);
                if ($success) {
                    $successMessage = "Goal set successfully!";
                    $showGoals = true;
                } else {
                    $errorMessage = "Error setting goal.";
                    $showGoals = true;
                }
            }
        }
    } catch (PDOException $e) {
        $errorMessage = "Database error: " . $e->getMessage();
        if (isset($_POST['add_policy'])) $showPolicies = true;
        elseif (isset($_POST['approve_transaction']) || isset($_POST['reject_transaction'])) $showTransactions = true;
        elseif (isset($_POST['approve_decision']) || isset($_POST['reject_decision'])) $showDecisions = true;
        elseif (isset($_POST['set_goal'])) $showGoals = true;
    }
}

// Fetch data for views
if ($showReports) {
    $stmt = $pdo->query("SELECT * FROM reports ORDER BY generated_at DESC");
    $reports = $stmt->fetchAll();
}
if ($showPolicies) {
    $stmt = $pdo->query("SELECT * FROM company_policies ORDER BY created_at DESC");
    $policies = $stmt->fetchAll();
}
if ($showTransactions) {
    // Get pending transactions
    $stmt = $pdo->query("SELECT * FROM transactions WHERE status = 'Pending' ORDER BY submitted_at DESC");
    $pendingTransactions = $stmt->fetchAll();
    
    // Get processed transactions (approved/rejected)
    $stmt = $pdo->query("SELECT * FROM transactions WHERE status IN ('Approved', 'Rejected') ORDER BY updated_at DESC");
    $processedTransactions = $stmt->fetchAll();
}
if ($showDecisions) {
    // Get pending decisions
    $stmt = $pdo->query("SELECT * FROM decisions WHERE status = 'Pending' ORDER BY submitted_at DESC");
    $pendingDecisions = $stmt->fetchAll();
    
    // Get processed decisions (approved/rejected)
    $stmt = $pdo->query("SELECT * FROM decisions WHERE status IN ('Approved', 'Rejected') ORDER BY updated_at DESC");
    $processedDecisions = $stmt->fetchAll();
}
if ($showPerformance) {
    $stmt = $pdo->query("SELECT * FROM performance_metrics ORDER BY recorded_at DESC");
    $metrics = $stmt->fetchAll();
}
if ($showGoals) {
    $stmt = $pdo->query("SELECT * FROM company_goals WHERE status = 'Active' ORDER BY target_date ASC");
    $goals = $stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CEO Dashboard | Digiscan</title>
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

        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8em;
            font-weight: bold;
        }

        .status-badge.approved {
            background-color: #d4edda;
            color: #155724;
        }

        .status-badge.rejected {
            background-color: #f8d7da;
            color: #721c24;
        }

        .status-badge.pending {
            background-color: #fff3cd;
            color: #856404;
        }

        .message.info {
            background-color: #d1ecf1;
            color: #0c5460;
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
        }

        .card-icon.bg-success {
            background-color: #28a745;
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
                <h3>CEO Dashboard</h3>
                <p><?= htmlspecialchars($displayName) ?></p>
            </div>
            <div class="sidebar-menu">
                <a href="?view=reports" class="menu-item <?= $showReports ? 'active' : '' ?>"><i class="fas fa-tachometer-alt"></i> View Reports</a>
                <a href="?view=add_policies" class="menu-item <?= $showPolicies ? 'active' : '' ?>"><i class="fas fa-users"></i> Add Company Policies</a>
                <a href="?view=approve_transactions" class="menu-item <?= $showTransactions ? 'active' : '' ?>"><i class="fas fa-cog"></i> Approve Major Transactions</a>
                <a href="?view=approve_decisions" class="menu-item <?= $showDecisions ? 'active' : '' ?>"><i class="fas fa-chart-bar"></i> Approve Decisions</a>
                <a href="?view=performance_review" class="menu-item <?= $showPerformance ? 'active' : '' ?>"><i class="fas fa-bell"></i> Review Company Performance</a>
                <a href="?view=company_goals" class="menu-item <?= $showGoals ? 'active' : '' ?>"><i class="fas fa-question-circle"></i> Set Company Goals</a>
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
                <?php if ($showReports && $reports): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Reports</span>
                        <div class="card-icon bg-primary"><i class="fas fa-tachometer-alt"></i></div>
                    </div>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Report Type</th>
                                <th>Generated At</th>
                                <th>File Path</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reports as $report): ?>
                            <tr>
                                <td><?= htmlspecialchars($report['report_type']) ?></td>
                                <td><?= htmlspecialchars($report['generated_at']) ?></td>
                                <td><a href="<?= htmlspecialchars($report['file_path']) ?>" class="btn">View</a></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php elseif ($showPolicies): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Add Company Policy</span>
                        <div class="card-icon bg-secondary"><i class="fas fa-users"></i></div>
                    </div>
                    <?php if ($errorMessage): ?>
                        <div class="message error"><?= htmlspecialchars($errorMessage) ?></div>
                    <?php endif; ?>
                    <?php if ($successMessage): ?>
                        <div class="message success"><?= htmlspecialchars($successMessage) ?></div>
                    <?php endif; ?>
                    <form action="" method="POST" class="form-table">
                        <input type="hidden" name="add_policy" value="1">
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
                            <th>Created At</th>
                        </tr>
                        <?php foreach ($policies as $policy): ?>
                        <tr>
                            <td><?= htmlspecialchars($policy['title']) ?></td>
                            <td><?= htmlspecialchars($policy['description']) ?></td>
                            <td><?= htmlspecialchars($policy['created_at']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                    <?php endif; ?>
                </div>
                <?php elseif ($showTransactions): ?>
                    <div class="card">
                        <div class="card-header">
                            <span class="card-title">Transaction Approval System</span>
                            <div class="card-icon bg-accent"><i class="fas fa-cog"></i></div>
                        </div>
                        <?php if ($errorMessage): ?>
                            <div class="message error"><?= htmlspecialchars($errorMessage) ?></div>
                        <?php endif; ?>
                        <?php if ($successMessage): ?>
                            <div class="message success"><?= htmlspecialchars($successMessage) ?></div>
                        <?php endif; ?>
                        
                        <!-- Pending Transactions Section -->
                        <?php if (!empty($pendingTransactions)): ?>
                            <h3>Pending Approvals</h3>
                            <table class="data-table">
                                <tr>
                                    <th>Description</th>
                                    <th>Amount</th>
                                    <th>Submitted At</th>
                                    <th>Action</th>
                                </tr>
                                <?php foreach ($pendingTransactions as $transaction): ?>
                                <tr>
                                    <td><?= htmlspecialchars($transaction['description']) ?></td>
                                    <td><?= htmlspecialchars($transaction['amount']) ?></td>
                                    <td><?= htmlspecialchars($transaction['submitted_at']) ?></td>
                                    <td>
                                        <form action="" method="POST" style="display: inline;">
                                            <input type="hidden" name="transaction_id" value="<?= $transaction['transaction_id'] ?>">
                                            <button type="submit" name="approve_transaction" class="action-btn approve-btn"><i class="fas fa-check"></i> Approve</button>
                                            <button type="submit" name="reject_transaction" class="action-btn reject-btn"><i class="fas fa-times"></i> Reject</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </table>
                        <?php else: ?>
                            <div class="message info">No pending transactions awaiting approval</div>
                        <?php endif; ?>
                        
                        <!-- Processed Transactions Section -->
                        <h3>Transaction History</h3>
                        <?php if (!empty($processedTransactions)): ?>
                            <table class="data-table">
                                <tr>
                                    <th>Description</th>
                                    <th>Amount</th>
                                    <th>Submitted At</th>
                                    <th>Status</th>
                                    <th>Updated At</th>
                                </tr>
                                <?php foreach ($processedTransactions as $transaction): ?>
                                <tr>
                                    <td><?= htmlspecialchars($transaction['description']) ?></td>
                                    <td><?= htmlspecialchars($transaction['amount']) ?></td>
                                    <td><?= htmlspecialchars($transaction['submitted_at']) ?></td>
                                    <td>
                                        <span class="status-badge <?= strtolower($transaction['status']) ?>">
                                            <?= htmlspecialchars($transaction['status']) ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($transaction['updated_at'] ?? 'N/A') ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </table>
                        <?php else: ?>
                            <div class="message info">No transaction history available</div>
                        <?php endif; ?>
                    </div>
                <?php elseif ($showDecisions): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Decision Approval System</span>
                        <div class="card-icon bg-success"><i class="fas fa-chart-bar"></i></div>
                    </div>
                    <?php if ($errorMessage): ?>
                        <div class="message error"><?= htmlspecialchars($errorMessage) ?></div>
                    <?php endif; ?>
                    <?php if ($successMessage): ?>
                        <div class="message success"><?= htmlspecialchars($successMessage) ?></div>
                    <?php endif; ?>
                    
                    <!-- Pending Decisions Section -->
                    <?php if (!empty($pendingDecisions)): ?>
                        <h3>Pending Approvals</h3>
                        <table class="data-table">
                            <tr>
                                <th>Title</th>
                                <th>Description</th>
                                <th>Submitted At</th>
                                <th>Action</th>
                            </tr>
                            <?php foreach ($pendingDecisions as $decision): ?>
                            <tr>
                                <td><?= htmlspecialchars($decision['title']) ?></td>
                                <td><?= htmlspecialchars($decision['description']) ?></td>
                                <td><?= htmlspecialchars($decision['submitted_at']) ?></td>
                                <td>
                                    <form action="" method="POST" style="display: inline;">
                                        <input type="hidden" name="decision_id" value="<?= $decision['decision_id'] ?>">
                                        <button type="submit" name="approve_decision" class="action-btn approve-btn"><i class="fas fa-check"></i> Approve</button>
                                        <button type="submit" name="reject_decision" class="action-btn reject-btn"><i class="fas fa-times"></i> Reject</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </table>
                    <?php else: ?>
                        <div class="message info">No pending decisions awaiting approval</div>
                    <?php endif; ?>
                    
                    <!-- Processed Decisions Section -->
                    <h3>Decision History</h3>
                    <?php if (!empty($processedDecisions)): ?>
                        <table class="data-table">
                            <tr>
                                <th>Title</th>
                                <th>Description</th>
                                <th>Submitted At</th>
                                <th>Status</th>
                                <th>Updated At</th>
                            </tr>
                            <?php foreach ($processedDecisions as $decision): ?>
                            <tr>
                                <td><?= htmlspecialchars($decision['title']) ?></td>
                                <td><?= htmlspecialchars($decision['description']) ?></td>
                                <td><?= htmlspecialchars($decision['submitted_at']) ?></td>
                                <td>
                                    <span class="status-badge <?= strtolower($decision['status']) ?>">
                                        <?= htmlspecialchars($decision['status']) ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($decision['updated_at'] ?? 'N/A') ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </table>
                    <?php else: ?>
                        <div class="message info">No decision history available</div>
                    <?php endif; ?>
                </div>
                <?php elseif ($showPerformance && $metrics): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Company Performance Review</span>
                        <div class="card-icon bg-primary"><i class="fas fa-bell"></i></div>
                    </div>
                    <table class="data-table">
                        <tr>
                            <th>Metric Name</th>
                            <th>Value</th>
                            <th>Recorded At</th>
                        </tr>
                        <?php foreach ($metrics as $metric): ?>
                        <tr>
                            <td><?= htmlspecialchars($metric['metric_name']) ?></td>
                            <td><?= htmlspecialchars($metric['value']) ?></td>
                            <td><?= htmlspecialchars($metric['recorded_at']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
                <?php elseif ($showGoals): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Set Company Goals</span>
                        <div class="card-icon bg-secondary"><i class="fas fa-question-circle"></i></div>
                    </div>
                    <?php if ($errorMessage): ?>
                        <div class="message error"><?= htmlspecialchars($errorMessage) ?></div>
                    <?php endif; ?>
                    <?php if ($successMessage): ?>
                        <div class="message success"><?= htmlspecialchars($successMessage) ?></div>
                    <?php endif; ?>
                    <form action="" method="POST" class="form-table">
                        <input type="hidden" name="set_goal" value="1">
                        <table>
                            <tr>
                                <th><label for="title">Goal Title</label></th>
                                <td><input type="text" id="title" name="title" required></td>
                            </tr>
                            <tr>
                                <th><label for="description">Description</label></th>
                                <td><textarea id="description" name="description" required></textarea></td>
                            </tr>
                            <tr>
                                <th><label for="target_date">Target Date</label></th>
                                <td><input type="date" id="target_date" name="target_date" required></td>
                            </tr>
                        </table>
                        <button type="submit" class="btn"><i class="fas fa-save"></i> Set Goal</button>
                    </form>
                    <?php if ($goals): ?>
                    <table class="data-table">
                        <tr>
                            <th>Title</th>
                            <th>Description</th>
                            <th>Target Date</th>
                            <th>Status</th>
                        </tr>
                        <?php foreach ($goals as $goal): ?>
                        <tr>
                            <td><?= htmlspecialchars($goal['title']) ?></td>
                            <td><?= htmlspecialchars($goal['description']) ?></td>
                            <td><?= htmlspecialchars($goal['target_date']) ?></td>
                            <td><?= htmlspecialchars($goal['status']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>