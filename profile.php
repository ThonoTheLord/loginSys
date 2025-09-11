<?php
session_start();

// Redirect to login if not authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'includes/dbh.inc.php';

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Get current user data
$stmt = $conn->prepare("SELECT full_name, email, uid, department, role FROM employees WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Handle profile updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        // Update profile information
        $new_name = trim($_POST['full_name']);
        $new_email = trim($_POST['email']);
        
        // Validate inputs
        if (empty($new_name) || empty($new_email)) {
            $error = "Please fill in all required fields";
        } else {
            // Check if email exists
            $stmt = $conn->prepare("SELECT id FROM employees WHERE email = ? AND id != ?");
            $stmt->bind_param("si", $new_email, $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $error = "Email is already in use by another account";
            } else {
                // Update profile
                $stmt = $conn->prepare("UPDATE employees SET full_name = ?, email = ? WHERE id = ?");
                $stmt->bind_param("ssi", $new_name, $new_email, $user_id);
                
                if ($stmt->execute()) {
                    $success = "Profile updated successfully";
                    // Refresh user data
                    $user['full_name'] = $new_name;
                    $user['email'] = $new_email;
                } else {
                    $error = "Error updating profile: " . $conn->error;
                }
                $stmt->close();
            }
        }
    }
    elseif (isset($_POST['change_password'])) 
    {
        // Handle password change
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        if (empty($current_password)) {
            $error = "Current password is required";
        } elseif ($new_password !== $confirm_password) {
            $error = "New passwords do not match";
        } elseif (strlen($new_password) < 8) {
            $error = "Password must be at least 8 characters";
        } else {
            // Verify current password
            $stmt = $conn->prepare("SELECT pwd FROM employees WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $db_password = $result->fetch_assoc()['pwd'];
            $stmt->close();
            
            if (password_verify($current_password, $db_password)) {
                // Update password
                $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE employees SET pwd = ? WHERE id = ?");
                $stmt->bind_param("si", $new_hashed_password, $user_id);
                
                if ($stmt->execute()) {
                    $success = "Password changed successfully";
                } else {
                    $error = "Error changing password: " . $conn->error;
                }
                $stmt->close();
            } else {
                $error = "Current password is incorrect";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Digital CitiZen's</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include_once 'header.php'; ?>

    <main class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <h2 class="mb-4">User Profile</h2>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>

                <!-- Profile Update Form -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Personal Information</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Full Name</label>
                                <input type="text" class="form-control" name="full_name" 
                                       value="<?= htmlspecialchars($user['full_name']) ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Email Address</label>
                                <input type="email" class="form-control" name="email" 
                                       value="<?= htmlspecialchars($user['email']) ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Company ID</label>
                                <input type="text" class="form-control" 
                                       value="<?= htmlspecialchars($user['uid']) ?>" disabled>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Department</label>
                                <input type="text" class="form-control" 
                                       value="<?= htmlspecialchars($user['department']) ?>" disabled>
                            </div>
                            
                            <button type="submit" name="update_profile" class="btn btn-primary">
                                Update Profile
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Password Change Form -->
                <div class="card">
                    <div class="card-header">
                        <h5>Change Password</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Current Password</label>
                                <input type="password" class="form-control" name="current_password" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">New Password</label>
                                <input type="password" class="form-control" name="new_password" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Confirm New Password</label>
                                <input type="password" class="form-control" name="confirm_password" required>
                            </div>
                            
                            <button type="submit" name="change_password" class="btn btn-warning">
                                Change Password
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include_once 'footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>