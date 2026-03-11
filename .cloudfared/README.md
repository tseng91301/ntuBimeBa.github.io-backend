# Cloudfare tunnel 管理
## Cloudfare 網頁端的設定
### 先創建一個免費的網域 (若還沒有創建)
* 可參考 [零度解說文章](https://www.freedidi.com/20033.html) 或 [零度解說影片](https://www.youtube.com/watch?v=aZGlGjn4OHM&t=487s) 建立
### 將創建的網域放到 Cloudfare 上面託管 (可參考上面的文章)
### 在完成 Linux 端設定後
* 在 Linux 主機輸入以下指令查看 tunnel id:
```shell
cloudflared tunnel list
```
* 登入 cloudfare dashboard
* 前往 `你的網域 → DNS 設定`
新增以下 DNS 記錄（如果還沒加）：

類型 | 名稱 | 內容 | Proxy 狀態 |
| --- | --- | --- | --- |
CNAME | ntubimeba.dpdns.org | &lt;Tunnel ID&gt;.cfargotunnel.com | ✅ 橘色雲朵（啟用 Proxy）|
CNAME | admin.ntubimeba.dpdns.org | &lt;Tunnel ID&gt;.cfargotunnel.com | ✅ 橘色雲朵（啟用 Proxy）|
CNAME | ssh.ntubimeba.dpdns.org | &lt;Tunnel ID&gt;.cfargotunnel.com | ✅ 橘色雲朵（啟用 Proxy）|

## Linux 主機上的設定步驟:
### cloudfared 安裝
```shell
# 1. 下載 cloudflared 的最新 .deb 安裝檔
wget https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-linux-amd64.deb

# 2. 使用 dpkg 安裝
sudo dpkg -i cloudflared-linux-amd64.deb

# 3. 確認安裝成功
cloudflared --version

# 4. 刪除安裝檔
rm ./cloudflared-linux-amd64.deb
```
### cloudfare 設定
1. 登入 Cloudfare
```shell
cloudflared tunnel login
# 接下來依照上面指示打開網頁驗證
```
2. 建立 Tunnel 並綁定域名
```shell
cloudflared tunnel create bime_ba_api_tunnel
cloudflared tunnel create bime_ba_code_server_tunnel
# 要記錄指令輸入之後她回傳的一串 ID，並寫到 config.yml 裡面
```
3. 將 `.cloudfare/` 目錄下的所有 `.yml` 放到 `$HOME/.cloudflared/` 內
4. 啟動 tunnel (在 start_local_server.sh, start_local_code_server.sh 就有啟動指令)