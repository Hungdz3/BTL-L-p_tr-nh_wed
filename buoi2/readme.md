# Bài thực hành Buổi 2 - Cá nhân

## Thông tin sinh viên

| Thông tin | Chi tiết |
|-----------|----------|
| **Họ và tên** | [Tên của bạn] |
| **Mã số sinh viên** | [MSSV của bạn] |
| **Lớp** | [Tên lớp của bạn] |
| **Buổi học** | Buổi 2 |
| **Ngày thực hiện** | [Ngày thực hiện] |

---

## Đề tài nhóm
**Hệ thống Quản lý Khóa học và Đăng ký Học phần**

---

## Đối tượng dữ liệu

### Lịch học (Schedule)

Đối tượng dữ liệu được chọn là **Lịch học** - lưu trữ thông tin về thời gian và địa điểm học của các lớp học phần trong hệ thống quản lý khóa học và đăng ký học phần.

### Các trường dữ liệu

| STT | Trường | Kiểu dữ liệu | Bắt buộc | Mô tả |
|-----|--------|--------------|----------|-------|
| 1 | `course_name` | VARCHAR(255) | | Tên môn học |
| 2 | `course_code` | VARCHAR(50) |  | Mã môn học |
| 3 | `weekday` | VARCHAR(10) |  | Mã thứ trong tuần (2-8) |
| 4 | `weekday_name` | VARCHAR(20) |  | Tên thứ (Thứ 2, Thứ 3, ...) |
| 5 | `start_period` | INT | | Tiết bắt đầu (1-12) |
| 6 | `end_period` | INT |  | Tiết kết thúc (1-12) |
| 7 | `course_type` | VARCHAR(10) |  | Loại học phần (LT/TH/BT/DT) |
| 8 | `course_type_name` | VARCHAR(50) |  | Tên loại học phần |
| 9 | `room` | VARCHAR(100) |  | Phòng học |
| 10 | `instructor` | VARCHAR(255) |  | Giảng viên phụ trách |
| 11 | `status` | VARCHAR(20) |  | Trạng thái lịch học |
| 12 | `created_at` | TIMESTAMP |  | Ngày tạo |
| 13 | `updated_at` | TIMESTAMP |  | Ngày cập nhật |

**Tối thiểu 3 trường dữ liệu bắt buộc:** `course_name`, `course_code`, `weekday`, `start_period`, `end_period`

---

## Cấu trúc thư mục
buoi2/
├── index.php # Giao diện chính và xử lý form
├── functions.php # Các hàm xử lý logic nghiệp vụ
├── database.php # Kết nối và thao tác với CSDL
├── config.php # Cấu hình hệ thống
├── style.css # CSS riêng biệt
├── sql/
│ └── schedule_db.sql # Script tạo database và dữ liệu mẫu
└── README.md # Tài liệu hướng dẫn (file này)

## Khả năng tái sử dụng và phát triển tiếp 
Các hàm trong functions.php có thể tái sử dụng:
getWeekdays() - Lấy danh sách thứ
getCourseTypes() - Lấy danh sách loại học phần
getStatusDefinitions() - Lấy định nghĩa trạng thái
checkScheduleConflict() - Kiểm tra xung đột lịch
filterSchedules() - Lọc danh sách lịch học
getScheduleStats() - Thống kê lịch học
validateScheduleData() - Validate dữ liệu
Có thể phát triển thành các chức năng:
Xem lịch học của sinh viên theo từng học kỳ
Xem lịch giảng dạy của giảng viên
Tự động gợi ý lịch học không bị xung đột
Xuất lịch học ra file PDF/Excel
Gửi thông báo nhắc nhở lịch học
## Chức năng chi tiết
1. Thêm lịch học mới
Người dùng nhập thông tin vào form
Hệ thống kiểm tra dữ liệu (validate)
Hệ thống kiểm tra xung đột lịch
Lưu vào database nếu hợp lệ
2. Xem danh sách lịch học
Hiển thị tất cả lịch học dưới dạng bảng
Hiển thị trạng thái với màu sắc phân biệt
Phân trang (nếu có nhiều dữ liệu)
3. Lọc dữ liệu
Lọc theo thứ trong tuần
Lọc theo loại học phần
Tìm kiếm theo từ khóa
4. Xóa lịch học
Xóa một lịch học cụ thể
Có xác nhận trước khi xóa
5. Cập nhật trạng thái
Tự động cập nhật trạng thái cho tất cả lịch học
Phân loại: available, conflict, pending
6. Thống kê
Tổng số lịch học
Số lượng theo từng trạng thái
Thống kê theo giảng viên