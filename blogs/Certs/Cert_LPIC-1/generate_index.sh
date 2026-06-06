#!/bin/bash

generate_dir() {
    local TARGET_DIR="$1"
    
    (
        cd "$TARGET_DIR" || exit
        
        # Calculate the display path dynamically for the prompt
        local CURRENT_DIR_NAME=$(basename "$PWD")
        local RELATIVE_PATH=""
        
        if [[ "$PWD" == *"/blog"* ]]; then
            # Grabs everything from 'blog' onwards and prepends ~
            RELATIVE_PATH=$(pwd | sed -n 's/.*\/blog/~\/blog/p')
        else
            RELATIVE_PATH="~/$CURRENT_DIR_NAME"
        fi

        echo "Generating: $(pwd)/index.html"
        local OUTPUT="index.html"

        # Write the top half of the HTML
        cat << EOF > $OUTPUT
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>root@0x5ha157:$RELATIVE_PATH</title>
    <style>
        :root { --bg-color: #0d1117; --text-color: #c9d1d9; --accent-color: #58a6ff; --cmd-color: #7ee787; --dim-color: #8b949e; --dir-color: #79c0ff; --body-font: 'Consolas', 'Monaco', 'Andale Mono', monospace; }
        body { background-color: var(--bg-color); color: var(--text-color); font-family: var(--body-font); padding: 2rem; max-width: 900px; margin: 0 auto; }
        a { text-decoration: none; color: inherit; }
        a:hover { color: var(--accent-color); text-decoration: underline; }
        .prompt { color: var(--cmd-color); font-weight: bold; } .path { color: var(--accent-color); } .command { color: #fff; }
        
        /* FIXED TABLE STYLING */
        .file-list { width: 100%; border-collapse: collapse; margin-top: 1rem; font-size: 0.95rem; }
        .file-list th { text-align: left; border-bottom: 1px solid #30363d; padding-bottom: 10px; color: var(--dim-color); font-weight: normal; }
        
        /* Added strict padding to stop text from squishing together */
        .file-list td { padding: 10px 20px 10px 0; border-bottom: 1px solid #21262d; white-space: nowrap; }
        .file-list tr:last-child td { border-bottom: none; }
        
        /* Enforced column widths */
        .permissions { color: var(--dim-color); font-size: 0.85rem; width: 15%; } 
        .size { color: var(--dim-color); width: 10%; } 
        .date { color: var(--dim-color); width: 20%; }
        .filename { color: var(--text-color); font-weight: bold; width: 55%; white-space: normal; } 
        
        .dir { color: var(--dir-color); font-weight: bold; }
        @media (max-width: 600px) { .permissions, .size { display: none; } }
        .cursor { font-weight: bold; color: var(--cmd-color); animation: blink 1s step-end infinite; }
        @keyframes blink { 0%, 100% { opacity: 1; } 50% { opacity: 0; } }
    </style>
</head>
<body>
    <div style="margin-bottom: 2rem;">
        <span class="prompt">root@0x5ha157</span>:<span class="path">$RELATIVE_PATH</span>$ <span class="command">ls -lah</span>
    </div>
    <table class="file-list">
        <thead>
            <tr><th class="permissions">Permissions</th><th class="size">Size</th><th class="date">Date Modified</th><th class="name">Name</th></tr>
        </thead>
        <tbody>
            <tr><td class="permissions">drwxr-xr-x</td><td class="size">4.0K</td><td class="date">---</td><td class="filename dir"><a href="../">../ (cd ../)</a></td></tr>
EOF

        # Loop through files and directories to generate rows
        for item in *; do
            # Skip if directory is empty
            [ -e "$item" ] || continue
            
            # Clean up the output by ignoring background files
            if [ "$item" = "generate_index.sh" ] || [ "$item" = "index.html" ] || [ "$item" = "index.php" ] || [ "$item" = "index.html.old" ] || [ "$item" = "post_template.html" ]; then
                continue
            fi

            if [ -d "$item" ]; then
                SIZE="4.0K"
                PERMS="drwxr-xr-x"
                CLASS="filename dir"
                LINK="cd $item/"
                
                # Recursively generate index.html in subfolders
                generate_dir "$item"
            else
                SIZE=$(du -h "$item" | cut -f1 | tr -d ' ')
                PERMS="-rw-r--r--"
                CLASS="filename"
                LINK="$item"
            fi
            
            DATE=$(date -r "$item" "+%b %d %H:%M")

            echo "            <tr><td class=\"permissions\">$PERMS</td><td class=\"size\">$SIZE</td><td class=\"date\">$DATE</td><td class=\"$CLASS\"><a href=\"$item\">$LINK</a></td></tr>" >> $OUTPUT
        done

        # Write the bottom half of the HTML
        cat << EOF >> $OUTPUT
        </tbody>
    </table>
    <div style="margin-top: 2rem; color: var(--dim-color);">
        <span class="prompt">root@0x5ha157</span>:<span class="path">$RELATIVE_PATH</span>$ <span class="cursor">_</span>
    </div>
</body>
</html>
EOF
    )
}

# Start generation from the current directory
generate_dir "."

echo "----------------------------------------"
echo "Success! Clean index files generated."
