<?php
// thong-bao.php
session_start();
require_once __DIR__ . '/../config/db.php';

$ma_sv = $_SESSION['ma_sv'] ?? 'SV001';
$db = getDB();

$sinh_vien = null;
try {
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
} catch (Exception $e) {
    // Silent catch
}

if (!$sinh_vien) {
    $sinh_vien = [
        'ma_sv' => $ma_sv,
        'ho_ten' => 'Chưa có thông tin',
        'ten_lop' => 'N/A',
        'ten_khoa' => 'N/A'
    ];
}

// Lấy danh sách thông báo từ CSDL (chỉ cho phép xem các thông báo công khai dành cho Sinh viên hoặc Toàn trường)
$notices = [];
try {
    $stmt_tb = $db->query("
        SELECT id, tieu_de, noi_dung, doi_tuong_nhan, COALESCE(ngay_dang::text, '') AS ngay_dang, file_dinh_kem, trang_thai
        FROM thong_bao
        WHERE doi_tuong_nhan IN ('TAT_CA', 'SINH_VIEN') AND trang_thai = 'DA_GUI'
        ORDER BY id DESC
        LIMIT 20
    ");
    $raw_notices = $stmt_tb->fetchAll(PDO::FETCH_ASSOC);
    foreach ($raw_notices as $idx => $tb) {
        $date_str = !empty($tb['ngay_dang']) ? date('d/m/Y', strtotime($tb['ngay_dang'])) : 'Mới cập nhật';
        $tag_label = ($tb['doi_tuong_nhan'] === 'SINH_VIEN') ? 'SINH VIÊN' : 'TOÀN TRƯỜNG';
        $tag_class = ($tb['doi_tuong_nhan'] === 'SINH_VIEN') ? 'tag-registration' : 'tag-general';
        
        $notices[] = [
            'id' => $tb['id'],
            'tag' => $tag_label,
            'tag_class' => $tag_class,
            'title' => $tb['tieu_de'],
            'date' => $date_str,
            'summary' => mb_substr(strip_tags($tb['noi_dung']), 0, 120, 'UTF-8') . '...',
            'content' => nl2br(htmlspecialchars($tb['noi_dung'])),
            'file_dinh_kem' => $tb['file_dinh_kem'] ?? ''
        ];
    }
} catch (Exception $e) {
    // If table structure differs slightly, query general
    try {
        $stmt_tb = $db->query("SELECT * FROM thong_bao ORDER BY id DESC LIMIT 20");
        $raw_notices = $stmt_tb->fetchAll(PDO::FETCH_ASSOC);
        foreach ($raw_notices as $tb) {
            $notices[] = [
                'id' => $tb['id'],
                'tag' => 'THÔNG BÁO',
                'tag_class' => 'tag-general',
                'title' => $tb['tieu_de'] ?? 'Thông báo',
                'date' => date('d/m/Y'),
                'summary' => mb_substr(strip_tags($tb['noi_dung'] ?? ''), 0, 120, 'UTF-8') . '...',
                'content' => nl2br(htmlspecialchars($tb['noi_dung'] ?? '')),
                'file_dinh_kem' => $tb['file_dinh_kem'] ?? ''
            ];
        }
    } catch (Exception $e2) {}
}

$id_selected = isset($_GET['id']) ? intval($_GET['id']) : 1;
$current_notice = null;
foreach ($notices as $n) {
    if ($n['id'] === $id_selected) {
        $current_notice = $n;
        break;
    }
}
if (!$current_notice && !empty($notices)) {
    $current_notice = $notices[0];
}

require_once 'includes/header.php';
?>

<section class="banner-title-row">
  <h2>THÔNG BÁO TỪ NHÀ TRƯỜNG</h2>
  <p class="subtitle-text">Thông báo kế hoạch giảng dạy, đăng ký học tập & tin tức chung từ Phòng Đào tạo</p>
</section>

<div class="notice-board-layout">
  
  <!-- Cột bên trái: Danh sách các tin thông báo -->
  <div class="notice-list">
    <?php foreach ($notices as $n): 
        $is_active = ($n['id'] === $current_notice['id']);
    ?>
      <a href="thong-bao.php?id=<?= $n['id'] ?>" class="notice-card <?= $is_active ? 'active' : '' ?>">
        <div class="notice-card-header">
          <span class="tag-badge <?= $n['tag_class'] ?>"><?= htmlspecialchars($n['tag']) ?></span>
          <span class="notice-date">📅 <?= htmlspecialchars($n['date']) ?></span>
        </div>
        <h4 class="notice-card-title"><?= htmlspecialchars($n['title']) ?></h4>
        <p class="notice-card-summary"><?= htmlspecialchars($n['summary']) ?></p>
      </a>
    <?php endforeach; ?>
  </div>

  <!-- Cột bên phải: Chi tiết thông báo được chọn -->
  <article class="notice-detail-container">
    <?php if ($current_notice): ?>
      <div class="notice-detail-header">
        <span class="tag-badge <?= $current_notice['tag_class'] ?>"><?= htmlspecialchars($current_notice['tag']) ?></span>
        <span class="notice-date">Ngày đăng: <?= htmlspecialchars($current_notice['date']) ?></span>
      </div>
      <h2 class="notice-detail-title"><?= htmlspecialchars($current_notice['title']) ?></h2>
      <hr class="notice-divider">
      <div class="notice-detail-content">
        <?= $current_notice['content'] ?>
        <?php if (!empty($current_notice['file_dinh_kem'])): ?>
          <div style="margin-top: 20px; padding: 12px 16px; background: #f1f5f9; border-radius: 8px; display: inline-block;">
            <a href="../assets/uploads/notifications/<?= htmlspecialchars($current_notice['file_dinh_kem']) ?>" download style="color: #1E3A8A; font-weight: 600; text-decoration: none; display: flex; align-items: center; gap: 8px;">
              📎 Tải tệp đính kèm: <?= htmlspecialchars($current_notice['file_dinh_kem']) ?>
            </a>
          </div>
        <?php endif; ?>
      </div>
    <?php else: ?>
      <div class="no-notice-selected">
        <p>Vui lòng chọn một thông báo từ danh sách để xem chi tiết.</p>
      </div>
    <?php endif; ?>
  </article>

</div>

<?php require_once 'includes/footer.php'; ?>
