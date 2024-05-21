<?php

declare(strict_types=1);

namespace Terpz710\CustomRanks\RankCommand;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat as TF;

use Terpz710\CustomRanks\Loader;

class RanksCommand extends Command {

    private $plugin;

    public function __construct(Loader $plugin) {
        parent::__construct("rank");
        $this->setLabel("rank");
        $this->setDescription("Set or Remove a players rank");
        $this->setAliases(["r", "cr", "ranks"]);
        $this->setPermission("customranks.cmd");
        $this->plugin = $plugin;
    }

    public function execute(CommandSender $sender, string $label, array $args) {
        if (!$this->testPermission($sender)) {
            return false;
        }

        if (count($args) < 2) {
            $sender->sendMessage(TF::RED . "Usage: /rank <set|remove|check> <player> <rank>");
            return false;
        }

        $action = array_shift($args);
        $playerName = array_shift($args);
        $rank = array_shift($args);

        $player = $this->plugin->getServer()->getPlayerExact($playerName);
        if ($player === null) {
            $sender->sendMessage(TF::RED . "Player not found!");
            return false;
        }

        $ranksManager = $this->plugin->getRanksManager();

        switch ($action) {
            case "set":
                if ($rank === null) {
                    $sender->sendMessage(TF::RED . "You must specify a rank!");
                    return false;
                }
                if (!$ranksManager->rankExists($rank)) {
                    $sender->sendMessage(TF::RED . "The rank \"" . $rank . "\" does not exist!");
                    return false;
                }
                $ranksManager->setRank($player, $rank);
                $sender->sendMessage(TF::GREEN . "Set rank of " . $playerName . " to " . $rank);
                break;
            case "remove":
                $ranksManager->removeRank($player);
                $sender->sendMessage(TF::GREEN . "Removed rank of " . $playerName);
                break;
            case "check":
                $currentRank = $ranksManager->getRank($player);
                $sender->sendMessage(TF::GREEN . $playerName . "'s current rank is " . $currentRank);
                break;
            default:
                $sender->sendMessage(TF::RED . "Invalid action. Usage: /rank <set|remove|check> <player> <rank>");
                break;
        }
        return true;
    }
}
