<?php
declare(strict_types=1);
require_once __DIR__ . "/inc/db.php";
require_once __DIR__ . "/inc/auth.php";
require_role(['admin']);

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) die("User không hợp lệ!");

$stmt = $pdo->prepare("SELECT id, full_name, email, role FROM users WHERE id=?");
$stmt->execute([$id]);
$user = $stmt->fetch();
if (!$user) die("Không tìm thấy user!");

$msg = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? "");
    $role = $_POST['role'] ?? "user";
    $newpass = trim($_POST['new_password'] ?? "");

    if ($full_name === "") {
        $msg = "Tên không được để trống!";
    } elseif (!in_array($role, ['admin','technician','user'], true)) {
        $msg = "Role không hợp lệ!";
    } else {
        if ($newpass !== "") {
            $hash = password_hash($newpass, PASSWORD_BCRYPT);
            $upd = $pdo->prepare("UPDATE users SET full_name=?, role=?, password_hash=? WHERE id=?");
            $upd->execute([$full_name, $role, $hash, $id]);
        } else {
            $upd = $pdo->prepare("UPDATE users SET full_name=?, role=? WHERE id=?");
            $upd->execute([$full_name, $role, $id]);
        }
        header("Location: users_list.php");
        exit;
    }
}

include __DIR__ . "/inc/header.php";
?>

<div class="card shadow-sm">
  <div class="card-body">
    <h4 class="mb-3">Sửa User</h4>

    <?php if ($msg): ?>
      <div class="alert alert-danger"><?= e($msg) ?></div>
    <?php endif; ?>

    <form method="post" class="vstack gap-2">
      <div>
        <label class="form-label">Họ tên</label>
        <input name="full_name" class="form-control" value="<?= e($user['full_name']) ?>" required>
      </div>

      <div>
        <label class="form-label">Email (không sửa)</label>
        <input class="form-control" value="<?= e($user['email']) ?>" disabled>
      </div>

      <div>
        <label class="form-label">Role</label>
        <select name="role" class="form-select">
          <?php foreach (['user','technician','admin'] as $r): ?>
            <option value="<?= $r ?>" <?= $user['role']===$r?'selected':'' ?>><?= $r ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div>
        <label class="form-label">Mật khẩu mới (bỏ trống nếu không đổi)</label>
        <input name="new_password" type="password" class="form-control">
      </div>

      <button class="btn btn-dark">Lưu</button>
      <a class="btn btn-outline-secondary" href="users_list.php">Quay lại</a>
    </form>
  </div>
</div>

<?php include __DIR__ . "/inc/footer.php"; ?>
