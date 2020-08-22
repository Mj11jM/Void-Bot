import discord
from discord.ext import commands


class NSFW(commands.Cog):

    """Nothing here"""

    def __init__(self, bot):
        self.bot = bot

    


def setup(bot):
    bot.add_cog(NSFW(bot))
