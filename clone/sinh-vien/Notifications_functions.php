<?php

function getNotificationStatus($title, $description)
{
    if (empty($title) || empty($description)) {
        return "Thiếu thông tin";
    }

    if (mb_strlen($description, 'UTF-8') >= 100) {
        return "Thông báo chi tiết";
    }

    return "Thông báo ngắn";
}

function getPublishedNotifications(PDO $db, ?int $limit = null, string $targetRole = 'SINH_VIEN'): array
{
    $sql = "
        SELECT id, tieu_de AS title, noi_dung AS description,
               file_dinh_kem, ngay_dang, doi_tuong_nhan
        FROM thong_bao
        WHERE trang_thai = :trang_thai
          AND doi_tuong_nhan IN ('TAT_CA', :target_role)
        ORDER BY ngay_dang DESC, id DESC
    ";
    if ($limit !== null) {
        $sql .= ' LIMIT ' . max(1, $limit);
    }

    $stmt = $db->prepare($sql);
    $stmt->execute([
        ':trang_thai' => 'DA_GUI',
        ':target_role' => $targetRole,
    ]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getNotificationImage(array $notification): string
{
    $file = $notification['file_dinh_kem'] ?? '';
    if ($file !== '' && preg_match('/\.(jpe?g|png|gif|webp|svg)$/i', $file)) {
        return $file;
    }

    return 'assets/illustration.png';
}
