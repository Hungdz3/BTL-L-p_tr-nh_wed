<?php
// admin/giang-vien.php
require_once 'includes/header.php';
?>

<!-- Filter Bar -->
<section class="admin-filter-bar">
  <div class="filter-inputs-group">
    <input type="text" id="search-input" placeholder="Tìm giảng viên..." onkeyup="if(event.key === 'Enter') taiGiangVien()">
    <select id="filter-khoa" onchange="taiGiangVien()">
      <option value="">Khoa</option>
      <!-- Loaded dynamically -->
    </select>
    <button class="btn-admin-primary" onclick="taiGiangVien()">Tìm kiếm</button>
  </div>
  <button class="btn-add-new" onclick="moModalThem()">+ Thêm giảng viên</button>
</section>

<!-- Table Content -->
<section class="panel-card" style="margin: 0 28px 24px 28px;">
  <table style="width: 100%;">
    <thead>
      <tr>
        <th style="width: 120px;">Mã GV</th>
        <th>Tên giảng viên</th>
        <th>Email</th>
        <th style="width: 250px;">Khoa</th>
        <th style="width: 150px;">Trạng thái</th>
        <th style="width: 100px; text-align: center;">Thao tác</th>
      </tr>
    </thead>
    <tbody id="giang-vien-tbody">
      <!-- AJAX loads list here -->
    </tbody>
  </table>
  <div class="pagination-row">
    <div id="pagination-info">Hiển thị 1-0 trong số 0 giảng viên</div>
    <div class="pagination-buttons" id="pagination-btns"></div>
  </div>
</section>

<!-- Modal Add/Edit Teacher -->
<div class="custom-modal-admin" id="modal-giang-vien" style="max-width: 500px;">
  <div class="modal-header-admin">
    <span id="modal-title">Thêm giảng viên mới</span>
    <button class="modal-close-admin" onclick="dongModal()">×</button>
  </div>
  <form id="form-giang-vien" onsubmit="guiFormGiangVien(event)">
    <div class="modal-body-admin">
      <input type="hidden" id="gv-is-update" value="false">
      
      <div class="form-group-admin">
        <label for="gv-ma">Mã giảng viên <span>*</span></label>
        <input type="text" class="form-control-admin" id="gv-ma" required placeholder="Ví dụ: GV001">
      </div>

      <div class="form-group-admin">
        <label for="gv-ho-ten">Họ tên giảng viên <span>*</span></label>
        <input type="text" class="form-control-admin" id="gv-ho-ten" required placeholder="Ví dụ: Nguyễn Thị Lan">
      </div>

      <div class="form-group-admin">
        <label for="gv-email">Email</label>
        <input type="email" class="form-control-admin" id="gv-email" placeholder="Ví dụ: lan.nt@hnda.edu.vn">
      </div>

      <div class="form-group-admin">
        <label for="gv-sdt">Số điện thoại</label>
        <input type="text" class="form-control-admin" id="gv-sdt" placeholder="Ví dụ: 0912345678">
      </div>

      <div class="form-group-admin">
        <label for="gv-khoa">Khoa chủ quản <span>*</span></label>
        <select class="form-control-admin" id="gv-khoa" required>
          <!-- Loaded dynamically -->
        </select>
      </div>

      <div class="form-group-admin">
        <label for="gv-trang-thai">Trạng thái <span>*</span></label>
        <select class="form-control-admin" id="gv-trang-thai" required>
          <option value="HOAT_DONG">Hoạt động</option>
          <option value="NGUNG_HOAT_DONG">Ngưng hoạt động</option>
          <option value="TAM_NGHI">Tạm nghỉ</option>
          <option value="NGHI_HUC">Nghỉ hưu</option>
        </select>
      </div>

    </div>
    <div class="modal-footer-admin">
      <button type="button" class="btn-admin-secondary" onclick="dongModal()">Hủy</button>
      <button type="submit" class="btn-admin-primary" id="btn-submit-gv">Thêm giảng viên</button>
    </div>
  </form>
</div>

<!-- Modal Backdrop -->
<div class="modal-backdrop" id="modal-backdrop-gv" style="display: none; z-index: 10000;" onclick="dongModal()"></div>

<script>
  let gvList = [];
  let filterKhoa = [];
  let currentPage = 1;
  const pageSize = 6;

  // Tải danh sách Khoa phục vụ lọc
  async function taiBoLoc() {
    try {
      const res = await fetch('../api/admin/sinh_vien_action.php?get_filters=1');
      const data = await res.json();
      if (data.success) {
        filterKhoa = data.khoa;
        
        // Populate Khoa vào filter bar
        const selFilter = document.getElementById('filter-khoa');
        selFilter.innerHTML = '<option value="">Tất cả khoa</option>' + 
          filterKhoa.map(k => `<option value="${k.ma_khoa}">${escapeHtml(k.ten_khoa)}</option>`).join('');
          
        // Populate Khoa vào Form modal
        const selModal = document.getElementById('gv-khoa');
        selModal.innerHTML = filterKhoa.map(k => `<option value="${k.ma_khoa}">${escapeHtml(k.ten_khoa)}</option>`).join('');
      }
    } catch (e) {
      console.error(e);
    }
  }

  // Tải danh sách giảng viên qua API
  async function taiGiangVien() {
    const q = document.getElementById('search-input').value;
    const khoa = document.getElementById('filter-khoa').value;
    
    try {
      const res = await fetch(`../api/admin/giang_vien_action.php?q=${encodeURIComponent(q)}&khoa=${encodeURIComponent(khoa)}`);
      const resData = await res.json();
      if (resData.success) {
        gvList = resData.data;
        currentPage = 1;
        renderBang(gvList);
      } else {
        showToast(resData.message, 'error');
      }
    } catch (e) {
      console.error(e);
      showToast('Lỗi khi tải danh sách giảng viên.', 'error');
    }
  }

  // Render bảng với phân trang 6 giảng viên/trang
  function renderBang(ds) {
    const tbody = document.getElementById('giang-vien-tbody');
    const info = document.getElementById('pagination-info');
    const btnsContainer = document.getElementById('pagination-btns');
    
    if (!ds || ds.length === 0) {
      tbody.innerHTML = `<tr><td colspan="6" style="text-align: center; color: #888; padding: 20px;">Không tìm thấy giảng viên nào.</td></tr>`;
      info.textContent = 'Hiển thị 0-0 trong số 0 giảng viên';
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

    tbody.innerHTML = pageItems.map(gv => {
      let statusText = 'Hoạt động';
      let statusClass = 'active';
      if (gv.trang_thai === 'TAM_NGHI') {
        statusText = 'Tạm nghỉ';
        statusClass = 'pending';
      } else if (gv.trang_thai === 'NGHI_HUC' || gv.trang_thai === 'NGUNG_HOAT_DONG') {
        statusText = gv.trang_thai === 'NGHI_HUC' ? 'Nghỉ hưu' : 'Ngưng hoạt động';
        statusClass = 'closed';
      }

      return `
        <tr>
          <td><strong style="color: #1e4d8c;">${escapeHtml(gv.ma_gv)}</strong></td>
          <td><strong>${escapeHtml(gv.ho_ten)}</strong></td>
          <td>${escapeHtml(gv.email || '-')}</td>
          <td>${escapeHtml(gv.ten_khoa)}</td>
          <td><span class="badge-status ${statusClass}">${statusText}</span></td>
          <td style="text-align: center;">
            <div class="action-icons" style="justify-content: center;">
              <button class="btn-action edit" onclick="suaGiangVien('${gv.ma_gv}')" title="Sửa">✏️</button>
              <button class="btn-action delete" onclick="xoaGiangVien('${gv.ma_gv}')" title="Xóa">🗑️</button>
            </div>
          </td>
        </tr>
      `;
    }).join('');

    info.textContent = `Hiển thị ${startIdx + 1}-${endIdx} trong số ${totalItems} giảng viên`;

    // Render các nút bấm chuyển trang
    if (btnsContainer) {
      let btnsHtml = '';
      
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

      btnsHtml += `<button class="btn-page" ${currentPage === totalPages ? 'disabled style="opacity:0.5;cursor:not-allowed;"' : ''} onclick="chuyenTrang(${currentPage + 1})">Sau ›</button>`;

      btnsContainer.innerHTML = btnsHtml;
    }
  }

  function chuyenTrang(page) {
    const totalPages = Math.ceil(gvList.length / pageSize) || 1;
    if (page < 1 || page > totalPages) return;
    currentPage = page;
    renderBang(gvList);
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }

  // Mở modal thêm
  function moModalThem() {
    document.getElementById('form-giang-vien').reset();
    document.getElementById('gv-is-update').value = 'false';
    document.getElementById('gv-ma').readOnly = false;
    document.getElementById('gv-ma').style.backgroundColor = '';
    
    document.getElementById('modal-title').textContent = 'Thêm giảng viên mới';
    document.getElementById('btn-submit-gv').textContent = 'Thêm giảng viên';
    
    document.getElementById('modal-giang-vien').style.display = 'flex';
    document.getElementById('modal-backdrop-gv').style.display = 'block';
  }

  // Đóng modal
  function dongModal() {
    document.getElementById('modal-giang-vien').style.display = 'none';
    document.getElementById('modal-backdrop-gv').style.display = 'none';
  }

  // Sửa giảng viên
  function suaGiangVien(ma) {
    const gv = gvList.find(x => x.ma_gv === ma);
    if (!gv) return;

    document.getElementById('gv-is-update').value = 'true';
    document.getElementById('gv-ma').value = gv.ma_gv;
    document.getElementById('gv-ma').readOnly = true;
    document.getElementById('gv-ma').style.backgroundColor = '#f1f5f9';

    document.getElementById('gv-ho-ten').value = gv.ho_ten;
    document.getElementById('gv-email').value = gv.email || '';
    document.getElementById('gv-sdt').value = gv.so_dien_thoai || '';
    document.getElementById('gv-khoa').value = gv.ma_khoa;
    document.getElementById('gv-trang-thai').value = gv.trang_thai;

    document.getElementById('modal-title').textContent = 'Chỉnh sửa giảng viên';
    document.getElementById('btn-submit-gv').textContent = 'Lưu thay đổi';

    document.getElementById('modal-giang-vien').style.display = 'flex';
    document.getElementById('modal-backdrop-gv').style.display = 'block';
  }

  // Gửi form
  async function guiFormGiangVien(e) {
    e.preventDefault();
    const isUpdate = document.getElementById('gv-is-update').value === 'true';
    
    const payload = {
      is_update: isUpdate,
      ma_gv: document.getElementById('gv-ma').value,
      ho_ten: document.getElementById('gv-ho-ten').value,
      email: document.getElementById('gv-email').value,
      so_dien_thoai: document.getElementById('gv-sdt').value,
      ma_khoa: document.getElementById('gv-khoa').value,
      trang_thai: document.getElementById('gv-trang-thai').value
    };

    try {
      const res = await fetch('../api/admin/giang_vien_action.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });
      const data = await res.json();
      if (data.success) {
        showToast(data.message, 'success');
        dongModal();
        taiGiangVien();
      } else {
        showToast(data.message, 'error');
      }
    } catch (error) {
      console.error(error);
      showToast('Lỗi kết nối máy chủ.', 'error');
    }
  }

  // Xóa giảng viên
  function xoaGiangVien(ma) {
    showConfirmModal(`Bạn có chắc chắn muốn xóa giảng viên ${ma}? Tài khoản đăng nhập tương ứng cũng sẽ bị xóa.`, async () => {
      try {
        const res = await fetch('../api/admin/giang_vien_action.php', {
          method: 'DELETE',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ ma_gv: ma })
        });
        const data = await res.json();
        if (data.success) {
          showToast(data.message, 'success');
          taiGiangVien();
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
    taiGiangVien();
    
    // Tự động mở form từ tham số URL
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('add')) {
      moModalThem();
    }
  });
</script>

<?php require_once 'includes/footer.php'; ?>
