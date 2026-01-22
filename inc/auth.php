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

function enforce_route_access(): void {
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
    $fallback = $_SERVER['SCRIPT_NAME'] ?? ($_SERVER['PHP_SELF'] ?? '');
    $route = basename($path ?: $fallback);

    $public_routes = ['login.php', 'logout.php', 'index.php'];
    if (in_array($route, $public_routes, true)) {
        return;
    }

    $role_map = [
        'dashboard.php' => ['admin', 'technician', 'user'],
        'tickets_all.php' => ['admin'],
        'tickets_assign.php' => ['admin'],
        'tickets_create.php' => ['user'],
        'tickets_my.php' => ['admin', 'technician', 'user'],
        'tickets_update.php' => ['technician'],
        'tickets_view.php' => ['admin', 'technician', 'user'],
        'tickets_export.php' => ['admin', 'technician'],
        'users_list.php' => ['admin'],
        'users_create.php' => ['admin'],
        'users_edit.php' => ['admin'],
        'users_delete.php' => ['admin'],
        'categories_list.php' => ['admin'],
        'categories_create.php' => ['admin'],
        'categories_edit.php' => ['admin'],
        'categories_delete.php' => ['admin'],
        'reset_demo.php' => ['admin'],
    ];

    require_login();

    $allowed = $role_map[$route] ?? null;
    if ($allowed === null) {
        http_response_code(403);
        die("403 - Ban khong co quyen truy cap trang nay.");
    }

    $role = current_user()['role'] ?? '';
    if (!in_array($role, $allowed, true)) {
        http_response_code(403);
        die("403 - Ban khong co quyen truy cap trang nay.");
    }
}
