<?php

declare(strict_types=1);

namespace Terpz710\CustomRanks;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\player\Player;
use pocketmine\player\chat\LegacyRawChatFormatter;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\utils\Config;

use Terpz710\CustomRanks\RankCommand\RanksCommand;
use Terpz710\CustomRanks\RanksManager;

class Loader extends PluginBase implements Listener {

    private $ranksManager;
    private $messagesConfig;
    private $rankCommandConfig;

    public function onEnable(): void {
        if (!is_dir($this->getDataFolder())) {
            mkdir($this->getDataFolder());
        }

        $this->saveResource("Messages.yml");
        $this->saveResource("Ranks.yml");
        $this->messagesConfig = new Config($this->getDataFolder() . "Messages.yml", Config::YAML);
        $this->rankCommandConfig = new Config($this->getDataFolder() . "RankCommand.yml", Config::YAML);
        $this->ranksManager = new RanksManager($this);
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->registerCommands();
    }

    public function getRanksManager(): RanksManager {
        return $this->ranksManager;
    }

    public function getConfigMessages() {
        return $this->messagesConfig;
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
    }

    public function onPlayerChat(PlayerChatEvent $event) {
        $player = $event->getPlayer();
        $rank = $this->ranksManager->getRank($player);
        $rankChatFormat = $rank ? $this->ranksManager->getChatFormat($rank) : "{playerName}: {message}";
        $formattedMessage = str_replace(["{playerName}", "{message}"], [$player->getName(), $event->getMessage()], $rankChatFormat);
        $event->setFormatter(new LegacyRawChatFormatter($formattedMessage));
    }

    public function updatePlayerDisplayName(Player $player): void {
        $rank = $this->ranksManager->getRank($player);
        $rankTag = $rank ? $this->ranksManager->getRankTag($rank) : "{playerName}";
        $displayName = str_replace("{playerName}", $player->getName(), $rankTag);
        $player->setDisplayName($displayName);
    }
}
