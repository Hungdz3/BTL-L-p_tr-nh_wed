<?php
/**
 * Trang Quản lý Lớp học & Nhập điểm cho Giảng viên
 * HNDA Portal System
 */
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/db.php';

$maLopHp = $_GET['ma_lop_hp'] ?? 'LHP001';
$hoTenGV = $_SESSION['ho_ten'] ?? 'TS. Nguyễn Minh Châu';
?>

<div class="main-content-container" style="max-width: 1240px; margin: 24px auto; padding: 0 20px;">

    <!-- Banner Chào Giảng viên -->
    <div style="background: #1E3A8A; color: white; padding: 20px 28px; border-radius: 12px; margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 4px 14px rgba(30, 58, 138, 0.15);">
        <div style="display: flex; align-items: center; gap: 16px;">
            <div style="width: 48px; height: 48px; background: rgba(255,255,255,0.18); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 22px; border: 2px solid rgba(255,255,255,0.3);">
                👨‍🏫
            </div>
            <div>
                <h2 style="margin: 0; font-size: 18px; font-weight: 700; color: #ffffff;">Xin chào, <?= e($hoTenGV) ?></h2>
                <p style="margin: 4px 0 0 0; font-size: 13px; color: #93c5fd;">Bộ môn: Công nghệ phần mềm • Khoa Công nghệ thông tin</p>
            </div>
        </div>
    </div>

    <!-- Thẻ Chọn Lớp Học Đang Chọn -->
    <div style="background: white; border-radius: 12px; padding: 16px 20px; margin-bottom: 20px; border: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 2px 6px rgba(0,0,0,0.02);">
        <div style="display: flex; align-items: center; gap: 12px;">
            <div style="background: #e0f2fe; color: #0284c7; width: 36px; height: 36px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 18px;">📖</div>
            <div>
                <span style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px;">LỚP HỌC ĐANG CHỌN</span>
                <h3 id="lbl-ten-lop" style="margin: 2px 0 0 0; font-size: 15px; font-weight: 800; color: #0f172a;">
                    CS101 - Lập trình Python - HK1 2025
                </h3>
            </div>
        </div>
        <div>
            <button onclick="openClassModal()" type="button" style="background: #ffffff; color: #334155; font-size: 13px; font-weight: 600; border: 1px solid #cbd5e1; padding: 8px 16px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; gap: 6px;">
                Thay đổi lớp học <span style="font-size: 10px;">▼</span>
            </button>
        </div>
    </div>

    <!-- 4 Thẻ Thống Kê Chỉ Số (Pixel-Perfect Image 2) -->
    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 20px;">
        <!-- Card 1: SĨ SỐ -->
        <div style="background: white; border-radius: 12px; padding: 20px; border: 1px solid #e2e8f0; box-shadow: 0 2px 8px rgba(0,0,0,0.03); display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <span style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px;">SĨ SỐ</span>
                <h3 id="stat-si-so" style="margin: 8px 0 2px 0; font-size: 24px; font-weight: 800; color: #0f172a;">40 học viên</h3>
                <p style="margin: 0; font-size: 11px; color: #64748b;">Lớp lý thuyết chính thức</p>
            </div>
            <div style="background: #f1f5f9; color: #475569; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 14px;">👥</div>
        </div>

        <!-- Card 2: ĐIỂM TB LỚP -->
        <div style="background: white; border-radius: 12px; padding: 20px; border: 1px solid #e2e8f0; box-shadow: 0 2px 8px rgba(0,0,0,0.03); display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <span style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px;">ĐIỂM TB LỚP</span>
                <h3 id="stat-diem-tb" style="margin: 8px 0 2px 0; font-size: 24px; font-weight: 800; color: #0f172a;">7.2</h3>
                <p style="margin: 0; font-size: 11px; color: #64748b;">Hệ điểm 10</p>
            </div>
            <div style="background: #1E3A8A; color: white; width: 32px; height: 32px; border-radius: 6px; display: flex; align-items: center; justify-content: center; font-size: 14px;">📈</div>
        </div>

        <!-- Card 3: TỶ LỆ ĐẠT -->
        <div style="background: white; border-radius: 12px; padding: 20px; border: 1px solid #e2e8f0; box-shadow: 0 2px 8px rgba(0,0,0,0.03); display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <span style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px;">TỶ LỆ ĐẠT</span>
                <h3 id="stat-ty-le-dat" style="margin: 8px 0 2px 0; font-size: 24px; font-weight: 800; color: #0f172a;">92.5%</h3>
                <p id="stat-so-dat" style="margin: 0; font-size: 11px; color: #64748b;">37 / 40 học viên đạt</p>
            </div>
            <div style="background: #f1f5f9; color: #475569; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 14px;">✓</div>
        </div>

        <!-- Card 4: ĐIỂM CAO NHẤT -->
        <div style="background: white; border-radius: 12px; padding: 20px; border: 1px solid #e2e8f0; box-shadow: 0 2px 8px rgba(0,0,0,0.03); display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <span style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px;">ĐIỂM CAO NHẤT</span>
                <h3 id="stat-diem-max" style="margin: 8px 0 2px 0; font-size: 24px; font-weight: 800; color: #0f172a;">9.6</h3>
                <p id="stat-top-sv" style="margin: 0; font-size: 11px; color: #64748b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 140px;">Bùi Minh Tuấn (21120593)</p>
            </div>
            <div style="background: #f1f5f9; color: #475569; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 14px;">🏅</div>
        </div>
    </div>

    <!-- Thanh Công Cụ Bar -->
    <div style="background: white; border-radius: 12px; padding: 16px 20px; margin-bottom: 20px; border: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
        <div style="display: flex; items-center; gap: 10px; flex: 1; max-width: 320px;">
            <input type="text" id="search-student" placeholder="🔍 Tìm kiếm MSSV, tên..." style="width: 100%; padding: 8px 16px; border: 1px solid #cbd5e1; border-radius: 20px; font-size: 13px; outline: none;" onkeyup="if(event.key==='Enter') loadDiemData()">
        </div>
        <div style="display: flex; align-items: center; gap: 10px;">
            <button onclick="openAddModal()" type="button" style="margin: 0; background: white; color: #1E3A8A; border: 1px solid #1E3A8A; padding: 8px 16px; font-weight: 600; border-radius: 6px; cursor: pointer; font-size: 13px;">
                + Nhập điểm
            </button>
            <button onclick="exportCSV()" type="button" style="background: white; color: #1E3A8A; border: 1px solid #1E3A8A; padding: 8px 16px; font-weight: 600; border-radius: 6px; cursor: pointer; font-size: 13px;">
                📊 Xuất Excel
            </button>
            <button onclick="saveAllGrades()" type="button" style="padding: 8px 20px; font-weight: 700; border-radius: 6px; background: #1E3A8A; color: white; border: none; cursor: pointer; font-size: 13px; display: inline-flex; align-items: center; justify-content: center;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 6px;"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                Lưu điểm
            </button>
        </div>
    </div>

    <!-- Bảng Điểm Sinh Viên -->
    <div style="background: white; margin-bottom: 20px; overflow-x: auto; padding: 0; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 2px 8px rgba(0,0,0,0.03);">
        <table style="width: 100%; border-collapse: collapse;" id="gradeTable">
            <thead>
                <tr style="background: #ffffff; border-bottom: 2px solid #f1f5f9; font-size: 11px; color: #475569; text-transform: uppercase;">
                    <th style="padding: 14px 10px; text-align: center; width: 45px;">STT</th>
                    <th style="padding: 14px 12px; width: 110px;">MSSV</th>
                    <th style="padding: 14px 12px;">Họ và tên</th>
                    <th style="padding: 14px 10px; text-align: center; width: 130px;">Chuyên cần (10%)</th>
                    <th style="padding: 14px 10px; text-align: center; width: 130px;">Giữa kỳ (30%)</th>
                    <th style="padding: 14px 10px; text-align: center; width: 130px;">Cuối kỳ (60%)</th>
                    <th style="padding: 14px 10px; text-align: center; width: 100px; font-weight: 800; color: #1e293b;">Tổng kết</th>
                    <th style="padding: 14px 10px; text-align: center; width: 110px;">Xếp loại</th>
                    <th style="padding: 14px 10px; text-align: center; width: 60px;">Xóa</th>
                </tr>
            </thead>
            <tbody id="grade-tbody" style="font-size: 13px;">
                <tr><td colspan="9" style="text-align: center; padding: 30px; color: #94a3b8;">Đang tải danh sách học viên...</td></tr>
            </tbody>
        </table>
    </div>

    <!-- Thanh Phân Trang -->
    <div style="display: flex; align-items: center; justify-content: space-between; font-size: 13px; color: #64748b; padding: 10px 0;">
        <div id="pagination-info">Hiển thị 0 trong số 0 học viên của lớp</div>
        <div class="pagination-buttons" id="pagination-btns" style="display: flex; gap: 4px;"></div>
    </div>
</div>

<!-- Modal Chọn Lớp Học -->
<div class="custom-modal-admin" id="modal-class" style="display: none; max-width: 550px; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.15); z-index: 10001; width: 90%;">
    <div class="modal-header-admin" style="padding: 16px 20px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; font-weight: 700; color: #1E3A8A; display: flex; justify-content: space-between; align-items: center;">
        <span>Chọn Lớp Học Phần Giảng Dạy</span>
        <button class="modal-close-admin" onclick="closeClassModal()" style="background: none; border: none; font-size: 18px; cursor: pointer; color: #64748b;">×</button>
    </div>
    <div class="modal-body-admin" style="padding: 20px; max-height: 400px; overflow-y: auto;">
        <div id="class-list-container" style="display: flex; flex-direction: column; gap: 8px;">
            <!-- Render via JS -->
        </div>
    </div>
    <div class="modal-footer-admin" style="padding: 12px 20px; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end;">
        <button type="button" class="btn-admin-secondary" onclick="closeClassModal()" style="padding: 8px 16px; background: #cbd5e1; border: none; border-radius: 6px; font-weight: 600; cursor: pointer;">Đóng</button>
    </div>
</div>

<!-- Modal Thêm/Sửa Học Viên & Điểm -->
<div class="custom-modal-admin" id="modal-add-student" style="display: none; max-width: 450px; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.15); z-index: 10001; width: 90%;">
    <div class="modal-header-admin" style="padding: 16px 20px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; font-weight: 700; color: #1E3A8A; display: flex; justify-content: space-between; align-items: center;">
        <span id="modal-student-title">Nhập điểm học viên mới</span>
        <button class="modal-close-admin" onclick="closeAddModal()" style="background: none; border: none; font-size: 18px; cursor: pointer; color: #64748b;">×</button>
    </div>
    <form id="form-student-grade" onsubmit="submitStudentForm(event)">
        <div class="modal-body-admin" style="padding: 20px;">
            <div class="form-group-admin" style="margin-bottom: 15px;">
                <label style="font-size: 13px; font-weight: bold; margin-bottom: 5px; display: block; color: #334155;">Mã số sinh viên (MSSV) <span style="color:#e53e3e;">*</span></label>
                <select id="m-mssv" required onchange="onMssvInputChange(this.value)" style="width: 100%; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 8px; font-weight: 700; font-size: 14px; color: #1e293b; background: #ffffff; cursor: pointer; outline: none; appearance: none; -webkit-appearance: none; background-image: url('data:image/svg+xml;charset=US-ASCII,<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"14\" height=\"14\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"%23334155\" stroke-width=\"2.5\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><polyline points=\"6 9 12 15 18 9\"></polyline></svg>'); background-repeat: no-repeat; background-position: right 14px center; padding-right: 36px;">
                    <option value="">-- Chọn mã số sinh viên --</option>
                </select>
            </div>
            <div class="form-group-admin" style="margin-bottom: 15px;">
                <label style="font-size: 13px; font-weight: bold; margin-bottom: 5px; display: block; color: #334155;">Họ và tên học viên <span style="color:#e53e3e;">*</span></label>
                <input type="text" id="m-ho-ten" required readonly placeholder="Họ tên tự động hiển thị theo MSSV" style="width: 100%; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 8px; background: #f8fafc; font-weight: 700; color: #1e293b; font-size: 14px;">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px;">
                <div class="form-group-admin">
                    <label style="font-size: 12px; font-weight: bold; margin-bottom: 5px; display: block; color: #334155;">CC (10%)</label>
                    <input type="number" step="0.1" min="0" max="10" id="m-cc" value="10" style="width: 100%; padding: 8px; text-align: center; border: 1px solid #cbd5e1; border-radius: 6px; font-weight: 600;">
                </div>
                <div class="form-group-admin">
                    <label style="font-size: 12px; font-weight: bold; margin-bottom: 5px; display: block; color: #334155;">GK (30%)</label>
                    <input type="number" step="0.1" min="0" max="10" id="m-gk" value="8" style="width: 100%; padding: 8px; text-align: center; border: 1px solid #cbd5e1; border-radius: 6px; font-weight: 600;">
                </div>
                <div class="form-group-admin">
                    <label style="font-size: 12px; font-weight: bold; margin-bottom: 5px; display: block; color: #334155;">CK (60%)</label>
                    <input type="number" step="0.1" min="0" max="10" id="m-ck" value="8" style="width: 100%; padding: 8px; text-align: center; border: 1px solid #cbd5e1; border-radius: 6px; font-weight: 600;">
                </div>
            </div>
        </div>
        <div class="modal-footer-admin" style="padding: 12px 20px; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end; gap: 10px;">
            <button type="button" class="btn-admin-secondary" onclick="closeAddModal()" style="padding: 8px 16px; background: #cbd5e1; border: none; border-radius: 6px; font-weight: 600; cursor: pointer;">Hủy</button>
            <button type="submit" class="btn-admin-primary" style="padding: 8px 16px; background: #1E3A8A; color: white; border: none; border-radius: 6px; font-weight: 600; cursor: pointer;">Lưu học viên</button>
        </div>
    </form>
</div>


<div class="modal-backdrop" id="modal-backdrop-diem" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.4); z-index: 10000;" onclick="closeClassModal(); closeAddModal();"></div>

<script>
    let currentMaLop = "<?= e($maLopHp) ?>";
    let studentItems = [];
    let currentPage = 1;
    const perPage = 10;

    function getBadgeHtml(xl) {
        switch (xl) {
            case 'Xuất sắc':
                return `<span style="background: #ccfbf1; color: #0f766e; padding: 3px 10px; border-radius: 12px; font-weight: 700; font-size: 11px; display: inline-block;">Xuất sắc</span>`;
            case 'Giỏi':
                return `<span style="background: #dcfce7; color: #166534; padding: 3px 10px; border-radius: 12px; font-weight: 700; font-size: 11px; display: inline-block;">Giỏi</span>`;
            case 'Khá':
                return `<span style="background: #fef3c7; color: #92400e; padding: 3px 10px; border-radius: 12px; font-weight: 700; font-size: 11px; display: inline-block;">Khá</span>`;
            case 'Trung bình':
                return `<span style="background: #f1f5f9; color: #475569; padding: 3px 10px; border-radius: 12px; font-weight: 700; font-size: 11px; display: inline-block;">Trung bình</span>`;
            case 'Yếu':
                return `<span style="background: #fee2e2; color: #991b1b; padding: 3px 10px; border-radius: 12px; font-weight: 700; font-size: 11px; display: inline-block;">Yếu</span>`;
            case 'Kém':
                return `<span style="background: #fee2e2; color: #991b1b; padding: 3px 10px; border-radius: 12px; font-weight: 700; font-size: 11px; display: inline-block;">Kém</span>`;
            default:
                return `<span style="background: #f1f5f9; color: #64748b; padding: 3px 10px; border-radius: 12px; font-weight: 700; font-size: 11px; display: inline-block;">-</span>`;
        }
    }

    async function loadClassList() {
        try {
            const res = await fetch('../api/giang_vien_diem_action.php?action=get_danh_sach_lop');
            const data = await res.json();
            if (data.success && data.classes) {
                const container = document.getElementById('class-list-container');
                container.innerHTML = data.classes.map(c => `
                    <div onclick="selectClass('${c.MaLop}')" style="padding: 12px; border: 1px solid ${c.MaLop === currentMaLop ? '#1E3A8A' : '#e2e8f0'}; border-radius: 8px; cursor: pointer; background: ${c.MaLop === currentMaLop ? '#eff6ff' : '#fff'}; display: flex; align-items: center; justify-content: space-between;">
                        <div>
                            <strong style="color: #1e293b; font-size: 14px;">${c.TenMonHoc || c.MaLop}</strong>
                            <p style="margin: 2px 0 0 0; font-size: 12px; color: #64748b;">Mã LHP: ${c.MaLop} • Sĩ số: ${c.SoHocVien} SV</p>
                        </div>
                        ${c.MaLop === currentMaLop ? '<span style="color:#1E3A8A; font-weight:700; font-size:12px;">✓ Đang chọn</span>' : '<button style="padding:4px 10px; font-size:11px; background:#f1f5f9; border:1px solid #cbd5e1; border-radius:4px;">Chọn lớp</button>'}
                    </div>
                `).join('');

                const curr = data.classes.find(x => x.MaLop === currentMaLop);
                if (curr) {
                    document.getElementById('lbl-ten-lop').textContent = `${curr.MaLop} - ${curr.TenMonHoc}`;
                }
            }
        } catch(e) {
            console.error(e);
        }
    }

    async function loadDiemData() {
        const search = document.getElementById('search-student').value;
        try {
            const res = await fetch(`../api/giang_vien_diem_action.php?action=get_danh_sach_diem&ma_lop_hp=${encodeURIComponent(currentMaLop)}&search=${encodeURIComponent(search)}&page=${currentPage}&per_page=${perPage}`);
            const data = await res.json();
            if (data.success) {
                studentItems = data.items;
                renderTable(data);
                renderStats(data.stats);
            } else {
                alert(data.message || 'Lỗi khi tải bảng điểm');
            }
        } catch(e) {
            console.error(e);
        }
    }

    function renderStats(st) {
        if (!st) return;
        document.getElementById('stat-si-so').textContent = st.si_so + ' học viên';
        document.getElementById('stat-diem-tb').textContent = st.diem_tb;
        document.getElementById('stat-ty-le-dat').textContent = st.ty_le_dat + '%';
        document.getElementById('stat-so-dat').textContent = `${st.so_dat} / ${st.si_so} học viên đạt`;
        document.getElementById('stat-diem-max').textContent = st.diem_cao_nhat;
        document.getElementById('stat-top-sv').textContent = st.hoc_vien_top;
    }

    let studentMap = {}; // Lưu liên kết MSSV -> Họ tên

    async function loadStudentList() {
        let studentsList = [
            { MSSV: '2200003', HoTen: 'Đặng Gia An' },
            { MSSV: '2200004', HoTen: 'Ngô Thị Nhi' },
            { MSSV: '2300002', HoTen: 'Trần Ngọc Lan' },
            { MSSV: '224001818', HoTen: 'Đỗ Hoàng Sĩ Nguyên' },
            { MSSV: 'SV001', HoTen: 'Nguyễn Văn An' }
        ];

        try {
            const res = await fetch('../api/giang_vien_diem_action.php?action=get_sinh_vien_list');
            const data = await res.json();
            if (data.success && data.students && data.students.length > 0) {
                studentsList = data.students;
            }
        } catch(e) {
            console.error(e);
        }

        const selectEl = document.getElementById('m-mssv');
        studentMap = {};
        let optionsHtml = '<option value="">-- Chọn mã số sinh viên --</option>';
        studentsList.forEach(s => {
            studentMap[s.MSSV] = s.HoTen;
            optionsHtml += `<option value="${s.MSSV}">${s.MSSV}</option>`;
        });
        selectEl.innerHTML = optionsHtml;
    }

    function onMssvInputChange(val) {
        val = val.trim();
        const hoTenInput = document.getElementById('m-ho-ten');
        if (studentMap[val]) {
            hoTenInput.value = studentMap[val];
        } else {
            hoTenInput.value = '';
        }
    }

    function openEditModal(mssv, hoTen, cc, gk, ck) {
        document.getElementById('modal-student-title').textContent = "Cập nhật điểm học viên";
        
        const selectEl = document.getElementById('m-mssv');
        studentMap[mssv] = hoTen;
        
        // Nếu MSSV chưa có trong dropdown options thì thêm vào
        let exists = false;
        for (let i = 0; i < selectEl.options.length; i++) {
            if (selectEl.options[i].value === mssv) {
                exists = true;
                break;
            }
        }
        if (!exists) {
            const opt = document.createElement('option');
            opt.value = mssv;
            opt.textContent = mssv;
            selectEl.appendChild(opt);
        }

        selectEl.value = mssv;
        document.getElementById('m-ho-ten').value = hoTen;
        document.getElementById('m-cc').value = cc;
        document.getElementById('m-gk').value = gk;
        document.getElementById('m-ck').value = ck;
        openAddModal();
    }


    function renderTable(data) {
        const tbody = document.getElementById('grade-tbody');
        const info = document.getElementById('pagination-info');
        const btns = document.getElementById('pagination-btns');

        if (!data.items || data.items.length === 0) {
            tbody.innerHTML = `<tr><td colspan="9" style="text-align: center; padding: 25px; color: #94a3b8;">Không tìm thấy học viên nào.</td></tr>`;
            info.textContent = 'Hiển thị 0 trong số 0 học viên của lớp';
            btns.innerHTML = '';
            return;
        }

        let stt = data.start_index;
        tbody.innerHTML = data.items.map((s) => {
            const isFailing = s.TongKet < 5.0;
            const safeName = (s.HoTen || '').replace(/'/g, "\\'");
            return `
            <tr style="border-bottom: 1px solid #f1f5f9;" data-mssv="${s.MSSV}">
                <td style="padding: 12px 10px; text-align: center; color: #94a3b8;">${stt++}</td>
                <td style="padding: 12px; font-weight: 700; color: #1e293b;">${s.MSSV}</td>
                <td style="padding: 12px; font-weight: 600; color: #334155;">${s.HoTen}</td>
                
                <td style="padding: 12px 10px; text-align: center;">
                    <span style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 4px 12px; border-radius: 6px; font-weight: 700; color: #1e293b; display: inline-block; min-width: 45px;">${parseFloat(s.DiemCC).toFixed(1)}</span>
                </td>
                
                <td style="padding: 12px 10px; text-align: center;">
                    <span style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 4px 12px; border-radius: 6px; font-weight: 700; color: #1e293b; display: inline-block; min-width: 45px;">${parseFloat(s.DiemGK).toFixed(1)}</span>
                </td>

                <td style="padding: 12px 10px; text-align: center;">
                    <span style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 4px 12px; border-radius: 6px; font-weight: 700; color: #1e293b; display: inline-block; min-width: 45px;">${parseFloat(s.DiemCK).toFixed(1)}</span>
                </td>

                <td class="cell-tk" style="padding: 12px 10px; text-align: center; font-weight: 800; color: ${isFailing ? '#dc2626' : '#0f172a'}; font-size: 14px;">
                    ${parseFloat(s.TongKet).toFixed(1)}
                </td>

                <td class="cell-xl" style="padding: 12px 10px; text-align: center;">
                    ${getBadgeHtml(s.XepLoai)}
                </td>

                <td style="padding: 12px 10px; text-align: center; white-space: nowrap;">
                    <button onclick="openEditModal('${s.MSSV}', '${safeName}', ${s.DiemCC}, ${s.DiemGK}, ${s.DiemCK})" title="Cập nhật điểm" style="background: none; border: none; color: #0284c7; cursor: pointer; font-size: 15px; margin-right: 8px;">✏️</button>
                    <button onclick="deleteStudent('${s.MSSV}')" title="Xóa học viên" style="background: none; border: none; color: #94a3b8; cursor: pointer; font-size: 14px;">🗑️</button>
                </td>
            </tr>
        `;
        }).join('');

        info.textContent = `Hiển thị ${data.items.length} trong số ${data.total_records} học viên của lớp`;

        // Render page buttons (<  1  2  3  >)
        let btnsHtml = `<button onclick="gotoPage(${Math.max(1, currentPage - 1)})" style="padding: 4px 10px; border: 1px solid #cbd5e1; background: white; border-radius: 4px; cursor: pointer; font-size: 12px;">‹</button>`;
        for (let p = 1; p <= data.total_pages; p++) {
            btnsHtml += `<button onclick="gotoPage(${p})" style="padding: 4px 10px; border: 1px solid ${p === currentPage ? '#0284c7' : '#cbd5e1'}; background: ${p === currentPage ? '#0284c7' : 'white'}; color: ${p === currentPage ? 'white' : '#334155'}; border-radius: 4px; cursor: pointer; font-size: 12px; font-weight: 600;">${p}</button>`;
        }
        btnsHtml += `<button onclick="gotoPage(${Math.min(data.total_pages, currentPage + 1)})" style="padding: 4px 10px; border: 1px solid #cbd5e1; background: white; border-radius: 4px; cursor: pointer; font-size: 12px;">›</button>`;
        btns.innerHTML = btnsHtml;
    }

    function openEditModal(mssv, hoTen, cc, gk, ck) {
        document.getElementById('modal-student-title').textContent = "Cập nhật điểm học viên";
        document.getElementById('m-mssv').value = mssv;
        document.getElementById('m-ho-ten').value = hoTen;
        document.getElementById('m-cc').value = cc;
        document.getElementById('m-gk').value = gk;
        document.getElementById('m-ck').value = ck;
        openAddModal();
    }


    function calcRow(input) {
        const tr = input.closest('tr');
        const inputs = tr.querySelectorAll('.score-input');
        const cc = parseFloat(inputs[0].value) || 0;
        const gk = parseFloat(inputs[1].value) || 0;
        const ck = parseFloat(inputs[2].value) || 0;
        const total = (cc * 0.1) + (gk * 0.3) + (ck * 0.6);
        const rounded = Math.round(total * 10) / 10;

        const cellTk = tr.querySelector('.cell-tk');
        cellTk.textContent = rounded.toFixed(1);
        cellTk.style.color = rounded < 5.0 ? '#dc2626' : '#0f172a';

        let xl = 'Kém';
        if (rounded >= 9.0) xl = 'Xuất sắc';
        else if (rounded >= 8.0) xl = 'Giỏi';
        else if (rounded >= 7.0) xl = 'Khá';
        else if (rounded >= 5.0) xl = 'Trung bình';
        else if (rounded >= 3.5) xl = 'Yếu';

        const cellXl = tr.querySelector('.cell-xl');
        cellXl.innerHTML = getBadgeHtml(xl);
    }

    function gotoPage(p) {
        currentPage = p;
        loadDiemData();
    }

    function selectClass(ma) {
        currentMaLop = ma;
        currentPage = 1;
        closeClassModal();
        loadClassList();
        loadDiemData();
        window.history.pushState({}, '', `diem.php?ma_lop_hp=${encodeURIComponent(ma)}`);
    }

    function openClassModal() {
        document.getElementById('modal-class').style.display = 'block';
        document.getElementById('modal-backdrop-diem').style.display = 'block';
    }
    function closeClassModal() {
        document.getElementById('modal-class').style.display = 'none';
        document.getElementById('modal-backdrop-diem').style.display = 'none';
    }
    function openAddModal() {
        document.getElementById('modal-add-student').style.display = 'block';
        document.getElementById('modal-backdrop-diem').style.display = 'block';
    }
    function closeAddModal() {
        document.getElementById('modal-add-student').style.display = 'none';
        document.getElementById('modal-backdrop-diem').style.display = 'none';
    }

    async function saveAllGrades() {
        const rows = document.querySelectorAll('#grade-tbody tr[data-mssv]');
        if (rows.length === 0) return;

        const grades = {};
        rows.forEach(tr => {
            const mssv = tr.dataset.mssv;
            const inputs = tr.querySelectorAll('.score-input');
            grades[mssv] = {
                cc: inputs[0].value,
                gk: inputs[1].value,
                ck: inputs[2].value
            };
        });

        try {
            const res = await fetch('../api/giang_vien_diem_action.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'save_grades',
                    ma_lop_hp: currentMaLop,
                    grades: grades
                })
            });
            const data = await res.json();
            alert(data.message || 'Đã lưu điểm');
            loadDiemData();
        } catch(e) {
            console.error(e);
            alert('Lỗi khi lưu điểm');
        }
    }

    async function submitStudentForm(e) {
        e.preventDefault();
        const payload = {
            action: 'add_student',
            ma_lop_hp: currentMaLop,
            mssv: document.getElementById('m-mssv').value,
            ho_ten: document.getElementById('m-ho-ten').value,
            diem_cc: document.getElementById('m-cc').value,
            diem_gk: document.getElementById('m-gk').value,
            diem_ck: document.getElementById('m-ck').value
        };

        try {
            const res = await fetch('../api/giang_vien_diem_action.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const data = await res.json();
            alert(data.message);
            if (data.success) {
                closeAddModal();
                loadDiemData();
            }
        } catch(e) {
            console.error(e);
        }
    }

    async function deleteStudent(mssv) {
        if (!confirm(`Bạn có chắc chắn muốn xóa học viên MSV ${mssv} khỏi lớp học phần này?`)) return;
        try {
            const res = await fetch('../api/giang_vien_diem_action.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'delete_student',
                    ma_lop_hp: currentMaLop,
                    mssv: mssv
                })
            });
            const data = await res.json();
            alert(data.message);
            loadDiemData();
        } catch(e) {
            console.error(e);
        }
    }

    function exportCSV() {
        const rows = document.querySelectorAll('#grade-tbody tr[data-mssv]');
        let csvContent = "\uFEFFSTT,MSSV,Họ và tên,Điểm CC (10%),Điểm GK (30%),Điểm CK (60%),Điểm Tổng kết,Xếp loại\n";
        let stt = 1;
        rows.forEach(tr => {
            const mssv = tr.dataset.mssv;
            const name = tr.children[2].textContent.trim();
            const inputs = tr.querySelectorAll('.score-input');
            const tk = tr.querySelector('.cell-tk').textContent.trim();
            const xl = tr.querySelector('.cell-xl').textContent.trim();
            csvContent += `"${stt++}","${mssv}","${name}","${inputs[0].value}","${inputs[1].value}","${inputs[2].value}","${tk}","${xl}"\n`;
        });

        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const link = document.createElement("a");
        link.setAttribute("href", url);
        link.setAttribute("download", `BangDiem_${currentMaLop}.csv`);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    // Keyboard Navigation for Score Inputs (Excel-like & Modal Shortcuts)
    document.addEventListener('keydown', function(e) {
        // Alt + N mở Modal nhập điểm
        if (e.altKey && (e.key === 'n' || e.key === 'N')) {
            e.preventDefault();
            openAddModal();
            const mssvInput = document.getElementById('m-mssv');
            if (mssvInput) mssvInput.focus();
            return;
        }

        // Xử lý khi Modal Nhập điểm học viên mới đang mở
        const addModal = document.getElementById('modal-add-student');
        if (addModal && addModal.style.display !== 'none') {
            if (e.key === 'Escape') {
                e.preventDefault();
                closeAddModal();
                return;
            }

            if (e.key === 'Enter') {
                const active = document.activeElement;
                if (active && active.tagName === 'BUTTON') return;
                
                const formInputs = Array.from(document.querySelectorAll('#form-student-grade input'));
                const currIdx = formInputs.indexOf(active);
                if (currIdx !== -1 && currIdx < formInputs.length - 1) {
                    e.preventDefault();
                    formInputs[currIdx + 1].focus();
                    if (formInputs[currIdx + 1].select) formInputs[currIdx + 1].select();
                }
            }
        }

        // Điều hướng dạng Excel bằng phím mũi tên trong Bảng Điểm
        if (!e.target.classList.contains('score-input')) return;
        const input = e.target;
        const row = parseInt(input.dataset.row);
        const col = parseInt(input.dataset.col);
        let targetRow = row, targetCol = col;

        if (e.key === 'ArrowUp') { targetRow = row - 1; e.preventDefault(); }
        else if (e.key === 'ArrowDown') { targetRow = row + 1; e.preventDefault(); }
        else if (e.key === 'Enter' && (!addModal || addModal.style.display === 'none')) { targetRow = row + 1; e.preventDefault(); }
        else if (e.key === 'ArrowRight' && input.selectionEnd === input.value.length) { targetCol = col + 1; }
        else if (e.key === 'ArrowLeft' && input.selectionStart === 0) { targetCol = col - 1; }

        if (targetRow !== row || targetCol !== col) {
            const nextInput = document.querySelector(`.score-input[data-row="${targetRow}"][data-col="${targetCol}"]`);
            if (nextInput) { nextInput.focus(); nextInput.select(); }
        }
    });

    document.addEventListener('DOMContentLoaded', () => {
        loadClassList();
        loadDiemData();
        loadStudentList();
    });
</script>



<?php require_once __DIR__ . '/includes/footer.php'; ?>
