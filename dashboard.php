<?php
declare(strict_types=1);
require_once __DIR__ . "/inc/db.php";
require_once __DIR__ . "/inc/auth.php";
require_login();

include __DIR__ . "/inc/header.php";
$role = current_user()['role'];

if ($role === 'admin') {
  $stmt = $pdo->query("
    SELECT t.*, p.ten AS phan_loai,
           u.full_name AS nguoi_tao,
           tech.full_name AS nguoi_xu_ly
    FROM tickets t
    JOIN phan_loai p ON p.id = t.phan_loai_id
    JOIN users u ON u.id = t.user_id
    LEFT JOIN users tech ON tech.id = t.assigned_to
    ORDER BY FIELD(t.trang_thai, 'Mới','Đang xử ký','Đã hoàn thành'),
             t.created_at DESC
  ");
  $tickets = $stmt->fetchAll();
} elseif ($role === 'technician') {
  $stmt = $pdo->prepare("
    SELECT t.*, p.ten AS phan_loai, u.full_name AS nguoi_tao
    FROM tickets t
    JOIN phan_loai p ON p.id = t.phan_loai_id
    JOIN users u ON u.id = t.user_id
    WHERE t.assigned_to = ?
    ORDER BY FIELD(t.trang_thai, 'Mới','Đang xử lý','Đã hoàn thành'),
             t.created_at DESC
  ");
  $stmt->execute([current_user()['id']]);
  $tickets = $stmt->fetchAll();
} else {
  $stmt = $pdo->prepare("
    SELECT t.*, p.ten AS phan_loai
    FROM tickets t
    JOIN phan_loai p ON p.id = t.phan_loai_id
    WHERE t.user_id = ?
    ORDER BY FIELD(t.trang_thai, 'Mới','Đang xử lý','Đã hoàn thành'),
             t.created_at DESC
  ");
  $stmt->execute([current_user()['id']]);
  $tickets = $stmt->fetchAll();
}
?>

<div class="card shadow-sm">
  <div class="card-body">
    <h4 class="mb-2 page-title">Trang chủ</h4>
    <p class="text-muted mb-3">Chọn chức năng theo quyền của bạn.</p>

    <div class="d-flex flex-wrap gap-2">
      <?php if ($role === 'admin'): ?>
        <a class="btn btn-dark" href="tickets_all.php">Quản lý Tickets</a>
        <a class="btn btn-outline-dark" href="users_list.php">Quản lý Users</a>
        <a class="btn btn-outline-dark" href="categories_list.php">Quản lý Phân loại</a>
      <?php elseif ($role === 'technician'): ?>
        <a class="btn btn-dark" href="tickets_my.php">Ticket được giao</a>
      <?php else: ?>
        <a class="btn btn-dark" href="tickets_create.php">Tạo Ticket mới</a>
        <a class="btn btn-outline-dark" href="tickets_my.php">Ticket của tôi</a>
      <?php endif; ?>
    </div>
  </div>
</div>

<div class="card shadow-sm mt-3">
  <div class="card-body">
    <h5 class="mb-3">Lịch sử</h5>
    <?php
      $colspan = 6;
      if ($role === 'technician') $colspan = 7;
      if ($role === 'admin') $colspan = 8;
    ?>
    <div class="table-responsive">
      <table class="table table-sm align-middle table-striped">
        <thead>
          <tr>
            <th>#</th>
            <th>Tiêu đề</th>
            <th>Phân loại</th>
            <?php if ($role === 'admin' || $role === 'technician'): ?>
              <th>Người tạo</th>
            <?php endif; ?>
            <?php if ($role === 'admin'): ?>
              <th>Người xử lý</th>
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
              <?php if ($role === 'admin' || $role === 'technician'): ?>
                <td><?= e($t['nguoi_tao'] ?? '') ?></td>
              <?php endif; ?>
              <?php if ($role === 'admin'): ?>
                <td><?= e($t['nguoi_xu_ly'] ?? 'Chưa gán') ?></td>
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
            <tr><td colspan="<?= $colspan ?>" class="text-muted">Chưa có ticket nào !</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include __DIR__ . "/inc/footer.php"; ?>
