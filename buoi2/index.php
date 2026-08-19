<?php
session_start();

require_once "functions.php";
require_once "database.php";


$weekdays = getWeekdays();
$courseTypes = getCourseTypes();
$statusDefinitions = getStatusDefinitions();


$message = '';
$messageType = '';


if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["action"]) && $_POST["action"] === "add") {
    
   
    $validation = validateScheduleData($_POST);
    
    if (!$validation['valid']) {
               $message = implode('<br>', $validation['errors']);
        $messageType = 'error';
    } else {
        $newSchedule = createScheduleFromData($_POST);
        $result = addScheduleToDB($newSchedule);
        
        if ($result) {
            $message = ' Đã thêm lịch học thành công!';
            $messageType = 'success';
        } else {
            $message = ' Lỗi khi thêm lịch học. Vui lòng thử lại.';
            $messageType = 'error';
        }
    }
}


if (isset($_GET["action"]) && $_GET["action"] === "delete" && isset($_GET["id"])) {
    $id = intval($_GET["id"]);
    if (deleteScheduleFromDB($id)) {
        $message = ' Đã xóa lịch học thành công!';
        $messageType = 'success';
    } else {
        $message = ' Lỗi khi xóa lịch học.';
        $messageType = 'error';
    }
}


if (isset($_POST["action"]) && $_POST["action"] === "update_status") {
    $count = updateAllStatusesInDB();
    if ($count > 0) {
        $message = "Đã cập nhật trạng thái cho {$count} lịch học!";
        $messageType = 'success';
    } else {
        $message = 'Không có lịch học nào cần cập nhật trạng thái.';
        $messageType = 'info';
    }
}


$filter_weekday = $_GET["filter_weekday"] ?? '';
$filter_type = $_GET["filter_type"] ?? '';

$displaySchedules = getSchedulesFromDB($filter_weekday, $filter_type);

$stats = getStatsFromDB();


try {
    $instructorStats = getInstructorStats();
} catch (Exception $e) {
    $instructorStats = [];
    error_log("Lỗi lấy thống kê giảng viên: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Lịch học</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>
             Quản lý Lịch học
            <span class="badge">Hệ thống Đăng ký Học phần</span>
        </h1>
        <div class="subtitle">Nhập thông tin lịch học cho các lớp học phần</div>

        
        <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo $messageType; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

       
        <form method="POST" action="">
            <input type="hidden" name="action" value="add">
            <div class="form-grid">
                <div class="form-group">
                    <label for="course_name">Tên môn học <span class="required">*</span></label>
                    <input type="text" id="course_name" name="course_name" placeholder="VD: Lập trình Web" required>
                    <small class="hint">Tối thiểu 3 ký tự, tối đa 255 ký tự</small>
                </div>
                <div class="form-group">
                    <label for="course_code"> Mã môn học <span class="required">*</span></label>
                    <input type="text" id="course_code" name="course_code" placeholder="VD: WEB101" required>
                    <small class="hint">Chỉ chứa chữ cái, số, dấu gạch dưới và gạch ngang</small>
                </div>
                <div class="form-group">
                    <label for="weekday"> Thứ <span class="required">*</span></label>
                    <select id="weekday" name="weekday" required>
                        <option value="">-- Chọn thứ --</option>
                        <?php foreach ($weekdays as $key => $value): ?>
                            <option value="<?php echo $key; ?>"><?php echo $value; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="start_period"> Tiết bắt đầu <span class="required">*</span></label>
                    <input type="number" id="start_period" name="start_period" min="1" max="12" placeholder="VD: 2" required>
                    <small class="hint">Từ 1 đến 12</small>
                </div>
                <div class="form-group">
                    <label for="end_period">Tiết kết thúc <span class="required">*</span></label>
                    <input type="number" id="end_period" name="end_period" min="1" max="12" placeholder="VD: 4" required>
                    <small class="hint">Từ 1 đến 12, phải lớn hơn tiết bắt đầu</small>
                </div>
                <div class="form-group">
                    <label for="course_type"> Loại học phần</label>
                    <select id="course_type" name="course_type">
                        <option value="">-- Chọn loại --</option>
                        <?php foreach ($courseTypes as $key => $value): ?>
                            <option value="<?php echo $key; ?>"><?php echo $value; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="room"> Phòng học</label>
                    <input type="text" id="room" name="room" placeholder="VD: P.301">
                    <small class="hint">Tối đa 100 ký tự</small>
                </div>
                <div class="form-group">
                    <label for="instructor"> Giảng viên</label>
                    <input type="text" id="instructor" name="instructor" placeholder="VD: TS. Nguyễn Văn A">
                    <small class="hint">Tối đa 255 ký tự</small>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Thêm lịch học</button>
                    <button type="reset" class="btn btn-secondary"> Nhập lại</button>
                </div>
            </div>
        </form>


        <div class="filter-section">
            <form method="GET" action="">
                <label>Lọc theo thứ:</label>
                <select name="filter_weekday">
                    <option value="">-- Tất cả --</option>
                    <?php foreach ($weekdays as $key => $value): ?>
                        <option value="<?php echo $key; ?>" <?php echo ($filter_weekday == $key) ? 'selected' : ''; ?>>
                            <?php echo $value; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                
                <label>Lọc loại học phần:</label>
                <select name="filter_type">
                    <option value="">-- Tất cả --</option>
                    <?php foreach ($courseTypes as $key => $value): ?>
                        <option value="<?php echo $key; ?>" <?php echo ($filter_type == $key) ? 'selected' : ''; ?>>
                            <?php echo $value; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                
                <button type="submit" class="btn btn-primary btn-sm"> Lọc</button>
                <a href="?" class="btn btn-secondary btn-sm"> Xóa lọc</a>
            </form>
        </div>

       
        <div class="stats">
            <div class="stats-item"> Tổng số lịch: <span class="count"><?php echo $stats['total'] ?? 0; ?></span></div>
            <div class="stats-item"> Có thể đăng ký: <span class="count"><?php echo $stats['available'] ?? 0; ?></span></div>
            <div class="stats-item"> Xung đột: <span class="count"><?php echo $stats['conflict'] ?? 0; ?></span></div>
            <div class="stats-item"> Chờ xác nhận: <span class="count"><?php echo $stats['pending'] ?? 0; ?></span></div>
            <div class="stats-item">
                <form method="POST" action="" style="display:inline;">
                    <input type="hidden" name="action" value="update_status">
                    <button type="submit" class="btn btn-success btn-sm"> Cập nhật trạng thái</button>
                </form>
            </div>
        </div>

       
        <?php if (!empty($instructorStats)): ?>
        <div style="background:#f5f6fa; padding:15px; border-radius:8px; margin:15px 0;">
            <h3 style="color:#1a237e; margin-bottom:10px;">Thống kê theo giảng viên</h3>
            <div style="display:flex; flex-wrap:wrap; gap:20px;">
                <?php foreach ($instructorStats as $stat): ?>
                    <div style="background:white; padding:10px 18px; border-radius:8px; box-shadow:0 2px 8px rgba(0,0,0,0.05);">
                        <strong><?php echo htmlspecialchars($stat['instructor']); ?></strong>
                        <span style="margin-left:10px; background:#3f51b5; color:white; padding:2px 10px; border-radius:12px; font-size:12px;">
                            <?php echo $stat['total_courses']; ?> môn
                        </span>
                        <br>
                        <small style="color:#666;"><?php echo htmlspecialchars($stat['course_list']); ?></small>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

       
        <h2> Danh sách lịch học</h2>
        
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Tên môn học</th>
                        <th>Mã môn</th>
                        <th>Thứ</th>
                        <th>Tiết</th>
                        <th>Loại</th>
                        <th>Phòng</th>
                        <th>Giảng viên</th>
                        <th>Trạng thái</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($displaySchedules)): ?>
                        <tr class="empty-row">
                            <td colspan="10"> Chưa có lịch học nào. Hãy thêm lịch học mới!</td>
                        </tr>
                    <?php else: ?>
                        <?php $stt = 1; ?>
                        <?php foreach ($displaySchedules as $schedule): ?>
                            <?php 
                            $statusInfo = $statusDefinitions[$schedule['status']] ?? $statusDefinitions['available'];
                            ?>
                            <tr>
                                <td><?php echo $stt++; ?></td>
                                <td><strong><?php echo htmlspecialchars($schedule['course_name']); ?></strong></td>
                                <td><code><?php echo htmlspecialchars($schedule['course_code']); ?></code></td>
                                <td><?php echo htmlspecialchars($schedule['weekday_name']); ?></td>
                                <td><?php echo $schedule['start_period']; ?> - <?php echo $schedule['end_period']; ?></td>
                                <td><?php echo htmlspecialchars($schedule['course_type_name']); ?></td>
                                <td><?php echo htmlspecialchars($schedule['room']); ?></td>
                                <td><?php echo htmlspecialchars($schedule['instructor']); ?></td>
                                <td>
                                    <span class="status-badge <?php echo $statusInfo['class']; ?>">
                                        <?php echo $statusInfo['label']; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-cell">
                                        <a href="?action=delete&id=<?php echo $schedule['id']; ?>" 
                                           class="btn btn-danger" 
                                           onclick="return confirm('Bạn có chắc muốn xóa lịch học này?')">
                                             Xóa
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
      
        <div class="note">
            <strong> Ghi chú:</strong> 
            Hệ thống tự động kiểm tra xung đột lịch khi thêm mới. 
            Trạng thái <span class="status-badge status-available" style="font-size:11px;">Có thể đăng ký</span>, 
            <span class="status-badge status-conflict" style="font-size:11px;">Xung đột lịch</span> 
            và <span class="status-badge status-pending" style="font-size:11px;">Chờ xác nhận</span>.
        </div>
    </div>
</body>
</html>