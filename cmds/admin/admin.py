import asyncio
import discord
from helpers import confirmationMenu
from bot_index import startExt
from discord.ext import commands

confirm_men = confirmationMenu.ConfirmationMenus

class Admin(commands.Cog):

    """Admin-esque commands."""

    def __init__(self, bot):
        self.bot = bot

    @commands.command()
    @commands.has_guild_permissions(administrator=True)
    async def leave(self, ctx):
        """The bot will leave the server"""
        confirm = await confirm_men('Are you sure you want Void Bot to leave the server?').prompt(ctx)
        if confirm :
            await asyncio.sleep(2)
            await ctx.message.guild.leave()
    
    @commands.command()
    @commands.has_guild_permissions(ban_members=True)
    async def ban(self, ctx, user: discord.User, delete_days: int, *, reason = None):
        if delete_days > 7:
            embed = discord.Embed(title="Too Many Days",description=f"You can only choose between 0 and 7 days to delete.", color = 0xaa0000)
            await ctx.send(embed=embed)
            return
        confirm = await confirm_men(f'Are you sure you want to ban {user} and delete {delete_days} days of their messages?').prompt(ctx)
        if confirm:
            await ctx.guild.ban(user, reason = reason, delete_message_days=delete_days)
            embed = discord.Embed(title="Successfully Banned User",description=f"{user} has been banned from this server.", color = 0x00aa00)
            await ctx.send(embed=embed)

    
    @commands.command()
    @commands.has_guild_permissions(ban_members=True)
    async def unban(self, ctx, user: discord.User, *, reason = None):
        unbanned = await ctx.guild.unban(user, reason=reason)
        embed = discord.Embed(title="Successfully Un-Banned User",description=f"{user} has been un-banned from this server.", color = 0x00aa00)
        await ctx.send(embed=embed)



def setup(bot):
    bot.add_cog(Admin(bot))


