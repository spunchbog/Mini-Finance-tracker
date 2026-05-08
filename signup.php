<?php
include('header.php');
include('connection.php'); // Ensure this matches your connection filename

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize user inputs
    $username = mysqli_real_escape_string($connb, $_POST['username']);
    $user_id  = mysqli_real_escape_string($connb, $_POST['user_id']);
    
    // We don't sanitize the password because hashing handles it securely
    $plain_password = $_POST['password'];

    # Data validation: Numeric check for User ID
    if (!is_numeric($user_id)) {
        echo "<script>alert('Please enter a valid numeric User ID'); location.href='signup.php';</script>";
        exit;
    }

    # Data validation: Ensure User ID isn't too short
    if (strlen($user_id) < 3) {
        echo "<script>alert('User ID must be at least 3 characters'); location.href='signup.php';</script>";
        exit;
    }

    # Check if User ID already exists
    $sql_check = "SELECT user_id FROM user WHERE user_id='$user_id' LIMIT 1";
    $result = mysqli_query($connb, $sql_check);
    
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

    if (mysqli_query($connb, $query)) {
        echo "<script>
                alert('User Registered Successfully!');
                location.href='login.php';
              </script>";
    } else {
        echo "<script>alert('Registration failed. Please try again.');</script>";
        // Useful for debugging during development:
        // echo mysqli_error($connb); 
    }
}
?>

<h3>Register New User</h3>
<p>Fill in the form below to create a new account for FinTrack.</p>

<form method='POST' action=''>
    <table border='0'>
        <tr>
            <td>Email:</td>
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
                <button type='submit'>Register Account</button>
            </td>
        </tr>
    </table>
</form>

<?php include('footer.php'); ?>
