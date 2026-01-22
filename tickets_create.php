<?php
declare(strict_types=1);
require_once __DIR__ . "/inc/db.php";
require_once __DIR__ . "/inc/auth.php";

require_role(['user']);

$cats = $pdo->query("SELECT * FROM phan_loai ORDER BY ten")->fetchAll();

$msg = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tieu_de = trim($_POST['tieu_de'] ?? "");
    $mo_ta = trim($_POST['mo_ta'] ?? "");
    $phan_loai_id = (int)($_POST['phan_loai_id'] ?? 0);

    if ($tieu_de === "" || $mo_ta === "" || $phan_loai_id <= 0) {
        $msg = "Vui lòng nhập đủ thông tin!";
    } else {
        $stmt = $pdo->prepare("INSERT INTO tickets(tieu_de, mo_ta, phan_loai_id, user_id) VALUES (?,?,?,?)");
        $stmt->execute([$tieu_de, $mo_ta, $phan_loai_id, current_user()['id']]);
        header("Location: tickets_my.php");
        exit;
    }
}

include __DIR__ . "/inc/header.php";
?>

<div class="card shadow-sm">
  <div class="card-body">
    <h4 class="mb-3">Tạo Ticket mới</h4>

    <?php if ($msg): ?>
      <div class="alert alert-danger"><?= e($msg) ?></div>
    <?php endif; ?>

    <form method="post" class="vstack gap-3">
      <div>
        <label class="form-label">Tiêu đề</label>
        <input name="tieu_de" class="form-control" required>
      </div>

      <div>
        <label class="form-label">Phân loại</label>
        <select name="phan_loai_id" class="form-select" required>
          <option value="">-- Chọn --</option>
          <?php foreach ($cats as $c): ?>
            <option value="<?= (int)$c['id'] ?>"><?= e($c['ten']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div>
        <label class="form-label">Mô tả</label>
        <textarea name="mo_ta" class="form-control" rows="5" required></textarea>
      </div>

      <button class="btn btn-dark">Gửi yêu cầu</button>
      <a class="btn btn-outline-secondary" href="dashboard.php">Quay lại</a>
    </form>
  </div>
</div>

<?php include __DIR__ . "/inc/footer.php"; ?>
