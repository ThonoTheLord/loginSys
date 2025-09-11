<?php
if (isset($_POST["submit"])) {
    $uid = $_POST["uid"];
    $pwd = $_POST["pwd"];

    require_once 'dbh.inc.php';
    require_once 'functions.inc.php';

    if (empty($uid) || empty($pwd)) {
        header("location: ../login.php?error=emptyinput");
        exit();
    }

    $user = uidExists($conn, $uid);
    if ($user === false) {
        header("location: ../login.php?error=wronglogin");
        exit();
    }

    if (password_verify($pwd, $user["pwd"])) {
        session_start();
        $_SESSION["user_id"] = $user["id"];
        $_SESSION["role"] = $user["role"];
        $_SESSION["name"] = $user["full_name"];
        header("location: ../dashboard.php");
        exit();
    } else {
        header("location: ../login.php?error=wronglogin");
        exit();
    }
} else {
    header("location: ../login.php");
    exit();
}