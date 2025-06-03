<?php
session_start();

// 1. Connect to the database
$host = "localhost";
$db = "digiscan_db";  // replace with your database name
$user = "root";       // default for XAMPP
$pass = "";           // default for XAMPP

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// 2. Get form input
$username = $_POST['username'];
$password = $_POST['password'];

// 3. Query the user table
$sql = "SELECT * FROM users WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

// 4. Check credentials
if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();

    // If using plaintext passwords (NOT recommended in production)
    if ($user['password'] === $password) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role']; // e.g., admin

        header("Location: admin_dashboard.php");
        exit;
    } else {
        $_SESSION['error'] = "Invalid username or password";
    }
} else {
    $_SESSION['error'] = "Invalid username or password";
}

header("Location: admin_login.php");
exit;
