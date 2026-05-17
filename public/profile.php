<?php
// public/profile.php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$username = htmlspecialchars($_SESSION['username'] ?? 'User');

// Dummy data — replace with real DB query once connected
$dummy_playlists = [
    ["title" => "Rainy Sunday Vibes",    "prompt" => "Rain on a Sunday, cup of tea, no plans...",      "date" => "Oct 24, 2023"],
    ["title" => "Late Night Coding",     "prompt" => "Need focus, cyberpunk energy, lots of caffeine",  "date" => "Oct 22, 2023"],
    ["title" => "Morning Commute",       "prompt" => "Walking in the city, crisp air, feeling optimistic", "date" => "Oct 18, 2023"],
    ["title" => "Gym Adrenaline",        "prompt" => "Heavy weights, aggressive bass, high energy",     "date" => "Oct 15, 2023"],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Playlists - Sonoresu</title>
    <link rel="stylesheet" href="global.css">
    <style>
        /* Main Layout */
        .container    { max-width: 1100px; margin: 100px auto 40px auto; padding: 0 20px; }
        .history-layout { display: grid; grid-template-columns: 300px 1fr; gap: 40px; }

        /* Sidebar */
        .profile-sidebar {
            background: var(--surface); border: 1px solid var(--border);
            border-radius: 24px; padding: 40px 20px; text-align: center;
            height: fit-content; position: sticky; top: 100px;
        }
        .profile-avatar {
            width: 160px; height: 160px; margin: 0 auto 20px auto;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--surface2) 0%, #0b0b0f 100%);
            border: 2px solid var(--accent);
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 0 30px rgba(108,99,255,0.2);
            position: relative;
        }
        .profile-avatar svg { opacity: 0.7; }
        .profile-avatar::after {
            content: ''; position: absolute; inset: -6px; border-radius: 50%;
            border: 1px dashed var(--accent2);
            animation: spin 20s linear infinite;
        }
        @keyframes spin { 100% { transform: rotate(360deg); } }

        .profile-name  { font-size: 1.5rem; font-weight: 700; margin-bottom: 8px; }
        .profile-stats { font-family: 'Space Mono', monospace; color: var(--accent2); font-size: 0.85rem; }

        /* Edit profile button in sidebar */
        .btn-edit-profile {
            display: inline-flex; align-items: center; gap: 8px;
            margin-top: 20px; padding: 10px 20px;
            background: transparent; border: 1px solid var(--border);
            border-radius: 10px; color: var(--muted); font-size: 0.88rem;
            font-weight: 600; text-decoration: none; transition: all 0.2s;
            font-family: inherit;
        }
        .btn-edit-profile:hover { border-color: var(--accent); color: var(--text); }

        /* Playlist header */
        .playlist-header-card {
            background: linear-gradient(135deg, var(--surface2) 0%, var(--surface) 100%);
            border: 1px solid var(--border); border-radius: 20px;
            padding: 30px; margin-bottom: 30px;
        }
        .playlist-header-card h2 { font-size: 1.8rem; margin-bottom: 10px; }
        .playlist-header-card p  { color: var(--muted); max-width: 80%; line-height: 1.6; }

        /* Playlist list */
        .list-stack { display: flex; flex-direction: column; gap: 16px; }
        .list-item {
            background: var(--surface); border: 1px solid var(--border);
            border-radius: 16px; padding: 20px 24px;
            display: flex; align-items: center; justify-content: space-between;
            transition: all 0.3s ease; cursor: pointer;
        }
        .list-item:hover {
            background: var(--surface2); transform: translateX(5px);
            border-color: var(--accent); box-shadow: 0 4px 20px rgba(0,0,0,0.3);
        }
        .item-info { display: flex; align-items: center; gap: 20px; }
        .item-icon {
            width: 50px; height: 50px; border-radius: 12px;
            background: linear-gradient(135deg, var(--border), #111);
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .item-icon svg { opacity: 0.6; }
        .item-text strong { display: block; font-size: 1.1rem; margin-bottom: 4px; }
        .item-text .prompt { color: var(--muted); font-size: 0.9rem; font-style: italic; margin-bottom: 4px; }
        .item-text .date   { font-family: 'Space Mono', monospace; font-size: 0.75rem; color: var(--accent2); }
        .play-btn {
            width: 40px; height: 40px; border-radius: 50%;
            background: var(--text); color: var(--bg);
            display: flex; align-items: center; justify-content: center;
            font-size: 0.9rem; transition: transform 0.2s, background 0.2s;
            flex-shrink: 0;
        }
        .list-item:hover .play-btn { background: var(--accent); color: #fff; transform: scale(1.1); }

        @media (max-width: 850px) {
            .history-layout { grid-template-columns: 1fr; }
            .profile-sidebar { position: static; }
        }
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
            <!-- Headphones icon replacing emoji -->
            <div class="profile-avatar">
                <svg width="72" height="72" viewBox="0 0 24 24" fill="none" stroke="var(--accent2)" stroke-width="1.2" stroke-linecap="round">
                    <path d="M3 18v-6a9 9 0 0 1 18 0v6"/>
                    <path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3z"/>
                    <path d="M3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"/>
                </svg>
            </div>

            <h3 class="profile-name"><?php echo $username; ?></h3>
            <p class="profile-stats">Music Enjoyer</p>

            <a href="editProfile.php" class="btn-edit-profile">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                Edit Profile
            </a>

            <div style="margin-top: 24px; border-top: 1px solid var(--border); padding-top: 20px; text-align: left; color: var(--muted); font-size: 0.9rem; line-height: 1.8;">
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
                <?php if (empty($dummy_playlists)): ?>
                <p style="color: var(--muted);">
                    You haven't generated any mood capsules yet.
                    <a href="sonorous_couch.php" style="color: var(--accent);">Create one now!</a>
                </p>
                <?php else: ?>
                    <?php foreach ($dummy_playlists as $playlist): ?>
                    <div class="list-item">
                        <div class="item-info">
                            <!-- Disc icon replacing emoji -->
                            <div class="item-icon">
                                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="var(--accent2)" stroke-width="1.5" stroke-linecap="round">
                                    <circle cx="12" cy="12" r="10"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                            </div>
                            <div class="item-text">
                                <strong><?php echo htmlspecialchars($playlist['title']); ?></strong>
                                <div class="prompt">"<?php echo htmlspecialchars($playlist['prompt']); ?>"</div>
                                <div class="date"><?php echo htmlspecialchars($playlist['date']); ?></div>
                            </div>
                        </div>
                        <div class="play-btn">&#9654;</div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>

</body>
</html>