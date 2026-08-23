<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/functions.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Lớp Học - Cổng Thông Tin Giảng Viên - ĐH HNDA</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- FontAwesome Icons CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f3f4f6;
        }
        .bg-hnda-dark {
            background-color: #1a365d;
        }
        .bg-hnda-blue {
            background-color: #1e40af;
        }
        .bg-banner-blue {
            background-color: #1e3a8a;
        }
    </style>
</head>
<body class="text-gray-800 flex flex-col min-h-screen">

    <!-- Top Branding Header -->
    <header class="bg-white border-b border-gray-200 py-3 px-6 shadow-sm">
        <div class="max-w-7xl mx-auto flex items-center justify-between">
            <!-- Left: Logo & University Title -->
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-indigo-900 text-white rounded-lg flex items-center justify-center font-extrabold text-xl shadow">
                    A
                </div>
                <div>
                    <h1 class="text-sm font-bold tracking-wide text-indigo-950 uppercase leading-tight">Trường Đại Học HNDA</h1>
                    <p class="text-xs font-semibold text-red-600 uppercase tracking-wider">Cổng Thông Tin Giảng Viên</p>
                </div>
            </div>

            <!-- Right: Lecturer Info Profile -->
            <div class="flex items-center space-x-3">
                <div class="text-right hidden sm:block">
                    <p class="text-sm font-bold text-gray-900">TS. Nguyễn Minh Châu</p>
                    <p class="text-xs text-gray-500">Mã GV: GV001 • Khoa CNTT</p>
                </div>
                <div class="w-10 h-10 bg-gray-300 rounded-full flex items-center justify-center text-gray-600 border border-gray-300">
                    <i class="fa-solid fa-user text-lg"></i>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Navigation Bar -->
    <nav class="bg-indigo-900 text-white shadow-md">
        <div class="max-w-7xl mx-auto flex items-center px-6 text-sm font-medium overflow-x-auto">
            <a href="#" class="py-3 px-4 hover:bg-indigo-800 transition">TRANG CHỦ</a>
            <a href="#" class="py-3 px-4 hover:bg-indigo-800 transition">THÔNG BÁO</a>
            <a href="#" class="py-3 px-4 hover:bg-indigo-800 transition">LỊCH GIẢNG DẠY</a>
            <a href="index.php" class="py-3 px-4 bg-indigo-800 border-b-4 border-white font-bold transition">QUẢN LÝ LỚP HỌC</a>
        </div>
    </nav>

    <!-- Main Content Container -->
    <main class="flex-grow max-w-7xl w-full mx-auto p-4 sm:p-6 space-y-5">
