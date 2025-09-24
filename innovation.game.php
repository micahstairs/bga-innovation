<?php
/**
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * Innovation implementation: © Jean Portemer <jportemer@gmail.com> and Micah Stairs <micah.stairs@gmail.com>
 * 
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 */

require_once(APP_GAMEMODULE_PATH . 'module/table/table.game.php');
require_once('modules/Innovation/Cards/AbstractCard.php');
require_once('modules/Innovation/Cards/ExecutionState.php');
require_once('modules/Innovation/Cards/InteractionBuilder.php');
require_once('modules/Innovation/GameState.php');
require_once('modules/Innovation/Enums/CardIds.php');
require_once('modules/Innovation/Enums/CardTypes.php');
require_once('modules/Innovation/Enums/Colors.php');
require_once('modules/Innovation/Enums/Directions.php');
require_once('modules/Innovation/Enums/Icons.php');
require_once('modules/Innovation/Enums/Locations.php');
require_once('modules/Innovation/Enums/ValueSelectors.php');
require_once('modules/Innovation/Utils/Arrays.php');
require_once('modules/Innovation/Utils/Notifications.php');
require_once('modules/Innovation/Utils/Strings.php');


use Innovation\GameState;
use Innovation\Cards\ExecutionState;
use Innovation\Enums\CardIds;
use Innovation\Enums\CardTypes;
use Innovation\Enums\Colors;
use Innovation\Enums\Directions;
use Innovation\Enums\Icons;
use Innovation\Enums\Locations;
use Innovation\Enums\ValueSelectors;
use Innovation\Utils\Arrays;
use Innovation\Utils\Notifications;
use Innovation\Utils\Strings;

/* Exception to be called when the game must end */
class EndOfGame extends Exception
{
}

class Innovation extends Table
{

    /** @var GameState An inverted control structure for accessing game state in a testable manner */
    public GameState $innovationGameState;

    /** @var Notifications Used to help create notifications */
    public Notifications $notifications;

    public $textual_card_infos;

    // Effect types
    const DEMAND_EFFECT = 0;
    const NON_DEMAND_EFFECT = 1;
    const COMPEL_EFFECT = 2;
    const ECHO_EFFECT = 3;

    static function stripTransferInfoForNotification(array $transferInfo): array
    {
        // dereference transferInfo
        $transferInfo = (array) $transferInfo;
        unset($transferInfo['splay_direction_from']);
        unset($transferInfo['bottom_from']);
        unset($transferInfo['bottom_to']);
        unset($transferInfo['score_keyword']);
        unset($transferInfo['meld_keyword']);
        unset($transferInfo['achieve_keyword']);
        unset($transferInfo['draw_keyword']);
        unset($transferInfo['safeguard_keyword']);
        unset($transferInfo['return_keyword']);
        unset($transferInfo['foreshadow_keyword']);
        //unset($transferInfo['player_id']);
        //unset($transferInfo['opponent_id']);
        return $transferInfo;
    }

    function __construct()
    {
        // Your global variables labels:
        //  Here, you can assign labels to global variables you are using for this game.
        //  You can use any number of global variables with IDs between 10 and 99.
        //  If your game has options (variants), you also have to associate here a label to
        //  the corresponding ID in gameoptions.inc.php.
        // Note: afterwards, you can get/set the global variables with getGameStateValue/setGameStateInitialValue/setGameStateValue
        parent::__construct();
        require 'material.inc.php'; // Required for testing purposes
        $this->innovationGameState = new GameState($this);
        $this->notifications = new Notifications($this);
        // NOTE: The following values are unused and safe to use: 21-22, 24-25, 61-67, 91-92
        self::initGameStateLabels(
            [
                'number_of_achievements_needed_to_win' => 10,
                'turn0'                                => 11,
                'first_player_with_only_one_action'    => 12,
                'second_player_with_only_one_action'   => 13,
                'has_second_action'                    => 14,
                'game_end_type'                        => 15,
                'player_who_could_not_draw'            => 16,
                'winner_by_dogma'                      => 17,
                'active_player'                        => 18,
                // 0 = not allowed to do an endorse action, 1 = allowed to do an endorse action, 2 = currently executing an effect for the 1st time, 3 = currently executing an effect for the 2nd time
                'endorse_action_state'                 => 19,
                // 0 = dogma has not yet had an impact, 1 = dogma has had an impact
                'dogma_had_impact'                     => 20,
                'sharing_bonus'                        => 23,
                'special_type_of_choice'               => 26,
                'choice'                               => 27,
                'can_pass'                             => 28,
                'n_min'                                => 29,
                'n_max'                                => 30,
                // TODO(LATER): Deprecate and remove 'solid_constraint'. But wait until we finish implementing 4th edition before deciding we do not need this.
                'solid_constraint'                     => 31,
                'splay_direction'                      => 32,
                'owner_from'                           => 33,
                'location_from'                        => 34,
                'owner_to'                             => 35,
                'location_to'                          => 36,
                'bottom_to'                            => 37,
                'age_min'                              => 38,
                'age_max'                              => 39,
                'color_array'                          => 40,
                // TODO(LATER): Remove with_icon. It's now stored in with_icons instead.
                'with_icon'                            => 41,
                'with_icons'                           => 52,
                // TODO(LATER): Remove without_icon. It's now stored in without_icons instead.
                'without_icon'                         => 42,
                'without_icons'                        => 53,
                'not_id'                               => 43,
                'n'                                    => 44,
                'id_last_selected'                     => 45,
                'age_last_selected'                    => 46,
                'color_last_selected'                  => 47,
                'score_keyword'                        => 48,
                'meld_keyword'                         => 50,
                'achieve_keyword'                      => 54,
                // TODO(4E): Remove draw_keyword if it doesn't end up being used
                'draw_keyword'                         => 55,
                'safeguard_keyword'                    => 56,
                'return_keyword'                       => 57,
                'foreshadow_keyword'                   => 58,
                'include_special_achievements'         => 59,
                // Whether to refresh the selection after a choice is made (1 means it should be refreshed)
                'refresh_selection'                    => 60,
                // Whether the safe/forecast limit shrunk the selection size (1 means it was shrunk)
                'limit_shrunk_selection_size'          => 68,
                'card_id_1'                            => 69,
                'card_id_2'                            => 70,
                'card_id_3'                            => 71,
                'require_achievement_eligibility'      => 72,
                'has_demand_effect'                    => 73,
                'has_splay_direction'                  => 74,
                'owner_last_selected'                  => 75,
                'type_array'                           => 76,
                'icon_array'                           => 49,
                'age_array'                            => 51,
                'choice_array'                         => 77,
                'player_array'                         => 78,
                'icon_hash_1'                          => 79,
                'icon_hash_2'                          => 80,
                'icon_hash_3'                          => 81,
                'icon_hash_4'                          => 82,
                'icon_hash_5'                          => 83,
                'enable_autoselection'                 => 84,
                'include_relics'                       => 85,
                'bottom_from'                          => 86,
                'with_bonus'                           => 87,
                'without_bonus'                        => 88,
                'card_ids_are_in_auxiliary_array'      => 89,
                // 1 if the zone should be revealed if the player is unable to perform the interaction, else 0
                'reveal_if_unable'                     => 90,

                // ID of the card which was foreseen
                'foreseen_card_id'                     => 93,
                // ID of the card which was melded
                'melded_card_id'                       => 94,
                // ID of the relic which may be seized
                'relic_id'                             => 95,
                // -1 = none, 0 = free action, 1 = first action, 2 = second action
                'current_action_number'                => 96,
                // 0 refers to the originally executed card, 1 refers to a card exexcuted by that initial card, etc.
                'current_nesting_index'                => 97,
                // Used to help release new versions of the game without breaking existing games (3 = Cities, 4 = 4th edition base game, 5 = 4th edition Unseen)
                'release_version'                      => 98,
                // 0 for disabled, 1 for enabled, 2 for a special mode of testing which does not terminate the game
                'debug_mode'                           => 99,

                // 1 for normal game, 2/3/4/5 for team game
                'game_type'                            => 100,
                // 1 for third edition, 2 for first edition, 3 for fourth edition
                'game_rules'                           => 101,
                // 1 for "Disabled", 2 for "Enabled without Relics", 3 for "Enabled with Relics"
                'artifacts_mode'                       => 102,
                // 1 for "Disabled", 2 for "Enabled"
                'cities_mode'                          => 103,
                // 1 for "Disabled", 2 for "Enabled"
                'echoes_mode'                          => 104,
                // 1 for "Disabled", 2 for "Enabled"
                'unseen_mode'                          => 106,
                // 1 for "Disabled", 2 for "Enabled"
                'extra_achievement_to_win'             => 110,
            ]
        );
    }

    protected function getGameName()
    {
        return "innovation";
    }

    // TODO(4E): Simulate migration.
    function upgradeTableDb($from_version)
    {
        self::applyDbUpgradeToAllDB("ALTER TABLE DBPREFIX_auxiliary_value_table MODIFY COLUMN `nesting_index` SMALLINT NOT NULL;");
        if (is_null(self::getUniqueValueFromDB("SHOW COLUMNS FROM `player` LIKE 'player_icon_count_7'"))) {
            self::applyDbUpgradeToAllDB("ALTER TABLE DBPREFIX_player ADD `player_icon_count_7` SMALLINT UNSIGNED NOT NULL DEFAULT 0;");
        }
        if (is_null(self::getUniqueValueFromDB("SHOW COLUMNS FROM `player` LIKE 'democracy_counter'"))) {
            self::applyDbUpgradeToAllDB("ALTER TABLE DBPREFIX_player ADD `democracy_counter` TINYINT UNSIGNED NOT NULL DEFAULT 0;");
        }
        if (is_null(self::getUniqueValueFromDB("SHOW COLUMNS FROM `player` LIKE 'distance_rule_share_state'"))) {
            self::applyDbUpgradeToAllDB("ALTER TABLE DBPREFIX_player ADD `distance_rule_share_state` TINYINT UNSIGNED NOT NULL DEFAULT 0;");
        }
        if (is_null(self::getUniqueValueFromDB("SHOW COLUMNS FROM `player` LIKE 'distance_rule_demand_state'"))) {
            self::applyDbUpgradeToAllDB("ALTER TABLE DBPREFIX_player ADD `distance_rule_demand_state` TINYINT UNSIGNED NOT NULL DEFAULT 0;");
        }
        if (is_null(self::getUniqueValueFromDB("SHOW COLUMNS FROM `player` LIKE 'player_index'"))) {
            self::applyDbUpgradeToAllDB("ALTER TABLE DBPREFIX_player ADD `player_index` TINYINT UNSIGNED NOT NULL DEFAULT 0;");
            self::calculatePlayerIndexes();
        }
        if (is_null(self::getUniqueValueFromDB("SHOW COLUMNS FROM `player` LIKE 'will_draw_unseen_card_next'"))) {
            self::applyDbUpgradeToAllDB("ALTER TABLE DBPREFIX_player ADD `will_draw_unseen_card_next` BOOLEAN DEFAULT FALSE;");
        }
        if (is_null(self::getUniqueValueFromDB("SHOW COLUMNS FROM `nested_card_execution` LIKE 'replace_may_with_must'"))) {
            self::applyDbUpgradeToAllDB("ALTER TABLE DBPREFIX_nested_card_execution ADD `replace_may_with_must` BOOLEAN DEFAULT FALSE;");
        }
        if (is_null(self::getUniqueValueFromDB("SHOW COLUMNS FROM `nested_card_execution` LIKE 'super_execute'"))) {
            self::applyDbUpgradeToAllDB("ALTER TABLE DBPREFIX_nested_card_execution ADD `super_execute` BOOLEAN DEFAULT FALSE;");
        }
        if (is_null(self::getUniqueValueFromDB("SHOW COLUMNS FROM `nested_card_execution` LIKE 'performed_one_time_setup'"))) {
            self::applyDbUpgradeToAllDB("ALTER TABLE DBPREFIX_nested_card_execution ADD `performed_one_time_setup` INT DEFAULT NULL;");
        }

        // TODO(4E): Update what we are using to compare from_version. 
        if ($from_version <= 2408050353) {
            self::initGameStateLabels(['dogma_had_impact' => 20]);
            $this->innovationGameState->set('dogma_had_impact', 0);
        }

        // TODO(4E): Update what we are using to compare from_version. 
        if ($from_version <= 2310040231) {
            self::initGameStateLabels([
                'refresh_selection' => 60,
            ]);
            $this->innovationGameState->set('refresh_selection', -1);
        }

        // TODO(4E): Update what we are using to compare from_version. 
        if ($from_version <= 2309210143) {
            self::initGameStateLabels([
                'reveal_if_unable' => 90,
            ]);
            $this->innovationGameState->set('reveal_if_unable', -1);
        }

        // TODO(4E): Update what we are using to compare from_version. 
        if ($from_version <= 2309210501) {
            self::initGameStateLabels(['include_special_achievements' => 59]);
            $this->innovationGameState->set('include_special_achievements', -1);
        }

        // TODO(4E): Update what we are using to compare from_version. 
        if ($from_version <= 2308231318) {
            self::initGameStateLabels(
                array(
                    'achieve_keyword'    => 54,
                    'draw_keyword'       => 55,
                    'safeguard_keyword'  => 56,
                    'return_keyword'     => 57,
                    'foreshadow_keyword' => 58,
                )
            );
            $this->innovationGameState->set('achieve_keyword', -1);
            $this->innovationGameState->set('draw_keyword', -1);
            $this->innovationGameState->set('safeguard_keyword', -1);
            $this->innovationGameState->set('return_keyword', -1);
            $this->innovationGameState->set('foreshadow_keyword', -1);
        }

        // TODO(4E): Update what we are using to compare from_version. 
        if ($from_version <= 2307142341) {
            self::initGameStateLabels(
                array(
                    'limit_shrunk_selection_size' => 68,
                    'foreseen_card_id'            => 93,
                    'with_icons'                  => 52,
                    'without_icons'               => 53,
                )
            );
            // TODO(4E): Is there a way to make the deployment smoother? Right now this will break a lot of cards when it's deployed.
            $this->innovationGameState->set('limit_shrunk_selection_size', -1);
            // $with_icon = $this->innovationGameState->get('with_icon');
            // if ($with_icon > 0) {
            //     $this->innovationGameState->set('with_icons', Arrays::encode([$with_icon]));
            // } else {
            $this->innovationGameState->set('with_icons', Arrays::encode([]));
            // }
            // $without_icon = $this->innovationGameState->get('without_icon');
            // if ($without_icon > 0) {
            //     $this->innovationGameState->set('without_icons', Arrays::encode([$without_icon]));
            // } else {
            $this->innovationGameState->set('without_icons', Arrays::encode([]));
            // }
        }

        // TODO(4E): Update what we are using to compare from_version. 
        if ($from_version <= 2302100853) {
            self::initGameStateLabels(
                array(
                    'icon_array'   => 49,
                    'choice_array' => 51,
                )
            );
            $this->innovationGameState->set('icon_array', Arrays::encode([1, 2, 3, 4, 5, 6]));
        }
        // TODO(LATER): Remove this.
        if ($from_version <= 2303050253) {
            self::initGameStateLabels(
                array(
                    'meld_keyword' => 50,
                )
            );
            $this->innovationGameState->set('meld_keyword', -1);
        }
        if ($from_version <= 2302100853) {
            self::initGameStateLabels(
                array(
                    'endorse_action_state' => 19,
                )
            );
            $this->innovationGameState->set('endorse_action_state', 0);
        }
    }

    function debug_transfer($card_id, $action)
    {
        if ($this->innovationGameState->get('debug_mode') == 0) {
            return; // Not in debug mode
        }
        $player_id = self::getCurrentPlayerId();
        $card = self::getCardInfo($card_id);
        $card['using_debug_buttons'] = true;
        if ($card['owner'] != $player_id && $card['owner'] != 0) {
            $card = self::returnCard($card);
            $card['using_debug_buttons'] = true;
        }
        switch ($action) {
            case 'draw':
                self::transferCardFromTo($card, $player_id, 'hand');
                break;
            case 'meld':
                self::meldCard($card, $player_id);
                break;
            case 'tuck':
                self::tuckCard($card, $player_id);
                break;
            case 'score':
                self::scoreCard($card, $player_id);
                break;
            case 'achieve':
                self::transferCardFromTo($card, $player_id, "achievements");
                break;
            case 'return':
                self::returnCard($card);
                break;
            case 'topdeck':
                self::transferCardFromTo($card, 0, 'deck', ['bottom_to' => false]);
                break;
            case 'dig':
                if (self::getArtifactOnDisplay($player_id) != null) {
                    throw new BgaUserException("There is already an Artifact on display");
                }
                self::digCard($card, $player_id);
                break;
            case 'foreshadow':
                self::foreshadowCard($card, $player_id);
                break;
            case 'junk':
                self::junkCard($card);
                break;
            case 'safeguard':
                self::safeguardCard($card, $player_id);
                break;
            default:
                throw new BgaUserException("Unsupported debug action: " . $action);
        }
    }
    function debug_transfer_all($location_from, $location_to)
    {
        if ($this->innovationGameState->get('debug_mode') == 0) {
            return; // Not in debug mode
        }
        $player_id = self::getCurrentPlayerId();
        $owner_from = $player_id;
        $owner_to = $location_to == 'deck' ? 0 : $player_id;
        foreach (self::getCardsInLocation($owner_from, $location_from) as $card) {
            $card['using_debug_buttons'] = true;
            self::transferCardFromTo($card, $owner_to, $location_to);
        }
    }

    function debug_splay($color, $direction)
    {
        if ($this->innovationGameState->get('debug_mode') == 0) {
            return; // Not in debug mode
        }
        $player_id = self::getCurrentPlayerId();
        self::splay($player_id, $player_id, $color, $direction, /*force_unsplay=*/ $direction == 0);
    }

    /*
        setupNewGame:

        This method is called only once, when a new game is launched.
        In this method, you must setup the game according to the game rules, so that
        the game is ready to be played.
    */
    protected function setupNewGame($players, $options = array())
    {
        self::DbQuery("DELETE FROM player WHERE TRUE");

        // Set the colors of the players with HTML color code
        // The available colors are blue, red, green and yellow
        // There are compatible with player preferences
        $default_colors = array("0000ff", "ff0000", "008000", "ffa500", "0000000");

        $game_type = $this->innovationGameState->get('game_type');
        $individual_game = self::decodeGameType($game_type) == 'individual';

        if ($game_type > 2) { // Team game with fixed teams (1 vs 2 or vs 3 or vs 4)
            $teammate_of_first = $game_type - 1; // 2nd if game_mode is 3, 3rd if game_mode is 4, 4th if game_mode is 5
            $players = self::rearrangePlayersForFixedTeams($players, $teammate_of_first);
        }

        $sql = "INSERT INTO player (player_id, player_color, player_canal, player_name, player_avatar, player_team) VALUES ";
        $values = array();
        $t = 0;

        foreach ($players as $player_id => $player) {
            $color = $default_colors[$t]; // There is a blue team and a red team: preferences of players on colors are disabled
            $values[$player_id] = "('" . $player_id . "','$color','" . $player['player_canal'] . "','" . addslashes($player['player_name']) . "','" . addslashes($player['player_avatar']) . "'," . ($t + 1) . ")";
            if ($individual_game) {
                $t++;
            } else { // Team game: the players of the same team are sitting across from each other
                $t = ($t + 1) % 2;
            }
        }
        $sql .= implode(',', $values);
        self::DbQuery($sql);
        if ($individual_game) { // We can take into account the preferences of players on colors
            self::reattributeColorsBasedOnPreferences($players, $default_colors);
        }
        self::reloadPlayersBasicInfos();

        self::calculatePlayerIndexes();

        /************ Start the game initialization *****/

        // Indicate that this production game was created after the 4th edition unseen game was released
        // TODO(FIGURES): Update this before releasing future expansions.
        $this->innovationGameState->setInitial('release_version', 5);

        // Init global values with their initial values
        $this->innovationGameState->setInitial('debug_mode', $this->getBgaEnvironment() == 'studio' ? 1 : 0);

        // Number of achievements needed to win: 6 with 2 players, 5 with 3 players, 4 with 4 players and 6 for team game
        $number_of_achievements_needed_to_win = $individual_game ? 8 - count($players) : 6;
        $this->innovationGameState->setInitial('number_of_achievements_needed_to_win', $number_of_achievements_needed_to_win);

        // Add one required achievement for each expansion
        if ($this->innovationGameState->artifactsExpansionEnabled()) {
            $this->innovationGameState->increment('number_of_achievements_needed_to_win');
        }

        if ($this->innovationGameState->citiesExpansionEnabled()) {
            $this->innovationGameState->increment('number_of_achievements_needed_to_win');
        }

        if ($this->innovationGameState->echoesExpansionEnabled()) {
            $this->innovationGameState->increment('number_of_achievements_needed_to_win');
        }

        if ($this->innovationGameState->unseenExpansionEnabled()) {
            $this->innovationGameState->increment('number_of_achievements_needed_to_win');
        }

        // Add extra achievement to win
        if ($this->innovationGameState->get('extra_achievement_to_win') > 1) {
            $this->innovationGameState->increment('number_of_achievements_needed_to_win');
        }

        // In a certain debug mode, make it so that achievements do not cause the game to end
        if ($this->innovationGameState->get('debug_mode') == 2) {
            $this->innovationGameState->set('number_of_achievements_needed_to_win', 100);
        }

        // Flag used to know if we are still on turn0 (1) or not (0)
        $this->innovationGameState->setInitial('turn0', 1);

        // Flags used to know if the player has one or two actions to perform
        $this->innovationGameState->setInitial('first_player_with_only_one_action', 1);
        $this->innovationGameState->setInitial('second_player_with_only_one_action', count($players) >= 4 ? 1 : 0); // used when >= 4 players only
        $this->innovationGameState->setInitial('has_second_action', 1);
        $this->innovationGameState->setInitial('current_action_number', -1);
        $this->innovationGameState->setInitial('endorse_action_state', 0);

        // Flags used when the game ends to know how it ended
        $this->innovationGameState->setInitial('game_end_type', -1); // 0 for game end by achievements, 1 for game end by score, -1 for game end by dogma
        $this->innovationGameState->setInitial('player_who_could_not_draw', -1); // When end of game by score, id of the player who triggered it

        // Flag used to remember whose turn it is
        $this->innovationGameState->setInitial('active_player', -1);

        // Flags used in dogma to remember player roles and which card it is, which effect (yet -1 as default value since there are not currently in use)
        $this->innovationGameState->setInitial('sharing_bonus', -1); // 1 if the dogma player will have a sharing bonus, else 0
        $this->innovationGameState->setInitial('dogma_had_impact', -1);
        $this->innovationGameState->setInitial('current_nesting_index', -1);
        self::DbQuery("
            INSERT INTO nested_card_execution (
                nesting_index,
                card_id,
                executing_as_if_on_card_id,
                launcher_id,
                current_player_id,
                current_effect_type,
                current_effect_number,
                step,
                step_max
            ) VALUES (0, -1, -1, -1, -1, -1, -1, -1, -1)");

        // Flag used for player interaction in dogma to remember what splay is proposed (-1 as default and if the choice does not involve splaying)
        $this->innovationGameState->setInitial('splay_direction', -1);

        // Flags used to describe the range of the selection the player in dogma must take (yet -1 as default value since there are not currently in use)
        $this->innovationGameState->setInitial('special_type_of_choice', -1); // Indicate the type of choice the player faces. See encodeSpecialTypeOfChoice() for possible values.
        $this->innovationGameState->setInitial('choice', -1); // Numeric choice when the player has to make a special choice (-2 if the player passed)
        $this->innovationGameState->setInitial('n_min', -1); // Minimal number of cards to be chosen (999 stands for all possible)
        $this->innovationGameState->setInitial('n_max', -1); // Maximal number of cards to be chosen (999 stands for no limit)
        $this->innovationGameState->setInitial('solid_constraint', -1); // 1 if there need to be at least n_min cards to trigger the effect or 0 if it is triggered no matter what, which will consume all eligible cards (do what you can rule)
        $this->innovationGameState->setInitial('owner_from', -1); // Owner from whom choose the card (0 for nobody, -2 for any player, -3 for any opponent, -4 for any other player)
        $this->innovationGameState->setInitial('location_from', -1); // Location from where choose the card (0 for deck, 1 for hand, 2 for board, 3 for score)
        $this->innovationGameState->setInitial('bottom_from', -1); // Whether the card must be taken from the bottom of the location (1) or not (0)
        $this->innovationGameState->setInitial('owner_to', -1); // Owner to whom the chosen card will be transfered (0 for nobody)
        $this->innovationGameState->setInitial('location_to', -1); // Location where the chosen card will be transfered (0 for deck, 1 for hand, 2 for board, 3 for score)
        $this->innovationGameState->setInitial('bottom_to', -1); // Whether the card will be placed at the bottom, typically for tucking or returning, (1) or not (0)
        $this->innovationGameState->setInitial('age_min', -1); // Age min of the card to be chosen
        $this->innovationGameState->setInitial('age_max', -1); // Age max of the card to be chosen
        $this->innovationGameState->setInitial('age_array', -1); // List of selectable ages encoded in a single value
        $this->innovationGameState->setInitial('color_array', -1); // List of selectable colors encoded in a single value
        $this->innovationGameState->setInitial('type_array', -1); // List of selectable types encoded in a single value
        $this->innovationGameState->setInitial('choice_array', -1); // List of selectable choices encoded in a single value
        $this->innovationGameState->setInitial('icon_array', -1); // List of selectable icons encoded in a single value
        $this->innovationGameState->setInitial('player_array', -1); // List of selectable players encoded in a single value (players are listed by their 0-based 'player_index', not their 'player_id')
        $this->innovationGameState->setInitial('with_icon', -1); // 0 if there is no specific icon for the card to be selected, else the number of the icon needed
        $this->innovationGameState->setInitial('with_icons', -1); // List of selectable icons encoded in a single value (but an empty list does not filter anything out)
        $this->innovationGameState->setInitial('without_icon', -1); // 0 if there is no specific icon for the card to be selected, else the number of the icon which can't be selected
        $this->innovationGameState->setInitial('without_icons', -1); // List of icons which are not selectable encoded in a single value
        $this->innovationGameState->setInitial('not_id', -1); // id of a card which cannot be selected, else -2
        $this->innovationGameState->setInitial('card_id_1', -1); // id of a card which is allowed to be selected, else -2
        $this->innovationGameState->setInitial('card_id_2', -1); // id of a card which is allowed to be selected, else -2
        $this->innovationGameState->setInitial('card_id_3', -1); // id of a card which is allowed to be selected, else -2
        $this->innovationGameState->setInitial('icon_hash_1', -1); // icon hash of a card which is allowed to be selected, else -1
        $this->innovationGameState->setInitial('icon_hash_2', -1); // icon hash of a card which is allowed to be selected, else -1
        $this->innovationGameState->setInitial('icon_hash_3', -1); // icon hash of a card which is allowed to be selected, else -1
        $this->innovationGameState->setInitial('icon_hash_4', -1); // icon hash of a card which is allowed to be selected, else -1
        $this->innovationGameState->setInitial('icon_hash_5', -1); // icon hash of a card which is allowed to be selected, else -1
        $this->innovationGameState->setInitial('enable_autoselection', -1); // 1 if cards are allowed to be autoselected during an interaction, 2 if forced autoselection should be used
        $this->innovationGameState->setInitial('include_relics', -1); // 1 if relics cards are allowed to be selected during an interaction
        $this->innovationGameState->setInitial('include_special_achievements', -1); // 1 if special achievements are allowed to be selected during an interaction
        $this->innovationGameState->setInitial('with_bonus', -1); // 1 if only cards with a bonus are allowed to be selected during an interaction
        $this->innovationGameState->setInitial('without_bonus', -1); // 1 if only cards without a bonus are allowed to be selected during an interaction
        $this->innovationGameState->setInitial('card_ids_are_in_auxiliary_array', -1); // 1 if only cards whose ID are in the auxiliary array are allowed to be selected during an interaction
        $this->innovationGameState->setInitial('can_pass', -1); // 1 if the player can pass else 0
        $this->innovationGameState->setInitial('n', -1); // Actual number of cards having being selected yet
        $this->innovationGameState->setInitial('id_last_selected', -1); // Id of the last selected card
        $this->innovationGameState->setInitial('age_last_selected', -1); // Age of the last selected card
        $this->innovationGameState->setInitial('color_last_selected', -1); // Color of the last selected card
        $this->innovationGameState->setInitial('owner_last_selected', -1); // Owner of the last selected card
        $this->innovationGameState->setInitial('score_keyword', -1); // 1 if the selected card is being scored, else 0
        $this->innovationGameState->setInitial('meld_keyword', -1); // 1 if the selected card is being melded, else 0
        $this->innovationGameState->setInitial('achieve_keyword', -1); // 1 if the selected card is being achieved, else 0
        $this->innovationGameState->setInitial('safeguard_keyword', -1); // 1 if the selected card is being safeguarded, else 0
        $this->innovationGameState->setInitial('draw_keyword', -1); // 1 if the selected card is being drawn, else 0
        $this->innovationGameState->setInitial('return_keyword', -1); // 1 if the selected card is being returned, else 0
        $this->innovationGameState->setInitial('foreshadow_keyword', -1); // 1 if the selected card is being foreshadowed, else 0
        $this->innovationGameState->setInitial('require_achievement_eligibility', -1); // 1 if the numeric achievement card can only be selected if the player is eligible to claim it based on their score
        $this->innovationGameState->setInitial('has_demand_effect', -1); // 1 if the card to be chosen must have a demand effect on it
        $this->innovationGameState->setInitial('has_splay_direction', -1); // List of splay directions encoded in a single value
        $this->innovationGameState->setInitial('refresh_selection', -1); // 1 if the selection should be refreshed after a choice is made, else 0
        $this->innovationGameState->setInitial('limit_shrunk_selection_size', -1); // Whether the safe/forecast limit shrunk the selection size (1 means it was shrunk)
        $this->innovationGameState->setInitial('reveal_if_unable', -1); // 1 if the zone should be revealed if the player is unable to perform the interaction, else 0

        // Flags specific to the meld action
        $this->innovationGameState->setInitial('relic_id', -1);
        $this->innovationGameState->setInitial('melded_card_id', -1);
        $this->innovationGameState->setInitial('foreseen_card_id', -1);

        $this->innovationGameState->setInitial('winner_by_dogma', -1);

        // Init game statistics
        self::initStat('table', 'turns_number', 0);
        self::initStat('player', 'turns_number', 0);
        self::initStat('table', 'actions_number', 0);
        self::initStat('player', 'actions_number', 0);

        self::initStat('table', 'end_achievements', false);
        self::initStat('table', 'end_score', false);
        self::initStat('table', 'end_dogma', false);
        self::initStat('table', 'fission_triggered', false);

        self::initStat('player', 'achievements_number', 0);
        self::initStat('player', 'score', 0);
        self::initStat('player', 'max_age_on_board', 0);
        self::initStat('player', 'draw_actions_number', 0);
        self::initStat('player', 'meld_actions_number', 0);
        self::initStat('player', 'dogma_actions_number', 0);
        self::initStat('player', 'achieve_actions_number', 0);
        self::initStat('player', 'special_achievements_number', 0);
        self::initStat('player', 'dogma_actions_number_with_i_demand', 0);
        self::initStat('player', 'dogma_actions_number_with_sharing', 0);
        self::initStat('player', 'i_demand_effects_number', 0);
        self::initStat('player', 'sharing_effects_number', 0);

        $edition = $this->innovationGameState->getEdition();

        if ($edition >= 4) {
            self::initStat('player', 'execution_combo_count', 0);
        }

        // Add cards from expansions that are in use.

        if ($this->innovationGameState->artifactsExpansionEnabled()) {
            self::DbQuery("UPDATE card SET location = 'deck', position = NULL WHERE 110 <= id AND id <= 214");
            if ($this->innovationGameState->artifactsExpansionEnabledWithRelics()) {
                self::DbQuery("UPDATE card SET location = 'relics', position = 0 WHERE is_relic");
            }
            if ($edition == 4) {
                self::DbQuery("UPDATE card SET location = 'museums' WHERE 1200 <= id AND id <= 1204");
                self::DbQuery("UPDATE card SET location = 'deck', position = NULL WHERE 450 <= id AND id <= 459");
                // TODO(4E): Implement Martian Internet later.
                self::DbQuery("UPDATE card SET location = 'removed' WHERE id = 451");
            }
            if ($edition <= 3) {
                self::DbQuery("UPDATE card SET spot_1 = 3, spot_2 = 3, spot_3 = 3, dogma_icon = 3 WHERE id = 206"); // Higgs Boson
                self::DbQuery("UPDATE card SET spot_1 = 1, spot_2 = 1, spot_4 = 1, dogma_icon = 1 WHERE id = 209"); // Maastricht Treaty
                self::DbQuery("UPDATE card SET spot_2 = 6 WHERE id = 212"); // Where's Waldo
                self::DbQuery("UPDATE card SET spot_2 = 5, spot_3 = 5, spot_4 = 6, dogma_icon = 5 WHERE id = 214"); // Twister
            }
        }

        if ($this->innovationGameState->citiesExpansionEnabled()) {
            self::DbQuery("UPDATE card SET location = 'deck', position = NULL WHERE 220 <= id AND id <= 324");
            if ($edition == 4) {
                self::DbQuery("UPDATE card SET location = 'deck', position = NULL WHERE 460 <= id AND id <= 469");
                // In the 4th editions, Cities cannot be dogma'd.
                self::DbQuery("UPDATE card SET dogma_icon = NULL WHERE 220 <= id AND id <= 324");
                self::DbQuery("UPDATE card SET dogma_icon = NULL WHERE 460 <= id AND id <= 469");
            }
            self::DbQuery("UPDATE card SET location = 'achievements' WHERE 325 <= id AND id <= 329");
            if ($edition <= 3) {
                self::DbQuery("UPDATE card SET spot_6 = 4 WHERE id = 221"); // Troy
                self::DbQuery("UPDATE card SET spot_6 = 14 WHERE id = 228"); // Babylon
                self::DbQuery("UPDATE card SET spot_3 = 5, spot_6 = 4 WHERE id = 237"); // Sparta
                self::DbQuery("UPDATE card SET spot_6 = 14 WHERE id = 243"); // Luoyang
                self::DbQuery("UPDATE card SET spot_6 = 14 WHERE id = 245"); // Hangzhou
                self::DbQuery("UPDATE card SET spot_1 = 1, spot_6 = 2 WHERE id = 252"); // Jakarta
                self::DbQuery("UPDATE card SET spot_3 = 14 WHERE id = 258"); // Milan
                self::DbQuery("UPDATE card SET spot_6 = 3 WHERE id = 264"); // Seville
                self::DbQuery("UPDATE card SET spot_6 = 8 WHERE id = 267"); // Zurich
                self::DbQuery("UPDATE card SET spot_6 = 14 WHERE id = 269"); // Amsterdam
                self::DbQuery("UPDATE card SET spot_6 = 8 WHERE id = 282"); // Dublin
                self::DbQuery("UPDATE card SET spot_3 = 14 WHERE id = 284"); // New York City
                self::DbQuery("UPDATE card SET spot_6 = 1 WHERE id = 286"); // Johannesburg
                self::DbQuery("UPDATE card SET spot_6 = 14 WHERE id = 288"); // Montreal
                self::DbQuery("UPDATE card SET spot_6 = 14 WHERE id = 289"); // London
                self::DbQuery("UPDATE card SET spot_6 = 6 WHERE id = 290"); // Toronto
                self::DbQuery("UPDATE card SET spot_6 = 1 WHERE id = 292"); // Melbourne
                self::DbQuery("UPDATE card SET spot_4 = 1, spot_6 = 1 WHERE id = 294"); // San Francisco
                self::DbQuery("UPDATE card SET spot_3 = 14 WHERE id = 295"); // Chongqing
                self::DbQuery("UPDATE card SET spot_4 = 5 WHERE id = 298"); // Los Angeles
                self::DbQuery("UPDATE card SET spot_6 = 9 WHERE id = 299"); // Hamburg
                self::DbQuery("UPDATE card SET spot_6 = 2 WHERE id = 300"); // São Paulo
                self::DbQuery("UPDATE card SET spot_6 = 2 WHERE id = 301"); // Chicago
                self::DbQuery("UPDATE card SET spot_6 = 6 WHERE id = 303"); // Buenos Aires
                self::DbQuery("UPDATE card SET spot_6 = 6 WHERE id = 305"); // Houston
                self::DbQuery("UPDATE card SET spot_6 = 5 WHERE id = 308"); // Perth
                self::DbQuery("UPDATE card SET spot_6 = 3 WHERE id = 309"); // Santiago
                self::DbQuery("UPDATE card SET spot_6 = 2 WHERE id = 312"); // Miami
                self::DbQuery("UPDATE card SET spot_3 = 14 WHERE id = 313"); // Hong Kong
                self::DbQuery("UPDATE card SET spot_6 = 14 WHERE id = 314"); // Moscow
                self::DbQuery("UPDATE card SET spot_6 = 9 WHERE id = 315"); // Bangalore
                self::DbQuery("UPDATE card SET spot_2 = 110, spot_6 = 6 WHERE id = 316"); // Atlanta
                self::DbQuery("UPDATE card SET spot_1 = 110, spot_6 = 5 WHERE id = 317"); // Singapore
                self::DbQuery("UPDATE card SET spot_1 = 2, spot_2 = 6, spot_4 = 6, spot_6 = 9 WHERE id = 318"); // Seoul
                self::DbQuery("UPDATE card SET spot_6 = 9 WHERE id = 319"); // Tel Aviv
                self::DbQuery("UPDATE card SET spot_4 = 1 WHERE id = 320"); // Bangkok
                self::DbQuery("UPDATE card SET spot_6 = 9 WHERE id = 321"); // Copenhagen
                self::DbQuery("UPDATE card SET spot_2 = 110, spot_6 = 1 WHERE id = 322"); // Dubai
                self::DbQuery("UPDATE card SET spot_2 = 6, spot_3 = 9, spot_4 = 5, spot_6 = 9 WHERE id = 323"); // Brussels
                self::DbQuery("UPDATE card SET spot_6 = 9 WHERE id = 324"); // Essen (renamed to Lagos in 4th edition)
            }
        }

        if ($this->innovationGameState->echoesExpansionEnabled()) {
            self::DbQuery("UPDATE card SET location = 'deck', position = NULL WHERE 330 <= id AND id <= 434");
            if ($edition == 4) {
                self::DbQuery("UPDATE card SET location = 'deck', position = NULL WHERE 470 <= id AND id <= 479");
            }
            self::DbQuery("UPDATE card SET location = 'achievements' WHERE 435 <= id AND id <= 439");
            if ($edition <= 3) {
                self::DbQuery("UPDATE card SET spot_2 = 3 WHERE id = 426"); // Human Genome
                self::DbQuery("UPDATE card SET spot_3 = 6, spot_4 = 6, dogma_icon = 6 WHERE id = 428"); // Social Networking
                self::DbQuery("UPDATE card SET spot_4 = 6 WHERE id = 431"); // Cell Phone
                self::DbQuery("UPDATE card SET spot_1 = 1, spot_3 = 0, spot_4 = 1, dogma_icon = 1 WHERE id = 432"); // MP3
                self::DbQuery("UPDATE card SET spot_1 = 3, spot_2 = 3, spot_4 = 3, dogma_icon = 3 WHERE id = 433"); // Puzzle Cube
                self::DbQuery("UPDATE card SET spot_3 = 3 WHERE id = 434"); // Sudoku
            }
        }

        if ($this->innovationGameState->unseenExpansionEnabled()) {
            self::DbQuery("UPDATE card SET location = 'deck', position = NULL WHERE 480 <= id AND id <= 594");
            self::DbQuery("UPDATE card SET location = 'achievements' WHERE 595 <= id AND id <= 599");
            // TODO(4E): Implement Hitchhiking and Teleprompter later.
            self::DbQuery("UPDATE card SET location = 'removed' WHERE id = 560 OR id = 570");
        }

        if ($edition <= 3) {
            // Certain cards got new symbols in the 4th edition, so we need to revert them when using an earlier edition
            self::DbQuery("UPDATE card SET spot_2 = 6 WHERE id = 96"); // Software
            self::DbQuery("UPDATE card SET spot_3 = 6 WHERE id = 98"); // Robotics
            self::DbQuery("UPDATE card SET spot_3 = 1 WHERE id = 100"); // Self Service
            self::DbQuery("UPDATE card SET spot_3 = 6, spot_4 = 3, dogma_icon = 6 WHERE id = 104"); // The Internet
            // Remove age 11 cards from play when using an earlier edition
            self::DbQuery("UPDATE card SET location = 'removed' WHERE age = 11");
        }

        // Initialize Artifacts-specific statistics
        if ($this->innovationGameState->artifactsExpansionEnabled()) {
            self::initStat('player', 'dig_events_number', 0);
            self::initStat('player', 'free_action_dogma_number', 0);
            self::initStat('player', 'free_action_return_number', 0);
            self::initStat('player', 'free_action_pass_number', 0);
            self::initStat('player', 'dogma_actions_number_targeting_artifact_on_board', 0);
            self::initStat('player', 'dogma_actions_number_with_i_compel', 0);
            self::initStat('player', 'i_compel_effects_number', 0);

            // Initialize Relic-specific statistics
            if ($this->innovationGameState->artifactsExpansionEnabledWithRelics()) {
                self::initStat('player', 'relics_seized_number', 0);
                self::initStat('player', 'relics_stolen_number', 0);
            }
        }

        // Initialize Cities-specific statistics
        if ($this->innovationGameState->citiesExpansionEnabled()) {
            self::initStat('player', 'endorse_actions_number', 0);
            self::initStat('player', 'city_cards_drawn_number', 0);
        }

        // Initialize Echoes-specific statistics
        if ($this->innovationGameState->echoesExpansionEnabled()) {
            self::initStat('player', 'foreshadowed_number', 0);
            self::initStat('player', 'promoted_number', 0);
            self::initStat('player', 'executed_echo_effect_number', 0);
        }

        // Store the age of each card when face-up
        self::DbQuery("UPDATE card SET faceup_age = (CASE id WHEN 188 THEN 11 ELSE age END)");

        // Card shuffling in decks
        self::shuffle();

        // Isolate one base card of each age (except the highest age) to create the available age achievements
        self::extractAgeAchievements();

        // Deal 2 cards of age 1 to each player
        foreach ($players as $player_id => $player) {
            $this->gamestate->changeActivePlayer($player_id);
            self::executeDraw($player_id, 1);
            self::executeDraw($player_id, 1);
        }

        // Add information to the database about which cards have a demand.
        foreach ($this->textual_card_infos as $id => $card_info) {
            if (self::getDemandEffect($id) || self::getCompelEffect($id)) {
                self::DbQuery(self::format("UPDATE card SET has_demand = TRUE WHERE id = {id}", array("id" => $id)));
            }
        }

        // Activate first player
        $this->activeNextPlayer();

        /************ End of the game initialization *****/
    }

    /*
        getAllDatas: 

        Gather all informations about current game situation (visible by the current player).

        The method is called each time the game interface is displayed to a player, ie:
        _ when the game starts
        _ when a player refreshes the game page (F5)
    */
    protected function getAllDatas()
    {
        $result = array();

        $result['debug_mode'] = $this->innovationGameState->get('debug_mode');

        // Get static information about all cards
        $cards = array();
        foreach (self::getStaticInfoOfAllCards() as $card) {
            $cards[$card['id']] = $card;
        }
        $result['cards'] = $cards;

        $result['fourth_edition'] = $this->innovationGameState->usingFourthEditionRules();
        $result['artifacts_expansion_enabled'] = $this->innovationGameState->artifactsExpansionEnabled();
        $result['relics_enabled'] = $this->innovationGameState->artifactsExpansionEnabledWithRelics();
        $result['cities_expansion_enabled'] = $this->innovationGameState->citiesExpansionEnabled();
        $result['echoes_expansion_enabled'] = $this->innovationGameState->echoesExpansionEnabled();
        // TODO(FIGURES): Update this when the expansion is added.
        $result['figures_expansion_enabled'] = false;
        $result['unseen_expansion_enabled'] = $this->innovationGameState->unseenExpansionEnabled();

        $current_player_id = self::getCurrentPlayerId(); // !! We must only return information visible by this player !!

        // Get information about players
        $players = self::getCollectionFromDb("SELECT player_id, player_score, player_team, player_color FROM player");
        foreach ($players as $player_id => $player) {
            $result['players'][$player_id]['achievement_count'] = (Integer) ($player['player_score']);
            $result['players'][$player_id]['player_team'] = (Integer) ($player['player_team']);
        }

        // Public information

        // Number of achievements needed to win
        $result['number_of_achievements_needed_to_win'] = $this->innovationGameState->get('number_of_achievements_needed_to_win');

        // All boards
        $result['board'] = self::getBoards(self::getAllPlayerIds());

        // Splay state for stacks on board
        $result['board_splay_directions'] = array();
        $result['board_splay_directions_in_clear'] = array();
        foreach ($players as $player_id => $player) {
            $result['board_splay_directions'][$player_id] = array();
            $result['board_splay_directions_in_clear'][$player_id] = array();
            foreach (Colors::ALL as $color) {
                $direction = self::getCurrentSplayDirection($player_id, $color);
                $result['board_splay_directions'][$player_id][] = $direction;
                $result['board_splay_directions_in_clear'][$player_id][] = Directions::render($direction);
            }
        }

        // Artifacts on display
        $result['artifacts_on_display'] = self::getArtifactsOnDisplay($players);
        $result['artifacts_in_museums'] = self::getArtifactsInAllMuseums($players);

        // Backs of the cards in junk
        $result['junk_counts'] = array();
        for ($type = 0; $type <= 5; $type++) {
            for ($is_relic = 0; $is_relic <= 1; $is_relic++) {
                $result['junk_counts'][$type][$is_relic] = self::countCardsInLocationKeyedByAge(0, 'junk', $type, $is_relic);
            }
        }

        // Backs of the cards in hands
        $result['hand_counts'] = array();
        for ($type = 0; $type <= 5; $type++) {
            for ($is_relic = 0; $is_relic <= 1; $is_relic++) {
                foreach ($players as $player_id => $player) {
                    $result['hand_counts'][$player_id][$type][$is_relic] = self::countCardsInLocationKeyedByAge($player_id, 'hand', $type, $is_relic);
                }
            }
        }

        // Backs of the cards in each player's safe
        $result['safe_counts'] = array();
        for ($type = 0; $type <= 5; $type++) {
            foreach ($players as $player_id => $player) {
                $result['safe_counts'][$player_id][$type] = self::countCardsInLocationKeyedByAge($player_id, 'safe', $type, /*is_relic=*/ 0);
            }
        }

        // Backs of the cards in forecast piles
        $result['forecast_counts'] = array();
        for ($type = 0; $type <= 5; $type++) {
            for ($is_relic = 0; $is_relic <= 1; $is_relic++) {
                foreach ($players as $player_id => $player) {
                    $result['forecast_counts'][$player_id][$type][$is_relic] = self::countCardsInLocationKeyedByAge($player_id, 'forecast', $type, $is_relic);
                }
            }
        }

        // Backs of the cards in score piles
        $result['score_counts'] = array();
        for ($type = 0; $type <= 5; $type++) {
            for ($is_relic = 0; $is_relic <= 1; $is_relic++) {
                foreach ($players as $player_id => $player) {
                    $result['score_counts'][$player_id][$type][$is_relic] = self::countCardsInLocationKeyedByAge($player_id, 'score', $type, $is_relic);
                }
            }
        }

        // Score (totals in the score piles) for each player
        $result['score'] = array();
        foreach ($players as $player_id => $player) {
            $result['score'][$player_id] = self::getPlayerScore($player_id);
        }

        // Revealed cards
        $result['revealed'] = array();
        foreach ($players as $player_id => $player) {
            $result['revealed'][$player_id] = self::getCardsInLocation($player_id, 'revealed');
        }

        // Unclaimed relics
        $result['unclaimed_relics'] = self::getCardsInLocation(0, Locations::RELICS);

        // Unclaimed museums
        $result['unclaimed_museums'] = self::getCardsInLocation(0, Locations::MUSEUMS);

        // Unclaimed achievements
        // TODO(#229): Deprecate this and add a new unclaimed_special_achievements entry.
        $result['unclaimed_achievements'] = self::getCardsInLocation(0, 'achievements');
        $result['unclaimed_standard_achievement_counts'] = array();
        for ($type = 0; $type <= 5; $type++) {
            for ($is_relic = 0; $is_relic <= 1; $is_relic++) {
                $result['unclaimed_standard_achievement_counts'][$type][$is_relic] = self::countCardsInLocationKeyedByAge(0, 'achievements', $type, $is_relic);
            }
        }

        // Claimed achievements for each player
        // TODO(#229): Pass counts instead of list of cards (the flags and fountains will still need the full cards passed).
        $result['claimed_achievements'] = array();
        foreach ($players as $player_id => $player) {
            $result['claimed_achievements'][$player_id] = self::getCardsInLocation($player_id, 'achievements');
        }

        // Ressources for each player
        $result['ressource_counts'] = array();
        foreach ($players as $player_id => $player) {
            $result['ressource_counts'][$player_id] = self::getPlayerResourceCounts($player_id);
        }

        // Max age on board for each player
        $result['max_age_on_board'] = array();
        foreach ($players as $player_id => $player) {
            $result['max_age_on_board'][$player_id] = self::getMaxAgeOnBoardTopCards($player_id);
        }

        // Remaining cards in deck
        for ($type = 0; $type <= 5; $type++) {
            $result['deck_counts'][$type] = self::countCardsInLocationKeyedByAge(0, 'deck', $type);
        }

        // Turn0 or not
        $result['turn0'] = $this->innovationGameState->get('turn0') == 1;

        // Number of achievements needed to win
        $result['number_of_achievements_needed_to_win'] = $this->innovationGameState->get('number_of_achievements_needed_to_win');

        // Link to the current dogma effect (if any)
        $nested_card_state = self::getCurrentNestedCardState();
        if ($nested_card_state == null) {
            $JSCardEffectQuery = null;
        } else {
            $current_effect_type = $nested_card_state['current_effect_type'];
            $current_effect_number = $nested_card_state['current_effect_number'];
            $card_id = $nested_card_state['card_id'];

            // Echo effects are sometimes executed on cards other than the card being dogma'd
            if ($current_effect_type == 3) {
                $nesting_index = $nested_card_state['nesting_index'];
                $card_id = self::getUniqueValueFromDB(
                    self::format(
                        "SELECT card_id FROM echo_execution WHERE nesting_index = {nesting_index} AND execution_index = {effect_number}",
                        array('nesting_index' => $nesting_index, 'effect_number' => $current_effect_number)
                    )
                );
            }

            $JSCardEffectQuery = $card_id == -1 ? null : self::getJSCardEffectQuery(self::getCardInfo($card_id), $current_effect_type, $current_effect_number);
        }
        $result['JSCardEffectQuery'] = $JSCardEffectQuery;

        // Whose turn is it?
        $active_player = $this->innovationGameState->get('active_player');
        $result['active_player'] = $active_player == -1 ? null : $active_player;
        if ($active_player != -1) {
            $action_number = $this->innovationGameState->get('current_action_number');
            $result['action_number'] = $action_number;
            $card = self::getArtifactOnDisplay($active_player);
            if ($card !== null && $this->gamestate->state()['name'] == 'artifactPlayerTurn') {
                $result['artifact_on_display_icons'] = array();
                $result['artifact_on_display_icons']['resource_icon'] = $card['dogma_icon'];
                $result['artifact_on_display_icons']['resource_count_delta'] = self::countIconsOnCard($card, $card['dogma_icon']);
            }
        }

        // Private information
        $result['my_hand'] = Arrays::flatten(self::getCardsInLocationKeyedByAge($current_player_id, 'hand'));
        $result['my_forecast'] = Arrays::flatten(self::getCardsInLocationKeyedByAge($current_player_id, 'forecast'));
        $result['my_score'] = Arrays::flatten(self::getCardsInLocationKeyedByAge($current_player_id, 'score'));
        $result['my_safe'] = Arrays::flatten(self::getCardsInLocationKeyedByAge($current_player_id, 'safe'));

        // My wish for splay
        $result['display_mode'] = self::getPlayerWishForSplay($current_player_id);
        $result['view_full'] = self::getPlayerWishForViewFull($current_player_id);

        // Counters used for the Monument special achievement
        $result['monument_counters'] = self::getFlagsForMonument($current_player_id);

        return $result;
    }

    /*
        getGameProgression:

        Compute and return the current game progression.
        The number returned must be an integer beween 0 (=the game just started) and
        100 (= the game is finished or almost finished).

        This method is called each time we are in a game state with the "updateGameProgression" property set to true 

    */
    function getGameProgression()
    {
        // Start or end of game
        $current_state = $this->gamestate->state();
        switch ($current_state['name']) {
            case 'gameSetup':
            case 'turn0':
                return 0;
            case 'whoBegins':
                return 1;
            case 'justBeforeGameEnd':
            case 'gameEnd':
                return 100;
        }
        // For other states (all included in player action)
        $players = self::loadPlayersBasicInfos();

        // The total progression is a mix of:
        // -the progression of the decreasing number of cards in deck (end of game by score)
        // -the progression of each player in terms of the achievements they get

        // Progression in cards
        // Hypothesis: a card of age 9 is drawn three times quicker than a card of age 1. Cards of age 10 are worth six times a card of age 1 because if there are none left it is the end of the game
        $weight = 0;
        $total_weight = 0;

        // TODO(4E): Update game progression calculations.
        $number_of_cards_in_decks = self::countCardsInLocationKeyedByAge(0, 'deck', CardTypes::BASE);
        for ($age = 1; $age <= 10; $age++) {
            $n = $number_of_cards_in_decks[$age];
            switch ($age) {
                case 1:
                    $n_max = 14 - 2 * count($players); // number of cards in the deck at the beginning: 14 (15 minus one taken for achievement) minus the cards dealt to the players at the beginning
                    $w = 1; // weight for cards of age 1: 1
                    break;
                case 10:
                    $n++; // one more "virtual" card because the game is not over where the tenth age 10 card is drawn (but quite...)
                    $n_max = 11; // number of cards in the deck at the beginning: 10, +1 one more "virtual" card because the game is not over when the last is drawn
                    $w = 6; // weight for cards of age 10: 6
                    break;
                default:
                    $n_max = 9; // number of cards in the deck at the beginning: 9 (10 minus one taken for achievement)
                    $w = ($age - 1) / 4 + 1; // weight between 1.25 (for age 2) and 3 (for age 9)
                    break;
            }
            ;
            $weight += ($n_max - $n) * $w; // What is really important are the cards already drawn
            $total_weight += $n_max * $w;
        }
        $progression_in_cards = $weight / $total_weight;

        // Progression of players
        // This is the ratio between the number of achievements the player have got so far and the number of achievements needed to win the game
        $progression_of_players = array();
        $n_max = $this->innovationGameState->get('number_of_achievements_needed_to_win');
        foreach ($players as $player_id => $player) {
            $n = self::getPlayerNumberOfAchievements($player_id);
            $progression_of_players[] = $n / $n_max;
        }

        // If any of the above progression was 100%, the game would be over. So, 100% is a kind of "absorbing" element. So,the method is to multiply the complements of the progression.
        // A complement is defined as 100% - progression
        $complement = 1 - $progression_in_cards;
        foreach ($progression_of_players as $progression) {
            $complement *= 1 - $progression;
        }
        $final_progression = 1 - $complement;

        // Convert the final result in percentage
        $percentage = intval(100 * $final_progression);
        $percentage = min(max(1, $percentage), 99); // Set that progression between 1% and 99%
        return $percentage;
    }

    /** integer division **/
    function intDivision($a, $b)
    {
        return (int) ($a / $b);
    }

    /** Returns the card types in use by the current game **/
    function getActiveCardTypes()
    {
        $active_types = array(0);
        if ($this->innovationGameState->artifactsExpansionEnabled()) {
            $active_types[] = 1;
        }
        if ($this->innovationGameState->citiesExpansionEnabled()) {
            $active_types[] = 2;
        }
        if ($this->innovationGameState->echoesExpansionEnabled()) {
            $active_types[] = 3;
        }
        // TODO(FIGURES): Update this when implementing the expansion.
        if ($this->innovationGameState->unseenExpansionEnabled()) {
            $active_types[] = 5;
        }
        return $active_types;
    }

    function calculatePlayerIndexes()
    {
        $player_nos = self::getObjectListFromDB("SELECT player_no FROM player ORDER BY player_no", true);
        $index = 0;
        foreach ($player_nos as $player_no) {
            self::DbQuery(
                self::format(
                    "UPDATE player SET player_index = {player_index} WHERE player_no = {player_no}",
                    array('player_index' => $index++, 'player_no' => $player_no)
                )
            );
        }
    }

    function playerIdToPlayerIndex($player_id)
    {
        return self::getUniqueValueFromDB(self::format("SELECT player_index FROM player WHERE player_id = {player_id}", array('player_id' => $player_id)));
    }

    function playerIndexToPlayerId($player_index)
    {
        return self::getUniqueValueFromDB(self::format("SELECT player_id FROM player WHERE player_index = {player_index}", array('player_index' => $player_index)));
    }

    function getAllPlayerIds()
    {
        return self::getObjectListFromDB("SELECT player_id FROM player", true);
    }

    function getAllActivePlayerIds()
    {
        return self::getObjectListFromDB("SELECT player_id FROM player WHERE player_eliminated = 0", true);
    }

    function getOtherActivePlayerIds($player_id)
    {
        return self::getObjectListFromDB(self::format("
            SELECT
                player_id
            FROM
                player
            WHERE
                player_eliminated = 0 AND
                player_id <> {player_id}
        ", array('player_id' => $player_id)), true);
    }

    function getActiveOpponentIds($player_id)
    {
        return self::getObjectListFromDB(self::format("
            SELECT
                player_id
            FROM
                player
            WHERE
                player_eliminated = 0 AND
                player_team <> (
                    SELECT
                        player_team
                    FROM
                        player
                    WHERE
                        player_id = {player_id}
                )
        ", array('player_id' => $player_id)), true);
    }

    function getAllActivePlayers()
    {
        return self::getObjectListFromDB("SELECT player_index FROM player WHERE player_eliminated = 0", true);
    }

    function getOtherActivePlayers($player_id)
    {
        return self::getObjectListFromDB(self::format("
            SELECT
            player_index
            FROM
                player
            WHERE
                player_eliminated = 0 AND
                player_id <> {player_id}
        ", array('player_id' => $player_id)), true);
    }

    function getActiveOpponents($player_id)
    {
        return self::getObjectListFromDB(self::format("
            SELECT
                player_index
            FROM
                player
            WHERE
                player_eliminated = 0 AND
                player_team <> (
                    SELECT
                        player_team
                    FROM
                        player
                    WHERE
                        player_id = {player_id}
                )
        ", array('player_id' => $player_id)), true);
    }

    function isEliminated($player_id)
    {
        return self::getUniqueValueFromDB(self::format("SELECT player_eliminated FROM player WHERE player_id={player_id}", array('player_id' => $player_id)));
    }

    // TODO(LATER): Use this helper more.
    function isTeamGame()
    {
        return self::decodeGameType($this->innovationGameState->get('game_type')) == 'team';
    }

    /** Formatting **/
    // TODO(LATER): Remove this once we are using the function in Strings.php instead.
    static function format($msg, $vars)
    {
        /** Format the string using named or unamed parameters **/
        $vars = (array) $vars;

        $msg = preg_replace_callback('#\{\}#', function ($r) {
            static $i = 0;
            return '{' . ($i++) . '}';
        }, $msg);

        return str_replace(
            array_map(function ($k) {
                return '{' . $k . '}';
            }, array_keys($vars)),

            array_values($vars),

            $msg
        );
    }

    /** Utility for team game **/
    function rearrangePlayersForFixedTeams($player_array, $partnair_of_first)
    {
        // The goal of this function is to rearrange the player array so that the first player in the lobby plays with his partnair ($partnair_of_first) as decide in the options

        // "Fix" the player table order so that it is in the range 1..nbr_players
        $unfixed_player_table_orders = array();
        foreach ($player_array as $player_id => $player) {
            $unfixed_player_table_orders[] = $player["player_table_order"];
        }
        sort($unfixed_player_table_orders);
        $fixed_player_table_orders = array();
        foreach ($unfixed_player_table_orders as $key => $val) {
            $fixed_player_table_orders[$val] = $key + 1;
        }

        // Locate who was the first player in the lobby
        $player_no = 0;
        foreach ($player_array as $player_id => $player) {
            $player_table_order = $fixed_player_table_orders[$player['player_table_order']];
            if ($player_table_order == 1) {
                $first_player_no = $player_no;
            } else if ($player_table_order == $partnair_of_first) {
                $partnair_of_first_no = $player_no;
            }
            $player_no++;
        }

        // Check if the playing order needs to be changed
        if (($first_player_no + 2) % 4 == $partnair_of_first_no) { // Players are already on their right places (teammates are facing each other)
            return $player_array;
        }

        // Determine the neighbor of the first player in the lobby who is an opponent
        if (($first_player_no + 1) % 4 == $partnair_of_first_no) {
            $neighbor_opponent_no = ($first_player_no + 3) % 4;
        } else {
            $neighbor_opponent_no = ($first_player_no + 1) % 4;
        }

        // Swap the seats of the first player in the lobby with that opponent
        $player_order = array_keys($player_array);

        $first_player_id = $player_order[$first_player_no];
        $neighbor_opponent_id = $player_order[$neighbor_opponent_no];
        $player_order[$first_player_no] = $neighbor_opponent_id;
        $player_order[$neighbor_opponent_no] = $first_player_id;

        // Made this change effective in the whole array
        $new_player_array = array();
        foreach ($player_order as $player_id) {
            $new_player_array[$player_id] = $player_array[$player_id];
        }
        return $new_player_array;
    }

    /** Database manipulations **/
    function shuffle()
    {
        /** Shuffle all cards in their piles grouped by type and age, at the beginning of the game **/

        // Generate a random number for each card in the deck
        self::DbQuery("
        INSERT INTO random
            SELECT
                id,
                type,
                age,
                RAND() AS random_number
            FROM
                card 
            WHERE
                location = 'deck'
        ");

        // Give the new position based on the random number of the card, in the type and age pile it belongs to
        self::DbQuery("
        INSERT INTO shuffled
            SELECT
                a.id,
                (
                    SELECT
                        COUNT(*)
                    FROM
                        random AS b
                    WHERE
                        b.age = a.age AND
                        b.type = a.type AND
                        b.random_number < a.random_number
                ) AS new_position
            FROM
                random AS a
        ");

        // Assign this new position to the actual database
        self::DbQuery("
        UPDATE
            card AS a
            INNER JOIN shuffled AS b
                ON a.id = b.id
        SET
            a.position = b.new_position
        WHERE
            b.new_position IS NOT NULL
        ");

        // Empty auxiliary tables
        self::DbQuery("
        DELETE FROM
            random
        ");

        self::DbQuery("
        DELETE FROM
            shuffled
        ");
    }

    function extractAgeAchievements()
    {
        /** Take the top card from each pile from age 1 to age 9, in the beginning of the game; these will be used as achievements **/
        self::DbQuery(self::format("
            UPDATE
                card as a
                INNER JOIN (SELECT age, MAX(position) AS position FROM card WHERE type = 0 GROUP BY age) as b ON a.age = b.age
            SET
                a.location = 'achievements',
                a.position = 0
            WHERE
                a.position = b.position AND
                a.type = 0 AND
                a.age BETWEEN 1 AND {max_achievement_age}
            ", ["max_achievement_age" => $this->innovationGameState->usingFourthEditionRules() ? 10 : 9]));
    }

    function tuckCard($card, $owner_to): ?array
    {
        return self::transferCardFromTo($card, $owner_to, 'board', ['bottom_to' => true]);
    }

    function scoreCard(array $card, int $owner_to, array $properties = []): ?array
    {
        return self::transferCardFromTo($card, $owner_to, 'score', array_merge($properties, ['score_keyword' => true]));
    }

    function meldCard($card, $owner_to): ?array
    {
        return self::transferCardFromTo($card, $owner_to, 'board', ['bottom_to' => false, 'meld_keyword' => true]);
    }

    function returnCard($card): ?array
    {
        return self::transferCardFromTo($card, 0, 'deck', ['return_keyword' => true]);
    }

    function digCard($card, $owner_to): ?array
    {
        return self::transferCardFromTo($card, $owner_to, "display");
    }

    function foreshadowCard($card, $owner_to): ?array
    {
        return self::transferCardFromTo($card, $owner_to, 'forecast', ['foreshadow_keyword' => true]);
    }

    function junkCard(array $card, array $properties = []): ?array
    {
        return self::transferCardFromTo($card, 0, 'junk', $properties);
    }

    function removeCard(array $card, array $properties = []): ?array
    {
        return self::transferCardFromTo($card, 0, 'removed', $properties);
    }

    function safeguardCard($card, $owner_to): ?array
    {
        return self::transferCardFromTo($card, $owner_to, 'safe', ['safeguard_keyword' => true]);
    }

    function putCardBackInSafe($card, $owner_to): ?array
    {
        return self::transferCardFromTo($card, $owner_to, 'safe', ['safeguard' => false, 'force' => true]);
    }

    function bulkTransferCards(array $cards, int $owner_to, string $location_to, array $properties = []): bool
    {
        if (!$cards) {
            return false;
        }
        // NOTE: The caller is responsible for printing any relevant messages to the game log.
        for ($i = 0; $i < count($cards); $i++) {
            self::transferCardFromTo($cards[$i], $owner_to, $location_to, array_merge($properties, ['bulk_transfer' => true, 'last_card_of_bulk_transfer' => $i == count($cards) - 1]));
        }
        return true;
    }

    /**
     * Executes the transfer of the card, returning the new card info.
     **/
    function transferCardFromTo(?array $card, int $owner_to, string $location_to, array $properties = []): ?array
    {
        if (!$card) {
            return null;
        }

        $bottom_from = array_key_exists('bottom_from', $properties) ? $properties['bottom_from'] : false;
        $bottom_to = array_key_exists('bottom_to', $properties) ? $properties['bottom_to'] : $location_to == 'deck' && !$card['is_relic'];
        $score_keyword = array_key_exists('score_keyword', $properties) ? $properties['score_keyword'] : false;
        $meld_keyword = array_key_exists('meld_keyword', $properties) ? $properties['meld_keyword'] : false;
        $achieve_keyword = array_key_exists('achieve_keyword', $properties) ? $properties['achieve_keyword'] : $location_to == 'achievements' && $owner_to != 0;
        $draw_keyword = array_key_exists('draw_keyword', $properties) ? $properties['draw_keyword'] : $card['location'] == 'deck';
        $safeguard_keyword = array_key_exists('safeguard_keyword', $properties) ? $properties['safeguard_keyword'] : $location_to == 'safe';
        $return_keyword = array_key_exists('return_keyword', $properties) ? $properties['return_keyword'] : $location_to == 'deck';
        $foreshadow_keyword = array_key_exists('foreshadow_keyword', $properties) ? $properties['foreshadow_keyword'] : $location_to == 'forecast';
        $force = array_key_exists('force', $properties) ? $properties['force'] : false;
        $bulk_transfer = array_key_exists('bulk_transfer', $properties) ? $properties['bulk_transfer'] : false;
        $last_card_of_bulk_transfer = array_key_exists('last_card_of_bulk_transfer', $properties) ? $properties['last_card_of_bulk_transfer'] : false;
        $player_already_lost = array_key_exists('player_already_lost', $properties) ? $properties['player_already_lost'] : false;

        if (self::getGameStateValue('debug_mode') >= 1 && !array_key_exists('using_debug_buttons', $card)) {
            error_log("  - Transferring " . self::getCardName($card['id']) . " from " . $card['owner'] . "'s " . $card['location'] . " to " . $owner_to . "'s " . $location_to);
        }

        // Get updated state of card in case a stale reference was passed.
        $using_debug_buttons = array_key_exists('using_debug_buttons', $card);
        $card = self::getCardInfo($card['id']);
        if ($using_debug_buttons) {
            $card['using_debug_buttons'] = true;
        }

        // Do not move the card at all.
        if ($location_to == 'none') {
            return null;
        }

        // Do not move the card if the was was supposed to move to the safe but it is already full (unless we are returning the card to the safe after it was revealed)
        if (!$force && $location_to == 'safe' && self::countCardsInLocation($owner_to, 'safe') >= self::getForecastAndSafeLimit($owner_to)) {
            $this->notifications->notifyLocationFull(clienttranslate('safe'), $owner_to);
            return null;
        }

        // Do not move the card if the was was supposed to move to the forecast but it is already full (unless we are returning the card to the forecast after it was revealed)
        if (!$force && $location_to == 'forecast' && $this->innovationGameState->usingFourthEditionRules() && self::countCardsInLocation($owner_to, 'forecast') >= self::getForecastAndSafeLimit($owner_to)) {
            $this->notifications->notifyLocationFull(clienttranslate('forecast'), $owner_to);
            return null;
        }

        // Players can only draw an Unseen card on the first draw of a turn
        if ($card['location'] == 'deck') {
            // TODO(4E): Is there a bug here when there is the "look" keyword?
            self::setPlayerWillDrawUnseenCardNext($owner_to, false);
        }

        // Relics are not returned to the deck.
        if ($card['is_relic'] && $location_to == 'deck') {
            $location_to = 'relics';
        }

        $id = $card['id'];
        $age = $card['age'];
        $type = $card['type'];
        $is_relic = $card['is_relic'];
        $color = $card['color'];
        $owner_from = $card['owner'];
        $location_from = $card['location'];
        $position_from = $card['position'];
        $splay_direction_from = $card['splay_direction'];

        // Determine the splay direction of destination if any
        if ($location_to == 'board') {
            // The card must continue the current splay
            $splay_direction_to = self::getCurrentSplayDirection($owner_to, $color);
        } else {
            $splay_direction_to = 'NULL';
        }

        // Filter from
        $filter_from = self::format("owner = {owner_from} AND location = '{location_from}'", array('owner_from' => $owner_from, 'location_from' => $location_from));
        switch ($location_from) {
            case 'deck':
                $filter_from .= self::format(" AND type = {type} AND age = {age}", array('type' => $type, 'age' => $age));
                break;
            case 'achievements':
                if ($age == null) {
                    break;
                }
                // The player's achievement pile is not grouped by type or age
                if ($owner_from != 0) {
                    break;
                }
            case 'hand':
            case 'forecast':
            case 'score':
            case 'safe':
            case 'relics':
            case 'junk':
                // Special achievements aren't grouped
                if ($age == null) {
                    break;
                }
                $filter_from .= self::format(" AND type = {type} AND age = {age} AND is_relic = {is_relic}", array('type' => $type, 'age' => $age, 'is_relic' => $is_relic));
                break;
            case 'board':
                $filter_from .= self::format(" AND color = {color}", array('color' => $color));
                break;
            case 'removed':
                $filter_from = self::format("id = {id}", array('id' => $id)); // Always use position 0
                break;
            default:
                break;
        }

        // Filter to
        $filter_to = self::format("owner = {owner_to} AND location = '{location_to}'", array('owner_to' => $owner_to, 'location_to' => $location_to));
        switch ($location_to) {
            case 'deck':
                $filter_to .= self::format(" AND type = {type} AND age = {age}", array('type' => $type, 'age' => $age));
                break;
            case 'achievements':
                // Special achievements aren't grouped
                if ($age == null) {
                    break;
                }
                // The player's achievement pile is not grouped by type or age
                if ($owner_to != 0) {
                    break;
                }
            case 'hand':
            case 'forecast':
            case 'score':
            case 'safe':
            case 'relics':
            case 'junk':
                // Special achievements aren't grouped
                if ($age == null) {
                    break;
                }
                $filter_to .= self::format(" AND type = {type} AND age = {age} AND is_relic = {is_relic}", array('type' => $type, 'age' => $age, 'is_relic' => $is_relic));
                break;
            case 'board':
                $filter_to .= self::format(" AND color = {color}", array('color' => $color));
                break;
            case 'removed':
                $filter_to = self::format("id = {id}", array('id' => $id)); // Always use position 0
                break;
            default:
                break;
        }

        // Get the position of destination and update some other card positions if needed
        if ($bottom_to) { // The card must go to bottom of the location: update the position of the other cards accordingly
            // Execution of the query
            self::DbQuery(
                self::format("
                UPDATE
                    card
                SET
                    position = position + 1
                WHERE
                    {filter_to}
            ",
                    array('filter_to' => $filter_to)
                )
            );
            $position_to = 0;
        } else { // $bottom_to is false
            // new_position = number of cards in the location
            $position_to = self::getUniqueValueFromDB(
                self::format("
            SELECT
                COUNT(position)
            FROM
                card
            WHERE
                {filter_to}
            ",
                    array('filter_to' => $filter_to)
                )
            );
        }

        // Execute the transfer
        self::DbQuery(
            self::format("
            UPDATE
                card
            SET
                owner = {owner_to},
                location = '{location_to}',
                position = {position_to},
                selected = FALSE,
                splay_direction = {splay_direction_to}
            WHERE
                id = {id}
        ",
                array('owner_to' => $owner_to, 'location_to' => $location_to, 'position_to' => $position_to, 'id' => $id, 'splay_direction_to' => $splay_direction_to)
            )
        );

        // Update the position of the cards of the location the transferred card came from to fill the gap
        self::DbQuery(
            self::format("
            UPDATE
                card
            SET
                position = position - 1 
            WHERE
                {filter_from} AND
                position > {position_from}
        ",
                array('filter_from' => $filter_from, 'position_from' => $position_from)
            )
        );

        if ($location_to == 'forecast') {
            self::incStat(1, 'foreshadowed_number', $owner_to);
        }

        $transferInfo = array(
            'owner_from'                 => $owner_from,
            'location_from'              => $location_from,
            'position_from'              => $position_from,
            'splay_direction_from'       => $splay_direction_from,
            'owner_to'                   => $owner_to,
            'location_to'                => $location_to,
            'position_to'                => $position_to,
            'splay_direction_to'         => $splay_direction_to,
            'bottom_from'                => $bottom_from,
            'bottom_to'                  => $bottom_to,
            'score_keyword'              => $score_keyword,
            'meld_keyword'               => $meld_keyword,
            'achieve_keyword'            => $achieve_keyword,
            'draw_keyword'               => $draw_keyword,
            'safeguard_keyword'          => $safeguard_keyword,
            'return_keyword'             => $return_keyword,
            'foreshadow_keyword'         => $foreshadow_keyword,
            'bulk_transfer'              => $bulk_transfer,
            'last_card_of_bulk_transfer' => $last_card_of_bulk_transfer,
        );

        // Update the current state of the card
        $card['owner'] = $owner_to;
        $card['location'] = $location_to;
        $card['position'] = $position_to;
        $card['splay_direction'] = $splay_direction_to;

        $current_state = $this->gamestate->state();
        if ($current_state['name'] != 'gameSetup') {
            try {
                self::updateGameSituation($card, $transferInfo);
                if ($card['type'] == CardTypes::CITIES && !$player_already_lost) {
                    if ($location_from == 'hand' && $location_to == 'junk') {
                        if (self::hasRessource($card, 8)) { // has a flag
                            self::claimSpecialAchievement($owner_from, 328); // Glory (4th edition)
                        }
                        if (self::hasRessource($card, 9)) { // has a fountain
                            self::claimSpecialAchievement($owner_from, 329); // Victory (4th edition)
                        }
                    } else if ($location_to == 'board' && $bottom_to && $this->innovationGameState->getEdition() <= 3) { // tuck
                        if (self::hasRessource($card, 8)) { // has a flag
                            self::claimSpecialAchievement($owner_to, 328); // Glory
                        }
                        if (self::hasRessource($card, 9)) { // has a fountain
                            self::claimSpecialAchievement($owner_to, 329); // Victory
                        }
                    } else if ($location_to == 'board' && $meld_keyword) { // meld
                        $current_splay_direction = self::getCurrentSplayDirection($owner_to, $card['color']);
                        if (self::hasRessource($card, 11) && $current_splay_direction == 1) { // has a left arrow and already splayed left
                            self::claimSpecialAchievement($owner_to, 325); // Legend
                        }
                        if (self::hasRessource($card, 12) && $current_splay_direction == 2) { // has a right arrow and already splayed right
                            self::claimSpecialAchievement($owner_to, 326); // Repute
                        }
                        if (self::hasRessource($card, 13) && $current_splay_direction == 3) { // has an up arrow and already splayed up
                            self::claimSpecialAchievement($owner_to, 327); // Fame
                        }
                    }
                }
            } catch (EndOfGame $e) {
                self::trace('EOG bubbled from self::transferCardFromTo');
                throw $e; // Re-throw exception to higher level
            } finally {
                // Determine if the loss of the card from its location of depart breaks a splay. If it's the case, change the splay_direction of the remaining card to unsplay (a notification being sent).
                if ($location_from == 'board' && $splay_direction_from > 0) {
                    $number_of_cards_in_pile = self::getUniqueValueFromDB(
                        self::format("
                        SELECT
                            COUNT(*)
                        FROM
                            card
                        WHERE
                            owner={owner_from} AND
                            location='board' AND
                            color={color}
                    ",
                            array('owner_from' => $owner_from, 'color' => $color)
                        )
                    );

                    if ($number_of_cards_in_pile <= 1) {
                        self::splay($owner_from, $owner_from, $color, 0); // Unsplay
                    }
                }
            }
        }
        return $card;
    }

    /** Splay mechanism **/

    function unsplay($player_id, $target_player_id, $color): bool
    {
        return self::splay($player_id, $target_player_id, $color, Directions::UNSPLAYED, /*force_unsplay=*/ true);
    }

    function splayLeft($player_id, $target_player_id, $color): bool
    {
        return self::splay($player_id, $target_player_id, $color, Directions::LEFT);
    }

    function splayRight($player_id, $target_player_id, $color): bool
    {
        return self::splay($player_id, $target_player_id, $color, Directions::RIGHT);
    }

    function splayUp($player_id, $target_player_id, $color): bool
    {
        return self::splay($player_id, $target_player_id, $color, Directions::UP);
    }

    function splayAslant($player_id, $target_player_id, $color): bool
    {
        return self::splay($player_id, $target_player_id, $color, Directions::ASLANT);
    }

    function splay($player_id, $target_player_id, $color, $splay_direction, $force_unsplay = false): bool
    {

        // Return early if the stack is already splayed in the requested direction.
        if (self::getCurrentSplayDirection($target_player_id, $color) == $splay_direction) {
            return false;
        }

        // Return early if a stack with less than 2 cards is attempting to be splayed.
        if ($splay_direction != Directions::UNSPLAYED && self::countCardsInLocationKeyedByColor($target_player_id, 'board')[$color] <= 1) {
            return false;
        }

        self::DbQuery(
            self::format("
            UPDATE
                card
            SET
                splay_direction = {splay_direction}
            WHERE
                owner = {owner} AND
                location = 'board' AND
                color = {color}
         ",
                array('owner' => $target_player_id, 'color' => $color, 'splay_direction' => $splay_direction)
            )
        );

        self::notifyForSplay($player_id, $target_player_id, $color, $splay_direction, $force_unsplay);

        $end_of_game = false;

        self::removeOldFlagsAndFountains();
        try {
            self::addNewFlagsAndFountains();
        } catch (EndOfGame $e) {
            $end_of_game = true;
        }

        try {
            self::checkForSpecialAchievements();
        } catch (EndOfGame $e) {
            $end_of_game = true;
        }

        if ($end_of_game) {
            self::trace('EOG bubbled from self::splay');
            throw $e; // Re-throw exception to higher level
        }

        // Changing a splay results in a Cities card being drawn (as long as there isn't already one in hand)
        if ($this->innovationGameState->citiesExpansionEnabled() && $splay_direction > 0 && self::countCardsInLocation($player_id, 'hand', CardTypes::CITIES) == 0) {
            self::executeDraw($player_id, self::getAgeToDrawIn($player_id), 'hand', /*bottom_to=*/ false, CardTypes::CITIES);
        }

        self::recordThatChangeOccurred();
        return true;
    }

    function getForecastAndSafeLimit($player_id): int
    {
        $maxSplayDirection = 0;
        foreach (self::getTopCardsOnBoard($player_id) as $card) {
            $maxSplayDirection = max($maxSplayDirection, $card['splay_direction']);
        }
        return 5 - $maxSplayDirection;
    }

    /* Rearrangement mechanism */
    function rearrange($player_id, $color, $permutations)
    {

        $old_board = self::getCardsInLocationKeyedByColor($player_id, 'board');

        foreach ($permutations as $permutation) {
            $data = $permutation;
            $data['player_id'] = $player_id;
            $data['color'] = $color;
            $data['position_plus_delta'] = $data['position'] + $data['delta'];
            self::DbQuery(
                self::format("
                UPDATE
                    card
                SET
                    position = (CASE position
                                WHEN  {position} THEN {position_plus_delta}
                                ELSE {position}
                                END)
                WHERE
                    owner = {player_id} AND
                    location = 'board' AND
                    color = {color} AND
                    position IN ({position}, {position_plus_delta})
            ",
                    $data
                )
            );
        }

        $new_board = self::getCardsInLocationKeyedByColor($player_id, 'board');

        $actual_change = $old_board[$color] != $new_board[$color];

        if ($actual_change) {
            self::updatePlayerRessourceCounts($player_id);
            self::recordThatChangeOccurred();
        }

        return $actual_change;
    }

    /** Selection card management **/
    function markAsSelected($card_id)
    {
        /**
        Mark one card via its id.
        **/
        self::DbQuery(
            self::format("
            UPDATE
                card
            SET
                selected = TRUE
            WHERE
                id = {card_id}
        ",
                array('card_id' => $card_id)
            )
        );
    }

    function unmarkAsSelected($card_id)
    {
        /**
        Mark one card via its id.
        **/
        self::DbQuery(
            self::format("
            UPDATE
                card
            SET
                selected = FALSE
            WHERE
                id = {card_id}
        ",
                array('card_id' => $card_id)
            )
        );
    }

    function countSelectedCards()
    {
        return self::getUniqueValueFromDB("
            SELECT
                COUNT(*)
            FROM
                card
            WHERE
                selected IS TRUE
        ");
    }

    function getSelectedCards()
    {
        return self::getObjectListFromDB("SELECT * FROM card WHERE selected IS TRUE ORDER BY location, position");
    }

    function getVisibleSelectedCards($player_id)
    {
        return self::getObjectListFromDB(
            self::format("
            SELECT
                *
            FROM
                card
            WHERE
                selected IS TRUE AND
                (location = 'board' OR (owner = {player_id} AND location != 'achievements') OR (location = 'achievements' AND age IS NULL))
        ",
                ['player_id' => $player_id]
            )
        );
    }

    function getSelectableRectos($player_id)
    {
        return self::getObjectListFromDB(
            self::format("
            SELECT
                owner, location, age, type, is_relic, position
            FROM
                card
            WHERE
                selected IS TRUE AND
                location != 'board' AND
                age IS NOT NULL AND
                (owner != {player_id} OR location = 'score' OR location = 'forecast' OR location = 'achievements' OR location = 'safe')
        ",
                ['player_id' => $player_id]
            )
        );
    }

    function deselectAllCards()
    {
        /**
        Deselect all cards.
        **/

        self::DBQuery("
            UPDATE
                card
            SET
                selected = FALSE
        ");
    }

    /** Notification system for transfer **/
    function notifyAll($notification_type, $notification_log, $notification_args = [])
    {
        self::notifyAllPlayersBut(array(), $notification_type, $notification_log, $notification_args);
    }

    function notifyAllPlayersBut($player_ids, $notification_type, $notification_log, $notification_args = [])
    {
        /**
        Notify all players except the players in the list $player_ids (or with only one value, one can pass directly the id of the player to exclude).
        The spectators are notified as well.
        **/
        if (!is_array($player_ids)) { // The first argument is a single value
            // Transform to an array with one element
            $player_ids = array($player_ids);
        }

        // Notify players
        foreach (self::getAllPlayerIds() as $player_id) {
            if (in_array($player_id, $player_ids)) {
                continue;
            }
            self::notifyPlayer($player_id, $notification_type, $notification_log, $notification_args);
        }

        // Notify spectator: same message but have to redirect on other handler in JS for spectators to see messages in logs
        self::notifyAllPlayers($notification_type . '_spectator', '', array_merge($notification_args, array('notification_type' => $notification_type, 'log' => $notification_log))); // Players won't suscribe to this: it is filtered by the JS
    }

    function notifyIfLocationLimitShrunkSelection($player_id)
    {
        if ($this->innovationGameState->get('limit_shrunk_selection_size') == 1) {
            $location_to = Locations::decode($this->innovationGameState->get('location_to'));
            if ($location_to == 'safe') {
                self::notifyPlayer($player_id, 'log', clienttranslate('${Your} safe is full so no more cards can be transferred to your safe.'), ['Your' => 'Your']);
                self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name}\'s safe is full so no more cards can be transferred to his safe.'), ['player_name' => self::renderPlayerName($player_id)]);
            } else if ($location_to == 'forecast') {
                self::notifyPlayer($player_id, 'log', clienttranslate('${Your} forecast is full so no more cards can be transferred to your forecast.'), ['Your' => 'Your']);
                self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name}\'s forecast is full so no more cards can be transferred to his forecast.'), ['player_name' => self::renderPlayerName($player_id)]);
            }
            $this->innovationGameState->set('limit_shrunk_selection_size', -1);
        }
    }

    function updateGameSituation($card, $transferInfo)
    {
        self::recordThatChangeOccurred();

        $bulk_transfer = $transferInfo['bulk_transfer'];
        $last_card_of_bulk_transfer = $transferInfo['last_card_of_bulk_transfer'];
        $owner_from = $transferInfo['owner_from'];
        $owner_to = $transferInfo['owner_to'];
        $location_from = $transferInfo['location_from'];
        $location_to = $transferInfo['location_to'];
        $bottom_to = $transferInfo['bottom_to'];

        $score_from_update = $location_from == 'score' || $location_from == 'board';
        $score_to_update = $location_to == 'score' || $location_to == 'board';

        $max_age_on_board_from_update = $location_from == 'board';
        $max_age_on_board_to_update = $location_to == 'board';

        $active_player_id = self::getActivePlayerId();

        $progressInfo = array();
        // Update player progression if applicable
        // TODO(4E): Remove the no_players_involved case. There is always a player which initiates the action.
        $no_players_involved = $owner_from == 0 && $owner_to == 0 && $location_to != 'junk' && $location_from != 'junk';
        $one_player_involved = array_key_exists('using_debug_buttons', $card) // Debug buttons can be used by non-active players
            || $card['age'] === null // Flags, fountains, and special achievements only involve one player
            || ($owner_from == 0 && $owner_to == $active_player_id)
            || ($owner_to == 0 && $owner_from == $active_player_id)
            || ($owner_from == $owner_to && $owner_from == $active_player_id)
            || ($owner_from == 0 && $owner_to == 0);

        if ($no_players_involved) {
            self::notifyWithNoPlayersInvolved($card, $transferInfo, $progressInfo);
        } else if ($one_player_involved) {
            $player_id = $active_player_id;
            if ($owner_to != 0) {
                $player_id = $owner_to;
            } else if ($owner_from != 0) {
                $player_id = $owner_from;
            }
            $transferInfo['player_id'] = $player_id;

            if ($score_from_update) {
                $progressInfo['new_score'] = self::updatePlayerScore($owner_from);
            }
            if ($score_to_update) {
                $progressInfo['new_score'] = self::updatePlayerScore($owner_to);
            }
            if ($max_age_on_board_from_update || $max_age_on_board_to_update) {
                $max_age_on_board = self::getMaxAgeOnBoardTopCards($player_id);
                $progressInfo['new_max_age_on_board'] = $max_age_on_board;
                self::setStat($max_age_on_board, 'max_age_on_board', $player_id);
            }
            if ($location_from == 'board' || $location_to == 'board') {
                $progressInfo['new_ressource_counts'] = self::updatePlayerRessourceCounts($player_id);
            }
            // Update counters for the Monument special achievement
            if ($this->innovationGameState->getEdition() <= 3) {
                if ($location_from == 'board' && $bottom_to) { // That's a tuck
                    self::incrementFlagForMonument($player_id, 'number_of_tucked_cards');
                } else if ($transferInfo['score_keyword']) { // That's a score
                    self::incrementFlagForMonument($player_id, 'number_of_scored_cards');
                }
                $transferInfo['monument_counters'][$player_id] = self::getFlagsForMonument($player_id);
            }
            self::notifyWithOnePlayerInvolved($card, $transferInfo, $progressInfo);
        } else {
            $player_id = $active_player_id;
            if ($owner_from == 0) {
                $opponent_id = $owner_to;
            } else if ($owner_to == 0) {
                $opponent_id = $owner_from;
            } else if ($owner_from == $player_id) {
                $opponent_id = $owner_to;
            } else {
                $opponent_id = $owner_from;
            }
            $transferInfo['player_id'] = $player_id;
            $transferInfo['opponent_id'] = $opponent_id;

            if ($score_from_update) {
                $progressInfo['new_score_from'] = self::updatePlayerScore($owner_from);
            }
            if ($score_to_update) {
                $progressInfo['new_score_to'] = self::updatePlayerScore($owner_to);
            }

            if ($location_from == 'board') {
                $progressInfo['new_ressource_counts_from'] = self::updatePlayerRessourceCounts($owner_from);
            }
            if ($location_to == 'board') {
                $progressInfo['new_ressource_counts_to'] = self::updatePlayerRessourceCounts($owner_to);
            }
            if ($max_age_on_board_from_update) {
                $max_age_on_board_from = self::getMaxAgeOnBoardTopCards($owner_from);
                $progressInfo['new_max_age_on_board_from'] = $max_age_on_board_from;
                self::setStat($max_age_on_board_from, 'max_age_on_board', $owner_from);
            }
            if ($max_age_on_board_to_update) {
                $max_age_on_board_to = self::getMaxAgeOnBoardTopCards($owner_to);
                $progressInfo['new_max_age_on_board_to'] = $max_age_on_board_to;
                self::setStat($max_age_on_board_to, 'max_age_on_board', $owner_to);
            }
            self::notifyWithTwoPlayersInvolved($card, $transferInfo, $progressInfo);
        }

        $end_of_game = false;

        // A player is losing an achievement
        if ($owner_from != 0 && $location_from == 'achievements') {
            // // The number of achievements is the BGA score (not to be confused with the definition of score in an Innovation game)
            self::decrementBGAScore($owner_from);
        }

        // A player is gaining an achievement
        if ($owner_to != 0 && $location_to == 'achievements') {
            try {
                // The number of achievements is the BGA score (not to be confused with the definition of score in an Innovation game)
                self::incrementBGAScore($owner_to, /*is_special_achievement=*/ $card['age'] === null && $card['id'] < 1000); // Fountains and flags are not considered special achievements
            } catch (EndOfGame $e) {
                $end_of_game = true;
            }
        }

        if (!$bulk_transfer || $last_card_of_bulk_transfer) {
            if ($location_from == 'board' || $location_to == 'board') {
                self::removeOldFlagsAndFountains();
                try {
                    self::addNewFlagsAndFountains();
                } catch (EndOfGame $e) {
                    $end_of_game = true;
                }
            }

            try {
                self::checkForSpecialAchievements();
            } catch (EndOfGame $e) {
                $end_of_game = true;
            }
        }

        if ($end_of_game) {
            self::trace('EOG bubbled from self::updateGameSituation');
            throw $e; // Re-throw exception to higher level
        }
    }

    function revealLocation(int $player_id, string $location, bool $forProvingPurposes = false)
    {
        $cards = self::getCardsInLocation($player_id, $location);
        $args = ['i18n' => ['location'], 'location' => Locations::render($location)];
        if (count($cards) == 0) {
            if (!$forProvingPurposes) {
                $this->notifyPlayer(
                    $player_id,
                    'log',
                    clienttranslate('${You} reveal an empty ${location}.'),
                    array_merge($args, ['You' => 'You'])
                );
                $this->notifyAllPlayersBut(
                    $player_id,
                    'log',
                    clienttranslate('${player_name} reveals an empty ${location}.'),
                    array_merge($args, ['player_name' => self::getPlayerNameFromId($player_id)])
                );
            }
            return;
        }
        $args = array_merge($args, ['card_ids' => self::getCardIds($cards), 'card_list' => self::getNotificationArgsForCardList($cards)]);
        $playerArgs = array_merge($args, ['You' => 'You']);
        $otherArgs = array_merge($args, ['player_name' => self::getPlayerNameFromId($player_id)]);
        if ($forProvingPurposes) {
            $this->notifyPlayer($player_id, 'logWithCardTooltips', clienttranslate('${You} reveal your ${location} to prove that no card could be selected: ${card_list}.'), $playerArgs);
            $this->notifyAllPlayersBut($player_id, 'logWithCardTooltips', clienttranslate('${player_name} reveals his ${location} to prove that no card could be selected: ${card_list}.'), $otherArgs);
        } else {
            $this->notifyPlayer($player_id, 'logWithCardTooltips', clienttranslate('${You} reveal your ${location}: ${card_list}.'), $playerArgs);
            $this->notifyAllPlayersBut($player_id, 'logWithCardTooltips', clienttranslate('${player_name} reveals his ${location}: ${card_list}.'), $otherArgs);
        }
    }

    function revealHand($player_id)
    {
        self::revealLocation($player_id, 'hand');
    }

    function revealCardWithoutMoving($player_id, $card, $mentionLocation = true)
    {
        if ($mentionLocation) {
            $args = ['i18n' => ['location'], 'location' => Locations::render($card['location']), 'card_ids' => [$card['id']], 'card_list' => self::getNotificationArgsForCardList([$card])];
            $this->notifyPlayer(
                $player_id,
                'logWithCardTooltips',
                clienttranslate('${You} reveal ${card_list} from your ${location}.'),
                array_merge($args, ['You' => 'You'])
            );
            $this->notifyAllPlayersBut(
                $player_id,
                'logWithCardTooltips',
                clienttranslate('${player_name} reveals ${card_list} from his ${location}.'),
                array_merge($args, ['player_name' => self::getPlayerNameFromId($player_id)])
            );
        } else {
            $args = ['card_ids' => [$card['id']], 'card_list' => self::getNotificationArgsForCardList([$card])];
            $this->notifyPlayer(
                $player_id,
                'logWithCardTooltips',
                clienttranslate('${You} reveal ${card_list}.'),
                array_merge($args, ['You' => 'You'])
            );
            $this->notifyAllPlayersBut(
                $player_id,
                'logWithCardTooltips',
                clienttranslate('${player_name} reveals ${card_list}.'),
                array_merge($args, ['player_name' => self::getPlayerNameFromId($player_id)])
            );
        }
    }

    function getNotificationArgsForCardList($cards)
    {
        $args = array();
        $args['i18n'] = array();
        $log = "";
        for ($i = 0; $i < count($cards); $i++) {
            $card = $cards[$i];
            if ($i > 0) {
                $log = $log . ', ';
            }
            if ($card['age'] != null) {
                $log = $log . "<span class='square N age age_" . $card['age'] . " type_" . $card['type'] . "'>" . $card['age'] . "</span> ";
            }
            $log = $log . '<span id=\'' . uniqid() . '\'class=\'card_name card_id_' . $card['id'] . '\'>${name_' . $i . '}</span>';
            $args['name_' . $i] = self::getCardName($card['id']);
            $args['i18n'][] = 'name_' . $i;
        }
        return ['log' => $log, 'args' => $args];
    }

    function getDelimiterMeanings($text, $card_id = null)
    {
        $delimiters = array();

        // Delimiters for age icon
        if (strpos($text, '{<}') > -1) {
            if ($card_id == null) {
                $delimiters['<'] = "<span class='square N age'>";
            } else {
                $delimiters['<'] = "<span class='square N age type_" . self::getCardInfo($card_id)['type'] . "'>";
            }
            $delimiters['>'] = "</span>";
        }

        // Delimiters for card name
        if (strpos($text, '{<<}') > -1) {
            // Without an ID it's not possible to add a BGA tooltip to it.
            $delimiters['<<'] = "<span id='" . uniqid() . "'class='card_name card_id_" . $card_id . "'>";
            $delimiters['>>'] = "</span>";
        }

        // Delimiters for achievement name
        if (strpos($text, '{<<<}') > -1) {
            $delimiters['<<<'] = "<span class='card_name'>";
            $delimiters['>>>'] = "</span>";
        }

        // Delimiters for ressource icon
        if (strpos($text, '{[}') > -1) {
            $delimiters['['] = "<span class='square N icon_";
            $delimiters[']'] = "'></span>";
        }
        return $delimiters;
    }

    function getCardExecutionCodeWithLetter($card_id, $current_effect_type, $current_effect_number, $step)
    {
        $letters = array(1 => 'A', 2 => 'B', 3 => 'C', 4 => 'D');
        // TODO(LATER): Remove this hack since it's likely just masking another problem.
        if ($step >= 1 && $step <= 4) {
            $letter = $letters[$step];
        } else {
            $letter = '?';
        }
        return self::getCardExecutionBaseCode($card_id, $current_effect_type, $current_effect_number) . $letter . self::getEditionSuffix($card_id);
    }

    function getCardExecutionCode($card_id, $current_effect_type, $current_effect_number)
    {
        return self::getCardExecutionBaseCode($card_id, $current_effect_type, $current_effect_number) . self::getEditionSuffix($card_id);
    }

    function getCardExecutionBaseCode($card_id, $current_effect_type, $current_effect_number)
    {
        $nested_card_state = self::getCurrentNestedCardState();
        $post_execution_indicator = $nested_card_state['post_execution_index'] == 0 ? '' : '+';
        // Echo effects are sometimes executed on cards other than the card being dogma'd
        if ($current_effect_type == 3) {
            $nesting_index = $nested_card_state['nesting_index'];
            $card_id = self::getUniqueValueFromDB(
                self::format(
                    "SELECT card_id FROM echo_execution WHERE nesting_index = {nesting_index} AND execution_index = {effect_number}",
                    array('nesting_index' => $nesting_index, 'effect_number' => $current_effect_number)
                )
            );
            $current_effect_number = 1;
        }
        return $card_id . self::getLetterForEffectType($current_effect_type) . $current_effect_number . $post_execution_indicator;
    }

    function getEditionSuffix($card_id)
    {
        if (array_key_exists('separate_4E_implementation', $this->textual_card_infos[$card_id]) && $this->textual_card_infos[$card_id]['separate_4E_implementation'] == true) {
            if ($this->innovationGameState->getEdition() == 4) {
                return '_4E'; // 4th edition or later
            } else {
                return '_3E'; // 3rd edition or earlier
            }
        }
        return '';
    }

    function getLetterForEffectType($effect_type)
    {
        switch ($effect_type) {
            case self::DEMAND_EFFECT:
                // I demand
                return "D";
            case self::NON_DEMAND_EFFECT:
                // Non-demand
                return "N";
            case self::COMPEL_EFFECT:
                // I compel
                return "C";
            case self::ECHO_EFFECT:
                // Echo
                return "E";
            default:
                // This should not happen
                throw new BgaVisibleSystemException(self::format(self::_("Unhandled case in {function}: '{code}'"), array('function' => "getLetterForEffectType()", 'code' => $effect_type)));
        }
    }

    function notifyWithNoPlayersInvolved($card, $transferInfo, $progressInfo)
    {
        $bulk_transfer = $transferInfo['bulk_transfer'];
        $location_from = $transferInfo['location_from'];
        $location_to = $transferInfo['location_to'];

        // TODO(4E): Revise this.
        switch ($location_from . '->' . $location_to) {
            case 'deck->achievements':
                $message = clienttranslate('The bottom ${<}${age}${>} card is transfered to the available achievements.');
                break;
            case 'achievements->deck':
                $message = clienttranslate('A ${<}${age}${>} achievement card is returned to its deck.');
                break;
            default:
                // This should not happen
                throw new BgaVisibleSystemException(self::format(self::_("Unhandled case in {function}: '{code}'"), array('function' => 'notifyWithNoPlayersInvolved()', 'code' => $location_from . '->' . $location_to)));
        }

        $transferInfo = self::stripTransferInfoForNotification($transferInfo);
        $info = array_merge($transferInfo, $progressInfo);

        $notif_args = array_merge($info, self::getDelimiterMeanings($message, $card['id']));
        $notif_args['age'] = $card['age'];
        $notif_args['type'] = $card['type'];
        $notif_args['is_relic'] = $card['is_relic'];

        self::notifyAllPlayers("transferedCard", $message, $notif_args);
    }

    function notifyWithOnePlayerInvolved($card, $transferInfo, $progressInfo)
    {
        $is_special_achievement = $card['age'] === null;
        $is_museum = 1200 <= $card['id'] && $card['id'] <= 1204;

        $bulk_transfer = $transferInfo['bulk_transfer'];
        $location_from = $transferInfo['location_from'];
        $location_to = $transferInfo['location_to'];
        $owner_from = $transferInfo['owner_from'];
        $owner_to = $transferInfo['owner_to'];
        $bottom_to = $transferInfo['bottom_to'];
        $score_keyword = $transferInfo['score_keyword'];
        $meld_keyword = $transferInfo['meld_keyword'];
        $achieve_keyword = $transferInfo['achieve_keyword'];
        $draw_keyword = $transferInfo['draw_keyword'];
        $safeguard_keyword = $transferInfo['safeguard_keyword'];
        $return_keyword = $transferInfo['return_keyword'];
        $foreshadow_keyword = $transferInfo['foreshadow_keyword'];

        if ($location_from === Locations::MUSEUMS && $location_to === Locations::MUSEUMS) {
            $notif_args = array_merge($transferInfo, $progressInfo, $card);
            self::notifyAllPlayers("transferedCard", "", $notif_args);
            return;
        }

        // Used for the active player
        $visible_for_player = false;
        $action_for_player = clienttranslate('transfer');
        $from_somewhere_for_player = '';
        $to_somewhere_for_player = '';

        // Used for the other players
        $visible_for_others = false;
        $action_for_others = clienttranslate('transfers');
        $from_somewhere_for_others = '';
        $to_somewhere_for_others = '';

        // Update text based on where the card is coming from
        if ($location_from === Locations::DECK && $draw_keyword) {
            $action_for_player = clienttranslate('draw');
            $action_for_others = clienttranslate('draws');
        } else if ($location_from === 'safe') {
            $from_somewhere_for_player = clienttranslate(' from your safe');
            $from_somewhere_for_others = clienttranslate(' from his safe');
        } else if ($location_from === Locations::DISPLAY) {
            $visible_for_player = true;
            $visible_for_others = true;
            $from_somewhere_for_player = clienttranslate(' from your display');
            $from_somewhere_for_others = clienttranslate(' from his display');
            if ($location_to === Locations::HAND || $location_to === Locations::MUSEUMS) {
                $action_for_player = clienttranslate('rotate');
                $action_for_others = clienttranslate('rotates');
            }
        } else if ($location_from === Locations::MUSEUMS) {
            $visible_for_player = true;
            $visible_for_others = true;
            $from_somewhere_for_player = clienttranslate(' from your museum');
            $from_somewhere_for_others = clienttranslate(' from his museum');
        } else if ($location_from === Locations::HAND) {
            $visible_for_player = true;
            $from_somewhere_for_player = clienttranslate(' from your hand');
            $from_somewhere_for_others = clienttranslate(' from his hand');
        } else if ($location_from === Locations::BOARD || $location_from === Locations::PILE) {
            $visible_for_player = true;
            $visible_for_others = true;
            $from_somewhere_for_player = clienttranslate(' from your board');
            $from_somewhere_for_others = clienttranslate(' from his board');
        } else if ($location_from === Locations::FORECAST) {
            $visible_for_player = true;
            $from_somewhere_for_player = clienttranslate(' from your forecast');
            $from_somewhere_for_others = clienttranslate(' from his forecast');
        } else if ($location_from === Locations::SCORE) {
            $visible_for_player = true;
            $from_somewhere_for_player = clienttranslate(' from your score pile');
            $from_somewhere_for_others = clienttranslate(' from his score pile');
        } else if ($location_from === Locations::ACHIEVEMENTS) {
            if ($owner_from == 0) {
                $from_somewhere_for_player = clienttranslate(' from the available achievements');
                $from_somewhere_for_others = clienttranslate(' from the available achievements');
            } else {
                $from_somewhere_for_player = clienttranslate(' from your achievements');
                $from_somewhere_for_others = clienttranslate(' from his achievements');
            }
        } else if ($location_from === Locations::RELICS) {
            $action_for_player = clienttranslate('seize');
            $action_for_others = clienttranslate('seizes');
        } else if ($location_from === Locations::JUNK) {
            $from_somewhere_for_player = clienttranslate(' from the junk');
            $from_somewhere_for_others = clienttranslate(' from the junk');
        } else if ($location_from === Locations::REVEALED) {
            $visible_for_player = true;
            $visible_for_others = true;
        } else if ($location_from === 'flags' || $location_from === 'fountains') {
            $visible_for_player = true;
            $visible_for_others = true;
        }

        // Update text based on where the card is going to
        if ($location_to === Locations::BOARD) {
            $visible_for_player = true;
            $visible_for_others = true;
            if ($meld_keyword) {
                if ($draw_keyword) {
                    $action_for_player = clienttranslate('draw and meld');
                    $action_for_others = clienttranslate('draw and melds');
                } else if ($this->gamestate->state()['name'] == 'promoteCardPlayerTurn') {
                    $action_for_player = clienttranslate('promote');
                    $action_for_others = clienttranslate('promotes');
                } else {
                    $action_for_player = clienttranslate('meld');
                    $action_for_others = clienttranslate('melds');
                }
            } else if ($bottom_to) {
                if ($draw_keyword) {
                    $action_for_player = clienttranslate('draw and tuck');
                    $action_for_others = clienttranslate('draw and tucks');
                } else {
                    $action_for_player = clienttranslate('tuck');
                    $action_for_others = clienttranslate('tucks');
                }
            } else {
                $to_somewhere_for_player = clienttranslate(' to your board');
                $to_somewhere_for_others = clienttranslate(' to his board');
            }
        } else if ($location_to === Locations::DISPLAY) {
            $visible_for_player = true;
            $visible_for_others = true;
            $action_for_player = clienttranslate('dig');
            $to_somewhere_for_player = clienttranslate(' and put it on display');
            $action_for_others = clienttranslate('digs');
            $to_somewhere_for_others = clienttranslate(' and puts it on display');
        } else if ($location_to === Locations::MUSEUMS) {
            $visible_for_player = true;
            $visible_for_others = true;
            $to_somewhere_for_player = clienttranslate(' into a museum');
            $to_somewhere_for_others = clienttranslate(' into a museum');
        } else if ($location_to === Locations::FORECAST) {
            $visible_for_player = true;
            if ($draw_keyword) {
                $action_for_player = clienttranslate('draw and foreshadow');
                $action_for_others = clienttranslate('draws and foreshadows');
            } else {
                $action_for_player = clienttranslate('foreshadow');
                $action_for_others = clienttranslate('foreshadows');
            }
        } else if ($location_to === Locations::REVEALED) {
            $visible_for_player = true;
            $visible_for_others = true;
            if ($draw_keyword) {
                $action_for_player = clienttranslate('draw and reveal');
                $action_for_others = clienttranslate('draws and reveals');
            } else {
                $action_for_player = clienttranslate('reveal');
                $action_for_others = clienttranslate('reveals');
            }
        } else if ($location_to === Locations::ACHIEVEMENTS) {
            if ($owner_to == 0) {
                $to_somewhere_for_player = clienttranslate(' to the available achievements');
                $to_somewhere_for_others = clienttranslate(' to the available achievements');
            } else if ($draw_keyword) {
                $visible_for_player = true;
                $action_for_player = clienttranslate('draw and achieve');
                $action_for_others = clienttranslate('draws and achieves');
            } else if ($achieve_keyword) {
                $action_for_player = clienttranslate('achieve');
                $action_for_others = clienttranslate('achieves');
            } else {
                $to_somewhere_for_player = clienttranslate(' to your achievements');
                $to_somewhere_for_others = clienttranslate(' to his achievements');
            }
        } else if ($location_to === Locations::SCORE) {
            $visible_for_player = true;
            if ($draw_keyword) {
                $action_for_player = clienttranslate('draw and score');
                $action_for_others = clienttranslate('draws and scores');
            } else if ($score_keyword) {
                $action_for_player = clienttranslate('score');
                $action_for_others = clienttranslate('scores');
            } else {
                $to_somewhere_for_player = clienttranslate(' to your score pile');
                $to_somewhere_for_others = clienttranslate(' to his score pile');
            }
        } else if ($location_to === Locations::HAND) {
            $visible_for_player = true;
            $to_somewhere_for_player = clienttranslate(' to your hand');
            $to_somewhere_for_others = clienttranslate(' to his hand');
        } else if ($location_to === Locations::SAFE) {
            if ($draw_keyword) {
                $visible_for_player = true;
                $action_for_player = clienttranslate('draw and safeguard');
                $action_for_others = clienttranslate('draws and safeguards');
            } else if ($safeguard_keyword) {
                $action_for_player = clienttranslate('safeguard');
                $action_for_others = clienttranslate('safeguards');
            } else {
                $to_somewhere_for_player = clienttranslate(' to your safe');
                $to_somewhere_for_others = clienttranslate(' to his safe');
            }
        } else if ($location_to === Locations::DECK) {
            if ($bottom_to) {
                $action_for_player = clienttranslate('return');
                $action_for_others = clienttranslate('returns');
            } else {
                $action_for_player = clienttranslate('place');
                $to_somewhere_for_player = clienttranslate(' on top of its deck');
                $action_for_others = clienttranslate('places');
                $to_somewhere_for_others = clienttranslate(' on top of its deck');
            }
        } else if ($location_to === Locations::RELICS) {
            $action_for_player = clienttranslate('return');
            $action_for_others = clienttranslate('returns');
        } else if ($location_to === Locations::JUNK) {
            $action_for_player = clienttranslate('junk');
            $action_for_others = clienttranslate('junks');
        } else if ($location_to === 'removed') {
            $action_for_player = clienttranslate('remove');
            $action_for_others = clienttranslate('removes');
        } else if ($location_to === 'flags' || $location_to === 'fountains') {
            $visible_for_player = true;
            $visible_for_others = true;
        }

        // Choose a pattern for the messages, depending on the context of the card transfer
        $notif_args_for_player = [];
        $notif_args_for_player['You'] = 'You';
        $notif_args_for_player['your'] = 'your';
        if ($visible_for_player || $is_special_achievement) {
            $notif_args_for_player['i18n'] = ['name'];
            $notif_args_for_player['name'] = self::getCardName($card['id']);
            // TODO(LATER): We should stop sending the properties of the card which aren't actually used.
            $notif_args_for_player = array_merge($notif_args_for_player, $card);
        } else {
            $notif_args_for_player['age'] = $card['age'];
            $notif_args_for_player['type'] = $card['type'];
            $notif_args_for_player['is_relic'] = $card['is_relic'];
        }
        $notif_args_for_others = [];
        $notif_args_for_others['player_name'] = self::getPlayerNameFromId($transferInfo['player_id']);
        if ($visible_for_others || $is_special_achievement) {
            $notif_args_for_others['i18n'] = ['name'];
            $notif_args_for_others['name'] = self::getCardName($card['id']);
            // TODO(LATER): We should stop sending the properties of the card which aren't actually used.
            $notif_args_for_others = array_merge($notif_args_for_others, $card);
        } else {
            $notif_args_for_others['age'] = $card['age'];
            $notif_args_for_others['type'] = $card['type'];
            $notif_args_for_others['is_relic'] = $card['is_relic'];
        }


        if ($location_from === 'fountains') {
            $message_for_player = clienttranslate('A fountain became visible on ${your} board so it now counts as an achievement.');
            $message_for_others = clienttranslate('A fountain became visible on ${player_name}\'s board so it now counts as an achievement.');
        } else if ($location_to === 'fountains') {
            $message_for_player = clienttranslate('A fountain which was visible on ${your} board no longer counts as an achievement.');
            $message_for_others = clienttranslate('A fountain which was visible on ${player_name}\'s board no longer counts as an achievement.');
        } else if ($location_from === 'flags') {
            $message_for_player = clienttranslate('A flag on ${your} board now counts as an achievement since no opponent has more cards of that color visible on their board.');
            $message_for_others = clienttranslate('A flag on ${player_name}\'s board now counts as an achievement since none of their opponents has more cards of that color visible on their board.');
        } else if ($location_to === 'flags') {
            $message_for_player = clienttranslate('A flag which was visible on ${your} board no longer counts as an achievement.');
            $message_for_others = clienttranslate('A flag which was visible on ${player_name}\'s board no longer counts as an achievement.');
        } else {
            $notif_args_for_player['action'] = $action_for_player;
            $notif_args_for_player['from_somewhere'] = $from_somewhere_for_player;
            $notif_args_for_player['to_somewhere'] = $to_somewhere_for_player;
            $notif_args_for_others['action'] = $action_for_others;
            $notif_args_for_others['from_somewhere'] = $from_somewhere_for_others;
            $notif_args_for_others['to_somewhere'] = $to_somewhere_for_others;
            if ($is_museum) {
                $message_for_player = clienttranslate('${You} ${action} a museum.');
                $message_for_others = clienttranslate('${player_name} ${action} a museum.');
            } else if ($is_special_achievement) {
                $message_for_player = clienttranslate('${You} ${action} ${<<<}${name}${>>>}${from_somewhere}${to_somewhere}.');
                $message_for_others = clienttranslate('${player_name} ${action} ${<<<}${name}${>>>}${from_somewhere}${to_somewhere}.');
            } else {
                if ($visible_for_player) {
                    $message_for_player = clienttranslate('${You} ${action} ${<}${age}${>} ${<<}${name}${>>}${from_somewhere}${to_somewhere}.');
                } else {
                    $message_for_player = clienttranslate('${You} ${action} a ${<}${age}${>}${from_somewhere}${to_somewhere}.');
                }
                if ($visible_for_others) {
                    $message_for_others = clienttranslate('${player_name} ${action} ${<}${age}${>} ${<<}${name}${>>}${from_somewhere}${to_somewhere}.');
                } else {
                    $message_for_others = clienttranslate('${player_name} ${action} a ${<}${age}${>}${from_somewhere}${to_somewhere}.');
                }
            }
        }

        $player_id = $transferInfo['player_id'];
        $transferInfo = self::stripTransferInfoForNotification($transferInfo);
        $info = array_merge($transferInfo, $progressInfo);
        $delimiters_for_player = self::getDelimiterMeanings($message_for_player, $card['id']);
        $notif_args_for_player = array_merge($notif_args_for_player, $info, $delimiters_for_player);
        $delimiters_for_others = self::getDelimiterMeanings($message_for_others, $card['id']);
        $notif_args_for_others = array_merge($notif_args_for_others, $info, $delimiters_for_others);
        self::notifyPlayer($player_id, "transferedCard", $message_for_player, $notif_args_for_player);
        self::notifyAllPlayersBut($player_id, "transferedCard", $message_for_others, $notif_args_for_others);
    }

    function getTransferInfoWithOnePlayerInvolved($owner_from, $location_from, $location_to, $player_id_is_owner_from, $player_id_is_owner_to, $bottom_from, $bottom_to, $score_keyword, $meld_keyword, $achieve_keyword, $you_must, $player_must, $player_name, $number, $cards, $targetable_players, $code)
    {

        if ($location_from === Locations::MUSEUMS) {
            return [
                'message_for_player' => [
                    'i18n' => ['log'],
                    'log'  => clienttranslate('${You} must return all artifacts from all museums'),
                    'args' => ['You' => 'You'],
                ],
                'message_for_others' => [
                    'i18n' => ['log'],
                    'log'  => clienttranslate('${player_name} must return all artifacts from all museums'),
                    'args' => ['player_name' => $player_name],
                ],
            ];
        }

        // TODO(4E): Pass this keyword in.
        $safeguard_keyword = false;

        // Text used for the active player
        $message_for_player = clienttranslate('${You_must} ${action} ${number} ${card_qualifier}${card}${from_somewhere}${to_somewhere}');
        $from_somewhere_for_player = '';
        $to_somewhere_for_player = '';

        // Text used for the other players
        $message_for_others = clienttranslate('${player_must} ${action} ${number} ${card_qualifier}${card}${from_somewhere}${to_somewhere}');
        $from_somewhere_for_others = '';
        $to_somewhere_for_others = '';

        // Text used for all players
        $action = clienttranslate('transfer');
        $card_qualifier = '';

        // Update text based on where the card is coming from
        if ($location_from === 'hand') {
            if ($targetable_players === null) {
                $from_somewhere_for_player = clienttranslate(' from your hand');
                $from_somewhere_for_others = clienttranslate(' from his hand');
            } else {
                $from_somewhere_for_player = clienttranslate(' from the hand of ${targetable_players}');
                $from_somewhere_for_others = clienttranslate(' from the hand of ${targetable_players}');
            }
        } else if ($location_from === 'score') {
            if ($targetable_players === null) {
                $from_somewhere_for_player = clienttranslate(' from your score pile');
                $from_somewhere_for_others = clienttranslate(' from his score pile');
            } else {
                $from_somewhere_for_player = clienttranslate(' from the score pile of ${targetable_players}');
                $from_somewhere_for_others = clienttranslate(' from the score pile of ${targetable_players}');
            }
        } else if ($location_from === 'board') {
            if ($targetable_players === null) {
                $from_somewhere_for_player = clienttranslate(' from your board');
                $from_somewhere_for_others = clienttranslate(' from his board');
                if ($bottom_from) {
                    $card_qualifier = clienttranslate('bottom ');
                } else {
                    $card_qualifier = clienttranslate('top ');
                }
            } else {
                $from_somewhere_for_player = clienttranslate(' from the board of ${targetable_players}');
                $from_somewhere_for_others = clienttranslate(' from the board of ${targetable_players}');
            }
        } else if ($location_from === 'pile') {
            if ($targetable_players === null) {
                $from_somewhere_for_player = clienttranslate(' from your board');
                $from_somewhere_for_others = clienttranslate(' from his board');
            } else {
                $from_somewhere_for_player = clienttranslate(' from the board of ${targetable_players}');
                $from_somewhere_for_others = clienttranslate(' from the board of ${targetable_players}');
            }
        } else if ($location_from === 'safe') {
            $from_somewhere_for_player = clienttranslate(' from your safe');
            $from_somewhere_for_others = clienttranslate(' from his safe');
        } else if ($location_from === 'forecast') {
            $from_somewhere_for_player = clienttranslate(' from your forecast');
            $from_somewhere_for_others = clienttranslate(' from his forecast');
        } else if ($location_from === 'revealed') {
            $card_qualifier = clienttranslate('revealed ');
        } else if ($location_from === 'achievements') {
            if ($player_id_is_owner_from) {
                $from_somewhere_for_player = clienttranslate(' from your achievements');
                $from_somewhere_for_others = clienttranslate(' from his achievements');
            } else {
                $from_somewhere_for_player = clienttranslate(' from the available achievements');
                $from_somewhere_for_others = clienttranslate(' from the available achievements');
            }
        } else if ($location_from === Locations::MUSEUMS) {
            $from_somewhere_for_player = clienttranslate(' from all museums');
            $from_somewhere_for_others = clienttranslate(' from all museums');
        } else if ($location_from === Locations::HAND_OR_SCORE) {
            $from_somewhere_for_player = clienttranslate(' from your hand and score pile');
            $from_somewhere_for_others = clienttranslate(' from his hand and score pile');
        } else if ($location_from === Locations::REVEALED_THEN_HAND) {
            $from_somewhere_for_player = clienttranslate(' that you revealed and from your hand');
            $from_somewhere_for_others = clienttranslate(' that he revealed and from his hand');
        } else if ($location_from === Locations::REVEALED_THEN_SCORE) {
            $from_somewhere_for_player = clienttranslate(' that you revealed and from your score pile');
            $from_somewhere_for_others = clienttranslate(' that he revealed and from his score pile');
        } else if ($location_from === Locations::PILE_OR_SCORE) {
            $from_somewhere_for_player = clienttranslate(' from your board and score pile');
            $from_somewhere_for_others = clienttranslate(' from his board and score pile');
        }

        // Update text based on where the card is going to
        if ($location_to === 'hand') {
            $to_somewhere_for_player = clienttranslate(' to your hand');
            $to_somewhere_for_others = clienttranslate(' to his hand');
        } else if ($location_to === 'deck') {
            if ($bottom_to) {
                $action = clienttranslate('return');
            } else {
                $action = clienttranslate('place');
                $to_somewhere_for_player = clienttranslate(' on top of its deck');
                $to_somewhere_for_others = clienttranslate(' on top of its deck');
            }
        } else if ($location_to === 'board') {
            if ($bottom_to) {
                $action = clienttranslate('tuck');
            } else if ($meld_keyword) {
                $action = clienttranslate('meld');
            } else {
                $to_somewhere_for_player = clienttranslate(' to your board');
                $to_somewhere_for_others = clienttranslate(' to his board');
            }
        } else if ($location_to === 'score') {
            if ($score_keyword) {
                $action = clienttranslate('score');
            } else {
                $to_somewhere_for_player = clienttranslate(' to your score pile');
                $to_somewhere_for_others = clienttranslate(' to his score pile');
            }
        } else if ($location_to === 'achievements') {
            if ($player_id_is_owner_to) {
                if ($achieve_keyword) {
                    $action = clienttranslate('achieve');
                } else {
                    $to_somewhere_for_player = clienttranslate(' to your achievements');
                    $to_somewhere_for_others = clienttranslate(' to his achievements');
                }
            } else {
                $to_somewhere_for_player = clienttranslate(' to the available achievements');
                $to_somewhere_for_others = clienttranslate(' to the available achievements');
            }
        } else if ($location_to === 'safe') {
            if ($safeguard_keyword) {
                $action = clienttranslate('safeguard');
            } else {
                $to_somewhere_for_player = clienttranslate(' to your safe');
                $to_somewhere_for_others = clienttranslate(' to his safe');
            }
        } else if ($location_to === 'forecast') {
            $action = clienttranslate('foreshadow');
        } else if ($location_to === 'junk') {
            $action = clienttranslate('junk');
        } else if ($location_to === Locations::JUNK_THEN_SAFEGUARD) {
            $action = clienttranslate('junk then safeguard');
        } else if ($location_to === 'revealed' || ($location_from === Locations::HAND && $location_to === Locations::REVEALED_THEN_HAND)) {
            $action = clienttranslate('reveal');
        } else if ($location_to === Locations::REVEALED_THEN_SCORE) {
            $action = clienttranslate('reveal and score');
        } else if ($location_to === 'revealed,deck') {
            $action = clienttranslate('reveal and return');
        } else if ($location_to === 'none') {
            $action = clienttranslate('choose');
        }

        // Override the text for some specific cases
        // TODO(4E): Fix the following cases: Cyrus Cylinder (134N1+A, 134N1B), Kobukson (367E1A), Dark Web (588N1A), Tuning Fork
        if ($code === '100N1A' || $code === '100N2A' || $code === '134N1A') { // Self Service (either edition) or Cyrus Cylinder
            $card_qualifier = clienttranslate('other top ');
        } else if ($code === '417N1A') { // Helicopter
            $to_somewhere_for_player = clienttranslate('to his score pile');
            $to_somewhere_for_others = clienttranslate('to his score pile');
        } else if (self::getPlayerTableColumn($owner_from, 'distance_rule_share_state') == 1) {
            $to_somewhere_for_player = clienttranslate(' to share in the dogma effect');
            $to_somewhere_for_others = clienttranslate(' to share in the dogma effect');
        } else if (self::getPlayerTableColumn($owner_from, 'distance_rule_demand_state') == 1) {
            $to_somewhere_for_player = clienttranslate(' to avoid executing the demand effect');
            $to_somewhere_for_others = clienttranslate(' to avoid executing the demand effect');
        }

        // TODO(4E): Make sure this translates correctly.
        return [
            'message_for_player' => [
                'i18n' => ['log'],
                'log'  => $message_for_player,
                'args' => [
                    'You_must'           => [
                        'i18n' => ['log'],
                        'log'  => $you_must,
                        'args' => [
                            'You' => 'You',
                        ],
                    ],
                    'action'             => $action,
                    'number'             => $number,
                    'card_qualifier'     => $card_qualifier,
                    'card'               => $cards,
                    'from_somewhere'     => [
                        'i18n' => ['targetable_players'],
                        'log'  => $from_somewhere_for_player,
                        'args' => ['targetable_players' => $targetable_players],
                    ],
                    'to_somewhere'       => $to_somewhere_for_player,
                    'targetable_players' => $targetable_players,
                ],
            ],
            'message_for_others' => [
                'i18n' => ['log'],
                'log'  => $message_for_others,
                'args' => [
                    'i18n'               => ['targetable_players'],
                    'player_must'        => [
                        'i18n' => ['log'],
                        'log'  => $player_must,
                        'args' => [
                            'player_name' => $player_name,
                        ],
                    ],
                    'action'             => $action,
                    'number'             => $number,
                    'card_qualifier'     => $card_qualifier,
                    'card'               => $cards,
                    'from_somewhere'     => [
                        'i18n' => ['targetable_players'],
                        'log'  => $from_somewhere_for_others,
                        'args' => ['targetable_players' => $targetable_players],
                    ],
                    'to_somewhere'       => $to_somewhere_for_others,
                    'targetable_players' => $targetable_players,
                ],
            ],
        ];
    }

    function notifyWithTwoPlayersInvolved($card, $transferInfo, $progressInfo)
    {
        $bulk_transfer = $transferInfo['bulk_transfer'];
        $owner_from = $transferInfo['owner_from'];
        $owner_to = $transferInfo['owner_to'];
        $location_from = $transferInfo['location_from'];
        $location_to = $transferInfo['location_to'];
        $meld_keyword = $transferInfo['meld_keyword'];
        $player_id = $transferInfo['player_id'];

        if ($location_from === Locations::MUSEUMS && $location_to === Locations::MUSEUMS) {
            $notif_args = array_merge($transferInfo, $progressInfo, $card);
            self::notifyAllPlayers("transferedCard", "", $notif_args);
            return;
        }

        // TODO(LATER): Add special cases for seizing relics.

        // Used for the active player
        $visible_for_player = false;
        $action_for_player = clienttranslate('transfer');
        $from_somewhere_for_player = '';
        $to_somewhere_for_player = '';

        // Used for the opponent
        $visible_for_opponent = false;
        $action_for_opponent = clienttranslate('transfers');
        $from_somewhere_for_opponent = '';
        $to_somewhere_for_opponent = '';

        // Used for the other players
        $visible_for_others = false;
        $action_for_others = clienttranslate('transfers');
        $from_somewhere_for_others = '';
        $to_somewhere_for_others = '';

        // Update text based on where the card is coming from
        if ($location_from === Locations::HAND) {
            if ($player_id == $owner_from) {
                $visible_for_player = true;
                $from_somewhere_for_player = clienttranslate(' from your hand');
                $from_somewhere_for_opponent = clienttranslate(' from his hand');
                $from_somewhere_for_others = clienttranslate(' from his hand');
            } else {
                $visible_for_opponent = true;
                $to_somewhere_for_player = clienttranslate(' from ${opponent_name}\'s hand');
                $to_somewhere_for_opponent = clienttranslate(' from ${your} hand');
                $to_somewhere_for_others = clienttranslate(' from ${opponent_name}\'s hand');
            }
        } else if ($location_from === Locations::SCORE) {
            if ($player_id == $owner_from) {
                $visible_for_player = true;
                $from_somewhere_for_player = clienttranslate(' from your score pile');
                $from_somewhere_for_opponent = clienttranslate(' from his score pile');
                $from_somewhere_for_others = clienttranslate(' from his score pile');
            } else {
                $visible_for_opponent = true;
                $from_somewhere_for_player = clienttranslate(' from ${opponent_name}\'s score pile');
                $from_somewhere_for_opponent = clienttranslate(' from ${your} score pile');
                $from_somewhere_for_others = clienttranslate(' from ${opponent_name}\'s score pile');
            }
        } else if ($location_from === Locations::BOARD) {
            $visible_for_player = true;
            $visible_for_opponent = true;
            $visible_for_others = true;
            if ($player_id == $owner_from) {
                $from_somewhere_for_player = clienttranslate(' from your board');
                $from_somewhere_for_opponent = clienttranslate(' from his board');
                $from_somewhere_for_others = clienttranslate(' from his board');
            } else {
                $from_somewhere_for_player = clienttranslate(' from ${opponent_name}\'s board');
                $from_somewhere_for_opponent = clienttranslate(' from ${your} board');
                $from_somewhere_for_others = clienttranslate(' from ${opponent_name}\'s board');
            }
        } else if ($location_from === Locations::SAFE) {
            if ($player_id == $owner_from) {
                $from_somewhere_for_player = clienttranslate(' from your safe');
                $from_somewhere_for_opponent = clienttranslate(' from his safe');
                $from_somewhere_for_others = clienttranslate(' from his safe');
            } else {
                $from_somewhere_for_player = clienttranslate(' from ${opponent_name}\'s safe');
                $from_somewhere_for_opponent = clienttranslate(' from ${your} safe');
                $from_somewhere_for_others = clienttranslate(' from ${opponent_name}\'s safe');
            }
        } else if ($location_from === Locations::ACHIEVEMENTS) {
            if ($player_id == $owner_from) {
                $from_somewhere_for_player = clienttranslate(' from your achievements');
                $from_somewhere_for_opponent = clienttranslate(' from his achievements');
                $from_somewhere_for_others = clienttranslate(' from his achievements');
            } else {
                $from_somewhere_for_player = clienttranslate(' from ${opponent_name}\'s achievements');
                $from_somewhere_for_opponent = clienttranslate(' from ${your} achievements');
                $from_somewhere_for_others = clienttranslate(' from ${opponent_name}\'s achievements');
            }
        } else if ($location_from === Locations::DISPLAY) {
            $visible_for_player = true;
            $visible_for_opponent = true;
            $visible_for_others = true;
            if ($player_id == $owner_from) {
                $from_somewhere_for_player = clienttranslate(' from your display');
                $from_somewhere_for_opponent = clienttranslate(' from his display');
                $from_somewhere_for_others = clienttranslate(' from his display');
            } else {
                $from_somewhere_for_player = clienttranslate(' from ${opponent_name}\'s display');
                $from_somewhere_for_opponent = clienttranslate(' from ${your} display');
                $from_somewhere_for_others = clienttranslate(' from ${opponent_name}\'s display');
            }
        } else if ($location_from === Locations::MUSEUMS) {
            $visible_for_player = true;
            $visible_for_opponent = true;
            $visible_for_others = true;
            $from_somewhere_for_player = clienttranslate(' from ${opponent_name}\'s museum');
            $from_somewhere_for_opponent = clienttranslate(' from ${your} museum');
            $from_somewhere_for_others = clienttranslate(' from ${opponent_name}\'s museum');
        } else if ($location_from === Locations::REVEALED) {
            $visible_for_player = true;
            $visible_for_opponent = true;
            $visible_for_others = true;
        }

        // Update text based on where the card is going to
        if ($location_to === 'hand') {
            if ($player_id == $owner_to) {
                $visible_for_player = true;
                $to_somewhere_for_player = clienttranslate(' to your hand');
                $to_somewhere_for_opponent = clienttranslate(' to his hand');
                $to_somewhere_for_others = clienttranslate(' to his hand');
            } else {
                $visible_for_opponent = true;
                $to_somewhere_for_player = clienttranslate(' to ${opponent_name}\'s hand');
                $to_somewhere_for_opponent = clienttranslate(' to ${your} hand');
                $to_somewhere_for_others = clienttranslate(' to ${opponent_name}\'s hand');
            }
        } else if ($location_to === 'score') {
            if ($player_id == $owner_to) {
                $visible_for_player = true;
                $to_somewhere_for_player = clienttranslate(' to your score pile');
                $to_somewhere_for_opponent = clienttranslate(' to his score pile');
                $to_somewhere_for_others = clienttranslate(' to his score pile');
            } else {
                $visible_for_opponent = true;
                $to_somewhere_for_player = clienttranslate(' to ${opponent_name}\'s score pile');
                $to_somewhere_for_opponent = clienttranslate(' to ${your} score pile');
                $to_somewhere_for_others = clienttranslate(' to ${opponent_name}\'s score pile');
            }
        } else if ($location_to === 'board') {
            $visible_for_player = true;
            $visible_for_opponent = true;
            $visible_for_others = true;
            if ($player_id == $owner_to) {
                if ($meld_keyword) {
                    $action_for_player = clienttranslate('meld');
                    $action_for_opponent = clienttranslate('melds');
                    $action_for_others = clienttranslate('melds');
                } else {
                    $to_somewhere_for_player = clienttranslate(' to your board');
                    $to_somewhere_for_opponent = clienttranslate(' to his board');
                    $to_somewhere_for_others = clienttranslate(' to his board');
                }
            } else {
                $to_somewhere_for_player = clienttranslate(' to ${opponent_name}\'s board');
                $to_somewhere_for_opponent = clienttranslate(' to ${your} board');
                $to_somewhere_for_others = clienttranslate(' to ${opponent_name}\'s board');
            }
        } else if ($location_to === 'forecast') {
            if ($player_id == $owner_to) {
                $visible_for_player = true;
                $to_somewhere_for_player = clienttranslate(' to your forecast');
                $to_somewhere_for_opponent = clienttranslate(' to his forecast');
                $to_somewhere_for_others = clienttranslate(' to his forecast');
            } else {
                $visible_for_opponent = true;
                $to_somewhere_for_player = clienttranslate(' to ${opponent_name}\'s forecast');
                $to_somewhere_for_opponent = clienttranslate(' to ${your} forecast');
                $to_somewhere_for_others = clienttranslate(' to ${opponent_name}\'s forecast');
            }
        } else if ($location_to === 'safe') {
            if ($player_id == $owner_to) {
                $to_somewhere_for_player = clienttranslate(' to your safe');
                $to_somewhere_for_opponent = clienttranslate(' to his safe');
                $to_somewhere_for_others = clienttranslate(' to his safe');
            } else {
                $to_somewhere_for_player = clienttranslate(' to ${opponent_name}\'s safe');
                $to_somewhere_for_opponent = clienttranslate(' to ${your} safe');
                $to_somewhere_for_others = clienttranslate(' to ${opponent_name}\'s safe');
            }
        } else if ($location_to === 'achievements') {
            if ($owner_to == 0) {
                $to_somewhere_for_player = clienttranslate(' to the available achievements');
                $to_somewhere_for_opponent = clienttranslate(' to the available achievements');
                $to_somewhere_for_others = clienttranslate(' to the available achievements');
            } else if ($player_id == $owner_to) {
                $to_somewhere_for_player = clienttranslate(' to your achievements');
                $to_somewhere_for_opponent = clienttranslate(' to his achievements');
                $to_somewhere_for_others = clienttranslate(' to his achievements');
            } else {
                $to_somewhere_for_player = clienttranslate(' to ${opponent_name}\'s achievements');
                $to_somewhere_for_opponent = clienttranslate(' to ${your} achievements');
                $to_somewhere_for_others = clienttranslate(' to ${opponent_name}\'s achievements');
            }
        } else if ($location_to === Locations::DISPLAY) {
            $visible_for_player = true;
            $visible_for_opponent = true;
            $visible_for_others = true;
            if ($player_id == $owner_to) {
                $to_somewhere_for_player = clienttranslate(' to your display');
                $to_somewhere_for_opponent = clienttranslate(' to his display');
                $to_somewhere_for_others = clienttranslate(' to his display');
            }
        } else if ($location_to === 'deck' || $location_to === 'relics') {
            $action_for_player = clienttranslate('return');
            $action_for_opponent = clienttranslate('returns');
            $action_for_others = clienttranslate('returns');
        } else if ($location_to === 'junk') {
            $action_for_player = clienttranslate('junk');
            $action_for_opponent = clienttranslate('junks');
            $action_for_others = clienttranslate('junks');
        } else if ($location_to === 'revealed') {
            $visible_for_player = true;
            $visible_for_opponent = true;
            $visible_for_others = true;
        }

        // Choose a pattern for the messages, depending on the context of the card transfer
        $player_name = self::getPlayerNameFromId($transferInfo['player_id']);
        $opponent_name = self::getPlayerNameFromId($transferInfo['opponent_id']);
        $notif_args_for_player = [
            'i18n'           => ['name'],
            'You'            => 'You',
            'action'         => $action_for_player,
            'from_somewhere' => ['log' => $from_somewhere_for_player, 'args' => ['opponent_name' => $opponent_name]],
            'to_somewhere'   => ['log' => $to_somewhere_for_player, 'args' => ['opponent_name' => $opponent_name]],
        ];
        $notif_args_for_opponent = [
            'your'           => 'your',
            'player_name'    => $player_name,
            'action'         => $action_for_opponent,
            'from_somewhere' => ['log' => $from_somewhere_for_opponent, 'args' => ['opponent_name' => $opponent_name, 'your' => 'your']],
            'to_somewhere'   => ['log' => $to_somewhere_for_opponent, 'args' => ['opponent_name' => $opponent_name, 'your' => 'your']],
        ];
        $notif_args_for_others = [
            'player_name'    => $player_name,
            'opponent_name'  => $opponent_name,
            'action'         => $action_for_others,
            'from_somewhere' => ['log' => $from_somewhere_for_others, 'args' => ['opponent_name' => $opponent_name]],
            'to_somewhere'   => ['log' => $to_somewhere_for_others, 'args' => ['opponent_name' => $opponent_name]],
        ];
        if ($visible_for_player) {
            $message_for_player = clienttranslate('${You} ${action} ${<}${age}${>} ${<<}${name}${>>}${from_somewhere}${to_somewhere}.');
            $notif_args_for_player['i18n'] = array('name');
            $notif_args_for_player['name'] = self::getCardName($card['id']);
            // TODO(LATER): We should stop sending the card's properties which aren't actually used.
            $notif_args_for_player = array_merge($notif_args_for_player, $card);
        } else {
            $message_for_player = clienttranslate('${You} ${action} a ${<}${age}${>}${from_somewhere}${to_somewhere}.');
            $notif_args_for_player['age'] = $card['age'];
            $notif_args_for_player['type'] = $card['type'];
            $notif_args_for_player['is_relic'] = $card['is_relic'];
        }
        if ($visible_for_opponent) {
            $message_for_opponent = clienttranslate('${player_name} ${action} ${<}${age}${>} ${<<}${name}${>>}${from_somewhere}${to_somewhere}.');
            $notif_args_for_opponent['i18n'] = array('name');
            $notif_args_for_opponent['name'] = self::getCardName($card['id']);
            // TODO(LATER): We should stop sending the card's properties which aren't actually used.
            $notif_args_for_opponent = array_merge($notif_args_for_opponent, $card);
        } else {
            $message_for_opponent = clienttranslate('${player_name} ${action} a ${<}${age}${>}${from_somewhere}${to_somewhere}.');
            $notif_args_for_opponent['age'] = $card['age'];
            $notif_args_for_opponent['type'] = $card['type'];
            $notif_args_for_opponent['is_relic'] = $card['is_relic'];
        }
        if ($visible_for_others) {
            $message_for_others = clienttranslate('${player_name} ${action} ${<}${age}${>} ${<<}${name}${>>}${from_somewhere}${to_somewhere}.');
            $notif_args_for_others['i18n'] = array('name');
            $notif_args_for_others['name'] = self::getCardName($card['id']);
            // TODO(LATER): We should stop sending the card's properties which aren't actually used.
            $notif_args_for_others = array_merge($notif_args_for_others, $card);
        } else {
            $message_for_others = clienttranslate('${player_name} ${action} a ${<}${age}${>}${from_somewhere}${to_somewhere}.');
            $notif_args_for_others['age'] = $card['age'];
            $notif_args_for_others['type'] = $card['type'];
            $notif_args_for_others['is_relic'] = $card['is_relic'];
        }

        $player_id = $transferInfo['player_id'];
        $opponent_id = $transferInfo['opponent_id'];
        $transferInfo = self::stripTransferInfoForNotification($transferInfo);
        $info = array_merge($transferInfo, $progressInfo);
        $notif_args_for_player = array_merge($notif_args_for_player, $info, self::getDelimiterMeanings($message_for_player, $card['id']));
        $notif_args_for_opponent = array_merge($notif_args_for_opponent, $info, self::getDelimiterMeanings($message_for_opponent, $card['id']));
        $notif_args_for_others = array_merge($notif_args_for_others, $info, self::getDelimiterMeanings($message_for_others, $card['id']));


        self::notifyPlayer($player_id, "transferedCard", $message_for_player, $notif_args_for_player);
        self::notifyPlayer($opponent_id, "transferedCard", $message_for_opponent, $notif_args_for_opponent);
        self::notifyAllPlayersBut(array($player_id, $opponent_id), "transferedCard", $message_for_others, $notif_args_for_others);
    }

    function getTransferInfoWithTwoPlayersInvolved($location_from, $location_to, $player_id_is_owner_from, $player_id_is_owner_to, $opponent_id_is_owner_from, $opponent_id_is_owner_to, $bottom_from, $bottom_to, $score_keyword, $meld_keyword, $you_must, $player_must, $your, $player_name, $opponent_name, $number, $cards)
    {

        // TODO(4E): Pass these keywords in.
        $safeguard_keyword = false;
        $achieve_keyword = false;

        // Text used for the active player
        $message_for_player = clienttranslate('${You_must} ${action} ${number} ${card_qualifier}${card}${from_somewhere}${to_somewhere}');
        $from_somewhere_for_player = '';
        $to_somewhere_for_player = '';

        // Text used for the opponent
        $message_for_opponent = clienttranslate('${player_must} ${action} ${number} ${card_qualifier}${card}${from_somewhere}${to_somewhere}');
        $from_somewhere_for_opponent = '';
        $to_somewhere_for_opponent = '';

        // Text used for the other players
        $message_for_others = clienttranslate('${player_must} ${action} ${number} ${card_qualifier}${card}${from_somewhere}${to_somewhere}');
        $from_somewhere_for_others = '';
        $to_somewhere_for_others = '';

        // Text used for all players
        $action = clienttranslate('transfer');
        $card_qualifier = '';

        // Update text based on where the card is coming from
        if ($location_from === Locations::HAND) {
            if ($player_id_is_owner_from) {
                $from_somewhere_for_player = clienttranslate(' from your hand');
                $from_somewhere_for_opponent = clienttranslate(' from his hand');
                $from_somewhere_for_others = clienttranslate(' from his hand');
            } else if ($opponent_id_is_owner_from) {
                $from_somewhere_for_player = clienttranslate(' from ${opponent_name}\'s hand');
                $from_somewhere_for_opponent = clienttranslate(' from ${your} hand');
                $from_somewhere_for_others = clienttranslate(' from ${opponent_name}\'s hand');
            }
        } else if ($location_from === Locations::BOARD) {
            if ($player_id_is_owner_from) {
                $from_somewhere_for_player = clienttranslate(' from your board');
                $from_somewhere_for_opponent = clienttranslate(' from his board');
                $from_somewhere_for_others = clienttranslate(' from his board');
            } else if ($opponent_id_is_owner_from) {
                $from_somewhere_for_player = clienttranslate(' from ${opponent_name}\'s board');
                $from_somewhere_for_opponent = clienttranslate(' from ${your} board');
                $from_somewhere_for_others = clienttranslate(' from ${opponent_name}\'s board');
            }
        } else if ($location_from === Locations::SCORE) {
            if ($player_id_is_owner_from) {
                $from_somewhere_for_player = clienttranslate(' from your score pile');
                $from_somewhere_for_opponent = clienttranslate(' from his score pile');
                $from_somewhere_for_others = clienttranslate(' from his score pile');
            } else if ($opponent_id_is_owner_from) {
                $from_somewhere_for_player = clienttranslate(' from ${opponent_name}\'s score pile');
                $from_somewhere_for_opponent = clienttranslate(' from ${your} score pile');
                $from_somewhere_for_others = clienttranslate(' from ${opponent_name}\'s score pile');
            }
        } else if ($location_from === Locations::SAFE) {
            if ($player_id_is_owner_from) {
                $from_somewhere_for_player = clienttranslate(' from your safe');
                $from_somewhere_for_opponent = clienttranslate(' from his safe');
                $from_somewhere_for_others = clienttranslate(' from his safe');
            } else if ($opponent_id_is_owner_from) {
                $from_somewhere_for_player = clienttranslate(' from ${opponent_name}\'s safe');
                $from_somewhere_for_opponent = clienttranslate(' from ${your} safe');
                $from_somewhere_for_others = clienttranslate(' from ${opponent_name}\'s safe');
            }
        } else if ($location_from === Locations::REVEALED) {
            $card_qualifier = clienttranslate('revealed ');
        }

        // Update text based on where the card is going to
        if ($location_to === 'hand') {
            if ($player_id_is_owner_to) {
                $to_somewhere_for_player = clienttranslate(' to your hand');
                $to_somewhere_for_opponent = clienttranslate(' to his hand');
                $to_somewhere_for_others = clienttranslate(' to his hand');
            } else if ($opponent_id_is_owner_to) {
                $to_somewhere_for_player = clienttranslate(' to ${opponent_name}\'s hand');
                $to_somewhere_for_opponent = clienttranslate(' to ${your} hand');
                $to_somewhere_for_others = clienttranslate(' to ${opponent_name}\'s hand');
            }
        } else if ($location_to === 'score') {
            if ($score_keyword) {
                $action = clienttranslate('score');
            } else if ($player_id_is_owner_to) {
                $to_somewhere_for_player = clienttranslate(' to your score pile');
                $to_somewhere_for_opponent = clienttranslate(' to his score pile');
                $to_somewhere_for_others = clienttranslate(' to his score pile');
            } else if ($opponent_id_is_owner_to) {
                $to_somewhere_for_player = clienttranslate(' to ${opponent_name}\'s score pile');
                $to_somewhere_for_opponent = clienttranslate(' to ${your} score pile');
                $to_somewhere_for_others = clienttranslate(' to ${opponent_name}\'s score pile');
            }
        } else if ($location_to === 'board') {
            if ($bottom_to) {
                $action = clienttranslate('tuck');
            } else if ($meld_keyword) {
                $action = clienttranslate('meld');
            } else if ($player_id_is_owner_to) {
                $to_somewhere_for_player = clienttranslate(' to your board');
                $to_somewhere_for_opponent = clienttranslate(' to his board');
                $to_somewhere_for_others = clienttranslate(' to his board');
            } else if ($opponent_id_is_owner_to) {
                $to_somewhere_for_player = clienttranslate(' to ${opponent_name}\'s board');
                $to_somewhere_for_opponent = clienttranslate(' to ${your} board');
                $to_somewhere_for_others = clienttranslate(' to ${opponent_name}\'s board');
            }
        } else if ($location_to === 'achievements') {
            if ($achieve_keyword) {
                $action = clienttranslate('achieve');
            } else if ($player_id_is_owner_to) {
                $to_somewhere_for_player = clienttranslate(' to your achievements');
                $to_somewhere_for_opponent = clienttranslate(' to his achievements');
                $to_somewhere_for_others = clienttranslate(' to his achievements');
            } else if ($opponent_id_is_owner_to) {
                $to_somewhere_for_player = clienttranslate(' to ${opponent_name}\'s achievements');
                $to_somewhere_for_opponent = clienttranslate(' to ${your} achievements');
                $to_somewhere_for_others = clienttranslate(' to ${opponent_name}\'s achievements');
            }
        } else if ($location_to === 'safe') {
            if ($safeguard_keyword) {
                $action = clienttranslate('safeguard');
            } else if ($player_id_is_owner_to) {
                $to_somewhere_for_player = clienttranslate(' to your safe');
                $to_somewhere_for_opponent = clienttranslate(' to his safe');
                $to_somewhere_for_others = clienttranslate(' to his safe');
            } else if ($opponent_id_is_owner_to) {
                $to_somewhere_for_player = clienttranslate(' to ${opponent_name}\'s safe');
                $to_somewhere_for_opponent = clienttranslate(' to ${your} safe');
                $to_somewhere_for_others = clienttranslate(' to ${opponent_name}\'s safe');
            }
        } else if ($location_to === 'none') {
            $action = clienttranslate('choose');
        }

        // TODO(4E): Make sure this translates correctly.
        return [
            'message_for_player'   => [
                'i18n' => ['log'],
                'log'  => $message_for_player,
                'args' => [
                    'You_must'       => [
                        'i18n' => ['log'],
                        'log'  => $you_must,
                        'args' => [
                            'You' => 'You',
                        ],
                    ],
                    'number'         => $number,
                    'action'         => $action,
                    'card_qualifier' => $card_qualifier,
                    'card'           => $cards,
                    'from_somewhere' => ['log' => $from_somewhere_for_player, 'args' => ['opponent_name' => $opponent_name]],
                    'to_somewhere'   => ['log' => $to_somewhere_for_player, 'args' => ['opponent_name' => $opponent_name]],
                ],
            ],
            'message_for_opponent' => [
                'i18n' => ['log'],
                'log'  => $message_for_opponent,
                'args' => [
                    'player_must'    => [
                        'i18n' => ['log'],
                        'log'  => $player_must,
                        'args' => [
                            'player_name' => $player_name,
                        ],
                    ],
                    'number'         => $number,
                    'action'         => $action,
                    'card_qualifier' => $card_qualifier,
                    'card'           => $cards,
                    'from_somewhere' => ['log' => $from_somewhere_for_opponent, 'args' => ['opponent_name' => $opponent_name, 'your' => $your]],
                    'to_somewhere'   => ['log' => $to_somewhere_for_opponent, 'args' => ['opponent_name' => $opponent_name, 'your' => $your]],
                ],
            ],
            'message_for_others'   => [
                'i18n' => ['log'],
                'log'  => $message_for_others,
                'args' => [
                    'player_must'    => [
                        'i18n' => ['log'],
                        'log'  => $player_must,
                        'args' => [
                            'player_name' => $player_name,
                        ],
                    ],
                    'number'         => $number,
                    'action'         => $action,
                    'card_qualifier' => $card_qualifier,
                    'card'           => $cards,
                    'from_somewhere' => ['log' => $from_somewhere_for_others, 'args' => ['opponent_name' => $opponent_name]],
                    'to_somewhere'   => ['log' => $to_somewhere_for_others, 'args' => ['opponent_name' => $opponent_name]],
                ],
            ],
        ];
    }

    /** Returns the list of player IDs which are not adjacent to the launcher (i.e. the players used for the 4th edition distance rule) */
    function getPlayerIdsAffectedByDistanceRule($launcher_id)
    {
        if (!$this->innovationGameState->usingFourthEditionRules()) {
            return [];
        }
        if (self::decodeGameType($this->innovationGameState->get('game_type')) == 'team') {
            return [];
        }
        $player_ids = self::getActivePlayerIdsInTurnOrder($launcher_id);
        if (count($player_ids) <= 3) {
            return [];
        }
        return array_slice($player_ids, 2, count($player_ids) - 3);
    }

    function getActivePlayerIdsInTurnOrderStartingToLeftOfActingPlayer()
    {
        $current_player_index = self::playerIdToPlayerIndex(self::getCurrentPlayerUnderDogmaEffect());
        $players = self::getCollectionFromDB("SELECT player_index, player_id, player_eliminated FROM player");
        $player_id_to_left = self::playerIndexToPlayerId(($current_player_index + 1) % count($players));
        return self::getActivePlayerIdsInTurnOrder($player_id_to_left);
    }

    function getActivePlayerIdOnRightOfActingPlayer()
    {
        $player_ids = self::getActivePlayerIdsInTurnOrderStartingToLeftOfActingPlayer();
        return $player_ids[count($player_ids) - 2];
    }

    function getActivePlayerIdsInTurnOrderStartingWithCurrentPlayer()
    {
        $current_player_id = self::getCurrentPlayerUnderDogmaEffect();
        return self::getActivePlayerIdsInTurnOrder($current_player_id);
    }

    function getActivePlayerIdsInTurnOrder($starting_player_id)
    {
        if ($starting_player_id < 0) {
            // Pick an arbitrary player if it's not anyone's turn (e.g. initial meld)
            $starting_index = 0;
        } else {
            $starting_index = self::getUniqueValueFromDB(self::format("SELECT player_index FROM player WHERE player_id={starting_player_id}", array('starting_player_id' => $starting_player_id)));
        }

        $players = self::getCollectionFromDB("SELECT player_index, player_id, player_eliminated FROM player");
        $num_players = count($players);

        $player_ids = [];
        for ($i = 0; $i < $num_players; $i++) {
            $index = ($starting_index + $i) % $num_players;
            if ($players[$index]['player_eliminated'] == 0) {
                $player_ids[] = $players[$index]['player_id'];
            }
        }
        return $player_ids;
    }

    /** Checks to see if any players are eligible for special achievements. **/
    function checkForSpecialAchievements($is_end_of_action_check = false)
    {
        if ($this->innovationGameState->usingFourthEditionRules()) {
            $player_ids = self::getActivePlayerIdsInTurnOrderStartingToLeftOfActingPlayer();
        } else {
            // "In the rare case that two players simultaneously become eligible to claim a special achievement,
            // the tie is broken in turn order going clockwise, with the current player winning ties."
            // https://boardgamegeek.com/thread/2710666/simultaneous-special-achievements-tiebreaker
            $player_ids = self::getActivePlayerIdsInTurnOrderStartingWithCurrentPlayer();
        }

        foreach ($player_ids as $player_id) {
            self::checkForSpecialAchievementsForPlayer($player_id, $is_end_of_action_check);
        }
    }

    /** Checks if the player meets the conditions to get a special achievement. Do the transfer if he does. **/
    function checkForSpecialAchievementsForPlayer($player_id, $is_end_of_action_check)
    {
        // TODO(FIGURES): Update this once there are other special achievements to test for.
        $edition = $this->innovationGameState->getEdition();
        $achievements_to_test = [];
        if ($edition <= 3 || $is_end_of_action_check) {
            $achievements_to_test = array_merge($achievements_to_test, [105, 106, 107, 108, 109]);
        } else if ($edition <= 3) {
            array_merge($achievements_to_test, [CardIds::MONUMENT]);
        }
        if ($this->innovationGameState->echoesExpansionEnabled() && ($edition <= 3 || $is_end_of_action_check)) {
            $achievements_to_test = array_merge($achievements_to_test, [435, 436, 437, 438, 439]);
        }
        if ($this->innovationGameState->unseenExpansionEnabled() && $is_end_of_action_check) {
            $achievements_to_test = array_merge($achievements_to_test, [595, 596, 597, 598, 599]);
        }

        $end_of_game_exception = null;

        foreach ($achievements_to_test as $achievement_id) {
            $achievement = self::getCardInfo($achievement_id);

            // Only continue if the achievement is claimable.
            if ($achievement['owner'] != 0 || $achievement['location'] != 'achievements') {
                continue;
            }

            switch ($achievement_id) {
                case 105: // Empire: three or more icons of the six main icon types
                    $num_resources_with_three_or_more = 0;
                    foreach (self::getPlayerResourceCounts($player_id) as $icon => $count) {
                        if ($icon <= 6 && $count >= 3) {
                            $num_resources_with_three_or_more++;
                        }
                    }
                    $eligible = $num_resources_with_three_or_more >= 6;
                    break;
                case CardIds::MONUMENT: // Monument: 
                    if ($edition <= 3) { // tuck 6 cards or score 6 cards
                        $flags = self::getFlagsForMonument($player_id);
                        $eligible = $flags['number_of_tucked_cards'] >= 6 || $flags['number_of_scored_cards'] >= 6;
                    } else { // at least four top cards with a demand effect
                        $num_cards_with_demand_effect = 0;
                        foreach (self::getTopCardsOnBoard($player_id) as $card) {
                            if ($card['has_demand'] == true) {
                                $num_cards_with_demand_effect++;
                            }
                        }
                        $eligible = $num_cards_with_demand_effect >= 4;
                    }
                    break;
                case 107: // Wonder: 5 colors, each being splayed right, up, or aslant
                    $eligible = true;
                    foreach (Colors::ALL as $color) {
                        if (self::getCurrentSplayDirection($player_id, $color) <= 1) { // This color is missing, unsplayed or splayed left
                            $eligible = false;
                            break;
                        }
                        ;
                    }
                    break;
                case 108: // World: 12 or more visible clocks (icon 6) on the board 
                    $eligible = self::getPlayerSingleRessourceCount($player_id, 6) >= 12;
                    break;
                case 109: // Universe: Five top cards, each being of value 8 or more
                    $eligible = true;
                    foreach (Colors::ALL as $color) {
                        $top_card = self::getTopCardOnBoard($player_id, $color);
                        if ($top_card === null || $top_card['age'] < 8) { // This color is missing or its top card has value less than 8
                            $eligible = false;
                            break;
                        }
                    }
                    break;

                case 435: // Wealth: A total of 8 or more visible bonus icons
                    $eligible = count(self::getVisibleBonusesOnBoard($player_id)) >= 8;
                    break;
                case 436: // Destiny: A total of 5 cards in forecast (7 in earlier editions)
                    $eligible = self::countCardsInLocation($player_id, 'forecast') >= ($this->innovationGameState->usingFourthEditionRules() ? 5 : 7);
                    break;
                case 437: // Heritage: 8 or more visible hexagons in a pile
                    $eligible = false;
                    foreach (Colors::ALL as $color) {
                        if (self::countVisibleIconsInPile($player_id, 0 /* empty hex */ , $color) >= 8) {
                            $eligible = true;
                            break;
                        }
                    }
                    break;
                case 438: // History: A total of 4 or more visible echo effects in a pile
                    $eligible = false;
                    foreach (Colors::ALL as $color) {
                        if (self::countVisibleIconsInPile($player_id, 10 /* echo effect */ , $color) >= 4) {
                            $eligible = true;
                            break;
                        }
                    }
                    break;
                case 439: // Supremacy: 4 different piles have at least 3 of the same icon
                    $eligible = false;
                    for ($icon = 1; $icon <= 7 && !$eligible; $icon++) {
                        $num_piles = 0;
                        foreach (Colors::ALL as $color) {
                            if (self::countVisibleIconsInPile($player_id, $icon, $color) >= 3) {
                                $num_piles = $num_piles + 1;
                                if ($num_piles >= 4) {
                                    $eligible = true; // 4 piles found
                                    break;
                                }
                            }
                        }
                    }
                    break;
                case 595: // Confidence, age 5 minimum, 4 or more cards in safeguard
                    $eligible = false;
                    if (self::getMaxAgeOnBoardTopCards($player_id) >= 5 && self::countCardsInLocation($player_id, 'safe') >= 4) {
                        $eligible = true;
                    }
                    break;
                case 596: // Zen, age 6 minimum, no odd valued top cards
                    $eligible = true;
                    if (self::getMaxAgeOnBoardTopCards($player_id) >= 6) {
                        $top_cards = self::getTopCardsOnBoard($player_id);
                        foreach ($top_cards as $card) {
                            if ($card !== null) {
                                if (
                                    $card['faceup_age'] == 1 || $card['faceup_age'] == 3 ||
                                    $card['faceup_age'] == 5 || $card['faceup_age'] == 7 ||
                                    $card['faceup_age'] == 9 || $card['faceup_age'] == 11
                                )
                                    $eligible = false;
                            }
                        }
                    } else {
                        $eligible = false;
                    }
                    break;
                case 597: // Anonymity, age 7 minimum and no standard achievements
                    $eligible = true;
                    if (self::getMaxAgeOnBoardTopCards($player_id) >= 7) {
                        foreach (self::getCardsInLocation($player_id, 'achievements') as $card) {
                            if ($card['age'] !== null) { // aged achievement
                                $eligible = false;
                            }
                        }
                    } else {
                        $eligible = false;
                    }

                    break;
                case 598: // Folklore, age 8 minimum, no factories
                    $eligible = false;
                    if (self::getMaxAgeOnBoardTopCards($player_id) >= 8 && self::getPlayerResourceCounts($player_id)[5] == 0) {
                        $eligible = true;
                    }
                    break;
                case 599: // Mystery - age 9 minimum, less than 5 top cards
                    $eligible = false;
                    if (self::getMaxAgeOnBoardTopCards($player_id) >= 9) {
                        $top_cards = self::countCardsInLocationKeyedByColor($player_id, 'board');
                        $top_card_count = 0;
                        foreach (Colors::ALL as $color) {
                            if ($top_cards[$color] > 0) {
                                $top_card_count++;
                            }
                        }
                        if ($top_card_count < 5) {
                            $eligible = true;
                        }
                    }
                    break;

                default:
                    break;
            }

            if ($eligible) { // The player meet the conditions to achieve
                try {
                    self::transferCardFromTo($achievement, $player_id, 'achievements');
                } catch (EndOfGame $e) { // End of game has been detected
                    self::trace('EOG bubbled but suspended from self::checkForSpecialAchievementsForPlayer');
                    $end_of_game_exception = $e;
                    continue; // But the other achievements must be checked as well before ending
                }
            }
        }
        // All special achievements have been checked
        if ($end_of_game_exception instanceof EndOfGame) { // End of game has been detected
            self::trace('EOG bubbled from self::checkForSpecialAchievementsForPlayer');
            throw $end_of_game_exception; // Re-throw the flag
        }
    }

    /** Checks to see if any players lose any flag/fountain achievements. **/
    function removeOldFlagsAndFountains()
    {
        if (!$this->innovationGameState->citiesExpansionEnabled()) {
            return;
        }

        foreach (self::getActivePlayerIdsInTurnOrderStartingWithCurrentPlayer() as $player_id) {
            $opponent_ids = self::getActiveOpponentIds($player_id);
            foreach (Colors::ALL as $color) {
                // Flags
                $num_visible_flags = self::countVisibleIconsInPile($player_id, 8 /* flag */ , $color);
                $num_visible_cards = self::countVisibleCards($player_id, $color);
                $opponent_has_more_visible_cards = false;
                foreach ($opponent_ids as $opponent_id) {
                    if (self::countVisibleCards($opponent_id, $color) > $num_visible_cards) {
                        $opponent_has_more_visible_cards = true;
                    }
                }
                $desired_flag_achievements = $opponent_has_more_visible_cards ? 0 : $num_visible_flags;
                $current_flag_achievements = self::getUniqueValueFromDB(
                    self::format("
                    SELECT COUNT(*) FROM card WHERE owner = {owner} AND location = 'achievements' AND color = {color} AND 1000 <= id AND id <= 1099",
                        array('owner' => $player_id, 'color' => $color)
                    )
                );
                for ($i = $desired_flag_achievements; $i < $current_flag_achievements; $i++) {
                    $flag_id = self::getUniqueValueFromDB(
                        self::format("
                        SELECT MIN(id) FROM card WHERE owner = {owner} AND location = 'achievements' AND color = {color} AND 1000 <= id AND id <= 1099",
                            array('owner' => $player_id, 'color' => $color)
                        )
                    );
                    self::transferCardFromTo(self::getCardInfo($flag_id), 0, 'flags');
                }

                // Fountains
                $desired_fountain_achievements = self::countVisibleIconsInPile($player_id, 9 /* fountain */ , $color);
                $current_fountain_achievements = self::getUniqueValueFromDB(
                    self::format("
                    SELECT COUNT(*) FROM card WHERE owner = {owner} AND location = 'achievements' AND color = {color} AND id >= 1100",
                        array('owner' => $player_id, 'color' => $color)
                    )
                );
                for ($i = $desired_fountain_achievements; $i < $current_fountain_achievements; $i++) {
                    $fountain_id = self::getUniqueValueFromDB(
                        self::format("
                        SELECT MIN(id) FROM card WHERE owner = {owner} AND location = 'achievements' AND color = {color} AND id >= 1100",
                            array('owner' => $player_id, 'color' => $color)
                        )
                    );
                    self::transferCardFromTo(self::getCardInfo($fountain_id), 0, 'fountains');
                }
            }
        }
    }

    /** Checks to see if any players gain any flag/fountain achievements. **/
    function addNewFlagsAndFountains()
    {
        if (!$this->innovationGameState->citiesExpansionEnabled()) {
            return;
        }

        $end_of_game = false;

        foreach (self::getActivePlayerIdsInTurnOrderStartingWithCurrentPlayer() as $player_id) {
            $opponent_ids = self::getActiveOpponentIds($player_id);
            foreach (Colors::ALL as $color) {
                // Flags
                $num_visible_flags = self::countVisibleIconsInPile($player_id, Icons::FLAG, $color);
                $num_visible_cards = self::countVisibleCards($player_id, $color);
                $opponent_has_more_visible_cards = false;
                foreach ($opponent_ids as $opponent_id) {
                    if (self::countVisibleCards($opponent_id, $color) > $num_visible_cards) {
                        $opponent_has_more_visible_cards = true;
                    }
                }
                $desired_flag_achievements = $opponent_has_more_visible_cards ? 0 : $num_visible_flags;
                $current_flag_achievements = self::getUniqueValueFromDB(
                    self::format("
                    SELECT COUNT(*) FROM card WHERE owner = {owner} AND location = 'achievements' AND color = {color} AND 1000 <= id AND id <= 1099",
                        array('owner' => $player_id, 'color' => $color)
                    )
                );
                for ($i = $current_flag_achievements; $i < $desired_flag_achievements; $i++) {
                    $flag_id = self::getUniqueValueFromDB(
                        self::format("
                        SELECT MIN(id) FROM card WHERE owner = 0 AND location = 'flags' AND color = {color} AND 1000 <= id AND id <= 1099",
                            array('color' => $color)
                        )
                    );
                    try {
                        self::transferCardFromTo(self::getCardInfo($flag_id), $player_id, 'achievements');
                    } catch (EndOfGame $e) {
                        $end_of_game = true;
                    }
                }

                // Fountains
                $desired_fountain_achievements = self::countVisibleIconsInPile($player_id, 9 /* fountain */ , $color);
                $current_fountain_achievements = self::getUniqueValueFromDB(
                    self::format("
                    SELECT COUNT(*) FROM card WHERE owner = {owner} AND location = 'achievements' AND color = {color} AND id >= 1100",
                        array('owner' => $player_id, 'color' => $color)
                    )
                );
                for ($i = $current_fountain_achievements; $i < $desired_fountain_achievements; $i++) {
                    $fountain_id = self::getUniqueValueFromDB(
                        self::format("
                        SELECT MIN(id) FROM card WHERE owner = 0 AND location = 'fountains' AND color = {color} AND id >= 1100",
                            array('color' => $color)
                        )
                    );
                    try {
                        self::transferCardFromTo(self::getCardInfo($fountain_id), $player_id, 'achievements');
                    } catch (EndOfGame $e) {
                        $end_of_game = true;
                    }
                }
            }
        }

        if ($end_of_game) { // End of game has been detected
            self::trace('EOG bubbled from self::addNewFlagsAndFountains');
            throw $e; // Re-throw the flag
        }
    }

    /** Database management for Monument special achievement **/
    function incrementFlagForMonument($player_id, $column_name)
    { // The player tucked or scored a card. Update database accordingly
        self::DbQuery(
            self::format("
            UPDATE
                player
            SET
                {column_name} = {column_name} + 1
            WHERE
                player_id = {player_id}
        ",
                array('player_id' => $player_id, 'column_name' => $column_name)
            )
        );
    }

    function resetFlagsForMonument()
    { // The turn of the current player has ended. Set the numbers of tuck cards and scored cards back to zero for all players 
        self::notifyAll('resetMonumentCounters', '', array());
        self::DbQuery("
            UPDATE
                player
            SET
                number_of_tucked_cards = 0,
                number_of_scored_cards = 0
        ");
    }

    function getFlagsForMonument($player_id)
    { // Query the number of cards the player tucked or scored so far during the turn of the current player
        return self::getObjectFromDB(
            self::format("
            SELECT
                number_of_tucked_cards, number_of_scored_cards
            FROM
                player
            WHERE
                player_id = {player_id}
        ",
                array('player_id' => $player_id)
            )
        );
    }

    function notifyForSplay($player_id, $target_player_id, $color, $splay_direction, $force_unsplay)
    {

        $new_score = self::updatePlayerScore($target_player_id);

        if ($splay_direction == 0 && !$force_unsplay) {
            $color_in_clear = Colors::render($color);

            if ($player_id != $target_player_id) {
                throw new BgaVisibleSystemException(self::format(self::_("Unhandled case in {function}: '{code}'"), array('function' => "notifyForSplay()", 'code' => 'player_id != target_player_id in unsplay event')));
            }

            self::notifyPlayer(
                $target_player_id,
                'splayedPile',
                clienttranslate('${Your} ${colored} stack is reduced to one card so it loses its splay.'),
                array(
                    'i18n'            => array('colored'),
                    'Your'            => 'Your',
                    'colored'         => $color_in_clear,
                    'player_id'       => $target_player_id,
                    'color'           => $color,
                    'splay_direction' => $splay_direction,
                    'new_score'       => $new_score,
                )
            );

            self::notifyAllPlayersBut(
                $target_player_id,
                'splayedPile',
                clienttranslate('${player_name}\'s ${colored} stack is reduced to one card so it loses its splay.'),
                array(
                    'i18n'            => array('colored'),
                    'player_name'     => self::getPlayerNameFromId($target_player_id),
                    'colored'         => $color_in_clear,
                    'player_id'       => $target_player_id,
                    'color'           => $color,
                    'splay_direction' => $splay_direction,
                    'new_score'       => $new_score,
                )
            );
            return;
        }

        $splay_direction_in_clear = Directions::render($splay_direction);
        $colored_cards = self::renderColorCards($color);

        // Update player ressources
        $new_ressource_counts = self::updatePlayerRessourceCounts($target_player_id);

        if ($player_id == $target_player_id) {

            self::notifyPlayer(
                $player_id,
                'splayedPile',
                $force_unsplay ? clienttranslate('${You} unsplay your ${colored_cards}.') : clienttranslate('${You} splay your ${colored_cards} ${splay_direction_in_clear}.'),
                array(
                    'i18n'                     => array('colored_cards', 'splay_direction_in_clear'),
                    'player_id'                => $player_id,
                    'You'                      => 'You',
                    'color'                    => $color,
                    'colored_cards'            => $colored_cards,
                    'splay_direction'          => $splay_direction,
                    'splay_direction_in_clear' => $splay_direction_in_clear,
                    'new_ressource_counts'     => $new_ressource_counts,
                    'forced_unsplay'           => $force_unsplay,
                    'new_score'                => $new_score,
                )
            );

            self::notifyAllPlayersBut(
                $player_id,
                'splayedPile',
                $force_unsplay ? clienttranslate('${player_name} unsplays his ${colored_cards}.') : clienttranslate('${player_name} splays his ${colored_cards} ${splay_direction_in_clear}.'),
                array(
                    'i18n'                     => array('colored_cards', 'splay_direction_in_clear'),
                    'player_id'                => $player_id,
                    'player_name'              => self::getPlayerNameFromId($player_id),
                    'color'                    => $color,
                    'colored_cards'            => $colored_cards,
                    'splay_direction'          => $splay_direction,
                    'splay_direction_in_clear' => $splay_direction_in_clear,
                    'new_ressource_counts'     => $new_ressource_counts,
                    'forced_unsplay'           => $force_unsplay,
                    'new_score'                => $new_score,
                )
            );

        } else {

            self::notifyPlayer(
                $player_id,
                'splayedPile',
                $force_unsplay ? clienttranslate('${You} unsplay ${target_player_name}\'s ${colored_cards}.') : clienttranslate('${You} splay ${target_player_name}\'s ${colored_cards} ${splay_direction_in_clear}.'),
                array(
                    'i18n'                     => array('colored_cards', 'splay_direction_in_clear'),
                    'player_id'                => $target_player_id,
                    'You'                      => 'You',
                    'target_player_name'       => self::getPlayerNameFromId($target_player_id),
                    'color'                    => $color,
                    'colored_cards'            => $colored_cards,
                    'splay_direction'          => $splay_direction,
                    'splay_direction_in_clear' => $splay_direction_in_clear,
                    'new_ressource_counts'     => $new_ressource_counts,
                    'forced_unsplay'           => $force_unsplay,
                    'new_score'                => $new_score,
                )
            );

            self::notifyPlayer(
                $target_player_id,
                'splayedPile',
                $force_unsplay ? clienttranslate('${player_name} unsplays your ${colored_cards}.') : clienttranslate('${player_name} splays your ${colored_cards} ${splay_direction_in_clear}.'),
                array(
                    'i18n'                     => array('colored_cards', 'splay_direction_in_clear'),
                    'player_id'                => $target_player_id,
                    'player_name'              => self::getPlayerNameFromId($player_id),
                    'color'                    => $color,
                    'colored_cards'            => $colored_cards,
                    'splay_direction'          => $splay_direction,
                    'splay_direction_in_clear' => $splay_direction_in_clear,
                    'new_ressource_counts'     => $new_ressource_counts,
                    'forced_unsplay'           => $force_unsplay,
                    'new_score'                => $new_score,
                )
            );

            self::notifyAllPlayersBut(
                array($player_id, $target_player_id),
                'splayedPile',
                $force_unsplay ? clienttranslate('${player_name} unsplays ${target_player_name}\'s ${colored_cards}.') : clienttranslate('${player_name} splays ${target_player_name}\'s ${colored_cards} ${splay_direction_in_clear}.'),
                array(
                    'i18n'                     => array('colored_cards', 'splay_direction_in_clear'),
                    'player_id'                => $target_player_id,
                    'player_name'              => self::getPlayerNameFromId($player_id),
                    'target_player_name'       => self::getPlayerNameFromId($target_player_id),
                    'color'                    => $color,
                    'colored_cards'            => $colored_cards,
                    'splay_direction'          => $splay_direction,
                    'splay_direction_in_clear' => $splay_direction_in_clear,
                    'new_ressource_counts'     => $new_ressource_counts,
                    'forced_unsplay'           => $force_unsplay,
                    'new_score'                => $new_score,
                )
            );

        }
    }

    /** Notify end of game **/
    function notifyEndOfGameByAchievements()
    {
        // Display who won and with how many achievements
        // (There can be weird cases when two players tie or one player get more achievements than needed if two or more special achievements are claimed at the same time)
        $players = self::getCollectionFromDb("SELECT player_id, player_score FROM player");
        $number_of_achievements_needed_to_win = $this->innovationGameState->get('number_of_achievements_needed_to_win');
        $number_of_achievements_winner = $number_of_achievements_needed_to_win;
        $winners = array();

        foreach ($players as $player_id => $player) {
            if ($player['player_score'] == $number_of_achievements_winner) {
                $winners[] = $player_id;
            } else if ($player['player_score'] > $number_of_achievements_winner) {
                $number_of_achievements_winner = $player['player_score'];
                $winners = array($player_id);
            }
        }

        foreach ($winners as $player_id) {
            if (self::decodeGameType($this->innovationGameState->get('game_type')) == 'individual') {
                self::notifyAllPlayersBut(
                    $player_id,
                    "log",
                    clienttranslate('END OF GAME BY ACHIEVEMENTS: ${player_name} has got ${n} achievements. He wins!'),
                    array(
                        'n'           => $number_of_achievements_winner,
                        'player_name' => self::getPlayerNameFromId($player_id)
                    )
                );

                self::notifyPlayer(
                    $player_id,
                    "log",
                    clienttranslate('END OF GAME BY ACHIEVEMENTS: ${You} have got ${n} achievements. You win!'),
                    array(
                        'n'   => $number_of_achievements_winner,
                        'You' => 'You'
                    )
                );
            } else { // Team game
                $teammate_id = self::getPlayerTeammate($player_id);
                $winning_team = array($player_id, $teammate_id);
                self::notifyAllPlayersBut(
                    $winning_team,
                    "log",
                    clienttranslate('END OF GAME BY ACHIEVEMENTS: The other team has got ${n} achievements. They win!'),
                    array(
                        'n' => $number_of_achievements_winner
                    )
                );

                foreach ($winning_team as $player_id) {
                    self::notifyPlayer(
                        $player_id,
                        "log",
                        clienttranslate('END OF GAME BY ACHIEVEMENTS: Your team has got ${n} achievements. You win!'),
                        array(
                            'n' => $number_of_achievements_winner
                        )
                    );
                }
            }
        }
    }

    function notifyEndOfGameByScore()
    {
        $player_id = $this->innovationGameState->get('player_who_could_not_draw');
        $max_age = self::getMaxAge();
        if (self::decodeGameType($this->innovationGameState->get('game_type')) == 'individual') {
            self::notifyAllPlayersBut(
                $player_id,
                "log",
                clienttranslate('END OF GAME BY SCORE: ${player_name} attempts to draw a card above ${age_10}. The player with the greatest score win.'),
                array(
                    'player_name' => self::getPlayerNameFromId($player_id),
                    'age_10'      => $max_age
                )
            );

            self::notifyPlayer(
                $player_id,
                "log",
                clienttranslate('END OF GAME BY SCORE: ${You} attempt to draw a card above ${age_10}. The player with the greatest score win.'),
                array(
                    'You'    => 'You',
                    'age_10' => $max_age
                )
            );
        } else { // Team play
            self::notifyAllPlayersBut(
                $player_id,
                "log",
                clienttranslate('END OF GAME BY SCORE: ${player_name} attempts to draw a card above ${age_10}. The team with the greatest combined score win.'),
                array(
                    'player_name' => self::getPlayerNameFromId($player_id),
                    'age_10'      => $max_age
                )
            );

            self::notifyPlayer(
                $player_id,
                "log",
                clienttranslate('END OF GAME BY SCORE: ${You} attempt to draw a card above ${age_10}. The team with the greatest combined score win.'),
                array(
                    'You'    => 'You',
                    'age_10' => $max_age
                )
            );
        }
    }

    function notifyEndOfGameByDogma()
    {
        $dogma_card_id = self::getCurrentNestedCardState()['card_id'];
        $card_args = self::getNotificationArgsForCardList([self::getCardInfo($dogma_card_id)]);
        self::notifyAllPlayers('logWithCardTooltips', clienttranslate('END OF GAME BY DOGMA: ${card}.'), ['card' => $card_args, 'card_ids' => [$dogma_card_id]]);
    }

    /** Notify general info **/
    function notifyGeneralInfo($message, $args = array())
    {
        $delimiters = self::getDelimiterMeanings($message);
        self::notifyAll('log', $message, array_merge($args, $delimiters));
    }

    /** This function should be called whenever something changes in the game **/
    function recordThatChangeOccurred()
    {

        $nested_card_state = self::getCurrentNestedCardState();
        if ($nested_card_state == null) {
            return;
        }
        $current_effect_type = $nested_card_state['current_effect_type'];
        $current_player_under_dogma_effect = $nested_card_state['current_player_id'];

        // Tell all currently executing "The Big Bang" cards that the game state has changed.
        self::DbQuery("UPDATE nested_card_execution SET auxiliary_value = 1 WHERE card_id = 203");

        // Mark that the player under effect made a change in the game
        self::markExecutingPlayer($current_player_under_dogma_effect);

        // Remember that part of the dogma had an impact
        $this->innovationGameState->set('dogma_had_impact', 1);

        // We only need to check for sharing bonuses for the initially triggered card (otherwise Blackmail can incorrectly trigger the bonus)
        if ($nested_card_state['nesting_index'] > 0) {
            return;
        }

        // The sharing bonus is already on
        if ($this->innovationGameState->get('sharing_bonus') != 0) {
            return;
        }

        // A sharing bonus is triggered if an opponent was affected by a non-demand or echo effect
        $player_who_launched_the_dogma = $this->innovationGameState->get('active_player');
        if (($current_effect_type == 1 || $current_effect_type == 3) && $current_player_under_dogma_effect <> $player_who_launched_the_dogma && self::getPlayerTeammate($current_player_under_dogma_effect) <> $player_who_launched_the_dogma) {
            $this->innovationGameState->set('sharing_bonus', 1);
        }
    }

    // This function is used to mark a player when he executed a dogma card effect with true consequences
    function markExecutingPlayer($player_id)
    {
        self::DbQuery(
            self::format("
            UPDATE
                player
            SET
                effects_had_impact = TRUE
            WHERE
                player_id = {player_id}
        ",
                array('player_id' => $player_id)
            )
        );
    }

    function resetPlayerTable()
    {
        self::DbQuery("
            UPDATE
                player
            SET
                featured_icon_count = NULL,
                effects_had_impact = FALSE
        ");
    }

    /** Notification system for dogma **/

    function getAgeSquare($age)
    {
        return self::format("<span title='{age}' class='square N age age_{age}'>{age}</span>", array('age' => $age));
    }

    function getAgeSquareWithType($age, $type)
    {
        return self::format("<span title='{age}' class='square N age age_{age} type_{type}'>{age}</span>", array('age' => $age, 'type' => $type));
    }

    function getMusicNoteIcon()
    {
        return "<span title='music note' class='square N music_note'></span>";
    }

    function notifyDogma($card)
    {
        $player_id = self::getActivePlayerId();
        $card_id = $card['id'];

        if ($this->innovationGameState->get('current_nesting_index') == -1 && $this->innovationGameState->get('endorse_action_state') == 2) {
            $message_for_player = clienttranslate('${You} endorse the dogma of ${card} with ${[}${icon}${]} as the featured icon.');
            $message_for_others = clienttranslate('${player_name} endorses the dogma of ${card} with ${[}${icon}${]} as the featured icon.');
            self::incStat(1, 'endorse_actions_number', $player_id);
        } else {
            $message_for_player = clienttranslate('${You} activate the dogma of ${card} with ${[}${icon}${]} as the featured icon.');
            $message_for_others = clienttranslate('${player_name} activates the dogma of ${card} with ${[}${icon}${]} as the featured icon.');
        }

        $delimiters_for_player = self::getDelimiterMeanings($message_for_player, $card_id);
        $delimiters_for_others = self::getDelimiterMeanings($message_for_others, $card_id);

        $card_arg = ['card_ids' => [$card_id], 'card' => self::getNotificationArgsForCardList(array($card))];
        self::notifyPlayer(
            $player_id,
            'logWithCardTooltips',
            $message_for_player,
            array_merge(
                $card_arg,
                $delimiters_for_player,
                array(
                    'You'  => 'You',
                    'icon' => $card['dogma_icon'],
                )
            )
        );
        self::notifyAllPlayersBut(
            $player_id,
            'logWithCardTooltips',
            $message_for_others,
            array_merge(
                $card_arg,
                $delimiters_for_others,
                array(
                    'player_name' => self::getPlayerNameFromId($player_id),
                    'icon'        => $card['dogma_icon'],
                )
            )
        );
    }

    function notifyEffectOnPlayer($qualified_effect, $player_id, $launcher_id)
    {
        if (self::isExecutingAgainDueToEndorsedAction()) {
            self::notifyPlayer(
                $player_id,
                'log',
                clienttranslate('<span class="minor_information">${You} have to execute the ${qualified_effect} again because it was endorsed.</span>'),
                array(
                    'i18n'             => array('qualified_effect'),
                    'You'              => 'You',
                    'qualified_effect' => $qualified_effect
                )
            );
            self::notifyAllPlayersBut(
                $player_id,
                'log',
                clienttranslate('<span class="minor_information">${player_name} has to execute the ${qualified_effect} again because it was endorsed.</span>'),
                array(
                    'i18n'             => array('qualified_effect'),
                    'player_name'      => self::getPlayerNameFromId($player_id),
                    'qualified_effect' => $qualified_effect
                )
            );
        } else {
            self::notifyPlayer(
                $player_id,
                'log',
                clienttranslate('<span class="minor_information">${You} have to execute the ${qualified_effect}.</span>'),
                array(
                    'i18n'             => array('qualified_effect'),
                    'You'              => 'You',
                    'qualified_effect' => $qualified_effect
                )
            );
            self::notifyAllPlayersBut(
                $player_id,
                'log',
                clienttranslate('<span class="minor_information">${player_name} has to execute the ${qualified_effect}.</span>'),
                array(
                    'i18n'             => array('qualified_effect'),
                    'player_name'      => self::getPlayerNameFromId($player_id),
                    'qualified_effect' => $qualified_effect
                )
            );
        }
    }

    function notifyPass($player_id)
    {
        self::notifyPlayer(
            $player_id,
            'log',
            clienttranslate('${You} pass.'),
            array(
                'You' => 'You'
            )
        );

        self::notifyAllPlayersBut(
            $player_id,
            'log',
            clienttranslate('${player_name} passes.'),
            array(
                'player_name' => self::getPlayerNameFromId($player_id)
            )
        );
    }

    function notifyNoSelectableCards()
    {
        if ($this->innovationGameState->get('splay_direction') == -1) {
            if ($this->innovationGameState->get('n') == 0) {
                $message = clienttranslate("No card matches the criteria of the effect.");
            } else {
                $message = clienttranslate("No more card matches the criteria of the effect.");
            }
        } else {
            $message = clienttranslate("No stack matches the criteria of the effect for splaying.");
        }
        self::notifyGeneralInfo($message);
    }

    function notifyDogmaWithNoEffect($player_id, $dogma_icon)
    {
        $icon = "<span class='square N icon_" . $dogma_icon . "'></span>";

        self::notifyPlayer(
            $player_id,
            'log',
            clienttranslate('This card has only an I demand effect but nobody has fewer ${icon} than ${you}. Nothing happens.'),
            array(
                'you'  => 'you',
                'icon' => $icon
            )
        );

        self::notifyAllPlayersBut(
            $player_id,
            'log',
            clienttranslate('This card has only an I demand effect but nobody has fewer ${icon} than ${player_name}. Nothing happens.'),
            array(
                'player_name' => self::getPlayerNameFromId($player_id),
                'icon'        => $icon
            )
        );
    }

    /** Information about cards **/
    function getCardInfo($id): ?array
    {
        /**
            Get all information from the database about the card indicated by its id, which includes:
                -intrisic properties,
                -owner, location and position
        **/
        if ($id < 0) {
            return null;
        }
        return self::getNonEmptyObjectFromDB(self::format("SELECT * FROM card WHERE id = {id}", array('id' => $id)));
    }

    function getStaticInfoOfAllCards()
    {
        /**
            Get all static information about all cards in the database.
        **/
        if ($this->innovationGameState->usingFourthEditionRules()) {
            $cards = self::getObjectListFromDB("SELECT id, type, age, faceup_age, color, spot_1, spot_2, spot_3, spot_4, spot_5, spot_6, dogma_icon, is_relic, has_demand FROM `card` WHERE `location` != 'removed'");
        } else {
            $cards = self::getObjectListFromDB("SELECT id, type, age, faceup_age, color, spot_1, spot_2, spot_3, spot_4, spot_5, spot_6, dogma_icon, is_relic FROM `card`");
        }
        return self::attachTextualInfoToList($cards);
    }

    function getCardInfoFromPosition($owner, $location, $age, $type, $is_relic, $position)
    {
        /**
            Get all information from the database about the card indicated by its position
        **/
        return self::getObjectFromDB(
            self::format("
                SELECT * FROM card WHERE
                    owner = {owner}
                    AND location = '{location}'
                    AND age = {age}
                    AND type = {type}
                    AND is_relic = {is_relic}
                    AND position = {position}
            ",
                array('owner' => $owner, 'location' => $location, 'age' => $age, 'type' => $type, 'is_relic' => $is_relic, 'position' => $position)
            )
        );
    }

    function getCardIds($cards)
    {
        $card_ids = array();
        foreach ($cards as $card) {
            $card_ids[] = $card['id'];
        }
        return $card_ids;
    }

    function getCardName($id)
    {
        if ($id >= 1000) { // Flags and fountains
            return null;
        }
        return self::getCardPropertyForCurrentVersion('name', $id);
    }

    function getNonDemandEffect($id, $effect_number)
    {
        return self::getCardPropertyForCurrentVersion('non_demand_effect_' . $effect_number, $id);
    }

    function getDemandEffect($id)
    {
        return self::getCardPropertyForCurrentVersion('i_demand_effect', $id);
    }

    function getCompelEffect($id)
    {
        return self::getCardPropertyForCurrentVersion('i_compel_effect', $id);
    }

    function getEchoEffect($id)
    {
        return self::getCardPropertyForCurrentVersion('echo_effect', $id);
    }

    function getCardPropertyForCurrentVersion($prefix, $id)
    {
        $card_info = $this->textual_card_infos[$id];
        if (array_key_exists($prefix, $card_info)) {
            return $card_info[$prefix];
        }
        $edition = $this->innovationGameState->getEdition();
        if ($edition == 1 && array_key_exists($prefix . '_first', $card_info)) {
            return $card_info[$prefix . '_first'];
        }
        if ($edition <= 3 && array_key_exists($prefix . '_first_and_third', $card_info)) {
            return $card_info[$prefix . '_first_and_third'];
        }
        if ($edition == 3 && array_key_exists($prefix . '_third', $card_info)) {
            return $card_info[$prefix . '_third'];
        }
        if ($edition >= 3 && array_key_exists($prefix . '_third_and_fourth', $card_info)) {
            return $card_info[$prefix . '_third_and_fourth'];
        }
        if ($edition == 4 && array_key_exists($prefix . '_fourth', $card_info)) {
            return $card_info[$prefix . '_fourth'];
        }
        return null;
    }

    function unsetVersionedCardProperties($textual_infos, $prefix)
    {
        unset($textual_infos[$prefix . '_first']);
        unset($textual_infos[$prefix . '_first_and_third']);
        unset($textual_infos[$prefix . '_third']);
        unset($textual_infos[$prefix . '_third_and_fourth']);
        unset($textual_infos[$prefix . '_fourth']);
    }

    function attachTextualInfo($card)
    {
        if ($card === null) {
            return null;
        }

        if (!array_key_exists($card['id'], $this->textual_card_infos)) {
            return $card;
        }

        $id = $card['id'];
        $textual_infos = $this->textual_card_infos[$id];

        // Make sure the name reflects the current edition
        $textual_infos['name'] = self::getCardName($id);
        self::unsetVersionedCardProperties($textual_infos, 'name');

        // Make sure the echo effect reflects the current edition
        $textual_infos['echo_effect'] = self::getEchoEffect($id);
        if ($textual_infos['echo_effect'] === null) {
            unset($textual_infos['echo_effect']);
        }
        self::unsetVersionedCardProperties($textual_infos, 'echo_effect');

        // Make sure the demand effect reflects the current edition
        $textual_infos['i_demand_effect'] = self::getDemandEffect($id);
        if ($textual_infos['i_demand_effect'] === null) {
            unset($textual_infos['i_demand_effect']);
        }
        self::unsetVersionedCardProperties($textual_infos, 'i_demand_effect');

        // Make sure the compel effect reflects the current edition
        $textual_infos['i_compel_effect'] = self::getCompelEffect($id);
        if ($textual_infos['i_compel_effect'] === null) {
            unset($textual_infos['i_compel_effect']);
        }
        self::unsetVersionedCardProperties($textual_infos, 'i_compel_effect');

        // Make sure the non-demand effects reflects the current edition
        for ($i = 1; $i <= 3; $i++) {
            $non_demand = 'non_demand_effect_' . $i;
            $textual_infos[$non_demand] = self::getNonDemandEffect($id, $i);
            if ($textual_infos[$non_demand] === null) {
                unset($textual_infos[$non_demand]);
            }
            self::unsetVersionedCardProperties($textual_infos, $non_demand);
        }

        // Make sure the condition for claiming the special achievement reflects the current edition
        $textual_infos['condition_for_claiming'] = self::getCardPropertyForCurrentVersion('condition_for_claiming', $id);
        if ($textual_infos['condition_for_claiming'] === null) {
            unset($textual_infos['condition_for_claiming']);
        }
        self::unsetVersionedCardProperties($textual_infos, 'condition_for_claiming');

        unset($textual_infos['separate_4E_implementation']);
        return array_merge($card, $textual_infos);
    }

    function attachTextualInfoToList($card_list)
    {
        foreach ($card_list as &$card) {
            $card = self::attachTextualInfo($card);
        }
        return $card_list;
    }

    /**
     * Returns true if card_1 comes before card_2 in English alphabetical order.
     *
     * @param array $card1
     * @param array $card2
     * @return bool
     */
    public function comesAlphabeticallyBefore($card1, $card2): bool
    {
        $name1 = $this->getCardName($card1['id']);
        $name2 = $this->getCardName($card2['id']);
        return Strings::doesStringComeBefore($name1, $name2);
    }

    function getDeckTopCard($age, $type)
    {
        /**
            Get all information of the card to be drawn from the deck of the type and age indicated, which includes:
                -intrisic properties,
                -owner, location and position
        **/

        return self::getObjectFromDB(
            self::format("
            SELECT
                *
            FROM
                card
            WHERE
                location = 'deck' AND
                type = {type} AND
                age = {age} AND
                position = (SELECT MAX(position) FROM card WHERE location = 'deck' AND type = {type} AND age = {age})
        ",
                array('type' => $type, 'age' => $age)
            )
        );
    }

    function getDeckBottomCard($age, $type)
    {
        /**
            Get all information of the card to be taken from the bottom of the deck of the type and age indicated, which includes:
                -intrisic properties,
                -owner, location and position
        **/

        return self::getObjectFromDB(
            self::format("
            SELECT
                *
            FROM
                card
            WHERE
                location = 'deck' AND
                type = {type} AND
                age = {age} AND
                position = 0
        ",
                array('type' => $type, 'age' => $age)
            )
        );
    }

    function getAgeToDrawIn($player_id, $age_min = null)
    {
        if ($age_min === null) {
            // $age_min is the maximum age on player board
            $age_min = self::getMaxAgeOnBoardTopCards($player_id);
        }
        if ($age_min < 1) {
            $age_min = 1;
        }

        $deck_count = self::countCardsInLocationKeyedByAge(0, 'deck', CardTypes::BASE);
        $age_to_draw = $age_min;
        $max_age = self::getMaxAge();
        while ($age_to_draw <= $max_age && $deck_count[$age_to_draw] == 0) {
            $age_to_draw++;
        }
        return $age_to_draw;
    }

    function getCurrentSplayDirection($player_id, $color): int
    {
        $splay_direction = self::getUniqueValueFromDB(
            self::format("
            SELECT
                splay_direction
            FROM
                card
            WHERE
                owner = {owner} AND
                location = 'board' AND
                color = {color} AND
                position = 0
        ",
                array('owner' => $player_id, 'color' => $color)
            )
        );

        return $splay_direction === null ? Directions::UNSPLAYED : intval($splay_direction);
    }

    function getIdsOfCardsInLocation($owner, $location)
    {
        /**
            Get all cards in a particular location, sorted by position
        **/

        return self::getObjectListFromDB(self::format("
                SELECT
                    id
                FROM
                    card
                WHERE
                    owner = {owner} AND
                    location = '{location}'
                ORDER BY
                    position
            ",
            array('owner' => $owner, 'location' => $location)
        ), true);
    }

    function getIdsOfHighestOrLowestCardsInLocation($owner, $location, $highest)
    {
        /**
            Get all highest or lowest cards in a particular location, sorted by position
        **/

        return self::getObjectListFromDB(self::format("
                SELECT
                    id
                FROM
                    card
                WHERE
                    owner = {owner} AND
                    location = '{location}' AND
                    age = (
                        SELECT
                            {min_or_max}(age)
                        FROM
                            card
                        WHERE
                            owner = {owner} AND
                            location = '{location}'
                    )
                ORDER BY
                    position
            ",
            array('owner' => $owner, 'location' => $location, 'min_or_max' => $highest ? 'MAX' : 'MIN')
        ), true);
    }

    function getIdsOfHighestCardsInLocation($owner, $location)
    {
        /**
            Get all highest cards in a particular location, sorted by position
        **/
        return self::getIdsOfHighestOrLowestCardsInLocation($owner, $location, true);
    }

    function getIdsOfLowestCardsInLocation($owner, $location)
    {
        /**
            Get all highest cards in a particular location, sorted by position
        **/
        return self::getIdsOfHighestOrLowestCardsInLocation($owner, $location, false);
    }

    function getOrCountCardsInLocation($count, $owner, $location, $key = null, $type = null, $is_relic = null)
    {
        /**
            Get ($count is false) or count ($count is true) all the cards in a particular location, sorted by position. The result can be first keyed by age (for deck or hand) or color (for board) if needed
        **/

        $type_of_result = $count ? "COUNT(*)" : "*";
        $opt_order_by = $count ? "" : "ORDER BY position";
        $getFromDB = $count ? 'getUniqueValueFromDB' : 'getObjectListFromDB'; // If we count, we want to get an unique value, else, we want to get a list of cards
        $type_condition = $type === null ? "" : self::format("type = {type} AND", array('type' => $type));
        $is_relic_condition = $is_relic === null ? "" : self::format("is_relic = {is_relic} AND", array('is_relic' => ($is_relic ? 'TRUE' : 'FALSE')));

        if ($location == Locations::AVAILABLE_ACHIEVEMENTS) {
            $location = Locations::ACHIEVEMENTS;
            $owner = 0;
        }

        if ($owner == -2) { // any player
            $owner_condition = "owner != 0 AND";
        } else if ($owner == -3) { // any opponent
            $opponent_ids = self::getActiveOpponentIds(self::getActivePlayerId());
            $owner_condition = self::format("owner IN ({owners}) AND", array('owners' => join(',', $opponent_ids)));
        } else if ($owner == -4) { // any other player
            $owner_condition = self::format("owner != 0 AND owner != {player_id} AND", array('player_id' => self::getActivePlayerId()));
        } else {
            $owner_condition = self::format("owner = {owner} AND", array('owner' => $owner));
        }

        if ($key == 'age' || $key == 'faceup_age') {
            $num_min = 0;
            $num_max = 11;
        } else if ($key == 'color') {
            $num_min = 0;
            $num_max = 4;
        } else {
            return self::$getFromDB(
                self::format("
                SELECT
                    {type_of_result}
                FROM
                    card
                WHERE
                    {type_condition}
                    {is_relic_condition}
                    {owner_condition}
                    location = '{location}'
                {opt_order_by}
            ",
                    array('type_of_result' => $type_of_result, 'type_condition' => $type_condition, 'is_relic_condition' => $is_relic_condition, 'owner_condition' => $owner_condition, 'location' => $location, 'opt_order_by' => $opt_order_by)
                )
            );
        }

        $result = array();

        for ($value = $num_min; $value <= $num_max; $value++) {
            $result[$value] = self::$getFromDB(
                self::format("
                SELECT
                    {type_of_result}
                FROM
                    card
                WHERE
                    {type_condition}
                    {is_relic_condition}
                    {owner_condition}
                    location = '{location}' AND
                    {key} = {value}
                {opt_order_by}
            ",
                    array('type_of_result' => $type_of_result, 'type_condition' => $type_condition, 'is_relic_condition' => $is_relic_condition, 'owner_condition' => $owner_condition, 'location' => $location, 'key' => $key, 'value' => $value, 'opt_order_by' => $opt_order_by)
                )
            );
        }
        return $result;
    }

    function getArtifactsOnDisplay($players)
    {
        $result = array();
        foreach ($players as $player_id => $player) {
            $result[$player_id] = self::getArtifactOnDisplay($player_id);
        }
        return $result;
    }

    function getArtifactOnDisplay($player_id)
    {
        $cards = self::getCardsInLocation($player_id, Locations::DISPLAY);
        if (empty($cards)) {
            return null;
        }
        return $cards[0];
    }

    function getArtifactsInAllMuseums($players)
    {
        $result = array();
        foreach ($players as $player_id => $player) {
            $result[$player_id] = self::getCardsInLocation($player_id, Locations::MUSEUMS);
        }
        return $result;
    }

    function getBoards($player_ids)
    {
        $result = array();
        foreach ($player_ids as $player_id) {
            $result[$player_id] = self::getCardsInLocationKeyedByColor($player_id, Locations::BOARD);
        }
        return $result;
    }

    function isTopBoardCard($card)
    {

        if ($card['position'] === null || $card['location'] != 'board') {
            return false;
        }
        $number_of_cards_above = self::getUniqueValueFromDB(
            self::format("
                SELECT
                    COUNT(*)
                FROM
                    card
                WHERE
                    owner = {owner} AND
                    location = 'board' AND
                    color = {color} AND
                    position > {position}",
                array('owner' => $card['owner'], 'color' => $card['color'], 'position' => $card['position'])
            )
        );
        return $number_of_cards_above == 0;
    }

    function getCardsInLocationKeyedByAge($owner, $location, $type = null)
    {
        /**
            Get all the cards in a particular location, keyed by age, then sorted by position.
        **/
        $column = $location === 'board' ? 'faceup_age' : 'age';
        return self::getOrCountCardsInLocation( /*count=*/ false, $owner, $location, $column, $type);
    }

    function getCardsInLocationKeyedByColor($owner, $location)
    {
        /**
            Get all the cards in a particular location, keyed by color, then sorted by position.
        **/
        return self::getOrCountCardsInLocation( /*count=*/ false, $owner, $location, 'color');
    }

    function getCardsInLocation($owner, $location)
    {
        /**
            Get all the cards in a particular location, sorted by position.
        **/
        return self::getOrCountCardsInLocation( /*count=*/ false, $owner, $location);
    }

    function getCardsInHand($player_id)
    {
        return self::getCardsInLocation($player_id, 'hand');
    }

    function countCardsInHand($player_id): int
    {
        return self::countCardsInLocation($player_id, 'hand');
    }

    function countCardsInLocationKeyedByAge($owner, $location, $type = null, $is_relic = null)
    {
        /**
            Count all the cards in a particular location, keyed by age.
        **/
        $column = $location === 'board' ? 'faceup_age' : 'age';
        return self::getOrCountCardsInLocation( /*count=*/ true, $owner, $location, $column, $type, $is_relic);
    }

    function countCardsInLocationKeyedByColor($owner, $location)
    {
        /**
            Count all the cards in a particular location, keyed by color.
        **/
        return self::getOrCountCardsInLocation( /*count=*/ true, $owner, $location, 'color');
    }

    function countCardsInLocation($owner, $location, $type = null): int
    {
        /**
            Count all the cards in a particular location.
        **/
        return intval(self::getOrCountCardsInLocation( /*count=*/ true, $owner, $location, /*key=*/ null, $type));
    }

    function getTopCardOnBoard($player_id, $color)
    {
        /**
        Get the top card of specified color
        (null if the player have no card on his board)
        **/
        return self::getObjectFromDB(
            self::format("
                SELECT
                    *
                FROM
                    card
                WHERE
                    card.owner = {player_id} AND
                    card.location = 'board' AND
                    card.color = {color} AND
                    card.position = (
                        SELECT
                            MAX(position) AS position
                        FROM
                            card
                        WHERE
                            owner = {player_id} AND
                            location = 'board' AND
                            color = {color}
                    )
        ",
                array('player_id' => $player_id, 'color' => $color)
            )
        );
    }


    function getOwnersOfTopCardWithColorAndAge($color, $age)
    {
        /**
        Returns the IDs of all players with a top card of the specified color and age
        **/
        return self::getObjectListFromDB(self::format("
            SELECT
                a.owner
            FROM
                card AS a
            LEFT JOIN
                (SELECT
                    owner, MAX(position) AS position
                FROM
                    card
                WHERE
                    color = {color} AND
                    location = 'board'
                GROUP BY
                    owner) AS b ON a.owner = b.owner
            WHERE
                a.owner != 0 AND
                a.location = 'board' AND
                a.color = {color} AND
                a.age = {age} AND
                a.position = b.position
        ",
            array('color' => $color, 'age' => $age)
        ), true);
    }

    // TODO(ARTIACTS): Most call sites assume the player has at least one top card on their board. I think this is a
    // safe assumption (since you can't execute a non-demand unless you have at least 1 icon on your board) but it
    // would be better to handle the null case explicitly, especially if this is added to a demand effect sometime.
    function getTopCardsOnBoard($player_id)
    {
        /**
        Get all of the top cards on a player board, or null if the player has no cards on his board
        **/
        return self::getCollectionFromDb(
            self::format("
                SELECT
                    *
                FROM
                    card AS a
                LEFT JOIN
                    (SELECT
                        color, MAX(position) AS position
                    FROM
                        card
                    WHERE
                        owner = {player_id} AND
                        location = 'board'
                    GROUP BY
                        color) AS b ON a.color = b.color
                WHERE
                    a.owner = {player_id} AND
                    a.location = 'board' AND
                    a.position = b.position
        ",
                array('player_id' => $player_id)
            )
        );
    }

    function getIfTopCardOnBoard($id)
    {
        /**
        Returns the card if card is a top card on a board, or null if it isn't present as a top card
        **/
        return self::getObjectFromDB(
            self::format("
            SELECT
                *
            FROM
                card AS a
            LEFT JOIN
                (SELECT
                    owner, color, MAX(position) AS position
                FROM
                    card
                WHERE
                    location = 'board'
                GROUP BY
                    owner, color) AS b ON a.owner = b.owner AND a.color = b.color
            WHERE
                a.id = {id} AND
                a.location = 'board' AND
                a.position = b.position
            ",
                array('id' => $id)
            )
        );
    }

    function getBottomCardOnBoard($player_id, $color)
    {
        /**
        Get the bottom card of specified color
        (null if the player have no card on his board)
        **/
        return self::getObjectFromDB(
            self::format("
                SELECT
                    *
                FROM
                    card
                WHERE
                    card.owner = {player_id} AND
                    card.location = 'board' AND
                    card.color = {color} AND
                    card.position = 0
        ",
                array('player_id' => $player_id, 'color' => $color)
            )
        );
    }

    function getMaxAgeOnBoardTopCards($player_id)
    {
        /**
        Get the age the player is in, that is to say, the maximum age that can be found on his board top cards
        (0 if the player have no card on his board)
        **/

        // Get the max of the age matching the position defined in the sub-request
        return self::getUniqueValueFromDB(
            self::format("
            SELECT
                COALESCE(MAX(a.faceup_age), 0)
            FROM
                card AS a
            LEFT JOIN
                (SELECT
                    color, MAX(position) AS position
                FROM
                    card
                WHERE
                    owner = {player_id} AND
                    location = 'board'
                GROUP BY
                    color) AS b ON a.color = b.color
            WHERE
                a.owner = {player_id} AND
                a.location = 'board' AND
                a.position = b.position
        ",
                array('player_id' => $player_id)
            )
        );
    }

    function getMaxAgeOfTopCardOfColor($color)
    {
        /**
        Get the maximum age that can be found on top of any player's pile of a specific color
        (0 if no players have that color on their board)
        **/

        // Get the max of the age matching the position defined in the sub-request
        return self::getUniqueValueFromDB(
            self::format("
            SELECT
                COALESCE(MAX(a.faceup_age), 0)
            FROM
                card AS a
            LEFT JOIN
                (SELECT
                    owner, MAX(position) AS position
                FROM
                    card
                WHERE
                    color = {color} AND
                    location = 'board'
                GROUP BY
                    owner) AS b ON a.owner = b.owner
            WHERE
                a.owner != 0 AND
                a.location = 'board' AND
                a.color = {color} AND
                a.position = b.position
        ",
                array('color' => $color)
            )
        );
    }

    function getMinAgeOnBoardTopCardsWithoutIcon($player_id, $icon)
    {
        /**
        Get the minimum age of the top cards with a particular icon
        (0 if the player have no card on his board)
        **/


        // Get the max of the age matching the position defined in the sub-request
        return self::getUniqueValueFromDB(
            self::format("
            SELECT
                COALESCE(MIN(a.faceup_age), 0)
            FROM
                card AS a
            LEFT JOIN
                (SELECT
                    color, MAX(position) AS position
                FROM
                    card
                WHERE
                    owner = {player_id} AND
                    location = 'board'
                GROUP BY
                    color) AS b ON a.color = b.color
            WHERE
                a.owner = {player_id} AND
                a.location = 'board' AND
                a.position = b.position AND
                (
                    (a.spot_1 IS NULL OR a.spot_1 <> {icon}) AND
                    (a.spot_2 IS NULL OR a.spot_2 <> {icon}) AND
                    (a.spot_3 IS NULL OR a.spot_3 <> {icon}) AND
                    (a.spot_4 IS NULL OR a.spot_4 <> {icon}) AND
                    (a.spot_5 IS NULL OR a.spot_5 <> {icon}) AND
                    (a.spot_6 IS NULL OR a.spot_6 <> {icon})
                )
        ",
                array('player_id' => $player_id, 'icon' => $icon)
            )
        );
    }

    function getMaxAgeOnBoardTopCardsWithIcon($player_id, $icon)
    {
        /**
        Get the maximum age of the top cards with a particular icon
        (0 if the player have no card on his board)
        **/


        // Get the max of the age matching the position defined in the sub-request
        return self::getUniqueValueFromDB(
            self::format("
            SELECT
                COALESCE(MAX(a.faceup_age), 0)
            FROM
                card AS a
            LEFT JOIN
                (SELECT
                    color, MAX(position) AS position
                FROM
                    card
                WHERE
                    owner = {player_id} AND
                    location = 'board'
                GROUP BY
                    color) AS b ON a.color = b.color
            WHERE
                a.owner = {player_id} AND
                a.location = 'board' AND
                a.position = b.position AND
                (a.spot_1 = {icon} OR a.spot_2 = {icon} OR a.spot_3 = {icon} OR a.spot_4 = {icon} OR a.spot_5 = {icon} OR a.spot_6 = {icon})
        ",
                array('player_id' => $player_id, 'icon' => $icon)
            )
        );
    }

    function getMaxAgeOnBoardOfColorsWithoutIcon($player_id, $colors, $icon)
    {
        /**
        Get the maximum age of the top cards without a particular icon
        (0 if the player have no card on his board)
        **/

        // Get the max of the age matching the position defined in the sub-request
        return self::getUniqueValueFromDB(
            self::format("
            SELECT
                COALESCE(MAX(a.faceup_age), 0)
            FROM
                card AS a
            LEFT JOIN
                (SELECT
                    color, MAX(position) AS position
                FROM
                    card
                WHERE
                    owner = {player_id} AND
                    location = 'board'
                GROUP BY
                    color) AS b ON a.color = b.color
            WHERE
                a.owner = {player_id} AND
                a.location = 'board' AND
                a.position = b.position AND
                a.color IN ({colors}) AND
                (a.spot_1 IS NULL OR a.spot_1 <> {icon}) AND
                (a.spot_2 IS NULL OR a.spot_2 <> {icon}) AND
                (a.spot_3 IS NULL OR a.spot_3 <> {icon}) AND
                (a.spot_4 IS NULL OR a.spot_4 <> {icon}) AND
                (a.spot_5 IS NULL OR a.spot_5 <> {icon}) AND
                (a.spot_6 IS NULL OR a.spot_6 <> {icon})
        ",
                array('player_id' => $player_id, 'colors' => join(',', $colors), 'icon' => $icon)
            )
        );
    }

    function getMinOrMaxAgeInLocation($player_id, $location, $min_or_max)
    {
        /**
        Get the minimum or maximum age that can be found in a player particular location
        (0 if the player have no card in this location)
        **/

        return self::getUniqueValueFromDB(
            self::format("
            SELECT
                COALESCE({min_or_max}(age), 0)
            FROM
                card AS a
            WHERE
                owner = {player_id} AND
                location = '{location}'
        ",
                array('min_or_max' => $min_or_max, 'player_id' => $player_id, 'location' => $location)
            )
        );
    }

    function getCardIdsWithVisibleEchoEffects($dogma_card)
    {
        /**
        Gets the list of card IDs with visible echo effects given a specific card being executed (from top to bottom)
        **/

        // TODO(4E): This logic may need to be revised as part of https://github.com/micahstairs/bga-innovation/issues/1426.
        if (!$dogma_card['dogma_icon']) {
            return [];
        }

        $color = $dogma_card['color'];
        $pile = self::getCardsInLocationKeyedByColor($dogma_card['owner'], 'board')[$color];

        $visible_echo_effects = array();

        // Handle the case when the card being executed isn't even in the pile (e.g. Artifact on display)
        if ($dogma_card['location'] != 'board') {
            if (self::countIconsOnCard($dogma_card, Icons::ECHO_EFFECT) > 0) {
                $visible_echo_effects[] = $dogma_card['id'];
            }
        }

        for ($i = count($pile) - 1; $i >= 0; $i--) {
            $card = $pile[$i];
            $splay_direction = $card['splay_direction'];

            $has_visible_echo_efffect = false;
            if ($i == count($pile) - 1 && self::countIconsOnCard($card, Icons::ECHO_EFFECT) > 0) {
                $has_visible_echo_efffect = true;
            } else if ($splay_direction == 1) { // left
                $has_visible_echo_efffect = $card['spot_4'] == Icons::ECHO_EFFECT || $card['spot_5'] == Icons::ECHO_EFFECT;
            } else if ($splay_direction == 2) { // right
                $has_visible_echo_efffect = $card['spot_1'] == Icons::ECHO_EFFECT || $card['spot_2'] == Icons::ECHO_EFFECT;
            } else if ($splay_direction == 3) { // up
                $has_visible_echo_efffect = $card['spot_2'] == Icons::ECHO_EFFECT || $card['spot_3'] == Icons::ECHO_EFFECT || $card['spot_4'] == Icons::ECHO_EFFECT;
            } else if ($splay_direction == 4) { // aslant
                $has_visible_echo_efffect = $card['spot_1'] == Icons::ECHO_EFFECT || $card['spot_2'] == Icons::ECHO_EFFECT || $card['spot_3'] == Icons::ECHO_EFFECT || $card['spot_4'] == Icons::ECHO_EFFECT;
            }

            if ($has_visible_echo_efffect) {
                $visible_echo_effects[] = $card['id'];
            }

            // Skip covered up cards
            if ($card['location'] == 'board' && $splay_direction == 0) {
                break;
            }
        }

        return $visible_echo_effects;
    }

    function getVisibleBonusesOnPile($player_id, $color)
    {
        /**
        Gets the list of bonus icons visible on a specific pile
        **/
        $visible_bonus_icons = array();

        $board = self::getCardsInLocationKeyedByColor($player_id, 'board');
        $pile = $board[$color];
        if (count($pile) == 0) { // No card of that color
            return $visible_bonus_icons;
        }
        $top_card = $pile[count($pile) - 1];
        $visible_bonus_icons = self::getBonusIcons($top_card);

        $splay_direction = $top_card['splay_direction'];
        if ($splay_direction == 0) { // Unsplayed
            return $visible_bonus_icons;
        }
        // Since the stack is not unsplayed, it has at least two cards
        for ($i = 0; $i < count($pile) - 1; $i++) {
            $card = $pile[$i];
            if ($splay_direction == 1) { // left
                if ($card['spot_4'] >= 101 && $card['spot_4'] <= 112) {
                    $visible_bonus_icons[] = $card['spot_4'] - 100;
                }
                if ($card['spot_5'] >= 101 && $card['spot_5'] <= 112) {
                    $visible_bonus_icons[] = $card['spot_5'] - 100;
                }
            } elseif ($splay_direction == 2) { // right
                if ($card['spot_1'] >= 101 && $card['spot_1'] <= 112) {
                    $visible_bonus_icons[] = $card['spot_1'] - 100;
                }
                if ($card['spot_2'] >= 101 && $card['spot_2'] <= 112) {
                    $visible_bonus_icons[] = $card['spot_2'] - 100;
                }
            } elseif ($splay_direction == 3) { // up
                if ($card['spot_2'] >= 101 && $card['spot_2'] <= 112) {
                    $visible_bonus_icons[] = $card['spot_2'] - 100;
                }
                if ($card['spot_3'] >= 101 && $card['spot_3'] <= 112) {
                    $visible_bonus_icons[] = $card['spot_3'] - 100;
                }
                if ($card['spot_4'] >= 101 && $card['spot_4'] <= 112) {
                    $visible_bonus_icons[] = $card['spot_4'] - 100;
                }
            } elseif ($splay_direction == 4) { // aslant
                if ($card['spot_1'] >= 101 && $card['spot_1'] <= 112) {
                    $visible_bonus_icons[] = $card['spot_1'] - 100;
                }
                if ($card['spot_2'] >= 101 && $card['spot_2'] <= 112) {
                    $visible_bonus_icons[] = $card['spot_2'] - 100;
                }
                if ($card['spot_3'] >= 101 && $card['spot_3'] <= 112) {
                    $visible_bonus_icons[] = $card['spot_3'] - 100;
                }
                if ($card['spot_4'] >= 101 && $card['spot_4'] <= 112) {
                    $visible_bonus_icons[] = $card['spot_4'] - 100;
                }
            }
        }
        return $visible_bonus_icons;
    }


    function getVisibleBonusesOnBoard($player_id)
    {
        $visible_bonus_icons = array();

        foreach (Colors::ALL as $color) {
            $visible_bonus_icons = array_merge($visible_bonus_icons, self::getVisibleBonusesOnPile($player_id, $color));
        }
        return $visible_bonus_icons;
    }

    function getBonusIcons($card)
    {
        $bonus_icons = array();
        if ($card !== null) {
            if ($card['spot_1'] >= 101 && $card['spot_1'] <= 112) {
                $bonus_icons[] = $card['spot_1'] - 100;
            }
            if ($card['spot_2'] >= 101 && $card['spot_2'] <= 112) {
                $bonus_icons[] = $card['spot_2'] - 100;
            }
            if ($card['spot_3'] >= 101 && $card['spot_3'] <= 112) {
                $bonus_icons[] = $card['spot_3'] - 100;
            }
            if ($card['spot_4'] >= 101 && $card['spot_4'] <= 112) {
                $bonus_icons[] = $card['spot_4'] - 100;
            }
            if ($card['spot_5'] >= 101 && $card['spot_5'] <= 112) {
                $bonus_icons[] = $card['spot_5'] - 100;
            }
            if ($card['spot_6'] >= 101 && $card['spot_6'] <= 112) {
                $bonus_icons[] = $card['spot_6'] - 100;
            }
        }
        return $bonus_icons;
    }

    /** Information about card resources **/
    function hasRessource($card, $icon)
    {
        return $card !== null && ($card['spot_1'] == $icon || $card['spot_2'] == $icon || $card['spot_3'] == $icon || $card['spot_4'] == $icon || $card['spot_5'] == $icon || $card['spot_6'] == $icon);
    }

    /* Count the number of a particular icon on the specified card */
    function countIconsOnCard($card, $icon): int
    {
        $icon_count = 0;
        if ($card['spot_1'] !== null && $card['spot_1'] == $icon) {
            $icon_count++;
        }
        if ($card['spot_2'] !== null && $card['spot_2'] == $icon) {
            $icon_count++;
        }
        if ($card['spot_3'] !== null && $card['spot_3'] == $icon) {
            $icon_count++;
        }
        if ($card['spot_4'] !== null && $card['spot_4'] == $icon) {
            $icon_count++;
        }
        if ($card['spot_5'] !== null && $card['spot_5'] == $icon) {
            $icon_count++;
        }
        if ($card['spot_6'] !== null && $card['spot_6'] == $icon) {
            $icon_count++;
        }
        return $icon_count;
    }

    /** Counts the number of visible cards based on the splay **/
    function countVisibleCards($player_id, $color)
    {
        $board = self::getCardsInLocationKeyedByColor($player_id, 'board');
        $pile = $board[$color];
        $pile_size = count($pile);
        if ($pile_size == 0) { // No card of that color
            return 0;
        }
        $top_card = $pile[$pile_size - 1];
        if ($top_card['splay_direction'] == 0) { // Unsplayed
            return 1;
        }
        return $pile_size; // All other splays result in the current stack size
    }

    /** Get and update game situation **/
    function incrementBGAScore($player_id, $is_special_achievement)
    { // Increment the BGA score of the team (single player or to player in 2 vs 2 game) (number of achievements) then check if he got enough to win
        $player = self::getObjectFromDB(
            self::format(
                "SELECT
                player_score, player_team
            FROM
                player
            WHERE
                player_id={player_id}"
                ,
                array('player_id' => $player_id)
            )
        );

        $player['player_score']++;

        self::DbQuery(
            self::format(
                "UPDATE
                player
            SET
                player_score = {player_score}
            WHERE
                player_team={player_team}"
                ,
                $player
            )
        );

        // Stats
        self::incStat(1, 'achievements_number', $player_id);
        if ($is_special_achievement) {
            self::incStat(1, 'special_achievements_number', $player_id);
        }

        // Was it the last achievement needed for the player for winning?      
        if ($player['player_score'] >= $this->innovationGameState->get('number_of_achievements_needed_to_win')) {
            $this->innovationGameState->set('game_end_type', 0);
            self::trace('EOG bubbled from self::incrementBGAScore');
            throw new EndOfGame();
        }
    }

    /** Get and update game situation **/
    function decrementBGAScore($player_id)
    {
        $player = self::getObjectFromDB(
            self::format(
                "SELECT
                player_score, player_team
            FROM
                player
            WHERE
                player_id={player_id}"
                ,
                array('player_id' => $player_id)
            )
        );

        $player['player_score']--;

        self::DbQuery(
            self::format(
                "UPDATE
                player
            SET
                player_score = {player_score}
            WHERE
                player_team={player_team}"
                ,
                $player
            )
        );

        // Stats
        self::incStat(-1, 'achievements_number', $player_id);
    }

    function getPlayerScore($player_id)
    { // Player Innovation score is different from the BGA score (number of achievements)
        return self::getUniqueValueFromDB(
            self::format("
        SELECT
            player_innovation_score
        FROM
            player
        WHERE
            player_id = {player_id}
        ",
                array('player_id' => $player_id)
            )
        );
    }

    function getPlayerNumberOfAchievements($player_id)
    { // Player Innovation score is different from the BGA score (number of achievements)
        return self::getUniqueValueFromDB(
            self::format("
        SELECT
            player_score
        FROM
            player
        WHERE
            player_id = {player_id}
        ",
                array('player_id' => $player_id)
            )
        );
    }

    function updatePlayerScore($player_id)
    {
        $score = 0;
        foreach (self::getCardsInLocation($player_id, 'score') as $card) {
            $score += $card['age'];
        }
        $score += self::countBonusPoints($player_id);
        self::DBQuery(
            self::format("
            UPDATE
                player
            SET
                player_innovation_score = {score}
            WHERE
                player_id = {player_id}
        ",
                array('player_id' => $player_id, 'score' => $score)
            )
        );
        self::setStat($score, 'score', $player_id);
        return $score;
    }

    function countBonusPoints($player_id)
    {
        $bonuses = self::getVisibleBonusesOnBoard($player_id);
        if (count($bonuses) > 0) {
            return max($bonuses) + count($bonuses) - 1;
        }
        return 0;
    }

    // Returns the icon count for a particular color on a player's board (also works for hexagon icons if icon=0)
    function countVisibleIconsInPile($player_id, $icon, $color)
    {
        $board = self::getCardsInLocationKeyedByColor($player_id, 'board');
        $pile = $board[$color];
        $pile_size = count($pile);

        // No card of the specified color
        if ($pile_size == 0) {
            return 0;
        }

        // Always count the icons on the top card
        $top_card = $pile[$pile_size - 1];
        $count = self::countIconsOnCard($top_card, $icon);

        // Determine splay direction
        $unsplayed = $top_card['splay_direction'] == 0;
        $splayed_left = $top_card['splay_direction'] == 1;
        $splayed_right = $top_card['splay_direction'] == 2;
        $splayed_up = $top_card['splay_direction'] == 3;
        $splayed_aslant = $top_card['splay_direction'] == 4;

        // If unsplayed, only return the count of the icons on the top card
        if ($unsplayed == 1) {
            return $count;
        }

        // Add icons of the other cards.
        for ($i = 0; $i < $pile_size - 1; $i++) {
            $card = $pile[$i];

            if ($splayed_right || $splayed_aslant) {
                if ($card['spot_1'] !== null && $card['spot_1'] == $icon) {
                    $count += 1;
                }
            }
            if ($splayed_right || $splayed_up || $splayed_aslant) {
                if ($card['spot_2'] !== null && $card['spot_2'] == $icon) {
                    $count += 1;
                }
            }
            if ($splayed_up || $splayed_aslant) {
                if ($card['spot_3'] !== null && $card['spot_3'] == $icon) {
                    $count += 1;
                }
            }
            if ($splayed_left || $splayed_up || $splayed_aslant) {
                if ($card['spot_4'] !== null && $card['spot_4'] == $icon) {
                    $count += 1;
                }
            }
            if ($splayed_left) {
                if ($card['spot_5'] !== null && $card['spot_5'] == $icon) {
                    $count += 1;
                }
            }
        }
        return $count;
    }

    function getPlayerSingleRessourceCount($player_id, $icon)
    {
        return self::getUniqueValueFromDB(
            self::format("
            SELECT
                player_icon_count_{icon}
            FROM
                player
            WHERE
                player_id = {player_id}
        ",
                array('player_id' => $player_id, 'icon' => $icon)
            )
        );
    }

    function getPlayerResourceCounts($player_id)
    {
        $table = self::getNonEmptyObjectFromDB(
            self::format("
        SELECT
            player_icon_count_1, player_icon_count_2, player_icon_count_3, player_icon_count_4, player_icon_count_5, player_icon_count_6, player_icon_count_7
        FROM
            player
        WHERE
            player_id = {player_id}
        ",
                array('player_id' => $player_id)
            )
        );

        // Convert to a numeric associative array
        $result = array();
        for ($icon = 1; $icon <= 7; $icon++) {
            $result[$icon] = $table["player_icon_count_" . $icon];
        }
        return $result;
    }

    function getPlayerWishForSplay($player_id)
    {
        return self::getUniqueValueFromDB(
            self::format("
        SELECT
            pile_display_mode
        FROM
            player
        WHERE
            player_id = {player_id}
        ",
                array('player_id' => $player_id)
            )
        ) == 1;
    }

    function getPlayerWishForViewFull($player_id)
    {
        return self::getUniqueValueFromDB(
            self::format("
        SELECT
            pile_view_full
        FROM
            player
        WHERE
            player_id = {player_id}
        ",
                array('player_id' => $player_id)
            )
        ) == 1;
    }

    function setPlayerWishForSplay($player_id, $pile_display_mode)
    {
        self::DbQuery(
            self::format("
        UPDATE
            player
        SET
            pile_display_mode = {pile_display_mode}
        WHERE
            player_id = {player_id}
        ",
                array('player_id' => $player_id, 'pile_display_mode' => $pile_display_mode ? "TRUE" : "FALSE")
            )
        );
    }

    function setPlayerWishForViewFull($player_id, $pile_view_full)
    {
        self::DbQuery(
            self::format("
        UPDATE
            player
        SET
            pile_view_full = {pile_view_full}
        WHERE
            player_id = {player_id}
        ",
                array('player_id' => $player_id, 'pile_view_full' => $pile_view_full ? "TRUE" : "FALSE")
            )
        );
    }

    function getPlayerWillDrawUnseenCardNext($player_id)
    {
        return self::getUniqueValueFromDB(
            self::format("
        SELECT
            will_draw_unseen_card_next
        FROM
            player
        WHERE
            player_id = {player_id}
        ",
                array('player_id' => $player_id)
            )
        ) == 1;
    }

    function setPlayerWillDrawUnseenCardNext($player_id, $will_draw_unseen_card_next)
    {
        self::DbQuery(
            self::format("
        UPDATE
            player
        SET
            will_draw_unseen_card_next = {will_draw_unseen_card_next}
        WHERE
            player_id = {player_id}
        ",
                array('player_id' => $player_id, 'will_draw_unseen_card_next' => $will_draw_unseen_card_next ? "TRUE" : "FALSE")
            )
        );
    }

    function resetWillDrawUnseenCardNext()
    {
        self::DbQuery("UPDATE player SET will_draw_unseen_card_next = TRUE");
    }

    function updatePlayerRessourceCounts($player_id)
    {
        self::DbQuery("
            INSERT INTO
                base (icon)
            VALUES
                (1), (2), (3), (4), (5), (6), (7)
        ");

        self::DbQuery(
            self::format("
            INSERT INTO card_with_top_card_indication (id, type, age, color, spot_1, spot_2, spot_3, spot_4, spot_5, spot_6, dogma_icon, owner, location, position, splay_direction, selected, is_top_card)
                SELECT
                a.id, a.type, a.age, a.color, a.spot_1, a.spot_2, a.spot_3, a.spot_4, a.spot_5, a.spot_6, a.dogma_icon, a.owner, a.location, a.position, a.splay_direction, a.selected,
                    (a.position = b.position_of_top_card) AS is_top_card
                FROM
                    card AS a
                    LEFT JOIN (
                        SELECT 
                            color, MAX(position) AS position_of_top_card
                        FROM
                            card
                        WHERE
                            owner = {player_id} AND
                            location = 'board'
                        GROUP BY
                            color
                   ) AS b ON a.color = b.color
                WHERE
                    a.owner = {player_id} AND
                    a.location = 'board'
        ",
                array('player_id' => $player_id)
            )
        );

        self::DbQuery("
            INSERT INTO icon_count
                SELECT
                    a.icon,
                    COALESCE(s1.count, 0) + COALESCE(s2.count, 0) + COALESCE(s3.count, 0) + COALESCE(s4.count, 0) + COALESCE(s5.count, 0) + COALESCE(s6.count, 0) AS count
                FROM
                    base AS a
                    LEFT JOIN (SELECT spot_1, COUNT(spot_1) AS count FROM card_with_top_card_indication WHERE is_top_card IS TRUE OR splay_direction = 2 OR splay_direction = 4 GROUP BY spot_1) AS s1 ON a.icon = s1.spot_1
                    LEFT JOIN (SELECT spot_2, COUNT(spot_2) AS count FROM card_with_top_card_indication WHERE is_top_card IS TRUE OR splay_direction = 2 OR splay_direction = 3 OR splay_direction = 4 GROUP BY spot_2) AS s2 ON a.icon = s2.spot_2
                    LEFT JOIN (SELECT spot_3, COUNT(spot_3) AS count FROM card_with_top_card_indication WHERE is_top_card IS TRUE OR splay_direction = 3 OR splay_direction = 4 GROUP BY spot_3) AS s3 ON a.icon = s3.spot_3
                    LEFT JOIN (SELECT spot_4, COUNT(spot_4) AS count FROM card_with_top_card_indication WHERE is_top_card IS TRUE OR splay_direction = 1 OR splay_direction = 3 OR splay_direction = 4 GROUP BY spot_4) AS s4 ON a.icon = s4.spot_4
                    LEFT JOIN (SELECT spot_5, COUNT(spot_5) AS count FROM card_with_top_card_indication WHERE is_top_card IS TRUE OR splay_direction = 1 OR splay_direction = 3 GROUP BY spot_5) AS s5 ON a.icon = s5.spot_5
                    LEFT JOIN (SELECT spot_6, COUNT(spot_6) AS count FROM card_with_top_card_indication WHERE is_top_card IS TRUE GROUP BY spot_6) AS s6 ON a.icon = s6.spot_6
        ");

        self::DbQuery(
            self::format("
            UPDATE
                player AS a
                LEFT JOIN icon_count AS i1 ON TRUE
                LEFT JOIN icon_count AS i2 ON TRUE
                LEFT JOIN icon_count AS i3 ON TRUE
                LEFT JOIN icon_count AS i4 ON TRUE
                LEFT JOIN icon_count AS i5 ON TRUE
                LEFT JOIN icon_count AS i6 ON TRUE
                LEFT JOIN icon_count AS i7 ON TRUE
            SET
                a.player_icon_count_1 = i1.count,
                a.player_icon_count_2 = i2.count,
                a.player_icon_count_3 = i3.count,
                a.player_icon_count_4 = i4.count,
                a.player_icon_count_5 = i5.count,
                a.player_icon_count_6 = i6.count,
                a.player_icon_count_7 = i7.count
            WHERE
                a.player_id = {player_id} AND
                i1.icon = 1 AND
                i2.icon = 2 AND
                i3.icon = 3 AND
                i4.icon = 4 AND
                i5.icon = 5 AND
                i6.icon = 6 AND
                i7.icon = 7
        ",
                array('player_id' => $player_id)
            )
        );

        // Delete all values of the auxiliary tables
        self::DbQuery("DELETE FROM card_with_top_card_indication");
        self::DbQuery("DELETE FROM base");
        self::DbQuery("DELETE FROM icon_count");

        return self::getPlayerResourceCounts($player_id);
    }

    function promoteScoreToBGAScore()
    {
        // Called if the game ends by drawing. The innovation score is the main value to check to determine the winner and the number of achievements is used as a tie-breaker.

        // If team game, add the score of the teammate first
        if (self::decodeGameType($this->innovationGameState->get('game_type')) == 'team') {
            self::DbQuery("
            UPDATE
                player AS a
                LEFT JOIN (
                    SELECT
                        player_team, SUM(player_innovation_score) AS team_score
                    FROM
                        player
                    GROUP BY
                        player_team
                
                ) AS b ON a.player_team = b.player_team
            SET
                a.player_innovation_score = b.team_score
            ");
        }

        self::DbQuery("
        UPDATE
            player
        SET
            player_score_aux = player_score,
            player_score = player_innovation_score
        ");
    }

    function binarizeBGAScore()
    {
        // Called if the game ends by dogma. The innovation score is 1 for winners, 0 for losers. There is no tie-breaker.
        self::DbQuery(
            self::format("
        UPDATE
            player
        SET
            player_score_aux = 0,
            player_score = (CASE WHEN player_id = {winner} THEN 1 ELSE 0 END)
        ",
                array('winner' => $this->innovationGameState->get('winner_by_dogma'))
            )
        );

        if (self::decodeGameType($this->innovationGameState->get('game_type')) == 'team') {
            // Add the score of the teammate 0 + 0 for losers, 0 + 1 for winners
            self::DbQuery("
            UPDATE
                player AS a
                LEFT JOIN (
                    SELECT
                        player_team, SUM(player_score) AS team_score
                    FROM
                        player
                    GROUP BY
                        player_team
                
                ) AS b ON a.player_team = b.player_team
            SET
                a.player_score = b.team_score
            ");
        }
    }

    /** Information about players **/
    function getPlayerNameFromId($player_id)
    {
        // TODO(LATER): Identify and fix the nested execution bug which makes this hack necessary.
        if ($player_id == -1 || $player_id == null) {
            return "unknown";
        }
        $players = self::loadPlayersBasicInfos();
        return $players[$player_id]['player_name'];
    }

    function getPlayerColorFromId($player_id)
    {
        // TODO(LATER): Identify and fix the nested execution bug which makes this hack necessary.
        if ($player_id == -1 || $player_id == null) {
            return "unknown";
        }
        $players = self::loadPlayersBasicInfos();
        return $players[$player_id]['player_color'];
    }

    function getPlayerTeammate($player_id)
    {
        /** Return the teammate in a team game or null if there is None **/
        return self::getUniqueValueFromDB(
            self::format("
            SELECT
                player_id
            FROM
                player
            WHERE
                player_id <> {player_id} AND
                player_team = (
                    SELECT
                        player_team
                    FROM
                        player
                    WHERE
                        player_id = {player_id}
                )
        ",
                array('player_id' => $player_id)
            )
        );
    }

    /** Information when in dogma **/
    function qualifyEffect($current_effect_type, $current_effect_number, $card)
    {
        $unique_non_demand_effect = self::getNonDemandEffect($card['id'], 2) === null;

        return $current_effect_type == 0 ? clienttranslate('I demand effect') :
            ($current_effect_type == 2 ? clienttranslate('I compel effect') :
                ($current_effect_type == 3 ? clienttranslate('echo effect') :
                    ($unique_non_demand_effect ? clienttranslate('non-demand effect') :
                        ($current_effect_number == 1 ? clienttranslate('1<sup>st</sup> non-demand effect') :
                            ($current_effect_number == 2 ? clienttranslate('2<sup>nd</sup> non-demand effect') : clienttranslate('3<sup>rd</sup> non-demand effect'))))));
    }

    function getFirstPlayerUnderEffect($dogma_effect_type, $launcher_id)
    {
        return self::getNextPlayerUnderEffect($dogma_effect_type, -1, $launcher_id);
    }

    /* Returns the ID of the next player under effect, or null */
    function getNextPlayerUnderEffect($dogma_effect_type, $player_id, $launcher_id)
    {
        $current_nested_state = self::getCurrentNestedCardState();
        $is_being_super_executed = $current_nested_state['super_execute'];

        // I demand
        $launcher_icon_count = self::getUniqueValueFromDB(
            self::format("
            SELECT
                featured_icon_count
            FROM
                player
            WHERE
                player_id = {launcher_id}
        ",
                array('launcher_id' => $launcher_id)
            )
        );
        // I demand
        if ($dogma_effect_type == 0) {
            if ($is_being_super_executed) {
                $player_query = self::format(
                    "player_id != {launcher_id} AND player_team <> (SELECT player_team FROM player WHERE player_id = {launcher_id}) AND distance_rule_demand_state != 3",
                    array('launcher_id' => $launcher_id)
                );
            } else {
                $player_query = self::format(
                    "featured_icon_count < {launcher_icon_count} AND player_id != {launcher_id} AND player_team <> (SELECT player_team FROM player WHERE player_id = {launcher_id}) AND distance_rule_demand_state != 3",
                    array('launcher_id' => $launcher_id, 'launcher_icon_count' => $launcher_icon_count)
                );
            }
            // I compel
        } else if ($dogma_effect_type == 2) {
            if ($is_being_super_executed) {
                $player_query = "FALSE";
            } else {
                $player_query = self::format(
                    "featured_icon_count >= {launcher_icon_count} AND player_id != {launcher_id} AND player_team <> (SELECT player_team FROM player WHERE player_id = {launcher_id}) AND distance_rule_demand_state != 3",
                    array('launcher_id' => $launcher_id, 'launcher_icon_count' => $launcher_icon_count)
                );
            }
            // Non-demand or echo effect
        } else {
            $player_query = self::format(
                "featured_icon_count >= {launcher_icon_count} AND distance_rule_share_state != 2",
                array('launcher_icon_count' => $launcher_icon_count)
            );
        }

        // NOTE: The constant '100' is mostly arbitrary. It just needed to be at least as large as the maximum number of players in the game.
        self::DbQuery(self::format("
            UPDATE
                player
            SET
                turn_order_ending_with_launcher = (CASE WHEN player_index <= {launcher_player_index} THEN player_index + 100 ELSE player_index END)
        ", array('launcher_player_index' => self::playerIdToPlayerIndex($launcher_id))));
        $current_turn = $player_id == -1 ? -1 : self::getUniqueValueFromDB(self::format("SELECT turn_order_ending_with_launcher FROM player WHERE player_id = {player_id}", array('player_id' => $player_id)));
        return self::getUniqueValueFromDB(
            self::format("
            SELECT
                player_id
            FROM
                player
            WHERE
                turn_order_ending_with_launcher = (
                    SELECT
                        MIN(turn_order_ending_with_launcher)
                    FROM
                        player
                    WHERE
                        player_eliminated = 0
                        AND turn_order_ending_with_launcher > {current_turn}
                        AND {player_query}
                )
        ",
                array('player_query' => $player_query, 'current_turn' => $current_turn)
            )
        );
    }

    function renderColorCards($color)
    {
        switch ($color) {
            case Colors::BLUE:
                return clienttranslate('blue cards');
            case Colors::RED:
                return clienttranslate('red cards');
            case Colors::GREEN:
                return clienttranslate('green cards');
            case Colors::YELLOW:
                return clienttranslate('yellow cards');
            case Colors::PURPLE:
                return clienttranslate('purple cards');
        }
    }

    function renderNumber($number)
    {
        switch ($number) {
            case 0:
                return clienttranslate('zero');
            case 1:
                return clienttranslate('one');
            case 2:
                return clienttranslate('two');
            case 3:
                return clienttranslate('three');
            case 4:
                return clienttranslate('four');
            case 5:
                return clienttranslate('five');
            case 6:
                return clienttranslate('six');
            case 7:
                return clienttranslate('seven');
            case 8:
                return clienttranslate('eight');
            case 9:
                return clienttranslate('nine');
            case 10:
                return clienttranslate('ten');
            default:
                return $number;
        }
    }

    function getColoredText($text, $player_id)
    {
        $color = self::getPlayerColorFromId($player_id);
        return "<span style='font-weight: bold; color:#" . $color . ";'>" . $text . "</span>";
    }

    function renderPlayerName($player_id)
    {
        return self::getColoredText(self::getPlayerNameFromId($player_id), $player_id);
    }

    /** Execution of actions authorized by server **/

    function executeDrawAndScore($player_id, $age_min = null)
    {
        return self::executeDraw($player_id, $age_min, 'score');
    }

    function executeDrawAndReveal($player_id, $age_min = null, $type = null)
    {
        return self::executeDraw($player_id, $age_min, 'revealed', /*bottom_to=*/ false, $type);
    }

    function executeDrawAndMeld($player_id, $age_min = null, $type = null)
    {
        return self::executeDraw($player_id, $age_min, 'board', /*bottom_to=*/ false, $type, /*bottom_from=*/ false, /*meld_keyword=*/ true);
    }

    function executeDrawAndTuck($player_id, $age_min = null, $type = null)
    {
        return self::executeDraw($player_id, $age_min, 'board', /*bottom_to=*/ true, $type);
    }

    /* Execute a draw. If $age_min is null, draw in the deck according to the board of the player, else, draw a card of the specified value or more, according to the rules */
    function executeDraw($player_id, $age_min = null, $location_to = 'hand', $bottom_to = false, $type = null, $bottom_from = false, $meld_keyword = false)
    {
        $age_to_draw = self::getAgeToDrawIn($player_id, $age_min);

        $max_age = self::getMaxAge();
        if ($age_to_draw > $max_age) {
            if ($this->innovationGameState->get('debug_mode') == 2) {
                $age_to_draw = self::getAgeToDrawIn($player_id, 0);
                if ($age_to_draw > $max_age) {
                    error_log("* All base decks are empty, so was unable to draw and avoid ending the game...");
                    $this->innovationGameState->set('game_end_type', 1);
                    $this->innovationGameState->set('player_who_could_not_draw', $player_id);
                    throw new EndOfGame();
                } else {
                    error_log("* Avoided ending the game by altering the age of drawn card");
                }
            } else {
                $this->innovationGameState->set('game_end_type', 1);
                $this->innovationGameState->set('player_who_could_not_draw', $player_id);
                self::trace('EOG bubbled from self::executeDraw (age higher than highest deck age');
                throw new EndOfGame();
            }
        }

        // "If an expansion’s supply pile has no cards in it, and you try to draw from it (after skipping empty ages),
        // draw a base card of that value instead."
        if ($type != null && self::countCardsInLocationKeyedByAge(0, 'deck', /*type=*/ $type)[$age_to_draw] == 0) {
            $type = null;
        }

        // If the type isn't specified, then we are either drawing a Base, Echoes, or Unseen card.
        if ($type === null) {
            $type = self::getCardTypeToDraw($age_to_draw, $player_id);
        }

        if ($bottom_from) {
            $card = self::getDeckBottomCard($age_to_draw, $type);
        } else {
            $card = self::getDeckTopCard($age_to_draw, $type);
        }

        try {
            $card = self::transferCardFromTo($card, $player_id, $location_to, [
                'bottom_to'     => $bottom_to,
                'score_keyword' => $location_to === 'score',
                'meld_keyword'  => $meld_keyword,
                'bottom_from'   => $bottom_from,
            ]);
        } catch (EndOfGame $e) {
            self::trace('EOG bubbled from self::executeDraw');
            throw $e; // Re-throw exception to higher level
        }

        if ($type == 2) {
            self::incStat(1, 'city_cards_drawn_number', $player_id);
        }

        return $card;
    }

    function getCardTypeToDraw($age_to_draw, $player_id)
    {
        $card_type = CardTypes::BASE;

        if ($this->innovationGameState->echoesExpansionEnabled()) {
            if ($this->innovationGameState->usingFourthEditionRules()) {
                // Draw an Echoes card if you have a unique highest top card
                $maxValue = null;
                $uniqueHighestValue = false;
                foreach (self::getTopCardsOnBoard($player_id) as $card) {
                    $value = $card['faceup_age'];
                    if ($maxValue === null || $value > $maxValue) {
                        $maxValue = $value;
                        $uniqueHighestValue = true;
                    } else if ($maxValue != null && $value === $maxValue) {
                        $uniqueHighestValue = false;
                    }
                }
                if ($uniqueHighestValue) {
                    $card_type = CardTypes::ECHOES;
                }
            } else {
                // Draw an Echoes card if none is currently in hand and at least one other card is in hand (drawn and revealed counts as being in hand)
                if (
                    (self::countCardsInLocation($player_id, 'hand') + self::countCardsInLocation($player_id, 'revealed')) > 0 &&
                    self::countCardsInLocation($player_id, 'hand', CardTypes::ECHOES) == 0 &&
                    self::countCardsInLocation($player_id, 'revealed', CardTypes::ECHOES) == 0
                ) {
                    $card_type = CardTypes::ECHOES;
                }
            }
        }

        if ($card_type === CardTypes::BASE && self::getPlayerWillDrawUnseenCardNext($player_id)) {
            $card_type = CardTypes::UNSEEN;
        }

        // If an expansion’s supply pile has no cards in it, and you try to draw from it (after skipping empty ages),
        // draw a base card of that value instead.
        if (self::getDeckTopCard($age_to_draw, $card_type) === null) {
            $card_type = CardTypes::BASE;
        }
        return $card_type;
    }

    function getMaxAge()
    {
        return $this->innovationGameState->usingFourthEditionRules() ? 11 : 10;
    }

    function junkBaseDeck($age): bool
    {
        return self::junkDeck($age, CardTypes::BASE);
    }

    function junkDeck($age, $type): bool
    {
        if ($age == 0 || $age >= 12) {
            // TODO(FIGURES): Handle junking the age 0 deck
            return false;
        }
        $cards = self::getCardsInLocationKeyedByAge( /*owner=*/ 0, 'deck', $type)[$age];
        if (empty($cards)) {
            self::notifyGeneralInfo(clienttranslate('No cards were left in the ${age} deck to junk.'), ['age' => self::getAgeSquareWithType($age, $type)]);
            return false;
        }
        self::bulkTransferCards($cards, 0, Locations::JUNK);
        self::notifyGeneralInfo(
            clienttranslate('The ${age} deck, which contained ${n} card(s), was junked.'),
            ['age' => self::getAgeSquareWithType($age, $type), 'n' => self::renderNumber(count($cards))]
        );
        return true;
    }

    function expandInteractionOptions(array $options, int $player_id, bool $is_refreshing_options): ?array
    {
        if (empty($options)) {
            return null;
        }

        if (array_key_exists('n', $options) && $options['n'] === 'all') {
            $options['n'] = 999;
        }
        if (array_key_exists('n_max', $options) && $options['n_max'] === 'all') {
            $options['n_max'] = 999;
        }
        if (!array_key_exists('can_pass', $options) && !$is_refreshing_options) {
            $options['can_pass'] = false;
        }
        if (array_key_exists('location', $options)) {
            $options['location_from'] = $options['location'];
            $options['location_to'] = $options['location'];
            unset($options['location']);
        }
        if (array_key_exists('choose_from', $options)) {
            $options['location_from'] = $options['choose_from'];
            $options['location_to'] = 'none';
            unset($options['choose_from']);
        }
        if (array_key_exists('achieve_if_eligible', $options)) {
            $options['achieve_keyword'] = true;
            $options['require_achievement_eligibility'] = true;
            unset($options['achieve_if_eligible']);
        }
        if (array_key_exists('meld_keyword', $options)) {
            $options['location_to'] = 'board';
        }
        if (array_key_exists('score_keyword', $options)) {
            $options['location_to'] = 'score';
        }
        if (array_key_exists('tuck_keyword', $options)) {
            $options['location_to'] = 'board';
            $options['bottom_to'] = true;
            unset($options['tuck_keyword']);
        }
        if (array_key_exists('achieve_keyword', $options)) {
            $options['location_to'] = 'achievements';
            if (!array_key_exists('owner_from', $options) && !array_key_exists('location_from', $options)) {
                $options['owner_from'] = 0;
                $options['location_from'] = 'achievements';
            }
        }
        if (array_key_exists('safeguard_keyword', $options)) {
            $options['location_to'] = 'safe';
            if (!array_key_exists('owner_from', $options) && !array_key_exists('location_from', $options)) {
                $options['owner_from'] = 0;
                $options['location_from'] = 'achievements';
            }
        }
        if (array_key_exists('foreshadow_keyword', $options)) {
            $options['location_to'] = 'forecast';
        }
        if (array_key_exists('return_keyword', $options) && !array_key_exists('location_to', $options)) {
            $options['location_to'] = 'deck';
        }
        if (array_key_exists('topdeck_keyword', $options)) {
            $options['location_to'] = 'deck';
            $options['bottom_to'] = false;
            unset($options['topdeck_keyword']);
        }
        if (array_key_exists('junk_keyword', $options)) {
            $options['location_to'] = 'junk';
            unset($options['junk_keyword']);
        }
        if (array_key_exists('reveal_keyword', $options)) {
            $options['location_to'] = 'revealed';
            unset($options['reveal_keyword']);
        }
        if (array_key_exists('location_from', $options) && $options['location_from'] == Locations::AVAILABLE_ACHIEVEMENTS) {
            $options['location_from'] = Locations::ACHIEVEMENTS;
            $options['owner_from'] = 0;
        }
        if (array_key_exists('location_to', $options) && $options['location_to'] == Locations::AVAILABLE_ACHIEVEMENTS) {
            $options['location_to'] = Locations::ACHIEVEMENTS;
            $options['owner_to'] = 0;
        }
        if (!array_key_exists('n', $options) && !array_key_exists('n_min', $options) && !array_key_exists('n_max', $options) && !$is_refreshing_options) {
            $options['n'] = 1;
        }
        if (!array_key_exists('player_id', $options) && !$is_refreshing_options) {
            $options['player_id'] = $player_id;
        }
        if (array_key_exists('location_from', $options) && ($options['location_from'] == 'deck' || $options['location_from'] == 'junk')) {
            $options['owner_from'] = 0;
        }
        if (!array_key_exists('owner_from', $options) && !$is_refreshing_options) {
            $options['owner_from'] = $player_id;
        }
        if (array_key_exists('location_to', $options) && ($options['location_to'] == 'deck' || $options['location_to'] == 'junk')) {
            $options['owner_to'] = 0;
        }
        if (!array_key_exists('owner_to', $options) && !$is_refreshing_options) {
            $options['owner_to'] = $player_id;
        }
        if (array_key_exists('choices', $options)) {
            $options['choose_from_list'] = true;
        }
        if (array_key_exists('enable_autoselection', $options)) {
            if ($options['enable_autoselection']) {
                $options['enable_autoselection'] = 2; // Forced on (more aggressive than the default)
            } else {
                $options['enable_autoselection'] = 0; // No autoselection
            }
        }
        return $options;
    }

    function setSelectionRange(array $options, $is_refreshing_options = false)
    {
        self::deselectAllCards();

        $rewritten_options = array();
        foreach ($options as $key => $value) {
            switch ($key) {
                case 'player_id':
                    $player_id = $value;
                    break;
                case 'owner_from':
                    if ($value === 'any player') {
                        $value = -2;
                    } else if ($value === 'any opponent') {
                        $value = -3;
                    } else if ($value === 'any other player') {
                        $value = -4;
                    }
                    $rewritten_options['owner_from'] = $value;
                    break;
                case 'n':
                    $rewritten_options['n_min'] = $value;
                    $rewritten_options['n_max'] = $value;
                    break;
                case 'age':
                    // TODO(LATER): Stop overloading the 'age' option and add a separate option for when we are passing an array.
                    if (array_key_exists('choose_value', $options)) {
                        $rewritten_options[$key] = $value;
                    } else {
                        $rewritten_options['age_min'] = $value;
                        $rewritten_options['age_max'] = $value;
                    }
                    break;
                case 'with_icon':
                    $rewritten_options['with_icons'] = [$value];
                    break;
                case 'without_icon':
                    $rewritten_options['without_icons'] = [$value];
                    break;
                default:
                    $rewritten_options[$key] = $value;
                    break;
            }
        }
        // TODO(4E): This might break the search icon.
        if (!array_key_exists('can_pass', $rewritten_options) || self::getCurrentNestedCardState()['replace_may_with_must']) {
            $rewritten_options['can_pass'] = false;
        }
        if (array_key_exists('color', $rewritten_options)) {
            $rewritten_options['color'] = array_unique($rewritten_options['color']);
        } else {
            $rewritten_options['color'] = Colors::ALL;
        }
        if (!array_key_exists('type', $rewritten_options)) {
            $rewritten_options['type'] = self::getActiveCardTypes();
        }
        if (!array_key_exists('icon', $rewritten_options)) {
            $rewritten_options['icon'] = $this->innovationGameState->usingFourthEditionRules() ? array(1, 2, 3, 4, 5, 6, 7) : array(1, 2, 3, 4, 5, 6);
        }
        if (array_key_exists('age', $rewritten_options)) {
            $rewritten_options['age'] = array_unique($rewritten_options['age']);
        } else {
            // TODO(FIGURES): Handle the age 0 deck
            $rewritten_options['age'] = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11];
        }
        if (!array_key_exists('players', $rewritten_options)) {
            $rewritten_options['players'] = self::getAllActivePlayers();
        }
        if (array_key_exists('choices', $rewritten_options)) {
            $rewritten_options['choices'] = array_unique($rewritten_options['choices']);
        } else {
            $rewritten_options['choices'] = [];
        }

        if (!$is_refreshing_options && self::getActivePlayerId() != $player_id) {
            $this->gamestate->changeActivePlayer($player_id);
        }

        $possible_special_types_of_choice = [
            'choose_from_list',
            'choose_value',
            'choose_color',
            'choose_two_colors',
            'choose_three_colors',
            'choose_player',
            'choose_rearrange',
            'choose_yes_or_no',
            'choose_type',
            'choose_non_negative_integer',
            'choose_icon_type',
            'choose_special_achievement',
        ];
        foreach ($possible_special_types_of_choice as $special_type_of_choice) {
            if (array_key_exists($special_type_of_choice, $options)) {
                $this->innovationGameState->set('special_type_of_choice', self::encodeSpecialTypeOfChoice($special_type_of_choice));
                $this->innovationGameState->set('can_pass', $rewritten_options['can_pass'] ? 1 : 0);

                // NOTE: It is the responsibility of the card's implementation to ensure that the array in use has at least one element in it.
                $this->innovationGameState->setFromArray('age_array', $rewritten_options['age']); // used by 'choose_value'
                $this->innovationGameState->setFromArray('color_array', $rewritten_options['color']); // used by 'choose_color', 'choose_two_colors', and 'choose_three_colors'
                $this->innovationGameState->setFromArray('type_array', $rewritten_options['type']); // used by 'choose_type'
                $this->innovationGameState->setFromArray('icon_array', $rewritten_options['icon']); // used by 'choose_icon_type'
                $this->innovationGameState->setFromArray('player_array', $rewritten_options['players']); // used by 'choose_player'
                $this->innovationGameState->setFromArray('choice_array', $rewritten_options['choices']); // used by 'choose_from_list'
                return;
            }
        }

        $this->innovationGameState->set('special_type_of_choice', 0);
        if (!$is_refreshing_options && !array_key_exists('n_min', $rewritten_options)) {
            $rewritten_options['n_min'] = 999;
        }
        if (!$is_refreshing_options && !array_key_exists('n_max', $rewritten_options)) {
            $rewritten_options['n_max'] = 999;
        }
        if (!array_key_exists('solid_constraint', $rewritten_options)) {
            $rewritten_options['solid_constraint'] = false;
        }
        if (!array_key_exists('age_min', $rewritten_options)) {
            // TODO(FIGURES): Handle the age 0 deck
            $rewritten_options['age_min'] = 1;
        }
        if (!array_key_exists('age_max', $rewritten_options)) {
            $rewritten_options['age_max'] = 11;
        }
        if (!array_key_exists('with_icons', $rewritten_options)) {
            $rewritten_options['with_icons'] = [];
        }
        if (!array_key_exists('without_icons', $rewritten_options)) {
            $rewritten_options['without_icons'] = [];
        }
        if (!array_key_exists('not_id', $rewritten_options)) {
            $rewritten_options['not_id'] = -2;
        }
        if (!array_key_exists('card_id_1', $rewritten_options)) {
            $rewritten_options['card_id_1'] = -2;
        }
        if (!array_key_exists('card_id_2', $rewritten_options)) {
            $rewritten_options['card_id_2'] = -2;
        }
        if (!array_key_exists('card_id_3', $rewritten_options)) {
            $rewritten_options['card_id_3'] = -2;
        }
        if (!array_key_exists('icon_hash_1', $rewritten_options)) {
            $rewritten_options['icon_hash_1'] = -1;
        }
        if (!array_key_exists('icon_hash_2', $rewritten_options)) {
            $rewritten_options['icon_hash_2'] = -1;
        }
        if (!array_key_exists('icon_hash_3', $rewritten_options)) {
            $rewritten_options['icon_hash_3'] = -1;
        }
        if (!array_key_exists('icon_hash_4', $rewritten_options)) {
            $rewritten_options['icon_hash_4'] = -1;
        }
        if (!array_key_exists('icon_hash_5', $rewritten_options)) {
            $rewritten_options['icon_hash_5'] = -1;
        }
        if (!array_key_exists('enable_autoselection', $rewritten_options)) {
            $rewritten_options['enable_autoselection'] = 1; // Non-aggressive autoselection
        }
        if (!array_key_exists('include_relics', $rewritten_options)) {
            $rewritten_options['include_relics'] = true;
        }
        if (!array_key_exists('include_special_achievements', $rewritten_options)) {
            $rewritten_options['include_special_achievements'] = false;
        }
        if (!array_key_exists('with_bonus', $rewritten_options)) {
            $rewritten_options['with_bonus'] = false;
        }
        if (!array_key_exists('without_bonus', $rewritten_options)) {
            $rewritten_options['without_bonus'] = false;
        }
        if (!array_key_exists('card_ids_are_in_auxiliary_array', $rewritten_options)) {
            $rewritten_options['card_ids_are_in_auxiliary_array'] = false;
        }
        if (!array_key_exists('bottom_from', $rewritten_options)) {
            $rewritten_options['bottom_from'] = false;
        }
        if (!array_key_exists('bottom_to', $rewritten_options)) {
            $rewritten_options['bottom_to'] = (array_key_exists('location_to', $rewritten_options) && $rewritten_options['location_to'] == 'deck');
        }
        if (!array_key_exists('score_keyword', $rewritten_options)) {
            $rewritten_options['score_keyword'] = false;
        }
        if (!array_key_exists('meld_keyword', $rewritten_options)) {
            $rewritten_options['meld_keyword'] = false;
        }
        if (!array_key_exists('foreshadow_keyword', $rewritten_options)) {
            $rewritten_options['foreshadow_keyword'] = false;
        }
        if (!array_key_exists('achieve_keyword', $rewritten_options)) {
            $rewritten_options['achieve_keyword'] = false;
        }
        if (!array_key_exists('safeguard_keyword', $rewritten_options)) {
            $rewritten_options['safeguard_keyword'] = false;
        }
        if (!array_key_exists('draw_keyword', $rewritten_options)) {
            $rewritten_options['draw_keyword'] = false;
        }
        if (!array_key_exists('return_keyword', $rewritten_options)) {
            $rewritten_options['return_keyword'] = false;
        }
        if (!array_key_exists('require_achievement_eligibility', $rewritten_options)) {
            $rewritten_options['require_achievement_eligibility'] = false;
        }
        if (!array_key_exists('refresh_selection', $rewritten_options)) {
            $rewritten_options['refresh_selection'] = false;
        }
        if (!array_key_exists('reveal_if_unable', $rewritten_options)) {
            $rewritten_options['reveal_if_unable'] = false;
        }
        if (!array_key_exists('has_demand_effect', $rewritten_options)) {
            $rewritten_options['has_demand_effect'] = false;
        }
        if (!array_key_exists('has_splay_direction', $rewritten_options)) {
            $rewritten_options['has_splay_direction'] = array(0, 1, 2, 3, 4); // Unsplayed, left, right, up, or aslant
        }
        if (!array_key_exists('splay_direction', $rewritten_options)) {
            $rewritten_options['splay_direction'] = -1;
        } else { // This is a choice for splay
            $rewritten_options['owner_from'] = $player_id;
            $rewritten_options['location_from'] = 'board'; // Splaying is equivalent as selecting a board card, by design
            $rewritten_options['location_to'] = 'board';
            $number_of_cards_on_board = self::countCardsInLocationKeyedByColor($player_id, 'board');

            // A color must have at least 1 card in order to be a valid target for splaying/unsplaying
            $colors = [];
            foreach ($rewritten_options['color'] as $color) {
                $current_splay_direction = self::getCurrentSplayDirection($player_id, $color);

                // Skip this color if the player is allowed to pass and splaying it won't do anything
                if ($rewritten_options['can_pass'] && ($current_splay_direction == $rewritten_options['splay_direction'] || $number_of_cards_on_board[$color] <= 1)) {
                    continue;
                }

                // Skip this color if it doesn't match the has_splay_direction filter
                if (!in_array($current_splay_direction, $rewritten_options['has_splay_direction'])) {
                    continue;
                }

                if ($number_of_cards_on_board[$color] > 0) {
                    $colors[] = $color;
                }
            }
            $rewritten_options['color'] = $colors;
        }

        foreach ($rewritten_options as $key => $value) {
            switch ($key) {
                case 'can_pass':
                case 'score_keyword':
                case 'meld_keyword':
                case 'foreshadow_keyword':
                case 'achieve_keyword':
                case 'draw_keyword':
                case 'safeguard_keyword':
                case 'return_keyword':
                case 'solid_constraint':
                case 'require_achievement_eligibility':
                case 'refresh_selection':
                case 'reveal_if_unable':
                case 'has_demand_effect':
                case 'bottom_from':
                case 'bottom_to':
                case 'include_relics':
                case 'include_special_achievements':
                case 'with_bonus':
                case 'without_bonus':
                case 'card_ids_are_in_auxiliary_array':
                    $value = $value ? 1 : 0;
                    break;
                case 'location_from':
                case 'location_to':
                    $value = Locations::encode($value);
                    break;
                case 'age':
                    $this->innovationGameState->setFromArray('age_array', $value);
                    break;
                case 'color':
                    $this->innovationGameState->setFromArray('color_array', $value);
                    break;
                case 'type':
                    $this->innovationGameState->setFromArray('type_array', $value);
                    break;
                case 'icon':
                    $this->innovationGameState->setFromArray('icon_array', $value);
                    break;
                case 'players':
                    $this->innovationGameState->setFromArray('player_array', $value);
                    break;
                case 'choices':
                    $this->innovationGameState->setFromArray('choice_array', $value);
                    break;
                case 'has_splay_direction':
                    $this->innovationGameState->setFromArray('has_splay_direction', $value);
                    break;
                case 'with_icons':
                    $this->innovationGameState->setFromArray('with_icons', $value);
                    break;
                case 'without_icons':
                    $this->innovationGameState->setFromArray('without_icons', $value);
                    break;
            }
            if ($key <> 'age' && $key <> 'color' && $key <> 'type' && $key <> 'icon' && $key <> 'players' && $key <> 'choices' && $key <> 'has_splay_direction' && $key <> 'with_icons' && $key <> 'without_icons') {
                if (!is_numeric($value)) {
                    throw new BgaUserException("Value for option '$key' must be numeric but was of type " . gettype($value));
                }
                $this->innovationGameState->set($key, $value);
            }
        }

        // Set the selection on DB side
        self::selectEligibleCards();

        $this->innovationGameState->set('n', 0);
    }

    function selectEligibleCards()
    {
        // Select in database the eligible cards for the current selection to be made.
        // Return the number of selected cards that way

        $player_id = self::getActivePlayerId();

        // Condition for owner
        $owner_from = $this->innovationGameState->get('owner_from');
        if ($owner_from == -2) { // Any player
            $condition_for_owner = "owner <> 0";
        } else if ($owner_from == -3) { // Any opponent
            $opponents = self::getObjectListFromDB(
                self::format("
                SELECT
                    player_id
                FROM
                    player
                WHERE
                    player_team <> (
                        SELECT
                            player_team
                        FROM
                            player
                        WHERE
                            player_id = {player_id}
                    )
            ",
                    array('player_id' => $player_id)
                ),
                true
            );
            $condition_for_owner = self::format("owner IN ({opponents})", array('opponents' => join(',', $opponents)));
        } else if ($owner_from == -4) { // Any other player
            $other_players = self::getObjectListFromDB(
                self::format("
                SELECT
                    player_id
                FROM
                    player
                WHERE
                    player_id <> {player_id}
            ",
                    array('player_id' => $player_id)
                ),
                true
            );
            $condition_for_owner = self::format("owner IN ({other_players})", array('other_players' => join(',', $other_players)));
        } else {
            $condition_for_owner = self::format("owner = {owner_from}", array('owner_from' => $owner_from));
        }

        // Condition for location
        $location_from = Locations::decode($this->innovationGameState->get('location_from'));
        if ($location_from == Locations::REVEALED_THEN_HAND) {
            $condition_for_location = "location IN ('revealed', 'hand')";
        } else if ($location_from == Locations::REVEALED_THEN_SCORE) {
            $condition_for_location = "location IN ('revealed', 'score')";
        } else if ($location_from == Locations::HAND_OR_SCORE) {
            $condition_for_location = "location IN ('hand', 'score')";
        } else if ($location_from == 'pile') {
            $condition_for_location = "location = 'board'";
        } else if ($location_from == Locations::PILE_OR_SCORE) {
            $condition_for_location = "location IN ('board', 'score')";
        } else {
            $condition_for_location = self::format("location = '{location_from}'", array('location_from' => $location_from));
        }

        // Condition for age because of achievement eligibility
        $condition_for_claimable_ages = true;
        if ($this->innovationGameState->get('require_achievement_eligibility') == 1) {
            $claimable_ages = self::getClaimableValuesIgnoringAvailability($player_id);
            if (count($claimable_ages) == 0) {
                $condition_for_claimable_ages = "FALSE";
            } else {
                $condition_for_claimable_ages = self::format("age IN ({claimable_ages})", ['claimable_ages' => join(',', $claimable_ages)]);
            }
        }

        // Condition for whether it has a demand effect
        $condition_for_demand_effect = "TRUE";
        if ($this->innovationGameState->get('has_demand_effect') == 1) {
            $condition_for_demand_effect = "has_demand = TRUE";
        }

        // Condition for color
        $color_array = $this->innovationGameState->getAsArray('color_array');
        $include_special_achievements = $this->innovationGameState->get('include_special_achievements') == 1;
        if ($include_special_achievements) {
            // NOTE: We currently assume that 'include_special_achievements' is not used in conjunction with any restrictions on color.
            $condition_for_color = "TRUE";
        } else if (count($color_array) == 0) {
            $condition_for_color = "FALSE";
        } else {
            $condition_for_color = "color IN (" . join(',', $color_array) . ")";
        }

        // Condition for type
        $type_array = $this->innovationGameState->getAsArray('type_array');
        $condition_for_type = count($type_array) == 0 ? "FALSE" : "type IN (" . join(',', $type_array) . ")";

        // Condition for icon
        $condition_for_icon = "TRUE";
        $with_icons = $this->innovationGameState->getAsArray('with_icons');
        if (count($with_icons) > 0) {
            $condition_for_icon = "(FALSE";
            foreach ($with_icons as $icon) {
                $condition_for_icon = $condition_for_icon . self::format(" OR spot_1 = {icon} OR spot_2 = {icon} OR spot_3 = {icon} OR spot_4 = {icon} OR spot_5 = {icon} OR spot_6 = {icon}", array('icon' => $icon));
            }
            $condition_for_icon = $condition_for_icon . ")";
        }
        foreach ($this->innovationGameState->getAsArray('without_icons') as $icon) {
            $condition_for_icon = $condition_for_icon . self::format(" AND (spot_1 IS NULL OR spot_1 <> {icon}) AND (spot_2 IS NULL OR spot_2 <> {icon}) AND (spot_3 IS NULL OR spot_3 <> {icon}) AND (spot_4 IS NULL OR spot_4 <> {icon}) AND (spot_5 IS NULL OR spot_5 <> {icon}) AND (spot_6 IS NULL OR spot_6 <> {icon})", array('icon' => $icon));
        }

        // Condition for icon hash
        $condition_for_icon_hash = "TRUE";
        $icon_hash_1 = $this->innovationGameState->get('icon_hash_1');
        $icon_hash_2 = $this->innovationGameState->get('icon_hash_2');
        $icon_hash_3 = $this->innovationGameState->get('icon_hash_3');
        $icon_hash_4 = $this->innovationGameState->get('icon_hash_4');
        $icon_hash_5 = $this->innovationGameState->get('icon_hash_5');
        if ($icon_hash_1 >= 0 || $icon_hash_2 >= 0 || $icon_hash_3 >= 0 || $icon_hash_4 >= 0 || $icon_hash_5 >= 0) {
            $condition_for_icon_hash = self::format("
                (
                    icon_hash = {icon_hash_1} OR
                    icon_hash = {icon_hash_2} OR
                    icon_hash = {icon_hash_3} OR
                    icon_hash = {icon_hash_4} OR
                    icon_hash = {icon_hash_5}
                )",
                array(
                    'icon_hash_1' => $icon_hash_1,
                    'icon_hash_2' => $icon_hash_2,
                    'icon_hash_3' => $icon_hash_3,
                    'icon_hash_4' => $icon_hash_4,
                    'icon_hash_5' => $icon_hash_5
                )
            );
        }

        // Condition for whether the stack is splayed
        $splay_directions = $this->innovationGameState->getAsArray('has_splay_direction');
        $condition_for_splay = "TRUE";
        if (count($splay_directions) == 0) {
            $condition_for_splay = "FALSE";
        } else if (count($splay_directions) < 5) {
            $condition_for_splay = "splay_direction IN (" . join(',', $splay_directions) . ")";
        }

        // Condition for requiring ID
        $condition_for_requiring_id = "TRUE";
        $card_ids_are_in_auxiliary_array = $this->innovationGameState->get('card_ids_are_in_auxiliary_array');
        if ($card_ids_are_in_auxiliary_array == 1) {
            $card_ids = self::getAuxiliaryArray();
            if (empty($card_ids)) {
                $condition_for_requiring_id = "FALSE";
            } else {
                $condition_for_requiring_id = "id IN (";
                $first_id = true;
                foreach ($card_ids as $card_id) {
                    if ($first_id) {
                        $first_id = false;
                    } else {
                        $condition_for_requiring_id .= ", ";
                    }
                    $condition_for_requiring_id .= "$card_id";
                }
                $condition_for_requiring_id .= ")";
            }
        } else {
            $card_id_1 = $this->innovationGameState->get('card_id_1');
            $card_id_2 = $this->innovationGameState->get('card_id_2');
            $card_id_3 = $this->innovationGameState->get('card_id_3');
            if ($card_id_3 != -2) {
                $condition_for_requiring_id = self::format("id IN ({card_id_1}, {card_id_2}, {card_id_3})", array('card_id_1' => $card_id_1, 'card_id_2' => $card_id_2, 'card_id_3' => $card_id_3));
            } else if ($card_id_2 != -2) {
                $condition_for_requiring_id = self::format("id IN ({card_id_1}, {card_id_2})", array('card_id_1' => $card_id_1, 'card_id_2' => $card_id_2));
            } else if ($card_id_1 != -2) {
                $condition_for_requiring_id = self::format("id IN ({card_id_1})", array('card_id_1' => $card_id_1));
            }
        }

        // Condition for excluding ID
        $condition_for_excluding_id = "TRUE";
        $not_id = $this->innovationGameState->get('not_id');
        if ($not_id != -2) { // Used by cards like Fission and Self service
            $condition_for_excluding_id = self::format("id <> {not_id}", array('not_id' => $not_id));
        }

        // Condition for including relics
        $condition_for_including_relic = "TRUE";
        $include_relics = $this->innovationGameState->get('include_relics');
        if ($include_relics == 0) {
            $condition_for_including_relic = "is_relic = FALSE";
        }

        // Condition for including cards with at least one bonus icon
        $condition_for_including_bonus = "TRUE";
        $with_bonus = $this->innovationGameState->get('with_bonus');
        if ($with_bonus == 1) {
            $condition_for_including_bonus = "(spot_1 >= 101 OR spot_2 >= 101 OR spot_3 >= 101 OR spot_4 >= 101 OR spot_5 >= 101 OR spot_6 >= 101)";
        }

        // Condition for excluding cards with at least one bonus icon
        $condition_for_excluding_bonus = "TRUE";
        $without_bonus = $this->innovationGameState->get('without_bonus');
        if ($without_bonus == 1) {
            $condition_for_excluding_bonus = "(spot_1 IS NULL OR spot_1 < 101) AND (spot_2 IS NULL OR spot_2 < 101) AND (spot_3 IS NULL OR spot_3 < 101) AND (spot_4 IS NULL OR spot_4 < 101) AND (spot_5 IS NULL OR spot_5 < 101) AND (spot_6 IS NULL OR spot_6 < 101)";
        }

        $condition_for_position = "TRUE";
        $join_for_position = "";
        if ($this->innovationGameState->get('splay_direction') == -1 && $location_from == 'board') {
            if ($this->innovationGameState->get('bottom_from') == 1) {
                $position_to_select = '0';
            } else {
                $position_to_select = 'MAX(position)';
            }
            $join_for_position = self::format("
                LEFT JOIN
                    (SELECT owner AS joined_owner, color AS joined_color, {position} AS position_to_select FROM card WHERE location = 'board' GROUP BY owner, color) AS joined
                    ON
                        owner = joined_owner AND
                        color = joined_color
            ",
                ['position' => $position_to_select]
            );
            $condition_for_position = "position = position_to_select";
        }

        $conditions = self::format("
            {condition_for_position} AND
            {condition_for_owner} AND
            {condition_for_location} AND
            {condition_for_claimable_ages} AND
            {condition_for_demand_effect} AND
            {condition_for_color} AND
            {condition_for_type} AND
            {condition_for_icon} AND
            {condition_for_icon_hash} AND
            {condition_for_splay} AND
            {condition_for_requiring_id} AND
            {condition_for_excluding_id} AND
            {condition_for_including_relic} AND
            {condition_for_including_bonus} AND
            {condition_for_excluding_bonus}
        ", [
            'condition_for_position'        => $condition_for_position,
            'condition_for_owner'           => $condition_for_owner,
            'condition_for_location'        => $condition_for_location,
            'condition_for_claimable_ages'  => $condition_for_claimable_ages,
            'condition_for_demand_effect'   => $condition_for_demand_effect,
            'condition_for_color'           => $condition_for_color,
            'condition_for_type'            => $condition_for_type,
            'condition_for_icon'            => $condition_for_icon,
            'condition_for_icon_hash'       => $condition_for_icon_hash,
            'condition_for_splay'           => $condition_for_splay,
            'condition_for_requiring_id'    => $condition_for_requiring_id,
            'condition_for_excluding_id'    => $condition_for_excluding_id,
            'condition_for_including_relic' => $condition_for_including_relic,
            'condition_for_including_bonus' => $condition_for_including_bonus,
            'condition_for_excluding_bonus' => $condition_for_excluding_bonus
        ]);

        // Condition for age
        if ($include_special_achievements) {
            // NOTE: We currently assume that 'include_special_achievements' is not used in conjunction with any restrictions on age.
            $condition_for_age = "TRUE";
        } else {
            if ($location_from === 'board' || $location_from === 'display') {
                $age_column = 'faceup_age';
            } else {
                $age_column = 'age';
            }

            $age_min = $this->innovationGameState->get('age_min');
            $age_max = $this->innovationGameState->get('age_max');

            // Handle "highest" and "lowest" special cases
            if ($age_min < 0 && $age_max < 0) {
                $max_or_min = $age_min == ValueSelectors::HIGHEST ? 'MAX' : 'MIN';
                $value = self::getUniqueValueFromDB(self::format("
                    SELECT
                        COALESCE({max_or_min}({age_column}), 0)
                    FROM
                        card
                    {join_for_position}
                    WHERE
                        {conditions}
                ", [
                    'max_or_min'        => $max_or_min,
                    'age_column'        => $age_column,
                    'join_for_position' => $join_for_position,
                    'conditions'        => $conditions
                ]));
                $age_min = $value;
                $age_max = $value;
            }
            $age_args = [
                'age_min'    => $age_min,
                'age_max'    => $age_max,
                'age_column' => $age_column,
            ];
            $condition_for_age = self::format("{age_column} BETWEEN {age_min} AND {age_max}", $age_args);
        }
        // TODO(LATER): Take 'age_array' into account if there are any cards which need to rely on this mechanism.

        self::DbQuery(
            self::format("
                UPDATE
                    card
                {join_for_position}
                SET
                    selected = TRUE
                WHERE
                    {condition_for_age} AND
                    {conditions}
                ",
                [
                    'join_for_position' => $join_for_position,
                    'condition_for_age' => $condition_for_age,
                    'conditions'        => $conditions,
                ]
            )
        );

        return self::getUniqueValueFromDB("SELECT COUNT(*) FROM card WHERE selected IS TRUE");
    }

    function encodeSpecialTypeOfChoice($special_type_of_choice)
    {
        // NOTE: The following value is unused and safe to re-use: 2
        switch ($special_type_of_choice) {
            case 'choose_from_list':
                return 1;
            case 'choose_value':
                return 3;
            case 'choose_color':
                return 4;
            case 'choose_two_colors':
                return 5;
            case 'choose_rearrange':
                return 6;
            case 'choose_yes_or_no':
                return 7;
            case 'choose_type':
                return 8;
            case 'choose_three_colors':
                return 9;
            case 'choose_player':
                return 10;
            case 'choose_non_negative_integer':
                return 11;
            case 'choose_icon_type':
                return 12;
            case 'choose_special_achievement':
                return 13;
        }
    }

    function decodeSpecialTypeOfChoice($special_type_of_choice_code)
    {
        // NOTE: The following value is unused and safe to re-use: 2
        switch ($special_type_of_choice_code) {
            case 1:
                return 'choose_from_list';
            case 3:
                return 'choose_value';
            case 4:
                return 'choose_color';
            case 5:
                return 'choose_two_colors';
            case 6:
                return 'choose_rearrange';
            case 7:
                return 'choose_yes_or_no';
            case 8:
                return 'choose_type';
            case 9:
                return 'choose_three_colors';
            case 10:
                return 'choose_player';
            case 11:
                return 'choose_non_negative_integer';
            case 12:
                return 'choose_icon_type';
            case 13:
                return 'choose_special_achievement';
        }
    }

    function decodeGameType($game_type_code)
    {
        switch ($game_type_code) {
            case 1:
                return 'individual';
            default:
                return 'team';
        }
    }

    /** Functions used for returning args to clients (Several states send these same things) **/
    function getArgForDogmaEffect()
    {
        $nested_card_state = self::getCurrentNestedCardState();

        // There won't be any nested card state if a player is returning cards after the Search icon was triggered.
        if ($nested_card_state == null) {
            // TODO(4E): Handle the other non-dogma interactions (junk achievement, returning artifacts, etc.)
            return array_merge([
                'qualified_effect' => clienttranslate('search icon'),
                'card_name'        => 'card_name',
                'i18n'             => ['qualified_effect', 'card_name'],
            ], self::getDogmaCardNames());
        }

        $card_id = $nested_card_state['card_id'];
        $current_effect_type = $nested_card_state['current_effect_type'];
        $current_effect_number = $nested_card_state['current_effect_number'];
        // Echo effects are sometimes executed on cards other than the card being dogma'd
        if ($current_effect_type == 3) {
            $nesting_index = $nested_card_state['nesting_index'];
            $card_id = self::getUniqueValueFromDB(
                self::format(
                    "SELECT card_id FROM echo_execution WHERE nesting_index = {nesting_index} AND execution_index = {effect_number}",
                    array('nesting_index' => $nesting_index, 'effect_number' => $current_effect_number)
                )
            );
        }
        $card = self::getCardInfo($card_id);

        $card_names = self::getDogmaCardNames();

        $args = array_merge(
            array(
                'qualified_effect'  => self::qualifyEffect($current_effect_type, $current_effect_number, $card),
                'card_name'         => 'card_name',
                'JSCardEffectQuery' => self::getJSCardEffectQuery($card, $current_effect_type, $current_effect_number)
            ),
            $card_names
        );

        $args['i18n'][] = 'qualified_effect';
        $args['i18n'][] = 'card_name';

        return $args;
    }

    function getArgForPlayerUnderDogmaEffect()
    {
        $player_id = self::getCurrentPlayerUnderDogmaEffect();
        return array_merge(
            self::getArgForDogmaEffect(),
            array(
                'player'             => '${player}',
                'player_id'          => $player_id,
                'player_name'        => self::renderPlayerName($player_id),
                'player_name_as_you' => 'You'
            )
        );
    }

    function getDogmaCardNames()
    { // Returns the name of the current dogma card or all the names where there are nested dogma effects
        $nesting_index = $this->innovationGameState->get('current_nesting_index');
        $player_id = self::getCurrentPlayerUnderDogmaEffect();

        // There won't be any nested card state if this interaction is happening outside of the context of a dogma effect
        if ($nesting_index < 0) {
            if ($this->innovationGameState->get('special_type_of_choice') > 0) { // Digging/stealing artifact
                return ['ref_player_0' => $player_id];
            } else if (Locations::decode($this->innovationGameState->get('location_from')) === Locations::MUSEUMS) { // Returning artifacts from museums
                return ['ref_player_0' => $player_id];
            } else { // Search icon or Junk Achievement icon
                return [
                    'card_0'       => self::getCardName($this->innovationGameState->get('melded_card_id')),
                    'ref_player_0' => $player_id,
                    'i18n'         => ['card_0'],
                ];
            }
        }

        $card_names = [];
        $i18n = [];
        for ($i = 0; $i <= $nesting_index; $i++) {
            $nested_card_state = self::getNestedCardState($i);
            $current_effect_type = $nested_card_state['current_effect_type'];
            $current_effect_number = $nested_card_state['current_effect_number'];
            $card_id = $nested_card_state['card_id'];
            // Echo effects are sometimes executed on cards other than the card being dogma'd
            if ($current_effect_type == 3) {
                $nesting_index = $nested_card_state['nesting_index'];
                $card_id = self::getUniqueValueFromDB(
                    self::format(
                        "SELECT card_id FROM echo_execution WHERE nesting_index = {nesting_index} AND execution_index = {effect_number}",
                        array('nesting_index' => $nesting_index, 'effect_number' => $current_effect_number)
                    )
                );
            }
            $card_names['card_' . $i] = self::getCardName($card_id);
            $card_names['ref_player_' . $i] = $player_id;
            $i18n[] = 'card_' . $i;
        }

        $card_names['i18n'] = $i18n;
        return $card_names;
    }

    function getJSCardId($card)
    {
        return "#item_" . $card['id'] . "__age_" . $card['age'] . "__type_" . $card['type'] . "__is_relic_" . $card['is_relic'] . "__M__card";
    }

    function getJSCardEffectQuery($card, $effect_type, $effect_number)
    {
        switch ($effect_type) {
            case self::ECHO_EFFECT:
                return self::getJSCardId($card) . " .echo_effect";
            case self::COMPEL_EFFECT:
                return self::getJSCardId($card) . " .i_compel_effect";
            case self::DEMAND_EFFECT:
                return self::getJSCardId($card) . " .i_demand_effect";
            default:
                return self::getJSCardId($card) . " .non_demand_effect_" . $effect_number;
        }
    }

    function setLauncherId(int $launcher_id)
    {
        self::updateCurrentNestedCardState('launcher_id', $launcher_id);
    }

    function getLauncherId(): int
    {
        if ($this->innovationGameState->get('current_nesting_index') < 0) {
            return intval($this->innovationGameState->get('active_player'));
        }
        return intval(self::getCurrentNestedCardState()['launcher_id']);
    }

    function incrementStep(int $delta)
    {
        self::setStep(self::getStep() + $delta);
    }

    function setStep(int $step)
    {
        self::updateCurrentNestedCardState('step', $step);
    }

    function getStep()
    {
        return self::getCurrentNestedCardState()['step'];
    }

    function incrementStepMax(int $delta)
    {
        self::setStepMax(self::getStepMax() + $delta);
    }

    function setStepMax(int $step_max)
    {
        self::updateCurrentNestedCardState('step_max', $step_max);
    }

    function getStepMax()
    {
        return self::getCurrentNestedCardState()['step_max'];
    }

    function setAuxiliaryValue(int $auxiliary_value)
    {
        self::updateCurrentNestedCardState('auxiliary_value', $auxiliary_value);
    }

    function setAuxiliaryValueFromArray($array)
    {
        self::setAuxiliaryValue(Arrays::encode($array));
    }

    function getAuxiliaryValue(): int
    {
        return intval(self::getCurrentNestedCardState()['auxiliary_value']);
    }

    function getAuxiliaryValueAsArray()
    {
        return Arrays::decode(self::getAuxiliaryValue());
    }

    function setAuxiliaryValue2(int $auxiliary_value_2)
    {
        self::updateCurrentNestedCardState('auxiliary_value_2', $auxiliary_value_2);
    }

    function setAuxiliaryValue2FromArray($array)
    {
        self::setAuxiliaryValue2(Arrays::encode($array));
    }

    function getAuxiliaryValue2(): int
    {
        return intval(self::getCurrentNestedCardState()['auxiliary_value_2']);
    }

    function getAuxiliaryValue2AsArray()
    {
        return Arrays::decode(self::getAuxiliaryValue2());
    }

    function setAuxiliaryArray($array)
    {
        $nesting_index = $this->innovationGameState->get('current_nesting_index');
        $array_vals = array_values($array);

        // Remove the old array (if it exists)
        self::DbQuery(self::format("DELETE FROM auxiliary_value_table WHERE nesting_index = {nesting_index}", array('nesting_index' => $nesting_index)));

        // Write array size
        self::DbQuery(self::format("
                INSERT INTO auxiliary_value_table
                    (nesting_index, array_index, value)
                VALUES
                    ({nesting_index}, 0, {array_size})
            ", array('nesting_index' => $nesting_index, 'array_size' => count($array_vals))));

        // Write array values
        for ($i = 1; $i <= count($array_vals); $i++) {
            self::DbQuery(self::format("
                INSERT INTO auxiliary_value_table
                    (nesting_index, array_index, value)
                VALUES
                    ({nesting_index}, {array_index}, {value})
            ", array('nesting_index' => $nesting_index, 'array_index' => $i, 'value' => $array_vals[$i - 1])));
        }
    }

    function getAuxiliaryArray()
    {
        $nesting_index = $this->innovationGameState->get('current_nesting_index');

        // Get array size
        $array_size = self::getUniqueValueFromDB(
            self::format("
            SELECT
                value
            FROM
                auxiliary_value_table
            WHERE
                nesting_index = {nesting_index} AND
                array_index = 0
        ",
                array('nesting_index' => $nesting_index)
            )
        );

        // Return empty array if no array was stored
        if ($array_size == null) {
            return [];
        }

        // Get array values
        $array = array();
        for ($i = 1; $i <= $array_size; $i++) {
            $array[] = self::getUniqueValueFromDB(
                self::format("
                SELECT
                    value
                FROM
                    auxiliary_value_table
                WHERE
                    nesting_index = {nesting_index} AND
                    array_index = {array_index}
            ",
                    array('nesting_index' => $nesting_index, 'array_index' => $i)
                )
            );
        }

        return $array;
    }

    function setActionScopedAuxiliaryArray($card_id, $player_id, $array)
    {
        $array_vals = array_values($array);

        // Remove the old array (if it exists)
        self::DbQuery(self::format("DELETE FROM action_scoped_auxiliary_value_table WHERE card_id = {card_id} AND player_id = {player_id}", array('card_id' => $card_id, 'player_id' => $player_id)));

        // Write array size
        self::DbQuery(self::format("
                INSERT INTO action_scoped_auxiliary_value_table
                    (card_id, player_id, array_index, value)
                VALUES
                    ({card_id}, {player_id}, 0, {array_size})
            ", array('card_id' => $card_id, 'player_id' => $player_id, 'array_size' => count($array_vals))));

        // Write array values
        for ($i = 1; $i <= count($array_vals); $i++) {
            self::DbQuery(self::format("
                INSERT INTO action_scoped_auxiliary_value_table
                    (card_id, player_id, array_index, value)
                VALUES
                    ({card_id}, {player_id}, {array_index}, {value})
            ", array('card_id' => $card_id, 'player_id' => $player_id, 'array_index' => $i, 'value' => $array_vals[$i - 1])));
        }
    }

    function getActionScopedAuxiliaryArray($card_id, $player_id)
    {
        // Get array size
        $array_size = self::getUniqueValueFromDB(
            self::format("
            SELECT
                value
            FROM
                action_scoped_auxiliary_value_table
            WHERE
                card_id = {card_id} AND
                player_id = {player_id} AND
                array_index = 0
        ",
                array('card_id' => $card_id, 'player_id' => $player_id)
            )
        );

        // Return empty array if no array was stored
        if ($array_size == null) {
            return [];
        }

        // Get array values
        $array = array();
        for ($i = 1; $i <= $array_size; $i++) {
            $array[] = self::getUniqueValueFromDB(
                self::format("
                SELECT
                    value
                FROM
                    action_scoped_auxiliary_value_table
                WHERE
                    card_id = {card_id} AND
                    player_id = {player_id} AND
                    array_index = {array_index}
            ",
                    array('card_id' => $card_id, 'player_id' => $player_id, 'array_index' => $i)
                )
            );
        }

        return $array;
    }

    function setIndexedAuxiliaryValue(int $index_id, int $value)
    {
        $nesting_index = $this->innovationGameState->get('current_nesting_index');

        // Check to see if a value already exists
        $result = self::getUniqueValueFromDB(
            self::format("
            SELECT
                value
            FROM
                indexed_auxiliary_value
            WHERE
                nesting_index = {nesting_index} AND
                index_id = {index_id}
        ",
                array('nesting_index' => $nesting_index, 'index_id' => $index_id)
            )
        );

        // If it doesn't already exist, insert it
        if ($result == null) {
            self::DbQuery(self::format("
                INSERT INTO indexed_auxiliary_value
                    (nesting_index, index_id, value)
                VALUES
                    ({nesting_index}, {index_id}, {value})
            ", array('nesting_index' => $nesting_index, 'index_id' => $index_id, 'value' => $value)));

            // If it does, update it
        } else {
            self::DbQuery(self::format("
                UPDATE
                    indexed_auxiliary_value
                SET
                    value = {value}
                WHERE
                    nesting_index = {nesting_index} AND index_id = {index_id}
            ", array('nesting_index' => $nesting_index, 'index_id' => $index_id, 'value' => $value)));
        }
    }

    function getIndexedAuxiliaryValue($index_id): int
    {
        $nesting_index = $this->innovationGameState->get('current_nesting_index');
        $result = self::getUniqueValueFromDB(
            self::format("
            SELECT
                value
            FROM
                indexed_auxiliary_value
            WHERE
                nesting_index = {nesting_index} AND
                index_id = {index_id}
        ",
                array('nesting_index' => $nesting_index, 'index_id' => $index_id)
            )
        );
        if ($result == null) {
            $result = -1;
        }
        return intval($result);
    }

    // TODO(LATER): Use this more widely.
    function getPlayerTableColumn($player_id, $column)
    {
        return self::getUniqueValueFromDB(self::format("SELECT {column} FROM player WHERE player_id = {player_id}", array('player_id' => $player_id, 'column' => $column)));
    }

    // TODO(LATER): Use this more widely.
    function setPlayerTableColumn($player_id, $column, $value)
    {
        self::DbQuery(self::format("UPDATE player SET {column} = {val} WHERE player_id = {player_id}", array('player_id' => $player_id, 'column' => $column, 'val' => $value)));
    }

    /** Returns true if the ongoing effect is currently executing a second time due to an Endorse action */
    function isExecutingAgainDueToEndorsedAction()
    {
        return $this->innovationGameState->get('current_nesting_index') == 0 && $this->innovationGameState->get('endorse_action_state') == 3;
    }

    /** Nested dogma excution management system: FIFO stack **/
    function selfExecute($card, $replace_may_with_must = false): bool
    {
        $player_id = self::getCurrentPlayerUnderDogmaEffect();

        self::checkForChainAchievement($player_id);

        $card_args = self::getNotificationArgsForCardList([$card]);
        if (self::getNonDemandEffect($card['id'], 1) === null && !self::getCardIdsWithEchoEffectsForNestedExecution($card)) {
            self::notifyAll('logWithCardTooltips', clienttranslate('There are no non-demand effects on ${card} to execute.'), ['card' => $card_args, 'card_ids' => [$card['id']]]);
            return false;
        }
        if ($replace_may_with_must) {
            self::notifyPlayer(
                $player_id,
                'logWithCardTooltips',
                clienttranslate('${You} self-execute the non-demand effect(s) of ${card}, replacing \'may\' with \'must\'.'),
                ['You' => 'You', 'card' => $card_args, 'card_ids' => [$card['id']]]
            );
            self::notifyAllPlayersBut(
                $player_id,
                'logWithCardTooltips',
                clienttranslate('${player_name} self-executes the non-demand effect(s) of ${card}, replacing \'may\' with \'must\'.'),
                ['player_name' => self::renderPlayerName($player_id), 'card' => $card_args, 'card_ids' => [$card['id']]]
            );
        } else {
            self::notifyPlayer(
                $player_id,
                'logWithCardTooltips',
                clienttranslate('${You} self-execute the non-demand effect(s) of ${card}.'),
                ['You' => 'You', 'card' => $card_args, 'card_ids' => [$card['id']]]
            );
            self::notifyAllPlayersBut(
                $player_id,
                'logWithCardTooltips',
                clienttranslate('${player_name} self-executes the non-demand effect(s) of ${card}.'),
                ['player_name' => self::renderPlayerName($player_id), 'card' => $card_args, 'card_ids' => [$card['id']]]
            );
        }
        self::pushCardIntoNestedDogmaStack($card, /*execute_demand_effects=*/ false, $replace_may_with_must);
        return true;
    }

    function superExecute($card)
    {
        $player_id = self::getCurrentPlayerUnderDogmaEffect();

        self::checkForChainAchievement($player_id);

        if ($this->innovationGameState->usingFourthEditionRules()) {
            $card_args = self::getNotificationArgsForCardList([$card]);
            self::notifyPlayer(
                $player_id,
                'logWithCardTooltips',
                clienttranslate('${You} super-execute the effects of ${card}.'),
                ['You' => 'You', 'card' => $card_args, 'card_ids' => [$card['id']]]
            );
            self::notifyAllPlayersBut(
                $player_id,
                'logWithCardTooltips',
                clienttranslate('${player_name} super-executes the effects of ${card}.'),
                ['player_name' => self::renderPlayerName($player_id), 'card' => $card_args, 'card_ids' => [$card['id']]]
            );
        } else {
            $current_nested_state = self::getCurrentNestedCardState();
            $current_card = self::getCardInfo($current_nested_state['card_id']);
            $card_1_args = self::getNotificationArgsForCardList([$current_card]);
            $card_2_args = self::getNotificationArgsForCardList([$card]);
            $initially_executed_card = self::getCardInfo($current_nested_state['executing_as_if_on_card_id']);
            $icon = Icons::render($initially_executed_card['dogma_icon']);
            self::notifyPlayer(
                $player_id,
                'logWithCardTooltips',
                clienttranslate('${You} fully execute the effects of ${card_2} as if it were on ${card_1}, using ${icon} as the featured icon.'),
                ['You' => 'You', 'card_1' => $card_1_args, 'card_2' => $card_2_args, 'card_ids' => [$current_card['id'], $card['id']], 'icon' => $icon]
            );
            self::notifyAllPlayersBut(
                $player_id,
                'logWithCardTooltips',
                clienttranslate('${player_name} fully executes the effects of ${card_2} as if it were on ${card_1}, using ${icon} as the featured icon.'),
                ['player_name' => self::renderPlayerName($player_id), 'card_1' => $card_1_args, 'card_2' => $card_2_args, 'card_ids' => [$current_card['id'], $card['id']], 'icon' => $icon]
            );
        }

        self::pushCardIntoNestedDogmaStack($card, /*execute_demand_effects=*/ true);
    }

    function getCardIdsWithEchoEffectsForNestedExecution($card)
    {
        if ($this->innovationGameState->getEdition() <= 3) {
            if (self::getEchoEffect($card['id'])) {
                return [$card['id']];
            }
            return [];
        } else {
            return self::getCardIdsWithVisibleEchoEffects($card);
        }
    }

    function checkForChainAchievement(int $player_id)
    {
        // TODO(4E): There may be a bug here if a card calls this which does not actually mention
        // "self-execute" or "fully execute".

        // TODO(4E): Update the implementation to match the new rule: "If, while self-executing or
        // super-executing a card’s non-demand effect, you would perform the keyword “self-execute”
        // or “super-execute”, first draw and achieve an 11, awarding yourself a Chain Achievement."
        if (!$this->innovationGameState->usingFourthEditionRules()) {
            return;
        }

        // Make sure this player is the same one who executed the current card
        if (self::getCurrentNestedCardState()['launcher_id'] != $player_id) {
            return;
        }
        if ($this->innovationGameState->get('current_nesting_index') >= 1) {
            self::incStat(1, 'execution_combo_count', $player_id);
            self::notifyPlayer($player_id, 'log', clienttranslate('${You} receive a Chain Achievement.'), ['You' => 'You',]);
            self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} receives a Chain Achievement.'), ['player_name' => self::renderPlayerName($player_id)]);
            self::executeDraw($player_id, 11, 'achievements');
        }
    }

    function pushCardIntoNestedDogmaStack($card, $execute_demand_effects, $replace_may_with_must = false)
    {
        $current_player_id = self::getCurrentPlayerUnderDogmaEffect();
        $nested_card_state = self::getCurrentNestedCardState();

        $super_execute = false;
        $as_if_on = $card['id'];
        if ($execute_demand_effects) {
            if ($this->innovationGameState->usingFourthEditionRules()) {
                $super_execute = true;
            } else {
                // Every 1st/3rd edition card that says "execute the effects" also says "as if they were on this card"
                $as_if_on = $nested_card_state['executing_as_if_on_card_id'];
            }
        }
        if ($nested_card_state['replace_may_with_must']) {
            $replace_may_with_must = true;
        }

        $next_nesting_index = $this->innovationGameState->get('current_nesting_index') + 1;

        $has_i_demand = self::getDemandEffect($card['id']) !== null;
        $has_i_compel = self::getCompelEffect($card['id']) !== null;
        if ($execute_demand_effects || $this->innovationGameState->usingFourthEditionRules()) {
            $card_ids_with_echo_effects = self::getCardIdsWithEchoEffectsForNestedExecution($card);
        } else {
            $card_ids_with_echo_effects = [];
        }

        if ($card_ids_with_echo_effects) {
            $effect_type = 3;
            $effect_number = count($card_ids_with_echo_effects);
            for ($i = count($card_ids_with_echo_effects); $i >= 1; $i--) {
                self::DbQuery(self::format("
                    INSERT INTO echo_execution
                        (nesting_index, execution_index, card_id)
                    VALUES
                        ({nesting_index}, {execution_index}, {card_id})
                ", ['nesting_index' => $next_nesting_index, 'execution_index' => $i, 'card_id' => $card_ids_with_echo_effects[$i - 1]]));
            }
        } else if ($execute_demand_effects && $has_i_demand) {
            $effect_type = 0;
            $effect_number = 1;
        } else if ($execute_demand_effects && $has_i_compel) {
            $effect_type = 2;
            $effect_number = 1;
        } else {
            $effect_type = 1;
            $effect_number = 1;
        }

        self::DbQuery(self::format("
            INSERT INTO nested_card_execution
                (nesting_index, card_id, executing_as_if_on_card_id, super_execute, replace_may_with_must, launcher_id, current_effect_type, current_effect_number, step, step_max)
            VALUES
                ({nesting_index}, {card_id}, {as_if_on}, {super_execute}, {replace_may_with_must}, {launcher_id}, {effect_type}, {effect_number}, -1, -1)
        ", [
            'nesting_index'         => $next_nesting_index,
            'card_id'               => $card['id'],
            'as_if_on'              => $as_if_on,
            'super_execute'         => $super_execute ? 'TRUE' : 'FALSE',
            'replace_may_with_must' => $replace_may_with_must ? 'TRUE' : 'FALSE',
            'launcher_id'           => $current_player_id,
            'effect_type'           => $effect_type,
            'effect_number'         => $effect_number,
        ]));
    }

    function popCardFromNestedDogmaStack()
    {
        self::DbQuery(self::format("DELETE FROM indexed_auxiliary_value WHERE nesting_index = {nesting_index}", array('nesting_index' => $this->innovationGameState->get('current_nesting_index'))));
        self::DbQuery(self::format("DELETE FROM nested_card_execution WHERE nesting_index = {nesting_index}", array('nesting_index' => $this->innovationGameState->get('current_nesting_index'))));
        $this->innovationGameState->increment('current_nesting_index', -1);
        self::updateCurrentNestedCardState('post_execution_index', 'post_execution_index + 1');
    }

    function echoEffectWasExecuted(): bool
    {
        if ($this->innovationGameState->usingFourthEditionRules()) {
            return true;
        }

        $nested_card_state = self::getCurrentNestedCardState();
        // Every card that says "execute the effects" also says "as if they were on this card". However, if the card says
        // "execute all of the non-demand dogma effects" then the echo effects will be skipped in the 3rd edition and earlier.
        return $nested_card_state['nesting_index'] == 0 || $nested_card_state['executing_as_if_on_card_id'] != $nested_card_state['card_id'];
    }

    function getNestedCardState($nesting_index)
    {
        return self::getObjectFromDB(
            self::format("
                SELECT
                    nesting_index,
                    card_id,
                    executing_as_if_on_card_id,
                    super_execute,
                    replace_may_with_must,
                    card_location,
                    launcher_id,
                    current_player_id,
                    current_effect_type,
                    current_effect_number,
                    step,
                    step_max,
                    post_execution_index,
                    performed_one_time_setup,
                    auxiliary_value,
                    auxiliary_value_2
                FROM
                    nested_card_execution
                WHERE
                    nesting_index = {nesting_index}",
                array('nesting_index' => $nesting_index)
            )
        );
    }

    function getCurrentNestedCardState()
    {
        return self::getNestedCardState($this->innovationGameState->get('current_nesting_index'));
    }

    function updateCurrentNestedCardState($column, $value)
    {
        self::DbQuery(
            self::format("
                UPDATE
                    nested_card_execution
                SET
                    {column} = {value}
                WHERE
                    nesting_index = {nesting_index}",
                array('column' => $column, 'value' => $value, 'nesting_index' => $this->innovationGameState->get('current_nesting_index'))
            )
        );
    }

    function getCurrentPlayerUnderDogmaEffect()
    {
        // There won't be any nested card state if a player is returning cards after the Search icon was triggered.
        $current_nested_state = self::getCurrentNestedCardState();
        if ($current_nested_state == null) {
            return $this->innovationGameState->get('active_player');
        }

        $player_id = $current_nested_state['current_player_id'];
        // TODO(LATER): Figure out why this workaround is necessary.
        if ($player_id == -1) {
            return $this->innovationGameState->get('active_player');
        }
        return $player_id;
    }

    function initialMeld($card_id)
    {
        // Check that this is the player's turn and that it is a "possible action" at this game state
        self::checkAction('initialMeld');
        $player_id = self::getCurrentPlayerId();

        // Check if the player really has this card
        $card = self::getCardInfo($card_id);

        if ($card['owner'] != $player_id || $card['location'] != "hand") {
            self::throwInvalidChoiceException();
        }

        // Stats
        self::setStat(1, 'turns_number', $player_id); // First turn for this player
        if (self::getStat('turns_number') == 0) {
            self::setStat(1, 'turns_number'); // First turn for the table
        }

        // Mark it as selected
        self::markAsSelected($card_id);

        // Notify
        self::notifyPlayer(
            $player_id,
            'log',
            clienttranslate('${You} choose a card.'),
            array(
                'You' => 'You'
            )
        );

        self::notifyAllPlayersBut(
            $player_id,
            'log',
            clienttranslate('${player_name} chooses a card.'),
            array(
                'player_name' => self::getPlayerNameFromId($player_id)
            )
        );

        // If that was the last player to choose his card, go on for the next state (whoBegins?), else, wait for remaining players
        $this->gamestate->setPlayerNonMultiactive($player_id, '');
    }

    function updateInitialMeld($card_id)
    {
        $this->gamestate->checkPossibleAction('updateInitialMeld');
        $this->gamestate->setPlayersMultiactive(array($this->getCurrentPlayerId()), 'error', false);

        // Check if the player really has this card
        $card = self::getCardInfo($card_id);
        $player_id = self::getCurrentPlayerId();
        if ($card['owner'] != $player_id || $card['location'] != "hand") {
            self::throwInvalidChoiceException();
        }

        // Update card selection
        $cards = self::getCardsInHand($player_id);
        foreach ($cards as $card_in_hand) {
            if ($card_in_hand['id'] == $card_id) {
                self::markAsSelected($card_in_hand['id']);
            } else {
                self::unmarkAsSelected($card_in_hand['id']);
            }
        }

        // Notify
        self::notifyPlayer(
            $player_id,
            'log',
            clienttranslate('${You} choose a card.'),
            array(
                'You' => 'You'
            )
        );

        self::notifyAllPlayersBut(
            $player_id,
            'log',
            clienttranslate('${player_name} chooses a card.'),
            array(
                'player_name' => self::getPlayerNameFromId($player_id)
            )
        );

        // If that was the last player to choose his card, go on for the next state (whoBegins?), else, wait for remaining players
        $this->gamestate->setPlayerNonMultiactive($player_id, '');
    }

    function passSeizeRelic()
    {
        // Check that this is the player's turn and that it is a "possible action" at this game state
        self::checkAction('passSeizeRelic');

        $player_id = self::getCurrentPlayerId();
        self::notifyPlayer($player_id, 'log', clienttranslate('${You} choose not to seize the relic.'), array('You' => 'You'));
        self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} chooses not to seize the relic.'), array('player_name' => self::getPlayerNameFromId($player_id)));
        $this->innovationGameState->set('relic_id', -1);

        self::trace('relicPlayerTurn->promoteCard (passSeizeRelic)');
        $this->gamestate->nextState('promoteCard');
    }

    function seizeRelicToHand()
    {
        // Check that this is the player's turn and that it is a "possible action" at this game state
        self::checkAction('seizeRelicToHand');

        $player_id = self::getCurrentPlayerId();
        $relic = self::getCardInfo($this->innovationGameState->get('relic_id'));

        if (!self::canSeizeRelicToAchievements($relic, $player_id)) {
            self::throwInvalidChoiceException();
        }

        if ($relic['owner'] != 0 && $relic['owner'] != $player_id) {
            self::incStat(1, 'relics_stolen_number', $relic['owner']);
        }
        self::incStat(1, 'relics_seized_number', $player_id);

        self::transferCardFromTo($relic, $player_id, 'hand');
        $this->innovationGameState->set('relic_id', -1);

        self::trace('relicPlayerTurn->promoteCard (seizeRelicToHand)');
        $this->gamestate->nextState('promoteCard');
    }

    function seizeRelicToAchievements()
    {
        // Check that this is the player's turn and that it is a "possible action" at this game state
        self::checkAction('seizeRelicToAchievements');

        $player_id = self::getCurrentPlayerId();
        $relic = self::getCardInfo($this->innovationGameState->get('relic_id'));

        if (!self::canSeizeRelicToAchievements($relic, $player_id)) {
            self::throwInvalidChoiceException();
        }

        if ($relic['owner'] != 0 && $relic['owner'] != $player_id) {
            self::incStat(1, 'relics_stolen_number', $relic['owner']);
        }
        self::incStat(1, 'relics_seized_number', $player_id);

        try {
            self::transferCardFromTo($relic, $player_id, "achievements");
        } catch (EndOfGame $e) {
            // End of the game: the exception has reached the highest level of code
            self::trace('EOG bubbled from self::promoteCard');
            self::trace('promoteCard->justBeforeGameEnd');
            $this->gamestate->nextState('justBeforeGameEnd');
            return;
        }

        $this->innovationGameState->set('relic_id', -1);

        self::trace('relicPlayerTurn->promoteCard (seizeRelicToAchievements)');
        $this->gamestate->nextState('promoteCard');
    }

    function dogmaArtifactOnDisplay()
    {
        // Check that this is the player's turn and that it is a "possible action" at this game state
        self::checkAction('dogmaArtifactOnDisplay');

        $player_id = self::getCurrentPlayerId();
        $card = self::getArtifactOnDisplay($player_id);

        // Cards without a featured icon cannot be dogma'd
        if (!$card['dogma_icon']) {
            self::throwInvalidChoiceException();
        }

        self::decreaseResourcesForArtifactOnDisplay($player_id, $card);

        self::setUpDogma($player_id, $card, self::countIconsOnCard($card, $card['dogma_icon']));
        self::incStat(1, 'free_action_dogma_number', $player_id);

        // Resolve the first dogma effect of the card
        self::trace('artifactPlayerTurn->dogmaEffect (dogmaArtifactOnDisplay)');
        $this->gamestate->nextState('dogmaEffect');
    }

    function returnArtifactOnDisplay()
    {
        // Check that this is the player's turn and that it is a "possible action" at this game state
        self::checkAction('returnArtifactOnDisplay');

        // Artifacts cannot be returned in the 4th edition
        if ($this->innovationGameState->usingFourthEditionRules()) {
            self::throwInvalidChoiceException();
        }

        $player_id = self::getCurrentPlayerId();
        $card = self::getArtifactOnDisplay($player_id);
        self::decreaseResourcesForArtifactOnDisplay($player_id, $card);
        self::returnCard($card);

        self::trace('artifactPlayerTurn->finishArtifactPlayerTurn (returnArtifactOnDisplay)');
        $this->gamestate->nextState('finishArtifactPlayerTurn');
    }

    function passArtifactOnDisplay()
    {
        // Check that this is the player's turn and that it is a "possible action" at this game state
        self::checkAction('passArtifactOnDisplay');

        $player_id = self::getCurrentPlayerId();

        if ($this->innovationGameState->usingFourthEditionRules()) {
            self::notifyPlayer($player_id, 'log', clienttranslate('${You} choose not to dogma your Artifact on display.'), array('You' => 'You'));
            self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} chooses not to dogma his Artifact on display.'), array('player_name' => self::getPlayerNameFromId($player_id)));
        } else {
            self::notifyPlayer($player_id, 'log', clienttranslate('${You} choose not to return or dogma your Artifact on display.'), array('You' => 'You'));
            self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} chooses not to return or dogma his Artifact on display.'), array('player_name' => self::getPlayerNameFromId($player_id)));
        }
        $card = self::getArtifactOnDisplay($player_id);
        self::decreaseResourcesForArtifactOnDisplay($player_id, $card);

        if ($this->innovationGameState->usingFourthEditionRules()) {
            self::rotateArtifactOnDisplayIntoMuseum($player_id);
            $card_ids = self::getArtifactIdsIfNoMuseumsAvailable();
            if ($card_ids) {
                self::setAuxiliaryArray($card_ids);
                $options = array(
                    'player_id'                       => $player_id,
                    'n'                               => count($card_ids),
                    'owner_from'                      => 'any player',
                    'location_from'                   => Locations::MUSEUMS,
                    'owner_to'                        => 0,
                    'location_to'                     => Locations::DECK,
                    'card_ids_are_in_auxiliary_array' => true,
                );
                self::setSelectionRange($options);
                self::trace('artifactPlayerTurn->preSelectionMove');
                $this->gamestate->nextState('preSelectionMove');
                return;
            }
        }

        self::trace('artifactPlayerTurn->finishArtifactPlayerTurn (passArtifactOnDisplay)');
        $this->gamestate->nextState('finishArtifactPlayerTurn');
    }

    function rotateArtifactOnDisplayIntoMuseum($player_id): bool
    {
        $artifact = self::getArtifactOnDisplay($player_id);
        if (!$artifact) {
            return false;
        }
        $available_museums = self::getCardsInLocation(0, Locations::MUSEUMS);
        if ($available_museums) {
            self::transferCardFromTo($available_museums[0], $player_id, Locations::MUSEUMS);
            self::transferCardFromTo($artifact, $player_id, Locations::MUSEUMS);
        } else {
            self::transferCardFromTo($artifact, $player_id, Locations::HAND);
        }
        return true;
    }

    function getArtifactIdsIfNoMuseumsAvailable(): array
    {
        if (self::countCardsInLocation(0, Locations::MUSEUMS)) {
            return [];
        }
        $card_ids = [];
        foreach (self::getAllActivePlayerIds() as $playerId) {
            foreach (self::getCardsInLocation($playerId, Locations::MUSEUMS) as $card) {
                if ($card['color'] !== null) {
                    $card_ids[] = $card['id'];
                }
            }
        }
        return $card_ids;
    }

    function stFinishArtifactPlayerTurn()
    {
        $player_id = self::getActivePlayerId();

        // Check for special achievements at end of free action (only necessary in 4th edition)
        if ($this->innovationGameState->usingFourthEditionRules()) {
            try {
                self::checkForSpecialAchievements(/*is_end_of_action_check=*/ true);
            } catch (EndOfGame $e) {
                // End of the game: the exception has reached the highest level of code
                self::trace('EOG bubbled from self::passArtifactOnDisplay');
                self::trace('finishArtifactPlayerTurn->justBeforeGameEnd');
                $this->gamestate->nextState('justBeforeGameEnd');
                return;
            }
        }

        self::giveExtraTime($player_id);
        $this->innovationGameState->set('current_action_number', 1);
        self::incStat(1, 'free_action_return_number', $player_id);

        self::trace('finishArtifactPlayerTurn->playerTurn');
        $this->gamestate->nextState('playerTurn');
    }

    function passPromoteCard()
    {
        // Check that this is the player's turn and that it is a "possible action" at this game state
        self::checkAction('passPromoteCard');

        // Promoting became mandatory in 4th edition
        if ($this->innovationGameState->usingFourthEditionRules()) {
            self::throwInvalidChoiceException();
        }

        $player_id = self::getCurrentPlayerId();
        self::notifyPlayer($player_id, 'log', clienttranslate('${You} choose not to promote a card from your forecast.'), array('You' => 'You'));
        self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} chooses not to promote a card from his forecast.'), array('player_name' => self::getPlayerNameFromId($player_id)));

        self::trace('promoteCardPlayerTurn->interPlayerTurn (passPromoteCard)');
        $this->gamestate->nextState('interPlayerTurn');
    }

    function promoteCard($card_id)
    {
        // Check that this is the player's turn and that it is a "possible action" at this game state
        self::checkAction('promoteCard');

        $originally_melded_card = self::getCardInfo($this->innovationGameState->get('melded_card_id'));
        $promoted_card = self::getCardInfo($card_id);
        $player_id = self::getCurrentPlayerId();

        if ($promoted_card === null || $promoted_card['owner'] != $player_id || $promoted_card['location'] != 'forecast') {
            self::throwInvalidChoiceException();
        }
        if ($promoted_card['age'] > $originally_melded_card['age']) {
            self::throwInvalidChoiceException();
        }

        try {
            self::meldCard($promoted_card, $player_id);
        } catch (EndOfGame $e) {
            // End of the game: the exception has reached the highest level of code
            self::trace('EOG bubbled from self::promoteCard');
            self::trace('promoteCard->justBeforeGameEnd');
            $this->gamestate->nextState('justBeforeGameEnd');
            return;
        }
        $this->innovationGameState->set('melded_card_id', $card_id);
        $this->innovationGameState->set('foreseen_card_id', $card_id);

        self::incStat(1, 'promoted_number', $player_id);

        if ($this->innovationGameState->usingFourthEditionRules()) {
            $card = self::getCardInfo($card_id);
            if ($card['dogma_icon']) {
                self::setUpDogma($player_id, self::getCardInfo($card_id));
                self::trace('promoteCardPlayerTurn->dogmaEffect (promoteCard)');
                $this->gamestate->nextState('dogmaEffect');
            } else {
                self::trace('promoteCardPlayerTurn->interPlayerTurn (unable to dogma)');
                $this->gamestate->nextState('interPlayerTurn');
            }
        } else {
            self::trace('promoteCardPlayerTurn->promoteDogmaPlayerTurn (promoteCard)');
            $this->gamestate->nextState('promoteDogmaPlayerTurn');
        }
    }

    function promoteCardBack($owner, $location, $age, $type, $is_relic, $position)
    {
        // Check that this is the player's turn and that it is a "possible action" at this game state
        self::checkAction('promoteCard');

        $card = self::getCardInfoFromPosition($owner, $location, $age, $type, $is_relic, $position);
        if ($card === null) {
            self::throwInvalidChoiceException();
        }
        self::promoteCard($card['id']);
    }


    function passDogmaPromotedCard()
    {
        // Check that this is the player's turn and that it is a "possible action" at this game state
        self::checkAction('passDogmaPromotedCard');

        // Dogma'ing the promoted card became mandatory in 4th edition
        if (!$this->innovationGameState->usingFourthEditionRules()) {
            self::throwInvalidChoiceException();
        }

        $player_id = self::getCurrentPlayerId();
        self::notifyPlayer($player_id, 'log', clienttranslate('${You} choose not to dogma your promoted card.'), array('You' => 'You'));
        self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} chooses not to dogma his promoted card.'), array('player_name' => self::getPlayerNameFromId($player_id)));

        self::trace('promoteDogmaPlayerTurn->interPlayerTurn (passDogmaPromotedCard)');
        $this->gamestate->nextState('interPlayerTurn');
    }

    function dogmaPromotedCard()
    {
        // Check that this is the player's turn and that it is a "possible action" at this game state
        self::checkAction('dogmaPromotedCard');

        $player_id = self::getCurrentPlayerId();
        self::notifyPlayer($player_id, 'log', clienttranslate('${You} choose to dogma your promoted card.'), array('You' => 'You'));
        self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} chooses to dogma his promoted card.'), array('player_name' => self::getPlayerNameFromId($player_id)));

        self::setUpDogma($player_id, self::getCardInfo($this->innovationGameState->get('melded_card_id')));

        self::trace('promoteDogmaPlayerTurn->dogmaEffect (dogmaPromotedCard)');
        $this->gamestate->nextState('dogmaEffect');
    }

    function achieve($owner, $location, $age)
    {
        // Check that this is the player's turn and that it is a "possible action" at this game state
        self::checkAction('achieve');
        $player_id = self::getActivePlayerId();

        // Check if there are any achievements/secrets available to claim
        $card = self::getObjectFromDB(
            self::format(
                "SELECT * FROM card WHERE location = '{location}' AND owner = {owner} AND age = {age} ORDER BY type LIMIT 1",
                ['owner' => $owner, 'location' => $location, 'age' => $age, 'player_id' => $player_id]
            )
        );
        if ($card === null) {
            self::throwInvalidChoiceException();
        }

        self::achieveSpecificCard($card);
    }

    function achieveCardBack($owner, $location, $age, $type, $is_relic, $position)
    {
        // Check that this is the player's turn and that it is a "possible action" at this game state
        self::checkAction('achieve');

        $card = self::getCardInfoFromPosition($owner, $location, $age, $type, $is_relic, $position);
        if ($card === null) {
            self::throwInvalidChoiceException();
        }
        self::achieveSpecificCard($card);
    }

    function achieveSpecificCard($card)
    {
        $player_id = self::getActivePlayerId();

        // Make sure the player has enough points to claim the card
        $can_claim = false;
        foreach (self::getClaimableValuesIgnoringAvailability($player_id) as $claimable_age) {
            if ($claimable_age == $card['age']) {
                $can_claim = true;
                break;
            }
        }
        if (!$can_claim) {
            self::throwInvalidChoiceException();
        }

        // Stats
        self::updateActionAndTurnStats($player_id);
        self::incStat(1, 'achieve_actions_number', $player_id);

        // Execute the transfer
        try {
            self::transferCardFromTo($card, $player_id, "achievements");
        } catch (EndOfGame $e) {
            // End of the game: the exception has reached the highest level of code
            self::trace('EOG bubbled from self::achieve');
            self::trace('playerTurn->justBeforeGameEnd');
            $this->gamestate->nextState('justBeforeGameEnd');
            return;
        }

        // End of player action
        self::trace('playerTurn->interPlayerTurn (achieve)');
        $this->gamestate->nextState('interPlayerTurn');
    }

    function draw()
    {
        // Check that this is the player's turn and that it is a "possible action" at this game state
        self::checkAction('draw');
        $player_id = self::getActivePlayerId();

        // Stats
        self::updateActionAndTurnStats($player_id);
        self::incStat(1, 'draw_actions_number', $player_id);

        // Execute the draw
        try {
            self::executeDraw($player_id); // Draw a card with age consistent with player board
        } catch (EndOfGame $e) {
            // End of the game: the exception has reached the highest level of code
            self::trace('EOG bubbled from self::draw');
            self::trace('playerTurn->justBeforeGameEnd');
            $this->gamestate->nextState('justBeforeGameEnd');
            return;
        }
        // End of player action
        self::trace('playerTurn->interPlayerTurn (draw)');
        $this->gamestate->nextState('interPlayerTurn');
    }

    function meld($card_id)
    {
        // Check that this is the player's turn and that it is a "possible action" at this game state
        self::checkAction('meld');
        $player_id = self::getActivePlayerId();

        // Check if the player really has this card in their hand or is in the location where artifacts can be melded from
        $card = self::getCardInfo($card_id);
        $artifact_location = $this->innovationGameState->usingFourthEditionRules() ? Locations::MUSEUMS : Locations::DISPLAY;
        if ($card['owner'] != $player_id || ($card['location'] != Locations::HAND && $card['location'] != $artifact_location)) {
            self::throwInvalidChoiceException();
        }

        // Identify the associated museum card (if melding an artifact from a museum)
        $museum = null;
        if ($card['location'] === Locations::MUSEUMS) {
            // The museum is always placed immediately before the associated artifact card
            $museum = self::getCardsInLocation($player_id, Locations::MUSEUMS)[$card['position'] - 1];
        }

        // Stats
        self::updateActionAndTurnStats($player_id);
        self::incStat(1, 'meld_actions_number', $player_id);

        // Execute the meld
        try {
            self::meldCard($card, $card['owner']);

            // Make the museum available again
            if ($museum) {
                self::transferCardFromTo($museum, 0, Locations::MUSEUMS);
            }
        } catch (EndOfGame $e) {
            // End of the game: the exception has reached the highest level of code
            self::trace('EOG bubbled from self::meld');
            self::trace('playerTurn->justBeforeGameEnd');
            $this->gamestate->nextState('justBeforeGameEnd');
            return;
        }

        $this->innovationGameState->set('melded_card_id', $card['id']);

        // Execute city's icon effect
        if ($card['type'] == 2) {
            try {

                $top_middle_icon = $card['spot_6'];
                $bottom_middle_icon = $card['spot_3'];

                // NOTE: This logic relies on the (correct) assumption that the Plus/Arrow icons only appear in the top-middle or bottom-middle of cards.
                $icons_to_check = array($top_middle_icon, $bottom_middle_icon);
                for ($i = 0; $i < count($icons_to_check); $i++) {
                    switch ($icons_to_check[$i]) {
                        case 11: // Left Arrow: Splay the city's color left
                            self::splayLeft($player_id, $player_id, $card['color']);
                            break;
                        case 12: // Right Arrow: Splay the city's color right
                            self::splayRight($player_id, $player_id, $card['color']);
                            break;
                        case 13: // Up Arrow: Splay the city's color up
                            self::splayUp($player_id, $player_id, $card['color']);
                            break;
                        case 14: // Plus: Draw a card of value one higher than the city's age
                            self::executeDraw($player_id, $card['age'] + 1);
                            break;
                        case 16: // Uplift: Junk the deck one higher than this city, then draw a card of value one higher than that
                            self::junkBaseDeck($card['age'] + 1);
                            self::executeDraw($player_id, $card['age'] + 2);
                            break;
                        case 17: // Unsplay: Each opponent unsplays this color
                            foreach (self::getActiveOpponentIds($player_id) as $opponent_id) {
                                self::unsplay($opponent_id, $opponent_id, $card['color']);
                            }
                            break;
                    }
                }

                // Junk Achievement
                if ($top_middle_icon == 15 || $bottom_middle_icon == 15) {
                    $options = array(
                        'player_id'     => $player_id,
                        'n'             => 1,
                        'owner_from'    => 0,
                        'location_from' => 'achievements',
                        'owner_to'      => 0,
                        'location_to'   => 'junk',
                        'age'           => $card['age'],
                    );
                    self::setSelectionRange($options);
                    self::trace('playerTurn->preSelectionMove');
                    $this->gamestate->nextState('preSelectionMove');
                    return;
                }

                // NOTE: This logic relies on the (correct) assumption that whenever there is a resource icon in the
                // top-midddle of the card that is age 5 or earlier (or using 3rd edition or earlier), that means that
                // it is a Search icon.
                if ($top_middle_icon >= 1 && $top_middle_icon <= 6 && (!$this->innovationGameState->usingFourthEditionRules() || $card['age'] <= 5)) {
                    // Determine how many cards can be drawn.
                    $deck_count = self::countCardsInLocationKeyedByAge(0, 'deck', CardTypes::BASE);
                    $age_of_melded_card = $card['age'];
                    $num_cards_to_reveal = min($age_of_melded_card, $deck_count[$card['age']]);

                    if ($num_cards_to_reveal > 0) {
                        $card_ids_to_return = array();
                        for ($i = 0; $i < $num_cards_to_reveal; $i++) {
                            $card = self::executeDraw($player_id, $card['age'], 'revealed', /*bottom_to=*/ false, CardTypes::BASE);
                            self::transferCardFromTo($card, $player_id, 'hand');
                            if (!self::hasRessource($card, $top_middle_icon)) {
                                $card_ids_to_return[] = $card['id'];
                            }
                        }
                        if ($num_cards_to_reveal < $age_of_melded_card) {
                            self::notifyGeneralInfo(clienttranslate('The ${age} supply pile ran out of cards, so no more cards will be drawn.'), array('age' => self::getAgeSquareWithType($age_of_melded_card, CardTypes::BASE)));
                        }
                        self::notifyGeneralInfo(clienttranslate('The revealed cards with a ${icon} will be kept and the others will be returned.'), array('icon' => Icons::render($top_middle_icon)));
                        if (count($card_ids_to_return) > 0) {
                            self::setAuxiliaryArray($card_ids_to_return);
                            $options = array(
                                'player_id'                       => $player_id,
                                'n'                               => count($card_ids_to_return),
                                'owner_from'                      => $player_id,
                                'location_from'                   => 'hand',
                                'owner_to'                        => 0,
                                'location_to'                     => 'deck',
                                'card_ids_are_in_auxiliary_array' => true,
                            );
                            self::setSelectionRange($options);
                            self::trace('playerTurn->preSelectionMove');
                            $this->gamestate->nextState('preSelectionMove');
                            return;
                        }
                    } else {
                        self::notifyGeneralInfo(clienttranslate('The ${age} supply pile was empty, so no cards could be drawn.'), array('age' => self::getAgeSquareWithType($age_of_melded_card, CardTypes::BASE)));
                    }
                }
            } catch (EndOfGame $e) {
                // End of the game: the exception has reached the highest level of code
                self::trace('EOG bubbled from self::meld');
                self::trace('playerTurn->justBeforeGameEnd');
                $this->gamestate->nextState('justBeforeGameEnd');
                return;
            }
        }

        self::trace('playerTurn->digArtifact');
        $this->gamestate->nextState('digArtifact');
    }

    function stDigArtifact()
    {
        try {

            $player_id = self::getActivePlayerId();
            $melded_card = self::getCardInfo($this->innovationGameState->get('melded_card_id'));

            if ($this->innovationGameState->citiesExpansionEnabled()) {
                // "When you take a Meld action to meld a card that adds a new color to your board, draw a City" (unless you already have a Cities card in hand)
                if ($melded_card['position'] == 0 && self::countCardsInLocation($player_id, 'hand', CardTypes::CITIES) == 0) {
                    self::executeDraw($player_id, self::getAgeToDrawIn($player_id), 'hand', /*bottom_to=*/ false, CardTypes::CITIES);
                }
            }

            if (!$this->innovationGameState->artifactsExpansionEnabled() || self::getArtifactOnDisplay($player_id)) {
                self::trace('digArtifact->promoteCard');
                $this->gamestate->nextState('promoteCard');
                return;
            }

            $stack = self::getCardsInLocationKeyedByColor($player_id, 'board')[$melded_card['color']];
            if (count($stack) >= 2) {
                $previous_top_card = $stack[count($stack) - 2];
            } else {
                $previous_top_card = null;
            }

            // A dig happens when a card is covered with a card of lower or equal value, or both cards have their hexagonal icons in the same location.
            $new_card_has_lower_or_equal_value = $previous_top_card !== null && $previous_top_card['faceup_age'] >= $melded_card['faceup_age'];
            $overlapping_icons = $previous_top_card !== null && self::haveOverlappingHexagonIcons($previous_top_card, $melded_card);
            $eligible_for_dig = $new_card_has_lower_or_equal_value || $overlapping_icons;

            $card_ids = [];
            if ($eligible_for_dig) {
                // You first draw up through any empty ages (base cards) before looking at the relevant artifact deck
                $age_after_drawing_up = self::getAgeToDrawIn($player_id, $previous_top_card['faceup_age']);
                $top_artifact_card = self::getDeckTopCard($age_after_drawing_up, CardTypes::ARTIFACTS);
                if ($top_artifact_card) {
                    $card_ids[] = $top_artifact_card['id'];
                }
                if ($this->innovationGameState->usingFourthEditionRules()) {
                    foreach (self::getActiveOpponentIds($player_id) as $opponent_id) {
                        foreach (self::getCardsInLocation($opponent_id, Locations::MUSEUMS) as $card) {
                            if ($card['color'] !== null && $card['faceup_age'] == $previous_top_card['faceup_age']) {
                                $card_ids[] = $card['id'];
                            }
                        }
                    }
                }
            }

            if ($card_ids) {
                self::setAuxiliaryArray($card_ids);
                $options = [
                    'player_id'        => $player_id,
                    'choose_from_list' => true,
                    'choices'          => range(0, count($card_ids) - 1),
                ];
                self::setSelectionRange($options);
                self::trace('digArtifact->preSelectionMove');
                $this->gamestate->nextState('preSelectionMove');
                return;
            } else if ($eligible_for_dig) {
                self::notifyPlayer($player_id, "log", clienttranslate('There are no Artifact cards in the ${age} deck, so the dig event is ignored.'), array('age' => self::getAgeSquare($age_after_drawing_up)));
            }

            self::trace('digArtifact->promoteCard');
            $this->gamestate->nextState('promoteCard');
            return;

        } catch (EndOfGame $e) {
            self::trace('EOG bubbled from self::digArtifact');
            self::trace('digArtifact->justBeforeGameEnd');
            $this->gamestate->nextState('justBeforeGameEnd');
            return;
        }
    }

    function stPromoteCard()
    {
        if ($this->innovationGameState->echoesExpansionEnabled()) {
            $melded_card = self::getCardInfo($this->innovationGameState->get('melded_card_id'));
            $card_counts = self::countCardsInLocationKeyedByAge($melded_card['owner'], 'forecast');
            for ($age = 1; $age <= $melded_card['age']; $age++) {
                if ($card_counts[$age] > 0) {
                    self::trace('promoteCard->promoteCardPlayerTurn');
                    // Give the player extra time to decide which card to promote
                    self::giveExtraTime($melded_card['owner']);
                    $this->gamestate->nextState('promoteCardPlayerTurn');
                    return;
                }
            }
        }

        self::trace('promoteCard->interPlayerTurn');
        $this->gamestate->nextState('interPlayerTurn');
    }

    function claimSpecialAchievement($player_id, $achievement_id): ?array
    {
        $achievement = self::getCardInfo($achievement_id);
        if ($achievement['owner'] == 0 && $achievement['location'] == 'achievements') {
            return self::transferCardFromTo($achievement, $player_id, 'achievements');
        } else {
            $card_args = self::getNotificationArgsForCardList([$achievement]);
            self::notifyAll('logWithCardTooltips', clienttranslate('${card} has already been claimed.'), ['card' => $card_args, 'card_ids' => [$achievement_id]]);
            return null;
        }
    }

    function haveOverlappingHexagonIcons(array $card_1, array $card_2): bool
    {
        for ($i = 1; $i <= 6; $i++) {
            $spot = 'spot_' . $i;
            if ($card_1[$spot] === '0' && $card_2[$spot] === '0') {
                return true;
            }
        }
        return false;
    }

    /* Returns null if there is no relic of the specified age */
    function getRelicForAge($age)
    {
        return self::getObjectFromDB(self::format("SELECT * FROM card WHERE age = {age} AND is_relic", array('age' => $age)));
    }

    function dogma($card_id, $card_id_to_return)
    {
        // Check that this is the player's turn and that it is a "possible action" at this game state
        self::checkAction('dogma');
        $player_id = self::getActivePlayerId();

        $card = self::getCardInfo($card_id);

        $card_to_return = null;
        if ($card_id_to_return === null) {
            if ($card['owner'] != $player_id) {
                self::throwInvalidChoiceException();
            }
        } else {

            $card_to_return = self::getCardInfo($card_id_to_return);

            // The distance rule is only in effect when using the 4th edition
            if (!$this->innovationGameState->usingFourthEditionRules()) {
                self::throwInvalidChoiceException();
            }
            if ($card_to_return['owner'] != $player_id || $card_to_return['location'] != 'hand') {
                self::throwInvalidChoiceException();
            }
            $found_owner = false;
            foreach (self::getPlayerIdsAffectedByDistanceRule($player_id) as $opponent_id) {
                if ($card['owner'] == $opponent_id) {
                    $found_owner = true;
                    break;
                }
            }
            if (!$found_owner) {
                self::throwInvalidChoiceException();
            }
        }

        // Card is not at the top of a stack
        if (!self::isTopBoardCard($card)) {
            self::throwInvalidChoiceException();
        }
        // Cards without a featured icon cannot be dogma'd
        if (!$card['dogma_icon']) {
            self::throwInvalidChoiceException();
        }

        // Stats
        self::updateActionAndTurnStats($player_id);
        self::incStat(1, 'dogma_actions_number', $player_id);
        if ($card['type'] == 1) {
            self::incStat(1, 'dogma_actions_number_targeting_artifact_on_board', $player_id);
        }

        self::setUpDogma($player_id, $card, /*extra_icons_from_artifact_on_display=*/ 0, /*endorse_payment_card=*/ null, $card_to_return);

        // Resolve the first dogma effect of the card
        self::trace('playerTurn->dogmaEffect (dogma)');
        $this->gamestate->nextState('dogmaEffect');
    }

    function endorse($card_to_endorse_id, $card_for_payment_id)
    {
        // Check that this is the player's turn and that it is a "possible action" at this game state
        self::checkAction('endorse');

        // Ensure that the player still has an endorse action to use this turn
        if ($this->innovationGameState->get('endorse_action_state') == 0) {
            self::throwInvalidChoiceException();
        }

        $player_id = self::getActivePlayerId();
        $card_to_endorse = self::getCardInfo($card_to_endorse_id);

        // Check that the player really has this card on his board
        if ($card_to_endorse['owner'] != $player_id || $card_to_endorse['location'] != "board") {
            self::throwInvalidChoiceException();
        }
        // Card is not at the top of a stack
        if (!self::isTopBoardCard($card_to_endorse)) {
            self::throwInvalidChoiceException();
        }
        // Cards without a featured icon cannot be dogma'd
        if (!$card_to_endorse['dogma_icon']) {
            self::throwInvalidChoiceException();
        }

        // Check that the player really has a card to tuck/junk
        $card_payment = self::getCardInfo($card_for_payment_id);
        if ($card_payment['owner'] != $player_id || $card_payment['location'] != "hand") {
            self::throwInvalidChoiceException();
        }

        // Make sure the endorsement is valid given this card being tucked/junked
        $max_payment_age = self::getMaxAgeForEndorsePayment($card_to_endorse);
        if ($max_payment_age == null || $card_payment['age'] > $max_payment_age) {
            self::throwInvalidChoiceException();
        }

        $this->innovationGameState->set('endorse_action_state', 2);

        try {
            // The tuck to pay for the Endorse action happens inside of setUpDogma
            self::setUpDogma($player_id, $card_to_endorse, /*extra_icons_from_artifact_on_display=*/ 0, $card_payment);
        } catch (EndOfGame $e) {
            // End of the game: the exception has reached the highest level of code
            self::trace('EOG bubbled from self::endorse');
            self::trace('playerTurn->justBeforeGameEnd');
            $this->gamestate->nextState('justBeforeGameEnd');
            return;
        }

        // Resolve the first dogma effect of the card
        self::trace('playerTurn->dogmaEffect (dogma)');
        $this->gamestate->nextState('dogmaEffect');
    }

    function updateActionAndTurnStats($player_id)
    {
        if ($this->innovationGameState->get('current_action_number') == 1) {
            self::incStat(1, 'turns_number');
            self::incStat(1, 'turns_number', $player_id);
        }
        self::incStat(1, 'actions_number');
        self::incStat(1, 'actions_number', $player_id);
    }

    function increaseResourcesForArtifactOnDisplay($player_id, $card)
    {
        // Battleship Yamato does not have any icons on it
        if ($card['dogma_icon'] == null) {
            $resource_icon = null;
            $resource_count_delta = 0;
        } else {
            $resource_icon = $card['dogma_icon'];
            $resource_count_delta = self::countIconsOnCard($card, $resource_icon);
        }
        self::updateResourcesForArtifactOnDisplay($player_id, $resource_icon, $resource_count_delta);
    }

    function decreaseResourcesForArtifactOnDisplay($player_id, $card)
    {
        // Battleship Yamato does not have any icons on it
        if ($card['dogma_icon'] == null) {
            $resource_icon = null;
            $resource_count_delta = 0;
        } else {
            $resource_icon = $card['dogma_icon'];
            $resource_count_delta = -self::countIconsOnCard($card, $resource_icon);
        }
        self::updateResourcesForArtifactOnDisplay($player_id, $resource_icon, $resource_count_delta);
    }

    function updateResourcesForArtifactOnDisplay($player_id, $resource_icon, $resource_count_delta)
    {
        self::notifyAll(
            'updateResourcesForArtifactOnDisplay',
            '',
            array(
                'player_id'            => $player_id,
                'resource_icon'        => $resource_icon,
                'resource_count_delta' => $resource_count_delta,
            )
        );
    }

    function setUpDogma($player_id, $card, $extra_icons_from_artifact_on_display = 0, $endorse_payment_card = null, $card_to_return = null)
    {

        self::notifyDogma($card);

        if ($card_to_return != null) {
            try {
                self::returnCard($card_to_return);
            } catch (EndOfGame $e) {
                self::trace('EOG bubbled from self::setUpDogma');
                throw $e; // Re-throw exception to higher level
            }
        }

        if ($endorse_payment_card != null) {
            try {
                if ($this->innovationGameState->usingFourthEditionRules()) {
                    self::junkCard($endorse_payment_card);
                } else {
                    self::tuckCard($endorse_payment_card, $player_id);
                }
            } catch (EndOfGame $e) {
                self::trace('EOG bubbled from self::setUpDogma');
                throw $e; // Re-throw exception to higher level
            }
        }

        $dogma_icon = $card['dogma_icon'];
        $icon_column = 'player_icon_count_' . $dogma_icon;

        $players = self::getCollectionFromDB(self::format("SELECT player_index, player_id, player_team, {icon_column} FROM player", array('icon_column' => $icon_column)));

        // Count how many each player has of the featured icon
        $player_index = self::playerIdToPlayerIndex($player_id);
        $dogma_player_icon_count = $players[$player_index][$icon_column] + $extra_icons_from_artifact_on_display;
        foreach ($players as $index => $player) {
            $player_icon_count = $index == $player_index ? $dogma_player_icon_count : $player[$icon_column];
            $this->notifications->notifyPlayerIconCount($player['player_id'], $dogma_icon, $player_icon_count);
            self::DBQuery(
                self::format("
                UPDATE 
                    player
                SET
                    featured_icon_count = {featured_icon_count}
                WHERE
                    player_id = {player_id}"
                    ,
                    array('featured_icon_count' => $player_icon_count, 'player_id' => $player['player_id'])
                )
            );
        }

        $visible_echo_effects = self::getCardIdsWithVisibleEchoEffects($card);
        // NOTE: In the 4th edition, echo effects are skipped when using the free dogma action on an artifact on display
        $execute_echo_effects = $this->innovationGameState->getEdition() <= 3 || $extra_icons_from_artifact_on_display === 0;
        if ($execute_echo_effects && !empty($visible_echo_effects)) {
            $current_effect_type = 3; // echo
            $current_effect_number = count($visible_echo_effects);
            for ($i = count($visible_echo_effects); $i >= 1; $i--) {
                self::DbQuery(self::format("
                    INSERT INTO echo_execution
                        (nesting_index, execution_index, card_id)
                    VALUES
                        (0, {execution_index}, {card_id})
                ", array('execution_index' => $i, 'card_id' => $visible_echo_effects[$i - 1])));
            }
        } else if (self::getCompelEffect($card['id'])) {
            $current_effect_type = 2; // I compel
            $current_effect_number = 1;
        } else if (self::getDemandEffect($card['id'])) {
            $current_effect_type = 0; // I demand
            $current_effect_number = 1;
        } else {
            $current_effect_type = 1; // non-demand
            $current_effect_number = 1;
        }

        // Write info in global variables to prepare the first effect
        self::DbQuery("DELETE FROM indexed_auxiliary_value");
        $this->innovationGameState->set('current_nesting_index', 0);
        self::DbQuery(
            self::format("
                UPDATE
                    nested_card_execution
                SET
                    card_id = {card_id},
                    executing_as_if_on_card_id = {card_id},
                    card_location = '{card_location}',
                    launcher_id = {launcher_id},
                    current_effect_type = {effect_type},
                    current_effect_number = {effect_number},
                    post_execution_index = 0,
                    performed_one_time_setup = FALSE
                WHERE
                    nesting_index = 0",
                array('card_id' => $card['id'], 'card_location' => $card['location'], 'launcher_id' => $player_id, 'effect_type' => $current_effect_type, 'effect_number' => $current_effect_number)
            )
        );
        $this->innovationGameState->set('sharing_bonus', 0);
        $this->innovationGameState->set('dogma_had_impact', 0);
        self::DbQuery("UPDATE player SET distance_rule_share_state = 0");
        self::DbQuery("UPDATE player SET distance_rule_demand_state = 0");
    }

    function choose($card_id)
    {
        // Check that this is the player's turn and that it is a "possible action" at this game state
        self::checkAction('choose');

        $is_special_choice = $this->innovationGameState->get('special_type_of_choice') > 0;
        if ($card_id == -1) {
            // The player chooses to pass or stop
            if ($this->innovationGameState->get('can_pass') == 0 && ($this->innovationGameState->get('n_min') > 0 || $is_special_choice)) {
                self::throwInvalidChoiceException();
            }
            if ($is_special_choice) {
                $this->innovationGameState->set('choice', -2);
            } else {
                $this->innovationGameState->set('id_last_selected', -1);
            }
        } else if ($is_special_choice) {
            self::throwInvalidChoiceException();
        } else {
            // Check if the card is within the selection range
            $card = self::getCardInfo($card_id);

            if (!$card['selected']) {
                self::throwInvalidChoiceException();
            }

            $this->innovationGameState->set('id_last_selected', $card_id);
            self::unmarkAsSelected($card_id);

            // Passing is only possible at the beginning of the step
            $this->innovationGameState->set('can_pass', 0);
        }

        // Return to the resolution of the effect
        self::trace('selectionMove->interSelectionMove (choose)');
        $this->gamestate->nextState('interSelectionMove');
    }

    function chooseRecto($owner, $location, $age, $type, $is_relic, $position)
    {
        // Check that this is the player's turn and that it is a "possible action" at this game state
        self::checkAction('choose');
        $players = array_keys(self::loadPlayersBasicInfos());
        if ($this->innovationGameState->get('special_type_of_choice') != 0) {
            self::throwInvalidChoiceException();
        }
        if ((!in_array($owner, $players) && $owner != 0) || !in_array($location, self::getObjectListFromDB("SELECT DISTINCT location FROM card", true)) || $age < 1 || $age > 11) {
            self::throwInvalidChoiceException();
        }

        $card = self::getCardInfoFromPosition($owner, $location, $age, $type, $is_relic, $position);
        if ($card === null) {
            self::throwInvalidChoiceException();
        }
        if (!$card['selected']) {
            self::throwInvalidChoiceException();
        }

        $this->innovationGameState->set('id_last_selected', $card['id']);
        self::unmarkAsSelected($card['id']);

        // Passing is only possible at the beginning of the step
        $this->innovationGameState->set('can_pass', 0);

        // Return to the resolution of the effect
        self::trace('selectionMove->interSelectionMove (chooseRecto)');
        $this->gamestate->nextState('interSelectionMove');
    }

    function chooseSpecialOption($choice)
    {
        // Check that this is the player's turn and that it is a "possible action" at this game state
        self::checkAction('choose');
        $player_id = self::getActivePlayerId();

        $special_type_of_choice = $this->innovationGameState->get('special_type_of_choice');

        if ($special_type_of_choice == 0) { // This is not a special choice
            self::throwInvalidChoiceException();
        }

        switch (self::decodeSpecialTypeOfChoice($special_type_of_choice)) {
            case 'choose_from_list':
                if (!ctype_digit($choice) || !in_array($choice, $this->innovationGameState->getAsArray('choice_array'))) {
                    self::throwInvalidChoiceException();
                }
                break;
            case 'choose_value':
                if (!ctype_digit($choice) || !in_array($choice, $this->innovationGameState->getAsArray('age_array'))) {
                    self::throwInvalidChoiceException();
                }
                break;
            case 'choose_non_negative_integer':
                if (!ctype_digit($choice) || $choice < 0 || $choice > 1000) {
                    self::throwInvalidChoiceException();
                }
                break;
            case 'choose_color':
                if (!ctype_digit($choice) || !in_array($choice, $this->innovationGameState->getAsArray('color_array'))) {
                    self::throwInvalidChoiceException();
                }
                break;
            case 'choose_two_colors':
                if (!ctype_digit($choice) || $choice < 0) {
                    self::throwInvalidChoiceException();
                }
                $colors = Arrays::decode($choice);
                if (count($colors) <> 2 || $colors[0] == $colors[1] || !in_array($colors[0], $this->innovationGameState->getAsArray('color_array')) || !in_array($colors[1], $this->innovationGameState->getAsArray('color_array'))) {
                    self::throwInvalidChoiceException();
                }
                break;
            case 'choose_three_colors':
                if (!ctype_digit($choice) || $choice < 0) {
                    self::throwInvalidChoiceException();
                }
                $colors = Arrays::decode($choice);
                $allowed_color_choices = $this->innovationGameState->getAsArray('color_array');
                if (count($colors) <> 3 || count(array_unique($colors)) <> 3 || !in_array($colors[0], $allowed_color_choices) || !in_array($colors[1], $allowed_color_choices) || !in_array($colors[2], $allowed_color_choices)) {
                    self::throwInvalidChoiceException();
                }
                break;
            case 'choose_player':
                if (!ctype_digit($choice)) {
                    self::throwInvalidChoiceException();
                }
                $player_index = self::getUniqueValueFromDB(self::format("SELECT player_index FROM player WHERE player_id = {player_id}", array('player_id' => $choice)));
                if ($player_index === null || !in_array($player_index, $this->innovationGameState->getAsArray('player_array'))) {
                    // TODO(4E): Remove debugging once the bug is gone (I think it will be fixed now that I changed == to === above)
                    if (self::getGameStateValue('debug_mode') >= 1) {
                        error_log("Invalid player index: $player_index");
                        error_log("Valid player indexes: " . implode(", ", $this->innovationGameState->getAsArray('player_array')));
                    }
                    self::throwInvalidChoiceException();
                }
                break;
            case 'choose_special_achievement':
                if (!ctype_digit($choice)) {
                    self::throwInvalidChoiceException();
                }
                $special_achievement = self::getCardInfo($choice);
                if ($special_achievement === null || $special_achievement['owner'] != 0 || $special_achievement['age'] != null) {
                    self::throwInvalidChoiceException();
                }
                if ($special_achievement['location'] != 'achievements' && $special_achievement['location'] != 'junk') {
                    self::throwInvalidChoiceException();
                }
                break;
            case 'choose_rearrange':
                // Choice contains the color and the permutations made
                if (!is_array($choice) || !array_key_exists('color', $choice)) {
                    self::throwInvalidChoiceException();
                }
                $color = $choice['color'];
                if (!ctype_digit($color) || $color < 0 || $color > 4) {
                    self::throwInvalidChoiceException();
                }
                if (!array_key_exists('permutations_done', $choice)) {
                    self::throwInvalidChoiceException();
                }
                $permutations_done = $choice['permutations_done'];
                if (!is_array($permutations_done) || count($permutations_done) == 0) {
                    self::throwInvalidChoiceException();
                }
                $n = self::countCardsInLocationKeyedByColor($player_id, 'board');
                $n = $n[$color];

                foreach ($permutations_done as $permutation) {
                    if (!array_key_exists('position', $permutation)) {
                        self::throwInvalidChoiceException();
                    }
                    $position = $permutation['position'];
                    if (!array_key_exists('delta', $permutation)) {
                        self::throwInvalidChoiceException();
                    }
                    $delta = $permutation['delta'];
                    if ($delta <> 1 && $delta <> -1) {
                        self::throwInvalidChoiceException();
                    }
                    if (!ctype_digit($position) || $position >= $n || $position + $delta >= $n) {
                        self::throwInvalidChoiceException();
                    }
                }

                // Do the rearrangement now
                $actual_change = self::rearrange($player_id, $color, $permutations_done);

                if (!$actual_change) {
                    self::throwInvalidChoiceException();
                }

                // Update max age on board in case it changed
                $new_max_age_on_board = self::getMaxAgeOnBoardTopCards($player_id);
                self::setStat($new_max_age_on_board, 'max_age_on_board', $player_id);

                self::notifyPlayer(
                    $player_id,
                    'rearrangedPile',
                    clienttranslate('${You} rearrange your ${color} stack.'),
                    array(
                        'i18n'                 => array('color'),
                        'player_id'            => $player_id,
                        'new_max_age_on_board' => $new_max_age_on_board,
                        'rearrangement'        => $choice,
                        'You'                  => 'You',
                        'color'                => Colors::render($color)
                    )
                );
                self::notifyAllPlayersBut(
                    $player_id,
                    'rearrangedPile',
                    clienttranslate('${player_name} rearranges his ${color} stack.'),
                    array(
                        'i18n'                 => array('color'),
                        'player_id'            => $player_id,
                        'new_max_age_on_board' => $new_max_age_on_board,
                        'rearrangement'        => $choice,
                        'player_name'          => self::renderPlayerName($player_id),
                        'color'                => Colors::render($color)
                    )
                );

                $end_of_game = false;

                self::removeOldFlagsAndFountains();
                try {
                    self::addNewFlagsAndFountains();
                } catch (EndOfGame $e) {
                    $end_of_game = true;
                }

                try {
                    self::checkForSpecialAchievements();
                } catch (EndOfGame $e) {
                    $end_of_game = true;
                }

                if ($end_of_game) {
                    self::trace('EOG bubbled from self::chooseSpecialOption');
                    self::trace('selectionMove->justBeforeGameEnd');
                    $this->gamestate->nextState('justBeforeGameEnd');
                    return;
                }

                $choice = 1;
                break;
            case 'choose_yes_or_no':
                // Yes/no choice
                if ($choice != 0 && $choice != 1) {
                    self::throwInvalidChoiceException();
                }
                break;
            case 'choose_type':
                if (!ctype_digit($choice) || !in_array($choice, $this->innovationGameState->getAsArray('type_array'))) {
                    self::throwInvalidChoiceException();
                }
                break;
            case 'choose_icon_type':
                if (!ctype_digit($choice) || !in_array($choice, $this->innovationGameState->getAsArray('icon_array'))) {
                    self::throwInvalidChoiceException();
                }
                break;
            default:
                break;
        }
        $this->innovationGameState->set('choice', $choice);

        // Return to the resolution of the effect
        self::trace('selectionMove->interSelectionMove (chooseSpecialOption)');
        $this->gamestate->nextState('interSelectionMove');
    }

    function updateDisplayMode($display_mode)
    {
        $player_id = self::getCurrentPlayerId();
        self::setPlayerWishForSplay($player_id, $display_mode);
        self::notifyPlayer($player_id, 'log', '', array());
    }

    function updateViewFull($view_full)
    {
        $player_id = self::getCurrentPlayerId();
        self::setPlayerWishForViewFull($player_id, $view_full);
        self::notifyPlayer($player_id, 'log', '', array());
    }

    function throwInvalidChoiceException()
    {
        if (self::getGameStateValue('debug_mode') >= 1) {
            debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        }
        throw new BgaUserException(self::_("Your choice was invalid (try refreshing the page)"));
    }

    function argTurn0()
    {
        if (self::decodeGameType($this->innovationGameState->get('game_type')) == 'team') {
            // Indicate what the teams are
            $messages = array();
            foreach (self::loadPlayersBasicInfos() as $player_id => $player) {
                $teammate_id = self::getPlayerTeammate($player_id);
                $teammate_name = self::getPlayerNameFromId($teammate_id);
                $message = self::format(clienttranslate('{You} are in team with {player_name}.'), array('You' => 'You', 'player_name' => self::getColoredText($teammate_name, $teammate_id)));
                $messages[$player_id] = $message;
            }
            return array('team_game' => true, 'messages' => $messages);
        }
        return array('team_game' => false);
    }

    function argRelicPlayerTurn()
    {
        $player_id = $this->innovationGameState->get('active_player');
        $relic = self::getCardInfo($this->innovationGameState->get('relic_id'));
        return array(
            'can_seize_to_hand'         => self::canSeizeRelicToHand($relic, $player_id),
            'can_seize_to_achievements' => self::canSeizeRelicToAchievements($relic, $player_id),
            'relic_id'                  => $relic['id'],
            'relic_name'                => self::getNotificationArgsForCardList(array($relic)),
        );
    }

    function canSeizeRelicToHand($relic, $player_id)
    {
        return self::relicSetIsInUse($relic) && ($relic['location'] == 'achievements' || $relic['location'] == 'relics');
    }

    function canSeizeRelicToAchievements($relic, $player_id)
    {
        return $relic['location'] == 'relics' || ($relic['location'] == 'achievements' && $relic['owner'] != $player_id);
    }

    /* Returns whether the relic's set is being used for this game. */
    function relicSetIsInUse($relic)
    {
        switch ($relic['type']) {
            // Base set
            case 0:
                return true;
            case 1:
                return $this->innovationGameState->artifactsExpansionEnabled();
            case 2:
                return $this->innovationGameState->citiesExpansionEnabled();
            case 3:
                return $this->innovationGameState->echoesExpansionEnabled();
            // TODO(FIGURES): Add another case when we implement this expansion.
            default:
                return false;
        }
    }

    function argPlayerArtifactTurn()
    {
        $player_id = $this->innovationGameState->get('active_player');
        $card = self::getArtifactOnDisplay($player_id);
        $effect_info = [];
        if ($card['dogma_icon']) {
            $effect_info[$card['id']] = self::getDogmaEffectInfo($card, $player_id, /*is_on_display=*/ true);
        }
        return ['_private' => ['active' => ["dogma_effect_info" => $effect_info]]];
    }

    function argPromoteCardPlayerTurn()
    {
        $player_id = $this->innovationGameState->get('active_player');
        $must = $this->innovationGameState->usingFourthEditionRules() ? clienttranslate('must') : clienttranslate('may');
        return [
            "max_age_to_promote" => self::getCardInfo($this->innovationGameState->get('melded_card_id'))['age'],
            'message_for_player' => [
                'i18n' => ['log'],
                'log'  => clienttranslate('${You} ${must} choose a card to promote from your forecast'),
                'args' => ['i18n' => ['must'], 'You' => 'You', 'must' => $must],
            ],
            'message_for_others' => [
                'i18n' => ['log'],
                'log'  => clienttranslate('${player_name} ${must} choose a card to promote from your forecast'),
                'args' => ['i18n' => ['must'], 'player_name' => self::renderPlayerName($player_id), 'must' => $must],
            ],
        ];
    }

    function argDogmaPromotedCardPlayerTurn()
    {
        return [
            "promoted_card_id" => $this->innovationGameState->get('melded_card_id'),
        ];
    }

    function argPlayerTurn()
    {
        $player_id = $this->innovationGameState->get('active_player');
        // In the 4th edition, a card can be returned in order to dogma a top card on a non-adjacent player's board
        $non_adjacent_player_ids = self::countCardsInHand($player_id) > 0 ? self::getPlayerIdsAffectedByDistanceRule($player_id) : [];
        $age_to_draw = self::getAgeToDrawIn($player_id);
        return array(
            'i18n'                                  => array('qualified_action'),
            'action_number'                         => $this->innovationGameState->get('first_player_with_only_one_action') || $this->innovationGameState->get('second_player_with_only_one_action') || $this->innovationGameState->get('has_second_action') ? 1 : 2,

            'qualified_action'                      => $this->innovationGameState->get('first_player_with_only_one_action') || $this->innovationGameState->get('second_player_with_only_one_action') ? clienttranslate('a single action') :
                ($this->innovationGameState->get('has_second_action') ? clienttranslate('a first action') : clienttranslate('a second action')),
            'age_to_draw'                           => $age_to_draw,
            'type_to_draw'                          => self::getCardTypeToDraw($age_to_draw, $player_id),
            'claimable_standard_achievement_values' => self::getClaimableStandardAchievementValues($player_id),
            'claimable_secret_values'               => self::getClaimableSecretValues($player_id),
            'city_draw_falls_back_to_other_type'    => $age_to_draw > 11 ? false : self::countCardsInLocationKeyedByAge(0, 'deck', CardTypes::CITIES)[$age_to_draw] == 0,
            '_private'                              => array(
                'active' => array(
                    // "Active" player only
                    "non_adjacent_player_ids" => $non_adjacent_player_ids,
                    "dogma_effect_info"       => self::getDogmaEffectInfoOfTopCards($player_id, $non_adjacent_player_ids),
                    "meld_info"               => self::getMeldInfo($player_id),
                )
            )
        );
    }

    function getMeldInfo($player_id)
    {
        $info_by_card_id = array();

        // Get list iof cards which can be melded right now
        $cards_which_can_be_melded = self::getCardsInLocation($player_id, 'hand');
        if ($this->innovationGameState->usingFourthEditionRules()) {
            foreach (self::getCardsInLocation($player_id, Locations::MUSEUMS) as $card) {
                if ($card['color'] !== null) { // The museums cannot be melded (only the artifacts)
                    $cards_which_can_be_melded[] = $card;
                }
            }
        } else {
            foreach (self::getCardsInLocation($player_id, Locations::DISPLAY) as $card) {
                $cards_which_can_be_melded[] = $card;
            }
        }


        // Identify which cards will trigger a City draw when melded
        $cities_expansion_enabled = $this->innovationGameState->citiesExpansionEnabled();
        $pile_size_counts = self::countCardsInLocationKeyedByColor($player_id, 'board');
        $num_cities_in_hand = self::countCardsInLocation($player_id, 'hand', CardTypes::CITIES);
        foreach ($cards_which_can_be_melded as $card) {
            $no_cities_in_hand_after_meld = $num_cities_in_hand == 0 || ($num_cities_in_hand == 1 && $card['type'] == 2);
            $info_by_card_id[$card['id']] = [
                'triggers_city_draw' => $cities_expansion_enabled && $pile_size_counts[$card['color']] == 0 && $no_cities_in_hand_after_meld,
            ];
        }

        return $info_by_card_id;
    }

    /** Returns the values of the standard achievements that are currently claimable */
    function getClaimableStandardAchievementValues($player_id): array
    {
        $values = [];
        $unclaimed_achievement_count = self::countCardsInLocationKeyedByAge(0, 'achievements');
        foreach (self::getClaimableValuesIgnoringAvailability($player_id) as $age) {
            if ($unclaimed_achievement_count[$age] > 0) {
                $values[] = $age;
            }
        }
        return $values;
    }

    /** Returns the values of the secrets that are currently claimable */
    function getClaimableSecretValues($player_id)
    {
        $values = [];
        $unclaimed_secret_count = self::countCardsInLocationKeyedByAge($player_id, 'safe');
        foreach (self::getClaimableValuesIgnoringAvailability($player_id) as $age) {
            if ($unclaimed_secret_count[$age] > 0) {
                $values[] = $age;
            }
        }
        return $values;
    }

    /** Returns the values that would be claimable (ignoring whether they actually exist in the standard achievements pile) */
    function getClaimableValuesIgnoringAvailability($player_id, $score_multiplier = 1)
    {
        $age_max = self::getMaxAgeOnBoardTopCards($player_id);
        $player_score = self::getPlayerScore($player_id) * $score_multiplier;
        $claimed_achievement_count = self::countCardsInLocationKeyedByAge($player_id, 'achievements', $type = null, $is_relic = false);

        $claimable_ages = array();
        for ($age = 1; $age <= 11; $age++) {
            // Rule: to achieve the age X, the player has to have a top card of his board of age >= X and 5*X points in his score pile
            if ($age <= $age_max && $player_score >= 5 * $age * ($claimed_achievement_count[$age] + 1)) {
                $claimable_ages[] = $age;
            }
        }
        return $claimable_ages;
    }

    /** Returns dogma effect information about the top cards belonging to the specified player or non-adjacent players. */
    function getDogmaEffectInfoOfTopCards($launcher_id, $non_adjacent_player_ids = [])
    {
        $dogma_effect_info = array();
        foreach (self::getTopCardsOnBoard($launcher_id) as $top_card) {
            if ($top_card['dogma_icon']) {
                $dogma_effect_info[$top_card['id']] = self::getDogmaEffectInfo($top_card, $launcher_id);
            }
        }
        foreach ($non_adjacent_player_ids as $player_id) {
            foreach (self::getTopCardsOnBoard($player_id) as $top_card) {
                if ($top_card['dogma_icon']) {
                    $dogma_effect_info[$top_card['id']] = self::getDogmaEffectInfo($top_card, $launcher_id);
                }
            }
        }
        return $dogma_effect_info;
    }

    /** Returns dogma effect information of the specified card. */
    function getDogmaEffectInfo($card, $launcher_id, $is_on_display = false)
    {
        $dogma_effect_info = array();

        $dogma_icon = $card['dogma_icon'];
        $resource_column = 'player_icon_count_' . $dogma_icon;
        $extra_icons = $is_on_display ? self::countIconsOnCard($card, $dogma_icon) : 0;

        $players_executing_i_compel_effects = [];
        $players_executing_i_demand_effects = [];
        $players_executing_non_demand_effects = [];
        $players_executing_echo_effects = [];

        if (self::getCompelEffect($card['id'])) {
            $players_executing_i_compel_effects =
                self::getObjectListFromDB(self::format("
                    SELECT
                        player_id
                    FROM
                        player
                    WHERE
                        {col} >= {extra_icons} + (SELECT {col} FROM player WHERE player_id = {launcher_id})
                        AND player_team <> (SELECT player_team FROM player WHERE player_id = {launcher_id})
                        AND player_eliminated = 0
                ", array('col' => $resource_column, 'launcher_id' => $launcher_id, 'extra_icons' => $extra_icons)), true);
        } else if (self::getDemandEffect($card['id'])) {
            $players_executing_i_demand_effects =
                self::getObjectListFromDB(self::format("
                        SELECT
                            player_id
                        FROM
                            player
                        WHERE
                            {col} < {extra_icons} + (SELECT {col} FROM player WHERE player_id = {launcher_id})
                            AND player_team <> (SELECT player_team FROM player WHERE player_id = {launcher_id})
                            AND player_eliminated = 0
                    ", array('col' => $resource_column, 'launcher_id' => $launcher_id, 'extra_icons' => $extra_icons)), true);
        }

        // Identify opponents affected by the 4th edition distance rule which have an empty hand, since they means they cannot share.
        $opponents_which_cannot_afford_to_share = [];
        $distance_rule_condition = "";
        // NOTE: Colt Paterson Revolver and Battleship Bismarck are exceptions since the compel effect causes the player to draw cards before
        // they would need to return a card in order to share.
        if ($card['id'] != 181 && $card['id'] != 187) {
            foreach (self::getPlayerIdsAffectedByDistanceRule($launcher_id) as $opponent_id) {
                if (self::countCardsInHand($opponent_id) == 0) {
                    $opponents_which_cannot_afford_to_share[] = $opponent_id;
                }
            }
            if (count($opponents_which_cannot_afford_to_share) > 0) {
                $distance_rule_condition = self::format("AND player_id NOT IN ({player_ids})", array("player_ids" => join(',', $opponents_which_cannot_afford_to_share)));
            }
        }

        // NOTE: We slightly abuse the term "sharing" here since the following can include a player's teammate (even though
        // that wouldn't trigger a sharing bonus)
        $sharing_players =
            self::getObjectListFromDB(self::format("
                    SELECT
                        player_id
                    FROM
                        player
                    WHERE
                        player_id = {launcher_id} OR {col} >= {extra_icons} + (SELECT {col} FROM player WHERE player_id = {launcher_id})
                        AND player_eliminated = 0
                        {distance_rule_condition}
                ", array('col' => $resource_column, 'launcher_id' => $launcher_id, 'extra_icons' => $extra_icons, 'distance_rule_condition' => $distance_rule_condition)), true);
        $card_ids_with_visible_echo_effects = self::getCardIdsWithVisibleEchoEffects($card);
        $dogma_effect_info['num_echo_effects'] = count($card_ids_with_visible_echo_effects);
        if (self::getNonDemandEffect($card['id'], 1) !== null) {
            $players_executing_non_demand_effects = $sharing_players;
        }
        if ($dogma_effect_info['num_echo_effects'] > 0) {
            $players_executing_echo_effects = $sharing_players;
        }

        // NOTE: No-op detection is best effort. If in doubt, we assume it will have an effect.
        $players_with_no_effect = [];
        $effective_sharing_players = [];
        $active_players = self::getAllActivePlayerIds();
        foreach ($active_players as $player_id) {
            $no_effect = true;
            if (in_array($player_id, $players_executing_non_demand_effects) || in_array($player_id, $players_executing_echo_effects)) {
                $no_effect = $no_effect && self::sharingHasNoEffect($card, $launcher_id, $player_id, $card_ids_with_visible_echo_effects);
                if (!$no_effect) {
                    $effective_sharing_players[] = $player_id;
                }
            }
            if (in_array($player_id, $players_executing_i_compel_effects)) {
                $no_effect = $no_effect && self::compelHasNoEffect($card, $launcher_id, $player_id);
            }
            if (in_array($player_id, $players_executing_i_demand_effects)) {
                $no_effect = $no_effect && self::demandHasNoEffect($card, $launcher_id, $player_id);
            }
            if ($no_effect) {
                $players_with_no_effect[] = $player_id;
            }
        }

        $dogma_effect_info['players_executing_i_demand_effects'] = $players_executing_i_demand_effects;
        $dogma_effect_info['players_executing_i_compel_effects'] = $players_executing_i_compel_effects;
        $dogma_effect_info['players_executing_non_demand_effects'] = $players_executing_non_demand_effects;
        $dogma_effect_info['players_executing_echo_effects'] = $players_executing_echo_effects;
        $dogma_effect_info['sharing_players'] = $effective_sharing_players;
        $dogma_effect_info['no_effect'] = count($players_with_no_effect) == count($active_players);
        $dogma_effect_info['on_non_adjacent_board'] = $card['owner'] != $launcher_id;

        if ($this->innovationGameState->get('endorse_action_state') == 1 && !$is_on_display) {
            $max_age_for_endorse_payment = self::getMaxAgeForEndorsePayment($card);
            $can_endorse = false;
            if ($max_age_for_endorse_payment != null) {
                foreach (self::getCardsInHand($launcher_id) as $card_in_hand) {
                    if ($card_in_hand['age'] <= $max_age_for_endorse_payment) {
                        $can_endorse = true;
                        break;
                    }
                }
            }
            if ($can_endorse) {
                $dogma_effect_info['max_age_for_endorse_payment'] = $max_age_for_endorse_payment;
            }
        }

        return $dogma_effect_info;
    }

    /** Returns the maximum age card that can be tucked/junked in order to endorse the card, or null if there are no City cards matching the featured icon. */
    function getMaxAgeForEndorsePayment($card)
    {
        // Battleship Yamato does not have any icons on it so it cannot be executed
        $dogma_icon = $card['dogma_icon'];
        if ($dogma_icon == null) {
            return null;
        }
        // To take an Endorse action, perform the following steps:
        // 1) Choose the top card on your board that you want to Endorse, and note its featured icon.
        // 2) Choose a top city on your board. It must have the featured icon on it.
        // 3) Pay for the Endorse action by tucking a card from your hand of equal or lower value to
        //    the city you chose. The tucked card’s color and icons are irrelevant.
        $highest_city_with_featured_icon = null;
        foreach (self::getTopCardsOnBoard($card['owner']) as $top_card) {
            if ($top_card['type'] == 2 && self::hasRessource($top_card, $dogma_icon)) {
                if ($highest_city_with_featured_icon == null || $top_card['age'] > $highest_city_with_featured_icon) {
                    $highest_city_with_featured_icon = $top_card['age'];
                }
            }
        }
        return $highest_city_with_featured_icon;
    }

    /** Returns true if the dogma is guaranteed to have no effect when the specified player executes the non-demand and echo effects (without revealing hidden info to the launching player). */
    function sharingHasNoEffect($card, $launcher_id, $executing_player_id, $card_ids_with_visible_echo_effects)
    {
        $card_id = $card['id'];

        // Check all echo effects that will be executed
        foreach ($card_ids_with_visible_echo_effects as $card_id) {
            if (self::isInSeparateFile($card_id)) {
                $executionState = (new ExecutionState($this))
                    ->setEdition($this->innovationGameState->getEdition())
                    ->setLauncherId($launcher_id)
                    ->setPlayerId($executing_player_id);
                if (self::getCardInstance($card_id, $executionState)->echoMightBeEffective()) {
                    return false;
                }
            } else {
                // Otherwise, we have to assume it has an effect
                return false;
            }
        }

        // Many cards do not have a non-demand effect on them
        if (self::getNonDemandEffect($card['id'], 1) == null) {
            return true;
        }

        if (self::isInSeparateFile($card_id)) {
            $executionState = (new ExecutionState($this))
                ->setEdition($this->innovationGameState->getEdition())
                ->setLauncherId($launcher_id)
                ->setPlayerId($executing_player_id);
            return !self::getCardInstance($card_id, $executionState)->nonDemandsMightBeEffective();
        }

        // Otherwise, we assume the non-demand effect(s) will have an effect
        return false;
    }

    /** Returns true if the dogma is guaranteed to have no effect when the specified player executes the demand effect (without revealing hidden info to the launching player). */
    function demandHasNoEffect($card, $launcher_id, $executing_player_id)
    {
        $card_id = $card['id'];

        // Many cards do not have a demand effect on them
        if (self::getDemandEffect($card['id']) === null) {
            return true;
        }

        if (self::isInSeparateFile($card_id)) {
            $executionState = (new ExecutionState($this))
                ->setEdition($this->innovationGameState->getEdition())
                ->setLauncherId($launcher_id)
                ->setPlayerId($executing_player_id);
            return !self::getCardInstance($card_id, $executionState)->demandMightBeEffective();
        }

        // Otherwise, we assume the demand effect will have an effect
        return false;
    }

    /** Returns true if the dogma is guaranteed to have no effect when the specified player executes the compel effect (without revealing hidden info to the launching player). */
    function compelHasNoEffect($card, $launcher_id, $executing_player_id)
    {
        $card_id = $card['id'];

        // Many cards do not have a compel effect on them
        if (self::getCompelEffect($card_id) === null) {
            return true;
        }

        if (self::isInSeparateFile($card_id)) {
            $executionState = (new ExecutionState($this))
                ->setEdition($this->innovationGameState->getEdition())
                ->setLauncherId($launcher_id)
                ->setPlayerId($executing_player_id);
            return !self::getCardInstance($card_id, $executionState)->compelMightBeEffective();
        }

        // Otherwise, we assume the compel effect will have an effect
        return false;
    }

    function argDogmaEffect()
    {
        return self::getArgForDogmaEffect();
    }

    function argInterDogmaEffect()
    {
        return self::getArgForDogmaEffect();
    }

    function argPlayerInvolvedTurn()
    {
        return self::getArgForPlayerUnderDogmaEffect();
    }

    function argInterPlayerInvolvedTurn()
    {
        return self::getArgForPlayerUnderDogmaEffect();
    }

    function argInteractionStep()
    {
        return self::getArgForPlayerUnderDogmaEffect();
    }

    function argInterInteractionStep()
    {
        return self::getArgForPlayerUnderDogmaEffect();
    }

    function argPreSelectionMove()
    {
        return self::getArgForPlayerUnderDogmaEffect();
    }

    function argInterSelectionMove()
    {
        return self::getArgForPlayerUnderDogmaEffect();
    }

    function argSelectionMove()
    {
        $player_id = self::getActivePlayerId();
        $player_name = self::renderPlayerName($player_id);
        $special_type_of_choice = $this->innovationGameState->get('special_type_of_choice');

        $nested_card_state = self::getCurrentNestedCardState();

        // There won't be any nested card state if a player is doing an interaction outside of the context of a dogma action
        if ($nested_card_state == null) {
            $card_id = null;
            $code = null;
            $current_effect_type = -1;
            $current_effect_number = -1;
        } else {
            $card_id = $nested_card_state['card_id'];
            $current_effect_type = $nested_card_state['current_effect_type'];
            $current_effect_number = $nested_card_state['current_effect_number'];
            // Echo effects are sometimes executed on cards other than the card being dogma'd
            if ($current_effect_type == 3) {
                $nesting_index = $nested_card_state['nesting_index'];
                $card_id = self::getUniqueValueFromDB(self::format("SELECT card_id FROM echo_execution WHERE nesting_index = {nesting_index} AND execution_index = {effect_number}", array('nesting_index' => $nesting_index, 'effect_number' => $current_effect_number)));
            }
            $step = self::getStep();
            $code = self::getCardExecutionCodeWithLetter($card_id, $current_effect_type, $current_effect_number, $step);
        }

        $can_pass = $this->innovationGameState->get('can_pass') == 1;
        $can_stop = $this->innovationGameState->get('n_min') <= 0;

        if ($special_type_of_choice > 0) {
            switch (self::decodeSpecialTypeOfChoice($special_type_of_choice)) {
                case 'choose_from_list':
                    // See the card
                    break;
                case 'choose_value':
                    $options = array();
                    foreach ($this->innovationGameState->getAsArray('age_array') as $age) {
                        $options[] = array('value' => $age, 'text' => self::getAgeSquare($age));
                    }
                    break;
                case 'choose_non_negative_integer':
                    // Nothing
                    $options = null;
                    break;
                case 'choose_color':
                case 'choose_two_colors':
                case 'choose_three_colors':
                    $options = array();
                    foreach ($this->innovationGameState->getAsArray('color_array') as $color) {
                        $options[] = array('value' => $color, 'text' => Colors::render($color));
                    }
                    break;
                case 'choose_player':
                    $options = self::getObjectListFromDB(
                        self::format("
                    SELECT
                        player_id AS value,
                        player_name AS text
                    FROM
                        player
                    WHERE
                        player_index IN ({player_indexes})
                ",
                            array('player_indexes' => join(',', $this->innovationGameState->getAsArray('player_array')))
                        )
                    );
                    break;
                case 'choose_special_achievement':
                    // Nothing
                    $options = null;
                    break;
                case 'choose_rearrange':
                    // Nothing
                    $options = null;
                    break;
                case 'choose_yes_or_no':
                    // See the card
                    break;
                case 'choose_type':
                    $options = array();
                    foreach ($this->innovationGameState->getAsArray('type_array') as $type) {
                        $options[] = array('value' => $type, 'text' => CardTypes::render($type));
                    }
                    break;
                case 'choose_icon_type':
                    $options = array();
                    foreach ($this->innovationGameState->getAsArray('icon_array') as $icon) {
                        $options[] = array('value' => $icon, 'text' => Icons::render($icon));
                    }
                    break;
                default:
                    break;
            }

            // The message to display is specific of the card
            $message_args_for_player = array('You' => 'You', 'you' => 'you');
            $message_args_for_others = array('player_name' => $player_name);

            if ($card_id === null) { // Digging/stealing artifact
                $message_for_player = clienttranslate('${You} must make a choice');
                $message_for_others = clienttranslate('${player_name} must choose a card to dig or an artifact to rotate into a museum');
                $card_ids = self::getAuxiliaryArray();
                $options = [
                    [
                        'value' => 0,
                        'text'  => clienttranslate('Dig from ${age} deck'),
                        'age'   => self::getAgeSquareWithType(self::getCardInfo($card_ids[0])['age'], CardTypes::ARTIFACTS),
                    ],
                ];
                for ($i = 1; $i < count($card_ids); $i++) {
                    $options[] = [
                        'value' => $i,
                        'text'  => clienttranslate('Rotate ${card} into a museum'),
                        'card'  => $this->getNotificationArgsForCardList([self::getCardInfo($card_ids[$i])]),
                    ];
                }
            } else {
                $executionState = (new ExecutionState($this))
                    ->setEdition($this->innovationGameState->getEdition())
                    ->setLauncherId($nested_card_state['launcher_id'])
                    ->setPlayerId($player_id)
                    ->setEffectType($current_effect_type)
                    ->setEffectNumber($current_effect_number)
                    ->setCurrentStep(self::getStep())
                    ->setMaxSteps(self::getStepMax());
                $prompt = self::getCardInstance($card_id, $executionState)->getSpecialChoicePrompt();
                $message_for_player = $prompt['message_for_player'];
                $message_for_others = $prompt['message_for_others'];
                if (array_key_exists('options', $prompt)) {
                    $options = $prompt['options'];
                }
            }

            $card_names = self::getDogmaCardNames();

            $args = array_merge(
                array(
                    // Public info
                    'card_name'              => 'card_name',
                    'special_type_of_choice' => $special_type_of_choice,
                    'options'                => $options,
                    'can_pass'               => $can_pass,
                    'can_stop'               => false,
                    'opponent_id'            => null,
                    'splay_direction'        => null,
                    'color_pile'             => null,
                    'message_for_player'     => array('i18n' => array('log'), 'log' => $message_for_player, 'args' => $message_args_for_player),
                    'message_for_others'     => array('i18n' => array('log'), 'log' => $message_for_others, 'args' => $message_args_for_others),
                    'player_name'            => $player_name
                ),
                $card_names
            );

            if ($special_type_of_choice == 11 /* choose_non_negative_integer */) {
                $args['default_integer'] = self::getAuxiliaryValue();
            } else if ($special_type_of_choice == 13 /* choose_special_achievement */) {
                $available_ids = [];
                foreach (self::getCardsInLocation(0, 'achievements') as $card) {
                    if ($card['age'] === null && $card['id'] < 1000) {
                        $available_ids[] = $card['id'];
                    }
                }
                $args['available_special_achievements'] = $available_ids;
                $junked_ids = [];
                foreach (self::getCardsInLocation(0, 'junk') as $card) {
                    if ($card['age'] === null && $card['id'] < 1000) {
                        $junked_ids[] = $card['id'];
                    }
                }
                $args['junked_special_achievements'] = $junked_ids;
            }

            return $args;
        }

        $splay_direction = $this->innovationGameState->get('splay_direction');
        $n_min = $this->innovationGameState->get("n_min");
        $n_max = $this->innovationGameState->get("n_max");
        $n = $this->innovationGameState->get("n");
        $owner_from = $this->innovationGameState->get("owner_from");
        if ($splay_direction == -1) {
            $location_from = Locations::decode($this->innovationGameState->get("location_from"));
            $bottom_from = $this->innovationGameState->get("bottom_from");
            $owner_to = $this->innovationGameState->get("owner_to");
            $location_to = Locations::decode($this->innovationGameState->get("location_to"));
            $bottom_to = $this->innovationGameState->get("bottom_to");
            $age_min = $this->innovationGameState->get("age_min");
            $age_max = $this->innovationGameState->get("age_max");
            $with_icons = $this->innovationGameState->getAsArray("with_icons");
            $without_icons = $this->innovationGameState->getAsArray("without_icons");
            $with_demand_effect = $this->innovationGameState->get("has_demand_effect");
            $score_keyword = $this->innovationGameState->get("score_keyword");
            $meld_keyword = $this->innovationGameState->get("meld_keyword");
            $achieve_keyword = $this->innovationGameState->get("achieve_keyword");
        }

        // Number of cards
        if ($n_min <= 0) {
            $n_min = 1;
        }

        $opponent_id = null;
        if ($splay_direction == -1) {
            // Identification of the potential opponent(s)
            if ($owner_from == -2 || $owner_from == -3 || $owner_from == -4) {
                $opponent_id = $owner_from;
            } else if ($owner_to == -2 || $owner_to == -3 || $owner_to == -4) {
                $opponent_id = $owner_to;
            } else if ($owner_from > 0 && $owner_from <> $player_id) {
                $opponent_id = $owner_from;
            } else if ($owner_to > 0 && $owner_to <> $player_id) {
                $opponent_id = $owner_to;
            }

            $player_id_is_owner_from = $owner_from == $player_id;
            $player_id_is_owner_to = $owner_to == $player_id;

            $opponent_id_is_owner_from = $owner_from == $opponent_id;
            $opponent_id_is_owner_to = $owner_to == $opponent_id;
        }

        if ($opponent_id === null) {
            $your = null;
            $opponent_name = null;
        } else if ($opponent_id > 0) {
            $your = 'your';
            $opponent_name = self::renderPlayerName($opponent_id);
        } else if ($opponent_id == -2) {
            $your = null;
            if ($n_min > 800) {
                $opponent_name = clienttranslate("all players");
            } else {
                $opponent_name = clienttranslate("any player");
            }
        } else if ($opponent_id == -3) {
            $your = null;
            if ($n_min > 800) {
                $opponent_name = clienttranslate("all opponents");
            } else {
                $opponent_name = clienttranslate("any opponent");
            }
        } else { // opponent_id == -4
            $your = null;
            if ($n_min > 800) {
                $opponent_name = clienttranslate("all other players");
            } else {
                $opponent_name = clienttranslate("any other player");
            }
        }

        // Action to be done
        if ($n == 0) {
            if ($can_pass || $can_stop) {
                $you_must = clienttranslate('${You} may');
                $player_must = clienttranslate('${player_name} may');
            } else {
                $you_must = clienttranslate('${You} must');
                $player_must = clienttranslate('${player_name} must');
            }
        } else {
            if ($can_pass || $can_stop) {
                $you_must = clienttranslate('${You} still may');
                $player_must = clienttranslate('${player_name} still may');
            } else {
                $you_must = clienttranslate('${You} still must');
                $player_must = clienttranslate('${player_name} still must');
            }
        }

        // Number of cards
        $number = self::getRecursivelyTranslatedNumberRange($n_min, $n_max);

        if ($splay_direction == -1) {
            $cards = self::getRecursivelyTranslatedCardSelection($age_min, $age_max, $with_icons, $without_icons, $with_demand_effect);
        } else { // splay_direction <> -1
            $splayable_colors = $this->innovationGameState->getAsArray('color_array');
            $splayable_colors_in_clear = array();
            foreach ($splayable_colors as $color) {
                $splayable_colors_in_clear[] = self::renderColorCards($color);
            }
        }

        // Creation of the message
        if ($opponent_name === null || $opponent_id == -2 || $opponent_id == -3 || $opponent_id == -4) {
            if ($splay_direction == -1) {
                $messages = self::getTransferInfoWithOnePlayerInvolved($owner_from, $location_from, $location_to, $player_id_is_owner_from, $player_id_is_owner_to, $bottom_from, $bottom_to, $score_keyword, $meld_keyword, $achieve_keyword, $you_must, $player_must, $player_name, $number, $cards, $opponent_name, $code);
                $splay_direction = null;
                $splay_direction_in_clear = null;
            } else {
                $messages = [
                    'message_for_player'        => ['i18n' => ['log'], 'log' => $you_must, 'args' => ['You' => 'You']],
                    'message_for_others'        => ['i18n' => ['log'], 'log' => $player_must, 'args' => ['player_name' => $player_name]],
                    'splayable_colors'          => $splayable_colors,
                    'splayable_colors_in_clear' => $splayable_colors_in_clear,
                ];
                $splay_direction_in_clear = Directions::render($splay_direction);
            }
        } else {
            $messages = self::getTransferInfoWithTwoPlayersInvolved($location_from, $location_to, $player_id_is_owner_from, $player_id_is_owner_to, $opponent_id_is_owner_from, $opponent_id_is_owner_to, $bottom_from, $bottom_to, $score_keyword, $meld_keyword, $you_must, $player_must, $your, $player_name, $opponent_name, $number, $cards);
            $splay_direction = null;
            $splay_direction_in_clear = null;
        }

        $must_show_score = false;
        if ($special_type_of_choice == 0 && $splay_direction === null && $location_from == 'score') {
            if ($owner_from == $player_id) {
                $must_show_score = true;
            } else if ($owner_from == -2) {
                $visible_cards = self::getVisibleSelectedCards($player_id);
                foreach ($visible_cards as $card) {
                    if ($card['owner'] == $player_id && $card['location'] == 'score') {
                        $must_show_score = true;
                        break;
                    }
                }
            }
        }

        $must_show_forecast = false;
        if ($special_type_of_choice == 0 && $splay_direction === null && $location_from == 'forecast') {
            if ($owner_from == $player_id) {
                $must_show_forecast = true;
            } else if ($owner_from == -2) {
                $visible_cards = self::getVisibleSelectedCards($player_id);
                foreach ($visible_cards as $card) {
                    if ($card['owner'] == $player_id && $card['location'] == 'forecast') {
                        $must_show_forecast = true;
                        break;
                    }
                }
            }
        }

        $must_show_junk = false;
        if ($special_type_of_choice == 0 && $splay_direction === null && $location_from == 'junk') {
            $must_show_junk = true;
        }

        $card_names = self::getDogmaCardNames();

        $args = array_merge(
            $messages,
            $card_names,
            array(
                // Public info
                'card_name'                => 'card_name',
                'special_type_of_choice'   => $special_type_of_choice,
                'can_pass'                 => $can_pass,
                'can_stop'                 => $can_stop,
                'opponent_id'              => $opponent_id,
                'splay_direction'          => $splay_direction,
                'splay_direction_in_clear' => $splay_direction_in_clear,
                'color_pile'               => $splay_direction === null && ($location_from == 'pile' || $location_from == Locations::PILE_OR_SCORE) ? $this->innovationGameState->getAsArray('color_array')[0] : null,
                'card_interaction'         => $code,
                'num_cards_already_chosen' => $n,

                // Private info
                '_private'                 => array(
                    'active' => array(
                        // "Active" player only
                        "visible_selectable_cards" => self::getVisibleSelectedCards($player_id),
                        "selectable_rectos"        => self::getSelectableRectos($player_id),
                        // Most of the time, the player choose among versos he can see this array is empty so this array is empty except for few dogma effects
                        "must_show_score"          => $must_show_score,
                        "must_show_forecast"       => $must_show_forecast,
                        "must_show_junk"           => $must_show_junk,
                        "show_all_cards_on_board"  => $special_type_of_choice == 0 && ($splay_direction == -1 || $splay_direction === null) && $location_from == 'board' && $bottom_from == 1,
                    )
                )
            )
        );

        $args['i18n'][] = 'card_name';
        $args['i18n'][] = 'splay_direction_in_clear';
        $args['i18n'][] = 'splayable_colors_in_clear';

        return $args;
    }

    function getRecursivelyTranslatedNumberRange($n_min, $n_max)
    {
        if ($n_min > 800) {
            $number_log = clienttranslate("all the");
        } else if ($n_max > 800) {
            $number_log = clienttranslate("any number of");
        } else if ($n_min == $n_max) {
            $number_log = '${n_min}';
        } else if ($n_min + 1 == $n_max) {
            $number_log = clienttranslate('${n_min} or ${n_max}');
        } else {
            $number_log = clienttranslate('${n_min} to ${n_max}');
        }
        return [
            'i18n' => ['log'],
            'log'  => $number_log,
            'args' => [
                'i18n'  => ['n_min', 'n_max'],
                'n_min' => self::renderNumber($n_min),
                'n_max' => self::renderNumber($n_max),
            ],
        ];
    }

    function getRecursivelyTranslatedCardSelection($age_min, $age_max, $with_icons, $without_icons, $with_demand_effect)
    {
        $card_args = array();

        $selectable_colors = $this->innovationGameState->getAsArray('color_array');
        if (count($selectable_colors) < 5) {
            $colors = self::getRecursivelyTranslatedColorList($selectable_colors);
            $card_log = clienttranslate('${color} ${qualifier}${cards}${of_age}${with_icon}${with_demand}');
            $card_args['color'] = $colors;
            $card_args['i18n'] = ['color', 'qualifier', 'cards', 'of_age', 'with_icon', 'with_demand'];
        } else {
            $card_log = clienttranslate('${qualifier}${cards}${of_age}${with_icon}${with_demand}');
            $card_args['i18n'] = ['qualifier', 'cards', 'of_age', 'with_icon', 'with_demand'];
        }
        $card_args['qualifier'] = '';
        $card_args['cards'] = clienttranslate('card(s)');
        $card_args['of_age'] = '';
        $card_args['with_icon'] = '';
        $card_args['with_demand'] = '';

        if ($age_min == ValueSelectors::HIGHEST && $age_max == ValueSelectors::HIGHEST) {
            $card_args['qualifier'] = clienttranslate('highest ');
        } else if ($age_min == ValueSelectors::LOWEST && $age_max == ValueSelectors::LOWEST) {
            $card_args['qualifier'] = clienttranslate('lowest ');
        } else if ($age_min != 1 || $age_max != 11) {
            if ($age_min == $age_max) {
                $of_age_log = clienttranslate(' of value ${<}${age_min}${>}');
            } else if ($age_min + 1 == $age_max) {
                $of_age_log = clienttranslate(' of value ${<}${age_min}${>} or ${<}${age_max}${>}');
            } else {
                $of_age_log = clienttranslate(' of value ${<}${age_min}${>} to ${<}${age_max}${>}');
            }
            $card_args['of_age'] = [
                'i18n' => ['log'],
                'log'  => $of_age_log,
                'args' => array_merge(self::getDelimiterMeanings($of_age_log), ['age_min' => $age_min, 'age_max' => $age_max]),
            ];
        }

        if (count($with_icons) === 1) {
            $with_icon_log = clienttranslate(' with a ${[}${icon}${]}');
            $card_args['with_icon'] = [
                'i18n' => ['log'],
                'log'  => $with_icon_log,
                'args' => array_merge(self::getDelimiterMeanings($with_icon_log), ['icon' => $with_icons[0]]),
            ];
        } else if (count($with_icons) === 2) {
            $with_icon_log = clienttranslate(' with a ${[}${icon_1}${]} or a ${[}${icon_2}${]}');
            $card_args['with_icon'] = [
                'i18n' => ['log'],
                'log'  => $with_icon_log,
                'args' => array_merge(self::getDelimiterMeanings($with_icon_log), ['icon_1' => $with_icons[0], 'icon_2' => $with_icons[1]]),
            ];
        } else if (count($without_icons) === 1) {
            $without_icon_log = clienttranslate(' without a ${[}${icon}${]}');
            $card_args['with_icon'] = [
                'i18n' => ['log'],
                'log'  => $without_icon_log,
                'args' => array_merge(self::getDelimiterMeanings($without_icon_log), ['icon' => $without_icons[0]]),
            ];
        } else if (count($without_icons) === 2) {
            $without_icon_log = clienttranslate(' without a ${[}${icon_1}${]} or a ${[}${icon_2}${]}');
            $card_args['with_icon'] = [
                'i18n' => ['log'],
                'log'  => $without_icon_log,
                'args' => array_merge(self::getDelimiterMeanings($without_icon_log), ['icon_1' => $without_icons[0], 'icon_2' => $without_icons[1]]),
            ];
        }

        if ($with_demand_effect == 1) {
            $card_args['with_demand'] = clienttranslate(' with a demand effect');
        }

        return ['i18n' => ['log'], 'log' => $card_log, 'args' => $card_args];
    }

    function getRecursivelyTranslatedColorList($colors)
    {
        $color_log = "";
        $color_args = array();

        for ($i = 0; $i < count($colors); $i++) {
            $colors_in_clear[$i] = Colors::render($colors[$i]);
        }
        switch (count($colors)) {
            case 1:
                $color_log = '${color}';
                $color_args['color'] = Colors::render($colors[0]);
                $color_args['i18n'] = ['color'];
                break;

            case 2:
                $color_log = clienttranslate('${color_1} or ${color_2}');
                $color_args['color_1'] = Colors::render($colors[0]);
                $color_args['color_2'] = Colors::render($colors[1]);
                $color_args['i18n'] = ['color_1', 'color_2'];
                break;

            case 3:
                $color_log = clienttranslate('${color_1}, ${color_2} or ${color_3}');
                $color_args['color_1'] = Colors::render($colors[0]);
                $color_args['color_2'] = Colors::render($colors[1]);
                $color_args['color_3'] = Colors::render($colors[2]);
                $color_args['i18n'] = ['color_1', 'color_2', 'color_3'];
                break;

            case 4:
                $color_log = clienttranslate('non-${color}');
                foreach (Colors::ALL as $color) {
                    if (!in_array($color, $colors)) {
                        $color_args['color'] = Colors::render($color);
                        break;
                    }
                }
                $color_args['i18n'] = ['color'];
                break;
        }
        return ['i18n' => ['log'], 'log' => $color_log, 'args' => $color_args];
    }

    function stTurn0()
    {
        // All players must choose a card for initial meld
        $this->gamestate->setAllPlayersMultiactive();

        // If in debug mode, automatically choose arbitrary initial cards for other players (speeds up manual testing).
        if ($this->innovationGameState->get('debug_mode') == 1) {
            $other_player_ids = self::getObjectListFromDB("SELECT player_id FROM player WHERE player_id != (SELECT MIN(player_id) FROM player)", true);
            foreach ($other_player_ids as $player_id) {
                $card_1 = self::getCardsInHand($player_id)[0];
                $card_2 = self::getCardsInHand($player_id)[1];
                $card_id = self::comesAlphabeticallyBefore($card_1, $card_2) ? $card_2['id'] : $card_1['id'];
                self::markAsSelected($card_id);
                self::notifyPlayer($player_id, 'log', clienttranslate('${You} choose a card.'), array('You' => 'You'));
                self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} chooses a card.'), array('player_name' => self::getPlayerNameFromId($player_id)));
                $this->gamestate->setPlayerNonMultiactive($player_id, '');
            }
        }
    }

    function stWhoBegins()
    {
        $this->innovationGameState->set('turn0', 0); // End of turn 0

        if ($this->innovationGameState->unseenExpansionEnabled()) {
            self::resetWillDrawUnseenCardNext();
        }

        $cards = self::getSelectedCards();
        // Deselect the cards
        self::deselectAllCards();

        // Execute the melds planned by players
        foreach ($cards as $card) {
            $this->gamestate->changeActivePlayer($card['owner']);
            self::meldCard($card, $card['owner']);
        }

        // The first active player is the one who chose for meld the first card in (English) alphabetical order
        $earliest_card = null;
        foreach ($cards as $card) {
            if ($earliest_card === null || self::comesAlphabeticallyBefore($card, $earliest_card)) {
                $earliest_card = $card;
            }
        }
        $player_id = $earliest_card['owner'];

        $english_card_name = self::getCardName($earliest_card['id']);
        self::notifyPlayer(
            $player_id,
            'initialCardChosen',
            clienttranslate('${You} melded the first card in English alphabetical order (${english_name}): You play first.'),
            array(
                'You'          => 'You',
                'english_name' => $english_card_name,
            )
        );
        self::notifyAllPlayersBut(
            $player_id,
            'log',
            clienttranslate('${player_name} melded the first card in English alphabetical order (${english_name}): he plays first.'),
            array(
                'player_name'  => self::getPlayerNameFromId($player_id),
                'english_name' => $english_card_name,
            )
        );

        // Enter normal play loop
        $this->innovationGameState->set('active_player', $player_id);
        self::setLauncherId($player_id);
        $this->gamestate->changeActivePlayer($player_id);
        $this->innovationGameState->set('current_action_number', 1);
        self::notifyGeneralInfo('<!--empty-->');
        self::trace('turn0->playerTurn');
        $this->gamestate->nextState();
    }

    function stInterPlayerTurn()
    {
        // An action of the player has been fully resolved.

        // Move the Artifact on display if the free dogma action was used
        $player_id = self::getActivePlayerId();
        if ($this->innovationGameState->get('current_action_number') == 0) {
            if ($this->innovationGameState->usingFourthEditionRules()) {
                if (self::rotateArtifactOnDisplayIntoMuseum($player_id)) {
                    $card_ids = self::getArtifactIdsIfNoMuseumsAvailable();
                    if ($card_ids) {
                        self::setAuxiliaryArray($card_ids);
                        $options = array(
                            'player_id'                       => $player_id,
                            'n'                               => count($card_ids),
                            'owner_from'                      => 'any player',
                            'location_from'                   => Locations::MUSEUMS,
                            'owner_to'                        => 0,
                            'location_to'                     => Locations::DECK,
                            'card_ids_are_in_auxiliary_array' => true,
                        );
                        self::setSelectionRange($options);
                        self::trace('interPlayerTurn->preSelectionMove');
                        $this->gamestate->nextState('preSelectionMove');
                        return;
                    }
                }
            } else {
                self::returnCard(self::getArtifactOnDisplay($player_id));
            }
        }

        // Check for special achievements (only necessary in 4th edition)
        if ($this->innovationGameState->usingFourthEditionRules()) {
            try {
                self::checkForSpecialAchievements( /*is_end_of_action_check=*/ true);
            } catch (EndOfGame $e) {
                // End of the game: the exception has reached the highest level of code
                self::trace('EOG bubbled from self::stInterPlayerTurn');
                self::trace('interPlayerTurn->justBeforeGameEnd');
                $this->gamestate->nextState('justBeforeGameEnd');
                return;
            }
        }

        // Reset the counter used to track the cards returned by each player via Democracy during the action.
        self::DbQuery("UPDATE player SET democracy_counter = 0");

        // Give him extra time for his actions to come
        self::giveExtraTime(self::getActivePlayerId());

        // Does he play again?
        if ($this->innovationGameState->get('current_action_number') == 0) {
            $next_player = false;
        } else if ($this->innovationGameState->get('first_player_with_only_one_action')) {
            // First turn: the player had only one action to make
            $next_player = true;
            $this->innovationGameState->set('first_player_with_only_one_action', 0);
        } else if ($this->innovationGameState->get('second_player_with_only_one_action')) {
            // 4 players at least and this is the second turn: the player had only one action to make
            $next_player = true;
            $this->innovationGameState->set('second_player_with_only_one_action', 0);
        } else if ($this->innovationGameState->get('has_second_action')) {
            // The player took his first action and has another one
            $next_player = false;
            $this->innovationGameState->set('has_second_action', 0);
            if ($this->innovationGameState->get('endorse_action_state') >= 2) {
                $this->innovationGameState->set('endorse_action_state', 0);
            }
        } else {
            // The player took his second action
            $next_player = true;
            $this->innovationGameState->set('has_second_action', 1);
        }
        if ($next_player) { // The turn for the current player is over
            if (!$this->innovationGameState->usingFirstEditionRules()) {
                self::resetFlagsForMonument();
            }

            if ($this->innovationGameState->citiesExpansionEnabled()) {
                $this->innovationGameState->set('endorse_action_state', 1);
            }

            if ($this->innovationGameState->unseenExpansionEnabled()) {
                self::resetWillDrawUnseenCardNext();
            }

            // Activate the next non-eliminated player in turn order
            do {
                $this->activeNextPlayer();
            } while (self::isEliminated($this->getActivePlayerId()));
            $player_id = self::getActivePlayerId();
            $this->innovationGameState->set('active_player', $player_id);
            self::setLauncherId($player_id);

            // Get next player to decide what to do with their Artifact
            $card = self::getArtifactOnDisplay($player_id);
            if ($card !== null) {
                $this->innovationGameState->set('current_action_number', 0);
                self::notifyGeneralInfo('<!--empty-->');
                self::increaseResourcesForArtifactOnDisplay($player_id, $card);
                self::trace('interPlayerTurn->artifactPlayerTurn');
                $this->gamestate->nextState('artifactPlayerTurn');
                return;
            }
            $this->innovationGameState->set('current_action_number', 1);
        } else {
            $this->innovationGameState->increment('current_action_number');
        }
        self::notifyGeneralInfo('<!--empty-->');
        self::trace('interPlayerTurn->playerTurn');
        $this->gamestate->nextState('playerTurn');
    }

    function stDogmaEffect()
    {
        // An effect of a dogma has to be resolved
        $nested_card_state = self::getCurrentNestedCardState();
        $card_id = $nested_card_state['card_id'];
        $current_effect_type = $nested_card_state['current_effect_type'];
        $current_effect_number = $nested_card_state['current_effect_number'];
        $card = self::getCardInfo($card_id);
        $qualified_effect = self::qualifyEffect($current_effect_type, $current_effect_number, $card);
        $launcher_id = self::getLauncherId();

        // Perform one-time setup for the effect
        if (!$nested_card_state['performed_one_time_setup']) {
            if (self::isInSeparateFile($card_id)) {
                $executionState = (new ExecutionState($this))
                    ->setEdition($this->innovationGameState->getEdition())
                    ->setLauncherId($launcher_id);
                $cardInstance = self::getCardInstance($card_id, $executionState);
                $cardInstance->oneTimeSetup();
            }
            self::updateCurrentNestedCardState('performed_one_time_setup', true);
        }

        // Search for the first player who will undergo/share the effects, if any
        // NOTE: During nested execution echo/non-demand effects are not shared with other players.
        $first_player = $nested_card_state['nesting_index'] > 0 && ($current_effect_type == 1 || $current_effect_type == 3) ? $launcher_id : self::getFirstPlayerUnderEffect($current_effect_type, $launcher_id);
        if ($first_player === null) {
            self::notifyGeneralInfo(
                "<span class='minor_information'>" . clienttranslate('Nobody is affected by the ${qualified_effect} of the card.') . "</span>",
                array(
                    'i18n'             => array('qualified_effect'),
                    'qualified_effect' => $qualified_effect
                )
            );

            // End of the effect
            self::trace('dogmaEffect->interDogmaEffect');
            $this->gamestate->nextState('interDogmaEffect');
            return;
        }

        self::updateCurrentNestedCardState('current_player_id', $first_player);
        $this->gamestate->changeActivePlayer($first_player);

        // Begin the loop with this player
        self::trace('dogmaEffect->playerInvolvedTurn');
        $this->gamestate->nextState('playerInvolvedTurn');
    }

    function stInterDogmaEffect()
    {
        // A effect of a dogma card has been resolved. Is there another one?
        $nested_card_state = self::getCurrentNestedCardState();
        $nesting_index = $nested_card_state['nesting_index'];
        $card_id = $nested_card_state['card_id'];
        $previous_effect_type = $nested_card_state['current_effect_type'];

        $launcher_id = $this->innovationGameState->get('active_player');
        if ($previous_effect_type == 3) { // echo effect
            $previous_effect_number = $nested_card_state['current_effect_number'];

            // After executing each buried echo effect, clear the auxiliary values.
            if ($nesting_index == 0) {
                $card_id_of_previous_echo_effect = self::getUniqueValueFromDB(
                    self::format(
                        "SELECT card_id FROM echo_execution WHERE nesting_index = {nesting_index} AND execution_index = {execution_index}",
                        array('nesting_index' => $nesting_index, 'execution_index' => $previous_effect_number)
                    )
                );
                if ($card_id_of_previous_echo_effect != $card_id) {
                    self::DbQuery("
                        UPDATE
                            nested_card_execution
                        SET
                            auxiliary_value = -1,
                            auxiliary_value_2 = -1
                        WHERE
                            nesting_index = 0"
                    );
                }
            }

            self::DbQuery(
                self::format(
                    "DELETE FROM echo_execution WHERE nesting_index = {nesting_index} AND execution_index = {execution_index}",
                    array('nesting_index' => $nesting_index, 'execution_index' => $previous_effect_number)
                )
            );

            if ($previous_effect_number > 1) {
                // Move to next echo effect
                $next_effect_number = $previous_effect_number - 1;
                $next_effect_type = 3;
            } else {
                // The last echo effect is complete, so move onto the next non-echo effect
                $next_effect_number = 1;
                $next_effect_type = 1; // non-demand
                if ($nesting_index == 0 && self::getCompelEffect($card_id)) {
                    $next_effect_type = 2; // I compel
                } else if (self::getDemandEffect($card_id)) {
                    // NOTE: In the 4th edition, demands on nested cards only happen if the card is being
                    // super-executed. We don't need to handle the nested case for earlier editions, since
                    // echo effects are not executed in that situation.
                    if ($nesting_index == 0 || $nested_card_state['super_execute']) {
                        $next_effect_type = 0; // I demand
                    }
                }
            }

        } else if ($previous_effect_type == 0 || $previous_effect_type == 2) { // There is only ever one "I demand" or "I compel" effect per card
            $next_effect_type = 1;
            $next_effect_number = 1;

            // Update statistics about I demand and I compel execution.
            if ($nesting_index == 0) {
                $affected_players = self::getObjectListFromDB("SELECT player_id FROM player WHERE effects_had_impact IS TRUE", true);
                foreach ($affected_players as $player_id) {
                    if ($previous_effect_type == 0) {
                        self::incStat(1, 'i_demand_effects_number', $player_id);
                    } else {
                        self::incStat(1, 'i_compel_effects_number', $player_id);
                    }
                }
                if (count($affected_players) > 0) {
                    if ($previous_effect_type == 0) {
                        self::incStat(1, 'dogma_actions_number_with_i_demand', $launcher_id);
                    } else {
                        self::incStat(1, 'dogma_actions_number_with_i_compel', $launcher_id);
                    }
                }
                // Reset 'effects_had_impact' so that it can be re-used for non-demand effects.
                self::DbQuery("UPDATE player SET effects_had_impact = FALSE");
            }
        } else {
            // Next non-demand effect, if it exists
            $next_effect_type = 1;
            $next_effect_number = $nested_card_state['current_effect_number'] + 1;
        }

        $card = self::getCardInfo($card_id);

        // If there isn't another dogma effect on the card
        if ($next_effect_type == 1 && ($next_effect_number > 3 || self::getNonDemandEffect($card['id'], $next_effect_number) === null)) {

            // Finish executing the card which triggered this one
            if ($nesting_index >= 1) {
                $card_args = self::getNotificationArgsForCardList([$card]);
                self::notifyAll(
                    'logWithCardTooltips',
                    clienttranslate('Execution of ${card_1} is complete.'),
                    ['card_1' => $card_args, 'card_ids' => [$card_id]]
                );

                self::popCardFromNestedDogmaStack();

                $nested_card_state = self::getCurrentNestedCardState();
                $this->gamestate->changeActivePlayer($nested_card_state['current_player_id']);
                self::trace('interDogmaEffect->playerInvolvedTurn');
                $this->gamestate->nextState('playerInvolvedTurn');
                return;
            }

            // Update statistics about which opponents shared in the non-demand effects
            $affected_players = self::getObjectListFromDB("SELECT player_id FROM player WHERE effects_had_impact IS TRUE", true);
            foreach ($affected_players as $player_id) {
                if ($player_id != $launcher_id) {
                    self::incStat(1, 'sharing_effects_number', $player_id);
                }
            }

            // Indicate that no dogma effects are being executed anymore
            $this->innovationGameState->set('current_nesting_index', -1);

            // Award the sharing bonus if needed
            $sharing_bonus = $this->innovationGameState->get('sharing_bonus');
            if ($sharing_bonus == 1) {
                self::incStat(1, 'dogma_actions_number_with_sharing', $launcher_id);
                self::notifyGeneralInfo('<span class="minor_information">${text}</span>', array('i18n' => array('text'), 'text' => clienttranslate('Sharing bonus.')));
                $player_who_launched_the_dogma = $this->innovationGameState->get('active_player');
                try {
                    self::executeDraw($player_who_launched_the_dogma); // Draw a card with age consistent with player board
                } catch (EndOfGame $e) {
                    // End of the game: the exception has reached the highest level of code
                    self::trace('EOG bubbled from self::stInterDogmaEffect');
                    self::trace('interDogmaEffect->justBeforeGameEnd');
                    $this->gamestate->nextState('justBeforeGameEnd');
                    return;
                }
            }

            // The active player may have changed during the dogma. Reset it on the player whose turn was
            $this->gamestate->changeActivePlayer($launcher_id);

            // Reset player table
            self::resetPlayerTable();

            // Disable the flags used when in dogma 
            if (self::getGameStateValue('release_version') >= 5) {
                self::DbQuery("DELETE FROM action_scoped_auxiliary_value_table");
            }
            self::DbQuery("
                UPDATE
                    nested_card_execution
                SET
                    card_id = -1,
                    executing_as_if_on_card_id = -1,
                    launcher_id = -1,
                    card_location = NULL,
                    current_player_id = -1,
                    current_effect_type = -1,
                    current_effect_number = -1,
                    auxiliary_value = -1,
                    auxiliary_value_2 = -1
                WHERE
                    nesting_index = 0"
            );
            $this->innovationGameState->set('sharing_bonus', -1);
            self::setStep(-1);
            self::setStepMax(-1);
            $this->innovationGameState->set('special_type_of_choice', -1);
            $this->innovationGameState->set('choice', -1);
            $this->innovationGameState->set('splay_direction', -1);
            $this->innovationGameState->set('n_min', -1);
            $this->innovationGameState->set('n_max', -1);
            $this->innovationGameState->set('solid_constraint', -1);
            $this->innovationGameState->set('owner_from', -1);
            $this->innovationGameState->set('location_from', -1);
            $this->innovationGameState->set('owner_to', -1);
            $this->innovationGameState->set('location_to', -1);
            $this->innovationGameState->set('bottom_to', -1);
            $this->innovationGameState->set('bottom_from', -1);
            $this->innovationGameState->set('age_min', -1);
            $this->innovationGameState->set('age_max', -1);
            $this->innovationGameState->set('age_array', -1);
            $this->innovationGameState->set('color_array', -1);
            $this->innovationGameState->set('type_array', -1);
            $this->innovationGameState->set('choice_array', -1);
            $this->innovationGameState->set('icon_array', -1);
            $this->innovationGameState->set('player_array', -1);
            $this->innovationGameState->set('not_id', -1);
            $this->innovationGameState->set('card_id_1', -1);
            $this->innovationGameState->set('card_id_2', -1);
            $this->innovationGameState->set('card_id_3', -1);
            $this->innovationGameState->set('icon_hash_1', -1);
            $this->innovationGameState->set('icon_hash_2', -1);
            $this->innovationGameState->set('icon_hash_3', -1);
            $this->innovationGameState->set('icon_hash_4', -1);
            $this->innovationGameState->set('icon_hash_5', -1);
            $this->innovationGameState->set('enable_autoselection', -1);
            $this->innovationGameState->set('include_relics', -1);
            $this->innovationGameState->set('include_special_achievements', -1);
            $this->innovationGameState->set('can_pass', -1);
            $this->innovationGameState->set('n', -1);
            $this->innovationGameState->set('id_last_selected', -1);
            $this->innovationGameState->set('age_last_selected', -1);
            $this->innovationGameState->set('color_last_selected', -1);
            $this->innovationGameState->set('owner_last_selected', -1);
            $this->innovationGameState->set('score_keyword', -1);
            $this->innovationGameState->set('meld_keyword', -1);
            $this->innovationGameState->set('achieve_keyword', -1);
            $this->innovationGameState->set('safeguard_keyword', -1);
            $this->innovationGameState->set('draw_keyword', -1);
            $this->innovationGameState->set('return_keyword', -1);
            $this->innovationGameState->set('foreshadow_keyword', -1);
            $this->innovationGameState->set('require_achievement_eligibility', -1);
            $this->innovationGameState->set('has_demand_effect', -1);
            $this->innovationGameState->set('refresh_selection', -1);
            $this->innovationGameState->set('reveal_if_unable', -1);
            $this->innovationGameState->set('has_splay_direction', -1);
            $this->innovationGameState->set('foreseen_card_id', -1);

            // End of this player action
            self::trace('interDogmaEffect->interPlayerTurn');
            $this->gamestate->nextState('interPlayerTurn');
            return;
        }

        // There is another effect to perform
        self::updateCurrentNestedCardState('current_effect_number', $next_effect_number);
        self::updateCurrentNestedCardState('current_effect_type', $next_effect_type);
        if (self::isExecutingAgainDueToEndorsedAction()) {
            $this->innovationGameState->set('endorse_action_state', 2);
        }

        // Jump to this effect
        self::trace('interDogmaEffect->dogmaEffect');
        $this->gamestate->nextState('dogmaEffect');
    }

    /* Whether or not the card's implementation is in a separate file */
    function isInSeparateFile($card_id)
    {
        $card = $this->getCardInfo($card_id);
        if ($card['type'] == CardTypes::CITIES) {
            return false;
        }
        return $card_id <= 214
            || (220 <= $card_id && $card_id <= 498)
            || $card_id >= 501;
    }

    function getCardInstance($card_id, $execution_state)
    {
        $card = $this->getCardInfo($card_id);
        $set = "Base";
        if ($card['type'] == CardTypes::ARTIFACTS) {
            $set = "Artifacts";
        } else if ($card['type'] == CardTypes::ECHOES) {
            $set = "Echoes";
        } else if ($card['type'] == CardTypes::UNSEEN) {
            $set = "Unseen";
        }
        $suffix = self::getEditionSuffix($card_id);
        require_once("modules/Innovation/Cards/{$set}/Card{$card_id}{$suffix}.php");
        $classname = "Innovation\Cards\\{$set}\Card{$card_id}{$suffix}";
        return new $classname($this, $execution_state);
    }

    function stPlayerInvolvedTurn()
    {
        // A player must or can undergo/share an effect of a dogma card
        $player_id = self::getCurrentPlayerUnderDogmaEffect();
        $launcher_id = self::getLauncherId();

        $nested_card_state = self::getCurrentNestedCardState();
        $card_id = $nested_card_state['card_id'];
        $current_effect_type = $nested_card_state['current_effect_type'];
        $current_effect_number = $nested_card_state['current_effect_number'];
        // Echo effects are sometimes executed on cards other than the card being dogma'd
        if ($current_effect_type == 3) {
            $nesting_index = $nested_card_state['nesting_index'];
            $card_id = self::getUniqueValueFromDB(
                self::format(
                    "SELECT card_id FROM echo_execution WHERE nesting_index = {nesting_index} AND execution_index = {effect_number}",
                    array('nesting_index' => $nesting_index, 'effect_number' => $current_effect_number)
                )
            );
        }

        // The distance rule allows non-adjacent players to return a card from hand in order share or to avoid a demand
        $is_non_demand_or_echo_effect = $current_effect_type == 1 || $current_effect_type == 3;
        $column = $is_non_demand_or_echo_effect ? 'distance_rule_share_state' : 'distance_rule_demand_state';
        if (self::getPlayerTableColumn($player_id, $column) == 0) {
            foreach (self::getPlayerIdsAffectedByDistanceRule($launcher_id) as $opponent_id) {
                if ($opponent_id == $player_id) {
                    if (self::countCardsInHand($opponent_id) == 0) {
                        // Player does not have cards in hand so they are unable to return a card in order to share the effect or avoid a demand
                        self::setPlayerTableColumn($player_id, $column, 2);
                        if ($is_non_demand_or_echo_effect) {
                            self::notifyPlayer($player_id, 'log', clienttranslate('${You} did not have any cards in your hand so you could not share the effect.'), array('You' => 'You'));
                            self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} did not have any cards in his hand so he could not share the effect.'), array('player_name' => self::getPlayerNameFromId($player_id)));
                            // Skip sharing
                            self::trace('playerInvolvedTurn->interPlayerInvolvedTurn');
                            $this->gamestate->nextState('interPlayerInvolvedTurn');
                            return;
                        } else {
                            self::notifyPlayer($player_id, 'log', clienttranslate('${You} did not have any cards in your hand so you could not avoid the demand.'), array('You' => 'You'));
                            self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} did not have any cards in his hand so he could not avoid the demand.'), array('player_name' => self::getPlayerNameFromId($player_id)));
                        }
                    } else {
                        // Player will be given the opportunity to return a card in order to share the effect or avoid a demand
                        self::setPlayerTableColumn($player_id, $column, 1);
                        self::trace('playerInvolvedTurn->interactionStep');
                        $this->gamestate->nextState('interactionStep');
                        return;
                    }
                    break;
                }
            }
        }

        $executionState = (new ExecutionState($this))
            ->setEdition($this->innovationGameState->getEdition())
            ->setLauncherId($launcher_id)
            ->setPlayerId($player_id)
            ->setEffectType($current_effect_type)
            ->setEffectNumber($current_effect_number)
            ->setMaxSteps(0);

        $code = self::getCardExecutionCode($card_id, $current_effect_type, $current_effect_number);
        $step_max = null;
        $step = null;

        if ($nested_card_state['post_execution_index'] == 0) {
            $qualified_effect = self::qualifyEffect($current_effect_type, $current_effect_number, self::getCardInfo($card_id));
            self::notifyEffectOnPlayer($qualified_effect, $player_id, $launcher_id);
        } else {
            // TODO(LATER): Consider adding something to the log which says that an effect is resuming.
        }

        $leaf = Icons::render(2);
        $lightbulb = Icons::render(3);

        $using_execution_status_object = false;

        try {

            if (self::isInSeparateFile($card_id)) {
                $cardInstance = self::getCardInstance($card_id, $executionState);
                if ($nested_card_state['post_execution_index'] == 0 || $cardInstance->hasPostExecutionLogic()) {
                    self::getCardInstance($card_id, $executionState)->initialExecution();
                }
                $using_execution_status_object = true;
            }

            switch ($code) {
                // The first number is the id of the card
                // D1 means the first (and single) I demand effect
                // C1 means the first (and single) I compel effect
                // N1 means the first non-demand effect
                // N2 means the second non-demand effect
                // N3 means the third non-demand effect
                // E1 means the first (and single) echo effect

                // Setting the $step_max variable means there is interaction needed with the player

                // id 216, Relic age 4: Complex Numbers
                case "216N1":
                    if (self::countCardsInLocation($player_id, 'hand') > 0) {
                        $step_max = 1;
                    }
                    break;

                // id 217, Relic age 5: Newton-Wickins Telescope
                case "217N1":
                    $step_max = 1;
                    break;

                // id 219, Relic age 7: Safety Pin
                case "219E1":
                    // Draw and score a 7."
                    self::executeDraw($player_id, 7, 'score');
                    break;

                case "219D1":
                    $step_max = 1;
                    break;

                // id 499, Unseen age 2: Cipher
                case "499N1":
                    self::setAuxiliaryValue(0);
                    $step_max = 1;
                    break;

                case "499N2":
                    // "Draw a 2."
                    self::executeDraw($player_id, 2);
                    $step_max = 1;
                    break;

                // id 500, Unseen age 2: Counterfeiting
                case "500N1":
                    $top_cards = self::getTopCardsOnBoard($player_id);
                    $score_cards_by_age = self::countCardsInLocationKeyedByAge($player_id, 'score');
                    $card_id_array = array();
                    foreach ($top_cards as $card) {
                        for ($age = 1; $age <= 11; $age++) {
                            if ($score_cards_by_age[$card['age']] == 0) {
                                $card_id_array[] = $card['id'];
                            }
                        }
                    }
                    if (count($card_id_array) > 0) {
                        $step_max = 1;
                        self::setAuxiliaryArray($card_id_array);
                    }
                    break;

                case "500N2":
                    $step_max = 1;
                    break;

                default:
                    // Do not throw an exception so that we are able to stop executing a card after it's popped from
                    // the stack and there's nothing left to do.
                    break;
            }
        } catch (EndOfGame $e) {
            // End of the game: the exception has reached the highest level of code
            self::trace('EOG bubbled from self::stPlayerInvolvedTurn');
            self::trace('playerInvolvedTurn->justBeforeGameEnd');
            $this->gamestate->nextState('justBeforeGameEnd');
            return;
        }

        if ($using_execution_status_object) {
            $step_max = $executionState->getMaxSteps();
            $step = $executionState->getNextStep();
        }

        // TODO(#1102): Remove null check.
        if ($step_max === null || $step_max === 0) {
            // End of the effect for this player
            self::trace('playerInvolvedTurn->interPlayerInvolvedTurn');
            $this->gamestate->nextState('interPlayerInvolvedTurn');
            return;
        }
        // There is an interaction needed
        self::setStepMax($step_max);

        // Prepare the first step
        // TODO(#1102): Remove null check.
        self::setStep($step === null ? 1 : $step);
        self::trace('playerInvolvedTurn->interactionStep');
        $this->gamestate->nextState('interactionStep');
    }

    function stInterPlayerInvolvedTurn()
    {

        // Switch to new card that was pushed onto the stack
        if (self::getNestedCardState($this->innovationGameState->get('current_nesting_index') + 1) != null) {
            $this->innovationGameState->increment('current_nesting_index');
            self::trace('interPlayerInvolvedTurn->dogmaEffect');
            $this->gamestate->nextState('dogmaEffect');
            return;
        }

        // A player has executed an effect of a dogma card (or passed). Is there another player on which the effect can apply?
        $player_id = self::getCurrentPlayerUnderDogmaEffect();
        $launcher_id = self::getLauncherId();

        $nesting_index = $this->innovationGameState->get('current_nesting_index');
        $current_effect_type = self::getNestedCardState($nesting_index)['current_effect_type'];

        if ($current_effect_type == 3) {
            self::incStat(1, 'executed_echo_effect_number', $player_id);
        }

        $nesting_index = $this->innovationGameState->get('current_nesting_index');
        self::updateCurrentNestedCardState('post_execution_index', 0);
        $nested_card_state = self::getNestedCardState($nesting_index);

        // If this is a nested card, don't allow other players to share the non-demand effect
        if ($nesting_index >= 1 && $nested_card_state['current_effect_type'] == 1) {
            $next_player = null;
            // If the dogma is being endorsed, allow the launcher to execute the non-demand or echo effect again
        } else if ($nesting_index == 0 && $this->innovationGameState->get('endorse_action_state') == 2 && $player_id == $launcher_id && ($current_effect_type == 1 || $current_effect_type == 3)) {
            $this->innovationGameState->set('endorse_action_state', 3);
            $next_player = $player_id;
        } else {
            $next_player = self::getNextPlayerUnderEffect($current_effect_type, $player_id, $launcher_id);
            // If the dogma is being endorsed and it's a demand (or compel) effect, go around a second time
            if ($next_player == null && $nesting_index == 0 && $this->innovationGameState->get('endorse_action_state') == 2 && ($current_effect_type == 0 || $current_effect_type == 2)) {
                $this->innovationGameState->set('endorse_action_state', 3);
                $next_player = self::getFirstPlayerUnderEffect($current_effect_type, $launcher_id);
            }
        }

        // There are no more players which are eligible to share this effect
        if ($next_player === null) {
            self::trace('interPlayerInvolvedTurn->interDogmaEffect');
            $this->gamestate->nextState('interDogmaEffect');
            return;
        }

        // Jump to this player
        if ($player_id != $next_player) {
            self::updateCurrentNestedCardState('current_player_id', $next_player);
            $this->gamestate->changeActivePlayer($next_player);
        }
        self::trace('interPlayerInvolvedTurn->playerInvolvedTurn');
        $this->gamestate->nextState('playerInvolvedTurn');
    }

    function stInteractionStep()
    {
        $player_id = self::getCurrentPlayerUnderDogmaEffect();
        $launcher_id = self::getLauncherId();

        if (self::getPlayerTableColumn($player_id, 'distance_rule_share_state') == 1 || self::getPlayerTableColumn($player_id, 'distance_rule_demand_state') == 1) {
            $options = array(
                'player_id'     => $player_id,
                'n'             => 1,
                'can_pass'      => true,

                'owner_from'    => $player_id,
                'location_from' => 'hand',
                'owner_to'      => 0,
                'location_to'   => 'deck',
            );
            self::setSelectionRange($options);
            self::trace('interactionStep->preSelectionMove');
            $this->gamestate->nextState('preSelectionMove');
            return;
        }

        $nested_card_state = self::getCurrentNestedCardState();
        $card_id = $nested_card_state['card_id'];
        $current_effect_type = $nested_card_state['current_effect_type'];
        $current_effect_number = $nested_card_state['current_effect_number'];
        // Echo effects are sometimes executed on cards other than the card being dogma'd
        if ($current_effect_type == 3) {
            $nesting_index = $nested_card_state['nesting_index'];
            $card_id = self::getUniqueValueFromDB(self::format("SELECT card_id FROM echo_execution WHERE nesting_index = {nesting_index} AND execution_index = {effect_number}", array('nesting_index' => $nesting_index, 'effect_number' => $current_effect_number)));
        }

        $step = self::getStep();

        $executionState = (new ExecutionState($this))
            ->setEdition($this->innovationGameState->getEdition())
            ->setLauncherId($launcher_id)
            ->setPlayerId($player_id)
            ->setEffectType($current_effect_type)
            ->setEffectNumber($current_effect_number)
            ->setCurrentStep(self::getStep())
            ->setNextStep(self::getStep() + 1)
            ->setMaxSteps(self::getStepMax());

        $code = self::getCardExecutionCodeWithLetter($card_id, $current_effect_type, $current_effect_number, $step);

        if (self::isInSeparateFile($card_id)) {
            $compact_options = self::getCardInstance($card_id, $executionState)->getInteractionOptions();
            $options = self::expandInteractionOptions($compact_options, $player_id, /*is_refreshing_options*/ false);
        }

        switch ($code) {

            // The first number is the id of the card
            // D1 means the first (and single) I demand effect
            // C1 means the first (and single) I compel effect
            // N1 means the first non-demand effect
            // N2 means the second non-demand effect
            // N3 means the third non-demand effect
            // E1 means the first (and single) echo effect

            // The letter indicates the step : A for the first one, B for the second

            // Setting the $step_max variable means there is interaction needed with the player

            // id 216, Relic age 4: Complex Numbers
            case "216N1A":
                // "You may reveal a card from your hand having exactly the same icons, in type and number, as a top card on your board"
                $card_ids = array();
                $hand_cards = self::getCardsInLocation($player_id, 'hand');
                $top_cards = self::getTopCardsOnBoard($player_id);
                // Bonus icons and other special city icons are counted as per https://boardgamegeek.com/thread/1872362/article/40784224.
                foreach ($hand_cards as $card) {
                    $eligible = false;

                    foreach ($top_cards as $top_card) {
                        $match_found = true;
                        if ($top_card !== null) {
                            // search icons are considered different than basic icons so they need to be handled separately
                            if ($card['spot_6'] !== null && $top_card['spot_6'] !== null && $card['spot_6'] != $top_card['spot_6']) {
                                $match_found = false; // If the search icons don't match
                                break;
                            }
                            for ($icon = 1; $icon <= 13; $icon++) {
                                // Echo effects are not considered icons, so they are skipped
                                if ($icon == 10) {
                                    continue;
                                }
                                if (self::countIconsOnCard($card, $icon) != self::countIconsOnCard($top_card, $icon)) {
                                    $match_found = false; // If any icon counts mismatch, then the card isn't eligible
                                    break;
                                }
                            }
                            for ($icon = 101; $icon <= 112; $icon++) { // count bonus icons
                                if (self::countIconsOnCard($card, $icon) != self::countIconsOnCard($top_card, $icon)) {
                                    $match_found = false; // If any icon counts mismatch, then the card isn't eligible
                                    break;
                                }
                            }
                            if ($match_found) {
                                $eligible = true;
                            }
                        }
                    }
                    if ($eligible) {
                        $card_ids[] = $card['id'];
                    }

                }
                self::setAuxiliaryArray($card_ids);

                $options = array(
                    'player_id'                       => $player_id,
                    'n'                               => 1,
                    'can_pass'                        => true,

                    'owner_from'                      => $player_id,
                    'location_from'                   => 'hand',
                    'owner_to'                        => $player_id,
                    'location_to'                     => 'revealed',

                    'card_ids_are_in_auxiliary_array' => true,
                    'enable_autoselection'            => false,
                );
                break;

            case "216N1B":
                // "Claim an achievement of matching value, ignoring eligibility"
                $options = array(
                    'player_id'                       => $player_id,
                    'n'                               => 1,

                    'owner_from'                      => 0,
                    'location_from'                   => 'achievements',
                    'owner_to'                        => $player_id,
                    'location_to'                     => 'achievements',

                    'age'                             => $this->innovationGameState->get('age_last_selected'),
                    'require_achievement_eligibility' => false
                );
                break;

            // id 217, Relic age 5: Newton-Wickins Telescope
            case "217N1A":
                // "You may return any number of cards from your score pile"
                $options = array(
                    'player_id'     => $player_id,
                    'n_min'         => 1,
                    'can_pass'      => true,

                    'owner_from'    => $player_id,
                    'location_from' => 'score',
                    'owner_to'      => 0,
                    'location_to'   => 'deck'
                );
                break;

            // id 219, Relic age 7: Safety Pin
            case "219D1A":
                // "I demand you return all cards of value higher than 6 from your hand!"
                $options = array(
                    'player_id'     => $player_id,

                    'owner_from'    => $player_id,
                    'location_from' => 'hand',
                    'owner_to'      => 0,
                    'location_to'   => 'deck',

                    'age_min'       => 7,
                );
                break;

            // id 499, Unseen age 2: Cipher
            case "499N1A":
                // "Return all cards from your hand."
                $options = array(
                    'player_id'     => $player_id,

                    'owner_from'    => $player_id,
                    'location_from' => 'hand',
                    'owner_to'      => 0,
                    'location_to'   => 'deck',
                );
                break;

            case "499N2A":
                // "You may splay your blue cards left."
                $options = array(
                    'player_id'       => $player_id,
                    'n'               => 1,
                    'can_pass'        => true,

                    'splay_direction' => 1,
                    'color'           => array(0),
                    // blue
                );
                break;

            // id 500, Unseen age 2: Counterfeiting
            case "500N1A":
                // "Score a top card from your board of a value not in your score pile."            
                $options = array(
                    'player_id'                       => $player_id,
                    'n'                               => 1,

                    'owner_from'                      => $player_id,
                    'location_from'                   => 'board',
                    'owner_to'                        => $player_id,
                    'location_to'                     => 'score',

                    'card_ids_are_in_auxiliary_array' => true,
                );
                break;

            case "500N2A":
                // "You may splay your green or purple cards left."
                $options = array(
                    'player_id'       => $player_id,
                    'n'               => 1,
                    'can_pass'        => true,

                    'splay_direction' => 1,
                    'color'           => array(2, 4),
                    // green or purple
                );
                break;

            default:
                if (!self::isInSeparateFile($card_id)) {
                    // This should not happen
                    throw new BgaVisibleSystemException(self::format(self::_("Unreferenced card effect code in section B: '{code}'"), array('code' => $code)));
                }
                break;
        }

        // Decrease the number of cards to select based on the forecast/safe limit
        if ($options && array_key_exists('location_to', $options) && ($options['location_to'] == 'forecast' || $options['location_to'] == 'safe')) {
            $space_left = self::getForecastAndSafeLimit($options['owner_to']) - self::countCardsInLocation($options['owner_to'], $options['location_to']);
            if ($space_left < 0) {
                $space_left = 0;
            }
            // NOTE: This is only being set now in case notifyIfLocationLimitShrunkSelection is called.
            $this->innovationGameState->set('location_to', Locations::encode($options['location_to']));
            if (array_key_exists('n', $options) && $options['n'] > $space_left) {
                $options['n'] = $space_left;
                $this->innovationGameState->set('limit_shrunk_selection_size', 1);
            }
            if (array_key_exists('n_min', $options) && $options['n_min'] > $space_left) {
                $options['n_min'] = $space_left;
                $this->innovationGameState->set('limit_shrunk_selection_size', 1);
            }
            if (array_key_exists('n_max', $options) && $options['n_max'] > $space_left) {
                $options['n_max'] = $space_left;
                $this->innovationGameState->set('limit_shrunk_selection_size', 1);
            }
        }

        // There wasn't an interaction needed in this step after all
        if (
            $options == null
            || (array_key_exists('n', $options) && $options['n'] <= 0)
            || (array_key_exists('n_max', $options) && $options['n_max'] <= 0)
            || (array_key_exists('choose_value', $options) && (array_key_exists('age', $options) && empty($options['age'])))
            || (array_key_exists('choices', $options) && empty($options['choices']))
            || (array_key_exists('color', $options) && empty($options['color']))
            || (array_key_exists('players', $options) && empty($options['players']))
        ) {

            self::notifyIfLocationLimitShrunkSelection($executionState->getPlayerId());

            if (self::isInSeparateFile($card_id)) {
                $executionState->setNumChosen(0);
                self::getCardInstance($card_id, $executionState)->handleAbortedInteraction();
                $step = $executionState->getNextStep() - 1;
                self::setStep($step);
                self::setStepMax($executionState->getMaxSteps());
            }

            // The last step has been completed, so it's the end of the turn for the player involved
            if ($step == self::getStepMax()) {
                self::trace('interactionStep->interPlayerInvolvedTurn');
                $this->gamestate->nextState('interPlayerInvolvedTurn');
                return;
            }
            // There's at least one more interaction step to attempt
            self::incrementStep(1);
            self::trace('interactionStep->interactionStep');
            $this->gamestate->nextState('interactionStep');
            return;
        }

        self::setSelectionRange($options);

        self::trace('interactionStep->preSelectionMove');
        $this->gamestate->nextState('preSelectionMove');
    }

    function stInterInteractionStep()
    {
        $player_id = self::getCurrentPlayerUnderDogmaEffect();
        $launcher_id = self::getLauncherId();

        $nested_card_state = self::getCurrentNestedCardState();

        // There won't be any nested card state if a player is returning cards after the Search icon or Junk Achievement icon was triggered.
        if ($nested_card_state == null) {
            $code = null;
            $step = 1;
            $step_max = 1;
            $current_effect_type = -1;
            $current_effect_number = -1;
        } else {
            $card_id = $nested_card_state['card_id'];
            $current_effect_type = $nested_card_state['current_effect_type'];
            $current_effect_number = $nested_card_state['current_effect_number'];
            // Echo effects are sometimes executed on cards other than the card being dogma'd
            if ($current_effect_type == 3) {
                $nesting_index = $nested_card_state['nesting_index'];
                $card_id = self::getUniqueValueFromDB(
                    self::format(
                        "SELECT card_id FROM echo_execution WHERE nesting_index = {nesting_index} AND execution_index = {effect_number}",
                        array('nesting_index' => $nesting_index, 'effect_number' => $current_effect_number)
                    )
                );
            }
            $step = self::getStep();
            $step_max = self::getStepMax();
            $code = self::getCardExecutionCodeWithLetter($card_id, $current_effect_type, $current_effect_number, $step);
        }

        $n = $this->innovationGameState->get('n');

        $executionState = (new ExecutionState($this))
            ->setEdition($this->innovationGameState->getEdition())
            ->setLauncherId($launcher_id)
            ->setPlayerId($player_id)
            ->setEffectType($current_effect_type)
            ->setEffectNumber($current_effect_number)
            ->setNumChosen($n)
            ->setCurrentStep($step)
            ->setNextStep($step + 1)
            ->setMaxSteps($step_max);

        self::notifyIfLocationLimitShrunkSelection($player_id);

        if (!self::isZombie(self::getActivePlayerId())) {
            try {
                if ($code !== null && self::isInSeparateFile($card_id)) {
                    self::getCardInstance($card_id, $executionState)->afterInteraction();
                    $step = $executionState->getNextStep() - 1;
                    $step_max = $executionState->getMaxSteps();
                    self::setStep($step);
                    self::setStepMax($step_max);
                }

                switch ($code) {
                    // The first number is the id of the card
                    // D1 means the first (and single) I demand effect
                    // C1 means the first (and single) I compel effect
                    // N1 means the first non-demand effect
                    // N2 means the second non-demand effect
                    // N3 means the third non-demand effect
                    // E1 means the first (and single) echo effect

                    // The letter indicates the step : A for the first one, B for the second

                    // id 149, Artifacts age 4: Molasses Reef Caravel
                    case "149N1A":
                        // "Draw three 4's"
                        self::executeDraw($player_id, 4);
                        self::executeDraw($player_id, 4);
                        self::executeDraw($player_id, 4);

                        $number_of_blue_cards = self::countCardsInLocationKeyedByColor($player_id, 'hand')[Colors::BLUE];
                        if ($number_of_blue_cards == 0) {
                            self::revealHand($player_id);
                            $color_in_clear = Colors::render(Colors::BLUE);
                            self::notifyPlayer($player_id, 'log', clienttranslate('${You} have no ${colored} cards in your hand.'), array('i18n' => array('colored'), 'You' => 'You', 'colored' => $color_in_clear));
                            self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} has no ${colored} cards in his hand.'), array('i18n' => array('colored'), 'player_name' => self::renderPlayerName($player_id), 'colored' => $color_in_clear));
                            $step = $step + 1;
                            self::incrementStep(1);
                        }
                        break;

                    // id 180, Artifacts age 7: Hansen Writing Ball
                    case "180C1A":
                        // "Transfer all cards in your hand to my hand"
                        foreach (self::getIdsOfCardsInLocation($player_id, 'hand') as $id) {
                            self::transferCardFromTo(self::getCardInfo($id), $launcher_id, 'hand');
                        }
                        break;

                    // id 216, Relic age 4: Complex Numbers
                    case "216N1A":
                        // "If you do"
                        if ($n > 0) {
                            // Return card to hand
                            $revealed_card = self::getCardsInLocation($player_id, 'revealed')[0];
                            self::transferCardFromTo($revealed_card, $player_id, 'hand');
                            self::incrementStepMax(1);
                        }
                        break;

                    // id 217, Relic age 5: Newton-Wickins Telescope
                    case "217N1A":
                        // "If you do, draw and meld a card of value equal to the number of cards returned"
                        if ($n > 0) {
                            $card = self::executeDrawAndMeld($player_id, $n);

                            // "If the melded card has a clock, return it"
                            if (self::countIconsOnCard($card, 6) > 0) {
                                self::returnCard($card);
                            }
                        }
                        break;

                    // id 219, Relic age 7: Safety Pin
                    case "219D1A":
                        // "Draw a 6!"
                        self::executeDraw($player_id, 6);
                        break;

                    // id 499, Unseen age 2: Cipher
                    case "499N1A":
                        if ($n >= 2) { // "If you return two or more,"
                            // " draw a card of value one higher than the highest value of card you return."
                            self::executeDraw($player_id, self::getAuxiliaryValue() + 1);
                        }
                        break;

                    // id 500, Unseen age 2: Counterfeiting
                    case "500N1A":
                        if ($n > 0) { // "If you do,"
                            $top_cards = self::getTopCardsOnBoard($player_id);
                            $score_cards_by_age = self::countCardsInLocationKeyedByAge($player_id, 'score');
                            $card_id_array = array();
                            foreach ($top_cards as $card) {
                                for ($age = 0; $age < 12; $age++) {
                                    if ($score_cards_by_age[$card['age']] == 0) {
                                        $card_id_array[] = $card['id'];
                                    }
                                }
                            }
                            if (count($card_id_array) > 0) {
                                // "repeat this effect."
                                self::setStep(0);
                                $step = 0;
                                self::setAuxiliaryArray($card_id_array);
                            }
                        }
                        break;

                }

            } catch (EndOfGame $e) {
                // End of the game: the exception has reached the highest level of code
                self::trace('EOG bubbled from self::stInterInteractionStep');
                self::trace('interInteractionStep->justBeforeGameEnd');
                $this->gamestate->nextState('justBeforeGameEnd');
                return;
            }
        }

        // There won't be any nested card state if a player just performed an interaction outside of the context of a dogma effect
        if ($nested_card_state === null) {
            if ($this->innovationGameState->get('special_type_of_choice') > 0) { // Digging/stealing artifact

                // "After you dig an artifact, you may seize a Relic of the same value as the Artifact card drawn."
                if ($this->innovationGameState->artifactsExpansionEnabledWithRelics()) {
                    $artifact = self::getArtifactOnDisplay($player_id);
                    if ($artifact) {
                        $relic = self::getRelicForAge($artifact['faceup_age']);
                        // "You may only do this if the Relic is next to its supply pile, or in any achievements pile (even your own!)."
                        if ($relic != null && (self::canSeizeRelicToHand($relic, $player_id) || self::canSeizeRelicToAchievements($relic, $player_id))) {
                            $this->innovationGameState->set('relic_id', $relic['id']);
                            self::trace('interInteractionStep->relicPlayerTurn');
                            $this->gamestate->nextState('relicPlayerTurn');
                            return;
                        }
                    }
                }

                self::trace('interInteractionStep->promoteCard');
                $this->gamestate->nextState('promoteCard');
                return;
            } else if (Locations::decode($this->innovationGameState->get('location_from')) === Locations::MUSEUMS) { // Returning artifacts from museums
                // Award a museum to the player with the single most museums
                $max_count = 0;
                $player_id_with_max = null;
                foreach (self::getAllActivePlayerIds() as $playerId) {
                    $count = self::countCardsInLocation($playerId, Locations::MUSEUMS);
                    if ($count > $max_count) {
                        $max_count = $count;
                        $player_id_with_max = $playerId;
                    } else if ($count === $max_count) {
                        $player_id_with_max = null;
                    }
                }
                if ($player_id_with_max) {
                    $museum = self::getCardsInLocation($player_id_with_max, Locations::MUSEUMS)[0];
                    try {
                        $this->transferCardFromTo($museum, $player_id_with_max, Locations::ACHIEVEMENTS, ["achieve_keyword" => true]);
                    } catch (EndOfGame $e) {
                        self::trace('interInteractionStep->justBeforeGameEnd');
                        $this->gamestate->nextState('justBeforeGameEnd');
                        return;
                    }
                }

                // Make the remaining museums available again
                foreach (self::getAllActivePlayerIds() as $playerId) {
                    foreach (self::getCardsInLocation($playerId, Locations::MUSEUMS) as $museum) {
                        $this->transferCardFromTo($museum, 0, Locations::MUSEUMS);
                    }
                }

                self::trace('interInteractionStep->finishArtifactPlayerTurn');
                $this->gamestate->nextState('finishArtifactPlayerTurn');
                return;
            } else { // Search icon or Junk Achievement icon
                self::trace('interInteractionStep->digArtifact');
                $this->gamestate->nextState('digArtifact');
                return;
            }
        }

        if ($step == self::getStepMax()) { // The last step has been completed

            if ($code !== null && self::isInSeparateFile($card_id)) {
                $executionState->setCurrentStep(null);
                $executionState->setNextStep(null);
                $executionState->setMaxSteps(null);
                self::getCardInstance($card_id, $executionState)->atEndOfEffect();
            }

            // End of the turn for the player involved
            self::trace('interInteractionStep->interPlayerInvolvedTurn');
            $this->gamestate->nextState('interPlayerInvolvedTurn');
            return;
        }
        // New interaction step
        self::incrementStep(1);
        self::trace('interInteractionStep->interactionStep');
        $this->gamestate->nextState('interactionStep');
    }

    function stPreSelectionMove()
    {
        $player_id = self::getActivePlayerId();
        $special_type_of_choice = $this->innovationGameState->get('special_type_of_choice');
        $can_pass = $this->innovationGameState->get('can_pass') == 1;

        if ($special_type_of_choice == 0) {
            $selection_size = self::countSelectedCards();
            $cards_chosen_so_far = $this->innovationGameState->get('n');
            $n_min = $this->innovationGameState->get('n_min');
            $n_max = $this->innovationGameState->get('n_max');
            $splay_direction = $this->innovationGameState->get('splay_direction');
            $autoselection_mode = $this->innovationGameState->get('enable_autoselection');
            $refresh_selection = $this->innovationGameState->get('refresh_selection') == 1;
            $owner_from = $this->innovationGameState->get('owner_from');
            $location_from = Locations::decode($this->innovationGameState->get('location_from'));
            $location_to = Locations::decode($this->innovationGameState->get('location_to'));
            $bottom_to = $this->innovationGameState->get('bottom_to');
            $colors = $this->innovationGameState->getAsArray('color_array');
            $with_icons = $this->innovationGameState->getAsArray('with_icons');
            $without_icons = $this->innovationGameState->getAsArray('without_icons');
            $with_bonus = $this->innovationGameState->get('with_bonus');
            $without_bonus = $this->innovationGameState->get('without_bonus');
            $card_id_returning_to_unique_supply_pile = $location_to == 'deck' || $location_to == 'revealed,deck' ? self::getSelectedCardIdBelongingToUniqueSupplyPile() : null;
            $card_id_with_unique_color = $location_to == 'board' ? self::getSelectedCardIdWithUniqueColor() : null;

            // TODO(FIGURES): Figure out if we need to make any updates to this logic.
            $num_cards_in_location_from = self::countCardsInLocation($owner_from, $location_from);
            $selection_will_reveal_hidden_information =
                    // The player making the decision has hidden information about the card(s) that other players do not have.
                ($location_from == 'hand' || $location_from == 'score' || $location_from == 'forecast') &&
                // All players can see the number of cards (even in hidden locations) so if there aren't any cards there
                // it's obvious a selection can't be made.
                $num_cards_in_location_from > 0 &&
                    // Player is forced to choose a card based on a hidden property (e.g. color or icons). There are
                    // other hidden properties (has_demand_effect, icon_hash_X) that aren't included here because there
                    // are currently no cards where this would actually matter.
                ($colors != array(0, 1, 2, 3, 4) || count($with_icons) > 0 || count($without_icons) > 0 || $with_bonus > 0 || $without_bonus > 0);

            // If all cards from the location must be chosen, then it doesn't matter if the information is hidden or not. It will soon come to light.
            if (($cards_chosen_so_far == 0 && $num_cards_in_location_from <= $n_max && !$can_pass) || ($cards_chosen_so_far > 0 && $n_min >= $num_cards_in_location_from)) {
                $selection_will_reveal_hidden_information = false;
            }

            // There is no selectable card
            if ($selection_size == 0) {
                if (($splay_direction == -1 && ($can_pass || $n_min <= 0)) && ($selection_will_reveal_hidden_information || ($num_cards_in_location_from > 0 && $autoselection_mode == 0))) {
                    // The player can pass or stop and the opponents can't know that the player has no eligible card
                    // This can happen for example in the Masonry effect

                    // No automatic pass or stop: the only choice the player will have in client side is to pass/stop
                    // This way the other players won't get the information that the player was forced to pass/stop
                    self::trace('preSelectionMove->selectionMove (player has to pass)');
                    $this->gamestate->nextState('selectionMove');
                    self::giveExtraTime($player_id);
                    return;
                }

                // The player passes or stops automatically
                if ($this->innovationGameState->get('reveal_if_unable')) {
                    self::revealLocation($owner_from, $location_from, /*forProvingPurposes*/ true);
                } else {
                    self::notifyNoSelectableCards();
                }
                self::trace('preSelectionMove->interInteractionStep (no card)');
                $this->gamestate->nextState('interInteractionStep');
                return;
            }

            // Color must be splayed and there is only one choice
            if ($autoselection_mode >= 1 && !$can_pass && $splay_direction >= 0 && count($colors) === 1) {
                // A card is chosen automatically for the player
                $card = self::getSelectedCards()[0];
                // Simplified version of self::choose()
                $this->innovationGameState->set('id_last_selected', $card['id']);
                self::unmarkAsSelected($card['id']);
                $this->innovationGameState->set('can_pass', 0);
                self::trace('preSelectionMove->interSelectionMove (automated splay selection)');
                $this->gamestate->nextState('interSelectionMove');
                return;
            }

            // All selectable cards must be chosen
            if (
                $autoselection_mode >= 1
                // Make sure choosing these cards won't reveal hidden information (unless all cards in that location need to be chosen anyway)
                && (!$selection_will_reveal_hidden_information || self::countCardsInLocation($owner_from, $location_from) <= $selection_size)
                // The player must choose at least all of the selectable cards
                && (($cards_chosen_so_far == 0 && !$can_pass && $selection_size <= $n_min) || ($cards_chosen_so_far > 0 && $n_min >= $selection_size))
                // If there's more than one selectable card, only automate the choices if the order does not matter
                && ($selection_size == 1 || ((!$refresh_selection || $autoselection_mode == 2) && $location_to != 'board' && $location_to != 'deck' && $location_to != 'revealed,deck' && $location_to != 'safe'))
            ) {
                // A card is chosen automatically for the player
                $card = self::getSelectedCards()[0];
                // Simplified version of self::choose()
                $this->innovationGameState->set('id_last_selected', $card['id']);
                self::unmarkAsSelected($card['id']);
                $this->innovationGameState->set('can_pass', 0);
                self::trace('preSelectionMove->interSelectionMove (automated card selection)');
                $this->gamestate->nextState('interSelectionMove');
                return;
            }

            // Try to return cards to the deck where the order doesn't matter
            if (
                (($autoselection_mode >= 1 && !$refresh_selection) || ($autoselection_mode == 2))
                // Make sure choosing these cards won't reveal hidden information
                && (!$selection_will_reveal_hidden_information)
                // The player must choose at least all of the selectable cards
                && (($cards_chosen_so_far == 0 && !$can_pass && $selection_size <= $n_min) || ($cards_chosen_so_far > 0 && $n_min >= $selection_size))
                // There must be at least one card which goes to a unique supply pile
                && $card_id_returning_to_unique_supply_pile != null
                // The cards are coming from anywhere but the board (unless we are playing with the 4th edition because relevant special achievement checks were moved to end of the action)
                && ($location_from != 'board' || $this->innovationGameState->usingFourthEditionRules())
            ) {
                $this->innovationGameState->set('id_last_selected', $card_id_returning_to_unique_supply_pile);
                self::unmarkAsSelected($card_id_returning_to_unique_supply_pile);
                $this->innovationGameState->set('can_pass', 0);
                self::trace('preSelectionMove->interSelectionMove (automated card selection)');
                $this->gamestate->nextState('interSelectionMove');
                return;
            }

            // Try to tuck cards where the order doesn't matter
            if (
                (($autoselection_mode >= 1 && !$refresh_selection) || ($autoselection_mode == 2))
                // Make sure choosing these cards won't reveal hidden information
                && (!$selection_will_reveal_hidden_information)
                // The player must choose at least all of the selectable cards
                && (($cards_chosen_so_far == 0 && !$can_pass && $selection_size <= $n_min) || ($cards_chosen_so_far > 0 && $n_min >= $selection_size))
                // The cards are being tucked (unless we are playing with the 4th edition because relevant special achievement checks were moved to end of the action)
                && ($bottom_to > 0 || $this->innovationGameState->usingFourthEditionRules())
                // There must be at least one card which has a unique color
                && $card_id_with_unique_color != null
            ) {
                $this->innovationGameState->set('id_last_selected', $card_id_with_unique_color);
                self::unmarkAsSelected($card_id_with_unique_color);
                $this->innovationGameState->set('can_pass', 0);
                self::trace('preSelectionMove->interSelectionMove (automated card selection)');
                $this->gamestate->nextState('interSelectionMove');
                return;
            }

            // There are selectable cards, but not enough to fulfill the requirement ("May effects only")
            if ($n_min < 800 && $selection_size < $n_min) {
                if ($this->innovationGameState->get('solid_constraint') == 1) {
                    self::notifyGeneralInfo(clienttranslate("There are not enough cards to fulfill the condition."));
                    self::deselectAllCards();
                    self::trace('preSelectionMove->interInteractionStep (not enough cards)');
                    $this->gamestate->nextState('interInteractionStep');
                    return;
                } else {
                    // Reduce n_min and n_max to the selection size
                    $this->innovationGameState->set('n_min', $selection_size);
                    $this->innovationGameState->set('n_max', $selection_size);
                }

                // Reduce n_max to the selection size (Globe is an exception because it would reveal hidden info)
            } else if ($n_max < 800 && $selection_size < $n_max && !$selection_will_reveal_hidden_information) {
                $this->innovationGameState->set('n_max', $selection_size);
            }
        } else if ($special_type_of_choice == 1) { // choose_from_list
            $choice_array = $this->innovationGameState->getAsArray('choice_array');
            // Automatically choose the value if there's only one option (and passing isn't allowed)
            if (count($choice_array) == 1 && !$can_pass) {
                $this->innovationGameState->set('choice', $choice_array[0]);
                self::trace('preSelectionMove->interSelectionMove (only one choice)');
                $this->gamestate->nextState('interSelectionMove');
                return;
            }
        } else if ($special_type_of_choice == 3) { // choose_value
            $age_array = $this->innovationGameState->getAsArray('age_array');
            // Automatically choose the value if there's only one option (and passing isn't allowed)
            if (count($age_array) == 1 && !$can_pass) {
                $this->innovationGameState->set('choice', $age_array[0]);
                self::trace('preSelectionMove->interSelectionMove (only one value)');
                $this->gamestate->nextState('interSelectionMove');
                return;
            }
        } else if ($special_type_of_choice == 4) { // choose_color
            $color_array = $this->innovationGameState->getAsArray('color_array');
            // Automatically choose the color if there's only one option (and passing isn't allowed)
            if (count($color_array) == 1 && !$can_pass) {
                $this->innovationGameState->set('choice', $color_array[0]);
                self::trace('preSelectionMove->interSelectionMove (only one color)');
                $this->gamestate->nextState('interSelectionMove');
                return;
            }
        } else if ($special_type_of_choice == 10) { // choose_player
            $player_array = $this->innovationGameState->getAsArray('player_array');
            // Automatically choose the player if there's only one option (and passing isn't allowed)
            if (count($player_array) == 1 && !$can_pass) {
                $this->innovationGameState->set('choice', self::playerIndexToPlayerId($player_array[0]));
                self::trace('preSelectionMove->interSelectionMove (only one player)');
                $this->gamestate->nextState('interSelectionMove');
                return;
            }
        } else if ($special_type_of_choice == 12) { // choose_icon_type
            $icon_array = $this->innovationGameState->getAsArray('icon_array');
            // Automatically choose the icon if there's only one option (and passing isn't allowed)
            if (count($icon_array) == 1 && !$can_pass) {
                $this->innovationGameState->set('choice', $icon_array[0]);
                self::trace('preSelectionMove->interSelectionMove (only one icon)');
                $this->gamestate->nextState('interSelectionMove');
                return;
            }
        }

        // Let the player make his choice
        self::trace('preSelectionMove->selectionMove');
        $this->gamestate->nextState('selectionMove');
        self::giveExtraTime($player_id);
    }

    function getSelectedCardIdBelongingToUniqueSupplyPile()
    {
        return self::getUniqueValueFromDB("
            SELECT
                id
            FROM
                card AS a
            LEFT JOIN(
                SELECT
                    COUNT(*) AS size, age, type, is_relic
                FROM
                    card
                WHERE
                    selected
                GROUP BY
                    age, type, is_relic
            ) AS b
            ON
                a.age = b.age AND a.type = b.type AND a.is_relic = b.is_relic
            WHERE
                selected AND b.size = 1
            ORDER BY
                a.location, a.age, a.type, a.is_relic, a.position
            LIMIT 1
        ");
    }

    function getSelectedCardIdWithUniqueColor()
    {
        return self::getUniqueValueFromDB("
            SELECT
                id
            FROM
                card AS a
            LEFT JOIN(
                SELECT
                    COUNT(*) AS size, color
                FROM
                    card
                WHERE
                    selected
                GROUP BY
                    color
            ) AS b
            ON
                a.color = b.color
            WHERE
                selected AND b.size = 1
            ORDER BY
                a.location, a.color, a.position
            LIMIT 1
        ");
    }

    function stInterSelectionMove()
    {
        $player_id = self::getCurrentPlayerUnderDogmaEffect();
        $special_type_of_choice = $this->innovationGameState->get('special_type_of_choice');
        if ($special_type_of_choice == 0) { // The player was prompted to choose a card
            $selected_card_id = $this->innovationGameState->get('id_last_selected');
            if ($selected_card_id == -1) {
                // Unset the selection
                self::deselectAllCards();

                // Indicate that the player decided not to return a card in order to share in an effect
                if (self::getPlayerTableColumn($player_id, 'distance_rule_share_state') == 1) {
                    self::setPlayerTableColumn($player_id, 'distance_rule_share_state', 2);
                    self::notifyPlayer($player_id, 'log', clienttranslate('${You} choose not to return a card from your hand in order to share the effect.'), array('You' => 'You'));
                    self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} chooses not to return a card from his hand in order to share the effect.'), array('player_name' => self::getPlayerNameFromId($player_id)));
                    // Skip sharing
                    self::trace('interSelectionMove->interPlayerInvolvedTurn');
                    $this->gamestate->nextState('interPlayerInvolvedTurn');
                    return;
                }

                // Indicate that the player decided not to return a card in order to avoid a demand
                if (self::getPlayerTableColumn($player_id, 'distance_rule_demand_state') == 1) {
                    self::setPlayerTableColumn($player_id, 'distance_rule_demand_state', 2);
                    self::notifyPlayer($player_id, 'log', clienttranslate('${You} choose not to return a card from your hand in order to avoid the demand.'), array('You' => 'You'));
                    self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} chooses not to return a card from his hand in order to avoid the demand.'), array('player_name' => self::getPlayerNameFromId($player_id)));
                    // Return to demand
                    self::trace('interSelectionMove->playerInvolvedTurn');
                    $this->gamestate->nextState('playerInvolvedTurn');
                    return;
                }

                // The player passed or stopped
                if ($this->innovationGameState->get('can_pass') == 1) {
                    self::notifyPass($player_id);
                }

                self::trace('interSelectionMove->interInteractionStep');
                $this->gamestate->nextState('interInteractionStep');
                return;
            }

            // The player has chosen one card
            $card = self::getCardInfo($selected_card_id);

            // Flags
            $owner_to = $this->innovationGameState->get('owner_to');
            $location_to = Locations::decode($this->innovationGameState->get('location_to'));
            $bottom_to = $this->innovationGameState->get('bottom_to');
            $score_keyword = $this->innovationGameState->get('score_keyword') == 1;
            $meld_keyword = $this->innovationGameState->get('meld_keyword') == 1;
            $achieve_keyword = $this->innovationGameState->get('achieve_keyword') == 1;
            $draw_keyword = $this->innovationGameState->get('draw_keyword') == 1;
            $safeguard_keyword = $this->innovationGameState->get('safeguard_keyword') == 1;
            $return_keyword = $this->innovationGameState->get('return_keyword') == 1;
            $foreshadow_keyword = $this->innovationGameState->get('foreshadow_keyword') == 1;

            $splay_direction = $this->innovationGameState->get('splay_direction'); // -1 if that was not a choice for splay
        } else { // The player had to make a special choice
            $choice = $this->innovationGameState->get('choice');
            if ($choice == -2) {
                // The player passed
                self::notifyPass($player_id);
                self::trace('interSelectionMove->interInteractionStep');
                $this->gamestate->nextState('interInteractionStep');
                return;
            }
        }

        $nested_card_state = self::getCurrentNestedCardState();

        // There won't be any nested card state if a player is doing an interaction outside of the context of a dogma
        if ($nested_card_state == null) {
            $card_id = null;
            $launcher_id = $player_id;
            $current_effect_type = -1;
            $current_effect_number = -1;
            $code = null;
        } else {
            $card_id = $nested_card_state['card_id'];
            $launcher_id = $nested_card_state['launcher_id'];
            $current_effect_type = $nested_card_state['current_effect_type'];
            $current_effect_number = $nested_card_state['current_effect_number'];
            // Echo effects are sometimes executed on cards other than the card being dogma'd
            if ($current_effect_type == 3) {
                $nesting_index = $nested_card_state['nesting_index'];
                $card_id = self::getUniqueValueFromDB(self::format("SELECT card_id FROM echo_execution WHERE nesting_index = {nesting_index} AND execution_index = {effect_number}", array('nesting_index' => $nesting_index, 'effect_number' => $current_effect_number)));
            }
            $step = self::getStep();
            $code = self::getCardExecutionCodeWithLetter($card_id, $current_effect_type, $current_effect_number, $step);
        }

        try {

            if ($special_type_of_choice != 0 && $code !== null && self::isInSeparateFile($card_id)) {
                $executionState = (new ExecutionState($this))
                    ->setEdition($this->innovationGameState->getEdition())
                    ->setLauncherId($launcher_id)
                    ->setPlayerId($player_id)
                    ->setEffectType($current_effect_type)
                    ->setEffectNumber($current_effect_number)
                    ->setCurrentStep(self::getStep())
                    ->setNextStep(self::getStep() + 1)
                    ->setMaxSteps(self::getStepMax());
                self::getCardInstance($card_id, $executionState)->handleSpecialChoice(intval($choice));
                self::setStepMax($executionState->getMaxSteps());
                self::setStep($executionState->getNextStep() - 1);
            }

            switch ($code) {
                // The first number is the id of the card
                // D1 means the first (and single) I demand effect
                // C1 means the first (and single) I compel effect
                // N1 means the first non-demand effect
                // N2 means the second non-demand effect
                // N3 means the third non-demand effect
                // E1 means the first (and single) echo effect

                // The letter indicates the step : A for the first one, B for the second

                // Default behaviour: make the transfer or the splay as stated in B

                // id 499, Unseen age 2: Cipher
                case "499N1A":
                    $max_value_selected_so_far = self::getAuxiliaryValue();
                    if ($card['age'] > $max_value_selected_so_far) {
                        self::setAuxiliaryValue($card['age']);
                    }
                    self::returnCard($card);
                    break;

                default:
                    if ($special_type_of_choice == 0) {
                        if ($code !== null) {
                            $executionState = (new ExecutionState($this))
                                ->setEdition($this->innovationGameState->getEdition())
                                ->setLauncherId($launcher_id)
                                ->setPlayerId($player_id)
                                ->setEffectType($current_effect_type)
                                ->setEffectNumber($current_effect_number)
                                ->setCurrentStep(self::getStep())
                                ->setNextStep(self::getStep() + 1)
                                ->setMaxSteps(self::getStepMax())
                                ->setNumChosen($this->innovationGameState->get('n') + 1);
                        }

                        if ($splay_direction == -1) {
                            if ($code !== null) {
                                $this->innovationGameState->set("age_last_selected", $card['age'] ?? -1);
                                $this->innovationGameState->set("color_last_selected", $card['color'] ?? -1);
                                $this->innovationGameState->set("owner_last_selected", $card['owner']);
                            }
                            if ($code !== null && self::isInSeparateFile($card_id) && self::getCardInstance($card_id, $executionState)->executeCardTransfer(self::getCardInfo($selected_card_id))) {
                                // Do nothing since the card transfer was overridden
                            } else if ($location_to == Locations::REVEALED_THEN_HAND) {
                                $card = self::transferCardFromTo($card, $owner_to, Locations::REVEALED);
                                self::transferCardFromTo($card, $owner_to, Locations::HAND);
                            } else if ($location_to == Locations::REVEALED_THEN_DECK) {
                                $card = self::transferCardFromTo($card, $owner_to, Locations::REVEALED);
                                self::returnCard($card);
                            } else if ($location_to == Locations::REVEALED_THEN_SCORE) {
                                $card = self::transferCardFromTo($card, $owner_to, Locations::REVEALED);
                                self::scoreCard($card, $owner_to);
                            } else if ($location_to == Locations::JUNK_THEN_SAFEGUARD) {
                                $card = self::junkCard($card);
                                self::safeguardCard($card, $owner_to);
                            } else {
                                // TODO(LATER): Figure out if 'bottom_from' should be included here too.
                                self::transferCardFromTo(
                                    $card,
                                    $owner_to,
                                    $location_to,
                                    [
                                        'bottom_to'          => $bottom_to,
                                        'score_keyword'      => $score_keyword,
                                        'meld_keyword'       => $meld_keyword,
                                        'achieve_keyword'    => $achieve_keyword,
                                        'draw_keyword'       => $draw_keyword,
                                        'safeguard_keyword'  => $safeguard_keyword,
                                        'return_keyword'     => $return_keyword,
                                        'foreshadow_keyword' => $foreshadow_keyword,
                                    ]
                                );
                            }
                            if ($code !== null && self::isInSeparateFile($card_id)) {
                                self::getCardInstance($card_id, $executionState)->handleCardChoice(self::getCardInfo($selected_card_id));
                                self::setStepMax($executionState->getMaxSteps());
                                self::setStep($executionState->getNextStep() - 1);
                            }
                        } else {
                            // Do the splay as stated in B
                            $this->innovationGameState->set("color_last_selected", $card['color']);
                            $did_splay = self::splay($player_id, $card['owner'], $card['color'], $splay_direction, /*force_unsplay=*/ $splay_direction == 0);
                            if ($code !== null && self::isInSeparateFile($card_id)) {
                                self::getCardInstance($card_id, $executionState)->handleSplayChoice($card['color'], $did_splay);
                                self::setStepMax($executionState->getMaxSteps());
                                self::setStep($executionState->getNextStep() - 1);
                            }
                        }
                    } else if ($card_id === null) { // Digging/stealing artifact
                        $card_ids = self::getAuxiliaryArray();
                        $chosen_card = self::getCardInfo($card_ids[$choice]);
                        if ($choice == 0) {
                            self::digCard($chosen_card, $player_id);
                            self::incStat(1, 'dig_events_number', $player_id);
                        } else {
                            // If an artifact was stolen from an opponent's museum, rotate the museum and the artifact
                            $museum = self::getCardsInLocation($chosen_card['owner'], Locations::MUSEUMS)[$chosen_card['position'] - 1];
                            self::transferCardFromTo($museum, $player_id, Locations::MUSEUMS);
                            self::transferCardFromTo($chosen_card, $player_id, Locations::MUSEUMS);
                        }
                    } else if (!self::isInSeparateFile($card_id)) {
                        throw new BgaVisibleSystemException(self::format(self::_("Unhandled case in {function}: '{code}'"), array('function' => "stInterSelectionMove()", 'code' => $code)));
                    }
                    break;
            }
        } catch (EndOfGame $e) {
            // End of the game: the exception has reached the highest level of code
            self::trace('EOG bubbled from self::stInterSelectionMove');
            self::trace('interSelectionMove->justBeforeGameEnd');
            $this->gamestate->nextState('justBeforeGameEnd');
            return;
        }

        if ($special_type_of_choice == 0) {
            // Mark extra information about this chosen card
            // TODO(LATER): Remove this once it becomes redundant with the same 3 lines above (once all cards are in separate files)
            $this->innovationGameState->set("age_last_selected", $card['age'] ?? -1);
            $this->innovationGameState->set("color_last_selected", $card['color'] ?? -1);
            $this->innovationGameState->set("owner_last_selected", $card['owner']);

            // Indicate that the player decided to return a card in order to avoid a demand
            if (self::getPlayerTableColumn($player_id, 'distance_rule_demand_state') == 1) {
                self::deselectAllCards();
                self::setPlayerTableColumn($player_id, 'distance_rule_demand_state', 3);
                // Skip demand
                self::notifyPlayer($player_id, 'log', clienttranslate('${You} returned a card from your hand in order to avoid the demand.'), array('You' => 'You'));
                self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} returned a card from his hand in order to avoid the demand.'), array('player_name' => self::getPlayerNameFromId($player_id)));
                self::trace('interSelectionMove->interPlayerInvolvedTurn');
                $this->gamestate->nextState('interPlayerInvolvedTurn');
                return;
            }

            // Indicate that the player decided to return a card in order to share in an effect
            if (self::getPlayerTableColumn($player_id, 'distance_rule_share_state') == 1) {
                self::deselectAllCards();
                self::setPlayerTableColumn($player_id, 'distance_rule_share_state', 3);
                self::notifyPlayer($player_id, 'log', clienttranslate('${You} returned a card from your hand in order to share the effect.'), array('You' => 'You'));
                self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} returned a card from his hand in order to share the effect.'), array('player_name' => self::getPlayerNameFromId($player_id)));
                // Return to effect
                self::trace('interSelectionMove->playerInvolvedTurn');
                $this->gamestate->nextState('playerInvolvedTurn');
                return;
            }

            // Mark that one more card has been chosen and proceeded in that step
            $this->innovationGameState->increment('n');
            $this->innovationGameState->increment('n_min', -1);
            $this->innovationGameState->increment('n_max', -1);
        }

        // Stop if no more choices should be made
        if ($special_type_of_choice != 0 || $this->innovationGameState->get('n_max') == 0) {
            // Unset the selection
            self::deselectAllCards();
            // End of this interaction step
            self::trace('interSelectionMove->interInteractionStep');
            $this->gamestate->nextState('interInteractionStep');
            return;
        }

        // Refresh selection, if prompted by the card
        if ($this->innovationGameState->get('refresh_selection') == 1) {
            $compact_options = self::getCardInstance($card_id, $executionState)->updateInteractionOptions();
            $options = self::expandInteractionOptions($compact_options, $player_id, /*is_refreshing_options=*/ true);
            self::setSelectionRange($options, /*is_refreshing_options=*/ true);
            self::trace('interSelectionMove->preSelectionMove');
            $this->gamestate->nextState('preSelectionMove');
            return;
        }

        $this->innovationGameState->set('can_pass', 0); // Passing is no longer possible (stopping will be if n_min == 0)
        self::trace('interSelectionMove->preSelectionMove');
        $this->gamestate->nextState('preSelectionMove');
    }

    function stJustBeforeGameEnd()
    {
        switch ($this->innovationGameState->get('game_end_type')) {
            case 0: // achievements
                self::notifyEndOfGameByAchievements();
                self::setStat(true, 'end_achievements');
                self::notifyAll('endOfGame', '', array('end_of_game_type' => 'achievements'));
                break;
            case 1: // score
                // Important value for winning is no more the number of achievements but the score
                // Promote player score to BGA score
                // Keeping the number of achivement as BGA auxiliary score as tie breaker
                self::promoteScoreToBGAScore();
                self::notifyEndOfGameByScore();
                self::setStat(true, 'end_score');
                self::notifyAll('endOfGame', '', array('end_of_game_type' => 'score'));
                break;
            case -1: // dogma
                // In that case, the score is modified so that the winner team got 1, the losers 0, there is no tie breaker
                self::binarizeBGAScore();
                self::notifyEndOfGameByDogma();
                self::setStat(true, 'end_dogma');
                self::notifyAll('endOfGame', '', array('end_of_game_type' => 'dogma'));
                break;
            default:
                break;
        }

        self::trace('justBeforeGameEnd->gameEnd');
        $this->gamestate->nextState(); // End the game
    }

    /*
        zombieTurn:

        This method is called each time it is the turn of a player who has quit the game (= "zombie" player).
        You can do whatever you want in order to make sure the turn of this player ends appropriately
        (ex: pass).
    */
    function zombieTurn($state, $active_player)
    {
        throw new feException("Zombie mode not supported at this moment");
    }

    function isZombie($player_id)
    {
        return self::getUniqueValueFromDB(self::format("
            SELECT player_zombie FROM player WHERE player_id={player_id}
        ", array('player_id' => $player_id)));
    }
}
