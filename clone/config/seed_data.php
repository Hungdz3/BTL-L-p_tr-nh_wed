<?php
/**
 * seed_data.php - Tạo dữ liệu mẫu đầy đủ cho tất cả các bảng trong DB
 * Chạy: php config/seed_data.php
 */
require_once 'C:/laptrinhweddd/BTL_LapTrinhWed/config/db.php';

$db = getDB();
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "=== BẮT ĐẦU TẠO DỮ LIỆU MẪU DỰ ÁN ===\n\n";

try {
    $db->beginTransaction();

    // ==================================================
    // 1. VAI_TRO
    // ==================================================
    echo "1. Tạo vai_tro...\n";
    $vaiTroIds = [];
    $vaiTros = [
        ['ADMIN', 'Quản trị viên'],
        ['GIANG_VIEN', 'Giảng viên'],
        ['SINH_VIEN', 'Sinh viên'],
    ];
    foreach ($vaiTros as [$ten, $mo_ta]) {
        $stmt = $db->prepare("INSERT INTO vai_tro (ten, mo_ta) VALUES (:ten, :mo_ta) ON CONFLICT DO NOTHING RETURNING id");
        $stmt->execute([':ten' => $ten, ':mo_ta' => $mo_ta]);
        $row = $stmt->fetch();
        if ($row) {
            $vaiTroIds[$ten] = $row['id'];
        } else {
            $row = $db->query("SELECT id FROM vai_tro WHERE ten = '$ten'")->fetch();
            $vaiTroIds[$ten] = $row['id'];
        }
    }
    echo "   -> OK: " . count($vaiTroIds) . " vai trò\n";

    // ==================================================
    // 2. QUYEN_HAN
    // ==================================================
    echo "2. Tạo quyen_han...\n";
    $quyens = [
        ['QUAN_LY_SV', 'Quản lý sinh viên'],
        ['QUAN_LY_GV', 'Quản lý giảng viên'],
        ['QUAN_LY_MON_HOC', 'Quản lý môn học'],
        ['QUAN_LY_THONG_BAO', 'Quản lý thông báo'],
        ['XEM_LICH_HOC', 'Xem lịch học'],
        ['DANG_KY_HOC_PHAN', 'Đăng ký học phần'],
        ['XEM_DIEM', 'Xem điểm'],
        ['NHAP_DIEM', 'Nhập điểm'],
        ['XUAT_BAO_CAO', 'Xuất báo cáo'],
    ];
    $quyenIds = [];
    foreach ($quyens as [$ten, $mo_ta]) {
        $stmt = $db->prepare("INSERT INTO quyen_han (ten_quyen_han, mo_ta) VALUES (:ten, :mo_ta) ON CONFLICT DO NOTHING RETURNING id");
        $stmt->execute([':ten' => $ten, ':mo_ta' => $mo_ta]);
        $row = $stmt->fetch();
        if ($row) {
            $quyenIds[$ten] = $row['id'];
        } else {
            $row = $db->query("SELECT id FROM quyen_han WHERE ten_quyen_han = '$ten'")->fetch();
            $quyenIds[$ten] = $row['id'];
        }
    }
    echo "   -> OK: " . count($quyenIds) . " quyền hạn\n";

    // ==================================================
    // 3. ROLE_QUYEN_HAN
    // ==================================================
    echo "3. Tạo role_quyen_han...\n";
    foreach ($quyenIds as $qId) {
        $db->prepare("INSERT INTO role_quyen_han (vai_tro_id, quyen_han_id) VALUES (?, ?) ON CONFLICT DO NOTHING")
            ->execute([$vaiTroIds['ADMIN'], $qId]);
    }
    foreach (['XEM_LICH_HOC', 'NHAP_DIEM', 'XEM_DIEM', 'XUAT_BAO_CAO'] as $q) {
        if (isset($quyenIds[$q])) {
            $db->prepare("INSERT INTO role_quyen_han (vai_tro_id, quyen_han_id) VALUES (?, ?) ON CONFLICT DO NOTHING")
                ->execute([$vaiTroIds['GIANG_VIEN'], $quyenIds[$q]]);
        }
    }
    foreach (['XEM_LICH_HOC', 'DANG_KY_HOC_PHAN', 'XEM_DIEM'] as $q) {
        if (isset($quyenIds[$q])) {
            $db->prepare("INSERT INTO role_quyen_han (vai_tro_id, quyen_han_id) VALUES (?, ?) ON CONFLICT DO NOTHING")
                ->execute([$vaiTroIds['SINH_VIEN'], $quyenIds[$q]]);
        }
    }
    echo "   -> OK\n";

    // ==================================================
    // 4. KHOA
    // ==================================================
    echo "4. Tạo khoa...\n";
    $khoaList = [
        ['CNTT', 'Khoa Công nghệ Thông tin'],
        ['DTVT', 'Khoa Điện tử Viễn thông'],
        ['QTKD', 'Khoa Quản trị Kinh doanh'],
        ['KT', 'Khoa Kế toán'],
        ['NGANHK', 'Khoa Ngoại ngữ'],
    ];
    foreach ($khoaList as [$ma, $ten]) {
        $db->prepare("INSERT INTO khoa (ma_khoa, ten_khoa) VALUES (?, ?) ON CONFLICT DO NOTHING")
            ->execute([$ma, $ten]);
    }
    echo "   -> OK: " . count($khoaList) . " khoa\n";

    // ==================================================
    // 5. NGANH
    // ==================================================
    echo "5. Tạo nganh...\n";
    $nganhList = [
        ['KTPM', 'Kỹ thuật Phần mềm', 'CNTT'],
        ['HTTT', 'Hệ thống Thông tin', 'CNTT'],
        ['ATTT', 'An toàn Thông tin', 'CNTT'],
        ['KTDT', 'Kỹ thuật Điện tử', 'DTVT'],
        ['VT', 'Viễn thông', 'DTVT'],
        ['QTKDTH', 'Quản trị Kinh doanh Tổng hợp', 'QTKD'],
        ['MKT', 'Marketing', 'QTKD'],
        ['KTKT', 'Kế toán Kiểm toán', 'KT'],
        ['TCNH', 'Tài chính Ngân hàng', 'KT'],
        ['NNTA', 'Ngôn ngữ Tiếng Anh', 'NGANHK'],
    ];
    foreach ($nganhList as [$ma, $ten, $khoa]) {
        $db->prepare("INSERT INTO nganh (ma_nganh, ten_nganh, ma_khoa) VALUES (?, ?, ?) ON CONFLICT DO NOTHING")
            ->execute([$ma, $ten, $khoa]);
    }
    echo "   -> OK: " . count($nganhList) . " ngành\n";

    // ==================================================
    // 6. LOP_SINH_VIEN
    // ==================================================
    echo "6. Tạo lop_sinh_vien...\n";
    $lopList = [
        ['K65-KTPM-A', 'KTPM K65A', 'KTPM', '2022-2026', 2022],
        ['K65-KTPM-B', 'KTPM K65B', 'KTPM', '2022-2026', 2022],
        ['K65-HTTT', 'HTTT K65', 'HTTT', '2022-2026', 2022],
        ['K65-ATTT', 'ATTT K65', 'ATTT', '2022-2026', 2022],
        ['K66-KTPM', 'KTPM K66', 'KTPM', '2023-2027', 2023],
        ['K66-HTTT', 'HTTT K66', 'HTTT', '2023-2027', 2023],
        ['K65-MKT', 'Marketing K65', 'MKT', '2022-2026', 2022],
        ['K65-KTKT', 'Kế toán K65', 'KTKT', '2022-2026', 2022],
        ['K66-QTKD', 'QTKD K66', 'QTKDTH', '2023-2027', 2023],
        ['K65-NNTA', 'Tiếng Anh K65', 'NNTA', '2022-2026', 2022],
    ];
    foreach ($lopList as [$ma, $ten, $nganh, $khoahoc, $nam]) {
        $db->prepare("INSERT INTO lop_sinh_vien (ma_lop_sv, ten_lop, ma_nganh, khoa_hoc, nam_nhap_hoc) VALUES (?, ?, ?, ?, ?) ON CONFLICT DO NOTHING")
            ->execute([$ma, $ten, $nganh, $khoahoc, $nam]);
    }
    echo "   -> OK: " . count($lopList) . " lớp\n";

    // ==================================================
    // 7. TAI_KHOAN
    // ==================================================
    echo "7. Tạo tai_khoan...\n";
    $defaultPass = password_hash('123456', PASSWORD_BCRYPT);
    $adminPass   = password_hash('admin@123', PASSWORD_BCRYPT);

    $tkIds = [];
    // Admin
    $stmt = $db->prepare("INSERT INTO tai_khoan (username, password_hash, email, vai_tro_id) VALUES (?, ?, ?, ?) ON CONFLICT DO NOTHING RETURNING id");
    $stmt->execute(['admin', $adminPass, 'admin@hnda.edu.vn', $vaiTroIds['ADMIN']]);
    $row = $stmt->fetch();
    $tkIds['admin'] = $row ? $row['id'] : $db->query("SELECT id FROM tai_khoan WHERE username='admin'")->fetchColumn();

    // Giảng viên accounts
    $gvAccounts = [
        ['GV001', 'gv001@hnda.edu.vn'],
        ['GV002', 'gv002@hnda.edu.vn'],
        ['GV003', 'gv003@hnda.edu.vn'],
        ['GV004', 'gv004@hnda.edu.vn'],
        ['GV005', 'gv005@hnda.edu.vn'],
    ];
    foreach ($gvAccounts as [$un, $email]) {
        $stmt = $db->prepare("INSERT INTO tai_khoan (username, password_hash, email, vai_tro_id) VALUES (?, ?, ?, ?) ON CONFLICT DO NOTHING RETURNING id");
        $stmt->execute([$un, $defaultPass, $email, $vaiTroIds['GIANG_VIEN']]);
        $row = $stmt->fetch();
        $tkIds[$un] = $row ? $row['id'] : $db->query("SELECT id FROM tai_khoan WHERE username='$un'")->fetchColumn();
    }

    // Sinh viên accounts
    $svAccounts = [
        '2200001','2200002','2200003','2200004','2200005',
        '2200006','2200007','2200008','2200009','2200010',
        '2300001','2300002','2300003','2300004','2300005',
    ];
    foreach ($svAccounts as $msv) {
        $stmt = $db->prepare("INSERT INTO tai_khoan (username, password_hash, email, vai_tro_id) VALUES (?, ?, ?, ?) ON CONFLICT DO NOTHING RETURNING id");
        $stmt->execute([$msv, $defaultPass, "$msv@student.hnda.edu.vn", $vaiTroIds['SINH_VIEN']]);
        $row = $stmt->fetch();
        $tkIds[$msv] = $row ? $row['id'] : $db->query("SELECT id FROM tai_khoan WHERE username='$msv'")->fetchColumn();
    }
    echo "   -> OK: " . count($tkIds) . " tài khoản\n";

    // ==================================================
    // 8. GIAO_VIEN
    // ==================================================
    echo "8. Tạo giao_vien...\n";
    $gvList = [
        ['GV001', 'Nguyễn Thanh Hùng',  'gv001@hnda.edu.vn', '0912100001', 'CNTT'],
        ['GV002', 'Trần Thị Lan',        'gv002@hnda.edu.vn', '0912100002', 'CNTT'],
        ['GV003', 'Lê Văn Dũng',         'gv003@hnda.edu.vn', '0912100003', 'DTVT'],
        ['GV004', 'Phạm Ngọc Hà',        'gv004@hnda.edu.vn', '0912100004', 'QTKD'],
        ['GV005', 'Hoàng Thị Mai',       'gv005@hnda.edu.vn', '0912100005', 'KT'],
    ];
    foreach ($gvList as [$ma, $ten, $email, $sdt, $khoa]) {
        $tkId = $tkIds[$ma] ?? null;
        if (!$tkId) continue;
        $db->prepare("INSERT INTO giao_vien (ma_gv, tai_khoan_id, ho_ten, email, so_dien_thoai, ma_khoa) VALUES (?,?,?,?,?,?) ON CONFLICT DO NOTHING")
            ->execute([$ma, $tkId, $ten, $email, $sdt, $khoa]);
    }
    echo "   -> OK: " . count($gvList) . " giảng viên\n";

    // ==================================================
    // 9. SINH_VIEN (Sử dụng 'NAM', 'NU' chuẩn enum)
    // ==================================================
    echo "9. Tạo sinh_vien...\n";
    $svList = [
        ['2200001', 'Nguyễn Văn An',    '2004-03-15', 'NAM', 'an.nv@student.hnda.edu.vn', '0901000001', 'K65-KTPM-A'],
        ['2200002', 'Trần Thị Bích',    '2004-07-22', 'NU',  'bich.tt@student.hnda.edu.vn','0901000002', 'K65-KTPM-A'],
        ['2200003', 'Lê Văn Cường',     '2004-01-10', 'NAM', 'cuong.lv@student.hnda.edu.vn','0901000003', 'K65-KTPM-A'],
        ['2200004', 'Phạm Thị Diệu',    '2004-11-05', 'NU',  'dieu.pt@student.hnda.edu.vn','0901000004', 'K65-KTPM-B'],
        ['2200005', 'Vũ Hoàng Duy',     '2004-05-18', 'NAM', 'duy.vh@student.hnda.edu.vn', '0901000005', 'K65-KTPM-B'],
        ['2200006', 'Hoàng Thị Ế',      '2004-09-30', 'NU',  'e.ht@student.hnda.edu.vn',   '0901000006', 'K65-HTTT'],
        ['2200007', 'Đặng Văn Phúc',    '2004-02-14', 'NAM', 'phuc.dv@student.hnda.edu.vn','0901000007', 'K65-HTTT'],
        ['2200008', 'Ngô Thị Giang',    '2004-06-25', 'NU',  'giang.nt@student.hnda.edu.vn','0901000008', 'K65-ATTT'],
        ['2200009', 'Bùi Đức Hải',      '2004-04-08', 'NAM', 'hai.bd@student.hnda.edu.vn', '0901000009', 'K65-MKT'],
        ['2200010', 'Đinh Thị Hoa',     '2004-12-01', 'NU',  'hoa.dt@student.hnda.edu.vn', '0901000010', 'K65-KTKT'],
        ['2300001', 'Nguyễn Minh Khoa', '2005-03-20', 'NAM', 'khoa.nm@student.hnda.edu.vn','0901000011', 'K66-KTPM'],
        ['2300002', 'Trần Ngọc Lan',    '2005-07-11', 'NU',  'lan.tn@student.hnda.edu.vn', '0901000012', 'K66-KTPM'],
        ['2300003', 'Lê Văn Long',      '2005-10-15', 'NAM', 'long.lv@student.hnda.edu.vn','0901000013', 'K66-HTTT'],
        ['2300004', 'Phạm Thị Mai',     '2005-04-22', 'NU',  'mai.pt@student.hnda.edu.vn', '0901000014', 'K66-QTKD'],
        ['2300005', 'Vũ Đình Nam',      '2005-08-18', 'NAM', 'nam.vd@student.hnda.edu.vn', '0901000015', 'K65-NNTA'],
    ];
    foreach ($svList as [$ma, $ten, $ngSinh, $gioiTinh, $email, $sdt, $lop]) {
        $tkId = $tkIds[$ma] ?? null;
        if (!$tkId) continue;
        $db->prepare("INSERT INTO sinh_vien (ma_sv, tai_khoan_id, ho_ten, ngay_sinh, gioi_tinh, email, so_dien_thoai, ma_lop_sv) VALUES (?,?,?,?,?,?,?,?) ON CONFLICT DO NOTHING")
            ->execute([$ma, $tkId, $ten, $ngSinh, $gioiTinh, $email, $sdt, $lop]);
    }
    echo "   -> OK: " . count($svList) . " sinh viên\n";

    // ==================================================
    // 10. HOC_KY (Sử dụng HK1, HK2, CHUA_MO, DANG_HOC, DA_KET_THUC)
    // ==================================================
    echo "10. Tạo hoc_ky...\n";
    $hkIds = [];
    $hkList = [
        ['2024-2025', 'HK1', '2024-09-01', '2025-01-15', '2024-08-15 08:00:00+07', '2024-08-25 23:59:59+07', 'DA_KET_THUC'],
        ['2024-2025', 'HK2', '2025-02-01', '2025-06-30', '2025-01-15 08:00:00+07', '2025-01-25 23:59:59+07', 'DA_KET_THUC'],
        ['2025-2026', 'HK1', '2025-09-01', '2026-01-15', '2025-08-15 08:00:00+07', '2025-08-25 23:59:59+07', 'DANG_HOC'],
        ['2025-2026', 'HK2', '2026-02-01', '2026-06-30', '2026-01-15 08:00:00+07', '2026-01-25 23:59:59+07', 'CHUA_MO'],
    ];
    foreach ($hkList as [$nam, $ten, $bd, $kt, $moDB, $dongDB, $tt]) {
        $stmt = $db->prepare("INSERT INTO hoc_ky (nam_hoc, ten_hoc_ky, ngay_bat_dau, ngay_ket_thuc, ngay_mo_dang_ky, ngay_dong_dang_ky, trang_thai) VALUES (?,?,?,?,?,?,?) ON CONFLICT DO NOTHING RETURNING id_hoc_ky");
        $stmt->execute([$nam, $ten, $bd, $kt, $moDB, $dongDB, $tt]);
        $row = $stmt->fetch();
        $key = "$nam-$ten";
        $hkIds[$key] = $row ? $row['id_hoc_ky'] : $db->query("SELECT id_hoc_ky FROM hoc_ky WHERE nam_hoc='$nam' AND ten_hoc_ky='$ten'")->fetchColumn();
    }
    echo "   -> OK: " . count($hkIds) . " học kỳ\n";

    // ==================================================
    // 11. TIET_HOC
    // ==================================================
    echo "11. Tạo tiet_hoc...\n";
    $tietList = [
        [1,  '06:45', '07:35', 'SANG'],
        [2,  '07:40', '08:30', 'SANG'],
        [3,  '08:35', '09:25', 'SANG'],
        [4,  '09:30', '10:20', 'SANG'],
        [5,  '10:25', '11:15', 'SANG'],
        [6,  '11:20', '12:10', 'SANG'],
        [7,  '12:45', '13:35', 'CHIEU'],
        [8,  '13:40', '14:30', 'CHIEU'],
        [9,  '14:35', '15:25', 'CHIEU'],
        [10, '15:30', '16:20', 'CHIEU'],
        [11, '16:25', '17:15', 'CHIEU'],
        [12, '17:20', '18:10', 'CHIEU'],
    ];
    $tietIds = [];
    foreach ($tietList as [$so, $gbd, $gkt, $ca]) {
        $stmt = $db->prepare("INSERT INTO tiet_hoc (so_tiet, gio_bat_dau, gio_ket_thuc, ca_hoc) VALUES (?,?,?,?) ON CONFLICT DO NOTHING RETURNING id_tiet");
        $stmt->execute([$so, $gbd, $gkt, $ca]);
        $row = $stmt->fetch();
        $tietIds[$so] = $row ? $row['id_tiet'] : $db->query("SELECT id_tiet FROM tiet_hoc WHERE so_tiet=$so")->fetchColumn();
    }
    echo "   -> OK: " . count($tietIds) . " tiết học\n";

    // ==================================================
    // 12. MON_HOC (Sử dụng trang_thai = 'HOAT_DONG')
    // ==================================================
    echo "12. Tạo mon_hoc...\n";
    $monList = [
        ['30INF001', 'Toán cao cấp', 3, null, 'MON_CHUNG', null],
        ['30INF002', 'Vật lý đại cương', 2, null, 'MON_CHUNG', null],
        ['30INF003', 'Tiếng Anh 1', 3, null, 'MON_CHUNG', null],
        ['30INF004', 'Tiếng Anh 2', 3, null, 'MON_CHUNG', null],
        ['30INF005', 'Tư tưởng Hồ Chí Minh', 2, null, 'MON_CHUNG', null],
        ['30INF006', 'Pháp luật đại cương', 2, null, 'MON_CHUNG', null],
        ['30CNTT001', 'Lập trình cơ bản', 3, 'CNTT', 'MON_CHUYEN_NGANH', 'KTPM'],
        ['30CNTT002', 'Cấu trúc dữ liệu và giải thuật', 3, 'CNTT', 'MON_CHUYEN_NGANH', 'KTPM'],
        ['30CNTT003', 'Lập trình hướng đối tượng', 3, 'CNTT', 'MON_CHUYEN_NGANH', 'KTPM'],
        ['30CNTT004', 'Cơ sở dữ liệu', 3, 'CNTT', 'MON_CHUYEN_NGANH', 'HTTT'],
        ['30CNTT005', 'Mạng máy tính', 3, 'CNTT', 'MON_CHUYEN_NGANH', 'HTTT'],
        ['30CNTT006', 'Lập trình web', 3, 'CNTT', 'MON_CHUYEN_NGANH', 'KTPM'],
        ['30CNTT007', 'An toàn thông tin', 3, 'CNTT', 'MON_CHUYEN_NGANH', 'ATTT'],
        ['30CNTT008', 'Trí tuệ nhân tạo', 3, 'CNTT', 'MON_CHUYEN_NGANH', 'KTPM'],
        ['30DTVT001', 'Điện tử cơ bản', 3, 'DTVT', 'MON_CHUYEN_NGANH', 'KTDT'],
        ['30QTKD001', 'Quản trị học', 3, 'QTKD', 'MON_CHUYEN_NGANH', 'QTKDTH'],
        ['30KT001',   'Nguyên lý kế toán', 3, 'KT', 'MON_CHUYEN_NGANH', 'KTKT'],
    ];
    foreach ($monList as [$ma, $ten, $tc, $khoa, $loai, $nganh]) {
        $db->prepare("INSERT INTO mon_hoc (ma_mon, ten_mon, so_tin_chi, ma_khoa, loai_mon_hoc, ma_nganh, trang_thai) VALUES (?,?,?,?,?,?,'HOAT_DONG') ON CONFLICT DO NOTHING")
            ->execute([$ma, $ten, $tc, $khoa, $loai, $nganh]);
    }
    echo "   -> OK: " . count($monList) . " môn học\n";

    // ==================================================
    // 13. MON_HOC_TIEN_QUYET
    // ==================================================
    echo "13. Tạo mon_hoc_tien_quyet...\n";
    $tqList = [
        ['30CNTT002', '30CNTT001', 'BAT_BUOC'],
        ['30CNTT003', '30CNTT001', 'BAT_BUOC'],
        ['30CNTT006', '30CNTT003', 'BAT_BUOC'],
        ['30CNTT007', '30CNTT005', 'BAT_BUOC'],
        ['30CNTT008', '30CNTT002', 'BAT_BUOC'],
        ['30INF004',  '30INF003',  'BAT_BUOC'],
    ];
    foreach ($tqList as [$ma, $tq, $loai]) {
        $db->prepare("INSERT INTO mon_hoc_tien_quyet (ma_mon, ma_mon_tien_quyet, loai_dieu_kien) VALUES (?,?,?) ON CONFLICT DO NOTHING")
            ->execute([$ma, $tq, $loai]);
    }
    echo "   -> OK: " . count($tqList) . " môn học tiên quyết\n";

    // ==================================================
    // 14. GIAO_VIEN_MON_HOC
    // ==================================================
    echo "14. Tạo giao_vien_mon_hoc...\n";
    $gvMhList = [
        ['GV001', '30CNTT001'], ['GV001', '30CNTT002'], ['GV001', '30CNTT003'],
        ['GV002', '30CNTT004'], ['GV002', '30CNTT005'], ['GV002', '30CNTT006'],
        ['GV003', '30DTVT001'], ['GV003', '30INF001'],
        ['GV004', '30QTKD001'], ['GV004', '30INF005'], ['GV004', '30INF006'],
        ['GV005', '30KT001'],   ['GV005', '30INF006'],
    ];
    foreach ($gvMhList as [$gv, $mh]) {
        $db->prepare("INSERT INTO giao_vien_mon_hoc (ma_gv, ma_mon) VALUES (?,?) ON CONFLICT DO NOTHING")
            ->execute([$gv, $mh]);
    }
    echo "   -> OK: " . count($gvMhList) . " phân công\n";

    // ==================================================
    // 15. LOP_HOC_PHAN (DANG_MO / CHUA_MO)
    // ==================================================
    echo "15. Tạo lop_hoc_phan...\n";
    $hk1Id = $hkIds['2025-2026-HK1'] ?? null;
    $lhpList = [
        ['LHP001', '30CNTT001', $hk1Id, 'GV001', 'Lập trình cơ bản - L01', 45, '2025-09-08', '2026-01-10', 'DANG_MO'],
        ['LHP002', '30CNTT001', $hk1Id, 'GV001', 'Lập trình cơ bản - L02', 45, '2025-09-08', '2026-01-10', 'DANG_MO'],
        ['LHP003', '30CNTT002', $hk1Id, 'GV001', 'CTDL - L01',              40, '2025-09-08', '2026-01-10', 'DANG_MO'],
        ['LHP004', '30CNTT003', $hk1Id, 'GV001', 'OOP - L01',               40, '2025-09-08', '2026-01-10', 'DANG_MO'],
        ['LHP005', '30CNTT004', $hk1Id, 'GV002', 'CSDL - L01',              40, '2025-09-08', '2026-01-10', 'DANG_MO'],
        ['LHP006', '30CNTT005', $hk1Id, 'GV002', 'Mạng máy tính - L01',    35, '2025-09-08', '2026-01-10', 'DANG_MO'],
        ['LHP007', '30CNTT006', $hk1Id, 'GV002', 'Lập trình web - L01',    40, '2025-09-08', '2026-01-10', 'CHUA_MO'],
        ['LHP008', '30INF001',  $hk1Id, 'GV003', 'Toán cao cấp - L01',     50, '2025-09-08', '2026-01-10', 'DANG_MO'],
        ['LHP009', '30INF003',  $hk1Id, 'GV004', 'Tiếng Anh 1 - L01',      50, '2025-09-08', '2026-01-10', 'DANG_MO'],
        ['LHP010', '30INF005',  $hk1Id, 'GV004', 'TTHCM - L01',             50, '2025-09-08', '2026-01-10', 'DANG_MO'],
    ];
    foreach ($lhpList as [$ma, $maMon, $hkId, $gv, $ten, $ss, $bd, $kt, $tt]) {
        if (!$hkId) continue;
        $db->prepare("INSERT INTO lop_hoc_phan (ma_lhp, ma_mon, id_hoc_ky, ma_gv, ten_lop, si_so_toi_da, ngay_bat_dau, ngay_ket_thuc, trang_thai) VALUES (?,?,?,?,?,?,?,?,?) ON CONFLICT DO NOTHING")
            ->execute([$ma, $maMon, $hkId, $gv, $ten, $ss, $bd, $kt, $tt]);
    }
    echo "   -> OK: " . count($lhpList) . " lớp học phần\n";

    // ==================================================
    // 16. MON_HOC_HOC_KY
    // ==================================================
    echo "16. Tạo mon_hoc_hoc_ky...\n";
    if ($hk1Id) {
        $monHks = ['30CNTT001','30CNTT002','30CNTT003','30CNTT004','30CNTT005','30CNTT006','30INF001','30INF003','30INF005','30INF006'];
        foreach ($monHks as $maMon) {
            $db->prepare("INSERT INTO mon_hoc_hoc_ky (ma_mon, id_hoc_ky) VALUES (?,?) ON CONFLICT DO NOTHING")
                ->execute([$maMon, $hk1Id]);
        }
    }
    echo "   -> OK\n";

    // ==================================================
    // 17. LICH_HOC
    // ==================================================
    echo "17. Tạo lich_hoc...\n";
    $lichList = [
        ['LHP001', 2, 1, 'A101'],
        ['LHP001', 2, 2, 'A101'],
        ['LHP002', 3, 1, 'A102'],
        ['LHP002', 3, 2, 'A102'],
        ['LHP003', 4, 3, 'B201'],
        ['LHP003', 4, 4, 'B201'],
        ['LHP004', 5, 1, 'B202'],
        ['LHP004', 5, 2, 'B202'],
        ['LHP005', 2, 7, 'C301'],
        ['LHP005', 2, 8, 'C301'],
        ['LHP006', 3, 7, 'C302'],
        ['LHP006', 3, 8, 'C302'],
        ['LHP008', 5, 3, 'A103'],
        ['LHP008', 5, 4, 'A103'],
        ['LHP009', 6, 7, 'D401'],
        ['LHP009', 6, 8, 'D401'],
        ['LHP010', 7, 1, 'D402'],
        ['LHP010', 7, 2, 'D402'],
    ];
    foreach ($lichList as [$lhp, $thu, $soTiet, $phong]) {
        $tietId = $tietIds[$soTiet] ?? null;
        if (!$tietId) continue;
        $db->prepare("INSERT INTO lich_hoc (ma_lhp, thu, id_tiet, phong) VALUES (?,?,?,?)")
            ->execute([$lhp, $thu, $tietId, $phong]);
    }
    echo "   -> OK: " . count($lichList) . " lịch học\n";

    // ==================================================
    // 18. DOT_DANG_KY (Trạng thái HOAT_DONG / DA_DONG / CHO_DUYET)
    // ==================================================
    echo "18. Tạo dot_dang_ky...\n";
    if ($hk1Id) {
        $dotList = [
            ['DK001', 'Đợt đăng ký chính học kỳ 1 (2025-2026)', $hk1Id, '2025-08-15 08:00:00+07', '2025-08-25 23:59:59+07', 24, 'HOAT_DONG', 'Đợt đăng ký chính thức'],
            ['DK002', 'Đợt đăng ký bổ sung HK1 (2025-2026)', $hk1Id, '2025-09-01 08:00:00+07', '2025-09-05 23:59:59+07', 6, 'DA_DONG', 'Đăng ký bổ sung sau khai giảng'],
        ];
        foreach ($dotList as [$ma, $ten, $hkId, $bd, $kt, $maxTc, $tt, $ghiChu]) {
            $db->prepare("INSERT INTO dot_dang_ky (ma_dot_dk, ten_dot_dk, id_hoc_ky, ngay_bat_dau, ngay_ket_thuc, so_tin_chi_toi_da, trang_thai, ghi_chu) VALUES (?,?,?,?,?,?,?,?) ON CONFLICT DO NOTHING")
                ->execute([$ma, $ten, $hkId, $bd, $kt, $maxTc, $tt, $ghiChu]);
        }
    }
    echo "   -> OK\n";

    // ==================================================
    // 19. DANG_KY_HOC_PHAN
    // ==================================================
    echo "19. Tạo dang_ky_hoc_phan...\n";
    $dkList = [
        ['2200001','LHP001','DK001'], ['2200001','LHP005','DK001'], ['2200001','LHP008','DK001'],
        ['2200002','LHP001','DK001'], ['2200002','LHP003','DK001'], ['2200002','LHP008','DK001'],
        ['2200003','LHP002','DK001'], ['2200003','LHP004','DK001'], ['2200003','LHP009','DK001'],
        ['2200004','LHP002','DK001'], ['2200004','LHP005','DK001'], ['2200004','LHP010','DK001'],
        ['2200005','LHP001','DK001'], ['2200005','LHP003','DK001'], ['2200005','LHP010','DK001'],
        ['2200006','LHP005','DK001'], ['2200006','LHP006','DK001'], ['2200006','LHP008','DK001'],
        ['2200007','LHP005','DK001'], ['2200007','LHP006','DK001'], ['2200007','LHP009','DK001'],
        ['2200008','LHP006','DK001'], ['2200008','LHP008','DK001'], ['2200008','LHP010','DK001'],
        ['2200009','LHP009','DK001'], ['2200009','LHP010','DK001'],
        ['2200010','LHP009','DK001'], ['2200010','LHP010','DK001'],
        ['2300001','LHP001','DK001'], ['2300001','LHP008','DK001'],
        ['2300002','LHP002','DK001'], ['2300002','LHP009','DK001'],
    ];
    foreach ($dkList as [$sv, $lhp, $dot]) {
        $db->prepare("INSERT INTO dang_ky_hoc_phan (ma_sv, ma_lhp, ma_dot_dk) VALUES (?,?,?) ON CONFLICT DO NOTHING")
            ->execute([$sv, $lhp, $dot]);
    }
    echo "   -> OK: " . count($dkList) . " đăng ký\n";

    // ==================================================
    // 20. DIEM
    // ==================================================
    echo "20. Tạo diem...\n";
    $stmtDK = $db->query("SELECT id FROM dang_ky_hoc_phan LIMIT 20");
    $allDkIds = $stmtDK->fetchAll(PDO::FETCH_COLUMN);
    foreach ($allDkIds as $dkId) {
        $cc  = rand(60, 100) / 10;
        $gk  = rand(50, 100) / 10;
        $ck  = rand(50, 100) / 10;
        $tkt = round($cc * 0.1 + $gk * 0.3 + $ck * 0.6, 1);
        $db->prepare("INSERT INTO diem (dang_ky_id, diem_chuyen_can, diem_giua_ky, diem_cuoi_ky, diem_tong_ket, nguoi_nhap) VALUES (?,?,?,?,?,?) ON CONFLICT DO NOTHING")
            ->execute([$dkId, $cc, $gk, $ck, $tkt, 'GV001']);
    }
    echo "   -> OK: " . count($allDkIds) . " bảng điểm\n";

    // ==================================================
    // 21. THONG_BAO
    // ==================================================
    echo "21. Tạo thong_bao...\n";
    $tbList = [
        ['Thông báo khai giảng năm học 2025-2026', 'Trường Đại học HNDA thông báo lịch khai giảng năm học 2025-2026. Sinh viên xem lịch chi tiết tại website nhà trường.', 'TAT_CA', 'DA_GUI'],
        ['Hướng dẫn đăng ký học phần HK1 2025-2026', 'Sinh viên thực hiện đăng ký học phần qua cổng thông tin từ ngày 15/08 đến 25/08/2025. Mỗi sinh viên được đăng ký tối đa 24 tín chỉ.', 'SINH_VIEN', 'DA_GUI'],
        ['Thông báo lịch học bổ sung tháng 9/2025', 'Giảng viên cập nhật lịch giảng dạy bổ sung cho học kỳ 1 năm học 2025-2026.', 'GIANG_VIEN', 'DA_GUI'],
        ['Nhắc nhở nộp học phí HK1 2025-2026', 'Sinh viên hoàn thành nộp học phí học kỳ 1 trước ngày 30/09/2025 để tránh bị khóa quyền đăng ký học phần.', 'SINH_VIEN', 'DA_GUI'],
        ['Thông báo về lịch thi giữa kỳ', 'Lịch thi giữa kỳ học kỳ 1 năm học 2025-2026 sẽ được đăng tải vào tuần 8. Sinh viên theo dõi thông báo.', 'SINH_VIEN', 'DA_GUI'],
        ['Hội thảo công nghệ AI và Machine Learning', 'Mời toàn thể sinh viên và giảng viên tham dự hội thảo chuyên đề về AI và Machine Learning diễn ra ngày 15/10/2025.', 'TAT_CA', 'DA_GUI'],
        ['Thông báo bổ sung lớp học phần web', 'Lớp học phần Lập trình web (LHP007) đã được mở thêm. Sinh viên có thể đăng ký bổ sung trong đợt 2.', 'SINH_VIEN', 'BAN_NHAP'],
    ];
    $adminTkId = $tkIds['admin'] ?? null;
    foreach ($tbList as [$tieuDe, $noiDung, $doiTuong, $tt]) {
        $db->prepare("INSERT INTO thong_bao (tieu_de, noi_dung, doi_tuong_nhan, trang_thai, nguoi_dang_id) VALUES (?,?,?,?,?) ON CONFLICT DO NOTHING")
            ->execute([$tieuDe, $noiDung, $doiTuong, $tt, $adminTkId]);
    }
    echo "   -> OK: " . count($tbList) . " thông báo\n";

    // ==================================================
    // 22. AUDIT_LOG
    // ==================================================
    echo "22. Tạo audit_log...\n";
    $logList = [
        [$adminTkId, 'sinh_vien', '2200001', 'INSERT', null, '{"ma_sv":"2200001","ho_ten":"Nguyễn Văn An"}'],
        [$adminTkId, 'sinh_vien', '2200002', 'INSERT', null, '{"ma_sv":"2200002","ho_ten":"Trần Thị Bích"}'],
        [$adminTkId, 'giao_vien', 'GV001', 'INSERT', null, '{"ma_gv":"GV001","ho_ten":"Nguyễn Thanh Hùng"}'],
        [$adminTkId, 'mon_hoc', '30CNTT001', 'INSERT', null, '{"ma_mon":"30CNTT001","ten_mon":"Lập trình cơ bản"}'],
        [$adminTkId, 'lop_hoc_phan', 'LHP001', 'INSERT', null, '{"ma_lhp":"LHP001","trang_thai":"DANG_MO"}'],
        [$adminTkId, 'thong_bao', '1', 'INSERT', null, '{"tieu_de":"Thông báo khai giảng"}'],
        [$adminTkId, 'dot_dang_ky', 'DK001', 'INSERT', null, '{"ma_dot_dk":"DK001","trang_thai":"HOAT_DONG"}'],
    ];
    foreach ($logList as [$uid, $table, $recordId, $action, $old, $new]) {
        $db->prepare("INSERT INTO audit_log (user_id, table_name, record_id, action, old_data, new_data) VALUES (?,?,?,?,?,?)")
            ->execute([$uid, $table, $recordId, $action, $old, $new]);
    }
    echo "   -> OK: " . count($logList) . " log\n";

    $db->commit();

    echo "\n==================================================\n";
    echo "  HOÀN THÀNH TẤT CẢ DỮ LIỆU MẪU THÀNH CÔNG!\n";
    echo "==================================================\n";

} catch (Exception $e) {
    $db->rollBack();
    echo "\n!!! LỖI KHI TẠO DỮ LIỆU: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " (Line " . $e->getLine() . ")\n";
}
