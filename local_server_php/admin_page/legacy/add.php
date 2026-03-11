<?php
include_once $_SERVER['DOCUMENT_ROOT']."/settings.php";
session_start();
if(!isset($_SESSION['admin_login'])){
    echo('<script>window.location.href = "/admin_page/login.php";</script>');
    return;
}
?>
<?php
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $machine_name = $_POST["machine_name"];
    $machine_alias_name = $_POST['machine_alias_name'];
    $machine_admin = $_POST["machine_admin"];
    $machine_description = $_POST["machine_description"];
    $machine_default_permission = $_POST["machine_default_permission"];
    // Open mysql connection
    $conn = new mysqli($mysql_servername, $mysql_username, $mysql_password, $mysql_dbname);
    // 检查连接
    if ($conn->connect_error) {
        die("Connection failed...: " . $conn->connect_error);
    }

    $comm = "insert into bime_maker_space_machines (name, alias_name, admin_stu_id, machine_description) values ('$machine_name', '$machine_alias_name', '$machine_admin', '$machine_description')";
    $comm2 = "alter table bime_maker_space_machines_users add $machine_alias_name INT DEFAULT $machine_default_permission";
    // 执行插入操作并检查是否成功
    if ($conn->query($comm) === TRUE && $conn->query($comm2) === TRUE) {
        echo("<script>alert('新增成功!');</script>");
        echo('<script>window.location.href = "machines.php";</script>');
    } else {
        echo("<script>alert('Task Failed... ".$conn->error."');</script>");
    }
    return;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>新增設備</title>
</head>
<body>
    <form action="add_machine.php" method="post">
        機器名稱: <input name="machine_name" required><br/>
        機器 Alias name (限英文+底線，不可修改！): <input name="machine_alias_name" required><br/>
        機器管理員(學號): <input name="machine_admin"><br/>
        機器預設使用權限: <input type="number" name="machine_default_permission" required value="0"><br/>
        說明: 
<textarea name="machine_description">

</textarea><br/>
        <input type="submit" value="新增機器"><br/>
    </form>
</body>
</html>