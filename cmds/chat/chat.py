import discord
import time
import asyncio
from discord.ext import commands


class Chat(commands.Cog):

    """Chat commands"""

    def __init__(self, bot):
        self.bot = bot

    @commands.command(aliases=["clear", "purge"], description="Deletes one message by default, amount deleted is variable\n \nREQUIRES MANAGE_MESSAGES PERMISSION")
    @commands.has_permissions(manage_messages=True)
    async def prune(self, ctx, amount=1):
        """Delete up to 50 messages at once in channel"""
        if amount >= 50:
            if await self.bot.is_owner(ctx.author):
                await ctx.channel.purge(limit=(amount + 1))
            else:
                embed = discord.Embed(
                    title="Number Too Large", description="Due to rate limitations, this command is limited to 50 at a time", color=0xff0000)
                await ctx.send(embed=embed)
        else:
            await ctx.channel.purge(limit=(amount + 1))
            await asyncio.sleep(0.5)
            if amount == 1:
                embed = discord.Embed(title="Messages Cleared",
                                      description='Successfully removed ' + str(amount) + ' message', color=0x00ff00)
            else:
                embed = discord.Embed(title="Messages Cleared",
                                      description='Successfully removed ' + str(amount) + ' messages', color=0x00ff00)
            await ctx.send(embed=embed, delete_after=5)


def setup(bot):
    bot.add_cog(Chat(bot))
