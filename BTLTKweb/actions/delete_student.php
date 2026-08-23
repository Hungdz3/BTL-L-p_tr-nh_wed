<?php
/**
 * Xử lý Xóa học viên khỏi lớp (GET)
 * File: D:\BTLTKweb\actions\delete_student.php
 */
session_start();
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/queries.php';

$mssv = $_GET['mssv'] ?? '';
$maLop = $_GET['ma_lop'] ?? 'CS101';

if (!empty($mssv)) {
    if (xoaHocVienKhoiLop($maLop, $mssv)) {
        $_SESSION['flash_success'] = "Đã xóa thành công học viên MSSV $mssv khỏi lớp học!";
    } else {
        $_SESSION['flash_error'] = "Không thể xóa học viên khỏi CSDL!";
    }
}

header('Location: ../index.php?ma_lop=' . urlencode($maLop));
exit;
