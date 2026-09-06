<?php
// admin/dang-ky-hp.php
require_once 'includes/header.php';
?>

<div style="margin: 20px 28px; padding: 0; max-width: 1600px;">

    <!-- Top Title Header -->
    <div style="margin-bottom: 20px;">
        <h2 style="margin: 0; font-size: 22px; font-weight: 800; color: #0f172a;">QUẢN LÝ ĐỢT ĐĂNG KÝ HỌC PHẦN</h2>
        <p style="margin: 4px 0 0 0; font-size: 13px; color: #64748b;">Cập nhật và quản lý các đợt đăng ký học phần cho sinh viên.</p>
    </div>

    <!-- Main Split Container (Left List + Right Side Panel) -->
    <div id="registration-main-container" style="display: grid; grid-template-columns: 1fr; gap: 24px; align-items: start; transition: grid-template-columns 0.3s ease;">

        <!-- CỘT TRÁI: DANH SÁCH ĐỢT ĐĂNG KÝ -->
        <div>
            <!-- Main Card Container -->
            <div style="background: white; border-radius: 12px; border: 1px solid #e2e8f0; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.03); margin-bottom: 20px;">
                
                <!-- Header Filter & Add Button Bar -->
                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 16px;">
                    <h3 style="margin: 0; font-size: 14px; font-weight: 800; color: #1E3A8A; text-transform: uppercase; letter-spacing: 0.3px;">DANH SÁCH ĐỢT ĐĂNG KÝ</h3>
                    
                    <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                        <input type="text" id="search-input" placeholder="🔍 Tìm kiếm theo mã hoặc tên đợt..." onkeyup="if(event.key === 'Enter') taiDotDangKy()" style="width: 250px; padding: 8px 14px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; outline: none;">
                        
                        <select id="status-filter" onchange="taiDotDangKy()" style="padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; outline: none; background: white;">
                            <option value="">Trạng thái: Tất cả</option>
                            <option value="HOAT_DONG">Đang diễn ra</option>
                            <option value="CHO_DUYET">Chưa bắt đầu</option>
                            <option value="DA_DONG">Đã kết thúc</option>
                        </select>
                        
                        <button type="button" onclick="hienThiPanelThem(true)" style="background: #1E3A8A; color: white; border: none; padding: 8px 16px; border-radius: 6px; font-weight: 600; font-size: 13px; cursor: pointer; display: flex; align-items: center; gap: 6px;">
                            + Thêm đợt đăng ký
                        </button>
                    </div>
                </div>

                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 13px; table-layout: auto;">
                        <thead>
                            <tr style="background: #f8fafc; border-bottom: 2px solid #e2e8f0; font-size: 11px; color: #475569; text-transform: uppercase; text-align: left;">
                                <th style="padding: 10px 8px; width: 36px; text-align: center;">STT</th>
                                <th style="padding: 10px 8px; white-space: nowrap;">MÃ ĐỢT</th>
                                <th style="padding: 10px 8px;">TÊN ĐỢT ĐĂNG KÝ</th>
                                <th style="padding: 10px 8px; white-space: nowrap;">THỜI GIAN BẮT ĐẦU</th>
                                <th style="padding: 10px 8px; white-space: nowrap;">THỜI GIAN KẾT THÚC</th>
                                <th style="padding: 10px 8px; white-space: nowrap; text-align: center;">TRẠNG THÁI</th>
                                <th style="padding: 10px 8px; white-space: nowrap; text-align: center;">SỐ LỚP MỞ</th>
                                <th style="padding: 10px 8px; white-space: nowrap; text-align: center;">SỐ HỌC PHẦN</th>
                                <th style="padding: 10px 8px; white-space: nowrap; text-align: center;">THAO TÁC</th>
                            </tr>
                        </thead>
                        <tbody id="dot-dang-ky-tbody">
                            <!-- Loaded via AJAX -->
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Footer -->
                <div style="display: flex; align-items: center; justify-content: space-between; font-size: 12px; color: #64748b; margin-top: 16px; padding-top: 12px; border-top: 1px solid #f1f5f9;">
                    <div id="pagination-info">Hiển thị 1-6 trên 6 đợt đăng ký</div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <div id="pagination-btns" style="display: flex; gap: 4px;"></div>
                        <select style="padding: 4px 8px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 12px; background: white;">
                            <option>10 / trang</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Notice Info Box -->
            <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 10px; padding: 16px 20px; font-size: 12.5px; color: #1e3a8a;">
                <strong style="display: block; margin-bottom: 6px; font-size: 13px;">🛈 Lưu ý:</strong>
                <ul style="margin: 0; padding-left: 18px; line-height: 1.6; color: #1e40af;">
                    <li>Chỉ đợt đăng ký ở trạng thái <strong>"Đang diễn ra"</strong> sinh viên mới có thể đăng ký học phần.</li>
                    <li>Sau khi kết thúc đợt đăng ký, hệ thống sẽ tự động khóa, sinh viên không thể đăng ký mới hoặc thay đổi.</li>
                    <li>Bạn có thể chỉnh sửa hoặc xóa thông tin đợt đăng ký khi đợt đó ở trạng thái <strong>"Chưa bắt đầu"</strong>.</li>
                </ul>
            </div>
        </div>

        <!-- CỘT PHẢI: PANEL THÊM / CHỈNH SỬA ĐỢT ĐĂNG KÝ HỌC PHẦN (Hiển thị khi bấm + Thêm) -->
        <div id="side-panel-add-edit" style="display: none; background: white; border-radius: 12px; border: 1px solid #e2e8f0; padding: 20px; box-shadow: 0 4px 16px rgba(0,0,0,0.06); width: 380px;">
            <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #f1f5f9; padding-bottom: 12px; margin-bottom: 16px;">
                <h3 id="side-panel-title" style="margin: 0; font-size: 14px; font-weight: 800; color: #1E3A8A; text-transform: uppercase;">THÊM ĐỢT ĐĂNG KÝ HỌC PHẦN</h3>
                <button type="button" onclick="hienThiPanelThem(false)" style="background: none; border: none; font-size: 18px; cursor: pointer; color: #94a3b8;">×</button>
            </div>

            <form id="form-side-panel" onsubmit="guiFormDotDangKy(event)">
                <input type="hidden" id="dk-is-update" value="false">
                <input type="hidden" id="dk-id-hoc-ky" value="1">

                <!-- Mã đợt đăng ký -->
                <div style="margin-bottom: 14px;">
                    <label style="display: block; font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 4px;">Mã đợt đăng ký <span style="color: #dc2626;">*</span></label>
                    <input type="text" id="dk-ma-dot" placeholder="Nhập mã đợt đăng ký (ví dụ: DKHP2024-01)" required style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; outline: none;">
                </div>

                <!-- Tên đợt đăng ký -->
                <div style="margin-bottom: 14px;">
                    <label style="display: block; font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 4px;">Tên đợt đăng ký <span style="color: #dc2626;">*</span></label>
                    <input type="text" id="dk-ten-dot" placeholder="Nhập tên đợt đăng ký" required style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; outline: none;">
                </div>

                <!-- Thời gian đăng ký -->
                <div style="margin-bottom: 14px;">
                    <label style="display: block; font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 4px;">Thời gian đăng ký <span style="color: #dc2626;">*</span></label>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                        <div>
                            <span style="font-size: 11px; color: #64748b; display: block; margin-bottom: 2px;">Bắt đầu</span>
                            <input type="datetime-local" id="dk-ngay-bd" min="2024-01-01T00:00" required style="width: 100%; padding: 6px 8px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 11.5px; outline: none;">
                        </div>
                        <div>
                            <span style="font-size: 11px; color: #64748b; display: block; margin-bottom: 2px;">Kết thúc</span>
                            <input type="datetime-local" id="dk-ngay-kt" min="2024-01-01T00:00" required style="width: 100%; padding: 6px 8px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 11.5px; outline: none;">
                        </div>
                    </div>
                </div>

                <!-- Chọn học phần mở đăng ký -->
                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 4px;">Chọn học phần mở đăng ký <span style="color: #dc2626;">*</span></label>
                    <div style="display: flex; gap: 6px; margin-bottom: 8px;">
                        <select id="select-mon-hoc" style="flex: 1; padding: 8px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 12.5px; outline: none; background: white;">
                            <option value="">Chọn học phần</option>
                        </select>
                        <button type="button" onclick="themHocPhanVaoDanhSach()" style="background: #1E3A8A; color: white; border: none; padding: 8px 14px; border-radius: 6px; font-weight: 600; font-size: 12px; cursor: pointer;">+ Thêm</button>
                    </div>

                    <!-- Bảng học phần đã chọn -->
                    <div style="border: 1px solid #e2e8f0; border-radius: 6px; max-height: 120px; overflow-y: auto; background: #fafafa;">
                        <table style="width: 100%; border-collapse: collapse; font-size: 11.5px;">
                            <thead>
                                <tr style="background: #f1f5f9; text-align: left; color: #475569;">
                                    <th style="padding: 6px 8px;">MÃ HỌC PHẦN</th>
                                    <th style="padding: 6px 8px;">TÊN HỌC PHẦN</th>
                                    <th style="padding: 6px 8px; text-align: center;">THAO TÁC</th>
                                </tr>
                            </thead>
                            <tbody id="tbody-mon-hoc-select">
                                <tr>
                                    <td colspan="3" style="text-align: center; padding: 20px; color: #94a3b8;">
                                        <div style="font-size: 20px; margin-bottom: 4px;">📥</div>
                                        Chưa có mục nào được chọn
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Chọn lớp học phần mở đăng ký -->
                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 4px;">Chọn lớp học phần mở đăng ký <span style="color: #dc2626;">*</span></label>
                    <div style="display: flex; gap: 6px; margin-bottom: 8px;">
                        <select id="select-lop-hp" style="flex: 1; padding: 8px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 12.5px; outline: none; background: white;">
                            <option value="">Chọn lớp học phần</option>
                        </select>
                        <button type="button" onclick="themLopHocPhanVaoDanhSach()" style="background: #1E3A8A; color: white; border: none; padding: 8px 14px; border-radius: 6px; font-weight: 600; font-size: 12px; cursor: pointer;">+ Thêm</button>
                    </div>

                    <!-- Bảng lớp học phần đã chọn -->
                    <div style="border: 1px solid #e2e8f0; border-radius: 6px; max-height: 120px; overflow-y: auto; background: #fafafa;">
                        <table style="width: 100%; border-collapse: collapse; font-size: 11.5px;">
                            <thead>
                                <tr style="background: #f1f5f9; text-align: left; color: #475569;">
                                    <th style="padding: 6px 8px;">MÃ LỚP</th>
                                    <th style="padding: 6px 8px;">TÊN LỚP</th>
                                    <th style="padding: 6px 8px; text-align: center;">THAO TÁC</th>
                                </tr>
                            </thead>
                            <tbody id="tbody-lop-hp-select">
                                <tr>
                                    <td colspan="3" style="text-align: center; padding: 20px; color: #94a3b8;">
                                        <div style="font-size: 20px; margin-bottom: 4px;">📥</div>
                                        Chưa có mục nào được chọn
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Submit & Cancel Buttons -->
                <div style="display: flex; justify-content: flex-end; gap: 10px; padding-top: 10px; border-top: 1px solid #f1f5f9;">
                    <button type="button" onclick="hienThiPanelThem(false)" style="background: white; border: 1px solid #cbd5e1; color: #475569; padding: 8px 18px; border-radius: 6px; font-weight: 600; font-size: 13px; cursor: pointer;">Hủy bỏ</button>
                    <button type="submit" id="btn-submit-dk" style="background: #1E3A8A; border: none; color: white; padding: 8px 20px; border-radius: 6px; font-weight: 700; font-size: 13px; cursor: pointer;">Lưu đợt đăng ký</button>
                </div>
            </form>
        </div>

    </div>

</div>

<script>
  let dotDangKyList = [];
  let dsMonHocChon = [];
  let dsLopHpChon = [];

  // Toggle hiển thị panel bên phải khi click + Thêm đợt đăng ký
  function hienThiPanelThem(show = true, isEdit = false) {
    const mainContainer = document.getElementById('registration-main-container');
    const sidePanel = document.getElementById('side-panel-add-edit');
    const title = document.getElementById('side-panel-title');
    const btnSubmit = document.getElementById('btn-submit-dk');

    if (show) {
      mainContainer.style.gridTemplateColumns = '1fr 380px';
      sidePanel.style.display = 'block';
      
      if (!isEdit) {
        document.getElementById('form-side-panel').reset();
        document.getElementById('dk-is-update').value = 'false';
        document.getElementById('dk-ma-dot').readOnly = false;
        document.getElementById('dk-ma-dot').style.backgroundColor = '';
        title.textContent = 'THÊM ĐỢT ĐĂNG KÝ HỌC PHẦN';
        btnSubmit.textContent = 'Lưu đợt đăng ký';
        dsMonHocChon = [];
        dsLopHpChon = [];
        renderDsMonHocChon();
        renderDsLopHpChon();
      }
    } else {
      mainContainer.style.gridTemplateColumns = '1fr';
      sidePanel.style.display = 'none';
    }
  }

  async function taiMetadataSelect() {
    try {
      const res = await fetch('../api/admin/hoc_phan_action.php?action=get_metadata');
      const data = await res.json();
      if (data.success) {
        const selectMon = document.getElementById('select-mon-hoc');
        selectMon.innerHTML = '<option value="">Chọn học phần</option>' + 
            data.mon_hoc.map(m => `<option value="${m.ma_mon}">${m.ma_mon} - ${m.ten_mon}</option>`).join('');

        const resClass = await fetch('../api/admin/hoc_phan_action.php?action=get_class_courses');
        const dataClass = await resClass.json();
        if (dataClass.success) {
            const selectLop = document.getElementById('select-lop-hp');
            selectLop.innerHTML = '<option value="">Chọn lớp học phần</option>' +
                dataClass.data.map(l => `<option value="${l.ma_lhp}">${l.ma_lhp} - ${l.ten_mon}</option>`).join('');
        }
      }
    } catch(e) {
      console.error(e);
    }
  }

  function themHocPhanVaoDanhSach() {
    const select = document.getElementById('select-mon-hoc');
    const ma = select.value;
    if (!ma) return;
    const text = select.options[select.selectedIndex].text;
    const parts = text.split(' - ');
    
    if (!dsMonHocChon.some(x => x.ma === ma)) {
        dsMonHocChon.push({ ma: ma, ten: parts[1] || text });
        renderDsMonHocChon();
    }
  }

  function renderDsMonHocChon() {
    const tbody = document.getElementById('tbody-mon-hoc-select');
    if (dsMonHocChon.length === 0) {
        tbody.innerHTML = `<tr><td colspan="3" style="text-align: center; padding: 15px; color: #94a3b8;"><div style="font-size: 18px;">📥</div>Chưa có mục nào được chọn</td></tr>`;
        return;
    }
    tbody.innerHTML = dsMonHocChon.map((m, idx) => `
        <tr>
            <td style="padding: 6px 8px; font-weight: 700; color: #1e3a8a;">${m.ma}</td>
            <td style="padding: 6px 8px;">${m.ten}</td>
            <td style="padding: 6px 8px; text-align: center;">
                <button type="button" onclick="xoaMonChon(${idx})" style="background: none; border: none; color: #dc2626; cursor: pointer; font-size: 13px;">🗑️</button>
            </td>
        </tr>
    `).join('');
  }

  function xoaMonChon(idx) {
    dsMonHocChon.splice(idx, 1);
    renderDsMonHocChon();
  }

  function themLopHocPhanVaoDanhSach() {
    const select = document.getElementById('select-lop-hp');
    const ma = select.value;
    if (!ma) return;
    const text = select.options[select.selectedIndex].text;
    const parts = text.split(' - ');

    if (!dsLopHpChon.some(x => x.ma === ma)) {
        dsLopHpChon.push({ ma: ma, ten: parts[1] || text });
        renderDsLopHpChon();
    }
  }

  function renderDsLopHpChon() {
    const tbody = document.getElementById('tbody-lop-hp-select');
    if (dsLopHpChon.length === 0) {
        tbody.innerHTML = `<tr><td colspan="3" style="text-align: center; padding: 15px; color: #94a3b8;"><div style="font-size: 18px;">📥</div>Chưa có mục nào được chọn</td></tr>`;
        return;
    }
    tbody.innerHTML = dsLopHpChon.map((l, idx) => `
        <tr>
            <td style="padding: 6px 8px; font-weight: 700; color: #1e3a8a;">${l.ma}</td>
            <td style="padding: 6px 8px;">${l.ten}</td>
            <td style="padding: 6px 8px; text-align: center;">
                <button type="button" onclick="xoaLopChon(${idx})" style="background: none; border: none; color: #dc2626; cursor: pointer; font-size: 13px;">🗑️</button>
            </td>
        </tr>
    `).join('');
  }

  function xoaLopChon(idx) {
    dsLopHpChon.splice(idx, 1);
    renderDsLopHpChon();
  }

  async function taiDotDangKy() {
    const q = document.getElementById('search-input').value.trim();
    const stFilter = document.getElementById('status-filter').value;
    try {
      const res = await fetch(`../api/admin/dot_dang_ky_action.php?q=${encodeURIComponent(q)}`);
      const resData = await res.json();
      if (resData.success) {
        dotDangKyList = resData.data;
        if (stFilter) {
            dotDangKyList = dotDangKyList.filter(x => x.trang_thai === stFilter);
        }
        renderBang(dotDangKyList);
      } else {
        alert(resData.message || 'Lỗi khi tải đợt đăng ký');
      }
    } catch (error) {
      console.error(error);
    }
  }

  function renderBang(ds) {
    const tbody = document.getElementById('dot-dang-ky-tbody');
    const info = document.getElementById('pagination-info');
    
    if (ds.length === 0) {
      tbody.innerHTML = `<tr><td colspan="9" style="text-align: center; color: #94a3b8; padding: 25px;">Không có đợt đăng ký nào.</td></tr>`;
      info.textContent = 'Hiển thị 0 trên 0 đợt đăng ký';
      return;
    }

    let stt = 1;
    tbody.innerHTML = ds.map(d => {
      const start = formattedDate(d.ngay_bat_dau);
      const end = formattedDate(d.ngay_ket_thuc);
      
      let statusBadge = '<span style="background: #fef3c7; color: #92400e; padding: 4px 10px; border-radius: 12px; font-size: 11px; font-weight: 700;">Chưa bắt đầu</span>';
      if (d.trang_thai === 'HOAT_DONG') {
        statusBadge = '<span style="background: #dcfce7; color: #166534; padding: 4px 10px; border-radius: 12px; font-size: 11px; font-weight: 700;">Đang diễn ra</span>';
      } else if (d.trang_thai === 'DA_DONG') {
        statusBadge = '<span style="background: #f1f5f9; color: #475569; padding: 4px 10px; border-radius: 12px; font-size: 11px; font-weight: 700;">Đã kết thúc</span>';
      }

      return `
        <tr style="border-bottom: 1px solid #f1f5f9;">
          <td style="padding: 10px 8px; text-align: center; color: #94a3b8; white-space: nowrap;">${stt++}</td>
          <td style="padding: 10px 8px; white-space: nowrap;"><strong style="color: #1E3A8A;">${escapeHtml(d.ma_dot_dk)}</strong></td>
          <td style="padding: 10px 8px;"><strong>${escapeHtml(d.ten_dot_dk)}</strong></td>
          <td style="padding: 10px 8px; color: #64748b; font-size: 12.5px; white-space: nowrap;">${start}</td>
          <td style="padding: 10px 8px; color: #64748b; font-size: 12.5px; white-space: nowrap;">${end}</td>
          <td style="padding: 10px 8px; text-align: center; white-space: nowrap;">${statusBadge}</td>
          <td style="padding: 10px 8px; text-align: center; font-weight: 700; color: #334155; white-space: nowrap;">${d.so_lop_mo || '—'}</td>
          <td style="padding: 10px 8px; text-align: center; font-weight: 700; color: #334155; white-space: nowrap;">${d.so_hoc_phan || '—'}</td>
          <td style="padding: 10px 8px; text-align: center; white-space: nowrap;">
            <button onclick="suaDotDangKy('${d.ma_dot_dk}')" title="Sửa" style="background: none; border: none; cursor: pointer; font-size: 14px; margin-right: 4px;">📝</button>
            <button onclick="xoaDotDangKy('${d.ma_dot_dk}')" title="Xóa" style="background: none; border: none; cursor: pointer; font-size: 14px;">🗑️</button>
          </td>
        </tr>
      `;
    }).join('');

    info.textContent = `Hiển thị 1-${ds.length} trên ${ds.length} đợt đăng ký`;
  }

  function formattedDate(isoStr) {
    if (!isoStr) return '';
    const d = new Date(isoStr);
    const day = String(d.getDate()).padStart(2, '0');
    const m = String(d.getMonth() + 1).padStart(2, '0');
    const y = d.getFullYear();
    const h = String(d.getHours()).padStart(2, '0');
    const min = String(d.getMinutes()).padStart(2, '0');
    return `${day}/${m}/${y} ${h}:${min}`;
  }

  function toLocalDatetimeString(isoStr) {
    if (!isoStr) return '';
    const d = new Date(isoStr);
    const y = d.getFullYear();
    const m = String(d.getMonth() + 1).padStart(2, '0');
    const date = String(d.getDate()).padStart(2, '0');
    const h = String(d.getHours()).padStart(2, '0');
    const min = String(d.getMinutes()).padStart(2, '0');
    return `${y}-${m}-${date}T${h}:${min}`;
  }

  function suaDotDangKy(ma) {
    const d = dotDangKyList.find(x => x.ma_dot_dk === ma);
    if (!d) return;

    document.getElementById('dk-is-update').value = 'true';
    document.getElementById('dk-ma-dot').value = d.ma_dot_dk;
    document.getElementById('dk-ma-dot').readOnly = true;
    document.getElementById('dk-ma-dot').style.backgroundColor = '#f1f5f9';

    document.getElementById('dk-ten-dot').value = d.ten_dot_dk;
    document.getElementById('dk-ngay-bd').value = toLocalDatetimeString(d.ngay_bat_dau);
    document.getElementById('dk-ngay-kt').value = toLocalDatetimeString(d.ngay_ket_thuc);

    document.getElementById('side-panel-title').textContent = 'CHỈNH SỬA ĐỢT ĐĂNG KÝ';
    document.getElementById('btn-submit-dk').textContent = 'Lưu đợt đăng ký';

    hienThiPanelThem(true, true);
  }

  async function guiFormDotDangKy(e) {
    e.preventDefault();

    const isUpdate = document.getElementById('dk-is-update').value === 'true';
    const payload = {
      is_update: isUpdate,
      ma_dot_dk: document.getElementById('dk-ma-dot').value,
      ten_dot_dk: document.getElementById('dk-ten-dot').value,
      id_hoc_ky: document.getElementById('dk-id-hoc-ky').value || 1,
      ngay_bat_dau: document.getElementById('dk-ngay-bd').value,
      ngay_ket_thuc: document.getElementById('dk-ngay-kt').value,
      so_tin_chi_toi_da: 24,
      trang_thai: 'HOAT_DONG',
      ghi_chu: ''
    };

    try {
      const res = await fetch('../api/admin/dot_dang_ky_action.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });
      const data = await res.json();
      
      if (data.success) {
        alert(data.message);
        hienThiPanelThem(false);
        taiDotDangKy();
      } else {
        alert(data.message || 'Lỗi khi lưu đợt đăng ký');
      }
    } catch (error) {
      console.error(error);
      alert('Lỗi kết nối máy chủ.');
    }
  }

  async function xoaDotDangKy(ma) {
    if (!confirm(`Bạn có chắc chắn muốn xóa đợt đăng ký ${ma}?`)) return;
    try {
      const res = await fetch('../api/admin/dot_dang_ky_action.php', {
        method: 'DELETE',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ ma_dot_dk: ma })
      });
      const data = await res.json();
      
      if (data.success) {
        alert(data.message);
        taiDotDangKy();
      } else {
        alert(data.message || 'Lỗi khi xóa đợt đăng ký');
      }
    } catch (error) {
      console.error(error);
    }
  }

  function escapeHtml(string) {
    const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
    return String(string).replace(/[&<>"']/g, m => map[m]);
  }

  document.addEventListener('DOMContentLoaded', () => {
    taiMetadataSelect();
    taiDotDangKy();
  });
</script>

<?php require_once 'includes/footer.php'; ?>
