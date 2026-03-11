<?php
include_once $_SERVER['DOCUMENT_ROOT']."/settings.php";
session_start();
if($_SESSION['login'] != 1){
    echo("Not logged in");
    $_SESSION['login_redirect'] = "/reservation/maker_space/index.php";
    echo("<script>document.location.href=\"https://access.line.me/oauth2/v2.1/authorize?response_type=code&client_id=2005653179&redirect_uri=https://ntu-bime-linebot.onrender.com/login_callback?authenticator=line&state=fsaafjwri20ttga0hwpjisg0t5&scope=openid%20profile&nonce=helloWorld&prompt=consent&max_age=3600&ui_locales=zh-TW&bot_prompt=normal\";</script>");
    return;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>生機系創客空間預約系統</title>
    <link rel="stylesheet" href="res/style.css">
</head>
<body>
    <div class="clock">
        <h1 id="time"> </h1>
    </div>
    <div class="book-form section-block">
        <h2>請選擇要前往創客空間的日期及時間</h2>
        <p>What is the date you want to come?</p>
        <input type="date" id="book-date" class="date-selection" placeholder="Click to choose">
        <p>Select a start time</p>
        <input type="time" id="book-start-time" class="time-selection" placeholder="Click to choose">
        <p>Select an end time</p>
        <input type="time" id="book-end-time" class="time-selection" placeholder="Click to choose">
        <p>Select a machine/device you want to use</p>
        <select id="book-machine" placeholder="Click to choose">
<?php
// 创建连接
$conn = new mysqli($mysql_servername, $mysql_username, $mysql_password, $mysql_dbname);
// 检查连接
if ($conn->connect_error) {
    die('{"result":"error", "code":"[E3]Connection failed...: ' . $conn->connect_error . '"}');
}
$stu_id = $_SESSION['stu_id'];
// 查询数据库
$comm = "SELECT name, alias_name, status FROM bime_maker_space_machines";
$result = $conn->query($comm);
$comm2 = "SELECT * FROM bime_maker_space_machines_users WHERE stu_id='$stu_id'";
$result2 = $conn->query($comm2);
if ($result2->num_rows > 0) {
    $access_status = $result2->fetch_assoc();
}
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()){
        if($access_status[$row["alias_name"]] == 1 && $row["status"] == 1) {
            echo('<option value="'.$row["alias_name"].'">'.$row["name"].'</option>');
        }
    }
}
?>
        </select>
        <style>
            #book-start-time {
                background-color: #95ff7d;
            }

            #book-end-time {
                background-color: #ffc780;
            }
        </style>
        <p style="font-size: 12px;display: flex;flex-direction: row;"><label>
            <input type="checkbox" id="agree-with-eula">
            <span class="custom-checkbox"></span>
        </label>　我同意<a href="https://hackmd.io/@tseng91301/rku7EEfuye">創客空間的預約及使用條款</a></p>
        <button id="book-step-1" >預約</button>
    </div>

    <div class="book-tools section-block">
        <h2>預約功能表列</h2>
        <div class="tools-arr" >
            <a href="book_check.php" >預約紀錄查詢</a>
            <a href="/index.html" >回首頁</a>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="res/script.js"></script>
</body>
</html>
