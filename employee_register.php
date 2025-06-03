<?php
session_start();
require 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['FName'])) {
    // Handle registration
    $firstName = trim($_POST['FName']);
    $lastName = trim($_POST['LName']);
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $nic = trim($_POST['EmpNIC']);
    $experience = trim($_POST['years_of_experience']);
    $designation = trim($_POST['designation']);
    $contactNo = trim($_POST['EmpContact_No']);
    $dob = trim($_POST['DOB']);
    $email = trim($_POST['Emp_Email']);
    $dependentName = trim($_POST['Dependent_Name']);
    $dependentNic = trim($_POST['Dependent_NIC']);
    $relationship = trim($_POST['Relationship']);
    $depContactNo = trim($_POST['DeContact_No']);
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    if (empty($firstName) || empty($lastName) || empty($username) || empty($password) || empty($designation)) {
        $_SESSION['error'] = 'Please fill all required fields';
        header('Location: employee_register.php');
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO employees 
            (FName, LName, username, password, EmpNIC, years_of_experience, designation, EmpContact_No, DOB, Emp_Email, Dependent_Name, Dependent_NIC, Relationship, DeContact_No) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $stmt->execute([
            $firstName, $lastName, $username, $hashedPassword, $nic, $experience, $designation,
            $contactNo, $dob, $email, $dependentName, $dependentNic, $relationship, $depContactNo
        ]);

        $_SESSION['success'] = 'Employee registered successfully!';
        header('Location: employee_register.php');
        exit;
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Registration error: ' . $e->getMessage();
        header('Location: employee_register.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Registration | Digiscan</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #e74c3c;
            --light-color: #ecf0f1;
            --dark-color: #2c3e50;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80');
            background-size: cover;
            background-position: center;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            color: var(--light-color);
            padding: 20px;
        }
        
        .registration-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 10px;
            width: 100%;
            max-width: 800px;
            padding: 2rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            color: var(--dark-color);
        }
        
        .registration-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .registration-header h2 {
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }
        
        .registration-header p {
            color: #666;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--primary-color);
        }
        
        .form-control {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
            transition: border 0.3s ease;
        }
        
        .form-control:focus {
            border-color: var(--secondary-color);
            outline: none;
        }
        
        .btn {
            width: 100%;
            padding: 0.8rem;
            background-color: var(--secondary-color);
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            grid-column: span 2;
        }
        
        .btn:hover {
            background-color: #2980b9;
        }
        
        .error-message {
            color: var(--accent-color);
            margin-bottom: 1rem;
            text-align: center;
        }
        
        .success-message {
            color: var(--secondary-color);
            margin-bottom: 1rem;
            text-align: center;
        }
        
        .section-title {
            grid-column: span 2;
            color: var(--primary-color);
            margin: 1rem 0;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #eee;
        }
        
        .logo {
            text-align: center;
            margin-bottom: 1rem;
            font-size: 1.8rem;
            font-weight: bold;
            color: var(--primary-color);
        }
        
        .logo span {
            color: var(--secondary-color);
        }

        .back-button {
            margin-bottom: 2rem;
        }

        .btn {
            display: inline-block;
            padding: 0.8rem 1.5rem;
            background-color: var(--secondary-color);
            color: white;
            text-decoration: none;
            border: none;
            border-radius: 4px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        .btn:hover {
            background-color: #2980b9;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
        }

        .btn-back {
            background-color: var(--primary-color);
        }

        .btn-back:hover {
            background-color: #1a252f;
        }
        
        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .btn, .section-title {
                grid-column: span 1;
            }
        }
    </style>
</head>
<body>
    <div class="registration-container">
        <div class="back-button">
            <a href="staffDashboard.html"><button type="button" class="btn btn-back"><i class="fas fa-arrow-left"></i> BACK</button></a>
        </div>
        <div class="logo">Digi<span>Scan</span></div>

        <h3 class="section-title">Employee Login</h3>
        <form action="employee_login_process.php" method="POST" class="form-grid" style="margin-bottom: 35px;">
            <div class="form-group">
                <label for="login_username"><i class="fas fa-user"></i> Username</label>
                <input type="text" name="login_username" id="login_username" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="login_password"><i class="fas fa-lock"></i> Password</label>
                <input type="password" name="login_password" id="login_password" class="form-control" required>
            </div>
            <button type="submit" class="btn">Login</button>
        </form>

        <div class="registration-header">
            <h2><i class="fas fa-user-plus"></i> Employee Registration</h2>
            <p>Please fill in your details below</p>
        </div>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="error-message">
                <?= $_SESSION['error']; ?>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="success-message">
                <?= $_SESSION['success']; ?>
                <?php unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>
        
        <form action="employee_register.php" method="POST" class="form-grid">
            <h3 class="section-title">Personal Information</h3>
            
            <div class="form-group">
                <label for="FName"><i class="fas fa-user"></i> First Name *</label>
                <input type="text" id="FName" name="FName" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label for="LName"><i class="fas fa-user"></i> Last Name *</label>
                <input type="text" id="LName" name="LName" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="username"><i class="fas fa-user-circle"></i> Username *</label>
                <input type="text" id="username" name="username" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="password"><i class="fas fa-lock"></i> Password *</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label for="EmpNIC"><i class="fas fa-id-card"></i> NIC Number *</label>
                <input type="text" id="EmpNIC" name="EmpNIC" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label for="DOB"><i class="fas fa-birthday-cake"></i> Date of Birth</label>
                <input type="date" id="DOB" name="DOB" class="form-control">
            </div>
            
            <div class="form-group">
                <label for="Emp_Email"><i class="fas fa-envelope"></i> Email</label>
                <input type="email" id="Emp_Email" name="Emp_Email" class="form-control">
            </div>
            
            <div class="form-group">
                <label for="EmpContact_No"><i class="fas fa-phone"></i> Contact Number</label>
                <input type="tel" id="EmpContact_No" name="EmpContact_No" class="form-control">
            </div>
            
            <h3 class="section-title">Employment Details</h3>
            
            <div class="form-group">
                <label for="designation"><i class="fas fa-briefcase"></i> Designation *</label>
                <select id="designation" name="designation" class="form-control" required>
                    <option value="" disabled selected>Select Designation</option>
                    <option value="Employee">Employee</option>
                    <option value="Admin">Admin</option>
                    <option value="CEO">CEO</option>
                    <option value="General">General Manager</option>
                    <option value="Finance">Finance Manager</option>
                    <option value="Sales">Sales & Marketing Manager</option>
                    <option value="Legal">Legal & HR Manager</option>
                    <option value="IT">IT Manager</option>
                    <option value="Production">Production Manager</option>
                    <option value="Graphic Division">Graphic Division Manager</option>
                    <option value="Inventory">Inventory Manager</option>
                    <option value="Production">Production Supervisor</option>
                    <option value="Accountant">Accountant</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="years_of_experience"><i class="fas fa-chart-line"></i> Years of Experience</label>
                <input type="number" id="years_of_experience" name="years_of_experience" class="form-control" min="0" max="50">
            </div>
            
            <h3 class="section-title">Dependent Information</h3>
            
            <div class="form-group">
                <label for="Dependent_Name"><i class="fas fa-user-friends"></i> Dependent Name</label>
                <input type="text" id="Dependent_Name" name="Dependent_Name" class="form-control">
            </div>
            
            <div class="form-group">
                <label for="Dependent_NIC"><i class="fas fa-id-card"></i> Dependent NIC</label>
                <input type="text" id="Dependent_NIC" name="Dependent_NIC" class="form-control">
            </div>
            
            <div class="form-group">
                <label for="Relationship"><i class="fas fa-heart"></i> Relationship</label>
                <select id="Relationship" name="Relationship" class="form-control">
                    <option value="">Select Relationship</option>
                    <option value="Spouse">Spouse</option>
                    <option value="Child">Child</option>
                    <option value="Parent">Parent</option>
                    <option value="Sibling">Sibling</option>
                    <option value="Other">Other</option>
                </select>
            </div>

            <div class="form-group">
                <label for="DeContact_No"><i class="fas fa-phone"></i> Dependant Contact Number</label>
                <input type="tel" id="DeContact_No" name="DeContact_No" class="form-control">
            </div>
            
            <button type="submit" class="btn">Submit Information</button>
        </form>
    </div>
</body>
</html>