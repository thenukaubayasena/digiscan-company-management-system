<?php
session_start();
require 'db_connection.php';

// Check if client is logged in
if (!isset($_SESSION['client_id'])) {
    $_SESSION['error'] = "Please log in to access the dashboard.";
    header('Location: client_login.php');
    exit;
}

$client_id = $_SESSION['client_id'];
$displayName = htmlspecialchars($_SESSION['client_username']);
$avatar = strtoupper(substr($displayName, 0, 1));

// Initialize message variables
$successMessage = '';
$errorMessage = '';

// Initialize view variables
$views = [
    'add_terms', 'view_catalogue', 'customize_product', 'upload_design', 'preview_designs',
    'submit_inquiry', 'view_discounts', 'place_order', 'view_orders', 'cancel_order',
    'view_history', 'view_invoices', 'select_payment', 'request_return', 'request_refund', 'provide_feedback'
];
$show = array_fill_keys($views, false);

// Determine view based on GET parameter
$view = isset($_GET['view']) && in_array($_GET['view'], $views) ? $_GET['view'] : '';
$show[$view] = true;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Add Terms and Agreements
        if (isset($_POST['add_terms'])) {
            $title = trim($_POST['title']);
            $content = trim($_POST['content']);
            if (empty($title) || empty($content)) {
                $errorMessage = "All fields are required.";
                $show['add_terms'] = true;
            } else {
                $stmt = $pdo->prepare("INSERT INTO terms_agreements (client_id, title, content, created_at) VALUES (?, ?, ?, NOW())");
                $stmt->execute([$client_id, $title, $content]);
                $successMessage = "Terms added successfully!";
                $show['add_terms'] = true;
            }
        }
        // Customize Product
        if (isset($_POST['customize_product'])) {
            $item_id = $_POST['item_id'];
            $specifications = trim($_POST['specifications']);
            if (empty($item_id) || empty($specifications)) {
                $errorMessage = "All fields are required.";
                $show['customize_product'] = true;
            } else {
                $stmt = $pdo->prepare("INSERT INTO custom_products (client_id, item_id, specifications, created_at) VALUES (?, ?, ?, NOW())");
                $stmt->execute([$client_id, $item_id, $specifications]);
                $successMessage = "Product customized successfully!";
                $show['customize_product'] = true;
            }
        }
        // Upload Designed File
        if (isset($_POST['upload_design'])) {
            $custom_id = $_POST['custom_id'];
            if (empty($custom_id) || empty($_FILES['design_file']['name'])) {
                $errorMessage = "All fields are required.";
                $show['upload_design'] = true;
            } elseif ($_FILES['design_file']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = 'uploads/';
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
                $file_name = time() . '_' . basename($_FILES['design_file']['name']);
                $file_path = $upload_dir . $file_name;
                if (move_uploaded_file($_FILES['design_file']['tmp_name'], $file_path)) {
                    $stmt = $pdo->prepare("INSERT INTO designed_files (client_id, custom_id, file_path, created_at) VALUES (?, ?, ?, NOW())");
                    $stmt->execute([$client_id, $custom_id, $file_path]);
                    $successMessage = "File uploaded successfully!";
                    $show['upload_design'] = true;
                } else {
                    $errorMessage = "Failed to upload file.";
                    $show['upload_design'] = true;
                }
            } else {
                $errorMessage = "File upload error.";
                $show['upload_design'] = true;
            }
        }
        // Submit Product Inquiry
        if (isset($_POST['submit_inquiry'])) {
            $item_id = $_POST['item_id'];
            $message = trim($_POST['message']);
            if (empty($item_id) || empty($message)) {
                $errorMessage = "All fields are required.";
                $show['submit_inquiry'] = true;
            } else {
                $stmt = $pdo->prepare("INSERT INTO inquiries (client_id, item_id, message, created_at) VALUES (?, ?, ?, NOW())");
                $stmt->execute([$client_id, $item_id, $message]);
                $successMessage = "Inquiry submitted successfully!";
                $show['submit_inquiry'] = true;
            }
        }
        // Place Order
        if (isset($_POST['place_order'])) {
            $custom_id = $_POST['custom_id'];
            $quantity = $_POST['quantity'];
            $discount_id = !empty($_POST['discount_id']) ? $_POST['discount_id'] : null;
            if (empty($custom_id) || empty($quantity)) {
                $errorMessage = "All fields are required.";
                $show['place_order'] = true;
            } elseif (!is_numeric($quantity) || $quantity <= 0) {
                $errorMessage = "Quantity must be a positive number.";
                $show['place_order'] = true;
            } else {
                // Calculate total amount
                $stmt = $pdo->prepare("SELECT c.price FROM catalogue c JOIN custom_products cp ON c.item_id = cp.item_id WHERE cp.custom_id = ?");
                $stmt->execute([$custom_id]);
                $price = $stmt->fetchColumn();
                $discount = 0;
                if ($discount_id) {
                    $stmt = $pdo->prepare("SELECT discount_percentage FROM discounts WHERE discount_id = ? AND end_date >= CURDATE()");
                    $stmt->execute([$discount_id]);
                    $discount = $stmt->fetchColumn() / 100;
                }
                $total_amount = $price * $quantity * (1 - $discount);
                $stmt = $pdo->prepare("INSERT INTO orders (client_id, custom_id, quantity, total_amount, discount_id, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
                $stmt->execute([$client_id, $custom_id, $quantity, $total_amount, $discount_id]);
                // Create invoice
                $order_id = $pdo->lastInsertId();
                $stmt = $pdo->prepare("INSERT INTO invoices (order_id, amount, due_date, created_at) VALUES (?, ?, CURDATE(), NOW())");
                $stmt->execute([$order_id, $total_amount]);
                $successMessage = "Order placed successfully!";
                $show['place_order'] = true;
            }
        }
        // Cancel Order
        if (isset($_POST['cancel_order'])) {
            $order_id = $_POST['order_id'];
            $stmt = $pdo->prepare("UPDATE orders SET status = 'Cancelled' WHERE order_id = ? AND client_id = ? AND status = 'Pending'");
            $stmt->execute([$order_id, $client_id]);
            if ($stmt->rowCount() > 0) {
                $successMessage = "Order cancelled successfully!";
                $show['cancel_order'] = true;
            } else {
                $errorMessage = "Unable to cancel order.";
                $show['cancel_order'] = true;
            }
        }
        // Select Payment Method
        if (isset($_POST['select_payment'])) {
            $invoice_id = $_POST['invoice_id'];
            $method = $_POST['method'];
            $amount = $_POST['amount'];
            if (empty($invoice_id) || empty($method) || empty($amount)) {
                $errorMessage = "All fields are required.";
                $show['select_payment'] = true;
            } elseif (!is_numeric($amount) || $amount <= 0) {
                $errorMessage = "Amount must be a positive number.";
                $show['select_payment'] = true;
            } else {
                $stmt = $pdo->prepare("INSERT INTO payments (invoice_id, method, amount, created_at) VALUES (?, ?, ?, NOW())");
                $stmt->execute([$invoice_id, $method, $amount]);
                $stmt = $pdo->prepare("UPDATE invoices SET status = 'Paid' WHERE invoice_id = ?");
                $stmt->execute([$invoice_id]);
                $successMessage = "Payment recorded successfully!";
                $show['select_payment'] = true;
            }
        }
        // Request Return
        if (isset($_POST['request_return'])) {
            $order_id = $_POST['order_id'];
            $reason = trim($_POST['reason']);
            if (empty($order_id) || empty($reason)) {
                $errorMessage = "All fields are required.";
                $show['request_return'] = true;
            } else {
                $stmt = $pdo->prepare("INSERT INTO returns (order_id, reason, created_at) VALUES (?, ?, NOW())");
                $stmt->execute([$order_id, $reason]);
                $successMessage = "Return requested successfully!";
                $show['request_return'] = true;
            }
        }
        // Request Refund
        if (isset($_POST['request_refund'])) {
            $return_id = $_POST['return_id'];
            $amount = $_POST['amount'];
            if (empty($return_id) || empty($amount)) {
                $errorMessage = "All fields are required.";
                $show['request_refund'] = true;
            } elseif (!is_numeric($amount) || $amount <= 0) {
                $errorMessage = "Amount must be a positive number.";
                $show['request_refund'] = true;
            } else {
                $stmt = $pdo->prepare("INSERT INTO refunds (return_id, amount, created_at) VALUES (?, ?, NOW())");
                $stmt->execute([$return_id, $amount]);
                $successMessage = "Refund requested successfully!";
                $show['request_refund'] = true;
            }
        }
        // Provide Feedback
        if (isset($_POST['provide_feedback'])) {
            $order_id = !empty($_POST['order_id']) ? $_POST['order_id'] : null;
            $rating = $_POST['rating'];
            $comments = trim($_POST['comments']);
            if (empty($rating) || empty($comments)) {
                $errorMessage = "Rating and comments are required.";
                $show['provide_feedback'] = true;
            } elseif (!is_numeric($rating) || $rating < 1 || $rating > 5) {
                $errorMessage = "Rating must be between 1 and 5.";
                $show['provide_feedback'] = true;
            } else {
                $stmt = $pdo->prepare("INSERT INTO feedback (client_id, order_id, rating, comments, created_at) VALUES (?, ?, ?, ?, NOW())");
                $stmt->execute([$client_id, $order_id, $rating, $comments]);
                $successMessage = "Feedback submitted successfully!";
                $show['provide_feedback'] = true;
            }
        }
    } catch (PDOException $e) {
        $errorMessage = "Database error: " . htmlspecialchars($e->getMessage());
        $show[$view] = true;
    }
}

// Fetch data for views
if ($show['add_terms']) {
    $stmt = $pdo->prepare("SELECT * FROM terms_agreements WHERE client_id = ? ORDER BY created_at DESC");
    $stmt->execute([$client_id]);
    $terms = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
if ($show['view_catalogue']) {
    $stmt = $pdo->prepare("SELECT * FROM catalogue ORDER BY name");
    $stmt->execute();
    $catalogue = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
if ($show['customize_product']) {
    $stmt = $pdo->prepare("SELECT * FROM catalogue ORDER BY name");
    $stmt->execute();
    $catalogue = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt = $pdo->prepare("SELECT cp.*, c.name FROM custom_products cp JOIN catalogue c ON cp.item_id = c.item_id WHERE cp.client_id = ? ORDER BY cp.created_at DESC");
    $stmt->execute([$client_id]);
    $custom_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
if ($show['upload_design']) {
    $stmt = $pdo->prepare("SELECT cp.custom_id, c.name FROM custom_products cp JOIN catalogue c ON cp.item_id = c.item_id WHERE cp.client_id = ?");
    $stmt->execute([$client_id]);
    $custom_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt = $pdo->prepare("SELECT df.*, cp.custom_id, c.name FROM designed_files df JOIN custom_products cp ON df.custom_id = cp.custom_id JOIN catalogue c ON cp.item_id = c.item_id WHERE df.client_id = ? ORDER BY df.created_at DESC");
    $stmt->execute([$client_id]);
    $designed_files = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
if ($show['preview_designs']) {
    $stmt = $pdo->prepare("SELECT df.*, cp.custom_id, c.name FROM designed_files df JOIN custom_products cp ON df.custom_id = cp.custom_id JOIN catalogue c ON cp.item_id = c.item_id WHERE df.client_id = ? ORDER BY df.created_at DESC");
    $stmt->execute([$client_id]);
    $designed_files = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
if ($show['submit_inquiry']) {
    $stmt = $pdo->prepare("SELECT * FROM catalogue ORDER BY name");
    $stmt->execute();
    $catalogue = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt = $pdo->prepare("SELECT i.*, c.name FROM inquiries i JOIN catalogue c ON i.item_id = c.item_id WHERE i.client_id = ? ORDER BY i.created_at DESC");
    $stmt->execute([$client_id]);
    $inquiries = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
if ($show['view_discounts']) {
    $stmt = $pdo->prepare("SELECT * FROM discounts WHERE end_date >= CURDATE() ORDER BY end_date");
    $stmt->execute();
    $discounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
if ($show['place_order']) {
    $stmt = $pdo->prepare("SELECT cp.custom_id, c.name FROM custom_products cp JOIN catalogue c ON cp.item_id = c.item_id WHERE cp.client_id = ?");
    $stmt->execute([$client_id]);
    $custom_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt = $pdo->prepare("SELECT * FROM discounts WHERE end_date >= CURDATE()");
    $stmt->execute();
    $discounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt = $pdo->prepare("SELECT o.*, cp.custom_id, c.name, d.product_id FROM orders o JOIN custom_products cp ON o.custom_id = cp.custom_id JOIN catalogue c ON cp.item_id = c.item_id LEFT JOIN discounts d ON o.discount_id = d.discount_id WHERE o.client_id = ? ORDER BY o.created_at DESC");
    $stmt->execute([$client_id]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
if ($show['view_orders'] || $show['cancel_order']) {
    $stmt = $pdo->prepare("SELECT o.*, cp.custom_id, c.name, d.product_id FROM orders o JOIN custom_products cp ON o.custom_id = cp.custom_id JOIN catalogue c ON cp.item_id = c.item_id LEFT JOIN discounts d ON o.discount_id = d.discount_id WHERE o.client_id = ? ORDER BY o.created_at DESC");
    $stmt->execute([$client_id]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
if ($show['view_history']) {
    $stmt = $pdo->prepare("SELECT o.*, cp.custom_id, c.name, d.product_id FROM orders o JOIN custom_products cp ON o.custom_id = cp.custom_id JOIN catalogue c ON cp.item_id = c.item_id LEFT JOIN discounts d ON o.discount_id = d.discount_id WHERE o.client_id = ? AND o.status IN ('Completed', 'Delivered') ORDER BY o.created_at DESC");
    $stmt->execute([$client_id]);
    $purchase_history = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
if ($show['view_invoices']) {
    $stmt = $pdo->prepare("SELECT i.*, o.order_id, c.name FROM invoices i JOIN orders o ON i.order_id = o.order_id JOIN custom_products cp ON o.custom_id = cp.custom_id JOIN catalogue c ON cp.item_id = c.item_id WHERE o.client_id = ? ORDER BY i.created_at DESC");
    $stmt->execute([$client_id]);
    $invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
if ($show['select_payment']) {
    $stmt = $pdo->prepare("SELECT i.*, o.order_id, c.name FROM invoices i JOIN orders o ON i.order_id = o.order_id JOIN custom_products cp ON o.custom_id = cp.custom_id JOIN catalogue c ON cp.item_id = c.item_id WHERE o.client_id = ? AND i.status = 'Pending' ORDER BY i.created_at DESC");
    $stmt->execute([$client_id]);
    $pending_invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt = $pdo->prepare("SELECT p.*, i.invoice_id, c.name FROM payments p JOIN invoices i ON p.invoice_id = i.invoice_id JOIN orders o ON i.order_id = o.order_id JOIN custom_products cp ON o.custom_id = cp.custom_id JOIN catalogue c ON cp.item_id = c.item_id WHERE o.client_id = ? ORDER BY p.created_at DESC");
    $stmt->execute([$client_id]);
    $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
if ($show['request_return']) {
    $stmt = $pdo->prepare("SELECT o.*, cp.custom_id, c.name FROM orders o JOIN custom_products cp ON o.custom_id = cp.custom_id JOIN catalogue c ON cp.item_id = c.item_id WHERE o.client_id = ? AND o.status = 'Delivered' ORDER BY o.created_at DESC");
    $stmt->execute([$client_id]);
    $delivered_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt = $pdo->prepare("SELECT r.*, o.order_id, c.name FROM returns r JOIN orders o ON r.order_id = o.order_id JOIN custom_products cp ON o.custom_id = cp.custom_id JOIN catalogue c ON cp.item_id = c.item_id WHERE o.client_id = ? ORDER BY r.created_at DESC");
    $stmt->execute([$client_id]);
    $returns = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
if ($show['request_refund']) {
    $stmt = $pdo->prepare("SELECT r.*, o.order_id, c.name FROM returns r JOIN orders o ON r.order_id = o.order_id JOIN custom_products cp ON o.custom_id = cp.custom_id JOIN catalogue c ON cp.item_id = c.item_id WHERE o.client_id = ? AND r.status = 'Approved' ORDER BY r.created_at DESC");
    $stmt->execute([$client_id]);
    $approved_returns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt = $pdo->prepare("SELECT rf.*, r.return_id, c.name FROM refunds rf JOIN returns r ON rf.return_id = r.return_id JOIN orders o ON r.order_id = o.order_id JOIN custom_products cp ON o.custom_id = cp.custom_id JOIN catalogue c ON cp.item_id = c.item_id WHERE o.client_id = ? ORDER BY rf.created_at DESC");
    $stmt->execute([$client_id]);
    $refunds = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
if ($show['provide_feedback']) {
    $stmt = $pdo->prepare("SELECT o.*, cp.custom_id, c.name FROM orders o JOIN custom_products cp ON o.custom_id = cp.custom_id JOIN catalogue c ON cp.item_id = c.item_id WHERE o.client_id = ? ORDER BY o.created_at DESC");
    $stmt->execute([$client_id]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt = $pdo->prepare("SELECT f.*, c.name FROM feedback f LEFT JOIN orders o ON f.order_id = o.order_id LEFT JOIN custom_products cp ON o.custom_id = cp.custom_id LEFT JOIN catalogue c ON cp.item_id = c.item_id WHERE f.client_id = ? ORDER BY f.created_at DESC");
    $stmt->execute([$client_id]);
    $feedbacks = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Company Dashboard | Digiscan</title>
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
                <h3>Client Company Dashboard</h3>
                <p><?php echo $displayName; ?></p>
            </div>
            <div class="sidebar-menu">
                <a href="?view=add_terms" class="menu-item <?php echo $show['add_terms'] ? 'active' : ''; ?>"><i class="fas fa-file-contract"></i> Add Terms</a>
                <a href="?view=view_catalogue" class="menu-item <?php echo $show['view_catalogue'] ? 'active' : ''; ?>"><i class="fas fa-book"></i> View Catalogue</a>
                <a href="?view=customize_product" class="menu-item <?php echo $show['customize_product'] ? 'active' : ''; ?>"><i class="fas fa-paint-brush"></i> Customize Product</a>
                <a href="?view=upload_design" class="menu-item <?php echo $show['upload_design'] ? 'active' : ''; ?>"><i class="fas fa-upload"></i> Upload Design</a>
                <a href="?view=preview_designs" class="menu-item <?php echo $show['preview_designs'] ? 'active' : ''; ?>"><i class="fas fa-eye"></i> Preview Designs</a>
                <a href="?view=submit_inquiry" class="menu-item <?php echo $show['submit_inquiry'] ? 'active' : ''; ?>"><i class="fas fa-question-circle"></i> Submit Inquiry</a>
                <a href="?view=view_discounts" class="menu-item <?php echo $show['view_discounts'] ? 'active' : ''; ?>"><i class="fas fa-tags"></i> View Discounts</a>
                <a href="?view=place_order" class="menu-item <?php echo $show['place_order'] ? 'active' : ''; ?>"><i class="fas fa-shopping-cart"></i> Place Order</a>
                <a href="?view=view_orders" class="menu-item <?php echo $show['view_orders'] ? 'active' : ''; ?>"><i class="fas fa-list"></i> View Orders</a>
                <a href="?view=cancel_order" class="menu-item <?php echo $show['cancel_order'] ? 'active' : ''; ?>"><i class="fas fa-times"></i> Cancel Order</a>
                <a href="?view=view_history" class="menu-item <?php echo $show['view_history'] ? 'active' : ''; ?>"><i class="fas fa-history"></i> Purchase History</a>
                <a href="?view=view_invoices" class="menu-item <?php echo $show['view_invoices'] ? 'active' : ''; ?>"><i class="fas fa-file-invoice"></i> View Invoices</a>
                <a href="?view=select_payment" class="menu-item <?php echo $show['select_payment'] ? 'active' : ''; ?>"><i class="fas fa-credit-card"></i> Select Payment</a>
                <a href="?view=request_return" class="menu-item <?php echo $show['request_return'] ? 'active' : ''; ?>"><i class="fas fa-undo"></i> Request Return</a>
                <a href="?view=request_refund" class="menu-item <?php echo $show['request_refund'] ? 'active' : ''; ?>"><i class="fas fa-money-bill-wave"></i> Request Refund</a>
                <a href="?view=provide_feedback" class="menu-item <?php echo $show['provide_feedback'] ? 'active' : ''; ?>"><i class="fas fa-comment"></i> Provide Feedback</a>
                <a href="client_logout.php" class="menu-item" style="margin-top: 2rem; color: #e74c3c;"><i class="fas fa-sign-out-alt"></i> Logout</a>
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
                <?php if ($show['add_terms']): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Add Terms and Agreements</span>
                        <div class="card-icon bg-primary"><i class="fas fa-file-contract"></i></div>
                    </div>
                    <?php if ($errorMessage): ?>
                        <div class="message error"><?php echo htmlspecialchars($errorMessage); ?></div>
                    <?php endif; ?>
                    <?php if ($successMessage): ?>
                        <div class="message success"><?php echo htmlspecialchars($successMessage); ?></div>
                    <?php endif; ?>
                    <form action="" method="POST" class="form-table">
                        <input type="hidden" name="add_terms" value="1">
                        <table>
                            <tr>
                                <th><label for="title">Title</label></th>
                                <td><input type="text" id="title" name="title" required></td>
                            </tr>
                            <tr>
                                <th><label for="content">Content</label></th>
                                <td><textarea id="content" name="content" required></textarea></td>
                            </tr>
                        </table>
                        <button type="submit" class="btn"><i class="fas fa-save"></i> Add Terms</button>
                    </form>
                    <?php if ($terms): ?>
                    <table class="data-table">
                        <tr>
                            <th>Term ID</th>
                            <th>Title</th>
                            <th>Created At</th>
                        </tr>
                        <?php foreach ($terms as $term): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($term['term_id']); ?></td>
                            <td><?php echo htmlspecialchars($term['title']); ?></td>
                            <td><?php echo htmlspecialchars($term['created_at']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                    <?php else: ?>
                    <p>No terms found.</p>
                    <?php endif; ?>
                </div>
                <?php elseif ($show['view_catalogue']): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Print Material Catalogue</span>
                        <div class="card-icon bg-secondary"><i class="fas fa-book"></i></div>
                    </div>
                    <?php if ($catalogue): ?>
                    <table class="data-table">
                        <tr>
                            <th>Item ID</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Price</th>
                            <th>Category</th>
                        </tr>
                        <?php foreach ($catalogue as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['item_id']); ?></td>
                            <td><?php echo htmlspecialchars($item['name']); ?></td>
                            <td><?php echo htmlspecialchars($item['description']); ?></td>
                            <td>$<?php echo number_format($item['price'], 2); ?></td>
                            <td><?php echo htmlspecialchars($item['category']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                    <?php else: ?>
                    <p>No catalogue items found.</p>
                    <?php endif; ?>
                </div>
                <?php elseif ($show['customize_product']): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Customize Product</span>
                        <div class="card-icon bg-accent"><i class="fas fa-paint-brush"></i></div>
                    </div>
                    <?php if ($errorMessage): ?>
                        <div class="message error"><?php echo htmlspecialchars($errorMessage); ?></div>
                    <?php endif; ?>
                    <?php if ($successMessage): ?>
                        <div class="message success"><?php echo htmlspecialchars($successMessage); ?></div>
                    <?php endif; ?>
                    <form action="" method="POST" class="form-table">
                        <input type="hidden" name="customize_product" value="1">
                        <table>
                            <tr>
                                <th><label for="item_id">Product</label></th>
                                <td>
                                    <select id="item_id" name="item_id" required>
                                        <option value="">Select Product</option>
                                        <?php foreach ($catalogue as $item): ?>
                                        <option value="<?php echo $item['item_id']; ?>"><?php echo htmlspecialchars($item['name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="specifications">Specifications</label></th>
                                <td><textarea id="specifications" name="specifications" required></textarea></td>
                            </tr>
                        </table>
                        <button type="submit" class="btn"><i class="fas fa-save"></i> Customize</button>
                    </form>
                    <?php if ($custom_products): ?>
                    <table class="data-table">
                        <tr>
                            <th>Custom ID</th>
                            <th>Product</th>
                            <th>Specifications</th>
                            <th>Created At</th>
                        </tr>
                        <?php foreach ($custom_products as $product): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($product['custom_id']); ?></td>
                            <td><?php echo htmlspecialchars($product['name']); ?></td>
                            <td><?php echo htmlspecialchars($product['specifications']); ?></td>
                            <td><?php echo htmlspecialchars($product['created_at']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                    <?php else: ?>
                    <p>No customized products found.</p>
                    <?php endif; ?>
                </div>
                <?php elseif ($show['upload_design']): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Upload Designed File</span>
                        <div class="card-icon bg-success"><i class="fas fa-upload"></i></div>
                    </div>
                    <?php if ($errorMessage): ?>
                        <div class="message error"><?php echo htmlspecialchars($errorMessage); ?></div>
                    <?php endif; ?>
                    <?php if ($successMessage): ?>
                        <div class="message success"><?php echo htmlspecialchars($successMessage); ?></div>
                    <?php endif; ?>
                    <form action="" method="POST" enctype="multipart/form-data" class="form-table">
                        <input type="hidden" name="upload_design" value="1">
                        <table>
                            <tr>
                                <th><label for="custom_id">Custom Product</label></th>
                                <td>
                                    <select id="custom_id" name="custom_id" required>
                                        <option value="">Select Product</option>
                                        <?php foreach ($custom_products as $product): ?>
                                        <option value="<?php echo $product['custom_id']; ?>"><?php echo htmlspecialchars($product['name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="design_file">File</label></th>
                                <td><input type="file" id="design_file" name="design_file" accept=".pdf,.jpg,.png" required></td>
                            </tr>
                        </table>
                        <button type="submit" class="btn"><i class="fas fa-upload"></i> Upload</button>
                    </form>
                    <?php if ($designed_files): ?>
                    <table class="data-table">
                        <tr>
                            <th>File ID</th>
                            <th>Product</th>
                            <th>File</th>
                            <th>Created At</th>
                        </tr>
                        <?php foreach ($designed_files as $file): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($file['file_id']); ?></td>
                            <td><?php echo htmlspecialchars($file['name']); ?></td>
                            <td><a href="<?php echo htmlspecialchars($file['file_path']); ?>" class="btn" target="_blank"><i class="fas fa-download"></i> View</a></td>
                            <td><?php echo htmlspecialchars($file['created_at']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                    <?php else: ?>
                    <p>No designed files found.</p>
                    <?php endif; ?>
                </div>
                <?php elseif ($show['preview_designs']): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Preview Print Designs</span>
                        <div class="card-icon bg-primary"><i class="fas fa-eye"></i></div>
                    </div>
                    <?php if ($designed_files): ?>
                    <table class="data-table">
                        <tr>
                            <th>File ID</th>
                            <th>Product</th>
                            <th>Preview</th>
                            <th>Created At</th>
                        </tr>
                        <?php foreach ($designed_files as $file): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($file['file_id']); ?></td>
                            <td><?php echo htmlspecialchars($file['name']); ?></td>
                            <td>
                                <?php if (in_array(pathinfo($file['file_path'], PATHINFO_EXTENSION), ['jpg', 'png'])): ?>
                                <img src="<?php echo htmlspecialchars($file['file_path']); ?>" alt="Design Preview" style="max-width: 100px;">
                                <?php else: ?>
                                <a href="<?php echo htmlspecialchars($file['file_path']); ?>" class="btn" target="_blank"><i class="fas fa-eye"></i> View</a>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($file['created_at']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                    <?php else: ?>
                    <p>No designs found.</p>
                    <?php endif; ?>
                </div>
                <?php elseif ($show['submit_inquiry']): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Submit Product Inquiry</span>
                        <div class="card-icon bg-secondary"><i class="fas fa-question-circle"></i></div>
                    </div>
                    <?php if ($errorMessage): ?>
                        <div class="message error"><?php echo htmlspecialchars($errorMessage); ?></div>
                    <?php endif; ?>
                    <?php if ($successMessage): ?>
                        <div class="message success"><?php echo htmlspecialchars($successMessage); ?></div>
                    <?php endif; ?>
                    <form action="" method="POST" class="form-table">
                        <input type="hidden" name="submit_inquiry" value="1">
                        <table>
                            <tr>
                                <th><label for="item_id">Product</label></th>
                                <td>
                                    <select id="item_id" name="item_id" required>
                                        <option value="">Select Product</option>
                                        <?php foreach ($catalogue as $item): ?>
                                        <option value="<?php echo $item['item_id']; ?>"><?php echo htmlspecialchars($item['name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="message">Message</label></th>
                                <td><textarea id="message" name="message" required></textarea></td>
                            </tr>
                        </table>
                        <button type="submit" class="btn"><i class="fas fa-paper-plane"></i> Submit</button>
                    </form>
                    <?php if ($inquiries): ?>
                    <table class="data-table">
                        <tr>
                            <th>Inquiry ID</th>
                            <th>Product</th>
                            <th>Message</th>
                            <th>Status</th>
                            <th>Created At</th>
                        </tr>
                        <?php foreach ($inquiries as $inquiry): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($inquiry['inquiry_id']); ?></td>
                            <td><?php echo htmlspecialchars($inquiry['name']); ?></td>
                            <td><?php echo htmlspecialchars($inquiry['message']); ?></td>
                            <td><?php echo htmlspecialchars($inquiry['status']); ?></td>
                            <td><?php echo htmlspecialchars($inquiry['created_at']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                    <?php else: ?>
                    <p>No inquiries found.</p>
                    <?php endif; ?>
                </div>
                <?php elseif ($show['view_discounts']): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">View Discounts</span>
                        <div class="card-icon bg-accent"><i class="fas fa-tags"></i></div>
                    </div>
                    <?php if ($discounts): ?>
                    <table class="data-table">
                        <tr>
                            <th>Discount ID</th>
                            <th>Product ID</th>
                            <th>Percentage</th>
                            <th>Valid Until</th>
                        </tr>
                        <?php foreach ($discounts as $discount): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($discount['discount_id']); ?></td>
                            <td><?php echo htmlspecialchars($discount['product_id']); ?></td>
                            <td><?php echo number_format($discount['discount_percentage'], 2); ?>%</td>
                            <td><?php echo htmlspecialchars($discount['end_date']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                    <?php else: ?>
                    <p>No discounts available.</p>
                    <?php endif; ?>
                </div>
                <?php elseif ($show['place_order']): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Place Order</span>
                        <div class="card-icon bg-success"><i class="fas fa-shopping-cart"></i></div>
                    </div>
                    <?php if ($errorMessage): ?>
                        <div class="message error"><?php echo htmlspecialchars($errorMessage); ?></div>
                    <?php endif; ?>
                    <?php if ($successMessage): ?>
                        <div class="message success"><?php echo htmlspecialchars($successMessage); ?></div>
                    <?php endif; ?>
                    <form action="" method="POST" class="form-table">
                        <input type="hidden" name="place_order" value="1">
                        <table>
                            <tr>
                                <th><label for="custom_id">Custom Product</label></th>
                                <td>
                                    <select id="custom_id" name="custom_id" required>
                                        <option value="">Select Product</option>
                                        <?php foreach ($custom_products as $product): ?>
                                        <option value="<?php echo $product['custom_id']; ?>"><?php echo htmlspecialchars($product['name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="quantity">Quantity</label></th>
                                <td><input type="number" id="quantity" name="quantity" min="1" required></td>
                            </tr>
                            <tr>
                                <th><label for="discount_id">Discount Code</label></th>
                                <td>
                                    <select id="discount_id" name="discount_id">
                                        <option value="">No Discount</option>
                                        <?php foreach ($discounts as $discount): ?>
                                        <option value="<?php echo $discount['discount_id']; ?>">
                                            <?php echo htmlspecialchars($discount['product_id']); ?> (<?php echo $discount['discount_percentage']; ?>%)
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                        </table>
                        <button type="submit" class="btn"><i class="fas fa-save"></i> Place Order</button>
                    </form>
                    <?php if ($orders): ?>
                    <table class="data-table">
                        <tr>
                            <th>Order ID</th>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th>Total</th>
                            <th>Discount</th>
                            <th>Status</th>
                            <th>Created At</th>
                        </tr>
                        <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($order['order_id']); ?></td>
                            <td><?php echo htmlspecialchars($order['name']); ?></td>
                            <td><?php echo htmlspecialchars($order['quantity']); ?></td>
                            <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                            <td><?php echo htmlspecialchars($order['product_id'] ?: 'None'); ?></td>
                            <td><?php echo htmlspecialchars($order['status']); ?></td>
                            <td><?php echo htmlspecialchars($order['created_at']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                    <?php else: ?>
                    <p>No orders found.</p>
                    <?php endif; ?>
                </div>
                <?php elseif ($show['view_orders']): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">View Orders</span>
                        <div class="card-icon bg-primary"><i class="fas fa-list"></i></div>
                    </div>
                    <?php if ($orders): ?>
                    <table class="data-table">
                        <tr>
                            <th>Order ID</th>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th>Total</th>
                            <th>Discount</th>
                            <th>Status</th>
                            <th>Created At</th>
                        </tr>
                        <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($order['order_id']); ?></td>
                            <td><?php echo htmlspecialchars($order['name']); ?></td>
                            <td><?php echo htmlspecialchars($order['quantity']); ?></td>
                            <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                            <td><?php echo htmlspecialchars($order['product_id'] ?: 'None'); ?></td>
                            <td><?php echo htmlspecialchars($order['status']); ?></td>
                            <td><?php echo htmlspecialchars($order['created_at']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                    <?php else: ?>
                    <p>No orders found.</p>
                    <?php endif; ?>
                </div>
                <?php elseif ($show['cancel_order']): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Cancel Order</span>
                        <div class="card-icon bg-accent"><i class="fas fa-times"></i></div>
                    </div>
                    <?php if ($errorMessage): ?>
                        <div class="message error"><?php echo htmlspecialchars($errorMessage); ?></div>
                    <?php endif; ?>
                    <?php if ($successMessage): ?>
                        <div class="message success"><?php echo htmlspecialchars($successMessage); ?></div>
                    <?php endif; ?>
                    <?php if ($orders): ?>
                    <table class="data-table">
                        <tr>
                            <th>Order ID</th>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Created At</th>
                            <th>Action</th>
                        </tr>
                        <?php foreach ($orders as $order): ?>
                        <?php if ($order['status'] === 'Pending'): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($order['order_id']); ?></td>
                            <td><?php echo htmlspecialchars($order['name']); ?></td>
                            <td><?php echo htmlspecialchars($order['quantity']); ?></td>
                            <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                            <td><?php echo htmlspecialchars($order['status']); ?></td>
                            <td><?php echo htmlspecialchars($order['created_at']); ?></td>
                            <td>
                                <form action="" method="POST" style="display: inline;">
                                    <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                    <button type="submit" name="cancel_order" class="action-btn cancel-btn"><i class="fas fa-times"></i> Cancel</button>
                                </form>
                            </td>
                        </tr>
                        <?php endif; ?>
                        <?php endforeach; ?>
                    </table>
                    <?php else: ?>
                    <p>No pending orders found.</p>
                    <?php endif; ?>
                </div>
                <?php elseif ($show['view_history']): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Purchase History</span>
                        <div class="card-icon bg-secondary"><i class="fas fa-history"></i></div>
                    </div>
                    <?php if ($purchase_history): ?>
                    <table class="data-table">
                        <tr>
                            <th>Order ID</th>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th>Total</th>
                            <th>Discount ID</th>
                            <th>Status</th>
                            <th>Created At</th>
                        </tr>
                        <?php foreach ($purchase_history as $order): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($order['order_id']); ?></td>
                            <td><?php echo htmlspecialchars($order['name']); ?></td>
                            <td><?php echo htmlspecialchars($order['quantity']); ?></td>
                            <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                            <td><?php echo htmlspecialchars($order['product_id'] ?: 'None'); ?></td>
                            <td><?php echo htmlspecialchars($order['status']); ?></td>
                            <td><?php echo htmlspecialchars($order['created_at']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                    <?php else: ?>
                    <p>No purchase history found.</p>
                    <?php endif; ?>
                </div>
                <?php elseif ($show['view_invoices']): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">View Invoices</span>
                        <div class="card-icon bg-primary"><i class="fas fa-file-invoice"></i></div>
                    </div>
                    <?php if ($invoices): ?>
                    <table class="data-table">
                        <tr>
                            <th>Invoice ID</th>
                            <th>Order ID</th>
                            <th>Product</th>
                            <th>Amount</th>
                            <th>Due Date</th>
                            <th>Status</th>
                            <th>Created At</th>
                        </tr>
                        <?php foreach ($invoices as $invoice): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($invoice['invoice_id']); ?></td>
                            <td><?php echo htmlspecialchars($invoice['order_id']); ?></td>
                            <td><?php echo htmlspecialchars($invoice['name']); ?></td>
                            <td>$<?php echo number_format($invoice['amount'], 2); ?></td>
                            <td><?php echo htmlspecialchars($invoice['due_date']); ?></td>
                            <td><?php echo htmlspecialchars($invoice['status']); ?></td>
                            <td><?php echo htmlspecialchars($invoice['created_at']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                    <?php else: ?>
                    <p>No invoices found.</p>
                    <?php endif; ?>
                </div>
                <?php elseif ($show['select_payment']): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Select Payment Method</span>
                        <div class="card-icon bg-accent"><i class="fas fa-credit-card"></i></div>
                    </div>
                    <?php if ($errorMessage): ?>
                        <div class="message error"><?php echo htmlspecialchars($errorMessage); ?></div>
                    <?php endif; ?>
                    <?php if ($successMessage): ?>
                        <div class="message success"><?php echo htmlspecialchars($successMessage); ?></div>
                    <?php endif; ?>
                    <form action="" method="POST" class="form-table">
                        <input type="hidden" name="select_payment" value="1">
                        <table>
                            <tr>
                                <th><label for="invoice_id">Invoice</label></th>
                                <td>
                                    <select id="invoice_id" name="invoice_id" required>
                                        <option value="">Select Invoice</option>
                                        <?php foreach ($pending_invoices as $invoice): ?>
                                        <option value="<?php echo $invoice['invoice_id']; ?>">ID <?php echo $invoice['invoice_id']; ?> (<?php echo htmlspecialchars($invoice['name']); ?>)</option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="method">Payment Method</label></th>
                                <td>
                                    <select id="method" name="method" required>
                                        <option value="">Select Method</option>
                                        <option value="Cash">Cash</option>
                                        <option value="Card">Card</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="amount">Amount</label></th>
                                <td><input type="number" id="amount" name="amount" step="0.01" min="0" required></td>
                            </tr>
                        </table>
                        <button type="submit" class="btn"><i class="fas fa-save"></i> Record Payment</button>
                    </form>
                    <?php if ($payments): ?>
                    <table class="data-table">
                        <tr>
                            <th>Payment ID</th>
                            <th>Invoice ID</th>
                            <th>Product</th>
                            <th>Method</th>
                            <th>Amount</th>
                            <th>Created At</th>
                        </tr>
                        <?php foreach ($payments as $payment): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($payment['payment_id']); ?></td>
                            <td><?php echo htmlspecialchars($payment['invoice_id']); ?></td>
                            <td><?php echo htmlspecialchars($payment['name']); ?></td>
                            <td><?php echo htmlspecialchars($payment['method']); ?></td>
                            <td>$<?php echo number_format($payment['amount'], 2); ?></td>
                            <td><?php echo htmlspecialchars($payment['created_at']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                    <?php else: ?>
                    <p>No payments found.</p>
                    <?php endif; ?>
                </div>
                <?php elseif ($show['request_return']): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Request Product Return</span>
                        <div class="card-icon bg-success"><i class="fas fa-undo"></i></div>
                    </div>
                    <?php if ($errorMessage): ?>
                        <div class="message error"><?php echo htmlspecialchars($errorMessage); ?></div>
                    <?php endif; ?>
                    <?php if ($successMessage): ?>
                        <div class="message success"><?php echo htmlspecialchars($successMessage); ?></div>
                    <?php endif; ?>
                    <form action="" method="POST" class="form-table">
                        <input type="hidden" name="request_return" value="1">
                        <table>
                            <tr>
                                <th><label for="order_id">Order</label></th>
                                <td>
                                    <select id="order_id" name="order_id" required>
                                        <option value="">Select Order</option>
                                        <?php foreach ($delivered_orders as $order): ?>
                                        <option value="<?php echo $order['order_id']; ?>">ID <?php echo $order['order_id']; ?> (<?php echo htmlspecialchars($order['name']); ?>)</option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
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
                            <th>Order ID</th>
                            <th>Product</th>
                            <th>Reason</th>
                            <th>Status</th>
                            <th>Created At</th>
                        </tr>
                        <?php foreach ($returns as $return): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($return['return_id']); ?></td>
                            <td><?php echo htmlspecialchars($return['order_id']); ?></td>
                            <td><?php echo htmlspecialchars($return['name']); ?></td>
                            <td><?php echo htmlspecialchars($return['reason']); ?></td>
                            <td><?php echo htmlspecialchars($return['status']); ?></td>
                            <td><?php echo htmlspecialchars($return['created_at']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                    <?php else: ?>
                    <p>No returns found.</p>
                    <?php endif; ?>
                </div>
                <?php elseif ($show['request_refund']): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Request Refund</span>
                        <div class="card-icon bg-primary"><i class="fas fa-money-bill-wave"></i></div>
                    </div>
                    <?php if ($errorMessage): ?>
                        <div class="message error"><?php echo htmlspecialchars($errorMessage); ?></div>
                    <?php endif; ?>
                    <?php if ($successMessage): ?>
                        <div class="message success"><?php echo htmlspecialchars($successMessage); ?></div>
                    <?php endif; ?>
                    <form action="" method="POST" class="form-table">
                        <input type="hidden" name="request_refund" value="1">
                        <table>
                            <tr>
                                <th><label for="return_id">Approved Return</label></th>
                                <td>
                                    <select id="return_id" name="return_id" required>
                                        <option value="">Select Return</option>
                                        <?php foreach ($approved_returns as $return): ?>
                                        <option value="<?php echo htmlspecialchars($return['return_id']); ?>">
                                            ID <?php echo htmlspecialchars($return['return_id']); ?> (<?php echo htmlspecialchars($return['name']); ?>)
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="amount">Refund Amount ($)</label></th>
                                <td><input type="number" id="amount" name="amount" step="0.01" min="0" required></td>
                            </tr>
                        </table>
                        <button type="submit" class="btn"><i class="fas fa-save"></i> Request Refund</button>
                    </form>
                    <?php if ($refunds): ?>
                    <table class="data-table">
                        <tr>
                            <th>Refund ID</th>
                            <th>Return ID</th>
                            <th>Product</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Created At</th>
                        </tr>
                        <?php foreach ($refunds as $refund): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($refund['refund_id']); ?></td>
                            <td><?php echo htmlspecialchars($refund['return_id']); ?></td>
                            <td><?php echo htmlspecialchars($refund['name']); ?></td>
                            <td>$<?php echo number_format($refund['amount'], 2); ?></td>
                            <td><?php echo htmlspecialchars($refund['status']); ?></td>
                            <td><?php echo htmlspecialchars($refund['created_at']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                    <?php else: ?>
                    <p>No refunds found.</p>
                    <?php endif; ?>
                </div>
                <?php elseif ($show['provide_feedback']): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Provide Feedback</span>
                        <div class="card-icon bg-success"><i class="fas fa-comment"></i></div>
                    </div>
                    <?php if ($errorMessage): ?>
                        <div class="message error"><?php echo htmlspecialchars($errorMessage); ?></div>
                    <?php endif; ?>
                    <?php if ($successMessage): ?>
                        <div class="message success"><?php echo htmlspecialchars($successMessage); ?></div>
                    <?php endif; ?>
                    <form action="" method="POST" class="form-table">
                        <input type="hidden" name="provide_feedback" value="1">
                        <table>
                            <tr>
                                <th><label for="order_id">Order (Optional)</label></th>
                                <td>
                                    <select id="order_id" name="order_id">
                                        <option value="">Select Order (Optional)</option>
                                        <?php foreach ($orders as $order): ?>
                                        <option value="<?php echo htmlspecialchars($order['order_id']); ?>">
                                            ID <?php echo htmlspecialchars($order['order_id']); ?> (<?php echo htmlspecialchars($order['name']); ?>)
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="rating">Rating (1–5)</label></th>
                                <td>
                                    <select id="rating" name="rating" required>
                                        <option value="">Select Rating</option>
                                        <option value="1">1</option>
                                        <option value="2">2</option>
                                        <option value="3">3</option>
                                        <option value="4">4</option>
                                        <option value="5">5</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="comments">Comments</label></th>
                                <td><textarea id="comments" name="comments" required></textarea></td>
                            </tr>
                        </table>
                        <button type="submit" class="btn"><i class="fas fa-paper-plane"></i> Submit Feedback</button>
                    </form>
                    <?php if ($feedbacks): ?>
                    <table class="data-table">
                        <tr>
                            <th>Feedback ID</th>
                            <th>Product</th>
                            <th>Rating</th>
                            <th>Comments</th>
                            <th>Created At</th>
                        </tr>
                        <?php foreach ($feedbacks as $feedback): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($feedback['feedback_id']); ?></td>
                            <td><?php echo htmlspecialchars($feedback['name'] ?: 'General'); ?></td>
                            <td><?php echo htmlspecialchars($feedback['rating']); ?>/5</td>
                            <td><?php echo htmlspecialchars($feedback['comments']); ?></td>
                            <td><?php echo htmlspecialchars($feedback['created_at']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                    <?php else: ?>
                    <p>No feedback found.</p>
                    <?php endif; ?>
                </div>
                <?php else: ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>