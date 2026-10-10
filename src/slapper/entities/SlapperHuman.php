<?php

declare(strict_types=1);

namespace slapper\entities;

use pocketmine\entity\Human;
use pocketmine\entity\Location;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\SetActorDataPacket;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\network\mcpe\protocol\types\entity\PropertySyncData;
use pocketmine\network\mcpe\protocol\types\entity\StringMetadataProperty;
use pocketmine\player\Player;
use slapper\SlapperTrait;

class SlapperHuman extends Human {
    use SlapperTrait;

    public function __construct(Location $location, ?CompoundTag $nbt = null) {
        $nbt ??= CompoundTag::create();
        parent::__construct($location, self::parseSkinNBT($nbt), $nbt);
    }

    protected function initEntity(CompoundTag $nbt): void {
        $this->namedtag = $nbt;
        parent::initEntity($nbt);
        $this->prepareMetadata();
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
        $player->getNetworkSession()->sendDataPacket(SetActorDataPacket::create(
            $this->getId(),
            [EntityMetadataProperties::NAMETAG => new StringMetadataProperty($this->getDisplayNameFor($player))],
            new PropertySyncData([], []),
            0
        ));
    }
}
