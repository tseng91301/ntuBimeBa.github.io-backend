#!/bin/bash

SESSION_NAME="bime-ba-backend-local-server"

echo "Checking for existing tmux session named '$SESSION_NAME'..."

# 檢查 tmux 伺服器是否正在運行
if ! tmux has-session -t $SESSION_NAME 2>/dev/null; then
  echo "No existing session named '$SESSION_NAME' found."
else
  echo "Existing session '$SESSION_NAME' found. Killing it..."
  # 如果存在，則關閉該 session
  tmux kill-session -t $SESSION_NAME
  echo "Session '$SESSION_NAME' killed."
fi

echo "Adding new session $SESSION_NAME"

tmux new-session -d -s "$SESSION_NAME" -n "node.js server" "bash -c 'echo Starting node server... && source ./env/setenv.sh && cd ./local_server && node server.js'"

tmux new-window -t "$SESSION_NAME:1" -n "php admin server" "bash -c 'echo Starting php admin server... && source ./env/setenv.sh && php -t ./local_server_php -S 0.0.0.0:13800'"

tmux new-window -t "$SESSION_NAME:2" -n "Cloudfare tunnel service" "bash -c 'echo Starting Cloudfare tunnel service... && source ./env/setenv.sh && cloudflared tunnel run bime_ba_api_tunnel_main'"

# 選擇第一個 window（你可改成 0 或 1）
tmux select-window -t $SESSION_NAME:0

# 附加到這個 session
# tmux attach-session -t $SESSION_NAME
