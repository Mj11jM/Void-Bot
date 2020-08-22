from pymongo import MongoClient
from bot_index import logDB, greetDB, streamDB, prefixDB

class DBLookups():

    def __init__(self):
        super().__init__()

    async def loggerLook(self, guildID):
        search = {
            "guild_id": str(guildID)
        }
        findLog = logDB.find_one(search)
        if findLog != None:
            return findLog
        else:
            return None

    async def greetLook(self, guildID):
        search = {
            "guild_id": str(guildID)
        }
        findGreet = greetDB.find_one(search)
        if findGreet != None:
            return findGreet
        else:
            return None

    async def streamLook(self, guildID):
        search = {
            "guild_id": str(guildID)
        }
        findStream = streamDB.find_one(search)
        if findStream != None:
            return findStream
        else:
            return None
    
    async def prefixLook(self, guildID):
        search = {
            "guild_id": str(guildID)
        }
        findPre = prefixDB.find_one(search)
        if findPre != None:
            return findPre
        else:
            return None
    