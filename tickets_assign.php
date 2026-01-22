<?php
declare(strict_types=1);
require_once __DIR__ . "/inc/db.php";
require_once __DIR__ . "/inc/auth.php";
require_role(['admin']);

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) die("Ticket không hợp lệ!");

$ticket = $pdo->prepare("SELECT * FROM tickets WHERE id=?");
$ticket->execute([$id]);
$ticket = $ticket->fetch();
if (!$ticket) die("Không tìm thấy ticket!");

$techs = $pdo->query("SELECT id, full_name FROM users WHERE role='technician' ORDER BY full_name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $assigned_to = (int)($_POST['assigned_to'] ?? 0);
    $status = $_POST['trang_thai'] ?? 'Mới';

    if (!in_array($status, ['Mới','Đang xử lý','Đã hoàn thành'], true)) $status = 'Mới';

    $stmt = $pdo->prepare("UPDATE tickets SET assigned_to=?, trang_thai=?, updated_at=NOW() WHERE id=?");
    $stmt->execute([$assigned_to ?: null, $status, $id]);

    header("Location: tickets_all.php");
    exit;
}

include __DIR__ . "/inc/header.php";
?>

<div class="card shadow-sm">
  <div class="card-body">
    <h4 class="mb-3">Gán Ticket #<?= (int)$ticket['id'] ?></h4>

    <p class="text-muted mb-2"><b>Tiêu đề:</b> <?= e($ticket['tieu_de']) ?></p>

    <form method="post" class="vstack gap-3">
      <div>
        <label class="form-label">Gán cho Technician</label>
        <select name="assigned_to" class="form-select">
          <option value="0">-- Chưa gán --</option>
          <?php foreach ($techs as $t): ?>
            <option value="<?= (int)$t['id'] ?>" <?= ((int)$ticket['assigned_to'] === (int)$t['id']) ? 'selected' : '' ?>>
              <?= e($t['full_name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div>
        <label class="form-label">Trạng thái</label>
        <select name="trang_thai" class="form-select">
          <?php foreach (['Mới','Đang xử lý','Đã hoàn thành'] as $st): ?>
            <option value="<?= $st ?>" <?= $ticket['trang_thai']===$st?'selected':'' ?>><?= $st ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <button class="btn btn-dark">Lưu</button>
      <a class="btn btn-outline-secondary" href="tickets_all.php">Quay lại</a>
    </form>
  </div>
</div>

<?php include __DIR__ . "/inc/footer.php"; ?>
