<?php
// admin/sinh-vien.php
require_once 'includes/header.php';
?>

<!-- Filter Bar -->
<section class="admin-filter-bar">
  <div class="filter-inputs-group">
    <input type="text" id="search-input" placeholder="Tìm tên hoặc mã SV..." onkeyup="if(event.key === 'Enter') taiSinhVien()">
    <select id="filter-khoa" onchange="locNganhVaLop(); taiSinhVien();">
      <option value="">Khoa</option>
      <!-- AJAX loads options -->
    </select>
    <select id="filter-nganh" onchange="locLop(); taiSinhVien();">
      <option value="">Ngành</option>
    </select>
    <select id="filter-lop" onchange="taiSinhVien()">
      <option value="">Lớp</option>
    </select>
    <button class="btn-admin-primary" onclick="taiSinhVien()">Tìm kiếm</button>
  </div>
  <div style="display: flex; gap: 10px;">
    <button class="btn-add-new" style="background-color: #10b981;" onclick="moModalImport()">📁 Nhập Excel/CSV</button>
    <button class="btn-add-new" onclick="moModalThem()">+ Thêm sinh viên</button>
  </div>
</section>

<!-- Table Content -->
<section class="panel-card" style="margin: 0 28px 24px 28px;">
  <table style="width: 100%;">
    <thead>
      <tr>
        <th style="width: 100px;">Mã SV</th>
        <th>Họ tên</th>
        <th>Email</th>
        <th style="width: 120px;">Lớp</th>
        <th style="width: 180px;">Khoa</th>
        <th style="width: 120px;">Trạng thái</th>
        <th style="width: 100px; text-align: center;">Thao tác</th>
      </tr>
    </thead>
    <tbody id="sinh-vien-tbody">
      <!-- Loaded dynamically via AJAX -->
    </tbody>
  </table>
  <div class="pagination-row">
    <div id="pagination-info">Hiển thị 1-0 trong số 0 sinh viên</div>
    <div class="pagination-buttons" id="pagination-btns"></div>
  </div>
</section>

<!-- Modal 1: Add/Edit Student Manual Form -->
<div class="custom-modal-admin" id="modal-sinh-vien" style="max-width: 520px;">
  <div class="modal-header-admin">
    <span id="modal-title">Thêm sinh viên mới</span>
    <button class="modal-close-admin" onclick="dongModal()">×</button>
  </div>
  <form id="form-sinh-vien" onsubmit="guiFormSinhVien(event)">
    <div class="modal-body-admin">
      <input type="hidden" id="sv-is-update" value="false">
      
      <div class="form-row-admin">
        <div class="form-group-admin">
          <label for="sv-ma">Mã sinh viên <span>*</span></label>
          <input type="text" class="form-control-admin" id="sv-ma" required placeholder="Ví dụ: SV001">
        </div>
        <div class="form-group-admin">
          <label for="sv-ho-ten">Họ tên <span>*</span></label>
          <input type="text" class="form-control-admin" id="sv-ho-ten" required placeholder="Ví dụ: Nguyễn Văn Anh">
        </div>
      </div>

      <div class="form-row-admin">
        <div class="form-group-admin">
          <label for="sv-email">Email</label>
          <input type="email" class="form-control-admin" id="sv-email" placeholder="Ví dụ: anh.nv@student.hnda.edu.vn">
        </div>
        <div class="form-group-admin">
          <label for="sv-sdt">Số điện thoại</label>
          <input type="text" class="form-control-admin" id="sv-sdt" placeholder="Ví dụ: 0987654321">
        </div>
      </div>

      <div class="form-row-admin">
        <div class="form-group-admin">
          <label for="sv-ngay-sinh">Ngày sinh</label>
          <input type="date" class="form-control-admin" id="sv-ngay-sinh">
        </div>
        <div class="form-group-admin">
          <label for="sv-gioi-tinh">Giới tính</label>
          <select class="form-control-admin" id="sv-gioi-tinh">
            <option value="NAM">Nam</option>
            <option value="NU">Nữ</option>
            <option value="KHAC">Khác</option>
          </select>
        </div>
      </div>

      <div class="form-group-admin">
        <label for="sv-lop">Lớp sinh hoạt <span>*</span></label>
        <select class="form-control-admin" id="sv-lop" required>
          <!-- Loaded dynamically -->
        </select>
      </div>

      <div class="form-group-admin">
        <label for="sv-trang-thai">Trạng thái <span>*</span></label>
        <select class="form-control-admin" id="sv-trang-thai" required>
          <option value="DANG_HOC">Đang học</option>
          <option value="TAM_DUNG">Tạm dừng</option>
          <option value="THOI_HOC">Thôi học</option>
          <option value="DA_TOT_NGHIEP">Đã tốt nghiệp</option>
        </select>
      </div>

    </div>
    <div class="modal-footer-admin">
      <button type="button" class="btn-admin-secondary" onclick="dongModal()">Hủy</button>
      <button type="submit" class="btn-admin-primary" id="btn-submit-sv">Thêm sinh viên</button>
    </div>
  </form>
</div>

<!-- Modal 2: Import from Excel/CSV -->
<div class="custom-modal-admin" id="modal-import-sinh-vien" style="max-width: 520px;">
  <div class="modal-header-admin">
    <span>Nhập sinh viên từ Excel/CSV</span>
    <button class="modal-close-admin" onclick="dongModalImport()">×</button>
  </div>
  <form id="form-import-sv" onsubmit="guiFormImport(event)">
    <div class="modal-body-admin">
      <div style="background-color: #f0fdf4; border: 1px solid #bbf7d0; padding: 12px; border-radius: 6px; margin-bottom: 15px; color: #166534; font-size: 13px;">
        <strong>Tải tệp mẫu:</strong> Bạn có thể tạo file Excel/CSV có thứ tự cột:
        <br><code style="font-weight: bold; background: #dcfce7; padding: 2px 4px; border-radius: 4px;">Mã SV, Họ tên, Email, Số điện thoại, Mã lớp SV, Ngày sinh (YYYY-MM-DD), Giới tính</code>
        <br><a href="#" onclick="taiCSVMau(); return false;" style="color: #166534; font-weight: bold; text-decoration: underline; margin-top: 5px; display: inline-block;">📥 Tải tệp CSV mẫu tại đây</a>
      </div>

      <div class="form-group-admin">
        <label>Chọn tệp CSV / Excel <span>*</span></label>
        <div class="file-upload-box" onclick="document.getElementById('import-file').click()">
          <svg width="24" height="24" viewBox="0 0 24 24" style="margin: 0 auto 5px auto; display: block;">
            <path d="M19.35 10.04C18.67 6.59 15.64 4 12 4 9.11 4 6.6 5.64 5.35 8.04 2.34 8.36 0 10.91 0 14c0 3.31 2.69 6 6 6h13c2.76 0 5-2.24 5-5 0-2.64-2.05-4.78-4.65-4.96zM14 13v4h-4v-4H7l5-5 5 5h-3z"/>
          </svg>
          <span id="import-file-label">Kéo thả tệp CSV vào đây hoặc Click để duyệt</span>
          <br><small style="color: #94a3b8; font-size: 10px;">Chỉ hỗ trợ tệp định dạng .csv</small>
        </div>
        <input type="file" id="import-file" accept=".csv" required style="display: none;" onchange="capNhatTenFileImport(this)">
      </div>

      <!-- Hiển thị kết quả Log Import -->
      <div id="import-results" style="display: none; margin-top: 15px; max-height: 180px; overflow-y: auto; font-size: 12px; border: 1px solid #cbd5e1; border-radius: 6px; padding: 10px; background: #f8fafc;">
        <!-- Log results loaded dynamically -->
      </div>
    </div>
    <div class="modal-footer-admin">
      <button type="button" class="btn-admin-secondary" onclick="dongModalImport()">Hủy</button>
      <button type="submit" class="btn-admin-primary" style="background-color: #10b981;">Nhập dữ liệu</button>
    </div>
  </form>
</div>

<!-- Modal Backdrop -->
<div class="modal-backdrop" id="modal-backdrop-sv" style="display: none; z-index: 10000;"></div>

<script>
  let svList = [];
  let filterData = { khoa: [], nganh: [], lop: [] };
  let currentPage = 1;
  const pageSize = 6;

  // Tải danh sách bộ lọc
  async function taiBoLoc() {
    try {
      const res = await fetch('../api/admin/sinh_vien_action.php?get_filters=1');
      const data = await res.json();
      if (data.success) {
        filterData = data;
        
        // Render bộ lọc Khoa
        const filterKhoa = document.getElementById('filter-khoa');
        filterKhoa.innerHTML = '<option value="">Khoa</option>' + 
          filterData.khoa.map(k => `<option value="${k.ma_khoa}">${escapeHtml(k.ten_khoa)}</option>`).join('');
          
        // Render danh sách Lớp vào Form modal
        const formLop = document.getElementById('sv-lop');
        formLop.innerHTML = filterData.lop.map(l => `<option value="${l.ma_lop_sv}">${escapeHtml(l.ten_lop)}</option>`).join('');
        
        locNganhVaLop();
      }
    } catch (e) {
      console.error(e);
    }
  }

  // Tải danh sách sinh viên qua API
  async function taiSinhVien() {
    const q = document.getElementById('search-input').value;
    const khoa = document.getElementById('filter-khoa').value;
    const nganh = document.getElementById('filter-nganh').value;
    const lop = document.getElementById('filter-lop').value;
    
    try {
      const res = await fetch(`../api/admin/sinh_vien_action.php?q=${encodeURIComponent(q)}&khoa=${encodeURIComponent(khoa)}&nganh=${encodeURIComponent(nganh)}&lop=${encodeURIComponent(lop)}`);
      const resData = await res.json();
      
      if (resData.success) {
        svList = resData.data;
        currentPage = 1;
        renderBang(svList);
      } else {
        showToast(resData.message, 'error');
      }
    } catch (error) {
      console.error(error);
      showToast('Lỗi khi kết nối máy chủ.', 'error');
    }
  }

  // Lọc Ngành và Lớp dựa trên lựa chọn Khoa
  function locNganhVaLop() {
    const kVal = document.getElementById('filter-khoa').value;
    const filterNganh = document.getElementById('filter-nganh');
    const filterLop = document.getElementById('filter-lop');
    
    if (kVal === '') {
      filterNganh.innerHTML = '<option value="">Ngành</option>';
      filterLop.innerHTML = '<option value="">Lớp</option>';
      return;
    }
    
    // Lọc ngành thuộc khoa
    const ngs = filterData.nganh.filter(n => n.ma_khoa === kVal);
    filterNganh.innerHTML = '<option value="">Ngành</option>' + 
      ngs.map(n => `<option value="${n.ma_nganh}">${escapeHtml(n.ten_nganh)}</option>`).join('');
      
    // Lọc lớp thuộc các ngành của khoa
    const ngIds = ngs.map(n => n.ma_nganh);
    const lps = filterData.lop.filter(l => ngIds.includes(l.ma_nganh));
    filterLop.innerHTML = '<option value="">Lớp</option>' + 
      lps.map(l => `<option value="${l.ma_lop_sv}">${escapeHtml(l.ten_lop)}</option>`).join('');
  }

  // Lọc Lớp dựa trên lựa chọn Ngành
  function locLop() {
    const nVal = document.getElementById('filter-nganh').value;
    const filterLop = document.getElementById('filter-lop');
    
    if (nVal === '') {
      locNganhVaLop();
      return;
    }
    
    const lps = filterData.lop.filter(l => l.ma_nganh === nVal);
    filterLop.innerHTML = '<option value="">Lớp</option>' + 
      lps.map(l => `<option value="${l.ma_lop_sv}">${escapeHtml(l.ten_lop)}</option>`).join('');
  }

  // Render bảng với phân trang 6 sinh viên/trang
  function renderBang(ds) {
    const tbody = document.getElementById('sinh-vien-tbody');
    const info = document.getElementById('pagination-info');
    const btnsContainer = document.getElementById('pagination-btns');
    
    if (!ds || ds.length === 0) {
      tbody.innerHTML = `<tr><td colspan="7" style="text-align: center; color: #888; padding: 20px;">Không tìm thấy sinh viên nào.</td></tr>`;
      info.textContent = 'Hiển thị 0-0 trong số 0 sinh viên';
      if (btnsContainer) btnsContainer.innerHTML = '';
      return;
    }

    const totalItems = ds.length;
    const totalPages = Math.ceil(totalItems / pageSize) || 1;
    if (currentPage > totalPages) currentPage = totalPages;
    if (currentPage < 1) currentPage = 1;

    const startIdx = (currentPage - 1) * pageSize;
    const endIdx = Math.min(startIdx + pageSize, totalItems);
    const pageItems = ds.slice(startIdx, endIdx);

    tbody.innerHTML = pageItems.map(sv => {
      let statusText = 'Đang học';
      let statusClass = 'active';
      if (sv.trang_thai === 'TAM_DUNG') {
        statusText = 'Tạm dừng';
        statusClass = 'pending';
      } else if (sv.trang_thai === 'THOI_HOC' || sv.trang_thai === 'DA_TOT_NGHIEP') {
        statusText = sv.trang_thai === 'DA_TOT_NGHIEP' ? 'Tốt nghiệp' : 'Thôi học';
        statusClass = 'closed';
      }

      return `
        <tr>
          <td><strong style="color: #1e4d8c;">${escapeHtml(sv.ma_sv)}</strong></td>
          <td><strong>${escapeHtml(sv.ho_ten)}</strong></td>
          <td>${escapeHtml(sv.email || '-')}</td>
          <td>${escapeHtml(sv.ten_lop)}</td>
          <td>${escapeHtml(sv.ten_khoa)}</td>
          <td><span class="badge-status ${statusClass}">${statusText}</span></td>
          <td style="text-align: center;">
            <div class="action-icons" style="justify-content: center;">
              <button class="btn-action edit" onclick="suaSinhVien('${sv.ma_sv}')" title="Sửa">✏️</button>
              <button class="btn-action delete" onclick="xoaSinhVien('${sv.ma_sv}')" title="Xóa">🗑️</button>
            </div>
          </td>
        </tr>
      `;
    }).join('');

    info.textContent = `Hiển thị ${startIdx + 1}-${endIdx} trong số ${totalItems} sinh viên`;

    // Render các nút bấm chuyển trang
    if (btnsContainer) {
      let btnsHtml = '';
      
      // Nút Trước
      btnsHtml += `<button class="btn-page" ${currentPage === 1 ? 'disabled style="opacity:0.5;cursor:not-allowed;"' : ''} onclick="chuyenTrang(${currentPage - 1})">‹ Trước</button>`;

      let startPage = Math.max(1, currentPage - 2);
      let endPage = Math.min(totalPages, currentPage + 2);

      if (startPage > 1) {
        btnsHtml += `<button class="btn-page ${currentPage === 1 ? 'active' : ''}" onclick="chuyenTrang(1)">1</button>`;
        if (startPage > 2) {
          btnsHtml += `<span style="padding: 3px 5px; color: #94a3b8;">...</span>`;
        }
      }

      for (let p = startPage; p <= endPage; p++) {
        btnsHtml += `<button class="btn-page ${p === currentPage ? 'active' : ''}" onclick="chuyenTrang(${p})">${p}</button>`;
      }

      if (endPage < totalPages) {
        if (endPage < totalPages - 1) {
          btnsHtml += `<span style="padding: 3px 5px; color: #94a3b8;">...</span>`;
        }
        btnsHtml += `<button class="btn-page ${currentPage === totalPages ? 'active' : ''}" onclick="chuyenTrang(${totalPages})">${totalPages}</button>`;
      }

      // Nút Sau
      btnsHtml += `<button class="btn-page" ${currentPage === totalPages ? 'disabled style="opacity:0.5;cursor:not-allowed;"' : ''} onclick="chuyenTrang(${currentPage + 1})">Sau ›</button>`;

      btnsContainer.innerHTML = btnsHtml;
    }
  }

  function chuyenTrang(page) {
    const totalPages = Math.ceil(svList.length / pageSize) || 1;
    if (page < 1 || page > totalPages) return;
    currentPage = page;
    renderBang(svList);
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }

  // Tải CSV mẫu
  function taiCSVMau() {
    const content = "ma_sv,ho_ten,email,so_dien_thoai,ma_lop_sv,ngay_sinh,gioi_tinh\nSV001,Nguyen Van A,anh.nv@student.hnda.edu.vn,0987654321,K65-CNTT,2005-01-15,Nam\nSV002,Le Thi B,b.lt@student.hnda.edu.vn,0987654322,K65-CNTT,2005-08-20,Nữ\n";
    const blob = new Blob([content], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement("a");
    const url = URL.createObjectURL(blob);
    link.setAttribute("href", url);
    link.setAttribute("download", "student_template.csv");
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  }

  // Cập nhật tên file tải lên khi import
  function capNhatTenFileImport(input) {
    const label = document.getElementById('import-file-label');
    if (input.files && input.files[0]) {
      label.textContent = "Đã chọn: " + input.files[0].name;
      label.style.color = "#10b981";
      label.style.fontWeight = "bold";
    } else {
      label.textContent = "Kéo thả tệp CSV vào đây hoặc Click để duyệt";
      label.style.color = "";
      label.style.fontWeight = "";
    }
  }

  // Mở modal import
  function moModalImport() {
    document.getElementById('form-import-sv').reset();
    document.getElementById('import-results').style.display = 'none';
    document.getElementById('import-file-label').textContent = "Kéo thả tệp CSV vào đây hoặc Click để duyệt";
    document.getElementById('import-file-label').style.color = "";
    document.getElementById('import-file-label').style.fontWeight = "";
    
    document.getElementById('modal-import-sinh-vien').style.display = 'flex';
    document.getElementById('modal-backdrop-sv').style.display = 'block';
  }

  // Đóng modal import
  function dongModalImport() {
    document.getElementById('modal-import-sinh-vien').style.display = 'none';
    document.getElementById('modal-backdrop-sv').style.display = 'none';
  }

  // Xử lý gửi file import
  async function guiFormImport(e) {
    e.preventDefault();
    const fileInput = document.getElementById('import-file');
    if (!fileInput.files[0]) return;

    const formData = new FormData();
    formData.append('file', fileInput.files[0]);

    try {
      const res = await fetch('../api/admin/sinh_vien_action.php?import=1', {
        method: 'POST',
        body: formData
      });
      const data = await res.json();

      const resultBox = document.getElementById('import-results');
      resultBox.style.display = 'block';
      
      if (data.success) {
        let htmlLog = `<p style="color: #166534; font-weight: bold;">✅ ${data.message}</p>`;
        if (data.errors && data.errors.length > 0) {
          htmlLog += `<p style="color: #991b1b; font-weight: bold; margin-top: 10px;">⚠️ Lỗi tại một số dòng:</p><ul style="padding-left: 15px; margin-top:5px; color:#991b1b;">`;
          htmlLog += data.errors.map(err => `<li>${escapeHtml(err)}</li>`).join('');
          htmlLog += `</ul>`;
        }
        resultBox.innerHTML = htmlLog;
        showToast(data.message, 'success');
        taiSinhVien();
      } else {
        resultBox.innerHTML = `<p style="color: #991b1b; font-weight: bold;">❌ ${data.message}</p>`;
        showToast(data.message, 'error');
      }
    } catch (error) {
      console.error(error);
      showToast('Lỗi kết nối đến máy chủ.', 'error');
    }
  }

  // Mở modal thêm tay
  function moModalThem() {
    document.getElementById('form-sinh-vien').reset();
    document.getElementById('sv-is-update').value = 'false';
    document.getElementById('sv-ma').readOnly = false;
    document.getElementById('sv-ma').style.backgroundColor = '';
    
    document.getElementById('modal-title').textContent = 'Thêm sinh viên mới';
    document.getElementById('btn-submit-sv').textContent = 'Thêm sinh viên';
    
    document.getElementById('modal-sinh-vien').style.display = 'flex';
    document.getElementById('modal-backdrop-sv').style.display = 'block';
  }

  // Đóng modal thêm tay
  function dongModal() {
    document.getElementById('modal-sinh-vien').style.display = 'none';
    document.getElementById('modal-backdrop-sv').style.display = 'none';
  }

  // Chỉnh sửa sinh viên
  function suaSinhVien(ma) {
    const sv = svList.find(x => x.ma_sv === ma);
    if (!sv) return;

    document.getElementById('sv-is-update').value = 'true';
    document.getElementById('sv-ma').value = sv.ma_sv;
    document.getElementById('sv-ma').readOnly = true;
    document.getElementById('sv-ma').style.backgroundColor = '#f1f5f9';

    document.getElementById('sv-ho-ten').value = sv.ho_ten;
    document.getElementById('sv-email').value = sv.email || '';
    document.getElementById('sv-sdt').value = sv.so_dien_thoai || '';
    document.getElementById('sv-ngay-sinh').value = sv.ngay_sinh || '';
    let gt = (sv.gioi_tinh || 'NAM').toUpperCase();
    if (gt === 'NỮ' || gt === 'NU') gt = 'NU';
    else if (gt === 'KHÁC' || gt === 'KHAC') gt = 'KHAC';
    else gt = 'NAM';
    document.getElementById('sv-gioi-tinh').value = gt;
    document.getElementById('sv-lop').value = sv.ma_lop_sv;
    document.getElementById('sv-trang-thai').value = sv.trang_thai;

    document.getElementById('modal-title').textContent = 'Chỉnh sửa thông tin sinh viên';
    document.getElementById('btn-submit-sv').textContent = 'Lưu thay đổi';

    document.getElementById('modal-sinh-vien').style.display = 'flex';
    document.getElementById('modal-backdrop-sv').style.display = 'block';
  }

  // Gửi Form thêm/sửa sinh viên
  async function guiFormSinhVien(e) {
    e.preventDefault();
    const isUpdate = document.getElementById('sv-is-update').value === 'true';
    
    const payload = {
      is_update: isUpdate,
      ma_sv: document.getElementById('sv-ma').value,
      ho_ten: document.getElementById('sv-ho-ten').value,
      email: document.getElementById('sv-email').value,
      so_dien_thoai: document.getElementById('sv-sdt').value,
      ngay_sinh: document.getElementById('sv-ngay-sinh').value,
      gioi_tinh: document.getElementById('sv-gioi-tinh').value,
      ma_lop_sv: document.getElementById('sv-lop').value,
      trang_thai: document.getElementById('sv-trang-thai').value
    };

    try {
      const res = await fetch('../api/admin/sinh_vien_action.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });
      const data = await res.json();
      
      if (data.success) {
        showToast(data.message, 'success');
        dongModal();
        taiSinhVien();
      } else {
        showToast(data.message, 'error');
      }
    } catch (error) {
      console.error(error);
      showToast('Lỗi kết nối máy chủ.', 'error');
    }
  }

  // Xóa sinh viên
  function xoaSinhVien(ma) {
    showConfirmModal(`Bạn có chắc chắn muốn xóa sinh viên ${ma}? Tài khoản đăng nhập của sinh viên cũng sẽ bị xóa.`, async () => {
      try {
        const res = await fetch('../api/admin/sinh_vien_action.php', {
          method: 'DELETE',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ ma_sv: ma })
        });
        const data = await res.json();
        
        if (data.success) {
          showToast(data.message, 'success');
          taiSinhVien();
        } else {
          showToast(data.message, 'error');
        }
      } catch (error) {
        console.error(error);
        showToast('Lỗi kết nối máy chủ.', 'error');
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
    await taiBoLoc();
    taiSinhVien();
    
    // Tự động mở form thêm từ tham số URL
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('add')) {
      moModalThem();
    }
  });
</script>

<?php require_once 'includes/footer.php'; ?>
