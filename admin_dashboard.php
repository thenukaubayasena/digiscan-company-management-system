<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: admin_login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Digiscan</title>
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
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
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
                <h3>Admin Dashboard</h3>
                <!-- <p><?= $_SESSION['full_name'] ?></p> -->
            </div>
            
            <div class="sidebar-menu">
                <a href="#" class="menu-item active">
                    <i class="fas fa-tachometer-alt"></i>
                    View User Accounts
                </a>
                <a href="#" class="menu-item">
                    <i class="fas fa-users"></i>
                    Register Client Companies
                </a>
                <a href="#" class="menu-item">
                    <i class="fas fa-cog"></i>
                    Assign System Roles
                </a>
                <a href="#" class="menu-item">
                    <i class="fas fa-chart-bar"></i>
                    Send Notifications
                </a>
                <a href="#" class="menu-item">
                    <i class="fas fa-bell"></i>
                    Backup Data
                </a>
                <a href="#" class="menu-item">
                    <i class="fas fa-question-circle"></i>
                    Restore Data
                </a>
                <a href="#" class="menu-item">
                    <i class="fas fa-question-circle"></i>
                    Configure System
                </a>
                <a href="#" class="menu-item">
                    <i class="fas fa-question-circle"></i>
                    View Reports
                </a>
                <a href="#" class="menu-item">
                    <i class="fas fa-question-circle"></i>
                    View Orders
                </a>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="top-bar">
                <!-- <h2>Welcome back, <?= $_SESSION['full_name'] ?></h2> -->
                <div class="user-info">
                    <!-- <div class="user-avatar"><?= strtoupper(substr($_SESSION['full_name'], 0, 1)) ?></div> -->
                    <form action="logout.php" method="post">
                        <button type="submit" class="logout-btn">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </button>
                    </form>
                </div>
            </div>
            
            <div class="dashboard-cards">
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Total Users</span>
                        <div class="card-icon bg-primary">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                    <div class="card-value">142</div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Active Sessions</span>
                        <div class="card-icon bg-success">
                            <i class="fas fa-user-check"></i>
                        </div>
                    </div>
                    <div class="card-value">24</div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Pending Requests</span>
                        <div class="card-icon bg-accent">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                    <div class="card-value">8</div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">System Health</span>
                        <div class="card-icon bg-secondary">
                            <i class="fas fa-heartbeat"></i>
                        </div>
                    </div>
                    <div class="card-value">98%</div>
                </div>
            </div>
            
            <div class="recent-activity">
                <h3 class="section-title">Recent Activity</h3>
                
                <div class="activity-item">
                    <div class="activity-icon">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <div class="activity-content">
                        <div class="activity-title">New user registered</div>
                        <div class="activity-time">10 minutes ago</div>
                    </div>
                </div>
                
                <div class="activity-item">
                    <div class="activity-icon">
                        <i class="fas fa-cog"></i>
                    </div>
                    <div class="activity-content">
                        <div class="activity-title">System settings updated</div>
                        <div class="activity-time">25 minutes ago</div>
                    </div>
                </div>
                
                <div class="activity-item">
                    <div class="activity-icon">
                        <i class="fas fa-lock"></i>
                    </div>
                    <div class="activity-content">
                        <div class="activity-title">Password changed</div>
                        <div class="activity-time">1 hour ago</div>
                    </div>
                </div>
                
                <div class="activity-item">
                    <div class="activity-icon">
                        <i class="fas fa-bell"></i>
                    </div>
                    <div class="activity-content">
                        <div class="activity-title">New notification sent</div>
                        <div class="activity-time">2 hours ago</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>