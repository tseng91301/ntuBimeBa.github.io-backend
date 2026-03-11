<?php
include_once $_SERVER['DOCUMENT_ROOT']."/settings.php";
session_start();
if(!isset($_SESSION['admin_login'])){
    echo('<script>window.location.href = "/admin_page/login.php";</script>');
    return;
}
?>

<?php
header('Content-Type: application/json');

if (!isset($_POST['name']) || trim($_POST['name']) === '') {
    echo json_encode(['success' => false, 'message' => 'Tag 名稱不可為空']);
    exit;
}

try {
    $pdo = new PDO($mysql_dsn, $mysql_username, $mysql_password);
    $stmt = $pdo->prepare("INSERT INTO legacy_tags (name) VALUES (:name)");
    $stmt->execute(['name' => $_POST['name']]);
    echo json_encode(['success' => true, 'id' => $pdo->lastInsertId(), 'name' => $_POST['name']]);
} catch (PDOException $e) {
    if ($e->errorInfo[1] == 1062) {
        // Duplicate entry
        $stmt = $pdo->prepare("SELECT id FROM legacy_tags WHERE name = :name");
        $stmt->execute(['name' => $_POST['name']]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'id' => $existing['id'], 'name' => $_POST['name']]);
    } else {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
