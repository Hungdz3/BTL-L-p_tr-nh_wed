<?php
// trang-chu.php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once 'Notifications_functions.php';

// Sử dụng mã sinh viên mặc định SV001
$ma_sv = $_SESSION['ma_sv'] ?? 'SV001';
$db = getDB();

$sinh_vien = null;
$tong_tc_tich_luy = 0;
$tong_tc_yeu_cau = 130;
$gpa_he_4 = 0.00;
$gpa_he_10 = 0.00;
$notifications = [];
$notification_error = '';

try {
    // 1. Lấy thông tin sinh viên
    $stmt = $db->prepare("
        SELECT 
            sv.ma_sv,
            sv.ho_ten,
            sv.trang_thai AS trang_thai_sv,
            lsv.ten_lop,
            k.ten_khoa,
            lsv.khoa_hoc
        FROM sinh_vien sv
        LEFT JOIN lop_sinh_vien lsv ON sv.ma_lop_sv = lsv.ma_lop_sv
        LEFT JOIN nganh n ON lsv.ma_nganh = n.ma_nganh
        LEFT JOIN khoa k ON n.ma_khoa = k.ma_khoa
        WHERE sv.ma_sv = :ma_sv
    ");
    $stmt->execute([':ma_sv' => $ma_sv]);
    $sinh_vien = $stmt->fetch();

    // 2. Lấy số tín chỉ tích lũy (các môn đã đạt) từ bảng điểm SQL
    $stmt_tc = $db->prepare("
        SELECT COALESCE(SUM(mh.so_tin_chi), 0)
        FROM diem d
        JOIN dang_ky_hoc_phan dk ON d.dang_ky_id = dk.id
        JOIN lop_hoc_phan lhp ON dk.ma_lhp = lhp.ma_lhp
        JOIN mon_hoc mh ON lhp.ma_mon = mh.ma_mon
        WHERE dk.ma_sv = :ma_sv AND d.diem_tong_ket >= 4.0
    ");
    $stmt_tc->execute([':ma_sv' => $ma_sv]);
    $tong_tc_tich_luy = (int)$stmt_tc->fetchColumn();

    // 3. Tính điểm trung bình GPA
    $stmt_gpa = $db->prepare("
        SELECT 
            d.diem_tong_ket,
            mh.so_tin_chi
        FROM diem d
        JOIN dang_ky_hoc_phan dk ON d.dang_ky_id = dk.id
        JOIN lop_hoc_phan lhp ON dk.ma_lhp = lhp.ma_lhp
        JOIN mon_hoc mh ON lhp.ma_mon = mh.ma_mon
        WHERE dk.ma_sv = :ma_sv AND d.diem_tong_ket IS NOT NULL
    ");
    $stmt_gpa->execute([':ma_sv' => $ma_sv]);
    $diem_list = $stmt_gpa->fetchAll();
    if (count($diem_list) > 0) {
        $tong_diem_he_10 = 0;
        $tong_tin_chi = 0;
        $tong_diem_he_4 = 0;
        foreach ($diem_list as $row) {
            $diem_10 = floatval($row['diem_tong_ket']);
            $tc = intval($row['so_tin_chi']);
            
            $tong_diem_he_10 += $diem_10 * $tc;
            $tong_tin_chi += $tc;
            
            // Quy đổi sang hệ 4
            if ($diem_10 >= 9.0) $diem_4 = 4.0;
            elseif ($diem_10 >= 8.5) $diem_4 = 3.7;
            elseif ($diem_10 >= 8.0) $diem_4 = 3.5;
            elseif ($diem_10 >= 7.0) $diem_4 = 3.0;
            elseif ($diem_10 >= 6.5) $diem_4 = 2.5;
            elseif ($diem_10 >= 5.5) $diem_4 = 2.0;
            elseif ($diem_10 >= 5.0) $diem_4 = 1.5;
            elseif ($diem_10 >= 4.0) $diem_4 = 1.0;
            else $diem_4 = 0.0;
            
            $tong_diem_he_4 += $diem_4 * $tc;
        }
        if ($tong_tin_chi > 0) {
            $gpa_he_10 = round($tong_diem_he_10 / $tong_tin_chi, 2);
            $gpa_he_4 = round($tong_diem_he_4 / $tong_tin_chi, 2);
        }
    }
} catch (Exception $e) {
    // Silent catch
}

$notifications = [];
$notification_error = '';
try {
    $notifications = getPublishedNotifications($db, 3, 'SINH_VIEN');
} catch (Exception $e) {
    $notification_error = 'Không thể tải danh sách thông báo. Vui lòng thử lại sau.';
}

if (!$sinh_vien) {
    $sinh_vien = [
        'ma_sv' => $ma_sv,
        'ho_ten' => 'NGUYỄN VĂN A',
        'trang_thai_sv' => 'DANG_HOC',
        'ten_lop' => 'CNTT D2024A',
        'ten_khoa' => 'KHOA TOÁN - CÔNG NGHỆ THÔNG TIN',
        'khoa_hoc' => '2024'
    ];
}

$phan_tram_tiendo = round(($tong_tc_tich_luy / $tong_tc_yeu_cau) * 100);

require_once 'includes/header.php';
?>

<!-- Banner chào -->
<section class="banner-welcome">
  <div class="banner-welcome-content">
    <h2>Xin chào, <?= htmlspecialchars($sinh_vien['ho_ten']) ?></h2>
    <p>MSV: <?= htmlspecialchars($sinh_vien['ma_sv']) ?> • Lớp: <?= htmlspecialchars($sinh_vien['ten_lop']) ?> • Khoa: <?= htmlspecialchars($sinh_vien['ten_khoa']) ?></p>
  </div>
</section>

<!-- Dashboard Grid -->
<div class="dashboard-grid">
  
  <!-- Cột 1: Thông tin sinh viên -->
  <div class="card-info">
    <div class="card-header-row">
      <h3>THÔNG TIN SINH VIÊN</h3>
      <span class="badge-status-active">ĐANG HỌC</span>
    </div>
    <ul class="info-list">
      <li><span>Mã sinh viên:</span> <strong><?= htmlspecialchars($sinh_vien['ma_sv']) ?></strong></li>
      <li><span>Lớp:</span> <strong><?= htmlspecialchars($sinh_vien['ten_lop']) ?></strong></li>
      <li><span>Khoa:</span> <strong><?= htmlspecialchars($sinh_vien['ten_khoa']) ?></strong></li>
      <li><span>Khóa:</span> <strong><?= htmlspecialchars($sinh_vien['khoa_hoc']) ?></strong></li>
      <li><span>Cố vấn học tập:</span> <strong>Nguyễn Đoàn H</strong></li>
    </ul>
  </div>

  <!-- Cột 2: Tiến độ học tập -->
  <div class="card-progress">
    <h3>TIẾN ĐỘ HỌC TẬP</h3>
    <div class="progress-container">
      <div class="progress-labels">
        <span class="progress-numbers"><strong><?= $tong_tc_tich_luy ?></strong> / <?= $tong_tc_yeu_cau ?> Tín chỉ</span>
        <span class="progress-percent"><?= $phan_tram_tiendo ?>%</span>
      </div>
      <div class="progress-bar-bg">
        <div class="progress-bar-fill" style="width: <?= $phan_tram_tiendo ?>%;"></div>
      </div>
    </div>
  </div>

  <!-- Cột 3: Kết quả học tập -->
  <div class="card-results">
    <h3>KẾT QUẢ HỌC TẬP</h3>
    <div class="results-container">
      <div class="result-box">
        <span class="result-label">Điểm TB tích lũy hệ 4</span>
        <span class="result-value"><?= number_format($gpa_he_4, 2) ?></span>
      </div>
      <div class="result-box">
        <span class="result-label">Điểm TB tích lũy hệ 10</span>
        <span class="result-value"><?= number_format($gpa_he_10, 2) ?></span>
      </div>
    </div>
  </div>

</div>

<!-- Banner lớn giới thiệu dưới -->
<section class="banner-bottom-promo">
  <div class="promo-left">
    <h2>Hệ thống quản lý khóa học và đăng ký học phần</h2>
    <p>Nền tảng giúp sinh viên dễ dàng tra cứu thông tin, đăng ký học phần, xem lịch học và quản lý quá trình học tập một cách hiệu quả.</p>
  </div>
  <div class="promo-right">
    <div class="promo-illustration">
      <!-- Biểu tượng SVG / Vẽ đồ họa minh họa -->
      <svg width="200" height="150" viewBox="0 0 200 150" fill="none" xmlns="http://www.w3.org/2000/svg">
        <rect x="20" y="20" width="160" height="110" rx="8" fill="white" fill-opacity="0.15" stroke="white" stroke-width="2"/>
        <rect x="40" y="45" width="80" height="8" rx="4" fill="white" fill-opacity="0.8"/>
        <rect x="40" y="65" width="120" height="6" rx="3" fill="white" fill-opacity="0.5"/>
        <rect x="40" y="80" width="100" height="6" rx="3" fill="white" fill-opacity="0.5"/>
        <circle cx="150" cy="110" r="18" fill="#F0C040"/>
        <path d="M145 110L148 113L155 106" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        <rect x="145" y="45" width="15" height="15" rx="3" fill="#FFF"/>
      </svg>
    </div>
  </div>
</section>

<!-- Section Thông Báo Mới Cập Nhật trên Trang Chủ -->
<section id="notifications" class="home-notifications" style="padding: 0 28px; max-width: 1400px; margin: 0 auto 32px auto;">
  <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 20px;">
    <div>
      <p style="color: #0284CF; font-weight: 800; font-size: 11px; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 4px;">TIN MỚI CẬP NHẬT</p>
      <h2 style="font-size: 22px; color: #1a3a6b; margin: 0; font-weight: 700;">Thông báo từ nhà trường</h2>
    </div>
    <a href="thong-bao.php" style="color: #1e4d8c; font-weight: 700; text-decoration: none; font-size: 13px; display: inline-flex; align-items: center; gap: 4px;">Xem tất cả thông báo ➔</a>
  </div>

  <?php if ($notification_error !== ''): ?>
    <div class="no-data-card"><?= htmlspecialchars($notification_error, ENT_QUOTES, 'UTF-8') ?></div>
  <?php elseif (empty($notifications)): ?>
    <div class="no-data-card">Hiện chưa có thông báo mới nào.</div>
  <?php else: ?>
    <div class="home-notifications-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
      <?php foreach ($notifications as $n):
        $status = getNotificationStatus($n['title'], $n['description']);
        $image_url = getNotificationImage($n);
      ?>
        <a href="thong-bao.php?id=<?= $n['id'] ?>" class="home-notification-card" style="text-decoration: none; color: inherit; background: white; border-radius: 12px; border: 1px solid #eef2f6; overflow: hidden; box-shadow: 0 3px 10px rgba(0,0,0,0.04); transition: transform 0.2s, box-shadow 0.2s; display: flex; flex-direction: column;" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 8px 20px rgba(0,0,0,0.08)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 3px 10px rgba(0,0,0,0.04)';">
          <div class="home-notification-image" style="height: 140px; overflow: hidden; background: #f1f5f9; display: flex; align-items: center; justify-content: center;">
            <img
                 src="../<?= htmlspecialchars($image_url, ENT_QUOTES, 'UTF-8') ?>"
                 onerror="this.src='../assets/illustration.png'"
                 alt="<?= htmlspecialchars($n['title'], ENT_QUOTES, 'UTF-8') ?>"
                 style="width: 100%; height: 100%; object-fit: cover;">
          </div>
          <div class="home-notification-content" style="padding: 18px; display: flex; flex-direction: column; flex: 1; justify-content: space-between;">
            <div>
              <h3 style="font-size: 15px; color: #0f172a; margin-bottom: 8px; font-weight: 700; line-height: 1.4;"><?= htmlspecialchars($n['title'], ENT_QUOTES, 'UTF-8') ?></h3>
              <p style="font-size: 13px; color: #64748b; line-height: 1.5; margin-bottom: 14px;"><?= htmlspecialchars(mb_substr(strip_tags($n['description']), 0, 110, 'UTF-8')) ?>...</p>
            </div>
            <div class="home-notification-tags" style="display: flex; justify-content: space-between; align-items: center; font-size: 12px; color: #64748b; border-top: 1px dashed #f1f5f9; padding-top: 10px; margin-top: 10px;">
              <span style="background: #dbeafe; color: #1e40af; font-weight: 700; padding: 2px 8px; border-radius: 4px; font-size: 11px;"><?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8') ?></span>
              <?php if (!empty($n['ngay_dang'])): ?>
                <time datetime="<?= htmlspecialchars($n['ngay_dang'], ENT_QUOTES, 'UTF-8') ?>" style="font-weight: 500;">
                  📅 <?= date('d/m/Y', strtotime($n['ngay_dang'])) ?>
                </time>
              <?php endif; ?>
            </div>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>

<?php require_once 'includes/footer.php'; ?>
