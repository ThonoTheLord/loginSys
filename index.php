<?php
include_once 'header.php';

// Check authentication
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: loginPage.php");
    exit();
}

// Database connection
require_once 'db_connect.php';

// Get user role
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT role, name FROM employees WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
?>

<section class="dashboard-section py-5">
    <div class="container">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?= $_SESSION['success'] ?></div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <h2 class="mb-4">Welcome, <?= htmlspecialchars($user['name']) ?>!</h2>

        <?php if ($user['role'] === 'admin'): ?>
            <!-- Admin Dashboard -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card text-white bg-primary mb-3">
                        <div class="card-body">
                            <h5 class="card-title">Total Employees</h5>
                            <?php
                            $stmt = $pdo->query("SELECT COUNT(*) FROM employees");
                            $count = $stmt->fetchColumn();
                            ?>
                            <p class="card-text display-4"><?= $count ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-white bg-success mb-3">
                        <div class="card-body">
                            <h5 class="card-title">Today's Clock-Ins</h5>
                            <?php
                            $stmt = $pdo->query("SELECT COUNT(*) FROM clock_ins WHERE DATE(timestamp) = CURDATE()");
                            $count = $stmt->fetchColumn();
                            ?>
                            <p class="card-text display-4"><?= $count ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-white bg-info mb-3">
                        <div class="card-body">
                            <h5 class="card-title">Active Departments</h5>
                            <?php
                            $stmt = $pdo->query("SELECT COUNT(DISTINCT department) FROM employees");
                            $count = $stmt->fetchColumn();
                            ?>
                            <p class="card-text display-4"><?= $count ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h4 class="mb-0">Recent Clock-Ins</h4>
                </div>
                <div class="card-body">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Department</th>
                                <th>Time</th>
                                <th>Code Used</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmt = $pdo->query("
                                SELECT e.username, e.department, c.timestamp, c.code_used 
                                FROM clock_ins c
                                JOIN employees e ON c.employee_id = e.id
                                ORDER BY c.timestamp DESC
                                LIMIT 10
                            ");
                            while ($row = $stmt->fetch()):
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($row['username']) ?></td>
                                <td><?= htmlspecialchars($row['department']) ?></td>
                                <td><?= $row['timestamp'] ?></td>
                                <td><?= $row['code_used'] ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <?php else: ?>
            <!-- Employee Dashboard -->
            <div class="row">
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h4 class="mb-0">Clock In</h4>
                        </div>
                        <div class="card-body text-center">
                            <div id="qr-code" class="mb-3"></div>
                            <p class="text-muted">Scan this QR code or enter the daily code below</p>
                            
                            <form id="clockin-form" class="mt-3">
                                <div class="input-group mb-3">
                                    <input type="text" id="code-input" class="form-control" placeholder="Enter daily code">
                                    <button type="submit" class="btn btn-primary">Submit</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="mb-0">Recent Activity</h4>
                        </div>
                        <div class="card-body">
                            <ul class="list-group">
                                <?php
                                $stmt = $pdo->prepare("
                                    SELECT timestamp, code_used 
                                    FROM clock_ins 
                                    WHERE employee_id = ?
                                    ORDER BY timestamp DESC 
                                    LIMIT 5
                                ");
                                $stmt->execute([$user_id]);
                                while ($row = $stmt->fetch()):
                                ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span><?= $row['timestamp'] ?></span>
                                    <span class="badge bg-primary rounded-pill"><?= $row['code_used'] ?></span>
                                </li>
                                <?php endwhile; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
// QR Code Generation
fetch('get_daily_code.php')
    .then(response => response.text())
    .then(code => {
        new QRCode(document.getElementById("qr-code"), {
            text: code,
            width: 200,
            height: 200
        });
    });

// Clock-In Form Submission
document.getElementById("clockin-form").addEventListener("submit", function(e) {
    e.preventDefault();
    const code = document.getElementById('code-input').value;
    
    fetch('clock_in.php', {
        method: 'POST',
        body: JSON.stringify({ code: code }),
        headers: { 'Content-Type': 'application/json' }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert("Clocked in successfully!");
            window.location.reload();
        } else {
            alert("Error: " + data.message);
        }
    });
});
</script>

<?php 
include_once 'footer.php';
?>