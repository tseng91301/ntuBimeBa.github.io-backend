<?php
include_once $_SERVER['DOCUMENT_ROOT']."/settings.php";
session_start();
if(!isset($_SESSION['admin_login'])){
    echo('{"status": "error", "e": "Not Logged In!"}');
    return;
}
?>
<?php
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $o = $_POST["o"];
    $attr = $_POST["attr"];
    $machine = $_POST["machine"];
    $val = $_POST["val"];
    // Open mysql connection
    $conn = new mysqli($mysql_servername, $mysql_username, $mysql_password, $mysql_dbname);
    // 检查连接
    if ($conn->connect_error) {
        die("Connection failed...: " . $conn->connect_error);
    }

    if($o == "del") {
        $comm = "DELETE FROM bime_maker_space_machines WHERE alias_name='$machine'";
        $comm2 = "ALTER TABLE bime_maker_space_machines_users DROP COLUMN $machine";
        // 执行插入操作并检查是否成功
        if ($conn->query($comm) === TRUE && $conn->query($comm2) === TRUE) {
            echo('{"status": "success"}');
        } else {
            echo('{"status": "error", "e": '.$conn->error.'}');
        }
        return;
    } 

    if($attr == "status") {
        $comm = "UPDATE bime_maker_space_machines SET $attr=$val where alias_name='$machine'";
    }else {
        $comm = "UPDATE bime_maker_space_machines SET $attr='$val' where alias_name='$machine'";
    }
    // 执行插入操作并检查是否成功
    if ($conn->query($comm) === TRUE) {
        echo('{"status": "success"}');
    } else {
        echo('{"status": "error", "e": '.$conn->error.'}');
    }
    return;
}
?>