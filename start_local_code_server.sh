#!/bin/bash

SESSION_NAME="bime-ba-local-code-server"

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

# 建立新的 tmux session，不自動附加
tmux new-session -d -s "$SESSION_NAME" -n "code-server" "bash -c 'echo Starting code-server... && source ./env/setenv.sh && source ./venv/bin/activate && code-server --bind-addr 0.0.0.0:13822 ~/bime_ba_webpage'"

# 新開一個 window 執行第二個指令
tmux new-window -t "$SESSION_NAME:1" -n "Cloudfare tunnel service" "bash -c 'echo Starting Cloudfare tunnel service... && source ./env/setenv.sh && cloudflared tunnel --config ~/.cloudflared/config-code-server.yml run'"

# 選擇第一個 window（你可改成 0 或 1）
tmux select-window -t $SESSION_NAME:0

# 附加到這個 session
# tmux attach-session -t $SESSION_NAME
