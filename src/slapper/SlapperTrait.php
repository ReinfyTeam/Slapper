<?php

declare(strict_types=1);

namespace slapper;

use pocketmine\entity\Entity;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataFlags;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\network\mcpe\protocol\SetActorDataPacket as SetEntityDataPacket;
use pocketmine\network\mcpe\protocol\types\entity\PropertySyncData;
use pocketmine\network\mcpe\protocol\types\entity\StringMetadataProperty;
use pocketmine\player\Player;

/**
 * Trait containing methods used in various Slappers.
 */
trait SlapperTrait {
    public CompoundTag $namedtag;

    public function setGenericFlag(int $flag, bool $value = true): void {
        $this->getNetworkProperties()->setGenericFlag($flag, $value);
    }

    abstract public function sendNameTag(Player $player): void;

    public function prepareMetadata(): void {
        $this->setGenericFlag(EntityMetadataFlags::IMMOBILE, true);
        $this->setNoClientPredictions();
        $this->setScale($this->namedtag->getFloat("Scale", 1.0));
    }

    public function tryChangeMovement(): void {

    }

    public function sendData(?array $targets, ?array $data = null): void {
        $targets ??= $this->getViewers();
        parent::sendData($targets, $data);
        foreach ($targets as $player) {
            $this->sendNameTag($player);
        }
    }

    public function saveSlapperNbt(CompoundTag $nbt): void {
        $visibility = 0;
        if ($this->isNameTagVisible()) {
            $visibility = 1;
            if ($this->isNameTagAlwaysVisible()) {
                $visibility = 2;
            }
        }
        $nbt->setInt("NameVisibility", $visibility);
        $nbt->setFloat("Scale", $this->getScale());
    }

    public function getDisplayNameFor(Player $player): string {
        $vars = [
            "{name}" => $player->getName(),
            "{display_name}" => $player->getName(),
            "{nametag}" => $player->getNameTag()
        ];
        return str_replace(array_keys($vars), array_values($vars), $this->getNameTag());
    }
}
