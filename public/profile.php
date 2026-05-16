<?php
// public/profile.php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$username = htmlspecialchars($_SESSION['username'] ?? 'User');

// Dummy data for visual representation until hooked up to your DB
$dummy_playlists = [
    ["title" => "Rainy Sunday Vibes", "prompt" => "Rain on a Sunday, cup of tea, no plans...", "date" => "Oct 24, 2023"],
    ["title" => "Late Night Coding", "prompt" => "Need focus, cyberpunk energy, lots of caffeine", "date" => "Oct 22, 2023"],
    ["title" => "Morning Commute", "prompt" => "Walking in the city, crisp air, feeling optimistic", "date" => "Oct 18, 2023"],
    ["title" => "Gym Adrenaline", "prompt" => "Heavy weights, aggressive bass, high energy", "date" => "Oct 15, 2023"],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Playlists - Sonoresu</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
    <style>
        :root { --bg: #0b0b0f; --surface: #14141a; --surface2: #1c1c24; --border: #262633; --accent: #6c63ff; --accent2: #a78bfa; --green: #1db954; --text: #f3f3f5; --muted: #71717a; --danger: #ff6b6b; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: var(--bg); color: var(--text); min-height: 100vh; background-image: radial-gradient(circle at 10% 20%, rgba(108, 99, 255, 0.08) 0%, transparent 40%); }
        
        /* --- Unified Top Navigation --- */
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

        /* Main Layout */
        .container { max-width: 1100px; margin: 100px auto 40px auto; padding: 0 20px; }
        .history-layout { display: grid; grid-template-columns: 300px 1fr; gap: 40px; }
        .profile-sidebar { background: var(--surface); border: 1px solid var(--border); border-radius: 24px; padding: 40px 20px; text-align: center; height: fit-content; position: sticky; top: 100px; }
        .profile-avatar { width: 160px; height: 160px; margin: 0 auto 20px auto; border-radius: 50%; background: linear-gradient(135deg, var(--surface2), #000); border: 2px solid var(--accent); display: flex; align-items: center; justify-content: center; font-size: 4rem; box-shadow: 0 0 30px rgba(108, 99, 255, 0.2); position: relative; }
        .profile-avatar::after { content: ''; position: absolute; inset: -6px; border-radius: 50%; border: 1px dashed var(--accent2); animation: spin 20s linear infinite; }
        @keyframes spin { 100% { transform: rotate(360deg); } }
        .profile-name { font-size: 1.5rem; font-weight: 700; margin-bottom: 8px; }
        .profile-stats { font-family: 'Space Mono', monospace; color: var(--accent2); font-size: 0.85rem; }
        .playlist-header-card { background: linear-gradient(135deg, var(--surface2) 0%, var(--surface) 100%); border: 1px solid var(--border); border-radius: 20px; padding: 30px; margin-bottom: 30px; position: relative; overflow: hidden; }
        .playlist-header-card h2 { font-size: 1.8rem; margin-bottom: 10px; }
        .playlist-header-card p { color: var(--muted); max-width: 80%; line-height: 1.6; }
        .list-stack { display: flex; flex-direction: column; gap: 16px; }
        .list-item { background: var(--surface); border: 1px solid var(--border); border-radius: 16px; padding: 20px 24px; display: flex; align-items: center; justify-content: space-between; transition: all 0.3s ease; cursor: pointer; }
        .list-item:hover { background: var(--surface2); transform: translateX(5px); border-color: var(--accent); box-shadow: 0 4px 20px rgba(0,0,0,0.3); }
        .item-info { display: flex; align-items: center; gap: 20px; }
        .item-icon { width: 50px; height: 50px; border-radius: 12px; background: linear-gradient(45deg, var(--border), #111); display: flex; align-items: center; justify-content: center; font-size: 1.5rem; }
        .item-text strong { display: block; font-size: 1.1rem; margin-bottom: 4px; }
        .item-text .prompt { color: var(--muted); font-size: 0.9rem; font-style: italic; margin-bottom: 4px; }
        .item-text .date { font-family: 'Space Mono', monospace; font-size: 0.75rem; color: var(--accent2); }
        .play-btn { width: 40px; height: 40px; border-radius: 50%; background: var(--text); color: var(--bg); display: flex; align-items: center; justify-content: center; font-size: 1.2rem; transition: transform 0.2s, background 0.2s; }
        .list-item:hover .play-btn { background: var(--accent); color: #fff; transform: scale(1.1); }
        @media (max-width: 850px) { .history-layout { grid-template-columns: 1fr; } .profile-sidebar { position: static; } }
    </style>
</head>
<body>

    <nav>
        <a class="logo" href="sonorous_couch.php">SONORESU</a>
        <div class="nav-right">
            <span class="username-badge">Hello, <?php echo $username; ?></span>
            <div class="nav-links">
                <a href="sonorous_couch.php">Terminal</a>
                <a href="profile.php" class="active">My Playlists</a>
                <a href="lounge.php">Global Lounge</a>
                <a href="logout.php" class="logout-btn">Log out</a>
            </div>
        </div>
    </nav>

    <div class="container history-layout">
        <aside class="profile-sidebar">
            <div class="profile-avatar">🎧</div>
            <h3 class="profile-name"><?php echo $username; ?></h3>
            <p class="profile-stats">Music Enjoyer</p>
            <div style="margin-top: 30px; border-top: 1px solid var(--border); padding-top: 20px; text-align: left; color: var(--muted); font-size: 0.9rem; line-height: 1.8;">
                <p><strong>Capsules Generated:</strong> 24</p>
                <p><strong>Top Vibe:</strong> Melancholy</p>
                <p><strong>Joined:</strong> Fall 2023</p>
            </div>
        </aside>

        <main>
            <div class="playlist-header-card">
                <h2>Your Mood Capsules</h2>
                <p>A personal gallery of your previously generated soundtracks. Relive the exact frequency you were vibrating at on any given day.</p>
            </div>
            
            <div class="list-stack">
                <?php if(empty($dummy_playlists)): ?>
                    <p style="color: var(--muted);">You haven't generated any mood capsules yet. <a href="sonorous_couch.php" style="color: var(--accent);">Create one now!</a></p>
                <?php else: ?>
                    <?php foreach($dummy_playlists as $playlist): ?>
                    <div class="list-item">
                        <div class="item-info">
                            <div class="item-icon">💿</div>
                            <div class="item-text">
                                <strong><?php echo htmlspecialchars($playlist['title']); ?></strong>
                                <div class="prompt">"<?php echo htmlspecialchars($playlist['prompt']); ?>"</div>
                                <div class="date"><?php echo htmlspecialchars($playlist['date']); ?></div>
                            </div>
                        </div>
                        <div class="play-btn">▶</div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>

</body>
</html>