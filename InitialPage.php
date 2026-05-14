<?php
session_start();
require_once 'db_connect.php';

$user_id = 1; // Forcing User 1 for the demo

// If they already finished setup, don't let them stay here
$query = mysqli_query($conn, "SELECT setup_complete FROM user WHERE user_id = $user_id");
$user = mysqli_fetch_assoc($query);

if ($user['setup_complete'] == 1) {
    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>FinTrack | Initial Setup</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root { --bg: #fdfcf8; --accent: #2d2d2d; }
        body { background-color: var(--bg); height: 100vh; display: flex; align-items: center; justify-content: center; overflow: hidden; }
        .setup-card { background: white; padding: 40px; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.03); max-width: 400px; width: 100%; border: 1px solid #eee; }
        .btn-start { background: var(--accent); color: white; border: none; padding: 12px; border-radius: 8px; width: 100%; font-weight: 600; margin-top: 20px; }
        .btn-start:hover { background: #000; }
        input[type="number"] { border-radius: 8px; padding: 12px; border: 1px solid #ddd; }
    </style>
</head>
<body>
    <div class="setup-card">
        <h3 class="fw-bold mb-2">Welcome to FinTrack</h3>
        <p class="text-muted small mb-4">To begin tracking, we need to know your current starting balance or monthly budget.</p>
        
        <form action="save_initial.php" method="POST">P
            <label class="form-label small fw-bold text-uppercase">Initial Capital (RM)</label>
            <input type="number" name="amount" class="form-control" placeholder="0.00" step="0.01" required autofocus>
            <button type="submit" class="btn-start">Set Balance & Enter Dashboard</button>
        </form>
    </div>
</body>
</html>