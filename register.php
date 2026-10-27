<?php
require __DIR__ . '/config.php';
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name   = trim($_POST['full_name'] ?? '');
    $email       = trim($_POST['email'] ?? '');
    $phone       = trim($_POST['phone'] ?? '');
    $department  = trim($_POST['department'] ?? '');
    $password    = $_POST['password'] ?? '';
    $confirm     = $_POST['confirm'] ?? '';

    if ($full_name === '' || strlen($full_name) < 3) $errors[] = 'Full name must be at least 3 characters.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter a valid email address.';
    if (!preg_match('/^[0-9+\- ]{10,15}$/', $phone)) $errors[] = 'Enter a valid phone number.';
    if ($department === '') $errors[] = 'Select a department.';
    if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';
    if ($password !== $confirm) $errors[] = 'Passwords do not match.';

    if (empty($errors)) {
        $conn = db();
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $errors[] = 'An account with this email already exists.';
        } else {
            $pass_hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $conn->prepare("INSERT INTO users (full_name, email, phone, department, password, role, status)
                                    VALUES (?, ?, ?, ?, ?, 'employee', 'pending')");
            $stmt->bind_param('sssss', $full_name, $email, $phone, $department, $pass_hash);

            if ($stmt->execute()) {
                $success = true;
            } else {
                $errors[] = 'Registration failed, please try again.';
            }
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Strix</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <nav class="navbar">
        <div class="logo"><span>Strix</span></div>
        <div class="nav-links">
            <a href="index.php">Home</a>
            <a href="login.php">Login</a>
        </div>
    </nav>

    <div class="form-container">
        <h2>Employee Registration</h2>
        <p class="subtitle">Fill in your details. An admin will approve your account.</p>

        <?php if ($success): ?>
            <div class="alert alert-success">
                Registration successful! Your account is <strong>pending approval</strong>.
                Once an admin approves it, you can login.
                <br><br><a href="login.php" class="btn btn-dark" style="width:100%;text-align:center;">Go to Login</a>
            </div>
        <?php else: ?>

            <?php foreach ($errors as $e): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($e); ?></div>
            <?php endforeach; ?>

            <form method="POST" action="" onsubmit="return validateForm()">
                <div class="form-group">
                    <label for="full_name">Full Name</label>
                    <input type="text" id="full_name" name="full_name" placeholder="e.g. John Smith"
                           value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="e.g. john@company.com"
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="phone">Phone</label>
                    <input type="text" id="phone" name="phone" placeholder="e.g. 9876543210"
                           value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="department">Department</label>
                    <select id="department" name="department">
                        <option value="">-- Select Department --</option>
                        <?php
                        $depts = ['IT', 'HR', 'Finance', 'Marketing', 'Sales', 'Operations', 'Administration'];
                        $sel = $_POST['department'] ?? '';
                        foreach ($depts as $d) {
                            echo '<option value="' . $d . '"' . ($sel === $d ? ' selected' : '') . '>' . $d . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="At least 6 characters">
                </div>
                <div class="form-group">
                    <label for="confirm">Confirm Password</label>
                    <input type="password" id="confirm" name="confirm" placeholder="Re-enter password">
                </div>
                <button type="submit" class="form-submit">Register</button>
            </form>

            <div class="form-footer">
                Already have an account? <a href="login.php">Login here</a>
            </div>

        <?php endif; ?>
    </div>

    <footer>&copy; 2026 Strix. All rights reserved.</footer>

    <script>
        function validateForm() {
            var pass = document.getElementById('password').value;
            var confirm = document.getElementById('confirm').value;
            if (pass !== confirm) {
                alert('Passwords do not match!');
                return false;
            }
            return true;
        }
    </script>

</body>
</html>