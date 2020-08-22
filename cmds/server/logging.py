import discord
import asyncio
import time
from helpers import confirmationMenu
from discord.ext import commands
from bot_index import logDB, greetDB, streamDB, prefixDB

client = discord.Client()
confirm_menu = confirmationMenu.ConfirmationMenus

class Logging(commands.Cog):

    """Setup Server Logging"""

    def __init__(self, bot):
        self.bot = bot


    @commands.command(aliases=['setlog', 'logset'], description="")
    @commands.has_guild_permissions(administrator=True)
    async def serLog(self, ctx):
        """Sets a channel to log server events"""
        gID = str(ctx.guild.id)
        chanID = str(ctx.channel.id)
        logSet = {
            "guild_id": gID,
            "channel_id": chanID,
            "user_events": True,
            "message_delete": True,
            "message_edit": True,
            "role_change": True,
            "member_join": True,
            "member_leave": True, 
            "avatar": False,
            "bans": True,
            "channel_add_remove": True,
            "channel_edit": True,
            "role_edit": True,
            "role_add_remove": True,
            "ignored_channels": []
        }
        logDB.insert_one(logSet)
        embed = discord.Embed(title="Success!", description="This channel will now be used for logging!", color=0x00ff00)
        await ctx.send(embed=embed)
    

    @commands.command(aliases=['rmlog', 'logrm'], description="")
    @commands.has_guild_permissions(administrator=True)
    async def remLog(self, ctx):
        """Removes logging from the server"""
        gID = ctx.guild.id
        find = {
            "guild_id": str(gID)
        }
        confirm = await confirm_menu('Are you sure you want to stop logging?\nNote: This will also delete all settings for logging').prompt(ctx)
        if confirm :
            logDB.delete_one(find)
            embed = discord.Embed(title="Logging Stopped", description="Logging has been stopped for this server", color=0x00ff00)
            await ctx.send(embed=embed)
        else:
            embed = discord.Embed(title="Operation Cancelled", description="You have cancelled the removal of logging from this server", color=0xff0000)
            await ctx.send(embed=embed, delete_after=5)


    @commands.command(aliases=['editlog', 'logpart', 'logedit'], description="")
    @commands.has_guild_permissions(administrator=True)
    async def chLog(self, ctx, state, *, module):
        """Enable or Disable log parts"""
        gID = ctx.guild.id
        toggle = bool
        setMod = str(module).strip().replace(" ", "_")
        findMod = logDB.find_one({
            "guild_id": str(gID)
        })
        checkMod = findMod[setMod]
        if checkMod != None:
            if str(state).casefold() == "disable":
                toggle = False
                logDB.find_one_and_update({"guild_id": str(gID)}, {'$set': {setMod: toggle}})
                embed = discord.Embed(title="Log Part Disabled", description="Disabled Log Part" + setMod, color=0x00ff00)
                await ctx.send(embed=embed)
            elif str(state).casefold() == "enable":
                toggle = True
                logDB.find_one_and_update({"guild_id": str(gID)}, {'$set': {setMod: toggle}})
                embed = discord.Embed(title="Log Part Enabled", description="Enabled Log Part" + setMod, color=0x00ff00)
                await ctx.send(embed=embed)

    @chLog.error
    async def chLogErrors(self, ctx, error):
        if isinstance(error, KeyError):
            embed = discord.Embed(title="Log Part Not Found", description="That part was not found, please make sure you have spelled it correctly and that you have fully typed out the part", color=0xff0000)
            await ctx.send(embed=embed)
        else:
            embed = discord.Embed(title="Log Part Not Found", description="That part was not found, please make sure you have spelled it correctly and that you have fully typed out the part", color=0xff0000)
            await ctx.send(embed=embed)
        

    @commands.command(description="")
    @commands.has_guild_permissions(administrator=True)
    async def logIgnore(self, ctx):
        """Tells bot to ignore channel for logging"""
        chanID = ctx.channel.id
        gID = ctx.guild.id
        findLog = logDB.find_one({
            "guild_id": str(gID)
        })
        if findLog != None:
            if chanID in findLog['ignored_channels']:
                embed=discord.Embed(title="Channel Already Ignored", description="This channel is already ignored by the bot for logging", color=0xff0000)
                await ctx.send(embed=embed)
            else:
                logDB.find_one_and_update({"guild_id": str(gID)}, {'$push': {"ignored_channels": chanID}})
                embed = discord.Embed(title="Logging Ignoring Channel", description="Logs will ignore this channel", color=0x00ff00)
                await ctx.send(embed=embed)
        else:
            embed = discord.Embed(title="Logs Not Found", description="No logging channels have been found on this server", color=0xff0000)
            await ctx.send(embed=embed)


    @commands.command(description="")
    @commands.has_guild_permissions(administrator=True)
    async def logWatch(self, ctx):
        """Tells bot to watch channel for logging"""
        chanID = ctx.channel.id
        gID = ctx.guild.id
        findLog = logDB.find_one({
            "guild_id": str(gID)
        })
        if findLog != None:
            if chanID not in findLog['ignored_channels']:
                embed=discord.Embed(title="Channel Already Watched", description="This channel is already watched by the bot for logging", color=0xff0000)
                await ctx.send(embed=embed)
            else:
                logDB.find_one_and_update({"guild_id": str(gID)}, {'$pull': {"ignored_channels": chanID}})
                embed = discord.Embed(title="Logging Watching Channel", description="Logs will watch this channel again", color=0x00ff00)
                await ctx.send(embed=embed)
        else:
            embed = discord.Embed(title="Logs Not Found", description="No logging channels have been found on this server", color=0xff0000)
            await ctx.send(embed=embed)


def setup(bot):
    bot.add_cog(Logging(bot))
