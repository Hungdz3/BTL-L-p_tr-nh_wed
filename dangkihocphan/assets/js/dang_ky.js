// assets/js/dang_ky.js

let allHocPhanRaw = [];       // Dữ liệu thô từ API
let groupedSubjects = [];     // Danh sách đã gộp theo Môn học
let filteredSubjects = [];    // Danh sách sau khi tìm kiếm
let daDangKy = [];            // Danh sách học phần đã chọn của sinh viên
let expandedSubjects = new Set(); // Lưu trữ mã môn đang được mở rộng
let currentPage = 1;          // Trang hiện tại
const perPage = 6;            // Số môn học mỗi trang

// ── Tải danh sách tất cả học phần từ API ────────────────────────────────────
async function taiHocPhan(keyword = '', keepPage = true) {
  try {
    const savedPage = keepPage ? currentPage : 1;
    const res  = await fetch(`../api/get_hoc_phan.php?msv=${MSV}&q=${encodeURIComponent(keyword)}`);
    const data = await res.json();
    allHocPhanRaw = Array.isArray(data) ? data : [];
    processGroupedSubjects();
    applyFilter(keyword);
    currentPage = savedPage;
    renderBang();
  } catch (error) {
    console.error('Lỗi khi tải danh sách học phần:', error);
  }
}

// ── Nhóm dữ liệu lớp học phần theo Môn học ──────────────────────────────────
function processGroupedSubjects() {
  const map = new Map();

  allHocPhanRaw.forEach(hp => {
    const key = hp.ma_mon || hp.ten_mon;
    if (!map.has(key)) {
      map.set(key, {
        ma_mon: hp.ma_mon || 'HP',
        ten_mon: hp.ten_mon,
        so_tin_chi: hp.so_tin_chi,
        classes: []
      });
    }
    map.get(key).classes.push(hp);
  });

  groupedSubjects = Array.from(map.values());
}

// ── Tải danh sách môn học đã đăng ký thành công của SV ───────────────────────
async function taiDanhSachDaDangKy() {
  try {
    const res  = await fetch(`../api/get_da_chon.php?msv=${MSV}`);
    const data = await res.json();
    daDangKy = Array.isArray(data) ? data : [];
    renderSidebar();
    renderBang();
  } catch (error) {
    console.error('Lỗi khi tải danh sách đã đăng ký:', error);
  }
}

// ── Toggle mở / đóng danh sách lớp con của 1 môn học ────────────────────────
function toggleSubjectClasses(maMon, evt) {
  if (evt) {
    evt.preventDefault();
    evt.stopPropagation();
  }
  if (expandedSubjects.has(maMon)) {
    expandedSubjects.delete(maMon);
  } else {
    expandedSubjects.add(maMon);
  }
  renderBang();
}

// ── Render bảng danh sách môn học và phân trang ─────────────────────────────
function renderBang() {
  const tbody = document.getElementById('danh-sach-hp');
  const pageInfo = document.getElementById('hp-pagination-info');
  const pageBtns = document.getElementById('hp-pagination-btns');

  if (!tbody) return;

  if (filteredSubjects.length === 0) {
    tbody.innerHTML = `<tr><td colspan="6" style="text-align: center; color: #888; padding: 28px;">Không tìm thấy môn học nào phù hợp.</td></tr>`;
    if (pageInfo) pageInfo.textContent = 'Hiển thị 0 trong số 0 môn học';
    if (pageBtns) pageBtns.innerHTML = '';
    return;
  }

  const totalItems = filteredSubjects.length;
  const totalPages = Math.ceil(totalItems / perPage);
  currentPage = Math.max(1, Math.min(currentPage, totalPages));

  const startIndex = (currentPage - 1) * perPage;
  const pageItems = filteredSubjects.slice(startIndex, startIndex + perPage);

  let html = '';

  pageItems.forEach((sub, idx) => {
    const isExpanded = expandedSubjects.has(sub.ma_mon);
    const numClasses = sub.classes.length;

    // Kiểm tra xem sinh viên đã đăng ký lớp nào trong môn này chưa
    const registeredClass = sub.classes.find(c => 
      c.da_dang_ky || daDangKy.some(d => d.ma_lhp === c.ma_lhp)
    );

    let statusHtml = '';
    let mainRowStyle = isExpanded ? 'background: #f8fafc;' : '';

    if (registeredClass) {
      statusHtml = `<span style="background: #dcfce7; color: #166534; font-weight: 700; padding: 4px 10px; border-radius: 20px; font-size: 11.5px; border: 1px solid #bbf7d0; display: inline-flex; align-items: center; gap: 4px;">✓ Đã ĐK: <strong>${escapeHtml(registeredClass.ma_lhp)}</strong></span>`;
      mainRowStyle = 'background: #f0fdf4;';
    } else {
      statusHtml = `<span style="color: #64748b; font-size: 12px; font-weight: 500;">Chưa đăng ký</span>`;
    }

    // Dòng thông tin Môn học chính
    html += `
      <tr style="${mainRowStyle}; border-bottom: 1px solid #e2e8f0; transition: background 0.15s;">
        <td style="font-weight: 700; color: #1E3A8A; vertical-align: middle;">
          ${escapeHtml(sub.ma_mon)}
        </td>
        <td style="vertical-align: middle;">
          <strong style="font-size: 14px; color: #0f172a;">${escapeHtml(sub.ten_mon)}</strong>
        </td>
        <td style="text-align: center; font-weight: 700; color: #334155; vertical-align: middle;">
          ${sub.so_tin_chi}
        </td>
        <td style="text-align: center; vertical-align: middle;">
          <span style="background: #e0f2fe; color: #0369a1; font-weight: 700; padding: 3px 10px; border-radius: 12px; font-size: 12px;">
            ${numClasses} lớp mở
          </span>
        </td>
        <td style="text-align: center; vertical-align: middle;">
          ${statusHtml}
        </td>
        <td style="text-align: center; vertical-align: middle;">
          <button type="button" onclick="toggleSubjectClasses('${escapeJsString(sub.ma_mon)}', event)" style="background: ${isExpanded ? '#1E3A8A' : '#ffffff'}; color: ${isExpanded ? '#ffffff' : '#1E3A8A'}; border: 1px solid #1E3A8A; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 4px; transition: all 0.2s;">
            <span>${isExpanded ? 'Đóng lại' : 'Xem ' + numClasses + ' lớp'}</span>
            <span style="font-size: 10px;">${isExpanded ? '▲' : '▼'}</span>
          </button>
        </td>
      </tr>
    `;

    // Dòng mở rộng (Accordion) chi tiết danh sách các lớp học phần con
    if (isExpanded) {
      html += `
        <tr style="background: #f8fafc;">
          <td colspan="6" style="padding: 12px 18px; border-bottom: 2px solid #cbd5e1;">
            <div style="background: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; padding: 14px; box-shadow: 0 2px 6px rgba(0,0,0,0.03);">
              <div style="font-size: 12.5px; font-weight: 700; color: #1E3A8A; margin-bottom: 10px; display: flex; align-items: center; justify-content: space-between;">
                <span>📖 DANH SÁCH CÁC LỚP HỌC PHẦN ĐANG MỞ — ${escapeHtml(sub.ten_mon)}</span>
                <span style="color: #64748b; font-weight: normal; font-size: 11.5px;">Chọn 1 lớp phù hợp với lịch của bạn</span>
              </div>
              
              <table style="width: 100%; border-collapse: collapse; font-size: 12.5px;">
                <thead>
                  <tr style="background: #f1f5f9; text-align: left; color: #475569; font-size: 11px; text-transform: uppercase;">
                    <th style="padding: 8px 10px;">Mã lớp</th>
                    <th style="padding: 8px 10px;">Giảng viên</th>
                    <th style="padding: 8px 10px;">Lịch học & Phòng</th>
                    <th style="padding: 8px 10px; text-align: center;">Sĩ số</th>
                    <th style="padding: 8px 10px; text-align: center; width: 140px;">Đăng ký</th>
                  </tr>
                </thead>
                <tbody>
                  ${sub.classes.map(c => {
                    const isFull = c.si_so_hien >= c.si_so_max;
                    const isThisClassReg = c.da_dang_ky || daDangKy.some(d => d.ma_lhp === c.ma_lhp);
                    const isUnscheduled = !c.lich_hoc || c.lich_hoc === 'Chưa xếp lịch';

                    let btnHtml = '';
                    let rowBg = isThisClassReg ? 'background: #f0fdf4;' : '';

                    if (isThisClassReg) {
                      btnHtml = `<span style="background: #dcfce7; color: #166534; font-weight: 700; padding: 4px 12px; border-radius: 4px; font-size: 11.5px; border: 1px solid #bbf7d0; display: inline-block;">✓ Đang học</span>`;
                    } else if (registeredClass) {
                      // Đã đăng ký 1 lớp khác của môn này -> cho phép chuyển lớp
                      btnHtml = `<button type="button" class="btn-dangky" style="background: #d97706; padding: 4px 10px; font-size: 11.5px;" onclick="dangKy('${c.ma_lhp}', ${c.so_tin_chi}, '${escapeJsString(c.ten_mon)}', event)">Đổi sang lớp này</button>`;
                    } else if (isFull) {
                      btnHtml = `<span style="background: #fee2e2; color: #991b1b; font-weight: 700; padding: 4px 10px; border-radius: 4px; font-size: 11.5px; border: 1px solid #fecaca; display: inline-block;">🚫 Hết chỗ</span>`;
                    } else {
                      btnHtml = `<button type="button" class="btn-dangky" style="padding: 4px 12px; font-size: 11.5px;" onclick="dangKy('${c.ma_lhp}', ${c.so_tin_chi}, '${escapeJsString(c.ten_mon)}', event)">Đăng ký</button>`;
                    }

                    return `
                      <tr style="border-bottom: 1px dashed #e2e8f0; ${rowBg}">
                        <td style="padding: 8px 10px; font-weight: 700; color: #1E3A8A;">${escapeHtml(c.ma_lhp)}</td>
                        <td style="padding: 8px 10px;">${escapeHtml(c.giang_vien)}</td>
                        <td style="padding: 8px 10px;">
                          ${isUnscheduled ? '<span style="color: #ea580c; font-style: italic;">Chưa xếp lịch</span>' : escapeHtml(c.lich_hoc)}
                        </td>
                        <td style="padding: 8px 10px; text-align: center; font-weight: 600;">
                          <span style="${isFull ? 'color: #dc2626; font-weight: 800;' : 'color: #334155;'}">
                            ${c.si_so_hien}/${c.si_so_max}
                          </span>
                        </td>
                        <td style="padding: 8px 10px; text-align: center;">
                          ${btnHtml}
                        </td>
                      </tr>
                    `;
                  }).join('')}
                </tbody>
              </table>
            </div>
          </td>
        </tr>
      `;
    }
  });

  tbody.innerHTML = html;

  // Render thông tin và nút phân trang
  if (pageInfo) {
    pageInfo.textContent = `Hiển thị ${startIndex + 1} - ${Math.min(startIndex + perPage, totalItems)} trong số ${totalItems} môn học`;
  }

  if (pageBtns) {
    let btnsHtml = '';
    
    // Nút Trước (Prev)
    btnsHtml += `<button type="button" onclick="chuyenTrang(${Math.max(1, currentPage - 1)}, event)" ${currentPage === 1 ? 'disabled style="opacity: 0.5; cursor: not-allowed;"' : ''} style="padding: 5px 12px; border: 1px solid #cbd5e1; background: #ffffff; border-radius: 6px; cursor: pointer; font-size: 12px; font-weight: 600;">‹ Trước</button>`;

    // Các nút số trang
    for (let p = 1; p <= totalPages; p++) {
      const isAct = p === currentPage;
      btnsHtml += `<button type="button" onclick="chuyenTrang(${p}, event)" style="padding: 5px 12px; border: 1px solid ${isAct ? '#1E3A8A' : '#cbd5e1'}; background: ${isAct ? '#1E3A8A' : '#ffffff'}; color: ${isAct ? '#ffffff' : '#334155'}; border-radius: 6px; cursor: pointer; font-size: 12px; font-weight: 700;">${p}</button>`;
    }

    // Nút Sau (Next)
    btnsHtml += `<button type="button" onclick="chuyenTrang(${Math.min(totalPages, currentPage + 1)}, event)" ${currentPage === totalPages ? 'disabled style="opacity: 0.5; cursor: not-allowed;"' : ''} style="padding: 5px 12px; border: 1px solid #cbd5e1; background: #ffffff; border-radius: 6px; cursor: pointer; font-size: 12px; font-weight: 600;">Sau ›</button>`;

    pageBtns.innerHTML = btnsHtml;
  }
}

function chuyenTrang(page, evt) {
  if (evt) {
    evt.preventDefault();
  }
  currentPage = page;
  renderBang();
}

// ── Gửi yêu cầu đăng ký 1 học phần lên Server ───────────────────────────────
async function dangKy(ma_hp, so_tc, ten_mon, evt) {
  if (evt) {
    evt.preventDefault();
    evt.stopPropagation();
  }
  try {
    const res  = await fetch('../api/dang_ky.php', {
      method : 'POST',
      headers: { 'Content-Type': 'application/json' },
      body   : JSON.stringify({ msv: MSV, ma_hp }),
    });
    const data = await res.json();

    if (data.success) {
      await taiDanhSachDaDangKy();
      await taiHocPhan((document.getElementById('search-input').value || '').trim(), true);
      showToast(data.message || 'Đăng ký học phần thành công!', 'success');
    } else {
      showToast(data.message, 'error');
    }
  } catch (error) {
    showToast('Không thể kết nối đến máy chủ.', 'error');
  }
}

// ── Gửi yêu cầu huỷ/xoá đăng ký học phần ────────────────────────────────────
function huyChon(ma_hp, evt) {
  if (evt) {
    evt.preventDefault();
  }
  showConfirmModal(`Bạn có chắc chắn muốn hủy đăng ký lớp học phần ${ma_hp}?`, async () => {
    try {
      const res  = await fetch('../api/huy_dang_ky.php', {
        method : 'POST',
        headers: { 'Content-Type': 'application/json' },
        body   : JSON.stringify({ msv: MSV, ma_hp }),
      });
      const data = await res.json();

      if (data.success) {
        await taiDanhSachDaDangKy();
        await taiHocPhan((document.getElementById('search-input').value || '').trim(), true);
        showToast(data.message || `Đã hủy lớp học phần ${ma_hp} thành công.`, 'success');
      } else {
        showToast(data.message, 'error');
      }
    } catch (error) {
      showToast('Không thể kết nối đến máy chủ.', 'error');
    }
  });
}

// ── Cập nhật giao diện Sidebar đã chọn ───────────────────────────────────────
function renderSidebar() {
  const container = document.getElementById('ds-da-chon');
  const tongTC    = daDangKy.reduce((s, h) => s + Number(h.so_tin_chi), 0);

  if (!container) return;

  if (daDangKy.length === 0) {
    container.innerHTML = `<p style="text-align: center; color: #888; padding: 20px 0;">Chưa đăng ký học phần nào.</p>`;
    if (document.getElementById('tong-mon')) document.getElementById('tong-mon').textContent    = '0';
    if (document.getElementById('tong-tc-chon')) document.getElementById('tong-tc-chon').textContent = '0 TC';
    if (document.getElementById('so-tc')) document.getElementById('so-tc').textContent = '0/24 tín chỉ';
    return;
  }

  container.innerHTML = daDangKy.map(h => `
    <div class="sidebar-item">
      <div class="sidebar-item-info">
        <strong class="ma-hp">${escapeHtml(h.ma_lhp)}</strong>
        <span style="font-size: 13px; font-weight: 500;">${escapeHtml(h.ten_mon)}</span>
        <span class="so-tc">${h.so_tin_chi} tín chỉ</span>
      </div>
      <button type="button" class="btn-xoa" onclick="huyChon('${h.ma_lhp}', event)" title="Huỷ học phần này">🗑</button>
    </div>
  `).join('');

  if (document.getElementById('tong-mon')) document.getElementById('tong-mon').textContent    = daDangKy.length;
  if (document.getElementById('tong-tc-chon')) document.getElementById('tong-tc-chon').textContent = tongTC + ' TC';
  if (document.getElementById('so-tc')) document.getElementById('so-tc').textContent = tongTC + '/24 tín chỉ';
}

// ── Hàm lọc dữ liệu ─────────────────────────────────────────────────────────
function applyFilter(kw) {
  const q = kw !== undefined ? kw : (document.getElementById('search-input').value || '').trim().toLowerCase();
  if (!q) {
    filteredSubjects = [...groupedSubjects];
  } else {
    filteredSubjects = groupedSubjects.filter(sub => 
      (sub.ten_mon && sub.ten_mon.toLowerCase().includes(q)) ||
      (sub.ma_mon && sub.ma_mon.toLowerCase().includes(q)) ||
      sub.classes.some(c => c.giang_vien && c.giang_vien.toLowerCase().includes(q)) ||
      sub.classes.some(c => c.ma_lhp && c.ma_lhp.toLowerCase().includes(q))
    );
  }
}

// ── Hàm tìm kiếm (chỉ reset về trang 1 khi người dùng chủ động tìm kiếm) ─────
function timKiem() {
  applyFilter();
  currentPage = 1;
  renderBang();
}

// ── Sự kiện nút "Xác nhận đăng ký" ──────────────────────────────────────────
function xacNhanDangKy() {
  if (daDangKy.length === 0) {
    showToast('Vui lòng chọn ít nhất một lớp học phần để đăng ký!', 'error');
    return;
  }
  showToast('Hệ thống đã lưu nhận danh sách đăng ký học phần của bạn thành công!', 'success');
}

// ── Hàm tiện ích bảo mật tránh lỗi XSS khi hiển thị text từ DB ───────────────
function escapeHtml(string) {
  if (!string) return '';
  const map = {
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#039;'
  };
  return String(string).replace(/[&<>"']/g, function(m) { return map[m]; });
}

function escapeJsString(str) {
  if (!str) return '';
  return String(str).replace(/'/g, "\\'").replace(/"/g, '&quot;');
}

// ── Khởi chạy khi tải xong trang ─────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
  taiDanhSachDaDangKy();
  taiHocPhan('', false);
});
