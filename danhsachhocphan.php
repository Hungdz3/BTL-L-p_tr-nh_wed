<?php
declare(strict_types=1);
session_start();

if (!isset($_SESSION["courseList"])) {
    $_SESSION["courseList"] = [];
}

$courseList = &$_SESSION["courseList"];
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = trim($_POST["name"] ?? "");
    $credits = (int)($_POST["credits"] ?? 0);
    $teacher = trim($_POST["teacher"] ?? "");
    $day = $_POST["day"] ?? "";
    $start = (int)($_POST["start"] ?? 0);
    $end = (int)($_POST["end"] ?? 0);
    $capacity = (int)($_POST["capacity"] ?? 0);

    if (
        $name == "" ||
        $credits <= 0 ||
        $teacher == "" ||
        $day == "" ||
        $start <= 0 ||
        $end <= 0 ||
        $capacity <= 0
    ) {
        $message = "Vui lòng nhập đầy đủ thông tin!";

    } elseif ($start > $end) {
        $message = "Tiết bắt đầu không được lớn hơn tiết kết thúc!";

    } else {

        $courseList[] = [
            "name" => $name,
            "credits" => $credits,
            "teacher" => $teacher,
            "day" => $day,
            "start" => $start,
            "end" => $end,
            "capacity" => $capacity
        ];

        $message = "Thêm học phần thành công!";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Quản lý học phần</title>
</head>

<body>

<h1>QUẢN LÝ HỌC PHẦN</h1>

<h2>Nhập thông tin học phần</h2>

<?php if ($message != ""): ?>
    <p>
        <b><?= htmlspecialchars($message) ?></b>
    </p>
<?php endif; ?>

<form method="POST">

    <p>
        <label>Tên học phần:</label><br>
        <input type="text" name="name" required>
    </p>

    <p>
        <label>Số tín chỉ:</label><br>
        <input
            type="number"
            name="credits"
            min="1"
            max="6"
            required
        >
    </p>

    <p>
        <label>Giảng viên:</label><br>
        <input type="text" name="teacher" required>
    </p>

    <p>
        <label>Thứ học:</label><br>

        <select name="day" required>
            <option value="">-- Chọn thứ --</option>
            <option value="Thứ 2">Thứ 2</option>
            <option value="Thứ 3">Thứ 3</option>
            <option value="Thứ 4">Thứ 4</option>
            <option value="Thứ 5">Thứ 5</option>
            <option value="Thứ 6">Thứ 6</option>
            <option value="Thứ 7">Thứ 7</option>
        </select>
    </p>

    <p>
        <label>Tiết bắt đầu:</label><br>
        <input
            type="number"
            name="start"
            min="1"
            max="15"
            required
        >
    </p>

    <p>
        <label>Tiết kết thúc:</label><br>
        <input
            type="number"
            name="end"
            min="1"
            max="15"
            required
        >
    </p>

    <p>
        <label>Số lượng sinh viên:</label><br>
        <input
            type="number"
            name="capacity"
            min="1"
            required
        >
    </p>

    <button type="submit">
        Thêm học phần
    </button>

</form>


<?php if (count($courseList) > 0): ?>

    <h2>Danh sách học phần</h2>

    <table border="1" cellpadding="8" cellspacing="0">

        <tr>
            <th>STT</th>
            <th>Tên học phần</th>
            <th>Số tín chỉ</th>
            <th>Giảng viên</th>
            <th>Thứ học</th>
            <th>Tiết học</th>
            <th>Số lượng SV</th>
        </tr>

        <?php
        $stt = 1;

        foreach ($courseList as $course):
        ?>

            <tr>

                <td>
                    <?= $stt ?>
                </td>

                <td>
                    <?= htmlspecialchars($course["name"]) ?>
                </td>

                <td>
                    <?= $course["credits"] ?>
                </td>

                <td>
                    <?= htmlspecialchars($course["teacher"]) ?>
                </td>

                <td>
                    <?= htmlspecialchars($course["day"]) ?>
                </td>

                <td>
                    Tiết <?= $course["start"] ?>
                    -
                    <?= $course["end"] ?>
                </td>

                <td>
                    <?= $course["capacity"] ?>
                </td>

            </tr>

        <?php
            $stt++;
        endforeach;
        ?>

    </table>

<?php endif; ?>

</body>
</html>