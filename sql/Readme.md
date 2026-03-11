# 資料庫資訊
## 帳號
user: bime
pass: BIME2024aewBHDBzrag493903gvag9u835gg834
database_name: bime_line_api_user
## 登入方式
mysql -u bime -p
[password]
use bime_line_api_user

# 資料表加入順序
1. users-tables-add.sql
2. makerspace-reservation-add.sql
3. legacy-tables-add.sql
4. files-tables-add.sql
5. applications.sql

# 資料表定義

Table 1: bime_linebot_users
description: The database that stores user's information
`status_code`: 
1: Good
2: real name not set
3: suspend
4: Waiting to be qualified

Table 2: bime_line_api_notification
description: The database that stores notification

Table 3: bime_line_api_admins
description: The database that stores admin users

Table 4: bime_line_api_chat_suggestion
description: The database that stores chat response suggestions

Table 5: bime_maker_space_reservation
description: The database that stores the reservation of BIME Maker Space
`status_code`:
0: 預約成立
1: 正在審核
2: 正在安排管理人員
3: 預約取消
4: 預約被禁止
5: 預約未到
6: Show up
7: 預約被管理員取消

Table 6: bime_maker_space_machines
description: The database that stores the machines of BIME Maker Space, status.

Table 7: bime_maker_space_machines_users
description: The database that stores who can use machines of BIME Maker Space.
* 隨著機器登錄，會不斷增加欄位(使用 alias_name 作為名稱)，並預設所有人無法使用

