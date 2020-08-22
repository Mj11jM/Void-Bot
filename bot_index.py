import discord
import logging
import os
import json
from pymongo import MongoClient
from discord.ext import commands

# setup mongoDB
localMongo = MongoClient()
db = localMongo.voidbot
prefixDB = db.guildPrefixes
selfRoleDB = db.selfRoles
reroDB = db.reactionroles
logDB = db.logging
greetDB = db.greeting
streamDB = db.streamRoles
presDB = db.rotatePresence
freeDB = db.freeGames
giveDB = db.giveaways
gvRolesDB = db.gvRoles
delegationDB = db.delegation

# logger
logger = logging.getLogger('discord')
logger.setLevel(logging.DEBUG)
handler = logging.FileHandler(filename='discord.log', encoding='utf-8', mode='w')
handler.setFormatter(logging.Formatter('%(asctime)s:%(levelname)s:%(name)s: %(message)s'))
logger.addHandler(handler)


# read discord token
def read_dToke():
    with open("dToke.config", "r") as f:
        lines = f.readlines()
        return lines[0].strip()


def prefixes(client, message):
    if not message.guild:
        return '-'
    else:
        gID = str(message.guild.id)
        prefixResult = prefixDB.find_one({"guild_id": gID})
        if prefixResult is not None:
            try:
                result_found = prefixResult['prefix']
                return result_found
            except KeyError as err:
                print("Error for result: " + prefixResult + '--' + err)
        else:
            print('Nothing Found')


disToken = read_dToke()
bot = commands.Bot(command_prefix=prefixes, case_insensitive=True, max_messages=10000)
startExt = []
bot.remove_command('help')


@bot.event
async def on_ready():
    print('Logged in as {0.user}'.format(bot))


# initializes the extensions to start
class AllLoad:
    def __init__(self):
        print('AllLoad Loaded')

    def loadAll(self):
        allFiles = self.pathWalker('./cmds')
        for mods in allFiles:
            if mods.endswith('.py'):
                mods = mods[2:-3].replace("/", ".")
                bot.load_extension(mods)
                startExt.append(mods.casefold().split('.')[2])
                print('Loaded ' + mods)
                continue
            else:
                continue

    def pathWalker(self, dirName):
        listOfFile = os.listdir(dirName)
        allFiles = list()
        for entry in listOfFile:
            strJoin = dirName + '/' + entry
            if os.path.isdir(strJoin):
                if entry.startswith('.') or entry.startswith('__'):
                    continue
                if not strJoin:
                    continue
                else:
                    allFiles = allFiles + self.pathWalker(strJoin)
            else:
                if not strJoin:
                    continue
                else:
                    allFiles.append(strJoin)
        return allFiles


loads = AllLoad()
loads.loadAll()

# Start the bot
bot.run(disToken)
