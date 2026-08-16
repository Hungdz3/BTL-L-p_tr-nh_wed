<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'schedule_management');
define('DB_USER', 'root');
define('DB_PASS', 'vertrigo'); 


define('APP_NAME', 'Quản lý Lịch học');
define('APP_VERSION', '1.0.0');
define('TIMEZONE', 'Asia/Ho_Chi_Minh');


date_default_timezone_set(TIMEZONE);


error_reporting(E_ALL);
ini_set('display_errors', 1);

ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Chỉ bật khi có HTTPS
?>