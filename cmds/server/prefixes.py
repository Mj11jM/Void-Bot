import discord
import json
import pymongo.collection
from bot_index import prefixDB
from discord.ext import commands


class Prefixes(commands.Cog):

    """Change the custom prefix for your guild"""

    def __init__(self, bot):
        self.bot = bot
    
    @commands.Cog.listener()
    async def on_guild_join(self, guild):
        prefix = {
            "guild_id": str(guild.id),
            "prefix": '-'
        }
        prefixDB.insert_one(prefix) 

    @commands.Cog.listener()
    async def on_guild_remove(self, guild):
        prefixDB.delete_one({'guild_id': str(guild.id)})

    @commands.command(aliases=['chpref'], description="")
    @commands.has_permissions()
    async def changeprefix(self, ctx, pref):
        """Set a custom Prefix"""
        prefixDB.find_one_and_update({"guild_id": str(ctx.guild.id)}, {'$set': {"prefix": str(pref)}})
        embed = discord.Embed(title='Prefix Changed',
                              description='Prefix changed to '+pref, color=0x00ff00)
        await ctx.send(embed=embed)


def setup(bot):
    bot.add_cog(Prefixes(bot))
