<?php

declare(strict_types=1);

namespace slapper\events;

use pocketmine\event\entity\EntityEvent;
use pocketmine\entity\Entity;
use pocketmine\player\Player;

/** @extends EntityEvent<Entity> */
class SlapperCreationEvent extends EntityEvent {

    public const CAUSE_COMMAND = 0;

    private string $type;
    private ?Player $creator;
    private int $cause;

    public function __construct(Entity $entity, string $type, ?Player $creator = null, int $cause = self::CAUSE_COMMAND) {
        $this->entity = $entity;
        $this->type = $type;
        $this->creator = $creator;
        $this->cause = $cause;
    }

    public function getCreator() : ?Player {
        return $this->creator;
    }

    public function hasCreator() : bool {
        return $this->creator !== null;
    }

    public function getCause() : int {
        return $this->cause;
    }

    public function getType() : string {
        return $this->type;
    }
}
