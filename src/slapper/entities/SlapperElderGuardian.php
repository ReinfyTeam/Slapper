<?php

declare(strict_types=1);

namespace slapper\entities;

use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataFlags;

class SlapperElderGuardian extends SlapperEntity {

    const TYPE_ID = 50;
    const HEIGHT = 1.9975;

    public function prepareMetadata(): void {
        $this->setGenericFlag(EntityMetadataFlags::ELDER, true);
        parent::prepareMetadata();
    }

}
