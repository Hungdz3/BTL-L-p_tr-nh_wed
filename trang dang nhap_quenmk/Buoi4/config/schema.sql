-- Schema mẫu cho hệ thống đăng nhập sinh viên/giảng viên - Trường Đại học HNDA
-- Import file này vào phpMyAdmin/MySQL để tạo bảng.

CREATE DATABASE IF NOT EXISTS hnda_qlsv CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE hnda_qlsv;

-- Bảng sinh viên
CREATE TABLE IF NOT EXISTS sinh_vien (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ma_sv VARCHAR(20) NOT NULL UNIQUE,
    email VARCHAR(150) NOT NULL UNIQUE,
    ho_ten VARCHAR(150) NOT NULL,
    ngay_sinh DATE NOT NULL,
    so_dt VARCHAR(15) NOT NULL,
    mat_khau VARCHAR(255) NOT NULL,   -- lưu bằng password_hash(), KHÔNG lưu plain text
    trang_thai TINYINT(1) NOT NULL DEFAULT 1, -- 1 = hoạt động, 0 = khóa
    tao_luc TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Bảng giảng viên
CREATE TABLE IF NOT EXISTS giang_vien (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ma_gv VARCHAR(20) NOT NULL UNIQUE,
    email VARCHAR(150) NOT NULL UNIQUE,
    ho_ten VARCHAR(150) NOT NULL,
    ngay_sinh DATE NOT NULL,
    so_dt VARCHAR(15) NOT NULL,
    mat_khau VARCHAR(255) NOT NULL,
    trang_thai TINYINT(1) NOT NULL DEFAULT 1,
    tao_luc TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Dữ liệu mẫu để test đăng nhập (mật khẩu: 123456)
-- Hash bên dưới tương ứng với "123456" qua password_hash() thuật toán bcrypt
INSERT INTO sinh_vien (ma_sv, email, ho_ten, ngay_sinh, so_dt, mat_khau) VALUES
('SV001', 'sv001@hnda.edu.vn', 'Nguyễn Văn A', '2003-05-10', '0901234567',
 '$2y$10$9x1Q2r7e8Z1o1Q1a1YbYVeQb6nQ1E1o1Q1a1YbYVeQb6nQ1E1o1Q1');
-- Lưu ý: hash mẫu trên chỉ minh họa định dạng, hãy tự tạo hash thật bằng password_hash('123456', PASSWORD_DEFAULT) trong PHP.