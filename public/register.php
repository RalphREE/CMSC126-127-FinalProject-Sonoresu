<?php
// public/register.php
require_once '../config/db.php';

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (!empty($username) && !empty($email) && !empty($password)) {
        // Always hash passwords. NEVER store them in plain text.
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // 1. Check if the email OR username is already in use
        $check_stmt = $conn->prepare("SELECT email, username FROM Users WHERE email = ? OR username = ?");
        $check_stmt->bind_param("ss", $email, $username);
        $check_stmt->execute();
        $result = $check_stmt->get_result();

        if ($result->num_rows > 0) {
            // Determine exactly what is already taken for better user feedback
            $email_taken = false;
            $username_taken = false;

            while ($row = $result->fetch_assoc()) {
                if (strtolower($row['email']) === strtolower($email)) {
                    $email_taken = true;
                }
                if (strtolower($row['username']) === strtolower($username)) {
                    $username_taken = true;
                }
            }

            if ($email_taken && $username_taken) {
                $message = "Error: Both the username and email are already taken.";
            } elseif ($email_taken) {
                $message = "Error: That email is already registered.";
            } else {
                $message = "Error: That username is already taken.";
            }
        } else {
            // 2. Neither exists, so insert the new user using a prepared statement
            $insert_stmt = $conn->prepare("INSERT INTO Users (username, email, password_hash) VALUES (?, ?, ?)");
            $insert_stmt->bind_param("sss", $username, $email, $hashed_password);
            
            if ($insert_stmt->execute()) {
                $message = "Registration successful! <a href='login.php' style='color:#6c63ff; font-weight:600;'>Click here to login</a>.";
            } else {
                $message = "Database Error: " . $conn->error;
            }
            $insert_stmt->close();
        }
        $check_stmt->close();
    } else {
        $message = "Please fill in all required fields.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Register - Sonoresu</title>
<link rel="stylesheet" href="global.css">
<style>
        :root {
            --bg: #0b0b0f;
            --surface: #14141a;
            --border: #262633;
            --accent: #6c63ff;
            --text-main: #f3f3f5;
            --text-sub: #71717a;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background-image: radial-gradient(circle at 50% 0%, rgba(108, 99, 255, 0.12) 0%, transparent 50%);
        }

        .auth-container {
            width: 100%;
            max-width: 440px;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.5);
        }

        .auth-header { text-align: center; margin-bottom: 32px; }
        .auth-header h2 { font-size: 1.8rem; font-weight: 700; letter-spacing: -0.02em; margin-bottom: 8px; }
        .auth-header p { color: var(--text-sub); font-size: 0.9rem; }

        .status-message {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid var(--border);
            padding: 14px;
            border-radius: 10px;
            font-size: 0.9rem;
            margin-bottom: 24px;
            text-align: center;
            line-height: 1.5;
        }

        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 8px; color: #d1d1d6; }
        
        .form-group input {
            width: 100%;
            background: #0b0b0f;
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 12px 16px;
            color: var(--text-main);
            font-size: 0.95rem;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .form-group input:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(108,99,255,0.15);
        }

        .submit-btn {
            width: 100%;
            background: var(--accent);
            color: #fff;
            border: none;
            border-radius: 10px;
            padding: 14px;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
            margin-top: 10px;
            box-shadow: 0 4px 12px rgba(108,99,255,0.3);
        }
        .submit-btn:hover { background: #5b52e6; }

        .auth-footer {
            text-align: center;
            margin-top: 28px;
            font-size: 0.9rem;
            color: var(--text-sub);
        }
        .auth-footer a { color: var(--accent); text-decoration: none; font-weight: 600; }
        .auth-footer a:hover { text-decoration: underline; }
    </style>
</head>
<body>

    <div class="auth-container">
        <header class="auth-header">
            <h2>Create an Account</h2>
            <p>Join Sonoresu and sync your music with your mood</p>
        </header>
        
        <?php if ($message): ?>
            <div class="status-message">
                <strong><?php echo $message; ?></strong>
            </div>
        <?php endif; ?>

        <form method="POST" action="register.php">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" placeholder="Choose a username" required>
            </div>
            
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" placeholder="you@example.com" required>
            </div>
            
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="••••••••" required>
            </div>
            
            <button type="submit" class="submit-btn">Register</button>
        </form>
        
        <footer class="auth-footer">
            <p>Already have an account? <a href="login.php">Log in here</a></p>
        </footer>
    </div>

</body>
</html>