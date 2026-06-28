<?php
session_start();
include('db_connect.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

$info = '';
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['Email']) ? trim($_POST['Email']) : '';

    if (empty($email)) {
        $err = 'Please enter your email address.';
    } else {
        $email = mysqli_real_escape_string($conn, $email);
        $query = "SELECT user_id FROM user WHERE email = '$email' LIMIT 1";
        $result = mysqli_query($conn, $query);

        if (mysqli_num_rows($result) > 0) {
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            $escaped_token = mysqli_real_escape_string($conn, $token);
            $escaped_expires = mysqli_real_escape_string($conn, $expires);

            $update = "UPDATE user SET reset_token = '$escaped_token', reset_expires = '$escaped_expires' WHERE email = '$email'";
            mysqli_query($conn, $update);

            $resetLink = "http://localhost/fintrack/reset-password.php?token=" . $token;

            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'daavinesh879@gmail.com';
                $mail->Password   = 'ghpa wxbk vtkt bhoj';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                $mail->setFrom('daavinesh879@gmail.com', 'FinTrack');
                $mail->addAddress($email);

                $mail->isHTML(true);
                $mail->Subject = 'FinTrack Password Reset Request';
                $mail->Body    = "<p>You requested a password reset for your FinTrack account.</p>"
                                . "<p>Click the link below to set a new password. This link will expire in 1 hour.</p>"
                                . "<p><a href='$resetLink'>Reset your password</a></p>";

                $mail->send();
                $info = 'If that email exists in our system, a reset link has been sent. Please check your inbox.';
            } catch (Exception $e) {
                $errorMessage = $mail->ErrorInfo ?: $e->getMessage();
                $err = 'Unable to send reset email. ' . htmlspecialchars($errorMessage);
            }
        } else {
            $info = 'If that email exists in our system, a reset link has been sent. Please check your inbox.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>FinTrack - Forgot Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body { background-color: #f5f5f5; }
        .card { border-radius: 12px; box-shadow: 0 10px 40px rgba(0,0,0,0.1); overflow: hidden; }
        .card-header { background: linear-gradient(135deg, #6ee7b7 0%, #34d399 100%); color: white; text-align: center; padding: 40px 20px; }
        .card-header h1 { margin: 0; font-size: 2rem; }
        .card-body { padding: 40px; }
        .form-group { margin-bottom: 24px; }
        .form-group label { font-weight: 600; margin-bottom: 8px; display: block; }
        .input-wrapper { position: relative; display: flex; align-items: center; }
        .input-wrapper i { position: absolute; left: 14px; color: #059669; font-size: 1.1rem; }
        .input-wrapper input { width: 100%; padding: 12px 12px 12px 42px; border: 2px solid #e0d7c6; border-radius: 8px; background: #fdfaf5; }
        .input-wrapper input:focus { outline: none; border-color: #059669; background: white; box-shadow: 0 0 0 3px rgba(5,150,105,0.1); }
        .btn-primary { width: 100%; padding: 12px; border-radius: 8px; font-weight: 600; background: linear-gradient(135deg, #6ee7b7 0%, #34d399 100%); border: none; }
        .alert { border-radius: 8px; }
        .card-footer { text-align: center; padding: 20px 40px; border-top: 1px solid #e0d7c6; }
        .card-footer a { color: #059669; text-decoration: none; font-weight: 600; }
        .card-footer a:hover { color: #047857; }
    </style>
</head>
<body>
<div class="d-flex align-items-center justify-content-center min-vh-100">
    <div class="card" style="max-width: 420px; width: 100%;">
        <div class="card-header">
            <h1>Forgot Password</h1>
            <p>Enter your email to receive a reset link.</p>
        </div>
        <div class="card-body">
            <?php if (!empty($err)) { ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($err); ?></div>
            <?php } elseif (!empty($info)) { ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($info); ?></div>
            <?php } ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <div class="input-wrapper">
                        <i class="bi bi-envelope"></i>
                        <input type="email" id="email" name="Email" placeholder="Enter your email" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Send Reset Link</button>
            </form>
        </div>
        <div class="card-footer">
            Remembered your password? <a href="login.php">Sign in</a>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
