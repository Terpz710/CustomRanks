<?php

declare(strict_types=1);

namespace Terpz710\CustomRanks;

use pocketmine\player\Player;
use pocketmine\utils\Config;

use Terpz710\CustomRanks\Loader;

class RanksManager {

    private $plugin;
    private $ranksData;
    private $ranksConfig;
    private $defaultRank;

    public function __construct(Loader $plugin) {
        $this->plugin = $plugin;
        $this->loadRanks();
        $this->loadRanksConfig();
    }

    private function loadRanks() {
        $this->ranksData = (new Config($this->plugin->getDataFolder() . "Player_Ranks.json", Config::JSON))->getAll();
    }

    private function saveRanks() {
        $config = new Config($this->plugin->getDataFolder() . "Player_Ranks.json", Config::JSON);
        $config->setAll($this->ranksData);
        $config->save();
    }

    private function loadRanksConfig() {
        $this->ranksConfig = (new Config($this->plugin->getDataFolder() . "Ranks.yml", Config::YAML))->getAll();
        $this->defaultRank = $this->ranksConfig['default_rank'] ?? null;
    }

    public function setRank(Player $player, string $rank) {
        $this->ranksData[$player->getName()] = $rank;
        $this->saveRanks();
        $this->plugin->updatePlayerDisplayName($player);
    }

    public function getRank(Player $player): ?string {
        return $this->ranksData[$player->getName()] ?? null;
    }

    public function removeRank(Player $player) {
        if (isset($this->ranksData[$player->getName()])) {
            unset($this->ranksData[$player->getName()]);
            $this->saveRanks();
            $this->plugin->updatePlayerDisplayName($player);
        }
    }

    public function rankExists(string $rank): bool {
        return isset($this->ranksConfig['ranks'][$rank]);
    }

    public function rankHierarchy(): array {
        return $this->ranksConfig['hierarchy'] ?? [];
    }

    public function getRankPermissions(string $rank): ?array {
        return $this->ranksConfig['ranks'][$rank]['permissions'] ?? null;
    }

    public function getDefaultRank(): ?string {
        return $this->defaultRank;
    }

    public function getRankDisplay(string $rank): ?string {
        return $this->ranksConfig['ranks'][$rank]['rank_display'] ?? $rank;
    }
}
