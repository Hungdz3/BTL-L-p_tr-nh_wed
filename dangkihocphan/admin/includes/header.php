<?php
// admin/includes/header.php
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Dashboard — Trường Đại Học HNDA</title>
  <link rel="icon" type="image/svg+xml" href="../assets/images/favicon.svg">
  <link rel="stylesheet" href="../assets/css/style.css">
  <!-- Bổ sung CSS bổ trợ cho trang Admin -->
  <style>
    /* Admin specific helper classes */
    .dashboard-grid { margin-top: 20px; }
    .card-header-row { display: flex; justify-content: space-between; align-items: center; }
    .site-header { position: sticky; top: 0; z-index: 1000; }
    .navbar { position: sticky; top: 68px; z-index: 999; }
    
    /* Stats & Badge classes for admin dashboard */
    .dashboard-stats-row {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
      gap: 15px;
      margin: 20px 28px;
    }
    .stat-card {
      background: white;
      padding: 20px;
      border-radius: 8px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.05);
      border-left: 5px solid #1e4d8c;
      display: flex;
      flex-direction: column;
    }
    .stat-card.green { border-left-color: #2e7d32; }
    .stat-card.yellow { border-left-color: #f57c00; }
    .stat-card.red { border-left-color: #d32f2f; }
    .stat-card.cyan { border-left-color: #00838f; }
    
    .stat-card-title { font-size: 11px; color: #888; text-transform: uppercase; margin-bottom: 5px; font-weight: 600; }
    .stat-card-value { font-size: 24px; font-weight: bold; color: #1a1a2e; }
    .stat-card-sub { font-size: 11px; color: #38a169; margin-top: 5px; font-weight: 500; }
    .stat-card-sub.neutral { color: #888; }
    .stat-card-sub.negative { color: #e53e3e; }
    
    /* Tab controls */
    .tabs-row { display: flex; gap: 10px; margin: 20px 28px 10px 28px; }
    .btn-tab {
      padding: 10px 20px;
      background: #e2e8f0;
      color: #475569;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-weight: 600;
      font-size: 14px;
      transition: all 0.2s;
    }
    .btn-tab.active { background: #1a3a6b; color: white; }
    
    /* Quick Actions */
    .quick-actions-card {
      background: white;
      padding: 20px;
      border-radius: 12px;
      margin: 0 28px 24px 28px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }
    .quick-actions-card h3 { font-size: 15px; margin-bottom: 15px; color: #1a3a6b; }
    .quick-actions-buttons { display: flex; gap: 10px; flex-wrap: wrap; }
    .btn-quick {
      padding: 10px 18px;
      background: #f1f5f9;
      color: #334155;
      border: 1px solid #e2e8f0;
      border-radius: 6px;
      text-decoration: none;
      font-size: 13px;
      font-weight: bold;
      transition: all 0.2s;
    }
    .btn-quick:hover { background: #e2e8f0; color: #1a3a6b; border-color: #cbd5e1; }
    
    /* Dynamic Layout (Dashboard Content) */
    .admin-layout-two-cols {
      display: grid;
      grid-template-columns: 2fr 1fr;
      gap: 24px;
      padding: 0 28px 24px 28px;
      max-width: 1400px;
      margin: 0 auto;
    }
    @media (max-width: 900px) {
      .admin-layout-two-cols { grid-template-columns: 1fr; }
    }
    
    .panel-card {
      background: white;
      border-radius: 12px;
      padding: 24px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.05);
      border: 1px solid #eef2f6;
      margin-bottom: 20px;
    }
    .panel-card h3 {
      font-size: 15px;
      color: #1a3a6b;
      margin-bottom: 15px;
      font-weight: 700;
      border-left: 4px solid #f0c040;
      padding-left: 10px;
    }
    .panel-card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
    .panel-card-header h3 { margin-bottom: 0; }
    
    .view-all-link { text-decoration: none; color: #1e4d8c; font-size: 12px; font-weight: 600; }
    .view-all-link:hover { text-decoration: underline; }
    
    /* Table modifications */
    .badge-status { font-weight: 600; font-size: 12px; padding: 4px 8px; border-radius: 4px; display: inline-block; }
    .badge-status.active { color: #2e7d32; background: #e6f6ec; }
    .badge-status.pending { color: #f57c00; background: #fff3e0; }
    .badge-status.closed { color: #7f1d1d; background: #ffebee; }
    
    .action-icons { display: flex; gap: 8px; justify-content: flex-start; align-items: center; }
    .btn-action {
      background: none;
      border: none;
      cursor: pointer;
      font-size: 14px;
      padding: 4px;
      transition: transform 0.1s;
    }
    .btn-action:hover { transform: scale(1.15); }
    .btn-action.edit { color: #1e4d8c; }
    .btn-action.delete { color: #e53e3e; }
    
    /* Modal style */
    .custom-modal-admin {
      position: fixed;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      background: white;
      width: 90%;
      max-width: 550px;
      border-radius: 12px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.15);
      z-index: 10001;
      display: none;
      flex-direction: column;
    }
    .modal-header-admin {
      padding: 16px 20px;
      background: #f8fafc;
      border-bottom: 1px solid #e2e8f0;
      font-weight: 700;
      color: #1e4d8c;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .modal-close-admin {
      background: none;
      border: none;
      font-size: 18px;
      cursor: pointer;
      color: #64748b;
    }
    .modal-body-admin { padding: 20px; font-size: 14px; max-height: 70vh; overflow-y: auto; }
    .modal-footer-admin {
      padding: 12px 20px;
      background: #f8fafc;
      border-top: 1px solid #e2e8f0;
      display: flex;
      justify-content: flex-end;
      gap: 10px;
    }
    
    /* Form controls */
    .form-group-admin { display: flex; flex-direction: column; margin-bottom: 15px; }
    .form-group-admin label { font-size: 13px; font-weight: bold; margin-bottom: 5px; color: #334155; }
    .form-group-admin label span { color: #e53e3e; }
    .form-control-admin {
      padding: 8px 12px;
      border: 1px solid #cbd5e1;
      border-radius: 6px;
      outline: none;
      font-size: 14px;
    }
    .form-control-admin:focus { border-color: #1e4d8c; }
    .form-row-admin { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
    @media (max-width: 500px) {
      .form-row-admin { grid-template-columns: 1fr; gap: 0; }
    }
    
    .btn-admin-primary {
      padding: 8px 16px;
      background: #1E3A8A;
      color: white;
      border: none;
      border-radius: 6px;
      font-weight: 600;
      cursor: pointer;
    }
    .btn-admin-primary:hover { background: #163a70; }
    .btn-admin-secondary {
      padding: 8px 16px;
      background: #cbd5e1;
      color: #334155;
      border: none;
      border-radius: 6px;
      font-weight: 600;
      cursor: pointer;
    }
    .btn-admin-secondary:hover { background: #94a3b8; }
    
    /* Top filter bar */
    .admin-filter-bar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      background: white;
      padding: 15px 28px;
      margin: 20px 28px;
      border-radius: 8px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.05);
      flex-wrap: wrap;
      gap: 15px;
    }
    .filter-inputs-group { display: flex; gap: 10px; flex-wrap: wrap; }
    .filter-inputs-group input, .filter-inputs-group select {
      padding: 8px 12px;
      border: 1px solid #cbd5e1;
      border-radius: 6px;
      outline: none;
      font-size: 13px;
    }
    .btn-add-new {
      padding: 8px 16px;
      background: #0284CF;
      color: white;
      border: none;
      border-radius: 6px;
      font-weight: 600;
      cursor: pointer;
      font-size: 13px;
    }
    .btn-add-new:hover { background: #026cae; }
    
    /* Pagination */
    .pagination-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-top: 15px;
      font-size: 13px;
      color: #64748b;
    }
    .pagination-buttons { display: flex; gap: 5px; }
    .btn-page {
      padding: 5px 10px;
      border: 1px solid #cbd5e1;
      background: white;
      color: #334155;
      border-radius: 4px;
      cursor: pointer;
    }
    .btn-page.active { background: #1e4d8c; color: white; border-color: #1e4d8c; }
    .btn-page:hover:not(.active) { background: #f1f5f9; }
    
    /* Activity Feed styling */
    .activity-feed { list-style: none; padding: 0; }
    .activity-item {
      display: flex;
      justify-content: space-between;
      padding: 12px 0;
      border-bottom: 1px dashed #e2e8f0;
      font-size: 13px;
    }
    .activity-item:last-child { border-bottom: none; }
    .activity-text { font-weight: 500; color: #334155; }
    .activity-time { color: #64748b; font-size: 11px; white-space: nowrap; margin-left: 10px; }
    
    /* List items in main cards */
    .item-list-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 10px 0;
      border-bottom: 1px dashed #e2e8f0;
      font-size: 13px;
    }
    .item-list-row:last-child { border-bottom: none; }
    .item-title { font-weight: bold; color: #1e4d8c; }
    
    /* Drag & Drop File Upload Box */
    .file-upload-box {
      border: 2px dashed #cbd5e1;
      border-radius: 8px;
      padding: 20px;
      text-align: center;
      cursor: pointer;
      background: #f8fafc;
      color: #64748b;
      font-size: 13px;
      transition: all 0.2s;
    }
    .file-upload-box:hover { border-color: #1e4d8c; background: #f1f5f9; color: #1e4d8c; }
    .file-upload-box svg { font-size: 24px; margin-bottom: 8px; fill: currentColor; }
    
    /* Schedule ca hoc selectors */
    .day-checkboxes { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 5px; }
    .btn-day-chk {
      padding: 6px 12px;
      border: 1px solid #cbd5e1;
      background: white;
      border-radius: 4px;
      cursor: pointer;
      font-size: 12px;
      font-weight: 600;
      color: #475569;
    }
    .btn-day-chk.selected { background: #1e4d8c; color: white; border-color: #1e4d8c; }
  </style>
  <script src="../assets/js/global.js"></script>
</head>
<body>

<!-- Header -->
<header class="site-header" style="background: #ffffff; padding: 10px 28px; border-bottom: 2px solid #3b82f6; display: flex; justify-content: space-between; align-items: center;">
  <div class="header-left">
    <a href="index.php" style="text-decoration: none; color: inherit; display: flex; align-items: center; gap: 12px;" title="Về trang chủ Quản trị">
      <div style="background: #1E3A8A; width: 42px; height: 42px; border-radius: 8px; color: #ffffff; font-weight: 800; font-size: 22px; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 6px rgba(30, 58, 138, 0.2);">A</div>
      <div>
        <strong style="color: #1E3A8A; font-weight: 800; font-size: 15px; display: block; line-height: 1.2;">TRƯỜNG ĐẠI HỌC HNDA</strong>
        <span style="color: #B91C1C; font-weight: 700; font-size: 11px; display: block; text-transform: uppercase; margin-top: 2px;">HỆ THỐNG QUẢN TRỊ TRỰC TUYẾN</span>
      </div>
    </a>
  </div>
  <div class="header-right" style="display: flex; align-items: center; gap: 14px; position: relative;">
    <div class="user-text" style="text-align: right;">
      <span class="user-name" style="color: #0f172a; font-weight: 700; font-size: 14px; display: block;">Administrator</span>
      <small class="user-info" style="color: #64748b; font-size: 11px; display: block; margin-top: 2px;">Quản trị hệ thống</small>
    </div>
    <!-- Avatar Cụm Clickable -->
    <div class="avatar" onclick="toggleUserDropdown(event)" style="background: #cbd5e1; width: 42px; height: 42px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; color: #334155; font-size: 16px; cursor: pointer; user-select: none; transition: transform 0.15s;" title="Bấm vào đây để mở menu cá nhân">A</div>

    <!-- User Dropdown Menu -->
    <div class="user-dropdown-menu" id="userDropdownMenu" style="display: none; position: absolute; right: 0; top: 52px; background: white; border: 1px solid #e2e8f0; border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.15); min-width: 200px; z-index: 10002; padding: 6px 0;">
      <div style="padding: 10px 16px; border-bottom: 1px solid #f1f5f9; font-size: 13px;">
        <strong style="color: #1e293b; display: block;">Administrator</strong>
        <span style="color: #64748b; font-size: 11px;">Quản trị hệ thống</span>
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
?>
<nav class="navbar">
  <a href="index.php" class="<?= $current_page == 'index.php' ? 'active' : '' ?>">Trang Chủ</a>
  <a href="thong-bao.php" class="<?= $current_page == 'thong-bao.php' ? 'active' : '' ?>">Thông Báo</a>
  <a href="sinh-vien.php" class="<?= $current_page == 'sinh-vien.php' ? 'active' : '' ?>">Sinh Viên</a>
  <a href="giang-vien.php" class="<?= $current_page == 'giang-vien.php' ? 'active' : '' ?>">Giảng Viên</a>
  <a href="hoc-phan.php" class="<?= $current_page == 'hoc-phan.php' ? 'active' : '' ?>">Học Phần</a>
  <a href="dang-ky-hp.php" class="<?= $current_page == 'dang-ky-hp.php' ? 'active' : '' ?>">Đăng Ký Học Phần</a>
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

<div class="toast-container" id="toast-container"></div>
