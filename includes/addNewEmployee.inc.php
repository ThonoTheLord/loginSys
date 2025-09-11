<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (isset($_POST["submit"])) {
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";

    $name = $_POST["name"];
    $email = $_POST["email"];
    $uid = $_POST["uid"];
    $pwd = $_POST["pwd"];
    $pwdRepeat = $_POST["pwdrepeat"];
    $department = $_POST["department"];
    $role = $_POST["role"];

    require_once 'dbh.inc.php';
    require_once 'functions.inc.php';

    // Debug: Check database connection
    if (!$conn) {
        die("Database connection failed: " . mysqli_connect_error());
    }

    // Debug: Check if required functions exist
    if (!function_exists('emptyInputSignup') || !function_exists('createUser')) {
        die("Required functions are missing!");
    }

    // Validate inputs
    if (emptyInputSignup($name, $email, $uid, $pwd, $pwdRepeat)) {
        header("Location: ../manage_employees.php?error=emptyinput");
        exit();
    }

    if (pwdMatch($pwd, $pwdRepeat)) {
        header("Location: ../manage_employees.php?error=passwordsdontmatch");
        exit();
    }

    // if (uidExists($conn, $uid) !== false) {
    //     header("Location: ../manage_employees.php?error=userexists");
    //     exit();
    // }

    // Debug: Check if createUser is called
    echo "Creating user..."; // This should appear if the function is called
    createUser($conn, $name, $email, $uid, $pwd, $department, $role);
} else {
    header("Location: ../manage_employees.php");
    exit();
}