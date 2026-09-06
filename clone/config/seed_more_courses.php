<?php
/**
 * Script bổ sung toàn bộ môn học thuộc tất cả các Khoa & Chuyên ngành
 * HNDA Portal System
 */
require_once __DIR__ . '/db.php';

$db = getDB();

$courses = [
    // --- KHOA CÔNG NGHỆ THÔNG TIN (CNTT) ---
    // Ngành KTPM
    ['30CNTT001', 'Lập trình cơ bản', 3, 'CNTT', 'MON_CHUYEN_NGANH', 'KTPM'],
    ['30CNTT002', 'Cấu trúc dữ liệu và giải thuật', 3, 'CNTT', 'MON_CHUYEN_NGANH', 'KTPM'],
    ['30CNTT003', 'Lập trình hướng đối tượng', 3, 'CNTT', 'MON_CHUYEN_NGANH', 'KTPM'],
    ['30CNTT006', 'Lập trình Web', 3, 'CNTT', 'MON_CHUYEN_NGANH', 'KTPM'],
    ['30CNTT008', 'Trí tuệ nhân tạo', 3, 'CNTT', 'MON_CHUYEN_NGANH', 'KTPM'],
    ['30CNTT009', 'Lập trình ứng dụng Di động', 3, 'CNTT', 'MON_CHUYEN_NGANH', 'KTPM'],
    ['30CNTT010', 'Kiến trúc phần mềm', 3, 'CNTT', 'MON_CHUYEN_NGANH', 'KTPM'],
    ['30CNTT011', 'Kiểm thử phần mềm', 3, 'CNTT', 'MON_CHUYEN_NGANH', 'KTPM'],
    ['30CNTT012', 'Quản lý dự án phần mềm', 3, 'CNTT', 'MON_CHUYEN_NGANH', 'KTPM'],
    ['30CNTT013', 'Đảm bảo chất lượng phần mềm', 3, 'CNTT', 'MON_CHUYEN_NGANH', 'KTPM'],
    ['30CNTT014', 'Điện toán đám mây', 3, 'CNTT', 'MON_CHUYEN_NGANH', 'KTPM'],
    ['30CNTT015', 'Đồ án chuyên ngành KTPM', 4, 'CNTT', 'MON_CHUYEN_NGANH', 'KTPM'],

    // Ngành HTTT
    ['30CNTT004', 'Cơ sở dữ liệu', 3, 'CNTT', 'MON_CHUYEN_NGANH', 'HTTT'],
    ['30CNTT005', 'Mạng máy tính', 3, 'CNTT', 'MON_CHUYEN_NGANH', 'HTTT'],
    ['30CNTT016', 'Phân tích thiết kế hệ thống thông tin', 3, 'CNTT', 'MON_CHUYEN_NGANH', 'HTTT'],
    ['30CNTT017', 'Hệ quản trị CSDL PostgreSQL & Oracle', 3, 'CNTT', 'MON_CHUYEN_NGANH', 'HTTT'],
    ['30CNTT018', 'Khai phá dữ liệu (Data Mining)', 3, 'CNTT', 'MON_CHUYEN_NGANH', 'HTTT'],
    ['30CNTT019', 'Dữ liệu lớn (Big Data)', 3, 'CNTT', 'MON_CHUYEN_NGANH', 'HTTT'],
    ['30CNTT020', 'Hệ thống thông tin doanh nghiệp ERP', 3, 'CNTT', 'MON_CHUYEN_NGANH', 'HTTT'],

    // Ngành ATTT
    ['30CNTT007', 'An toàn thông tin', 3, 'CNTT', 'MON_CHUYEN_NGANH', 'ATTT'],
    ['30CNTT021', 'Mật mã học và an toàn dữ liệu', 3, 'CNTT', 'MON_CHUYEN_NGANH', 'ATTT'],
    ['30CNTT022', 'An ninh mạng máy tính', 3, 'CNTT', 'MON_CHUYEN_NGANH', 'ATTT'],
    ['30CNTT023', 'Bảo mật hệ điều hành và ứng dụng', 3, 'CNTT', 'MON_CHUYEN_NGANH', 'ATTT'],
    ['30CNTT024', 'Kiểm thử an ninh và đánh giá lỗ hổng', 3, 'CNTT', 'MON_CHUYEN_NGANH', 'ATTT'],

    // --- KHOA ĐIỆN TỬ VIỄN THÔNG (DTVT) ---
    // Ngành KTDT
    ['30DTVT001', 'Điện tử cơ bản', 3, 'DTVT', 'MON_CHUYEN_NGANH', 'KTDT'],
    ['30DTVT002', 'Lý thuyết mạch điện', 3, 'DTVT', 'MON_CHUYEN_NGANH', 'KTDT'],
    ['30DTVT003', 'Kỹ thuật vi xử lý', 3, 'DTVT', 'MON_CHUYEN_NGANH', 'KTDT'],
    ['30DTVT004', 'Điện tử tương tự & điện tử số', 3, 'DTVT', 'MON_CHUYEN_NGANH', 'KTDT'],
    ['30DTVT005', 'Xử lý tín hiệu số', 3, 'DTVT', 'MON_CHUYEN_NGANH', 'KTDT'],
    ['30DTVT006', 'Thiết kế vi mạch (VLSI)', 3, 'DTVT', 'MON_CHUYEN_NGANH', 'KTDT'],
    ['30DTVT007', 'Hệ thống nhúng & IoT', 3, 'DTVT', 'MON_CHUYEN_NGANH', 'KTDT'],

    // Ngành VT
    ['30DTVT008', 'Lý thuyết thông tin', 3, 'DTVT', 'MON_CHUYEN_NGANH', 'VT'],
    ['30DTVT009', 'Mạng viễn thông & Truyền thông dữ liệu', 3, 'DTVT', 'MON_CHUYEN_NGANH', 'VT'],
    ['30DTVT010', 'Thông tin di động 5G/6G', 3, 'DTVT', 'MON_CHUYEN_NGANH', 'VT'],
    ['30DTVT011', 'Kỹ thuật Anten và truyền sóng', 3, 'DTVT', 'MON_CHUYEN_NGANH', 'VT'],
    ['30DTVT012', 'Thông tin quang', 3, 'DTVT', 'MON_CHUYEN_NGANH', 'VT'],

    // --- KHOA QUẢN TRỊ KINH DOANH (QTKD) ---
    // Ngành QTKDTH
    ['30QTKD001', 'Quản trị học đại cương', 3, 'QTKD', 'MON_CHUYEN_NGANH', 'QTKDTH'],
    ['30QTKD002', 'Quản trị chiến lược', 3, 'QTKD', 'MON_CHUYEN_NGANH', 'QTKDTH'],
    ['30QTKD003', 'Quản trị nhân sự', 3, 'QTKD', 'MON_CHUYEN_NGANH', 'QTKDTH'],
    ['30QTKD004', 'Quản trị chất lượng & chuỗi cung ứng', 3, 'QTKD', 'MON_CHUYEN_NGANH', 'QTKDTH'],
    ['30QTKD005', 'Hành vi tổ chức', 3, 'QTKD', 'MON_CHUYEN_NGANH', 'QTKDTH'],
    ['30QTKD006', 'Quản trị rủi ro doanh nghiệp', 3, 'QTKD', 'MON_CHUYEN_NGANH', 'QTKDTH'],

    // Ngành MKT
    ['30QTKD007', 'Quản trị Marketing', 3, 'QTKD', 'MON_CHUYEN_NGANH', 'MKT'],
    ['30QTKD008', 'Nghiên cứu thị trường', 3, 'QTKD', 'MON_CHUYEN_NGANH', 'MKT'],
    ['30QTKD009', 'Hành vi người tiêu dùng', 3, 'QTKD', 'MON_CHUYEN_NGANH', 'MKT'],
    ['30QTKD010', 'Marketing số (Digital Marketing)', 3, 'QTKD', 'MON_CHUYEN_NGANH', 'MKT'],
    ['30QTKD011', 'Truyền thông Marketing tích hợp (IMC)', 3, 'QTKD', 'MON_CHUYEN_NGANH', 'MKT'],
    ['30QTKD012', 'Quản trị thương hiệu', 3, 'QTKD', 'MON_CHUYEN_NGANH', 'MKT'],

    // --- KHOA KẾ TOÁN (KT) ---
    // Ngành KTKT
    ['30KT001', 'Nguyên lý kế toán', 3, 'KT', 'MON_CHUYEN_NGANH', 'KTKT'],
    ['30KT002', 'Kế toán tài chính 1', 3, 'KT', 'MON_CHUYEN_NGANH', 'KTKT'],
    ['30KT003', 'Kế toán tài chính 2', 3, 'KT', 'MON_CHUYEN_NGANH', 'KTKT'],
    ['30KT004', 'Kế toán quản trị', 3, 'KT', 'MON_CHUYEN_NGANH', 'KTKT'],
    ['30KT005', 'Kiểm toán tài chính', 3, 'KT', 'MON_CHUYEN_NGANH', 'KTKT'],
    ['30KT006', 'Kế toán thuế & Báo cáo tài chính', 3, 'KT', 'MON_CHUYEN_NGANH', 'KTKT'],

    // Ngành TCNH
    ['30KT007', 'Nhập môn Tài chính tiền tệ', 3, 'KT', 'MON_CHUYEN_NGANH', 'TCNH'],
    ['30KT008', 'Quản trị Ngân hàng thương mại', 3, 'KT', 'MON_CHUYEN_NGANH', 'TCNH'],
    ['30KT009', 'Thị trường chứng khoán & Đầu tư tài chính', 3, 'KT', 'MON_CHUYEN_NGANH', 'TCNH'],
    ['30KT010', 'Tài chính doanh nghiệp', 3, 'KT', 'MON_CHUYEN_NGANH', 'TCNH'],
    ['30KT011', 'Thẩm định dự án đầu tư', 3, 'KT', 'MON_CHUYEN_NGANH', 'TCNH'],

    // --- KHOA NGOẠI NGỮ (NGANHK) ---
    // Ngành NNTA
    ['30INF003', 'Tiếng Anh 1', 3, 'NGANHK', 'MON_CHUNG', 'NNTA'],
    ['30INF004', 'Tiếng Anh 2', 3, 'NGANHK', 'MON_CHUNG', 'NNTA'],
    ['30NNTA001', 'Tiếng Anh chuyên ngành Kinh tế & Kỹ thuật', 3, 'NGANHK', 'MON_CHUYEN_NGANH', 'NNTA'],
    ['30NNTA002', 'Lý thuyết dịch & Phân tích văn bản', 3, 'NGANHK', 'MON_CHUYEN_NGANH', 'NNTA'],
    ['30NNTA003', 'Kỹ năng Biên dịch nâng cao', 3, 'NGANHK', 'MON_CHUYEN_NGANH', 'NNTA'],
    ['30NNTA004', 'Kỹ năng Phiên dịch nâng cao', 3, 'NGANHK', 'MON_CHUYEN_NGANH', 'NNTA'],
    ['30NNTA005', 'Văn hóa và Văn học các nước nói Tiếng Anh', 3, 'NGANHK', 'MON_CHUYEN_NGANH', 'NNTA'],
    ['30NNTA006', 'Giao tiếp liên văn hóa', 3, 'NGANHK', 'MON_CHUYEN_NGANH', 'NNTA'],

    // --- MÔN CHUNG ---
    ['30INF001', 'Toán cao cấp', 3, null, 'MON_CHUNG', null],
    ['30INF002', 'Vật lý đại cương', 2, null, 'MON_CHUNG', null],
    ['30INF005', 'Tư tưởng Hồ Chí Minh', 2, null, 'MON_CHUNG', null],
    ['30INF006', 'Pháp luật đại cương', 2, null, 'MON_CHUNG', null],
];

$stmt = $db->prepare("
    INSERT INTO mon_hoc (ma_mon, ten_mon, so_tin_chi, ma_khoa, loai_mon_hoc, ma_nganh, trang_thai)
    VALUES (?, ?, ?, ?, ?, ?, 'HOAT_DONG')
    ON CONFLICT (ma_mon) DO UPDATE 
    SET ten_mon = EXCLUDED.ten_mon,
        so_tin_chi = EXCLUDED.so_tin_chi,
        ma_khoa = EXCLUDED.ma_khoa,
        loai_mon_hoc = EXCLUDED.loai_mon_hoc,
        ma_nganh = EXCLUDED.ma_nganh,
        trang_thai = 'HOAT_DONG'
");

$inserted = 0;
foreach ($courses as [$ma, $ten, $tc, $khoa, $loai, $nganh]) {
    $stmt->execute([$ma, $ten, $tc, $khoa, $loai, $nganh]);
    $inserted++;
}

echo "✅ Đã thêm thành công $inserted môn học đầy đủ cho tất cả các Khoa & Chuyên ngành!\n";
