<?php
declare(strict_types=1);

if (!isset($_SESSION["courseList"])) {
    $_SESSION["courseList"] = [];
}

$courseList = &$_SESSION["courseList"];

$errors = [];
$message = "";


$courseCode = "";
$name = "";
$credits = "";
$teacher = "";
$day = "";
$start = "";
$end = "";
$capacity = "";


function isValidName(string $value): bool
{
    return preg_match('/^[\p{L}\s]+$/u', $value) === 1;
}


if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $courseCode = trim($_POST["courseCode"] ?? "");
    $name = trim($_POST["name"] ?? "");
    $credits = trim($_POST["credits"] ?? "");
    $teacher = trim($_POST["teacher"] ?? "");
    $day = trim($_POST["day"] ?? "");
    $start = trim($_POST["start"] ?? "");
    $end = trim($_POST["end"] ?? "");
    $capacity = trim($_POST["capacity"] ?? "");


    if ($courseCode === "") {
        $errors["courseCode"] =
            "Vui lòng nhập mã học phần.";

    } elseif (!preg_match('/^[A-Za-z0-9]+$/', $courseCode)) {
        $errors["courseCode"] =
            "Mã học phần chỉ được chứa chữ và số.";
    }


    if ($name === "") {
        $errors["name"] =
            "Vui lòng nhập tên học phần.";

    } elseif (!preg_match('/\p{L}/u', $name)) {
        $errors["name"] =
            "Tên học phần phải có ít nhất một ký tự chữ.";

    } elseif (!preg_match('/^[\p{L}\p{N}\s]+$/u', $name)) {
        $errors["name"] =
            "Tên học phần không được chứa ký tự đặc biệt.";
    }


    if ($credits === "") {
        $errors["credits"] =
            "Vui lòng nhập số tín chỉ.";

    } elseif (!ctype_digit($credits)) {
        $errors["credits"] =
            "Số tín chỉ phải là số nguyên.";

    } elseif ((int)$credits < 1 || (int)$credits >= 6) {
        $errors["credits"] =
            "Số tín chỉ phải từ 1 đến 5.";
    }


    if ($teacher === "") {
        $errors["teacher"] =
            "Vui lòng nhập tên giảng viên.";

    } elseif (!isValidName($teacher)) {
        $errors["teacher"] =
            "Tên giảng viên chỉ được chứa chữ và khoảng trắng.";
    }


    $days = [
        "Thứ 2",
        "Thứ 3",
        "Thứ 4",
        "Thứ 5",
        "Thứ 6",
        "Thứ 7"
    ];

    if ($day === "") {
        $errors["day"] =
            "Vui lòng chọn thứ học.";

    } elseif (!in_array($day, $days, true)) {
        $errors["day"] =
            "Thứ học không hợp lệ.";
    }


    if ($start === "") {
        $errors["start"] =
            "Vui lòng nhập tiết bắt đầu.";

    } elseif (!ctype_digit($start)) {
        $errors["start"] =
            "Tiết bắt đầu phải là số nguyên.";

    } elseif ((int)$start < 1 || (int)$start > 15) {
        $errors["start"] =
            "Tiết bắt đầu phải từ 1 đến 15.";
    }


    if ($end === "") {
        $errors["end"] =
            "Vui lòng nhập tiết kết thúc.";

    } elseif (!ctype_digit($end)) {
        $errors["end"] =
            "Tiết kết thúc phải là số nguyên.";

    } elseif ((int)$end < 1 || (int)$end > 15) {
        $errors["end"] =
            "Tiết kết thúc phải từ 1 đến 15.";
    }


    if (
        $start !== "" &&
        $end !== "" &&
        ctype_digit($start) &&
        ctype_digit($end)
    ) {

        if ((int)$start >= (int)$end) {
            $errors["end"] =
                "Tiết bắt đầu phải nhỏ hơn tiết kết thúc.";
        }
    }


    if ($capacity === "") {
        $errors["capacity"] =
            "Vui lòng nhập số lượng sinh viên.";

    } elseif (!ctype_digit($capacity)) {
        $errors["capacity"] =
            "Số lượng sinh viên phải là số nguyên.";

    } elseif ((int)$capacity <= 0) {
        $errors["capacity"] =
            "Số lượng sinh viên phải lớn hơn 0.";
    }



    if (isset($errors["courseCode"])) {
        $courseCode = "";
    }

    if (isset($errors["name"])) {
        $name = "";
    }

    if (isset($errors["credits"])) {
        $credits = "";
    }

    if (isset($errors["teacher"])) {
        $teacher = "";
    }

    if (isset($errors["day"])) {
        $day = "";
    }

    if (isset($errors["start"])) {
        $start = "";
    }

    if (isset($errors["end"])) {
        $end = "";
    }

    if (isset($errors["capacity"])) {
        $capacity = "";
    }


    if (empty($errors)) {

        $courseList[] = [
            "courseCode" => $courseCode,
            "name" => $name,
            "credits" => (int)$credits,
            "teacher" => $teacher,
            "day" => $day,
            "start" => (int)$start,
            "end" => (int)$end,
            "capacity" => (int)$capacity
        ];

        $message = "Thêm học phần thành công!";

        $courseCode = "";
        $name = "";
        $credits = "";
        $teacher = "";
        $day = "";
        $start = "";
        $end = "";
        $capacity = "";
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


<?php if ($message !== ""): ?>
    <p style="color: green;">
        <b>
            <?= htmlspecialchars($message) ?>
        </b>
    </p>
<?php endif; ?>


<form method="POST">

    <p>
        <label>Mã học phần:</label>
        <br>

        <input
            type="text"
            name="courseCode"
            value="<?= htmlspecialchars($courseCode) ?>"
        >

        <?php if (isset($errors["courseCode"])): ?>
            <br>
            <span style="color: red;">
                <?= htmlspecialchars($errors["courseCode"]) ?>
            </span>
        <?php endif; ?>

    </p>


    <p>

        <label>Tên học phần:</label>
        <br>
        <input
            type="text"
            name="name"
            value="<?= htmlspecialchars($name) ?>"
        >

        <?php if (isset($errors["name"])): ?>
            <br>

            <span style="color: red;">
                <?= htmlspecialchars($errors["name"]) ?>
            </span>
        <?php endif; ?>

    </p>


    <p>
        <label>Số tín chỉ:</label>
        <br>
        <input
            type="number"
            name="credits"
            value="<?= htmlspecialchars($credits) ?>"
        >

        <?php if (isset($errors["credits"])): ?>
            <br>
            <span style="color: red;">
                <?= htmlspecialchars($errors["credits"]) ?>
            </span>
        <?php endif; ?>

    </p>


    <p>

        <label>Giảng viên:</label>
        <br>
        <input
            type="text"
            name="teacher"
            value="<?= htmlspecialchars($teacher) ?>"
        >

        <?php if (isset($errors["teacher"])): ?>
            <br>
            <span style="color: red;">
                <?= htmlspecialchars($errors["teacher"]) ?>
            </span>
        <?php endif; ?>

    </p>


    <p>

        <label>Thứ học:</label>
        <br>
        <select name="day">
            <option value="">
                -- Chọn thứ --
            </option>

            <option
                value="Thứ 2"
                <?= $day === "Thứ 2" ? "selected" : "" ?>
            >
                Thứ 2
            </option>

            <option
                value="Thứ 3"
                <?= $day === "Thứ 3" ? "selected" : "" ?>
            >
                Thứ 3
            </option>

            <option
                value="Thứ 4"
                <?= $day === "Thứ 4" ? "selected" : "" ?>
            >
                Thứ 4
            </option>

            <option
                value="Thứ 5"
                <?= $day === "Thứ 5" ? "selected" : "" ?>
            >
                Thứ 5
            </option>

            <option
                value="Thứ 6"
                <?= $day === "Thứ 6" ? "selected" : "" ?>
            >
                Thứ 6
            </option>

            <option
                value="Thứ 7"
                <?= $day === "Thứ 7" ? "selected" : "" ?>
            >
                Thứ 7
            </option>

        </select>

        <?php if (isset($errors["day"])): ?>
            <br>
            <span style="color: red;">
                <?= htmlspecialchars($errors["day"]) ?>
            </span>
        <?php endif; ?>
    </p>


    <p>

        <label>Tiết bắt đầu:</label>
        <br>
        <input
            type="number"
            name="start"
            value="<?= htmlspecialchars($start) ?>"
        >

        <?php if (isset($errors["start"])): ?>
            <br>
            <span style="color: red;">
                <?= htmlspecialchars($errors["start"]) ?>
            </span>
        <?php endif; ?>

    </p>


    <p>

        <label>Tiết kết thúc:</label>
        <br>
        <input
            type="number"
            name="end"
            value="<?= htmlspecialchars($end) ?>"
        >

        <?php if (isset($errors["end"])): ?>
            <br>
            <span style="color: red;">
                <?= htmlspecialchars($errors["end"]) ?>
            </span>
        <?php endif; ?>

    </p>


    <p>

        <label>Số lượng sinh viên:</label>
        <br>
        <input
            type="number"
            name="capacity"
            value="<?= htmlspecialchars($capacity) ?>"
        >

        <?php if (isset($errors["capacity"])): ?>
            <br>
            <span style="color: red;">
                <?= htmlspecialchars($errors["capacity"]) ?>
            </span>
        <?php endif; ?>
    </p>


    <button type="submit">
        Thêm học phần
    </button>

</form>



<?php if (count($courseList) > 0): ?>
    <h2>Danh sách học phần</h2>
    <table border="1" cellpadding="8">

        <tr>
            <th>STT</th>
            <th>Mã học phần</th>
            <th>Tên học phần</th>
            <th>Tín chỉ</th>
            <th>Giảng viên</th>
            <th>Thứ</th>
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
                    <?= htmlspecialchars(
                        $course["courseCode"] ?? ""
                    ) ?>
                </td>

                <td>
                    <?= htmlspecialchars(
                        $course["name"] ?? ""
                    ) ?>
                </td>

                <td>
                    <?= htmlspecialchars(
                        (string)($course["credits"] ?? "")
                    ) ?>
                </td>

                <td>
                    <?= htmlspecialchars(
                        $course["teacher"] ?? ""
                    ) ?>
                </td>

                <td>
                    <?= htmlspecialchars(
                        $course["day"] ?? ""
                    ) ?>
                </td>

                <td>
                    Tiết
                    <?= htmlspecialchars(
                        (string)($course["start"] ?? "")
                    ) ?>
                    -
                    <?= htmlspecialchars(
                        (string)($course["end"] ?? "")
                    ) ?>
                </td>

                <td>
                    <?= htmlspecialchars(
                        (string)($course["capacity"] ?? "")
                    ) ?>
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
