<?php
session_start();
if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'admin') {
    header('Location: admin_dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | Digiscan</title>
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
        }
        
        .login-container {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 10px;
            width: 100%;
            max-width: 400px;
            padding: 2rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            color: var(--dark-color);
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .login-header h2 {
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }
        
        .login-header p {
            color: #666;
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
        }
        
        .btn:hover {
            background-color: #2980b9;
        }
        
        .error-message {
            color: var(--accent-color);
            margin-bottom: 1rem;
            text-align: center;
        }
        
        .login-footer {
            text-align: center;
            margin-top: 1.5rem;
            color: #666;
        }
        
        .login-footer a {
            color: var(--secondary-color);
            text-decoration: none;
        }
        
        .login-footer a:hover {
            text-decoration: underline;
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
    </style>
</head>
<body>
    <div class="login-container">
        <div class="header">
            <div class="back-button">
                <a href="staffDashboard.html"><button type="button" class="btn btn-back"><i class="fas fa-arrow-left"></i> BACK</button></a>
            </div>
        </div>
        <div class="logo">Digi<span>Scan</span></div>
        <div class="login-header">
            <h2><i class="fas fa-lock"></i> Admin Login</h2>
            <p>Enter your credentials to access the dashboard</p>
        </div>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="error-message">
                <?= $_SESSION['error']; ?>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>
        
        <form action="login.php" method="POST">
            <div class="form-group">
                <label for="username"><i class="fas fa-user"></i> Username</label>
                <input type="text" id="username" name="username" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label for="password"><i class="fas fa-key"></i> Password</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>
            
            <button type="submit" class="btn">Login</button>
        </form>
        
        <div class="login-footer">
            <p>Having trouble? <a href="mailto:support@digiscan.com">Contact support</a></p>
        </div>
    </div>
</body>
</html>