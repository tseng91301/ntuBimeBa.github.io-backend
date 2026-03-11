<?php
include_once $_SERVER['DOCUMENT_ROOT']."/settings.php";
session_start();
if(!isset($_SESSION['admin_login'])){
    echo('<script>window.location.href = "/admin_page/login.php";</script>');
    return;
}
?>
<?php

// Open mysql connection
$mysqli = new mysqli($mysql_servername, $mysql_username, $mysql_password, $mysql_dbname);
// 检查连接
if ($mysqli->connect_error) {
    die("Database connection failed...: " . $conn->connect_error);
}

// ======== 表單送出處理 ========
$document_store_place = getenv("DOCUMENT_STORE_PLACE");
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $expires = $_POST['expires'] ?? '';

    // 檢查必填欄位
    if (empty($name) || empty($expires)) {
        http_response_code(400);
        echo json_encode(['error' => '名稱和截止日期為必填']);
        exit;
    }

    // 1. 新增申請作業
    $stmt = $mysqli->prepare("INSERT INTO all_applications (name, description, expires) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $description, $expires);
    $stmt->execute();
    $applicationId = $stmt->insert_id;
    $stmt->close();

    // 2. 處理檔案上傳
    if (!empty($_FILES['files']['name'][0])) {
        $uploadDir = $document_store_place . "/";

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        foreach ($_FILES['files']['tmp_name'] as $key => $tmpName) {
            if ($_FILES['files']['error'][$key] === UPLOAD_ERR_OK) {
                $originalName = $_FILES['files']['name'][$key];
                $safeName = time() . "_" . basename($originalName); 
                $targetPath = $uploadDir . $safeName;

                if (move_uploaded_file($tmpName, $targetPath)) {
                    // 存 open_files
                    $stmt = $mysqli->prepare("INSERT INTO open_files (name, description, path) VALUES (?, ?, ?)");
                    $emptyDesc = ''; // 如果需要可改成從表單取值
                    $filePath = $safeName;
                    $stmt->bind_param("sss", $originalName, $emptyDesc, $filePath);
                    $stmt->execute();
                    $documentId = $stmt->insert_id;
                    $stmt->close();

                    // 存 link
                    $stmt = $mysqli->prepare("INSERT INTO application_file_link (application_id, document_id) VALUES (?, ?)");
                    $stmt->bind_param("ii", $applicationId, $documentId);
                    $stmt->execute();
                    $stmt->close();
                }
            }
        }
    }

    echo json_encode(['success' => true, 'application_id' => $applicationId]);
    exit;
}
?>

<!-- ======== HTML 表單 ======== -->
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>新增申請作業</title>
</head>
<body>
    <h2>新增申請作業</h2>
    <form method="post" enctype="multipart/form-data">
        <label>名稱 (必填)：</label><br>
        <input type="text" name="name" required><br><br>

        <label>描述：</label><br>
        <textarea name="description"></textarea><br><br>

        <label>截止日期 (必填)：</label><br>
        <input type="datetime-local" name="expires" required><br><br>

        <label>上傳檔案（可多選）：</label><br>
        <input type="file" name="files[]" multiple><br><br>

        <button type="submit">送出</button>
    </form>
</body>
</html>