<?php
// api/admin/sinh_vien_action.php
set_time_limit(300);
ini_set('memory_limit', '256M');
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);

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

// Lấy vai_tro_id của sinh viên
function getStudentRoleId($db) {
    $stmt = $db->query("SELECT id FROM vai_tro WHERE ten ILIKE '%sinh%' OR ten ILIKE '%student%' LIMIT 1");
    $id = $stmt->fetchColumn();
    if (!$id) {
        // Fallback tự sinh role nếu chưa có
        $stmt_ins = $db->query("INSERT INTO vai_tro (ten, mo_ta) VALUES ('SINH_VIEN', 'Vai trò sinh viên') RETURNING id");
        $id = $stmt_ins->fetchColumn();
    }
    return $id;
}

switch ($method) {
    case 'GET':
        try {
            // Option 1: Trả về bộ lọc (Khoa, Ngành, Lớp)
            if (isset($_GET['get_filters'])) {
                $khoas = $db->query("SELECT ma_khoa, ten_khoa FROM khoa WHERE trang_thai = 'HOAT_DONG' ORDER BY ten_khoa ASC")->fetchAll(PDO::FETCH_ASSOC);
                $nganhs = $db->query("SELECT ma_nganh, ten_nganh, ma_khoa FROM nganh WHERE trang_thai = 'HOAT_DONG' ORDER BY ten_nganh ASC")->fetchAll(PDO::FETCH_ASSOC);
                $lops = $db->query("SELECT ma_lop_sv, ten_lop, ma_nganh FROM lop_sinh_vien WHERE trang_thai = 'HOAT_DONG' ORDER BY ten_lop ASC")->fetchAll(PDO::FETCH_ASSOC);
                
                echo json_encode([
                    'success' => true,
                    'khoa' => $khoas,
                    'nganh' => $nganhs,
                    'lop' => $lops
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }
            
            // Option 2: Trả về danh sách sinh viên cùng bộ lọc tìm kiếm
            $keyword = trim($_GET['q'] ?? '');
            $khoa = trim($_GET['khoa'] ?? '');
            $nganh = trim($_GET['nganh'] ?? '');
            $lop = trim($_GET['lop'] ?? '');
            
            $sql = "
                SELECT 
                    sv.ma_sv, sv.ho_ten, sv.email, sv.so_dien_thoai, sv.ngay_sinh, sv.gioi_tinh, sv.trang_thai,
                    l.ten_lop, l.ma_lop_sv,
                    k.ten_khoa, k.ma_khoa,
                    n.ten_nganh, n.ma_nganh
                FROM sinh_vien sv
                JOIN lop_sinh_vien l ON sv.ma_lop_sv = l.ma_lop_sv
                JOIN nganh n ON l.ma_nganh = n.ma_nganh
                JOIN khoa k ON n.ma_khoa = k.ma_khoa
                WHERE 1=1
            ";
            $params = [];
            
            if ($keyword !== '') {
                $sql .= " AND (sv.ma_sv ILIKE :q OR sv.ho_ten ILIKE :q OR sv.email ILIKE :q)";
                $params[':q'] = "%{$keyword}%";
            }
            if ($khoa !== '') {
                $sql .= " AND k.ma_khoa = :khoa";
                $params[':khoa'] = $khoa;
            }
            if ($nganh !== '') {
                $sql .= " AND n.ma_nganh = :nganh";
                $params[':nganh'] = $nganh;
            }
            if ($lop !== '') {
                $sql .= " AND l.ma_lop_sv = :lop";
                $params[':lop'] = $lop;
            }
            
            $sql .= " ORDER BY sv.ma_sv ASC";
            
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
            // Phân biệt Thêm qua Form thường vs Thêm qua Excel/CSV
            $is_import = isset($_GET['import']) && $_GET['import'] == 1;
            
            if ($is_import) {
                // Nhập danh sách từ tệp CSV/Excel
                if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
                    echo json_encode(['success' => false, 'message' => 'Vui lòng chọn tệp CSV hợp lệ.'], JSON_UNESCAPED_UNICODE);
                    exit;
                }
                
                $file_tmp = $_FILES['file']['tmp_name'];
                $file_content = file_get_contents($file_tmp);
                if ($file_content === false) {
                    echo json_encode(['success' => false, 'message' => 'Không thể đọc tệp.'], JSON_UNESCAPED_UNICODE);
                    exit;
                }

                // Bỏ UTF-8 BOM nếu có
                if (substr($file_content, 0, 3) === "\xEF\xBB\xBF") {
                    $file_content = substr($file_content, 3);
                }

                $temp_stream = fopen('php://memory', 'r+');
                fwrite($temp_stream, $file_content);
                rewind($temp_stream);
                
                $role_id = getStudentRoleId($db);
                $password_hash = password_hash('123456', PASSWORD_DEFAULT);
                
                // Cache danh sách lớp có sẵn trong CSDL
                $stmt_all_lops = $db->query("SELECT ma_lop_sv, ten_lop FROM lop_sinh_vien");
                $lop_cache = [];
                foreach ($stmt_all_lops->fetchAll(PDO::FETCH_ASSOC) as $l) {
                    $lop_cache[strtoupper($l['ma_lop_sv'])] = $l['ma_lop_sv'];
                    $lop_cache[strtoupper($l['ten_lop'])] = $l['ma_lop_sv'];
                    $lop_cache[str_replace(' ', '', strtoupper($l['ten_lop']))] = $l['ma_lop_sv'];
                }

                $success_count = 0;
                $errors = [];
                $row_idx = 0;
                
                // Đọc hàng tiêu đề
                $headers = fgetcsv($temp_stream, 1000, ",", '"', "\\");
                
                while (($row = fgetcsv($temp_stream, 1000, ",", '"', "\\")) !== false) {
                    $row_idx++;
                    if (empty($row) || count($row) < 3 || empty(trim($row[0]))) {
                        continue;
                    }
                    
                    $ma_sv = trim($row[0]);
                    $ho_ten = trim($row[1]);
                    $email = trim($row[2]);
                    $so_dien_thoai = trim($row[3] ?? '');
                    $class_input = trim($row[4] ?? '');
                    $raw_ngay_sinh = trim($row[5] ?? '');
                    $raw_gt = mb_strtoupper(trim($row[6] ?? 'NAM'), 'UTF-8');
                    $raw_st = mb_strtoupper(trim($row[7] ?? 'DANG_HOC'), 'UTF-8');
                    
                    if (!$ma_sv || !$ho_ten) {
                        $errors[] = "Dòng {$row_idx}: Thiếu Mã SV hoặc Họ tên.";
                        continue;
                    }

                    // 1. Chuẩn hóa Giới tính
                    if (str_contains($raw_gt, 'NỮ') || $raw_gt === 'NU') {
                        $gioi_tinh = 'NU';
                    } elseif (str_contains($raw_gt, 'KHÁC') || $raw_gt === 'KHAC') {
                        $gioi_tinh = 'KHAC';
                    } else {
                        $gioi_tinh = 'NAM';
                    }

                    // 2. Chuẩn hóa Trạng thái
                    if (str_contains($raw_st, 'BẢO LƯU') || str_contains($raw_st, 'BAO LUU') || $raw_st === 'BAO_LUU') {
                        $trang_thai = 'BAO_LUU';
                    } elseif (str_contains($raw_st, 'TẠM DỪNG') || str_contains($raw_st, 'TAM DUNG') || $raw_st === 'TAM_DUNG') {
                        $trang_thai = 'TAM_DUNG';
                    } elseif (str_contains($raw_st, 'THÔI HỌC') || str_contains($raw_st, 'THOI HOC') || $raw_st === 'THOI_HOC') {
                        $trang_thai = 'THOI_HOC';
                    } elseif (str_contains($raw_st, 'TỐT NGHỆP') || str_contains($raw_st, 'TOT NGHIEP') || $raw_st === 'DA_TOT_NGHIEP') {
                        $trang_thai = 'DA_TOT_NGHIEP';
                    } else {
                        $trang_thai = 'DANG_HOC';
                    }

                    // 3. Chuẩn hóa Ngày sinh
                    $ngay_sinh = null;
                    if (!empty($raw_ngay_sinh)) {
                        if (preg_match('/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})$/', $raw_ngay_sinh, $m)) {
                            $ngay_sinh = sprintf('%04d-%02d-%02d', $m[3], $m[2], $m[1]);
                        } else if (strtotime($raw_ngay_sinh) !== false) {
                            $ngay_sinh = date('Y-m-d', strtotime($raw_ngay_sinh));
                        }
                    }
                    
                    try {
                        $db->beginTransaction();
                        
                        // 4. Tìm kiếm hoặc tự động tạo mã lớp
                        $ma_lop_sv = null;
                        if ($class_input !== '') {
                            $k_raw = strtoupper($class_input);
                            $k_nosp = str_replace(' ', '', $k_raw);
                            
                            if (isset($lop_cache[$k_raw])) {
                                $ma_lop_sv = $lop_cache[$k_raw];
                            } elseif (isset($lop_cache[$k_nosp])) {
                                $ma_lop_sv = $lop_cache[$k_nosp];
                            } else {
                                $clean_code = strtoupper(str_replace(' ', '-', $class_input));
                                $ma_lop_sv = $clean_code;

                                $default_nganh = 'KTPM';
                                if (str_contains(strtoupper($class_input), 'HTTT')) $default_nganh = 'HTTT';
                                if (str_contains(strtoupper($class_input), 'ATTT')) $default_nganh = 'ATTT';
                                if (str_contains(strtoupper($class_input), 'MKT')) $default_nganh = 'MKT';
                                if (str_contains(strtoupper($class_input), 'KTKT')) $default_nganh = 'KTKT';

                                $stmt_ins_lop = $db->prepare("
                                    INSERT INTO lop_sinh_vien (ma_lop_sv, ten_lop, ma_nganh, khoa_hoc, nam_nhap_hoc, trang_thai)
                                    VALUES (:ma_lop, :ten_lop, :ma_nganh, 'K65', 2022, 'HOAT_DONG')
                                    ON CONFLICT (ma_lop_sv) DO NOTHING
                                ");
                                $stmt_ins_lop->execute([
                                    ':ma_lop' => $ma_lop_sv,
                                    ':ten_lop' => $class_input,
                                    ':ma_nganh' => $default_nganh
                                ]);

                                $lop_cache[$k_raw] = $ma_lop_sv;
                                $lop_cache[$k_nosp] = $ma_lop_sv;
                            }
                        }

                        if (!$ma_lop_sv) {
                            $ma_lop_sv = 'K65-KTPM-A';
                        }

                        // 5. Kiểm tra sinh viên (UPSERT: Nếu đã có thì cập nhật, nếu chưa thì thêm mới)
                        $stmt_check_sv = $db->prepare("SELECT ma_sv, tai_khoan_id FROM sinh_vien WHERE ma_sv = :ma_sv");
                        $stmt_check_sv->execute([':ma_sv' => $ma_sv]);
                        $existing_sv = $stmt_check_sv->fetch(PDO::FETCH_ASSOC);

                        if ($existing_sv) {
                            $stmt_up_sv = $db->prepare("
                                UPDATE sinh_vien 
                                SET ho_ten = :ho_ten, email = :email, so_dien_thoai = :sdt, ma_lop_sv = :ma_lop, 
                                    ngay_sinh = :ngay_sinh, gioi_tinh = :gioi_tinh, trang_thai = :trang_thai, updated_at = NOW()
                                WHERE ma_sv = :ma_sv
                            ");
                            $stmt_up_sv->execute([
                                ':ho_ten' => $ho_ten,
                                ':email' => $email,
                                ':sdt' => $so_dien_thoai,
                                ':ma_lop' => $ma_lop_sv,
                                ':ngay_sinh' => $ngay_sinh,
                                ':gioi_tinh' => $gioi_tinh,
                                ':trang_thai' => $trang_thai,
                                ':ma_sv' => $ma_sv
                            ]);

                            if ($existing_sv['tai_khoan_id']) {
                                $stmt_up_tk = $db->prepare("UPDATE tai_khoan SET email = :email, updated_at = NOW() WHERE id = :tk_id");
                                $stmt_up_tk->execute([':email' => $email, ':tk_id' => $existing_sv['tai_khoan_id']]);
                            }
                        } else {
                            $stmt_tk = $db->prepare("
                                INSERT INTO tai_khoan (username, password_hash, email, vai_tro_id, trang_thai)
                                VALUES (:username, :pass, :email, :role_id, 'HOAT_DONG')
                                RETURNING id
                            ");
                            $stmt_tk->execute([
                                ':username' => $ma_sv,
                                ':pass' => $password_hash,
                                ':email' => $email,
                                ':role_id' => $role_id
                            ]);
                            $tk_id = $stmt_tk->fetchColumn();

                            $stmt_sv = $db->prepare("
                                INSERT INTO sinh_vien (ma_sv, tai_khoan_id, ho_ten, email, so_dien_thoai, ma_lop_sv, ngay_sinh, gioi_tinh, trang_thai)
                                VALUES (:ma_sv, :tk_id, :ho_ten, :email, :sdt, :ma_lop, :ngay_sinh, :gioi_tinh, :trang_thai)
                            ");
                            $stmt_sv->execute([
                                ':ma_sv' => $ma_sv,
                                ':tk_id' => $tk_id,
                                ':ho_ten' => $ho_ten,
                                ':email' => $email,
                                ':sdt' => $so_dien_thoai,
                                ':ma_lop' => $ma_lop_sv,
                                ':ngay_sinh' => $ngay_sinh,
                                ':gioi_tinh' => $gioi_tinh,
                                ':trang_thai' => $trang_thai
                            ]);
                        }
                        
                        $db->commit();
                        $success_count++;
                        logAdminAction($db, 'sinh_vien', $ma_sv, 'IMPORT', null, ['ma_sv' => $ma_sv, 'ho_ten' => $ho_ten]);
                        
                    } catch (Exception $ex) {
                        $db->rollBack();
                        $errors[] = "Dòng {$row_idx} ({$ma_sv}): Lỗi lưu database - " . $ex->getMessage();
                    }
                }
                
                fclose($temp_stream);
                echo json_encode([
                    'success' => true,
                    'message' => "Đã xử lý nhập dữ liệu thành công {$success_count} dòng.",
                    'errors' => $errors
                ], JSON_UNESCAPED_UNICODE);
                exit;
            } else {
                // Nhập form thủ công bình thường
                $input = json_decode(file_get_contents('php://input'), true);
                
                $ma_sv = trim($input['ma_sv'] ?? '');
                $ho_ten = trim($input['ho_ten'] ?? '');
                $email = trim($input['email'] ?? '');
                $sdt = trim($input['so_dien_thoai'] ?? '');
                $ma_lop = trim($input['ma_lop_sv'] ?? '');
                $ngay_sinh = trim($input['ngay_sinh'] ?? '');
                $raw_gt = mb_strtoupper(trim($input['gioi_tinh'] ?? 'NAM'), 'UTF-8');
                if (str_contains($raw_gt, 'NỮ') || $raw_gt === 'NU') {
                    $gioi_tinh = 'NU';
                } elseif (str_contains($raw_gt, 'KHÁC') || $raw_gt === 'KHAC') {
                    $gioi_tinh = 'KHAC';
                } else {
                    $gioi_tinh = 'NAM';
                }
                $trang_thai = trim($input['trang_thai'] ?? 'DANG_HOC');
                $is_update = isset($input['is_update']) && $input['is_update'] == true;
                
                if (!$ma_sv || !$ho_ten || !$ma_lop) {
                    echo json_encode(['success' => false, 'message' => 'Mã sinh viên, Họ tên và Lớp là bắt buộc.'], JSON_UNESCAPED_UNICODE);
                    exit;
                }
                
                if ($is_update) {
                    // Update sinh viên
                    $stmt_old = $db->prepare("SELECT * FROM sinh_vien WHERE ma_sv = :ma_sv");
                    $stmt_old->execute([':ma_sv' => $ma_sv]);
                    $old_data = $stmt_old->fetch(PDO::FETCH_ASSOC);
                    
                    if (!$old_data) {
                        echo json_encode(['success' => false, 'message' => 'Sinh viên không tồn tại.'], JSON_UNESCAPED_UNICODE);
                        exit;
                    }
                    
                    $db->beginTransaction();
                    
                    // Cập nhật bảng sinh viên
                    $stmt = $db->prepare("
                        UPDATE sinh_vien 
                        SET ho_ten = :ten, email = :email, so_dien_thoai = :sdt, ma_lop_sv = :lop, 
                            ngay_sinh = :dob, gioi_tinh = :gender, trang_thai = :status, updated_at = NOW()
                        WHERE ma_sv = :ma_sv
                    ");
                    $stmt->execute([
                        ':ten' => $ho_ten,
                        ':email' => $email,
                        ':sdt' => $sdt,
                        ':lop' => $ma_lop,
                        ':dob' => $ngay_sinh ? $ngay_sinh : null,
                        ':gender' => $gioi_tinh,
                        ':status' => $trang_thai,
                        ':ma_sv' => $ma_sv
                    ]);
                    
                    // Cập nhật bảng tài khoản tương ứng
                    $stmt_tk = $db->prepare("
                        UPDATE tai_khoan 
                        SET email = :email, updated_at = NOW()
                        WHERE id = :tk_id
                    ");
                    $stmt_tk->execute([':email' => $email, ':tk_id' => $old_data['tai_khoan_id']]);
                    
                    $db->commit();
                    
                    $stmt_new = $db->prepare("SELECT * FROM sinh_vien WHERE ma_sv = :ma_sv");
                    $stmt_new->execute([':ma_sv' => $ma_sv]);
                    $new_data = $stmt_new->fetch(PDO::FETCH_ASSOC);
                    logAdminAction($db, 'sinh_vien', $ma_sv, 'UPDATE', $old_data, $new_data);
                    
                    echo json_encode(['success' => true, 'message' => 'Cập nhật sinh viên thành công!'], JSON_UNESCAPED_UNICODE);
                } else {
                    // Thêm sinh viên mới
                    $stmt_check = $db->prepare("SELECT ma_sv FROM sinh_vien WHERE ma_sv = :ma_sv");
                    $stmt_check->execute([':ma_sv' => $ma_sv]);
                    if ($stmt_check->fetch()) {
                        echo json_encode(['success' => false, 'message' => 'Mã sinh viên đã tồn tại.'], JSON_UNESCAPED_UNICODE);
                        exit;
                    }
                    
                    $db->beginTransaction();
                    $role_id = getStudentRoleId($db);
                    $raw_pass = trim($input['mat_khau'] ?? $input['password'] ?? $ma_sv);
                    $password_hash = password_hash($raw_pass, PASSWORD_DEFAULT);
                    $stmt_tk = $db->prepare("
                        INSERT INTO tai_khoan (username, password_hash, email, vai_tro_id, trang_thai)
                        VALUES (:username, :pass, :email, :role_id, 'HOAT_DONG')
                        RETURNING id
                    ");
                    $stmt_tk->execute([
                        ':username' => $ma_sv,
                        ':pass' => $password_hash,
                        ':email' => $email,
                        ':role_id' => $role_id
                    ]);
                    $tk_id = $stmt_tk->fetchColumn();
                    
                    $stmt_sv = $db->prepare("
                        INSERT INTO sinh_vien (ma_sv, tai_khoan_id, ho_ten, email, so_dien_thoai, ma_lop_sv, ngay_sinh, gioi_tinh, trang_thai)
                        VALUES (:ma_sv, :tk_id, :ho, :email, :sdt, :lop, :dob, :gender, :status)
                    ");
                    $stmt_sv->execute([
                        ':ma_sv' => $ma_sv,
                        ':tk_id' => $tk_id,
                        ':ho' => $ho_ten,
                        ':email' => $email,
                        ':sdt' => $sdt,
                        ':lop' => $ma_lop,
                        ':dob' => $ngay_sinh ? $ngay_sinh : null,
                        ':gender' => $gioi_tinh,
                        ':status' => $trang_thai
                    ]);
                    
                    $db->commit();
                    
                    $stmt_new = $db->prepare("SELECT * FROM sinh_vien WHERE ma_sv = :ma_sv");
                    $stmt_new->execute([':ma_sv' => $ma_sv]);
                    $new_data = $stmt_new->fetch(PDO::FETCH_ASSOC);
                    logAdminAction($db, 'sinh_vien', $ma_sv, 'INSERT', null, $new_data);
                    
                    echo json_encode(['success' => true, 'message' => 'Thêm sinh viên thành công! Mật khẩu mặc định là 123456.'], JSON_UNESCAPED_UNICODE);
                }
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
            $ma_sv = trim($input['ma_sv'] ?? '');
            
            if (!$ma_sv) {
                echo json_encode(['success' => false, 'message' => 'Mã sinh viên không hợp lệ.'], JSON_UNESCAPED_UNICODE);
                exit;
            }
            
            $stmt_old = $db->prepare("SELECT * FROM sinh_vien WHERE ma_sv = :ma_sv");
            $stmt_old->execute([':ma_sv' => $ma_sv]);
            $old_data = $stmt_old->fetch(PDO::FETCH_ASSOC);
            
            if (!$old_data) {
                echo json_encode(['success' => false, 'message' => 'Sinh viên không tồn tại.'], JSON_UNESCAPED_UNICODE);
                exit;
            }
            
            $db->beginTransaction();
            
            // Xóa sinh viên
            $stmt = $db->prepare("DELETE FROM sinh_vien WHERE ma_sv = :ma_sv");
            $stmt->execute([':ma_sv' => $ma_sv]);
            
            // Xóa tài khoản
            $stmt_tk = $db->prepare("DELETE FROM tai_khoan WHERE id = :tk_id");
            $stmt_tk->execute([':tk_id' => $old_data['tai_khoan_id']]);
            
            $db->commit();
            
            // Ghi log
            logAdminAction($db, 'sinh_vien', $ma_sv, 'DELETE', $old_data, null);
            
            echo json_encode(['success' => true, 'message' => 'Xóa sinh viên thành công!'], JSON_UNESCAPED_UNICODE);
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
