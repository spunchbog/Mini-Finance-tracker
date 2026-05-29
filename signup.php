<?php
include('db_connect.php');

if (isset($_GET['reset_demo'])) {
    mysqli_query($conn, "UPDATE user SET initial_capital = 0, setup_complete = 0 WHERE user_id = '1'");
    header("Location: InitialPage.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $user_id  = mysqli_real_escape_string($conn, $_POST['user_id']);
    $plain_password = $_POST['password'];

    if (!is_numeric($user_id)) {
        die("<script>alert('Please enter a valid numeric user ID'); location.href='signup.php';</script>");
    }

    if (strlen($user_id) < 3) {
        die("<script>alert('User ID must be at least 3 characters'); location.href='signup.php';</script>");
    }

    $sql_check = "SELECT user_id FROM user WHERE user_id='$user_id' LIMIT 1";
    $result = mysqli_query($conn, $sql_check);
    
    if (mysqli_num_rows($result) > 0) {
        echo "<script>alert('User ID already exists. Please choose another.'); location.href='signup.php';</script>";
        exit;
    }

    $hashed_password = password_hash($plain_password, PASSWORD_DEFAULT);

    $query = "INSERT INTO user (username, user_id, password, role) 
              VALUES ('$username', '$user_id', '$hashed_password', 'user')";

    if (mysqli_query($conn, $query)) {
        echo "<script>alert('User Registered Successfully! Redirecting to setup...'); location.href='InitialPage.php';</script>";
    } else {
        echo "<script>alert('Registration failed. Please try again.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">    
<head>  
    <meta charset="UTF-8">
    <title>FinTrack - Sign Up</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div id="wrapper" class="d-flex vh-100 w-100" style="overflow: hidden;"> 
    <?php include 'sidebar.php'; ?>

    <!-- Main Content Area -->
    <div id="page-content-wrapper" class="flex-grow-1 d-flex flex-column p-4" style="overflow: hidden;">
        <header class="mb-3">
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
        <div class="mt-3">
            <a href="signup.php?reset_demo=1" class="btn btn-outline-primary shadow-sm">
                <i class="bi bi-gear-fill"></i> Reset & Test Setup Flow
            </a>
        </div>
    </div>

</div>
</body>
</html>