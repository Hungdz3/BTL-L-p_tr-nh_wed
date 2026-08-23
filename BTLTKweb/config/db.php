<?php
/**
 * Kết nối CSDL SQL Server Manager bằng PDO
 * Hỗ trợ SQL Server (sqlsrv / odbc) và Tự động Fallback SQLite với nhiều lớp học mẫu.
 */

define('DB_SERVER', 'localhost');
define('DB_NAME', 'QuanLyDiemDB');
define('DB_USER', 'sa');
define('DB_PASS', '123456');

function getDbConnection() {
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    $errors = [];

    // 1. Thử kết nối SQL Server qua driver PDO_SQLSRV
    if (in_array('sqlsrv', PDO::getAvailableDrivers())) {
        try {
            $dsn = "sqlsrv:Server=" . DB_SERVER . ";Database=" . DB_NAME . ";TrustServerCertificate=true";
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
            return $pdo;
        } catch (PDOException $e) {
            $errors[] = "SQLSRV Driver Error: " . $e->getMessage();
        }
    }

    // 2. Thử kết nối SQL Server qua ODBC
    if (in_array('odbc', PDO::getAvailableDrivers())) {
        try {
            $dsn = "odbc:Driver={SQL Server};Server=" . DB_SERVER . ";Database=" . DB_NAME . ";";
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
            return $pdo;
        } catch (PDOException $e) {
            $errors[] = "ODBC Driver Error: " . $e->getMessage();
        }
    }

    // 3. Fallback: Sử dụng SQLite nếu chưa cài SQL Server extension trên môi trường PHP cục bộ
    $sqliteFile = __DIR__ . '/QuanLyDiemDB.sqlite';
    $needInit = !file_exists($sqliteFile);

    try {
        $pdo = new PDO("sqlite:" . $sqliteFile, null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);

        if ($needInit) {
            initSqliteDatabase($pdo);
        }
        return $pdo;
    } catch (PDOException $e) {
        die("Không thể kết nối CSDL SQL Server hoặc SQLite Fallback: " . $e->getMessage());
    }
}

/**
 * Khởi tạo dữ liệu mẫu cho SQLite Fallback (5 Lớp Học & 40 Sinh viên)
 */
function initSqliteDatabase($pdo) {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS GiangVien (
            MaGV TEXT PRIMARY KEY,
            HoTen TEXT NOT NULL,
            HocHamHocVi TEXT,
            BoMon TEXT,
            Khoa TEXT,
            Email TEXT
        );

        CREATE TABLE IF NOT EXISTS LopHoc (
            MaLop TEXT PRIMARY KEY,
            TenMonHoc TEXT NOT NULL,
            HocKy TEXT NOT NULL,
            NamHoc INTEGER NOT NULL,
            MaGV TEXT NOT NULL
        );

        CREATE TABLE IF NOT EXISTS HocVien (
            MSSV TEXT PRIMARY KEY,
            HoTen TEXT NOT NULL,
            NgaySinh TEXT,
            LopSinhHoat TEXT,
            Email TEXT
        );

        CREATE TABLE IF NOT EXISTS Diem (
            Id INTEGER PRIMARY KEY AUTOINCREMENT,
            MaLop TEXT NOT NULL,
            MSSV TEXT NOT NULL,
            DiemCC REAL DEFAULT 0.0,
            DiemGK REAL DEFAULT 0.0,
            DiemCK REAL DEFAULT 0.0,
            UNIQUE(MaLop, MSSV)
        );
    ");

    // Chèn dữ liệu giảng viên & 5 lớp mẫu
    $pdo->exec("INSERT OR IGNORE INTO GiangVien VALUES ('GV001', 'Nguyễn Minh Châu', 'TS.', 'Công nghệ phần mềm', 'Công nghệ thông tin', 'chaunm@hnda.edu.vn')");
    $pdo->exec("INSERT OR IGNORE INTO LopHoc VALUES ('CS101', 'CS101 - Lập trình Python - HK1 2025', 'HK1', 2025, 'GV001')");
    $pdo->exec("INSERT OR IGNORE INTO LopHoc VALUES ('CS102', 'CS102 - Cấu trúc dữ liệu & Giải thuật - HK1 2025', 'HK1', 2025, 'GV001')");
    $pdo->exec("INSERT OR IGNORE INTO LopHoc VALUES ('CS103', 'CS103 - Lập trình Web PHP & MySQL - HK2 2025', 'HK2', 2025, 'GV001')");
    $pdo->exec("INSERT OR IGNORE INTO LopHoc VALUES ('CS104', 'CS104 - Cơ sở dữ liệu & SQL Server - HK2 2025', 'HK2', 2025, 'GV001')");
    $pdo->exec("INSERT OR IGNORE INTO LopHoc VALUES ('CS105', 'CS105 - Phân tích & Thiết kế hệ thống - HK1 2026', 'HK1', 2026, 'GV001')");

    // Thêm 40 sinh viên mẫu
    $students = [
        ['21120042', 'Nguyễn Đức Anh', 10.0, 8.5, 9.0],
        ['21120158', 'Trần Thị Thanh Thảo', 9.5, 9.0, 9.5],
        ['21120204', 'Lê Hoàng Nam', 8.0, 7.0, 7.5],
        ['21120291', 'Phạm Minh Đức', 9.0, 6.0, 5.5],
        ['21120330', 'Vũ Hoàng Long', 10.0, 4.5, 5.0],
        ['21120412', 'Đỗ Thùy Dương', 9.5, 8.0, 8.5],
        ['21120509', 'Ngô Quốc Bảo', 8.5, 5.0, 4.0],
        ['21120593', 'Bùi Minh Tuấn', 10.0, 9.5, 9.5],
        ['21120681', 'Hoàng Thị Mai', 7.0, 4.0, 3.5],
        ['21120754', 'Phan Văn Khải', 5.0, 3.0, 3.0],
        ['21120780', 'Đặng Phương Anh', 9.0, 8.0, 8.5],
        ['21120812', 'Dương Gia Bảo', 8.5, 7.5, 8.0],
        ['21120855', 'Nguyễn Văn Huy', 9.5, 8.5, 9.0],
        ['21120890', 'Lê Thị Bảo Ngọc', 10.0, 9.0, 9.0],
        ['21120921', 'Trần Tuấn Kiệt', 8.0, 6.5, 7.0],
        ['21120964', 'Vũ Ngọc Ánh', 9.0, 8.5, 8.0],
        ['21121002', 'Hoàng Khánh Linh', 8.5, 8.0, 8.5],
        ['21121045', 'Phạm Quang Minh', 7.5, 6.0, 6.5],
        ['21121089', 'Đỗ Hoài Nam', 9.0, 7.5, 7.5],
        ['21121120', 'Nguyễn Thành Long', 8.5, 8.0, 8.0],
        ['21121165', 'Ngô Thùy Trang', 9.5, 9.0, 9.2],
        ['21121201', 'Bùi Tiến Dũng', 8.0, 7.0, 7.2],
        ['21121244', 'Phan Thị Hà', 9.0, 8.5, 8.8],
        ['21121288', 'Lê Quốc Khánh', 10.0, 8.0, 8.5],
        ['21121320', 'Trịnh Văn Sơn', 7.0, 5.5, 6.0],
        ['21121366', 'Đặng Thanh Tùng', 8.5, 7.5, 7.8],
        ['21121402', 'Nguyễn Thị Yến', 9.0, 9.0, 9.5],
        ['21121450', 'Vũ Hoàng Anh', 8.0, 6.5, 6.8],
        ['21121491', 'Đỗ Minh Châu', 9.5, 8.5, 8.5],
        ['21121533', 'Phạm Hữu Đạt', 7.5, 6.0, 6.0],
        ['21121570', 'Hoàng Trung Kiên', 8.5, 7.0, 7.5],
        ['21121612', 'Lê Mai Phương', 9.0, 8.0, 8.2],
        ['21121655', 'Ngô Như Quỳnh', 10.0, 9.5, 9.8],
        ['21121700', 'Bùi Đức Thắng', 8.5, 7.5, 7.5],
        ['21121742', 'Trần Minh Trí', 9.0, 8.0, 8.5],
        ['21121788', 'Nguyễn Văn Trọng', 7.0, 5.0, 5.5],
        ['21121820', 'Phan Anh Tú', 8.5, 8.0, 8.0],
        ['21121865', 'Vũ Thị Cẩm Tú', 9.5, 8.5, 9.0],
        ['21121901', 'Đặng Văn Vinh', 8.0, 6.5, 7.0],
        ['21121950', 'Hoàng Mỹ Duyên', 9.0, 8.5, 8.7]
    ];

    $stmtHv = $pdo->prepare("INSERT OR IGNORE INTO HocVien (MSSV, HoTen, LopSinhHoat) VALUES (?, ?, 'K66-CNTT')");
    $stmtDiemCS101 = $pdo->prepare("INSERT OR IGNORE INTO Diem (MaLop, MSSV, DiemCC, DiemGK, DiemCK) VALUES ('CS101', ?, ?, ?, ?)");

    foreach ($students as $s) {
        $stmtHv->execute([$s[0], $s[1]]);
        $stmtDiemCS101->execute([$s[0], $s[2], $s[3], $s[4]]);
    }

    // Chèn điểm cho các lớp CS102, CS103, CS104, CS105
    $otherGrades = [
        ['CS102', '21120042', 9.0, 8.0, 8.5],
        ['CS102', '21120158', 10.0, 9.5, 9.0],
        ['CS102', '21120204', 8.5, 7.5, 8.0],
        ['CS102', '21120291', 7.0, 6.0, 6.5],
        ['CS102', '21120330', 9.5, 8.0, 8.5],

        ['CS103', '21120042', 10.0, 9.0, 9.5],
        ['CS103', '21120158', 9.0, 8.5, 9.0],
        ['CS103', '21120204', 8.0, 8.0, 8.5],
        ['CS103', '21120291', 8.5, 7.0, 7.5],

        ['CS104', '21120042', 8.5, 8.5, 8.5],
        ['CS104', '21120158', 9.5, 9.0, 9.5],
        ['CS104', '21120204', 9.0, 8.0, 8.0],

        ['CS105', '21120042', 9.5, 9.0, 9.0],
        ['CS105', '21120158', 10.0, 9.5, 9.5],
        ['CS105', '21120593', 10.0, 9.5, 10.0]
    ];

    $stmtOther = $pdo->prepare("INSERT OR IGNORE INTO Diem (MaLop, MSSV, DiemCC, DiemGK, DiemCK) VALUES (?, ?, ?, ?, ?)");
    foreach ($otherGrades as $og) {
        $stmtOther->execute($og);
    }
}
