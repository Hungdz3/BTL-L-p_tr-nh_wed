<?php
/**
 * Xử lý Xóa học viên khỏi lớp (POST)
 * File: D:\BTLTKweb\actions\delete_student.php
 */
session_start();
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/queries.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php');
    exit;
}

$mssv = $_POST['mssv'] ?? '';
$maLop = $_POST['ma_lop'] ?? 'CS101';

if (!empty($mssv)) {
    if (xoaHocVienKhoiLop($maLop, $mssv)) {
        $_SESSION['flash_success'] = "Đã xóa thành công học viên MSSV $mssv khỏi lớp học!";
    } else {
        $_SESSION['flash_error'] = "Không thể xóa học viên khỏi CSDL!";
    }
}

header('Location: ../index.php?ma_lop=' . urlencode($maLop));
exit;
