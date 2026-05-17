<?php
// public/editProfile.php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id  = (int)$_SESSION['user_id'];
$username = "";
$email    = "";
$messages = []; // ['type' => 'success'|'error', 'text' => '...']

// ── Fetch current account data ───────────────────────────────
$fetch = $conn->prepare("SELECT username, email FROM Users WHERE user_id = ?");
$fetch->bind_param("i", $user_id);
$fetch->execute();
$row = $fetch->get_result()->fetch_assoc();
$fetch->close();

if (!$row) {
    // Corrupt session — force re-login
    session_destroy();
    header("Location: login.php");
    exit;
}

$username = $row['username'];
$email    = $row['email'];

// ── Handle POST ──────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $section = $_POST['section'] ?? '';

    // ── Section 1: Update username / email ──────────────────
    if ($section === 'identity') {
        $new_username = trim($_POST['username'] ?? '');
        $new_email    = trim($_POST['email']    ?? '');

        if (empty($new_username) || empty($new_email)) {
            $messages[] = ['type' => 'error', 'text' => 'Username and email cannot be empty.'];
        } elseif (strlen($new_username) < 3 || strlen($new_username) > 30) {
            $messages[] = ['type' => 'error', 'text' => 'Username must be 3–30 characters.'];
        } elseif (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
            $messages[] = ['type' => 'error', 'text' => 'Please enter a valid email address.'];
        } else {
            // Check duplicates — exclude the current user
            $dup = $conn->prepare(
                "SELECT username, email FROM Users
                 WHERE (email = ? OR username = ?) AND user_id != ?"
            );
            $dup->bind_param("ssi", $new_email, $new_username, $user_id);
            $dup->execute();
            $conflicts = $dup->get_result()->fetch_all(MYSQLI_ASSOC);
            $dup->close();

            $email_taken    = false;
            $username_taken = false;
            foreach ($conflicts as $c) {
                if (strtolower($c['email'])    === strtolower($new_email))    $email_taken    = true;
                if (strtolower($c['username']) === strtolower($new_username)) $username_taken = true;
            }

            if ($email_taken && $username_taken) {
                $messages[] = ['type' => 'error', 'text' => 'That username and email are both already in use.'];
            } elseif ($email_taken) {
                $messages[] = ['type' => 'error', 'text' => 'That email address is already registered to another account.'];
            } elseif ($username_taken) {
                $messages[] = ['type' => 'error', 'text' => 'That username is already taken.'];
            } else {
                $upd = $conn->prepare(
                    "UPDATE Users SET username = ?, email = ? WHERE user_id = ?"
                );
                $upd->bind_param("ssi", $new_username, $new_email, $user_id);

                if ($upd->execute()) {
                    $username = $new_username;
                    $email    = $new_email;
                    $_SESSION['username'] = $new_username;
                    $messages[] = ['type' => 'success', 'text' => 'Username and email updated successfully.'];
                } else {
                    $messages[] = ['type' => 'error', 'text' => 'A database error occurred. Please try again.'];
                }
                $upd->close();
            }
        }
    }

    // ── Section 2: Change password ───────────────────────────
    elseif ($section === 'password') {
        $current_pw  = $_POST['current_password']  ?? '';
        $new_pw      = $_POST['new_password']       ?? '';
        $confirm_pw  = $_POST['confirm_password']   ?? '';

        if (empty($current_pw) || empty($new_pw) || empty($confirm_pw)) {
            $messages[] = ['type' => 'error', 'text' => 'All password fields are required.'];
        } elseif (strlen($new_pw) < 8) {
            $messages[] = ['type' => 'error', 'text' => 'New password must be at least 8 characters.'];
        } elseif ($new_pw !== $confirm_pw) {
            $messages[] = ['type' => 'error', 'text' => 'New password and confirmation do not match.'];
        } else {
            // Verify current password
            $vfy = $conn->prepare("SELECT password_hash FROM Users WHERE user_id = ?");
            $vfy->bind_param("i", $user_id);
            $vfy->execute();
            $hash_row = $vfy->get_result()->fetch_assoc();
            $vfy->close();

            if (!$hash_row || !password_verify($current_pw, $hash_row['password_hash'])) {
                $messages[] = ['type' => 'error', 'text' => 'Current password is incorrect.'];
            } else {
                $new_hash = password_hash($new_pw, PASSWORD_DEFAULT);
                $upd_pw   = $conn->prepare("UPDATE Users SET password_hash = ? WHERE user_id = ?");
                $upd_pw->bind_param("si", $new_hash, $user_id);

                if ($upd_pw->execute()) {
                    $messages[] = ['type' => 'success', 'text' => 'Password changed successfully.'];
                } else {
                    $messages[] = ['type' => 'error', 'text' => 'A database error occurred. Please try again.'];
                }
                $upd_pw->close();
            }
        }
    }
}

$display_username = htmlspecialchars($username);
$display_email    = htmlspecialchars($email);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - Sonoresu</title>
    <link rel="stylesheet" href="global.css">
    <style>
        .page-container {
            max-width: 700px; margin: 110px auto 60px auto; padding: 0 20px;
        }

        .page-header { margin-bottom: 36px; }
        .page-header h1 { font-size: 2rem; font-weight: 700; margin-bottom: 6px; }
        .page-header p  { color: var(--muted); font-size: 0.95rem; }

        /* Alert banners */
        .alert {
            padding: 14px 18px; border-radius: 10px; font-size: 0.9rem;
            margin-bottom: 24px; display: flex; align-items: center; gap: 12px;
        }
        .alert.error   { background: rgba(255,107,107,0.1); border: 1px solid rgba(255,107,107,0.3); color: var(--danger); }
        .alert.success { background: rgba(29,185,84,0.1);   border: 1px solid rgba(29,185,84,0.3);   color: var(--green);  }
        .alert svg { flex-shrink: 0; }

        /* Section cards */
        .section-card {
            background: var(--surface); border: 1px solid var(--border);
            border-radius: 20px; padding: 32px; margin-bottom: 24px;
        }
        .section-card h2 {
            font-size: 1.15rem; font-weight: 700; margin-bottom: 6px;
            display: flex; align-items: center; gap: 10px;
        }
        .section-card h2 svg { color: var(--accent2); }
        .section-card p.section-desc { color: var(--muted); font-size: 0.88rem; margin-bottom: 24px; }
        .section-divider { border: none; border-top: 1px solid var(--border); margin: 0 0 24px 0; }

        /* Form controls */
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-size: 0.84rem; font-weight: 600; margin-bottom: 8px; color: #d1d1d6; }
        .form-group input {
            width: 100%; background: #0b0b0f; border: 1px solid var(--border);
            border-radius: 10px; padding: 12px 16px; color: var(--text);
            font-size: 0.95rem; outline: none; transition: border-color 0.2s, box-shadow 0.2s;
            font-family: inherit;
        }
        .form-group input:focus { border-color: var(--accent); box-shadow: 0 0 0 3px rgba(108,99,255,0.15); }
        .form-hint { font-size: 0.78rem; color: var(--muted); margin-top: 6px; }

        /* Buttons */
        .btn-row { display: flex; justify-content: flex-end; gap: 12px; margin-top: 8px; }
        .btn {
            padding: 11px 24px; border-radius: 10px; font-size: 0.9rem;
            font-weight: 600; cursor: pointer; border: none; transition: all 0.2s;
            font-family: inherit;
        }
        .btn-primary { background: var(--accent); color: #fff; box-shadow: 0 4px 12px rgba(108,99,255,0.3); }
        .btn-primary:hover  { background: #5b52e6; }
        .btn-primary:active { transform: scale(0.97); }
        .btn-ghost  { background: transparent; color: var(--muted); border: 1px solid var(--border); }
        .btn-ghost:hover { color: var(--text); border-color: var(--muted); }

        /* Danger zone */
        .danger-note {
            background: rgba(255,107,107,0.06); border: 1px solid rgba(255,107,107,0.2);
            border-radius: 12px; padding: 16px 18px; margin-top: 8px;
            color: var(--muted); font-size: 0.88rem; line-height: 1.6;
        }

        @media (max-width: 600px) {
            .form-row { grid-template-columns: 1fr; }
            .btn-row { flex-direction: column; }
            .btn { width: 100%; text-align: center; }
        }
    </style>
</head>
<body>

    <nav>
        <a class="logo" href="sonorous_couch.php">SONORESU</a>
        <div class="nav-right">
            <span class="username-badge">Hello, <?php echo $display_username; ?></span>
            <div class="nav-links">
                <a href="sonorous_couch.php">Terminal</a>
                <a href="profile.php" class="active">My Playlists</a>
                <a href="lounge.php">Global Lounge</a>
                <a href="logout.php" class="logout-btn">Log out</a>
            </div>
        </div>
    </nav>

    <div class="page-container">

        <div class="page-header">
            <h1>Edit Profile</h1>
            <p>Update your account identity or change your password below.</p>
        </div>

        <?php foreach ($messages as $msg): ?>
        <div class="alert <?php echo $msg['type']; ?>">
            <?php if ($msg['type'] === 'success'): ?>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>
            <?php else: ?>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <?php endif; ?>
            <?php echo htmlspecialchars($msg['text']); ?>
        </div>
        <?php endforeach; ?>

        <!-- ── Section 1: Identity ── -->
        <div class="section-card">
            <h2>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                Account Identity
            </h2>
            <p class="section-desc">Change your display name or email address. Both must be unique across all accounts.</p>
            <hr class="section-divider">

            <form method="POST" action="editProfile.php">
                <input type="hidden" name="section" value="identity">

                <div class="form-row">
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="username"
                               value="<?php echo $display_username; ?>"
                               minlength="3" maxlength="30" required>
                        <p class="form-hint">3–30 characters. Must be unique.</p>
                    </div>
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" name="email"
                               value="<?php echo $display_email; ?>"
                               required>
                        <p class="form-hint">Must be unique across all accounts.</p>
                    </div>
                </div>

                <div class="btn-row">
                    <a href="profile.php" class="btn btn-ghost">Cancel</a>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>

        <!-- ── Section 2: Password ── -->
        <div class="section-card">
            <h2>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                Change Password
            </h2>
            <p class="section-desc">Enter your current password to confirm your identity, then set a new one.</p>
            <hr class="section-divider">

            <form method="POST" action="editProfile.php">
                <input type="hidden" name="section" value="password">

                <div class="form-group">
                    <label>Current Password</label>
                    <input type="password" name="current_password" placeholder="Your current password" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>New Password</label>
                        <input type="password" name="new_password" placeholder="At least 8 characters" required minlength="8">
                    </div>
                    <div class="form-group">
                        <label>Confirm New Password</label>
                        <input type="password" name="confirm_password" placeholder="Repeat new password" required minlength="8">
                    </div>
                </div>

                <div class="btn-row">
                    <button type="submit" class="btn btn-primary">Update Password</button>
                </div>
            </form>
        </div>

        <!-- ── Danger note ── -->
        <div class="danger-note">
            <strong style="color: var(--danger);">Account Deletion</strong><br>
            To permanently delete your account and all associated playlists, please contact support.
            This action cannot be undone.
        </div>

    </div>
</body>
</html>