<?php
/* logger.php - FIXED for Docker/Proxies */

// 1. Get the JSON data
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if ($data) {
    // --- FIX START: Get Real IP ---
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        // If multiple IPs, the first one is the real user
        $ipList = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $ip = trim($ipList[0]);
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    // --- FIX END ---

    $event = $data['event'] ?? 'Unknown Event';
    $date = date("Y-m-d H:i:s");

    // Format and Write
    $logLine = "[$date] IP: $ip | ACTION: $event" . PHP_EOL;
    file_put_contents('security.log', $logLine, FILE_APPEND);
}
?>
