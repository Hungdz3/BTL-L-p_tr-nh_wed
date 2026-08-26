-- public.diem definition

-- Drop table

-- DROP TABLE public.diem;

CREATE TABLE public.diem (
	id uuid DEFAULT uuid_generate_v4() NOT NULL,
	dang_ky_id uuid NOT NULL,
	diem_chuyen_can numeric(4, 2) NULL,
	diem_giua_ky numeric(4, 2) NULL,
	diem_cuoi_ky numeric(4, 2) NULL,
	diem_tong_ket numeric(4, 2) NULL,
	nguoi_nhap varchar(30) NULL,
	created_at timestamptz DEFAULT now() NOT NULL,
	updated_at timestamptz DEFAULT now() NOT NULL,
	CONSTRAINT chk_diem_chuyen_can CHECK (((diem_chuyen_can IS NULL) OR ((diem_chuyen_can >= (0)::numeric) AND (diem_chuyen_can <= (10)::numeric)))),
	CONSTRAINT chk_diem_cuoi_ky CHECK (((diem_cuoi_ky IS NULL) OR ((diem_cuoi_ky >= (0)::numeric) AND (diem_cuoi_ky <= (10)::numeric)))),
	CONSTRAINT chk_diem_giua_ky CHECK (((diem_giua_ky IS NULL) OR ((diem_giua_ky >= (0)::numeric) AND (diem_giua_ky <= (10)::numeric)))),
	CONSTRAINT chk_diem_tong_ket CHECK (((diem_tong_ket IS NULL) OR ((diem_tong_ket >= (0)::numeric) AND (diem_tong_ket <= (10)::numeric)))),
	CONSTRAINT diem_created_at_not_null NOT NULL created_at,
	CONSTRAINT diem_dang_ky_id_key UNIQUE (dang_ky_id),
	CONSTRAINT diem_dang_ky_id_not_null NOT NULL dang_ky_id,
	CONSTRAINT diem_id_not_null NOT NULL id,
	CONSTRAINT diem_pkey PRIMARY KEY (id),
	CONSTRAINT diem_updated_at_not_null NOT NULL updated_at
);


-- public.diem foreign keys

ALTER TABLE public.diem ADD CONSTRAINT fk_diem_dang_ky FOREIGN KEY (dang_ky_id) REFERENCES public.dang_ky_hoc_phan(id) ON DELETE CASCADE;
ALTER TABLE public.diem ADD CONSTRAINT fk_diem_nguoi_nhap FOREIGN KEY (nguoi_nhap) REFERENCES public.giao_vien(ma_gv) ON DELETE SET NULL;