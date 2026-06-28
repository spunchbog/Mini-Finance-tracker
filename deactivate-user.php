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

$target_user_query = mysqli_query($conn, "SELECT role FROM user WHERE user_id = $user_id LIMIT 1");
if (!$target_user_query || mysqli_num_rows($target_user_query) === 0) {
    die('User not found.');
}
$target_user = mysqli_fetch_assoc($target_user_query);
if ($target_user['role'] !== 'admin') {
    die('Admins may only deactivate other admin accounts, not normal users.');
}

$delete = mysqli_query($conn, "DELETE FROM user WHERE user_id = $user_id");
if (!$delete) {
    die('Database error: ' . mysqli_error($conn));
}

header('Location: user-management.php');
exit();
