<?php

declare(strict_types=1);

namespace slapper\entities;

use pocketmine\entity\Entity;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\Location;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\SetActorDataPacket as SetEntityDataPacket;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\network\mcpe\protocol\types\entity\PropertySyncData;
use pocketmine\network\mcpe\protocol\types\entity\StringMetadataProperty;
use pocketmine\player\Player;
use slapper\SlapperTrait;

class SlapperEntity extends Entity {
    use SlapperTrait;

    const TYPE_ID = 0;
    const HEIGHT = 0;

    private const NETWORK_TYPE_IDS = [
        10 => "minecraft:chicken",
        11 => "minecraft:cow",
        12 => "minecraft:pig",
        13 => "minecraft:sheep",
        14 => "minecraft:wolf",
        15 => "minecraft:villager",
        16 => "minecraft:mooshroom",
        17 => "minecraft:squid",
        18 => "minecraft:rabbit",
        19 => "minecraft:bat",
        20 => "minecraft:iron_golem",
        21 => "minecraft:snow_golem",
        22 => "minecraft:ocelot",
        23 => "minecraft:horse",
        24 => "minecraft:donkey",
        25 => "minecraft:mule",
        26 => "minecraft:skeleton_horse",
        27 => "minecraft:zombie_horse",
        28 => "minecraft:polar_bear",
        29 => "minecraft:llama",
        32 => "minecraft:zombie",
        33 => "minecraft:creeper",
        34 => "minecraft:skeleton",
        35 => "minecraft:spider",
        36 => "minecraft:zombie_pigman",
        37 => "minecraft:slime",
        38 => "minecraft:enderman",
        39 => "minecraft:silverfish",
        40 => "minecraft:cave_spider",
        41 => "minecraft:ghast",
        42 => "minecraft:magma_cube",
        43 => "minecraft:blaze",
        44 => "minecraft:zombie_villager",
        45 => "minecraft:witch",
        46 => "minecraft:stray",
        47 => "minecraft:husk",
        48 => "minecraft:wither_skeleton",
        49 => "minecraft:guardian",
        50 => "minecraft:elder_guardian",
        52 => "minecraft:wither",
        54 => "minecraft:shulker",
        55 => "minecraft:endermite",
        57 => "minecraft:vindicator",
        65 => "minecraft:tnt",
        66 => "minecraft:falling_block",
        84 => "minecraft:minecart",
        90 => "minecraft:boat",
        104 => "minecraft:evocation_illager",
        105 => "minecraft:vex",
    ];

    public static function getNetworkTypeId(): string {
        $typeId = static::TYPE_ID;
        if (!is_int($typeId)) {
            throw new \LogicException("Slapper entity TYPE_ID must be an integer");
        }
        $id = self::NETWORK_TYPE_IDS[$typeId] ?? null;
        if ($id === null) {
            throw new \LogicException("Unknown legacy Slapper entity type " . $typeId);
        }
        return $id;
    }

    protected function getInitialSizeInfo(): EntitySizeInfo {
        $height = static::HEIGHT;
        if (!is_int($height) && !is_float($height)) {
            throw new \LogicException("Slapper entity HEIGHT must be numeric");
        }
        return new EntitySizeInfo((float) $height, 1.0);
    }

    protected function getInitialDragMultiplier(): float {
        return 0.0;
    }

    protected function getInitialGravity(): float {
        return 0.0;
    }

    protected function initEntity(CompoundTag $nbt): void {
        $this->namedtag = $nbt;
        parent::initEntity($nbt);
        $this->prepareMetadata();
        $this->setNameTagVisible(false);
    }

    public function saveNBT(): CompoundTag {
        $nbt = parent::saveNBT();
        $this->saveSlapperNbt($nbt);
        return $nbt;
    }

    protected function sendSpawnPacket(Player $player): void {
        parent::sendSpawnPacket($player);
        $this->sendNameTag($player);
    }

    public function sendNameTag(Player $player): void {
        $player->getNetworkSession()->sendDataPacket(SetEntityDataPacket::create(
            $this->getId(),
            [EntityMetadataProperties::NAMETAG => new StringMetadataProperty($this->getDisplayNameFor($player))],
            new PropertySyncData([], []),
            0
        ));
    }
}
