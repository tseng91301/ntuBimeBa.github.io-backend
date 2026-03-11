<?php
include_once $_SERVER['DOCUMENT_ROOT']."/settings.php";
session_start();
if(!isset($_SESSION['admin_login'])){
    echo('<script>window.location.href = "/admin_page/login.php";</script>');
    return;
}
?>
<?php
try {
    // if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //     echo "<pre>";
    //     print_r($_POST);
    //     print_r($_FILES);
    //     echo "</pre>";
    //     exit;
    // }
    $pdo = new PDO($mysql_dsn, $mysql_username, $mysql_password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 插入 legacy_documents
    $stmt = $pdo->prepare("INSERT INTO legacy_documents 
        (name, year, grade, semester, teacher, subject, course_code, type, description, file_path, created_by_stu_id, created_by_real_name)
        VALUES (:name, :year, :grade, :semester, :teacher, :subject, :course_code, :type, :description, :file_path, :created_by_stu_id, :created_by_real_name)");

    $stmt->execute([
        'name' => $_POST['name'],
        'year' => $_POST['year'],
        'grade' => $_POST['grade'],
        'semester' => $_POST['semester'],
        'teacher' => $_POST['teacher'] ?? null,
        'subject' => $_POST['subject'],
        'course_code' => $_POST['course_code'] ?? null,
        'type' => $_POST['type'],
        'description' => $_POST['description'] ?? null,
        'file_path' => $_POST['file_path'],
        'created_by_stu_id' => $_POST['created_by_stu_id'],
        'created_by_real_name' => $_POST['created_by_real_name']
    ]);

    $document_id = $pdo->lastInsertId();

    // 插入 legacy_document_tags
    if (isset($_POST['tags']) && is_array($_POST['tags'])) {
        $stmtTag = $pdo->prepare("INSERT INTO legacy_document_tags (document_id, tag_id) VALUES (:document_id, :tag_id)");
        foreach ($_POST['tags'] as $tag_id) {
            $stmtTag->execute([
                'document_id' => $document_id,
                'tag_id' => $tag_id
            ]);
        }
    }

    echo "Success";
} catch (PDOException $e) {
    http_response_code(500);
    echo "錯誤：" . $e->getMessage();
}
