# 伺服器從 0 到開始運行的方法

## 構建伺服器
* 選擇你的伺服主機類型:
    1. 安裝 Linux 發行版的系統 [server-build-normal](./server-build-normal.md) (Recommended)
    2. 安裝 Windows 的系統 (使用 Windows Subsystem for Linux) [server-build-wsl](./server-build-wsl.md)
    3. 使用 ARM 系統架構的系統或手機 [server-build-mobile](./server-build-mobile.md) (不推薦)

## 創建伺服器基本資料目錄
```shell
mkdir -p bime_ba_webpage bime_ba_webpage/frontend bime_ba_webpage/backend
```

## 安裝伺服器所需套件
前往 [package-install](./package-install.md) 查看伺服器套件安裝說明

## 配置 mysql 資料庫
前往 [mysql-init](./mysql-init.md) 查看資料庫設定說明

## Git 儲存庫同步
### 設定連接 Github 的 ssh-key
```shell
ssh-keygen -t rsa
```
* 接著在儲存路徑輸入步驟中輸入
```shell
/home/bime/.ssh/id_rsa_github_bimestudy2024
```
### 設定上面 ssh-key 管理的主機
```shell
vim ~/.ssh/config
```
* 在 Vim 編輯器中輸入
```shell
Host github-bimestudy2024
  HostName github.com
  User git
  IdentityFile ~/.ssh/id_rsa_github_bimestudy2024
  IdentitiesOnly yes
```
### 將 github 上面的專案導入
```shell
cd ~/bime_ba_webpage/backend
git init
git remote add origin git@github-bimestudy2024:ntuBimeBa/ntuBimeBa.github.io-backend.git
git pull
git checkout main
cd ../frontend
git init
git remote add origin git@github-bimestudy2024:ntuBimeBa/ntuBimeBa.github.io.git
git pull
git checkout main
```

### 設定 Cloudfare 以及通訊轉發
前往 [cloudfare-config](./.cloudfared/) 查看伺服器套件安裝說明

### 開啟伺服器後端的部分