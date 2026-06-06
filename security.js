/* FILENAME: security.js
   AUTHOR: 0x5ha157
   DESC: Enhanced Security, Anti-Save, Right-Click Block & IP Logging
*/

(function() {
    console.log("🔒 Security Protocols Initiated...");
    let userIP = "Scanning...";

    // --- 0. IP FETCH & LOGGING SYSTEM ---
    async function logActivity(activityType) {
        // 1. Get IP if we haven't already
        if (userIP === "Scanning...") {
            try {
                // Try fetching public IP
                const response = await fetch('https://api.ipify.org?format=json');
                const data = await response.json();
                userIP = data.ip;
            } catch (e) {
                userIP = "Unknown";
            }
        }

        // 2. Send Data to PHP Backend
        // Ensure logger.php is in the same directory!
        fetch('logger.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ 
                ip: userIP,
                event: activityType 
            })
        });
    }

    // Log the visit immediately on load
    logActivity("New Session Started");


    // --- 1. VISUAL: CROSSHAIR CURSOR ---
    window.addEventListener('DOMContentLoaded', () => {
        const style = document.createElement('style');
        style.innerHTML = `
            * { cursor: crosshair !important; -webkit-user-select: none; user-select: none; }
            input, textarea { -webkit-user-select: text; user-select: text; }
        `;
        document.head.appendChild(style);
    });

    // --- 2. INPUT BLOCKING & TRAPS ---

    // Disable Right-Click & SHOW WARNING
    document.addEventListener('contextmenu', event => {
        event.preventDefault();
        // Log the attempt
        logActivity("Right-Click Attempt");
        // Show the warning
        alert("⚠️ ACCESS DENIED: System Protected.\nRight-click disabled by administrator.");
    });

    // Keydown Traps
    document.onkeydown = function(e) {
        // Block F12, Ctrl+Shift+I/J/C, Ctrl+U
        if(e.keyCode == 123) return false;
        if(e.ctrlKey && e.shiftKey && (e.keyCode == 'I'.charCodeAt(0) || e.keyCode == 'J'.charCodeAt(0) || e.keyCode == 'C'.charCodeAt(0))) return false;
        if(e.ctrlKey && e.keyCode == 'U'.charCodeAt(0)) return false;

        // --- THE TRAP: Ctrl+S (Save) ---
        if(e.ctrlKey && e.keyCode == 'S'.charCodeAt(0)) {
            e.preventDefault(); // Stop the save
            
            // Log the "Crime"
            logActivity("ILLEGAL DOWNLOAD ATTEMPT (Ctrl+S)");

            // Scare the User
            alert(`⚠️ SECURITY ALERT ⚠️\n\nDownload attempt detected.\nYour IP (${userIP}) has been logged and reported to the administrator.\n\nConnection Terminated.`);
            
            return false;
        }
    };

    // --- 3. COPY TRAP ---
    document.addEventListener('copy', function(e) {
        e.preventDefault();
        logActivity("Text Copy Attempt");
        alert("⚠️ ACTION BLOCKED: Content is protected.");
    });

})();
