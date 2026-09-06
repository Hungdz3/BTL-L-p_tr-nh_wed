<?php
// index.php
session_start();
require_once __DIR__ . '/../config/db.php';

// Sử dụng mã sinh viên mặc định để chạy thử (Sau này tích hợp đăng nhập sẽ lấy từ $_SESSION['ma_sv'])
$ma_sv = $_SESSION['ma_sv'] ?? 'SV001';

$db = getDB();

$sinh_vien = null;
$tong_tc = 0;

try {
    // 1. Lấy thông tin chi tiết sinh viên bằng cách JOIN các bảng liên quan
    $stmt = $db->prepare("
        SELECT 
            sv.ma_sv,
            sv.ho_ten,
            lsv.ten_lop,
            k.ten_khoa
        FROM sinh_vien sv
        JOIN lop_sinh_vien lsv ON sv.ma_lop_sv = lsv.ma_lop_sv
        JOIN nganh n ON lsv.ma_nganh = n.ma_nganh
        JOIN khoa k ON n.ma_khoa = k.ma_khoa
        WHERE sv.ma_sv = :ma_sv
    ");
    $stmt->execute([':ma_sv' => $ma_sv]);
    $sinh_vien = $stmt->fetch();

    // 2. Tính tổng số tín chỉ sinh viên đã đăng ký trong học kỳ hiện tại
    $stmt2 = $db->prepare("
        SELECT COALESCE(SUM(mh.so_tin_chi), 0) AS tong_tc
        FROM dang_ky_hoc_phan dk
        JOIN lop_hoc_phan lhp ON dk.ma_lhp = lhp.ma_lhp
        JOIN mon_hoc mh ON lhp.ma_mon = mh.ma_mon
        WHERE dk.ma_sv = :ma_sv AND dk.trang_thai = 'DA_DANG_KY'
    ");
    $stmt2->execute([':ma_sv' => $ma_sv]);
    $tong_tc = $stmt2->fetchColumn();

} catch (Exception $e) {
    // Bắt lỗi CSDL âm thầm để fallback hoạt động trơn tru
}

// Không dùng dữ liệu mẫu - chỉ hiển thị khi có dữ liệu thật từ CSDL
if (!$sinh_vien) {
    $sinh_vien = [
        'ma_sv' => $ma_sv,
        'ho_ten' => 'Chưa có thông tin',
        'ten_lop' => 'N/A',
        'ten_khoa' => 'N/A'
    ];
}

require_once 'includes/header.php';
?>

<!-- Banner chào -->
<section class="banner">
  <h2>Xin chào, <?= htmlspecialchars($sinh_vien['ho_ten']) ?></h2>
  <p>Cổng đăng ký học phần chính thức học kỳ mới năm học 2026-2027</p>
</section>

<!-- Thông tin sinh viên -->
<section class="sv-info">
  <div><small>MÃ SINH VIÊN</small><strong><?= htmlspecialchars($sinh_vien['ma_sv']) ?></strong></div>
  <div><small>LỚP CHUYÊN NGÀNH</small><strong><?= htmlspecialchars($sinh_vien['ten_lop']) ?></strong></div>
  <div><small>KHOA CHỦ QUẢN</small><strong><?= htmlspecialchars($sinh_vien['ten_khoa']) ?></strong></div>
  <div><small>HỌC KỲ ĐĂNG KÝ</small><strong>Học Kỳ 1 (2026-2027)</strong></div>
  <div class="tc-counter">
    <small>SỐ TÍN CHỈ ĐÃ ĐĂNG KÝ</small>
    <strong id="so-tc"><?= $tong_tc ?>/24 tín chỉ</strong>
  </div>
</section>

<!-- Layout giao diện chính -->
<div class="main-layout">

  <!-- Cột trái: Bảng danh sách lớp học phần -->
  <div class="col-main">
    <!-- Thanh tìm kiếm -->
    <div class="search-bar">
      <input type="text" id="search-input" placeholder="🔍 Nhập tên môn học hoặc mã lớp học phần để tìm kiếm..." onkeyup="if(event.key === 'Enter') timKiem()">
      <button onclick="timKiem()">Tìm kiếm</button>
    </div>

    <h3>Học phần mở đăng ký học kỳ này</h3>

    <table style="width: 100%;">
      <thead>
        <tr>
          <th style="width: 110px;">Mã môn</th>
          <th>Tên môn học / Học phần</th>
          <th style="text-align: center; width: 80px;">Tín chỉ</th>
          <th style="text-align: center; width: 120px;">Lớp đang mở</th>
          <th style="text-align: center; width: 150px;">Trạng thái</th>
          <th style="text-align: center; width: 130px;">Danh sách lớp</th>
        </tr>
      </thead>
      <tbody id="danh-sach-hp">
        <!-- Chèn danh sách qua AJAX (dang_ky.js) -->
      </tbody>
    </table>

    <!-- Thanh chuyển trang (Pagination) -->
    <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 16px; padding: 12px 16px; background: #ffffff; border-radius: 8px; border: 1px solid #e2e8f0; font-size: 13px;">
      <div id="hp-pagination-info" style="color: #64748b;">Hiển thị 0 trong số 0 học phần</div>
      <div id="hp-pagination-btns" style="display: flex; gap: 6px;"></div>
    </div>
  </div>

  <!-- Cột phải: Giỏ hàng đăng ký -->
  <aside class="col-sidebar">
    <h4>Học phần đã đăng ký</h4>
    <p class="sidebar-hint">Danh sách các môn học đăng ký thành công trong học kỳ này</p>
    
    <div id="ds-da-chon">
      <!-- Render động qua JS -->
    </div>
    
    <div class="sidebar-footer">
      <p>Số môn: <strong id="tong-mon">0</strong> môn</p>
      <p>Tổng tín chỉ: <strong id="tong-tc-chon" class="highlight">0 TC</strong></p>
      <button class="btn-xac-nhan" onclick="xacNhanDangKy()">Xác nhận hoàn tất</button>
    </div>
  </aside>

</div>

<script>
  // Chuyển mã sinh viên sang JS để thực hiện gọi API
  const MSV = '<?= htmlspecialchars($sinh_vien['ma_sv']) ?>';
</script>
<script src="../assets/js/dang_ky.js"></script>

<?php require_once 'includes/footer.php'; ?>
