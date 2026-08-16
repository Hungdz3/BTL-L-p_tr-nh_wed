<?php
require_once "config.php";

/**
 * Lấy kết nối PDO đến database
 * 
 * @return PDO Object PDO
 */
function getDBConnection() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
            ];
            
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die(" Kết nối database thất bại: " . $e->getMessage());
        }
    }
    
    return $pdo;
}



/**
 * Lấy danh sách tất cả lịch học
 * @param string $filter_weekday Lọc theo thứ
 * @param string $filter_type Lọc theo loại
 * @return array Danh sách lịch học
 */
function getAllSchedules($filter_weekday = '', $filter_type = '') {
    $pdo = getDBConnection();
    
    $sql = "SELECT * FROM schedules WHERE 1=1";
    $params = [];
    

    if (!empty($filter_weekday)) {
        $sql .= " AND weekday = ?";
        $params[] = $filter_weekday;
    }
    

    if (!empty($filter_type)) {
        $sql .= " AND course_type = ?";
        $params[] = $filter_type;
    }
    
    $sql .= " ORDER BY weekday, start_period";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    return $stmt->fetchAll();
}

/**
 * Lấy thông tin lịch học theo ID
 * 
 * @param int $id ID lịch học
 * @return array|bool Thông tin lịch học hoặc false
 */
function getScheduleById($id) {
    $pdo = getDBConnection();
    
    $sql = "SELECT * FROM schedules WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    
    return $stmt->fetch();
}

/**
 * Thêm lịch học mới
 * 
 * @param array $data Dữ liệu lịch học
 * @return int|bool ID vừa thêm hoặc false
 */
function addSchedule($data) {
    $pdo = getDBConnection();
    
    $sql = "INSERT INTO schedules (
                course_name, course_code, weekday, weekday_name, 
                start_period, end_period, course_type, course_type_name,
                room, instructor, status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $data['course_name'],
            $data['course_code'],
            $data['weekday'],
            $data['weekday_name'],
            $data['start_period'],
            $data['end_period'],
            $data['course_type'],
            $data['course_type_name'],
            $data['room'],
            $data['instructor'],
            $data['status']
        ]);
        
        $id = $pdo->lastInsertId();
        
        
        logActivity('add', $id, 'Thêm lịch học mới');
        
        return $id;
    } catch (PDOException $e) {
        error_log("Lỗi thêm lịch: " . $e->getMessage());
        return false;
    }
}

/**
 * Cập nhật lịch học
 * 
 * @param int $id ID lịch học
 * @param array $data Dữ liệu cập nhật
 * @return bool Thành công hay không
 */
function updateSchedule($id, $data) {
    $pdo = getDBConnection();
    
    $sql = "UPDATE schedules SET 
                course_name = ?,
                course_code = ?,
                weekday = ?,
                weekday_name = ?,
                start_period = ?,
                end_period = ?,
                course_type = ?,
                course_type_name = ?,
                room = ?,
                instructor = ?,
                status = ?
            WHERE id = ?";
    
    try {
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([
            $data['course_name'],
            $data['course_code'],
            $data['weekday'],
            $data['weekday_name'],
            $data['start_period'],
            $data['end_period'],
            $data['course_type'],
            $data['course_type_name'],
            $data['room'],
            $data['instructor'],
            $data['status'],
            $id
        ]);
        
        if ($result) {
            logActivity('update', $id, 'Cập nhật lịch học');
        }
        
        return $result;
    } catch (PDOException $e) {
        error_log("Lỗi cập nhật lịch: " . $e->getMessage());
        return false;
    }
}

/**
 * Xóa lịch học
 * 
 * @param int $id ID lịch học
 * @return bool Thành công hay không
 */
function deleteScheduleById($id) {
    $pdo = getDBConnection();
    
    try {
        // Lấy thông tin trước khi xóa để ghi log
        $schedule = getScheduleById($id);
        
        $sql = "DELETE FROM schedules WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([$id]);
        
        if ($result && $schedule) {
            logActivity('delete', $id, 'Xóa lịch học: ' . $schedule['course_name']);
        }
        
        return $result;
    } catch (PDOException $e) {
        error_log("Lỗi xóa lịch: " . $e->getMessage());
        return false;
    }
}

/**
 * Cập nhật trạng thái cho tất cả lịch học
 * 
 * @return int Số lượng cập nhật
 */
function updateAllStatuses() {
    $pdo = getDBConnection();
    
    // Lấy tất cả lịch học
    $schedules = getAllSchedules();
    $updatedCount = 0;
    
    foreach ($schedules as $schedule) {
        // Kiểm tra xung đột với các lịch khác
        $conflict = checkConflictWithDatabase(
            $schedule['weekday'],
            $schedule['start_period'],
            $schedule['end_period'],
            $schedule['id']
        );
        
        $newStatus = $conflict ? 'conflict' : 'available';
        
        // Nếu không có giảng viên thì pending
        if (empty($schedule['instructor']) || $schedule['instructor'] === 'Chưa phân công') {
            $newStatus = 'pending';
        }
        
        // Cập nhật nếu khác
        if ($schedule['status'] !== $newStatus) {
            $data = $schedule;
            $data['status'] = $newStatus;
            updateSchedule($schedule['id'], $data);
            $updatedCount++;
        }
    }
    
    if ($updatedCount > 0) {
        logActivity('update_status', 0, "Cập nhật trạng thái cho {$updatedCount} lịch học");
    }
    
    return $updatedCount;
}

/**
 * Kiểm tra xung đột lịch trong database
 * 
 * @param string $weekday Mã thứ
 * @param int $start_period Tiết bắt đầu
 * @param int $end_period Tiết kết thúc
 * @param int $exclude_id ID lịch học cần loại trừ
 * @return bool Có xung đột hay không
 */
function checkConflictWithDatabase($weekday, $start_period, $end_period, $exclude_id = 0) {
    $pdo = getDBConnection();
    
    $sql = "SELECT * FROM schedules 
            WHERE weekday = ? 
            AND id != ? 
            AND start_period < ? 
            AND end_period > ?";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$weekday, $exclude_id, $end_period, $start_period]);
    
    return $stmt->rowCount() > 0;
}

/**
 * Ghi log hoạt động
 * 
 * @param string $action Hành động
 * @param int $schedule_id ID lịch học
 * @param string $details Chi tiết
 * @return bool Thành công hay không
 */
function logActivity($action, $schedule_id = 0, $details = '') {
    $pdo = getDBConnection();
    
    $sql = "INSERT INTO log_activities (action, schedule_id, details, ip_address, user_agent) 
            VALUES (?, ?, ?, ?, ?)";
    
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    try {
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([$action, $schedule_id, $details, $ip, $user_agent]);
    } catch (PDOException $e) {
        error_log("Lỗi ghi log: " . $e->getMessage());
        return false;
    }
}

/**
 * Lấy thống kê lịch học
 * 
 * @return array Thống kê
 */
function getScheduleStats() {
    $pdo = getDBConnection();
    
    try {
        // Sử dụng Stored Procedure
        $stmt = $pdo->query("CALL sp_get_schedule_stats()");
        $result = $stmt->fetch();
        $stmt->closeCursor(); // Đóng cursor cho stored procedure
        
        return $result;
    } catch (PDOException $e) {
        // Fallback nếu stored procedure không tồn tại
        $sql = "SELECT 
                    COUNT(*) AS total,
                    SUM(CASE WHEN status = 'available' THEN 1 ELSE 0 END) AS available,
                    SUM(CASE WHEN status = 'conflict' THEN 1 ELSE 0 END) AS conflict,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending
                FROM schedules";
        
        $stmt = $pdo->query($sql);
        return $stmt->fetch();
    }
}

/**
 * Lấy danh sách lịch học theo giảng viên (sử dụng View)
 * 
 * @return array Danh sách thống kê theo giảng viên
 */
function getStatsByInstructor() {
    $pdo = getDBConnection();
    
    $sql = "SELECT * FROM v_schedules_by_instructor";
    $stmt = $pdo->query($sql);
    
    return $stmt->fetchAll();
}

/**
 * Tìm kiếm lịch học
 * 
 * @param string $keyword Từ khóa tìm kiếm
 * @return array Kết quả tìm kiếm
 */
function searchSchedules($keyword) {
    $pdo = getDBConnection();
    
    $sql = "SELECT * FROM schedules 
            WHERE course_name LIKE ? 
            OR course_code LIKE ? 
            OR instructor LIKE ? 
            OR room LIKE ?
            ORDER BY weekday, start_period";
    
    $keyword = "%{$keyword}%";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$keyword, $keyword, $keyword, $keyword]);
    
    return $stmt->fetchAll();
}
?>