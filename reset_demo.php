<?php
require_once __DIR__ . "/inc/db.php";
require_once __DIR__ . "/inc/auth.php";
enforce_route_access();
require_role(['admin']);

$hash = password_hash("123456", PASSWORD_BCRYPT);

// Reset lai 3 tai khoan demo
$stmt = $pdo->prepare("UPDATE users SET password_hash=? WHERE email IN (?,?,?)");
$stmt->execute([$hash, "admin@gmail.com", "tech@gmail.com", "user@gmail.com"]);

header("Content-Type: text/html; charset=UTF-8");

$safeHash = htmlspecialchars($hash, ENT_QUOTES, "UTF-8");
?>
<!doctype html>
<html lang="vi">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reset Demo - Ticketing</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    <style>
      :root {
        --bg-1: #f4f0ea;
        --bg-2: #e8f1fb;
        --accent: #e3684f;
        --accent-2: #1f8f7c;
        --card: #ffffff;
        --text: #1f2838;
        --muted: #5c6b82;
        --success: #1f8f7c;
        --border: #d9e2ee;
        --shadow: 0 24px 60px rgba(20, 35, 60, 0.12);
      }
      * { box-sizing: border-box; }
      body {
        margin: 0;
        font-family: "Plus Jakarta Sans", "Segoe UI", sans-serif;
        color: var(--text);
        background:
          radial-gradient(1000px 520px at 90% -10%, rgba(31, 143, 124, 0.12), transparent 60%),
          radial-gradient(900px 520px at 10% -10%, rgba(227, 104, 79, 0.14), transparent 60%),
          linear-gradient(160deg, var(--bg-1), var(--bg-2));
        min-height: 100vh;
        display: grid;
        place-items: center;
        padding: 24px;
      }
      .card {
        width: min(720px, 92vw);
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: 18px;
        padding: 28px 30px;
        box-shadow: var(--shadow);
      }
      .badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: rgba(31, 143, 124, 0.12);
        color: var(--success);
        border: 1px solid rgba(31, 143, 124, 0.3);
        padding: 6px 12px;
        border-radius: 999px;
        font-size: 12px;
        letter-spacing: 0.08em;
        text-transform: uppercase;
      }
      h1 {
        margin: 14px 0 10px;
        font-size: clamp(22px, 3vw, 30px);
        letter-spacing: 0.3px;
        font-family: "Space Grotesk", "Segoe UI", sans-serif;
      }
      p {
        margin: 0 0 14px;
        color: var(--muted);
        line-height: 1.6;
      }
      .grid {
        display: grid;
        gap: 14px;
      }
      .info {
        background: #f7f9fc;
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 14px 16px;
        font-family: "Courier New", monospace;
        font-size: 13px;
        word-break: break-all;
      }
      .row {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        align-items: center;
      }
      .tag {
        background: rgba(227, 104, 79, 0.12);
        color: var(--accent);
        border: 1px solid rgba(227, 104, 79, 0.35);
        padding: 6px 10px;
        border-radius: 10px;
        font-size: 12px;
        letter-spacing: 0.04em;
      }
      .muted {
        color: var(--muted);
        font-size: 13px;
      }
      @media (max-width: 520px) {
        .card { padding: 22px; }
        .info { font-size: 12px; }
      }
    </style>
  </head>
  <body>
    <main class="card">
      <div class="badge">Reset thanh cong</div>
      <h1>Cap nhat mat khau demo hoan tat</h1>
      <p>Ba tai khoan demo da duoc dat lai mat khau ve <strong>123456</strong>. Ban co the dang nhap bang email tuong ung.</p>
      <div class="grid">
        <div class="row">
          <span class="tag">admin@gmail.com</span>
          <span class="tag">tech@gmail.com</span>
          <span class="tag">user@gmail.com</span>
        </div>
        <div>
          <div class="muted">Hash moi:</div>
          <div class="info"><?php echo $safeHash; ?></div>
        </div>
      </div>
    </main>
  </body>
</html>
