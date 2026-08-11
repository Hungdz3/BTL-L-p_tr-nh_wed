# Hệ Thống Đăng Ký Học Phần

## Yêu cầu môi trường
- PHP >= 8.1
- Composer
- MySQL >= 8.0
- Apache/Nginx hoặc XAMPP/Laragon   

Kiểm tra phiên bản:
```bash
13
php -v
14
composer -V
Show more lines


Cài đặt project
1. Clone source code
git clone <repository-url>
cd <project-folder>
Show more lines

2. Cài đặt thư viện

3. Cấu hình môi trường
Sao chép file cấu hình:

Cập nhật thông tin kết nối cơ sở dữ liệu trong file .env:
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=course_registration
DB_USERNAME=root
DB_PASSWORD=

4. Tạo cơ sở dữ liệu

Tạo database:

SQL
CREATE DATABASE course_registration;

Nếu project sử dụng migration:
php artisan migrate


Hoặc import file SQL:
mysql -u root -p course_registration < database.sql

5. Chạy project local
Đối với Laravel:
php artisan serve

Truy cập:
Plain Text

http://127.0.0.1:8000
2
``
Show more lines
6. Tài khoản mẫu
Admin
Username: admin
Password: admin123

Sinh viên
Username: student01
Password: 123456

Giảng viên
Username: lecturer01
Password: 123456

Chức năng chính
Đăng nhập và phân quyền người dùng.
Quản lý học kỳ, học phần, lớp học phần.
Đăng ký và hủy đăng ký học phần.
Xem lịch sử đăng ký.
Tìm kiếm và lọc lớp học phần.
Quản lý sĩ số và trạng thái đăng ký.
Cấu trúc thư mục


Plain Text
1
project/
2
├── app/
3
├── config/
4
├── database/
5
├── public/
6
├── resources/
7
├── routes/
8
├── storage/
9
├── .env
10
└── README.md
11