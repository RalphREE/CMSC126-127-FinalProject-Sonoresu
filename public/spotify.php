<?php
// includes/spotify.php
require_once __DIR__ . '/../config/api_keys.php';

/**
 * Fetches a Client Credentials access token from Spotify.
 * Note: The Recommendations endpoint requires your app to be in
 * Extended Quota Mode on the Spotify Developer Dashboard.
 */
function get_spotify_token(): string {
    $credentials = base64_encode(SPOTIFY_CLIENT_ID . ':' . SPOTIFY_CLIENT_SECRET);

    $ch = curl_init('https://accounts.spotify.com/api/token');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => 'grant_type=client_credentials',
        CURLOPT_HTTPHEADER     => [
            'Authorization: Basic ' . $credentials,
            'Content-Type: application/x-www-form-urlencoded',
        ],
        CURLOPT_TIMEOUT => 10,
    ]);

    $response = curl_exec($ch);
    $err      = curl_error($ch);
    curl_close($ch);

    if ($err) throw new Exception("Spotify auth failed: $err");

    $data = json_decode($response, true);
    if (empty($data['access_token'])) {
        throw new Exception("No access token returned by Spotify.");
    }

    return $data['access_token'];
}

/**
 * Gets 10 track recommendations from Spotify using audio feature targets.
 * Falls back to genre search if the Recommendations endpoint is unavailable.
 */
function get_spotify_recommendations(string $token, array $mood_data, int $limit = 10): array {
    $params = http_build_query([
        'seed_genres'    => implode(',', $mood_data['genres']),
        'target_valence' => round($mood_data['valence'], 2),
        'target_energy'  => round($mood_data['energy'],  2),
        'target_tempo'   => $mood_data['tempo'],
        'limit'          => $limit,
    ]);

    $ch = curl_init('https://api.spotify.com/v1/recommendations?' . $params);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $token],
        CURLOPT_TIMEOUT        => 15,
    ]);

    $response = curl_exec($ch);
    $err      = curl_error($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($err) throw new Exception("Spotify recommendations request failed: $err");

    $data = json_decode($response, true);

    // Fall back to genre search if recommendations unavailable (403 / missing tracks)
    if ($http_code !== 200 || empty($data['tracks'])) {
        return spotify_genre_search($token, $mood_data, $limit);
    }

    return normalize_tracks($data['tracks']);
}

/**
 * Fallback: search tracks by genre keyword.
 */
function spotify_genre_search(string $token, array $mood_data, int $limit = 10): array {
    $genre = $mood_data['genres'][0] ?? 'pop';
    $params = http_build_query([
        'q'     => "genre:$genre",
        'type'  => 'track',
        'limit' => $limit,
    ]);

    $ch = curl_init('https://api.spotify.com/v1/search?' . $params);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $token],
        CURLOPT_TIMEOUT        => 15,
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($response, true);

    if (empty($data['tracks']['items'])) {
        throw new Exception("No tracks returned from Spotify search.");
    }

    return normalize_tracks($data['tracks']['items']);
}

/**
 * Normalizes raw Spotify track objects into a clean array.
 */
function normalize_tracks(array $raw_tracks): array {
    $tracks = [];
    foreach ($raw_tracks as $t) {
        $tracks[] = [
            'spotify_track_id' => $t['id'],
            'title'            => $t['name'],
            'artist'           => implode(', ', array_column($t['artists'], 'name')),
            'album'            => $t['album']['name'] ?? '',
            'album_art'        => $t['album']['images'][1]['url']
                                  ?? ($t['album']['images'][0]['url'] ?? ''),
            'preview_url'      => $t['preview_url'] ?? null,
            'spotify_url'      => $t['external_urls']['spotify'] ?? '',
            'duration_ms'      => $t['duration_ms'] ?? 0,
        ];
    }
    return $tracks;
}
