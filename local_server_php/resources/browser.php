<?php 
include_once $_SERVER['DOCUMENT_ROOT']."/settings.php";
session_start();
?>
<?php
// Access check
if(!isset($_SESSION['login']) || $_SESSION['login'] != 1){
    $_SESSION['login_redirect'] = "/resources/browser.php";
    echo("<script>document.location.href=\"https://access.line.me/oauth2/v2.1/authorize?response_type=code&client_id=2005653179&redirect_uri=https://ntu-bime-linebot.onrender.com/login_callback?authenticator=line&state=fsaafjwri20ttga0hwpjisg0t5&scope=openid%20profile&nonce=helloWorld&prompt=consent&max_age=3600&ui_locales=zh-TW&bot_prompt=normal\";</script>");
    return;
}

$uid = $_SESSION['userId'];

// 创建连接
$conn = new mysqli($mysql_servername, $mysql_username, $mysql_password, $mysql_dbname);
// 检查连接
if ($conn->connect_error) {
    die("Connection failed...: " . $conn->connect_error);
}

$sql = "SELECT * FROM bime_linebot_users WHERE uid='$uid'";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    if($row["sa_fee"] == 0) {
        echo("<script>document.location.href=\"/information/fee/index.php?q=access_products\";</script>");
        return;
    }
    $stu_id = $row['stu_id'];
}else{
    return;
}

?>

<?php
$path = escapeshellcmd($_GET['path']);
if($path[-1] != "/") {
    $file_str = exec('sudo /usr/bin/docker start department_product && sudo /usr/bin/docker exec -it department_product /bin/bash -c "cd /api && source /api/myenv/bin/activate && python /api/create_watermark.py '.$path.' '.$stu_id.'"');
    $ret = json_decode($file_str, true);
    if($ret['code'] != 0){
        echo($ret['response']);
    }else{
        $file_raw = base64_decode($ret['data']);
        $file_name = basename($path);
        // 檢查解碼是否成功
        if ($file_raw === false) {
            die('Base64 解碼失敗');
        }
        // 設置標頭以強制下載
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $file_name . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . strlen($file_raw));
        ob_clean(); // 清空緩衝區
        flush();    // 強制輸出緩衝區
        echo($file_raw);
    }
    return;
    
}else {
    $list = exec('sudo /usr/bin/docker start department_product && sudo /usr/bin/docker exec -it department_product /bin/bash -c "cd /api && source /api/myenv/bin/activate && python /api/list.py \''.$path.'\'"');
    $ret = json_decode($list, true);
    if($ret['code'] != 0){
        echo($ret['response']);
        return;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="/css/themify-icons.css" rel="stylesheet">
    <link href="/module/css/global-style.css" rel="stylesheet">  
    <link href="info/style.css" rel="stylesheet">
    <title>系產資源</title>
</head>
<body>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="/module/js/size-change.js"></script>
    <div class="floating">
        <span class="ti-close floating-close"></span>
        <div class="floating-content"></div>
        <script type="text/javascript" src="/module/js/dev-login.js"></script>
    </div>
    <div class="page">
        <div id="page-top" class="page-top"></div>
        <div class="page-center" id="page-center" style="min-height: 65vh;">
            <div id="inf-show">
                <h2>About: 系產</h2>
                <h3>Current path: <?php echo(htmlentities($path));?></h3>
            </div>
            <div id="file-list">
                <?php
                foreach($ret['data'] as $v) {
                    if($v['t'] == 0) { // file
                        echo('<div class="file-block file-type" onclick="window.open(\'browser.php?path='.$path.$v['n'].'\', \'_blank\');">');
                        echo('<span class="ti-file"></span>');
                        echo('<span class="file-name">&emsp;'.$v['n'].'</span>');
                    }else if($v['t'] == 1) {
                        echo('<div class="file-block folder-type" onclick="document.location.href=\'browser.php?path='.$path.$v['n'].'/'.'\'">');
                        echo('<span class="ti-folder"></span>');
                        echo('<span class="file-name">&emsp;'.$v['n'].'</span>');
                    }
                    echo('</div>');
                }
                ?>
                <!-- <div class="file-block file-type" onclick="document.location.href=''">
                    <span class="ti-file"></span>
                    <span class="file-name">Test.txt</span>
                </div>
                <div class="file-block folder-type">
                    <span class="ti-folder"></span>
                    <span class="file-name">A folder</span>
                </div> -->
            </div>
        </div><!-- div class="page-center" -->
        <div class="page-footer" id="page-footer"></div>
    </div>
    <script src="/module/js/floating.js"></script>
    <script>
        $("#page-top").load("/module/top.php");
        $("#page-footer").load("/module/foot.html");
    </script>
    
</body>
</html>