<?php
session_start();
require 'db_connection.php';

$displayName = isset($_SESSION['FName'], $_SESSION['LName']) ? $_SESSION['FName'] . ' ' . $_SESSION['LName'] : 'Graphic Division Manager';
$avatar = strtoupper(substr($displayName, 0, 1));

// Initialize message variables
$successMessage = '';
$errorMessage = '';

// Initialize view variables
$showViewProductDesigns = false;
$showApproveDesigns = false;
$showReviewFeedback = false;
$showDesignProgressReports = false;

// Determine which view to show based on GET parameter
if (isset($_GET['view'])) {
    $showViewProductDesigns = ($_GET['view'] === 'view_product_designs');
    $showApproveDesigns = ($_GET['view'] === 'approve_designs');
    $showReviewFeedback = ($_GET['view'] === 'review_feedback');
    $showDesignProgressReports = ($_GET['view'] === 'design_progress_reports');
} else {
    // Default view: show summary cards
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Approve/Reject Designs
        if (isset($_POST['approve_design']) || isset($_POST['reject_design'])) {
            $design_id = $_POST['design_id'];
            $status = isset($_POST['approve_design']) ? 'Approved' : 'Rejected';
            $stmt = $pdo->prepare("UPDATE product_designs SET status = ? WHERE design_id = ?");
            $success = $stmt->execute([$status, $design_id]);
            if ($success) {
                $successMessage = "Design $status successfully!";
                $showApproveDesigns = true;
            } else {
                $errorMessage = "Error updating design status.";
                $showApproveDesigns = true;
            }
        }
    } catch (PDOException $e) {
        $errorMessage = "Database error: " . $e->getMessage();
        $showApproveDesigns = true;
    }
}

// Fetch data for views
if ($showViewProductDesigns) {
    $stmt = $pdo->query("SELECT d.design_id, d.design_name, d.designer, d.status, d.created_at
                         FROM product_designs d 
                         ORDER BY d.created_at DESC");
    $designs = $stmt->fetchAll();
}
if ($showApproveDesigns) {
    $stmt = $pdo->query("SELECT d.design_id, d.design_name, d.designer, d.status, d.created_at
                         FROM product_designs d 
                         WHERE d.status = 'Pending' 
                         ORDER BY d.created_at DESC");
    $pending_designs = $stmt->fetchAll();
}
if ($showReviewFeedback) {
    $stmt = $pdo->query("SELECT f.feedback_id, f.design_id, f.rating, f.feedback, f.submitted_at, d.design_name 
                         FROM customer_feedback f 
                         JOIN product_designs d ON f.design_id = d.design_id 
                         ORDER BY f.submitted_at DESC");
    $feedback = $stmt->fetchAll();
}
if ($showDesignProgressReports) {
    $stmt = $pdo->query("SELECT * FROM design_reports ORDER BY generated_at DESC");
    $reports = $stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Graphic Division Manager Dashboard | Digiscan</title>
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
            grid-template-columns: repeat(auto-fit, minmax(550px, 1fr));
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
                <h3>Graphic Division Manager Dashboard</h3>
                <p><?php echo htmlspecialchars($displayName); ?></p>
            </div>
            <div class="sidebar-menu">
                <a href="?view=view_product_designs" class="menu-item <?php echo $showViewProductDesigns ? 'active' : ''; ?>"><i class="fas fa-drafting-compass"></i> View Product Designs</a>
                <a href="?view=approve_designs" class="menu-item <?php echo $showApproveDesigns ? 'active' : ''; ?>"><i class="fas fa-check-double"></i> Approve Designs for Production</a>
                <a href="?view=review_feedback" class="menu-item <?php echo $showReviewFeedback ? 'active' : ''; ?>"><i class="fas fa-comments"></i> Review Customer Feedback</a>
                <a href="?view=design_progress_reports" class="menu-item <?php echo $showDesignProgressReports ? 'active' : ''; ?>"><i class="fas fa-chart-pie"></i> Generate Design Reports</a>
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
                <?php if ($showViewProductDesigns): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">View Product Designs</span>
                        <div class="card-icon bg-primary"><i class="fas fa-drafting-compass"></i></div>
                    </div>
                    <?php if ($designs): ?>
                    <table class="data-table">
                        <tr>
                            <th>Design ID</th>
                            <th>Name</th>
                            <th>Designer</th>
                            <th>Status</th>
                            <th>Created At</th>
                        </tr>
                        <?php foreach ($designs as $design): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($design['design_id']); ?></td>
                            <td><?php echo htmlspecialchars($design['design_name']); ?></td>
                            <td><?php echo htmlspecialchars($design['designer']); ?></td>
                            <td><?php echo htmlspecialchars($design['status']); ?></td>
                            <td><?php echo htmlspecialchars($design['created_at']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                    <?php else: ?>
                    <p>No designs found.</p>
                    <?php endif; ?>
                </div>
                <?php elseif ($showApproveDesigns): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Approve Designs for Production</span>
                        <div class="card-icon bg-secondary"><i class="fas fa-check-double"></i></div>
                    </div>
                    <?php if ($errorMessage): ?>
                        <div class="message error-message"><?php echo htmlspecialchars($errorMessage); ?></div>
                    <?php endif; ?>
                    <?php if ($successMessage): ?>
                        <div class="message success"><?php echo htmlspecialchars($successMessage); ?></div>
                    <?php endif; ?>
                    <?php if ($pending_designs): ?>
                    <table class="data-table">
                        <tr>
                            <th>Design ID</th>
                            <th>Name</th>
                            <th>Designer</th>
                            <th>Status</th>
                            <th>Created At</th>
                            <th>Action</th>
                        </tr>
                        <?php foreach ($pending_designs as $design): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($design['design_id']); ?></td>
                            <td><?php echo htmlspecialchars($design['design_name']); ?></td>
                            <td><?php echo htmlspecialchars($design['designer']); ?></td>
                            <td><?php echo htmlspecialchars($design['status']); ?></td>
                            <td><?php echo htmlspecialchars($design['created_at']); ?></td>
                            <td>
                                <form action="" method="POST" style="display: inline;">
                                    <input type="hidden" name="design_id" value="<?php echo $design['design_id']; ?>">
                                    <button type="submit" name="approve_design" class="action-btn approve-btn"><i class="fas fa-check"></i> Approve</button>
                                    <button type="submit" name="reject_design" class="action-btn reject-btn"><i class="fas fa-times"></i> Reject</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                    <?php else: ?>
                    <p>No pending designs for approval.</p>
                    <?php endif; ?>
                </div>
                <?php elseif ($showReviewFeedback): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Review Customer Feedback</span>
                        <div class="card-icon bg-accent"><i class="fas fa-comments"></i></div>
                    </div>
                    <?php if ($feedback): ?>
                    <table class="data-table">
                        <tr>
                            <th>Feedback ID</th>
                            <th>Design ID</th>
                            <th>Design Name</th>
                            <th>Rating</th>
                            <th>Comments</th>
                            <th>Submitted At</th>
                        </tr>
                        <?php foreach ($feedback as $fb): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($fb['feedback_id']); ?></td>
                            <td><?php echo htmlspecialchars($fb['design_id']); ?></td>
                            <td><?php echo htmlspecialchars($fb['design_name']); ?></td>
                            <td><?php echo htmlspecialchars($fb['rating']); ?>/5</td>
                            <td><?php echo htmlspecialchars($fb['feedback']); ?></td>
                            <td><?php echo htmlspecialchars($fb['submitted_at']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                    <?php else: ?>
                    <p>No feedback found.</p>
                    <?php endif; ?>
                </div>
                <?php elseif ($showDesignProgressReports): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Generate Design Progress Reports</span>
                        <div class="card-icon bg-success"><i class="fas fa-chart-pie"></i></div>
                    </div>
                    <?php if ($reports): ?>
                    <table class="data-table">
                        <tr>
                            <th>Title</th>
                            <th>Generated On</th>
                            <th>File</th>
                        </tr>
                        <?php foreach ($reports as $report): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($report['title']); ?></td>
                            <td><?php echo htmlspecialchars($report['generated_at']); ?></td>
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
                        <span class="card-title">Total Designs</span>
                        <div class="card-icon bg-primary"><i class="fas fa-drafting-compass"></i></div>
                    </div>
                    <div class="card-value"><?php
                        $stmt = $pdo->query("SELECT COUNT(*) FROM product_designs");
                        echo $stmt->fetchColumn();
                    ?></div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Pending Approvals</span>
                        <div class="card-icon bg-secondary"><i class="fas fa-check-double"></i></div>
                    </div>
                    <div class="card-value"><?php
                        $stmt = $pdo->query("SELECT COUNT(*) FROM product_designs WHERE status = 'Pending'");
                        echo $stmt->fetchColumn();
                    ?></div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Recent Feedback</span>
                        <div class="card-icon bg-accent"><i class="fas fa-comments"></i></div>
                    </div>
                    <div class="card-value"><?php
                        $stmt = $pdo->query("SELECT COUNT(*) FROM customer_feedback WHERE submitted_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
                        echo $stmt->fetchColumn();
                    ?></div>
                </div>
                <?php endif; ?>
            </div>

            <div class="recent-activity">
                <h3 class="section-title">Recent Activity</h3>
                <div class="activity-item">
                    <div class="activity-icon"><i class="fas fa-check-double"></i></div>
                    <div class="activity-content">
                        <div class="activity-title">Design approved for production</div>
                        <div class="activity-time">2 hours ago</div>
                    </div>
                </div>
                <div class="activity-item">
                    <div class="activity-icon"><i class="fas fa-comments"></i></div>
                    <div class="activity-content">
                        <div class="activity-title">New customer feedback received</div>
                        <div class="activity-time">Yesterday</div>
                    </div>
                </div>
                <div class="activity-item">
                    <div class="activity-icon"><i class="fas fa-chart-pie"></i></div>
                    <div class="activity-content">
                        <div class="activity-title">Design progress report generated</div>
                        <div class="activity-time">Yesterday</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>