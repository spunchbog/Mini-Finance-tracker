
<?php
session_start();
include('header.php');
include('db_connect.php');

// Admin-only access check
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Fetch all users
$sql = "SELECT user_id, email, role FROM user ORDER BY user_id ASC";
$result = mysqli_query($conn, $sql);
?>

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
                <a href="view-user.php?user_id=<?php echo urlencode($row['user_id']); ?>">View Details</a> |
                <a href="deactivate-user.php?user_id=<?php echo urlencode($row['user_id']); ?>" onclick="return confirm('Are you sure you want to deactivate this user?');">Deactivate</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<?php include('footer.php'); ?>

