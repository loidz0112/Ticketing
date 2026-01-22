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
    $files = [];
    $max_files = 5;
    $max_file_size = 2 * 1024 * 1024;
    $allowed_mimes = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp',
    ];

    if (isset($_FILES['attachments']) && is_array($_FILES['attachments']['name'])) {
        $count = count($_FILES['attachments']['name']);
        if ($count > $max_files) {
            $msg = "Chi duoc tai toi da {$max_files} file.";
        } else {
            $finfo = function_exists('finfo_open') ? finfo_open(FILEINFO_MIME_TYPE) : null;
            for ($i = 0; $i < $count; $i++) {
                $error = $_FILES['attachments']['error'][$i] ?? UPLOAD_ERR_NO_FILE;
                if ($error === UPLOAD_ERR_NO_FILE) {
                    continue;
                }
                if ($error !== UPLOAD_ERR_OK) {
                    $msg = "Upload file bi loi.";
                    break;
                }
                $size = (int)($_FILES['attachments']['size'][$i] ?? 0);
                if ($size <= 0 || $size > $max_file_size) {
                    $msg = "File qua lon (toi da 2MB).";
                    break;
                }
                $tmp = $_FILES['attachments']['tmp_name'][$i] ?? '';
                $mime = $finfo ? finfo_file($finfo, $tmp) : (function_exists('mime_content_type') ? mime_content_type($tmp) : '');
                if (!$mime || !isset($allowed_mimes[$mime])) {
                    $msg = "Chi ho tro anh JPEG/PNG/GIF/WEBP.";
                    break;
                }
                $files[] = [
                    'tmp' => $tmp,
                    'name' => $_FILES['attachments']['name'][$i] ?? 'upload',
                    'mime' => $mime,
                    'size' => $size,
                    'ext' => $allowed_mimes[$mime],
                ];
            }
            if ($finfo) {
                finfo_close($finfo);
            }
        }
    }

    if ($tieu_de === "" || $mo_ta === "" || $phan_loai_id <= 0) {
        $msg = "Vui lòng nhập đủ thông tin!";
    } elseif ($msg === "") {
        $upload_dir = __DIR__ . "/uploads/tickets";
        $web_dir = "uploads/tickets";
        $saved_files = [];

        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("INSERT INTO tickets(tieu_de, mo_ta, phan_loai_id, user_id) VALUES (?,?,?,?)");
            $stmt->execute([$tieu_de, $mo_ta, $phan_loai_id, current_user()['id']]);
            $ticket_id = (int)$pdo->lastInsertId();

            if ($files) {
                $ins = $pdo->prepare("
                    INSERT INTO ticket_attachments(ticket_id, file_path, original_name, uploaded_by, mime_type, file_size)
                    VALUES (?,?,?,?,?,?)
                ");
                foreach ($files as $file) {
                    $name = bin2hex(random_bytes(16)) . "." . $file['ext'];
                    $target = $upload_dir . "/" . $name;
                    if (!move_uploaded_file($file['tmp'], $target)) {
                        throw new RuntimeException("Khong the luu file.");
                    }
                    $saved_files[] = $target;
                    $ins->execute([
                        $ticket_id,
                        $web_dir . "/" . $name,
                        $file['name'],
                        current_user()['id'],
                        $file['mime'],
                        $file['size'],
                    ]);
                }
            }

            $pdo->commit();
            header("Location: tickets_my.php");
            exit;
        } catch (Throwable $e) {
            $pdo->rollBack();
            foreach ($saved_files as $path) {
                if (is_file($path)) {
                    @unlink($path);
                }
            }
            $msg = "Khong the luu ticket. Vui long thu lai.";
        }
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

    <form method="post" class="vstack gap-3" enctype="multipart/form-data">
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

      <div>
        <label class="form-label">Anh dinh kem</label>
        <input type="file" name="attachments[]" class="form-control" accept="image/*" multiple>
        <div class="form-text">Toi da 5 file, 2MB moi file.</div>
      </div>

      <button class="btn btn-dark">Gửi yêu cầu</button>
      <a class="btn btn-outline-secondary" href="dashboard.php">Quay lại</a>
    </form>
  </div>
</div>

<?php include __DIR__ . "/inc/footer.php"; ?>
