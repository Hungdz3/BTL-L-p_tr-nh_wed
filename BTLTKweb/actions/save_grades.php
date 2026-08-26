<?php
/**
 * Xử lý Lưu điểm hàng loạt từ bảng điểm (POST)
 */
session_start();
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/queries.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php');
    exit;
}

$maLop = $_POST['ma_lop'] ?? 'CS101';
$page = $_POST['page'] ?? 1;
$search = $_POST['search'] ?? '';
$grades = $_POST['grades'] ?? [];

$errors = [];
$successCount = 0;

foreach ($grades as $mssv => $scoreData) {
    $diemCC = trim($scoreData['cc'] ?? '0');
    $diemGK = trim($scoreData['gk'] ?? '0');
    $diemCK = trim($scoreData['ck'] ?? '0');

    // Validate server-side cho từng học viên
    if (!is_numeric($diemCC) || floatval($diemCC) < 0 || floatval($diemCC) > 10 ||
        !is_numeric($diemGK) || floatval($diemGK) < 0 || floatval($diemGK) > 10 ||
        !is_numeric($diemCK) || floatval($diemCK) < 0 || floatval($diemCK) > 10) {
        $errors[] = "Học viên MSSV $mssv có điểm không hợp lệ (Điểm phải từ 0.0 đến 10.0).";
        continue;
    }

    $cc = round(floatval($diemCC), 1);
    $gk = round(floatval($diemGK), 1);
    $ck = round(floatval($diemCK), 1);

    if (luuCapNhatDiem($maLop, $mssv, $cc, $gk, $ck)) {
        $successCount++;
    }
}

if (!empty($errors)) {
    $_SESSION['flash_error'] = implode('<br>', array_map('e', $errors));
}

if ($successCount > 0) {
    $_SESSION['flash_success'] = "Đã lưu điểm thành công cho $successCount học viên!";
}

header('Location: ../index.php?ma_lop=' . urlencode($maLop) . '&page=' . urlencode($page) . '&search=' . urlencode($search));
exit;
