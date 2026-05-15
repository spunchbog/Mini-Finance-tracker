<?php
include('header.php');
include('db_connect.php');



if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 1. Sanitize user inputs
    $email    = mysqli_real_escape_string($conn, $_POST['email']);
    $user_id  = mysqli_real_escape_string($conn, $_POST['user_id']);
    $password = $_POST['password']; 


    // 2. Data validation
    if (!is_numeric($user_id)) {
        echo "<script>alert('Please enter a valid numeric user ID'); location.href='signup.php';</script>";
        exit();
    }
    if (strlen($email) < 5) {
        echo "<script>alert('Email must be at least 5 characters'); location.href='signup.php';</script>";
        exit();
    }
    if (strlen($user_id) < 3) {
        echo "<script>alert('User ID must be at least 3 characters'); location.href='signup.php';</script>";
        exit();
    }

    // 3. Check if User ID already exists
    $sql_check = "SELECT user_id FROM user WHERE user_id='$user_id'";
    $result = mysqli_query($conn, $sql_check);
    
    if (mysqli_num_rows($result) > 0) {
        echo "<script>alert('User ID already exists'); location.href='signup.php';</script>";
        exit();
    }

    // 4. Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // 5. Save user data (no email verification)
    $query = "INSERT INTO user (email, user_id, password, role) 
              VALUES ('$email', '$user_id', '$hashed_password', 'user')";
    
    if (mysqli_query($conn, $query)) {
        echo "<script>alert('Registration successful! You can now log in.'); window.location.href='login.php';</script>";
    } else {
        echo "<script>alert('Recording failed: " . mysqli_error($conn) . "');</script>";
    }
}
?>

<h3>Register New User</h3>
<p>Fill in the form below to create a new account for FinTrack.</p>
<form method='POST' action=''>
    <table border='0'>
        <tr>
            <td>Email:</td>
            <td><input type='email' name='email' required></td>
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

<?php include('footer.php'); ?>
