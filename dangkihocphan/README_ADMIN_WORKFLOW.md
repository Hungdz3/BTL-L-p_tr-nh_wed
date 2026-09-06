# TÀI LIỆU TOÀN DIỆN VỀ WORKFLOW, VÒNG ĐỜI DỮ LIỆU & VALIDATE FORM PHÂN HỆ ADMIN
**Hệ thống:** Cổng Quản lý & Đăng ký Học phần — Trường Đại Học HNDA  
**Công nghệ:** PHP 8.x + PostgreSQL PDO + JavaScript (Fetch API / AJAX) + CSS3

---

## 📑 MỤC LỤC
1. [Tổng quan Kiến trúc & Luồng xử lý chung](#1-tổng-quan-kiến-trúc--luồng-xử-lý-chung)
2. [Bước 1: Luồng Đăng nhập, Cơ chế Băm Mật khẩu & Xác thực Admin](#2-bước-1-luồng-đăng-nhập-cơ-chế-băm-mật-khẩu--xác-thực-admin)
   - [2.1. Lệnh Băm & Đối chiếu Mật khẩu trong Hệ thống (Password Hashing)](#21-lệnh-băm--đối-chiếu-mật-khẩu-trong-hệ-thống-password-hashing)
   - [2.2. Luồng Xử lý Đăng nhập & Phân quyền Admin](#22-luồng-xử-lý-đăng-nhập--phân-quyền-admin)
3. [Bước 2: Trang Dashboard Tổng quan (Admin Home)](#3-bước-2-trang-dashboard-tổng-quan)
4. [Bước 3: Chi tiết Vòng đời Dữ liệu từng Form Admin (Lưu ở đâu — Dùng ra sao — Hiển thị thế nào)](#4-chi-tiết-vòng-đời-dữ-liệu-từng-form-admin)
   - [4.1. Form Thêm / Cập nhật Sinh viên (Thủ công & File Excel/CSV)](#41-form-thêm--cập-nhật-sinh-viên-thủ-công--file-excelcsv)
   - [4.2. Form Thêm / Cập nhật Giảng viên](#42-form-thêm--cập-nhật-giảng-viên)
   - [4.3. Form Thêm / Sửa Môn học (Học phần gốc)](#43-form-thêm--sửa-môn-học-học-phần-gốc)
   - [4.4. Form Mở Lớp học phần & Xếp lịch học](#44-form-mở-lớp-học-phần--xếp-lịch-học)
   - [4.5. Form Cấu hình Đợt Đăng ký Học phần](#45-form-cấu-hình-đợt-đăng-ký-học-phần)
   - [4.6. Form Soạn & Đăng Thông báo](#46-form-soạn--đăng-thông-báo)
   - [4.7. Form Đặt lại Mật khẩu (Reset Password)](#47-form-đặt-lại-mật-khẩu-reset-password)
5. [Bảng Tổng hợp Database Tables & File Code Phụ trách](#5-bảng-tổng-hợp-database-tables--file-code-phụ-trách)
6. [Toàn bộ Quy tắc Validate Form (Frontend & Backend & Database)](#6-toàn-bộ-quy-tắc-validate-form-frontend--backend--database)

---

## 1. TỔNG QUAN KIẾN TRÚC & LUỒNG XỬ LÝ CHUNG

Hệ thống hoạt động theo mô hình **Client-Server kiến trúc lai (Hybrid Render)**:
- **Server-side Rendering (SSR):** Dùng PHP render cấu trúc HTML layout, menu header, footer và kiểm tra phiên đăng nhập (`$_SESSION`).
- **Client-side API (AJAX/Fetch):** Tải dữ liệu động, phân trang, lọc, thêm/sửa/xóa không cần tải lại trang thông qua các RESTful JSON API tại thư mục `api/admin/`.
- **Cơ sở dữ liệu:** PostgreSQL kết nối qua lớp PDO tại `config/db.php`.

```
[Trình duyệt Web] 
       │ 
       ├─► (1. Đăng nhập) ────────► login.php ────────► Bảng: tai_khoan, vai_tro
       │                                                     │
       ├─► (2. Chuyển hướng) ────► admin/index.php ◄─────────┘ (Lưu Session)
       │                                │
       ├─► (3. Gọi API dữ liệu) ──► api/admin/*.php ──► Bảng: sinh_vien, giao_vien, mon_hoc,
       │                                                       lop_hoc_phan, dot_dang_ky,
       │                                                       dang_ky_hoc_phan, thong_bao...
       └─► (4. Render dữ liệu) ◄─── JSON Response
```

---

## 2. BƯỚC 1: LUỒNG ĐĂNG NHẬP, CƠ CHẾ BĂM MẬT KHẨU & XÁC THỰC ADMIN

### 2.1. Lệnh Băm & Đối chiếu Mật khẩu trong Hệ thống (Password Hashing)

Hệ thống tuyệt đối **không lưu mật khẩu dạng văn bản thô (Plain-text)** vào Database mà sử dụng cơ chế băm mật khẩu một chiều tiêu chuẩn công nghiệp của PHP:

#### 🔐 1. Lệnh BĂM mật khẩu (Khi tạo tài khoản mới / Reset mật khẩu):
* **Lệnh PHP thực thi:**
  ```php
  $password_hash = password_hash($plain_password, PASSWORD_DEFAULT);
  ```
  *(Ví dụ mật khẩu mặc định: `password_hash('123456', PASSWORD_DEFAULT)`)*
* **Thuật toán áp dụng:** Thuật toán **Bcrypt** (chuẩn `PASSWORD_BCRYPT` với cost factor mặc định là 10 hoặc 12).
* **Đặc điểm bảo mật:**
  - Chuỗi băm đầu ra có độ dài cố định **60 ký tự**, luôn bắt đầu bằng tiền tố `$2y$...` (hoặc `$2a$`).
  - **Tự động sinh chuỗi muối ngẫu nhiên (Auto Salt):** Cùng một mật khẩu `123456`, mỗi lần chạy hàm `password_hash()` sẽ cho ra một chuỗi băm hoàn toàn khác nhau. Điều này ngăn chặn 100% các cuộc tấn công bằng bảng tra sẵn (Rainbow Table Attack).
* **Các file code sử dụng lệnh băm này:**
  - `api/admin/sinh_vien_action.php` (Dòng 145 & 290): Khi thêm sinh viên hoặc import Excel.
  - `api/admin/giang_vien_action.php` (Dòng 115): Khi thêm giảng viên mới.
  - `config/seed_data.php` (Dòng 25): Khi nạp dữ liệu mẫu ban đầu.

#### 🔍 2. Lệnh ĐỐI CHIẾU mật khẩu (Khi người dùng đăng nhập):
* **Lệnh PHP thực thi:**
  ```php
  if (password_verify($matKhau, $user['password_hash'])) {
      // Mật khẩu chính xác -> Cấp quyền truy cập
  }
  ```
* **Nguyên lý:** Hàm `password_verify()` tự động trích xuất chuỗi Salt và thuật toán từ trong chính chuỗi hash đã lưu trong Database để kiểm tra tính hợp lệ mà không bao giờ cần giải mã ngược mật khẩu.
* **File code sử dụng:** `login.php` (Dòng 36).

---

### 2.2. Luồng Xử lý Đăng nhập & Phân quyền Admin
1. Người dùng nhập tên đăng nhập/email và mật khẩu trên form đăng nhập `login.php`.
2. Hệ thống kiểm tra tài khoản trong bảng `tai_khoan`, so khớp mật khẩu mã hóa bằng `password_verify()` và lấy tên vai trò từ bảng `vai_tro`.
3. Nếu vai trò chứa `ADMIN`, tạo `$_SESSION` và chuyển hướng trực tiếp sang `admin/index.php`.

```php
// File: login.php (Dòng 24 - 48)
$stmt = $db->prepare("
    SELECT tk.*, vt.ten AS ten_vai_tro 
    FROM tai_khoan tk
    JOIN vai_tro vt ON tk.vai_tro_id = vt.id
    WHERE (tk.username = :tk OR tk.email = :tk) AND tk.trang_thai = 'HOAT_DONG'
    LIMIT 1
");
$stmt->execute([':tk' => $taiKhoanPost]);
$user = $stmt->fetch();

// Kiểm tra mật khẩu băm và cấp quyền Admin
if (!$user) {
    $loi = 'Tài khoản không tồn tại hoặc đã bị khóa.';
} elseif (!password_verify($matKhau, $user['password_hash'])) {
    $loi = 'Mật khẩu không chính xác.';
} else {
    $vaiTro = strtoupper($user['ten_vai_tro']);
    if (str_contains($vaiTro, 'ADMIN')) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['loai_tk'] = 'admin';
        $_SESSION['ho_ten'] = 'Administrator';
        
        header('Location: admin/index.php');
        exit;
    }
}
```

---

## 3. BƯỚC 2: TRANG DASHBOARD TỔNG QUAN

Sau khi đăng nhập thành công, Admin được chuyển đến `admin/index.php`. Trang này tải các chỉ số thống kê và nhật ký hoạt động hệ thống.

### 🔹 Dữ liệu Render lúc đầu (SSR):
- **File:** `admin/index.php`
- **Tables:** `sinh_vien`, `giao_vien`, `mon_hoc`, `lop_hoc_phan`, `thong_bao`, `tai_khoan`, `audit_log`
- **Đoạn code thực thi (Dòng 13 - 52 trong `admin/index.php`):**
```php
// Thống kê tổng số lượng các thực thể
$counts['sinh_vien']   = $db->query("SELECT COUNT(*) FROM sinh_vien")->fetchColumn();
$counts['giao_vien']   = $db->query("SELECT COUNT(*) FROM giao_vien")->fetchColumn();
$counts['mon_hoc']     = $db->query("SELECT COUNT(*) FROM mon_hoc")->fetchColumn();
$counts['lop_hoc_phan']= $db->query("SELECT COUNT(*) FROM lop_hoc_phan")->fetchColumn();
$counts['thong_bao']   = $db->query("SELECT COUNT(*) FROM thong_bao")->fetchColumn();
$counts['tai_khoan']   = $db->query("SELECT COUNT(*) FROM tai_khoan")->fetchColumn();

// Lấy 5 sinh viên mới nhất
$new_students = $db->query("
    SELECT sv.ma_sv, sv.ho_ten, l.ten_lop 
    FROM sinh_vien sv
    LEFT JOIN lop_sinh_vien l ON sv.ma_lop_sv = l.ma_lop_sv
    ORDER BY sv.created_at DESC LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

// Lấy 5 giảng viên mới nhất
$new_teachers = $db->query("
    SELECT gv.ma_gv, gv.ho_ten, k.ten_khoa 
    FROM giao_vien gv
    LEFT JOIN khoa k ON gv.ma_khoa = k.ma_khoa
    ORDER BY gv.created_at DESC LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);
```

---

## 4. CHI TIẾT VÒNG ĐỜI DỮ LIỆU TỪNG FORM ADMIN
*(Lưu vào table nào — Dữ liệu được sử dụng ra sao trong toàn hệ thống — Hiển thị như thế nào)*

---

### 4.1. Form Thêm / Cập nhật Sinh viên (Thủ công & File Excel/CSV)
* **File giao diện:** [admin/sinh-vien.php](file:///C:/laptrinhweddd/clone/admin/sinh-vien.php)
* **File xử lý API:** [api/admin/sinh_vien_action.php](file:///C:/laptrinhweddd/clone/api/admin/sinh_vien_action.php) (Dòng 115–330 và 335–450)

#### A. Dữ liệu được lưu vào Table nào trong Database?
1. **Bảng `tai_khoan`:**
   - Hệ thống tự động tạo 1 tài khoản đăng nhập với `username = ma_sv`, mật khẩu băm mặc định `123456` qua `password_hash('123456', PASSWORD_DEFAULT)`, `vai_tro_id = 3` (Vai trò Sinh viên), `email = email_sv`, `trang_thai = 'HOAT_DONG'`.
2. **Bảng `sinh_vien`:**
   - Lưu thông tin hồ sơ: `ma_sv` (PK), `tai_khoan_id` (FK liên kết với bảng `tai_khoan`), `ho_ten`, `email`, `so_dien_thoai`, `ma_lop_sv` (FK liên kết `lop_sinh_vien`), `ngay_sinh`, `gioi_tinh`, `trang_thai`.
3. **Bảng `audit_log`:**
   - Tự động ghi lại hành động của Admin: `user_id` (Admin), `table_name = 'sinh_vien'`, `record_id = ma_sv`, `action = 'INSERT' / 'UPDATE'`.

#### B. Dữ liệu này được sử dụng ra sao trong toàn hệ thống?
* **Phân hệ Đăng nhập:** Sinh viên sử dụng chính `ma_sv` hoặc `email` vừa được tạo cùng mật khẩu `123456` để đăng nhập vào hệ thống tại `login.php`.
* **Phân hệ Đăng ký học phần:** Mã `ma_sv` được lưu trong `$_SESSION['ma_sv']` để sinh viên chọn môn học, hệ thống ghi nhận vào bảng `dang_ky_hoc_phan`.
* **Phân hệ Giảng viên:** Tên và `ma_sv` tự động hiển thị trong danh sách lớp tại `giang-vien/diem.php` để giảng viên phụ trách nhập điểm Chuyên cần, Giữa kỳ, Cuối kỳ.

#### C. Dữ liệu hiển thị như thế nào?
* **Tại trang Admin (`admin/sinh-vien.php`):** Hiển thị trên bảng quản lý gồm các cột: *Mã SV, Họ và tên, Lớp chuyên ngành, Khoa, Email, SĐT, Trạng thái hoạt động*, kèm các nút thao tác nhanh (Sửa, Xóa, Reset mật khẩu).
* **Tại trang Sinh viên (`sinh-vien/trang-chu.php`, `sinh-vien/sinh-vien.php`):** Hiển thị lời chào *"Xin chào, [Họ tên]"*, thẻ sinh viên, khoa, lớp, tiến độ tích lũy tín chỉ và bảng điểm cá nhân.
* **Tại trang Giảng viên (`giang-vien/diem.php`):** Hiển thị thành từng hàng trong bảng chấm điểm (STT, MSSV, Họ tên, ô nhập điểm).

---

### 4.2. Form Thêm / Cập nhật Giảng viên
* **File giao diện:** [admin/giang-vien.php](file:///C:/laptrinhweddd/clone/admin/giang-vien.php)
* **File xử lý API:** [api/admin/giang_vien_action.php](file:///C:/laptrinhweddd/clone/api/admin/giang_vien_action.php) (Dòng 80–220)

#### A. Dữ liệu được lưu vào Table nào trong Database?
1. **Bảng `tai_khoan`:** Tạo tài khoản với `username = ma_gv`, mật khẩu băm `password_hash('123456', PASSWORD_DEFAULT)`, `vai_tro_id = 2` (Vai trò Giảng viên), `email = email_gv`.
2. **Bảng `giao_vien`:** Lưu `ma_gv` (PK), `tai_khoan_id` (FK), `ho_ten`, `email`, `so_dien_thoai`, `hoc_vi`, `ma_khoa` (FK liên kết bảng `khoa`), `trang_thai`.
3. **Bảng `audit_log`:** Ghi vết thao tác `action = 'INSERT' / 'UPDATE'`.

#### B. Dữ liệu này được sử dụng ra sao trong toàn hệ thống?
* **Phân hệ Đăng nhập:** Giảng viên dùng `ma_gv` đăng nhập tại `login.php` $\rightarrow$ hệ thống nhận diện vai trò và chuyển thẳng vào cổng giảng viên `giang-vien/index.php`.
* **Phân hệ Phân công giảng dạy:** Admin sử dụng danh sách giảng viên này trong thẻ `<select>` của Form Mở Lớp học phần (`admin/hoc-phan.php`) để phân công giáo viên đứng lớp.
* **Phân hệ Quản lý Lớp & Điểm:** Khi giảng viên vào `giang-vien/lich-giang-day.php` hoặc `giang-vien/diem.php`, hệ thống dùng `$_SESSION['ma_gv']` để chỉ load đúng các lớp mà giảng viên đó được phân công dạy.

#### C. Dữ liệu hiển thị như thế nào?
* **Tại trang Admin (`admin/giang-vien.php`):** Bảng danh sách gồm *Mã GV, Họ tên, Học vị, Khoa trực thuộc, Email, SĐT, Trạng thái*.
* **Tại trang Giảng viên (`giang-vien/index.php`):** Thẻ thông tin cá nhân ở góc trái trang chủ (Mã GV, Khoa công tác, Email liên hệ).
* **Tại trang Sinh viên (`sinh-vien/index.php`):** Tên giảng viên hiển thị ở cột *"Giảng viên giảng dạy"* trong bảng danh sách lớp học phần mở đăng ký.

---

### 4.3. Form Thêm / Sửa Môn học (Học phần gốc)
* **File giao diện:** [admin/hoc-phan.php](file:///C:/laptrinhweddd/clone/admin/hoc-phan.php) (Tab 1: Môn học)
* **File xử lý API:** [api/admin/hoc_phan_action.php](file:///C:/laptrinhweddd/clone/api/admin/hoc_phan_action.php) (Dòng 50–160)

#### A. Dữ liệu được lưu vào Table nào trong Database?
* **Bảng `mon_hoc`:** Lưu `ma_mon` (PK), `ten_mon`, `so_tin_chi`, `ma_khoa` (FK), `mo_ta`, `trang_thai`, `created_at`.
* **Bảng `audit_log`:** Ghi nhận thao tác cập nhật môn học.

#### B. Dữ liệu này được sử dụng ra sao trong toàn hệ thống?
* Dữ liệu môn học là **danh mục chương trình đào tạo chuẩn** của nhà trường.
* Dùng làm dữ liệu nguồn để mở các lớp học phần cụ thể (`lop_hoc_phan`) theo từng học kỳ.
* Số tín chỉ `so_tin_chi` được dùng để tính toán tổng số tín chỉ sinh viên đăng ký, kiểm tra giới hạn tín chỉ tối thiểu/tối đa trong đợt đăng ký, và tính điểm trung bình tích lũy GPA.

#### C. Dữ liệu hiển thị như thế nào?
* **Tại trang Admin (`admin/hoc-phan.php`):** Hiển thị trong bảng danh mục môn học (Mã môn, Tên môn học, Số tín chỉ, Khoa quản lý).
* **Tại trang Sinh viên:** Tên môn và số tín chỉ hiển thị ở Cổng đăng ký học phần `sinh-vien/index.php` và Bảng điểm tích lũy `sinh-vien/sinh-vien.php`.

---

### 4.4. Form Mở Lớp học phần & Xếp lịch học
* **File giao diện:** [admin/hoc-phan.php](file:///C:/laptrinhweddd/clone/admin/hoc-phan.php) (Tab 2: Lớp học phần)
* **File xử lý API:** [api/admin/hoc_phan_action.php](file:///C:/laptrinhweddd/clone/api/admin/hoc_phan_action.php) (Dòng 170–380)

#### A. Dữ liệu được lưu vào Table nào trong Database?
1. **Bảng `lop_hoc_phan`:** Lưu `ma_lhp` (PK), `ma_mon` (FK), `id_hoc_ky` (FK), `ma_gv` (FK), `so_luong_max`, `so_luong_hien_tai = 0`, `phong_hoc`, `trang_thai = 'MO_DANG_KY'`.
2. **Bảng `lich_hoc`:** Lưu lịch học chi tiết của lớp: `ma_lhp`, `thu` (Thứ 2 - Thứ 7), `id_tiet` (Tiết bắt đầu - kết thúc), `phong_hoc`.

#### B. Dữ liệu này được sử dụng ra sao trong toàn hệ thống?
* **Cổng Sinh viên:** Lớp học phần này ngay lập tức xuất hiện trong bảng tra cứu môn mở của học kỳ hiện tại (`sinh-vien/index.php`). Khi sinh viên bấm "Đăng ký", hệ thống kiểm tra `so_luong_hien_tai < so_luong_max` $\rightarrow$ chèn vào `dang_ky_hoc_phan` và tự động tăng `so_luong_hien_tai`.
* **Thời khóa biểu Sinh viên:** Lịch học từ bảng `lich_hoc` được nạp tự động lên ma trận lịch học tuần tại `sinh-vien/lich-hoc.php`.
* **Lịch giảng dạy & Chấm điểm Giảng viên:** Tự động đồng bộ lên lịch giảng dạy tuần `giang-vien/lich-giang-day.php` và tạo bảng điểm trống cho lớp tại `giang-vien/diem.php`.

#### C. Dữ liệu hiển thị như thế nào?
* **Tại trang Admin (`admin/hoc-phan.php`):** Bảng quản lý lớp học phần (Mã LHP, Tên môn, Số TC, Giảng viên, Sĩ số hiện tại / Tối đa, Phòng học, Thứ/Tiết, Nút xem danh sách sinh viên trong lớp).
* **Tại trang Sinh viên (`sinh-vien/index.php` & `sinh-vien/lich-hoc.php`):** Danh sách lớp kèm nút "Chọn đăng ký", hiển thị số chỗ còn trống (VD: `45/60`), và hiển thị ô lịch học màu xanh trên Thời khóa biểu tuần.
* **Tại trang Giảng viên (`giang-vien/lich-giang-day.php`):** Ô lịch dạy chi tiết theo thứ, phòng học và ca học.

---

### 4.5. Form Cấu hình Đợt Đăng ký Học phần
* **File giao diện:** [admin/dang-ky-hp.php](file:///C:/laptrinhweddd/clone/admin/dang-ky-hp.php)
* **File xử lý API:** [api/admin/dot_dang_ky_action.php](file:///C:/laptrinhweddd/clone/api/admin/dot_dang_ky_action.php) (Dòng 60–180)

#### A. Dữ liệu được lưu vào Table nào trong Database?
* **Bảng `dot_dang_ky`:** Lưu `id` (PK tự tăng), `ten_dot`, `id_hoc_ky` (FK), `thoi_gian_bat_dau`, `thoi_gian_ket_thuc`, `tc_toi_thieu`, `tc_toi_da`, `trang_thai` (`DANG_MO` / `DONG` / `SAP_MO`).

#### B. Dữ liệu này được sử dụng ra sao trong toàn hệ thống?
* Đóng vai trò là **"Cầu dao đóng/mở cổng đăng ký tín chỉ"**.
* Khi sinh viên gửi yêu cầu đăng ký môn học (`api/dang_ky.php`), backend kiểm tra bảng `dot_dang_ky`:
  1. Trạng thái có đang là `'DANG_MO'` không?
  2. Thời gian thực tế `NOW()` có nằm trong khoảng `thoi_gian_bat_dau <= NOW() <= thoi_gian_ket_thuc` không?
  3. Tổng số tín chỉ sinh viên đăng ký có thoả mãn `tc_toi_thieu <= tổng_tc <= tc_toi_da` không?
* Nếu không thoả mãn bất kỳ điều kiện nào, API sẽ chặn đăng ký và trả về thông báo lỗi thích hợp.

#### C. Dữ liệu hiển thị như thế nào?
* **Tại trang Admin (`admin/dang-ky-hp.php`):** Bảng danh sách các đợt (Tên đợt, Học kỳ áp dụng, Thời gian mở - đóng, Giới hạn tín chỉ, Badge trạng thái Xanh/Đỏ, Switch Bật/Tắt đợt, Nút xem thống kê số sinh viên đã tham gia).
* **Tại trang Sinh viên (`sinh-vien/index.php`):** Banner hiển thị đợt đăng ký hiện hành kèm đồng hồ đếm ngược hoặc trạng thái *"Cổng đăng ký đang mở / Cổng đăng ký đã đóng"*.

---

### 4.6. Form Soạn & Đăng Thông báo
* **File giao diện:** [admin/thong-bao.php](file:///C:/laptrinhweddd/clone/admin/thong-bao.php)
* **File xử lý API:** [api/admin/thong_bao_action.php](file:///C:/laptrinhweddd/clone/api/admin/thong_bao_action.php) (Dòng 60–160)

#### A. Dữ liệu được lưu vào Table nào trong Database?
1. **Bảng `thong_bao`:** Lưu `id` (PK), `tieu_de`, `noi_dung`, `doi_tuong_nhan` (`TAT_CA` / `SINH_VIEN` / `GIANG_VIEN`), `trang_thai` (`DA_GUI` / `NHAP` / `AN`), `ngay_dang`, `file_dinh_kem`, `tai_khoan_id` (FK Admin đăng bài).
2. **Hệ thống tệp (File System):** File đính kèm được lưu vật lý vào thư mục `uploads/` trên máy chủ web.

#### B. Dữ liệu này được sử dụng ra sao trong toàn hệ thống?
* **Phân quyền hiển thị:**
  - Nếu `doi_tuong_nhan = 'TAT_CA'` $\rightarrow$ Cả Sinh viên và Giảng viên đều xem được.
  - Nếu `doi_tuong_nhan = 'GIANG_VIEN'` $\rightarrow$ Chỉ Giảng viên xem được, Sinh viên không thấy.
  - Nếu `doi_tuong_nhan = 'SINH_VIEN'` $\rightarrow$ Chỉ Sinh viên xem được.
* Người dùng có thể click vào liên kết tệp đính kèm để xem hoặc tải về máy.

#### C. Dữ liệu hiển thị như thế nào?
* **Tại trang Admin (`admin/thong-bao.php`):** Bảng quản lý tin bài (Tiêu đề, Người đăng, Ngày đăng, Đối tượng nhận, Tên tệp đính kèm, Nút Sửa/Ẩn/Xóa).
* **Tại trang Giảng viên (`giang-vien/index.php` & `giang-vien/thong-bao.php`):** Khối 3 thông báo mới nhất trên trang chủ Dashboard và danh sách đầy đủ tại trang Thông báo.
* **Tại trang Sinh viên (`sinh-vien/thong-bao.php`):** Bảng tin tức đào tạo kèm ngày đăng và link tải tài liệu hướng dẫn.

---

### 4.7. Form Đặt lại Mật khẩu (Reset Password)
* **Thực hiện tại:** Nút "Reset mật khẩu" trong [admin/sinh-vien.php](file:///C:/laptrinhweddd/clone/admin/sinh-vien.php) và [admin/giang-vien.php](file:///C:/laptrinhweddd/clone/admin/giang-vien.php)
* **Xử lý Backend:** `api/admin/sinh_vien_action.php` / `api/admin/giang_vien_action.php`

#### A. Dữ liệu được lưu vào Table nào?
* **Bảng `tai_khoan`:** Cập nhật cột `password_hash = password_hash('123456', PASSWORD_DEFAULT)` và `updated_at = NOW()` tại bản ghi có `id = tai_khoan_id`.
* **Bảng `audit_log`:** Ghi vết hành động `action = 'RESET_PASSWORD'`.

#### B. Dữ liệu được sử dụng ra sao?
* Sinh viên hoặc Giảng viên bị quên mật khẩu sẽ dùng lại mật khẩu mặc định `123456` để đăng nhập vào hệ thống tại `login.php`.

#### C. Hiển thị như thế nào?
* **Tại trang Admin:** Hiển thị thông báo Toast thành công màu xanh: *"Đã đặt lại mật khẩu của tài khoản về 123456 thành công!"*.

---

## 5. BẢNG TỔNG HỢP DATABASE TABLES & FILE CODE PHỤ TRÁCH

| Chức năng Admin | File Giao diện (View) | File API Xử lý (Controller/Model) | Các Table Database tác động |
| :--- | :--- | :--- | :--- |
| **Đăng nhập & Cấp quyền** | `login.php` | `login.php` | `tai_khoan`, `vai_tro` |
| **Dashboard Tổng quan** | `admin/index.php` | `api/admin/dashboard_data.php` | `sinh_vien`, `giao_vien`, `mon_hoc`, `lop_hoc_phan`, `thong_bao`, `tai_khoan`, `audit_log` |
| **Quản lý Sinh viên** | `admin/sinh-vien.php` | `api/admin/sinh_vien_action.php` | `sinh_vien`, `lop_sinh_vien`, `khoa`, `nganh`, `tai_khoan`, `dang_ky_hoc_phan`, `diem` |
| **Quản lý Giảng viên** | `admin/giang-vien.php` | `api/admin/giang_vien_action.php` | `giao_vien`, `khoa`, `tai_khoan`, `lop_hoc_phan`, `vai_tro` |
| **Môn học & Học phần** | `admin/hoc-phan.php` | `api/admin/hoc_phan_action.php` | `mon_hoc`, `lop_hoc_phan`, `hoc_ky`, `giao_vien`, `khoa`, `lich_hoc`, `dang_ky_hoc_phan` |
| **Đợt Đăng ký học phần** | `admin/dang-ky-hp.php`| `api/admin/dot_dang_ky_action.php` | `dot_dang_ky`, `hoc_ky`, `dang_ky_hoc_phan`, `lop_hoc_phan`, `sinh_vien` |
| **Quản lý Thông báo** | `admin/thong-bao.php` | `api/admin/thong_bao_action.php` | `thong_bao`, `tai_khoan`, `audit_log` |
| **Cấu hình Kết nối CSDL**| — | `config/db.php` | Kết nối PostgreSQL qua PDO (`getDB()`) |

---

## 6. TOÀN BỘ QUY TẮC VALIDATE FORM (FRONTEND & BACKEND & DATABASE)

### 📝 1. Form Đăng nhập Quản trị (`login.php`)
* **Tài khoản / Email (`tai_khoan`):**
  - *Frontend:* `required`, không được để khoảng trắng rỗng (`trim() !== ''`).
  - *Backend:* Kiểm tra tài khoản tồn tại trong bảng `tai_khoan`, `trang_thai = 'HOAT_DONG'`.
  - *Lỗi xuất ra:* `"Vui lòng nhập đầy đủ tài khoản/email và mật khẩu."` / `"Tài khoản không tồn tại hoặc đã bị khóa."`
* **Mật khẩu (`mat_khau`):**
  - *Frontend:* `required`, `type="password"`.
  - *Backend:* `mb_strlen($matKhau) >= 6`, đối chiếu qua lệnh `password_verify($matKhau, $user['password_hash'])`.
  - *Lỗi xuất ra:* `"Mật khẩu phải có ít nhất 6 ký tự."` / `"Mật khẩu không chính xác."`
* **Quyền Admin:**
  - *Backend:* Kiểm tra `str_contains(strtoupper($user['ten_vai_tro']), 'ADMIN')` $\rightarrow$ gán `$_SESSION['loai_tk'] = 'admin'`.

---

### 📝 2. Form Thêm / Cập nhật Sinh viên (`admin/sinh-vien.php`)
* **Mã sinh viên (`ma_sv`):**
  - *Frontend:* Bắt buộc, tự động chuyển chữ hoa, không dấu cách.
  - *Backend:* Không được rỗng. Khi Thêm mới: kiểm tra `SELECT ma_sv FROM sinh_vien` $\rightarrow$ Nếu trùng báo lỗi: `"Mã sinh viên đã tồn tại."`
  - *Database:* Khóa chính `PRIMARY KEY` bảng `sinh_vien`.
* **Họ và tên (`ho_ten`):**
  - *Frontend:* `required`, tối thiểu 2 ký tự.
  - *Backend:* `trim()`, không được rỗng.
  - *Database:* `VARCHAR(100) NOT NULL`.
* **Lớp sinh hoạt (`ma_lop_sv`):**
  - *Frontend:* `<select>` bắt buộc chọn một lớp hợp lệ.
  - *Backend:* Kiểm tra mã lớp phải tồn tại trong bảng `lop_sinh_vien`.
  - *Database:* Khóa ngoại `FOREIGN KEY REFERENCES lop_sinh_vien(ma_lop_sv)`.
* **Email (`email`):**
  - *Frontend:* `type="email"`, đúng định dạng `@`.
  - *Backend:* `filter_var($email, FILTER_VALIDATE_EMAIL)`. Tự động cập nhật đồng bộ sang bảng `tai_khoan`.
* **Số điện thoại (`so_dien_thoai`):**
  - *Frontend:* `pattern="[0-9]{10,11}"` chỉ nhận số.
  - *Backend:* Chuỗi số từ 9 đến 11 ký tự.
* **Ngày sinh (`ngay_sinh`):**
  - *Frontend:* `type="date"`, không cho phép ngày lớn hơn ngày hiện tại.
  - *Backend:* Validate định dạng `Y-m-d` qua `strtotime()`.
* **Giới tính (`gioi_tinh`):**
  - *Frontend & Backend:* Chuẩn hóa về Enum: `'NAM'`, `'NU'`, `'KHAC'`.
* **Trạng thái học tập (`trang_thai`):**
  - *Backend:* Enum: `'DANG_HOC'`, `'BAO_LUU'`, `'DA_TOT_NGHIEP'`, `'THOI_HOC'`.

---

### 📝 3. Form Nhập Sinh viên hàng loạt qua File (Excel / CSV)
* **Tệp đính kèm:** `$_FILES['file']['error'] === UPLOAD_ERR_OK`, định dạng `.csv`.
* **Tiêu đề cột:** Bắt buộc có các cột: `Mã SV`, `Họ tên`, `Lớp`, `Email`, `Ngày sinh`.
* **BOM UTF-8:** Tự động cắt bỏ chuỗi byte ẩn `\xEF\xBB\xBF` để tránh lỗi font tiếng Việt.
* **Upsert thông minh:** Nếu `ma_sv` đã có $\rightarrow$ cập nhật thông tin; Nếu chưa có $\rightarrow$ tự động tạo bản ghi mới ở cả bảng `tai_khoan` (mật khẩu mặc định băm bằng `password_hash('123456', PASSWORD_DEFAULT)`) và bảng `sinh_vien`.
* **Báo cáo lỗi:** Trả về JSON chứa tổng số dòng thành công và danh sách chi tiết các dòng bị lỗi cú pháp.

---

### 📝 4. Form Thêm / Sửa Giảng viên (`admin/giang-vien.php`)
* **Mã giảng viên (`ma_gv`):**
  - *Frontend:* Bắt buộc, không dấu cách (VD: `GV001`, `GV202401`).
  - *Backend:* Không được rỗng. Khi thêm mới: kiểm tra trùng lặp trong bảng `giao_vien`.
* **Họ và tên (`ho_ten`):**
  - *Frontend:* `required`.
  - *Backend:* `trim()`, `VARCHAR(100) NOT NULL`.
* **Khoa trực thuộc (`ma_khoa`):**
  - *Frontend:* Chọn từ `<select>`.
  - *Backend:* Bắt buộc tồn tại trong bảng `khoa`.
* **Học vị (`hoc_vi`):** Thạc sĩ, Tiến sĩ, PGS, GS...
* **Ràng buộc khi Xóa Giảng viên:**
  - *Backend:* Truy vấn `SELECT COUNT(*) FROM lop_hoc_phan WHERE ma_gv = :ma_gv`.
  - *Nghiệp vụ:* Nếu giảng viên đang phụ trách bất kỳ lớp học phần nào $\rightarrow$ **Chặn xóa tuyệt đối** và xuất thông báo: *"Không thể xóa giảng viên đang có lớp giảng dạy."*

---

### 📝 5. Form Quản lý Môn học & Lớp học phần (`admin/hoc-phan.php`)
* **Môn học (Học phần gốc):**
  - `ma_mon`: Bắt buộc, viết hoa, không trùng lặp trong `mon_hoc`.
  - `ten_mon`: `required`, `VARCHAR(150) NOT NULL`.
  - `so_tin_chi`: Số nguyên dương `1 <= so_tin_chi <= 10` (Frontend: `min="1" max="10"`).
  - `ma_khoa`: Bắt buộc tồn tại trong bảng `khoa`.
* **Lớp học phần (`lop_hoc_phan`):**
  - `ma_lhp`: Bắt buộc, duy nhất (VD: `LHP001`, `IT101-01`).
  - `id_hoc_ky`: Bắt buộc chọn từ bảng `hoc_ky`.
  - `so_luong_max`: Số nguyên dương từ `15` đến `150` sinh viên.
  - `phong_hoc`: Bắt buộc nhập tên phòng (VD: `A101`, `B203`).
  - `Kiem_tra_trung_lich`: Chặn xếp trùng phòng học hoặc trùng giảng viên trong cùng một thứ (`thu`) và tiết học (`id_tiet`).

---

### 📝 6. Form Cấu hình Đợt Đăng ký Học phần (`admin/dang-ky-hp.php`)
* **Tên đợt (`ten_dot`):** Bắt buộc nhập (VD: `Đợt 1 - Học kỳ 1 (2026-2027)`).
* **Học kỳ (`id_hoc_ky`):** Phải tồn tại trong bảng `hoc_ky`.
* **Thời gian bắt đầu & Kết thúc:**
  - *Frontend:* `type="datetime-local"`.
  - *Backend:* Validate ngày giờ hợp lệ.
  - *Quy tắc Logic:* **Thời gian kết thúc phải diễn ra SAU thời gian bắt đầu** (`thoi_gian_ket_thuc > thoi_gian_bat_dau`). Nếu sai báo lỗi: *"Thời gian kết thúc phải sau thời gian bắt đầu."*
* **Tín chỉ Tối thiểu & Tối đa (`tc_toi_thieu`, `tc_toi_da`):**
  - *Quy tắc Logic:* `0 <= tc_toi_thieu <= tc_toi_da`. Số tín chỉ tối đa không được nhỏ hơn số tín chỉ tối thiểu.
* **Trạng thái cổng (`trang_thai`):**
  - Enum: `'DANG_MO'`, `'DONG'`, `'SAP_MO'`.
  - Sinh viên chỉ được phép gửi request đăng ký khi đợt ở trạng thái `'DANG_MO'` và thời gian hiện tại nằm trong khung giờ quy định.

---

### 📝 7. Form Soạn & Đăng Thông báo (`admin/thong-bao.php`)
* **Tiêu đề (`tieu_de`):** `required`, độ dài từ 5 đến 255 ký tự.
* **Nội dung (`noi_dung`):** `required`, lọc XSS an toàn bằng `htmlspecialchars()`, hỗ trợ xuống dòng `nl2br()`.
* **Đối tượng nhận (`doi_tuong_nhan`):** Enum: `'TAT_CA'`, `'SINH_VIEN'`, `'GIANG_VIEN'`.
* **Trạng thái xuất bản (`trang_thai`):** Enum: `'DA_GUI'`, `'NHAP'`, `'AN'`.
* **Tệp đính kèm (`file_dinh_kem`):**
  - Giới hạn dung lượng: $\le 10\text{ MB}$.
  - Định dạng cho phép: `.pdf`, `.doc`, `.docx`, `.xls`, `.xlsx`, `.png`, `.jpg`.
  - Cơ chế lưu trữ: Tự động băm tên tệp ngẫu nhiên chống ghi đè khi lưu vào thư mục `uploads/`.

---

### 📝 8. Form Đặt lại Mật khẩu (Reset Password)
* **Quy tắc Validate:**
  - Kiểm tra ID tài khoản đích có tồn tại trong bảng `tai_khoan`.
  - Mã hóa mật khẩu mặc định qua thuật toán bảo mật `password_hash('123456', PASSWORD_DEFAULT)`.
  - Ghi vết hành động (Audit Trail) vào bảng `audit_log` với `action = 'RESET_PASSWORD'`.
