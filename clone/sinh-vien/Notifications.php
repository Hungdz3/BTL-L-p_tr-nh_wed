<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once 'Notifications_functions.php';

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
    // Header vẫn hiển thị thông tin mặc định nếu chưa tìm thấy sinh viên.
}

if (!$sinh_vien) {
    $sinh_vien = [
        'ma_sv' => $ma_sv,
        'ho_ten' => 'NGUYỄN VĂN A',
        'ten_lop' => 'CNTT D2024A',
        'ten_khoa' => 'Khoa Toán - CNTT',
    ];
}

$notifications = [];
$notification_error = '';

try {
    $notifications = getPublishedNotifications($db);
} catch (Exception $e) {
    $notification_error = 'Không thể tải danh sách thông báo. Vui lòng thử lại sau.';
}

require_once 'includes/header.php';
?>

<section class="banner-title-row">
  <h2>TIN TỨC &amp; THÔNG BÁO CHUNG</h2>
  <p class="subtitle-text">Xem các tin tức đào tạo và thông báo mới nhất từ nhà trường</p>
</section>

<div class="notifications-layout">
  <div class="notifications-list-col">
    <h3>DANH SÁCH THÔNG BÁO</h3>

    <?php if ($notification_error !== ''): ?>
      <div class="no-data-card"><?= htmlspecialchars($notification_error, ENT_QUOTES, 'UTF-8') ?></div>
    <?php elseif (empty($notifications)): ?>
      <div class="no-data-card">Không có thông báo nào được tìm thấy.</div>
    <?php else: ?>
      <?php foreach ($notifications as $n):
        $status = getNotificationStatus($n['title'], $n['description']);
        $badge_class = mb_strlen($n['description'], 'UTF-8') >= 100
            ? 'status-badge-detailed'
            : 'status-badge-short';
        $image_url = getNotificationImage($n);
      ?>
        <div class="notification-item-card">
          <div class="notification-img-wrapper">
            <img class="notification-img"
                 src="<?= htmlspecialchars($image_url, ENT_QUOTES, 'UTF-8') ?>"
                 alt="<?= htmlspecialchars($n['title'], ENT_QUOTES, 'UTF-8') ?>">
          </div>
          <div class="notification-body">
            <div>
              <h4 class="notification-item-title"><?= htmlspecialchars($n['title'], ENT_QUOTES, 'UTF-8') ?></h4>
              <p class="notification-item-desc"><?= htmlspecialchars($n['description'], ENT_QUOTES, 'UTF-8') ?></p>
            </div>
            <div class="notification-meta-row">
              <span><strong>Mã số:</strong> #<?= (int) $n['id'] ?></span>
              <span><strong>Độ dài:</strong> <span class="<?= $badge_class ?>"><?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8') ?></span></span>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>

<?php require_once 'includes/footer.php'; ?>
