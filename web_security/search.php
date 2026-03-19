<?php
// FILE: search.php
// VULNERABLE TO: Reflected Cross-Site Scripting (XSS)
$db_host = "localhost"; $db_user = "root"; $db_pass = ""; $db_name = "assignment_db";
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

$search_query = isset($_GET['q']) ? $_GET['q'] : '';
$results = [];
if ($search_query !== '') {
    $sql = "SELECT product_id, name, price FROM products WHERE name LIKE '%$search_query%'";
    $result = $conn->query($sql);
    if ($result) { while ($row = $result->fetch_assoc()) { $results[] = $row; } }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SecureCorp™ — Product Search</title>
    <link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        *,*::before,*::after{margin:0;padding:0;box-sizing:border-box}
        :root{--bg:#0a0a0f;--card:rgba(15,15,25,0.85);--inp:rgba(10,10,20,0.9);--grn:#00ff41;--cyn:#00e5ff;--red:#ff1744;--ylw:#ffea00;--txt:#c9d1d9;--dim:#6a7a8a;--brd:rgba(0,255,65,0.2)}
        body{font-family:'Inter',sans-serif;background:var(--bg);color:var(--txt);min-height:100vh;display:flex;flex-direction:column}
        body::after{content:'';position:fixed;inset:0;background:repeating-linear-gradient(0deg,transparent,transparent 2px,rgba(0,255,65,.015) 2px,rgba(0,255,65,.015) 4px);pointer-events:none;z-index:9999}
        body::before{content:'';position:fixed;inset:0;background-image:linear-gradient(rgba(0,255,65,.03) 1px,transparent 1px),linear-gradient(90deg,rgba(0,255,65,.03) 1px,transparent 1px);background-size:50px 50px;pointer-events:none;z-index:-1}
        nav{background:rgba(10,10,18,.95);border-bottom:1px solid var(--brd);padding:0 30px;display:flex;align-items:center;height:56px;backdrop-filter:blur(10px)}
        nav .brand{font-family:'Fira Code',monospace;font-size:14px;color:var(--grn);font-weight:600;letter-spacing:2px;text-transform:uppercase;margin-right:40px}
        nav a{font-family:'Fira Code',monospace;font-size:12px;color:var(--dim);text-decoration:none;padding:18px 16px;transition:.2s;border-bottom:2px solid transparent}
        nav a:hover,nav a.active{color:var(--grn);border-bottom-color:var(--grn)}
        main{flex:1;padding:40px 20px;max-width:800px;margin:0 auto;width:100%}
        .hdr{margin-bottom:32px} .hdr h1{font-family:'Fira Code',monospace;font-size:22px;color:var(--grn);margin-bottom:8px} .hdr p{font-size:13px;color:var(--dim)}
        .sf{background:var(--card);border:1px solid var(--brd);border-radius:10px;padding:24px;margin-bottom:30px;backdrop-filter:blur(20px);display:flex;gap:12px;align-items:end}
        .sf .f{flex:1} .sf label{display:block;font-family:'Fira Code',monospace;font-size:11px;color:var(--dim);text-transform:uppercase;letter-spacing:1px;margin-bottom:6px}
        .sf input{width:100%;padding:12px 16px;background:var(--inp);border:1px solid var(--brd);border-radius:6px;color:var(--txt);font-family:'Fira Code',monospace;font-size:14px;outline:none;transition:.3s}
        .sf input:focus{border-color:var(--grn);box-shadow:0 0 12px rgba(0,255,65,.1)}
        .sf button{padding:12px 24px;background:linear-gradient(135deg,rgba(0,255,65,.2),rgba(0,229,255,.15));border:1px solid var(--grn);border-radius:6px;color:var(--grn);font-family:'Fira Code',monospace;font-size:12px;cursor:pointer;transition:.3s;white-space:nowrap}
        .sf button:hover{background:rgba(0,255,65,.3);box-shadow:0 0 15px rgba(0,255,65,.2)}
        .ibox{background:rgba(255,234,0,.05);border:1px solid rgba(255,234,0,.2);border-radius:8px;padding:12px 16px;margin-bottom:24px;font-family:'Fira Code',monospace;font-size:12px;color:var(--ylw)}
        .echo{background:var(--card);border:1px solid var(--brd);border-radius:10px;padding:20px;margin-bottom:24px}
        .echo .lbl{font-family:'Fira Code',monospace;font-size:11px;color:var(--dim);text-transform:uppercase;letter-spacing:1px;margin-bottom:8px}
        .echo .qd{font-family:'Fira Code',monospace;font-size:16px;color:var(--cyn);padding:12px;background:var(--inp);border-radius:6px;border:1px solid rgba(0,229,255,.15)}
        .rc{background:var(--card);border:1px solid var(--brd);border-radius:10px;overflow:hidden;backdrop-filter:blur(20px)}
        .rc h2{font-family:'Fira Code',monospace;font-size:13px;color:var(--grn);padding:16px 20px;border-bottom:1px solid var(--brd);letter-spacing:1px;text-transform:uppercase}
        table{width:100%;border-collapse:collapse}
        th{background:rgba(0,255,65,.08);font-family:'Fira Code',monospace;font-size:11px;color:var(--grn);text-transform:uppercase;letter-spacing:1px;padding:12px 20px;text-align:left}
        td{padding:14px 20px;border-top:1px solid rgba(0,255,65,.08);font-family:'Fira Code',monospace;font-size:13px}
        tr:hover td{background:rgba(0,255,65,.03)}
        .nr{padding:30px;text-align:center;font-family:'Fira Code',monospace;font-size:13px;color:var(--dim)}
        footer{text-align:center;padding:20px;font-family:'Fira Code',monospace;font-size:11px;color:var(--dim);border-top:1px solid var(--brd)}
    </style>
</head>
<body>
    <nav>
        <div class="brand">⬡ SecureCorp</div>
        <a href="login.php">Login</a>
        <a href="products.php">Products</a>
        <a href="guestbook.php">Guestbook</a>
        <a href="search.php" class="active">Search</a>
        <a href="profile.php">Profile</a>
    </nav>
    <main>
        <div class="hdr">
            <h1>🔍 Product Search</h1>
            <p>Search the SecureCorp™ product catalog</p>
        </div>
        <form class="sf" method="GET" action="">
            <div class="f">
                <label for="q">Search Query</label>
                <input type="text" id="q" name="q" value="<?php echo isset($_GET['q']) ? $_GET['q'] : ''; ?>" placeholder="Search products...">
            </div>
            <button type="submit">⌕ Search</button>
        </form>
        <div class="ibox">
            <strong>⚠</strong> Vulnerable parameter: <code>?q=[input]</code> | Example: <code>search.php?q=Firewall</code>
        </div>
        <?php if ($search_query !== ''): ?>
        <div class="echo">
            <div class="lbl">Showing results for:</div>
            <!-- !!! VULNERABLE — Search query reflected WITHOUT htmlspecialchars() !!! -->
            <div class="qd"><?php echo $search_query; ?></div>
        </div>
        <div class="rc">
            <h2>▸ Search Results (<?php echo count($results); ?> found)</h2>
            <?php if (count($results) > 0): ?>
            <table>
                <tr><th>ID</th><th>Product Name</th><th>Price</th></tr>
                <?php foreach ($results as $row): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['product_id']); ?></td>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td>$<?php echo htmlspecialchars($row['price']); ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
            <?php else: ?>
            <div class="nr">No products found matching your query.</div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </main>
    <footer>SecureCorp™ Security Lab &mdash; Information Security Assignment 03</footer>
</body>
</html>
