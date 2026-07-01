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
$result = mysqli_query($conn, "SELECT user_id, email, role, initial_capital, setup_complete, created_at, last_login FROM user WHERE user_id = $user_id LIMIT 1");
if (!$result || mysqli_num_rows($result) !== 1) {
    die('User not found.');
}

$user = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View User</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div id="wrapper" class="d-flex vh-100 w-100" style="overflow: hidden;">
    <?php include 'sidebar.php'; ?>
    <div id="page-content-wrapper" class="flex-grow-1 d-flex flex-column p-4" style="overflow: hidden;">
        <h2>User Details</h2>
        <table class="table table-bordered w-50">
            <tr><th>User ID</th><td><?php echo htmlspecialchars($user['user_id']); ?></td></tr>
            <tr><th>Email</th><td><?php echo htmlspecialchars($user['email']); ?></td></tr>
            <tr><th>Role</th><td><?php echo htmlspecialchars($user['role']); ?></td></tr>
            <tr><th>Initial Capital</th><td><?php echo $user['initial_capital'] === null ? '-' : htmlspecialchars($user['initial_capital']); ?></td></tr>
            <tr><th>Setup Complete</th><td><?php echo $user['setup_complete'] ? 'Yes' : 'No'; ?></td></tr>
            <tr><th>Created At</th><td><?php echo htmlspecialchars($user['created_at']); ?></td></tr>
            <tr><th>Last Login</th><td><?php echo $user['last_login'] ? htmlspecialchars($user['last_login']) : 'Never'; ?></td></tr>
        </table>
        <a href="user-management.php" class="btn btn-secondary">Back to User Management</a>
    </div>
</div>
</body>
</html>