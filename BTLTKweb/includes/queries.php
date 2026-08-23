<?php
/**
 * Chứa các câu truy vấn CSDL SQL Server với JOIN và WHERE.
 * Tương thích 100% với cả SQL Server (SSMS) và SQLite.
 * File: D:\BTLTKweb\includes\queries.php
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/functions.php';

/**
 * Lấy danh sách tất cả các Lớp học kèm Số lượng học viên cho Dropdown / Modal Chọn lớp
 */
function getDanhSachLopHoc() {
    $pdo = getDbConnection();
    $sql = "SELECT 
                l.MaLop, 
                l.TenMonHoc, 
                l.HocKy, 
                l.NamHoc, 
                COUNT(d.MSSV) AS SoHocVien
            FROM LopHoc l
            LEFT JOIN Diem d ON l.MaLop = d.MaLop
            GROUP BY l.MaLop, l.TenMonHoc, l.HocKy, l.NamHoc
            ORDER BY l.MaLop ASC";
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll();
}

/**
 * CÂU SELECT 1: Lấy danh sách học viên & điểm số theo Lớp học (có Phân trang & Tìm kiếm) bằng JOIN và WHERE
 */
function getDanhSachDiemLop($maLop = 'CS101', $keyword = '', $page = 1, $perPage = 10) {
    $pdo = getDbConnection();
    
    $countSql = "SELECT COUNT(*) FROM Diem d 
                 INNER JOIN HocVien h ON d.MSSV = h.MSSV 
                 WHERE d.MaLop = :maLop";
    $countParams = [':maLop' => $maLop];

    if (!empty($keyword)) {
        $countSql .= " AND (h.MSSV LIKE :kw OR h.HoTen LIKE :kw)";
        $countParams[':kw'] = '%' . $keyword . '%';
    }

    $stmtCount = $pdo->prepare($countSql);
    $stmtCount->execute($countParams);
    $totalRecords = (int)$stmtCount->fetchColumn();

    $totalPages = max(1, (int)ceil($totalRecords / $perPage));
    $page = max(1, min($page, $totalPages));
    $offset = ($page - 1) * $perPage;

    $sql = "SELECT 
                h.MSSV, 
                h.HoTen, 
                h.LopSinhHoat,
                d.DiemCC, 
                d.DiemGK, 
                d.DiemCK
            FROM Diem d
            INNER JOIN HocVien h ON d.MSSV = h.MSSV
            INNER JOIN LopHoc l ON d.MaLop = l.MaLop
            WHERE d.MaLop = :maLop";

    $params = [':maLop' => $maLop];

    if (!empty($keyword)) {
        $sql .= " AND (h.MSSV LIKE :kw OR h.HoTen LIKE :kw)";
        $params[':kw'] = '%' . $keyword . '%';
    }

    $sql .= " ORDER BY h.MSSV ASC LIMIT :limit OFFSET :offset";

    try {
        $stmt = $pdo->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll();
    } catch (PDOException $e) {
        $sqlSqlSrv = "SELECT 
                        h.MSSV, 
                        h.HoTen, 
                        h.LopSinhHoat,
                        d.DiemCC, 
                        d.DiemGK, 
                        d.DiemCK
                    FROM Diem d
                    INNER JOIN HocVien h ON d.MSSV = h.MSSV
                    INNER JOIN LopHoc l ON d.MaLop = l.MaLop
                    WHERE d.MaLop = :maLop";
        if (!empty($keyword)) {
            $sqlSqlSrv .= " AND (h.MSSV LIKE :kw OR h.HoTen LIKE :kw)";
        }
        $sqlSqlSrv .= " ORDER BY h.MSSV ASC OFFSET $offset ROWS FETCH NEXT $perPage ROWS ONLY";
        
        $stmt = $pdo->prepare($sqlSqlSrv);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();
    }

    foreach ($rows as &$row) {
        $row['TongKet'] = tinhTongKet($row['DiemCC'], $row['DiemGK'], $row['DiemCK']);
        $row['XepLoai'] = xepLoaiDiem($row['TongKet']);
    }

    return [
        'items' => $rows,
        'total_records' => $totalRecords,
        'total_pages' => $totalPages,
        'current_page' => $page,
        'per_page' => $perPage,
        'start_index' => $offset + 1
    ];
}

/**
 * CÂU SELECT 2: Thống kê các chỉ số của Lớp học (Sĩ số, Điểm TB, Tỷ lệ đạt) dùng JOIN và WHERE
 */
function getThongKeLopHoc($maLop = 'CS101') {
    $pdo = getDbConnection();
    
    $sql = "SELECT 
                d.MSSV, 
                d.DiemCC, 
                d.DiemGK, 
                d.DiemCK
            FROM Diem d
            INNER JOIN LopHoc l ON d.MaLop = l.MaLop
            WHERE d.MaLop = :maLop";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':maLop' => $maLop]);
    $rows = $stmt->fetchAll();

    $siSo = count($rows);
    if ($siSo === 0) {
        return [
            'si_so' => 0,
            'diem_tb' => 0.0,
            'so_dat' => 0,
            'ty_le_dat' => 0.0,
            'diem_cao_nhat' => 0.0,
            'hoc_vien_top' => 'N/A'
        ];
    }

    $tongDiemLop = 0;
    $soDat = 0;
    $maxDiem = -1;

    foreach ($rows as $row) {
        $tk = tinhTongKet($row['DiemCC'], $row['DiemGK'], $row['DiemCK']);
        $tongDiemLop += $tk;
        if ($tk >= 5.0) {
            $soDat++;
        }
        if ($tk > $maxDiem) {
            $maxDiem = $tk;
        }
    }

    $diemTB = round($tongDiemLop / $siSo, 1);
    $tyLeDat = round(($soDat / $siSo) * 100, 1);

    $topStudent = getHocVienDiemCaoNhat($maLop);

    return [
        'si_so' => $siSo,
        'diem_tb' => $diemTB,
        'so_dat' => $soDat,
        'ty_le_dat' => $tyLeDat,
        'diem_cao_nhat' => $maxDiem > 0 ? $maxDiem : 0.0,
        'hoc_vien_top' => $topStudent['HoTen'] . ' (' . $topStudent['MSSV'] . ')'
    ];
}

/**
 * CÂU SELECT 3: Lấy học viên có điểm cao nhất bằng JOIN, ORDER BY & WHERE
 */
function getHocVienDiemCaoNhat($maLop = 'CS101') {
    $pdo = getDbConnection();

    $sql = "SELECT 
                h.MSSV, 
                h.HoTen, 
                (d.DiemCC * 0.10 + d.DiemGK * 0.30 + d.DiemCK * 0.60) AS TongKetCalc
            FROM Diem d
            INNER JOIN HocVien h ON d.MSSV = h.MSSV
            WHERE d.MaLop = :maLop
            ORDER BY TongKetCalc DESC LIMIT 1";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':maLop' => $maLop]);
        $row = $stmt->fetch();
    } catch (PDOException $e) {
        $sqlSqlSrv = "SELECT TOP 1
                    h.MSSV, 
                    h.HoTen, 
                    (d.DiemCC * 0.10 + d.DiemGK * 0.30 + d.DiemCK * 0.60) AS TongKetCalc
                FROM Diem d
                INNER JOIN HocVien h ON d.MSSV = h.MSSV
                WHERE d.MaLop = :maLop
                ORDER BY TongKetCalc DESC";
        $stmt = $pdo->prepare($sqlSqlSrv);
        $stmt->execute([':maLop' => $maLop]);
        $row = $stmt->fetch();
    }

    if ($row) {
        return [
            'MSSV' => $row['MSSV'],
            'HoTen' => $row['HoTen'],
            'TongKet' => round(floatval($row['TongKetCalc']), 1)
        ];
    }

    return ['MSSV' => '', 'HoTen' => 'Chưa có', 'TongKet' => 0.0];
}

/**
 * Lưu / Cập nhật điểm cho Học viên
 */
function luuCapNhatDiem($maLop, $mssv, $diemCC, $diemGK, $diemCK) {
    $pdo = getDbConnection();
    
    $checkSql = "SELECT COUNT(*) FROM Diem WHERE MaLop = :maLop AND MSSV = :mssv";
    $stmtCheck = $pdo->prepare($checkSql);
    $stmtCheck->execute([':maLop' => $maLop, ':mssv' => $mssv]);
    $exists = $stmtCheck->fetchColumn() > 0;

    if ($exists) {
        $updateSql = "UPDATE Diem SET DiemCC = :cc, DiemGK = :gk, DiemCK = :ck WHERE MaLop = :maLop AND MSSV = :mssv";
        $stmt = $pdo->prepare($updateSql);
        return $stmt->execute([':cc' => $diemCC, ':gk' => $diemGK, ':ck' => $diemCK, ':maLop' => $maLop, ':mssv' => $mssv]);
    } else {
        $insertSql = "INSERT INTO Diem (MaLop, MSSV, DiemCC, DiemGK, DiemCK) VALUES (:maLop, :mssv, :cc, :gk, :ck)";
        $stmt = $pdo->prepare($insertSql);
        return $stmt->execute([':maLop' => $maLop, ':mssv' => $mssv, ':cc' => $diemCC, ':gk' => $diemGK, ':ck' => $diemCK]);
    }
}

/**
 * Thêm mới học viên và khởi tạo điểm
 */
function themHocVienVoiDiem($maLop, $mssv, $hoTen, $diemCC, $diemGK, $diemCK) {
    $pdo = getDbConnection();
    
    $sqlHv = "SELECT COUNT(*) FROM HocVien WHERE MSSV = :mssv";
    $stmtHvCheck = $pdo->prepare($sqlHv);
    $stmtHvCheck->execute([':mssv' => $mssv]);
    if ($stmtHvCheck->fetchColumn() == 0) {
        $stmtInsHv = $pdo->prepare("INSERT INTO HocVien (MSSV, HoTen, LopSinhHoat) VALUES (:mssv, :hoTen, 'K66-CNTT')");
        $stmtInsHv->execute([':mssv' => $mssv, ':hoTen' => $hoTen]);
    } else {
        $stmtUpdHv = $pdo->prepare("UPDATE HocVien SET HoTen = :hoTen WHERE MSSV = :mssv");
        $stmtUpdHv->execute([':hoTen' => $hoTen, ':mssv' => $mssv]);
    }

    return luuCapNhatDiem($maLop, $mssv, $diemCC, $diemGK, $diemCK);
}

/**
 * Sửa thông tin Họ tên và 3 cột điểm của học viên
 */
function capNhatThongTinHocVienVoiDiem($maLop, $mssv, $hoTen, $diemCC, $diemGK, $diemCK) {
    $pdo = getDbConnection();
    $stmtUpdHv = $pdo->prepare("UPDATE HocVien SET HoTen = :hoTen WHERE MSSV = :mssv");
    $stmtUpdHv->execute([':hoTen' => $hoTen, ':mssv' => $mssv]);
    return luuCapNhatDiem($maLop, $mssv, $diemCC, $diemGK, $diemCK);
}

/**
 * Xóa học viên khỏi lớp học (DELETE)
 */
function xoaHocVienKhoiLop($maLop, $mssv) {
    $pdo = getDbConnection();
    $stmt = $pdo->prepare("DELETE FROM Diem WHERE MaLop = :maLop AND MSSV = :mssv");
    return $stmt->execute([':maLop' => $maLop, ':mssv' => $mssv]);
}
