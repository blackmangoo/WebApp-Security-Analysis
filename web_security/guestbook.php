<?php
// FILE: guestbook.php
// VULNERABLE TO: Stored Cross-Site Scripting (XSS)
// User-submitted messages are stored in the database and rendered
// directly into the page WITHOUT sanitization.

// 1. Database Configuration
$db_host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "assignment_db";
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$post_status = "";

// Handle new guestbook entry
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $author = $_POST['author'];
    $message_text = $_POST['message'];

    // !!! VULNERABLE — Input stored directly without sanitization !!!
    $stmt = $conn->prepare("INSERT INTO guestbook (author, message) VALUES (?, ?)");
    $stmt->bind_param("ss", $author, $message_text);
    
    if ($stmt->execute()) {
        $post_status = "success";
    } else {
        $post_status = "error";
    }
    $stmt->close();
}

// Fetch all guestbook entries
$entries = [];
$result = $conn->query("SELECT author, message, created_at FROM guestbook ORDER BY created_at DESC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $entries[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SecureCorp™ — Guestbook</title>
    <link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
        :root {
            --bg-primary: #0a0a0f;
            --bg-card: rgba(15, 15, 25, 0.85);
            --bg-input: rgba(10, 10, 20, 0.9);
            --neon-green: #00ff41;
            --neon-cyan: #00e5ff;
            --neon-red: #ff1744;
            --neon-purple: #d500f9;
            --text-primary: #c9d1d9;
            --text-dim: #6a7a8a;
            --border-color: rgba(0, 255, 65, 0.2);
        }
        body {
            font-family: 'Inter', sans-serif; background-color: var(--bg-primary);
            color: var(--text-primary); min-height: 100vh; display: flex; flex-direction: column;
        }
        body::after {
            content: ''; position: fixed; top: 0; left: 0; right: 0; bottom: 0;
            background: repeating-linear-gradient(0deg, transparent, transparent 2px, rgba(0,255,65,0.015) 2px, rgba(0,255,65,0.015) 4px);
            pointer-events: none; z-index: 9999;
        }
        body::before {
            content: ''; position: fixed; top: 0; left: 0; right: 0; bottom: 0;
            background-image: linear-gradient(rgba(0,255,65,0.03) 1px, transparent 1px), linear-gradient(90deg, rgba(0,255,65,0.03) 1px, transparent 1px);
            background-size: 50px 50px; pointer-events: none; z-index: -1;
        }
        nav {
            background: rgba(10, 10, 18, 0.95); border-bottom: 1px solid var(--border-color);
            padding: 0 30px; display: flex; align-items: center; height: 56px; backdrop-filter: blur(10px);
        }
        nav .brand { font-family: 'Fira Code', monospace; font-size: 14px; color: var(--neon-green); font-weight: 600; letter-spacing: 2px; text-transform: uppercase; margin-right: 40px; }
        nav a { font-family: 'Fira Code', monospace; font-size: 12px; color: var(--text-dim); text-decoration: none; padding: 18px 16px; transition: all 0.2s; border-bottom: 2px solid transparent; }
        nav a:hover { color: var(--neon-green); border-bottom-color: var(--neon-green); }
        nav a.active { color: var(--neon-green); border-bottom-color: var(--neon-green); }

        main { flex: 1; padding: 40px 20px; max-width: 800px; margin: 0 auto; width: 100%; }

        .page-header { margin-bottom: 32px; }
        .page-header h1 { font-family: 'Fira Code', monospace; font-size: 22px; color: var(--neon-green); margin-bottom: 8px; }
        .page-header p { font-size: 13px; color: var(--text-dim); }

        /* ─── Post Form ─── */
        .post-form {
            background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 10px;
            padding: 24px; margin-bottom: 30px; backdrop-filter: blur(20px);
        }
        .post-form h2 {
            font-family: 'Fira Code', monospace; font-size: 14px; color: var(--neon-cyan);
            margin-bottom: 16px; letter-spacing: 1px;
        }
        .form-row { display: flex; gap: 12px; margin-bottom: 12px; }
        .form-row .field { flex: 1; }
        .form-row label, .post-form > label {
            display: block; font-family: 'Fira Code', monospace; font-size: 11px;
            color: var(--text-dim); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 6px;
        }
        .post-form input, .post-form textarea {
            width: 100%; padding: 10px 14px; background: var(--bg-input); border: 1px solid var(--border-color);
            border-radius: 6px; color: var(--text-primary); font-family: 'Fira Code', monospace; font-size: 13px;
            outline: none; transition: all 0.3s;
        }
        .post-form textarea { resize: vertical; min-height: 80px; margin-bottom: 12px; }
        .post-form input:focus, .post-form textarea:focus { border-color: var(--neon-green); box-shadow: 0 0 12px rgba(0,255,65,0.1); }
        .post-form button {
            padding: 10px 24px; background: linear-gradient(135deg, rgba(0,255,65,0.2), rgba(0,229,255,0.15));
            border: 1px solid var(--neon-green); border-radius: 6px; color: var(--neon-green);
            font-family: 'Fira Code', monospace; font-size: 12px; cursor: pointer; transition: all 0.3s;
        }
        .post-form button:hover { background: rgba(0,255,65,0.3); box-shadow: 0 0 15px rgba(0,255,65,0.2); }

        .status-msg { padding: 10px 14px; margin-bottom: 20px; border-radius: 6px; font-family: 'Fira Code', monospace; font-size: 12px; }
        .status-success { background: rgba(0,255,65,0.08); border: 1px solid rgba(0,255,65,0.3); color: var(--neon-green); }
        .status-error { background: rgba(255,23,68,0.08); border: 1px solid rgba(255,23,68,0.3); color: var(--neon-red); }

        /* ─── Entries ─── */
        .entries-header {
            font-family: 'Fira Code', monospace; font-size: 13px; color: var(--neon-green);
            margin-bottom: 16px; letter-spacing: 1px; text-transform: uppercase;
        }
        .entry {
            background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 10px;
            padding: 18px 20px; margin-bottom: 14px; backdrop-filter: blur(20px); transition: all 0.3s;
        }
        .entry:hover { border-color: rgba(0,255,65,0.4); box-shadow: 0 0 20px rgba(0,255,65,0.05); }
        .entry-meta {
            display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;
        }
        .entry-author {
            font-family: 'Fira Code', monospace; font-size: 13px; font-weight: 600;
            color: var(--neon-cyan);
        }
        .entry-date { font-family: 'Fira Code', monospace; font-size: 11px; color: var(--text-dim); }
        .entry-message { font-size: 14px; line-height: 1.7; color: var(--text-primary); }

        footer { text-align: center; padding: 20px; font-family: 'Fira Code', monospace; font-size: 11px; color: var(--text-dim); border-top: 1px solid var(--border-color); }
    </style>
</head>
<body>
    <nav>
        <div class="brand">⬡ SecureCorp</div>
        <a href="login.php">Login</a>
        <a href="products.php">Products</a>
        <a href="guestbook.php" class="active">Guestbook</a>
        <a href="search.php">Search</a>
        <a href="profile.php">Profile</a>
    </nav>

    <main>
        <div class="page-header">
            <h1>📝 SecureCorp Guestbook</h1>
            <p>Leave a message for the team — all entries are publicly visible</p>
        </div>

        <?php if ($post_status === "success"): ?>
            <div class="status-msg status-success">✓ Your message has been posted successfully.</div>
        <?php elseif ($post_status === "error"): ?>
            <div class="status-msg status-error">✗ Failed to post message. Please try again.</div>
        <?php endif; ?>

        <div class="post-form">
            <h2>▸ New Entry</h2>
            <form method="POST" action="">
                <div class="form-row">
                    <div class="field">
                        <label for="author">Your Name</label>
                        <input type="text" id="author" name="author" placeholder="Enter your name..." required>
                    </div>
                </div>
                <label for="message">Message</label>
                <textarea id="message" name="message" placeholder="Write your message..." required></textarea>
                <button type="submit">▶ Post Message</button>
            </form>
        </div>

        <div class="entries-header">▸ All Entries (<?php echo count($entries); ?>)</div>

        <?php foreach ($entries as $entry): ?>
        <div class="entry">
            <div class="entry-meta">
                <!-- !!! VULNERABLE — Author rendered without htmlspecialchars() !!! -->
                <span class="entry-author">⟐ <?php echo $entry['author']; ?></span>
                <span class="entry-date"><?php echo htmlspecialchars($entry['created_at']); ?></span>
            </div>
            <!-- !!! VULNERABLE — Message rendered without htmlspecialchars() !!! -->
            <div class="entry-message"><?php echo $entry['message']; ?></div>
        </div>
        <?php endforeach; ?>
    </main>

    <footer>SecureCorp™ Security Lab &mdash; Information Security Assignment 03</footer>
</body>
</html>
