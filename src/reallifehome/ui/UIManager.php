<?php
declare(strict_types=1);

namespace reallifehome\ui;

use jojoe77777\FormAPI\SimpleForm;
use pocketmine\player\Player;
use reallifehome\Main;
use reallifehome\land\Land;

class UIManager {

    public function __construct(private Main $plugin) {}

    public function sendMainMenu(Player $player): void {
        $land = $this->plugin->getLandManager()->getOwnedLand($player->getName());

        $form = new SimpleForm(function (Player $p, $data) use ($land): void {
            if ($data === null) return;
            if ($data === "close") return;

            if ($land !== null && $data === "open") {
                $this->sendLandMenu($p, $land);
            }
        });

        $form->setTitle("RealLifeHome");

        if ($land === null) {
            $form->setContent($this->plugin->getMessage("ui_no_own_lands"));
        } else {
            $form->addButton(str_replace("%name%", $land->getName(), $this->plugin->getMessage("ui_my_land_button")), 1, "", "open");
        }

        $form->addButton($this->plugin->getMessage("ui_btn_close"), 1, "", "close");
        $player->sendForm($form);
    }

    public function sendLandMenu(Player $player, Land $land): void {
        $form = new SimpleForm(function (Player $p, $data) use ($land): void {
            if ($data === null) return;

            switch ($data) {
                case "add":
                    if (count($land->getMembers()) >= $land->getMaxMembers() - 1) {
                        $p->sendMessage($this->plugin->getMessage("ui_member_cap_reached"));
                        return;
                    }
                    $this->sendAddMemberForm($p, $land);
                    break;

                case "remove":
                    $this->sendRemoveMemberForm($p, $land);
                    break;

                case "leave":
                    $land->setOwner(null);
                    $land->setPrice(0);
                    $land->removeMember($p->getName());
                    $this->plugin->getLandManager()->save();
                    $this->plugin->refund($p, (int)($land->getPrice() / 2));
                    break;
            }
        });

        $form->setTitle(str_replace("%name%", $land->getName(), $this->plugin->getMessage("ui_land_menu_title")));
        $info = str_replace(
            ["%owner%", "%price%", "%members%", "%max%"],
            [
                $land->getOwner() ?? "未設定",
                (string)$land->getPrice(),
                implode(", ", $land->getMembers()),
                $land->getMaxMembers()
            ],
            $this->plugin->getMessage("ui_info_line")
        );
        $form->setContent($info);
        $form->addButton($this->plugin->getMessage("ui_btn_add_member"), 1, "", "add");
        $form->addButton($this->plugin->getMessage("ui_btn_remove_member"), 1, "", "remove");
        $form->addButton($this->plugin->getMessage("ui_btn_leave"), 1, "", "leave");
        $form->addButton($this->plugin->getMessage("ui_btn_close"), 1, "", "close");
        $player->sendForm($form);
    }

    public function sendAddMemberForm(Player $player, Land $land): void {
        $form = new SimpleForm(function (Player $p, $data) use ($land): void {
            if ($data === null) return;
            if ($data === $p->getName()) {
                $p->sendMessage($this->plugin->getMessage("ui_cannot_add_self"));
                return;
            }
            if ($land->isMember($data)) {
                $p->sendMessage($this->plugin->getMessage("ui_already_member"));
                return;
            }
            $land->addMember($data);
            $this->plugin->getLandManager()->save();
            $p->sendMessage($this->plugin->getMessage("ui_added_ok", ["%player%" => $data]));
        });

        $form->setTitle(str_replace("%name%", $land->getName(), $this->plugin->getMessage("ui_add_member_title")));
        foreach ($this->plugin->getServer()->getOnlinePlayers() as $target) {
            if ($target->getName() !== $player->getName() && !$land->isMember($target->getName())) {
                $form->addButton($target->getName(), 1, "", $target->getName());
            }
        }

        $player->sendForm($form);
    }

    public function sendRemoveMemberForm(Player $player, Land $land): void {
        $form = new SimpleForm(function (Player $p, $data) use ($land): void {
            if ($data === null) return;
            $land->removeMember($data);
            $this->plugin->getLandManager()->save();
            $p->sendMessage($this->plugin->getMessage("ui_remove_ok"));
        });

        $form->setTitle(str_replace("%name%", $land->getName(), $this->plugin->getMessage("ui_remove_member_title")));
        foreach ($land->getMembers() as $name) {
            if ($name !== $player->getName()) {
                $form->addButton($name, 1, "", $name);
            }
        }

        $player->sendForm($form);
    }
}
