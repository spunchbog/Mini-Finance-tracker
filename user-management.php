
<?php
session_start();
include('db_connect.php');

// Admin-only access check
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

$admin_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_admin'])) {
    $admin_email = trim($_POST['admin_email'] ?? '');
    $admin_password = $_POST['admin_password'] ?? '';

    if ($admin_email === '' || $admin_password === '') {
        $admin_message = '<div class="alert alert-danger">Please enter both email and password for the new admin.</div>';
    } elseif (!filter_var($admin_email, FILTER_VALIDATE_EMAIL)) {
        $admin_message = '<div class="alert alert-danger">Please enter a valid email address.</div>';
    } else {
        $admin_email = mysqli_real_escape_string($conn, $admin_email);
        $sql_check_admin = "SELECT user_id FROM user WHERE email='$admin_email' LIMIT 1";
        $result_check = mysqli_query($conn, $sql_check_admin);

        if ($result_check && mysqli_num_rows($result_check) > 0) {
            $admin_message = '<div class="alert alert-warning">That email is already registered.</div>';
        } else {
            $hashed_admin_password = password_hash($admin_password, PASSWORD_DEFAULT);
            $escaped_hash = mysqli_real_escape_string($conn, $hashed_admin_password);
            $insert_admin = "INSERT INTO user (email, password, role, is_verified, verification_token, initial_capital, setup_complete) VALUES ('$admin_email', '$escaped_hash', 'admin', 1, NULL, 0.00, 1)";

            if (mysqli_query($conn, $insert_admin)) {
                $admin_message = '<div class="alert alert-success">New admin created successfully with an encrypted password.</div>';
            } else {
                $admin_message = '<div class="alert alert-danger">Database error: ' . htmlspecialchars(mysqli_error($conn)) . '</div>';
            }
        }
    }
}

// Fetch all users
$sql = "SELECT user_id, email, role FROM user ORDER BY user_id ASC";
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
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php while($row = mysqli_fetch_assoc($result)): ?>
        <tr>
            <td><?php echo htmlspecialchars($row['user_id']); ?></td>
            <td><?php echo htmlspecialchars($row['email']); ?></td>
            <td><?php echo htmlspecialchars($row['role']); ?></td>
            <td>
                <a href="view-user.php?user_id=<?php echo urlencode($row['user_id']); ?>">View Details</a>
                <?php if ($row['role'] === 'admin' && intval($row['user_id']) !== intval($_SESSION['user_id'])): ?>
                    | <a href="deactivate-user.php?user_id=<?php echo urlencode($row['user_id']); ?>" class="deactivate-link" data-user-id="<?php echo intval($row['user_id']); ?>" onclick="return confirm('Are you sure you want to deactivate this admin account?');">Deactivate</a>
                <?php endif; ?>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<div class="mt-4">
    <?php echo $admin_message; ?>
    <h3>Create a New Admin</h3>
    <form method="POST" action="user-management.php">
        <div class="mb-3">
            <label for="admin_email" class="form-label">Admin Email</label>
            <input type="email" class="form-control" id="admin_email" name="admin_email" required>
        </div>
        <div class="mb-3">
            <label for="admin_password" class="form-label">Admin Password</label>
            <input type="password" class="form-control" id="admin_password" name="admin_password" required>
        </div>
        <button type="submit" name="create_admin" class="btn btn-primary">Create Admin</button>
    </form>
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

