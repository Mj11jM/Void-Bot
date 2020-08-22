import discord
import asyncio
import time
import re
import helpers.dbLookups
from discord.ext import commands
from bot_index import reroDB, giveDB


class Events(commands.Cog):

    """There are no commands here"""

    def __init__(self, bot):
        self.bot = bot

    @commands.Cog.listener()
    async def on_command_error(self, ctx, error):
        if isinstance(error, commands.CommandNotFound):
            logs = helpers.dbLookups.DBLookups()
            find = await logs.prefixLook(ctx.guild.id)
            if not find:
                usedPref = "-"
            else:
                usedPref = find['prefix']
            prefLength = len(usedPref)
            message = ctx.message.content.casefold()
            normalChar = re.search("^[a-zA-Z]", message[prefLength:])
            if (normalChar):
                embed = discord.Embed(title=f'Command: ' + message[prefLength:] + ' was not found, try ' +
                                    message[:prefLength]+'help for a list of commands', description='', color=0xff0000)
                await ctx.send(embed=embed)
            else:
                return
        else:
            print(error)

    
    @commands.Cog.listener()
    async def on_member_join(self, member):
        newMem = member
        newMemID = str(member.id)
        memAvatar = member.avatar_url_as(static_format='png', size=128)
        guildID = member.guild.id
        guild = member.guild
        logs = helpers.dbLookups.DBLookups()
        find = await logs.loggerLook(guildID)
        if find != None and find['member_join'] == True:
            logChannel = int(find['channel_id'])
            logChannelObj = guild.get_channel(logChannel)
            embed = discord.Embed(title="🆙 **User Joined**", description="<@" + newMemID + ">  `" + str(newMem) + "`", color=0xffff00)
            embed.add_field(name="User ID", value=newMemID, inline=False)
            embed.add_field(name="Joined Server", value=str(member.joined_at)[:-7], inline=True)
            embed.add_field(name="Joined Discord", value=str(member.created_at)[:-7], inline=True)
            embed.set_thumbnail(url=memAvatar)
            await logChannelObj.send(embed=embed)
        findG = await logs.greetLook(guildID)
        if findG != None:
            greetChannel = int(findG['channel_id'])
            greetChanObj = guild.get_channel(greetChannel)
            greetMessage = findG['greeting']
            replaceGreet = greetMessage.replace("<user>", "<@" + newMemID + ">")
            await greetChanObj.send(replaceGreet)


    @commands.Cog.listener()
    async def on_member_remove(self, member):
        oldMem = member
        oldMemID = str(member.id)
        oldMemAvatar = member.avatar_url_as(static_format='png', size=128)
        guildID = member.guild.id
        guild = member.guild
        logs = helpers.dbLookups.DBLookups()
        find = await logs.loggerLook(guildID)
        if find != None and find['member_leave'] == True:
            timegm = time.gmtime()
            logChannel = int(find['channel_id'])
            logChannelObj = guild.get_channel(logChannel)
            embed = discord.Embed(title="🚫 **User Left**", description="<@" + oldMemID + "> `" + str(oldMem) + "`", color=0xffff00)
            embed.add_field(name="User ID", value=oldMemID)
            embed.set_thumbnail(url=oldMemAvatar)
            embed.set_footer(text=time.asctime(timegm))
            await logChannelObj.send(embed=embed)


    @commands.Cog.listener()
    async def on_raw_message_edit(self, payload):
        if payload.cached_message != None:
            if payload.cached_message.author.bot == True:
                return
            chanID = payload.channel_id
            gID = int(payload.data['guild_id'])
            guildObj = self.bot.get_guild(gID)
            authorUN = payload.data['author']['username']
            authorDisc = payload.data['author']['discriminator']
            chanObj = guildObj.get_channel(chanID)
            chanName = chanObj.name
            msgID = payload.message_id
            logs = helpers.dbLookups.DBLookups()
            find = await logs.loggerLook(gID)
            if find != None:
                if find['message_events'] == True and chanID not in find['ignored_channels']:
                    newChanID = int(find['channel_id'])
                    newChannel = guildObj.get_channel(newChanID)
                    cacheMess = payload.cached_message.content
                    newMess = payload.data['content']
                    editTime = payload.data['edited_timestamp']
                    embed = discord.Embed(title="Message edited in #" + chanName, description=authorUN + '#' + authorDisc + " edited their message", color=0xffff00)
                    embed.add_field(name="Old Message", value=cacheMess, inline=False)
                    embed.add_field(name="New Message", value=newMess, inline=False)
                    embed.add_field(name="Channel + Message ID", value=str(chanID) + "/" + str(msgID))
                    embed.set_footer(text=editTime[11:-12])
                    await newChannel.send(embed=embed)
        else:
            return


    @commands.Cog.listener()
    async def on_raw_message_delete(self, payload):
        if payload.cached_message != None:
            if payload.cached_message.author.bot == True:
                return
            delMessage = payload.cached_message.content
            delMessageAttach = payload.cached_message.attachments
            if delMessageAttach:
                attachURL = delMessageAttach[0]
                actualURL = attachURL.url
            mesAuth = payload.cached_message.author.id
            mesName = payload.cached_message.author
            chanID = payload.channel_id
            gID = payload.guild_id
            guildObj = self.bot.get_guild(gID)
            chanObj = guildObj.get_channel(chanID)
            chanName = chanObj.name
            logs = helpers.dbLookups.DBLookups()
            find = await logs.loggerLook(gID)
            if find != None:
                if find['message_events'] == True and chanID not in find['ignored_channels']:
                    timegm = time.gmtime()
                    newChanID = int(find['channel_id'])
                    newChannel = guildObj.get_channel(newChanID)
                    embed = discord.Embed(title="🗑️ Message deleted in #" + chanName, description="<@" + str(mesAuth) + "> `" + str(mesName) + "` deleted their message", color=0xffff00)
                    if delMessage:
                        embed.add_field(name="Message Deleted", value=delMessage + " ", inline=False)
                    if delMessageAttach:
                        embed.add_field(name="Attachements", value=actualURL + " ", inline=False)
                    embed.set_footer(text=time.asctime(timegm))
                    await newChannel.send(embed=embed)


    @commands.Cog.listener()
    async def on_member_update(self, prior, post):
        gID = str(prior.guild.id)
        logs = helpers.dbLookups.DBLookups()
        find = await logs.loggerLook(gID)
        if find != None and find['role_change'] == True:
            name = str(prior)
            uID = str(prior.id)
            logChannel = int(find['channel_id'])
            roleRemove = self.compare(prior.roles, post.roles)
            roleAdd = self.compare(post.roles, prior.roles)
            guildObj = self.bot.get_guild(prior.guild.id)
            chanObj = guildObj.get_channel(logChannel)
            if roleAdd != None:
                for i in roleAdd:
                    embed = discord.Embed(title="Role Added", description="User: " + name + " | " + uID, color=0xffff00)
                    embed.add_field(name="Role Added:", value = str(i))
                    await chanObj.send(embed=embed)
            if roleRemove != None:
                for i in roleRemove:
                    embed = discord.Embed(title="Role Removed", description="User: " + name + " | " + uID, color=0xffff00)
                    embed.add_field(name="Role removed: ", value=str(i))
                    await chanObj.send(embed=embed)
        findStream = await logs.streamLook(gID)
        if findStream != None:
            if prior.activity != post.activity:
                if prior.activity != None:
                    if str(prior.activity.type) == "ActivityType.streaming":
                        streamerRole = int(findStream['roleID'])
                        findRole = guildObj.get_role(streamerRole)
                        await prior.remove_roles(findRole)
                if post.activity != None:
                    if str(post.activity.type) == "ActivityType.streaming":
                        streamerRole = int(findStream['roleID'])
                        findRole = guildObj.get_role(streamerRole)
                        await post.add_roles(findRole)
    

    @commands.Cog.listener()
    async def on_user_update(self, prior, post):
        priorID = prior.id
        if prior.bot == True:
            return
        for guild in self.bot.guilds:
            if guild.get_member(priorID) is None:
                continue
            else:
                gID = str(guild.id)
                logs = helpers.dbLookups.DBLookups()
                find = await logs.loggerLook(gID)
                if find != None and find['user_events'] == True: 
                    logChannel = int(find['channel_id'])
                    chanObj = guild.get_channel(logChannel)
                    if prior.name != post.name:
                        embed = discord.Embed(title="✏️ **Username Changed**", description=str(prior.name) + " | "+str(prior.id), color = 0xffff00)
                        embed.add_field(name="Old Name", value = str(prior.name), inline= True)
                        embed.add_field(name="New Name", value = str(post.name), inline= True)
                        embed.set_footer(text=time.asctime(time.gmtime()))
                        await chanObj.send(embed=embed)
                        await asyncio.sleep(0.5)
                        continue
                    elif prior.discriminator != post.discriminator:
                        embed = discord.Embed(title="✏️ Discriminator Changed", description=str(prior.name) + " | "+str(prior.id), color = 0xffff00)
                        embed.add_field(name="Old Discriminator", value = str(prior), inline= True)
                        embed.add_field(name="New Discriminator", value = str(post), inline= True)
                        embed.set_footer(text=time.asctime(time.gmtime()))
                        await chanObj.send(embed=embed)
                        await asyncio.sleep(0.5)
                        continue
                    elif prior.avatar != post.avatar and find['avatar'] == True:
                        embed = discord.Embed(title="✏️ Avatar Changed", description=str(prior.name) + " | "+str(prior.id), color = 0xffff00)
                        embed.add_field(name="Old Avatar", value = "in thumbnail", inline= True)
                        embed.add_field(name="New Avatar", value = "below", inline= True)
                        embed.set_thumbnail(url=prior.avatar_url)
                        embed.set_image(url=post.avatar_url)
                        embed.set_footer(text=time.asctime(time.gmtime()))
                        await chanObj.send(embed=embed)
                        await asyncio.sleep(0.5)
                        continue 
                else:
                    continue


    def compare(self, list1, list2):
        return (list(set(list1) - set(list2)))

    @commands.Cog.listener()
    async def on_raw_reaction_add(self, payload):
        if self.bot.user == payload.member:
            return
        emote = str(payload.emoji)
        guild = self.bot.get_guild(payload.guild_id)
        chanID = guild.get_channel(payload.channel_id)
        msgID = payload.message_id
        search = {
        'message_id': str(msgID),
        'guild_id': str(payload.guild_id)
        }
        startFound = reroDB.find_one(search)
        giveFound = giveDB.find_one(search)
        if startFound != None:
            emoteFound = startFound['roles']
            equaled = False
            for d in emoteFound:
                if d[0][0:] == emote:
                    roleID = d[2]
                    findRole = guild.get_role(roleID)
                    member = payload.member
                    await member.add_roles(findRole)
                    return
                else:
                    equaled = True
                    continue
            if equaled:
                member = payload.member
                msgFetch = await chanID.fetch_message(msgID)
                await msgFetch.remove_reaction(emote, member)
        elif emote == '🔁':
            msgFetch = await chanID.fetch_message(msgID)
            msgContent = str(msgFetch.content)
            msgAuthor = str(msgFetch.author)
            msgSentAt = str(msgFetch.created_at)
            msgLink = msgFetch.jump_url
            repeaterM = str(payload.member.name)
            embed = discord.Embed(title=repeaterM + " quoted " + msgAuthor[:-5], description=msgContent, color=0x0fff15)
            if msgFetch.embeds:
                embed.add_field(name="OLD EMBED MESSAGE", value="Here is the content from an older embed", inline = False)
                embedList = msgFetch.embeds
                oldTitle = None
                oldDescription = None
                for e in embedList:
                    if e.title:
                        oldTitle = e.title
                    if e.description:
                        oldDescription = e.description
                    embed.add_field(name=oldTitle, value=oldDescription, inline=False)
                    if e.fields:
                        fieldList = e.fields
                        for f in fieldList:
                            embed.add_field(name=f.name, value=f.value, inline=f.inline)
            if msgFetch.attachments:
                msgAttach = msgFetch.attachments[0].url
                imageExt = ["jpeg", "jpg", "png", "gif", "mp4"]
                if str(msgAttach[-3:]) in imageExt:
                    embed.set_image(url=msgAttach)
                else:
                    embed.add_field(name="Quoted Message has attachments that were not transferred",value="Click message link to find them", inline=False)
            embed.add_field(name="Message was sent:", value=msgSentAt[:-7] + 'Z', inline=True)
            embed.add_field(name="Message link:", value="[Link](" + msgLink + ")", inline=True)
            await chanID.send(embed=embed)
        elif emote == '🎁':
            if giveFound != None:
                if payload.member.id in giveFound['reactions']:
                    return
                else:
                    giveDB.find_one_and_update(search, {'$push': {"reactions": payload.member.id}})
            elif giveFound != None:
                member= payload.member
                msgFetch = await chanID.fetch_message(msgID)
                await msgFetch.remove_reaction(emote, member)


    @commands.Cog.listener()
    async def on_raw_reaction_remove(self, payload):
        emote = str(payload.emoji)
        userID = payload.user_id
        guild = self.bot.get_guild(payload.guild_id)
        user = guild.get_member(userID)
        search = {
        'message_id': str(payload.message_id),
        'guild_id': str(payload.guild_id)
        }
        startFound = reroDB.find_one(search)
        if startFound != None:
            emoteFound = startFound['roles']
            for d in emoteFound:
                if d[0][0:] == emote:
                    roleID = d[2]
                    findRole = guild.get_role(roleID)
                    await user.remove_roles(findRole)
                elif str(d[0][0:]) == emote.replace("<:", "<a:"):
                    roleID = d[2]
                    findRole = guild.get_role(roleID)
                    await user.remove_roles(findRole)
        elif emote == "🎁":
            giveFound = giveDB.find_one(search)
            if giveFound != None:
                if userID in giveFound['reactions']:
                    giveDB.find_one_and_update(search, {'$pull': {"reactions": userID}})
                else:
                    return

    @commands.Cog.listener()
    async def on_guild_channel_create(self, channel):
        gID = channel.guild.id
        logs = helpers.dbLookups.DBLookups()
        find = await logs.loggerLook(gID)
        if find != None and find['channel_add_remove'] == True:
            guildObj = channel.guild
            logChannel = int(find['channel_id'])
            chanObj = guildObj.get_channel(logChannel)
            embed = discord.Embed(title="✏️ Channel Created", description=channel.name + ' | ' + str(channel.id), color=0xffff00)
            embed.set_footer(text=time.asctime(time.gmtime()))
            await chanObj.send(embed=embed)

    @commands.Cog.listener()
    async def on_guild_channel_delete(self, channel):
        gID = channel.guild.id
        logs = helpers.dbLookups.DBLookups()
        find = await logs.loggerLook(gID)
        if find != None and find['channel_add_remove'] == True:
            guildObj = channel.guild
            logChannel = int(find['channel_id'])
            chanObj = guildObj.get_channel(logChannel)
            embed = discord.Embed(title="🗑️ Channel Deleted", description=channel.name + ' | ' + str(channel.id), color=0xffff00)
            embed.set_footer(text=time.asctime(time.gmtime()))
            await chanObj.send(embed=embed)

    @commands.Cog.listener()
    async def on_guild_channel_update(self, prior, post):
        if prior.name != post.name:
            gID = post.guild.id
            logs = helpers.dbLookups.DBLookups()
            find = await logs.loggerLook(gID)
            if find != None and find['channel_edit'] == True:
                guildObj = post.guild
                logChannel = int(find['channel_id'])
                chanObj = guildObj.get_channel(logChannel)
                embed = discord.Embed(title="✏️ Channel Name Changed", color=0xffff00)
                embed.add_field(name="Prior Name", value=prior.name)
                embed.add_field(name="New Name", value=post.name)
                embed.set_footer(text=time.asctime(time.gmtime()))
                await chanObj.send(embed=embed)
            else:
                return
        else:
            return

    @commands.Cog.listener()
    async def on_guild_role_create(self, role):
        gID = role.guild.id
        logs = helpers.dbLookups.DBLookups()
        find = await logs.loggerLook(gID)
        if find != None and find['role_add_remove'] == True:
            guildObj = role.guild
            logChannel = int(find['channel_id'])
            chanObj = guildObj.get_channel(logChannel)
            embed = discord.Embed(title="✏️ Role Created", description=role.name, color=0xffff00)
            embed.set_footer(text=time.asctime(time.gmtime()))
            await chanObj.send(embed=embed)

    @commands.Cog.listener()
    async def on_guild_role_delete(self, role):
        gID = role.guild.id
        logs = helpers.dbLookups.DBLookups()
        find = await logs.loggerLook(gID)
        if find != None and find['role_add_remove'] == True:
            guildObj = role.guild
            logChannel = int(find['channel_id'])
            chanObj = guildObj.get_channel(logChannel)
            embed = discord.Embed(title="🗑️ Role Deleted", description=role.name, color=0xffff00)
            embed.set_footer(text=time.asctime(time.gmtime()))
            await chanObj.send(embed=embed)

    @commands.Cog.listener()
    async def on_member_ban(self, guild, user):
        gID = guild.id
        logs = helpers.dbLookups.DBLookups()
        find = await logs.loggerLook(gID)
        if find != None and find['bans'] == True:
            guildObj = guild
            logChannel = int(find['channel_id'])
            chanObj = guildObj.get_channel(logChannel)
            embed = discord.Embed(title="🚫 User Banned 🚫", description=str(user.name) + '#' + str(user.discriminator), color=0xffff00)
            embed.add_field(name='ID',value=user.id)
            embed.set_footer(text=time.asctime(time.gmtime()))
            await chanObj.send(embed=embed)
        
    @commands.Cog.listener()
    async def on_member_unban(self, guild, user):
        gID = guild.id
        logs = helpers.dbLookups.DBLookups()
        find = await logs.loggerLook(gID)
        if find != None and find['bans'] == True:
            guildObj = guild
            logChannel = int(find['channel_id'])
            chanObj = guildObj.get_channel(logChannel)
            embed = discord.Embed(title="⏪ User Unbanned ⏪", description=str(user.name) + '#' + str(user.discriminator), color=0xffff00)
            embed.set_footer(text=time.asctime(time.gmtime()))
            await chanObj.send(embed=embed)


def setup(bot):
    bot.add_cog(Events(bot))