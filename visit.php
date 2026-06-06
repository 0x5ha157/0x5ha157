<?php
$db_file = '/var/www/html/unique_visitors.json';
$cookie_name = '0x5ha157_tracked';

// 1. Check if the user already has our tracking cookie
$has_cookie = isset($_COOKIE[$cookie_name]);

// 2. Load the existing visitors database
$visitors = [];
if (file_exists($db_file)) {
    $json_data = file_get_contents($db_file);
    $visitors = json_decode($json_data, true);
    if (!is_array($visitors)) {
        $visitors = [];
    }
}

// 3. Capture the real client IP (handling Nginx proxies safely)
$ip = $_SERVER['REMOTE_ADDR'];
if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $ip_array = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
    $ip = trim($ip_array[0]);
}

// 4. The "Honest" Counter Logic
if (!$has_cookie && !in_array($ip, $visitors)) {
    // Brand new visitor! Drop a cookie that expires in 1 year
    setcookie($cookie_name, 'v_set', time() + (365 * 24 * 60 * 60), '/');
    
    // Log their IP to the backup JSON file
    $visitors[] = $ip;
    file_put_contents($db_file, json_encode($visitors));
} 
elseif (!$has_cookie && in_array($ip, $visitors)) {
    // Edge case: They cleared their cookies but their IP is already in our logs.
    // Give them the cookie back so changing IPs later won't trick the system, 
    // but DO NOT increase the visitor count.
    setcookie($cookie_name, 'v_set', time() + (365 * 24 * 60 * 60), '/');
}

// 5. Output the total payload back to your dashboard
header('Content-Type: application/json');
echo json_encode(["total_unique" => count($visitors)]);
?>
