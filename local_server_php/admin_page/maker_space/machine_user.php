<?php
include_once $_SERVER['DOCUMENT_ROOT']."/settings.php";
session_start();
if(!isset($_SESSION['admin_login'])){
    echo('<script>window.location.href = "/admin_page/login.php";</script>');
    return;
}
?>
<?php
$conn = new mysqli($mysql_servername, $mysql_username, $mysql_password, $mysql_dbname);
// 检查连接
if ($conn->connect_error) {
    die("Connection failed...: " . $conn->connect_error);
}
// 查询数据库
$sql = "SELECT name, alias_name FROM bime_maker_space_machines";
$result = $conn->query($sql);
$names = [];
$alias_names = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()){
        $names[] = $row["name"];
        $alias_names[] = $row["alias_name"];
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>設備使用者權限列表</title>
</head>
<body>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <table id="machine_users_list">
        <tbody>
            <tr>
                <td></td>
                <?php
                foreach($names as $item) {
                    echo("<td>$item</td>");
                }
                ?>
            </tr>
<?php
$sql = "SELECT * FROM bime_maker_space_machines_users";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()){
        $stu_id = $row["stu_id"];
        echo("<tr>");
        echo("<td>$stu_id</td>");
        foreach($alias_names as $item) {
            echo('<td><input type="number" class="permission-change table-edit-element" id="p-'.$item.'-'.$stu_id.'" value="'.$row[$item].'"></td>');
        }
        echo("</tr>");
    }
}
?>
        </tbody>
    </table>
    <a href="/admin_page/index.php">回首頁</a>
</body>
<style>
    .safe {
        color: green;
    }
    .danger {
        color: red;
    }
    table td {
        border-left: 2px groove;
        border-bottom: 2px groove;
    }
</style>
<script>
    $(document).ready(function() {
        // 監聽具有 class "my-input" 的 input 元素的變化
        $('.permission-change').on('blur', function() {
            // 獲取當前 input 的 id
            var inputId = $(this).attr('id');
            // 獲取當前 input 的值
            var inputValue = $(this).val();
            if(inputValue != "") {
                var inf = inputId.split("-");
                var inp_stu_id = inf[2];
                var mach = inf[1];
                $.ajax({
                url: 'api/machine_user.php', // 請求的 URL
                type: 'POST', // 請求類型
                data: {
                    "o": 'chmod',
                    stu_id: inp_stu_id,
                    machine: mach,
                    val: parseInt(inputValue, 10)
                },
                success: function(response) {
                    console.log('Change successfully: ', response);
                },
                error: function(xhr, status, error) {
                    console.error('Error: ', error);
                }
            });
            }
        });
    });
</script>
</html>