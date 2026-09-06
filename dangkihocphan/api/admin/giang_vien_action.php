<?php
// api/admin/giang_vien_action.php
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

// Lấy vai_tro_id của giảng viên
function getTeacherRoleId($db) {
    $stmt = $db->query("SELECT id FROM vai_tro WHERE ten ILIKE '%giao%' OR ten ILIKE '%giang%' OR ten ILIKE '%teacher%' LIMIT 1");
    $id = $stmt->fetchColumn();
    if (!$id) {
        $stmt_ins = $db->query("INSERT INTO vai_tro (ten, mo_ta) VALUES ('GIANG_VIEN', 'Vai trò giảng viên') RETURNING id");
        $id = $stmt_ins->fetchColumn();
    }
    return $id;
}

switch ($method) {
    case 'GET':
        try {
            $keyword = trim($_GET['q'] ?? '');
            $khoa = trim($_GET['khoa'] ?? '');
            
            $sql = "
                SELECT 
                    gv.ma_gv, gv.ho_ten, gv.email, gv.so_dien_thoai, gv.trang_thai,
                    k.ten_khoa, k.ma_khoa
                FROM giao_vien gv
                JOIN khoa k ON gv.ma_khoa = k.ma_khoa
                WHERE 1=1
            ";
            $params = [];
            
            if ($keyword !== '') {
                $sql .= " AND (gv.ma_gv ILIKE :q OR gv.ho_ten ILIKE :q OR gv.email ILIKE :q)";
                $params[':q'] = "%{$keyword}%";
            }
            if ($khoa !== '') {
                $sql .= " AND k.ma_khoa = :khoa";
                $params[':khoa'] = $khoa;
            }
            
            $sql .= " ORDER BY gv.ma_gv ASC";
            
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
            $input = json_decode(file_get_contents('php://input'), true);
            
            $ma_gv = trim($input['ma_gv'] ?? '');
            $ho_ten = trim($input['ho_ten'] ?? '');
            $email = trim($input['email'] ?? '');
            $sdt = trim($input['so_dien_thoai'] ?? '');
            $ma_khoa = trim($input['ma_khoa'] ?? '');
            $trang_thai = trim($input['trang_thai'] ?? 'HOAT_DONG');
            $is_update = isset($input['is_update']) && $input['is_update'] == true;
            
            if (!$ma_gv || !$ho_ten || !$ma_khoa) {
                echo json_encode(['success' => false, 'message' => 'Mã giảng viên, Họ tên và Khoa là bắt buộc.'], JSON_UNESCAPED_UNICODE);
                exit;
            }
            
            if ($is_update) {
                // Cập nhật thông tin giảng viên
                $stmt_old = $db->prepare("SELECT * FROM giao_vien WHERE ma_gv = :ma_gv");
                $stmt_old->execute([':ma_gv' => $ma_gv]);
                $old_data = $stmt_old->fetch(PDO::FETCH_ASSOC);
                
                if (!$old_data) {
                    echo json_encode(['success' => false, 'message' => 'Giảng viên không tồn tại.'], JSON_UNESCAPED_UNICODE);
                    exit;
                }
                
                $db->beginTransaction();
                
                $stmt = $db->prepare("
                    UPDATE giao_vien 
                    SET ho_ten = :ten, email = :email, so_dien_thoai = :sdt, ma_khoa = :khoa, 
                        trang_thai = :status, updated_at = NOW()
                    WHERE ma_gv = :ma_gv
                ");
                $stmt->execute([
                    ':ten' => $ho_ten,
                    ':email' => $email,
                    ':sdt' => $sdt,
                    ':khoa' => $ma_khoa,
                    ':status' => $trang_thai,
                    ':ma_gv' => $ma_gv
                ]);
                
                // Cập nhật email trong bảng tài khoản
                $stmt_tk = $db->prepare("UPDATE tai_khoan SET email = :email, updated_at = NOW() WHERE id = :tk_id");
                $stmt_tk->execute([':email' => $email, ':tk_id' => $old_data['tai_khoan_id']]);
                
                $db->commit();
                
                $stmt_new = $db->prepare("SELECT * FROM giao_vien WHERE ma_gv = :ma_gv");
                $stmt_new->execute([':ma_gv' => $ma_gv]);
                $new_data = $stmt_new->fetch(PDO::FETCH_ASSOC);
                logAdminAction($db, 'giao_vien', $ma_gv, 'UPDATE', $old_data, $new_data);
                
                echo json_encode(['success' => true, 'message' => 'Cập nhật giảng viên thành công!'], JSON_UNESCAPED_UNICODE);
            } else {
                // Thêm giảng viên mới
                $stmt_check = $db->prepare("SELECT ma_gv FROM giao_vien WHERE ma_gv = :ma_gv");
                $stmt_check->execute([':ma_gv' => $ma_gv]);
                if ($stmt_check->fetch()) {
                    echo json_encode(['success' => false, 'message' => 'Mã giảng viên đã tồn tại.'], JSON_UNESCAPED_UNICODE);
                    exit;
                }
                
                $db->beginTransaction();
                $role_id = getTeacherRoleId($db);
                
                $raw_pass = trim($input['mat_khau'] ?? $input['password'] ?? $ma_gv);
                $password_hash = password_hash($raw_pass, PASSWORD_DEFAULT);
                $stmt_tk = $db->prepare("
                    INSERT INTO tai_khoan (username, password_hash, email, vai_tro_id, trang_thai)
                    VALUES (:username, :pass, :email, :role_id, 'HOAT_DONG')
                    RETURNING id
                ");
                $stmt_tk->execute([
                    ':username' => $ma_gv,
                    ':pass' => $password_hash,
                    ':email' => $email,
                    ':role_id' => $role_id
                ]);
                $tk_id = $stmt_tk->fetchColumn();
                
                $stmt_gv = $db->prepare("
                    INSERT INTO giao_vien (ma_gv, tai_khoan_id, ho_ten, email, so_dien_thoai, ma_khoa, trang_thai)
                    VALUES (:ma_gv, :tk_id, :ho, :email, :sdt, :khoa, :status)
                ");
                $stmt_gv->execute([
                    ':ma_gv' => $ma_gv,
                    ':tk_id' => $tk_id,
                    ':ho' => $ho_ten,
                    ':email' => $email,
                    ':sdt' => $sdt,
                    ':khoa' => $ma_khoa,
                    ':status' => $trang_thai
                ]);
                
                $db->commit();
                
                $stmt_new = $db->prepare("SELECT * FROM giao_vien WHERE ma_gv = :ma_gv");
                $stmt_new->execute([':ma_gv' => $ma_gv]);
                $new_data = $stmt_new->fetch(PDO::FETCH_ASSOC);
                logAdminAction($db, 'giao_vien', $ma_gv, 'INSERT', null, $new_data);
                
                echo json_encode(['success' => true, 'message' => 'Thêm giảng viên thành công!'], JSON_UNESCAPED_UNICODE);
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
            $ma_gv = trim($input['ma_gv'] ?? '');
            
            if (!$ma_gv) {
                echo json_encode(['success' => false, 'message' => 'Mã giảng viên không hợp lệ.'], JSON_UNESCAPED_UNICODE);
                exit;
            }
            
            $stmt_old = $db->prepare("SELECT * FROM giao_vien WHERE ma_gv = :ma_gv");
            $stmt_old->execute([':ma_gv' => $ma_gv]);
            $old_data = $stmt_old->fetch(PDO::FETCH_ASSOC);
            
            if (!$old_data) {
                echo json_encode(['success' => false, 'message' => 'Giảng viên không tồn tại.'], JSON_UNESCAPED_UNICODE);
                exit;
            }
            
            $db->beginTransaction();
            
            // Xóa giảng viên
            $stmt = $db->prepare("DELETE FROM giao_vien WHERE ma_gv = :ma_gv");
            $stmt->execute([':ma_gv' => $ma_gv]);
            
            // Xóa tài khoản
            $stmt_tk = $db->prepare("DELETE FROM tai_khoan WHERE id = :tk_id");
            $stmt_tk->execute([':tk_id' => $old_data['tai_khoan_id']]);
            
            $db->commit();
            
            // Ghi log
            logAdminAction($db, 'giao_vien', $ma_gv, 'DELETE', $old_data, null);
            
            echo json_encode(['success' => true, 'message' => 'Xóa giảng viên thành công!'], JSON_UNESCAPED_UNICODE);
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
