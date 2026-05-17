<?php
// includes/youtube.php
require_once __DIR__ . '/../config/api_keys.php';

function youtube_search_top_result(string $query): array {
    $apiKey = YOUTUBE_API_KEY;
    if (empty($apiKey)) throw new Exception('YouTube API key not configured.');

    // OPTIMIZATION: Force official audio and block shorts natively!
    $optimizedQuery = $query . ' official audio -shorts';

    $params = http_build_query([
        'part' => 'snippet',
        'q' => $optimizedQuery,
        'maxResults' => 1,
        'type' => 'video',
        'key' => $apiKey,
    ]);

    $url = 'https://www.googleapis.com/youtube/v3/search?' . $params;

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [ 'Accept: application/json' ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 8);
    // 🚨 LOCALHOST FIXES 🚨
    curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4); 
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

    $resp = curl_exec($ch);
    $err  = curl_error($ch);
    curl_close($ch);

    if ($resp === false || $err) {
        throw new Exception('YouTube search failed: ' . $err);
    }

    $data = json_decode($resp, true);

    if (isset($data['error'])) {
        throw new Exception('YouTube API Error (Quota?): ' . $data['error']['message']);
    }
    
    if (!$data || empty($data['items'][0])) {
         throw new Exception('No YouTube result found.');
    }

    $item = $data['items'][0];
    $videoId = $item['id']['videoId'] ?? null;
    $title   = $item['snippet']['title'] ?? $query;
    $channel = $item['snippet']['channelTitle'] ?? '';

    $thumbnail = '';
    if (!empty($item['snippet']['thumbnails']['maxres']['url'])) $thumbnail = $item['snippet']['thumbnails']['maxres']['url'];
    elseif (!empty($item['snippet']['thumbnails']['high']['url'])) $thumbnail = $item['snippet']['thumbnails']['high']['url'];
    elseif (!empty($item['snippet']['thumbnails']['medium']['url'])) $thumbnail = $item['snippet']['thumbnails']['medium']['url'];
    elseif (!empty($item['snippet']['thumbnails']['default']['url'])) $thumbnail = $item['snippet']['thumbnails']['default']['url'];

    if (!$videoId) throw new Exception('Missing videoId');

    return [ 'id' => $videoId, 'title' => $title, 'channel' => $channel, 'thumbnail' => $thumbnail ];
}
?>