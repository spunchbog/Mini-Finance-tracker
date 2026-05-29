<?php
session_start();
include('db_connect.php');

// TEMPORARY: Force User 1
$user_id = $_SESSION['user_id'];

// Check if setup is done
$check = mysqli_query($conn, "SELECT setup_complete FROM user WHERE user_id = $user_id");
$user = mysqli_fetch_assoc($check);



require_once 'data.php'; // pulls in variables and calculations
?>

<!DOCTYPE html>
<html lang="en">    
<head>  
    <meta charset="UTF-8">
    <title>FinTrack Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div id="wrapper" class="d-flex vh-100 w-100" style="overflow: hidden;"> 
        <?php include 'sidebar.php'; ?>

        <!-- Main Content Area -->
        <div id="page-content-wrapper" class="flex-grow-1 d-flex flex-column p-4" style="overflow: hidden;">
            <header class="mb-3" style="flex: 0 1 auto;">
                <h2>Dashboard</h2>
                <p class="text-muted small mb-0"><?= date('l, d M Y') ?></p>
            </header>


    <div class="card p-3 shadow-sm border-0 mb-3">
        <h6 class="fw-bold text-muted mb-2">💡 Financial Insights</h6>
        <div class="mb-2">
            <strong>Highest spending category:</strong> 
            <span class="badge bg-warning text-dark"><?php echo $highestSpendingCategory; ?></span> 
            (RM <?php echo number_format($highestSpendingAmount, 2); ?>)
        </div>
        <div class="small text-secondary">
            <?php echo $insightMessage; ?>
        </div>
    </div>
</body>