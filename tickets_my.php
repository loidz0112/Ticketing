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

$q = trim($_GET['q'] ?? '');
$status = $_GET['status'] ?? '';
$status_options = ['Mới','Đang xử lý','Đã hoàn thành'];
if (!in_array($status, $status_options, true)) {
    $status = '';
}

$where = [];
$params = [];
if ($role === 'user') {
    $where[] = "t.user_id = ?";
    $params[] = current_user()['id'];
} else { // technician
    $where[] = "t.assigned_to = ?";
    $params[] = current_user()['id'];
}

if ($q !== '') {
    $like = '%' . $q . '%';
    $where[] = "(t.tieu_de LIKE ? OR t.mo_ta LIKE ? OR p.ten LIKE ? OR u.full_name LIKE ?)";
    array_push($params, $like, $like, $like, $like);
}
if ($status !== '') {
    $where[] = "t.trang_thai = ?";
    $params[] = $status;
}

$sql = "
    SELECT t.*, p.ten AS phan_loai, u.full_name AS nguoi_tao
    FROM tickets t
    JOIN phan_loai p ON p.id = t.phan_loai_id
    JOIN users u ON u.id = t.user_id
";
if ($where) {
    $sql .= " WHERE " . implode(" AND ", $where);
}
$sql .= " ORDER BY t.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$tickets = $stmt->fetchAll();

$export_query = http_build_query(array_filter([
  'q' => $q,
  'status' => $status,
], static fn($value) => $value !== ''));

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
    <a class="btn btn-outline-secondary" href="dashboard.php">Trang chủ</a>
  </div>
</div>

<div class="card shadow-sm mb-3">
  <div class="card-body">
    <form method="get" class="d-flex flex-wrap gap-2 align-items-center">
      <input name="q" value="<?= e($q) ?>" class="form-control" placeholder="Tìm kiếm">
      <select name="status" class="form-select">
        <option value="">Tất cả trạng thái</option>
        <?php foreach ($status_options as $st): ?>
          <option value="<?= e($st) ?>" <?= $status===$st?'selected':'' ?>><?= e($st) ?></option>
        <?php endforeach; ?>
      </select>
      <button class="btn btn-outline-dark">Lọc</button>
      <a class="btn btn-outline-secondary" href="tickets_my.php">Reset</a>
      <a class="btn btn-dark" href="tickets_export.php?format=excel<?= $export_query ? '&' . $export_query : '' ?>">Xuất Excel</a>
      <a class="btn btn-outline-dark" href="tickets_export.php?format=pdf<?= $export_query ? '&' . $export_query : '' ?>">Xuất PDF</a>
    </form>
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
