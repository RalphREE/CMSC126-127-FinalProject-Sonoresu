<?php
// public/index.php
session_start();

// Authentication Guard: Redirect to login if the session variable isn't set
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Home - Sonoresu</title>
</head>
<body>
    <h1>Sonoresu Home</h1>
    <p>Welcome, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>!</p>
    <p>This is the protected index page. Only logged-in users can see this.</p>
    
    <p><a href="logout.php">Log Out</a></p>
</body>
</html>