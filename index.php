<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Strix - Home</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <nav class="navbar">
        <div class="logo"><span>Strix</span></div>
        <div class="nav-links">
            <a href="index.php">Home</a>
            <a href="register.php">Register</a>
            <a href="login.php">Login</a>
        </div>
    </nav>

    <?php if (isset($_SESSION['user_id'])): ?>
        <div style="background:#28a745;color:#fff;text-align:center;padding:8px;">
            You are logged in as <strong><?php echo htmlspecialchars($_SESSION['full_name']); ?></strong>
            (<strong><?php echo strtoupper($_SESSION['role']); ?></strong>) -
            <a href="dashboard.php" style="color:#fff;text-decoration:underline;">Go to Dashboard</a> |
            <a href="logout.php" style="color:#fff;text-decoration:underline;">Logout</a>
        </div>
    <?php endif; ?>

    <header class="hero">
        <h1>Welcome to Strix</h1>
        <p>Your trusted company portal to manage employees and administrators.
           Register as an employee and get approved by our admin team to access the dashboard.</p>
        <div class="hero-btns">
            <a href="register.php" class="btn btn-primary">Register as Employee</a>
            <a href="login.php" class="btn btn-outline">Login</a>
        </div>
    </header>

    <section class="features">
        <div class="feature-card">
            <div class="icon">&#128100;</div>
            <h3>Employee Registration</h3>
            <p>Create your account in minutes and wait for admin approval to start working.</p>
        </div>
        <div class="feature-card">
            <div class="icon">&#128273;</div>
            <h3>Secure Login</h3>
            <p>Passwords are hashed and stored safely. Each role sees its own dashboard.</p>
        </div>
        <div class="feature-card">
            <div class="icon">&#129309;</div>
            <h3>Admin Approval</h3>
            <p>Admins review and approve or reject employee registrations from the admin panel.</p>
        </div>
    </section>

    <footer>
        &copy; 2026 Strix. All rights reserved.
    </footer>

</body>
</html>