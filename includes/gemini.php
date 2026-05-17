<?php
// includes/gemini.php
require_once __DIR__ . '/../config/api_keys.php';

function analyze_mood_with_gemini(string $mood_prompt): array {
    $apiKey = GEMINI_API_KEY;
    if (empty($apiKey)) throw new Exception('Gemini API key not configured.');

    $system = "You are a music recommendation engine. Based on the user's specific input: '{$mood_prompt}', recommend exactly 15 highly relevant, REAL, existing songs. Do not just pick the most famous mainstream songs; provide a diverse, dynamic, and unique selection tailored specifically to the nuances of the input. Do NOT hallucinate or invent songs. Your output must be STRICTLY a raw JSON array of 15 strings formatted as 'Song Title by Artist Name'. Do not include markdown or explanations.";

    $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=' . urlencode($apiKey);

    $payload = [
        'system_instruction' => [ 'parts' => [ [ 'text' => $system ] ] ],
        'contents' => [ [ 'parts' => [ [ 'text' => $mood_prompt ] ] ] ],
        'generationConfig' => [ 'temperature' => 0.9, 'responseMimeType' => 'application/json' ]
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [ 'Content-Type: application/json' ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);
    
    curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4); 
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

    $resp = curl_exec($ch);
    $err  = curl_error($ch);
    curl_close($ch);

    if ($resp === false || $err) {
        throw new Exception('cURL Error connecting to Gemini: ' . $err);
    }

    $data = json_decode($resp, true);
    
    if (isset($data['error'])) {
        throw new Exception('Gemini API Error: ' . $data['error']['message']);
    }

    if (!isset($data['candidates'][0]['content']['parts'][0]['text'])) {
        throw new Exception('Invalid response structure from Gemini.');
    }

    $text = $data['candidates'][0]['content']['parts'][0]['text'];
    
    // BULLETPROOF JSON CLEANER
    $text = trim($text);
    $text = preg_replace('/^```json/i', '', $text);
    $text = preg_replace('/```$/', '', $text);
    $text = trim($text);

    $arr = json_decode($text, true);

    if (!is_array($arr) || count($arr) < 5) {
        throw new Exception('Gemini did not return a valid array. Output was: ' . $text);
    }

    if (count($arr) > 15) $arr = array_slice($arr, 0, 15);

    return array_map(function($s){ return trim((string)$s); }, $arr);
}
?>