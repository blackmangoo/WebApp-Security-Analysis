<?php
// PHP Script for a Vulnerable Login Page
// VULNERABLE TO: In-Band SQLi, Error-Based SQLi, Stacked Queries

// 1. Database Configuration (Adjust if not using XAMPP defaults)
$db_host = "localhost";
$db_user = "root";
$db_pass = ""; // Default XAMPP password is empty
$db_name = "assignment_db";

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // !!! VULNERABLE LINE !!!
    // User input is directly concatenated into the SQL query string
    $sql = "SELECT username, role, secret_flag FROM users WHERE username = '$username' AND password_hash = MD5('$password')";
    
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        // Displaying the secret_flag confirms a successful, privileged login
        $message = "Login Successful! Welcome, " . htmlspecialchars($row['username']) . " (" . $row['role'] . ")<br>Your Secret Flag: " . htmlspecialchars($row['secret_flag']);
    } else {
        // Default error message for failed login
        $message = "Login Failed. Check username and password.";

        // !!! IMPORTANT FOR TASK 3.A !!!
        // Uncomment the line below to enable Error-Based SQLi for Task 3.A
        // $message = "Login Failed. MySQL Error: " . $conn->error; 
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SecureCorp™ — Authentication Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        :root {
            --bg-primary: #0a0a0f;
            --bg-card: rgba(15, 15, 25, 0.85);
            --bg-input: rgba(10, 10, 20, 0.9);
            --neon-green: #00ff41;
            --neon-green-dim: rgba(0, 255, 65, 0.15);
            --neon-cyan: #00e5ff;
            --neon-red: #ff1744;
            --text-primary: #c9d1d9;
            --text-dim: #6a7a8a;
            --border-color: rgba(0, 255, 65, 0.2);
            --border-hover: rgba(0, 255, 65, 0.5);
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-primary);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
        }

        /* Scanline overlay */
        body::after {
            content: '';
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: repeating-linear-gradient(
                0deg,
                transparent,
                transparent 2px,
                rgba(0, 255, 65, 0.015) 2px,
                rgba(0, 255, 65, 0.015) 4px
            );
            pointer-events: none;
            z-index: 9999;
        }

        /* Grid background */
        body::before {
            content: '';
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background-image:
                linear-gradient(rgba(0, 255, 65, 0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(0, 255, 65, 0.03) 1px, transparent 1px);
            background-size: 50px 50px;
            pointer-events: none;
            z-index: -1;
        }

        /* ─── Navigation ─── */
        nav {
            background: rgba(10, 10, 18, 0.95);
            border-bottom: 1px solid var(--border-color);
            padding: 0 30px;
            display: flex;
            align-items: center;
            height: 56px;
            backdrop-filter: blur(10px);
        }
        nav .brand {
            font-family: 'Fira Code', monospace;
            font-size: 14px;
            color: var(--neon-green);
            font-weight: 600;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-right: 40px;
        }
        nav a {
            font-family: 'Fira Code', monospace;
            font-size: 12px;
            color: var(--text-dim);
            text-decoration: none;
            padding: 18px 16px;
            transition: all 0.2s;
            border-bottom: 2px solid transparent;
        }
        nav a:hover { color: var(--neon-green); border-bottom-color: var(--neon-green); }
        nav a.active { color: var(--neon-green); border-bottom-color: var(--neon-green); }

        /* ─── Main ─── */
        main {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }

        .login-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 40px;
            width: 100%;
            max-width: 440px;
            backdrop-filter: blur(20px);
            box-shadow:
                0 0 30px rgba(0, 255, 65, 0.05),
                0 20px 60px rgba(0, 0, 0, 0.5);
            position: relative;
        }
        .login-card::before {
            content: '';
            position: absolute;
            top: -1px; left: -1px; right: -1px; bottom: -1px;
            border-radius: 13px;
            background: linear-gradient(135deg, rgba(0, 255, 65, 0.3), transparent, rgba(0, 229, 255, 0.2));
            z-index: -1;
            opacity: 0;
            transition: opacity 0.4s;
        }
        .login-card:hover::before { opacity: 1; }

        .card-header {
            text-align: center;
            margin-bottom: 32px;
        }
        .card-header .icon {
            font-size: 36px;
            margin-bottom: 12px;
            display: block;
        }
        .card-header h1 {
            font-family: 'Fira Code', monospace;
            font-size: 20px;
            font-weight: 600;
            color: var(--neon-green);
            letter-spacing: 1px;
        }
        .card-header p {
            font-size: 13px;
            color: var(--text-dim);
            margin-top: 6px;
        }

        .credentials-hint {
            background: rgba(0, 229, 255, 0.05);
            border: 1px solid rgba(0, 229, 255, 0.2);
            border-radius: 8px;
            padding: 10px 14px;
            margin-bottom: 24px;
            font-family: 'Fira Code', monospace;
            font-size: 12px;
            color: var(--neon-cyan);
        }
        .credentials-hint span { opacity: 0.6; }

        /* ─── Form ─── */
        .form-group { margin-bottom: 20px; }
        .form-group label {
            display: block;
            font-family: 'Fira Code', monospace;
            font-size: 11px;
            color: var(--text-dim);
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-bottom: 8px;
        }
        .form-group input {
            width: 100%;
            padding: 12px 16px;
            background: var(--bg-input);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            color: var(--text-primary);
            font-family: 'Fira Code', monospace;
            font-size: 14px;
            transition: all 0.3s;
            outline: none;
        }
        .form-group input:focus {
            border-color: var(--neon-green);
            box-shadow: 0 0 15px rgba(0, 255, 65, 0.15);
        }
        .form-group input::placeholder { color: var(--text-dim); opacity: 0.5; }

        .submit-btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, rgba(0, 255, 65, 0.2), rgba(0, 229, 255, 0.15));
            border: 1px solid var(--neon-green);
            border-radius: 8px;
            color: var(--neon-green);
            font-family: 'Fira Code', monospace;
            font-size: 13px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 2px;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 8px;
        }
        .submit-btn:hover {
            background: linear-gradient(135deg, rgba(0, 255, 65, 0.35), rgba(0, 229, 255, 0.25));
            box-shadow: 0 0 25px rgba(0, 255, 65, 0.2);
            transform: translateY(-1px);
        }

        /* ─── Messages ─── */
        .message {
            padding: 14px 18px;
            margin-top: 20px;
            border-radius: 8px;
            font-family: 'Fira Code', monospace;
            font-size: 12px;
            line-height: 1.6;
        }
        .success {
            background: rgba(0, 255, 65, 0.08);
            border: 1px solid rgba(0, 255, 65, 0.3);
            color: var(--neon-green);
        }
        .failure {
            background: rgba(255, 23, 68, 0.08);
            border: 1px solid rgba(255, 23, 68, 0.3);
            color: var(--neon-red);
        }

        /* ─── Footer ─── */
        footer {
            text-align: center;
            padding: 20px;
            font-family: 'Fira Code', monospace;
            font-size: 11px;
            color: var(--text-dim);
            border-top: 1px solid var(--border-color);
        }
    </style>
</head>
<body>
    <nav>
        <div class="brand">⬡ SecureCorp</div>
        <a href="login.php" class="active">Login</a>
        <a href="products.php">Products</a>
        <a href="guestbook.php">Guestbook</a>
        <a href="search.php">Search</a>
        <a href="profile.php">Profile</a>
    </nav>

    <main>
        <div class="login-card">
            <div class="card-header">
                <span class="icon">🔐</span>
                <h1>Authentication Portal</h1>
                <p>SecureCorp™ Internal Access</p>
            </div>

            <div class="credentials-hint">
                <span>$</span> test_credentials → <strong>alice</strong> / <strong>password</strong>
            </div>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" placeholder="Enter username..." required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter password..." required>
                </div>
                <button type="submit" class="submit-btn">▶ Authenticate</button>
            </form>

            <?php if ($message): ?>
                <div class="message <?php echo strpos($message, 'Successful') !== false ? 'success' : 'failure'; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <footer>
        SecureCorp™ Security Lab &mdash; Information Security Assignment 03
    </footer>
</body>
</html>
