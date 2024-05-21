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
        if (!$this->rankExists($rank)) {
            return;
        }
        $this->ranksData[$player->getName()] = $rank;
        $this->saveRanks();
        
        $permissions = $this->getRankPermissions($rank);
        if ($permissions !== null) {
            $this->applyPermissions($player, $permissions);
        }
        $this->plugin->updatePlayerDisplayName($player);
    }

    private function applyPermissions(Player $player, array $permissions) {
        $permissionManager = $this->plugin->getServer()->getPluginManager()->getPermissionManager();
        foreach ($permissions as $permission) {
            $permissionInstance = $permissionManager->getPermission($permission);
            if ($permissionInstance !== null) {
                $player->addAttachment($this->plugin, $permission, true);
            }
        }
    }

    public function getRank(Player $player): ?string {
        return $this->ranksData[$player->getName()] ?? null;
    }

    public function removeRank(Player $player) {
        $playerName = $player->getName();
        if (isset($this->ranksData[$playerName])) {
            $removedRank = $this->ranksData[$playerName];
            unset($this->ranksData[$playerName]);
            $this->saveRanks();
        
            $permissions = $this->getRankPermissions($removedRank);
            if ($permissions !== null) {
                $this->removePermissions($player, $permissions);
            }
            $this->plugin->updatePlayerDisplayName($player);
       }
   }

    private function removePermissions(Player $player, array $permissions) {
        $permissionManager = $this->plugin->getServer()->getPluginManager()->getPermissionManager();
        foreach ($permissions as $permission) {
            $permissionInstance = $permissionManager->getPermission($permission);
            if ($permissionInstance !== null) {
                $player->removeAttachment($permissionInstance);
            }
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

    public function getRankTag(string $rank): ?string {
        return $this->ranksConfig['ranks'][$rank]['rank_player_tag'] ?? null;
    }

    public function getChatFormat(string $rank): ?string {
        return $this->ranksConfig['ranks'][$rank]['rank_chat_format'] ?? null;
    }
}
