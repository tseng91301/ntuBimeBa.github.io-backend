import asyncio
import time
from discord_bot import DiscordBot
import os
import redis
import json
import threading

r = redis.Redis(host='localhost', port=6379, db=0)

def redis_event_listener(bot: DiscordBot):
    pubsub = r.pubsub()
    pubsub.subscribe("newReservation")
    print("🔔 Redis listener started...")

    while not bot.is_ready:
        time.sleep(0.2)
    
    for message in pubsub.listen():
        if message['type'] == 'message':
            try:
                payload = json.loads(message['data'])
                id = payload["id"]
                name = payload["name"]
                date = payload["date"]
                brief = payload["message"]
                contact = payload["contact"]
                other = payload["other"]
                bot.send_message(f"You got reservation, \n  id: {id}\n  Who: {name}\n  date: {date}\n  message: {brief}\n  contact method: {contact}\n  other messages: {other}\n")
            except Exception as e:
                print(f"[ERROR] Redis message parse failed: {e}")
    print("end")

if __name__ == "__main__":
    bot = DiscordBot(os.environ['SERVER_MANAGE_DISCORD_BOT_TOKEN'])
    bot.set_channel_number(int(os.environ['SERVER_MANAGE_DISCORD_BOT_CHANNEL_NUMBER']))

    # 啟動 Redis 監聽器（在 background thread）
    threading.Thread(target=redis_event_listener, args=(bot,), daemon=True).start()

    # 啟動 Discord Bot（會阻塞）
    bot.run()