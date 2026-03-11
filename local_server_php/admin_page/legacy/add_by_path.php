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
// 檢查連線是否成功
if ($conn->connect_error) {
    die("資料庫連線失敗：" . $conn->connect_error);
}
$conn->set_charset("utf8mb4");
$result = $conn->query("SELECT id, name FROM legacy_tags ORDER BY name ASC");
// 檢查查詢是否成功
if (!$result) {
    die("查詢失敗：" . $conn->error);
}
// 取得所有標籤資料
$tags = [];
while ($row = $result->fetch_assoc()) {
    $tags[] = $row;
}
$result->free();
$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>系產文件上傳</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <h1>上傳系產文件</h1>
    <form id="documentForm" method="post" action="utils/submit_document.php" enctype="multipart/form-data">
        <label>名稱：<input type="text" name="name" required></label><br>
        <label>屆數/學年：<input type="number" name="year" required></label><br>
        <label>年級：<input type="number" name="grade" required></label><br>
        <label>學期：
            <select name="semester" required>
                <option value="1">上</option>
                <option value="2">下</option>
                <option value="3">暑修</option>
            </select>
        </label><br>
        <label>授課教師：<input type="text" name="teacher"></label><br>
        <label>科目：<input type="text" name="subject" required></label><br>
        <label>課程代碼：<input type="text" name="course_code"></label><br>
        <label>類型：
            <select name="type" required>
                <option value="homework">作業</option>
                <option value="exam">考卷</option>
                <option value="solution">解答</option>
                <option value="handout">講義</option>
            </select>
        </label><br>
        <label>簡單描述：<br><textarea name="description"></textarea></label><br>
        <label>檔案路徑 (root: /)<input type="text" name="file_path" required></label><br>
        <label>學號：<input type="text" name="created_by_stu_id" required></label><br>
        <label>姓名：<input type="text" name="created_by_real_name" required></label><br>

        <fieldset>
            <legend>標籤</legend>
            <div id="tagCheckboxes">
                <?php foreach ($tags as $tag): ?>
                    <label>
                        <input type="checkbox" name="tags[]" value="<?= htmlspecialchars($tag['id']) ?>">
                        <?= htmlspecialchars($tag['name']) ?>
                    </label><br>
                <?php endforeach; ?>
            </div>
            <button type="button" id="addTagBtn">+ 新增標籤</button>
        </fieldset>

        <button type="submit">新增系產</button>
    </form>

    <script>
        // 監聽檔案路徑欄位輸入，將所有反斜線 \ 換成斜線 /
        $('input[name="file_path"]').on('input', function() {
            const val = $(this).val();
            if (val.includes('\\')) {
                $(this).val(val.replace(/\\/g, '/'));
            }
        });
        
        // 新增 tag 彈窗輸入與送出
        $('#addTagBtn').on('click', function() {
            const tagName = prompt('輸入新標籤名稱：');
            if (tagName) {
                $.post('utils/insert_tag.php', { name: tagName }, function(response) {
                    if (response.success) {
                        const newTag = `<label><input type='checkbox' name='tags[]' value='${response.id}' checked> ${response.name}</label><br>`;
                        $('#tagCheckboxes').append(newTag);
                    } else {
                        alert(response.message || '新增失敗');
                    }
                }, 'json');
            }
        });

        // 表單 Ajax 提交
        $('#documentForm').on('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            // for (let [key, value] of formData.entries()) {
            //     console.log(key, value);
            // }

            const submitBtn = $(this).find('button[type="submit"]');
            submitBtn.prop('disabled', true).text('Processing...');

            $.ajax({
                url: 'utils/submit_document.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {
                    console.log(res)
                    alert('提交成功！');
                    submitBtn.prop('disabled', false).text('新增系產');
                },
                error: function(err) {
                    alert('提交失敗');
                    submitBtn.prop('disabled', false).text('新增系產');
                }
            });
        });
    </script>
</body>
</html>
