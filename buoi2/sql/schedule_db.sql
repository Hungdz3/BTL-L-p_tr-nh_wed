CREATE DATABASE IF NOT EXISTS schedule_management;
USE schedule_management;


CREATE TABLE IF NOT EXISTS schedules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_name VARCHAR(255) NOT NULL COMMENT 'Tên môn học',
    course_code VARCHAR(50) NOT NULL COMMENT 'Mã môn học',
    weekday VARCHAR(10) NOT NULL COMMENT 'Thứ (2-8)',
    weekday_name VARCHAR(20) NOT NULL COMMENT 'Tên thứ',
    start_period INT NOT NULL COMMENT 'Tiết bắt đầu (1-12)',
    end_period INT NOT NULL COMMENT 'Tiết kết thúc (1-12)',
    course_type VARCHAR(10) DEFAULT '' COMMENT 'Loại học phần (LT/TH/BT/DT)',
    course_type_name VARCHAR(50) DEFAULT 'Chưa phân loại' COMMENT 'Tên loại học phần',
    room VARCHAR(100) DEFAULT 'Chưa có phòng' COMMENT 'Phòng học',
    instructor VARCHAR(255) DEFAULT 'Chưa phân công' COMMENT 'Giảng viên',
    status VARCHAR(20) DEFAULT 'available' COMMENT 'Trạng thái: available/conflict/pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Ngày tạo',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Ngày cập nhật',
    
    INDEX idx_weekday (weekday),
    INDEX idx_course_code (course_code),
    INDEX idx_status (status),
    INDEX idx_course_type (course_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Bảng lịch học';


CREATE TABLE IF NOT EXISTS log_activities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    action VARCHAR(50) NOT NULL COMMENT 'Hành động: add/delete/update',
    schedule_id INT COMMENT 'ID lịch học liên quan',
    details TEXT COMMENT 'Chi tiết',
    ip_address VARCHAR(45) COMMENT 'Địa chỉ IP',
    user_agent TEXT COMMENT 'User Agent',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_action (action),
    INDEX idx_schedule_id (schedule_id),
    INDEX idx_created_at (created_at),
    
    FOREIGN KEY (schedule_id) REFERENCES schedules(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Lịch sử hoạt động';

INSERT INTO schedules 
    (course_name, course_code, weekday, weekday_name, start_period, end_period, 
     course_type, course_type_name, room, instructor, status) 
VALUES
    ('Lập trình Web', 'WEB101', '3', 'Thứ 3', 2, 4, 'LT', 'Lý thuyết', 'P.301', 'TS. Nguyễn Văn A', 'available'),
    ('Cơ sở dữ liệu', 'CSDL102', '4', 'Thứ 4', 5, 7, 'TH', 'Thực hành', 'P.205', 'ThS. Trần Thị B', 'available'),
    ('Hệ điều hành', 'HĐH103', '5', 'Thứ 5', 1, 3, 'LT', 'Lý thuyết', 'P.102', 'PGS. Lê Văn C', 'available'),
    ('Mạng máy tính', 'MMT104', '3', 'Thứ 3', 5, 7, 'BT', 'Bài tập', 'P.401', 'TS. Phạm Thị D', 'conflict'),
    ('Trí tuệ nhân tạo', 'TTNT105', '6', 'Thứ 6', 8, 10, 'DT', 'Đồ án', 'P.505', '', 'pending');


DELIMITER //

CREATE PROCEDURE sp_get_schedule_stats()
BEGIN
    SELECT 
        COUNT(*) AS total,
        SUM(CASE WHEN status = 'available' THEN 1 ELSE 0 END) AS available,
        SUM(CASE WHEN status = 'conflict' THEN 1 ELSE 0 END) AS conflict,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending
    FROM schedules;
END //

DELIMITER ;


CREATE VIEW v_schedules_by_instructor AS
SELECT 
    instructor,
    COUNT(*) AS total_courses,
    GROUP_CONCAT(course_name SEPARATOR ', ') AS course_list
FROM schedules
WHERE instructor != 'Chưa phân công'
GROUP BY instructor
ORDER BY total_courses DESC;


SELECT * FROM schedules;
SELECT * FROM v_schedules_by_instructor;
CALL sp_get_schedule_stats();