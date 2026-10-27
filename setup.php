<?php
// Run this ONCE to create the database, table, and default admin account.
// Recommended: delete this file after running.

require __DIR__ . '/config.php';

$conn = db_connect();

// Create database
$conn->query("CREATE DATABASE IF NOT EXISTS " . DB_NAME);
$conn->select_db(DB_NAME);

// Create users table
$sql = "CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL UNIQUE,
  phone VARCHAR(20) NOT NULL,
  department VARCHAR(50) NOT NULL,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin','employee') NOT NULL DEFAULT 'employee',
  status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
$conn->query($sql);

// Create default admin account
$email = 'admin@company.com';
$check = $conn->query("SELECT id FROM users WHERE email = '$email'");
if ($check && $check->num_rows === 0) {
    $pass_hash = password_hash('admin123', PASSWORD_BCRYPT);
    $conn->query("INSERT INTO users (full_name, email, phone, department, password, role, status)
                  VALUES ('Company Admin', '$email', '0000000000', 'Administration', '$pass_hash', 'admin', 'approved')");
}

echo "Setup completed successfully!<br>";
echo "Default Admin Login -> Email: admin@company.com&nbsp;&nbsp;Password: admin123<br>";
echo '<a href="index.php">Go to Home Page</a>';