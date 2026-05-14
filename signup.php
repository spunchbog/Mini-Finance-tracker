<?php
include('db_connect.php');

// logic to reset the demo user via the button link
if (isset($_GET['reset_demo'])) {
    mysqli_query($condb, "UPDATE user SET initial_capital = 0, setup_complete = 0 WHERE user_id = '1'");
    header("Location: InitialPage.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize user inputs
    $username = mysqli_real_escape_string($condb, $_POST['username']);
    $user_id  = mysqli_real_escape_string($condb, $_POST['user_id']);
    
    // We don't sanitize the password because hashing handles it securely
    $plain_password = $_POST['password'];

    # Data validation: Numeric check
    if (!is_numeric($user_id) ) {
        die("<script>
            alert('Please enter a valid numeric user ID');
            location.href='signup.php';
        </script>");
    }

    # Data validation: Ensure username isn't too short
    if (strlen($user_id) < 3) {
        die("<script>
            alert('User ID must be at least 3 characters');
            location.href='signup.php';
        </script>");
    }

    # Check if User ID already exists
    $sql_check = "SELECT user_id FROM user WHERE user_id='$user_id' LIMIT 1";
    $result = mysqli_query($condb, $sql_check);
    
    if (mysqli_num_rows($result) > 0) {
        echo "<script>alert('User ID already exists. Please choose another.'); location.href='signup.php';</script>";
        exit;
    }

    // 1. PASSWORD HASHING
    // This turns the plain password into a secure 60+ character string
    $hashed_password = password_hash($plain_password, PASSWORD_DEFAULT);

    // 2. Save user data with the HASHED password
    $query = "INSERT INTO user (username, user_id, password, role) 
              VALUES ('$username', '$user_id', '$hashed_password', 'user')";

    if (mysqli_query($condb, $query)) {
    echo "<script>
        alert('User Registered Successfully! Redirecting to setup...');
        location.href='InitialPage.php'; // Change from login.php to InitialPage.php
        </script>";
    } else {
        echo "<script>alert('Registration failed. Please try again.');</script>";
        // Useful for debugging during development:
        // echo mysqli_error($condb); 
    }
}
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
    </div>

    <!-- Main Content Area -->
<div id="page-content-wrapper" class="flex-grow-1 d-flex flex-column p-4" style="overflow: hidden;">
    <header class="mb-3" style="flex: 0 1 auto;">
    <h3>Register New User</h3>
    </header>
    <p>Fill in the form below to create a new account.</p>
    <form method='POST' action=''>
        <table border='0'>
            <tr>
                <td>Username:</td>
                <td><input type='text' name='username' required></td>
            </tr>
            <tr>
                <td>User ID (Numbers only):</td>
                <td><input type='number' name='user_id' required></td>
            </tr>
            <tr>
                <td>Password:</td>
                <td><input type='password' name='password' required minlength="5"></td>
            </tr>
            <tr>
                <td colspan='2' align='center'>
                    <br>
                    <button type='submit'>Register</button>
                </td>
            </tr>
        </table>
    </form>
</div>
<div class="mt-3">
    <a href="signup.php?reset_demo=1" class="btn btn-outline-primary shadow-sm">
        <i class="bi bi-gear-fill"></i> Reset & Test Setup Flow
    </a>
</div>
</body>
</html>

