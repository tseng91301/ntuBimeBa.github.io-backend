<?php
include_once $_SERVER['DOCUMENT_ROOT']."/settings.php";
session_start();
if(!isset($_SESSION['admin_login'])){
    echo('<script>window.location.href = "/admin_page/login.php";</script>');
    return;
}
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
<meta charset="UTF-8">
<title>管理申請作業</title>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<style>
body {
    font-family: "Microsoft JhengHei", sans-serif;
    margin: 20px;
    background: #f4f7f8;
    color: #333;
}

h2 { color: #2c3e50; }

button {
    padding: 6px 12px;
    border: none;
    background-color: #3498db;
    color: #fff;
    border-radius: 4px;
    cursor: pointer;
}
button:hover { background-color: #2980b9; }

table {
    border-collapse: collapse;
    width: 100%;
    background: #fff;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}
th, td {
    border: 1px solid #ddd;
    padding: 8px;
    text-align: left;
}
th {
    background-color: #3498db;
    color: white;
}
input, textarea, select {
    width: 95%;
    padding: 4px;
    border-radius: 3px;
    border: 1px solid #ccc;
}
textarea { resize: vertical; }

#newApplicationModal {
    display:none;
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 400px;
    background: #fff;
    padding: 20px;
    box-shadow: 0 3px 10px rgba(0,0,0,0.2);
    border-radius: 6px;
    z-index: 1000;
}
#modalOverlay {
    display: none;
    position: fixed;
    top:0; left:0;
    width:100%; height:100%;
    background: rgba(0,0,0,0.4);
    z-index: 900;
}
</style>
</head>
<body>

<h2>申請作業管理</h2>
<button id="newApplicationBtn">新增申請作業</button>
<button onclick="window.location.href = '/admin_page/'">回首頁</button>
<br><br>

<table id="applicationsTable">
    <thead>
        <tr>
            <th>ID</th>
            <th>名稱</th>
            <th>描述</th>
            <th>新增時間</th>
            <th>截止日期</th>
            <th>檔案</th>
            <th>操作</th>
        </tr>
    </thead>
    <tbody></tbody>
</table>

<!-- Modal overlay -->
<div id="modalOverlay"></div>

<!-- 新增申請 modal -->
<div id="newApplicationModal">
    <h3>新增申請作業</h3>
    <form id="newApplicationForm" enctype="multipart/form-data">
        <label>名稱：</label><br>
        <input type="text" name="name" required><br><br>

        <label>描述：</label><br>
        <textarea name="description"></textarea><br><br>

        <label>截止日期：</label><br>
        <input type="datetime-local" name="expires" required><br><br>

        <label>上傳檔案（可多選）：</label><br>
        <input type="file" name="files[]" multiple><br><br>

        <button type="submit">送出</button>
        <button type="button" id="cancelBtn">取消</button>
    </form>
</div>

<script>
// 讀取 applications 並 render
function loadApplications() {
    $.getJSON('get_applications.php', function(data){
        const tbody = $('#applicationsTable tbody');
        tbody.empty();
        data.forEach(app => {
            const row = $('<tr>');
            row.append(`<td>${app.id}</td>`);
            row.append(`<td><input type="text" data-id="${app.id}" data-field="name" value="${app.name}"></td>`);
            row.append(`<td><textarea data-id="${app.id}" data-field="description">${app.description}</textarea></td>`);
            row.append(`<td><input type="datetime-local" data-id="${app.id}" data-field="add_date" value="${app.add_date.replace(' ', 'T')}"></td>`);
            row.append(`<td><input type="datetime-local" data-id="${app.id}" data-field="expires" value="${app.expires ? app.expires.replace(' ', 'T') : ''}"></td>`);

            // 檔案欄位
            let filesTd = $('<td>');
            $.getJSON('get_files.php?application_id=' + app.id, function(files){
                files.forEach(f=>{
                    const fileLink = $('<a>')
                        .attr('href', f.path)
                        .attr('target', '_blank')
                        .text(f.name);
                    filesTd.append(fileLink).append('<br>');
                });
            });
            row.append(filesTd);
            row.append(`<td><button class="deleteBtn" data-id="${app.id}">刪除</button></td>`);

            tbody.append(row);
        });
    });
}

// input / textarea blur 時更新
$(document).on('blur', 'input, textarea', function(){
    const id = $(this).data('id');
    const field = $(this).data('field');
    const value = $(this).val();

    $.ajax({
        url: 'update_application.php',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({id, field, value}),
        success: function(res){ console.log(res); }
    });
});

// 顯示新增 modal
$('#newApplicationBtn').click(function(){
    $('#modalOverlay, #newApplicationModal').show();
});

// 取消 modal
$('#cancelBtn, #modalOverlay').click(function(){
    $('#modalOverlay, #newApplicationModal').hide();
});

// 刪除按鈕事件
$(document).on('click', '.deleteBtn', function(){
    if (!confirm('確定要刪除這筆申請作業嗎？')) return;

    const id = $(this).data('id');
    $.ajax({
        url: 'delete.php',
        method: 'POST',
        data: { id: id },
        success: function(res){
            alert('刪除成功！');
            loadApplications();
        },
        error: function(xhr){
            alert('刪除失敗: ' + xhr.responseText);
        }
    });
});

// 送出新增申請 AJAX
$('#newApplicationForm').submit(function(e){
    e.preventDefault();
    const formData = new FormData(this);
    $.ajax({
        url: 'add_new.php',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(res){
            alert('新增成功！');
            $('#modalOverlay, #newApplicationModal').hide();
            loadApplications();
        },
        error: function(xhr){
            alert('新增失敗: ' + xhr.responseText);
        }
    });
});

// 初次載入
loadApplications();
</script>
</body>
</html>
