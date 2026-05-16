<?php
// public/sonorous_couch.php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}
$username = htmlspecialchars($_SESSION['username'] ?? 'User');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sonorous Couch — Sonoresu</title>
<link rel="stylesheet" href="global.css">
<style>
  .shell { height: 100vh; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 24px; }

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

  /* High Fidelity Card Component */
  .card { width: 100%; max-width: 680px; background: var(--surface); border: 1px solid var(--border); border-radius: 24px; padding: 44px; box-shadow: 0 30px 70px rgba(0,0,0,.6), inset 0 1px 1px rgba(255,255,255,0.05); }
  .card-header { margin-bottom: 24px; }
  .label { font-family: 'Space Mono', monospace; font-size: .75rem; letter-spacing: .15em; color: var(--accent); text-transform: uppercase; margin-bottom: 12px; font-weight: 700; }
  h1 { font-size: 2rem; font-weight: 700; line-height: 1.25; color: var(--text); letter-spacing: -0.02em; }
  h1 span { color: var(--accent2); }
  .subtitle { margin-top: 10px; font-size: .95rem; color: var(--muted); line-height: 1.6; }

  /* Premium Integrated Console Area */
  .terminal { background: #07070a; border: 1px solid rgba(255,255,255,0.04); border-radius: 14px; padding: 20px; margin-top: 24px; box-shadow: inset 0 4px 20px rgba(0,0,0,0.8); }
  .terminal-prompt { font-family: 'Space Mono', monospace; font-size: .8rem; color: var(--green); margin-bottom: 14px; display: flex; align-items: center; gap: 8px; font-weight: 700; }
  textarea#mood-input { width: 100%; background: transparent; border: none; outline: none; resize: none; font-family: 'Space Mono', monospace; font-size: .95rem; color: var(--text); line-height: 1.6; min-height: 90px; caret-color: var(--accent); }
  textarea#mood-input::placeholder { color: #3b3b4f; }
  .char-count { font-family: 'Space Mono', monospace; font-size: .7rem; color: var(--muted); text-align: right; margin-top: 4px; }

  /* Buttons */
  .actions { margin-top: 28px; display: flex; gap: 14px; }
  .btn { display: inline-flex; align-items: center; gap: 10px; padding: 14px 28px; border-radius: 12px; font-size: .95rem; font-weight: 600; cursor: pointer; border: none; transition: all .2s cubic-bezier(0.4, 0, 0.2, 1); }
  .btn-primary { background: var(--accent); color: #fff; flex: 1; justify-content: center; box-shadow: 0 4px 14px rgba(108,99,255,.4); }
  .btn-primary:hover { background: #5b52e6; transform: translateY(-1px); box-shadow: 0 6px 20px rgba(108,99,255,.5); }
  .btn-lucky { background: var(--surface2); color: var(--text); border: 1px solid var(--border); }
  .btn-lucky:hover { background: #252530; border-color: var(--accent); }
  .btn:disabled { opacity: .3; cursor: not-allowed; transform: none !important; box-shadow: none !important; }

  /* Error Banner */
  .error-msg { display: none; margin-top: 20px; padding: 14px 18px; background: rgba(255,107,107,.08); border: 1px solid rgba(255,107,107,.2); border-radius: 10px; font-size: .9rem; color: var(--danger); }
  .error-msg.show { display: block; }
  .lucky-reveal { display: none; margin-top: 20px; padding: 14px 18px; background: rgba(108,99,255,.08); border: 1px solid rgba(108,99,255,.15); border-radius: 10px; font-size: .9rem; color: var(--accent2); font-family: 'Space Mono', monospace; }
  .lucky-reveal.show { display: block; }

  /* Cinematic Process Overlay Screens */
  .overlay { display: none; position: fixed; inset: 0; background: rgba(7,7,10,.95); backdrop-filter: blur(12px); z-index: 200; align-items: center; justify-content: center; flex-direction: column; }
  .overlay.show { display: flex; }
  .overlay-inner { text-align: center; width: 100%; max-width: 400px; padding: 0 24px; }
  .waveform { display: flex; align-items: center; justify-content: center; gap: 6px; height: 60px; margin-bottom: 40px; }
  .bar { width: 5px; border-radius: 3px; background: linear-gradient(to top, var(--accent), var(--accent2)); animation: wave 1.2s ease-in-out infinite; }
  .bar:nth-child(1)  { animation-delay: 0s; height: 20px; } .bar:nth-child(2)  { animation-delay: .1s; height: 35px; } .bar:nth-child(3)  { animation-delay: .2s; height: 50px; } .bar:nth-child(4)  { animation-delay: .3s; height: 35px; } .bar:nth-child(5)  { animation-delay: .4s; height: 60px; } .bar:nth-child(6)  { animation-delay: .5s; height: 40px; } .bar:nth-child(7)  { animation-delay: .6s; height: 60px; } .bar:nth-child(8)  { animation-delay: .7s; height: 35px; } .bar:nth-child(9)  { animation-delay: .8s; height: 50px; } .bar:nth-child(10) { animation-delay: .9s; height: 25px; } .bar:nth-child(11) { animation-delay: 1.0s; height: 35px; } .bar:nth-child(12) { animation-delay: 1.1s; height: 20px; }
  @keyframes wave { 0%, 100% { transform: scaleY(1); opacity: .4; } 50% { transform: scaleY(.2); opacity: 1; } }

  .steps { display: flex; flex-direction: column; gap: 16px; width: 100%; text-align: left; }
  .step { display: flex; align-items: center; gap: 16px; font-size: .95rem; color: var(--muted); transition: color .3s; }
  .step.active { color: var(--text); font-weight: 500; }
  .step.done { color: var(--green); }
  .step-dot { width: 22px; height: 22px; border-radius: 50%; border: 2px solid var(--border); flex-shrink: 0; position: relative; transition: all .3s; }
  .step.active .step-dot { border-color: var(--accent); background: rgba(108,99,255,0.1); }
  .step.done .step-dot { border-color: var(--green); background: var(--green); }
  .step.done .step-dot::after { content: '✓'; position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; font-size: .7rem; color: #fff; font-weight: 700; }
</style>
</head>
<body>

<nav>
    <a class="logo" href="index.php">SONORESU</a>
    <div class="nav-right">
        <span class="username-badge">Hello, <?php echo $username; ?></span>
        <div class="nav-links">
            <a href="sonorous_couch.php" class="active">Terminal</a>
            <a href="history.php">My Playlists</a>
            <a href="lounge.php">Global Lounge</a>
            <a href="logout.php" class="logout-btn">Log out</a>
        </div>
    </div>
</nav>

<div class="shell">
  <div class="card">
    <div class="card-header">
      <div class="label">Sonorous Couch</div>
      <h1>What's your <span>soundtrack</span><br>right now?</h1>
      <p class="subtitle">Describe a feeling, a scene, a moment — anything.<br>We'll find the music that fits perfectly.</p>
    </div>

    <div class="terminal">
      <div class="terminal-prompt">mood_prompt.txt</div>
      <textarea id="mood-input" maxlength="300" placeholder="e.g. 'Rain on a Sunday, cup of tea, no plans for the day...'" autofocus></textarea>
      <div class="char-count"><span id="char-n">0</span> / 300</div>
    </div>

    <div id="lucky-reveal" class="lucky-reveal"></div>
    <div id="error-msg" class="error-msg"></div>

    <div class="actions">
      <button class="btn btn-primary" id="btn-generate">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="margin-right:2px;"><path d="M9 18V5l12-2v13"/><circle cx="6" cy="18" r="3"/><circle cx="18" cy="16" r="3"/></svg>
        Generate My Playlist
      </button>
      <button class="btn btn-lucky" id="btn-lucky" title="Pick a random mood for me">🎲 I'm Feeling Lucky</button>
    </div>
  </div>
</div>

<div class="overlay" id="overlay">
  <div class="overlay-inner">
    <div class="waveform">
      <?php for ($i = 0; $i < 12; $i++): ?>
        <div class="bar"></div>
      <?php endfor; ?>
    </div>
    <div class="steps">
      <div class="step" id="step-0"><div class="step-dot"></div><span>Tuning in to your frequency…</span></div>
      <div class="step" id="step-1"><div class="step-dot"></div><span>Translating emotion to sound…</span></div>
      <div class="step" id="step-2"><div class="step-dot"></div><span>Searching the sonic universe…</span></div>
      <div class="step" id="step-3"><div class="step-dot"></div><span>Assembling your playlist…</span></div>
    </div>
  </div>
</div>

<script>
(() => {
  const textarea = document.getElementById('mood-input');
  const charN = document.getElementById('char-n');
  const btnGen = document.getElementById('btn-generate');
  const btnLucky = document.getElementById('btn-lucky');
  const errorBox = document.getElementById('error-msg');
  const overlay = document.getElementById('overlay');
  const luckyReveal = document.getElementById('lucky-reveal');

  textarea.addEventListener('input', () => { charN.textContent = textarea.value.length; });

  let stepIndex = 0, stepTimer = null, apiDone = false, redirectTo = null;

  function startSteps() {
    activateStep(0);
    stepTimer = setInterval(() => {
      if (stepIndex < 3) { markDone(stepIndex); stepIndex++; activateStep(stepIndex); } 
      else { clearInterval(stepTimer); if (apiDone) doRedirect(); }
    }, 1350);
  }

  function activateStep(i) { document.getElementById(`step-${i}`).classList.add('active'); }
  function markDone(i) { const el = document.getElementById(`step-${i}`); el.classList.remove('active'); el.classList.add('done'); }
  function doRedirect() { markDone(stepIndex); setTimeout(() => { window.location.href = redirectTo; }, 400); }

  function showError(msg) {
    overlay.classList.remove('show'); clearInterval(stepTimer);
    [0,1,2,3].forEach(i => { document.getElementById(`step-${i}`).classList.remove('active', 'done'); });
    stepIndex = 0; apiDone = false; redirectTo = null;
    errorBox.textContent = '⚠ ' + msg; errorBox.classList.add('show');
    btnGen.disabled = false; btnLucky.disabled = false;
  }

  function submit(isLucky) {
    errorBox.classList.remove('show'); luckyReveal.classList.remove('show');
    const prompt = textarea.value.trim();
    if (!isLucky && !prompt) { errorBox.textContent = '⚠ Please describe a mood before generating.'; errorBox.classList.add('show'); textarea.focus(); return; }
    btnGen.disabled = true; btnLucky.disabled = true; overlay.classList.add('show'); apiDone = false; startSteps();

    const body = new URLSearchParams(); body.append('mood_prompt', prompt);
    if (isLucky) body.append('is_lucky', '1');

    fetch('generate_playlist.php', { method: 'POST', body })
      .then(r => r.json())
      .then(data => {
        if (!data.success) { showError(data.error || 'Something went wrong.'); return; }
        if (data.lucky_prompt) {
          textarea.value = data.lucky_prompt; charN.textContent = data.lucky_prompt.length;
          luckyReveal.textContent = `🎲 "${data.lucky_prompt}"`; luckyReveal.classList.add('show');
        }
        redirectTo = data.redirect; apiDone = true;
        if (stepIndex >= 3) doRedirect();
      })
      .catch(() => showError('Network error. Check your connection and try again.'));
  }

  btnGen.addEventListener('click', () => submit(false));
  btnLucky.addEventListener('click', () => submit(true));
  textarea.addEventListener('keydown', e => { if ((e.metaKey || e.ctrlKey) && e.key === 'Enter') submit(false); });
})();
</script>
</body>
</html>