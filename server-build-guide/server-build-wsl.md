# 如何在 Windows WSL 上面構建伺服器
## 創建一個新的 wsl 容器
```shell
# 使用 Ubuntu-24.04 當作 Linux 發行版
wsl --install -d Ubuntu-24.04 --name bime-server-alternative
```
## 打開 wsl 容器
```shell
wsl -d bime-server-alternative
```
### Optional
* 將此容器設為預設容器
```shell
wsl --set-default bime-server-alternative
```
* 列出所有安裝在電腦上的 wsl 容器
```shell
wsl --list --verbose
```
## 設定 wsl 到 windows 系統的端口映射
### 設置定義
假設我要打開 ssh port 是 port 22 對應到 windows 主機上的 port 12022
1. 在 wsl 中執行
```shell
ifconfig
```
得到 WSL 獲得的對外 IP 地址，並複製起來
2. 在 Windows Powershell (Administrator) 中執行
* 設定端口映射 12022, 13800, 14230
```shell
netsh interface portproxy add v4tov4 listenport=12022 listenaddress=0.0.0.0 connectport=22 connectaddress=<Your WSL IP Addr>
```
* 打開防火牆
```shell
netsh advfirewall firewall add rule name="WSL SSH" dir=in action=allow protocol=TCP localport=2222
```
3. 測試 server 是否成功連接
**這個轉發會在重開機後消失，可以設定在開機自動執行的腳本中**
*如果需要開其他 port ，照上面的方法設定即可*

## ---此部分設定完成---