<?php
declare(strict_types=1);
require_once __DIR__ . "/inc/db.php";
require_once __DIR__ . "/inc/auth.php";
require_role(['admin']);

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) die("Không hợp lệ!");

$stmt = $pdo->prepare("SELECT * FROM phan_loai WHERE id=?");
$stmt->execute([$id]);
$cat = $stmt->fetch();
if (!$cat) die("Không tìm thấy!");

$msg = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ten = trim($_POST['ten'] ?? "");
    if ($ten === "") {
        $msg = "Tên không được để trống!";
    } else {
        try {
            $upd = $pdo->prepare("UPDATE phan_loai SET ten=? WHERE id=?");
            $upd->execute([$ten, $id]);
            header("Location: categories_list.php");
            exit;
        } catch (PDOException $e) {
            $msg = "Tên bị trùng!";
        }
    }
}

include __DIR__ . "/inc/header.php";
?>

<div class="card shadow-sm">
  <div class="card-body">
    <h4 class="mb-3">Sửa Phân loại</h4>

    <?php if ($msg): ?>
      <div class="alert alert-danger"><?= e($msg) ?></div>
    <?php endif; ?>

    <form method="post" class="vstack gap-2">
      <div>
        <label class="form-label">Tên phân loại</label>
        <input name="ten" class="form-control" value="<?= e($cat['ten']) ?>" required>
      </div>
      <button class="btn btn-dark">Lưu</button>
      <a class="btn btn-outline-secondary" href="categories_list.php">Quay lại</a>
    </form>
  </div>
</div>

<?php include __DIR__ . "/inc/footer.php"; ?>
