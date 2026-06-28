<?php
// Enable error reporting to find the cause of blank pages
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include('db_connect.php');

// Include PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = mysqli_real_escape_string($conn, $_POST['Email']); 
    $plain_password = $_POST['password'];

    // Check if email already exists
    $sql_check = "SELECT user_id FROM user WHERE email='$email' LIMIT 1";
    $result = mysqli_query($conn, $sql_check);
    
    if (mysqli_num_rows($result) > 0) {
        echo "<script>alert('Email already exists.'); location.href='signup.php';</script>";
        exit;
    }

    $token = bin2hex(random_bytes(32)); 
    $hashed_password = password_hash($plain_password, PASSWORD_DEFAULT);

    // Insert user with is_verified = 0
    $query = "INSERT INTO user (email, password, role, is_verified, verification_token, initial_capital, setup_complete) 
              VALUES ('$email', '$hashed_password', 'user', 0, '$token', 0.00, 0)";

    if (mysqli_query($conn, $query)) {
        // Send Verification Email
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'daavinesh879@gmail.com'; 
            $mail->Password   = 'ghpa wxbk vtkt bhoj'; // App Password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('daavinesh879@gmail.com', 'FinTrack');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Verify your FinTrack Account';
            $mail->Body    = "Click here to verify: <a href='http://localhost/fintrack/verify.php?token=$token'>Verify Account</a>";

            $mail->send();
            echo "<script>alert('Registration successful! Please check your email to verify.'); location.href='login.php';</script>";
        } catch (Exception $e) {
            echo "Registration successful, but email could not be sent. Mailer Error: " . $mail->ErrorInfo;
        }
        exit;
    } else {
        echo "Database Error: " . mysqli_error($conn);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">    
<head>  
    <meta charset="UTF-8">
    <title>FinTrack - Sign Up</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .signup-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #a7f3d0 0%, #6ee7b7 100%);
            padding: 20px;
        }

        .signup-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            max-width: 420px;
            width: 100%;
        }

        .signup-header {
            background: linear-gradient(135deg, #6ee7b7 0%, #34d399 100%);
            color: white;
            padding: 40px 20px;
            text-align: center;
        }

        .signup-header h1 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 10px;
            letter-spacing: -0.5px;
        }

        .signup-header p {
            font-size: 0.95rem;
            opacity: 0.95;
            margin: 0;
        }

        .signup-body {
            padding: 40px;
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 8px;
            font-size: 0.95rem;
        }

        .form-group .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .form-group .input-wrapper i {
            position: absolute;
            left: 14px;
            color: #059669;
            font-size: 1.1rem;
        }

        .form-group input {
            width: 100%;
            padding: 12px 12px 12px 42px;
            border: 2px solid #e0d7c6;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background-color: #fdfaf5;
        }

        .form-group input:focus {
            outline: none;
            border-color: #059669;
            background-color: white;
            box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.1);
        }

        .form-group input::placeholder {
            color: #999;
        }

        .password-hint {
            font-size: 0.85rem;
            color: #666;
            margin-top: 6px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .password-hint i {
            font-size: 0.9rem;
            color: #88d38c;
        }

        .btn-signup {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #6ee7b7 0%, #34d399 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-signup:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(5, 150, 105, 0.4);
        }

        .btn-signup:active {
            transform: translateY(0);
        }

        .signup-footer {
            text-align: center;
            padding: 20px 40px;
            border-top: 1px solid #e0d7c6;
            font-size: 0.9rem;
            color: #666;
        }

        .signup-footer a {
            color: #059669;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .signup-footer a:hover {
            color: #047857;
        }

        @media (max-width: 480px) {
            .signup-header {
                padding: 30px 20px;
            }

            .signup-header h1 {
                font-size: 1.75rem;
            }

            .signup-body {
                padding: 30px 20px;
            }

            .signup-footer {
                padding: 15px 20px;
            }
        }
    </style>
</head>
<body style="background-color: #f5f5f5;">
<div class="signup-container">
    <div class="signup-card">
        <div class="signup-header">
            <h1>Join FinTrack</h1>
            <p>Create Your Financial Account</p>
        </div>

        <div class="signup-body">
            <form method='POST' action=''>
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <div class="input-wrapper">
                        <i class="bi bi-envelope"></i>
                        <input type='email' id="email" name='Email' placeholder="Enter your email" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-wrapper">
                        <i class="bi bi-lock"></i>
                        <input type='password' id="password" name='password' placeholder="Min. 5 characters" required minlength="5">
                    </div>
                    <div class="password-hint">
                        <i class="bi bi-info-circle"></i>
                        <span>At least 5 characters required</span>
                    </div>
                </div>

                <button type='submit' class="btn-signup">Create Account</button>
            </form>
        </div>

        <div class="signup-footer">
            Already have an account? <a href="login.php">Sign in here</a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
