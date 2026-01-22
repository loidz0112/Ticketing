<?php
declare(strict_types=1);
require_once __DIR__ . "/inc/db.php";
require_once __DIR__ . "/inc/auth.php";
require_role(['admin']);

$msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? "");
    $email = trim($_POST['email'] ?? "");
    $password = trim($_POST['password'] ?? "");
    $role = $_POST['role'] ?? "user";

    if ($full_name === "" || $email === "" || $password === "") {
        $msg = "Vui lòng nhập đủ thông tin!";
    } elseif (!in_array($role, ['admin','technician','user'], true)) {
        $msg = "Role không hợp lệ!";
    } else {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        try {
            $stmt = $pdo->prepare("INSERT INTO users(full_name,email,password_hash,role) VALUES (?,?,?,?)");
            $stmt->execute([$full_name, $email, $hash, $role]);
            header("Location: users_list.php");
            exit;
        } catch (PDOException $e) {
            $msg = "Email đã tồn tại!";
        }
    }
}

include __DIR__ . "/inc/header.php";
?>

<div class="card shadow-sm">
  <div class="card-body">
    <h4 class="mb-3">Thêm User</h4>

    <?php if ($msg): ?>
      <div class="alert alert-danger"><?= e($msg) ?></div>
    <?php endif; ?>

    <form method="post" class="vstack gap-2">
      <div>
        <label class="form-label">Họ tên</label>
        <input name="full_name" class="form-control" required>
      </div>
      <div>
        <label class="form-label">Email</label>
        <input name="email" type="email" class="form-control" required>
      </div>
      <div>
        <label class="form-label">Mật khẩu</label>
        <input name="password" type="password" class="form-control" required>
      </div>
      <div>
        <label class="form-label">Role</label>
        <select name="role" class="form-select">
          <option value="user">user</option>
          <option value="technician">technician</option>
          <option value="admin">admin</option>
        </select>
      </div>
      <button class="btn btn-dark">Tạo</button>
      <a class="btn btn-outline-secondary" href="users_list.php">Quay lại</a>
    </form>
  </div>
</div>

<?php include __DIR__ . "/inc/footer.php"; ?>
