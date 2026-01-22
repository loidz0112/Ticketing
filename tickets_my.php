<?php
declare(strict_types=1);
require_once __DIR__ . "/inc/db.php";
require_once __DIR__ . "/inc/auth.php";
require_login();

$role = current_user()['role'];

if ($role === 'admin') {
    header("Location: tickets_all.php");
    exit;
}

if ($role === 'user') {
    $stmt = $pdo->prepare("
        SELECT t.*, p.ten AS phan_loai
        FROM tickets t
        JOIN phan_loai p ON p.id = t.phan_loai_id
        WHERE t.user_id = ?
        ORDER BY t.created_at DESC
    ");
    $stmt->execute([current_user()['id']]);
} else { // technician
    $stmt = $pdo->prepare("
        SELECT t.*, p.ten AS phan_loai, u.full_name AS nguoi_tao
        FROM tickets t
        JOIN phan_loai p ON p.id = t.phan_loai_id
        JOIN users u ON u.id = t.user_id
        WHERE t.assigned_to = ?
        ORDER BY t.created_at DESC
    ");
    $stmt->execute([current_user()['id']]);
}

$tickets = $stmt->fetchAll();

include __DIR__ . "/inc/header.php";
?>

<div class="page-header">
  <h4 class="m-0">
    <?= $role === 'user' ? "Ticket của tôi" : "Ticket được giao" ?>
  </h4>
  <div class="d-flex gap-2">
    <?php if ($role === 'user'): ?>
      <a class="btn btn-dark" href="tickets_create.php">+ Tạo Ticket</a>
    <?php endif; ?>
    <a class="btn btn-outline-secondary" href="dashboard.php">Dashboard</a>
  </div>
</div>

<div class="card shadow-sm">
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-sm align-middle table-striped">
        <thead>
          <tr>
            <th>#</th>
            <th>Tiêu đề</th>
            <th>Phân loại</th>
            <?php if ($role === 'technician'): ?>
              <th>Người tạo</th>
            <?php endif; ?>
            <th>Trạng thái</th>
            <th>Ngày tạo</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($tickets as $t): ?>
            <tr>
              <td><?= (int)$t['id'] ?></td>
              <td><?= e($t['tieu_de']) ?></td>
              <td><?= e($t['phan_loai']) ?></td>
              <?php if ($role === 'technician'): ?>
                <td><?= e($t['nguoi_tao']) ?></td>
              <?php endif; ?>
              <td>
                <span class="badge bg-<?= $t['trang_thai']==='Đã hoàn thành'?'success':($t['trang_thai']==='Đang xử lý'?'warning text-dark':'secondary') ?>">
                  <?= e($t['trang_thai']) ?>
                </span>
              </td>
              <td><?= e($t['created_at']) ?></td>
              <td>
                <a class="btn btn-sm btn-outline-dark" href="tickets_view.php?id=<?= (int)$t['id'] ?>">Xem</a>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if (count($tickets) === 0): ?>
            <tr><td colspan="7" class="text-muted">Chưa có ticket nào.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include __DIR__ . "/inc/footer.php"; ?>
