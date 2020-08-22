import discord
import random
import aiohttp
import os
import cmds.utils.loader
from io import BytesIO
from bot_index import startExt, presDB, freeDB, AllLoad
from discord.ext import commands, tasks


class Owner(commands.Cog):

    """Owner Only Commands"""

    def __init__(self, bot):
        self.bot = bot
        self.presCycle.start()
    
    def cog_unload(self):
        self.presCycle.cancel()

    # Do I need to explain?
    @commands.command(hidden=True, aliases=['sd', 'die'])
    @commands.is_owner()
    async def shutdown(self, ctx):
        embed = discord.Embed(title="Shutdown Initiated",
                              description="You're a monster for how many times you kill me for 'testing'", color=0xff0000)
        await ctx.send(embed=embed)
        await ctx.bot.logout()

    # load extensions on command
    @commands.command(hidden=True)
    @commands.is_owner()
    async def load(self, ctx, extension):
        try:
            load = cmds.utils.loader.Loader(self)
            loader = load.pathWalkerLoader('./cmds')
            for i in loader:
                if i.endswith('.py') and i[2:-3].casefold().split('.')[2] == extension.casefold():
                    i = i[2:-3]
                    self.bot.load_extension(i)
                    print('Reloaded: ' + i)
                else:
                    continue
            embed = discord.Embed(title='Successfully loaded '+extension+'!', color=0xffff00)
            await ctx.send(embed=embed)
            startExt.append(extension)
        except commands.ExtensionNotFound:
            embed = discord.Embed(title='Extension: "' + extension +
                                  '" was not found!', color=0xff0000)
            await ctx.send(embed=embed)
        except commands.ExtensionAlreadyLoaded:
            embed = discord.Embed(title='Extension: "' + extension +
                                  '" is already loaded!', color=0xff0000)
            await ctx.send(embed=embed)

    # unload extensions on command
    @commands.command(hidden=True)
    @commands.is_owner()
    async def unload(self, ctx, extension):
        try:
            load = cmds.utils.loader.Loader(self)
            loader = load.pathWalkerLoader('./cmds')
            for i in loader:
                if i.endswith('.py') and i[2:-3].casefold().split('.')[2] == extension.casefold():
                    i = i[2:-3]
                    self.bot.unload_extension(i)
                    print('Reloaded: ' + i)
                else:
                    continue
            embed = discord.Embed(title='Successfully un-loaded '+extension+'!', color=0xffff00)
            await ctx.send(embed=embed)
            startExt.remove(extension)
        except commands.ExtensionNotLoaded:
            embed = discord.Embed(title='Extension: "' + extension +
                                  '" is already unloaded or was not found!', color=0xff0000)
            await ctx.send(embed=embed)

    # reload extensions on command
    @commands.command(hidden=True, name='reload', description="test description")
    @commands.is_owner()
    async def reloadExt(self, ctx, extension):
        try:
            load = cmds.utils.loader.Loader(self)
            loader = load.pathWalkerLoader('./cmds')
            for i in loader:
                if i.endswith('.py') and i[2:-3].casefold().split('.')[2] == extension.casefold():
                    i = i[2:-3]
                    self.bot.reload_extension(i)
                    print('Reloaded: ' + i)
                else:
                    continue
            embed = discord.Embed(title='Successfully re-loaded '+extension+'!', color=0xffff00)
            await ctx.send(embed=embed)
        except commands.ExtensionNotFound:
            embed = discord.Embed(title='Extension: "' + extension +
                                '" was not found!', color=0xff0000)
            await ctx.send(embed=embed)
        except commands.ExtensionNotLoaded:
            embed = discord.Embed(title='Extension: "' + extension +
                                '" did not load or was not found!', color=0xff0000)
            await ctx.send(embed=embed)

    @commands.command(hidden=True)
    async def allExt(self, ctx):
        load = cmds.utils.loader.Loader(self)
        loader = load.pathWalkerLoader('./cmds')
        for i in loader:
            if i.endswith('.py'):
                i = i[2:-3]
                self.bot.reload_extension(i)
                print('Reloaded: ' + i)
            else:
                continue
        

    @commands.command(hidden=True, aliases=["chpres"], description="0=Playing\n1=Streaming\n2=Listening\n3=Watching\ndnd = do not disturb\nonline=why are you asking\nidle = orange/afk")
    @commands.is_owner()
    async def changePresence(self, ctx, status: str, types: int, *, name: str):
        await self.bot.change_presence(status=status, activity=discord.Activity(type=types, name=name))

    @commands.command(hidden=True)
    @commands.is_owner()
    async def rPresSet(self, ctx, status: str, types: int, *, name: str):
        allAsList = [status, types, name]
        author = ctx.author.id
        allList = {
            "owner": author
        }
        presDB.find_one_and_update(allList, {'$push': {"rPres": allAsList}})
        currentDB = presDB.find_one(allList)
        await ctx.send("success", delete_after=5)
    

    @tasks.loop(minutes=5.0)
    async def presCycle(self):
        findMe = {
            "owner": self.bot.owner_id
        }
        presList = presDB.find_one(findMe)
        if presList != None:
            randomPres = random.choice(presList['rPres'])
            status = randomPres[0]
            types = randomPres[1]
            name = randomPres[2]
            await self.bot.change_presence(status=status, activity=discord.Activity(type=types, name=name))

    @presCycle.before_loop
    async def before_presCycle(self):
        await self.bot.wait_until_ready()

    @commands.command(hidden=True)
    @commands.is_owner()
    async def stopPres(self, ctx):
        self.presCycle.cancel()
        embed = discord.Embed(description="Stopped Presence Cycle", color=0x00aa00)
        await ctx.send(embed=embed)
    
    @commands.command(hidden=True)
    @commands.is_owner()
    async def startPres(self, ctx):
        self.presCycle.start()
        embed = discord.Embed(description="Started Presence Cycle", color=0x00aa00)
        await ctx.send(embed=embed)

    @commands.command(hidden=True)
    @commands.is_owner()
    async def broadcastGame(self, ctx, *, message):
        ayy = list(freeDB.find())
        for a in ayy:
            newGuild = self.bot.get_guild(a["guild_id"])
            new_channel = newGuild.get_channel(a["channel_id"])
            await new_channel.send("{}\n<@&{}>".format(message, str(a['role_ID'])))

    @commands.command(hidden=True)
    @commands.is_owner()
    async def changeAvatar(self, ctx, *, message):
        async with aiohttp.ClientSession() as session:
            async with session.get(message) as resp:
                if resp.status != 200:
                    return await ctx.channel.send('Could not download file...')
                toBytes = await resp.read()
                await self.bot.user.edit(avatar=toBytes)

def setup(bot):
    bot.add_cog(Owner(bot))
