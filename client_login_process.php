<?php
session_start();
require 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['client_username']);
    $password = trim($_POST['client_password']);

    $stmt = $pdo->prepare("SELECT * FROM clients WHERE client_username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['client_password'])) {
        $_SESSION['client_id'] = $user['client_id'];
        $_SESSION['client_username'] = $user['client_username'];
        header("Location: client_company.php?view=add_terms");
        exit;
    } else {
        $_SESSION['error'] = "Invalid username or password";
        header("Location: client_login.php");
        exit;
    }
}
?>
