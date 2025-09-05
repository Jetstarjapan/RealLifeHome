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
        if (!in_array($name, $this->members, true)) {
            $this->members[] = $name;
        }
    }

    public function removeMember(string $name): void {
        $this->members = array_values(array_filter($this->members, fn($m) => $m !== $name));
    }

    public function isMember(string $name): bool {
        return in_array($name, $this->members, true);
    }

    public function setMaxMembers(int $num): void {
        $this->maxMembers = $num;
    }

    public function getMaxMembers(): int {
        return $this->maxMembers;
    }

    public function toArray(): array {
        return [
            "name" => $this->name,
            "world" => $this->world,
            "pos1" => [$this->pos1->x, $this->pos1->y, $this->pos1->z],
            "pos2" => [$this->pos2->x, $this->pos2->y, $this->pos2->z],
            "owner" => $this->owner,
            "price" => $this->price,
            "members" => $this->members,
            "maxMembers" => $this->maxMembers,
        ];
    }

    public static function fromArray(array $data): Land {
        $land = new Land(
            $data["name"],
            new Vector3(...$data["pos1"]),
            new Vector3(...$data["pos2"]),
            $data["world"]
        );
        $land->owner = $data["owner"];
        $land->price = $data["price"] ?? 0;
        $land->members = $data["members"] ?? [];
        $land->maxMembers = $data["maxMembers"] ?? 2;
        return $land;
    }

    public function contains(Vector3 $pos): bool {
        $min = $this->getMin();
        $max = $this->getMax();
        return (
            $pos->x >= $min->x && $pos->x <= $max->x &&
            $pos->y >= $min->y && $pos->y <= $max->y &&
            $pos->z >= $min->z && $pos->z <= $max->z
        );
    }
}
