<?php
// verify/view_card.php

// 1. Get and Sanitize ID
$id = $_GET['id'] ?? '';
$safeID = preg_replace('/[^a-zA-Z0-9]/', '', $id);
$filePath = $safeID . '.json';

// 2. Check if file exists
$cardFound = false;
$data = [];
$user = [];
$qrBase64 = ""; // Variable to hold our embedded image

if (!empty($safeID) && file_exists($filePath)) {
    $cardFound = true;
    $jsonContent = file_get_contents($filePath);
    $data = json_decode($jsonContent, true);
    $user = $data['userData'] ?? [];

    // 3. Generate QR Code URL
    $currentURL = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    $verifyURL = str_replace("view_card.php", "index.php", $currentURL);
    
    // API URL
    $qrApiUrl = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . urlencode($verifyURL) . "&color=000000&bgcolor=ffffff";

    // 4. Fetch image server-side for Base64 embedding (prevents blank QR on download)
    $imageData = @file_get_contents($qrApiUrl);
    if ($imageData !== false) {
        $qrBase64 = 'data:image/png;base64,' . base64_encode($imageData);
    } else {
        // Fallback transparent pixel
        $qrBase64 = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7'; 
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $cardFound ? "IDENTITY: $safeID" : "ERROR: NOT FOUND"; ?></title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <style>
        :root {
            --bg-color: #0d1117;
            --card-bg: #161b22;
            --text-color: #c9d1d9;
            --cmd-color: #7ee787;  
            --error-color: #ff7b72;
            --accent-color: #58a6ff;
            --dim-color: #8b949e;
            --border-color: #30363d;
            --font-stack: 'Consolas', 'Monaco', monospace;
        }

        body {
            background-color: var(--bg-color);
            color: var(--text-color);
            font-family: var(--font-stack);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
            /* Scanline background effect */
            background-image: linear-gradient(rgba(18, 16, 16, 0) 50%, rgba(0, 0, 0, 0.25) 50%), linear-gradient(90deg, rgba(255,0,0,0.06), rgba(0,255,0,0.02), rgba(0,0,255,0.06));
            background-size: 100% 2px, 3px 100%;
        }

        /* --- THE CARD DESIGN --- */
        #identity-card {
            width: 600px;
            background-color: var(--bg-color);
            /* On screen, it has a thicker border and shadow for depth */
            border: 2px solid var(--border-color);
            border-radius: 12px;
            padding: 25px;
            position: relative;
            box-shadow: 0 0 35px rgba(0,0,0,0.6);
            background-image: radial-gradient(circle at top right, #1f242e 0%, #0d1117 60%);
        }

        .header {
            display: flex;
            justify-content: space-between;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 10px;
            margin-bottom: 20px;
            font-size: 0.8rem;
            color: var(--cmd-color);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .main-content {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .details { flex: 1; }

        .row {
            margin-bottom: 15px;
            display: flex;
            flex-direction: column;
        }

        .label {
            color: var(--dim-color);
            font-size: 0.7rem;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 3px;
        }

        .value {
            color: #fff;
            font-weight: bold;
            font-size: 1.1rem;
            text-shadow: 0 0 2px rgba(255,255,255,0.1);
            letter-spacing: 0.5px;
        }

        .qr-column {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-left: 20px;
        }

        .qr-section {
            width: 140px;
            height: 140px;
            border: 2px solid #fff;
            padding: 5px;
            background: #fff;
            border-radius: 4px;
        }
        
        .qr-section img {
            width: 100%;
            height: 100%;
            display: block;
        }

        .alien-text {
            font-family: sans-serif;
            letter-spacing: 2px;
            color: var(--accent-color);
            margin-top: 15px;
            text-align: center;
            font-size: 1rem;
            border-top: 1px solid var(--dim-color);
            padding-top: 10px;
            width: 100%;
        }

        .footer {
            margin-top: 25px;
            border-top: 1px dashed var(--dim-color);
            padding-top: 15px;
            font-size: 0.75rem;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }

        .pgp-key {
            font-family: monospace;
            color: var(--accent-color);
            background: rgba(88, 166, 255, 0.1);
            padding: 5px 10px;
            border-radius: 4px;
            border: 1px solid rgba(88, 166, 255, 0.2);
            font-size: 0.8rem;
        }

        .ip-add { color: var(--cmd-color); }

        /* --- CONTROLS & ERROR STYLES --- */
        .controls {
            margin-top: 30px;
            display: flex;
            gap: 20px;
        }

        .btn {
            background: transparent;
            color: var(--cmd-color);
            border: 2px solid var(--cmd-color);
            padding: 10px 20px;
            font-family: var(--font-stack);
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            text-transform: uppercase;
            transition: 0.2s;
            font-size: 0.9rem;
        }

        .btn:hover {
            background: var(--cmd-color);
            color: var(--bg-color);
            box-shadow: 0 0 15px var(--cmd-color);
        }

        .btn.download {
            color: var(--accent-color);
            border-color: var(--accent-color);
        }
        .btn.download:hover {
            background: var(--accent-color);
            color: var(--bg-color);
            box-shadow: 0 0 15px var(--accent-color);
        }

        /* ERROR CONTAINER */
        .error-container {
            border: 1px solid var(--error-color);
            padding: 40px;
            text-align: center;
            box-shadow: 0 0 20px rgba(255, 123, 114, 0.1);
            background: rgba(13, 17, 23, 0.95);
            max-width: 500px;
        }

        .error-title {
            color: var(--error-color);
            font-size: 1.5rem;
            margin-bottom: 10px;
            text-shadow: 0 0 5px var(--error-color);
        }

        .error-desc {
            color: var(--dim-color);
            margin-bottom: 20px;
        }

        @media (max-width: 650px) {
            #identity-card { width: 100%; padding: 15px; }
            .main-content { flex-direction: column-reverse; }
            .qr-column { margin-left: 0; margin-bottom: 20px; width: 100%; }
            .alien-text { width: auto; }
            .footer { flex-direction: column; gap: 10px; align-items: flex-start; }
        }
    </style>
</head>
<body>

<?php if ($cardFound): ?>

    <div id="identity-card">
        <div class="header">
            <span>● SECURE_CONNECTION</span>
            <span>ID: <?php echo htmlspecialchars($data['id']); ?></span>
        </div>

        <div class="main-content">
            <div class="details">
                <div class="row">
                    <span class="label">Name</span>
                    <span class="value"><?php echo htmlspecialchars($user['name']); ?></span>
                </div>
                <div class="row">
                    <span class="label">Role</span>
                    <span class="value"><?php echo htmlspecialchars($user['role']); ?></span>
                </div>
                <div class="row">
                    <span class="label">Email</span>
                    <span class="value"><?php echo htmlspecialchars($user['email']); ?></span>
                </div>
                <div class="row">
                    <span class="label">Phone</span>
                    <span class="value"><?php echo htmlspecialchars($user['phone']); ?></span>
                </div>
                <div class="row">
                    <span class="label">Certifications</span>
                    <span class="value"><?php echo htmlspecialchars($user['certs']); ?></span>
                </div>
            </div>

            <div class="qr-column">
                <div class="qr-section">
                    <img src="<?php echo $qrBase64; ?>" alt="QR Code">
                </div>
                <div class="alien-text">
                    <?php echo htmlspecialchars($data['sga'] ?? '⊣⋮∷ᒲ'); ?>
                </div>
            </div>
        </div>

        <div class="footer">
            <div>
                <div style="color:var(--dim-color); margin-bottom:5px;">CLIENT_IP :: <span class="ip-add"><?php echo htmlspecialchars($data['ip']); ?></span></div>
                <div style="color:var(--dim-color);">ROOT@0x5ha157:~# <?php echo htmlspecialchars($data['timestamp']); ?></div>
            </div>
            
            <div style="text-align: right;">
                <div style="color:var(--dim-color); margin-bottom:5px; font-size:0.6rem;">SESSION PGP KEY (STATIC)</div>
                <div class="pgp-key"><?php echo htmlspecialchars($data['pgp']); ?></div>
            </div>
        </div>
    </div>

    <div class="controls">
        <a href="index.php?id=<?php echo $safeID; ?>" class="btn">Back</a>
        <button onclick="downloadCard()" class="btn download" id="dlBtn">Download Copy</button>
    </div>

    <script>
        function downloadCard() {
            const cardElement = document.getElementById('identity-card');
            const btn = document.getElementById('dlBtn');
            const originalText = btn.innerText;
            btn.innerText = "GENERATING...";
            
            // Wait a tiny bit to ensure rendering is stable
            setTimeout(() => {
                html2canvas(cardElement, {
                    backgroundColor: "#0d1117", 
                    scale: 3, // High resolution scale
                    useCORS: true,
                    // --- THE FIX FOR THICKNESS ---
                    // This runs right before the screenshot is taken.
                    // We modify the cloned element to be slimmer.
                    onclone: (clonedDoc) => {
                        const clonedCard = clonedDoc.getElementById('identity-card');
                        // Remove the heavy shadow
                        clonedCard.style.boxShadow = 'none';
                        // Change thick 2px border to thin 1px border
                        clonedCard.style.border = '1px solid #30363d'; 
                        // Reduce padding slightly for a tighter look
                        clonedCard.style.padding = '20px';
                        // Ensure corners are still smooth
                        clonedCard.style.borderRadius = '10px';
                    }
                    // -----------------------------
                }).then(canvas => {
                    const link = document.createElement('a');
                    link.download = 'ID_<?php echo $safeID; ?>.png';
                    link.href = canvas.toDataURL('image/png');
                    link.click();
                    btn.innerText = originalText;
                });
            }, 100);
        }
    </script>

<?php else: ?>

    <div class="error-container">
        <div class="error-title">[!] SYSTEM FAILURE</div>
        <div class="error-desc">
            > FATAL ERROR: IDENTITY_MISSING<br>
            > The requested ID "<?php echo htmlspecialchars($safeID); ?>" could not be retrieved from the archives.
        </div>
        <div style="font-size: 0.8rem; color: var(--dim-color); text-align: left; margin-bottom: 20px; font-family: monospace;">
            root@sys:~$ locate <?php echo htmlspecialchars($safeID); ?><br>
            ... searching /var/www/verify ... <span style="color:var(--error-color)">NOT FOUND</span><br>
            ... checking integrity ... <span style="color:var(--error-color)">FAILED</span>
        </div>
        <a href="index.php" class="btn" style="border-color: var(--error-color); color: var(--error-color);">RETURN TO TERMINAL</a>
    </div>

<?php endif; ?>

</body>
</html>
