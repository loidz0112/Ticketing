<?php
declare(strict_types=1);
require_once __DIR__ . "/inc/db.php";
require_once __DIR__ . "/inc/auth.php";
require_role(['admin']);

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) die("Không hợp lệ!");

try {
    $del = $pdo->prepare("DELETE FROM phan_loai WHERE id=?");
    $del->execute([$id]);
} catch (PDOException $e) {
    die("Không thể xóa vì đang được dùng trong tickets!");
}

header("Location: categories_list.php");
exit;
