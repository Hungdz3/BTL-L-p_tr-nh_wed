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
        LEFT JOIN lop_sinh_vien lsv ON sv.ma_lop_sv = lsv.ma_lop_sv
        LEFT JOIN nganh n ON lsv.ma_nganh = n.ma_nganh
        LEFT JOIN khoa k ON n.ma_khoa = k.ma_khoa
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
        'ho_ten' => 'NGUYỄN VĂN A',
        'ten_lop' => 'CNTT D2024A',
        'ten_khoa' => 'Khoa Toán - CNTT'
    ];
}

// Lấy danh sách thông báo chính thức từ CSDL (đã gửi, dành cho sinh viên hoặc tất cả)
$notices = [];
try {
    $stmt = $db->query("
        SELECT id, tieu_de, noi_dung, doi_tuong_nhan, trang_thai, file_dinh_kem, ngay_dang
        FROM thong_bao
        WHERE trang_thai = 'DA_GUI'
          AND doi_tuong_nhan IN ('TAT_CA', 'SINH_VIEN')
        ORDER BY ngay_dang DESC, id DESC
    ");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($rows as $row) {
        $tag = 'TOÀN TRƯỜNG';
        $tag_class = 'tag-general';
        if ($row['doi_tuong_nhan'] === 'SINH_VIEN') {
            $tag = 'SINH VIÊN';
            $tag_class = 'tag-registration';
        }

        $noi_dung_plain = strip_tags($row['noi_dung'] ?? '');
        $summary = mb_substr($noi_dung_plain, 0, 140, 'UTF-8');
        if (mb_strlen($noi_dung_plain, 'UTF-8') > 140) {
            $summary .= '...';
        }

        $date_formatted = !empty($row['ngay_dang']) 
            ? date('d/m/Y H:i', strtotime($row['ngay_dang']))
            : date('d/m/Y');

        $notices[] = [
            'id' => (int)$row['id'],
            'tag' => $tag,
            'tag_class' => $tag_class,
            'title' => $row['tieu_de'],
            'date' => $date_formatted,
            'summary' => $summary,
            'content' => nl2br(htmlspecialchars($row['noi_dung'] ?? '', ENT_QUOTES, 'UTF-8')),
            'file_dinh_kem' => $row['file_dinh_kem'] ?? null
        ];
    }
} catch (Exception $e) {
    // Fallback if query fails
}

$id_selected = isset($_GET['id']) ? intval($_GET['id']) : 0;
$current_notice = null;
if ($id_selected > 0) {
    foreach ($notices as $n) {
        if ($n['id'] === $id_selected) {
            $current_notice = $n;
            break;
        }
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
  
  <?php if (empty($notices)): ?>
    <div style="width: 100%; background: white; padding: 40px; border-radius: 12px; text-align: center; color: #64748b; border: 1px solid #eef2f6;">
      <div style="font-size: 36px; margin-bottom: 12px;">📭</div>
      <h3 style="margin-bottom: 8px; color: #1e293b;">Hiện chưa có thông báo mới nào</h3>
      <p style="margin: 0; font-size: 13px;">Vui lòng quay lại sau để cập nhật các thông báo mới nhất từ Nhà trường.</p>
    </div>
  <?php else: ?>
    <!-- Cột bên trái: Danh sách các tin thông báo -->
    <div class="notice-list">
      <?php foreach ($notices as $n): 
          $is_active = ($current_notice && $n['id'] === $current_notice['id']);
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
        <div class="notice-detail-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
          <span class="tag-badge <?= $current_notice['tag_class'] ?>"><?= htmlspecialchars($current_notice['tag']) ?></span>
          <span class="notice-date" style="font-size: 12px; color: #64748b;">📅 Ngày đăng: <?= htmlspecialchars($current_notice['date']) ?></span>
        </div>
        <h2 class="notice-detail-title" style="font-size: 18px; color: #1a3a6b; margin-bottom: 16px; line-height: 1.4;"><?= htmlspecialchars($current_notice['title']) ?></h2>
        <hr class="notice-divider" style="border: 0; border-top: 1px solid #e2e8f0; margin-bottom: 20px;">
        <div class="notice-detail-content" style="font-size: 14px; color: #334155; line-height: 1.6;">
          <?= $current_notice['content'] ?>
          
          <?php if (!empty($current_notice['file_dinh_kem'])): ?>
            <div style="margin-top: 24px; padding: 14px 18px; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px;">
              <a href="../<?= htmlspecialchars($current_notice['file_dinh_kem']) ?>" download target="_blank" style="color: #1e40af; font-weight: 700; text-decoration: none; font-size: 13px; display: inline-flex; align-items: center; gap: 8px;">
                📎 Tải tệp đính kèm: <?= htmlspecialchars(basename($current_notice['file_dinh_kem'])) ?>
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
  <?php endif; ?>

</div>

<?php require_once 'includes/footer.php'; ?>
