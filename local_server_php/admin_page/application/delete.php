<?php
include_once $_SERVER['DOCUMENT_ROOT']."/settings.php";
session_start();
if(!isset($_SESSION['admin_login'])){
    echo('<script>window.location.href = "/admin_page/login.php";</script>');
    return;
}
?>
<?php
// 連線
$mysqli = new mysqli($mysql_servername, $mysql_username, $mysql_password, $mysql_dbname);
if ($mysqli->connect_error) {
    http_response_code(500);
    exit('Database connection failed');
}

// 取得要刪除的 application ID
$id = $_POST['id'] ?? null;
if (!$id) {
    http_response_code(400);
    exit('Missing ID');
}

// 1. 先取得關聯檔案
$stmt = $mysqli->prepare("SELECT f.path FROM open_files f 
                          JOIN application_file_link l ON f.id = l.document_id
                          WHERE l.application_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
$filesToDelete = [];
while ($row = $res->fetch_assoc()) {
    $filesToDelete[] = $row['path'];
}
$stmt->close();

// 2. 刪除關聯資料（link + open_files + all_applications）
// link 表會因 foreign key cascade 自動刪除
$stmt = $mysqli->prepare("DELETE FROM open_files WHERE id IN (
    SELECT document_id FROM application_file_link WHERE application_id = ?
)");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->close();

// 刪除申請作業
$stmt = $mysqli->prepare("DELETE FROM all_applications WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->close();

// 3. 刪除實體檔案（可選）
$document_store_place = getenv("DOCUMENT_STORE_PLACE");
foreach ($filesToDelete as $file) {
    $filePath = $document_store_place . '/' . $file;
    if (file_exists($filePath)) {
        @unlink($filePath);
    }
}

echo json_encode(['success'=>true]);
