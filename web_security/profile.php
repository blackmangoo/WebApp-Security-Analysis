<?php
// FILE: profile.php
// VULNERABLE TO: Cross-Site Request Forgery (CSRF)
// Profile update form has NO CSRF token — any external page can submit changes.
session_start();

$db_host = "localhost"; $db_user = "root"; $db_pass = ""; $db_name = "assignment_db";
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

// Simulate logged-in user (alice, user_id=1) — no real auth for lab simplicity
$current_user_id = 1;
$update_msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_email = $_POST['email'];
    $new_display = $_POST['display_name'];

    // !!! VULNERABLE — No CSRF token validation !!!
    $stmt = $conn->prepare("UPDATE profiles SET email = ?, display_name = ? WHERE user_id = ?");
    $stmt->bind_param("ssi", $new_email, $new_display, $current_user_id);
    
    if ($stmt->execute()) {
        $update_msg = "success";
    } else {
        $update_msg = "error";
    }
    $stmt->close();
}

// Fetch current profile
$stmt = $conn->prepare("SELECT username, email, display_name FROM profiles WHERE user_id = ?");
$stmt->bind_param("i", $current_user_id);
$stmt->execute();
$profile = $stmt->get_result()->fetch_assoc();
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SecureCorp™ — Profile Settings</title>
    <link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        *,*::before,*::after{margin:0;padding:0;box-sizing:border-box}
        :root{--bg:#0a0a0f;--card:rgba(15,15,25,0.85);--inp:rgba(10,10,20,0.9);--grn:#00ff41;--cyn:#00e5ff;--red:#ff1744;--prp:#d500f9;--txt:#c9d1d9;--dim:#6a7a8a;--brd:rgba(0,255,65,0.2)}
        body{font-family:'Inter',sans-serif;background:var(--bg);color:var(--txt);min-height:100vh;display:flex;flex-direction:column}
        body::after{content:'';position:fixed;inset:0;background:repeating-linear-gradient(0deg,transparent,transparent 2px,rgba(0,255,65,.015) 2px,rgba(0,255,65,.015) 4px);pointer-events:none;z-index:9999}
        body::before{content:'';position:fixed;inset:0;background-image:linear-gradient(rgba(0,255,65,.03) 1px,transparent 1px),linear-gradient(90deg,rgba(0,255,65,.03) 1px,transparent 1px);background-size:50px 50px;pointer-events:none;z-index:-1}
        nav{background:rgba(10,10,18,.95);border-bottom:1px solid var(--brd);padding:0 30px;display:flex;align-items:center;height:56px;backdrop-filter:blur(10px)}
        nav .brand{font-family:'Fira Code',monospace;font-size:14px;color:var(--grn);font-weight:600;letter-spacing:2px;text-transform:uppercase;margin-right:40px}
        nav a{font-family:'Fira Code',monospace;font-size:12px;color:var(--dim);text-decoration:none;padding:18px 16px;transition:.2s;border-bottom:2px solid transparent}
        nav a:hover,nav a.active{color:var(--grn);border-bottom-color:var(--grn)}
        main{flex:1;padding:40px 20px;max-width:600px;margin:0 auto;width:100%}
        .hdr{margin-bottom:32px} .hdr h1{font-family:'Fira Code',monospace;font-size:22px;color:var(--grn);margin-bottom:8px} .hdr p{font-size:13px;color:var(--dim)}
        .warn{background:rgba(255,23,68,.05);border:1px solid rgba(255,23,68,.2);border-radius:8px;padding:12px 16px;margin-bottom:24px;font-family:'Fira Code',monospace;font-size:12px;color:var(--red)}
        .card{background:var(--card);border:1px solid var(--brd);border-radius:10px;padding:28px;backdrop-filter:blur(20px);box-shadow:0 0 30px rgba(0,255,65,.05)}
        .card h2{font-family:'Fira Code',monospace;font-size:14px;color:var(--cyn);margin-bottom:20px;letter-spacing:1px}
        .badge{display:inline-block;background:rgba(0,255,65,.1);border:1px solid rgba(0,255,65,.3);border-radius:4px;padding:4px 10px;font-family:'Fira Code',monospace;font-size:11px;color:var(--grn);margin-bottom:20px}
        .fg{margin-bottom:18px}
        .fg label{display:block;font-family:'Fira Code',monospace;font-size:11px;color:var(--dim);text-transform:uppercase;letter-spacing:1.5px;margin-bottom:6px}
        .fg input{width:100%;padding:11px 14px;background:var(--inp);border:1px solid var(--brd);border-radius:6px;color:var(--txt);font-family:'Fira Code',monospace;font-size:13px;outline:none;transition:.3s}
        .fg input:focus{border-color:var(--grn);box-shadow:0 0 12px rgba(0,255,65,.1)}
        .fg input[readonly]{opacity:.5;cursor:not-allowed}
        .btn{width:100%;padding:13px;background:linear-gradient(135deg,rgba(0,255,65,.2),rgba(0,229,255,.15));border:1px solid var(--grn);border-radius:8px;color:var(--grn);font-family:'Fira Code',monospace;font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:2px;cursor:pointer;transition:.3s;margin-top:8px}
        .btn:hover{background:rgba(0,255,65,.3);box-shadow:0 0 20px rgba(0,255,65,.2);transform:translateY(-1px)}
        .msg{padding:12px 16px;margin-bottom:20px;border-radius:8px;font-family:'Fira Code',monospace;font-size:12px}
        .msg-ok{background:rgba(0,255,65,.08);border:1px solid rgba(0,255,65,.3);color:var(--grn)}
        .msg-err{background:rgba(255,23,68,.08);border:1px solid rgba(255,23,68,.3);color:var(--red)}
        footer{text-align:center;padding:20px;font-family:'Fira Code',monospace;font-size:11px;color:var(--dim);border-top:1px solid var(--brd)}
    </style>
</head>
<body>
    <nav>
        <div class="brand">⬡ SecureCorp</div>
        <a href="login.php">Login</a>
        <a href="products.php">Products</a>
        <a href="guestbook.php">Guestbook</a>
        <a href="search.php">Search</a>
        <a href="profile.php" class="active">Profile</a>
    </nav>
    <main>
        <div class="hdr">
            <h1>👤 Profile Settings</h1>
            <p>Manage your SecureCorp™ account details</p>
        </div>
        <div class="warn">
            <strong>⚠ Notice:</strong> This form has no CSRF protection. Simulated session: logged in as <strong>alice</strong> (user_id=1).
        </div>
        <?php if ($update_msg === "success"): ?>
            <div class="msg msg-ok">✓ Profile updated successfully.</div>
        <?php elseif ($update_msg === "error"): ?>
            <div class="msg msg-err">✗ Failed to update profile.</div>
        <?php endif; ?>
        <div class="card">
            <h2>▸ Account Information</h2>
            <div class="badge">● ONLINE — <?php echo htmlspecialchars($profile['username']); ?></div>
            <form method="POST" action="">
                <div class="fg">
                    <label for="username">Username (read-only)</label>
                    <input type="text" id="username" value="<?php echo htmlspecialchars($profile['username']); ?>" readonly>
                </div>
                <div class="fg">
                    <label for="email">Email Address</label>
                    <input type="text" id="email" name="email" value="<?php echo htmlspecialchars($profile['email']); ?>">
                </div>
                <div class="fg">
                    <label for="display_name">Display Name</label>
                    <input type="text" id="display_name" name="display_name" value="<?php echo htmlspecialchars($profile['display_name']); ?>">
                </div>
                <button type="submit" class="btn">▶ Update Profile</button>
            </form>
        </div>
    </main>
    <footer>SecureCorp™ Security Lab &mdash; Information Security Assignment 03</footer>
</body>
</html>
