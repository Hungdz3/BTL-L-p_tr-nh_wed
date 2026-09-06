<?php
// login.php - Cổng đăng nhập hợp nhất cho Sinh viên, Giảng viên và Admin
session_start();
require_once 'config/db.php';

$loi = '';
$taiKhoanPost = '';
$loaiTKPost = 'sinhvien';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tai_khoan'])) {
    $taiKhoanPost = trim($_POST['tai_khoan'] ?? '');
    $matKhau  = trim($_POST['mat_khau'] ?? '');
    $loaiTKPost = trim($_POST['loai_tk'] ?? 'sinhvien');

    if ($taiKhoanPost === '' || $matKhau === '') {
        $loi = 'Vui lòng nhập đầy đủ tài khoản/email và mật khẩu.';
    } elseif (mb_strlen($matKhau) < 6) {
        $loi = 'Mật khẩu phải có ít nhất 6 ký tự.';
    } else {
        try {
            $db = getDB();
            
            // Truy vấn bảng tài khoản kết hợp vai trò
            $stmt = $db->prepare("
                SELECT tk.*, vt.ten AS ten_vai_tro 
                FROM tai_khoan tk
                JOIN vai_tro vt ON tk.vai_tro_id = vt.id
                WHERE (tk.username = :tk OR tk.email = :tk) AND tk.trang_thai = 'HOAT_DONG'
                LIMIT 1
            ");
            $stmt->execute([':tk' => $taiKhoanPost]);
            $user = $stmt->fetch();
            
            if (!$user) {
                $loi = 'Tài khoản không tồn tại hoặc đã bị khóa.';
            } elseif (!password_verify($matKhau, $user['password_hash'])) {
                $loi = 'Mật khẩu không chính xác.';
            } else {
                $vaiTro = strtoupper($user['ten_vai_tro']);
                
                if (str_contains($vaiTro, 'ADMIN')) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['loai_tk'] = 'admin';
                    $_SESSION['ho_ten'] = 'Administrator';
                    
                    header('Location: admin/index.php');
                    exit;
                } elseif (str_contains($vaiTro, 'GIANG') || str_contains($vaiTro, 'TEACHER') || str_contains($vaiTro, 'GIAO')) {
                    $stmt_gv = $db->prepare("SELECT * FROM giao_vien WHERE tai_khoan_id = :tk_id LIMIT 1");
                    $stmt_gv->execute([':tk_id' => $user['id']]);
                    $gv = $stmt_gv->fetch();
                    
                    if (!$gv) {
                        $loi = 'Không tìm thấy hồ sơ giảng viên của tài khoản này.';
                    } else {
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['ma_gv'] = $gv['ma_gv'];
                        $_SESSION['ho_ten'] = $gv['ho_ten'];
                        $_SESSION['loai_tk'] = 'giangvien';
                        
                        header('Location: giang-vien/index.php'); 
                        exit;
                    }
                } else {
                    $stmt_sv = $db->prepare("
                        SELECT sv.*, l.ten_lop 
                        FROM sinh_vien sv
                        LEFT JOIN lop_sinh_vien l ON sv.ma_lop_sv = l.ma_lop_sv
                        WHERE sv.tai_khoan_id = :tk_id LIMIT 1
                    ");
                    $stmt_sv->execute([':tk_id' => $user['id']]);
                    $sv = $stmt_sv->fetch();
                    
                    if (!$sv) {
                        $loi = 'Không tìm thấy hồ sơ sinh viên của tài khoản này.';
                    } else {
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['ma_sv'] = $sv['ma_sv'];
                        $_SESSION['ho_ten'] = $sv['ho_ten'];
                        $_SESSION['ten_lop'] = $sv['ten_lop'];
                        $_SESSION['loai_tk'] = 'sinhvien';
                        
                        header('Location: sinh-vien/trang-chu.php');
                        exit;
                    }
                }
            }
        } catch (Exception $e) {
            $loi = 'Lỗi kết nối cơ sở dữ liệu: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Đăng nhập cổng thông tin | Trường Đại học HNDA</title>
<link rel="icon" type="image/svg+xml" href="assets/images/favicon.svg">
<style>
    /* ===== Reset & base ===== */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Segoe UI', Arial, Helvetica, sans-serif;
        background-color: #ffffff;
        color: #1e2a4a;
    }

    a {
        text-decoration: none;
        color: inherit;
    }

    ul {
        list-style: none;
    }

    img {
        max-width: 100%;
        display: block;
    }

    /* ===== Page layout ===== */
    .page {
        display: flex;
        min-height: 100vh;
    }

    .left-panel {
        flex: 1 1 62%;
        display: flex;
        flex-direction: column;
        background-color: #eef3fc;
    }

    .right-panel {
        flex: 0 0 38%;
        background-color: #1E3A8A;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 40px;
    }

    /* ===== Top bar (logo) ===== */
    .topbar {
        background-color: #ffffff;
        padding: 18px 40px;
    }

    .logo-box {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .logo-icon {
        width: 40px;
        height: 40px;
        background-color: #1a3a6b;
        color: #ffffff;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 18px;
    }

    .logo-text {
        display: flex;
        flex-direction: column;
        line-height: 1.3;
    }

    .logo-title {
        font-weight: 800;
        font-size: 16px;
        color: #17296b;
        letter-spacing: 0.3px;
    }

    /* ===== Left content ===== */
    .left-content {
        flex: 1;
        padding: 48px 40px 30px;
    }

    .left-content h1 {
        font-size: 34px;
        font-weight: 800;
        line-height: 1.25;
        color: #17296b;
        max-width: 560px;
    }

    .left-content .lead {
        margin-top: 18px;
        font-size: 15px;
        line-height: 1.6;
        color: #5a6685;
        max-width: 520px;
    }

    .btn {
        padding: 15px 30px;
        border-radius: 6px;
        font-weight: 700;
        font-size: 14px;
        letter-spacing: 0.3px;
        text-align: center;
    }

    .btn-primary {
        background-color: #17296b;
        color: #ffffff;
    }

    .btn-primary:hover {
        background-color: #101d4d;
    }

    .btn-outline {
        background-color: #ffffff;
        color: #17296b;
        border: 1.5px solid #17296b;
    }

    .btn-outline:hover {
        background-color: #f0f3fc;
    }

    .illustration {
        margin-top: 30px;
        max-width: 560px;
    }

    /* ===== Right panel: login card ===== */
    .login-card {
        width: 100%;
        max-width: 420px;
    }

    .login-card h2 {
        color: #ffffff;
        font-size: 30px;
        font-weight: 800;
        letter-spacing: 0.5px;
        margin-bottom: 26px;
    }

    .tab-switch {
        display: flex;
        gap: 14px;
        margin-bottom: 26px;
    }

    .tab-btn {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 14px 10px;
        border-radius: 8px;
        background-color: transparent;
        border: 1.5px solid rgba(255, 255, 255, 0.35);
        color: #ffffff;
        font-weight: 700;
        font-size: 14px;
        cursor: pointer;
    }

    .tab-icon {
        font-size: 15px;
    }

    .tab-btn.tab-active {
        background-color: #ffffff;
        color: #17296b;
        border-color: #ffffff;
    }

    .login-form {
        display: flex;
        flex-direction: column;
    }

    .login-form label {
        color: #dfe4f5;
        font-size: 14px;
        margin-bottom: 8px;
        margin-top: 18px;
    }

    .login-form label:first-of-type {
        margin-top: 0;
    }

    .login-form input {
        padding: 15px 16px;
        border-radius: 8px;
        border: none;
        outline: none;
        font-size: 14px;
        background-color: #ffffff;
        color: #1e2a4a;
    }

    .login-form input::placeholder {
        color: #9aa3bd;
    }

    .login-form input:focus {
        box-shadow: 0 0 0 2px #e14b3d;
    }

    .btn-login {
        margin-top: 26px;
        padding: 16px;
        border: none;
        border-radius: 8px;
        background-color: #ffffff;
        color: #17296b;
        font-weight: 800;
        font-size: 15px;
        letter-spacing: 0.5px;
        cursor: pointer;
    }

    .btn-login:hover {
        background-color: #eef1fb;
    }

    .form-error {
        background-color: rgba(225, 75, 61, 0.15);
        border: 1px solid #e14b3d;
        color: #ffe9e6;
        font-size: 13px;
        padding: 10px 14px;
        border-radius: 6px;
        margin-bottom: 16px;
    }

    /* ===== Responsive ===== */
    @media (max-width: 992px) {
        .page {
            flex-direction: column;
        }

        .left-panel,
        .right-panel {
            flex: 1 1 auto;
        }

        .right-panel {
            padding: 40px 24px;
        }

        .left-content h1 {
            font-size: 28px;
        }
    }

    @media (max-width: 560px) {
        .topbar,
        .left-content {
            padding-left: 20px;
            padding-right: 20px;
        }

        .tab-switch {
            flex-direction: column;
        }
    }
</style>
</head>
<body>

<main class="page">

    <section class="left-panel">
        <header class="topbar">
            <div class="logo-box">
                <div class="logo-icon">A</div>
                <div class="logo-text">
                    <span class="logo-title">TRƯỜNG ĐẠI HỌC HNDA</span>
                </div>
            </div>
        </header>

        <div class="left-content">
            <h1>Xin chào đến với<br>Hệ thống quản lý khóa học và đăng ký học phần</h1>
            <p class="lead">
                Nền tảng giúp sinh viên dễ dàng tra cứu thông tin, đăng ký học phần, xem lịch học và
                quản lý quá trình học tập một cách hiệu quả.
            </p>

            <div class="illustration">
                <img src="assets/illustration.png" alt="Sinh viên học trực tuyến">
            </div>
        </div>
    </section>

    <section class="right-panel">
        <div class="login-card">
            <h2>ĐĂNG NHẬP</h2>

            <?php if ($loi): ?>
                <div class="form-error"><?php echo htmlspecialchars($loi); ?></div>
            <?php endif; ?>

            <form method="POST" action="login.php" class="login-form">
                <label for="tai_khoan" id="input-label">Mã SV / Giảng viên / Email</label>
                <input type="text" id="tai_khoan" name="tai_khoan" placeholder="Nhập mã sinh viên, mã giảng viên hoặc email" autocomplete="username" value="<?php echo htmlspecialchars($taiKhoanPost); ?>" required>

                <label for="mat_khau">Mật khẩu</label>
                <input type="password" id="mat_khau" name="mat_khau" placeholder="Nhập mật khẩu" autocomplete="current-password" required>

                <button type="submit" class="btn-login">ĐĂNG NHẬP</button>
            </form>
        </div>
    </section>

</main>

</body>
</html>