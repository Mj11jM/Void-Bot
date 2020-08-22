import discord
import aiohttp
import asyncio
import re
import json
import random
import typing
from datetime import datetime, timedelta
from discord.ext import commands, tasks
from bot_index import freeDB, giveDB, gvRolesDB


class Misc(commands.Cog):

    """Misc Commands"""

    def __init__(self, bot):
        self.bot = bot
        self.giveLoop.start()

    def cog_unload(self):
        self.giveLoop.cancel()

    @commands.command()
    async def ping(self, ctx):
        """Rounded general ping"""
        embed = discord.Embed(title="",
                              description=f'Returned in {round(self.bot.latency * 1000)}ms', color=0x00ff00)
        await ctx.send(embed=embed)

    @commands.command()
    async def avatar(self, ctx, *user: discord.User):
        """Shows your, or another user's avatar"""
        if not user:
            ctxUserAvatar = ctx.author.avatar_url
            embed = discord.Embed(title="Here is your avatar!", color=0x00ff00)
            embed.set_image(url=ctxUserAvatar)
            await ctx.send(embed=embed)
        else:
            otherUser = user[0]
            otherUserAvatar = otherUser.avatar_url
            otherUserName = otherUser.name
            embed = discord.Embed(title="Here is " + otherUserName + "'s avatar!", color=0x00ff00)
            embed.set_image(url=otherUserAvatar)
            await ctx.send(embed=embed)


    @commands.command(aliases=['profile', 'uinfo'])
    async def userInfo(self, ctx, *member: discord.Member):
        """Shows discord information"""
        if not member:
            author = ctx.author
            authorID = ctx.author.id
            authorAvatar = ctx.author.avatar_url
            selfMember = ctx.guild.get_member(authorID)
            selfMemberName = selfMember.name
            selfMemberID = selfMember.id
            joinedAt = selfMember.joined_at
            nickname = selfMember.nick
            createdAt = selfMember.created_at
            memberRoles = selfMember.roles[1:]
            roleString = ""
            for d in memberRoles:
                roleString += str(d) + '\n' 
            embed = discord.Embed(title="Here is your information!", color=0x00ff00)
            embed.add_field(name="Real Username:", value=str(selfMemberName))
            embed.add_field(name="Current Nickname:", value=str(nickname), inline = True)
            embed.add_field(name="Roles", value="You have " + str(len(memberRoles)) + " roles. They are: \n" + roleString, inline = False)
            embed.add_field(name="Joined Guild On:", value=str(joinedAt)[:-7], inline = True)
            embed.add_field(name="Created Account On:", value=str(createdAt)[:-7], inline=True)
            if selfMember.premium_since != None:
                premiumSince = selfMember.premium_since
                embed.add_field(name="Nitro Booster Since", value=str(premiumSince)[:-7])
            embed.set_thumbnail(url=authorAvatar)
            await ctx.send(embed=embed)
        else:
            otherMember = member[0]
            otherMemberName = otherMember.name
            otherMemberID = otherMember.id
            joinedAt = otherMember.joined_at
            nickname = otherMember.nick
            createdAt = otherMember.created_at
            otherMemberRoles = otherMember.roles[1:]
            newRoleString = ""
            otherMemberAvatar = otherMember.avatar_url
            for d in otherMemberRoles:
                newRoleString += str(d) + '\n' 
            embed = discord.Embed(title="", description="", color=0x00ff00)
            embed = discord.Embed(title="Here is your information!", color=0x00ff00)
            embed.add_field(name="Real Username:", value=str(otherMemberName))
            embed.add_field(name="Current Nickname:", value=str(nickname), inline = True)
            embed.add_field(name="Roles", value="They have " + str(len(otherMemberRoles)) + " roles. They are: \n" + newRoleString, inline = False)
            embed.add_field(name="Joined Guild On:", value=str(joinedAt)[:-7], inline = True)
            embed.add_field(name="Created Account On:", value=str(createdAt)[:-7], inline=True)
            if otherMember.premium_since !=None:
                premiumSince = otherMember.premium_since
                embed.add_field(name="Nitro Booster Since", value=str(premiumSince)[:-7])
            embed.set_thumbnail(url=otherMemberAvatar)
            await ctx.send(embed=embed)

    @commands.command(description="")
    @commands.cooldown(1, 10, commands.BucketType.guild)
    async def meow(self, ctx, *amount: int):
        """Random Cats!"""
        if not amount:
            async with aiohttp.ClientSession() as session:
                async with session.get('http://aws.random.cat/meow') as r:
                    if r.status == 200:
                        js = await r.json()
                        embed = discord.Embed(color=0x000000)
                        embed.set_image(url=js['file'])
                        await ctx.send(embed=embed)
        else:
            number = amount[0]
            if number > 10 or number < 0:
                embed = discord.Embed(title="Too Many or Few Images", description="This command is limited to 10 meows max, and must also be a positive number.", color=0xff0000)
                await ctx.send(embed=embed)
            else:
                count = 0
                while (count < number):
                    async with aiohttp.ClientSession() as session:
                        async with session.get('http://aws.random.cat/meow') as r:
                            if r.status == 200:
                                js = await r.json()
                                embed = discord.Embed(color=0x000000)
                                embed.set_image(url=js['file'])
                                await ctx.send(embed=embed)
                                count = count + 1
                                await asyncio.sleep(0.75)
    @meow.error
    async def meowError(self, ctx, error):
        if isinstance(error, commands.CommandOnCooldown):
            embed = discord.Embed(title="Command On Cooldown", description=str(error), color = 0xff0000)
            await ctx.send(embed=embed)

    @commands.group(aliases=['sg'])
    @commands.has_guild_permissions(administrator=True)
    async def steamGames(self, ctx):
        """Sub or unsub to/from Steam game pings"""
        if ctx.invoked_subcommand is None:
            await ctx.send("Invalid command passed")
    
    @steamGames.command(description="")
    async def sub(self, ctx, *, role: discord.Role):
        """Subscribe to pings for free Steam games"""
        gID = ctx.guild.id
        roleID = role.id
        chanID = ctx.channel.id
        insertion = {
            "guild_id": gID,
            "channel_id": chanID,
            "role_ID": roleID
        }
        insert = freeDB.insert_one(insertion)
        embed = discord.Embed(title="Success", description="This channel will now be subscribed to free Steam game notifications!", color=0x00aa00)
        await ctx.send(embed=embed)
    
    @steamGames.error 
    async def subError(self, ctx, error):
        if isinstance(error, commands.CommandError):
            embed=discord.Embed(title="Error", description="{}".format(error), color=0xaa0000)
            await ctx.send(embed=embed)

    @steamGames.command()
    async def unsub(self, ctx):
        """Remove the Steam games pings"""
        gID = ctx.guild.id
        chanID = ctx.channel.id
        find = {
            "guild_id": gID,
            "channel_id": chanID
        }
        deleteOne = freeDB.delete_one(find)
        embed = discord.Embed(title="Success", description="This channel will no longer receive game pings", color=0x00aa00)
        await ctx.send(embed=embed)   

    @commands.group(aliases=['gv'], description="")
    @commands.has_guild_permissions(administrator=True)
    async def giveAway(self, ctx):
        """Giveaway Command"""
        if ctx.invoked_subcommand is None:
            await ctx.send("Invalid command passed")

    @giveAway.command(name="complex",aliases=['cplx'])
    async def complexGV(self, ctx, everyone, time, winners: int, exclusive, *message):
        """Giveaway Command"""
        searchMess = re.findall("[0-9][0-9][a-zA-Z]|[0-9][a-zA-Z]", str(time))
        newMessage = ''
        pingAll = False
        pingRole = False
        excl = False
        pRoleSearch = {
            "guild_id": ctx.guild.id
        }
        findGVRole = gvRolesDB.find_one(pRoleSearch)
        confirmations = ["yes","y","1","true", 't']
        if str(everyone).casefold() in confirmations:
            pingAll = True
        elif str(everyone).casefold() == 'r' and findGVRole != None:
            pingRole = True
            roleID = findGVRole['roleID']
            role = ctx.guild.get_role(roleID)
        if str(exclusive).casefold() in confirmations:
            excl = True
        for mes in message:
            newMessage += mes + " "
        mWeeks = 0
        mDays = 0
        mHours = 0
        mMinutes = 0
        for i in searchMess:
            last = len(i)
            if i[last-1] == 'w':
                mWeeks = int(i[:-1])
                continue
            elif i[last-1] == 'd':
                mDays = int(i[:-1])
                continue
            elif i[last-1] == 'h':
                if int(i[:-1]) > 23:
                    return
                else:
                    mHours = int(i[:-1])
                continue
            elif i[last-1] == 'm':
                if int(i[:-1]) > 59:
                    return
                else:
                    mMinutes = int(i[:-1])
        curDate = datetime.utcnow()
        futureDate = curDate + timedelta(weeks=mWeeks, days=mDays, hours=mHours, minutes=mMinutes)
        embed = discord.Embed(title="New Giveaway!", description = "A giveaway has started for **{}**! React with 🎁 below to enter to win!".format(newMessage[:-1]), color=0x00aa00)
        if winners > 1:
            embed.add_field(name="Number of Winners:", value=winners)
        testMsg = await ctx.send(embed=embed)
        setup = {
            "guild_id": str(ctx.guild.id),
            "channel_id": ctx.channel.id,
            "curDate": curDate,
            "endDate": futureDate,
            "message_id": str(testMsg.id), 
            "message": newMessage, 
            "reactions": [],
            "winners": winners,
            "exclusive": excl
        }
        giveDB.insert_one(setup)
        await testMsg.add_reaction("🎁")
        if pingAll == True:
            await ctx.send("@everyone")
        if pingRole == True:
            await ctx.send("<@&" +str(roleID)+">")
    
    @giveAway.command(aliases=['smpl'])
    async def simple(self, ctx, time, winners : typing.Optional[int], exclusive : typing.Optional[bool] = False, *prize):
        print(str(time) + ' Time')
        print(str(winners) + ' Winners')
        print(str(exclusive) + ' Exclusive')
        print(str(prize) + ' Prize')
        return
    
    @giveAway.command(aliases=['gvrole'], description="")
    @commands.has_guild_permissions(administrator=True)
    async def role(self, ctx, *, role: discord.Role):
        """Setup a role for the Giveaway command to ping"""
        if not role:
            return
        else:
            giveRoles = {
            "guild_id": ctx.guild.id,
            "roleName": str(role).casefold(),
            "roleID": role.id,
            "realRoleName": str(role)
            }
            gvRolesDB.insert_one(giveRoles)
            return

    @tasks.loop(seconds=5)
    async def giveLoop(self):
        curDate = datetime.utcnow()
        ends = giveDB.find()
        if ends != None:
            for i in ends:
                if curDate > i['endDate']:
                    await self.endGive(i)
                else:
                    continue

    @giveLoop.before_loop
    async def before_giveLoop(self):
        await self.bot.wait_until_ready()

    async def endGive(self, dbEntry):
        msgID = dbEntry['message_id']
        chanID = dbEntry['channel_id']
        guildID = dbEntry['guild_id']
        msg = dbEntry['message']
        reacts = dbEntry['reactions']
        winNum = dbEntry['winners']
        excl = dbEntry['exclusive']
        guildObj = self.bot.get_guild(int(guildID))
        chanObj = guildObj.get_channel(int(chanID))
        oldMSG = await chanObj.fetch_message(int(msgID))
        multiWin = ''
        exclusive = []
        count = 0
        if reacts:
            if winNum > 1:
                while winNum > count:
                    randPerson = random.choice(dbEntry['reactions'])
                    if excl and randPerson in exclusive:
                        continue
                    exclusive.append(randPerson)
                    memberObj = guildObj.get_member(randPerson)
                    multiWin += str(memberObj) + ', '
                    count += 1      
            else:
                singleRandPerson = random.choice(dbEntry['reactions'])
                memberObj = guildObj.get_member(singleRandPerson)
                multiWin += str(memberObj) + '  '
        else: 
            if oldMSG:
                embed = discord.Embed(title="Giveaway Over!", description="The giveaway for **{}** has ended. Sadly no one entered so there is no winner.".format(msg), color=0x00aa00)
                await oldMSG.edit(embed=embed)
                await oldMSG.clear_reactions()
            giveDB.delete_one(dbEntry)
        if oldMSG and reacts:
            embed = discord.Embed(title="Giveaway Over!", description="The giveaway for **{}** has ended. Congratulations to {} for winning!".format(msg, multiWin[:-2]), color=0x00aa00)
            await oldMSG.edit(embed=embed)
            await oldMSG.clear_reactions()
        giveDB.delete_one(dbEntry)

    async def giveHandle(self, ctx, time, exclusive, winners = None, everyone = None):

        return
        
                
        



def setup(bot):
    bot.add_cog(Misc(bot))
