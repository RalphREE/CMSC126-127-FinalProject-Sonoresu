<?php
// public/generate_playlist.php
session_start();
require_once '../includes/gemini.php';
require_once '../includes/spotify.php';

header('Content-Type: application/json');

// Authentication guard 
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Method not allowed.']);
    exit;
}

// ── Resolve mood prompt ──────────────────────────────────────
$is_lucky    = !empty($_POST['is_lucky']);
$mood_prompt = trim($_POST['mood_prompt'] ?? '');

if ($is_lucky) {
    $lucky_prompts = [
        "Rain on a Sunday, cup of tea, cozy blanket",
        "Driving at 2 AM with nowhere in particular to go",
        "First day of summer, windows down, no plans",
        "Late-night study session, almost across the finish line",
        "Heartbroken but slowly, quietly healing",
        "Dancing completely alone in the kitchen at midnight",
        "Nostalgic for a time that never quite existed",
        "Golden-hour ocean waves and nothing else to think about",
        "City lights blurring in the rain outside the window",
        "Waking up early on a day that's entirely yours",
    ];
    $mood_prompt = $lucky_prompts[array_rand($lucky_prompts)];
}

if (empty($mood_prompt)) {
    echo json_encode(['success' => false, 'error' => 'Mood prompt is required.']);
    exit;
}

// ── Orchestrate API calls ────────────────────────────────────
try {
    $mood_data = analyze_mood_with_gemini($mood_prompt);
    $token     = get_spotify_token();
    $tracks    = get_spotify_recommendations($token, $mood_data);

    $_SESSION['playlist_data'] = [
        'original_prompt' => $mood_prompt,
        'mood_data'       => $mood_data,
        'tracks'          => $tracks,
        'saved'           => false,
    ];

    echo json_encode([
        'success'      => true,
        'lucky_prompt' => $is_lucky ? $mood_prompt : null,
        'redirect'     => 'soundtrack.php',
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
