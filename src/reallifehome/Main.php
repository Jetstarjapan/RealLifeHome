<?php
declare(strict_types=1);

namespace reallifehome;

use cooldogedev\BedrockEconomy\api\BedrockEconomyAPI;
use jojoe77777\FormAPI\SimpleForm;
use pocketmine\block\BaseSign;
use pocketmine\block\tile\Sign;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use reallifehome\command\RLifeCommand;
use reallifehome\land\LandManager;
use reallifehome\ui\UIManager;

class Main extends PluginBase implements Listener {

    private LandManager $landManager;
    private UIManager $uiManager;
    private array $configData = [];
    private array $messages = [];
    private array $selections = [];

    public function onEnable(): void {
        $this->saveResource("config.yml");
        $this->saveResource("messages.yml");

        $this->configData = (new Config($this->getDataFolder() . "config.yml", Config::YAML))->getAll();
        $lang = $this->configData["language"] ?? "ja_JP";
        $this->messages = (new Config($this->getDataFolder() . "messages.yml", Config::YAML))->get($lang, []);

        $this->landManager = new LandManager($this->getDataFolder(), (int)($this->configData["default-max-members"] ?? 2));
        $this->uiManager = new UIManager($this);

        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getServer()->getCommandMap()->register("rlife", new RLifeCommand($this));
    }

    public function getLandManager(): LandManager {
        return $this->landManager;
    }

    public function getUIManager(): UIManager {
        return $this->uiManager;
    }

    public function getMessage(string $key, array $replaces = []): string {
        $msg = $this->messages[$key] ?? $key;
        foreach ($replaces as $k => $v) {
            $msg = str_replace($k, $v, $msg);
        }
        return ($this->messages["prefix"] ?? "") . $msg;
    }

    public function isTargetWorld(string $worldName): bool {
        foreach ($this->configData["worlds"] ?? [] as $w) {
            if (strcasecmp($w, $worldName) === 0) return true;
        }
        return false;
    }

    public function setSelection(string $player, int $posIndex, \pocketmine\math\Vector3 $pos, string $world): void {
        if (!isset($this->selections[$player])) {
            $this->selections[$player] = [];
        }
        $this->selections[$player]["pos{$posIndex}"] = $pos;
        $this->selections[$player]["world{$posIndex}"] = $world;
    }

    public function getSelection(string $player): ?array {
        return $this->selections[$player] ?? null;
    }

    public function refund(Player $player, int $amount): void {
        BedrockEconomyAPI::getInstance()->addTo($player->getName(), $amount,
            function () use ($player, $amount): void {
                $player->sendMessage($this->getMessage("ui_refund_ok", ["%amount%" => (string)$amount]));
            },
            function () use ($player): void {
                $player->sendMessage("§c返金処理に失敗しました。");
            }
        );
    }
}
