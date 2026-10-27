<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'employee') {
    header('Location: login.php');
    exit;
}

require __DIR__ . '/config.php';
$conn = db();

// Fetch full profile of logged-in employee
$stmt = $conn->prepare("SELECT id, full_name, email, phone, department, status, created_at FROM users WHERE id = ?");
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$me = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Fetch all admins
$admins = $conn->query("SELECT id, full_name, email, phone, department, created_at
                        FROM users WHERE role = 'admin' AND status = 'approved'");
$admin_count = $admins ? $admins->num_rows : 0;

// Company location
$company_city = 'Bengaluru';
$company_state = 'Karnataka, India';
$company_address = 'Strix Technologies, 4th Floor, Tech Park, MG Road, Bengaluru - 560001, India';

// Announcements (edit these anytime)
$notices = [
    ['icon' => '&#128197;', 'title' => 'Company Holiday', 'date' => 'Sep 18, 2026',
     'text' => 'The office will remain closed for the annual foundation day.'],
    ['icon' => '&#128188;', 'title' => 'Salary Disbursement', 'date' => 'Sep 1, 2026',
     'text' => 'Salaries for the month of August have been processed successfully.'],
    ['icon' => '&#128274;', 'title' => 'Security Update', 'date' => 'Aug 25, 2026',
     'text' => 'All employees must use the new secure login on the portal. Do not share credentials.'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Dashboard - Strix</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <nav class="navbar">
        <div class="logo"><span>Strix</span></div>
        <div class="nav-links">
            <a href="index.php">Home</a>
            <span class="user-chip"><?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
            <a href="logout.php" class="btn btn-primary" style="padding:6px 14px;">Logout</a>
        </div>
    </nav>

    <div class="dashboard">
        <div class="welcome">
            <h2>Welcome, <?php echo htmlspecialchars($me['full_name']); ?>!</h2>
            <p>You are logged in as an employee of Strix Technologies, <?php echo htmlspecialchars($company_city); ?>.</p>
        </div>

        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="num"><?php echo $admin_count; ?></div>
                <div class="label">Company Admins</div>
            </div>
            <div class="stat-card">
                <div class="num"><?php echo htmlspecialchars($me['department']); ?></div>
                <div class="label">Your Department</div>
            </div>
            <div class="stat-card">
                <div class="num"><?php echo date('d M Y', strtotime($me['created_at'])); ?></div>
                <div class="label">Joined On</div>
            </div>
            <div class="stat-card">
                <div class="num">09:00 - 18:00</div>
                <div class="label">Office Hours</div>
            </div>
        </div>

        <!-- Office hours + Profile -->
        <div class="grid-2">
            <div class="card">
                <h4>&#128339; Office Hours</h4>
                <div class="clock-display" id="clock">--:--:--</div>
                <div class="clock-date" id="today">Loading date...</div>
                <div class="profile-row"><span class="k">Working Days</span><span class="v">Monday - Friday</span></div>
                <div class="profile-row"><span class="k">Office Timing</span><span class="v">9:00 AM - 6:00 PM</span></div>
                <div class="profile-row"><span class="k">Lunch Break</span><span class="v">1:00 PM - 2:00 PM</span></div>
                <div class="profile-row"><span class="k">Weekly Off</span><span class="v">Saturday &amp; Sunday</span></div>
            </div>

            <div class="card">
                <h4>&#128100; My Profile</h4>
                <div class="profile-row"><span class="k">Full Name</span><span class="v"><?php echo htmlspecialchars($me['full_name']); ?></span></div>
                <div class="profile-row"><span class="k">Email</span><span class="v"><?php echo htmlspecialchars($me['email']); ?></span></div>
                <div class="profile-row"><span class="k">Phone</span><span class="v"><?php echo htmlspecialchars($me['phone']); ?></span></div>
                <div class="profile-row"><span class="k">Department</span><span class="v"><?php echo htmlspecialchars($me['department']); ?></span></div>
                <div class="profile-row"><span class="k">Joined</span><span class="v"><?php echo htmlspecialchars($me['created_at']); ?></span></div>
                <div class="profile-row"><span class="k">Status</span><span class="v"><?php echo ucfirst(htmlspecialchars($me['status'])); ?></span></div>
            </div>
        </div>

        <!-- Company location -->
        <div class="card" style="margin-bottom:25px;">
            <h4>&#128205; Company Location</h4>
            <div class="location-box">
                <div class="location-address">
                    <strong><?php echo htmlspecialchars($company_address); ?></strong><br>
                    City: <strong><?php echo htmlspecialchars($company_city); ?></strong> &nbsp;|&nbsp;
                    State: <strong><?php echo htmlspecialchars($company_state); ?></strong>
                </div>
                </div>
        </div>

        <!-- Notices -->
        <div class="card" style="margin-bottom:25px;">
            <h4>&#128226; Company Notices</h4>
            <?php foreach ($notices as $n): ?>
                <div class="notice-card">
                    <div class="n-icon"><?php echo $n['icon']; ?></div>
                    <div class="n-body">
                        <strong><?php echo htmlspecialchars($n['title']); ?></strong>
                        <p><?php echo htmlspecialchars($n['text']); ?></p>
                        <span class="n-date"><?php echo htmlspecialchars($n['date']); ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Admin list with search -->
        <h3 class="section-title">Company Admins
            <span style="font-weight:400;font-size:0.85rem;color:#888;">(<?php echo $admin_count; ?>)</span>
        </h3>
        <input type="text" id="adminSearch" class="search-box" placeholder="Search by name, email, phone or department...">
        <?php if ($admin_count > 0): ?>
            <table id="adminTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Department</th>
                        <th>Since</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $admins->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td><?php echo htmlspecialchars($row['phone']); ?></td>
                            <td><?php echo htmlspecialchars($row['department']); ?></td>
                            <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="empty-msg">No admins available.</div>
        <?php endif; ?>
    </div>

    <footer>&copy; 2026 Strix. All rights reserved.</footer>

    <script>
        // ---------- Live clock (very simple) ----------
        function showTime() {
            var now = new Date();
            document.getElementById('clock').innerHTML = now.toLocaleTimeString();
            document.getElementById('today').innerHTML = now.toDateString();
        }
        setInterval(showTime, 1000);

        // ---------- Admin table search ----------
        var searchInput = document.getElementById('adminSearch');
        if (searchInput) {
            searchInput.addEventListener('keyup', function () {
                var q = this.value.toLowerCase();
                var rows = document.querySelectorAll('#adminTable tbody tr');
                rows.forEach(function (row) {
                    row.style.display = row.textContent.toLowerCase().indexOf(q) > -1 ? '' : 'none';
                });
            });
        }
    </script>

</body>
</html>