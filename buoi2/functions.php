<?php
require_once "database.php";


function getWeekdays() {
    return [
        '2' => 'Thứ 2',
        '3' => 'Thứ 3',
        '4' => 'Thứ 4',
        '5' => 'Thứ 5',
        '6' => 'Thứ 6',
        '7' => 'Thứ 7',
        '8' => 'Chủ nhật'
    ];
}

function getCourseTypes() {
    return [
        'LT' => 'Lý thuyết',
        'TH' => 'Thực hành',
        'BT' => 'Bài tập',
        'DT' => 'Đồ án'
    ];
}

function getStatusDefinitions() {
    return [
        'available' => ['label' => 'Có thể đăng ký', 'class' => 'status-available'],
        'conflict' => ['label' => 'Xung đột lịch', 'class' => 'status-conflict'],
        'pending' => ['label' => 'Chờ xác nhận', 'class' => 'status-pending']
    ];
}


function createScheduleFromData($data) {
    $weekdays = getWeekdays();
    $courseTypes = getCourseTypes();
    
    return [
        'course_name' => trim($data['course_name'] ?? ''),
        'course_code' => trim($data['course_code'] ?? ''),
        'weekday' => $data['weekday'] ?? '',
        'weekday_name' => $weekdays[$data['weekday']] ?? $data['weekday'],
        'start_period' => intval($data['start_period'] ?? 0),
        'end_period' => intval($data['end_period'] ?? 0),
        'course_type' => $data['course_type'] ?? '',
        'course_type_name' => $courseTypes[$data['course_type']] ?? 'Chưa phân loại',
        'room' => trim($data['room'] ?? '') ?: 'Chưa có phòng',
        'instructor' => trim($data['instructor'] ?? '') ?: 'Chưa phân công',
        'status' => 'available'
    ];
}


function validateScheduleData($data) {
    $errors = [];
    
    // Lấy dữ liệu
    $course_name = trim($data['course_name'] ?? '');
    $course_code = trim($data['course_code'] ?? '');
    $weekday = $data['weekday'] ?? '';
    $start_period = intval($data['start_period'] ?? 0);
    $end_period = intval($data['end_period'] ?? 0);
    $course_type = $data['course_type'] ?? '';
    $room = trim($data['room'] ?? '');
    $instructor = trim($data['instructor'] ?? '');
    
   
    if (empty($course_name)) {
        $errors[] = "Tên môn học không được để trống";
    } elseif (strlen($course_name) < 3) {
        $errors[] = "Tên môn học phải có ít nhất 3 ký tự";
    } elseif (strlen($course_name) > 255) {
        $errors[] = "Tên môn học không được quá 255 ký tự";
    } elseif (!preg_match('/^[a-zA-ZÀ-ỹ0-9\s\-.,:()]+$/u', $course_name)) {
        $errors[] = "Tên môn học chứa ký tự không hợp lệ";
    }
    

    if (empty($course_code)) {
        $errors[] = "Mã môn học không được để trống";
    } elseif (strlen($course_code) < 2) {
        $errors[] = "Mã môn học phải có ít nhất 2 ký tự";
    } elseif (strlen($course_code) > 50) {
        $errors[] = "Mã môn học không được quá 50 ký tự";
    } elseif (!preg_match('/^[A-Za-z0-9_\-]+$/', $course_code)) {
        $errors[] = "Mã môn học chỉ được chứa chữ cái, số, dấu gạch dưới và gạch ngang";
    }
    
    
    $validWeekdays = ['2', '3', '4', '5', '6', '7', '8'];
    if (empty($weekday)) {
        $errors[] = "Vui lòng chọn thứ trong tuần";
    } elseif (!in_array($weekday, $validWeekdays)) {
        $errors[] = "Thứ không hợp lệ";
    }
    
    
    if ($start_period <= 0) {
        $errors[] = "Tiết bắt đầu phải lớn hơn 0";
    } elseif ($start_period < 1 || $start_period > 12) {
        $errors[] = "Tiết bắt đầu phải từ 1 đến 12";
    }
   

    if ($end_period <= 0) {
        $errors[] = "Tiết kết thúc phải lớn hơn 0";
    } elseif ($end_period < 1 || $end_period > 12) {
        $errors[] = "Tiết kết thúc phải từ 1 đến 12";
    }
    
   
    if ($start_period >= $end_period) {
        $errors[] = "Tiết bắt đầu phải nhỏ hơn tiết kết thúc";
    }
    
   
    $period_count = $end_period - $start_period;
    if ($period_count < 1) {
        $errors[] = "Số tiết học phải ít nhất 1 tiết";
    } elseif ($period_count > 6) {
        $errors[] = "Số tiết học không được vượt quá 6 tiết";
    }
    
  
    $validTypes = ['LT', 'TH', 'BT', 'DT', ''];
    if (!in_array($course_type, $validTypes)) {
        $errors[] = "Loại học phần không hợp lệ";
    }
    
  
    if (!empty($room)) {
        if (strlen($room) > 100) {
            $errors[] = "Tên phòng học không được quá 100 ký tự";
        } elseif (!preg_match('/^[a-zA-ZÀ-ỹ0-9\s\-.,:]+$/u', $room)) {
            $errors[] = "Tên phòng học chứa ký tự không hợp lệ";
        }
    }
    
    if (!empty($instructor)) {
        if (strlen($instructor) > 255) {
            $errors[] = "Tên giảng viên không được quá 255 ký tự";
        } elseif (!preg_match('/^[a-zA-ZÀ-ỹ\s\-.]+$/u', $instructor)) {
            $errors[] = "Tên giảng viên chứa ký tự không hợp lệ";
        }
    }
    
   
    $weekdays = getWeekdays();
    $conflict = checkConflictWithDatabase($weekday, $start_period, $end_period);
    if ($conflict) {
        $errors[] = "Lịch học bị xung đột với lịch đã tồn tại vào " . ($weekdays[$weekday] ?? $weekday);
    }
    
    
    if (!empty($errors)) {
        return [
            'valid' => false,
            'errors' => $errors,
            'message' => implode('; ', $errors)
        ];
    }
    
    return [
        'valid' => true,
        'errors' => [],
        'message' => ''
    ];
}


function getSchedulesFromDB($filter_weekday = '', $filter_type = '') {
    return getAllSchedules($filter_weekday, $filter_type);
}

function getScheduleFromDB($id) {
    return getScheduleById($id);
}

function addScheduleToDB($data) {
    return addSchedule($data);
}

function deleteScheduleFromDB($id) {
    return deleteScheduleById($id);
}

function updateAllStatusesInDB() {
    return updateAllStatuses();
}

function getStatsFromDB() {
    return getScheduleStats();
}

function getInstructorStats() {
    return getStatsByInstructor();
}

function searchSchedulesInDB($keyword) {
    return searchSchedules($keyword);
}

function checkScheduleConflictInDB($weekday, $start_period, $end_period, $exclude_id = 0) {
    return checkConflictWithDatabase($weekday, $start_period, $end_period, $exclude_id);
}
?>