<?php
// public/lounge.php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$username = htmlspecialchars($_SESSION['username'] ?? 'User');

$global_feed = [
    ["username" => "Alex99",      "mood" => "Staring out a train window at night, cinematic...",       "track" => "Midnight City",  "artist" => "M83",               "likes" => 124],
    ["username" => "SarahVibes",  "mood" => "Need chaotic energy to finish this essay due in 1 hour.", "track" => "Gosh",           "artist" => "Jamie xx",          "likes" => 89],
    ["username" => "LoFi_King",   "mood" => "Sunday morning making pancakes, golden hour light.",      "track" => "Sunflower",      "artist" => "Rex Orange County", "likes" => 210],
    ["username" => "CyberPunk",   "mood" => "Neon lights, heavy rain, feeling like a replicant.",      "track" => "Nightcity",      "artist" => "The Algorithm",     "likes" => 45],
    ["username" => "ZenMaster",   "mood" => "Deep breathing, clearing the mind, floating in space.",   "track" => "Weightless",     "artist" => "Marconi Union",     "likes" => 302],
    ["username" => "RockerGirl",  "mood" => "Just quit my job. Give me unadulterated rebellion.",      "track" => "Rebel Girl",     "artist" => "Bikini Kill",       "likes" => 156],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Global Lounge - Sonoresu</title>
    <link rel="stylesheet" href="global.css">
    <style>
        .container   { max-width: 1200px; margin: 100px auto 40px auto; padding: 0 20px; }
        .page-header { text-align: center; margin-bottom: 50px; }
        .page-header h1 { font-size: 2.5rem; font-weight: 700; margin-bottom: 10px; }
        .page-header p  { color: var(--muted); font-size: 1.1rem; max-width: 600px; margin: 0 auto; }
        .feed-grid   { display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 24px; }

        /* Social Card */
        .feed-card {
            background: var(--surface); border: 1px solid var(--border); border-radius: 20px;
            padding: 24px; transition: transform 0.2s, box-shadow 0.2s;
            display: flex; flex-direction: column;
        }
        .feed-card:hover { transform: translateY(-4px); box-shadow: 0 10px 30px rgba(0,0,0,0.4); border-color: #38384a; }

        .card-header { display: flex; align-items: center; gap: 12px; margin-bottom: 16px; }
        .user-avi {
            width: 36px; height: 36px; border-radius: 50%;
            background: linear-gradient(135deg, var(--accent) 0%, #000 100%);
            display: flex; align-items: center; justify-content: center;
            font-size: 0.8rem; font-weight: bold;
        }
        .username { font-weight: 600; font-size: 0.95rem; }

        .card-mood {
            background: rgba(255,255,255,0.03); border-left: 3px solid var(--accent);
            padding: 12px 16px; border-radius: 0 8px 8px 0;
            font-style: italic; font-size: 0.95rem; color: #d1d1d6;
            margin-bottom: 20px; line-height: 1.5;
        }

        .card-track {
            display: flex; align-items: center; gap: 16px;
            background: var(--surface2); padding: 12px; border-radius: 12px;
            margin-bottom: 20px; margin-top: auto;
        }
        .track-art {
            width: 48px; height: 48px; background: #2a2a35; border-radius: 8px;
            display: flex; align-items: center; justify-content: center; flex-shrink: 0;
        }
        .track-art svg { opacity: 0.6; }
        .track-info strong { display: block; font-size: 0.95rem; margin-bottom: 2px; }
        .track-info small  { color: var(--muted); font-size: 0.8rem; }

        .card-actions {
            display: flex; align-items: center; justify-content: space-between;
            border-top: 1px solid var(--border); padding-top: 16px;
        }
        .action-group { display: flex; gap: 16px; }
        .action-btn {
            background: none; border: none; color: var(--muted);
            display: flex; align-items: center; gap: 6px;
            font-size: 0.9rem; font-weight: 600; cursor: pointer; transition: color 0.2s;
            font-family: inherit;
        }
        .action-btn:hover          { color: var(--text); }
        .action-btn.heart:hover    { color: #ff4b4b; }
        .action-btn.duplicate:hover { color: var(--green); }

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
                <a href="profile.php">My Playlists</a>
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
            <?php foreach ($global_feed as $post): ?>
            <div class="feed-card">
                <div class="card-header">
                    <div class="user-avi"><?php echo substr($post['username'], 0, 1); ?></div>
                    <span class="username">@<?php echo htmlspecialchars($post['username']); ?></span>
                </div>

                <div class="card-mood">"<?php echo htmlspecialchars($post['mood']); ?>"</div>

                <div class="card-track">
                    <!-- Music note icon replacing emoji -->
                    <div class="track-art">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--accent2)" stroke-width="1.5" stroke-linecap="round">
                            <path d="M9 18V5l12-2v13"/>
                            <circle cx="6" cy="18" r="3"/>
                            <circle cx="18" cy="16" r="3"/>
                        </svg>
                    </div>
                    <div class="track-info">
                        <strong><?php echo htmlspecialchars($post['track']); ?></strong>
                        <small><?php echo htmlspecialchars($post['artist']); ?> &bull; AI Generated</small>
                    </div>
                </div>

                <div class="card-actions">
                    <div class="action-group">
                        <button class="action-btn heart">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                            <span><?php echo $post['likes']; ?></span>
                        </button>
                    </div>
                    <button class="action-btn duplicate">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                        Save to Mine
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

</body>
</html>