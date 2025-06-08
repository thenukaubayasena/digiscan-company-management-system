<?php
session_start();
require 'db_connection.php';

$displayName = isset($_SESSION['FName'], $_SESSION['LName']) ? $_SESSION['FName'] . ' ' . $_SESSION['LName'] : 'Production Manager';
$avatar = strtoupper(substr($displayName, 0, 1));

// Initialize message variables
$successMessage = '';
$errorMessage = '';

// Initialize view variables
$showCreateProductionSchedules = false;
$showCheckEquipment = false;
$showViewOrders = false;
$showGenerateProductionReports = false;
$showApproveWorkflow = false;
$showManageProductionCosts = false;
$showApproveProductQuality = false;
$showManageProductionOrders = false;

// Determine which view to show based on GET parameter
if (isset($_GET['view'])) {
    $showCreateProductionSchedules = ($_GET['view'] === 'create_production_schedules');
    $showCheckEquipment = ($_GET['view'] === 'check_equipment');
    $showViewOrders = ($_GET['view'] === 'view_orders');
    $showGenerateProductionReports = ($_GET['view'] === 'generate_production_reports');
    $showApproveWorkflow = ($_GET['view'] === 'approve_workflow');
    $showManageProductionCosts = ($_GET['view'] === 'manage_production_costs');
    $showApproveProductQuality = ($_GET['view'] === 'approve_product_quality');
    $showManageProductionOrders = ($_GET['view'] === 'manage_production_orders');
} else {
    // Default view: show summary cards
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Create Production Schedules
        if (isset($_POST['create_production_schedule'])) {
            $product_name = $_POST['product_name'];
            $quantity = $_POST['quantity'];
            $start_date = $_POST['start_date'];
            $end_date = $_POST['end_date'];
            if (empty($product_name) || empty($quantity) || empty($start_date) || empty($end_date)) {
                $errorMessage = "All fields are required.";
                $showCreateProductionSchedules = true;
            } elseif (!is_numeric($quantity) || $quantity <= 0) {
                $errorMessage = "Quantity must be a positive number.";
                $showCreateProductionSchedules = true;
            } elseif (strtotime($end_date) < strtotime($start_date)) {
                $errorMessage = "End date must be after start date.";
                $showCreateProductionSchedules = true;
            } else {
                $stmt = $pdo->prepare("INSERT INTO production_schedules (product_name, quantity, start_date, end_date, created_at) VALUES (?, ?, ?, ?, NOW())");
                $success = $stmt->execute([$product_name, $quantity, $start_date, $end_date]);
                if ($success) {
                    $successMessage = "Schedule created successfully!";
                    $showCreateProductionSchedules = true;
                } else {
                    $errorMessage = "Error creating schedule.";
                    $showCreateProductionSchedules = true;
                }
            }
        }
        // Check Equipment
        if (isset($_POST['check_equipment'])) {
            $equipment_name = $_POST['equipment_name'];
            $status = $_POST['status'];
            if (empty($equipment_name) || empty($status)) {
                $errorMessage = "All fields are required.";
                $showCheckEquipment = true;
            } else {
                $stmt = $pdo->prepare("INSERT INTO equipment_logs (equipment_name, status, checked_at) VALUES (?, ?, NOW())");
                $success = $stmt->execute([$equipment_name, $status]);
                if ($success) {
                    $successMessage = "Equipment check logged successfully!";
                    $showCheckEquipment = true;
                } else {
                    $errorMessage = "Error logging equipment check.";
                    $showCheckEquipment = true;
                }
            }
        }
        // Approve/Reject Workflow
        if (isset($_POST['approve_workflow']) || isset($_POST['reject_workflow'])) {
            $workflow_id = $_POST['workflow_id'];
            $status = isset($_POST['approve_workflow']) ? 'Approved' : 'Rejected';
            $stmt = $pdo->prepare("UPDATE production_workflow SET status = ? WHERE workflow_id = ?");
            $success = $stmt->execute([$status, $workflow_id]);
            if ($success) {
                $successMessage = "Workflow $status successfully!";
                $showApproveWorkflow = true;
            } else {
                $errorMessage = "Error updating workflow.";
                $showApproveWorkflow = true;
            }
        }
        // Manage Production Costs
        if (isset($_POST['manage_production_costs'])) {
            $order_id = $_POST['order_id'];
            $cost_type = $_POST['cost_type'];
            $amount = $_POST['amount'];
            if (empty($order_id) || empty($cost_type) || empty($amount)) {
                $errorMessage = "All fields are required.";
                $showManageProductionCosts = true;
            } elseif (!is_numeric($amount) || $amount <= 0) {
                $errorMessage = "Amount must be a positive number.";
                $showManageProductionCosts = true;
            } else {
                $stmt = $pdo->prepare("INSERT INTO production_costs (order_id, cost_type, amount, logged_at) VALUES (?, ?, ?, NOW())");
                $success = $stmt->execute([$order_id, $cost_type, $amount]);
                if ($success) {
                    $successMessage = "Cost logged successfully!";
                    $showManageProductionCosts = true;
                } else {
                    $errorMessage = "Error logging cost.";
                    $showManageProductionCosts = true;
                }
            }
        }
        // Approve/Reject Product Quality
        if (isset($_POST['approve_quality']) || isset($_POST['reject_quality'])) {
            $inspection_id = $_POST['inspection_id'];
            $status = isset($_POST['approve_quality']) ? 'Approved' : 'Rejected';
            $stmt = $pdo->prepare("UPDATE quality_inspections SET status = ? WHERE inspection_id = ?");
            $success = $stmt->execute([$status, $inspection_id]);
            if ($success) {
                $successMessage = "Quality inspection $status successfully!";
                $showApproveProductQuality = true;
            } else {
                $errorMessage = "Error updating quality inspection.";
                $showApproveProductQuality = true;
            }
        }
        // Manage Production Orders
        if (isset($_POST['manage_production_orders'])) {
            $order_id = $_POST['order_id'];
            $status = $_POST['status'];
            if (empty($order_id) || empty($status)) {
                $errorMessage = "All fields are required.";
                $showManageProductionOrders = true;
            } else {
                $stmt = $pdo->prepare("UPDATE production_orders SET status = ? WHERE order_id = ?");
                $success = $stmt->execute([$status, $order_id]);
                if ($success) {
                    $successMessage = "Order status updated successfully!";
                    $showManageProductionOrders = true;
                } else {
                    $errorMessage = "Error updating order status.";
                    $showManageProductionOrders = true;
                }
            }
        }
    } catch (PDOException $e) {
        $errorMessage = "Database error: " . $e->getMessage();
        if (isset($_POST['create_production_schedule'])) $showCreateProductionSchedules = true;
        elseif (isset($_POST['check_equipment'])) $showCheckEquipment = true;
        elseif (isset($_POST['approve_workflow']) || isset($_POST['reject_workflow'])) $showApproveWorkflow = true;
        elseif (isset($_POST['manage_production_costs'])) $showManageProductionCosts = true;
        elseif (isset($_POST['approve_quality']) || isset($_POST['reject_quality'])) $showApproveProductQuality = true;
        elseif (isset($_POST['manage_production_orders'])) $showManageProductionOrders = true;
    }
}

// Fetch data for views
if ($showCreateProductionSchedules) {
    $stmt = $pdo->query("SELECT * FROM production_schedules ORDER BY created_at DESC");
    $schedules = $stmt->fetchAll();
}
if ($showCheckEquipment) {
    $stmt = $pdo->query("SELECT * FROM equipment_logs ORDER BY checked_at DESC");
    $equipment_logs = $stmt->fetchAll();
}
if ($showViewOrders) {
    $stmt = $pdo->query("SELECT * FROM production_orders ORDER BY created_at DESC");
    $orders = $stmt->fetchAll();
}
if ($showGenerateProductionReports) {
    $stmt = $pdo->query("SELECT * FROM production_reports ORDER BY generated_at DESC");
    $reports = $stmt->fetchAll();
}
if ($showApproveWorkflow) {
    $stmt = $pdo->query("SELECT w.workflow_id, w.order_id, w.step_name, w.status, w.submitted_at, o.product_name 
                         FROM production_workflow w 
                         JOIN production_orders o ON w.order_id = o.order_id 
                         WHERE w.status = 'Pending' 
                         ORDER BY w.submitted_at DESC");
    $workflows = $stmt->fetchAll();
}
if ($showManageProductionCosts) {
    $stmt = $pdo->query("SELECT c.cost_id, c.order_id, c.cost_type, c.amount, c.logged_at, o.product_name 
                         FROM production_costs c 
                         JOIN production_orders o ON c.order_id = o.order_id 
                         ORDER BY c.logged_at DESC");
    $costs = $stmt->fetchAll();
    $stmt = $pdo->query("SELECT order_id, product_name FROM production_orders");
    $orders = $stmt->fetchAll();
}
if ($showApproveProductQuality) {
    $stmt = $pdo->query("SELECT q.inspection_id, q.order_id, q.product_name, q.status, q.inspected_at, o.product_name AS order_product 
                         FROM quality_inspections q 
                         JOIN production_orders o ON q.order_id = o.order_id 
                         WHERE q.status = 'Pending' 
                         ORDER BY q.inspected_at DESC");
    $inspections = $stmt->fetchAll();
}
if ($showManageProductionOrders) {
    $stmt = $pdo->query("SELECT * FROM production_orders ORDER BY created_at DESC");
    $orders = $stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Production Manager Dashboard | Digiscan</title>
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
                <h3>Production Manager Dashboard</h3>
                <p><?= htmlspecialchars($displayName) ?></p>
            </div>
            <div class="sidebar-menu">
                <a href="?view=create_production_schedules" class="menu-item <?= $showCreateProductionSchedules ? 'active' : '' ?>"><i class="fas fa-calendar-alt"></i> Create Production Schedules</a>
                <a href="?view=check_equipment" class="menu-item <?= $showCheckEquipment ? 'active' : '' ?>"><i class="fas fa-tools"></i> Check Equipment</a>
                <a href="?view=view_orders" class="menu-item <?= $showViewOrders ? 'active' : '' ?>"><i class="fas fa-clipboard-list"></i> View Orders</a>
                <a href="?view=generate_production_reports" class="menu-item <?= $showGenerateProductionReports ? 'active' : '' ?>"><i class="fas fa-file-alt"></i> Generate Production Reports</a>
                <a href="?view=approve_workflow" class="menu-item <?= $showApproveWorkflow ? 'active' : '' ?>"><i class="fas fa-check-circle"></i> Approve Production Workflow</a>
                <a href="?view=manage_production_costs" class="menu-item <?= $showManageProductionCosts ? 'active' : '' ?>"><i class="fas fa-dollar-sign"></i> Manage Production Costs</a>
                <a href="?view=approve_product_quality" class="menu-item <?= $showApproveProductQuality ? 'active' : '' ?>"><i class="fas fa-thumbs-up"></i> Approve Product Quality</a>
                <a href="?view=manage_production_orders" class="menu-item <?= $showManageProductionOrders ? 'active' : '' ?>"><i class="fas fa-boxes"></i> Manage Production Orders</a>
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
                <?php if ($showCreateProductionSchedules): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Create Production Schedules</span>
                        <div class="card-icon bg-primary"><i class="fas fa-calendar-alt"></i></div>
                    </div>
                    <?php if ($errorMessage): ?>
                        <div class="message error"><?= htmlspecialchars($errorMessage) ?></div>
                    <?php endif; ?>
                    <?php if ($successMessage): ?>
                        <div class="message success"><?= htmlspecialchars($successMessage) ?></div>
                    <?php endif; ?>
                    <form action="" method="POST" class="form-table">
                        <input type="hidden" name="create_production_schedule" value="1">
                        <table>
                            <tr>
                                <th><label for="product_name">Product Name</label></th>
                                <td><input type="text" id="product_name" name="product_name" required></td>
                            </tr>
                            <tr>
                                <th><label for="quantity">Quantity</label></th>
                                <td><input type="number" id="quantity" name="quantity" required></td>
                            </tr>
                            <tr>
                                <th><label for="start_date">Start Date</label></th>
                                <td><input type="date" id="start_date" name="start_date" required></td>
                            </tr>
                            <tr>
                                <th><label for="end_date">End Date</label></th>
                                <td><input type="date" id="end_date" name="end_date" required></td>
                            </tr>
                        </table>
                        <button type="submit" class="btn"><i class="fas fa-save"></i> Create Schedule</button>
                    </form>
                    <?php if ($schedules): ?>
                    <table class="data-table">
                        <tr>
                            <th>Product Name</th>
                            <th>Quantity</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Created At</th>
                        </tr>
                        <?php foreach ($schedules as $schedule): ?>
                        <tr>
                            <td><?= htmlspecialchars($schedule['product_name']) ?></td>
                            <td><?= htmlspecialchars($schedule['quantity']) ?></td>
                            <td><?= htmlspecialchars($schedule['start_date']) ?></td>
                            <td><?= htmlspecialchars($schedule['end_date']) ?></td>
                            <td><?= htmlspecialchars($schedule['created_at']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                    <?php endif; ?>
                </div>
                <?php elseif ($showCheckEquipment): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Check Equipment</span>
                        <div class="card-icon bg-secondary"><i class="fas fa-tools"></i></div>
                    </div>
                    <?php if ($errorMessage): ?>
                        <div class="message error"><?= htmlspecialchars($errorMessage) ?></div>
                    <?php endif; ?>
                    <?php if ($successMessage): ?>
                        <div class="message success"><?= htmlspecialchars($successMessage) ?></div>
                    <?php endif; ?>
                    <form action="" method="POST" class="form-table">
                        <input type="hidden" name="check_equipment" value="1">
                        <table>
                            <tr>
                                <th><label for="equipment_name">Equipment Name</label></th>
                                <td><input type="text" id="equipment_name" name="equipment_name" required></td>
                            </tr>
                            <tr>
                                <th><label for="status">Status</label></th>
                                <td>
                                    <select id="status" name="status" required>
                                        <option value="Operational">Operational</option>
                                        <option value="Maintenance">Maintenance</option>
                                        <option value="Out of Service">Out of Service</option>
                                    </select>
                                </td>
                            </tr>
                        </table>
                        <button type="submit" class="btn"><i class="fas fa-save"></i> Log Check</button>
                    </form>
                    <?php if ($equipment_logs): ?>
                    <table class="data-table">
                        <tr>
                            <th>Equipment Name</th>
                            <th>Status</th>
                            <th>Checked At</th>
                        </tr>
                        <?php foreach ($equipment_logs as $log): ?>
                        <tr>
                            <td><?= htmlspecialchars($log['equipment_name']) ?></td>
                            <td><?= htmlspecialchars($log['status']) ?></td>
                            <td><?= htmlspecialchars($log['checked_at']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                    <?php endif; ?>
                </div>
                <?php elseif ($showViewOrders): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">View Orders</span>
                        <div class="card-icon bg-accent"><i class="fas fa-clipboard-list"></i></div>
                    </div>
                    <?php if ($orders): ?>
                    <table class="data-table">
                        <tr>
                            <th>Order ID</th>
                            <th>Product Name</th>
                            <th>Quantity</th>
                            <th>Due Date</th>
                            <th>Status</th>
                            <th>Created At</th>
                        </tr>
                        <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?= htmlspecialchars($order['order_id']) ?></td>
                            <td><?= htmlspecialchars($order['product_name']) ?></td>
                            <td><?= htmlspecialchars($order['quantity']) ?></td>
                            <td><?= htmlspecialchars($order['due_date']) ?></td>
                            <td><?= htmlspecialchars($order['status']) ?></td>
                            <td><?= htmlspecialchars($order['created_at']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                    <?php endif; ?>
                </div>
                <?php elseif ($showGenerateProductionReports): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Generate Production Reports</span>
                        <div class="card-icon bg-success"><i class="fas fa-file-alt"></i></div>
                    </div>
                    <?php if ($reports): ?>
                    <table class="data-table">
                        <tr>
                            <th>Title</th>
                            <th>Generated At</th>
                            <th>File</th>
                        </tr>
                        <?php foreach ($reports as $report): ?>
                        <tr>
                            <td><?= htmlspecialchars($report['title']) ?></td>
                            <td><?= htmlspecialchars($report['generated_at']) ?></td>
                            <td><a href="<?= htmlspecialchars($report['file_path']) ?>" class="btn">View</a></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                    <?php endif; ?>
                </div>
                <?php elseif ($showApproveWorkflow): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Approve Production Workflow</span>
                        <div class="card-icon bg-primary"><i class="fas fa-check-circle"></i></div>
                    </div>
                    <?php if ($errorMessage): ?>
                        <div class="message error"><?= htmlspecialchars($errorMessage) ?></div>
                    <?php endif; ?>
                    <?php if ($successMessage): ?>
                        <div class="message success"><?= htmlspecialchars($successMessage) ?></div>
                    <?php endif; ?>
                    <?php if ($workflows): ?>
                    <table class="data-table">
                        <tr>
                            <th>Order ID</th>
                            <th>Product Name</th>
                            <th>Step Name</th>
                            <th>Status</th>
                            <th>Submitted At</th>
                            <th>Action</th>
                        </tr>
                        <?php foreach ($workflows as $workflow): ?>
                        <tr>
                            <td><?= htmlspecialchars($workflow['order_id']) ?></td>
                            <td><?= htmlspecialchars($workflow['product_name']) ?></td>
                            <td><?= htmlspecialchars($workflow['step_name']) ?></td>
                            <td><?= htmlspecialchars($workflow['status']) ?></td>
                            <td><?= htmlspecialchars($workflow['submitted_at']) ?></td>
                            <td>
                                <form action="" method="POST" style="display: inline;">
                                    <input type="hidden" name="workflow_id" value="<?= $workflow['workflow_id'] ?>">
                                    <button type="submit" name="approve_workflow" class="action-btn approve-btn"><i class="fas fa-check"></i> Approve</button>
                                    <button type="submit" name="reject_workflow" class="action-btn reject-btn"><i class="fas fa-times"></i> Reject</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                    <?php endif; ?>
                </div>
                <?php elseif ($showManageProductionCosts): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Manage Production Costs</span>
                        <div class="card-icon bg-secondary"><i class="fas fa-dollar-sign"></i></div>
                    </div>
                    <?php if ($errorMessage): ?>
                        <div class="message error"><?= htmlspecialchars($errorMessage) ?></div>
                    <?php endif; ?>
                    <?php if ($successMessage): ?>
                        <div class="message success"><?= htmlspecialchars($successMessage) ?></div>
                    <?php endif; ?>
                    <form action="" method="POST" class="form-table">
                        <input type="hidden" name="manage_production_costs" value="1">
                        <table>
                            <tr>
                                <th><label for="order_id">Order</label></th>
                                <td>
                                    <select id="order_id" name="order_id" required>
                                        <?php foreach ($orders as $order): ?>
                                        <option value="<?= $order['order_id'] ?>"><?= htmlspecialchars($order['product_name']) ?> (ID: <?= $order['order_id'] ?>)</option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="cost_type">Cost Type</label></th>
                                <td>
                                    <select id="cost_type" name="cost_type" required>
                                        <option value="Material">Material</option>
                                        <option value="Labor">Labor</option>
                                        <option value="Overhead">Overhead</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="amount">Amount</label></th>
                                <td><input type="number" id="amount" name="amount" step="0.01" required></td>
                            </tr>
                        </table>
                        <button type="submit" class="btn"><i class="fas fa-save"></i> Log Cost</button>
                    </form>
                    <?php if ($costs): ?>
                    <table class="data-table">
                        <tr>
                            <th>Order ID</th>
                            <th>Product Name</th>
                            <th>Cost Type</th>
                            <th>Amount</th>
                            <th>Logged At</th>
                        </tr>
                        <?php foreach ($costs as $cost): ?>
                        <tr>
                            <td><?= htmlspecialchars($cost['order_id']) ?></td>
                            <td><?= htmlspecialchars($cost['product_name']) ?></td>
                            <td><?= htmlspecialchars($cost['cost_type']) ?></td>
                            <td>$<?= number_format($cost['amount'], 2) ?></td>
                            <td><?= htmlspecialchars($cost['logged_at']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                    <?php endif; ?>
                </div>
                <?php elseif ($showApproveProductQuality): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Approve Product Quality</span>
                        <div class="card-icon bg-success"><i class="fas fa-thumbs-up"></i></div>
                    </div>
                    <?php if ($errorMessage): ?>
                        <div class="message error"><?= htmlspecialchars($errorMessage) ?></div>
                    <?php endif; ?>
                    <?php if ($successMessage): ?>
                        <div class="message success"><?= htmlspecialchars($successMessage) ?></div>
                    <?php endif; ?>
                    <?php if ($inspections): ?>
                    <table class="data-table">
                        <tr>
                            <th>Order ID</th>
                            <th>Product Name</th>
                            <th>Status</th>
                            <th>Inspected At</th>
                            <th>Action</th>
                        </tr>
                        <?php foreach ($inspections as $inspection): ?>
                        <tr>
                            <td><?= htmlspecialchars($inspection['order_id']) ?></td>
                            <td><?= htmlspecialchars($inspection['product_name']) ?></td>
                            <td><?= htmlspecialchars($inspection['status']) ?></td>
                            <td><?= htmlspecialchars($inspection['inspected_at']) ?></td>
                            <td>
                                <form action="" method="POST" style="display: inline;">
                                    <input type="hidden" name="inspection_id" value="<?= $inspection['inspection_id'] ?>">
                                    <button type="submit" name="approve_quality" class="action-btn approve-btn"><i class="fas fa fa-check"></i> Approve</button>
                                    <button type="submit" name="reject_quality" class="action-btn" reject-btn><i class="fas fa fa-times"></i> Reject</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                    <?php endif; ?>
                </div>
                <?php elseif ($showManageProductionOrders): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Manage Production Orders</span>
                        <div class="card-icon bg-primary"><i class="fas fa-boxes"></i></div>
                    </div>
                    <?php if ($errorMessage): ?>
                        <div class="message error"><?php echo htmlspecialchars($errorMessage); ?></div>
                    <?php endif; ?>
                    <?php if ($successMessage): ?>
                        <div class="message success"><?php echo htmlspecialchars($successMessage); ?></div>
                    <?php endif; ?>
                    <form action="" method="POST" class="form-table">
                        <input type="hidden" name="manage_production_orders" value="1">
                        <table>
                            <tr>
                                <th><label for="order_id">Order:</label></th>
                                <td>
                                    <select id="order_id" name="order_id" required>
                                        <?php foreach ($orders as $order): ?>
                                        <option value="<?= $order['order_id'] ?>"><?= htmlspecialchars($order['product_name']) ?> (ID: <?= $order['order_id'] ?>)</option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="status">Status:</label></th>
                                <td>
                                    <select id="status" name="status" required>
                                        <option value="Pending">Pending</option>
                                        <option value="In Progress">In Progress</option>
                                        <option value="Completed">Completed</option>
                                    </select>
                                </td>
                            </tr>
                        </table>
                        <button type="submit" class="btn"><i class="fas fa fa-save"></i> Update Order</button>
                    </form>
                    <?php if ($orders): ?>
                    <table class="data-table">
                        <tr>
                            <th>Order ID</th>
                            <th>Product Name</th>
                            <th>Quantity</th>
                            <th>Due Date</th>
                            <th>Status</th>
                            <th>Created At</th>
                        </tr>
                        <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?= htmlspecialchars($order['order_id']) ?></td>
                            <td><?= htmlspecialchars($order['product_name']) ?></td>
                            <td><?= htmlspecialchars($order['quantity']) ?></td>
                            <td><?= htmlspecialchars($order['due_date']) ?></td>
                            <td><?= htmlspecialchars($order['status']) ?></td>
                            <td><?= htmlspecialchars($order['created_at']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                    <?php endif; ?>
                </div>
                <?php else: ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Total Orders</span>
                        <div class="card-icon bg-primary"><i class="fas fa-boxes"></i></div>
                    </div>
                    <div class="card-value"><?php
                        $stmt = $pdo->query("SELECT COUNT(*) FROM production_orders");
                        echo $stmt->fetchColumn();
                    ?></div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Operational Equipment</span>
                        <div class="card-icon bg-accent"><i class="fas fa-tools"></i></div>
                    </div>
                    <div class="card-value"><?php
                        $stmt = $pdo->query("SELECT COUNT(*) FROM equipment_logs WHERE status = 'Operational'");
                        echo $stmt->fetchColumn();
                    ?></div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Pending Quality Inspections</span>
                        <div class="card-icon bg-secondary"><i class="fas fa-thumbs-up"></i></div>
                    </div>
                    <div class="card-value"><?php
                        $stmt = $pdo->query("SELECT COUNT(*) FROM quality_inspections WHERE status = 'Pending'");
                        echo $stmt->fetchColumn();
                    ?></div>
                </div>
                <?php endif; ?>
            </div>

            <div class="recent-activity">
                <h3 class="section-title">Recent Activity</h3>
                <div class="activity-item">
                    <div class="activity-icon"><i class="fas fa-check-circle"></i></div>
                    <div class="activity-content">
                        <div class="activity-title">Workflow approved</div>
                        <div class="activity-time">2 hours ago</div>
                    </div>
                </div>
                <div class="activity-item">
                    <div class="activity-icon"><i class="fas fa-thumbs-up"></i></div>
                    <div class="activity-content">
                        <div class="activity-title">Quality inspection approved</div>
                        <div class="activity-time">Yesterday</div>
                    </div>
                </div>
                <div class="activity-item">
                    <div class="activity-icon"><i class="fas fa-calendar-alt"></i></div>
                    <div class="activity-content">
                        <div class="activity-title">New production schedule created</div>
                        <div class="activity-time">Yesterday</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>