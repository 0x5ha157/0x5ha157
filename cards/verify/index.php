<?php
// verify/index.php

// --- BACKEND LOGIC ---
$id = $_GET['id'] ?? '';
$safeID = preg_replace('/[^a-zA-Z0-9]/', '', $id);

// Look for the filename directly inside this folder
$filePath = $safeID . '.json'; 

$isValid = false;
$userData = [];

if (!empty($safeID) && file_exists($filePath)) {
    $isValid = true;
    $jsonContent = file_get_contents($filePath);
    $userData = json_decode($jsonContent, true);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $isValid ? "VERIFIED: $safeID" : "ERROR: Invalid ID"; ?></title>
    <style>
        /* --- CSS STYLES --- */
        :root {
            --bg-color: #0d1117;
            --text-color: #c9d1d9;
            --error-color: #ff7b72;
            --cmd-color: #7ee787;  /* Green */
            --accent-color: #58a6ff; /* Blue */
            --dim-color: #8b949e;
            --font-stack: 'Consolas', 'Monaco', 'Andale Mono', monospace;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            background-color: var(--bg-color);
            color: var(--text-color);
            font-family: var(--font-stack);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            cursor: crosshair; 
        }

        /* CRT SCANLINE EFFECT */
        body::before {
            content: " ";
            display: block;
            position: absolute;
            top: 0; left: 0; bottom: 0; right: 0;
            background: linear-gradient(
                to bottom, 
                rgba(18, 16, 16, 0) 50%, 
                rgba(0, 0, 0, 0.25) 50%
            );
            background-size: 100% 4px;
            z-index: 2;
            pointer-events: none;
        }

        .terminal-window {
            width: 100%;
            max-width: 800px;
            padding: 20px;
            border: 1px solid <?php echo $isValid ? 'var(--cmd-color)' : 'var(--error-color)'; ?>; 
            box-shadow: 0 0 20px <?php echo $isValid ? 'rgba(126, 231, 135, 0.1)' : 'rgba(255, 123, 114, 0.1)'; ?>;
            position: relative;
            z-index: 1;
            background: rgba(13, 17, 23, 0.95);
        }

        /* STATUS BAR */
        .status-bar {
            display: flex;
            justify-content: space-between;
            border-bottom: 1px solid var(--dim-color);
            padding-bottom: 10px;
            margin-bottom: 20px;
            font-size: 0.8rem;
            color: var(--dim-color);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .status-item { margin-right: 15px; }
        .status-value { color: var(--accent-color); }

        .prompt { color: var(--cmd-color); font-weight: bold; }
        .path { color: var(--accent-color); }
        .command { color: #fff; }
        
        /* DATA TABLE STYLING */
        .data-grid {
            display: grid;
            grid-template-columns: 120px 1fr;
            gap: 10px;
            margin-top: 1rem;
            margin-bottom: 1rem;
            font-size: 0.95rem;
        }
        .label { color: var(--dim-color); text-transform: uppercase; }
        .value { color: var(--text-color); font-weight: bold; }
        .alien-text { letter-spacing: 2px; color: var(--accent-color); }
        
        .pgp-block {
            margin-top: 10px;
            color: var(--dim-color);
            font-size: 0.8rem;
            border-left: 2px solid var(--dim-color);
            padding-left: 10px;
        }

        .error-msg {
            color: var(--error-color);
            margin-top: 1rem;
            margin-bottom: 1rem;
            font-weight: bold;
            text-shadow: 0 0 5px rgba(255, 123, 114, 0.3);
        }

        .success-msg {
            color: var(--cmd-color);
            margin-top: 1rem;
            margin-bottom: 1rem;
            font-weight: bold;
            text-shadow: 0 0 5px rgba(126, 231, 135, 0.3);
        }

        .ascii-art {
            font-size: 0.6rem;
            line-height: 1.2;
            white-space: pre;
            color: <?php echo $isValid ? 'var(--cmd-color)' : 'var(--error-color)'; ?>;
            margin: 1.5rem 0;
            opacity: 0.8;
            font-weight: bold;
        }

        /* BUTTON STYLING */
        .btn-container {
            margin-top: 1.5rem;
            display: flex;
            gap: 15px;
        }

        a.home-btn {
            color: var(--bg-color);
            background-color: var(--cmd-color);
            text-decoration: none;
            padding: 8px 15px;
            font-weight: bold;
            display: inline-block;
            border: 2px solid var(--cmd-color);
            transition: all 0.2s;
            cursor: pointer;
            text-transform: uppercase;
            font-size: 0.9rem;
        }

        a.home-btn:hover {
            background-color: transparent;
            color: var(--cmd-color);
            box-shadow: 0 0 10px var(--cmd-color);
        }
        
        a.view-card-btn {
            color: var(--bg-color);
            background-color: var(--accent-color); /* Blue button */
            text-decoration: none;
            padding: 8px 15px;
            font-weight: bold;
            display: inline-block;
            border: 2px solid var(--accent-color);
            transition: all 0.2s;
            cursor: pointer;
            text-transform: uppercase;
            font-size: 0.9rem;
        }

        a.view-card-btn:hover {
            background-color: transparent;
            color: var(--accent-color);
            box-shadow: 0 0 10px var(--accent-color);
        }

        /* Blinking Cursor */
        .cursor-block {
            display: inline-block;
            width: 10px;
            height: 1.2em;
            background-color: var(--cmd-color);
            animation: blink 1s step-end infinite;
            vertical-align: middle;
            margin-left: 5px;
        }

        @keyframes blink { 50% { opacity: 0; } }

        @media (max-width: 600px) {
            .ascii-art { font-size: 0.4rem; }
            .status-bar { flex-direction: column; gap: 5px; }
            .data-grid { grid-template-columns: 1fr; gap: 2px; margin-bottom: 15px; }
            .label { font-size: 0.8rem; margin-top: 5px;}
        }
    </style>
</head>
<body>

    <div class="terminal-window">
        <div class="status-bar">
            <div class="status-item">TARGET: <span class="status-value"><?php echo $isValid ? $safeID : "UNKNOWN"; ?></span></div>
            <div class="status-item">SYS_TIME: <span id="clock" class="status-value">00:00:00</span></div>
            <div class="status-item">STATUS: 
                <span style="color: <?php echo $isValid ? 'var(--cmd-color)' : 'var(--error-color)'; ?>;">
                    <?php echo $isValid ? "VERIFIED" : "NOT_FOUND"; ?>
                </span>
            </div>
        </div>

        <div>
            <span class="prompt">root@0x5ha157</span>:<span class="path">/var/www/verify</span>$ 
            <span class="command">./check_identity -id <?php echo $safeID ? $safeID : "NULL"; ?></span>
        </div>
        
        <?php if ($isValid): ?>
            <div class="ascii-art">
   _   _  _____ ____   ___ _____ ___ _____ ____  
  | | | || ____|  _ \|_ _|  ___|_ _| ____|  _ \ 
  | | | ||  _| | |_) || || |_   | ||  _| | | | |
  \ \_/ /| |___|  _ < | ||  _|  | || |___| |_| |
   \___/ |_____|_| \_\___|_|   |___|_____|____/ 
            </div>

            <div class="success-msg">
                [+] SUCCESS: IDENTITY MATCH FOUND IN DATABASE.<br>
                > Access Granted. Retrieving subject data...
            </div>

            <div class="data-grid">
                <div class="label">FULL NAME:</div>
                <div class="value"><?php echo htmlspecialchars($userData['userData']['name'] ?? 'N/A'); ?></div>

                <div class="label">ROLE:</div>
                <div class="value"><?php echo htmlspecialchars($userData['userData']['role'] ?? 'N/A'); ?></div>

                <div class="label">EMAIL:</div>
                <div class="value"><?php echo htmlspecialchars($userData['userData']['email'] ?? 'N/A'); ?></div>

                <div class="label">PHONE:</div>
                <div class="value"><?php echo htmlspecialchars($userData['userData']['phone'] ?? 'N/A'); ?></div>

                <div class="label">CERTS:</div>
                <div class="value"><?php echo htmlspecialchars($userData['userData']['certs'] ?? 'N/A'); ?></div>

                <div class="label">SGA ID:</div>
                <div class="value alien-text"><?php echo htmlspecialchars($userData['sga'] ?? 'N/A'); ?></div>

                <div class="label">TIMESTAMP:</div>
                <div class="value"><?php echo htmlspecialchars($userData['timestamp'] ?? 'N/A'); ?></div>
            </div>

            <div class="pgp-block">
                > DECRYPTING PGP KEY...<br>
                <?php echo htmlspecialchars($userData['pgp'] ?? 'NO_KEY_FOUND'); ?>
            </div>

        <?php else: ?>
            <div class="ascii-art">
   _____ ____   ____   ___  ____  
  | ____|  _ \|  _ \ / _ \|  _ \ 
  |  _| | |_) | |_) | | | | |_) |
  | |___|  _ <|  _ <| |_| |  _ < 
  |_____|_| \_\_| \_\\___/|_| \_\
            </div>

            <div class="error-msg">
                [!] FATAL ERROR: IDENTITY_NOT_FOUND<br>
                > The requested ID "<?php echo htmlspecialchars($safeID); ?>" does not exist in the registry.
            </div>

            <div style="color: var(--dim-color); margin-bottom: 1.5rem; font-size: 0.9rem; line-height: 1.6;">
                > searching local database... <span style="color:var(--error-color)">FAILED</span><br>
                > searching cloud archives... <span style="color:var(--error-color)">FAILED</span><br>
                > verifying integrity... <span style="color:var(--error-color)">NULL</span><br>
                > connection terminated.
            </div>

        <?php endif; ?>

        <br>
        <div>
            <span class="prompt">root@0x5ha157</span>:<span class="path">~</span>$ <span class="command">./exit_session.sh</span>
        </div>
        
        <div class="btn-container">
            <a href="/" class="home-btn">RETURN_HOME</a>
            <?php if ($isValid): ?>
                <a href="view_card.php?id=<?php echo $safeID; ?>" class="view-card-btn">VIEW_CARD</a>
            <?php endif; ?>
        </div>
        
        <br>
        <span class="cursor-block"></span>
    </div>

    <script>
        // 1. CLOCK SCRIPT
        function updateClock() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('en-GB', { hour12: false });
            document.getElementById('clock').innerText = timeString;
        }
        setInterval(updateClock, 1000);
        updateClock(); 
    </script>

</body>
</html>
