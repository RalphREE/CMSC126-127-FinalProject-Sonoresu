<?php
// public/save_playlist.php
session_start();
require_once '../config/db.php';

header('Content-Type: application/json');

// ── Guards ───────────────────────────────────────────────────
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated.']);
    exit;
}

if (empty($_SESSION['playlist_data'])) {
    echo json_encode(['success' => false, 'error' => 'No playlist in session.']);
    exit;
}

if (!empty($_SESSION['playlist_data']['saved'])) {
    // Idempotency: already saved this session
    echo json_encode(['success' => true, 'already_saved' => true]);
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$pd      = $_SESSION['playlist_data'];

try {
    $conn->begin_transaction();

    // ── 1. Insert Playlist row ───────────────────────────────
    $stmt = $conn->prepare("INSERT INTO Playlist (user_id, original_prompt) VALUES (?, ?)");
    $stmt->bind_param("is", $user_id, $pd['original_prompt']);
    $stmt->execute();
    $playlist_id = (int)$conn->insert_id;
    $stmt->close();

    // ── 2. Upsert each Track, then map to Playlist ───────────
    $ins_track = $conn->prepare(
        "INSERT IGNORE INTO Track (spotify_track_id, title, artist, energy_score) VALUES (?, ?, ?, ?)"
    );
    $get_track = $conn->prepare(
        "SELECT track_id FROM Track WHERE spotify_track_id = ?"
    );
    $ins_map   = $conn->prepare(
        "INSERT IGNORE INTO Playlist_Map (playlist_id, track_id) VALUES (?, ?)"
    );

    $energy = (float)$pd['mood_data']['energy'];

    foreach ($pd['tracks'] as $t) {
        $sid    = $t['spotify_track_id'];
        $title  = $t['title'];
        $artist = $t['artist'];

        $ins_track->bind_param("sssd", $sid, $title, $artist, $energy);
        $ins_track->execute();

        // Get track_id whether it was just inserted or already existed
        $get_track->bind_param("s", $sid);
        $get_track->execute();
        $row      = $get_track->get_result()->fetch_assoc();
        $track_id = (int)$row['track_id'];

        $ins_map->bind_param("ii", $playlist_id, $track_id);
        $ins_map->execute();
    }

    $ins_track->close();
    $get_track->close();
    $ins_map->close();

    $conn->commit();

    // Mark saved so the button can't double-fire
    $_SESSION['playlist_data']['saved']       = true;
    $_SESSION['playlist_data']['playlist_id'] = $playlist_id;

    echo json_encode(['success' => true, 'playlist_id' => $playlist_id]);

} catch (Throwable $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
