<?php
include_once $_SERVER['DOCUMENT_ROOT']."/settings.php";
session_start();
if(!isset($_SESSION['admin_login'])){
    echo('<script>window.location.href = "/admin_page/login.php";</script>');
    return;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>創客空間 - 設備管理</title>
</head>
<body>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <a href="add_machine.php">新增設備</a><br/>
    <a href="machine_user.php" target="_blank">設備使用者權限列表</a><br/>
    <table id="machine_list">
        <tbody>
        <tr>
            <td>id</td>
            <td>機器名稱</td>
            <td>機器 Alias name</td>
            <td>管理員學號</td>
            <td>狀態</td>
            <td>說明</td>
            <td>操作</td>
        </tr>
<?php
$conn = new mysqli($mysql_servername, $mysql_username, $mysql_password, $mysql_dbname);
// 检查连接
if ($conn->connect_error) {
    die("Connection failed...: " . $conn->connect_error);
}
// 查询数据库
$sql = "SELECT * FROM bime_maker_space_machines";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()){
        echo("<tr id='m-".$row["alias_name"]."'>");
        echo('<td>'.$row['id'].'</td>');
        echo('<td><input type="text" class="table-edit-element" id="m-'.$row['alias_name'].'-name" value="'.$row["name"].'"></td>');
        echo('<td>'.$row['alias_name'].'</td>');
        echo('<td><input type="text" class="table-edit-element" id="m-'.$row['alias_name'].'-admin_stu_id" value="'.$row["admin_stu_id"].'"></td>');
        echo('<td><input type="number" class="table-edit-element" id="m-'.$row['alias_name'].'-status" value="'.$row["status"].'"></td>');
        echo('<td><textarea class="table-edit-element" id="m-'.$row['alias_name'].'-machine_description">'.$row["machine_description"].'</textarea></td>');
        echo('<td><input type="button" class="delete-machine-btn" id="m-'.$row['alias_name'].'-delete" value="刪除"></td>');
        echo('</tr>');
    }
}
?>


        </tbody>
    </table>
    <a href="/admin_page/index.php">回首頁</a>
</body>
<script>
    $(document).ready(function() {
        // 監聽具有 class "my-input" 的 input 元素的變化
        $('.table-edit-element').on('blur', function() {
            // 獲取當前 input 的 id
            var inputId = $(this).attr('id');
            // 獲取當前 input 的值
            var inputValue
            var required_text = 0
            if($(this).is("input")) {
                inputValue = $(this).val();
                required_text = 1;
            }else if($(this).is("textarea")) {
                inputValue = $(this).val();
            }
            if(inputValue != "" || required_text == 0) {
                var inf = inputId.split("-");
                var inp_attr = inf[2];
                var mach = inf[1];
                $.ajax({
                    url: 'api/machines.php', // 請求的 URL
                    type: 'POST', // 請求類型
                    data: {
                        "o": 'ch',
                        attr: inp_attr,
                        machine: mach,
                        val: inputValue
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

        $('.delete-machine-btn').click(function() {
            var btn_id = $(this).attr('id');
            var inf = btn_id.split("-");
            var mach = inf[1];
            confirm_del = confirm("你確定要刪除機器嗎?");
            if(confirm_del) {
                $.ajax({
                    url: 'api/machines.php', // 請求的 URL
                    type: 'POST', // 請求類型
                    data: {
                        "o": 'del',
                        machine: mach,
                    },
                    success: function(response) {
                        console.log('Delete successfully: ', response);
                        $("#m-" + mach).remove();
                    },
                    error: function(xhr, status, error) {
                        console.error('Error: ', error);
                    }
                });
            }
        })
    });
</script>
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
</html>