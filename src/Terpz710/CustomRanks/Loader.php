<?php

declare(strict_types=1);

namespace Terpz710\CustomRanks;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\utils\TextFormat as TF;

use Terpz710\CustomRanks\RankCommand\RanksCommand;
use Terpz710\CustomRanks\RanksManager;

class Loader extends PluginBase implements Listener {

    private $ranksManager;

    public function onEnable(): void {
        if (!is_dir($this->getDataFolder())) {
            mkdir($this->getDataFolder());
        }

        $this->saveResource("Ranks.yml");
        $this->ranksManager = new RanksManager($this);
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->registerCommands();
    }

    public function getRanksManager(): RanksManager {
        return $this->ranksManager;
    }

    private function registerCommands() {
        $this->getServer()->getCommandMap()->register("rank", new RanksCommand($this));
    }

    public function onPlayerJoin(PlayerJoinEvent $event) {
        $player = $event->getPlayer();
        $ranksManager = $this->getRanksManager();
        if (!$ranksManager->getRank($player)) {
            $ranksManager->setRank($player, $ranksManager->getDefaultRank());
        }
        $this->updatePlayerDisplayName($player);
        $player->sendMessage(TF::GREEN . "Your rank has been set to " . $ranksManager->getDefaultRank() . "§a!");
    }

    public function onPlayerChat(PlayerChatEvent $event) {
        $player = $event->getPlayer();
        $rank = $this->ranksManager->getRank($player);
        $rankDisplay = $rank ? $this->ranksManager->getRankDisplay($rank) : "";
        $event->setFormat(TF::GREEN . "[" . $rankDisplay . "] " . $player->getName() . ": " . $event->getMessage());
    }

    public function updatePlayerDisplayName(Player $player): void {
        $rank = $this->ranksManager->getRank($player);
        $rankDisplay = $rank ? $this->ranksManager->getRankDisplay($rank) : "";
        $player->setDisplayName(TF::GREEN . "[" . $rankDisplay . "] " . $player->getName());
    }
}
