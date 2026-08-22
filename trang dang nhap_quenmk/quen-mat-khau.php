<?php
// Trang khôi phục mật khẩu - Cổng thông tin sinh viên - Trường Đại học HNDA
require_once __DIR__ . '/config/database.php';

// Xác định loại tài khoản đang thao tác (sinh viên hoặc giảng viên) để đổi label cho khớp
$loaiTaiKhoan = $_POST['loai_tk'] ?? $_GET['loai_tk'] ?? 'sinhvien';
if (!in_array($loaiTaiKhoan, ['sinhvien', 'giangvien'], true)) {
    $loaiTaiKhoan = 'sinhvien';
}
$nhanMa = $loaiTaiKhoan === 'giangvien' ? 'Mã giảng viên' : 'Mã sinh viên';

$loi = '';
$thanhCong = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $maSV     = trim($_POST['ma_sv'] ?? '');
    $hoTen    = trim($_POST['ho_ten'] ?? '');
    $ngaySinh = trim($_POST['ngay_sinh'] ?? '');
    $soDT     = trim($_POST['so_dt'] ?? '');

    // ===== VALIDATE dữ liệu đầu vào =====
    if ($maSV === '' || $hoTen === '' || $ngaySinh === '' || $soDT === '') {
        $loi = 'Vui lòng nhập đầy đủ thông tin để khôi phục mật khẩu.';
    } elseif (!preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $ngaySinh)) {
        $loi = 'Ngày sinh không đúng định dạng DD/MM/YYYY.';
    } elseif (!preg_match('/^[0-9]{9,11}$/', $soDT)) {
        $loi = 'Số điện thoại không hợp lệ.';
    } else {
        // Chuyển DD/MM/YYYY -> YYYY-MM-DD để so khớp với cột DATE trong CSDL
        [$ngay, $thang, $nam] = explode('/', $ngaySinh);
        $ngaySinhSQL = "{$nam}-{$thang}-{$ngay}";

        // ===== Kiểm tra khớp thông tin trong CSDL (thử cả 2 bảng sinh viên / giảng viên) =====
        $tim = null;
        $bang = null;
        foreach (['sinh_vien' => 'ma_sv', 'giang_vien' => 'ma_gv'] as $tenBang => $cotMa) {
            $sql = "SELECT * FROM {$tenBang}
                    WHERE ({$cotMa} = :ma OR email = :ma)
                      AND ho_ten = :ho_ten
                      AND ngay_sinh = :ngay_sinh
                      AND so_dt = :so_dt
                    LIMIT 1";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'ma'        => $maSV,
                'ho_ten'    => $hoTen,
                'ngay_sinh' => $ngaySinhSQL,
                'so_dt'     => $soDT,
            ]);
            $ketQua = $stmt->fetch();
            if ($ketQua) {
                $tim = $ketQua;
                $bang = $tenBang;
                break;
            }
        }

        if (!$tim) {
            $loi = 'Thông tin không khớp với dữ liệu trong hệ thống. Vui lòng kiểm tra lại.';
        } else {
            // ===== Tạo mật khẩu mới ngẫu nhiên và cập nhật vào CSDL =====
            $matKhauMoi = bin2hex(random_bytes(4)); // ví dụ: "a1b2c3d4"
            $hashMoi = password_hash($matKhauMoi, PASSWORD_DEFAULT);

            $capNhat = $pdo->prepare("UPDATE {$bang} SET mat_khau = :mk WHERE id = :id");
            $capNhat->execute(['mk' => $hashMoi, 'id' => $tim['id']]);

            // TODO: gửi $matKhauMoi cho người dùng qua email/SMS thay vì hiển thị trực tiếp
            $thanhCong = "Mật khẩu mới của bạn là: {$matKhauMoi} (hãy đổi mật khẩu ngay sau khi đăng nhập).";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Khôi phục mật khẩu | Trường Đại học HNDA</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body class="body-recover">

<main class="recover-page">
    <div class="recover-card">
        <div class="recover-logo">
            <div class="logo-icon">A</div>
        </div>
        <h1 class="recover-school">TRƯỜNG ĐẠI HỌC HNDA</h1>
        <h2 class="recover-title">KHÔI PHỤC MẬT KHẨU</h2>

        <?php if ($loi): ?>
            <div class="form-error"><?php echo htmlspecialchars($loi); ?></div>
        <?php endif; ?>

        <?php if ($thanhCong): ?>
            <div class="form-success"><?php echo htmlspecialchars($thanhCong); ?></div>
        <?php endif; ?>

        <form method="POST" action="quen-mat-khau.php" class="recover-form">
            <input type="hidden" name="loai_tk" value="<?php echo htmlspecialchars($loaiTaiKhoan); ?>">

            <label for="ma_sv"><?php echo $nhanMa; ?>/ Email</label>
            <input type="text" id="ma_sv" name="ma_sv" placeholder="Nhập <?php echo mb_strtolower($nhanMa); ?>" value="<?php echo htmlspecialchars($_POST['ma_sv'] ?? ''); ?>">

            <label for="ho_ten">Họ và tên</label>
            <input type="text" id="ho_ten" name="ho_ten" placeholder="Nhập họ và tên" value="<?php echo htmlspecialchars($_POST['ho_ten'] ?? ''); ?>">

            <label for="ngay_sinh">Ngày tháng năm sinh</label>
            <input type="text" id="ngay_sinh" name="ngay_sinh" placeholder="DD/MM/YYYY" value="<?php echo htmlspecialchars($_POST['ngay_sinh'] ?? ''); ?>">

            <label for="so_dt">Số điện thoại ( đăng ký trên hệ thông)</label>
            <input type="text" id="so_dt" name="so_dt" placeholder="Nhập SĐT đã đăng ký với nhà trường" value="<?php echo htmlspecialchars($_POST['so_dt'] ?? ''); ?>">

            <div class="recover-actions">
                <button type="submit" class="btn-recover">KHÔI PHỤC MẬT KHẨU</button>
                <a href="index.php?loai_tk=<?php echo htmlspecialchars($loaiTaiKhoan); ?>" class="btn-back-login">ĐĂNG NHẬP</a>
            </div>
        </form>
    </div>
</main>

</body>
</html>