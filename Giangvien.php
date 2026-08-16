<?php
/**
 * BUỔI 2 - CÁ NHÂN
 * Đề tài lớn: QUẢN LÝ KHÓA HỌC
 * Đối tượng dữ liệu được chọn: GIẢNG VIÊN
 *
 * Toàn bộ xử lý (kể cả việc "chọn khoa hiện chuyên ngành") đều dùng PHP
 * thuần, không dùng JavaScript. Cách làm: khi người dùng chọn Khoa và bấm
 * nút "Hiện chuyên ngành", form được submit lại (POST), PHP đọc khoa đã
 * chọn và render lại danh sách chuyên ngành tương ứng ngay trong lần tải
 * trang tiếp theo.
 */

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

// ==================== CÁC HÀM TỰ ĐỊNH NGHĨA ====================

/**
 * Xác định khối ngành dựa trên mã khoa - hàm xử lý nghiệp vụ có ý nghĩa:
 * dùng điều kiện (switch) để phân loại giảng viên theo khoa đang công tác.
 *
 * @param string $maKhoa
 * @return string
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
 * Kiểm tra mã giảng viên đã tồn tại trong danh sách hay chưa (không phân biệt
 * hoa thường, bỏ khoảng trắng thừa). Dùng để chống trùng mã khi thêm mới.
 *
 * @param string $maGv
 * @param array $danh_sach
 * @return bool
 */
function maGvDaTonTai(string $maGv, array $danh_sach): bool
{
    $ma_can_kiem_tra = mb_strtolower(trim($maGv));

    foreach ($danh_sach as $gv) {
        if (mb_strtolower(trim($gv['ma_gv'])) === $ma_can_kiem_tra) {
            return true;
        }
    }

    return false;
}

/**
 * Thống kê số lượng giảng viên theo từng khoa.
 *
 * @param array $danh_sach
 * @param array $danh_sach_khoa
 * @return array Mảng kết hợp mã khoa => số lượng
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
 *
 * @param array $danh_sach
 * @return array Mảng kết hợp mã trình độ => số lượng
 */
function thongKeTheoTrinhDo(array $danh_sach): array
{
    $thong_ke = [
        'cu_nhan' => 0,
        'thac_si' => 0,
        'tien_si' => 0,
        'pgs_gs'  => 0,
    ];

    foreach ($danh_sach as $gv) {
        if (isset($thong_ke[$gv['trinh_do']])) {
            $thong_ke[$gv['trinh_do']]++;
        }
    }

    return $thong_ke;
}

/**
 * Chuyển mã khoa thành tên khoa đầy đủ để hiển thị.
 *
 * @param string $maKhoa
 * @param array $danh_sach_khoa
 * @return string
 */
function tenKhoaDayDu(string $maKhoa, array $danh_sach_khoa): string
{
    return $danh_sach_khoa[$maKhoa] ?? "Không xác định";
}

/**
 * Chuẩn hóa/định dạng nhãn trình độ để hiển thị (dùng switch để minh họa
 * thêm một dạng cấu trúc điều kiện khác so với if/else ở trên).
 *
 * @param string $maTrinhDo
 * @return string
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
 * Kiểm tra dữ liệu nhập từ form. Trả về mảng lỗi (rỗng nếu hợp lệ).
 *
 * @param array $du_lieu
 * @param array $danh_sach_khoa
 * @param array $danh_sach_hien_co
 * @return array
 */
function kiemTraDuLieuNhap(array $du_lieu, array $danh_sach_khoa, array $danh_sach_hien_co): array
{
    $loi = [];

    if (trim($du_lieu['ma_gv']) === '') {
        $loi[] = "Mã giảng viên không được để trống.";
    } elseif (maGvDaTonTai($du_lieu['ma_gv'], $danh_sach_hien_co)) {
        $loi[] = "Mã giảng viên \"{$du_lieu['ma_gv']}\" đã tồn tại, vui lòng nhập mã khác.";
    }
    if (trim($du_lieu['ho_ten']) === '') {
        $loi[] = "Họ tên không được để trống.";
    }
    if (!array_key_exists($du_lieu['ten_khoa'], $danh_sach_khoa)) {
        $loi[] = "Vui lòng chọn khoa hợp lệ.";
    }
    if (trim($du_lieu['bo_mon']) === '') {
        $loi[] = "Vui lòng chọn chuyên ngành (bấm nút \"Hiện chuyên ngành\" sau khi chọn khoa).";
    }
    if (!in_array($du_lieu['trinh_do'], ['cu_nhan', 'thac_si', 'tien_si', 'pgs_gs'], true)) {
        $loi[] = "Trình độ không hợp lệ.";
    }

    return $loi;
}

// ==================== XỬ LÝ FORM (POST) ====================

$thong_bao_loi = [];
$thong_bao_thanh_cong = '';

// Giữ lại dữ liệu người dùng đã nhập/chọn để hiển thị lại trên form
// (kể cả khi chỉ bấm nút "Hiện chuyên ngành" hoặc khi có lỗi).
$gia_tri_form = [
    'ma_gv'    => $_POST['ma_gv'] ?? '',
    'ho_ten'   => $_POST['ho_ten'] ?? '',
    'ten_khoa' => $_POST['ten_khoa'] ?? '',
    'bo_mon'   => $_POST['bo_mon'] ?? '',
    'trinh_do' => $_POST['trinh_do'] ?? '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['hien_chuyen_nganh'])) {
    // Người dùng chỉ vừa chọn khoa và bấm "Hiện chuyên ngành":
    // không thêm giảng viên, chỉ load lại trang để hiện danh sách chuyên ngành.
    // Chuyên ngành đã chọn trước đó (nếu có) không còn phù hợp với khoa mới nên xóa đi.
    $gia_tri_form['bo_mon'] = '';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['them_giang_vien'])) {

    // Kiểm tra dữ liệu (điều kiện) - bao gồm kiểm tra trùng mã giảng viên
    $thong_bao_loi = kiemTraDuLieuNhap($gia_tri_form, $danh_sach_khoa, $_SESSION['danh_sach_giang_vien']);

    if (empty($thong_bao_loi)) {
        // Tổ chức dữ liệu bằng mảng kết hợp (1 phần tử = 1 giảng viên)
        $giang_vien_moi = [
            'ma_gv'    => htmlspecialchars(trim($gia_tri_form['ma_gv'])),
            'ho_ten'   => htmlspecialchars(trim($gia_tri_form['ho_ten'])),
            'ten_khoa' => $gia_tri_form['ten_khoa'],
            'bo_mon'   => htmlspecialchars(trim($gia_tri_form['bo_mon'])),
            'trinh_do' => $gia_tri_form['trinh_do'],
        ];

        // Thêm vào mảng danh sách (lưu trong session để tái sử dụng)
        $_SESSION['danh_sach_giang_vien'][] = $giang_vien_moi;

        $thong_bao_thanh_cong = "Đã thêm giảng viên \"{$giang_vien_moi['ho_ten']}\" vào danh sách.";

        // Thêm thành công thì làm trống lại form
        $gia_tri_form = [
            'ma_gv'    => '',
            'ho_ten'   => '',
            'ten_khoa' => '',
            'bo_mon'   => '',
            'trinh_do' => '',
        ];
    }
}

// Xử lý xóa 1 giảng viên khỏi danh sách (tiện ích thêm, không bắt buộc)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['xoa_index'])) {
    $index = (int)$_POST['xoa_index'];
    if (isset($_SESSION['danh_sach_giang_vien'][$index])) {
        unset($_SESSION['danh_sach_giang_vien'][$index]);
        $_SESSION['danh_sach_giang_vien'] = array_values($_SESSION['danh_sach_giang_vien']);
    }
}

$danh_sach = $_SESSION['danh_sach_giang_vien'];
$tong_so_giang_vien = count($danh_sach);

// Danh sách chuyên ngành sẽ hiện ra tương ứng với khoa đã chọn trên form (nếu có)
$chuyen_nganh_hien_thi = [];
if ($gia_tri_form['ten_khoa'] !== '' && isset($chuyen_nganh_theo_khoa[$gia_tri_form['ten_khoa']])) {
    $chuyen_nganh_hien_thi = $chuyen_nganh_theo_khoa[$gia_tri_form['ten_khoa']];
}

// Thống kê nhanh (tính sẵn ở đây để dùng khi render)
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
</head>
<body>
<div class="container">

    <h1>Hệ thống Quản lý Khóa học</h1>
    <p>Chức năng: Quản lý Giảng viên</p>

    <?php if (!empty($thong_bao_loi)): ?>
        <div>
            <strong>Dữ liệu chưa hợp lệ:</strong>
            <ul>
                <?php foreach ($thong_bao_loi as $loi): ?>
                    <li><?= htmlspecialchars($loi) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($thong_bao_thanh_cong !== ''): ?>
        <p><?= $thong_bao_thanh_cong ?></p>
    <?php endif; ?>

    <!-- ==================== FORM NHẬP THÔNG TIN GIẢNG VIÊN ==================== -->
    <h2>Thêm giảng viên mới</h2>
    <form method="POST" action="">
        <p>
            <label for="ma_gv">Mã giảng viên</label><br>
            <input type="text" id="ma_gv" name="ma_gv" placeholder="VD: GV001"
                   value="<?= htmlspecialchars($gia_tri_form['ma_gv']) ?>" required>
        </p>
        <p>
            <label for="ho_ten">Họ và tên</label><br>
            <input type="text" id="ho_ten" name="ho_ten" placeholder="VD: Nguyễn Văn A"
                   value="<?= htmlspecialchars($gia_tri_form['ho_ten']) ?>" required>
        </p>
        <p>
            <label for="ten_khoa">Khoa</label><br>
            <select id="ten_khoa" name="ten_khoa" required>
                <option value="">-- Chọn Khoa --</option>
                <?php foreach ($danh_sach_khoa as $ma => $ten): ?>
                    <option value="<?= htmlspecialchars($ma) ?>"
                        <?= $gia_tri_form['ten_khoa'] === $ma ? 'selected' : '' ?>>
                        <?= htmlspecialchars($ten) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" name="hien_chuyen_nganh">Hiện chuyên ngành</button>
        </p>
        <p>
            <label for="bo_mon">Chuyên ngành</label><br>
            <?php if (empty($chuyen_nganh_hien_thi)): ?>
                <select id="bo_mon" name="bo_mon" disabled>
                    <option value="">-- Chọn khoa rồi bấm "Hiện chuyên ngành" --</option>
                </select>
            <?php else: ?>
                <select id="bo_mon" name="bo_mon" required>
                    <option value="">-- Chọn chuyên ngành --</option>
                    <?php foreach ($chuyen_nganh_hien_thi as $chuyen_nganh): ?>
                        <option value="<?= htmlspecialchars($chuyen_nganh) ?>"
                            <?= $gia_tri_form['bo_mon'] === $chuyen_nganh ? 'selected' : '' ?>>
                            <?= htmlspecialchars($chuyen_nganh) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            <?php endif; ?>
        </p>
        <p>
            <label for="trinh_do">Trình độ</label><br>
            <select id="trinh_do" name="trinh_do" required>
                <option value="">-- Chọn trình độ --</option>
                <?php
                $danh_sach_trinh_do = ['cu_nhan', 'thac_si', 'tien_si', 'pgs_gs'];
                foreach ($danh_sach_trinh_do as $ma_trinh_do):
                ?>
                    <option value="<?= $ma_trinh_do ?>"
                        <?= $gia_tri_form['trinh_do'] === $ma_trinh_do ? 'selected' : '' ?>>
                        <?= formatTrinhDo($ma_trinh_do) ?>
                    </option>
                <?php endforeach; ?>
            </select>
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
        <tr>
            <th>Khoa</th>
            <th>Số lượng</th>
            <th>Tỉ lệ</th>
        </tr>
        <?php foreach ($danh_sach_khoa as $ma_khoa => $ten_khoa_full): ?>
            <tr>
                <td><?= htmlspecialchars($ten_khoa_full) ?></td>
                <td><?= $thong_ke_khoa[$ma_khoa] ?></td>
                <td>
                    <?= $tong_so_giang_vien > 0
                        ? round($thong_ke_khoa[$ma_khoa] / $tong_so_giang_vien * 100) . '%'
                        : '0%' ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>

    <h3>Theo trình độ</h3>
    <table border="1" cellpadding="6" cellspacing="0">
        <tr>
            <th>Trình độ</th>
            <th>Số lượng</th>
            <th>Tỉ lệ</th>
        </tr>
        <?php foreach ($thong_ke_trinh_do as $ma_trinh_do => $so_luong): ?>
            <tr>
                <td><?= formatTrinhDo($ma_trinh_do) ?></td>
                <td><?= $so_luong ?></td>
                <td>
                    <?= $tong_so_giang_vien > 0
                        ? round($so_luong / $tong_so_giang_vien * 100) . '%'
                        : '0%' ?>
                </td>
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
                <th>STT</th>
                <th>Mã GV</th>
                <th>Họ tên</th>
                <th>Khoa</th>
                <th>Chuyên ngành</th>
                <th>Trình độ</th>
                <th>Khối ngành</th>
                <th></th>
            </tr>
            <?php
            // Dùng vòng lặp for để duyệt mảng và hiển thị theo dạng bảng
            for ($i = 0; $i < count($danh_sach); $i++):
                $gv = $danh_sach[$i];

                // Gọi hàm tự định nghĩa để xử lý nghiệp vụ
                $khoi_nganh       = xacDinhKhoiNganh($gv['ten_khoa']);
                $ten_khoa_hienthi = tenKhoaDayDu($gv['ten_khoa'], $danh_sach_khoa);
                $trinh_do_hienthi = formatTrinhDo($gv['trinh_do']);
            ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= $gv['ma_gv'] ?></td>
                    <td><?= $gv['ho_ten'] ?></td>
                    <td><?= htmlspecialchars($ten_khoa_hienthi) ?></td>
                    <td><?= $gv['bo_mon'] ?></td>
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