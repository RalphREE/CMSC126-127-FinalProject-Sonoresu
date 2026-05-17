<?php
// public/login.php
session_start();
require_once '../config/db.php';

$login_error = "";
$show_login_prompt = false;

if (isset($_SESSION['user_id'])) {
    header("Location: sonorous_couch.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['vibe_input']) && !empty($_POST['vibe_input'])) {
        $show_login_prompt = true;
        $_SESSION['temp_vibe'] = $_POST['vibe_input'];
    }

    elseif (isset($_POST['email']) && isset($_POST['password'])) {
        $email    = trim($_POST['email']);
        $password = $_POST['password'];

        if (empty($email) || empty($password)) {
            $login_error = "Please enter both email and password.";
        } else {
            $stmt = $conn->prepare("SELECT user_id, username, password_hash FROM Users WHERE email = ?");
            if ($stmt) {
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($user = $result->fetch_assoc()) {
                    if (password_verify($password, $user['password_hash'])) {
                        $_SESSION['user_id']  = $user['user_id'];
                        $_SESSION['username'] = $user['username'];
                        header("Location: sonorous_couch.php");
                        exit;
                    } else {
                        $login_error = "Invalid email or password.";
                    }
                } else {
                    $login_error = "Invalid email or password.";
                }
                $stmt->close();
            } else {
                $login_error = "Database connection error.";
            }
        }
    }
}

$opm_songs = [
    ["title" => "Ang Huling El Bimbo", "artist" => "Eraserheads"],
    ["title" => "Kathang Isip",         "artist" => "Ben&Ben"],
    ["title" => "Mundo",                "artist" => "IV Of Spades"],
    ["title" => "Raining in Manila",    "artist" => "Lola Amour"],
    ["title" => "Jopay",                "artist" => "Mayonnaise"],
    ["title" => "Tadhana",              "artist" => "Up Dharma Down"],
    ["title" => "Uhaw (Tayong Lahat)",  "artist" => "Dilaw"],
    ["title" => "Pasilyo",              "artist" => "SunKissed Lola"],
    ["title" => "Ere",                  "artist" => "juan karlos"],
    ["title" => "Leaves",               "artist" => "Ben&Ben"],
    ["title" => "Ikaw Lang",            "artist" => "NOBITA"],
    ["title" => "Mahika",               "artist" => "Adie, Janine Berdin"],
];
shuffle($opm_songs);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sonoresu - Login</title>
    <link rel="stylesheet" href="global.css">
    <style>
        .brand-header { text-align: center; margin-bottom: 60px; margin-top: 20px; }
        .brand-header h1 {
            font-family: 'Space Mono', monospace; font-size: 2.5rem; letter-spacing: 0.15em;
            background: linear-gradient(to right, #ffffff, var(--accent));
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
        }

        .landing-layout {
            display: grid; grid-template-columns: 1.2fr 400px; gap: 60px;
            margin-bottom: 80px; align-items: center;
        }

        .hero-section h2 { font-size: 2.8rem; font-weight: 700; margin-bottom: 20px; line-height: 1.1; letter-spacing: -0.02em; }
        .hero-section p  { color: var(--muted); font-size: 1.1rem; margin-bottom: 40px; line-height: 1.6; max-width: 90%; }

        .vibe-box {
            background: var(--surface); color: var(--text);
            border-radius: 20px; padding: 20px 25px; width: 100%;
            font-size: 1.1rem; border: 1px solid var(--border);
            outline: none; box-shadow: 0 10px 30px rgba(0,0,0,0.4);
            transition: all 0.3s; margin-bottom: 20px;
        }
        .vibe-box:focus { border-color: var(--accent); box-shadow: 0 0 0 4px rgba(108,99,255,0.2), 0 10px 30px rgba(0,0,0,0.4); }

        .auth-prompt-banner {
            background: rgba(108,99,255,0.15); border: 1px solid var(--accent);
            padding: 14px 20px; border-radius: 12px; color: #b7b3ff; font-weight: 500;
            display: flex; align-items: center; gap: 12px;
            animation: slideDown 0.4s cubic-bezier(0.4,0,0.2,1); margin-bottom: 30px;
        }
        @keyframes slideDown { from { opacity:0; transform:translateY(-10px); } to { opacity:1; transform:translateY(0); } }

        /* Featured track card */
        .featured-track {
            background: linear-gradient(135deg, rgba(28,28,36,0.8) 0%, rgba(20,20,26,0.8) 100%);
            border: 1px solid var(--border); border-radius: 16px; padding: 20px;
            display: flex; align-items: center; gap: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3); backdrop-filter: blur(10px);
            max-width: 450px;
        }
        .featured-art {
            width: 80px; height: 80px; border-radius: 12px;
            background: linear-gradient(45deg, #1a1a2e, #2a2a45);
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.5); border: 1px solid #333;
            flex-shrink: 0;
        }
        .featured-art svg { opacity: 0.7; }
        .featured-info .badge {
            display: inline-block; padding: 4px 10px; background: rgba(255,255,255,0.1);
            border-radius: 20px; font-size: 0.7rem; font-family: 'Space Mono', monospace;
            text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; color: var(--muted);
        }
        .featured-info h4 { font-size: 1.2rem; margin-bottom: 4px; }
        .featured-info p  { color: var(--accent); font-weight: 600; font-size: 0.95rem; margin: 0; }

        /* Login card */
        .login-card {
            background: var(--surface); padding: 40px 30px;
            border-radius: 24px; border: 1px solid var(--surface2);
            box-shadow: 0 20px 50px rgba(0,0,0,0.6);
        }
        .login-card h3 { font-size: 1.6rem; margin-bottom: 24px; text-align: center; font-weight: 700; }

        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 8px; color: #d1d1d6; }
        .form-group input {
            width: 100%; background: #0b0b0f; border: 1px solid var(--border);
            border-radius: 10px; padding: 14px 16px; color: var(--text);
            font-size: 0.95rem; outline: none; transition: border-color 0.2s, box-shadow 0.2s;
        }
        .form-group input:focus { border-color: var(--accent); box-shadow: 0 0 0 3px rgba(108,99,255,0.15); }

        .btn-submit {
            width: 100%; background: var(--accent); color: #fff; border: none;
            border-radius: 10px; padding: 14px; font-size: 1rem; font-weight: 600;
            cursor: pointer; transition: background 0.2s, transform 0.1s; margin-top: 10px;
        }
        .btn-submit:hover  { background: #5b52e6; }
        .btn-submit:active { transform: scale(0.98); }

        .register-link { text-align: center; margin-top: 24px; font-size: 0.9rem; color: var(--muted); }
        .register-link a { color: var(--accent); text-decoration: none; font-weight: 600; }
        .register-link a:hover { text-decoration: underline; }

        .error-msg {
            color: var(--danger); font-size: 0.9rem; margin-bottom: 20px; text-align: center;
            background: rgba(255,107,107,0.1); padding: 12px; border-radius: 8px;
            border: 1px solid rgba(255,107,107,0.2);
        }

        /* Quick picks */
        .quick-picks h3 { margin-bottom: 24px; font-size: 1.4rem; font-weight: 600; }
        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; }
        .pick-item {
            background: var(--surface); padding: 14px; border-radius: 12px;
            display: flex; align-items: center; gap: 16px; border: 1px solid var(--surface2);
            transition: transform 0.2s, border-color 0.2s; cursor: pointer;
        }
        .pick-item:hover { transform: translateY(-3px); border-color: var(--border); }
        .square {
            width: 50px; height: 50px;
            background: linear-gradient(135deg, #2a2a35 0%, #1a1a24 100%);
            border-radius: 8px; display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .square svg { opacity: 0.6; }
        .song-meta strong { display: block; font-size: 0.95rem; margin-bottom: 4px; color: var(--text); }
        .song-meta small  { color: var(--muted); font-size: 0.8rem; }

        @media (max-width: 900px) {
            .landing-layout { grid-template-columns: 1fr; gap: 40px; }
            .login-card { max-width: 500px; margin: 0 auto; width: 100%; }
            .featured-track { max-width: 100%; }
        }
    </style>
</head>
<body>
    <div class="container" style="max-width:1200px;">
        <header class="brand-header">
            <h1>SONORESU</h1>
        </header>

        <main class="landing-layout">
            <section class="hero-section">
                <h2>What's your soundtrack right now?</h2>
                <p>Describe your current feeling, a scene, or a moment. Our AI will parse your vibe and generate the perfect playlist for your exact frequency.</p>

                <form method="POST" action="login.php">
                    <input type="text" name="vibe_input" class="vibe-box"
                           placeholder="e.g. late night driving with the windows down..."
                           autocomplete="off">
                </form>

                <?php if ($show_login_prompt): ?>
                <div class="auth-prompt-banner">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    <div>
                        <strong style="display:block; margin-bottom:2px;">Vibe captured!</strong>
                        <span style="font-size:0.9rem;">Please log in or register to generate your custom playlist.</span>
                    </div>
                </div>
                <?php endif; ?>

                <div class="featured-track">
                    <div class="featured-art">
                        <!-- Music waveform icon -->
                        <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="var(--accent2)" stroke-width="1.5" stroke-linecap="round">
                            <path d="M9 18V5l12-2v13"/>
                            <circle cx="6" cy="18" r="3"/>
                            <circle cx="18" cy="16" r="3"/>
                        </svg>
                    </div>
                    <div class="featured-info">
                        <span class="badge">Trending Vibe</span>
                        <h4>Come Inside Of My Heart</h4>
                        <p>IV Of Spades</p>
                    </div>
                </div>
            </section>

            <aside class="login-card" id="loginBox">
                <h3>Welcome Back</h3>
                <?php if ($login_error): ?>
                <div class="error-msg"><?php echo htmlspecialchars($login_error); ?></div>
                <?php endif; ?>

                <form method="POST" action="login.php">
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" name="email" placeholder="you@email.com" required
                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                               autofocus="<?php echo $show_login_prompt ? 'true' : 'false'; ?>">
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" placeholder="••••••••" required>
                    </div>
                    <button type="submit" class="btn-submit">Enter</button>
                </form>

                <div class="register-link">
                    New to Sonoresu? <a href="register.php">Create an account</a>
                </div>
            </aside>
        </main>

        <section class="quick-picks">
            <h3>Trending OPM Frequencies</h3>
            <div class="grid">
                <?php
                $display_songs = array_slice($opm_songs, 0, 9);
                foreach ($display_songs as $song):
                ?>
                <div class="pick-item">
                    <div class="square">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="var(--accent2)" stroke-width="1.5" stroke-linecap="round">
                            <circle cx="12" cy="12" r="2"/>
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2z"/>
                        </svg>
                    </div>
                    <div class="song-meta">
                        <strong><?php echo htmlspecialchars($song['title']); ?></strong>
                        <small><?php echo htmlspecialchars($song['artist']); ?></small>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
    </div>

    <?php if ($show_login_prompt): ?>
    <script>
        if (window.innerWidth <= 900) {
            document.getElementById('loginBox').scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    </script>
    <?php endif; ?>
</body>
</html>