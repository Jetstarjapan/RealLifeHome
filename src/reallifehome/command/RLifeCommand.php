<?php
declare(strict_types=1);

namespace reallifehome\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginOwned;
use reallifehome\Main;
use reallifehome\land\Land;

class RLifeCommand extends Command implements PluginOwned {

    public function __construct(private Main $plugin) {
        parent::__construct("rlife", "RealLifeHome 管理用コマンド", "/rlife");
        $this->setPermission("reallifehome.command");
    }

    public function execute(CommandSender $sender, string $label, array $args): void {
        if (!$sender instanceof Player) return;
        if (!$this->testPermission($sender)) return;

        if (!isset($args[0])) {
            $sender->sendMessage("§c使用法: /rlife pos1|pos2|set <名前>|remove <名前>|adduser <名前> <人数>");
            return;
        }

        switch (strtolower($args[0])) {
            case "pos1":
                $this->plugin->setSelection($sender->getName(), 1, $sender->getPosition(), $sender->getWorld()->getFolderName());
                $sender->sendMessage($this->plugin->getMessage("pos1-set"));
                break;

            case "pos2":
                $this->plugin->setSelection($sender->getName(), 2, $sender->getPosition(), $sender->getWorld()->getFolderName());
                $sender->sendMessage($this->plugin->getMessage("pos2-set"));
                break;

            case "set":
                if (!isset($args[1])) {
                    $sender->sendMessage("§c/rlife set <土地名>");
                    return;
                }

                $name = $args[1];
                if ($this->plugin->getLandManager()->exists($name)) {
                    $sender->sendMessage($this->plugin->getMessage("land-already-exists"));
                    return;
                }

                $sel = $this->plugin->getSelection($sender->getName());
                if ($sel === null || !isset($sel["pos1"], $sel["pos2"], $sel["world1"], $sel["world2"])) {
                    $sender->sendMessage("§cpos1とpos2を設定してください");
                    return;
                }

                if ($sel["world1"] !== $sel["world2"]) {
                    $sender->sendMessage("§cワールドが一致していません");
                    return;
                }

                $land = new Land($name, $sel["pos1"], $sel["pos2"], $sel["world1"]);
                $this->plugin->getLandManager()->add($land);
                $this->plugin->getLandManager()->save();
                $sender->sendMessage($this->plugin->getMessage("land-set", ["%land%" => $name]));
                break;

            case "remove":
                if (!isset($args[1])) {
                    $sender->sendMessage("§c/rlife remove <土地名>");
                    return;
                }

                if (!$this->plugin->getLandManager()->exists($args[1])) {
                    $sender->sendMessage($this->plugin->getMessage("land-not-found", ["%land%" => $args[1]]));
                    return;
                }

                $this->plugin->getLandManager()->remove($args[1]);
                $this->plugin->getLandManager()->save();
                $sender->sendMessage($this->plugin->getMessage("land-removed", ["%land%" => $args[1]]));
                break;

            case "adduser":
                if (!isset($args[1], $args[2]) || !is_numeric($args[2])) {
                    $sender->sendMessage("§c/rlife adduser <土地名> <人数>");
                    return;
                }

                $land = $this->plugin->getLandManager()->get($args[1]);
                if ($land === null) {
                    $sender->sendMessage($this->plugin->getMessage("land-not-found", ["%land%" => $args[1]]));
                    return;
                }

                $land->setMaxMembers((int)$args[2]);
                $this->plugin->getLandManager()->save();
                $sender->sendMessage($this->plugin->getMessage("max-members-set", ["%land%" => $land->getName(), "%max%" => $args[2]]));
                break;

            default:
                $sender->sendMessage("§c不明なサブコマンドです");
        }
    }

    public function getOwningPlugin(
