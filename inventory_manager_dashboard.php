<?php
session_start();
require 'db_connection.php';

$displayName = isset($_SESSION['FName'], $_SESSION['LName']) ? $_SESSION['FName'] . ' ' . $_SESSION['LName'] : 'Inventory Manager';
$avatar = strtoupper(substr($displayName, 0, 1));

// Initialize message variables
$successMessage = '';
$errorMessage = '';
$searchResults = [];

// Initialize view variables
$showSearchInventory = false;
$showCheckInventory = false;
$showAddInventory = false;
$showUpdateInventory = false;
$showAddSupplierOrder = false;
$showRequestMaterialReturns = false;
$showInventoryReports = false;

// Determine which view to show based on GET parameter
if (isset($_GET['view'])) {
    $showSearchInventory = ($_GET['view'] === 'search_inventory');
    $showCheckInventory = ($_GET['view'] === 'check_inventory');
    $showAddInventory = ($_GET['view'] === 'add_inventory');
    $showUpdateInventory = ($_GET['view'] === 'update_inventory');
    $showAddSupplierOrder = ($_GET['view'] === 'add_supplier_order');
    $showRequestMaterialReturns = ($_GET['view'] === 'request_material_returns');
    $showInventoryReports = ($_GET['view'] === 'inventory_reports');
} else {
    // Default view: show summary cards
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Search Inventory
        if (isset($_POST['search_inventory'])) {
            $search_term = trim($_POST['search_term']);
            if (empty($search_term)) {
                $errorMessage = "Search term is required.";
                $showSearchInventory = true;
            } else {
                $stmt = $pdo->prepare("SELECT * FROM inventory WHERE item_name LIKE ? OR item_id = ?");
                $stmt->execute(["%$search_term%", is_numeric($search_term) ? $search_term : 0]);
                $searchResults = $stmt->fetchAll();
                $showSearchInventory = true;
            }
        }
        // Add Inventory
        if (isset($_POST['add_inventory'])) {
            $item_name = $_POST['item_name'];
            $quantity = $_POST['quantity'];
            $unit_price = $_POST['unit_price'];
            $location = $_POST['location'];
            if (empty($item_name) || empty($quantity) || empty($unit_price) || empty($location)) {
                $errorMessage = "All fields are required.";
                $showAddInventory = true;
            } elseif (!is_numeric($quantity) || $quantity < 0) {
                $errorMessage = "Quantity must be a non-negative number.";
                $showAddInventory = true;
            } elseif (!is_numeric($unit_price) || $unit_price < 0) {
                $errorMessage = "Unit price must be a non-negative number.";
                $showAddInventory = true;
            } else {
                $stmt = $pdo->prepare("INSERT INTO inventory (item_name, quantity, unit_price, location, last_updated) VALUES (?, ?, ?, ?, NOW())");
                $success = $stmt->execute([$item_name, $quantity, $unit_price, $location]);
                if ($success) {
                    $successMessage = "Inventory item added successfully!";
                    $showAddInventory = true;
                } else {
                    $errorMessage = "Error adding inventory item.";
                    $showAddInventory = true;
                }
            }
        }
        // Update Inventory
        if (isset($_POST['update_inventory'])) {
            $item_id = $_POST['item_id'];
            $quantity = $_POST['quantity'];
            $unit_price = $_POST['unit_price'];
            $location = $_POST['location'];
            if (empty($item_id) || empty($quantity) || empty($unit_price) || empty($location)) {
                $errorMessage = "All fields are required.";
                $showUpdateInventory = true;
            } elseif (!is_numeric($quantity) || $quantity < 0) {
                $errorMessage = "Quantity must be a non-negative number.";
                $showUpdateInventory = true;
            } elseif (!is_numeric($unit_price) || $unit_price < 0) {
                $errorMessage = "Unit price must be a non-negative number.";
                $showUpdateInventory = true;
            } else {
                $stmt = $pdo->prepare("UPDATE inventory SET quantity = ?, unit_price = ?, location = ?, last_updated = NOW() WHERE item_id = ?");
                $success = $stmt->execute([$quantity, $unit_price, $location, $item_id]);
                if ($success) {
                    $successMessage = "Inventory item updated successfully!";
                    $showUpdateInventory = true;
                } else {
                    $errorMessage = "Error updating inventory item.";
                    $showUpdateInventory = true;
                }
            }
        }
        // Add Supplier Order
        if (isset($_POST['add_supplier_order'])) {
            $supplier_name = $_POST['supplier_name'];
            $item_id = $_POST['item_id'];
            $quantity = $_POST['quantity'];
            $order_date = $_POST['order_date'];
            if (empty($supplier_name) || empty($item_id) || empty($quantity) || empty($order_date)) {
                $errorMessage = "All fields are required.";
                $showAddSupplierOrder = true;
            } elseif (!is_numeric($quantity) || $quantity < 1) {
                $errorMessage = "Quantity must be a positive number.";
                $showAddSupplierOrder = true;
            } else {
                $stmt = $pdo->prepare("INSERT INTO supplier_orders (supplier_name, item_id, quantity, order_date, status, created_at) VALUES (?, ?, ?, ?, 'Pending', NOW())");
                $success = $stmt->execute([$supplier_name, $item_id, $quantity, $order_date]);
                if ($success) {
                    $successMessage = "Supplier order added successfully!";
                    $showAddSupplierOrder = true;
                } else {
                    $errorMessage = "Error adding supplier order.";
                    $showAddSupplierOrder = true;
                }
            }
        }
        // Request Material Returns
        if (isset($_POST['request_material'])) {
            $item_id = $_POSTRAC3item_id;
            $quantity = $_POST['quantity'];
            $reason = $_POST['reason'];
            if (empty($item_id) || empty($quantity) || empty($reason)) {
                $errorMessage = "All fields are required.";
                $showRequestMaterialReturns = true;
            } elseif (!is_numeric($quantity) || $quantity < 1) {
                $errorMessage = "Quantity must be a positive number.";
                $showRequestMaterialReturns = true;
            } else {
                $stmt = $pdo->prepare("INSERT INTO material_returns (item_id, quantity, reason, status, requested_at) VALUES (?, ?, ?, 'Pending', NOW())");
                $success = $stmt->execute([$item_id, $quantity, $reason]);
                if ($success) {
                    $successMessage = "Material return requested successfully!";
                    $showRequestMaterialReturns = true;
                } else {
                    $errorMessage = "Error requesting material return.";
                    $showRequestMaterialReturns = true;
                }
            }
        }
    } catch (PDOException $e) {
        $errorMessage = "Database error: " . $e->getMessage();
        if (isset($_POST['search_inventory'])) $showSearchInventory = true;
        elseif (isset($_POST['add_inventory'])) $showAddInventory = true;
        elseif (isset($_POST['update_inventory'])) $showUpdateInventory = true;
        elseif (isset($_POST['add_supplier_order'])) $showAddSupplierOrder = true;
        elseif (isset($_POST['request_material'])) $showRequestMaterialReturns = true;
    }
}

// Fetch data for views
if ($showCheckInventory) {
    $stmt = $pdo->query("SELECT * FROM inventory ORDER BY quantity ASC");
    $inventory = $stmt->fetchAll();
}
if ($showUpdateInventory || $showAddSupplierOrder || $showRequestMaterialReturns) {
    $stmt = $pdo->query("SELECT item_id, item_name FROM inventory");
    $inventory_items = $stmt->fetchAll();
}
if ($showInventoryReports) {
    $stmt = $pdo->query("SELECT * FROM inventory_reports ORDER BY id DESC");
    $reports = $stmt->fetchAll();
}
if ($showAddSupplierOrder) {
    $stmt = $pdo->query("SELECT order_id, supplier_name, i.item_name, s.quantity, s.order_date, s.status, s.created_at 
                         FROM supplier_orders s 
                         JOIN inventory i ON s.item_id = i.item_id 
                         ORDER BY s.created_at DESC");
    $supplier_orders = $stmt->fetchAll();
}
if ($showRequestMaterialReturns) {
    $stmt = $pdo->query("SELECT r.id, r.item_id, i.item_name, r.quantity, r.reason, r.status, r.request_timestamp 
                         FROM material_returns r 
                         JOIN inventory i ON r.item_id = i.item_id 
                         ORDER BY r.request_timestamp DESC");
    $returns = $stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Manager Dashboard | Digiscan</title>
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
                <h3>Inventory Manager Dashboard</h3>
                <p><?= htmlspecialchars($displayName) ?></p>
            </div>
            <div class="sidebar-menu">
                <a href="?view=search_inventory" class="menu-item <?= $showSearchInventory ? 'active' : '' ?>"><i class="fas fa-search"></i> Search Inventory</a>
                <a href="?view=check_inventory" class="menu-item <?= $showCheckInventory ? 'active' : '' ?>"><i class="fas fa-warehouse"></i> Check Inventory</a>
                <a href="?view=add_inventory" class="menu-item <?= $showAddInventory ? 'active' : '' ?>"><i class="fas fa-plus-square"></i> Add Inventory</a>
                <a href="?view=update_inventory" class="menu-item <?= $showUpdateInventory ? 'active' : '' ?>"><i class="fas fa-edit"></i> Update Inventory</a>
                <a href="?view=add_supplier_order" class="menu-item <?= $showAddSupplierOrder ? 'active' : '' ?>"><i class="fas fa-truck-loading"></i> Add Supplier Order</a>
                <a href="?view=request_material_returns" class="menu-item <?= $showRequestMaterialReturns ? 'active' : '' ?>"><i class="fas fa-undo-alt"></i> Request Material Returns</a>
                <a href="?view=inventory_reports" class="menu-item <?= $showInventoryReports ? 'active' : '' ?>"><i class="fas fa-file-alt"></i> Generate Inventory Reports</a>
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
                <?php if ($showSearchInventory): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Search Inventory</span>
                        <div class="card-icon bg-primary"><i class="fas fa-search"></i></div>
                    </div>
                    <?php if ($errorMessage): ?>
                        <div class="message error"><?= htmlspecialchars($errorMessage) ?></div>
                    <?php endif; ?>
                    <form action="" method="POST" class="form-table">
                        <input type="hidden" name="search_inventory" value="1">
                        <table>
                            <tr>
                                <th><label for="search_term">Search Term (Name or ID)</label></th>
                                <td><input type="text" id="search_term" name="search_term" required></td>
                            </tr>
                        </table>
                        <button type="submit" class="btn"><i class="fas fa-search"></i> Search</button>
                    </form>
                    <?php if ($searchResults): ?>
                    <table class="data-table">
                        <tr>
                            <th>Item ID</th>
                            <th>Name</th>
                            <th>Quantity</th>
                            <th>Unit Price</th>
                            <th>Location</th>
                            <th>Last Updated</th>
                        </tr>
                        <?php foreach ($searchResults as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['item_id']) ?></td>
                            <td><?= htmlspecialchars($item['item_name']) ?></td>
                            <td><?= htmlspecialchars($item['quantity']) ?></td>
                            <td>$<?= number_format($item['unit_price'], 2) ?></td>
                            <td><?= htmlspecialchars($item['location']) ?></td>
                            <td><?= htmlspecialchars($item['last_updated']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                    <?php elseif (isset($_POST['search_inventory'])): ?>
                    <p>No items found.</p>
                    <?php endif; ?>
                </div>
                <?php elseif ($showCheckInventory): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Check Inventory</span>
                        <div class="card-icon bg-secondary"><i class="fas fa-warehouse"></i></div>
                    </div>
                    <?php if ($inventory): ?>
                    <table class="data-table">
                        <tr>
                            <th>Item ID</th>
                            <th>Name</th>
                            <th>Quantity</th>
                            <th>Unit Price</th>
                            <th>Location</th>
                            <th>Last Updated</th>
                        </tr>
                        <?php foreach ($inventory as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['item_id']) ?></td>
                            <td><?= htmlspecialchars($item['item_name']) ?></td>
                            <td><?= htmlspecialchars($item['quantity']) ?></td>
                            <td>$<?= number_format($item['unit_price'], 2) ?></td>
                            <td><?= htmlspecialchars($item['location']) ?></td>
                            <td><?= htmlspecialchars($item['last_updated']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                    <?php else: ?>
                    <p>No inventory items found.</p>
                    <?php endif; ?>
                </div>
                <?php elseif ($showAddInventory): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Add Inventory</span>
                        <div class="card-icon bg-success"><i class="fas fa-plus-square"></i></div>
                    </div>
                    <?php if ($errorMessage): ?>
                        <div class="message error"><?= htmlspecialchars($errorMessage) ?></div>
                    <?php endif; ?>
                    <?php if ($successMessage): ?>
                        <div class="message success"><?= htmlspecialchars($successMessage) ?></div>
                    <?php endif; ?>
                    <form action="" method="POST" class="form-table">
                        <input type="hidden" name="add_inventory" value="1">
                        <table>
                            <tr>
                                <th><label for="item_name">Item Name</label></th>
                                <td><input type="text" id="item_name" name="item_name" required></td>
                            </tr>
                            <tr>
                                <th><label for="quantity">Quantity</label></th>
                                <td><input type="number" id="quantity" name="quantity" min="0" required></td>
                            </tr>
                            <tr>
                                <th><label for="unit_price">Unit Price</label></th>
                                <td><input type="number" id="unit_price" name="unit_price" step="0.01" min="0" required></td>
                            </tr>
                            <tr>
                                <th><label for="location">Location</label></th>
                                <td><input type="text" id="location" name="location" required></td>
                            </tr>
                        </table>
                        <button type="submit" class="btn"><i class="fas fa-save"></i> Add Item</button>
                    </form>
                </div>
                <?php elseif ($showUpdateInventory): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Update Inventory</span>
                        <div class="card-icon bg-accent"><i class="fas fa-edit"></i></div>
                    </div>
                    <?php if ($errorMessage): ?>
                        <div class="message error"><?= htmlspecialchars($errorMessage) ?></div>
                    <?php endif; ?>
                    <?php if ($successMessage): ?>
                        <div class="message success"><?= htmlspecialchars($successMessage) ?></div>
                    <?php endif; ?>
                    <form action="" method="POST" class="form-table">
                        <input type="hidden" name="update_inventory" value="1">
                        <table>
                            <tr>
                                <th><label for="item_id">Item</label></th>
                                <td>
                                    <select id="item_id" name="item_id" required>
                                        <option value="">Select Item</option>
                                        <?php foreach ($inventory_items as $item): ?>
                                        <option value="<?= $item['item_id'] ?>"><?= htmlspecialchars($item['item_name']) ?> (ID: <?= $item['item_id'] ?>)</option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="quantity">Quantity</label></th>
                                <td><input type="number" id="quantity" name="quantity" min="0" required></td>
                            </tr>
                            <tr>
                                <th><label for="unit_price">Unit Price</label></th>
                                <td><input type="number" id="unit_price" name="unit_price" step="0.01" min="0" required></td>
                            </tr>
                            <tr>
                                <th><label for="location">Location</label></th>
                                <td><input type="text" id="location" name="location" required></td>
                            </tr>
                        </table>
                        <button type="submit" class="btn"><i class="fas fa-save"></i> Update Item</button>
                    </form>
                </div>
                <?php elseif ($showAddSupplierOrder): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Add Supplier Order</span>
                        <div class="card-icon bg-primary"><i class="fas fa-truck-loading"></i></div>
                    </div>
                    <?php if ($errorMessage): ?>
                        <div class="message error"><?= htmlspecialchars($errorMessage) ?></div>
                    <?php endif; ?>
                    <?php if ($successMessage): ?>
                        <div class="message success"><?= htmlspecialchars($successMessage) ?></div>
                    <?php endif; ?>
                    <form action="" method="POST" class="form-table">
                        <input type="hidden" name="add_supplier_order" value="1">
                        <table>
                            <tr>
                                <th><label for="supplier_name">Supplier Name</label></th>
                                <td><input type="text" id="supplier_name" name="supplier_name" required></td>
                            </tr>
                            <tr>
                                <th><label for="item_id">Item</label></th>
                                <td>
                                    <select id="item_id" name="item_id" required>
                                        <option value="">Select Item</option>
                                        <?php foreach ($inventory_items as $item): ?>
                                        <option value="<?= $item['item_id'] ?>"><?= htmlspecialchars($item['item_name']) ?> (ID: <?= $item['item_id'] ?>)</option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="quantity">Quantity</label></th>
                                <td><input type="number" id="quantity" name="quantity" min="1" required></td>
                            </tr>
                            <tr>
                                <th><label for="order_date">Order Date</label></th>
                                <td><input type="date" id="order_date" name="order_date" required></td>
                            </tr>
                        </table>
                        <button type="submit" class="btn"><i class="fas fa-save"></i> Add Order</button>
                    </form>
                    <?php if ($supplier_orders): ?>
                    <table class="data-table">
                        <tr>
                            <th>Order ID</th>
                            <th>Supplier Name</th>
                            <th>Item Name</th>
                            <th>Quantity</th>
                            <th>Order Date</th>
                            <th>Status</th>
                            <th>Created At</th>
                        </tr>
                        <?php foreach ($supplier_orders as $order): ?>
                        <tr>
                            <td><?= htmlspecialchars($order['order_id']) ?></td>
                            <td><?= htmlspecialchars($order['supplier_name']) ?></td>
                            <td><?= htmlspecialchars($order['item_name']) ?></td>
                            <td><?= htmlspecialchars($order['quantity']) ?></td>
                            <td><?= htmlspecialchars($order['order_date']) ?></td>
                            <td><?= htmlspecialchars($order['status']) ?></td>
                            <td><?= htmlspecialchars($order['created_at']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                    <?php endif; ?>
                </div>
                <?php elseif ($showRequestMaterialReturns): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Request Material Returns</span>
                        <div class="card-icon bg-secondary"><i class="fas fa-undo-alt"></i></div>
                    </div>
                    <?php if ($errorMessage): ?>
                        <div class="message error"><?= htmlspecialchars($errorMessage) ?></div>
                    <?php endif; ?>
                    <?php if ($successMessage): ?>
                        <div class="message success"><?= htmlspecialchars($successMessage) ?></div>
                    <?php endif; ?>
                    <form action="" method="POST" class="form-table">
                        <input type="hidden" name="request_material" value="1">
                        <table>
                            <tr>
                                <th><label for="item_id">Item</label></th>
                                <td>
                                    <select id="item_id" name="item_id" required>
                                        <option value="">Select Item</option>
                                        <?php foreach ($inventory_items as $item): ?>
                                        <option value="<?= $item['item_id'] ?>"><?= htmlspecialchars($item['item_name']) ?> (ID: <?= $item['item_id'] ?>)</option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="quantity">Quantity</label></th>
                                <td><input type="number" id="quantity" name="quantity" min="1" required></td>
                            </tr>
                            <tr>
                                <th><label for="reason">Reason</label></th>
                                <td><textarea id="reason" name="reason" required></textarea></td>
                            </tr>
                        </table>
                        <button type="submit" class="btn"><i class="fas fa-save"></i> Request Return</button>
                    </form>
                    <?php if ($returns): ?>
                    <table class="data-table">
                        <tr>
                            <th>Return ID</th>
                            <th>Item Name</th>
                            <th>Quantity</th>
                            <th>Reason</th>
                            <th>Status</th>
                            <th>Requested At</th>
                        </tr>
                        <?php foreach ($returns as $return): ?>
                        <tr>
                            <td><?= htmlspecialchars($return['id']) ?></td>
                            <td><?= htmlspecialchars($return['item_name']) ?></td>
                            <td><?= htmlspecialchars($return['quantity']) ?></td>
                            <td><?= htmlspecialchars($return['reason']) ?></td>
                            <td><?= htmlspecialchars($return['status']) ?></td>
                            <td><?= htmlspecialchars($return['request_timestamp']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                    <?php endif; ?>
                </div>
                <?php elseif ($showInventoryReports): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Generate Inventory Reports</span>
                        <div class="card-icon bg-accent"><i class="fas fa-file-alt"></i></div>
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
                            <td><a href="<?= htmlspecialchars($report['file_path']) ?>" class="btn"><i class="fas fa-download"></i> View</a></td>
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
                        <span class="card-title">Total Items</span>
                        <div class="card-icon bg-primary"><i class="fas fa-boxes"></i></div>
                    </div>
                    <div class="card-value"><?php
                        $stmt = $pdo->query("SELECT COUNT(*) FROM inventory");
                        echo $stmt->fetchColumn();
                    ?></div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Low Stock Items</span>
                        <div class="card-icon bg-accent"><i class="fas fa-exclamation-triangle"></i></div>
                    </div>
                    <div class="card-value"><?php
                        $stmt = $pdo->query("SELECT COUNT(*) FROM inventory WHERE quantity < 10");
                        echo $stmt->fetchColumn();
                    ?></div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Pending Returns</span>
                        <div class="card-icon bg-secondary"><i class="fas fa-undo-alt"></i></div>
                    </div>
                    <div class="card-value"><?php
                        $stmt = $pdo->query("SELECT COUNT(*) FROM material_returns WHERE status = 'Pending'");
                        echo $stmt->fetchColumn();
                    ?></div>
                </div>
                <?php endif; ?>
            </div>

            <div class="recent-activity">
                <h3 class="section-title">Recent Activity</h3>
                <div class="activity-item">
                    <div class="activity-icon"><i class="fas fa-plus-square"></i></div>
                    <div class="activity-content">
                        <div class="activity-title">New inventory item added</div>
                        <div class="activity-time">2 hours ago</div>
                    </div>
                </div>
                <div class="activity-item">
                    <div class="activity-icon"><i class="fas fa-truck-loading"></i></div>
                    <div class="activity-content">
                        <div class="activity-title">Supplier order placed</div>
                        <div class="activity-time">Yesterday</div>
                    </div>
                </div>
                <div class="activity-item">
                    <div class="activity-icon"><i class="fas fa-undo-alt"></i></div>
                    <div class="activity-content">
                        <div class="activity-title">Material return requested</div>
                        <div class="activity-time">Yesterday</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>