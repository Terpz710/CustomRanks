<?php

declare(strict_types=1);

namespace Terpz710\CustomRanks\RankCommand;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

use Terpz710\CustomRanks\Loader;

class RanksCommand extends Command {

    private $plugin;

    public function __construct(Loader $plugin) {
        parent::__construct("rank");
        $this->setLabel("rank");
        $this->setDescription("Set or Remove a players rank");
        $this->setAliases(["ranks", "r", "cr"]);
        $this->setPermission("customranks.cmd");
        $this->plugin = $plugin;
    }

    public function execute(CommandSender $sender, string $label, array $args) {
        if (!$this->testPermission($sender)) {
            return false;
        }

        if (count($args) < 2) {
            $sender->sendMessage($this->plugin->getConfigMessages()->get("command-usage"));
            return false;
        }

        $action = array_shift($args);
        $playerName = array_shift($args);
        $rank = array_shift($args);

        $player = $this->plugin->getServer()->getPlayerExact($playerName);
        if ($player === null) {
            $sender->sendMessage($this->plugin->getConfigMessages()->get("player-not-found"));
            return false;
        }

        $ranksManager = $this->plugin->getRanksManager();

        switch ($action) {
            case "set":
                if ($rank === null) {
                    $sender->sendMessage($this->plugin->getConfigMessages()->get("specify-rank"));
                    return false;
                }
                if (!$ranksManager->rankExists($rank)) {
                    $sender->sendMessage(str_replace("{rank}", $rank, $this->plugin->getConfigMessages()->get("rank-not-found")));
                    return false;
                }
                $ranksManager->setRank($player, $rank);
                $sender->sendMessage(str_replace(["{playerName}", "{rank}"], [$playerName, $rank], $this->plugin->getConfigMessages()->get("rank-successfully-set")));
                break;
            case "remove":
                $ranksManager->removeRank($player, $rank);
                $sender->sendMessage(str_replace(["{playerName}", "{rank}"], [$playerName, $rank], $this->plugin->getConfigMessages()->get("rank-successfully-removed")));
                break;
            case "check":
                $currentRank = $ranksManager->getRank($player);
                $sender->sendMessage(str_replace(["{playerName}", "{currentRank}"], [$playerName, $currentRank], $this->plugin->getConfigMessages()->get("player-current-rank")));
                break;
            default:
                $sender->sendMessage($this->plugin->getConfigMessages()->get("invalid-subcommand"));
                break;
        }
        return true;
    }
}
