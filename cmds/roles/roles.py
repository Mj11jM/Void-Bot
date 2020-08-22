import discord
import discord.utils
import pymongo
import asyncio
from bot_index import selfRoleDB, streamDB, delegationDB
from discord.ext import commands


class Roles(commands.Cog):

    """Self-Assigned roles as well as auto-streamer role"""

    def __init__(self, bot):
        self.bot = bot

    @commands.command(aliases=['asar'], description="")
    @commands.has_guild_permissions(administrator=True)
    @commands.bot_has_guild_permissions(manage_roles=True)
    async def addSar(self, ctx, *, role: discord.Role):
        """Add Roles for Self-Assignment"""
        assignableRole = {
            "guild_id": str(ctx.guild.id),
            "roleName": str(role).casefold(),
            "roleID": str(role.id),
            "realRoleName": str(role)
        }
        selfRoleDB.insert_one(assignableRole)
        embed = discord.Embed(title="Role Added", description="Role '**"+str(role)+"**', has been added to the list of self-assignable roles!", color=0x00ff00)
        await ctx.send(embed=embed)

    @addSar.error
    async def addSarError(self, ctx, error):
        if isinstance(error, commands.CommandError):
            embed = discord.Embed(title="Role Not Found", description="Role does not exist, or capitalization is not correct. Adding with asar requires exact capitalization, rsar or iam do not.", color=0xff0000)
            await ctx.send(embed=embed)

    @commands.command(aliases=['rsar'], description="")
    @commands.has_guild_permissions(administrator=True)
    async def remSar(self, ctx, *, roles):
        """Remove Roles for Self-Assignment"""
        assignableRole = {
            "guild_id": str(ctx.guild.id),
            "roleName": str(roles).casefold(),
        }
        findEntry = selfRoleDB.find_one(assignableRole)
        rrName = findEntry['realRoleName']
        if findEntry != None:
            selfRoleDB.delete_one(assignableRole)
            embed = discord.Embed(title="Role Removed", description="Role '**"+str(rrName)+"**', has been removed the list of self-assignable roles!", color=0x00ff00)
            await ctx.send(embed=embed)
        else:
            embed = discord.Embed(title="Error", description="Role '**"+str(roles)+"**', was not found in the database. Please check that it is in listSar first", color=0xff0000)
            await ctx.send(embed=embed)

    @commands.command(aliases=['lsar'], description="")
    async def listSar(self, ctx):
        """List all self-assignable roles in current server"""
        assignableRole = {
            "guild_id": str(ctx.guild.id)
        }
        findEntry = selfRoleDB.find(assignableRole)       
        if findEntry != None:
            findEntry.sort([('realRoleName', pymongo.ASCENDING)])
            values = [d['realRoleName'] for d in findEntry]
            roles = ""
            for entry in values:
                roles += entry + '\n'
            embed = discord.Embed(title="Self Assign Role List", description=str(roles), color=0xffff00)
            await ctx.send(embed=embed)
        else:
            embed = discord.Embed(title="Error", description="No self assigned roles have been found. Make a new one with addSar [Role]", color=0xff0000)
            await ctx.send(embed=embed)

    @commands.command(description="")
    @commands.bot_has_guild_permissions(manage_roles=True)
    async def iam(self, ctx, *, roles):
        """Give yourself a self-assignable role"""
        search = {
            "guild_id": str(ctx.guild.id),
            "roleName": str(roles).casefold()
        } # finding the guild ID and the specific role name, as I am not getting role ID atm to make the iam accept any case, whereas discord.py is case sensitive
        guildReturn = selfRoleDB.find_one(search) # search for the specified cases above, this will obviously not work well if a server has a role named the same thing
        if guildReturn != None:
            try:
                rolesFound = int(guildReturn['roleID']) #declaring the roleID as int, as it's stored as a string but it needs to be passed to guild.get_role as an int
                findRole = ctx.guild.get_role(rolesFound)
                await ctx.message.author.add_roles(findRole)
                embed = discord.Embed(title="Role Given", description="You are now '**" +str(findRole)+"**'", color=0x00ff00)
                await ctx.send(embed=embed, delete_after=5)
                await asyncio.sleep(5)
                await ctx.message.delete()
            except KeyError as err:
                print("Error for result: " +guildReturn+'--'+ err)
        else:
            embed = discord.Embed(title="Error", description="Error finding '**"+str(roles)+"**'! Check lsar for the list of roles available here.", color=0xff0000)
            await ctx.send(embed=embed)

    @iam.error
    async def iamError(self, ctx, error):
        if isinstance(error, commands.BotMissingPermissions):
            embed = discord.Embed(title="Permissions Error", description="Void Bot does not have permissions to use this command", color=0xff0000)
            await ctx.send(embed=embed)

    @commands.command(description="")
    @commands.bot_has_guild_permissions(manage_roles=True)
    async def iamnot(self, ctx, *, roles):
        """Remove a self-assignable role from yourself"""
        search = {
            "guild_id": str(ctx.guild.id),
            "roleName": str(roles).casefold()
        } # finding the guild ID and the specific role name, as I am not getting role ID atm to make the iam accept any case, whereas discord.py is case sensitive
        guildReturn = selfRoleDB.find_one(search) # search for the specified cases above, this will obviously not work well if a server has a role named the same thing
        if guildReturn != None:
            try:
                rolesFound = int(guildReturn['roleID']) #declaring the roleID as int, as it's stored as a string but it needs to be passed to guild.get_role as an int
                findRole = ctx.guild.get_role(rolesFound)
                await ctx.message.author.remove_roles(findRole)
                embed = discord.Embed(title="Role Removed", description="You are no longer '**" +str(findRole)+"**'", color=0x00ff00)
                await ctx.send(embed=embed, delete_after=5)
                await asyncio.sleep(5)
                await ctx.message.delete()
            except KeyError as err:
                print("Error for result: " +guildReturn+'--'+ err)
        else:
            embed = discord.Embed(title="Error", description="Error finding '**"+str(roles)+"**'! Check lsar for the list of roles available here.", color=0xff0000)
            await ctx.send(embed=embed)

    @commands.command(description="")
    @commands.bot_has_guild_permissions(manage_roles=True)
    @commands.has_guild_permissions(manage_roles=True)
    async def streamRole(self, ctx, *, role: discord.Role):
        """Add a role to automatically give streamers"""
        if not role:
            embed = discord.Embed(title="Role Not Found", description="Role name is case sensitive. Please try again.", color=0xaa0000)
            await ctx.send(embed=embed)

        else:
            streamingRole = {
            "guild_id": str(ctx.guild.id),
            "roleID": str(role.id), 
            "role_name": str(role).casefold()
            }
            streamDB.insert_one(streamingRole)
            embed = discord.Embed(title="Role Added", description=f"The role {role}, will be auto-applied to anyone detected as live!", color=0x00aa00)
            await ctx.send(embed=embed)

    @commands.command(aliases=['rmStreamRole'], description="")
    @commands.has_guild_permissions(manage_roles=True)
    async def removeStreamRole(self, ctx, *, role):
        """Removes the role to automatically give streamers"""
        search = {
            "guild_id": str(ctx.guild.id),
            "role_name": str(role).casefold()
        }
        findIt = streamDB.find_one(search)
        if findIt != None:
            streamDB.delete_one(search)
            embed = discord.Embed(title="Role Removed", description=f"The role {role}, will no longer be auto-applied to users detected as live.", color=0x00aa00)
            await ctx.send(embed=embed)
        else:
            embed = discord.Embed(title="Role Not Found", description=f"The role {role}, was not found as a streamer role. Make sure it is spelled correctly and try again.", color=0xaa0000)
            await ctx.send(embed=embed)

    @commands.group()
    @commands.has_guild_permissions(administrator=True)
    @commands.bot_has_guild_permissions(manage_roles=True)
    async def delegate(self, ctx):
        if ctx.invoked_subcommand is None:
            await ctx.send("Invalid command passed")

    @delegate.command()
    async def add(self, ctx, user : discord.Member, *, role : discord.Role):
        search = {
            "guild_id": ctx.guild.id,
            "role": role.id
        }
        searchDB = delegationDB.find_one(search)
        if searchDB != None:
            memberID = user.id
            if memberID in searchDB['manager']:
                embed = discord.Embed(title="Already Manager", description=f"User {user}, is already a manager for {role}", color=0xaa0000)
                await ctx.send(embed=embed)
            else:
                delegationDB.find_one_and_update(search, {'$push': {"manager": user.id}})
                embed = discord.Embed(title="Manager Added", description=f"User {user}, was added as a manager for {role}", color=0x00aa00)
                await ctx.send(embed=embed)
        else:
            listInsertion = [user.id]
            addition = {
                "guild_id": ctx.guild.id,
                "manager": listInsertion,
                "role": role.id,
                "role_name": str(role).casefold(),
                "mods": []
            }
            delegationDB.insert_one(addition)
            embed = discord.Embed(title="Manager Added", description=f"User {user}, was added as a manager for {role}", color=0x00aa00)
            await ctx.send(embed=embed)

    @delegate.command()
    async def remove(self, ctx, user: discord.Member, *, role: discord.Role):
        search = {
            "guild_id": ctx.guild.id,
            "role": role.id
        }
        searchDB = delegationDB.find_one(search)
        if searchDB != None and user.id in searchDB['manager']:
            delegationDB.find_one_and_update(search, {'$pull': {"manager": user.id}})
            embed = discord.Embed(title="Manager Removed", description=f"User {user}, is no longer a manager for {role}", color=0x00aa00)
            await ctx.send(embed=embed)
        else:
            embed = discord.Embed(title="Manager Not Found", description=f"User {user}, is not a manager for {role}", color=0xaa0000)
            await ctx.send(embed=embed)

    @commands.group()
    @commands.bot_has_guild_permissions(manage_roles=True)
    async def role(self, ctx):
        if ctx.invoked_subcommand is None:
            await ctx.send("Invalid command passed")

    @role.group(description="This is a test description of a subcommand")
    async def give(self, ctx, user: discord.Member, *, role : discord.Role = None):
        if not role:
            search = {
                "guild_id": ctx.guild.id,
                "manager": ctx.author.id
            }
            searchDB = delegationDB.find(search)
            if searchDB != None:
                amount = 0
                for i in searchDB:
                    amount += 1
                if amount > 1:
                    embed = discord.Embed(title="Specify Role", description="You are a manager for more than one role in this guild. Please specify the role you want to apply after the user.", color=0xaa0000)
                    await ctx.send(embed=embed)
                    return
                else:
                    print('1')
                    return
        else:
            search = {
                "guild_id": ctx.guild.id,
                "manager": ctx.author.id,
                "role": role.id
            }
            searchDB = delegationDB.find_one(search)
    @give.command(description="This is a test description of a sub-sub command description")
    async def testing(self, ctx):
        print('hi')

    @role.command(description="ayyyyyyyyyyyyyyy")
    async def take(self, ctx, user: discord.Member, *, role : discord.Role = None):
        if not role:
            print('no role')
        else:
            print('role')


def setup(bot):
    bot.add_cog(Roles(bot))
