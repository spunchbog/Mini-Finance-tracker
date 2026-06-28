<?php
session_start();
include('db_connect.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if (empty($_POST['Email']) || empty($_POST['password'])) {
        $err = "<p style='color:red;'>Please fill in all fields.</p>";
    } else {
        $email = mysqli_real_escape_string($conn, $_POST['Email']);
        $password_attempt = $_POST['password'];

        $query = "SELECT user_id, role, password 
                  FROM user 
                  WHERE email = '$email' LIMIT 1";
        
        $result = mysqli_query($conn, $query);

        if (mysqli_num_rows($result) == 1) {
            $row = mysqli_fetch_array($result);
            
            if (password_verify($password_attempt, $row['password'])) {
                session_regenerate_id();
                
                $_SESSION['user_id'] = $row['user_id'];
                $_SESSION['role'] = $row['role'];

                if ($_SESSION['role'] === 'admin') {
                    echo "<script>alert('Login successful!'); window.location.href='user-management.php';</script>";
                } else {
                    echo "<script>alert('Login successful!'); window.location.href='dashboard.php';</script>";
                }
                exit;
            } else {
                $err = "<p style='color:red;'>Invalid Email or Password.</p>";
            }
        } else {
            $err = "<p style='color:red;'>Invalid Email or Password.</p>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">    
<head>  
    <meta charset="UTF-8">
    <title>FinTrack - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<div id="wrapper" class="d-flex vh-100 w-100" style="overflow: hidden;"> 
    <?php include 'sidebar.php'; ?>

    <!-- Main Content Area -->
    <div id="page-content-wrapper" class="flex-grow-1 d-flex flex-column p-4" style="overflow: hidden;">
        <header class="mb-3">
            <h3>User Login</h3>
        </header>
        <p>Please complete the information below to access FinTrack</p>

        <form action='' method='POST'>
            <table border='0'>
                <tr>
                    <td>Email</td>
                    <td><input type="email" name="Email" required></td>
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

</div>
</body>
</html>