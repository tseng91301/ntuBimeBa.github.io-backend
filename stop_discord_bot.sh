SESSION_NAME="bime-ba-discord-bot"

# 檢查 tmux 伺服器是否正在運行
if ! tmux has-session -t $SESSION_NAME 2>/dev/null; then
  echo "Server hasn't start."
else
  echo "Server existing. Killing it..."
  # 如果存在，則關閉該 session
  tmux kill-session -t $SESSION_NAME
fi