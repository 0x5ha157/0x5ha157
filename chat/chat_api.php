<?php
// FILE: /var/www/html/chat_api.php
$file = 'chat_log.txt';

// 1. RECEIVE MESSAGE (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Basic security: remove HTML tags so people can't hack your site via chat
    $user = htmlspecialchars($_POST['user']); 
    $msg = htmlspecialchars($_POST['msg']);
    $time = date('H:i');
    
    // Create the HTML line for the message
    $entry = "<div class='msg'><span class='time'>[$time]</span> <span class='user'>$user:</span> <span class='text'>$msg</span></div>\n";
    
    // Write to the text file
    file_put_contents($file, $entry, FILE_APPEND);
    exit();
}

// 2. SEND MESSAGES (GET)
if (file_exists($file)) {
    // Read the file and show the last 50 lines
    $lines = file($file);
    $lines = array_slice($lines, -50); 
    foreach ($lines as $line) {
        echo $line;
    }
}
?>
