<?php
// ============================================================
//  setup.php — Sonoresu DB Initializer
//  Run once via browser or CLI to create the database & tables.
// ============================================================

// --- Connection Settings ------------------------------------
define('DB_HOST', 'localhost');
define('DB_USER', 'root');       // Change to your phpMyAdmin user
define('DB_PASS', '');           // Change to your phpMyAdmin password
define('DB_NAME', 'sonoresu_db');
// ------------------------------------------------------------

$errors  = [];
$success = [];

// Connect without selecting a database first
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS);

if ($conn->connect_error) {
    die("<b>Connection failed:</b> " . htmlspecialchars($conn->connect_error));
}

// ── Step 1: Create Database ──────────────────────────────────
$sql = "CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
if ($conn->query($sql)) {
    $success[] = "Database <code>" . DB_NAME . "</code> is ready.";
} else {
    $errors[] = "Create DB failed: " . $conn->error;
}

$conn->select_db(DB_NAME);

// ── Step 2: Define Tables ────────────────────────────────────
$tables = [];

$tables['Users'] = "
CREATE TABLE IF NOT EXISTS `Users` (
    user_id       INT          AUTO_INCREMENT PRIMARY KEY,
    username      VARCHAR(100) NOT NULL,
    email         VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    spotify_auth_token TEXT,
    created_at    TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

$tables['Playlist'] = "
CREATE TABLE IF NOT EXISTS `Playlist` (
    playlist_id     INT  AUTO_INCREMENT PRIMARY KEY,
    user_id         INT  NOT NULL,
    original_prompt TEXT NOT NULL,
    date_generated  DATETIME     DEFAULT CURRENT_TIMESTAMP,
    spotify_link    VARCHAR(255),
    FOREIGN KEY (user_id) REFERENCES `Users`(user_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

$tables['Track'] = "
CREATE TABLE IF NOT EXISTS `Track` (
    track_id         INT          AUTO_INCREMENT PRIMARY KEY,
    spotify_track_id VARCHAR(255) UNIQUE,
    title            VARCHAR(255) NOT NULL,
    artist           VARCHAR(255) NOT NULL,
    energy_score     FLOAT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

$tables['Playlist_Map'] = "
CREATE TABLE IF NOT EXISTS `Playlist_Map` (
    playlist_id INT NOT NULL,
    track_id    INT NOT NULL,
    PRIMARY KEY (playlist_id, track_id),
    FOREIGN KEY (playlist_id) REFERENCES `Playlist`(playlist_id) ON DELETE CASCADE,
    FOREIGN KEY (track_id)    REFERENCES `Track`(track_id)       ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

$tables['Interaction'] = "
CREATE TABLE IF NOT EXISTS `Interaction` (
    like_id     INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT NOT NULL,
    playlist_id INT NOT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id)     REFERENCES `Users`(user_id)       ON DELETE CASCADE,
    FOREIGN KEY (playlist_id) REFERENCES `Playlist`(playlist_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

// ── Step 3: Run Each Table Statement ─────────────────────────
foreach ($tables as $name => $sql) {
    if ($conn->query($sql)) {
        $success[] = "Table <code>$name</code> is ready.";
    } else {
        $errors[] = "Failed to create <code>$name</code>: " . $conn->error;
    }
}

$conn->close();

// ── Step 4: Output Results ───────────────────────────────────
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sonoresu DB Setup</title>
    <style>
        body { font-family: sans-serif; max-width: 640px; margin: 40px auto; padding: 0 20px; }
        h1   { color: #333; }
        .ok  { background: #e6f4ea; border-left: 4px solid #34a853; padding: 8px 12px; margin: 6px 0; border-radius: 4px; }
        .err { background: #fce8e6; border-left: 4px solid #ea4335; padding: 8px 12px; margin: 6px 0; border-radius: 4px; }
        code { font-weight: bold; }
        .done { margin-top: 20px; color: #555; font-size: 0.9em; }
    </style>
</head>
<body>
    <h1>Sonoresu DB Setup</h1>

    <?php foreach ($success as $msg): ?>
        <div class="ok">Sucessful.  <?= $msg ?></div>
    <?php endforeach; ?>

    <?php foreach ($errors as $msg): ?>
        <div class="err">Something Went Wrong <?= $msg ?></div>
    <?php endforeach; ?>

    <?php if (empty($errors)): ?>
        <p class="done">All done! You can now <strong>delete or restrict access to this file</strong> so it cannot be re-run.</p>
    <?php else: ?>
        <p class="done">Some steps failed. Check the errors above and fix your connection settings or SQL.</p>
    <?php endif; ?>
</body>
</html>
</php>