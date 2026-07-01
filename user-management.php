<?php
session_start();
include('db_connect.php');

// Admin-only access check
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

$admin_message = '';
if (isset($_SESSION['admin_message'])) {
    $admin_message = $_SESSION['admin_message'];
    unset($_SESSION['admin_message']);
}

// STEP 1: Generate & send verification code (prints to terminal)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_code'])) {
    $admin_email    = trim($_POST['admin_email'] ?? '');
    $admin_password = $_POST['admin_password'] ?? '';

    if ($admin_email === '' || $admin_password === '') {
        $admin_message = '<div class="alert alert-danger">Please enter both email and password for the new admin.</div>';
    } elseif (!filter_var($admin_email, FILTER_VALIDATE_EMAIL)) {
        $admin_message = '<div class="alert alert-danger">Please enter a valid email address.</div>';
    } else {
        $check_email = mysqli_real_escape_string($conn, $admin_email);
        $sql_check_admin = "SELECT user_id FROM user WHERE email='$check_email' LIMIT 1";
        $result_check = mysqli_query($conn, $sql_check_admin);

        if ($result_check && mysqli_num_rows($result_check) > 0) {
            $admin_message = '<div class="alert alert-warning">That email is already registered.</div>';
        } else {
            // Generate a 6-digit verification code
            $verification_code = str_pad(strval(random_int(0, 999999)), 6, '0', STR_PAD_LEFT);

            // Store pending admin details + code in session (expires when session ends)
            $_SESSION['pending_admin'] = [
                'email'    => $admin_email,
                'password' => $admin_password,
                'code'     => $verification_code,
                'expires'  => time() + 300 // 5 minute expiry
            ];

            // "Send" the code — also logged to the PHP error log for reference
            error_log("ADMIN VERIFICATION CODE for {$admin_email}: {$verification_code}");

            // DEV MODE: show the code directly on screen since there's no email/SMS step yet.
            // Replace this with a real email/SMS send before going to production, and remove
            // the code from $admin_message below so it isn't exposed to the browser.
            $admin_message = '<div class="alert alert-info">
                A verification code has been generated.
                <strong>DEV MODE — code shown here only for local testing:</strong>
                <span style="font-size:1.3em; letter-spacing:3px; font-weight:bold;">' . htmlspecialchars($verification_code) . '</span>
            </div>';
        }
    }
}

// STEP 2: Verify code & create admin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_code'])) {
    $entered_code = trim($_POST['verification_code'] ?? '');

    if (!isset($_SESSION['pending_admin'])) {
        $admin_message = '<div class="alert alert-danger">No pending admin request found. Please start again.</div>';
    } elseif (time() > $_SESSION['pending_admin']['expires']) {
        unset($_SESSION['pending_admin']);
        $admin_message = '<div class="alert alert-danger">Verification code expired. Please request a new one.</div>';
    } elseif ($entered_code === '' ) {
        $admin_message = '<div class="alert alert-danger">Please enter the verification code.</div>';
    } elseif (!hash_equals($_SESSION['pending_admin']['code'], $entered_code)) {
        $admin_message = '<div class="alert alert-danger">Incorrect verification code. Please try again.</div>';
    } else {
        // Code matches — create the admin
        $pending = $_SESSION['pending_admin'];
        $admin_email_escaped = mysqli_real_escape_string($conn, $pending['email']);
        $hashed_admin_password = password_hash($pending['password'], PASSWORD_DEFAULT);
        $escaped_hash = mysqli_real_escape_string($conn, $hashed_admin_password);

        $insert_admin = "INSERT INTO user (email, password, role, is_verified, verification_token, initial_capital, setup_complete) 
                          VALUES ('$admin_email_escaped', '$escaped_hash', 'admin', 1, NULL, 0.00, 1)";

        if (mysqli_query($conn, $insert_admin)) {
            $admin_message = '<div class="alert alert-success">New admin created successfully with an encrypted password.</div>';
            unset($_SESSION['pending_admin']);
        } else {
            $admin_message = '<div class="alert alert-danger">Database error: ' . htmlspecialchars(mysqli_error($conn)) . '</div>';
        }
    }
}

// Allow cancelling the pending verification step
if (isset($_GET['cancel'])) {
    unset($_SESSION['pending_admin']);
    header('Location: user-management.php');
    exit();
}

// Determine if we should show the code-entry step
$awaiting_code = isset($_SESSION['pending_admin']) && time() <= $_SESSION['pending_admin']['expires'];

// Fetch all users
$sql = "SELECT user_id, email, role, is_active FROM user ORDER BY user_id ASC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div id="wrapper" class="d-flex vh-100 w-100" style="overflow: hidden;">
    <?php include 'sidebar.php'; ?>

    <div id="page-content-wrapper" class="flex-grow-1 d-flex flex-column p-4" style="overflow: hidden;">

        <h2>User Management</h2>
        <p>Below is a searchable table of all registered users. You can view details or deactivate accounts.</p>
<table border="1" cellpadding="8" cellspacing="0">
    <thead>
        <tr>
            <th>User ID</th>
            <th>Email</th>
            <th>Role</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php while($row = mysqli_fetch_assoc($result)): ?>
        <tr>
            <td><?php echo htmlspecialchars($row['user_id']); ?></td>
            <td><?php echo htmlspecialchars($row['email']); ?></td>
            <td><?php echo htmlspecialchars($row['role']); ?></td>
            <td><?php echo ((int)$row['is_active'] === 0) ? 'Deactivated' : 'Active'; ?></td>
            <td>
                <a href="view-user.php?user_id=<?php echo urlencode($row['user_id']); ?>">View Details</a>
                <?php if ($row['role'] === 'admin' && intval($row['user_id']) !== intval($_SESSION['user_id'])): ?>
                    <?php if ((int)$row['is_active'] === 1): ?>
                        | <a href="deactivate-user.php?user_id=<?php echo urlencode($row['user_id']); ?>" class="deactivate-link" data-user-id="<?php echo intval($row['user_id']); ?>" onclick="return confirm('Are you sure you want to deactivate this admin account?');">Deactivate</a>
                    <?php else: ?>
                        | <a href="activate-user.php?user_id=<?php echo urlencode($row['user_id']); ?>" class="activate-link" data-user-id="<?php echo intval($row['user_id']); ?>" onclick="return confirm('Are you sure you want to reactivate this admin account?');">Activate</a>
                    <?php endif; ?>
                <?php endif; ?>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<div class="mt-4">
    <?php echo $admin_message; ?>
    <h3>Create a New Admin</h3>

    <?php if (!$awaiting_code): ?>
        <!-- STEP 1: Enter email + password, request verification code -->
        <form method="POST" action="user-management.php">
            <div class="mb-3">
                <label for="admin_email" class="form-label">Admin Email</label>
                <input type="email" class="form-control" id="admin_email" name="admin_email" required>
            </div>
            <div class="mb-3">
                <label for="admin_password" class="form-label">Admin Password</label>
                <input type="password" class="form-control" id="admin_password" name="admin_password" required>
            </div>
            <button type="submit" name="request_code" class="btn btn-primary">Send Verification Code</button>
        </form>
    <?php else: ?>
        <!-- STEP 2: Enter the code printed in the terminal -->
        <form method="POST" action="user-management.php">
            <div class="mb-3">
                <label for="verification_code" class="form-label">
                    Enter Verification Code
                    <small class="text-muted d-block">Enter the code shown above for <?php echo htmlspecialchars($_SESSION['pending_admin']['email']); ?></small>
                </label>
                <input type="text" class="form-control" id="verification_code" name="verification_code" maxlength="6" pattern="\d{6}" required autofocus>
            </div>
            <button type="submit" name="verify_code" class="btn btn-success">Verify &amp; Create Admin</button>
            <a href="user-management.php?cancel=1" class="btn btn-link">Cancel</a>
        </form>
    <?php endif; ?>
</div>

<?php include('footer.php'); ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const deactivateLinks = document.querySelectorAll('.deactivate-link');

    deactivateLinks.forEach(link => {
        const isSelf = link.dataset.self === 'true';
        if (isSelf) {
            link.addEventListener('click', function(event) {
                event.preventDefault();
                alert('You cannot deactivate your own admin account.');
            });
        }
    });
});
</script>

</body>
</html>