<?php
declare(strict_types=1);
require_once __DIR__ . "/inc/db.php";
require_once __DIR__ . "/inc/auth.php";
require_role(['admin']);

$msg = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ten = trim($_POST['ten'] ?? "");
    if ($ten === "") {
        $msg = "Tên không được để trống!";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO phan_loai(ten) VALUES (?)");
            $stmt->execute([$ten]);
            header("Location: categories_list.php");
            exit;
        } catch (PDOException $e) {
            $msg = "Tên phân loại đã tồn tại!";
        }
    }
}

include __DIR__ . "/inc/header.php";
?>

<div class="card shadow-sm">
  <div class="card-body">
    <h4 class="mb-3">Thêm Phân loại</h4>
    <?php if ($msg): ?>
      <div class="alert alert-danger"><?= e($msg) ?></div>
    <?php endif; ?>

    <form method="post" class="vstack gap-2">
      <div>
        <label class="form-label">Tên phân loại</label>
        <input name="ten" class="form-control" required>
      </div>
      <button class="btn btn-dark">Tạo</button>
      <a class="btn btn-outline-secondary" href="categories_list.php">Quay lại</a>
    </form>
  </div>
</div>

<?php include __DIR__ . "/inc/footer.php"; ?>
