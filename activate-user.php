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

// Prevent admin from re-activating themselves? (allowed) — we only protect self-deactivation elsewhere

$target_user_query = mysqli_query($conn, "SELECT role, is_verified FROM user WHERE user_id = $user_id LIMIT 1");
if (!$target_user_query || mysqli_num_rows($target_user_query) === 0) {
    die('User not found.');
}
$target_user = mysqli_fetch_assoc($target_user_query);
if ($target_user['role'] !== 'admin') {
    die('Admins may only activate other admin accounts, not normal users.');
}
if ((int)$target_user['is_verified'] === 1) {
    $_SESSION['admin_message'] = '<div class="alert alert-warning">This admin account is already active.</div>';
    header('Location: user-management.php');
    exit();
}

$update = mysqli_query($conn, "UPDATE user SET is_verified = 1 WHERE user_id = $user_id");
if (!$update) {
    die('Database error: ' . mysqli_error($conn));
}

$_SESSION['admin_message'] = '<div class="alert alert-success">Admin account re-activated successfully. They can now log in again.</div>';
header('Location: user-management.php');
exit();

?>
