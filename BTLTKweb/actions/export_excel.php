<?php
/**
 * Xuất toàn bộ Bảng điểm lớp học ra File Excel CSV (UTF-8 BOM tiếng Việt)
 */
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/queries.php';

$maLop = $_GET['ma_lop'] ?? 'CS101';
$res = getDanhSachDiemLop($maLop, '', 1, 10000);
$students = $res['items'];

$filename = "BangDiem_Lop_" . $maLop . "_" . date('Ymd_His') . ".csv";

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$output = fopen('php://output', 'w');

// Chèn UTF-8 Byte Order Mark (BOM) để Microsoft Excel hiển thị đúng tiếng Việt Unicode
fputs($output, "\xEF\xBB\xBF");

// Ghi Tiêu đề các cột
fputcsv($output, ['STT', 'MSSV', 'Họ và tên', 'Lớp sinh hoạt', 'Chuyên cần (10%)', 'Giữa kỳ (30%)', 'Cuối kỳ (60%)', 'Tổng kết', 'Xếp loại']);

// Ghi từng dòng dữ liệu
$stt = 1;
foreach ($students as $row) {
    fputcsv($output, [
        $stt++,
        $row['MSSV'],
        $row['HoTen'],
        $row['LopSinhHoat'],
        number_format($row['DiemCC'], 1),
        number_format($row['DiemGK'], 1),
        number_format($row['DiemCK'], 1),
        number_format($row['TongKet'], 1),
        $row['XepLoai']
    ]);
}

fclose($output);
exit;
