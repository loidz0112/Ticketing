<?php
declare(strict_types=1);
require_once __DIR__ . "/inc/db.php";
require_once __DIR__ . "/inc/auth.php";
require_role(['admin']);

$users = $pdo->query("SELECT id, full_name, email, role, created_at FROM users ORDER BY id DESC")->fetchAll();

include __DIR__ . "/inc/header.php";
?>

<div class="page-header">
  <h4 class="m-0">Quản lý Users</h4>
  <div class="d-flex gap-2">
    <a class="btn btn-dark" href="users_create.php">+ Thêm user</a>
    <a class="btn btn-outline-secondary" href="dashboard.php">Dashboard</a>
  </div>
</div>

<div class="card shadow-sm">
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-sm align-middle table-striped">
        <thead>
          <tr>
            <th>#</th><th>Họ tên</th><th>Email</th><th>Role</th><th>Ngày tạo</th><th></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($users as $u): ?>
            <tr>
              <td><?= (int)$u['id'] ?></td>
              <td><?= e($u['full_name']) ?></td>
              <td><?= e($u['email']) ?></td>
              <td><span class="badge bg-dark"><?= e($u['role']) ?></span></td>
              <td><?= e($u['created_at']) ?></td>
              <td class="d-flex gap-1">
                <a class="btn btn-sm btn-outline-dark" href="users_edit.php?id=<?= (int)$u['id'] ?>">Sửa</a>
                <?php if ((int)$u['id'] !== (int)current_user()['id']): ?>
                  <a class="btn btn-sm btn-outline-danger"
                     onclick="return confirm('Xóa user này?');"
                     href="users_delete.php?id=<?= (int)$u['id'] ?>">Xóa</a>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include __DIR__ . "/inc/footer.php"; ?>
