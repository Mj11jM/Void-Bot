import discord
from discord.ext import commands


class Help(commands.Cog):

    """This"""

    def __init__(self, bot):
        self.bot = bot

    @commands.command()
    async def help(self, ctx, *cog):
        if not cog:
            embed = discord.Embed(color=0x09f1a6)
            cogsL = ''
            skipCogs = ['Owner', 'Help', 'Loader']
            for x in self.bot.cogs:
                if str(x) in skipCogs:
                    continue
                cogsL += ('**{}** - {}'.format(x, self.bot.cogs[x].__doc__) + '\n')
            embed.add_field(name='Modules', value=cogsL, inline=False)
            await ctx.send(embed=embed)
        else: 
            search = ""
            for mes in cog:
                search += mes + ' '
            if len(cog) > 3:
                embed = discord.Embed(title="Too Many Modules", description="Please do help for one module at a time", color=0xaa0000)
                return await ctx.send(embed=embed)     
            else:
                startLen = len(cog[0])
                exists = False
                for x in self.bot.cogs:
                    allCog = self.bot.get_cog(x)
                    for y in cog:
                        if x.casefold() == search.strip().casefold():
                            if y.casefold() == "owner":
                                embed = discord.Embed(title="Hidden", color=0x000000)
                                await ctx.send(embed=embed)
                                return
                            else:
                                embed = discord.Embed(color=0x09f1a6)
                                cogCom = ''
                                for comms in self.bot.get_cog(x).walk_commands():
                                    if not comms.hidden:
                                        if isinstance (comms, commands.Group):
                                            if comms.root_parent != None:
                                                continue
                                            else:
                                                cogCom += f'**{comms.name}** - {comms.help}\n'
                                        else:
                                            if comms.root_parent != None:
                                                continue
                                            else:
                                                cogCom += f'**{comms.name}** - {comms.help}\n'
                                embed.add_field(name=f'{x} Module - {self.bot.cogs[x].__doc__}', value = cogCom)
                                exists = True
                if not exists:
                    for x in self.bot.cogs:
                        for comms in self.bot.get_cog(x).walk_commands():
                            if str(comms).casefold() == search.strip().casefold() or search.strip().casefold()[:startLen] in comms.aliases or str(comms).casefold() == str(cog[0]).casefold():
                                if len(cog) > 1 and isinstance(comms, commands.Group):
                                    subCom = self.bot.get_command(search)
                                    embed = discord.Embed(description=f"Module **{x}**", color=0x09f1a6)
                                    embed.add_field(name=f'{subCom.name} - {subCom.help}', value=f'How to use:\n{subCom.qualified_name} {subCom.signature}')
                                    if comms.aliases:
                                        aliases = ''
                                        for a in comms.aliases:
                                            aliases += a + ", "
                                        embed.add_field(name="Aliases", value=aliases[:-2] + ' ' + str(subCom.name), inline=False)
                                    if subCom.description:
                                        embed.add_field(name="Longer Description", value=subCom.description, inline=False)
                                    if comms.commands:
                                        subSubComms = ''
                                        for ssc in comms.commands:
                                            subSubComms += str(ssc.name) + ' | '
                                        embed.add_field(name='Sub-Commands', value="<" + subSubComms[:-3] + ">", inline=True)
                                    exists = True
                                else:
                                    embed = discord.Embed(description=f"Module **{x}**", color=0x09f1a6)
                                    embed.add_field(name=f'{comms.name} - {comms.help}', value=f'How to use:\n{comms.qualified_name} {comms.signature}')
                                    if comms.aliases:
                                        aliases = ''
                                        for a in comms.aliases:
                                            aliases += a + ", "
                                        embed.add_field(name="Aliases", value=aliases[:-2], inline=False)
                                    if comms.description:
                                        embed.add_field(name="Longer Description", value=comms.description, inline=False)
                                    if isinstance (comms, commands.Group):
                                        subComStr = ''
                                        subComs = comms.commands
                                        for b in subComs:
                                            subComStr += str(b.name) + ' | '
                                        embed.add_field(name='Sub-Commands', value="<" + subComStr[:-3] + ">", inline=True)
                                    exists = True
                    if not exists:
                        embed = discord.Embed(title="Doesn't Exist", description=f"{cog[0]} doesn't exist in modules or commands. Are you sure you're trying the right bot?", color=0xaa0000)
            await ctx.send(embed=embed)


def setup(bot):
    bot.add_cog(Help(bot))