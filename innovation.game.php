<?php
 /**
  *------
  * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
  * Innovation implementation : © Jean Portemer <jportemer@gmail.com>
  * 
  * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
  * See http://en.boardgamearena.com/#!doc/Studio for more information.
  * -----
  * 
  * innovation.game.php
  *
  * This is the main file for your game logic.
  *
  * In this PHP file, you are going to defines the rules of the game.
  *
  */

require_once(APP_GAMEMODULE_PATH.'module/table/table.game.php');
require_once('modules/Innovation/Cards/Card.php');
require_once('modules/Innovation/Cards/ExecutionState.php');
require_once('modules/Innovation/GameState.php');
require_once('modules/Innovation/Enums/CardIds.php');
require_once('modules/Innovation/Enums/CardTypes.php');
require_once('modules/Innovation/Enums/Colors.php');
require_once('modules/Innovation/Enums/Directions.php');
require_once('modules/Innovation/Enums/Icons.php');
require_once('modules/Innovation/Utils/Arrays.php');
require_once('modules/Innovation/Utils/Notifications.php');
require_once('modules/Innovation/Utils/Strings.php');


use Innovation\GameState;
use Innovation\Cards\ExecutionState;
use Innovation\Utils\Arrays;
use Innovation\Enums\CardIds;
use Innovation\Enums\CardTypes;
use Innovation\Enums\Colors;
use Innovation\Enums\Directions;
use Innovation\Enums\Icons;
use Innovation\Utils\Notifications;
use Innovation\Utils\Strings;

/* Exception to be called when the game must end */
class EndOfGame extends Exception {}

class Innovation extends Table
{

    /** @var GameState An inverted control structure for accessing game state in a testable manner */
    public GameState $innovationGameState;

    /** @var Notifications Used to help create notifications */
    public Notifications $notifications;

    // Effect types
    const DEMAND_EFFECT = 0;
    const NON_DEMAND_EFFECT = 1;
    const COMPEL_EFFECT = 2;
    const ECHO_EFFECT = 3;

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
        // NOTE: The following values are unused and safe to use: 20-22, 24-25, 59-67, 90-92
        self::initGameStateLabels(array(
            'number_of_achievements_needed_to_win' => 10,
            'turn0' => 11,
            'first_player_with_only_one_action' => 12,
            'second_player_with_only_one_action' => 13,
            'has_second_action' => 14,
            'game_end_type' => 15,
            'player_who_could_not_draw' => 16,
            'winner_by_dogma' => 17,
            'active_player' => 18,
            'endorse_action_state' => 19, // 0 = not allowed to do an endorse action, 1 = allowed to do an endorse action, 2 = currently executing an effect for the 1st time, 3 = currently executing an effect for the 2nd time
            'sharing_bonus' => 23,
            'special_type_of_choice' => 26,
            'choice' => 27,
            'can_pass' => 28,
            'n_min' => 29,
            'n_max' => 30,
            // TODO(LATER): Deprecate and remove 'solid_constraint'. But wait until we finish implementing 4th edition before deciding we do not need this.
            'solid_constraint' => 31,
            'splay_direction' => 32,
            'owner_from' => 33,
            'location_from' => 34,
            'owner_to' => 35,
            'location_to' => 36,
            'bottom_to' => 37,
            'age_min' => 38,
            'age_max' => 39,
            'color_array' => 40,
            'with_icon' => 41, // TODO(LATER): Remove this. It's now stored in with_icons instead.
            'with_icons' => 52,
            'without_icon' => 42, // TODO(LATER): Remove this. It's now stored in without_icons instead.
            'without_icons' => 53,
            'not_id' => 43,
            'n' => 44,
            'id_last_selected' => 45,
            'age_last_selected' => 46,
            'color_last_selected' => 47,
            'score_keyword' => 48,
            'meld_keyword' => 50,
            'achieve_keyword' => 54,
            'draw_keyword' => 55, // TODO(4E): Remove this if it doesn't end up being used
            'safeguard_keyword' => 56,
            'return_keyword' => 57,
            'foreshadow_keyword' => 58,
            'limit_shrunk_selection_size' => 68, // Whether the safe/forecast limit shrunk the selection size (1 means it was shrunk)
            'card_id_1' => 69,
            'card_id_2' => 70,
            'card_id_3' => 71,
            'require_achievement_eligibility' => 72,
            'has_demand_effect' => 73,
            'has_splay_direction' => 74,
            'owner_last_selected' => 75,
            'type_array' => 76,
            'icon_array' => 49,
            'age_array' => 51,
            'choice_array' => 77,
            'player_array' => 78,
            'icon_hash_1' => 79,
            'icon_hash_2' => 80,
            'icon_hash_3' => 81,
            'icon_hash_4' => 82,
            'icon_hash_5' => 83,
            'enable_autoselection' => 84,
            'include_relics' => 85,
            'bottom_from' => 86,
            'with_bonus' => 87,
            'without_bonus' => 88,
            'card_ids_are_in_auxiliary_array' => 89,
            
            'foreseen_card_id' => 93, // ID of the card which was foreseen
            'melded_card_id' => 94, // ID of the card which was melded
            'relic_id' => 95, // ID of the relic which may be seized
            'current_action_number' => 96, // -1 = none, 0 = free action, 1 = first action, 2 = second action
            'current_nesting_index' => 97, // 0 refers to the originally executed card, 1 refers to a card exexcuted by that initial card, etc.
            'release_version' => 98, // Used to help release new versions of the game without breaking existing games (3 = Cities, 4 = 4th edition base game, 5 = 4th edition Unseen)
            'debug_mode' => 99, // 0 for disabled, 1 for enabled
            
            'game_type' => 100, // 1 for normal game, 2/3/4/5 for team game
            'game_rules' => 101, // 1 for third edition, 2 for first edition, 3 for fourth edition
            'artifacts_mode' => 102, // 1 for "Disabled", 2 for "Enabled without Relics", 3 for "Enabled with Relics"
            'cities_mode' => 103, // 1 for "Disabled", 2 for "Enabled"
            'echoes_mode' => 104, // 1 for "Disabled", 2 for "Enabled"
            'unseen_mode' => 106, // 1 for "Disabled", 2 for "Enabled"
            'extra_achievement_to_win' => 110 // 1 for "Disabled", 2 for "Enabled"
        ));
    }
    
    protected function getGameName()
    {
        return "innovation";
    }

    // TODO(4E): Simulate migration.
    function upgradeTableDb($from_version) {
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

        // TODO(4E): Update what we are using to compare from_version. 
        if ($from_version <= 2308231318) {
            self::initGameStateLabels(array(
                'achieve_keyword' => 54,
                'draw_keyword' => 55,
                'safeguard_keyword' => 56,
                'return_keyword' => 57,
                'foreshadow_keyword' => 58,
            ));
            $this->innovationGameState->set('achieve_keyword', -1);
            $this->innovationGameState->set('draw_keyword', -1);
            $this->innovationGameState->set('safeguard_keyword', -1);
            $this->innovationGameState->set('return_keyword', -1);
            $this->innovationGameState->set('foreshadow_keyword', -1);
        }

        // TODO(4E): Update what we are using to compare from_version. 
        if ($from_version <= 2307142341) {
            self::initGameStateLabels(array(
                'limit_shrunk_selection_size' => 68,
                'foreseen_card_id' => 93,
                'with_icons' => 52,
                'without_icons' => 53,
            ));
            // TODO(4E): Is there a way to make the deployment smoother? Right now this will break a lot of cards when it's deployed.
            $this->innovationGameState->set('limit_shrunk_selection_size', -1);
            // $with_icon = $this->innovationGameState->get('with_icon');
            // if ($with_icon > 0) {
            //     $this->innovationGameState->set('with_icons', Arrays::getArrayAsValue([$with_icon]));
            // } else {
            $this->innovationGameState->set('with_icons', Arrays::getArrayAsValue([]));
            // }
            // $without_icon = $this->innovationGameState->get('without_icon');
            // if ($without_icon > 0) {
            //     $this->innovationGameState->set('without_icons', Arrays::getArrayAsValue([$without_icon]));
            // } else {
            $this->innovationGameState->set('without_icons', Arrays::getArrayAsValue([]));
            // }
        }

        // TODO(4E): Update what we are using to compare from_version. 
        if ($from_version <= 2302100853) {
            self::initGameStateLabels(array(
                'icon_array' => 49,
                'choice_array' => 51,
            ));
            $this->innovationGameState->set('icon_array', Arrays::getArrayAsValue([1,2,3,4,5,6]));
        }
        // TODO(LATER): Remove this.
        if ($from_version <= 2303050253) {
            self::initGameStateLabels(array(
                'meld_keyword' => 50,
            ));
            $this->innovationGameState->set('meld_keyword', -1);
        }
        if ($from_version <= 2302100853) {
            self::initGameStateLabels(array(
                'endorse_action_state' => 19,
            ));
            $this->innovationGameState->set('endorse_action_state', 0);
        }
    }

    function debug_transfer($card_id, $action) {
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
                throw new BgaUserException("Unsupported debug action: ".$action);
        }
    }
    function debug_transfer_all($location_from, $location_to) {
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

    function debug_splay($color, $direction) {
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
        
        foreach($players as $player_id => $player) {
            $color = $default_colors[$t]; // There is a blue team and a red team: preferences of players on colors are disabled
            $values[$player_id] = "('".$player_id."','$color','".$player['player_canal']."','".addslashes($player['player_name'])."','".addslashes($player['player_avatar'])."',".($t+1).")";
            if ($individual_game) {
                $t++;
            }
            else { // Team game: the players of the same team are sitting across from each other
                $t = ($t+1) % 2;
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
        $this->innovationGameState->setInitial('enable_autoselection', -1); // 1 if cards are allowed to be autoselected during an interaction
        $this->innovationGameState->setInitial('include_relics', -1); // 1 if relics cards are allowed to be selected during an interaction
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
        $this->innovationGameState->setInitial('limit_shrunk_selection_size', -1); // Whether the safe/forecast limit shrunk the selection size (1 means it was shrunk)
        
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
                self::DbQuery("UPDATE card SET spot_6 = 14 WHERE id = 288"); // Montreal
                self::DbQuery("UPDATE card SET spot_6 = 14 WHERE id = 289"); // London
                self::DbQuery("UPDATE card SET spot_3 = 14 WHERE id = 295"); // Chongqing
                self::DbQuery("UPDATE card SET spot_6 = 9 WHERE id = 299"); // Hamburg
                self::DbQuery("UPDATE card SET spot_3 = 14 WHERE id = 313"); // Hong Kong
                self::DbQuery("UPDATE card SET spot_6 = 14 WHERE id = 314"); // Moscow
                self::DbQuery("UPDATE card SET spot_6 = 9 WHERE id = 315"); // Bangalore
                self::DbQuery("UPDATE card SET spot_2 = 110, spot_6 = 6 WHERE id = 316"); // Atlanta
                self::DbQuery("UPDATE card SET spot_1 = 110, spot_6 = 5 WHERE id = 317"); // Singapore
                self::DbQuery("UPDATE card SET spot_1 = 2, spot_2 = 6, spot_4 = 6, spot_6 = 9 WHERE id = 318"); // Seoul
                self::DbQuery("UPDATE card SET spot_6 = 9 WHERE id = 319"); // Tel Aviv
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
    protected function getAllDatas() {
        $result = array();
        
        $result['debug_mode'] = $this->innovationGameState->get('debug_mode');

        // Get static information about all cards
        $cards  = array();
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
    
        $current_player_id = self::getCurrentPlayerId();    // !! We must only return information visible by this player !!
        
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
        foreach($players as $player_id => $player) {
            $result['board_splay_directions'][$player_id] = array();
            $result['board_splay_directions_in_clear'][$player_id] = array();
            for($color = 0; $color < 5 ; $color++) {
                $direction = self::getCurrentSplayDirection($player_id, $color);
                $result['board_splay_directions'][$player_id][] = $direction;
                $result['board_splay_directions_in_clear'][$player_id][] = Directions::render($direction);
            }
        }

        // Artifacts on display
        $result['artifacts_on_display'] = self::getArtifactsOnDisplay($players);

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
        foreach($players as $player_id => $player) {
            $result['revealed'][$player_id] = self::getCardsInLocation($player_id, 'revealed');
        }

        // Unclaimed relics
        $result['unclaimed_relics'] = self::getCardsInLocation(0, 'relics');
        
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
                    self::format("SELECT card_id FROM echo_execution WHERE nesting_index = {nesting_index} AND execution_index = {effect_number}",
                        array('nesting_index' => $nesting_index, 'effect_number' => $current_effect_number)));
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
        switch($current_state['name']) {
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
        for($age=1; $age<=10; $age++) {
            $n = $number_of_cards_in_decks[$age];
            switch($age) {
            case 1:
                $n_max  = 14 - 2 * count($players); // number of cards in the deck at the beginning: 14 (15 minus one taken for achievement) minus the cards dealt to the players at the beginning
                $w = 1; // weight for cards of age 1: 1
                break;
            case 10:
                $n++; // one more "virtual" card because the game is not over where the tenth age 10 card is drawn (but quite...)
                $n_max = 11; // number of cards in the deck at the beginning: 10, +1 one more "virtual" card because the game is not over when the last is drawn
                $w = 6; // weight for cards of age 10: 6
                break;
            default:
                $n_max = 9; // number of cards in the deck at the beginning: 9 (10 minus one taken for achievement)
                $w = ($age-1) / 4 + 1; // weight between 1.25 (for age 2) and 3 (for age 9)
                break;
            };
            $weight += ($n_max - $n) * $w; // What is really important are the cards already drawn
            $total_weight += $n_max  * $w;
        }
        $progression_in_cards = $weight/$total_weight;
        
        // Progression of players
        // This is the ratio between the number of achievements the player have got so far and the number of achievements needed to win the game
        $progression_of_players = array();
        $n_max = $this->innovationGameState->get('number_of_achievements_needed_to_win');
        foreach($players as $player_id=>$player) {
            $n = self::getPlayerNumberOfAchievements($player_id);
            $progression_of_players[] = $n/$n_max;
        }
        
        // If any of the above progression was 100%, the game would be over. So, 100% is a kind of "absorbing" element. So,the method is to multiply the complements of the progression.
        // A complement is defined as 100% - progression
        $complement = 1 - $progression_in_cards;
        foreach($progression_of_players as $progression) {
            $complement *= 1 - $progression;
        }
        $final_progression = 1 - $complement;
        
        // Convert the final result in percentage
        $percentage = intval(100 * $final_progression);
        $percentage = min(max(1, $percentage), 99); // Set that progression between 1% and 99%
        return $percentage;
    }


//////////////////////////////////////////////////////////////////////////////
//////////// Utility functions
////////////    

    /*
        In this space, you can put any utility methods useful for your game logic
    */
    
    function getFaceupAgeLastSelected() {
        return self::getUniqueValueFromDB(self::format("SELECT faceup_age FROM card WHERE id = {id}", array('id' => $this->innovationGameState->get('id_last_selected'))));
    }
    
    /** integer division **/
    function intDivision($a, $b) {
        return (int)($a/$b);
    }

    /** Returns the card types in use by the current game **/
    function getActiveCardTypes() {
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

    function calculatePlayerIndexes() {
        $player_nos = self::getObjectListFromDB("SELECT player_no FROM player ORDER BY player_no", true);
        $index = 0;
        foreach ($player_nos as $player_no) {
            self::DbQuery(self::format("UPDATE player SET player_index = {player_index} WHERE player_no = {player_no}",
                array('player_index' => $index++, 'player_no' => $player_no)
            ));
        }
    }

    function playerIdToPlayerIndex($player_id) {
        return self::getUniqueValueFromDB(self::format("SELECT player_index FROM player WHERE player_id = {player_id}", array('player_id' => $player_id)));
    }

    function playerIndexToPlayerId($player_index) {
        return self::getUniqueValueFromDB(self::format("SELECT player_id FROM player WHERE player_index = {player_index}", array('player_index' => $player_index)));
    }

    function getAllPlayerIds() {
        return self::getObjectListFromDB("SELECT player_id FROM player", true);
    }

    function getAllActivePlayerIds() {
        return self::getObjectListFromDB("SELECT player_id FROM player WHERE player_eliminated = 0", true);
    }

    function getOtherActivePlayerIds($player_id) {
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

    function getActiveOpponentIds($player_id) {
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

    function getAllActivePlayers() {
        return self::getObjectListFromDB("SELECT player_index FROM player WHERE player_eliminated = 0", true);
    }

    function getOtherActivePlayers($player_id) {
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

    function getActiveOpponents($player_id) {
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

    function getActiveOpponentsWithFewerPoints($player_id) {
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
                ) AND
                player_innovation_score < (
                    SELECT
                        player_innovation_score
                    FROM
                        player
                    WHERE
                        player_id = {player_id}
                )
        ", array('player_id' => $player_id)), true);
    }

    function isEliminated($player_id) {
        return self::getUniqueValueFromDB(self::format("SELECT player_eliminated FROM player WHERE player_id={player_id}", array('player_id' => $player_id)));
    }

    // TODO(LATER): Use this helper more.
    function isTeamGame() {
        return self::decodeGameType($this->innovationGameState->get('game_type')) == 'team';
    }
    
    /** log for debugging **/
    function log() {
        $args = func_get_args();
        $line = Array();
        foreach ($args as $arg) {
            $line[] = is_string($arg) ? $arg : var_export($arg, true);
        }
        self::DbQuery("INSERT INTO logs (line) VALUE ('".mysql_escape_string(implode("\n\n", $line))."')");
    }
    
    
    /** Formatting **/
    // TODO(LATER): Remove this once we are using the function in Strings.php instead.
    static function format($msg, $vars)
    {
        /** Format the string using named or unamed parameters **/
        $vars = (array)$vars;

        $msg = preg_replace_callback('#\{\}#', function($r){
            static $i = 0;
            return '{'.($i++).'}';
        }, $msg);

        return str_replace(
            array_map(function($k) {
                return '{'.$k.'}';
            }, array_keys($vars)),

            array_values($vars),

            $msg
       ) ;
    }
    
    /** Utility for team game **/
    function rearrangePlayersForFixedTeams($player_array, $partnair_of_first) {
        // The goal of this function is to rearrange the player array so that the first player in the lobby plays with his partnair ($partnair_of_first) as decide in the options
        
        // "Fix" the player table order so that it is in the range 1..nbr_players
        $unfixed_player_table_orders = array();
        foreach($player_array as $player_id => $player) {
            $unfixed_player_table_orders[] = $player["player_table_order"];
        }
        sort($unfixed_player_table_orders);
        $fixed_player_table_orders = array();
        foreach($unfixed_player_table_orders as $key => $val) {
            $fixed_player_table_orders[$val] = $key + 1;
        }
        
        // Locate who was the first player in the lobby
        $player_no = 0;
        foreach($player_array as $player_id => $player)
        {
            $player_table_order = $fixed_player_table_orders[$player['player_table_order']];
            if ($player_table_order == 1) {
                $first_player_no = $player_no;
            }
            else if ($player_table_order == $partnair_of_first) {
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
        }
        else {
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
        foreach($player_order as $player_id) {
            $new_player_array[$player_id] = $player_array[$player_id];
        }
        return $new_player_array;
    }
    
    /** Database manipulations **/
    function shuffle() {
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
    
    function extractAgeAchievements() {
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
    
    function tuckCard($card, $owner_to): ?array {
        return self::transferCardFromTo($card, $owner_to, 'board', ['bottom_to' => true]);
    }

    function scoreCard($card, $owner_to): ?array {
        return self::transferCardFromTo($card, $owner_to, 'score', ['score_keyword' => true]);
    }

    function meldCard($card, $owner_to): ?array {
    return self::transferCardFromTo($card, $owner_to, 'board', ['bottom_to' => false, 'meld_keyword' => true]);
    }

    function returnCard($card): ?array {
        return self::transferCardFromTo($card, 0, 'deck', ['return_keyword' => true]);
    }

    function digCard($card, $owner_to): ?array {
        return self::transferCardFromTo($card, $owner_to, "display");
    }

    function foreshadowCard($card, $owner_to): ?array {
        return self::transferCardFromTo($card, $owner_to, 'forecast', ['foreshadow_keyword' => true]);
    }

    function junkCard($card): ?array {
        return self::transferCardFromTo($card, 0, 'junk');
    }

    function safeguardCard($card, $owner_to): ?array {
        return self::transferCardFromTo($card, $owner_to, 'safe', ['safeguard_keyword' => true]);
    }

    function putCardBackInSafe($card, $owner_to): ?array {
        return self::transferCardFromTo($card, $owner_to, 'safe', ['safeguard' => false, 'force' => true]);
    }

    /**
     * Executes the transfer of the card, returning the new card info.
     **/
    function transferCardFromTo($card, $owner_to, $location_to, $properties = []): ?array {
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

        if (self::getGameStateValue('debug_mode') == 1 && !array_key_exists('using_debug_buttons', $card)) {
            error_log("  - Transferring ". self::getCardName($card['id']) . " from " . $card['owner'] . "'s " . $card['location'] . " to " . $owner_to . "'s " . $location_to);
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
        } else { // $location_to != 'board'
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
            self::DbQuery(self::format("
                UPDATE
                    card
                SET
                    position = position + 1
                WHERE
                    {filter_to}
            ",
                array('filter_to' => $filter_to)
           ));
            $position_to = 0;
        } else { // $bottom_to is false
            // new_position = number of cards in the location
            $position_to = self::getUniqueValueFromDB(self::format("
            SELECT
                COUNT(position)
            FROM
                card
            WHERE
                {filter_to}
            ",
                array('filter_to' => $filter_to)
           )); 
        }

        // Execute the transfer
        self::DbQuery(self::format("
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
       ));
        
        // Update the position of the cards of the location the transferred card came from to fill the gap
        self::DbQuery(self::format("
            UPDATE
                card
            SET
                position = position - 1 
            WHERE
                {filter_from} AND
                position > {position_from}
        ",
            array('filter_from' => $filter_from, 'position_from' => $position_from)
        ));

        if ($location_to == 'forecast') {
            self::incStat(1, 'foreshadowed_number', $owner_to);
        }
        
        $transferInfo = array(
            'owner_from' => $owner_from,
            'location_from' => $location_from,
            'position_from' => $position_from,
            'splay_direction_from' => $splay_direction_from, 
            'owner_to' => $owner_to,
            'location_to' => $location_to,
            'position_to' => $position_to,
            'splay_direction_to' => $splay_direction_to, 
            'bottom_from' => $bottom_from,
            'bottom_to' => $bottom_to,
            'score_keyword' => $score_keyword,
            'meld_keyword' => $meld_keyword,
            'achieve_keyword' => $achieve_keyword,
            'draw_keyword' => $draw_keyword,
            'safeguard_keyword' => $safeguard_keyword,
            'return_keyword' => $return_keyword,
            'foreshadow_keyword' => $foreshadow_keyword,
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
                if ($card['type'] == 2) {
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
            }
            catch (EndOfGame $e) {
                self::trace('EOG bubbled from self::transferCardFromTo');
                throw $e; // Re-throw exception to higher level
            }
            finally {
                // Determine if the loss of the card from its location of depart breaks a splay. If it's the case, change the splay_direction of the remaining card to unsplay (a notification being sent).
                if ($location_from == 'board' && $splay_direction_from > 0) {
                    $number_of_cards_in_pile = self::getUniqueValueFromDB(self::format("
                        SELECT
                            COUNT(*)
                        FROM
                            card
                        WHERE
                            owner={owner_from} AND
                            location='board' AND
                            color={color}
                    ",
                        array('owner_from'=>$owner_from, 'color'=>$color)
                    ));
                    
                    if ($number_of_cards_in_pile <= 1) {
                        self::splay($owner_from, $owner_from, $color, 0); // Unsplay
                    }
                }
            }
        }
        return $card;
    }
    
    /** Splay mechanism **/

    function unsplay($player_id, $target_player_id, $color) {
        self::splay($player_id, $target_player_id, $color, Directions::UNSPLAYED, /*force_unsplay=*/ true);
    }

    function splayLeft($player_id, $target_player_id, $color) {
        self::splay($player_id, $target_player_id, $color, Directions::LEFT);
    }

    function splayRight($player_id, $target_player_id, $color) {
        self::splay($player_id, $target_player_id, $color, Directions::RIGHT);
    }

    function splayUp($player_id, $target_player_id, $color) {
        self::splay($player_id, $target_player_id, $color, Directions::UP);
    }

    function splayAslant($player_id, $target_player_id, $color) {
        self::splay($player_id, $target_player_id, $color, Directions::ASLANT);
    }

    function splay($player_id, $target_player_id, $color, $splay_direction, $force_unsplay=false) {

        // Return early if the stack is already splayed in the requested direction.
        if (self::getCurrentSplayDirection($target_player_id, $color) == $splay_direction) {
            return;
        }

        // Return early if a stack with less than 2 cards is attempting to be splayed.
        if ($splay_direction >= 1 && self::countCardsInLocationKeyedByColor($target_player_id, 'board')[$color] <= 1) {
            return;
        }

        self::DbQuery(self::format("
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
        ));
        
        self::notifyForSplay($player_id, $target_player_id, $color, $splay_direction, $force_unsplay);

        $end_of_game = false;

        self::removeOldFlagsAndFountains();
        try {
            self::addNewFlagsAndFountains();
        } catch(EndOfGame $e) {
            $end_of_game = true;
        }

        try {
            self::checkForSpecialAchievements();
        } catch(EndOfGame $e) {
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
    }

    function getForecastAndSafeLimit($player_id): int {
        $maxSplayDirection = 0;
        foreach (self::getTopCardsOnBoard($player_id) as $card) {
            $maxSplayDirection = max($maxSplayDirection, $card['splay_direction']);
        }
        return 5 - $maxSplayDirection;
    }
    
    /* Rearrangement mechanism */
    function rearrange($player_id, $color, $permutations) {
        
        $old_board = self::getCardsInLocationKeyedByColor($player_id, 'board');
        
        foreach($permutations as $permutation) {
            $data = $permutation;
            $data['player_id'] = $player_id;
            $data['color'] = $color;
            $data['position_plus_delta'] = $data['position'] + $data['delta'];
            self::DbQuery(self::format("
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
            ));
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
    function markAsSelected($card_id) {
        /**
        Mark one card via its id.
        **/
        self::DbQuery(self::format("
            UPDATE
                card
            SET
                selected = TRUE
            WHERE
                id = {card_id}
        ",
            array('card_id' => $card_id)
        ));
    }
    
    function unmarkAsSelected($card_id) {
        /**
        Mark one card via its id.
        **/
        self::DbQuery(self::format("
            UPDATE
                card
            SET
                selected = FALSE
            WHERE
                id = {card_id}
        ",
            array('card_id' => $card_id)
        ));
    }

    function countSelectedCards() {
        return self::getUniqueValueFromDB("
            SELECT
                COUNT(*)
            FROM
                card
            WHERE
                selected IS TRUE
        ");
    }
    
    function getSelectedCards() {
        return self::getObjectListFromDB("SELECT * FROM card WHERE selected IS TRUE ORDER BY location, position");
    }
    
    function getVisibleSelectedCards($player_id) {
        return self::getObjectListFromDB(self::format("
            SELECT
                *
            FROM
                card
            WHERE
                selected IS TRUE AND
                (location = 'board' OR owner = {player_id} AND location != 'achievements')
                
        ", // A player can see the versos of all cards on all boards and all the cards in his hand and his score
            array('player_id' => $player_id)
        ));
    }
    
    function getSelectableRectos($player_id) {
        return self::getObjectListFromDB(self::format("
            SELECT
                owner, location, age, type, is_relic, position
            FROM
                card
            WHERE
                selected IS TRUE AND
                location != 'board' AND
                (owner != {player_id} OR location = 'score' OR location = 'forecast' OR location = 'achievements' OR location = 'safe')
        ",
            array('player_id' => $player_id)
        ));
    }

    function deselectAllCards() {
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
    function notifyAll($notification_type, $notification_log, $notification_args = []) {
        self::notifyAllPlayersBut(array(), $notification_type, $notification_log, $notification_args);
    }
    
    function notifyAllPlayersBut($player_ids, $notification_type, $notification_log, $notification_args = []) {
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

    function notifyIfLocationLimitShrunkSelection($player_id) {
        if ($this->innovationGameState->get('limit_shrunk_selection_size') == 1) {
            $location_to = self::decodeLocation($this->innovationGameState->get('location_to'));
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
    
    function updateGameSituation($card, $transferInfo) {
        self::recordThatChangeOccurred();

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
            // TODO(FIGURES): If there are any cards which tuck/score a card which belongs to another player, then
            // there is a bug here that we need to fix.
            if ($location_to == 'board' && $bottom_to) { // That's a tuck
                self::incrementFlagForMonument($player_id, 'number_of_tucked_cards');
            } else if ($transferInfo['score_keyword']) { // That's a score
                self::incrementFlagForMonument($player_id, 'number_of_scored_cards');
            }
            $transferInfo['monument_counters'][$player_id] = self::getFlagsForMonument($player_id);
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
            } catch(EndOfGame $e) {
                $end_of_game = true;
            }
        }

        if ($location_from == 'board' || $location_to == 'board') {
            self::removeOldFlagsAndFountains();
            try {
                self::addNewFlagsAndFountains();
            } catch(EndOfGame $e) {
                $end_of_game = true;
            }
        }

        try {
            self::checkForSpecialAchievements();
        } catch(EndOfGame $e) {
            $end_of_game = true;
        }

        if ($end_of_game) {
            self::trace('EOG bubbled from self::updateGameSituation');
            throw $e; // Re-throw exception to higher level
        }
    }

    function revealLocation($player_id, $location) {
        $cards = self::getCardsInLocation($player_id, $location);
        $args = ['i18n' => ['location'], 'location' => self::renderLocation($location)];
        if (count($cards) == 0) {
            $this->notifyPlayer($player_id, 'log', clienttranslate('${You} reveal an empty ${location}.'),
                array_merge($args, ['You' => 'You']));
            $this->notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} reveals an empty ${location}.'),
                array_merge($args, ['player_name' => self::getPlayerNameFromId($player_id)]));
            return;
        }
        $args = array_merge($args, ['card_ids' => self::getCardIds($cards), 'card_list' => self::getNotificationArgsForCardList($cards)]);
        $this->notifyPlayer($player_id, 'logWithCardTooltips', clienttranslate('${You} reveal your ${location}: ${card_list}.'),
            array_merge($args, ['You' => 'You']));
        $this->notifyAllPlayersBut($player_id, 'logWithCardTooltips', clienttranslate('${player_name} reveals his ${location}: ${card_list}.'),
            array_merge($args, ['player_name' => self::getPlayerNameFromId($player_id)]));
    }

    function revealHand($player_id) {
        self::revealLocation($player_id, 'hand');
    }

    function revealScorePile($player_id) {
        self::revealLocation($player_id, 'score');
    }

    function revealCardWithoutMoving($player_id, $card, $mentionLocation = true) {
        if ($mentionLocation) {
            $args = ['i18n' => ['location'], 'location' => self::renderLocation($card['location']), 'card_ids' => [$card['id']], 'card_list' => self::getNotificationArgsForCardList([$card])];
            $this->notifyPlayer($player_id, 'logWithCardTooltips', clienttranslate('${You} reveal ${card_list} from your ${location}.'),
                array_merge($args, ['You' => 'You']));
            $this->notifyAllPlayersBut($player_id, 'logWithCardTooltips', clienttranslate('${player_name} reveals ${card_list} from his ${location}.'),
                array_merge($args, ['player_name' => self::getPlayerNameFromId($player_id)]));
        } else {
            $args = ['card_ids' => [$card['id']], 'card_list' => self::getNotificationArgsForCardList([$card])];
            $this->notifyPlayer($player_id, 'logWithCardTooltips', clienttranslate('${You} reveal ${card_list}.'),
                array_merge($args, ['You' => 'You']));
            $this->notifyAllPlayersBut($player_id, 'logWithCardTooltips', clienttranslate('${player_name} reveals ${card_list}.'),
                array_merge($args, ['player_name' => self::getPlayerNameFromId($player_id)]));
        }
    }

    function getNotificationArgsForCardList($cards) {
        $args = array();
        $args['i18n'] = array();
        $log = "";
        for ($i = 0; $i < count($cards); $i++) {
            $card = $cards[$i];
            if ($i > 0) {
                $log = $log.', ';
            }
            if ($card['age'] != null) {
                $log = $log."<span class='square N age age_".$card['age']." type_".$card['type']."'>".$card['age']."</span> ";
            }
            $log = $log.'<span id=\''.uniqid().'\'class=\'card_name card_id_'.$card['id'].'\'>${name_'.$i.'}</span>';
            $args['name_'.$i] = self::getCardName($card['id']);
            $args['i18n'][] = 'name_'.$i;
        }
        return ['log' => $log, 'args'=> $args];
    }
    
    function getDelimiterMeanings($text, $card_id = null) {
        $delimiters = array();
        
        // Delimiters for age icon
        if (strpos($text, '{<}') > -1) {
            if ($card_id == null){
                $delimiters['<'] = "<span class='square N age'>";
            } else {
                $delimiters['<'] = "<span class='square N age type_".self::getCardInfo($card_id)['type']."'>";
            }
            $delimiters['>'] = "</span>";
        }

        // Delimiters for card name
        if (strpos($text, '{<<}') > -1) {
            // Without an ID it's not possible to add a BGA tooltip to it.
            $delimiters['<<'] = "<span id='".uniqid()."'class='card_name card_id_".$card_id."'>";
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

    function getCardExecutionCodeWithLetter($card_id, $current_effect_type, $current_effect_number, $step) {
        $letters = array(1 => 'A', 2 => 'B', 3 => 'C', 4 => 'D');
        // TODO(LATER): Remove this hack since it's likely just masking another problem.
        if ($step >= 1 && $step <= 4) {
            $letter = $letters[$step];
        } else {
            $letter = '?';
        }
        return self::getCardExecutionBaseCode($card_id, $current_effect_type, $current_effect_number) . $letter . self::getEditionSuffix($card_id);
    }

    function getCardExecutionCode($card_id, $current_effect_type, $current_effect_number) {
        return self::getCardExecutionBaseCode($card_id, $current_effect_type, $current_effect_number) . self::getEditionSuffix($card_id);
    }

    function getCardExecutionBaseCode($card_id, $current_effect_type, $current_effect_number) {
        $nested_card_state = self::getCurrentNestedCardState();
        $post_execution_indicator = $nested_card_state['post_execution_index'] == 0 ? '' : '+';
        // Echo effects are sometimes executed on cards other than the card being dogma'd
        if ($current_effect_type == 3) {
            $nesting_index = $nested_card_state['nesting_index'];
            $card_id = self::getUniqueValueFromDB(
                self::format("SELECT card_id FROM echo_execution WHERE nesting_index = {nesting_index} AND execution_index = {effect_number}",
                    array('nesting_index' => $nesting_index, 'effect_number' => $current_effect_number)));
            $current_effect_number = 1;
        }
        return $card_id . self::getLetterForEffectType($current_effect_type) . $current_effect_number . $post_execution_indicator;
    }

    function getEditionSuffix($card_id) {
        if (array_key_exists('separate_4E_implementation', $this->textual_card_infos[$card_id]) && $this->textual_card_infos[$card_id]['separate_4E_implementation'] == true) {
            if ($this->innovationGameState->getEdition() == 4) {
                return '_4E'; // 4th edition or later
            } else {
                return '_3E'; // 3rd edition or earlier
            }
        }
        return '';
    }

    function getLetterForEffectType($effect_type) {
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

    function notifyWithNoPlayersInvolved($card, $transferInfo, $progressInfo) {
        $location_from = $transferInfo['location_from'];
        $location_to = $transferInfo['location_to'];

        // TODO(4E): Revise this.
        switch($location_from . '->' . $location_to) {
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
        
        $info = array_merge($transferInfo, $progressInfo);
        
        $notif_args = array_merge($info, self::getDelimiterMeanings($message, $card['id']));
        $notif_args['age'] = $card['age'];
        $notif_args['type'] = $card['type'];
        $notif_args['is_relic'] = $card['is_relic'];
        
        self::notifyAllPlayers("transferedCard", $message, $notif_args);
    }
    
    function notifyWithOnePlayerInvolved($card, $transferInfo, $progressInfo) {
        $is_special_achievement = $card['age'] === null;

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
        if ($location_from === 'deck' && $draw_keyword) {
            $action_for_player = clienttranslate('draw');
            $action_for_others = clienttranslate('draws');
        } else if ($location_from === 'safe') {
            $from_somewhere_for_player = clienttranslate(' from your safe');
            $from_somewhere_for_others = clienttranslate(' from his safe');
        } else if ($location_from === 'display') {
            $visible_for_player = true;
            $visible_for_others = true;
            $from_somewhere_for_player = clienttranslate(' from your display');
            $from_somewhere_for_others = clienttranslate(' from his display');
        } else if ($location_from === 'hand') {
            $visible_for_player = true;
            $from_somewhere_for_player = clienttranslate(' from your hand');
            $from_somewhere_for_others = clienttranslate(' from his hand');
        } else if ($location_from === 'board' || $location_from === 'pile') {
            $visible_for_player = true;
            $visible_for_others = true;
            $from_somewhere_for_player = clienttranslate(' from your board');
            $from_somewhere_for_others = clienttranslate(' from his board');
        } else if ($location_from === 'forecast') {
            $visible_for_player = true;
            $from_somewhere_for_player = clienttranslate(' from your forecast');
            $from_somewhere_for_others = clienttranslate(' from his forecast');
        } else if ($location_from === 'score') {
            $visible_for_player = true;
            $from_somewhere_for_player = clienttranslate(' from your score pile');
            $from_somewhere_for_others = clienttranslate(' from his score pile');
        } else if ($location_from === 'achievements') {
            if ($owner_from == 0) {
                $from_somewhere_for_player = clienttranslate(' from the available achievements');
                $from_somewhere_for_others = clienttranslate(' from the available achievements');
            } else {
                $from_somewhere_for_player = clienttranslate(' from your achievements');
                $from_somewhere_for_others = clienttranslate(' from his achievements');
            }
        } else if ($location_from === 'relics') {
            $action_for_player = clienttranslate('seize');
            $action_for_others = clienttranslate('seizes');
        } else if ($location_from === 'junk') {
            $from_somewhere_for_player = clienttranslate(' from the junk');
            $from_somewhere_for_others = clienttranslate(' from the junk');
        } else if ($location_from === 'revealed') {
            $visible_for_player = true;
            $visible_for_others = true;
        } else if ($location_from === 'flags' || $location_from === 'fountains') {
            $visible_for_player = true;
            $visible_for_others = true;
        }

        // Update text based on where the card is going to
        if ($location_to === 'board') {
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
        } else if ($location_to === 'display') {
            $visible_for_player = true;
            $visible_for_others = true;
            $action_for_player = clienttranslate('dig');
            $to_somewhere_for_player = clienttranslate(' and put it on display');
            $action_for_others = clienttranslate('digs');
            $to_somewhere_for_others = clienttranslate(' and puts it on display');
        } else if ($location_to === 'forecast') {
            $visible_for_player = true;
            if ($draw_keyword) {
                $action_for_player = clienttranslate('draw and foreshadow');
                $action_for_others = clienttranslate('draws and foreshadows');
            } else {
                $action_for_player = clienttranslate('foreshadow');
                $action_for_others = clienttranslate('foreshadows');
            }
        } else if ($location_to === 'revealed') {
            $visible_for_player = true;
            $visible_for_others = true;
            if ($draw_keyword) {
                $action_for_player = clienttranslate('draw and reveal');
                $action_for_others = clienttranslate('draws and reveals');
            } else {
                $action_for_player = clienttranslate('reveal');
                $action_for_others = clienttranslate('reveals');
            }
        } else if ($location_to === 'achievements') {
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
        } else if ($location_to === 'score') {
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
        } else if ($location_to === 'hand') {
            $visible_for_player = true;
            $to_somewhere_for_player = clienttranslate(' to your hand');
            $to_somewhere_for_others = clienttranslate(' to his hand');
        } else if ($location_to === 'safe') {
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
        } else if ($location_to === 'deck') {
            if ($bottom_to) {
                $action_for_player = clienttranslate('return');
                $action_for_others = clienttranslate('returns');
            } else {
                $action_for_player = clienttranslate('place');
                $to_somewhere_for_player = clienttranslate(' on top of its deck');
                $action_for_others = clienttranslate('places');
                $to_somewhere_for_others = clienttranslate(' on top of its deck');
            }
        } else if ($location_to === 'relics') {
            $action_for_player = clienttranslate('return');
            $action_for_others = clienttranslate('returns');
        } else if ($location_to === 'junk') {
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
            if ($is_special_achievement) {
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

        $info = array_merge($transferInfo, $progressInfo);
        $delimiters_for_player = self::getDelimiterMeanings($message_for_player, $card['id']);
        $notif_args_for_player = array_merge($notif_args_for_player, $info, $delimiters_for_player);
        $delimiters_for_others = self::getDelimiterMeanings($message_for_others, $card['id']);
        $notif_args_for_others = array_merge($notif_args_for_others, $info, $delimiters_for_others);
        self::notifyPlayer($transferInfo['player_id'], "transferedCard", $message_for_player, $notif_args_for_player);
        self::notifyAllPlayersBut($transferInfo['player_id'], "transferedCard", $message_for_others, $notif_args_for_others);
    }
    
    function getTransferInfoWithOnePlayerInvolved($owner_from, $location_from, $location_to, $player_id_is_owner_from, $player_id_is_owner_to, $bottom_from, $bottom_to, $score_keyword, $meld_keyword, $achieve_keyword, $you_must, $player_must, $player_name, $number, $cards, $targetable_players, $code) {

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
            $from_somewhere_for_player = clienttranslate(' from your hand');
            $from_somewhere_for_others = clienttranslate(' from his hand');
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
        } else if ($location_from === 'hand,score') {
            $from_somewhere_for_player = clienttranslate(' from your hand and score pile');
            $from_somewhere_for_others = clienttranslate(' from his hand and score pile');
        } else if ($location_from === 'revealed,hand') {
            $from_somewhere_for_player = clienttranslate(' that you revealed and from your hand');
            $from_somewhere_for_others = clienttranslate(' that he revealed and from his hand');
        } else if ($location_from === 'revealed,score') {
            $from_somewhere_for_player = clienttranslate(' that you revealed and from your score pile');
            $from_somewhere_for_others = clienttranslate(' that he revealed and from his score pile');
        } else if ($location_from === 'pile,score') {
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
        } else if ($location_to === 'junk,safe') {
            $action = clienttranslate('junk then safeguard');
        } else if ($location_to === 'revealed') {
            $action = clienttranslate('reveal');
        } else if ($location_to === 'revealed,score') {
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
                'log' => $message_for_player,
                'args' => [
                    'You_must' => [
                        'i18n' => ['log'],
                        'log' => $you_must,
                        'args' => [
                            'You' => 'You',
                        ],
                    ],
                    'action' => $action,
                    'number' => $number,
                    'card_qualifier' => $card_qualifier,
                    'card' => $cards,
                    'from_somewhere' => [
                        'i18n' => ['targetable_players'],
                        'log' => $from_somewhere_for_player,
                        'args' => ['targetable_players' => $targetable_players],
                    ],
                    'to_somewhere' => $to_somewhere_for_player,
                    'targetable_players' => $targetable_players,
                ],
            ],
            'message_for_others' => [
                'i18n' => ['log'],
                'log' => $message_for_others,
                'args' => [
                    'i18n' => ['targetable_players'],
                    'player_must' => [
                        'i18n' => ['log'],
                        'log' => $player_must,
                        'args' => [
                            'player_name' => $player_name,
                        ],
                    ],
                    'action' => $action,
                    'number' => $number,
                    'card_qualifier' => $card_qualifier,
                    'card' => $cards,
                    'from_somewhere' => [
                        'i18n' => ['targetable_players'],
                        'log' => $from_somewhere_for_others,
                        'args' => ['targetable_players' => $targetable_players],
                    ],
                    'to_somewhere' => $to_somewhere_for_others,
                    'targetable_players' => $targetable_players,
                ],
            ],
        ];
    }
    
    function notifyWithTwoPlayersInvolved($card, $transferInfo, $progressInfo) {
        $owner_from = $transferInfo['owner_from'];
        $owner_to = $transferInfo['owner_to'];
        $location_from = $transferInfo['location_from'];
        $location_to = $transferInfo['location_to'];
        $meld_keyword = $transferInfo['meld_keyword'];
        $player_id = $transferInfo['player_id'];

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
        if ($location_from === 'hand') {
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
        } else if ($location_from === 'score') {
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
        } else if ($location_from === 'board') {
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
        } else if ($location_from === 'safe') {
            if ($player_id == $owner_from) {
                $from_somewhere_for_player = clienttranslate(' from your safe');
                $from_somewhere_for_opponent = clienttranslate(' from his safe');
                $from_somewhere_for_others = clienttranslate(' from his safe');
            } else {
                $from_somewhere_for_player = clienttranslate(' from ${opponent_name}\'s safe');
                $from_somewhere_for_opponent = clienttranslate(' from ${your} safe');
                $from_somewhere_for_others = clienttranslate(' from ${opponent_name}\'s safe');
            }
        } else if ($location_from === 'achievements') {
            if ($player_id == $owner_from) {
                $from_somewhere_for_player = clienttranslate(' from your achievements');
                $from_somewhere_for_opponent = clienttranslate(' from his achievements');
                $from_somewhere_for_others = clienttranslate(' from his achievements');
            } else {
                $from_somewhere_for_player = clienttranslate(' from ${opponent_name}\'s achievements');
                $from_somewhere_for_opponent = clienttranslate(' from ${your} achievements');
                $from_somewhere_for_others = clienttranslate(' from ${opponent_name}\'s achievements');
            }
        } else if ($location_from === 'display') {
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
        } else if ($location_from === 'revealed') {
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
            'i18n' => ['name'],
            'You' => 'You',
            'action' => $action_for_player,
            'from_somewhere' => ['log' => $from_somewhere_for_player, 'args' => ['opponent_name' => $opponent_name]],
            'to_somewhere' => ['log' => $to_somewhere_for_player, 'args' => ['opponent_name' => $opponent_name]],
        ];
        $notif_args_for_opponent = [
            'your' => 'your',
            'player_name' => $player_name,
            'action' => $action_for_opponent,
            'from_somewhere' => ['log' => $from_somewhere_for_opponent, 'args' => ['opponent_name' => $opponent_name]],
            'to_somewhere' => ['log' => $to_somewhere_for_opponent, 'args' => ['opponent_name' => $opponent_name, 'your' => 'your']],
        ];
        $notif_args_for_others = [
            'player_name' => $player_name,
            'opponent_name' => $opponent_name,
            'action' => $action_for_others,
            'from_somewhere' => ['log' => $from_somewhere_for_others, 'args' => ['opponent_name' => $opponent_name]],
            'to_somewhere' => ['log' => $to_somewhere_for_others, 'args' => ['opponent_name' => $opponent_name]],
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
        
        $info = array_merge($transferInfo, $progressInfo);
        $notif_args_for_player = array_merge($notif_args_for_player, $info, self::getDelimiterMeanings($message_for_player, $card['id']));
        $notif_args_for_opponent = array_merge($notif_args_for_opponent, $info, self::getDelimiterMeanings($message_for_opponent, $card['id']));
        $notif_args_for_others = array_merge($notif_args_for_others, $info, self::getDelimiterMeanings($message_for_others, $card['id']));
        
        self::notifyPlayer($transferInfo['player_id'], "transferedCard", $message_for_player, $notif_args_for_player);
        self::notifyPlayer($transferInfo['opponent_id'], "transferedCard", $message_for_opponent, $notif_args_for_opponent);
        self::notifyAllPlayersBut(array($transferInfo['player_id'], $transferInfo['opponent_id']), "transferedCard", $message_for_others, $notif_args_for_others);
    }
        
    function getTransferInfoWithTwoPlayersInvolved($location_from, $location_to, $player_id_is_owner_from, $player_id_is_owner_to, $opponent_id_is_owner_from, $opponent_id_is_owner_to, $bottom_from, $bottom_to, $score_keyword, $meld_keyword, $you_must, $player_must, $your, $player_name, $opponent_name, $number, $cards) {
    
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
        if ($location_from === 'hand') {
            if ($player_id_is_owner_from) {
                $from_somewhere_for_player = clienttranslate(' from your hand');
                $from_somewhere_for_opponent = clienttranslate(' from his hand');
                $from_somewhere_for_others = clienttranslate(' from his hand');
            } else if ($opponent_id_is_owner_from) {
                $from_somewhere_for_player = clienttranslate(' from ${opponent_name}\'s hand');
                $from_somewhere_for_opponent = clienttranslate(' from ${your} hand');
                $from_somewhere_for_others = clienttranslate(' from ${opponent_name}\'s hand');
            }
        } else if ($location_from === 'board') {
            if ($player_id_is_owner_from) {
                $from_somewhere_for_player = clienttranslate(' from your board');
                $from_somewhere_for_opponent = clienttranslate(' from his board');
                $from_somewhere_for_others = clienttranslate(' from his board');
            } else if ($opponent_id_is_owner_from) {
                $from_somewhere_for_player = clienttranslate(' from ${opponent_name}\'s board');
                $from_somewhere_for_opponent = clienttranslate(' from ${your} board');
                $from_somewhere_for_others = clienttranslate(' from ${opponent_name}\'s board');
            }
        } else if ($location_from === 'score') {
            if ($player_id_is_owner_from) {
                $from_somewhere_for_player = clienttranslate(' from your score pile');
                $from_somewhere_for_opponent = clienttranslate(' from his score pile');
                $from_somewhere_for_others = clienttranslate(' from his score pile');
            } else if ($opponent_id_is_owner_from) {
                $from_somewhere_for_player = clienttranslate(' from ${opponent_name}\'s score pile');
                $from_somewhere_for_opponent = clienttranslate(' from ${your} score pile');
                $from_somewhere_for_others = clienttranslate(' from ${opponent_name}\'s score pile');
            }
        } else if ($location_from === 'safe') {
            if ($player_id_is_owner_from) {
                $from_somewhere_for_player = clienttranslate(' from your safe');
                $from_somewhere_for_opponent = clienttranslate(' from his safe');
                $from_somewhere_for_others = clienttranslate(' from his safe');
            } else if ($opponent_id_is_owner_from) {
                $from_somewhere_for_player = clienttranslate(' from ${opponent_name}\'s safe');
                $from_somewhere_for_opponent = clienttranslate(' from ${your} safe');
                $from_somewhere_for_others = clienttranslate(' from ${opponent_name}\'s safe');
            }
        } else if ($location_from === 'revealed') {
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
            'message_for_player' => [
                'i18n' => ['log'],
                'log' => $message_for_player,
                'args' => [
                    'You_must' => [
                        'i18n' => ['log'],
                        'log' => $you_must,
                        'args' => [
                            'You' => 'You',
                        ],
                    ],
                    'number' => $number,
                    'action' => $action,
                    'card_qualifier' => $card_qualifier,
                    'card' => $cards,
                    'from_somewhere' => ['log' => $from_somewhere_for_player, 'args' => ['opponent_name' => $opponent_name]],
                    'to_somewhere' => ['log' => $to_somewhere_for_player, 'args' => ['opponent_name' => $opponent_name]],
                ],
            ],
            'message_for_opponent' => [
                'i18n' => ['log'],
                'log' => $message_for_opponent,
                'args' => [
                    'player_must' => [
                        'i18n' => ['log'],
                        'log' => $player_must,
                        'args' => [
                            'player_name' => $player_name,
                        ],
                    ],
                    'number' => $number,
                    'action' => $action,
                    'card_qualifier' => $card_qualifier,
                    'card' => $cards,
                    'from_somewhere' => ['log' => $from_somewhere_for_opponent, 'args' => ['opponent_name' => $opponent_name]],
                    'to_somewhere' => ['log' => $to_somewhere_for_opponent, 'args' => ['opponent_name' => $opponent_name, 'your' => $your]],
                ],
            ],
            'message_for_others' => [
                'i18n' => ['log'],
                'log' => $message_for_others,
                'args' => [
                    'player_must' => [
                        'i18n' => ['log'],
                        'log' => $player_must,
                        'args' => [
                            'player_name' => $player_name,
                        ],
                    ],
                    'number' => $number,
                    'action' => $action,
                    'card_qualifier' => $card_qualifier,
                    'card' => $cards,
                    'from_somewhere' => ['log' => $from_somewhere_for_others, 'args' => ['opponent_name' => $opponent_name]],
                    'to_somewhere' => ['log' => $to_somewhere_for_others, 'args' => ['opponent_name' => $opponent_name]],
                ],
            ],
        ];
    }

    /** Returns the list of player IDs which are not adjacent to the launcher (i.e. the players used for the 4th edition distance rule) */
    function getPlayerIdsAffectedByDistanceRule($launcher_id) {
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

    function getActivePlayerIdsInTurnOrderStartingToLeftOfActingPlayer() {
        $current_player_index = self::playerIdToPlayerIndex(self::getCurrentPlayerUnderDogmaEffect());
        $players = self::getCollectionFromDB("SELECT player_index, player_id, player_eliminated FROM player");
        $player_id_to_left = self::playerIndexToPlayerId(($current_player_index + 1) % count($players));
        return self::getActivePlayerIdsInTurnOrder($player_id_to_left);
    }

    function getActivePlayerIdOnRightOfActingPlayer() {
        $player_ids = self::getActivePlayerIdsInTurnOrderStartingToLeftOfActingPlayer();
        return $player_ids[count($player_ids) - 2];
    }
    
    function getActivePlayerIdsInTurnOrderStartingWithCurrentPlayer() {
        $current_player_id = self::getCurrentPlayerUnderDogmaEffect();
        return self::getActivePlayerIdsInTurnOrder($current_player_id);
    }

    function getActivePlayerIdsInTurnOrder($starting_player_id) {
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
    function checkForSpecialAchievements($is_end_of_action_check = false) {
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
    function checkForSpecialAchievementsForPlayer($player_id, $is_end_of_action_check) {
        // TODO(FIGURES): Update this once there are other special achievements to test for.
        $achievements_to_test = array(106);
        $edition = $this->innovationGameState->getEdition();
        if ($edition <= 3 || $is_end_of_action_check) {
            $achievements_to_test = array_merge($achievements_to_test, [105, 107, 108, 109]);
        }
        if ($this->innovationGameState->echoesExpansionEnabled() && ($edition <= 3 || $is_end_of_action_check)) {
            $achievements_to_test = array_merge($achievements_to_test, [435, 436, 437, 438, 439]);
        }
        if ($this->innovationGameState->unseenExpansionEnabled() && $is_end_of_action_check) {
            $achievements_to_test = array_merge($achievements_to_test, [595, 596, 597, 598, 599]);
        }
        $end_of_game = false;
        
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
            case 106: // Monument: tuck 6 cards or score 6 cards
                $flags = self::getFlagsForMonument($player_id);
                $eligible = $flags['number_of_tucked_cards'] >= 6 || $flags['number_of_scored_cards'] >= 6;
                break;
            case 107: // Wonder: 5 colors, each being splayed right, up, or aslant
                $eligible = true;
                for($color = 0; $color < 5 ; $color++) {
                    if (self::getCurrentSplayDirection($player_id, $color) <= 1) { // This color is missing, unsplayed or splayed left
                        $eligible = false;
                        break;
                    };
                }
                break;
            case 108: // World: 12 or more visible clocks (icon 6) on the board 
                $eligible = self::getPlayerSingleRessourceCount($player_id, 6) >= 12;
                break;
            case 109: // Universe: Five top cards, each being of value 8 or more
                $eligible = true;
                for($color = 0; $color < 5 ; $color++) {
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
                for ($color = 0; $color < 5 ; $color++) {
                    if (self::countVisibleIconsInPile($player_id, 0 /* empty hex */, $color) >= 8) {
                        $eligible = true;
                        break;
                    }
                }
                break;
            case 438: // History: A total of 4 or more visible echo effects in a pile
                $eligible = false;
                for ($color = 0; $color < 5 ; $color++) {
                    if (self::countVisibleIconsInPile($player_id, 10 /* echo effect */, $color) >= 4) {
                        $eligible = true;
                        break;
                    }
                }
                break;
            case 439: // Supremacy: 4 different piles have at least 3 of the same icon
                $eligible = false;
                for ($icon = 1; $icon <= 7 && !$eligible; $icon++) {
                    $num_piles = 0;
                    for ($color = 0; $color < 5; $color++) {
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
                            if ($card['faceup_age'] == 1 || $card['faceup_age'] == 3 || 
                                $card['faceup_age'] == 5 || $card['faceup_age'] == 7 || 
                                $card['faceup_age'] == 9 || $card['faceup_age'] == 11)
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
                    for ($color = 0; $color < 5; $color++) {
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
                }
                catch (EndOfGame $e) { // End of game has been detected
                    self::trace('EOG bubbled but suspended from self::checkForSpecialAchievementsForPlayer');
                    $end_of_game = true;
                    continue; // But the other achievements must be checked as well before ending
                }
            }
        }
        // All special achievements have been checked
        if ($end_of_game) { // End of game has been detected
            self::trace('EOG bubbled from self::checkForSpecialAchievementsForPlayer');
            throw $e; // Re-throw the flag
        }
    }

    /** Checks to see if any players lose any flag/fountain achievements. **/
    function removeOldFlagsAndFountains() {
        if (!$this->innovationGameState->citiesExpansionEnabled()) {
            return;
        }

        foreach (self::getActivePlayerIdsInTurnOrderStartingWithCurrentPlayer() as $player_id) {
            $opponent_ids = self::getActiveOpponentIds($player_id);
            for ($color = 0; $color < 5 ; $color++) {
                // Flags
                $num_visible_flags = self::countVisibleIconsInPile($player_id, 8 /* flag */, $color);
                $num_visible_cards = self::countVisibleCards($player_id, $color);
                $opponent_has_more_visible_cards = false;
                foreach ($opponent_ids as $opponent_id) {
                    if (self::countVisibleCards($opponent_id, $color) > $num_visible_cards) {
                        $opponent_has_more_visible_cards = true;
                    }
                }
                $desired_flag_achievements = $opponent_has_more_visible_cards ? 0 : $num_visible_flags;
                $current_flag_achievements = self::getUniqueValueFromDB(self::format("
                    SELECT COUNT(*) FROM card WHERE owner = {owner} AND location = 'achievements' AND color = {color} AND 1000 <= id AND id <= 1099",
                    array('owner' => $player_id, 'color' => $color)
                ));
                for ($i = $desired_flag_achievements; $i < $current_flag_achievements; $i++) {
                    $flag_id = self::getUniqueValueFromDB(self::format("
                        SELECT MIN(id) FROM card WHERE owner = {owner} AND location = 'achievements' AND color = {color} AND 1000 <= id AND id <= 1099",
                        array('owner' => $player_id, 'color' => $color)
                    ));
                    self::transferCardFromTo(self::getCardInfo($flag_id), 0, 'flags');
                }

                // Fountains
                $desired_fountain_achievements = self::countVisibleIconsInPile($player_id, 9 /* fountain */, $color);
                $current_fountain_achievements = self::getUniqueValueFromDB(self::format("
                    SELECT COUNT(*) FROM card WHERE owner = {owner} AND location = 'achievements' AND color = {color} AND id >= 1100",
                    array('owner' => $player_id, 'color' => $color)
                ));
                for ($i = $desired_fountain_achievements; $i < $current_fountain_achievements; $i++) {
                    $fountain_id = self::getUniqueValueFromDB(self::format("
                        SELECT MIN(id) FROM card WHERE owner = {owner} AND location = 'achievements' AND color = {color} AND id >= 1100",
                        array('owner' => $player_id, 'color' => $color)
                    ));
                    self::transferCardFromTo(self::getCardInfo($fountain_id), 0, 'fountains');
                }
            }
        }
    }

    /** Checks to see if any players gain any flag/fountain achievements. **/
    function addNewFlagsAndFountains() {
        if (!$this->innovationGameState->citiesExpansionEnabled()) {
            return;
        }

        $end_of_game = false;

        foreach (self::getActivePlayerIdsInTurnOrderStartingWithCurrentPlayer() as $player_id) {
            $opponent_ids = self::getActiveOpponentIds($player_id);
            for ($color = 0; $color < 5 ; $color++) {
                // Flags
                $num_visible_flags = self::countVisibleIconsInPile($player_id, 8 /* flag */, $color);
                $num_visible_cards = self::countVisibleCards($player_id, $color);
                $opponent_has_more_visible_cards = false;
                foreach ($opponent_ids as $opponent_id) {
                    if (self::countVisibleCards($opponent_id, $color) > $num_visible_cards) {
                        $opponent_has_more_visible_cards = true;
                    }
                }
                $desired_flag_achievements = $opponent_has_more_visible_cards ? 0 : $num_visible_flags;
                $current_flag_achievements = self::getUniqueValueFromDB(self::format("
                    SELECT COUNT(*) FROM card WHERE owner = {owner} AND location = 'achievements' AND color = {color} AND 1000 <= id AND id <= 1099",
                    array('owner' => $player_id, 'color' => $color)
                ));
                for ($i = $current_flag_achievements; $i < $desired_flag_achievements; $i++) {
                    $flag_id = self::getUniqueValueFromDB(self::format("
                        SELECT MIN(id) FROM card WHERE owner = 0 AND location = 'flags' AND color = {color} AND 1000 <= id AND id <= 1099",
                        array('color' => $color)
                    ));
                    try {
                        self::transferCardFromTo(self::getCardInfo($flag_id), $player_id, 'achievements');
                    } catch (EndOfGame $e) {
                        $end_of_game = true;
                    }
                }

                // Fountains
                $desired_fountain_achievements = self::countVisibleIconsInPile($player_id, 9 /* fountain */, $color);
                $current_fountain_achievements = self::getUniqueValueFromDB(self::format("
                    SELECT COUNT(*) FROM card WHERE owner = {owner} AND location = 'achievements' AND color = {color} AND id >= 1100",
                    array('owner' => $player_id, 'color' => $color)
                ));
                for ($i = $current_fountain_achievements; $i < $desired_fountain_achievements; $i++) {
                    $fountain_id = self::getUniqueValueFromDB(self::format("
                        SELECT MIN(id) FROM card WHERE owner = 0 AND location = 'fountains' AND color = {color} AND id >= 1100",
                        array('color' => $color)
                    ));
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
    function incrementFlagForMonument($player_id, $column_name) { // The player tucked or scored a card. Update database accordingly
        self::DbQuery(self::format("
            UPDATE
                player
            SET
                {column_name} = {column_name} + 1
            WHERE
                player_id = {player_id}
        ",
            array('player_id' => $player_id, 'column_name' => $column_name)
        ));
    }
    
    function resetFlagsForMonument() { // The turn of the current player has ended. Set the numbers of tuck cards and scored cards back to zero for all players 
        self::notifyAll('resetMonumentCounters', '', array());
        self::DbQuery("
            UPDATE
                player
            SET
                number_of_tucked_cards = 0,
                number_of_scored_cards = 0
        ");
    }
    
    function getFlagsForMonument($player_id) { // Query the number of cards the player tucked or scored so far during the turn of the current player
        return self::getObjectFromDB(self::format("
            SELECT
                number_of_tucked_cards, number_of_scored_cards
            FROM
                player
            WHERE
                player_id = {player_id}
        ",
            array('player_id' => $player_id)
        ));
    }
    
    function notifyForSplay($player_id, $target_player_id, $color, $splay_direction, $force_unsplay) {

        $new_score = self::updatePlayerScore($target_player_id);

        if ($splay_direction == 0 && !$force_unsplay) {
            $color_in_clear = Colors::render($color);

            if ($player_id != $target_player_id) {
                throw new BgaVisibleSystemException(self::format(self::_("Unhandled case in {function}: '{code}'"), array('function' => "notifyForSplay()", 'code' => 'player_id != target_player_id in unsplay event')));
            }

            self::notifyPlayer($target_player_id, 'splayedPile', clienttranslate('${Your} ${colored} stack is reduced to one card so it loses its splay.'), array(
                'i18n' => array('colored'),
                'Your' => 'Your',
                'colored' => $color_in_clear,
                'player_id' => $target_player_id,
                'color' => $color,
                'splay_direction' => $splay_direction,
                'new_score' => $new_score,
            ));
            
            self::notifyAllPlayersBut($target_player_id, 'splayedPile', clienttranslate('${player_name}\'s ${colored} stack is reduced to one card so it loses its splay.'), array(
                'i18n' => array('colored'),
                'player_name' => self::getPlayerNameFromId($target_player_id),
                'colored' => $color_in_clear,
                'player_id' => $target_player_id,
                'color' => $color,
                'splay_direction' => $splay_direction,
                'new_score' => $new_score,
            ));
            return;
        }
        
        $splay_direction_in_clear = Directions::render($splay_direction);
        $colored_cards = self::renderColorCards($color);
        
        // Update player ressources
        $new_ressource_counts = self::updatePlayerRessourceCounts($target_player_id);
        
        if ($player_id == $target_player_id) {

            self::notifyPlayer($player_id, 'splayedPile', $force_unsplay ? clienttranslate('${You} unsplay your ${colored_cards}.') : clienttranslate('${You} splay your ${colored_cards} ${splay_direction_in_clear}.'), array(
                'i18n' => array('colored_cards', 'splay_direction_in_clear'),
                'player_id' => $player_id,
                'You' => 'You',
                'color' => $color,
                'colored_cards' => $colored_cards,
                'splay_direction' => $splay_direction,
                'splay_direction_in_clear' => $splay_direction_in_clear,
                'new_ressource_counts' => $new_ressource_counts,
                'forced_unsplay' => $force_unsplay,
                'new_score' => $new_score,
            ));
            
            self::notifyAllPlayersBut($player_id, 'splayedPile', $force_unsplay ? clienttranslate('${player_name} unsplays his ${colored_cards}.') : clienttranslate('${player_name} splays his ${colored_cards} ${splay_direction_in_clear}.'), array(
                'i18n' => array('colored_cards', 'splay_direction_in_clear'),
                'player_id' => $player_id,
                'player_name' => self::getPlayerNameFromId($player_id),
                'color' => $color,
                'colored_cards' => $colored_cards,
                'splay_direction' => $splay_direction,
                'splay_direction_in_clear' => $splay_direction_in_clear,
                'new_ressource_counts' => $new_ressource_counts,
                'forced_unsplay' => $force_unsplay,
                'new_score' => $new_score,
            ));

        } else {

            self::notifyPlayer($player_id, 'splayedPile', $force_unsplay ? clienttranslate('${You} unsplay ${target_player_name}\'s ${colored_cards}.') : clienttranslate('${You} splay ${target_player_name}\'s ${colored_cards} ${splay_direction_in_clear}.'), array(
                'i18n' => array('colored_cards', 'splay_direction_in_clear'),
                'player_id' => $target_player_id,
                'You' => 'You',
                'target_player_name' => self::getPlayerNameFromId($target_player_id),
                'color' => $color,
                'colored_cards' => $colored_cards,
                'splay_direction' => $splay_direction,
                'splay_direction_in_clear' => $splay_direction_in_clear,
                'new_ressource_counts' => $new_ressource_counts,
                'forced_unsplay' => $force_unsplay,
                'new_score' => $new_score,
            ));

            self::notifyPlayer($target_player_id, 'splayedPile', $force_unsplay ? clienttranslate('${player_name} unsplays your ${colored_cards}.') : clienttranslate('${player_name} splays your ${colored_cards} ${splay_direction_in_clear}.'), array(
                'i18n' => array('colored_cards', 'splay_direction_in_clear'),
                'player_id' => $target_player_id,
                'player_name' => self::getPlayerNameFromId($player_id),
                'color' => $color,
                'colored_cards' => $colored_cards,
                'splay_direction' => $splay_direction,
                'splay_direction_in_clear' => $splay_direction_in_clear,
                'new_ressource_counts' => $new_ressource_counts,
                'forced_unsplay' => $force_unsplay,
                'new_score' => $new_score,
            ));
            
            self::notifyAllPlayersBut(array($player_id, $target_player_id), 'splayedPile', $force_unsplay ? clienttranslate('${player_name} unsplays ${target_player_name}\'s ${colored_cards}.') : clienttranslate('${player_name} splays ${target_player_name}\'s ${colored_cards} ${splay_direction_in_clear}.'), array(
                'i18n' => array('colored_cards', 'splay_direction_in_clear'),
                'player_id' => $target_player_id,
                'player_name' => self::getPlayerNameFromId($player_id),
                'target_player_name' => self::getPlayerNameFromId($target_player_id),
                'color' => $color,
                'colored_cards' => $colored_cards,
                'splay_direction' => $splay_direction,
                'splay_direction_in_clear' => $splay_direction_in_clear,
                'new_ressource_counts' => $new_ressource_counts,
                'forced_unsplay' => $force_unsplay,
                'new_score' => $new_score,
            ));

        }
    }
    
    /** Notify end of game **/
    function notifyEndOfGameByAchievements() {
        // Display who won and with how many achievements
        // (There can be weird cases when two players tie or one player get more achievements than needed if two or more special achievements are claimed at the same time)
        $players = self::getCollectionFromDb("SELECT player_id, player_score FROM player");
        $number_of_achievements_needed_to_win = $this->innovationGameState->get('number_of_achievements_needed_to_win');
        $number_of_achievements_winner = $number_of_achievements_needed_to_win;
        $winners = array();
        
        foreach($players as $player_id => $player) {
            if ($player['player_score'] == $number_of_achievements_winner) {
                $winners[] = $player_id;
            } else if ($player['player_score'] > $number_of_achievements_winner) {
                $number_of_achievements_winner = $player['player_score'];
                $winners = array($player_id);
            }
        }
        
        foreach ($winners as $player_id) {
            if (self::decodeGameType($this->innovationGameState->get('game_type')) == 'individual') {
                self::notifyAllPlayersBut($player_id, "log", clienttranslate('END OF GAME BY ACHIEVEMENTS: ${player_name} has got ${n} achievements. He wins!'), array(
                    'n' => $number_of_achievements_winner,
                    'player_name' => self::getPlayerNameFromId($player_id)
                ));
                
                self::notifyPlayer($player_id, "log", clienttranslate('END OF GAME BY ACHIEVEMENTS: ${You} have got ${n} achievements. You win!'), array(
                    'n' => $number_of_achievements_winner,
                    'You' => 'You'
                ));
            } else { // Team game
                $teammate_id = self::getPlayerTeammate($player_id);
                $winning_team = array($player_id, $teammate_id);
                self::notifyAllPlayersBut($winning_team, "log", clienttranslate('END OF GAME BY ACHIEVEMENTS: The other team has got ${n} achievements. They win!'), array(
                    'n' => $number_of_achievements_winner
                ));
                
                foreach($winning_team as $player_id) {
                    self::notifyPlayer($player_id, "log", clienttranslate('END OF GAME BY ACHIEVEMENTS: Your team has got ${n} achievements. You win!'), array(
                        'n' => $number_of_achievements_winner
                    ));
                }
            }
        }
    }
    
    function notifyEndOfGameByScore() {
        $player_id = $this->innovationGameState->get('player_who_could_not_draw');
        $max_age = self::getMaxAge();
        if (self::decodeGameType($this->innovationGameState->get('game_type')) == 'individual') {
            self::notifyAllPlayersBut($player_id, "log", clienttranslate('END OF GAME BY SCORE: ${player_name} attempts to draw a card above ${age_10}. The player with the greatest score win.'), array(
                'player_name' => self::getPlayerNameFromId($player_id),
                'age_10' => $max_age
            ));
            
            self::notifyPlayer($player_id, "log", clienttranslate('END OF GAME BY SCORE: ${You} attempt to draw a card above ${age_10}. The player with the greatest score win.'), array(
                'You' => 'You',
                'age_10' => $max_age
            ));
        } else { // Team play
            self::notifyAllPlayersBut($player_id, "log", clienttranslate('END OF GAME BY SCORE: ${player_name} attempts to draw a card above ${age_10}. The team with the greatest combined score win.'), array(
                'player_name' => self::getPlayerNameFromId($player_id),
                'age_10' => $max_age
            ));
            
            self::notifyPlayer($player_id, "log", clienttranslate('END OF GAME BY SCORE: ${You} attempt to draw a card above ${age_10}. The team with the greatest combined score win.'), array(
                'You' => 'You',
                'age_10' => $max_age
            ));
        }
    }
    
    function notifyEndOfGameByDogma() {
        $dogma_card_id = self::getCurrentNestedCardState()['card_id'];
        $card_args = self::getNotificationArgsForCardList([self::getCardInfo($dogma_card_id)]);
        self::notifyAllPlayers('logWithCardTooltips', clienttranslate('END OF GAME BY DOGMA: ${card}.'), ['card' => $card_args, 'card_ids' => [$dogma_card_id]]);
    }
    
    /** Notify general info **/
    function notifyGeneralInfo($message, $args = array()) {
        $delimiters = self::getDelimiterMeanings($message);
        self::notifyAll('log', $message, array_merge($args, $delimiters));
    }
    
    /** This function should be called whenever something changes in the game **/
    function recordThatChangeOccurred() {
        
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
    function markExecutingPlayer($player_id) {
        self::DbQuery(self::format("
            UPDATE
                player
            SET
                effects_had_impact = TRUE
            WHERE
                player_id = {player_id}
        ", 
            array('player_id' => $player_id)
        ));
    }
    
    function resetPlayerTable() {
        self::DbQuery("
            UPDATE
                player
            SET
                featured_icon_count = NULL,
                effects_had_impact = FALSE
        ");
    }
    
    /** Notification system for dogma **/
    
    function getAgeSquare($age) {
        return self::format("<span title='{age}' class='square N age age_{age}'>{age}</span>", array('age' => $age));
    }

    function getAgeSquareWithType($age, $type) {
        return self::format("<span title='{age}' class='square N age age_{age} type_{type}'>{age}</span>", array('age' => $age, 'type' => $type));
    }

    function getMusicNoteIcon() {
        return "<span title='music note' class='square N music_note'></span>";
    }

    function notifyDogma($card) {
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
        self::notifyPlayer($player_id, 'logWithCardTooltips', $message_for_player, array_merge($card_arg, $delimiters_for_player, array(
            'You' => 'You',
            'icon' => $card['dogma_icon'],
        )));
        self::notifyAllPlayersBut($player_id, 'logWithCardTooltips', $message_for_others, array_merge($card_arg, $delimiters_for_others, array(
            'player_name' => self::getPlayerNameFromId($player_id),
            'icon' => $card['dogma_icon'],
        ))); 
    }
    
    function notifyEffectOnPlayer($qualified_effect, $player_id, $launcher_id) {
        if (self::isExecutingAgainDueToEndorsedAction()) {
            self::notifyPlayer($player_id, 'log', clienttranslate('<span class="minor_information">${You} have to execute the ${qualified_effect} again because it was endorsed.</span>'), array(
                'i18n' => array('qualified_effect'),
                'You' => 'You',
                'qualified_effect' => $qualified_effect
            ));
            self::notifyAllPlayersBut($player_id, 'log', clienttranslate('<span class="minor_information">${player_name} has to execute the ${qualified_effect} again because it was endorsed.</span>'), array(
                'i18n' => array('qualified_effect'),
                'player_name' => self::getPlayerNameFromId($player_id),
                'qualified_effect' => $qualified_effect
            ));
        } else {
            self::notifyPlayer($player_id, 'log', clienttranslate('<span class="minor_information">${You} have to execute the ${qualified_effect}.</span>'), array(
                'i18n' => array('qualified_effect'),
                'You' => 'You',
                'qualified_effect' => $qualified_effect
            ));
            self::notifyAllPlayersBut($player_id, 'log', clienttranslate('<span class="minor_information">${player_name} has to execute the ${qualified_effect}.</span>'), array(
                'i18n' => array('qualified_effect'),
                'player_name' => self::getPlayerNameFromId($player_id),
                'qualified_effect' => $qualified_effect
            ));
        }
    }
    
    function notifyPass($player_id) {
        self::notifyPlayer($player_id, 'log', clienttranslate('${You} pass.'), array(
            'You' => 'You'            
        ));
        
        self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} passes.'), array(
            'player_name' => self::getPlayerNameFromId($player_id)
        ));
    }
    
    function notifyNoSelectableCards() {
        if ($this->innovationGameState->get('splay_direction') == -1) {
            if ($this->innovationGameState->get('n') == 0) {
                $message = clienttranslate("No card matches the criteria of the effect.");
            }
            else {
                $message = clienttranslate("No more card matches the criteria of the effect.");
            }
        } else {
            $message = clienttranslate("No stack matches the criteria of the effect for splaying.");
        }
        self::notifyGeneralInfo($message);
    }
    
    function notifyPlayerRessourceCount($player_id, $dogma_icon, $ressource_count) {
        $icon = "<span class='square N icon_" . $dogma_icon . "'></span>";

        self::notifyPlayer($player_id, 'log', clienttranslate('${You} have ${ressource_count} ${icon}.'), array(
            'You' => 'You',
            'ressource_count' => $ressource_count,
            'icon' => $icon
        ));
        
        self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} has ${ressource_count} ${icon}.'), array(
            'player_name' => self::getPlayerNameFromId($player_id),
            'ressource_count' => $ressource_count,
            'icon' => $icon
        )); 
    }
    
    function notifyDogmaWithNoEffect($player_id, $dogma_icon) {
        $icon = "<span class='square N icon_" . $dogma_icon . "'></span>";
        
        self::notifyPlayer($player_id, 'log', clienttranslate('This card has only an I demand effect but nobody has fewer ${icon} than ${you}. Nothing happens.'), array(
            'you' => 'you',
            'icon' => $icon
        ));
        
        self::notifyAllPlayersBut($player_id, 'log', clienttranslate('This card has only an I demand effect but nobody has fewer ${icon} than ${player_name}. Nothing happens.'), array(
            'player_name' => self::getPlayerNameFromId($player_id),
            'icon' => $icon
        )); 
    }
    
    /** Information about cards **/
    function getCardInfo($id) {
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

    function getStaticInfoOfAllCards() {
        /**
            Get all static information about all cards in the database.
        **/
        // if 4th edition
        if ($this->innovationGameState->usingFourthEditionRules()) {
            $cards = self::getObjectListFromDB("SELECT id, type, age, faceup_age, color, spot_1, spot_2, spot_3, spot_4, spot_5, spot_6, dogma_icon, is_relic FROM `card` WHERE `location` != 'removed'");
        } else {
            $cards = self::getObjectListFromDB("SELECT id, type, age, faceup_age, color, spot_1, spot_2, spot_3, spot_4, spot_5, spot_6, dogma_icon, is_relic FROM `card`");
        }
        return self::attachTextualInfoToList($cards);
    }
    
    function getCardInfoFromPosition($owner, $location, $age, $type, $is_relic, $position) {
        /**
            Get all information from the database about the card indicated by its position
        **/
        return self::getObjectFromDB(self::format("
                SELECT * FROM card WHERE
                    owner = {owner}
                    AND location = '{location}'
                    AND age = {age}
                    AND type = {type}
                    AND is_relic = {is_relic}
                    AND position = {position}
            ",
                array('owner' => $owner, 'location' => $location, 'age' => $age, 'type' => $type, 'is_relic' => $is_relic, 'position' => $position)
        ));
    }

    function getCardIds($cards) {
        $card_ids = array();
        foreach ($cards as $card) {
            $card_ids[] = $card['id'];
        }
        return $card_ids;
    }

    function getCardName($id) {
        if ($id >= 1000) { // Flags and fountains
            return null;
        }
        return self::getCardPropertyForCurrentVersion('name', $id);
    }

    function getNonDemandEffect($id, $effect_number) {
        return self::getCardPropertyForCurrentVersion('non_demand_effect_'.$effect_number, $id);
    }

    function getDemandEffect($id) {
        return self::getCardPropertyForCurrentVersion('i_demand_effect', $id);
    }

    function getCompelEffect($id) {
        return self::getCardPropertyForCurrentVersion('i_compel_effect', $id);
    }

    function getEchoEffect($id) {
        return self::getCardPropertyForCurrentVersion('echo_effect', $id);
    }

    function getCardPropertyForCurrentVersion($prefix, $id) {
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

    function unsetVersionedCardProperties($textual_infos, $prefix) {
        unset($textual_infos[$prefix . '_first']);
        unset($textual_infos[$prefix . '_first_and_third']);
        unset($textual_infos[$prefix . '_third']);
        unset($textual_infos[$prefix . '_third_and_fourth']);
        unset($textual_infos[$prefix . '_fourth']);
    }
    
    function attachTextualInfo($card) {
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
    
    function attachTextualInfoToList($card_list) {
        foreach($card_list as &$card) {
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
    public function comesAlphabeticallyBefore($card1, $card2) : bool
    {
        $name1 = $this->getCardName($card1['id']);
        $name2 = $this->getCardName($card2['id']);
        return Strings::doesStringComeBefore($name1, $name2);
    }
    
    function getColorsOfRepeatedValueOfTopCardsOnBoard($player_id) {
        /**
            Returns an array of all colors whose top card's value matches the value of another top card on that player's board .
        **/

        $colors = array();
        $top_cards = self::getTopCardsOnBoard($player_id);
            
        foreach ($top_cards as $card_1) {
            $top_age = $card_1['faceup_age'];
            foreach ($top_cards as $card_2) {
                if ($card_1['id'] != $card_2['id'] && $card_2['faceup_age'] == $top_age) {
                    $colors[] = $card_1['color'];
                    continue 2;
                }
            }
        }
        
        return $colors;
    }

    function getDeckTopCard($age, $type) {
        /**
            Get all information of the card to be drawn from the deck of the type and age indicated, which includes:
                -intrisic properties,
                -owner, location and position
        **/
        
        return self::getObjectFromDB(self::format("
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
        ));
    }

    function getDeckBottomCard($age, $type) {
        /**
            Get all information of the card to be taken from the bottom of the deck of the type and age indicated, which includes:
                -intrisic properties,
                -owner, location and position
        **/
        
        return self::getObjectFromDB(self::format("
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
        ));
    }
    
    function getAgeToDrawIn($player_id, $age_min=null) {
        if($age_min === null){
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
    
    function getCurrentSplayDirection($player_id, $color): int {
        $splay_direction = self::getUniqueValueFromDB(self::format("
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
       ));
        
        return $splay_direction === null ? Directions::UNSPLAYED : intval($splay_direction);
    }
    
    function getIdsOfCardsInLocation($owner, $location) {
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
    
    function getIdsOfHighestOrLowestCardsInLocation($owner, $location, $highest) {
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
    
    function getIdsOfHighestCardsInLocation($owner, $location) {
        /**
            Get all highest cards in a particular location, sorted by position
        **/
        return self::getIdsOfHighestOrLowestCardsInLocation($owner, $location, true);
    }
    
    function getIdsOfLowestCardsInLocation($owner, $location) {
        /**
            Get all highest cards in a particular location, sorted by position
        **/
        return self::getIdsOfHighestOrLowestCardsInLocation($owner, $location, false);
    }

    function getSizeOfMaxVisiblePileOnBoard($owner) {
        /**
            Return the size of the stack(s) which have maximum number of visible cards on a specific player's board 
        **/
        return self::getUniqueValueFromDB(self::format("
            SELECT
                COALESCE(MAX(CASE WHEN splay_direction = 0 THEN 1 ELSE position + 1 END), 0)
            FROM
                card
            WHERE
                owner = {owner} AND
                location = 'board'
        ",
            array('owner' => $owner)
       ));
    }

    
    
    function getOrCountCardsInLocation($count, $owner, $location, $key = null, $type = null, $is_relic = null) {
        /**
            Get ($count is false) or count ($count is true) all the cards in a particular location, sorted by position. The result can be first keyed by age (for deck or hand) or color (for board) if needed
        **/
        
        $type_of_result = $count ? "COUNT(*)" : "*";
        $opt_order_by = $count ? "" : "ORDER BY position";
        $getFromDB = $count ? 'getUniqueValueFromDB' : 'getObjectListFromDB'; // If we count, we want to get an unique value, else, we want to get a list of cards
        $type_condition = $type === null ? "" : self::format("type = {type} AND", array('type' => $type));
        $is_relic_condition = $is_relic === null ? "" : self::format("is_relic = {is_relic} AND", array('is_relic' => ($is_relic ? 'TRUE' : 'FALSE')));

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
            $num_min = 1;
            $num_max = 11;
        } else if ($key == 'color') {
            $num_min = 0;
            $num_max = 4;
        } else {
            return self::$getFromDB(self::format("
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
            ));
        }
        
        $result = array();
        
        for ($value = $num_min; $value <= $num_max; $value++) {
            $result[$value] = self::$getFromDB(self::format("
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
           ));
        }
        return $result;
    }

    function getArtifactsOnDisplay($players) {
        $result = array();
        foreach($players as $player_id => $player) {
            $result[$player_id] = self::getArtifactOnDisplay($player_id);
        }
        return $result;
    }

    function getArtifactOnDisplay($player_id) {
        $cards = self::getCardsInLocation($player_id, 'display');
        if (empty($cards)) {
            return null;
        }
        return $cards[0];
    }
    
    function getBoards($player_ids) {
        $result = array();
        foreach ($player_ids as $player_id) {
            $result[$player_id] = self::getCardsInLocationKeyedByColor($player_id, 'board');
        }
        return $result;
    }

    function isTopBoardCard($card) {
        if ($card['position'] == null || $card['location'] != 'board') {
            return false;
        }
        $number_of_cards_above = self::getUniqueValueFromDB(self::format("
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
        ));
        return $number_of_cards_above == 0;
    }
    
    function hasThisColorOnBoard($player_id, $color) {
        $number_of_cards = self::getUniqueValueFromDB(self::format("
                SELECT
                    COUNT(*)
                FROM
                    card
                WHERE
                    owner = {owner} AND
                    location = 'board' AND
                    color = {color}",
                array('owner' => $player_id, 'color' => $color)
        ));
        return $number_of_cards > 0;
    }

    function getCardsInLocationKeyedByAge($owner, $location) {
        /**
            Get all the cards in a particular location, keyed by age, then sorted by position.
        **/
        $column = $location === 'board' ? 'faceup_age' : 'age';
        return self::getOrCountCardsInLocation(/*count=*/ false, $owner, $location, $column);
    }

    function getCardsInLocationKeyedByColor($owner, $location) {
        /**
            Get all the cards in a particular location, keyed by color, then sorted by position.
        **/
        return self::getOrCountCardsInLocation(/*count=*/ false, $owner, $location, 'color');
    }
    
    function getCardsInLocation($owner, $location) {
        /**
            Get all the cards in a particular location, sorted by position.
        **/
        return self::getOrCountCardsInLocation(/*count=*/ false, $owner, $location);
    }

    function getCardsInHand($player_id) {
        return self::getCardsInLocation($player_id, 'hand');
    }

    function countCardsInHand($player_id) {
        return self::countCardsInLocation($player_id, 'hand');
    }

    function getCardsInScorePile($player_id) {
        return self::getCardsInLocation($player_id, 'score');
    }

    function countCardsInLocationKeyedByAge($owner, $location, $type=null, $is_relic=null) {
        /**
            Count all the cards in a particular location, keyed by age.
        **/
        $column = $location === 'board' ? 'faceup_age' : 'age';
        return self::getOrCountCardsInLocation(/*count=*/ true, $owner, $location, $column, $type, $is_relic);
    }

    function countCardsInLocationKeyedByColor($owner, $location) {
        /**
            Count all the cards in a particular location, keyed by color.
        **/
        return self::getOrCountCardsInLocation(/*count=*/ true, $owner, $location, 'color');
    }
    
    function countCardsInLocation($owner, $location, $type=null) {
        /**
            Count all the cards in a particular location.
        **/
        return self::getOrCountCardsInLocation(/*count=*/ true, $owner, $location, /*key=*/ null, $type);
    }
    
    function getTopCardOnBoard($player_id, $color) {
        /**
        Get the top card of specified color
        (null if the player have no card on his board)
        **/
        return self::getObjectFromDB(self::format("
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
        ));
    }


function getOwnersOfTopCardWithColorAndAge($color, $age) {
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
    function getTopCardsOnBoard($player_id) {
        /**
        Get all of the top cards on a player board, or null if the player has no cards on his board
        **/
        return self::getCollectionFromDb(self::format("
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
        ));
    }
    
    function getIfTopCardOnBoard($id) {
        /**
        Returns the card if card is a top card on a board, or null if it isn't present as a top card
        **/
        return self::getObjectFromDB(self::format("
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
        ));
    }
    
    function getBottomCardOnBoard($player_id, $color) {
        /**
        Get the bottom card of specified color
        (null if the player have no card on his board)
        **/
        return self::getObjectFromDB(self::format("
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
        ));
    }

    function getSplayableColorsOnBoard($player_id, $splay_direction) {
        /**
        Returns the splayable colors in the specified direction on a player's board, in ascending order
        **/
        return self::getObjectListFromDB(self::format("
            SELECT
                color
            FROM
                card
            WHERE
                owner = {player_id} AND
                location = 'board' AND
                position = 1 AND
                splay_direction != {splay_direction}
            ORDER BY
                color
            ",
            array('player_id' => $player_id, 'splay_direction' => $splay_direction)
        ), true);
    }

    function getMinAgeOnBoardTopCards($player_id) {
        /**
        Get the age the player is in, that is to say, the minimum age that can be found on his board top cards
        (0 if the player have no card on his board)
        **/
        
        // Get the min of the age matching the position defined in the sub-request
        return self::getUniqueValueFromDB(self::format("
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
                a.position = b.position
        ",
            array('player_id' => $player_id)
       ));
    }

    function getMaxAgeOnBoardTopCards($player_id) {
        /**
        Get the age the player is in, that is to say, the maximum age that can be found on his board top cards
        (0 if the player have no card on his board)
        **/
        
        // Get the max of the age matching the position defined in the sub-request
        return self::getUniqueValueFromDB(self::format("
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
       ));
    }
    
    function getMaxAgeOfTopCardOfColor($color) {
        /**
        Get the maximum age that can be found on top of any player's pile of a specific color
        (0 if no players have that color on their board)
        **/
        
        // Get the max of the age matching the position defined in the sub-request
        return self::getUniqueValueFromDB(self::format("
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
       ));
    }

    function getMinAgeOnBoardTopCardsWithoutIcon($player_id, $icon) {
        /**
        Get the minimum age of the top cards with a particular icon
        (0 if the player have no card on his board)
        **/
        
        
        // Get the max of the age matching the position defined in the sub-request
        return self::getUniqueValueFromDB(self::format("
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
        ));
    }    
    function getMinAgeOnBoardTopCardsWithIcon($player_id, $icon) {
        /**
        Get the minimum age of the top cards with a particular icon
        (0 if the player have no card on his board)
        **/
        
        
        // Get the max of the age matching the position defined in the sub-request
        return self::getUniqueValueFromDB(self::format("
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
                (a.spot_1 = {icon} OR a.spot_2 = {icon} OR a.spot_3 = {icon} OR a.spot_4 = {icon} OR a.spot_5 = {icon} OR a.spot_6 = {icon})
        ",
            array('player_id' => $player_id, 'icon' => $icon)
        ));
    }

    function getMaxAgeOnBoardTopCardsWithIcon($player_id, $icon) {
        /**
        Get the maximum age of the top cards with a particular icon
        (0 if the player have no card on his board)
        **/
        
        
        // Get the max of the age matching the position defined in the sub-request
        return self::getUniqueValueFromDB(self::format("
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
        ));
    }
    
    function getMaxAgeOnBoardOfColorsWithoutIcon($player_id, $colors, $icon) {
        /**
        Get the maximum age of the top cards without a particular icon
        (0 if the player have no card on his board)
        **/
        
        // Get the max of the age matching the position defined in the sub-request
        return self::getUniqueValueFromDB(self::format("
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
        ));
    }
    
    function getMinOrMaxAgeInLocation($player_id, $location, $min_or_max) {
        /**
        Get the minimum or maximum age that can be found in a player particular location
        (0 if the player have no card in this location)
        **/
        
        return self::getUniqueValueFromDB(self::format("
            SELECT
                COALESCE({min_or_max}(age), 0)
            FROM
                card AS a
            WHERE
                owner = {player_id} AND
                location = '{location}'
        ",
            array('min_or_max' => $min_or_max, 'player_id' => $player_id, 'location' => $location)
        ));
    }
    
    function getMinAgeInHand($player_id) {
        /**
        Get the minimum age that can be found in a player hand
        (0 if the player have no card in his hand)
        **/
        return self::getMinOrMaxAgeInLocation($player_id, 'hand', 'MIN');
    }
    
    function getMaxAgeInHand($player_id) {
        /**
        Get the maximum age that can be found in a player hand
        (0 if the player have no card in his hand)
        **/
        return self::getMinOrMaxAgeInLocation($player_id, 'hand', 'MAX');
    }
    
    function getMinAgeInScore($player_id) {
        /**
        Get the minimum age that can be found in a player score
        (0 if the player have no card in his score pile)
        **/
        return self::getMinOrMaxAgeInLocation($player_id, 'score', 'MIN');
    }
    
    function getMaxAgeInScore($player_id) {
        /**
        Get the maximum age that can be found in a player score
        (0 if the player have no card in his score pile)
        **/
        return self::getMinOrMaxAgeInLocation($player_id, 'score', 'MAX');
    }
    
    function getMaxBonusIconOnBoard($player_id) {
        /**
        Get the maximum visible bonus
        **/
        $visible_bonus_icons = self::getVisibleBonusesOnBoard($player_id);
        return count($visible_bonus_icons) == 0 ? 0 : max($visible_bonus_icons);
    }

    function getCardsWithVisibleEchoEffects($dogma_card) {
        /**
        Gets the list of cards with visible echo effects given a specific card being executed (from top to bottom)
        **/

        $color = $dogma_card['color'];
        $pile = self::getCardsInLocationKeyedByColor($dogma_card['owner'], 'board')[$color];

        $visible_echo_effects = array();

        // Handle the case when the card being executed isn't even in the pile (e.g. Artifact on display)
        if ($dogma_card['location'] != 'board') {
            if (self::countIconsOnCard($dogma_card, 10 /* echo effect */) > 0) {
                $visible_echo_effects[] = $dogma_card['id'];
            }
        }

        for ($i = count($pile) - 1; $i >= 0; $i--) {
            $card = $pile[$i];
            $splay_direction = $card['splay_direction'];

            $has_visible_echo_efffect = false;
            if ($i == count($pile) - 1 && self::countIconsOnCard($card, 10 /* echo effect */) > 0) {
                $has_visible_echo_efffect = true;
            } else if ($splay_direction == 1) { // left
                $has_visible_echo_efffect = $card['spot_4'] == 10 || $card['spot_5'] == 10;
            } else if ($splay_direction == 2) { // right
                $has_visible_echo_efffect = $card['spot_1'] == 10 || $card['spot_2'] == 10;
            } else if ($splay_direction == 3) { // up
                $has_visible_echo_efffect = $card['spot_2'] == 10 || $card['spot_3'] == 10 || $card['spot_4'] == 10;
            } else if ($splay_direction == 4) { // aslant
                $has_visible_echo_efffect = $card['spot_1'] == 10 || $card['spot_2'] == 10 || $card['spot_3'] == 10 || $card['spot_4'] == 10;
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
    
    function getVisibleBonusesOnPile($player_id, $color) {
        /**
        Gets the list of bonus icons visible on a specific pile
        **/
        $visible_bonus_icons = array();
        
        $board = self::getCardsInLocationKeyedByColor($player_id, 'board');
        $pile = $board[$color];
        if (count($pile) == 0) { // No card of that color
            return $visible_bonus_icons;
        }
        $top_card = $pile[count($pile)-1];
        $visible_bonus_icons = self::getBonusIcons($top_card);
        
        $splay_direction = $top_card['splay_direction'];
        if ($splay_direction == 0) { // Unsplayed
            return $visible_bonus_icons;
        }
        // Since the stack is not unsplayed, it has at least two cards
        for($i=0; $i<count($pile)-1; $i++) {
            $card = $pile[$i];
            if($splay_direction == 1) { // left
                if ($card['spot_4'] >= 101 && $card['spot_4'] <= 112)
                {
                    $visible_bonus_icons[] = $card['spot_4'] - 100;
                }
                if ($card['spot_5'] >= 101 && $card['spot_5'] <= 112) {
                    $visible_bonus_icons[] = $card['spot_5'] - 100;
                }
            }
            elseif ($splay_direction == 2) { // right
                if ($card['spot_1'] >= 101 && $card['spot_1'] <= 112) {
                    $visible_bonus_icons[] = $card['spot_1'] - 100;
                }
                if ($card['spot_2'] >= 101 && $card['spot_2'] <= 112) {
                    $visible_bonus_icons[] = $card['spot_2'] - 100;
                }
            }
            elseif ($splay_direction == 3) { // up
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
            elseif ($splay_direction == 4) { // aslant
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

    
    function getVisibleBonusesOnBoard($player_id) {
        $visible_bonus_icons = array();
        
        for ($color = 0; $color < 5; $color++) {
            $visible_bonus_icons = array_merge($visible_bonus_icons, self::getVisibleBonusesOnPile($player_id, $color));
        }
        return $visible_bonus_icons;
    }

    function getBonusIcons($card) {
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
    function hasRessource($card, $icon) {
        return $card !== null && ($card['spot_1'] == $icon || $card['spot_2'] == $icon || $card['spot_3'] == $icon || $card['spot_4'] == $icon || $card['spot_5'] == $icon || $card['spot_6'] == $icon);
    }
    
    /* Count the number of a particular icon on the specified card */
    function countIconsOnCard($card, $icon) {
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

    function boardPileHasRessource($player_id, $color, $icon) {
        $board = self::getCardsInLocationKeyedByColor($player_id, 'board');
        $pile = $board[$color];
        if (count($pile) == 0) { // No card of that color
            return false;
        }
        $top_card = $pile[count($pile)-1];
        if (self::hasRessource($top_card, $icon)) { // The top card of that stack has that icon
            return true;
        }
        $splay_direction = $top_card['splay_direction'];
        if ($splay_direction == 0) { // Unsplayed
            return false;
        }
        // Since the stack is not unsplayed, it has at least two cards
        for($i=0; $i<count($pile)-1; $i++) {
            $card = $pile[$i];
            if($splay_direction == 1 && ($card['spot_4'] == $icon || $card['spot_5'] == $icon) || 
                    $splay_direction == 2 && ($card['spot_1'] == $icon || $card['spot_2'] == $icon) || 
                    $splay_direction == 3 && ($card['spot_2'] == $icon || $card['spot_3'] == $icon || $card['spot_4'] == $icon) ||
                    $splay_direction == 4 && ($card['spot_1'] == $icon || $card['spot_2'] == $icon || $card['spot_3'] == $icon || $card['spot_4'] == $icon)) {
                return true;
            }
        }
        return false;
    }
    
    /** Counts the number of visible cards based on the splay **/
    function countVisibleCards($player_id, $color) {
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
    function incrementBGAScore($player_id, $is_special_achievement) { // Increment the BGA score of the team (single player or to player in 2 vs 2 game) (number of achievements) then check if he got enough to win
        $player = self::getObjectFromDB(self::format(
            "SELECT
                player_score, player_team
            FROM
                player
            WHERE
                player_id={player_id}"
            ,
                array('player_id' => $player_id)));
        
        $player['player_score']++;
        
        self::DbQuery(self::format(
            "UPDATE
                player
            SET
                player_score = {player_score}
            WHERE
                player_team={player_team}"
            ,
                $player));
                
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
    function decrementBGAScore($player_id) {
        $player = self::getObjectFromDB(self::format(
            "SELECT
                player_score, player_team
            FROM
                player
            WHERE
                player_id={player_id}"
            ,
                array('player_id' => $player_id)));
        
        $player['player_score']--;
        
        self::DbQuery(self::format(
            "UPDATE
                player
            SET
                player_score = {player_score}
            WHERE
                player_team={player_team}"
            ,
                $player));
                
        // Stats
        self::incStat(-1, 'achievements_number', $player_id);
    }

    function getPlayerScore($player_id) { // Player Innovation score is different from the BGA score (number of achievements)
        return self::getUniqueValueFromDB(self::format("
        SELECT
            player_innovation_score
        FROM
            player
        WHERE
            player_id = {player_id}
        ",
            array('player_id' => $player_id)
       ));
    }
    
    function getPlayerNumberOfAchievements($player_id) { // Player Innovation score is different from the BGA score (number of achievements)
        return self::getUniqueValueFromDB(self::format("
        SELECT
            player_score
        FROM
            player
        WHERE
            player_id = {player_id}
        ",
            array('player_id' => $player_id)
       ));
    }
    
    function updatePlayerScore($player_id) {
        $score = 0;
        foreach (self::getCardsInLocation($player_id, 'score') as $card) {
            $score += $card['age'];
        }
        $score += self::countBonusPoints($player_id);
        self::DBQuery(self::format("
            UPDATE
                player
            SET
                player_innovation_score = {score}
            WHERE
                player_id = {player_id}
        ",
            array('player_id' => $player_id, 'score' => $score)
        ));
        self::setStat($score, 'score', $player_id);
        return $score;
    }

    function countBonusPoints($player_id) {
        $num_visible_bonus_icons = count(self::getVisibleBonusesOnBoard($player_id));
        if ($num_visible_bonus_icons > 0) {
            return self::getMaxBonusIconOnBoard($player_id) + $num_visible_bonus_icons - 1;
        }
        return 0;
    }
    
    // Returns the icon count for a particular color on a player's board (also works for hexagon icons if icon=0)
    function countVisibleIconsInPile($player_id, $icon, $color) {
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
            if ($splayed_right || $splayed_up) {
                if ($card['spot_2'] !== null && $card['spot_2'] == $icon) {
                    $count += 1;
                }
            }
            if ($splayed_up) {
                if ($card['spot_3'] !== null && $card['spot_3'] == $icon) {
                    $count += 1;
                }
            }
            if ($splayed_left || $splayed_up) {
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
    
    function getPlayerSingleRessourceCount($player_id, $icon) {
        return self::getUniqueValueFromDB(self::format("
            SELECT
                player_icon_count_{icon}
            FROM
                player
            WHERE
                player_id = {player_id}
        ",
            array('player_id' => $player_id, 'icon' => $icon)
        ));
    }
    
    function getPlayerResourceCounts($player_id) {
        $table = self::getNonEmptyObjectFromDB(self::format("
        SELECT
            player_icon_count_1, player_icon_count_2, player_icon_count_3, player_icon_count_4, player_icon_count_5, player_icon_count_6, player_icon_count_7
        FROM
            player
        WHERE
            player_id = {player_id}
        ",
            array('player_id' => $player_id)
        ));
        
        // Convert to a numeric associative array
        $result = array();
        for ($icon = 1; $icon <= 7; $icon++) {
            $result[$icon] = $table["player_icon_count_".$icon];
        }
        return $result;
    }
    
    function getPlayerWishForSplay($player_id) {
        return self::getUniqueValueFromDB(self::format("
        SELECT
            pile_display_mode
        FROM
            player
        WHERE
            player_id = {player_id}
        ",
            array('player_id' => $player_id)
       )) == 1;
    }
    
    function getPlayerWishForViewFull($player_id) {
        return self::getUniqueValueFromDB(self::format("
        SELECT
            pile_view_full
        FROM
            player
        WHERE
            player_id = {player_id}
        ",
            array('player_id' => $player_id)
       )) == 1;
    }
    
    function setPlayerWishForSplay($player_id, $pile_display_mode) {
        self::DbQuery(self::format("
        UPDATE
            player
        SET
            pile_display_mode = {pile_display_mode}
        WHERE
            player_id = {player_id}
        ",
            array('player_id' => $player_id, 'pile_display_mode' => $pile_display_mode ? "TRUE" : "FALSE")
        ));
    }
    
    function setPlayerWishForViewFull($player_id, $pile_view_full) {
        self::DbQuery(self::format("
        UPDATE
            player
        SET
            pile_view_full = {pile_view_full}
        WHERE
            player_id = {player_id}
        ",
            array('player_id' => $player_id, 'pile_view_full' => $pile_view_full ? "TRUE" : "FALSE")
        ));
    }

    function getPlayerWillDrawUnseenCardNext($player_id) {
        return self::getUniqueValueFromDB(self::format("
        SELECT
            will_draw_unseen_card_next
        FROM
            player
        WHERE
            player_id = {player_id}
        ",
            array('player_id' => $player_id)
       )) == 1;
    }

    function setPlayerWillDrawUnseenCardNext($player_id, $will_draw_unseen_card_next) {
        self::DbQuery(self::format("
        UPDATE
            player
        SET
            will_draw_unseen_card_next = {will_draw_unseen_card_next}
        WHERE
            player_id = {player_id}
        ",
            array('player_id' => $player_id, 'will_draw_unseen_card_next' => $will_draw_unseen_card_next ? "TRUE" : "FALSE")
        ));
    }

    function resetWillDrawUnseenCardNext() {
        self::DbQuery("UPDATE player SET will_draw_unseen_card_next = TRUE");
    }
    
    function updatePlayerRessourceCounts($player_id) {     
        self::DbQuery("
            INSERT INTO
                base (icon)
            VALUES
                (1), (2), (3), (4), (5), (6), (7)
        ");
        
        self::DbQuery(self::format("
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
       ));
        
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
        
        self::DbQuery(self::format("
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
        ));
        
        // Delete all values of the auxiliary tables
        self::DbQuery("DELETE FROM card_with_top_card_indication");
        self::DbQuery("DELETE FROM base");
        self::DbQuery("DELETE FROM icon_count");
        
        return self::getPlayerResourceCounts($player_id);
    }
    
    function promoteScoreToBGAScore() {
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
    
    function binarizeBGAScore() {
        // Called if the game ends by dogma. The innovation score is 1 for winners, 0 for losers. There is no tie-breaker.
        self::DbQuery(self::format("
        UPDATE
            player
        SET
            player_score_aux = 0,
            player_score = (CASE WHEN player_id = {winner} THEN 1 ELSE 0 END)
        ",
            array('winner' => $this->innovationGameState->get('winner_by_dogma'))        
        ));
        
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
    function getPlayerNameFromId($player_id) {
        // TODO(LATER): Identify and fix the nested execution bug which makes this hack necessary.
        if ($player_id == -1 || $player_id == null) {
            return "unknown";
        }
        $players = self::loadPlayersBasicInfos();
        return $players[$player_id]['player_name'];
    }
    
    function getPlayerColorFromId($player_id) {
        // TODO(LATER): Identify and fix the nested execution bug which makes this hack necessary.
        if ($player_id == -1 || $player_id == null) {
            return "unknown";
        }
        $players = self::loadPlayersBasicInfos();
        return $players[$player_id]['player_color'];
    }
    
    function getPlayerTeam($player_id) {
        return self::getUniqueValueFromDB(self::format("
            SELECT
                player_id
            FROM
                player
            WHERE
                player_id={player_id}
        ",
            array('player_id' => $player_id)
        ));
    }

    function countNonEliminatedPlayers() {
        return self::getUniqueValueFromDB("
            SELECT
                COUNT(*)
            FROM
                player
            WHERE
                player_eliminated=0
        ");
    }
    
    function getPlayerTeammate($player_id) {
        /** Return the teammate in a team game or null if there is None **/
        return self::getUniqueValueFromDB(self::format("
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
        ));
    }
    
    /** Information when in dogma **/
    function qualifyEffect($current_effect_type, $current_effect_number, $card) {
        $unique_non_demand_effect = self::getNonDemandEffect($card['id'], 2) === null;
        
        return $current_effect_type == 0 ? clienttranslate('I demand effect') :
               ($current_effect_type == 2 ? clienttranslate('I compel effect') :
               ($current_effect_type == 3 ? clienttranslate('echo effect') :
               ($unique_non_demand_effect ? clienttranslate('non-demand effect') :
               ($current_effect_number == 1 ? clienttranslate('1<sup>st</sup> non-demand effect') :
               ($current_effect_number == 2 ? clienttranslate('2<sup>nd</sup> non-demand effect') : clienttranslate('3<sup>rd</sup> non-demand effect'))))));
    }
                                  
    function getFirstPlayerUnderEffect($dogma_effect_type, $launcher_id) {
        return self::getNextPlayerUnderEffect($dogma_effect_type, -1, $launcher_id);
    }
       
    /* Returns the ID of the next player under effect, or null */
    function getNextPlayerUnderEffect($dogma_effect_type, $player_id, $launcher_id) {
        // I demand
        $launcher_icon_count = self::getUniqueValueFromDB(self::format("
            SELECT
                featured_icon_count
            FROM
                player
            WHERE
                player_id = {launcher_id}
        ",
            array('launcher_id' => $launcher_id)
        ));
        // I demand
        if ($dogma_effect_type == 0) {
            $player_query = self::format(
                "featured_icon_count < {launcher_icon_count} AND player_id != {launcher_id} AND player_team <> (SELECT player_team FROM player WHERE player_id = {launcher_id}) AND distance_rule_demand_state != 3",
                array('launcher_id' => $launcher_id, 'launcher_icon_count' => $launcher_icon_count)
            );
        // I compel
        } else if ($dogma_effect_type == 2) {
            $player_query = self::format(
                "featured_icon_count >= {launcher_icon_count} AND player_id != {launcher_id} AND player_team <> (SELECT player_team FROM player WHERE player_id = {launcher_id}) AND distance_rule_demand_state != 3",
                array('launcher_id' => $launcher_id, 'launcher_icon_count' => $launcher_icon_count)
            );
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
        return self::getUniqueValueFromDB(self::format("
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
        ));
    }
    
    function renderColorCards($color) {
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
    
    function renderNumber($number) {
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
    
    function getColoredText($text, $player_id) {
        $color = self::getPlayerColorFromId($player_id);
        return "<span style='font-weight: bold; color:#".$color.";'>".$text."</span>";
    }

    function renderPlayerName($player_id) {
        return self::getColoredText(self::getPlayerNameFromId($player_id), $player_id);
    }
    
    /** Execution of actions authorized by server **/

    function executeDrawAndScore($player_id, $age_min = null) {
        return self::executeDraw($player_id, $age_min, 'score');
    }

    function executeDrawAndReveal($player_id, $age_min = null, $type = null) {
        return self::executeDraw($player_id, $age_min, 'revealed', /*bottom_to=*/ false, $type);
    }

    function executeDrawAndMeld($player_id, $age_min = null, $type = null) {
        return self::executeDraw($player_id, $age_min, 'board', /*bottom_to=*/ false, $type, /*bottom_from=*/ false, /*meld_keyword=*/ true);
    }

    function executeDrawAndTuck($player_id, $age_min = null, $type = null) {
        return self::executeDraw($player_id, $age_min, 'board', /*bottom_to=*/ true, $type);
    }

    /* Execute a draw. If $age_min is null, draw in the deck according to the board of the player, else, draw a card of the specified value or more, according to the rules */
    function executeDraw($player_id, $age_min = null, $location_to = 'hand', $bottom_to = false, $type = null, $bottom_from = false, $meld_keyword = false) {
        $age_to_draw = self::getAgeToDrawIn($player_id, $age_min);
        
        $max_age = self::getMaxAge();
        if ($age_to_draw > $max_age) {
            // Attempt to draw a card above the max age : end of the game by score
            $this->innovationGameState->set('game_end_type', 1);
            $this->innovationGameState->set('player_who_could_not_draw', $player_id);
            self::trace('EOG bubbled from self::executeDraw (age higher than highest deck age');
            throw new EndOfGame();
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
            $card = self::transferCardFromTo($card, $player_id, $location_to, ['bottom_to' => $bottom_to, 'score_keyword' => $location_to == 'score', 'bottom_from' => $bottom_from]);
        }
        catch (EndOfGame $e) {
            self::trace('EOG bubbled from self::executeDraw');
            throw $e; // Re-throw exception to higher level
        }

        if ($type == 2) {
            self::incStat(1, 'city_cards_drawn_number', $player_id);
        }

        return $card;
    }

    function getCardTypeToDraw($age_to_draw, $player_id) {
        $card_type = CardTypes::BASE;

        if ($this->innovationGameState->echoesExpansionEnabled()) {
            if ($this->innovationGameState->usingFourthEditionRules()) {
                // Draw an Echoes card if yellow top card is higher than the blue top card
                $topBlue = self::getTopCardOnBoard($player_id, Colors::BLUE);
                $topYellow = self::getTopCardOnBoard($player_id, Colors::YELLOW);
                if ($topYellow && (!$topBlue || $topYellow['faceup_age'] > $topBlue['faceup_age'])) {
                    $card_type = CardTypes::ECHOES;
                }
            } else {
                // Draw an Echoes card if none is currently in hand and at least one other card is in hand (drawn and revealed counts as being in hand)
                if ((self::countCardsInLocation($player_id, 'hand') + self::countCardsInLocation($player_id, 'revealed')) > 0 &&
                        self::countCardsInLocation($player_id, 'hand', CardTypes::ECHOES) == 0 && 
                        self::countCardsInLocation($player_id, 'revealed', CardTypes::ECHOES) == 0) {
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

    function getMaxAge() {
        return $this->innovationGameState->usingFourthEditionRules() ? 11 : 10;
    }

    function junkBaseDeck($age): bool {
        $cardCount = self::countCardsInLocationKeyedByAge(/*owner=*/ 0, 'deck', CardTypes::BASE)[$age];
        if ($cardCount == 0) {
            self::notifyGeneralInfo(clienttranslate('No cards were left in the ${age} deck to junk.'),  array('age' => self::getAgeSquareWithType($age, CardTypes::BASE)));
            return false;
        }

       $nextJunkPosition = 1 + self::getUniqueValueFromDB(self::format("
            SELECT
                COALESCE(MAX(position), -1)
            FROM
                card
            WHERE
                owner = 0
                AND location = 'junk'
                AND age = {age}
                AND type = 0
        ", ["age" => $age]));

        self::DbQuery(
            self::format("
                UPDATE
                    card
                SET
                    location = 'junk',
                    position = {nextJunkPosition} + position
                WHERE
                    owner = 0
                    AND location = 'deck'
                    AND age = {age}
                    AND type = 0
            ", ["age" => $age, "nextJunkPosition" => $nextJunkPosition],
            )
        );
        self::recordThatChangeOccurred();
        self::notifyAll(
            'junkedBaseDeck',
            clienttranslate('The ${age} deck, which contained ${n} card(s), is junked.'),
            [
                'i18n' => ['n'],
                'age' => self::getAgeSquareWithType($age, CardTypes::BASE),
                'n' => self::renderNumber($cardCount),
                'age_to_junk' => $age,
                'next_junk_position' => $nextJunkPosition,
            ]
        );
        return true;
    }
    
    function removeAllHandsBoardsAndScores() {
        self::DbQuery("
            UPDATE
                card
            SET
                owner = 0,
                location = 'removed',
                position = NULL
            WHERE
                location IN ('hand', 'board', 'score', 'revealed')
        ");
        
        // Set statistics back to zero
        self::DbQuery("
            UPDATE
                player
            SET
                player_innovation_score = 0,
                player_icon_count_1 = 0,
                player_icon_count_2 = 0,
                player_icon_count_3 = 0,
                player_icon_count_4 = 0,
                player_icon_count_5 = 0,
                player_icon_count_6 = 0,
                player_icon_count_7 = 0
        ");
        
        // Stats
        $players = self::loadPlayersBasicInfos();
        foreach($players as $player_id => $player) {
            self::setStat(0, 'score', $player_id);
            self::setStat(0, 'max_age_on_board', $player_id);
        }

        self::removeOldFlagsAndFountains();
    }

    function removeAllCardsFromPlayer($player_id) {
        // Even if no cards are removed we will still mark that change has occurred because a player has been eliminated.
        self::recordThatChangeOccurred();

        self::DbQuery(self::format("
            UPDATE
                card
            SET
                owner = 0,
                location = 'removed',
                position = NULL
            WHERE
                owner = {player_id}
        ", array('player_id' => $player_id)));

        self::DbQuery(self::format("
            UPDATE
                player
            SET
                player_score = 0,
                player_innovation_score = 0,
                player_icon_count_1 = 0,
                player_icon_count_2 = 0,
                player_icon_count_3 = 0,
                player_icon_count_4 = 0,
                player_icon_count_5 = 0,
                player_icon_count_6 = 0,
                player_icon_count_7 = 0
            WHERE
                player_id = {player_id}
        ", array('player_id' => $player_id)));

        self::setStat(0, 'achievements_number', $player_id);
        self::setStat(0, 'special_achievements_number', $player_id);
        self::setStat(0, 'score', $player_id);
        self::setStat(0, 'max_age_on_board', $player_id);

        self::notifyPlayer($player_id, 'removedPlayer', clienttranslate('All ${your} cards were removed from the game.'), array(
            'your' => 'your',
            'player_to_remove' => $player_id,
        ));
        self::notifyAllPlayersBut($player_id, 'removedPlayer', clienttranslate('All ${player_name}\'s cards were removed from the game.'), array(
            'player_name' => self::renderPlayerName($player_id),
            'player_to_remove' => $player_id,
        ));
    }

    function removeAllTopCardsAndHands() {
        // Remove cards from all players' hands.
        self::DbQuery("
            UPDATE
                card
            SET
                owner = 0,
                location = 'removed',
                position = NULL
            WHERE
                location = 'hand'
        ");
        if (self::DbAffectedRow() > 0) {
            self::recordThatChangeOccurred();
        }
        
        // Get list of the all top cards on the board.
        $top_cards_to_remove = array();
        $player_ids = self::getAllActivePlayerIds();
        foreach ($player_ids as $player_id) {
            $top_cards_to_remove = array_merge($top_cards_to_remove, self::getTopCardsOnBoard($player_id));
        }

        // Remove top cards from boards.
        self::DbQuery("
            UPDATE
                card
            LEFT JOIN
                (SELECT
                    owner, color, MAX(position) AS position
                FROM
                    card
                WHERE
                    location = 'board'
                GROUP BY
                    owner, color) AS b ON card.owner = b.owner AND card.color = b.color
            SET
                card.owner = 0,
                card.location = 'removed',
                card.position = NULL
            WHERE
                card.location = 'board' AND
                card.owner = b.owner AND
                card.color = b.color AND
                card.position = b.position
        ");
        if (self::DbAffectedRow() > 0) {
            self::recordThatChangeOccurred();
        }

        $new_resource_counts_by_player = array();
        $new_max_age_on_board_by_player = array();
        foreach ($player_ids as $player_id) {
            $new_resource_counts_by_player[$player_id] = self::updatePlayerRessourceCounts($player_id);
            $new_max_age_on_board = self::getMaxAgeOnBoardTopCards($player_id);
            $new_max_age_on_board_by_player[$player_id] = $new_max_age_on_board;
            self::setStat($new_max_age_on_board, 'max_age_on_board', $player_id);
        }
        self::notifyAll('removedTopCardsAndHands', clienttranslate('All top cards on all boards and all cards in all hands are removed from the game.'), array(
            'new_resource_counts_by_player' => $new_resource_counts_by_player,
            'new_max_age_on_board_by_player' => $new_max_age_on_board_by_player,
            'top_cards_to_remove' => $top_cards_to_remove,
        ));

        // Unsplay all stacks which only have one card left in them.
        foreach (self::getActivePlayerIdsInTurnOrderStartingWithCurrentPlayer() as $player_id) {
            $number_of_cards_per_pile = self::countCardsInLocationKeyedByColor($player_id, 'board');
            for ($color = 0; $color < 5; $color++) {
                if ($number_of_cards_per_pile[$color] == 1) {
                    self::splay($player_id, $player_id, $color, Directions::UNSPLAYED);
                }
            }
        }

        $end_of_game = false;

        self::removeOldFlagsAndFountains();
        try {
            self::addNewFlagsAndFountains();
        } catch(EndOfGame $e) {
            $end_of_game = true;
        }

        try {
            self::checkForSpecialAchievements();
        } catch(EndOfGame $e) {
            $end_of_game = true;
        }

        if ($end_of_game) {
            self::trace('EOG bubbled from self::removeAllTopCardsAndHands');
            throw $e; // Re-throw exception to higher level
        }
    }
    
    function setSelectionRange($options) {

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

        if (self::getActivePlayerId() != $player_id) {
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
        foreach($possible_special_types_of_choice as $special_type_of_choice) {
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
        if (!array_key_exists('n_min', $rewritten_options)) {
            $rewritten_options['n_min'] = 999;
        }
        if (!array_key_exists('n_max', $rewritten_options)) {
            $rewritten_options['n_max'] = 999;
        }
        if (!array_key_exists('solid_constraint', $rewritten_options)) {
            $rewritten_options['solid_constraint'] = false;
        }
        if (!array_key_exists('age_min', $rewritten_options)) {
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
            $rewritten_options['enable_autoselection'] = 1;
        }
        if (!array_key_exists('include_relics', $rewritten_options)) {
            $rewritten_options['include_relics'] = 1;
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
        
        foreach($rewritten_options as $key => $value) {
            switch($key) {
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
            case 'has_demand_effect':
            case 'bottom_from':
            case 'bottom_to':
            case 'enable_autoselection':
            case 'include_relics':
            case 'with_bonus':
            case 'without_bonus':
            case 'card_ids_are_in_auxiliary_array':
                $value = $value ? 1 : 0;
                break;
            case 'location_from':
            case 'location_to':
                $value = self::encodeLocation($value);
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
                $this->innovationGameState->set($key, $value);
            }
        }
        
        // Set the selection on DB side
        self::selectEligibleCards();
        
        $this->innovationGameState->set('n', 0);
    }
    
    function selectEligibleCards() {
        // Select in database the eligible cards for the current selection to be made.
        // Return the number of selected cards that way

        $player_id = self::getActivePlayerId();
        
        // Condition for owner
        $owner_from = $this->innovationGameState->get('owner_from');
        if ($owner_from == -2) { // Any player
            $condition_for_owner = "owner <> 0";
        } else if ($owner_from == -3) { // Any opponent
            $opponents = self::getObjectListFromDB(self::format("
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
                array('player_id' => $player_id)), true
            );
            $condition_for_owner = self::format("owner IN ({opponents})", array('opponents' => join(',', $opponents)));
        } else if ($owner_from == -4) { // Any other player
            $other_players = self::getObjectListFromDB(self::format("
                SELECT
                    player_id
                FROM
                    player
                WHERE
                    player_id <> {player_id}
            ",
                array('player_id' => $player_id)), true
            );
            $condition_for_owner = self::format("owner IN ({other_players})", array('other_players' => join(',', $other_players)));
        } else {
            $condition_for_owner = self::format("owner = {owner_from}", array('owner_from' => $owner_from));
        }
        
        // Condition for location
        $location_from = self::decodeLocation($this->innovationGameState->get('location_from'));
        if ($location_from == 'revealed,hand') {
            $condition_for_location = "location IN ('revealed', 'hand')";
        } else if ($location_from == 'revealed,score') {
            $condition_for_location = "location IN ('revealed', 'score')";
        } else if ($location_from == 'hand,score') {
            $condition_for_location = "location IN ('hand', 'score')";
        } else if ($location_from == 'pile') {
            $condition_for_location = "location = 'board'";
        } else if ($location_from == 'pile,score') {
            $condition_for_location = "location IN ('board', 'score')";
        } else {
            $condition_for_location = self::format("location = '{location_from}'", array('location_from' => $location_from));
        }
        
        // Condition for age
        $age_min = $this->innovationGameState->get('age_min');
        $age_max = $this->innovationGameState->get('age_max');
        $condition_for_age = self::format("age BETWEEN {age_min} AND {age_max}", array('age_min' => $age_min, 'age_max' => $age_max));
        // TODO(LATER): Take 'age_array' into account if there are any cards which need to rely on this mechanism.

        // Condition for age because of achievement eligibility
        $claimable_ages = array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11);
        if ($this->innovationGameState->get('require_achievement_eligibility') == 1) {
            $claimable_ages = self::getClaimableValuesIgnoringAvailability($player_id);
            if (count($claimable_ages) == 0) {
                // Avoid calling a SQL query with 'age IN ()' in it since it isn't correct syntax.
                $claimable_ages[] = -1;
            }
        }
        $condition_for_claimable_ages = self::format("age IN ({claimable_ages})", array('claimable_ages' => join(',', $claimable_ages)));

        // Condition for whether it has a demand effect
        $condition_for_demand_effect = "TRUE";
        if ($this->innovationGameState->get('has_demand_effect') == 1) {
            $condition_for_demand_effect = "has_demand = TRUE";
        }
        
        // Condition for color
        $color_array = $this->innovationGameState->getAsArray('color_array');
        $condition_for_color = count($color_array) == 0 ? "FALSE" : "color IN (".join(',', $color_array).")";

        // Condition for type
        $type_array = $this->innovationGameState->getAsArray('type_array');
        $condition_for_type = count($type_array) == 0 ? "FALSE" : "type IN (".join(',', $type_array).")";
        
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
                )", array(
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
            $condition_for_splay = "splay_direction IN (".join(',', $splay_directions).")";
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

        // Condition for including relic
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
        
        if ($this->innovationGameState->get('splay_direction') == -1 && $location_from == 'board') {
            self::DbQuery(self::format("
                UPDATE
                    card
                LEFT JOIN
                    (SELECT owner AS joined_owner, color AS joined_color, {position} AS position_to_select FROM card WHERE location = 'board' GROUP BY owner, color) AS joined
                    ON
                        owner = joined_owner AND
                        color = joined_color
                SET
                    selected = TRUE
                WHERE
                    {condition_for_owner} AND
                    {condition_for_location} AND
                    {condition_for_age} AND
                    {condition_for_claimable_ages} AND
                    {condition_for_demand_effect} AND
                    position = position_to_select AND
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
            ",
                array(
                    'position' => $this->innovationGameState->get('bottom_from') == 1 ? '0' : 'MAX(position)',
                    'condition_for_owner' => $condition_for_owner,
                    'condition_for_location' => $condition_for_location,
                    'condition_for_age' => $condition_for_age,
                    'condition_for_claimable_ages' => $condition_for_claimable_ages,
                    'condition_for_demand_effect' => $condition_for_demand_effect,
                    'condition_for_color' => $condition_for_color,
                    'condition_for_type' => $condition_for_type,
                    'condition_for_icon' => $condition_for_icon,
                    'condition_for_icon_hash' => $condition_for_icon_hash,
                    'condition_for_splay' => $condition_for_splay,
                    'condition_for_requiring_id' => $condition_for_requiring_id,
                    'condition_for_excluding_id' => $condition_for_excluding_id,
                    'condition_for_including_relic' => $condition_for_including_relic,
                    'condition_for_including_bonus' => $condition_for_including_bonus,
                    'condition_for_excluding_bonus' => $condition_for_excluding_bonus
                )
            ));
        }
        else {
            self::DbQuery(self::format("
                UPDATE
                    card
                SET
                    selected = TRUE
                WHERE
                    {condition_for_owner} AND
                    {condition_for_location} AND
                    {condition_for_age} AND
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
            ",
                array(
                    'condition_for_owner' => $condition_for_owner,
                    'condition_for_location' => $condition_for_location,
                    'condition_for_age' => $condition_for_age,
                    'condition_for_claimable_ages' => $condition_for_claimable_ages,
                    'condition_for_demand_effect' => $condition_for_demand_effect,
                    'condition_for_color' => $condition_for_color,
                    'condition_for_type' => $condition_for_type,
                    'condition_for_icon' => $condition_for_icon,
                    'condition_for_icon_hash' => $condition_for_icon_hash,
                    'condition_for_splay' => $condition_for_splay,
                    'condition_for_requiring_id' => $condition_for_requiring_id,
                    'condition_for_excluding_id' => $condition_for_excluding_id,
                    'condition_for_including_relic' => $condition_for_including_relic,
                    'condition_for_including_bonus' => $condition_for_including_bonus,
                    'condition_for_excluding_bonus' => $condition_for_excluding_bonus
                )
            ));
        }
        
        return self::getUniqueValueFromDB("SELECT COUNT(*) FROM card WHERE selected IS TRUE");
    }

    function renderLocation($location) {
        switch($location) {
        case 'deck':
            return clienttranslate('deck');
        case 'hand':
            return clienttranslate('hand');
        case 'board':
            return clienttranslate('board');
        case 'score':
            return clienttranslate('score pile');
        case 'forecast':
            return clienttranslate('forecast');
        case 'safe':
            return clienttranslate('safe');
        default:
            // NOTE: If this code path gets hit, then that means we are not properly translating it.
            return $location;
        }
    }
    
    function encodeLocation($location) {
        switch($location) {
        case 'deck':
            return 0;
        case 'hand':
            return 1;
        case 'board':
            return 2;
        case 'score':
            return 3;
        case 'revealed':
            return 4;
        case 'revealed,hand':
            return 5;
        case 'revealed,deck':
            return 6;
        case 'pile':
            return 7;
        case 'revealed,score':
            return 8;
        case 'achievements':
            return 9;
        case 'none':
            return 10;
        case 'display':
            return 11;
        case 'relics':
            return 12;
        case 'removed':
            return 13;
        case 'forecast':
            return 14;
        case 'hand,score':
            return 15;
        case 'junk':
            return 16;
        case 'safe':
            return 17;
        case 'junk,safe':
            return 18;
        case 'pile,score':
            return 19;
        default:
            // This should not happen
            throw new BgaVisibleSystemException(self::format(self::_("Unhandled case in {function}: '{code}'"), array('function' => "encodeLocation()", 'code' => $location)));
        }
    }
    
    function decodeLocation($location_code) {
        switch($location_code) {
        case 0:
            return 'deck';
        case 1:
            return 'hand';
        case 2:
            return 'board';
        case 3:
            return 'score';
        case 4:
            return 'revealed';
        case 5:
            return 'revealed,hand';
        case 6:
            return 'revealed,deck';
        case 7:
            return 'pile';
        case 8:
            return 'revealed,score';
        case 9:
            return 'achievements';
        case 10:
            return 'none';
        case 11:
            return 'display';
        case 12:
            return 'relics';
        case 13:
            return 'removed';
        case 14:
            return 'forecast';
        case 15:
            return 'hand,score';
        case 16:
            return 'junk';
        case 17:
            return 'safe';
        case 18:
            return 'junk,safe';
        case 19:
            return 'pile,score';
        default:
            // This should not happen
            throw new BgaVisibleSystemException(self::format(self::_("Unhandled case in {function}: '{code}'"), array('function' => "decodeLocation()", 'code' => $location_code)));
        }
    }
    
    function encodeSpecialTypeOfChoice($special_type_of_choice) {
        // NOTE: The following value is unused and safe to re-use: 2
        switch($special_type_of_choice) {
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
    
    function decodeSpecialTypeOfChoice($special_type_of_choice_code) {
        // NOTE: The following value is unused and safe to re-use: 2
        switch($special_type_of_choice_code) {
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
    
    function decodeGameType($game_type_code) {
        switch($game_type_code) {
        case 1:
            return 'individual';
        default:
            return 'team';
        }
    }
    
    /** Functions used for returning args to clients (Several states send these same things) **/
    function getArgForDogmaEffect() {
        $nested_card_state = self::getCurrentNestedCardState();

        // There won't be any nested card state if a player is returning cards after the Search icon was triggered.
        if ($nested_card_state == null) {
            return array_merge([
                'qualified_effect' => clienttranslate('search icon'),
                'card_name' => 'card_name',
                'i18n' => ['qualified_effect', 'card_name'],
            ], self::getDogmaCardNames());
        }

        $card_id = $nested_card_state['card_id'];
        $current_effect_type = $nested_card_state['current_effect_type'];
        $current_effect_number = $nested_card_state['current_effect_number'];
        // Echo effects are sometimes executed on cards other than the card being dogma'd
        if ($current_effect_type == 3) {
            $nesting_index = $nested_card_state['nesting_index'];
            $card_id = self::getUniqueValueFromDB(
                self::format("SELECT card_id FROM echo_execution WHERE nesting_index = {nesting_index} AND execution_index = {effect_number}",
                    array('nesting_index' => $nesting_index, 'effect_number' => $current_effect_number)));
        }
        $card = self::getCardInfo($card_id);
        
        $card_names = self::getDogmaCardNames();
        
        $args = array_merge(array(
            'qualified_effect' => self::qualifyEffect($current_effect_type, $current_effect_number, $card),
            'card_name' => 'card_name',
            'JSCardEffectQuery' => self::getJSCardEffectQuery($card, $current_effect_type, $current_effect_number)
        ), $card_names);
        
        $args['i18n'][] = 'qualified_effect';
        $args['i18n'][] = 'card_name';
        
        return $args;
    }
    
    function getArgForPlayerUnderDogmaEffect() {
        $player_id = self::getCurrentPlayerUnderDogmaEffect();
        return array_merge(
            self::getArgForDogmaEffect(),
            array(
                'player' => '${player}',
                'player_id' => $player_id,
                'player_name' => self::renderPlayerName($player_id),
                'player_name_as_you' => 'You'
            )
        );
    }
    
    function getDogmaCardNames() { // Returns the name of the current dogma card or all the names where there are nested dogma effects
        $nesting_index = $this->innovationGameState->get('current_nesting_index');
        $player_id = self::getCurrentPlayerUnderDogmaEffect();

        // There won't be any nested card state if a player is returning cards after the Search icon was triggered.
        if ($nesting_index < 0) {
            return [
                'card_0' => self::getCardName($this->innovationGameState->get('melded_card_id')),
                'ref_player_0' => $player_id,
                'i18n' => ['card_0'],
            ];
        }

        $card_names = array();
        $i18n = array();
        for ($i = 0; $i <= $nesting_index; $i++) {
            $nested_card_state = self::getNestedCardState($i);
            $current_effect_type = $nested_card_state['current_effect_type'];
            $current_effect_number = $nested_card_state['current_effect_number'];
            $card_id = $nested_card_state['card_id'];
            // Echo effects are sometimes executed on cards other than the card being dogma'd
            if ($current_effect_type == 3) {
                $nesting_index = $nested_card_state['nesting_index'];
                $card_id = self::getUniqueValueFromDB(
                    self::format("SELECT card_id FROM echo_execution WHERE nesting_index = {nesting_index} AND execution_index = {effect_number}",
                        array('nesting_index' => $nesting_index, 'effect_number' => $current_effect_number)));
            }
            $card_names['card_'.$i] = self::getCardName($card_id);
            $card_names['ref_player_'.$i] = $player_id;
            $i18n[] = 'card_'.$i;
        }

        $card_names['i18n'] = $i18n;
        return $card_names;
    }
    
    function getJSCardId($card) {
        return "#item_" . $card['id'] . "__age_" . $card['age'] . "__type_" . $card['type'] . "__is_relic_" . $card['is_relic'] . "__M__card";
    }
    
    function getJSCardEffectQuery($card, $effect_type, $effect_number) {
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

    function setLauncherId($launcher_id) {
        self::updateCurrentNestedCardState('launcher_id', $launcher_id);
    }

    function getLauncherId() {
        if ($this->innovationGameState->get('current_nesting_index') < 0) {
            return $this->innovationGameState->get('active_player');
        }
        return self::getCurrentNestedCardState()['launcher_id'];
    }

    function incrementStep($delta) {
        self::setStep(self::getStep() + $delta);
    }

    function setStep($step) {
        self::updateCurrentNestedCardState('step', $step);
    }

    function getStep() {
        return self::getCurrentNestedCardState()['step'];
    }

    function incrementStepMax($delta) {
        self::setStepMax(self::getStepMax() + $delta);
    }

    function setStepMax($step_max) {
        self::updateCurrentNestedCardState('step_max', $step_max);
    }

    function getStepMax() {
        return self::getCurrentNestedCardState()['step_max'];
    }

    function setAuxiliaryValue($auxiliary_value) {
        self::updateCurrentNestedCardState('auxiliary_value', $auxiliary_value);
    }

    function setAuxiliaryValueFromArray($array) {
        self::setAuxiliaryValue(Arrays::getArrayAsValue($array));
    }

    function getAuxiliaryValue() {
        return self::getCurrentNestedCardState()['auxiliary_value'];
    }

    function getAuxiliaryValueAsArray() {
        return Arrays::getValueAsArray(self::getAuxiliaryValue());
    }

    function setAuxiliaryValue2($auxiliary_value_2) {
        self::updateCurrentNestedCardState('auxiliary_value_2', $auxiliary_value_2);
    }

    function setAuxiliaryValue2FromArray($array) {
        self::setAuxiliaryValue2(Arrays::getArrayAsValue($array));
    }

    function getAuxiliaryValue2() {
        return self::getCurrentNestedCardState()['auxiliary_value_2'];
    }

    function getAuxiliaryValue2AsArray() {
        return Arrays::getValueAsArray(self::getAuxiliaryValue2());
    }

    function setAuxiliaryArray($array) {
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

    function getAuxiliaryArray() {
        $nesting_index = $this->innovationGameState->get('current_nesting_index');

        // Get array size
        $array_size = self::getUniqueValueFromDB(self::format("
            SELECT
                value
            FROM
                auxiliary_value_table
            WHERE
                nesting_index = {nesting_index} AND
                array_index = 0
        ",
            array('nesting_index' => $nesting_index)
       ));

       // Return empty array if no array was stored
       if ($array_size == null) {
           return [];
       }
            
        // Get array values
        $array = array();
        for ($i = 1; $i <= $array_size; $i++) {
            $array[] = self::getUniqueValueFromDB(self::format("
                SELECT
                    value
                FROM
                    auxiliary_value_table
                WHERE
                    nesting_index = {nesting_index} AND
                    array_index = {array_index}
            ",
                array('nesting_index' => $nesting_index, 'array_index' => $i)
            ));
        }

        return $array;
    }

    function setActionScopedAuxiliaryArray($card_id, $player_id, $array) {
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

    function getActionScopedAuxiliaryArray($card_id, $player_id) {
        // Get array size
        $array_size = self::getUniqueValueFromDB(self::format("
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
       ));

       // Return empty array if no array was stored
       if ($array_size == null) {
           return [];
       }
            
        // Get array values
        $array = array();
        for ($i = 1; $i <= $array_size; $i++) {
            $array[] = self::getUniqueValueFromDB(self::format("
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
            ));
        }

        return $array;
    }

    function setIndexedAuxiliaryValue(int $index_id, int $value) {
        $nesting_index = $this->innovationGameState->get('current_nesting_index');

        // Check to see if a value already exists
        $result = self::getUniqueValueFromDB(self::format("
            SELECT
                value
            FROM
                indexed_auxiliary_value
            WHERE
                nesting_index = {nesting_index} AND
                index_id = {index_id}
        ",
            array('nesting_index' => $nesting_index, 'index_id' => $index_id)
        ));

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

    function getIndexedAuxiliaryValue($index_id): int {
        $nesting_index = $this->innovationGameState->get('current_nesting_index');
        $result = self::getUniqueValueFromDB(self::format("
            SELECT
                value
            FROM
                indexed_auxiliary_value
            WHERE
                nesting_index = {nesting_index} AND
                index_id = {index_id}
        ",
            array('nesting_index' => $nesting_index, 'index_id' => $index_id)
       ));
       if ($result == null) {
        $result = -1;
       }
       return intval($result);
    }

    // TODO(LATER): Use this more widely.
    function getPlayerTableColumn($player_id, $column) {
        return self::getUniqueValueFromDB(self::format("SELECT {column} FROM player WHERE player_id = {player_id}", array('player_id' => $player_id, 'column' => $column)));
    }

    // TODO(LATER): Use this more widely.
    function setPlayerTableColumn($player_id, $column, $value) {
        self::DbQuery(self::format("UPDATE player SET {column} = {val} WHERE player_id = {player_id}", array('player_id' => $player_id, 'column' => $column, 'val' => $value)));
    }

    /** Returns true if the ongoing effect is currently executing a second time due to an Endorse action */
    function isExecutingAgainDueToEndorsedAction() {
        return $this->innovationGameState->get('current_nesting_index') == 0 && $this->innovationGameState->get('endorse_action_state') == 3;
    }
    
    /** Nested dogma excution management system: FIFO stack **/
    function selfExecute($card, $replace_may_with_must = false): bool {
        $player_id = self::getCurrentPlayerUnderDogmaEffect();

        // TODO(4E): There may be a bug here if a card calls this which does not actually use the word "self-execute".
        if ($this->innovationGameState->get('current_nesting_index') >= 1 && $this->innovationGameState->usingFourthEditionRules()) {
            self::incStat(1, 'execution_combo_count', $player_id);
            self::notifyPlayer($player_id, 'log', clienttranslate('${You} receive a Chain Achievement.'), ['You' => 'You', ]);
            self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} receives a Chain Achievement.'), ['player_name' => self::renderPlayerName($player_id)]);
            self::executeDraw($player_id, 11, 'achievements');
        }

        $card_args = self::getNotificationArgsForCardList([$card]);
        if (self::getNonDemandEffect($card['id'], 1) === null) {
            self::notifyAll('logWithCardTooltips', clienttranslate('There are no non-demand effects on ${card} to execute.'), ['card' => $card_args, 'card_ids' => [$card['id']]]);
            return false;
        }
        if ($replace_may_with_must) {
            self::notifyPlayer($player_id, 'logWithCardTooltips', clienttranslate('${You} self-execute the non-demand effect(s) of ${card}, replacing \'may\' with \'must\'.'),
                ['You' => 'You', 'card' => $card_args, 'card_ids' => [$card['id']]]);
            self::notifyAllPlayersBut($player_id, 'logWithCardTooltips', clienttranslate('${player_name} self-executes the non-demand effect(s) of ${card}, replacing \'may\' with \'must\'.'),
                ['player_name' => self::renderPlayerName($player_id), 'card' => $card_args, 'card_ids' => [$card['id']]]);
        } else {
            self::notifyPlayer($player_id, 'logWithCardTooltips', clienttranslate('${You} self-execute the non-demand effect(s) of ${card}.'),
                ['You' => 'You', 'card' => $card_args, 'card_ids' => [$card['id']]]);
            self::notifyAllPlayersBut($player_id, 'logWithCardTooltips', clienttranslate('${player_name} self-executes the non-demand effect(s) of ${card}.'),
                ['player_name' => self::renderPlayerName($player_id), 'card' => $card_args, 'card_ids' => [$card['id']]]);
        }
        self::pushCardIntoNestedDogmaStack($card, /*execute_demand_effects=*/ false, $replace_may_with_must);
        return true;
    }

    function fullyExecute($card) {
        $player_id = self::getCurrentPlayerUnderDogmaEffect();
        $current_nested_state = self::getCurrentNestedCardState();

        // TODO(4E): There may be a bug here if a card calls this which does not actually use the phrase "fully execute".
        if ($current_nested_state['nesting_index'] >= 1 && $this->innovationGameState->usingFourthEditionRules()) {
            self::incStat(1, 'execution_combo_count', $player_id);
            self::notifyPlayer($player_id, 'log', clienttranslate('${You} receive a Chain Achievement.'), ['You' => 'You', ]);
            self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} receives a Chain Achievement.'), ['player_name' => self::renderPlayerName($player_id)]);
            self::executeDraw($player_id, 11, 'achievements');
        }

        $current_card = self::getCardInfo($current_nested_state['card_id']);
        $card_1_args = self::getNotificationArgsForCardList([$current_card]);
        $card_2_args = self::getNotificationArgsForCardList([$card]);
        $initially_executed_card = self::getCardInfo($current_nested_state['executing_as_if_on_card_id']);
        $icon = Icons::render($initially_executed_card['dogma_icon']);
        self::notifyPlayer($player_id, 'logWithCardTooltips', clienttranslate('${You} fully execute the effects of ${card_2} as if it were on ${card_1}, using ${icon} as the featured icon.'),
            ['You' => 'You', 'card_1' => $card_1_args, 'card_2' => $card_2_args, 'card_ids' => [$current_card['id'], $card['id']], 'icon' => $icon]);
        self::notifyAllPlayersBut($player_id, 'logWithCardTooltips', clienttranslate('${player_name} fully executes the effects of ${card_2} as if it were on ${card_1}, using ${icon} as the featured icon.'),
            ['player_name' => self::renderPlayerName($player_id), 'card_1' => $card_1_args, 'card_2' => $card_2_args, 'card_ids' => [$current_card['id'], $card['id']], 'icon' => $icon]);
        self::pushCardIntoNestedDogmaStack($card, /*execute_demand_effects=*/ true);
    }

    function pushCardIntoNestedDogmaStack($card, $execute_demand_effects, $replace_may_with_must = false) {
        $current_player_id = self::getCurrentPlayerUnderDogmaEffect();
        $nested_card_state = self::getCurrentNestedCardState();
        // Every card that says "execute the effects" also says "as if they were on this card"
        if ($execute_demand_effects) {
            $as_if_on = $nested_card_state['executing_as_if_on_card_id'];
        } else {
            $as_if_on = $card['id'];
        }
        if ($nested_card_state['replace_may_with_must']) {
            $replace_may_with_must = true;
        }
        $next_nesting_index = $this->innovationGameState->get('current_nesting_index') + 1;
        $has_i_demand = self::getDemandEffect($card['id']) !== null;
        $has_i_compel = self::getCompelEffect($card['id']) !== null;
        $has_echo_effect = self::getEchoEffect($card['id']) !== null;
        $effect_type = $execute_demand_effects ? ($has_echo_effect ? 3 : ($has_i_demand ? 0 : ($has_i_compel ? 2 : 1))) : 1;
        if ($effect_type == 3) {
            self::DbQuery(self::format("
                    INSERT INTO echo_execution
                        (nesting_index, execution_index, card_id)
                    VALUES
                        ({nesting_index}, 1, {card_id})
                ", array('nesting_index' => $next_nesting_index, 'card_id' => $card['id'])));
        }
        self::DbQuery(self::format("
            INSERT INTO nested_card_execution
                (nesting_index, card_id, executing_as_if_on_card_id, replace_may_with_must, launcher_id, current_effect_type, current_effect_number, step, step_max)
            VALUES
                ({nesting_index}, {card_id}, {as_if_on}, {replace_may_with_must}, {launcher_id}, {effect_type}, 1, -1, -1)
        ", array('nesting_index' => $next_nesting_index, 'card_id' => $card['id'], 'as_if_on' => $as_if_on, 'replace_may_with_must' => $replace_may_with_must ? 'TRUE' : 'FALSE', 'launcher_id' => $current_player_id, 'effect_type' => $effect_type)));
    }
    
    function popCardFromNestedDogmaStack() {
        self::DbQuery(self::format("DELETE FROM indexed_auxiliary_value WHERE nesting_index = {nesting_index}", array('nesting_index' => $this->innovationGameState->get('current_nesting_index'))));
        self::DbQuery(self::format("DELETE FROM nested_card_execution WHERE nesting_index = {nesting_index}", array('nesting_index' => $this->innovationGameState->get('current_nesting_index'))));
        $this->innovationGameState->increment('current_nesting_index', -1);
        self::updateCurrentNestedCardState('post_execution_index', 'post_execution_index + 1');
    }

    function echoEffectWasExecuted() {
        $nested_card_state = self::getCurrentNestedCardState();
        // Every card that says "execute the effects" also says "as if they were on this card". However, if the card says
        // "execute all of the non-demand dogma effects" then the echo effects will be skipped.
        // TODO(4E): Revise this logic.
        return $nested_card_state['nesting_index']  == 0 || $nested_card_state['executing_as_if_on_card_id'] != $nested_card_state['card_id'];
    }

    function getNestedCardState($nesting_index) {
        return self::getObjectFromDB(
            self::format("
                SELECT
                    nesting_index,
                    card_id,
                    executing_as_if_on_card_id,
                    replace_may_with_must,
                    card_location,
                    launcher_id,
                    current_player_id,
                    current_effect_type,
                    current_effect_number,
                    step,
                    step_max,
                    post_execution_index,
                    auxiliary_value,
                    auxiliary_value_2
                FROM
                    nested_card_execution
                WHERE
                    nesting_index = {nesting_index}",
                array('nesting_index' => $nesting_index)
        ));
    }

    function getCurrentNestedCardState() {
        return self::getNestedCardState($this->innovationGameState->get('current_nesting_index'));
    }

    function updateCurrentNestedCardState($column, $value) {
        self::DbQuery(
            self::format("
                UPDATE
                    nested_card_execution
                SET
                    {column} = {value}
                WHERE
                    nesting_index = {nesting_index}",
                array('column' => $column, 'value' => $value, 'nesting_index' => $this->innovationGameState->get('current_nesting_index')))
        );
    }

    function getCurrentPlayerUnderDogmaEffect() {
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
    
//////////// Player actions
//////////// 

    /*
        Each time a player is doing some game action, one of the methods below is called.
        (note: each method below must match an input method in innovation.action.php)
    */
    
    function initialMeld($card_id) {
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
        self::notifyPlayer($player_id, 'log', clienttranslate('${You} choose a card.'), array(
            'You' => 'You'            
        ));
            
        self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} chooses a card.'), array(
            'player_name' => self::getPlayerNameFromId($player_id)
        ));
        
        // If that was the last player to choose his card, go on for the next state (whoBegins?), else, wait for remaining players
        $this->gamestate->setPlayerNonMultiactive($player_id, '');
    }

    function updateInitialMeld($card_id) {
        $this->gamestate->checkPossibleAction('updateInitialMeld');
        $this->gamestate->setPlayersMultiactive(array ($this->getCurrentPlayerId() ), 'error', false);

        // Check if the player really has this card
        $card = self::getCardInfo($card_id);
        $player_id = self::getCurrentPlayerId();
        if ($card['owner'] != $player_id || $card['location'] != "hand") {
            self::throwInvalidChoiceException();
        }
        
        // Update card selection
        $cards = self::getCardsInHand($player_id);
        foreach($cards as $card_in_hand) {
            if ($card_in_hand['id'] == $card_id) {
                self::markAsSelected($card_in_hand['id']);
            } else {
                self::unmarkAsSelected($card_in_hand['id']);
            }
        }
        
        // Notify
        self::notifyPlayer($player_id, 'log', clienttranslate('${You} choose a card.'), array(
            'You' => 'You'            
        ));
            
        self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} chooses a card.'), array(
            'player_name' => self::getPlayerNameFromId($player_id)
        ));
        
        // If that was the last player to choose his card, go on for the next state (whoBegins?), else, wait for remaining players
        $this->gamestate->setPlayerNonMultiactive($player_id, '');
    }

    function passSeizeRelic() {
        // Check that this is the player's turn and that it is a "possible action" at this game state
        self::checkAction('passSeizeRelic');

        $player_id = self::getCurrentPlayerId();
        self::notifyPlayer($player_id, 'log', clienttranslate('${You} choose not to seize the relic.'), array('You' => 'You'));    
        self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} chooses not to seize the relic.'), array('player_name' => self::getPlayerNameFromId($player_id)));
        $this->innovationGameState->set('relic_id', -1);

        self::trace('relicPlayerTurn->promoteCard (passSeizeRelic)');
        $this->gamestate->nextState('promoteCard');
    }

    function seizeRelicToHand() {
        // Check that this is the player's turn and that it is a "possible action" at this game state
        self::checkAction('seizeRelicToHand');

        $player_id = self::getCurrentPlayerId();
        $card = self::getCardInfo($this->innovationGameState->get('relic_id'));

        if ($card['owner'] != 0 && $card['owner'] != $player_id) {
            self::incStat(1, 'relics_stolen_number', $card['owner']);
        }
        self::incStat(1, 'relics_seized_number', $player_id);

        self::transferCardFromTo($card, $player_id, 'hand');
        $this->innovationGameState->set('relic_id', -1);
       
        self::trace('relicPlayerTurn->promoteCard (seizeRelicToHand)');
        $this->gamestate->nextState('promoteCard');
    }

    function seizeRelicToAchievements() {
        // Check that this is the player's turn and that it is a "possible action" at this game state
        self::checkAction('seizeRelicToAchievements');

        $player_id = self::getCurrentPlayerId();
        $card = self::getCardInfo($this->innovationGameState->get('relic_id'));

        if ($card['owner'] != 0 && $card['owner'] != $player_id) {
            self::incStat(1, 'relics_stolen_number', $card['owner']);
        }
        self::incStat(1, 'relics_seized_number', $player_id);

        try {
            self::transferCardFromTo($card, $player_id, "achievements");
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

    function dogmaArtifactOnDisplay() {
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

    function returnArtifactOnDisplay() {
        // Check that this is the player's turn and that it is a "possible action" at this game state
        self::checkAction('returnArtifactOnDisplay');

        $player_id = self::getCurrentPlayerId();
        $card = self::getArtifactOnDisplay($player_id);
        self::decreaseResourcesForArtifactOnDisplay($player_id, $card);
        self::returnCard($card);

        // Check for special achievements (only necessary in 4th edition)
        if ($this->innovationGameState->usingFourthEditionRules()) {
            try {
            self::checkForSpecialAchievements(/*is_end_of_action_check=*/ true);
            } catch(EndOfGame $e) {
                // End of the game: the exception has reached the highest level of code
                self::trace('EOG bubbled from self::returnArtifactOnDisplay');
                self::trace('artifactPlayerTurn->justBeforeGameEnd');
                $this->gamestate->nextState('justBeforeGameEnd');
                return;
            }
        }

        self::giveExtraTime($player_id);
        self::trace('artifactPlayerTurn->playerTurn (returnArtifactOnDisplay)');
        $this->gamestate->nextState('playerTurn');
        $this->innovationGameState->set('current_action_number', 1);
        
        self::incStat(1, 'free_action_return_number', $player_id);
    }

    function passArtifactOnDisplay() {
        // Check that this is the player's turn and that it is a "possible action" at this game state
        self::checkAction('passArtifactOnDisplay');

        $player_id = self::getCurrentPlayerId();
        self::notifyPlayer($player_id, 'log', clienttranslate('${You} choose not to return or dogma your Artifact on display.'), array('You' => 'You'));    
        self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} chooses not to return or dogma his Artifact on display.'), array('player_name' => self::getPlayerNameFromId($player_id)));
        $card = self::getArtifactOnDisplay($player_id);
        self::decreaseResourcesForArtifactOnDisplay($player_id, $card);

        // Check for special achievements (only necessary in 4th edition)
        if ($this->innovationGameState->usingFourthEditionRules()) {
            try {
            self::checkForSpecialAchievements(/*is_end_of_action_check=*/ true);
            } catch(EndOfGame $e) {
                // End of the game: the exception has reached the highest level of code
                self::trace('EOG bubbled from self::passArtifactOnDisplay');
                self::trace('artifactPlayerTurn->justBeforeGameEnd');
                $this->gamestate->nextState('justBeforeGameEnd');
                return;
            }
        }

        self::giveExtraTime($player_id);
        self::trace('artifactPlayerTurn->playerTurn (passArtifactOnDisplay)');
        $this->gamestate->nextState('playerTurn');
        $this->innovationGameState->set('current_action_number', 1);
        
        self::incStat(1, 'free_action_pass_number', $player_id);
    }

    function passPromoteCard() {
        // Check that this is the player's turn and that it is a "possible action" at this game state
        self::checkAction('passPromoteCard');

        // Promoting became mandatory in 4th edition
        if (!$this->innovationGameState->usingFourthEditionRules()) {
            self::throwInvalidChoiceException();
        }

        $player_id = self::getCurrentPlayerId();
        self::notifyPlayer($player_id, 'log', clienttranslate('${You} choose not to promote a card from your forecast.'), array('You' => 'You'));    
        self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} chooses not to promote a card from his forecast.'), array('player_name' => self::getPlayerNameFromId($player_id)));

        self::trace('promoteCardPlayerTurn->interPlayerTurn (passPromoteCard)');
        $this->gamestate->nextState('interPlayerTurn');
    }

    function promoteCard($card_id) {
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

    function promoteCardBack($owner, $location, $age, $type, $is_relic, $position) {
        // Check that this is the player's turn and that it is a "possible action" at this game state
        self::checkAction('promoteCard');

        $card = self::getCardInfoFromPosition($owner, $location, $age, $type, $is_relic, $position);
        if ($card === null) {
            self::throwInvalidChoiceException();
        }
        self::promoteCard($card['id']);
    }


    function passDogmaPromotedCard() {
        // Check that this is the player's turn and that it is a "possible action" at this game state
        self::checkAction('passDogmaPromotedCard');

        $player_id = self::getCurrentPlayerId();
        self::notifyPlayer($player_id, 'log', clienttranslate('${You} choose not to dogma your promoted card.'), array('You' => 'You'));    
        self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} chooses not to dogma his promoted card.'), array('player_name' => self::getPlayerNameFromId($player_id)));

        self::trace('promoteDogmaPlayerTurn->interPlayerTurn (passDogmaPromotedCard)');
        $this->gamestate->nextState('interPlayerTurn');
    }

    function dogmaPromotedCard() {
        // Check that this is the player's turn and that it is a "possible action" at this game state
        self::checkAction('dogmaPromotedCard');

        $player_id = self::getCurrentPlayerId();
        self::notifyPlayer($player_id, 'log', clienttranslate('${You} choose to dogma your promoted card.'), array('You' => 'You'));    
        self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} chooses to dogma his promoted card.'), array('player_name' => self::getPlayerNameFromId($player_id)));

        self::setUpDogma($player_id, self::getCardInfo($this->innovationGameState->get('melded_card_id')));

        self::trace('promoteDogmaPlayerTurn->dogmaEffect (dogmaPromotedCard)');
        $this->gamestate->nextState('dogmaEffect');
    }
    
    function achieve($owner, $location, $age) {
        // Check that this is the player's turn and that it is a "possible action" at this game state
        self::checkAction('achieve');
        $player_id = self::getActivePlayerId();
        
        // Check if there are any achievements/secrets available to claim
        $card = self::getObjectFromDB(
            self::format("SELECT * FROM card WHERE location = '{location}' AND owner = {owner} AND age = {age} ORDER BY type LIMIT 1",
            ['owner' => $owner, 'location' => $location, 'age' => $age, 'player_id' => $player_id])
        );
        if ($card === null) {
            self::throwInvalidChoiceException();
        }
        
        self::achieveSpecificCard($card);
    }

    function achieveCardBack($owner, $location, $age, $type, $is_relic, $position) {
        // Check that this is the player's turn and that it is a "possible action" at this game state
        self::checkAction('achieve');

        $card = self::getCardInfoFromPosition($owner, $location, $age, $type, $is_relic, $position);
        if ($card === null) {
            self::throwInvalidChoiceException();
        }
        self::achieveSpecificCard($card);
    }

    function achieveSpecificCard($card) {
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
        }
        catch (EndOfGame $e) {
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
    
    function draw() {
        // Check that this is the player's turn and that it is a "possible action" at this game state
        self::checkAction('draw');
        $player_id = self::getActivePlayerId();
        
        // Stats
        self::updateActionAndTurnStats($player_id);
        self::incStat(1, 'draw_actions_number', $player_id);
        
        // Execute the draw
        try {
            self::executeDraw($player_id); // Draw a card with age consistent with player board
        }
        catch (EndOfGame $e) {
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
    
    function meld($card_id) {
        // Check that this is the player's turn and that it is a "possible action" at this game state
        self::checkAction('meld');
        $player_id = self::getActivePlayerId();

        // Check if the player really has this card in their hand or on display
        $card = self::getCardInfo($card_id);
        if ($card['owner'] != $player_id || ($card['location'] != "hand" && $card['location'] != "display")) {
            self::throwInvalidChoiceException();
        }
        
        // Stats
        self::updateActionAndTurnStats($player_id);
        self::incStat(1, 'meld_actions_number', $player_id);
        
        // Execute the meld
        try {
            self::meldCard($card, $card['owner']);
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
                        'player_id' => $player_id,
                        'n' => 1,
                        'owner_from' => 0,
                        'location_from' => 'achievements',
                        'owner_to' => 0,
                        'location_to' => 'junk',
                        'age' => $card['age'],
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
                                'player_id' => $player_id,
                                'n' => count($card_ids_to_return),
                                'owner_from' => $player_id,
                                'location_from' => 'hand',
                                'owner_to' => 0,
                                'location_to' => 'deck',
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

    function stDigArtifact() {
        $player_id = self::getActivePlayerId();
        $melded_card = self::getCardInfo($this->innovationGameState->get('melded_card_id'));

        if ($this->innovationGameState->citiesExpansionEnabled()) {
            // "When you take a Meld action to meld a card that adds a new color to your board, draw a City" (unless you already have a Cities card in hand)
            if ($melded_card['position'] == 0 && self::countCardsInLocation($player_id, 'hand', CardTypes::CITIES) == 0) {
                self::executeDraw($player_id, self::getAgeToDrawIn($player_id), 'hand', /*bottom_to=*/ false, CardTypes::CITIES);
            }
        }

        if (self::tryToDigArtifactAndSeizeRelic($melded_card)) {
            self::trace('digArtifact->relicPlayerTurn');
            $this->gamestate->nextState('relicPlayerTurn');
            return;
        }
        self::trace('digArtifact->promoteCard');
        $this->gamestate->nextState('promoteCard');
    }

    function stPromoteCard() {
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

    function claimSpecialAchievement($player_id, $achievement_id) {
        $achievement = self::getCardInfo($achievement_id);
        if ($achievement['owner'] == 0 && $achievement['location'] == 'achievements') {
            self::transferCardFromTo($achievement, $player_id, 'achievements');
        } else {
            $card_args = self::getNotificationArgsForCardList([$achievement]);
            self::notifyAll('logWithCardTooltips', clienttranslate('${card} has already been claimed.'), ['card' => $card_args, 'card_ids' => [$achievement_id]]);
        }
    }
    
    /* Returns true if a relic is being seized */
    function tryToDigArtifactAndSeizeRelic($melded_card) {
        // The Artifacts expansion is not enabled.
        if (!$this->innovationGameState->artifactsExpansionEnabled()) {
            return false;
        }

        $player_id = $melded_card['owner'];
        $pile = self::getCardsInLocationKeyedByColor($player_id, 'board')[$melded_card['color']];
        if (count($pile) >= 2) {
            $previous_top_card = $pile[count($pile) - 2];
        } else {
            $previous_top_card = null;
        }

        // An Artifact is already on display.
        if (self::getArtifactOnDisplay($player_id) !== null) {
            return false;
        }
                
        // A dig happens when a card is covered with a card of lower or equal value, or both cards have their hexagonal icons in the same location.
        $new_card_has_lower_or_equal_value = $previous_top_card !== null && $previous_top_card['faceup_age'] >= $melded_card['faceup_age'];
        $overlapping_icons = $previous_top_card !== null && self::haveOverlappingHexagonIcons($previous_top_card, $melded_card);
        if ($new_card_has_lower_or_equal_value || $overlapping_icons) {
            
            // You first draw up through any empty ages (base cards) before looking at the relevant artifact pile.
            $age_draw = self::getAgeToDrawIn($player_id, $previous_top_card['faceup_age']);
            $top_artifact_card = self::getDeckTopCard($age_draw, CardTypes::ARTIFACTS);
            
            if ($top_artifact_card == null) {
                self::notifyPlayer($player_id, "log", clienttranslate('There are no Artifact cards in the ${age} deck, so the dig event is ignored.'), array('age' => self::getAgeSquare($age_draw)));
            } else {
                self::digCard($top_artifact_card, $player_id);
                self::incStat(1, 'dig_events_number', $player_id);
                
                // "After you dig an artifact, you may seize a Relic of the same value as the Artifact card drawn."
                if ($this->innovationGameState->artifactsExpansionEnabledWithRelics()) {
                    $relic = self::getRelicForAge($top_artifact_card['faceup_age']);
                    // "You may only do this if the Relic is next to its supply pile, or in any achievements pile (even your own!)."
                    if ($relic != null && (self::canSeizeRelicToHand($relic, $player_id) || self::canSeizeRelicToAchievements($relic, $player_id))) {
                        $this->innovationGameState->set('relic_id', $relic['id']);
                        return true;
                    }
                }
            }
        }
        return false;
    }

    function haveOverlappingHexagonIcons($card_1, $card_2) {
        return
            ($card_1['spot_1'] === '0' && $card_2['spot_1'] === '0') || 
            ($card_1['spot_2'] === '0' && $card_2['spot_2'] === '0') || 
            ($card_1['spot_3'] === '0' && $card_2['spot_3'] === '0') ||
            ($card_1['spot_4'] === '0' && $card_2['spot_4'] === '0') || 
            ($card_1['spot_5'] === '0' && $card_2['spot_5'] === '0') ||
            ($card_1['spot_6'] === '0' && $card_2['spot_6'] === '0');
    }

    /* Returns null if there is no relic of the specified age */
    function getRelicForAge($age) {
        return self::getObjectFromDB(self::format("SELECT * FROM card WHERE age = {age} AND is_relic", array('age' => $age)));
    }

    function dogma($card_id, $card_id_to_return) {
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

    function endorse($card_to_endorse_id, $card_for_payment_id) {
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

    function updateActionAndTurnStats($player_id) {
        if ($this->innovationGameState->get('current_action_number') == 1) {
            self::incStat(1, 'turns_number');
            self::incStat(1, 'turns_number', $player_id);
        }
        self::incStat(1, 'actions_number');
        self::incStat(1, 'actions_number', $player_id);
    }

    function increaseResourcesForArtifactOnDisplay($player_id, $card) {
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

    function decreaseResourcesForArtifactOnDisplay($player_id, $card) {
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

    function updateResourcesForArtifactOnDisplay($player_id, $resource_icon, $resource_count_delta) {
        self::notifyAll('updateResourcesForArtifactOnDisplay', '', array(
            'player_id' => $player_id,
            'resource_icon' => $resource_icon,
            'resource_count_delta' => $resource_count_delta,
        ));
    }
    
    function setUpDogma($player_id, $card, $extra_icons_from_artifact_on_display = 0, $endorse_payment_card = null, $card_to_return = null) {

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
            self::notifyPlayerRessourceCount($player['player_id'], $dogma_icon, $player_icon_count);
            self::DBQuery(self::format("
                UPDATE 
                    player
                SET
                    featured_icon_count = {featured_icon_count}
                WHERE
                    player_id = {player_id}"
            ,
                array('featured_icon_count' => $player_icon_count, 'player_id' => $player['player_id'])
            ));
        }

        $visible_echo_effects = self::getCardsWithVisibleEchoEffects($card);
        if (!empty($visible_echo_effects)) {
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
                    post_execution_index = 0
                WHERE
                    nesting_index = 0",
                array('card_id' => $card['id'], 'card_location' => $card['location'], 'launcher_id' => $player_id, 'effect_type' => $current_effect_type, 'effect_number' => $current_effect_number))
        );
        $this->innovationGameState->set('sharing_bonus', 0);
        self::DbQuery("UPDATE player SET distance_rule_share_state = 0");
        self::DbQuery("UPDATE player SET distance_rule_demand_state = 0");
    }

    function choose($card_id) {
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
    
    function chooseRecto($owner, $location, $age, $type, $is_relic, $position) {
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
    
    function chooseSpecialOption($choice) {
        // Check that this is the player's turn and that it is a "possible action" at this game state
        self::checkAction('choose');
        $player_id = self::getActivePlayerId();
        
        $special_type_of_choice = $this->innovationGameState->get('special_type_of_choice');
        
        if ($special_type_of_choice == 0) { // This is not a special choice
            self::throwInvalidChoiceException();
        }
        
        switch(self::decodeSpecialTypeOfChoice($special_type_of_choice)) {
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
                $colors = Arrays::getValueAsArray($choice);
                if (count($colors) <> 2 || $colors[0] == $colors[1] || !in_array($colors[0], $this->innovationGameState->getAsArray('color_array')) || !in_array($colors[1], $this->innovationGameState->getAsArray('color_array'))) {
                    self::throwInvalidChoiceException();
                }
                break;
            case 'choose_three_colors':
                if (!ctype_digit($choice) || $choice < 0) {
                    self::throwInvalidChoiceException();
                }
                $colors = Arrays::getValueAsArray($choice);
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
                if ($player_index == null || !in_array($player_index, $this->innovationGameState->getAsArray('player_array'))) {
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
                
                foreach($permutations_done as $permutation) {
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

                self::notifyPlayer($player_id, 'rearrangedPile', clienttranslate('${You} rearrange your ${color} stack.'), array(
                    'i18n' => array('color'),
                    'player_id' => $player_id,
                    'new_max_age_on_board' => $new_max_age_on_board,
                    'rearrangement' => $choice,
                    'You' => 'You',
                    'color' => Colors::render($color)
                ));
                self::notifyAllPlayersBut($player_id, 'rearrangedPile', clienttranslate('${player_name} rearranges his ${color} stack.'), array(
                    'i18n' => array('color'),
                    'player_id' => $player_id,
                    'new_max_age_on_board' => $new_max_age_on_board,
                    'rearrangement' => $choice,
                    'player_name' => self::renderPlayerName($player_id),
                    'color' => Colors::render($color))
                );

                $end_of_game = false;

                self::removeOldFlagsAndFountains();
                try {
                    self::addNewFlagsAndFountains();
                } catch(EndOfGame $e) {
                    $end_of_game = true;
                }

                try {
                    self::checkForSpecialAchievements();
                } catch(EndOfGame $e) {
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
    
    function updateDisplayMode($display_mode) {
        $player_id = self::getCurrentPlayerId();
        self::setPlayerWishForSplay($player_id, $display_mode);
        self::notifyPlayer($player_id, 'log', '', array());
    }
    
    function updateViewFull($view_full) {
        $player_id = self::getCurrentPlayerId();
        self::setPlayerWishForViewFull($player_id, $view_full);
        self::notifyPlayer($player_id, 'log', '', array());
    }

    function throwInvalidChoiceException() {
        if (self::getGameStateValue('debug_mode') == 1) {
            debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        }
        throw new BgaUserException(self::_("Your choice was invalid (try refreshing the page)"));
    }
    
//////////////////////////////////////////////////////////////////////////////
//////////// Game state arguments
////////////

    /*
        Here, you can create methods defined as "game state arguments" (see "args" property in states.inc.php).
        These methods function is to return some additional information that is specific to the current
        game state.
    */

    /*
    
    Example for game state "MyGameState":
    
    function argMyGameState()
    {
        // Get some values from the current game situation in database...
    
        // return values:
        return array(
            'variable1' => $value1,
            'variable2' => $value2,
            ...
       );
    } 
    */
    function argTurn0() {
        if (self::decodeGameType($this->innovationGameState->get('game_type')) == 'team') {
            // Indicate what the teams are
            $messages = array();
            foreach(self::loadPlayersBasicInfos() as $player_id => $player) {
                $teammate_id = self::getPlayerTeammate($player_id);
                $teammate_name = self::getPlayerNameFromId($teammate_id);
                $message = self::format(clienttranslate('{You} are in team with {player_name}.'), array('You' => 'You', 'player_name' => self::getColoredText($teammate_name, $teammate_id)));
                $messages[$player_id] = $message;
            }
            return array('team_game' => true, 'messages' => $messages);
        }
        return array('team_game' => false);
    }

    function argRelicPlayerTurn() {
        $player_id = $this->innovationGameState->get('active_player');
        $relic = self::getCardInfo($this->innovationGameState->get('relic_id'));
        return array(
            'can_seize_to_hand' => self::canSeizeRelicToHand($relic, $player_id),
            'can_seize_to_achievements' => self::canSeizeRelicToAchievements($relic, $player_id),
            'relic_id' => $relic['id'],
            'relic_name' => self::getNotificationArgsForCardList(array($relic)),
        );
    }

    function canSeizeRelicToHand($relic, $player_id) {
        return self::relicSetIsInUse($relic) && ($relic['location'] == 'achievements' || $relic['location'] == 'relics');
    }

    function canSeizeRelicToAchievements($relic, $player_id) {
        return $relic['location'] == 'relics' || ($relic['location'] == 'achievements' && $relic['owner'] != $player_id);
    }

    /* Returns whether the relic's set is being used for this game. */
    function relicSetIsInUse($relic) {
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

    function argPlayerArtifactTurn() {
        $player_id = $this->innovationGameState->get('active_player');
        $card = self::getArtifactOnDisplay($player_id);
        // TODO(4E): Make sure this works with Battleship Yamato
        return array(
            '_private' => array(
                'active' => array( // "Active" player only
                    "dogma_effect_info" => array($card['id'] => self::getDogmaEffectInfo($card, $player_id, /*is_on_display=*/ true)),
                )
            )
        );
    }

    function argPromoteCardPlayerTurn() {
        $player_id = $this->innovationGameState->get('active_player');
        $must = $this->innovationGameState->usingFourthEditionRules() ? clienttranslate('must') : clienttranslate('may');
        return [
            "max_age_to_promote" => self::getCardInfo($this->innovationGameState->get('melded_card_id'))['age'],
            'message_for_player' => [
                'i18n' => ['log'],
                'log' => clienttranslate('${You} ${must} choose a card to promote from your forecast'),
                'args' => ['i18n' => ['must'], 'You' => 'You', 'must' => $must],
            ],
            'message_for_others' => [
                'i18n' => ['log'],
                'log' => clienttranslate('${player_name} ${must} choose a card to promote from your forecast'),
                'args' => ['i18n' => ['must'], 'player_name' => self::renderPlayerName($player_id), 'must' => $must],
            ],
        ];
    }

    function argDogmaPromotedCardPlayerTurn() {
        return [
            "promoted_card_id" => $this->innovationGameState->get('melded_card_id'),
        ];
    }

    function argPlayerTurn() {
        $player_id = $this->innovationGameState->get('active_player');
        // In the 4th edition, a card can be returned in order to dogma a top card on a non-adjacent player's board
        $non_adjacent_player_ids = self::countCardsInHand($player_id) > 0 ? self::getPlayerIdsAffectedByDistanceRule($player_id) : [];
        $age_to_draw = self::getAgeToDrawIn($player_id);
        return array(
            'i18n' => array('qualified_action'),
            'action_number' => $this->innovationGameState->get('first_player_with_only_one_action') || $this->innovationGameState->get('second_player_with_only_one_action') || $this->innovationGameState->get('has_second_action') ? 1 : 2,

            'qualified_action' => $this->innovationGameState->get('first_player_with_only_one_action') || $this->innovationGameState->get('second_player_with_only_one_action') ? clienttranslate('a single action') :
                                  ($this->innovationGameState->get('has_second_action') ? clienttranslate('a first action') : clienttranslate('a second action')),
            'age_to_draw' => $age_to_draw,
            'type_to_draw' => self::getCardTypeToDraw($age_to_draw, $player_id),
            'claimable_standard_achievement_values' => self::getClaimableStandardAchievementValues($player_id),
            'claimable_secret_values' => self::getClaimableSecretValues($player_id),
            'city_draw_falls_back_to_other_type' => $age_to_draw > 11 ? false : self::countCardsInLocationKeyedByAge(0, 'deck', CardTypes::CITIES)[$age_to_draw] == 0,
            '_private' => array(
                'active' => array( // "Active" player only
                    "non_adjacent_player_ids" => $non_adjacent_player_ids,
                    "dogma_effect_info" => self::getDogmaEffectInfoOfTopCards($player_id, $non_adjacent_player_ids),
                    "meld_info" => self::getMeldInfo($player_id),
                )
            )
        );
    }

    function getMeldInfo($player_id) {
        $info_by_card_id = array();
        
        // Get list iof cards which can be melded right now
        $cards_which_can_be_melded = self::getCardsInLocation($player_id, 'hand');
        $artifact = self::getArtifactOnDisplay($player_id);
        if ($artifact !== null) {
            $cards_which_can_be_melded[] = $artifact; 
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
    function getClaimableStandardAchievementValues($player_id) {
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
    function getClaimableSecretValues($player_id) {
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
    function getClaimableValuesIgnoringAvailability($player_id, $score_multiplier = 1) {
        $age_max = self::getMaxAgeOnBoardTopCards($player_id);
        $player_score = self::getPlayerScore($player_id) * $score_multiplier;
        $claimed_achievement_count = self::countCardsInLocationKeyedByAge($player_id, 'achievements', $type=null, $is_relic=false);
        
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
    function getDogmaEffectInfoOfTopCards($launcher_id, $non_adjacent_player_ids=[]) {
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
    function getDogmaEffectInfo($card, $launcher_id, $is_on_display = false) {
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
        $card_ids_with_visible_echo_effects = self::getCardsWithVisibleEchoEffects($card);
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
    function getMaxAgeForEndorsePayment($card) {
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
    function sharingHasNoEffect($card, $launcher_id, $executing_player_id, $card_ids_with_visible_echo_effects) {

        // TODO(4E): Add proper no-op detection for 4th edition cards.
        if ($this->innovationGameState->usingFourthEditionRules() && ($card['type'] == CardTypes::ECHOES || $card['type'] == CardTypes::ARTIFACTS)) {
            return false;
        }

        // Check all echo effects that will be executed
        foreach ($card_ids_with_visible_echo_effects as $card_id) {
            // NOTE: All cards with echo effects must be included in this switch statement, otherwise it breaks the logic
            // farther down in this method. Also, we can't return true anywhere here, since an echo effect is not the only
            // thing being executed.
            switch ($card_id) {
                case 219: // Safety Pin
                case 331: // Perfume
                case 332: // Ruler
                case 339: // Chopsticks
                case 345: // Lever
                case 346: // Linguistics
                case 348: // Horseshoes
                case 355: // Almanac
                case 356: // Magnifying Glass
                case 359: // Charitable Trust
                case 361: // Deodorant
                case 363: // Novel
                case 366: // Telescope
                case 372: // Pencil
                case 374: // Toilet
                case 375: // Lightning Rod
                case 377: // Coke
                case 385: // Bifocals
                case 389: // Hot Air Balloon
                case 391: // Dentures
                case 398: // Rubber
                case 399: // Jeans
                case 410: // Sliced Bread
                case 406: // X-Ray
                case 412: // Tractor
                case 414: // Television
                case 419: // Credit Card
                case 420: // Email
                case 421: // ATM
                case 423: // Karaoke
                    // These cards always have an effect.
                    return false;

                case 334: // Candles
                case 335: // Plumbing
                case 343: // Flute
                case 350: // Scissors
                case 367: // Kobukson
                case 371: // Barometer
                case 373: // Clock
                case 376: // Thermometer
                case 382: // Stove
                case 384: // Tuning Fork
                case 387: // Loom
                case 395: // Photography
                case 397: // Machine Gun
                case 401: // Elevator
                case 403: // Ice Cream
                case 422: // Wristwatch
                    // These cards sometimes have an effect. Until we add more granular logic for these cards, we will
                    // assume the cards have an effect.
                    return false;

                case 383: // Piano
                    // This card has no effect if all players have empty hands.
                    foreach (self::getAllActivePlayerIds() as $player_id) {
                        if (self::countCardsInLocation($player_id, 'hand') > 0) {
                            return false;
                        }
                    }
                    break;

                case 333: // Bangle
                case 338: // Umbrella
                case 342: // Bell
                case 349: // Glassblowing
                case 351: // Toothbrush
                case 364: // Sunglasses
                case 386: // Stethoscope
                case 392: // Morphine
                case 407: // Bandage
                case 411: // Air Conditioner
                case 418: // Jet
                    // These echo effects have no effect if the player has an empty hand.
                    if (self::countCardsInLocation($executing_player_id, 'hand') > 0) {
                        return false;
                    }
                    break;
            }
        }

        // Many cards do not have a non-demand effect on them
        if (self::getNonDemandEffect($card['id'], 1) == null) {
            return true;
        }

        // Check the card's non-demand effects
        // NOTE: There is no point in adding any cases for cards which have an echo effect which ALWAYS has an effect (e.g. "Draw a 2"),
        // but for the sake of completeness, it also doesn't hurt to add them below (even though they will never get executed).
        switch ($card['id']) {

            // TODO(FIGURES): Add cases.

            /*** Non-demand effects which read "No effect." ***/

            case 332: // Ruler
            case 335: // Plumbing
            case 344: // Puppet
                return true;

            /*** Cases where the execution of the non-demand depends on an earlier demand ***/

            case 20: // Mapmaking
            case 38: // Gunpowder
            case 48: // The Pirate Code
            case 62: // Vaccination
                // We can ignore these non-demand effect, because if the demand has no effect, then this won't either.
                return false;

            /*** Basic cases involving empty hands and/or empty score piles ***/

            case 1: // Tools
            case 9: // Agriculture
            case 13: // Code of Laws
            case 16: // Mathematics
            case 18: // Road Building
            case 19: // Currency
            case 42: // Perspective
            case 50: // Measurement
            case 59: // Classification
            case 63: // Democracy
            case 71: // Refrigeration
            case 73: // Lighting
            case 75: // Quantum Theory
            case 81: // Antibiotics
            case 114: // Papyrus of Ani
            case 120: // Lurgan Canoe
            case 121: // Xianrendong Shards
            case 130: // Baghdad Battery
            case 131: // Holy Grail
            case 136: // Charter of Liberties
            case 139: // Philosopher's Stone
            case 144: // Shroud of Turin
            case 153: // Cross of Coronado
            case 164: // Almira, Queen of the Castle
            case 174: // Marcha Real
            case 182: // Singer Model 27
            case 216: // Complex Numbers
            case 338: // Umbrella
            case 341: // Soap
            case 347: // Crossbow
            case 352: // Watermill
            case 362: // Sandpaper
            case 370: // Globe
            case 372: // Pencil
            case 384: // Tuning Fork
                // These non-demand effects have no effect if the player has an empty hand.
                return self::countCardsInLocation($executing_player_id, 'hand') == 0;

            case 33: // Education            
            case 146: // Delft Pocket Telescope
            case 217: // Newton-Wickins Telescope
            case 401: // Elevator
            case 430: // Flash Drive
                // These non-demand effects have no effect if the player has an empty score pile.
                return self::countCardsInLocation($executing_player_id, 'score') == 0;
                
            case 76: // Rocketry
                // These non-demand effects have no effect if all players have empty score piles.
                foreach (self::getAllActivePlayerIds() as $player_id) {
                    if (self::countCardsInLocation($player_id, 'score') > 0) {
                        return false;
                    }
                }
                return true;

            case 69: // Bicycle
                // These non-demand effects have no effect if the player has an empty score pile and an empty hand.
                return self::countCardsInLocation($executing_player_id, 'hand') == 0 && self::countCardsInLocation($executing_player_id, 'score') == 0;
            
            case 393: // Indian Clubs
                // These non-demands have no effect if the player has an empty hand or empty score pile.
                return self::countCardsInLocation($executing_player_id, 'hand') == 0 || self::countCardsInLocation($executing_player_id, 'score') == 0;

            /*** Other cases (sorted by card ID) **/

            case 15: // Calendar
                // The non-demand effect has no effect unless the player has more cards in their score pile than their hand.
                return self::countCardsInLocation($executing_player_id, 'score') <= self::countCardsInLocation($executing_player_id, 'hand');

            case 17: // Construction
                // The non-demand effect has no effect if the Empire achievement was already awarded.
                if (self::getCardInfo(105)['owner'] != 0) {
                    return true;
                }

                // The non-demand effect has no effect unless they are the only player with 5 top cards.
                $boards = self::getBoards(self::getAllActivePlayerIds());
                $num_players_with_five_top_cards = 0;
                $executing_player_has_five_top_cards = false;
                foreach ($boards as $player_id => $board) {
                    $number_of_top_cards = 0;
                    for ($color = 0; $color < 5; $color++) {
                        if (count($board[$color]) > 0) {
                            $number_of_top_cards++;
                        }
                    }
                    if ($number_of_top_cards == 5) {
                        $num_players_with_five_top_cards += 1;
                        if ($player_id == $executing_player_id) {
                            $executing_player_has_five_top_cards = true;
                        }
                    }
                }
                if ($num_players_with_five_top_cards != 1 || !$executing_player_has_five_top_cards) {
                    return true;
                }
                break;

            case 21: // Canal Building
                // The non-demand effect in the 4th edition will have an effect if the there is at least one age 3 card in the base deck.
                if ($this->innovationGameState->usingFourthEditionRules() && self::countCardsInLocationKeyedByAge(0, 'deck', CardTypes::BASE)[3] > 0) {
                    return false;
                }
                // The non-demand effect will have no effect if the player has an empty score pile and an empty hand.
                return self::countCardsInLocation($executing_player_id, 'hand') == 0 && self::countCardsInLocation($executing_player_id, 'score') == 0;

            case 24: // Philosophy
                // The non-demand effects have no effect if the player has no cards in hand and no piles that can be splayed left
                return self::countCardsInLocation($executing_player_id, 'hand') == 0 && count(self::getSplayableColorsOnBoard($executing_player_id, Directions::LEFT)) == 0;

            case 27: // Engineering
                // The non-demand effect has no effect if the player cannot splay their red pile left
                return !in_array(Colors::RED,  self::getSplayableColorsOnBoard($executing_player_id, Directions::LEFT));

            case 31: // Machinery
                // The non-demand effect has no effect if the player has no cards in hand and cannot splay their red pile left
                return self::countCardsInLocation($executing_player_id, 'hand') == 0 && !in_array(Colors::RED,  self::getSplayableColorsOnBoard($executing_player_id, Directions::LEFT));

            case 32: // Medicine
                if ($this->innovationGameState->usingFourthEditionRules()) {
                    // The non-demand has no effect if both the age 3 and age 4 base decks are empty.
                    $base_decks = self::countCardsInLocationKeyedByAge(0, 'deck', CardTypes::BASE);
                    return $base_decks[3] == 0 && $base_decks[4] == 0;
                }
                return true;

            case 36: // Printing Press
                // The non-demand effect has no effect if the player has no cards in their score pile and cannot splay their blue pile right
                return self::countCardsInLocation($executing_player_id, 'score') == 0 && !in_array(Colors::BLUE,  self::getSplayableColorsOnBoard($executing_player_id, Directions::RIGHT));

            case 43: // Enterprise
                // The non-demand effect has no effect if the player cannot splay their green pile right
                return !in_array(Colors::GREEN,  self::getSplayableColorsOnBoard($executing_player_id, Directions::RIGHT));

            case 44: // Reformation
                // The non-demand effect has no effect if the player has no cards in their hand and cannot splay their yellow or purple piles right
                $right_splayable_colors = self::getSplayableColorsOnBoard($executing_player_id, Directions::RIGHT);
                return self::countCardsInLocation($executing_player_id, 'hand') == 0 && !in_array(Colors::YELLOW,  $right_splayable_colors) && !in_array(Colors::PURPLE,  $right_splayable_colors);

            case 49: // Banking
                // The non-demand effect has no effect if the player cannot splay their green pile right
                return !in_array(Colors::GREEN,  self::getSplayableColorsOnBoard($executing_player_id, Directions::RIGHT));

            case 51: // Statistics
                // The non-demand effect has no effect if the player cannot splay their yellow pile right
                return !in_array(Colors::YELLOW,  self::getSplayableColorsOnBoard($executing_player_id, Directions::RIGHT));

            case 56: // Encyclopedia
                if ($this->innovationGameState->usingFourthEditionRules()) {
                    // The non-demand will have an effect if there are cards in any of the age 5, 6, or 7 base decks.
                    $base_decks = self::countCardsInLocationKeyedByAge(0, 'deck', CardTypes::BASE);
                    if ($base_decks[5] > 0 || $base_decks[6] > 0 || $base_decks[7] > 0) {
                        return false;
                    }
                }
                return self::countCardsInLocation($executing_player_id, 'score') == 0;

            case 60: // Metric System
                $right_splayable_colors = self::getSplayableColorsOnBoard($executing_player_id, Directions::RIGHT);
                // The second non-demand effect has no effect if the player's green pile is not or cannot be splayed right
                if (self::getCurrentSplayDirection($executing_player_id, Colors::GREEN) != Directions::RIGHT && !in_array(Colors::GREEN, $right_splayable_colors)) {
                    return true;
                }
                // The non-demand effects have no effect if the player has no piles that can be splayed right
                return count($right_splayable_colors) == 0;

            case 64: // Emancipation
                // The non-demand effect has no effect if the player cannot splay their red or purple piles right
                $right_splayable_colors = self::getSplayableColorsOnBoard($executing_player_id, Directions::RIGHT);
                return !in_array(Colors::RED,  $right_splayable_colors) && !in_array(Colors::PURPLE,  $right_splayable_colors);
            
            case 72: // Sanitation
                if ($this->innovationGameState->usingFourthEditionRules()) {
                    // The non-demand has no effect if both the age 7 and age 8 base decks are empty.
                    $base_decks = self::countCardsInLocationKeyedByAge(0, 'deck', CardTypes::BASE);
                    return $base_decks[7] == 0 && $base_decks[8] == 0;
                }
                return true;

            case 77: // Flight
                $up_splayable_colors = self::getSplayableColorsOnBoard($executing_player_id, Directions::UP);
                $top_red_card = self::getTopCardOnBoard($executing_player_id, Colors::RED);
                // The second non-demand effect has no effect if the player's red pile is not and cannot be splayed up
                if ($top_red_card == null || ($top_red_card['splay_direction'] != 3 && !in_array(Colors::RED, $up_splayable_colors))) {
                    return true;
                }
                // The non-demand effects have no effect if the player has no piles that can be splayed up
                return count($up_splayable_colors) == 0;
            
            case 91: // Ecology
                if ($this->innovationGameState->usingFourthEditionRules()) {
                    // The non-demand will have an effect if there are cards in the age 10 base deck.
                    if (self::countCardsInLocationKeyedByAge(0, 'deck', CardTypes::BASE)[10] > 0) {
                        return false;
                    }
                }
                return self::countCardsInLocation($executing_player_id, 'hand') == 0;
            
            case 94: // Specialization
                // The non-demand effect has no effect if the player has no cards in their hand and cannot splay their yellow or blue piles up
                $up_splayable_colors = self::getSplayableColorsOnBoard($executing_player_id, Directions::UP);
                return self::countCardsInLocation($executing_player_id, 'hand') == 0 && !in_array(Colors::BLUE,  $up_splayable_colors) && !in_array(Colors::YELLOW,  $up_splayable_colors);

            case 175: // Periodic Table
                // The non-demand effect has no effect if the player has top cards with unique values.
                return count(self::getColorsOfRepeatedValueOfTopCardsOnBoard($executing_player_id)) == 0;
            
            // All other cards with non-demand effects are assumed to have an effect.
            default:
                return false;
        }
    }

    /** Returns true if the dogma is guaranteed to have no effect when the specified player executes the demand effect (without revealing hidden info to the launching player). */
    function demandHasNoEffect($card, $launcher_id, $executing_player_id) {

        // TODO(4E): Add proper no-op detection for 4th edition cards.
        if ($this->innovationGameState->usingFourthEditionRules() && ($card['type'] == CardTypes::ECHOES || $card['type'] == CardTypes::ARTIFACTS)) {
            return false;
        }

        // Many cards do not have a demand effect on them
        if (self::getDemandEffect($card['id']) === null) {
            return true;
        }

        // Check the card's demand effect (excludes compel effects even they are technically a type of demand)
        switch ($card['id']) {

            /*** Basic cases involving empty hands and/or empty score piles ***/

            case 64: // Emancipation
            case 68: // Explosives
            case 71: // Refrigeration
            case 334: // Candles
            case 347: // Crossbow
            case 408: // Parachute
                // This demand has no effect if the executer has an empty hand.
                return self::countCardsInLocation($executing_player_id, 'hand') == 0;

            case 31: // Machinery
            case 72: // Sanitation
                // This demand has no effect if both the launcher amd executer have empty hands.
                return self::countCardsInLocation($launcher_id, 'hand') == 0 && self::countCardsInLocation($executing_player_id, 'hand') == 0;
        
            case 41: // Anatomy
            case 51: // Statistics
            case 62: // Vaccination
            case 99: // Databases
            case 393: // Indian Clubs
            case 411: // Air Conditioner
            case 430: // Flash Drive
                // This demand has no effect if the executer has an empty score pile.
                return self::countCardsInLocation($executing_player_id, 'score') == 0;

            case 32: // Medicine
                // This demand has no effect if both the launcher or executer have empty score piles.
                return self::countCardsInLocation($launcher_id, 'score') == 0 && self::countCardsInLocation($executing_player_id, 'score') == 0;

            /*** Other cases (sorted by card ID) ***/

            case 12: // City States
                // This demand has no effect if the player has less than 4 towers on their board.
                return self::getPlayerResourceCounts($executing_player_id)[4] < 4;

            case 20: // Mapmaking
                // This demand has no effect if the player has no 1s in their score pile.
                return self::countCardsInLocationKeyedByAge($executing_player_id, 'score')[1] == 0;

            case 27: // Engineering
                // This demand has no effect unless the player has a top card with a tower.
                foreach (self::getTopCardsOnBoard($executing_player_id) as $top_card) {
                    if (self::hasRessource($top_card, 4)) {
                        return false;
                    }
                }
                return true;

            case 40: // Navigation
                // This demand has no effect if the player has no 2s or 3s in their score pile.
                $age_counts = self::countCardsInLocationKeyedByAge($executing_player_id, 'score');
                return $age_counts[2] == 0 && $age_counts[3] == 0;

            case 43: // Enterprise
                // This demand has no effect unless the player has a top non-purple card with a crown.
                for ($color = 0; $color < 4; $color++) {
                    $top_card = self::getTopCardOnBoard($executing_player_id, $color);
                    if (self::hasRessource($top_card, 1 /* crown */)) {
                        return false;
                    }
                }
                return true;

            case 48: // The Pirate Code
                // This demand has no effect if the player has no 1s, 2s, 3s, or 4s in their score pile.
                $age_counts = self::countCardsInLocationKeyedByAge($executing_player_id, 'score');
                return $age_counts[1] == 0 && $age_counts[2] == 0 && $age_counts[3] == 0 && $age_counts[4] == 0;

            case 49: // Banking
                // This demand has no effect unless the player has a top non-green card with a factory.
                foreach ([0, 1, 3, 4] as $color) {
                    $top_card = self::getTopCardOnBoard($executing_player_id, $color);
                    if (self::hasRessource($top_card, 5 /* factory */)) {
                        return false;
                    }
                }
                return true;

            case 54: // Societies
                if ($this->innovationGameState->usingFirstEditionRules()) {
                    // This demand has no effect unless the player has a top non-purple card with a lightbulb.
                    for ($color = 0; $color < 4; $color++) {
                        $top_card = self::getTopCardOnBoard($executing_player_id, $color);
                        if (self::hasRessource($top_card, 3 /* lightbulb */)) {
                            return false;
                        }
                    }
                    return true;
                }
                // This demand has no effect unless the player has a top card with a lightbulb higher than the
                // launcher's top card of the same color.
                for ($color = 0; $color < 5; $color++) {
                    $launcher_top_card = self::getTopCardOnBoard($launcher_id, $color);
                    $player_top_card = self::getTopCardOnBoard($executing_player_id, $color);
                    if (!self::hasRessource($player_top_card, 3 /* lightbulb */)) {
                        continue;
                    }
                    if ($launcher_top_card === null || $player_top_card['faceup_age'] > $launcher_top_card['faceup_age']) {
                        return false;
                    }
                }
                return true;
        
            // All other cards with demand effects are assumed to have an effect.
            default:
                return false;
        }
    }

    /** Returns true if the dogma is guaranteed to have no effect when the specified player executes the compel effect (without revealing hidden info to the launching player). */
    function compelHasNoEffect($card, $launcher_id, $executing_player_id) {

        // TODO(4E): Add proper no-op detection for 4th edition cards.
        if ($this->innovationGameState->usingFourthEditionRules() && ($card['type'] == CardTypes::ECHOES || $card['type'] == CardTypes::ARTIFACTS)) {
            return false;
        }

        // Many cards do not have a compel effect on them
        if (self::getCompelEffect($card['id']) === null) {
            return true;
        }

        // Check the card's compel effect
        switch ($card['id']) {

            /*** Basic cases involving empty hands and/or empty score piles ***/

            case 141: // Moylough Belt Shrine
                // This demand has no effect if the player has an empty hand.
                return self::countCardsInLocation($executing_player_id, 'hand') == 0;

            case 118: // Jiskairumoko Necklace
            case 145: // Petition of Right
            case 148: // Tortugas Galleon
            case 167: // Frigate Constitution
                // This demand has no effect if the player has an empty score pile.
                return self::countCardsInLocation($executing_player_id, 'score') == 0;
                
            default:
                // All other cards with compel effects are assumed to have an effect.
                return false;

        }
    }
    
    function argDogmaEffect() {
        return self::getArgForDogmaEffect();
    }
    
    function argInterDogmaEffect() {
        return self::getArgForDogmaEffect();
    }
    
    function argPlayerInvolvedTurn() {
        return self::getArgForPlayerUnderDogmaEffect();
    }
    
    function argInterPlayerInvolvedTurn() {
        return self::getArgForPlayerUnderDogmaEffect();
    }
    
    function argInteractionStep() {
        return self::getArgForPlayerUnderDogmaEffect();
    }
    
    function argInterInteractionStep() {
        return self::getArgForPlayerUnderDogmaEffect();
    }
 
    function argPreSelectionMove() {
        return self::getArgForPlayerUnderDogmaEffect();
    }
    
    function argInterSelectionMove() {
        return self::getArgForPlayerUnderDogmaEffect();
    }
 
    function argSelectionMove() {
        $player_id = self::getActivePlayerId();
        $player_name = self::renderPlayerName($player_id);
        $special_type_of_choice = $this->innovationGameState->get('special_type_of_choice');
        
        $nested_card_state = self::getCurrentNestedCardState();

        // There won't be any nested card state if a player is returning cards after the Search icon or Junk Achievement icon was triggered.
        if ($nested_card_state == null) {
            $card_id = $this->innovationGameState->get('melded_card_id');
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

        $card = self::getCardInfo($card_id);
        
        $can_pass = $this->innovationGameState->get('can_pass') == 1;
        $can_stop = $this->innovationGameState->get('n_min') <= 0;
        
        if ($special_type_of_choice > 0) {
            switch(self::decodeSpecialTypeOfChoice($special_type_of_choice)) {
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
                $options = self::getObjectListFromDB(self::format("
                    SELECT
                        player_id AS value,
                        player_name AS text
                    FROM
                        player
                    WHERE
                        player_index IN ({player_indexes})
                ",
                    array('player_indexes' => join(',', $this->innovationGameState->getAsArray('player_array')))
                ));
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

            if ($code !== null && self::isInSeparateFile($card_id)) {
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
            
            switch($code) {
            // id 18, age 2: Road building
            case "18N1B":
                $message_for_player = clienttranslate('${You} may choose another player to transfer your top red card to his board, then transfer his top green card to your board:');
                $message_for_others = clienttranslate('${player_name} may choose another player to transfer his own red card to that player\'s board, then transfer that player\'s top green card to his own board');
                break;
            
            // id 21, age 2: Canal building
            case "21N1A":
                if ($this->innovationGameState->usingFourthEditionRules() && self::countCardsInLocationKeyedByAge(0, 'deck', CardTypes::BASE)[3] > 0) {
                    $message_args_for_player['age_3'] = self::getAgeSquare(3);
                    $message_args_for_others['age_3'] = self::getAgeSquare(3);
                    $message_for_player = clienttranslate('Do ${you} want to exchange all the highest cards in your hand with all the highest cards in your score pile or junk the ${age_3} pile?');
                    $message_for_others = clienttranslate('${player_name} may exchange all his highest cards in his hand with all the highest cards in his score pile or junk the ${age_3} pile?');
                    $options = array(array('value' => 1, 'text' => clienttranslate("Exchange")), array('value' => 0, 'text' => clienttranslate("Junk")));
                } else {
                    $message_for_player = clienttranslate('Do ${you} want to exchange all the highest cards in your hand with all the highest cards in your score pile?');
                    $message_for_others = clienttranslate('${player_name} may exchange all his highest cards in his hand with all the highest cards in his score pile');
                    $options = array(array('value' => 1, 'text' => clienttranslate("Yes")), array('value' => 0, 'text' => clienttranslate("No")));
                }
                break;
                
            // id 28, age 3: Optics        
            case "28N1A":
                $message_for_player = clienttranslate('${You} must choose an opponent with fewer points than you to transfer a card from your score pile to his score pile');
                $message_for_others = clienttranslate('${player_name} must choose an opponent with fewer points than him to transfer a card from his own score pile to that opponent score pile');
                break;
                
            // id 50, age 5: Measurement
            case "50N1B":
                $message_for_player = clienttranslate('${You} must choose a color');
                $message_for_others = clienttranslate('${player_name} must choose a color');
                break;
            
            // id 61, age 6: Canning
            case "61N1A":
                $age_to_draw = self::getAgeToDrawIn($player_id, 6);
                $age_6 = self::getAgeSquare($age_to_draw);
                $max_age = self::getMaxAge();
                $age_10 = self::getAgeSquare($max_age);
                $icon_5 = Icons::render(5);
                $message_args_for_player['age_6'] = $age_6;
                $message_args_for_player['age_10'] = $age_10;
                $message_args_for_player['icon_5'] =$icon_5;
                $message_args_for_others['age_6'] = $age_6;
                $message_args_for_others['age_10'] = $age_10;
                $message_args_for_others['icon_5'] =$icon_5;
                $message_for_player = $age_to_draw <= $max_age ? clienttranslate('Do ${you} want to draw and tuck a ${age_6}, then score all your top cards without a ${icon_5}?')
                                                        : clienttranslate('Finish the game (attempt to draw above ${age_10})');
                $message_for_others = $age_to_draw <= $max_age ? clienttranslate('${player_name} may draw and tuck a ${age_6}, then score all his top cards without a ${icon_5}')
                                                        : clienttranslate('${player_name} may finish the game (attempting to draw above ${age_10})');
                $options = array(array('value' => 1, 'text' => clienttranslate("Yes")), array('value' => 0, 'text' => clienttranslate("No")));
                break;
            
            // id 66, age 7: Publications
            case "66N1A_3E":
                $message_for_player = clienttranslate('${You} may rearrange one color of your cards. Click on a card then use arrows to move it within the pile');
                $message_for_others = clienttranslate('${player_name} may rearrange one color of his cards');
                break;

            case "66N2A_4E":
                $message_for_player = clienttranslate('${You} may junk an available special achievement or make a junked special achievement available');
                $message_for_others = clienttranslate('${player_name} may junk an available special achievement or make a junked special achievement available');
                break;
                
            // id 69, age 7: Bicycle 
            case "69N1A":
                $message_for_player = clienttranslate('Do ${you} want to exchange all the cards in your hand with all the cards in your score pile?');
                $message_for_others = clienttranslate('${player_name} may exchange all his cards in his hand with all the cards in his score pile');
                $options = array(array('value' => 1, 'text' => clienttranslate("Yes")), array('value' => 0, 'text' => clienttranslate("No")));
                break;

            // id 80, age 8: Mass media
            case "80N1B":
                $message_for_player = clienttranslate('Choose a value');
                $message_for_others = clienttranslate('${player_name} must choose a value');
                break;
                
            // id 83, age 8: Empiricism
            case "83N1A":
                $message_for_player = clienttranslate('${You} must choose two colors');
                $message_for_others = clienttranslate('${player_name} must choose two colors');
                break;

            // id 91, age 9: Ecology
            case "91N2A":
                $message_args_for_player['age_10'] = self::getAgeSquareWithType(10, 0);
                $message_args_for_others['age_10'] = self::getAgeSquareWithType(10, 0);
                $message_for_player = clienttranslate('Do ${you} want to junk the ${age_10} pile?');
                $message_for_others = clienttranslate('${player_name} may junk the ${age_10} pile');
                $options = array(array('value' => 1, 'text' => clienttranslate("Yes")), array('value' => 0, 'text' => clienttranslate("No")));
                break;

            // id 92, age 9: Suburbia
            case "92N2A":
                $message_args_for_player['age_9'] = self::getAgeSquareWithType(9, 0);
                $message_args_for_others['age_9'] = self::getAgeSquareWithType(9, 0);
                $message_for_player = clienttranslate('Do ${you} want to junk the ${age_9} pile?');
                $message_for_others = clienttranslate('${player_name} may junk the ${age_9} pile');
                $options = array(array('value' => 1, 'text' => clienttranslate("Yes")), array('value' => 0, 'text' => clienttranslate("No")));
                break;
            
            // id 102, age 10: Stem cells 
            case "102N1A":
                $message_for_player = clienttranslate('Do ${you} want to score all the cards from your hand?');
                $message_for_others = clienttranslate('${player_name} may score all the cards from his hand');
                $options = array(array('value' => 1, 'text' => clienttranslate("Yes")), array('value' => 0, 'text' => clienttranslate("No")));
                break;

            // id 147, Artifacts age 4: East India Company Charter
            case "147N1A":
                $message_for_player = clienttranslate('Choose a value');
                $message_for_others = clienttranslate('${player_name} must choose a value');
                break;
 
            // id 152, Artifacts age 5: Mona Lisa
            case "152N1A":
                $message_for_player = clienttranslate('Choose a color');
                $message_for_others = clienttranslate('${player_name} must choose a color');
                break;
                
            case "152N1B":
                $message_for_player = clienttranslate('Choose a number');
                $message_for_others = clienttranslate('${player_name} must choose a number');
                break;

            // id 157, Artifacts age 5: Bill of Rights
            case "157C1A":
                $message_for_player = clienttranslate('${You} must choose a color');
                $message_for_others = clienttranslate('${player_name} must choose a color');
                break;

            // id 158, Artifacts age 5: Ship of the Line Sussex
            case "158N1B":
                $message_for_player = clienttranslate('${You} must choose a color');
                $message_for_others = clienttranslate('${player_name} must choose a color');
                break;

            // id 162, Artifacts age 5: The Daily Courant
            case "162N1A":
                $message_for_player = clienttranslate('Choose a value');
                $message_for_others = clienttranslate('${player_name} must choose a value');
                break;
                
            // id 170, Artifacts age 6: Buttonwood Agreement
            case "170N1A":
                $message_for_player = clienttranslate('${You} must choose three colors');
                $message_for_others = clienttranslate('${player_name} must choose three colors');
                break;

            // id 173, Artifacts age 6: Moonlight Sonata
            case "173N1A":
                $message_for_player = clienttranslate('${You} must choose a color');
                $message_for_others = clienttranslate('${player_name} must choose a color');
                break;
                
            // id 179, Artifacts age 7: International Prototype Metre Bar
            case "179N1A":
                $message_for_player = clienttranslate('Choose a value');
                $message_for_others = clienttranslate('${player_name} must choose a value');
                break;

            // id 184, Artifacts age 7: The Communist Manifesto
            case "184N1A":
                $message_for_player = clienttranslate('Choose a player to transfer a card to');
                $message_for_others = clienttranslate('${player_name} must choose a player to transfer a card to');
                break;

            // id 191, Artifacts age 8: Plush Beweglich Rod Bear
            case "191N1A":
                $message_for_player = clienttranslate('Choose a value');
                $message_for_others = clienttranslate('${player_name} must choose a value');
                break;

            // id 211, Artifacts age 10: Dolly the Sheep
            case "211N1A":
                $message_for_player = clienttranslate('Do ${you} want to score your bottom yellow card?');
                $message_for_others = clienttranslate('${player_name} must choose whether to score his bottom yellow card');
                $options = array(array('value' => 1, 'text' => clienttranslate("Yes")), array('value' => 0, 'text' => clienttranslate("No")));
                break;

            case "211N1B":
                $age_to_draw = self::getAgeToDrawIn($player_id, 1);
                $age_1 = self::getAgeSquare($age_to_draw);
                $max_age = self::getMaxAge();
                $age_10 = self::getAgeSquare($max_age);
                $message_args_for_player['age_1'] = $age_1;
                $message_args_for_player['age_10'] = $age_10;
                $message_args_for_others['age_1'] = $age_1;
                $message_args_for_others['age_10'] = $age_10;
                $message_for_player = $age_to_draw <= $max_age ? clienttranslate('Do ${you} want to draw and tuck a ${age_1}?')
                                                        : clienttranslate('Finish the game (attempt to draw above ${age_10})');
                $message_for_others = $age_to_draw <= $max_age ? clienttranslate('${player_name} may draw and tuck a ${age_1}')
                                                        : clienttranslate('${player_name} may finish the game (attempting to draw above ${age_10})');
                $options = array(array('value' => 1, 'text' => clienttranslate("Yes")), array('value' => 0, 'text' => clienttranslate("No")));
                break;

            // id 443, age 11: Fusion
            case "443N1B":
                $message_for_player = clienttranslate('Choose a value');
                $message_for_others = clienttranslate('${player_name} must choose a value');
                break;

            // id 489, Unseen age 1: Handshake
            case "489D1A":
                $message_for_player = clienttranslate('${You} must choose two colors');
                $message_for_others = clienttranslate('${player_name} must choose two colors');
                break;

            // id 525, Unseen age 5: Popular Science
            case "525N1A":
                $message_for_player = clienttranslate('Choose a value');
                $message_for_others = clienttranslate('${player_name} must choose a value');
                break;

            default:
                // This should not happen
                if (!self::isInSeparateFile($card_id)) {
                    throw new BgaVisibleSystemException(self::format(self::_("Unreferenced card effect code in section S: '{code}'"), array('code' => $code)));
                }
            }
            
            $card_names = self::getDogmaCardNames();

            $args = array_merge(array(
                // Public info
                'card_name' => 'card_name',
                'special_type_of_choice' => $special_type_of_choice,
                'options' => $options,
                'can_pass' => $can_pass,
                'can_stop' => false,
                'opponent_id' => null,
                'splay_direction' => null,
                'color_pile' => null,
                'message_for_player' => array('i18n' => array('log'), 'log' => $message_for_player, 'args' => $message_args_for_player),
                'message_for_others' => array('i18n' => array('log'), 'log' => $message_for_others, 'args' => $message_args_for_others),
                'player_name' => $player_name
            ), $card_names);

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
            $location_from = self::decodeLocation($this->innovationGameState->get("location_from"));
            $bottom_from = $this->innovationGameState->get("bottom_from");
            $owner_to = $this->innovationGameState->get("owner_to");
            $location_to = self::decodeLocation($this->innovationGameState->get("location_to"));
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
                    'message_for_player' => ['i18n' => ['log'], 'log' => $you_must, 'args' => ['You' => 'You']],
                    'message_for_others' => ['i18n' => ['log'], 'log' => $player_must, 'args' => ['player_name' => $player_name]],
                    'splayable_colors' => $splayable_colors,
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
        
        $args = array_merge($messages, $card_names, array(
            // Public info
            'card_name' => 'card_name',
            'special_type_of_choice' => $special_type_of_choice,
            'can_pass' => $can_pass,
            'can_stop' => $can_stop,
            'opponent_id' => $opponent_id,
            'splay_direction' => $splay_direction,
            'splay_direction_in_clear' => $splay_direction_in_clear,
            'color_pile' => $splay_direction === null && ($location_from == 'pile' || $location_from == 'pile,score') ? $this->innovationGameState->getAsArray('color_array')[0] : null,
            'card_interaction' => $code,
            'num_cards_already_chosen' => $n,
            
            // Private info
            '_private' => array(
                'active' => array( // "Active" player only
                    "visible_selectable_cards" => self::getVisibleSelectedCards($player_id),
                    "selectable_rectos" => self::getSelectableRectos($player_id), // Most of the time, the player choose among versos he can see this array is empty so this array is empty except for few dogma effects
                    "must_show_score" => $must_show_score,
                    "must_show_forecast" => $must_show_forecast,
                    "must_show_junk" => $must_show_junk,
                    "show_all_cards_on_board" => $special_type_of_choice == 0 && ($splay_direction == -1 || $splay_direction === null) && $location_from == 'board' && $bottom_from == 1,
                )
            ))
        );
        
        $args['i18n'][] = 'card_name';
        $args['i18n'][] = 'splay_direction_in_clear';
        $args['i18n'][] = 'splayable_colors_in_clear';
        
        return $args;
    }

    function getRecursivelyTranslatedNumberRange($n_min, $n_max) {
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
            'log' => $number_log,
            'args' => [
                'i18n' => ['n_min', 'n_max'],
                'n_min' => self::renderNumber($n_min),
                'n_max' => self::renderNumber($n_max),
            ],
        ];
    }

    function getRecursivelyTranslatedCardSelection($age_min, $age_max, $with_icons, $without_icons, $with_demand_effect) {
        $card_args = array();

        $selectable_colors = $this->innovationGameState->getAsArray('color_array');
        if (count($selectable_colors) < 5) {
            $colors = self::getRecursivelyTranslatedColorList($selectable_colors);
            $card_log = clienttranslate('${color} ${cards}${of_age}${with_icon}${with_demand}');
            $card_args['color'] = $colors;
            $card_args['i18n'] = ['color', 'cards', 'of_age', 'with_icon', 'with_demand'];
        } else {
            $card_log = clienttranslate('${cards}${of_age}${with_icon}${with_demand}');
            $card_args['i18n'] = ['cards', 'of_age', 'with_icon', 'with_demand'];
        }
        $card_args['cards'] = clienttranslate('card(s)');
        $card_args['of_age'] = '';
        $card_args['with_icon'] = '';
        $card_args['with_demand'] = '';

        if ($age_min != 1 || $age_max != 11) {
            if ($age_min == $age_max) {
                $of_age_log = clienttranslate(' of value ${<}${age_min}${>}');
            } else if ($age_min + 1 == $age_max) {
                $of_age_log = clienttranslate(' of value ${<}${age_min}${>} or ${<}${age_max}${>}');
            } else {
                $of_age_log = clienttranslate(' of value ${<}${age_min}${>} to ${<}${age_max}${>}');
            }
            $card_args['of_age'] = [
                'i18n' => ['log'],
                'log' => $of_age_log,
                'args' => array_merge(self::getDelimiterMeanings($of_age_log), ['age_min' => $age_min, 'age_max' => $age_max]),
            ];
        }

        if (count($with_icons) === 1) {
            $with_icon_log = clienttranslate(' with a ${[}${icon}${]}');
            $card_args['with_icon'] = [
                'i18n' => ['log'],
                'log' => $with_icon_log,
                'args' => array_merge(self::getDelimiterMeanings($with_icon_log), ['icon' => $with_icons[0]]),
            ];
        } else if (count($with_icons) === 2) {
            $with_icon_log = clienttranslate(' with a ${[}${icon_1}${]} or a ${[}${icon_2}${]}');
            $card_args['with_icon'] = [
                'i18n' => ['log'],
                'log' => $with_icon_log,
                'args' => array_merge(self::getDelimiterMeanings($with_icon_log), ['icon_1' => $with_icons[0], 'icon_2' => $with_icons[1]]),
            ];
        } else if (count($without_icons) === 1) {
            $without_icon_log = clienttranslate(' without a ${[}${icon}${]}');
            $card_args['with_icon'] = [
                'i18n' => ['log'],
                'log' => $without_icon_log,
                'args' => array_merge(self::getDelimiterMeanings($without_icon_log), ['icon' => $without_icons[0]]),
            ];
        } else if (count($without_icons) === 2) {
            $without_icon_log = clienttranslate(' without a ${[}${icon_1}${]} or a ${[}${icon_2}${]}');
            $card_args['with_icon'] = [
                'i18n' => ['log'],
                'log' => $without_icon_log,
                'args' => array_merge(self::getDelimiterMeanings($without_icon_log), ['icon_1' => $without_icons[0], 'icon_2' => $without_icons[1]]),
            ];
        }

        if ($with_demand_effect == 1) {
            $card_args['with_demand'] = clienttranslate(' with a demand effect');
        }
    
        return ['i18n' => ['log'], 'log' => $card_log, 'args' => $card_args];
    }

    function getRecursivelyTranslatedColorList($colors) {
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
                for ($color = 0; $color < 5; $color++) {
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

//////////////////////////////////////////////////////////////////////////////
//////////// Game state actions
////////////

    /*
        Here, you can create methods defined as "game state actions" (see "action" property in states.inc.php).
        The action method of state X is called everytime the current game state is set to X.
    */

    /*
    
    Example for game state "MyGameState":

    function stMyGameState()
    {
        // Do some stuff ...
        
        // (very often) go to another gamestate
        $this->gamestate->nextState('some_gamestate_transition');
    }    
    */
    
    function stTurn0() {
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
    
    function stWhoBegins() {
        $this->innovationGameState->set('turn0', 0); // End of turn 0

        if ($this->innovationGameState->unseenExpansionEnabled()) {
            self::resetWillDrawUnseenCardNext();
        }
        
        $cards = self::getSelectedCards();
        // Deselect the cards
        self::deselectAllCards();
        
        // Execute the melds planned by players
        foreach($cards as $card) {
            $this->gamestate->changeActivePlayer($card['owner']);
            self::meldCard($card, $card['owner']);
        }
        
        // The first active player is the one who chose for meld the first card in (English) alphabetical order
        $earliest_card = null;
        foreach($cards as $card) {
            if ($earliest_card === null || self::comesAlphabeticallyBefore($card, $earliest_card)) {
                $earliest_card = $card;
            }
        }
        $player_id = $earliest_card['owner'];
        
        $english_card_name = self::getCardName($earliest_card['id']);
        self::notifyPlayer($player_id, 'initialCardChosen', clienttranslate('${You} melded the first card in English alphabetical order (${english_name}): You play first.'), array(
            'You' => 'You',
            'english_name' => $english_card_name,
        ));
        self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} melded the first card in English alphabetical order (${english_name}): he plays first.'), array(
            'player_name' => self::getPlayerNameFromId($player_id),
            'english_name' => $english_card_name,
        ));
        
        // Enter normal play loop
        $this->innovationGameState->set('active_player', $player_id);
        self::setLauncherId($player_id);
        $this->gamestate->changeActivePlayer($player_id);
        $this->innovationGameState->set('current_action_number', 1);
        self::notifyGeneralInfo('<!--empty-->');
        self::trace('turn0->playerTurn');
        $this->gamestate->nextState();
    }
    
    function stInterPlayerTurn() {
        // An action of the player has been fully resolved.

        // Check for special achievements (only necessary in 4th edition)
        if ($this->innovationGameState->usingFourthEditionRules()) {
            try {
            self::checkForSpecialAchievements(/*is_end_of_action_check=*/ true);
            } catch(EndOfGame $e) {
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
            self::resetFlagsForMonument();

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
    
    function stDogmaEffect() {
        // An effect of a dogma has to be resolved
        $nested_card_state = self::getCurrentNestedCardState();
        $card_id = $nested_card_state['card_id'];
        $current_effect_type = $nested_card_state['current_effect_type'];
        $current_effect_number = $nested_card_state['current_effect_number'];
        $card = self::getCardInfo($card_id);
        $qualified_effect = self::qualifyEffect($current_effect_type, $current_effect_number, $card);
        
        // Search for the first player who will undergo/share the effects, if any
        $launcher_id = self::getLauncherId();
        // During nested execution echo/non-demand effects are not shared with other players.
        $first_player = $nested_card_state['nesting_index'] > 0 && ($current_effect_type == 1 || $current_effect_type == 3) ? $launcher_id : self::getFirstPlayerUnderEffect($current_effect_type, $launcher_id);
        if ($first_player === null) {
            // There is no player affected by the effect
            self::notifyGeneralInfo("<span class='minor_information'>" . clienttranslate('Nobody is affected by the ${qualified_effect} of the card.') . "</span>", array(
                'i18n' => array('qualified_effect'),
                'qualified_effect' => $qualified_effect
            ));
            
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
    
    function stInterDogmaEffect() {
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
                    self::format("SELECT card_id FROM echo_execution WHERE nesting_index = {nesting_index} AND execution_index = {execution_index}",
                        array('nesting_index' => $nesting_index, 'execution_index' => $previous_effect_number)));
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
                self::format("DELETE FROM echo_execution WHERE nesting_index = {nesting_index} AND execution_index = {execution_index}",
                    array('nesting_index' => $nesting_index, 'execution_index' => $previous_effect_number)));
            
            if ($previous_effect_number > 1) {
                // Move to next echo effect
                $next_effect_number = $previous_effect_number - 1;
                $next_effect_type = 3;
            } else {
                // The last echo effect is complete, so move onto the next non-echo effect
                $next_effect_number = 1;
                if (self::getCompelEffect($card_id)) {
                    $next_effect_type = 2; // I compel
                } else if (self::getDemandEffect($card_id)) {
                    $next_effect_type = 0; // I demand
                } else {
                    $next_effect_type = 1; // non-demand
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
                self::notifyAll('logWithCardTooltips', clienttranslate('Execution of ${card_1} is complete.'),
                    ['card_1' => $card_args, 'card_ids' => [$card_id]]);
                
                self::popCardFromNestedDogmaStack();

                $nested_card_state = self::getCurrentNestedCardState();
                $this->gamestate->changeActivePlayer($nested_card_state['current_player_id']);
                self::trace('interDogmaEffect->playerInvolvedTurn');
                $this->gamestate->nextState('playerInvolvedTurn');
                return;
            }

            // Return the Artifact on display if the free dogma action was used
            $this->gamestate->changeActivePlayer($launcher_id);
            $nested_card_state = self::getNestedCardState(0);
            if ($nested_card_state['card_location'] == 'display') {
                $launcher_id = $nested_card_state['launcher_id'];
                self::returnCard($card);
                self::giveExtraTime($launcher_id);
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
                self::notifyGeneralInfo('<span class="minor_information">${text}</span>', array('i18n'=>array('text'), 'text'=>clienttranslate('Sharing bonus.')));
                $player_who_launched_the_dogma = $this->innovationGameState->get('active_player');
                try {
                    self::executeDraw($player_who_launched_the_dogma); // Draw a card with age consistent with player board
                }
                catch (EndOfGame $e) {
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
    function isInSeparateFile($card_id) {
        return $card_id <= 4
            || $card_id == 22
            || $card_id == 65
            || $card_id == 72
            || (110 <= $card_id && $card_id <= 214)
            || (330 <= $card_id && $card_id <= 434)
            || (440 <= $card_id && $card_id == 441)
            || $card_id == 445
            || (470 <= $card_id && $card_id <= 486)
            || $card_id == 488
            || (492 <= $card_id && $card_id <= 494)
            || $card_id == 498
            || $card_id == 503
            || (505 <= $card_id && $card_id <= 509)
            || $card_id == 512
            || (514 <= $card_id && $card_id <= 524)
            || $card_id >= 528;
    }

    function getCardInstance($card_id, $execution_state) {
        $card = $this->getCardInfo($card_id);
        $set = "Base";
        if ($card['type'] == 1) {
            $set = "Artifacts";
        } else if ($card['type'] == 3) {
            $set = "Echoes";
        } else if ($card['type'] == 5) {
            $set = "Unseen";
        }
        require_once("modules/Innovation/Cards/${set}/Card${card_id}.php");
        $classname = "Innovation\Cards\\${set}\Card${card_id}";
        return new $classname($this, $execution_state);
    }
    
    function stPlayerInvolvedTurn() {
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
                self::format("SELECT card_id FROM echo_execution WHERE nesting_index = {nesting_index} AND execution_index = {effect_number}",
                    array('nesting_index' => $nesting_index, 'effect_number' => $current_effect_number)));
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
        
        $crown = Icons::render(1);
        $leaf = Icons::render(2);
        $lightbulb = Icons::render(3);
        $tower = Icons::render(4);
        $factory = Icons::render(5);
        $clock = Icons::render(6);
        
        $using_execution_status_object = false;

        try {

            if (self::isInSeparateFile($card_id)) {
                $cardInstance = self::getCardInstance($card_id, $executionState);
                if ($nested_card_state['post_execution_index'] == 0 || $cardInstance->hasPostExecutionLogic()) {
                    self::getCardInstance($card_id, $executionState)->initialExecution();
                }
                $using_execution_status_object = true;
            }

            switch($code) {
            // The first number is the id of the card
            // D1 means the first (and single) I demand effect
            // C1 means the first (and single) I compel effect
            // N1 means the first non-demand effect
            // N2 means the second non-demand effect
            // N3 means the third non-demand effect
            // E1 means the first (and single) echo effect
            
            // Setting the $step_max variable means there is interaction needed with the player
            
            // id 5, age 1: Oars
            case "5D1":
                // Skip automation entirely if Echoes is being used and there's at least two cards with
                // crowns (unless there's at least one Echoes card without a crown). We do this because
                // the selection order can often affect which cards are drawn, so automating it is not
                // possible.
                $num_cards_with_crowns = 0;
                $num_echoes_cards_without_crowns = 0;
                foreach (self::getCardsInHand($player_id) as $card) {
                    if (self::hasRessource($card, 1)) {
                        $num_cards_with_crowns++;
                    } else if ($card['type'] == 3) {
                        $num_echoes_cards_without_crowns++;
                    }
                }
                if ($this->innovationGameState->echoesExpansionEnabled() && $num_cards_with_crowns >= 2 && $num_echoes_cards_without_crowns == 0) {
                    $step_max = 1;
                } else {
                    do {
                        $card_transfered = false;
                        foreach (self::getCardsInHand($player_id) as $card) {
                            // "I demand you transfer a card with a crown from your hand to my score pile"
                            if (self::hasRessource($card, 1)) {
                                self::transferCardFromTo($card, $launcher_id, 'score');
                                self::executeDraw($player_id, 1); // "If you do, draw a 1"
                                $card_transfered = true; // "and repeat this dogma effect"
                                self::setAuxiliaryValue(1);
                                break;
                            }
                        }
                    } while ($card_transfered && !$this->innovationGameState->usingFirstEditionRules());
                    // Reveal hand to prove that they have no crowns.
                    self::revealHand($player_id);
                }
                break;
            
            case "5N1":
                if (self::getAuxiliaryValue() <= 0) { // "If no cards were transfered due to this demand"
                    self::executeDraw($player_id, 1); // "Draw a 1"
                }
                break;
            
            // id 6, age 1: Clothing
            case "6N1":
                $step_max = 1;
                break;
            case "6N2":
                // "Score a 1 for each color present on your board not present on any other player board"
                // Compute the number of specific colors
                $number_to_be_scored = 0;
                $boards = self::getBoards(self::getAllActivePlayerIds());
                for ($color = 0; $color < 5; $color++) { // Evaluate each color
                    if (count($boards[$player_id][$color]) == 0) { // The player does not have this color => no point
                        continue;
                    }
                    // The player has this color, do opponents have?
                    $color_on_opponent_board = false;
                    foreach (self::getActiveOpponentIds($player_id) as $opponent_id) {
                        if (count($boards[$opponent_id][$color]) > 0) { // This opponent has this color => no point
                            $color_on_opponent_board = true;
                            break;
                        }
                    }
                    if (!$color_on_opponent_board) { // The opponents do not have this color => point
                        $number_to_be_scored++;
                    }
                }
                // Indicate this number
                $translated_number = self::renderNumber($number_to_be_scored);
                self::notifyPlayer($player_id, 'log', clienttranslate('${You} have ${n} color(s) present on your board not present on any opponent\'s board.'), array('i18n' => array('n'), 'You' => 'You', 'n' => $translated_number));
                self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} has ${n} color(s) present on his board not present on any of his opponents\' boards.'), array('i18n' => array('n'), 'player_name' => self::renderPlayerName($player_id), 'n' => $translated_number));

                // Score this number of times
                for ($i = 0; $i < $number_to_be_scored; $i++) {
                    self::executeDraw($player_id, 1, 'score');
                }
                break;
            
            // id 7, age 1: Sailing
            case "7N1":
                self::executeDrawAndMeld($player_id, 1); // "Draw and meld a 1"
                break;
                
            // id 8, age 1: The wheel
            case "8N1":
                // "Draw two 1"
                self::executeDraw($player_id, 1);
                self::executeDraw($player_id, 1);
                break;
                
            // id 9, age 1: Agriculture
            case "9N1":
                $step_max = 1;
                break;
            
            // id 10, age 1: Domestication
            case "10N1":
                $step_max = 1;
                break;
            
            // id 11, age 1: Masonry
            case "11N1":
                $step_max = 1;
                break;
                
            // id 12, age 1: City states
            case "12D1":
                if (self::getPlayerSingleRessourceCount($player_id, 4) >= 4) { // "If you have at least four towers on your board"
                    self::notifyPlayer($player_id, 'log', clienttranslate('${You} have at least four ${icon} on your board.'), array('You' => 'You', 'icon' => $tower));
                    self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} has at least four ${icon} on his board.'), array('player_name' => self::renderPlayerName($player_id), 'icon' => $tower));
                    $step_max = 1;
                }
                else {
                    self::notifyPlayer($player_id, 'log', clienttranslate('${You} have less than four ${icon} on your board.'), array('You' => 'You', 'icon' => $tower));
                    self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} has less than four ${icon} on his board.'), array('player_name' => self::renderPlayerName($player_id), 'icon' => $tower));
                }
                break;
                
            // id 13, age 1: Code of laws
            case "13N1":
                $step_max = 1;
                break;
                
            // id 14, age 1: Mysticism
            case "14N1":
                $card = self::executeDraw($player_id, 1, 'revealed'); // "Draw and reveal a 1
                $color = $card['color'];
                if (self::hasThisColorOnBoard($player_id, $color)) { // "If it is the same color of any card on your board"
                    self::notifyPlayer($player_id, 'log', clienttranslate('This card is ${color}; ${you} have this color on your board.'), array('i18n' => array('color'), 'you' => 'you', 'color' => Colors::render($color)));
                    self::notifyAllPlayersBut($player_id, 'log', clienttranslate('This card is ${color}; ${player_name} has this color on his board.'), array('i18n' => array('color'), 'player_name' => self::renderPlayerName($player_id), 'color' => Colors::render($color)));
                    self::meldCard($card, $player_id); // "Meld it"
                    self::executeDraw($player_id, 1); // "Draw a 1"
                }
                else {
                    self::notifyPlayer($player_id, 'log', clienttranslate('This card is ${color}; ${you} do not have this color on your board.'), array('i18n' => array('color'), 'you' => 'you', 'color' => Colors::render($color)));
                    self::notifyAllPlayersBut($player_id, 'log', clienttranslate('This card is ${color}; ${player_name} does not have this color on his board.'), array('i18n' => array('color'), 'player_name' => self::renderPlayerName($player_id), 'color' => Colors::render($color)));
                    self::transferCardFromTo($card, $player_id, 'hand'); // (Put the card in your hand)
                }
                break;
                
            // id 15, age 2: Calendar
            case "15N1":
                if (self::countCardsInLocation($player_id, 'score') > self::countCardsInLocation($player_id, 'hand')) { // "If you have more cards in your score pile than in your hand"
                    self::notifyPlayer($player_id, 'log', clienttranslate('${You} have more cards in your score pile than in your hand.'), array('You' => 'You'));
                    self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} has more cards in his score pile than in his hand.'), array('player_name' => self::renderPlayerName($player_id)));
                    
                    self::executeDraw($player_id, 3); // "Draw two 3"
                    self::executeDraw($player_id, 3); // 
                }
                else {
                    self::notifyPlayer($player_id, 'log', clienttranslate('${You} do not have more cards in your score pile than in your hand.'), array('You' => 'You'));
                    self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} does not have more cards in his score pile than in his hand.'), array('player_name' => self::renderPlayerName($player_id)));
                }
                break;
                
            // id 16, age 2: Mathematics
            case "16N1":
                $step_max = 1;
                break;
                
            // id 17, age 2: Construction
            case "17D1":
                $step_max = 1;
                break;
                
            case "17N1":
                $boards = self::getBoards(self::getAllActivePlayerIds());
                $eligible = true;
                foreach ($boards as $current_id => $board) {
                    if ($current_id == self::getPlayerTeammate($player_id)) { // Ignore teammate
                        continue;
                    }
                    $number_of_top_cards = 0;
                    for($color=0; $color<5; $color++) {
                        if (count($board[$color]) > 0) { // This player has a top card for this color.
                            $number_of_top_cards++;
                        }
                    }
                    if ($current_id == $player_id && $number_of_top_cards < 5 || $current_id != $player_id && $number_of_top_cards == 5) { // This player is the active player and has not 5 top cards, or he is an opponent who has 5 top cards
                        $eligible = false;
                    }
                }
                if ($eligible) { // "If you are the only player with five top cards"
                    $achievement = self::getCardInfo(105);
                    if ($achievement['owner'] == 0 && $achievement['location'] == 'achievements') {
                        self::notifyPlayer($player_id, 'log', clienttranslate('${You} are the only player with five top cards.'), array('You' => 'You'));
                        self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} is the only player with five top cards.'), array('player_name' => self::renderPlayerName($player_id)));
                        self::transferCardFromTo($achievement, $player_id, 'achievements');  // "Claim the Empire achievement"
                    } else {
                        self::notifyPlayer($player_id, 'log', clienttranslate('${You} are the only player with five top cards but the Empire achievement has already been claimed.'), array('You' => 'You'));
                        self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} is the only player with five top cards but the Empire achievement has already been claimed.'), array('player_name' => self::renderPlayerName($player_id)));
                    }
                }
                break;
            
            // id 18, age 2: Road building
            case "18N1":
                $step_max = 1;
                break;
                
            // id 19, age 2: Currency
            case "19N1":
                self::setAuxiliaryValueFromArray(array());
                $step_max = 1;
                break;
                
            // id 20, age 2: Mapmaking
            case "20D1":
                if (self::getAuxiliaryValue() == -1) { // If this variable has not been set before
                    self::setAuxiliaryValue(0);
                }
                $step_max = 1;
                break;
            
            case "20N1":
                if (self::getAuxiliaryValue() == 1) { // "If any card was transfered due to the demand"
                    self::executeDraw($player_id, 1, 'score'); // "Draw and score a 1"
                }
                break;
                
            // id 21, age 2: Canal building         
            case "21N1":
                if (!$this->innovationGameState->usingFourthEditionRules() && self::countCardsInLocation($player_id, 'score') == 0 && self::countCardsInLocation($player_id, 'hand') == 0) {
                    self::notifyPlayer($player_id, 'log', clienttranslate('${You} have no cards in your hand or score pile to exchange.'), array('You' => 'You'));
                    self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} has no cards in their hand or score pile to exchange.'), array('player_name' => self::renderPlayerName($player_id)));
                } else {
                    $step_max = 1;
                }
                break;
                
            // id 23, age 2: Monotheism        
            case "23D1":
                $step_max = 1;
                break;
            
            case "23N1":
                self::executeDrawAndTuck($player_id, 1); // "Draw and tuck a 1"
                break;
                
            // id 24, age 2: Philosophy        
            case "24N1":
                $step_max = 1;
                break;
            
            case "24N2":
                $step_max = 1;
                break;
                
            // id 25, age 3: Alchemy        
            case "25N1":
                $number_of_towers = self::getPlayerSingleRessourceCount($player_id, 4);
                self::notifyPlayer($player_id, 'log', clienttranslate('${You} have ${n} ${towers}.'), array('You' => 'You', 'n' => $number_of_towers, 'towers' => $tower));
                self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} has ${n} ${towers}.'), array('player_name' => self::renderPlayerName($player_id), 'n' => $number_of_towers, 'towers' => $tower));
                $any_card_red = false;
                $cards = array();
                for($i=0; $i<self::intDivision($number_of_towers,3); $i++) { // "For every three towers on your board"
                    $card = self::executeDraw($player_id, 4, 'revealed'); // "Draw and reveal a 4"
                    if ($card['color'] == 1) { // This card is red
                        $any_card_red = true;
                    }
                    $cards[] = $card;
                }
                
                if ($any_card_red) { // "If any of the drawn cards are red"
                    self::notifyPlayer($player_id, 'log', clienttranslate('${You} drew a red card.'), array('You' => 'You'));
                    self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} drew a red card.'), array('player_name' => self::renderPlayerName($player_id)));

                    $step_max = 1;
                }
                else { // "Otherwise"
                    self::notifyPlayer($player_id, 'log', clienttranslate('${You} did not draw a red card.'), array('You' => 'You'));
                    self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} did not draw a red card.'), array('player_name' => self::renderPlayerName($player_id)));
                    foreach($cards as $card) {
                        self::transferCardFromTo($card, $player_id, 'hand'); // "Keep them" (ie place them in your hand)
                    }
                }
                break;
                
            case "25N2":
                $step_max = 2;
                break;
                
            // id 26, age 3: Translation        
            case "26N1":
                $step_max = 1;
                break;
                
            case "26N2":
                $eligible = true;
                for($color = 0; $color < 5 ; $color++) {
                    $top_card = self::getTopCardOnBoard($player_id, $color);
                    if ($top_card !== null && !self::hasRessource($top_card, 1)) { // This top card is present, with no crown on it
                        $eligible = false;
                    }
                }
                if ($eligible) { // "If each card on your board has a crown"
                    $achievement = self::getCardInfo(108);
                    if ($achievement['owner'] == 0 && $achievement['location'] == 'achievements') {
                        self::notifyPlayer($player_id, 'log', clienttranslate('Each top card on ${your} board has a ${crown}.'), array('your' => 'your', 'crown' => $crown));
                        self::notifyAllPlayersBut($player_id, 'log', clienttranslate('Each top card on ${player_name} board has a ${crown}.'), array('player_name' => self::renderPlayerName($player_id), 'crown' => $crown));
                        self::transferCardFromTo($achievement, $player_id, 'achievements');  // "Claim the World achievement"
                    } else {
                        self::notifyPlayer($player_id, 'log', clienttranslate('Each top card on ${your} board has a ${crown} but the Empire achievement has already been claimed.'), array('your' => 'your', 'crown' => $crown));
                        self::notifyAllPlayersBut($player_id, 'log', clienttranslate('Each top card on ${player_name} board has a ${crown} but the World achievement has already been claimed.'), array('player_name' => self::renderPlayerName($player_id), 'crown' => $crown));
                    }
                }
                break;
                
            // id 27, age 3: Engineering        
            case "27D1":
                // "I demand you transfer all top cards with a tower from your board to my score pile"
                $no_top_card_with_tower = true;
                for($color = 0; $color < 5 ; $color++) {
                    $top_card = self::getTopCardOnBoard($player_id, $color);
                    if (self::hasRessource($top_card, 4)) { // This top card has a tower on it
                        $no_top_card_with_tower = false;
                        self::transferCardFromTo($top_card, $launcher_id, 'score');
                    }
                }
                if ($no_top_card_with_tower) {
                    self::notifyPlayer($player_id, 'log', clienttranslate('${You} have no top card with a ${tower} on your board.'), array('You' => 'You', 'tower' => $tower));
                    self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} has no top card with a ${tower} on his board.'), array('player_name' => self::renderPlayerName($player_id), 'tower' => $tower));
                }
                break;
                
            case "27N1":
                $step_max = 1;
                break;
                
            // id 28, age 3: Optics        
            case "28N1":
                $card = self::executeDrawAndMeld($player_id, 3); // "Draw and meld a 3"
                if (self::hasRessource($card, 1)) { // "If it has a crown"
                    self::notifyGeneralInfo(clienttranslate('It has a ${crown}.'), array('crown' => $crown));
                    self::executeDraw($player_id, 4, 'score'); // "Draw and score a 4"
                } else { // "Otherwise"
                    self::notifyGeneralInfo(clienttranslate('It does not have a ${crown}.'), array('crown' => $crown));
                    if (empty(self::getActiveOpponentsWithFewerPoints($player_id))) {
                        self::notifyPlayer($player_id, 'log', clienttranslate('There is no opponent who has fewer points than ${you}.'), array('you' => 'you'));
                        self::notifyAllPlayersBut($player_id, 'log', clienttranslate('There is no opponent who has fewer points than ${player_name}.'), array('player_name' => self::renderPlayerName($player_id)));
                    } else {
                        $step_max = 2;
                    }
                }
                break;
                
            // id 29, age 3: Compass
            case "29D1":
                $step_max = 2;
                break;
                
            // id 30, age 3: Paper        
            case "30N1_3E":
            case "30N1_4E":
                $step_max = 1;
                break;
                
            case "30N2_3E":
                // "Draw a 4 for every color you have splayed left"
                $number_of_colors_splayed_left = 0;
                for ($color = 0; $color < 5 ; $color++) {
                    if (self::getCurrentSplayDirection($player_id, $color) == Directions::LEFT) {
                        $number_of_colors_splayed_left++;
                    }
                }
                if ($number_of_colors_splayed_left == 1) {
                    self::notifyPlayer($player_id, 'log', clienttranslate('${You} have ${n} color splayed left.'), array('i18n' => array('n'), 'You' => 'You', 'n' => $number_of_colors_splayed_left));
                    self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} has ${n} color splayed left.'), array('i18n' => array('n'), 'player_name' => self::renderPlayerName($player_id), 'n' => $number_of_colors_splayed_left));
                } else {
                    self::notifyPlayer($player_id, 'log', clienttranslate('${You} have ${n} colors splayed left.'), array('i18n' => array('n'), 'You' => 'You', 'n' => $number_of_colors_splayed_left));
                    self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} has ${n} colors splayed left.'), array('i18n' => array('n'), 'player_name' => self::renderPlayerName($player_id), 'n' => $number_of_colors_splayed_left));
                }
                for ($i = 0; $i < $number_of_colors_splayed_left; $i++) {
                    self::executeDraw($player_id, 4);
                }
                break;
            
            case "30N2_4E":
                $step_max = 1;
                break;
                
            // id 31, age 3: Machinery        
            case "31D1_3E":
            case "31D1_4E":
                // "Exchange all the cards in your hand with all the highest cards in my hand"
                
                // Get cards in hand
                $ids_of_cards_in_player_hand = self::getIdsOfCardsInLocation($player_id, 'hand');
                $ids_of_highest_cards_in_launcher_hand = self::getIdsOfHighestCardsInLocation($launcher_id, 'hand');
                
                // Make the transfers
                foreach($ids_of_cards_in_player_hand as $id) {
                    $card = self::getCardInfo($id);
                    self::transferCardFromTo($card, $launcher_id, 'hand');
                }
                foreach($ids_of_highest_cards_in_launcher_hand as $id) {
                    $card = self::getCardInfo($id);
                    self::transferCardFromTo($card, $player_id, 'hand');
                }
                break;
                
            case "31N1_3E":
                $has_card_with_tower = false;
                foreach (self::getCardsInLocation($player_id, 'hand') as $card) {
                    if (self::hasRessource($card, 4 /* tower */)) {
                        $has_card_with_tower = true;
                        break;
                    }
                }
                if (!$has_card_with_tower) {
                    $step = 2; // Skip first interaction
                    self::revealHand($player_id);
                    self::notifyPlayer($player_id, 'log', clienttranslate('${You} have no cards with a ${tower} in your hand.'), array('You' => 'You', 'tower' => $tower));
                    self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} has no cards with a ${tower} in his hand.'), array('player_name' => self::renderPlayerName($player_id), 'tower' => $tower));

                }
                $step_max = 2;
                break;

            case "31N1_4E":
                $has_card_with_tower = false;
                foreach (self::getCardsInLocation($player_id, 'hand') as $card) {
                    if (self::hasRessource($card, 4 /* tower */)) {
                        $has_card_with_tower = true;
                        break;
                    }
                }
                if ($has_card_with_tower) {
                    $step_max = 1;
                } else {
                    self::revealHand($player_id);
                    self::notifyPlayer($player_id, 'log', clienttranslate('${You} have no cards with a ${tower} in your hand.'), array('You' => 'You', 'tower' => $tower));
                    self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} has no cards with a ${tower} in his hand.'), array('player_name' => self::renderPlayerName($player_id), 'tower' => $tower));
                }
                break;

            case "31N2_4E":
                $step_max = 1;
                break;
                
            // id 32, age 3: Medicine        
            case "32D1":
                $step_max = 2;
                break;

            case "32N1":
                $step_max = 1; // 4th edition and beyond only
                break;
                
            // id 33, age 3: Education        
            case "33N1":
                $step_max = 1;
                break;
                
            // id 34, age 3: Feudalism        
            case "34D1":
                $has_card_with_tower = false;
                foreach (self::getCardsInLocation($player_id, 'hand') as $card) {
                    if (self::hasRessource($card, 4 /* tower */)) {
                        $has_card_with_tower = true;
                        break;
                    }
                }
                if ($has_card_with_tower) {
                    $step_max = 1;
                } else {
                    self::revealHand($player_id);
                    self::notifyPlayer($player_id, 'log', clienttranslate('${You} have no cards with a ${tower} in your hand.'), array('You' => 'You', 'tower' => $tower));
                    self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} has no cards with a ${tower} in his hand.'), array('player_name' => self::renderPlayerName($player_id), 'tower' => $tower));
                }
                break;
                
            case "34N1":
                $step_max = 1;
                break;
                
            // id 35, age 4: Experimentation        
            case "35N1":
                 // "Draw and meld a 5"
                self::executeDrawAndMeld($player_id, 5);
                break;
                
            // id 36, age 4: Printing press        
            case "36N1":
                $step_max = 1;
                break;
                
            case "36N2":
                $step_max = 1;
                break;
                
            // id 37, age 4: Colonialism
            case "37N1":
                do {
                    // "Draw and tuck a 3"
                    $card = self::executeDrawAndTuck($player_id, 3);
                    // "If it is green, junk all cards in the 5 deck"
                    if ($card['color'] == 2 && $this->innovationGameState->usingFourthEditionRules()) {
                        self::junkBaseDeck(5);
                    }
                } while (self::hasRessource($card, 1 /* crown */)); // "If it has a crown, repeat this effect"
                break;
            
            // id 38, age 4: Gunpowder
            case "38D1":
                if (self::getAuxiliaryValue() == -1) { // If this variable has not been set before
                    self::setAuxiliaryValue(0);
                }
                $step_max = 1;
                break;
                
            case "38N1":
                if (self::getAuxiliaryValue() == 1) { // "If any card was transfered due to the demand"
                    self::executeDraw($player_id, 2, 'score'); // "Draw and score a 2"
                }
                break;
                
            // id 39, age 4: Invention
            case "39N1":
                $step_max = 1;
                break;
                
            case "39N2":
                $eligible = true;
                for($color = 0; $color < 5 ; $color++) {
                    if (self::getCurrentSplayDirection($player_id, $color) == 0) { // This color is missing or unsplayed
                        $eligible = false;
                    };
                }
                if ($eligible) { // "If you have colors splayed, each in any direction"
                    $achievement = self::getCardInfo(107);
                    if ($achievement['owner'] == 0 && $achievement['location'] == 'achievements') {
                        self::notifyPlayer($player_id, 'log', clienttranslate('${You} have all your five colors splayed.'), array('You' => 'You'));
                        self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} has all his five colors splayed.'), array('player_name' => self::renderPlayerName($player_id)));
                        self::transferCardFromTo($achievement, $player_id, 'achievements');  // "Claim the Wonder achievement"
                    } else {
                        self::notifyPlayer($player_id, 'log', clienttranslate('${You} have all your five colors splayed but the Wonder achievement has already been claimed.'), array('You' => 'You'));
                        self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} has all his five colors splayed but the Wonder achievement has already been claimed.'), array('player_name' => self::renderPlayerName($player_id)));
                    }
                }
                break;
                
            // id 40, age 4: Navigation
            case "40D1":
                $step_max = 1;
                break;
            
            // id 41, age 4: Anatomy
            case "41D1":
                $step_max = 1;
                break;
                
            // id 42, age 4: Perspective
            case "42N1":
                $step_max = 1;
                break;
                
            // id 43, age 4: Enterprise
            case "43D1":
                $step_max = 1;
                break;
            
            case "43N1":
                $step_max = 1;
                break;
                
            // id 44, age 4: Reformation
            case "44N1":
                $step_max = 1;
                break;
            
            case "44N2":
                $step_max = 1;
                break;
                
            // id 45, age 5: Chemistry
            case "45N1":
                $step_max = 1;
                break;
            
            case "45N2":
                // "Draw and score a card of value one higher than the highest top card on your board"
                self::executeDraw($player_id, self::getMaxAgeOnBoardTopCards($player_id) + 1, 'score');
                $step_max = 1;
                break;
                
            // id 46, age 5: Physics
            case "46N1":
                $cards = array();
                $colors = array();
                $same_color = false;
                for($i=0; $i<3; $i++) { // "Three times"
                    $card = self::executeDraw($player_id, 6, 'revealed'); // "Draw and reveal a 6"
                    if (in_array($card['color'], $colors)) { // This card has the same color than one that has already been drawn
                        $same_color = true;
                    }
                    else {
                        $colors[] = $card['color'];
                    }
                    $cards[] = $card;
                }
                
                if ($same_color) { // "If two or more cards are the same color"
                    $step_max = 1;
                    self::notifyPlayer($player_id, 'log', clienttranslate('${You} drew two cards of the same color.'), array('You' => 'You'));
                    self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} drew two cards of the same color.'), array('player_name' => self::renderPlayerName($player_id)));
                }
                else { // "Otherwise"
                    self::notifyPlayer($player_id, 'log', clienttranslate('All the cards ${you} drew have different colors.'), array('you' => 'you'));
                    self::notifyAllPlayersBut($player_id, 'log', clienttranslate('All the cards ${player_name} drew have different colors.'), array('player_name' => self::renderPlayerName($player_id)));
                    foreach($cards as $card) {
                        self::transferCardFromTo($card, $player_id, 'hand'); // "Keep them" (ie place them in your hand)
                    }
                }
                break;
            
            // id 47, age 5: Coal
            case "47N1":
                self::executeDrawAndTuck($player_id, 5); // "Draw and tuck a 5"
                break;

            case "47N2":
                $step_max = 1;
                break;
                
            case "47N3":
                $step_max = 1;
                break;
                
            // id 48, age 5: The pirate code
            case "48D1":
                if (self::getAuxiliaryValue() == -1) { // If this variable has not been set before
                    self::setAuxiliaryValue(0);
                }
                $step_max = 1;
                break;

            case "48N1":
                if (self::getAuxiliaryValue() == 1) { // "If any card was transfered due to the demand"
                    $step_max = 1;
                }
                break;
                
            // id 49, age 5: Banking
            case "49D1":
                $step_max = 1;
                break;

            case "49N1":
                $step_max = 1;
                break;
                
            // id 50, age 5: Measurement
            case "50N1":
                $step_max = 1;
                break;
            
            // id 51, age 5: Statistics
            case "51D1":
                if ($this->innovationGameState->usingFirstEditionRules()) {
                    $step_max = 1;
                } else {
                    // Get highest cards in score
                    $ids_of_highest_cards_in_score = self::getIdsOfHighestCardsInLocation($player_id, 'score');

                    // Make the transfers
                    foreach($ids_of_highest_cards_in_score as $id) {
                        $card = self::getCardInfo($id);
                        self::transferCardFromTo($card, $player_id, 'hand'); // "Transfer all the highest cards in your score pile to your hand"
                    }
                }
                break;

            case "51N1":
                $step_max = 1;
                break;
            
            // id 52, age 5: Steam engine
            case "52N1":
                self::setIndexedAuxiliaryValue($player_id, 0);
                self::executeDrawAndTuck($player_id, 4); // "Draw and tuck two 4s"
                self::executeDrawAndTuck($player_id, 4); //
                $card = self::getBottomCardOnBoard($player_id, Colors::YELLOW);
                if ($card !== null) {
                    self::scoreCard($card, $player_id);
                    // "If it is Steam Engine, junk all cards in the 6 deck."
                    if ($card['id'] == 52 && $this->innovationGameState->usingFourthEditionRules()) {
                        self::junkBaseDeck(6);
                    }
                }
                break;
                
            // id 53, age 5: Astronomy
            case "53N1":
                while (true) {
                    $card = self::executeDraw($player_id, 6, 'revealed'); // "Draw and reveal a 6"
                    self::notifyGeneralInfo(clienttranslate('This card is ${color}.'), array('i18n' => array('color'), 'color' => Colors::render($card['color'])));
                    if ($card['color'] != Colors::BLUE && $card['color'] != Colors::GREEN) {
                        break; // "Otherwise"
                    };
                    // "If the card is green or blue"
                    self::meldCard($card, $player_id); // "Meld it"
                }
                self::transferCardFromTo($card, $player_id, 'hand'); // Keep it
                break;
            
            case "53N2":
                $eligible = true;
                for($color = 0; $color < 4 /* purple is not tested */ ; $color++) {
                    $top_card = self::getTopCardOnBoard($player_id, $color);
                    if ($top_card !== null && $top_card['age'] < 6) { // This top card is value 5 or fewer
                        $eligible = false;
                    }
                }
                if ($eligible) { // "If all your non-purple top cards on your board are value 6 or higher"
                    $achievement = self::getCardInfo(109);
                    if ($achievement['owner'] == 0 && $achievement['location'] == 'achievements') {
                        self::notifyPlayer($player_id, 'log', clienttranslate('All non-purple top cards on ${your} board are value ${age_6} or higher.'), array('your' => 'your', 'age_6' => self::getAgeSquare(6)));
                        self::notifyAllPlayersBut($player_id, 'log', clienttranslate('All non-purple top cards on ${player_name}\'s board are value ${age_6} or higher.'), array('player_name' => self::renderPlayerName($player_id), 'age_6' => self::getAgeSquare(6)));
                        self::transferCardFromTo($achievement, $player_id, 'achievements');  // "Claim the Universe achievement"
                    } else {
                        self::notifyPlayer($player_id, 'log', clienttranslate('All non-purple top cards on ${your} board are value ${age_6} or higher but the Universe achievement has already been claimed.'), array('your' => 'your', 'age_6' => self::getAgeSquare(6)));
                        self::notifyAllPlayersBut($player_id, 'log', clienttranslate('All non-purple top cards on ${player_name}\'s board are value ${age_6} or higher but the Universe achievement has already been claimed.'), array('player_name' => self::renderPlayerName($player_id), 'age_6' => self::getAgeSquare(6)));
                    }
                }
                break;
                
            // id 54, age 5: Societies
            case "54D1":
                if ($this->innovationGameState->usingFirstEditionRules()) {
                    $colors = array(0,1,2,3); // All but purple
                } else {
                    $colors = array();
                    // Determine colors which top cards with a lightbulb of the player have a value higher than the tops cards of the launcher
                    for ($color = 0; $color < 5; $color++) {
                        $player_top_card = self::getTopCardOnBoard($player_id, $color);
                        if (!self::hasRessource($player_top_card, 3 /* lightbulb */)) {
                            continue;
                        }
                        $launcher_top_card = self::getTopCardOnBoard($launcher_id, $color);
                        if ($launcher_top_card === null /* => Value 0, so the color is selectable */ || $player_top_card['faceup_age'] > $launcher_top_card['faceup_age']) {
                            $colors[] = $color; // This color is selectable
                        }
                    }
                }
                self::setAuxiliaryValueFromArray($colors);
                $step_max = 1;
                break;
            
            // id 55, age 6: Atomic theory
            case "55N1":
                $step_max = 1;
                break;
            
            case "55N2":
                // "Draw and meld a 7"
                self::executeDrawAndMeld($player_id, 7);
                break;
            
            // id 56, age 6: Encyclopedia
            case "56N1":
                $step_max = 1;
                break;

            case "56N2":
                $step_max = 1; // 4th edition and beyond only
                break;
                
            // id 57, age 6: Industrialisation
            case "57N1":
                if ($this->innovationGameState->usingFirstEditionRules()) {
                    // "For every two factories on your board"
                    $number_of_factories = self::getPlayerSingleRessourceCount($player_id, 5 /* factory */);
                    self::notifyPlayer($player_id, 'log', clienttranslate('${You} have ${n} ${factories}.'), array('You' => 'You', 'n' => $number_of_factories, 'factories' => $factory));
                    self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} has ${n} ${factories}.'), array('player_name' => self::renderPlayerName($player_id), 'n' => $number_of_factories, 'factories' => $factory));
                    $number = self::intDivision($number_of_factories,2);
                } else {
                    // "For each color of your board that have one factory or more"
                    $number = 0;
                    for($color=0; $color<5; $color++) {
                        if (self::boardPileHasRessource($player_id, $color, 5 /* factory */)) { // There is at least one visible factory in that color
                            $number++;
                        }
                    }
                    if ($number <= 1) {
                        self::notifyPlayer($player_id, 'log', clienttranslate('${You} have ${n} color with one or more visible ${factories}.'), array('i18n' => array('n'), 'You' => 'You', 'n' => self::renderNumber($number), 'factories' => $factory));
                        self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} has ${n} color with one or more ${factories}.'), array('i18n' => array('n'), 'player_name' => self::renderPlayerName($player_id), 'n' => self::renderNumber($number), 'factories' => $factory));
                    }
                    else { // $number > 1
                        self::notifyPlayer($player_id, 'log', clienttranslate('${You} have ${n} colors with one or more visible ${factories}.'), array('i18n' => array('n'), 'You' => 'You', 'n' => self::renderNumber($number), 'factories' => $factory));
                        self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} has ${n} colors with one or more ${factories}.'), array('i18n' => array('n'), 'player_name' => self::renderPlayerName($player_id), 'n' => self::renderNumber($number), 'factories' => $factory));
                    }
                }

                $eight_or_ten_tucked = false;
                for ($i = 0; $i < $number; $i++) {
                    $card = self::executeDrawAndTuck($player_id, 6); // "Draw and tuck a 6"
                    if ($card['age'] == 8 || $card['age'] == 10) {
                        $eight_or_ten_tucked = true;
                    }
                }

                // "If you tuck an 8 or 10, return Industrialization if it is a top card on any board." (4th edition only)
                $industrialization_card = self::getCardInfo(57);
                if ($this->innovationGameState->usingFourthEditionRules()
                        && self::isTopBoardCard($industrialization_card)
                        && $eight_or_ten_tucked) {
                    self::returnCard($industrialization_card);
                }
                break;
                
            case "57N2":
                $step_max = 1;
                break;
               
            // id 58, age 6: Machine tools
            case "58N1":
                self::executeDraw($player_id, self::getMaxAgeInScore($player_id), 'score'); // "Draw and score a card of value equal to the highest card in your score pile"
                break;
            
            // id 59, age 6: Classification
            case "59N1":
                $step_max = 1;
                break;
                
            // id 60, age 6: Metric system
            case "60N1":
                if (self::getCurrentSplayDirection($player_id, Colors::GREEN) == Directions::RIGHT) { // "If your green cards are splayed right"
                    $step_max = 1;
                }
                break;
            
            case "60N2":
                $step_max = 1;
                break;
            
            // id 61, age 6: Canning
            case "61N1":
                $step_max = 1;
                break;
            
            case "61N2":
                $step_max = 1;
                break;
            
            // id 62, age 6: Vaccination
            case "62D1":
                if (self::getAuxiliaryValue() == -1) { // If this variable has not been set before
                    self::setAuxiliaryValue(0);
                }
                $step_max = 1;
                break;
            
            case "62N1":
                // "If any card was returned as a result of the demand, draw and meld a 7."
                if (self::getAuxiliaryValue() == 1) {
                    self::executeDrawAndMeld($player_id, 7);
                }
                break;
            
            // id 63, age 6: Democracy          
            case "63N1":
                $step_max = 1;
                break;
            
            // id 64, age 6: Emancipation
            case "64D1":
                $step_max = 1;
                break;
            
            case "64N1":
                $step_max = 1;
                break;
            
            // id 66, age 7: Publications
            case "66N1_3E":
                // Make sure there's at least one pile which can be rearranged
                $number_of_cards_on_board = self::countCardsInLocationKeyedByColor($player_id, 'board');
                for ($color = 0; $color < 5; $color++) {
                    if ($number_of_cards_on_board[$color] > 1) {
                        $step_max = 1;
                        break;
                    }
                }
                break;
            
            case "66N2_3E":
            case "66N1_4E":
                $step_max = 1;
                break;

            case "66N2_4E":
                $step_max = 1;
                break;

            // id 67, age 7: Combustion
            case "67D1":
                if ($this->innovationGameState->usingFirstEditionRules()) {
                    $number = 2;
                } else {
                    $number_of_crowns = self::getPlayerSingleRessourceCount($launcher_id, 1 /* crown */);
                    self::notifyPlayer($launcher_id, 'log', clienttranslate('${You} have ${n} ${crowns}.'), array('You' => 'You', 'n' => $number_of_crowns, 'crowns' => $crown));
                    self::notifyAllPlayersBut($launcher_id, 'log', clienttranslate('${player_name} has ${n} ${crowns}.'), array('player_name' => self::renderPlayerName($player_id), 'n' => $number_of_crowns, 'crowns' => $crown));
                    $number = self::intDivision($number_of_crowns, 4);
                    if ($number == 0) {
                        self::notifyGeneralInfo(clienttranslate('No card has to be transfered.'));
                        break;
                    }
                }
                self::setAuxiliaryValue($number);
                $step_max = 1;
                break;
                
            case "67N1":
                if (!$this->innovationGameState->usingFirstEditionRules()) {
                    $bottom_red_card = self::getBottomCardOnBoard($player_id, Colors::RED);
                    if ($bottom_red_card !== null) {
                        self::returnCard($bottom_red_card); // "Return your bottom red card"
                    }
                }
                break;
            
            // id 68, age 7: Explosives
            case "68D1":

                // Automate taking as many highest cards as possible
                $num_cards_in_hand = self::countCardsInLocation($player_id, 'hand');
                $cards_by_age = self::getCardsInLocationKeyedByAge($player_id, 'hand');
                $num_cards_left_to_transfer = 3;
                for ($age = 11; $age >= 1; $age--) {
                    if (count($cards_by_age[$age]) <= $num_cards_left_to_transfer) {
                        foreach ($cards_by_age[$age] as $card) {
                            self::transferCardFromTo($card, $launcher_id, 'hand');
                            $num_cards_left_to_transfer--;
                            $num_cards_in_hand--;
                        }
                    } else {
                        break;
                    }
                }

                $num_cards_which_will_be_transferred = min($num_cards_left_to_transfer, $num_cards_in_hand);
                if ($num_cards_which_will_be_transferred > 0) {
                    // TODO(LATER): Remove the use of the auxilary value.
                    // Flag to indicate if the player has transfered a card or not
                    if ($num_cards_left_to_transfer == 3) {
                        self::setAuxiliaryValue(0);
                    } else {
                        self::setAuxiliaryValue(1);
                    }
                    $step = 4 - $num_cards_which_will_be_transferred;
                    $step_max = 3;
                
                // "If you transferred any, and then have no cards in hand"
                } else if ($num_cards_left_to_transfer < 3 && $num_cards_in_hand == 0) {
                    self::executeDraw($player_id, 7); // "Draw a 7"
                }
                break;
            
            // id 69, age 7: Bicycle
            case "69N1":
                if (self::countCardsInLocation($player_id, 'hand') > 0 || self::countCardsInLocation($player_id, 'score') > 0) {
                    $step_max = 1;
                } else {
                    self::notifyPlayer($player_id, 'log', clienttranslate('${You} have no cards to exchange.'), array('You' => 'You'));
                    self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} has no cards to exchange.'), array('player_name' => self::renderPlayerName($player_id)));
                }
                break;
            
            // id 70, age 7: Electricity
            case "70N1":
                $step_max = 1;
                break;
                
            // id 71, age 7: Refrigeration
            case "71D1":
                if (self::countCardsInLocation($player_id, 'hand') > 1) {
                    $step_max = 1;
                }
                break;
                
            case "71N1":
                $step_max = 1;
                break;
                
            // id 73, age 7: Lighting        
            case "73N1":
                self::setAuxiliaryValueFromArray(array()); // Flag to indicate what ages have been tucked
                $step_max = 1;
                break;
            
            // id 74, age 7: Railroad        
            case "74N1_3E":
            case "74N1_4E":
                $step_max = 1;
                break;
            
            case "74N2_3E":
                $step_max = 1;
                break;

            case "74N2_4E":
                // "Draw three 6"
                self::executeDraw($player_id, 6);
                self::executeDraw($player_id, 6);
                self::executeDraw($player_id, 6);
                break;

            case "74N3_4E":
                $step_max = 1;
                break;
                
            // id 75, age 8: Quantum theory        
            case "75N1":
                $step_max = 1;
                break;
                
            // id 76, age 8: Rocketry       
            case "76N1":
                $step_max = 1;
                break;
            
            // id 77, age 8: Flight
            case "77N1":
                if (self::getCurrentSplayDirection($player_id, Colors::RED) == 3 /* up */) { // "If your red cards are splayed up"
                    $step_max = 1;
                }
                break;
            
            case "77N2":
                $step_max = 1;
                break;
                
            // id 78, age 8: Mobility        
            case "78D1":
                // "I demand you transfer the two highest non-red top cards without a factory from your board to my score pile!"
                // NOTE: This code is only here in order to add automation to the situation where there is no choice for which two
                // cards need to be transferred. Generic automation is not possible here because we must implement the card as two
                // separate interactions instead of a single interaction which returns two cards.
                $top_cards = self::getTopCardsOnBoard($player_id);
                $selectable_cards = array();
                for ($age = 11; $age >= 1; $age--) {
                    foreach ($top_cards as $top_card) {
                        if ($top_card['faceup_age'] == $age && $top_card['color'] != 1 && !self::hasRessource($top_card, 5)) {
                            $selectable_cards[] = $top_card;
                        }
                    }
                    if (count($selectable_cards) == 2) {
                        foreach ($selectable_cards as $card) {
                            self::transferCardFromTo($card, $launcher_id, 'score');
                        }
                        // "If you transferred any cards, draw an 8"
                        self::executeDraw($player_id, 8);
                        break 2; // Exit the for loop and the switch
                    } else if (count($selectable_cards) > 2) {
                        break;
                    }
                }

                // Proceed without automation
                self::setAuxiliaryValueFromArray(array(0,2,3,4)); // Flag to indicate the colors the player can still choose (not red at the start)
                $step_max = 2;
                break;
                
            // id 79, age 8: Corporations        
            case "79D1":
                $step_max = 1;
                break;
                
            case "79N1":
                // "Draw and meld an 8"
                self::executeDrawAndMeld($player_id, 8);
                break;
                
            // id 80, age 8: Mass media 
            case "80N1":
                $step_max = 1;
                break;
                
            case "80N2":
                $step_max = 1;
                break;

            // id 81, age 8: Antibiotics
            case "81N1":
                self::setAuxiliaryValueFromArray(array()); // Flag to indicate what ages have been returned
                $step_max = 1;
                break;

            // id 82, age 8: Skyscrapers
            case "82D1":
                $step_max = 1;
                break;
            
            // id 83, age 8: Empiricism     
            case "83N1":
                $step_max = 1;
                break;
                
            case "83N2":
                if (self::getPlayerSingleRessourceCount($player_id, 3 /* lightbulb */) >= 20) { // "If you have twenty or more lightbulbs on your board"
                    self::notifyPlayer($player_id, 'log', clienttranslate('${You} have at least twenty ${lightbulbs}.'), array('You' => 'You', 'lightbulbs' => $lightbulb));
                    self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} has at least twenty ${lightbulbs}.'), array('player_name' => self::renderPlayerName($player_id), 'lightbulbs' => $lightbulb));
                    $this->innovationGameState->set('winner_by_dogma', $player_id); // "You win"
                    self::trace('EOG bubbled from self::stPlayerInvolvedTurn Empiricism');
                    throw new EndOfGame();                
                }
                break;
            
            // id 84, age 8: Socialism     
            case "84N1_3E":
                self::setAuxiliaryValue(0); // Flag to indicate if one purple card has been tucked or not
                $step_max = 1;
                break;

            case "84N1_4E":
                $step_max = 1;
                break;

            case "84N2_4E":
                $step_max = 1;
                break;
                
            // id 85, age 9: Computers     
            case "85N1":
                $step_max = 1;
                break;
                
            case "85N2":
                // "Draw and meld a 10"
                $card = self::executeDrawAndMeld($player_id, 10);
                self::selfExecute($card); // "Execute each of its non-demand dogma effects"
                break;
            
            // id 86, age 9: Genetics     
            case "86N1":
                if ($this->innovationGameState->usingFourthEditionRules()) {
                    // "Draw and meld a 11"
                    $card = self::executeDrawAndMeld($player_id, 11);
                } else {
                    // "Draw and meld a 10"
                    $card = self::executeDrawAndMeld($player_id, 10);
                }
                $board = self::getCardsInLocationKeyedByColor($player_id, 'board');
                $pile = $board[$card['color']];
                for($p=0; $p < count($pile)-1; $p++) { // "For each card beneath it"
                    $card = self::getCardInfo($pile[$p]['id']);
                    self::scoreCard($card, $player_id); // "Score that card"
                }
                break;
            
            // id 87, age 9: Composites     
            case "87D1":
                $step_max = 2;
                if (self::countCardsInLocation($player_id, 'hand') <= 1) {
                    $step = 2; // --> (All but one card when there is 0 or 1 card means that nothing is to be done) Jump directly to step 2
                }
                break;
            
            // id 88, age 9: Fission
            case "88D1":
                $card = self::executeDraw($player_id, 10, 'revealed'); // "Draw a 10"
                if ($card['color'] == Colors::RED) { // "If it is red"
                    self::notifyGeneralInfo(clienttranslate('This card is ${color}.'), array('i18n' => array('color'), 'color' => Colors::render($card['color'])));
                    // TODO(4E): These need to be junked instead.
                    self::removeAllHandsBoardsAndScores(); // "Remove all hands, boards and score piles from the game"
                    // TODO(4E): Create new bulk notification for 4th edition.
                    self::notifyAll('removedHandsBoardsAndScores', clienttranslate('All hands, boards and score piles are removed from the game. Achievements are kept.'), array());

                    if ($this->innovationGameState->usingFourthEditionRules()) {
                        // "junk each player's non-achievement cards, and the dogma action is complete!"
                        // The above action already removes the hands, boards, and score piles.
                        // In fourth edition, safe (unseen, display (artifacts) and forecast (echoes) needs
                        // to be removed as well.
                        foreach (self::getAllPlayerIds() as $player) {
                            foreach (self::getCardsInLocation($player, 'display') as $display_card) {
                                self::junkCard($display_card);
                            }
                            foreach (self::getCardsInLocation($player, 'forecast') as $forecast_card) {
                                self::junkCard($forecast_card);
                            }
                            foreach (self::getCardsInLocation($player, 'safe') as $forecast_card) {
                                self::junkCard($forecast_card);
                            }
                        }
                    }
                    
                    // Stats
                    self::setStat(true, 'fission_triggered');
                    
                    // "If this occurs, the dogma action is complete"
                    // (Set the flags as if the launcher had completed the non-demand dogma effect)
                    self::DbQuery(
                        self::format("
                            UPDATE
                                nested_card_execution
                            SET
                                current_player_id = {player_id},
                                current_effect_type = 1,
                                current_effect_number = 2
                            WHERE
                                nesting_index = {nesting_index}",
                            array('player_id' => $launcher_id, 'nesting_index' => $this->innovationGameState->get('current_nesting_index')))
                    );

                } else {
                    self::notifyGeneralInfo(clienttranslate('This card is not red.'));
                    // (Implicit) "Place it into your hand"
                    self::transferCardFromTo($card, $player_id, 'hand');
                }
                break;
                
            case "88N1":
                $step_max = 1;
                break;

            case "88N2":
                // NOTE: This is only in 4th edition and beyond
                self::executeDraw($player_id, 10); // "Draw a 10"
                break;

            // id 89, age 9: Collaboration
            case "89D1":
                self::executeDraw($player_id, 9, 'revealed'); // "Draw two 9 and reveal them"
                self::executeDraw($player_id, 9, 'revealed'); //
                $step_max = 1;
                break;
                
            case "89N1":
                $number_of_cards_on_board = self::countCardsInLocationKeyedByColor($player_id, 'board');
                $number_of_green_cards = $number_of_cards_on_board[2];
                if ($number_of_green_cards >= 10) { // "If you have ten or more green cards on your board"
                    self::notifyPlayer($player_id, 'log', clienttranslate('${You} have at least ten green cards.'), array('You' => 'You'));
                    self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} has at least ten green cards.'), array('player_name' => self::renderPlayerName($player_id)));
                    $this->innovationGameState->set('winner_by_dogma', $player_id); // "You win"
                    self::trace('EOG bubbled from self::stPlayerInvolvedTurn Collaboration');
                    throw new EndOfGame();                
                }
                break;
            
            // id 90, age 9: Satellites
            case "90N1_3E":
                $step_max = 1;
                break;

            case "90N1_4E":
                $step_max = 2;
                break;

            case "90N2_3E":
                $step_max = 1;
                break;

            case "90N2_4E":
                // "Draw three 8"
                self::executeDraw($player_id, 8);
                self::executeDraw($player_id, 8);
                self::executeDraw($player_id, 8);
                break;
                
            case "90N3_3E":
            case "90N3_4E":
                $step_max = 1;
                break;

            // id 91, age 9: Ecology
            case "91N1":
                $step_max = 1;
                break;

            case "91N2":
                // NOTE: This is only in 4th edition and beyond
                $step_max = 1;
                break;

            // id 92, age 9: Suburbia
            case "92N1":
                $step_max = 1;
                break;

            case "92N2":
                // NOTE: This is only in 4th edition and beyond
                $step_max = 1;
                break;
                
            // id 93, age 9: Services
            case "93D1":
                $ids_of_highest_cards_in_score = self::getIdsOfHighestCardsInLocation($player_id, 'score');
                foreach($ids_of_highest_cards_in_score as $id) {
                    $card = self::getCardInfo($id);
                    self::transferCardFromTo($card, $launcher_id, 'hand'); // "Transfer all the highest cards from your score pile to my hand"
                }
            
                if (count($ids_of_highest_cards_in_score) > 0) { // "If you transferred any cards"
                    $step_max = 1;
                }
                break;
              

            // id 94, age 9: Specialization
            case "94N1":
                $step_max = 1;
                break;
            
            case "94N2":
                $step_max = 1;
                break;
                
            // id 95, age 10: Bioengineering
            case "95N1":
                $step_max = 1;
                break;
            
            case "95N2":
                $player_ids = self::getAllActivePlayerIds();
                $max_number_of_leaves = -1;
                $leaves_limit = $this->innovationGameState->usingFourthEditionRules() ? 2 : 3;
                $anyone_under_leaves_limit = false;
                foreach ($player_ids as $player_id) {
                    $number_of_leaves = self::getPlayerSingleRessourceCount($player_id, 2);
                    self::notifyPlayer($player_id, 'log', clienttranslate('${You} have ${n} ${leaves}.'), array('You' => 'You', 'n' => $number_of_leaves, 'leaves' => $leaf));
                    self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} has ${n} ${leaves}.'), array('player_name' => self::renderPlayerName($player_id), 'n' => $number_of_leaves, 'leaves' => $leaf));
                    if (!$anyone_under_leaves_limit && $number_of_leaves < $leaves_limit) {
                        self::notifyGeneralInfo(clienttranslate('That is less than ${n}.'), array('i18n' => array('n'), 'n' => self::renderNumber($leaves_limit)));
                        $anyone_under_leaves_limit = true;
                    }
                    if ($number_of_leaves > $max_number_of_leaves) {
                        $max_number_of_leaves = $number_of_leaves;
                        $owner_of_max_number_of_leaves = $player_id;
                        $tie = false;                        
                    }
                    else if ($number_of_leaves == $max_number_of_leaves && $player_id != self::getPlayerTeammate($owner_of_max_number_of_leaves)) {
                        $tie = true;
                    }
                }
                
                if (!$anyone_under_leaves_limit) {
                    self::notifyGeneralInfo(clienttranslate('Nobody has less than ${n} ${leaves}.'), array('i18n' => array('n'), 'leaves' => $leaf, 'n' => self::renderNumber($leaves_limit)));
                }
                else if ($tie) {
                    self::notifyGeneralInfo(clienttranslate('There is a tie for the most number of ${leaves}. The game continues.'), array('leaves' => $leaf));
                }
                else { // "If any player has less than three leaves, the single player with the most number of leaves"
                    self::notifyPlayer($owner_of_max_number_of_leaves, 'log', clienttranslate('${You} have more ${leaves} than each opponent.'), array('You' => 'You', 'leaves' => $leaf));
                    self::notifyAllPlayersBut($owner_of_max_number_of_leaves, 'log', clienttranslate('${player_name} has more ${leaves} than each opponent.'), array('player_name' => self::renderPlayerName($owner_of_max_number_of_leaves), 'leaves' => $leaf));
                    $this->innovationGameState->set('winner_by_dogma', $owner_of_max_number_of_leaves); // "Wins"
                    self::trace('EOG bubbled from self::stPlayerInvolvedTurn Bioengineering');
                    throw new EndOfGame();
                }
                
                break;

            // id 96, age 10: Software
            case "96N1":
                self::executeDraw($player_id, 10, 'score'); // "Draw and score a 10"
                break;
                
            case "96N2":
                // Draw and meld two 10s, then execute each of the second card's non dogma effects. Do not share them."
                // NOTE: In 4th edition, draw and meld two 9s instead.
                $value = $this->innovationGameState->usingFourthEditionRules() ? 9 : 10;
                self::executeDrawAndMeld($player_id, $value);
                $card = self::executeDrawAndMeld($player_id, $value);
                self::selfExecute($card);
                break;
                
            // id 97, age 10: Miniaturization
            case "97N1":
                $step_max = 1;
                break;
            
            // id 98, age 10: Robotics
            case "98N1":
                $top_green_card = self::getTopCardOnBoard($player_id, Colors::GREEN);
                if ($top_green_card !== null) {
                    self::scoreCard($top_green_card, $player_id); // "Score your top green card"
                }
                $card = self::executeDrawAndMeld($player_id, 10); // "Draw and meld a 10
                if ($this->innovationGameState->getEdition() <= 3 || self::hasRessource($card, Icons::INDUSTRY) || self::hasRessource($card, Icons::EFFICIENCY)) {
                    self::selfExecute($card); // "Execute each its non-demand dogma effects"
                }
                break;
            
            // id 99, age 10: Databases
            case "99D1":
                if (self::countCardsInLocation($player_id, 'score') > 0) { // (Nothing to do if the player has nothing in his score pile)
                    $step_max = 1;
                }
                break;
            
            // id 100, age 10: Self service
            case "100N1":
                if ($this->innovationGameState->usingFourthEditionRules()) {
                    // "If you have at least twice as many achievements as each opponent, you win."
                    $number_of_achievements = self::getPlayerNumberOfAchievements($player_id);
                    $twice_the_achievements = true;
                    foreach (self::getActiveOpponentIds($player_id) as $opponent_id) {
                        if ($number_of_achievements < self::getPlayerNumberOfAchievements($opponent_id) * 2) {
                            $twice_the_achievements = false;
                        }
                    }
                    if ($twice_the_achievements) {
                        self::notifyAllPlayersBut($player_id, "log", clienttranslate('${player_name} has at least twice as many achievements as each opponent.'), array(
                            'player_name' => self::getPlayerNameFromId($player_id)
                        ));
                        self::notifyPlayer($player_id, "log", clienttranslate('${You} have at least twice as many achievements as each opponent.'), array(
                            'You' => 'You'
                        ));
                        $this->innovationGameState->set('winner_by_dogma', $player_id); // "You win"
                        self::trace('EOG bubbled from self::stPlayerInvolvedTurn Self service');
                        throw new EndOfGame();
                    }
                } else {
                    $step_max = 1;
                }
                break;
                
            case "100N2":
                if ($this->innovationGameState->usingFourthEditionRules()) {
                    $step_max = 1;
                } else {
                    $number_of_achievements = self::getPlayerNumberOfAchievements($player_id);
                    $most_achievements = true;
                    foreach (self::getActiveOpponentIds($player_id) as $opponent_id) {
                        if (self::getPlayerNumberOfAchievements($opponent_id) >= $number_of_achievements) {
                            $most_achievements = false;
                        }
                    }
                    if ($most_achievements) { // "If you have more achievements than each other player"
                        if (self::decodeGameType($this->innovationGameState->get('game_type')) == 'individual') {
                            self::notifyAllPlayersBut($player_id, "log", clienttranslate('${player_name} has more achievements than each other player.'), array(
                                'player_name' => self::getPlayerNameFromId($player_id)
                            ));
                            
                            self::notifyPlayer($player_id, "log", clienttranslate('${You} have more achievements than each other player.'), array(
                                'You' => 'You'
                            ));
                        }
                        else { // $this->innovationGameState->get('game_type')) == 'team'
                            $teammate_id = self::getPlayerTeammate($player_id);
                            $winning_team = array($player_id, $teammate_id);
                            self::notifyAllPlayersBut($winning_team, "log", clienttranslate('The other team has more achievements than yours.'), array());
                            
                            self::notifyPlayer($player_id, "log", clienttranslate('Your team has more achievements than the other.'), array());
                            
                            self::notifyPlayer($teammate_id, "log", clienttranslate('Your team has more achievements than the other.'), array());
                        }
                        $this->innovationGameState->set('winner_by_dogma', $player_id); // "You win"
                        self::trace('EOG bubbled from self::stPlayerInvolvedTurn Self service');
                        throw new EndOfGame();
                    }
                }
                break;
                
            // id 101, age 10: Globalization
            case "101D1":
                $step_max = 1;
                break;

            case "101N1":
                if ($this->innovationGameState->usingFourthEditionRules()) {
                    self::executeDrawAndMeld($player_id, 11); // "Draw and meld an 11"
                } else {
                    self::executeDraw($player_id, 6, 'score'); // "Draw and score a 6"
                }
                
                $player_ids = self::getAllActivePlayerIds();
                $nobody_more_leaves_than_factories = true;
                foreach ($player_ids as $player_id) {
                    $number_of_leaves = self::getPlayerSingleRessourceCount($player_id, 2);
                    $number_of_factories = self::getPlayerSingleRessourceCount($player_id, 5);
                    
                    self::notifyPlayer($player_id, 'log', clienttranslate('${You} have ${m} ${leaves} and ${n} ${factories}.'), array('You' => 'You', 'm' => $number_of_leaves, 'leaves' => $leaf, 'n' => $number_of_factories, 'factories' => $factory));
                    self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} has ${m} ${leaves} and ${n} ${factories}.'), array('player_name' => self::renderPlayerName($player_id), 'm' => $number_of_leaves, 'leaves' => $leaf, 'n' => $number_of_factories, 'factories' => $factory));
                    
                    if ($nobody_more_leaves_than_factories && $number_of_leaves > $number_of_factories) {
                        self::notifyGeneralInfo(clienttranslate('That is more ${leaves} than ${factories}'), array('leaves' => $leaf, 'factories' => $factory));
                        $nobody_more_leaves_than_factories = false;
                    }
                }
                
                if ($nobody_more_leaves_than_factories) { // "If no player has more leaves than factories on their board"
                    $teams = array();
                    $scores = array();
                    foreach ($player_ids as $player_id) {
                        $team = self::getPlayerTeam($player_id);
                        $score = self::getPlayerScore($player_id);
                        if (!array_key_exists($team, $teams)) {
                            $teams[$team] = array($player_id);
                            $scores[$team] = $score;
                        }
                        else {
                            $teams[$team][] = $player_id;
                            $scores[$team] += $score;
                        }
                    }
                    
                    $max_score = -1;
                    foreach($scores as $team => $score) {
                        if ($score > $max_score) {
                            $max_score = $score;
                            $team_max = $team;
                            $tie = false;
                        }
                        else if ($score == $max_score) {
                            $tie = true;
                        }
                        
                        // Display the score (or the combined score for the team)
                        if (self::decodeGameType($this->innovationGameState->get('game_type')) == 'individual') {
                            $player_id = $teams[$team][0];
                            if ($score < 2) {
                                $message_for_others = clienttranslate('${player_name} has ${n} point.');
                                $message_for_player = clienttranslate('${You} have ${n} point.');
                            }
                            else {
                                $message_for_others = clienttranslate('${player_name} has ${n} points.');
                                $message_for_player = clienttranslate('${You} have ${n} points.');                                
                            }
                            self::notifyAllPlayersBut($player_id, "log", $message_for_others, array(
                                'player_name' => self::getPlayerNameFromId($player_id),
                                'n' => $score
                            ));
                            
                            self::notifyPlayer($player_id, "log", $message_for_player, array(
                                'You' => 'You',
                                'n' => $score
                            ));
                        }                        
                        else { // $this->innovationGameState->get('game_type') == 'team'
                            $current_team = $teams[$team];
                            $player_id = $current_team[0];
                            $teammate_id = $current_team[1];
                            if ($score < 2) {
                                $message_for_team = clienttranslate('Your team has ${n} point.');
                                $message_for_others = clienttranslate('The other team has ${n} point.');
                            }
                            else {
                                $message_for_team = clienttranslate('Your team has ${n} points.');
                                $message_for_others = clienttranslate('The other team has ${n} points.');                            
                            }
                            self::notifyAllPlayersBut($current_team, "log", $message_for_others, array('n' => $score));
                            
                            self::notifyPlayer($player_id, "log", $message_for_team, array('n' => $score));
                            
                            self::notifyPlayer($teammate_id, "log",$message_for_team, array('n' => $score));    
                        }
                    }
                    
                    if ($tie) {
                        self::notifyGeneralInfo(clienttranslate('There is a tie for the greatest score. The game continues.'));
                    }
                    else {
                        $winning_team = $teams[$team_max];
                        if (self::decodeGameType($this->innovationGameState->get('game_type')) == 'individual') {
                            $player_id = $winning_team[0];
                            self::notifyAllPlayersBut($player_id, "log", clienttranslate('${player_name} has a greater score than each other player.'), array(
                                'player_name' => self::getPlayerNameFromId($player_id)
                            ));
                            
                            self::notifyPlayer($player_id, "log", clienttranslate('${You} have a greater score than each other player.'), array(
                                'You' => 'You'
                            ));
                        }
                        else { // $this->innovationGameState->get('game_type')) == 'team'
                            $player_id = $winning_team[0];
                            $teammate_id = $winning_team[1];
                            self::notifyAllPlayersBut($winning_team, "log", clienttranslate('The other team has a greater score than yours.'), array());
                            
                            self::notifyPlayer($player_id, "log", clienttranslate('Your team has a greater score than the other one.'), array());
                            
                            self::notifyPlayer($teammate_id, "log", clienttranslate('Your team has a greater score than the other one.'), array());
                        }
                        $this->innovationGameState->set('winner_by_dogma', $player_id); // "The single player with the most points wins" (or combined scores for team)
                        self::trace('EOG bubbled from self::stPlayerInvolvedTurn Globalization');
                        throw new EndOfGame();
                    }
                }
                break;
                
            // id 102, age 10: Stem cells
            case "102N1":
                if (self::countCardsInLocation($player_id, 'hand') == 0) {
                    self::notifyPlayer($player_id, 'log', clienttranslate('${You} have no cards in your hand to score.'), array('You' => 'You'));
                    self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} has no cards in their hand to score.'), array('player_name' => self::renderPlayerName($player_id)));
                } else {
                    $step_max = 1;
                }
                break;
            
            case "102N2":
                // NOTE: This is only present in 4th edition and beyond
                self::executeDraw($player_id, 11);
                break;
                
            // id 103, age 10: A. I.
            case "103N1":
                self::executeDraw($player_id, 10, 'score'); // "Draw and score a 10"
                break;

            case "103N2":
                if (self::isTopBoardCard(self::getCardInfo(96)) && self::isTopBoardCard(self::getCardInfo(98))) { // "If Robotics and Software are top cards on any board"
                    self::notifyGeneralInfo(clienttranslate('Robotics and Software are both visible as top cards.'));
                    
                    $min_score = 9999;
                    foreach(self::getAllActivePlayerIds() as $any_player_id) {
                        $score = self::getPlayerScore($any_player_id);
                        if ($score < $min_score) {
                            $min_score = $score;
                            $player_with_min_score = $any_player_id;
                            $tie = false;
                        }
                        else if ($score == $min_score) {
                            $tie = true;
                        }
                        
                        // Display the score (or the combined score for the team)
                        if ($score < 2) {
                            $message_for_others = clienttranslate('${player_name} has ${n} point.');
                            $message_for_player = clienttranslate('${You} have ${n} point.');
                        }
                        else {
                            $message_for_others = clienttranslate('${player_name} has ${n} points.');
                            $message_for_player = clienttranslate('${You} have ${n} points.');
                        }
                        self::notifyAllPlayersBut($any_player_id, "log", $message_for_others, array(
                            'player_name' => self::getPlayerNameFromId($any_player_id),
                            'n' => $score
                        ));
                        
                        self::notifyPlayer($any_player_id, "log", $message_for_player, array(
                            'You' => 'You',
                            'n' => $score
                        ));
                    }
                    if ($tie) {
                        self::notifyGeneralInfo(clienttranslate('There is a tie for the lowest score. The game continues.'));
                    }
                    else {
                        self::notifyAllPlayersBut($player_with_min_score, "log", clienttranslate('${player_name} has the lowest score.'), array(
                            'player_name' => self::getPlayerNameFromId($player_with_min_score)
                        ));
                        
                        self::notifyPlayer($player_with_min_score, "log", clienttranslate('${You} have the lowest score.'), array(
                            'You' => 'You'
                        ));
                        $this->innovationGameState->set('winner_by_dogma', $player_with_min_score); // "The single player with the most points wins" (scores are not combined for teams)
                        self::trace('EOG bubbled from self::stPlayerInvolvedTurn A. I.');
                        throw new EndOfGame();
                    }
                }
                break;

            // id 104, age 10: The internet.
            case "104N1":
                $step_max = 1;
                break;

            case "104N2":
                self::executeDraw($player_id, 10, 'score'); // "Draw and score a 10"
                break;

            case "104N3":
                $number_of_clocks = self::getPlayerSingleRessourceCount($player_id, 6 /* clock */);
                self::notifyPlayer($player_id, 'log', clienttranslate('${You} have ${n} ${clocks}.'), array('You' => 'You', 'n' => $number_of_clocks, 'clocks' => $clock));
                self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} has ${n} ${clocks}.'), array('player_name' => self::renderPlayerName($player_id), 'n' => $number_of_clocks, 'clocks' => $clock));
                for($i=0; $i<self::intDivision($number_of_clocks,2); $i++) { // "For every two clocks on your board"
                    self::executeDrawAndMeld($player_id, 10); // "Draw and meld a 10"
                }
                break;

            // id 136, Artifacts age 3: Charter of Liberties
            case "136N1":
                $step_max = 1;
                break;

            // id 137, Artifacts age 2: Excalibur
            case "137C1":
                // Determine colors where top card has a higher value than the launcher's top card of the same color
                $colors = array();
                for ($color = 0; $color < 5; $color++) {
                    $player_top_card = self::getTopCardOnBoard($player_id, $color);
                    if ($player_top_card === null) {
                        continue;
                    }
                    $launcher_top_card = self::getTopCardOnBoard($launcher_id, $color);
                    if ($launcher_top_card === null || $player_top_card['faceup_age'] > $launcher_top_card['faceup_age']) {
                        $colors[] = $color;
                    }
                }
                self::setAuxiliaryValueFromArray($colors);
                $step_max = 1;
                break;
            
            // id 138, Artifacts age 3: Mjolnir Amulet
            case "138C1":
                $step_max = 1;
                break;

            // id 139, Artifacts age 3: Philosopher's Stone
            case "139N1":
                $step_max = 1;
                break;

            // id 140, Artifacts age 3: Beauvais Cathedral Clock
            case "140N1":
                // "Draw and reveal a 4"
                $card = self::executeDraw($player_id, 4, 'revealed');
                // "Splay right the color matching the drawn card"
                self::splayRight($player_id, $player_id, $card['color']);
                self::transferCardFromTo($card, $player_id, 'hand');
                break;

            // id 141, Artifacts age 3: Moylough Belt Shrine
            case "141C1":
                // "I compel you to reveal all cards in your hand"
                // TODO(https://github.com/micahstairs/bga-innovation/issues/304): Use bulk reveal mechanism.
                $cards = self::getCardsInLocation($player_id, 'hand');
                foreach ($cards as $card) {
                    self::transferCardFromTo($card, $player_id, 'revealed');
                }
                $step_max = 1;
                break;
            
            // id 142, Artifacts age 3: Along the River during the Qingming Festival
            case "142N1":
                do {
                    $card = self::executeDraw($player_id, 4, 'revealed'); // "Draw and reveal a 4"
                    if ($card['color'] == 4) { // "If it is purple, score it"
                        self::transferCardFromTo($card, $player_id, 'score');
                    } else if ($card['color'] == 3) { // "If it is yellow, tuck it"
                        self::tuckCard($card, $player_id);
                    } else { // Put it in hand
                        self::transferCardFromTo($card, $player_id, 'hand');
                    }
                } while($card['color'] == 0 || $card['color'] == 1 || $card['color'] == 2); // "Otherwise, repeat this effect"
                break;
            
            // id 143, Artifacts age 3: Necronomicon
            case "143N1":
                $card = self::executeDraw($player_id, 3, 'revealed'); // "Draw and reveal a 3"
                self::notifyGeneralInfo(clienttranslate('This card is ${color}.'), array('i18n' => array('color'), 'color' => Colors::render($card['color'])));
                if ($card['color'] == 0)  { // Blue
                    self::executeDraw($player_id, 9); // "Draw a 9"
                    self::transferCardFromTo($card, $player_id, 'hand'); // Keep revealed card
                } else if ($card['color'] == 2) { // Green
                    for ($color = 0; $color < 5; $color++) {
                        self::unsplay($player_id, $player_id, $color);
                    }
                    self::transferCardFromTo($card, $player_id, 'hand'); // Keep revealed card
                } else if ($card['color'] == 1 || $card['color'] == 3)  { // Red or yellow
                    self::setAuxiliaryValue($card['color']);
                    $step_max = 1;
                } else {
                    self::transferCardFromTo($card, $player_id, 'hand'); // Keep revealed card
                };
                break;
            
            // id 144, Artifacts age 3: Shroud of Turin
            case "144N1":
                $step_max = 1;
                break;
            
            // id 145, Artifacts age 4: Petition of Right
            case "145C1":
                $num_cards = 0;
                for ($color = 0; $color < 5 ; $color++) {
                    $top_card = self::getTopCardOnBoard($player_id, $color);
                    if (self::hasRessource($top_card, 4 /* tower */)) {
                        $num_cards++;
                    }
                }
                self::notifyPlayer($player_id, 'log', clienttranslate('${You} have ${n} top card(s) with a ${tower} on your board.'), array('i18n' => array('n'), 'You' => 'You', 'n' => self::renderNumber($num_cards), 'tower' => $tower));
                self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} has ${n} top card(s) with a ${tower} on his board.'), array('i18n' => array('n'), 'player_name' => self::renderPlayerName($player_id), 'n' => self::renderNumber($num_cards), 'tower' => $tower));
                self::setAuxiliaryValue($num_cards);
                if ($num_cards > 0) {
                    $step_max = 1;
                }
                break;

            // id 146, Artifacts age 4: Delft Pocket Telescope
            case "146N1":
                $step_max = 1;
                break;

            // id 147, Artifacts age 4: East India Company Charter
            case "147N1":
                $step_max = 2;
                break;

            // id 148, Artifacts age 4: Tortugas Galleon
            case "148C1":
                $card_ids = self::getIdsOfHighestCardsInLocation($player_id, 'score');
                if (count($card_ids) > 0) {
                    // "I compel you to transfer all the highest cards from your score pile to my score pile!"
                    foreach ($card_ids as $card_id) {
                        $card = self::getCardInfo($card_id);
                        self::transferCardFromTo($card, $launcher_id, 'score');
                    }
                    self::setAuxiliaryValue($card['age']);
                    $step_max = 1;
                }
                break;
            
            // id 149, Artifacts age 4: Molasses Reef Caravel
            case "149N1":
                $step_max = 4;
                break;

            // id 150, Artifacts age 4: Hunt-Lenox Globe
            case "150N1":
                $step_max = 1;
                break;
            
            // id 151, Artifacts age 4: Moses
            case "151C1":    
                // "I compel you transfer all top cards with a crown from your board to my score pile"
                $no_top_card_with_crown = true;
                for ($color = 0; $color < 5 ; $color++) {
                    $top_card = self::getTopCardOnBoard($player_id, $color);
                    if (self::hasRessource($top_card, 1 /* crown */)) {
                        $no_top_card_with_crown = false;
                        self::transferCardFromTo($top_card, $launcher_id, 'score');
                    }
                }
                if ($no_top_card_with_crown) {
                    self::notifyPlayer($player_id, 'log', clienttranslate('${You} have no top cards with a ${crown} on your board.'), array('You' => 'You', 'crown' => $crown));
                    self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} has no top cards with a ${crown} on his board.'), array('player_name' => self::renderPlayerName($player_id), 'crown' => $crown));
                }
                break;

            case "151N1":
                $step_max = 1;
                break;

            // id 152, Artifacts age 4: Mona Lisa
            case "152N1":
                $step_max = 2;
                break;

            // id 153, Artifacts age 4: Cross of Coronado
            case "153N1":
                // "Reveal your hand"
                self::revealHand($player_id);
            
                // "If you have exactly five cards and five colors in your hand, you win"
                $card_count_by_color = self::countCardsInLocationKeyedByColor($player_id, 'hand');
                if (count(array_diff($card_count_by_color, array(1))) == 0) {
                    self::notifyPlayer($player_id, 'log', clienttranslate('${You} have exactly five cards and five colors in your hand.'), array('You' => 'You'));
                    self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} has exactly five cards and five colors in his hand.'), array('player_name' => self::renderPlayerName($player_id)));
                    $this->innovationGameState->set('winner_by_dogma', $player_id);
                    self::trace('EOG bubbled from self::stInterInteractionStep CrossOfCoronado');
                    throw new EndOfGame();
                } else {
                    self::notifyPlayer($player_id, 'log', clienttranslate('${You} do not have exactly five cards and five colors in your hand.'), array('You' => 'You'));
                    self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} does not have exactly five cards and five colors in his hand.'), array('player_name' => self::renderPlayerName($player_id)));
                }
                break;

            // id 154, Artifacts age 4: Abell Gallery Harpsichord
            case "154N1":
                // "For each value of top card on your board appearing exactly once draw and score a card of that value in ascending order"
                $top_cards = self::getTopCardsOnBoard($player_id);
                $top_values = array();
                foreach ($top_cards as $top_card) {
                    $top_values[] = $top_card['faceup_age'];
                }
                asort($top_values);
                foreach (array_count_values($top_values) as $value => $count) {
                    // Appears exactly once
                    if ($count == 1) {
                        self::executeDraw($player_id, $value, 'score');
                    }
                }
                break;

            // id 155, Artifacts age 5: Boerhavve Silver Microscope
            case "155N1":
                $step_max = 2;
                break;

            // id 156, Artifacts age 5: Principia
            case "156N1":
                $step_max = 1;
                break;
            
            // id 157, Artifacts age 5: Bill of Rights
            case "157C1":
                // "A color where you have more visible cards than I do"
                $colors_with_more_visible_cards = array();
                for ($color = 0; $color < 5; $color++){
                    if (self::countVisibleCards($player_id, $color) > self::countVisibleCards($launcher_id, $color)) {
                        $colors_with_more_visible_cards[] = $color;
                        $step_max = 1;
                    }
                }
                $this->innovationGameState->setFromArray('color_array', $colors_with_more_visible_cards);
                break;

            // id 158, Artifacts age 5: Ship of the Line Sussex
            case "158N1":
                $number_of_cards_in_score_pile = self::countCardsInLocation($player_id, 'score');
                if ($number_of_cards_in_score_pile == 0) {
                    // Only do interaction B
                    $step = 2;
                    $step_max = 2;
                } else {
                    // Only do interaction A
                    $step_max = 1;
                }
                break;
 
            // id 159, Artifacts age 5: Barque-Longue La Belle
            case "159N1":
                do {
                    $card = self::executeDrawAndMeld($player_id, 5); // "Draw and meld a 5"
                } while ($card['color'] != 2); // // "If the drawn card is not green, repeat this effect"
                break;

            // id 160, Artifacts age 5: Hudson's Bay Company Archives
            case "160N1":
                // "Score the bottom card of every color on your board"
                for ($color = 0; $color< 5; $color++) {
                    $card = self::getBottomCardOnBoard($player_id, $color);
                    if ($card !== null) {
                        self::scoreCard($card, $player_id);
                    }
                }
                $step_max = 1;
                break;

            // id 161, Artifacts age 5: Gujin Tushu Jinsheng
            case "161N1":
                // "If Gujin Tushu Jinsheng is on your board"
                $top_yellow_card = self::getTopCardOnBoard($player_id, 3);
                if ($top_yellow_card !== null && $top_yellow_card['id'] == 161) {
                    $step_max = 1;
                }
                break;

            // id 162, Artifacts age 5: The Daily Courant
            case "162N1":
                $step_max = 3;
                break;

            // id 163, Artifacts age 5: Sandham Room Cricket Bat
            case "163N1":
                // "Draw and reveal a 6"
                $card = self::executeDraw($player_id, 6, 'revealed');
                if ($card['color'] == 1) { // "If it is red"
                    $step_max = 1;
                }
                self::notifyGeneralInfo(clienttranslate('This card is ${color}.'), array('i18n' => array('color'), 'color' => Colors::render($card['color'])));
                self::transferCardFromTo($card, $player_id, 'hand');
                break;
 
            // id 164, Artifacts age 5: Almira, Queen of the Castle
            case "164N1":
                $step_max = 1;
                break;

            // id 165, Artifacts age 6: Kilogram of the Archives
            case "165N1":
                $step_max = 2;
                break;

            // id 166, Artifacts age 6: Puffing Billy
            case "166N1":
                $step_max = 1;
                break;

            // id 167, Artifacts age 6: Frigate Constitution
            case "167C1":
                $step_max = 1;
                break;

            // id 168, Artifacts age 6: U.S. Declaration of Independence
            case "168C1":
                $step_max = 3;
                break;

            // id 169, Artifacts age 6: The Wealth of Nations
            case "169N1":
                // "Draw and score a 1"
                self::executeDraw($player_id, 1, 'score');
                // "Add up the values of all the cards in your score pile, divide by five, and round up"
                $age_to_score = ceil(self::getPlayerScore($player_id) / 5);
                // "Draw and score a card of value equal to the result"
                self::executeDraw($player_id, $age_to_score, 'score');
                break;
                
            // id 170, Artifacts age 6: Buttonwood Agreement
            case "170N1":
                $step_max = 1;
                break;

            // id 171, Artifacts age 6: Stamp Act
            case "171C1":
                $top_yellow_card = self::getTopCardOnBoard($player_id, 3);
                if ($top_yellow_card !== null) {
                    self::setAuxiliaryValue($top_yellow_card['age']);
                    $step_max = 1;
                }
                break;

            // id 172, Artifacts age 6: Pride and Prejudice
            case "172N1":
                do {
                    // "Draw and meld a 6"
                    $card = self::executeDrawAndMeld($player_id, 6);
                    // "If the drawn card's color is the color with the fewest (or tied) number of visible cards on your board"
                    $num_visible_cards_of_drawn_color = self::countVisibleCards($player_id, $card['color']);
                    for ($color = 0; $color < 5; $color++) {
                        if ($num_visible_cards_of_drawn_color > self::countVisibleCards($player_id, $color)) {
                            break 2; // Exit do-while loop
                        }
                    }
                    // "Score the melded card"
                    self::scoreCard($card, $player_id);
                } while (true); // "Repeat this effect"
                break;

            // id 173, Artifacts age 6: Moonlight Sonata
            case "173N1":
                if (self::getMaxAgeOnBoardTopCards($player_id) > 0) {
                    $step_max = 2;
                }
                break;
                
            // id 174, Artifacts age 6: Marcha Real
            case "174N1":
                self::setAuxiliaryValue(-1);
                self::setAuxiliaryValue2(-1);
                $step_max = 1;
                break;

            // id 175, Artifacts age 7: Periodic Table
            case "175N1":
                // Determine if there are any top cards which have the same value as another top card on their board
                $colors = self::getColorsOfRepeatedValueOfTopCardsOnBoard($player_id);
                if (count($colors) >= 2) {
                    self::setAuxiliaryValueFromArray($colors);
                    $step_max = 2;
                } else {
                    self::notifyGeneralInfo(clienttranslate("No two top cards have the same value."));
                }
                break;

            // id 176, Artifacts age 7: Corvette Challenger
            case "176N1":
                // "Draw and tuck an 8"
                $card = self::executeDrawAndTuck($player_id, 8);
                // "Splay up the color of the tucked card"
                self::splayUp($player_id, $player_id, $card['color']);
                //  "Draw and score a card of value equal to the number of cards of that color visible on your board"
                $visible_card_count = self::countVisibleCards($player_id, $card['color']);
                self::notifyPlayer($player_id, 'log', clienttranslate('There are ${number} ${color} card(s) visible on ${your} board.'), array(
                    'i18n' => array('color'),
                    'number' => $visible_card_count,
                    'color' => Colors::render($card['color']),
                    'your' => 'your')
                );
                self::notifyAllPlayersBut($player_id, 'log', clienttranslate('There are ${number} ${color} card(s) visible on ${player_name}\'s board.'), array(
                    'i18n' => array('color'),
                    'number' => $visible_card_count,
                    'color' => Colors::render($card['color']),
                    'player_name' => self::renderPlayerName($player_id))
                );
                self::executeDraw($player_id, $visible_card_count, 'score');
                break;

            // id 177, Artifacts age 7: Submarine H. L. Hunley
            case "177C1":
                // "I compel you to draw and meld a 7" 
                $card = self::executeDrawAndMeld($player_id, 7);

                // "Reveal the bottom card on your board of the melded card's color"
                $bottom_card = self::getBottomCardOnBoard($player_id, $card['color']);
                self::transferCardFromTo($bottom_card, $player_id, 'revealed');

                // "If the revealed card is a 1"
                if ($bottom_card['faceup_age'] == 1) {
                    $step_max = 1;
                    self::setAuxiliaryValue($bottom_card['color']);
                }
                // Put the revealed card back on the bottom
                $revealed_card = self::getCardInfo($bottom_card['id']);
                self::tuckCard($revealed_card, $player_id);
                break;

            // id 178, Artifacts age 7: Jedlik's Electromagnetic Self-Rotor
            case "178N1":
                // "Draw and score an 8"
                $card = self::executeDraw($player_id, 8, 'score');
                // "Draw and meld an 8"
                $card = self::executeDrawAndMeld($player_id, 8);
                $step_max = 1;
                break;

            // id 179, Artifacts age 7: International Prototype Metre Bar
            case "179N1":
                $step_max = 1;
                break;

            // id 180, Artifacts age 7: Hansen Writing Ball
            case "180C1":
                // "I compel you to draw four 7s"
                self::executeDraw($player_id, 7);
                self::executeDraw($player_id, 7);
                self::executeDraw($player_id, 7);
                self::executeDraw($player_id, 7);

                $number_of_blue_cards = self::countCardsInLocationKeyedByColor($player_id, 'hand')[Colors::BLUE];
                if ($number_of_blue_cards == 0) {
                    self::revealHand($player_id);
                    $color_in_clear = Colors::render(Colors::BLUE);
                    self::notifyPlayer($player_id, 'log', clienttranslate('${You} have no ${colored} cards in your hand.'), array('i18n' => array('colored'), 'You' => 'You', 'colored' => $color_in_clear));
                    self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} has no ${colored} cards in his hand.'), array('i18n' => array('colored'), 'player_name' => self::renderPlayerName($player_id), 'colored' => $color_in_clear));

                    // "Transfer all cards in your hand to my hand"
                    foreach (self::getIdsOfCardsInLocation($player_id, 'hand') as $id) {
                        self::transferCardFromTo(self::getCardInfo($id), $launcher_id, 'hand');
                    }
                } else {
                    $step_max = 1;
                }
                break;

            case "180N1":
                do {
                    // "Draw and reveal a 7"
                    $card = self::executeDraw($player_id, 7, 'revealed');
                    if (self::hasRessource($card, 6)) {
                        self::transferCardFromTo($card, $player_id, 'hand');
                        break;
                    } else {
                        // "If it has no clocks, tuck it"
                        self::tuckCard($card, $player_id);
                    }
                } while (true); // "Repeat this effect"
                break;

            // id 181, Artifacts age 7: Colt Paterson Revolver
            case "181C1":
                // "I compel you to reveal your hand"
                self::revealHand($player_id);

                // Store list of colors in hand before the new card is drawn.
                $cards = self::getCardsInHand($player_id);
                $colors_in_hand = array(0, 0, 0, 0, 0);
                foreach ($cards as $card) {
                    $colors_in_hand[$card['color']] = 1;
                }

                // "Draw a 7"
                if (count($cards) > 0) {
                    // If the player has other cards in hand, we need to reveal the card first in order to prove to other players
                    // whether the card matched the color of another card in hand.
                    $new_card = self::transferCardFromTo(self::executeDraw($player_id, 7, 'revealed'), $player_id, 'hand');
                } else {
                    $new_card = self::executeDraw($player_id, 7);
                }
                
                // "If the color of the drawn card matches the color of any other cards in your hand"
                if ($colors_in_hand[$new_card['color']] == 1) {
                    self::notifyGeneralInfo(clienttranslate("The drawn card's color matches the color of another card in hand."));
                    $step_max = 2;
                } else {
                    self::notifyGeneralInfo(clienttranslate("The drawn card's color does not match the color of another card in hand."));
                }
                break;
                
            // id 182, Artifacts age 7: Singer Model 27
            case "182N1":
                $step_max = 1;
                break;

            // id 183, Artifacts age 7: Roundhay Garden Scene
            case "183N1":
                $step_max = 1;
                break;

            // id 184, Artifacts age 7: The Communist Manifesto
            case "184N1":
                // "For each player in the game, draw and reveal a 7"
                foreach (self::getAllActivePlayerIds() as $any_player_id) {
                    self::executeDraw($player_id, 7, 'revealed');
                }
                $this->innovationGameState->setFromArray('player_array', self::getAllActivePlayers());
                $step_max = 2;
                break;

            // id 185, Artifacts age 8: Parnell Pitch Drop
            case "185N1":
                // "Draw and meld a card of value one higher than the highest top card on your board"
                $card = self::executeDrawAndMeld($player_id, self::getMaxAgeOnBoardTopCards($player_id) + 1);
                if (self::countIconsOnCard($card, 6) == 3) {
                    // "If the melded card has three clocks, you win"
                    self::notifyPlayer($player_id, 'log', clienttranslate('${You} melded a card with 3 ${clocks}.'), array('You' => 'You', 'clocks' => $clock));
                    self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} melded a card with 3 ${clocks}.'), array('player_name' => self::renderPlayerName($player_id), 'clocks' => $clock));
                    $this->innovationGameState->set('winner_by_dogma', $player_id);
                    self::trace('EOG bubbled from self::stPlayerInvolvedTurn Parnell Pitch Drop');
                    throw new EndOfGame();
                }
                break;

            // id 186, Artifacts age 8: Earhart's Lockheed Electra 10E
            case "186N1":
                self::setAuxiliaryValue(0);
                $this->innovationGameState->set('age_last_selected', 9);
                $step_max = 1;
                break;

            // id 187, Artifacts age 8: Battleship Bismarck
            case "187C1":
                // "Draw and reveal an 8"
                $card = self::executeDraw($player_id, 8, 'revealed');
                self::transferCardFromTo($card, $player_id, 'hand');
                self::setAuxiliaryValue($card['color']);
                $step_max = 1;
                break;

            // id 189, Artifacts age 8: Ocean Liner Titanic
            case "189N1":
                // "Score all bottom cards from your board"
                for ($color = 0; $color < 5; $color++) {
                    $card = self::getBottomCardOnBoard($player_id, $color);
                    if ($card !== null) {
                        $card = self::scoreCard($card, $player_id);
                    }
                }
                break;

            // id 190, Artifacts age 8: Meiji-Mura Stamp Vending Machine
            case "190N1":
                $step_max = 1;
                break;

            // id 191, Artifacts age 8: Plush Beweglich Rod Bear
            case "191N1":
                $step_max = 2;
                break;

           // id 192, Artifacts age 8: Time
            case "192C1":
                $step_max = 1;
                break;

           // id 193, Artifacts age 8: Garland's Ruby Slippers
            case "193N1":
                $step_max = 1;
                break;
                
            // id 194, Artifacts age 8: '30 World Cup Final Ball
            case "194C1":
                $step_max = 1;
                break;

            case "194N1":
                do {
                    // "Draw and reveal an 8"
                    $card = self::executeDraw($player_id, 8, 'revealed');
                    $color = $card['color'];
                    
                    // "The single player with the highest top card of the drawn card's color achieves it, ignoring eligibility"
                    $player_ids = self::getOwnersOfTopCardWithColorAndAge($color, self::getMaxAgeOfTopCardOfColor($color));
                    if (count($player_ids) == 1) {
                        $single_player_id = $player_ids[0];
                        self::notifyPlayer($single_player_id, 'log', clienttranslate('${You} have the highest top ${color} card.'), array(
                            'i18n' => array('color'),
                            'You' => 'You',
                            'color' => Colors::render($color)
                        )); 
                        self::notifyAllPlayersBut($single_player_id, 'log', clienttranslate('${player_name} has the highest top ${color} card.'), array(
                            'i18n' => array('color'),
                            'player_name' => self::renderPlayerName($single_player_id),
                            'color' => Colors::render($color)
                        ));
                        self::transferCardFromTo($card, $single_player_id, 'achievements');
                    } else {
                        break;
                    }
                } while (true); // "If that happens, repeat this effect"
                self::transferCardFromTo($card, $player_id, 'hand');
                break;
                
            
            // id 195, Artifacts age 9: Yeager's Bell X-1A
            case "195N1+":
                // "If that card has a clock, repeat this effect"
                if (self::getAuxiliaryValue() == 1) {
                    // Reset the post_execution_index so that we will return to 195N1+ (instead of 195N1++)
                    // if we repeat the effect again.
                    self::updateCurrentNestedCardState('post_execution_index', 0);
                    // Purposefully fall through to 195N1 so that the effect can be repeated.
                } else {
                    break;
                }

            case "195N1":
                // "Draw and meld a 9"
                $card = self::executeDrawAndMeld($player_id, 9);

                // Store information about whether the card has a clock or not
                if (self::hasRessource($card, 6)) {
                    self::setAuxiliaryValue(1);
                } else {
                    self::setAuxiliaryValue(0);
                }

                // "Execute the effects of the melded card as if they were on this card, without sharing"
                self::fullyExecute($card);
                break;

            // id 196, Artifacts age 9: Luna 3
            case "196N1":
                $step_max = 1;
                break;            

            // id 197, Artifacts age 9: United Nations Charter
            case "197C1":
                $step_max = 1;
                break;            

            case "197N1":
                // "If you have a top card on your board with a demand effect, draw a 10"
                $top_cards = self::getTopCardsOnBoard($player_id);
                foreach ($top_cards as $card){
                    if ($card['has_demand'] == true) {
                        self::executeDraw($player_id, 10);
                        break;
                    }
                }
                break;            

            // id 198, Artifacts age 9: Velcro Shoes
            case "198C1":
                $step_max = 1;
                break;            

            // id 199, Artifacts age 9: Philips Compact Cassette
            case "199C1":
                // "I compel you to unsplay all splayed colors on your board!"
                for ($color = 0; $color < 5; $color++) {
                    self::unsplay($player_id, $player_id, $color);
                }
                break;

            case "199N1":
                // If there are only one or two splayable stacks then we can splay up automatically
                $splayable_colors = self::getSplayableColorsOnBoard($player_id, Directions::UP);
                if (count($splayable_colors) <= 2) {
                    foreach ($splayable_colors as $color) {
                        self::splayUp($player_id, $player_id, $color);
                    }
                
                // Otherwise we need to prompt the player to choose which colors to splay up
                } else {
                    $step_max = 1;
                }
                break;

            // id 200, Artifacts age 9: Syncom 3
            case "200N1":
                $step_max = 1;
                break;

            // id 201, Artifacts age 9: Rock Around the Clock
            case "201N1":
                // "For each top card on your board with a clock, draw and score a 9"
                $top_cards = self::getTopCardsOnBoard($player_id);
                foreach ($top_cards as $card){
                    if (self::hasRessource($card, 6)) {
                        self::executeDraw($player_id, 9, 'score');
                    }
                }
                break;        
            
            // id 202, Artifacts age 9: Magnavox Odyssey
            case "202N1":
                // "Draw and meld two 10s"
                $card_1 = self::executeDrawAndMeld($player_id, 10);
                $card_2 = self::executeDrawAndMeld($player_id, 10);
                
                // "If they are the same color, you win"
                if ($card_1['color'] == $card_2['color']) {
                    self::notifyPlayer($player_id, 'log', clienttranslate('${You} melded two cards of the same color'), array('You' => 'You'));
                    self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} melded two cards of the same color'), array('player_name' => self::renderPlayerName($player_id)));
                    $this->innovationGameState->set('winner_by_dogma', $player_id);
                    self::trace('EOG bubbled from self::stPlayerInvolvedTurn Magnavox Odyssey');
                    throw new EndOfGame();
                }
                break;
            
            // id 203, Artifacts age 9: The Big Bang
            case "203N1+":
                // "If this caused any change to occur, draw and remove a 10 from the game, then repeat this effect"
                if (self::getAuxiliaryValue() == 1) {
                    $card = self::executeDraw($player_id, 10, 'revealed');
                    self::transferCardFromTo($card, 0, 'removed');
                    // Reset the post_execution_index so that we will return to 203N1+ (instead of 203N1++)
                    // if we repeat the effect again.
                    self::updateCurrentNestedCardState('post_execution_index', 0);
                    // Purposefully fall through to 203N1 so that the effect can be repeated.
                } else {
                    break;
                }

            case "203N1":
                // "Execute the non-demand effects of your top blue card, without sharing"
                $top_blue_card = self::getTopCardOnBoard($player_id, 0);
                if ($top_blue_card !== null) {
                    self::setAuxiliaryValue(0);
                    self::selfExecute($top_blue_card);
                }
                break;

            // id 204, Artifacts age 9: Marilyn Diptych
            case "204N1":
                $step_max = 2;
                break;

            // id 205, Artifacts age 10: Rover Curiosity
            case "205N1":
                // "Draw and meld an Artifact 10"
                $card = self::executeDrawAndMeld($player_id, 10, CardTypes::ARTIFACTS);
                // "Execute the effects of the melded card as if they were on this card. Do not share them"
                self::fullyExecute($card);
                break;
            
            // id 206, Artifacts age 10: Higgs Boson
            case "206N1":
                // "Transfer all cards on your board to your score pile"
                $piles = self::getCardsInLocationKeyedByColor($player_id, 'board');
                for ($i = 0; $i < 5 ; $i++){
                    $pile = $piles[$i];
                    for ($j = count($pile) - 1; $j >= 0; $j--) {
                        self::transferCardFromTo($pile[$j], $player_id, 'score');
                    }
                }
                break;
                
           // id 207, Artifacts age 10: Exxon Valdez
            case "207C1":
                // "I compel you to remove all cards from your hand, score pile, board, and achievements from the game"
                self::removeAllCardsFromPlayer($player_id);
                
                // "You lose! If there is only one player remaining in the game, that player wins"
                if (self::decodeGameType($this->innovationGameState->get('game_type')) == 'individual') {
                    self::notifyPlayer($player_id, 'log', clienttranslate('${You} lose.'),  array('You' => 'You'));
                    self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} loses.'), array(
                        'player_name' => self::renderPlayerName($player_id)
                    ));
                    if (count(self::getAllActivePlayers()) == 2) {
                        $this->innovationGameState->set('winner_by_dogma', $launcher_id);
                        self::trace('EOG bubbled from self::stInterInteractionStep Exxon Valdez');
                        throw new EndOfGame();
                    } else {
                        // Only eliminate the player if the game isn't ending
                        self::eliminatePlayer($player_id);
                    }
                } else { // Team play
                    // Entire team loses if one player loses 
                    $teammate_id = self::getPlayerTeammate($player_id);
                    $losing_team = array($player_id, $teammate_id);
                    self::notifyPlayer($player_id, 'log', clienttranslate('${Your} team loses.'), array('Your' => 'Your'));
                    self::notifyPlayer($teammate_id, 'log', clienttranslate('${Your} team loses.'), array('Your' => 'Your'));
                    self::notifyAllPlayersBut($losing_team, 'log', clienttranslate('The other team loses.'), array());
                    $this->innovationGameState->set('winner_by_dogma', $launcher_id);
                    self::trace('EOG bubbled from self::stInterInteractionStep Exxon Valdez');
                    throw new EndOfGame();
                }
                break;

            // id 208, Artifacts age 10: Maldives
            case "208C1":
                $step_max = 2;
                break;

            case "208N1":
                $score_cards = self::getCardsInLocation($player_id, 'score');
                self::setAuxiliaryValue(count($score_cards));
                if (count($score_cards) > 4) {
                    $step_max = 1;
                }
                break;

            // id 209, Artifacts age 10: Maastricht Treaty
            case "209N1":
                // "If you have the most cards in your score pile, you win"
                $win_condition_met = true;
                $cards_in_my_score_pile = self::countCardsInLocation($player_id, 'score');
                foreach (self::getAllActivePlayerIds() as $id) {
                    if ($player_id != $id && self::countCardsInLocation($id, 'score') >= $cards_in_my_score_pile) {
                        $win_condition_met = false;
                        break;
                    }
                }
                if ($win_condition_met) {
                    self::notifyPlayer($player_id, 'log', clienttranslate('${You} have the most cards in your score pile.'), array('You' => 'You'));
                    self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} has the most cards in his score pile.'), array('player_name' => self::renderPlayerName($player_id)));
                    $this->innovationGameState->set('winner_by_dogma', $player_id);
                    self::trace('EOG bubbled from self::stPlayerInvolvedTurn Maastricht Treaty');
                    throw new EndOfGame();
                } else {
                    self::notifyPlayer($player_id, 'log', clienttranslate('${You} do not have the most cards in your score pile.'), array('You' => 'You'));
                    self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} does not have the most cards in his score pile.'), array('player_name' => self::renderPlayerName($player_id)));
                }
                break;

            // id 210, Artifacts age 10: Seikan Tunnel
            case "210N1":
                // "If you have the most cards of a color showing on your board out of all colors on all boards, you win"
                $win_condition_met = true;
                $max_visible_card_count = self::getSizeOfMaxVisiblePileOnBoard($player_id);
                foreach (self::getAllActivePlayerIds() as $id) {
                    if ($id != $player_id && self::getSizeOfMaxVisiblePileOnBoard($id) > $max_visible_card_count) {
                        $win_condition_met = false;
                        break;
                    }
                }

                // There is a stack on your board that has the most cards of all stacks on all boards
                if ($win_condition_met) {
                    self::notifyPlayer($player_id, 'log', clienttranslate('${You} have the most cards of a color showing on your board out of all colors on all boards.'), array('You' => 'You'));
                    self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} has the most cards of a color showing on his board out of all colors on all boards.'), array('player_name' => self::renderPlayerName($player_id)));
                    $this->innovationGameState->set('winner_by_dogma', $player_id);
                    self::trace('EOG bubbled from self::stPlayerInvolvedTurn Seikan Tunnel');
                    throw new EndOfGame();
                } else {
                    self::notifyPlayer($player_id, 'log', clienttranslate('${You} do not have the most cards of a color showing on your board out of all colors on all boards.'), array('You' => 'You'));
                    self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} does not have the most cards of a color showing on his board out of all colors on all boards.'), array('player_name' => self::renderPlayerName($player_id)));
                }
                break;
                
            // id 211, Artifacts age 10: Dolly the Sheep
            case "211N1":
                $step_max = 3;
                break;
            
            // id 212, Artifacts age 10: Where's Waldo?
            case "212N1":
                // "You win"
                self::notifyPlayer($player_id, 'log', clienttranslate('${You} found Waldo!'), array('You' => 'You'));
                self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} found Waldo!'), array('player_name' => self::renderPlayerName($player_id)));
                $this->innovationGameState->set('winner_by_dogma', $player_id);
                self::trace('EOG bubbled from self::stPlayerInvolvedTurn Wheres Waldo');
                throw new EndOfGame();

             // id 213, Artifacts age 10: DeLorean DMC-12
            case "213N1":
                // "If DeLorean DMC-12 is a top card on any board, remove all top cards on all boards and all cards in all hands from the game"
                if (self::isTopBoardCard(self::getCardInfo(213))) {
                    self::removeAllTopCardsAndHands();
                }
                break;
            
            // id 214, Artifacts age 10: Twister
            case "214C1":
                // "I compel you to reveal your score pile"
                self::revealScorePile($player_id);
                $colors = array();
                foreach (self::getCardsInLocation($player_id, 'score') as $card) {
                    $colors[] = $card['color'];
                }
                if (count($colors) > 0) {
                    self::setAuxiliaryValueFromArray(array_unique($colors));
                    $step_max = 1;
                }
                break;

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

            // id 442, age 11: Astrogeology
            case "442N1":
                // "Draw and reveal an 11."
                $revealed_card = self::executeDraw($player_id, 11, 'revealed');
                
                // "Splay its color on your board aslant. If you do, transfer all but your top two cards of that color into your hand."
                $color = $revealed_card['color'];
                $num_cards_in_pile = self::countCardsInLocationKeyedByColor($player_id, 'board')[$color];
                if ($revealed_card['splay_direction'] != 4 && $num_cards_in_pile >= 2) { // aslant
                    self::splayAslant($player_id, $player_id, $color);
                    
                    while ($num_cards_in_pile > 2) {
                        $card = self::getBottomCardOnBoard($player_id, $color);
                        self::transferCardFromTo($card, $player_id, 'hand');
                        $num_cards_in_pile--;
                    }
                }
                self::transferCardFromTo($revealed_card, $player_id, 'hand'); // put revealed card in hand
                break;

            case "442N2":
                if (self::countCardsInLocation($player_id, 'hand') >= 11) { 
                    // "If you have eleven or more cards in your hand, you win."
                    self::notifyPlayer($player_id, 'log', clienttranslate('${You} have 11 or more cards in hand.'), array('You' => 'You'));
                    self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} has 11 or more cards in hand.'), array('player_name' => self::renderPlayerName($player_id)));
                    $this->innovationGameState->set('winner_by_dogma', $player_id);
                    self::trace('EOG bubbled from self::stPlayerInvolvedTurn Astrogeology');
                    throw new EndOfGame();
                }
                break;
            
            // id 443, age 11: Fusion
            case "443N1":
                self::setAuxiliaryValue(11);
                $step_max = 1;
                break;

            // id 444, age 11: Hypersonics
            case "444D1":
                $top_cards = self::getTopCardsOnBoard($player_id);
                $matching_cards = array();
                foreach ($top_cards as $first_card) {
                    foreach ($top_cards as $second_card) {
                        if ($first_card['id'] != $second_card['id'] && $first_card['faceup_age'] == $second_card['faceup_age']) {
                            $matching_cards[] = $first_card['id'];
                        }
                    }
                }
                
                if (count($matching_cards) > 0) {
                    $step_max = 2;
                    self::setAuxiliaryArray($matching_cards);
                } else {
                    self::notifyPlayer($player_id, 'log', clienttranslate('${You} do not have two top cards of matching value on your board.'),  
                        array('You' => 'You'));
                    self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} does not have two top cards of matching value on his board.'), 
                        array('player_name' => self::renderPlayerName($player_id)));
                }
                break;

            // id 446, age 11: Near-Field Comm
            case "446D1":
                $step_max = 1;
                break;

            case "446N1":
                $step_max = 1;
                break;

            // id 447, age 11: Reclamation
            case "447N1":
                // "Return your three bottom red cards."
                $color = 1; // start with red
                
                $keep_going = true;
                do {
                    $card_counts = self::countCardsInLocationKeyedByColor($player_id, 'board');
                    if ($card_counts[$color] >= 3) {
                        // "If you returned three cards, repeat this dogma effect using the color of the melded card."
                        $return_card_count = 3;
                    } else {
                        $return_card_count = $card_counts[$color];
                        $keep_going  = false; // stop the loop.  not enough cards returned
                    }
                    
                    $total_age_value = 0;
                    for ($i = 0; $i < $return_card_count; $i++) {
                        $bottom_card = self::getBottomCardOnBoard($player_id, $color);
                        self::returnCard($bottom_card);
                        
                        $total_age_value += $bottom_card['faceup_age'];
                    }
                    // "Draw and meld a card of value equal to half the total sum value of the returned cards, rounded up."
                    $melded_card = self::executeDrawAndMeld($player_id, ceil($total_age_value / 2));
                    $color = $melded_card['color']; // assign a new color for the next loop (if necessary)
                } while ($keep_going);
                break;

            // id 448, age 11: Escapism
            case "448N1":
                $step_max = 1;
                break;

            // id 449, age 11: Whataboutism
            case "449D1":
                $step_max = 1;
                break;

            // id 487, Unseen age 1: Rumor
            case "487N1":
                $step_max = 1;
                break;
            
            case "487N2":
                $step_max = 1;
                break;
                
            // id 489, Unseen age 1: Handshake
            case "489D1":
                // "I demand you transfer all cards from my hand to your hand!"
                foreach (self::getCardsInHand($launcher_id) as $card) {
                    self::transferCardFromTo($card, $player_id, 'hand');
                }

                // Find unique colors
                $cards_in_player_hand = self::countCardsInLocationKeyedByColor($player_id, 'hand');
                $color_array = array();
                for ($color = 0; $color < 5; $color++) {
                    if ($cards_in_player_hand[$color] > 0) {
                        $color_array[] = $color;
                    }
                }
                
                // "Choose two colors of cards in your hand! Transfer all cards in your hand of those colors to my hand!"
                if (count($color_array) == 1 || count($color_array) == 2) {
                    foreach (self::getCardsInHand($player_id) as $card) {
                        self::transferCardFromTo($card, $launcher_id, 'hand');
                    }
                } else if (count($color_array) > 2) {
                    $step_max = 1;
                    self::setAuxiliaryValueFromArray($color_array);
                }
                break;

            // id 490, Unseen age 1: Tomb
            case "490N1":
                $step_max = 1;
                break;

            // id 491, Unseen age 1: Woodworking
            case "491N1":
                // "Draw and meld a 2."
                $card = self::executeDrawAndMeld($player_id, 2);
                $bottom_card = self::getBottomCardOnBoard($player_id, $card['color']);
                if ($bottom_card['id'] == $card['id'])
                {
                    // "If the melded card is a bottom card on your board, score it."
                    self::scoreCard($card, $player_id);
                }
                break;

            // id 495, Unseen age 2: Astrology
            case "495N1":
                $stack_size = self::countCardsInLocationKeyedByColor($player_id, 'board');
                $largest_stack = max($stack_size);
                $color_array = array();
                for ($color = 0; $color < 5; $color++) {
                    if ($stack_size[$color] == $largest_stack) {
                        $color_array[] = $color;
                    }
                }
                $step_max = 1;
                self::setAuxiliaryValueFromArray($color_array);
                break;

            case "495N2":
                // "Draw and meld a card of value equal to the number of visible purple cards on your board."
                $purple_pile_size = self::countVisibleCards($player_id, 4);
                $card = self::executeDrawAndMeld($player_id, $purple_pile_size);
                if (!self::hasRessource($card, Icons::PROSPERITY)) {
                    // "If the melded card has no crowns, tuck it."
                    self::tuckCard($card, $player_id);
                }
                break;
                
            // id 496, Unseen age 2: Meteorology
            case "496N1":
                // "Draw and reveal a 3. If it has a leaf, score it.  Otherwise, if it has a crown, return it and draw two 3. Otherwise, tuck it."
                $revealed_card = self::executeDrawAndReveal($player_id, 3);
                if (self::hasRessource($revealed_card, Icons::HEALTH)) {
                    self::scoreCard($revealed_card, $player_id);
                } else if (self::hasRessource($revealed_card, Icons::PROSPERITY)) {
                    self::returnCard($revealed_card);
                    self::executeDraw($player_id, 3);
                    self::executeDraw($player_id, 3);
                } else {
                    self::tuckCard($revealed_card, $player_id);
                }
                break;

            case "496N2":
                // "If you have no towers, claim the Zen achievement."
                $icon_counts = self::getPlayerResourceCounts($player_id);
                if ($icon_counts[Icons::AUTHORITY] == 0) {
                    self::claimSpecialAchievement($player_id, 596); // Zen
                }
                break;

            // id 497, Unseen age 2: Padlock
            case "497D1":
                $step_max = 1;
                self::setAuxiliaryValue(0);
                break;

            case "497N1":
                // "If no card was transferred due to the demand,"
                if (self::getAuxiliaryValue() <= 0) {
                    $step_max = 1;
                }
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
                
            // id 501, Unseen age 2: Exile
            case "501D1":
                $step_max = 2;
                self::setAuxiliaryValue(0);
                break;

            case "501N1":
                if (self::getAuxiliaryValue() == 1) {
                    // "If exactly one card was returned due to the demand, return Exile and draw a 3."
                    $exile_card = self::getCardInfo(501);
                    if ($exile_card['location'] != 'deck') {
                        // don't return this multiple times
                        self::returnCard(self::getCardInfo(501));
                    }
                    self::executeDraw($player_id, 3);
                }
                break;

            // id 502, Unseen age 2: Fingerprints
            case "502N1":
                $step_max = 1;
                break;
                
            case "502N2":
                $step_max = 1;
                break;
                
            // id 504, Unseen age 2: Steganography
            case "504N1":
                $color_array = array();
                for ($color = 0; $color < 5; $color++) {
                    if (self::countVisibleIconsInPile($player_id, 3, $color) > 0) {
                        $color_array[] = $color;
                    }
                }
                if (count($color_array) > 0) {
                    $step_max = 1;
                    self::setAuxiliaryValueFromArray($color_array);
                }
                break;

            // id 506, Unseen age 3: Secret Secretorum
            case "506N1":
                break;

            // id 510, Unseen age 3: Smuggling
            case "510D1":
                $demander_top_yellow = self::getTopCardOnBoard($launcher_id, 3);
                $demandee_top_yellow = self::getTopCardOnBoard($player_id, 3);
                if ($demandee_top_yellow !== null && $demander_top_yellow !== null) {
                    $step_max = 2;
                } else if ($demander_top_yellow !== null) {
                    // demander has a yellow, skip to interaction #2
                    $step_max = 2; $step = 2;
                } else if ($demandee_top_yellow !== null) {
                    $step_max = 1;
                } else {
                    // no yellow cards.  Stop the action
                }
                break;

            // id 511, Unseen age 3: Freemasons
            case "511N1":
                $cards_in_hand = self::getCardsInHand($player_id);                
                if (count($cards_in_hand) > 0) {
                    self::setAuxiliaryValue2FromArray(array(0,1,2,3,4));
                    $step_max = 1;
                    self::setAuxiliaryValue(0); // no yellows or expansion cards tucked
                }
                break;

            case "511N2":
                $step_max = 1;
                break;
                
            // id 513, Unseen age 3: Masquerade
            case "513N1":
                $step_max = 1;
                break;

            case "513N2":
                $step_max = 1;
                break;

            // id 525, Unseen age 5: Popular Science
            case "525N1":
                $age_array = array();
                foreach (self::getActivePlayerIdsInTurnOrderStartingWithCurrentPlayer() as $all_player_id) {
                    $top_green_card = self::getTopCardOnBoard($all_player_id, 2);
                    if ($top_green_card !== null) {
                        $age_array[] = $top_green_card['age'];
                    }
                }
                if (count($age_array) == 1) {
                    // "Draw and meld a card of value equal to the value of a top green card anywhere."
                    self::executeDrawAndMeld($player_id, $age_array[0]);
                } else if (count($age_array) > 1) {
                    $step_max = 1;
                    self::setAuxiliaryArray(array_unique($age_array));
                }
                break;

            case "525N2":
                // "Draw and meld a card of value one higher than the value of your top yellow card."
                $top_yellow_card = self::getTopCardOnBoard($player_id, 3);
                if ($top_yellow_card !== null) {
                    self::executeDrawAndMeld($player_id, $top_yellow_card['age'] + 1);
                } else {
                    self::executeDrawAndMeld($player_id, 1);
                }
                break;

            case "525N3":
                $step_max = 1;
                break;

            // id 526, Unseen age 5: Probability
            case "526N1":
                $step_max = 1;
                break;

            case "526N2":
                // "Draw and reveal two 6"
                $card1 = self::executeDrawAndReveal($player_id, 6);
                $card2 = self::executeDrawAndReveal($player_id, 6);
                $unique_icon_count = 0;
                for ($icon=1; $icon < 10; $icon++) {
                    if (self::hasRessource($card1, $icon) || self::hasRessource($card2, $icon)) {
                        $unique_icon_count++;
                    }
                }
                for ($icon=11; $icon < 18; $icon++) {
                    if (self::hasRessource($card1, $icon) || self::hasRessource($card2, $icon)) {
                        $unique_icon_count++;
                    }
                }
                for ($icon=101; $icon < 113; $icon++) {
                    if (self::hasRessource($card1, $icon) || self::hasRessource($card2, $icon)) {
                        $unique_icon_count++;
                    }
                }
                self::setAuxiliaryValue($unique_icon_count);
                $step_max = 1;
                break;
                
            // id 527, Unseen age 5: Cabal
            case "527D1":
                // "I demand you transfer all cards from your hand that have a value matching any of my secrets to my score pile!"
                $hand_cards = self::getCardsInHand($player_id);
                $safe_cards_by_age = self::countCardsInLocationKeyedByAge($launcher_id, 'safe');
                foreach ($hand_cards as $card) {
                    if ($safe_cards_by_age[$card['age'] ] > 0) {
                        self::transferCardFromTo($card, $launcher_id, 'score');
                    }
                }
                // "Draw a 5!"
                self::executeDraw($player_id, 5);
                break;

            case "527N1":
                $card_id_array = array();
                $top_cards = self::getTopCardsOnBoard($player_id);
                $achievement_cards = self::getCardsInLocation(0, 'achievements');
                foreach ($achievement_cards as $achieve_card) {
                    foreach ($top_cards as $top_card) {
                        if ($top_card['age'] == $achieve_card['age']) {
                            $card_id_array[] = $achieve_card['id'];
                        }
                    }
                }
                if (count($card_id_array) > 0) {
                    $step_max = 1;
                    self::setAuxiliaryArray($card_id_array);
                }
                break;

            default:
                // Do not throw an exception so that we are able to stop executing a card after it's popped from
                // the stack and there's nothing left to do.
                break;
            }
        }
        catch (EndOfGame $e) {
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
    
    function stInterPlayerInvolvedTurn() {

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
    
    function stInteractionStep() {
        $player_id = self::getCurrentPlayerUnderDogmaEffect();
        $launcher_id = self::getLauncherId();

        if (self::getPlayerTableColumn($player_id, 'distance_rule_share_state') == 1 || self::getPlayerTableColumn($player_id, 'distance_rule_demand_state') == 1) {
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => true,
                
                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => 0,
                'location_to' => 'deck',
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
            ->setMaxSteps(self::getStepMax());

        $code = self::getCardExecutionCodeWithLetter($card_id, $current_effect_type, $current_effect_number, $step);

        $card_id_1 = $this->innovationGameState->get('card_id_1');
        $card_id_2 = $this->innovationGameState->get('card_id_2');
        $card_id_3 = $this->innovationGameState->get('card_id_3');
        
        $leaf = Icons::render(2);
        $lightbulb = Icons::render(3);
        $clock = Icons::render(6);

        if (self::isInSeparateFile($card_id)) {
            $options = self::getCardInstance($card_id, $executionState)->getInteractionOptions();
            if (empty($options)) {
                $options = null;
            } else {
                // Use sensible defaults for unset options
                if (array_key_exists('n', $options) && $options['n'] == 'all') {
                    $options['n'] = 999;
                }
                if (array_key_exists('n_max', $options) && $options['n_max'] == 'all') {
                    $options['n_max'] = 999;
                }
                if (!array_key_exists('can_pass', $options)) {
                    $options['can_pass'] = false;
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
                }
                if (array_key_exists('foreshadow_keyword', $options)) {
                    $options['location_to'] = 'forecast';
                }
                if (array_key_exists('return_keyword', $options)) {
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
                if (!array_key_exists('n', $options) && !array_key_exists('n_min', $options) && !array_key_exists('n_max', $options)) {
                    $options['n'] = 1;
                }
                if (!array_key_exists('player_id', $options)) {
                    $options['player_id'] = $player_id;
                }
                if (array_key_exists('location_from', $options) && ($options['location_from'] == 'deck' || $options['location_from'] == 'junk')) {
                    $options['owner_from'] = 0;
                }
                if (!array_key_exists('owner_from', $options)) {
                    $options['owner_from'] = $player_id;
                }
                if (array_key_exists('location_to', $options) && ($options['location_to'] == 'deck' || $options['location_to'] == 'junk')) {
                    $options['owner_to'] = 0;
                }
                if (!array_key_exists('owner_to', $options)) {
                    $options['owner_to'] = $player_id;
                }
                if (array_key_exists('choices', $options)) {
                    $options['choose_from_list'] = true;
                }
            }
        }

        switch($code) {

        // The first number is the id of the card
        // D1 means the first (and single) I demand effect
        // C1 means the first (and single) I compel effect
        // N1 means the first non-demand effect
        // N2 means the second non-demand effect
        // N3 means the third non-demand effect
        // E1 means the first (and single) echo effect
        
        // The letter indicates the step : A for the first one, B for the second
        
        // Setting the $step_max variable means there is interaction needed with the player
            
        // id 5, age 1: Oars
        case "5D1A":
            // "Transfer a card with a crown from your hand to my score pile"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                
                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => $launcher_id,
                'location_to' => 'score',
                
                'with_icon' => 1
            );
            break;

        // id 6, age 1: Clothing
        case "6N1A":
            // "Meld a card from your hand of different color of any card on your board"
            $board = self::getCardsInLocationKeyedByColor($player_id, 'board');
            $selectable_colors = array();
            for ($color=0; $color<5; $color++) {
                if (count($board[$color]) == 0) { // This is a color the player does not have
                    $selectable_colors[] = $color;
                }
            }
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                
                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => $player_id,
                'location_to' => 'board',
                
                'color' => $selectable_colors,
                'meld_keyword' => true,
            );
            break;
        
        // id 9, age 1: Agriculture
        case "9N1A":
            // "You may return a card from you hand"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => true,
                
                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => 0,
                'location_to' => 'deck'
            );
            break;
            
        // id 10, age 1: Domestication
        case "10N1A":
            // "Meld the lowest card in your hand"
            $age = self::getMinAgeInHand($player_id);
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                
                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => $player_id,
                'location_to' => 'board',
                
                'age' => $age,
                'meld_keyword' => true,
            );
            break;
        
        // id 11, age 1: Masonry
        case "11N1A":
            // "You may meld any number of cards from your hand, each with a tower"
            $options = array(
                'player_id' => $player_id,
                'n_min' => 1,
                'can_pass' => true,
                
                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => $player_id,
                'location_to' => 'board',
                
                'with_icon' => 4,
                'meld_keyword' => true,
            );
            break;
        
        // id 12, age 1: City states
        case "12D1A":
            // "Transfer a top card with a tower from your board to my board"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                
                'owner_from' => $player_id,
                'location_from' => 'board',
                'owner_to' => $launcher_id,
                'location_to' => 'board',
                
                'with_icon' => 4 
            );
            break;
            
        // id 13, age 1: Code of laws
        case "13N1A":
            // "You may tuck a card from your hand of the same color of any card on your board"
            $board = self::getCardsInLocationKeyedByColor($player_id, 'board');
            $selectable_colors = array();
            for ($color=0; $color<5; $color++) {
                if (count($board[$color]) > 0) { // This is a color the player already have
                    $selectable_colors[] = $color;
                }
            }
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => true,
                
                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => $player_id,
                'location_to' => 'board',
                'bottom_to' => true,
                
                'color' => $selectable_colors
            );
            break;
        
        case "13N1B":
            // "You may splay that color left"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => true,
                
                'splay_direction' => Directions::LEFT,
                'color' => array($this->innovationGameState->get('color_last_selected'))
            );
            break;
        
        // id 16, age 2: Mathematics
        case "16N1A":
            // "You may return a card from your hand"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => true,
                
                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => 0,
                'location_to' => 'deck'
            );
            break;
            
        // id 17, age 2: Construction
        case "17D1A":
            // "Transfer two cards from you hand to my hand"
            $options = array(
                'player_id' => $player_id,
                'n' => 2,
                
                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => $launcher_id,
                'location_to' => 'hand'
            );
            break;
            
        // id 18, age 2: Road building
        case "18N1A":
            // "Meld one or two cards from your hand"
            $options = array(
                'player_id' => $player_id,
                'n_min' => 1,
                'n_max' => 2,
                
                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => $player_id,
                'location_to' => 'board',

                'meld_keyword' => true,
            );
            break;
            
        case "18N1B":
            // "You may transfer your top red card to another player's board. If you do, meld that player's top green card."
            $options = array(
                'player_id' => $player_id,
                'can_pass' => true,
                
                'choose_player' => true,
                'players' => self::getOtherActivePlayers($player_id),

                // 4th edition says "meld that player's top green card" but earlier editions say "transfer that player's top green card to your board"
                'meld_keyword' => $this->innovationGameState->usingFourthEditionRules(),
            );
            break;
            
        // id 19, age 2: Currency
        case "19N1A":
            // "You may return any number of card from your hand"
            $options = array(
                'player_id' => $player_id,
                'n_min' => 1,
                'can_pass' => true,
                
                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => 0,
                'location_to' => 'deck'
            );
            break;
        
        // id 20, age 2: Mapmaking
        case "20D1A":
            // "Transfer a 1 from your score pile to my score pile"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                
                'owner_from' => $player_id,
                'location_from' => 'score',
                'owner_to' => $launcher_id,
                'location_to' => 'score',
                
                'age' => 1
            );
            break;
            
        // id 21, age 2: Canal building         
        case "21N1A":
            if ($this->innovationGameState->usingFourthEditionRules() && self::countCardsInLocationKeyedByAge(0, 'deck', CardTypes::BASE)[3] > 0) {
                // "You may choose to either exchange all the highest cards in your hand with all the highest cards in your score pile, or junk all cards in the 3 deck."
                $options = array(
                    'player_id' => $player_id,
                    'can_pass' => true,
                    'choose_yes_or_no' => true,
                );
            } else {
                // "You may exchange all the highest cards in your hand with all the highest cards in your score pile"
                $options = array(
                    'player_id' => $player_id,
                    'choose_yes_or_no' => true,
                );
            }
            break;
            
        // id 23, age 2: Monotheism        
        case "23D1A":
            // "I demand you transfer a top card on your board of different color from any card on my board to my score pile!"
            $board = self::getCardsInLocationKeyedByColor($launcher_id, 'board');
            $selectable_colors = array();
            for ($color=0; $color<5; $color++) {
                if (count($board[$color]) == 0) { // This is a color the player does not have
                    $selectable_colors[] = $color;
                }
            }
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                
                'owner_from' => $player_id,
                'location_from' => 'board',
                'owner_to' => $launcher_id,
                'location_to' => 'score',
                
                'color' => $selectable_colors,
            );
            break;
            
        // id 24, age 2: Philosophy        
        case "24N1A":
            // "You may splay any one color of your cards"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => true,
                
                'splay_direction' => Directions::LEFT
            );
            break;
        
        case "24N2A":
            // "You may score a card from your board"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => true,
                
                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => $player_id,
                'location_to' => 'score',
                
                'score_keyword' => true
            );
            break;
            
        // id 25, age 3: Alchemy        
        case "25N1A":
            // "Return the drawn cards and all cards from your hand"
            $options = array(
                'player_id' => $player_id,
                
                'owner_from' => $player_id,
                'location_from' => 'revealed,hand',
                'owner_to' => 0,
                'location_to' => 'deck',
            );
            break;
            
        case "25N2A":
            // "Meld a card from your hand"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                
                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => $player_id,
                'location_to' => 'board',

                'meld_keyword' => true,
            );
            break;
            
        case "25N2B":
            // "Score a card from your hand"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                
                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => $player_id,
                'location_to' => 'score',
                
                'score_keyword' => true
            );
            break;
            
        // id 26, age 3: Translation        
        case "26N1A":
            // "You may meld all the cards in your score pile. If you meld one, you must meld them all"
            $options = array(
                'player_id' => $player_id,
                'can_pass' => true,
                
                'owner_from' => $player_id,
                'location_from' => 'score',
                'owner_to' => $player_id,
                'location_to' => 'board',

                'meld_keyword' => true,
            );
            break;
        
        // id 27, age 3: Engineering        
        case "27N1A":
            // "You may splay your red cards left"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => true,
                
                'splay_direction' => Directions::LEFT,
                'color' => array(1) /* red */
            );
            break;
                
        // id 28, age 3: Optics
        case "28N1A":
            // "An opponent with fewer points than you"
            $options = array(
                'player_id' => $player_id,
                
                'choose_player' => true,
                'players' => self::getActiveOpponentsWithFewerPoints($player_id)
            );
            break;
            
        case "28N1B":
            // "Transfer a card from your score pile to the opponent score pile"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                
                'owner_from' => $player_id,
                'location_from' => 'score',
                'owner_to' => $this->innovationGameState->get('choice'), // ie the opponent chosen on the previous step
                'location_to' => 'score'
            );
            break;

        // id 29, age 3: Compass        
        case "29D1A":
            // "Transfer a top non-green card with a leaf from your board to my board"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                
                'owner_from' => $player_id,
                'location_from' => 'board',
                'owner_to' => $launcher_id,
                'location_to' => 'board',
                
                'color' => array(0,1,3,4) /* non-green */,
                'with_icon' => 2 /* with a leaf */
            );
            break;
            
        case "29D1B":
            // "Transfer a top card without a leaf from my board to your board"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                
                'owner_from' => $launcher_id,
                'location_from' => 'board',
                'owner_to' => $player_id,
                'location_to' => 'board',
                
                'without_icon' => 2 /* without a leaf */
            );
            break;
            
        // id 30, age 3: Paper
        case "30N1A_3E":
        case "30N1A_4E":
            // "You may splay your green or blue cards left"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => true,
                
                'splay_direction' => Directions::LEFT,
                'color' => array(0, 2) /* blue or green */
            );
            break;
            
        case "30N2A_4E":
            // "Score a top card with a leaf."
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                
                'owner_from' => $player_id,
                'location_from' => 'board',
                'owner_to' => $player_id,
                'location_to' => 'score',
                'score_keyword' => true,
                
                'with_icon' => 2 /* with a leaf */
            );
            break;
            
        // id 31, age 3: Machinery        
        case "31N1A_3E":
        case "31N1A_4E":
            // "Score a card from your hand with a tower"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                
                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => $player_id,
                'location_to' => 'revealed,score',
                
                'with_icon' => 4, /* tower */
                
                'score_keyword' => true,
            );
            break;
          
        case "31N1B_3E":
        case "31N2A_4E":
            // "You may splay your red cards left"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => true,
                
                'splay_direction' => Directions::LEFT,
                'color' => array(1) /* red */
            );
            break;

        // id 32, age 3: Medicine
        case "32D1A":
            // "... with the lowest card in my score pile"
            $options = array(
                'player_id' => $launcher_id,
                'n' => 1,
                
                'owner_from' => $launcher_id,
                'location_from' => 'score',
                'owner_to' => $player_id,
                'location_to' => 'score',
                
                'age' => self::getMinAgeInScore($launcher_id)
            );
            break;

        case "32N1A":
            // "Junk an available achievement of value 3 or 4"
            // NOTE: This only occurs in the 4th edition and beyond
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                
                'owner_from' => 0,
                'location_from' => 'achievements',
                'owner_to' => 0,
                'location_to' => 'junk',
                
                'age_min' => 3,
                'age_max' => 4,
            );
            break;
            
        case "32D1B":
            // "Exchange the highest card in your score pile..."
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                
                'owner_from' => $player_id,
                'location_from' => 'score',
                'owner_to' => $launcher_id,
                'location_to' => 'score',
                
                'age' => self::getMaxAgeInScore($player_id)
            );
            break;

            
        // id 33, age 3: Education        
        case "33N1A":
            // You may return the highest card from your score pile
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => true,
                
                'owner_from' => $player_id,
                'location_from' => 'score',
                'owner_to' => 0,
                'location_to' => 'deck',
                
                'age' => self::getMaxAgeInScore($player_id)
            );
            break;

        // id 34, age 3: Feudalism        
        case "34D1A":
            // "Transfer a card with a tower from your hand to my hand"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                
                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => $launcher_id,
                'location_to' => 'hand',
                
                'with_icon' => 4 /* tower */
            );
            break;
            
        case "34N1A":
            // "You may splay your yellow or purple cards left"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => true,
                
                'splay_direction' => 1, /* left */
                'color' => array(3, 4) /* yellow or purple */
            );
            break;
            
        // id 36, age 4: Printing press        
        case "36N1A":
            // "You may return a card from your score pile"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => true,
                
                'owner_from' => $player_id,
                'location_from' => 'score',
                'owner_to' => 0,
                'location_to' => 'deck'
            );
            break;
            
        case "36N2A":
            // "You may splay your blue cards right"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => true,
                
                'splay_direction' => Directions::RIGHT,
                'color' => array(0) /* blue */
            );
            break;
            
        // id 38, age 4: Gunpowder
        case "38D1A":
            // "Transfer a top card with a tower from your board to my score pile"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                
                'owner_from' => $player_id,
                'location_from' => 'board',
                'owner_to' => $launcher_id,
                'location_to' => 'score',
                
                'with_icon' => 4 /* tower */
            );
            break;
        
        // id 39, age 4: Invention
        case "39N1A":
            $splayed_left_colors = array();
            for ($color = 0; $color < 5; $color++) {
                if (self::getCurrentSplayDirection($player_id, $color) == Directions::LEFT) {
                    $splayed_left_colors[] = $color;
                }
            }
            // "You may splay right any one color of your cards currently splayed left"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => true,
                
                'splay_direction' => Directions::RIGHT,
                'color' => $splayed_left_colors
            );
            break;
            
        // id 40, age 4: Navigation
        case "40D1A":
            // "Transfer a 2 or a 3 from your score pile to my score pile"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                
                'owner_from' => $player_id,
                'location_from' => 'score',
                'owner_to' => $launcher_id,
                'location_to' => 'score',
                
                'age_min' => 2,
                'age_max' => 3,
            );
            break;
            
        // id 41, age 4: Anatomy
        case "41D1A":
            // "Return a card from your score pile"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                
                'owner_from' => $player_id,
                'location_from' => 'score',
                'owner_to' => 0,
                'location_to' => 'deck',
            );
            break;
        
        case "41D1B":
            // "Return a card of equal value from your board"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                
                'owner_from' => $player_id,
                'location_from' => 'board',
                'owner_to' => 0,
                'location_to' => 'deck',
                
                'age' => self::getAuxiliaryValue(),
                // TODO(LATER): We could just use faceup age instead of this unecessary hack.
                'not_id' => 188, // Battleship Yamato should not be returned even if an 8 is returned from the score pile
            );
            break;
            
        // id 42, age 4: Perspective
        case "42N1A":
            // "You may return a card from your hand"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => true,
                
                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => 0,
                'location_to' => 'deck',
            );
            break;
        
        case "42N1B":
            $number_of_lightbulbs = self::getPlayerSingleRessourceCount($player_id, 3 /* lightbulb */);
            self::notifyPlayer($player_id, 'log', clienttranslate('${You} have ${n} ${lightbulbs}.'), array('You' => 'You', 'n' => $number_of_lightbulbs, 'lightbulbs' => $lightbulb));
            self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} has ${n} ${lightbulbs}.'), array('player_name' => self::renderPlayerName($player_id), 'n' => $number_of_lightbulbs, 'lightbulbs' => $lightbulb));
            // "Score a card from your hand for every two lightbulbs on your board"
            $options = array(
                'player_id' => $player_id,
                'n' => self::intDivision($number_of_lightbulbs, 2),
                
                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => $player_id,
                'location_to' => 'score',
                
                'score_keyword' => true
            );
            break;
            
        // id 43, age 4: Enterprise
        case "43D1A":
            // "Transfer a top non-purple card with a crown from your board to my board"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                
                'owner_from' => $player_id,
                'location_from' => 'board',
                'owner_to' => $launcher_id,
                'location_to' => 'board',
                
                'color' => array(0,1,2,3) /* non-purple */,
                'with_icon' => 1 /* with a crown */
            );
            break;
        
        case "43N1A":
            // "You may splay your green cards right"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => true,
                
                'splay_direction' => Directions::RIGHT,
                'color' => array(2) /* green */
            );
            break;
        
        // id 44, age 4: Reformation
        case "44N1A":
            $number_of_leaves = self::getPlayerSingleRessourceCount($player_id, 2);
            self::notifyPlayer($player_id, 'log', clienttranslate('${You} have ${n} ${leaves}.'), array('You' => 'You', 'n' => $number_of_leaves, 'leaves' => $leaf));
            self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} has ${n} ${leaves}.'), array('player_name' => self::renderPlayerName($player_id), 'n' => $number_of_leaves, 'leaves' => $leaf));
            // "You may tuck a card from your hand for every two leaves on your board"
            $options = array(
                'player_id' => $player_id,
                'n' => self::intDivision($number_of_leaves, 2),
                'can_pass' => true,
                
                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => $player_id,
                'location_to' => 'board',
                
                'bottom_to' => true
            );
            break;
        
        case "44N2A":
            // "You may splay your yellow or purple cards right"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => true,
                
                'splay_direction' => Directions::RIGHT,
                'color' => array(3,4) /* yellow, purple */
            );
            break;

        // id 45, age 5: Chemistry
        case "45N1A":
            // "You may splay your blue cards right"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => true,
                
                'splay_direction' => Directions::RIGHT,
                'color' => array(0) /* blue */
            );
            break;
        
        case "45N2A":
            // "Return a card from your score pile"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                
                'owner_from' => $player_id,
                'location_from' => 'score',
                'owner_to' => 0,
                'location_to' => 'deck',
            );
            break;
            
        // id 46, age 5: Physics        
        case "46N1A":
            // "Return the drawn cards and all cards from your hand"
            $options = array(
                'player_id' => $player_id,
                
                'owner_from' => $player_id,
                'location_from' => 'revealed,hand',
                'owner_to' => 0,
                'location_to' => 'deck',
            );
            break;

        // id 47, age 5: Coal
        case "47N2A":
            // "You may splay your red cards right"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => true,
                
                'splay_direction' => Directions::RIGHT,
                'color' => array(1) /* red */
            );
            break;
            
        case "47N3A":
            // "You may score any one of your top cards"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => true,
                
                'owner_from' => $player_id,
                'location_from' => 'board',
                'owner_to' => $player_id,
                'location_to' => 'score',
                
                'score_keyword' => true
            );
            break;
        
        // id 48, age 5: The pirate code
        case "48D1A":
            // "Transfer two cards of value 4 or less from your score pile to my score pile"
            $options = array(
                'player_id' => $player_id,
                'n' => 2,
                
                'owner_from' => $player_id,
                'location_from' => 'score',
                'owner_to' => $launcher_id,
                'location_to' => 'score',
                
                'age_max' => 4
            );
            break;

        case "48N1A":
            // "Score the lowest top card with a crown from your board"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                
                'owner_from' => $player_id,
                'location_from' => 'board',
                'owner_to' => $player_id,
                'location_to' => 'score',
                
                'age' => self::getMinAgeOnBoardTopCardsWithIcon($player_id, 1 /* crown */),
                'with_icon' => 1, /* crown */

                'score_keyword' => true
            );
            break;

        // id 49, age 5: Banking
        case "49D1A":
            // "Transfer a top non-green card with a factory from your board to my board"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                
                'owner_from' => $player_id,
                'location_from' => 'board',
                'owner_to' => $launcher_id,
                'location_to' => 'board',
                
                'color' => array(0,1,3,4) /* non-green */,
                'with_icon' => 5 /* with a factory */
            );
            break;

        case "49N1A":
            // "You may splay your green cards right"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => true,
                
                'splay_direction' => Directions::RIGHT,
                'color' => array(2) /* green */
            );
            break;

        // id 50, age 5: Measurement
        case "50N1A":
            if ($this->innovationGameState->usingFirstEditionRules()) {
                // "You may return a card from your hand"
                $options = array(
                    'player_id' => $player_id,
                    'n' => 1,
                    'can_pass' => true,
                    
                    'owner_from' => $player_id,
                    'location_from' => 'hand',
                    'owner_to' => 0,
                    'location_to' => 'deck',
                );
            } else {
                // "You may reveal and return a card from your hand"
                $options = array(
                    'player_id' => $player_id,
                    'n' => 1,
                    'can_pass' => true,
                    
                    'owner_from' => $player_id,
                    'location_from' => 'hand',
                    'owner_to' => $player_id,
                    'location_to' => 'revealed,deck',
                );
            }
            break;
            
        case "50N1B":
            // "Choose a color"
            $options = array(
                'player_id' => $player_id,
                
                'choose_color' => true
            );
            break;

        // id 51, age 5: Statistics
        case "51D1A":
            // First edition only
            // "I demand you transfer the highest card in your score pile to your hand"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                
                'owner_from' => $player_id,
                'location_from' => 'score',
                'owner_to' => $player_id,
                'location_to' => 'hand',
                
                'age' => self::getMaxAgeInScore($player_id)
            );
            break;

        case "51N1A":
            // "You may splay your yellow cards right"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => true,
                
                'splay_direction' => Directions::RIGHT,
                'color' => array(3) /* yellow */
            );
            break;
        
        // id 54, age 5: Societies
        case "54D1A":
            // Last edition: "Transfer a card with a lightbulb higher than my top card of the same color from your board to my board"
            // First edition: "Transfer a top non-purple card with a lightbulb from your board to my board"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                
                'owner_from' => $player_id,
                'location_from' => 'board',
                'owner_to' => $launcher_id,
                'location_to' => 'board',
                
                'color' => self::getAuxiliaryValueAsArray(),
                'with_icon' => 3 /* with a lightbulb */
            );
            break;
            
        // id 55, age 6: Atomic theory
        case "55N1A":
            // "You may splay your blue cards right"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => true,
                
                'splay_direction' => Directions::RIGHT,
                'color' => array(0) /* blue */
            );
            break;
            
        // id 56, age 6: Encyclopedia
        case "56N1A":
            // "You may meld all the highest cards on your score pile. If you meld one, you must meld them all"
            $options = array(
                'player_id' => $player_id,
                'can_pass' => true,
                
                'owner_from' => $player_id,
                'location_from' => 'score',
                'owner_to' => $player_id,
                'location_to' => 'board',

                'meld_keyword' => true,
                'age' => self::getMaxAgeInScore($player_id),
            );
            break;

        case "56N2A":
            // "You may junk an available achievement of value 5, 6, or 7."
            // NOTE: This only occurs in the 4th edition and beyond
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => true,
                
                'owner_from' => 0,
                'location_from' => 'achievements',
                'owner_to' => 0,
                'location_to' => 'junk',
                
                'age_min' => 5,
                'age_max' => 7,
            );
            break;
            
        // id 57, age 6: Industrialisation
        case "57N2A":
            // "You may splay your red or purple cards right"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => true,
                
                'splay_direction' => Directions::RIGHT,
                'color' => array(1,4) /* red or purple */
            );
            break;

        // id 59, age 6: Classification
        case "59N1A":
            // "Reveal the color of a card from your hand"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                
                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => $player_id,
                'location_to' => 'revealed'
            );
            break;
            
        case "59N1B":
            // "Meld of cards of that color from your hand"
            $options = array(
                'player_id' => $player_id,
                
                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => $player_id,
                'location_to' => 'board',

                'meld_keyword' => true,
                'color' => array(self::getAuxiliaryValue()), /* The color the player has revealed */
            );
            break;
        
        // id 60, age 6: Metric system
        case "60N1A":
            // "You may splay any one color of your cards right"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => true,
                
                'splay_direction' => Directions::RIGHT
            );
            break;
        
        case "60N2A":
            // "You may splay your green cards right"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => true,
                
                'splay_direction' => 2, /* right */
                'color' => array(2) /* green */
            );
            break;
            
        // id 61, age 6: Canning
        case "61N1A":
            // "You may draw and tuck a 6"
            $options = array(
                'player_id' => $player_id,
                
                'choose_yes_or_no' => true
            );
            break;
        
        case "61N2A":
            // "You may splay your yellow cards right"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => true,
                
                'splay_direction' => Directions::RIGHT,
                'color' => array(3) /* yellow */
            );
            break;
        
        // id 62, age 6: Vaccination
        case "62D1A":
            // "Return all the lowest cards in your score pile"
            $options = array(
                'player_id' => $player_id,
                
                'owner_from' => $player_id,
                'location_from' => 'score',
                'owner_to' => 0,
                'location_to' => 'deck',
                
                'age' => self::getMinAgeInScore($player_id)
            );
            break;
            
        // id 63, age 6: Democracy          
        case "63N1A":
            // "You may return any number of cards from your hand"
            $options = array(
                'player_id' => $player_id,
                'n_min' => 1,
                'can_pass' => true,
                
                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => 0,
                'location_to' => 'deck'
            );
            break;
            
        // id 64, age 6: Emancipation
        case "64D1A":
            // "Transfer a card from your hand to my score pile"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                
                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => $launcher_id,
                'location_to' => 'score'
            );
            break;
        
        case "64N1A":
            // "You may splay your red or purple cards right"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => true,
                
                'splay_direction' => Directions::RIGHT,
                'color' => array(1,4) /* red or purple */
            );
            break;
        
        // id 66, age 7: Publications
        case "66N1A_3E":
            // "You may rearrange the order of one color of cards on your board"
            $options = array(
                'player_id' => $player_id,
                'can_pass' => true,
                
                'choose_rearrange' => true
            );
            break;
        
        case "66N2A_3E":
        case "66N1A_4E":
            // "You may splay your blue or yellow cards up"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => true,
                
                'splay_direction' => 3 /* up */,
                'color' => array(0,3) /* blue or yellow */
            );
            break;

        case "66N2A_4E":
            // "You may junk an available special achievement or make a junked special achievement available"
            $options = array(
                'player_id' => $player_id,
                'can_pass' => true,
                
                'choose_special_achievement' => true,
            );
            break;
            
        // id 67, age 7: Combustion
        case "67D1A":
            // Last edition => "Transfer one card from your score pile to my score pile for every four crown on my board"
            // First edition => "Transfer two cards from your score pile to my score pile"
            // Cf A
            $options = array(
                'player_id' => $player_id,
                'n' => self::getAuxiliaryValue(),
                
                'owner_from' => $player_id,
                'location_from' => 'score',
                'owner_to' => $launcher_id,
                'location_to' => 'score'
            );
            break;
            
        // id 68, age 7: Explosives
        case "68D1A":
        case "68D1B":
        case "68D1C":
            // "Transfer the highest cards from your hand to my hand" (three times)
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                
                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => $launcher_id,
                'location_to' => 'hand',
                
                'age' => self::getMaxAgeInHand($player_id)
            );
            break;
            
        // id 69, age 7: Bicycle      
        case "69N1A":
            // "You may exchange all the highest cards in your hand with all the highest cards in your score pile"
            $options = array(
                'player_id' => $player_id,
                
                'choose_yes_or_no' => true
            );
            break;

        // id 70, age 7: Electricity
        case "70N1A":
            // "Return all your top cards without a factory"
            $options = array(
                'player_id' => $player_id,
                
                'owner_from' => $player_id,
                'location_from' => 'board',
                'owner_to' => 0,
                'location_to' => 'deck',
                
                'without_icon' => 5, /* factory */
            );
            break;
        
        // id 71, age 7: Refrigeration
        case "71D1A":
            $hand_count = self::countCardsInLocation($player_id, 'hand');
            if ($this->innovationGameState->usingFourthEditionRules()) {
                // "I demand you return all but one of the cards in your hand!"
                $num_cards_to_return = $hand_count == 0 ? 0 : $hand_count - 1;
            } else {  
                // "Return half (rounded down) of the cards in your hand"
                $num_cards_to_return = self::intDivision($hand_count, 2);
            }
            
            $options = array(
                'player_id' => $player_id,
                'n' => $num_cards_to_return,
                
                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => 0,
                'location_to' => 'deck',
            );
            break;
            
        case "71N1A":
            // "You may score a card from your hand"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => true,
                
                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => $player_id,
                'location_to' => 'score',
                
                'score_keyword' => true
            );
            break;
            
        // id 73, age 7: Lighting        
        case "73N1A":
            // "You may tuck up to three cards from your hand"
            $options = array(
                'player_id' => $player_id,
                'n_min' => 1,
                'n_max' => 3,
                'can_pass' => true,
                
                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => $player_id,
                'location_to' => 'board',
                
                'bottom_to' => true
            );
            break;
        
        // id 74, age 7: Railroad        
        case "74N1A_3E":
        case "74N1A_4E":
            // "Return all the cards in your hand"
            $options = array(
                'player_id' => $player_id,
                
                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => 0,
                'location_to' => 'deck',
            );
            break;
        
        case "74N2A_3E":
        case "74N3A_4E":
            $splayed_right_colors = array();
            for ($color = 0; $color < 5; $color++) {
                if (self::getCurrentSplayDirection($player_id, $color) == Directions::RIGHT) {
                    $splayed_right_colors[] = $color;
                }
            }
            // "You may splay up any one color of your cards currently splayed right"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => true,
                
                'splay_direction' => 3 /* up */,
                'color' => $splayed_right_colors
            );
            break;
            
        // id 75, age 8: Quantum theory        
        case "75N1A":
            // "You may return up to two cards from your hand"
            $options = array(
                'player_id' => $player_id,
                'n_min' => 1,
                'n_max' => 2,
                'can_pass' => true,
                
                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => 0,
                'location_to' => 'deck',
            );
            break;
        
        // id 76, age 8: Rocketry       
        case "76N1A":
            $number_of_clocks = self::getPlayerSingleRessourceCount($player_id, 6 /* clock */);
            self::notifyPlayer($player_id, 'log', clienttranslate('${You} have ${n} ${clocks}.'), array('You' => 'You', 'n' => $number_of_clocks, 'clocks' => $clock));
            self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} has ${n} ${clocks}.'), array('player_name' => self::renderPlayerName($player_id), 'n' => $number_of_clocks, 'clocks' => $clock));
            // "Return a card in any opponent's score pile for every two clocks on your board"
            $options = array(
                'player_id' => $player_id,
                'n' => self::intDivision($number_of_clocks, 2),
                
                'owner_from' => 'any opponent',
                'location_from' => 'score',
                'owner_to' => 0,
                'location_to' => 'deck'
            );
            break;
        
        // id 77, age 8: Flight
        case "77N1A":
            // "You may splay any one color of your cards up"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => true,
                
                'splay_direction' => 3 /* up */
            );
            break;
        
        case "77N2A":
            // "You may splay your red cards up"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => true,
                
                'splay_direction' => 3, /* up */
                'color' => array(1) /* red */
            );
            break;
        
        // id 78, age 8: Mobility        
        case "78D1A":
        case "78D1B":
            // "Transfer your highest non-red top cards without a factory from your board to my score pile" (twice)
            $selectable_colors = self::getAuxiliaryValueAsArray(); /* not red, and for the second card, not the same color as the first */
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                
                'owner_from' => $player_id,
                'location_from' => 'board',
                'owner_to' => $launcher_id,
                'location_to' => 'score',
                
                'age' => self::getMaxAgeOnBoardOfColorsWithoutIcon($player_id, $selectable_colors, 5 /* with no factory*/),
                'color' => $selectable_colors,
                'without_icon' => 5 /* factory */
            );
            break;
        
        // id 79, age 8: Corporations        
        case "79D1A":
            // "Transfer a top non-green card with a factory from your board to my score pile"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                
                'owner_from' => $player_id,
                'location_from' => 'board',
                'owner_to' => $launcher_id,
                'location_to' => 'score',
                
                'color' => array(0,1,3,4), /* not green */
                'with_icon' => 5 /* factory */
            );
            break;
            
        // id 80, age 8: Mass media         
        case "80N1A":
            // "You may return a card from your hand"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => true,
                
                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => 0,
                'location_to' => 'deck'
            );
            break;

        case "80N1B":
            // "Choose a value"
            $options = array(
                'player_id' => $player_id,
                
                'choose_value' => true
            );
            break;
            
        case "80N1C":
            // "Return all cards of that value from all score piles"
            $options = array(
                'player_id' => $player_id,
                
                'owner_from' => 'any player',
                'location_from' => 'score',
                'owner_to' => 0,
                'location_to' => 'deck',
                
                'age' => self::getAuxiliaryValue()
            );
            break;
            
        case "80N2A":
            // "You may splay your purple cards up"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => true,
                
                'splay_direction' => 3, /* up */
                'color' => array(4) /* purple */
            );
            break;
        
        // id 81, age 8: Antibiotics
        case "81N1A":
            // "You may return up to three cards from your hand"
            $options = array(
                'player_id' => $player_id,
                'n_min' => 1,
                'n_max' => 3,
                'can_pass' => true,
                
                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => 0,
                'location_to' => 'deck'
            );
            break;
    
        // id 82, age 8: Skyscrapers
        case "82D1A":
            // "Transfer a top non-yellow card with a clock from your board to my board"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                
                'owner_from' => $player_id,
                'location_from' => 'board',
                'owner_to' => $launcher_id,
                'location_to' => 'board',
                
                'color' => array(0,1,2,4), /* not yellow */
                'with_icon' => 6 /* clock */
             );
            break;
        
        case "82D1B":
            // "Return all the cards from that pile"
            $options = array(
                'player_id' => $player_id,
                
                'owner_from' => $player_id,
                'location_from' => 'pile',
                'owner_to' => 0,
                'location_to' => 'deck',
                
                'color' => array(self::getAuxiliaryValue()), /* the color of the card chosen on the first step */
             );
            break;
        
        // id 83, age 8: Empiricism     
        case "83N1A":
            // "Choose two colors"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                
                'choose_two_colors' => true 
            );
            break;
            
        case "83N1B":
            // "You may splay that color of your cards up"
            // NOTE: The 4th edition does not say "you may"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => !$this->innovationGameState->usingFourthEditionRules(),
                
                'splay_direction' => 3, /* up */
                'color' => array(self::getAuxiliaryValue())
            );            
            break;
        
        // id 84, age 8: Socialism     
        case "84N1A_3E":
            // "You may tuck all cards from your hand"
            $options = array(
                'player_id' => $player_id,
                'can_pass' => true,
                
                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => $player_id,
                'location_to' => 'board',
                
                'bottom_to' => true
            );    
            break;

        case "84N1A_4E":
            // "You may tuck a top card from your board"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => true,
                
                'owner_from' => $player_id,
                'location_from' => 'board',
                'owner_to' => $player_id,
                'location_to' => 'board',
                
                'bottom_to' => true,
            );    
            break;

        case "84N1B_4E":
            // "Tuck all cards from your hand"
            $options = array(
                'player_id' => $player_id,
                
                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => $player_id,
                'location_to' => 'board',
                
                'bottom_to' => true,
            );    
            break;

        case "84N2A_4E":
            // "You may junk an available achievement of value 8, 9, or 10"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => true,
                
                'owner_from' => 0,
                'location_from' => 'achievements',
                'owner_to' => 0,
                'location_to' => 'junk',

                'age_min' => 8,
                'age_max' => 10,
            );    
            break;
        
        // id 85, age 9: Computers     
        case "85N1A":
            // "You may splay your red or green cards up"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => true,
                
                'splay_direction' => 3, /* up */
                'color' => array(1,2) /* red or green */
            );
            break;

        // id 87, age 9: Composites     
        case "87D1A":
            // "Transfer all but one card from your hand to my hand"
            $options = array(
                'player_id' => $player_id,
                'n' => self::countCardsInLocation($player_id, 'hand') - 1,
                
                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => $launcher_id,
                'location_to' => 'hand'
            );        
            break;
            
        case "87D1B":
            // "Transfer the highest card from your score pile to my score pile"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                
                'owner_from' => $player_id,
                'location_from' => 'score',
                'owner_to' => $launcher_id,
                'location_to' => 'score',
                
                'age' => self::getMaxAgeInScore($player_id)
            );
            break;
        
        // id 88, age 9: Fission
        case "88N1A":
            // "Return a top card other than Fission from any player board"
             $options = array(
                'player_id' => $player_id,
                'n' => 1,
                
                'owner_from' => 'any player',
                'location_from' => 'board',
                'owner_to' => 0,
                'location_to' => 'deck',
                
                'not_id' => 88 /* Fission */
            );
            break;
        
        // id 89, age 9: Collaboration
        case "89D1A":
            // "Transfer the card of my choice to my board"
            $options = array(
                'player_id' => $launcher_id,
                'n' => 1,
                
                'owner_from' => $player_id,
                'location_from' => 'revealed',
                'owner_to' => $launcher_id,
                'location_to' => 'board'
            );
            break;        
        
        // id 90, age 9: Satellites
        case "90N1A_3E":
        case "90N1A_4E":
            // "Return all cards from your hand"
            $options = array(
                'player_id' => $player_id,
                
                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => 0,
                'location_to' => 'deck'
            );
            break;

        case "90N2A_3E":
        case "90N1B_4E":
            // "You may splay your purple cards up"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => true,
                
                'splay_direction' => 3, /* up */
                'color' => array(4) /* purple */
            );
            break;
            
        case "90N3A_3E":
        case "90N3A_4E":
            // "Meld a card from your hand"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                
                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => $player_id,
                'location_to' => 'board',

                'meld_keyword' => true,
            );
            break;        

        // id 91, age 9: Ecology
        case "91N1A":
            // "You may return a card from you hand"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => true,
                
                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => 0,
                'location_to' => 'deck'
            );
            break;
            
        case "91N1B":
            // "Score a card from you hand"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                
                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => $player_id,
                'location_to' => 'score',
                
                'score_keyword' => true
            );
            break;
            
        case "91N2A":        
            // "You may junk all cards in the 10 deck."
            $options = array(
                'player_id' => $player_id,
                'can_pass' => true,
                'choose_yes_or_no' => true,
            );
            break;
        
        // id 92, age 9: Suburbia
        case "92N1A":
            // "You may tuck any number of cards from your hand"
            $options = array(
                'player_id' => $player_id,
                'n_min' => 1,
                'can_pass' => true,
                
                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => $player_id,
                'location_to' => 'board',
                
                'bottom_to' => true
            );
            break;

        case "92N2A":        
            // "You may junk all cards in the 9 deck."
            $options = array(
                'player_id' => $player_id,
                'can_pass' => true,
                'choose_yes_or_no' => true,
            );
            break;
            
        // id 93, age 9: Services
        case "93D1A":
            // "Transfer a top card from my board without a leaf to your hand"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                
                'owner_from' => $launcher_id,
                'location_from' => 'board',
                'owner_to' => $player_id,
                'location_to' => 'hand',
                
                'without_icon' => 2 /* leaf */
            );
            break;
        
        // id 94, age 9: Specialization
        case "94N1A":
            // "Reveal a card from your hand"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                
                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => $player_id,
                'location_to' => 'revealed'
            );
            break;

        case "94N2A":
            // "You may splay your yellow or blue cards up"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => true,
                
                'splay_direction' => 3, /* up */
                'color' => array(0,3) /* blue or yellow */
            );
            break;
        
        // id 95, age 10: Bioengineering
        case "95N1A":
            // "Transfer a top card with a leaf from any opponent's board to your score pile"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                
                'owner_from' => 'any opponent',
                'location_from' => 'board',
                'owner_to' => $player_id,
                'location_to' => 'score',
                
                'with_icon' => 2, /* leaf */

                // Fourth edition: "Score a top card with a leaf on any opponent's board."
                'score_keyword' => $this->innovationGameState->usingFourthEditionRules(),
            );
            break;
        
        // id 97, age 10: Miniaturization
        case "97N1A":
            // "You may return a card from you hand"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => $this->innovationGameState->getEdition() <= 3, // Fourth edition doesn't say "You may"
                
                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => 0,
                'location_to' => 'deck'
            );
            break;
        
        // id 99, age 10: Databases
        case "99D1A":
            // "Return half (rounded up) of the cards in your score pile"
            $options = array(
                'player_id' => $player_id,
                'n' => ceil(self::countCardsInLocation($player_id, 'score') / 2),
                
                'owner_from' => $player_id,
                'location_from' => 'score',
                'owner_to' => 0,
                'location_to' => 'deck'
            );        
            break;
        
        // id 100, age 10: Self service
        case "100N1A": // 3rd edition and earlier
        case "100N2A": // 4th edition
            // "Execute each of the non-demand dogma effects of any other top card on your board" (a card with no non-demand effect can be chosen)
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                
                'owner_from' => $player_id,
                'location_from' => 'board',
                'owner_to' => $player_id, // Nothing is to be done with that card
                'location_to' => 'none',
                
                // Exclude the card currently being executed (it's possible for the effects of Self Service to be executed as if it were on another card)
                'not_id' => self::getCurrentNestedCardState()['executing_as_if_on_card_id'],
            );       
            break;
        
        // id 101, age 10: Globalization
        case "101D1A":
            // "Return a top card with a ${icon_2} on your board" 
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                
                'owner_from' => $player_id,
                'location_from' => 'board',
                'owner_to' => 0,
                'location_to' => 'deck',
                
                'with_icon' => 2 /* leaf */
            );
            break;
        
        // id 102, age 10: Stem cells
        case "102N1A":
            // The player faces a choice
            $options = array(
                'player_id' => $player_id,
                
                'choose_yes_or_no' => true
            );
            break;
        
        // id 104, age 10: The internet.
        case "104N1A":
            // "You may splay your green cards up"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => true,
                
                'splay_direction' => 3, /* up */
                'color' => array(2) /* green */
            );
            break;

        // id 136, Artifacts age 3: Charter of Liberties
        case "136N1A":
            // "Tuck a card from your hand"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,

                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => $player_id,
                'location_to' => 'board',
                
                'bottom_to' => true
            );
            break;

        case "136N1B":
            // "Choose a splayed color on any player's board"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,

                'owner_from' => 'any player',
                'location_from' => 'board',
                'location_to' => 'none',
                
                'has_splay_direction' => array(1, 2, 3, 4) // Left, right, up, or aslant
            );
            break;

        // id 137, Artifacts age 2: Excalibur
        case "137C1A":
            // "I compel you to transfer a top card of higher value than my
            // top card of the same color from your board to my board!"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                
                'owner_from' => $player_id,
                'location_from' => 'board',
                'owner_to' => $launcher_id,
                'location_to' => 'board',
                
                'color' => self::getAuxiliaryValueAsArray()
            );
            break;
            
        // id 138, Artifacts age 3: Mjolnir Amulet
        case "138C1A":
            // "I compel you to choose a top card on your board"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                
                'owner_from' => $player_id,
                'location_from' => 'board',
                'owner_to' => $player_id,
                'location_to' => 'none',
            );
            break;
            
        // id 139, Artifacts age 3: Philosopher's Stone
        case "139N1A":
            // "Return a card from your hand"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,

                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => 0,
                'location_to' => 'deck'
            );
            break;

        case "139N1B":
            // "Score a number of cards from your hand equal to the value of the card returned"
            $age_selected = $this->innovationGameState->get('age_last_selected');
            $options = array(
                'player_id' => $player_id,
                'n' => $age_selected,
                
                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => $player_id,
                'location_to' => 'score',

                'score_keyword' => true
            );
            break;

        // id 141, Artifacts age 3: Moylough Belt Shrine
        case "141C1A":
            // "Transfer the card of my choice to my board"
            $options = array(
                'player_id' => $launcher_id,
                'n' => 1,
                
                'owner_from' => $player_id,
                'location_from' => 'revealed',
                'owner_to' => $launcher_id,
                'location_to' => 'board'
            );
            break;

        // id 143, Artifacts age 3: Necronomicon
        case "143N1A":
            // "If red, return all cards in your score pile"
            if (self::getAuxiliaryValue() == 1) { // Red
                $options = array(
                    'player_id' => $player_id,
                    'can_pass' => false,
                    
                    'owner_from' => $player_id,
                    'location_from' => 'score',
                    'owner_to' => 0,
                    'location_to' => 'deck'
                );     
            // "If yellow, return all cards in your hand"
            } else if (self::getAuxiliaryValue() == 3) { // Yellow
                $options = array(
                    'player_id' => $player_id,
                    'can_pass' => false,
                    
                    'owner_from' => $player_id,
                    'location_from' => 'revealed,hand',
                    'owner_to' => 0,
                    'location_to' => 'deck',
                );
            };
            break;
        
        // id 144, Artifacts age 3: Shroud of Turin
        case "144N1A":
            // "Return a card from hand"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,

                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => $player_id,
                'location_to' => 'revealed,deck'
            );
            break;

        case "144N1B":
            // "Return a top card from your board of the returned card's color"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,

                'owner_from' => $player_id,
                'location_from' => 'board',
                'owner_to' => 0,
                'location_to' => 'deck',

                'color' => array($this->innovationGameState->get('color_last_selected'))
            );
            break;

        case "144N1C":
            // "Return a card from score pile of the returned card's color"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,

                'owner_from' => $player_id,
                'location_from' => 'score',
                'owner_to' => $player_id,
                'location_to' => 'revealed,deck',

                'color' => array($this->innovationGameState->get('color_last_selected'))
            );
            break;

        case "144N1D":
            // "Claim an achievement ignoring eligibility"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,

                'owner_from' => 0,
                'location_from' => 'achievements',
                'owner_to' => $player_id,
                'location_to' => 'achievements',

                'require_achievement_eligibility' => false
            );
            break;

        // id 145, Artifacts age 4: Petition of Right
        case "145C1A":    
            // "I compel you to transfer a card from your score pile to my score pile for each top card with a tower on your board!"
            $options = array(
                'player_id' => $player_id,
                'n' => self::getAuxiliaryValue(),
                
                'owner_from' => $player_id,
                'location_from' => 'score',
                'owner_to' => $launcher_id,
                'location_to' => 'score'
            );
            break;

        // id 146, Artifacts age 4: Delft Pocket Telescope
        case "146N1A":
            // "Return a card from your score pile"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,

                'owner_from' => $player_id,
                'location_from' => 'score',
                'owner_to' => $player_id,
                'location_to' => 'revealed,deck'
            );
            break;
            
        case "146N1B":            
            // "Return the drawn cards"
             $options = array(
                'player_id' => $player_id,
                'n' => 2,

                'owner_from' => $player_id,
                'location_from' => 'revealed',
                'owner_to' => 0,
                'location_to' => 'deck',

                'card_id_1' => $card_id_1,
                'card_id_2' => $card_id_2
            );            
            break;

        case "146N1C":
            // "Reveal one of the drawn cards that has a symbol in common with the returned card"   
            $options = array(
                'player_id' => $player_id,
                'n' => 1,

                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => $player_id,
                'location_to' => 'revealed',

                'card_id_1' => $card_id_1,
                'card_id_2' => $card_id_2,

                'enable_autoselection' => false, // Automating this always reveals hidden info
            );
            break;

        // id 147, Artifacts age 4: East India Company Charter
        case "147N1A":
            // "Choose a value other than 5"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                
                'choose_value' => true,

                'age' => array(1, 2, 3, 4, 6, 7, 8, 9, 10, 11)
            );
            break;

        case "147N1B":
            // "Return all cards of that value from all score piles"
            $value_to_return = self::getAuxiliaryValue();
            $num_players_who_returned = 0;
            foreach (self::getAllActivePlayerIds() as $id) {
                $score_pile = self::getCardsInLocation($id, 'score');
                foreach ($score_pile as $card) {
                    if ($card['age'] == $value_to_return) {
                        $num_players_who_returned++;
                        break;
                    }
                }
            }
            self::setAuxiliaryValue($num_players_who_returned);
            
            $options = array(
                'player_id' => $player_id,
                
                'owner_from' => 'any player',
                'location_from' => 'score',
                'owner_to' => 0,
                'location_to' => 'deck',
                
                'age' => $value_to_return
            );
            break;

        // id 148, Artifacts age 4: Tortugas Galleon
        case "148C1A":
            // "Transfer a top card on your board of that value to my board"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                
                'owner_from' => $player_id,
                'location_from' => 'board',
                'owner_to' => $launcher_id,
                'location_to' => 'board',

                'age' => self::getAuxiliaryValue()
            );
            break;
            
        // id 149, Artifacts age 4: Molasses Reef Caravel
        case "149N1A":
            // "Return all cards from your hand"
            $options = array(
                'player_id' => $player_id,

                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => 0,
                'location_to' => 'deck'
            );
            break;
            
        case "149N1B":
            // "Meld a blue card from your hand"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,

                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => $player_id,
                'location_to' => 'board',

                'color' => array(0), // blue

                'meld_keyword' => true,
            );
            break;

        case "149N1C":
            // "Score a card from your hand"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,

                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => $player_id,
                'location_to' => 'score',

                'score_keyword' => true
            );
            break;
            
        case "149N1D":
            // "Return a card from your score pile"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,

                'owner_from' => $player_id,
                'location_from' => 'score',
                'owner_to' => 0,
                'location_to' => 'deck'
            );
            break;
            
        // id 150, Artifacts age 4: Hunt-Lenox Globe
        case "150N1A":
            // "If you have fewer than four cards in your hand"
            if (self::countCardsInLocation($player_id, 'hand') < 4) {
                // "Return all non-green top cards from your board"
                self::setAuxiliaryValue(1); // Indicate that player had fewer than four cards in hands
                $options = array(
                    'player_id' => $player_id,
                    'can_pass' => false,

                    'owner_from' => $player_id,
                    'location_from' => 'board',
                    'owner_to' => 0,
                    'location_to' => 'deck',

                    'color' => array(0,1,3,4)
                );
                self::incrementStepMax(1);

            // "Meld a card from your hand"
            } else {
                self::setAuxiliaryValue(0); // Indicate that player had at least four cards in hands
                $options = array(
                    'player_id' => $player_id,
                    'n' => 1,
                    'can_pass' => false,

                    'owner_from' => $player_id,
                    'location_from' => 'hand',
                    'owner_to' => $player_id,
                    'location_to' => 'board',

                    'meld_keyword' => true,
                );
            }
            break;
            
        case "150N1B":
            // "Meld a card from your hand"
            self::setAuxiliaryValue(0);
            $options = array(
                'player_id' => $player_id,
                'n' => 1,

                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => $player_id,
                'location_to' => 'board'
            );
            break;

        // id 151, Artifacts age 4: Moses
        case "151N1A":    
            // "Score a top card on your board with a crown"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                
                'owner_from' => $player_id,
                'location_from' => 'board',
                'owner_to' => $player_id,
                'location_to' => 'score',
                
                'with_icon' => 1, /* crown */

                'score_keyword' => true
            );
            break;
        
        // id 152, Artifacts age 4: Mona Lisa
        case "152N1A":
            // Choose a color
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                
                'choose_color' => true,
            );
            break;

        case "152N1B":
            // Help the UI pick a reasonable default for the integer selection
            $chosen_color = self::getAuxiliaryValue2();
            $cards = self::getCardsInLocationKeyedByColor($player_id, 'hand');
            self::setAuxiliaryValue(count($cards[$chosen_color]));

            // Choose a number
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                
                'choose_non_negative_integer' => true,
            );
            break;

        case "152N1C":
            // "Otherwise, return all cards from your hand"
            $options = array(
                'player_id' => $player_id,
                
                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => 0,
                'location_to' => 'deck',
            );
            break;
            
        // id 155, Artifacts age 5: Boerhavve Silver Microscope
        case "155N1A":
            // "Return the lowest card in your hand"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,

                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => 0,
                'location_to' => 'deck',

                'age' => self::getMinAgeInHand($player_id)
            );
            break;

        case "155N1B":
            // "and the lowest top card on your board"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,

                'owner_from' => $player_id,
                'location_from' => 'board',
                'owner_to' => 0,
                'location_to' => 'deck',

                'age' => self::getMinAgeOnBoardTopCards($player_id),
            );
            break;

        // id 156, Artifacts age 5: Principia
        case "156N1A":
            // Record the values of all non-blue top cards.
            $ages_on_top = array();
            for ($color = 1; $color < 5; $color++) { // non-blue
                $top_card = self::getTopCardOnBoard($player_id, $color);
                if ($top_card !== null) {
                    $ages_on_top[] = $top_card['faceup_age'];
                }
            }
            self::setAuxiliaryValue(Arrays::getValueFromBase16Array($ages_on_top));

            // "Return all non-blue top cards from your board"
            $options = array(
                'player_id' => $player_id,

                'owner_from' => $player_id,
                'location_from' => 'board',
                'owner_to' => 0,
                'location_to' => 'deck',

                'color' => array(1, 2, 3, 4), // non-blue
            );
            break;

        // id 157, Artifacts age 5: Bill of Rights
        case "157C1A":
            $options = array(
                'player_id' => $player_id,
                
                'choose_color' => true,
                'color' => $this->innovationGameState->getAsArray('color_array')
            );            
            break;
        
        // id 158, Artifacts age 5: Ship of the Line Sussex
        case "158N1A":
            // "Return all cards from your score pile"
            $options = array(
                'player_id' => $player_id,
                
                'owner_from' => $player_id,
                'location_from' => 'score',
                'owner_to' => 0,
                'location_to' => 'deck'
            );
            break;
        
        case "158N1B":
            // "Choose a color"
            $options = array(
                'player_id' => $player_id,
                
                'choose_color' => true
            );
            break;
            
        // id 160, Artifacts age 5: Hudson's Bay Company Archives
        case "160N1A":    
            // "Meld a card from your score pile"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,

                'owner_from' => $player_id,
                'location_from' => 'score',
                'owner_to' => $player_id,
                'location_to' => 'board',

                'meld_keyword' => true,
            );
            break;
            
        // id 161, Artifacts age 5: Gujin Tushu Jinsheng
        case "161N1A":
            // "Choose any other top card on any other board"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                
                'owner_from' => 'any other player',
                'location_from' => 'board',
                'location_to' => 'none'               
            );
            break;
        
        // id 162, age 5: The Daily Courant
        case "162N1A":
            // Choose value to draw
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                
                'choose_value' => true
            );       
            break;

        case "162N1B":
            // Return the card to top of deck
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'enable_autoselection' => false, // Give the player the chance to read the card
                
                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => 0,
                'location_to' => 'deck',

                'bottom_to' => false, // Topdeck
                
                'card_id_1' => $this->innovationGameState->get('card_id_1')
            );       
            break;
            
        case "162N1C":
            // "You may execute the effects of one of your other top cards as if they were on this card. Do not share them."
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => true,
                
                'owner_from' => $player_id,
                'location_from' => 'board',
                'owner_to' => $player_id,
                'location_to' => 'none',
                
                // Exclude the card currently being executed (it's possible for the effects of The Daily Courant to be executed as if it were on another card)
                'not_id' => self::getCurrentNestedCardState()['executing_as_if_on_card_id'],
            );       
            break;      
            
        // id 163, Artifacts age 5: Sandham Room Cricket Bat
        case "163N1A":
            // "Claim an achievement ignoring eligibility"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,

                'owner_from' => 0,
                'location_from' => 'achievements',
                'owner_to' => $player_id,
                'location_to' => 'achievements',

                'require_achievement_eligibility' => false
            );
            break;

        // id 164, Artifacts age 5: Almira, Queen of the Castle
        case "164N1A":
            // "Meld a card from your hand"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                
                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => $player_id,
                'location_to' => 'board',

                'meld_keyword' => true,
            );
            break;

        case "164N1B":
            // "Claim an achievement of matching value, ignoring eligibility"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                
                'owner_from' => 0,
                'location_from' => 'achievements',
                'owner_to' => $player_id,
                'location_to' => 'achievements',

                'age' => self::getFaceupAgeLastSelected(),
                'require_achievement_eligibility' => false
            );
            break;

        // id 165, Artifacts age 6: Kilogram of the Archives
        case "165N1A":
            // "Return a card from your hand"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                
                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => 0,
                'location_to' => 'deck'
            );
            break;

        case "165N1B":
            // "Return a top card from your board"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                
                'owner_from' => $player_id,
                'location_from' => 'board',
                'owner_to' => 0,
                'location_to' => 'deck'
            );
            break;

        // id 166, Artifacts age 6: Puffing Billy
        case "166N1A":
            // "Return a card from your hand"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                
                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => $player_id,
                'location_to' => 'revealed,deck'
            );
            break;

        // id 167, Artifacts age 6: Frigate Constitution
        case "167C1A":
            // "I compel you to reveal a card in your hand!"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                
                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => $player_id,
                'location_to' => 'revealed'
            );
            break;

        case "167C1B":
            // "Return it"
            $revealed_card = self::getCardsInLocation($player_id, 'revealed')[0];
            self::returnCard($revealed_card);

            // "And all cards of its color from your board"
            $options = array(
                'player_id' => $player_id,
                
                'owner_from' => $player_id,
                'location_from' => 'pile',
                'owner_to' => 0,
                'location_to' => 'deck',
                
                'color' => array($this->innovationGameState->get('color_last_selected')),
            );
            break;

        // id 168, Artifacts age 6: U.S. Declaration of Independence
        case "168C1A":
            // "Transfer the highest card in your hand to my hand"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                
                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => $launcher_id,
                'location_to' => 'hand',

                'age' => self::getMaxAgeInHand($player_id)
            );
            break;

        case "168C1B":
            // "Transfer the highest card in your score pile to my score pile"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                
                'owner_from' => $player_id,
                'location_from' => 'score',
                'owner_to' => $launcher_id,
                'location_to' => 'score',

                'age' => self::getMaxAgeInScore($player_id)
            );
            break;
            
        case "168C1C":
            // "Transfer the highest top card with a factory from your board to my board"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                
                'owner_from' => $player_id,
                'location_from' => 'board',
                'owner_to' => $launcher_id,
                'location_to' => 'board',

                'age' => self::getMaxAgeOnBoardTopCardsWithIcon($player_id, 5),
                'with_icon' => 5
            );
            break;

        // id 170, Artifacts age 6: Buttonwood Agreement
        case "170N1A":
            // "Choose three colors"
            $options = array(
                'player_id' => $player_id,
                
                'choose_three_colors' => true
            );
            break;

        case "170N1B":
            // Reveal score pile to prove to other players that they are returning the correct cards.
            self::revealScorePile($player_id);
            // "Return all cards of the drawn card's color from your score pile"
            $options = array(
                'player_id' => $player_id,
                
                'owner_from' => $player_id,
                'location_from' => 'score',
                'owner_to' => 0,
                'location_to' => 'deck',

                'color' => array(self::getAuxiliaryValue()),
            );
            break;

        // id 171, Artifacts age 6: Stamp Act
        case "171C1A":
            // "Transfer a card of value equal to the top yellow card on your board from your score pile to mine"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                
                'owner_from' => $player_id,
                'location_from' => 'score',
                'owner_to' => $launcher_id,
                'location_to' => 'score',

                'age' => self::getAuxiliaryValue()
            );
            break;

        case "171C1B":
            // "Return a card from your score pile of value equal to the top green card on your board"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                
                'owner_from' => $player_id,
                'location_from' => 'score',
                'owner_to' => 0,
                'location_to' => 'deck',

                'age' => self::getAuxiliaryValue()
            );
            break;

        // id 173, Artifacts age 6: Moonlight Sonata
        case "173N1A":
            // "Choose a color on your board having the highest top card"
            $max_age = self::getMaxAgeOnBoardTopCards($player_id);
            $color_array = array();
            for ($color = 0; $color < 5; $color++) {
                $top_card = self::getTopCardOnBoard($player_id, $color);
                if ($top_card !== null && $top_card['age'] == $max_age) {
                    $color_array[] = $color;
                }
            }
            
            $options = array(
                'player_id' => $player_id,
                'n' => 1,

                'choose_color' => true,
                'color' =>  $color_array
            );
            break;
            
        case "173N1B":
            // "Claim an achievement ignoring eligibility"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,

                'owner_from' => 0,
                'location_from' => 'achievements',
                'owner_to' => $player_id,
                'location_to' => 'achievements',

                'require_achievement_eligibility' => false
            );
            break;
            
        // id 174, Artifacts age 6: Marcha Real
        case "174N1A":
            // "Reveal and return two cards from your hand"
            $options = array(
                'player_id' => $player_id,
                'n' => 2,

                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => $player_id,
                'location_to' => 'revealed,deck'
            );
            break;
    
        case "174N1B":
            // "Claim an achievement ignoring eligibility"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,

                'owner_from' => 0,
                'location_from' => 'achievements',
                'owner_to' => $player_id,
                'location_to' => 'achievements',

                'require_achievement_eligibility' => false
            );
            break;

        // id 175, Artifacts age 7: Periodic Table
        case "175N1A":
            // "Choose two top cards on your board of the same value"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                
                'owner_from' => $player_id,
                'location_from' => 'board',
                'owner_to' => $player_id,
                'location_to' => 'none',

                'color' => self::getAuxiliaryValueAsArray(),
            );

            break;

        case "175N1B":
            // "Choose two top cards on your board of the same value"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                
                'owner_from' => $player_id,
                'location_from' => 'board',
                'owner_to' => $player_id,
                'location_to' => 'none',

                'not_id' => $this->innovationGameState->get('id_last_selected'),
                'age' => $this->innovationGameState->get('age_last_selected'),
            );
            break;

        // id 177, Artifacts age 7: Submarine H. L. Hunley
        case "177C1A":
            // "Return all cards of its color from your board"
            $options = array(
                'player_id' => $player_id,
                
                'owner_from' => $player_id,
                'location_from' => 'pile',
                'owner_to' => 0,
                'location_to' => 'deck',
                
                'color' => array(self::getAuxiliaryValue()),
             );
            break;
            
        // id 178, Artifacts age 7: Jedlik's Electromagnetic Self-Rotor
        case "178N1A":
            // "Claim an achievement of value 8 if it is available, ignoring eligibility"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,

                'owner_from' => 0,
                'location_from' => 'achievements',
                'owner_to' => $player_id,
                'location_to' => 'achievements',

                'age' => 8,
                'require_achievement_eligibility' => false
            );
            break;            

        // id 179, Artifacts age 7: International Prototype Metre Bar
        case "179N1A":
            // "Choose a value"
            $options = array(
                'player_id' => $player_id,

                'choose_value' => true
            );
            break;

        // id 180, Artifacts age 7: Hansen Writing Ball
        case "180C1A":
            // "Meld a blue card"
            $options = array(
                'player_id' => $player_id,  
                'n' => 1,

                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => $player_id,
                'location_to' => 'board',

                'color' => array(0),

                'meld_keyword' => true,
            );
            break;

        // id 181, Artifacts age 7: Colt Paterson Revolver
        case "181C1A":
            // "Return all cards in your hand"
            $options = array(
                'player_id' => $player_id,
                
                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => 0,
                'location_to' => 'deck'
            );
            break;            

        case "181C1B":
            // "Return all cards in your score pile"
            $options = array(
                'player_id' => $player_id,
                
                'owner_from' => $player_id,
                'location_from' => 'score',
                'owner_to' => 0,
                'location_to' => 'deck'
            );
            break;            

        // id 182, Artifacts age 7: Singer Model 27
        case "182N1A":
            // "Tuck a card from your hand"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                
                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => $player_id,
                'location_to' => 'board',
                'bottom_to' => true
            );
            break;            

        case "182N1B":
            // "Tuck all cards from your score pile of that color"
            $options = array(
                'player_id' => $player_id,
                
                'owner_from' => $player_id,
                'location_from' => 'score',
                'owner_to' => $player_id,
                'location_to' => 'board',
                'bottom_to' => true,

                'color' => array($this->innovationGameState->get('color_last_selected'))
            );
            break;

        // id 183, Artifacts age 7: Roundhay Garden Scene
        case "183N1A":
            // "Meld the highest card from your score pile"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                
                'age' => self::getMaxAgeInScore($player_id),
                
                'owner_from' => $player_id,
                'location_from' => 'score',
                'owner_to' => $player_id,
                'location_to' => 'board',

                'meld_keyword' => true,
            );
            break;
            
        // id 184, Artifacts age 7: The Communist Manifesto
        case "184N1A":
            // Choose a player
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                
                'choose_player' => true,
                'players' => $this->innovationGameState->getAsArray('player_array')
            );
            break;

        case "184N1B":
            // "Transfer one of the drawn cards to each player's board"
            $player_choice = self::getAuxiliaryValue();            
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                
                'owner_from' => $player_id,
                'location_from' => 'revealed',
                'owner_to' => $player_choice,
                'location_to' => 'board'
            );
            break;
            
        // id 186, Artifacts age 8: Earhart's Lockheed Electra 10E'),
        case "186N1A":
            // "For each value below nine, return a top card of that value from your board, in descending order"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                
                'owner_from' => $player_id,
                'location_from' => 'board',
                'owner_to' => 0,
                'location_to' => 'deck',

                'age' => $this->innovationGameState->get('age_last_selected') - 1,
                'not_id' => 188 // Battleship Yamato's face-up age is 11 (not 8), so it's never a valid selection
            );
            break;

        case "186N1B":
            // "Otherwise, claim an achievement, ignoring eligibility"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                
                'owner_from' => 0,
                'location_from' => 'achievements',
                'owner_to' => $player_id,
                'location_to' => 'achievements',

                'require_achievement_eligibility' => false
            );
            break;
        
        // id 187, Artifacts age 8: Battleship Bismarck
        case "187C1A":
            // "Return all cards of the drawn color from your board"
            $options = array(
                'player_id' => $player_id,
                
                'owner_from' => $player_id,
                'location_from' => 'pile',
                'owner_to' => 0,
                'location_to' => 'deck',
                
                'color' => array(self::getAuxiliaryValue()),
            );
            break;
            
        // id 190, Artifacts age 8: Meiji-Mura Stamp Vending Machine
        case "190N1A":
            // "Return a card from your hand"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                
                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => 0,
                'location_to' => 'deck'
            );
            break;

        // id 191, Artifacts age 8: Plush Beweglich Rod Bear
        case "191N1A":
            // "Choose a value"
            $selectable_ages = array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10);
            // The value 11 should only be an option when Battleship Yamato is a top card on the player's board or if
            // 4th edition is in use.
            $battleship_yamato = self::getCardInfo(188);
            if ($this->innovationGameState->usingFourthEditionRules() || (self::isTopBoardCard($battleship_yamato) && $battleship_yamato['owner'] === $player_id)) {
                $selectable_ages[] = 11;
            }
            
            $options = array(
                'player_id' => $player_id,

                'choose_value' => true,
                'age' => $selectable_ages
            );
            break;

        case "191N1B":
            // "Return all cards of that value from all score piles"
            $options = array(
                'player_id' => $player_id,
                
                'owner_from' => 'any player',
                'location_from' => 'score',
                'owner_to' => 0,
                'location_to' => 'deck',
                
                'age' => self::getAuxiliaryValue()
            );
            break;

        // id 192, Artifacts age 8: Time
        case "192C1A":
            // "Transfer a non-yellow top card with a clock from your board to my board"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,

                'owner_from' => $player_id,
                'location_from' => 'board',
                'owner_to' => $launcher_id,
                'location_to' => 'board',
                
                'with_icon' => 6,
                'color' => array(0, 1, 2, 4)
            );
            break;

       // id 193, Artifacts age 8: Garland's Ruby Slippers
        case "193N1A":
            // "Meld an 8 from your hand"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                
                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => $player_id,
                'location_to' => 'board',
                
                'age' => 8,

                'meld_keyword' => true,
            );
            break;

        // id 194, Artifacts age 8: 30 World Cup Final Ball
        case "194C1A":
            // "I compel you to return one of your achievements"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                
                'owner_from' => $player_id,
                'location_from' => 'achievements',
                'owner_to' => 0,
                'location_to' => 'deck',

                'include_relics' => false,
            );
            break;

        // id 196, Artifacts age 9: Luna 3
        case "196N1A":
            // "Return all cards from your score pile"
            $options = array(
                'player_id' => $player_id,
                
                'owner_from' => $player_id,
                'location_from' => 'score',
                'owner_to' => 0,
                'location_to' => 'deck'
            );
            break;            

         // id 197, Artifacts age 9: United Nations Charter
         case "197C1A":
            // "Transfer all top cards on your board with a demand effect to my score pile"
            $options = array(
                'player_id' => $player_id,

                'owner_from' => $player_id,
                'location_from' => 'board',
                'owner_to' => $launcher_id,
                'location_to' => 'score',

                'has_demand_effect' => true,
            );
            break;

         // id 198, Artifacts age 9: Velcro Shoes
         case "198C1A":
            // "Transfer a 9 from your hand to my hand"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                
                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => $launcher_id,
                'location_to' => 'hand',

                'age' => 9
            );
            break;

         case "198C1B":
            // "Transfer a 9 from your score pile to my score pile"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                
                'owner_from' => $player_id,
                'location_from' => 'score',
                'owner_to' => $launcher_id,
                'location_to' => 'score',

                'age' => 9
            );

            break;
            
        // id 199, Artifacts age 9: Philips Compact Cassette
        case "199N1A":
            // "Splay up two colors on your board"
            $options = array(
                'player_id' => $player_id,
                'n' => 2,
                
                'splay_direction' => 3
            );
            break;
            
        // id 200, Artifacts age 9: Syncom 3
        case "200N1A":
            // "Return all cards from your hand"
            $options = array(
                'player_id' => $player_id,
                
                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => 0,
                'location_to' => 'deck'
            );
            break;

        // id 204, Artifacts age 9: Marilyn Diptych
        case "204N1A":
            // "You may score a card from your hand"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => true,
                
                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => $player_id,
                'location_to' => 'score',

                'score_keyword' => true
            );
            break;

        case "204N1B":
            // "You may transfer any card from your score pile to your hand"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => true,
                
                'owner_from' => $player_id,
                'location_from' => 'score',
                'owner_to' => $player_id,
                'location_to' => 'hand'
            );
            break;
            
        // id 208, Artifacts age 10: Maldives
        case "208C1A":
            // "I compel you to return all cards in your hand but two"
            $num_cards_in_hand = count(self::getCardsInLocation($player_id, 'hand'));
            if ($num_cards_in_hand > 2) {
                $options = array(
                    'player_id' => $player_id,
                    'n' => $num_cards_in_hand - 2,
                    'can_pass' => false,
                        
                    'owner_from' => $player_id,
                    'location_from' => 'hand',
                    'owner_to' => 0,
                    'location_to' => 'deck'
                );
            }
            break;

        case "208C1B":
            // "Return all cards in your score pile but two"
            $num_cards_in_score_pile = count(self::getCardsInLocation($player_id, 'score'));
            if ($num_cards_in_score_pile > 2) {
                $options = array(
                    'player_id' => $player_id,
                    'n' => $num_cards_in_score_pile - 2,
                    'can_pass' => false,
                    
                    'owner_from' => $player_id,
                    'location_from' => 'score',
                    'owner_to' => 0,
                    'location_to' => 'deck'
                );
            }
            break;

        case "208N1A":
            // "Return all cards in your score pile but four"
            $num_cards = self::getAuxiliaryValue();
            $options = array(
                'player_id' => $player_id,
                'n' => $num_cards - 4,
                
                'owner_from' => $player_id,
                'location_from' => 'score',
                'owner_to' => 0,
                'location_to' => 'deck'
            );
            break;

        // id 211, Artifacts age 10: Dolly the Sheep
        case "211N1A":
            // "You may score your bottom yellow card"
            $bottom_yellow_card = self::getBottomCardOnBoard($player_id, 3);
            if ($bottom_yellow_card != null) {
                $options = array(
                    'player_id' => $player_id,
                    'can_pass' => false,
                    
                    'choose_yes_or_no' => true
                );
            }
            break;

        case "211N1B":
            // "You may draw and tuck a 1"
            $options = array(
                'player_id' => $player_id,
                
                'choose_yes_or_no' => true
            );
            break;    
            
        case "211N1C":
            // "Meld the highest card in your hand"
            if (count(self::getCardsInLocation($player_id, 'hand')) > 0) {
                $options = array(
                    'player_id' => $player_id,
                    'n' => 1,
                    'can_pass' => false,
                    
                    'owner_from' => $player_id,
                    'location_from' => 'hand',
                    'owner_to' => $player_id,
                    'location_to' => 'board',

                    'age' => self::getMaxAgeInHand($player_id),

                    'meld_keyword' => true,
                );
            }
            break;
        

       // id 214, Artifacts age 10: Twister
       case "214C1A":
        // "For each color, meld a card of that color from your score pile"
        $options = array(
            'player_id' => $player_id,
            'n' => 1,
            'can_pass' => false,
            
            'owner_from' => $player_id,
            'location_from' => 'score',
            'owner_to' => $player_id,
            'location_to' => 'board',

            'color' => self::getAuxiliaryValueAsArray(),

            'meld_keyword' => true,
        );
        break;

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
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => true,
                
                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => $player_id,
                'location_to' => 'revealed',
                
                'card_ids_are_in_auxiliary_array' => true,
                'enable_autoselection' => false,
            );
            break;

        case "216N1B":
            // "Claim an achievement of matching value, ignoring eligibility"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                
                'owner_from' => 0,
                'location_from' => 'achievements',
                'owner_to' => $player_id,
                'location_to' => 'achievements',

                'age' => $this->innovationGameState->get('age_last_selected'),
                'require_achievement_eligibility' => false
            );
            break;

        // id 217, Relic age 5: Newton-Wickins Telescope
        case "217N1A":
            // "You may return any number of cards from your score pile"
            $options = array(
                'player_id' => $player_id,
                'n_min' => 1,
                'can_pass' => true,
                
                'owner_from' => $player_id,
                'location_from' => 'score',
                'owner_to' => 0,
                'location_to' => 'deck'
            );
            break;

        // id 219, Relic age 7: Safety Pin
        case "219D1A":
            // "I demand you return all cards of value higher than 6 from your hand!"
            $options = array(
                'player_id' => $player_id,
                
                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => 0,
                'location_to' => 'deck',
                
                'age_min' => 7,
            );
            break;

        // id 443, age 11: Fusion
        case "443N1A":
        case "443N1C": // We have to use a third interaction because if we repeat the first interaction then we wind up overwriting the auxiliary value with 11
            // "Score a top card of value 11 on your board."    
            $options = array(
                'player_id' => $player_id,                
                'n' => 1,

                'owner_from' => $player_id,
                'location_from' => 'board',
                'owner_to' => $player_id,
                'location_to' => 'score',

                'age' => self::getAuxiliaryValue(),
                
                'score_keyword' => true,
            );
            break;

        case "443N1B":
            // "Choose a value one or two lower than the scored card"

            $options = array(
                'player_id' => $player_id,
                'n' => 1,

                'choose_value' => true,
                'age' => self::getAuxiliaryArray(),
            );
            break;

        // id 444, age 11: Hypersonics
        case "444D1A":
            // "I demand you return two top cards on your board of the same value!"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,

                'owner_from' => $player_id,
                'location_from' => 'board',
                'owner_to' => 0,
                'location_to' => 'deck',

                'card_ids_are_in_auxiliary_array' => true,
            );
            break;

        case "444D1B":
            // "I demand you return two top cards on your board of the same value!"  (second card)
            $options = array(
                'player_id' => $player_id,
                'n' => 1,

                'owner_from' => $player_id,
                'location_from' => 'board',
                'owner_to' => 0,
                'location_to' => 'deck',

                'age' => $this->innovationGameState->get('age_last_selected'), // matches previous selection
            );
            break;

        case "444D1C":
            // "return all cards of that value or less in your hand and score pile!"
            $options = array(
                'player_id' => $player_id,                

                'owner_from' => $player_id,
                'location_from' => 'hand,score',
                'owner_to' => 0,
                'location_to' => 'deck',

                'card_ids_are_in_auxiliary_array' => true,
            );
            break;
        
        // id 446, age 11: Near-Field Comm
        case "446D1A":
            // "I demand you transfer all the highest cards in your score pile to my score pile!"
            $options = array(
                'player_id' => $player_id,
                
                'owner_from' => $player_id,
                'location_from' => 'score',
                'owner_to' => $launcher_id,
                'location_to' => 'score',
                
                'age' => self::getMaxAgeInScore($player_id),
            );
            break;

        case "446N1A":
            // "Reveal the highest card in your score pile and execute its non-demand dogma effects. Do not share them."
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                
                'owner_from' => $player_id,
                'location_from' => 'score',
                'owner_to' => $player_id,
                'location_to' => 'revealed',
                
                'age' => self::getMaxAgeInScore($player_id),
            );
            break;
            
            
        // id 448, age 11: Escapism
        case "448N1A":
            // "Reveal and junk a card in your hand"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                
                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => $player_id,
                'location_to' => 'revealed',
            );
            break;

        case "448N1B":
            // "Return from your hand all cards of value equal to the value of the junked card."
            $options = array(
                'player_id' => $player_id,

                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => 0,
                'location_to' => 'deck',

                'age' => $this->innovationGameState->get('age_last_selected'),
            );
            break;
            
        // id 449, age 11: Whataboutism
        case "449D1A":
            // "I demand you transfer all your top cards with a demand effect from your board to my board!"
            $options = array(
                'player_id' => $player_id,

                'owner_from' => $player_id,
                'location_from' => 'board',
                'owner_to' => $launcher_id,
                'location_to' => 'board',

                'has_demand_effect' => true,
            );
            break;
            
        // id 487, Unseen age 1: Rumor
        case "487N1A":
            // "Return a card from your score pile."
            $options = array(
                'player_id' => $player_id,                
                'n' => 1,

                'owner_from' => $player_id,
                'location_from' => 'score',
                'owner_to' => 0,
                'location_to' => 'deck',
            );
            break;

        case "487N2A":
            // "Transfer a card from your hand to the hand of the player on your left."
            $players = self::getActivePlayerIdsInTurnOrderStartingToLeftOfActingPlayer();
            $options = array(
                'player_id' => $player_id,                
                'n' => 1,

                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => $players[0],
                'location_to' => 'hand',
            );
            break;
            
        // id 489, Unseen age 1: Handshake
        case "489D1A":
            // "Choose two colors of cards in your hand!"
            $options = array(
                'player_id' => $player_id, 
                
                'choose_two_colors' => true,
                'color' => self::getAuxiliaryValueAsArray(),
            );
            break;

        // id 490, Unseen age 1: Tomb
        case "490N1A":
            // "Safeguard an available achievement of value 1 plus the number of achievements you have."
            $options = array(
                'player_id' => $player_id,
                'n' => 1,

                'owner_from' => 0,
                'location_from' => 'achievements',
                'owner_to' => $player_id,
                'location_to' => 'safe',
                
                'age' => self::countCardsInLocation($player_id, 'achievements') + 1,
            );
            break;
            
        // id 495, Unseen age 2: Astrology
        case "495N1A":
            // "You may splay left the color of which you have the most cards on your board."
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => true,

                'splay_direction' => Directions::LEFT,
                'color' => self::getAuxiliaryValueAsArray(),
            );
            break;

        // id 497, Unseen age 2: Padlock
        case "497D1A":
            // "I demand you transfer one of your secrets to the available achievements!"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,

                'owner_from' => $player_id,
                'location_from' => 'safe',
                'owner_to' => 0,
                'location_to' => 'achievements',
            );
            break;

        case "497N1A":
            // "you may score up to three cards from hand of different values."
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => true,

                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => $player_id,
                'location_to' => 'score',
                
                'score_keyword' => true,
            );
            break;

        case "497N1B":
            // "you may score up to three cards from hand of different values."
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => true,
                
                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => $player_id,
                'location_to' => 'score',

                'score_keyword' => true,

                'card_ids_are_in_auxiliary_array' => true,
            );
            break;

        case "497N1C":
            // "you may score up to three cards from hand of different values."
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => true,
                
                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => $player_id,
                'location_to' => 'score',

                'card_ids_are_in_auxiliary_array' => true,
            );
            break;

        // id 499, Unseen age 2: Cipher
        case "499N1A":
            // "Return all cards from your hand."
            $options = array(
                'player_id' => $player_id,

                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => 0,
                'location_to' => 'deck',
            );
            break;

        case "499N2A":
            // "You may splay your blue cards left."
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => true,

                'splay_direction' => 1,
                'color' => array(0), // blue
            );
            break;

        // id 500, Unseen age 2: Counterfeiting
        case "500N1A":
            // "Score a top card from your board of a value not in your score pile."            
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                
                'owner_from' => $player_id,
                'location_from' => 'board',
                'owner_to' => $player_id,
                'location_to' => 'score',

                'card_ids_are_in_auxiliary_array' => true,
            );
            break;

        case "500N2A":
            // "You may splay your green or purple cards left."
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => true,

                'splay_direction' => 1,
                'color' => array(2,4), // green or purple
            );
            break;

        // id 501, Unseen age 2: Exile
        case "501D1A":
            // "I demand you return a top card without a leaf from your board!"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                
                'owner_from' => $player_id,
                'location_from' => 'board',
                'owner_to' => 0,
                'location_to' => 'deck',
                
                'without_icon' => 2,
            );
            break;

        case "501D1B":
            // "Return all cards of the returned card's value from your score pile!"
            $options = array(
                'player_id' => $player_id,

                'owner_from' => $player_id,
                'location_from' => 'score',
                'owner_to' => 0,
                'location_to' => 'deck',
                
                'age' => $this->innovationGameState->get('age_last_selected'),
            );
            break;

        // id 502, Unseen age 2: Fingerprints
        case "502N1A":
            // "You may splay your red or yellow cards left."
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => true,

                'splay_direction' => 1,
                'color' => array(1,3), // red or yellow
            );
            break;
            
        case "502N2A":
            // "Safeguard an available achievement of value equal to the number of splayed colors on your board."
            $top_cards = self::getTopCardsOnBoard($player_id);
            $count = 0;
            foreach($top_cards as $card) {
                if ($card !== null) {
                    if ($card['splay_direction'] > 0) {
                        $count++;
                    }
                }
            }
            $options = array(
                'player_id' => $player_id,
                'n' => 1,

                'owner_from' => 0,
                'location_from' => 'achievements',
                'owner_to' => $player_id,
                'location_to' => 'safe',
                
                'age' => $count,
            );
            break;

        // id 504, Unseen age 2: Steganography
        case "504N1A":
            // "You may splay left a color on your board with a visible bulb."
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => true,

                'splay_direction' => Directions::LEFT,
                'color' => self::getAuxiliaryValueAsArray(),
            );
            break;
            
        case "504N1B":
            // "safeguard an available achievement of value equal to the number of cards of that color on your board."
            $options = array(
                'player_id' => $player_id,
                'n' => 1,

                'owner_from' => 0,
                'location_from' => 'achievements',
                'owner_to' => $player_id,
                'location_to' => 'safe',
                
                'age' => self::countCardsInLocationKeyedByColor($player_id, 'board')[$this->innovationGameState->get('color_last_selected')],
            );
            break;

        // id 510, Unseen age 3: Smuggling
        case "510D1A":
            // "I demand you transfer a card of value equal to the top yellow card on your board"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,

                'owner_from' => $player_id,
                'location_from' => 'score',
                'owner_to' => $launcher_id, 
                'location_to' => 'score',
                
                'age' => self::getTopCardOnBoard($player_id, 3)['age'],
            );
            break;

        case "510D1B":
            // "and a card of value  equal to the top yellow card on my board from your score pile to my score pile!"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,

                'owner_from' => $player_id,
                'location_from' => 'score',
                'owner_to' => $launcher_id, 
                'location_to' => 'score',
                
                'age' => self::getTopCardOnBoard($launcher_id, 3)['age'],
            );
            break;

        // id 511, Unseen age 3: Freemasons
        case "511N1A":
            // "For each color, you may tuck a card from your hand of that color."
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => true,

                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => $player_id, 
                'location_to' => 'board',
                
                'bottom_to' => true,
                'color' => self::getAuxiliaryValue2AsArray(),
            );
            break;

        case "511N2A":
            // "You may splay your yellow or blue cards left."
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => true,

                'splay_direction' => Directions::LEFT,
                'color' =>array(0,3), // blue or yellow
            );
            break;

        // id 513, Unseen age 3: Masquerade
        case "513N1A":
            // "Safeguard an available achievement of value equal to the number of cards in your hand."
            $options = array(
                'player_id' => $player_id,
                'n' => 1,

                'owner_from' => 0,
                'location_from' => 'achievements',
                'owner_to' => $player_id, 
                'location_to' => 'safe',
                
                'age' => self::countCardsInHand($player_id),
            );
            break;

        case "513N1B":
            // "return all the highest cards from your hand."
            $options = array(
                'player_id' => $player_id,

                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => 0, 
                'location_to' => 'deck',
                
                'age' => self::getMaxAgeInHand($player_id),
            );
            break;

        case "513N2A":
            // "You may splay your purple cards left."
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => true,

                'splay_direction' => Directions::LEFT,
                'color' => array(4),
            );
            break;

        // id 525, Unseen age 5: Popular Science
        case "525N1A":
            $options = array(
               'player_id' => $player_id,
               'n' => 1,

               'choose_value' => true,
               'age' => self::getAuxiliaryArray(),
           );
           break;

       case "525N3A":
           // "You may splay your blue cards right."
           $options = array(
               'player_id' => $player_id,
               'n' => 1,
               'can_pass' => true,

               'splay_direction' => Directions::RIGHT,
               'color' => array(0),
           );
           break;

       // id 526, Unseen age 5: Probability
       case "526N1A":
           // "Return all cards from your hand."
           $options = array(
               'player_id' => $player_id,

               'owner_from' => $player_id,
               'location_from' => 'hand',
               'owner_to' => 0,
               'location_to' => 'deck',
           );
           break;

       case "526N2A":
           // ", then return them"
           $options = array(
               'player_id' => $player_id,

               'owner_from' => $player_id,
               'location_from' => 'revealed',
               'owner_to' => 0,
               'location_to' => 'deck',
           );
           break;

       // id 527, Unseen age 5: Cabal
       case "527N1A":
           // "Safeguard an available achievement of value equal to a top card on your board."
           $options = array(
               'player_id' => $player_id,
               'n' => 1,

               'owner_from' => 0,
               'location_from' => 'achievements',
               'owner_to' => $player_id,
               'location_to' => 'safe',

               'card_ids_are_in_auxiliary_array' => true,
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
            $this->innovationGameState->set('location_to', self::encodeLocation($options['location_to']));
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
        if ($options == null
                || (array_key_exists('n', $options) && $options['n'] <= 0)
                || (array_key_exists('n_max', $options) && $options['n_max'] <= 0)
                || (array_key_exists('choose_value', $options) && (array_key_exists('age', $options) && empty($options['age']))
                || (array_key_exists('choices', $options) && empty($options['choices'])))) {

            self::notifyIfLocationLimitShrunkSelection($player_id);

            if (self::isInSeparateFile($card_id)) {
                $executionState = (new ExecutionState($this))
                    ->setEdition($this->innovationGameState->getEdition())
                    ->setLauncherId($launcher_id)
                    ->setPlayerId($player_id)
                    ->setEffectType($current_effect_type)
                    ->setEffectNumber($current_effect_number)
                    ->setCurrentStep(self::getStep())
                    ->setNextStep(self::getStep() + 1)
                    ->setMaxSteps(self::getStepMax());
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
    
    function stInterInteractionStep() {
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
                    self::format("SELECT card_id FROM echo_execution WHERE nesting_index = {nesting_index} AND execution_index = {effect_number}",
                        array('nesting_index' => $nesting_index, 'effect_number' => $current_effect_number)));
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

                switch($code) {
                // The first number is the id of the card
                // D1 means the first (and single) I demand effect
                // C1 means the first (and single) I compel effect
                // N1 means the first non-demand effect
                // N2 means the second non-demand effect
                // N3 means the third non-demand effect
                // E1 means the first (and single) echo effect
                
                // The letter indicates the step : A for the first one, B for the second
                    
                // id 5, age 1: Oars
                case "5D1A":
                    if ($n > 0) { // "If you do"
                        self::executeDraw($player_id, 1); // "Draw a 1"
                        self::setAuxiliaryValue(1); // A transfer has been made, flag it
                        if (!$this->innovationGameState->usingFirstEditionRules()) {
                            $step--; self::incrementStep(-1); // "Repeat that dogma effect"
                        }
                    } else {
                        // Reveal hand to prove that they have no crowns.
                        self::revealHand($player_id);
                    }
                    break;

                // id 6, age 1: Clothing
                case "6N1A":
                    if ($n == 0) { // "If you don't"
                        // Only reveal hand if there is at least one card in hand and fewer than 5 top cards
                        $hand_count = self::countCardsInLocation($player_id, 'hand');
                        if ($hand_count > 0) {
                            $pile_sizes = self::countCardsInLocationKeyedByColor($player_id, 'board');
                            for ($color = 0; $color < 5; $color++) {
                                if ($pile_sizes[$color] == 0) {
                                    self::revealHand($player_id);
                                    break;
                                }
                            }
                        }
                    }
                    break;
                
                // id 9, age 1: Agriculture
                case "9N1A":
                    if ($n > 0) { // "If you do"
                        $age_to_draw_in = $this->innovationGameState->get('age_last_selected') + 1;
                        self::executeDraw($player_id, $age_to_draw_in, 'score'); // "Draw and score a card of value one higher than the card you returned"
                    }
                    break;
                
                // id 10, age 1: Domestication
                case "10N1A":
                    self::executeDraw($player_id, 1); // "Draw a 1"
                    break;
                
                // id 11, age 1: Masonry
                case "11N1A":
                    if ($n >= 4) { // "If you melded four or more cards this way"
                        $achievement = self::getCardInfo(106);
                        if ($achievement['owner'] == 0 && $achievement['location'] == 'achievements') {
                            self::notifyGeneralInfo(clienttranslate("At least four cards have been melded."));
                            self::transferCardFromTo($achievement, $player_id, 'achievements'); // "Claim the Monument achievement"
                        } else {
                            self::notifyGeneralInfo(clienttranslate("At least four cards have been melded but the Monument achievement has already been claimed."));
                        }
                    }
                    break;
                    
                // id 12, age 1: City states
                case "12D1A":
                    if ($n > 0) { // "If you do"
                        self::executeDraw($player_id, 1); // "Draw a 1"
                    }
                    break;
                    
                // id 13, age 1: Code of laws
                case "13N1A":
                    if ($n > 0) { // "If you do"
                        self::incrementStepMax(1);
                    }
                    break;
                
                // id 16, age 2: Mathematics
                case "16N1A":
                    if ($n > 0) { // "If you do"
                        self::executeDrawAndMeld($player_id, $this->innovationGameState->get('age_last_selected') + 1); // "Draw and meld a card of value one higher than the card you returned"
                    }
                    break;
               
                // id 17, age 2: Construction
                case "17D1A":
                    self::executeDraw($player_id, 2); // "Draw a 2"
                    break;
                
                // id 18, age 2: Road building
                case "18N1A":
                    if ($n == 2) { // "If you melded two"
                        if (self::getTopCardOnBoard($player_id, 1) !== null) { // The player has a top red board card
                            self::incrementStepMax(1);
                        }
                        else {
                            self::notifyPlayer($player_id, 'log', clienttranslate('${You} have no top red card on your board.'), array('You' => 'You'));
                            self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} has no top red card on your board.'), array('player_name' => self::renderPlayerName($player_id)));
                        }
                    }
                    break;
                    
                // id 19, age 2: Currency
                case "19N1A":
                    if ($n > 0) { // "If you do"
                        $different_values_selected_so_far = self::getAuxiliaryValueAsArray();
                        $number_of_cards_to_score = count($different_values_selected_so_far);
                        
                        if ($number_of_cards_to_score == 1) {
                            self::notifyPlayer($player_id, 'log', clienttranslate('Each card ${you} returned has the same value.'), array('you' => 'you'));
                            self::notifyAllPlayersBut($player_id, 'log', clienttranslate('Each card ${player_name} returned has the same value.'), array('player_name' => self::renderPlayerName($player_id)));
                        }
                        else if ($number_of_cards_to_score > 1) {
                            $n = self::renderNumber($number_of_cards_to_score);
                            self::notifyPlayer($player_id, 'log', clienttranslate('${You} returned cards of ${n} different values.'), array('i18n' => array('n'), 'You' => 'You', 'n' => $n));
                            self::notifyAllPlayersBut($player_id, 'log', clienttranslate('There are ${n} different values that can be found in the cards ${player_name} returned.'), array('i18n' => array('n'), 'player_name' => self::renderPlayerName($player_id), 'n' => $n));
                        }
                        
                        // "For every different value of card you returned"
                        for($i=0; $i<$number_of_cards_to_score; $i++) {
                            self::executeDraw($player_id, 2, 'score'); // "Draw and score a 2"
                        }
                    }
                    break;
                    
                // id 20, age 2: Mapmaking
                case "20D1A":
                    if ($n > 0) {
                        self::setAuxiliaryValue(1); // A transfer has been made, flag it
                    }
                    break;
                    
                // id 23, age 2: Monotheism        
                case "23D1A":
                    if ($n > 0) { // "If you do"
                        self::executeDrawAndTuck($player_id, 1); // "Draw an tuck a 1"
                    }
                    break;
                
                // id 30, age 3: Paper
                case "30N2A_4E":
                    if ($n > 0) { // "If you do, draw a 4 for every color you have splayed left"
                        $number_of_colors_splayed_left = 0;
                        for ($color = 0; $color < 5 ; $color++) {
                            if (self::getCurrentSplayDirection($player_id, $color) == Directions::LEFT) {
                                $number_of_colors_splayed_left++;
                            }
                        }
                        if ($number_of_colors_splayed_left == 1) {
                            self::notifyPlayer($player_id, 'log', clienttranslate('${You} have ${n} color splayed left.'), array('i18n' => array('n'), 'You' => 'You', 'n' => $number_of_colors_splayed_left));
                            self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} has ${n} color splayed left.'), array('i18n' => array('n'), 'player_name' => self::renderPlayerName($player_id), 'n' => $number_of_colors_splayed_left));
                        } else {
                            self::notifyPlayer($player_id, 'log', clienttranslate('${You} have ${n} colors splayed left.'), array('i18n' => array('n'), 'You' => 'You', 'n' => $number_of_colors_splayed_left));
                            self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} has ${n} colors splayed left.'), array('i18n' => array('n'), 'player_name' => self::renderPlayerName($player_id), 'n' => $number_of_colors_splayed_left));
                        }
                        for ($i = 0; $i < $number_of_colors_splayed_left; $i++) {
                            self::executeDraw($player_id, 4);
                        }
                    }
                    break;
                            
                // id 32, age 3: Medicine
                case "32D1B":
                    // Finish the exchange
                    $this->gamestate->changeActivePlayer($launcher_id); // This exchange was initiated by $launcher_id
                    $id = self::getAuxiliaryValue();
                    if ($id != -1) { // The attacking player could indeed choose a card
                        $card = self::getCardInfo($id);
                        self::transferCardFromTo($card, $player_id, 'score'); // $launcher_id -> $player_id
                        self::setAuxiliaryValue(-1);
                    }
                    break;
                
                // id 33, age 3: Education        
                case "33N1A":
                    if ($n > 0) { // "If you do"
                        self::executeDraw($player_id, self::getMaxAgeInScore($player_id) + 2); // "Draw a card of value two higher than the highest card remaining in your score pile"
                    }
                    break;
                    
                // id 34, age 3: Feudalism        
                case "34D1A":
                    if ($this->innovationGameState->usingThirdEditionRules()) {
                        if ($n > 0) { // "If you do"
                            self::unsplay($player_id, $player_id, $this->innovationGameState->get('color_last_selected')); // "Unsplay that color of your cards"
                        }
                    } else if ($this->innovationGameState->usingFourthEditionRules()) {
                        // "junk all available special achievements!"
                        $achievement_cards = self::getCardsInLocation(0, 'achievements');
                        foreach ($achievement_cards as $card) {
                            if ($card['age'] == null) {
                                self::transferCardFromTo($card, 0, 'junk');
                            }
                        }
                    }
                    break;

                case "34N1A":
                    // "If you do, draw a 3"
                    // NOTE: This only occurs in the 4th edition and beyond
                    if ($n > 0 && $this->innovationGameState->usingFourthEditionRules()) {
                        self::executeDraw($player_id, 3);
                    }
                    break;
                    
                // id 36, age 4: Printing press        
                case "36N1A":
                    if ($n > 0) { // "If you do"
                        $top_purple_card = self::getTopCardOnBoard($player_id, Colors::PURPLE);
                        if ($top_purple_card !== null) {
                            self::executeDraw($player_id, $top_purple_card['age'] + 2); // "Draw a card of value two higher than the top purple card on your board"
                        }
                        else {
                            self::executeDraw($player_id, 2); // If no purple card, draw a 2.
                        }
                    }
                    break;
                    
                // id 38, age 4: Gunpowder
                case "38D1A":
                    if ($n > 0) {
                        self::setAuxiliaryValue(1); // A transfer has been made, flag it
                    }
                    break;
                    
                // id 39, age 4: Invention
                case "39N1A":
                    if ($n > 0) { // "If you do"
                        self::executeDraw($player_id, 4, 'score'); // "Draw and score a 4"
                    }
                    break;
                    
                // id 41, age 4: Anatomy
                case "41D1A":
                    if ($n > 0) { // "If you do"
                        self::setAuxiliaryValue($this->innovationGameState->get('age_last_selected')); // Save the age of the returned card
                        self::incrementStepMax(1);
                    }
                    break;
                    
                case "41D1B":
                    // "If you do, junk all cards in the 4 deck"
                    if ($n > 0 && $this->innovationGameState->usingFourthEditionRules()) {
                        self::junkBaseDeck(4);
                    }
                    break;
                
                // id 42, age 4: Perspective
                case "42N1A":
                    if ($n > 0) { // "If you do"
                        self::incrementStepMax(1);
                    }
                    break;
                    
                // id 43, age 4: Enterprise
                case "43D1A":
                    if ($n > 0) { // "If you do"
                        self::executeDrawAndMeld($player_id, 4); // "Draw and meld a 4"
                    }
                    break;
                
                // id 47, age 5: Coal
                case "47N3A":
                    if ($n > 0) { // "If you do"
                        $card = self::getTopCardOnBoard($player_id, $this->innovationGameState->get('color_last_selected'));
                        if ($card !== null) { // Check if the p^layer has a card beneath the card he scored
                            self::scoreCard($card, $player_id); // "Also score the card beneath it"
                        }
                    }                
                    break;

                // id 48, age 5: The pirate code
                case "48D1A":
                    if ($n > 0) {
                        self::setAuxiliaryValue(1); // A transfer has been made, flag it
                    }
                    break;

                // id 49, age 5: Banking
                case "49D1A":
                    if ($n > 0) { // "If you do"
                        self::executeDraw($player_id, 5, 'score'); // "Draw and score a 5"
                    }
                    break;
                    
                // id 50, age 5: Measurement
                case "50N1A":
                    if ($n > 0) { // "If you do"
                        if ($this->innovationGameState->usingFirstEditionRules()) {
                            // In the first edition, color is chosen by the player
                            self::incrementStepMax(1);
                        } else {
                            $color = $this->innovationGameState->get('color_last_selected');
                            self::splayRight($player_id, $player_id, $color); // "Splay that color of your cards right"
                            $number_of_cards = self::countCardsInLocationKeyedByColor($player_id, 'board')[$color];
                            if ($number_of_cards == 1) {
                                self::notifyPlayer($player_id, 'log', clienttranslate('${You} have ${n} ${colored} card.'), array('i18n' => array('n', 'colored'), 'You' => 'You', 'n' => self::renderNumber($number_of_cards), 'colored' => Colors::render($color)));
                                self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} has  ${n} ${colored} card.'), array('i18n' => array('n', 'colored'), 'player_name' => self::renderPlayerName($player_id), 'n' => self::renderNumber($number_of_cards), 'colored' => Colors::render($color)));
                            } else {
                                self::notifyPlayer($player_id, 'log', clienttranslate('${You} have ${n} ${colored_cards}.'), array('i18n' => array('n', 'colored_cards'), 'You' => 'You', 'n' => self::renderNumber($number_of_cards), 'colored_cards' => self::renderColorCards($color)));
                                self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} has  ${n} ${colored_cards}.'), array('i18n' => array('n', 'colored_cards'), 'player_name' => self::renderPlayerName($player_id), 'n' => self::renderNumber($number_of_cards), 'colored_cards' => self::renderColorCards($color)));
                            }
                            self::executeDraw($player_id, $number_of_cards); // "Draw a card of value equal to the number of cards of that color on your board"
                        }
                    }
                    break;
                    
                // id 51, age 5: Statistics
                case "51D1A":
                    // First edition only
                    if ($n > 0 && self::countCardsInLocation($player_id, 'hand') == 1) { // "If you do, and have only one card in hand afterwards"
                        self::notifyPlayer($player_id, 'log', clienttranslate('${You} have now only one card in your hand.'), array('You' => 'You'));
                        self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} has now only one card in his hand.'), array('player_name' => self::renderPlayerName($player_id)));
                        $step--; self::incrementStep(-1); // --> "Repeat this demand"
                    }
                    break;
                    
                // id 54, age 5: Societies
                case "54D1A":
                    if ($n > 0) { // "If you do"
                        self::executeDraw($player_id, 5); // Draw a 5
                    }
                    break;
                    
                // id 59, age 6: Classification
                case "59N1A":
                    if ($n > 0) { // Unsaid rule: the player must have at least one card to show from his hand, else, the effect can't continue
                        $color = $this->innovationGameState->get('color_last_selected');
                        self::setAuxiliaryValue($color); // Save the color of the revealed card
                        $revealed_card = self::getCardInfo($this->innovationGameState->get('id_last_selected'));
                        self::revealHand($player_id);
                        foreach (self::getActiveOpponentIds($player_id) as $other_player_id) {
                            self::revealHand($other_player_id);
                            $transfer = false;
                            foreach (self::getIdsOfCardsInLocation($other_player_id, 'hand') as $id) {
                                $card = self::getCardInfo($id);
                                if ($card['color'] == $color) { // This card must be given to the player
                                    self::transferCardFromTo($card, $player_id, 'hand'); // "Take into your hand all cards of that color from all other player's hands"
                                    $transfer = true;
                                }
                            }
                            if (!$transfer) { // The player had no card of this color in his hand
                                $color_in_clear = Colors::render($color);
                                self::notifyPlayer($other_player_id, 'log', clienttranslate('${You} have no ${colored} cards in your hand.'), array('i18n' => array('colored'), 'You' => 'You', 'colored' => $color_in_clear));
                                self::notifyAllPlayersBut($other_player_id, 'log', clienttranslate('${player_name} has no ${colored} cards in his hand.'), array('i18n' => array('colored'), 'player_name' => self::renderPlayerName($other_player_id), 'colored' => $color_in_clear));
                            }
                        }
                        self::transferCardFromTo($revealed_card, $player_id, 'hand'); // Place back the card into player's hand
                        self::incrementStepMax(1);
                    }
                    break;
                    
                // id 62, age 6: Vaccination
                case "62D1A":
                    if ($n > 0) { // "If you returned any"
                        self::executeDrawAndMeld($player_id, 6); // "Draw and meld a 6"
                        self::setAuxiliaryValue(1); // Flag that a card has been returned
                    }
                    break;
                    
                // id 63, age 6: Democracy
                case "63N1A":
                    // "If you have returned more cards than any other player due to Democracy so far during this dogma action, draw and score an 8."
                    $num_cards_returned_by_this_player = $n + self::getUniqueValueFromDB(self::format("SELECT democracy_counter FROM player WHERE player_id = {player_id}", array('player_id' => $player_id)));
                    $max_cards_returned_by_another_player = self::getUniqueValueFromDB(self::format("SELECT MAX(democracy_counter) FROM player WHERE player_id != {player_id}", array('player_id' => $player_id)));
                    if ($num_cards_returned_by_this_player > $max_cards_returned_by_another_player) {
                        self::executeDraw($player_id, 8, 'score');
                        self::DbQuery(self::format("UPDATE player SET democracy_counter = {count} WHERE player_id = {player_id}", array('count' => $num_cards_returned_by_this_player, 'player_id' => $player_id)));
                    }
                    break;
                    
                // id 64, age 6: Emancipation
                case "64D1A":
                    if ($n > 0) { // "If you do"
                        self::executeDraw($player_id, 6); // "Draw a 6"
                    }
                    break;
                
                // id 68, age 7: Explosives
                case "68D1A":
                    // TODO(LATER): Remove the use of the auxilary value.
                    if ($n > 0) {
                        self::setAuxiliaryValue(1);  // Flag that at least one card has been transfered
                    }
                    break;
                    
                case "68D1C":
                    // TODO(LATER): Remove the use of the auxilary value.
                    if (self::getAuxiliaryValue() == 1 && self::countCardsInLocation($player_id, 'hand') == 0) { // "If you transferred any, and then have no cards in hand"
                        self::executeDraw($player_id, 7); // "Draw a 7"
                    }
                    break;
                
                // id 70, age 7: Electricity
                case "70N1A":
                    for($i=0; $i<$n; $i++){ // "For each card you returned"
                        self::executeDraw($player_id, 8);  // "Draw a 8"
                    }
                    break;
    
                // id 73, age 7: Lighting
                case "73N1A":
                    if ($n > 0) { // "If you do"
                        $different_values_selected_so_far = self::getAuxiliaryValueAsArray();
                        $number_of_cards_to_score = count($different_values_selected_so_far);
                        
                        if ($number_of_cards_to_score == 1) {
                            self::notifyPlayer($player_id, 'log', clienttranslate('Each card ${you} tucked has the same value.'), array('you' => 'you'));
                            self::notifyAllPlayersBut($player_id, 'log', clienttranslate('Each card ${player_name} tucked has the same value.'), array('player_name' => self::renderPlayerName($player_id)));
                        }
                        else if ($number_of_cards_to_score > 1) {
                            $n = self::renderNumber($number_of_cards_to_score);
                            self::notifyPlayer($player_id, 'log', clienttranslate('${You} tucked cards of ${n} different values.'), array('i18n' => array('n'), 'You' => 'You', 'n' => $n));
                            self::notifyAllPlayersBut($player_id, 'log', clienttranslate('There are ${n} different values that can be found in the cards ${player_name} tucked.'), array('i18n' => array('n'), 'player_name' => self::renderPlayerName($player_id), 'n' => $n));
                        }
                        
                        // "For every different value of card you tucked"
                        for($i=0; $i<$number_of_cards_to_score; $i++) {
                            self::executeDraw($player_id, 7, 'score'); // "Draw and score a 7"
                        }
                    }
                    break;
                
                // id 74, age 7: Railroad        
                case "74N1A_3E":
                    // "Draw three 6"
                    self::executeDraw($player_id, 6);
                    self::executeDraw($player_id, 6);
                    self::executeDraw($player_id, 6);
                    break;
                
                // id 75, age 8: Quantum theory        
                case "75N1A":
                    if ($n == 2) { // "If you return two"
                        self::executeDraw($player_id, 10); // "Draw a 10"
                        self::executeDraw($player_id, 10, 'score'); // "Draw and score a 10"
                    }
                    break;
                
                // id 78, age 8: Mobility        
                case "78D1A":
                    if ($n > 0) {
                        $color = $this->innovationGameState->get('color_last_selected');
                        $selectable_colors = self::getAuxiliaryValueAsArray();
                        $selectable_colors = array_diff($selectable_colors, array($color)); // Remove the color of the card the player has chosen: he could not choose the same for his next card
                        self::setAuxiliaryValueFromArray($selectable_colors);
                    }
                    break;
                    
                case "78D1B":
                    if (self::getAuxiliaryValueAsArray() <> array(0,2,3,4)) { // "If you transferred any cards" (ie: a color has been removed from the initial array)
                        self::executeDraw($player_id, 8); // "Draw a 8"
                    }
                    break;
                    
                // id 79, age 8: Corporations        
                case "79D1A":
                    if ($n > 0) { // "If you transfered any cards"
                        self::executeDrawAndMeld($player_id, 8); // "Draw and meld a 8"
                    }
                    break;

                // id 80, age 8: Mass media         
                case "80N1A":
                    if ($n > 0) { // "If you do
                        self::incrementStepMax(2);
                    }
                    break;
                
                // id 81, age 8: Antibiotics
                case "81N1A":
                    if ($n > 0) { // If you do (implicit)
                        $different_values_selected_so_far = self::getAuxiliaryValueAsArray();
                        $number_of_cards_to_draw = count($different_values_selected_so_far);
                        
                        if ($number_of_cards_to_draw == 1) {
                            self::notifyPlayer($player_id, 'log', clienttranslate('Each card ${you} returned has the same value.'), array('you' => 'you'));
                            self::notifyAllPlayersBut($player_id, 'log', clienttranslate('Each card ${player_name} returned has the same value.'), array('player_name' => self::renderPlayerName($player_id)));
                        }
                        else if ($number_of_cards_to_draw > 1) {
                            $n = self::renderNumber($number_of_cards_to_draw);
                            self::notifyPlayer($player_id, 'log', clienttranslate('${You} returned cards of ${n} different values.'), array('i18n' => array('n'), 'You' => 'You', 'n' => $n));
                            self::notifyAllPlayersBut($player_id, 'log', clienttranslate('There are ${n} different values that can be found in the cards ${player_name} returned.'), array('i18n' => array('n'), 'player_name' => self::renderPlayerName($player_id), 'n' => $n));
                        }
                        
                        // "For every different value of card that you returned"
                        for($i=0; $i<$number_of_cards_to_draw; $i++) {
                            self::executeDraw($player_id, 8); // "Draw two 8"
                            self::executeDraw($player_id, 8); //
                        }
                    }
                    break;
                
                // id 82, age 8: Skyscrapers
                case "82D1A":
                    if ($n > 0) { // "If you do
                        $color = $this->innovationGameState->get('color_last_selected');
                        $card = self::getTopCardOnBoard($player_id, $color); // The card now on top of the pile
                        if ($card !== null) {
                            self::scoreCard($card, $player_id); // "Score the card beneath it"
                        }
                        self::setAuxiliaryValue($color);// Flag the chosen color for the next interaction
                        self::incrementStepMax(1);
                    }
                    break;
                
                case "82D1B":
                    if ($this->innovationGameState->usingFourthEditionRules()) {
                        $skyscrapers_card = self::getIfTopCardOnBoard(82);
                        if ($skyscrapers_card !== null) {
                            // "transfer Skyscrapers to my hand if it is a top card!"
                            self::transferCardFromTo($skyscrapers_card, $launcher_id, 'hand');
                        }
                    }
                    break;
                                    
                // id 84, age 8: Socialism     
                case "84N1A_3E":
                    if ($n > 0) {
                        if (self::getAuxiliaryValue() == 1) { // "If you tucked at least one purple card"
                            self::notifyGeneralInfo(clienttranslate('At least one purple card has been tucked.'));
                            
                            foreach (self::getActiveOpponentIds($player_id) as $opponent_id) {
                                $ids_of_lowest_cards_in_hand = self::getIdsOfLowestCardsInLocation($opponent_id, 'hand');
                                foreach ($ids_of_lowest_cards_in_hand as $card_id) {
                                    $card = self::getCardInfo($card_id);
                                    self::transferCardFromTo($card, $player_id, 'hand'); // "Take all the lowest cards in each other opponent's hand into your hand" 
                                }
                            }
                        } else {
                            self::notifyGeneralInfo(clienttranslate('No purple card has been tucked.'));
                        }
                    }
                    break;
                
                case "84N1A_4E":
                    // If a top card is tucked, then proceed to make the player tuck all cards from their hand
                    if ($n > 0) {
                        self::incrementStepMax(1);
                    }
                    break;
                
                // id 88, age 9: Fission
                case "88N1A":
                    if ($this->innovationGameState->usingThirdEditionRules()) {
                        self::executeDraw($player_id, 10); // "Draw a 10"
                    }
                    break;
                
                // id 89, age 9: Collaboration
                case "89D1A":
                    $this->gamestate->changeActivePlayer($player_id);
                    $remaining_revealed_card = self::getCardsInLocation($player_id, 'revealed')[0]; // There is one card left revealed
                    self::meldCard($remaining_revealed_card, $player_id); // "Meld the other one"
                    break;
                    
                // id 90, age 9: Satellites
                case "90N1A_3E":
                    // "Draw three 8"
                    self::executeDraw($player_id, 8);
                    self::executeDraw($player_id, 8);
                    self::executeDraw($player_id, 8);
                    break;
                    
                case "90N3A_3E":
                case "90N3A_4E":
                    if ($n > 0) {
                        $card = self::getCardInfo($this->innovationGameState->get('id_last_selected')); // The card the player melded from his hand
                        self::selfExecute($card); // "Execute each of its non-demand dogma effects"
                    }
                    break;
                    
                // id 91, age 9: Ecology
                case "91N1A":
                    if ($n > 0) { // "If you do
                        self::incrementStepMax(1);
                    }
                    break;
                    
                case "91N1B":
                    self::executeDraw($player_id, 10); // "Draw two 10"
                    self::executeDraw($player_id, 10); //
                    break;

                // id 92, age 9: Suburbia
                case "92N1A":
                    for($i=0; $i<$n; $i++) { // "For each card you tucked"
                        self::executeDraw($player_id, 1, 'score'); // "Draw and score a 1"
                    }
                    break;
                    
                // id 94, age 9: Specialization
                case "94N1A":
                    if ($n > 0) { // Unsaid rule: the player must have at least one card to show from his hand, else, the effect cant' continue
                        $color = $this->innovationGameState->get('color_last_selected');
                        $revealed_card = self::getCardInfo($this->innovationGameState->get('id_last_selected'));
                        
                        foreach (self::getActiveOpponentIds($player_id) as $opponent_id) { // "From all other opponents' boards"
                            $top_card = self::getTopCardOnBoard($opponent_id, $color);
                            if ($top_card !== null) { // If the opponent has indeed a top card of that color on his board
                                self::transferCardFromTo($top_card, $player_id, 'hand'); // "Take into your hand the top card of that color"
                            }
                        }
                        self::transferCardFromTo($revealed_card, $player_id, 'hand'); // Place back the card into player's hand
                    }
                    break;
                    
                // id 97, age 10: Miniaturization
                case "97N1A":
                    // Only proceed if a card was returned
                    if ($n <= 0) {
                        break;
                    }

                    $age_last_selected = $this->innovationGameState->get('age_last_selected');
                    if ($age_last_selected == 10) { // "If you returned a 10"
                        $number_of_cards_in_score = self::countCardsInLocationKeyedByAge($player_id, 'score');
                        $number_of_different_value = 0;
                        for($age=1; $age<=11; $age++) {
                            if ($number_of_cards_in_score[$age] > 0) {
                                $number_of_different_value++;
                            }
                        }
                        
                        if ($number_of_different_value == 0) {
                            self::notifyPlayer($player_id, 'log', clienttranslate('${You} have no card in your score pile.'), array('You' => 'You'));
                            self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name}\'s has no card in his score pile.'), array('player_name' => self::renderPlayerName($player_id)));
                        }
                        else if ($number_of_different_value == 1) {
                            self::notifyPlayer($player_id, 'log', clienttranslate('Each card in ${your} score pile has the same value.'), array('your' => 'your'));
                            self::notifyAllPlayersBut($player_id, 'log', clienttranslate('Each card ${player_name}\'s score pile has the same value.'), array('player_name' => self::renderPlayerName($player_id)));
                        }
                        else if ($number_of_different_value > 1) {
                            $n = self::renderNumber($number_of_different_value);
                            self::notifyPlayer($player_id, 'log', clienttranslate('There are ${n} different values that can be found in ${your} score pile.'), array('i18n' => array('n'), 'your' => 'your', 'n' => $n));
                            self::notifyAllPlayersBut($player_id, 'log', clienttranslate('There are ${n} different values that can be found in ${player_name}\'s score pile.'), array('i18n' => array('n'), 'player_name' => self::renderPlayerName($player_id), 'n' => $n));
                        }
                        
                        for($i=0; $i<$number_of_different_value; $i++) { // "For every different value of card in your score pile"
                            self::executeDraw($player_id, 10); // "Draw a 10"                    
                        }
                    } else {
                        if ($this->innovationGameState->usingFourthEditionRules()) {
                            if  ($age_last_selected == 11) {
                                // "If you returned an 11, junk all cards in the 11 deck."
                                self::junkBaseDeck(11);
                            } else {
                                self::notifyGeneralInfo(clienttranslate('The returned card is not of value ${age10} or ${age11}.'), array('age10' => self::getAgeSquare(10), 'age11' => self::getAgeSquare(11)));
                            }
                        } else {
                            self::notifyGeneralInfo(clienttranslate('The returned card is not of value ${age}.'), array('age' => self::getAgeSquare(10)));
                        }
                    }
                    break;

                // id 136, Artifacts age 3: Charter of Liberties
                case "136N1A":
                    if ($n > 0) {
                        // "If you do, splay left its color"
                        self::splayLeft($player_id, $player_id, $this->innovationGameState->get('color_last_selected'));
                        self::incrementStepMax(1);
                    }
                    break;

                case "136N1B":
                    if ($n > 0) {
                        // "Execute all of that color's top card's non-demand effects, without sharing"
                        self::selfExecute(self::getCardInfo($this->innovationGameState->get('id_last_selected')));
                    }
                    break;
                    
                // id 138, Artifacts age 3: Mjolnir Amulet
                case "138C1A":
                    if ($n > 0) {
                        // "Transfer all cards of that card's color from your board to my score pile"
                        $board = self::getCardsInLocationKeyedByColor($player_id, 'board');
                        $pile = $board[$this->innovationGameState->get('color_last_selected')];
                        for ($i = count($pile) - 1; $i >= 0; $i--) {
                            self::transferCardFromTo($pile[$i], $launcher_id, 'score'); 
                        }
                    }
                    break;

                // id 139, Artifacts age 3: Philosopher's Stone
                case "139N1A":
                    // Move to next interaction if a card was returned
                    if ($n > 0) {
                        self::incrementStepMax(1);
                    }
                    break;
            
                // id 141, Artifacts age 3: Moylough Belt Shrine
                case "141C1A":
                    // Return revealed cards back to player's hand.
                    $this->gamestate->changeActivePlayer($player_id);
                    $cards = self::getCardsInLocation($player_id, 'revealed');
                    foreach ($cards as $card) {
                        self::transferCardFromTo($card, $player_id, 'hand');
                    }
                    break;
                    
                // id 143, Artifacts age 3: Necronomicon
                case "143N1A":
                    if (self::getAuxiliaryValue() == 1) { // Red
                        $revealed_card = self::getCardsInLocation($player_id, 'revealed')[0];
                        self::transferCardFromTo($revealed_card, $player_id, 'hand'); // Keep revealed card
                    }
                    break;

                // id 144, Artifacts age 3: Shroud of Turin
                case "144N1A":
                    if ($n > 0) {
                        self::notifyGeneralInfo(clienttranslate('This card is ${color}.'), array(
                            'i18n' => array('color'),
                            'color' => Colors::render($this->innovationGameState->get('color_last_selected'))
                        ));
                        self::setAuxiliaryValue(1);
                        self::incrementStepMax(2);
                    }
                    break;
                
                case "144N1B":
                    if ($n > 0) {
                        self::setAuxiliaryValue(2);
                    }
                    break;
                
                case "144N1C":
                    // "If you did all three"
                    if ($n == 0) {
                        // Reveal score pile to prove that no cards of the specified color could have been returned
                        self::revealScorePile($player_id);
                    } else if ($n > 0 && self::getAuxiliaryValue() == 2) {
                        self::incrementStepMax(1);
                    }
                    break;

                // id 146, Artifacts age 4: Delft Pocket Telescope
                case "146N1A":
                    // Reset the max steps in case the effect is being repeated
                    self::setStepMax(1);

                    if ($n > 0) { // "If you do"
                        // "Draw a 5 and a 6"
                        $card_1 = self::executeDraw($player_id, 5);
                        $this->innovationGameState->set('card_id_1', $card_1['id']);
                        $card_2 = self::executeDraw($player_id, 6);
                        $this->innovationGameState->set('card_id_2', $card_2['id']);

                        // Check if any icons on the returned card match one of the drawn cards
                        $returned_card = self::getCardInfo($this->innovationGameState->get('id_last_selected'));
                        $matching_icon_on_card_1 = false;
                        $matching_icon_on_card_2 = false;
                        for ($icon = 1; $icon <= 7; $icon++) { 
                            $has_icon = self::hasRessource($returned_card, $icon);
                            if ($has_icon && self::hasRessource($card_1, $icon)) {
                                $matching_icon_on_card_1 = true;
                            }
                            if ($has_icon && self::hasRessource($card_2, $icon)) {
                                $matching_icon_on_card_2 = true;
                            }
                        }
                        
                        if (!$matching_icon_on_card_1 && !$matching_icon_on_card_2) {
                            // Reveal cards to prove that no icons matched with the previously returned card
                            self::transferCardFromTo($card_1, $player_id, 'revealed');
                            self::transferCardFromTo($card_2, $player_id, 'revealed');
                            self::notifyGeneralInfo(clienttranslate('Neither card has a symbol in common with the returned card.'));
                            self::setStepMax(2);
                        } else {
                            // Skip to the third interaction
                            $step++;
                            self::incrementStep(1);
                            self::setStepMax(3);

                            // Remove card as an option if it does not have any matching symbols
                            if (!$matching_icon_on_card_1) {
                                $this->innovationGameState->set('card_id_1', -1);
                            } else if (!$matching_icon_on_card_2) {
                                $this->innovationGameState->set('card_id_2', -1);
                            }
                        }
                    }
                    break;

                case "146N1B":
                    $step = $step - 2;
                    self::incrementStep(-2); // "And repeat this effect"
                    break;

                case "146N1C":
                    // Move revealed card back to the player's hand
                    self::transferCardFromTo(self::getCardInfo($this->innovationGameState->get('id_last_selected')), $player_id, 'hand');
                    break;
                    
                // id 147, Artifacts age 4: East India Company Charter
                case "147N1B":
                    // "For each player that returned cards, draw and score a 5"
                    for ($i = 0; $i < self::getAuxiliaryValue(); $i++) {
                        self::executeDraw($player_id, 5, 'score');
                    }
                    break;
                    
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
                    
                // id 150, Artifacts age 4: Hunt-Lenox Globe
                case "150N1A":
                    if (self::getAuxiliaryValue() == 1) {
                        // "Draw a 5 for each card returned"
                        for ($i = 1; $i <= $n; $i++) {
                            self::executeDraw($player_id, 5);
                        }
                    }
                    break;

                // id 157, Artifacts age 5: Bill of Rights
                case "157C1A":
                    $color = self::getAuxiliaryValue();
                    do {
                        // "Transfer all cards of that color from your board to my board, from the bottom up!"
                        $card = self::getBottomCardOnBoard($player_id, $color);
                        if ($card != null) {
                            self::transferCardFromTo($card, $launcher_id, 'board');
                        }
                    } while ($card != null);
                    break;

                // id 167, Artifacts age 6: Frigate Constitution
                case "167C1A":
                    if ($n > 0) { // "If you do"
                        // "and its value is equal to the value of any of my top cards"
                        $value_to_match = $this->innovationGameState->get('age_last_selected');
                        $found_match = false;
                        $top_cards = self::getTopCardsOnBoard($launcher_id);
                        foreach ($top_cards as $top_card) {
                            if ($top_card['faceup_age'] == $value_to_match) {
                                self::incrementStepMax(1);
                                $found_match = true;
                                break;
                            }
                        }
                        // Otherwise keep the card
                        if (!$found_match) {
                            $card = self::getCardInfo($this->innovationGameState->get('id_last_selected'));
                            self::transferCardFromTo($card, $player_id, 'hand');
                        }
                    }
                    break;
                    
                // id 152, Artifacts age 4: Mona Lisa
                case "152N1B":
                    // "Draw five 4s, then reveal your hand"
                    for ($i = 0; $i < 5; $i++) {
                        self::executeDraw($player_id, 4);
                    }
                    self::revealHand($player_id);
                    
                    $chosen_color = self::getAuxiliaryValue2();
                    $cards = self::getCardsInLocationKeyedByColor($player_id, 'hand');
                    $colored_cards = $cards[$chosen_color];
                    $num_cards = count($colored_cards);
                    self::notifyPlayer($player_id, 'log', clienttranslate('${You} revealed ${n} ${color} cards.'), array('i18n' => array('color'), 'You' => 'You', 'n' => $num_cards, 'color' => Colors::render($chosen_color)));
                    self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} revealed ${n} ${color} cards.'), array('i18n' => array('color'), 'player_name' => self::renderPlayerName($player_id), 'n' => $num_cards, 'color' => Colors::render($chosen_color)));
                    
                    // "If you have exactly that many cards of that color, score them, and splay right your cards of that color"
                    if ($num_cards == self::getAuxiliaryValue()) {
                        foreach ($colored_cards as $card) {
                            $card = self::getCardInfo($card['id']);
                            self::scoreCard($card, $player_id);
                        }
                        self::splayRight($player_id, $player_id, $chosen_color);
                    // "Otherwise"
                    } else {
                        self::incrementStepMax(1);
                    }
                    break;

                // id 155, Artifacts age 5: Boerhavve Silver Microscope
                case "155N1A":
                    if ($n > 0) {
                        self::setAuxiliaryValue($this->innovationGameState->get('age_last_selected'));
                    } else {
                        self::setAuxiliaryValue(0);
                    }
                    break;

                case "155N1B":
                    $first_age = self::getAuxiliaryValue();
                    if ($n > 0) {
                        $second_age = self::getFaceupAgeLastSelected();
                    } else {
                        $second_age = 0;
                    }
                    
                    // "Draw and score a card of value equal to the sum of the values of the cards returned"
                    $sum = $first_age + $second_age;
                    self::notifyGeneralInfo(clienttranslate('The values sum to ${number}'), array('number' => $sum));
                    self::executeDraw($player_id, $sum, 'score');
                    break;
                    
                // id 156, Artifacts age 5: Principia
                case "156N1A":
                    $ages_on_top = Arrays::getBase16ArrayFromValue(self::getAuxiliaryValue());
                    sort($ages_on_top);
                    // "For each card returned, draw and meld a card of value one higher than the value of the returned card, in ascending order"
                    foreach ($ages_on_top as $card_age) {
                        self::executeDrawAndMeld($player_id, $card_age + 1);
                    }
                    break;

                // id 158, Artifacts age 5: Ship of the Line Sussex
                case "158N1B":
                    // "Score all cards of that color from your board"
                    $board = self::getCardsInLocationKeyedByColor($player_id, 'board');
                    $pile = $board[self::getAuxiliaryValue()];
                    for ($i = count($pile) - 1; $i >= 0; $i--) {
                        self::scoreCard($pile[$i], $player_id);
                    }
                    break;

                // id 160, Artifacts age 5: Hudson's Bay Company Archives
                case "160N1A":
                    if ($n > 0) {
                        // "Splay right the color of the melded card"
                        self::splayRight($player_id, $player_id, $this->innovationGameState->get('color_last_selected'));
                    }
                    break;

                // id 161, Artifacts age 5: Gujin Tushu Jinsheng
                case "161N1A":
                    // "Execute the effects on the chosen card as if they were on this card. Do not share them"
                    if ($n > 0) {
                        self::fullyExecute(self::getCardInfo($this->innovationGameState->get('id_last_selected')));
                    }
                    break;

                // id 162, Artifacts age 5: The Daily Courant
                case "162N1A":
                    // "Draw a card of any value"
                    $card = self::executeDraw($player_id, self::getAuxiliaryValue());
                    $this->innovationGameState->set('card_id_1', $card['id']);
                    break;

                case "162N1C":
                    // "Execute the effects of one of your other top cards as if they were on this card. Do not share them."
                    if ($n > 0) {
                        self::fullyExecute(self::getCardInfo($this->innovationGameState->get('id_last_selected')));
                    }
                    break;

                // id 164, Artifacts age 5: Almira, Queen of the Castle
                case "164N1A":
                    if ($n > 0) { // If no card is melded, then the value cannot match an achievement
                        self::incrementStepMax(1);
                    }
                    break;

                // id 165, Artifacts age 6: Kilogram of the Archives
                case "165N1A":
                    if ($n > 0) {
                        // Log the value of the first returned card
                        self::setAuxiliaryValue($this->innovationGameState->get('age_last_selected'));
                    } else {
                        // No card was returned
                        self::setAuxiliaryValue(0);
                    }
                    
                    break;

                case "165N1B":
                    // "If you returned two cards and their values sum to ten, draw and score a 10"
                    $first_value = self::getAuxiliaryValue();
                    if ($n > 0) {
                        $second_value = self::getFaceupAgeLastSelected();
                    } else {
                        $second_value = 0;
                    }
                    $sum = $first_value + $second_value;
                    self::notifyGeneralInfo(clienttranslate('The values sum to ${number}'), array('number' => $sum));
                    if ($sum == 10) {
                        self::executeDraw($player_id, 10, 'score');
                    }
                    break;

                // id 166, Artifacts age 6: Puffing Billy
                case "166N1A":
                    if ($n > 0) {
                        // "Draw a card of value equal to the highest number of symbols of the same type visible in that color on your board"
                        $color = $this->innovationGameState->get('color_last_selected');
                        $max_symbols = 0;
                        for ($icon = 1; $icon <= 7; $icon++) {
                            $icon_count = self::countVisibleIconsInPile($player_id, $icon, $color);
                            if ($icon_count > $max_symbols){
                                $max_symbols = $icon_count;
                            }
                        }
                        self::executeDraw($player_id, $max_symbols);

                        // "Splay right that color"
                        self::splayRight($player_id, $player_id, $color);
                    }
                    break;
                    
                // id 170, Artifacts age 6: Buttonwood Agreement
                case "170N1B":
                    // "Unsplay that color"
                    self::unsplay($player_id, $player_id, self::getAuxiliaryValue());
                    break;
                    
                // id 171, Artifacts age 6: Stamp Act
                case "171C1A":
                    if ($n > 0) { // "If you do"
                        $top_green_card = self::getTopCardOnBoard($player_id, 2);
                        if ($top_green_card != null) {
                            self::setAuxiliaryValue($top_green_card['age']);
                            self::incrementStepMax(1);
                        }
                    }
                    break;
 
                // id 173, Artifacts age 6: Moonlight Sonata
                case "173N1A":
                    // "Meld the bottom card on your board of that color"
                    $bottom_card = self::getBottomCardOnBoard($player_id, self::getAuxiliaryValue());
                    self::meldCard($bottom_card, $player_id);
                    break;
            
                // id 174, Artifacts age 6: Marcha Real
                case "174N1A":
                    $card_id_1 = self::getAuxiliaryValue();
                    $card_1 = $card_id_1 < 0 ? null : self::getCardInfo($card_id_1);
                    $card_id_2 = self::getAuxiliaryValue2();
                    $card_2 = $card_id_2 < 0 ? null : self::getCardInfo($card_id_2);

                    if ($card_1 != null && $card_2 != null) {
                        // "If they have the same value, draw a card of value one higher"
                        if ($card_1['age'] == $card_2['age']) {
                            self::notifyGeneralInfo(clienttranslate('They both have the same value.'));
                            self::executeDraw($player_id, $card_1['age'] + 1);
                        } else {
                            self::notifyGeneralInfo(clienttranslate('They do not have the same value.'));
                        }
                        // "If they have the same color, claim an achievement, ignoring eligibility"
                        if ($card_1['color'] == $card_2['color']) {
                            self::notifyGeneralInfo(clienttranslate('They both have the same color.'));
                            self::incrementStepMax(1);
                        } else {
                            self::notifyGeneralInfo(clienttranslate('They do not have the same color.'));
                        }
                    } else if ($card_1 == null && $card_2 == null) { // If none are returned, they are still considered to have the same value (0)
                        self::executeDraw($player_id, 1);
                    }
                    break;

                // id 175, Artifacts age 7: Periodic Table
                case "175N1A":
                    self::setAuxiliaryValue($this->innovationGameState->get('color_last_selected'));
                    break;

                case "175N1B":
                    $color_1 = self::getAuxiliaryValue();
                    $color_2 = $this->innovationGameState->get('color_last_selected');
                    self::notifyPlayer($player_id, 'log', clienttranslate('${You} choose your top ${color_1} and ${color_2} cards.'), array('i18n' => array('color_1', 'color_2'), 'You' => 'You', 'color_1' => Colors::render($color_1), 'color_2' => Colors::render($color_2)));
                    self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} chooses his top ${color_1} and ${color_2} cards.'), array('i18n' => array('color_1', 'color_2'), 'player_name' => self::renderPlayerName($player_id), 'color_1' => Colors::render($color_1), 'color_2' => Colors::render($color_2)));

                    // "Draw a card of value one higher and meld it"
                    $age_selected = self::getFaceupAgeLastSelected();
                    $card = self::executeDrawAndMeld($player_id, $age_selected + 1);
                    
                    // "If it melded over one of the chosen cards, repeat this effect"
                    if ($card['color'] == $color_1 || $card['color'] == $color_2) {
                        // Determine if there are still any top cards which have the same value as another top card on their board
                        $colors = self::getColorsOfRepeatedValueOfTopCardsOnBoard($player_id);
                        if (count($colors) >= 2) {
                            self::setAuxiliaryValueFromArray($colors);
                            $step = $step - 2;
                            self::incrementStep(-2);
                        } else {
                            self::notifyGeneralInfo(clienttranslate("No two top cards have the same value."));
                        }
                    }
                    break;

                // id 179, Artifacts age 7: International Prototype Metre Bar   
                case "179N1A":
                    $age_value = self::getAuxiliaryValue();
                    
                    // "Draw and meld a card of that value"
                    $card = self::executeDrawAndMeld($player_id, $age_value);

                    // "Splay up the color of the melded card"
                    self::splayUp($player_id, $player_id, $card['color']);
                    
                    // "If the number of cards of that color visible on your board is exactly equal to the card's value, you win"
                    if ($card['faceup_age'] == self::countVisibleCards($player_id, $card['color'])) {
                        self::notifyPlayer($player_id, 'log', clienttranslate('${You} melded a card whose value is equal to the number of visible cards in your ${color} stack.'), array('You' => 'You', 'color'=> Colors::render($card['color'])));
                        self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} melded a card whose value is equal to the number of visible cards in his ${color} stack.'), array('player_name' => self::renderPlayerName($player_id), 'color'=> Colors::render($card['color'])));
                        $this->innovationGameState->set('winner_by_dogma', $player_id);
                        self::trace('EOG bubbled from self::stInterInteractionStep International Prototype Metre Bar');
                        throw new EndOfGame();
                    
                    // "Otherwise, return the melded card"
                    } else {
                        self::returnCard($card);
                    }
                    break;
                
                // id 180, Artifacts age 7: Hansen Writing Ball
                case "180C1A":
                    // "Transfer all cards in your hand to my hand"
                    foreach (self::getIdsOfCardsInLocation($player_id, 'hand') as $id) {
                        self::transferCardFromTo(self::getCardInfo($id), $launcher_id, 'hand');
                    }
                    break;
                             
                // id 182, Artifacts age 7: Singer Model 27
                case "182N1A":
                    if ($n > 0) { // "If you do"
                        // "Splay up its color"
                        self::splayUp($player_id, $player_id, $this->innovationGameState->get('color_last_selected'));
                        self::incrementStepMax(1);
                    }
                    break;
                
                case "182N1B":
                    // Reveal remaining score pile to prove that no more cards can be tucked
                    self::revealScorePile($player_id);
                    break;

                // id 183, Artifacts age 7: Roundhay Garden Scene
                case "183N1A":
                     if ($n > 0) {
                        // "Draw and score two cards of value equal to the melded card"
                        $melded_card = self::getCardInfo($this->innovationGameState->get('id_last_selected'));
                        self::executeDraw($player_id, $melded_card['faceup_age'], 'score');
                        self::executeDraw($player_id, $melded_card['faceup_age'], 'score');
                        
                        // "Execute the effects of the melded card as if they were on this card. Do not share them."
                        self::fullyExecute($melded_card);
                    } else { // If no card is melded, the absence is treated like a 0 and cards are still scored.
                        self::executeDraw($player_id, 0, 'score');
                        self::executeDraw($player_id, 0, 'score');
                    }
                    break;
                    
                // id 184, Artifacts age 7: The Communist Manifesto
                case "184N1B":
                    $revealed_cards = self::getCardsInLocation($player_id, 'revealed');
                    if (self::getAuxiliaryValue() == $player_id) {
                        // Track which card was melded by the launcher so it can be executed later.
                        self::setAuxiliaryValue2($this->innovationGameState->get('id_last_selected'));
                    }
                    if (count($revealed_cards) > 0) {
                        // Remove the chosen player from the list of options.
                        $selectable_players = $this->innovationGameState->getAsArray('player_array');
                        $selected_player = self::getAuxiliaryValue();
                        $selectable_players = array_diff($selectable_players, array(self::playerIdToPlayerIndex($selected_player)));
                        $this->innovationGameState->setFromArray('player_array', $selectable_players);
                        
                        // Repeat for next player
                        $step = $step - 2;
                        self::incrementStep(-2);
                    } else {
                        // "Execute the non-demand effects of your card. Do not share them"
                        self::selfExecute(self::getCardInfo(self::getAuxiliaryValue2()));
                    }
                    break;
                    
                // id 186, Artifacts age 8: Earhart's Lockheed Electra 10E
                case "186N1A":
                    if ($n > 0) {
                        self::setAuxiliaryValue(self::getAuxiliaryValue() + 1);
                    } else {
                        $this->innovationGameState->increment('age_last_selected', -1);
                    }

                    // There are no more values to return
                    if ($this->innovationGameState->get('age_last_selected') == 0) {
                        // "If you return eight cards, you win"
                        if (self::getAuxiliaryValue() == 8) {
                            self::notifyPlayer($player_id, 'log', clienttranslate('${You} returned 8 cards.'), array('You' => 'You'));
                            self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} returned 8 cards.'), array('player_name' => self::renderPlayerName($player_id)));
                            $this->innovationGameState->set('winner_by_dogma', $player_id);
                            self::trace('EOG bubbled from self::stInterInteractionStep Earharts Lockheed Electra 10E');
                            throw new EndOfGame();

                        // "Otherwise"
                        } else {
                            self::incrementStepMax(1);
                        }
                    
                    // Repeat interaction with a lower value than last time
                    } else {
                        $step--; self::incrementStep(-1);
                    }
                    break;
                    
                // id 190, Artifacts age 8: Meiji-Mura Stamp Vending Machine
                case "190N1A":
                    // "Draw and score three cards of the returned card's value"
                    if ($n > 0) {
                        $age_to_score = $this->innovationGameState->get('age_last_selected');
                    } else {
                        $age_to_score = 0;
                    }
                    self::executeDraw($player_id, $age_to_score, 'score');
                    self::executeDraw($player_id, $age_to_score, 'score');
                    self::executeDraw($player_id, $age_to_score, 'score');
                    break;

                // id 191, Artifacts age 8: Plush Beweglich Rod Bear
                case "191N1A":
                    // "Splay up each color with a top card of the chosen value"
                    $age_value = self::getAuxiliaryValue();
                    $top_cards = self::getTopCardsOnBoard($player_id);
                    foreach ($top_cards as $top_card) {
                        if ($top_card['faceup_age'] == $age_value) {
                            self::splayUp($player_id, $player_id, $top_card['color']);
                        }
                    }
                    break;

                // id 192, Artifacts age 8: Time
                case "192C1A":
                    // "If you do, repeat this effect"
                    if ($n > 0) {
                        $step--;
                        self::incrementStep(-1);
                    }
                    break;

                // id 193, Artifacts age 8: Garland's Ruby Slippers
                case "193N1A":
                    // If a card was melded
                    if ($n > 0) {
                        $melded_card = self::getCardInfo($this->innovationGameState->get('id_last_selected'));
                        
                        // "If the melded card has no effects, you win"
                        if ($melded_card['dogma_icon'] == null || $melded_card['type'] == 2) {
                            self::notifyPlayer($player_id, 'log', clienttranslate('${You} melded a card with no effects.'), array('You' => 'You'));
                            self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} melded a card with no effects.'), array('player_name' => self::renderPlayerName($player_id)));
                            $this->innovationGameState->set('winner_by_dogma', $player_id);
                            self::trace('EOG bubbled from self::stPlayerInvolvedTurn Garlands Ruby Slippers');
                            throw new EndOfGame();
                        } else {
                            // "Otherwise, execute the effects of the melded card as if they were on this card. Do not share them"
                            self::fullyExecute($melded_card);
                        }
                    }
                    break;
                    
                // id 196, Artifacts age 9: Luna 3
                case "196N1A":
                    // "Draw and score a card of value equal to the number of cards returned"
                    self::executeDraw($player_id, $n, 'score');
                    break;

                // id 198, Artifacts age 9: Velcro Shoes
                case "198C1A":
                    // "If you do not"
                    if ($n == 0) {
                        self::incrementStepMax(1);
                    }
                    break;

                case "198C1B":
                    // "If you do neither, I win"
                    if ($n == 0) {
                        self::notifyGeneralInfo(clienttranslate('Neither transfer took place.'));
                        $this->innovationGameState->set('winner_by_dogma', $launcher_id);
                        self::trace('EOG bubbled from self::stInterInteractionStep Velcro Shoes');
                        throw new EndOfGame();
                    }
                    break;

                // id 200, Artifacts age 9: Syncom 3
                case "200N1A":
                    // "Draw and reveal five 9s"
                    $revealed_cards = array();
                    $revealed_colors = array();
                    for ($i = 0; $i < 5; $i++) {
                        $card = self::executeDraw($player_id, 9, 'revealed');
                        $revealed_cards[] = $card;
                        $revealed_colors[] = $card['color'];
                    }
                    
                    // "If you revealed all five colors, you win"
                    if (count(array_count_values($revealed_colors)) == 5) {
                        self::notifyPlayer($player_id, 'log', clienttranslate('${You} revealed all 5 colors.'), array('You' => 'You'));
                        self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} revealed all 5 colors.'), array('player_name' => self::renderPlayerName($player_id)));
                        $this->innovationGameState->set('winner_by_dogma', $player_id);
                        self::trace('EOG bubbled from self::stInterInteractionStep Syncom 3');
                        throw new EndOfGame();
                    }

                    // Put the revealed cards in hand
                    foreach ($revealed_cards as $card) {
                        self::transferCardFromTo($card, $player_id, 'hand');
                    }
                    break;                    
                    
                case "204N1B":
                    // "If you have exactly 25 points, you win"
                    if (self::getPlayerScore($player_id) == 25) {
                        self::notifyPlayer($player_id, 'log', clienttranslate('${You} have exactly 25 points.'), array('You' => 'You'));
                        self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} has exactly 25 points.'), array('player_name' => self::renderPlayerName($player_id)));
                        $this->innovationGameState->set('winner_by_dogma', $player_id);
                        self::trace('EOG bubbled from self::stInterInteractionStep Marilyn Diptych');
                        throw new EndOfGame();
                    }
                    break;

                // id 211, Artifacts age 10: Dolly the Sheep
                case "211N1A":
                    // "You may score your bottom yellow card"
                    $choice = self::getAuxiliaryValue();
                    if ($choice == 1) {
                        $bottom_yellow_card = self::getBottomCardOnBoard($player_id, 3);
                        self::scoreCard($bottom_yellow_card, $player_id);
                    }
                    break;

                case "211N1B":
                    // "You may draw and tuck a 1"
                    if (self::getAuxiliaryValue() == 1) {
                        self::executeDrawAndTuck($player_id, 1);
                    }
                    
                    // "If your bottom yellow card is Domestication, you win"
                    $bottom_yellow_card = self::getBottomCardOnBoard($player_id, 3);
                    if ($bottom_yellow_card != null && $bottom_yellow_card['id'] == 10) {
                        self::notifyPlayer($player_id, 'log', clienttranslate('${You} have Domestication as a bottom card.'), array('You' => 'You'));
                        self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} has Domestication as a bottom card.'), array('player_name' => self::renderPlayerName($player_id)));
                        $this->innovationGameState->set('winner_by_dogma', $player_id);
                        self::trace('EOG bubbled from self::stInterInteractionStep Dolly the Sheep');
                        throw new EndOfGame();
                    }
                    break;       

                case "211N1C":
                    // "Then draw a 10"
                    self::executeDraw($player_id, 10);
                    break;

                // id 214, Artifacts age 10: Twister
                case "214C1A":
                    if ($n > 0) {
                        $selectable_colors = self::getAuxiliaryValueAsArray();
                        $selectable_colors = array_diff($selectable_colors, [$this->innovationGameState->get('color_last_selected')]);

                        // Repeat this interaction if there are more cards to meld
                        if (count($selectable_colors) > 0) {
                            self::setAuxiliaryValueFromArray($selectable_colors);
                            $step = 0;
                            self::setStep(0);
                        }
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

                // id 443, age 11: Fusion
                case "443N1A":
                case "443N1C":
                    // "If you do, choose a value one or two lower than the scored card, then repeat this dogma effect using the chosen value."
                    if($n > 0) {
                        $age_last_selected = $this->innovationGameState->get('age_last_selected');
                        $upper_age = $age_last_selected - 1;
                        $lower_age = $age_last_selected - 2;
                        $found_matching_card = false;
                        foreach (self::getTopCardsOnBoard($player_id) as $card) {
                            if ($card['age'] == $upper_age || $card['age'] == $lower_age) {
                                $found_matching_card = true;
                                break;
                            }
                        }
                        if ($found_matching_card > 0) {
                            self::setStep(1);
                            $step = 1;
                            self::setStepMax(3);
                            self::setAuxiliaryArray([$lower_age, $upper_age]);
                        } else {
                            self::notifyPlayer($player_id, 'log', clienttranslate('${You} have no cards of value ${lower_age} or ${upper_age} on your board.'), array('You' => 'You', 'lower_age' => self::getAgeSquare($lower_age), 'upper_age' => self::getAgeSquare($upper_age)));
                            self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} has no cards of value ${lower_age} or ${upper_age} on their board.'), array('player_name' => self::renderPlayerName($player_id), 'lower_age' => self::getAgeSquare($lower_age), 'upper_age' => self::getAgeSquare($upper_age)));                                 
                        }
                    }
                    break;

                // id 444, age 11: Hypersonics
                case "444D1B":
                    if ($n > 0) { // "if you do"
                        // TODO(4E): There might be a Battleship Yamato bug here which could be solved with a faceup_age_last_selected global variable.
                        $returned_age = $this->innovationGameState->get('age_last_selected');

                        $hand_cards = self::getCardsInHand($player_id);
                        $cards_to_return = array();
                        foreach ($hand_cards as $card) {
                            if ($card['age'] <= $returned_age) {
                                $cards_to_return[] = $card['id'];
                            }
                        }

                        $score_cards = self::getCardsInScorePile($player_id);
                        foreach ($score_cards as $card) {
                            if ($card['age'] <= $returned_age) {
                                $cards_to_return[] = $card['id'];
                            }
                        }
                        if (count($cards_to_return) > 0) {
                            self::incrementStepMax(1);
                            self::setAuxiliaryArray($cards_to_return);
                        } else {
                            self::notifyPlayer($player_id, 'log', clienttranslate('${You} have no cards of value ${age} or less in your hand or score pile.'),  
                                array('You' => 'You', 'age' => self::getAgeSquare($returned_age)));
                            self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} has no cards of value ${age} or less in his hand or score pile.'), 
                                array('player_name' => self::renderPlayerName($player_id), 'age' => self::getAgeSquare($returned_age)));
                        }
                    }
                    break;

                // id 446, age 11: Near-Field Comm
                case "446N1A":
                    // "Reveal the highest card in your score pile and execute its non-demand dogma effects. Do not share them."
                    if ($n > 0) {
                        $card = self::getCardInfo($this->innovationGameState->get('id_last_selected'));
                        self::transferCardFromTo($card, $player_id, 'score');
                        self::selfExecute($card);
                    }
                    break;

                // id 448, age 11: Escapism
                case "448N1A":
                    // "junk a card in your hand"
                    if ($n > 0) {
                        $card = self::getCardInfo($this->innovationGameState->get('id_last_selected'));
                        self::junkCard($card);
                        self::setAuxiliaryValue($card['id']);
                        self::incrementStepMax(1);
                    }
                    break;

                case "448N1B":
                    // "Draw three cards of that value. Self-execute the junked card."
                    $card = self::getCardInfo(self::getAuxiliaryValue());
                    self::executeDraw($player_id, $card['age']);
                    self::executeDraw($player_id, $card['age']);
                    self::executeDraw($player_id, $card['age']);
                    self::selfExecute($card);
                    break;
                    
                // id 449, age 11: Whataboutism
                case "449D1A":
                    if ($n > 0) { // "if you do"
                        // "exchange all cards in your score pile with all cards in my score pile!"
                        $launcher_score_cards = self::getCardsInScorePile($launcher_id);
                        $player_score_cards = self::getCardsInScorePile($player_id);
                        foreach ($launcher_score_cards as $card) {
                            self::transferCardFromTo($card, $player_id, 'score');
                        }
                        foreach ($player_score_cards as $card) {
                            self::transferCardFromTo($card, $launcher_id, 'score');
                        }
                    }
                    break;

                // id 487, Unseen age 1: Rumor
                case "487N1A":
                    if ($n > 0) { // "if you do"
                        // "draw a card of value one higher than the card you return."
                        self::executeDraw($player_id, $this->innovationGameState->get('age_last_selected') + 1);
                    }
                    break;
                    
                // id 489, Unseen age 1: Handshake
                case "489D1A":
                    // "Transfer all cards in your hand of those colors to my hand!"
                    $colors = self::getAuxiliaryValueAsArray();
                    foreach (self::getCardsInHand($player_id) as $card) {
                        if ($colors[0] == $card['color'] || $colors[1] == $card['color']) {
                            self::transferCardFromTo($card, $launcher_id, 'hand');
                        }
                    }
                    break;
                    
                // id 497, Unseen age 2: Padlock
                case "497D1A":
                    self::setAuxiliaryValue($n);
                    break;

                case "497N1A":
                    if ($n > 0) { // card scored
                        $selectable_card_ids = array();
                        $age = $this->innovationGameState->get('age_last_selected');
                        foreach (self::getCardsInLocation($player_id, 'hand') as $card) {
                            if ($age != $card['age']) {
                                $selectable_card_ids[] = $card['id'];
                            }
                        }
                        if (count($selectable_card_ids) > 0) {
                            self::setAuxiliaryArray($selectable_card_ids);
                            self::setAuxiliaryValue2($age);
                            self::incrementStepMax(1);
                        }
                    }
                    break;                    

                case "497N1B":
                    if ($n > 0) { // card scored
                        $selectable_card_ids = array();
                        $age = $this->innovationGameState->get('age_last_selected');
                        $age2 = self::getAuxiliaryValue2();
                        foreach (self::getCardsInLocation($player_id, 'hand') as $card) {
                            if ($age != $card['age'] && $age2 != $card['age'] ) {
                                $selectable_card_ids[] = $card['id'];
                            }
                        }
                        if (count($selectable_card_ids) > 0) {
                            self::setAuxiliaryArray($selectable_card_ids);
                            self::incrementStepMax(1);
                        }
                    }
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
                        foreach($top_cards as $card) {
                            for ($age = 0; $age < 12; $age++) {
                                if ($score_cards_by_age[$card['age']] == 0) {
                                    $card_id_array[] = $card['id'];
                                }
                            }
                        }
                        if (count($card_id_array) > 0) {
                            // "repeat this effect."
                            self::setStep(0); $step = 0;
                            self::setAuxiliaryArray($card_id_array);
                        }
                    }
                    break;

                // id 501, Unseen age 2: Exile
                case "501D1A":
                    self::setAuxiliaryValue($n);
                    break;

                case "501D1B":
                    self::setAuxiliaryValue($n + self::getAuxiliaryValue());
                    break;

                // id 504, Unseen age 2: Steganography
                case "504N1A":
                    if ($n > 0) { // "If you do,"
                        self::incrementStepMax(1);
                    }
                    break;

                // id 525, Unseen age 5: Popular Science
                case "525N1A":
                    // "Draw and meld a card of value equal to the value of a top green card anywhere."
                    self::executeDrawAndMeld($player_id, self::getAuxiliaryValue());
                    break;

                // id 526, Unseen age 5: Probability
                case "526N2A":
                    // "If exactly two different icon types appear on the drawn cards, draw and score two 6."
                    if (self::getAuxiliaryValue() == 2) {
                        self::executeDrawAndScore($player_id, 6);
                        self::executeDrawAndScore($player_id, 6);
                    // "If exactly four different icon types appear, draw a 7."
                    } else if (self::getAuxiliaryValue() == 4) {
                        self::executeDraw($player_id, 7);
                    }
                    // "Draw a 6."
                    self::executeDraw($player_id, 6);
                    break;          

                // id 511, Unseen age 3: Freemasons
                case "511N1A":
                    if ($n > 0) { 
                        $card = self::getCardInfo($this->innovationGameState->get('id_last_selected'));
                        if ($card['color'] == 3 || $card['type'] > 0) {
                            self::setAuxiliaryValue(1); // yellow or expansion card!
                        }

                        $color_array = array_diff(self::getAuxiliaryValue2AsArray(), array($this->innovationGameState->get('color_last_selected')));
                        
                        if (count($color_array) > 0) {
                            // more to tuck
                            self::setStep(0); $step = 0; // repeat until all colors are considered
                            self::setAuxiliaryValue2FromArray($color_array);
                        } else {
                            // "If you tuck a yellow card or an expansion card, draw two 3s."
                            if (self::getAuxiliaryValue() == 1) {
                                self::executeDraw($player_id, 3);
                                self::executeDraw($player_id, 3);
                            }                            
                        }
                        
                    } else {
                        // "If you tuck a yellow card or an expansion card, draw two 3s."
                        if (self::getAuxiliaryValue() == 1) {
                            self::executeDraw($player_id, 3);
                            self::executeDraw($player_id, 3);
                        }                            
                    }
                    break;

                // id 513, Unseen age 3: Masquerade
                case "513N1A":
                    if ($n > 0) { // "if you do"
                        self::incrementStepMax(1);
                    }
                    break;

                case "513N1B":
                    if ($n > 0) { 
                        // "If you return a 4, claim the Anonymity achievement."
                        if ($this->innovationGameState->get('age_last_selected') == 4) {
                            self::claimSpecialAchievement($player_id, 597);
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

        // There won't be any nested card state if a player is returning cards after the Search icon or Junk Achievement icon was triggered.
        if ($nested_card_state == null) {
            self::trace('interInteractionStep->digArtifact');
            $this->gamestate->nextState('digArtifact');
            return;
        }

        if ($step == self::getStepMax()) { // The last step has been completed
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
    
    function stPreSelectionMove() {
        $player_id = self::getActivePlayerId();
        $special_type_of_choice = $this->innovationGameState->get('special_type_of_choice');
        $can_pass = $this->innovationGameState->get('can_pass') == 1;

        if ($special_type_of_choice == 0) {
            $selection_size = self::countSelectedCards();
            $cards_chosen_so_far = $this->innovationGameState->get('n');
            $n_min = $this->innovationGameState->get('n_min');
            $n_max = $this->innovationGameState->get('n_max');
            $splay_direction = $this->innovationGameState->get('splay_direction');
            $enable_autoselection = $this->innovationGameState->get('enable_autoselection') == 1;
            $owner_from = $this->innovationGameState->get('owner_from');
            $location_from = self::decodeLocation($this->innovationGameState->get('location_from'));
            $location_to = self::decodeLocation($this->innovationGameState->get('location_to'));
            $bottom_to = $this->innovationGameState->get('bottom_to');
            $colors = $this->innovationGameState->getAsArray('color_array');
            $with_icons = $this->innovationGameState->getAsArray('with_icons');
            $without_icons = $this->innovationGameState->getAsArray('without_icons');
            $with_bonus = $this->innovationGameState->get('with_bonus');
            $without_bonus = $this->innovationGameState->get('without_bonus');
            $card_id_returning_to_unique_supply_pile = $location_to == 'deck' || $location_to == 'revealed,deck' ? self::getSelectedCardIdBelongingToUniqueSupplyPile() : null;
            $card_id_with_unique_color = $location_to == 'board' ? self::getSelectedCardIdWithUniqueColor() : null;

            $nested_card_state = self::getCurrentNestedCardState();

            // There won't be any nested card state if a player is returning cards after the Search icon or Junk Achievement icon was triggered.
            if ($nested_card_state == null) {
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

            // Special automation case for Periodic Table (it's broken into two interactions only because the choice is sometimes complex)
            if ($code == '175N1A' && $selection_size == 2) {
                $card = self::getSelectedCards()[0];
                $this->innovationGameState->set('id_last_selected', $card['id']);
                self::unmarkAsSelected($card['id']);
                self::trace('preSelectionMove->interSelectionMove (Periodic Table automation)');
                $this->gamestate->nextState('interSelectionMove');
                return;
            }

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
                
                if (($splay_direction == -1 && ($can_pass || $n_min <= 0)) && ($selection_will_reveal_hidden_information || ($num_cards_in_location_from > 0 && !$enable_autoselection))) {
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
                self::notifyNoSelectableCards();
                self::trace('preSelectionMove->interInteractionStep (no card)');
                $this->gamestate->nextState('interInteractionStep');
                return;

            // Color must be splayed and there is only one choice
            } else if ($enable_autoselection && !$can_pass && $splay_direction >= 0 && count($colors) === 1) {
                // A card is chosen automatically for the player
                $card = self::getSelectedCards()[0];
                // Simplified version of self::choose()
                $this->innovationGameState->set('id_last_selected', $card['id']);
                self::unmarkAsSelected($card['id']);
                $this->innovationGameState->set('can_pass', 0);
                self::trace('preSelectionMove->interSelectionMove (automated splay selection)');
                $this->gamestate->nextState('interSelectionMove');
                return;
            // All selectable cards must be chosen
            } else if ($enable_autoselection
                    // Make sure choosing these cards won't reveal hidden information (unless all cards in that location need to be chosen anyway)
                    && (!$selection_will_reveal_hidden_information || self::countCardsInLocation($owner_from, $location_from) <= $selection_size)
                    // The player must choose at least all of the selectable cards
                    && (($cards_chosen_so_far == 0 && !$can_pass && $selection_size <= $n_min) || ($cards_chosen_so_far > 0 && $n_min >= $selection_size))
                    // If there's more than one selectable card, only automate the choices if the order does not matter
                    && ($selection_size == 1 || ($location_to != 'board' && $location_to != 'deck' && $location_to != 'revealed,deck' && $location_to != 'safe'))) {
                // A card is chosen automatically for the player
                $card = self::getSelectedCards()[0];
                // Simplified version of self::choose()
                $this->innovationGameState->set('id_last_selected', $card['id']);
                self::unmarkAsSelected($card['id']);
                $this->innovationGameState->set('can_pass', 0);
                self::trace('preSelectionMove->interSelectionMove (automated card selection)');
                $this->gamestate->nextState('interSelectionMove');
                return;
            // Try to return cards to the deck where the order doesn't matter
            } else if ($enable_autoselection
                    // Make sure choosing these cards won't reveal hidden information
                    && (!$selection_will_reveal_hidden_information)
                    // The player must choose at least all of the selectable cards
                    && (($cards_chosen_so_far == 0 && !$can_pass && $selection_size <= $n_min) || ($cards_chosen_so_far > 0 && $n_min >= $selection_size))
                    // There must be at least one card which goes to a unique supply pile
                    && $card_id_returning_to_unique_supply_pile != null
                    // The cards are coming from anywhere but the board (unless we are playing with the 4th edition because relevant special achievement checks were moved to end of the action)
                    && ($location_from != 'board' || $this->innovationGameState->usingFourthEditionRules())) {
                $this->innovationGameState->set('id_last_selected', $card_id_returning_to_unique_supply_pile);
                self::unmarkAsSelected($card_id_returning_to_unique_supply_pile);
                $this->innovationGameState->set('can_pass', 0);
                self::trace('preSelectionMove->interSelectionMove (automated card selection)');
                $this->gamestate->nextState('interSelectionMove');
                return;
            // Try to tuck cards where the order doesn't matter
            } else if ($enable_autoselection
                    // Make sure choosing these cards won't reveal hidden information
                    && (!$selection_will_reveal_hidden_information)
                    // The player must choose at least all of the selectable cards
                    && (($cards_chosen_so_far == 0 && !$can_pass && $selection_size <= $n_min) || ($cards_chosen_so_far > 0 && $n_min >= $selection_size))
                    // The cards are being tucked (unless we are playing with the 4th edition because relevant special achievement checks were moved to end of the action)
                    && ($bottom_to > 0 || $this->innovationGameState->usingFourthEditionRules())
                    // There must be at least one card which has a unique color
                    && $card_id_with_unique_color != null) {
                $this->innovationGameState->set('id_last_selected', $card_id_with_unique_color);
                self::unmarkAsSelected($card_id_with_unique_color);
                $this->innovationGameState->set('can_pass', 0);
                self::trace('preSelectionMove->interSelectionMove (automated card selection)');
                $this->gamestate->nextState('interSelectionMove');
                return;
            // There are selectable cards, but not enough to fulfill the requirement ("May effects only")
            } else if ($n_min < 800 && $selection_size < $n_min) {
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
        }

        // Let the player make his choice
        self::trace('preSelectionMove->selectionMove');
        $this->gamestate->nextState('selectionMove');
        self::giveExtraTime($player_id);
    }

    function getSelectedCardIdBelongingToUniqueSupplyPile() {
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

    function getSelectedCardIdWithUniqueColor() {
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
    
    function stInterSelectionMove() {
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
            $location_to = self::decodeLocation($this->innovationGameState->get('location_to'));
            $bottom_to = $this->innovationGameState->get('bottom_to');
            $score_keyword = $this->innovationGameState->get('score_keyword') == 1;
            $meld_keyword = $this->innovationGameState->get('meld_keyword') == 1;
            $achieve_keyword = $this->innovationGameState->get('achieve_keyword') == 1;
            $draw_keyword = $this->innovationGameState->get('draw_keyword') == 1;
            $safeguard_keyword = $this->innovationGameState->get('safeguard_keyword') == 1;
            $return_keyword = $this->innovationGameState->get('return_keyword') == 1;
            $foreshadow_keyword = $this->innovationGameState->get('foreshadow_keyword') == 1;
            
            $splay_direction = $this->innovationGameState->get('splay_direction'); // -1 if that was not a choice for splay
        }
        else { // The player had to make a special choice
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

        // There won't be any nested card state if a player is returning cards after the Search icon or Junk Achievement icon was triggered.
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

            switch($code) {
            // The first number is the id of the card
            // D1 means the first (and single) I demand effect
            // C1 means the first (and single) I compel effect
            // N1 means the first non-demand effect
            // N2 means the second non-demand effect
            // N3 means the third non-demand effect
            // E1 means the first (and single) echo effect
            
            // The letter indicates the step : A for the first one, B for the second
            
            // Default behaviour: make the transfer or the splay as stated in B
            
            // id 18, age 2: Road building
            case "18N1B":
                // $choice is the chosen player id
                $top_red_card = self::getTopCardOnBoard($player_id, 1);
                self::transferCardFromTo($top_red_card, $choice, 'board'); // "Transfer your top red card to another's player's board"
                $top_green_card = self::getTopCardOnBoard($choice, 2);
                if ($top_green_card !== null) {
                    self::transferCardFromTo($top_green_card, $player_id, 'board'); // "Transfer that player's top green card to your board"
                }
                else {
                    self::notifyPlayer($choice, 'log', clienttranslate('${You} have no top green card on your board.'), array('You' => 'You'));
                    self::notifyAllPlayersBut($choice, 'log', clienttranslate('${player_name} has no top green card on his board.'), array('player_name' => self::renderPlayerName($choice)));
                }
                break;
                
            // id 19, age 2: Currency
            case "19N1A":
                $different_values_selected_so_far = self::getAuxiliaryValueAsArray();
                if (!in_array($card['age'], $different_values_selected_so_far)) { // The player choose to return a card of a new value
                    $different_values_selected_so_far[] = $card['age'];
                    self::setAuxiliaryValueFromArray($different_values_selected_so_far);
                }
                self::returnCard($card);
                break;
            
            // id 21, age 2: Canal building         
            case "21N1A":
                if ($choice == 0) { // No exchange
                    if ($this->innovationGameState->usingFourthEditionRules()) {
                        self::junkBaseDeck(3);
                    } else {
                        self::notifyPlayer($player_id, 'log', clienttranslate('${You} decide not to exchange.'), array('You' => 'You'));
                        self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} decides not to exchange.'), array('player_name' => self::renderPlayerName($player_id)));
                    }
                }
                else { // "Exchange all the highest cards in your hand with all the highest cards in your score pile"
                    self::notifyPlayer($player_id, 'log', clienttranslate('${You} decide to exchange.'), array('You' => 'You'));
                    self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} decides to exchange.'), array('player_name' => self::renderPlayerName($player_id)));
                    
                    // Get highest cards in hand and highest cards in score
                    $ids_of_highest_cards_in_hand = self::getIdsOfHighestCardsInLocation($player_id, 'hand');
                    $ids_of_highest_cards_in_score = self::getIdsOfHighestCardsInLocation($player_id, 'score');

                    // Make the transfers
                    foreach($ids_of_highest_cards_in_score as $id) {
                        $card = self::getCardInfo($id);
                        self::transferCardFromTo($card, $player_id, 'hand');
                    }
                    foreach($ids_of_highest_cards_in_hand as $id) {
                        $card = self::getCardInfo($id);
                        self::transferCardFromTo($card, $player_id, 'score'); // Note: this has no score keyword 
                    }
                }
                break;
            
            // id 28, age 3: Optics        
            case "28N1A":
                // Nothing to do but to go to the next step
                break;
                
            // id 32, age 3: Medicine
            case "32D1A":
                // Delay the transfer: this way the launcher can not choose the card he would just receive
                self::setAuxiliaryValue($card['id']);
                break;
            
            // id 50, age 5: Measurement
            case "50N1B":
                // $choice is a color
                self::notifyPlayer($player_id, 'log', clienttranslate('${You} choose ${color}.'), array('i18n' => array('color'), 'You' => 'You', 'color' => Colors::render($choice)));
                self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} chooses ${color}.'), array('i18n' => array('color'), 'player_name' => self::renderPlayerName($player_id), 'color' => Colors::render($choice)));
                self::splayRight($player_id, $player_id, $choice); // "Splay that color of your cards right"
                $number_of_cards = self::countCardsInLocationKeyedByColor($player_id, 'board')[$choice];
                if ($number_of_cards == 1) {
                    self::notifyPlayer($player_id, 'log', clienttranslate('${You} have ${n} ${colored} card.'), array('i18n' => array('n', 'colored'), 'You' => 'You', 'n' => self::renderNumber($number_of_cards), 'colored' => Colors::render($choice)));
                    self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} has  ${n} ${colored} card.'), array('i18n' => array('n', 'colored'), 'player_name' => self::renderPlayerName($player_id), 'n' => self::renderNumber($number_of_cards), 'colored' => Colors::render($choice)));
                } else {
                    self::notifyPlayer($player_id, 'log', clienttranslate('${You} have ${n} ${colored_cards}.'), array('i18n' => array('n', 'colored_cards'), 'You' => 'You', 'n' => self::renderNumber($number_of_cards), 'colored_cards' => self::renderColorCards($choice)));
                    self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} has  ${n} ${colored_cards}.'), array('i18n' => array('n', 'colored_cards'), 'player_name' => self::renderPlayerName($player_id), 'n' => self::renderNumber($number_of_cards), 'colored_cards' => self::renderColorCards($choice)));
                }
                self::executeDraw($player_id, $number_of_cards); // "Draw a card of value equal to the number of cards of that color on your board"
                break;
                
            // id 61, age 6: Canning
            case "61N1A":
                // $choice is yes or no
                if ($choice == 0) { // No tuck
                    self::notifyPlayer($player_id, 'log', clienttranslate('${You} decide not to tuck.'), array('You' => 'You'));
                    self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} decides not to tuck.'), array('player_name' => self::renderPlayerName($player_id)));
                } else { // Draw and tuck
                    self::notifyPlayer($player_id, 'log', clienttranslate('${You} decide to tuck.'), array('You' => 'You'));
                    self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} decides to tuck.'), array('player_name' => self::renderPlayerName($player_id)));
                    
                    self::executeDrawAndTuck($player_id, 6); // "Draw and tuck a 6"
                    
                    // Make the transfers
                    for ($color = 0; $color < 5; $color++) {
                        $card = self::getTopCardOnBoard($player_id, $color);
                        if ($card !== null && !self::hasRessource($card, 5)) {
                            self::scoreCard($card, $player_id); // "Score all your top cards without a factory"
                        }
                    }
                }
                break;
                
            // id 66, age 7: Publications          
            case "66N1A_3E":
                // The rearrangement was already done.
                break;
            
            case "66N2A_4E":
                // "You may junk an available special achievement or make a junked special achievement available"
                $achievement = self::getCardInfo($choice);
                if ($achievement['location'] == 'junk') {
                    self::transferCardFromTo($achievement, 0, 'achievements');
                } else {
                    self::transferCardFromTo($achievement, 0, 'junk');
                }
                break;
            
            // id 67, age 7: Bicycle         
            case "69N1A":
                // $choice is yes or no
                if ($choice == 0) { // No exchange
                    self::notifyPlayer($player_id, 'log', clienttranslate('${You} decide not to exchange.'), array('You' => 'You'));
                    self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} decides not to exchange.'), array('player_name' => self::renderPlayerName($player_id)));
                }
                else { // "Exchange all the cards in your hand with all the cards in your score pile"
                    self::notifyPlayer($player_id, 'log', clienttranslate('${You} decide to exchange.'), array('You' => 'You'));
                    self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} decides to exchange.'), array('player_name' => self::renderPlayerName($player_id)));
                    
                    // Get all in hand and all cards in score
                    $ids_of_cards_in_hand = self::getIdsOfCardsInLocation($player_id, 'hand');
                    $ids_of_cards_in_score = self::getIdsOfCardsInLocation($player_id, 'score');

                    // Make the transfers
                    foreach($ids_of_cards_in_score as $id) {
                        $card = self::getCardInfo($id);
                        self::transferCardFromTo($card, $player_id, 'hand');
                    }
                    foreach($ids_of_cards_in_hand as $id) {
                        $card = self::getCardInfo($id);
                        self::transferCardFromTo($card, $player_id, 'score'); // Nota: this has no score keyword 
                    }
                }
                break;
                    
            // id 73, age 7: Lighting
            case "73N1A":
                $different_values_selected_so_far = self::getAuxiliaryValueAsArray();
                if (!in_array($card['age'], $different_values_selected_so_far)) { // The player choose to tuck a card of a new value
                    $different_values_selected_so_far[] = $card['age'];
                    self::setAuxiliaryValueFromArray($different_values_selected_so_far);
                }
                self::tuckCard($card, $owner_to);
                break;
            
            // id 80, age 8, Mass media
            case "80N1B":
                self::notifyPlayer($player_id, 'log', clienttranslate('${You} choose the value ${age}.'), array('You' => 'You', 'age' => self::getAgeSquare($choice)));
                self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} chooses the value ${age}.'), array('player_name' => self::renderPlayerName($player_id), 'age' => self::getAgeSquare($choice)));
                self::setAuxiliaryValue($choice);
                break;
                
            // id 81, age 8: Antibiotics
            case "81N1A":
                $different_values_selected_so_far = self::getAuxiliaryValueAsArray();
                if (!in_array($card['age'], $different_values_selected_so_far)) { // The player choose to return a card of a new value
                    $different_values_selected_so_far[] = $card['age'];
                    self::setAuxiliaryValueFromArray($different_values_selected_so_far);
                }
                self::returnCard($card);
                break;
            
            // id 83, age 8: Empiricism     
            case "83N1A":
                // $choice was two colors
                $colors = Arrays::getValueAsArray($choice);
                self::notifyPlayer($player_id, 'log', clienttranslate('${You} choose ${color_1} and ${color_2}.'), array('i18n' => array('color_1', 'color_2'), 'You' => 'You', 'color_1' => Colors::render($colors[0]), 'color_2' => Colors::render($colors[1])));
                self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} chooses ${color_1} and ${color_2}.'), array('i18n' => array('color_1', 'color_2'), 'player_name' => self::renderPlayerName($player_id), 'color_1' => Colors::render($colors[0]), 'color_2' => Colors::render($colors[1])));
                
                $card = self::executeDraw($player_id, 9, 'revealed'); // "Draw and reveal a 9"
                if ($card['color'] <> $colors[0] && $card['color'] <> $colors[1]) {
                    self::notifyGeneralInfo(clienttranslate('It does not match any of the chosen colors.'));
                    self::transferCardFromTo($card, $player_id, 'hand'); // (Implicit) "Keep it in your hand"
                    if ($this->innovationGameState->usingFourthEditionRules()) {
                        self::unsplay($player_id, $player_id, $card['color']);
                    }
                }
                else { // "If it is either of the colors you chose"
                    self::notifyGeneralInfo(clienttranslate('It matches a chosen color: ${color}.'), array('i18n' => array('color'), 'color' => Colors::render($card['color'])));
                    self::meldCard($card, $player_id); // "Meld it"
                    self::setAuxiliaryValue($card['color']); // Flag the sucessful colors
                    self::incrementStepMax(1);
                }
                break;
            
            // id 84, age 8: Socialism     
            case "84N1A_3E":
                if ($card['color'] == 4 /* purple*/) { // A purple card has been tucked
                    self::setAuxiliaryValue(1); // Flag that
                }
                self::tuckCard($card, $owner_to);
                break;

            // id 91, age 9: Ecology   
            case "91N2A":
                if ($choice == 1) {
                    self::junkBaseDeck(10);
                }
                break;

            // id 92, age 9: Suburbia
            case "92N2A":
                if ($choice == 1) {
                    self::junkBaseDeck(9);
                }
                break;
                
            // id 100, age 10: Self service
            case "100N1A": // 3rd edition and earlier
            case "100N2A": // 4th edition
                self::selfExecute($card); // The player chose this card for execution
                break;
            
            // id 102, age 10: Stem cells 
            case "102N1A":
                // $choice is yes or no
                if ($choice == 0) { // No scoring
                    self::notifyPlayer($player_id, 'log', clienttranslate('${You} decide not to score the cards in your hand.'), array('You' => 'You'));
                    self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} decides not to score the cards in his hand.'), array('player_name' => self::renderPlayerName($player_id)));
                }
                else { // "Score all cards from your hand"
                    self::notifyPlayer($player_id, 'log', clienttranslate('${You} decide to score the cards in your hand.'), array('You' => 'You'));
                    self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} decides to score the cards in his hand.'), array('player_name' => self::renderPlayerName($player_id)));
                    
                    // Get all cards in hand
                    $ids_of_cards_in_hand = self::getIdsOfCardsInLocation($player_id, 'hand');

                    // Make the transfers
                    foreach($ids_of_cards_in_hand as $id) {
                        $card = self::getCardInfo($id);
                        self::scoreCard($card, $player_id);
                    }
                }                
                break;

            // id 147, Artifacts age 4: East India Company Charter
            case "147N1A":
                self::notifyPlayer($player_id, 'log', clienttranslate('${You} choose the value ${age}.'), array('You' => 'You', 'age' => self::getAgeSquare($choice)));
                self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} chooses the value ${age}.'), array('player_name' => self::renderPlayerName($player_id), 'age' => self::getAgeSquare($choice)));
                self::setAuxiliaryValue($choice);
                break;

            // id 152, Artifacts age 5: Mona Lisa
            case "152N1A":
                self::notifyPlayer($player_id, 'log', clienttranslate('${You} choose ${color}.'), array('i18n' => array('color'), 'You' => 'You', 'color' => Colors::render($choice)));
                self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} chooses ${color}.'), array('i18n' => array('color'), 'player_name' => self::renderPlayerName($player_id), 'color' => Colors::render($choice)));
                self::setAuxiliaryValue2($choice);
                break;

            case "152N1B":
                self::notifyPlayer($player_id, 'log', clienttranslate('${You} choose the number ${n}.'), array('You' => 'You', 'n' => $choice));
                self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} chooses the number ${n}.'), array('player_name' => self::renderPlayerName($player_id), 'n' => $choice));
                self::setAuxiliaryValue($choice);
                break;

            // id 157, Artifacts age 5: Bill of Rights
            case "157C1A":
                self::notifyPlayer($player_id, 'log', clienttranslate('${You} choose ${color}.'), array('i18n' => array('color'), 'You' => 'You', 'color' => Colors::render($choice)));
                self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} chooses ${color}.'), array('i18n' => array('color'), 'player_name' => self::renderPlayerName($player_id), 'color' => Colors::render($choice)));
                self::setAuxiliaryValue($choice);
                break;
            
            // id 158, Artifacts age 5: Ship of the Line Sussex
            case "158N1B":
                self::notifyPlayer($player_id, 'log', clienttranslate('${You} choose ${color}.'), array('i18n' => array('color'), 'You' => 'You', 'color' => Colors::render($choice)));
                self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} chooses ${color}.'), array('i18n' => array('color'), 'player_name' => self::renderPlayerName($player_id), 'color' => Colors::render($choice)));
                self::setAuxiliaryValue($choice);
                break;
            
            // id 162, Artifacts age 5: The Daily Courant
            case "162N1A":
                self::notifyPlayer($player_id, 'log', clienttranslate('${You} choose the value ${age}.'), array('You' => 'You', 'age' => self::getAgeSquare($choice)));
                self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} chooses the value ${age}.'), array('player_name' => self::renderPlayerName($player_id), 'age' => self::getAgeSquare($choice)));
                self::setAuxiliaryValue($choice);
                break;
            
            // id 170, Artifacts age 6: Buttonwood Agreement
            case "170N1A":
                $colors = Arrays::getValueAsArray($choice);
                self::notifyPlayer($player_id, 'log', clienttranslate('${You} choose ${color_1}, ${color_2}, and ${color_3}.'), array(
                    'i18n' => array('color_1', 'color_2', 'color_3'),
                    'You' => 'You',
                    'color_1' => Colors::render($colors[0]),
                    'color_2' => Colors::render($colors[1]),
                    'color_3' => Colors::render($colors[2]))
                );
                self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} chooses ${color_1}, ${color_2}, and ${color_3}.'), array(
                    'i18n' => array('color_1', 'color_2', 'color_3'),
                    'player_name' => self::renderPlayerName($player_id),
                    'color_1' => Colors::render($colors[0]),
                    'color_2' => Colors::render($colors[1]),
                    'color_3' => Colors::render($colors[2]))
                );
                
                // "Draw and reveal a 8"
                $card = self::executeDraw($player_id, 8, 'revealed');
                // "If the drawn card is one of the chosen colors, score it and splay up that color"
                if ($card['color'] == $colors[0] || $card['color'] == $colors[1] || $card['color'] == $colors[2]) {
                    self::notifyGeneralInfo(clienttranslate('It matches a chosen color: ${color}.'), array('i18n' => array('color'), 'color' => Colors::render($card['color'])));
                    self::scoreCard($card, $player_id);
                    self::splayUp($player_id, $player_id, $card['color']);

                // "Otherwise"
                } else {
                    self::notifyGeneralInfo(clienttranslate('It does not match any of the chosen colors.'));
                    self::transferCardFromTo($card, $player_id, 'hand');
                    self::setAuxiliaryValue($card['color']);
                    self::incrementStepMax(1);
                }
                break;

            // id 173, Artifacts age 6: Moonlight Sonata
            case "173N1A":
                self::notifyPlayer($player_id, 'log', clienttranslate('${You} choose ${color}.'), array('i18n' => array('color'), 'You' => 'You', 'color' => Colors::render($choice)));
                self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} chooses ${color}.'), array('i18n' => array('color'), 'player_name' => self::renderPlayerName($player_id), 'color' => Colors::render($choice)));
                self::setAuxiliaryValue($choice);
                break;

            // id 174, Artifacts age 6: Marcha Real
            case "174N1A":
                if (self::getAuxiliaryValue() < 0) {
                    self::setAuxiliaryValue($card['id']);
                } else {
                    self::setAuxiliaryValue2($card['id']);
                }
                $card = self::transferCardFromTo($card, $owner_to, 'revealed');
                self::returnCard($card);
                break;
                
            // id 179, Artifacts age 7: International Prototype Metre Bar
            case "179N1A":
                self::notifyPlayer($player_id, 'log', clienttranslate('${You} choose the value ${age}.'), array('You' => 'You', 'age' => self::getAgeSquare($choice)));
                self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} chooses the value ${age}.'), array('player_name' => self::renderPlayerName($player_id), 'age' => self::getAgeSquare($choice)));
                self::setAuxiliaryValue($choice);
                break;

            // id 184, Artifacts age 7: The Communist Manifesto
            case "184N1A":
                // NOTE: It doesn't add any value if we log which player was chosen, since it will be obvious which player
                // is chosen when the card is transferred to them.
                self::setAuxiliaryValue($choice);
                break;
                
            // id 191, Artifacts age 8: Plush Beweglich Rod Bear
            case "191N1A":
                self::notifyPlayer($player_id, 'log', clienttranslate('${You} choose the value ${age}.'), array('You' => 'You', 'age' => self::getAgeSquare($choice)));
                self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} chooses the value ${age}.'), array('player_name' => self::renderPlayerName($player_id), 'age' => self::getAgeSquare($choice)));
                self::setAuxiliaryValue($choice);
                break;

            // id 211, Artifacts age 10: Dolly the Sheep
            case "211N1A":
                if ($choice == 0) {
                    self::notifyPlayer($player_id, 'log', clienttranslate('${You} choose not to score your bottom yellow card.'), array('You' => 'You'));
                    self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} chooses not to score his bottom yellow card.'), array('player_name' => self::renderPlayerName($player_id)));
                }
                self::setAuxiliaryValue($choice);
                break;

            case "211N1B":
                $age_to_draw_in = self::getAgeToDrawIn($player_id, 1);
                if ($choice == 0) {
                    self::notifyPlayer($player_id, 'log', clienttranslate('${You} choose not to draw and tuck.'), array('You' => 'You'));
                    self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} chooses not to draw and tuck.'), array('player_name' => self::renderPlayerName($player_id)));
                } else {
                    self::notifyPlayer($player_id, 'log', clienttranslate('${You} choose to draw and tuck.'), array('You' => 'You'));
                    self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} chooses to draw and tuck.'), array('player_name' => self::renderPlayerName($player_id)));
                }
                self::setAuxiliaryValue($choice);
                break;

            case "346N1A":
                self::notifyPlayer($player_id, 'log', clienttranslate('${You} choose the value ${age}.'), array('You' => 'You', 'age' => self::getAgeSquare($choice)));
                self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} chooses the value ${age}.'), array('player_name' => self::renderPlayerName($player_id), 'age' => self::getAgeSquare($choice)));
                self::setAuxiliaryValue($choice);
                break;
                
            // id 443, age 11: Fusion
            case "443N1B":
                self::notifyPlayer($player_id, 'log', clienttranslate('${You} choose the value ${age}.'), array('You' => 'You', 'age' => self::getAgeSquare($choice)));
                self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} chooses the value ${age}.'), array('player_name' => self::renderPlayerName($player_id), 'age' => self::getAgeSquare($choice)));
                self::setAuxiliaryValue($choice);
                break;

            // id 489, Unseen age 1: Handshake     
            case "489D1A":
                // $choice was two colors
                $colors = Arrays::getValueAsArray($choice);
                self::notifyPlayer($player_id, 'log', clienttranslate('${You} choose ${color_1} and ${color_2}.'), array('i18n' => array('color_1', 'color_2'), 'You' => 'You', 'color_1' => Colors::render($colors[0]), 'color_2' => Colors::render($colors[1])));
                self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} chooses ${color_1} and ${color_2}.'), array('i18n' => array('color_1', 'color_2'), 'player_name' => self::renderPlayerName($player_id), 'color_1' => Colors::render($colors[0]), 'color_2' => Colors::render($colors[1])));
                self::setAuxiliaryValueFromArray($colors);
                break;

            // id 499, Unseen age 2: Cipher
            case "499N1A":
                $max_value_selected_so_far = self::getAuxiliaryValue();
                if ($card['age'] > $max_value_selected_so_far) {
                    self::setAuxiliaryValue($card['age']);
                }
                self::returnCard($card);
                break;
            
            // id 525, Unseen age 5: Popular Science
            case "525N1A":
                self::notifyPlayer($player_id, 'log', clienttranslate('${You} choose the value ${age}.'), array('You' => 'You', 'age' => self::getAgeSquare($choice)));
                self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} chooses the value ${age}.'), array('player_name' => self::renderPlayerName($player_id), 'age' => self::getAgeSquare($choice)));
                self::setAuxiliaryValue($choice);
                break;

            default:
                if ($special_type_of_choice == 0) {
                    if ($splay_direction == -1) {

                        if ($code !== null) {
                            $this->innovationGameState->set("age_last_selected", $card['age']);
                            $this->innovationGameState->set("color_last_selected", $card['color']);
                            $this->innovationGameState->set("owner_last_selected", $card['owner']);
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

                        if ($code !== null && self::isInSeparateFile($card_id) && self::getCardInstance($card_id, $executionState)->executeCardTransfer(self::getCardInfo($selected_card_id))) {
                            // Do nothing since the card transfer was overridden
                        } else if ($location_to == 'revealed,hand') {
                            $card = self::transferCardFromTo($card, $owner_to, 'revealed');
                            self::transferCardFromTo($card, $owner_to, 'hand');
                        } else if ($location_to == 'revealed,deck') {
                            $card = self::transferCardFromTo($card, $owner_to, 'revealed');
                            self::returnCard($card);
                        } else if ($location_to == 'revealed,score') {
                            $card = self::transferCardFromTo($card, $owner_to, 'revealed');
                            self::scoreCard($card, $owner_to);
                        } else if ($location_to == 'junk,safe') {
                            $card = self::junkCard($card);
                            self::safeguardCard($card, $owner_to);
                        } else {
                            // TODO(LATER): Figure out if 'bottom_from' should be included here too.
                            self::transferCardFromTo($card, $owner_to, $location_to,
                                [
                                    'bottom_to' => $bottom_to,
                                    'score_keyword' => $score_keyword,
                                    'meld_keyword' => $meld_keyword,
                                    'achieve_keyword' => $achieve_keyword,
                                    'draw_keyword' => $draw_keyword,
                                    'safeguard_keyword' => $safeguard_keyword,
                                    'return_keyword' => $return_keyword,
                                    'foreshadow_keyword' => $foreshadow_keyword,
                                ]
                            );
                        }
                        if ($code !== null && self::isInSeparateFile($card_id)) {
                            self::getCardInstance($card_id, $executionState)->handleCardChoice(self::getCardInfo($selected_card_id));
                            self::setStepMax($executionState->getMaxSteps());
                            self::setStep($executionState->getNextStep() - 1);
                        }
                    }
                    else {
                        // Do the splay as stated in B
                        $this->innovationGameState->set("color_last_selected", $card['color']);
                        self::splay($player_id, $card['owner'], $card['color'], $splay_direction, /*force_unsplay=*/ $splay_direction == 0);
                    }
                } else if (!self::isInSeparateFile($card_id)) {
                    throw new BgaVisibleSystemException(self::format(self::_("Unhandled case in {function}: '{code}'"), array('function' => "stInterSelectionMove()", 'code' => $code)));
                }
                break;
            }
        }
        catch (EndOfGame $e) {
            // End of the game: the exception has reached the highest level of code
            self::trace('EOG bubbled from self::stInterSelectionMove');
            self::trace('interSelectionMove->justBeforeGameEnd');
            $this->gamestate->nextState('justBeforeGameEnd');
            return;
        }
        
        if ($special_type_of_choice == 0) {
            // Mark extra information about this chosen card
            // TODO(LATER): Remove this once it becomes redundant with the same 3 lines above (once all cards are in separate files)
            $this->innovationGameState->set("age_last_selected", $card['age']);
            $this->innovationGameState->set("color_last_selected", $card['color']);
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
        // Check if another selection is to be done
        if ($special_type_of_choice != 0 || $this->innovationGameState->get('n_max') == 0) { // No more choice can be made
            // Unset the selection
            self::deselectAllCards();
            // End of this interaction step
            self::trace('interSelectionMove->interInteractionStep');
            $this->gamestate->nextState('interInteractionStep');
            return;
        }
        // New selection move
        $this->innovationGameState->set('can_pass', 0); // Passing is no longer possible (stopping will be if n_min == 0)
        self::trace('interSelectionMove->preSelectionMove');
        $this->gamestate->nextState('preSelectionMove');        
    }
    
    function stJustBeforeGameEnd() {
        switch($this->innovationGameState->get('game_end_type')) {
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
    function zombieTurn($state, $active_player) {
        throw new feException( "Zombie mode not supported at this moment" );
    }
    
    function isZombie($player_id) {
        return self::getUniqueValueFromDB(self::format("
            SELECT player_zombie FROM player WHERE player_id={player_id}
        ", array('player_id' => $player_id)));
    }
}
