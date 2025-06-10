<?php
session_start();
require 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['supplier_username']);
    $password = trim($_POST['supplier_password']);

    $stmt = $pdo->prepare("SELECT * FROM suppliers WHERE supplier_username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['supplier_password'])) {
        $_SESSION['supplier_id'] = $user['supplier_id'];
        $_SESSION['supplier_username'] = $user['supplier_username'];
        header("Location: supplier.php?view=update_supplier");
        exit;
    } else {
        $_SESSION['error'] = "Invalid username or password";
        header("Location: supplier_login.php");
        exit;
    }
}
?>
