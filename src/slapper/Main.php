<?php

declare(strict_types=1);

namespace slapper;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\console\ConsoleCommandSender;
use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntitySpawnEvent;
use pocketmine\event\entity\EntityMotionEvent;
use pocketmine\event\Listener;
use pocketmine\entity\EntityDataHelper;
use pocketmine\item\Item;
use pocketmine\entity\EntityFactory;
use pocketmine\item\StringToItemParser;

use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;
use pocketmine\world\World;

use slapper\entities\other\{
    SlapperBoat, SlapperFallingSand, SlapperMinecart, SlapperPrimedTNT
};
use slapper\entities\{
    SlapperBat, SlapperBlaze, SlapperCaveSpider, SlapperChicken,
    SlapperCow, SlapperCreeper, SlapperDonkey, SlapperElderGuardian,
    SlapperEnderman, SlapperEndermite, SlapperEntity, SlapperEvoker,
    SlapperGhast, SlapperGuardian, SlapperHorse, SlapperHuman,
    SlapperHusk, SlapperIronGolem, SlapperLavaSlime, SlapperLlama,
    SlapperMule, SlapperMushroomCow, SlapperOcelot, SlapperPig,
    SlapperPigZombie, SlapperPolarBear, SlapperRabbit, SlapperSheep,
    SlapperShulker, SlapperSilverfish, SlapperSkeleton, SlapperSkeletonHorse,
    SlapperSlime, SlapperSnowman, SlapperSpider, SlapperSquid,
    SlapperStray, SlapperVex, SlapperVillager, SlapperVindicator,
    SlapperWitch, SlapperWither, SlapperWitherSkeleton, SlapperWolf,
    SlapperZombie, SlapperZombieHorse, SlapperZombieVillager
};

use slapper\events\SlapperCreationEvent;
use slapper\events\SlapperDeletionEvent;
use slapper\events\SlapperHitEvent;


class Main extends PluginBase implements Listener {

    const ENTITY_TYPES = [
        "Chicken", "Pig", "Sheep", "Cow",
        "MushroomCow", "Wolf", "Enderman", "Spider",
        "Skeleton", "PigZombie", "Creeper", "Slime",
        "Silverfish", "Villager", "Zombie", "Human",
        "Bat", "CaveSpider", "LavaSlime", "Ghast",
        "Ocelot", "Blaze", "ZombieVillager", "Snowman",
        "Minecart", "FallingSand", "Boat", "PrimedTNT",
        "Horse", "Donkey", "Mule", "SkeletonHorse",
        "ZombieHorse", "Witch", "Rabbit", "Stray",
        "Husk", "WitherSkeleton", "IronGolem", "Snowman",
        "LavaSlime", "Squid", "ElderGuardian", "Endermite",
        "Evoker", "Guardian", "PolarBear", "Shulker",
        "Vex", "Vindicator", "Wither", "Llama"
    ];

    const ENTITY_ALIASES = [
		"MagmaCube" => "LavaSlime",
        "ZombiePigman" => "PigZombie",
        "Mooshroom" => "MushroomCow",
        "Player" => "Human",
        "VillagerZombie" => "ZombieVillager",
        "SnowGolem" => "Snowman",
        "FallingBlock" => "FallingSand",
        "FakeBlock" => "FallingSand",
        "VillagerGolem" => "IronGolem",
        "EGuardian" => "ElderGuardian",
        "Emite" => "Endermite"
    ];

    /** @var array<string, bool> */
    public array $hitSessions = [];
    /** @var array<string, bool> */
    public array $idSessions = [];
    /** @var string */
    public $prefix = TextFormat::GREEN . "[" . TextFormat::YELLOW . "Slapper" . TextFormat::GREEN . "] ";
    /** @var string */
    public $noperm = TextFormat::GREEN . "[" . TextFormat::YELLOW . "Slapper" . TextFormat::GREEN . "] You don't have permission.";
    /** @var string */
    public $helpHeader =
        TextFormat::YELLOW . "---------- " .
        TextFormat::GREEN . "[" . TextFormat::YELLOW . "Slapper Help" . TextFormat::GREEN . "] " .
        TextFormat::YELLOW . "----------";

    /** @var string[] */
    public $mainArgs = [
        "help: /slapper help",
        "spawn: /slapper spawn <type> [name]",
        "edit: /slapper edit [id] [args...]",
        "id: /slapper id",
        "remove: /slapper remove [id]",
        "version: /slapper version",
        "cancel: /slapper cancel",
    ];
    /** @var string[] */
    public $editArgs = [
        "helmet: /slapper edit <eid> helmet <id>",
        "chestplate: /slapper edit <eid> chestplate <id>",
        "leggings: /slapper edit <eid> leggings <id>",
        "boots: /slapper edit <eid> boots <id>",
        "skin: /slapper edit <eid> skin",
        "name: /slapper edit <eid> name <name>",
        "addcommand: /slapper edit <eid> addcommand <command>",
        "delcommand: /slapper edit <eid> delcommand <command>",
        "listcommands: /slapper edit <eid> listcommands",
        "blockid: /slapper edit <eid> block <id[:meta]>",
        "scale: /slapper edit <eid> scale <size>",
        "tphere: /slapper edit <eid> tphere",
        "tpto: /slapper edit <eid> tpto",
        "menuname: /slapper edit <eid> menuname <name/remove>"
    ];

    /**
     * @return void
     */
    public function onEnable(): void {
        foreach ([
                     SlapperCreeper::class, SlapperBat::class, SlapperSheep::class,
                     SlapperPigZombie::class, SlapperGhast::class, SlapperBlaze::class,
                     SlapperIronGolem::class, SlapperSnowman::class, SlapperOcelot::class,
                     SlapperZombieVillager::class, SlapperHuman::class, SlapperCow::class,
                     SlapperZombie::class, SlapperSquid::class, SlapperVillager::class,
                     SlapperSpider::class, SlapperPig::class, SlapperMushroomCow::class,
                     SlapperWolf::class, SlapperLavaSlime::class, SlapperSilverfish::class,
                     SlapperSkeleton::class, SlapperSlime::class, SlapperChicken::class,
                     SlapperEnderman::class, SlapperCaveSpider::class, SlapperBoat::class,
                     SlapperMinecart::class, SlapperMule::class, SlapperWitch::class,
                     SlapperPrimedTNT::class, SlapperHorse::class, SlapperDonkey::class,
                     SlapperSkeletonHorse::class, SlapperZombieHorse::class, SlapperRabbit::class,
                     SlapperStray::class, SlapperHusk::class, SlapperWitherSkeleton::class,
                     SlapperFallingSand::class, SlapperElderGuardian::class, SlapperEndermite::class,
                     SlapperEvoker::class, SlapperGuardian::class, SlapperLlama::class,
                     SlapperPolarBear::class, SlapperShulker::class, SlapperVex::class,
                     SlapperVindicator::class, SlapperWither::class
                 ] as $className) {
            $this->registerSlapperEntity($className);
        }
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    /**
     * @param class-string<Entity> $className
     */
    private function registerSlapperEntity(string $className): void {
        $saveName = substr($className, strrpos($className, "\\") + 1);
        EntityFactory::getInstance()->register(
            $className,
            static function (World $world, CompoundTag $nbt) use ($className): Entity {
                return self::createSlapperEntity($className, $world, $nbt);
            },
            [$saveName]
        );
    }

    /**
     * @param class-string<Entity> $className
     */
    private static function createSlapperEntity(string $className, World $world, CompoundTag $nbt): Entity {
        $location = EntityDataHelper::parseLocation($nbt, $world);
        return match ($className) {
            SlapperCreeper::class => new SlapperCreeper($location, $nbt),
            SlapperBat::class => new SlapperBat($location, $nbt),
            SlapperSheep::class => new SlapperSheep($location, $nbt),
            SlapperPigZombie::class => new SlapperPigZombie($location, $nbt),
            SlapperGhast::class => new SlapperGhast($location, $nbt),
            SlapperBlaze::class => new SlapperBlaze($location, $nbt),
            SlapperIronGolem::class => new SlapperIronGolem($location, $nbt),
            SlapperSnowman::class => new SlapperSnowman($location, $nbt),
            SlapperOcelot::class => new SlapperOcelot($location, $nbt),
            SlapperZombieVillager::class => new SlapperZombieVillager($location, $nbt),
            SlapperHuman::class => new SlapperHuman($location, $nbt),
            SlapperCow::class => new SlapperCow($location, $nbt),
            SlapperZombie::class => new SlapperZombie($location, $nbt),
            SlapperSquid::class => new SlapperSquid($location, $nbt),
            SlapperVillager::class => new SlapperVillager($location, $nbt),
            SlapperSpider::class => new SlapperSpider($location, $nbt),
            SlapperPig::class => new SlapperPig($location, $nbt),
            SlapperMushroomCow::class => new SlapperMushroomCow($location, $nbt),
            SlapperWolf::class => new SlapperWolf($location, $nbt),
            SlapperLavaSlime::class => new SlapperLavaSlime($location, $nbt),
            SlapperSilverfish::class => new SlapperSilverfish($location, $nbt),
            SlapperSkeleton::class => new SlapperSkeleton($location, $nbt),
            SlapperSlime::class => new SlapperSlime($location, $nbt),
            SlapperChicken::class => new SlapperChicken($location, $nbt),
            SlapperEnderman::class => new SlapperEnderman($location, $nbt),
            SlapperCaveSpider::class => new SlapperCaveSpider($location, $nbt),
            SlapperBoat::class => new SlapperBoat($location, $nbt),
            SlapperMinecart::class => new SlapperMinecart($location, $nbt),
            SlapperMule::class => new SlapperMule($location, $nbt),
            SlapperWitch::class => new SlapperWitch($location, $nbt),
            SlapperPrimedTNT::class => new SlapperPrimedTNT($location, $nbt),
            SlapperHorse::class => new SlapperHorse($location, $nbt),
            SlapperDonkey::class => new SlapperDonkey($location, $nbt),
            SlapperSkeletonHorse::class => new SlapperSkeletonHorse($location, $nbt),
            SlapperZombieHorse::class => new SlapperZombieHorse($location, $nbt),
            SlapperRabbit::class => new SlapperRabbit($location, $nbt),
            SlapperStray::class => new SlapperStray($location, $nbt),
            SlapperHusk::class => new SlapperHusk($location, $nbt),
            SlapperWitherSkeleton::class => new SlapperWitherSkeleton($location, $nbt),
            SlapperFallingSand::class => new SlapperFallingSand($location, $nbt),
            SlapperElderGuardian::class => new SlapperElderGuardian($location, $nbt),
            SlapperEndermite::class => new SlapperEndermite($location, $nbt),
            SlapperEvoker::class => new SlapperEvoker($location, $nbt),
            SlapperGuardian::class => new SlapperGuardian($location, $nbt),
            SlapperLlama::class => new SlapperLlama($location, $nbt),
            SlapperPolarBear::class => new SlapperPolarBear($location, $nbt),
            SlapperShulker::class => new SlapperShulker($location, $nbt),
            SlapperVex::class => new SlapperVex($location, $nbt),
            SlapperVindicator::class => new SlapperVindicator($location, $nbt),
            SlapperWither::class => new SlapperWither($location, $nbt),
            default => throw new \InvalidArgumentException("Unknown Slapper entity class: " . $className)
        };
    }

    /**
     * @param CommandSender $sender
     * @param Command       $command
     * @param string        $label
     * @param string[]      $args
     *
     * @return bool
     */
    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool {
        switch (strtolower($command->getName())) {
            case "nothing":
                return true;
            case "rca":
                if (count($args) < 2) {
                    $sender->sendMessage($this->prefix . "Please enter a player and a command.");
                    return true;
                }
                $player = $this->getServer()->getPlayerExact(array_shift($args));
                if ($player instanceof Player) {
                    $this->getServer()->dispatchCommand($player, trim(implode(" ", $args)));
                    return true;
                } else {
                    $sender->sendMessage($this->prefix . "Player not found.");
                    return true;
                }
            case "slapper":
                if ($sender instanceof Player) {
                    if (!isset($args[0])) {
                        if (!$sender->hasPermission("slapper.command")) {
                            $sender->sendMessage($this->noperm);
                            return true;
                        } else {
                            $sender->sendMessage($this->prefix . "Please type '/slapper help'.");
                            return true;
                        }
                    }
                    $arg = array_shift($args);
                    switch ($arg) {
                        case "id":
                            if (!$sender->hasPermission("slapper.id")) {
                                $sender->sendMessage($this->noperm);
                                return true;
                            }
                            $this->idSessions[$sender->getName()] = true;
                            $sender->sendMessage($this->prefix . "Hit an entity to get its ID!");
                            return true;
                        case "version":
                            if (!$sender->hasPermission("slapper.version")) {
                                $sender->sendMessage($this->noperm);
                                return true;
                            }
                            $desc = $this->getDescription();
                            $sender->sendMessage($this->prefix . TextFormat::BLUE . $desc->getName() . " " . $desc->getVersion() . " " . TextFormat::GREEN . "by " . TextFormat::GOLD . "jojoe77777");
                            return true;
                        case "cancel":
                        case "stopremove":
                        case "stopid":
                            unset($this->hitSessions[$sender->getName()]);
                            unset($this->idSessions[$sender->getName()]);
                            $sender->sendMessage($this->prefix . "Cancelled.");
                            return true;
                        case "remove":
                            if (!$sender->hasPermission("slapper.remove")) {
                                $sender->sendMessage($this->noperm);
                                return true;
                            }
                            if (!isset($args[0])) {
                                $this->hitSessions[$sender->getName()] = true;
                                $sender->sendMessage($this->prefix . "Hit an entity to remove it.");
                                return true;
                            }
                            $entity = $sender->getWorld()->getEntity((int) $args[0]);
                            if ($entity !== null) {
                                if ($entity instanceof SlapperEntity || $entity instanceof SlapperHuman) {
                                    (new SlapperDeletionEvent($entity))->call();
                                    $entity->close();
                                    $sender->sendMessage($this->prefix . "Entity removed.");
                                } else {
                                    $sender->sendMessage($this->prefix . "That entity is not handled by Slapper.");
                                }
                            } else {
                                $sender->sendMessage($this->prefix . "Entity does not exist.");
                            }
                            return true;
                        case "edit":
                            if (!$sender->hasPermission("slapper.edit")) {
                                $sender->sendMessage($this->noperm);
                                return true;
                            }
                            if (isset($args[0])) {
                                $world = $sender->getWorld();
                                $entity = $world->getEntity((int) $args[0]);
                                if ($entity !== null) {
                                    if ($entity instanceof SlapperEntity || $entity instanceof SlapperHuman) {
                                        if (isset($args[1])) {
                                            switch ($args[1]) {
                                                case "helm":
                                                case "helmet":
                                                case "head":
                                                case "hat":
                                                case "cap":
                                                    if ($entity instanceof SlapperHuman) {
                                                        if (isset($args[2])) {
                                                            if (($item = $this->parseItem($sender, $args[2])) !== null) {
                                                                $entity->getArmorInventory()->setHelmet($item);
                                                                $sender->sendMessage($this->prefix . "Helmet updated.");
                                                            }
                                                        } else {
                                                            $sender->sendMessage($this->prefix . "Please enter an item ID.");
                                                        }
                                                    } else {
                                                        $sender->sendMessage($this->prefix . "That entity can not wear armor.");
                                                    }
                                                    return true;
                                                case "chest":
                                                case "shirt":
                                                case "chestplate":
                                                    if ($entity instanceof SlapperHuman) {
                                                        if (isset($args[2])) {
                                                            if (($item = $this->parseItem($sender, $args[2])) !== null) {
                                                                $entity->getArmorInventory()->setChestplate($item);
                                                                $sender->sendMessage($this->prefix . "Chestplate updated.");
                                                            }
                                                        } else {
                                                            $sender->sendMessage($this->prefix . "Please enter an item ID.");
                                                        }
                                                    } else {
                                                        $sender->sendMessage($this->prefix . "That entity can not wear armor.");
                                                    }
                                                    return true;
                                                case "pants":
                                                case "legs":
                                                case "leggings":
                                                    if ($entity instanceof SlapperHuman) {
                                                        if (isset($args[2])) {
                                                            if (($item = $this->parseItem($sender, $args[2])) !== null) {
                                                                $entity->getArmorInventory()->setLeggings($item);
                                                                $sender->sendMessage($this->prefix . "Leggings updated.");
                                                            }
                                                        } else {
                                                            $sender->sendMessage($this->prefix . "Please enter an item ID.");
                                                        }
                                                    } else {
                                                        $sender->sendMessage($this->prefix . "That entity can not wear armor.");
                                                    }
                                                    return true;
                                                case "feet":
                                                case "boots":
                                                case "shoes":
                                                    if ($entity instanceof SlapperHuman) {
                                                        if (isset($args[2])) {
                                                            if (($item = $this->parseItem($sender, $args[2])) !== null) {
                                                                $entity->getArmorInventory()->setBoots($item);
                                                                $sender->sendMessage($this->prefix . "Boots updated.");
                                                            }
                                                        } else {
                                                            $sender->sendMessage($this->prefix . "Please enter an item ID.");
                                                        }
                                                    } else {
                                                        $sender->sendMessage($this->prefix . "That entity can not wear armor.");
                                                    }
                                                    return true;
                                                case "hand":
                                                case "item":
                                                case "holding":
                                                case "arm":
                                                case "held":
                                                    if ($entity instanceof SlapperHuman) {
                                                        if (isset($args[2])) {
                                                            if (($item = $this->parseItem($sender, $args[2])) !== null) {
                                                                $entity->getInventory()->setItemInHand($item);
                                                                $sender->sendMessage($this->prefix . "Item updated.");
                                                            }
                                                        } else {
                                                            $sender->sendMessage($this->prefix . "Please enter an item ID.");
                                                        }
                                                    } else {
                                                        $sender->sendMessage($this->prefix . "That entity can not wear armor.");
                                                    }
                                                    return true;
                                                case "setskin":
                                                case "changeskin":
                                                case "editskin";
                                                case "skin":
                                                    if ($entity instanceof SlapperHuman) {
                                                        $entity->setSkin($sender->getSkin());
                                                        $entity->sendData($entity->getViewers());
                                                        $sender->sendMessage($this->prefix . "Skin updated.");
                                                    } else {
                                                        $sender->sendMessage($this->prefix . "That entity can't have a skin.");
                                                    }
                                                    return true;
                                                case "name":
                                                case "customname":
                                                    if (isset($args[2])) {
                                                        array_shift($args);
                                                        array_shift($args);
                                                        $entity->setNameTag(str_replace(["{color}", "{line}"], ["§", "\n"], trim(implode(" ", $args))));
                                                        $entity->sendData($entity->getViewers());
                                                        $sender->sendMessage($this->prefix . "Name updated.");
                                                    } else {
                                                        $sender->sendMessage($this->prefix . "Please enter a name.");
                                                    }
                                                    return true;
                                                case "listname":
                                                case "nameonlist":
                                                case "menuname":
                                                    if ($entity instanceof SlapperHuman) {
                                                        if (isset($args[2])) {
                                                            $type = 0;
                                                            array_shift($args);
                                                            array_shift($args);
                                                            $input = trim(implode(" ", $args));
                                                            switch (strtolower($input)) {
                                                                case "remove":
                                                                case "":
                                                                case "disable":
                                                                case "off":
                                                                case "hide":
                                                                    $type = 1;
                                                            }
                                                            if ($type === 0) {
                                                                $entity->namedtag->setString("MenuName", $input);
                                                            } else {
                                                                $entity->namedtag->setString("MenuName", "");
                                                            }
                                                            $entity->respawnToAll();
                                                            $sender->sendMessage($this->prefix . "Menu name updated.");
                                                        } else {
                                                            $sender->sendMessage($this->prefix . "Please enter a menu name.");
                                                            return true;
                                                        }
                                                    } else {
                                                        $sender->sendMessage($this->prefix . "That entity can not have a menu name.");
                                                    }
                                                    return true;
                                                case "addc":
                                                case "addcmd":
                                                case "addcommand":
                                                    if (isset($args[2])) {
                                                        array_shift($args);
                                                        array_shift($args);
                                                        $input = trim(implode(" ", $args));

                                                        $commands = $entity->namedtag->getCompoundTag("Commands") ?? CompoundTag::create();

                                                        if ($commands->getTag($input) !== null) {
                                                            $sender->sendMessage($this->prefix . "That command has already been added.");
                                                            return true;
                                                        }
                                                        $commands->setString($input, $input);
                                                        $entity->namedtag->setTag("Commands", $commands); //in case a new CompoundTag was created
                                                        $sender->sendMessage($this->prefix . "Command added.");
                                                    } else {
                                                        $sender->sendMessage($this->prefix . "Please enter a command.");
                                                    }
                                                    return true;
                                                case "delc":
                                                case "delcmd":
                                                case "delcommand":
                                                case "removecommand":
                                                    if (isset($args[2])) {
                                                        array_shift($args);
                                                        array_shift($args);
                                                        $input = trim(implode(" ", $args));

                                                        $commands = $entity->namedtag->getCompoundTag("Commands") ?? CompoundTag::create();

                                                        $commands->removeTag($input);
                                                        $entity->namedtag->setTag("Commands", $commands); //in case a new CompoundTag was created
                                                        $sender->sendMessage($this->prefix . "Command removed.");
                                                    } else {
                                                        $sender->sendMessage($this->prefix . "Please enter a command.");
                                                    }
                                                    return true;
                                                case "listcommands":
                                                case "listcmds":
                                                case "listcs":
                                                    $commands = $entity->namedtag->getCompoundTag("Commands");
                                                    if ($commands !== null and $commands->getCount() > 0) {
                                                        $id = 0;

                                                        /** @var StringTag $stringTag */
                                                        foreach ($commands as $stringTag) {
                                                            $id++;
                                                            $sender->sendMessage(TextFormat::GREEN . "[" . TextFormat::YELLOW . "S" . TextFormat::GREEN . "] " . TextFormat::YELLOW . $id . ". " . TextFormat::GREEN . $stringTag->getValue() . "\n");
                                                        }
                                                    } else {
                                                        $sender->sendMessage($this->prefix . "That entity does not have any commands.");
                                                    }
                                                    return true;
                                                case "block":
                                                case "tile":
                                                case "blockid":
                                                case "tileid":
                                                    if (isset($args[2])) {
                                                        if ($entity instanceof SlapperFallingSand) {
                                                            $data = explode(":", $args[2]);
                                                            //haxx: we shouldn't use toStaticRuntimeId() because it's internal, but there isn't really any better option at the moment
                                                            $entity->setBlockId((int) $data[0], (int) ($data[1] ?? 0));
                                                            $entity->sendData($entity->getViewers());
                                                            $sender->sendMessage($this->prefix . "Block updated.");
                                                        } else {
                                                            $sender->sendMessage($this->prefix . "That entity is not a block.");
                                                        }
                                                    } else {
                                                        $sender->sendMessage($this->prefix . "Please enter a value.");
                                                    }
                                                    return true;
                                                case "teleporthere":
                                                case "tphere":
                                                case "movehere":
                                                case "bringhere":
                                                    $entity->teleport($sender->getPosition());
                                                    $sender->sendMessage($this->prefix . "Teleported entity to you.");
                                                    $entity->respawnToAll();
                                                    return true;
                                                case "teleportto":
                                                case "tpto":
                                                case "goto":
                                                case "teleport":
                                                case "tp":
                                                    $sender->teleport($entity->getPosition());
                                                    $sender->sendMessage($this->prefix . "Teleported you to entity.");
                                                    return true;
                                                case "scale":
                                                case "size":
                                                    if (isset($args[2])) {
                                                        $scale = (float) $args[2];
                                                        $entity->setScale($scale);
                                                        $entity->sendData($entity->getViewers());
                                                        $sender->sendMessage($this->prefix . "Updated scale.");
                                                    } else {
                                                        $sender->sendMessage($this->prefix . "Please enter a value.");
                                                    }
                                                    return true;
                                                default:
                                                    $sender->sendMessage($this->prefix . "Unknown command.");
                                                    return true;
                                            }
                                        } else {
                                            $sender->sendMessage($this->helpHeader);
                                            foreach ($this->editArgs as $msgArg) {
                                                $sender->sendMessage(str_replace("<eid>", $args[0], TextFormat::GREEN . " - " . $msgArg . "\n"));
                                            }
                                            return true;
                                        }
                                    } else {
                                        $sender->sendMessage($this->prefix . "That entity is not handled by Slapper.");
                                    }
                                } else {
                                    $sender->sendMessage($this->prefix . "Entity does not exist.");
                                }
                                return true;
                            } else {
                                $sender->sendMessage($this->helpHeader);
                                foreach ($this->editArgs as $msgArg) {
                                    $sender->sendMessage(TextFormat::GREEN . " - " . $msgArg . "\n");
                                }
                                return true;
                            }
                        case "help":
                        case "?":
                            $sender->sendMessage($this->helpHeader);
                            foreach ($this->mainArgs as $msgArg) {
                                $sender->sendMessage(TextFormat::GREEN . " - " . $msgArg . "\n");
                            }
                            return true;
                        case "add":
                        case "make":
                        case "create":
                        case "spawn":
                        case "apawn":
                        case "spanw":
                            if (!$sender->hasPermission("slapper.create")) {
                                $sender->sendMessage($this->noperm);
                                return true;
                            }
                            $type = array_shift($args);
                            $name = str_replace(["{color}", "{line}"], ["§", "\n"], trim(implode(" ", $args)));
                            if ($type === null || empty(trim($type))) {
                                $sender->sendMessage($this->prefix . "Please enter an entity type.");
                                return true;
                            }
                            if (empty($name)) {
                                $name = $sender->getDisplayName();
                            }
                            $types = self::ENTITY_TYPES;
                            $aliases = self::ENTITY_ALIASES;
                            $chosenType = null;
                            foreach ($types as $t) {
                                if (strtolower($type) === strtolower($t)) {
                                    $chosenType = $t;
                                }
                            }
                            if ($chosenType === null) {
                                foreach ($aliases as $alias => $t) {
                                    if (strtolower($type) === strtolower($alias)) {
                                        $chosenType = $t;
                                    }
                                }
                            }
                            if ($chosenType === null) {
                                $sender->sendMessage($this->prefix . "Invalid entity type.");
                                return true;
                            }
                            $nbt = $this->makeNBT($chosenType, $sender, $name);
                            $entity = EntityFactory::getInstance()->createFromData($sender->getWorld(), $nbt);
                            if (!$entity instanceof SlapperEntity && !$entity instanceof SlapperHuman) {
                                $sender->sendMessage($this->prefix . "Unable to create that entity type.");
                                return true;
                            }
                            (new SlapperCreationEvent($entity, "Slapper" . $chosenType, $sender, SlapperCreationEvent::CAUSE_COMMAND))->call();
                            $entity->spawnToAll();
                            $sender->sendMessage($this->prefix . $chosenType . " entity spawned with name " . TextFormat::WHITE . "\"" . TextFormat::BLUE . $name . TextFormat::WHITE . "\"" . TextFormat::GREEN . " and entity ID " . TextFormat::BLUE . $entity->getId());
                            return true;
                        default:
                            $sender->sendMessage($this->prefix . "Unknown command. Type '/slapper help' for help.");
                            return true;
                    }
                } else {
                    $sender->sendMessage($this->prefix . "This command only works in game.");
                    return true;
                }
        }
        return true;
    }

    private function parseItem(CommandSender $sender, string $input): ?Item {
        $item = StringToItemParser::getInstance()->parse($input);
        if ($item === null) {
            $sender->sendMessage($this->prefix . "Invalid item ID.");
        }
        return $item;
    }

    /**
     * @param string $type
     * @param Player $player
     * @param string $name
     *
     * @return CompoundTag
     */
    private function makeNBT(string $type, Player $player, string $name): CompoundTag {
        $location = $player->getLocation();
        $nbt = CompoundTag::create()
            ->setTag(Entity::TAG_POS, new ListTag([
                new DoubleTag($location->x),
                new DoubleTag($location->y),
                new DoubleTag($location->z)
            ]))
            ->setTag(Entity::TAG_MOTION, new ListTag([
                new DoubleTag(0.0),
                new DoubleTag(0.0),
                new DoubleTag(0.0)
            ]))
            ->setTag(Entity::TAG_ROTATION, new ListTag([
                new FloatTag($location->yaw),
                new FloatTag($location->pitch)
            ]))
            ->setString(EntityFactory::TAG_IDENTIFIER, "Slapper" . $type)
            ->setTag("Commands", CompoundTag::create());
        $nbt->setShort("Health", 1);
        $nbt->setString("MenuName", "");
        $nbt->setString("CustomName", $name);
        $nbt->setString("SlapperVersion", $this->getDescription()->getVersion());
        if ($type === "Human") {
            $playerNbt = $player->saveNBT();
            $inventoryTag = $playerNbt->getListTag("Inventory");
            if ($inventoryTag !== null) {
                $nbt->setTag("Inventory", clone $inventoryTag);
            }
            $skinTag = $playerNbt->getCompoundTag("Skin");
            if ($skinTag !== null) {
                $nbt->setTag("Skin", clone $skinTag);
            }
        }
        return $nbt;
    }

    /**
     * @param EntityDamageEvent $event
     *
     * @ignoreCancelled true
     *
     * @return void
     */
    public function onEntityDamage(EntityDamageEvent $event): void {
        $entity = $event->getEntity();
        if ($entity instanceof SlapperEntity || $entity instanceof SlapperHuman) {
            $event->cancel();
            if (!$event instanceof EntityDamageByEntityEvent) {
                return;
            }
            $damager = $event->getDamager();
            if (!$damager instanceof Player) {
                return;
            }
            $event = new SlapperHitEvent($entity, $damager);
            $event->call();
            if ($event->isCancelled()) {
                return;
            }
            $damagerName = $damager->getName();
            if (isset($this->hitSessions[$damagerName])) {
                if ($entity instanceof SlapperHuman) {
                    $entity->getInventory()->clearAll();
                }
                $entity->close();
                unset($this->hitSessions[$damagerName]);
                $damager->sendMessage($this->prefix . "Entity removed.");
                return;
            }
            if (isset($this->idSessions[$damagerName])) {
                $damager->sendMessage($this->prefix . "Entity ID: " . $entity->getId());
                unset($this->idSessions[$damagerName]);
                return;
            }

            if (($commands = $entity->namedtag->getCompoundTag("Commands")) !== null) {
                $server = $this->getServer();
                /** @var StringTag $stringTag */
                foreach ($commands as $stringTag) {
                    $server->dispatchCommand(new ConsoleCommandSender($server, $server->getLanguage()), str_replace("{player}", '"' . $damagerName . '"', $stringTag->getValue()));
                }
            }
        }
    }

    /**
     * @param EntitySpawnEvent $ev
     *
     * @return void
     */
    public function onEntitySpawn(EntitySpawnEvent $ev): void {
        $entity = $ev->getEntity();
        if ($entity instanceof SlapperEntity || $entity instanceof SlapperHuman) {
            $clearLagg = $this->getServer()->getPluginManager()->getPlugin("ClearLagg");
            if ($clearLagg !== null && method_exists($clearLagg, "exemptEntity")) {
                $clearLagg->exemptEntity($entity);
            }
        }
    }

    /**
     * @param EntityMotionEvent $event
     *
     * @return void
     */
    public function onEntityMotion(EntityMotionEvent $event): void {
        $entity = $event->getEntity();
        if ($entity instanceof SlapperEntity || $entity instanceof SlapperHuman) {
            $event->cancel();
        }
    }
}
