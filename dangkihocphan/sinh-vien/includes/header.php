<?php
// sinh-vien/includes/header.php
// Nhận $sinh_vien từ trang gọi vào

$ten_sv  = $sinh_vien['ho_ten']  ?? 'Sinh viên';
$msv     = $sinh_vien['ma_sv']   ?? '';
$lop     = $sinh_vien['ten_lop'] ?? '';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Cổng Thông Tin Sinh Viên — HNDA</title>
  <link rel="icon" type="image/svg+xml" href="../assets/images/favicon.svg">
  <link rel="stylesheet" href="../assets/css/style.css">
  <script src="../assets/js/global.js"></script>
</head>
<body>

<!-- Header -->
<header class="site-header" style="background: #ffffff; padding: 10px 28px; border-bottom: 2px solid #3b82f6; display: flex; justify-content: space-between; align-items: center;">
  <div class="header-left">
    <a href="trang-chu.php" style="text-decoration: none; color: inherit; display: flex; align-items: center; gap: 12px;" title="Về trang chủ Sinh viên">
      <div style="background: #1E3A8A; width: 42px; height: 42px; border-radius: 8px; color: #ffffff; font-weight: 800; font-size: 22px; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 6px rgba(30, 58, 138, 0.2);">A</div>
      <div>
        <strong style="color: #1E3A8A; font-weight: 800; font-size: 15px; display: block; line-height: 1.2;">TRƯỜNG ĐẠI HỌC HNDA</strong>
        <span style="color: #B91C1C; font-weight: 700; font-size: 11px; display: block; text-transform: uppercase; margin-top: 2px;">CỔNG THÔNG TIN SINH VIÊN</span>
      </div>
    </a>
  </div>
  <div class="header-right" style="display: flex; align-items: center; gap: 14px; position: relative;">
    <div class="user-text" style="text-align: right;">
      <span class="user-name" style="color: #0f172a; font-weight: 700; font-size: 14px; display: block;"><?= htmlspecialchars($ten_sv) ?></span>
      <small class="user-info" style="color: #64748b; font-size: 11px; display: block; margin-top: 2px;">MSV: <?= htmlspecialchars($msv) ?> • Lớp: <?= htmlspecialchars($lop) ?></small>
    </div>
    <!-- Avatar Cụm Clickable -->
    <div class="avatar" onclick="toggleUserDropdown(event)" style="background: #cbd5e1; width: 42px; height: 42px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; color: #334155; font-size: 16px; cursor: pointer; user-select: none; transition: transform 0.15s;" title="Bấm vào đây để mở menu cá nhân">
      <?= mb_strtoupper(mb_substr($ten_sv, 0, 1, 'UTF-8'), 'UTF-8') ?>
    </div>

    <!-- User Dropdown Menu -->
    <div class="user-dropdown-menu" id="userDropdownMenu" style="display: none; position: absolute; right: 0; top: 52px; background: white; border: 1px solid #e2e8f0; border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.15); min-width: 200px; z-index: 10002; padding: 6px 0;">
      <div style="padding: 10px 16px; border-bottom: 1px solid #f1f5f9; font-size: 13px;">
        <strong style="color: #1e293b; display: block;"><?= htmlspecialchars($ten_sv) ?></strong>
        <span style="color: #64748b; font-size: 11px;">MSV: <?= htmlspecialchars($msv) ?></span>
      </div>
      <a href="javascript:void(0)" onclick="confirmLogout(event, '../logout.php')" style="display: flex; align-items: center; gap: 8px; padding: 10px 16px; color: #dc2626; text-decoration: none; font-size: 13px; font-weight: 600; transition: background 0.15s;">
        🚪 Đăng xuất
      </a>
    </div>
  </div>
</header>

<!-- Navbar -->
<?php
$current_page = basename($_SERVER['PHP_SELF']);
$notification_url = $current_page === 'trang-chu.php'
    ? '#notifications'
    : 'trang-chu.php#notifications';
?>
<nav class="navbar">
  <a href="trang-chu.php" class="<?= $current_page == 'trang-chu.php' ? 'active' : '' ?>">Trang Chủ</a>
  <a href="thong-bao.php" class="<?= $current_page == 'thong-bao.php' ? 'active' : '' ?>">Thông Báo</a>
  <a href="index.php" class="<?= $current_page == 'index.php' ? 'active' : '' ?>">Đăng Ký Học Phần</a>
  <a href="lich-hoc.php" class="<?= $current_page == 'lich-hoc.php' ? 'active' : '' ?>">Lịch Học</a>
  <a href="sinh-vien.php" class="<?= $current_page == 'sinh-vien.php' ? 'active' : '' ?>">Thông Tin Cá Nhân</a>
</nav>

<!-- Modal Xác nhận Đăng xuất -->
<div class="custom-modal-admin" id="modal-logout-confirm" style="display: none; max-width: 400px; text-align: center; padding: 24px; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; z-index: 10001;">
  <div style="font-size: 40px; margin-bottom: 8px;">🚪</div>
  <h3 style="margin: 0 0 8px 0; color: #1e293b; font-size: 16px; font-weight: 700;">Xác nhận Đăng Xuất</h3>
  <p style="margin: 0 0 20px 0; color: #64748b; font-size: 13px;">Bạn có chắc chắn muốn đăng xuất khỏi hệ thống hay không?</p>
  <div style="display: flex; gap: 10px; justify-content: center;">
    <button type="button" onclick="closeLogoutModal()" style="padding: 8px 18px; background: #e2e8f0; color: #475569; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 13px;">Hủy bỏ</button>
    <a id="btn-do-logout" href="../logout.php" style="padding: 8px 18px; background: #dc2626; color: white; border: none; border-radius: 6px; font-weight: 600; text-decoration: none; font-size: 13px;">Đăng xuất</a>
  </div>
</div>
<div class="modal-backdrop" id="modal-backdrop-logout" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.4); z-index: 10000;" onclick="closeLogoutModal()"></div>

<script>
  function toggleUserDropdown(e) {
    e.stopPropagation();
    const menu = document.getElementById('userDropdownMenu');
    if (menu) menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
  }
  document.addEventListener('click', function() {
    const menu = document.getElementById('userDropdownMenu');
    if (menu) menu.style.display = 'none';
  });
  function confirmLogout(e, logoutUrl) {
    if (e) e.preventDefault();
    const modal = document.getElementById('modal-logout-confirm');
    const backdrop = document.getElementById('modal-backdrop-logout');
    const btn = document.getElementById('btn-do-logout');
    if (btn && logoutUrl) btn.href = logoutUrl;
    if (modal) modal.style.display = 'block';
    if (backdrop) backdrop.style.display = 'block';
  }
  function closeLogoutModal() {
    const modal = document.getElementById('modal-logout-confirm');
    const backdrop = document.getElementById('modal-backdrop-logout');
    if (modal) modal.style.display = 'none';
    if (backdrop) backdrop.style.display = 'none';
  }
</script>
