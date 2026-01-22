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
    $reject_reason = trim($_POST['ly_do_tu_choi'] ?? '');

    if (!in_array($status, ['Mới','Đang xử lý','Đã hoàn thành','Từ chối'], true)) $status = 'Mới';
    if ($status === 'Từ chối') {
        if ($reject_reason === '') {
            die("Vui lòng nhập lý do từ chối.");
        }
        $assigned_to = 0;
    } else {
        $reject_reason = null;
    }

    $stmt = $pdo->prepare("UPDATE tickets SET assigned_to=?, trang_thai=?, ly_do_tu_choi=?, updated_at=NOW() WHERE id=?");
    $stmt->execute([$assigned_to ?: null, $status, $reject_reason, $id]);

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
          <?php foreach (['Mới','Đang xử lý','Đã hoàn thành','Từ chối'] as $st): ?>
            <option value="<?= $st ?>" <?= $ticket['trang_thai']===$st?'selected':'' ?>><?= $st ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div>
        <label class="form-label">Lý do từ chối</label>
        <textarea name="ly_do_tu_choi" class="form-control" rows="3" placeholder="Bắt buộc khi chọn trạng thái Từ chối"><?= e($ticket['trang_thai']==='Từ chối' ? ($ticket['ly_do_tu_choi'] ?? '') : '') ?></textarea>
        <div class="form-text">Chỉ áp dụng khi trạng thái là Từ chối.</div>
      </div>

      <button class="btn btn-dark">Lưu</button>
      <a class="btn btn-outline-secondary" href="tickets_all.php">Quay lại</a>
    </form>
  </div>
</div>

<?php include __DIR__ . "/inc/footer.php"; ?>
