<?php
// trang-chu.php
session_start();
require_once __DIR__ . '/../config/db.php';

// Sử dụng mã sinh viên mặc định SV001
$ma_sv = $_SESSION['ma_sv'] ?? 'SV001';
$db = getDB();

$sinh_vien = null;
$tong_tc_tich_luy = 0;
$tong_tc_yeu_cau = 130;
$gpa_he_4 = 0.00;
$gpa_he_10 = 0.00;

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

if (!$sinh_vien) {
    $sinh_vien = [
        'ma_sv' => $ma_sv,
        'ho_ten' => 'Chưa có thông tin',
        'trang_thai_sv' => '',
        'ten_lop' => 'N/A',
        'ten_khoa' => 'N/A',
        'khoa_hoc' => 'N/A'
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
<?php require_once 'includes/footer.php'; ?>
