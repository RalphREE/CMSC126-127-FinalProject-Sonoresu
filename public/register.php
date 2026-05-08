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

        // 1. Check if the email is already in use
        $check_stmt = $conn->prepare("SELECT user_id FROM Users WHERE email = ?");
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $check_stmt->store_result();

        if ($check_stmt->num_rows > 0) {
            $message = "Error: That email is already registered.";
        } else {
            // 2. Insert the new user using a prepared statement
            $insert_stmt = $conn->prepare("INSERT INTO Users (username, email, password_hash) VALUES (?, ?, ?)");
            $insert_stmt->bind_param("sss", $username, $email, $hashed_password);
            
            if ($insert_stmt->execute()) {
                $message = "Registration successful! <a href='login.php'>Click here to login</a>.";
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
</head>
<body>
    <h2>Create an Account</h2>
    
    <?php if ($message): ?>
        <p><strong><?php echo $message; ?></strong></p>
    <?php endif; ?>

    <form method="POST" action="register.php">
        <div>
            <label>Username:</label><br>
            <input type="text" name="username" required>
        </div>
        <br>
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
        <button type="submit">Register</button>
    </form>
    
    <p>Already have an account? <a href="login.php">Log in here</a>.</p>
</body>
</html>