<?php
declare(strict_types=1);

namespace reallifehome\land;

use pocketmine\math\Vector3;

class Land {

    private ?string $owner = null;
    private int $price = 0;
    private array $members = [];
    private int $maxMembers;

    public function __construct(
        private string $name,
        private Vector3 $pos1,
        private Vector3 $pos2,
        private string $world
    ) {
        $this->maxMembers = 2;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getWorld(): string {
        return $this->world;
    }

    public function getMin(): Vector3 {
        return new Vector3(
            min($this->pos1->x, $this->pos2->x),
            min($this->pos1->y, $this->pos2->y),
            min($this->pos1->z, $this->pos2->z)
        );
    }

    public function getMax(): Vector3 {
        return new Vector3(
            max($this->pos1->x, $this->pos2->x),
            max($this->pos1->y, $this->pos2->y),
            max($this->pos1->z, $this->pos2->z)
        );
    }

    public function setOwner(string $owner): void {
        $this->owner = $owner;
    }

    public function getOwner(): ?string {
        return $this->owner;
    }

    public function setPrice(int $price): void {
        $this->price = $price;
    }

    public function getPrice(): int {
        return $this->price;
    }

    public function getMembers(): array {
        return $this->members;
    }

    public function addMember(string $name): void {
        if (!in_array($name, $this-_
