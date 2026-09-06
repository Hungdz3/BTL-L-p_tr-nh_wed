<?php
// api/admin/dot_dang_ky_action.php
header('Content-Type: application/json; charset=utf-8');
require_once '../../config/db.php';

$db = getDB();
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
            // Lấy danh sách học kỳ để populate vào select option của modal
            if (isset($_GET['get_semesters'])) {
                $stmt = $db->query("SELECT id_hoc_ky, ten_hoc_ky, nam_hoc FROM hoc_ky ORDER BY nam_hoc DESC, ten_hoc_ky DESC");
                $semesters = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode(['success' => true, 'data' => $semesters], JSON_UNESCAPED_UNICODE);
                exit;
            }
            
            $keyword = trim($_GET['q'] ?? '');
            
            $sql = "
                SELECT d.*, h.ten_hoc_ky, h.nam_hoc 
                FROM dot_dang_ky d
                JOIN hoc_ky h ON d.id_hoc_ky = h.id_hoc_ky
                WHERE 1=1
            ";
            $params = [];
            
            if ($keyword !== '') {
                $sql .= " AND (d.ten_dot_dk ILIKE :q OR d.ma_dot_dk ILIKE :q)";
                $params[':q'] = "%{$keyword}%";
            }
            
            $sql .= " ORDER BY d.created_at DESC";
            
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
            // Nhận dữ liệu POST dạng JSON
            $input = json_decode(file_get_contents('php://input'), true);
            
            $ma_dot_dk = trim($input['ma_dot_dk'] ?? '');
            $ten_dot_dk = trim($input['ten_dot_dk'] ?? '');
            $id_hoc_ky = trim($input['id_hoc_ky'] ?? '');
            $ngay_bat_dau = trim($input['ngay_bat_dau'] ?? '');
            $ngay_ket_thuc = trim($input['ngay_ket_thuc'] ?? '');
            $so_tin_chi_toi_da = isset($input['so_tin_chi_toi_da']) ? intval($input['so_tin_chi_toi_da']) : 24;
            $trang_thai = trim($input['trang_thai'] ?? 'CHO_DUYET');
            $ghi_chu = trim($input['ghi_chu'] ?? '');
            $is_update = isset($input['is_update']) && $input['is_update'] == true;
            
            if (!$ma_dot_dk || !$ten_dot_dk || !$id_hoc_ky || !$ngay_bat_dau || !$ngay_ket_thuc) {
                echo json_encode(['success' => false, 'message' => 'Vui lòng điền đầy đủ các thông tin bắt buộc.'], JSON_UNESCAPED_UNICODE);
                exit;
            }
            
            // Validate ngày tháng & Năm học >= 2024
            $start_dt = new DateTime($ngay_bat_dau);
            $end_dt = new DateTime($ngay_ket_thuc);
            if ($start_dt >= $end_dt) {
                echo json_encode(['success' => false, 'message' => 'Ngày bắt đầu phải trước ngày kết thúc.'], JSON_UNESCAPED_UNICODE);
                exit;
            }
            
            $start_year = (int)$start_dt->format('Y');
            $end_year = (int)$end_dt->format('Y');
            if ($start_year < 2024 || $end_year < 2024) {
                echo json_encode(['success' => false, 'message' => 'Năm đợt đăng ký không được nhỏ hơn năm 2024! Chỉ cho phép mở đợt từ năm 2024 trở về sau (không được thêm năm 2023 trở về trước).'], JSON_UNESCAPED_UNICODE);
                exit;
            }
            
            if ($is_update) {
                // Sửa đợt đăng ký
                $stmt_old = $db->prepare("SELECT * FROM dot_dang_ky WHERE ma_dot_dk = :ma_dot");
                $stmt_old->execute([':ma_dot' => $ma_dot_dk]);
                $old_data = $stmt_old->fetch(PDO::FETCH_ASSOC);
                
                if (!$old_data) {
                    echo json_encode(['success' => false, 'message' => 'Đợt đăng ký không tồn tại.'], JSON_UNESCAPED_UNICODE);
                    exit;
                }
                
                $stmt = $db->prepare("
                    UPDATE dot_dang_ky 
                    SET ten_dot_dk = :ten, id_hoc_ky = :id_hk, ngay_bat_dau = :start_t, ngay_ket_thuc = :end_t, 
                        so_tin_chi_toi_da = :max_tc, trang_thai = :status, ghi_chu = :note, updated_at = NOW()
                    WHERE ma_dot_dk = :ma_dot
                ");
                $stmt->execute([
                    ':ten' => $ten_dot_dk,
                    ':id_hk' => $id_hoc_ky,
                    ':start_t' => $ngay_bat_dau,
                    ':end_t' => $ngay_ket_thuc,
                    ':max_tc' => $so_tin_chi_toi_da,
                    ':status' => $trang_thai,
                    ':note' => $ghi_chu,
                    ':ma_dot' => $ma_dot_dk
                ]);
                
                // Ghi log
                $stmt_new = $db->prepare("SELECT * FROM dot_dang_ky WHERE ma_dot_dk = :ma_dot");
                $stmt_new->execute([':ma_dot' => $ma_dot_dk]);
                $new_data = $stmt_new->fetch(PDO::FETCH_ASSOC);
                logAdminAction($db, 'dot_dang_ky', $ma_dot_dk, 'UPDATE', $old_data, $new_data);
                
                echo json_encode(['success' => true, 'message' => 'Cập nhật đợt đăng ký học phần thành công!'], JSON_UNESCAPED_UNICODE);
            } else {
                // Thêm mới đợt đăng ký
                // Kiểm tra trùng mã đợt
                $stmt_check = $db->prepare("SELECT ma_dot_dk FROM dot_dang_ky WHERE ma_dot_dk = :ma_dot");
                $stmt_check->execute([':ma_dot' => $ma_dot_dk]);
                if ($stmt_check->fetch()) {
                    echo json_encode(['success' => false, 'message' => 'Mã đợt đăng ký này đã tồn tại.'], JSON_UNESCAPED_UNICODE);
                    exit;
                }
                
                $stmt = $db->prepare("
                    INSERT INTO dot_dang_ky (ma_dot_dk, ten_dot_dk, id_hoc_ky, ngay_bat_dau, ngay_ket_thuc, so_tin_chi_toi_da, trang_thai, ghi_chu)
                    VALUES (:ma_dot, :ten, :id_hk, :start_t, :end_t, :max_tc, :status, :note)
                ");
                $stmt->execute([
                    ':ma_dot' => $ma_dot_dk,
                    ':ten' => $ten_dot_dk,
                    ':id_hk' => $id_hoc_ky,
                    ':start_t' => $ngay_bat_dau,
                    ':end_t' => $ngay_ket_thuc,
                    ':max_tc' => $so_tin_chi_toi_da,
                    ':status' => $trang_thai,
                    ':note' => $ghi_chu
                ]);
                
                // Ghi log
                $stmt_new = $db->prepare("SELECT * FROM dot_dang_ky WHERE ma_dot_dk = :ma_dot");
                $stmt_new->execute([':ma_dot' => $ma_dot_dk]);
                $new_data = $stmt_new->fetch(PDO::FETCH_ASSOC);
                logAdminAction($db, 'dot_dang_ky', $ma_dot_dk, 'INSERT', null, $new_data);
                
                echo json_encode(['success' => true, 'message' => 'Mở đợt đăng ký học phần mới thành công!'], JSON_UNESCAPED_UNICODE);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
        break;

    case 'DELETE':
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $ma_dot_dk = trim($input['ma_dot_dk'] ?? '');
            
            if (!$ma_dot_dk) {
                echo json_encode(['success' => false, 'message' => 'Mã đợt đăng ký không hợp lệ.'], JSON_UNESCAPED_UNICODE);
                exit;
            }
            
            $stmt_old = $db->prepare("SELECT * FROM dot_dang_ky WHERE ma_dot_dk = :ma_dot");
            $stmt_old->execute([':ma_dot' => $ma_dot_dk]);
            $old_data = $stmt_old->fetch(PDO::FETCH_ASSOC);
            
            if (!$old_data) {
                echo json_encode(['success' => false, 'message' => 'Đợt đăng ký không tồn tại.'], JSON_UNESCAPED_UNICODE);
                exit;
            }
            
            $stmt = $db->prepare("DELETE FROM dot_dang_ky WHERE ma_dot_dk = :ma_dot");
            $stmt->execute([':ma_dot' => $ma_dot_dk]);
            
            // Ghi log
            logAdminAction($db, 'dot_dang_ky', $ma_dot_dk, 'DELETE', $old_data, null);
            
            echo json_encode(['success' => true, 'message' => 'Xóa đợt đăng ký thành công!'], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Phương thức HTTP không hợp lệ.'], JSON_UNESCAPED_UNICODE);
}
