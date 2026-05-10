<?php
// public/login.php
session_start();
require_once '../config/db.php';

// 1. CAPTURE VIBE: If they enter a vibe before logging in, save it to the session
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['vibe_input'])) {
    $_SESSION['temp_vibe'] = $_POST['vibe_input'];
}

// 2. AUTHENTICATION: If they are already logged in, go to index
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// ... rest of your existing login logic (password verification, etc.) ...
// Inside your successful login block, ensure it redirects:
if (password_verify($password, $user['password_hash'])) {
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['username'] = $user['username'];
    
    // Redirect to index - the vibe is already safe in $_SESSION['temp_vibe']
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sonoresu</title>
    <style>
        :root {
            --bg-color: #b5ac99;
            --card-bg: #1e1e1e;
            --sidebar-bg: #8e8778;
            --sub-text: #b3b3b3;
        }

        body { 
            font-family: 'Segoe UI', sans-serif; 
            background-color: var(--bg-color); 
            color: #121212; 
            margin: 0; 
            padding: 0; 
        }

        .container { max-width: 1000px; margin: 0 auto; padding: 40px 20px; }
        
        .mood-header { text-align: center; margin-bottom: 50px; }
        .vibe-box {
            background: #000;
            color: #fff;
            border-radius: 50px;
            padding: 15px 30px;
            display: inline-block;
            border: 2px solid transparent;
            width: 100%;
            max-width: 450px;
            font-size: 1.1rem;
            outline: none;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        .dashboard-layout { 
            display: grid; 
            grid-template-columns: 1.5fr 1fr; 
            gap: 30px; 
            margin-bottom: 50px;
        }

        .player-card { 
            background: var(--card-bg); 
            color: white; 
            padding: 30px; 
            border-radius: 12px; 
        }
        
        .album-art { 
            width: 100%; 
            aspect-ratio: 1 / 1; 
            background: #333; 
            border-radius: 8px; 
            margin-bottom: 20px;
            overflow: hidden;
        }

        .album-art img { width: 100%; height: 100%; object-fit: cover; opacity: 0.7; }
        
        .sidebar-card { 
            background: var(--sidebar-bg); 
            padding: 30px; 
            border-radius: 12px; 
            border: 1px solid rgba(0,0,0,0.1);
        }

        .login-form input {
            width: 100%;
            padding: 12px;
            margin: 8px 0 18px 0;
            border-radius: 6px;
            border: none;
            background: rgba(255,255,255,0.9);
            box-sizing: border-box;
        }

        .login-btn {
            background: #000;
            color: #fff;
            border: none;
            padding: 14px;
            width: 100%;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
            letter-spacing: 1px;
        }

        /* Quick Picks Grid */
        .quick-picks { text-align: left; }
        .quick-picks h3 { margin-bottom: 20px; font-size: 1.4rem; }
        .grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); 
            gap: 20px; 
        }
        
        .pick-item { 
            background: rgba(255,255,255,0.2);
            padding: 12px;
            border-radius: 8px;
            display: flex; 
            align-items: center; 
            gap: 15px; 
        }
        
        .square { width: 55px; height: 55px; background: #333; border-radius: 4px; flex-shrink: 0; }
        .song-meta strong { display: block; font-size: 0.9rem; }
        .song-meta small { color: #444; font-size: 0.75rem; }
    </style>
</head>
<body>
    <div class="container">
        
        <header class="mood-header">
            <h3>What's our mood for today?</h3>
            <form method="POST">
                <input type="text" name="vibe_input" class="vibe-box" placeholder="✨ e.g. morning dawn sipping coffee" autocomplete="off">
            </form>
            <?php if ($vibe_message): ?>
                <p style="margin-top: 15px;"><strong><?php echo $vibe_message; ?></strong></p>
            <?php endif; ?>
        </header>

        <main class="dashboard-layout">
            <section class="player-card">
                <div class="album-art">
                    <img src="https://via.placeholder.com/400/333333/666666?text=Sonoresu+Visual" alt="Music Visual">
                </div>
                <h2>Ready to play?</h2>
                <p style="color: var(--sub-text);">Sign in to generate your custom vibe-based soundtrack.</p>
            </section>

            <aside class="sidebar-card">
                <h2 style="margin-top:0;">Sign In</h2>
                <?php if ($login_error): ?>
                    <p style="color: #721c24; font-size: 0.85rem;"><?php echo $login_error; ?></p>
                <?php endif; ?>
                
                <form class="login-form" method="POST">
                    <label style="font-size: 0.8rem; font-weight: bold;">EMAIL</label>
                    <input type="email" name="email" required placeholder="your@email.com">
                    
                    <label style="font-size: 0.8rem; font-weight: bold;">PASSWORD</label>
                    <input type="password" name="password" required placeholder="••••••••">
                    
                    <button type="submit" class="login-btn">ENTER</button>
                </form>
                <p style="margin-top: 20px; font-size: 0.85rem;">New here? <a href="register.php" style="color: #000; font-weight: bold;">Create account</a></p>
            </aside>
        </main>

        <section class="quick-picks">
            <h3>Quick picks</h3>
            <div class="grid">
                <?php for($i=1; $i<=9; $i++): ?>
                <div class="pick-item">
                    <div class="square"></div>
                    <div class="song-meta">
                        <strong>Track Title <?php echo $i; ?></strong>
                        <small>Artist Name</small>
                    </div>
                </div>
                <?php endfor; ?>
            </div>
        </section>

    </div>
</body>
</html>