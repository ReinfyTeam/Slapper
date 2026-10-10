<?php

declare(strict_types=1);

namespace slapper\entities\other;

use pocketmine\entity\Location;
use pocketmine\entity\object\FallingBlock as NativeFallingBlock;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\block\RuntimeBlockStateRegistry;
use pocketmine\network\mcpe\convert\TypeConverter;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use slapper\entities\SlapperEntity;

class SlapperFallingSand extends SlapperEntity {

    const TYPE_ID = 66;
    const HEIGHT = 0.98;

    public function __construct(Location $location, ?CompoundTag $nbt = null) {
        parent::__construct($location, $nbt);
    }

    public function prepareMetadata(): void {
        parent::prepareMetadata();

        $blockId = $this->namedtag->getInt("BlockID", 1);
        $blockMeta = $this->namedtag->getInt("BlockMeta", 0);
        $this->namedtag->setInt("BlockID", $blockId);
        $this->namedtag->setInt("BlockMeta", $blockMeta);
        $this->namedtag->setInt("TileID", $blockId);
        $this->namedtag->setByte("Data", $blockMeta);

        $block = NativeFallingBlock::parseBlockNBT(RuntimeBlockStateRegistry::getInstance(), $this->namedtag);
        $networkId = TypeConverter::getInstance()->getBlockTranslator()->internalIdToNetworkId($block->getStateId());
        $this->getNetworkProperties()->setInt(EntityMetadataProperties::VARIANT, $networkId);
    }

    public function saveNBT(): CompoundTag {
        $nbt = parent::saveNBT();
        $nbt->setInt("BlockID", $this->namedtag->getInt("BlockID", 1));
        $nbt->setInt("BlockMeta", $this->namedtag->getInt("BlockMeta", 0));
        return $nbt;
    }

}
