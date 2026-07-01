<?php
// verify.php
include('db_connect.php');
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$status = 'error';
$message = 'No verification token provided.';

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    $query = "UPDATE user 
              SET is_verified = 1, verification_token = NULL 
              WHERE verification_token = ?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $token);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $status = 'success';
        $message = 'Your email has been verified. You can now log in to your account.';
    } else {
        $status = 'failed';
        $message = 'The link is invalid, expired, or the account has already been verified.';
    }
    $stmt->close();
}

include 'header.php';
?>

<div class="container d-flex align-items-center justify-content-center" style="min-height: 70vh;">
    <div class="card shadow-sm" style="max-width:720px; width:100%;">
        <div class="card-body p-5 text-center">
            <?php if ($status === 'success') { ?>
                <div class="mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="72" height="72" fill="none" viewBox="0 0 24 24" stroke="#198754" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4" />
                        <circle cx="12" cy="12" r="9" stroke="#198754" stroke-width="1.5" fill="rgba(25,135,84,0.06)" />
                    </svg>
                </div>
                <h2 class="card-title">Verification Successful</h2>
                <p class="text-muted mb-4"><?php echo htmlspecialchars($message); ?></p>
                <a href="login.php" class="btn btn-primary btn-lg">Go to Login</a>
            <?php } elseif ($status === 'failed') { ?>
                <div class="mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="72" height="72" viewBox="0 0 24 24" fill="none" stroke="#dc3545" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 6L6 18M6 6l12 12" />
                        <circle cx="12" cy="12" r="9" stroke="#dc3545" stroke-width="1.5" fill="rgba(220,53,69,0.06)" />
                    </svg>
                </div>
                <h2 class="card-title">Verification Failed</h2>
                <p class="text-muted mb-4"><?php echo htmlspecialchars($message); ?></p>
                <a href="index.php" class="btn btn-secondary">Return Home</a>
            <?php } else { ?>
                <div class="mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="72" height="72" viewBox="0 0 24 24" fill="none" stroke="#6c757d" stroke-width="1.5">
                        <circle cx="12" cy="12" r="9" stroke="#6c757d" stroke-width="1.5" fill="rgba(108,117,125,0.04)" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l2 2" />
                    </svg>
                </div>
                <h2 class="card-title">Verification</h2>
                <p class="text-muted mb-4"><?php echo htmlspecialchars($message); ?></p>
                <a href="index.php" class="btn btn-secondary">Return Home</a>
            <?php } ?>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>

<?php
// end verify.php
?>