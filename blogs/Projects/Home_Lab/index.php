<?php
// Get current directory info for the prompt
$current_dir_name = basename(getcwd());
$path = getcwd();
if (strpos($path, '/blog') !== false) {
    $relative_path = '~' . substr($path, strpos($path, '/blog'));
} else {
    $relative_path = '~/' . $current_dir_name;
}

// Function to format file size cleanly
function formatSize($bytes) {
    if ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 1) . 'M';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 1) . 'K';
    } else {
        return $bytes . 'B';
    }
}

// Get all files in the current directory
$files = scandir('.');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>root@0x5ha157:<?php echo $relative_path; ?></title>
    <style>
        :root { 
            --bg-color: #0d1117; 
            --text-color: #c9d1d9; 
            --accent-color: #58a6ff; 
            --cmd-color: #7ee787; 
            --dim-color: #8b949e; 
            --dir-color: #79c0ff; 
            --body-font: 'Consolas', 'Monaco', 'Andale Mono', monospace; 
        }
        
        body { 
            background-color: var(--bg-color); 
            color: var(--text-color); 
            font-family: var(--body-font); 
            padding: 2rem; 
            max-width: 950px; 
            margin: 0 auto; 
            line-height: 1.6;
        }
        
        a { text-decoration: none; color: inherit; display: inline-block; width: 100%; }
        a:hover { color: var(--accent-color); text-decoration: underline; }
        
        /* Terminal Prompt Styling */
        .prompt { color: var(--cmd-color); font-weight: bold; } 
        .path { color: var(--dir-color); font-weight: bold; } 
        .command { color: #fff; }
        
        /* Table Styling */
        .file-list { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 2rem; 
            margin-bottom: 2rem;
            font-size: 0.95rem; 
        }
        
        .file-list th { 
            text-align: left; 
            border-bottom: 1px solid #30363d; 
            padding-bottom: 0.5rem; 
            color: var(--dim-color); 
            font-weight: normal; 
        }
        
        .file-list td { padding: 0.4rem 0; }
        
        /* TIGHT COLUMN SPACING FIX */
        /* width: 1% forces the column to shrink to the content size */
        .permissions { color: var(--dim-color); padding-right: 1.2rem; width: 1%; white-space: nowrap; } 
        .size { color: var(--dim-color); padding-right: 1.2rem; width: 1%; white-space: nowrap; } 
        .date { color: var(--dim-color); padding-right: 1.5rem; width: 1%; white-space: nowrap; }
        
        /* The filename takes up the remaining space */
        .filename { color: var(--text-color); font-weight: bold; } 
        .dir { color: var(--dir-color); font-weight: bold; }
        
        @media (max-width: 600px) { .permissions, .size, .date { display: none; } }
        
        .cursor { font-weight: bold; color: var(--cmd-color); animation: blink 1s step-end infinite; }
        @keyframes blink { 0%, 100% { opacity: 1; } 50% { opacity: 0; } }
    </style>
</head>
<body>

    <div style="margin-bottom: 1.5rem;">
        <span class="prompt">root@0x5ha157</span>:<span class="path">~</span>$ <span class="command">cd <?php echo $current_dir_name; ?>/</span><br>
        <span class="prompt">root@0x5ha157</span>:<span class="path"><?php echo $relative_path; ?></span>$ <span class="command">ls -lah</span>
    </div>
    
    <table class="file-list">
        <thead>
            <tr>
                <th class="permissions">Permissions</th>
                <th class="size">Size</th>
                <th class="date">Date Modified</th>
                <th class="name">Name</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="permissions">drwxr-xr-x</td>
                <td class="size">4.0K</td>
                <td class="date">---</td>
                <td class="filename dir"><a href="../">../ (Return)</a></td>
            </tr>

            <?php
            foreach ($files as $file) {
                if ($file === '.' || $file === '..') continue;
                if ($file === 'index.php' || $file === 'index.html' || $file === 'generate_index.sh' || $file === 'post_template.html' || $file === 'index.html.old') continue;

                $is_dir = is_dir($file);
                
                $size = $is_dir ? '4.0K' : formatSize(filesize($file));
                $date = date("M d H:i", filemtime($file));
                $perms = $is_dir ? 'drwxr-xr-x' : '-rw-r--r--';
                $class = $is_dir ? 'filename dir' : 'filename';
                $link_prefix = $is_dir ? 'cd ' : '';
                $link_suffix = $is_dir ? '/' : '';
                
                $href = rawurlencode($file);
                if ($is_dir) {
                    $href .= '/';
                }

                echo "<tr>";
                echo "<td class=\"permissions\">{$perms}</td>";
                echo "<td class=\"size\">{$size}</td>";
                echo "<td class=\"date\">{$date}</td>";
                echo "<td class=\"{$class}\"><a href=\"{$href}\">{$link_prefix}{$file}{$link_suffix}</a></td>";
                echo "</tr>\n";
            }
            ?>

        </tbody>
    </table>
    
    <div>
        <span class="prompt">root@0x5ha157</span>:<span class="path"><?php echo $relative_path; ?></span>$ <span class="cursor">_</span>
    </div>

</body>
</html>
