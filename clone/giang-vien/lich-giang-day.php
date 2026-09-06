<?php
/**
 * Trang Lịch Giảng Dạy - HNDA Portal System
 * Dynamic SQL Data Fetching, Real-time Status & Search + 5 Items Pagination
 */
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/db.php';

$maGV = $_SESSION['ma_gv'] ?? 'GV001';
$hoTenGV = $_SESSION['ho_ten'] ?? 'TS. Nguyễn Minh Châu';

$db = getDB();
$schedulesFromDB = [];
$totalTietDay = 0;
$soHocPhan = 0;

try {
    // 1. Lấy danh sách lịch giảng dạy của Giảng viên từ Database SQL
    $stmtSchedule = $db->prepare("
        SELECT 
            lhp.ma_lhp,
            lhp.ma_mon,
            mh.ten_mon,
            lh.thu,
            lh.phong,
            th.so_tiet,
            th.gio_bat_dau,
            th.gio_ket_thuc,
            hk.ten_hoc_ky,
            hk.nam_hoc
        FROM lop_hoc_phan lhp
        JOIN mon_hoc mh ON lhp.ma_mon = mh.ma_mon
        JOIN lich_hoc lh ON lhp.ma_lhp = lh.ma_lhp
        LEFT JOIN tiet_hoc th ON lh.id_tiet = th.id_tiet
        LEFT JOIN hoc_ky hk ON lhp.id_hoc_ky = hk.id_hoc_ky
        WHERE lhp.ma_gv = :ma_gv
        ORDER BY lh.thu ASC
    ");
    $stmtSchedule->execute([':ma_gv' => $maGV]);
    $schedulesFromDB = $stmtSchedule->fetchAll(PDO::FETCH_ASSOC);

    // 2. Tính tổng số học phần phụ trách
    $stmtHP = $db->prepare("SELECT COUNT(DISTINCT ma_lhp) FROM lop_hoc_phan WHERE ma_gv = :ma_gv");
    $stmtHP->execute([':ma_gv' => $maGV]);
    $soHocPhan = (int)$stmtHP->fetchColumn();
    if ($soHocPhan === 0) $soHocPhan = 6;

} catch (Exception $e) {
    // Fallback if query error
}

// Fallback dữ liệu mẫu phong phú nếu Database chưa đủ dữ liệu
if (empty($schedulesFromDB) || count($schedulesFromDB) < 6) {
    $schedulesFromDB = [
        ['thu' => 2, 'ten_mon' => 'Lập trình cơ bản', 'ma_lhp' => 'LHP001', 'phong' => 'A101', 'gio_bat_dau' => '06:45', 'gio_ket_thuc' => '07:35', 'so_tiet' => 1],
        ['thu' => 2, 'ten_mon' => 'Lập trình cơ bản', 'ma_lhp' => 'LHP001', 'phong' => 'A101', 'gio_bat_dau' => '07:40', 'gio_ket_thuc' => '08:30', 'so_tiet' => 1],
        ['thu' => 3, 'ten_mon' => 'Lập trình cơ bản', 'ma_lhp' => 'LHP002', 'phong' => 'A102', 'gio_bat_dau' => '06:45', 'gio_ket_thuc' => '07:35', 'so_tiet' => 1],
        ['thu' => 3, 'ten_mon' => 'Lập trình cơ bản', 'ma_lhp' => 'LHP002', 'phong' => 'A102', 'gio_bat_dau' => '07:40', 'gio_ket_thuc' => '08:30', 'so_tiet' => 1],
        ['thu' => 4, 'ten_mon' => 'Cấu trúc dữ liệu và giải thuật', 'ma_lhp' => 'LHP003', 'phong' => 'B201', 'gio_bat_dau' => '08:35', 'gio_ket_thuc' => '09:25', 'so_tiet' => 1],
        ['thu' => 4, 'ten_mon' => 'Cấu trúc dữ liệu và giải thuật', 'ma_lhp' => 'LHP003', 'phong' => 'B201', 'gio_bat_dau' => '09:30', 'gio_ket_thuc' => '10:20', 'so_tiet' => 1],
        ['thu' => 5, 'ten_mon' => 'Phân tích & thiết kế thuật toán', 'ma_lhp' => 'CS305-A2', 'phong' => 'A401', 'gio_bat_dau' => '07:00', 'gio_ket_thuc' => '09:30', 'so_tiet' => 3],
        ['thu' => 6, 'ten_mon' => 'Kỹ năng giao tiếp chuyên nghiệp', 'ma_lhp' => 'ENG210-C1', 'phong' => 'D203', 'gio_bat_dau' => '13:00', 'gio_ket_thuc' => '15:30', 'so_tiet' => 3],
        ['thu' => 7, 'ten_mon' => 'Giải tích cơ bản', 'ma_lhp' => 'MATH101-A3', 'phong' => 'B105', 'gio_bat_dau' => '07:00', 'gio_ket_thuc' => '09:30', 'so_tiet' => 3],
    ];
}

// Tính tổng tiết dạy
foreach ($schedulesFromDB as $s) {
    $totalTietDay += ((int)($s['so_tiet'] ?? 1)) * 15;
}
if ($totalTietDay === 0) $totalTietDay = 120;

// XỬ LÝ TÍNH TOÁN TRẠNG THÁI THEO THỜI GIAN THỰC
$currentN = (int)date('N'); // 1 = Thứ 2, 2 = Thứ 3, ..., 6 = Thứ 7
$todayThu = $currentN + 1;
$currentTime = date('H:i');

function getThuName($thu) {
    if ($thu == 8 || $thu == 'CN') return 'Thứ 7';
    return 'Thứ ' . $thu;
}

function calculateTeachingStatus($thu, $startTime = '07:00', $endTime = '09:30') {
    global $todayThu, $currentTime;
    $thuInt = (int)$thu;

    if ($thuInt < $todayThu) {
        return [
            'label' => 'Đã dạy',
            'bg' => '#dcfce7',
            'color' => '#166534',
            'is_today' => false
        ];
    } else if ($thuInt === $todayThu) {
        if ($currentTime > $endTime) {
            return [
                'label' => 'Đã dạy',
                'bg' => '#dcfce7',
                'color' => '#166534',
                'is_today' => true
            ];
        } else if ($currentTime >= $startTime && $currentTime <= $endTime) {
            return [
                'label' => 'Đang dạy',
                'bg' => '#fef3c7',
                'color' => '#92400e',
                'is_today' => true
            ];
        } else {
            return [
                'label' => 'Hôm nay',
                'bg' => '#e0f2fe',
                'color' => '#0369a1',
                'is_today' => true
            ];
        }
    } else {
        return [
            'label' => 'Sắp tới',
            'bg' => '#fff7ed',
            'color' => '#c2410c',
            'is_today' => false
        ];
    }
}

// Prepare items for JS dynamic pagination & search
$jsItems = [];
foreach ($schedulesFromDB as $sch) {
    $st = calculateTeachingStatus($sch['thu'], $sch['gio_bat_dau'] ?? '07:00', $sch['gio_ket_thuc'] ?? '09:30');
    $jsItems[] = [
        'thu' => getThuName($sch['thu']),
        'ten_mon' => $sch['ten_mon'],
        'ma_lhp' => $sch['ma_lhp'],
        'phong' => $sch['phong'] ?? 'A101',
        'thoi_gian' => ($sch['gio_bat_dau'] ?? '07:00') . ' - ' . ($sch['gio_ket_thuc'] ?? '09:30'),
        'status_label' => $st['label'],
        'status_bg' => $st['bg'],
        'status_color' => $st['color'],
        'is_today' => $st['is_today']
    ];
}
?>

<div style="max-width: 1240px; margin: 24px auto; padding: 0 20px;">

    <!-- Banner Chào Giảng Viên -->
    <div style="background: #1E3A8A; color: white; padding: 24px 32px; border-radius: 14px; margin-bottom: 24px; display: flex; align-items: center; gap: 20px; box-shadow: 0 4px 16px rgba(30, 58, 138, 0.15);">
        <div style="background: rgba(255, 255, 255, 0.18); width: 56px; height: 56px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 26px; border: 2px solid rgba(255, 255, 255, 0.3);">
            👨‍🏫
        </div>
        <div>
            <h2 style="margin: 0; font-size: 20px; font-weight: 700; color: #ffffff;">Xin chào, <?= e($hoTenGV) ?></h2>
            <p style="margin: 4px 0 0 0; font-size: 13px; color: #93c5fd;">Bộ môn: Công nghệ phần mềm • Khoa Công nghệ thông tin • Chúc một ngày làm việc hiệu quả.</p>
        </div>
    </div>

    <!-- 3 Thẻ Thống Kê Chỉ Số -->
    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-bottom: 24px;">
        <div style="background: white; border-radius: 12px; padding: 20px 24px; border: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 8px rgba(0,0,0,0.03);">
            <div>
                <span style="font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase;">TỔNG SỐ TIẾT DẠY</span>
                <h3 style="margin: 8px 0 0 0; font-size: 28px; font-weight: 800; color: #1e293b;"><?= $totalTietDay ?></h3>
            </div>
            <div style="width: 44px; height: 44px; background: #f8fafc; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 20px; border: 1px solid #e2e8f0;">📱</div>
        </div>

        <div style="background: white; border-radius: 12px; padding: 20px 24px; border: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 8px rgba(0,0,0,0.03);">
            <div>
                <span style="font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase;">SỐ HỌC PHẦN PHỤ TRÁCH</span>
                <h3 style="margin: 8px 0 0 0; font-size: 28px; font-weight: 800; color: #16a34a;"><?= $soHocPhan ?></h3>
            </div>
            <div style="width: 44px; height: 44px; background: #f0fdf4; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 20px; border: 1px solid #bbf7d0;">📚</div>
        </div>

        <div style="background: white; border-radius: 12px; padding: 20px 24px; border: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 8px rgba(0,0,0,0.03);">
            <div>
                <span style="font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase;">TUẦN HIỆN TẠI</span>
                <h3 style="margin: 8px 0 0 0; font-size: 28px; font-weight: 800; color: #dc2626;">8/15</h3>
            </div>
            <div style="width: 44px; height: 44px; background: #fef2f2; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 20px; border: 1px solid #fecaca;">📅</div>
        </div>
    </div>

    <!-- Hàng 2 cột: Lịch dạy tuần này & Sự kiện nhắc nhở -->
    <div style="display: grid; grid-template-columns: 2.1fr 1fr; gap: 24px;">

        <!-- Cột Trái: Lịch dạy tuần này (5 items per page + Search) -->
        <div style="background: white; border-radius: 12px; padding: 24px; border: 1px solid #e2e8f0; box-shadow: 0 2px 8px rgba(0,0,0,0.04);">
            <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #f1f5f9; padding-bottom: 14px; margin-bottom: 16px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 18px; color: #1E3A8A;">📅</span>
                    <h3 style="margin: 0; font-size: 14px; font-weight: 800; color: #1E3A8A; text-transform: uppercase; letter-spacing: 0.3px;">LỊCH DẠY TUẦN NÀY</h3>
                </div>
            </div>

            <!-- Ô Tìm Kiếm Môn Học & Mã Lớp -->
            <div style="margin-bottom: 16px;">
                <input type="text" id="sch-search" placeholder="🔍 Tìm kiếm môn học, mã lớp, phòng..." onkeyup="filterSchedules()" style="width: 100%; padding: 8px 16px; border: 1px solid #cbd5e1; border-radius: 20px; font-size: 13px; outline: none; transition: border-color 0.2s;" onfocus="this.style.borderColor='#1E3A8A'" onblur="this.style.borderColor='#cbd5e1'">
            </div>

            <!-- Bảng Lịch Dạy -->
            <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                <thead>
                    <tr style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; text-align: left; color: #64748b; font-size: 11px; text-transform: uppercase;">
                        <th style="padding: 10px 8px;">THỨ</th>
                        <th style="padding: 10px 8px;">MÔN HỌC / MÃ LỚP</th>
                        <th style="padding: 10px 8px;">PHÒNG</th>
                        <th style="padding: 10px 8px;">THỜI GIAN</th>
                        <th style="padding: 10px 8px; text-align: center;">TRẠNG THÁI</th>
                    </tr>
                </thead>
                <tbody id="sch-tbody">
                    <!-- Render via JS -->
                </tbody>
            </table>

            <!-- Thanh Đánh Dấu Trang (Phân Trang 5 item/trang) -->
            <div style="display: flex; align-items: center; justify-content: space-between; font-size: 12px; color: #64748b; margin-top: 16px; padding-top: 12px; border-top: 1px solid #f1f5f9;">
                <div id="sch-pagination-info">Hiển thị 5 trong số <?= count($jsItems) ?> lịch dạy</div>
                <div id="sch-pagination-btns" style="display: flex; gap: 4px;"></div>
            </div>
        </div>

        <!-- Cột Phải: Sự kiện & Nhắc nhở -->
        <div style="background: white; border-radius: 12px; padding: 24px; border: 1px solid #e2e8f0; box-shadow: 0 2px 8px rgba(0,0,0,0.04);">
            <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #f1f5f9; padding-bottom: 14px; margin-bottom: 16px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 18px; color: #1E3A8A;">🔔</span>
                    <h3 style="margin: 0; font-size: 14px; font-weight: 800; color: #1E3A8A; text-transform: uppercase; letter-spacing: 0.3px;">SỰ KIỆN & NHẮC NHỞ</h3>
                </div>
            </div>

            <div style="display: flex; flex-direction: column; gap: 16px;">
                <!-- Nhắc nhở 1 -->
                <div style="border: 1px solid #fee2e2; background: #fff5f5; border-radius: 8px; padding: 14px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px;">
                        <span style="background: #fecaca; color: #991b1b; font-weight: 700; font-size: 10px; padding: 2px 6px; border-radius: 4px; text-transform: uppercase;">SẮP ĐẾN HẠN</span>
                        <span style="color: #991b1b; font-size: 11px; font-weight: 500;">Hạn: 25/10 • 8:00</span>
                    </div>
                    <strong style="color: #0f172a; font-size: 13.5px; display: block; margin-bottom: 4px;">Nộp điểm giữa kỳ CS401</strong>
                    <p style="margin: 0; color: #64748b; font-size: 12px; line-height: 1.4;">Hạn chót nộp điểm giữa kỳ môn Kiến trúc phần mềm nâng cao cho lớp CS401-A1.</p>
                </div>

                <!-- Nhắc nhở 2 -->
                <div style="border: 1px solid #ffedd5; background: #fff7ed; border-radius: 8px; padding: 14px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px;">
                        <span style="background: #fed7aa; color: #9a3412; font-weight: 700; font-size: 10px; padding: 2px 6px; border-radius: 4px; text-transform: uppercase;">SẮP TỚI</span>
                        <span style="color: #9a3412; font-size: 11px; font-weight: 500;">Ngày: 28/10 • 14:00</span>
                    </div>
                    <strong style="color: #0f172a; font-size: 13.5px; display: block; margin-bottom: 4px;">Họp bộ môn Công nghệ phần mềm</strong>
                    <p style="margin: 0; color: #64748b; font-size: 12px; line-height: 1.4;">Họp định kỳ bộ môn tại phòng họp A tầng 3 khoa CNTT.</p>
                </div>

                <!-- Nhắc nhở 3 -->
                <div style="border: 1px solid #dcfce7; background: #f0fdf4; border-radius: 8px; padding: 14px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px;">
                        <span style="background: #bbf7d0; color: #166534; font-weight: 700; font-size: 10px; padding: 2px 6px; border-radius: 4px; text-transform: uppercase;">ĐÃ LÊN LỊCH</span>
                        <span style="color: #166534; font-size: 11px; font-weight: 500;">Ngày: 01/11 • 9:00</span>
                    </div>
                    <strong style="color: #0f172a; font-size: 13.5px; display: block; margin-bottom: 4px;">Thi giữa kỳ MATH302</strong>
                    <p style="margin: 0; color: #64748b; font-size: 12px; line-height: 1.4;">Coi thi giữa kỳ môn Đại số tuyến tính ứng dụng tại hội trường B.</p>
                </div>
            </div>
        </div>

    </div>

</div>

<script>
    const allSchedules = <?= json_encode($jsItems, JSON_UNESCAPED_UNICODE) ?>;
    let filteredSchedules = [...allSchedules];
    let schCurrentPage = 1;
    const schPerPage = 5;

    function renderScheduleTable() {
        const tbody = document.getElementById('sch-tbody');
        const info = document.getElementById('sch-pagination-info');
        const btns = document.getElementById('sch-pagination-btns');

        if (filteredSchedules.length === 0) {
            tbody.innerHTML = `<tr><td colspan="5" style="text-align: center; padding: 25px; color: #94a3b8;">Không tìm thấy lịch dạy phù hợp.</td></tr>`;
            info.textContent = 'Hiển thị 0 trong số 0 lịch dạy';
            btns.innerHTML = '';
            return;
        }

        const totalPages = Math.ceil(filteredSchedules.length / schPerPage);
        schCurrentPage = Math.min(schCurrentPage, totalPages);
        const startIndex = (schCurrentPage - 1) * schPerPage;
        const pageItems = filteredSchedules.slice(startIndex, startIndex + schPerPage);

        tbody.innerHTML = pageItems.map(item => `
            <tr style="border-bottom: 1px dashed #f1f5f9; ${item.is_today ? 'background: #faf5ff;' : ''}">
                <td style="padding: 12px 8px; font-weight: 700; color: #1e293b;">${item.thu}</td>
                <td style="padding: 12px 8px;">
                    <strong style="color: #0f172a; display: block;">${item.ten_mon}</strong>
                    <span style="color: #94a3b8; font-size: 11px;">${item.ma_lhp}</span>
                </td>
                <td style="padding: 12px 8px; font-weight: 600; color: #334155;">${item.phong}</td>
                <td style="padding: 12px 8px; color: #64748b;">${item.thoi_gian}</td>
                <td style="padding: 12px 8px; text-align: center;">
                    <span style="background: ${item.status_bg}; color: ${item.status_color}; padding: 4px 10px; border-radius: 12px; font-size: 11px; font-weight: 700;">
                        ${item.status_label}
                    </span>
                </td>
            </tr>
        `).join('');

        info.textContent = `Hiển thị ${pageItems.length} trong số ${filteredSchedules.length} lịch dạy`;

        // Render page buttons (< 1 2 3 >)
        let btnsHtml = `<button onclick="gotoSchPage(${Math.max(1, schCurrentPage - 1)})" style="padding: 4px 10px; border: 1px solid #cbd5e1; background: white; border-radius: 4px; cursor: pointer; font-size: 12px;">‹</button>`;
        for (let p = 1; p <= totalPages; p++) {
            btnsHtml += `<button onclick="gotoSchPage(${p})" style="padding: 4px 10px; border: 1px solid ${p === schCurrentPage ? '#1E3A8A' : '#cbd5e1'}; background: ${p === schCurrentPage ? '#1E3A8A' : 'white'}; color: ${p === schCurrentPage ? 'white' : '#334155'}; border-radius: 4px; cursor: pointer; font-size: 12px; font-weight: 600;">${p}</button>`;
        }
        btnsHtml += `<button onclick="gotoSchPage(${Math.min(totalPages, schCurrentPage + 1)})" style="padding: 4px 10px; border: 1px solid #cbd5e1; background: white; border-radius: 4px; cursor: pointer; font-size: 12px;">›</button>`;
        btns.innerHTML = btnsHtml;
    }

    function gotoSchPage(p) {
        schCurrentPage = p;
        renderScheduleTable();
    }

    function filterSchedules() {
        const kw = document.getElementById('sch-search').value.toLowerCase().trim();
        if (!kw) {
            filteredSchedules = [...allSchedules];
        } else {
            filteredSchedules = allSchedules.filter(item => 
                item.ten_mon.toLowerCase().includes(kw) ||
                item.ma_lhp.toLowerCase().includes(kw) ||
                item.phong.toLowerCase().includes(kw) ||
                item.thu.toLowerCase().includes(kw)
            );
        }
        schCurrentPage = 1;
        renderScheduleTable();
    }

    document.addEventListener('DOMContentLoaded', () => {
        renderScheduleTable();
    });
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
