<?php
/**
 * Xử lý thêm học viên và nhập điểm từ Modal "+ Nhập điểm"
 * Kiểm tra dữ liệu server-side đầy đủ theo Hình 3.
 */
session_start();
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/queries.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php');
    exit;
}

$maLop = $_POST['ma_lop'] ?? 'CS101';

// 1. Thực hiện Server-side Validation
$validation = validateDiemForm($_POST);

if (!$validation['is_valid']) {
    // Giữ lại dữ liệu người dùng đã nhập và mảng lỗi
    $_SESSION['form_errors'] = $validation['errors'];
    $_SESSION['form_data'] = [
        'mssv' => $_POST['mssv'] ?? '',
        'ho_ten' => $_POST['ho_ten'] ?? '',
        'diem_cc' => $_POST['diem_cc'] ?? '',
        'diem_gk' => $_POST['diem_gk'] ?? '',
        'diem_ck' => $_POST['diem_ck'] ?? ''
    ];
    $_SESSION['open_modal'] = true;
    $_SESSION['flash_error'] = 'Vui lòng kiểm tra và sửa các lỗi nhập liệu dưới đây.';
    header('Location: ../index.php?ma_lop=' . urlencode($maLop));
    exit;
}

$clean = $validation['clean'];

// 2. Thêm vào CSDL
$res = themHocVienVoiDiem(
    $maLop, 
    $clean['mssv'], 
    $clean['ho_ten'], 
    $clean['diem_cc'], 
    $clean['diem_gk'], 
    $clean['diem_ck']
);

if ($res) {
    unset($_SESSION['form_errors'], $_SESSION['form_data'], $_SESSION['open_modal']);
    $_SESSION['flash_success'] = "Đã nhập điểm thành công cho học viên " . e($clean['ho_ten']) . " (" . e($clean['mssv']) . ")!";
} else {
    $_SESSION['flash_error'] = "Không thể lưu điểm vào CSDL SQL Server!";
}

header('Location: ../index.php?ma_lop=' . urlencode($maLop));
exit;
