<?php
// Trang đăng nhập - Cổng thông tin sinh viên - Trường Đại học HNDA
session_start();
require_once __DIR__ . '/config/database.php';

$loaiTaiKhoan = 'sinhvien';
if (isset($_POST['loai_tk'])) {
    $loaiTaiKhoan = $_POST['loai_tk'];
} elseif (isset($_GET['loai_tk'])) {
    $loaiTaiKhoan = $_GET['loai_tk'];
}
if (!in_array($loaiTaiKhoan, ['sinhvien', 'giangvien'], true)) {
    $loaiTaiKhoan = 'sinhvien';
}
$loi = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tai_khoan'])) {
    $taiKhoan = trim($_POST['tai_khoan'] ?? '');
    $matKhau  = trim($_POST['mat_khau'] ?? '');

    // ===== VALIDATE dữ liệu đầu vào =====
    if ($taiKhoan === '' || $matKhau === '') {
        $loi = 'Vui lòng nhập đầy đủ mã sinh viên/email và mật khẩu.';
    } elseif (mb_strlen($taiKhoan) > 150) {
        $loi = 'Mã sinh viên/Email không hợp lệ.';
    } elseif (mb_strlen($matKhau) < 6) {
        $loi = 'Mật khẩu phải có ít nhất 6 ký tự.';
    } else {
        // Chọn bảng theo loại tài khoản
        $bang     = $loaiTaiKhoan === 'giangvien' ? 'giang_vien' : 'sinh_vien';
        $cotMa    = $loaiTaiKhoan === 'giangvien' ? 'ma_gv' : 'ma_sv';

        // ===== Truy vấn bằng prepared statement, chống SQL injection =====
        $sql = "SELECT * FROM {$bang} WHERE ({$cotMa} = :tk OR email = :tk) AND trang_thai = 1 LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['tk' => $taiKhoan]);
        $nguoiDung = $stmt->fetch();

        if (!$nguoiDung) {
            $loi = 'Tài khoản không tồn tại hoặc đã bị khóa.';
        } elseif (!password_verify($matKhau, $nguoiDung['mat_khau'])) {
            $loi = 'Mật khẩu không chính xác.';
        } else {
            // Đăng nhập thành công -> lưu session
            $_SESSION['user_id']   = $nguoiDung['id'];
            $_SESSION['loai_tk']   = $loaiTaiKhoan;
            $_SESSION['ho_ten']    = $nguoiDung['ho_ten'];

            header('Location: trang-chu.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Đăng nhập - Cổng thông tin sinh viên | Trường Đại học HNDA</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>

<main class="page">

    <section class="left-panel">
        <header class="topbar">
            <div class="logo-box">
                <div class="logo-icon">A</div>
                <div class="logo-text">
                    <span class="logo-title">TRƯỜNG ĐẠI HỌC HNDA</span>
                    <span class="logo-subtitle">CỔNG THÔNG TIN SINH VIÊN</span>
                </div>
            </div>
        </header>

        <nav class="navbar">
            <ul>
                <li><a href="#" class="active">TRANG CHỦ</a></li>
                <li><a href="#">THÔNG BÁO</a></li>
                <li><a href="#">DANH SÁCH HỌC PHẦN</a></li>
                <li><a href="#">ĐĂNG KÝ HỌC PHẦN</a></li>
                <li><a href="#">LỊCH HỌC</a></li>
            </ul>
        </nav>

        <div class="left-content">
            <h1>Xin chào đến với<br>Hệ thống quản lý khóa học và đăng ký học phần</h1>
            <p class="lead">
                Nền tảng giúp sinh viên dễ dàng tra cứu thông tin, đăng ký học phần, xem lịch học và
                quản lý quá trình học tập một cách hiệu quả.
            </p>

            <div class="cta-row">
                <a href="#" class="btn btn-primary">ĐĂNG KÝ HỌC PHẦN</a>
                <a href="#" class="btn btn-outline">TRA CỨU HỌC PHẦN</a>
            </div>

            <div class="illustration">
                <img src="assets/illustration.png" alt="Sinh viên học trực tuyến">
            </div>
        </div>
    </section>

    <section class="right-panel">
        <div class="login-card">
            <h2>ĐĂNG NHẬP</h2>

            <div class="tab-switch">
                <a href="?loai_tk=sinhvien" class="tab-btn <?php echo $loaiTaiKhoan === 'sinhvien' ? 'tab-active' : ''; ?>">
                    <span class="tab-icon">&#128100;</span> Sinh viên
                </a>
                <a href="?loai_tk=giangvien" class="tab-btn <?php echo $loaiTaiKhoan === 'giangvien' ? 'tab-active' : ''; ?>">
                    <span class="tab-icon">&#127891;</span> Giảng viên
                </a>
            </div>

            <?php if ($loi): ?>
                <div class="form-error"><?php echo htmlspecialchars($loi); ?></div>
            <?php endif; ?>

            <form method="POST" action="index.php" class="login-form">
                <input type="hidden" name="loai_tk" value="<?php echo htmlspecialchars($loaiTaiKhoan); ?>">

                <label for="tai_khoan"><?php echo $loaiTaiKhoan === 'giangvien' ? 'Mã giảng viên/ Email' : 'Mã sinh viên/ Email'; ?></label>
                <input type="text" id="tai_khoan" name="tai_khoan" placeholder="<?php echo $loaiTaiKhoan === 'giangvien' ? 'Nhập mã giảng viên' : 'Nhập mã sinh viên'; ?>" autocomplete="username" value="<?php echo htmlspecialchars($_POST['tai_khoan'] ?? ''); ?>">

                <label for="mat_khau">Mật khẩu</label>
                <input type="password" id="mat_khau" name="mat_khau" placeholder="Nhập mật khẩu" autocomplete="current-password">

                <a href="quen-mat-khau.php" class="forgot-link">Quên mật khẩu ?</a>

                <button type="submit" class="btn-login">ĐĂNG NHẬP</button>
            </form>
        </div>
    </section>

</main>


</body>
</html>