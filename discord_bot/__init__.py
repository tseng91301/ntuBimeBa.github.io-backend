import discord
from discord.ext import commands
from typing import Callable, List, Awaitable

intents = discord.Intents.default()
intents.message_content = True  # 需要開啟此功能才能讀取訊息內容


class DiscordBot:
    def __init__(self, token: str, command_prefix='!'):
        self.token = token
        self.bot = commands.Bot(command_prefix=command_prefix, intents=intents)
        self.channel_number = 0
        self._custom_event_listeners: List[Callable[[str], Awaitable[None]]] = []
        self.is_ready = False

        @self.bot.event
        async def on_ready():
            print(f'✅ Logged in as {self.bot.user.name}')
            if self.channel_number:
                try:
                    self.channel = await self.bot.fetch_channel(self.channel_number)
                    await self.channel.send("機器人已啟動 ✅（使用 fetch_channel）")
                    self.is_ready = True
                except discord.NotFound:
                    print("❌ 找不到頻道（NotFound）")
                except discord.Forbidden:
                    print("❌ 沒有權限讀取該頻道（Forbidden）")
                except discord.HTTPException as e:
                    print(f"❌ 其他錯誤：{e}")

        # 也可以加指令
        @self.bot.command()
        async def hello(ctx):
            await ctx.send("哈囉，我是機器人！")

        @self.bot.command()
        async def test(ctx):
            if self.channel:
                await self.channel.send("這是指定頻道的測試訊息 🎯")
            else:
                await ctx.send("尚未設定頻道或頻道不存在。")

        @self.bot.command()
        async def trigger(ctx, event_name:str):
            await ctx.send(f"⚡ 觸發事件: {event_name}")
            await self._fire_custom_event(event_name)
    
    async def _fire_custom_event(self, payload: str):
        for listener in self._custom_event_listeners:
            await listener(payload)

    def on_custom_event(self, listener: Callable[[str], Awaitable[None]]):
        """主程式可以使用這個來註冊自訂事件 listener"""
        self._custom_event_listeners.append(listener)

    def set_channel_number(self, num):
        self.channel_number = num
        pass

    def send_message(self, message):
        if self.channel:
            self.bot.loop.create_task(self.channel.send(message))
        else:
            print("尚未設定頻道或頻道不存在。")

    def run(self):
        self.bot.run(self.token)

    async def start(self):
        # 非同步啟動 bot（不會阻塞 main thread）
        await self.bot.start(self.token)