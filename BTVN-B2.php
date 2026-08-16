<?php
session_start();
if (!isset($_SESSION['dsDangKy'])) {
    $_SESSION['dsDangKy'] = []; 
}


foreach ($_SESSION['dsDangKy'] as $k => $p) {
    if (!isset($p['id'])) {
        $_SESSION['dsDangKy'][$k]['id'] = uniqid();
    }
}

$GIOI_HAN_TIN_CHI = 24;
$thongBao = "";


function tinhTongTinChi($ds, $maSV, $hocKy) {
    $tong = 0;
    foreach ($ds as $p) {
        if ($p['maSV'] === $maSV && $p['hocKy'] === $hocKy) {
            $tong += $p['soTinChi'];
        }
    }
    return $tong;
}


function daDangKy($ds, $maSV, $maHP, $hocKy) {
    foreach ($ds as $p) {
        if ($p['maSV'] === $maSV && $p['maHP'] === $maHP && $p['hocKy'] === $hocKy) {
            return true;
        }
    }
    return false;
}


function huyDangKy(&$ds, $id) {
    foreach ($ds as $index => $p) {
        if (isset($p['id']) && $p['id'] === $id) {
            array_splice($ds, $index, 1); // loại phần tử khỏi mảng
            return true;
        }
    }
    return false;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'huy') {
    // Hủy phiếu đăng ký
    $id = $_POST['id'];
    if (huyDangKy($_SESSION['dsDangKy'], $id)) {
        $thongBao = "Đã hủy phiếu đăng ký.";
    } else {
        $thongBao = "Không tìm thấy phiếu để hủy.";
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Đăng ký mới
    $maSV     = strtoupper(trim($_POST['maSV']));
    $maHP     = strtoupper(trim($_POST['maHP']));
    $hocKy    = trim($_POST['hocKy']);
    $soTinChi = (int) $_POST['soTinChi'];

    if ($maSV === "" || $maHP === "" || $hocKy === "" || $soTinChi <= 0) {
        $thongBao = "Vui lòng nhập đầy đủ và hợp lệ 4 trường.";
    } elseif (daDangKy($_SESSION['dsDangKy'], $maSV, $maHP, $hocKy)) {
        $thongBao = "$maSV đã đăng ký học phần $maHP trong $hocKy rồi.";
    } else {
        $_SESSION['dsDangKy'][] = [
            'id' => uniqid(),
            'maSV' => $maSV, 'maHP' => $maHP,
            'hocKy' => $hocKy, 'soTinChi' => $soTinChi
        ];
        $tong = tinhTongTinChi($_SESSION['dsDangKy'], $maSV, $hocKy);
        $thongBao = ($tong > $GIOI_HAN_TIN_CHI)
            ? "Đã ghi nhận, nhưng $maSV vượt giới hạn tín chỉ ($tong/$GIOI_HAN_TIN_CHI)."
            : "Đã ghi nhận phiếu đăng ký cho $maSV.";
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Đăng ký học phần</title>
</head>
<body>

<h1>Đăng ký học phần</h1>

<?php if ($thongBao): ?>
  <p><?= htmlspecialchars($thongBao) ?></p>
<?php endif; ?>

<form method="post">
  Mã sinh viên: <input name="maSV" required><br>
  Mã học phần: <input name="maHP" required><br>
  Học kỳ: <input name="hocKy" required><br>
  Số tín chỉ: <input name="soTinChi" type="number" min="1" max="10" required><br>
  <button type="submit">Đăng ký</button>
</form>

<h2>Danh sách đăng ký</h2>

<table border="1" cellpadding="6">
  <tr>
    <th>#</th><th>Sinh viên</th><th>Học phần</th><th>Học kỳ</th>
    <th>Tín chỉ</th><th>Trạng thái</th><th>Hủy</th>
  </tr>
  <?php
  $stt = 1;
  foreach ($_SESSION['dsDangKy'] as $p) {
      $tong = tinhTongTinChi($_SESSION['dsDangKy'], $p['maSV'], $p['hocKy']);
      $trangThai = ($tong > $GIOI_HAN_TIN_CHI) ? "Vượt giới hạn" : "Hợp lệ";
      echo "<tr>";
      echo "<td>$stt</td>";
      echo "<td>{$p['maSV']}</td>";
      echo "<td>{$p['maHP']}</td>";
      echo "<td>{$p['hocKy']}</td>";
      echo "<td>{$p['soTinChi']}</td>";
      echo "<td>$trangThai</td>";
      echo "<td>
              <form method='post' style='display:inline'>
                <input type='hidden' name='action' value='huy'>
                <input type='hidden' name='id' value='" . htmlspecialchars($p['id'] ?? '') . "'>
                <button type='submit'>Hủy</button>
              </form>
            </td>";
      echo "</tr>";
      $stt++;
  }
  if (empty($_SESSION['dsDangKy'])) {
      echo "<tr><td colspan='7'>Chưa có phiếu đăng ký nào.</td></tr>";
  }
  ?>
</table>

</body>
</html>