import discord
import os
from discord.ext import commands, tasks

class Loader(commands.Cog):
    """"""

    def __init__(self, bot):
        self.bot = bot

    def pathWalkerLoader(self, dirName):
        listOfFiles = os.listdir(dirName)
        allFilesLoader = list()
        for entry in listOfFiles:
            strJoin = dirName + '/' + entry 
            if os.path.isdir(strJoin):
                if entry.startswith('.') or entry.startswith('__'):
                    continue
                if not strJoin:
                    continue
                else:
                    allFilesLoader = allFilesLoader + self.pathWalkerLoader(strJoin)
            else:
                if not strJoin:
                    continue
                else:
                    strJoin = strJoin.replace("/", ".")
                    allFilesLoader.append(strJoin)
        return allFilesLoader

def setup(bot):
    bot.add_cog(Loader(bot))