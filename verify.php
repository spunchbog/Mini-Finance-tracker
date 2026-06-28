<?php
// verify.php
include('db_connect.php');

if (isset($_GET['token'])) {
    // 1. Sanitize the token to prevent SQL injection
    $token = mysqli_real_escape_string($conn, $_GET['token']);
    
    // 2. Look for a user with this specific token
    // We update is_verified to 1 and clear the token so it can't be reused
    $query = "UPDATE user 
              SET is_verified = 1, verification_token = NULL 
              WHERE verification_token = '$token'";
    
    mysqli_query($conn, $query);
    
    // 3. Check if any row was actually updated
    if (mysqli_affected_rows($conn) > 0) {
        echo "<h2>Verification Successful!</h2>";
        echo "<p>Your email has been verified. You can now <a href='login.php'>Login</a> to your account.</p>";
    } else {
        echo "<h2>Verification Failed</h2>";
        echo "<p>The link is invalid or the account has already been verified.</p>";
    }
} else {
    echo "No verification token provided.";
}
?>