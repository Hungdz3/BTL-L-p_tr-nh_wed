# BTL-L-p_tr-nh_w

Hệ thống quản lý khóa học và đăng ký học phần, xây dựng bằng PHP, Javascript, MySQL

## Vai trò người dùng
- Sinh viên: xem lớp, đăng ký, hủy đăng ký, xem kết quả và xem hồ sơ cá nhân.
- Giảng viên: xem các lớp được phân công và danh sách sinh viên.
- Quản trị viên: Quản lý tài khoản và vai trò; quản lý học phần, học kỳ, lớp, sĩ số và trạng thái đăng ký

## Chức năng cốt lõi
1. Quản lý tài khoản và vai trò.
2. CRUD học kỳ, học phần và lớp học phần.
3. Đăng ký/hủy đăng ký; xem lịch sử đăng ký.
4. Tìm kiếm, lọc theo học kỳ, giảng viên và trạng thái.
5. Phân trang danh sách lớp; xuất danh sách lớp dạng CSV là tính năng mở rộng.
6. Endpoint JSON kiểm tra số chỗ còn lại hoặc tìm lớp theo từ khóa

## Yêu cầu môi trường
- PHP 8.2 trở lên
- MySQL/MariaDB
- Trình duyệt hiện đại (Chrome/Firefox/Edge)

## Cài đặt
- Clone repository
- Tạo cấu hình file
- Tạo database và import schema
- Chạy project trên localhost

## Quy tắc nghiệp vụ chính
- Không cho đăng ký trùng cùng một lớp.
- Không cho đăng ký khi lớp đã đủ sĩ số hoặc đã khóa.
- Sinh viên chỉ được hủy đăng ký của chính mình.
- Giảng viên chỉ xem được lớp mình phụ trách.
- Chỉ admin được thay đổi sĩ số, học kỳ và trạng thái lớp.
- Mọi thao tác dữ liệu dùng PDO prepared statement, có xử lý lỗi.
- Các quy tắc nghiệp vụ được kiểm tra ở server, không chỉ ẩn nút trên giao diện.
- Thao tác thay đổi dữ liệu (đăng ký, hủy...) được bảo vệ bằng CSRF token.

## Quy ước Git 
- Không làm trực tiếp trên `main`; tạo nhánh `feature/<ten-chuc-nang>`.
- Mỗi commit chỉ chứa một thay đổi có ý nghĩa.
- Tạo Pull Request để thành viên khác kiểm tra trước khi merge.


