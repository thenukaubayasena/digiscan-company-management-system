<?php
session_start();
require 'db_connection.php';

$displayName = isset($_SESSION['FName'], $_SESSION['LName']) ? $_SESSION['FName'] . ' ' . $_SESSION['LName'] : 'Finance Manager';
$avatar = strtoupper(substr($displayName, 0, 1));

// Initialize message variables
$successMessage = '';
$errorMessage = '';

// Initialize view variables
$showAddTransactions = false;
$showViewPayroll = false;
$showFinancialAudits = false;
$showFinancialReports = false;
$showReceivePayments = false;
$showGenerateInvoices = false;
$showSupplierPayments = false;
$showSupplierInvoices = false;

// Determine which view to show based on GET parameter
if (isset($_GET['view'])) {
    $showAddTransactions = ($_GET['view'] === 'add_transactions');
    $showViewPayroll = ($_GET['view'] === 'view_payroll');
    $showFinancialAudits = ($_GET['view'] === 'financial_audits');
    $showFinancialReports = ($_GET['view'] === 'financial_reports');
    $showReceivePayments = ($_GET['view'] === 'receive_payments');
    $showGenerateInvoices = ($_GET['view'] === 'generate_invoices');
    $showSupplierPayments = ($_GET['view'] === 'supplier_payments');
    $showSupplierInvoices = ($_GET['view'] === 'supplier_invoices');
} else {
    // Default view: show summary cards
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Add Financial Transaction
        if (isset($_POST['add_transaction'])) {
            $type = $_POST['type'];
            $category = $_POST['category'];
            $amount = $_POST['amount'];
            $description = $_POST['description'];
            if (empty($type) || empty($category) || empty($amount) || empty($description)) {
                $errorMessage = "All fields are required.";
                $showAddTransactions = true;
            } elseif (!is_numeric($amount) || $amount <= 0) {
                $errorMessage = "Amount must be a positive number.";
                $showAddTransactions = true;
            } else {
                $stmt = $pdo->prepare("INSERT INTO financial_transactions (type, category, amount, description, created_at) VALUES (?, ?, ?, ?, NOW())");
                $success = $stmt->execute([$type, $category, $amount, $description]);
                if ($success) {
                    $successMessage = "Transaction added successfully!";
                    $showAddTransactions = true;
                } else {
                    $errorMessage = "Error adding transaction.";
                    $showAddTransactions = true;
                }
            }
        }
        // Update Received Payment
        if (isset($_POST['update_payment'])) {
            $invoice_id = $_POST['invoice_id'];
            $status = $_POST['status'];
            $stmt = $pdo->prepare("UPDATE customer_invoices SET status = ? WHERE invoice_id = ?");
            $success = $stmt->execute([$status, $invoice_id]);
            if ($success) {
                $successMessage = "Payment status updated successfully!";
                $showReceivePayments = true;
            } else {
                $errorMessage = "Error updating payment status.";
                $showReceivePayments = true;
            }
        }
        // Generate Invoice
        if (isset($_POST['generate_invoice'])) {
            $client_name = $_POST['client_name'];
            $amount = $_POST['amount'];
            $issue_date = $_POST['issue_date'];
            if (empty($client_name) || empty($amount) || empty($issue_date)) {
                $errorMessage = "All fields are required.";
                $showGenerateInvoices = true;
            } elseif (!is_numeric($amount) || $amount <= 0) {
                $errorMessage = "Amount must be a positive number.";
                $showGenerateInvoices = true;
            } elseif (strtotime($issue_date) > time()) {
                $errorMessage = "Issue date cannot be in the future.";
                $showGenerateInvoices = true;
            } else {
                $stmt = $pdo->prepare("INSERT INTO customer_invoices (client_name, amount, issue_date, status) VALUES (?, ?, ?, 'Pending')");
                $success = $stmt->execute([$client_name, $amount, $issue_date]);
                if ($success) {
                    $successMessage = "Invoice generated successfully!";
                    $showGenerateInvoices = true;
                } else {
                    $errorMessage = "Error generating invoice.";
                    $showGenerateInvoices = true;
                }
            }
        }
        // Add Supplier Payment
        if (isset($_POST['add_supplier_payment'])) {
            $supplier_name = $_POST['supplier_name'];
            $amount = $_POST['amount'];
            $payment_date = $_POST['payment_date'];
            if (empty($supplier_name) || empty($amount) || empty($payment_date)) {
                $errorMessage = "All fields are required.";
                $showSupplierPayments = true;
            } elseif (!is_numeric($amount) || $amount <= 0) {
                $errorMessage = "Amount must be a positive number.";
                $showSupplierPayments = true;
            } elseif (strtotime($payment_date) > time()) {
                $errorMessage = "Payment date cannot be in the future.";
                $showSupplierPayments = true;
            } else {
                $stmt = $pdo->prepare("INSERT INTO supplier_payments (supplier_name, amount, payment_date) VALUES (?, ?, ?)");
                $success = $stmt->execute([$supplier_name, $amount, $payment_date]);
                if ($success) {
                    $successMessage = "Supplier payment added successfully!";
                    $showSupplierPayments = true;
                } else {
                    $errorMessage = "Error adding supplier payment.";
                    $showSupplierPayments = true;
                }
            }
        }
    } catch (PDOException $e) {
        $errorMessage = "Database error: " . $e->getMessage();
        if (isset($_POST['add_transaction'])) $showAddTransactions = true;
        elseif (isset($_POST['update_payment'])) $showReceivePayments = true;
        elseif (isset($_POST['generate_invoice'])) $showGenerateInvoices = true;
        elseif (isset($_POST['add_supplier_payment'])) $showSupplierPayments = true;
    }
}

// Fetch data for views
if ($showAddTransactions) {
    $stmt = $pdo->query("SELECT * FROM financial_transactions ORDER BY created_at DESC");
    $transactions = $stmt->fetchAll();
}
if ($showViewPayroll) {
    $stmt = $pdo->query("SELECT p.payroll_id, p.EMP_ID, p.salary, p.payment_date, e.FName, e.LName 
                         FROM payroll p 
                         JOIN employees e ON p.EMP_ID = e.EMP_ID 
                         ORDER BY p.payment_date DESC");
    $payroll = $stmt->fetchAll();
}
if ($showFinancialAudits) {
    $stmt = $pdo->query("SELECT * FROM audit_logs ORDER BY recorded_at DESC");
    $audits = $stmt->fetchAll();
}
if ($showFinancialReports) {
    $stmt = $pdo->query("SELECT * FROM financial_reports ORDER BY generated_at DESC");
    $reports = $stmt->fetchAll();
}
if ($showReceivePayments) {
    $stmt = $pdo->query("SELECT * FROM customer_invoices WHERE status = 'Pending' ORDER BY issue_date DESC");
    $invoices = $stmt->fetchAll();
}
if ($showGenerateInvoices) {
    $stmt = $pdo->query("SELECT * FROM customer_invoices ORDER BY issue_date DESC");
    $customer_invoices = $stmt->fetchAll();
}
if ($showSupplierPayments) {
    $stmt = $pdo->query("SELECT * FROM supplier_payments ORDER BY payment_date DESC");
    $supplier_payments = $stmt->fetchAll();
}
if ($showSupplierInvoices) {
    $stmt = $pdo->query("SELECT * FROM supplier_invoices ORDER BY issue_date DESC");
    $supplier_invoices = $stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finance Manager Dashboard | Digiscan</title>
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
                <h3>Finance Manager Dashboard</h3>
                <p><?= htmlspecialchars($displayName) ?></p>
            </div>
            <div class="sidebar-menu">
                <a href="?view=add_transactions" class="menu-item <?= $showAddTransactions ? 'active' : '' ?>"><i class="fas fa-tachometer-alt"></i> Add Financial Transactions</a>
                <a href="?view=view_payroll" class="menu-item <?= $showViewPayroll ? 'active' : '' ?>"><i class="fas fa-users"></i> View Payroll</a>
                <a href="?view=financial_audits" class="menu-item <?= $showFinancialAudits ? 'active' : '' ?>"><i class="fas fa-cog"></i> Conduct Financial Audits</a>
                <a href="?view=financial_reports" class="menu-item <?= $showFinancialReports ? 'active' : '' ?>"><i class="fas fa-chart-bar"></i> Generate Financial Reports</a>
                <a href="?view=receive_payments" class="menu-item <?= $showReceivePayments ? 'active' : '' ?>"><i class="fas fa-bell"></i> Update Received Payments</a>
                <a href="?view=generate_invoices" class="menu-item <?= $showGenerateInvoices ? 'active' : '' ?>"><i class="fas fa-file-invoice"></i> Generate Invoices</a>
                <a href="?view=supplier_payments" class="menu-item <?= $showSupplierPayments ? 'active' : '' ?>"><i class="fas fa-hand-holding-usd"></i> Add Supplier Payments</a>
                <a href="?view=supplier_invoices" class="menu-item <?= $showSupplierInvoices ? 'active' : '' ?>"><i class="fas fa-file-invoice-dollar"></i> View Supplier Invoices</a>
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
                <?php if ($showAddTransactions): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Add Financial Transaction</span>
                        <div class="card-icon bg-primary"><i class="fas fa-tachometer-alt"></i></div>
                    </div>
                    <?php if ($errorMessage): ?>
                        <div class="message error"><?= htmlspecialchars($errorMessage) ?></div>
                    <?php endif; ?>
                    <?php if ($successMessage): ?>
                        <div class="message success"><?= htmlspecialchars($successMessage) ?></div>
                    <?php endif; ?>
                    <form action="" method="POST" class="form-table">
                        <input type="hidden" name="add_transaction" value="1">
                        <table>
                            <tr>
                                <th><label for="type">Type</label></th>
                                <td>
                                    <select id="type" name="type" required>
                                        <option value="Income">Income</option>
                                        <option value="Expense">Expense</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="category">Category</label></th>
                                <td><input type="text" id="category" name="category" required></td>
                            </tr>
                            <tr>
                                <th><label for="amount">Amount</label></th>
                                <td><input type="number" id="amount" name="amount" step="0.01" required></td>
                            </tr>
                            <tr>
                                <th><label for="description">Description</label></th>
                                <td><textarea id="description" name="description" required></textarea></td>
                            </tr>
                        </table>
                        <button type="submit" class="btn"><i class="fas fa-save"></i> Add Transaction</button>
                    </form>
                    <?php if ($transactions): ?>
                    <table class="data-table">
                        <tr>
                            <th>Type</th>
                            <th>Category</th>
                            <th>Amount</th>
                            <th>Description</th>
                            <th>Created At</th>
                        </tr>
                        <?php foreach ($transactions as $transaction): ?>
                        <tr>
                            <td><?= htmlspecialchars($transaction['type']) ?></td>
                            <td><?= htmlspecialchars($transaction['category']) ?></td>
                            <td>$<?= number_format($transaction['amount'], 2) ?></td>
                            <td><?= htmlspecialchars($transaction['description']) ?></td>
                            <td><?= htmlspecialchars($transaction['created_at']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                    <?php endif; ?>
                </div>
                <?php elseif ($showViewPayroll && $payroll): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Payroll</span>
                        <div class="card-icon bg-secondary"><i class="fas fa-users"></i></div>
                    </div>
                    <table class="data-table">
                        <tr>
                            <th>Employee</th>
                            <th>Salary</th>
                            <th>Payment Date</th>
                        </tr>
                        <?php foreach ($payroll as $record): ?>
                        <tr>
                            <td><?= htmlspecialchars($record['FName'] . ' ' . $record['LName']) ?></td>
                            <td>$<?= number_format($record['salary'], 2) ?></td>
                            <td><?= htmlspecialchars($record['payment_date']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
                <?php elseif ($showFinancialAudits && $audits): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Financial Audits</span>
                        <div class="card-icon bg-accent"><i class="fas fa-cog"></i></div>
                    </div>
                    <table class="data-table">
                        <tr>
                            <th>Description</th>
                            <th>Recorded At</th>
                        </tr>
                        <?php foreach ($audits as $audit): ?>
                        <tr>
                            <td><?= htmlspecialchars($audit['description']) ?></td>
                            <td><?= htmlspecialchars($audit['recorded_at']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
                <?php elseif ($showFinancialReports && $reports): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Financial Reports</span>
                        <div class="card-icon bg-success"><i class="fas fa-chart-bar"></i></div>
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
                <?php elseif ($showReceivePayments && $invoices): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Update Received Payments</span>
                        <div class="card-icon bg-primary"><i class="fas fa-bell"></i></div>
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
                            <th>Issue Date</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                        <?php foreach ($invoices as $invoice): ?>
                        <tr>
                            <td><?= htmlspecialchars($invoice['client_name']) ?></td>
                            <td>$<?= number_format($invoice['amount'], 2) ?></td>
                            <td><?= htmlspecialchars($invoice['issue_date']) ?></td>
                            <td><?= htmlspecialchars($invoice['status']) ?></td>
                            <td>
                                <form action="" method="POST" style="display: inline;">
                                    <input type="hidden" name="invoice_id" value="<?= $invoice['invoice_id'] ?>">
                                    <select name="status" required>
                                        <option value="Pending">Pending</option>
                                        <option value="Paid">Paid</option>
                                    </select>
                                    <button type="submit" name="update_payment" class="action-btn approve-btn"><i class="fas fa-save"></i> Update</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
                <?php elseif ($showGenerateInvoices): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Generate Invoices</span>
                        <div class="card-icon bg-secondary"><i class="fas fa-file-invoice"></i></div>
                    </div>
                    <?php if ($errorMessage): ?>
                        <div class="message error"><?= htmlspecialchars($errorMessage) ?></div>
                    <?php endif; ?>
                    <?php if ($successMessage): ?>
                        <div class="message success"><?= htmlspecialchars($successMessage) ?></div>
                    <?php endif; ?>
                    <form action="" method="POST" class="form-table">
                        <input type="hidden" name="generate_invoice" value="1">
                        <table>
                            <tr>
                                <th><label for="client_name">Client Name</label></th>
                                <td><input type="text" id="client_name" name="client_name" required></td>
                            </tr>
                            <tr>
                                <th><label for="amount">Amount</label></th>
                                <td><input type="number" id="amount" name="amount" step="0.01" required></td>
                            </tr>
                            <tr>
                                <th><label for="issue_date">Issue Date</label></th>
                                <td><input type="date" id="issue_date" name="issue_date" required></td>
                            </tr>
                        </table>
                        <button type="submit" class="btn"><i class="fas fa-save"></i> Generate Invoice</button>
                    </form>
                    <?php if ($customer_invoices): ?>
                    <table class="data-table">
                        <tr>
                            <th>Client Name</th>
                            <th>Amount</th>
                            <th>Issue Date</th>
                            <th>Status</th>
                        </tr>
                        <?php foreach ($customer_invoices as $invoice): ?>
                        <tr>
                            <td><?= htmlspecialchars($invoice['client_name']) ?></td>
                            <td>$<?= number_format($invoice['amount'], 2) ?></td>
                            <td><?= htmlspecialchars($invoice['issue_date']) ?></td>
                            <td><?= htmlspecialchars($invoice['status']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                    <?php endif; ?>
                </div>
                <?php elseif ($showSupplierPayments): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Add Supplier Payments</span>
                        <div class="card-icon bg-accent"><i class="fas fa-hand-holding-usd"></i></div>
                    </div>
                    <?php if ($errorMessage): ?>
                        <div class="message error"><?= htmlspecialchars($errorMessage) ?></div>
                    <?php endif; ?>
                    <?php if ($successMessage): ?>
                        <div class="message success"><?= htmlspecialchars($successMessage) ?></div>
                    <?php endif; ?>
                    <form action="" method="POST" class="form-table">
                        <input type="hidden" name="add_supplier_payment" value="1">
                        <table>
                            <tr>
                                <th><label for="supplier_name">Supplier Name</label></th>
                                <td><input type="text" id="supplier_name" name="supplier_name" required></td>
                            </tr>
                            <tr>
                                <th><label for="amount">Amount</label></th>
                                <td><input type="number" id="amount" name="amount" step="0.01" required></td>
                            </tr>
                            <tr>
                                <th><label for="payment_date">Payment Date</label></th>
                                <td><input type="date" id="payment_date" name="payment_date" required></td>
                            </tr>
                        </table>
                        <button type="submit" class="btn"><i class="fas fa-save"></i> Add Payment</button>
                    </form>
                    <?php if ($supplier_payments): ?>
                    <table class="data-table">
                        <tr>
                            <th>Supplier Name</th>
                            <th>Amount</th>
                            <th>Payment Date</th>
                        </tr>
                        <?php foreach ($supplier_payments as $payment): ?>
                        <tr>
                            <td><?= htmlspecialchars($payment['supplier_name']) ?></td>
                            <td>$<?= number_format($payment['amount'], 2) ?></td>
                            <td><?= htmlspecialchars($payment['payment_date']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                    <?php endif; ?>
                </div>
                <?php elseif ($showSupplierInvoices && $supplier_invoices): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Supplier Invoices</span>
                        <div class="card-icon bg-primary"><i class="fas fa-file-invoice-dollar"></i></div>
                    </div>
                    <table class="data-table">
                        <tr>
                            <th>Supplier Name</th>
                            <th>Amount</th>
                            <th>Issue Date</th>
                            <th>Status</th>
                        </tr>
                        <?php foreach ($supplier_invoices as $invoice): ?>
                        <tr>
                            <td><?= htmlspecialchars($invoice['supplier_name']) ?></td>
                            <td>$<?= number_format($invoice['amount'], 2) ?></td>
                            <td><?= htmlspecialchars($invoice['issue_date']) ?></td>
                            <td><?= htmlspecialchars($invoice['status']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
                <?php else: ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Total Transactions</span>
                        <div class="card-icon bg-primary"><i class="fas fa-tachometer-alt"></i></div>
                    </div>
                    <div class="card-value"><?php
                        $stmt = $pdo->query("SELECT COUNT(*) FROM financial_transactions");
                        echo $stmt->fetchColumn();
                    ?></div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Pending Customer Invoices</span>
                        <div class="card-icon bg-accent"><i class="fas fa-file-invoice"></i></div>
                    </div>
                    <div class="card-value"><?php
                        $stmt = $pdo->query("SELECT COUNT(*) FROM customer_invoices WHERE status = 'Pending'");
                        echo $stmt->fetchColumn();
                    ?></div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Pending Supplier Invoices</span>
                        <div class="card-icon bg-secondary"><i class="fas fa-file-invoice-dollar"></i></div>
                    </div>
                    <div class="card-value"><?php
                        $stmt = $pdo->query("SELECT COUNT(*) FROM supplier_invoices WHERE status = 'Pending'");
                        echo $stmt->fetchColumn();
                    ?></div>
                </div>
                <?php endif; ?>
            </div>

            <div class="recent-activity">
                <h3 class="section-title">Recent Activity</h3>
                <div class="activity-item">
                    <div class="activity-icon"><i class="fas fa-tachometer-alt"></i></div>
                    <div class="activity-content">
                        <div class="activity-title">New transaction added</div>
                        <div class="activity-time">2 hours ago</div>
                    </div>
                </div>
                <div class="activity-item">
                    <div class="activity-icon"><i class="fas fa-file-invoice"></i></div>
                    <div class="activity-content">
                        <div class="activity-title">New invoice generated</div>
                        <div class="activity-time">Yesterday</div>
                    </div>
                </div>
                <div class="activity-item">
                    <div class="activity-icon"><i class="fas fa-hand-holding-usd"></i></div>
                    <div class="activity-content">
                        <div class="activity-title">Supplier payment recorded</div>
                        <div class="activity-time">Yesterday</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>