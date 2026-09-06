<?php
// api/admin/thong_bao_action.php
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
            $keyword = trim($_GET['q'] ?? '');
            $target = trim($_GET['doi_tuong'] ?? '');
            
            $sql = "SELECT * FROM thong_bao WHERE 1=1";
            $params = [];
            
            if ($keyword !== '') {
                $sql .= " AND (tieu_de ILIKE :q OR noi_dung ILIKE :q)";
                $params[':q'] = "%{$keyword}%";
            }
            if ($target !== '' && $target !== 'Tất cả') {
                $sql .= " AND doi_tuong_nhan = :target";
                // Chuyển đổi tên hiển thị sang tên CSDL
                $db_target = 'TAT_CA';
                if ($target === 'Sinh viên') $db_target = 'SINH_VIEN';
                if ($target === 'Giảng viên') $db_target = 'GIANG_VIEN';
                $params[':target'] = $db_target;
            }
            
            $sql .= " ORDER BY ngay_dang DESC";
            
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
            $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
            $id = isset($input['id']) ? intval($input['id']) : 0;
            $tieu_de = trim($input['tieu_de'] ?? '');
            $doi_tuong = trim($input['doi_tuong_nhan'] ?? 'TAT_CA');
            $noi_dung = trim($input['noi_dung'] ?? '');
            $trang_thai = trim($input['trang_thai'] ?? 'BAN_NHAP');
            
            if (!$tieu_de || !$noi_dung) {
                echo json_encode(['success' => false, 'message' => 'Vui lòng nhập đầy đủ tiêu đề và nội dung.'], JSON_UNESCAPED_UNICODE);
                exit;
            }
            
            // Xử lý upload file đính kèm
            $file_path = null;
            if (isset($_FILES['file_dinh_kem']) && $_FILES['file_dinh_kem']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = '../../assets/uploads/notifications/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                $file_name = time() . '_' . basename($_FILES['file_dinh_kem']['name']);
                $target_file = $upload_dir . $file_name;
                
                if (move_uploaded_file($_FILES['file_dinh_kem']['tmp_name'], $target_file)) {
                    $file_path = 'assets/uploads/notifications/' . $file_name;
                }
            }
            
            if ($id > 0) {
                // Sửa thông báo
                $stmt_old = $db->prepare("SELECT * FROM thong_bao WHERE id = :id");
                $stmt_old->execute([':id' => $id]);
                $old_data = $stmt_old->fetch(PDO::FETCH_ASSOC);
                
                if (!$old_data) {
                    echo json_encode(['success' => false, 'message' => 'Thông báo không tồn tại.'], JSON_UNESCAPED_UNICODE);
                    exit;
                }
                
                $sql = "UPDATE thong_bao SET tieu_de = :tieu_de, doi_tuong_nhan = :doi_tuong, noi_dung = :noi_dung, trang_thai = :trang_thai";
                $params = [
                    ':tieu_de' => $tieu_de,
                    ':doi_tuong' => $doi_tuong,
                    ':noi_dung' => $noi_dung,
                    ':trang_thai' => $trang_thai,
                    ':id' => $id
                ];
                
                if ($file_path !== null) {
                    $sql .= ", file_dinh_kem = :file_path";
                    $params[':file_path'] = $file_path;
                }
                
                $sql .= " WHERE id = :id";
                $stmt = $db->prepare($sql);
                $stmt->execute($params);
                
                // Ghi log hoạt động
                $stmt_new = $db->prepare("SELECT * FROM thong_bao WHERE id = :id");
                $stmt_new->execute([':id' => $id]);
                $new_data = $stmt_new->fetch(PDO::FETCH_ASSOC);
                logAdminAction($db, 'thong_bao', $id, 'UPDATE', $old_data, $new_data);
                
                echo json_encode(['success' => true, 'message' => 'Cập nhật thông báo thành công!'], JSON_UNESCAPED_UNICODE);
            } else {
                // Thêm mới thông báo
                $stmt = $db->prepare("
                    INSERT INTO thong_bao (tieu_de, doi_tuong_nhan, noi_dung, trang_thai, file_dinh_kem, ngay_dang)
                    VALUES (:tieu_de, :doi_tuong, :noi_dung, :trang_thai, :file_path, NOW())
                    RETURNING id
                ");
                $stmt->execute([
                    ':tieu_de' => $tieu_de,
                    ':doi_tuong' => $doi_tuong,
                    ':noi_dung' => $noi_dung,
                    ':trang_thai' => $trang_thai,
                    ':file_path' => $file_path
                ]);
                $new_id = $stmt->fetchColumn();
                
                // Ghi log hoạt động
                $stmt_new = $db->prepare("SELECT * FROM thong_bao WHERE id = :id");
                $stmt_new->execute([':id' => $new_id]);
                $new_data = $stmt_new->fetch(PDO::FETCH_ASSOC);
                logAdminAction($db, 'thong_bao', $new_id, 'INSERT', null, $new_data);
                
                echo json_encode(['success' => true, 'message' => 'Đăng thông báo mới thành công!'], JSON_UNESCAPED_UNICODE);
            }
            
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
        break;

    case 'DELETE':
        try {
            // Delete request payload parsing (JSON)
            $input = json_decode(file_get_contents('php://input'), true);
            $id = isset($input['id']) ? intval($input['id']) : 0;
            
            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Mã thông báo không hợp lệ.'], JSON_UNESCAPED_UNICODE);
                exit;
            }
            
            $stmt_old = $db->prepare("SELECT * FROM thong_bao WHERE id = :id");
            $stmt_old->execute([':id' => $id]);
            $old_data = $stmt_old->fetch(PDO::FETCH_ASSOC);
            
            if (!$old_data) {
                echo json_encode(['success' => false, 'message' => 'Thông báo không tồn tại.'], JSON_UNESCAPED_UNICODE);
                exit;
            }
            
            // Xóa file vật lý nếu có
            if ($old_data['file_dinh_kem'] && file_exists('../../' . $old_data['file_dinh_kem'])) {
                unlink('../../' . $old_data['file_dinh_kem']);
            }
            
            $stmt = $db->prepare("DELETE FROM thong_bao WHERE id = :id");
            $stmt->execute([':id' => $id]);
            
            // Ghi log
            logAdminAction($db, 'thong_bao', $id, 'DELETE', $old_data, null);
            
            echo json_encode(['success' => true, 'message' => 'Xóa thông báo thành công!'], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Phương thức HTTP không hợp lệ.'], JSON_UNESCAPED_UNICODE);
}
