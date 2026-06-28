<?php
session_start();
include('db_connect.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $initial_amount = $_POST['amount'];
    
    if (empty($_SESSION['user_id']) || !is_numeric($_SESSION['user_id'])) {
        header('Location: login.php');
        exit();
    }

    $user_id = intval($_SESSION['user_id']);

    // Update the record you just created in the DB
    $sql = "UPDATE user SET initial_capital = '$initial_amount', setup_complete = 1 WHERE user_id = $user_id";

    if (mysqli_query($conn, $sql)) {
        // SUCCESS: Redirect to the dashboard
        header("Location: dashboard.php");
        exit();
    } else {
        echo "Error updating record: " . mysqli_error($conn);
    }
}
?>