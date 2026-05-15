<?php
session_start();
include('header.php');
include('db_connect.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // 1. Basic Validation: Check if fields are empty
    if (empty($_POST['user_id']) || empty($_POST['password'])) {
        $err = "<p style='color:red;'>Please fill in all fields.</p>";
    } else {
        // Sanitize User ID (Password doesn't need escaping if using password_verify)
        $user_id = mysqli_real_escape_string($conn, $_POST['user_id']);
        $password_attempt = $_POST['password'];

        // 2. Fetch user data by ID only
        // We don't check the password in the SQL query anymore
        $query = "SELECT user_id, role, password 
              FROM user 
              WHERE user_id = '$user_id' LIMIT 1";
        
        $result = mysqli_query($conn, $query);

        if (mysqli_num_rows($result) == 1) {
            $row = mysqli_fetch_array($result);
            
            // 3. Password Hashing Validation
            // This compares the typed password with the hashed one in the DB
            if (password_verify($password_attempt, $row['password'])) {
                
                // Regenerate session ID for better security
                session_regenerate_id();
                
                $_SESSION['user_id'] = $row['user_id'];
                $_SESSION['role'] = $row['role'];

                if ($_SESSION['role'] === 'admin') {
                    echo "<script>alert('Login successful!'); window.location.href='user-management.php';</script>";
                } else {
                    echo "<script>alert('Login successful!'); window.location.href='dashboard.html';</script>";
                }
                exit;
            } else {
                $err = "<p style='color:red;'>Invalid User ID or Password.</p>";
            }
        } else {
            $err = "<p style='color:red;'>Invalid User ID or Password.</p>";
        }
    }
}
?>

<div class="login-container">
    <h3>User Login</h3>
    <p>Please complete the information below to access FinTrack</p>

    <form action='' method='POST'>
        <table border='0'>
            <tr>
                <td>User ID</td>
                <td><input type="text" name="user_id" required></td>
            </tr>
            <tr>
                <td>Password</td>
                <td><input type="password" name="password" required></td>
            </tr>
            <tr>
                <td colspan="2" align="center">
                    <br>
                    <input type="submit" value="Login">
                </td>
            </tr>
        </table>
        <?php if (!empty($err)) echo $err; ?>
    </form>
</div>

<?php include('footer.php'); ?>
