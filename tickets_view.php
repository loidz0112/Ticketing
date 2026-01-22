<?php
declare(strict_types=1);
require_once __DIR__ . "/inc/db.php";
require_once __DIR__ . "/inc/auth.php";
require_login();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) die("Ticket không hợp lệ!");

$stmt = $pdo->prepare("
  SELECT t.*, p.ten AS phan_loai,
         u.full_name AS nguoi_tao,
         tech.full_name AS nguoi_xu_ly
  FROM tickets t
  JOIN phan_loai p ON p.id = t.phan_loai_id
  JOIN users u ON u.id = t.user_id
  LEFT JOIN users tech ON tech.id = t.assigned_to
  WHERE t.id=?
");
$stmt->execute([$id]);
$ticket = $stmt->fetch();
if (!$ticket) die("Không tìm thấy ticket!");

$me = current_user();
$role = $me['role'];

// phân quyền xem ticket
if ($role === 'user' && (int)$ticket['user_id'] !== (int)$me['id']) {
    http_response_code(403); die("403 - Không được xem ticket của người khác.");
}
if ($role === 'technician' && (int)$ticket['assigned_to'] !== (int)$me['id']) {
    http_response_code(403); die("403 - Ticket này không được giao cho bạn.");
}

$notesStmt = $pdo->prepare("
  SELECT n.*, u.full_name AS tech_name
  FROM ticket_notes n
  JOIN users u ON u.id = n.technician_id
  WHERE n.ticket_id=?
  ORDER BY n.created_at DESC
");
$notesStmt->execute([$id]);
$notes = $notesStmt->fetchAll();

include __DIR__ . "/inc/header.php";
?>

<div class="page-header">
  <h4 class="m-0">Ticket #<?= (int)$ticket['id'] ?></h4>
  <a class="btn btn-outline-secondary"
     href="<?= $role==='admin'?'tickets_all.php':'tickets_my.php' ?>">Quay lại</a>
</div>

<div class="row g-3">
  <div class="col-lg-7">
    <div class="card shadow-sm">
      <div class="card-body">
        <h5 class="mb-2"><?= e($ticket['tieu_de']) ?></h5>

        <div class="small text-muted mb-2">
          <b>Phân loại:</b> <?= e($ticket['phan_loai']) ?> |
          <b>Người tạo:</b> <?= e($ticket['nguoi_tao']) ?> |
          <b>Người xử lý:</b> <?= e($ticket['nguoi_xu_ly'] ?? 'Chưa gán') ?>
        </div>

        <div class="mb-2">
          <b>Trạng thái:</b>
          <span class="badge bg-<?= $ticket['trang_thai']==='Đã hoàn thành'?'success':($ticket['trang_thai']==='Đang xử lý'?'warning text-dark':'secondary') ?>">
            <?= e($ticket['trang_thai']) ?>
          </span>
        </div>

        <hr>
        <p class="mb-0" style="white-space: pre-wrap;"><?= e($ticket['mo_ta']) ?></p>
      </div>
    </div>
  </div>

  <div class="col-lg-5">
    <?php if ($role === 'technician'): ?>
      <div class="card shadow-sm mb-3">
        <div class="card-body">
          <h6>Cập nhật xử lý</h6>
          <form method="post" action="tickets_update.php" class="vstack gap-2">
            <input type="hidden" name="ticket_id" value="<?= (int)$ticket['id'] ?>">
            <div>
              <label class="form-label">Trạng thái</label>
              <select name="trang_thai" class="form-select">
                <?php foreach (['Đang xử lý','Đã hoàn thành'] as $st): ?>
                  <option value="<?= $st ?>" <?= $ticket['trang_thai']===$st?'selected':'' ?>><?= $st ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div>
              <label class="form-label">Ghi chú (sẽ lưu vào lịch sử)</label>
              <textarea name="note" class="form-control" rows="3" required></textarea>
            </div>
            <button class="btn btn-dark">Cập nhật</button>
          </form>
        </div>
      </div>
    <?php endif; ?>

    <div class="card shadow-sm">
      <div class="card-body">
        <h6 class="mb-2">Lịch sử xử lý</h6>
        <?php foreach ($notes as $n): ?>
          <div class="panel-note p-2 mb-2">
            <div class="small text-muted">
              <b><?= e($n['tech_name']) ?></b> — <?= e($n['created_at']) ?>
            </div>
            <div style="white-space: pre-wrap;"><?= e($n['note']) ?></div>
          </div>
        <?php endforeach; ?>

        <?php if (count($notes) === 0): ?>
          <div class="text-muted small">Chưa có ghi chú xử lý.</div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . "/inc/footer.php"; ?>
