<?php
// api/admin/db_migration.php
header('Content-Type: application/json; charset=utf-8');
require_once '../../config/db.php';

// Chỉ cho phép admin chạy (để demo, chúng ta bỏ qua kiểm tra session hoặc thêm kiểm tra cơ bản)
try {
    $db = getDB();
    $sql_file = '../../config/update_schema.sql';
    if (!file_exists($sql_file)) {
        echo json_encode(['success' => false, 'message' => 'Không tìm thấy file SQL schema.'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    $sql = file_get_contents($sql_file);
    $db->exec($sql);
    
    echo json_encode(['success' => true, 'message' => 'Nâng cấp Cơ sở dữ liệu thành công!'], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Lỗi nâng cấp: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
