<?php
// 1. Bắt buộc phải start session trước, để PHP biết session hiện tại là session nào
session_start();

// 2. Xóa toàn bộ biến trong session (an toàn hơn là chỉ destroy)
$_SESSION = [];

// 3. Xóa cookie session ở trình duyệt (nếu dùng cookie-based session, thường là mặc định)
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

// 4. Hủy session ở server
session_destroy();

// 5. Chuyển hướng về trang đăng nhập
header('Location: index.php');
exit;