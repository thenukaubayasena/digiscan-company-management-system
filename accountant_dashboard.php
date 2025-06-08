<?php
session_start();
require 'db_connection.php';

$displayName = isset($_SESSION['FName'], $_SESSION['LName']) ? $_SESSION['FName'] . ' ' . $_SESSION['LName'] : 'Accountant';
$avatar = strtoupper(substr($displayName, 0, 1));

// Initialize message variables
$successMessage = '';
$errorMessage = '';

// Initialize view variables
$showGenerateInvoices = false;
$showViewPayroll = false;
$showPrepareTaxDocuments = false;
$showCreateFinancialRecords = false;
$showApproveInvoices = false;
$showCreateBudgets = false;
$showVerifyTransactions = false;
$showGenerateAuditReports = false;

// Determine which view to show based on GET parameter
if (isset($_GET['view'])) {
    $showGenerateInvoices = ($_GET['view'] === 'generate_invoices');
    $showViewPayroll = ($_GET['view'] === 'view_payroll');
    $showPrepareTaxDocuments = ($_GET['view'] === 'prepare_tax_documents');
    $showCreateFinancialRecords = ($_GET['view'] === 'create_financial_records');
    $showApproveInvoices = ($_GET['view'] === 'approve_invoices');
    $showCreateBudgets = ($_GET['view'] === 'create_budgets');
    $showVerifyTransactions = ($_GET['view'] === 'verify_transactions');
    $showGenerateAuditReports = ($_GET['view'] === 'generate_audit_reports');
} else {
    // Default view: show summary cards
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Generate Invoices
        if (isset($_POST['generate_invoice'])) {
            $client_name = trim($_POST['client_name']);
            $amount = $_POST['amount'];
            $due_date = $_POST['due_date'];
            if (empty($client_name) || empty($amount) || empty($due_date)) {
                $errorMessage = "All fields are required.";
                $showGenerateInvoices = true;
            } elseif (!is_numeric($amount) || $amount <= 0) {
                $errorMessage = "Amount must be a positive number.";
                $showGenerateInvoices = true;
            } else {
                $stmt = $pdo->prepare("INSERT INTO invoices (client_name, amount, due_date, status, created_at) VALUES (?, ?, ?, 'Pending', NOW())");
                $success = $stmt->execute([$client_name, $amount, $due_date]);
                if ($success) {
                    $successMessage = "Invoice generated successfully!";
                    $showGenerateInvoices = true;
                } else {
                    $errorMessage = "Error generating invoice.";
                    $showGenerateInvoices = true;
                }
            }
        }
        // Prepare Tax Documents
        if (isset($_POST['prepare_tax_document'])) {
            $type = trim($_POST['type']);
            $year = $_POST['year'];
            $file_path = $_POST['file_path']; // Placeholder for file upload
            if (empty($type) || empty($year) || empty($file_path)) {
                $errorMessage = "All fields are required.";
                $showPrepareTaxDocuments = true;
            } elseif (!is_numeric($year) || $year < 2000 || $year > date('Y')) {
                $errorMessage = "Invalid year.";
                $showPrepareTaxDocuments = true;
            } else {
                $stmt = $pdo->prepare("INSERT INTO tax_documents (type, year, file_path, created_at) VALUES (?, ?, ?, NOW())");
                $success = $stmt->execute([$type, $year, $file_path]);
                if ($success) {
                    $successMessage = "Tax document prepared successfully!";
                    $showPrepareTaxDocuments = true;
                } else {
                    $errorMessage = "Error preparing tax document.";
                    $showPrepareTaxDocuments = true;
                }
            }
        }
        // Create Financial Records
        if (isset($_POST['create_financial_record'])) {
            $type = $_POST['type'];
            $amount = $_POST['amount'];
            $category = $_POST['category'];
            $description = trim($_POST['description']);
            if (empty($type) || empty($amount) || empty($category) || empty($description)) {
                $errorMessage = "All fields are required.";
                $showCreateFinancialRecords = true;
            } elseif (!is_numeric($amount) || $amount <= 0) {
                $errorMessage = "Amount must be a positive number.";
                $showCreateFinancialRecords = true;
            } else {
                $stmt = $pdo->prepare("INSERT INTO financial_transactions (type, amount, category, description, created_at) VALUES (?, ?, ?, ?, NOW())");
                $success = $stmt->execute([$type, $amount, $category, $description]);
                if ($success) {
                    $successMessage = "Financial record created successfully!";
                    $showCreateFinancialRecords = true;
                } else {
                    $errorMessage = "Error creating financial record.";
                    $showCreateFinancialRecords = true;
                }
            }
        }
        // Approve/Reject Invoices
        if (isset($_POST['approve_invoice']) || isset($_POST['reject_invoice'])) {
            $invoice_id = $_POST['invoice_id'];
            $status = isset($_POST['approve_invoice']) ? 'Approved' : 'Rejected';
            $stmt = $pdo->prepare("UPDATE invoices SET status = ? WHERE invoice_id = ?");
            $success = $stmt->execute([$status, $invoice_id]);
            if ($success) {
                $successMessage = "Invoice $status successfully!";
                $showApproveInvoices = true;
            } else {
                $errorMessage = "Error updating invoice.";
                $showApproveInvoices = true;
            }
        }
        // Create Budgets
        if (isset($_POST['create_budget'])) {
            $department = trim($_POST['department']);
            $amount = $_POST['amount'];
            $period = $_POST['period'];
            if (empty($department) || empty($amount) || empty($period)) {
                $errorMessage = "All fields are required.";
                $showCreateBudgets = true;
            } elseif (!is_numeric($amount) || $amount <= 0) {
                $errorMessage = "Amount must be a positive number.";
                $showCreateBudgets = true;
            } else {
                $stmt = $pdo->prepare("INSERT INTO budgets (department, amount, period, created_at) VALUES (?, ?, ?, NOW())");
                $success = $stmt->execute([$department, $amount, $period]);
                if ($success) {
                    $successMessage = "Budget created successfully!";
                    $showCreateBudgets = true;
                } else {
                    $errorMessage = "Error creating budget.";
                    $showCreateBudgets = true;
                }
            }
        }
        // Verify/Reject Transactions
        if (isset($_POST['verify_transaction']) || isset($_POST['reject_transaction'])) {
            $transaction_id = $_POST['transaction_id'];
            $status = isset($_POST['verify_transaction']) ? 'Verified' : 'Rejected';
            $stmt = $pdo->prepare("UPDATE transactions SET status = ? WHERE transaction_id = ?");
            $success = $stmt->execute([$status, $transaction_id]);
            if ($success) {
                $successMessage = "Transaction $status successfully!";
                $showVerifyTransactions = true;
            } else {
                $errorMessage = "Error updating transaction.";
                $showVerifyTransactions = true;
            }
        }
        // Generate Audit Reports
        if (isset($_POST['generate_audit'])) {
            $title = trim($_POST['description']);
            $file_path = $_POST['file_path']; // Placeholder for file upload
            if (empty($title) || empty($file_path)) {
                $errorMessage = "All fields are required.";
                $showGenerateAuditReports = true;
            } else {
                $stmt = $pdo->prepare("INSERT INTO audit_logs (description, file_path, recorded_at) VALUES (?, ?, NOW())");
                $success = $stmt->execute([$title, $file_path]);
                if ($success) {
                    $successMessage = "Audit report generated successfully!";
                    $showGenerateAuditReports = true;
                } else {
                    $errorMessage = "Error generating audit report.";
                    $showGenerateAuditReports = true;
                }
            }
        }
    } catch (PDOException $e) {
        $errorMessage = "Database error: " . htmlspecialchars($e->getMessage());
        if (isset($_POST['generate_invoice'])) $showGenerateInvoices = true;
        elseif (isset($_POST['prepare_tax_document'])) $showPrepareTaxDocuments = true;
        elseif (isset($_POST['create_financial_record'])) $showCreateFinancialRecords = true;
        elseif (isset($_POST['approve_invoice']) || isset($_POST['reject_invoice'])) $showApproveInvoices = true;
        elseif (isset($_POST['create_budget'])) $showCreateBudgets = true;
        elseif (isset($_PO['verify_transaction']) || isset($_POST['reject_transaction'])) $showVerifyTransactions = true;
        elseif (isset($_POST['generate_audit'])) $showGenerateAuditReports = true;
    }
}

// Fetch data for views
if ($showGenerateInvoices) {
    $stmt = $pdo->query("SELECT * FROM invoices ORDER BY created_at DESC");
    $invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
if ($showViewPayroll) {
    $stmt = $pdo->query("SELECT p.payroll_id, p.salary, p.payment_date, p.created_at, e.FName, e.LName 
                         FROM payroll p 
                         JOIN employees e ON p.EMP_ID = e.EMP_ID 
                         ORDER BY p.created_at DESC");
    $payroll = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
if ($showPrepareTaxDocuments) {
    $stmt = $pdo->query("SELECT * FROM tax_documents ORDER BY created_at DESC");
    $tax_documents = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
if ($showCreateFinancialRecords) {
    $stmt = $pdo->query("SELECT * FROM financial_transactions ORDER BY created_at DESC");
    $financial_records = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
if ($showApproveInvoices) {
    $stmt = $pdo->query("SELECT * FROM invoices WHERE status = 'Pending' ORDER BY created_at DESC");
    $pending_invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
if ($showCreateBudgets) {
    $stmt = $pdo->query("SELECT * FROM budgets ORDER BY created_at DESC");
    $budgets = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
if ($showVerifyTransactions) {
    $stmt = $pdo->query("SELECT * FROM transactions WHERE status = 'Unverified' ORDER BY submitted_at DESC");
    $unverified_transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
if ($showGenerateAuditReports) {
    $stmt = $pdo->query("SELECT * FROM audit_logs ORDER BY recorded_at DESC");
    $audit_reports = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accountant Dashboard | Digiscan</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #e74c3c;
            --light-color: #ecf0f1;
            --dark-color: #333333;
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
                <h3>Accountant Dashboard</h3>
                <p><?php echo htmlspecialchars($displayName); ?></p>
            </div>
            <div class="sidebar-menu">
                <a href="?view=generate_invoices" class="menu-item <?php echo $showGenerateInvoices ? 'active' : ''; ?>"><i class="fas fa-file-invoice-dollar"></i> Generate Invoices</a>
                <a href="?view=view_payroll" class="menu-item <?php echo $showViewPayroll ? 'active' : ''; ?>"><i class="fas fa-money-check-alt"></i> View Payroll</a>
                <a href="?view=prepare_tax_documents" class="menu-item <?php echo $showPrepareTaxDocuments ? 'active' : ''; ?>"><i class="fas fa-file-alt"></i> Prepare Tax Documents</a>
                <a href="?view=create_financial_records" class="menu-item <?php echo $showCreateFinancialRecords ? 'active' : ''; ?>"><i class="fas fa-book"></i> Create Financial Records</a>
                <a href="?view=approve_invoices" class="menu-item <?php echo $showApproveInvoices ? 'active' : ''; ?>"><i class="fas fa-file-signature"></i> Approve Invoices</a>
                <a href="?view=create_budgets" class="menu-item <?php echo $showCreateBudgets ? 'active' : ''; ?>"><i class="fas fa-wallet"></i> Create Budgets</a>
                <a href="?view=verify_transactions" class="menu-item <?php echo $showVerifyTransactions ? 'active' : ''; ?>"><i class="fas fa-receipt"></i> Verify Transactions</a>
                <a href="?view=generate_audit_reports" class="menu-item <?php echo $showGenerateAuditReports ? 'active' : ''; ?>"><i class="fas fa-balance-scale"></i> Generate Audit Reports</a>
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
                <?php if ($showGenerateInvoices): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Generate Invoices</span>
                        <div class="card-icon bg-primary"><i class="fas fa-file-invoice-dollar"></i></div>
                    </div>
                    <?php if ($errorMessage): ?>
                        <div class="message error-message"><?php echo htmlspecialchars($errorMessage); ?></div>
                    <?php endif; ?>
                    <?php if ($successMessage): ?>
                        <div class="message success"><?php echo htmlspecialchars($successMessage); ?></div>
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
                                <td><input type="number" id="amount" name="amount" step="0.01" min="0" required></td>
                            </tr>
                            <tr>
                                <th><label for="due_date">Due Date</label></th>
                                <td><input type="date" id="due_date" name="due_date" required></td>
                            </tr>
                        </table>
                        <button type="submit" class="btn"><i class="fas fa-save"></i> Generate Invoice</button>
                    </form>
                    <?php if ($invoices): ?>
                    <table class="data-table">
                        <tr>
                            <th>Invoice ID</th>
                            <th>Client Name</th>
                            <th>Amount</th>
                            <th>Due Date</th>
                            <th>Status</th>
                            <th>Created At</th>
                        </tr>
                        <?php foreach ($invoices as $invoice): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($invoice['invoice_id']); ?></td>
                            <td><?php echo htmlspecialchars($invoice['client_name']); ?></td>
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
                <?php elseif ($showViewPayroll): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">View Payroll</span>
                        <div class="card-icon bg-secondary"><i class="fas fa-money-check-alt"></i></div>
                    </div>
                    <?php if ($payroll): ?>
                    <table class="data-table">
                        <tr>
                            <th>Payroll ID</th>
                            <th>Employee</th>
                            <th>Salary</th>
                            <th>Pay Date</th>
                            <th>Created At</th>
                        </tr>
                        <?php foreach ($payroll as $record): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($record['payroll_id']); ?></td>
                            <td><?php echo htmlspecialchars($record['FName'] . ' ' . $record['LName']); ?></td>
                            <td>$<?php echo number_format($record['salary'], 2); ?></td>
                            <td><?php echo htmlspecialchars($record['payment_date']); ?></td>
                            <td><?php echo htmlspecialchars($record['created_at']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                    <?php else: ?>
                    <p>No payroll records found.</p>
                    <?php endif; ?>
                </div>
                <?php elseif ($showPrepareTaxDocuments): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Prepare Tax Documents</span>
                        <div class="card-icon bg-accent"><i class="fas fa-file-alt"></i></div>
                    </div>
                    <?php if ($errorMessage): ?>
                        <div class="message error-message"><?php echo htmlspecialchars($errorMessage); ?></div>
                    <?php endif; ?>
                    <?php if ($successMessage): ?>
                        <div class="message success"><?php echo htmlspecialchars($successMessage); ?></div>
                    <?php endif; ?>
                    <form action="" method="POST" class="form-table">
                        <input type="hidden" name="prepare_tax_document" value="1">
                        <table>
                            <tr>
                                <th><label for="type">Document Type</label></th>
                                <td><input type="text" id="type" name="type" required></td>
                            </tr>
                            <tr>
                                <th><label for="year">Year</label></th>
                                <td><input type="number" id="year" name="year" min="2000" max="<?php echo date('Y'); ?>" required></td>
                            </tr>
                            <tr>
                                <th><label for="file_path">File Path</label></th>
                                <td><input type="text" id="file_path" name="file_path" placeholder="Enter file URL" required></td>
                            </tr>
                        </table>
                        <button type="submit" class="btn"><i class="fas fa-save"></i> Prepare Document</button>
                    </form>
                    <?php if ($tax_documents): ?>
                    <table class="data-table">
                        <tr>
                            <th>Document ID</th>
                            <th>Type</th>
                            <th>Year</th>
                            <th>File</th>
                            <th>Created At</th>
                        </tr>
                        <?php foreach ($tax_documents as $document): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($document['document_id']); ?></td>
                            <td><?php echo htmlspecialchars($document['type']); ?></td>
                            <td><?php echo htmlspecialchars($document['year']); ?></td>
                            <td><a href="<?php echo htmlspecialchars($document['file_path']); ?>" class="btn"><i class="fas fa-download"></i> View</a></td>
                            <td><?php echo htmlspecialchars($document['created_at']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                    <?php else: ?>
                    <p>No tax documents found.</p>
                    <?php endif; ?>
                </div>
                <?php elseif ($showCreateFinancialRecords): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Create Financial Records</span>
                        <div class="card-icon bg-success"><i class="fas fa-book"></i></div>
                    </div>
                    <?php if ($errorMessage): ?>
                        <div class="message error-message"><?php echo htmlspecialchars($errorMessage); ?></div>
                    <?php endif; ?>
                    <?php if ($successMessage): ?>
                        <div class="message success"><?php echo htmlspecialchars($successMessage); ?></div>
                    <?php endif; ?>
                    <form action="" method="POST" class="form-table">
                        <input type="hidden" name="create_financial_record" value="1">
                        <table>
                            <tr>
                                <th><label for="type">Type</label></th>
                                <td>
                                    <select id="type" name="type" required>
                                        <option value="">Select Type</option>
                                        <option value="Income">Income</option>
                                        <option value="Expense">Expense</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="amount">Amount</label></th>
                                <td><input type="number" id="amount" name="amount" step="0.01" min="0" required></td>
                            </tr>
                            <tr>
                                <th><label for="category">Category</label></th>
                                <td><textarea id="category" name="category" required></textarea></td>
                            </tr>
                            <tr>
                                <th><label for="description">Description</label></th>
                                <td><textarea id="description" name="description" required></textarea></td>
                            </tr>
                        </table>
                        <button type="submit" class="btn"><i class="fas fa-save"></i> Create Record</button>
                    </form>
                    <?php if ($financial_records): ?>
                    <table class="data-table">
                        <tr>
                            <th>Record ID</th>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Date</th>
                            <th>Description</th>
                            <th>Created At</th>
                        </tr>
                        <?php foreach ($financial_records as $record): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($record['transaction_id']); ?></td>
                            <td><?php echo htmlspecialchars($record['type']); ?></td>
                            <td>$<?php echo number_format($record['amount'], 2); ?></td>
                            <td><?php echo htmlspecialchars($record['category']); ?></td>
                            <td><?php echo htmlspecialchars($record['description']); ?></td>
                            <td><?php echo htmlspecialchars($record['created_at']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                    <?php else: ?>
                    <p>No financial records found.</p>
                    <?php endif; ?>
                </div>
                <?php elseif ($showApproveInvoices): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Approve Invoices</span>
                        <div class="card-icon bg-primary"><i class="fas fa-file-signature"></i></div>
                    </div>
                    <?php if ($errorMessage): ?>
                        <div class="message error-message"><?php echo htmlspecialchars($errorMessage); ?></div>
                    <?php endif; ?>
                    <?php if ($successMessage): ?>
                        <div class="message success"><?php echo htmlspecialchars($successMessage); ?></div>
                    <?php endif; ?>
                    <?php if ($pending_invoices): ?>
                    <table class="data-table">
                        <tr>
                            <th>Invoice ID</th>
                            <th>Client Name</th>
                            <th>Amount</th>
                            <th>Due Date</th>
                            <th>Status</th>
                            <th>Created At</th>
                            <th>Action</th>
                        </tr>
                        <?php foreach ($pending_invoices as $invoice): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($invoice['invoice_id']); ?></td>
                            <td><?php echo htmlspecialchars($invoice['client_name']); ?></td>
                            <td>$<?php echo number_format($invoice['amount'], 2); ?></td>
                            <td><?php echo htmlspecialchars($invoice['due_date']); ?></td>
                            <td><?php echo htmlspecialchars($invoice['status']); ?></td>
                            <td><?php echo htmlspecialchars($invoice['created_at']); ?></td>
                            <td>
                                <form action="" method="POST" style="display: inline;">
                                    <input type="hidden" name="invoice_id" value="<?php echo $invoice['invoice_id']; ?>">
                                    <button type="submit" name="approve_invoice" class="action-btn approve-btn"><i class="fas fa-check"></i> Approve</button>
                                    <button type="submit" name="reject_invoice" class="action-btn reject-btn"><i class="fas fa-times"></i> Reject</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                    <?php else: ?>
                    <p>No pending invoices found.</p>
                    <?php endif; ?>
                </div>
                <?php elseif ($showCreateBudgets): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Create Budgets</span>
                        <div class="card-icon bg-secondary"><i class="fas fa-wallet"></i></div>
                    </div>
                    <?php if ($errorMessage): ?>
                        <div class="message error-message"><?php echo htmlspecialchars($errorMessage); ?></div>
                    <?php endif; ?>
                    <?php if ($successMessage): ?>
                        <div class="message success"><?php echo htmlspecialchars($successMessage); ?></div>
                    <?php endif; ?>
                    <form action="" method="POST" class="form-table">
                        <input type="hidden" name="create_budget" value="1">
                        <table>
                            <tr>
                                <th><label for="department">Department</label></th>
                                <td><input type="text" id="department" name="department" required></td>
                            </tr>
                            <tr>
                                <th><label for="amount">Amount</label></th>
                                <td><input type="number" id="amount" name="amount" step="0.01" min="0" required></td>
                            </tr>
                            <tr>
                                <th><label for="period">Period</label></th>
                                <td><input type="text" id="period" name="period" placeholder="e.g., Q1 2025" required></td>
                            </tr>
                        </table>
                        <button type="submit" class="btn"><i class="fas fa-save"></i> Create Budget</button>
                    </form>
                    <?php if ($budgets): ?>
                    <table class="data-table">
                        <tr>
                            <th>Budget ID</th>
                            <th>Department</th>
                            <th>Amount</th>
                            <th>Period</th>
                            <th>Created At</th>
                        </tr>
                        <?php foreach ($budgets as $budget): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($budget['budget_id']); ?></td>
                            <td><?php echo htmlspecialchars($budget['department']); ?></td>
                            <td>$<?php echo number_format($budget['amount'], 2); ?></td>
                            <td><?php echo htmlspecialchars($budget['period']); ?></td>
                            <td><?php echo htmlspecialchars($budget['created_at']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                    <?php else: ?>
                    <p>No budgets found.</p>
                    <?php endif; ?>
                </div>
                <?php elseif ($showVerifyTransactions): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Verify Transactions</span>
                        <div class="card-icon bg-accent"><i class="fas fa-receipt"></i></div>
                    </div>
                    <?php if ($errorMessage): ?>
                        <div class="message error-message"><?php echo htmlspecialchars($errorMessage); ?></div>
                    <?php endif; ?>
                    <?php if ($successMessage): ?>
                        <div class="message success"><?php echo htmlspecialchars($successMessage); ?></div>
                    <?php endif; ?>
                    <?php if ($unverified_transactions): ?>
                    <table class="data-table">
                        <tr>
                            <th>Transaction ID</th>
                            <th>Amount</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th>Submitted At</th>
                            <th>Action</th>
                        </tr>
                        <?php foreach ($unverified_transactions as $transaction): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($transaction['transaction_id']); ?></td>
                            <td>$<?php echo number_format($transaction['amount'], 2); ?></td>
                            <td><?php echo htmlspecialchars($transaction['description']); ?></td>
                            <td><?php echo htmlspecialchars($transaction['status']); ?></td>
                            <td><?php echo htmlspecialchars($transaction['submitted_at']); ?></td>
                            <td>
                                <form action="" method="POST" style="display: inline;">
                                    <input type="hidden" name="transaction_id" value="<?php echo $transaction['transaction_id']; ?>">
                                    <button type="submit" name="verify_transaction" class="action-btn approve-btn"><i class="fas fa-check"></i> Verify</button>
                                    <button type="submit" name="reject_transaction" class="action-btn reject-btn"><i class="fas fa-times"></i> Reject</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                    <?php else: ?>
                    <p>No unverified transactions found.</p>
                    <?php endif; ?>
                </div>
                <?php elseif ($showGenerateAuditReports): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Generate Audit Reports</span>
                        <div class="card-icon bg-primary"><i class="fas fa-balance-scale"></i></div>
                    </div>
                    <?php if ($errorMessage): ?>
                        <div class="message error-message"><?php echo htmlspecialchars($errorMessage); ?></div>
                    <?php endif; ?>
                    <?php if ($successMessage): ?>
                        <div class="message success"><?php echo htmlspecialchars($successMessage); ?></div>
                    <?php endif; ?>
                    <form action="" method="POST" class="form-table">
                        <input type="hidden" name="generate_audit" value="1">
                        <table>
                            <tr>
                                <th><label for="description">Report Description</label></th>
                                <td><input type="text" id="description" name="description" required></td>
                            </tr>
                            <tr>
                                <th><label for="file_path">File Path</label></th>
                                <td><input type="text" id="file_path" name="file_path" placeholder="Enter file URL" required></td>
                            </tr>
                        </table>
                        <button type="submit" class="btn"><i class="fas fa-save"></i> Generate Report</button>
                    </form>
                    <?php if ($audit_reports): ?>
                    <table class="data-table">
                        <tr>
                            <th>Report ID</th>
                            <th>Description</th>
                            <th>File</th>
                            <th>Recorded At</th>
                        </tr>
                        <?php foreach ($audit_reports as $report): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($report['audit_id']); ?></td>
                            <td><?php echo htmlspecialchars($report['description']); ?></td>
                            <td><a href="<?php echo htmlspecialchars($report['file_path']); ?>" class="btn"><i class="fas fa-download"></i> View</a></td>
                            <td><?php echo htmlspecialchars($report['recorded_at']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                    <?php else: ?>
                    <p>No audit reports found.</p>
                    <?php endif; ?>
                </div>
                <?php else: ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Total Invoices</span>
                        <div class="card-icon bg-primary"><i class="fas fa-file-invoice-dollar"></i></div>
                    </div>
                    <div class="card-value"><?php
                        $stmt = $pdo->query("SELECT COUNT(*) FROM invoices");
                        echo $stmt->fetchColumn();
                    ?></div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Pending Invoices</span>
                        <div class="card-icon bg-accent"><i class="fas fa-file-signature"></i></div>
                    </div>
                    <div class="card-value"><?php
                        $stmt = $pdo->query("SELECT COUNT(*) FROM invoices WHERE status = 'Pending'");
                        echo $stmt->fetchColumn();
                    ?></div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Unverified Transactions</span>
                        <div class="card-icon bg-secondary"><i class="fas fa-receipt"></i></div>
                    </div>
                    <div class="card-value"><?php
                        $stmt = $pdo->query("SELECT COUNT(*) FROM transactions WHERE status = 'Unverified'");
                        echo $stmt->fetchColumn();
                    ?></div>
                </div>
                <?php endif; ?>
            </div>

            <div class="recent-activity">
                <h3 class="section-title">Recent Activity</h3>
                <div class="activity-item">
                    <div class="activity-icon"><i class="fas fa-file-invoice-dollar"></i></div>
                    <div class="activity-content">
                        <div class="activity-title">New invoice generated</div>
                        <div class="activity-time">2 hours ago</div>
                    </div>
                </div>
                <div class="activity-item">
                    <div class="activity-icon"><i class="fas fa-file-signature"></i></div>
                    <div class="activity-content">
                        <div class="activity-title">Invoice approved</div>
                        <div class="activity-time">Yesterday</div>
                    </div>
                </div>
                <div class="activity-item">
                    <div class="activity-icon"><i class="fas fa-receipt"></i></div>
                    <div class="activity-content">
                        <div class="activity-title">Transaction verified</div>
                        <div class="activity-time">Yesterday</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>