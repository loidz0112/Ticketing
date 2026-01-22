<?php
// inc/header.php
require_once __DIR__ . "/auth.php";
enforce_route_access();
?>
<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Ticketing System</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    :root {
      color-scheme: light;
      --bg-1: #f4f0ea;
      --bg-2: #e8f1fb;
      --ink: #1f2838;
      --ink-soft: #5c6b82;
      --accent: #e3684f;
      --accent-2: #1f8f7c;
      --card: #ffffff;
      --border: #d9e2ee;
      --shadow: 0 18px 45px rgba(20, 35, 60, 0.12);
    }
    * { box-sizing: border-box; }
    body.app-body {
      font-family: "Plus Jakarta Sans", "Segoe UI", sans-serif;
      color: var(--ink);
      background:
        radial-gradient(1000px 520px at 90% -10%, rgba(31, 143, 124, 0.12), transparent 60%),
        radial-gradient(900px 520px at 10% -10%, rgba(227, 104, 79, 0.14), transparent 60%),
        linear-gradient(160deg, var(--bg-1), var(--bg-2));
      min-height: 100vh;
    }
    @keyframes float-in {
      from { opacity: 0; transform: translateY(12px); }
      to { opacity: 1; transform: translateY(0); }
    }
    a { text-decoration: none; }
    .app-nav {
      background: rgba(21, 28, 40, 0.86);
      backdrop-filter: blur(14px);
      border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    }
    .app-nav .navbar-brand {
      font-family: "Space Grotesk", "Segoe UI", sans-serif;
      font-weight: 700;
      letter-spacing: 0.4px;
    }
    .app-nav .navbar-text { font-size: 0.95rem; }
    .app-container {
      padding-top: 24px;
      padding-bottom: 36px;
    }
    .page-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 16px;
      flex-wrap: wrap;
      margin-bottom: 22px;
      animation: float-in 0.45s ease both;
    }
    .page-title,
    .page-header h4,
    .page-header h5,
    .card h4,
    .card h5 {
      font-family: "Space Grotesk", "Segoe UI", sans-serif;
      font-weight: 600;
      margin: 0;
      font-size: clamp(1.2rem, 1.2rem + 0.6vw, 1.7rem);
    }
    .card {
      border: 1px solid var(--border);
      border-radius: 16px;
      box-shadow: var(--shadow);
      background: var(--card);
      animation: float-in 0.6s ease both;
    }
    .card-header {
      background: transparent;
      border-bottom: 1px solid var(--border);
    }
    .table {
      --bs-table-striped-bg: rgba(31, 143, 124, 0.05);
    }
    .table thead th {
      text-transform: uppercase;
      font-size: 0.72rem;
      letter-spacing: 0.12em;
      color: var(--ink-soft);
      border-bottom-color: var(--border);
    }
    .table td, .table th {
      padding: 0.8rem 0.75rem;
    }
    .badge {
      border-radius: 999px;
      padding: 0.35em 0.7em;
      font-weight: 600;
      letter-spacing: 0.02em;
    }
    .btn {
      border-radius: 12px;
      font-weight: 600;
      padding: 0.5rem 1rem;
    }
    .btn-dark {
      background: var(--accent);
      border-color: var(--accent);
    }
    .btn-dark:hover,
    .btn-dark:focus {
      background: #c95742;
      border-color: #c95742;
    }
    .btn-outline-dark {
      color: var(--ink);
      border-color: rgba(31, 40, 56, 0.35);
    }
    .btn-outline-dark:hover,
    .btn-outline-dark:focus {
      color: #fff;
      background: var(--ink);
      border-color: var(--ink);
    }
    .btn-outline-secondary {
      color: var(--ink-soft);
      border-color: rgba(92, 107, 130, 0.4);
    }
    .btn-outline-secondary:hover,
    .btn-outline-secondary:focus {
      color: #fff;
      background: var(--ink-soft);
      border-color: var(--ink-soft);
    }
    .form-label {
      font-weight: 600;
      color: var(--ink);
    }
    .form-control,
    .form-select {
      border-radius: 12px;
      border-color: var(--border);
      padding: 0.6rem 0.75rem;
    }
    .form-control:focus,
    .form-select:focus {
      border-color: rgba(227, 104, 79, 0.6);
      box-shadow: 0 0 0 0.2rem rgba(227, 104, 79, 0.15);
    }
    .alert {
      border-radius: 12px;
    }
    .panel-note {
      border: 1px dashed rgba(31, 143, 124, 0.35);
      border-radius: 12px;
      background: rgba(31, 143, 124, 0.06);
    }
    .auth-wrap {
      min-height: calc(100vh - 120px);
      display: flex;
      align-items: center;
    }
    .auth-card {
      background: rgba(255, 255, 255, 0.96);
      border: 1px solid rgba(255, 255, 255, 0.6);
      box-shadow: 0 25px 60px rgba(31, 40, 56, 0.12);
      backdrop-filter: blur(12px);
    }
    @media (max-width: 576px) {
      .btn { width: 100%; }
    }
  </style>
</head>
<body class="app-body">
<nav class="navbar navbar-expand-lg navbar-dark app-nav">
  <div class="container">
    <a class="navbar-brand" href="dashboard.php">Quản lý bảo trì kỹ thuật nội bộ</a>
    <div class="d-flex gap-2">
      <?php if (is_logged_in()): ?>
        <span class="navbar-text text-white">
          <?= e(current_user()['full_name']) ?> (<?= e(current_user()['role']) ?>)
        </span>
        <a class="btn btn-sm btn-outline-light" href="logout.php">Đăng xuất</a>
      <?php else: ?>
        <a class="btn btn-sm btn-outline-light" href="login.php">Đăng nhập</a>
      <?php endif; ?>
    </div>
  </div>
</nav>

<div class="container app-container">
