<?php
    $mysql_servername = "localhost";
    $mysql_username = getenv("MYSQL_USER");
    $mysql_password = getenv('MYSQL_PASSWORD');
    $mysql_dbname = getenv('MYSQL_USE_DATABASE');
    $mysql_dsn = "mysql:host=$mysql_servername;dbname=$mysql_dbname;charset=utf8mb4";

    $notification_root = "/mnt/disk1/linebot_notifications";

    // Line Bot server
    $LINE_CHANNEL_ACCESS_TOKEN = getenv('CHANNEL_ACCESS_TOKEN');
    $LINE_CHANNEL_SECRET = getenv('CHANNEL_SECRET');
    $LINE_LOGIN_ID = getenv('LINE_LOGIN_ID');
    $LINE_LOGIN_SECRET = getenv('LINE_LOGIN_SECRET');
    $LINE_LOGIN_REDIRECT_URI = getenv('LINE_LOGIN_REDIRECT_URI');
    $HTTP_TRANSFER_SECRET_KEY = getenv('TRANSFER_SECRET_KEY');

    function generateRandomString($length = 20) {
        // 定義可用的字符集合
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
    
        // 使用 random_bytes 生成隨機字節
        $bytes = random_bytes($length);
    
        // 將隨機字節轉換為英數字字符
        for ($i = 0; $i < $length; $i++) {
            $index = ord($bytes[$i]) % $charactersLength;
            $randomString .= $characters[$index];
        }
    
        return $randomString;
    }

    function find_notification_access_token($mysql_proc, $uid) {
        $sql = "SELECT * FROM bime_linebot_users WHERE uid='$uid'";
        $result = $mysql_proc->query($sql);
        $row = $result->fetch_assoc();
        if($row['notify_access_token'] != null) {
            return $row['notify_access_token'];
        }else {
            return "";
        }
    }

    function send_line_text_notification($nid, $text) {
        if($nid == null || $nid == "") {
            return 0;
        }
        // Send message
        $token_url = 'https://notify-api.line.me/api/notify';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $token_url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer $nid"
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true); // 设置请求方法为 POST
        $data = [
            'message' => $text
        ];
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data)); // 将数据编码为 URL 查询字符串
        $response = curl_exec($ch);
        $success = 0;
        if (curl_errno($ch)) {
            $success = 0;
        } else {
            $success = 1;
        }
        curl_close($ch);
        return $success;
    }

    function check_maker_space_access_table($mysql_proc, $stu_id) {
        $sql = "SELECT * FROM bime_maker_space_machines_users WHERE stu_id='$stu_id'";
        $result = $mysql_proc->query($sql);
        if ($result->num_rows > 0) {
            
        }else {
            $sql = "insert into bime_maker_space_machines_users (stu_id) values ('$stu_id')";
            $mysql_proc->query($sql);
        }
    }

    function get_machine_prop($mysql_proc, $alias_name) {
        $sql = "SELECT * FROM bime_maker_space_machines WHERE alias_name='$alias_name'";
        $result = $mysql_proc->query($sql);
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row;
        }else {
            return [];
        }
    }
?>