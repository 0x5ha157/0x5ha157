<?php
// save_identity.php

// 1. Allow Access from Anywhere (CORS)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json");

// 2. CRITICAL FIX: Handle the "Pre-flight" Handshake (OPTIONS request)
// Browsers send this empty request first to check if it's safe to send data.
// We must exit with 200 OK, otherwise the script tries to read empty data and fails.
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// 3. Setup the save folder
$saveDir = 'verify';
if (!file_exists($saveDir)) {
    mkdir($saveDir, 0777, true);
}

// 4. Get the JSON data
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if ($data) {
    $id = $data['id'] ?? 'unknown';
    
    // Clean ID
    $safeID = preg_replace('/[^a-zA-Z0-9]/', '', $id);
    if(empty($safeID)) $safeID = 'identity_' . time();

    $filename = $saveDir . '/' . $safeID . '.json';

    // Save Data
    $jsonContent = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    if (file_put_contents($filename, $jsonContent)) {
        echo json_encode([
            "status" => "success", 
            "file" => $filename, 
            "id" => $safeID,
            "message" => "Identity saved successfully"
        ]);
    } else {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Server write permission denied"]);
    }
} else {
    // This is where your error 400 was coming from
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "No JSON data received. Raw input: " . substr($json, 0, 100)]);
}
?>
