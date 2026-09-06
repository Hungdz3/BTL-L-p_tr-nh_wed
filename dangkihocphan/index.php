<?php
/**
 * Trang Điều Hướng Hệ Thống (Root Router)
 * Kiểm tra trạng thái Đăng nhập để tự động chuyển hướng đúng phân vùng
 */
session_start();

// Nếu chưa đăng nhập -> Chuyển về trang Login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Nếu đã đăng nhập -> Chuyển hướng theo Vai trò (Role)
$loaiTK = $_SESSION['loai_tk'] ?? 'sinhvien';

if ($loaiTK === 'admin') {
    header('Location: admin/index.php');
    exit;
} elseif ($loaiTK === 'giangvien') {
    header('Location: giang-vien/index.php');
    exit;
} else {
    header('Location: sinh-vien/trang-chu.php');
    exit;
}
