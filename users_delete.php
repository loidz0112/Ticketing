<?php
declare(strict_types=1);
require_once __DIR__ . "/inc/db.php";
require_once __DIR__ . "/inc/auth.php";
require_role(['admin']);

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) die("Không hợp lệ!");

if ($id === (int)current_user()['id']) {
    die("Không thể tự xóa chính mình!");
}

$stmt = $pdo->prepare("DELETE FROM users WHERE id=?");
$stmt->execute([$id]);

header("Location: users_list.php");
exit;
