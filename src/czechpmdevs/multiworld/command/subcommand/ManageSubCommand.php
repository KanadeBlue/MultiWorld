<?php

declare(strict_types=1);

namespace czechpmdevs\multiworld\command\subcommand;

use CortexPE\Commando\BaseSubCommand;
use czechpmdevs\multiworld\util\LanguageManager;
use czechpmdevs\multiworld\util\WorldUtils;
use dktapps\pmforms\CustomForm;
use dktapps\pmforms\CustomFormResponse;
use dktapps\pmforms\element\Input;
use dktapps\pmforms\element\Dropdown;
use dktapps\pmforms\element\Label;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\Server;

class ManageSubCommand extends BaseSubCommand {
    protected function prepare(): void {
        $this->setPermission("multiworld.command.manage");
    }

    /**
     * @param array<string, mixed> $args
     */
    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {
        if (!$sender instanceof Player) {
            $sender->sendMessage("§cThis command can be used only in-game!");
            return;
        }

        $form = new CustomForm(
            "World Manager",
            [
                new Label("label", "Choose an option:"),
                new Dropdown("menu", "Select an action", [
                    "Create World",
                    "Delete World",
                    "World Info",
                    "Load World",
                    "Unload World",
                    "Teleport To World",
                    "Teleport Player to World",
                    "Set Spawn",
                    "Set Lobby"
                ])
            ],
            function (Player $player, CustomFormResponse $response): void {
                $menuIndex = $response->getInt("menu");
                if ($menuIndex === null) {
                    // Form closed or invalid input
                    return;
                }

                switch ($menuIndex) {
                    case 0: // Create World
                        $generators = ["Normal", "Custom", "Nether", "End", "Flat", "Void", "SkyBlock"];
                        $form = new CustomForm(
                            "Create World",
                            [
                                new Label("label", "Create world"),
                                new Input("inputName", "World name"),
                                new Input("inputSeed", "World seed"),
                                new Dropdown("dropdownGenerator", "Generator", $generators)
                            ],
                            function (Player $player, CustomFormResponse $response) use ($generators): void {
                                $name = $response->getString("inputName") ?? "";
                                $seed = $response->getString("inputSeed") ?? "";
                                $genIndex = $response->getInt("dropdownGenerator");

                                if ($name === "" || ($seed !== "" && !is_numeric($seed)) || $genIndex === null || !isset($generators[$genIndex])) {
                                    $player->sendMessage(LanguageManager::translateMessage($player, "forms-invalid"));
                                    return;
                                }

                                $seedValue = trim($seed) === "" ? time() : (int)$seed;
                                $cmd = "mw create \"$name\" \"$seedValue\" \"{$generators[$genIndex]}\"";
                                Server::getInstance()->dispatchCommand($player, $cmd);
                            }
                        );
                        $player->sendForm($form);
                        break;

                    case 1: // Delete World
                        $worlds = WorldUtils::getAllWorlds();
                        $form = new CustomForm(
                            "Delete World",
                            [
                                new Label("label", "Remove world"),
                                new Dropdown("dropdownWorld", "World name", $worlds)
                            ],
                            function (Player $player, CustomFormResponse $response) use ($worlds): void {
                                $worldIndex = $response->getInt("dropdownWorld");
                                if ($worldIndex === null || !isset($worlds[$worldIndex])) {
                                    $player->sendMessage(LanguageManager::translateMessage($player, "forms-invalid"));
                                    return;
                                }
                                Server::getInstance()->dispatchCommand($player, "mw delete \"{$worlds[$worldIndex]}\"");
                            }
                        );
                        $player->sendForm($form);
                        break;

                    case 2: // World Info
                        $worlds = WorldUtils::getAllWorlds();
                        $form = new CustomForm(
                            "World Info",
                            [
                                new Label("label", "Get information about the world"),
                                new Dropdown("dropdownWorld", "Worlds", $worlds)
                            ],
                            function (Player $player, CustomFormResponse $response) use ($worlds): void {
                                $worldIndex = $response->getInt("dropdownWorld");
                                if ($worldIndex === null || !isset($worlds[$worldIndex])) {
                                    $player->sendMessage(LanguageManager::translateMessage($player, "forms-invalid"));
                                    return;
                                }
                                Server::getInstance()->dispatchCommand($player, "mw info \"{$worlds[$worldIndex]}\"");
                            }
                        );
                        $player->sendForm($form);
                        break;

                    case 3: // Load World
                        $worlds = array_values(array_filter(WorldUtils::getAllWorlds(), fn(string $name) => !Server::getInstance()->getWorldManager()->isWorldLoaded($name)));
                        $form = new CustomForm(
                            "Load World",
                            [
                                new Label("label", "Load world"),
                                new Dropdown("dropdownWorld", "World to load", $worlds)
                            ],
                            function (Player $player, CustomFormResponse $response) use ($worlds): void {
                                $worldIndex = $response->getInt("dropdownWorld");
                                if ($worldIndex === null || !isset($worlds[$worldIndex])) {
                                    $player->sendMessage(LanguageManager::translateMessage($player, "forms-invalid"));
                                    return;
                                }
                                Server::getInstance()->dispatchCommand($player, "mw load \"{$worlds[$worldIndex]}\"");
                            }
                        );
                        $player->sendForm($form);
                        break;

                    case 4: // Unload World
                        $worlds = array_values(array_filter(WorldUtils::getAllWorlds(), fn(string $name) => Server::getInstance()->getWorldManager()->isWorldLoaded($name)));
                        $form = new CustomForm(
                            "Unload World",
                            [
                                new Label("label", "Unload world"),
                                new Dropdown("dropdownWorld", "World to unload", $worlds)
                            ],
                            function (Player $player, CustomFormResponse $response) use ($worlds): void {
                                $worldIndex = $response->getInt("dropdownWorld");
                                if ($worldIndex === null || !isset($worlds[$worldIndex])) {
                                    $player->sendMessage(LanguageManager::translateMessage($player, "forms-invalid"));
                                    return;
                                }
                                Server::getInstance()->dispatchCommand($player, "mw unload \"{$worlds[$worldIndex]}\"");
                            }
                        );
                        $player->sendForm($form);
                        break;

                    case 5: // Teleport To World
                        $worlds = WorldUtils::getAllWorlds();
                        $form = new CustomForm(
                            "Teleport To World",
                            [
                                new Label("label", "Teleport to world"),
                                new Dropdown("dropdownWorld", "World", $worlds)
                            ],
                            function (Player $player, CustomFormResponse $response) use ($worlds): void {
                                $worldIndex = $response->getInt("dropdownWorld");
                                if ($worldIndex === null || !isset($worlds[$worldIndex])) {
                                    $player->sendMessage(LanguageManager::translateMessage($player, "forms-invalid"));
                                    return;
                                }
                                Server::getInstance()->dispatchCommand($player, "mw teleport \"{$worlds[$worldIndex]}\"");
                            }
                        );
                        $player->sendForm($form);
                        break;

                    case 6: // Teleport Player to World
                        $players = array_values(array_map(fn(Player $p) => $p->getName(), Server::getInstance()->getOnlinePlayers()));
                        $worlds = WorldUtils::getAllWorlds();
                        $form = new CustomForm(
                            "Teleport Player to World",
                            [
                                new Label("label", "Teleport player to world"),
                                new Dropdown("dropdownPlayer", "Player", $players),
                                new Dropdown("dropdownWorld", "World", $worlds)
                            ],
                            function (Player $player, CustomFormResponse $response) use ($players, $worlds): void {
                                $playerIndex = $response->getInt("dropdownPlayer");
                                $worldIndex = $response->getInt("dropdownWorld");
                                if ($playerIndex === null || $worldIndex === null || !isset($players[$playerIndex]) || !isset($worlds[$worldIndex])) {
                                    $player->sendMessage(LanguageManager::translateMessage($player, "forms-invalid"));
                                    return;
                                }
                                Server::getInstance()->dispatchCommand($player, "mw teleport \"{$worlds[$worldIndex]}\" \"{$players[$playerIndex]}\"");
                            }
                        );
                        $player->sendForm($form);
                        break;

                    case 7: // Set Spawn
                        Server::getInstance()->dispatchCommand($player, "mw setspawn");
                        break;

                    case 8: // Set Lobby
                        Server::getInstance()->dispatchCommand($player, "mw setlobby");
                        break;
                }
            }
        );

        $sender->sendForm($form);
    }
}
