<?php
declare(strict_types=1);
require_once __DIR__ . "/inc/db.php";
require_once __DIR__ . "/inc/auth.php";
enforce_route_access();

$role = current_user()['role'];
$q = trim($_GET['q'] ?? '');
$status = $_GET['status'] ?? '';
$status_options = ['Mới','Đang xử lý','Đã hoàn thành','Từ chối'];
if (!in_array($status, $status_options, true)) {
    $status = '';
}

$where = [];
$params = [];
if ($role === 'user') {
    $where[] = "t.user_id = ?";
    $params[] = current_user()['id'];
} elseif ($role === 'technician') {
    $where[] = "t.assigned_to = ?";
    $params[] = current_user()['id'];
}

if ($q !== '') {
    $like = '%' . $q . '%';
    $where[] = "(t.tieu_de LIKE ? OR t.mo_ta LIKE ? OR p.ten LIKE ? OR u.full_name LIKE ? OR tech.full_name LIKE ?)";
    array_push($params, $like, $like, $like, $like, $like);
}
if ($status !== '') {
    $where[] = "t.trang_thai = ?";
    $params[] = $status;
}

$sql = "
  SELECT t.id, t.tieu_de, t.mo_ta, p.ten AS phan_loai,
         u.full_name AS nguoi_tao,
         tech.full_name AS nguoi_xu_ly,
         t.trang_thai, t.ly_do_tu_choi, t.created_at
  FROM tickets t
  JOIN phan_loai p ON p.id = t.phan_loai_id
  JOIN users u ON u.id = t.user_id
  LEFT JOIN users tech ON tech.id = t.assigned_to
";
if ($where) {
    $sql .= " WHERE " . implode(" AND ", $where);
}
$sql .= " ORDER BY t.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$tickets = $stmt->fetchAll();

$format = $_GET['format'] ?? 'excel';
if ($format === 'pdf') {
    export_pdf($tickets);
} else {
    export_excel($tickets);
}

function export_excel(array $tickets): void {
    $name = "tickets_" . date('Ymd_His') . ".xls";
    header('Content-Type: application/vnd.ms-excel; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $name . '"');
    echo "\xEF\xBB\xBF";

    $out = fopen('php://output', 'w');
    fputcsv($out, ['ID', 'Tieu de', 'Phan loai', 'Nguoi tao', 'Nguoi xu ly', 'Trang thai', 'Ly do tu choi', 'Ngay tao']);
    foreach ($tickets as $t) {
        fputcsv($out, [
            $t['id'],
            $t['tieu_de'],
            $t['phan_loai'],
            $t['nguoi_tao'],
            $t['nguoi_xu_ly'] ?? 'Chua gan',
            $t['trang_thai'],
            $t['ly_do_tu_choi'] ?? '',
            $t['created_at'],
        ]);
    }
    fclose($out);
    exit;
}

function export_pdf(array $tickets): void {
    $lines = [];
    $lines[] = "TICKETS EXPORT";
    $lines[] = "Generated: " . date('Y-m-d H:i');
    $lines[] = "Total: " . count($tickets);
    $lines[] = str_repeat('-', 100);
    $widths = [6, 24, 14, 16, 16, 12, 24, 19];
    $lines[] = format_row(['ID', 'Tieu de', 'Phan loai', 'Nguoi tao', 'Nguoi xu ly', 'Trang thai', 'Ly do tu choi', 'Ngay tao'], $widths);
    $lines[] = str_repeat('-', 100);
    foreach ($tickets as $t) {
        $lines[] = format_row([
            (string)$t['id'],
            (string)$t['tieu_de'],
            (string)$t['phan_loai'],
            (string)$t['nguoi_tao'],
            (string)($t['nguoi_xu_ly'] ?? 'Chua gan'),
            (string)$t['trang_thai'],
            (string)($t['ly_do_tu_choi'] ?? ''),
            (string)$t['created_at'],
        ], $widths);
    }

    $pdf = build_pdf($lines);
    $name = "tickets_" . date('Ymd_His') . ".pdf";
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $name . '"');
    echo $pdf;
    exit;
}

function format_row(array $cols, array $widths): string {
    $out = [];
    foreach ($cols as $i => $col) {
        $width = $widths[$i] ?? 20;
        $text = normalize_pdf_text($col);
        if (strlen($text) > $width) {
            $text = substr($text, 0, max(0, $width - 3)) . '...';
        }
        $out[] = str_pad($text, $width);
    }
    return rtrim(implode(' ', $out));
}

function normalize_pdf_text(string $text): string {
    $text = preg_replace('/\s+/', ' ', trim($text));
    if (function_exists('iconv')) {
        $converted = @iconv('UTF-8', 'windows-1252//TRANSLIT//IGNORE', $text);
        if ($converted !== false) {
            $text = $converted;
        }
    } else {
        $text = utf8_decode($text);
    }
    $text = preg_replace('/[^\x20-\x7E]/', '', $text);
    return $text;
}

function build_pdf(array $lines): string {
    $max_lines = 46;
    if (count($lines) > $max_lines) {
        $lines = array_slice($lines, 0, $max_lines - 1);
        $lines[] = "...";
    }

    $content = "BT\n/F1 10 Tf\n12 TL\n50 800 Td\n";
    $last = count($lines) - 1;
    foreach ($lines as $i => $line) {
        $content .= "(" . pdf_escape($line) . ") Tj\n";
        if ($i < $last) {
            $content .= "T*\n";
        }
    }
    $content .= "ET";

    $objects = [];
    $objects[] = "1 0 obj << /Type /Catalog /Pages 2 0 R >> endobj\n";
    $objects[] = "2 0 obj << /Type /Pages /Kids [3 0 R] /Count 1 >> endobj\n";
    $objects[] = "3 0 obj << /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Contents 5 0 R /Resources << /Font << /F1 4 0 R >> >> >> endobj\n";
    $objects[] = "4 0 obj << /Type /Font /Subtype /Type1 /BaseFont /Helvetica >> endobj\n";
    $objects[] = "5 0 obj << /Length " . strlen($content) . " >> stream\n" . $content . "\nendstream\nendobj\n";

    $pdf = "%PDF-1.4\n";
    $offsets = [];
    foreach ($objects as $obj) {
        $offsets[] = strlen($pdf);
        $pdf .= $obj;
    }

    $startxref = strlen($pdf);
    $pdf .= "xref\n0 " . (count($objects) + 1) . "\n";
    $pdf .= "0000000000 65535 f \n";
    foreach ($offsets as $offset) {
        $pdf .= sprintf("%010d 00000 n \n", $offset);
    }
    $pdf .= "trailer << /Size " . (count($objects) + 1) . " /Root 1 0 R >>\n";
    $pdf .= "startxref\n" . $startxref . "\n%%EOF";

    return $pdf;
}

function pdf_escape(string $text): string {
    $text = normalize_pdf_text($text);
    $text = str_replace("\\", "\\\\", $text);
    $text = str_replace("(", "\\(", $text);
    $text = str_replace(")", "\\)", $text);
    return $text;
}
