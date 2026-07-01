<?php
session_start();
include('db_connect.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

if (empty($_GET['user_id']) || !is_numeric($_GET['user_id'])) {
    die('Invalid user ID.');
}

$user_id = intval($_GET['user_id']);

// Prevent admin from deactivating themselves
if ($user_id === intval($_SESSION['user_id'])) {
    die('You cannot deactivate your own admin account.');
}

mysqli_query($conn, "ALTER TABLE user ADD COLUMN IF NOT EXISTS is_active TINYINT(1) NOT NULL DEFAULT 1");

$target_user_query = mysqli_query($conn, "SELECT role, is_active FROM user WHERE user_id = $user_id LIMIT 1");
if (!$target_user_query || mysqli_num_rows($target_user_query) === 0) {
    die('User not found.');
}
$target_user = mysqli_fetch_assoc($target_user_query);
if ($target_user['role'] !== 'admin') {
    die('Admins may only deactivate other admin accounts, not normal users.');
}

if ((int)$target_user['is_active'] === 0) {
    $_SESSION['admin_message'] = '<div class="alert alert-warning">This admin account is already deactivated.</div>';
    header('Location: user-management.php');
    exit();
}

$update = mysqli_query($conn, "UPDATE user SET is_active = 0 WHERE user_id = $user_id");
if (!$update) {
    die('Database error: ' . mysqli_error($conn));
}

$_SESSION['admin_message'] = '<div class="alert alert-success">Admin account deactivated successfully. They will no longer be able to log in.</div>';
header('Location: user-management.php');
exit();
