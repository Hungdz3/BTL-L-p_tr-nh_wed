<?php
// admin/thong-bao.php
require_once 'includes/header.php';
?>

<!-- Filter Bar -->
<section class="admin-filter-bar">
  <div class="filter-inputs-group">
    <input type="text" id="search-input" placeholder="Tìm kiếm thông báo..." onkeyup="if(event.key === 'Enter') taiThongBao()">
    <select id="filter-recipient" onchange="taiThongBao()">
      <option value="">Đối tượng nhận</option>
      <option value="Tất cả">Tất cả</option>
      <option value="Sinh viên">Sinh viên</option>
      <option value="Giảng viên">Giảng viên</option>
    </select>
    <button class="btn-admin-primary" onclick="taiThongBao()">Tìm kiếm</button>
  </div>
  <button class="btn-add-new" onclick="moModalThem()">+ Thêm thông báo</button>
</section>

<!-- Main Table Content -->
<section class="panel-card" style="margin: 0 28px 24px 28px;">
  <table style="width: 100%;">
    <thead>
      <tr>
        <th style="width: 60px; text-align: center;">STT</th>
        <th>Tiêu đề thông báo</th>
        <th style="width: 180px;">Đối tượng nhận</th>
        <th style="width: 150px;">Ngày đăng</th>
        <th style="width: 120px;">Trạng thái</th>
        <th style="width: 100px; text-align: center;">Thao tác</th>
      </tr>
    </thead>
    <tbody id="thong-bao-tbody">
      <!-- AJAX loads list here -->
    </tbody>
  </table>
  <div class="pagination-row">
    <div id="pagination-info">Hiển thị 1-0 trong số 0 thông báo</div>
    <div class="pagination-buttons" id="pagination-btns">
      <!-- Pagination links -->
    </div>
  </div>
</section>

<!-- Admin Custom Modal: Add/Edit Notification -->
<div class="custom-modal-admin" id="modal-thong-bao">
  <div class="modal-header-admin">
    <span id="modal-title">Thêm thông báo mới</span>
    <button class="modal-close-admin" onclick="dongModal()">×</button>
  </div>
  <form id="form-thong-bao" onsubmit="guiFormThongBao(event)">
    <div class="modal-body-admin">
      <input type="hidden" id="tb-id" name="id" value="0">
      
      <div class="form-group-admin">
        <label for="tb-tieu-de">Tiêu đề thông báo <span>*</span></label>
        <input type="text" class="form-control-admin" id="tb-tieu-de" name="tieu_de" placeholder="Nhập tiêu đề thông báo phát hành..." required>
      </div>
      
      <div class="form-group-admin">
        <label for="tb-recipient">Đối tượng nhận <span>*</span></label>
        <select class="form-control-admin" id="tb-recipient" name="doi_tuong_nhan" required>
          <option value="TAT_CA">Tất cả</option>
          <option value="SINH_VIEN">Sinh viên</option>
          <option value="GIANG_VIEN">Giảng viên</option>
        </select>
      </div>
      
      <div class="form-group-admin">
        <label for="tb-noi-dung">Nội dung thông báo <span>*</span></label>
        <textarea class="form-control-admin" id="tb-noi-dung" name="noi_dung" rows="6" placeholder="Nhập nội dung chi tiết của thông báo gửi đến sinh viên/giảng viên..." required style="resize: vertical; font-family: inherit;"></textarea>
      </div>
      
      <div class="form-group-admin">
        <label>Tệp đính kèm</label>
        <div class="file-upload-box" onclick="document.getElementById('tb-file').click()">
          <svg width="24" height="24" viewBox="0 0 24 24" style="margin: 0 auto 5px auto; display: block;">
            <path d="M19.35 10.04C18.67 6.59 15.64 4 12 4 9.11 4 6.6 5.64 5.35 8.04 2.34 8.36 0 10.91 0 14c0 3.31 2.69 6 6 6h13c2.76 0 5-2.24 5-5 0-2.64-2.05-4.78-4.65-4.96zM14 13v4h-4v-4H7l5-5 5 5h-3z"/>
          </svg>
          <span id="file-label">Kéo thả hoặc click vào đây để tải lên</span>
          <br><small style="color: #94a3b8; font-size: 10px;">Hỗ trợ các định dạng PDF, DOCX, XLSX tối đa 10MB</small>
        </div>
        <input type="file" id="tb-file" name="file_dinh_kem" style="display: none;" onchange="capNhatTenFile(this)">
      </div>
      
      <div class="form-group-admin" style="flex-direction: row; gap: 10px; align-items: center; margin-top: 10px;">
        <label style="margin-bottom: 0; cursor: pointer;">
          <input type="checkbox" id="tb-draft" name="trang_thai_checkbox" value="BAN_NHAP"> Chỉ lưu bản nháp (Chưa gửi ngay)
        </label>
      </div>
    </div>
    <div class="modal-footer-admin">
      <button type="button" class="btn-admin-secondary" onclick="dongModal()">Hủy</button>
      <button type="submit" class="btn-admin-primary" id="btn-submit-tb">Gửi thông báo</button>
    </div>
  </form>
</div>

<!-- Modal Background Layer -->
<div class="modal-backdrop" id="modal-backdrop-tb" style="display: none; z-index: 10000;" onclick="dongModal()"></div>

<script>
  let noticesList = [];

  // Tải danh sách thông báo qua API
  async function taiThongBao() {
    const q = document.getElementById('search-input').value;
    const recipient = document.getElementById('filter-recipient').value;
    
    try {
      const res = await fetch(`../api/admin/thong_bao_action.php?q=${encodeURIComponent(q)}&doi_tuong=${encodeURIComponent(recipient)}`);
      const resData = await res.json();
      
      if (resData.success) {
        noticesList = resData.data;
        renderBang(noticesList);
      } else {
        showToast(resData.message || 'Lỗi khi tải thông báo', 'error');
      }
    } catch (error) {
      console.error(error);
      showToast('Không thể kết nối đến máy chủ.', 'error');
    }
  }

  // Render bảng thông báo
  function renderBang(ds) {
    const tbody = document.getElementById('thong-bao-tbody');
    const info = document.getElementById('pagination-info');
    
    if (ds.length === 0) {
      tbody.innerHTML = `<tr><td colspan="6" style="text-align: center; color: #888; padding: 20px;">Không có thông báo nào phù hợp.</td></tr>`;
      info.textContent = 'Hiển thị 0-0 trong số 0 thông báo';
      return;
    }
    
    tbody.innerHTML = ds.map((tb, idx) => {
      let recipientText = 'Tất cả';
      if (tb.doi_tuong_nhan === 'SINH_VIEN') recipientText = 'Sinh viên';
      if (tb.doi_tuong_nhan === 'GIANG_VIEN') recipientText = 'Giảng viên';
      
      const dateText = new Date(tb.ngay_dang).toLocaleDateString('vi-VN');
      const isDraft = tb.trang_thai === 'BAN_NHAP';
      
      const statusBadge = isDraft 
        ? `<span class="badge-status pending">Bản nháp</span>`
        : `<span class="badge-status active">Đã gửi</span>`;
        
      return `
        <tr>
          <td style="text-align: center; color: #888;">${idx + 1}</td>
          <td>
            <strong>${escapeHtml(tb.tieu_de)}</strong>
            ${tb.file_dinh_kem ? ` <a href="../${tb.file_dinh_kem}" target="_blank" title="Tải tệp đính kèm" style="text-decoration:none;">📎</a>` : ''}
          </td>
          <td>${recipientText}</td>
          <td>${dateText}</td>
          <td>${statusBadge}</td>
          <td style="text-align: center;">
            <div class="action-icons" style="justify-content: center;">
              <button class="btn-action edit" onclick="suaThongBao(${tb.id})" title="Chỉnh sửa">✏️</button>
              <button class="btn-action delete" onclick="xoaThongBao(${tb.id})" title="Xóa">🗑️</button>
            </div>
          </td>
        </tr>
      `;
    }).join('');
    
    info.textContent = `Hiển thị 1-${ds.length} trong số ${ds.length} thông báo`;
  }

  // Cập nhật tên file tải lên
  function capNhatTenFile(input) {
    const label = document.getElementById('file-label');
    if (input.files && input.files[0]) {
      label.textContent = "Đã chọn: " + input.files[0].name;
      label.style.color = "#1e4d8c";
      label.style.fontWeight = "bold";
    } else {
      label.textContent = "Kéo thả hoặc click vào đây để tải lên";
      label.style.color = "";
      label.style.fontWeight = "";
    }
  }

  // Mở modal thêm mới
  function moModalThem() {
    document.getElementById('form-thong-bao').reset();
    document.getElementById('tb-id').value = 0;
    document.getElementById('modal-title').textContent = 'Thêm thông báo mới';
    document.getElementById('btn-submit-tb').textContent = 'Gửi thông báo';
    document.getElementById('file-label').textContent = "Kéo thả hoặc click vào đây để tải lên";
    document.getElementById('file-label').style.color = "";
    document.getElementById('file-label').style.fontWeight = "";
    
    document.getElementById('modal-thong-bao').style.display = 'flex';
    document.getElementById('modal-backdrop-tb').style.display = 'block';
  }

  // Đóng modal
  function dongModal() {
    document.getElementById('modal-thong-bao').style.display = 'none';
    document.getElementById('modal-backdrop-tb').style.display = 'none';
  }

  // Chỉnh sửa thông báo
  function suaThongBao(id) {
    const tb = noticesList.find(x => x.id === id);
    if (!tb) return;
    
    document.getElementById('tb-id').value = tb.id;
    document.getElementById('tb-tieu-de').value = tb.tieu_de;
    document.getElementById('tb-recipient').value = tb.doi_tuong_nhan;
    document.getElementById('tb-noi-dung').value = tb.noi_dung;
    document.getElementById('tb-draft').checked = tb.trang_thai === 'BAN_NHAP';
    
    document.getElementById('modal-title').textContent = 'Chỉnh sửa thông báo';
    document.getElementById('btn-submit-tb').textContent = 'Lưu thay đổi';
    
    if (tb.file_dinh_kem) {
      document.getElementById('file-label').textContent = "Đã có tệp đính kèm (Click để thay đổi tệp mới)";
      document.getElementById('file-label').style.color = "#2e7d32";
    } else {
      document.getElementById('file-label').textContent = "Kéo thả hoặc click vào đây để tải lên";
      document.getElementById('file-label').style.color = "";
    }
    
    document.getElementById('modal-thong-bao').style.display = 'flex';
    document.getElementById('modal-backdrop-tb').style.display = 'block';
  }

  // Gửi Form thêm/sửa thông báo (FormData để hỗ trợ tải file)
  async function guiFormThongBao(e) {
    e.preventDefault();
    
    const form = document.getElementById('form-thong-bao');
    const formData = new FormData(form);
    
    // Thiết lập trạng thái
    const draftChk = document.getElementById('tb-draft').checked;
    formData.set('trang_thai', draftChk ? 'BAN_NHAP' : 'DA_GUI');
    
    try {
      const res = await fetch('../api/admin/thong_bao_action.php', {
        method: 'POST',
        body: formData
      });
      const data = await res.json();
      
      if (data.success) {
        showToast(data.message, 'success');
        dongModal();
        taiThongBao();
      } else {
        showToast(data.message || 'Lỗi khi lưu thông báo', 'error');
      }
    } catch (error) {
      console.error(error);
      showToast('Lỗi kết nối máy chủ.', 'error');
    }
  }

  // Xóa thông báo
  function xoaThongBao(id) {
    showConfirmModal('Bạn có chắc chắn muốn xóa thông báo này? Tệp đính kèm liên quan cũng sẽ bị xóa.', async () => {
      try {
        const res = await fetch('../api/admin/thong_bao_action.php', {
          method: 'DELETE',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ id: id })
        });
        const data = await res.json();
        
        if (data.success) {
          showToast(data.message, 'success');
          taiThongBao();
        } else {
          showToast(data.message || 'Lỗi khi xóa thông báo', 'error');
        }
      } catch (error) {
        console.error(error);
        showToast('Lỗi kết nối máy chủ.', 'error');
      }
    });
  }

  // XSS protection escape html
  function escapeHtml(string) {
    const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
    return String(string).replace(/[&<>"']/g, m => map[m]);
  }

  // Khởi chạy khi tải xong trang
  document.addEventListener('DOMContentLoaded', () => {
    taiThongBao();
    
    // Tự động mở modal nếu tham số URL yêu cầu
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('add')) {
      moModalThem();
    }
  });
</script>

<?php require_once 'includes/footer.php'; ?>
