<?php
// FILE: products.php
// VULNERABLE TO: Union-Based Data Exfiltration

// 1. Database Configuration
$db_host = "localhost";
$db_user = "root";
$db_pass = ""; 
$db_name = "assignment_db";
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$output_rows = [];
$product_id = isset($_GET['id']) ? $_GET['id'] : 101;

// !!! VULNERABLE QUERY - Three Columns Selected !!!
$sql = "SELECT product_id, name, price FROM products WHERE product_id = '$product_id'";
$result = $conn->query($sql);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $output_rows[] = $row;
    }
} else {
    // Show error if query fails (useful for debugging Union syntax)
    $output_rows[] = ['product_id' => 'ERR', 'name' => 'Query Failed', 'price' => $conn->error];
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SecureCorp™ — Product Catalog</title>
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
            --text-primary: #c9d1d9;
            --text-dim: #6a7a8a;
            --border-color: rgba(0, 255, 65, 0.2);
        }
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-primary);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
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

        main { flex: 1; padding: 40px 20px; max-width: 900px; margin: 0 auto; width: 100%; }

        .page-header { margin-bottom: 32px; }
        .page-header h1 { font-family: 'Fira Code', monospace; font-size: 22px; color: var(--neon-green); margin-bottom: 8px; }
        .page-header p { font-size: 13px; color: var(--text-dim); }

        .search-bar {
            background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 10px;
            padding: 20px; margin-bottom: 30px; backdrop-filter: blur(20px); display: flex; gap: 12px; align-items: end;
        }
        .search-bar .field { flex: 1; }
        .search-bar label { display: block; font-family: 'Fira Code', monospace; font-size: 11px; color: var(--text-dim); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 6px; }
        .search-bar input {
            width: 100%; padding: 10px 14px; background: var(--bg-input); border: 1px solid var(--border-color);
            border-radius: 6px; color: var(--text-primary); font-family: 'Fira Code', monospace; font-size: 13px; outline: none; transition: all 0.3s;
        }
        .search-bar input:focus { border-color: var(--neon-green); box-shadow: 0 0 12px rgba(0,255,65,0.1); }
        .search-bar button {
            padding: 10px 20px; background: linear-gradient(135deg, rgba(0,255,65,0.2), rgba(0,229,255,0.15));
            border: 1px solid var(--neon-green); border-radius: 6px; color: var(--neon-green);
            font-family: 'Fira Code', monospace; font-size: 12px; cursor: pointer; transition: all 0.3s; white-space: nowrap;
        }
        .search-bar button:hover { background: rgba(0,255,65,0.3); box-shadow: 0 0 15px rgba(0,255,65,0.2); }

        .info-box {
            background: rgba(0, 229, 255, 0.05); border: 1px solid rgba(0, 229, 255, 0.2); border-radius: 8px;
            padding: 12px 16px; margin-bottom: 24px; font-family: 'Fira Code', monospace; font-size: 12px; color: var(--neon-cyan);
        }

        .results-card {
            background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 10px;
            overflow: hidden; backdrop-filter: blur(20px); box-shadow: 0 0 30px rgba(0,255,65,0.05);
        }
        .results-card h2 {
            font-family: 'Fira Code', monospace; font-size: 13px; color: var(--neon-green);
            padding: 16px 20px; border-bottom: 1px solid var(--border-color); letter-spacing: 1px; text-transform: uppercase;
        }

        table { width: 100%; border-collapse: collapse; }
        th {
            background: rgba(0, 255, 65, 0.08); font-family: 'Fira Code', monospace; font-size: 11px;
            color: var(--neon-green); text-transform: uppercase; letter-spacing: 1px; padding: 12px 20px; text-align: left;
        }
        td {
            padding: 14px 20px; border-top: 1px solid rgba(0,255,65,0.08);
            font-family: 'Fira Code', monospace; font-size: 13px; color: var(--text-primary);
        }
        tr:hover td { background: rgba(0, 255, 65, 0.03); }

        footer { text-align: center; padding: 20px; font-family: 'Fira Code', monospace; font-size: 11px; color: var(--text-dim); border-top: 1px solid var(--border-color); }
    </style>
</head>
<body>
    <nav>
        <div class="brand">⬡ SecureCorp</div>
        <a href="login.php">Login</a>
        <a href="products.php" class="active">Products</a>
        <a href="guestbook.php">Guestbook</a>
        <a href="search.php">Search</a>
        <a href="profile.php">Profile</a>
    </nav>

    <main>
        <div class="page-header">
            <h1>📦 Product Catalog</h1>
            <p>SecureCorp™ Internal Inventory System</p>
        </div>

        <form class="search-bar" method="GET" action="">
            <div class="field">
                <label for="id">Product ID</label>
                <input type="text" id="id" name="id" value="<?php echo htmlspecialchars($product_id); ?>" placeholder="Enter product ID...">
            </div>
            <button type="submit">⌕ Lookup</button>
        </form>

        <div class="info-box">
            <strong>$</strong> endpoint → <code>products.php?id=<?php echo htmlspecialchars($product_id); ?></code> &nbsp;|&nbsp; Query selects <strong>3 columns</strong>
        </div>

        <div class="results-card">
            <h2>▸ Query Results</h2>
            <table>
                <tr>
                    <th>Col 1 (ID)</th>
                    <th>Col 2 (Name)</th>
                    <th>Col 3 (Price)</th>
                </tr>
                <?php foreach ($output_rows as $row): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['product_id']); ?></td>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td><?php echo htmlspecialchars($row['price']); ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </main>

    <footer>SecureCorp™ Security Lab &mdash; Information Security Assignment 03</footer>
</body>
</html>
