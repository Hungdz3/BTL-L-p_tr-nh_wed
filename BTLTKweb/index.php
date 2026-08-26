<?php
/**
 * Trang Quản lý Lớp học & Nhập điểm cho Giảng viên (Popup Chọn Lớp Học - Phân trang - Nhập Bàn phím Excel - Full CRUD)
 * File: D:\BTLTKweb\index.php
 */
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/queries.php';
require_once __DIR__ . '/includes/functions.php';

$maLop = $_GET['ma_lop'] ?? 'CS101';
$keyword = trim($_GET['search'] ?? '');
$page = (int)($_GET['page'] ?? 1);
$perPage = 10;

// Lấy danh sách tất cả Lớp học đính kèm sĩ số và danh sách toàn bộ học viên
$classList = getDanhSachLopHoc();
$allStudents = getAllHocVien();

// Lấy dữ liệu học viên trang hiện tại & chỉ số thống kê toàn lớp
$data = getDanhSachDiemLop($maLop, $keyword, $page, $perPage);
$students = $data['items'];
$stats = getThongKeLopHoc($maLop);

// Lấy thông báo Flash & Lỗi Form
$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError = $_SESSION['flash_error'] ?? null;
$formErrors = $_SESSION['form_errors'] ?? [];
$formData = $_SESSION['form_data'] ?? [];
$openModal = $_SESSION['open_modal'] ?? false;

unset($_SESSION['flash_success'], $_SESSION['flash_error'], $_SESSION['form_errors'], $_SESSION['form_data'], $_SESSION['open_modal']);
?>

<!-- Banner Chào Giảng viên (Compact Size) -->
<div class="bg-indigo-900 text-white rounded-xl p-4 shadow-md flex flex-col md:flex-row items-start md:items-center justify-between gap-3">
    <div class="flex items-center space-x-3">
        <div class="w-11 h-11 bg-indigo-700/60 rounded-full flex items-center justify-center text-white text-xl font-bold border-2 border-indigo-400">
            <i class="fa-solid fa-user-tie"></i>
        </div>
        <div>
            <h2 class="text-base font-bold tracking-tight">Xin chào, TS. Nguyễn Minh Châu</h2>
            <p class="text-[11px] text-indigo-200">Bộ môn: Công nghệ phần mềm • Khoa Công nghệ thông tin</p>
        </div>
    </div>
</div>

<!-- Thẻ Chọn Lớp Học (Nút Thay đổi lớp học tương tác Modal Chọn Lớp) -->
<div class="bg-white rounded-xl p-3.5 shadow-sm border border-gray-200 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
    <div class="flex items-center space-x-3">
        <div class="p-2 bg-emerald-50 text-emerald-600 rounded-lg border border-emerald-200">
            <i class="fa-solid fa-book-bookmark text-base"></i>
        </div>
        <div>
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">LỚP HỌC ĐANG CHỌN</p>
            <p class="text-xs font-bold text-gray-900">
                <?php 
                $currClass = array_filter($classList, fn($c) => $c['MaLop'] === $maLop);
                $currClassName = reset($currClass)['TenMonHoc'] ?? 'CS101 - Lập trình Python - HK1 2025';
                echo e($currClassName);
                ?>
            </p>
        </div>
    </div>
    
    <!-- Nút "Thay đổi lớp học v" bật Modal Chọn Lớp -->
    <div>
        <button onclick="openClassModal()" type="button" 
                class="px-3.5 py-1.5 bg-white border border-gray-300 text-gray-700 text-xs font-semibold rounded-lg hover:bg-indigo-50 hover:border-indigo-800 hover:text-indigo-900 transition shadow-sm flex items-center gap-2 cursor-pointer">
            <span>Thay đổi lớp học</span>
            <i class="fa-solid fa-chevron-down text-[10px]"></i>
        </button>
    </div>
</div>

<!-- Thông Báo Flash Notifications -->
<?php if ($flashSuccess): ?>
    <div class="p-3 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-800 rounded-r-lg text-xs shadow-sm flex items-center space-x-2">
        <i class="fa-solid fa-circle-check text-emerald-600 text-sm"></i>
        <span class="font-medium"><?= e($flashSuccess) ?></span>
    </div>
<?php endif; ?>

<?php if ($flashError): ?>
    <div class="p-3 bg-rose-50 border-l-4 border-rose-500 text-rose-800 rounded-r-lg text-xs shadow-sm flex items-center space-x-2">
        <i class="fa-solid fa-triangle-exclamation text-rose-600 text-sm"></i>
        <span class="font-medium"><?= $flashError ?></span>
    </div>
<?php endif; ?>

<!-- 4 Thẻ Thống Kê Chỉ Số -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
    <!-- Card 1: Sĩ số -->
    <div class="bg-white p-3.5 rounded-xl border border-gray-200 shadow-sm flex justify-between items-start">
        <div>
            <p class="text-[10px] font-bold text-gray-400 tracking-wider uppercase">SĨ SỐ</p>
            <h3 class="text-xl font-black text-gray-900 mt-0.5"><?= $stats['si_so'] ?> học viên</h3>
            <p class="text-[10px] text-gray-500 mt-0.5">Lớp lý thuyết chính thức</p>
        </div>
        <div class="p-2 bg-gray-100 text-gray-500 rounded-lg">
            <i class="fa-solid fa-users text-sm"></i>
        </div>
    </div>

    <!-- Card 2: Điểm TB Lớp -->
    <div class="bg-white p-3.5 rounded-xl border border-gray-200 shadow-sm flex justify-between items-start">
        <div>
            <p class="text-[10px] font-bold text-gray-400 tracking-wider uppercase">ĐIỂM TB LỚP</p>
            <h3 class="text-xl font-black text-gray-900 mt-0.5"><?= number_format($stats['diem_tb'], 1) ?></h3>
            <p class="text-[10px] text-gray-500 mt-0.5">Hệ điểm 10</p>
        </div>
        <div class="p-2 bg-gray-100 text-gray-500 rounded-lg">
            <i class="fa-solid fa-chart-line text-sm"></i>
        </div>
    </div>

    <!-- Card 3: Tỷ lệ đạt -->
    <div class="bg-white p-3.5 rounded-xl border border-gray-200 shadow-sm flex justify-between items-start">
        <div>
            <p class="text-[10px] font-bold text-gray-400 tracking-wider uppercase">TỶ LỆ ĐẠT</p>
            <h3 class="text-xl font-black text-gray-900 mt-0.5"><?= number_format($stats['ty_le_dat'], 1) ?>%</h3>
            <p class="text-[10px] text-gray-500 mt-0.5"><?= $stats['so_dat'] ?> / <?= $stats['si_so'] ?> học viên đạt</p>
        </div>
        <div class="p-2 bg-gray-100 text-gray-500 rounded-lg">
            <i class="fa-solid fa-circle-check text-sm"></i>
        </div>
    </div>

    <!-- Card 4: Điểm cao nhất -->
    <div class="bg-white p-3.5 rounded-xl border border-gray-200 shadow-sm flex justify-between items-start">
        <div>
            <p class="text-[10px] font-bold text-gray-400 tracking-wider uppercase">ĐIỂM CAO NHẤT</p>
            <h3 class="text-xl font-black text-gray-900 mt-0.5"><?= number_format($stats['diem_cao_nhat'], 1) ?></h3>
            <p class="text-[10px] text-gray-500 mt-0.5 truncate max-w-[130px]" title="<?= e($stats['hoc_vien_top']) ?>">
                <?= e($stats['hoc_vien_top']) ?>
            </p>
        </div>
        <div class="p-2 bg-gray-100 text-gray-500 rounded-lg">
            <i class="fa-solid fa-ribbon text-sm"></i>
        </div>
    </div>
</div>

<!-- Thanh Công Cụ (Search & Actions Bar) -->
<div class="bg-white p-3 rounded-xl border border-gray-200 shadow-sm flex flex-col md:flex-row items-center justify-between gap-3">
    <!-- Ô tìm kiếm -->
    <form method="GET" action="index.php" class="w-full md:w-72 flex items-center relative">
        <input type="hidden" name="ma_lop" value="<?= e($maLop) ?>">
        <i class="fa-solid fa-magnifying-glass absolute left-3 text-gray-400 text-xs"></i>
        <input type="text" name="search" value="<?= e($keyword) ?>" 
               placeholder="Tìm kiếm MSSV, tên..." 
               class="w-full pl-8 pr-3 py-1.5 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-indigo-800 focus:border-indigo-800 outline-none transition">
    </form>

    <!-- Nút Thao tác -->
    <div class="flex items-center space-x-2 w-full md:w-auto justify-end">
        <button onclick="openAddModal()" type="button" class="px-3.5 py-1.5 bg-emerald-700 hover:bg-emerald-800 text-white font-semibold text-xs rounded-lg shadow-sm transition flex items-center gap-1.5">
            <i class="fa-solid fa-plus"></i>
            <span>Nhập điểm</span>
        </button>

        <a href="actions/export_excel.php?ma_lop=<?= urlencode($maLop) ?>" class="px-3.5 py-1.5 bg-white border border-indigo-900 text-indigo-900 font-semibold text-xs rounded-lg hover:bg-indigo-50 transition shadow-sm flex items-center gap-1.5">
            <i class="fa-solid fa-file-excel"></i>
            <span>Xuất Excel</span>
        </a>

        <button type="submit" form="gradeForm" class="px-4 py-1.5 bg-indigo-900 hover:bg-indigo-950 text-white font-semibold text-xs rounded-lg shadow-sm transition flex items-center gap-1.5">
            <i class="fa-solid fa-floppy-disk"></i>
            <span>Lưu điểm</span>
        </button>
    </div>
</div>

<!-- Bảng Điểm Học Viên (Giao diện Full CRUD: Cột Thao tác Sửa & Xóa) -->
<form id="gradeForm" method="POST" action="actions/save_grades.php" class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
    <input type="hidden" name="ma_lop" value="<?= e($maLop) ?>">
    <input type="hidden" name="page" value="<?= $data['current_page'] ?>">
    <input type="hidden" name="search" value="<?= e($keyword) ?>">

    <?php 
    // Tự động chèn dữ liệu ẩn của tất cả học viên không hiển thị trên trang hiện tại để đảm bảo khi bấm "Lưu điểm" sẽ lưu 100% toàn bộ học viên của lớp
    $allClassData = getDanhSachDiemLop($maLop, '', 1, 100000);
    $allClassStudents = $allClassData['items'];
    $visibleMssvs = array_column($students, 'MSSV');
    foreach ($allClassStudents as $cs):
        if (!in_array($cs['MSSV'], $visibleMssvs)):
    ?>
        <input type="hidden" name="grades[<?= e($cs['MSSV']) ?>][cc]" value="<?= number_format($cs['DiemCC'], 1) ?>">
        <input type="hidden" name="grades[<?= e($cs['MSSV']) ?>][gk]" value="<?= number_format($cs['DiemGK'], 1) ?>">
        <input type="hidden" name="grades[<?= e($cs['MSSV']) ?>][ck]" value="<?= number_format($cs['DiemCK'], 1) ?>">
    <?php 
        endif;
    endforeach; 
    ?>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-[12px]" id="gradeTable">
            <thead class="bg-gray-50 border-b border-gray-200 text-gray-500 font-bold uppercase tracking-wider text-[11px]">
                <tr>
                    <th class="py-2.5 px-3 text-center w-10">STT</th>
                    <th class="py-2.5 px-3 w-28">MSSV</th>
                    <th class="py-2.5 px-3">Họ và tên</th>
                    <th class="py-2.5 px-3 text-center w-32">Chuyên cần (10%)</th>
                    <th class="py-2.5 px-3 text-center w-32">Giữa kỳ (30%)</th>
                    <th class="py-2.5 px-3 text-center w-32">Cuối kỳ (60%)</th>
                    <th class="py-2.5 px-3 text-center w-24 font-extrabold text-gray-700">Tổng kết</th>
                    <th class="py-2.5 px-3 text-center w-24">Xếp loại</th>
                    <th class="py-2.5 px-3 text-center w-28 text-indigo-900 font-extrabold">Thao tác</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 font-medium text-gray-800">
                <?php if (empty($students)): ?>
                    <tr>
                        <td colspan="9" class="text-center py-8 text-gray-400 text-xs">Không tìm thấy học viên nào trong lớp.</td>
                    </tr>
                <?php else: ?>
                    <?php $stt = $data['start_index']; foreach ($students as $rowIndex => $s): ?>
                        <tr class="hover:bg-indigo-50/40 transition group" data-mssv="<?= e($s['MSSV']) ?>">
                            <td class="py-2 px-3 text-center text-gray-400 font-normal"><?= $stt++ ?></td>
                            <td class="py-2 px-3 font-bold text-gray-900"><?= e($s['MSSV']) ?></td>
                            <td class="py-2 px-3 font-semibold text-gray-800"><?= e($s['HoTen']) ?></td>
                            
                            <!-- Điểm Chuyên cần -->
                            <td class="py-1.5 px-2 text-center">
                                <input type="text" inputmode="decimal"
                                       name="grades[<?= e($s['MSSV']) ?>][cc]" 
                                       value="<?= number_format($s['DiemCC'], 1) ?>" 
                                       onfocus="this.select()"
                                       oninput="updateRowTotal(this)"
                                       data-row="<?= $rowIndex ?>" data-col="0"
                                       class="score-input w-16 text-center py-1 border border-gray-300 rounded text-xs font-bold text-gray-900 focus:border-indigo-800 focus:ring-2 focus:ring-indigo-800/30 outline-none transition">
                            </td>

                            <!-- Điểm Giữa kỳ -->
                            <td class="py-1.5 px-2 text-center">
                                <input type="text" inputmode="decimal"
                                       name="grades[<?= e($s['MSSV']) ?>][gk]" 
                                       value="<?= number_format($s['DiemGK'], 1) ?>" 
                                       onfocus="this.select()"
                                       oninput="updateRowTotal(this)"
                                       data-row="<?= $rowIndex ?>" data-col="1"
                                       class="score-input w-16 text-center py-1 border border-gray-300 rounded text-xs font-bold text-gray-900 focus:border-indigo-800 focus:ring-2 focus:ring-indigo-800/30 outline-none transition">
                            </td>

                            <!-- Điểm Cuối kỳ -->
                            <td class="py-1.5 px-2 text-center">
                                <input type="text" inputmode="decimal"
                                       name="grades[<?= e($s['MSSV']) ?>][ck]" 
                                       value="<?= number_format($s['DiemCK'], 1) ?>" 
                                       onfocus="this.select()"
                                       oninput="updateRowTotal(this)"
                                       data-row="<?= $rowIndex ?>" data-col="2"
                                       class="score-input w-16 text-center py-1 border border-gray-300 rounded text-xs font-bold text-gray-900 focus:border-indigo-800 focus:ring-2 focus:ring-indigo-800/30 outline-none transition">
                            </td>

                            <!-- Điểm Tổng kết -->
                            <td class="py-2 px-3 text-center font-black text-xs row-total <?= $s['TongKet'] < 5.0 ? 'text-red-600' : 'text-gray-900' ?>">
                                <?= number_format($s['TongKet'], 1) ?>
                            </td>

                            <!-- Xếp loại Badge -->
                            <td class="py-2 px-3 text-center row-badge">
                                <?= renderBadgeXepLoai($s['XepLoai']) ?>
                            </td>

                            <!-- Cột Thao tác SỬA & XÓA (Full CRUD) -->
                            <td class="py-2 px-3 text-center">
                                <div class="flex items-center justify-center space-x-1.5">
                                    <!-- Nút SỬA -->
                                    <button type="button" 
                                            onclick="openEditModal('<?= e($s['MSSV']) ?>', '<?= e(addslashes($s['HoTen'])) ?>', '<?= $s['DiemCC'] ?>', '<?= $s['DiemGK'] ?>', '<?= $s['DiemCK'] ?>')" 
                                            class="p-1.5 bg-amber-50 border border-amber-300 text-amber-800 hover:bg-amber-100 rounded shadow-sm transition" title="Sửa thông tin & điểm">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>

                                    <!-- Nút XÓA (Thực hiện bằng POST qua JS form độc lập) -->
                                    <button type="button" 
                                            onclick="confirmDeleteStudent('<?= e($s['MSSV']) ?>', '<?= e(addslashes($s['HoTen'])) ?>')" 
                                            class="p-1.5 bg-rose-50 border border-rose-300 text-rose-700 hover:bg-rose-100 rounded shadow-sm transition cursor-pointer" title="Xóa học viên">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Thanh Phân Trang -->
    <div class="px-4 py-3 bg-white border-t border-gray-200 flex flex-col sm:flex-row items-center justify-between text-xs text-gray-500 gap-2">
        <p>Hiển thị <span class="font-bold text-gray-700"><?= count($students) > 0 ? $data['start_index'] : 0 ?> - <?= $data['start_index'] + count($students) - 1 ?></span> trong số <span class="font-bold text-gray-700"><?= $data['total_records'] ?></span> học viên của lớp</p>
        
        <?php if ($data['total_pages'] > 1): ?>
            <div class="flex items-center space-x-1">
                <!-- Nút Trang trước -->
                <?php if ($data['current_page'] > 1): ?>
                    <a href="index.php?ma_lop=<?= urlencode($maLop) ?>&page=<?= $data['current_page'] - 1 ?>&search=<?= urlencode($keyword) ?>" 
                       class="w-7 h-7 flex items-center justify-center rounded border border-gray-300 text-gray-600 hover:bg-indigo-50 hover:text-indigo-900 font-semibold transition">
                        <i class="fa-solid fa-chevron-left text-[10px]"></i>
                    </a>
                <?php else: ?>
                    <span class="w-7 h-7 flex items-center justify-center rounded border border-gray-200 text-gray-300 cursor-not-allowed">
                        <i class="fa-solid fa-chevron-left text-[10px]"></i>
                    </span>
                <?php endif; ?>

                <!-- Các nút trang 1, 2, 3, 4... -->
                <?php for ($p = 1; $p <= $data['total_pages']; $p++): ?>
                    <?php if ($p == $data['current_page']): ?>
                        <span class="w-7 h-7 flex items-center justify-center rounded bg-indigo-900 text-white font-bold shadow-sm text-xs"><?= $p ?></span>
                    <?php else: ?>
                        <a href="index.php?ma_lop=<?= urlencode($maLop) ?>&page=<?= $p ?>&search=<?= urlencode($keyword) ?>" 
                           class="w-7 h-7 flex items-center justify-center rounded border border-gray-300 text-gray-700 hover:bg-indigo-50 hover:text-indigo-900 font-semibold transition text-xs">
                            <?= $p ?>
                        </a>
                    <?php endif; ?>
                <?php endfor; ?>

                <!-- Nút Trang sau -->
                <?php if ($data['current_page'] < $data['total_pages']): ?>
                    <a href="index.php?ma_lop=<?= urlencode($maLop) ?>&page=<?= $data['current_page'] + 1 ?>&search=<?= urlencode($keyword) ?>" 
                       class="w-7 h-7 flex items-center justify-center rounded border border-gray-300 text-gray-600 hover:bg-indigo-50 hover:text-indigo-900 font-semibold transition">
                        <i class="fa-solid fa-chevron-right text-[10px]"></i>
                    </a>
                <?php else: ?>
                    <span class="w-7 h-7 flex items-center justify-center rounded border border-gray-200 text-gray-300 cursor-not-allowed">
                        <i class="fa-solid fa-chevron-right text-[10px]"></i>
                    </span>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</form>

<!-- Form Xóa Học Viên Dùng Phương Thức POST (Độc lập, không lồng trong gradeForm) -->
<form id="deleteStudentForm" method="POST" action="actions/delete_student.php" class="hidden">
    <input type="hidden" name="mssv" id="delete_mssv" value="">
    <input type="hidden" name="ma_lop" value="<?= e($maLop) ?>">
</form>

<!-- MODAL THAY ĐỔI LỚP HỌC -->
<div id="selectClassModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-gray-200 transform transition-all">
        <div class="flex items-center justify-between border-b pb-3">
            <h3 class="text-sm font-bold text-indigo-900 flex items-center gap-2">
                <i class="fa-solid fa-chalkboard-user text-indigo-800 text-base"></i>
                <span>Danh sách Lớp học Giảng dạy</span>
            </h3>
            <button onclick="closeClassModal()" type="button" class="text-gray-400 hover:text-gray-600 text-lg">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div class="mt-4 space-y-2.5 max-h-[400px] overflow-y-auto pr-1">
            <?php foreach ($classList as $c): ?>
                <?php $isCurrent = ($c['MaLop'] === $maLop); ?>
                <a href="index.php?ma_lop=<?= urlencode($c['MaLop']) ?>" 
                   class="block p-3.5 rounded-xl border transition flex items-center justify-between group <?= $isCurrent ? 'border-indigo-900 bg-indigo-50/60 ring-2 ring-indigo-900/20' : 'border-gray-200 hover:border-indigo-400 hover:bg-gray-50' ?>">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-lg flex items-center justify-center font-bold text-xs <?= $isCurrent ? 'bg-indigo-900 text-white' : 'bg-gray-100 text-gray-600 group-hover:bg-indigo-100 group-hover:text-indigo-900' ?>">
                            <?= e($c['MaLop']) ?>
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-gray-900 group-hover:text-indigo-900"><?= e($c['TenMonHoc']) ?></h4>
                            <p class="text-[11px] text-gray-500 mt-0.5">
                                <span class="font-medium text-gray-700">Sĩ số: <?= $c['SoHocVien'] ?> học viên</span> • Học kỳ: <?= e($c['HocKy']) ?> - <?= e($c['NamHoc']) ?>
                            </p>
                        </div>
                    </div>

                    <div>
                        <?php if ($isCurrent): ?>
                            <span class="px-2.5 py-1 bg-indigo-900 text-white text-[10px] font-bold rounded-full flex items-center gap-1 shadow-sm">
                                <i class="fa-solid fa-circle-check"></i> Đang chọn
                            </span>
                        <?php else: ?>
                            <span class="px-2.5 py-1 bg-white border border-gray-300 text-gray-700 text-[10px] font-semibold rounded-lg group-hover:bg-indigo-900 group-hover:text-white group-hover:border-indigo-900 transition">
                                Chọn lớp này
                            </span>
                        <?php endif; ?>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>

        <div class="mt-4 pt-3 border-t text-right">
            <button type="button" onclick="closeClassModal()" class="px-4 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded-lg transition">Đóng</button>
        </div>
    </div>
</div>

<!-- MODAL: Nhập điểm học viên mới (CREATE) -->
<div id="addStudentModal" class="<?= $openModal ? '' : 'hidden' ?> fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-3">
    <div class="bg-white rounded-2xl max-w-md w-full p-5 shadow-2xl border border-gray-200 transform transition-all">
        <div class="flex items-center justify-between border-b pb-2.5">
            <h3 class="text-sm font-bold text-indigo-900 flex items-center gap-2">
                <i class="fa-solid fa-user-plus text-indigo-800"></i>
                <span>Nhập điểm học viên mới</span>
            </h3>
            <button onclick="closeAddModal()" type="button" class="text-gray-400 hover:text-gray-600 text-base">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form method="POST" action="actions/add_student_grade.php" class="mt-3 space-y-3 text-xs" onsubmit="return validateMssvAddForm(this)">
            <input type="hidden" name="ma_lop" value="<?= e($maLop) ?>">

            <div>
                <label class="block text-[11px] font-bold text-gray-700 mb-1">Mã số sinh viên (MSSV) <span class="text-red-500">*</span></label>
                <input type="text" name="mssv" id="modal_mssv" list="mssv_list" required pattern="[0-9]{1,10}" maxlength="10" inputmode="numeric"
                       oninput="onMssvChange(this)" onchange="onMssvChange(this)"
                       value="<?= e($formData['mssv'] ?? '') ?>" 
                       class="w-full px-3 py-1.5 border rounded-lg text-xs font-bold text-gray-800 bg-white outline-none focus:ring-2 focus:ring-indigo-800 <?= isset($formErrors['mssv']) ? 'border-red-500 bg-red-50' : 'border-gray-300' ?>">
                <datalist id="mssv_list">
                    <?php foreach ($allStudents as $st): ?>
                        <option value="<?= e($st['MSSV']) ?>"></option>
                    <?php endforeach; ?>
                </datalist>
                <?php if (isset($formErrors['mssv'])): ?>
                    <p class="text-red-500 text-[10px] mt-0.5 font-medium"><?= e($formErrors['mssv']) ?></p>
                <?php endif; ?>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-gray-700 mb-1">Họ và tên học viên <span class="text-red-500">*</span></label>
                <input type="text" name="ho_ten" id="modal_ho_ten" required value="<?= e($formData['ho_ten'] ?? '') ?>" 
                       class="w-full px-3 py-1.5 border rounded-lg text-xs font-semibold outline-none focus:ring-2 focus:ring-indigo-800 bg-gray-50 <?= isset($formErrors['ho_ten']) ? 'border-red-500 bg-red-50' : 'border-gray-300' ?>">
                <?php if (isset($formErrors['ho_ten'])): ?>
                    <p class="text-red-500 text-[10px] mt-0.5 font-medium"><?= e($formErrors['ho_ten']) ?></p>
                <?php endif; ?>
            </div>

            <div class="grid grid-cols-3 gap-2">
                <div>
                    <label class="block text-[10px] font-bold text-gray-700 mb-1">Chuyên cần (10%)</label>
                    <input type="text" inputmode="decimal" id="modal_diem_cc" name="diem_cc" 
                           onfocus="this.select()" oninput="calculateModalGrade()" 
                           value="<?= e($formData['diem_cc'] ?? '') ?>"
                           class="w-full px-2 py-1.5 border rounded-lg text-xs text-center font-bold text-gray-900 outline-none focus:ring-2 focus:ring-indigo-800 <?= isset($formErrors['diem_cc']) ? 'border-red-500 bg-red-50' : 'border-gray-300' ?>">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-gray-700 mb-1">Giữa kỳ (30%)</label>
                    <input type="text" inputmode="decimal" id="modal_diem_gk" name="diem_gk" 
                           onfocus="this.select()" oninput="calculateModalGrade()" 
                           value="<?= e($formData['diem_gk'] ?? '') ?>"
                           class="w-full px-2 py-1.5 border rounded-lg text-xs text-center font-bold text-gray-900 outline-none focus:ring-2 focus:ring-indigo-800 <?= isset($formErrors['diem_gk']) ? 'border-red-500 bg-red-50' : 'border-gray-300' ?>">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-gray-700 mb-1">Cuối kỳ (60%)</label>
                    <input type="text" inputmode="decimal" id="modal_diem_ck" name="diem_ck" 
                           onfocus="this.select()" oninput="calculateModalGrade()" 
                           value="<?= e($formData['diem_ck'] ?? '') ?>"
                           class="w-full px-2 py-1.5 border rounded-lg text-xs text-center font-bold text-gray-900 outline-none focus:ring-2 focus:ring-indigo-800 <?= isset($formErrors['diem_ck']) ? 'border-red-500 bg-red-50' : 'border-gray-300' ?>">
                </div>
            </div>

            <div class="flex items-center justify-end space-x-2 pt-2.5 border-t">
                <button type="button" onclick="closeAddModal()" class="px-3.5 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded-lg transition">Hủy bỏ</button>
                <button type="submit" class="px-4 py-1.5 bg-indigo-900 hover:bg-indigo-950 text-white text-xs font-semibold rounded-lg shadow transition">Lưu học viên</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL: Sửa Thông Tin & Điểm Học Viên (UPDATE) -->
<div id="editStudentModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-3">
    <div class="bg-white rounded-2xl max-w-md w-full p-5 shadow-2xl border border-gray-200 transform transition-all">
        <div class="flex items-center justify-between border-b pb-2.5">
            <h3 class="text-sm font-bold text-amber-800 flex items-center gap-2">
                <i class="fa-solid fa-pen-to-square"></i>
                <span>Sửa Điểm Học Viên</span>
            </h3>
            <button onclick="closeEditModal()" type="button" class="text-gray-400 hover:text-gray-600 text-base">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form method="POST" action="actions/edit_student.php" class="mt-3 space-y-3 text-xs">
            <input type="hidden" name="ma_lop" value="<?= e($maLop) ?>">

            <div>
                <label class="block text-[11px] font-bold text-gray-700 mb-1">Mã số sinh viên (MSSV)</label>
                <input type="text" name="mssv" id="edit_mssv" readonly class="w-full px-3 py-1.5 bg-gray-100 border border-gray-300 rounded-lg text-xs font-bold text-gray-700 outline-none">
            </div>

            <div>
                <label class="block text-[11px] font-bold text-gray-700 mb-1">Họ và tên học viên</label>
                <input type="text" name="ho_ten" id="edit_ho_ten" readonly class="w-full px-3 py-1.5 bg-gray-100 border border-gray-300 rounded-lg text-xs font-semibold text-gray-700 outline-none">
            </div>

            <div class="grid grid-cols-3 gap-2">
                <div>
                    <label class="block text-[10px] font-bold text-gray-700 mb-1">Chuyên cần (10%)</label>
                    <input type="text" inputmode="decimal" name="diem_cc" id="edit_diem_cc" onfocus="this.select()" class="w-full px-2 py-1.5 border border-gray-300 rounded-lg text-xs text-center font-bold text-gray-900 outline-none focus:ring-2 focus:ring-amber-600">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-gray-700 mb-1">Giữa kỳ (30%)</label>
                    <input type="text" inputmode="decimal" name="diem_gk" id="edit_diem_gk" onfocus="this.select()" class="w-full px-2 py-1.5 border border-gray-300 rounded-lg text-xs text-center font-bold text-gray-900 outline-none focus:ring-2 focus:ring-amber-600">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-gray-700 mb-1">Cuối kỳ (60%)</label>
                    <input type="text" inputmode="decimal" name="diem_ck" id="edit_diem_ck" onfocus="this.select()" class="w-full px-2 py-1.5 border border-gray-300 rounded-lg text-xs text-center font-bold text-gray-900 outline-none focus:ring-2 focus:ring-amber-600">
                </div>
            </div>

            <div class="flex items-center justify-end space-x-2 pt-2.5 border-t">
                <button type="button" onclick="closeEditModal()" class="px-3.5 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded-lg transition">Hủy bỏ</button>
                <button type="submit" class="px-4 py-1.5 bg-amber-700 hover:bg-amber-800 text-white text-xs font-semibold rounded-lg shadow transition">Cập nhật</button>
            </div>
        </form>
    </div>
</div>

<!-- Client-side Script -->
<script>
    const studentMap = <?= json_encode(array_column($allStudents, 'HoTen', 'MSSV'), JSON_UNESCAPED_UNICODE) ?>;

    function onMssvChange(select) {
        const selectedMssv = select.value;
        const nameInput = document.getElementById('modal_ho_ten');
        if (studentMap[selectedMssv]) {
            nameInput.value = studentMap[selectedMssv];
        } else {
            nameInput.value = '';
        }
    }

    function openClassModal() { document.getElementById('selectClassModal').classList.remove('hidden'); }
    function closeClassModal() { document.getElementById('selectClassModal').classList.add('hidden'); }
    function openAddModal() { document.getElementById('addStudentModal').classList.remove('hidden'); }
    function closeAddModal() { document.getElementById('addStudentModal').classList.add('hidden'); }

    function openEditModal(mssv, hoTen, cc, gk, ck) {
        document.getElementById('edit_mssv').value = mssv;
        document.getElementById('edit_ho_ten').value = hoTen;
        document.getElementById('edit_diem_cc').value = '';
        document.getElementById('edit_diem_gk').value = '';
        document.getElementById('edit_diem_ck').value = '';
        document.getElementById('editStudentModal').classList.remove('hidden');
    }
    function closeEditModal() { document.getElementById('editStudentModal').classList.add('hidden'); }

    function confirmDeleteStudent(mssv, hoTen) {
        if (confirm('Bạn có chắc chắn muốn XÓA học viên ' + hoTen + ' (MSSV: ' + mssv + ') khỏi lớp này không?')) {
            document.getElementById('delete_mssv').value = mssv;
            document.getElementById('deleteStudentForm').submit();
        }
    }

    function validateMssvAddForm(form) {
        const mssv = form.mssv.value.trim();
        const regex = /^[0-9]{1,10}$/;
        if (!regex.test(mssv)) {
            alert("LỖI NHẬP LIỆU: Mã số sinh viên (MSSV) chỉ gồm các chữ số (0-9) từ 1 đến 10 chữ số!");
            form.mssv.focus();
            return false;
        }
        return true;
    }

    function updateRowTotal(input) {
        const tr = input.closest('tr');
        const ccInput = tr.querySelector('input[name*="[cc]"]');
        const gkInput = tr.querySelector('input[name*="[gk]"]');
        const ckInput = tr.querySelector('input[name*="[ck]"]');

        const cc = parseFloat(ccInput.value.replace(',', '.')) || 0;
        const gk = parseFloat(gkInput.value.replace(',', '.')) || 0;
        const ck = parseFloat(ckInput.value.replace(',', '.')) || 0;

        const total = (cc * 0.10) + (gk * 0.30) + (ck * 0.60);
        const roundedTotal = Math.round(total * 10) / 10;

        const totalCell = tr.querySelector('.row-total');
        totalCell.textContent = roundedTotal.toFixed(1);
        if (roundedTotal < 5.0) {
            totalCell.classList.add('text-red-600');
            totalCell.classList.remove('text-gray-900');
        } else {
            totalCell.classList.remove('text-red-600');
            totalCell.classList.add('text-gray-900');
        }

        const badgeCell = tr.querySelector('.row-badge');
        let badgeHtml = '';
        if (roundedTotal >= 9.0) {
            badgeHtml = '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-800 border border-emerald-300">Xuất sắc</span>';
        } else if (roundedTotal >= 8.0) {
            badgeHtml = '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-green-100 text-green-700 border border-green-300">Giỏi</span>';
        } else if (roundedTotal >= 7.0) {
            badgeHtml = '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-amber-100 text-amber-800 border border-amber-300">Khá</span>';
        } else if (roundedTotal >= 5.0) {
            badgeHtml = '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-700 border border-slate-300">Trung bình</span>';
        } else if (roundedTotal >= 3.5) {
            badgeHtml = '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-rose-100 text-rose-600 border border-rose-200">Yếu</span>';
        } else {
            badgeHtml = '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-red-200 text-red-800 border border-red-400">Kém</span>';
        }
        badgeCell.innerHTML = badgeHtml;
    }

    document.addEventListener('keydown', function(e) {
        if (!e.target.classList.contains('score-input')) return;
        const input = e.target;
        const row = parseInt(input.dataset.row);
        const col = parseInt(input.dataset.col);
        let targetRow = row, targetCol = col;

        if (e.key === 'ArrowUp') { targetRow = row - 1; e.preventDefault(); }
        else if (e.key === 'ArrowDown' || e.key === 'Enter') { targetRow = row + 1; e.preventDefault(); }
        else if (e.key === 'ArrowRight' && input.selectionEnd === input.value.length) { targetCol = col + 1; }
        else if (e.key === 'ArrowLeft' && input.selectionStart === 0) { targetCol = col - 1; }

        if (targetRow !== row || targetCol !== col) {
            const nextInput = document.querySelector(`.score-input[data-row="${targetRow}"][data-col="${targetCol}"]`);
            if (nextInput) { nextInput.focus(); nextInput.select(); }
        }
    });
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
