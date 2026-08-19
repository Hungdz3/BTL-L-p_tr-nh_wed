<?php
require_once "config.php";


function getDBConnection() {
    static $pdo = null;
    static $last_check = 0;
    
  
    $now = time();
    if ($pdo !== null && ($now - $last_check) > 3) {
        try {
                        $pdo->query("SELECT 1");
            $last_check = $now;
        } catch (PDOException $e) {
            $pdo = null;
            error_log("Mất kết nối database, sẽ kết nối lại...");
        }
    }
    
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
                PDO::ATTR_TIMEOUT => 30, 
                PDO::ATTR_PERSISTENT => false,
            ];
            
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            
            $pdo->query("SET SESSION wait_timeout = 28800");
            $pdo->query("SET SESSION interactive_timeout = 28800");
            $pdo->query("SET SESSION net_read_timeout = 60");
            $pdo->query("SET SESSION net_write_timeout = 60");
            
            $last_check = time();
            
        } catch (PDOException $e) {
            die(" Kết nối database thất bại: " . $e->getMessage());
        }
    }
    
    return $pdo;
}


function executeQuery($sql, $params = []) {
    $maxRetries = 3;
    $attempt = 0;
    
    while ($attempt < $maxRetries) {
        try {
            $pdo = getDBConnection();
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            $attempt++;
                       
            if ($e->getCode() == 'HY000' || $e->getCode() == '2006' || strpos($e->getMessage(), 'gone away') !== false) {
            
                $pdo = null;
                getDBConnection();
                error_log("Thử kết nối lại lần {$attempt}...");
                
                if ($attempt >= $maxRetries) {
                    throw new Exception("Không thể kết nối database sau {$maxRetries} lần thử: " . $e->getMessage());
                }
                
              
                sleep(1);
            } else {
              
                throw $e;
            }
        }
    }
    
    throw new Exception("Không thể thực thi query");
}


function getAllSchedules($filter_weekday = '', $filter_type = '') {
    try {
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
        
        $stmt = executeQuery($sql, $params);
        return $stmt->fetchAll();
        
    } catch (Exception $e) {
        error_log("Lỗi lấy danh sách lịch: " . $e->getMessage());
        return [];
    }
}


function getScheduleById($id) {
    try {
        $sql = "SELECT * FROM schedules WHERE id = ?";
        $stmt = executeQuery($sql, [$id]);
        return $stmt->fetch();
    } catch (Exception $e) {
        error_log("Lỗi lấy lịch theo ID: " . $e->getMessage());
        return false;
    }
}


function addSchedule($data) {
    try {
        $sql = "INSERT INTO schedules (
                    course_name, course_code, weekday, weekday_name, 
                    start_period, end_period, course_type, course_type_name,
                    room, instructor, status
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $params = [
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
        ];
        
        $stmt = executeQuery($sql, $params);
        $id = getDBConnection()->lastInsertId();
        
        logActivity('add', $id, 'Thêm lịch học mới');
        
        return $id;
        
    } catch (Exception $e) {
        error_log("Lỗi thêm lịch: " . $e->getMessage());
        return false;
    }
}


function updateSchedule($id, $data) {
    try {
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
        
        $params = [
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
        ];
        
        $stmt = executeQuery($sql, $params);
        $result = $stmt->rowCount() > 0;
        
        if ($result) {
            logActivity('update', $id, 'Cập nhật lịch học');
        }
        
        return $result;
        
    } catch (Exception $e) {
        error_log("Lỗi cập nhật lịch: " . $e->getMessage());
        return false;
    }
}


function deleteScheduleById($id) {
    try {
        $schedule = getScheduleById($id);
        
        $sql = "DELETE FROM schedules WHERE id = ?";
        $stmt = executeQuery($sql, [$id]);
        $result = $stmt->rowCount() > 0;
        
        if ($result && $schedule) {
            logActivity('delete', $id, 'Xóa lịch học: ' . $schedule['course_name']);
        }
        
        return $result;
        
    } catch (Exception $e) {
        error_log("Lỗi xóa lịch: " . $e->getMessage());
        return false;
    }
}


function updateAllStatuses() {
    try {
        $schedules = getAllSchedules();
        $updatedCount = 0;
        
        foreach ($schedules as $schedule) {
            $conflict = checkConflictWithDatabase(
                $schedule['weekday'],
                $schedule['start_period'],
                $schedule['end_period'],
                $schedule['id']
            );
            
            $newStatus = $conflict ? 'conflict' : 'available';
            
            if (empty($schedule['instructor']) || $schedule['instructor'] === 'Chưa phân công') {
                $newStatus = 'pending';
            }
            
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
        
    } catch (Exception $e) {
        error_log("Lỗi cập nhật trạng thái: " . $e->getMessage());
        return 0;
    }
}


function checkConflictWithDatabase($weekday, $start_period, $end_period, $exclude_id = 0) {
    try {
        $sql = "SELECT * FROM schedules 
                WHERE weekday = ? 
                AND id != ? 
                AND start_period < ? 
                AND end_period > ?";
        
        $stmt = executeQuery($sql, [$weekday, $exclude_id, $end_period, $start_period]);
        return $stmt->rowCount() > 0;
        
    } catch (Exception $e) {
        error_log("Lỗi kiểm tra xung đột: " . $e->getMessage());
        return false;
    }
}


function logActivity($action, $schedule_id = 0, $details = '') {
    try {
        $sql = "INSERT INTO log_activities (action, schedule_id, details, ip_address, user_agent) 
                VALUES (?, ?, ?, ?, ?)";
        
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        $stmt = executeQuery($sql, [$action, $schedule_id, $details, $ip, $user_agent]);
        return true;
        
    } catch (Exception $e) {
        error_log("Lỗi ghi log: " . $e->getMessage());
        return false;
    }
}


function getScheduleStats() {
    try {
        $sql = "SELECT 
                    COUNT(*) AS total,
                    SUM(CASE WHEN status = 'available' THEN 1 ELSE 0 END) AS available,
                    SUM(CASE WHEN status = 'conflict' THEN 1 ELSE 0 END) AS conflict,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending
                FROM schedules";
        
        $stmt = executeQuery($sql);
        return $stmt->fetch();
        
    } catch (Exception $e) {
        error_log("Lỗi thống kê: " . $e->getMessage());
        return ['total' => 0, 'available' => 0, 'conflict' => 0, 'pending' => 0];
    }
}

function getStatsByInstructor() {
    try {
        $sql = "SELECT 
                    instructor,
                    COUNT(*) AS total_courses,
                    GROUP_CONCAT(course_name SEPARATOR ', ') AS course_list
                FROM schedules
                WHERE instructor != 'Chưa phân công' 
                AND instructor IS NOT NULL
                AND instructor != ''
                GROUP BY instructor
                ORDER BY total_courses DESC";
        
        $stmt = executeQuery($sql);
        $result = $stmt->fetchAll();
        
        return $result ?: [];
        
    } catch (Exception $e) {
        error_log("Lỗi thống kê giảng viên: " . $e->getMessage());
        return [];
    }
}


function searchSchedules($keyword) {
    try {
        $sql = "SELECT * FROM schedules 
                WHERE course_name LIKE ? 
                OR course_code LIKE ? 
                OR instructor LIKE ? 
                OR room LIKE ?
                ORDER BY weekday, start_period";
        
        $keyword = "%{$keyword}%";
        $stmt = executeQuery($sql, [$keyword, $keyword, $keyword, $keyword]);
        return $stmt->fetchAll();
        
    } catch (Exception $e) {
        error_log("Lỗi tìm kiếm: " . $e->getMessage());
        return [];
    }
}
?>
