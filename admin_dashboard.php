<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

require __DIR__ . '/config.php';
$conn = db();

// Handle approve / reject actions
if (isset($_GET['action'], $_GET['id']) && in_array($_GET['action'], ['approve', 'reject'])) {
    $id     = (int)$_GET['id'];
    $status = $_GET['action'] === 'approve' ? 'approved' : 'rejected';
    $stmt   = $conn->prepare("UPDATE users SET status = ? WHERE id = ? AND role = 'employee'");
    $stmt->bind_param('si', $status, $id);
    $stmt->execute();
    $stmt->close();
    header('Location: admin_dashboard.php');
    exit;
}

// Fetch all pending employees
$pending = $conn->query("SELECT * FROM users WHERE role = 'employee' AND status = 'pending'");

// Fetch all approved employees
$approvedAll = $conn->query("SELECT * FROM users WHERE role = 'employee' AND status = 'approved'");

// All admins list (for display reference)
$admins = $conn->query("SELECT * FROM users WHERE role = 'admin' AND status = 'approved'");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Strix</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <nav class="navbar">
        <div class="logo"><span>Strix</span></div>
        <div class="nav-links">
            <a href="index.php">Home</a>
            <span class="user-chip">Admin: <?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
            <a href="logout.php" class="btn btn-primary" style="padding:6px 14px;">Logout</a>
        </div>
    </nav>

    <div class="dashboard">
        <div class="welcome">
            <h2>Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?>!</h2>
            <p>Here you can approve or reject pending employee registrations.</p>
        </div>

        <h3 class="section-title">Pending Approvals</h3>
        <?php if ($pending && $pending->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Department</th>
                        <th>Registered At</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $pending->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td><?php echo htmlspecialchars($row['phone']); ?></td>
                            <td><?php echo htmlspecialchars($row['department']); ?></td>
                            <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                            <td><span class="badge badge-pending">Pending</span></td>
                            <td class="actions">
                                <a href="?action=approve&id=<?php echo $row['id']; ?>"
                                   class="btn btn-success" style="padding:6px 12px;font-size:0.85rem;">Approve</a>
                                <a href="?action=reject&id=<?php echo $row['id']; ?>"
                                   class="btn btn-danger" style="padding:6px 12px;font-size:0.85rem;">Reject</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="empty-msg">No pending registrations. All caught up! &#128077;</div>
        <?php endif; ?>

        <br><br>

        <h3 class="section-title">Approved Employees</h3>
        <?php if ($approvedAll && $approvedAll->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Department</th>
                        <th>Joined</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $approvedAll->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td><?php echo htmlspecialchars($row['phone']); ?></td>
                            <td><?php echo htmlspecialchars($row['department']); ?></td>
                            <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                            <td><span class="badge badge-approved">Approved</span></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="empty-msg">No approved employees yet.</div>
        <?php endif; ?>

        <br><br>

        <h3 class="section-title">Admin Team</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Department</th>
                    <th>Role</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $admins->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo htmlspecialchars($row['department']); ?></td>
                        <td><span class="badge badge-admin">Admin</span></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <footer>&copy; 2026 Strix. All rights reserved.</footer>

</body>
</html>