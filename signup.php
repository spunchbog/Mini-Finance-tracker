<?php
session_start();
include('db_connect.php');


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // FIXED: Mapping form 'Email' input directly to your database 'email' column field
    $email    = mysqli_real_escape_string($conn, $_POST['Email']); 
    $plain_password = $_POST['password'];

    // Check if email already exists
    $sql_check = "SELECT user_id FROM user WHERE email='$email' LIMIT 1";
    $result = mysqli_query($conn, $sql_check);
    
    if (mysqli_num_rows($result) > 0) {
        echo "<script>alert('Email already exists. Please use a different email.'); location.href='signup.php';</script>";
        exit;
    }

    // Generate secure 60-character password string (Fits perfectly in your VARCHAR(255) column!)
    $hashed_password = password_hash($plain_password, PASSWORD_DEFAULT);

    // FIXED: Matching your exact column definitions: user_id, email, password, role, is_verified, initial_capital, setup_complete
    // Setting 'is_verified' to 1 automatically so your new users can log in instantly without an email sender server
    $query = "INSERT INTO user (email, password, role, is_verified, initial_capital, setup_complete) 
              VALUES ('$email', '$hashed_password', 'user', 1, 0.00, 0)";

    if (mysqli_query($conn, $query)) {
        // Set session so the new user is treated as logged in
        $new_user_id = mysqli_insert_id($conn);
        $_SESSION['user_id'] = $new_user_id;
        $_SESSION['role'] = 'user';

        echo "<script>alert('User Registered Successfully! Redirecting to setup...'); location.href='InitialPage.php';</script>";
        exit;
    } else {
        $mysql_error_msg = mysqli_escape_string($conn, mysqli_error($conn));
        echo "<script>alert('Registration failed! Database Error: " . $mysql_error_msg . "'); location.href='signup.php';</script>";
        exit;
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
                    <td>Email:</td>
                    <td><input type='text' name='Email' required></td>
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

</div>
</body>
</html>