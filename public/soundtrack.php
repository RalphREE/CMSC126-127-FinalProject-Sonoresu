<?php
// public/soundtrack.php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (empty($_SESSION['playlist_data'])) {
    header("Location: sonorous_couch.php");
    exit;
}

$pd       = $_SESSION['playlist_data'];
$mood     = $pd['mood_data'];
$tracks   = $pd['tracks'];
$prompt   = htmlspecialchars($pd['original_prompt']);
$already_saved = !empty($pd['saved']);

// Helper: 0-1 float → filled bars
function meter(float $val, int $bars = 8): string {
    $filled = (int)round($val * $bars);
    $out = '';
    for ($i = 0; $i < $bars; $i++) {
        $out .= '<span class="bar' . ($i < $filled ? ' on' : '') . '"></span>';
    }
    return $out;
}

function fmt_ms(int $ms): string {
    $s = intdiv($ms, 1000);
    return sprintf('%d:%02d', intdiv($s, 60), $s % 60);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Your Soundtrack — Sonoresu</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  :root {
    --bg:       #080810;
    --surface:  #10101e;
    --surface2: #181830;
    --border:   #222238;
    --accent:   #6c63ff;
    --accent2:  #a78bfa;
    --green:    #1db954;
    --text:     #e4e4f0;
    --muted:    #7070a0;
    --danger:   #ff6b6b;
  }

  html { scroll-behavior: smooth; }

  body {
    background: var(--bg);
    color: var(--text);
    font-family: 'Space Grotesk', sans-serif;
    min-height: 100vh;
  }

  body::before {
    content: '';
    position: fixed; inset: 0;
    background:
      radial-gradient(ellipse 70% 40% at 15% 10%, rgba(108,99,255,.1) 0%, transparent 60%),
      radial-gradient(ellipse 50% 50% at 85% 80%, rgba(29,185,84,.06) 0%, transparent 60%);
    pointer-events: none;
    z-index: 0;
  }

  /* ── Nav ── */
  nav {
    position: sticky;
    top: 0;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 32px;
    border-bottom: 1px solid var(--border);
    background: rgba(8,8,16,.85);
    backdrop-filter: blur(14px);
    z-index: 50;
    gap: 16px;
  }

  .nav-left { display: flex; align-items: center; gap: 20px; }

  .logo {
    font-family: 'Space Mono', monospace;
    font-size: .85rem;
    letter-spacing: .15em;
    color: var(--accent2);
    text-decoration: none;
  }

  .back-btn {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: .85rem;
    color: var(--muted);
    text-decoration: none;
    transition: color .2s;
  }

  .back-btn:hover { color: var(--text); }

  .btn-save {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 22px;
    background: var(--green);
    color: #fff;
    border: none;
    border-radius: 8px;
    font-family: 'Space Grotesk', sans-serif;
    font-size: .875rem;
    font-weight: 600;
    cursor: pointer;
    transition: all .2s;
    white-space: nowrap;
  }

  .btn-save:hover { background: #1ed760; transform: translateY(-1px); box-shadow: 0 6px 20px rgba(29,185,84,.3); }
  .btn-save:active { transform: translateY(0); }
  .btn-save:disabled { background: #1a3d25; color: #2d7a43; cursor: not-allowed; transform: none; box-shadow: none; }

  /* ── Page layout ── */
  main {
    max-width: 820px;
    margin: 0 auto;
    padding: 40px 24px 80px;
    position: relative;
    z-index: 1;
  }

  /* ── Mood header ── */
  .mood-header {
    margin-bottom: 36px;
  }

  .label {
    font-family: 'Space Mono', monospace;
    font-size: .7rem;
    letter-spacing: .15em;
    color: var(--accent2);
    text-transform: uppercase;
    margin-bottom: 10px;
  }

  .original-prompt {
    font-size: .8rem;
    color: var(--muted);
    font-style: italic;
    margin-bottom: 14px;
  }

  .mood-description {
    font-size: 1.45rem;
    font-weight: 500;
    line-height: 1.35;
    color: var(--text);
    margin-bottom: 20px;
  }

  /* Tags */
  .tags {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 22px;
  }

  .tag {
    padding: 5px 14px;
    border-radius: 100px;
    font-size: .78rem;
    font-weight: 500;
    background: rgba(108,99,255,.15);
    border: 1px solid rgba(108,99,255,.3);
    color: var(--accent2);
  }

  /* Audio feature meters */
  .features {
    display: flex;
    gap: 28px;
    flex-wrap: wrap;
  }

  .feature {
    display: flex;
    flex-direction: column;
    gap: 7px;
  }

  .feature-label {
    font-size: .7rem;
    color: var(--muted);
    letter-spacing: .08em;
    text-transform: uppercase;
  }

  .feature-value {
    font-size: .9rem;
    font-family: 'Space Mono', monospace;
    color: var(--text);
  }

  .meter { display: flex; gap: 3px; align-items: center; }

  .meter .bar {
    width: 5px;
    height: 14px;
    border-radius: 2px;
    background: var(--border);
    transition: background .3s;
  }

  .meter .bar.on { background: var(--accent); }

  /* ── Divider ── */
  .divider {
    border: none;
    border-top: 1px solid var(--border);
    margin: 30px 0;
  }

  /* ── Track list ── */
  .tracks-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 20px;
  }

  .tracks-title {
    font-size: 1.05rem;
    font-weight: 600;
    color: var(--text);
  }

  .track-count {
    font-size: .8rem;
    color: var(--muted);
  }

  .track-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
  }

  /* ── Track card ── */
  .track-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 12px;
    overflow: hidden;
    transition: border-color .2s;
  }

  .track-card:hover { border-color: #3a3a5a; }

  .track-meta {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 14px 18px 14px 16px;
  }

  .track-num {
    font-family: 'Space Mono', monospace;
    font-size: .72rem;
    color: var(--muted);
    width: 18px;
    flex-shrink: 0;
    text-align: center;
  }

  .album-art {
    width: 48px;
    height: 48px;
    border-radius: 6px;
    object-fit: cover;
    flex-shrink: 0;
    background: var(--surface2);
  }

  .album-art-placeholder {
    width: 48px;
    height: 48px;
    border-radius: 6px;
    background: var(--surface2);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 1.2rem;
  }

  .track-info { flex: 1; min-width: 0; }

  .track-title {
    font-size: .92rem;
    font-weight: 600;
    color: var(--text);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    margin-bottom: 3px;
  }

  .track-artist {
    font-size: .8rem;
    color: var(--muted);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .track-duration {
    font-family: 'Space Mono', monospace;
    font-size: .72rem;
    color: var(--muted);
    flex-shrink: 0;
  }

  .track-actions {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-shrink: 0;
  }

  .btn-expand {
    width: 32px;
    height: 32px;
    border-radius: 6px;
    border: 1px solid var(--border);
    background: var(--surface2);
    color: var(--muted);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: .8rem;
    transition: all .2s;
    flex-shrink: 0;
  }

  .btn-expand:hover { border-color: var(--accent2); color: var(--accent2); }
  .btn-expand.open  { background: rgba(108,99,255,.15); border-color: var(--accent); color: var(--accent2); }

  .spotify-link {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 6px;
    background: var(--green);
    text-decoration: none;
    transition: opacity .2s;
    flex-shrink: 0;
  }

  .spotify-link:hover { opacity: .85; }

  .embed-wrap {
    display: none;
    border-top: 1px solid var(--border);
    background: #06060e;
  }

  .embed-wrap.open { display: block; }

  .embed-wrap iframe {
    display: block;
    width: 100%;
    border: none;
    border-radius: 0;
  }

  /* ── Toast ── */
  .toast {
    position: fixed;
    bottom: 28px;
    left: 50%;
    transform: translateX(-50%) translateY(80px);
    background: var(--surface2);
    border: 1px solid var(--border);
    border-radius: 10px;
    padding: 14px 22px;
    font-size: .88rem;
    color: var(--text);
    display: flex;
    align-items: center;
    gap: 10px;
    box-shadow: 0 16px 40px rgba(0,0,0,.5);
    transition: transform .35s cubic-bezier(.34,1.56,.64,1);
    z-index: 200;
    white-space: nowrap;
  }

  .toast.show { transform: translateX(-50%) translateY(0); }
  .toast.success { border-color: rgba(29,185,84,.4); }
  .toast.error   { border-color: rgba(255,107,107,.4); }
</style>
</head>
<body>

<nav>
  <div class="nav-left">
    <a class="logo" href="sonorous_couch.php">SONORESU</a>
    <a class="back-btn" href="sonorous_couch.php">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
      New playlist
    </a>
  </div>

  <button
    class="btn-save"
    id="btn-save"
    <?= $already_saved ? 'disabled' : '' ?>
  >
    <?php if ($already_saved): ?>
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
      Saved to Profile
    <?php else: ?>
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
      Save to Profile
    <?php endif; ?>
  </button>
</nav>

<main>
  <!-- ── Mood header ── -->
  <div class="mood-header">
    <div class="label">Your Soundtrack</div>
    <div class="original-prompt">"<?= $prompt ?>"</div>
    <div class="mood-description"><?= htmlspecialchars($mood['mood_description']) ?></div>

    <?php if (!empty($mood['mood_tags'])): ?>
    <div class="tags">
      <?php foreach ($mood['mood_tags'] as $tag): ?>
        <span class="tag"><?= htmlspecialchars($tag) ?></span>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <div class="features">
      <div class="feature">
        <div class="feature-label">Valence</div>
        <div class="meter"><?= meter($mood['valence']) ?></div>
        <div class="feature-value"><?= number_format($mood['valence'], 2) ?></div>
      </div>
      <div class="feature">
        <div class="feature-label">Energy</div>
        <div class="meter"><?= meter($mood['energy']) ?></div>
        <div class="feature-value"><?= number_format($mood['energy'], 2) ?></div>
      </div>
      <div class="feature">
        <div class="feature-label">Tempo</div>
        <div class="feature-value"><?= $mood['tempo'] ?> BPM</div>
      </div>
      <?php if (!empty($mood['genres'])): ?>
      <div class="feature">
        <div class="feature-label">Genres</div>
        <div class="feature-value"><?= implode(' · ', array_map('htmlspecialchars', $mood['genres'])) ?></div>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <hr class="divider">

  <!-- ── Track list ── -->
  <div class="tracks-header">
    <div class="tracks-title">Your Tracks</div>
    <div class="track-count"><?= count($tracks) ?> songs</div>
  </div>

  <div class="track-list">
    <?php foreach ($tracks as $i => $t): ?>
    <?php
      $tid  = htmlspecialchars($t['spotify_track_id']);
      $name = htmlspecialchars($t['title']);
      $art  = htmlspecialchars($t['artist']);
      $alb  = htmlspecialchars($t['album'] ?? '');
      $img  = htmlspecialchars($t['album_art'] ?? '');
      $url  = htmlspecialchars($t['spotify_url'] ?? '#');
      $dur  = fmt_ms((int)($t['duration_ms'] ?? 0));
    ?>
    <div class="track-card">
      <div class="track-meta">
        <div class="track-num"><?= $i + 1 ?></div>

        <?php if ($img): ?>
          <img class="album-art" src="<?= $img ?>" alt="<?= $alb ?>" loading="lazy">
        <?php else: ?>
          <div class="album-art-placeholder">🎵</div>
        <?php endif; ?>

        <div class="track-info">
          <div class="track-title" title="<?= $name ?>"><?= $name ?></div>
          <div class="track-artist"><?= $art ?></div>
        </div>

        <div class="track-duration"><?= $dur ?></div>

        <div class="track-actions">
          <button class="btn-expand" data-track="<?= $tid ?>" title="Preview in Spotify">
            ▶
          </button>
          <?php if ($url !== '#'): ?>
          <a class="spotify-link" href="<?= $url ?>" target="_blank" rel="noopener" title="Open in Spotify">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="white"><path d="M12 0C5.4 0 0 5.4 0 12s5.4 12 12 12 12-5.4 12-12S18.66 0 12 0zm5.521 17.34c-.24.359-.66.48-1.021.24-2.82-1.74-6.36-2.101-10.561-1.141-.418.122-.779-.179-.899-.539-.12-.421.18-.78.54-.9 4.56-1.021 8.52-.6 11.64 1.32.42.18.479.659.301 1.02zm1.44-3.3c-.301.42-.841.6-1.262.3-3.239-1.98-8.159-2.58-11.939-1.38-.479.12-1.02-.12-1.14-.6-.12-.48.12-1.021.6-1.141C9.6 9.9 15 10.561 18.72 12.84c.361.181.54.78.241 1.2zm.12-3.36C15.24 8.4 8.82 8.16 5.16 9.301c-.6.179-1.2-.181-1.38-.721-.18-.601.18-1.2.72-1.381 4.26-1.26 11.28-1.02 15.721 1.621.539.3.719 1.02.419 1.56-.299.421-1.02.599-1.559.3z"/></svg>
          </a>
          <?php endif; ?>
        </div>
      </div>

      <!-- Spotify embed (lazy) -->
      <div class="embed-wrap" id="embed-<?= $tid ?>"></div>
    </div>
    <?php endforeach; ?>
  </div>
</main>

<!-- ── Toast ── -->
<div class="toast" id="toast"></div>

<script>
(() => {
  // ── Track expand / Spotify embed ──────────────────────────
  document.querySelectorAll('.btn-expand').forEach(btn => {
    btn.addEventListener('click', function() {
      const tid   = this.dataset.track;
      const wrap  = document.getElementById('embed-' + tid);
      const isOpen = wrap.classList.contains('open');

      if (isOpen) {
        wrap.classList.remove('open');
        wrap.innerHTML = '';
        this.classList.remove('open');
        this.textContent = '▶';
      } else {
        wrap.classList.add('open');
        wrap.innerHTML = `<iframe
          src="https://open.spotify.com/embed/track/${tid}?utm_source=generator&theme=0"
          width="100%" height="80"
          allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture"
          loading="lazy"></iframe>`;
        this.classList.add('open');
        this.textContent = '▼';
      }
    });
  });

  // ── Save to Profile ───────────────────────────────────────
  const btnSave = document.getElementById('btn-save');
  const toast   = document.getElementById('toast');
  let toastTimer;

  function showToast(msg, type = 'success') {
    clearTimeout(toastTimer);
    toast.textContent = msg;
    toast.className   = `toast ${type} show`;
    toastTimer = setTimeout(() => toast.classList.remove('show'), 3200);
  }

  <?php if (!$already_saved): ?>
  btnSave.addEventListener('click', async () => {
    btnSave.disabled = true;

    try {
      const res  = await fetch('save_playlist.php', { method: 'POST' });
      const data = await res.json();

      if (data.success) {
        btnSave.innerHTML = `
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <polyline points="20 6 9 17 4 12"/>
          </svg>
          Saved to Profile`;
        showToast('✓ Playlist saved to your profile!', 'success');
      } else {
        btnSave.disabled = false;
        showToast('⚠ ' + (data.error || 'Save failed. Try again.'), 'error');
      }
    } catch {
      btnSave.disabled = false;
      showToast('⚠ Network error. Try again.', 'error');
    }
  });
  <?php endif; ?>
})();
</script>
</body>
</html>
