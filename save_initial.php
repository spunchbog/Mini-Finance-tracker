<?php
session_start();
include('db_connect.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $initial_amount = $_POST['amount'];
    
    // TEMPORARY: Force User 1 for your demo
    $user_id = 1; 

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