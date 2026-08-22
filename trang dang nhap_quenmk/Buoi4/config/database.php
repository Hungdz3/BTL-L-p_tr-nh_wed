<?php

// ===== Thông tin kết nối - SỬA LẠI cho đúng với server của bạn =====
$dbHost = 'localhost';
$dbName = '';      
$dbUser = 'root';          
$dbPass = 'vertrigo';                
$dbCharset = 'utf8mb4';

$dsn = "mysql:host={$dbHost};dbname={$dbName};charset={$dbCharset}";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // báo lỗi bằng exception thay vì im lặng
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,        // fetch trả về mảng kết hợp
    PDO::ATTR_EMULATE_PREPARES   => false,                    // dùng prepared statement thật của MySQL
];

try {
    $pdo = new PDO($dsn, $dbUser, $dbPass, $options);
} catch (PDOException $e) {
    // Trong môi trường thật: ghi log lỗi vào file, KHÔNG hiển thị chi tiết lỗi cho người dùng
    error_log('Loi ket noi CSDL: ' . $e->getMessage());
    die('Hệ thống đang gặp sự cố kết nối. Vui lòng thử lại sau.');
}