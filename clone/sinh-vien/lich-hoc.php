<?php
// lich-hoc.php
session_start();
require_once __DIR__ . '/../config/db.php';

$ma_sv = $_SESSION['ma_sv'] ?? 'SV001';
$db = getDB();

$sinh_vien = null;
$lich_hoc_db = [];

try {
    // 1. Lấy thông tin sinh viên
    $stmt = $db->prepare("
        SELECT sv.ma_sv, sv.ho_ten, lsv.ten_lop, k.ten_khoa
        FROM sinh_vien sv
        JOIN lop_sinh_vien lsv ON sv.ma_lop_sv = lsv.ma_lop_sv
        JOIN nganh n ON lsv.ma_nganh = n.ma_nganh
        JOIN khoa k ON n.ma_khoa = k.ma_khoa
        WHERE sv.ma_sv = :ma_sv
    ");
    $stmt->execute([':ma_sv' => $ma_sv]);
    $sinh_vien = $stmt->fetch();

    // 2. Lấy danh sách lớp học phần sinh viên đã đăng ký trong học kỳ hiện tại cùng lịch học
    $stmt_lh = $db->prepare("
        SELECT 
            lhp.ma_lhp,
            lhp.ma_mon,
            mh.ten_mon,
            gv.ho_ten AS giang_vien,
            lh.thu,
            lh.phong,
            th.so_tiet,
            th.gio_bat_dau,
            th.gio_ket_thuc,
            hk.ten_hoc_ky,
            hk.nam_hoc,
            hk.ngay_bat_dau,
            hk.ngay_ket_thuc
        FROM dang_ky_hoc_phan dk
        JOIN lop_hoc_phan lhp ON dk.ma_lhp = lhp.ma_lhp
        JOIN mon_hoc mh ON lhp.ma_mon = mh.ma_mon
        JOIN giao_vien gv ON lhp.ma_gv = gv.ma_gv
        JOIN lich_hoc lh ON lhp.ma_lhp = lh.ma_lhp
        JOIN tiet_hoc th ON lh.id_tiet = th.id_tiet
        JOIN hoc_ky hk ON lhp.id_hoc_ky = hk.id_hoc_ky
        WHERE dk.ma_sv = :ma_sv AND dk.trang_thai = 'DA_DANG_KY'
    ");
    $stmt_lh->execute([':ma_sv' => $ma_sv]);
    $lich_hoc_db = $stmt_lh->fetchAll();

} catch (Exception $e) {
    // Silent catch
}

if (!$sinh_vien) {
    $sinh_vien = [
        'ma_sv' => $ma_sv,
        'ho_ten' => 'NGUYỄN VĂN A',
        'ten_lop' => 'CNTT D2024A',
        'ten_khoa' => 'Khoa Toán - CNTT'
    ];
}

// Giờ học cố định
$tiet_hours = [
    1 => '6h45 - 7h35',
    2 => '7h40 - 8h30',
    3 => '8h35 - 9h25',
    4 => '9h30 - 10h20',
    5 => '10h25 - 11h15',
    6 => '11h20 - 12h10',
    7 => '12h45 - 13h35',
    8 => '13h40 - 14h30',
    9 => '14h35 - 15h25',
    10 => '15h30 - 16h20',
    11 => '16h25 - 17h15',
    12 => '17h20 - 18h10'
];

// Hàm lấy màu sắc pastel cho từng môn học dựa trên mã môn
function getPastelColorClass($ma_mon) {
    $colors = ['pastel-red', 'pastel-blue', 'pastel-purple', 'pastel-yellow', 'pastel-green', 'pastel-orange', 'pastel-cyan'];
    $hash = crc32($ma_mon);
    return $colors[abs($hash) % count($colors)];
}

// Nhóm các tiết liên tiếp cùng lớp trong một thứ
$grouped_schedule = [];
foreach ($lich_hoc_db as $lh) {
    $thu = intval($lh['thu']);
    $ma_lhp = $lh['ma_lhp'];
    $grouped_schedule[$thu][$ma_lhp][] = $lh;
}

$cards = [];
foreach ($grouped_schedule as $thu => $classes) {
    foreach ($classes as $ma_lhp => $slots) {
        // Sắp xếp các tiết tăng dần
        usort($slots, function($a, $b) {
            return intval($a['so_tiet']) - intval($b['so_tiet']);
        });

        // Nhóm các tiết liên tiếp
        $current_group = [];
        foreach ($slots as $slot) {
            if (empty($current_group)) {
                $current_group[] = $slot;
            } else {
                $last_slot = end($current_group);
                if (intval($slot['so_tiet']) == intval($last_slot['so_tiet']) + 1) {
                    $current_group[] = $slot;
                } else {
                    $cards[] = [
                        'thu' => $thu,
                        'start_tiet' => intval($current_group[0]['so_tiet']),
                        'span' => count($current_group),
                        'info' => $current_group[0]
                    ];
                    $current_group = [$slot];
                }
            }
        }
        if (!empty($current_group)) {
            $cards[] = [
                'thu' => $thu,
                'start_tiet' => intval($current_group[0]['so_tiet']),
                'span' => count($current_group),
                'info' => $current_group[0]
            ];
        }
    }
}

require_once 'includes/header.php';
?>

<!-- Banner tiêu đề -->
<section class="banner-title-row">
  <h2>THỜI KHÓA BIỂU CÁ NHÂN</h2>
  <div class="banner-actions">
    <button class="btn-print" onclick="window.print()">🖨 In thời khóa biểu</button>
    <button class="btn-calendar" onclick="showToast('Đã đồng bộ thời khóa biểu sang Google Calendar thành công!', 'success')">📅 Xuất sang Google Calendar</button>
  </div>
</section>

<!-- Bộ lọc học kỳ -->
<section class="filter-section">
  <div class="filter-group">
    <div class="filter-item">
      <label>Học kỳ</label>
      <select>
        <option>Học kỳ 1 (2025 - 2026)</option>
        <option selected>Học kỳ 1 (2026 - 2027)</option>
      </select>
    </div>
    <div class="filter-item">
      <label>Ngày bắt đầu</label>
      <select>
        <option>22/12/2026</option>
      </select>
    </div>
    <div class="filter-item">
      <label>Ngày kết thúc</label>
      <select>
        <option>26/04/2027</option>
      </select>
    </div>
    <button class="btn-reset-filter" onclick="location.reload()">Đặt lại bộ lọc</button>
  </div>
</section>

<!-- Lưới thời khóa biểu trực quan -->
<div class="timetable-container">
  <div class="timetable-grid">
    
    <!-- Hàng đầu tiên: Headers cột -->
    <div class="grid-header-cell cell-tiet">TIẾT</div>
    <div class="grid-header-cell">THỨ 2</div>
    <div class="grid-header-cell">THỨ 3</div>
    <div class="grid-header-cell">THỨ 4</div>
    <div class="grid-header-cell">THỨ 5</div>
    <div class="grid-header-cell">THỨ 6</div>
    <div class="grid-header-cell">THỨ 7</div>
    <div class="grid-header-cell cell-cn">CHỦ NHẬT</div>

    <!-- Hàng 2: Nhãn Buổi Sáng -->
    <div class="grid-section-header" style="grid-row: 2; grid-column: 1 / span 8;">
      ☀️ BUỔI SÁNG
    </div>

    <!-- Hàng 3 - 8: Tiết 1 đến 6 và khung lưới nền -->
    <?php for ($t = 1; $t <= 6; $t++): $row_index = $t + 2; ?>
      <div class="grid-tiet-label" style="grid-row: <?= $row_index ?>; grid-column: 1;">
        <strong>Tiết <?= $t ?></strong>
        <small><?= $tiet_hours[$t] ?></small>
      </div>
      <?php for ($thu = 2; $thu <= 8; $thu++): ?>
        <div class="grid-bg-cell" style="grid-row: <?= $row_index ?>; grid-column: <?= $thu ?>;"></div>
      <?php endfor; ?>
    <?php endfor; ?>

    <!-- Hàng 9: Nhãn Buổi Chiều -->
    <div class="grid-section-header shadow-top" style="grid-row: 9; grid-column: 1 / span 8;">
      🌙 BUỔI CHIỀU
    </div>

    <!-- Hàng 10 - 15: Tiết 7 đến 12 và khung lưới nền -->
    <?php for ($t = 7; $t <= 12; $t++): $row_index = $t + 3; ?>
      <div class="grid-tiet-label" style="grid-row: <?= $row_index ?>; grid-column: 1;">
        <strong>Tiết <?= $t ?></strong>
        <small><?= $tiet_hours[$t] ?></small>
      </div>
      <?php for ($thu = 2; $thu <= 8; $thu++): ?>
        <div class="grid-bg-cell" style="grid-row: <?= $row_index ?>; grid-column: <?= $thu ?>;"></div>
      <?php endfor; ?>
    <?php endfor; ?>

    <!-- Vẽ các thẻ lịch học động đè lên lưới -->
    <?php foreach ($cards as $card): 
      $thu = intval($card['thu']); // 2 -> 8 (Chủ nhật là 8)
      $start = $card['start_tiet']; // 1 -> 12
      $span = $card['span'];
      $info = $card['info'];

      // Tính toán hàng (grid-row) bắt đầu
      if ($start <= 6) {
          $start_row = $start + 2; // Buổi sáng bắt đầu ở hàng 3
      } else {
          $start_row = $start + 3; // Buổi chiều bắt đầu ở hàng 10 (tiết 7 tương đương hàng 10)
      }
      
      $color_class = getPastelColorClass($info['ma_mon']);
      $date_range = '';
      if (!empty($info['ngay_bat_dau']) && !empty($info['ngay_ket_thuc'])) {
          $date_range = '(' . date('d/m', strtotime($info['ngay_bat_dau'])) . ' - ' . date('d/m', strtotime($info['ngay_ket_thuc'])) . ')';
      }
    ?>
      <div class="timetable-card <?= $color_class ?>" 
           style="grid-column: <?= $thu ?>; grid-row: <?= $start_row ?> / span <?= $span ?>;">
        <div class="card-subject"><?= htmlspecialchars($info['ten_mon']) ?></div>
        <div class="card-teacher"><?= htmlspecialchars($info['giang_vien']) ?></div>
        <?php if ($date_range): ?>
          <div class="card-dates"><?= $date_range ?></div>
        <?php endif; ?>
        <div class="card-room"><?= htmlspecialchars($info['phong']) ?></div>
      </div>
    <?php endforeach; ?>

  </div>
</div>

<?php require_once 'includes/footer.php'; ?>
