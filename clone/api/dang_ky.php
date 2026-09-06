<?php
// api/dang_ky.php
header('Content-Type: application/json; charset=utf-8');
require_once '../config/db.php';

$input = json_decode(file_get_contents('php://input'), true);
$ma_sv   = trim($input['msv'] ?? '');
$ma_lhp  = trim($input['ma_hp'] ?? ''); 

if (!$ma_sv || !$ma_lhp) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Thiếu thông tin sinh viên hoặc mã lớp học phần.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$db = getDB();

try {
    $db->beginTransaction();

    // 1. Kiểm tra lớp học phần có tồn tại không và lấy thông tin môn học, học kỳ
    $stmt_lhp = $db->prepare("
        SELECT 
            lhp.ma_mon, lhp.si_so_toi_da, lhp.trang_thai, lhp.id_hoc_ky,
            mh.ten_mon, COALESCE(mh.hoc_ky_mo, 'CA_HAI') AS hoc_ky_mo,
            hk.ten_hoc_ky, hk.nam_hoc, hk.ngay_mo_dang_ky, hk.ngay_dong_dang_ky
        FROM lop_hoc_phan lhp
        JOIN mon_hoc mh ON lhp.ma_mon = mh.ma_mon
        JOIN hoc_ky hk ON lhp.id_hoc_ky = hk.id_hoc_ky
        WHERE lhp.ma_lhp = :ma_lhp 
        FOR UPDATE
    ");
    $stmt_lhp->execute([':ma_lhp' => $ma_lhp]);
    $lhp = $stmt_lhp->fetch();

    if (!$lhp) {
        $db->rollBack();
        echo json_encode(['success' => false, 'message' => 'Lớp học phần không tồn tại.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($lhp['trang_thai'] !== 'DANG_MO' && $lhp['trang_thai'] !== 'CHUA_MO') {
        $db->rollBack();
        echo json_encode(['success' => false, 'message' => 'Lớp học phần này không mở đăng ký.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 1.5. Kiểm tra thời hạn mở đăng ký học kỳ
    if (!empty($lhp['ngay_mo_dang_ky']) && !empty($lhp['ngay_dong_dang_ky'])) {
        $now = new DateTime();
        $mo = new DateTime($lhp['ngay_mo_dang_ky']);
        $dong = new DateTime($lhp['ngay_dong_dang_ky']);
        if ($now < $mo || $now > $dong) {
            $db->rollBack();
            echo json_encode([
                'success' => false, 
                'message' => 'Cổng đăng ký đang đóng. Thời gian đăng ký: từ ' . $mo->format('d/m/Y H:i') . ' đến ' . $dong->format('d/m/Y H:i') . '.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }

    // 1.6. KIỂM TRA MÔN HỌC CHỈ MỞ THEO HỌC KỲ THÍCH HỢP (HK1 vs HK2 vs CẢ HAI) & RÀNG BUỘC HỌC LẠI
    $currentTenHk = strtoupper(trim($lhp['ten_hoc_ky'])); // HK1 hoặc HK2
    $hocKyMo = strtoupper(trim($lhp['hoc_ky_mo']));       // HK1, HK2, hoặc CA_HAI

    if ($hocKyMo !== 'CA_HAI' && strpos($currentTenHk, $hocKyMo) === false) {
        $db->rollBack();
        $targetHkText = ($hocKyMo === 'HK1') ? 'Học kỳ 1 (HK1)' : 'Học kỳ 2 (HK2)';
        echo json_encode([
            'success' => false, 
            'message' => "Môn '" . $lhp['ten_mon'] . "' chỉ mở giảng dạy vào " . $targetHkText . "! Nếu đăng ký học lại hoặc học mới, bạn phải đợi đến " . $targetHkText . " năm sau mới được đăng ký."
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 1.7. KIỂM TRA MÔN HỌC TIÊN QUYẾT
    $stmt_tq = $db->prepare("
        SELECT tp.ma_mon_tien_quyet, mh.ten_mon AS ten_mon_tq
        FROM mon_hoc_tien_quyet tp
        JOIN mon_hoc mh ON tp.ma_mon_tien_quyet = mh.ma_mon
        WHERE tp.ma_mon = :ma_mon AND tp.loai_dieu_kien = 'BAT_BUOC'
    ");
    $stmt_tq->execute([':ma_mon' => $lhp['ma_mon']]);
    $tqList = $stmt_tq->fetchAll();

    foreach ($tqList as $tq) {
        // Kiểm tra xem sinh viên đã học và đạt môn tiên quyết chưa (diem_tong_ket >= 4.0)
        $stmt_check_tq = $db->prepare("
            SELECT d.diem_tong_ket 
            FROM diem d
            JOIN dang_ky_hoc_phan dk ON d.dang_ky_id = dk.id
            JOIN lop_hoc_phan lp ON dk.ma_lhp = lp.ma_lhp
            WHERE dk.ma_sv = :ma_sv AND lp.ma_mon = :ma_mon_tq AND d.diem_tong_ket >= 4.0
        ");
        $stmt_check_tq->execute([
            ':ma_sv' => $ma_sv,
            ':ma_mon_tq' => $tq['ma_mon_tien_quyet']
        ]);
        if (!$stmt_check_tq->fetch()) {
            $db->rollBack();
            echo json_encode([
                'success' => false,
                'message' => "Bạn chưa học hoặc chưa thi đạt môn học tiên quyết bắt buộc: '" . $tq['ten_mon_tq'] . "' (Mã môn: " . $tq['ma_mon_tien_quyet'] . ")!"
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }

    // 2. Tính sĩ số hiện tại của lớp học phần
    $stmt_siso = $db->prepare("SELECT COUNT(*) AS si_so_hien FROM dang_ky_hoc_phan WHERE ma_lhp = :ma_lhp AND trang_thai = 'DA_DANG_KY'");
    $stmt_siso->execute([':ma_lhp' => $ma_lhp]);
    $siso = $stmt_siso->fetch();

    if ($siso['si_so_hien'] >= $lhp['si_so_toi_da']) {
        $db->rollBack();
        echo json_encode(['success' => false, 'message' => 'Lớp học phần đã đầy sĩ số.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 3. Kiểm tra xem sinh viên đã đăng ký chính lớp này chưa (tránh trùng lặp)
    $stmt_check_registered = $db->prepare("
        SELECT id FROM dang_ky_hoc_phan 
        WHERE ma_sv = :ma_sv AND ma_lhp = :ma_lhp AND trang_thai = 'DA_DANG_KY'
    ");
    $stmt_check_registered->execute([':ma_sv' => $ma_sv, ':ma_lhp' => $ma_lhp]);
    if ($stmt_check_registered->fetch()) {
        $db->rollBack();
        echo json_encode(['success' => false, 'message' => 'Bạn đã đăng ký lớp học phần này rồi.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 4. Kiểm tra xem sinh viên đã đăng ký môn học này ở lớp học phần khác chưa trong CÙNG HỌC KỲ
    $stmt_check_subject = $db->prepare("
        SELECT dk.id 
        FROM dang_ky_hoc_phan dk
        JOIN lop_hoc_phan lp ON dk.ma_lhp = lp.ma_lhp
        WHERE dk.ma_sv = :ma_sv 
          AND dk.trang_thai = 'DA_DANG_KY'
          AND lp.ma_mon = :ma_mon
          AND lp.id_hoc_ky = :id_hoc_ky
    ");
    $stmt_check_subject->execute([
        ':ma_sv' => $ma_sv, 
        ':ma_mon' => $lhp['ma_mon'],
        ':id_hoc_ky' => $lhp['id_hoc_ky']
    ]);
    if ($stmt_check_subject->fetch()) {
        $db->rollBack();
        echo json_encode(['success' => false, 'message' => 'Bạn đã đăng ký một lớp khác của môn học này trong học kỳ này rồi.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 4.5. Kiểm tra trùng lịch học
    $stmt_lh_new = $db->prepare("SELECT thu, id_tiet FROM lich_hoc WHERE ma_lhp = :ma_lhp");
    $stmt_lh_new->execute([':ma_lhp' => $ma_lhp]);
    $lh_new = $stmt_lh_new->fetchAll();

    if (!empty($lh_new)) {
        $stmt_lh_existing = $db->prepare("
            SELECT lh.thu, lh.id_tiet, mh.ten_mon, th.so_tiet
            FROM dang_ky_hoc_phan dk
            JOIN lop_hoc_phan lp ON dk.ma_lhp = lp.ma_lhp
            JOIN mon_hoc mh ON lp.ma_mon = mh.ma_mon
            JOIN lich_hoc lh ON lp.ma_lhp = lh.ma_lhp
            JOIN tiet_hoc th ON lh.id_tiet = th.id_tiet
            WHERE dk.ma_sv = :ma_sv 
              AND dk.trang_thai = 'DA_DANG_KY'
              AND lp.id_hoc_ky = :id_hoc_ky
        ");
        $stmt_lh_existing->execute([
            ':ma_sv' => $ma_sv,
            ':id_hoc_ky' => $lhp['id_hoc_ky']
        ]);
        $lh_existing = $stmt_lh_existing->fetchAll();

        foreach ($lh_new as $new_slot) {
            foreach ($lh_existing as $exist_slot) {
                if ($new_slot['thu'] == $exist_slot['thu'] && $new_slot['id_tiet'] == $exist_slot['id_tiet']) {
                    $db->rollBack();
                    echo json_encode([
                        'success' => false, 
                        'message' => "Bị trùng lịch học với môn '" . $exist_slot['ten_mon'] . "' (Thứ " . $exist_slot['thu'] . ", Tiết " . $exist_slot['so_tiet'] . ")."
                    ], JSON_UNESCAPED_UNICODE);
                    exit;
                }
            }
        }
    }

    // 5. Thực hiện lưu thông tin đăng ký mới vào bảng dang_ky_hoc_phan
    $stmt_insert = $db->prepare("
        INSERT INTO dang_ky_hoc_phan (ma_sv, ma_lhp, trang_thai) 
        VALUES (:ma_sv, :ma_lhp, 'DA_DANG_KY')
    ");
    $stmt_insert->execute([':ma_sv' => $ma_sv, ':ma_lhp' => $ma_lhp]);

    $db->commit();
    echo json_encode(['success' => true, 'message' => 'Đăng ký học phần thành công!'], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
