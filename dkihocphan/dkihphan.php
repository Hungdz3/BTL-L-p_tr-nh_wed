<?php
session_start();
$sinh_vien = [
    'ma_sv'    => 'SV001',
    'ho_ten'   => 'Nguyễn Văn A',
    'ten_lop'  => 'Lớp Công nghệ thông tin K20',
    'ten_khoa' => 'Khoa Công nghệ thông tin',
];
$danh_sach_lhp = [
    [
        'ma_lhp'      => 'LHP001',
        'ten_mon'     => 'Lập trình Web nâng cao',
        'so_tin_chi'  => 3,
        'giang_vien'  => 'ThS. Trần Thị Giảng Viên',
        'lich_hoc'    => 'T2 (Tiết 1)',
        'si_so_hien'  => 0,
        'si_so_max'   => 50,
    ],
    [
        'ma_lhp'      => 'LHP002',
        'ten_mon'     => 'Lập trình Web nâng cao',
        'so_tin_chi'  => 3,
        'giang_vien'  => 'ThS. Trần Thị Giảng Viên',
        'lich_hoc'    => 'T3 (Tiết 2)',
        'si_so_hien'  => 0,
        'si_so_max'   => 40,
    ],
    [
        'ma_lhp'      => 'LHP003',
        'ten_mon'     => 'Cấu trúc dữ liệu và Giải thuật',
        'so_tin_chi'  => 4,
        'giang_vien'  => 'ThS. Trần Thị Giảng Viên',
        'lich_hoc'    => 'T4 (Tiết 3)',
        'si_so_hien'  => 0,
        'si_so_max'   => 60,
    ],
    [
        'ma_lhp'      => 'LHP004',
        'ten_mon'     => 'Cơ sở dữ liệu nâng cao',
        'so_tin_chi'  => 3,
        'giang_vien'  => 'ThS. Trần Thị Giảng Viên',
        'lich_hoc'    => 'T5 (Tiết 1)',
        'si_so_hien'  => 0,
        'si_so_max'   => 45,
    ],
    [
        'ma_lhp'      => 'LHP005',
        'ten_mon'     => 'Kinh tế vi mô',
        'so_tin_chi'  => 3,
        'giang_vien'  => 'ThS. Trần Thị Giảng Viên',
        'lich_hoc'    => 'T6 (Tiết 2)',
        'si_so_hien'  => 0,
        'si_so_max'   => 3,
    ],
    [
        'ma_lhp'      => 'LHP006',
        'ten_mon'     => 'Mạng máy tính',
        'so_tin_chi'  => 3,
        'giang_vien'  => 'ThS. Nguyễn Văn Giảng Viên',
        'lich_hoc'    => 'T2 (Tiết 4)',
        'si_so_hien'  => 15,
        'si_so_max'   => 35,
    ],
    [
        'ma_lhp'      => 'LHP007',
        'ten_mon'     => 'Trí tuệ nhân tạo',
        'so_tin_chi'  => 4,
        'giang_vien'  => 'PGS.TS. Lê Văn Chuyên Gia',
        'lich_hoc'    => 'T3 (Tiết 5)',
        'si_so_hien'  => 28,
        'si_so_max'   => 40,
    ],
    [
        'ma_lhp'      => 'LHP008',
        'ten_mon'     => 'Lập trình di động',
        'so_tin_chi'  => 3,
        'giang_vien'  => 'ThS. Phạm Thị Mobile',
        'lich_hoc'    => 'T4 (Tiết 1)',
        'si_so_hien'  => 30,
        'si_so_max'   => 30,
    ],
    [
        'ma_lhp'      => 'LHP009',
        'ten_mon'     => 'An toàn thông tin',
        'so_tin_chi'  => 3,
        'giang_vien'  => 'TS. Hoàng Văn Bảo Mật',
        'lich_hoc'    => 'T5 (Tiết 3)',
        'si_so_hien'  => 10,
        'si_so_max'   => 50,
    ],
    [
        'ma_lhp'      => 'LHP010',
        'ten_mon'     => 'Phân tích và thiết kế hệ thống',
        'so_tin_chi'  => 3,
        'giang_vien'  => 'ThS. Trần Văn Phân Tích',
        'lich_hoc'    => 'T6 (Tiết 4)',
        'si_so_hien'  => 22,
        'si_so_max'   => 45,
    ],
];
if (!isset($_SESSION['da_dk'])) {
    $_SESSION['da_dk'] = [];
}
$msg = '';
$msg_type = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action  = $_POST['action']  ?? '';
    $ma_lhp  = trim($_POST['ma_lhp'] ?? '');

    $lhp_info = null;
    foreach ($danh_sach_lhp as $lhp) {
        if ($lhp['ma_lhp'] === $ma_lhp) {
            $lhp_info = $lhp;
            break;
        }
    }

    if ($action === 'dang_ky' && $lhp_info) {
        $da_dk_ids = array_column($_SESSION['da_dk'], 'ma_lhp');
        if (in_array($ma_lhp, $da_dk_ids)) {
            $msg = 'Bạn đã đăng ký lớp học phần này rồi.';
            $msg_type = 'error';
        } elseif ($lhp_info['si_so_hien'] >= $lhp_info['si_so_max']) {
            $msg = 'Lớp học phần đã đầy sĩ số.';
            $msg_type = 'error';
        } else {
            $ten_mon_da_dk = array_column($_SESSION['da_dk'], 'ten_mon');
            if (in_array($lhp_info['ten_mon'], $ten_mon_da_dk)) {
                $msg = 'Bạn đã đăng ký môn học "' . $lhp_info['ten_mon'] . '" ở lớp khác rồi.';
                $msg_type = 'error';
            } else {
                $_SESSION['da_dk'][] = [
                    'ma_lhp'     => $lhp_info['ma_lhp'],
                    'ten_mon'    => $lhp_info['ten_mon'],
                    'so_tin_chi' => $lhp_info['so_tin_chi'],
                    'lich_hoc'   => $lhp_info['lich_hoc'],
                ];
                $msg = 'Đăng ký học phần "' . $lhp_info['ten_mon'] . '" thành công!';
                $msg_type = 'success';
            }
        }
    } elseif ($action === 'huy' && $ma_lhp) {
        $_SESSION['da_dk'] = array_values(array_filter(
            $_SESSION['da_dk'],
            fn($item) => $item['ma_lhp'] !== $ma_lhp
        ));
        $msg = 'Đã huỷ đăng ký lớp học phần ' . $ma_lhp . '.';
        $msg_type = 'success';
    }
}

$ds_da_dk   = $_SESSION['da_dk'];
$tong_tc    = array_sum(array_column($ds_da_dk, 'so_tin_chi'));
$da_dk_ids  = array_column($ds_da_dk, 'ma_lhp');

$keyword = trim($_GET['q'] ?? '');
$hien_thi = $danh_sach_lhp;
if ($keyword !== '') {
    $hien_thi = array_filter($hien_thi, fn($lhp) =>
        stripos($lhp['ten_mon'], $keyword) !== false ||
        stripos($lhp['ma_lhp'],  $keyword) !== false
    );
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Đăng Ký Học Phần — HNDA</title>
  <style>
    /* ── Reset & Base ──────────────────────────────────── */
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f4f6f9; color: #1a1a2e; }

    /* ── Site Header ───────────────────────────────────── */
    .site-header {
      display: flex; justify-content: space-between; align-items: center;
      background: #1a3a6b; color: white; padding: 12px 28px;
    }
    .header-left { display: flex; align-items: center; gap: 12px; }
    .logo-placeholder { font-size: 32px; background: rgba(255,255,255,0.1); padding: 5px; border-radius: 8px; }
    .header-left strong { font-size: 16px; display: block; }
    .header-left span   { font-size: 12px; opacity: 0.8; }
    .header-right { display: flex; align-items: center; gap: 12px; }
    .user-text { display: flex; flex-direction: column; text-align: right; }
    .user-name  { font-weight: bold; font-size: 15px; }
    .user-info  { font-size: 12px; color: #c8d8f0; margin-top: 2px; }
    .avatar {
      width: 40px; height: 40px; border-radius: 50%;
      background: #4a90d9; display: flex; align-items: center;
      justify-content: center; font-weight: bold; font-size: 18px;
    }

    /* ── Navbar ────────────────────────────────────────── */
    .navbar { background: #1e4d8c; display: flex; gap: 4px; padding: 0 20px; }
    .navbar a {
      padding: 14px 20px; color: #c8d8f0; text-decoration: none;
      font-size: 14px; font-weight: 500; transition: all 0.2s;
    }
    .navbar a.active, .navbar a:hover {
      color: white; border-bottom: 3px solid #f0c040;
      background: rgba(255,255,255,0.05);
    }

    /* ── Banner chào ───────────────────────────────────── */
    .banner { background: #1e4d8c; color: white; padding: 28px 32px; }
    .banner h2 { font-size: 24px; margin-bottom: 6px; }
    .banner p  { font-size: 14px; opacity: 0.85; }

    /* ── Thanh thông tin SV ────────────────────────────── */
    .sv-info {
      display: flex; gap: 32px; flex-wrap: wrap;
      background: white; padding: 20px 32px;
      border-bottom: 1px solid #e0e0e0;
    }
    .sv-info div { display: flex; flex-direction: column; }
    .sv-info small { font-size: 11px; color: #888; text-transform: uppercase; margin-bottom: 4px; }
    .sv-info strong { font-size: 15px; }
    .tc-counter { margin-left: auto; }
    .tc-counter strong { color: #1e4d8c; font-size: 20px; }

    /* ── Layout 2 cột ──────────────────────────────────── */
    .main-layout {
      display: flex; gap: 20px;
      padding: 24px 28px; max-width: 1400px; margin: 0 auto;
    }
    .col-main   { flex: 1; min-width: 0; }
    .col-sidebar { width: 320px; flex-shrink: 0; }

    /* ── Thanh tìm kiếm ────────────────────────────────── */
    .search-form { display: flex; gap: 10px; margin-bottom: 20px; }
    .search-form input {
      flex: 1; padding: 10px 14px; border: 1px solid #ddd;
      border-radius: 6px; outline: none; font-size: 14px;
    }
    .search-form input:focus { border-color: #1e4d8c; }
    .search-form button {
      padding: 10px 20px; background: #1e4d8c; color: white;
      border: none; border-radius: 6px; cursor: pointer; font-weight: bold;
      font-size: 14px;
    }
    .search-form button:hover { background: #163a70; }

    /* ── Bảng học phần ─────────────────────────────────── */
    table {
      width: 100%; border-collapse: collapse; background: white;
      border-radius: 10px; overflow: hidden;
      box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    }
    th {
      background: #f0f4fa; padding: 12px 14px; text-align: left;
      font-size: 12px; color: #555; font-weight: 600; text-transform: uppercase;
    }
    td { padding: 14px; font-size: 14px; border-bottom: 1px solid #f0f0f0; }
    tr:last-child td { border-bottom: none; }
    tr:hover td { background-color: #fafbfd; }
    .ma-hp { color: #1e4d8c; font-weight: 600; font-size: 13px; }

    /* ── Nút & Badge ───────────────────────────────────── */
    .btn-dangky {
      padding: 7px 18px; background: #1e4d8c; color: white;
      border: none; border-radius: 6px; cursor: pointer;
      font-size: 13px; font-weight: 500; transition: all 0.2s;
    }
    .btn-dangky:hover { background: #163a70; }
    .badge-day { color: #e53e3e; font-weight: 600; font-size: 13px; background: #fff5f5; padding: 4px 8px; border-radius: 4px; }
    .badge-dk  { color: #38a169; font-weight: 600; font-size: 13px; background: #f0fff4; padding: 4px 8px; border-radius: 4px; }

    /* ── Sidebar ───────────────────────────────────────── */
    .col-sidebar {
      background: white; border-radius: 10px;
      padding: 20px; height: fit-content;
      box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    }
    .col-sidebar h4 {
      font-size: 16px; margin-bottom: 4px;
      border-bottom: 2px solid #1e4d8c; padding-bottom: 8px;
    }
    .sidebar-hint { font-size: 12px; color: #888; margin-bottom: 14px; margin-top: 4px; }
    .sidebar-item {
      display: flex; justify-content: space-between; align-items: center;
      padding: 12px 0; border-bottom: 1px solid #f0f0f0;
    }
    .sidebar-item-info { display: flex; flex-direction: column; gap: 4px; }
    .sidebar-item .so-tc { font-size: 12px; color: #888; }
    .btn-xoa { background: none; border: none; cursor: pointer; color: #e53e3e; font-size: 16px; padding: 4px; }
    .btn-xoa:hover { transform: scale(1.1); }
    .sidebar-footer { margin-top: 16px; padding-top: 12px; border-top: 2px dashed #e8e8e8; }
    .sidebar-footer p { font-size: 14px; margin-bottom: 8px; display: flex; justify-content: space-between; }
    .highlight { color: #1e4d8c; font-size: 18px; font-weight: bold; }
    .btn-xac-nhan {
      width: 100%; margin-top: 14px; padding: 12px;
      background: #1e4d8c; color: white; border: none;
      border-radius: 8px; font-size: 15px; cursor: pointer; font-weight: bold;
    }
    .btn-xac-nhan:hover { background: #163a70; }

    /* ── Toast Thông báo ───────────────────────────────── */
    .toast-container {
      position: fixed; top: 20px; right: 20px; z-index: 9999;
      display: flex; flex-direction: column; gap: 10px; width: 340px;
      pointer-events: none;
    }
    .toast {
      background: white; color: #334155; padding: 14px 18px;
      border-radius: 8px; box-shadow: 0 5px 15px rgba(0,0,0,0.12);
      display: flex; align-items: flex-start; gap: 12px;
      font-size: 13px; font-weight: 500; line-height: 1.5;
      pointer-events: auto; border-left: 4px solid #cbd5e1;
      animation: slideIn 0.3s ease, fadeOut 0.4s ease 3.6s forwards;
    }
    .toast.success { border-left-color: #22c55e; background: #f0fff4; color: #14532d; }
    .toast.error   { border-left-color: #ef4444; background: #fff5f5; color: #7f1d1d; }
    @keyframes slideIn {
      from { transform: translateX(100%); opacity: 0; }
      to   { transform: translateX(0);    opacity: 1; }
    }
    @keyframes fadeOut {
      from { opacity: 1; }
      to   { opacity: 0; transform: translateY(-8px); }
    }

    /* ── Modal Xác nhận ────────────────────────────────── */
    .modal-backdrop {
      position: fixed; inset: 0;
      background: rgba(15,23,42,0.55); backdrop-filter: blur(4px);
      z-index: 10000; display: flex; align-items: center; justify-content: center;
    }
    .custom-modal {
      background: white; border-radius: 12px; width: 90%; max-width: 420px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.15); overflow: hidden;
    }
    .modal-header { padding: 16px 20px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; font-weight: 700; color: #1e4d8c; }
    .modal-body   { padding: 20px; font-size: 14px; color: #334155; line-height: 1.5; }
    .modal-footer { padding: 12px 20px; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end; gap: 10px; }
    .btn-modal-cancel  { padding: 8px 16px; border-radius: 6px; border: none; background: #e2e8f0; color: #475569; font-weight: 600; cursor: pointer; }
    .btn-modal-cancel:hover { background: #cbd5e1; }
    .btn-modal-confirm { padding: 8px 16px; border-radius: 6px; border: none; background: #e53e3e; color: white; font-weight: 600; cursor: pointer; }
    .btn-modal-confirm:hover { background: #c53030; }

    /* ── Footer ────────────────────────────────────────── */
    footer {
      text-align: center; padding: 20px; color: #888; font-size: 13px;
      margin-top: 40px; border-top: 1px solid #e0e0e0; background: white;
    }

    /* ── Responsive ────────────────────────────────────── */
    @media (max-width: 900px) {
      .main-layout { flex-direction: column; }
      .col-sidebar { width: 100%; }
      .sv-info { gap: 16px; }
      .tc-counter { margin-left: 0; }
    }
  </style>
</head>
<body>

<!-- ── Toast container ──────────────────────────────────────── -->
<div class="toast-container" id="toast-container"></div>

<!-- ── Site Header ──────────────────────────────────────────── -->
<header class="site-header">
  <div class="header-left">
    <div class="logo-placeholder">🏫</div>
    <div>
      <strong>TRƯỜNG ĐẠI HỌC HNDA</strong>
      <span>Cổng thông tin sinh viên</span>
    </div>
  </div>
  <div class="header-right">
    <div class="user-text">
      <span class="user-name"><?= htmlspecialchars($sinh_vien['ho_ten']) ?></span>
      <small class="user-info">MSV: <?= htmlspecialchars($sinh_vien['ma_sv']) ?> • Lớp: <?= htmlspecialchars($sinh_vien['ten_lop']) ?></small>
    </div>
    <div class="avatar"><?= mb_strtoupper(mb_substr($sinh_vien['ho_ten'], 0, 1, 'UTF-8'), 'UTF-8') ?></div>
  </div>
</header>

<!-- ── Navbar ────────────────────────────────────────────────── -->
<nav class="navbar">
  <a href="#">Trang Chủ</a>
  <a href="#">Thông Báo</a>
  <a href="#" class="active">Đăng Ký Học Phần</a>
  <a href="#">Lịch Học</a>
  <a href="#">Sinh Viên</a>
</nav>

<!-- ── Banner chào ───────────────────────────────────────────── -->
<section class="banner">
  <h2>Xin chào, <?= htmlspecialchars($sinh_vien['ho_ten']) ?></h2>
  <p>Cổng đăng ký học phần chính thức học kỳ mới năm học 2026-2027</p>
</section>

<!-- ── Thông tin sinh viên ───────────────────────────────────── -->
<section class="sv-info">
  <div>
    <small>MÃ SINH VIÊN</small>
    <strong><?= htmlspecialchars($sinh_vien['ma_sv']) ?></strong>
  </div>
  <div>
    <small>LỚP CHUYÊN NGÀNH</small>
    <strong><?= htmlspecialchars($sinh_vien['ten_lop']) ?></strong>
  </div>
  <div>
    <small>KHOA CHỦ QUẢN</small>
    <strong><?= htmlspecialchars($sinh_vien['ten_khoa']) ?></strong>
  </div>
  <div>
    <small>HỌC KỲ ĐĂNG KÝ</small>
    <strong>Học Kỳ 1 (2026-2027)</strong>
  </div>
  <div class="tc-counter">
    <small>SỐ TÍN CHỈ ĐÃ ĐĂNG KÝ</small>
    <strong id="so-tc-display"><?= $tong_tc ?>/24 tín chỉ</strong>
  </div>
</section>

<!-- ── Layout chính ──────────────────────────────────────────── -->
<div class="main-layout">

  <!-- Cột trái: Bảng học phần -->
  <div class="col-main">

    <!-- Tìm kiếm -->
    <form class="search-form" method="GET">
      <input
        type="text"
        name="q"
        value="<?= htmlspecialchars($keyword) ?>"
        placeholder="🔍 Nhập tên môn học hoặc mã lớp học phần để tìm kiếm..."
      >
      <button type="submit">Tìm kiếm</button>
    </form>

    <h3 style="margin-bottom: 14px; font-size: 16px; color: #1a3a6b;">Học phần mở đăng ký học kỳ này</h3>

    <table>
      <thead>
        <tr>
          <th>Mã lớp</th>
          <th>Tên môn học</th>
          <th style="text-align: center;">Tín chỉ</th>
          <th>Giảng viên</th>
          <th>Lịch học</th>
          <th>Sĩ số</th>
          <th>Hành động</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($hien_thi as $lhp):
          $day   = $lhp['si_so_hien'] >= $lhp['si_so_max'];
          $da_dk = in_array($lhp['ma_lhp'], $da_dk_ids);
        ?>
        <tr>
          <td class="ma-hp"><?= htmlspecialchars($lhp['ma_lhp']) ?></td>
          <td><strong><?= htmlspecialchars($lhp['ten_mon']) ?></strong></td>
          <td style="text-align: center;"><?= $lhp['so_tin_chi'] ?></td>
          <td><?= htmlspecialchars($lhp['giang_vien']) ?></td>
          <td><?= htmlspecialchars($lhp['lich_hoc']) ?></td>
          <td><?= $lhp['si_so_hien'] ?>/<?= $lhp['si_so_max'] ?></td>
          <td>
            <?php if ($da_dk): ?>
              <span class="badge-dk">Đã đăng ký</span>
            <?php elseif ($day): ?>
              <span class="badge-day">Đã đầy</span>
            <?php else: ?>
              <form method="POST" style="display:inline;">
                <input type="hidden" name="action"  value="dang_ky">
                <input type="hidden" name="ma_lhp"  value="<?= htmlspecialchars($lhp['ma_lhp']) ?>">
                <button type="submit" class="btn-dangky">Đăng ký</button>
              </form>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($hien_thi)): ?>
        <tr>
          <td colspan="7" style="text-align: center; color: #888; padding: 30px;">
            Không tìm thấy học phần nào phù hợp.
          </td>
        </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- Cột phải: Giỏ đã đăng ký -->
  <aside class="col-sidebar">
    <h4>Học phần đã đăng ký</h4>
    <p class="sidebar-hint">Danh sách các môn học đăng ký thành công trong học kỳ này</p>

    <?php if (empty($ds_da_dk)): ?>
      <p style="text-align: center; color: #888; padding: 20px 0;">Chưa đăng ký học phần nào.</p>
    <?php else: ?>
      <?php foreach ($ds_da_dk as $item): ?>
        <div class="sidebar-item">
          <div class="sidebar-item-info">
            <strong class="ma-hp"><?= htmlspecialchars($item['ma_lhp']) ?></strong>
            <span style="font-size: 13px; font-weight: 500;"><?= htmlspecialchars($item['ten_mon']) ?></span>
            <span class="so-tc"><?= $item['so_tin_chi'] ?> tín chỉ &nbsp;|&nbsp; <?= htmlspecialchars($item['lich_hoc']) ?></span>
          </div>
          <form method="POST" style="display:inline;">
            <input type="hidden" name="action"  value="huy">
            <input type="hidden" name="ma_lhp"  value="<?= htmlspecialchars($item['ma_lhp']) ?>">
            <button type="submit" class="btn-xoa" title="Huỷ học phần này"
              onclick="return confirm('Bạn có chắc chắn muốn hủy đăng ký lớp <?= htmlspecialchars($item['ma_lhp']) ?>?')">
              🗑
            </button>
          </form>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>

    <div class="sidebar-footer">
      <p><span>Số môn:</span> <strong><?= count($ds_da_dk) ?> môn</strong></p>
      <p><span>Tổng tín chỉ:</span> <strong class="highlight"><?= $tong_tc ?> TC</strong></p>
      <form method="POST">
        <input type="hidden" name="action" value="xac_nhan">
        <button type="button" class="btn-xac-nhan"
          onclick="
            if (<?= count($ds_da_dk) ?> === 0) {
              showToast('Vui lòng chọn ít nhất một lớp học phần để đăng ký!', 'error');
            } else {
              showToast('Hệ thống đã lưu danh sách đăng ký học phần của bạn thành công!', 'success');
            }
          ">
          Xác nhận hoàn tất
        </button>
      </form>
    </div>
  </aside>

</div>

<!-- ── JavaScript: Toast ─────────────────────────────────────── -->
<script>
function showToast(message, type = 'info') {
  const container = document.getElementById('toast-container');
  const toast = document.createElement('div');
  toast.className = `toast ${type}`;
  const icons = { success: '✅', error: '❌', info: 'ℹ️' };
  toast.innerHTML = `<span style="font-size:16px;">${icons[type] || 'ℹ️'}</span><div>${message}</div>`;
  container.appendChild(toast);
  setTimeout(() => toast.remove(), 4000);
}

<?php if ($msg): ?>
// Hiển thị thông báo kết quả từ server
document.addEventListener('DOMContentLoaded', () => {
  showToast(<?= json_encode($msg, JSON_UNESCAPED_UNICODE) ?>, <?= json_encode($msg_type) ?>);
});
<?php endif; ?>
</script>

</body>
</html>