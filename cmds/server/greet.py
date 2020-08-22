import discord
from bot_index import greetDB
from discord.ext import commands


class Greet(commands.Cog):

    """Greet new members with a custom message!"""

    def __init__(self, bot):
        self.bot = bot

    @commands.command(description="")
    @commands.has_guild_permissions(administrator=True)
    async def greeting(self, ctx, *, greeting):
        """Set a greeting"""
        chanID = str(ctx.channel.id)
        guildID = str(ctx.guild.id)
        inputs = {
            "guild_id": guildID,
            "channel_id": chanID, 
            "greeting": greeting
        }
        greetDB.insert_one(inputs)
        embed = discord.Embed(title="Message Set", description="Greet message has been set and will greet new users in this channel.\n Please note greet is limited to one channel per server", color=0x00ff00)
        await ctx.send(embed=embed, delete_after=10)


    @commands.command(aliases=['rmgreet', 'greetrm'], description="")
    @commands.has_guild_permissions(administrator=True)
    async def removeGreeting(self, ctx):
        """Remove the greeting"""
        gID = str(ctx.guild.id)
        delete = {
            "guild_id": gID
        }
        greetDB.delete_one(delete)
        embed = discord.Embed(title="Greet Deleted", description="Void Bot will no longer greet new members in this server", color=0x00ff00)
        await ctx.send(embed=embed)


def setup(bot):
    bot.add_cog(Greet(bot))