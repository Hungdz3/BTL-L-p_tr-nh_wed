<?php
// api/admin/hoc_phan_action.php
header('Content-Type: application/json; charset=utf-8');
require_once '../../config/db.php';

$db = getDB();
// Hỗ trợ cả nhận JSON payload hoặc POST form-data thường
$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$method = $_SERVER['REQUEST_METHOD'];

// Helper to log audit actions
function logAdminAction($db, $tableName, $recordId, $action, $oldData = null, $newData = null) {
    try {
        $stmt = $db->prepare("
            INSERT INTO audit_log (table_name, record_id, action, old_data, new_data)
            VALUES (:table, :record_id, :action, :old_data, :new_data)
        ");
        $stmt->execute([
            ':table' => $tableName,
            ':record_id' => strval($recordId),
            ':action' => $action,
            ':old_data' => $oldData ? json_encode($oldData) : null,
            ':new_data' => $newData ? json_encode($newData) : null
        ]);
    } catch (Exception $e) {
        // Silent catch
    }
}

switch ($method) {
    case 'GET':
        try {
            // Option 1: Trả về siêu dữ liệu cấu hình
            if (isset($_GET['get_metadata'])) {
                $khoas = $db->query("SELECT ma_khoa, ten_khoa FROM khoa WHERE trang_thai = 'HOAT_DONG' ORDER BY ten_khoa ASC")->fetchAll(PDO::FETCH_ASSOC);
                $nganhs = $db->query("SELECT ma_nganh, ten_nganh, ma_khoa FROM nganh WHERE trang_thai = 'HOAT_DONG' ORDER BY ten_nganh ASC")->fetchAll(PDO::FETCH_ASSOC);
                $mon_hocs = $db->query("SELECT ma_mon, ten_mon, so_tin_chi FROM mon_hoc WHERE trang_thai = 'HOAT_DONG' ORDER BY ten_mon ASC")->fetchAll(PDO::FETCH_ASSOC);
                $giao_viens = $db->query("SELECT ma_gv, ho_ten FROM giao_vien WHERE trang_thai = 'HOAT_DONG' ORDER BY ho_ten ASC")->fetchAll(PDO::FETCH_ASSOC);
                $hoc_kys = $db->query("SELECT id_hoc_ky, ten_hoc_ky, nam_hoc FROM hoc_ky ORDER BY nam_hoc DESC, ten_hoc_ky DESC")->fetchAll(PDO::FETCH_ASSOC);
                
                echo json_encode([
                    'success' => true,
                    'khoa' => $khoas,
                    'nganh' => $nganhs,
                    'mon_hoc' => $mon_hocs,
                    'giao_vien' => $giao_viens,
                    'hoc_ky' => $hoc_kys
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }
            
            // Option 2: Trả về danh sách lớp học phần
            if (isset($_GET['get_class_courses'])) {
                $keyword = trim($_GET['q'] ?? '');
                $status = trim($_GET['trang_thai'] ?? '');
                
                $sql = "
                    SELECT 
                        lhp.ma_lhp, lhp.ten_lop, lhp.si_so_toi_da, lhp.trang_thai, lhp.ngay_bat_dau, lhp.ngay_ket_thuc,
                        mh.ma_mon, mh.ten_mon, mh.so_tin_chi,
                        gv.ho_ten AS giang_vien,
                        hk.ten_hoc_ky, hk.nam_hoc
                    FROM lop_hoc_phan lhp
                    JOIN mon_hoc mh ON lhp.ma_mon = mh.ma_mon
                    JOIN giao_vien gv ON lhp.ma_gv = gv.ma_gv
                    JOIN hoc_ky hk ON lhp.id_hoc_ky = hk.id_hoc_ky
                    WHERE 1=1
                ";
                $params = [];
                
                if ($keyword !== '') {
                    $sql .= " AND (lhp.ma_lhp ILIKE :q OR mh.ten_mon ILIKE :q OR gv.ho_ten ILIKE :q)";
                    $params[':q'] = "%{$keyword}%";
                }
                if ($status !== '') {
                    $sql .= " AND lhp.trang_thai = :status";
                    $params[':status'] = $status;
                }
                
                $sql .= " ORDER BY lhp.created_at DESC";
                
                $stmt = $db->prepare($sql);
                $stmt->execute($params);
                $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                echo json_encode(['success' => true, 'data' => $data], JSON_UNESCAPED_UNICODE);
                exit;
            }
            
            // Option 3: Trả về danh sách môn học chính
            $keyword = trim($_GET['q'] ?? '');
            $khoa = trim($_GET['khoa'] ?? '');
            $nganh = trim($_GET['nganh'] ?? '');
            
            $sql = "
                SELECT 
                    mh.ma_mon, mh.ten_mon, mh.so_tin_chi, mh.loai_mon_hoc, mh.trang_thai,
                    k.ten_khoa, k.ma_khoa,
                    n.ten_nganh, n.ma_nganh
                FROM mon_hoc mh
                LEFT JOIN khoa k ON mh.ma_khoa = k.ma_khoa
                LEFT JOIN nganh n ON mh.ma_nganh = n.ma_nganh
                WHERE 1=1
            ";
            $params = [];
            
            if ($keyword !== '') {
                $sql .= " AND (mh.ma_mon ILIKE :q OR mh.ten_mon ILIKE :q)";
                $params[':q'] = "%{$keyword}%";
            }
            if ($khoa !== '') {
                $sql .= " AND mh.ma_khoa = :khoa";
                $params[':khoa'] = $khoa;
            }
            if ($nganh !== '') {
                $sql .= " AND mh.ma_nganh = :nganh";
                $params[':nganh'] = $nganh;
            }
            
            $sql .= " ORDER BY mh.ma_mon ASC";
            
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode(['success' => true, 'data' => $data], JSON_UNESCAPED_UNICODE);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
        break;

    case 'POST':
        try {
            $action = trim($input['action'] ?? '');
            
            if ($action === 'add_mon_hoc') {
                // Thêm / Sửa môn học
                $ma_mon = trim($input['ma_mon'] ?? '');
                $ten_mon = trim($input['ten_mon'] ?? '');
                $so_tin_chi = intval($input['so_tin_chi'] ?? 3);
                $loai_mon_hoc = trim($input['loai_mon_hoc'] ?? 'MON_CHUNG');
                $ma_khoa = trim($input['ma_khoa'] ?? '');
                $ma_nganh = trim($input['ma_nganh'] ?? '');
                $trang_thai = trim($input['trang_thai'] ?? 'HOAT_DONG');
                $is_update = isset($input['is_update']) && $input['is_update'] == true;
                
                if (!$ma_mon || !$ten_mon) {
                    echo json_encode(['success' => false, 'message' => 'Mã môn và Tên môn học là bắt buộc.'], JSON_UNESCAPED_UNICODE);
                    exit;
                }
                
                // Môn chung không trực thuộc khoa/ngành
                if ($loai_mon_hoc === 'MON_CHUNG') {
                    $ma_khoa = null;
                    $ma_nganh = null;
                }
                
                if ($is_update) {
                    // Cập nhật
                    $stmt_old = $db->prepare("SELECT * FROM mon_hoc WHERE ma_mon = :ma_mon");
                    $stmt_old->execute([':ma_mon' => $ma_mon]);
                    $old_data = $stmt_old->fetch(PDO::FETCH_ASSOC);
                    
                    if (!$old_data) {
                        echo json_encode(['success' => false, 'message' => 'Học phần không tồn tại.'], JSON_UNESCAPED_UNICODE);
                        exit;
                    }
                    
                    $stmt = $db->prepare("
                        UPDATE mon_hoc 
                        SET ten_mon = :ten, so_tin_chi = :tc, loai_mon_hoc = :loai, 
                            ma_khoa = :khoa, ma_nganh = :nganh, trang_thai = :status, updated_at = NOW()
                        WHERE ma_mon = :ma_mon
                    ");
                    $stmt->execute([
                        ':ten' => $ten_mon,
                        ':tc' => $so_tin_chi,
                        ':loai' => $loai_mon_hoc,
                        ':khoa' => $ma_khoa,
                        ':nganh' => $ma_nganh,
                        ':status' => $trang_thai,
                        ':ma_mon' => $ma_mon
                    ]);
                    
                    $stmt_new = $db->prepare("SELECT * FROM mon_hoc WHERE ma_mon = :ma_mon");
                    $stmt_new->execute([':ma_mon' => $ma_mon]);
                    $new_data = $stmt_new->fetch(PDO::FETCH_ASSOC);
                    logAdminAction($db, 'mon_hoc', $ma_mon, 'UPDATE', $old_data, $new_data);
                    
                    echo json_encode(['success' => true, 'message' => 'Cập nhật học phần thành công!'], JSON_UNESCAPED_UNICODE);
                } else {
                    // Thêm mới
                    $stmt_check = $db->prepare("SELECT ma_mon FROM mon_hoc WHERE ma_mon = :ma_mon");
                    $stmt_check->execute([':ma_mon' => $ma_mon]);
                    if ($stmt_check->fetch()) {
                        echo json_encode(['success' => false, 'message' => 'Mã học phần này đã tồn tại.'], JSON_UNESCAPED_UNICODE);
                        exit;
                    }
                    
                    $stmt = $db->prepare("
                        INSERT INTO mon_hoc (ma_mon, ten_mon, so_tin_chi, loai_mon_hoc, ma_khoa, ma_nganh, trang_thai)
                        VALUES (:ma_mon, :ten, :tc, :loai, :khoa, :nganh, :status)
                    ");
                    $stmt->execute([
                        ':ma_mon' => $ma_mon,
                        ':ten' => $ten_mon,
                        ':tc' => $so_tin_chi,
                        ':loai' => $loai_mon_hoc,
                        ':khoa' => $ma_khoa,
                        ':nganh' => $ma_nganh,
                        ':status' => $trang_thai
                    ]);
                    
                    $stmt_new = $db->prepare("SELECT * FROM mon_hoc WHERE ma_mon = :ma_mon");
                    $stmt_new->execute([':ma_mon' => $ma_mon]);
                    $new_data = $stmt_new->fetch(PDO::FETCH_ASSOC);
                    logAdminAction($db, 'mon_hoc', $ma_mon, 'INSERT', null, $new_data);
                    
                    echo json_encode(['success' => true, 'message' => 'Thêm học phần mới thành công!'], JSON_UNESCAPED_UNICODE);
                }
            } 
            
            elseif ($action === 'add_lop_hoc_phan') {
                // Thêm lớp học phần + Xếp lịch học
                $ma_lhp = trim($input['ma_lhp'] ?? '');
                $ma_mon = trim($input['ma_mon'] ?? '');
                $ma_gv = trim($input['ma_gv'] ?? '');
                $id_hoc_ky = trim($input['id_hoc_ky'] ?? '');
                $ten_lop = trim($input['ten_lop'] ?? '');
                $si_so_max = intval($input['si_so_toi_da'] ?? 50);
                if ($ngay_bat_dau) {
                    $y_start = (int)(new DateTime($ngay_bat_dau))->format('Y');
                    if ($y_start < 2024) {
                        echo json_encode(['success' => false, 'message' => 'Năm bắt đầu lớp học phần không được nhỏ hơn năm 2024! Chỉ cho phép mở lớp từ năm 2024 trở về sau (không được thêm năm 2023 trở về trước).'], JSON_UNESCAPED_UNICODE);
                        exit;
                    }
                }
                $trang_thai = trim($input['trang_thai'] ?? 'CHO_DUYET');
                
                // Ca học
                $thu = intval($input['thu'] ?? 2); // 2 -> 7
                $tiet_bat_dau = intval($input['tiet_bat_dau'] ?? 1);
                $tiet_ket_thuc = intval($input['tiet_ket_thuc'] ?? 3);
                $phong = trim($input['phong'] ?? 'A101');
                
                if (!$ma_lhp || !$ma_mon || !$ma_gv || !$id_hoc_ky) {
                    echo json_encode(['success' => false, 'message' => 'Vui lòng nhập đầy đủ Mã lớp, Học phần, Giảng viên và Học kỳ.'], JSON_UNESCAPED_UNICODE);
                    exit;
                }
                
                if ($tiet_bat_dau > $tiet_ket_thuc) {
                    echo json_encode(['success' => false, 'message' => 'Tiết bắt đầu không được lớn hơn tiết kết thúc.'], JSON_UNESCAPED_UNICODE);
                    exit;
                }
                
                $db->beginTransaction();
                
                // Lấy UUID của các tiết học tương ứng
                $stmt_tiets = $db->prepare("
                    SELECT id_tiet, so_tiet FROM tiet_hoc 
                    WHERE so_tiet BETWEEN :start_t AND :end_t
                ");
                $stmt_tiets->execute([':start_t' => $tiet_bat_dau, ':end_t' => $tiet_ket_thuc]);
                $tiets = $stmt_tiets->fetchAll(PDO::FETCH_ASSOC);
                
                if (empty($tiets)) {
                    $db->rollBack();
                    echo json_encode(['success' => false, 'message' => 'Cơ cấu tiết học không tồn tại trong hệ thống.'], JSON_UNESCAPED_UNICODE);
                    exit;
                }
                
                // Kiểm tra trùng lịch giảng viên trong cùng kỳ
                foreach ($tiets as $tiet) {
                    $stmt_check_lh = $db->prepare("
                        SELECT lhp.ma_lhp, mh.ten_mon 
                        FROM lich_hoc lh
                        JOIN lop_hoc_phan lhp ON lh.ma_lhp = lhp.ma_lhp
                        JOIN mon_hoc mh ON lhp.ma_mon = mh.ma_mon
                        WHERE lhp.ma_gv = :ma_gv AND lhp.id_hoc_ky = :id_hk AND lh.thu = :thu AND lh.id_tiet = :id_tiet
                    ");
                    $stmt_check_lh->execute([
                        ':ma_gv' => $ma_gv,
                        ':id_hk' => $id_hoc_ky,
                        ':thu' => $thu,
                        ':id_tiet' => $tiet['id_tiet']
                    ]);
                    $conflict = $stmt_check_lh->fetch();
                    if ($conflict) {
                        $db->rollBack();
                        echo json_encode([
                            'success' => false, 
                            'message' => "Giảng viên đã có lịch giảng dạy lớp '{$conflict['ma_lhp']}' ({$conflict['ten_mon']}) tại Thứ {$thu}, Tiết {$tiet['so_tiet']}."
                        ], JSON_UNESCAPED_UNICODE);
                        exit;
                    }
                }
                
                // Tạo lớp học phần
                $stmt_ins = $db->prepare("
                    INSERT INTO lop_hoc_phan (ma_lhp, ma_mon, id_hoc_ky, ma_gv, ten_lop, si_so_toi_da, trang_thai, ngay_bat_dau, ngay_ket_thuc)
                    VALUES (:ma_lhp, :ma_mon, :id_hk, :ma_gv, :ten, :si_so, :status, :start_d, :end_d)
                ");
                $stmt_ins->execute([
                    ':ma_lhp' => $ma_lhp,
                    ':ma_mon' => $ma_mon,
                    ':id_hk' => $id_hoc_ky,
                    ':ma_gv' => $ma_gv,
                    ':ten' => $ten_lop ? $ten_lop : $ma_lhp,
                    ':si_so' => $si_so_max,
                    ':status' => $trang_thai,
                    ':start_d' => $ngay_bat_dau ? $ngay_bat_dau : null,
                    ':end_d' => $ngay_ket_thuc ? $ngay_ket_thuc : null
                ]);
                
                // Chèn các slot lịch học tương ứng
                $stmt_ins_lh = $db->prepare("
                    INSERT INTO lich_hoc (ma_lhp, thu, id_tiet, phong)
                    VALUES (:ma_lhp, :thu, :id_tiet, :phong)
                ");
                foreach ($tiets as $tiet) {
                    $stmt_ins_lh->execute([
                        ':ma_lhp' => $ma_lhp,
                        ':thu' => $thu,
                        ':id_tiet' => $tiet['id_tiet'],
                        ':phong' => $phong
                    ]);
                }
                
                $db->commit();
                
                $stmt_new = $db->prepare("SELECT * FROM lop_hoc_phan WHERE ma_lhp = :ma");
                $stmt_new->execute([':ma' => $ma_lhp]);
                $new_data = $stmt_new->fetch(PDO::FETCH_ASSOC);
                logAdminAction($db, 'lop_hoc_phan', $ma_lhp, 'INSERT', null, $new_data);
                
                echo json_encode(['success' => true, 'message' => 'Tạo lớp học phần và xếp ca học thành công!'], JSON_UNESCAPED_UNICODE);
            } 
            
            elseif ($action === 'approve_class') {
                // Duyệt mở lớp học phần
                $ma_lhp = trim($input['ma_lhp'] ?? '');
                $status = trim($input['trang_thai'] ?? 'DA_DUYET'); // DA_DUYET hoặc TU_CHOI
                
                if (!$ma_lhp) {
                    echo json_encode(['success' => false, 'message' => 'Thiếu mã lớp học phần cần duyệt.'], JSON_UNESCAPED_UNICODE);
                    exit;
                }
                
                // Mặc định chuyển sang CHUA_MO (chờ mở cổng) hoặc DANG_MO (đang đăng ký)
                $db_status = 'CHUA_MO';
                if ($status === 'TU_CHOI') $db_status = 'TU_CHOI';
                if ($status === 'DANG_MO') $db_status = 'DANG_MO';
                
                $stmt_old = $db->prepare("SELECT * FROM lop_hoc_phan WHERE ma_lhp = :ma");
                $stmt_old->execute([':ma' => $ma_lhp]);
                $old_data = $stmt_old->fetch(PDO::FETCH_ASSOC);
                
                if (!$old_data) {
                    echo json_encode(['success' => false, 'message' => 'Lớp học phần không tồn tại.'], JSON_UNESCAPED_UNICODE);
                    exit;
                }
                
                $stmt = $db->prepare("UPDATE lop_hoc_phan SET trang_thai = :status, updated_at = NOW() WHERE ma_lhp = :ma");
                $stmt->execute([':status' => $db_status, ':ma' => $ma_lhp]);
                
                $stmt_new = $db->prepare("SELECT * FROM lop_hoc_phan WHERE ma_lhp = :ma");
                $stmt_new->execute([':ma' => $ma_lhp]);
                $new_data = $stmt_new->fetch(PDO::FETCH_ASSOC);
                logAdminAction($db, 'lop_hoc_phan', $ma_lhp, 'UPDATE', $old_data, $new_data);
                
                $msg = $db_status === 'TU_CHOI' ? 'Đã từ chối mở lớp học phần.' : 'Duyệt mở lớp học phần thành công!';
                echo json_encode(['success' => true, 'message' => $msg], JSON_UNESCAPED_UNICODE);
            }
            
        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
        break;

    case 'DELETE':
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $action = trim($input['action'] ?? '');
            
            if ($action === 'delete_mon_hoc') {
                $ma_mon = trim($input['ma_mon'] ?? '');
                
                $stmt_old = $db->prepare("SELECT * FROM mon_hoc WHERE ma_mon = :ma");
                $stmt_old->execute([':ma' => $ma_mon]);
                $old_data = $stmt_old->fetch(PDO::FETCH_ASSOC);
                
                if (!$old_data) {
                    echo json_encode(['success' => false, 'message' => 'Học phần không tồn tại.'], JSON_UNESCAPED_UNICODE);
                    exit;
                }
                
                $stmt = $db->prepare("DELETE FROM mon_hoc WHERE ma_mon = :ma");
                $stmt->execute([':ma' => $ma_mon]);
                
                logAdminAction($db, 'mon_hoc', $ma_mon, 'DELETE', $old_data, null);
                echo json_encode(['success' => true, 'message' => 'Xóa học phần thành công!'], JSON_UNESCAPED_UNICODE);
            } 
            
            elseif ($action === 'delete_lop_hoc_phan') {
                $ma_lhp = trim($input['ma_lhp'] ?? '');
                
                $stmt_old = $db->prepare("SELECT * FROM lop_hoc_phan WHERE ma_lhp = :ma");
                $stmt_old->execute([':ma' => $ma_lhp]);
                $old_data = $stmt_old->fetch(PDO::FETCH_ASSOC);
                
                if (!$old_data) {
                    echo json_encode(['success' => false, 'message' => 'Lớp học phần không tồn tại.'], JSON_UNESCAPED_UNICODE);
                    exit;
                }
                
                $db->beginTransaction();
                // Xóa lịch học
                $stmt_lh = $db->prepare("DELETE FROM lich_hoc WHERE ma_lhp = :ma");
                $stmt_lh->execute([':ma' => $ma_lhp]);
                
                // Xóa lớp
                $stmt = $db->prepare("DELETE FROM lop_hoc_phan WHERE ma_lhp = :ma");
                $stmt->execute([':ma' => $ma_lhp]);
                
                $db->commit();
                
                logAdminAction($db, 'lop_hoc_phan', $ma_lhp, 'DELETE', $old_data, null);
                echo json_encode(['success' => true, 'message' => 'Hủy/Xóa lớp học phần thành công!'], JSON_UNESCAPED_UNICODE);
            }
        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Phương thức HTTP không hợp lệ.'], JSON_UNESCAPED_UNICODE);
}
