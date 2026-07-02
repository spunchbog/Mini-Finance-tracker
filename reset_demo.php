<?php
include('db_connect.php');
// Reset User 1 to "New User" status
mysqli_query($conn, "UPDATE user SET setup_complete = 0 WHERE user_id = 1");
header("Location: signup.php");
?>