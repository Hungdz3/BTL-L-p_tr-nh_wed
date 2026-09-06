<?php
// sinh-vien/lich-hoc.php
session_start();
require_once __DIR__ . '/../config/db.php';

$ma_sv = $_SESSION['ma_sv'] ?? 'SV001';
$db = getDB();

$sinh_vien = null;
$lich_hoc_db = [];
$hoc_ky_list = [];
$selected_hk = $_GET['hk'] ?? '';

try {
    // 1. Lấy thông tin sinh viên
    $stmt = $db->prepare("
        SELECT sv.ma_sv, sv.ho_ten, lsv.ten_lop, k.ten_khoa
        FROM sinh_vien sv
        LEFT JOIN lop_sinh_vien lsv ON sv.ma_lop_sv = lsv.ma_lop_sv
        LEFT JOIN nganh n ON lsv.ma_nganh = n.ma_nganh
        LEFT JOIN khoa k ON n.ma_khoa = k.ma_khoa
        WHERE sv.ma_sv = :ma_sv
    ");
    $stmt->execute([':ma_sv' => $ma_sv]);
    $sinh_vien = $stmt->fetch();

    // 2. Lấy danh sách các học kỳ
    $stmt_hk_all = $db->query("SELECT id_hoc_ky, ten_hoc_ky, nam_hoc, ngay_bat_dau, ngay_ket_thuc, trang_thai FROM hoc_ky ORDER BY ngay_bat_dau DESC");
    $hoc_ky_list = $stmt_hk_all->fetchAll();

    // 3. Lấy danh sách lớp học phần sinh viên đã đăng ký cùng lịch học
    $sql_lh = "
        SELECT 
            lhp.ma_lhp,
            lhp.ma_mon,
            mh.ten_mon,
            mh.so_tin_chi,
            gv.ho_ten AS giang_vien,
            lh.thu,
            lh.phong,
            th.so_tiet,
            th.gio_bat_dau,
            th.gio_ket_thuc,
            hk.id_hoc_ky,
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
    ";

    $params_lh = [':ma_sv' => $ma_sv];
    if (!empty($selected_hk) && $selected_hk !== 'ALL') {
        $sql_lh .= " AND hk.id_hoc_ky = :hk_id";
        $params_lh[':hk_id'] = $selected_hk;
    }

    $sql_lh .= " ORDER BY lh.thu ASC, th.so_tiet ASC";

    $stmt_lh = $db->prepare($sql_lh);
    $stmt_lh->execute($params_lh);
    $lich_hoc_db = $stmt_lh->fetchAll();

} catch (Exception $e) {
    // Silent catch
}

if (!$sinh_vien) {
    $sinh_vien = [
        'ma_sv' => $ma_sv,
        'ho_ten' => 'Sinh viên',
        'ten_lop' => 'N/A',
        'ten_khoa' => 'N/A'
    ];
}

// Giờ học cố định
$tiet_hours = [
    1 => '06:45 - 07:35',
    2 => '07:40 - 08:30',
    3 => '08:35 - 09:25',
    4 => '09:30 - 10:20',
    5 => '10:25 - 11:15',
    6 => '11:20 - 12:10',
    7 => '12:45 - 13:35',
    8 => '13:40 - 14:30',
    9 => '14:35 - 15:25',
    10 => '15:30 - 16:20',
    11 => '16:25 - 17:15',
    12 => '17:20 - 18:10'
];

// Hàm lấy màu sắc pastel đồng bộ theo mã môn
function getPastelTheme($ma_mon) {
    $themes = [
        ['bg' => '#eff6ff', 'border' => '#2563eb', 'text' => '#1e3a8a', 'badge' => '#dbeafe'], // Blue
        ['bg' => '#f0fdf4', 'border' => '#16a34a', 'text' => '#14532d', 'badge' => '#dcfce7'], // Green
        ['bg' => '#faf5ff', 'border' => '#9333ea', 'text' => '#581c87', 'badge' => '#f3e8ff'], // Purple
        ['bg' => '#fff7ed', 'border' => '#ea580c', 'text' => '#7c2d12', 'badge' => '#ffedd5'], // Orange
        ['bg' => '#fefce8', 'border' => '#ca8a04', 'text' => '#713f12', 'badge' => '#fef9c3'], // Yellow
        ['bg' => '#ecfeff', 'border' => '#0891b2', 'text' => '#164e63', 'badge' => '#cffafe'], // Cyan
        ['bg' => '#fff1f2', 'border' => '#e11d48', 'text' => '#881337', 'badge' => '#ffe4e6'], // Rose
    ];
    $hash = abs(crc32($ma_mon ?? 'DEFAULT'));
    return $themes[$hash % count($themes)];
}

// Nhóm các tiết liên tiếp của từng lớp trong mỗi thứ
$grouped_schedule = [];
$unique_courses = [];
$total_weekly_slots = 0;
$active_days = [];

foreach ($lich_hoc_db as $lh) {
    $thu = intval($lh['thu']);
    $ma_lhp = $lh['ma_lhp'];
    $grouped_schedule[$thu][$ma_lhp][] = $lh;
    $unique_courses[$ma_lhp] = $lh;
    $total_weekly_slots++;
    $active_days[$thu] = true;
}

$cards = [];
foreach ($grouped_schedule as $thu => $classes) {
    foreach ($classes as $ma_lhp => $slots) {
        usort($slots, function($a, $b) {
            return intval($a['so_tiet']) - intval($b['so_tiet']);
        });

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

<style>
  /* Timetable Modern High-End Styles */
  .tt-banner-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin: 20px 28px 16px 28px;
    flex-wrap: wrap;
    gap: 12px;
  }
  .tt-banner-row h2 {
    margin: 0;
    font-size: 22px;
    font-weight: 800;
    color: #1E3A8A;
    letter-spacing: -0.3px;
  }
  .tt-banner-actions {
    display: flex;
    gap: 10px;
  }
  .btn-tt-action {
    padding: 8px 16px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.2s;
    border: 1px solid #cbd5e1;
    background: #ffffff;
    color: #334155;
  }
  .btn-tt-action:hover {
    background: #f8fafc;
    border-color: #94a3b8;
  }
  .btn-tt-action.primary {
    background: #1E3A8A;
    color: #ffffff;
    border-color: #1E3A8A;
  }
  .btn-tt-action.primary:hover {
    background: #1e40af;
  }

  /* Stats summary row */
  .tt-stats-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
    margin: 0 28px 20px 28px;
  }
  .tt-stat-box {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 14px 18px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.02);
    display: flex;
    align-items: center;
    justify-content: space-between;
  }
  .tt-stat-box .title {
    font-size: 11.5px;
    font-weight: 700;
    color: #64748b;
    text-transform: uppercase;
    display: block;
    margin-bottom: 4px;
  }
  .tt-stat-box .val {
    font-size: 20px;
    font-weight: 800;
    color: #0f172a;
    margin: 0;
  }
  .tt-stat-box .icon {
    width: 38px;
    height: 38px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
  }

  /* Filter bar */
  .tt-filter-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 16px 20px;
    margin: 0 28px 20px 28px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.02);
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 14px;
  }
  .tt-filter-left {
    display: flex;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
  }
  .tt-filter-left label {
    font-size: 12.5px;
    font-weight: 700;
    color: #475569;
  }
  .tt-filter-select {
    padding: 8px 14px;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    font-size: 13px;
    color: #0f172a;
    background: #f8fafc;
    outline: none;
    font-weight: 600;
  }
  .tt-filter-select:focus {
    border-color: #1E3A8A;
    background: #ffffff;
  }

  /* Grid wrapper */
  .tt-wrapper {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    padding: 20px;
    margin: 0 28px 40px 28px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.04);
    overflow-x: auto;
  }
  .tt-grid-custom {
    display: grid;
    grid-template-columns: 95px repeat(7, minmax(130px, 1fr));
    grid-template-rows: 44px 34px repeat(6, 75px) 34px repeat(6, 75px);
    gap: 5px;
    min-width: 1020px;
    position: relative;
  }

  /* Header Cells */
  .tt-hd-cell {
    background: #1E3A8A;
    color: #ffffff;
    font-weight: 800;
    font-size: 13px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    letter-spacing: 0.3px;
    box-shadow: 0 2px 4px rgba(30, 58, 138, 0.15);
  }
  .tt-hd-cell.tiet-col {
    background: #0f2b66;
  }
  .tt-hd-cell.sun-col {
    background: #dc2626;
  }

  /* Shift Dividers */
  .tt-shift-bar {
    background: #f1f5f9;
    color: #334155;
    font-weight: 800;
    font-size: 12px;
    display: flex;
    align-items: center;
    padding-left: 14px;
    border-radius: 6px;
    letter-spacing: 0.5px;
    border: 1px solid #e2e8f0;
  }

  /* Tiet Side labels */
  .tt-tiet-label {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-left: 3px solid #1E3A8A;
    border-radius: 6px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    padding: 4px 6px;
    text-align: center;
  }
  .tt-tiet-label strong {
    font-size: 12.5px;
    font-weight: 800;
    color: #1E3A8A;
  }
  .tt-tiet-label small {
    font-size: 9.5px;
    color: #64748b;
    margin-top: 2px;
    font-weight: 600;
  }

  /* Background Cell Slots */
  .tt-bg-slot {
    background: #fafafa;
    border: 1px dashed #e2e8f0;
    border-radius: 6px;
    transition: background 0.15s;
  }
  .tt-bg-slot:hover {
    background: #f1f5f9;
  }

  /* Course Card */
  .tt-course-card {
    border-radius: 8px;
    padding: 10px 12px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    box-shadow: 0 4px 10px rgba(0,0,0,0.06);
    z-index: 5;
    border-left: 4px solid;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
  }
  .tt-course-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 18px rgba(0,0,0,0.12);
    z-index: 10;
  }
  .tt-c-name {
    font-weight: 800;
    font-size: 13px;
    line-height: 1.35;
    margin-bottom: 4px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
  }
  .tt-c-teacher {
    font-size: 11.5px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 4px;
    margin-bottom: 4px;
    opacity: 0.9;
  }
  .tt-c-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 4px;
    margin-top: 4px;
  }
  .tt-c-room {
    background: #ffffff;
    border: 1px solid rgba(0,0,0,0.08);
    padding: 2px 7px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 800;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
  }
  .tt-c-code {
    font-size: 10px;
    font-weight: 700;
    opacity: 0.75;
  }

  @media print {
    .site-header, .navbar, .tt-banner-actions, .tt-filter-card, .tt-stats-row {
      display: none !important;
    }
    .tt-wrapper {
      border: none;
      box-shadow: none;
      margin: 0;
      padding: 0;
    }
  }
</style>

<!-- Banner Tiêu Đề -->
<section class="tt-banner-row">
  <div>
    <h2>📅 THỜI KHÓA BIỂU CÁ NHÂN</h2>
    <p style="margin: 4px 0 0 0; font-size: 13px; color: #64748b;">Lịch học trực quan theo tuần dành cho sinh viên <?= htmlspecialchars($sinh_vien['ho_ten']) ?> (<?= htmlspecialchars($sinh_vien['ma_sv']) ?>)</p>
  </div>
  <div class="tt-banner-actions">
    <button type="button" class="btn-tt-action" onclick="window.print()">🖨 In thời khóa biểu</button>
    <button type="button" class="btn-tt-action primary" onclick="exportICalendar()">📅 Xuất lịch iCal / Google</button>
  </div>
</section>

<!-- Thẻ Thống Kê Tổng Quan Lịch Học -->
<section class="tt-stats-row">
  <div class="tt-stat-box">
    <div>
      <span class="title">SỐ MÔN HỌC ĐÃ ĐK</span>
      <h3 class="val" style="color: #1E3A8A;"><?= count($unique_courses) ?> môn</h3>
    </div>
    <div class="icon" style="background: #e0f2fe; color: #0369a1;">📚</div>
  </div>

  <div class="tt-stat-box">
    <div>
      <span class="title">TỔNG SỐ TIẾT / TUẦN</span>
      <h3 class="val" style="color: #16a34a;"><?= $total_weekly_slots ?> tiết</h3>
    </div>
    <div class="icon" style="background: #dcfce7; color: #166534;">⏱️</div>
  </div>

  <div class="tt-stat-box">
    <div>
      <span class="title">SỐ NGÀY ĐI HỌC / TUẦN</span>
      <h3 class="val" style="color: #ea580c;"><?= count($active_days) ?> / 6 ngày</h3>
    </div>
    <div class="icon" style="background: #ffedd5; color: #9a3412;">🏫</div>
  </div>

  <div class="tt-stat-box">
    <div>
      <span class="title">TRẠNG THÁI XẾP LỊCH</span>
      <h3 class="val" style="color: #2563eb; font-size: 16px;">
        <?= count($cards) > 0 ? '✓ Đã sẵn sàng' : 'Chưa có lịch' ?>
      </h3>
    </div>
    <div class="icon" style="background: #ede9fe; color: #6d28d9;">📋</div>
  </div>
</section>

<!-- Bộ Lọc Học Kỳ Động -->
<section class="tt-filter-card">
  <div class="tt-filter-left">
    <label for="filter-hk">Lọc theo học kỳ:</label>
    <select id="filter-hk" class="tt-filter-select" onchange="locTheoHocKy(this.value)">
      <option value="ALL" <?= ($selected_hk === 'ALL' || empty($selected_hk)) ? 'selected' : '' ?>>Tất cả học kỳ đã đăng ký</option>
      <?php foreach ($hoc_ky_list as $hk): 
        $hk_val = $hk['id_hoc_ky'];
        $hk_title = $hk['ten_hoc_ky'] . ' (' . $hk['nam_hoc'] . ')';
        $is_sel = ($selected_hk === strval($hk_val));
      ?>
        <option value="<?= htmlspecialchars($hk_val) ?>" <?= $is_sel ? 'selected' : '' ?>><?= htmlspecialchars($hk_title) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div>
    <button type="button" class="btn-tt-action" onclick="locTheoHocKy('ALL')">🔄 Đặt lại bộ lọc</button>
  </div>
</section>

<!-- Khung Thời Khóa Biểu CSS Grid -->
<div class="tt-wrapper">
  
  <?php if (empty($cards)): ?>
    <div style="text-align: center; padding: 60px 20px; color: #64748b;">
      <div style="font-size: 48px; margin-bottom: 12px;">📅</div>
      <h3 style="margin: 0 0 8px 0; color: #1e293b; font-size: 17px; font-weight: 700;">Chưa có lịch học cho học kỳ này</h3>
      <p style="margin: 0 0 16px 0; font-size: 13px;">Bạn chưa đăng ký học phần nào hoặc các lớp học phần chưa được xếp thời khóa biểu.</p>
      <a href="index.php" style="background: #1E3A8A; color: white; padding: 8px 18px; border-radius: 8px; text-decoration: none; font-size: 13px; font-weight: 700; display: inline-block;">
        👉 Sang trang Đăng ký học phần
      </a>
    </div>
  <?php else: ?>

    <div class="tt-grid-custom">
      
      <!-- Hàng 1: Header Ngày trong tuần -->
      <div class="tt-hd-cell tiet-col" style="grid-row: 1; grid-column: 1;">TIẾT</div>
      <div class="tt-hd-cell" style="grid-row: 1; grid-column: 2;">THỨ 2</div>
      <div class="tt-hd-cell" style="grid-row: 1; grid-column: 3;">THỨ 3</div>
      <div class="tt-hd-cell" style="grid-row: 1; grid-column: 4;">THỨ 4</div>
      <div class="tt-hd-cell" style="grid-row: 1; grid-column: 5;">THỨ 5</div>
      <div class="tt-hd-cell" style="grid-row: 1; grid-column: 6;">THỨ 6</div>
      <div class="tt-hd-cell" style="grid-row: 1; grid-column: 7;">THỨ 7</div>
      <div class="tt-hd-cell sun-col" style="grid-row: 1; grid-column: 8;">CHỦ NHẬT</div>

      <!-- Hàng 2: Buổi Sáng -->
      <div class="tt-shift-bar" style="grid-row: 2; grid-column: 1 / span 8;">
        ☀️ CA SÁNG (Tiết 1 – Tiết 6 • 06:45 – 12:10)
      </div>

      <!-- Hàng 3 đến 8: Tiết 1 - 6 và các ô nền -->
      <?php for ($t = 1; $t <= 6; $t++): $row = $t + 2; ?>
        <div class="tt-tiet-label" style="grid-row: <?= $row ?>; grid-column: 1;">
          <strong>Tiết <?= $t ?></strong>
          <small><?= $tiet_hours[$t] ?></small>
        </div>
        <?php for ($thu = 2; $thu <= 8; $thu++): ?>
          <div class="tt-bg-slot" style="grid-row: <?= $row ?>; grid-column: <?= $thu ?>;"></div>
        <?php endfor; ?>
      <?php endfor; ?>

      <!-- Hàng 9: Buổi Chiều -->
      <div class="tt-shift-bar" style="grid-row: 9; grid-column: 1 / span 8; border-top: 2px solid #cbd5e1;">
        🌙 CA CHIỀU (Tiết 7 – Tiết 12 • 12:45 – 18:10)
      </div>

      <!-- Hàng 10 đến 15: Tiết 7 - 12 và các ô nền -->
      <?php for ($t = 7; $t <= 12; $t++): $row = $t + 3; ?>
        <div class="tt-tiet-label" style="grid-row: <?= $row ?>; grid-column: 1;">
          <strong>Tiết <?= $t ?></strong>
          <small><?= $tiet_hours[$t] ?></small>
        </div>
        <?php for ($thu = 2; $thu <= 8; $thu++): ?>
          <div class="tt-bg-slot" style="grid-row: <?= $row ?>; grid-column: <?= $thu ?>;"></div>
        <?php endfor; ?>
      <?php endfor; ?>

      <!-- Render Thẻ Môn Học Phủ Lên Lưới -->
      <?php foreach ($cards as $card): 
        $thu = intval($card['thu']); // 2 -> 8 (CN = 8)
        $start = $card['start_tiet']; // 1 -> 12
        $span = $card['span'];
        $info = $card['info'];

        if ($start <= 6) {
            $start_row = $start + 2;
        } else {
            $start_row = $start + 3;
        }

        $theme = getPastelTheme($info['ma_mon']);
      ?>
        <div class="tt-course-card"
             style="grid-column: <?= $thu ?>; grid-row: <?= $start_row ?> / span <?= $span ?>; background: <?= $theme['bg'] ?>; border-left-color: <?= $theme['border'] ?>; color: <?= $theme['text'] ?>;">
          <div>
            <div class="tt-c-name"><?= htmlspecialchars($info['ten_mon']) ?></div>
            <div class="tt-c-teacher">👨‍🏫 <?= htmlspecialchars($info['giang_vien']) ?></div>
          </div>
          <div class="tt-c-footer">
            <span class="tt-c-room" style="color: <?= $theme['text'] ?>;">📍 <?= htmlspecialchars($info['phong']) ?></span>
            <span class="tt-c-code"><?= htmlspecialchars($info['ma_lhp']) ?></span>
          </div>
        </div>
      <?php endforeach; ?>

    </div>
  <?php endif; ?>

</div>

<script>
  function locTheoHocKy(hkId) {
    const url = new URL(window.location.href);
    if (hkId === 'ALL' || !hkId) {
      url.searchParams.delete('hk');
    } else {
      url.searchParams.set('hk', hkId);
    }
    window.location.href = url.toString();
  }

  // Xuất file lịch biểu tiêu chuẩn quốc tế (.ics) đồng bộ sang Google Calendar, Apple Calendar, Outlook
  function exportICalendar() {
    const cardsData = <?= json_encode($cards, JSON_UNESCAPED_UNICODE) ?>;
    if (!cardsData || cardsData.length === 0) {
      showToast('Chưa có lịch môn học nào để xuất lịch!', 'error');
      return;
    }

    const dayMap = { 2: 'MO', 3: 'TU', 4: 'WE', 5: 'TH', 6: 'FR', 7: 'SA', 8: 'SU' };
    const timeMap = {
      1: { start: '064500', end: '073500' },
      2: { start: '074000', end: '083000' },
      3: { start: '083500', end: '092500' },
      4: { start: '093000', end: '102000' },
      5: { start: '102500', end: '111500' },
      6: { start: '112000', end: '121000' },
      7: { start: '124500', end: '133500' },
      8: { start: '134000', end: '143000' },
      9: { start: '143500', end: '152500' },
      10: { start: '153000', end: '162000' },
      11: { start: '162500', end: '171500' },
      12: { start: '172000', end: '181000' },
    };

    let ics = "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-//HNDA University//Student Portal Schedule//VI\r\nCALSCALE:GREGORIAN\r\nMETHOD:PUBLISH\r\nX-WR-CALNAME:Thời khóa biểu - <?= addslashes($sinh_vien['ho_ten']) ?>\r\n";

    cardsData.forEach((card, idx) => {
      const thu = parseInt(card.thu);
      const startTiet = parseInt(card.start_tiet);
      const endTiet = startTiet + parseInt(card.span) - 1;
      const info = card.info;
      const byDay = dayMap[thu] || 'MO';

      const startTime = (timeMap[startTiet] && timeMap[startTiet].start) || '070000';
      const endTime = (timeMap[endTiet] && timeMap[endTiet].end) || '093000';

      // Base date: Thứ Hai của tuần hiện tại
      const now = new Date();
      const currentDay = now.getDay() === 0 ? 7 : now.getDay();
      const distance = (thu === 8 ? 7 : thu - 1) - (currentDay - 1);
      const eventDate = new Date(now.setDate(now.getDate() + distance));
      
      const yyyy = eventDate.getFullYear();
      const mm = String(eventDate.getMonth() + 1).padStart(2, '0');
      const dd = String(eventDate.getDate()).padStart(2, '0');
      const datePrefix = `${yyyy}${mm}${dd}`;

      ics += "BEGIN:VEVENT\r\n";
      ics += `UID:tkb-${info.ma_lhp}-${thu}-${startTiet}-${idx}@hnda.edu.vn\r\n`;
      ics += `SUMMARY:${info.ten_mon} (${info.ma_lhp})\r\n`;
      ics += `DESCRIPTION:Giảng viên: ${info.giang_vien}\\nTiết học: Tiết ${startTiet} đến Tiết ${endTiet}\\nHọc kỳ: ${info.ten_hoc_ky || ''} (${info.nam_hoc || ''})\r\n`;
      ics += `LOCATION:${info.phong || 'Chưa xếp phòng'}\r\n`;
      ics += `DTSTART;TZID=Asia/Ho_Chi_Minh:${datePrefix}T${startTime}\r\n`;
      ics += `DTEND;TZID=Asia/Ho_Chi_Minh:${datePrefix}T${endTime}\r\n`;
      ics += `RRULE:FREQ=WEEKLY;BYDAY=${byDay};COUNT=15\r\n`; // Lặp lại 15 tuần của học kỳ
      ics += "STATUS:CONFIRMED\r\n";
      ics += "END:VEVENT\r\n";
    });

    ics += "END:VCALENDAR\r\n";

    const blob = new Blob([ics], { type: 'text/calendar;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement("a");
    link.setAttribute("href", url);
    link.setAttribute("download", `ThoiKhoaBieu_<?= $ma_sv ?>.ics`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);

    showToast('Đã tải tệp lịch (.ics) thành công! Bạn có thể mở tệp này để tự động đồng bộ vào Google Calendar hoặc Lịch điện thoại.', 'success');
  }
</script>

<?php require_once 'includes/footer.php'; ?>
