#!/bin/bash

DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

while IFS='=' read -r key value || [[ -n $key ]]; do
  # 跳過空行或註解行
  if [[ -z "$key" || "$key" =~ ^# ]]; then
    continue
  fi

  # 去除前後空白
  key=$(echo "$key" | xargs)
  value=$(echo "$value" | xargs)

  # 去除前後雙引號
  value="${value%\"}"
  value="${value#\"}"

  export "$key=$value"
done < "$DIR/.env"

echo "環境變數已載入"
