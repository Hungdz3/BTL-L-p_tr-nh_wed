<?php
session_start();


if (!isset($_SESSION['ds_lich'])) {
    $_SESSION['ds_lich'] = [];
}


function phanLoaiBuoi($tiet) {
    if ($tiet <= 6) return "Sáng";
    if ($tiet <= 9) return "Chiều";
    return "Tối";
}



function kiemTraTrungLich($thu, $tiet, $dsLich, $boQuaIndex = null) {
    foreach ($dsLich as $i => $lich) {
        if ($i === $boQuaIndex) continue; // bỏ qua dòng đang sửa
        if ($lich['thu'] == $thu && $lich['tiet'] == $tiet) {
            return $lich; 
        }
    }
    return false; 
}


if (isset($_POST['luu'])) {
    $tenMon = trim($_POST['ten_mon']);
    $thu    = trim($_POST['thu']);
    $tiet   = (int)$_POST['tiet'];
    $phong  = trim($_POST['phong']);
    $index  = $_POST['index']; 


    if ($tenMon != "" && $thu != "" && $tiet > 0 && $phong != "") {

        $boQuaIndex = ($index !== '') ? (int)$index : null;

        $lichTrung = kiemTraTrungLich($thu, $tiet, $_SESSION['ds_lich'], $boQuaIndex);

        if ($lichTrung !== false) {
          
            $_SESSION['loi'] = "Trùng lịch với môn \"{$lichTrung['ten_mon']}\" ($thu, tiết {$lichTrung['tiet']}).";
        } else {
            $lich = [
                'ten_mon' => $tenMon,
                'thu'     => $thu,
                'tiet'    => $tiet,
                'phong'   => $phong,
                'buoi'    => phanLoaiBuoi($tiet) 
            ];

            if ($index === '' || !isset($_SESSION['ds_lich'][$index])) {
                $_SESSION['ds_lich'][] = $lich; 
            } else {
                $_SESSION['ds_lich'][$index] = $lich; 
            }
        }
    }
   
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}


if (isset($_GET['xoa'])) {
    $idx = (int)$_GET['xoa'];
    if (isset($_SESSION['ds_lich'][$idx])) {
        unset($_SESSION['ds_lich'][$idx]);
        $_SESSION['ds_lich'] = array_values($_SESSION['ds_lich']); // đánh lại số thứ tự mảng
    }
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}


$dangSua = null;
if (isset($_GET['sua']) && isset($_SESSION['ds_lich'][(int)$_GET['sua']])) {
    $dangSua = (int)$_GET['sua'];
    $lichDangSua = $_SESSION['ds_lich'][$dangSua];
}

$dsLich = $_SESSION['ds_lich'];


$thongBaoLoi = $_SESSION['loi'] ?? '';
unset($_SESSION['loi']);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Lịch học</title>
</head>
<body>

<h2><?= $dangSua !== null ? "Sửa lịch học" : "Thêm lịch học" ?></h2>

<?php if ($thongBaoLoi): ?>
    <p><?= htmlspecialchars($thongBaoLoi) ?></p>
<?php endif; ?>

<form method="POST">
    <input type="hidden" name="index" value="<?= $dangSua !== null ? $dangSua : '' ?>">

    <label>Tên môn học</label><br>
    <input type="text" name="ten_mon" value="<?= $dangSua !== null ? htmlspecialchars($lichDangSua['ten_mon']) : '' ?>" required><br>

    <label>Thứ</label><br>
    <select name="thu" required>
        <?php $dsThu = ['Thứ 2', 'Thứ 3', 'Thứ 4', 'Thứ 5', 'Thứ 6']; ?>
        <?php foreach ($dsThu as $t): ?>
            <option value="<?= $t ?>" <?= ($dangSua !== null && $lichDangSua['thu'] == $t) ? 'selected' : '' ?>><?= $t ?></option>
        <?php endforeach; ?>
    </select><br>

    <label>Tiết bắt đầu</label><br>
    <input type="number" name="tiet" min="1" max="12" value="<?= $dangSua !== null ? $lichDangSua['tiet'] : '' ?>" required><br>

    <label>Phòng học</label><br>
    <input type="text" name="phong" value="<?= $dangSua !== null ? htmlspecialchars($lichDangSua['phong']) : '' ?>" required><br>

    <button type="submit" name="luu"><?= $dangSua !== null ? "Cập nhật" : "Thêm" ?></button>
    <?php if ($dangSua !== null): ?>
        <a href="<?= $_SERVER['PHP_SELF'] ?>">Hủy</a>
    <?php endif; ?>
</form>

<h2>Danh sách lịch học</h2>
<table border="1">
    <tr>
        <th>#</th>
        <th>Môn học</th>
        <th>Thứ</th>
        <th>Tiết</th>
        <th>Phòng</th>
        <th>Buổi</th>
        <th>Thao tác</th>
    </tr>
    <?php foreach ($dsLich as $i => $lich): ?>
    <tr>
        <td><?= $i + 1 ?></td>
        <td><?= htmlspecialchars($lich['ten_mon']) ?></td>
        <td><?= htmlspecialchars($lich['thu']) ?></td>
        <td><?= $lich['tiet'] ?></td>
        <td><?= htmlspecialchars($lich['phong']) ?></td>
        <td><?= $lich['buoi'] ?></td>
        <td>
            <a href="?sua=<?= $i ?>">Sửa</a> |
            <a href="?xoa=<?= $i ?>" onclick="return confirm('Xóa lịch học này?')">Xóa</a>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

</body>
</html>