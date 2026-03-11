<?php
include_once $_SERVER['DOCUMENT_ROOT']."/settings.php";
session_start();
if(!isset($_SESSION['admin_login'])){
    echo('<script>window.location.href = "/admin_page/login.php";</script>');
    return;
}
?>
<?php
$mysqli = new mysqli($mysql_servername, $mysql_username, $mysql_password, $mysql_dbname);
if ($mysqli->connect_error) {
    http_response_code(500);
    exit('Database connection failed');
}

$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'] ?? null;
$field = $data['field'] ?? null;
$value = $data['value'] ?? null;

// 檢查欄位安全
$allowed = ['name','description','add_date','expires'];
if (!$id || !$field || !in_array($field, $allowed)) {
    http_response_code(400);
    echo json_encode(['error'=>'Invalid input']);
    exit;
}

// SQL 更新
$stmt = $mysqli->prepare("UPDATE all_applications SET $field = ? WHERE id = ?");
$stmt->bind_param("si", $value, $id);
$stmt->execute();
$stmt->close();

echo json_encode(['success'=>true]);
