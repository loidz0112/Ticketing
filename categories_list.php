<?php
declare(strict_types=1);
require_once __DIR__ . "/inc/db.php";
require_once __DIR__ . "/inc/auth.php";
require_role(['admin']);

$cats = $pdo->query("SELECT * FROM phan_loai ORDER BY id DESC")->fetchAll();

include __DIR__ . "/inc/header.php";
?>

<div class="page-header">
  <h4 class="m-0">Quản lý Phân loại</h4>
  <div class="d-flex gap-2">
    <a class="btn btn-dark" href="categories_create.php">+ Thêm</a>
    <a class="btn btn-outline-secondary" href="dashboard.php">Dashboard</a>
  </div>
</div>

<div class="card shadow-sm">
  <div class="card-body">
    <table class="table table-sm align-middle table-striped">
      <thead>
        <tr><th>#</th><th>Tên</th><th></th></tr>
      </thead>
      <tbody>
        <?php foreach ($cats as $c): ?>
          <tr>
            <td><?= (int)$c['id'] ?></td>
            <td><?= e($c['ten']) ?></td>
            <td class="d-flex gap-1">
              <a class="btn btn-sm btn-outline-dark" href="categories_edit.php?id=<?= (int)$c['id'] ?>">Sửa</a>
              <a class="btn btn-sm btn-outline-danger"
                 onclick="return confirm('Xóa phân loại này?');"
                 href="categories_delete.php?id=<?= (int)$c['id'] ?>">Xóa</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include __DIR__ . "/inc/footer.php"; ?>
