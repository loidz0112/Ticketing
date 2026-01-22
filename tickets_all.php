<?php
declare(strict_types=1);
require_once __DIR__ . "/inc/db.php";
require_once __DIR__ . "/inc/auth.php";

require_role(['admin']);

$tickets = $pdo->query("
  SELECT t.*, p.ten AS phan_loai,
         u.full_name AS nguoi_tao,
         tech.full_name AS nguoi_xu_ly
  FROM tickets t
  JOIN phan_loai p ON p.id = t.phan_loai_id
  JOIN users u ON u.id = t.user_id
  LEFT JOIN users tech ON tech.id = t.assigned_to
  ORDER BY t.created_at DESC
")->fetchAll();

include __DIR__ . "/inc/header.php";
?>

<div class="page-header">
  <h4 class="m-0">Quản lý Tickets</h4>
  <a class="btn btn-outline-secondary" href="dashboard.php">Dashboard</a>
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
            <th>Người tạo</th>
            <th>Người xử lý</th>
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
              <td><?= e($t['nguoi_tao']) ?></td>
              <td><?= e($t['nguoi_xu_ly'] ?? 'Chưa gán') ?></td>
              <td>
                <span class="badge bg-<?= $t['trang_thai']==='Đã hoàn thành'?'success':($t['trang_thai']==='Đang xử lý'?'warning text-dark':'secondary') ?>">
                  <?= e($t['trang_thai']) ?>
                </span>
              </td>
              <td><?= e($t['created_at']) ?></td>
              <td class="d-flex gap-1">
                <a class="btn btn-sm btn-outline-dark" href="tickets_view.php?id=<?= (int)$t['id'] ?>">Xem</a>
                <a class="btn btn-sm btn-dark" href="tickets_assign.php?id=<?= (int)$t['id'] ?>">Gán</a>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if (count($tickets) === 0): ?>
            <tr><td colspan="8" class="text-muted">Chưa có ticket nào.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include __DIR__ . "/inc/footer.php"; ?>
