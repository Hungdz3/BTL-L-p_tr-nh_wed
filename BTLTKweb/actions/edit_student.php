<?php
/**
 * Xử lý Chỉnh sửa Họ tên và Điểm số của Học viên từ Popup (POST)
 * File: D:\BTLTKweb\actions\edit_student.php
 */
session_start();
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/queries.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php');
    exit;
}

$maLop = $_POST['ma_lop'] ?? 'CS101';
$validation = validateDiemForm($_POST);

if (!$validation['is_valid']) {
    $_SESSION['flash_error'] = implode('<br>', array_map('e', $validation['errors']));
    header('Location: ../index.php?ma_lop=' . urlencode($maLop));
    exit;
}

$clean = $validation['clean'];

if (capNhatThongTinHocVienVoiDiem($maLop, $clean['mssv'], $clean['ho_ten'], $clean['diem_cc'], $clean['diem_gk'], $clean['diem_ck'])) {
    $_SESSION['flash_success'] = "Đã cập nhật thành công thông tin & điểm học viên " . e($clean['ho_ten']) . " (MSSV: " . e($clean['mssv']) . ")!";
} else {
    $_SESSION['flash_error'] = "Không thể cập nhật thông tin học viên trong CSDL!";
}

header('Location: ../index.php?ma_lop=' . urlencode($maLop));
exit;
