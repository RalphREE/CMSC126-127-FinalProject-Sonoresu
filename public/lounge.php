<?php
// public/lounge.php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$username = htmlspecialchars($_SESSION['username'] ?? 'User');

// Dummy data for visual representation
$global_feed = [
    ["username" => "Alex99", "mood" => "Staring out a train window at night, cinematic...", "track" => "Midnight City", "artist" => "M83", "likes" => 124],
    ["username" => "SarahVibes", "mood" => "Need chaotic energy to finish this essay due in 1 hour.", "track" => "Gosh", "artist" => "Jamie xx", "likes" => 89],
    ["username" => "LoFi_King", "mood" => "Sunday morning making pancakes, golden hour light.", "track" => "Sunflower", "artist" => "Rex Orange County", "likes" => 210],
    ["username" => "CyberPunk2077", "mood" => "Neon lights, heavy rain, feeling like a replicant.", "track" => "Nightcity", "artist" => "The Algorithm", "likes" => 45],
    ["username" => "ZenMaster", "mood" => "Deep breathing, clearing the mind, floating in space.", "track" => "Weightless", "artist" => "Marconi Union", "likes" => 302],
    ["username" => "RockerGirl", "mood" => "Just quit my job. Give me unadulterated rebellion.", "track" => "Rebel Girl", "artist" => "Bikini Kill", "likes" => 156],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Global Lounge - Sonoresu</title>
<link rel="stylesheet" href="global.css">
<style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        nav {
            position: fixed; top: 0; left: 0; right: 0;
            display: flex; align-items: center; justify-content: space-between;
            padding: 16px 40px; border-bottom: 1px solid var(--border);
            background: rgba(11,11,15,.8); backdrop-filter: blur(16px); z-index: 100;
        }
        .logo {
            font-family: 'Space Mono', monospace; font-weight: 700; letter-spacing: .2em;
            background: linear-gradient(to right, #fff, var(--accent2));
            -webkit-background-clip: text; -webkit-text-fill-color: transparent; text-decoration: none;
        }
        .nav-right { display: flex; align-items: center; gap: 20px; }
        .username-badge {
            font-family: 'Space Mono', monospace; font-size: 0.9rem; color: var(--accent2);
            padding-right: 20px; border-right: 1px solid var(--border);
        }
        .nav-links { display: flex; align-items: center; gap: 25px; font-size: 0.95rem; font-weight: 500; }
        .nav-links a { color: var(--muted); text-decoration: none; transition: color 0.2s; }
        .nav-links a:hover, .nav-links a.active { color: var(--text); }
        .nav-links a.active { border-bottom: 2px solid var(--accent); padding-bottom: 4px; }
        .nav-links a.logout-btn { color: var(--danger); margin-left: 10px; font-weight: 600; }
        .nav-links a.logout-btn:hover { color: #ff4b4b; border-bottom: none; }

        /* Page Layout */
        .container { max-width: 1200px; margin: 100px auto 40px auto; padding: 0 20px; }
        .page-header { text-align: center; margin-bottom: 50px; }
        .page-header h1 { font-size: 2.5rem; font-weight: 700; margin-bottom: 10px; }
        .page-header p { color: var(--muted); font-size: 1.1rem; max-width: 600px; margin: 0 auto; }
        .feed-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 24px; }

        /* Social Card Component */
        .feed-card { background: var(--surface); border: 1px solid var(--border); border-radius: 20px; padding: 24px; transition: transform 0.2s, box-shadow 0.2s; display: flex; flex-direction: column; }
        .feed-card:hover { transform: translateY(-4px); box-shadow: 0 10px 30px rgba(0,0,0,0.4), 0 0 0 1px var(--accent-glow); border-color: #38384a; }
        .card-header { display: flex; align-items: center; gap: 12px; margin-bottom: 16px; }
        .user-avi { width: 36px; height: 36px; border-radius: 50%; background: linear-gradient(135deg, var(--accent) 0%, #000 100%); display: flex; align-items: center; justify-content: center; font-size: 0.8rem; font-weight: bold; }
        .username { font-weight: 600; font-size: 0.95rem; }
        .card-mood { background: rgba(255,255,255,0.03); border-left: 3px solid var(--accent); padding: 12px 16px; border-radius: 0 8px 8px 0; font-style: italic; font-size: 0.95rem; color: #d1d1d6; margin-bottom: 20px; line-height: 1.5; }
        .card-track { display: flex; align-items: center; gap: 16px; background: var(--surface2); padding: 12px; border-radius: 12px; margin-bottom: 20px; margin-top: auto; }
        .track-art { width: 48px; height: 48px; background: #2a2a35; border-radius: 8px; display:flex; align-items:center; justify-content:center; }
        .track-info strong { display: block; font-size: 0.95rem; margin-bottom: 2px; }
        .track-info small { color: var(--muted); font-size: 0.8rem; }
        .card-actions { display: flex; align-items: center; justify-content: space-between; border-top: 1px solid var(--border); padding-top: 16px; }
        .action-group { display: flex; gap: 16px; }
        .action-btn { background: none; border: none; color: var(--muted); display: flex; align-items: center; gap: 6px; font-size: 0.9rem; font-weight: 600; cursor: pointer; transition: color 0.2s; }
        .action-btn:hover { color: var(--text); }
        .action-btn.heart:hover { color: #ff4b4b; }
        .action-btn.duplicate:hover { color: var(--green); }
        .action-icon { font-size: 1.2rem; }
        @media (max-width: 768px) { .feed-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>

    <nav>
        <a class="logo" href="sonorous_couch.php">SONORESU</a>
        <div class="nav-right">
            <span class="username-badge">Hello, <?php echo $username; ?></span>
            <div class="nav-links">
                <a href="sonorous_couch.php">Terminal</a>
                <a href="history.php">My Playlists</a>
                <a href="lounge.php" class="active">Global Lounge</a>
                <a href="logout.php" class="logout-btn">Log out</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <header class="page-header">
            <h1>Global Lounge</h1>
            <p>See what frequencies other users are vibrating at right now. Discover new prompts and steal their soundtracks.</p>
        </header>

        <div class="feed-grid">
            <?php foreach($global_feed as $post): ?>
            <div class="feed-card">
                <div class="card-header">
                    <div class="user-avi"><?php echo substr($post['username'], 0, 1); ?></div>
                    <span class="username">@<?php echo htmlspecialchars($post['username']); ?></span>
                </div>
                <div class="card-mood">"<?php echo htmlspecialchars($post['mood']); ?>"</div>
                <div class="card-track">
                    <div class="track-art">🎵</div>
                    <div class="track-info">
                        <strong><?php echo htmlspecialchars($post['track']); ?></strong>
                        <small><?php echo htmlspecialchars($post['artist']); ?> &bull; AI Generated</small>
                    </div>
                </div>
                <div class="card-actions">
                    <div class="action-group">
                        <button class="action-btn heart">
                            <span class="action-icon">♡</span> <span><?php echo $post['likes']; ?></span>
                        </button>
                    </div>
                    <button class="action-btn duplicate">
                        <span class="action-icon">⎘</span> <span>Save to Mine</span>
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

</body>
</html>