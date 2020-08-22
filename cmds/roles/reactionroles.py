import asyncio
import discord
import time
from bot_index import reroDB
from discord.ext import commands
from emoji_data import EmojiSequence
from itertools import zip_longest


class ReactionRoles(commands.Cog):

    """Setup Reaction Roles"""

    def __init__(self, bot):
        self.bot = bot


    @commands.command(aliases=['rero'], description="")
    @commands.has_guild_permissions(administrator=True)
    @commands.bot_has_guild_permissions(manage_roles=True)
    async def reactRoles(self, ctx, *, text):
        """Allows members to get roles with reactions"""
        messages = await ctx.channel.history(limit=2).flatten()
        msgStrip = str(messages[1]).split()
        lastMsgID = msgStrip[1].strip('id=')
        lastMsg = await ctx.channel.fetch_message(lastMsgID)
        msg = text.strip().split(',')
        gID = str(ctx.message.guild.id)
        gmsg = list(self.grouper(2, msg))
        roles = []
        allRoles = ctx.guild.roles
        roleIDs = []
        channelID = str(ctx.channel.id)
        for x in gmsg:
            a = str(x[0].strip())
            b = str(x[1].strip())
            if a[0:].startswith('<:') | a[0:].startswith('<a:'):
                roles.append(a) #emote first
                roles.append(b) #role second
                i = discord.utils.get(allRoles, name=b)
                if i != None:
                    rID = i.id
                    roles.append(rID) #role id
                    await asyncio.sleep(0.5)
            elif a[0:] in EmojiSequence:
                roles.append(a) #emote first
                roles.append(b) #role second
                i = discord.utils.get(allRoles, name=b)
                if i != None:
                    rID = i.id
                    roles.append(rID) #role id   
                    await asyncio.sleep(0.5)             
            else:                    
                roles.append(b) #emote first
                roles.append(a) #role second
                i = discord.utils.get(allRoles, name=a)
                if i != None:
                    rID = i.id
                    roles.append(rID) #role id 
                    await asyncio.sleep(0.5)               
        ggmsg = list(self.grouper(3, roles))
        for y in ggmsg:
            await lastMsg.add_reaction(y[0][0:])
            await asyncio.sleep(1)
        tests = {
            'guild_id': gID,
            'roles': ggmsg,
            'message_id': lastMsgID,
            'channel_id': channelID
        }
        reroDB.insert_one(tests)
        embed = discord.Embed(title="Completed", description="This message will delete itself after 20 seconds")
        await ctx.send(embed=embed, delete_after=20)
    

    def grouper(self, n, iterable):
        return zip_longest(*[iter(iterable)]*n, fillvalue=None)
    

    @reactRoles.error
    async def reroError(self, ctx, error):
        if isinstance(error, commands.BotMissingPermissions):
            embed = discord.Embed(title="Permissions Error", description="Void Bot does not have permissions to use this command", color=0xff0000)
            await ctx.send(embed=embed)
        elif isinstance(error, commands.MissingPermissions):
            embed = discord.Embed(title="Permissions Error", description="You do not have permissions to use this command. You need to be administrator to use it", color=0xff0000)
            await ctx.send(embed=embed)


    @commands.command(aliases=['rerorm', 'rmrero'], description="")
    @commands.has_guild_permissions(administrator=True)
    @commands.bot_has_guild_permissions(manage_roles=True)
    async def reactRolesRemove(self, ctx, msgID):
        """Removes Reaction Roles"""
        gID = ctx.guild.id
        search = {
            'guild_id': str(gID),
            'message_id': msgID
        }
        findEntry = reroDB.find_one(search)
        if findEntry != None:
            reroDB.delete_one(search)
            embed=discord.Embed(title="Reaction Role Deleted", description="Successfully deleted reaction role activity on: "+ str(msgID))
            await ctx.send(embed=embed)
        else:
            embed=discord.Embed(title="Not Found", description="No reaction role activity found on message ID: "+ str(msgID) +" in guild ID: "+ str(gID))
            await ctx.send(embed=embed)


    @reactRolesRemove.error
    async def reroRMError(self, ctx, error):
        if isinstance(error, commands.BotMissingPermissions):
            embed = discord.Embed(title="Permissions Error", description="Void Bot does not have permissions to use this command", color=0xff0000)
            await ctx.send(embed=embed)
        elif isinstance(error, commands.MissingPermissions):
            embed = discord.Embed(title="Permissions Error", description="You do not have permissions to use this command. You need to be administrator to use it", color=0xff0000)
            await ctx.send(embed=embed)


    @commands.command(aliases=['lirero', 'reroli'], description="")
    async def listReactRoles(self, ctx):
        """Lists Reaction Roles in current channel"""
        gID = str(ctx.guild.id)
        chanID = str(ctx.channel.id)
        search = {
            'guild_id': gID,
            'channel_id': chanID
        }
        links = ''
        findrero = reroDB.find(search)
        if findrero != None:   
            msgID = [b['message_id'] for b in findrero]
            for ids in msgID: 
                links += str('<https://discordapp.com/channels/'+gID+'/'+chanID+'/'+ids+'>\n')
            if not links:
                embed = discord.Embed(title="Not Found", description="No reaction roles found for current channel. This command is currently limited to one channel only", color=0xff0000)
                await ctx.send(embed=embed)
            if links:
                embed = discord.Embed(title="Channel Reaction Roles", description=links, color=0x00ff00)
                await ctx.send(embed=embed)
        else:
            embed = discord.Embed(title="Not Found", description="No reaction roles found for current channel. This command is currently limited to one channel only", color=0xff0000)
            await ctx.send(embed=embed)


    


def setup(bot):
    bot.add_cog(ReactionRoles(bot))
