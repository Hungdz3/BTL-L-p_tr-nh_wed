<?php

require_once "functions.php";

$notifications = [
    [
        "id" => 1,
        "title" => "Công khai luận án",
        "description" => "Công khai thông tin luận án Tiến sĩ của nghiên cứu sinh Trần Thị Thịnh trước khi bảo vệ luận án cấp Trường Đại học Thủ đô Hà Nội",
        "image_url" => "https://hnmu.edu.vn/sites/default/files/2026-08/anh-cong-khai.png"
    ],

    [
        "id" => 2,
        "title" => "Thông điệp",
        "description" => "Thông điệp năm học 2026 - 2027 của Hiệu trưởng",
        "image_url" => "https://hnmu.edu.vn/sites/default/files/2026-08/logo-hnmu-1.ai_.png"
    ],

    [
        "id" => 3,
        "title" => "Thông báo học phí",
        "description" => "Nhà trường thông báo thời gian nộp học phí cho học kỳ mới. Sinh viên cần hoàn thành đúng thời hạn.",
        "image_url" => "https://hnmu.edu.vn/sites/default/files/2026-06/quyet-dinh_4.jpg"
    ]
];

$title = "";
$description = "";
$image_url = "";

$error_title = "";
$error_description = "";
$error_image = "";

$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $title = trim($_POST["title"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $image_url = trim($_POST["image_url"] ?? "");

    if ($title === "") {
        $error_title = "Vui lòng nhập tiêu đề.";
    }

    if ($description === "") {
        $error_description = "Vui lòng nhập nội dung.";
    }

    if ($image_url === "") {
        $error_image = "Vui lòng nhập URL hình ảnh.";
    } elseif (!filter_var($image_url, FILTER_VALIDATE_URL)) {
        $error_image = "Image URL không hợp lệ.";
    } elseif (!preg_match(
        '/\.(jpg|jpeg|png|gif|webp|svg)(\?.*)?$/i',
        $image_url
    )) {
        $error_image = "URL phải là link hình ảnh (.jpg, .jpeg, .png, .gif, .webp hoặc .svg).";
    }

    if (
        $error_title === "" &&
        $error_description === "" &&
        $error_image === ""
    ) {

        $id = count($notifications) + 1;

        $notifications[] = [
            "id" => $id,
            "title" => $title,
            "description" => $description,
            "image_url" => $image_url
        ];

        $success = "Thêm thông báo thành công!";

        $title = "";
        $description = "";
        $image_url = "";
    }
}

?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Thông báo</title>
</head>

<body>

<h1>Thông báo</h1>

<h2>Thêm thông báo</h2>

<?php if ($success !== "") { ?>
    <p style="color: green; font-weight: bold;">
        <?php echo htmlspecialchars($success); ?>
    </p>
<?php } ?>

<form method="POST">

    <label>Tiêu đề:</label>

    <input
        type="text"
        name="title"
        value="<?php echo htmlspecialchars($title); ?>"
    >

    <?php if ($error_title !== "") { ?>
        <p style="color: red;">
            <?php echo htmlspecialchars($error_title); ?>
        </p>
    <?php } ?>

    <br><br>

    <label>Nội dung:</label>

    <br>

    <textarea
        name="description"
        rows="5"
        cols="50"
    ><?php echo htmlspecialchars($description); ?></textarea>

    <?php if ($error_description !== "") { ?>
        <p style="color: red;">
            <?php echo htmlspecialchars($error_description); ?>
        </p>
    <?php } ?>

    <br><br>

    <label>Image URL:</label>

    <input
        type="text"
        name="image_url"
        value="<?php echo htmlspecialchars($image_url); ?>"
    >

    <?php if ($error_image !== "") { ?>
        <p style="color: red;">
            <?php echo htmlspecialchars($error_image); ?>
        </p>
    <?php } ?>

    <br><br>

    <button type="submit">
        Thêm thông báo
    </button>

</form>

<hr>

<h2>Danh sách thông báo</h2>

<?php foreach ($notifications as $notification) { ?>

    <img
        src="<?php echo htmlspecialchars($notification["image_url"]); ?>"
        width="300"
        alt="<?php echo htmlspecialchars($notification["title"]); ?>"
    >

    <h3>
        <?php echo htmlspecialchars($notification["title"]); ?>
    </h3>

    <p>
        <?php echo htmlspecialchars($notification["description"]); ?>
    </p>

    <p>
        <strong>ID:</strong>
        <?php echo $notification["id"]; ?>
    </p>

    <p>
        <strong>Trạng thái:</strong>
        <?php
        echo htmlspecialchars(
            getNotificationStatus(
                $notification["title"],
                $notification["description"]
            )
        );
        ?>
    </p>

    <hr>

<?php } ?>

</body>

</html>