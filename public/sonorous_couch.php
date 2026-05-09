<?php
// public/sonorous_couch.php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$username = htmlspecialchars($_SESSION['username']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sonorous Couch — Sonoresu</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  :root {
    --bg:         #080810;
    --surface:    #10101e;
    --surface2:   #181830;
    --border:     #252540;
    --accent:     #6c63ff;
    --accent2:    #a78bfa;
    --green:      #1db954;
    --text:       #e4e4f0;
    --muted:      #7070a0;
    --danger:     #ff6b6b;
  }

  html, body {
    height: 100%;
    background: var(--bg);
    color: var(--text);
    font-family: 'Space Grotesk', sans-serif;
    overflow: hidden;
  }

  /* ── Animated background ── */
  body::before {
    content: '';
    position: fixed; inset: 0;
    background:
      radial-gradient(ellipse 60% 50% at 20% 30%, rgba(108,99,255,.12) 0%, transparent 70%),
      radial-gradient(ellipse 50% 40% at 80% 70%, rgba(167,139,250,.08) 0%, transparent 70%);
    pointer-events: none;
  }

  /* ── Layout ── */
  .shell {
    height: 100vh;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 24px;
  }

  /* ── Nav ── */
  nav {
    position: fixed;
    top: 0; left: 0; right: 0;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 18px 32px;
    border-bottom: 1px solid var(--border);
    background: rgba(8,8,16,.8);
    backdrop-filter: blur(12px);
    z-index: 10;
  }

  .logo {
    font-family: 'Space Mono', monospace;
    font-size: .9rem;
    letter-spacing: .15em;
    color: var(--accent2);
    text-decoration: none;
  }

  .nav-right {
    display: flex;
    align-items: center;
    gap: 20px;
    font-size: .85rem;
    color: var(--muted);
  }

  .nav-right a { color: var(--muted); text-decoration: none; transition: color .2s; }
  .nav-right a:hover { color: var(--text); }

  /* ── Terminal card ── */
  .card {
    width: 100%;
    max-width: 680px;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 40px 44px 36px;
    box-shadow: 0 24px 80px rgba(0,0,0,.5), 0 0 0 1px rgba(108,99,255,.1);
  }

  .card-header {
    margin-bottom: 28px;
  }

  .label {
    font-family: 'Space Mono', monospace;
    font-size: .7rem;
    letter-spacing: .15em;
    color: var(--accent2);
    text-transform: uppercase;
    margin-bottom: 8px;
  }

  h1 {
    font-size: 1.65rem;
    font-weight: 600;
    line-height: 1.25;
    color: var(--text);
  }

  h1 span { color: var(--accent2); }

  .subtitle {
    margin-top: 8px;
    font-size: .9rem;
    color: var(--muted);
    line-height: 1.5;
  }

  /* ── Terminal input area ── */
  .terminal {
    background: #06060e;
    border: 1px solid var(--border);
    border-radius: 10px;
    padding: 20px 22px;
    margin-top: 28px;
    position: relative;
  }

  .terminal-prompt {
    font-family: 'Space Mono', monospace;
    font-size: .75rem;
    color: var(--green);
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .terminal-prompt::before {
    content: '▶';
    font-size: .6rem;
    color: var(--accent);
  }

  textarea#mood-input {
    width: 100%;
    background: transparent;
    border: none;
    outline: none;
    resize: none;
    font-family: 'Space Mono', monospace;
    font-size: .92rem;
    color: var(--text);
    line-height: 1.6;
    min-height: 88px;
    max-height: 200px;
    caret-color: var(--accent2);
  }

  textarea#mood-input::placeholder { color: #3a3a60; }

  .char-count {
    font-family: 'Space Mono', monospace;
    font-size: .68rem;
    color: var(--muted);
    text-align: right;
    margin-top: 8px;
    opacity: .6;
  }

  /* ── Buttons ── */
  .actions {
    margin-top: 24px;
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
  }

  .btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 13px 28px;
    border-radius: 8px;
    font-family: 'Space Grotesk', sans-serif;
    font-size: .92rem;
    font-weight: 500;
    cursor: pointer;
    border: none;
    transition: all .2s;
    white-space: nowrap;
  }

  .btn-primary {
    background: var(--accent);
    color: #fff;
    flex: 1;
    justify-content: center;
  }

  .btn-primary:hover { background: #7c74ff; transform: translateY(-1px); box-shadow: 0 8px 24px rgba(108,99,255,.35); }
  .btn-primary:active { transform: translateY(0); }

  .btn-lucky {
    background: var(--surface2);
    color: var(--text);
    border: 1px solid var(--border);
  }

  .btn-lucky:hover { border-color: var(--accent2); color: var(--accent2); }

  .btn:disabled { opacity: .4; cursor: not-allowed; transform: none !important; }

  /* ── Error ── */
  .error-msg {
    display: none;
    margin-top: 18px;
    padding: 12px 16px;
    background: rgba(255,107,107,.1);
    border: 1px solid rgba(255,107,107,.3);
    border-radius: 8px;
    font-size: .875rem;
    color: var(--danger);
    line-height: 1.5;
  }

  .error-msg.show { display: block; }

  /* ── Processing overlay ── */
  .overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(8,8,16,.92);
    backdrop-filter: blur(8px);
    z-index: 100;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    gap: 40px;
  }

  .overlay.show { display: flex; }

  .overlay-inner {
    text-align: center;
    max-width: 420px;
    padding: 0 24px;
  }

  /* Waveform animation */
  .waveform {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
    height: 50px;
    margin-bottom: 36px;
  }

  .bar {
    width: 4px;
    border-radius: 2px;
    background: var(--accent);
    animation: wave 1.2s ease-in-out infinite;
  }

  .bar:nth-child(1)  { animation-delay: 0s;    height: 16px; }
  .bar:nth-child(2)  { animation-delay: .1s;   height: 30px; }
  .bar:nth-child(3)  { animation-delay: .2s;   height: 44px; }
  .bar:nth-child(4)  { animation-delay: .3s;   height: 30px; }
  .bar:nth-child(5)  { animation-delay: .4s;   height: 50px; }
  .bar:nth-child(6)  { animation-delay: .5s;   height: 36px; }
  .bar:nth-child(7)  { animation-delay: .6s;   height: 50px; }
  .bar:nth-child(8)  { animation-delay: .7s;   height: 30px; }
  .bar:nth-child(9)  { animation-delay: .8s;   height: 44px; }
  .bar:nth-child(10) { animation-delay: .9s;   height: 22px; }
  .bar:nth-child(11) { animation-delay: 1.0s;  height: 30px; }
  .bar:nth-child(12) { animation-delay: 1.1s;  height: 16px; }

  @keyframes wave {
    0%, 100% { transform: scaleY(1);   opacity: .5; }
    50%       { transform: scaleY(.3); opacity: 1;   }
  }

  /* Processing steps */
  .steps {
    display: flex;
    flex-direction: column;
    gap: 14px;
    width: 100%;
    max-width: 340px;
    margin: 0 auto;
    text-align: left;
  }

  .step {
    display: flex;
    align-items: center;
    gap: 14px;
    font-size: .9rem;
    color: var(--muted);
    transition: color .4s;
  }

  .step.active  { color: var(--text); }
  .step.done    { color: var(--green); }

  .step-dot {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    border: 2px solid var(--border);
    flex-shrink: 0;
    position: relative;
    transition: border-color .4s, background .4s;
  }

  .step.active .step-dot {
    border-color: var(--accent);
    animation: pulse-dot 1s ease-in-out infinite;
  }

  .step.done .step-dot {
    border-color: var(--green);
    background: var(--green);
  }

  .step.done .step-dot::after {
    content: '✓';
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: .65rem;
    color: #fff;
    font-weight: 700;
  }

  @keyframes pulse-dot {
    0%, 100% { box-shadow: 0 0 0 0 rgba(108,99,255,.4); }
    50%       { box-shadow: 0 0 0 6px rgba(108,99,255,0); }
  }

  .lucky-reveal {
    display: none;
    margin-top: 16px;
    padding: 14px 18px;
    background: rgba(108,99,255,.1);
    border: 1px solid rgba(108,99,255,.25);
    border-radius: 8px;
    font-size: .85rem;
    color: var(--accent2);
    line-height: 1.5;
  }

  .lucky-reveal.show { display: block; }
</style>
</head>
<body>

<nav>
  <a class="logo" href="index.php">SONORESU</a>
  <div class="nav-right">
    <span><?= $username ?></span>
    <a href="logout.php">Sign out</a>
  </div>
</nav>

<div class="shell">
  <div class="card">
    <div class="card-header">
      <div class="label">Sonorous Couch</div>
      <h1>What's your <span>soundtrack</span><br>right now?</h1>
      <p class="subtitle">Describe a feeling, a scene, a moment — anything.<br>We'll find the music that fits.</p>
    </div>

    <div class="terminal">
      <div class="terminal-prompt">mood_prompt.txt</div>
      <textarea
        id="mood-input"
        maxlength="300"
        placeholder="e.g. 'Rain on a Sunday, cup of tea, no plans for the day...'"
        autofocus
      ></textarea>
      <div class="char-count"><span id="char-n">0</span> / 300</div>
    </div>

    <div id="lucky-reveal" class="lucky-reveal"></div>
    <div id="error-msg" class="error-msg"></div>

    <div class="actions">
      <button class="btn btn-primary" id="btn-generate">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M9 18V5l12-2v13"/><circle cx="6" cy="18" r="3"/><circle cx="18" cy="16" r="3"/></svg>
        Generate My Playlist
      </button>
      <button class="btn btn-lucky" id="btn-lucky" title="Pick a random mood for me">
        🎲 I'm Feeling Lucky
      </button>
    </div>
  </div>
</div>

<!-- ── Processing Overlay ── -->
<div class="overlay" id="overlay">
  <div class="overlay-inner">
    <div class="waveform">
      <?php for ($i = 0; $i < 12; $i++): ?>
        <div class="bar"></div>
      <?php endfor; ?>
    </div>

    <div class="steps">
      <div class="step" id="step-0">
        <div class="step-dot"></div>
        <span>Tuning in to your frequency…</span>
      </div>
      <div class="step" id="step-1">
        <div class="step-dot"></div>
        <span>Translating emotion to sound…</span>
      </div>
      <div class="step" id="step-2">
        <div class="step-dot"></div>
        <span>Searching the sonic universe…</span>
      </div>
      <div class="step" id="step-3">
        <div class="step-dot"></div>
        <span>Assembling your playlist…</span>
      </div>
    </div>
  </div>
</div>

<script>
(() => {
  const textarea   = document.getElementById('mood-input');
  const charN      = document.getElementById('char-n');
  const btnGen     = document.getElementById('btn-generate');
  const btnLucky   = document.getElementById('btn-lucky');
  const errorBox   = document.getElementById('error-msg');
  const overlay    = document.getElementById('overlay');
  const luckyReveal = document.getElementById('lucky-reveal');

  // ── char counter
  textarea.addEventListener('input', () => {
    charN.textContent = textarea.value.length;
  });

  // ── processing step animation
  let stepIndex  = 0;
  let stepTimer  = null;
  let apiDone    = false;
  let redirectTo = null;

  function startSteps() {
    activateStep(0);
    stepTimer = setInterval(() => {
      if (stepIndex < 3) {
        markDone(stepIndex);
        stepIndex++;
        activateStep(stepIndex);
      } else {
        clearInterval(stepTimer);
        // If API already returned, redirect immediately
        if (apiDone) doRedirect();
      }
    }, 1350);
  }

  function activateStep(i) {
    document.getElementById(`step-${i}`).classList.add('active');
  }

  function markDone(i) {
    const el = document.getElementById(`step-${i}`);
    el.classList.remove('active');
    el.classList.add('done');
  }

  function doRedirect() {
    markDone(stepIndex);
    setTimeout(() => { window.location.href = redirectTo; }, 400);
  }

  // ── show error and reset UI
  function showError(msg) {
    overlay.classList.remove('show');
    clearInterval(stepTimer);
    // Reset steps
    [0,1,2,3].forEach(i => {
      const el = document.getElementById(`step-${i}`);
      el.classList.remove('active', 'done');
    });
    stepIndex = 0; apiDone = false; redirectTo = null;

    errorBox.textContent = '⚠ ' + msg;
    errorBox.classList.add('show');
    btnGen.disabled   = false;
    btnLucky.disabled = false;
  }

  // ── core submit logic
  function submit(isLucky) {
    errorBox.classList.remove('show');
    luckyReveal.classList.remove('show');

    const prompt = textarea.value.trim();
    if (!isLucky && !prompt) {
      errorBox.textContent = '⚠ Please describe a mood before generating.';
      errorBox.classList.add('show');
      textarea.focus();
      return;
    }

    btnGen.disabled   = true;
    btnLucky.disabled = true;
    overlay.classList.add('show');
    apiDone = false;
    startSteps();

    const body = new URLSearchParams();
    body.append('mood_prompt', prompt);
    if (isLucky) body.append('is_lucky', '1');

    fetch('generate_playlist.php', { method: 'POST', body })
      .then(r => r.json())
      .then(data => {
        if (!data.success) {
          showError(data.error || 'Something went wrong. Please try again.');
          return;
        }

        if (data.lucky_prompt) {
          textarea.value    = data.lucky_prompt;
          charN.textContent = data.lucky_prompt.length;
          luckyReveal.textContent = `🎲 "${data.lucky_prompt}"`;
          luckyReveal.classList.add('show');
        }

        redirectTo = data.redirect;
        apiDone    = true;

        // If steps finished before API, redirect now
        if (stepIndex >= 3) doRedirect();
      })
      .catch(() => showError('Network error. Check your connection and try again.'));
  }

  btnGen.addEventListener('click', () => submit(false));
  btnLucky.addEventListener('click', () => submit(true));

  // Cmd/Ctrl+Enter to submit
  textarea.addEventListener('keydown', e => {
    if ((e.metaKey || e.ctrlKey) && e.key === 'Enter') submit(false);
  });
})();
</script>
</body>
</html>
