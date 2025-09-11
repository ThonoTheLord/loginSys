<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Validate that an employee ID has been passed via POST
if (!isset($_POST['employee_id'])) {
    header("Location: ../manage_employees.php?error=noselection");
    exit();
}

$employee_id = $_POST['employee_id'];

// Optional: Validate that the employee ID is numeric
if (!is_numeric($employee_id)) {
    header("Location: ../manage_employees.php?error=invalidid");
    exit();
}

// Include database connection
require_once 'dbh.inc.php';

// Prepare the SQL statement to delete the employee
$stmt = $conn->prepare("DELETE FROM employees WHERE id = ?");
if (!$stmt) {
    header("Location: ../manage_employees.php?error=stmtfailed");
    exit();
}

// Bind the employee ID as an integer
$stmt->bind_param("i", $employee_id);

// Execute the statement
$stmt->execute();

// Check if a row was affected (i.e., deletion was successful)
if ($stmt->affected_rows > 0) {
    header("Location: ../manage_employees.php?success=deleted");
} else {
    header("Location: ../manage_employees.php?error=notfound");
}

$stmt->close();
$conn->close();
exit();
?>
