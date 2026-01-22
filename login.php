<?php
declare(strict_types=1);
require_once __DIR__ . "/inc/db.php";
require_once __DIR__ . "/inc/auth.php";

if (is_logged_in()) {
    header("Location: dashboard.php");
    exit;
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? "");
    $pass  = trim($_POST['password'] ?? "");

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($pass, $user['password_hash'])) {
        $error = "Sai email hoặc mật khẩu!";
    } else {
        $_SESSION['user'] = [
            'id' => (int)$user['id'],
            'full_name' => $user['full_name'],
            'email' => $user['email'],
            'role' => $user['role'],
        ];
        header("Location: dashboard.php");
        exit;
    }
}

include __DIR__ . "/inc/header.php";
?>

<div class="row justify-content-center auth-wrap">
  <div class="col-md-5">
    <div class="card shadow-sm auth-card">
      <div class="card-body">
        <h4 class="mb-3">Đăng nhập</h4>

        <?php if ($error): ?>
          <div class="alert alert-danger"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="post" class="vstack gap-2">
          <div>
            <label class="form-label">Email</label>
            <input name="email" type="email" class="form-control" required>
          </div>
          <div>
            <label class="form-label">Mật khẩu</label>
            <input name="password" type="password" class="form-control" required>
          </div>
          <button class="btn btn-dark">Đăng nhập</button>

          <div class="small text-muted mt-2">
            Demo: admin@gmail.com / tech@gmail.com / user@gmail.com — mk: 1
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . "/inc/footer.php"; ?>
