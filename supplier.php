<?php
session_start();
require 'db_connection.php';

// Check if supplier is logged in
if (!isset($_SESSION['supplier_id'])) {
    $_SESSION['error'] = "Please log in to access the dashboard.";
    header('Location: supplier_login.php');
    exit;
}

$supplier_id = $_SESSION['supplier_id'];
$displayName = htmlspecialchars($_SESSION['supplier_username']);
$avatar = strtoupper(substr($displayName, 0, 1));

// Initialize message variables
$successMessage = '';
$errorMessage = '';

// Initialize view variables
$views = [
    'update_supplier', 'add_material_category', 'update_material_availability', 'manage_orders',
    'update_pricing', 'update_order_status', 'update_delivery_status', 'submit_inventory',
    'quality_reports', 'submit_invoices', 'view_payments', 'supplier_feedback'
];
$show = array_fill_keys($views, false);

// Determine view based on GET parameter
$view = isset($_GET['view']) && in_array($_GET['view'], $views) ? $_GET['view'] : '';
$show[$view] = true;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Update Supplier Information
        if (isset($_POST['update_supplier'])) {
            $company_name = trim($_POST['company_name']);
            $contact_email = trim($_POST['contact_email']);
            $contact_phone = trim($_POST['contact_phone']);
            $address = trim($_POST['address']);
            if (empty($company_name) || empty($contact_email) || empty($contact_phone) || empty($address)) {
                $errorMessage = "All fields are required.";
                $show['update_supplier'] = true;
            } else {
                $stmt = $pdo->prepare("INSERT INTO supplier_info (supplier_id, company_name, contact_email, contact_phone, address, updated_at) 
                    VALUES (?, ?, ?, ?, ?, NOW()) ON DUPLICATE KEY UPDATE 
                    company_name = ?, contact_email = ?, contact_phone = ?, address = ?, updated_at = NOW()");
                $stmt->execute([$supplier_id, $company_name, $contact_email, $contact_phone, $address, 
                                $company_name, $contact_email, $contact_phone, $address]);
                $successMessage = "Supplier information updated successfully!";
                $show['update_supplier'] = true;
            }
        }
        // Add Material Category
        if (isset($_POST['add_material_category'])) {
            $name = trim($_POST['name']);
            $description = trim($_POST['description']);
            if (empty($name) || empty($description)) {
                $errorMessage = "All fields are required.";
                $show['add_material_category'] = true;
            } else {
                $stmt = $pdo->prepare("INSERT INTO material_categories (supplier_id, name, description) VALUES (?, ?, ?)");
                $stmt->execute([$supplier_id, $name, $description]);
                $successMessage = "Material category added successfully!";
                $show['add_material_category'] = true;
            }
        }
        // Update Material Availability
        if (isset($_POST['update_material_availability'])) {
            $material_id = $_POST['material_id'];
            $availability = $_POST['availability'];
            if (empty($material_id) || empty($availability)) {
                $errorMessage = "All fields are required.";
                $show['update_material_availability'] = true;
            } else {
                $stmt = $pdo->prepare("UPDATE materials SET availability = ? WHERE material_id = ? AND supplier_id = ?");
                $stmt->execute([$availability, $material_id, $supplier_id]);
                $successMessage = "Material availability updated successfully!";
                $show['update_material_availability'] = true;
            }
        }
        // Update Material Pricing
        if (isset($_POST['update_pricing'])) {
            $material_id = $_POST['material_id'];
            $price = $_POST['price'];
            if (empty($material_id) || empty($price)) {
                $errorMessage = "All fields are required.";
                $show['update_pricing'] = true;
            } elseif (!is_numeric($price) || $price <= 0) {
                $errorMessage = "Price must be a positive number.";
                $show['update_pricing'] = true;
            } else {
                $stmt = $pdo->prepare("UPDATE materials SET price = ? WHERE material_id = ? AND supplier_id = ?");
                $stmt->execute([$price, $material_id, $supplier_id]);
                $successMessage = "Material pricing updated successfully!";
                $show['update_pricing'] = true;
            }
        }
        // Update Order Status
        if (isset($_POST['update_order_status'])) {
            $supplier_order_id = $_POST['order_id'];
            $status = $_POST['status'];
            if (empty($supplier_order_id) || empty($status)) {
                $errorMessage = "All fields are required.";
                $show['update_order_status'] = true;
            } else {
                $stmt = $pdo->prepare("UPDATE supplier_orders SET status = ? WHERE order_id = ? AND supplier_id = ?");
                $stmt->execute([$status, $supplier_order_id, $supplier_id]);
                $successMessage = "Order status updated successfully!";
                $show['update_order_status'] = true;
            }
        }
        // Update Delivery Status
        if (isset($_POST['update_delivery_status'])) {
            $supplier_order_id = $_POST['supplier_order_id'];
            $delivery_status = $_POST['delivery_status'];
            if (empty($supplier_order_id) || empty($delivery_status)) {
                $errorMessage = "All fields are required.";
                $show['update_delivery_status'] = true;
            } else {
                $stmt = $pdo->prepare("UPDATE supplier_orders SET delivery_status = ? WHERE supplier_order_id = ? AND supplier_id = ?");
                $stmt->execute([$delivery_status, $supplier_order_id, $supplier_id]);
                $successMessage = "Delivery status updated successfully!";
                $show['update_delivery_status'] = true;
            }
        }
        // Submit Inventory Update
        if (isset($_POST['submit_inventory'])) {
            $material_id = $_POST['material_id'];
            $quantity = $_POST['quantity'];
            if (empty($material_id) || empty($quantity)) {
                $errorMessage = "All fields are required.";
                $show['submit_inventory'] = true;
            } elseif (!is_numeric($quantity) || $quantity < 0) {
                $errorMessage = "Quantity must be a non-negative number.";
                $show['submit_inventory'] = true;
            } else {
                try {
                    $stmt = $pdo->prepare("INSERT INTO inventory_updates (supplier_id, material_id, quantity, updated_at) VALUES (?, ?, ?, NOW())");
                    $stmt->execute([$supplier_id, $material_id, $quantity]);
                    $successMessage = "Inventory update submitted successfully!";
                    $show['submit_inventory'] = true;
                } catch (PDOException $e) {
                    $errorMessage = "Database error: " . $e->getMessage();
                }
            }
        }
        // Add Quality Report
        if (isset($_POST['add_quality_report'])) {
            $material_id = $_POST['material_id'];
            $comments = trim($_POST['comments']);
            if (empty($material_id) || empty($comments) || empty($_FILES['report_file']['name'])) {
                $errorMessage = "All fields are required.";
                $show['quality_reports'] = true;
            } elseif ($_FILES['report_file']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = 'uploads/';
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
                $file_name = time() . '_' . basename($_FILES['report_file']['name']);
                $file_path = $upload_dir . $file_name;
                if (move_uploaded_file($_FILES['report_file']['tmp_name'], $file_path)) {
                    $stmt = $pdo->prepare("INSERT INTO quality_reports (supplier_id, material_id, report_file, comments, created_at) VALUES (?, ?, ?, ?, NOW())");
                    $stmt->execute([$supplier_id, $material_id, $file_path, $comments]);
                    $successMessage = "Quality report added successfully!";
                    $show['quality_reports'] = true;
                } else {
                    $errorMessage = "Failed to upload file.";
                    $show['quality_reports'] = true;
                }
            } else {
                $errorMessage = "File upload error.";
                $show['quality_reports'] = true;
            }
        }
        // Submit Invoice
        if (isset($_POST['submit_invoice'])) {
            $supplier_order_id = $_POST['supplier_order_id'];
            $amount = $_POST['amount'];
            if (empty($supplier_order_id) || empty($amount)) {
                $errorMessage = "All fields are required.";
                $show['submit_invoices'] = true;
            } elseif (!is_numeric($amount) || $amount <= 0) {
                $errorMessage = "Amount must be a positive number.";
                $show['submit_invoices'] = true;
            } else {
                $stmt = $pdo->prepare("INSERT INTO supplier_invoices (supplier_id, supplier_order_id, amount, issue_date, created_at) VALUES (?, ?, ?, CURDATE(), NOW())");
                $stmt->execute([$supplier_id, $supplier_order_id, $amount]);
                $successMessage = "Invoice submitted successfully!";
                $show['submit_invoices'] = true;
            }
        }
        // Submit Feedback
        if (isset($_POST['submit_feedback'])) {
            $comments = trim($_POST['comments']);
            if (empty($comments)) {
                $errorMessage = "Comments are required.";
                $show['supplier_feedback'] = true;
            } else {
                $stmt = $pdo->prepare("INSERT INTO supplier_feedback (supplier_id, comments, created_at) VALUES (?, ?, NOW())");
                $stmt->execute([$supplier_id, $comments]);
                $successMessage = "Feedback submitted successfully!";
                $show['supplier_feedback'] = true;
            }
        }
    } catch (PDOException $e) {
        $errorMessage = "Database error: " . htmlspecialchars($e->getMessage());
        $show[$view] = true;
    }
}

// Fetch data for views
if ($show['update_supplier']) {
    $stmt = $pdo->prepare("SELECT * FROM supplier_info WHERE supplier_id = ?");
    $stmt->execute([$supplier_id]);
    $supplier_info = $stmt->fetch(PDO::FETCH_ASSOC);
}
if ($show['add_material_category']) {
    $stmt = $pdo->prepare("SELECT * FROM material_categories WHERE supplier_id = ? ORDER BY name");
    $stmt->execute([$supplier_id]);
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
if ($show['update_material_availability'] || $show['update_pricing'] || $show['quality_reports']) {
    $stmt = $pdo->prepare("SELECT m.*, c.name AS category_name, cat.name AS item_name 
                           FROM materials m 
                           JOIN material_categories c ON m.category_id = c.category_id 
                           JOIN catalogue cat ON m.item_id = cat.item_id 
                           WHERE m.supplier_id = ?");
    $stmt->execute([$supplier_id]);
    $materials = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
if ($show['manage_orders'] || $show['update_order_status'] || $show['update_delivery_status'] || $show['submit_invoices']) {
    $stmt = $pdo->prepare("SELECT so.*, o.order_id, c.name AS item_name, cl.client_username 
                           FROM supplier_orders so 
                           JOIN orders o ON so.order_id = o.order_id 
                           JOIN custom_products cp ON o.custom_id = cp.custom_id 
                           JOIN catalogue c ON cp.item_id = c.item_id 
                           JOIN clients cl ON o.client_id = cl.client_id 
                           WHERE so.supplier_id = ? ORDER BY so.order_id DESC");
    $stmt->execute([$supplier_id]);
    $supplier_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
// Always fetch inventory updates when showing this view
if ($show['submit_inventory']) {
    // Fetch materials for dropdown
    $stmt = $pdo->prepare("SELECT m.*, c.name AS category_name, cat.name AS item_name 
                          FROM materials m 
                          JOIN material_categories c ON m.category_id = c.category_id 
                          JOIN catalogue cat ON m.item_id = cat.item_id 
                          WHERE m.supplier_id = ?");
    $stmt->execute([$supplier_id]);
    $materials = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch existing inventory updates
    $stmt = $pdo->prepare("SELECT iu.*, m.name AS material_name 
                          FROM inventory_updates iu
                          JOIN materials m ON iu.material_id = m.material_id
                          WHERE iu.supplier_id = ?
                          ORDER BY iu.updated_at DESC");
    $stmt->execute([$supplier_id]);
    $inventory_updates = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
if ($show['submit_invoices']) {
    $stmt = $pdo->prepare("SELECT si.*, so.supplier_order_id, c.name AS item_name 
                           FROM supplier_invoices si 
                           JOIN supplier_orders so ON si.supplier_order_id = so.supplier_order_id 
                           JOIN orders o ON so.order_id = o.order_id 
                           JOIN custom_products cp ON o.custom_id = cp.custom_id 
                           JOIN catalogue c ON cp.item_id = c.item_id 
                           WHERE si.supplier_id = ? ORDER BY si.created_at DESC");
    $stmt->execute([$supplier_id]);
    $invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
if ($show['view_payments']) {
    $stmt = $pdo->prepare("SELECT sp.*, si.invoice_id, c.name AS item_name 
                           FROM supplier_payments sp 
                           JOIN supplier_invoices si ON sp.invoice_id = si.invoice_id 
                           JOIN supplier_orders so ON si.supplier_order_id = so.supplier_order_id 
                           JOIN orders o ON so.order_id = o.order_id 
                           JOIN custom_products cp ON o.custom_id = cp.custom_id 
                           JOIN catalogue c ON cp.item_id = c.item_id 
                           WHERE sp.supplier_id = ? ORDER BY sp.created_at DESC");
    $stmt->execute([$supplier_id]);
    $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
if ($show['supplier_feedback']) {
    $stmt = $pdo->prepare("SELECT * FROM supplier_feedback WHERE supplier_id = ? ORDER BY created_at DESC");
    $stmt->execute([$supplier_id]);
    $feedbacks = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supplier Dashboard | Digiscan</title>
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
            border-collapse: collapse;
        }
        
        .data-table th, .data-table td, .form-table th, .form-table td {
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .data-table th, .form-table th {
            text-align: left;
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
        
        .cancel-btn {
            background: var(--accent-color);
            color: white;
        }
        
        .cancel-btn:hover {
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
                <h3>Supplier Dashboard</h3>
                <p><?php echo $displayName; ?></p>
            </div>
            <div class="sidebar-menu">
                <a href="?view=update_supplier" class="menu-item <?php echo $show['update_supplier'] ? 'active' : ''; ?>"><i class="fas fa-user-edit"></i> Update Supplier Information</a>
                <a href="?view=add_material_category" class="menu-item <?php echo $show['add_material_category'] ? 'active' : ''; ?>"><i class="fas fa-folder-plus"></i> Add Material Categories</a>
                <a href="?view=update_material_availability" class="menu-item <?php echo $show['update_material_availability'] ? 'active' : ''; ?>"><i class="fas fa-warehouse"></i> Update Material Availability</a>
                <a href="?view=manage_orders" class="menu-item <?php echo $show['manage_orders'] ? 'active' : ''; ?>"><i class="fas fa-shopping-cart"></i> View/Manage Orders</a>
                <a href="?view=update_pricing" class="menu-item <?php echo $show['update_pricing'] ? 'active' : ''; ?>"><i class="fas fa-dollar-sign"></i> Update Material Pricing</a>
                <a href="?view=update_order_status" class="menu-item <?php echo $show['update_order_status'] ? 'active' : ''; ?>"><i class="fas fa-sync"></i> Update Order Status</a>
                <a href="?view=update_delivery_status" class="menu-item <?php echo $show['update_delivery_status'] ? 'active' : ''; ?>"><i class="fas fa-truck"></i> Update Delivery Status</a>
                <a href="?view=submit_inventory" class="menu-item <?php echo $show['submit_inventory'] ? 'active' : ''; ?>"><i class="fas fa-boxes"></i> Submit Inventory Updates</a>
                <a href="?view=quality_reports" class="menu-item <?php echo $show['quality_reports'] ? 'active' : ''; ?>"><i class="fas fa-file-alt"></i> Add Quality Reports</a>
                <a href="?view=submit_invoices" class="menu-item <?php echo $show['submit_invoices'] ? 'active' : ''; ?>"><i class="fas fa-file-invoice"></i> Submit Invoices</a>
                <a href="?view=view_payments" class="menu-item <?php echo $show['view_payments'] ? 'active' : ''; ?>"><i class="fas fa-credit-card"></i> View Payments</a>
                <a href="?view=supplier_feedback" class="menu-item <?php echo $show['supplier_feedback'] ? 'active' : ''; ?>"><i class="fas fa-comment"></i> Submit Feedback</a>
                <a href="supplier_logout.php" class="menu-item" style="margin-top: 2rem; color: #e74c3c;"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="top-bar">
                <h2>Welcome back, <?php echo $displayName; ?>!</h2>
                <div class="user-info">
                    <div class="user-avatar"><?php echo $avatar; ?></div>
                </div>
            </div>

            <div class="dashboard-cards">
                <?php if (!$view): ?>
                <!-- Default View: Summary Cards -->
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Pending Orders</span>
                        <div class="card-icon bg-primary"><i class="fas fa-shopping-cart"></i></div>
                    </div>
                    <div class="card-value">
                        <?php
                        $stmt = $pdo->prepare("SELECT COUNT(*) FROM supplier_orders WHERE supplier_id = ? AND status = 'Pending'");
                        $stmt->execute([$supplier_id]);
                        echo htmlspecialchars($stmt->fetchColumn());
                        ?>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Low Stock Materials</span>
                        <div class="card-icon bg-accent"><i class="fas fa-warehouse"></i></div>
                    </div>
                    <div class="card-value">
                        <?php
                        $stmt = $pdo->prepare("SELECT COUNT(*) FROM materials WHERE supplier_id = ? AND availability = 'Low Stock'");
                        $stmt->execute([$supplier_id]);
                        echo htmlspecialchars($stmt->fetchColumn());
                        ?>
                    </div>
                </div>
                <?php elseif ($show['update_supplier']): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Update Supplier Information</span>
                        <div class="card-icon bg-primary"><i class="fas fa-user-edit"></i></div>
                    </div>
                    <?php if ($errorMessage): ?>
                        <div class="message error"><?php echo htmlspecialchars($errorMessage); ?></div>
                    <?php endif; ?>
                    <?php if ($successMessage): ?>
                        <div class="message success"><?php echo htmlspecialchars($successMessage); ?></div>
                    <?php endif; ?>
                    <form action="" method="POST" class="form-table">
                        <input type="hidden" name="update_supplier" value="1">
                        <table>
                            <tr>
                                <th><label for="company_name">Company Name</label></th>
                                <td><input type="text" id="company_name" name="company_name" value="<?php echo htmlspecialchars($supplier_info['company_name'] ?? ''); ?>" required></td>
                            </tr>
                            <tr>
                                <th><label for="contact_email">Contact Email</label></th>
                                <td><input type="email" id="contact_email" name="contact_email" value="<?php echo htmlspecialchars($supplier_info['contact_email'] ?? ''); ?>" required></td>
                            </tr>
                            <tr>
                                <th><label for="contact_phone">Contact Phone</label></th>
                                <td><input type="text" id="contact_phone" name="contact_phone" value="<?php echo htmlspecialchars($supplier_info['contact_phone'] ?? ''); ?>" required></td>
                            </tr>
                            <tr>
                                <th><label for="address">Address</label></th>
                                <td><textarea id="address" name="address" required><?php echo htmlspecialchars($supplier_info['address'] ?? ''); ?></textarea></td>
                            </tr>
                        </table>
                        <button type="submit" class="btn"><i class="fas fa-save"></i> Update</button>
                    </form>
                </div>
                <?php elseif ($show['add_material_category']): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Add Material Category</span>
                        <div class="card-icon bg-secondary"><i class="fas fa-folder-plus"></i></div>
                    </div>
                    <?php if ($errorMessage): ?>
                        <div class="message error"><?php echo htmlspecialchars($errorMessage); ?></div>
                    <?php endif; ?>
                    <?php if ($successMessage): ?>
                        <div class="message success"><?php echo htmlspecialchars($successMessage); ?></div>
                    <?php endif; ?>
                    <form action="" method="POST" class="form-table">
                        <input type="hidden" name="add_material_category" value="1">
                        <table>
                            <tr>
                                <th><label for="name">Category Name</label></th>
                                <td><input type="text" id="name" name="name" required></td>
                            </tr>
                            <tr>
                                <th><label for="description">Description</label></th>
                                <td><textarea id="description" name="description" required></textarea></td>
                            </tr>
                        </table>
                        <button type="submit" class="btn"><i class="fas fa-save"></i> Add Category</button>
                    </form>
                    <?php if ($categories): ?>
                    <table class="data-table">
                        <tr>
                            <th>Category ID</th>
                            <th>Name</th>
                            <th>Description</th>
                        </tr>
                        <?php foreach ($categories as $category): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($category['category_id']); ?></td>
                            <td><?php echo htmlspecialchars($category['name']); ?></td>
                            <td><?php echo htmlspecialchars($category['description']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                    <?php else: ?>
                    <p>No categories found.</p>
                    <?php endif; ?>
                </div>
                <?php elseif ($show['update_material_availability']): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Update Material Availability</span>
                        <div class="card-icon bg-accent"><i class="fas fa-warehouse"></i></div>
                    </div>
                    <?php if ($errorMessage): ?>
                        <div class="message error"><?php echo htmlspecialchars($errorMessage); ?></div>
                    <?php endif; ?>
                    <?php if ($successMessage): ?>
                        <div class="message success"><?php echo htmlspecialchars($successMessage); ?></div>
                    <?php endif; ?>
                    <form action="" method="POST" class="form-table">
                        <input type="hidden" name="update_material_availability" value="1">
                        <table>
                            <tr>
                                <th><label for="material_id">Material</label></th>
                                <td>
                                    <select id="material_id" name="material_id" required>
                                        <option value="">Select Material</option>
                                        <?php foreach ($materials as $material): ?>
                                        <option value="<?php echo htmlspecialchars($material['material_id']); ?>">
                                            <?php echo htmlspecialchars($material['item_name'] . ' (' . $material['category_name'] . ')'); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="availability">Availability</label></th>
                                <td>
                                    <select id="availability" name="availability" required>
                                        <option value="In Stock">In Stock</option>
                                        <option value="Low Stock">Low Stock</option>
                                        <option value="Out of Stock">Out of Stock</option>
                                    </select>
                                </td>
                            </tr>
                        </table>
                        <button type="submit" class="btn"><i class="fas fa-save"></i> Update</button>
                    </form>
                    <?php if ($materials): ?>
                    <table class="data-table">
                        <tr>
                            <th>Material ID</th>
                            <th>Item</th>
                            <th>Category</th>
                            <th>Availability</th>
                            <th>Price</th>
                        </tr>
                        <?php foreach ($materials as $material): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($material['material_id']); ?></td>
                            <td><?php echo htmlspecialchars($material['item_name']); ?></td>
                            <td><?php echo htmlspecialchars($material['category_name']); ?></td>
                            <td><?php echo htmlspecialchars($material['availability']); ?></td>
                            <td>$<?php echo number_format($material['price'], 2); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                    <?php else: ?>
                    <p>No materials found.</p>
                    <?php endif; ?>
                </div>
                <?php elseif ($show['manage_orders']): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">View/Manage Orders</span>
                        <div class="card-icon bg-primary"><i class="fas fa-shopping-cart"></i></div>
                    </div>
                    <?php if ($supplier_orders): ?>
                    <table class="data-table">
                        <tr>
                            <th>Order Number</th>
                            <th>Client</th>
                            <th>Item</th>
                            <th>Status</th>
                            <th>Delivery Status</th>
                        </tr>
                        <?php foreach ($supplier_orders as $order): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($order['order_id']); ?></td>
                            <td><?php echo htmlspecialchars($order['client_username']); ?></td>
                            <td><?php echo htmlspecialchars($order['item_name']); ?></td>
                            <td><?php echo htmlspecialchars($order['status']); ?></td>
                            <td><?php echo htmlspecialchars($order['delivery_status']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                    <?php else: ?>
                    <p>No orders found.</p>
                    </div>
                    <?php endif; ?>
                </div>
                <?php elseif ($show['update_pricing']): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Update Material Pricing</span>
                        <div class="card-icon bg-secondary"><i class="fas fa-dollar-sign"></i></div>
                    </div>
                    <?php if ($errorMessage): ?>
                        <div class="message error"><?php echo htmlspecialchars($errorMessage); ?></div>
                    <?php endif; ?>
                    <?php if ($successMessage): ?>
                        <div class="message success"><?php echo htmlspecialchars($successMessage); ?></div>
                    <?php endif; ?>
                    <form action="" method="POST" class="form-table">
                        <input type="hidden" name="update_pricing" value="1">
                        <table>
                            <tr>
                                <th><label for="material_id">Material</label></th>
                                <td>
                                    <select id="material_id" name="material_id" required>
                                        <option value="">Select Material</option>
                                        <?php foreach ($materials as $material): ?>
                                        <option value="<?php echo htmlspecialchars($material['material_id']); ?>">
                                            <?php echo htmlspecialchars($material['item_name'] . ' (' . $material['category_name'] . ')'); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="price">Price ($)</label></th>
                                <td><input type="number" id="price" name="price" step="0.01" min="0" required></td>
                            </tr>
                        </table>
                        <button type="submit" class="btn"><i class="fas fa-save"></i> Update</button>
                    </form>
                    <?php if ($materials): ?>
                    <table class="data-table">
                        <tr>
                            <th>Material ID</th>
                            <th>Item</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Availability</th>
                        </tr>
                        <?php foreach ($materials as $material): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($material['material_id']); ?></td>
                            <td><?php echo htmlspecialchars($material['item_name']); ?></td>
                            <td><?php echo htmlspecialchars($material['category_name']); ?></td>
                            <td>$<?php echo number_format($material['price'], 2); ?></td>
                            <td><?php echo htmlspecialchars($material['availability']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                    <?php else: ?>
                    <p>No materials found.</p>
                    <?php endif; ?>
                </div>
                <?php elseif ($show['update_order_status']): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Update Order Status</span>
                        <div class="card-icon bg-accent"><i class="fas fa-sync"></i></div>
                    </div>
                    <?php if ($errorMessage): ?>
                        <div class="message error"><?php echo htmlspecialchars($errorMessage); ?></div>
                    <?php endif; ?>
                    <?php if ($successMessage): ?>
                        <div class="message success"><?php echo htmlspecialchars($successMessage); ?></div>
                    <?php endif; ?>
                    <form action="" method="POST" class="form-table">
                        <input type="hidden" name="update_order_status" value="1">
                    <table>
                        <tr>
                            <th><label for="order_id">Order</label></th>
                            <td>
                                <select id="order_id" name="order_id" required>
                                    <option value="">Select an Order</option>
                                    <?php foreach ($orders as $order): ?>
                                    <option value="<?php echo htmlspecialchars($order['order_id']); ?>">
                                        ID <?php echo htmlspecialchars($order['order_id']); ?> (<?php echo htmlspecialchars($order['name']); ?>)
                                    </option>
                                    <?php endforeach; ?>
                                </td>
                        </tr>
                        <tr>
                            <th><label for="status">Status</label></th>
                            <td>
                                <select id="status" name="status" required>
                                    <option value="">Select Status</option>
                                    <option value="Pending">Pending</option>
                                    <option value="Shipped">Shipped</option>
                                    <option value="Delivered">Delivered</option>
                                    <option value="Cancelled">Cancelled</option>
                                </select>
                            </td>
                        </tr>
                    </table>
                    <button type="submit" class="btn"><i class="fas fa-save"></i> Update</button>
                    </form>
                    <?php if ($orders): ?>
                    <table class="data-table">
                        <tr>
                            <th>Order Number</th>
                            <th>Supplier</th>
                            <th>Item</th>
                            <th>Status</th>
                        </tr>
                        <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($order['order_id']); ?></td>
                            <td><?php echo htmlspecialchars($order['supplier_name']); ?></td>
                            <td><?php echo htmlspecialchars($order['item_id']); ?></td>
                            <td><?php echo htmlspecialchars($order['status']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                    <?php else: ?>
                    <p>No orders found.</p>
                    <?php endif; ?>
                </div>
                <?php elseif ($show['update_delivery_status']): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Update Delivery Status</span>
                        <div class="card-icon bg-primary"><i class="fas fa-truck"></i></div>
                    </div>
                    <?php if ($errorMessage): ?>
                        <div class="message error"><?php echo htmlspecialchars($errorMessage); ?></div>
                    <?php endif; ?>
                    <?php if ($successMessage): ?>
                        <div class="message success"><?php echo htmlspecialchars($successMessage); ?></div>
                    <?php endif ?>
                    <form action="" method="POST" class="form-table">
                        <input type="hidden" name="update_delivery_status" value="1">
                        <table>
                            <tr>
                            <th><label for="order_id">Order</label></th>
                            <td>
                                <select id="order_id" name="order_id" required>
                                    <option value="">Select Order</option>
                                    <?php foreach ($orders as $order): ?>
                                    <option value="<?php echo htmlspecialchars($order['order_id']); ?>">
                                        ID <?php echo htmlspecialchars($order['order_id']); ?> (<?php echo htmlspecialchars($order['item_id']); ?>)
                                    </option>
                                    <?php endforeach; ?>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="delivery_status">Delivery Status</label></th>
                                <td>
                                    <select id="delivery_status" name="delivery_status" required>
                                        <option value="none">Select Delivery Status</option>
                                        <option value="Not Shipped">Not Shipped</option>
                                        <option value="Shipped">Shipped</option>
                                        <option value="Delivered">Delivered</option>
                                    </select>
                                </td>
                            </tr>
                        </table>
                        <button type="submit" type="btn"><i class="fas fa-save"></i> Update</button>
                    </form>
                    <?php if ($orders): ?>
                    <table class="data-table">
                        <tr>
                            <th>Order Number</th>
                            <th>Client</th>
                            <th>Item</th>
                            <th>Status</th>
                            <th>Delivery Status</th>
                        </tr>
                        <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($order['order_id']); ?></td>
                            <td><?php echo htmlspecialchars($order['client_id']); ?></td>
                            <td><?php echo htmlspecialchars($order['item_id']); ?></td>
                            <td><?php echo htmlspecialchars($order['status']); ?></td>
                            <td><?php echo htmlspecialchars($order['delivery_status']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                    <?php else: ?>
                    <p>No orders found.</p>
                    <?php endif; ?>
                </div>
                <?php elseif ($show['submit_inventory']): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Submit Inventory Updates</span>
                        <div class="card-icon bg-success"><i class="fas fa-boxes"></i></div>
                    </div>
                    <?php if ($errorMessage): ?>
                        <div class="message error"><?php echo htmlspecialchars($errorMessage); ?></div>
                    <?php endif; ?>
                    <?php if ($successMessage): ?>
                        <div class="message success"><?php echo htmlspecialchars($successMessage); ?></div>
                    <?php endif; ?>
                    <form action="" method="POST" class="form-table">
                        <input type="hidden" name="submit_inventory" value="1">
                        <table>
                            <tr>
                                <th><label for="material_id">Material</label></th>
                                <td>
                                    <select id="material_id" name="material_id" required>
                                        <option value="">Select Material</option>
                                        <?php foreach ($materials as $material): ?>
                                        <option value="<?php echo htmlspecialchars($material['material_id']); ?>">
                                            <?php echo htmlspecialchars($material['item_name'] . ' (' . $material['category_name'] . ')'); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="quantity">Quantity</label></th>
                                <td><input type="number" id="quantity" name="quantity" min="0" required></td>
                            </tr>
                        </table>
                        <button type="submit" class="btn"><i class="fas fa-save"></i> Submit</button>
                    </form>
                    
                    <?php if (!empty($inventory_updates)): ?>
                    <table class="data-table">
                        <tr>
                            <th>Update ID</th>
                            <th>Material</th>
                            <th>Quantity</th>
                            <th>Updated At</th>
                        </tr>
                        <?php foreach ($inventory_updates as $update): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($update['update_id']); ?></td>
                            <td><?php echo htmlspecialchars($update['material_name']); ?></td>
                            <td><?php echo htmlspecialchars($update['quantity']); ?></td>
                            <td><?php echo htmlspecialchars($update['updated_at']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                    <?php else: ?>
                    <p>No inventory updates found.</p>
                    <?php endif; ?>
                </div>
                <?php elseif ($show['quality_reports']): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Add Quality Reports</span>
                        <div class="card-icon bg-primary"><i class="fas fa-file-alt"></i></div>
                    </div>
                    <?php if ($errorMessage): ?>
                        <div class="message error"><?php echo htmlspecialchars($errorMessage); ?></div>
                    <?php endif; ?>
                    <?php if ($successMessage): ?>
                        <div class="message success"><?php echo htmlspecialchars($successMessage); ?></div>
                    <?php endif; ?>
                    <form action="" method="POST" enctype="multipart/form-data" class="form-table">
                        <input type="hidden" name="add_quality_report" value="1">
                        <table>
                            <tr>
                                <th><label for="material_id">Material</label></th>
                                <td>
                                    <select id="material_id" name="material_id" required>
                                        <option value="">Select Material</option>
                                        <?php foreach ($material as $material): ?>
                                        <option value="<?php echo htmlspecialchars($material['material_id']); ?>">
                                            <?php echo htmlspecialchars($material['material_id'] . ' (' . $material['name'] . ')'); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="report_file">Report File</label></label>
                                <td><input type="file" id="report_file" name="report_file" accept=".pdf" required></td>
                            </tr>
                            <tr>
                                <th><label for="comments">Comments</label></th>
                                <td><textarea id="comments" name="comments" required></textarea></td>
                            </tr>
                        </table>
                        <button type="submit" class="btn"><i class="fas fa-upload"></i> Upload</button>
                    </form>
                    <?php if ($reports): ?>
                    <table class="data-table">
                        <tr>
                            <th>Report ID</th>
                            <th>Material</th>
                            <th>File</th>
                            </th>Comments</th>
                            <th>Created At</th>
                            </tr>
                        <?php foreach ($reports as $report): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($report['report_id']); ?></td>
                            <td><?php echo htmlspecialchars($report['material_id']); ?></td>
                            <td><a href="<?php echo htmlspecialchars($report['report']); ?>" target="_blank" class="btn"><i class="fas fa-download"></i> View</td>
                            <td><?php echo htmlspecialchars($report['comment_id']); ?></td>
                            <td><?php echo htmlspecialchars($report['created_at']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tr>
                    <?php else: ?>
                    <p>No quality reports found.</p>
                    <?php endif; ?>
                </div>
                <?php elseif ($show['submit_invoices']): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Submit Invoices</span>
                        <div class="card-icon bg-secondary"><i class="fas fa-file-invoice"></i></div>
                    </div>
                    <?php if ($errorMessage): ?>
                        <div class="message error"><?php echo htmlspecialchars($errorMessage); ?></div>
                    <?php endif; ?>
                    <?php if ($successMessage): ?>
                        <div class="message success"><?php echo htmlspecialchars($successMessage); ?></div>
                    <?php endif; ?>
                    <form action="" method="POST" class="form-table">
                        <input type="hidden" name="submit_invoice" value="1">
                        <table>
                            <tr>
                                <th><label for="order_id">Order</label></th>
                                <td>
                                    <select id="order_id" name="order_id" required>
                                        <option value="">Select Order</option>
                                        <?php foreach ($orders as $order): ?>
                                        <option value="<?php echo htmlspecialchars($order['order_id']); ?>">
                                            ID <?php echo htmlspecialchars($order['order_id']); ?> (<?php echo htmlspecialchars($order['item_id']); ?>)
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td><label for="amount">Amount ($)</label></th>
                                <td><input type="number" id="amount" name="amount" step="0.01" min="0" required></td>
                            </tr>
                        </table>
                        <button type="submit" class="btn"><i class="fas fa-save"></i> Submit</button>
                    </form>
                    <?php if ($invoices): ?>
                    <table class="data-table">
                        <tr>
                            <th>Invoice ID</th>
                            <th>Order ID</th>
                            <th>Item</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Issue Date</th>
                            <th>Created At</th>
                        </tr>
                        <?php foreach ($invoice as $invoice): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($invoice['invoice_id']); ?></td>
                            <td><?php echo htmlspecialchars($invoice['order_id']); ?></td>
                            <td><?php echo htmlspecialchars($invoice['item_id']); ?></td>
                            <td>$<?php echo number_format($invoice['amount'], 2); ?></td>
                            <td><?php echo htmlspecialchars($invoice['status']); ?></td>
                            <td><?php echo htmlspecialchars($invoice['issue_date']); ?></td>
                            <td><?php echo htmlspecialchars($invoice['created_at']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                    <?php else: ?>
                    <p>No invoices found.</p>
                    <?php endif; ?>
                </div>
                <?php elseif ($show['view_payments']): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">View Payments</span>
                        <div class="card-icon bg-accent"><i class="fas fa-credit-card"></i></div>
                    </div>
                    <?php if ($payments): ?>
                    <table class="data-table">
                        <tr>
                            <th>Payment ID</th>
                            <th>Invoice ID</th>
                            <th>Item</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Created At</th>
                        </tr>
                        <?php foreach ($payment as $payment): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($payment['payment_id']); ?></td>
                            <td><?php echo htmlspecialchars($payment['invoice_id']); ?></td>
                            <td><?php echo htmlspecialchars($payment['item_id']); ?></td>
                            <td>$<?php echo number_format($payment['amount'], 2); ?></td>
                            <td><?php echo htmlspecialchars($payment['method']); ?></td>
                            <td><?php echo htmlspecialchars($payment['created_at']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tr>
                    <?php else: ?>
                        <p>No payments found.</p>
                    <?php endif; ?>
                </div>
                <?php elseif ($show['supplier_feedback']): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Submit Feedback</span>
                        <div class="card-icon bg-success"><i class="fas fa-comment"></i></div>
                    </div>
                    <?php if ($errorMessage): ?>
                        <div class="message error"><?php echo htmlspecialchars($errorMessage); ?></div>
                    <?php endif; ?>
                    <?php if ($successMessage): ?>
                        <div class="message success"><?php echo htmlspecialchars($successMessage); ?></div>
                    <?php endif; ?>
                    <form action="" method="POST" class="form-table">
                        <input type="hidden" name="submit_feedback" value="1">
                        <table>
                            <tr>
                                <td><label for="comments">Comments</label></th>
                                <td><textarea id="comments" name="comments" required></textarea></td>
                            </tr>
                        </table>
                        <button type="submit" class="btn"><i class="fas fa-paper-plane"></i> Submit</button>
                    </form>
                    <?php if ($feedback): ?>
                    <table class="data-table">
                        <tr>
                            <th>Feedback ID</th>
                            <th>Comments</th>
                            <th>Created At</th>
                            </tr>
                        <?php foreach ($feedback as $feedback): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($feedback['feedback_id']); ?></td>
                            <td><?php echo htmlspecialchars($feedback['comment_id']); ?></td>
                            <td><?php echo htmlspecialchars($feedback['created_at']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                    <?php else: ?>
                    <p>No feedback found.</p>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>