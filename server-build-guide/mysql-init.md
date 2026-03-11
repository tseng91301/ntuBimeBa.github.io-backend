# 在 server 上面新增 mysql 用戶並授予相應權限
## 新增使用者
### 使用者創建
```sql
-- bime 是使用者名稱, 
CREATE USER 'bime'@'localhost' IDENTIFIED BY '<PASSWORD>';
```
### 授予資料庫權限給使用者
```sql
GRANT ALL PRIVILEGES ON bime_line_api_user.* TO 'bime'@'localhost';
FLUSH PRIVILEGES;
```
## 創建資料庫
```sql
CREATE DATABASE bime_line_api_user CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```
* 操作完畢後可以來測試資料庫是否成功貝登入，並且存取權正常
```shell
# 包含 host 連線的 sql 連線指令
mysql -u bime -p -h 127.0.0.1
# 接下來輸入密碼
```sql
-- 測試資料表創建是否能順利進行
use bime_line_api_user;

CREATE TABLE bime_linebot_users (
    id INT NOT NULL AUTO_INCREMENT,
    uid VARCHAR(1023) NOT NULL, -- line uid
    real_name VARCHAR(1023), -- 真實姓名
    username VARCHAR(1023) NOT NULL, -- Line 使用者名稱
    profile_img VARCHAR(1023), -- 個人檔案大頭貼連結
    stu_id VARCHAR(255), -- 學號
    passHash VARCHAR(255), -- 學號+密碼的哈希值
    add_date DATE, -- 加入日期
    status_code INT NOT NULL, -- 狀態碼
    notify_id VARCHAR(1023), -- line notify ID (已棄用)
    notify_access_token VARCHAR(1023), -- line notify access token (已棄用)
    sa_fee INT DEFAULT 0, -- 系學會費繳費狀態
    access_maker_space_reservation INT DEFAULT 0, 
    email VARCHAR(255),
    tel VARCHAR(255),
    discord VARCHAR(255), -- Discord ID
    address TEXT,
    note TEXT,
    PRIMARY KEY (id)
);
```