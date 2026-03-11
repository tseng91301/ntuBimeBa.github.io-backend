## 專案簡介與開發指南 ##

> 此專案文件是從正在運作中的網站專案複製過來，並且進行機密資訊屏蔽等必要資料防護措施，可以前往 https://ntubimeba.github.io/ 體驗實際網頁。

- 開發目標
  
  這裡是臺大生機系學會網站開發的代碼庫，**僅開放 NTU BIMEBA Server Team 人員獲取編輯權限進行合作開發**。我們期望透過系學會網站完成包含以下系務工作：

  1. 帳號管理：登入、帳戶權限管理、個人資料查詢
  2. 學生空間管理表單事務：文件下載、簽章繳交、違規記點
  3. 系產服務：驗證權限、文檔管理（排序方式與搜尋）、系產投稿
  4. 活動協助：活動公告宣傳、活動報名登記系統、活動團隊對系上成員公告
  5. 紀錄系學會聯絡資訊
  6. 完成其他系上指派之任務

## 專案技術棧 (Tech Stack)

本專案採用前後端分離與多語言後端微服務架構，主要技術包含：

- **Node.js (Express)**: 負責處理主要的 API 請求（位於 `local_server`），包含使用者驗證、系產文件上傳 (`multer`) 等功能。
- **PHP**: 負責後台管理介面及部分業務邏輯（位於 `local_server_php`），特別是 `admin_page` 目錄下實作了完整的管理者後台。
- **MySQL**: 作為關聯式結構資料的主要儲存方案（Node.js 使用 `mysql2` 模組，PHP 使用 `mysqli`/`PDO` 進行連線）。
- **Redis**: 負責快取或其他記憶體資料操作（伺服器配置需安裝 `redis-server`）。

### Node.js 中的 JWT 運作方式

本專案的 Node.js API 實作了基於 JSON Web Token (JWT) 的無狀態身分驗證，詳細運作流程如下：
1. **簽發 Token**: 當使用者透過 LINE 登入成功後，`local_server/server.js` 會呼叫 `jsonwebtoken` 模組的 `sign()`，將使用者的 `userId` 作為 Payload，搭配環境變數中的 `JWT_SECRET` 進行加密簽名，產生一組時效為 2 小時的 Token。
2. **傳遞給前端**: 伺服器將產生的 JWT Token 附帶於前端跳轉網址的參數中，交由前端妥善保存。
3. **驗證與授權**: 當前端向 API (例如 `users`, `legacy`, `application` 等路由) 發送需要驗證權限的請求時，必須提供此 Token。伺服器端會透過 `utils/basic_tools.js` 中的 `parse_jwt_token()` 函數，使用相同的 `JWT_SECRET` 來驗證 Token 的合法性及有效期限，並解析出 `userId` 以確認目前使用者的身分操作權限。若 Token 亦被竄改或過期，則會攔截並回傳 401 Unauthorized 錯誤。

### PHP 後台管理系統 (Admin Page)

管理員專用後台位於 `local_server_php/admin_page`，提供 UI 介面操作各項系務功能，主要涵蓋的模組包含：
- **帳號與權限資料庫管理** (`account.php`, `account_manage.php`)
- **應用程式與表單系統** (`application/`)
- **系產服務資產管理** (`legacy/`)
- **創客空間 (Maker Space) 預約與審核** (`maker_space/`, `reservation/`)
- **通知推播與訊息反饋** (`notification.php`, `chat_suggestion.php`)

## 伺服器設定

* 前往 [server-build-guide](./server-build-guide) 查看詳細的建構設定與架設說明。
