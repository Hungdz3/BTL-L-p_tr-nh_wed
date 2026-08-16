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

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $id = count($notifications) + 1;
    $title = $_POST["title"];
    $description = $_POST["description"];
    $image_url = $_POST["image_url"];

    $notifications[] = [
        "id" => $id,
        "title" => $title,
        "description" => $description,
        "image_url" => $image_url
    ];
}

foreach ($notifications as $notification) {

    echo "ID: " . $notification["id"] . "<br>";
    echo "Tiêu đề: " . $notification["title"] . "<br>";
    echo "Nội dung: " . $notification["description"] . "<br>";
    echo "Hình ảnh: " . $notification["image_url"] . "<br>";

    echo "Trạng thái: " .
        getNotificationStatus(
            $notification["title"],
            $notification["description"]
        ) . "<br>";

    echo "<hr>";
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

<form method="POST">

    <label>Tiêu đề:</label>
    <input type="text" name="title" required>
    <br><br>

    <label>Nội dung:</label>
    <textarea name="description" required></textarea>
    <br><br>

    <label>Image URL:</label>
    <input type="text" name="image_url" required>
    <br><br>

    <button type="submit">Thêm thông báo</button>

</form>

<hr>

<h2>Danh sách thông báo</h2>

<?php foreach ($notifications as $notification) { ?>

    <img
        src="<?php echo $notification["image_url"]; ?>"
        width="300"
    >

    <h3>
        <?php echo $notification["title"]; ?>
    </h3>

    <p>
        <?php echo $notification["description"]; ?>
    </p>

    <p>
        ID: <?php echo $notification["id"]; ?>
    </p>

    <p>
        Trạng thái:
        <?php
        echo getNotificationStatus(
            $notification["title"],
            $notification["description"]
        );
        ?>
    </p>

    <hr>

<?php } ?>

</body>

</html>