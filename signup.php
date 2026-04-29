<?php
include('header.php');
include('db_connect.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize user inputs
    $username  = mysqli_real_escape_string($condb, $_POST['username']);
    $user_id  = mysqli_real_escape_string($condb, $_POST['user_id']);
    $password = mysqli_real_escape_string($condb, $_POST['password']);

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

    // 
    $sql_check = "select user_id from users where user_id='$user_id'";
    $result = mysqli_query($condb, $sql_check);
    if (mysqli_num_rows($result) > 0) {
        die("<script>
            alert('User ID already exists');
            location.href='signup.php';
        </script>");
    }
    // Save user data
    $query = "INSERT INTO users (username, user_id, password , role ) 
    VALUES ('$username', '$user_id', '$password' , 'user')";
    if (mysqli_query($condb, $query)) {
        echo "<script>
            alert('User Registered Successfully');
            location.href='login.php';
            </script>";
    } else {
        echo "<script>alert('Recording failed. Please try again.');</script>";
        echo mysqli_error($condb);
    }
}
?>

### Register New User

<h3>Register New User</h3>
<p>Fill in the form below to create a new account.</p>
<form method='POST' action=''>
    <table border='0'>
        <tr>
            <td>Username:</td>
            <td><input type='text' name='username' required></td>
        </tr>
        <tr>
            <td>User ID:</td>
            <td><input type='number' name='user_id' required></td>
        </tr>
        <tr>
            <td>Password:</td>
            <td><input type='password' name='password' required></td>
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