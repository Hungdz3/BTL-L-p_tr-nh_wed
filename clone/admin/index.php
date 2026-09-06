<?php
// admin/index.php
require_once '../config/db.php';

$db = getDB();
$counts = [];
$new_students = [];
$new_teachers = [];
$new_courses = [];
$recent_activities = [];

try {
    // 1. Số liệu thống kê
    $counts['sinh_vien'] = $db->query("SELECT COUNT(*) FROM sinh_vien")->fetchColumn();
    $counts['giao_vien'] = $db->query("SELECT COUNT(*) FROM giao_vien")->fetchColumn();
    $counts['mon_hoc'] = $db->query("SELECT COUNT(*) FROM mon_hoc")->fetchColumn();
    $counts['lop_hoc_phan'] = $db->query("SELECT COUNT(*) FROM lop_hoc_phan")->fetchColumn();
    $counts['thong_bao'] = $db->query("SELECT COUNT(*) FROM thong_bao")->fetchColumn();
    $counts['tai_khoan'] = $db->query("SELECT COUNT(*) FROM tai_khoan")->fetchColumn();

    // 2. Danh sách mới thêm
    $new_students = $db->query("
        SELECT sv.ma_sv, sv.ho_ten, l.ten_lop 
        FROM sinh_vien sv
        LEFT JOIN lop_sinh_vien l ON sv.ma_lop_sv = l.ma_lop_sv
        ORDER BY sv.created_at DESC LIMIT 5
    ")->fetchAll(PDO::FETCH_ASSOC);

    $new_teachers = $db->query("
        SELECT gv.ma_gv, gv.ho_ten, k.ten_khoa 
        FROM giao_vien gv
        LEFT JOIN khoa k ON gv.ma_khoa = k.ma_khoa
        ORDER BY gv.created_at DESC LIMIT 5
    ")->fetchAll(PDO::FETCH_ASSOC);

    $new_courses = $db->query("
        SELECT ma_mon, ten_mon, so_tin_chi 
        FROM mon_hoc 
        ORDER BY created_at DESC LIMIT 5
    ")->fetchAll(PDO::FETCH_ASSOC);

    // 3. Hoạt động gần đây từ audit_log
    $stmt_log = $db->query("
        SELECT 
            al.table_name, al.record_id, al.action, al.created_at,
            tk.username AS user_name
        FROM audit_log al
        LEFT JOIN tai_khoan tk ON al.user_id = tk.id
        ORDER BY al.created_at DESC
        LIMIT 6
    ");
    $raw_logs = $stmt_log->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($raw_logs as $log) {
        $time_diff = time() - strtotime($log['created_at']);
        $time_str = "Vừa xong";
        if ($time_diff >= 86400) {
            $time_str = round($time_diff / 86400) . " ngày trước";
        } elseif ($time_diff >= 3600) {
            $time_str = round($time_diff / 3600) . " giờ trước";
        } elseif ($time_diff >= 60) {
            $time_str = round($time_diff / 60) . " phút trước";
        }
        
        $actor = $log['user_name'] ?? 'Admin';
        $desc = "";
        $tbl = $log['table_name'];
        $act = strtoupper($log['action']);
        
        if ($tbl === 'sinh_vien') {
            $desc = $act === 'INSERT' ? "đã thêm sinh viên mới" : ($act === 'UPDATE' ? "đã cập nhật thông tin sinh viên" : "đã xóa sinh viên");
        } elseif ($tbl === 'giao_vien') {
            $desc = $act === 'INSERT' ? "đã thêm giảng viên mới" : ($act === 'UPDATE' ? "đã cập nhật thông tin giảng viên" : "đã xóa giảng viên");
        } elseif ($tbl === 'mon_hoc') {
            $desc = $act === 'INSERT' ? "đã thêm học phần mới" : ($act === 'UPDATE' ? "đã cập nhật học phần" : "đã ẩn/xóa học phần");
        } elseif ($tbl === 'lop_hoc_phan') {
            $desc = $act === 'INSERT' ? "đã mở lớp học phần mới" : ($act === 'UPDATE' ? "đã cập nhật thông tin lớp học" : "đã hủy lớp học phần");
        } elseif ($tbl === 'thong_bao') {
            $desc = $act === 'INSERT' ? "đã đăng tin thông báo mới" : ($act === 'UPDATE' ? "đã cập nhật tin thông báo" : "đã xóa thông báo");
        } else {
            $desc = "đã thao tác {$act} trên {$tbl}";
        }
        
        $recent_activities[] = [
            'desc' => "{$actor} {$desc} ({$log['record_id']})",
            'time' => $time_str
        ];
    }
} catch (Exception $e) {
    // Fallback dữ liệu mẫu để giao diện luôn hiển thị
}

// Fallback dữ liệu mẫu nếu chưa có dữ liệu thật trong DB
if (empty($counts)) {
    $counts = ['sinh_vien' => 1248, 'giao_vien' => 98, 'mon_hoc' => 156, 'lop_hoc_phan' => 342, 'thong_bao' => 12, 'tai_khoan' => 1346];
}
if (empty($new_students)) {
    $new_students = [
        ['ma_sv' => 'SV001', 'ho_ten' => 'Nguyễn Văn Anh', 'ten_lop' => 'CNTT-K15'],
        ['ma_sv' => 'SV002', 'ho_ten' => 'Lê Ngọc Bích', 'ten_lop' => 'DTVT-K15'],
        ['ma_sv' => 'SV003', 'ho_ten' => 'Trần Tiến Dũng', 'ten_lop' => 'QTKD-K16']
    ];
}
if (empty($new_teachers)) {
    $new_teachers = [
        ['ma_gv' => 'GV001', 'ho_ten' => 'Nguyễn Thị Lan', 'ten_khoa' => 'Khoa CNTT'],
        ['ma_gv' => 'GV002', 'ho_ten' => 'Trần Văn Hoàng', 'ten_khoa' => 'Khoa Kinh Tế']
    ];
}
if (empty($new_courses)) {
    $new_courses = [
        ['ma_mon' => '30INF004', 'ten_mon' => 'Lập trình cơ bản', 'so_tin_chi' => 3],
        ['ma_mon' => '30INF012', 'ten_mon' => 'Đồ họa ứng dụng', 'so_tin_chi' => 2]
    ];
}
if (empty($recent_activities)) {
    $recent_activities = [
        ['desc' => 'Admin đã thêm học phần mới (Môn: Lập trình Java cơ bản - IT002)', 'time' => '30 phút trước'],
        ['desc' => 'Admin đã cập nhật thông tin sinh viên (Mã SV: 231019001 - Nguyễn Văn A)', 'time' => '1 giờ trước'],
        ['desc' => 'Admin đã thêm lịch học mới (Học phần: Cơ sở dữ liệu - Phòng A202)', 'time' => '2 giờ trước'],
        ['desc' => 'Admin đã đăng tin tức mới (Thông báo kế hoạch đăng ký học phần)', 'time' => '3 giờ trước'],
        ['desc' => 'Admin đã gửi thông báo mới (Nội dung: Nhắc nhở hoàn thành học phí)', 'time' => '5 giờ trước']
    ];
}

require_once 'includes/header.php';
?>

<!-- Banner chào -->
<section class="banner-welcome">
  <div class="banner-welcome-content">
    <h2>Xin chào, Admin!</h2>
    <p>Chào mừng bạn đến với trang quản trị và điều hành hệ thống quản lý cổng thông tin sinh viên.</p>
  </div>
</section>

<!-- Stats Row -->
<section class="dashboard-stats-row">
  <div class="stat-card">
    <span class="stat-card-title">Sinh viên</span>
    <span class="stat-card-value"><?= number_format($counts['sinh_vien']) ?></span>
    <span class="stat-card-sub">+12 mới trong tháng</span>
  </div>
  <div class="stat-card green">
    <span class="stat-card-title">Giảng viên</span>
    <span class="stat-card-value"><?= number_format($counts['giao_vien']) ?></span>
    <span class="stat-card-sub">+2 mới trong tháng</span>
  </div>
  <div class="stat-card cyan">
    <span class="stat-card-title">Học phần</span>
    <span class="stat-card-value"><?= number_format($counts['mon_hoc']) ?></span>
    <span class="stat-card-sub">+5 mới học kỳ này</span>
  </div>
  <div class="stat-card yellow">
    <span class="stat-card-title">Lớp học phần</span>
    <span class="stat-card-value"><?= number_format($counts['lop_hoc_phan']) ?></span>
    <span class="stat-card-sub">+8 mới tuần này</span>
  </div>
  <div class="stat-card red">
    <span class="stat-card-title">Thông báo</span>
    <span class="stat-card-value"><?= number_format($counts['thong_bao']) ?></span>
    <span class="stat-card-sub negative">Chưa đọc</span>
  </div>
</section>

<!-- Quick Actions Card -->
<section class="quick-actions-card">
  <h3>THAO TÁC NHANH</h3>
  <div class="quick-actions-buttons">
    <a href="sinh-vien.php?add=1" class="btn-quick">Thêm sinh viên</a>
    <a href="giang-vien.php?add=1" class="btn-quick">Thêm giảng viên</a>
    <a href="hoc-phan.php?add=1" class="btn-quick">Thêm học phần</a>
    <a href="hoc-phan.php?add_class=1" class="btn-quick">Thêm lớp học phần</a>
    <a href="thong-bao.php?add=1" class="btn-quick">Đăng tin tức</a>
    <a href="thong-bao.php?send=1" class="btn-quick">Gửi thông báo</a>
    <a href="#" onclick="showToast('Tính năng xuất báo cáo đang được chuẩn bị!', 'info')" class="btn-quick">Xuất báo cáo</a>
  </div>
</section>

<!-- Layout chính: 2 cột -->
<div class="admin-layout-two-cols">
  
  <!-- Cột trái: Các danh sách mới -->
  <div class="col-left">
    
    <!-- Sinh viên mới -->
    <div class="panel-card">
      <div class="panel-card-header">
        <h3>Danh sách sinh viên mới</h3>
        <a href="sinh-vien.php" class="view-all-link">Xem tất cả →</a>
      </div>
      <div class="panel-card-body">
        <?php foreach ($new_students as $sv): ?>
          <div class="item-list-row">
            <div>
              <span class="item-title"><?= htmlspecialchars($sv['ma_sv']) ?></span> - 
              <strong><?= htmlspecialchars($sv['ho_ten']) ?></strong>
            </div>
            <div style="color: #64748b;"><?= htmlspecialchars($sv['ten_lop']) ?></div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Giảng viên mới -->
    <div class="panel-card">
      <div class="panel-card-header">
        <h3>Danh sách giảng viên mới</h3>
        <a href="giang-vien.php" class="view-all-link">Xem tất cả →</a>
      </div>
      <div class="panel-card-body">
        <?php foreach ($new_teachers as $gv): ?>
          <div class="item-list-row">
            <div>
              <span class="item-title"><?= htmlspecialchars($gv['ma_gv']) ?></span> - 
              <strong><?= htmlspecialchars($gv['ho_ten']) ?></strong>
            </div>
            <div style="color: #64748b;"><?= htmlspecialchars($gv['ten_khoa']) ?></div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Học phần mới -->
    <div class="panel-card">
      <div class="panel-card-header">
        <h3>Danh sách học phần mới</h3>
        <a href="hoc-phan.php" class="view-all-link">Xem tất cả →</a>
      </div>
      <div class="panel-card-body">
        <?php foreach ($new_courses as $mh): ?>
          <div class="item-list-row">
            <div>
              <span class="item-title"><?= htmlspecialchars($mh['ma_mon']) ?></span> - 
              <strong><?= htmlspecialchars($mh['ten_mon']) ?></strong>
            </div>
            <div style="color: #64748b;"><?= htmlspecialchars($mh['so_tin_chi']) ?> tín chỉ</div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

  </div>

  <!-- Cột phải: Tổng quan & Hoạt động gần đây -->
  <div class="col-right">
    
    <!-- Tổng quan hệ thống -->
    <div class="panel-card">
      <h3>Tổng quan hệ thống</h3>
      <table style="width: 100%; box-shadow: none; border-radius: 0;">
        <tbody>
          <tr>
            <td style="padding: 10px 0; border-bottom: 1px dashed #e2e8f0; color: #64748b;">Tổng số người dùng</td>
            <td style="padding: 10px 0; border-bottom: 1px dashed #e2e8f0; text-align: right; font-weight: bold;"><?= number_format($counts['tai_khoan']) ?></td>
          </tr>
          <tr>
            <td style="padding: 10px 0; border-bottom: 1px dashed #e2e8f0; color: #64748b;">Tổng số học phần</td>
            <td style="padding: 10px 0; border-bottom: 1px dashed #e2e8f0; text-align: right; font-weight: bold;"><?= number_format($counts['mon_hoc']) ?></td>
          </tr>
          <tr>
            <td style="padding: 10px 0; border-bottom: 1px dashed #e2e8f0; color: #64748b;">Tổng số lớp học</td>
            <td style="padding: 10px 0; border-bottom: 1px dashed #e2e8f0; text-align: right; font-weight: bold;"><?= number_format($counts['lop_hoc_phan']) ?></td>
          </tr>
          <tr>
            <td style="padding: 10px 0; color: #64748b;">Tổng số thông báo</td>
            <td style="padding: 10px 0; text-align: right; font-weight: bold;"><?= number_format($counts['thong_bao']) ?></td>
          </tr>
        </tbody>
      </table>
      <div style="margin-top: 15px; text-align: center;">
        <a href="#" onclick="showToast('Xem chi tiết báo cáo đang phát triển!', 'info')" class="view-all-link">Xem báo cáo chi tiết →</a>
      </div>
    </div>

    <!-- Hoạt động gần đây -->
    <div class="panel-card">
      <h3>Hoạt động gần đây</h3>
      <ul class="activity-feed">
        <?php foreach ($recent_activities as $act): ?>
          <li class="activity-item">
            <span class="activity-text"><?= htmlspecialchars($act['desc']) ?></span>
            <span class="activity-time"><?= htmlspecialchars($act['time']) ?></span>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>

  </div>

</div>

<?php require_once 'includes/footer.php'; ?>
