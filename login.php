<?php
session_start();
include('db_connect.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (empty($_POST['Email']) || empty($_POST['password'])) {
        $err = "<p style='color:red;'>Please fill in all fields.</p>";
    } else {
        $email = mysqli_real_escape_string($conn, $_POST['Email']);
        $password_attempt = $_POST['password'];

        // FETCHING: Added 'is_verified' and 'setup_complete' to the query
        $query = "SELECT user_id, role, password, is_verified, setup_complete 
                  FROM user 
                  WHERE email = '$email' LIMIT 1";
        
        $result = mysqli_query($conn, $query);

        if (mysqli_num_rows($result) == 1) {
            $row = mysqli_fetch_array($result);
            $stored_password = $row['password'];
            $valid_password = false;

            if (password_verify($password_attempt, $stored_password)) {
                $valid_password = true;
            } elseif ($stored_password === $password_attempt) {
                $valid_password = true;
                $new_hash = password_hash($password_attempt, PASSWORD_DEFAULT);
                $escaped_hash = mysqli_real_escape_string($conn, $new_hash);
                mysqli_query($conn, "UPDATE user SET password = '$escaped_hash' WHERE user_id = " . intval($row['user_id']));
            }

            if ($valid_password) {
                // 1. CHECK: Email Verification
                if ($row['is_verified'] == 0) {
                    $err = "<p style='color:red;'>Please verify your email before logging in.</p>";
                } 
                // 2. CHECK: Setup Completion
                else {
                    session_regenerate_id();
                    $_SESSION['user_id'] = $row['user_id'];
                    $role = trim(strtolower($row['role']));
                    $_SESSION['role'] = ($role === 'admin') ? 'admin' : 'user';

                    // Redirect logic:
                    if ($_SESSION['role'] === 'admin') {
                        echo "<script>alert('Admin login successful!'); window.location.href='index.php';</script>";
                    } elseif ($row['setup_complete'] == 0) {
                        // Force users who haven't set their initial capital to InitialPage.php
                        echo "<script>alert('Login successful! Redirecting to setup...'); window.location.href='InitialPage.php';</script>";
                    } else {
                        // Regular user flow
                        echo "<script>alert('Login successful!'); window.location.href='dashboard.php';</script>";
                    }
                    exit;
                }
            } else {
                $err = "<p style='color:red;'>Invalid Email or Password.</p>";
            }
        } else {
            $err = "<p style='color:red;'>Invalid Email or Password.</p>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">    
<head>  
    <meta charset="UTF-8">
    <title>FinTrack - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #a7f3d0 0%, #6ee7b7 100%);
            padding: 20px;
        }

        .login-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            max-width: 420px;
            width: 100%;
        }

        .login-header {
            background: linear-gradient(135deg, #6ee7b7 0%, #34d399 100%);
            color: white;
            padding: 40px 20px;
            text-align: center;
        }

        .login-header h1 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 10px;
            letter-spacing: -0.5px;
        }

        .login-header p {
            font-size: 0.95rem;
            opacity: 0.95;
            margin: 0;
        }

        .login-body {
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

        .btn-login {
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

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(5, 150, 105, 0.4);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .alert-error {
            background-color: #fee;
            border: 2px solid #cc1808;
            color: #cc1808;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.95rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-error i {
            font-size: 1.2rem;
            flex-shrink: 0;
        }

        .login-footer {
            text-align: center;
            padding: 20px 40px;
            border-top: 1px solid #e0d7c6;
            font-size: 0.9rem;
            color: #666;
        }

        .login-footer a {
            color: #059669;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .login-footer a:hover {
            color: #047857;
        }

        @media (max-width: 480px) {
            .login-header {
                padding: 30px 20px;
            }

            .login-header h1 {
                font-size: 1.75rem;
            }

            .login-body {
                padding: 30px 20px;
            }

            .login-footer {
                padding: 15px 20px;
            }
        }
    </style>
</head>
<body style="background-color: #f5f5f5;">
<div class="login-container">
    <div class="login-card">
        <div class="login-header">
            <h1>FinTrack</h1>
            <p>Financial Management System</p>
        </div>

        <div class="login-body">
            <?php if (!empty($err)) { ?>
                <div class="alert-error">
                    <i class="bi bi-exclamation-circle-fill"></i>
                    <span><?php echo str_replace('<p style=\'color:red;\'>', '', str_replace('</p>', '', $err)); ?></span>
                </div>
            <?php } ?>

            <form action='' method='POST'>
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <div class="input-wrapper">
                        <i class="bi bi-envelope"></i>
                        <input type="email" id="email" name="Email" placeholder="Enter your email" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-wrapper">
                        <i class="bi bi-lock"></i>
                        <input type="password" id="password" name="password" placeholder="Enter your password" required>
                    </div>
                </div>

                <button type="submit" class="btn-login">Sign In</button>
            </form>
        </div>

        <div class="login-footer">
            Don't have an account? <a href="signup.php">Sign up here</a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
