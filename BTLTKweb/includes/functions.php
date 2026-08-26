<?php
/**
 * Các hàm tiện ích, tính toán điểm, xếp loại, mã hóa XSS và kiểm tra dữ liệu phía Server.
 * File: D:\BTLTKweb\includes\functions.php
 */

/**
 * XSS Cleaning - Mã hóa dữ liệu trước khi xuất ra HTML để chống tấn công XSS.
 */
function e($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Chuẩn hóa dữ liệu đầu vào (Input Normalization)
 */
function normalizeInput($data) {
    if (is_array($data)) {
        return array_map('normalizeInput', $data);
    }
    $str = trim((string)$data);
    return preg_replace('/\s+/', ' ', $str);
}

/**
 * Tính điểm tổng kết hệ 10: Chuyên cần (10%), Giữa kỳ (30%), Cuối kỳ (60%)
 */
function tinhTongKet($diemCC, $diemGK, $diemCK) {
    $cc = floatval($diemCC);
    $gk = floatval($diemGK);
    $ck = floatval($diemCK);
    return round(($cc * 0.10) + ($gk * 0.30) + ($ck * 0.60), 1);
}

/**
 * Xếp loại học tập theo Điểm tổng kết
 */
function xepLoaiDiem($tongKet) {
    $val = floatval($tongKet);
    if ($val >= 9.0) return 'Xuất sắc';
    if ($val >= 8.0) return 'Giỏi';
    if ($val >= 7.0) return 'Khá';
    if ($val >= 5.0) return 'Trung bình';
    if ($val >= 3.5) return 'Yếu';
    return 'Kém';
}

/**
 * Trả về class HTML Badge tương ứng với từng loại xếp loại theo giao diện đính kèm
 */
function renderBadgeXepLoai($xepLoai) {
    switch ($xepLoai) {
        case 'Xuất sắc':
            return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-300">Xuất sắc</span>';
        case 'Giỏi':
            return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-700 border border-green-300">Giỏi</span>';
        case 'Khá':
            return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 border border-amber-300">Khá</span>';
        case 'Trung bình':
            return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-300">Trung bình</span>';
        case 'Yếu':
            return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-100 text-rose-600 border border-rose-200">Yếu</span>';
        case 'Kém':
            return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-200 text-red-800 border border-red-400">Kém</span>';
        default:
            return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-700">N/A</span>';
    }
}

/**
 * Kiểm tra & Chuẩn hóa dữ liệu điểm nhập vào từ Form phía Server (Server-Side Validation)
 * Validate MSSV: Bắt buộc chỉ từ 1 đến 10 chữ số (0-9), không ký tự khác.
 */
function validateDiemForm($data) {
    $raw = normalizeInput($data);
    $errors = [];
    $clean = [];

    // 1. Kiểm tra MSSV (Chỉ gồm số từ 1 đến 10 chữ số)
    $mssv = $raw['mssv'] ?? '';
    if ($mssv === '') {
        $errors['mssv'] = 'Vui lòng nhập Mã số sinh viên (MSSV).';
    } elseif (!preg_match('/^[0-9]{1,10}$/', $mssv)) {
        $errors['mssv'] = 'LỖI VALIDATE: MSSV chỉ được phép nhập chữ số (0-9) từ 1 đến 10 chữ số, không chứa ký tự khác!';
    } else {
        $clean['mssv'] = $mssv;
    }

    // 2. Kiểm tra Họ và tên
    $hoTen = $raw['ho_ten'] ?? '';
    if ($hoTen === '') {
        $errors['ho_ten'] = 'Vui lòng nhập Họ và tên học viên.';
    } elseif (mb_strlen($hoTen, 'UTF-8') < 2 || mb_strlen($hoTen, 'UTF-8') > 100) {
        $errors['ho_ten'] = 'Họ và tên phải có độ dài từ 2 đến 100 ký tự.';
    } else {
        $clean['ho_ten'] = $hoTen;
    }

    // 3. Kiểm tra các điểm thành phần (Chuyên cần, Giữa kỳ, Cuối kỳ)
    $fields = [
        'diem_cc' => 'Điểm chuyên cần (10%)',
        'diem_gk' => 'Điểm giữa kỳ (30%)',
        'diem_ck' => 'Điểm cuối kỳ (60%)'
    ];

    foreach ($fields as $key => $label) {
        $val = $raw[$key] ?? '';
        if ($val === '') {
            $errors[$key] = "$label không được để trống.";
        } elseif (!is_numeric($val)) {
            $errors[$key] = "$label phải là một số thực hợp lệ (ví dụ: 8.5).";
        } else {
            $num = floatval($val);
            if ($num < 0.0 || $num > 10.0) {
                $errors[$key] = "$label phải nằm trong khoảng từ 0.0 đến 10.0.";
            } else {
                $clean[$key] = round($num, 1);
            }
        }
    }

    return [
        'is_valid' => empty($errors),
        'errors' => $errors,
        'clean' => $clean
    ];
}
