<?php
session_start();

// Khởi tạo mảng danh sách giảng viên trong session nếu chưa có
if (!isset($_SESSION['danh_sach_giang_vien'])) {
    $_SESSION['danh_sach_giang_vien'] = [];
}

// Danh sách khoa hợp lệ
$danh_sach_khoa = [
    'K_SP'          => 'Khoa Sư Phạm',
    'K_KHXH'        => 'Khoa KHXH và Nhân Văn',
    'K_TCNTT'       => 'Khoa Toán - CNTT',
    'K_NN'          => 'Khoa Ngoại Ngữ',
    'K_GDTC-GDQPAN' => 'Khoa GDTC - GDQPAN',
];

// Danh sách chuyên ngành theo từng khoa
$chuyen_nganh_theo_khoa = [
    'K_SP'          => ['Giáo dục Tiểu học', 'Giáo dục Mầm non', 'Sư phạm Toán học', 'Sư phạm Ngữ văn', 'Sư phạm Vật lý'],
    'K_KHXH'        => ['Văn học', 'Lịch sử', 'Việt Nam học', 'Công tác xã hội', 'Đông phương học'],
    'K_TCNTT'       => ['Công nghệ thông tin', 'Khoa học máy tính', 'Sư phạm Toán học', 'Toán ứng dụng', 'An toàn thông tin'],
    'K_NN'          => ['Ngôn ngữ Anh', 'Sư phạm Tiếng Anh', 'Ngôn ngữ Trung Quốc', 'Biên - Phiên dịch'],
    'K_GDTC-GDQPAN' => ['Giáo dục thể chất', 'Giáo dục Quốc phòng - An ninh', 'Huấn luyện thể thao'],
];

$danh_sach_trinh_do_hople = ['cu_nhan', 'thac_si', 'tien_si', 'pgs_gs'];

// ==================== CÁC HÀM TỰ ĐỊNH NGHĨA ====================

/**
 * Xác định khối ngành dựa trên mã khoa.
 */
function xacDinhKhoiNganh(string $maKhoa): string
{
    switch ($maKhoa) {
        case 'K_TCNTT':
            return "Khối Kỹ thuật - Công nghệ";
        case 'K_KHXH':
            return "Khối Khoa học xã hội";
        case 'K_NN':
            return "Khối Ngoại ngữ";
        case 'K_SP':
            return "Khối Sư phạm";
        case 'K_GDTC-GDQPAN':
            return "Khối GDTC - GDQPAN";
        default:
            return "Khối khác";
    }
}

/**
 * Kiểm tra mã giảng viên đã tồn tại trong danh sách hay chưa.
 * (Mã GV đã được chuẩn hóa viết hoa trước khi so sánh nên so sánh trực tiếp.)
 */
function maGvDaTonTai(string $maGv, array $danh_sach): bool
{
    foreach ($danh_sach as $gv) {
        if ($gv['ma_gv'] === $maGv) {
            return true;
        }
    }
    return false;
}

/**
 * Thống kê số lượng giảng viên theo từng khoa.
 */
function thongKeTheoKhoa(array $danh_sach, array $danh_sach_khoa): array
{
    $thong_ke = [];
    foreach ($danh_sach_khoa as $ma_khoa => $ten_khoa) {
        $thong_ke[$ma_khoa] = 0;
    }
    foreach ($danh_sach as $gv) {
        if (isset($thong_ke[$gv['ten_khoa']])) {
            $thong_ke[$gv['ten_khoa']]++;
        }
    }
    return $thong_ke;
}

/**
 * Thống kê số lượng giảng viên theo từng trình độ.
 */
function thongKeTheoTrinhDo(array $danh_sach): array
{
    $thong_ke = ['cu_nhan' => 0, 'thac_si' => 0, 'tien_si' => 0, 'pgs_gs' => 0];
    foreach ($danh_sach as $gv) {
        if (isset($thong_ke[$gv['trinh_do']])) {
            $thong_ke[$gv['trinh_do']]++;
        }
    }
    return $thong_ke;
}

/**
 * Chuyển mã khoa thành tên khoa đầy đủ để hiển thị.
 */
function tenKhoaDayDu(string $maKhoa, array $danh_sach_khoa): string
{
    return $danh_sach_khoa[$maKhoa] ?? "Không xác định";
}

/**
 * Chuẩn hóa/định dạng nhãn trình độ để hiển thị.
 */
function formatTrinhDo(string $maTrinhDo): string
{
    switch ($maTrinhDo) {
        case 'cu_nhan':
            return "Cử nhân";
        case 'thac_si':
            return "Thạc sĩ";
        case 'tien_si':
            return "Tiến sĩ";
        case 'pgs_gs':
            return "PGS/GS";
        default:
            return "Không xác định";
    }
}

/**
 * ---- MỚI (Buổi 3) ----
 * Chuẩn hóa một chuỗi văn bản thường: cắt khoảng trắng 2 đầu, gộp nhiều
 * khoảng trắng liên tiếp ở giữa thành 1 khoảng trắng.
 */
function chuanHoaChuoi(string $chuoi): string
{
    $chuoi = trim($chuoi);
    $chuoi = preg_replace('/\s+/u', ' ', $chuoi);
    return $chuoi;
}

/**
 * ---- MỚI (Buổi 3) ----
 * Chuẩn hóa mã giảng viên: chuẩn hóa khoảng trắng như trên rồi viết hoa
 * toàn bộ để tránh trùng mã do khác nhau chữ hoa/thường (vd "gv001" và
 * "GV001" sẽ được xem là một).
 */
function chuanHoaMaGV(string $maGv): string
{
    return strtoupper(chuanHoaChuoi($maGv));
}

/**
 * ---- MỚI (Buổi 3) ----
 * Kiểm tra dữ liệu nhập từ form theo TỪNG TRƯỜNG.
 * Trả về mảng dạng ['ten_truong' => 'thông báo lỗi', ...] (rỗng = hợp lệ).
 *
 * Quy tắc kiểm tra:
 *  - ma_gv    : bắt buộc, chỉ gồm chữ IN HOA/số, dài 3-10 ký tự, không trùng.
 *  - ho_ten   : bắt buộc, dài 2-100 ký tự, chỉ gồm chữ cái (có dấu) và khoảng trắng.
 *  - ten_khoa : bắt buộc, phải thuộc danh sách khoa hợp lệ.
 *  - bo_mon   : bắt buộc, phải thuộc đúng danh sách chuyên ngành của khoa đã chọn.
 *  - trinh_do : bắt buộc, phải thuộc danh sách trình độ hợp lệ.
 */
function kiemTraDuLieuNhap(
    array $du_lieu,
    array $danh_sach_khoa,
    array $chuyen_nganh_theo_khoa,
    array $danh_sach_trinh_do_hople,
    array $danh_sach_hien_co
): array {
    $loi = [];

    // ----- Mã giảng viên -----
    $ma_gv = $du_lieu['ma_gv'];
    if ($ma_gv === '') {
        $loi['ma_gv'] = "Mã giảng viên không được để trống.";
    } elseif (!preg_match('/^[A-Z0-9]{3,10}$/', $ma_gv)) {
        $loi['ma_gv'] = "Mã giảng viên chỉ gồm chữ in hoa/số, dài từ 3 đến 10 ký tự (VD: GV001).";
    } elseif (maGvDaTonTai($ma_gv, $danh_sach_hien_co)) {
        $loi['ma_gv'] = "Mã giảng viên \"{$ma_gv}\" đã tồn tại, vui lòng nhập mã khác.";
    }

    // ----- Họ tên -----
    $ho_ten = $du_lieu['ho_ten'];
    $do_dai_ho_ten = mb_strlen($ho_ten);
    if ($ho_ten === '') {
        $loi['ho_ten'] = "Họ tên không được để trống.";
    } elseif ($do_dai_ho_ten < 2 || $do_dai_ho_ten > 100) {
        $loi['ho_ten'] = "Họ tên phải có độ dài từ 2 đến 100 ký tự.";
    } elseif (!preg_match('/^[\p{L}\s]+$/u', $ho_ten)) {
        $loi['ho_ten'] = "Họ tên chỉ được chứa chữ cái và khoảng trắng, không chứa số hay ký tự đặc biệt.";
    }

    // ----- Khoa -----
    if ($du_lieu['ten_khoa'] === '') {
        $loi['ten_khoa'] = "Vui lòng chọn khoa.";
    } elseif (!array_key_exists($du_lieu['ten_khoa'], $danh_sach_khoa)) {
        $loi['ten_khoa'] = "Khoa được chọn không hợp lệ.";
    }

    // ----- Chuyên ngành (phải khớp với khoa đã chọn, chống dữ liệu giả mạo) -----
    if ($du_lieu['bo_mon'] === '') {
        $loi['bo_mon'] = "Vui lòng chọn chuyên ngành (bấm \"Hiện chuyên ngành\" sau khi chọn khoa).";
    } elseif (
        !isset($loi['ten_khoa'])
        && isset($chuyen_nganh_theo_khoa[$du_lieu['ten_khoa']])
        && !in_array($du_lieu['bo_mon'], $chuyen_nganh_theo_khoa[$du_lieu['ten_khoa']], true)
    ) {
        $loi['bo_mon'] = "Chuyên ngành không thuộc khoa đã chọn.";
    }

    // ----- Trình độ -----
    if ($du_lieu['trinh_do'] === '') {
        $loi['trinh_do'] = "Vui lòng chọn trình độ.";
    } elseif (!in_array($du_lieu['trinh_do'], $danh_sach_trinh_do_hople, true)) {
        $loi['trinh_do'] = "Trình độ không hợp lệ.";
    }

    return $loi;
}

/**
 * ---- MỚI (Buổi 3) ----
 * In ra thông báo lỗi (đã escape) ngay dưới 1 trường, nếu trường đó có lỗi.
 */
function inLoiTruong(string $ten_truong, array $thong_bao_loi): void
{
    if (isset($thong_bao_loi[$ten_truong])) {
        echo '<span class="loi">' . htmlspecialchars($thong_bao_loi[$ten_truong]) . '</span>';
    }
}

/**
 * ---- MỚI (Buổi 3) ----
 * Trả về class CSS "co-loi" nếu trường đang có lỗi, để tô viền đỏ cho input.
 */
function classLoiTruong(string $ten_truong, array $thong_bao_loi): string
{
    return isset($thong_bao_loi[$ten_truong]) ? 'co-loi' : '';
}

// ==================== XỬ LÝ FORM (POST) ====================

$thong_bao_loi = [];          // Mảng lỗi theo từng trường: ['ma_gv' => '...', ...]
$thong_bao_thanh_cong = '';

// Đọc dữ liệu người dùng nhập và CHUẨN HÓA NGAY (Buổi 3) trước khi dùng ở bất
// kỳ đâu (validate, hiển thị lại, lưu...).
$gia_tri_form = [
    'ma_gv'    => chuanHoaMaGV($_POST['ma_gv'] ?? ''),
    'ho_ten'   => chuanHoaChuoi($_POST['ho_ten'] ?? ''),
    'ten_khoa' => trim($_POST['ten_khoa'] ?? ''),
    'bo_mon'   => trim($_POST['bo_mon'] ?? ''),
    'trinh_do' => trim($_POST['trinh_do'] ?? ''),
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['hien_chuyen_nganh'])) {
    // Chỉ bấm "Hiện chuyên ngành": không thêm giảng viên, không validate.
    // Chuyên ngành cũ (nếu có) không còn phù hợp với khoa mới nên xóa đi.
    $gia_tri_form['bo_mon'] = '';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['them_giang_vien'])) {

    // Kiểm tra dữ liệu theo từng trường (Buổi 3)
    $thong_bao_loi = kiemTraDuLieuNhap(
        $gia_tri_form,
        $danh_sach_khoa,
        $chuyen_nganh_theo_khoa,
        $danh_sach_trinh_do_hople,
        $_SESSION['danh_sach_giang_vien']
    );

    if (empty($thong_bao_loi)) {
        // Lưu dữ liệu THÔ đã chuẩn hóa (KHÔNG htmlspecialchars ở đây).
        // Việc chống XSS sẽ thực hiện ở bước HIỂN THỊ ra HTML bên dưới.
        $giang_vien_moi = [
            'ma_gv'    => $gia_tri_form['ma_gv'],
            'ho_ten'   => $gia_tri_form['ho_ten'],
            'ten_khoa' => $gia_tri_form['ten_khoa'],
            'bo_mon'   => $gia_tri_form['bo_mon'],
            'trinh_do' => $gia_tri_form['trinh_do'],
        ];

        // Chưa yêu cầu lưu CSDL -> vẫn lưu trong session (Buổi 2 & 3 giống nhau)
        $_SESSION['danh_sach_giang_vien'][] = $giang_vien_moi;

        $thong_bao_thanh_cong = "Đã thêm giảng viên \"" . htmlspecialchars($giang_vien_moi['ho_ten']) . "\" vào danh sách.";

        // Thêm thành công thì làm trống lại form
        $gia_tri_form = [
            'ma_gv' => '', 'ho_ten' => '', 'ten_khoa' => '', 'bo_mon' => '', 'trinh_do' => '',
        ];
    }
    // Nếu có lỗi: KHÔNG làm gì thêm -> $gia_tri_form vẫn giữ nguyên các giá
    // trị (kể cả các trường hợp lệ) để hiển thị lại cho người dùng (Buổi 3).
}

// Xử lý xóa 1 giảng viên khỏi danh sách
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['xoa_index'])) {
    $index = (int)$_POST['xoa_index'];
    if (isset($_SESSION['danh_sach_giang_vien'][$index])) {
        unset($_SESSION['danh_sach_giang_vien'][$index]);
        $_SESSION['danh_sach_giang_vien'] = array_values($_SESSION['danh_sach_giang_vien']);
    }
}

$danh_sach = $_SESSION['danh_sach_giang_vien'];
$tong_so_giang_vien = count($danh_sach);

// Danh sách chuyên ngành sẽ hiện ra tương ứng với khoa đã chọn trên form
$chuyen_nganh_hien_thi = [];
if ($gia_tri_form['ten_khoa'] !== '' && isset($chuyen_nganh_theo_khoa[$gia_tri_form['ten_khoa']])) {
    $chuyen_nganh_hien_thi = $chuyen_nganh_theo_khoa[$gia_tri_form['ten_khoa']];
}

// Thống kê nhanh
$thong_ke_khoa      = thongKeTheoKhoa($danh_sach, $danh_sach_khoa);
$thong_ke_trinh_do  = thongKeTheoTrinhDo($danh_sach);

$khoa_nhieu_nhat = '';
$so_luong_nhieu_nhat = 0;
foreach ($thong_ke_khoa as $ma_khoa => $so_luong) {
    if ($so_luong > $so_luong_nhieu_nhat) {
        $so_luong_nhieu_nhat = $so_luong;
        $khoa_nhieu_nhat = $ma_khoa;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Quản lý Giảng viên - Hệ thống Quản lý Khóa học</title>
<style>
    body { font-family: Arial, sans-serif; }
    .container { max-width: 900px; margin: 0 auto; padding: 20px; }
    .loi { display: block; color: #c0392b; font-size: 0.9em; margin-top: 2px; }
    .co-loi { border: 2px solid #c0392b !important; }
    .thanh-cong { color: #1e7e34; font-weight: bold; }
    .hop-loi-chung { border: 1px solid #c0392b; background: #fdecea; padding: 10px 15px; margin-bottom: 15px; }
    table { border-collapse: collapse; margin-bottom: 20px; }
    th, td { padding: 6px 10px; }
</style>
</head>
<body>
<div class="container">

    <h1>Hệ thống Quản lý Khóa học</h1>
    <p>Chức năng: Quản lý Giảng viên</p>

    <?php if (!empty($thong_bao_loi)): ?>
        <div class="hop-loi-chung">
            <strong>Dữ liệu chưa hợp lệ, vui lòng kiểm tra lại các trường được đánh dấu bên dưới:</strong>
            <ul>
                <?php foreach ($thong_bao_loi as $loi): ?>
                    <li><?= htmlspecialchars($loi) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($thong_bao_thanh_cong !== ''): ?>
        <p class="thanh-cong"><?= $thong_bao_thanh_cong ?></p>
    <?php endif; ?>

    <!-- ==================== FORM NHẬP THÔNG TIN GIẢNG VIÊN ==================== -->
    <h2>Thêm giảng viên mới</h2>
    <form method="POST" action="" novalidate>
        <p>
            <label for="ma_gv">Mã giảng viên</label><br>
            <input type="text" id="ma_gv" name="ma_gv" placeholder="VD: GV001"
                   class="<?= classLoiTruong('ma_gv', $thong_bao_loi) ?>"
                   value="<?= htmlspecialchars($gia_tri_form['ma_gv']) ?>">
            <?php inLoiTruong('ma_gv', $thong_bao_loi); ?>
        </p>
        <p>
            <label for="ho_ten">Họ và tên</label><br>
            <input type="text" id="ho_ten" name="ho_ten" placeholder="VD: Nguyễn Văn A"
                   class="<?= classLoiTruong('ho_ten', $thong_bao_loi) ?>"
                   value="<?= htmlspecialchars($gia_tri_form['ho_ten']) ?>">
            <?php inLoiTruong('ho_ten', $thong_bao_loi); ?>
        </p>
        <p>
            <label for="ten_khoa">Khoa</label><br>
            <select id="ten_khoa" name="ten_khoa" class="<?= classLoiTruong('ten_khoa', $thong_bao_loi) ?>">
                <option value="">-- Chọn Khoa --</option>
                <?php foreach ($danh_sach_khoa as $ma => $ten): ?>
                    <option value="<?= htmlspecialchars($ma) ?>"
                        <?= $gia_tri_form['ten_khoa'] === $ma ? 'selected' : '' ?>>
                        <?= htmlspecialchars($ten) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" name="hien_chuyen_nganh">Hiện chuyên ngành</button>
            <?php inLoiTruong('ten_khoa', $thong_bao_loi); ?>
        </p>
        <p>
            <label for="bo_mon">Chuyên ngành</label><br>
            <?php if (empty($chuyen_nganh_hien_thi)): ?>
                <select id="bo_mon" name="bo_mon" disabled>
                    <option value="">-- Chọn khoa rồi bấm "Hiện chuyên ngành" --</option>
                </select>
            <?php else: ?>
                <select id="bo_mon" name="bo_mon" class="<?= classLoiTruong('bo_mon', $thong_bao_loi) ?>">
                    <option value="">-- Chọn chuyên ngành --</option>
                    <?php foreach ($chuyen_nganh_hien_thi as $chuyen_nganh): ?>
                        <option value="<?= htmlspecialchars($chuyen_nganh) ?>"
                            <?= $gia_tri_form['bo_mon'] === $chuyen_nganh ? 'selected' : '' ?>>
                            <?= htmlspecialchars($chuyen_nganh) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            <?php endif; ?>
            <?php inLoiTruong('bo_mon', $thong_bao_loi); ?>
        </p>
        <p>
            <label for="trinh_do">Trình độ</label><br>
            <select id="trinh_do" name="trinh_do" class="<?= classLoiTruong('trinh_do', $thong_bao_loi) ?>">
                <option value="">-- Chọn trình độ --</option>
                <?php foreach ($danh_sach_trinh_do_hople as $ma_trinh_do): ?>
                    <option value="<?= $ma_trinh_do ?>"
                        <?= $gia_tri_form['trinh_do'] === $ma_trinh_do ? 'selected' : '' ?>>
                        <?= formatTrinhDo($ma_trinh_do) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php inLoiTruong('trinh_do', $thong_bao_loi); ?>
        </p>
        <p>
            <button type="submit" name="them_giang_vien">Thêm giảng viên</button>
        </p>
    </form>

    <!-- ==================== THỐNG KÊ NHANH ==================== -->
    <h2>Thống kê nhanh</h2>
    <p>Tổng số giảng viên: <?= $tong_so_giang_vien ?></p>
    <p>
        Khoa có nhiều giảng viên nhất:
        <?= $khoa_nhieu_nhat !== '' ? htmlspecialchars($danh_sach_khoa[$khoa_nhieu_nhat]) . " ({$so_luong_nhieu_nhat} người)" : '—' ?>
    </p>

    <h3>Theo khoa</h3>
    <table border="1" cellpadding="6" cellspacing="0">
        <tr><th>Khoa</th><th>Số lượng</th><th>Tỉ lệ</th></tr>
        <?php foreach ($danh_sach_khoa as $ma_khoa => $ten_khoa_full): ?>
            <tr>
                <td><?= htmlspecialchars($ten_khoa_full) ?></td>
                <td><?= $thong_ke_khoa[$ma_khoa] ?></td>
                <td><?= $tong_so_giang_vien > 0 ? round($thong_ke_khoa[$ma_khoa] / $tong_so_giang_vien * 100) . '%' : '0%' ?></td>
            </tr>
        <?php endforeach; ?>
    </table>

    <h3>Theo trình độ</h3>
    <table border="1" cellpadding="6" cellspacing="0">
        <tr><th>Trình độ</th><th>Số lượng</th><th>Tỉ lệ</th></tr>
        <?php foreach ($thong_ke_trinh_do as $ma_trinh_do => $so_luong): ?>
            <tr>
                <td><?= formatTrinhDo($ma_trinh_do) ?></td>
                <td><?= $so_luong ?></td>
                <td><?= $tong_so_giang_vien > 0 ? round($so_luong / $tong_so_giang_vien * 100) . '%' : '0%' ?></td>
            </tr>
        <?php endforeach; ?>
    </table>

    <!-- ==================== DANH SÁCH GIẢNG VIÊN (BẢNG) ==================== -->
    <h2>Danh sách giảng viên</h2>
    <?php if (empty($danh_sach)): ?>
        <p>Chưa có giảng viên nào trong danh sách. Hãy thêm mới ở form phía trên.</p>
    <?php else: ?>
        <table border="1" cellpadding="6" cellspacing="0">
            <tr>
                <th>STT</th><th>Mã GV</th><th>Họ tên</th><th>Khoa</th>
                <th>Chuyên ngành</th><th>Trình độ</th><th>Khối ngành</th><th></th>
            </tr>
            <?php for ($i = 0; $i < count($danh_sach); $i++):
                $gv = $danh_sach[$i];
                $khoi_nganh       = xacDinhKhoiNganh($gv['ten_khoa']);
                $ten_khoa_hienthi = tenKhoaDayDu($gv['ten_khoa'], $danh_sach_khoa);
                $trinh_do_hienthi = formatTrinhDo($gv['trinh_do']);
            ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <!-- Buổi 3: escape ngay tại thời điểm hiển thị để chống XSS -->
                    <td><?= htmlspecialchars($gv['ma_gv']) ?></td>
                    <td><?= htmlspecialchars($gv['ho_ten']) ?></td>
                    <td><?= htmlspecialchars($ten_khoa_hienthi) ?></td>
                    <td><?= htmlspecialchars($gv['bo_mon']) ?></td>
                    <td><?= $trinh_do_hienthi ?></td>
                    <td><?= $khoi_nganh ?></td>
                    <td>
                        <form method="POST" action="" style="display:inline;">
                            <input type="hidden" name="xoa_index" value="<?= $i ?>">
                            <button type="submit">Xóa</button>
                        </form>
                    </td>
                </tr>
            <?php endfor; ?>
        </table>
    <?php endif; ?>

</div>
</body>
</html>
