<?php
session_start();
include('header.php');
include('connection.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Retrieve data from the form below
    $user_id = mysqli_real_escape_string($condb, $_POST['user_id']);
    $password = mysqli_real_escape_string($condb, $_POST['password']);

    // User login process
    $query = "SELECT user_id, username, role 
              FROM user 
              WHERE user_id = '$user_id' AND password = '$password'";
    
    $result = mysqli_query($condb, $query);

    if (mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_array($result);
        $_SESSION['user_id'] = $row['user_id'];
        $_SESSION['role'] = $row['role'];

        echo "<script>alert('Login successful!');</script>";
        header("Location: index.php");
        exit;

    } else {
        $err = "<p style='color:red;'>Login Failed<br>
                Please check your User ID and Password</p>";
    }
}
?>

<h3>User Login</h3>
<p>Please complete the information below</p>

<form action='' method='POST'>
    <table border='0'>
        <tr>
            <td>User ID</td>
            <td><input type='text' name='user_id'></td>
        </tr>
        <tr>
            <td>Password</td>
            <td><input type='password' name='password'></td>
        </tr>
        <tr>
            <td colspan='2' align='center'>
                <input type='submit' value='Login'>
            </td>
        </tr>
    </table>
    <?php if (!empty($err)) echo $err; ?>
</form>

<?php include('footer.php'); ?>