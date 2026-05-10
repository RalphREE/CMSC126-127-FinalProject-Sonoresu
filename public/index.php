<?php
// public/index.php
session_start();

// Authentication Guard: Redirect to login if the session variable isn't set
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$vibe_message = "";
// Capturing input - for now, it's stored but not processed by an API
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['vibe_input'])) {
    $vibe_message = "Current Vibe: " . htmlspecialchars($_POST['vibe_input']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - Sonoresu</title>
    <style>
        :root {
            --bg-color: #b5ac99;
            --card-bg: #1e1e1e;
            --sidebar-bg: #8e8778; /* Slightly darker than body for depth */
            --accent-text: #ffffff;
            --sub-text: #b3b3b3;
        }

        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background-color: var(--bg-color); 
            color: #121212; 
            margin: 0; 
            padding: 0; 
        }

        .container { max-width: 1000px; margin: 0 auto; padding: 40px 20px; }
        
        /* Header / Mood Section */
        .mood-header { text-align: center; margin-bottom: 50px; }
        .mood-header h3 { font-size: 1.8rem; margin-bottom: 15px; }
        
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
            transition: all 0.3s ease;
            outline: none;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        
        .vibe-box:focus { border-color: #fff; transform: translateY(-2px); }

        /* Content Layout */
        .dashboard-layout { 
            display: grid; 
            grid-template-columns: 1.5fr 1fr; 
            gap: 30px; 
            margin-bottom: 50px; 
        }

        /* Music Player Card */
        .player-card { 
            background: var(--card-bg); 
            color: white; 
            padding: 30px; 
            border-radius: 12px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        
        .album-art { 
            width: 100%; 
            aspect-ratio: 1 / 1; 
            background: #333; 
            border-radius: 8px; 
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 4rem;
        }
        
        .player-info h2 { margin: 0 0 5px 0; font-size: 1.5rem; }
        .player-info p { margin: 0; color: var(--sub-text); font-size: 1rem; }
        
        .controls { 
            display: flex; 
            justify-content: space-around; 
            align-items: center; 
            font-size: 1.8rem; 
            margin-top: 25px;
            cursor: pointer;
        }

        /* Sidebar / User Section */
        .sidebar-card { 
            background: var(--sidebar-bg); 
            padding: 25px; 
            border-radius: 12px; 
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            border: 1px solid rgba(0,0,0,0.1);
        }

        .user-status h4 { margin-top: 0; text-transform: uppercase; letter-spacing: 1px; font-size: 0.8rem; }
        .logout-btn { 
            display: inline-block;
            margin-top: 20px;
            color: #000;
            text-decoration: none;
            font-weight: bold;
            font-size: 0.9rem;
            border-bottom: 1px solid #000;
        }

        /* Quick Picks Grid */
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
            transition: background 0.2s;
            cursor: pointer;
        }
        
        .pick-item:hover { background: rgba(255,255,255,0.4); }
        .square { width: 55px; height: 55px; background: #333; border-radius: 4px; flex-shrink: 0; }
        .song-meta strong { display: block; font-size: 0.9rem; }
        .song-meta small { color: #444; font-size: 0.75rem; }
    </style>
</head>
<body>
    <div class="container">
        
        <header class="mood-header">
            <h3>What's our mood for today?</h3>
            <form method="POST" action="index.php">
                <input type="text" 
                       name="vibe_input" 
                       class="vibe-box" 
                       placeholder="✨ e.g. morning dawn sipping coffee"
                       autocomplete="off">
            </form>
            <?php if ($vibe_message): ?>
                <p style="margin-top: 15px;"><strong><?php echo $vibe_message; ?></strong></p>
            <?php endif; ?>
            <p style="color: #444;"><small>feeling something new? ✌️</small></p>
        </header>

        <main class="dashboard-layout">
            <section class="player-card">
                <div class="album-art">🎵</div>
                <div class="player-info">
                    <h2>lorem ipsum</h2>
                    <p>Artist Name • Album Title</p>
                </div>
                <div class="controls">
                    <span>🔄</span> <span>⏮</span> <span>▶️</span> <span>⏭</span> <span>🔀</span>
                </div>
            </section>

            <aside class="sidebar-card">
                <div class="user-status">
                    <h4>Current Session</h4>
                    <p style="font-size: 1.2rem;">Welcome back, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>!</p>
                </div>
                <div>
                    <hr style="border: 0; border-top: 1px solid rgba(0,0,0,0.1);">
                    <a href="logout.php" class="logout-btn">LOG OUT</a>
                </div>
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