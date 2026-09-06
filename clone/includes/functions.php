<?php
/**
 * Các hàm tiện ích, tính toán điểm, xếp loại, mã hóa XSS và kiểm tra dữ liệu phía Server.
 * File: includes/functions.php
 */

/**
 * XSS Cleaning - Mã hóa dữ liệu trước khi xuất ra HTML để chống tấn công XSS.
 */
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
    }
}

/**
 * Chuẩn hóa dữ liệu đầu vào (Input Normalization)
 */
if (!function_exists('normalizeInput')) {
    function normalizeInput($data) {
        if (is_array($data)) {
            return array_map('normalizeInput', $data);
        }
        $str = trim((string)$data);
        return preg_replace('/\s+/', ' ', $str);
    }
}

/**
 * Tính điểm tổng kết hệ 10: Chuyên cần (10%), Giữa kỳ (30%), Cuối kỳ (60%)
 */
if (!function_exists('tinhTongKet')) {
    function tinhTongKet($diemCC, $diemGK, $diemCK) {
        $cc = floatval($diemCC);
        $gk = floatval($diemGK);
        $ck = floatval($diemCK);
        return round(($cc * 0.10) + ($gk * 0.30) + ($ck * 0.60), 1);
    }
}

/**
 * Xếp loại học tập theo Điểm tổng kết
 */
if (!function_exists('xepLoaiDiem')) {
    function xepLoaiDiem($tongKet) {
        $val = floatval($tongKet);
        if ($val >= 9.0) return 'Xuất sắc';
        if ($val >= 8.0) return 'Giỏi';
        if ($val >= 7.0) return 'Khá';
        if ($val >= 5.0) return 'Trung bình';
        if ($val >= 3.5) return 'Yếu';
        return 'Kém';
    }
}

/**
 * Trả về class HTML Badge tương ứng với từng loại xếp loại
 */
if (!function_exists('renderBadgeXepLoai')) {
    function renderBadgeXepLoai($xepLoai) {
        switch ($xepLoai) {
            case 'Xuất sắc':
                return '<span class="badge-status active">Xuất sắc</span>';
            case 'Giỏi':
                return '<span class="badge-status active">Giỏi</span>';
            case 'Khá':
                return '<span class="badge-status pending">Khá</span>';
            case 'Trung bình':
                return '<span class="badge-status pending">Trung bình</span>';
            case 'Yếu':
                return '<span class="badge-status closed">Yếu</span>';
            case 'Kém':
                return '<span class="badge-status closed">Kém</span>';
            default:
                return '<span class="badge-status">-</span>';
        }
    }
}

/**
 * Kiểm tra & Chuẩn hóa dữ liệu điểm nhập vào từ Form phía Server
 */
if (!function_exists('validateDiemForm')) {
    function validateDiemForm($data) {
        $raw = normalizeInput($data);
        $errors = [];
        $clean = [];

        // 1. Kiểm tra MSSV (Mã SV)
        $mssv = $raw['mssv'] ?? $raw['ma_sv'] ?? '';
        if ($mssv === '') {
            $errors['mssv'] = 'Vui lòng nhập Mã số sinh viên (MSV).';
        } else {
            $clean['mssv'] = $mssv;
            $clean['ma_sv'] = $mssv;
        }

        // 2. Kiểm tra Họ và tên
        $hoTen = $raw['ho_ten'] ?? '';
        if ($hoTen === '') {
            $errors['ho_ten'] = 'Vui lòng nhập Họ và tên sinh viên.';
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
}
