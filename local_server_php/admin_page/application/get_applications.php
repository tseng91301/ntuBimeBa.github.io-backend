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

$result = $mysqli->query("SELECT id, name, description, add_date, expires FROM all_applications ORDER BY id DESC");

$applications = [];
while ($row = $result->fetch_assoc()) {
    $applications[] = $row;
}

header('Content-Type: application/json');
echo json_encode($applications);
?>