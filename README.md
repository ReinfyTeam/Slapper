# Slapper

<a href="https://discord.gg/n4hANRh"><img src="https://discordapp.com/api/guilds/412491783486832640/embed.png" alt="Discord server"/></a>

Slapper is an NPC plugin for PocketMine-MP servers. This ReinfyTeam-maintained fork preserves the upstream project identity, commands, permissions and addon ecosystem while porting the plugin to the API 5 contract.

## Requirements

- PocketMine-MP API `5.0.0` or newer.
- PHP 8.1 or newer for development and packaging.

The project is designed and supported for [PocketMine-MP](https://github.com/pmmp/PocketMine-MP). Compatibility with unofficial variants is not promised. Exact tested runtime revisions are recorded with the relevant release evidence; they do not expand the supported API range stated above.

## Addons

Official addons:
- [SlapBack](https://github.com/jojoe77777/SlapBack)
- [SlapperRotation](https://github.com/jojoe77777/SlapperRotation)
- [SlapperCache](https://github.com/jojoe77777/SlapperCache)
- [SlapperPlus](https://github.com/jojoe77777/SlapperPlus)
- [SlapperCooldown](https://github.com/jojoe77777/SlapperCooldown)



# Basic documentation

## Commands:

- /slapper [args...]
- /rca <player> <command> - Run command as another player! This can be used to only run the command if the player has permission.

## Main level commands:
- help: /slapper help
- spawn: /slapper spawn <type> [name]
- edit: /slapper edit [id] [args...]
- id: /slapper id
- remove: /slapper remove [id]
- version: /slapper version
- cancel: /slapper cancel
- updateall: /slapper updateall

### Edit args:
- helmet: /slapper edit <eid> helmet <id>
- chestplate: /slapper edit <eid> chestplate <id>
- leggings: /slapper edit <eid> leggings <id>
- boots: /slapper edit <eid> boots <id>
- skin: /slapper edit <eid> skin
- name: /slapper edit <eid> name <name>
- addcommand: /slapper edit <eid> addcommand <command>
- delcommand: /slapper edit <eid> delcommand <command>
- listcommands: /slapper edit <eid> listcommands
- update: /slapper edit <eid> update
- block: /slapper edit <eid> block <id>
- tphere: /slapper edit <eid> tphere
- tpto: /slapper edit <eid> tpto
- menuname: /slapper edit <eid> menuname <name/remove>
	
	
### Aliases for edit args
helmet: helm, helmet, head, hat, cap
chestplate: chest, shirt, chestplate
leggings: pants, legs, leggings
boots: feet, boots, shoes
item: hand, item, holding, arm, held
skin: setskin, changeskin, editskin, skin
name: name, customname
menuname: listname, nameonlist, menuname
namevisible: namevisible, customnamevisible, tagvisible, name_visible, custom_name_visible, tag_visible
addcommand: addc, adduced, add command
delcommand: delc, delcmd, delcommand, remove command
listcommands: listcommands, listcmds, listcs
fix: update, fix, migrate
block: block, tile, blockid, tileid
tphere: teleporthere, tphere, movehere, bringer
tpto: teleportto, tpto, goto, teleport, tp

## Installation

1. Download the latest compatible `Slapper.phar` from the [ReinfyTeam releases](https://github.com/ReinfyTeam/Slapper/releases).
2. Copy the PHAR into the server's `plugins/` directory.
3. Start the server and review the startup log for the Slapper enable message.
4. Grant the relevant `slapper.*` permissions to operators or permission groups.

## Features

- Create and manage NPC entities through `/slapper`.
- Execute commands as another player through `/rca`.
- Configure entity names, skins, equipment, locations and command actions.
- Preserve Slapper entity data through the server's normal world save lifecycle.
- Integrate with the Slapper addon ecosystem listed above.

## Permissions

- `slapper` — use all Slapper commands.
- `slapper.command` — use `/slapper`.
- `slapper.create` — create NPCs.
- `slapper.edit` — edit NPC data.
- `slapper.remove` — remove NPCs.
- `slapper.id` — view entity IDs.
- `slapper.help` — view help.
- `slapper.rca` — use `/rca`.
- `slapper.nothing` — use the no-op command.

The default permission for these nodes is operator. Confirm the exact command behavior and permission assignment on a staging server before applying a custom permission policy.

## Build and test

Install the locked development dependencies and run the repository's analysis command:

```sh
composer install
composer analyse
```

The build workflow uses [DevTools](https://github.com/NhanAZ/DevTools) to package and validate the plugin PHAR. The maintained workflow pins the builder revision used by CI; this README intentionally does not copy that CI pin into the user-facing installation guide.

Before a release, validate the PHAR manifest and checksum, load it in an isolated PocketMine-MP-compatible server data directory, and review the startup and shutdown logs. Do not test by overwriting a live server world or `plugin_data` directory.

## Compatibility and release status

The plugin manifest declares API `5.0.0`. Minimum API, supported range and tested server versions are separate claims; see release notes and campaign verification records for exact commits and runtime evidence.

Release verification records may identify a specific compatible runtime revision when reproducing a release; that evidence is separate from the supported API range stated above.

Release artifacts and source history are published on the [ReinfyTeam GitHub repository](https://github.com/ReinfyTeam/Slapper). Existing upstream attribution, LGPL-3.0-or-later licensing and original author credits are preserved in [LICENSE](LICENSE).

## Support and contributing

Use the [issue tracker](https://github.com/ReinfyTeam/Slapper/issues) for reproducible bugs and the [ReinfyTeam Discord server](https://discord.gg/7u7qKsvSxg) for community discussion. Include the Slapper version, server version, relevant log output and a minimal reproduction. Do not report issues from modified installations as if they were tested release artifacts.

## Credits and license

Slapper originated in the `jojoe77777/Slapper` project. The upstream author and project links are retained in the repository metadata and history. This fork is maintained by ReinfyTeam.

The project is licensed under [LGPL-3.0-or-later](LICENSE).
