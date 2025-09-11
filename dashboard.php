<?php
session_start();

// Redirect to login if not authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'includes/dbh.inc.php';
require_once 'includes/functions.inc.php';

// Get user data using prepared statements
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT full_name, role, department FROM employees WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Redirect if user data not found
if (!$user) {
    session_destroy();
    header("Location: login.php");
    exit();
}

// Get recent clock-ins for admin
if ($user['role'] === 'admin') {
    $stmt = $conn->prepare("
        SELECT e.full_name, e.department, c.timestamp, c.code_used 
        FROM clock_ins c
        JOIN employees e ON c.employee_id = e.id
        ORDER BY c.timestamp DESC
        LIMIT 10
    ");
    $stmt->execute();
    $recent_clockins = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

// Get recent activity for employee
if ($user['role'] === 'employee') {
    $stmt = $conn->prepare("
        SELECT timestamp, code_used 
        FROM clock_ins 
        WHERE employee_id = ?
        ORDER BY timestamp DESC 
        LIMIT 5
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $recent_activity = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Digital CitiZen's - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
</head>
<body>
    <?php include_once 'header.php'; ?>

    <main class="container mt-5 pt-5">
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($_GET['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Welcome, <?php echo htmlspecialchars($user['full_name']); ?></h2>
            <span class="badge bg-primary"><?php echo strtoupper($user['role']); ?></span>
        </div>

        <?php if ($user['role'] === 'admin'): ?>
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card text-white bg-primary mb-3">
                        <div class="card-body">
                            <h5 class="card-title">Total Employees</h5>
                            <?php
                            $stmt = $conn->prepare("SELECT COUNT(*) FROM employees");
                            $stmt->execute();
                            $stmt->bind_result($count);
                            $stmt->fetch();
                            $stmt->close();
                            ?>
                            <p class="card-text display-4"><?php echo $count; ?></p>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card text-white bg-success mb-3">
                        <div class="card-body">
                            <h5 class="card-title">Today's Clock-ins</h5>
                            <?php
                            $stmt = $conn->prepare("SELECT COUNT(*) FROM clock_ins WHERE DATE(timestamp) = CURDATE()");
                            $stmt->execute();
                            $stmt->bind_result($count);
                            $stmt->fetch();
                            $stmt->close();
                            ?>
                            <p class="card-text display-4"><?php echo $count; ?></p>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card text-white bg-info mb-3">
                        <div class="card-body">
                            <h5 class="card-title">Active Departments</h5>
                            <?php
                            $stmt = $conn->prepare("SELECT COUNT(DISTINCT department) FROM employees");
                            $stmt->execute();
                            $stmt->bind_result($count);
                            $stmt->fetch();
                            $stmt->close();
                            ?>
                            <p class="card-text display-4"><?php echo $count; ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Clock-ins Table -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>Recent Clock-ins</h4>
                    <a href="reports.php" class="btn btn-sm btn-outline-light">View Full Report</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
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
                                <?php foreach ($recent_clockins as $clockin): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($clockin['full_name']); ?></td>
                                        <td><?php echo htmlspecialchars($clockin['department']); ?></td>
                                        <td><?php echo date('M j, Y H:i', strtotime($clockin['timestamp'])); ?></td>
                                        <td><?php echo htmlspecialchars($clockin['code_used']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        <?php else: ?>
            <!-- Employee Dashboard Content -->
            <div class="row">
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h4>Clock In</h4>
                        </div>
                        <div class="card-body text-center">
                            <div id="qr-code" class="mb-3"></div>
                            <p class="text-muted">Scan this QR code or enter the daily code below</p>
                            
                            <form id="clockin-form" class="needs-validation" novalidate>
                                <div class="input-group has-validation">
                                    <input type="text" id="code-input" class="form-control" 
                                           placeholder="Enter daily code" required>
                                    <div class="invalid-feedback">
                                        Please enter the daily code
                                    </div>
                                    <button type="submit" class="btn btn-primary">Submit</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4>Recent Activity</h4>
                            <a href="history.php" class="btn btn-sm btn-outline-primary">View All</a>
                        </div>
                        <div class="card-body">
                            <ul class="list-group">
                                <?php foreach ($recent_activity as $activity): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <span><?php echo date('M j, Y H:i', strtotime($activity['timestamp'])); ?></span>
                                        <span class="badge bg-primary"><?php echo htmlspecialchars($activity['code_used']); ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <script>
    // Enhanced QR Code Generation
    document.addEventListener('DOMContentLoaded', function() {
        // Fetch daily code
        fetch('includes/get_daily_code.php')
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.text();
            })
            .then(code => {
                if (code.length > 0) {
                    new QRCode(document.getElementById("qr-code"), {
                        text: code,
                        width: 200,
                        height: 200,
                        colorDark: "#000000",
                        colorLight: "#ffffff",
                        correctLevel: QRCode.CorrectLevel.H
                    });
                } else {
                    document.getElementById('qr-code').innerHTML = 
                        '<div class="alert alert-warning">No code available</div>';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('qr-code').innerHTML = 
                    '<div class="alert alert-danger">Failed to load QR code</div>';
            });


        const form = document.getElementById('clockin-form');
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            if (!form.checkValidity()) {
                e.stopPropagation();
                form.classList.add('was-validated');
                return;
            }

            const code = document.getElementById('code-input').value;
            
            try {
                const response = await fetch('includes/clock_in.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ code: code })
                });

                const data = await response.json();
                
                if (data.success) {
                    // Clear input field
                    document.getElementById('code-input').value = '';
                    
                    // Show success message
                    const alert = document.createElement('div');
                    alert.className = 'alert alert-success alert-dismissible fade show';
                    alert.innerHTML = `
                        Clocked in successfully!
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    `;
                    document.querySelector('main').prepend(alert);
                    
                    // Refresh activity list
                    const activityList = document.querySelector('.list-group');
                    if (activityList) {
                        const response = await fetch('includes/get_recent_activity.php');
                        const html = await response.text();
                        activityList.innerHTML = html;
                    }
                } else {
                    throw new Error(data.message || 'Clock-in failed');
                }
            } catch (error) {
                const alert = document.createElement('div');
                alert.className = 'alert alert-danger alert-dismissible fade show';
                alert.innerHTML = `
                    Error: ${error.message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                document.querySelector('main').prepend(alert);
            }
        });
    });
    </script>

    <?php include_once 'footer.php'; ?>
</body>
</html>