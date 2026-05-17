<?php
// public/results.php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$username = htmlspecialchars($_SESSION['username'] ?? 'User');

// Pull generated data from session (set by generate_playlist.php)
$current_vibe = "Late night coding in the neon rain...";
$activeTracks = [];
$reserveTracks = [];
if (!empty($_SESSION['playlist_data'])) {
    $pd = $_SESSION['playlist_data'];
    $current_vibe = $pd['original_prompt'] ?? $current_vibe;

    // Build JS-friendly arrays from session data
    foreach ($pd['main'] ?? [] as $i => $item) {
        $activeTracks[] = [
            'id' => $item['id'],
            'title' => $item['title'] ?: ($item['query'] ?? ''),
            'channel' => $item['channel'] ?? '',
            'thumbnail' => $item['thumbnail'] ?? '',
            'color' => '#2a2a3a',
        ];
    }
    foreach ($pd['replacements'] ?? $pd['replacements'] ?? $pd['replacement'] ?? [] as $item) {
        $reserveTracks[] = [
            'id' => $item['id'],
            'title' => $item['title'] ?: ($item['query'] ?? ''),
            'channel' => $item['channel'] ?? '',
            'thumbnail' => $item['thumbnail'] ?? '',
            'color' => '#403040',
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Soundtrack - Sonoresu</title>
    <link rel="stylesheet" href="global.css">
    
    <style>
        /* Page-Specific Layouts Only */
        body { padding-bottom: 60px; }

        .results-header {
            display: flex; justify-content: space-between; align-items: flex-end;
            margin-bottom: 30px; padding-bottom: 20px; border-bottom: 1px solid var(--border);
        }
        .results-header h1 { font-size: 2.2rem; font-weight: 700; margin-bottom: 8px; }
        .results-header p { color: var(--accent2); font-family: 'Space Mono', monospace; font-size: 0.9rem; }
        
        .action-buttons-group { display: flex; gap: 14px; }

        .btn-cancel {
            background: transparent; color: var(--muted); border: 1px solid var(--border);
            padding: 12px 24px; border-radius: 12px; font-weight: 600; font-size: 0.95rem;
            cursor: pointer; transition: all 0.2s; display: flex; align-items: center; gap: 8px;
        }
        .btn-cancel:hover { background: rgba(255,255,255,0.05); color: var(--text); border-color: var(--muted); }
        .btn-cancel:active { transform: scale(0.95); }

        .btn-save {
            background: var(--accent); color: #fff; border: none;
            padding: 12px 24px; border-radius: 12px; font-weight: 600; font-size: 0.95rem;
            cursor: pointer; transition: all 0.2s; display: flex; align-items: center; gap: 8px;
            box-shadow: 0 4px 15px rgba(108,99,255,0.3);
        }
        .btn-save:hover { background: #5b52e6; transform: translateY(-2px); box-shadow: 0 6px 20px rgba(108,99,255,0.4); }
        .btn-save:active { transform: scale(0.95); }

        .section-title { font-size: 1.2rem; font-weight: 600; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
        .section-title span { background: var(--surface2); padding: 4px 10px; border-radius: 8px; font-size: 0.85rem; color: var(--muted); }

        .active-playlist-container {
            background: var(--surface); border: 1px solid var(--border);
            border-radius: 20px; padding: 20px; margin-bottom: 40px;
            max-height: 480px; overflow-y: auto; box-shadow: inset 0 2px 20px rgba(0,0,0,0.5);
        }
        
        .active-playlist-container::-webkit-scrollbar { width: 8px; }
        .active-playlist-container::-webkit-scrollbar-track { background: var(--surface); border-radius: 10px; }
        .active-playlist-container::-webkit-scrollbar-thumb { background: var(--border); border-radius: 10px; }
        .active-playlist-container::-webkit-scrollbar-thumb:hover { background: var(--muted); }

        .track-list { display: flex; flex-direction: column; gap: 16px; }
        
        .yt-card {
            display: flex; align-items: center; gap: 20px;
            background: var(--surface2); border: 1px solid transparent;
            padding: 12px; border-radius: 14px; transition: all 0.2s;
        }
        .yt-card:hover { background: #23232e; border-color: var(--border); }
        
        .yt-thumb {
            width: 140px; aspect-ratio: 16 / 9;
            background: #000; border-radius: 8px; position: relative;
            overflow: hidden; flex-shrink: 0; display: flex; align-items: center; justify-content: center;
            box-shadow: 0 4px 10px rgba(0,0,0,0.5);
        }
        .yt-thumb::before {
            content: '▶'; position: absolute; color: #fff; font-size: 1.5rem;
            background: rgba(0,0,0,0.6); width: 40px; height: 40px;
            display: flex; align-items: center; justify-content: center;
            border-radius: 50%; opacity: 0.8; transition: opacity 0.2s;
        }
        .yt-card:hover .yt-thumb::before { opacity: 1; color: var(--accent2); }
        
        .yt-info { flex: 1; }
        .yt-title { font-weight: 600; font-size: 1rem; margin-bottom: 4px; color: var(--text); display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .yt-channel { font-size: 0.85rem; color: var(--muted); display: flex; align-items: center; gap: 6px; }
        
        .action-btn {
            background: none; border: 1px solid var(--border); border-radius: 8px;
            width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;
            cursor: pointer; transition: all 0.2s; font-size: 1.2rem; flex-shrink: 0;
        }
        .btn-remove { color: var(--danger); }
        .btn-remove:hover { background: rgba(255,107,107,0.1); border-color: var(--danger); }
        .btn-add { color: var(--green); }
        .btn-add:hover { background: rgba(29,185,84,0.1); border-color: var(--green); }

        .reserve-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 16px; }
        .reserve-grid .yt-card { flex-direction: column; align-items: stretch; padding: 16px; }
        .reserve-grid .yt-thumb { width: 100%; margin-bottom: 12px; }
        .reserve-grid .yt-info { margin-bottom: 16px; }
        .reserve-grid .action-btn { width: 100%; border-radius: 8px; font-size: 0.95rem; gap: 8px; font-weight: 600; }

        .toast {
            position: fixed; bottom: 30px; left: 50%; transform: translateX(-50%) translateY(100px);
            background: var(--surface2); border: 1px solid var(--accent); color: #fff;
            padding: 16px 30px; border-radius: 50px; font-weight: 600; box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            display: flex; align-items: center; gap: 12px; opacity: 0; transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 1000;
        }
        .toast.show { transform: translateX(-50%) translateY(0); opacity: 1; }

        @media (max-width: 768px) {
            .results-header { flex-direction: column; align-items: flex-start; gap: 16px; }
            .action-buttons-group { width: 100%; flex-direction: column; }
            .btn-save, .btn-cancel { width: 100%; justify-content: center; }
            .yt-card { flex-direction: column; align-items: flex-start; }
            .yt-thumb { width: 100%; }
            .active-playlist-container .action-btn { width: 100%; margin-top: 10px; }
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
                <a href="profile.php">My Playlists</a>
                <a href="lounge.php">Global Lounge</a>
                <a href="logout.php" class="logout-btn">Log out</a>
            </div>
            <div style="margin-left:16px; display:flex; align-items:center; gap:10px;">
                <button id="debugToggle" class="btn-cancel" style="padding:8px 12px; font-size:0.85rem;">Toggle Debug</button>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="results-header">
            <div>
                <h1>Your Generated Soundtrack</h1>
                <p>Prompt: "<?php echo htmlspecialchars($current_vibe); ?>"</p>
            </div>
            
            <div class="action-buttons-group">
                <button class="btn-cancel" onclick="window.location.href='sonorous_couch.php'">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                    Don't Save
                </button>
                <button class="btn-save" onclick="savePlaylistAndRedirect()">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                    Save to Profile
                </button>
            </div>
        </div>

        <h3 class="section-title">Now Playing <span id="activeCount">5 Tracks</span></h3>
        <div class="active-playlist-container">
            <div class="track-list" id="activePlaylist">
                </div>
        </div>

        <details id="debugDumpWrapper" style="margin-top:18px; display:none;">
            <summary style="cursor:pointer; font-weight:600;">Session Playlist Data (debug)</summary>
            <pre id="debugDump" style="background:#0b0b10; color:#d7d7d7; padding:12px; border-radius:8px; overflow:auto; max-height:320px;"></pre>
        </details>

        <h3 class="section-title" style="margin-top: 50px;">Alternative Tracks <span>Replace discarded songs</span></h3>
        <div class="reserve-grid" id="reservePlaylist">
            </div>
    </div>

    <div class="toast" id="saveToast">
        <span style="font-size: 1.2rem; color: var(--green);">✓</span> Playlist successfully saved! Redirecting...
    </div>

    <script>
        let activeTracks = <?php echo json_encode($activeTracks, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE); ?>;
        let reserveTracks = <?php echo json_encode($reserveTracks, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE); ?>;

        const activeContainer = document.getElementById('activePlaylist');
        const reserveContainer = document.getElementById('reservePlaylist');
        const activeCountLabel = document.getElementById('activeCount');
        const debugToggle = document.getElementById('debugToggle');
        const debugDumpWrapper = document.getElementById('debugDumpWrapper');
        const debugDump = document.getElementById('debugDump');
        const serverPlaylistData = <?php echo json_encode($pd ?? [], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE); ?>;

        function renderUI() {
            activeContainer.innerHTML = '';
                activeTracks.forEach(track => {
                    // normalize video id if a full URL was stored
                    const normalizeId = (id) => {
                        if (!id) return '';
                        try {
                            // if it's a URL, extract v= or last path
                            if (id.includes('youtube.com') || id.includes('youtu.be')) {
                                const url = new URL(id.startsWith('http') ? id : ('https://' + id));
                                if (url.hostname.includes('youtu.be')) return url.pathname.slice(1);
                                const v = url.searchParams.get('v');
                                if (v) return v;
                                // fallback: last path segment
                                const segs = url.pathname.split('/').filter(Boolean);
                                return segs.length ? segs[segs.length-1] : id;
                            }
                        } catch (e) {}
                        return id;
                    };

                    const vid = normalizeId(track.id || '');
                    let thumbnail = track.thumbnail || '';
                    if (!thumbnail && vid) {
                        thumbnail = 'https://i.ytimg.com/vi/' + encodeURIComponent(vid) + '/hqdefault.jpg';
                    }

                    // create elements to avoid CSS string escaping issues
                    const card = document.createElement('div'); card.className = 'yt-card';
                    const thumb = document.createElement('div'); thumb.className = 'yt-thumb';
                    thumb.setAttribute('data-video-id', vid);
                    thumb.onclick = function(){ togglePlay(this, vid); };
                    if (thumbnail) {
                        thumb.style.backgroundImage = `url(${thumbnail})`;
                        thumb.style.backgroundSize = 'cover';
                        thumb.style.backgroundPosition = 'center';
                    } else {
                        thumb.style.background = `linear-gradient(45deg, #111, ${track.color})`;
                    }
                    const info = document.createElement('div'); info.className = 'yt-info';
                    const title = document.createElement('div'); title.className = 'yt-title'; title.textContent = track.title;
                    const channel = document.createElement('div'); channel.className = 'yt-channel'; channel.textContent = track.channel;
                    const btn = document.createElement('button'); btn.className = 'action-btn btn-remove'; btn.title = 'Remove Track'; btn.textContent = '✕'; btn.onclick = function(){ removeTrack(track.id); };

                    info.appendChild(title); info.appendChild(channel);
                    card.appendChild(thumb); card.appendChild(info); card.appendChild(btn);
                    activeContainer.appendChild(card);
                });
            activeCountLabel.textContent = `${activeTracks.length} Track${activeTracks.length !== 1 ? 's' : ''}`;

            reserveContainer.innerHTML = '';
            reserveTracks.forEach(track => {
                const normalizeId = (id) => {
                    if (!id) return '';
                    try {
                        if (id.includes('youtube.com') || id.includes('youtu.be')) {
                            const url = new URL(id.startsWith('http') ? id : ('https://' + id));
                            if (url.hostname.includes('youtu.be')) return url.pathname.slice(1);
                            const v = url.searchParams.get('v');
                            if (v) return v;
                            const segs = url.pathname.split('/').filter(Boolean);
                            return segs.length ? segs[segs.length-1] : id;
                        }
                    } catch (e) {}
                    return id;
                };
                const vid = normalizeId(track.id || '');
                let thumbnail = track.thumbnail || '';
                if (!thumbnail && vid) {
                    thumbnail = 'https://i.ytimg.com/vi/' + encodeURIComponent(vid) + '/hqdefault.jpg';
                }

                const card = document.createElement('div'); card.className = 'yt-card';
                const thumb = document.createElement('div'); thumb.className = 'yt-thumb';
                thumb.setAttribute('data-video-id', vid);
                thumb.onclick = function(){ togglePlay(this, vid); };
                if (thumbnail) {
                    thumb.style.backgroundImage = `url(${thumbnail})`;
                    thumb.style.backgroundSize = 'cover';
                    thumb.style.backgroundPosition = 'center';
                } else {
                    thumb.style.background = `linear-gradient(45deg, #111, ${track.color})`;
                }
                const info = document.createElement('div'); info.className = 'yt-info';
                const title = document.createElement('div'); title.className = 'yt-title'; title.textContent = track.title;
                const channel = document.createElement('div'); channel.className = 'yt-channel'; channel.textContent = track.channel;
                const btn = document.createElement('button'); btn.className = 'action-btn btn-add'; btn.onclick = function(){ addTrack(track.id); }; btn.innerHTML = '<span>＋</span> Replace / Add';

                info.appendChild(title); info.appendChild(channel);
                card.appendChild(thumb); card.appendChild(info); card.appendChild(btn);
                reserveContainer.appendChild(card);
            });
        }

        // Debug toggle: show raw session playlist_data for troubleshooting
        if (debugToggle) {
            debugToggle.addEventListener('click', function(){
                if (!debugDumpWrapper) return;
                if (debugDumpWrapper.style.display === 'none' || debugDumpWrapper.style.display === '') {
                    debugDumpWrapper.style.display = 'block';
                    debugDump.textContent = JSON.stringify(serverPlaylistData, null, 2);
                } else {
                    debugDumpWrapper.style.display = 'none';
                }
            });
        }

        // Toggle YouTube embed playback inside the thumbnail container
        function togglePlay(thumbEl, videoId) {
            // If no videoId available, do nothing
            if (!videoId) return;

            // Stop other players
            document.querySelectorAll('.yt-thumb iframe').forEach(f => {
                if (f.parentElement !== thumbEl) f.remove();
            });

            const existing = thumbEl.querySelector('iframe');
            if (existing) {
                existing.remove();
                return;
            }

            // Create iframe embed
            const iframe = document.createElement('iframe');
            iframe.src = 'https://www.youtube.com/embed/' + encodeURIComponent(videoId) + '?autoplay=1&rel=0';
            iframe.allow = 'autoplay; encrypted-media; picture-in-picture';
            iframe.allowFullscreen = true;
            iframe.style.width = '100%';
            iframe.style.height = '100%';
            iframe.style.border = '0';

            // Clear thumb contents and insert iframe
            thumbEl.innerHTML = '';
            thumbEl.appendChild(iframe);
        }

        function removeTrack(id) {
            const index = activeTracks.findIndex(t => t.id === id);
            if (index !== -1) {
                const track = activeTracks.splice(index, 1)[0];
                reserveTracks.unshift(track);
                renderUI();
            }
        }

        function addTrack(id) {
            const index = reserveTracks.findIndex(t => t.id === id);
            if (index !== -1) {
                const track = reserveTracks.splice(index, 1)[0];
                activeTracks.push(track);
                renderUI();
            }
        }

        function savePlaylistAndRedirect() {
            const toast = document.getElementById('saveToast');
            toast.classList.add('show');
            
            setTimeout(() => {
                window.location.href = 'profile.php'; // Updated redirect path from previous turn
            }, 1500);
        }

        renderUI();
    </script>
</body>
</html>