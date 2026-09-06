# HƯỚNG DẪN HOÀN THIỆN TRANG ADMIN & TÍCH HỢP EXCEL

Tài liệu này phân tích chi tiết các bước thiết kế giao diện Admin, cập nhật Cơ sở dữ liệu và triển khai tính năng Nhập/Xuất danh sách sinh viên từ file Excel/Google Sheets cho dự án **BTL_LapTrinhWed**.

---

## I. Phân Tích Cơ Sở Dữ Liệu (Database Schema Audit)

Sau khi đối chiếu cấu trúc CSDL PostgreSQL hiện tại với toàn bộ hình ảnh thiết kế giao diện Admin (bao gồm cả Thông báo, Đợt đăng ký, Sinh viên, Giảng viên, Học phần, Lớp học phần và Lịch học), chúng ta cần cập nhật cơ sở dữ liệu để đáp ứng đầy đủ yêu cầu:

### 1. Bổ sung Tệp đính kèm cho bảng `thong_bao`
*   **Mục tiêu:** Đáp ứng phần tải lên tài liệu đính kèm (PDF, DOCX, XLSX) trong modal *Thêm thông báo mới*.
*   **Giải pháp:** Thêm cột `file_dinh_kem` (TEXT) để lưu đường dẫn tệp tải lên server.
*   **Lệnh SQL:**
    ```sql
    ALTER TABLE thong_bao ADD COLUMN file_dinh_kem VARCHAR(255) NULL;
    ```

### 2. Thêm bảng `dot_dang_ky` (Đợt đăng ký học phần)
*   **Mục tiêu:** Bản thiết kế chỉ ra một học kỳ (`hoc_ky`) có thể chia làm nhiều đợt đăng ký học phần độc lập (ví dụ: *Đợt ĐK chính thức kì 2*, *Đợt ĐK bổ sung kì 2*, *Đợt ĐK học kì phụ hè*). Bảng `hoc_ky` hiện tại chỉ lưu trữ một khoảng thời gian đăng ký duy nhất, do đó cần tách riêng ra bảng đợt đăng ký.
*   **Lệnh SQL tạo bảng mới:**
    ```sql
    CREATE TABLE dot_dang_ky (
        ma_dot_dk VARCHAR(50) PRIMARY KEY,
        ten_dot_dk VARCHAR(255) NOT NULL,
        id_hoc_ky UUID NOT NULL REFERENCES hoc_ky(id_hoc_ky) ON DELETE CASCADE,
        ngay_bat_dau TIMESTAMP WITH TIME ZONE NOT NULL,
        ngay_ket_thuc TIMESTAMP WITH TIME ZONE NOT NULL,
        so_tin_chi_toi_da INT NOT NULL DEFAULT 24,
        trang_thai VARCHAR(50) NOT NULL DEFAULT 'CHO_DUYET', -- 'HOAT_DONG' (Hoạt động), 'CHO_DUYET' (Chờ duyệt), 'DA_DONG' (Đã đóng)
        ghi_chu TEXT NULL,
        created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW(),
        updated_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW()
    );
    ```

### 3. Cập nhật bảng môn học `mon_hoc` để phân loại
*   **Mục tiêu:** Đáp ứng modal *Thêm học phần*. Khi chọn **Loại học phần = Môn chuyên ngành**, giao diện bắt buộc chọn **Khoa** và **Ngành**. Khi chọn **Môn chung**, hai trường Khoa và Ngành bị ẩn đi.
*   **Giải pháp:**
    *   Thêm cột `loai_mon_hoc` để lưu loại môn học (mặc định là `MON_CHUNG`).
    *   Thêm cột `ma_nganh` (cho phép NULL) liên kết đến bảng `nganh`.
    *   Cho phép cột `ma_khoa` nhận giá trị NULL đối với môn chung.
*   **Lệnh SQL:**
    ```sql
    ALTER TABLE mon_hoc ADD COLUMN loai_mon_hoc VARCHAR(50) NOT NULL DEFAULT 'MON_CHUNG'; -- 'MON_CHUYEN_NGANH', 'MON_CHUNG'
    ALTER TABLE mon_hoc ADD COLUMN ma_nganh VARCHAR(50) REFERENCES nganh(ma_nganh) ON DELETE SET NULL;
    ALTER TABLE mon_hoc ALTER COLUMN ma_khoa DROP NOT NULL;
    ```

### 4. Cập nhật bảng lớp học phần `lop_hoc_phan`
*   **Mục tiêu:** Lưu trữ ngày bắt đầu và ngày kết thúc cho từng lớp học phần cụ thể được nhập từ modal *Thêm lớp học phần*. Ngoài ra cập nhật thêm các trạng thái xét duyệt lớp: `CHO_DUYET` (Chờ xét duyệt), `DA_DUYET` (Đã duyệt), `TU_CHOI` (Từ chối).
*   **Lệnh SQL:**
    ```sql
    ALTER TABLE lop_hoc_phan ADD COLUMN ngay_bat_dau DATE NULL;
    ALTER TABLE lop_hoc_phan ADD COLUMN ngay_ket_thuc DATE NULL;
    ```

---

## II. Các Bước Hoàn Thiện Trang Admin (Admin Panel Tasks)

Dự án sẽ được chia thành các cấu phần quản trị tương ứng với các ảnh mẫu thiết kế:

### 1. Trang chủ Admin Dashboard (Ảnh 11)
*   **Giao diện:** Hiển thị số liệu tổng quan hệ thống (Sinh viên, Giảng viên, Học phần, Lịch học, Thông báo). Cột bên trái hiển thị các danh sách mới thêm. Cột bên phải hiển thị hoạt động gần đây của Admin.
*   **Các bước thực hiện:**
    1.  Tạo giao diện hiển thị tổng quan theo thẻ dạng CSS Grid/Flexbox.
    2.  Thiết kế API Backend truy vấn các dữ liệu tổng hợp.
    3.  Tích hợp bảng `audit_log` của cơ sở dữ liệu để hiển thị tự động luồng dữ liệu **Hoạt động gần đây** theo thời gian thực (ví dụ: hành động Thêm/Cập nhật của admin).

### 2. Phân hệ Quản lý Thông báo (Ảnh 1 & Ảnh 2)
*   **Giao diện:**
    *   Trang chính hiển thị bảng danh sách thông báo gồm các cột: *STT, Tiêu đề thông báo, Đối tượng nhận (Sinh viên/Giảng viên/Tất cả), Ngày đăng, Trạng thái (Đã gửi/Bản nháp), Thao tác (Sửa/Xóa)*.
    *   Thanh tìm kiếm theo từ khóa và bộ lọc "Đối tượng nhận".
    *   Modal "Thêm thông báo mới" với các trường: *Tiêu đề, Đối tượng nhận, Nội dung chi tiết, và Vùng kéo thả tệp đính kèm (<10MB)*.
*   **Các bước thực hiện:**
    1.  Tạo giao diện HTML/CSS hiển thị danh sách và Modal dựa trên Bootstrap hoặc CSS tự dựng.
    2.  Tạo thư mục `assets/uploads/notifications/` trên máy chủ để lưu các file đính kèm khi admin upload.
    3.  Viết API PHP xử lý thêm/sửa/xóa thông báo, xử lý lưu tệp đính kèm và cập nhật đường dẫn vào cột `file_dinh_kem`.

### 3. Phân hệ Quản lý Đợt Đăng Ký Học Phần (Ảnh 3 & Ảnh 4)
*   **Giao diện:**
    *   Trang danh sách hiển thị các đợt đăng ký: *Mã đợt, Tên đợt đăng ký, Học kỳ, Năm học, Thời gian (Từ ngày - Đến ngày), Trạng thái (Hoạt động/Chờ duyệt/Đã đóng), Thao tác (Sửa/Xóa)*.
    *   Modal "Mở đợt đăng ký học phần mới" gồm: *Tên đợt đăng ký, Học kỳ (Chọn từ danh sách học kỳ hiện có), Năm học, Ngày bắt đầu, Ngày kết thúc, Số tín chỉ tối đa được đăng ký, Ghi chú*.
*   **Các bước thực hiện:**
    1.  Xây dựng giao diện bảng và Modal mở đợt đăng ký mới.
    2.  Viết API Backend truy vấn các học kỳ hiện có (`hoc_ky`) để đưa vào thẻ `<select>` của Modal.
    3.  Viết API lưu đợt đăng ký mới vào bảng `dot_dang_ky`. Khi lưu cần validate: *Ngày bắt đầu phải trước ngày kết thúc*.

### 4. Phân hệ Quản lý Sinh Viên & Nhập/Xuất Excel (Ảnh 5)
*   **Giao diện:**
    *   Bảng hiển thị danh sách sinh viên: *Mã SV, Họ tên, Email, Lớp, Khoa, Trạng thái (Hoạt động/Khóa), Thao tác (Sửa/Xóa)*.
    *   Bộ lọc tìm kiếm nhanh theo: *Từ khóa (Tên/Mã SV), Khoa, Ngành, Lớp*.
    *   Nút bấm: **[+ Thêm sinh viên]** (mở form nhập tay hoặc upload Excel/CSV).
*   **Giải pháp Nhập/Xuất Excel/CSV (Dành cho chức năng Thêm Sinh Viên):**
    Do dự án viết bằng PHP thuần, để tránh cài đặt các thư viện cồng kềnh như `PhpSpreadsheet` qua Composer, chúng ta có thể áp dụng 2 cách:
    *   **Cách 1: Sử dụng tệp định dạng CSV (Khuyên dùng)**
        *   Người quản trị xuất tệp Google Sheets hoặc Excel dưới định dạng `.csv` (UTF-8).
        *   Phía backend dùng hàm `fgetcsv()` có sẵn của PHP để đọc từng dòng và ghi nhận vào cơ sở dữ liệu.
        *   Tải tệp mẫu CSV trực tiếp từ trang Admin để đảm bảo người dùng nhập đúng định dạng cột.
    *   **Cách 2: Sử dụng thư viện PHP thuần đơn giản (ví dụ: `SimpleXLSX`)**
        *   Copy duy nhất 1 file mã nguồn `SimpleXLSX.php` vào thư mục của dự án để đọc trực tiếp file `.xlsx`.

    *Quy trình Import Sinh viên vào Hệ thống:*
    ```
    Tải lên file Excel/CSV ➔ Đọc dữ liệu từng dòng ➔ Kiểm tra tính hợp lệ (Mã lớp có tồn tại không)
                                                       │
         ┌─────────────────────────────────────────────┘
         ▼
    Tạo tài khoản đăng nhập (Bảng `tai_khoan`) ➔ Tạo thông tin sinh viên (Bảng `sinh_vien` trỏ tới `tai_khoan_id`)
    ```
    *   Mật khẩu mặc định của sinh viên mới import sẽ tự động được tạo và mã hóa bằng `password_hash()` (ví dụ: mặc định là `123456` hoặc ngày tháng năm sinh viết liền `DDMMYYYY`).

### 5. Phân hệ Quản lý Giảng viên (Ảnh 6)
*   **Giao diện:** Bảng danh sách giảng viên kèm Khoa và Trạng thái hoạt động, hỗ trợ thanh tìm kiếm và lọc theo Khoa.
*   **Các bước thực hiện:**
    1.  Xây dựng trang giao diện giảng viên.
    2.  Thiết kế API backend thêm/sửa/xóa giảng viên. Tương tự như sinh viên, khi thêm giảng viên hệ thống tự động sinh một tài khoản đăng nhập tương ứng trong bảng `tai_khoan`.

### 6. Phân hệ Quản lý Học phần (Ảnh 7 ➔ 10)
*   **Giao diện:** Hiển thị 2 tab lớn: *Danh sách học phần* và *Xét duyệt học phần*. Gồm các thẻ thống kê tổng số lượng học phần theo các trạng thái (Hoạt động, Chờ duyệt, Ẩn).
*   **Modal thêm học phần động (Ảnh 8, 9, 10):**
    *   Dropdown "Loại học phần" cho phép chọn giữa **Môn chung** và **Môn chuyên ngành**.
    *   Nếu chọn *Môn chuyên ngành*, hiển thị 2 trường **Khoa** và **Ngành** (sử dụng JS/AJAX để hiển thị động).
    *   Nếu chọn *Môn chung*, ẩn 2 trường trên.
*   **Hành động Backend:** API PHP cập nhật và lưu các thuộc tính phân loại môn học tương ứng vào CSDL PostgreSQL.

### 7. Xếp Lớp học phần & Xét duyệt Mở lớp (Ảnh 12 & 13)
*   **Modal Thêm lớp học phần:** Cho phép chọn môn học, nhập mã lớp, chọn giảng viên giảng dạy, ngày bắt đầu - ngày kết thúc, ca học (buổi T2-T7, tiết bắt đầu, tiết kết thúc).
*   **Trang Xét duyệt lớp học phần:** Quản lý các lớp học phần chờ duyệt mở. Admin có thể bấm **Duyệt** hoặc **Hủy lớp** (từ chối mở lớp).
*   **Các bước thực hiện:**
    1.  Thiết kế giao diện Modal và Tab xét duyệt.
    2.  Viết API Backend lưu thông tin lớp học phần. Phía backend cần thực hiện bóc tách tiết học bắt đầu và tiết học kết thúc để insert nhiều dòng lịch học tương ứng vào bảng `lich_hoc`.

---

## III. Các File Cần Tạo Mới & Chỉnh Sửa

### 1. SQL Cập nhật Database
Tạo file [`config/update_schema.sql`](file:///C:/laptrinhweddd/BTL_LapTrinhWed/config/update_schema.sql) để chạy các lệnh SQL bổ sung bảng và cột nêu trên.

### 2. Giao diện trang quản trị Admin
*   [`admin/index.php`](file:///C:/laptrinhweddd/BTL_LapTrinhWed/admin/index.php): Giao diện Trang chủ Admin.
*   [`admin/thong-bao.php`](file:///C:/laptrinhweddd/BTL_LapTrinhWed/admin/thong-bao.php): Giao diện quản lý thông báo.
*   [`admin/dang-ky-hp.php`](file:///C:/laptrinhweddd/BTL_LapTrinhWed/admin/dang-ky-hp.php): Giao diện quản lý đợt đăng ký học phần.
*   [`admin/sinh-vien.php`](file:///C:/laptrinhweddd/BTL_LapTrinhWed/admin/sinh-vien.php): Giao diện quản lý sinh viên và tích hợp upload file Excel/CSV.
*   [`admin/giang-vien.php`](file:///C:/laptrinhweddd/BTL_LapTrinhWed/admin/giang-vien.php): Giao diện quản lý giảng viên.
*   [`admin/hoc-phan.php`](file:///C:/laptrinhweddd/BTL_LapTrinhWed/admin/hoc-phan.php): Giao diện quản lý và phân loại học phần.

### 3. API xử lý hành động phía sau (Backend)
*   [`api/admin/dashboard_data.php`](file:///C:/laptrinhweddd/BTL_LapTrinhWed/api/admin/dashboard_data.php)
*   [`api/admin/thong_bao_action.php`](file:///C:/laptrinhweddd/BTL_LapTrinhWed/api/admin/thong_bao_action.php)
*   [`api/admin/dot_dang_ky_action.php`](file:///C:/laptrinhweddd/BTL_LapTrinhWed/api/admin/dot_dang_ky_action.php)
*   [`api/admin/sinh_vien_action.php`](file:///C:/laptrinhweddd/BTL_LapTrinhWed/api/admin/sinh_vien_action.php)
*   [`api/admin/giang_vien_action.php`](file:///C:/laptrinhweddd/BTL_LapTrinhWed/api/admin/giang_vien_action.php)
*   [`api/admin/hoc_phan_action.php`](file:///C:/laptrinhweddd/BTL_LapTrinhWed/api/admin/hoc_phan_action.php)
