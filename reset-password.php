<?php
session_start();
include('db_connect.php');

$err = '';
$info = '';
$show_form = false;
$token = isset($_GET['token']) ? $_GET['token'] : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = isset($_POST['token']) ? $_POST['token'] : '';
    $new_password = isset($_POST['password']) ? $_POST['password'] : '';
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

    if (empty($token) || empty($new_password) || empty($confirm_password)) {
        $err = 'Please fill in all fields.';
    } elseif ($new_password !== $confirm_password) {
        $err = 'Passwords do not match.';
    } else {
        $token = mysqli_real_escape_string($conn, $token);
        $query = "SELECT user_id, reset_expires FROM user WHERE reset_token = '$token' LIMIT 1";
        $result = mysqli_query($conn, $query);

        if (mysqli_num_rows($result) === 1) {
            $row = mysqli_fetch_assoc($result);
            $expires = $row['reset_expires'];

            if ($expires === null || strtotime($expires) < time()) {
                $err = 'This reset link has expired. Please request a new password reset.';
            } else {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $escaped_hash = mysqli_real_escape_string($conn, $hashed_password);

                $update = "UPDATE user SET password = '$escaped_hash', reset_token = NULL, reset_expires = NULL WHERE user_id = " . intval($row['user_id']);
                mysqli_query($conn, $update);

                $info = 'Your password has been updated successfully. You can now <a href="login.php">sign in</a>.';
            }
        } else {
            $err = 'Invalid password reset token. Please request a new reset link.';
        }
    }
} else {
    if (!empty($token)) {
        $token = mysqli_real_escape_string($conn, $token);
        $query = "SELECT reset_expires FROM user WHERE reset_token = '$token' LIMIT 1";
        $result = mysqli_query($conn, $query);

        if (mysqli_num_rows($result) === 1) {
            $row = mysqli_fetch_assoc($result);
            if ($row['reset_expires'] !== null && strtotime($row['reset_expires']) >= time()) {
                $show_form = true;
            } else {
                $err = 'This reset link is invalid or has expired. Please request a new password reset.';
            }
        } else {
            $err = 'This reset link is invalid or has expired. Please request a new password reset.';
        }
    } else {
        $err = 'No reset token provided.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>FinTrack - Reset Password</title>
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
            <h1>Reset Password</h1>
            <p>Choose a new password for your account.</p>
        </div>
        <div class="card-body">
            <?php if (!empty($err)) { ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($err); ?></div>
            <?php } elseif (!empty($info)) { ?>
                <div class="alert alert-success"><?php echo $info; ?></div>
            <?php } ?>

            <?php if ($show_form) { ?>
                <form method="POST" action="">
                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                    <div class="form-group">
                        <label for="password">New Password</label>
                        <div class="input-wrapper">
                            <i class="bi bi-lock"></i>
                            <input type="password" id="password" name="password" placeholder="Enter new password" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <div class="input-wrapper">
                            <i class="bi bi-lock-fill"></i>
                            <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm new password" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Reset Password</button>
                </form>
            <?php } ?>
        </div>
        <div class="card-footer">
            <a href="login.php">Back to sign in</a>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
