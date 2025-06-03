<?php
session_start();
require 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['login_username']);
    $password = trim($_POST['login_password']);

    $stmt = $pdo->prepare("SELECT * FROM employees WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['employee_id'] = $user['EMP_ID'];
        $_SESSION['employee_name'] = $user['FName'] . ' ' . $user['LName'];
        header("Location: employee_dashboard.php");
        exit;
    } else {
        $_SESSION['error'] = "Invalid username or password";
        header("Location: employee_register.php");
        exit;
    }
}
?>
