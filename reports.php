<?php
session_start();
include('db_connect.php');

// 1. DYNAMIC CHECK: Check if a user is actually logged in
if (!isset($_SESSION['user_id'])) {
    // Kick them back to login if no session exists
    header("Location: login.php");
    exit;
}

// 2. LIVE SESSION GRAB: Replace the hardcoded 1111 ID with the real user
$user_id = intval($_SESSION['user_id']);

// 3. Make sure the role matches their database entry (fallback to user if empty)
if (empty($_SESSION['role'])) {
    $_SESSION['role'] = 'user';
}

// 4. Run your setup verification query dynamically using the session's ID
$check = mysqli_query($conn, "SELECT setup_complete FROM user WHERE user_id = $user_id");
if (!$check) {
    die("Reports Query Failed: " . mysqli_error($conn));
}
$user = mysqli_fetch_assoc($check);

// 5. Pull in variables, categories, and calculations scoped to this user
require_once 'data.php'; 
?>

<!DOCTYPE html>
<html lang="en">    
<head>  
    <meta charset="UTF-8">
    <title>FinTrack Reports</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div id="wrapper" class="d-flex vh-100 w-100" style="overflow: hidden;"> 
        <?php include 'sidebar.php'; ?>

        <div id="page-content-wrapper" class="flex-grow-1 d-flex flex-column p-4" style="overflow: hidden;">
            <header class="mb-3" style="flex: 0 1 auto;">
                <h2>Reports</h2>
                <p class="text-muted small mb-0"><?= date('l, d M Y') ?></p>
            </header>

            <div class="card p-4 shadow-sm border-0 mb-3 bg-white rounded-3">
                <h5 class="fw-bold text-dark mb-3" style="letter-spacing: -0.3px;">💡 Financial Insights</h5>
                
                <div class="mb-3 fs-5 text-dark pb-2 border-bottom border-light">
                    <span class="text-secondary fw-normal">Highest spending category:</span> 
                    <strong class="fw-bold"><?php echo htmlspecialchars($highestSpendingCategory); ?></strong> 
                    <span class="text-danger fw-bold ms-1">
                        (RM <?php echo number_format($highestSpendingAmount, 2); ?>)
                    </span>
                </div>
                
                <div class="fs-6 text-secondary style-insight-line" style="line-height: 1.6;">
                    <?php echo $insightMessage; ?>
                </div>
            </div>

            <div class="card p-3 shadow-sm border-0 mb-4 text-center">
                <h6 class="text-muted small fw-bold text-uppercase">Net Savings Rate</h6>
                <div class="display-5 fw-bold my-2 <?php echo $savingsRate >= 20 ? 'text-success' : 'text-warning'; ?>">
                    <?php echo $savingsRate; ?>%
                </div>
                <p class="text-secondary small mb-0">
                    <?php 
                    if ($savingsRate >= 20) {
                        echo "Excellent! You are beating the healthy 20% savings rule baseline.";
                    } elseif ($savingsRate > 0) {
                        echo "You're saving money, but try to cut down minor expenses to reach 20%.";
                    } else {
                        echo "Alert: Your expenses are outpacing your structural income streams.";
                    }
                    ?>
                </p>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>