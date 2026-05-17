<?php
// public/generate_playlist.php
ob_start(); 
set_time_limit(150); 
error_reporting(E_ALL);
ini_set('display_errors', 0); 

register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        ob_clean(); 
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'PHP FATAL ERROR: ' . $error['message']]);
        exit;
    }
});

set_exception_handler(function($e) {
    ob_clean();
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'SYSTEM CRASH: ' . $e->getMessage()]);
    exit;
});

session_start();
require_once __DIR__ . '/../includes/gemini.php';
require_once __DIR__ . '/../includes/youtube.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Method not allowed.']);
    exit;
}

$is_lucky    = !empty($_POST['is_lucky']);
$mood_prompt = trim($_POST['mood_prompt'] ?? '');

if ($is_lucky) {
    $lucky_prompts = [
        "Rain on a Sunday, cup of tea, cozy blanket",
        "Driving at 2 AM with nowhere in particular to go",
        "Late-night study session, almost across the finish line",
        "Dancing completely alone in the kitchen at midnight",
        "Nostalgic for a time that never quite existed",
    ];
    $mood_prompt = $lucky_prompts[array_rand($lucky_prompts)];
}

if (empty($mood_prompt)) {
    echo json_encode(['success' => false, 'error' => 'Mood prompt is required.']);
    exit;
}

try {
    // ── Generate 15 Songs via Gemini ──
    $songList = null;
    $maxAttempts = 2;
    $attempt = 0;
    
    while ($attempt < $maxAttempts && $songList === null) {
        $attempt++;
        try {
            $songList = analyze_mood_with_gemini($mood_prompt); 
        } catch (Exception $e) {
            if ($attempt >= $maxAttempts) {
                echo json_encode(['success' => false, 'error' => 'GEMINI CRASHED: ' . $e->getMessage()]);
                exit;
            }
        }
    }

    // ── Fetch YouTube Data for all 15 ──
    $youTubeItems = [];
    $seenIds = [];
    $seenTitles = []; 

    foreach ($songList as $q) {
        if (count($youTubeItems) >= 15) break; 
        
        try {
            $res = youtube_search_top_result($q);
            
            if (!empty($res['id'])) {
                $id = $res['id'];
                $titleLower = strtolower(trim($res['title']));

                // Duplicate Tracker
                if (!in_array($id, $seenIds) && !in_array($titleLower, $seenTitles)) {
                    $youTubeItems[] = [
                        'query'     => $q,
                        'id'        => $id,
                        'title'     => $res['title'],
                        'channel'   => $res['channel'] ?? '',
                        'thumbnail' => $res['thumbnail'] ?? '',
                    ];
                    $seenIds[] = $id;
                    $seenTitles[] = $titleLower;
                }
            }
        } catch (Exception $e) {
            // STOP HIDING YOUTUBE ERRORS! Show them to the frontend:
            echo json_encode(['success' => false, 'error' => 'YOUTUBE CRASHED on "' . $q . '": ' . $e->getMessage()]);
            exit;
        }
    }

    if (count($youTubeItems) < 15) {
        echo json_encode(['success' => false, 'error' => 'Not enough unique songs found. Try a different prompt!']);
        exit;
    }

    // ── Prepare Output ──
    $main = array_slice($youTubeItems, 0, 5);
    $replacements = array_slice($youTubeItems, 5, 10);

    $_SESSION['playlist_data'] = [
        'original_prompt' => $mood_prompt,
        'song_queries'    => $songList,
        'main'            => $main,
        'replacements'    => $replacements,
        'saved'           => false,
        'generated_at'    => time(),
    ];

    echo json_encode([
        'success'      => true,
        'lucky_prompt' => $is_lucky ? $mood_prompt : null,
        'redirect'     => 'results.php',
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>