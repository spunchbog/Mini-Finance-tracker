<?php
session_start();
include 'db_connect.php';

$is_admin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';

$total_users = 0;
$total_admins = 0;
$total_categories = 0;

if ($is_admin) {
    $users_count = mysqli_query($conn, "SELECT COUNT(*) AS cnt FROM user");
    $admins_count = mysqli_query($conn, "SELECT COUNT(*) AS cnt FROM user WHERE role = 'admin'");
    $categories_count = mysqli_query($conn, "SELECT COUNT(*) AS cnt FROM category");

    if ($users_count && $row = mysqli_fetch_assoc($users_count)) {
        $total_users = $row['cnt'];
    }
    if ($admins_count && $row = mysqli_fetch_assoc($admins_count)) {
        $total_admins = $row['cnt'];
    }
    if ($categories_count && $row = mysqli_fetch_assoc($categories_count)) {
        $total_categories = $row['cnt'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $is_admin ? 'Admin Dashboard' : 'FinTrack'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div id="wrapper" class="d-flex vh-100 w-100" style="overflow: hidden;">
    <?php include 'sidebar.php'; ?>

    <div id="page-content-wrapper" class="flex-grow-1 d-flex flex-column p-4" style="overflow: hidden;">
        <header class="mb-3" style="flex: 0 1 auto;">
            <h3><?php echo $is_admin ? 'Admin Dashboard' : 'FinTrack'; ?></h3>
        </header>

        <?php if ($is_admin): ?>
            <p class="text-muted">Welcome back, Admin. Use the controls below to manage users and system categories.</p>
            <div class="row row-cols-1 row-cols-md-3 g-3 mb-4">
                <div class="col">
                    <div class="card shadow-sm border-0 p-3">
                        <div class="card-body">
                            <h6 class="text-uppercase text-muted">Total Users</h6>
                            <h2 class="mb-0"><?php echo $total_users; ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card shadow-sm border-0 p-3">
                        <div class="card-body">
                            <h6 class="text-uppercase text-muted">Admin Accounts</h6>
                            <h2 class="mb-0"><?php echo $total_admins; ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card shadow-sm border-0 p-3">
                        <div class="card-body">
                            <h6 class="text-uppercase text-muted">Categories</h6>
                            <h2 class="mb-0"><?php echo $total_categories; ?></h2>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="card shadow-sm border-0 p-4">
                        <h5 class="card-title">Quick Actions</h5>
                        <p class="card-text text-muted">Jump directly to administration areas.</p>
                        <a href="user-management.php" class="btn btn-primary me-2">Manage Users</a>
                        <a href="category.php" class="btn btn-outline-primary">Manage Categories</a>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card shadow-sm border-0 p-4">
                        <h5 class="card-title">System Overview</h5>
                        <p class="card-text text-muted">This page is your home base for admin tasks and platform management.</p>
                        <ul class="list-unstyled mb-0">
                            <li><strong><?php echo $total_users; ?></strong> registered users</li>
                            <li><strong><?php echo $total_categories; ?></strong> available categories</li>
                        </ul>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="card shadow-sm border-0 p-4">
                <h5>Welcome to FinTrack</h5>
                <p class="text-muted">Please login or sign up to access your personal finance dashboard.</p>
            </div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
