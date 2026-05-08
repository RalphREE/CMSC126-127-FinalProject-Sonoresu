<?php
// public/login.php
session_start();
require_once '../config/db.php';

// If user is already logged in, send them straight to index
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (!empty($email) && !empty($password)) {
        // Fetch user data based on email
        $stmt = $conn->prepare("SELECT user_id, username, password_hash FROM Users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($user = $result->fetch_assoc()) {
            // Verify the inputted password against the stored hash
            if (password_verify($password, $user['password_hash'])) {
                // Set session variables to keep the user logged in
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                
                // Redirect to the main index page
                header("Location: index.php");
                exit;
            } else {
                $message = "Invalid password.";
            }
        } else {
            $message = "No account found with that email address.";
        }
        $stmt->close();
    } else {
        $message = "Please enter both email and password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Sonoresu</title>
</head>
<body>
    <h2>Login</h2>
    
    <?php if ($message): ?>
        <p><strong><?php echo $message; ?></strong></p>
    <?php endif; ?>

    <form method="POST" action="login.php">
        <div>
            <label>Email:</label><br>
            <input type="email" name="email" required>
        </div>
        <br>
        <div>
            <label>Password:</label><br>
            <input type="password" name="password" required>
        </div>
        <br>
        <button type="submit">Login</button>
    </form>
    
    <p>Don't have an account? <a href="register.php">Register here</a>.</p>
</body>
</html>