<?php
declare(strict_types=1);
require_once __DIR__ . "/inc/db.php";
require_once __DIR__ . "/inc/auth.php";
enforce_route_access();

require_role(['technician']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: dashboard.php");
    exit;
}

$ticket_id = (int)($_POST['ticket_id'] ?? 0);
$trang_thai = $_POST['trang_thai'] ?? 'Đang xử lý';
$note = trim($_POST['note'] ?? "");

if ($ticket_id <= 0 || $note === "") die("Dữ liệu không hợp lệ!");

if (!in_array($trang_thai, ['Đang xử lý','Đã hoàn thành'], true)) {
    $trang_thai = 'Đang xử lý';
}

// đảm bảo ticket thuộc về technician
$stmt = $pdo->prepare("SELECT * FROM tickets WHERE id=? AND assigned_to=?");
$stmt->execute([$ticket_id, current_user()['id']]);
$ticket = $stmt->fetch();
if (!$ticket) {
    http_response_code(403);
    die("403 - Ticket không được giao cho bạn.");
}

// update ticket
$upd = $pdo->prepare("UPDATE tickets SET trang_thai=?, updated_at=NOW() WHERE id=?");
$upd->execute([$trang_thai, $ticket_id]);

// add note
$ins = $pdo->prepare("INSERT INTO ticket_notes(ticket_id, technician_id, note) VALUES (?,?,?)");
$ins->execute([$ticket_id, current_user()['id'], $note]);

header("Location: tickets_view.php?id=" . $ticket_id);
exit;
