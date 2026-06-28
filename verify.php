<?php
// verify.php
include('db_connect.php');

if (isset($_GET['token'])) {
    // 1. Get the token from the URL
    $token = $_GET['token'];
    
    // 2. Use prepared statement to prevent SQL injection
    $query = "UPDATE user 
              SET is_verified = 1, verification_token = NULL 
              WHERE verification_token = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    
    // 3. Check if any row was actually updated
    if ($stmt->affected_rows > 0) {
        echo "<h2>Verification Successful!</h2>";
        echo "<p>Your email has been verified. You can now <a href='login.php'>Login</a> to your account.</p>";
    } else {
        echo "<h2>Verification Failed</h2>";
        echo "<p>The link is invalid or the account has already been verified.</p>";
    }
    $stmt->close();
} else {
    echo "No verification token provided.";
}
?>