
IF NOT EXISTS (SELECT * FROM sys.databases WHERE name = N'QuanLyDiemDB')
BEGIN
    CREATE DATABASE QuanLyDiemDB;
END
GO

USE QuanLyDiemDB;
GO

-- 1. TẠO BẢNG
IF OBJECT_ID('dbo.Diem', 'U') IS NOT NULL DROP TABLE dbo.Diem;
IF OBJECT_ID('dbo.LopHoc', 'U') IS NOT NULL DROP TABLE dbo.LopHoc;
IF OBJECT_ID('dbo.HocVien', 'U') IS NOT NULL DROP TABLE dbo.HocVien;
IF OBJECT_ID('dbo.GiangVien', 'U') IS NOT NULL DROP TABLE dbo.GiangVien;

CREATE TABLE dbo.GiangVien (
    MaGV NVARCHAR(20) PRIMARY KEY,
    HoTen NVARCHAR(100) NOT NULL,
    HocHamHocVi NVARCHAR(50),
    BoMon NVARCHAR(100),
    Khoa NVARCHAR(100),
    Email NVARCHAR(100)
);

CREATE TABLE dbo.LopHoc (
    MaLop NVARCHAR(20) PRIMARY KEY,
    TenMonHoc NVARCHAR(150) NOT NULL,
    HocKy NVARCHAR(20) NOT NULL,
    NamHoc INT NOT NULL,
    MaGV NVARCHAR(20) NOT NULL,
    FOREIGN KEY (MaGV) REFERENCES dbo.GiangVien(MaGV) ON DELETE CASCADE
);

CREATE TABLE dbo.HocVien (
    MSSV NVARCHAR(20) PRIMARY KEY,
    HoTen NVARCHAR(100) NOT NULL,
    NgaySinh DATE,
    LopSinhHoat NVARCHAR(50),
    Email NVARCHAR(100)
);

CREATE TABLE dbo.Diem (
    Id INT IDENTITY(1,1) PRIMARY KEY,
    MaLop NVARCHAR(20) NOT NULL,
    MSSV NVARCHAR(20) NOT NULL,
    DiemCC DECIMAL(4,1) DEFAULT 0.0 CHECK (DiemCC BETWEEN 0.0 AND 10.0),
    DiemGK DECIMAL(4,1) DEFAULT 0.0 CHECK (DiemGK BETWEEN 0.0 AND 10.0),
    DiemCK DECIMAL(4,1) DEFAULT 0.0 CHECK (DiemCK BETWEEN 0.0 AND 10.0),
    FOREIGN KEY (MaLop) REFERENCES dbo.LopHoc(MaLop) ON DELETE CASCADE,
    FOREIGN KEY (MSSV) REFERENCES dbo.HocVien(MSSV) ON DELETE CASCADE,
    CONSTRAINT UQ_Diem_Lop_HocVien UNIQUE (MaLop, MSSV)
);
GO

-- 2. CHÈN GIẢNG VIÊN VÀ 5 LỚP HỌC MẪU
INSERT INTO dbo.GiangVien (MaGV, HoTen, HocHamHocVi, BoMon, Khoa, Email) VALUES
(N'GV001', N'Nguyễn Minh Châu', N'TS.', N'Công nghệ phần mềm', N'Công nghệ thông tin', N'chaunm@hnda.edu.vn');

INSERT INTO dbo.LopHoc (MaLop, TenMonHoc, HocKy, NamHoc, MaGV) VALUES
(N'CS101', N'CS101 - Lập trình Python - HK1 2025', N'HK1', 2025, N'GV001'),
(N'CS102', N'CS102 - Cấu trúc dữ liệu & Giải thuật - HK1 2025', N'HK1', 2025, N'GV001'),
(N'CS103', N'CS103 - Lập trình Web PHP & MySQL - HK2 2025', N'HK2', 2025, N'GV001'),
(N'CS104', N'CS104 - Cơ sở dữ liệu & SQL Server - HK2 2025', N'HK2', 2025, N'GV001'),
(N'CS105', N'CS105 - Phân tích & Thiết kế hệ thống - HK1 2026', N'HK1', 2026, N'GV001');

-- 3. CHÈN 40 HỌC VIÊN
INSERT INTO dbo.HocVien (MSSV, HoTen, LopSinhHoat) VALUES
(N'21120042', N'Nguyễn Đức Anh', N'K66-CNTT'),
(N'21120158', N'Trần Thị Thanh Thảo', N'K66-CNTT'),
(N'21120204', N'Lê Hoàng Nam', N'K66-CNTT'),
(N'21120291', N'Phạm Minh Đức', N'K66-CNTT'),
(N'21120330', N'Vũ Hoàng Long', N'K66-CNTT'),
(N'21120412', N'Đỗ Thùy Dương', N'K66-CNTT'),
(N'21120509', N'Ngô Quốc Bảo', N'K66-CNTT'),
(N'21120593', N'Bùi Minh Tuấn', N'K66-CNTT'),
(N'21120681', N'Hoàng Thị Mai', N'K66-CNTT'),
(N'21120754', N'Phan Văn Khải', N'K66-CNTT'),
(N'21120780', N'Đặng Phương Anh', N'K66-CNTT'),
(N'21120812', N'Dương Gia Bảo', N'K66-CNTT'),
(N'21120855', N'Nguyễn Văn Huy', N'K66-CNTT'),
(N'21120890', N'Lê Thị Bảo Ngọc', N'K66-CNTT'),
(N'21120921', N'Trần Tuấn Kiệt', N'K66-CNTT'),
(N'21120964', N'Vũ Ngọc Ánh', N'K66-CNTT'),
(N'21121002', N'Hoàng Khánh Linh', N'K66-CNTT'),
(N'21121045', N'Phạm Quang Minh', N'K66-CNTT'),
(N'21121089', N'Đỗ Hoài Nam', N'K66-CNTT'),
(N'21121120', N'Nguyễn Thành Long', N'K66-CNTT'),
(N'21121165', N'Ngô Thùy Trang', N'K66-CNTT'),
(N'21121201', N'Bùi Tiến Dũng', N'K66-CNTT'),
(N'21121244', N'Phan Thị Hà', N'K66-CNTT'),
(N'21121288', N'Lê Quốc Khánh', N'K66-CNTT'),
(N'21121320', N'Trịnh Văn Sơn', N'K66-CNTT'),
(N'21121366', N'Đặng Thanh Tùng', N'K66-CNTT'),
(N'21121402', N'Nguyễn Thị Yến', N'K66-CNTT'),
(N'21121450', N'Vũ Hoàng Anh', N'K66-CNTT'),
(N'21121491', N'Đỗ Minh Châu', N'K66-CNTT'),
(N'21121533', N'Phạm Hữu Đạt', N'K66-CNTT'),
(N'21121570', N'Hoàng Trung Kiên', N'K66-CNTT'),
(N'21121612', N'Lê Mai Phương', N'K66-CNTT'),
(N'21121655', N'Ngô Như Quỳnh', N'K66-CNTT'),
(N'21121700', N'Bùi Đức Thắng', N'K66-CNTT'),
(N'21121742', N'Trần Minh Trí', N'K66-CNTT'),
(N'21121788', N'Nguyễn Văn Trọng', N'K66-CNTT'),
(N'21121820', N'Phan Anh Tú', N'K66-CNTT'),
(N'21121865', N'Vũ Thị Cẩm Tú', N'K66-CNTT'),
(N'21121901', N'Đặng Văn Vinh', N'K66-CNTT'),
(N'21121950', N'Hoàng Mỹ Duyên', N'K66-CNTT');

-- 4. CHÈN ĐIỂM CHO LỚP CS101 (40 Học viên)
INSERT INTO dbo.Diem (MaLop, MSSV, DiemCC, DiemGK, DiemCK) VALUES
(N'CS101', N'21120042', 10.0, 8.5, 9.0),
(N'CS101', N'21120158', 9.5, 9.0, 9.5),
(N'CS101', N'21120204', 8.0, 7.0, 7.5),
(N'CS101', N'21120291', 9.0, 6.0, 5.5),
(N'CS101', N'21120330', 10.0, 4.5, 5.0),
(N'CS101', N'21120412', 9.5, 8.0, 8.5),
(N'CS101', N'21120509', 8.5, 5.0, 4.0),
(N'CS101', N'21120593', 10.0, 9.5, 9.5),
(N'CS101', N'21120681', 7.0, 4.0, 3.5),
(N'CS101', N'21120754', 5.0, 3.0, 3.0),
(N'CS101', N'21120780', 9.0, 8.0, 8.5),
(N'CS101', N'21120812', 8.5, 7.5, 8.0),
(N'CS101', N'21120855', 9.5, 8.5, 9.0),
(N'CS101', N'21120890', 10.0, 9.0, 9.0),
(N'CS101', N'21120921', 8.0, 6.5, 7.0),
(N'CS101', N'21120964', 9.0, 8.5, 8.0),
(N'CS101', N'21121002', 8.5, 8.0, 8.5),
(N'CS101', N'21121045', 7.5, 6.0, 6.5),
(N'CS101', N'21121089', 9.0, 7.5, 7.5),
(N'CS101', N'21121120', 8.5, 8.0, 8.0),
(N'CS101', N'21121165', 9.5, 9.0, 9.2),
(N'CS101', N'21121201', 8.0, 7.0, 7.2),
(N'CS101', N'21121244', 9.0, 8.5, 8.8),
(N'CS101', N'21121288', 10.0, 8.0, 8.5),
(N'CS101', N'21121320', 7.0, 5.5, 6.0),
(N'CS101', N'21121366', 8.5, 7.5, 7.8),
(N'CS101', N'21121402', 9.0, 9.0, 9.5),
(N'CS101', N'21121450', 8.0, 6.5, 6.8),
(N'CS101', N'21121491', 9.5, 8.5, 8.5),
(N'CS101', N'21121533', 7.5, 6.0, 6.0),
(N'CS101', N'21121570', 8.5, 7.0, 7.5),
(N'CS101', N'21121612', 9.0, 8.0, 8.2),
(N'CS101', N'21121655', 10.0, 9.5, 9.8),
(N'CS101', N'21121700', 8.5, 7.5, 7.5),
(N'CS101', N'21121742', 9.0, 8.0, 8.5),
(N'CS101', N'21121788', 7.0, 5.0, 5.5),
(N'CS101', N'21121820', 8.5, 8.0, 8.0),
(N'CS101', N'21121865', 9.5, 8.5, 9.0),
(N'CS101', N'21121901', 8.0, 6.5, 7.0),
(N'CS101', N'21121950', 9.0, 8.5, 8.7);

-- 5. CHÈN ĐIỂM CHO CÁC LỚP KHÁC (CS102, CS103, CS104, CS105)
INSERT INTO dbo.Diem (MaLop, MSSV, DiemCC, DiemGK, DiemCK) VALUES
(N'CS102', N'21120042', 9.0, 8.0, 8.5),
(N'CS102', N'21120158', 10.0, 9.5, 9.0),
(N'CS102', N'21120204', 8.5, 7.5, 8.0),
(N'CS102', N'21120291', 7.0, 6.0, 6.5),
(N'CS102', N'21120330', 9.5, 8.0, 8.5),
(N'CS102', N'21120412', 9.0, 8.5, 9.0),
(N'CS102', N'21120509', 8.0, 6.0, 5.5),
(N'CS102', N'21120593', 10.0, 10.0, 9.8),
(N'CS102', N'21120681', 8.5, 7.0, 7.5),
(N'CS102', N'21120754', 6.0, 5.0, 5.5),

(N'CS103', N'21120042', 10.0, 9.0, 9.5),
(N'CS103', N'21120158', 9.0, 8.5, 9.0),
(N'CS103', N'21120204', 8.0, 8.0, 8.5),
(N'CS103', N'21120291', 8.5, 7.0, 7.5),
(N'CS103', N'21120330', 9.0, 7.5, 8.0),
(N'CS103', N'21120412', 10.0, 9.0, 9.2),
(N'CS103', N'21120509', 7.5, 6.5, 6.0),
(N'CS103', N'21120593', 9.5, 9.0, 9.5),

(N'CS104', N'21120042', 8.5, 8.5, 8.5),
(N'CS104', N'21120158', 9.5, 9.0, 9.5),
(N'CS104', N'21120204', 9.0, 8.0, 8.0),
(N'CS104', N'21120291', 8.0, 7.5, 7.0),
(N'CS104', N'21120330', 8.5, 8.0, 8.5),
(N'CS104', N'21120412', 9.0, 9.0, 9.0),

(N'CS105', N'21120042', 9.5, 9.0, 9.0),
(N'CS105', N'21120158', 10.0, 9.5, 9.5),
(N'CS105', N'21120204', 8.5, 8.5, 8.5),
(N'CS105', N'21120291', 7.5, 7.0, 7.5),
(N'CS105', N'21120593', 10.0, 9.5, 10.0);
GO
