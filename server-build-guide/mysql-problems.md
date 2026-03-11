# mysql 遇到錯誤的解決方法
## 使用測試模式登入，看能不能進到資料庫
```shell
# 直接測試
sudo mysqld --console
# 顯示錯誤訊息
sudo mysqld --console --log-error-verbosity=3
# 最低限制啟動（跳過權限）
sudo mysqld --skip-grant-tables --skip-networking --console
```

## android 系統裝的嵌入型 linux 無法使用 tcp server (開 port)，所以要使用 socket
* 設定如下:
```shell
sudo vim /etc/mysql/mariadb.conf.d/50-server.cnf
# 在 [mysqld] 區段加入以下代碼
skip-networking
socket = /var/run/mysqld/mysqld.sock
##### end Vim

# 確保目錄存在
sudo mkdir -p /var/run/mysqld
sudo chown -R mysql:mysql /var/run/mysqld

sudo service mysql restart
```