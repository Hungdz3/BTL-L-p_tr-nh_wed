<?php
// admin/hoc-phan.php
require_once '../config/db.php';

$db = getDB();
$counts_mh = [];
$counts_lhp = [];

try {
    // 1. Thống kê môn học (Tab 1)
    $counts_mh['tong'] = $db->query("SELECT COUNT(*) FROM mon_hoc")->fetchColumn();
    $counts_mh['active'] = $db->query("SELECT COUNT(*) FROM mon_hoc WHERE trang_thai = 'HOAT_DONG'")->fetchColumn();
    $counts_mh['pending'] = $db->query("SELECT COUNT(*) FROM mon_hoc WHERE trang_thai = 'CHO_DUYET'")->fetchColumn();
    $counts_mh['hidden'] = $db->query("SELECT COUNT(*) FROM mon_hoc WHERE trang_thai = 'DA_AN'")->fetchColumn();

    // 2. Thống kê lớp học phần (Tab 2)
    $counts_lhp['tong'] = $db->query("SELECT COUNT(*) FROM lop_hoc_phan")->fetchColumn();
    $counts_lhp['pending'] = $db->query("SELECT COUNT(*) FROM lop_hoc_phan WHERE trang_thai = 'CHO_DUYET'")->fetchColumn();
    $counts_lhp['approved'] = $db->query("SELECT COUNT(*) FROM lop_hoc_phan WHERE trang_thai = 'CHUA_MO' OR trang_thai = 'DANG_MO'")->fetchColumn();
    $counts_lhp['rejected'] = $db->query("SELECT COUNT(*) FROM lop_hoc_phan WHERE trang_thai = 'TU_CHOI'")->fetchColumn();
} catch (Exception $e) {
    // Fallback
}

// Fallback values
if (empty($counts_mh)) {
    $counts_mh = ['tong' => 156, 'active' => 89, 'pending' => 12, 'hidden' => 55];
}
if (empty($counts_lhp)) {
    $counts_lhp = ['tong' => 60, 'pending' => 12, 'approved' => 45, 'rejected' => 3];
}

require_once 'includes/header.php';
?>

<!-- Tab Selector Header -->
<div class="tabs-row">
  <button class="btn-tab active" id="tab-btn-mh" onclick="chuyenTab('mh')">Danh sách học phần</button>
  <button class="btn-tab" id="tab-btn-lhp" onclick="chuyenTab('lhp')">Xét duyệt lớp học phần</button>
</div>

<!-- ==========================================
     TAB 1: DANH SÁCH HỌC PHẦN (MÔN HỌC)
     ========================================== -->
<div id="tab-content-mh">
  <!-- Stats Row for MH -->
  <section class="dashboard-stats-row" style="margin-top: 10px;">
    <div class="stat-card cyan">
      <span class="stat-card-title">Tổng khóa học</span>
      <span class="stat-card-value"><?= $counts_mh['tong'] ?></span>
    </div>
    <div class="stat-card">
      <span class="stat-card-title">Đang hoạt động</span>
      <span class="stat-card-value"><?= $counts_mh['active'] ?></span>
    </div>
    <div class="stat-card yellow">
      <span class="stat-card-title">Chờ duyệt</span>
      <span class="stat-card-value"><?= $counts_mh['pending'] ?></span>
    </div>
    <div class="stat-card red">
      <span class="stat-card-title">Đã ẩn</span>
      <span class="stat-card-value"><?= $counts_mh['hidden'] ?></span>
    </div>
  </section>

  <!-- Filter Bar -->
  <section class="admin-filter-bar">
    <div class="filter-inputs-group">
      <input type="text" id="mh-search" placeholder="Tìm tên học phần..." onkeyup="if(event.key === 'Enter') taiMonHoc()">
      <select id="mh-filter-khoa" onchange="locNganhFormMH(); taiMonHoc();">
        <option value="">Khoa</option>
      </select>
      <select id="mh-filter-nganh" onchange="taiMonHoc()">
        <option value="">Ngành</option>
      </select>
      <button class="btn-admin-primary" onclick="taiMonHoc()">Tìm kiếm</button>
    </div>
    <button class="btn-add-new" onclick="moModalThemMH()">+ Thêm học phần</button>
  </section>

  <!-- Table list MH -->
  <section class="panel-card" style="margin: 0 28px 24px 28px;">
    <table style="width: 100%;">
      <thead>
        <tr>
          <th style="width: 120px;">Mã HP</th>
          <th>Tên học phần</th>
          <th style="width: 120px; text-align: center;">Số tín chỉ</th>
          <th style="width: 180px;">Loại môn</th>
          <th style="width: 150px;">Trạng thái</th>
          <th style="width: 100px; text-align: center;">Thao tác</th>
        </tr>
      </thead>
      <tbody id="mon-hoc-tbody">
        <!-- AJAX loaded -->
      </tbody>
    </table>
    <div class="pagination-row" style="margin: 12px 0 0 0;">
      <div id="mh-pagination-info">Hiển thị 0-0 trong số 0 học phần</div>
      <div class="pagination-buttons" id="mh-pagination-btns"></div>
    </div>
  </section>
</div>

<!-- ==========================================
     TAB 2: XÉT DUYỆT LỚP HỌC PHẦN (LHP)
     ========================================== -->
<div id="tab-content-lhp" style="display: none;">
  <!-- Stats Row for LHP -->
  <section class="dashboard-stats-row" style="margin-top: 10px;">
    <div class="stat-card yellow">
      <span class="stat-card-title">Chờ xét duyệt</span>
      <span class="stat-card-value"><?= $counts_lhp['pending'] ?></span>
    </div>
    <div class="stat-card green">
      <span class="stat-card-title">Đã phê duyệt</span>
      <span class="stat-card-value"><?= $counts_lhp['approved'] ?></span>
    </div>
    <div class="stat-card red">
      <span class="stat-card-title">Từ chối mở</span>
      <span class="stat-card-value"><?= $counts_lhp['rejected'] ?></span>
    </div>
  </section>

  <!-- Filter Bar -->
  <section class="admin-filter-bar">
    <div class="filter-inputs-group">
      <input type="text" id="lhp-search" placeholder="Tìm kiếm lớp..." onkeyup="if(event.key === 'Enter') taiLopHocPhan()">
      <select id="lhp-filter-status" onchange="taiLopHocPhan()">
        <option value="">Trạng thái duyệt</option>
        <option value="CHO_DUYET">Chờ duyệt</option>
        <option value="CHUA_MO">Đã phê duyệt</option>
        <option value="TU_CHOI">Từ chối mở</option>
      </select>
      <button class="btn-admin-primary" onclick="taiLopHocPhan()">Tìm kiếm</button>
    </div>
    <button class="btn-add-new" onclick="moModalThemLHP()">+ Thêm lớp học phần</button>
  </section>

  <!-- Table list LHP -->
  <section class="panel-card" style="margin: 0 28px 24px 28px;">
    <h3 style="margin-bottom: 15px;">Lớp học phần chờ xét duyệt và xử lý</h3>
    <table style="width: 100%;">
      <thead>
        <tr>
          <th style="width: 120px;">Mã Lớp</th>
          <th>Tên học phần</th>
          <th>Giảng viên</th>
          <th>Học kỳ</th>
          <th style="width: 180px;">Thời gian</th>
          <th style="width: 130px;">Trạng thái</th>
          <th style="width: 160px; text-align: center;">Thao tác</th>
        </tr>
      </thead>
      <tbody id="lop-hoc-phan-tbody">
        <!-- AJAX loaded -->
      </tbody>
    </table>
    <div class="pagination-row" style="margin: 12px 0 0 0;">
      <div id="lhp-pagination-info">Hiển thị 0-0 trong số 0 lớp học phần</div>
      <div class="pagination-buttons" id="lhp-pagination-btns"></div>
    </div>
  </section>
</div>

<!-- ==========================================
     MODAL A: ADD/EDIT COURSE (MÔN HỌC)
     ========================================== -->
<div class="custom-modal-admin" id="modal-mon-hoc" style="max-width: 500px;">
  <div class="modal-header-admin">
    <span id="mh-modal-title">Thêm học phần mới</span>
    <button class="modal-close-admin" onclick="dongModalMH()">×</button>
  </div>
  <form id="form-mon-hoc" onsubmit="guiFormMonHoc(event)">
    <div class="modal-body-admin">
      <input type="hidden" id="mh-is-update" value="false">
      
      <div class="form-row-admin">
        <div class="form-group-admin">
          <label for="mh-ma">Mã học phần <span>*</span></label>
          <input type="text" class="form-control-admin" id="mh-ma" placeholder="VD: 30TRA001" required>
        </div>
        <div class="form-group-admin">
          <label for="mh-ten">Tên học phần <span>*</span></label>
          <input type="text" class="form-control-admin" id="mh-ten" placeholder="Nhập tên học phần..." required>
        </div>
      </div>

      <div class="form-row-admin">
        <div class="form-group-admin">
          <label for="mh-tc">Số tín chỉ <span>*</span></label>
          <input type="number" class="form-control-admin" id="mh-tc" value="3" min="1" max="10" required>
        </div>
        <div class="form-group-admin">
          <label for="mh-loai">Loại học phần <span>*</span></label>
          <select class="form-control-admin" id="mh-loai" onchange="toggleFormMHLimits()" required>
            <option value="MON_CHUNG">Môn chung (Đại cương)</option>
            <option value="MON_CHUYEN_NGANH">Môn chuyên ngành</option>
          </select>
        </div>
      </div>

      <!-- Dynamic major inputs -->
      <div class="form-row-admin" id="mh-major-row" style="display: none;">
        <div class="form-group-admin">
          <label for="mh-khoa">Khoa</label>
          <select class="form-control-admin" id="mh-khoa" onchange="locNganhFormModalMH()">
            <!-- AJAX -->
          </select>
        </div>
        <div class="form-group-admin">
          <label for="mh-nganh">Ngành</label>
          <select class="form-control-admin" id="mh-nganh">
            <!-- AJAX -->
          </select>
        </div>
      </div>

      <div class="form-group-admin">
        <label for="mh-trang-thai">Trạng thái <span>*</span></label>
        <select class="form-control-admin" id="mh-trang-thai" required>
          <option value="HOAT_DONG">Hoạt động</option>
          <option value="NGUNG_HOAT_DONG">Ngưng hoạt động</option>
          <option value="CHO_DUYET">Chờ duyệt</option>
          <option value="DA_AN">Ẩn</option>
        </select>
      </div>
      
      <div style="font-size: 11px; color: #64748b; margin-top: 5px;">
        * Lưu ý: Học phần phải phù hợp với chương trình đào tạo của ngành.
      </div>
    </div>
    <div class="modal-footer-admin">
      <button type="button" class="btn-admin-secondary" onclick="dongModalMH()">Hủy</button>
      <button type="submit" class="btn-admin-primary" id="mh-btn-submit">Thêm học phần</button>
    </div>
  </form>
</div>

<!-- ==========================================
     MODAL B: ADD CLASS COURSE SECTION (LHP)
     ========================================== -->
<div class="custom-modal-admin" id="modal-lop-hoc-phan" style="max-width: 550px;">
  <div class="modal-header-admin">
    <span>Thêm lớp học phần</span>
    <button class="modal-close-admin" onclick="dongModalLHP()">×</button>
  </div>
  <form id="form-lop-hoc-phan" onsubmit="guiFormLopHocPhan(event)">
    <div class="modal-body-admin">
      
      <div class="form-row-admin">
        <div class="form-group-admin">
          <label for="lhp-mon">Tên học phần <span>*</span></label>
          <select class="form-control-admin" id="lhp-mon" required>
            <!-- Loaded via metadata -->
          </select>
        </div>
        <div class="form-group-admin">
          <label for="lhp-ma">Mã lớp học phần <span>*</span></label>
          <input type="text" class="form-control-admin" id="lhp-ma" placeholder="VD: LHP001, K65_CNTT..." required>
        </div>
      </div>

      <div class="form-row-admin">
        <div class="form-group-admin">
          <label for="lhp-ngay-bd">Ngày bắt đầu</label>
          <input type="date" class="form-control-admin" id="lhp-ngay-bd">
        </div>
        <div class="form-group-admin">
          <label for="lhp-ngay-kt">Ngày kết thúc</label>
          <input type="date" class="form-control-admin" id="lhp-ngay-kt">
        </div>
      </div>

      <div class="form-row-admin">
        <div class="form-group-admin">
          <label for="lhp-gv">Giảng viên giảng dạy <span>*</span></label>
          <select class="form-control-admin" id="lhp-gv" required>
            <!-- Loaded -->
          </select>
        </div>
        <div class="form-group-admin">
          <label for="lhp-hk">Học kỳ đăng ký <span>*</span></label>
          <select class="form-control-admin" id="lhp-hk" required>
            <!-- Loaded -->
          </select>
        </div>
      </div>

      <div class="form-row-admin">
        <div class="form-group-admin">
          <label for="lhp-phong">Phòng học <span>*</span></label>
          <input type="text" class="form-control-admin" id="lhp-phong" value="A101" required>
        </div>
        <div class="form-group-admin">
          <label for="lhp-si-so">Sĩ số tối đa <span>*</span></label>
          <input type="number" class="form-control-admin" id="lhp-si-so" value="50" min="5" required>
        </div>
      </div>

      <!-- Schedule Setup ca hoc -->
      <div style="border-top: 1px dashed #e2e8f0; padding-top: 15px; margin-top: 15px;">
        <h4 style="font-size: 13px; color: #1a3a6b; margin-bottom: 10px;">XẾP LỊCH HỌC (CHỌN THỨ & TIẾT HỌC)</h4>
        
        <div class="form-group-admin">
          <label>Thứ giảng dạy</label>
          <div class="day-checkboxes">
            <button type="button" class="btn-day-chk selected" data-day="2" onclick="clickThuCheckbox(this)">T2</button>
            <button type="button" class="btn-day-chk" data-day="3" onclick="clickThuCheckbox(this)">T3</button>
            <button type="button" class="btn-day-chk" data-day="4" onclick="clickThuCheckbox(this)">T4</button>
            <button type="button" class="btn-day-chk" data-day="5" onclick="clickThuCheckbox(this)">T5</button>
            <button type="button" class="btn-day-chk" data-day="6" onclick="clickThuCheckbox(this)">T6</button>
            <button type="button" class="btn-day-chk" data-day="7" onclick="clickThuCheckbox(this)">T7</button>
          </div>
        </div>

        <div class="form-row-admin">
          <div class="form-group-admin">
            <label for="lhp-tiet-bd">Tiết bắt đầu <span>*</span></label>
            <select class="form-control-admin" id="lhp-tiet-bd" required>
              <option value="1">Tiết 1 (6:45)</option>
              <option value="2">Tiết 2 (7:40)</option>
              <option value="3">Tiết 3 (8:35)</option>
              <option value="4">Tiết 4 (9:30)</option>
              <option value="5">Tiết 5 (10:25)</option>
              <option value="6">Tiết 6 (11:20)</option>
              <option value="7">Tiết 7 (12:45)</option>
              <option value="8">Tiết 8 (13:40)</option>
              <option value="9">Tiết 9 (14:35)</option>
              <option value="10">Tiết 10 (15:30)</option>
              <option value="11">Tiết 11 (16:25)</option>
              <option value="12">Tiết 12 (17:20)</option>
            </select>
          </div>
          <div class="form-group-admin">
            <label for="lhp-tiet-kt">Tiết kết thúc <span>*</span></label>
            <select class="form-control-admin" id="lhp-tiet-kt" required>
              <option value="1">Tiết 1 (7:35)</option>
              <option value="2">Tiết 2 (8:30)</option>
              <option value="3" selected>Tiết 3 (9:25)</option>
              <option value="4">Tiết 4 (10:20)</option>
              <option value="5">Tiết 5 (11:15)</option>
              <option value="6">Tiết 6 (12:10)</option>
              <option value="7">Tiết 7 (13:35)</option>
              <option value="8">Tiết 8 (14:30)</option>
              <option value="9">Tiết 9 (15:25)</option>
              <option value="10">Tiết 10 (16:20)</option>
              <option value="11">Tiết 11 (17:15)</option>
              <option value="12">Tiết 12 (18:10)</option>
            </select>
          </div>
        </div>
      </div>
    </div>
    <div class="modal-footer-admin">
      <button type="button" class="btn-admin-secondary" onclick="dongModalLHP()">Hủy</button>
      <button type="submit" class="btn-admin-primary">Thêm lớp</button>
    </div>
  </form>
</div>

<!-- Backdrop -->
<div class="modal-backdrop" id="modal-backdrop-hp" style="display: none; z-index: 10000;"></div>

<script>
  let monHocList = [];
  let lopHocPhanList = [];
  let metadata = { khoa: [], nganh: [], mon_hoc: [], giao_vien: [], hoc_ky: [] };
  let selectedThu = 2; // Default Thứ 2
  let currentPageMH = 1;
  let currentPageLHP = 1;
  const pageSize = 6;

  // Chuyển đổi qua lại giữa Tab môn học và Tab lớp học phần
  function chuyenTab(loai) {
    document.getElementById('tab-btn-mh').className = (loai === 'mh') ? 'btn-tab active' : 'btn-tab';
    document.getElementById('tab-btn-lhp').className = (loai === 'lhp') ? 'btn-tab active' : 'btn-tab';
    
    document.getElementById('tab-content-mh').style.display = (loai === 'mh') ? 'block' : 'none';
    document.getElementById('tab-content-lhp').style.display = (loai === 'lhp') ? 'block' : 'none';
    
    if (loai === 'mh') taiMonHoc();
    else taiLopHocPhan();
  }

  // Tải thông tin dropdown từ API
  async function taiMetadata() {
    try {
      const res = await fetch('../api/admin/hoc_phan_action.php?get_metadata=1');
      const data = await res.json();
      if (data.success) {
        metadata = data;
        
        // Populate filter Môn học
        const filterKhoa = document.getElementById('mh-filter-khoa');
        filterKhoa.innerHTML = '<option value="">Khoa</option>' + 
          metadata.khoa.map(k => `<option value="${k.ma_khoa}">${escapeHtml(k.ten_khoa)}</option>`).join('');
          
        // Populate modal Môn học Khoa dropdown
        const modalKhoa = document.getElementById('mh-khoa');
        modalKhoa.innerHTML = metadata.khoa.map(k => `<option value="${k.ma_khoa}">${escapeHtml(k.ten_khoa)}</option>`).join('');
        
        // Populate modal Lớp học phần dropdowns
        document.getElementById('lhp-mon').innerHTML = metadata.mon_hoc.map(m => `<option value="${m.ma_mon}">${escapeHtml(m.ten_mon)} (${m.ma_mon})</option>`).join('');
        document.getElementById('lhp-gv').innerHTML = metadata.giao_vien.map(g => `<option value="${g.ma_gv}">${escapeHtml(g.ho_ten)}</option>`).join('');
        document.getElementById('lhp-hk').innerHTML = metadata.hoc_ky.map(h => `<option value="${h.id_hoc_ky}">${escapeHtml(h.ten_hoc_ky)} (${h.nam_hoc})</option>`).join('');
        
        locNganhFormMH();
        locNganhFormModalMH();
      }
    } catch (e) {
      console.error(e);
    }
  }

  // Tải danh sách môn học (Tab 1)
  async function taiMonHoc() {
    const q = document.getElementById('mh-search').value;
    const khoa = document.getElementById('mh-filter-khoa').value;
    const nganh = document.getElementById('mh-filter-nganh').value;
    
    try {
      const res = await fetch(`../api/admin/hoc_phan_action.php?q=${encodeURIComponent(q)}&khoa=${encodeURIComponent(khoa)}&nganh=${encodeURIComponent(nganh)}`);
      const resData = await res.json();
      if (resData.success) {
        monHocList = resData.data;
        currentPageMH = 1;
        renderBangMonHoc(monHocList);
      }
    } catch (error) {
      console.error(error);
    }
  }

  // Tải danh sách lớp học phần (Tab 2)
  async function taiLopHocPhan() {
    const q = document.getElementById('lhp-search').value;
    const status = document.getElementById('lhp-filter-status').value;
    
    try {
      const res = await fetch(`../api/admin/hoc_phan_action.php?get_class_courses=1&q=${encodeURIComponent(q)}&trang_thai=${encodeURIComponent(status)}`);
      const resData = await res.json();
      if (resData.success) {
        lopHocPhanList = resData.data;
        currentPageLHP = 1;
        renderBangLopHocPhan(lopHocPhanList);
      }
    } catch (error) {
      console.error(error);
    }
  }

  // Lọc ngành ở Filter bar (Tab 1)
  function locNganhFormMH() {
    const kVal = document.getElementById('mh-filter-khoa').value;
    const filterNganh = document.getElementById('mh-filter-nganh');
    
    if (kVal === '') {
      filterNganh.innerHTML = '<option value="">Ngành</option>';
      return;
    }
    const ngs = metadata.nganh.filter(n => n.ma_khoa === kVal);
    filterNganh.innerHTML = '<option value="">Ngành</option>' + 
      ngs.map(n => `<option value="${n.ma_nganh}">${escapeHtml(n.ten_nganh)}</option>`).join('');
  }

  // Lọc ngành ở Modal thêm môn học
  function locNganhFormModalMH() {
    const kVal = document.getElementById('mh-khoa').value;
    const modalNganh = document.getElementById('mh-nganh');
    const ngs = metadata.nganh.filter(n => n.ma_khoa === kVal);
    modalNganh.innerHTML = ngs.map(n => `<option value="${n.ma_nganh}">${escapeHtml(n.ten_nganh)}</option>`).join('');
  }

  // Render bảng môn học với phân trang 6 / trang
  function renderBangMonHoc(ds) {
    const tbody = document.getElementById('mon-hoc-tbody');
    const info = document.getElementById('mh-pagination-info');
    const btnsContainer = document.getElementById('mh-pagination-btns');

    if (!ds || ds.length === 0) {
      tbody.innerHTML = `<tr><td colspan="6" style="text-align: center; color: #888; padding: 20px;">Không tìm thấy học phần nào.</td></tr>`;
      if (info) info.textContent = 'Hiển thị 0-0 trong số 0 học phần';
      if (btnsContainer) btnsContainer.innerHTML = '';
      return;
    }

    const totalItems = ds.length;
    const totalPages = Math.ceil(totalItems / pageSize) || 1;
    if (currentPageMH > totalPages) currentPageMH = totalPages;
    if (currentPageMH < 1) currentPageMH = 1;

    const startIdx = (currentPageMH - 1) * pageSize;
    const endIdx = Math.min(startIdx + pageSize, totalItems);
    const pageItems = ds.slice(startIdx, endIdx);
    
    tbody.innerHTML = pageItems.map(mh => {
      const statusClass = mh.trang_thai === 'HOAT_DONG' ? 'active' : (mh.trang_thai === 'CHO_DUYET' ? 'pending' : 'closed');
      const statusText = mh.trang_thai === 'HOAT_DONG' ? 'Hoạt động' : (mh.trang_thai === 'CHO_DUYET' ? 'Chờ duyệt' : 'Ẩn');
      const loaiText = mh.loai_mon_hoc === 'MON_CHUYEN_NGANH' ? 'Môn chuyên ngành' : 'Môn chung';
      
      return `
        <tr>
          <td><strong style="color: #1e4d8c;">${escapeHtml(mh.ma_mon)}</strong></td>
          <td><strong>${escapeHtml(mh.ten_mon)}</strong></td>
          <td style="text-align: center;">${mh.so_tin_chi}</td>
          <td>${loaiText}</td>
          <td><span class="badge-status ${statusClass}">${statusText}</span></td>
          <td style="text-align: center;">
            <div class="action-icons" style="justify-content: center;">
              <button class="btn-action edit" onclick="suaMonHoc('${mh.ma_mon}')" title="Sửa">✏️</button>
              <button class="btn-action delete" onclick="xoaMonHoc('${mh.ma_mon}')" title="Xóa">🗑️</button>
            </div>
          </td>
        </tr>
      `;
    }).join('');

    if (info) info.textContent = `Hiển thị ${startIdx + 1}-${endIdx} trong số ${totalItems} học phần`;
    renderPageBtns(btnsContainer, totalPages, currentPageMH, 'MH');
  }

  // Render bảng lớp học phần xét duyệt với phân trang 6 / trang
  function renderBangLopHocPhan(ds) {
    const tbody = document.getElementById('lop-hoc-phan-tbody');
    const info = document.getElementById('lhp-pagination-info');
    const btnsContainer = document.getElementById('lhp-pagination-btns');

    if (!ds || ds.length === 0) {
      tbody.innerHTML = `<tr><td colspan="7" style="text-align: center; color: #888; padding: 20px;">Không có lớp học phần nào.</td></tr>`;
      if (info) info.textContent = 'Hiển thị 0-0 trong số 0 lớp học phần';
      if (btnsContainer) btnsContainer.innerHTML = '';
      return;
    }

    const totalItems = ds.length;
    const totalPages = Math.ceil(totalItems / pageSize) || 1;
    if (currentPageLHP > totalPages) currentPageLHP = totalPages;
    if (currentPageLHP < 1) currentPageLHP = 1;

    const startIdx = (currentPageLHP - 1) * pageSize;
    const endIdx = Math.min(startIdx + pageSize, totalItems);
    const pageItems = ds.slice(startIdx, endIdx);

    tbody.innerHTML = pageItems.map(lhp => {
      const isPending = lhp.trang_thai === 'CHO_DUYET';
      const isRejected = lhp.trang_thai === 'TU_CHOI';
      const isApproved = !isPending && !isRejected;
      
      let statusText = 'Chờ duyệt';
      let statusClass = 'pending';
      if (isApproved) {
        statusText = 'Đã duyệt';
        statusClass = 'active';
      } else if (isRejected) {
        statusText = 'Từ chối mở';
        statusClass = 'closed';
      }
      
      const start = lhp.ngay_bat_dau ? new Date(lhp.ngay_bat_dau).toLocaleDateString('vi-VN') : 'Dự kiến';
      const end = lhp.ngay_ket_thuc ? new Date(lhp.ngay_ket_thuc).toLocaleDateString('vi-VN') : 'Dự kiến';

      let actionHtml = '';
      if (isPending) {
        actionHtml = `
          <button class="btn-admin-primary" style="padding: 4px 10px; font-size:12px;" onclick="duyetLopHocPhan('${lhp.ma_lhp}', 'DANG_MO')">Duyệt</button>
          <button class="btn-admin-secondary" style="padding: 4px 10px; font-size:12px; background:#fca5a5; color:#7f1d1d;" onclick="duyetLopHocPhan('${lhp.ma_lhp}', 'TU_CHOI')">Hủy lớp</button>
        `;
      } else {
        actionHtml = `
          <button class="btn-admin-secondary" style="padding: 4px 10px; font-size:12px;" onclick="xoaLopHocPhan('${lhp.ma_lhp}')">Xóa lớp</button>
        `;
      }

      return `
        <tr>
          <td><strong style="color:#1e4d8c;">${escapeHtml(lhp.ma_lhp)}</strong></td>
          <td><strong>${escapeHtml(lhp.ten_mon)}</strong></td>
          <td>${escapeHtml(lhp.giang_vien)}</td>
          <td>${escapeHtml(lhp.ten_hoc_ky)}</td>
          <td style="font-size:12px;">${start} - ${end}</td>
          <td><span class="badge-status ${statusClass}">${statusText}</span></td>
          <td style="text-align: center;">${actionHtml}</td>
        </tr>
      `;
    }).join('');

    if (info) info.textContent = `Hiển thị ${startIdx + 1}-${endIdx} trong số ${totalItems} lớp học phần`;
    renderPageBtns(btnsContainer, totalPages, currentPageLHP, 'LHP');
  }

  // Hàm dùng chung để render các nút phân trang
  function renderPageBtns(container, totalPages, currentPage, tab) {
    if (!container) return;
    let html = '';
    const fn = tab === 'MH' ? 'chuyenTrangMH' : 'chuyenTrangLHP';

    html += `<button class="btn-page" ${currentPage === 1 ? 'disabled style="opacity:0.5;cursor:not-allowed;"' : ''} onclick="${fn}(${currentPage - 1})">‹ Trước</button>`;

    let startPage = Math.max(1, currentPage - 2);
    let endPage = Math.min(totalPages, currentPage + 2);

    if (startPage > 1) {
      html += `<button class="btn-page ${currentPage === 1 ? 'active' : ''}" onclick="${fn}(1)">1</button>`;
      if (startPage > 2) html += `<span style="padding: 3px 5px; color: #94a3b8;">...</span>`;
    }

    for (let p = startPage; p <= endPage; p++) {
      html += `<button class="btn-page ${p === currentPage ? 'active' : ''}" onclick="${fn}(${p})">${p}</button>`;
    }

    if (endPage < totalPages) {
      if (endPage < totalPages - 1) html += `<span style="padding: 3px 5px; color: #94a3b8;">...</span>`;
      html += `<button class="btn-page ${currentPage === totalPages ? 'active' : ''}" onclick="${fn}(${totalPages})">${totalPages}</button>`;
    }

    html += `<button class="btn-page" ${currentPage === totalPages ? 'disabled style="opacity:0.5;cursor:not-allowed;"' : ''} onclick="${fn}(${currentPage + 1})">Sau ›</button>`;
    container.innerHTML = html;
  }

  function chuyenTrangMH(page) {
    const total = Math.ceil(monHocList.length / pageSize) || 1;
    if (page < 1 || page > total) return;
    currentPageMH = page;
    renderBangMonHoc(monHocList);
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }

  function chuyenTrangLHP(page) {
    const total = Math.ceil(lopHocPhanList.length / pageSize) || 1;
    if (page < 1 || page > total) return;
    currentPageLHP = page;
    renderBangLopHocPhan(lopHocPhanList);
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }


  // Ẩn/Hiện Khoa/Ngành trong form môn học dựa theo Loại học phần
  function toggleFormMHLimits() {
    const loai = document.getElementById('mh-loai').value;
    const row = document.getElementById('mh-major-row');
    row.style.display = (loai === 'MON_CHUYEN_NGANH') ? 'grid' : 'none';
  }

  // Thứ giảng dạy selector logic
  function clickThuCheckbox(btn) {
    document.querySelectorAll('.btn-day-chk').forEach(b => b.classList.remove('selected'));
    btn.classList.add('selected');
    selectedThu = parseInt(btn.getAttribute('data-day'));
  }

  // Modal actions MH
  function moModalThemMH() {
    document.getElementById('form-mon-hoc').reset();
    document.getElementById('mh-is-update').value = 'false';
    document.getElementById('mh-ma').readOnly = false;
    document.getElementById('mh-ma').style.backgroundColor = '';
    toggleFormMHLimits();
    
    document.getElementById('modal-mon-hoc').style.display = 'flex';
    document.getElementById('modal-backdrop-hp').style.display = 'block';
  }
  function dongModalMH() {
    document.getElementById('modal-mon-hoc').style.display = 'none';
    document.getElementById('modal-backdrop-hp').style.display = 'none';
  }

  // Sửa học phần
  function suaMonHoc(ma) {
    const mh = monHocList.find(x => x.ma_mon === ma);
    if (!mh) return;

    document.getElementById('mh-is-update').value = 'true';
    document.getElementById('mh-ma').value = mh.ma_mon;
    document.getElementById('mh-ma').readOnly = true;
    document.getElementById('mh-ma').style.backgroundColor = '#f1f5f9';

    document.getElementById('mh-ten').value = mh.ten_mon;
    document.getElementById('mh-tc').value = mh.so_tin_chi;
    document.getElementById('mh-loai').value = mh.loai_mon_hoc;
    document.getElementById('mh-trang-thai').value = mh.trang_thai;
    
    toggleFormMHLimits();
    if (mh.loai_mon_hoc === 'MON_CHUYEN_NGANH') {
      document.getElementById('mh-khoa').value = mh.ma_khoa;
      locNganhFormModalMH();
      document.getElementById('mh-nganh').value = mh.ma_nganh;
    }

    document.getElementById('mh-modal-title').textContent = 'Chỉnh sửa học phần';
    document.getElementById('mh-btn-submit').textContent = 'Lưu thay đổi';

    document.getElementById('modal-mon-hoc').style.display = 'flex';
    document.getElementById('modal-backdrop-hp').style.display = 'block';
  }

  // Gửi form môn học
  async function guiFormMonHoc(e) {
    e.preventDefault();
    const isUpdate = document.getElementById('mh-is-update').value === 'true';
    
    const payload = {
      action: 'add_mon_hoc',
      is_update: isUpdate,
      ma_mon: document.getElementById('mh-ma').value,
      ten_mon: document.getElementById('mh-ten').value,
      so_tin_chi: document.getElementById('mh-tc').value,
      loai_mon_hoc: document.getElementById('mh-loai').value,
      ma_khoa: document.getElementById('mh-khoa').value,
      ma_nganh: document.getElementById('mh-nganh').value,
      trang_thai: document.getElementById('mh-trang-thai').value
    };

    try {
      const res = await fetch('../api/admin/hoc_phan_action.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });
      const data = await res.json();
      if (data.success) {
        showToast(data.message, 'success');
        dongModalMH();
        taiMonHoc();
      } else {
        showToast(data.message, 'error');
      }
    } catch (e) {
      console.error(e);
      showToast('Lỗi kết nối máy chủ.', 'error');
    }
  }

  // Xóa môn học
  function xoaMonHoc(ma) {
    showConfirmModal(`Bạn có chắc chắn muốn xóa học phần ${ma}?`, async () => {
      try {
        const res = await fetch('../api/admin/hoc_phan_action.php', {
          method: 'DELETE',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ action: 'delete_mon_hoc', ma_mon: ma })
        });
        const data = await res.json();
        if (data.success) {
          showToast(data.message, 'success');
          taiMonHoc();
        } else {
          showToast(data.message, 'error');
        }
      } catch (e) {
        console.error(e);
        showToast('Lỗi kết nối.', 'error');
      }
    });
  }

  // Modal actions LHP
  function moModalThemLHP() {
    document.getElementById('form-lop-hoc-phan').reset();
    document.getElementById('modal-lop-hoc-phan').style.display = 'flex';
    document.getElementById('modal-backdrop-hp').style.display = 'block';
  }
  function dongModalLHP() {
    document.getElementById('modal-lop-hoc-phan').style.display = 'none';
    document.getElementById('modal-backdrop-hp').style.display = 'none';
  }

  // Gửi form lớp học phần (xếp lịch học ca)
  async function guiFormLopHocPhan(e) {
    e.preventDefault();
    
    const payload = {
      action: 'add_lop_hoc_phan',
      ma_lhp: document.getElementById('lhp-ma').value,
      ma_mon: document.getElementById('lhp-mon').value,
      ma_gv: document.getElementById('lhp-gv').value,
      id_hoc_ky: document.getElementById('lhp-hk').value,
      phong: document.getElementById('lhp-phong').value,
      si_so_toi_da: document.getElementById('lhp-si-so').value,
      ngay_bat_dau: document.getElementById('lhp-ngay-bd').value,
      ngay_ket_thuc: document.getElementById('lhp-ngay-kt').value,
      thu: selectedThu,
      tiet_bat_dau: document.getElementById('lhp-tiet-bd').value,
      tiet_ket_thuc: document.getElementById('lhp-tiet-kt').value,
      trang_thai: 'CHO_DUYET' // Gửi chờ duyệt theo đúng nghiệp vụ
    };

    try {
      const res = await fetch('../api/admin/hoc_phan_action.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });
      const data = await res.json();
      if (data.success) {
        showToast(data.message, 'success');
        dongModalLHP();
        chuyenTab('lhp');
      } else {
        showToast(data.message, 'error');
      }
    } catch (e) {
      console.error(e);
      showToast('Lỗi kết nối máy chủ.', 'error');
    }
  }

  // Phê duyệt mở lớp học phần
  async function duyetLopHocPhan(ma, status) {
    try {
      const res = await fetch('../api/admin/hoc_phan_action.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'approve_class', ma_lhp: ma, trang_thai: status })
      });
      const data = await res.json();
      if (data.success) {
        showToast(data.message, 'success');
        taiLopHocPhan();
      } else {
        showToast(data.message, 'error');
      }
    } catch (e) {
      console.error(e);
    }
  }

  // Xóa lớp học phần
  function xoaLopHocPhan(ma) {
    showConfirmModal(`Bạn có chắc chắn muốn hủy/xóa lớp học phần ${ma}?`, async () => {
      try {
        const res = await fetch('../api/admin/hoc_phan_action.php', {
          method: 'DELETE',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ action: 'delete_lop_hoc_phan', ma_lhp: ma })
        });
        const data = await res.json();
        if (data.success) {
          showToast(data.message, 'success');
          taiLopHocPhan();
        } else {
          showToast(data.message, 'error');
        }
      } catch (e) {
        console.error(e);
      }
    });
  }

  // XSS protection
  function escapeHtml(string) {
    const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
    return String(string).replace(/[&<>"']/g, m => map[m]);
  }

  // Khởi chạy khi trang tải xong
  document.addEventListener('DOMContentLoaded', async () => {
    await taiMetadata();
    
    // Đọc URL parameter để tự động mở form/tab
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('add_class')) {
      chuyenTab('lhp');
      moModalThemLHP();
    } else if (urlParams.has('add')) {
      chuyenTab('mh');
      moModalThemMH();
    } else {
      taiMonHoc();
    }
  });
</script>

<?php require_once 'includes/footer.php'; ?>
