<?php
declare(strict_types=1);

namespace reallifehome\land;

use pocketmine\math\Vector3;

class LandManager {

    /** @var Land[] */
    private array $lands = [];

    public function __construct(
        private string $dataFolder,
        private int $defaultMaxMembers
    ) {
        $this->load();
    }

    public function load(): void {
        $file = $this->dataFolder . "lands.json";
        if (!is_file($file)) return;

        $data = json_decode(file_get_contents($file), true);
        if (!is_array($data)) return;

        foreach ($data as $landData) {
            $land = Land::fromArray($landData);
            $this->lands[$land->getName()] = $land;
        }
    }

    public function save(): void {
        $file = $this->dataFolder . "lands.json";
        $data = [];
        foreach ($this->lands as $land) {
            $data[] = $land->toArray();
        }
        file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    public function add(Land $land): void {
        $land->setMaxMembers($this->defaultMaxMembers);
        $this->lands[$land->getName()] = $land;
    }

    public function remove(string $name): void {
        unset($this->lands[$name]);
    }

    public function get(string $name): ?Land {
        return $this->lands[$name] ?? null;
    }

    public function getByPosition(string $world, Vector3 $pos): ?Land {
        foreach ($this->lands as $land) {
            if ($land->getWorld() === $world && $land->contains($pos)) {
                return $land;
            }
        }
        return null;
    }

    public function exists(string $name): bool {
        return isset($this->lands[$name]);
    }

    public function getOwnedLand(string $owner): ?Land {
        foreach ($this->lands as $land) {
            if ($land->getOwner() === $owner) {
                return $land;
            }
        }
        return null;
    }
}
