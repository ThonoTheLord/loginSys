<?php
function emptyInputSignup($name, $email, $uid, $pwd, $pwdRepeat) {
    return empty($name) || empty($email) || empty($uid) || empty($pwd) || empty($pwdRepeat);
}

function pwdMatch($pwd, $pwdRepeat) {
    return $pwd !== $pwdRepeat;
}

function uidExists($conn, $uid) {
    $sql = "SELECT * FROM employees WHERE uid = ?;";
    $stmt = mysqli_stmt_init($conn);
    
    if (!mysqli_stmt_prepare($stmt, $sql)) {
        // Debug: Log the error
        error_log("Failed to prepare statement: " . mysqli_error($conn));
        header("Location: ../manage_employees.php?error=stmtfailed");
        exit();
    }

    mysqli_stmt_bind_param($stmt, "s", $uid);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($result);
}

function createUser($conn, $name, $email, $uid, $pwd, $department, $role) {
    $sql = "INSERT INTO employees (full_name, email, uid, pwd, department, role) VALUES (?, ?, ?, ?, ?, ?);";
    $stmt = mysqli_stmt_init($conn);

    if (!mysqli_stmt_prepare($stmt, $sql)) {
        // Debug: Log the error
        error_log("Failed to prepare statement: " . mysqli_error($conn));
        header("Location: ../manage_employees.php?error=stmtfailed");
        exit();
    }

    $hashedPwd = password_hash($pwd, PASSWORD_DEFAULT);
    mysqli_stmt_bind_param($stmt, "ssssss", $name, $email, $uid, $hashedPwd, $department, $role);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: ../manage_employees.php?success=employeeadded");
    } else {
        // Debug: Log the SQL error
        error_log("SQL Error: " . mysqli_error($conn));
        header("Location: ../manage_employees.php?error=createfailed");
    }

    mysqli_stmt_close($stmt);
    exit();
}