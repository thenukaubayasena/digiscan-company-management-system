<?php
session_start();
require 'db_connection.php';

$displayName = isset($_SESSION['FName'], $_SESSION['LName']) ? $_SESSION['FName'] . ' ' . $_SESSION['LName'] : 'Sales & Marketing Manager';
$avatar = strtoupper(substr($displayName, 0, 1));

// Initialize message variables
$successMessage = '';
$errorMessage = '';

// Initialize view variables
$showGenerateReports = false;
$showManageBudgets = false;
$showAddProductPrices = false;
$showUpdateDiscounts = false;
$showAddPromotions = false;
$showApproveProposals = false;

// Determine which view to show based on GET parameter
if (isset($_GET['view'])) {
    $showGenerateReports = ($_GET['view'] === 'generate_reports');
    $showManageBudgets = ($_GET['view'] === 'manage_budgets');
    $showAddProductPrices = ($_GET['view'] === 'add_product_price');
    $showUpdateDiscounts = ($_GET['view'] === 'update_discounts');
    $showAddPromotions = ($_GET['view'] === 'add_promotions');
    $showApproveProposals = ($_GET['view'] === 'approve_proposals');
} else {
    // Default view: show summary cards
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Manage Budgets
        if (isset($_POST['manage_budget'])) {
            $budget_id = $_POST['budget_id'];
            $allocated_amount = $_POST['allocated_amount'];
            $fiscal_year = $_POST['fiscal_year'];
            if (empty($allocated_amount) || empty($fiscal_year)) {
                $errorMessage = "All fields are required.";
                $showManageBudgets = true;
            } elseif (!is_numeric($allocated_amount) || $allocated_amount <= 0) {
                $errorMessage = "Allocated amount must be a positive number.";
                $showManageBudgets = true;
            } else {
                $stmt = $pdo->prepare("UPDATE marketing_budgets SET allocated_amount = ?, fiscal_year = ? WHERE budget_id = ?");
                $success = $stmt->execute([$allocated_amount, $fiscal_year, $budget_id]);
                if ($success) {
                    $successMessage = "Budget updated successfully!";
                    $showManageBudgets = true;
                } else {
                    $errorMessage = "Error updating budget.";
                    $showManageBudgets = true;
                }
            }
        }
        // Add Product Price
        if (isset($_POST['add_product_price'])) {
            $product_name = $_POST['product_name'];
            $price = $_POST['price'];
            if (empty($product_name) || empty($price)) {
                $errorMessage = "All fields are required.";
                $showAddProductPrices = true;
            } elseif (!is_numeric($price) || $price <= 0) {
                $errorMessage = "Price must be a positive number.";
                $showAddProductPrices = true;
            } else {
                $stmt = $pdo->prepare("INSERT INTO products (product_name, price, updated_at) VALUES (?, ?, NOW()) ON DUPLICATE KEY UPDATE price = ?, updated_at = NOW()");
                $success = $stmt->execute([$product_name, $price, $price]);
                if ($success) {
                    $successMessage = "Product price added/updated successfully!";
                    $showAddProductPrices = true;
                } else {
                    $errorMessage = "Error adding/updating product price.";
                    $showAddProductPrices = true;
                }
            }
        }
        // Update Discount
        if (isset($_POST['update_discount'])) {
            $product_id = $_POST['product_id'];
            $discount_percentage = $_POST['discount_percentage'];
            $start_date = $_POST['start_date'];
            $end_date = $_POST['end_date'];
            if (empty($product_id) || empty($discount_percentage) || empty($start_date) || empty($end_date)) {
                $errorMessage = "All fields are required.";
                $showUpdateDiscounts = true;
            } elseif (!is_numeric($discount_percentage) || $discount_percentage < 0 || $discount_percentage > 100) {
                $errorMessage = "Discount percentage must be between 0 and 100.";
                $showUpdateDiscounts = true;
            } elseif (strtotime($end_date) < strtotime($start_date)) {
                $errorMessage = "End date cannot be before start date.";
                $showUpdateDiscounts = true;
            } else {
                $stmt = $pdo->prepare("INSERT INTO discounts (product_id, discount_percentage, start_date, end_date) VALUES (?, ?, ?, ?)");
                $success = $stmt->execute([$product_id, $discount_percentage, $start_date, $end_date]);
                if ($success) {
                    $successMessage = "Discount added successfully!";
                    $showUpdateDiscounts = true;
                } else {
                    $errorMessage = "Error adding discount.";
                    $showUpdateDiscounts = true;
                }
            }
        }
        // Add Promotion
        if (isset($_POST['add_promotion'])) {
            $title = $_POST['title'];
            $description = $_POST['description'];
            $start_date = $_POST['start_date'];
            $end_date = $_POST['end_date'];
            if (empty($title) || empty($description) || empty($start_date) || empty($end_date)) {
                $errorMessage = "All fields are required.";
                $showAddPromotions = true;
            } elseif (strtotime($end_date) < strtotime($start_date)) {
                $errorMessage = "End date cannot be before start date.";
                $showAddPromotions = true;
            } else {
                $stmt = $pdo->prepare("INSERT INTO promotions (title, description, start_date, end_date) VALUES (?, ?, ?, ?)");
                $success = $stmt->execute([$title, $description, $start_date, $end_date]);
                if ($success) {
                    $successMessage = "Promotion added successfully!";
                    $showAddPromotions = true;
                } else {
                    $errorMessage = "Error adding promotion.";
                    $showAddPromotions = true;
                }
            }
        }
        // Approve/Reject Sales Proposal
        if (isset($_POST['approve_proposal']) || isset($_POST['reject_proposal'])) {
            $proposal_id = $_POST['proposal_id'];
            $status = isset($_POST['approve_proposal']) ? 'Approved' : 'Rejected';
            $stmt = $pdo->prepare("UPDATE sales_proposals SET status = ? WHERE proposal_id = ?");
            $success = $stmt->execute([$status, $proposal_id]);
            if ($success) {
                $successMessage = "Proposal $status successfully!";
                $showApproveProposals = true;
            } else {
                $errorMessage = "Error updating proposal.";
                $showApproveProposals = true;
            }
        }
    } catch (PDOException $e) {
        $errorMessage = "Database error: " . $e->getMessage();
        if (isset($_POST['manage_budget'])) $showManageBudgets = true;
        elseif (isset($_POST['add_product_price'])) $showAddProductPrices = true;
        elseif (isset($_POST['update_discount'])) $showUpdateDiscounts = true;
        elseif (isset($_POST['add_promotion'])) $showAddPromotions = true;
        elseif (isset($_POST['approve_proposal']) || isset($_POST['reject_proposal'])) $showApproveProposals = true;
    }
}

// Fetch data for views
if ($showGenerateReports) {
    $stmt = $pdo->query("SELECT * FROM sales_reports ORDER BY generated_at DESC");
    $reports = $stmt->fetchAll();
}
if ($showManageBudgets) {
    $stmt = $pdo->query("SELECT * FROM marketing_budgets WHERE department = 'Marketing' ORDER BY fiscal_year DESC");
    $budgets = $stmt->fetchAll();
}
if ($showAddProductPrices) {
    $stmt = $pdo->query("SELECT * FROM products ORDER BY updated_at DESC");
    $products = $stmt->fetchAll();
}
if ($showUpdateDiscounts) {
    $stmt = $pdo->query("SELECT d.discount_id, d.product_id, d.discount_percentage, d.start_date, d.end_date, p.product_name 
                         FROM discounts d 
                         JOIN products p ON d.product_id = p.product_id 
                         ORDER BY d.start_date DESC");
    $discounts = $stmt->fetchAll();
    $stmt = $pdo->query("SELECT product_id, product_name FROM products");
    $product_list = $stmt->fetchAll();
}
if ($showAddPromotions) {
    $stmt = $pdo->query("SELECT * FROM promotions ORDER BY start_date DESC");
    $promotions = $stmt->fetchAll();
}
if ($showApproveProposals) {
    $stmt = $pdo->query("SELECT * FROM sales_proposals WHERE status = 'Pending' ORDER BY submitted_at DESC");
    $proposals = $stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales & Marketing Manager Dashboard | Digiscan</title>
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
                <h3>Sales & Marketing Manager Dashboard</h3>
                <p><?= htmlspecialchars($displayName) ?></p>
            </div>
            <div class="sidebar-menu">
                <a href="?view=generate_reports" class="menu-item <?= $showGenerateReports ? 'active' : '' ?>"><i class="fas fa-file-alt"></i> Generate Reports</a>
                <a href="?view=manage_budgets" class="menu-item <?= $showManageBudgets ? 'active' : '' ?>"><i class="fas fa-piggy-bank"></i> Manage Budgets</a>
                <a href="?view=add_product_price" class="menu-item <?= $showAddProductPrices ? 'active' : '' ?>"><i class="fas fa-tags"></i> Add Product Prices</a>
                <a href="?view=update_discounts" class="menu-item <?= $showUpdateDiscounts ? 'active' : '' ?>"><i class="fas fa-percentage"></i> Update Discount Prices</a>
                <a href="?view=add_promotions" class="menu-item <?= $showAddPromotions ? 'active' : '' ?>"><i class="fas fa-bullhorn"></i> Add Promotions</a>
                <a href="?view=approve_proposals" class="menu-item <?= $showApproveProposals ? 'active' : '' ?>"><i class="fas fa-thumbs-up"></i> Approve Sales Proposals</a>
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
                <?php if ($showGenerateReports && $reports): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Sales & Marketing Reports</span>
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
                <?php elseif ($showManageBudgets && $budgets): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Manage Marketing Budgets</span>
                        <div class="card-icon bg-secondary"><i class="fas fa-piggy-bank"></i></div>
                    </div>
                    <?php if ($errorMessage): ?>
                        <div class="message error"><?= htmlspecialchars($errorMessage) ?></div>
                    <?php endif; ?>
                    <?php if ($successMessage): ?>
                        <div class="message success"><?= htmlspecialchars($successMessage) ?></div>
                    <?php endif; ?>
                    <table class="data-table">
                        <tr>
                            <th>Department</th>
                            <th>Allocated Amount</th>
                            <th>Spent Amount</th>
                            <th>Fiscal Year</th>
                            <th>Action</th>
                        </tr>
                        <?php foreach ($budgets as $budget): ?>
                        <tr>
                            <td><?= htmlspecialchars($budget['department']) ?></td>
                            <td>$<?= number_format($budget['allocated_amount'], 2) ?></td>
                            <td>$<?= number_format($budget['spent_amount'], 2) ?></td>
                            <td><?= htmlspecialchars($budget['fiscal_year']) ?></td>
                            <td>
                                <form action="" method="POST" class="form-table">
                                    <input type="hidden" name="manage_budget" value="1">
                                    <input type="hidden" name="budget_id" value="<?= $budget['budget_id'] ?>">
                                    <table>
                                        <tr>
                                            <td><input type="number" name="allocated_amount" value="<?= htmlspecialchars($budget['allocated_amount']) ?>" step="0.01" required></td>
                                            <td><input type="text" name="fiscal_year" value="<?= htmlspecialchars($budget['fiscal_year']) ?>" required></td>
                                            <td><button type="submit" class="action-btn approve-btn"><i class="fas fa-save"></i> Update</button></td>
                                        </tr>
                                    </table>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
                <?php elseif ($showAddProductPrices): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Add Product Prices</span>
                        <div class="card-icon bg-accent"><i class="fas fa-tags"></i></div>
                    </div>
                    <?php if ($errorMessage): ?>
                        <div class="message error"><?= htmlspecialchars($errorMessage) ?></div>
                    <?php endif; ?>
                    <?php if ($successMessage): ?>
                        <div class="message success"><?= htmlspecialchars($successMessage) ?></div>
                    <?php endif; ?>
                    <form action="" method="POST" class="form-table">
                        <input type="hidden" name="add_product_price" value="1">
                        <table>
                            <tr>
                                <th><label for="product_name">Product Name</label></th>
                                <td><input type="text" id="product_name" name="product_name" required></td>
                            </tr>
                            <tr>
                                <th><label for="price">Price</label></th>
                                <td><input type="number" id="price" name="price" step="0.01" required></td>
                            </tr>
                        </table>
                        <button type="submit" class="btn"><i class="fas fa-save"></i> Add Price</button>
                    </form>
                    <?php if ($products): ?>
                    <table class="data-table">
                        <tr>
                            <th>Product Name</th>
                            <th>Price</th>
                            <th>Updated At</th>
                        </tr>
                        <?php foreach ($products as $product): ?>
                        <tr>
                            <td><?= htmlspecialchars($product['product_name']) ?></td>
                            <td>$<?= number_format($product['price'], 2) ?></td>
                            <td><?= htmlspecialchars($product['updated_at']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                    <?php endif; ?>
                </div>
                <?php elseif ($showUpdateDiscounts): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Update Discount Prices</span>
                        <div class="card-icon bg-success"><i class="fas fa-percentage"></i></div>
                    </div>
                    <?php if ($errorMessage): ?>
                        <div class="message error"><?= htmlspecialchars($errorMessage) ?></div>
                    <?php endif; ?>
                    <?php if ($successMessage): ?>
                        <div class="message success"><?= htmlspecialchars($successMessage) ?></div>
                    <?php endif; ?>
                    <form action="" method="POST" class="form-table">
                        <input type="hidden" name="update_discount" value="1">
                        <table>
                            <tr>
                                <th><label for="product_id">Product</label></th>
                                <td>
                                    <select id="product_id" name="product_id" required>
                                        <?php foreach ($product_list as $product): ?>
                                        <option value="<?= $product['product_id'] ?>"><?= htmlspecialchars($product['product_name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="discount_percentage">Discount (%)</label></th>
                                <td><input type="number" id="discount_percentage" name="discount_percentage" step="0.01" required></td>
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
                        <button type="submit" class="btn"><i class="fas fa-save"></i> Add Discount</button>
                    </form>
                    <?php if ($discounts): ?>
                    <table class="data-table">
                        <tr>
                            <th>Product Name</th>
                            <th>Discount (%)</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                        </tr>
                        <?php foreach ($discounts as $discount): ?>
                        <tr>
                            <td><?= htmlspecialchars($discount['product_name']) ?></td>
                            <td><?= number_format($discount['discount_percentage'], 2) ?>%</td>
                            <td><?= htmlspecialchars($discount['start_date']) ?></td>
                            <td><?= htmlspecialchars($discount['end_date']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                    <?php endif; ?>
                </div>
                <?php elseif ($showAddPromotions): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Add Promotions</span>
                        <div class="card-icon bg-primary"><i class="fas fa-bullhorn"></i></div>
                    </div>
                    <?php if ($errorMessage): ?>
                        <div class="message error"><?= htmlspecialchars($errorMessage) ?></div>
                    <?php endif; ?>
                    <?php if ($successMessage): ?>
                        <div class="message success"><?= htmlspecialchars($successMessage) ?></div>
                    <?php endif; ?>
                    <form action="" method="POST" class="form-table">
                        <input type="hidden" name="add_promotion" value="1">
                        <table>
                            <tr>
                                <th><label for="title">Title</label></th>
                                <td><input type="text" id="title" name="title" required></td>
                            </tr>
                            <tr>
                                <th><label for="description">Description</label></th>
                                <td><textarea id="description" name="description" required></textarea></td>
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
                        <button type="submit" class="btn"><i class="fas fa-save"></i> Add Promotion</button>
                    </form>
                    <?php if ($promotions): ?>
                    <table class="data-table">
                        <tr>
                            <th>Title</th>
                            <th>Description</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                        </tr>
                        <?php foreach ($promotions as $promotion): ?>
                        <tr>
                            <td><?= htmlspecialchars($promotion['title']) ?></td>
                            <td><?= htmlspecialchars($promotion['description']) ?></td>
                            <td><?= htmlspecialchars($promotion['start_date']) ?></td>
                            <td><?= htmlspecialchars($promotion['end_date']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                    <?php endif; ?>
                </div>
                <?php elseif ($showApproveProposals && $proposals): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Approve Sales Proposals</span>
                        <div class="card-icon bg-secondary"><i class="fas fa-thumbs-up"></i></div>
                    </div>
                    <?php if ($errorMessage): ?>
                        <div class="message error"><?= htmlspecialchars($errorMessage) ?></div>
                    <?php endif; ?>
                    <?php if ($successMessage): ?>
                        <div class="message success"><?= htmlspecialchars($successMessage) ?></div>
                    <?php endif; ?>
                    <table class="data-table">
                        <tr>
                            <th>Client Name</th>
                            <th>Amount</th>
                            <th>Submitted At</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                        <?php foreach ($proposals as $proposal): ?>
                        <tr>
                            <td><?= htmlspecialchars($proposal['client_name']) ?></td>
                            <td>$<?= number_format($proposal['amount'], 2) ?></td>
                            <td><?= htmlspecialchars($proposal['submitted_at']) ?></td>
                            <td><?= htmlspecialchars($proposal['status']) ?></td>
                            <td>
                                <form action="" method="POST" style="display: inline;">
                                    <input type="hidden" name="proposal_id" value="<?= $proposal['proposal_id'] ?>">
                                    <button type="submit" name="approve_proposal" class="action-btn approve-btn"><i class="fas fa-check"></i> Approve</button>
                                    <button type="submit" name="reject_proposal" class="action-btn reject-btn"><i class="fas fa-times"></i> Reject</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
                <?php else: ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Total Reports</span>
                        <div class="card-icon bg-primary"><i class="fas fa-file-alt"></i></div>
                    </div>
                    <div class="card-value"><?php
                        $stmt = $pdo->query("SELECT COUNT(*) FROM sales_reports");
                        echo $stmt->fetchColumn();
                    ?></div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Active Promotions</span>
                        <div class="card-icon bg-success"><i class="fas fa-bullhorn"></i></div>
                    </div>
                    <div class="card-value"><?php
                        $stmt = $pdo->query("SELECT COUNT(*) FROM promotions WHERE end_date >= CURDATE()");
                        echo $stmt->fetchColumn();
                    ?></div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Pending Proposals</span>
                        <div class="card-icon bg-accent"><i class="fas fa-thumbs-up"></i></div>
                    </div>
                    <div class="card-value"><?php
                        $stmt = $pdo->query("SELECT COUNT(*) FROM sales_proposals WHERE status = 'Pending'");
                        echo $stmt->fetchColumn();
                    ?></div>
                </div>
                <?php endif; ?>
            </div>

            <div class="recent-activity">
                <h3 class="section-title">Recent Activity</h3>
                <div class="activity-item">
                    <div class="activity-icon"><i class="fas fa-thumbs-up"></i></div>
                    <div class="activity-content">
                        <div class="activity-title">Sales proposal approved</div>
                        <div class="activity-time">2 hours ago</div>
                    </div>
                </div>
                <div class="activity-item">
                    <div class="activity-icon"><i class="fas fa-bullhorn"></i></div>
                    <div class="activity-content">
                        <div class="activity-title">New promotion added</div>
                        <div class="activity-time">Yesterday</div>
                    </div>
                </div>
                <div class="activity-item">
                    <div class="activity-icon"><i class="fas fa-percentage"></i></div>
                    <div class="activity-content">
                        <div class="activity-title">Discount updated</div>
                        <div class="activity-time">Yesterday</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>