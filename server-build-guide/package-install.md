# 伺服器套件安裝說明

## 基本套件安裝:
```shell
sudo apt install vim git curl tmux net-tools -y
```

## 下載伺服器所需套件:
### Python:
```shell
sudo apt install python3 python3-pip python3-venv -y
```
### node.js
```shell
# Download and install nvm:
curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.40.3/install.sh | bash
# in lieu of restarting the shell
\. "$HOME/.nvm/nvm.sh"
# Download and install Node.js:
nvm install 22
# Verify the Node.js version:
node -v # Should print "v22.17.0".
nvm current # Should print "v22.17.0".
# Verify npm version:
npm -v # Should print "10.9.2".
```
### php
```shell
sudo apt install -y php php-cli php-mbstring php-curl php-xml php-mysql
```

## mysql 資料庫
### ARM 系統無法使用 `mysql-server` 套件，使用 `mariadb-server` 套件
```shell
sudo apt install mariadb-server -y
```
### 其他系統則直接使用 `mysql-server`
```shell
sudo apt install mysql-server -y
```