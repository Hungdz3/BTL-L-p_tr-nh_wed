-- config/update_schema.sql

-- 1. Bổ sung tệp đính kèm cho bảng thong_bao
ALTER TABLE thong_bao ADD COLUMN IF NOT EXISTS file_dinh_kem VARCHAR(255) NULL;

-- 2. Tạo bảng đợt đăng ký dot_dang_ky
CREATE TABLE IF NOT EXISTS dot_dang_ky (
    ma_dot_dk VARCHAR(50) PRIMARY KEY,
    ten_dot_dk VARCHAR(255) NOT NULL,
    id_hoc_ky UUID NOT NULL REFERENCES hoc_ky(id_hoc_ky) ON DELETE CASCADE,
    ngay_bat_dau TIMESTAMP WITH TIME ZONE NOT NULL,
    ngay_ket_thuc TIMESTAMP WITH TIME ZONE NOT NULL,
    so_tin_chi_toi_da INT NOT NULL DEFAULT 24,
    trang_thai VARCHAR(50) NOT NULL DEFAULT 'CHO_DUYET', -- 'HOAT_DONG', 'CHO_DUYET', 'DA_DONG'
    ghi_chu TEXT NULL,
    created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW()
);

-- 3. Cập nhật bảng môn học mon_hoc để phân loại
ALTER TABLE mon_hoc ADD COLUMN IF NOT EXISTS loai_mon_hoc VARCHAR(50) NOT NULL DEFAULT 'MON_CHUNG';
ALTER TABLE mon_hoc ADD COLUMN IF NOT EXISTS ma_nganh VARCHAR(50) REFERENCES nganh(ma_nganh) ON DELETE SET NULL;
ALTER TABLE mon_hoc ALTER COLUMN ma_khoa DROP NOT NULL;

-- 4. Bổ sung ngày học và trạng thái duyệt cho bảng lớp học phần lop_hoc_phan
ALTER TABLE lop_hoc_phan ADD COLUMN IF NOT EXISTS ngay_bat_dau DATE NULL;
ALTER TABLE lop_hoc_phan ADD COLUMN IF NOT EXISTS ngay_ket_thuc DATE NULL;

-- 5. Cho phép mã đợt đăng ký trong dang_ky_hoc_phan (tùy chọn để liên kết đợt cụ thể)
ALTER TABLE dang_ky_hoc_phan ADD COLUMN IF NOT EXISTS ma_dot_dk VARCHAR(50) REFERENCES dot_dang_ky(ma_dot_dk) ON DELETE SET NULL;
