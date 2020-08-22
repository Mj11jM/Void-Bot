import discord
from discord.ext import menus

class ConfirmationMenus(menus.Menu):
    def __init__(self, msg):
        super().__init__(timeout=25.0, delete_message_after=True)
        self.msg = msg
        self.result = None
    
    async def send_initial_message(self, ctx, channel):
        embed = discord.Embed(title=self.msg, color=0xffff00)
        return await channel.send(embed=embed)


    @menus.button('✅')
    async def confirm(self, payload):
        self.result = True
        self.stop()

    @menus.button('❌')
    async def deny(self, payload):
        self.result = False
        self.stop()

    async def prompt(self, ctx):
        await self.start(ctx, wait=True)
        return self.result

