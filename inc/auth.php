<?php
// inc/auth.php
declare(strict_types=1);
if (session_status() === PHP_SESSION_NONE) session_start();

function is_logged_in(): bool {
    return isset($_SESSION['user']);
}

function current_user(): ?array {
    return $_SESSION['user'] ?? null;
}

function require_login(): void {
    if (!is_logged_in()) {
        header("Location: login.php");
        exit;
    }
}

function require_role(array $roles): void {
    require_login();
    $u = current_user();
    if (!$u || !in_array($u['role'], $roles, true)) {
        http_response_code(403);
        die("403 - Bạn không có quyền truy cập trang này.");
    }
}

function e(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}
