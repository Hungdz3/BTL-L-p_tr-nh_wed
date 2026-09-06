<?php
/**
 * Trang Thông Báo Giảng Viên - HNDA Portal System
 */
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/db.php';

$maGV = $_SESSION['ma_gv'] ?? 'GV001';
$hoTenGV = $_SESSION['ho_ten'] ?? 'TS. Nguyễn Minh Châu';

$db = getDB();
$thongBaoList = [];

try {
    $stmt = $db->query("
        SELECT id, tieu_de, noi_dung, ngay_dang, doi_tuong_nhan, file_dinh_kem
        FROM thong_bao
        WHERE doi_tuong_nhan IN ('TAT_CA', 'GIANG_VIEN') AND trang_thai = 'DA_GUI'
        ORDER BY ngay_dang DESC, id DESC
        LIMIT 50
    ");
    $thongBaoList = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // Fallback
}
?>

<div style="max-width: 1240px; margin: 24px auto; padding: 0 20px;">

    <!-- Header Banner -->
    <div style="background: #1E3A8A; color: white; padding: 24px 32px; border-radius: 14px; margin-bottom: 24px; display: flex; align-items: center; gap: 20px; box-shadow: 0 4px 16px rgba(30, 58, 138, 0.15);">
        <div style="background: rgba(255, 255, 255, 0.18); width: 56px; height: 56px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 26px; border: 2px solid rgba(255, 255, 255, 0.3);">
            📢
        </div>
        <div>
            <h2 style="margin: 0; font-size: 20px; font-weight: 700; color: #ffffff;">Thông Báo Dành Cho Giảng Viên</h2>
            <p style="margin: 4px 0 0 0; font-size: 13px; color: #93c5fd;">Cập nhật các thông báo công tác, kế hoạch đào tạo và lịch thi từ Nhà trường.</p>
        </div>
    </div>

    <!-- Danh sách Thông Báo -->
    <div style="background: white; border-radius: 12px; padding: 24px; border: 1px solid #e2e8f0; box-shadow: 0 2px 8px rgba(0,0,0,0.04);">
        <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #f1f5f9; padding-bottom: 14px; margin-bottom: 16px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="font-size: 18px; color: #1E3A8A;">📋</span>
                <h3 style="margin: 0; font-size: 14px; font-weight: 800; color: #1E3A8A; text-transform: uppercase; letter-spacing: 0.3px;">DANH SÁCH THÔNG BÁO CHÍNH THỨC</h3>
            </div>
            <span style="font-size: 12px; color: #64748b;"><?= count($thongBaoList) ?> thông báo</span>
        </div>

        <div style="display: flex; flex-direction: column; gap: 14px;">
            <?php if (empty($thongBaoList)): ?>
                <div style="text-align: center; padding: 40px; color: #94a3b8;">Hiện chưa có thông báo nào.</div>
            <?php else: ?>
                <?php foreach ($thongBaoList as $tb): ?>
                    <div style="border: 1px solid #f1f5f9; background: #f8fafc; border-radius: 10px; padding: 18px; transition: all 0.2s;" onmouseover="this.style.background='#eff6ff'; this.style.borderColor='#bfdbfe';" onmouseout="this.style.background='#f8fafc'; this.style.borderColor='#f1f5f9';">
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                            <span style="background: #dbeafe; color: #1e40af; font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 4px;">
                                <?= $tb['doi_tuong_nhan'] === 'GIANG_VIEN' ? 'GIẢNG VIÊN' : 'TOÀN TRƯỜNG' ?>
                            </span>
                            <span style="font-size: 12px; color: #64748b; font-weight: 500;">📅 <?= !empty($tb['ngay_dang']) ? date('d/m/Y H:i', strtotime($tb['ngay_dang'])) : date('d/m/Y') ?></span>
                        </div>
                        <h4 style="margin: 0 0 6px 0; font-size: 15px; color: #0f172a; font-weight: 700;"><?= e($tb['tieu_de']) ?></h4>
                        <p style="margin: 0; font-size: 13px; color: #475569; line-height: 1.5;"><?= nl2br(e($tb['noi_dung'])) ?></p>
                        <?php if (!empty($tb['file_dinh_kem'])): ?>
                            <div style="margin-top: 10px;">
                                <a href="../<?= e($tb['file_dinh_kem']) ?>" download target="_blank" style="font-size: 12px; color: #2563eb; font-weight: 600; text-decoration: none;">📎 Đính kèm: <?= e(basename($tb['file_dinh_kem'])) ?></a>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
