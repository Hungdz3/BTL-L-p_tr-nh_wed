<?php
session_start();

$sinh_vien = [
    'ma_sv' => '224001899',
    'ho_ten' => 'NGUYỄN VĂN A',
    'ten_lop' => 'CNTT D2024A',
];

/*
 * Dữ liệu hiển thị được bố trí theo đúng layout của ảnh mẫu.
 * span = số tiết mà ô môn học chiếm theo chiều dọc.
 */
$lich = [
    'sang' => [
        2 => [
            ['start' => 1, 'span' => 2, 'mon' => 'Tiếng Anh 2', 'gv' => 'C. Uyên', 'tuan' => '22/12 - 26/04', 'phong' => 'CS1-A1-403', 'type' => 'red'],
        ],
        3 => [
            ['start' => 3, 'span' => 4, 'mon' => 'Lập trình ứng dụng...', 'gv' => 'T. Trung', 'tuan' => '22/12 - 26/04', 'phong' => 'CS1-A1-304', 'type' => 'blue'],
        ],
        4 => [
            ['start' => 5, 'span' => 2, 'mon' => 'Cơ Sở VHVN', 'gv' => 'C. Hồng', 'tuan' => '22/12 - 19/04', 'phong' => 'CS1-A1-103', 'type' => 'purple'],
        ],
        7 => [
            ['start' => 1, 'span' => 2, 'mon' => 'Tư Tưởng HCM (Trực tuyến)', 'gv' => 'C. Thành', 'tuan' => '22/12 - 19/04', 'phong' => '', 'type' => 'yellow'],
            ['start' => 3, 'span' => 4, 'mon' => 'Khởi nghiệp & DMST...', 'gv' => '', 'tuan' => '22/12 - 19/04', 'phong' => 'CS1-A1-304', 'type' => 'green'],
        ],
    ],
    'chieu' => [
        3 => [
            ['start' => 1, 'span' => 3, 'mon' => 'Quản trị mạng 1', 'gv' => 'T. Chung', 'tuan' => '22/12 - 19/04', 'phong' => 'CS1-A3-109', 'type' => 'cyan'],
            ['start' => 4, 'span' => 3, 'mon' => 'Lập trình hướng đối...', 'gv' => 'T. Thông', 'tuan' => '22/12 - 26/04', 'phong' => 'CS1-A3-212', 'type' => 'red'],
        ],
        4 => [
            ['start' => 1, 'span' => 3, 'mon' => 'Kỹ thuật số 1', 'gv' => 'T. Chung', 'tuan' => '22/12 - 26/04', 'phong' => 'CS1-A2-408', 'type' => 'pink'],
            ['start' => 4, 'span' => 3, 'mon' => 'Nguyên lý HĐH 1', 'gv' => 'T. Đức', 'tuan' => '22/12 - 12/04', 'phong' => 'CS1-A2-406', 'type' => 'orange'],
        ],
    ],
];

$ten_tiet = [
    1 => '6h45 - 7h35',
    2 => '7h40 - 8h30',
    3 => '8h35 - 9h25',
    4 => '9h30 - 10h20',
    5 => '10h25 - 11h15',
    6 => '11h20 - 12h10',
    7 => '12h45 - 13h35',
    8 => '13h40 - 14h30',
    9 => '14h35 - 15h25',
    10 => '15h30 - 16h20',
    11 => '16h25 - 17h15',
    12 => '17h20 - 18h10',
];

$days = [
    2 => 'THỨ 2',
    3 => 'THỨ 3',
    4 => 'THỨ 4',
    5 => 'THỨ 5',
    6 => 'THỨ 6',
    7 => 'THỨ 7',
    8 => 'CHỦ NHẬT',
];

function e($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Thời khóa biểu cá nhân</title>

<style>
    :root{
        --navy:#213b8f;
        --navy-dark:#172b70;
        --text:#111b31;
        --muted:#8da0bd;
        --line:#e2e8f1;
        --page:#f7f9fc;
    }

    *{box-sizing:border-box}

    html,body{margin:0;padding:0}

    body{
        background:var(--page);
        color:var(--text);
        font-family:Inter,"Segoe UI",Arial,sans-serif;
        -webkit-font-smoothing:antialiased;
    }

    /* ===== TOP HEADER ===== */
    .topbar{
        height:96px;
        background:#fff;
        border-bottom:1px solid #e7ebf2;
        display:flex;
        align-items:center;
        justify-content:space-between;
        padding:0 52px;
    }

    .brand{
        display:flex;
        align-items:center;
        gap:14px;
    }

    .brand-mark{
        width:46px;
        height:46px;
        border-radius:7px;
        background:var(--navy);
        color:#fff;
        display:flex;
        align-items:center;
        justify-content:center;
        font-size:25px;
        font-weight:800;
    }

    .brand-title{
        color:#183982;
        font-size:20px;
        font-weight:800;
        line-height:1.05;
        letter-spacing:-.2px;
    }

    .brand-sub{
        color:#f03e3e;
        font-size:12px;
        font-weight:600;
        margin-top:5px;
        letter-spacing:.15px;
    }

    .student{
        display:flex;
        align-items:center;
        gap:16px;
    }

    .student-text{
        text-align:right;
        line-height:1.35;
    }

    .student-name{
        font-size:16px;
        font-weight:800;
        color:#111827;
    }

    .student-meta{
        font-size:12px;
        color:#667085;
        margin-top:4px;
    }

    .avatar{
        width:50px;
        height:50px;
        border-radius:50%;
        background:#c9c9c9;
    }

    /* ===== NAV ===== */
    .navbar{
        height:60px;
        background:#243776;
        display:flex;
        align-items:center;
        padding:0 54px;
        gap:43px;
    }

    .navbar a{
        color:#dce4fb;
        text-decoration:none;
        font-size:14px;
        font-weight:500;
        white-space:nowrap;
    }

    .navbar a.active{
        color:#fff;
        font-weight:800;
    }

    /* ===== MAIN ===== */
    .page{
        width:calc(100% - 84px);
        max-width:1840px;
        margin:0 auto;
        padding:42px 0 34px;
    }

    .title-row{
        display:flex;
        align-items:center;
        justify-content:space-between;
        margin:0 0 20px;
    }

    .page-title{
        margin:0;
        font-size:34px;
        line-height:1;
        font-weight:850;
        letter-spacing:-1.2px;
        color:#121b2e;
    }

    .actions{
        display:flex;
        gap:14px;
    }

    .btn{
        height:50px;
        border-radius:9px;
        padding:0 19px;
        border:1px solid #dce3ed;
        background:#fff;
        color:#4a5568;
        font-size:15px;
        font-weight:700;
        display:flex;
        align-items:center;
        gap:10px;
        cursor:pointer;
    }

    .btn.primary{
        background:var(--navy);
        border-color:var(--navy);
        color:#fff;
    }

    .btn:hover{filter:brightness(.98)}

    /* ===== FILTER ===== */
    .filter-panel{
        background:#fff;
        border:1px solid #e2e7ef;
        border-radius:12px;
        min-height:154px;
        padding:27px 58px 22px;
        display:grid;
        grid-template-columns:1fr 1fr 1fr 170px;
        gap:22px;
        align-items:end;
        box-shadow:0 1px 2px rgba(16,24,40,.02);
        margin-bottom:25px;
    }

    .filter-label{
        display:block;
        font-size:18px;
        font-weight:500;
        margin:0 0 12px;
        color:#111827;
    }

    .select-box{
        height:58px;
        border:1px solid #dfe4ec;
        border-radius:9px;
        padding:0 20px;
        display:flex;
        align-items:center;
        justify-content:space-between;
        font-size:18px;
        color:#161b26;
        background:#fff;
    }

    .chevron{
        font-size:22px;
        color:#101828;
        line-height:1;
        transform:translateY(-2px);
    }

    .reset{
        height:58px;
        border:0;
        border-radius:10px;
        background:var(--navy);
        color:#fff;
        font-size:18px;
        font-weight:800;
        cursor:pointer;
    }

    /* ===== SCHEDULE ===== */
    .schedule{
        background:#fff;
        border:1px solid #dfe6ef;
        border-radius:16px;
        padding:20px 20px 22px;
    }

    .day-header,
    .schedule-row{
        display:grid;
        grid-template-columns:178px repeat(7,minmax(0,1fr));
        column-gap:9px;
    }

    .day-header{
        margin-bottom:20px;
    }

    .day-header > div{
        height:66px;
        border-radius:10px;
        background:var(--navy);
        color:#fff;
        display:flex;
        align-items:center;
        justify-content:center;
        font-size:16px;
        font-weight:800;
    }

    .day-header > div:last-child{
        background:#ef4444;
    }

    .section{
        height:44px;
        border-radius:8px;
        display:flex;
        align-items:center;
        padding:0 18px;
        margin-bottom:10px;
        font-size:15px;
        font-weight:800;
        color:#21408f;
    }

    .section.morning{background:#edf5ff}
    .section.afternoon{
        background:#effbf5;
        color:#09a76a;
        margin-top:18px;
    }

    .section .moon{
        font-size:18px;
        margin-right:10px;
    }

    .schedule-body{
        display:grid;
        grid-template-columns:178px repeat(7,minmax(0,1fr));
        column-gap:9px;
    }

    .time-column{
        display:grid;
        grid-template-rows:repeat(6,1fr);
        gap:8px;
    }

    .time-cell{
        min-height:101px;
        border:1px solid #dce4ee;
        border-radius:9px;
        display:flex;
        flex-direction:column;
        align-items:center;
        justify-content:center;
        background:#fff;
    }

    .time-cell strong{
        font-size:15px;
        color:#173b91;
        font-weight:800;
        margin-bottom:7px;
    }

    .time-cell span{
        color:#91a5c1;
        font-size:12px;
        white-space:nowrap;
    }

    .day-column{
        position:relative;
        display:grid;
        grid-template-rows:repeat(6,101px);
        gap:8px;
        min-width:0;
    }

    .slot{
        border:1px solid #dce4ee;
        border-radius:9px;
        background:#fff;
        min-width:0;
    }

    .course{
        position:absolute;
        left:0;
        right:0;
        z-index:2;
        border:1px solid transparent;
        border-radius:9px;
        padding:20px 16px;
        overflow:hidden;
        display:flex;
        flex-direction:column;
        justify-content:center;
        line-height:1.25;
    }

    .course .name{
        font-size:15px;
        font-weight:850;
        white-space:nowrap;
        overflow:hidden;
        text-overflow:ellipsis;
        margin-bottom:7px;
    }

    .course .teacher,
    .course .week,
    .course .room{
        font-size:12px;
        margin-top:2px;
    }

    .course .room{
        font-weight:800;
        margin-top:7px;
    }

    .red{
        background:#ffe1e1;
        border-color:#ffcaca;
        color:#b42318;
    }

    .blue{
        background:#d8e8ff;
        border-color:#c4dcff;
        color:#1f54b5;
    }

    .purple{
        background:#f0ddff;
        border-color:#e5c9fb;
        color:#7430b0;
    }

    .yellow{
        background:#fff2c7;
        border-color:#ffdc73;
        color:#8a5b00;
    }

    .green{
        background:#c9f7df;
        border-color:#a8eeca;
        color:#08704c;
    }

    .cyan{
        background:#d8fbfd;
        border-color:#bceef2;
        color:#0e6876;
    }

    .pink{
        background:#fbdcf0;
        border-color:#f4c5df;
        color:#b52b69;
    }

    .orange{
        background:#ffead0;
        border-color:#ffd2a0;
        color:#a94709;
    }

    .afternoon-body .time-cell,
    .afternoon-body .slot{
        min-height:101px;
    }

    .legend{display:none}

    /* ===== PRINT ===== */
    @media print{
        .topbar,.navbar,.actions,.filter-panel{display:none!important}
        .page{width:100%;max-width:none;padding:0}
        .schedule{border:0}
    }

    /* ===== RESPONSIVE ===== */
    @media (max-width:1100px){
        .topbar{padding:0 24px}
        .navbar{padding:0 24px;gap:22px;overflow:auto}
        .page{width:calc(100% - 28px);padding-top:25px}
        .filter-panel{
            padding:20px;
            grid-template-columns:repeat(2,1fr);
        }
        .reset{height:54px}
        .schedule{overflow-x:auto}
        .day-header,.schedule-body{
            min-width:1350px;
        }
    }

    @media (max-width:700px){
        .topbar{height:auto;padding:14px 16px}
        .student-text{display:none}
        .navbar{height:48px;padding:0 16px;gap:18px}
        .navbar a{font-size:11px}
        .title-row{align-items:flex-start;gap:15px;flex-direction:column}
        .page-title{font-size:26px}
        .actions{width:100%}
        .btn{flex:1;justify-content:center;font-size:12px}
        .filter-panel{grid-template-columns:1fr}
    }
</style>
</head>

<body>
<header class="topbar">
    <div class="brand">
        <div class="brand-mark">A</div>
        <div>
            <div class="brand-title">TRƯỜNG ĐẠI HỌC HNDA</div>
            <div class="brand-sub">CỔNG THÔNG TIN SINH VIÊN</div>
        </div>
    </div>

    <div class="student">
        <div class="student-text">
            <div class="student-name"><?= e($sinh_vien['ho_ten']) ?></div>
            <div class="student-meta">MSV: <?= e($sinh_vien['ma_sv']) ?> · Lớp: <?= e($sinh_vien['ten_lop']) ?></div>
        </div>
        <div class="avatar"></div>
    </div>
</header>

<nav class="navbar">
    <a href="#">TRANG CHỦ</a>
    <a href="#">THÔNG BÁO</a>
    <a href="#">DANH SÁCH HỌC PHẦN</a>
    <a href="#">ĐĂNG KÝ HỌC PHẦN</a>
    <a class="active" href="#">LỊCH HỌC</a>
</nav>

<main class="page">
    <div class="title-row">
        <h1 class="page-title">THỜI KHÓA BIỂU CÁ NHÂN</h1>

        <div class="actions">
            <button class="btn" onclick="window.print()">▣&nbsp; In thời khóa biểu</button>
            <button class="btn primary">▣&nbsp; Xuất sang Google Calendar</button>
        </div>
    </div>

    <section class="filter-panel">
        <div>
            <label class="filter-label">Học kỳ</label>
            <div class="select-box">
                <span>Học kỳ 1 (2025 - 2026)</span>
                <span class="chevron">⌄</span>
            </div>
        </div>

        <div>
            <label class="filter-label">Ngày bắt đầu</label>
            <div class="select-box">
                <span>DD/MM/YY</span>
                <span class="chevron">⌄</span>
            </div>
        </div>

        <div>
            <label class="filter-label">Ngày kết thúc</label>
            <div class="select-box">
                <span>DD/MM/YY</span>
                <span class="chevron">⌄</span>
            </div>
        </div>

        <button class="reset" onclick="location.reload()">Đặt lại bộ lọc</button>
    </section>

    <section class="schedule">
        <div class="day-header">
            <div>TIẾT</div>
            <?php foreach ($days as $day): ?>
                <div><?= e($day) ?></div>
            <?php endforeach; ?>
        </div>

        <div class="section morning"><span class="moon">☼</span> BUỔI SÁNG</div>

        <div class="schedule-body">
            <div class="time-column">
                <?php for ($t = 1; $t <= 6; $t++): ?>
                    <div class="time-cell">
                        <strong>Tiết <?= $t ?></strong>
                        <span><?= e($ten_tiet[$t]) ?></span>
                    </div>
                <?php endfor; ?>
            </div>

            <?php for ($day = 2; $day <= 8; $day++): ?>
                <div class="day-column">
                    <?php for ($i = 1; $i <= 6; $i++): ?>
                        <div class="slot"></div>
                    <?php endfor; ?>

                    <?php foreach (($lich['sang'][$day] ?? []) as $course): ?>
                        <div class="course <?= e($course['type']) ?>"
                             style="top:calc((<?= $course['start'] - 1 ?>) * 101px + (<?= $course['start'] - 1 ?>) * 8px);
                                    height:calc(<?= $course['span'] ?> * 101px + <?= $course['span'] - 1 ?> * 8px);">
                            <div class="name"><?= e($course['mon']) ?></div>
                            <?php if ($course['gv'] !== ''): ?><div class="teacher"><?= e($course['gv']) ?></div><?php endif; ?>
                            <div class="week">(<?= e($course['tuan']) ?>)</div>
                            <?php if ($course['phong'] !== ''): ?><div class="room"><?= e($course['phong']) ?></div><?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endfor; ?>
        </div>

        <div class="section afternoon"><span class="moon">◔</span> BUỔI CHIỀU</div>

        <div class="schedule-body afternoon-body">
            <div class="time-column">
                <?php for ($t = 7; $t <= 12; $t++): ?>
                    <div class="time-cell">
                        <strong>Tiết <?= $t ?></strong>
                        <span><?= e($ten_tiet[$t]) ?></span>
                    </div>
                <?php endfor; ?>
            </div>

            <?php for ($day = 2; $day <= 8; $day++): ?>
                <div class="day-column">
                    <?php for ($i = 1; $i <= 6; $i++): ?>
                        <div class="slot"></div>
                    <?php endfor; ?>

                    <?php foreach (($lich['chieu'][$day] ?? []) as $course): ?>
                        <div class="course <?= e($course['type']) ?>"
                             style="top:calc((<?= $course['start'] - 1 ?>) * 101px + (<?= $course['start'] - 1 ?>) * 8px);
                                    height:calc(<?= $course['span'] ?> * 101px + <?= $course['span'] - 1 ?> * 8px);">
                            <div class="name"><?= e($course['mon']) ?></div>
                            <?php if ($course['gv'] !== ''): ?><div class="teacher"><?= e($course['gv']) ?></div><?php endif; ?>
                            <div class="week">(<?= e($course['tuan']) ?>)</div>
                            <?php if ($course['phong'] !== ''): ?><div class="room"><?= e($course['phong']) ?></div><?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endfor; ?>
        </div>
    </section>
</main>
</body>
</html>
