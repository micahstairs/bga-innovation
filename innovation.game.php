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

/* Exception to be called when the game must end */
class EndOfGame extends Exception {}

class Innovation extends Table
{
    function __construct()
    {
        // Your global variables labels:
        //  Here, you can assign labels to global variables you are using for this game.
        //  You can use any number of global variables with IDs between 10 and 99.
        //  If your game has options (variants), you also have to associate here a label to
        //  the corresponding ID in gameoptions.inc.php.
        // Note: afterwards, you can get/set the global variables with getGameStateValue/setGameStateInitialValue/setGameStateValue
        parent::__construct();self::initGameStateLabels(array(
            'number_of_achievements_needed_to_win' => 10,
            'turn0' => 11,
            'first_player_with_only_one_action' => 12,
            'second_player_with_only_one_action' => 13,
            'has_second_action' => 14,
            'game_end_type' => 15,
            'player_who_could_not_draw' => 16,
            'winner_by_dogma' => 17,
            'active_player' => 18,
            'current_player_under_dogma_effect' => 19, // Deprecated
            'dogma_card_id' => 20,                     // Deprecated
            'current_effect_type' => 21,               // Deprecated
            'current_effect_number' => 22,             // Deprecated
            'sharing_bonus' => 23,
            'step' => 24,     // Deprecated
            'step_max' => 25, // Deprecated
            'special_type_of_choice' => 26,
            'choice' => 27,
            'can_pass' => 28,
            'n_min' => 29,
            'n_max' => 30,
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
            'with_icon' => 41,
            'without_icon' => 42,
            'not_id' => 43,
            'n' => 44,
            'id_last_selected' => 45,
            'age_last_selected' => 46,
            'color_last_selected' => 47,
            'score_keyword' => 48,
            'auxiliary_value' => 49,                // Deprecated
            'nested_id_1' => 50,                    // Deprecated
            'nested_current_effect_number_1' => 51, // Deprecated
            'nested_id_2' => 52,                    // Deprecated
            'nested_current_effect_number_2' => 53, // Deprecated
            'nested_id_3' => 54,                    // Deprecated
            'nested_current_effect_number_3' => 55, // Deprecated
            'nested_id_4' => 56,                    // Deprecated
            'nested_current_effect_number_4' => 57, // Deprecated
            'nested_id_5' => 58,                    // Deprecated
            'nested_current_effect_number_5' => 59, // Deprecated
            'nested_id_6' => 60,                    // Deprecated
            'nested_current_effect_number_6' => 61, // Deprecated
            'nested_id_7' => 62,                    // Deprecated
            'nested_current_effect_number_7' => 63, // Deprecated
            'nested_id_8' => 64,                    // Deprecated
            'nested_current_effect_number_8' => 65, // Deprecated
            'nested_id_9' => 66,                    // Deprecated
            'nested_current_effect_number_9' => 67, // Deprecated 
            'card_id_1' => 69,
            'card_id_2' => 70,
            'card_id_3' => 71,
            'require_achievement_eligibility' => 72,
            'has_demand_effect' => 73,
            'has_splay_direction' => 74,
            'owner_last_selected' => 75,
            'type_array' => 76,
            'age_array' => 77,
            'player_array' => 78,
            'icon_hash_1' => 79,
            'icon_hash_2' => 80,
            'icon_hash_3' => 81,
            'icon_hash_4' => 82,
            'icon_hash_5' => 83,
            'enable_autoselection' => 84,
            'include_relics' => 85,
            
            'relic_id' => 95, // ID of the relic which may be seized
            'current_action_number' => 96, // -1 = none, 0 = free action, 1 = first action, 2 = second action
            'current_nesting_index' => 97, // 0 refers to the originally executed card, 1 refers to a card exexcuted by that initial card, etc.
            'release_version' => 98, // Used to help release new versions of the game without breaking existing games (undefined or 0 represents all base game only releases, 1 represents the Artifact expansion release)
            'debug_mode' => 99, // 0 for disabled, 1 for enabled
            
            'game_type' => 100, // 1 for normal game, 2 for team game
            'game_rules' => 101, // 1 for last edition, 2 for first edition
            'artifacts_mode' => 102, // 1 for "Disabled", 2 for "Enabled without Relics", 3 for "Enabled with Relics"
            'extra_achievement_to_win' => 110 // 1 for "Disabled", 2 for "Enabled"
        ));
    }
    
    protected function getGameName()
    {
        return "innovation";
    }

    function upgradeTableDb($from_version) {
        if (is_null(self::getUniqueValueFromDB("SHOW COLUMNS FROM `card` LIKE 'type'"))) {
            self::applyDbUpgradeToAllDB("ALTER TABLE DBPREFIX_card ADD `type` TINYINT UNSIGNED NOT NULL DEFAULT '0';"); 
        }
        if (is_null(self::getUniqueValueFromDB("SHOW COLUMNS FROM `card_with_top_card_indication` LIKE 'type'"))) {
            self::applyDbUpgradeToAllDB("ALTER TABLE DBPREFIX_card_with_top_card_indication ADD `type` TINYINT UNSIGNED NOT NULL DEFAULT '0';"); 
        }
        if (is_null(self::getUniqueValueFromDB("SHOW COLUMNS FROM `card` LIKE 'faceup_age'"))) {
            self::applyDbUpgradeToAllDB("ALTER TABLE DBPREFIX_card ADD `faceup_age` TINYINT UNSIGNED DEFAULT NULL;");
            self::DbQuery("UPDATE `card` SET `faceup_age` = `age`");
        }
        if (is_null(self::getUniqueValueFromDB("SHOW COLUMNS FROM `card` LIKE 'has_demand'"))) {
            self::applyDbUpgradeToAllDB("ALTER TABLE DBPREFIX_card ADD `has_demand` BOOLEAN NOT NULL DEFAULT FALSE;");
        }
        if (is_null(self::getUniqueValueFromDB("SHOW COLUMNS FROM `card` LIKE 'is_relic'"))) {
            self::applyDbUpgradeToAllDB("ALTER TABLE DBPREFIX_card ADD `is_relic` BOOLEAN NOT NULL DEFAULT FALSE;"); 
        }
        if (is_null(self::getUniqueValueFromDB("SHOW COLUMNS FROM `card` LIKE 'icon_hash'"))) {
            self::applyDbUpgradeToAllDB("ALTER TABLE DBPREFIX_card ADD `icon_hash` INT(32) UNSIGNED DEFAULT NULL;"); 
        }
        if (is_null(self::getUniqueValueFromDB("SHOW COLUMNS FROM `random` LIKE 'type'"))) {
            self::applyDbUpgradeToAllDB("ALTER TABLE DBPREFIX_random ADD `type` TINYINT UNSIGNED;"); 
        }
        if (is_null(self::getUniqueValueFromDB("SHOW COLUMNS FROM `player` LIKE 'featured_icon_count'"))) {
            self::applyDbUpgradeToAllDB("ALTER TABLE DBPREFIX_player ADD `featured_icon_count` SMALLINT UNSIGNED DEFAULT NULL;"); 
        }
        if (is_null(self::getUniqueValueFromDB("SHOW COLUMNS FROM `player` LIKE 'turn_order_ending_with_launcher'"))) {
            self::applyDbUpgradeToAllDB("ALTER TABLE DBPREFIX_player ADD `turn_order_ending_with_launcher` SMALLINT UNSIGNED DEFAULT NULL;"); 
        }
        if ($from_version <= 2111030321) {
            $players = self::getCollectionFromDb("SELECT player_id FROM player");
            foreach($players as $player_id => $player) {
                self::updatePlayerRessourceCounts($player_id);
            }
        }

        if (self::getGameStateValue('release_version') == 0) {
            self::initGameStateLabels(array(
                'card_id_1' => 69,
                'card_id_2' => 70,
                'card_id_3' => 71,
                'require_achievement_eligibility' => 72,
                'has_demand_effect' => 73,
                'has_splay_direction' => 74,
                'owner_last_selected' => 75,
                'type_array' => 76,
                'age_array' => 77,
                'player_array' => 78,
                'icon_hash_1' => 79,
                'icon_hash_2' => 80,
                'icon_hash_3' => 81,
                'icon_hash_4' => 82,
                'icon_hash_5' => 83,
                'enable_autoselection' => 84,
                'include_relics' => 85,
                'relic_id' => 95,
                'current_action_number' => 96,
                'current_nesting_index' => 97,
                'release_version' => 98,
                'debug_mode' => 99
            ));
            self::setGameStateValue('card_id_1', -2);
            self::setGameStateValue('card_id_2', -2);
            self::setGameStateValue('card_id_3', -2);
            self::setGameStateValue('require_achievement_eligibility', -1);
            self::setGameStateValue('has_demand_effect', -1);
            self::setGameStateValue('has_splay_direction', -1);
            self::setGameStateValue('owner_last_selected', -1);
            self::setGameStateValue('type_array', -1);
            self::setGameStateValue('age_array', -1);
            self::setGameStateValue('player_array', -1);
            self::setGameStateValue('icon_hash_1', -1);
            self::setGameStateValue('icon_hash_2', -1);
            self::setGameStateValue('icon_hash_3', -1);
            self::setGameStateValue('icon_hash_4', -1);
            self::setGameStateValue('icon_hash_5', -1);
            self::setGameStateValue('enable_autoselection', -1);
            self::setGameStateValue('include_relics', -1);
            self::setGameStateValue('relic_id', -1);
            self::setGameStateValue('current_action_number', -1);
            self::setGameStateValue('current_nesting_index', -1);
            self::setGameStateValue('release_version', 0);
            self::setGameStateValue('debug_mode', 0);
        }
    }

    //****** CODE FOR DEBUG MODE
    function debug_draw($card_id) {
        if (self::getGameStateValue('debug_mode') == 0) {
            return; // Not in debug mode
        }
        $player_id = self::getCurrentPlayerId();
        $card = self::getCardInfo($card_id);
        if ($card['location'] == 'board' || $card['location'] == 'deck' || $card['location'] == 'relics' || $card['location'] == 'score' || ($card['location'] == 'hand' && $card['owner'] != $player_id)) {
            self::transferCardFromTo($card, $player_id, 'hand');
        } else if ($card['location'] == 'achievements') {
            throw new BgaUserException("This card is used as an achievement");
        } else if ($card['location'] == 'removed') {
            throw new BgaUserException("This card is removed from the game");
        } else {
            throw new BgaUserException(self::format("This card is in {player_name}'s {location}", array('player_name' => self::getPlayerNameFromId($card['owner']), 'location' => $card['location'])));
        }
    }
    function debug_meld($card_id) {
        if (self::getGameStateValue('debug_mode') == 0) {
            return; // Not in debug mode
        }
        // The melding is being done in two steps because otherwise many of the transitions would not be supported.
        $player_id = self::getCurrentPlayerId();
        $card = self::getCardInfo($card_id);
        if (!($card['location'] == 'hand' && $card['owner'] == $player_id)) {
            self::debug_draw($card_id);
            $card = self::getCardInfo($card_id);
        }
        self::transferCardFromTo($card, $player_id, 'board');
    }
    function debug_score($card_id) {
        if (self::getGameStateValue('debug_mode') == 0) {
            return; // Not in debug mode
        }
        $player_id = self::getCurrentPlayerId();
        $card = self::getCardInfo($card_id);
        if ($card['location'] == 'hand' || $card['location'] == 'board' || $card['location'] == 'deck') {
            self::scoreCard($card, $player_id);
        } else if ($card['location'] == 'achievements') {
            throw new BgaUserException("This card is used as an achievement");
        } else if ($card['location'] == 'relics') {
            throw new BgaUserException("This card is used as a relic");
        } else if ($card['location'] == 'removed') {
            throw new BgaUserException("This card is removed from the game");
        } else {
            throw new BgaUserException(self::format("This card is in {player_name}'s {location}", array('player_name' => self::getPlayerNameFromId($card['owner']), 'location' => $card['location'])));
        }
    }
    function debug_achieve($card_id) {
        if (self::getGameStateValue('debug_mode') == 0) {
            return; // Not in debug mode
        }
        $player_id = self::getCurrentPlayerId();
        $card = self::getCardInfo($card_id);
        if ($card['location'] == 'achievements' && $card['owner'] == $player_id) {
            throw new BgaUserException("You already have this card as an achievement");
        } else if ($card['location'] == 'removed') {
            throw new BgaUserException("This card is removed from the game");
        } else if ($card['location'] == 'hand' || $card['location'] == 'board' || $card['location'] == 'deck' || $card['location'] == 'score' || $card['location'] == 'achievements' || $card['location'] == 'relics') {
            try {
                self::transferCardFromTo($card, $player_id, "achievements");
            }
            catch (EndOfGame $e) {
                // End of the game: the exception has reached the highest level of code
                self::trace('EOG bubbled from self::debug_achieve');
                $this->gamestate->nextState('justBeforeGameEnd');
                return;
            }
        } else {
            throw new BgaUserException(self::format("This card is in {player_name}'s {location}", array('player_name' => self::getPlayerNameFromId($card['owner']), 'location' => $card['location'])));
        }
       
    }
    function debug_return($card_id) {
        if (self::getGameStateValue('debug_mode') == 0) {
            return; // Not in debug mode
        }
        $player_id = self::getCurrentPlayerId();
        $card = self::getCardInfo($card_id);
        if ($card['location'] == 'deck') {
            throw new BgaUserException("This card is already in the deck");
        } else if ($card['location'] == 'relics') {
            throw new BgaUserException("This card is already in the relics area");
        } else if ($card['location'] == 'removed') {
            throw new BgaUserException("This card is removed from the game");
        } else if ($card['location'] == 'hand' || $card['location'] == 'board' || $card['location'] == 'score' || $card['location'] == 'display' || $card['location'] == 'achievements') {
            try {
                self::transferCardFromTo($card, 0, "deck");
            }
            catch (EndOfGame $e) {
                // End of the game: the exception has reached the highest level of code
                self::trace('EOG bubbled from self::debug_return');
                $this->gamestate->nextState('justBeforeGameEnd');
                return;
            }
        } else {
            throw new BgaUserException(self::format("This card is in {player_name}'s {location}", array('player_name' => self::getPlayerNameFromId($card['owner']), 'location' => $card['location'])));
        }
    }
    function debug_topdeck($card_id) {
        if (self::getGameStateValue('debug_mode') == 0) {
            return; // Not in debug mode
        }
        // The topdecking is being done in two steps because otherwise many of the transitions would not be supported.
        $player_id = self::getCurrentPlayerId();
        $card = self::getCardInfo($card_id);
        if (!($card['location'] == 'hand' && $card['owner'] == $player_id)) {
            self::debug_draw($card_id);
            $card = self::getCardInfo($card_id);
        }
        self::transferCardFromTo($card, 0, 'deck', /*bottom_to=*/ false);
    }
    function debug_dig($card_id) {
        if (self::getGameStateValue('debug_mode') == 0) {
            return; // Not in debug mode
        }
        $player_id = self::getCurrentPlayerId();
        $card = self::getCardInfo($card_id);
        if (self::getArtifactOnDisplay($player_id) != null) {
            throw new BgaUserException("There is already an Artifact on display");
        } else if ($card['location'] == 'achievements') {
            throw new BgaUserException("This card is used as an achievement");
        } else if ($card['location'] == 'relics') {
            throw new BgaUserException("This card is used as a relic");
        } else if ($card['location'] == 'removed') {
            throw new BgaUserException("This card is removed from the game");
        } else if ($card['location'] == 'deck') {
            try {
                self::transferCardFromTo($card, $player_id, "display");
            }
            catch (EndOfGame $e) {
                // End of the game: the exception has reached the highest level of code
                self::trace('EOG bubbled from self::debug_dig');
                $this->gamestate->nextState('justBeforeGameEnd');
                return;
            }
        } else {
            throw new BgaUserException(self::format("This card is in {player_name}'s {location}", array('player_name' => self::getPlayerNameFromId($card['owner']), 'location' => $card['location'])));
        }
    }
    //******
    
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
        $default_colors = array("0000ff", "ff0000", "008000", "ffa500");
        
        // Create players
        // Note: if you added some extra field on "player" table in the database (dbmodel.sql), you can initialize it there.
        
        $game_type = self::getGameStateValue('game_type');
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
            else { // Team game: the players of the same team are sit in front of each other
                $t = ($t+1)%2;
            }
        }
        $sql .= implode($values, ',');
        self::DbQuery($sql);
        if ($individual_game) { // We can take into account the preferences of players on colors
            self::reattributeColorsBasedOnPreferences($players, $default_colors);
        }
        self::reloadPlayersBasicInfos();
        
        /************ Start the game initialization *****/

        // Indicate that this production game was created after the Artifacts expansion was released
        self::setGameStateInitialValue('release_version', 1);

        // Init global values with their initial values
        self::setGameStateValue('debug_mode', $this->getBgaEnvironment() == 'studio' ? 1 : 0);
        
        // Number of achievements needed to win: 6 with 2 players, 5 with 3 players, 4 with 4 players and 6 for team game
        $number_of_achievements_needed_to_win = $individual_game ? 8 - count($players) : 6;
        self::setGameStateInitialValue('number_of_achievements_needed_to_win', $number_of_achievements_needed_to_win);

        // Add one required achievement for each expansion
        if (self::getGameStateValue('artifacts_mode') == 2 || self::getGameStateValue('artifacts_mode') == 3) {
            self::incGameStateValue('number_of_achievements_needed_to_win', 1);
        }

        // Add extra achievement to win
        if (self::getGameStateValue('extra_achievement_to_win') == 2) {
            self::incGameStateValue('number_of_achievements_needed_to_win', 1);
        }

        // Flag used to know if we are still on turn0 (1) or not (0)
        self::setGameStateInitialValue('turn0', 1);
        
        // Flags used to know if the player has one or two actions to perform
        self::setGameStateInitialValue('first_player_with_only_one_action', 1);
        self::setGameStateInitialValue('second_player_with_only_one_action', count($players) >= 4 ? 1 : 0); // used when >= 4 players only
        self::setGameStateInitialValue('has_second_action', 1);
        if (self::getGameStateValue('release_version') >= 1) {
            self::setGameStateInitialValue('current_action_number', -1);
        }
        
        // Flags used when the game ends to know how it ended
        self::setGameStateInitialValue('game_end_type', -1); // 0 for game end by achievements, 1 for game end by score, -1 for game end by dogma
        self::setGameStateInitialValue('player_who_could_not_draw', -1); // When end of game by score, id of the player who triggered it

        // Flag used to remember whose turn it is
        self::setGameStateInitialValue('active_player', -1);
        
        // Flags used in dogma to remember player roles and which card it is, which effect (yet -1 as default value since there are not currently in use)
        self::setGameStateInitialValue('sharing_bonus', -1); // 1 if the dogma player will have a sharing bonus, else 0
        if (self::getGameStateValue('release_version') >= 1) {
            self::setGameStateInitialValue('current_nesting_index', -1);
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
        } else {
            self::setGameStateInitialValue('current_player_under_dogma_effect', -1);
            self::setGameStateInitialValue('dogma_card_id', -1);
            self::setGameStateInitialValue('current_effect_type', -1); // 0 for I demand dogma, 1 for non-demand dogma, 2 for I compel dogma
            self::setGameStateInitialValue('current_effect_number', -1); // 1, 2 or 3
            for($i=1; $i<=9; $i++) {
                self::setGameStateInitialValue('nested_id_'.$i, -1); // The card being executed through exclusive execution
                self::setGameStateInitialValue('nested_current_effect_number_'.$i, -1); // The non-demand effect number currently executed
            }
            self::setGameStateInitialValue('step', -1);
            self::setGameStateInitialValue('step_max', -1);
        }
        
        // Flag used for player interaction in dogma to remember what splay is proposed (-1 as default and if the choice does not involve splaying)
        self::setGameStateInitialValue('splay_direction', -1);
        
        // Flags used to describe the range of the selection the player in dogma must take (yet -1 as default value since there are not currently in use)
        self::setGameStateInitialValue('special_type_of_choice', -1); // Indicate the type of choice the player faces. 0 for choosing a card or a color for splay, 1 for choosing an opponent, 2 for choising an opponent with fewer points, 3 for choosing a value, 4 for choosing between yes or no
        self::setGameStateInitialValue('choice', -1); // Numeric choice when the player has to make a special choice (-2 if the player passed)
        self::setGameStateInitialValue('n_min', -1); // Minimal number of cards to be chosen (999 stands for all possible)
        self::setGameStateInitialValue('n_max', -1); // Maximal number of cards to be chosen (999 stands for no limit)
        self::setGameStateInitialValue('solid_constraint', -1); // 1 if there need to be at least n_min cards to trigger the effect or 0 if it is triggered no matter what, which will consume all eligible cards (do what you can rule)
        self::setGameStateInitialValue('owner_from', -1); // Owner from whom choose the card (0 for nobody, -2 for any player, -3 for any opponent, -4 for any other player)
        self::setGameStateInitialValue('location_from', -1); // Location from where choose the card (0 for deck, 1 for hand, 2 for board, 3 for score)
        self::setGameStateInitialValue('owner_to', -1); // Owner to whom the chosen card will be transfered (0 for nobody)
        self::setGameStateInitialValue('location_to', -1); // Location where the chosen card will be transfered (0 for deck, 1 for hand, 2 for board, 3 for score)
        self::setGameStateInitialValue('age_min', -1); // Age min of the card to be chosen
        self::setGameStateInitialValue('age_max', -1); // Age max of the card to be chosen
        self::setGameStateInitialValue('age_array', -1); // List of selectable ages encoded in a single value
        self::setGameStateInitialValue('color_array', -1); // List of selectable colors encoded in a single value
        self::setGameStateInitialValue('type_array', -1); // List of selectable types encoded in a single value
        self::setGameStateInitialValue('player_array', -1); // List of selectable players encoded in a single value (players are listed by their 'player_no', not their 'player_id')
        self::setGameStateInitialValue('with_icon', -1); // 0 if there is no specific icon for the card to be selected, else the number of the icon needed
        self::setGameStateInitialValue('without_icon', -1); // 0 if there is no specific icon for the card to be selected, else the number of the icon which can't be selected
        self::setGameStateInitialValue('not_id', -1); // id of a card which cannot be selected, else -2
        self::setGameStateInitialValue('card_id_1', -1); // id of a card which is allowed to be selected, else -2
        self::setGameStateInitialValue('card_id_2', -1); // id of a card which is allowed to be selected, else -2
        self::setGameStateInitialValue('card_id_3', -1); // id of a card which is allowed to be selected, else -2
        self::setGameStateInitialValue('icon_hash_1', -1); // icon hash of a card which is allowed to be selected, else -1
        self::setGameStateInitialValue('icon_hash_2', -1); // icon hash of a card which is allowed to be selected, else -1
        self::setGameStateInitialValue('icon_hash_3', -1); // icon hash of a card which is allowed to be selected, else -1
        self::setGameStateInitialValue('icon_hash_4', -1); // icon hash of a card which is allowed to be selected, else -1
        self::setGameStateInitialValue('icon_hash_5', -1); // icon hash of a card which is allowed to be selected, else -1
        self::setGameStateInitialValue('enable_autoselection', -1); // 1 if cards are allowed to be autoselected during an interaction
        self::setGameStateInitialValue('include_relics', -1); // 1 if relics cards are allowed to be selected during an interaction
        self::setGameStateInitialValue('can_pass', -1); // 1 if the player can pass else 0
        self::setGameStateInitialValue('n', -1); // Actual number of cards having being selected yet
        self::setGameStateInitialValue('id_last_selected', -1); // Id of the last selected card
        self::setGameStateInitialValue('age_last_selected', -1); // Age of the last selected card
        self::setGameStateInitialValue('color_last_selected', -1); // Color of the last selected card
        self::setGameStateInitialValue('owner_last_selected', -1); // Owner of the last selected card
        self::setGameStateInitialValue('score_keyword', -1); // 1 if the action with the chosen card will be scoring, else 0
        self::setGameStateInitialValue('require_achievement_eligibility', -1); // 1 if the numeric achievement card can only be selected if the player is eligible to claim it based on their score
        self::setGameStateInitialValue('has_demand_effect', -1); // 1 if the card to be chosen must have a demand effect on it
        self::setGameStateInitialValue('has_splay_direction', -1); // List of splay directions encoded in a single value
        
        // Flags specific to some dogmas
        self::setGameStateInitialValue('auxiliary_value', -1); // This value is used when in dogma for some specific cards when it is needed to remember something between steps or effect. By default, it does not reinitialise until the end of the dogma

        // Flag specific to seizing Relics
        self::setGameStateInitialValue('relic_id', -1);
        
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
        
        // Remove cards from expansions that are not in use.
        if (self::getGameStateValue('artifacts_mode') == 1) {
            self::DbQuery("UPDATE card SET location = 'removed', position = NULL WHERE 110 <= id AND id <= 214");
        }
        if (self::getGameStateValue('artifacts_mode') != 3) {
            self::DbQuery("UPDATE card SET location = 'removed', position = NULL WHERE is_relic");
        }

        // Initialize Artifacts-specific statistics
        if (self::getGameStateValue('artifacts_mode') != 1) {
            self::initStat('player', 'dig_events_number', 0);
            self::initStat('player', 'free_action_dogma_number', 0);
            self::initStat('player', 'free_action_return_number', 0);
            self::initStat('player', 'free_action_pass_number', 0);
            self::initStat('player', 'dogma_actions_number_targeting_artifact_on_board', 0);
            self::initStat('player', 'dogma_actions_number_with_i_compel', 0);
            self::initStat('player', 'i_compel_effects_number', 0);
            
            // Initialize Relic-specific statistics
            if (self::getGameStateValue('artifacts_mode') == 3) {
                self::initStat('player', 'relics_seized_number', 0);
                self::initStat('player', 'relics_stolen_number', 0);
            }
        }
        
        // Store the age of each card when face-up
        self::DbQuery("UPDATE card SET faceup_age = (CASE id WHEN 188 THEN 11 ELSE age END)");

        // Create a hash of the icons for each card
        self::calculateIconHashForAllCards();

        // Card shuffling in decks
        self::shuffle();
        
        // Isolate one base card of each age (except 10) to create the available age achievements
        self::extractAgeAchievements();
        
        // Deal 2 cards of age 1 to each player
        for ($times = 0; $times < 2; $times++) {
            foreach ($players as $player_id => $player) {
                self::executeDraw($player_id, 1);
            }
        }

        // Add information to the database about which cards have a demand.
        foreach ($this->textual_card_infos as $id => $card_info) {
            if ($card_info['i_demand_effect_1'] !== null) {
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
        
        $result['debug_mode'] = self::getGameStateValue('debug_mode');

        // Get static information about all cards
        $cards  = array();
        foreach (self::getStaticInfoOfAllCards() as $card) {
            $cards[$card['id']] = $card;
        }
        $result['cards'] = $cards;

        $result['artifacts_expansion_enabled'] = self::getGameStateValue('artifacts_mode') != 1;
        $result['relics_enabled'] = self::getGameStateValue('artifacts_mode') == 3;
    
        $current_player_id = self::getCurrentPlayerId();    // !! We must only return information visible by this player !!
        
        // Get information about players
        $players = self::getCollectionFromDb("SELECT player_id, player_score, player_team FROM player");
        $result['players'] = $players;
        $result['current_player_id'] = $current_player_id;
        
        // Public information

        // Number of achievements needed to win
        $result['number_of_achievements_needed_to_win'] = self::getGameStateValue('number_of_achievements_needed_to_win');
        
        // All boards
        $result['board'] = self::getAllBoards($players);
        
        // Splay state for stacks on board
        $result['board_splay_directions'] = array();
        $result['board_splay_directions_in_clear'] = array();
        foreach($players as $player_id => $player) {
            $result['board_splay_directions'][$player_id] = array();
            $result['board_splay_directions_in_clear'][$player_id] = array();
            for($color = 0; $color < 5 ; $color++) {
                $direction = self::getCurrentSplayDirection($player_id, $color);
                $result['board_splay_directions'][$player_id][] = $direction;
                $result['board_splay_directions_in_clear'][$player_id][] = self::getSplayDirectionInClear($direction);
            }
        }

        // Artifacts on display
        $result['artifacts_on_display'] = self::getArtifactsOnDisplay($players);
        
        // Backs of the cards in hands
        $result['hand_counts'] = array();
        for ($type = 0; $type <= 1; $type++) {
            for ($is_relic = 0; $is_relic <= 1; $is_relic++) {
                foreach ($players as $player_id => $player) {
                    $result['hand_counts'][$player_id][$type][$is_relic] = self::countCardsInLocationKeyedByAge($player_id, 'hand', $type, $is_relic);
                }
            }
        }

        // Backs of the cards in score piles
        $result['score_counts'] = array();
        for ($type = 0; $type <= 1; $type++) {
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

        // Unclaimed achivements 
        $result['unclaimed_relics'] = self::getCardsInLocation(0, 'relics');
        
        // Unclaimed achivements 
        $result['unclaimed_achievements'] = self::getCardsInLocation(0, 'achievements');
        
        // Claimed achievements for each player
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
        for ($type = 0; $type <= 1; $type++) {
            $result['deck_counts'][$type] = self::countCardsInLocationKeyedByAge(0, 'deck', $type);
        }
        
        // Turn0 or not
        $result['turn0'] = self::getGameStateValue('turn0') == 1;
        
        // Number of achievements needed to win
        $result['number_of_achievements_needed_to_win'] = self::getGameStateValue('number_of_achievements_needed_to_win');
        
        // Link to the current dogma effect (if any)
        if (self::getGameStateValue('release_version') >= 1) {
            $nested_card_state = self::getCurrentNestedCardState();
            if ($nested_card_state == null) {
                $JSCardEffectQuery = null;
            } else {
                $current_effect_type = $nested_card_state['current_effect_type'];
                $current_effect_number = $nested_card_state['current_effect_number'];
                $card_id = $nested_card_state['card_id'];
                $JSCardEffectQuery = $card_id == -1 ? null : self::getJSCardEffectQuery(self::getCardInfo($card_id), $current_effect_type, $current_effect_number);
            }
        } else {
            $nested_id_1 = self::getGameStateValue('nested_id_1');
            $card_id = $nested_id_1 == -1 ? self::getGameStateValue('dogma_card_id') : $nested_id_1;
            if ($card_id == -1) {
                $JSCardEffectQuery = null;
            } else {
                $card = self::getCardInfo($card_id);
                $current_effect_type = $nested_id_1 == -1 ? self::getGameStateValue('current_effect_type') : 1 /* Non-demand effects only*/;
                $current_effect_number = $nested_id_1 == -1 ?  self::getGameStateValue('current_effect_number') : self::getGameStateValue('nested_current_effect_number_1');
                $JSCardEffectQuery = $current_effect_number == -1 ? null : self::getJSCardEffectQuery($card, $current_effect_type, $current_effect_number);
            }
        }
        $result['JSCardEffectQuery'] = $JSCardEffectQuery;
        
        // Whose turn is it?
        $active_player = self::getGameStateValue('active_player');
        $result['active_player'] = $active_player == -1 ? null : $active_player;
        if ($active_player != -1) {
            if (self::getGameStateValue('release_version') >= 1) {
                $action_number = self::getGameStateValue('current_action_number');
                $result['action_number'] = $action_number;
                $card = self::getArtifactOnDisplay($active_player);
                if ($card !== null && $this->gamestate->state()['name'] == 'artifactPlayerTurn') {
                    $result['artifact_on_display_icons'] = array();
                    $result['artifact_on_display_icons']['resource_icon'] = $card['dogma_icon'];
                    $result['artifact_on_display_icons']['resource_count_delta'] = self::countIconsOnCard($card, $card['dogma_icon']);
                }
            } else {
                $result['action_number'] = self::getGameStateValue('first_player_with_only_one_action') || self::getGameStateValue('second_player_with_only_one_action') || self::getGameStateValue('has_second_action') ? 1 : 2;
            }
        }
        
        // Private information
        // My hand
        $result['my_hand'] = self::flatten(self::getCardsInLocationKeyedByAge($current_player_id, 'hand'));
        
        // My score
        $result['my_score'] = self::flatten(self::getCardsInLocationKeyedByAge($current_player_id, 'score'));
        
        // My wish for splay
        $result['display_mode'] = self::getPlayerWishForSplay($current_player_id);        
        $result['view_full'] = self::getPlayerWishForViewFull($current_player_id);
        
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
        
        $number_of_cards_in_decks = self::countCardsInLocationKeyedByAge(0, 'deck');
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
        $n_max = self::getGameStateValue('number_of_achievements_needed_to_win');
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
    
    /** Utilities for storing arrays of unique unsorted little positive integer values in a single game state value **/
    function setGameStateValueFromArray($key, $array) {
        self::setGameStateValue($key, self::getArrayAsValue($array));
    }

    function getArrayAsValue($array) {
        $encoded_value = 0;
        foreach ($array as $value) {
            $encoded_value += pow(2, $value);
        }
        return $encoded_value;
    }
    
    function getGameStateValueAsArray($key) {
        $encoded_value = self::getGameStateValue($key);
        return self::getValueAsArray($encoded_value);
    }
    
    function getValueAsArray($encoded_value) {
        $array = array();
        $value = 0;
        while($encoded_value > 0) {
            if ($encoded_value % 2 == 1) {
                $array[] = $value;
            }
            $encoded_value /= 2;
            $value++;
        }
        return $array;
    }
    
    /* Encodes multiple integers into a single integer */
    function getValueFromBase16Array($array) {
        // Due to the maximum data value of 0x8000000, only 5 elements can be encoded using this function.
        if (count($array) > 5) {
            throw new BgaVisibleSystemException("setGameStateBase16Array() cannot encode more than 5 integers at once");
        }
        $encoded_value = 0;
        foreach($array as $value) {
            // This encoding assumes that each integer is in the range [0, 15].
            if ($value < 0 || $value > 15) {
                throw new BgaVisibleSystemException("setGameStateBase16Array() cannot encode integers smaller than 0 or larger than 15");
            }
            $encoded_value = $encoded_value * 16 + $value;
        }
        return $encoded_value * 6 + count($array);
    }
    
    /* Decodes an integer representing multiple integers */
    function getBase16ArrayFromValue($encoded_value) {
        $count = $encoded_value % 6;
        $encoded_value /= 6;
        $return_array = array();
        for ($i = 0; $i < $count; $i++) {
            $return_array[] = $encoded_value % 16;
            $encoded_value /= 16;
        }
        return $return_array;
    }

    function getFaceupAgeLastSelected() {
        return self::getUniqueValueFromDB(self::format("SELECT faceup_age FROM card WHERE id = {id}", array('id' => self::getGameStateValue('id_last_selected'))));
    }
    
    /** integer division **/
    function intDivision($a, $b) {
        return (int)($a/$b);
    }

    /** Returns the card types in use by the current game **/
    function getActiveCardTypes() {
        // TODO(CITIES, ECHOES): This needs to be updated when expansions are added. Right now, the only time
        // we use this is when we are playing with Artifacts, so if that's the case then both the Base and
        // Artifacts card types are being used by the game.
        return array(0, 1);
    }

    function playerIdToPlayerNo($player_id) {
        return self::getUniqueValueFromDB(self::format("SELECT player_no FROM player WHERE player_id = {player_id}", array('player_id' => $player_id)));
    }

    function playerNoToPlayerId($player_no) {
        return self::getUniqueValueFromDB(self::format("SELECT player_id FROM player WHERE player_no = {player_no}", array('player_no' => $player_no)));
    }

    function getAllPlayerIds() {
        return self::getObjectListFromDB("SELECT player_id FROM player", true);
    }

    function getAllActivePlayerIds() {
        return self::getObjectListFromDB("SELECT player_id FROM player WHERE player_eliminated = 0", true);
    }

    function getAllActivePlayers() {
        return self::getObjectListFromDB("SELECT player_no FROM player WHERE player_eliminated = 0", true);
    }

    function getOtherActivePlayers($player_id) {
        return self::getObjectListFromDB(self::format("
            SELECT
                player_no
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
                player_no
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
                player_no
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
    function format($msg, $vars)
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
    
    /** Flatten an array (one level) **/
    function flatten($array) {
        $result = array();
        foreach($array as $key => $subarray) {
            $result = array_merge($result, $subarray);
        }
        return $result;
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
        self::DbQuery("
        UPDATE
            card as a
            INNER JOIN (SELECT age, MAX(position) AS position FROM card WHERE type = 0 GROUP BY age) as b ON a.age = b.age
        SET
            a.location = 'achievements',
            a.position = a.age-1
        WHERE
            a.position = b.position AND
            a.type = 0 AND
            a.age BETWEEN 1 AND 9
        ");
    }
    
    function tuckCard($card, $owner_to) {
        return self::transferCardFromTo($card, $owner_to, 'board', /*bottom_to=*/ true);
    }

    function scoreCard($card, $owner_to) {
        return self::transferCardFromTo($card, $owner_to, 'score', /*bottom_to=*/ false, /*score_keyword=*/ true);
    }

    /**
     * Executes the transfer of the card, returning the new card info.
     **/
    function transferCardFromTo($card, $owner_to, $location_to, $bottom_to = null, $score_keyword = false) {

        // Do not move the card at all.
        if ($location_to == 'none') {
            return;
        }

        // Relics are not returned to the deck.
        if ($card['is_relic'] && $location_to == 'deck') {
            $location_to = 'relics';
        }

        // By default, cards are returned to the bottom of the deck, but other cards are returned to the top of their locations
        if ($bottom_to === null) {
            $bottom_to = $location_to == 'deck';
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
        }
        else { // $location_to != 'board'
            $splay_direction_to = 'NULL';
        }
        
        // Filter from
        $filter_from = self::format("owner = {owner_from} AND location = '{location_from}'", array('owner_from' => $owner_from, 'location_from' => $location_from));
        switch ($location_from) {
        case 'deck':
            $filter_from .= self::format(" AND type = {type} AND age = {age}", array('type' => $type, 'age' => $age));
            break;
        case 'hand':
        case 'score':
        case 'relics':
            $filter_from .= self::format(" AND type = {type} AND age = {age} AND is_relic = {is_relic}", array('type' => $type, 'age' => $age, 'is_relic' => $is_relic));
            break;
        case 'board':
            $filter_from .= self::format(" AND color = {color}", array('color' => $color));
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
        case 'hand':
        case 'score':
        case 'relics':
            $filter_to .= self::format(" AND type = {type} AND age = {age} AND is_relic = {is_relic}", array('type' => $type, 'age' => $age, 'is_relic' => $is_relic));
            break;
        case 'board':
            $filter_to .= self::format(" AND color = {color}", array('color' => $color));
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

        
        $transferInfo = array(
            'owner_from' => $owner_from, 'location_from' => $location_from, 'position_from' => $position_from, 'splay_direction_from' => $splay_direction_from, 
            'owner_to' => $owner_to, 'location_to' => $location_to, 'position_to' => $position_to, 'splay_direction_to' => $splay_direction_to, 
            'bottom_to' => $bottom_to, 'score_keyword' => $score_keyword
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

    /*
     * Assigns each card in the DB a hash for the icons on the card (equality means that the two cards share the same icons in both type and number).
     */
    function calculateIconHashForAllCards() {
        $cards = self::getObjectListFromDB("
            SELECT
                id,
                spot_1,
                spot_2,
                spot_3,
                spot_4
            FROM
                card
        ");
        
        foreach ($cards as $card) {
            // 1 is used for hex icons, allowing it to be ignored in the product
            // TODO(CITIES): Revisit formula.
            $icon_hash_key = array(1, 2, 3, 5, 7, 13, 17);
            $hash_value = ($icon_hash_key[$card['spot_1'] ?: 0]) *
                          ($icon_hash_key[$card['spot_2'] ?: 0]) *
                          ($icon_hash_key[$card['spot_3'] ?: 0]) *
                          ($icon_hash_key[$card['spot_4'] ?: 0]);
            self::DbQuery(self::format("UPDATE card SET icon_hash = {hash_value} WHERE id = {id}", array('hash_value' => $hash_value, 'id' => $card['id'])));
        }
    }
    
    /** Splay mechanism **/

    function unsplay($player_id, $target_player_id, $color) {
        self::splay($player_id, $target_player_id, $color, /*splay_direction=*/ 0, /*force_unsplay=*/ true);
    }

    function splayLeft($player_id, $target_player_id, $color) {
        self::splay($player_id, $target_player_id, $color, /*splay_direction=*/ 1);
    }

    function splayRight($player_id, $target_player_id, $color) {
        self::splay($player_id, $target_player_id, $color, /*splay_direction=*/ 2);
    }

    function splayUp($player_id, $target_player_id, $color) {
        self::splay($player_id, $target_player_id, $color, /*splay_direction=*/ 3);
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
        self::recordThatChangeOccurred();
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
        return self::getObjectListFromDB("SELECT * FROM card WHERE selected IS TRUE");
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
                (owner != {player_id} OR location = 'score' OR location = 'achievements')
        ", // The opposite of the cards the player can see except that we potentially select the cards in his score pile too (to enable direct selection if the player is lazy to see the card in his score pile for transfer)
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
    function notifyAll($notification_type, $notification_log, $notification_args) {
        self::notifyAllPlayersBut(array(), $notification_type, $notification_log, $notification_args);
    }
    
    function notifyAllPlayersBut($player_ids, $notification_type, $notification_log, $notification_args) {
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
    
    function updateGameSituation($card, $transferInfo) {
        self::recordThatChangeOccurred();

        $owner_from = $transferInfo['owner_from'];
        $owner_to = $transferInfo['owner_to'];
        $location_from = $transferInfo['location_from'];
        $location_to = $transferInfo['location_to'];
        $bottom_to = $transferInfo['bottom_to'];
        $age = $card['age'];
        
        $score_from_update = $location_from == 'score';
        $score_to_update = $location_to == 'score';
        
        $max_age_on_board_from_update = $location_from == 'board';
        $max_age_on_board_to_update = $location_to == 'board';
        
        $progressInfo = array();
        // Update player progression if applicable
        $one_player_involved = $owner_from == 0 || $owner_to == 0 || $owner_from == $owner_to;
              
        if ($one_player_involved) { // One player involved
            $player_id = $owner_to == 0 ? $owner_from : $owner_to; // The player whom transfer will change something on the cards he owns
            $transferInfo['player_id'] = $player_id;
            
            if ($score_from_update) {
                $progressInfo['new_score'] = self::updatePlayerScore($owner_from, -$age);
            }
            if ($score_to_update) {
                $progressInfo['new_score'] = self::updatePlayerScore($owner_to, $age);
            }
            if ($max_age_on_board_from_update || $max_age_on_board_to_update) {
                $max_age_on_board = self::getMaxAgeOnBoardTopCards($player_id);
                $progressInfo['new_max_age_on_board'] = $max_age_on_board;
                self::setStat($max_age_on_board, 'max_age_on_board', $player_id);
            }
            if ($location_from == 'board' || $location_to == 'board') {
                $progressInfo['new_ressource_counts'] = self::updatePlayerRessourceCounts($player_id);
            }
            if ($location_to == 'board' && $bottom_to) { // That's a tuck
                // Update player count for Monument
                self::incrementFlagForMonument($player_id, 'number_of_tucked_cards');
            }
            else if ($transferInfo['score_keyword']) { // That's a score
                // Update player count for Monument
                self::incrementFlagForMonument($player_id, 'number_of_scored_cards');
            }
            self::notifyWithOnePlayerInvolved($card, $transferInfo, $progressInfo);
        }
        else { // Two players involved
            $player_id = self::getActivePlayerId(); // $player_id == $owner_from or $owner_to. It is also the one from whom this action is originated
            if ($owner_from != 0 && $owner_to != 0) {
                $opponent_id = $player_id == $owner_from ? $owner_to : $owner_from; // The other player involved in the action
            }
            else { // The action originated from a player which take no part in the transfer
                $player_id = $launcher_id;
                $opponent_id = $owner_from == 0 ? $owner_to : $owner_from;
            }
            $transferInfo['player_id'] = $player_id;
            $transferInfo['opponent_id'] = $opponent_id;
            
            if ($score_from_update) {
                $progressInfo['new_score_from'] = self::updatePlayerScore($owner_from, -$age);
            }
            if ($score_to_update) {
                $progressInfo['new_score_to'] = self::updatePlayerScore($owner_to, $age);
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
                self::incrementBGAScore($owner_to, $card['age'] === null);
            } catch(EndOfGame $e) {
                $end_of_game = true;
            }
        }
        if ($end_of_game) {
            self::trace('EOG bubbled from self::updateGameSituation');
            throw $e; // Re-throw exception to higher level
        }
    }

    function revealHand($player_id) {
        $cards = self::getCardsInHand($player_id);
        if (count($cards) == 0) {
            $this->notifyPlayer($player_id, 'log', clienttranslate('${You} reveal an empty hand.'), ['You' => 'You']);
            $this->notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} reveals an empty hand.'), ['player_name' => self::getPlayerNameFromId($player_id)]);
            return;
        }
        $args = ['card_ids' => self::getCardIds($cards), 'card_list' => self::getNotificationArgsForCardList($cards)];
        $this->notifyPlayer($player_id, 'logWithCardTooltips', clienttranslate('${You} reveal your hand: ${card_list}.'),
            array_merge($args, ['You' => 'You']));
        $this->notifyAllPlayersBut($player_id, 'logWithCardTooltips', clienttranslate('${player_name} reveals his hand: ${card_list}.'),
            array_merge($args, ['player_name' => self::getPlayerNameFromId($player_id)]));
    }

    function revealScorePile($player_id) {
        $cards = self::getCardsInScorePile($player_id);
        if (count($cards) == 0) {
            $this->notifyPlayer($player_id, 'log', clienttranslate('${You} reveal an empty score pile.'), ['You' => 'You']);
            $this->notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} reveals an empty score pile.'), ['player_name' => self::getPlayerNameFromId($player_id)]);
            return;
        }
        $args = ['card_ids' => self::getCardIds($cards), 'card_list' => self::getNotificationArgsForCardList($cards)];
        $this->notifyPlayer($player_id, 'logWithCardTooltips', clienttranslate('${You} reveal your score pile: ${card_list}.'),
            array_merge($args, ['You' => 'You']));
        $this->notifyAllPlayersBut($player_id, 'logWithCardTooltips', clienttranslate('${player_name} reveals his score pile: ${card_list}.'),
            array_merge($args, ['player_name' => self::getPlayerNameFromId($player_id)]));
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
            $log = $log."<span class='square N age age_".$card['age']."'>".$card['age']."</span> ";
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
            $delimiters['<'] = "<span class='square N age'>";
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
            $delimiters['<<<'] = "<span class='achievement_name'>";
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
        return self::getCardExecutionCode($card_id, $current_effect_type, $current_effect_number) . $letter;
    }

    function getCardExecutionCode($card_id, $current_effect_type, $current_effect_number) {
        if (self::getGameStateValue('release_version') >= 1) {
            $post_execution_indicator = self::getCurrentNestedCardState()['post_execution_index'] == 0 ? '' : '+';
        } else {
            $post_execution_indicator = '';
        }
        return $card_id . self::getLetterForEffectType($current_effect_type) . $current_effect_number . $post_execution_indicator;
    }

    function getLetterForEffectType($effect_type) {
        switch ($effect_type) {
            case 0:
                // I demand
                return "D";
            case 1:
                // Non-demand
                return "N";
            case 2:
                // I compel
                return "C";
            default:
                // This should not happen
                throw new BgaVisibleSystemException(self::format(self::_("Unhandled case in {function}: '{code}'"), array('function' => "getLetterForEffectType()", 'code' => $effect_type)));
                break;
        }
    }
    
    function notifyWithOnePlayerInvolved($card, $transferInfo, $progressInfo) {
        $location_from = $transferInfo['location_from'];
        $location_to = $transferInfo['location_to'];
        $bottom_to = $transferInfo['bottom_to'];
        $score_keyword = $transferInfo['score_keyword'];

        switch($location_from . '->' . $location_to) {
        case 'deck->display':
            $message_for_player = clienttranslate('${You} dig ${<}${age}${>} ${<<}${name}${>>} and put it on display.');
            $message_for_others = clienttranslate('${player_name} digs ${<}${age}${>} ${<<}${name}${>>} and puts it on display.');
            break;
        case 'deck->hand':
            $message_for_player = clienttranslate('${You} draw ${<}${age}${>} ${<<}${name}${>>}.');
            $message_for_others = clienttranslate('${player_name} draws a ${<}${age}${>}.');
            break;
        case 'deck->board':
            if ($bottom_to) {
                $message_for_player = clienttranslate('${You} draw and tuck ${<}${age}${>} ${<<}${name}${>>}.');
                $message_for_others = clienttranslate('${player_name} draws and tucks ${<}${age}${>} ${<<}${name}${>>}.');
            }
            else {
                $message_for_player = clienttranslate('${You} draw and meld ${<}${age}${>} ${<<}${name}${>>}.');
                $message_for_others = clienttranslate('${player_name} draws and melds ${<}${age}${>} ${<<}${name}${>>}.');
            }
            break;
        case 'deck->score':
            $message_for_player = clienttranslate('${You} draw and score ${<}${age}${>} ${<<}${name}${>>}.');
            $message_for_others = clienttranslate('${player_name} draws and scores a ${<}${age}${>}.');
            break;
        case 'deck->revealed':
            $message_for_player = clienttranslate('${You} draw and reveal ${<}${age}${>} ${<<}${name}${>>}.');
            $message_for_others = clienttranslate('${player_name} draws and reveals ${<}${age}${>} ${<<}${name}${>>}.');
            break;
        case 'deck->achievements':
            $message_for_player = clienttranslate('${You} draw and achieve a ${<}${age}${>}.');
            $message_for_others = clienttranslate('${player_name} draws and achieves a ${<}${age}${>}.');
            break;
        case 'display->board':
            $message_for_player = clienttranslate('${You} meld ${<}${age}${>} ${<<}${name}${>>} from your display.');
            $message_for_others = clienttranslate('${player_name} melds ${<}${age}${>} ${<<}${name}${>>} from his display.');
            break;
        case 'display->deck':
        case 'display->relics': // Shouldn't be possible, but just in case.
            $message_for_player = clienttranslate('${You} return ${<}${age}${>} ${<<}${name}${>>} from your display.');
            $message_for_others = clienttranslate('${player_name} returns ${<}${age}${>} ${<<}${name}${>>} from his display.');
            break;
        case 'hand->deck':
            if ($bottom_to) {
                $message_for_player = clienttranslate('${You} return ${<}${age}${>} ${<<}${name}${>>} from your hand.');
                $message_for_others = clienttranslate('${player_name} returns a ${<}${age}${>} from his hand.');
            } else {
                $message_for_player = clienttranslate('${You} topdeck ${<}${age}${>} ${<<}${name}${>>} from your hand.');
                $message_for_others = clienttranslate('${player_name} topdecks a ${<}${age}${>} from his hand.');
            }
            break;
        case 'hand->relics':
            $message_for_player = clienttranslate('${You} return ${<}${age}${>} ${<<}${name}${>>} from your hand.');
            $message_for_others = clienttranslate('${player_name} returns ${<}${age}${>} ${<<}${name}${>>} from his hand.');
            break;
        case 'hand->board':
            if ($bottom_to) {
                $message_for_player = clienttranslate('${You} tuck ${<}${age}${>} ${<<}${name}${>>} from your hand.');
                $message_for_others = clienttranslate('${player_name} tucks ${<}${age}${>} ${<<}${name}${>>} from his hand.');
            }
            else {
                $message_for_player = clienttranslate('${You} meld ${<}${age}${>} ${<<}${name}${>>} from your hand.');
                $message_for_others = clienttranslate('${player_name} melds ${<}${age}${>} ${<<}${name}${>>} from his hand.');
            }
            break;
        case 'hand->score':
            if ($score_keyword) {
                $message_for_player = clienttranslate('${You} score ${<}${age}${>} ${<<}${name}${>>} from your hand.');
                $message_for_others = clienttranslate('${player_name} scores a ${<}${age}${>} from his hand.');
            }
            else {
                $message_for_player = clienttranslate('${You} transfer ${<}${age}${>} ${<<}${name}${>>} from your hand to your score pile.');
                $message_for_others = clienttranslate('${player_name} transfers a ${<}${age}${>} from his hand to his score pile.');
            }
            break;
        case 'hand->revealed':
            $message_for_player = clienttranslate('${You} reveal ${<}${age}${>} ${<<}${name}${>>} from your hand.');
            $message_for_others = clienttranslate('${player_name} reveals ${<}${age}${>} ${<<}${name}${>>} from his hand.');
            break;
        case 'hand->achievements':
            $message_for_player = clienttranslate('${You} achieve ${<}${age}${>} ${<<}${name}${>>} from your hand.');
            $message_for_others = clienttranslate('${player_name} achieves a ${<}${age}${>} from his hand.');
            break;
        case 'board->deck':
        case 'board->relics':
        case 'pile->deck': // Skyscrapers
        case 'pile->relics': // Skyscrapers
            $message_for_player = clienttranslate('${You} return ${<}${age}${>} ${<<}${name}${>>} from your board.');
            $message_for_others = clienttranslate('${player_name} returns ${<}${age}${>} ${<<}${name}${>>} from his board.');
            break;
        case 'achievements->relics':
            $message_for_player = clienttranslate('${You} return ${<}${age}${>} ${<<}${name}${>>} from your achievements.');
            $message_for_others = clienttranslate('${player_name} returns ${<}${age}${>} ${<<}${name}${>>} from his achievements.');
            break;
        case 'board->hand':
            $message_for_player = clienttranslate('${You} take back ${<}${age}${>} ${<<}${name}${>>} from your board to your hand.');
            $message_for_others = clienttranslate('${player_name} takes back ${<}${age}${>} ${<<}${name}${>>} from his board to his hand.');
            break;
        case 'board->revealed':
            $message_for_player = clienttranslate('${You} reveal ${<}${age}${>} ${<<}${name}${>>} from your board.');
            $message_for_others = clienttranslate('${player_name} reveals ${<}${age}${>} ${<<}${name}${>>} from his board.');
            break;
        case 'board->board':
            if ($bottom_to) {
                $message_for_player = clienttranslate('${You} tuck ${<}${age}${>} ${<<}${name}${>>}.');
                $message_for_others = clienttranslate('${player_name} tucks ${<}${age}${>} ${<<}${name}${>>}.');
            }
            else {
                $message_for_player = clienttranslate('${You} meld ${<}${age}${>} ${<<}${name}${>>}.');
                $message_for_others = clienttranslate('${player_name} melds ${<}${age}${>} ${<<}${name}${>>}.');
            }
            break;
        case 'board->score':
            $message_for_player = clienttranslate('${You} score ${<}${age}${>} ${<<}${name}${>>} from your board.');
            $message_for_others = clienttranslate('${player_name} scores ${<}${age}${>} ${<<}${name}${>>} from his board.');
            break;
        case 'board->achievements':
            $message_for_player = clienttranslate('${You} achieve ${<}${age}${>} ${<<}${name}${>>} from your board.');
            $message_for_others = clienttranslate('${player_name} achieves ${<}${age}${>} ${<<}${name}${>>} from his board.');
            break;
        case 'score->deck':
            $message_for_player = clienttranslate('${You} return ${<}${age}${>} ${<<}${name}${>>} from your score pile.');
            $message_for_others = clienttranslate('${player_name} returns a ${<}${age}${>} from his score pile.');
            break;
        case 'score->relics':
            $message_for_player = clienttranslate('${You} return ${<}${age}${>} ${<<}${name}${>>} from your score pile.');
            $message_for_others = clienttranslate('${player_name} returns ${<}${age}${>} ${<<}${name}${>>} from his score pile.');
            break;
        case 'score->hand':
            $message_for_player = clienttranslate('${You} transfer ${<}${age}${>} ${<<}${name}${>>} from your score pile to your hand.');
            $message_for_others = clienttranslate('${player_name} transfers a ${<}${age}${>} from his score pile to his hand.');
            break;
        case 'score->board':
            if ($bottom_to) {
                $message_for_player = clienttranslate('${You} tuck ${<}${age}${>} ${<<}${name}${>>} from your score pile.');
                $message_for_others = clienttranslate('${player_name} tucks ${<}${age}${>} ${<<}${name}${>>} from his score pile.');
            }
            else {
                $message_for_player = clienttranslate('${You} meld ${<}${age}${>} ${<<}${name}${>>} from your score pile.');
                $message_for_others = clienttranslate('${player_name} melds ${<}${age}${>} ${<<}${name}${>>} from his score pile.');
            }
            break;
        case 'score->revealed':
            $message_for_player = clienttranslate('${You} reveal ${<}${age}${>} ${<<}${name}${>>} from your score pile.');
            $message_for_others = clienttranslate('${player_name} reveals ${<}${age}${>} ${<<}${name}${>>} from his score pile.');
            break;
        case 'score->achievements':
            $message_for_player = clienttranslate('${You} achieve ${<}${age}${>} ${<<}${name}${>>} from your score pile.');
            $message_for_others = clienttranslate('${player_name} achieves a ${<}${age}${>} from his score pile.');
            break;
        case 'revealed->deck':
        case 'revealed->relics':
            $message_for_player = clienttranslate('${You} return ${<}${age}${>} ${<<}${name}${>>}.');
            $message_for_others = clienttranslate('${player_name} returns ${<}${age}${>} ${<<}${name}${>>}.');
            break;
        case 'revealed->hand':
            $message_for_player = clienttranslate('${You} place ${<}${age}${>} ${<<}${name}${>>} in your hand.');
            $message_for_others = clienttranslate('${player_name} places ${<}${age}${>} ${<<}${name}${>>} in his hand.');
            break;
        case 'revealed->board':
            if ($bottom_to) {
                $message_for_player = clienttranslate('${You} tuck ${<}${age}${>} ${<<}${name}${>>}.');
                $message_for_others = clienttranslate('${player_name} tucks ${<}${age}${>} ${<<}${name}${>>}.');
            } else {
                $message_for_player = clienttranslate('${You} meld ${<}${age}${>} ${<<}${name}${>>}.');
                $message_for_others = clienttranslate('${player_name} melds ${<}${age}${>} ${<<}${name}${>>}.');
            }
            break;
        case 'revealed->score':
            $message_for_player = clienttranslate('${You} score ${<}${age}${>} ${<<}${name}${>>}.');
            $message_for_others = clienttranslate('${player_name} scores ${<}${age}${>} ${<<}${name}${>>}.');
            break;
        case 'revealed->achievements':
            $message_for_player = clienttranslate('${You} achieve ${<}${age}${>} ${<<}${name}${>>}.');
            $message_for_others = clienttranslate('${player_name} achieves ${<}${age}${>} ${<<}${name}${>>}.');
            break;
        case 'revealed->removed':
            $message_for_player = clienttranslate('${<}${age}${>} ${<<}${name}${>>} is removed from the game.');
            $message_for_others = clienttranslate('${<}${age}${>} ${<<}${name}${>>} is removed from the game.');
            break;
        case 'relics->achievements':
            $message_for_player = clienttranslate('${You} seize the ${<}${age}${>} relic to your achievements.');
            $message_for_others = clienttranslate('${player_name} seizes the ${<}${age}${>} relic to his achievements.');
            break;
        case 'relics->hand':
            $message_for_player = clienttranslate('${You} seize ${<}${age}${>} ${<<}${name}${>>} to your hand.');
            $message_for_others = clienttranslate('${player_name} seizes the ${<}${age}${>} relic to his hand.');
            break;
        case 'achievements->hand':
            // TODO(ECHOES,FIGURES): Update this if any cards transfer non-relic cards from a player's achievement pile to their hand.
            $message_for_player = clienttranslate('${You} seize ${<}${age}${>} ${<<}${name}${>>} from your achievements to your hand.');
            $message_for_others = clienttranslate('${player_name} seizes the ${<}${age}${>} relic from his achievements to his hand.');
            break;
        case 'achievements->deck':
            $message_for_player = clienttranslate('${You} return ${<}${age}${>} ${<<}${name}${>>} from your achievements.');
            $message_for_others = clienttranslate('${player_name} returns a ${<}${age}${>} from his achievements.');
            break;
        case 'achievements->achievements': // That is: unclaimed achievement to achievement claimed by player
            if ($card['age'] === null) { // Special achivement
                $message_for_player = clienttranslate('${You} achieve ${<<<}${achievement_name}${>>>}.');
                $message_for_others = clienttranslate('${player_name} achieves ${<<<}${achievement_name}${>>>}.');
            } else { // Age achivement
                $message_for_player = clienttranslate('${You} achieve ${<}${age}${>} ${<<<}(${achievement_name})${>>>}.');
                $message_for_others = clienttranslate('${player_name} achieves ${<}${age}${>} ${<<<}(${achievement_name})${>>>}.');
            }
            break;
                
        default:
            // This should not happen
            throw new BgaVisibleSystemException(self::format(self::_("Unhandled case in {function}: '{code}'"), array('function' => 'notifyWithOnePlayerInvolved()', 'code' => $location_from . '->' . $location_to)));
            break;
        }
        
        self::sendNotificationWithOnePlayerInvolved($message_for_player, $message_for_others, $card, $transferInfo, $progressInfo);
    }
    
    function getTransferInfoWithOnePlayerInvolved($location_from, $location_to, $player_id_is_owner_from, $bottom_to, $you_must, $player_must, $player_name, $number, $cards, $targetable_players, $code) {
        // Creation of the message
        if ($location_from == $location_to && $location_from == 'board') { // Used only for Self service
            // TODO(LATER): We can simplify Self Service to use "board->none", guarded by release_version.
            $message_for_player = clienttranslate('${You_must} choose ${number} other top ${card} from your board');
            $message_for_others = clienttranslate('${player_must} choose ${number} other top ${card} from his board');            
        } else if ($targetable_players !== null) { // Used when several players can be targeted
            switch($location_from . '->' . $location_to) {
            case 'score->deck':
                $message_for_player = clienttranslate('${You_must} return ${number} ${card} from the score pile of ${targetable_players}');
                $message_for_others = clienttranslate('${player_must} return ${number} ${card} from the score pile of ${targetable_players}');
                break;
            case 'board->deck':
                $message_for_player = clienttranslate('${You_must} return ${number} top ${card} from the board of ${targetable_players}');
                $message_for_others = clienttranslate('${player_must} return ${number} top ${card} from the board of ${targetable_players}');
                break;
            case 'board->score':
                $message_for_player = clienttranslate('${You_must} transfer ${number} top ${card} from the board of ${targetable_players} to your score pile');
                $message_for_others = clienttranslate('${player_must} transfer ${number} top ${card} from the board of ${targetable_players} to his score pile');
                break;
            case 'board->none':
                if ($code === '134N1A') {
                    $message_for_player = clienttranslate('${You_must} choose ${number} other top ${card} from the board of ${targetable_players}');
                    $message_for_others = clienttranslate('${player_must} choose ${number} other top ${card} from the board of ${targetable_players}');
                } else if ($code === '134N1+A' || $code === '134N1B') {
                    $message_for_player = clienttranslate('${You_must} choose a pile to splay left from the board of ${targetable_players}');
                    $message_for_others = clienttranslate('${player_must} choose a pile to splay left from the board of ${targetable_players}');
                } else if ($code === '136N1B' || $code === '161N1A') {
                    $message_for_player = clienttranslate('${You_must} choose a top card to execute from the board of ${targetable_players}');
                    $message_for_others = clienttranslate('${player_must} choose a top card to execute from the board of ${targetable_players}');
                } else {
                    // This should not happen
                    throw new BgaVisibleSystemException(self::format(self::_("Unhandled case in {function}: '{code}'"), array('function' => 'getTransferInfoWithOnePlayerInvolved()', 'code' => $location_from . '->' . $location_to)));
                }
                break;
            default:
                // This should not happen
                throw new BgaVisibleSystemException(self::format(self::_("Unhandled case in {function}: '{code}'"), array('function' => 'getTransferInfoWithOnePlayerInvolved()', 'code' => $location_from . '->' . $location_to)));
                break;
            }
        } else {
            switch($location_from . '->' . $location_to) { 
            case 'achievements->achievements':
                if ($player_id_is_owner_from) {
                    $message_for_player = clienttranslate('${You_must} return ${number} ${card} to the available achievements');
                    $message_for_others = clienttranslate('${player_must} return ${number} ${card} to the available achievements');
                } else {
                    $message_for_player = clienttranslate('${You_must} claim ${number} ${card} from the available achievements');
                    $message_for_others = clienttranslate('${player_must} claim ${number} ${card} from the available achievements');
                }
                break;
            case 'achievements->deck':
                $message_for_player = clienttranslate('${You_must} return ${number} ${card} from your achievements');
                $message_for_others = clienttranslate('${player_must} return ${number} ${card} from his achievements');
                break;
            case 'hand->deck':
                if ($bottom_to) {
                    $message_for_player = clienttranslate('${You_must} return ${number} ${card} from your hand');
                    $message_for_others = clienttranslate('${player_must} return ${number} ${card} from his hand');
                } else {
                    $message_for_player = clienttranslate('${You_must} topdeck ${number} ${card} from your hand');
                    $message_for_others = clienttranslate('${player_must} topdeck ${number} ${card} from his hand');
                }
                break;
            case 'hand->board':
                if ($bottom_to) {
                    $message_for_player = clienttranslate('${You_must} tuck ${number} ${card} from your hand');
                    $message_for_others = clienttranslate('${player_must} tuck ${number} ${card} from his hand');
                }
                else {
                    $message_for_player = clienttranslate('${You_must} meld ${number} ${card} from your hand');
                    $message_for_others = clienttranslate('${player_must} meld ${number} ${card} from his hand');
                }
                break;
            case 'hand->score':
                $message_for_player = clienttranslate('${You_must} score ${number} ${card} from your hand');
                $message_for_others = clienttranslate('${player_must} score ${number} ${card} from his hand');
                break;
            case 'hand->revealed':
                $message_for_player = clienttranslate('${You_must} reveal ${number} ${card} from your hand');
                $message_for_others = clienttranslate('${player_must} reveal ${number} ${card} from his hand');
                break;
            case 'hand->achievements':
                $message_for_player = clienttranslate('${You_must} achieve ${number} ${card} from your hand');
                $message_for_others = clienttranslate('${player_must} achieve ${number} ${card} from his hand');
                break;
            case 'board->deck':
                $message_for_player = clienttranslate('${You_must} return ${number} top ${card} from your board');
                $message_for_others = clienttranslate('${player_must} return ${number} top ${card} from his board');
                break;
            case 'board->hand':
                $message_for_player = clienttranslate('${You_must} take back ${number} top ${card} from your board to your hand');
                $message_for_others = clienttranslate('${player_must} take back ${number} top ${card} from his board to his hand');
                break;
            case 'board->score':
                $message_for_player = clienttranslate('${You_must} score ${number} top ${card} from your board');
                $message_for_others = clienttranslate('${player_must} score ${number} top ${card} from his board');
                break;
            case 'board->none':
                $message_for_player = clienttranslate('${You_must} choose ${number} top ${card} from your board');
                $message_for_others = clienttranslate('${player_must} choose ${number} top ${card} from his board');
                break;
            case 'board->achievements':
                $message_for_player = clienttranslate('${You_must} achieve ${number} top ${card} from your board');
                $message_for_others = clienttranslate('${player_must} achieve ${number} top ${card} from his board');
                break;
            case 'score->deck':
                $message_for_player = clienttranslate('${You_must} return ${number} ${card} from your score pile');
                $message_for_others = clienttranslate('${player_must} return ${number} ${card} from his score pile');
                break;
            case 'score->hand':
                $message_for_player = clienttranslate('${You_must} transfer ${number} ${card} from your score pile to your hand');
                $message_for_others = clienttranslate('${player_must} transfer ${number} ${card} from his score pile to his hand');
                break;
            case 'score->board':
                if ($bottom_to) {
                    $message_for_player = clienttranslate('${You_must} tuck ${number} ${card} from your score pile');
                    $message_for_others = clienttranslate('${player_must} tucks ${number} ${card} from his score pile');
                }
                else {
                    $message_for_player = clienttranslate('${You_must} meld ${number} ${card} from your score pile');
                    $message_for_others = clienttranslate('${player_must} meld ${number} ${card} from his score pile');
                }
                break;
            case 'score->achievements':
                $message_for_player = clienttranslate('${You_must} achieve ${number} ${card} from your score pile');
                $message_for_others = clienttranslate('${player_must} achieve ${number} ${card} from his score pile');
                break;
            case 'revealed->deck':
                $message_for_player = clienttranslate('${You_must} return ${number} ${card} you revealed');
                $message_for_others = clienttranslate('${player_must} return ${number} ${card} he revealed');
                break;
            case 'revealed->board':
                $message_for_player = clienttranslate('${You_must} meld ${number} ${card} you revealed');
                $message_for_others = clienttranslate('${player_must} meld ${number} ${card} he revealed');
                break;
            case 'revealed->score':
                $message_for_player = clienttranslate('${You_must} score ${number} ${card} you revealed');
                $message_for_others = clienttranslate('${player_must} score ${number} ${card} he revealed');
                break;
            case 'revealed,hand->deck': // Alchemy, Physics
                $message_for_player = clienttranslate('${You_must} return ${number} ${card} you revealed and ${number} ${card} in your hand');
                $message_for_others = clienttranslate('${player_must} return ${number} ${card} he revealed and ${number} ${card} in his hand');
                break;
            case 'revealed,score->deck':
                $message_for_player = clienttranslate('${You_must} return ${number} ${card} you revealed and ${number} ${card} from your score pile');
                $message_for_others = clienttranslate('${player_must} return ${number} ${card} he revealed and ${number} ${card} from his score pile');
                break;
            case 'hand->revealed,deck': // Measurement
                $message_for_player = clienttranslate('${You_must} reveal and return ${number} ${card} from your hand');
                $message_for_others = clienttranslate('${player_must} reveal and return ${number} ${card} from his hand');
                break;
            case 'pile->deck': // Skyscrapers
                $message_for_player = clienttranslate('${You_must} return ${number} ${card} from your board');
                $message_for_others = clienttranslate('${player_must} return ${number} ${card} from his board');
                break;
            default:
                // This should not happen
                throw new BgaVisibleSystemException(self::format(self::_("Unhandled case in {function}: '{code}'"), array('function' => 'getTransferInfoWithOnePlayerInvolved()', 'code' => $location_from . '->' . $location_to)));
                break;
            }
        }

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
                    'card' => $cards,
                    'targetable_players' => $targetable_players,
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
                    'card' => $cards,
                    'targetable_players' => $targetable_players,
                ],
            ],
        ];
    }
    
    function notifyWithTwoPlayersInvolved($card, $transferInfo, $progressInfo) {
        // [*] ATTENTION: when modifiing, modify getTransferInfoWithTwoPlayersInvolved at the same time 
        $owner_from = $transferInfo['owner_from'];
        $owner_to = $transferInfo['owner_to'];
        
        $location_from = $transferInfo['location_from'];
        $location_to = $transferInfo['location_to'];
        
        $player_id = $transferInfo['player_id'];
        
        if ($owner_from == 0 || $owner_to == 0) { // Used only for Rocketry or Fission or Mass media
            switch($location_from . '->' . $location_to) {
            case 'score->deck':
                $message_for_player = clienttranslate('${You} return a ${<}${age}${>} from ${opponent_name}\'s score pile.');
                $message_for_opponent = clienttranslate('${player_name} returns ${<}${age}${>} ${<<}${name}${>>} from ${your} score pile.');
                $message_for_others = clienttranslate('${player_name} returns a ${<}${age}${>} from ${opponent_name}\'s score pile.');
                break;
            case 'score->relics':
                $message_for_player = clienttranslate('${You} return ${<}${age}${>} ${<<}${name}${>>} from ${opponent_name}\'s score pile.');
                $message_for_opponent = clienttranslate('${player_name} returns ${<}${age}${>} ${<<}${name}${>>} from ${your} score pile.');
                $message_for_others = clienttranslate('${player_name} returns ${<}${age}${>} ${<<}${name}${>>} from ${opponent_name}\'s score pile.');
                break;

            case 'board->deck':
            case 'board->relics':
                $message_for_player = clienttranslate('${You} return ${<}${age}${>} ${<<}${name}${>>} from ${opponent_name}\'s board.');
                $message_for_opponent = clienttranslate('${player_name} returns ${<}${age}${>} ${<<}${name}${>>} from ${your} board.');
                $message_for_others = clienttranslate('${player_name} returns ${<}${age}${>} ${<<}${name}${>>} from ${opponent_name}\'s board.');
                break;

            default:
                // This should not happen
                throw new BgaVisibleSystemException(self::format(self::_("Unhandled case in {function}: '{code}'"), array('function' => 'notifyWithTwoPlayersInvolved()', 'code' => $location_from . '->' . $location_to)));
                break;
            }
        }        
        else if ($player_id == $owner_from) {
            switch($location_from . '->' . $location_to) {
            case 'hand->hand':
                $message_for_player = clienttranslate('${You} transfer ${<}${age}${>} ${<<}${name}${>>} from your hand to ${opponent_name}\'s hand.');
                $message_for_opponent = clienttranslate('${player_name} transfers ${<}${age}${>} ${<<}${name}${>>} from his hand to ${your} hand.');
                $message_for_others = clienttranslate('${player_name} transfers a ${<}${age}${>} from his hand to ${opponent_name}\'s hand.');
                break;
                
            case 'hand->score':
                $message_for_player = clienttranslate('${You} transfer ${<}${age}${>} ${<<}${name}${>>} from your hand to ${opponent_name}\'s score pile.');
                $message_for_opponent = clienttranslate('${player_name} transfers ${<}${age}${>} ${<<}${name}${>>} from his hand to ${your} score pile.');
                $message_for_others = clienttranslate('${player_name} transfers a ${<}${age}${>} from his hand to ${opponent_name}\'s score pile.');
                break;
            
            case 'hand->board':
                $message_for_player = clienttranslate('${You} transfer ${<}${age}${>} ${<<}${name}${>>} from your hand to ${opponent_name}\'s board.');
                $message_for_opponent = clienttranslate('${player_name} transfers ${<}${age}${>} ${<<}${name}${>>} from his hand to ${your} board.');
                $message_for_others = clienttranslate('${player_name} transfers ${<}${age}${>} ${<<}${name}${>>} from his hand to ${opponent_name}\'s board.');
                break;
                
            case 'hand->achievements':
                $message_for_player = clienttranslate('${You} transfer ${<}${age}${>} ${<<}${name}${>>} from your hand to ${opponent_name}\'s achievements.');
                $message_for_opponent = clienttranslate('${player_name} transfers a ${<}${age}${>} from his hand to ${your} achievements.');
                $message_for_others = clienttranslate('${player_name} transfers a ${<}${age}${>} from his hand to ${opponent_name}\'s achievements.');
                break;
                
            case 'board->board':
                $message_for_player = clienttranslate('${You} transfer ${<}${age}${>} ${<<}${name}${>>} from your board to ${opponent_name}\'s board.');
                $message_for_opponent = clienttranslate('${player_name} transfers ${<}${age}${>} ${<<}${name}${>>} from his board to ${your} board.');
                $message_for_others = clienttranslate('${player_name} transfers ${<}${age}${>} ${<<}${name}${>>} from his board to ${opponent_name}\'s board.');
                break;
                
            case 'board->hand':
                $message_for_player = clienttranslate('${You} transfer ${<}${age}${>} ${<<}${name}${>>} from your board to ${opponent_name}\'s hand.');
                $message_for_opponent = clienttranslate('${player_name} transfers ${<}${age}${>} ${<<}${name}${>>} from his board to ${your} hand.');
                $message_for_others = clienttranslate('${player_name} transfers ${<}${age}${>} ${<<}${name}${>>} from his board to ${opponent_name}\'s hand.');
                break;
        
            case 'board->score':
                $message_for_player = clienttranslate('${You} transfer ${<}${age}${>} ${<<}${name}${>>} from your board to ${opponent_name}\'s score pile.');
                $message_for_opponent = clienttranslate('${player_name} transfers ${<}${age}${>} ${<<}${name}${>>} from his board to ${your} score pile.');
                $message_for_others = clienttranslate('${player_name} transfers ${<}${age}${>} ${<<}${name}${>>} from his board to ${opponent_name}\'s score pile.');
                break;
            
            case 'board->achievements':
                $message_for_player = clienttranslate('${You} transfer ${<}${age}${>} ${<<}${name}${>>} from your board to ${opponent_name}\'s achievements.');
                $message_for_opponent = clienttranslate('${player_name} transfers ${<}${age}${>} ${<<}${name}${>>} from his board to ${your} achievements.');
                $message_for_others = clienttranslate('${player_name} transfers a${<}${age}${>} ${<<}${name}${>>} from his board to ${opponent_name}\'s achievements.');
                break;

            case 'score->hand':
                $message_for_player = clienttranslate('${You} transfer ${<}${age}${>} ${<<}${name}${>>} from your score pile to ${opponent_name}\'s hand.');
                $message_for_opponent = clienttranslate('${player_name} transfers ${<}${age}${>} ${<<}${name}${>>} from his score pile to ${your} hand.');
                $message_for_others = clienttranslate('${player_name} transfers a ${<}${age}${>} from his score pile to ${opponent_name}\'s hand.');
                break;
                
            case 'score->score':
                $message_for_player = clienttranslate('${You} transfer ${<}${age}${>} ${<<}${name}${>>} from your score pile to ${opponent_name}\'s score pile.');
                $message_for_opponent = clienttranslate('${player_name} transfers ${<}${age}${>} ${<<}${name}${>>} from his score pile to ${your} score pile.');
                $message_for_others = clienttranslate('${player_name} transfers a ${<}${age}${>} from his score pile to ${opponent_name}\'s score pile.');
                break;

            case 'score->achievements':
                $message_for_player = clienttranslate('${You} transfer ${<}${age}${>} ${<<}${name}${>>} from your score pile to ${opponent_name}\'s achievements.');
                $message_for_opponent = clienttranslate('${player_name} transfers a ${<}${age}${>} from his score pile to ${your} achievements.');
                $message_for_others = clienttranslate('${player_name} transfers a ${<}${age}${>} from his score pile to ${opponent_name}\'s achievements.');
                break;     

            case 'revealed->achievements':
                $message_for_player = clienttranslate('${You} transfer ${<}${age}${>} ${<<}${name}${>>} to ${opponent_name}\'s achievements.');
                $message_for_opponent = clienttranslate('${player_name} transfers ${<}${age}${>} ${<<}${name}${>>} to ${your} achievements.');
                $message_for_others = clienttranslate('${player_name} transfers ${<}${age}${>} ${<<}${name}${>>} to ${opponent_name}\'s achievements.');
                break;
                
            case 'achievements->achievements':
                $message_for_player = clienttranslate('${You} transfer a ${<}${age}${>} from your achievements to ${opponent_name}\'s achievements.');
                $message_for_opponent = clienttranslate('${player_name} transfers a ${<}${age}${>} from his achievements to ${your} achievements.');
                $message_for_others = clienttranslate('${player_name} transfers a ${<}${age}${>} from his achievements to ${opponent_name}\'s achievements.');
                break;
                
            case 'revealed->board':
                $message_for_player = clienttranslate('${You} transfer ${<}${age}${>} ${<<}${name}${>>} to ${opponent_name}\'s board.');
                $message_for_opponent = clienttranslate('${player_name} transfers ${<}${age}${>} ${<<}${name}${>>} to ${your} board.');
                $message_for_others = clienttranslate('${player_name} transfers ${<}${age}${>} ${<<}${name}${>>} to ${opponent_name}\'s board.');
                break;

            default:
                // This should not happen
                throw new BgaVisibleSystemException(self::format(self::_("Unhandled case in {function}: '{code}'"), array('function' => 'notifyWithTwoPlayersInvolved()', 'code' => $location_from . '->' . $location_to)));
                break;
            }
        }
        else { // $transferInfo['player_id'] == $transferInfo['owner_to']
            switch($location_from . '->' . $location_to) {
            case 'hand->hand':
                $message_for_player = clienttranslate('${You} transfer ${<}${age}${>} ${<<}${name}${>>} from ${opponent_name}\'s hand to your hand.');
                $message_for_opponent = clienttranslate('${player_name} transfers ${<}${age}${>} ${<<}${name}${>>} from ${your} hand to his hand.');
                $message_for_others = clienttranslate('${player_name} transfers a ${<}${age}${>} from ${opponent_name}\'s hand to his hand.');
                break;

            case 'hand->achievements':
                $message_for_player = clienttranslate('${You} transfer a ${<}${age}${>} from ${opponent_name}\'s hand to your achievements.');
                $message_for_opponent = clienttranslate('${player_name} transfers ${<}${age}${>} ${<<}${name}${>>} from ${your} hand to his achievements.');
                $message_for_others = clienttranslate('${player_name} transfers a ${<}${age}${>} from ${opponent_name}\'s hand to his achievements.');
                break;  
            
            case 'board->board':
                $message_for_player = clienttranslate('${You} transfer ${<}${age}${>} ${<<}${name}${>>} from ${opponent_name}\'s board to your board.');
                $message_for_opponent = clienttranslate('${player_name} transfers ${<}${age}${>} ${<<}${name}${>>} from ${your} board to his board.');
                $message_for_others = clienttranslate('${player_name} transfers ${<}${age}${>} ${<<}${name}${>>} from ${opponent_name}\'s board to his board.');
                break;
                
            case 'board->hand':
                $message_for_player = clienttranslate('${You} transfer ${<}${age}${>} ${<<}${name}${>>} from ${opponent_name}\'s board to your hand.');
                $message_for_opponent = clienttranslate('${player_name} transfers ${<}${age}${>} ${<<}${name}${>>} from ${your} board to his hand.');
                $message_for_others = clienttranslate('${player_name} transfers ${<}${age}${>} ${<<}${name}${>>} from ${opponent_name}\'s board to his hand.');
                break;    

            case 'board->achievements':
                $message_for_player = clienttranslate('${You} transfer ${<}${age}${>} ${<<}${name}${>>} from ${opponent_name}\'s board to your achievements.');
                $message_for_opponent = clienttranslate('${player_name} transfers ${<}${age}${>} ${<<}${name}${>>} from ${your} board to his achievements.');
                $message_for_others = clienttranslate('${player_name} transfers ${<}${age}${>} ${<<}${name}${>>} from ${opponent_name}\'s board to his achievements.');
                break;  

            case 'board->score':
                $message_for_player = clienttranslate('${You} transfer ${<}${age}${>} ${<<}${name}${>>} from ${opponent_name}\'s board to your score pile.');
                $message_for_opponent = clienttranslate('${player_name} transfers ${<}${age}${>} ${<<}${name}${>>} from ${your} board to his score pile.');
                $message_for_others = clienttranslate('${player_name} transfers ${<}${age}${>} ${<<}${name}${>>} from ${opponent_name}\'s board to his score pile.');
                break;

            case 'display->board':
                $message_for_player = clienttranslate('${You} transfer ${<}${age}${>} ${<<}${name}${>>} from ${opponent_name}\'s display to your board.');
                $message_for_opponent = clienttranslate('${player_name} transfers ${<}${age}${>} ${<<}${name}${>>} from ${your} display to his board.');
                $message_for_others = clienttranslate('${player_name} transfers ${<}${age}${>} ${<<}${name}${>>} from ${opponent_name}\'s display to his board.');
                break;
                
            case 'revealed->board': // Collaboration
                $message_for_player = clienttranslate('${You} transfer ${<}${age}${>} ${<<}${name}${>>} to your board.');
                $message_for_opponent = clienttranslate('${player_name} transfers ${<}${age}${>} ${<<}${name}${>>} to his board.');
                $message_for_others = clienttranslate('${player_name} transfers ${<}${age}${>} ${<<}${name}${>>} to his board.');
                break;
                
            case 'revealed->achievements':
                $message_for_player = clienttranslate('${You} achieve ${<}${age}${>} ${<<}${name}${>>}.');
                $message_for_others = clienttranslate('${player_name} achieves ${<}${age}${>} ${<<}${name}${>>}.');
                break;

            case 'achievements->achievements':
                $message_for_player = clienttranslate('${You} seize the ${<}${age}${>} relic from ${opponent_name}\'s achievements to your achievements.');
                $message_for_opponent = clienttranslate('${player_name} seizes the ${<}${age}${>} relic from ${your} achievements to his achievements.');
                $message_for_others = clienttranslate('${player_name} seizes the ${<}${age}${>} relic from ${opponent_name}\'s achievements to his achievements.');
                break;

            case 'achievements->hand':
                $message_for_player = clienttranslate('${You} seize ${<}${age}${>} ${<<}${name}${>>} from ${opponent_name}\'s achievements to your hand.');
                $message_for_opponent = clienttranslate('${player_name} seizes the ${<}${age}${>} relic from ${your} achievements to his hand.');
                $message_for_others = clienttranslate('${player_name} seizes the ${<}${age}${>} relic from ${opponent_name}\'s achievements to his hand.');
                break;
                
            default:
                // This should not happen
                throw new BgaVisibleSystemException(self::format(self::_("Unhandled case in {function}: '{code}'"), array('function' => 'notifyWithTwoPlayersInvolved()', 'code' => $location_from . '->' . $location_to)));
                break;
            }
        }
        
        self::sendNotificationWithTwoPlayersInvolved($message_for_player, $message_for_opponent, $message_for_others, $card, $transferInfo, $progressInfo);
    }
        
    function getTransferInfoWithTwoPlayersInvolved($location_from, $location_to, $player_id_is_owner_from, $you_must, $player_must, $your, $player_name, $opponent_name, $number, $cards) {
        // [*] ATTENTION: when modifying, modify notifyWithTwoPlayersInvolved at the same time
        if ($player_id_is_owner_from) {
            switch($location_from . '->' . $location_to) {
            case 'hand->hand':
                $message_for_player = clienttranslate('${You_must} transfer ${number} ${card} from your hand to ${opponent_name}\'s hand');
                $message_for_opponent = clienttranslate('${player_must} transfer ${number} ${card} from his hand to ${your} hand');
                $message_for_others = clienttranslate('${player_must} transfer ${number} ${card} from his hand to ${opponent_name}\'s hand');
                break;
                
            case 'hand->score':
                $message_for_player = clienttranslate('${You_must} transfer ${number} ${card} from your hand to ${opponent_name}\'s score pile');
                $message_for_opponent = clienttranslate('${player_must} transfer ${number} ${card} from his hand to ${your} score pile');
                $message_for_others = clienttranslate('${player_must} transfer ${number} ${card} from his hand to ${opponent_name}\'s score pile');
                break;
                
            case 'hand->achievements':
                $message_for_player = clienttranslate('${You_must} transfer ${number} ${card} from your hand to ${opponent_name}\'s achievements');
                $message_for_opponent = clienttranslate('${player_must} transfer ${number} ${card} from his hand to ${your} achievements');
                $message_for_others = clienttranslate('${player_must} transfer ${number} ${card} from his hand to ${opponent_name}\'s achievements');
                break;              
                
            case 'board->board':
                $message_for_player = clienttranslate('${You_must} transfer ${number} top ${card} from your board to ${opponent_name}\'s board');
                $message_for_opponent = clienttranslate('${player_must} transfer ${number} top ${card} from his board to ${your} board');
                $message_for_others = clienttranslate('${player_must} transfer ${number} top ${card} from his board to ${opponent_name}\'s board');
                break;
            
            case 'board->hand':
                $message_for_player = clienttranslate('${You_must} transfer ${number} top ${card} from your board to ${opponent_name}\'s hand');
                $message_for_opponent = clienttranslate('${player_must} transfer ${number} top ${card} from his board to ${your} hand');
                $message_for_others = clienttranslate('${player_must} transfer ${number} top ${card} from his board to ${opponent_name}\'s hand');
                break;
                
            case 'board->score':
                $message_for_player = clienttranslate('${You_must} transfer ${number} top ${card} from your board to ${opponent_name}\'s score pile');
                $message_for_opponent = clienttranslate('${player_must} transfer ${number} top ${card} from his board to ${your} score pile');
                $message_for_others = clienttranslate('${player_must} transfer ${number} top ${card} from his board to ${opponent_name}\'s score pile');
                break;

            case 'board->achievements':
                $message_for_player = clienttranslate('${You_must} transfer ${number} top ${card} from your board to ${opponent_name}\'s achievements');
                $message_for_opponent = clienttranslate('${player_must} transfer ${number} top ${card} from his board to ${your} achievements');
                $message_for_others = clienttranslate('${player_must} transfer ${number} top ${card} from his board to ${opponent_name}\'s achievements');
                break;
                
            case 'score->score':
                $message_for_player = clienttranslate('${You_must} transfer ${number} ${card} from your score pile to ${opponent_name}\'s score pile');
                $message_for_opponent = clienttranslate('${player_must} transfer ${number} ${card} from his score pile to ${your} score pile');
                $message_for_others = clienttranslate('${player_must} transfer ${number} ${card} from his score pile to ${opponent_name}\'s score pile');
                break;
            
            case 'achievements->achievements':
                $message_for_player = clienttranslate('${You_must} transfer ${number} ${card} from your achievements to ${opponent_name}\'s achievements');
                $message_for_opponent = clienttranslate('${player_must} transfer ${number} ${card} from his achievements to ${your} achievements');
                $message_for_others = clienttranslate('${player_must} transfer ${number} ${card} from his achievements to ${opponent_name}\'s achievements');
                break;

            case 'revealed->board':
                $message_for_player = clienttranslate('${You_must} transfer ${number} ${card} to ${opponent_name}\'s board');
                $message_for_opponent = clienttranslate('${player_must} transfer ${number} ${card} to ${your} board');
                $message_for_others = clienttranslate('${player_must} transfer ${number} ${card} to ${opponent_name}\'s board');
                break;

            default:
                // This should not happen
                throw new BgaVisibleSystemException(self::format(self::_("Unhandled case in {function}: '{code}'"), array('function' => 'getTransferInfoWithTwoPlayersInvolved()', 'code' => $location_from . '->' . $location_to)));
                break;
            }
        }
        else { // $player_id_is_owner_to
            switch($location_from . '->' . $location_to) {
            case 'board->board':
                $message_for_player = clienttranslate('${You_must} transfer ${number} top ${card} from {opponent_name}\'s board to your board');
                $message_for_opponent = clienttranslate('${player_must} transfer ${number} top ${card} from ${your} board to his board');
                $message_for_others = clienttranslate('${player_must} transfer ${number} top ${card} from {opponent_name}\'s board to his board');
                break;
                
            case 'board->hand':
                $message_for_player = clienttranslate('${You_must} transfer ${number} top ${card} from {opponent_name}\'s board to your hand');
                $message_for_opponent = clienttranslate('${player_must} transfer ${number} top ${card} from ${your} board to his hand');
                $message_for_others = clienttranslate('${player_must} transfer ${number} top ${card} from {opponent_name}\'s board to his hand');
                break;
            
            case 'revealed->board': // Collaboration
                $message_for_player = clienttranslate('${You_must} transfer ${number} revealed ${card} to your board.');
                $message_for_opponent = clienttranslate('${player_must} transfer ${number} revealed ${card} to his board.');
                $message_for_others = clienttranslate('${player_must} transfer ${number} revealed ${card} to his board.');
                break;
            
            default:
                // This should not happen
                throw new BgaVisibleSystemException(self::format(self::_("Unhandled case in {function}: '{code}'"), array('function' => 'getTransferInfoWithTwoPlayersInvolved()', 'code' => $location_from . '->' . $location_to)));
                break;
            }
        }

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
                    'card' => $cards,
                    'opponent_name' => $opponent_name,
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
                    'card' => $cards,
                    'your' => $your,
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
                    'card' => $cards,
                    'opponent_name' => $opponent_name,
                ],
            ],
        ];
    }
    
    function sendNotificationWithOnePlayerInvolved($message_for_player, $message_for_others, $card, $transferInfo, $progressInfo) {     
        $player_id = $transferInfo['player_id'];
        $player_name = self::getPlayerNameFromId($player_id);
        
        $info = array_merge($transferInfo, $progressInfo);
        
        // Information to attach to the involved player
        $delimiters_for_player = self::getDelimiterMeanings($message_for_player, $card['id']);
        $notif_args_for_player = array_merge($info, $delimiters_for_player);
        $notif_args_for_player['You'] = 'You';
        // Visibility for involved player
        if (array_key_exists('<<', $delimiters_for_player)) {
            // The player can see the front of the card
            $notif_args_for_player['i18n'] = array('name');
            $notif_args_for_player['name'] = self::getCardName($card['id']);
            // TODO(LATER): We should stop sending the card's properties which aren't actually used.
            $notif_args_for_player = array_merge($notif_args_for_player, $card);
        } else if (array_key_exists('<<<', $delimiters_for_player)) {
            $notif_args_for_player['i18n'] = array('achievement_name');
            $notif_args_for_player['age'] = $card['age'];
            $notif_args_for_player['type'] = $card['type'];
            $notif_args_for_player['is_relic'] = $card['is_relic'];
            if ($card['age'] === null) {
                // The player can see the front of the card because it is a special achievement
                $notif_args_for_player['id'] = $card['id'];
                $notif_args_for_player['achievement_name'] = self::getAchievementCardName($card['id']);
            } else {
                // The player can't see the front of the card
                $notif_args_for_player['achievement_name'] = self::getNormalAchievementName($card['age']);
            }
        } else {
            // The player can't see the front of the card
            $notif_args_for_player['age'] = $card['age'];
            $notif_args_for_player['type'] = $card['type'];
            $notif_args_for_player['is_relic'] = $card['is_relic'];
        }
        
        // Information to attach to others (other players and spectators)
        $delimiters_for_others = self::getDelimiterMeanings($message_for_others, $card['id']);
        $notif_args_for_others = array_merge($info, $delimiters_for_others);
        $notif_args_for_others['player_name'] =  $player_name; // The color in the log will be defined automatically by the system
        
        // Visibility for others
        if (array_key_exists('<<', $delimiters_for_others)) {
            // Other players can see the front of the card
            $notif_args_for_others['i18n'] = array('name');
            $notif_args_for_others['name'] = self::getCardName($card['id']);
            // TODO(LATER): We should stop sending the card's properties which aren't actually used.
            $notif_args_for_others = array_merge($notif_args_for_others, $card);
        } else if (array_key_exists('<<<', $delimiters_for_others)) {
            $notif_args_for_others['i18n'] = array('achievement_name');
            $notif_args_for_others['age'] = $card['age'];
            $notif_args_for_others['type'] = $card['type'];
            $notif_args_for_others['is_relic'] = $card['is_relic'];
            if ($card['age'] === null) {
                // Other players can see the front of the card because it is a special achievement
                $notif_args_for_others['id'] = $card['id'];
                $notif_args_for_others['achievement_name'] = self::getAchievementCardName($card['id']);
            } else {
                // Other players can't see the front of the card
                $notif_args_for_others['achievement_name'] = self::getNormalAchievementName($card['age']);
            }
        } else {
            // Other players can't see the front of the card
            $notif_args_for_others['age'] = $card['age'];
            $notif_args_for_others['type'] = $card['type'];
            $notif_args_for_others['is_relic'] = $card['is_relic'];
        }
        
        self::notifyPlayer($player_id, "transferedCard", $message_for_player, $notif_args_for_player);
        self::notifyAllPlayersBut($player_id, "transferedCard", $message_for_others, $notif_args_for_others);
    }
    
    function sendNotificationWithTwoPlayersInvolved($message_for_player, $message_for_opponent, $message_for_others, $card, $transferInfo, $progressInfo) {
        $player_id = $transferInfo['player_id'];
        $player_name = self::getPlayerNameFromId($player_id);
        $opponent_id = $transferInfo['opponent_id'];
        $opponent_name = self::getPlayerNameFromId($opponent_id);
        
        $info = array_merge($transferInfo, $progressInfo);
        
        // Information to attach to the player
        $delimiters_for_player = self::getDelimiterMeanings($message_for_player, $card['id']);
        // TODO(LATER): We should stop sending the card's properties which aren't actually used.
        $notif_args_for_player = array_merge($info, $delimiters_for_player, $card); // The player can always see the card
        $notif_args_for_player['i18n'] = array('name');
        $notif_args_for_player['name'] = self::getCardName($card['id']);
        $notif_args_for_player['You'] =  'You';
        $notif_args_for_player['your'] = 'your';
        $notif_args_for_player['opponent_name'] =  self::getColoredText($opponent_name, $opponent_id);
        
        // Information to attach to the opponent
        $delimiters_for_opponent = self::getDelimiterMeanings($message_for_opponent, $card['id']);
        // TODO(LATER): We should stop sending the card's properties which aren't actually used.
        $notif_args_for_opponent = array_merge($info, $delimiters_for_opponent, $card); // The opponent can always see the card
        $notif_args_for_opponent['i18n'] = array('name');
        $notif_args_for_opponent['name'] = self::getCardName($card['id']);
        $notif_args_for_opponent['You'] = 'You';
        $notif_args_for_opponent['your'] = 'your';
        $notif_args_for_opponent['player_name'] =  $player_name;  // The color in the log will be defined automatically by the system
        
        // Information to attach to others (other players and spectators)
        $delimiters_for_others = self::getDelimiterMeanings($message_for_others, $card['id']);
        $notif_args_for_others = array_merge($info, $delimiters_for_others);
        $notif_args_for_others['player_name'] =  $player_name; // The color in the log will be defined automatically by the system
        $notif_args_for_others['opponent_name'] =  self::getColoredText($opponent_name, $opponent_id);
        
        // Visibility for others
        if (array_key_exists('<<', $delimiters_for_others)) {
            // Other players can see the front of the card
            $notif_args_for_others['i18n'] = array('name');
            $notif_args_for_others['name'] = self::getCardName($card['id']);
            // TODO(LATER): We should stop sending the card's properties which aren't actually used.
            $notif_args_for_others = array_merge($notif_args_for_others, $card);
        } else {
            // Other players can't see the front of the card
            $notif_args_for_others['age'] = $card['age'];
            $notif_args_for_others['type'] = $card['type'];
            $notif_args_for_others['is_relic'] = $card['is_relic'];
        }
        
        self::notifyPlayer($player_id, "transferedCard", $message_for_player, $notif_args_for_player);
        self::notifyPlayer($opponent_id, "transferedCard", $message_for_opponent, $notif_args_for_opponent);
        self::notifyAllPlayersBut(array($player_id, $opponent_id), "transferedCard", $message_for_others, $notif_args_for_others);
    }
    
    function getActivePlayerIdsInTurnOrderStartingWithCurrentPlayer() {
        $players = self::getCollectionFromDB("SELECT player_no, player_id, player_eliminated FROM player");
        $num_players = count($players);

        if (self::getGameStateValue('release_version') >= 1 && self::getGameStateValue('current_nesting_index') < 0) {
            $current_player_id = self::getGameStateValue('active_player');
        } else {
            $current_player_id = self::getCurrentPlayerUnderDogmaEffect();
        }

        $current_player_no =  self::getUniqueValueFromDB(self::format("SELECT player_no FROM player WHERE player_id={current_player_id}", array('current_player_id' => $current_player_id)));

        $player_ids = [];
        for ($i = 0; $i < $num_players; $i++) {
            $current_no = (($current_player_no + $i - 1) % $num_players) + 1;
            if ($players[$current_no]['player_eliminated'] == 0) {
                $player_ids[] = $players[$current_no]['player_id'];
            }
        }
        return $player_ids;
    }

    /** Checks to see if any players are eligible for special achievements. **/
    function checkForSpecialAchievements() {
        // "In the rare case that two players simultaneously become eligible to claim a special achievement,
        // the tie is broken in turn order going clockwise, with the current player winning ties."
        // https://boardgamegeek.com/thread/2710666/simultaneous-special-achievements-tiebreaker
        foreach (self::getActivePlayerIdsInTurnOrderStartingWithCurrentPlayer() as $player_id) {
            self::checkForSpecialAchievementsForPlayer($player_id);
        }
    }
    
    /** Checks if the player meets the conditions to get a special achievement. Do the transfer if he does. **/
    function checkForSpecialAchievementsForPlayer($player_id) {
        // TODO(CITIES,ECHOES): Update this once there are other special achievements to test for.
        $achievements_to_test = array(105, 106, 107, 108, 109);
        $end_of_game = false;
        
        foreach ($achievements_to_test as $achievement_id) {
            $achievement = self::getCardInfo($achievement_id);
            if ($achievement['owner'] != 0) { // Somebody has already claimed that achievement
                // So it's not claimable anymore
                continue;
            }
            
            switch ($achievement_id) {
            case 105: // Empire: three or more icons of all six types
                $eligible = true;
                $ressource_counts = self::getPlayerResourceCounts($player_id);
                foreach ($ressource_counts as $icon => $count) {
                    if ($count < 3) { // There are less than 3 icons
                        $eligible = false;
                        break;
                    }
                }
                break;
            case 106: // Monument: tuck 6 cards or score 6 cards
                $flags = self::getFlagsForMonument($player_id);
                $eligible = $flags['number_of_tucked_cards'] >= 6 || $flags['number_of_scored_cards'] >= 6;
                break;
            case 107: // Wonder: 5 colors, each being splayed right or up
                $eligible = true;
                for($color = 0; $color < 5 ; $color++) {
                    if (self::getCurrentSplayDirection($player_id, $color) < 2) { // This color is missing, unsplayed or splayed left
                        $eligible = false;
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
        if ($splay_direction == 0 && !$force_unsplay) { // Unsplay event
            $color_in_clear = self::getColorInClear($color);

            if ($player_id != $target_player_id) {
                throw new BgaVisibleSystemException(self::format(self::_("Unhandled case in {function}: '{code}'"), array('function' => "notifyForSplay()", 'code' => 'player_id != target_player_id in unsplay event')));
            }

            self::notifyPlayer($player_id, 'splayedPile', clienttranslate('${Your} ${colored} stack is reduced to one card so it loses its splay.'), array(
                'i18n' => array('colored'),
                'Your' => 'Your',
                'colored' => $color_in_clear,
                'player_id' => $player_id,
                'color' => $color,
                'splay_direction' => $splay_direction
            ));
            
            self::notifyAllPlayersBut($player_id, 'splayedPile', clienttranslate('${player_name}\'s ${colored} stack is reduced to one card so it loses its splay.'), array(
                'i18n' => array('colored'),
                'player_name' => self::getPlayerNameFromId($player_id),
                'colored' => $color_in_clear,
                'player_id' => $player_id,
                'color' => $color,
                'splay_direction' => $splay_direction
            ));
            return;
        }
        
        // $splay_direction > 0: actual splay or forced unsplay
        $splay_direction_in_clear = self::getSplayDirectionInClear($splay_direction);
        $colored_cards = self::getColorInClearWithCards($color);
        
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
                'forced_unsplay' => $force_unsplay
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
                'forced_unsplay' => $force_unsplay
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
                'forced_unsplay' => $force_unsplay
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
                'forced_unsplay' => $force_unsplay
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
                'forced_unsplay' => $force_unsplay
            ));

        }
    }
    
    /** Notify end of game **/
    function notifyEndOfGameByAchievements() {
        // Display who won and with how many achievements
        // (There can be weird cases when two players tie or one player get more achievements than needed if two or more special achievements are claimed at the same time)
        $players = self::getCollectionFromDb("SELECT player_id, player_score FROM player");
        $number_of_achievements_needed_to_win = self::getGameStateValue('number_of_achievements_needed_to_win');
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
            if (self::decodeGameType(self::getGameStateValue('game_type')) == 'individual') {
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
        $player_id = self::getGameStateValue('player_who_could_not_draw');
        $age_10 = self::getAgeSquare(10);
        if (self::decodeGameType(self::getGameStateValue('game_type')) == 'individual') {
            self::notifyAllPlayersBut($player_id, "log", clienttranslate('END OF GAME BY SCORE: ${player_name} attempts to draw a card above ${age_10}. The player with the greatest score win.'), array(
                'player_name' => self::getPlayerNameFromId($player_id),
                'age_10' => $age_10
            ));
            
            self::notifyPlayer($player_id, "log", clienttranslate('END OF GAME BY SCORE: ${You} attempt to draw a card above ${age_10}. The player with the greatest score win.'), array(
                'You' => 'You',
                'age_10' => $age_10
            ));
        } else { // Team play
            self::notifyAllPlayersBut($player_id, "log", clienttranslate('END OF GAME BY SCORE: ${player_name} attempts to draw a card above ${age_10}. The team with the greatest combined score win.'), array(
                'player_name' => self::getPlayerNameFromId($player_id),
                'age_10' => $age_10
            ));
            
            self::notifyPlayer($player_id, "log", clienttranslate('END OF GAME BY SCORE: ${You} attempt to draw a card above ${age_10}. The team with the greatest combined score win.'), array(
                'You' => 'You',
                'age_10' => $age_10
            ));
        }
    }
    
    function notifyEndOfGameByDogma() {
        $player_id = self::getGameStateValue('winner_by_dogma');
        if (self::getGameStateValue('release_version') >= 1) {
            $dogma_card_id = self::getCurrentNestedCardState()['card_id'];
        } else {
            $dogma_card_id = self::getGameStateValue('dogma_card_id');
        }

        if (self::decodeGameType(self::getGameStateValue('game_type')) == 'individual') {
            if ($dogma_card_id == 207 /* Exxon Valdez */ ) {
                self::notifyAllPlayersBut($player_id, "log", clienttranslate('END OF GAME BY DOGMA: ${player_name} is the only remaining player. He wins!'), array(
                    'player_name' => self::getPlayerNameFromId($player_id)
                ));
                self::notifyPlayer($player_id, "log", clienttranslate('END OF GAME BY DOGMA: You are the only remaining player. You win!'), array());
            } else {
                self::notifyAllPlayersBut($player_id, "log", clienttranslate('END OF GAME BY DOGMA: ${player_name} meets the victory condition. He wins!'), array(
                    'player_name' => self::getPlayerNameFromId($player_id)
                ));
                self::notifyPlayer($player_id, "log", clienttranslate('END OF GAME BY DOGMA: You meet the victory condition. You win!'), array());
            }
        } else { // Team play
            $teammate_id = self::getPlayerTeammate($player_id);
            $winning_team = array($player_id, $teammate_id);
            
            if ($dogma_card_id == 207 /* Exxon Valdez */ ) {
                self::notifyAllPlayersBut($winning_team, "log", clienttranslate('END OF GAME BY DOGMA: The other team is the only remaining team. They win!'), array());
                self::notifyPlayer($player_id, "log", clienttranslate('END OF GAME BY DOGMA: You are the only remaining team. You win!'), array());
                self::notifyPlayer($teammate_id, "log", clienttranslate('END OF GAME BY DOGMA: You are the only remaining team. You win!'), array());
            } else if ($dogma_card_id == 100 /* Self service*/ || $dogma_card_id == 101 /* Globalization */) {
                self::notifyAllPlayersBut($winning_team, "log", clienttranslate('END OF GAME BY DOGMA: The other team meets the victory condition. They win!'), array());
                self::notifyPlayer($player_id, "log", clienttranslate('END OF GAME BY DOGMA: Your team meets the victory condition. You win!'), array());
                self::notifyPlayer($teammate_id, "log", clienttranslate('END OF GAME BY DOGMA: Your team meets the victory condition. You win!'), array());
            } else {
                self::notifyAllPlayersBut($winning_team, "log", clienttranslate('END OF GAME BY DOGMA: ${player_name} meets the victory condition. The other team wins!'), array(
                    'player_name' => self::getPlayerNameFromId($player_id)
                ));
                self::notifyPlayer($player_id, "log", clienttranslate('END OF GAME BY DOGMA: You meet the victory condition. Your team wins!'), array());
                self::notifyPlayer($teammate_id, "log", clienttranslate('END OF GAME BY DOGMA: ${player_name} meets the victory condition. Your team wins!'), array(
                    'player_name' => self::getPlayerNameFromId($player_id)
                ));
            }
        }
    }
    
    /** Notify general info **/
    function notifyGeneralInfo($message, $args = array()) {
        $delimiters = self::getDelimiterMeanings($message);
        self::notifyAll('log', $message, array_merge($args, $delimiters));
    }
    
    /** This function should be called whenever something changes in the game **/
    function recordThatChangeOccurred() {
        
        if (self::getGameStateValue('release_version') >= 1) {
            $nested_card_state = self::getCurrentNestedCardState();
            if ($nested_card_state == null) {
                return;
            }
            $current_effect_type = $nested_card_state['current_effect_type'];
            $current_player_under_dogma_effect = $nested_card_state['current_player_id'];

            // Tell all currently executing "The Big Bang" cards that the game state has changed.
            self::DbQuery("UPDATE nested_card_execution SET auxiliary_value = 1 WHERE card_id = 203");
        } else {
            $current_effect_type = self::getGameStateValue('current_effect_type');
            if ($current_effect_type == -1) { // Not in dogma
                // Nothing to be done
                return;
            }
            $current_player_under_dogma_effect = self::getGameStateValue('current_player_under_dogma_effect');
        }
        
        // Mark that the player under effect made a change in the game
        self::markExecutingPlayer($current_player_under_dogma_effect);
        
        if (self::getGameStateValue('sharing_bonus') != 0) { // The sharing bonus is already on
            return;
        }
        
        // Check if this change triggers a sharing bonus
        $player_who_launched_the_dogma = self::getGameStateValue('active_player');
        if ($current_effect_type == 1 && $current_player_under_dogma_effect <> $player_who_launched_the_dogma && self::getPlayerTeammate($current_player_under_dogma_effect) <> $player_who_launched_the_dogma) {
            // This transfer took place during a non-demand effect, another player (not in the same team) sharing this effect
            // There is a sharing bonus
            self::setGameStateValue('sharing_bonus', 1);
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
    
    // NOTE: This is only executed by a deprecated code path.
    function getExecutingPlayers() {
        return self::getCollectionFromDB("
            SELECT
                player_id, stronger_or_equal
            FROM
                player
            WHERE
                effects_had_impact IS TRUE
        
        ", true);
    }
    
    function resetPlayerTable() {
        self::DbQuery("
            UPDATE
                player
            SET
                stronger_or_equal = NULL,
                featured_icon_count = NULL,
                effects_had_impact = FALSE
        ");
    }
    
    /** Notification system for dogma **/
    function getIconSquare($icon) {
        switch ($icon) {
        case 0:
            $title='';
            break;
        case 1:
            $title=clienttranslate('crown');
            break;
        case 2:
            $title=clienttranslate('leaf');
            break;
        case 3:
            $title=clienttranslate('light bulb');
            break;
        case 4:
            $title=clienttranslate('tower');
            break;
        case 5:
            $title=clienttranslate('factory');
            break;
        case 6:
            $title=clienttranslate('clock');
            break;
        }
        
        return self::format("<span title='{title}' class='square N icon_{icon}'></span>", array('icon' => $icon, 'title' => $title));
    }
    
    function getAgeSquare($age) {
        return self::format("<span title='{age}' class='square N age age_{age}'>{age}</span>", array('age' => $age));
    }
    
    function notifyDogma($card) {
        $player_id = self::getActivePlayerId();
        $card_id = $card['id'];
        
        $message_for_player = clienttranslate('${You} activate the dogma of ${card} with ${[}${icon}${]} as the featured icon.');
        $message_for_others = clienttranslate('${player_name} activates the dogma of ${card} with ${[}${icon}${]} as the featured icon.');
        
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
        self::notifyPlayer($player_id, 'log', clienttranslate('<span class="minor_information">${You} have to execute the ${qualified_effect}.</span>'), array(
            'i18n' => array('qualified_effect'),
            'You' => 'You',
            'qualified_effect' => $qualified_effect
        ));
        
        self::notifyAllPlayersBut($player_id, 'log', clienttranslate('<span class="minor_information">The ${qualified_effect} applies on ${player_name}</span>.'), array(
            'i18n' => array('qualified_effect'),
            'player_name' => self::getPlayerNameFromId($player_id),
            'qualified_effect' => $qualified_effect
        ));
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
        if (self::getGameStateValue('splay_direction') == -1) {
            if (self::getGameStateValue('n') == 0) {
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
        return self::getNonEmptyObjectFromDB(self::format("SELECT * FROM card WHERE id = {id}", array('id' => $id)));
    }

    function getStaticInfoOfAllCards() {
        /**
            Get all static information about all cards in the database.
        **/
        if (self::getGameStateValue('release_version') >= 1) {
            $cards = self::getObjectListFromDB("SELECT id, type, age, faceup_age, color, spot_1, spot_2, spot_3, spot_4, dogma_icon, is_relic FROM card");
        } else {
            $cards = self::getObjectListFromDB("SELECT id, 0 as type, age, age as faceup_age, color, spot_1, spot_2, spot_3, spot_4, dogma_icon, 0 as is_relic FROM card");
        }
        return self::attachTextualInfoToList($cards);
    }
    
    function getCardInfoFromPosition($owner, $location, $age, $position) {
        /**
            Get all information from the database about the card indicated by its position
        **/
        return self::getObjectFromDB(self::format("
                SELECT * FROM card WHERE owner = {owner} AND location = '{location}' AND age = {age} AND position = {position}
            ",
                array('owner' => $owner, 'location' => $location, 'age' => $age, 'position' => $position)
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
        return $this->textual_card_infos[$id]['name'];
    }

    function getAchievementCardName($id) {
        return $this->textual_card_infos[$id]['achievement_name'];
    }

    function getNonDemandEffect($id, $effect_number) {
        return $this->textual_card_infos[$id]['non_demand_effect_'.$effect_number];
    }

    function getDemandEffect($id) {
        return $this->textual_card_infos[$id]['i_demand_effect_1'];
    }

    function isCompelEffect($id) {
        if (self::getGameStateValue('release_version') < 1) {
            return false;
        }
        return $this->textual_card_infos[$id]['i_demand_effect_1_is_compel'];
    }
    
    function attachTextualInfo($card) {
        if ($card === null) {
            return null;
        }
        $textual_infos = $this->textual_card_infos[$card['id']];
        if (self::getGameStateValue('game_rules') == 2) { // If we play the first edition of the game
            // Inverse the rules used for effects if there is any difference. Then, only the non-alt version wil be used.
            if (array_key_exists('i_demand_effect_1_alt', $textual_infos)) {
                $unused_rule = $textual_infos['i_demand_effect_1'];
                $textual_infos['i_demand_effect_1'] = $textual_infos['i_demand_effect_1_alt'];
                $textual_infos['i_demand_effect_1_alt'] = $unused_rule;
            }
            for($i=1; $i<=3; $i++) {
                if (array_key_exists('non_demand_effect_'.$i.'_alt', $textual_infos)) {
                    $unused_rule = $textual_infos['non_demand_effect_'.$i];
                    $textual_infos['non_demand_effect_'.$i] = $textual_infos['non_demand_effect_'.$i.'_alt'];
                    $textual_infos['non_demand_effect_'.$i.'_alt'] = $unused_rule;
                }
            }
        }
        return array_merge($card, $textual_infos);
    }
    
    // TODO(https://github.com/micahstairs/bga-innovation/issues/331): Remove most call sites.
    function attachTextualInfoToList($card_list) {
        foreach($card_list as &$card) {
            $card = self::attachTextualInfo($card);
        }
        return $card_list;
    }

    function comesAlphabeticallyBefore($card_1, $card_2) {
        /**
            Returns true if card_1 comes before card_2 in English alphabetical order.
        **/
        return strcasecmp(self::getCardName($card_1['id']), self::getCardName($card_2['id'])) < 0;
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
    
    function getAgeToDrawIn($player_id, $age_min=null) {
        if($age_min === null){
            // $age_min is the maximum age on player board
            $age_min = self::getMaxAgeOnBoardTopCards($player_id);
        }
        if ($age_min < 1) {
            $age_min = 1;
        }
    
        $deck_count = self::countCardsInLocationKeyedByAge(0, 'deck', /*type=*/ 0);
        $age_to_draw = $age_min;
        while($age_to_draw <= 10 && $deck_count[$age_to_draw] == 0) {
            $age_to_draw++;
        }
        return $age_to_draw;
    }
    
    function getCurrentSplayDirection($player_id, $color) {
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
        
        return $splay_direction !== null ? $splay_direction : 0 /* No card => unsplayed */;
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
        $is_relic_condition = $is_relic === null ? "" : self::format("is_relic = {is_relic} AND", array('is_relic' => $is_relic));
                                                                    
        if ($key == 'age') {
            $num_min = 1;
            $num_max = 10;
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
                    owner = {owner} AND
                    location = '{location}'
                {opt_order_by}
            ",
                array('type_of_result' => $type_of_result, 'type_condition' => $type_condition, 'is_relic_condition' => $is_relic_condition, 'owner' => $owner, 'location' => $location, 'opt_order_by' => $opt_order_by)
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
                    owner = {owner} AND
                    location = '{location}' AND
                    {key} = {value}
                {opt_order_by}
            ",
                array('type_of_result' => $type_of_result, 'type_condition' => $type_condition, 'is_relic_condition' => $is_relic_condition, 'owner' => $owner, 'location' => $location, 'key' => $key, 'value' => $value, 'opt_order_by' => $opt_order_by)
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
    
    function getAllBoards($players) {
        $result = array();
        foreach($players as $player_id => $player) {
            $result[$player_id] = self::getCardsInLocationKeyedByColor($player_id, 'board');
        }
        return $result;
    }

    function isTopBoardCard($card) {
        if ($card['position'] == null) {
            return false;
        }
        $number_of_cards_above = self::getUniqueValueFromDB(self::format("
                SELECT
                    COUNT(*)
                FROM
                    card
                WHERE
                    owner = {owner} AND
                    location = '{location}' AND
                    color = {color} AND
                    position > {position}",
                array('owner' => $card['owner'], 'location' => $card['location'], 'color' => $card['color'], 'position' => $card['position'])
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
        return self::getOrCountCardsInLocation(/*count=*/ false, $owner, $location, 'age');
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

    function getCardsInScorePile($player_id) {
        return self::getCardsInLocation($player_id, 'score');
    }

    function countCardsInLocationKeyedByAge($owner, $location, $type=null, $is_relic=null) {
        /**
            Count all the cards in a particular location, keyed by age, then sorted by position.
        **/
        return self::getOrCountCardsInLocation(/*count=*/ true, $owner, $location, 'age', $type, $is_relic);
    }

    function countCardsInLocationKeyedByColor($owner, $location) {
        /**
            Count all the cards in a particular location, keyed by color, then sorted by position.
        **/
        return self::getOrCountCardsInLocation(/*count=*/ true, $owner, $location, 'color');
    }
    
    function countCardsInLocation($owner, $location, $type=null) {
        /**
            Count all the cards in a particular location, sorted by position.
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
                (a.spot_1 = {icon} OR a.spot_2 = {icon} OR a.spot_3 = {icon} OR a.spot_4 = {icon})
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
                (a.spot_1 = {icon} OR a.spot_2 = {icon} OR a.spot_3 = {icon} OR a.spot_4 = {icon})
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
                a.spot_1 <> {icon} AND a.spot_2 <> {icon} AND a.spot_3 <> {icon} AND a.spot_4 <> {icon}
        ",
            array('player_id' => $player_id, 'colors' => join($colors, ','), 'icon' => $icon)
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
    
    /** Information about card resources **/
    function hasRessource($card, $icon) {
        // TODO(CITIES): Handle extra icons.
        return $card !== null && ($card['spot_1'] == $icon || $card['spot_2'] == $icon || $card['spot_3'] == $icon || $card['spot_4'] == $icon);
    }
    
    /* Count the number of a particular icon on the specified card */
    function countIconsOnCard($card, $icon) {
        // TODO(CITIES): Handle extra icons.
        $icon_count = 0;
        if ($card['spot_1'] == $icon) {
            $icon_count++;
        }
        if ($card['spot_2'] == $icon) {
            $icon_count++;
        }
        if ($card['spot_3'] == $icon) {
            $icon_count++;
        }
        if ($card['spot_4'] == $icon) {
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
            if($splay_direction == 1 && $card['spot_4'] == $icon || $splay_direction == 2 && ($card['spot_1'] == $icon || $card['spot_2'] == $icon) || $splay_direction == 3 && ($card['spot_2'] == $icon || $card['spot_3'] == $icon || $card['spot_4'] == $icon)) {
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
        if ($player['player_score'] >= self::getGameStateValue('number_of_achievements_needed_to_win')) {
            self::setGameStateValue('game_end_type', 0);
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
    
    function updatePlayerScore($player_id, $delta) {
        self::DBQuery(self::format("
            UPDATE
                player
            SET
                player_innovation_score = player_innovation_score + {delta}
            WHERE
                player_id = {player_id}
        ",
            array('player_id' => $player_id, 'delta' => $delta)
        ));
        
        // Stats
        self::incStat($delta, 'score', $player_id);
        
        return self::getPlayerScore($player_id);
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

        // If unsplayed, only return the count of the icons on the top card
        if ($unsplayed == 0) {
            return $count;
        }
        
        // Add icons of the other cards.
        // TODO(CITIES): Handle extra icons.
        for ($i = 0; $i < $pile_size - 1; $i++) {
            $card = $pile[$i];
            if ($splayed_right) {
                $count += $card['spot_1'] == $icon;
            }
            if ($splayed_right || $splayed_up) {
                $count += $card['spot_2'] == $icon;
            }
            if ($splayed_up) {
                $count += $card['spot_3'] == $icon;
            }
            if ($splayed_left || $splayed_up) {
                $count += $card['spot_4'] == $icon;
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
            player_icon_count_1, player_icon_count_2, player_icon_count_3, player_icon_count_4, player_icon_count_5, player_icon_count_6
        FROM
            player
        WHERE
            player_id = {player_id}
        ",
            array('player_id' => $player_id)
        ));
        
        // Convert to a numeric associative array
        $result = array();
        for ($icon = 1; $icon <= 6; $icon++) {
            $result[$icon] = $table["player_icon_count_".$icon];
        }
        return $result;
    }

    function getPlayerResourceCountsOnDisplay($player_id) {
        $result = array();
        for ($icon = 1; $icon <= 6; $icon++) {
            $result[$icon] = 0;
        }

        $card = self::getArtifactOnDisplay($player_id);
        if ($card !== null) {
            for ($icon = 1; $icon <= 6; $icon++) {
                $result[$icon] = self::countIconsOnCard($card, $icon);
            }
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
    
    function updatePlayerRessourceCounts($player_id) {     
        self::DbQuery("
            INSERT INTO
                base (icon)
            VALUES
                (1), (2), (3), (4), (5), (6)
        ");
        
        self::DbQuery(self::format("
            INSERT INTO card_with_top_card_indication (id, type, age, color, spot_1, spot_2, spot_3, spot_4, dogma_icon, owner, location, position, splay_direction, selected, is_top_card)
                SELECT
                a.id, a.type, a.age, a.color, a.spot_1, a.spot_2, a.spot_3, a.spot_4, a.dogma_icon, a.owner, a.location, a.position, a.splay_direction, a.selected,
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
                    COALESCE(s1.count, 0) + COALESCE(s2.count, 0) + COALESCE(s3.count, 0) + COALESCE(s4.count, 0) AS count
                FROM
                    base AS a
                    LEFT JOIN (SELECT spot_1, COUNT(spot_1) AS count FROM card_with_top_card_indication WHERE is_top_card IS TRUE OR splay_direction = 2 GROUP BY spot_1) AS s1 ON a.icon = s1.spot_1
                    LEFT JOIN (SELECT spot_2, COUNT(spot_2) AS count FROM card_with_top_card_indication WHERE is_top_card IS TRUE OR splay_direction = 2 OR splay_direction = 3 GROUP BY spot_2) AS s2 ON a.icon = s2.spot_2
                    LEFT JOIN (SELECT spot_3, COUNT(spot_3) AS count FROM card_with_top_card_indication WHERE is_top_card IS TRUE OR splay_direction = 3 GROUP BY spot_3) AS s3 ON a.icon = s3.spot_3
                    LEFT JOIN (SELECT spot_4, COUNT(spot_4) AS count FROM card_with_top_card_indication WHERE is_top_card IS TRUE OR splay_direction = 1 OR splay_direction = 3 GROUP BY spot_4) AS s4 ON a.icon = s4.spot_4
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
            SET
                a.player_icon_count_1 = i1.count,
                a.player_icon_count_2 = i2.count,
                a.player_icon_count_3 = i3.count,
                a.player_icon_count_4 = i4.count,
                a.player_icon_count_5 = i5.count,
                a.player_icon_count_6 = i6.count
            WHERE
                a.player_id = {player_id} AND
                i1.icon = 1 AND
                i2.icon = 2 AND
                i3.icon = 3 AND
                i4.icon = 4 AND
                i5.icon = 5 AND
                i6.icon = 6
        ",
            array('player_id' => $player_id)
        ));
        
        // Delete all values of the auxiliary tables
        self::DbQuery("
        DELETE FROM
            card_with_top_card_indication
        ");
        
        self::DbQuery("
        DELETE FROM
            base
        ");
        
        self::DbQuery("
        DELETE FROM
            icon_count
        ");
        
        return self::getPlayerResourceCounts($player_id);
    }
    
    function promoteScoreToBGAScore() {
        // Called if the game ends by drawing. The innovation score is the main value to check to determine the winner and the number of achievements is used as a tie-breaker.
        
        // If team game, add the score of the teammate first
        if (self::decodeGameType(self::getGameStateValue('game_type')) == 'team') {
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
            array('winner' => self::getGameStateValue('winner_by_dogma'))        
        ));
        
        if (self::decodeGameType(self::getGameStateValue('game_type')) == 'team') {
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
               ($unique_non_demand_effect ? clienttranslate('non-demand effect') :
               ($current_effect_number == 1 ? clienttranslate('1<sup>st</sup> non-demand effect') :
               ($current_effect_number == 2 ? clienttranslate('2<sup>nd</sup> non-demand effect') : clienttranslate('3<sup>rd</sup> non-demand effect')))));
    }
                                  
    function getFirstPlayerUnderEffect($dogma_effect_type, $launcher_id) {
        return self::getNextPlayerUnderEffect($dogma_effect_type, -1, $launcher_id);
    }
       
    /* Returns the ID of the next player under effect, or null */
    function getNextPlayerUnderEffect($dogma_effect_type, $player_id, $launcher_id) {
        // I demand
        if (self::getGameStateValue('release_version') >= 1) {
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
                    "featured_icon_count < {launcher_icon_count} AND player_id != {launcher_id} AND player_team <> (SELECT player_team FROM player WHERE player_id = {launcher_id})",
                    array('launcher_id' => $launcher_id, 'launcher_icon_count' => $launcher_icon_count)
                );
            // I compel
            } else if ($dogma_effect_type == 2) {
                $player_query = self::format(
                    "featured_icon_count >= {launcher_icon_count} AND player_id != {launcher_id} AND player_team <> (SELECT player_team FROM player WHERE player_id = {launcher_id})",
                    array('launcher_id' => $launcher_id, 'launcher_icon_count' => $launcher_icon_count)
                );
            // Non-demand
            } else {
                $player_query = self::format(
                    "featured_icon_count >= {launcher_icon_count}",
                    array('launcher_icon_count' => $launcher_icon_count)
                );
            }
        } else {
            if ($dogma_effect_type == 0) {
                $player_query = self::format("stronger_or_equal = FALSE AND player_id != {launcher_id}", array('launcher_id' => $launcher_id));
            } else {
                $player_query = "stronger_or_equal = TRUE";
            }
        }

        // NOTE: The constant '100' is mostly arbitrary. It just needed to be at least as large as the maximum number of players in the game.
        self::DbQuery(self::format("
            UPDATE
                player
            SET
                turn_order_ending_with_launcher = (CASE WHEN player_no <= {launcher_player_no} THEN player_no + 100 ELSE player_no END)
        ", array('launcher_player_no' => self::playerIdToPlayerNo($launcher_id))));
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
    
    /** Representation in log and in main title text **/
    function getColorInClear($color) {
        switch($color) {
        case 0:
            return clienttranslate('blue');
        case 1:
            return clienttranslate('red');
        case 2:
            return clienttranslate('green');
        case 3:
            return clienttranslate('yellow');
        case 4:
            return clienttranslate('purple');
        }
    }
    
    function getColorInClearWithCards($color) {
        switch($color) {
        case 0:
            return clienttranslate('blue cards');
        case 1:
            return clienttranslate('red cards');
        case 2:
            return clienttranslate('green cards');
        case 3:
            return clienttranslate('yellow cards');
        case 4:
            return clienttranslate('purple cards');
        }
    }
    
    function getSplayDirectionInClear($splay_direction) {
        switch($splay_direction) {
        case 0:
            return clienttranslate('none');
        case 1:
            return clienttranslate('left');
        case 2:
            return clienttranslate('right');
        case 3:
            return clienttranslate('up');
        }
    }
    
    function getTranslatedNumber($number) {
        switch($number) {
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
    
    function getNormalAchievementName($age) {
        switch($age) {
            case 1:
                return clienttranslate('Prehistory');
            case 2:
                return clienttranslate('Classical');
            case 3:
                return clienttranslate('Medieval');
            case 4:
                return clienttranslate('Renaissance');
            case 5:
                return clienttranslate('Exploration');
            case 6:
                return clienttranslate('Enlightenment');
            case 7:
                return clienttranslate('Romance');
            case 8:
                return clienttranslate('Modern');
            case 9:
                return clienttranslate('Postmodern');
        }
    }

    function getPrintableStringForCardType($type) {
        switch($type) {
        case 0:
            return clienttranslate('Base');
        case 1:
            return clienttranslate('Artifacts');
        }
    }
    
    function getColoredText($text, $player_id) {
        $color = self::getPlayerColorFromId($player_id);
        return "<span style='font-weight: bold; color:#".$color.";'>".$text."</span>";
    }
    
    /** Execution of actions authorized by server **/

    function executeDrawAndTuck($player_id, $age_min = null, $type = null) {
        return self::executeDraw($player_id, $age_min, 'board', /*bottom_to=*/ true, $type);
    }

    /* Execute a draw. If $age_min is null, draw in the deck according to the board of the player, else, draw a card of the specified value or more, according to the rules */
    function executeDraw($player_id, $age_min = null, $location_to = 'hand', $bottom_to = false, $type = null) {
        $age_to_draw = self::getAgeToDrawIn($player_id, $age_min);
        
        if ($age_to_draw > 10) {
            // Attempt to draw a card above 10 : end of the game by score
            self::setGameStateValue('game_end_type', 1);
            self::setGameStateInitialValue('player_who_could_not_draw', $player_id);
            self::trace('EOG bubbled from self::executeDraw (age > 10');
            throw new EndOfGame();
        }

        // If the type isn't specified, then it is assumed we are drawing from the base cards.
        if ($type === null) {
            $type = 0;
        }
        
        $card = self::getDeckTopCard($age_to_draw, $type);

        // If an expansion’s supply pile has no cards in it, and you try to draw from it (after skipping empty ages),
        // draw a base card of that value instead.
        if ($card === null) {
            $card = self::getDeckTopCard($age_to_draw, /*type=*/ 0);
        }

        try {
            $card = self::transferCardFromTo($card, $player_id, $location_to, $bottom_to, $location_to == 'score');
        }
        catch (EndOfGame $e) {
            self::trace('EOG bubbled from self::executeDraw');
            throw $e; // Re-throw exception to higher level
        }
        return $card;
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
                player_icon_count_6 = 0
        ");
        
        // Stats
        $players = self::loadPlayersBasicInfos();
        foreach($players as $player_id => $player) {
            self::setStat(0, 'score', $player_id);
            self::setStat(0, 'max_age_on_board', $player_id);
        }
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

        // NOTE: It doesn't matter that we are technically zeroing the player's teammate's achievement count,
        // because if the game is 2v2 then the game will instantly end and the score will be binarized.
        self::DbQuery("
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
                player_icon_count_6 = 0
        ");
        self::setStat(0, 'achievements_number', $player_id);
        self::setStat(0, 'special_achievements_number', $player_id);
        self::setStat(0, 'score', $player_id);
        self::setStat(0, 'max_age_on_board', $player_id);

        self::notifyPlayer($player_id, 'removedPlayer', clienttranslate('All ${your} cards were removed from the game.'), array(
            'your' => 'your',
            'player_to_remove' => $player_id,
        ));
        self::notifyAllPlayersBut($player_id, 'removedPlayer', clienttranslate('All ${player_name}\'s cards were removed from the game.'), array(
            'player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id),
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
        $player_ids = self::getAllPlayerIds();
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
                    self::splay($player_id, $player_id, $color, /*splay_direction=*/ 0);
                }
            }
        }
    }
    
    function setSelectionRange($options) {
        // TODO(LATER): Deprecate and remove 'choose_opponent' and 'choose_opponent_with_fewer_points' and use 'choose_player' instead.
        $possible_special_types_of_choice = array('choose_opponent', 'choose_opponent_with_fewer_points', 'choose_value', 'choose_color', 'choose_two_colors', 'choose_three_colors', 'choose_player', 'choose_rearrange', 'choose_yes_or_no', 'choose_type');
        foreach($possible_special_types_of_choice as $special_type_of_choice) {
            if (array_key_exists($special_type_of_choice, $options)) {
                self::setGameStateValue('special_type_of_choice', self::encodeSpecialTypeOfChoice($special_type_of_choice));
                self::setGameStateValue('can_pass', $options['can_pass'] ? 1 : 0); 

                // Only used by 'choose_value'.
                if (array_key_exists('age', $options)) {
                    // NOTE: It is the responsibility of the card's implementation to ensure that $options['age'] has
                    // at least one element in it.
                    self::setGameStateValueFromArray('age_array', $options['age']);
                } else {
                    self::setGameStateValueFromArray('age_array', array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10));
                }

                // Only used by 'choose_color','choose_two_colors', and 'choose_three_colors'.
                if (array_key_exists('color', $options)) {
                    // NOTE: It is the responsibility of the card's implementation to ensure that $options['color'] has enough
                    // colors in it. For example, for 'choose_color', the array must have at least one element in it.
                    self::setGameStateValueFromArray('color_array', $options['color']);
                } else {
                    self::setGameStateValueFromArray('color_array', array(0, 1, 2, 3, 4));
                }

                // Only used by 'choose_type'.
                if (array_key_exists('type', $options)) {
                    // NOTE: It is the responsibility of the card's implementation to ensure that $options['type'] has
                    // at least one element in it.
                    self::setGameStateValueFromArray('type_array', $options['type']);
                } else {
                    self::setGameStateValueFromArray('type_array', self::getActiveCardTypes());
                }

                // Only used by 'choose_player'.
                if (array_key_exists('players', $options)) {
                    // NOTE: It is the responsibility of the card's implementation to ensure that $options['players'] has
                    // at least one element in it.
                    self::setGameStateValueFromArray('player_array', $options['players']);
                } else {
                    self::setGameStateValueFromArray('player_array', self::getAllActivePlayers());
                }

                return;
            }
        }

        self::setGameStateValue('special_type_of_choice', 0);
        
        $rewritten_options = array();
        foreach($options as $key => $value) {
            switch($key) {
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
                $rewritten_options['age_min'] = $value;
                $rewritten_options['age_max'] = $value;
                break;
            default:
                $rewritten_options[$key] = $value;
                break;
            }
        }
        
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
            $rewritten_options['age_max'] = 10;
        }
        // TODO(LATER): Rewrite 'age' if we end up needing this. Right now we only use 'age' for 'choose_value'.
        // TODO(LATER): Rewrite 'player' if we end up needing this. Right now we only use 'player' for 'choose_player'.
        if (!array_key_exists('color', $rewritten_options)) {
            $rewritten_options['color'] = array(0, 1, 2, 3, 4);
        }
        if (!array_key_exists('type', $rewritten_options)) {
            $rewritten_options['type'] = array(0, 1, 2, 3, 4);
        }
        if (!array_key_exists('with_icon', $rewritten_options)) {
            $rewritten_options['with_icon'] = 0;
        }
        if (!array_key_exists('without_icon', $rewritten_options)) {
            $rewritten_options['without_icon'] = 0;
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
        if (!array_key_exists('bottom_to', $rewritten_options)) {
            $rewritten_options['bottom_to'] = (array_key_exists('location_to', $rewritten_options) && $rewritten_options['location_to'] == 'deck');
        }
        if (!array_key_exists('score_keyword', $rewritten_options)) {
            $rewritten_options['score_keyword'] = false;
        }
        if (!array_key_exists('require_achievement_eligibility', $rewritten_options)) {
            $rewritten_options['require_achievement_eligibility'] = false;
        }
        if (!array_key_exists('has_demand_effect', $rewritten_options)) {
            $rewritten_options['has_demand_effect'] = false;
        }
        if (!array_key_exists('has_splay_direction', $rewritten_options)) {
            $rewritten_options['has_splay_direction'] = array(0, 1, 2, 3); // Unsplayed, left, right, or up
        }
        if (!array_key_exists('splay_direction', $rewritten_options)) {
             $rewritten_options['splay_direction'] = -1;
        } else { // This is a choice for splay
            $rewritten_options['owner_from'] = $player_id;
            $rewritten_options['location_from'] = 'board'; // Splaying is equivalent as selecting a board card, by design
            $rewritten_options['location_to'] = 'board';
            $number_of_cards_on_board = self::countCardsInLocationKeyedByColor($player_id, 'board');
            $splay_direction = $rewritten_options['splay_direction'];
            $colors = array();
            
            foreach ($rewritten_options['color'] as $color) {
                // Check if the stacks have at least 2 cards
                if ($number_of_cards_on_board[$color] < 2) {
                    // This color can't be chosen for splay since the stack is one card or less
                    continue;
                }
                
                // Check if the stack is not already splayed in the same direction
                if (self::getCurrentSplayDirection($player_id, $color) == $splay_direction) {
                    // This color can't be chosen for splay since the stack is already splayed in the same direction
                    continue;
                }
                // This color is still eligible for splaying
                $colors[] = $color;
            }
            $rewritten_options['color'] = $colors;
        }
        
        foreach($rewritten_options as $key => $value) {
            switch($key) {
            case 'can_pass':
            case 'score_keyword':
            case 'solid_constraint':
            case 'require_achievement_eligibility':
            case 'has_demand_effect':
            case 'bottom_to':
            case 'enable_autoselection':
            case 'include_relics':
                $value = $value ? 1 : 0;
                break;
            case 'location_from':
            case 'location_to':
                $value = self::encodeLocation($value);
                break;
            case 'age':
                self::setGameStateValueFromArray('age_array', $value);
                break;
            case 'color':
                self::setGameStateValueFromArray('color_array', $value);
                break;
            case 'type':
                self::setGameStateValueFromArray('type_array', $value);
                break;
            case 'players':
                self::setGameStateValueFromArray('player_array', $value);
                break;
            case 'has_splay_direction':
                self::setGameStateValueFromArray('has_splay_direction', $value);
                break;
            }
            if ($key <> 'age' && $key <> 'color' && $key <> 'type' && $key <> 'players' && $key <> 'has_splay_direction') {
                self::setGameStateValue($key, $value);
            }
        }
        
        // Set the selection on DB side
        self::selectEligibleCards();
        
        // This player will be active in the next interaction turn
        self::setGameStateValue('n', 0);
        $this->gamestate->changeActivePlayer($player_id);
    }
    
    function selectEligibleCards() {
        // Select in database the eligible cards for the current selection to be made.
        // Return the number of selected cards that way

        $player_id = self::getActivePlayerId();
        
        // Condition for owner
        $owner_from = self::getGameStateValue('owner_from');
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
            $condition_for_owner = self::format("owner IN ({opponents})", array('opponents' => join($opponents, ',')));
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
            $condition_for_owner = self::format("owner IN ({other_players})", array('other_players' => join($other_players, ',')));
        } else {
            $condition_for_owner = self::format("owner = {owner_from}", array('owner_from' => $owner_from));
        }
        
        // Condition for location
        $location_from = self::decodeLocation(self::getGameStateValue('location_from'));
        if ($location_from == 'revealed,hand') {
            $condition_for_location = "location IN ('revealed', 'hand')";
        } else if ($location_from == 'revealed,score') {
            $condition_for_location = "location IN ('revealed', 'score')";
        } else if ($location_from == 'pile') {
            $condition_for_location = "location = 'board'";
        } else {
            $condition_for_location = self::format("location = '{location_from}'", array('location_from' => $location_from));
        }
        
        // Condition for age
        $age_min = self::getGameStateValue('age_min');
        $age_max = self::getGameStateValue('age_max');
        $condition_for_age = self::format("age BETWEEN {age_min} AND {age_max}", array('age_min' => $age_min, 'age_max' => $age_max));
        // TODO(LATER): Take 'age_array' into account if there are any cards which need to rely on this mechanism.

        // Condition for age because of achievement eligibility
        $claimable_ages = array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10);
        if (self::getGameStateValue('require_achievement_eligibility') == 1) {
            $claimable_ages = self::getClaimableAges($player_id);
            if (count($claimable_ages) == 0) {
                // Avoid calling a SQL query with 'age IN ()' in it since it isn't correct syntax.
                $claimable_ages[] = -1;
            }
        }
        $condition_for_claimable_ages = self::format("age IN ({claimable_ages})", array('claimable_ages' => join($claimable_ages, ',')));

        // Condition for whether it has a demand effect
        $condition_for_demand_effect = "TRUE";
        if (self::getGameStateValue('has_demand_effect') == 1) {
            $condition_for_demand_effect = "has_demand = TRUE";
        }
        
        // Condition for color
        $color_array = self::getGameStateValueAsArray('color_array');
        $condition_for_color = count($color_array) == 0 ? "FALSE" : "color IN (".join($color_array, ',').")";

        // Condition for type
        $type_array = self::getGameStateValueAsArray('type_array');
        $condition_for_type = count($type_array) == 0 ? "AND FALSE" : "AND type IN (".join($type_array, ',').")";
        
        // Condition for icon
        // TODO(CITIES): Update this to handle 6 icons.
        $with_icon = self::getGameStateValue('with_icon');
        $without_icon = self::getGameStateValue('without_icon');
        if ($with_icon > 0) {
            $condition_for_icon = self::format("AND (spot_1 = {icon} OR spot_2 = {icon} OR spot_3 = {icon} OR spot_4 = {icon})", array('icon' => $with_icon));
        }
        else if ($without_icon > 0) {
            $condition_for_icon = self::format("AND (spot_1 IS NULL OR spot_1 <> {icon}) AND (spot_2 IS NULL OR spot_2 <> {icon}) AND (spot_3 IS NULL OR spot_3 <> {icon}) AND (spot_4 IS NULL OR spot_4 <> {icon})", array('icon' => $without_icon));
        }
        else {
            $condition_for_icon = "";
        }

        // Condition for icon hash
        $icon_hash_1 = self::getGameStateValue('icon_hash_1');
        $icon_hash_2 = self::getGameStateValue('icon_hash_2');
        $icon_hash_3 = self::getGameStateValue('icon_hash_3');
        $icon_hash_4 = self::getGameStateValue('icon_hash_4');
        $icon_hash_5 = self::getGameStateValue('icon_hash_5');
        if ($icon_hash_1 >= 0 || $icon_hash_2 >= 0 || $icon_hash_3 >= 0 || $icon_hash_4 >= 0 || $icon_hash_5 >= 0) {
            $condition_for_icon_hash = self::format("
                AND (
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
        } else {
            $condition_for_icon_hash = "";
        }

        // Condition for whether the stack is splayed
        $splay_directions = self::getGameStateValueAsArray('has_splay_direction');
        $condition_for_splay = "";
        if (count($splay_directions) == 0) {
            $condition_for_splay = "AND FALSE";
        } else if (count($splay_directions) < 4) {
            $condition_for_splay = "AND splay_direction IN (".join($splay_directions, ',').")";
        }

        // Condition for requiring ID
        $condition_for_requiring_id = "";
        $card_id_1 = self::getGameStateValue('card_id_1');
        $card_id_2 = self::getGameStateValue('card_id_2');
        $card_id_3 = self::getGameStateValue('card_id_3');
        if ($card_id_3 != -2) {
            $condition_for_requiring_id = self::format("AND id IN ({card_id_1}, {card_id_2}, {card_id_3})", array('card_id_1' => $card_id_1, 'card_id_2' => $card_id_2, 'card_id_3' => $card_id_3));
        } else if ($card_id_2 != -2) {
            $condition_for_requiring_id = self::format("AND id IN ({card_id_1}, {card_id_2})", array('card_id_1' => $card_id_1, 'card_id_2' => $card_id_2));
        } else if ($card_id_1 != -2) {
            $condition_for_requiring_id = self::format("AND id IN ({card_id_1})", array('card_id_1' => $card_id_1));
        }

        // Condition for excluding ID
        $condition_for_excluding_id = "";
        $not_id = self::getGameStateValue('not_id');
        if ($not_id != -2) { // Used by cards like Fission and Self service
            $condition_for_excluding_id = self::format("AND id <> {not_id}", array('not_id' => $not_id));
        }

        // Condition for including relic
        $condition_for_including_relic = "";
        $include_relics = self::getGameStateValue('include_relics');
        if ($include_relics == 0) {
            $condition_for_including_relic = "AND is_relic = FALSE";
        }
        
        if (self::getGameStateValue('splay_direction') == -1 && $location_from == 'board') {
            // Only the active card can be selected
            self::DbQuery(self::format("
                UPDATE
                    card
                LEFT JOIN
                    (SELECT owner AS joined_owner, color AS joined_color, MAX(position) AS position_of_active_card FROM card WHERE location = 'board' GROUP BY owner, color) AS joined
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
                    position = position_of_active_card AND
                    {condition_for_color}
                    {condition_for_type}
                    {condition_for_icon}
                    {condition_for_icon_hash}
                    {condition_for_splay}
                    {condition_for_requiring_id}
                    {condition_for_excluding_id}
                    {condition_for_including_relic}
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
                    'condition_for_including_relic' => $condition_for_including_relic
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
                    {condition_for_color}
                    {condition_for_type}
                    {condition_for_icon}
                    {condition_for_icon_hash}
                    {condition_for_splay}
                    {condition_for_requiring_id}
                    {condition_for_excluding_id}
                    {condition_for_including_relic}
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
                    'condition_for_including_relic' => $condition_for_including_relic
                )
            ));
        }
        
        return self::getUniqueValueFromDB("SELECT COUNT(*) FROM card WHERE selected IS TRUE");
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
       default:
            // This should not happen
            throw new BgaVisibleSystemException(self::format(self::_("Unhandled case in {function}: '{code}'"), array('function' => "encodeLocation()", 'code' => $location)));
            break;
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
        default:
            // This should not happen
            throw new BgaVisibleSystemException(self::format(self::_("Unhandled case in {function}: '{code}'"), array('function' => "decodeLocation()", 'code' => $location_code)));
            break;
        }
    }
    
    function encodeSpecialTypeOfChoice($special_type_of_choice) {
        switch($special_type_of_choice) {
        case 'choose_opponent':
            return 1;
        case 'choose_opponent_with_fewer_points':
            return 2;
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
        }
    }
    
    function decodeSpecialTypeOfChoice($special_type_of_choice_code) {
        switch($special_type_of_choice_code) {
        case 1:
            return 'choose_opponent';
        case 2:
            return 'choose_opponent_with_fewer_points';
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
        if (self::getGameStateValue('release_version') >= 1) {
            $nested_card_state = self::getCurrentNestedCardState();
            $card_id = $nested_card_state['card_id'];
            $card = self::getCardInfo($card_id);
            $current_effect_type = $nested_card_state['current_effect_type'];
            $current_effect_number = $nested_card_state['current_effect_number'];
        } else {
            $nested_id_1 = self::getGameStateValue('nested_id_1');
            $card_id = $nested_id_1 == -1 ? self::getGameStateValue('dogma_card_id') : $nested_id_1;
            $card = self::getCardInfo($card_id);
            $current_effect_type = $nested_id_1 == -1 ? self::getGameStateValue('current_effect_type') : 1 /* Non-demand effects only*/;
            $current_effect_number = $nested_id_1 == -1 ?  self::getGameStateValue('current_effect_number') : self::getGameStateValue('nested_current_effect_number_1');
        }
        
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
                'player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id),
                'player_name_as_you' => 'You'
            )
        );
    }
    
    function getDogmaCardNames() { // Returns the name of the current dogma card or all the names where there are nested dogma effects
        $player_id = self::getCurrentPlayerUnderDogmaEffect();
        
        $card_names = array();
        
        if (self::getGameStateValue('release_version') >= 1) {
            $i18n = array();
            $nesting_index = self::getGameStateValue('current_nesting_index');
            for ($i = 0; $i <= $nesting_index; $i++) {
                $card = self::getCardInfo(self::getNestedCardState($i)['card_id']);
                $card_names['card_'.$i] = self::getCardName($card['id']);
                $card_names['ref_player_'.$i] = $player_id;
                $i18n[] = 'card_'.$i;
            }
        } else {
            $dogma_card_id = self::getGameStateValue('dogma_card_id');
            $dogma_card = self::getCardInfo($dogma_card_id);
            $card_names['card_0'] = self::getCardName($dogma_card['id']);
            $card_names['ref_player_0'] = self::getGameStateValue('active_player');
            $i18n = array('card_0');

            $j = 1;
            for($i=9; $i>=1; $i--) {
                $nested_id = self::getGameStateValue('nested_id_'.$i);
                if ($nested_id == -1) {
                    continue;
                }
                
                $card = self::getCardInfo($nested_id);
                $card_names['card_'.$j] = self::getCardName($card['id']);
                $card_names['ref_player_'.$j] = $player_id;
                $i18n[] = 'card_'.$j;
                $j++;
            }
        }

        $card_names['i18n'] = $i18n;
        return $card_names;
    }
    
    function getJSCardId($card) {
        return "#item_" . $card['id'] . "__age_" . $card['age'] . "__type_" . $card['type'] . "__is_relic_" . $card['is_relic'] . "__M__card";
    }
    
    function getJSCardEffectQuery($card, $effect_type, $effect_number) {
        return self::getJSCardId($card) . " ." . ($effect_type == 1 ? "non_demand" : "i_demand") . "_effect_" . $effect_number;
    }

    function setLauncherId($launcher_id) {
        if (self::getGameStateValue('release_version') >= 1) {
            self::updateCurrentNestedCardState('launcher_id', $launcher_id);
        }
    }

    function getLauncherId() {
        if (self::getGameStateValue('release_version') >= 1) {
            if (self::getGameStateValue('current_nesting_index') < 0) {
                return self::getGameStateValue('active_player');
            }
            return self::getCurrentNestedCardState()['launcher_id'];
        } else {
            return self::getGameStateValue('active_player');
        }
    }

    function incrementStep($delta) {
        self::setStep(self::getStep() + $delta);
    }

    function setStep($step) {
        if (self::getGameStateValue('release_version') >= 1) {
            self::updateCurrentNestedCardState('step', $step);
        } else {
            self::setGameStateValue('step', $step);
        }
    }

    function getStep() {
        if (self::getGameStateValue('release_version') >= 1) {
            return self::getCurrentNestedCardState()['step'];
        } else {
            return self::getGameStateValue('step');
        }
    }

    function incrementStepMax($delta) {
        self::setStepMax(self::getStepMax() + $delta);
    }

    function setStepMax($step_max) {
        if (self::getGameStateValue('release_version') >= 1) {
            self::updateCurrentNestedCardState('step_max', $step_max);
        } else {
            self::setGameStateValue('step_max', $step_max);
        }
    }

    function getStepMax() {
        if (self::getGameStateValue('release_version') >= 1) {
            return self::getCurrentNestedCardState()['step_max'];
        } else {
            return self::getGameStateValue('step_max');
        }
    }

    function setAuxiliaryValue($auxiliary_value) {
        if (self::getGameStateValue('release_version') >= 1) {
            self::updateCurrentNestedCardState('auxiliary_value', $auxiliary_value);
        } else {
            self::setGameStateValue('auxiliary_value', $auxiliary_value);
        }
    }

    function setAuxiliaryValueFromArray($array) {
        self::setAuxiliaryValue(self::getArrayAsValue($array));
    }

    function getAuxiliaryValue() {
        if (self::getGameStateValue('release_version') >= 1) {
            return self::getCurrentNestedCardState()['auxiliary_value'];
        } else {
            return self::getGameStateValue('auxiliary_value');
        }
    }

    function getAuxiliaryValueAsArray() {
        return self::getValueAsArray(self::getAuxiliaryValue());
    }

    function setAuxiliaryValue2($auxiliary_value_2) {
        self::updateCurrentNestedCardState('auxiliary_value_2', $auxiliary_value_2);
    }

    function setAuxiliaryValue2FromArray($array) {
        self::setAuxiliaryValue2(self::getArrayAsValue($array));
    }

    function getAuxiliaryValue2() {
        return self::getCurrentNestedCardState()['auxiliary_value_2'];
    }

    function getAuxiliaryValue2AsArray() {
        return self::getValueAsArray(self::getAuxiliaryValue2());
    }
    
    /** Nested dogma excution management system: FIFO stack **/
    function executeNonDemandEffects($card) {
        $player_id = self::getCurrentPlayerUnderDogmaEffect();
        if (self::getGameStateValue('release_version') >= 1) {
            $card_args = self::getNotificationArgsForCardList([$card]);
            if (self::getNonDemandEffect($card['id'], 1) === null) {
                self::notifyAll('logWithCardTooltips', clienttranslate('There are no non-demand effects on ${card} to execute.'), ['card' => $card_args, 'card_ids' => [$card['id']]]);
                return;
            }
            self::notifyPlayer($player_id, 'logWithCardTooltips', clienttranslate('${You} execute the non-demand effect(s) of ${card}.'),
                ['You' => 'You', 'card' => $card_args, 'card_ids' => [$card['id']]]);
            self::notifyAllPlayersBut($player_id, 'logWithCardTooltips', clienttranslate('${player_name} executes the non-demand effect(s) of ${card}.'),
                ['player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id), 'card' => $card_args, 'card_ids' => [$card['id']]]);
        } else {
            if (self::getNonDemandEffect($card['id'], 1) === null) { // There is no non-demand effect
                self::notifyGeneralInfo(clienttranslate('There is no non-demand effect on this card.'));
                // No exclusive execution: do nothing
                return;
            }
            if (self::getNonDemandEffect($card['id'], 2) !== null) { // There are 2 or 3 non-demand effects
                    self::notifyPlayer($player_id, 'log', clienttranslate('${You} execute the non-demand effects of this card.'), array('You' => 'You'));
                    self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} executes the non-demand effects of this card.'), array('player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id)));
            } else { // There is a single non-demand effect
                    self::notifyPlayer($player_id, 'log', clienttranslate('${You} execute the non-demand effect of this card.'), array('You' => 'You'));
                    self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} executes the non-demand effect of this card.'), array('player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id)));
            }
        }
        self::pushCardIntoNestedDogmaStack($card, /*execute_demand_effects=*/ false);
    }

    function executeAllEffects($card) {
        $current_nested_state = self::getCurrentNestedCardState();
        $current_card = self::getCardInfo($current_nested_state['card_id']);
        $card_1_args = self::getNotificationArgsForCardList([$current_card]);
        $card_2_args = self::getNotificationArgsForCardList([$card]);
        $player_id = self::getCurrentPlayerUnderDogmaEffect();
        $initially_executed_card = self::getCardInfo($current_nested_state['executing_as_if_on_card_id']);
        $icon = self::getIconSquare($initially_executed_card['dogma_icon']);
        self::notifyPlayer($player_id, 'logWithCardTooltips', clienttranslate('${You} execute the effects of ${card_2} as if it were on ${card_1}, using ${icon} as the featured icon.'),
            ['You' => 'You', 'card_1' => $card_1_args, 'card_2' => $card_2_args, 'card_ids' => [$current_card['id'], $card['id']], 'icon' => $icon]);
        self::notifyAllPlayersBut($player_id, 'logWithCardTooltips', clienttranslate('${player_name} executes the effects of ${card_2} as if it were on ${card_1}, using ${icon} as the featured icon.'),
            ['player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id), 'card_1' => $card_1_args, 'card_2' => $card_2_args, 'card_ids' => [$current_card['id'], $card['id']], 'icon' => $icon]);
        self::pushCardIntoNestedDogmaStack($card, /*execute_demand_effects=*/ true);
    }
    
    function pushCardIntoNestedDogmaStack($card, $execute_demand_effects) {
        self::trace('nesting++');
        if (self::getGameStateValue('release_version') >= 1) {
            $current_player_id = self::getCurrentPlayerUnderDogmaEffect();
            $nested_card_state = self::getCurrentNestedCardState();
            // Every card that says "execute the effects" also says "as if they were on this card"
            if ($execute_demand_effects) {
                $as_if_on = $nested_card_state['executing_as_if_on_card_id'];
            } else {
                $as_if_on = $card['id'];
            }
            $nesting_index = self::getGameStateValue('current_nesting_index');
            $has_i_demand = self::getDemandEffect($card['id']) !== null && !self::isCompelEffect($card['id']);
            $has_i_compel = self::getDemandEffect($card['id']) !== null && self::isCompelEffect($card['id']);
            $effect_type = $execute_demand_effects ? ($has_i_demand ? 0 : ($has_i_compel ? 2 : 1)) : 1;
            self::DbQuery(self::format("
                INSERT INTO nested_card_execution
                    (nesting_index, card_id, executing_as_if_on_card_id, launcher_id, current_effect_type, current_effect_number, step, step_max)
                VALUES
                    ({nesting_index}, {card_id}, {as_if_on}, {launcher_id}, {effect_type}, 1, -1, -1)
            ", array('nesting_index' => $nesting_index + 1, 'card_id' => $card['id'], 'as_if_on' => $as_if_on, 'launcher_id' => $current_player_id, 'effect_type' => $effect_type)));
        } else {
            for($i=8; $i>=1; $i--) {
                self::setGameStateValue('nested_id_'.($i+1), self::getGameStateValue('nested_id_'.$i));
                self::setGameStateValue('nested_current_effect_number_'.($i+1), self::getGameStateValue('nested_current_effect_number_'.$i));
            }
            self::setGameStateValue('nested_id_1', $card['id']);
            self::setGameStateValue('nested_current_effect_number_1', 0);
        }
    }
    
    function popCardFromNestedDogmaStack() {
        self::trace('nesting--');
        if (self::getGameStateValue('release_version') >= 1) {
            self::DbQuery(self::format("DELETE FROM nested_card_execution WHERE nesting_index = {nesting_index}", array('nesting_index' => self::getGameStateValue('current_nesting_index'))));
            self::incGameStateValue('current_nesting_index', -1);
            self::updateCurrentNestedCardState('post_execution_index', 'post_execution_index + 1');
        } else {
            for($i=1; $i<=8; $i++) {
                self::setGameStateValue('nested_id_'.$i, self::getGameStateValue('nested_id_'.($i+1)));
                self::setGameStateValue('nested_current_effect_number_'.$i, self::getGameStateValue('nested_current_effect_number_'.($i+1)));
            }
        }
    }

    function getNestedCardState($nesting_index) {
        return self::getObjectFromDB(
            self::format("
                SELECT
                    nesting_index,
                    card_id,
                    executing_as_if_on_card_id,
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
        return self::getNestedCardState(self::getGameStateValue('current_nesting_index'));
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
                array('column' => $column, 'value' => $value, 'nesting_index' => self::getGameStateValue('current_nesting_index')))
        );
    }

    function getCurrentPlayerUnderDogmaEffect() {
        if (self::getGameStateValue('release_version') >= 1) {
            $player_id = self::getCurrentNestedCardState()['current_player_id'];
            // TODO(LATER): Figure out why this workaround is necessary.
            if ($player_id == -1) {
                return self::getGameStateValue('active_player');
            }
            return $player_id;
        } else {
            return self::getGameStateValue('current_player_under_dogma_effect');
        }
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
        self::markAsSelected($card_id, $player_id);
        
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
                self::markAsSelected($card_in_hand['id'], $player_id);
            } else {
                self::unmarkAsSelected($card_in_hand['id'], $player_id);
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
        self::setGameStateValue('relic_id', -1);

        self::trace('relicPlayerTurn->interPlayerTurn (passSeizeRelic)');
        $this->gamestate->nextState('interPlayerTurn');
    }

    function seizeRelicToHand() {
        // Check that this is the player's turn and that it is a "possible action" at this game state
        self::checkAction('seizeRelicToHand');

        $player_id = self::getCurrentPlayerId();
        $card = self::getCardInfo(self::getGameStateValue('relic_id'));

        if ($card['owner'] != 0 && $card['owner'] != $player_id) {
            self::incStat(1, 'relics_stolen_number', $card['owner']);
        }
        self::incStat(1, 'relics_seized_number', $player_id);

        self::transferCardFromTo($card, $player_id, 'hand');
        self::setGameStateValue('relic_id', -1);
       
        self::trace('relicPlayerTurn->interPlayerTurn (seizeRelicToHand)');
        $this->gamestate->nextState('interPlayerTurn');
    }

    function seizeRelicToAchievements() {
        // Check that this is the player's turn and that it is a "possible action" at this game state
        self::checkAction('seizeRelicToAchievements');

        $player_id = self::getCurrentPlayerId();
        $card = self::getCardInfo(self::getGameStateValue('relic_id'));

        if ($card['owner'] != 0 && $card['owner'] != $player_id) {
            self::incStat(1, 'relics_stolen_number', $card['owner']);
        }
        self::incStat(1, 'relics_seized_number', $player_id);

        try {
            self::transferCardFromTo($card, $player_id, "achievements");
        } catch (EndOfGame $e) {
            $this->gamestate->nextState('justBeforeGameEnd');
            return;
        }
       
        self::setGameStateValue('relic_id', -1);

        self::trace('relicPlayerTurn->interPlayerTurn (seizeRelicToAchievements)');
        $this->gamestate->nextState('interPlayerTurn');
    }

    function dogmaArtifactOnDisplay() {
        // Check that this is the player's turn and that it is a "possible action" at this game state
        self::checkAction('dogmaArtifactOnDisplay');

        $player_id = self::getCurrentPlayerId();
        $card = self::getArtifactOnDisplay($player_id);

        // Battleship Yamato does not have a dogma effect
        if ($card['id'] == 188) {
            self::throwInvalidChoiceException();
        }

        self::decreaseResourcesForArtifactOnDisplay($player_id, $card);

        // TODO(ECHOES): Triggers all applicable Echo effects.

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
        self::transferCardFromTo($card, 0, 'deck');

        self::giveExtraTime($player_id);
        self::trace('artifactPlayerTurn->playerTurn (returnArtifactOnDisplay)');
        $this->gamestate->nextState('playerTurn');
        self::setGameStateValue('current_action_number', 1);
        
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

        self::giveExtraTime($player_id);
        self::trace('artifactPlayerTurn->playerTurn (passArtifactOnDisplay)');
        $this->gamestate->nextState('playerTurn');
        self::setGameStateValue('current_action_number', 1);
        
        self::incStat(1, 'free_action_pass_number', $player_id);
    }
    
    function achieve($age) {
        // Check that this is the player's turn and that it is a "possible action" at this game state
        self::checkAction('achieve');
        $player_id = self::getActivePlayerId();
        
        // Check if the player really meet the conditions to achieve that card
        // TODO(ECHOES): Update this once there can be more than one achievement of the same age in the claimable achievements pile.
        $card = self::getObjectFromDB(self::format("SELECT * FROM card WHERE location = 'achievements' AND age = {age} AND owner = 0", array('age' => $age)));
        if ($card['owner'] != 0) {
            self::throwInvalidChoiceException();
        }
        
        $age_max = self::getMaxAgeOnBoardTopCards($player_id);
        $player_score = self::getPlayerScore($player_id);
        
        // Rule: to achieve the age X, the player has to have a top card of his board of age >= X and 5*X points in his score pile
        if ($age > $age_max || $player_score < 5*$age) {
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
        
        $previous_top_card = self::getTopCardOnBoard($card['owner'], $card['color']);
        // Execute the meld
        self::transferCardFromTo($card, $card['owner'], 'board');
        
        try {
            self::checkForSpecialAchievements();
        } catch (EndOfGame $e) {
            // End of the game: the exception has reached the highest level of code
            self::trace('EOG bubbled from self::meld');
            self::trace('playerTurn->justBeforeGameEnd');
            $this->gamestate->nextState('justBeforeGameEnd');
            return;
        }
        
        if (self::tryToDigArtifactAndSeizeRelic($player_id, $previous_top_card, $card)) {
            self::trace('playerTurn->relicPlayerTurn');
            $this->gamestate->nextState('relicPlayerTurn');
            return;
        }
        
        // End of player action
        self::trace('playerTurn->interPlayerTurn (meld)');
        $this->gamestate->nextState('interPlayerTurn');
    }

    /* Returns true if a relic is being seized */
    function tryToDigArtifactAndSeizeRelic($player_id, $previous_top_card, $melded_card) {
        // The Artifacts expansion is not enabled.
        if (self::getGameStateValue('artifacts_mode') == 1) {
            return false;
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
            $top_artifact_card = self::getDeckTopCard($age_draw, /*type=*/ 1);
            
            if ($top_artifact_card == null) {
                self::notifyPlayer($player_id, "log", clienttranslate('There are no Artifact cards in the ${age} deck, so the dig event is ignored.'), array('age' => self::getAgeSquare($age_draw)));
            } else {
                self::transferCardFromTo($top_artifact_card, $player_id, 'display');
                self::incStat(1, 'dig_events_number', $player_id);
                
                // "After you dig an artifact, you may seize a Relic of the same value as the Artifact card drawn."
                if (self::getGameStateValue('artifacts_mode') == 3) {
                    $relic = self::getRelicForAge($top_artifact_card['faceup_age']);
                    // "You may only do this if the Relic is next to its supply pile, or in any achievements pile (even your own!)."
                    if ($relic != null && (self::canSeizeRelicToHand($relic, $player_id) || self::canSeizeRelicToAchievements($relic, $player_id))) {
                        self::setGameStateValue('relic_id', $relic['id']);
                        return true;
                    }
                }
            }
        }
        return false;
    }

    function haveOverlappingHexagonIcons($card_1, $card_2) {
        // TODO(CITIES): Expand expression to include the extra 2 spots.
        return
            ($card_1['spot_1'] === '0' && $card_2['spot_1'] === '0') || 
            ($card_1['spot_2'] === '0' && $card_2['spot_2'] === '0') || 
            ($card_1['spot_3'] === '0' && $card_2['spot_3'] === '0') ||
            ($card_1['spot_4'] === '0' && $card_2['spot_4'] === '0');
    }

    /* Returns null if there is no relic of the specified age */
    function getRelicForAge($age) {
        return self::getObjectFromDB(self::format("SELECT * FROM card WHERE age = {age} AND is_relic", array('age' => $age)));
    }

    function dogma($card_id) {
        // Check that this is the player's turn and that it is a "possible action" at this game state
        self::checkAction('dogma');
        $player_id = self::getActivePlayerId();
        
        // Check if the player has this card really on his board
        $card = self::getCardInfo($card_id);
        
        // Player does not have this card on their board
        if ($card['owner'] != $player_id || $card['location'] != "board") {
            self::throwInvalidChoiceException();
        }
        // Card is not at the top of a stack
        if (!self::isTopBoardCard($card)) {
            self::throwInvalidChoiceException();
        }
        // Battleship Yamato does not have any dogma effects on it to execute
        if ($card['id'] == 188) {
            self::throwInvalidChoiceException();
        }
        
        // Stats
        self::updateActionAndTurnStats($player_id);
        self::incStat(1, 'dogma_actions_number', $player_id);
        if ($card['type'] == 1) {
            self::incStat(1, 'dogma_actions_number_targeting_artifact_on_board', $player_id);
        }

        self::setUpDogma($player_id, $card);

        // Resolve the first dogma effect of the card
        self::trace('playerTurn->dogmaEffect (dogma)');
        $this->gamestate->nextState('dogmaEffect');
    }

    function updateActionAndTurnStats($player_id) {
        if (self::getGameStateValue('release_version') >= 1) {
            if (self::getGameStateValue('current_action_number') == 1) {
                self::incStat(1, 'turns_number');
                self::incStat(1, 'turns_number', $player_id);
            }
        } else {
            if (self::getGameStateValue('has_second_action') || self::getGameStateValue('first_player_with_only_one_action') || self::getGameStateValue('second_player_with_only_one_action')) {
                self::incStat(1, 'turns_number');
                self::incStat(1, 'turns_number', $player_id);
            }
        }
        self::incStat(1, 'actions_number');
        self::incStat(1, 'actions_number', $player_id);
    }

    function increaseResourcesForArtifactOnDisplay($player_id, $card) {
        // Battleship Yamato does not have any resource symbols
        if ($card['id'] == 188) {
            $resource_icon = null;
            $resource_count_delta = 0;
        } else {
            $resource_icon = $card['dogma_icon'];
            $resource_count_delta = self::countIconsOnCard($card, $resource_icon);
        }
        self::updateResourcesForArtifactOnDisplay($player_id, $resource_icon, $resource_count_delta);
    }

    function decreaseResourcesForArtifactOnDisplay($player_id, $card) {
        // Battleship Yamato does not have any resource symbols
        if ($card['id'] == 188) {
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
    
    function setUpDogma($player_id, $card, $extra_icons_from_artifact_on_display = 0) {

        self::notifyDogma($card);
        
        $dogma_icon = $card['dogma_icon'];
        $ressource_column = 'player_icon_count_' . $dogma_icon;
        
        $players = self::getCollectionFromDB(self::format("SELECT player_id, player_no, player_team, {ressource_column} FROM player", array('ressource_column' => $ressource_column)));
        $players_nb = count($players);
        
        // Compare players ressources on dogma icon_count;
        $dogma_player = $players[$player_id];
        $dogma_player_team = $dogma_player['player_team'];
        $dogma_player_ressource_count = $dogma_player[$ressource_column] + $extra_icons_from_artifact_on_display;
        $dogma_player_no = $dogma_player['player_no'];
        
        // Count each player ressources
        $players_ressource_count = array();
        foreach ($players as $id => $player) {
            $player_no = $player['player_no'];
            $player_ressource_count = $id == $player_id ? $dogma_player_ressource_count : $player[$ressource_column];
            $players_ressource_count[$player_no] = $player_ressource_count;
            $players_teams[$player_no] = $player['player_team'];
            
            self::notifyPlayerRessourceCount($id, $dogma_icon, $player_ressource_count);

            if (self::getGameStateValue('release_version') >= 1) {
                self::DBQuery(self::format("
                    UPDATE 
                        player
                    SET
                        featured_icon_count = {featured_icon_count}
                    WHERE
                        player_id = {player_id}"
                ,
                    array('featured_icon_count' => $player_ressource_count, 'player_id' => $id)
                ));
            }
        }

        if (self::getGameStateValue('release_version') < 1) {
            $player_no = $dogma_player_no;
            $player_no_under_i_demand_effect = 0;
            $player_no_under_non_demand_effect = 0;
            
            // Loop on players finishing with the one who triggered the dogma
            do {
                if ($player_no == $players_nb) { // End of table reached, go back to the top
                    $player_no = 1;
                }
                else { // Next row
                    $player_no = $player_no + 1;          
                }
                
                $player_ressource_count = $players_ressource_count[$player_no];
                
                // Mark the player
                if ($player_ressource_count >= $dogma_player_ressource_count) {
                    $stronger_or_equal = "TRUE";
                    $player_no_under_non_demand_effect++;
                    $player_no_under_effect = $player_no_under_non_demand_effect;
                }
                else {
                    $stronger_or_equal = "FALSE";
                    if ($players_teams[$player_no] != $dogma_player_team) {
                        $player_no_under_i_demand_effect++;
                        $player_no_under_effect = $player_no_under_i_demand_effect;
                    }
                    else { // Player on the same team don't suffer the I demand effect of each other
                        $player_no_under_effect = 0;
                    }
                }
                self::DBQuery(self::format("
                    UPDATE 
                        player
                    SET
                        stronger_or_equal = {stronger_or_equal},
                        player_no_under_effect = {player_no_under_effect}
                    WHERE
                        player_no = {player_no}"
                ,
                    array('stronger_or_equal' => $stronger_or_equal, 'player_no_under_effect' => $player_no_under_effect, 'player_no' => $player_no)
                ));
                
            } while ($player_no != $dogma_player_no);
        }

        if (self::getDemandEffect($card['id']) == null) {
            $current_effect_type = 1;
        } else if (self::isCompelEffect($card['id'])) {
            $current_effect_type = 2;
        } else {
            $current_effect_type = 0;
        }
        
        // Write info in global variables to prepare the first effect
        if (self::getGameStateValue('release_version') >= 1) {
            self::setGameStateValue('current_nesting_index', 0);
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
                        current_effect_number = 1,
                        post_execution_index = 0
                    WHERE
                        nesting_index = 0",
                    array('card_id' => $card['id'], 'card_location' => $card['location'], 'launcher_id' => $player_id, 'effect_type' => $current_effect_type))
            );
        } else {
            self::setGameStateValue('dogma_card_id', $card['id']);
            self::setGameStateValue('current_effect_type', $current_effect_type);
            self::setGameStateValue('current_effect_number', 1);
        }
        self::setGameStateValue('sharing_bonus', 0);
    }

    function choose($card_id) {
        // Check that this is the player's turn and that it is a "possible action" at this game state
        self::checkAction('choose');
        $player_id = self::getActivePlayerId();
        
        if ($card_id == -1) {
            // The player chooses to pass or stop
            if (self::getGameStateValue('can_pass') == 0 && self::getGameStateValue('n_min') > 0) {
                self::throwInvalidChoiceException();
            }
            if (self::getGameStateValue('special_type_of_choice') == 0) {
                self::setGameStateValue('id_last_selected', -1);
            } else {
                self::setGameStateValue('choice', -2);
            }
        } else if (self::getGameStateValue('special_type_of_choice') != 0) {
            self::throwInvalidChoiceException();
        } else {
            // Check if the card is within the selection range
            $card = self::getCardInfo($card_id);
            
            if (!$card['selected']) {
                self::throwInvalidChoiceException();
            }
            
            self::setGameStateValue('id_last_selected', $card_id);
            self::unmarkAsSelected($card_id);
            
            // Passing is only possible at the beginning of the step
            self::setGameStateValue('can_pass', 0);
        }
        
        // Return to the resolution of the effect
        self::trace('selectionMove->interSelectionMove (choose)');
        $this->gamestate->nextState('interSelectionMove');
    }
    
    function chooseRecto($owner, $location, $age, $position) {
        // Check that this is the player's turn and that it is a "possible action" at this game state
        self::checkAction('choose');
        $players = array_keys(self::loadPlayersBasicInfos());
        $player_id = self::getActivePlayerId();
        if (self::getGameStateValue('special_type_of_choice') != 0) {
            self::throwInvalidChoiceException();
        }
        if ((!in_array($owner, $players) && $owner != 0) || !in_array($location, self::getObjectListFromDB("SELECT DISTINCT location FROM card", true)) || $age < 1 || $age > 10) {
            self::throwInvalidChoiceException();
        }
        
        $card = self::getCardInfoFromPosition($owner, $location, $age, $position);
        if ($card === null) {
            self::throwInvalidChoiceException();
        }
        if (!$card['selected']) {
            self::throwInvalidChoiceException();
        }

        self::setGameStateValue('id_last_selected', $card['id']);
        self::unmarkAsSelected($card['id']);
        
        // Passing is only possible at the beginning of the step
        self::setGameStateValue('can_pass', 0);
        
        // Return to the resolution of the effect
        self::trace('selectionMove->interSelectionMove (chooseRecto)');
        $this->gamestate->nextState('interSelectionMove');
    }
    
    function chooseSpecialOption($choice) {
        // Check that this is the player's turn and that it is a "possible action" at this game state
        self::checkAction('choose');
        $player_id = self::getActivePlayerId();
        
        $special_type_of_choice = self::getGameStateValue('special_type_of_choice');
        
        if ($special_type_of_choice == 0) { // This is not a special choice
            self::throwInvalidChoiceException();
        }
        
        switch(self::decodeSpecialTypeOfChoice($special_type_of_choice)) {
            case 'choose_opponent':
            case 'choose_opponent_with_fewer_points':
                // Player choice
                // Check if the choice is a opponent
                if ($choice == $player_id) {
                    self::throwInvalidChoiceException();
                }
                else if ($choice == self::getPlayerTeammate($player_id)) {
                    self::throwInvalidChoiceException();
                }
                $players = self::loadPlayersBasicInfos();
                if (!array_key_exists($choice, $players)) {
                    self::throwInvalidChoiceException();
                }
                if ($choice == 'choose_opponent_with_fewer_points' && self::getPlayerScore($choice) >= self::getPlayerScore($player_id)) {
                    self::throwInvalidChoiceException();
                }
                break;
            case 'choose_value':
                if (self::getGameStateValue('release_version') >= 1) {
                    if (!ctype_digit($choice) || !in_array($choice, self::getGameStateValueAsArray('age_array'))) {
                        self::throwInvalidChoiceException();
                    }
                } else {
                    if (!ctype_digit($choice) || $choice < 1 || $choice > 10) {
                        self::throwInvalidChoiceException();
                    }
                }
                break;
            case 'choose_color':
                if (self::getGameStateValue('release_version') >= 1) {
                    if (!ctype_digit($choice) || !in_array($choice, self::getGameStateValueAsArray('color_array'))) {
                        self::throwInvalidChoiceException();
                    }
                } else {
                    if (!ctype_digit($choice) || $choice < 0 || $choice > 4) {
                        self::throwInvalidChoiceException();
                    }
                }
                break;
            case 'choose_two_colors':
                if (!ctype_digit($choice) || $choice < 0) {
                    self::throwInvalidChoiceException();
                }
                $colors = self::getValueAsArray($choice);
                if (self::getGameStateValue('release_version') >= 1) {
                    if (count($colors) <> 2 || $colors[0] == $colors[1] || !in_array($colors[0], self::getGameStateValueAsArray('color_array')) || !in_array($colors[1], self::getGameStateValueAsArray('color_array'))) {
                        self::throwInvalidChoiceException();
                    }
                } else {
                    if (count($colors) <> 2 || $colors[0] == $colors[1] || $colors[0] < 0 || $colors[0] > 4 || $colors[1] < 0 || $colors[1] > 4) {
                        self::throwInvalidChoiceException();
                    }
                }
                break;
            case 'choose_three_colors':
                if (!ctype_digit($choice) || $choice < 0) {
                    self::throwInvalidChoiceException();
                }
                $colors = self::getValueAsArray($choice);
                $allowed_color_choices = self::getGameStateValueAsArray('color_array');
                if (count($colors) <> 3 || count(array_unique($colors)) <> 3 || !in_array($colors[0], $allowed_color_choices) || !in_array($colors[1], $allowed_color_choices) || !in_array($colors[2], $allowed_color_choices)) {
                    self::throwInvalidChoiceException();
                }
                break;
            case 'choose_player':
                if (!ctype_digit($choice)) {
                    self::throwInvalidChoiceException();
                }
                $player_no = self::getUniqueValueFromDB(self::format("SELECT player_no FROM player WHERE player_id = {player_id}", array('player_id' => $choice)));
                if ($player_no == null || !in_array($player_no, self::getGameStateValueAsArray('player_array'))) {
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
                    'color' => self::getColorInClear($color)
                ));
                self::notifyAllPlayersBut($player_id, 'rearrangedPile', clienttranslate('${player_name} rearranges his ${color} stack.'), array(
                    'i18n' => array('color'),
                    'player_id' => $player_id,
                    'new_max_age_on_board' => $new_max_age_on_board,
                    'rearrangement' => $choice,
                    'player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id),
                    'color' => self::getColorInClear($color))
                );
                $choice = 1;
                break;
            case 'choose_yes_or_no':
                // Yes/no choice
                if ($choice != 0 && $choice != 1) {
                    self::throwInvalidChoiceException();
                }
                break;
            case 'choose_type':
                if (!ctype_digit($choice) || !in_array($choice, self::getGameStateValueAsArray('type_array'))) {
                    self::throwInvalidChoiceException();
                }
                break;
            default:
                break;
        }
        self::setGameStateValue('choice', $choice);
        
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
        if (self::decodeGameType(self::getGameStateValue('game_type')) == 'team') {
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
        $player_id = self::getGameStateValue('active_player');
        $relic = self::getCardInfo(self::getGameStateValue('relic_id'));
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
                return self::getGameStateValue('artifacts_mode') > 1;
            // TODO(CITIES,ECHOES): Add other cases when we implement other expansions.
            default:
                return false;
        }
    }

    function argPlayerArtifactTurn() {
        $player_id = self::getGameStateValue('active_player');
        $card = self::getArtifactOnDisplay($player_id);
        return array(
            '_private' => array(
                'active' => array( // "Active" player only
                    "dogma_effect_info" => array($card['id'] => self::getDogmaEffectInfo($card, $player_id, /*is_on_display=*/ true)),
                )
            )
        );
    }

    function argPlayerTurn() {
        $player_id = self::getGameStateValue('active_player');
        return array(
            'i18n' => array('qualified_action'),
            'action_number' => self::getGameStateValue('first_player_with_only_one_action') || self::getGameStateValue('second_player_with_only_one_action') || self::getGameStateValue('has_second_action') ? 1 : 2,

            'qualified_action' => self::getGameStateValue('first_player_with_only_one_action') || self::getGameStateValue('second_player_with_only_one_action') ? clienttranslate('an action') :
                                  (self::getGameStateValue('has_second_action') ? clienttranslate('a first action') : clienttranslate('a second action')),
            'age_to_draw' => self::getAgeToDrawIn($player_id),
            'claimable_ages' => self::getClaimableAges($player_id),
            '_private' => array(
                'active' => array( // "Active" player only
                    "dogma_effect_info" => self::getDogmaEffectInfoOfTopCards($player_id)
                )
            )
        );
    }

    function getClaimableAges($player_id) {
        $unclaimed_achievements = self::getCardsInLocation(0, 'achievements');
        $age_max = self::getMaxAgeOnBoardTopCards($player_id);
        $player_score = self::getPlayerScore($player_id);
        $claimable_ages = array();
        foreach ($unclaimed_achievements as $achievement) {
            $age = $achievement['age'];
            if ($age === null) { // Special achievement
                continue;
            }
            // Rule: to achieve the age X, the player has to have a top card of his board of age >= X and 5*X points in his score pile
            if ($age <= $age_max && $player_score >= 5*$age) {
                $claimable_ages[] = $age;
            }
        }
        return $claimable_ages;
    }

    /** Returns dogma effect information about the top cards belonging to the specified player. */
    function getDogmaEffectInfoOfTopCards($launcher_id) {
        $dogma_effect_info = array();
        foreach (self::getTopCardsOnBoard($launcher_id) as $top_card) {
            $dogma_effect_info[$top_card['id']] = self::getDogmaEffectInfo($top_card, $launcher_id);
        }
        return $dogma_effect_info;
    }

    /** Returns dogma effect information of the specified card. */
    function getDogmaEffectInfo($card, $launcher_id, $is_on_display = false) {
        $dogma_effect_info = array();

        // Battleship Yamato cannot be triggered as a dogma effect, so we don't need to return anything
        if ($card['id'] == 188) {
            return $dogma_effect_info;
        }

        $dogma_icon = $card['dogma_icon'];
        $resource_column = 'player_icon_count_' . $dogma_icon;
        $extra_icons = $is_on_display ? self::countIconsOnCard($card, $dogma_icon) : 0;

        $dogma_effect_info['players_executing_i_compel_effects'] = [];
        $dogma_effect_info['players_executing_i_demand_effects'] = [];
        $dogma_effect_info['players_executing_non_demand_effects'] = [];

        if (self::isCompelEffect($card['id']) === true) {
            $dogma_effect_info['players_executing_i_compel_effects'] =
                self::getObjectListFromDB(self::format("
                    SELECT
                        player_id
                    FROM
                        player
                    WHERE
                        {col} >= {extra_icons} + (SELECT {col} FROM player WHERE player_id = {launcher_id})
                        AND player_team <> (SELECT player_team FROM player WHERE player_id = {launcher_id})
                ", array('col' => $resource_column, 'launcher_id' => $launcher_id, 'extra_icons' => $extra_icons)), true);
        } else if (self::getDemandEffect($card['id']) !== null) { 
            $dogma_effect_info['players_executing_i_demand_effects'] =
                self::getObjectListFromDB(self::format("
                        SELECT
                            player_id
                        FROM
                            player
                        WHERE
                            {col} < {extra_icons} + (SELECT {col} FROM player WHERE player_id = {launcher_id})
                            AND player_team <> (SELECT player_team FROM player WHERE player_id = {launcher_id})
                    ", array('col' => $resource_column, 'launcher_id' => $launcher_id, 'extra_icons' => $extra_icons)), true);
        }
        if (self::getNonDemandEffect($card['id'], 1) !== null) {
            $dogma_effect_info['players_executing_non_demand_effects'] =
                self::getObjectListFromDB(self::format("
                        SELECT player_id FROM player WHERE player_id = {launcher_id} OR {col} >= {extra_icons} + (SELECT {col} FROM player WHERE player_id = {launcher_id})
                    ", array('col' => $resource_column, 'launcher_id' => $launcher_id, 'extra_icons' => $extra_icons)), true);
        }

        $dogma_effect_info['no_effect'] = self::dogmaHasNoEffect(
            $card,
            $dogma_effect_info['players_executing_i_compel_effects'],
            $dogma_effect_info['players_executing_i_demand_effects'],
            $dogma_effect_info['players_executing_non_demand_effects']
        );

        return $dogma_effect_info;
    }

    /** Returns true if this dogma is guaranteed to have no effect. */
    function dogmaHasNoEffect($card, $i_compel_players, $i_demand_players, $non_demand_players) {

        $i_compel_will_be_executed = count($i_compel_players) > 0;
        $i_demand_will_be_executed = count($i_demand_players) > 0;
        $non_demand_will_be_executed = count($non_demand_players) > 0;

        if (!$i_demand_will_be_executed && !$i_compel_will_be_executed && !$non_demand_will_be_executed) {
            return true;
        }

        if ($card['id'] !== 48) {
            // self::throwInvalidChoiceException();
        }

        switch ($card['id']) {

            // id 20, age 2: Mapmaking
            case 20:
                // The non-demand has no effect unless the I demand is also executed.
                if (!$i_demand_will_be_executed) {
                    return true;
                }

            // id 38, age 4: Gunpowder
            case 38:
                // The non-demand has no effect unless the I demand is also executed.
                if (!$i_demand_will_be_executed) {
                    return true;
                }

            // id 48, age 5: The Pirate Code
            case 48:
                // The non-demand has no effect unless the I demand is also executed.
                if (!$i_demand_will_be_executed) {
                    return true;
                }

            // id 62, age 6: Vaccination
            case 62:
                // The non-demand has no effect unless the I demand is also executed.
                if (!$i_demand_will_be_executed) {
                    return true;
                }
        }

        return false;
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
        $player_name = self::getColoredText(self::getPlayerNameFromId($player_id), $player_id);
        $You = 'You';
        $you = 'you'; 
        $special_type_of_choice = self::getGameStateValue('special_type_of_choice');
        
        if (self::getGameStateValue('release_version') >= 1) {
            $nested_card_state = self::getCurrentNestedCardState();
            $card_id = $nested_card_state['card_id'];
            $current_effect_type = $nested_card_state['current_effect_type'];
            $current_effect_number = $nested_card_state['current_effect_number'];
        } else {
            $nested_id_1 = self::getGameStateValue('nested_id_1');
            $card_id = $nested_id_1 == -1 ? self::getGameStateValue('dogma_card_id') : $nested_id_1 ;
            $current_effect_type = $nested_id_1 == -1 ? self::getGameStateValue('current_effect_type') : 1 /* Non-demand effects only*/;
            $current_effect_number = $nested_id_1 == -1 ?  self::getGameStateValue('current_effect_number') : self::getGameStateValue('nested_current_effect_number_1');
        }
        
        $card = self::getCardInfo($card_id);
        $card_name = self::getCardName($card['id']);
        
        $can_pass = self::getGameStateValue('can_pass') == 1;
        $can_stop = self::getGameStateValue('n_min') <= 0;

        $step = self::getStep();
        $code = self::getCardExecutionCodeWithLetter($card_id, $current_effect_type, $current_effect_number, $step);
        
        if ($special_type_of_choice > 0) {
            switch(self::decodeSpecialTypeOfChoice($special_type_of_choice)) {
            case 'choose_opponent':
                $options = self::getObjectListFromDB(self::format("
                    SELECT
                        player_id AS value,
                        player_name AS text
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
                "
                ,
                    array('player_id' => $player_id)
                ));
                break;
            case 'choose_opponent_with_fewer_points':
                $options = self::getObjectListFromDB(self::format("
                    SELECT
                        player_id AS value,
                        player_name AS text
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
                        ) AND
                        player_innovation_score < (
                            SELECT
                                player_innovation_score
                            FROM
                                player
                            WHERE
                                player_id = {player_id}
                        )
                "
                ,
                    array('player_id' => $player_id)
                ));
                break;
            case 'choose_value':
                $options = array();
                foreach (self::getGameStateValueAsArray('age_array') as $age) {
                    $options[] = array('value' => $age, 'text' => self::getAgeSquare($age));
                }
                break;
            case 'choose_color':
            case 'choose_two_colors':
            case 'choose_three_colors':
                $options = array();
                foreach (self::getGameStateValueAsArray('color_array') as $color) {
                    $options[] = array('value' => $color, 'text' => self::getColorInClear($color));
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
                        player_no IN ({player_nos})
                ",
                    array('player_nos' => join(self::getGameStateValueAsArray('player_array'), ','))
                ));
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
                foreach (self::getGameStateValueAsArray('type_array') as $type) {
                    $options[] = array('value' => $type, 'text' => self::getPrintableStringForCardType($type));
                }
                break;
            default:
                break;
            }

            // The message to display is specific of the card
            $message_args_for_player = array('You' => 'You', 'you' => 'you');
            $message_args_for_others = array('player_name' => $player_name);
            
            //||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||
            // [S] SPECIFIC CODE: What is the special type of choice the player can make (message and values)?
            switch($code) {
            // id 18, age 2: Road building
            case "18N1B":
                $message_for_player = clienttranslate('${You} may choose another player to transfer your top red card to his board, then transfer his top green card to your board:');
                $message_for_others = clienttranslate('${player_name} may choose another player to transfer his own red card to that player\'s board, then transfer that player\'s top green card to his own board');
                break;
            
            // id 21, age 2: Canal building  
            case "21N1A":
                $message_for_player = clienttranslate('Do ${you} want to exchange all the highest cards in your hand with all the highest cards in your score pile?');
                $message_for_others = clienttranslate('${player_name} may exchange all his highest cards in his hand with all the highest cards in his score pile');
                $options = array(array('value' => 1, 'text' => clienttranslate("Yes")), array('value' => 0, 'text' => clienttranslate("No")));
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
                $age_10 = self::getAgeSquare(10);
                $icon_5 = self::getIconSquare(5);
                $message_args_for_player['age_6'] = $age_6;
                $message_args_for_player['age_10'] = $age_10;
                $message_args_for_player['icon_5'] =$icon_5;
                $message_args_for_others['age_6'] = $age_6;
                $message_args_for_others['age_10'] = $age_10;
                $message_args_for_others['icon_5'] =$icon_5;
                $message_for_player = $age_to_draw <= 10 ? clienttranslate('Do ${you} want to draw and tuck a ${age_6}, then score all your top cards without a ${icon_5}?')
                                                        : clienttranslate('Finish the game (attempt to draw above ${age_10})');
                $message_for_others = $age_to_draw <= 10 ? clienttranslate('${player_name} may draw and tuck a ${age_6}, then score all his top cards without a ${icon_5}')
                                                        : clienttranslate('${player_name} may finish the game (attempting to draw above ${age_10})');
                $options = array(array('value' => 1, 'text' => clienttranslate("Yes")), array('value' => 0, 'text' => clienttranslate("No")));
                break;
                
            // id 65, age 7: Evolution
            case "65N1A":
                $message_for_player = clienttranslate('${You} may make a choice');
                $message_for_others = clienttranslate('${player_name} may make a choice among the two possibilities offered by the card');
                $age_to_draw_for_score = self::getAgeToDrawIn($player_id, 8);
                $age_to_draw = self::getAgeToDrawIn($player_id, self::getMaxAgeInScore($player_id) + 1);
                $options = array(
                                array('value' => 1, 'text' => $age_to_draw_for_score <= 10 ? self::format(clienttranslate("Draw and score a {age}, then return a card from your score pile"), array('age' => self::getAgeSquare($age_to_draw_for_score)))
                                                                                            : self::format(clienttranslate("Finish the game (attempt to draw above {age_10})"), array('age_10' => self::getAgeSquare(10)))),
                                array('value' => 0, 'text' => $age_to_draw <= 10 ? self::format(clienttranslate("Draw a {age}"), array('age' => self::getAgeSquare($age_to_draw)))
                                                                                            : self::format(clienttranslate("Finish the game (attempt to draw above {age_10})"), array('age_10' => self::getAgeSquare(10))))
                );
                break;
            
            // id 66, age 7: Publications
            case "66N1A":
                $message_for_player = clienttranslate('${You} may rearrange one color of your cards. Click on a card then use arrows to move it within the pile');
                $message_for_others = clienttranslate('${player_name} may rearrange one color of his cards');
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
            
            // id 102, age 10: Stem cells 
            case "102N1A":
                $message_for_player = clienttranslate('Do ${you} want to score all the cards from your hand?');
                $message_for_others = clienttranslate('${player_name} may score all the cards from his hand');
                $options = array(array('value' => 1, 'text' => clienttranslate("Yes")), array('value' => 0, 'text' => clienttranslate("No")));
                break;

            // id 114, Artifacts age 1: Papyrus of Ani
            case "114N1B":
                $message_for_player = clienttranslate('${You} must choose a type');
                $message_for_others = clienttranslate('${player_name} must choose a type');
                break;

            // id 122, Artifacts age 1: Mask of Warka
            case "122N1A":
                $message_for_player = clienttranslate('${You} must choose a color');
                $message_for_others = clienttranslate('${player_name} must choose a color');
                break;

            // id 124, Artifacts age 1: Tale of the Shipwrecked Sailor
            case "124N1A":
                $message_for_player = clienttranslate('${You} must choose a color');
                $message_for_others = clienttranslate('${player_name} must choose a color');
                break;
            
            // id 126, Artifacts age 2: Rosetta Stone
            case "126N1A":
                $message_for_player = clienttranslate('${You} must choose a type');
                $message_for_others = clienttranslate('${player_name} must choose a type');
                break;

            case "126N1C":
                $message_for_player = clienttranslate('${You} must choose an opponent');
                $message_for_others = clienttranslate('${player_name} must choose an opponent');
                break;

            // id 147, Artifacts age 4: East India Company Charter
            case "147N1A":
                $message_for_player = clienttranslate('Choose a value');
                $message_for_others = clienttranslate('${player_name} must choose a value');
                break;
 
            // id 152, Artifacts age 5: Mona Lisa
            case "152N1A":
                $message_for_player = clienttranslate('Choose a number');
                $message_for_others = clienttranslate('${player_name} must choose a number');
                break;
                
            case "152N1B":
                $message_for_player = clienttranslate('Choose a color');
                $message_for_others = clienttranslate('${player_name} must choose a color');
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
                $age_10 = self::getAgeSquare(10);
                $message_args_for_player['age_1'] = $age_1;
                $message_args_for_player['age_10'] = $age_10;
                $message_args_for_others['age_1'] = $age_1;
                $message_args_for_others['age_10'] = $age_10;
                $message_for_player = $age_to_draw <= 10 ? clienttranslate('Do ${you} want to draw and tuck a ${age_1}?')
                                                        : clienttranslate('Finish the game (attempt to draw above ${age_10})');
                $message_for_others = $age_to_draw <= 10 ? clienttranslate('${player_name} may draw and tuck a ${age_1}')
                                                        : clienttranslate('${player_name} may finish the game (attempting to draw above ${age_10})');
                $options = array(array('value' => 1, 'text' => clienttranslate("Yes")), array('value' => 0, 'text' => clienttranslate("No")));
                break;
				
            default:
                // This should not happen
                throw new BgaVisibleSystemException(self::format(self::_("Unreferenced card effect code in section S: '{code}'"), array('code' => $code)));
            }
            //[SS]||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||
            
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
            
            return $args;
        }
        
        $splay_direction = self::getGameStateValue('splay_direction');
        $n_min = self::getGameStateValue("n_min");
        $n_max = self::getGameStateValue("n_max");
        $n = self::getGameStateValue("n");
        $owner_from = self::getGameStateValue("owner_from");
        if ($splay_direction == -1) {
            $location_from = self::decodeLocation(self::getGameStateValue("location_from"));
            $owner_to = self::getGameStateValue("owner_to");
            $location_to = self::decodeLocation(self::getGameStateValue("location_to"));
            $bottom_to = self::getGameStateValue("bottom_to");
            $age_min = self::getGameStateValue("age_min");
            $age_max = self::getGameStateValue("age_max");
            $with_icon = self::getGameStateValue("with_icon");
            $without_icon = self::getGameStateValue("without_icon");
            $with_demand_effect = self::getGameStateValue("has_demand_effect");
        }
        
        // Number of cards
        if ($n_min <= 0) {
            $n_min = 1;
        }

        $player_id_is_owner_from = $owner_from == $player_id;
        
        // Identification of the potential opponent(s)
        if ($splay_direction == -1 && ($owner_from == -2 || $owner_from == -3 || $owner_from == -4)) {
            $opponent_id = $owner_from;
        } else if ($splay_direction == -1 && ($owner_to == -2 || $owner_to == -3 || $owner_to == -4)) {
            $opponent_id = $owner_to;
        } else if ($splay_direction == -1 && $owner_from > 0 && $owner_from <> $player_id) {
            $opponent_id = $owner_from;
        } else if ($splay_direction == -1 && $owner_to > 0 && $owner_to <> $player_id) {
            $opponent_id = $owner_to;
        } else {
            $opponent_id = null;
        }
        
        if ($opponent_id === null) {
            $your = null;
            $opponent_name = null;
        } else if ($opponent_id > 0) {
            $your = 'your';
            $opponent_name = self::getColoredText(self::getPlayerNameFromId($opponent_id), $opponent_id);
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
            $cards = self::getRecursivelyTranslatedCardSelection($age_min, $age_max, $with_icon, $without_icon, $with_demand_effect);
        } else { // splay_direction <> -1
            $splayable_colors = self::getGameStateValueAsArray('color_array');
            $splayable_colors_in_clear = array();
            foreach ($splayable_colors as $color) {
                $splayable_colors_in_clear[] = self::getColorInClearWithCards($color);
            }
        }
        
        // Creation of the message
        if ($opponent_name === null || $opponent_id == -2 || $opponent_id == -3 || $opponent_id == -4) {
            if ($splay_direction == -1) {
                $messages = self::getTransferInfoWithOnePlayerInvolved($location_from, $location_to, $player_id_is_owner_from, $bottom_to, $you_must, $player_must, $player_name, $number, $cards, $opponent_name, $code);
                $splay_direction = null;
                $splay_direction_in_clear = null;
            } else {
                $messages = [
                    'message_for_player' => ['i18n' => ['log'], 'log' => $you_must, 'args' => ['You' => 'You']],
                    'message_for_others' => ['i18n' => ['log'], 'log' => $player_must, 'args' => ['player_name' => $player_name]],
                    'splayable_colors' => $splayable_colors,
                    'splayable_colors_in_clear' => $splayable_colors_in_clear
                ];
                $splay_direction_in_clear = self::getSplayDirectionInClear($splay_direction);
            }
        } else {
            $messages = self::getTransferInfoWithTwoPlayersInvolved($location_from, $location_to, $player_id_is_owner_from, $you_must, $player_must, $your, $player_name, $opponent_name, $number, $cards);
            $splay_direction = null;
            $splay_direction_in_clear = null;
        }
        
        if ($special_type_of_choice == 0 && $splay_direction == null && $location_from == 'score') {
            if ($owner_from == $player_id) {
                $must_show_score = true;
            } else if ($owner_from == -2) {
                $visible_cards = self::getVisibleSelectedCards($player_id);
                $must_show_score = false;
                foreach($visible_cards as $card) {
                    if ($card['owner'] == $player_id && $card['location'] == 'score') {
                        $must_show_score = true;
                        break;
                    }
                }
            } else {
                $must_show_score = false;
            }
        } else {
            $must_show_score = false;
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
            'color_pile' => $splay_direction === null && $location_from == 'pile' ? self::getGameStateValueAsArray('color_array')[0] : null,
            
            // Private info
            '_private' => array(
                'active' => array( // "Active" player only
                    "visible_selectable_cards" => self::getVisibleSelectedCards($player_id),
                    "selectable_rectos" => self::getSelectableRectos($player_id), // Most of the time, the player choose among versos he can see this array is empty so this array is empty except for few dogma effects
                    "must_show_score" => $must_show_score
                )
            ))
        );
        
        $args['i18n'][] = 'card_name';
        $args['i18n'][] = 'splay_direction_in_clear';
        
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
            $number_log = '${n_min} or ${n_max}';
        } else {
            $number_log = '${n_min} to ${n_max}';
        }
        return [
            'i18n' => ['log'],
            'log' => $number_log,
            'args' => [
                'i18n' => ['n_min', 'n_max'],
                'n_min' => self::getTranslatedNumber($n_min),
                'n_max' => self::getTranslatedNumber($n_max),
            ],
        ];
    }

    function getRecursivelyTranslatedCardSelection($age_min, $age_max, $with_icon, $without_icon, $with_demand_effect) {
        $card_args = array();

        $selectable_colors = self::getGameStateValueAsArray('color_array');
        if (count($selectable_colors) < 5) {
            $colors = self::getRecursivelyTranslatedColorList($selectable_colors);
            $card_log = clienttranslate('${color} ${cards}${of_age}${with_icon}${with_demand}');
            $card_args['color'] = $colors;
        } else {
            $card_log = clienttranslate('${cards}${of_age}${with_icon}${with_demand}');
        }
        $card_args['cards'] = clienttranslate('card(s)');
        $card_args['of_age'] = '';
        $card_args['with_icon'] = '';
        $card_args['with_demand'] = '';

        // TODO(ARTIFACTS): Figure out if we need to make any changes here to handle Battleship Yamato properly.
        if ($age_min != 1 || $age_max != 10) {
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

        if ($with_icon > 0) {
            $with_icon_log = clienttranslate(' with a ${[}${icon}${]}');
            $card_args['with_icon'] = [
                'i18n' => ['log'],
                'log' => $with_icon_log,
                'args' => array_merge(self::getDelimiterMeanings($with_icon_log), ['icon' => $with_icon]),
            ];
        } else if ($without_icon > 0) {
            $without_icon_log = clienttranslate(' without a ${[}${icon}${]}');
            $card_args['without_icon'] = [
                'i18n' => ['log'],
                'log' => $without_icon_log,
                'args' => array_merge(self::getDelimiterMeanings($without_icon_log), ['icon' => $without_icon]),
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
            $colors_in_clear[$i] = self::getColorInClear($colors[$i]);
        }
        switch (count($colors)) {
            case 1: 
                $color_log = '${color}';
                $color_args['color'] = self::getColorInClear($colors[0]);
                break;

            case 2:
                $color_log = '${color_1} or ${color_2}';
                $color_args['color_1'] = self::getColorInClear($colors[0]);
                $color_args['color_2'] = self::getColorInClear($colors[1]);
                break;

            case 3:
                $color_log = '${color_1}, ${color_2} or ${color_3}';
                $color_args['color_1'] = self::getColorInClear($colors[0]);
                $color_args['color_2'] = self::getColorInClear($colors[1]);
                $color_args['color_3'] = self::getColorInClear($colors[2]);
                break;

            case 4:
                $color_log = 'non-${color}';
                for ($color = 0; $color < 5; $color++) {
                    if (!in_array($color, $colors)) {
                        $color_args['color'] = self::getColorInClear($color);
                        break;
                    }
                }
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
    }
    
    function stWhoBegins() {
        self::setGameStateValue('turn0', 0); // End of turn 0
        
        $cards = self::getSelectedCards();
        // Deselect the cards
        self::deselectAllCards();
        
        // Execute the melds planned by players
        foreach($cards as $card) {
            $this->gamestate->changeActivePlayer($card['owner']);
            self::transferCardFromTo($card, $card['owner'], 'board');
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
        self::setGameStateValue('active_player', $player_id);
        self::setLauncherId($player_id);
        $this->gamestate->changeActivePlayer($player_id);
        self::setGameStateValue('current_action_number', 1);
        self::notifyGeneralInfo('<!--empty-->');
        self::trace('turn0->playerTurn');
        $this->gamestate->nextState();
    }
    
    function stInterPlayerTurn() {
        // An action of the player has been fully resolved.

        // Give him extra time for his actions to come
        self::giveExtraTime(self::getActivePlayerId());
        
        // Does he play again?
        if (self::getGameStateValue('release_version') >= 1 && self::getGameStateValue('current_action_number') == 0) {
            $next_player = false;
        } else if (self::getGameStateValue('first_player_with_only_one_action')) {
            // First turn: the player had only one action to make
            $next_player = true;
            self::setGameStateValue('first_player_with_only_one_action', 0);
        } else if (self::getGameStateValue('second_player_with_only_one_action')) {
            // 4 players at least and this is the second turn: the player had only one action to make
            $next_player = true;
            self::setGameStateValue('second_player_with_only_one_action', 0);
        } else if (self::getGameStateValue('has_second_action')) {
            // The player took his first action and has another one
            $next_player = false;
            self::setGameStateValue('has_second_action', 0);
        } else {
            // The player took his second action
            $next_player = true;
            self::setGameStateValue('has_second_action', 1);
        }
        if ($next_player) { // The turn for the current player is over
            // Reset the flags for Monument special achievement
            self::resetFlagsForMonument();
            
            // Activate the next non-eliminated player in turn order
            do {
                $this->activeNextPlayer();
            } while (self::isEliminated($this->getActivePlayerId()));
            $player_id = self::getActivePlayerId();
            self::setGameStateValue('active_player', $player_id);
            self::setLauncherId($player_id);

            // Get next player to decide what to do with their Artifact
            $card = self::getArtifactOnDisplay($player_id);
            if ($card !== null) {
                self::setGameStateValue('current_action_number', 0);
                self::notifyGeneralInfo('<!--empty-->');
                self::increaseResourcesForArtifactOnDisplay($player_id, $card);
                self::trace('interPlayerTurn->artifactPlayerTurn');
                $this->gamestate->nextState('artifactPlayerTurn');
                return;
            }
            if (self::getGameStateValue('release_version') >= 1) {
                self::setGameStateValue('current_action_number', 1);
            }
        } else {
            if (self::getGameStateValue('release_version') >= 1) {
                self::incGameStateValue('current_action_number', 1);
            }
        }
        self::notifyGeneralInfo('<!--empty-->');
        self::trace('interPlayerTurn->playerTurn');
        $this->gamestate->nextState('playerTurn');
    }
    
    function stDogmaEffect() {
        // An effect of a dogma has to be resolved
        if (self::getGameStateValue('release_version') >= 1) {
            $nested_card_state = self::getCurrentNestedCardState();
            $card_id = $nested_card_state['card_id'];
            $current_effect_type = $nested_card_state['current_effect_type'];
            $current_effect_number = $nested_card_state['current_effect_number'];
        } else {
            $current_effect_type = self::getGameStateValue('current_effect_type');
            $current_effect_number = self::getGameStateValue('current_effect_number');
            $card_id = self::getGameStateValue('dogma_card_id');
        }
        $card = self::getCardInfo($card_id);
        $qualified_effect = self::qualifyEffect($current_effect_type, $current_effect_number, $card);
        
        // Search for the first player who will undergo/share the effects, if any
        $launcher_id = self::getLauncherId();
        if (self::getGameStateValue('release_version') >= 1) {
            // Non-demand effects are not shared with other players.
            $first_player = $nested_card_state['nesting_index'] > 0 && $current_effect_type == 1 ? $launcher_id : self::getFirstPlayerUnderEffect($current_effect_type, $launcher_id);
        } else {
            $first_player = self::getFirstPlayerUnderEffect($current_effect_type, $launcher_id);
        }
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
        
        if (self::getGameStateValue('release_version') >= 1) {
            self::updateCurrentNestedCardState('current_player_id', $first_player);
        } else {
            self::setGameStateValue('current_player_under_dogma_effect', $first_player);
        }
        $this->gamestate->changeActivePlayer($first_player);
        
        // Begin the loop with this player
        self::trace('dogmaEffect->playerInvolvedTurn');
        $this->gamestate->nextState('playerInvolvedTurn');
    }
    
    function stInterDogmaEffect() {
        // A effect of a dogma card has been resolved. Is there another one?
        if (self::getGameStateValue('release_version') >= 1) {
            $nested_card_state = self::getCurrentNestedCardState();
            $card_id = $nested_card_state['card_id'];
            $current_effect_type = $nested_card_state['current_effect_type'];
        } else {
            $current_effect_type = self::getGameStateValue('current_effect_type');
            $card_id = self::getGameStateValue('dogma_card_id');
        }

        $launcher_id = self::getGameStateValue('active_player');
        
        // Indicate the potential new (non-demand) dogma to come
        if ($current_effect_type == 0 || $current_effect_type == 2) { // There is only ever one "I demand" or "I compel" effect per card
            $current_effect_number = 1; // Switch on the first non-demand dogma, if exists

            // Update statistics about I demand and I compel execution.
            if (self::getGameStateValue('release_version') >= 1 && self::getGameStateValue('current_nesting_index') == 0) {
                $affected_players = self::getObjectListFromDB("SELECT player_id FROM player WHERE effects_had_impact IS TRUE", true);
                foreach ($affected_players as $player_id) {
                    if ($current_effect_type == 0) {
                        self::incStat(1, 'i_demand_effects_number', $player_id);
                    } else {
                        self::incStat(1, 'i_compel_effects_number', $player_id);
                    }
                }
                if (count($affected_players) > 0) {
                    if ($current_effect_type == 0) {
                        self::incStat(1, 'dogma_actions_number_with_i_demand', $launcher_id);
                    } else {
                        self::incStat(1, 'dogma_actions_number_with_i_compel', $launcher_id);
                    }
                }
                // Reset 'effects_had_impact' so that it can be re-used for non-demand effects.
                self::DbQuery("UPDATE player SET effects_had_impact = FALSE");
            }
        } else {
            if (self::getGameStateValue('release_version') >= 1) {
                $current_effect_number = $nested_card_state['current_effect_number'] + 1; // Next non-demand dogma, if exists
            } else {
                $current_effect_number = self::getGameStateValue('current_effect_number') + 1; // Next non-demand dogma, if exists
            }
        }
        
        $card = self::getCardInfo($card_id);

        // If there isn't another dogma effect on the card
        if ($current_effect_number > 3 || self::getNonDemandEffect($card['id'], $current_effect_number) === null) {

            if (self::getGameStateValue('release_version') >= 1) {
                // Finish executing the card which triggered this one
                if (self::getGameStateValue('current_nesting_index') >= 1) {
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
                $nested_card_state = self::getNestedCardState(0);
                if ($nested_card_state['card_location'] == 'display') {
                    $launcher_id = $nested_card_state['launcher_id'];
                    self::transferCardFromTo($card, 0, 'deck');
                    self::giveExtraTime($launcher_id);
                }

                // Update statistics about which opponents shared in the non-demand effects
                $affected_players = self::getObjectListFromDB("SELECT player_id FROM player WHERE effects_had_impact IS TRUE", true);
                foreach ($affected_players as $player_id) {
                    if ($player_id != $launcher_id) {
                        self::incStat(1, 'sharing_effects_number', $player_id);
                    }
                }
            }
            
            // Stats
            if (self::getGameStateValue('release_version') < 1) {
                $i_demand_effects = false;
                $executing_players = self::getExecutingPlayers();
                foreach($executing_players as $player_id => $stronger_or_equal) {
                    if ($player_id == $launcher_id) {
                        continue;
                    }
                    if ($stronger_or_equal) { // This player had effectively shared some dogma effects of the card
                        self::incStat(1, 'sharing_effects_number', $player_id);
                    }
                    else { // The card had an I demand effect, and at least one player executed it with effects
                        self::incStat(1, 'i_demand_effects_number', $player_id);
                        $i_demand_effects = true;
                    }
                }
                if ($i_demand_effects) {
                    self::incStat(1, 'dogma_actions_number_with_i_demand', $launcher_id);
                }
            }

            // Award the sharing bonus if needed
            $sharing_bonus = self::getGameStateValue('sharing_bonus');
            if ($sharing_bonus == 1) {
                self::incStat(1, 'dogma_actions_number_with_sharing', $launcher_id);
                self::notifyGeneralInfo('<span class="minor_information">${text}</span>', array('i18n'=>array('text'), 'text'=>clienttranslate('Sharing bonus.')));
                $player_who_launched_the_dogma = self::getGameStateValue('active_player');
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
            
            // [R] Disable the flags used when in dogma 
            if (self::getGameStateValue('release_version') >= 1) {
                // NOTE: The implementation of Dancing Girl relies on the fact that the auxiliary_value is initialized to -1.
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
            } else {
                self::setGameStateValue('current_player_under_dogma_effect', -1);
                self::setGameStateValue('dogma_card_id', -1);
                self::setGameStateValue('current_effect_type', -1);
                self::setGameStateValue('current_effect_number', -1);
                for($i=1; $i<=9; $i++) {
                    self::setGameStateInitialValue('nested_id_'.$i, -1);
                    self::setGameStateInitialValue('nested_current_effect_number_'.$i, -1);
                }
                self::setAuxiliaryValue(-1);
            }
            self::setGameStateValue('sharing_bonus', -1);
            self::setStep(-1);
            self::setStepMax(-1);
            self::setGameStateValue('special_type_of_choice', -1);
            self::setGameStateValue('choice', -1);
            self::setGameStateValue('splay_direction', -1);
            self::setGameStateValue('n_min', -1);
            self::setGameStateValue('n_max', -1);
            self::setGameStateValue('solid_constraint', -1);
            self::setGameStateValue('owner_from', -1);
            self::setGameStateValue('location_from', -1);
            self::setGameStateValue('owner_to', -1);
            self::setGameStateValue('location_to', -1);
            self::setGameStateValue('bottom_to', -1);
            self::setGameStateValue('age_min', -1);
            self::setGameStateValue('age_max', -1);
            self::setGameStateValue('age_array', -1);
            self::setGameStateValue('color_array', -1);
            self::setGameStateValue('type_array', -1);
            self::setGameStateValue('player_array', -1);
            self::setGameStateValue('with_icon', -1);
            self::setGameStateValue('without_icon', -1);
            self::setGameStateValue('not_id', -1);
            self::setGameStateValue('card_id_1', -1);
            self::setGameStateValue('card_id_2', -1);
            self::setGameStateValue('card_id_3', -1);
            self::setGameStateValue('icon_hash_1', -1);
            self::setGameStateValue('icon_hash_2', -1);
            self::setGameStateValue('icon_hash_3', -1);
            self::setGameStateValue('icon_hash_4', -1);
            self::setGameStateValue('icon_hash_5', -1);
            self::setGameStateValue('enable_autoselection', -1);
            self::setGameStateValue('include_relics', -1);
            self::setGameStateValue('can_pass', -1);
            self::setGameStateValue('n', -1);
            self::setGameStateValue('id_last_selected', -1);
            self::setGameStateValue('age_last_selected', -1);
            self::setGameStateValue('color_last_selected', -1);
            self::setGameStateValue('owner_last_selected', -1);
            self::setGameStateValue('score_keyword', -1);
            self::setGameStateValue('require_achievement_eligibility', -1);
            self::setGameStateValue('has_demand_effect', -1);
            self::setGameStateValue('has_splay_direction', -1);

            // End of this player action
            self::trace('interDogmaEffect->interPlayerTurn');
            $this->gamestate->nextState('interPlayerTurn');
            return;
        }
        
        // There is another (non-demand) effect to perform
        if (self::getGameStateValue('release_version') >= 1) {
            self::updateCurrentNestedCardState('current_effect_type', 1);
            self::updateCurrentNestedCardState('current_effect_number', $current_effect_number);
        } else {
            self::setGameStateValue('current_effect_type', 1);
            self::setGameStateValue('current_effect_number', $current_effect_number);
        }
        
        // Jump to this effect
        self::trace('interDogmaEffect->dogmaEffect');
        $this->gamestate->nextState('dogmaEffect');
    }
    
    function stPlayerInvolvedTurn() {
        // A player must or can undergo/share an effect of a dogma card
        $player_id = self::getCurrentPlayerUnderDogmaEffect();
        $launcher_id = self::getLauncherId();

        if (self::getGameStateValue('release_version') >= 1) {
            $nested_card_state = self::getCurrentNestedCardState();
            $card_id = $nested_card_state['card_id'];
            $current_effect_type = $nested_card_state['current_effect_type'];
            $current_effect_number = $nested_card_state['current_effect_number'];
        } else {
            $nested_id_1 = self::getGameStateValue('nested_id_1');
            $card_id = $nested_id_1 == -1 ? self::getGameStateValue('dogma_card_id') : $nested_id_1 ;
            $current_effect_type = $nested_id_1 == -1 ? self::getGameStateValue('current_effect_type') : 1 /* Non-demand effects only*/;
            $current_effect_number = $nested_id_1 == -1 ?  self::getGameStateValue('current_effect_number') : self::getGameStateValue('nested_current_effect_number_1');
        }
        $step_max = null;
        $step = null;
        
        if (self::getGameStateValue('release_version') >= 1) {
            if ($nested_card_state['post_execution_index'] == 0) {
                $qualified_effect = self::qualifyEffect($current_effect_type, $current_effect_number, self::getCardInfo($card_id));      
                self::notifyEffectOnPlayer($qualified_effect, $player_id, $launcher_id);
            }
        } else {
            $qualified_effect = self::qualifyEffect($current_effect_type, $current_effect_number, self::getCardInfo($card_id));      
            self::notifyEffectOnPlayer($qualified_effect, $player_id, $launcher_id);
        }
        
        $crown = self::getIconSquare(1);
        $leaf = self::getIconSquare(2);
        $lightbulb = self::getIconSquare(3);
        $tower = self::getIconSquare(4);
        $factory = self::getIconSquare(5);
        $clock = self::getIconSquare(6);
        
        try {
            //||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||
            // [A] SPECIFIC CODE: what are the automatic actions to make and/or is there interaction needed?
            $code = self::getCardExecutionCode($card_id, $current_effect_type, $current_effect_number);
            self::trace('[A]'.$code.' '.self::getPlayerNameFromId($player_id).'('.$player_id.')'.' | '.self::getPlayerNameFromId($launcher_id).'('.$launcher_id.')');
            switch($code) {
            // The first number is the id of the card
            // D1 means the first (and single) I demand effect
            // N1 means the first non-demand effect
            // N2 means the second non-demand effect
            // N3 means the third non-demand effect
            
            // Setting the $step_max variable means there is interaction needed with the player
            
            // id 0, age 1: Pottery
            case "0N1":
                $step_max = 1; // --> 1 interaction: see B
                break;
            case "0N2":
                self::executeDraw($player_id, 1); // "Draw a 1"
                break;

            // id 1, age 1: Tools
            case "1N1":
                $step_max = 1; // --> 1 interaction: see B
                break;
            case "1N2":
                $step_max = 1; // --> 1 interaction: see B
                break;
                
            // id 2, age 1: Writing
            case "2N1":
                self::executeDraw($player_id, 2); // "Draw a 2"
                break;
                
            // id 3, age 1: Archery
            case "3D1":
                self::executeDraw($player_id, 1); // "Draw a 1"
                $step_max = 1; // --> 1 interaction: see B
                break;

            // id 4, age 1: Metalworking
            case "4N1":
                while(true) {
                    $card = self::executeDraw($player_id, 1, 'revealed'); // "Draw and reveal a 1"
                    if (self::hasRessource($card, 4)) { // "If it has a tower"
                        self::notifyGeneralInfo(clienttranslate('It has a ${tower}.'), array('tower' => $tower));
                        self::scoreCard($card, $player_id); // "Score it"
                        continue; // "Repeat this dogma effect"
                    }
                    break; // "Otherwise"        
                }
                self::notifyGeneralInfo(clienttranslate('It does not have a ${tower}.'), array('tower' => $tower));
                self::transferCardFromTo($card, $player_id, 'hand'); // "Keep it"
                break;
            
            // id 5, age 1: Oars
            case "5D1":
                if (self::getGameStateValue('release_version') >= 1) {
                    do {
                        $card_transfered = false;
                        foreach (self::getCardsInHand($player_id) as $card) {
                            // "I demand you transfer a card with a crown from your hand to my score pile"
                            if (self::hasRessource($card, 1)) {
                                self::transferCardFromTo($card, $launcher_id, 'score');
                                self::executeDraw($player_id, 1); // "If you do, draw a 1"
                                $card_transfered = true; // "and repeat this dogma effect"
                                break;
                            }
                        }
                    } while ($card_transfered && self::getGameStateValue('game_rules') == 1);
                } else {
                    if (self::getAuxiliaryValue() == -1) { // If this variable has not been set before
                        self::setAuxiliaryValue(0);
                    }
                    $step_max = 1; // --> 1 interaction: see B
                }
                break;
            
            case "5N1":
                if (self::getAuxiliaryValue() <= 0) { // "If no cards were transfered due to this demand"
                    self::executeDraw($player_id, 1); // "Draw a 1"
                }
                break;
            
            // id 6, age 1: Clothing
            case "6N1":
                $step_max = 1; // --> 1 interaction: see B
                break;
            case "6N2":
                // "Score a 1 for each color present on your board not present on any other player board"
                // Compute the number of specific colors
                $number_to_be_scored = 0;
                $players = self::loadPlayersBasicInfos();
                $boards = self::getAllBoards($players);
                for ($color = 0; $color < 5; $color++) { // Evaluate each color
                    if (count($boards[$player_id][$color]) == 0) { // The player does not have this color => no point
                        continue;
                    }
                    // The player has this color, do opponents have?
                    $color_on_opponent_board = false;
                    foreach($players as $other_player_id => $player) {
                        if ($other_player_id == $player_id || $other_player_id == self::getPlayerTeammate($player_id)) {
                            continue; // Skip the player being evaluated and his teammate
                        }
                        if (count($boards[$other_player_id][$color]) > 0) { // This opponent has this color => no point
                            $color_on_opponent_board = true;
                            break;
                        }
                    }
                    if (!$color_on_opponent_board) { // The opponents do not have this color => point
                        $number_to_be_scored++;
                    }
                }
                // Indicate this number
                $translated_number = self::getTranslatedNumber($number_to_be_scored);
                self::notifyPlayer($player_id, 'log', clienttranslate('${You} have ${n} color(s) present on your board not present on any opponent\'s board.'), array('i18n' => array('n'), 'You' => 'You', 'n' => $translated_number));
                self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} has ${n} color(s) present on his board not present on any of his opponents\' boards.'), array('i18n' => array('n'), 'player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id), 'n' => $translated_number));

                // Score this number of times
                for ($i = 0; $i < $number_to_be_scored; $i++) {
                    self::executeDraw($player_id, 1, 'score');
                }
                break;
            
            // id 7, age 1: Sailing
            case "7N1":
                self::executeDraw($player_id, 1, 'board'); // "Draw and meld a 1"
                break;
                
            // id 8, age 1: The wheel
            case "8N1":
                self::executeDraw($player_id, 1); // "Draw two 1"
                self::executeDraw($player_id, 1); // 
                break;
                
            // id 9, age 1: Agriculture
            case "9N1":
                $step_max = 1; // --> 1 interaction: see B
                break;
            
            // id 10, age 1: Domestication
            case "10N1":
                $step_max = 1; // --> 1 interaction: see B
                break;
            
            // id 11, age 1: Masonry
            case "11N1":
                $step_max = 1; // --> 1 interaction: see B
                break;
                
            // id 12, age 1: City states
            case "12D1":
                $number_of_towers = self::getUniqueValueFromDB(self::format("
                SELECT
                    player_icon_count_4
                FROM
                    player
                WHERE
                    player_id = {player_id}
                ",
                    array('player_id' => $player_id)
                ));
                
                if ($number_of_towers >= 4) { // "If you have at least four towers on your board"
                    self::notifyPlayer($player_id, 'log', clienttranslate('${You} have at least four ${icon} on your board.'), array('You' => 'You', 'icon' => $tower));
                    self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} has at least four ${icon} on his board.'), array('player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id), 'icon' => $tower));
                    $step_max = 1; // --> 1 interaction: see B
                }
                else {
                    self::notifyPlayer($player_id, 'log', clienttranslate('${You} have less than four ${icon} on your board.'), array('You' => 'You', 'icon' => $tower));
                    self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} has less than four ${icon} on his board.'), array('player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id), 'icon' => $tower));
                }
                break;
                
            // id 13, age 1: Code of laws
            case "13N1":
                $step_max = 1; // --> 1 interaction: see B
                break;
                
            // id 14, age 1: Mysticism
            case "14N1":
                $card = self::executeDraw($player_id, 1, 'revealed'); // "Draw and reveal a 1
                $color = $card['color'];
                if (self::hasThisColorOnBoard($player_id, $color)) { // "If it is the same color of any card on your board"
                    self::notifyPlayer($player_id, 'log', clienttranslate('This card is ${color}; ${you} have this color on your board.'), array('i18n' => array('color'), 'you' => 'you', 'color' => self::getColorInClear($color)));
                    self::notifyAllPlayersBut($player_id, 'log', clienttranslate('This card is ${color}; ${player_name} has this color on his board.'), array('i18n' => array('color'), 'player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id), 'color' => self::getColorInClear($color)));
                    self::transferCardFromTo($card, $player_id, 'board'); // "Meld it"
                    self::executeDraw($player_id, 1); // "Draw a 1"
                }
                else {
                    self::notifyPlayer($player_id, 'log', clienttranslate('This card is ${color}; ${you} do not have this color on your board.'), array('i18n' => array('color'), 'you' => 'you', 'color' => self::getColorInClear($color)));
                    self::notifyAllPlayersBut($player_id, 'log', clienttranslate('This card is ${color}; ${player_name} does not have this color on his board.'), array('i18n' => array('color'), 'player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id), 'color' => self::getColorInClear($color)));
                    self::transferCardFromTo($card, $player_id, 'hand'); // (Put the card in your hand)
                }
                break;
                
            // id 15, age 2: Calendar
            case "15N1":
                if (self::countCardsInLocation($player_id, 'score') > self::countCardsInLocation($player_id, 'hand')) { // "If you have more cards in your score pile than in your hand"
                    self::notifyPlayer($player_id, 'log', clienttranslate('${You} have more cards in your score pile than in your hand.'), array('You' => 'You'));
                    self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} has more cards in his score pile than in his hand.'), array('player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id)));
                    
                    self::executeDraw($player_id, 3); // "Draw two 3"
                    self::executeDraw($player_id, 3); // 
                }
                else {
                    self::notifyPlayer($player_id, 'log', clienttranslate('${You} do not have more cards in your score pile than in your hand.'), array('You' => 'You'));
                    self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} does not have more cards in his score pile than in his hand.'), array('player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id)));
                }
                break;
                
            // id 16, age 2: Mathematics
            case "16N1":
                $step_max = 1; // --> 1 interaction: see B
                break;
                
            // id 17, age 2: Construction
            case "17D1":
                $step_max = 1; // --> 1 interaction: see B
                break;
                
            case "17N1":
                $boards = self::getAllBoards(self::loadPlayersBasicInfos());
                $eligible = true;
                foreach($boards as $current_id => $board) {
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
                    if ($achievement['owner'] == 0) {
                        self::notifyPlayer($player_id, 'log', clienttranslate('${You} are the only player with five top cards.'), array('You' => 'You'));
                        self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} is the only player with five top cards.'), array('player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id)));
                        self::transferCardFromTo($achievement, $player_id, 'achievements');  // "Claim the Empire achievement"
                    }
                    else {
                        self::notifyPlayer($player_id, 'log', clienttranslate('${You} are the only player with five top cards but the Empire achievement has already been claimed.'), array('You' => 'You'));
                        self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} is the only player with five top cards but the Empire achievement has already been claimed.'), array('player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id)));
                    }
                }
                break;
            
            // id 18, age 2: Road building
            case "18N1":
                $step_max = 1; // --> 1 interaction: see B
                break;
                
            // id 19, age 2: Currency
            case "19N1":
                self::setAuxiliaryValueFromArray(array());
                $step_max = 1; // --> 1 interaction: see B
                break;
                
            // id 20, age 2: Mapmaking
            case "20D1":
                if (self::getAuxiliaryValue() == -1) { // If this variable has not been set before
                    self::setAuxiliaryValue(0);
                }
                $step_max = 1; // --> 1 interaction: see B
                break;
            
            case "20N1":
                if (self::getAuxiliaryValue() == 1) { // "If any card was transfered due to the demand"
                    self::executeDraw($player_id, 1, 'score'); // "Draw and score a 1"
                }
                break;
                
            // id 21, age 2: Canal building         
            case "21N1":
                if (self::countCardsInLocation($player_id, 'score') == 0 && self::countCardsInLocation($player_id, 'hand') == 0) {
                    self::notifyPlayer($player_id, 'log', clienttranslate('${You} have no cards in your hand or score pile to exchange.'), array('You' => 'You'));
                    self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} has no cards in their hand or score pile to exchange.'), array('player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id)));
                } else {
                    $step_max = 1; // --> 1 interaction: see B
                }
                break;
                
            // id 22, age 2: Fermenting        
            case "22N1":
                if (self::getGameStateValue('game_rules') == 1) { // Last edition
                    $number = 0;
                    for($color=0; $color<5; $color++) {
                        if (self::boardPileHasRessource($player_id, $color, 2 /* leaf */)) { // There is at least one visible leaf in that color
                            $number++;
                        }
                    }
                    if ($number <= 1) {
                        self::notifyPlayer($player_id, 'log', clienttranslate('${You} have ${n} color with one or more visible ${leaves}.'), array('i18n' => array('n'), 'You' => 'You', 'n' => self::getTranslatedNumber($number), 'leaves' => $leaf));
                        self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} has ${n} color with one or more ${leaves}.'), array('i18n' => array('n'), 'player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id), 'n' => self::getTranslatedNumber($number), 'leaves' => $leaf));
                    }
                    else { // $number > 1
                        self::notifyPlayer($player_id, 'log', clienttranslate('${You} have ${n} colors with one or more visible ${leaves}.'), array('i18n' => array('n'), 'You' => 'You', 'n' => self::getTranslatedNumber($number), 'leaves' => $leaf));
                        self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} has ${n} colors with one or more ${leaves}.'), array('i18n' => array('n'), 'player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id), 'n' => self::getTranslatedNumber($number), 'leaves' => $leaf));
                    }
                    // "For each color of your board that have one leaf or more"
                }
                else {
                    $number_of_leaves = self::getPlayerSingleRessourceCount($player_id, 2 /* leaf */);
                    self::notifyPlayer($player_id, 'log', clienttranslate('${You} have ${n} ${leaves}.'), array('You' => 'You', 'n' => $number_of_leaves, 'leaves' => $leaf));
                    self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} has ${n} ${leaves}.'), array('player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id), 'n' => $number_of_leaves, 'leaves' => $leaf));
                    $number = self::intDivision($number_of_leaves,2); // "For every two leaves on your board"
                }
                
                for($i=0; $i<$number; $i++) {
                    self::executeDraw($player_id, 2); // "Draw a 2"
                }
                break;
                
            // id 23, age 2: Monotheism        
            case "23D1":
                $step_max = 1; // --> 1 interaction: see B
                break;
            
            case "23N1":
                self::executeDrawAndTuck($player_id, 1); // "Draw and tuck a 1"
                break;
                
            // id 24, age 2: Philosophy        
            case "24N1":
                $step_max = 1; // --> 1 interaction: see B
                break;
            
            case "24N2":
                $step_max = 1; // --> 1 interaction: see B
                break;
                
            // id 25, age 3: Alchemy        
            case "25N1":
                $number_of_towers = self::getPlayerSingleRessourceCount($player_id, 4);
                self::notifyPlayer($player_id, 'log', clienttranslate('${You} have ${n} ${towers}.'), array('You' => 'You', 'n' => $number_of_towers, 'towers' => $tower));
                self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} has ${n} ${towers}.'), array('player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id), 'n' => $number_of_towers, 'towers' => $tower));
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
                    self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} drew a red card.'), array('player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id)));

                    $step_max = 1; // --> 1 interactions: see B
                }
                else { // "Otherwise"
                    self::notifyPlayer($player_id, 'log', clienttranslate('${You} did not draw a red card.'), array('You' => 'You'));
                    self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} did not draw a red card.'), array('player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id)));
                    foreach($cards as $card) {
                        self::transferCardFromTo($card, $player_id, 'hand'); // "Keep them" (ie place them in your hand)
                    }
                }
                break;
                
            case "25N2":
                $step_max = 2; // --> 2 interactions: see B
                break;
                
            // id 26, age 3: Translation        
            case "26N1":
                $step_max = 1; // --> 1 interactions: see B
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
                    if ($achievement['owner'] == 0) {
                        self::notifyPlayer($player_id, 'log', clienttranslate('Each top card on ${your} board has a ${crown}.'), array('your' => 'your', 'crown' => $crown));
                        self::notifyAllPlayersBut($player_id, 'log', clienttranslate('Each top card on ${player_name} board has a ${crown}.'), array('player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id), 'crown' => $crown));
                        self::transferCardFromTo($achievement, $player_id, 'achievements');  // "Claim the World achievement"
                    }
                    else {
                        self::notifyPlayer($player_id, 'log', clienttranslate('Each top card on ${your} board has a ${crown} but the Empire achievement has already been claimed.'), array('your' => 'your', 'crown' => $crown));
                        self::notifyAllPlayersBut($player_id, 'log', clienttranslate('Each top card on ${player_name} board has a ${crown} but the World achievement has already been claimed.'), array('player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id), 'crown' => $crown));
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
                    self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} has no top card with a ${tower} on his board.'), array('player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id), 'tower' => $tower));
                }
                break;
                
            case "27N1":
                $step_max = 1; // --> 1 interactions: see B
                break;
                
            // id 28, age 3: Optics        
            case "28N1":
                $card = self::executeDraw($player_id, 3, 'board'); // "Draw and meld a 3"
                if (self::hasRessource($card, 1)) { // "If it has a crown"
                    self::notifyGeneralInfo(clienttranslate('It has a ${crown}.'), array('crown' => $crown));
                    self::executeDraw($player_id, 4, 'score'); // "Draw and score a 4"
                } else { // "Otherwise"
                    self::notifyGeneralInfo(clienttranslate('It does not have a ${crown}.'), array('crown' => $crown));
                    if (empty(self::getActiveOpponentsWithFewerPoints($player_id))) {
                        self::notifyPlayer($player_id, 'log', clienttranslate('There is no opponent who has fewer points than ${you}.'), array('you' => 'you'));
                        self::notifyAllPlayersBut($player_id, 'log', clienttranslate('There is no opponent who has fewer points than ${player_name}.'), array('player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id)));
                    } else {
                        $step_max = 2;
                    }
                }
                break;
                
            // id 29, age 3: Compass
            case "29D1":
                $step_max = 2; // --> 2 interactions: see B
                break;
                
            // id 30, age 3: Paper        
            case "30N1":
                $step_max = 1; // --> 1 interaction: see B
                break;
                
            case "30N2":
                $number_of_colors_splayed_left = 0;
                for($color = 0; $color < 5 ; $color++) {
                    if (self::getCurrentSplayDirection($player_id, $color) == 1 /* left */) {
                        $number_of_colors_splayed_left++;
                    }
                }
                if ($number_of_colors_splayed_left < 2) {
                    self::notifyPlayer($player_id, 'log', clienttranslate('${You} have ${n} color splayed left.'), array('i18n' => array('n'), 'You' => 'You', 'n' => $number_of_colors_splayed_left));
                            self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} has ${n} color splayed left.'), array('i18n' => array('n'), 'player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id), 'n' => $number_of_colors_splayed_left));
                }
                else {
                    self::notifyPlayer($player_id, 'log', clienttranslate('${You} have ${n} colors splayed left.'), array('i18n' => array('n'), 'You' => 'You', 'n' => $number_of_colors_splayed_left));
                            self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} has ${n} colors splayed left.'), array('i18n' => array('n'), 'player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id), 'n' => $number_of_colors_splayed_left));
                }
                
                for($i=0;$i<$number_of_colors_splayed_left;$i++) { // For every color you have splayed left
                    self::executeDraw($player_id, 4); // Draw a 4
                }
                break;
                
            // id 31, age 3: Machinery        
            case "31D1":
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
                
            case "31N1":
                $step_max = 2; // --> 2 interactions: see B
                break;
                
            // id 32, age 3: Medicine        
            case "32D1":
                $step_max = 2; // --> 2 interactions: see B
                break;
                
            // id 33, age 3: Education        
            case "33N1":
                $step_max = 1; // --> 1 interaction: see B
                break;
                
            // id 34, age 3: Feudalism        
            case "34D1":
                $step_max = 1; // --> 1 interaction: see B
                break;
                
            case "34N1":
                $step_max = 1; // --> 1 interaction: see B
                break;
                
            // id 35, age 4: Experimentation        
            case "35N1":
                self::executeDraw($player_id, 5, 'board'); // "Draw and meld a 5"
                break;
                
            // id 36, age 4: Printing press        
            case "36N1":
                $step_max = 1; // --> 1 interaction: see B
                break;
                
            case "36N2":
                $step_max = 1; // --> 1 interaction: see B
                break;
                
            // id 37, age 4: Colonialism
            case "37N1":
                do {
                    $card = self::executeDrawAndTuck($player_id, 3); // "Draw and tuck a 3"
                } while (self::hasRessource($card, 1 /* crown */)); // "If it has a crown, repeat this dogma effect"
                break;
            
            // id 38, age 4: Gunpowder
            case "38D1":
                if (self::getAuxiliaryValue() == -1) { // If this variable has not been set before
                    self::setAuxiliaryValue(0);
                }
                $step_max = 1; // --> 1 interaction: see B
                break;
                
            case "38N1":
                if (self::getAuxiliaryValue() == 1) { // "If any card was transfered due to the demand"
                    self::executeDraw($player_id, 2, 'score'); // "Draw and score a 2"
                }
                break;
                
            // id 39, age 4: Invention
            case "39N1":
                $step_max = 1; // --> 1 interaction: see B
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
                    if ($achievement['owner'] == 0) {
                        self::notifyPlayer($player_id, 'log', clienttranslate('${You} have all your five colors splayed.'), array('You' => 'You'));
                        self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} has all his five colors splayed.'), array('player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id)));
                        self::transferCardFromTo($achievement, $player_id, 'achievements');  // "Claim the Wonder achievement"
                    }
                    else {
                        self::notifyPlayer($player_id, 'log', clienttranslate('${You} have all your five colors splayed but the Wonder achievement has already been claimed.'), array('You' => 'You'));
                        self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} has all his five colors splayed but the Wonder achievement has already been claimed.'), array('player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id)));
                    }
                }
                break;
                
            // id 40, age 4: Navigation
            case "40D1":
                $step_max = 1; // --> 1 interaction: see B
                break;
            
            // id 41, age 4: Anatomy
            case "41D1":
                $step_max = 1; // --> 1 interaction: see B
                break;
                
            // id 42, age 4: Perspective
            case "42N1":
                $step_max = 1; // --> 1 interaction: see B
                break;
                
            // id 43, age 4: Enterprise
            case "43D1":
                $step_max = 1; // --> 1 interaction: see B
                break;
            
            case "43N1":
                $step_max = 1; // --> 1 interaction: see B
                break;
                
            // id 44, age 4: Reformation
            case "44N1":
                $step_max = 1; // --> 1 interaction: see B
                break;
            
            case "44N2":
                $step_max = 1; // --> 1 interaction: see B
                break;
                
            // id 45, age 5: Chemistry
            case "45N1":
                $step_max = 1; // --> 1 interaction: see B
                break;
            
            case "45N2":
                // "Draw and score a card of value one higher than the highest top card on your board"
                self::executeDraw($player_id, self::getMaxAgeOnBoardTopCards($player_id) + 1, 'score');
                $step_max = 1; // --> 1 interaction: see B
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
                    $step_max = 1; // --> 1 interactions: see B
                    self::notifyPlayer($player_id, 'log', clienttranslate('${You} drew two cards of the same color.'), array('You' => 'You'));
                    self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} drew two cards of the same color.'), array('player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id)));
                }
                else { // "Otherwise"
                    self::notifyPlayer($player_id, 'log', clienttranslate('All the cards ${you} drew have different colors.'), array('you' => 'you'));
                    self::notifyAllPlayersBut($player_id, 'log', clienttranslate('All the cards ${player_name} drew have different colors.'), array('player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id)));
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
                $step_max = 1; // --> 1 interaction: see B
                break;
                
            case "47N3":
                $step_max = 1; // --> 1 interaction: see B
                break;
                
            // id 48, age 5: The pirate code
            case "48D1":
                if (self::getAuxiliaryValue() == -1) { // If this variable has not been set before
                    self::setAuxiliaryValue(0);
                }
                $step_max = 1; // --> 1 interaction: see B
                break;

            case "48N1":
                if (self::getAuxiliaryValue() == 1) { // "If any card was transfered due to the demand"
                    $step_max = 1; // --> 1 interaction: see B
                }
                break;
                
            // id 49, age 5: Banking
            case "49D1":
                $step_max = 1; // --> 1 interaction: see B
                break;

            case "49N1":
                $step_max = 1; // --> 1 interaction: see B
                break;
                
            // id 50, age 5: Measurement
            case "50N1":
                $step_max = 1; // --> 1 interaction: see B
                break;
            
            // id 51, age 5: Statistics
            case "51D1":
                if (self::getGameStateValue('game_rules') == 1) { // Last edition
                    // Get highest cards in score
                    $ids_of_highest_cards_in_score = self::getIdsOfHighestCardsInLocation($player_id, 'score');

                    // Make the transfers
                    foreach($ids_of_highest_cards_in_score as $id) {
                        $card = self::getCardInfo($id);
                        self::transferCardFromTo($card, $player_id, 'hand'); // "Transfer all the highest cards in your score pile to your hand"
                    }
                }
                else { // First edition
                    $step_max = 1; // --> 1 interaction: see B
                }
                break;

            case "51N1":
                $step_max = 1; // --> 1 interaction: see B
                break;
            
            // id 52, age 5: Steam engine
            case "52N1":
                self::executeDrawAndTuck($player_id, 4); // "Draw and tuck two 4s"
                self::executeDrawAndTuck($player_id, 4); //
                $card = self::getBottomCardOnBoard($player_id, 3 /* yellow */);
                if ($card !== null) {
                    self::scoreCard($card, $player_id);
                }
                break;
            
            // id 53, age 5: Astronomy
            case "53N1":
                while (true) {
                    $card = self::executeDraw($player_id, 6, 'revealed'); // "Draw and reveal a 6"
                    self::notifyGeneralInfo(clienttranslate('This card is ${color}.'), array('i18n' => array('color'), 'color' => self::getColorInClear($card['color'])));
                    if ($card['color'] != 0 /* blue */ && $card['color'] != 2 /* green */) {
                        break; // "Otherwise"
                    };
                    // "If the card is green or blue"
                    self::transferCardFromTo($card, $player_id, 'board'); // "Meld it"
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
                    if ($achievement['owner'] == 0) {
                        self::notifyPlayer($player_id, 'log', clienttranslate('All non-purple top cards on ${your} board are value ${age_6} or higher.'), array('your' => 'your', 'age_6' => self::getAgeSquare(6)));
                        self::notifyAllPlayersBut($player_id, 'log', clienttranslate('All non-purple top cards on ${player_name}\'s board are value ${age_6} or higher.'), array('player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id), 'age_6' => self::getAgeSquare(6)));
                        self::transferCardFromTo($achievement, $player_id, 'achievements');  // "Claim the Universe achievement"
                    }
                    else {
                        self::notifyPlayer($player_id, 'log', clienttranslate('All non-purple top cards on ${your} board are value ${age_6} or higher but the Universe achievement has already been claimed.'), array('your' => 'your', 'age_6' => self::getAgeSquare(6)));
                        self::notifyAllPlayersBut($player_id, 'log', clienttranslate('All non-purple top cards on ${player_name}\'s board are value ${age_6} or higher but the Universe achievement has already been claimed.'), array('player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id), 'age_6' => self::getAgeSquare(6)));
                    }
                }
                break;
                
            // id 54, age 5: Societies
            case "54D1":
                if (self::getGameStateValue('game_rules') == 1) { // Last edition
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
                else { // First edition
                   $colors = array(0,1,2,3); // All but purple
                }
                self::setAuxiliaryValueFromArray($colors);
                $step_max = 1; // --> 1 interaction: see B
                break;
            
            // id 55, age 6: Atomic theory
            case "55N1":
                $step_max = 1; // --> 1 interaction: see B
                break;
            
            case "55N2":
                self::executeDraw($player_id, 7, 'board'); // "Draw and meld a 7"
                break;
            
            // id 56, age 6: Encyclopedia
            case "56N1":
                $step_max = 1; // --> 1 interaction: see B
                break;
            
            // id 57, age 6: Industrialisation
            case "57N1":
                if (self::getGameStateValue('game_rules') == 1) { // Last edition
                    $number = 0;
                    for($color=0; $color<5; $color++) {
                        if (self::boardPileHasRessource($player_id, $color, 5 /* factory */)) { // There is at least one visible factory in that color
                            $number++;
                        }
                    }
                    if ($number <= 1) {
                        self::notifyPlayer($player_id, 'log', clienttranslate('${You} have ${n} color with one or more visible ${factories}.'), array('i18n' => array('n'), 'You' => 'You', 'n' => self::getTranslatedNumber($number), 'factories' => $factory));
                        self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} has ${n} color with one or more ${factories}.'), array('i18n' => array('n'), 'player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id), 'n' => self::getTranslatedNumber($number), 'factories' => $factory));
                    }
                    else { // $number > 1
                        self::notifyPlayer($player_id, 'log', clienttranslate('${You} have ${n} colors with one or more visible ${factories}.'), array('i18n' => array('n'), 'You' => 'You', 'n' => self::getTranslatedNumber($number), 'factories' => $factory));
                        self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} has ${n} colors with one or more ${factories}.'), array('i18n' => array('n'), 'player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id), 'n' => self::getTranslatedNumber($number), 'factories' => $factory));
                    }
                    // "For each color of your board that have one factory or more"
                }
                else {
                    $number_of_factories = self::getPlayerSingleRessourceCount($player_id, 5 /* factory */);
                    self::notifyPlayer($player_id, 'log', clienttranslate('${You} have ${n} ${factories}.'), array('You' => 'You', 'n' => $number_of_factories, 'factories' => $factory));
                    self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} has ${n} ${factories}.'), array('player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id), 'n' => $number_of_factories, 'factories' => $factory));
                    $number = self::intDivision($number_of_factories,2); // "For every two factories on your board"
                }
                
                for($i=0; $i<$number; $i++) {
                    self::executeDrawAndTuck($player_id, 6); // "Draw and tuck a 6"
                }
                break;
                
            case "57N2":
                $step_max = 1; // --> 1 interaction: see B
                break;
               
            // id 58, age 6: Machine tools
            case "58N1":
                self::executeDraw($player_id, self::getMaxAgeInScore($player_id), 'score'); // "Draw and score a card of value equal to the highest card in your score pile"
                break;
            
            // id 59, age 6: Classification
            case "59N1":
                $step_max = 1; // --> 1 interaction: see B
                break;
                
            // id 60, age 6: Metric system
            case "60N1":
                if (self::getCurrentSplayDirection($player_id, 2 /* green */) == 2 /* right */) { // "If your green cards are splayed right"
                    $step_max = 1; // --> 1 interaction: see B
                }
                break;
            
            case "60N2":
                $step_max = 1; // --> 1 interaction: see B
                break;
            
            // id 61, age 6: Canning
            case "61N1":
                $step_max = 1; // --> 1 interaction: see B
                break;
            
            case "61N2":
                $step_max = 1; // --> 1 interaction: see B
                break;
            
            // id 62, age 6: Vaccination
            case "62D1":
                if (self::getAuxiliaryValue() == -1) { // If this variable has not been set before
                    self::setAuxiliaryValue(0);
                }
                $step_max = 1; // --> 1 interaction: see B
                break;
            
            case "62N1":
                if (self::getAuxiliaryValue() == 1) { // "If any card was returned as a result of the demand"
                    self::executeDraw($player_id, 7, 'board'); // "Draw and meld a 7"
                }
                break;
            
            // id 63, age 6: Democracy          
            case "63N1":
                if (self::getAuxiliaryValue() == -1) { // If this variable has not been set before
                    self::setAuxiliaryValue(0);
                }
                $step_max = 1; // --> 1 interaction: see B
                break;
            
            // id 64, age 6: Emancipation
            case "64D1":
                $step_max = 1; // --> 1 interaction: see B
                break;
            
            case "64N1":
                $step_max = 1; // --> 1 interaction: see B
                break;
            
            // id 65, age 7: Evolution          
            case "65N1":
                $step_max = 1; // --> 1 interaction: see B
                break;
            
            // id 66, age 7: Publications
            case "66N1":
                $step_max = 1; // --> 1 interaction: see B
                break;
            
            case "66N2":
                $step_max = 1; // --> 1 interaction: see B
                break;

            // id 67, age 7: Combustion
            case "67D1":
                if (self::getGameStateValue('game_rules') == 1) { // Last edition
                    $number_of_crowns = self::getPlayerSingleRessourceCount($launcher_id, 1 /* crown */);
                    self::notifyPlayer($launcher_id, 'log', clienttranslate('${You} have ${n} ${crowns}.'), array('You' => 'You', 'n' => $number_of_crowns, 'crowns' => $crown));
                    self::notifyAllPlayersBut($launcher_id, 'log', clienttranslate('${player_name} has ${n} ${crowns}.'), array('player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id), 'n' => $number_of_crowns, 'crowns' => $crown));
                    $number = self::intDivision($number_of_crowns, 4);
                    if ($number == 0) {
                        self::notifyGeneralInfo(clienttranslate('No card has to be transfered.'));
                        break;
                    }
                }
                else { // First edition
                    $number = 2;
                }
                self::setAuxiliaryValue($number);
                $step_max = 1; // --> 1 interaction: see B
                break;
                
            case "67N1":
                if (self::getGameStateValue('game_rules') == 1) { // Last edition
                    $bottom_red_card = self::getBottomCardOnBoard($player_id, 1 /* red */);
                    if ($bottom_red_card !== null) {
                        self::transferCardFromTo($bottom_red_card, 0, 'deck'); // "Return your bottom red card"
                    }
                }
                break;
            
            // id 68, age 7: Explosives
            case "68D1":
                self::setAuxiliaryValue(0); // Flag to indicate if the player has transfered a card or not
                $step_max = 3; // --> 3 interactions: see B
                break;
            
            // id 69, age 7: Bicycle
            case "69N1":
                $step_max = 1; // --> 1 interaction: see B
                break;
            
            // id 70, age 7: Electricity
            case "70N1":
                $step_max = 1; // --> 1 interaction: see B
                break;
                
            // id 71, age 7: Refrigeration
            case "71D1":
                if (self::countCardsInLocation($player_id, 'hand') > 1) {
                    $step_max = 1; // --> 1 interaction: see B
                }
                break;
                
            case "71N1":
                $step_max = 1; // --> 1 interaction: see B
                break;
                
            // id 72, age 7: Sanitation        
            case "72D1":
                $step_max = 3; // --> 3 interactions: see B
                break;
                
            // id 73, age 7: Lighting        
            case "73N1":
                self::setAuxiliaryValueFromArray(array()); // Flag to indicate what ages have been tucked
                $step_max = 1; // --> 1 interaction: see B
                break;
            
            // id 74, age 7: Railroad        
            case "74N1":
                $step_max = 1; // --> 1 interaction: see B
                break;
            
            case "74N2":
                $step_max = 1; // --> 1 interaction: see B
                break;
                
            // id 75, age 8: Quantum theory        
            case "75N1":
                $step_max = 1; // --> 1 interaction: see B
                break;
                
            // id 76, age 8: Rocketry       
            case "76N1":
                $step_max = 1; // --> 1 interaction: see B
                break;
            
            // id 77, age 8: Flight
            case "77N1":
                if (self::getCurrentSplayDirection($player_id, 1 /* red */) == 3 /* up */) { // "If your red cards are splayed up"
                    $step_max = 1; // --> 1 interaction: see B
                }
                break;
            
            case "77N2":
                $step_max = 1; // --> 1 interaction: see B
                break;
                
            // id 78, age 8: Mobility        
            case "78D1":
                self::setAuxiliaryValueFromArray(array(0,2,3,4)); // Flag to indicate the colors the player can still choose (not red at the start)
                $step_max = 2; // --> 2 interactions: see B
                break;
                
            // id 79, age 8: Corporations        
            case "79D1":
                $step_max = 1; // --> 1 interaction: see B
                break;
                
            case "79N1":
                self::executeDraw($player_id, 8, 'board'); // "Draw and meld an ${age_8}"
                break;
                
            // id 80, age 8: Mass media 
            case "80N1":
                $step_max = 1; // --> 1 interaction: see B
                break;
                
            case "80N2":
                $step_max = 1; // --> 1 interaction: see B
                break;

            // id 81, age 8: Antibiotics
            case "81N1":
                self::setAuxiliaryValueFromArray(array()); // Flag to indicate what ages have been tucked
                $step_max = 1; // --> 1 interaction: see B
                break;

            // id 82, age 8: Skyscrapers
            case "82D1":
                $step_max = 1; // --> 1 interaction: see B
                break;
            
            // id 83, age 8: Empiricism     
            case "83N1":
                $step_max = 1; // --> 1 interaction: see B
                break;
                
            case "83N2":
                if (self::getPlayerSingleRessourceCount($player_id, 3 /* lightbulb */) >= 20) { // "If you have twenty or more lightbulbs on your board"
                    self::notifyPlayer($player_id, 'log', clienttranslate('${You} have at least twenty ${lightbulbs}.'), array('You' => 'You', 'lightbulbs' => $lightbulb));
                    self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} has at least twenty ${lightbulbs}.'), array('player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id), 'lightbulbs' => $lightbulb));
                    self::setGameStateValue('winner_by_dogma', $player_id); // "You win"
                    self::trace('EOG bubbled from self::stPlayerInvolvedTurn Empiricism');
                    throw new EndOfGame();                
                }
                break;
            
            // id 84, age 8: Socialism     
            case "84N1":
                self::setAuxiliaryValue(0); // Flag to indicate if one purple card has been tuckeds or not
                $step_max = 1; // --> 1 interaction: see B
                break;
                
            // id 85, age 9: Computers     
            case "85N1":
                $step_max = 1; // --> 1 interaction: see B
                break;
                
            case "85N2":
                $card = self::executeDraw($player_id, 10, 'board'); // "Draw and meld a 10"
                self::executeNonDemandEffects($card); // "Execute each of its non-demand dogma effects"
                break;
            
            // id 86, age 9: Genetics     
            case "86N1":
                $card = self::executeDraw($player_id, 10, 'board'); // "Draw and meld a 10"
                $board = self::getCardsInLocationKeyedByColor($player_id, 'board');
                $pile = $board[$card['color']];
                for($p=0; $p < count($pile)-1; $p++) { // "For each card beneath it"
                    $card = self::getCardInfo($pile[$p]['id']);
                    self::scoreCard($card, $player_id); // "Score that card"
                }
                break;
            
            // id 87, age 9: Composites     
            case "87D1":
                $step_max = 2; // --> 2 interactions: see B
                if (self::countCardsInLocation($player_id, 'hand') <= 1) {
                    $step = 2; // --> (All but one card when there is 0 or 1 card means that nothing is to be done) Jump directly to step 2
                }
                break;
            
            // id 88, age 9: Fission
            case "88D1":
                $card = self::executeDraw($player_id, 10, 'revealed'); // "Draw a 10"
                if ($card['color'] == 1 /* red */) { // "If it is red"
                    self::notifyGeneralInfo(clienttranslate('This card is ${color}.'), array('i18n' => array('color'), 'color' => self::getColorInClear($card['color'])));
                    self::removeAllHandsBoardsAndScores(); // "Remove all hands, boards and score piles from the game"
                    self::notifyAll('removedHandsBoardsAndScores', clienttranslate('All hands, boards and score piles are removed from the game. Achievements are kept.'), array());
                    
                    // Stats
                    self::setStat(true, 'fission_triggered');
                    
                    // "If this occurs, the dogma action is complete"
                    // (Set the flags as if the launcher had completed the non-demand dogma effect)
                    if (self::getGameStateValue('release_version') >= 1) {
                        self::DbQuery(
                            self::format("
                                UPDATE
                                    nested_card_execution
                                SET
                                    current_player_id = {player_id},
                                    current_effect_type = 1,
                                    current_effect_number = 1
                                WHERE
                                    nesting_index = {nesting_index}",
                                array('player_id' => $launcher_id, 'nesting_index' => self::getGameStateValue('current_nesting_index')))
                        );
                    } else {
                        self::setGameStateValue('current_player_under_dogma_effect', $launcher_id);
                        self::setGameStateValue('current_effect_type', 1);
                        self::setGameStateValue('current_effect_number', 1);
                    }
                } else {
                    self::notifyGeneralInfo(clienttranslate('This card is not red.'));
                    // (Implicit) "Place it into your hand"
                    self::transferCardFromTo($card, $player_id, 'hand');
                }
                break;
                
            case "88N1":
                $step_max = 1; // --> 1 interaction: see B
                break;

            // id 89, age 9: Collaboration
            case "89D1":
                self::executeDraw($player_id, 9, 'revealed'); // "Draw two 9 and reveal them"
                self::executeDraw($player_id, 9, 'revealed'); //
                $step_max = 1; // --> 1 interaction: see B
                break;
                
            case "89N1":
                $number_of_cards_on_board = self::countCardsInLocationKeyedByColor($player_id, 'board');
                $number_of_green_cards = $number_of_cards_on_board[2];
                if ($number_of_green_cards >= 10) { // "If you have ten or more green cards on your board"
                    self::notifyPlayer($player_id, 'log', clienttranslate('${You} have at least ten green cards.'), array('You' => 'You'));
                    self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} has at least ten green cards.'), array('player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id)));
                    self::setGameStateValue('winner_by_dogma', $player_id); // "You win"
                    self::trace('EOG bubbled from self::stPlayerInvolvedTurn Collaboration');
                    throw new EndOfGame();                
                }
                break;
            
            // id 90, age 9: Satellites
            case "90N1":
                $step_max = 1; // --> 1 interaction: see B
                break;

            case "90N2":
                $step_max = 1; // --> 1 interaction: see B
                break;
                
            case "90N3":
                $step_max = 1; // --> 1 interaction: see B
                break;

            // id 91, age 9: Ecology
            case "91N1":
                $step_max = 1; // --> 1 interaction: see B
                break;

            // id 92, age 9: Suburbia
            case "92N1":
                $step_max = 1; // --> 1 interaction: see B
                break;
            
            // id 93, age 9: Services
            case "93D1":
                $ids_of_highest_cards_in_score = self::getIdsOfHighestCardsInLocation($player_id, 'score');
                foreach($ids_of_highest_cards_in_score as $id) {
                    $card = self::getCardInfo($id);
                    self::transferCardFromTo($card, $launcher_id, 'hand'); // "Transfer all the highest cards from your score pile to my hand"
                }
            
                if (count($ids_of_highest_cards_in_score) > 0) { // "If you transferred any cards"
                    $step_max = 1; // --> 1 interaction: see B
                }
                break;
              

            // id 94, age 9: Specialization
            case "94N1":
                $step_max = 1; // --> 1 interaction: see B
                break;
            
            case "94N2":
                $step_max = 1; // --> 1 interaction: see B
                break;
                
            // id 95, age 10: Bioengineering
            case "95N1":
                $step_max = 1; // --> 1 interaction: see B
                break;
            
            case "95N2":
                $player_ids = self::getAllActivePlayerIds();
                $max_number_of_leaves = -1;
                $any_under_three_leaves = false;
                foreach ($player_ids as $player_id) {
                    $number_of_leaves = self::getPlayerSingleRessourceCount($player_id, 2);
                    self::notifyPlayer($player_id, 'log', clienttranslate('${You} have ${n} ${leaves}.'), array('You' => 'You', 'n' => $number_of_leaves, 'leaves' => $leaf));
                    self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} has ${n} ${leaves}.'), array('player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id), 'n' => $number_of_leaves, 'leaves' => $leaf));
                    if (!$any_under_three_leaves && $number_of_leaves < 3) { // Less than three
                        self::notifyGeneralInfo(clienttranslate('That is less than 3.'));
                        $any_under_three_leaves = true;
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
                
                if (!$any_under_three_leaves) {
                    self::notifyGeneralInfo(clienttranslate('Nobody has less than three ${leaves}.'), array('leaves' => $leaf));
                }
                else if ($tie) {
                    self::notifyGeneralInfo(clienttranslate('There is a tie for the most number of ${leaves}. The game continues.'), array('leaves' => $leaf));
                }
                else { // "If any player has less than three leaves, the single player with the most number of leaves"
                    self::notifyPlayer($owner_of_max_number_of_leaves, 'log', clienttranslate('${You} have more ${leaves} than each opponent.'), array('You' => 'You', 'leaves' => $leaf));
                    self::notifyAllPlayersBut($owner_of_max_number_of_leaves, 'log', clienttranslate('${player_name} has more ${leaves} than each opponent.'), array('player_name' => self::getColoredText(self::getPlayerNameFromId($owner_of_max_number_of_leaves), $owner_of_max_number_of_leaves), 'leaves' => $leaf));
                    self::setGameStateValue('winner_by_dogma', $owner_of_max_number_of_leaves); // "Wins"
                    self::trace('EOG bubbled from self::stPlayerInvolvedTurn Bioengineering');
                    throw new EndOfGame();
                }
                
                break;

            // id 96, age 10: Software
            case "96N1":
                self::executeDraw($player_id, 10, 'score'); // "Draw and score a 10"
                break;
                
            case "96N2":
                self::executeDraw($player_id, 10, 'board'); // "Draw and meld two 10"
                $card = self::executeDraw($player_id, 10, 'board'); //
                self::executeNonDemandEffects($card); // "Execute each of the second card's non-demand dogma effects"
                break;
                
            // id 97, age 10: Miniaturization
            case "97N1":
                $step_max = 1; // --> 1 interaction: see B
                break;
            
            // id 98, age 10: Robotics
            case "98N1":
                $top_green_card = self::getTopCardOnBoard($player_id, 2 /* green */);
                if ($top_green_card !== null) {
                    self::scoreCard($top_green_card, $player_id); // "Score your top green card"
                }
                $card = self::executeDraw($player_id, 10, 'board'); // "Draw and meld a 10
                self::executeNonDemandEffects($card); // "Execute each its non-demand dogma effects"
                break;
            
            // id 99, age 10: Databases
            case "99D1":
                if (self::countCardsInLocation($player_id, 'score') > 0) { // (Nothing to do if the player has nothing in his score pile)
                    $step_max = 1; // --> 1 interaction: see B
                }
                break;
            
            // id 100, age 10: Self service
            case "100N1":
                $step_max = 1; // --> 1 interaction: see B
                break;
                
            case "100N2":
                $players = self::loadPlayersBasicInfos();
                $number_of_achievements = self::getPlayerNumberOfAchievements($player_id);
                $most_achievements = true;
                foreach ($players as $other_player_id => $player) {
                    if ($other_player_id == $player_id || $other_player_id == self::getPlayerTeammate($player_id)) {
                        continue; // Skip the player being evaluated and his teammate
                    }
                    
                    if (self::getPlayerNumberOfAchievements($other_player_id) >= $number_of_achievements) {
                        $most_achievements = false;
                    }
                }
                if ($most_achievements) { // "If you have more achievements than each other player"
                    if (self::decodeGameType(self::getGameStateValue('game_type')) == 'individual') {
                        self::notifyAllPlayersBut($player_id, "log", clienttranslate('${player_name} has more achievements than each other player.'), array(
                            'player_name' => self::getPlayerNameFromId($player_id)
                        ));
                        
                        self::notifyPlayer($player_id, "log", clienttranslate('${You} have more achievements than each other player.'), array(
                            'You' => 'You'
                        ));
                    }
                    else { // self::getGameStateValue('game_type')) == 'team'
                        $teammate_id = self::getPlayerTeammate($player_id);
                        $winning_team = array($player_id, $teammate_id);
                        self::notifyAllPlayersBut($winning_team, "log", clienttranslate('The other team has more achievements than yours.'), array());
                        
                        self::notifyPlayer($player_id, "log", clienttranslate('Your team has more achievements than the other.'), array());
                        
                        self::notifyPlayer($teammate_id, "log", clienttranslate('Your team has more achievements than the other.'), array());
                    }
                    self::setGameStateValue('winner_by_dogma', $player_id); // "You win"
                    self::trace('EOG bubbled from self::stPlayerInvolvedTurn Self service');
                    throw new EndOfGame();
                }
                break;
                
            // id 101, age 10: Globalization
            case "101D1":
                $step_max = 1; // --> 1 interaction: see B
                break;

            case "101N1":
                self::executeDraw($player_id, 6, 'score'); // "Draw and score a 6"
                
                $player_ids = self::getAllActivePlayerIds();
                $nobody_more_leaves_than_factories = true;
                foreach ($player_ids as $player_id) {
                    $number_of_leaves = self::getPlayerSingleRessourceCount($player_id, 2);
                    $number_of_factories = self::getPlayerSingleRessourceCount($player_id, 5);
                    
                    self::notifyPlayer($player_id, 'log', clienttranslate('${You} have ${m} ${leaves} and ${n} ${factories}.'), array('You' => 'You', 'm' => $number_of_leaves, 'leaves' => $leaf, 'n' => $number_of_factories, 'factories' => $factory));
                    self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} has ${m} ${leaves} and ${n} ${factories}.'), array('player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id), 'm' => $number_of_leaves, 'leaves' => $leaf, 'n' => $number_of_factories, 'factories' => $factory));
                    
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
                        if (self::decodeGameType(self::getGameStateValue('game_type')) == 'individual') {
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
                        else { // self::getGameStateValue('game_type') == 'team'
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
                        if (self::decodeGameType(self::getGameStateValue('game_type')) == 'individual') {
                            $player_id = $winning_team[0];
                            self::notifyAllPlayersBut($player_id, "log", clienttranslate('${player_name} has a greater score than each other player.'), array(
                                'player_name' => self::getPlayerNameFromId($player_id)
                            ));
                            
                            self::notifyPlayer($player_id, "log", clienttranslate('${You} have a greater score than each other player.'), array(
                                'You' => 'You'
                            ));
                        }
                        else { // self::getGameStateValue('game_type')) == 'team'
                            $player_id = $winning_team[0];
                            $teammate_id = $winning_team[1];
                            self::notifyAllPlayersBut($winning_team, "log", clienttranslate('The other team has a greater score than yours.'), array());
                            
                            self::notifyPlayer($player_id, "log", clienttranslate('Your team has a greater score than the other one.'), array());
                            
                            self::notifyPlayer($teammate_id, "log", clienttranslate('Your team has a greater score than the other one.'), array());
                        }
                        self::setGameStateValue('winner_by_dogma', $player_id); // "The single player with the most points wins" (or combined scores for team)
                        self::trace('EOG bubbled from self::stPlayerInvolvedTurn Globalization');
                        throw new EndOfGame();
                    }
                }
                break;
                
            // id 102, age 10: Stem cells
            case "102N1":
                if (self::countCardsInLocation($player_id, 'hand') == 0) {
                    self::notifyPlayer($player_id, 'log', clienttranslate('${You} have no cards in your hand to score.'), array('You' => 'You'));
                    self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} has no cards in their hand to score.'), array('player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id)));
                } else {
                    $step_max = 1; // --> 1 interaction: see B
                }
                break;
            
        
            // id 103, age 10: A. I.
            case "103N1":
                self::executeDraw($player_id, 10, 'score'); // "Draw and score a 10"
                break;

            case "103N2":
                $players = self::loadPlayersBasicInfos();
                $software_found = false;
                foreach ($players as $any_player_id => $player) {
                    $top_blue_card = self::getTopCardOnBoard($any_player_id, 0 /* blue: color of Software*/);
                    if ($top_blue_card !== null && $top_blue_card['id'] == 96 /* Software */) {
                        $software_found = true;
                        break;
                    }
                }
                
                $robotics_found = false;
                foreach ($players as $any_player_id => $player) {
                    $top_red_card = self::getTopCardOnBoard($any_player_id, 1 /* red: color of Robotics*/);
                    if ($top_red_card !==null && $top_red_card['id'] == 98 /* Robotics */) {
                        $robotics_found = true;
                        break;
                    }
                }
                
                if ($software_found && $robotics_found) { // "If Robotics and Software are top cards on any board"
                    self::notifyGeneralInfo(clienttranslate('Robotics and Software are both visible as top cards.'));
                    
                    $min_score = 9999;
                    foreach($players as $any_player_id => $player) {
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
                        self::setGameStateValue('winner_by_dogma', $player_with_min_score); // "The single player with the most points wins" (scores are not combined for teams)
                        self::trace('EOG bubbled from self::stPlayerInvolvedTurn A. I.');
                        throw new EndOfGame();
                    }
                }
                break;

            // id 104, age 10: The internet.
            case "104N1":
                $step_max = 1; // --> 1 interaction: see B
                break;

            case "104N2":
                self::executeDraw($player_id, 10, 'score'); // "Draw and score a 10"
                break;

            case "104N3":
                $number_of_clocks = self::getPlayerSingleRessourceCount($player_id, 6 /* clock */);
                self::notifyPlayer($player_id, 'log', clienttranslate('${You} have ${n} ${clocks}.'), array('You' => 'You', 'n' => $number_of_clocks, 'clocks' => $clock));
                self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} has ${n} ${clocks}.'), array('player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id), 'n' => $number_of_clocks, 'clocks' => $clock));
                for($i=0; $i<self::intDivision($number_of_clocks,2); $i++) { // "For every two clocks on your board"
                    self::executeDraw($player_id, 10, 'board'); // "Draw and meld a 10"
                }
                break;

            // id 110, Artifacts age 1: Treaty of Kadesh
            case "110C1":
                $step_max = 1; // --> 1 interaction
                break;

            case "110N1":
                $step_max = 1; // --> 1 interaction
                break;

            // id 111, Artifacts age 1: Sibidu Needle
            case "111N1":
                while (true) {
                    $card = self::executeDraw($player_id, 1, 'revealed'); // "Draw and reveal a 1"
                    $top_card = self::getTopCardOnBoard($player_id, $card['color']);
                    if ($top_card !== null && $card['faceup_age'] == $top_card['faceup_age']) { // "If you have a top card of matching color and value"
                        self::scoreCard($card, $player_id); // "Score the drawn card"
                        continue; // "Repeat this effect"
                    }
                    self::notifyGeneralInfo(clienttranslate('There was not a top card of matching color and value.'));
                    break;        
                }
                self::transferCardFromTo($card, $player_id, 'hand'); // Keep it
                break;
            
            // id 112, Artifacts age 1: Basur Hoyuk Tokens
            case "112N1":
                $card = self::executeDraw($player_id, 4, 'revealed'); // "Draw and reveal a 4"
                $top_card = self::getTopCardOnBoard($player_id, $card['color']);
                if ($top_card === null) {
                    self::transferCardFromTo($card, $player_id, 'hand'); // Keep it
                } else if ($top_card !== null && self::comesAlphabeticallyBefore($top_card, $card)) { // "If you have a top card of the drawn card's color that comes before it in the alphabet"
                    self::notifyGeneralInfo(clienttranslate('In English alphabetical order, ${english_name_1} comes before ${english_name_2}.'), array(
                        'english_name_1' => self::getCardName($top_card['id']),
                        'english_name_2' => self::getCardName($card['id']),
                    ));
                    $step_max = 1;
                } else {
                    self::notifyGeneralInfo(clienttranslate('In English alphabetical order, ${english_name_1} does not come before ${english_name_2}.'), array(
                        'english_name_1' => self::getCardName($top_card['id']),
                        'english_name_2' => self::getCardName($card['id']),
                    ));
                    self::transferCardFromTo($card, $player_id, 'hand'); // Keep it
                }
                break;
            
            // id 113, Artifacts age 1: Holmegaard Bows
            case "113C1":
                $step_max = 1;
                break;
            
            case "113N1":
                // "Draw a 2"
                self::executeDraw($player_id, 2);
                break;

            // id 114, Artifacts age 1: Papyrus of Ani
            case "114N1":
                $step_max = 1;
                break;

            // id 115, Artifacts age 1: Pavlovian Tusk
            case "115N1":
                // "Draw three cards of value equal to your top green card"
                $top_green_card = self::getTopCardOnBoard($player_id, 2 /* green */);
                $top_green_card_age = 0;
                if ($top_green_card !== null) {
                    $top_green_card_age = $top_green_card["age"];
                }
                self::setGameStateValue('card_id_1', self::executeDraw($player_id, $top_green_card_age)['id']);
                self::setGameStateValue('card_id_2', self::executeDraw($player_id, $top_green_card_age)['id']);
                self::setGameStateValue('card_id_3', self::executeDraw($player_id, $top_green_card_age)['id']);
                $step_max = 2;
                break;
            
            // id 116, Artifacts age 1: Priest-King
            case "116N1":
                $step_max = 1;
                break;
            
            case "116N2":
                $step_max = 1;
                break;
            
            // id 117, Artifacts age 1: Electrum Stater of Efesos
            case "117N1":
                while (true) {
                    $card = self::executeDraw($player_id, 3, 'revealed'); // "Draw and reveal a 3"
                    $top_card = self::getTopCardOnBoard($player_id, $card['color']);
                    if ($top_card == null) { // "If you do not have a top card of the drawn card's color"
                        self::transferCardFromTo($card, $player_id, 'board'); // "Meld it"
                        continue; // "Repeat this effect"
                    }
                    break;
                }
                self::transferCardFromTo($card, $player_id, 'hand'); // Keep it
                break;
                
            // id 118, Artifacts age 1: Jiskairumoko Necklace
            case "118C1":
                $step_max = 1;
                break;
            
             // id 119, Artifacts age 1: Dancing Girl
             case "119C1":
                $card = self::getCardInfo(119); // Dancing Girl
                self::transferCardFromTo($card, $player_id, 'board');
                self::setAuxiliaryValue(self::getAuxiliaryValue() + 1); // Keep track of Dancing Girl's movements
                break;
            
            // id 119, Artifacts age 1: Dancing Girl
            case "119N1":
                $num_movements = self::getAuxiliaryValue() + 1; // + 1 since the variable is initialized to -1, not 0
                $initial_location = self::getCurrentNestedCardState()['card_location'];
                if ($player_id == $launcher_id) {
                    if ($num_movements == self::countNonEliminatedPlayers() - 1 && $initial_location == 'board') {
                        self::notifyPlayer($player_id, 'log', clienttranslate('Dancing Girl has been on every board during this action, and it started on your board, so you win.'), array());
                        self::notifyAllPlayersBut($player_id, 'log', clienttranslate('Dancing Girl has been on every board during this action, and it started on ${player_name}\'s board, so they win.'), array('player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id)));
                        self::setGameStateValue('winner_by_dogma', $player_id); // "You win"
                        self::trace('EOG bubbled from self::stPlayerInvolvedTurn Dancing Girl');
                        throw new EndOfGame();
                    } else {
                        self::notifyAll('log', clienttranslate('Dancing Girl has not been on every board during this action.'), array());
                    }
                }
                break;
            
            // id 120, Artifacts age 1: Lurgan Canoe
            case "120N1":
                $step_max = 1;
                break;
            
            // id 121, Artifacts age 1: Xianrendong Shards
            case "121N1":
                self::setGameStateValue('card_id_1', -1);
                self::setGameStateValue('card_id_2', -1);
                self::setGameStateValue('card_id_3', -1);
                $step_max = 2;
                break;

            // id 122, Artifacts age 1: Mask of Warka
            case "122N1":
                self::setAuxiliaryValue2FromArray(array());
                $step_max = 1;
                break;

            // id 123, Artifacts age 1: Ark of the Covenant
            case "123N1":
                $step_max = 1;
                break;
                
            // id 124, Artifacts age 1: Tale of the Shipwrecked Sailor
            case "124N1":
                $step_max = 2;
                break;
            
             // id 125, Artifacts age 2: Seikilos Epitaph
             case "125N1":
                // "Draw and meld a 3"
                $melded_card = self::executeDraw($player_id, 3, 'board');
               
                // "Meld your bottom card of the drawn card's color"
                $number_of_cards = self::countCardsInLocationKeyedByColor($player_id, 'board')[$melded_card['color']];
                if ($number_of_cards > 1) {
                    $bottom_card = self::getBottomCardOnBoard($player_id, $melded_card['color']);
                    $melded_card = self::transferCardFromTo($bottom_card, $player_id, 'board');
                }

                // "Execute its non-demand dogma effects. Do not share them."
                self::executeNonDemandEffects($melded_card);
                break;
            
            // id 126, Artifacts age 2: Rosetta Stone
            case "126N1":
                $step_max = 3;
                break;

            // id 127, Artifacts age 2: Chronicle of Zuo
            case "127N1":
                $min_towers = self::getUniqueValueFromDB(self::format("SELECT MIN(player_icon_count_4) FROM player WHERE player_id != {player_id} AND player_eliminated = 0", array('player_id' => $player_id)));
                $min_crowns = self::getUniqueValueFromDB(self::format("SELECT MIN(player_icon_count_1) FROM player WHERE player_id != {player_id} AND player_eliminated = 0", array('player_id' => $player_id)));
                $min_bulbs = self::getUniqueValueFromDB(self::format("SELECT MIN(player_icon_count_3) FROM player WHERE player_id != {player_id} AND player_eliminated = 0",  array('player_id' => $player_id)));
                
                $this_player_icon_counts = self::getPlayerResourceCounts($player_id);
                
                if ($this_player_icon_counts[4] <= $min_towers) {
                    $card = self::executeDraw($player_id, 2); // "If you have the least towers, draw a 2"
                }
                if ($this_player_icon_counts[1] <= $min_crowns) {
                    $card = self::executeDraw($player_id, 3); // "If you have the least crowns, draw a 3"
                }
                if ($this_player_icon_counts[3] <= $min_bulbs) {
                    $card = self::executeDraw($player_id, 4); // "If you have the least bulbs, draw a 4"
                }
                break;
                
            // id 128, Artifacts age 2: Babylonian Chronicles
            case "128C1":
                $step_max = 1;
                break;
            
            case "128N1":
                // "Draw and score a 3"
                self::executeDraw($player_id, 3, 'score');
                break;

            // id 129, Artifacts age 2: Holy Lance
            case "129C1":
                $step_max = 1;
                break;

            case "129N1":
                // "If Holy Grail is a top card on your board, you win"
                $top_card = self::getTopCardOnBoard($player_id, 3); // top yellow card
                if ($top_card !== null && $top_card['id'] == 131) {
                    self::notifyPlayer($player_id, 'log', clienttranslate('${You} have Holy Grail as a top card.'), array('You' => 'You'));
                    self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} has Holy Grail as a top card.'), array('player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id)));
                    self::setGameStateValue('winner_by_dogma', $player_id);
                    self::trace('EOG bubbled from self::stPlayerInvolvedTurn HolyLance');
                    throw new EndOfGame();
                }
                break;
                
            // id 130, Artifacts age 1: Baghdad Battery
            case "130N1":
                self::setAuxiliaryValue(-1);
                $step_max = 1;
                break;
            
            // id 131, Artifacts age 2: Holy Grail
            case "131N1":
                $step_max = 1;
                break;

            // id 132, Artifacts age 2: Terracotta Army
            case "132C1":
                $step_max = 1;
                break;
            
            case "132N1":
                $step_max = 1;
                break;

            // id 133, Artifacts age 2: Dead Sea Scrolls
            case "133N1":
                // "Draw an Artifact of value equal to the value of your highest top card"
                self::executeDraw($player_id, self::getMaxAgeOnBoardTopCards($player_id), 'hand', /*bottom_to=*/ false, /*type=*/ 1);
                break;

            // id 134, Artifacts age 2: Cyrus Cylinder
            case "134N1":
                $step_max = 1;
                break;

            case "134N1+":
                $step_max = 1;
                break;

            // id 135, Artifacts age 3: Dunhuang Star Chart
            case "135N1":
                $step_max = 1;
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
                    $card = self::getCardInfo($card['id']);
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
                self::notifyGeneralInfo(clienttranslate('This card is ${color}.'), array('i18n' => array('color'), 'color' => self::getColorInClear($card['color'])));
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
                self::notifyPlayer($player_id, 'log', clienttranslate('${You} have ${n} top card(s) with a ${tower} on your board.'), array('i18n' => array('n'), 'You' => 'You', 'n' => self::getTranslatedNumber($num_cards), 'tower' => $tower));
                self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} has ${n} top card(s) with a ${tower} on his board.'), array('i18n' => array('n'), 'player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id), 'n' => self::getTranslatedNumber($num_cards), 'tower' => $tower));
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
                        self::transferCardFromTo($card, $launcher_id, 'score', /*bottom_to=*/ false, /*score_keyword=*/ false);
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
                    self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} has no top cards with a ${crown} on his board.'), array('player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id), 'crown' => $crown));
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
                    self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} has exactly five cards and five colors in his hand.'), array('player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id)));
                    self::setGameStateValue('winner_by_dogma', $player_id);
                    self::trace('EOG bubbled from self::stInterInteractionStep CrossOfCoronado');
                    throw new EndOfGame();
                } else {
                    self::notifyPlayer($player_id, 'log', clienttranslate('${You} do not have exactly five cards and five colors in your hand.'), array('You' => 'You'));
                    self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} does not have exactly five cards and five colors in his hand.'), array('player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id)));
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
                self::setGameStateValueFromArray('color_array', $colors_with_more_visible_cards);
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
                    $card = self::executeDraw($player_id, 5, 'board'); // "Draw and meld a 5"
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
                self::notifyGeneralInfo(clienttranslate('This card is ${color}.'), array('i18n' => array('color'), 'color' => self::getColorInClear($card['color'])));
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
                    $card = self::executeDraw($player_id, 6, 'board');
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
                $step_max = 1;
                break;

            // id 175, Artifacts age 7: Periodic Table
            case "175N1":
                // Determine if there are any top cards which have the same value as another top card on their board
                $colors = self::getColorsOfRepeatedValueOfTopCardsOnBoard($player_id);
                if (count($colors) >= 1) {
                    self::setAuxiliaryValueFromArray($colors);
                    $step_max = 2;
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
                    'number' => $visible_card_count,
                    'color' => self::getColorInClear($card['color']),
                    'your' => 'your')
                );
                self::notifyAllPlayersBut($player_id, 'log', clienttranslate('There are ${number} ${color} card(s) visible on ${player_name}\'s board.'), array(
                    'number' => $visible_card_count,
                    'color' => self::getColorInClear($card['color']),
                    'player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id))
                );
                self::executeDraw($player_id, $visible_card_count, 'score');
                break;

            // id 177, Artifacts age 7: Submarine H. L. Hunley
            case "177C1":
                // "I compel you to draw and meld a 7" 
                $card = self::executeDraw($player_id, 7, 'board');

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
                $card = self::executeDraw($player_id, 8, 'board');
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
                
                $step_max = 2;
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
                $new_card = self::executeDraw($player_id, 7);
                
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
                self::setGameStateValueFromArray('player_array', self::getAllActivePlayers());
                $step_max = 2;
                break;

            // id 185, Artifacts age 8: Parnell Pitch Drop
            case "185N1":
                // "Draw and meld a card of value one higher than the highest top card on your board"
                $card = self::executeDraw($player_id, self::getMaxAgeOnBoardTopCards($player_id) + 1, 'board');
                if (self::countIconsOnCard($card, 6) == 3) {
                    // "If the melded card has three clocks, you win"
                    self::notifyPlayer($player_id, 'log', clienttranslate('${You} melded a card with 3 ${clocks}.'), array('You' => 'You', 'clocks' => $clock));
                    self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} melded a card with 3 ${clocks}.'), array('player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id), 'clocks' => $clock));
                    self::setGameStateValue('winner_by_dogma', $player_id);
                    self::trace('EOG bubbled from self::stPlayerInvolvedTurn Parnell Pitch Drop');
                    throw new EndOfGame();
                }
                break;

            // id 186, Artifacts age 8: Earhart's Lockheed Electra 10E
            case "186N1":
                self::setAuxiliaryValue(0);
                self::setGameStateValue('age_last_selected', 9);
                $step_max = 1;
                break;

            // id 187, Artifacts age 8: Battleship Bismarck
            case "187C1":
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
                            'You' => 'You',
                            'color' => self::getColorInClear($color)
                        )); 
                        self::notifyAllPlayersBut($single_player_id, 'log', clienttranslate('${player_name} has the highest top ${color} card.'), array(
                            'player_name' => self::getColoredText(self::getPlayerNameFromId($single_player_id), $single_player_id),
                            'color' => self::getColorInClear($color)
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
                $card = self::executeDraw($player_id, 9, 'board');

                // Store information about whether the card has a clock or not
                if (self::hasRessource($card, 6)) {
                    self::setAuxiliaryValue(1);
                } else {
                    self::setAuxiliaryValue(0);
                }

                // "Execute the effects of the melded card as if they were on this card, without sharing"
                self::executeAllEffects($card);
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
                $splayable_colors = self::getSplayableColorsOnBoard($player_id, /*splay_direction=*/ 3);
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
                $card_1 = self::executeDraw($player_id, 10, 'board');
                $card_2 = self::executeDraw($player_id, 10, 'board');
                
                // "If they are the same color, you win"
                if ($card_1['color'] == $card_2['color']) {
                    self::notifyPlayer($player_id, 'log', clienttranslate('${You} melded two cards of the same color'), array('You' => 'You'));
                    self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} melded two cards of the same color'), array('player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id)));
                    self::setGameStateValue('winner_by_dogma', $player_id);
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
                    self::executeNonDemandEffects($top_blue_card);
                }
                break;

            // id 204, Artifacts age 9: Marilyn Diptych
            case "204N1":
                $step_max = 2;
                break;

            // id 205, Artifacts age 10: Rover Curiosity
            case "205N1":
                // "Draw and meld an Artifact 10"
                $card = self::executeDraw($player_id, 10, 'board', /*bottom_to=*/ false, /*type=*/ 1);
                // "Execute the effects of the melded card as if they were on this card. Do not share them"
                self::executeAllEffects($card);
                break;
            
            // id 206, Artifacts age 10: Higgs Boson
            case "206N1":
                $step_max = 1;
                break;
                
           // id 207, Artifacts age 10: Exxon Valdez
            case "207C1":
                // "I compel you to remove all cards from your hand, score pile, board, and achievements from the game"
                self::removeAllCardsFromPlayer($player_id);
                
                // "You lose! If there is only one player remaining in the game, that player wins"
                if (self::decodeGameType(self::getGameStateValue('game_type')) == 'individual') {
                    self::notifyPlayer($player_id, 'log', clienttranslate('${You} lose.'),  array('You' => 'You'));
                    self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} loses.'), array(
                        'player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id)
                    ));
                    self::eliminatePlayer($player_id);
                    if (count(self::getAllActivePlayers()) == 1) {
                        self::setGameStateValue('winner_by_dogma', $launcher_id);
                        self::trace('EOG bubbled from self::stInterInteractionStep Exxon Valdez');
                        throw new EndOfGame();
                    }
                } else { // Team play
                    // Entire team loses if one player loses 
                    $teammate_id = self::getPlayerTeammate($player_id);
                    $winning_team = array($player_id, $teammate_id);
                    self::notifyPlayer($player_id, 'log', clienttranslate('${Your} team loses.'), array('Your' => 'Your'));
                    self::notifyPlayer($teammate_id, 'log', clienttranslate('${Your} team loses.'), array('Your' => 'Your'));
                    self::notifyAllPlayersBut($winning_team, 'log', clienttranslate('The other team loses.'), array());
                    self::eliminatePlayer($player_id);
                    self::setGameStateValue('winner_by_dogma', $launcher_id);
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
                $players = self::loadPlayersBasicInfos();
                foreach ($players as $id => $player) {
                    if ($player_id != $id && self::countCardsInLocation($id, 'score') >= $cards_in_my_score_pile) {
                        $win_condition_met = false;
                    }
                }
                if ($win_condition_met) {
                    self::notifyPlayer($player_id, 'log', clienttranslate('${You} have the most cards in your score pile.'), array('You' => 'You'));
                    self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} has the most cards in their score pile.'), array('player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id)));
                    self::setGameStateValue('winner_by_dogma', $player_id);
                    self::trace('EOG bubbled from self::stPlayerInvolvedTurn Maastricht Treaty');
                    throw new EndOfGame();
                } else {
                    self::notifyPlayer($player_id, 'log', clienttranslate('${You} do not have the most cards in your score pile.'), array('You' => 'You'));
                    self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} does not have the most cards in their score pile.'), array('player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id)));
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
                    self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} has the most cards of a color showing on his board out of all colors on all boards.'), array('player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id)));
                    self::setGameStateValue('winner_by_dogma', $player_id);
                    self::trace('EOG bubbled from self::stPlayerInvolvedTurn Seikan Tunnel');
                    throw new EndOfGame();
                } else {
                    self::notifyPlayer($player_id, 'log', clienttranslate('${You} do not have the most cards of a color showing on your board out of all colors on all boards.'), array('You' => 'You'));
                    self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} does not have the most cards of a color showing on his board out of all colors on all boards.'), array('player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id)));
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
                self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} found Waldo!'), array('player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id)));
                self::setGameStateValue('winner_by_dogma', $player_id);
                self::trace('EOG bubbled from self::stPlayerInvolvedTurn Wheres Waldo');
                throw new EndOfGame();
                break;

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
                $step_max = 1;
                break;

            // id 217, Relic age 5: Newton-Wickins Telescope
            case "217N1":
                $step_max = 1;
                break;
                
            default:
                // Do not throw an exception so that we are able to stop executing a card after it's popped from
                // the stack and there's nothing left to do.
                break;
            }
            //[AA]||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||
        }
        catch (EndOfGame $e) {
            // End of the game: the exception has reached the highest level of code
            self::trace('EOG bubbled from self::stPlayerInvolvedTurn');
            self::trace('playerInvolvedTurn->justBeforeGameEnd');
            $this->gamestate->nextState('justBeforeGameEnd');
            return;
        }

        if ($step_max === null) {
            // End of the effect for this player
            self::trace('playerInvolvedTurn->interPlayerInvolvedTurn');
            $this->gamestate->nextState('interPlayerInvolvedTurn');
            return;
        }
        // There is an interaction needed
        self::setStepMax($step_max);

        // Prepare the first step
        self::setStep($step === null ? 1 : $step);
        self::trace('playerInvolvedTurn->interactionStep');
        $this->gamestate->nextState('interactionStep');
    }
    
    function stInterPlayerInvolvedTurn() {
        // Code for handling "execute each of the non demand effects of card X"
        while (true) {

            if (self::getGameStateValue('release_version') >= 1) {
                break;
            } else {
                $nested_id_1 = self::getGameStateValue('nested_id_1');
                if ($nested_id_1 == -1) { // No or no more card in the execution stack
                    // Resume normal situation
                    self::trace('Out of nesting');
                    break;
                }
                
                $card = self::getCardInfo($nested_id_1);
                $current_effect_number = self::getGameStateValue('nested_current_effect_number_1') + 1; // Next effect
                
                if ($current_effect_number > 3 || self::getNonDemandEffect($card['id'], $current_effect_number) === null) {
                    // No card has more than 3 non-demand dogma => there is no more effect
                    // or the next non-demand-dogma effect is not defined
                    self::notifyGeneralInfo(clienttranslate("Card execution within dogma completed."));
                    self::popCardFromNestedDogmaStack();
                } else { // There is at least one effect the player can perform
                    self::setGameStateValue('nested_current_effect_number_1', $current_effect_number);
                    // Continuation of exclusive execution
                    self::trace('interPlayerInvolvedTurn->playerInvolvedTurn');
                    $this->gamestate->nextState('playerInvolvedTurn');
                    return;
                }
            }
        }
        
        // Code executed when there is no exclusive execution to handle, or when it's over

        try {
            self::checkForSpecialAchievements();
        } catch (EndOfGame $e) {
            // End of the game: the exception has reached the highest level of code
            self::trace('EOG bubbled from self::stInterPlayerInvolvedTurn');
            self::trace('interPlayerInvolvedTurn->justBeforeGameEnd');
            $this->gamestate->nextState('justBeforeGameEnd');
            return;
        }

        // Switch to new card that was pushed onto the stack
        if (self::getGameStateValue('release_version') >= 1 && self::getNestedCardState(self::getGameStateValue('current_nesting_index') + 1) != null) {
            self::incGameStateValue('current_nesting_index', 1);
            self::trace('interPlayerInvolvedTurn->dogmaEffect');
            $this->gamestate->nextState('dogmaEffect');
            return;
        }

        // A player has executed an effect of a dogma card (or passed). Is there another player on which the effect can apply?
        $player_id = self::getCurrentPlayerUnderDogmaEffect();
        $launcher_id = self::getLauncherId();

        if (self::getGameStateValue('release_version') >= 1) {
            $nesting_index = self::getGameStateValue('current_nesting_index');
            $current_effect_type = self::getNestedCardState($nesting_index)['current_effect_type'];

            // If this is a nested card, don't allow other players to share the non-demand effect
            $nesting_index = self::getGameStateValue('current_nesting_index');
            self::updateCurrentNestedCardState('post_execution_index', 0);
            $nested_card_state = self::getNestedCardState($nesting_index);
            $next_player = $nesting_index >= 1 && $nested_card_state['current_effect_type'] == 1 ? null : self::getNextPlayerUnderEffect($current_effect_type, $player_id, $launcher_id);

            // There are no more players which are eligible to share this effect
            if ($next_player === null) {
                self::trace('interPlayerInvolvedTurn->interDogmaEffect');
                $this->gamestate->nextState('interDogmaEffect');
                return;
            }
            self::updateCurrentNestedCardState('current_player_id', $next_player);
        } else {
            $current_effect_type = self::getGameStateValue('current_effect_type');
            $next_player = self::getNextPlayerUnderEffect($current_effect_type, $player_id, $launcher_id);
            if ($next_player === null) {
                // There is no more player eligible for this effect
                // End of the dogma effect
                self::trace('interPlayerInvolvedTurn->interDogmaEffect');
                $this->gamestate->nextState('interDogmaEffect');
                return;
            }
            self::setGameStateValue('current_player_under_dogma_effect', $next_player);
        }
        $this->gamestate->changeActivePlayer($next_player);
        
        // Jump to this player
        self::trace('interPlayerInvolvedTurn->playerInvolvedTurn');
        $this->gamestate->nextState('playerInvolvedTurn');
    }
    
    function stInteractionStep() {
        $player_id = self::getCurrentPlayerUnderDogmaEffect();
        $launcher_id = self::getLauncherId();

        if (self::getGameStateValue('release_version') >= 1) {
            $nested_card_state = self::getCurrentNestedCardState();
            $card_id = $nested_card_state['card_id'];
            $current_effect_type = $nested_card_state['current_effect_type'];
            $current_effect_number = $nested_card_state['current_effect_number'];
        } else {
            $nested_id_1 = self::getGameStateValue('nested_id_1');
            $card_id = $nested_id_1 == -1 ? self::getGameStateValue('dogma_card_id') : $nested_id_1 ;
            $current_effect_type = $nested_id_1 == -1 ? self::getGameStateValue('current_effect_type') : 1 /* Non-demand effects only*/;
            $current_effect_number = $nested_id_1 == -1 ?  self::getGameStateValue('current_effect_number') : self::getGameStateValue('nested_current_effect_number_1');
        }

        $step = self::getStep();
        $card_id_1 = self::getGameStateValue('card_id_1');
        $card_id_2 = self::getGameStateValue('card_id_2');
        $card_id_3 = self::getGameStateValue('card_id_3');
        
        $crown = self::getIconSquare(1);
        $leaf = self::getIconSquare(2);
        $lightbulb = self::getIconSquare(3);
        $tower = self::getIconSquare(4);
        $factory = self::getIconSquare(5);
        $clock = self::getIconSquare(6);
        
        //||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||
        // [B] SPECIFIC CODE: for effects where interaction is needed, what is the range of cards/colors/values among which the player has to make a choice? and what is to be done with that card?
        $letters = array(1 => 'A', 2 => 'B', 3 => 'C', 4 => 'D');
        $code = self::getCardExecutionCodeWithLetter($card_id, $current_effect_type, $current_effect_number, $step);
        self::trace('[B]'.$code.' '.self::getPlayerNameFromId($player_id).'('.$player_id.')'.' | '.self::getPlayerNameFromId($launcher_id).'('.$launcher_id.')');

        $options = null;
        switch($code) {
        // The first number is the id of the card
        // D1 means the first (and single) I demand effect
        // N1 means the first non-demand effect
        // N2 means the second non-demand effect
        // N3 means the third non-demand effect
        
        // The letter indicates the step : A for the first one, B for the second
        
        // Setting the $step_max variable means there is interaction needed with the player
        
        // id 0, age 1: Pottery
        case "0N1A":
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
            
        // id 1, age 1: Tools
        case "1N1A":
            // "You may return three cards from your hand"
            $options = array(
                'player_id' => $player_id,
                'n' => 3,
                'solid_constraint' => true, // The player MUST have three cards in hand to trigger the effect
                'can_pass' => true,
                
                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => 0,
                'location_to' => 'deck'
            );
            break;
        case "1N2A":
            // "You may return a 3 from your hand"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => true,
                
                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => 0,
                'location_to' => 'deck',
                
                'age' => 3
            );
            break;
        
        // id 3, age 1: Archery
        case "3D1A":
            // "Transfer the highest card in your hand to my hand"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => false,
                
                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => $launcher_id,
                'location_to' => 'hand',
                
                'age' => self::getMaxAgeInHand($player_id)
            );
            break;
            
        // id 5, age 1: Oars
        case "5D1A":
            // "Transfer a card with a crown from your hand to my score pile"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => false,
                
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
                'can_pass' => false,
                
                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => $player_id,
                'location_to' => 'board',
                
                'color' => $selectable_colors
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
                'can_pass' => false,
                
                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => $player_id,
                'location_to' => 'board',
                
                'age' => $age
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
                
                'with_icon' => 4 
            );
            break;
        
        // id 12, age 1: City states
        case "12D1A":
            // "Transfer a top card with a tower from your board to my board"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => false,
                
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
                
                'splay_direction' => 1 /* left */,
                'color' => array(self::getGameStateValue('color_last_selected'))
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
                'can_pass' => false,
                
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
                'can_pass' => false,
                
                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => $player_id,
                'location_to' => 'board'
            );
            break;
            
        case "18N1B":
            // "You may transfer your top red card to another player's board. If you do, transfer that player's top green card to your board.
            if (self::getGameStateValue('release_version') >= 1) {
                $options = array(
                    'player_id' => $player_id,
                    'can_pass' => true,
                    
                    'choose_player' => true,
                    'players' => self::getOtherActivePlayers($player_id)
                );
                
            } else {
                $options = array(
                    'player_id' => $player_id,
                    'can_pass' => true,
                    
                    'choose_opponent' => true
                );
            }
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
                'can_pass' => false,
                
                'owner_from' => $player_id,
                'location_from' => 'score',
                'owner_to' => $launcher_id,
                'location_to' => 'score',
                
                'age' => 1
            );
            break;
            
        // id 21, age 2: Canal building         
        case "21N1A":
            // "You may exchange all the highest cards in your hand with all the highest cards in your score pile"
            $options = array(
                'player_id' => $player_id,
                'can_pass' => false,
                
                'choose_yes_or_no' => true
            );
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
                'can_pass' => false,
                
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
                
                'splay_direction' => 1 /* left */
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
                'can_pass' => false,
                
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
                'can_pass' => false,
                
                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => $player_id,
                'location_to' => 'board',
            );
            break;
            
        case "25N2B":
            // "Score a card from your hand"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => false,
                
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
                'location_to' => 'board'
            );
            break;
        
        // id 27, age 3: Engineering        
        case "27N1A":
            // "You may splay your red cards left"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => true,
                
                'splay_direction' => 1 /* left */,
                'color' => array(1) /* red */
            );
            break;
                
            case "27N1":
                $step_max = 1; // --> 1 interactions: see B
                break;
                
        // id 28, age 3: Optics
        case "28N1A":
            // "An opponent with fewer points than you"
            if (self::getGameStateValue('release_version') >= 1) {
                $options = array(
                    'player_id' => $player_id,
                    'can_pass' => false,
                    
                    'choose_player' => true,
                    'players' => self::getActiveOpponentsWithFewerPoints($player_id)
                );
            } else {
                $options = array(
                    'player_id' => $player_id,
                    'can_pass' => false,
                    
                    'choose_opponent_with_fewer_points' => true
                );
            }
            break;
            
        case "28N1B":
            // "Transfer a card from your score pile to the opponent score pile"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => false,
                
                'owner_from' => $player_id,
                'location_from' => 'score',
                'owner_to' => self::getGameStateValue('choice'), // ie the opponent chosen on the previous step
                'location_to' => 'score'
            );
            break;

        // id 29, age 3: Compass        
        case "29D1A":
            // "Transfer a top non-green card with a leaf from your board to my board"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => false,
                
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
                'can_pass' => false,
                
                'owner_from' => $launcher_id,
                'location_from' => 'board',
                'owner_to' => $player_id,
                'location_to' => 'board',
                
                'without_icon' => 2 /* without a leaf */
            );
            break;
            
        // id 30, age 3: Paper        
        case "30N1A":
            // "You may splay your green or blue cards left"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => true,
                
                'splay_direction' => 1 /* left */,
                'color' => array(0, 2) /* blue or green */
            );
            break;
            
        // id 31, age 3: Machinery        
        case "31N1A":
            // "Score a card from your hand with a tower"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => false,
                
                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => $player_id,
                'location_to' => 'score',
                
                'with_icon' => 4, /* tower */
                
                'score_keyword' => true
            );
            break;
          
        case "31N1B":
            // "You may splay your red cards left"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => true,
                
                'splay_direction' => 1 /* left */,
                'color' => array(1) /* red */
            );
            break;

        // id 32, age 3: Medicine
        case "32D1A":
            // "... with the lowest card in my score pile"
            $options = array(
                'player_id' => $launcher_id,
                'n' => 1,
                'can_pass' => false,
                
                'owner_from' => $launcher_id,
                'location_from' => 'score',
                'owner_to' => $player_id,
                'location_to' => 'score',
                
                'age' => self::getMinAgeInScore($launcher_id)
            );
            break;
            
        case "32D1B":
            // "Exchange the highest card in your score pile..."
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => false,
                
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
                'can_pass' => false,
                
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
                
                'splay_direction' => 2 /* right */,
                'color' => array(0) /* blue */
            );
            break;
            
        // id 38, age 4: Gunpowder
        case "38D1A":
            // "Transfer a top card with a tower from your board to my score pile"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => false,
                
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
            for($color=0; $color<5; $color++) {
                if (self::getCurrentSplayDirection($player_id, $color)==1 /* left */) {
                    $splayed_left_colors[] = $color;
                }
            }
            // "You may splay right any one color of your cards currently splayed left"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => true,
                
                'splay_direction' => 2 /* right */,
                'color' => $splayed_left_colors
            );
            break;
            
        // id 40, age 4: Navigation
        case "40D1A":
            // "Transfer a 2 or a 3 from your score pile to my score pile"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => false,
                
                'owner_from' => $player_id,
                'location_from' => 'score',
                'owner_to' => $launcher_id,
                'location_to' => 'score',
                
                'age_min' => 2,
                'age_max' => 3
            );
            break;
            
        // id 41, age 4: Anatomy
        case "41D1A":
            // "Return a card from your score pile"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => false,
                
                'owner_from' => $player_id,
                'location_from' => 'score',
                'owner_to' => 0,
                'location_to' => 'deck'
            );
            break;
        
        case "41D1B":
            // "Return a card of equal value from your board"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => false,
                
                'owner_from' => $player_id,
                'location_from' => 'board',
                'owner_to' => 0,
                'location_to' => 'deck',
                
                'age' => self::getAuxiliaryValue(),
                'not_id' => 188 // Battleship Yamato should not be returned even if an 8 is returned from the score pile
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
                'location_to' => 'deck'
            );
            break;
        
        case "42N1B":
            $number_of_lightbulbs = self::getPlayerSingleRessourceCount($player_id, 3 /* lightbulb */);
            self::notifyPlayer($player_id, 'log', clienttranslate('${You} have ${n} ${lightbulbs}.'), array('You' => 'You', 'n' => $number_of_lightbulbs, 'lightbulbs' => $lightbulb));
            self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} has ${n} ${lightbulbs}.'), array('player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id), 'n' => $number_of_lightbulbs, 'lightbulbs' => $lightbulb));
            // "Score a card from your hand for every two lightbulbs on your board"
            $options = array(
                'player_id' => $player_id,
                'n' => self::intDivision($number_of_lightbulbs, 2),
                'can_pass' => false,
                
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
                'can_pass' => false,
                
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
                
                'splay_direction' => 2 /* right */,
                'color' => array(2) /* green */
            );
            break;
        
        // id 44, age 4: Reformation
        case "44N1A":
            $number_of_leaves = self::getPlayerSingleRessourceCount($player_id, 2);
            self::notifyPlayer($player_id, 'log', clienttranslate('${You} have ${n} ${leaves}.'), array('You' => 'You', 'n' => $number_of_leaves, 'leaves' => $leaf));
            self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} has ${n} ${leaves}.'), array('player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id), 'n' => $number_of_leaves, 'leaves' => $leaf));
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
                
                'splay_direction' => 2 /* right */,
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
                
                'splay_direction' => 2 /* right */,
                'color' => array(0) /* blue */
            );
            break;
        
        case "45N2A":
            // "Return a card from your score pile"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => false,
                
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
                'can_pass' => false,
                
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
                
                'splay_direction' => 2 /* right */,
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
                'can_pass' => false,
                
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
                'can_pass' => false,
                
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
                'can_pass' => false,
                
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
                
                'splay_direction' => 2 /* right */,
                'color' => array(2) /* green */
            );
            break;

        // id 50, age 5: Measurement
        case "50N1A":
            if (self::getGameStateValue('game_rules') == 1) { // Last edition
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
            else { // First edition
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
            }
            break;
            
        case "50N1B":
            // "Choose a color"
            $options = array(
                'player_id' => $player_id,
                'can_pass' => false,
                
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
                'can_pass' => false,
                
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
                
                'splay_direction' => 2 /* right */,
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
                'can_pass' => false,
                
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
                
                'splay_direction' => 2 /* right */,
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
                
                'age' => self::getMaxAgeInScore($player_id)
            );
            break;
        
        // id 57, age 6: Industrialisation
        case "57N2A":
            // "You may splay your red or purple cards right"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => true,
                
                'splay_direction' => 2 /* right */,
                'color' => array(1,4) /* red or purple */
            );
            break;

        // id 59, age 6: Classification
        case "59N1A":
            // "Reveal the color of a card from your hand"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => false,
                
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
                'can_pass' => false,
                
                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => $player_id,
                'location_to' => 'board',
                
                'color' => array(self::getAuxiliaryValue()) /* The color the player has revealed */
            );
            break;
        
        // id 60, age 6: Metric system
        case "60N1A":
            // "You may splay any one color of your cards right"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => true,
                
                'splay_direction' => 2 /* right */
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
                'can_pass' => false,
                
                'choose_yes_or_no' => true
            );
            break;
        
        case "61N2A":
            // "You may splay your yellow cards right"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => true,
                
                'splay_direction' => 2 /* right */,
                'color' => array(3) /* yellow */
            );
            break;
        
        // id 62, age 6: Vaccination
        case "62D1A":
            // "Return all the lowest cards in your score pile"
            $options = array(
                'player_id' => $player_id,
                'can_pass' => false,
                
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
                'can_pass' => false,
                
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
                
                'splay_direction' => 2 /* right */,
                'color' => array(1,4) /* red or purple */
            );
            break;
            
        // id 65, age 7: Evolution          
        case "65N1A":
            // The player faces a choice
            $options = array(
                'player_id' => $player_id,
                'can_pass' => true, // Interpretation of the rules: the player can pass
                
                'choose_yes_or_no' => true
            );
            break;
             
        case "65N1B":
            // "Return a card from your score pile"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => false,
                
                'owner_from' => $player_id,
                'location_from' => 'score',
                'owner_to' => 0,
                'location_to' => 'deck'
            );
            break;
        
        // id 66, age 7: Publications
        case "66N1A":
            // "You may rearrange the order of one color of cards on your board"
            $options = array(
                'player_id' => $player_id,
                'can_pass' => true,
                
                'choose_rearrange' => true
            );
            break;
        
        case "66N2A":
            // "You may splay your blue or yellow cards up"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => true,
                
                'splay_direction' => 3 /* up */,
                'color' => array(0,3) /* blue or yellow */
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
                'can_pass' => false,
                
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
                'can_pass' => false,
                
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
                'can_pass' => false,
                
                'choose_yes_or_no' => true
            );
            break;

        // id 70, age 7: Electricity
        case "70N1A":
            // "Return all your top cards without a factory"
            $options = array(
                'player_id' => $player_id,
                'can_pass' => false,
                
                'owner_from' => $player_id,
                'location_from' => 'board',
                'owner_to' => 0,
                'location_to' => 'deck',
                
                'without_icon' => 5 /* factory */
            );
            break;
        
        // id 71, age 7: Refrigeration
        case "71D1A":
            // "Return half (rounded down) of the cards in your hand"
            $options = array(
                'player_id' => $player_id,
                'n' => self::intDivision(self::countCardsInLocation($player_id, 'hand'), 2), // Half (rounded down)
                'can_pass' => false,
                
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
        
        // id 72, age 7: Sanitation
        case "72D1A":
            // "... with the lowest card in my hand"
            $options = array(
                'player_id' => $launcher_id,
                'n' => 1,
                'can_pass' => false,
                
                'owner_from' => $launcher_id,
                'location_from' => 'hand',
                'owner_to' => $player_id,
                'location_to' => 'hand',
                
                'age' => self::getMinAgeInHand($launcher_id)
            );
            break;
        
        case "72D1B":
        case "72D1C":
            // "Exchange the highest card in your hand..." (two times)
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => false,
                
                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => $launcher_id,
                'location_to' => 'hand',
                
                'age' => self::getMaxAgeInHand($player_id)
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
        case "74N1A":
            // "Return all the cards in your hand"
            $options = array(
                'player_id' => $player_id,
                'can_pass' => false,
                
                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => 0,
                'location_to' => 'deck',
            );
            break;
        
        case "74N2A":
            $splayed_right_colors = array();
            for($color=0; $color<5; $color++) {
                if (self::getCurrentSplayDirection($player_id, $color)==2 /* right */) {
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
            self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} has ${n} ${clocks}.'), array('player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id), 'n' => $number_of_clocks, 'clocks' => $clock));
            // "Return a card in any opponent's score pile for every two clocks on your board"
            $options = array(
                'player_id' => $player_id,
                'n' => self::intDivision($number_of_clocks, 2),
                'can_pass' => false,
                
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
                'can_pass' => false,
                
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
                'can_pass' => false,
                
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
                'can_pass' => false,
                
                'choose_value' => true
            );
            break;
            
        case "80N1C":
            // "Return all cards of that value from all score piles"
            $options = array(
                'player_id' => $player_id,
                'can_pass' => false,
                
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
                'can_pass' => false,
                
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
                'can_pass' => false,
                
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
                'can_pass' => false,
                
                'choose_two_colors' => true 
            );
            break;
            
        case "83N1B":
            // "You may splay that color of your cards up"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => true,
                
                'splay_direction' => 3, /* up */
                'color' => array(self::getAuxiliaryValue())
            );            
            break;
        
        // id 84, age 8: Socialism     
        case "84N1A":
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
                'can_pass' => false,
                
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
                'can_pass' => false,
                
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
                'can_pass' => false,
                
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
                'can_pass' => false,
                
                'owner_from' => $player_id,
                'location_from' => 'revealed',
                'owner_to' => $launcher_id,
                'location_to' => 'board'
            );
            break;        
        
        // id 90, age 9: Satellites
        case "90N1A":
            // "Return all cards from you hand"
            $options = array(
                'player_id' => $player_id,
                'can_pass' => false,
                
                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => 0,
                'location_to' => 'deck'
            );
            break;

        case "90N2A":
            // "You may splay your purple cards up"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => true,
                
                'splay_direction' => 3, /* up */
                'color' => array(4) /* purple */
            );
            break;
            
        case "90N3A":
            // "Meld a card from your hand"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => false,
                
                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => $player_id,
                'location_to' => 'board'
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
                'can_pass' => false,
                
                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => $player_id,
                'location_to' => 'score',
                
                'score_keyword' => true
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
        
        // id 93, age 9: Services
        case "93D1A":
            // "Transfer a top card from my board without a leaf to your hand"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => false,
                
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
                'can_pass' => false,
                
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
                'can_pass' => false,
                
                'owner_from' => 'any opponent',
                'location_from' => 'board',
                'owner_to' => $player_id,
                'location_to' => 'score',
                
                'with_icon' => 2 /* leaf */
            );
            break;
        
        // id 97, age 10: Miniaturization
        case "97N1A":
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
        
        // id 99, age 10: Databases
        case "99D1A":
            // "Return half (rounded up) of the cards in your score pile"
            $options = array(
                'player_id' => $player_id,
                'n' => ceil(self::countCardsInLocation($player_id, 'score') / 2),
                'can_pass' => false,
                
                'owner_from' => $player_id,
                'location_from' => 'score',
                'owner_to' => 0,
                'location_to' => 'deck'
            );        
            break;
        
        // id 100, age 10: Self service
        case "100N1A":
            // "Execute each of the non-demand dogma effects of any other top card on your board" (a card with no non-demand effect can be chosen)
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => false,
                
                'owner_from' => $player_id,
                'location_from' => 'board',
                'owner_to' => $player_id, // Nothing is to be done with that card
                'location_to' => 'board',
                
                // Exclude the card currently being executed (it's possible for the effects of Self Service to be executed as if it were on another card)
                'not_id' => (self::getGameStateValue('release_version') >= 1 ? self::getCurrentNestedCardState()['executing_as_if_on_card_id'] : 100),
            );       
            break;
        
        // id 101, age 10: Globalization
        case "101D1A":
            // "Return a top card with a ${icon_2} on your board" 
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => false,
                
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
                'can_pass' => false,
                
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

         // id 110, Artifacts age 1: Treaty of Kadesh
         case "110C1A":
            // "Return all top cards from your board with a demand effect"
            $options = array(
                'player_id' => $player_id,
                'can_pass' => false,

                'owner_from' => $player_id,
                'location_from' => 'board',
                'owner_to' => 0,
                'location_to' => 'deck',

                'has_demand_effect' => true
            );
            break;
        
        case "110N1A":
            // "Score a top, non-blue card from your board with a demand effect"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => false,

                'owner_from' => $player_id,
                'location_from' => 'board',
                'owner_to' => $player_id,
                'location_to' => 'score',

                'color' => array(1,2,3,4), // non-blue
                'has_demand_effect' => true,

                'score_keyword' => true
            );
            break;
        
        // id 112, Artifacts age 1: Basur Hoyuk Tokens
        case "112N1A":
            // "Return the drawn card and all cards from your score pile"
            $options = array(
                'player_id' => $player_id,
                'can_pass' => false,
                
                'owner_from' => $player_id,
                'location_from' => 'revealed,score',
                'owner_to' => 0,
                'location_to' => 'deck',
            );
            break;

        // id 113, Artifacts age 1: Holmegaard Bows
        case "113C1A":
            // "Transfer the highest top card with a tower on your board to my hand"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => false,
                
                'owner_from' => $player_id,
                'location_from' => 'board',
                'owner_to' => $launcher_id,
                'location_to' => 'hand',
                
                'with_icon' => 4, // tower
                'age' => self::getMaxAgeOnBoardTopCardsWithIcon($player_id, 4 /* tower */),
            );
            break;
        
        // id 114, Artifacts age 1: Papyrus of Ani
        case "114N1A":
            // "Return a purple card from your hand"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => false,
                
                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => 0,
                'location_to' => 'deck',

                'color' => array(4)
            );
            break;

        case "114N1B":
            // Choose any type
            $options = array(
                'player_id' => $player_id,
                'can_pass' => false,
                
                'choose_type' => true,
                'type' => self::getActiveCardTypes()
            );
            break;
        
        
        // id 115, Artifacts age 1: Pavlovian Tusk
        case "115N1A":
            // "Return one of the drawn cards"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => false,
                
                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => 0,
                'location_to' => 'deck',

                'card_id_1' => $card_id_1,
                'card_id_2' => $card_id_2,
                'card_id_3' => $card_id_3
            );
            break;
        
        case "115N1B":
            // "Score one of the drawn cards"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => false,
                
                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => $player_id,
                'location_to' => 'score',

                'card_id_1' => $card_id_1,
                'card_id_2' => $card_id_2,
                'card_id_3' => $card_id_3,

                'score_keyword' => true
            );
            break;

        // id 116, Artifacts age 1: Priest-King
        case "116N1A":
            // "Score a card from your hand"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => false,
                
                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => $player_id,
                'location_to' => 'score',

                'score_keyword' => true
            );
            break;

        case "116N2A":
            // "Claim an achievement, if eligible"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => false,
                
                'owner_from' => 0,
                'location_from' => 'achievements',
                'owner_to' => $player_id,
                'location_to' => 'achievements',

                'require_achievement_eligibility' => true
            );
            break;
        
        // id 118, Artifacts age 1: Jiskairumoko Necklace
        case "118C1A":
            // "Return a card from your score pile"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => false,
                
                'owner_from' => $player_id,
                'location_from' => 'score',
                'owner_to' => 0,
                'location_to' => 'deck'
            );
            break;
        
        case "118C1B":
            // "Transfer an achievement of the same value from your achievements to mine"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => false,
                
                'owner_from' => $player_id,
                'location_from' => 'achievements',
                'owner_to' => $launcher_id,
                'location_to' => 'achievements',
                
                'age' => self::getGameStateValue('age_last_selected')
            );
            break;
        
        // id 120, Artifacts age 1: Lurgan Canoe
        case "120N1A":
            // "Meld a card from your hand"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => false,
                
                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => $player_id,
                'location_to' => 'board'
            );
            break;
        
        // id 121, Artifacts age 1: Xianrendong Shards
        case "121N1A":
            // "Reveal three cards from your hand"
            $options = array(
                'player_id' => $player_id,
                'n' => 3,
                'can_pass' => false,
                
                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => $player_id,
                'location_to' => 'revealed'
            );
            break;
        
        case "121N1B":
            // "Score two"
            $options = array(
                'player_id' => $player_id,
                'n' => 2,
                'can_pass' => false,

                // Propagate values required to detect whether the scored cards are the same color
                'card_id_1' => $card_id_1,
                'card_id_2' => $card_id_2,
                'card_id_3' => $card_id_3,
                
                'owner_from' => $player_id,
                'location_from' => 'revealed',
                'owner_to' => $player_id,
                'location_to' => 'score',

                'score_keyword' => true
            );
            break;
        
        // id 122, Artifacts age 1: Mask of Warka
        case "122N1A":
            // "Choose a color"
            $options = array(
                'player_id' => $player_id,
                'can_pass' => false,
                
                'choose_color' => true
            );
            break;

        case "122N1B":
            // "return them"
            $options = array(
                'player_id' => $player_id,
                'can_pass' => false,
                
                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => 0,
                'location_to' => 'deck',

                'color' => array(self::getAuxiliaryValue()),
            );
            break;
            
        // id 123, Artifacts age 1: Ark of the Covenant
        case "123N1A":
            // "Return a card from your hand"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => false,
                
                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => 0,
                'location_to' => 'deck',
            );
            break;
            
        // id 124, Artifacts age 1: Tale of the Shipwrecked Sailor
        case "124N1A":
            // "Choose a color"
            $options = array(
                'player_id' => $player_id,
                'can_pass' => false,
                
                'choose_color' => true
            );
            break;
        
        case "124N1B":
            // "Meld a card of the chosen color from your hand"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => false,

                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => $player_id,
                'location_to' => 'board',
                
                'color' => array(self::getAuxiliaryValue())
            );
            break;
        
        // id 126, Artifacts age 1: Rosetta Stone
        case "126N1A":
            // "Choose a type"
            $options = array(
                'player_id' => $player_id,
                'can_pass' => false,

                'choose_type' => true,
                'type' => self::getActiveCardTypes()
            );
            break;

        case "126N1B":
            // "Meld one (of the drawn cards)"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => false,

                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => $player_id,
                'location_to' => 'board',

                'card_id_1' => $card_id_1,
                'card_id_2' => $card_id_2
            );
            break;

        case "126N1C":
            // Choose an opponent to transfer the other card to
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => false,
                
                'choose_player' => true,
                'players' => self::getActiveOpponents($player_id)
            );
            break;
        
        // id 128, Artifacts age 2: Babylonian Chronicles
        case "128C1A":
            // "Transfer a top non-red card with a tower from your board to my board"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => false,
                
                'owner_from' => $player_id,
                'location_from' => 'board',
                'owner_to' => $launcher_id,
                'location_to' => 'board',
                
                'with_icon' => 4, // tower
                'color' => array(0, 2, 3, 4) // non-red
            );
            break;

        // id 129, Artifacts age 2: Holy Lance
        case "129C1A":
            // "Transfer a top Artifact from your board to my board!"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => false,
                
                'owner_from' => $player_id,
                'location_from' => 'board',
                'owner_to' => $launcher_id,
                'location_to' => 'board',
                 
                'type' => array(1) // Artifact
            );
            break;

        // id 130, Artifacts age 1: Baghdad Battery
        case "130N1A":
            // "Meld two cards from your hand"
            $options = array(
                'player_id' => $player_id,
                'n' => 2,
                'can_pass' => false,
                
                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => $player_id,
                'location_to' => 'board'
            );
            break;

        // id 131, Artifacts age 2: Holy Grail
        case "131N1A":
            // "Return a card from your hand"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => false,
                
                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => 0,
                'location_to' => 'deck'
            );
            break;
            
        case "131N1B":
            // "Claim an achievement of matching value, ignoring eligibility"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => false,
                
                'age' => self::getGameStateValue('age_last_selected'),
                'owner_from' => 0,
                'location_from' => 'achievements',
                'owner_to' => $player_id,
                'location_to' => 'achievements',

                'require_achievement_eligibility' => false
            );
            break;

        // id 132, Artifacts age 2: Terracotta Army
        case "132C1A":
            // "I compel you to return a top card with no tower"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => false,
                
                'owner_from' => $player_id,
                'location_from' => 'board',
                'owner_to' => 0,
                'location_to' => 'deck',
                
                'without_icon' => 4 // tower
            );
            break;

        case "132N1A":
            // "Score a card from your hand with no towers"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => false,
                
                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => $player_id,
                'location_to' => 'score',
                
                'without_icon' => 4, // tower

                'score_keyword' => true
            );
            break;
        
        // id 134, Artifacts age 2: Cyrus Cylinder
        case "134N1A":
            // "Choose any other top purple card on any player's board"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => false,
                
                'owner_from' => 'any player',
                'location_from' => 'board',
                'location_to' => 'none',

                'color' => array(4), // Purple

                // Exclude the card currently being executed (it's possible for the effects of Cyrus Cylinder to be executed as if it were on another card)
                'not_id' => self::getCurrentNestedCardState()['executing_as_if_on_card_id'],
            );
            break;

        case "134N1B":
            // Prompt player to pick a stack which to splay left. (no purple card to execute)
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => false,
                
                'owner_from' => 'any player',
                'location_from' => 'board',
                'location_to' => 'none'
            );
            break;
            
        case "134N1+A":
            // Prompt player to pick a stack which to splay left.
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => false,
                
                'owner_from' => 'any player',
                'location_from' => 'board',
                'location_to' => 'none'
            );
            break;
            
        // id 135, Artifacts age 3: Dunhuang Star Chart
        case "135N1A":
            $hand_count = self::countCardsInLocation($player_id, 'hand');
            self::setAuxiliaryValue($hand_count);
            // "Return all cards from your hand"
            $options = array(
                'player_id' => $player_id,
                'can_pass' => false,

                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => 0,
                'location_to' => 'deck'
            );
            break;

        // id 136, Artifacts age 3: Charter of Liberties
        case "136N1A":
            // "Tuck a card from your hand"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => false,

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
                'can_pass' => false,

                'owner_from' => 'any player',
                'location_from' => 'board',
                'location_to' => 'none',
                
                'has_splay_direction' => array(1, 2, 3) // Left, right, or up
            );
            break;

        // id 137, Artifacts age 2: Excalibur
        case "137C1A":
            // "I compel you to transfer a top card of higher value than my
            // top card of the same color from your board to my board!"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => false,
                
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
                'can_pass' => false,
                
                'owner_from' => $player_id,
                'location_from' => 'board',
                'location_to' => 'none'
            );
            break;
            
        // id 139, Artifacts age 3: Philosopher's Stone
        case "139N1A":
            // "Return a card from your hand"
            $options = array(
                'player_id' => $player_id,
                'can_pass' => false,
                'n' => 1,

                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => 0,
                'location_to' => 'deck'
            );
            break;

        case "139N1B":
            // "Score a number of cards from your hand equal to the value of the card returned"
            $age_selected = self::getGameStateValue('age_last_selected');
            $options = array(
                'player_id' => $player_id,
                'n' => $age_selected,
                'can_pass' => false,
                
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
                'can_pass' => false,
                
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
                'can_pass' => false,

                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => 0,
                'location_to' => 'deck'
            );
            break;

        case "144N1B":
            // "Return a top card from your board of the returned card's color"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => false,

                'owner_from' => $player_id,
                'location_from' => 'board',
                'owner_to' => 0,
                'location_to' => 'deck',

                'color' => array(self::getGameStateValue('color_last_selected'))
            );
            break;

        case "144N1C":
            // "Return a card from score pile of the returned card's color"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => false,

                'owner_from' => $player_id,
                'location_from' => 'score',
                'owner_to' => 0,
                'location_to' => 'deck',

                'color' => array(self::getGameStateValue('color_last_selected'))
            );
            break;

        case "144N1D":
            // "Claim an achievement ignoring eligibility"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => false,

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
                'can_pass' => false,
                
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
                'can_pass' => false,

                'owner_from' => $player_id,
                'location_from' => 'score',
                'owner_to' => 0,
                'location_to' => 'deck'
            );
            break;
            
        case "146N1B":            
            // "Return the drawn cards"
             $options = array(
                'player_id' => $player_id,
                'n' => 2,
                'can_pass' => false,

                'owner_from' => $player_id,
                'location_from' => 'hand',
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
                'can_pass' => false,

                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => $player_id,
                'location_to' => 'revealed',

                'card_id_1' => $card_id_1,
                'card_id_2' => $card_id_2
            );
            break;

        // id 147, Artifacts age 4: East India Company Charter
        case "147N1A":
            // "Choose a value other than 5"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => false,
                
                'choose_value' => true,

                'age' => array(1, 2, 3, 4, 6, 7, 8, 9, 10)
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
                'can_pass' => false,
                
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
                'can_pass' => false,
                
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
                'can_pass' => false,

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
                'can_pass' => false,

                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => $player_id,
                'location_to' => 'board',

                'color' => array(0) // blue
            );
            break;

        case "149N1C":
            // "Score a card from your hand"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => false,

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
                'can_pass' => false,

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
                    'location_to' => 'board'
                );
            }
            break;
            
        case "150N1B":
            // "Meld a card from your hand"
            self::setAuxiliaryValue(0);
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => false,

                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => $player_id,
                'location_to' => 'board'
            );
            break;

        // id 151, Artifacts age 4: Moses
        case "151N1A":    
            // "Score a top card with a crown"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => false,
                
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
            // "Choose a number"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => false,
                
                // TODO(https://github.com/micahstairs/bga-innovation/issues/225): Allow player to pick an integer in 0-999 range instead of 1-10.
                'choose_value' => true
            );
            break;

        case "152N1B":
            // "and a color"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => false,
                
                'choose_color' => true
            );
            break;

        case "152N1C":
            // "Otherwise, return all cards from your hand"
            $options = array(
                'player_id' => $player_id,
                'can_pass' => false,
                
                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => 0,
                'location_to' => 'deck'
            );
            break;
            
        // id 155, Artifacts age 5: Boerhavve Silver Microscope
        case "155N1A":
            // "Return the lowest card in your hand"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => false,

                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => 0,
                'location_to' => 'deck',

                'age' => self::getMinAgeInHand($player_id)
            );
            break;

        case "155N1B":
            // "and the lowest top card on your board"
            // TODO(LATER): Create new getMinAgeOnBoardTopCards function instead (based on getMaxAgeOnBoardTopCards).
            $all_cards = self::getTopCardsOnBoard($player_id);
            $ages = array();
            foreach($all_cards as $card) {
                $ages[] = $card['faceup_age'];
            }
            if (empty($ages)) {
                $ages[] = 0;
            }

            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => false,

                'owner_from' => $player_id,
                'location_from' => 'board',
                'owner_to' => 0,
                'location_to' => 'deck',

                'age' => min($ages)
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
            self::setAuxiliaryValue(self::getValueFromBase16Array($ages_on_top));

            // "Return all non-blue top cards from your board"
            $options = array(
                'player_id' => $player_id,
                'can_pass' => false,

                'owner_from' => $player_id,
                'location_from' => 'board',
                'owner_to' => 0,
                'location_to' => 'deck',

                'color' => array(1, 2, 3, 4) // non-blue
            );
            break;

        // id 157, Artifacts age 5: Bill of Rights
        case "157C1A":
            $options = array(
                'player_id' => $player_id,
                'can_pass' => false,
                
                'choose_color' => true,
                'color' => self::getGameStateValueAsArray('color_array')
            );            
            break;
        
        // id 158, Artifacts age 5: Ship of the Line Sussex
        case "158N1A":
            // "Return all cards from your score pile"
            $options = array(
                'player_id' => $player_id,
                'can_pass' => false,
                
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
                'can_pass' => false,
                
                'choose_color' => true
            );
            break;
            
        // id 160, Artifacts age 5: Hudson's Bay Company Archives
        case "160N1A":    
            // "Meld a card from your score pile"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => false,

                'owner_from' => $player_id,
                'location_from' => 'score',
                'owner_to' => $player_id,
                'location_to' => 'board'
            );
            break;
            
        // id 161, Artifacts age 5: Gujin Tushu Jinsheng
        case "161N1A":
            // "Choose any other top card on any other board"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => false,
                
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
                'can_pass' => false,
                
                'choose_value' => true
            );       
            break;

        case "162N1B":
            // Return the card to top of deck
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => false,
                'enable_autoselection' => false, // Give the player the chance to read the card
                
                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => 0,
                'location_to' => 'deck',

                'bottom_to' => false, // Topdeck
                
                'card_id_1' => self::getGameStateValue('card_id_1')
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
                'can_pass' => false,

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
                'can_pass' => false,
                
                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => $player_id,
                'location_to' => 'board'
            );
            break;

        case "164N1B":
            // "Claim an achievement of matching value, ignoring eligibility"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => false,
                
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
                'can_pass' => false,
                
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
                'can_pass' => false,
                
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
                'can_pass' => false,
                
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
                'can_pass' => false,
                
                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => $player_id,
                'location_to' => 'revealed'
            );
            break;

        case "167C1B":
            // "Return it"
            $revealed_card = self::getCardsInLocation($player_id, 'revealed')[0];
            self::transferCardFromTo($revealed_card, 0, 'deck');

            // "And all cards of its color from your board"
            $options = array(
                'player_id' => $player_id,
                'can_pass' => false,
                
                'owner_from' => $player_id,
                'location_from' => 'pile',
                'owner_to' => 0,
                'location_to' => 'deck',
                
                'color' => array(self::getGameStateValue('color_last_selected'))
            );
            break;

        // id 168, Artifacts age 6: U.S. Declaration of Independence
        case "168C1A":
            // "Transfer the highest card in your hand to my hand"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => false,
                
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
                'can_pass' => false,
                
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
                'can_pass' => false,
                
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
                'can_pass' => false,
                
                'choose_three_colors' => true
            );
            break;

        case "170N1B":
            // "Return all cards of the drawn card's color from your score pile"
            $options = array(
                'player_id' => $player_id,
                'can_pass' => false,
                
                'owner_from' => $player_id,
                'location_from' => 'score',
                'owner_to' => 0,
                'location_to' => 'deck',

                'color' => array(self::getAuxiliaryValue())
            );
            break;

        // id 171, Artifacts age 6: Stamp Act
        case "171C1A":
            // "Transfer a card of value equal to the top yellow card on your board from your score pile to mine"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => false,
                
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
                'can_pass' => false,
                
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
                'can_pass' => false,

                'choose_color' => true,
                'color' =>  $color_array
            );
            break;
            
        case "173N1B":
            // "Claim an achievement ignoring eligibility"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => false,

                'owner_from' => 0,
                'location_from' => 'achievements',
                'owner_to' => $player_id,
                'location_to' => 'achievements',

                'require_achievement_eligibility' => false
            );
            break;
            
        // id 174, Artifacts age 6: Marcha Real
        case "174N1A":
            // Reveal two cards from your hand
            $options = array(
                'player_id' => $player_id,
                'n' => 2,
                'can_pass' => false,

                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => $player_id,
                'location_to' => 'revealed'
            );
            break;
    
        case "174N1B":
            // "Claim an achievement ignoring eligibility"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => false,

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
                'can_pass' => false,
                
                'owner_from' => $player_id,
                'location_from' => 'board',
                'location_to' => 'none',

                'color' => self::getAuxiliaryValueAsArray()
            );

            break;

        case "175N1B":
            // "Choose two top cards on your board of the same value"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => false,
                
                'owner_from' => $player_id,
                'location_from' => 'board',
                'location_to' => 'none',

                'not_id' => self::getGameStateValue('id_last_selected'),
                'age' => self::getGameStateValue('age_last_selected')
            );
            break;

        // id 177, Artifacts age 7: Submarine H. L. Hunley
        case "177C1A":
            // "Return all cards of its color from your board"
            $options = array(
                'player_id' => $player_id,
                'can_pass' => false,
                
                'owner_from' => $player_id,
                'location_from' => 'pile',
                'owner_to' => 0,
                'location_to' => 'deck',
                
                'color' => array(self::getAuxiliaryValue())
             );
            break;
            
        // id 178, Artifacts age 7: Jedlik's Electromagnetic Self-Rotor
        case "178N1A":
            // "Claim an achievement of value 8 if it is available, ignoring eligibility"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => false,

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
                'can_pass' => false,

                'choose_value' => true
            );
            break;

        // id 180, Artifacts age 7: Hansen Writing Ball
        case "180C1A":
            // "Meld a blue card"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => false,

                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => $player_id,
                'location_to' => 'board',

                'color' => array(0)
            );

            break;

        case "180C1B":
            // "Transfer all cards in your hand to my hand"
            // TODO(ARTIFACTS): This should be automated instead of an interaction.
            $options = array(
                'player_id' => $player_id,
                'can_pass' => false,

                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => $launcher_id,
                'location_to' => 'hand'
            );

            break;

        // id 181, Artifacts age 7: Colt Paterson Revolver
        case "181C1A":
            // "Return all cards in your hand"
            $options = array(
                'player_id' => $player_id,
                'can_pass' => false,
                
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
                'can_pass' => false,
                
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
                'can_pass' => false,
                
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
                'can_pass' => false,
                
                'owner_from' => $player_id,
                'location_from' => 'score',
                'owner_to' => $player_id,
                'location_to' => 'board',
                'bottom_to' => true,

                'color' => array(self::getGameStateValue('color_last_selected'))
            );
            break;

        // id 183, Artifacts age 7: Roundhay Garden Scene
        case "183N1A":
            // "Meld the highest card from your score pile"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => false,
                
                'age' => self::getMaxAgeInScore($player_id),
                
                'owner_from' => $player_id,
                'location_from' => 'score',
                'owner_to' => $player_id,
                'location_to' => 'board'
            );
            break;
            
        // id 184, Artifacts age 7: The Communist Manifesto
        case "184N1A":
            // Choose a player
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => false,
                
                'choose_player' => true,
                'players' => self::getGameStateValueAsArray('player_array')
            );
            break;

        case "184N1B":
            // "Transfer one of the drawn cards to each player's board"
            $player_choice = self::getAuxiliaryValue();            
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => false,
                
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
                'can_pass' => false,
                
                'owner_from' => $player_id,
                'location_from' => 'board',
                'owner_to' => 0,
                'location_to' => 'deck',

                'age' => self::getGameStateValue('age_last_selected') - 1,
                'not_id' => 188 // Battleship Yamato's face-up age is 11 (not 8), so it's never a valid selection
            );
            break;

        case "186N1B":
            // "Otherwise, claim an achievement, ignoring eligibility"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => false,
                
                'owner_from' => 0,
                'location_from' => 'achievements',
                'owner_to' => $player_id,
                'location_to' => 'achievements',

                'require_achievement_eligibility' => false
            );
            break;
        
        // id 187, Artifacts age 8: Battleship Bismarck
        case "187C1A":
            // "Draw and reveal an 8"
            $card = self::executeDraw($player_id, 8, 'revealed');
            self::transferCardFromTo($card, $player_id, 'hand');

            // "Return all cards of the drawn color from your board"
            $options = array(
                'player_id' => $player_id,
                'can_pass' => false,
                
                'owner_from' => $player_id,
                'location_from' => 'pile',
                'owner_to' => 0,
                'location_to' => 'deck',
                
                'color' => array($card['color'])
            );
            break;
            
        // id 190, Artifacts age 8: Meiji-Mura Stamp Vending Machine
        case "190N1A":
            // "Return a card from your hand"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => false,
                
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
            // The value 11 should only be an option when Battleship Yamato is a top card on the player's board.
            $battleship_yamato = self::getCardInfo(188);
            if (self::isTopBoardCard($battleship_yamato) && $battleship_yamato['owner'] === $player_id) {
                $selectable_ages[] = 11;
            }
            
            $options = array(
                'player_id' => $player_id,
                'can_pass' => false,

                'choose_value' => true,
                'age' => $selectable_ages
            );
            break;

        case "191N1B":
            // "Return all cards of that value from all score piles"
            $options = array(
                'player_id' => $player_id,
                'can_pass' => false,
                
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
                'can_pass' => false,

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
                'can_pass' => false,
                
                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => $player_id,
                'location_to' => 'board',
                
                'age' => 8
            );
            break;

        // id 194, Artifacts age 8: 30 World Cup Final Ball
        case "194C1A":
            // "I compel you to return one of your achievements"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => false,
                
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
                'can_pass' => false,
                
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
                'can_pass' => false,

                'owner_from' => $player_id,
                'location_from' => 'board',
                'owner_to' => $launcher_id,
                'location_to' => 'score',

                'has_demand_effect' => true
            );
            break;

         // id 198, Artifacts age 9: Velcro Shoes
         case "198C1A":
            // "Transfer a 9 from your hand to my hand"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => false,
                
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
                'can_pass' => false,
                
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
                'can_pass' => false,
                
                'splay_direction' => 3
            );
            break;
            
        // id 200, Artifacts age 9: Syncom 3
        case "200N1A":
            // "Return all cards from your hand"
            $options = array(
                'player_id' => $player_id,
                'can_pass' => false,
                
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
        
        // id 206, Artifacts age 10: Higgs Boson
        case "206N1A":
            // "Transfer all cards on your board to your score pile"
            // TODO(ARTIFACTS): Do a bulk transfer (like Fission) instead of moving cards one at a time.
            $piles = self::getCardsInLocationKeyedByColor($player_id, 'board');
            for ($i = 0; $i < 5 ; $i++){
                $pile = $piles[$i];
                for ($j = count($pile) - 1; $j >= 0; $j--) { 
                    self::transferCardFromTo($pile[$j], $player_id, 'score', /*bottom_to=*/ false, /*score_keyword=*/ false); 
                }
            }
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
                'can_pass' => false,
                
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
                'can_pass' => false,
                
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

                    'age' => self::getMaxAgeInHand($player_id)
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

            'color' => self::getAuxiliaryValueAsArray()
        );
        break;

        // id 216, Relic age 4: Complex Numbers
        case "216N1A":
            // "You may reveal a card from your hand having exactly the same icons, in type and number, as a top card on your board"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => true,
                
                'owner_from' => $player_id,
                'location_from' => 'hand',
                'owner_to' => $player_id,
                'location_to' => 'revealed'
            );
            $i = 1;
            foreach (self::getTopCardsOnBoard($player_id) as $top_card) {
                $options += array('icon_hash_'.$i++ => $top_card['icon_hash']);
            }
            break;

        case "216N1B":
            // "Claim an achievement of matching value, ignoring eligibility"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => false,
                
                'owner_from' => 0,
                'location_from' => 'achievements',
                'owner_to' => $player_id,
                'location_to' => 'achievements',

                'age' => self::getGameStateValue('age_last_selected'),
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
            
        default:
            // This should not happens
            throw new BgaVisibleSystemException(self::format(self::_("Unreferenced card effect code in section B: '{code}'"), array('code' => $code)));
            break;
        }
        //[BB]||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||
        
        // There wasn't an interaction needed in this step after all
        if ($options == null) {
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

        if (self::getGameStateValue('release_version') >= 1) {
            $nested_card_state = self::getCurrentNestedCardState();
            $card_id = $nested_card_state['card_id'];
            $current_effect_type = $nested_card_state['current_effect_type'];
            $current_effect_number = $nested_card_state['current_effect_number'];
        } else {
            $nested_id_1 = self::getGameStateValue('nested_id_1');
            $card_id = $nested_id_1 == -1 ? self::getGameStateValue('dogma_card_id') : $nested_id_1 ;
            $current_effect_type = $nested_id_1 == -1 ? self::getGameStateValue('current_effect_type') : 1 /* Non-demand effects only*/;
            $current_effect_number = $nested_id_1 == -1 ?  self::getGameStateValue('current_effect_number') : self::getGameStateValue('nested_current_effect_number_1');
        }

        $step = self::getStep();
        $n = self::getGameStateValue('n');
        
        $crown = self::getIconSquare(1);
        $leaf = self::getIconSquare(2);
        $lightbulb = self::getIconSquare(3);
        $tower = self::getIconSquare(4);
        $factory = self::getIconSquare(5);
        $clock = self::getIconSquare(6);
        
        if (!self::isZombie(self::getActivePlayerId())) {
            try {
                //||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||
                // [D] SPECIFIC CODE: what to be done after the player finished his selection of cards/colors/values?
                $letters = array(1 => 'A', 2 => 'B', 3 => 'C', 4 => 'D');
                $code = self::getCardExecutionCodeWithLetter($card_id, $current_effect_type, $current_effect_number, $step);
                self::trace('[D]'.$code.' '.self::getPlayerNameFromId($player_id).'('.$player_id.')'.' | '.self::getPlayerNameFromId($launcher_id).'('.$launcher_id.')');
                switch($code) {
                // The first number is the id of the card
                // D1 means the first (and single) I demand effect
                // N1 means the first non-demand effect
                // N2 means the second non-demand effect
                // N3 means the third non-demand effect
                
                // The letter indicates the step : A for the first one, B for the second
                
                // Setting the $step_max variable means there is interaction needed with the player
                
                // id 0, age 1: Pottery
                case "0N1A":
                    if ($n > 0) { // "If you returned any"
                        switch($n) {
                        case 1:
                            self::notifyGeneralInfo(clienttranslate("One card has been returned"));
                            break;
                        case 2:
                            self::notifyGeneralInfo(clienttranslate("Two cards have been returned"));
                            break;
                        case 3:
                            self::notifyGeneralInfo(clienttranslate("Three cards have been returned"));
                            break;
                        }
                        self::executeDraw($player_id, $n, 'score'); // "Draw and score a card of value equal to the number of cards you returned" 
                    }
                    break;
                
                // id 1, age 1: Tools
                case "1N1A":
                    if ($n > 0) { // "If you do"
                        self::executeDraw($player_id, 3, 'board'); // "Draw and meld a 3"
                    }
                    break;
                case "1N2A":
                    if ($n > 0) {
                        for ($times = 0; $times < 3; $times++) { // "If you do"
                            self::executeDraw($player_id, 1); // "Draw three 1"                
                        }
                    }
                    break;
                    
                // id 5, age 1: Oars
                case "5D1A":
                    if ($n > 0) { // "If you do"
                        self::executeDraw($player_id, 1); // "Draw a 1"
                        self::setAuxiliaryValue(1); // A transfer has been made, flag it
                        if (self::getGameStateValue('game_rules') == 1) { // Last edition => additionnal rule
                            $step--; self::incrementStep(-1); // "Repeat that dogma effect"
                        }
                    } else {
                        // Reveal hand to prove that they have no crowns.
                        self::revealHand($player_id);
                    }
                    break;
                
                // id 9, age 1: Agriculture
                case "9N1A":
                    if ($n > 0) { // "If you do"
                        $age_to_draw_in = self::getGameStateValue('age_last_selected') + 1;
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
                        $achievement = self::getCardInfo(106); // Monument achievement
                        if ($achievement['owner'] == 0) {
                            self::notifyGeneralInfo(clienttranslate("At least four cards have been melded."));
                            self::transferCardFromTo($achievement, $player_id, 'achievements'); // "Claim the Monument achievement"
                        }
                        else {
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
                        self::executeDraw($player_id, self::getGameStateValue('age_last_selected') + 1, 'board'); // "Draw and meld a card of value one higher than the card you returned"
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
                            self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} has no top red card on your board.'), array('player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id)));
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
                            self::notifyAllPlayersBut($player_id, 'log', clienttranslate('Each card ${player_name} returned has the same value.'), array('player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id)));
                        }
                        else if ($number_of_cards_to_score > 1) {
                            $n = self::getTranslatedNumber($number_of_cards_to_score);
                            self::notifyPlayer($player_id, 'log', clienttranslate('${You} returned cards of ${n} different values.'), array('i18n' => array('n'), 'You' => 'You', 'n' => $n));
                            self::notifyAllPlayersBut($player_id, 'log', clienttranslate('There are ${n} different values that can be found in the cards ${player_name} returned.'), array('i18n' => array('n'), 'player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id), 'n' => $n));
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
                    if (self::getGameStateValue('game_rules') == 1) { // Last edition => additional rule
                        if ($n > 0) { // "If you do"
                            self::unsplay($player_id, $player_id, self::getGameStateValue('color_last_selected')); // "Unsplay that color of your cards"
                        }
                    }
                    break;
                
                // id 36, age 4: Printing press        
                case "36N1A":
                    if ($n > 0) { // "If you do"
                        $top_purple_card = self::getTopCardOnBoard($player_id, 4 /* purple */);
                        self::executeDraw($player_id, $top_purple_card['age'] + 2); // "Draw a card of value two higher than the top purple card on your board"
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
                        self::setAuxiliaryValue(self::getGameStateValue('age_last_selected')); // Save the age of the returned card
                        self::incrementStepMax(1);
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
                        self::executeDraw($player_id, 4, 'board'); // "Draw and meld a 4"
                    }
                    break;
                
                // id 47, age 5: Coal
                case "47N3A":
                    if ($n > 0) { // "If you do"
                        $card = self::getTopCardOnBoard($player_id, self::getGameStateValue('color_last_selected'));
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
                        if (self::getGameStateValue('game_rules') == 1) { // Last edition
                            $color = self::getGameStateValue('color_last_selected');
                            self::splayRight($player_id, $player_id, $color); // "Splay that color of your cards right"
                            $number_of_cards = self::countCardsInLocationKeyedByColor($player_id, 'board')[$color];
                            if ($number_of_cards == 1) {
                                self::notifyPlayer($player_id, 'log', clienttranslate('${You} have ${n} ${colored} card.'), array('i18n' => array('n', 'colored'), 'You' => 'You', 'n' => self::getTranslatedNumber($number_of_cards), 'colored' => self::getColorInClear($color)));
                                self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} has  ${n} ${colored} card.'), array('i18n' => array('n', 'colored'), 'player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id), 'n' => self::getTranslatedNumber($number_of_cards), 'colored' => self::getColorInClear($color)));
                            } else {
                                self::notifyPlayer($player_id, 'log', clienttranslate('${You} have ${n} ${colored_cards}.'), array('i18n' => array('n', 'colored_cards'), 'You' => 'You', 'n' => self::getTranslatedNumber($number_of_cards), 'colored_cards' => self::getColorInClearWithCards($color)));
                                self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} has  ${n} ${colored_cards}.'), array('i18n' => array('n', 'colored_cards'), 'player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id), 'n' => self::getTranslatedNumber($number_of_cards), 'colored_cards' => self::getColorInClearWithCards($color)));
                            }
                            self::executeDraw($player_id, $number_of_cards); // "Draw a card of value equal to the number of cards of that color on your board"
                        }
                        else { // First edition => color is chosen by the player
                            self::incrementStepMax(1);
                        }
                    }
                    break;
                    
                // id 51, age 5: Statistics
                case "51D1A":
                    // First edition only
                    if ($n > 0 && self::countCardsInLocation($player_id, 'hand') == 1) { // "If you do, and have only one card in hand afterwards"
                        self::notifyPlayer($player_id, 'log', clienttranslate('${You} have now only one card in your hand.'), array('You' => 'You'));
                        self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} has now only one card in his hand.'), array('player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id)));
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
                        $color = self::getGameStateValue('color_last_selected');
                        self::setAuxiliaryValue($color); // Save the color of the revealed card
                        $revealed_card = self::getCardInfo(self::getGameStateValue('id_last_selected'));
                        
                        $players = self::loadPlayersBasicInfos();
                        foreach($players as $other_player_id => $player) {
                            if ($other_player_id == $player_id || $other_player_id == self::getPlayerTeammate($player_id)) { // Ignore the player and his potential teammate
                                continue;
                            }
                            $id_of_cards_in_hand = self::getIdsOfCardsInLocation($other_player_id, 'hand');
                            $no_transfer = true;
                            foreach($id_of_cards_in_hand as $id) {
                                $card = self::getCardInfo($id);
                                if ($card['color'] == $color) { // This card must be given to the player
                                    self::transferCardFromTo($card, $player_id, 'hand'); // "Take into your hand all cards of that color from all other player's hands"
                                    $no_transfer = false;
                                }
                            }
                            if ($no_transfer) { // The player had no card of this color in his hand
                                $color_in_clear = self::getColorInClear($color);
                                self::notifyPlayer($other_player_id, 'log', clienttranslate('${You} have no ${colored} card in your hand.'), array('i18n' => array('colored'), 'You' => 'You', 'colored' => $color_in_clear));
                                self::notifyAllPlayersBut($other_player_id, 'log', clienttranslate('${player_name} has no ${colored} card in his hand.'), array('i18n' => array('colored'), 'player_name' => self::getColoredText(self::getPlayerNameFromId($other_player_id), $other_player_id), 'colored' => $color_in_clear));
                            }
                        }
                        self::transferCardFromTo($revealed_card, $player_id, 'hand'); // Place back the card into player's hand
                        self::incrementStepMax(1);
                    }
                    break;
                    
                // id 62, age 6: Vaccination
                case "62D1A":
                    if ($n > 0) { // "If you returned any"
                        self::executeDraw($player_id, 6, 'board'); // "Draw and meld a 6"
                        self::setAuxiliaryValue(1); // Flag that a card has been returned
                    }
                    break;
                    
                // id 63, age 6: Democracy
                case "63N1A":
                    if ($n > self::getAuxiliaryValue()) { // "If you returned more than any other player due to Democracy so far during this dogma action"
                        self::executeDraw($player_id, 8, 'score'); // "Draw and score a 8"
                        self::setAuxiliaryValue($n); // Set the new maximum
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
                    if ($n > 0) {
                        self::setAuxiliaryValue(1);  // Flag that at least one card has been transfered
                    }
                    break;
                    
                case "68D1C":
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
                    
                // id 72, age 7: Sanitation
                case "72D1C":
                    // Finish the exchange
                    $this->gamestate->changeActivePlayer($launcher_id); // This exchange was initiated by $launcher_id
                    $id = self::getAuxiliaryValue();
                    if ($id != -1) { // The attacking player could indeed choose a card
                        $card = self::getCardInfo($id);
                        self::transferCardFromTo($card, $player_id, 'hand'); // $launcher_id -> $player_id
                        self::setAuxiliaryValue(-1);
                    }
                    break;
                    
                // id 73, age 7: Lighting
                case "73N1A":
                    if ($n > 0) { // "If you do"
                        $different_values_selected_so_far = self::getAuxiliaryValueAsArray();
                        $number_of_cards_to_score = count($different_values_selected_so_far);
                        
                        if ($number_of_cards_to_score == 1) {
                            self::notifyPlayer($player_id, 'log', clienttranslate('Each card ${you} tucked has the same value.'), array('you' => 'you'));
                            self::notifyAllPlayersBut($player_id, 'log', clienttranslate('Each card ${player_name} tucked has the same value.'), array('player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id)));
                        }
                        else if ($number_of_cards_to_score > 1) {
                            $n = self::getTranslatedNumber($number_of_cards_to_score);
                            self::notifyPlayer($player_id, 'log', clienttranslate('${You} tucked cards of ${n} different values.'), array('i18n' => array('n'), 'You' => 'You', 'n' => $n));
                            self::notifyAllPlayersBut($player_id, 'log', clienttranslate('There are ${n} different values that can be found in the cards ${player_name} tucked.'), array('i18n' => array('n'), 'player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id), 'n' => $n));
                        }
                        
                        // "For every different value of card you tucked"
                        for($i=0; $i<$number_of_cards_to_score; $i++) {
                            self::executeDraw($player_id, 7, 'score'); // "Draw and score a 7"
                        }
                    }
                    break;
                
                // id 74, age 7: Railroad        
                case "74N1A":
                    self::executeDraw($player_id, 6); // Draw three 6
                    self::executeDraw($player_id, 6); //
                    self::executeDraw($player_id, 6); //
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
                        $color = self::getGameStateValue('color_last_selected');
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
                        self::executeDraw($player_id, 8, 'board'); // "Draw and meld a 8"
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
                            self::notifyAllPlayersBut($player_id, 'log', clienttranslate('Each card ${player_name} returned has the same value.'), array('player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id)));
                        }
                        else if ($number_of_cards_to_draw > 1) {
                            $n = self::getTranslatedNumber($number_of_cards_to_draw);
                            self::notifyPlayer($player_id, 'log', clienttranslate('${You} returned cards of ${n} different values.'), array('i18n' => array('n'), 'You' => 'You', 'n' => $n));
                            self::notifyAllPlayersBut($player_id, 'log', clienttranslate('There are ${n} different values that can be found in the cards ${player_name} returned.'), array('i18n' => array('n'), 'player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id), 'n' => $n));
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
                        $color = self::getGameStateValue('color_last_selected');
                        $card = self::getTopCardOnBoard($player_id, $color); // The card now on top of the pile
                        if ($card !== null) {
                            self::scoreCard($card, $player_id); // "Score the card beneath it"
                        }
                        self::setAuxiliaryValue($color);// Flag the chosen color for the next interaction
                        self::incrementStepMax(1);
                    }
                    break;
                
                // id 84, age 8: Socialism     
                case "84N1A":
                    if ($n > 0) {
                        if (self::getAuxiliaryValue() == 1) { // "If you tucked at least one purple card"
                            self::notifyGeneralInfo(clienttranslate('At least one purple card has been tucked.'));
                            
                            $players = self::loadPlayersBasicInfos();
                            foreach($players as $other_player_id => $player) {
                                if ($other_player_id == $player_id || $other_player_id == self::getPlayerTeammate($player_id)) {
                                    continue; // Skip the active player and his teammate
                                }
                                $ids_of_lowest_cards_in_hand = self::getIdsOfLowestCardsInLocation($other_player_id, 'hand');
                                foreach($ids_of_lowest_cards_in_hand as $card_id) {
                                    $card = self::getCardInfo($card_id);
                                    self::transferCardFromTo($card, $player_id, 'hand'); // "Take all the lowest cards in each other player's hand to your hand" 
                                }
                            }
                        }
                        else {
                            self::notifyGeneralInfo(clienttranslate('No purple card has been tucked.'));
                        }
                    }
                    break;
                
                // id 88, age 9: Fission
                case "88N1A":
                    if (self::getGameStateValue('game_rules') == 1) {
                        self::executeDraw($player_id, 10); // "Draw a 10"
                    }
                    break;
                
                // id 89, age 9: Collaboration
                case "89D1A":
                    $remaining_revealed_card = self::getCardsInLocation($player_id, 'revealed')[0]; // There is one card left revealed
                    self::transferCardFromTo($remaining_revealed_card, $player_id, 'board'); // "Meld the other one"
                    break;
                    
                // id 90, age 9: Satellites
                case "90N1A":
                    self::executeDraw($player_id, 8); // "Draw three 8"
                    self::executeDraw($player_id, 8); //
                    self::executeDraw($player_id, 8); //
                    break;
                    
                case "90N3A":
                    if ($n > 0) {
                        $card = self::getCardInfo(self::getGameStateValue('id_last_selected')); // The card the player melded from his hand
                        self::executeNonDemandEffects($card); // "Execute each of its non-demand dogma effects"
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
                        $color = self::getGameStateValue('color_last_selected');
                        $revealed_card = self::getCardInfo(self::getGameStateValue('id_last_selected'));
                        
                        $players = self::loadPlayersBasicInfos();
                        foreach($players as $other_player_id => $player) { // "From all other players' boards"
                            if ($other_player_id == $player_id || $other_player_id == self::getPlayerTeammate($player_id)) { // Ignore the player and his potential teammate
                                continue;
                            }
                            $top_card = self::getTopCardOnBoard($other_player_id, $color);
                            if ($top_card !== null) { // If the opponent has indeed a top card of that color on his board
                                self::transferCardFromTo($top_card, $player_id, 'hand'); // "Take into your hand the top card of that color"
                            }
                        }
                        self::transferCardFromTo($revealed_card, $player_id, 'hand'); // Place back the card into player's hand
                    }
                    break;
                    
                // id 97, age 10: Miniaturization
                case "97N1A":
                    $age_last_selected = self::getGameStateValue('age_last_selected') == 10;
                    if ($n > 0 && $age_last_selected == 10) { // "If you returned a 10"
                        $number_of_cards_in_score = self::countCardsInLocationKeyedByAge($player_id, 'score');
                        $number_of_different_value = 0;
                        for($age=1; $age<=10; $age++) {
                            if ($number_of_cards_in_score[$age] > 0) {
                                $number_of_different_value++;
                            }
                        }
                        
                        if ($number_of_different_value == 0) {
                            self::notifyPlayer($player_id, 'log', clienttranslate('${You} have no card in your score pile.'), array('You' => 'You'));
                            self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name}\'s has no card in his score pile.'), array('player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id)));
                        }
                        else if ($number_of_different_value == 1) {
                            self::notifyPlayer($player_id, 'log', clienttranslate('Each card in ${your} score pile has the same value.'), array('your' => 'your'));
                            self::notifyAllPlayersBut($player_id, 'log', clienttranslate('Each card ${player_name}\'s score pile has the same value.'), array('player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id)));
                        }
                        else if ($number_of_different_value > 1) {
                            $n = self::getTranslatedNumber($number_of_different_value);
                            self::notifyPlayer($player_id, 'log', clienttranslate('There are ${n} different values that can be found in ${your} score pile.'), array('i18n' => array('n'), 'your' => 'your', 'n' => $n));
                            self::notifyAllPlayersBut($player_id, 'log', clienttranslate('There are ${n} different values that can be found in ${player_name}\'s score pile.'), array('i18n' => array('n'), 'player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id), 'n' => $n));
                        }
                        
                        for($i=0; $i<$number_of_different_value; $i++) { // "For every different value of card in your score pile"
                            self::executeDraw($player_id, 10); // "Draw a 10"                    
                        }
                    }
                    else if ($n > 0 && $age_last_selected < 10) {
                        self::notifyGeneralInfo(clienttranslate('The returned card is not of value ${age}'), array('age' => self::getAgeSquare(10)));
                    }
                    break;
                    
                // id 114, Artifacts age 1: Papyrus of Ani
                case "114N1A":
                    // "If you do"
                    if ($n > 0) {
                        self::incrementStepMax(1);
                    }
                    break;
                
                // id 116, Artifacts age 1: Priest-King
                case "116N1A":
                    // "If you do"
                    if ($n > 0) {
                        $color_scored = self::getGameStateValue('color_last_selected');
                        $top_card = self::getTopCardOnBoard($player_id, $color_scored);
                        if ($top_card !== null) { // "If you have a top card matching its color"
                            self::executeNonDemandEffects($top_card); // "Execute each of the top card's non-demand dogma effects. Do not share them."
                            break;
                        }
                    }
                    break;

                // id 118, Artifacts age 1: Jiskairumoko Necklace
                case "118C1A":
                    // "If you do"
                    if ($n > 0) {
                        self::incrementStepMax(1);
                    }
                    break;

                // id 120, Artifacts age 1: Lurgan Canoe
                case "120N1A":
                    if ($n > 0) {
                        $board = self::getCardsInLocationKeyedByColor($player_id, 'board');
                        $pile = $board[self::getGameStateValue('color_last_selected')];
                        $scored = false;
                        for ($i = 0; $i < count($pile) - 1; $i++) { // "Score all other cards of the same color from your board"
                            $card = self::getCardInfo($pile[$i]['id']);
                            self::scoreCard($card, $player_id);
                            $scored = true;
                        }
                        if ($scored) { // "If you scored at least one card, repeat this effect"
                            $step--; self::incrementStep(-1);
                        }
                    }
                    break;
                
                // id 121, Artifacts age 1: Xianrendong Shards
                case "121N1A":
                    // Store IDs of revealed cards so that we are later able to see if the scored cards had the same color.
                    $revealed_cards = self::getCardsInLocation($player_id, 'revealed');
                    for ($i = 1; $i <= count($revealed_cards); $i++) {
                        self::setGameStateValue('card_id_'.$i, $revealed_cards[$i-1]['id']);
                    }
                    break;

                case "121N1B":
                    $revealed_card_ids = array(self::getGameStateValue('card_id_1'), self::getGameStateValue('card_id_2'), self::getGameStateValue('card_id_3'));

                    // "Tuck the other"
                    $remaining_revealed_cards = self::getCardsInLocation($player_id, 'revealed');
                    if (count($remaining_revealed_cards) == 1) {
                        $remaining_card = $remaining_revealed_cards[0];
                        self::tuckCard($remaining_card, $player_id);
                        for ($i = 0; $i < 3; $i++) {
                            if ($revealed_card_ids[$i] == $remaining_card['id']) {
                                $revealed_card_ids[$i] = -1;
                            }
                        }
                    }

                    // Now revealed_card_ids contains only -1's and the IDs of the scored cards.
                    $first_color = null;
                    foreach ($revealed_card_ids as $card_id) {
                        if ($card_id < 0) {
                            continue;
                        }
                        $current_card = self::getCardInfo($card_id);
                        if ($first_color == null) {
                            $first_color = $current_card['color'];
                        // "If the scored cards were the same color, draw three 1s"
                        } else if ($first_color == $current_card['color']) {
                            self::notifyGeneralInfo(clienttranslate('The scored cards were the same color.'), array());
                            self::executeDraw($player_id, 1);
                            self::executeDraw($player_id, 1);
                            self::executeDraw($player_id, 1);
                            break;
                        } else {
                            self::notifyGeneralInfo(clienttranslate('The scored cards were not the same color.'), array());
                            break;
                        }
                    }
                    break;

                // id 122, Artifacts age 1: Mask of Warka
                case "122N1B":
                    // "Claim all achievements of value matching those [returned] cards, ignoring eligibility"
                    $achievements_by_age = self::getCardsInLocationKeyedByAge(0, "achievements");
                    $different_values_selected_so_far = self::getAuxiliaryValue2AsArray();
                    foreach ($different_values_selected_so_far as $returned_age) {
                        foreach ($achievements_by_age[$returned_age] as $achievement) {
                            $achievement = self::getCardInfo($achievement['id']); // refresh card info
                            self::transferCardFromTo($achievement, $player_id, 'achievements');
                        }
                    }
                    break;
                
                // id 123, Artifacts age 1: Ark of the Covenant
                case "123N1A":
                    $player_ids = self::getActivePlayerIdsInTurnOrderStartingWithCurrentPlayer();
                    
                    if ($n > 0) { // Unsaid rule: the player must have returned a card or else this part of the effect can't continue
                        $returned_color = self::getGameStateValue('color_last_selected');
                            
                        foreach ($player_ids as $id) {
                            $top_cards = self::getTopCardsOnBoard($id);
                            
                            $artifact_found = false;
                            foreach ($top_cards as &$card) {
                                if ($card['type'] == 1) { // Artifact
                                    $artifact_found = true;
                                    break;
                                }
                            }
                            
                            // TODO(ARTIFACTS): Add logging here for both cases (artifact is found and artifact is not found).
                            if (!$artifact_found) {
                                while (($top_card = self::getTopCardOnBoard($id, $returned_color)) !== null) {   
                                    // "Transfer all cards of the same color from the boards of all players with no top artifacts to your score pile."
                                    self::transferCardFromTo($top_card, $player_id, 'score');
                                }
                            }
                            
                        }
                    }

                    // "If Ark of the Covenant is a top card on any board, transfer it to your hand"
                    $ark_of_the_covenant = self::getIfTopCardOnBoard(123);
                    if ($ark_of_the_covenant !== null) {
                        self::transferCardFromTo($ark_of_the_covenant, $player_id, 'hand');
                    }
                    break;
                
                // id 124, Artifacts age 1: Tale of the Shipwrecked Sailor
                case "124N1A":
                    // "Draw a 1"
                    self::executeDraw($player_id, 1);
                    break;

                case "124N1B":
                    // "If you (melded a card)"
                    if ($n > 0) {
                        // "Splay that color left"
                        self::splayLeft($player_id, $player_id, self::getGameStateValue('color_last_selected')); 
                    }
                    break;

                // id 131, Artifacts age 2: Holy Grail
                case "131N1A":
                    // if you do
                    if ($n > 0) {
                        self::incrementStepMax(1);
                    }
                    break;
                
                // id 134, Artifacts age 2: Cyrus Cylinder
                case "134N1A":
                    // "Execute its non-demand dogma effects"
                    if ($n > 0) {
                        self::executeNonDemandEffects(self::getCardInfo(self::getGameStateValue('id_last_selected')));
                    }
                    else {
                        self::incrementStepMax(1); // still need to do the splay
                    }
                    break;

                case "134N1B":
                case "134N1+A":
                    // "Splay left a color on any player's board"
                    if ($n > 0) {
                        $color = self::getGameStateValue('color_last_selected');
                        $target_player_id = self::getGameStateValue('owner_last_selected');
                        self::splayLeft($player_id, $target_player_id, $color);
                    }
                    break;
                
                // id 135, Artifacts age 3: Dunhuang Star Chart
                case "135N1A":
                    // "Draw a card of value equal to the number of cards returned"
                    // TODO(ARTIFACTS): See if we can use $n instead of setting the auxiliary value.
                    self::executeDraw($player_id, self::getAuxiliaryValue());
                	break;

                // id 136, Artifacts age 3: Charter of Liberties
                case "136N1A":
                    if ($n > 0) {
                        // "If you do, splay left its color"
                        self::splayLeft($player_id, $player_id, self::getGameStateValue('color_last_selected'));
                        self::incrementStepMax(1);
                    }
                    break;

                case "136N1B":
                    if ($n > 0) {
                        // "Execute all of that color's top card's non-demand effects, without sharing"
                        self::executeNonDemandEffects(self::getCardInfo(self::getGameStateValue('id_last_selected')));
                    }
                    break;
                    
                // id 138, Artifacts age 3: Mjolnir Amulet
                case "138C1A":
                    if ($n > 0) {
                        // "Transfer all cards of that card's color from your board to my score pile"
                        $board = self::getCardsInLocationKeyedByColor($player_id, 'board');
                        $pile = $board[self::getGameStateValue('color_last_selected')];
                        for ($i = count($pile) - 1; $i >= 0; $i--) {
                            self::transferCardFromTo($pile[$i], $launcher_id, 'score', /*bottom_to=*/ false, /*score_keyword=*/ false); 
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
                            'color' => self::getColorInClear(self::getGameStateValue('color_last_selected'))
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
                    if ($n > 0 && self::getAuxiliaryValue() == 2) {
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
                        self::setGameStateValue('card_id_1', $card_1['id']);
                        $card_2 = self::executeDraw($player_id, 6);
                        self::setGameStateValue('card_id_2', $card_2['id']);

                        // Check if any icons on the returned card match one of the drawn cards
                        $returned_card = self::getCardInfo(self::getGameStateValue('id_last_selected'));
                        $matching_icon_on_card_1 = false;
                        $matching_icon_on_card_2 = false;
                        for ($icon = 1; $icon <= 6; $icon++) { 
                            $has_icon = self::hasRessource($returned_card, $icon);
                            if ($has_icon && self::hasRessource($card_1, $icon)) {
                                $matching_icon_on_card_1 = true;
                            }
                            if ($has_icon && self::hasRessource($card_2, $icon)) {
                                $matching_icon_on_card_2 = true;
                            }
                        }
                        
                        if (!$matching_icon_on_card_1 && !$matching_icon_on_card_2) {
                            // "If you cannot"
                            self::setStepMax(2);
                        } else {
                            // Skip to the third interaction
                            $step++;
                            self::incrementStep(1);
                            self::setStepMax(3);

                            // Remove card as an option if it does not have any matching symbols
                            if (!$matching_icon_on_card_1) {
                                self::setGameStateValue('card_id_1', -1);
                            } else if (!$matching_icon_on_card_2) {
                                self::setGameStateValue('card_id_2', -1);
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
                    self::transferCardFromTo(self::getCardInfo(self::getGameStateValue('id_last_selected')), $player_id, 'hand');
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
                        $value_to_match = self::getGameStateValue('age_last_selected');
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
                            $card = self::getCardInfo(self::getGameStateValue('id_last_selected'));
                            self::transferCardFromTo($card, $player_id, 'hand');
                        }
                    }
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
                    self::transferCardFromTo($bottom_card, $player_id, 'board');
                    break;
                    
                // id 152, Artifacts age 4: Mona Lisa
                case "152N1B":
                    // "Draw five 4s, then reveal your hand"
                    for ($i = 0; $i < 5; $i++) {
                        self::executeDraw($player_id, 4, 'hand');
                    }
                    self::revealHand($player_id);
                    
                    $chosen_color = self::getAuxiliaryValue2();
                    $cards = self::getCardsInLocationKeyedByColor($player_id, 'hand');
                    $colored_cards = $cards[$chosen_color];
                    $num_cards = count($colored_cards);
                    self::notifyPlayer($player_id, 'log', clienttranslate('${You} revealed ${n} ${color} cards.'), array('i18n' => array('color'), 'You' => 'You', 'n' => $num_cards, 'color' => self::getColorInClear($chosen_color)));
                    self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} revealed ${n} ${color} cards.'), array('i18n' => array('color'), 'player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id), 'n' => $num_cards, 'color' => self::getColorInClear($chosen_color)));
                    
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
                        self::setAuxiliaryValue(self::getGameStateValue('age_last_selected'));
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
                    $ages_on_top = self::getBase16ArrayFromValue(self::getAuxiliaryValue());
                    sort($ages_on_top);
                    // "For each card returned, draw and meld a card of value one higher than the value of the returned card, in ascending order"
                    foreach ($ages_on_top as $card_age) {
                        self::executeDraw($player_id, $card_age + 1, 'board');
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
                        self::splayRight($player_id, $player_id, self::getGameStateValue('color_last_selected'));
                    }
                    break;

                // id 161, Artifacts age 5: Gujin Tushu Jinsheng
                case "161N1A":
                    // "Execute the effects on the chosen card as if they were on this card. Do not share them"
                    if ($n > 0) {
                        self::executeAllEffects(self::getCardInfo(self::getGameStateValue('id_last_selected')));
                    }
                    break;

                // id 162, Artifacts age 5: The Daily Courant
                case "162N1A":
                    // "Draw a card of any value"
                    $card = self::executeDraw($player_id, self::getAuxiliaryValue(), 'hand');
                    self::setGameStateValue('card_id_1', $card['id']);
                    break;

                case "162N1C":
                    // "Execute the effects of one of your other top cards as if they were on this card. Do not share them."
                    if ($n > 0) {
                        self::executeAllEffects(self::getCardInfo(self::getGameStateValue('id_last_selected')));
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
                        self::setAuxiliaryValue(self::getGameStateValue('age_last_selected'));
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
                        $color = self::getGameStateValue('color_last_selected');
                        $max_symbols = 0;
                        for ($icon = 1; $icon <= 6; $icon++) {
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
                        if ($top_green_card !== null) {
                            self::setAuxiliaryValue($top_green_card['age']);
                            self::incrementStepMax(1);
                        }
                    }
                    break;
            
                // id 174, Artifacts age 6: Marcha Real
                case "174N1A":
                    $revealed_cards = self::getCardsInLocation($player_id, 'revealed');
                    $card_1 = count($revealed_cards) >= 1 ? $revealed_cards[0] : null;
                    $card_2 = count($revealed_cards) >= 2 ? $revealed_cards[1] : null;

                    // Return revealed cards from your hand
                    if ($card_1 != null) {
                        self::transferCardFromTo($card_1, 0, 'deck');
                    }
                    if ($card_2 != null) {
                        self::transferCardFromTo($card_2, 0, 'deck');
                    }

                    if ($card_1 != null && $card_2 != null) {
                        // "If they have the same value, draw a card of value one higher"
                        if ($card_1['age'] == $card_2['age']) {
                            self::executeDraw($player_id, $card_1['age'] + 1);
                        }
                        // "If they have the same color, claim an achievement, ignoring eligibility"
                        if ($card_1['color'] == $card_2['color']) {
                            self::incrementStepMax(1);
                        }
                    } else if ($card_1 == null && $card_2 == null) { // If none are returned, they are still considered to have the same value (0)
                        self::executeDraw($player_id, 1);
                    }
                    break;

                // id 175, Artifacts age 7: Periodic Table
                case "175N1A":
                    self::setAuxiliaryValue(self::getGameStateValue('color_last_selected'));
                    break;

                case "175N1B":
                    $color_1 = self::getAuxiliaryValue();
                    $color_2 = self::getGameStateValue('color_last_selected');

                    // "Draw a card of value one higher and meld it"
                    $age_selected = self::getFaceupAgeLastSelected();
                    $card = self::executeDraw($player_id, $age_selected + 1, 'board');
                    
                    // "If it melded over one of the chosen cards, repeat this effect"
                    if ($card['color'] == $color_1 || $card['color'] == $color_2) {
                        // Determine if there are still any top cards which have the same value as another top card on their board
                        $colors = self::getColorsOfRepeatedValueOfTopCardsOnBoard($player_id);
                        if (count($colors) >= 1) {
                            self::setAuxiliaryValueFromArray($colors);
                            $step = $step - 2;
                            self::incrementStep(-2);
                        }
                    }
                    break;

                // id 179, Artifacts age 7: International Prototype Metre Bar   
                case "179N1A":
                    $age_value = self::getAuxiliaryValue();
                    
                    // "Draw and meld a card of that value"
                    $card = self::executeDraw($player_id, $age_value, 'board');

                    // "Splay up the color of the melded card"
                    self::splayUp($player_id, $player_id, $card['color']);
                    
                    // "If the number of cards of that color visible on your board is exactly equal to the card's value, you win"
                    if ($card['faceup_age'] == self::countVisibleCards($player_id, $card['color'])) {
                        self::notifyPlayer($player_id, 'log', clienttranslate('${You} melded a card whose value is equal to the number of visible cards in your ${color} stack.'), array('You' => 'You', 'color'=> self::getColorInClear($card['color'])));
                        self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} melded a card whose value is equal to the number of visible cards in his ${color} stack.'), array('player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id), 'color'=> self::getColorInClear($card['color'])));
                        self::setGameStateValue('winner_by_dogma', $player_id);
                        self::trace('EOG bubbled from self::stInterInteractionStep International Prototype Metre Bar');
                        throw new EndOfGame();
                    
                    // "Otherwise, return the melded card"
                    } else {
                        self::transferCardFromTo($card, 0, 'deck');
                    }
                    break;
                             
                // id 182, Artifacts age 7: Singer Model 27
                case "182N1A":
                    if ($n > 0) { // "If you do"
                        // "Splay up its color"
                        self::splayUp($player_id, $player_id, self::getGameStateValue('color_last_selected'));
                        self::incrementStepMax(1);
                    }
                    break;

                // id 183, Artifacts age 7: Roundhay Garden Scene
                case "183N1A":
                     if ($n > 0) {
                        // "Draw and score two cards of value equal to the melded card"
                        $melded_card = self::getCardInfo(self::getGameStateValue('id_last_selected'));
                        self::executeDraw($player_id, $melded_card['faceup_age'], 'score');
                        self::executeDraw($player_id, $melded_card['faceup_age'], 'score');
                        
                        // "Execute the effects of the melded card as if they were on this card. Do not share them."
                        self::executeAllEffects($melded_card);
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
                        self::setAuxiliaryValue2(self::getGameStateValue('id_last_selected'));
                    }
                    if (count($revealed_cards) > 0) {
                        // Remove the chosen player from the list of options.
                        $selectable_players = self::getGameStateValueAsArray('player_array');
                        $selected_player = self::getAuxiliaryValue();
                        $selectable_players = array_diff($selectable_players, array(self::playerIdToPlayerNo($selected_player)));
                        self::setGameStateValueFromArray('player_array', $selectable_players);
                        
                        // Repeat for next player
                        $step = $step - 2;
                        self::incrementStep(-2);
                    } else {
                        // "Execute the non-demand effects of your card. Do not share them"
                        self::executeNonDemandEffects(self::getCardInfo(self::getAuxiliaryValue2()));
                    }
                    break;
                    
                // id 186, Artifacts age 8: Earhart's Lockheed Electra 10E
                case "186N1A":
                    if ($n > 0) {
                        self::setAuxiliaryValue(self::getAuxiliaryValue() + 1);
                    } else {
                        self::incGameStateValue('age_last_selected', -1);
                    }

                    // There are no more values to return
                    if (self::getGameStateValue('age_last_selected') == 0) {
                        // "If you return eight cards, you win"
                        if (self::getAuxiliaryValue() == 8) {
                            self::notifyPlayer($player_id, 'log', clienttranslate('${You} returned 8 cards.'), array('You' => 'You'));
                            self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} returned 8 cards.'), array('player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id)));
                            self::setGameStateValue('winner_by_dogma', $player_id);
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
                        $age_to_score = self::getGameStateValue('age_last_selected');
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
                        $melded_card = self::getCardInfo(self::getGameStateValue('id_last_selected'));
                        
                        // "If the melded card has no effects, you win"
                        if ($melded_card['type'] == 2 /* a City card */ || $melded_card['id'] == 188 /* Battleship Yamato */) {
                            self::notifyPlayer($player_id, 'log', clienttranslate('${You} melded a card with no effects.'), array('You' => 'You'));
                            self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} melded a card with no effects.'), array('player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id)));
                            self::setGameStateValue('winner_by_dogma', $player_id);
                            self::trace('EOG bubbled from self::stPlayerInvolvedTurn Garlands Ruby Slippers');
                            throw new EndOfGame();
                        } else {
                        	// "Otherwise, execute the effects of the melded card as if they were on this card. Do not share them"
                            self::executeAllEffects($melded_card);
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
                        self::setGameStateValue('winner_by_dogma', $player_id);
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
                        self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} revealed all 5 colors.'), array('player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id)));
                        self::setGameStateValue('winner_by_dogma', $player_id);
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
                        self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} has exactly 25 points.'), array('player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id)));
                        self::setGameStateValue('winner_by_dogma', $player_id);
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
                        self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} has Domestication as a bottom card.'), array('player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id)));
                        self::setGameStateValue('winner_by_dogma', $player_id);
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
                        $selectable_colors = array_diff($selectable_colors, [self::getGameStateValue('color_last_selected')]);

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
                        $card = self::executeDraw($player_id, $n, 'board');
                        
                        // "If the melded card has a clock, return it"
                        if (self::countIconsOnCard($card, 6) > 0) {
                            self::transferCardFromTo($card, $player_id, 'deck');
                        }
                    }
                    break;
                }
                
            //[DD]||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||
            } catch (EndOfGame $e) {
                // End of the game: the exception has reached the highest level of code
                self::trace('EOG bubbled from self::stInterInteractionStep');
                self::trace('interInteractionStep->justBeforeGameEnd');
                $this->gamestate->nextState('justBeforeGameEnd');
                return;
            }
        }

        $step_max = self::getStepMax();
        if ($step == $step_max) { // The last step has been completed
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
        if (self::getGameStateValue('special_type_of_choice') == 0) {
            $selection_size = self::countSelectedCards();
            $cards_chosen_so_far = self::getGameStateValue('n');
            $n_min = self::getGameStateValue('n_min');
            $n_max = self::getGameStateValue('n_max');
            $splay_direction = self::getGameStateValue('splay_direction');
            $can_pass = self::getGameStateValue('can_pass') == 1;
            $enable_autoselection = self::getGameStateValue('enable_autoselection') == 1;
            $owner_from = self::getGameStateValue('owner_from');
            $location_from = self::decodeLocation(self::getGameStateValue('location_from'));
            $colors = self::getGameStateValueAsArray('color_array');
            $with_icon = self::getGameStateValue('with_icon');
            $without_icon = self::getGameStateValue('without_icon');

            // TODO(ARTIFACTS,ECHOES,FIGURES): Figure out if we need to make any updates to this logic.
            $selection_will_reveal_hidden_information =
                ($splay_direction == -1 && ($can_pass || $n_min <= 0)) &&
                ($location_from == 'hand' || $location_from == 'score') &&
                self::countCardsInLocation($owner_from, $location_from) > 0 &&
                ($colors != array(0, 1, 2, 3, 4) || $with_icon > 0 || $without_icon > 0);
            
            // There is no selectable card
            if ($selection_size == 0) {
                
                if ($selection_will_reveal_hidden_information) {
                    // The player can pass or stop and the opponents can't know that the player has no eligible card
                    // This can happen for example in the Masonry effect
                    
                    // No automatic pass or stop: the only choice the player will have in client side is to pass/stop
                    // This way the other players won't get the information that the player was compeled to pass/stop
                    self::trace('preSelectionMove->selectionMove (player has to pass)');
                    $this->gamestate->nextState('selectionMove');
                    return;
                }
                
                // The player passes or stops automatically
                self::notifyNoSelectableCards();
                self::trace('preSelectionMove->interInteractionStep (no card)');
                $this->gamestate->nextState('interInteractionStep');
                return;

            // There is only one selectable card (and it must be chosen)
            } else if ($selection_size == 1
                    && $enable_autoselection
                    && !$selection_will_reveal_hidden_information
                    && (($cards_chosen_so_far == 0 && !$can_pass) || ($cards_chosen_so_far > 0 && $n_min >= 1))) {
                // The player chooses the card automatically
                $card = self::getSelectedCards()[0];
                // Simplified version of self::choose()
                self::setGameStateValue('id_last_selected', $card['id']);
                self::unmarkAsSelected($card['id']);
                self::setGameStateValue('can_pass', 0);

                self::trace('preSelectionMove->interSelectionMove (only one card)');
                $this->gamestate->nextState('interSelectionMove');
                return;
            
            // There are selectable cards, but not enough to fulfill the requirement ("May effects only")
            } else if ($n_min < 800 && $selection_size < $n_min) {
                if (self::getGameStateValue('solid_constraint') == 1) {
                    self::notifyGeneralInfo(clienttranslate("There are not enough cards to fulfill the condition."));
                    self::deselectAllCards();
                    self::trace('preSelectionMove->interInteractionStep (not enough cards)');
                    $this->gamestate->nextState('interInteractionStep');
                    return;
                } else {
                    // Reduce n_min and n_max to the selection size
                    self::setGameStateValue('n_min', $selection_size);
                    self::setGameStateValue('n_max', $selection_size);
                }

            // Reduce n_max to the selection size
            } else if ($n_max < 800 && $selection_size < $n_max) {
                self::setGameStateValue('n_max', $selection_size);
            }
        }
        // Let the player make his choice
        self::trace('preSelectionMove->selectionMove');
        $this->gamestate->nextState('selectionMove');
    }
    
    function stInterSelectionMove() {
        $player_id = self::getCurrentPlayerUnderDogmaEffect();
        $special_type_of_choice = self::getGameStateValue('special_type_of_choice');
        if ($special_type_of_choice == 0) { // The player had to choose a card
            $selected_card_id = self::getGameStateValue('id_last_selected');
            if ($selected_card_id == -1) {
                // The player passed or stopped
                if (self::getGameStateValue('can_pass') == 1) {
                    self::notifyPass($player_id);
                }
                // Unset the selection
                self::deselectAllCards();
                self::trace('interSelectionMove->interInteractionStep');
                $this->gamestate->nextState('interInteractionStep');
                return;
            }
            
            // The player has chosen one card
            $card = self::getCardInfo($selected_card_id);
            
            // Flags
            $owner_to = self::getGameStateValue('owner_to');
            $location_to = self::decodeLocation(self::getGameStateValue('location_to'));
            $bottom_to = self::getGameStateValue('bottom_to');
            $score_keyword = self::getGameStateValue('score_keyword') == 1;
            
            $splay_direction = self::getGameStateValue('splay_direction'); // -1 if that was not a choice for splay
        }
        else { // The player had to make a special choice
            $choice = self::getGameStateValue('choice');
            if ($choice == -2) {
                // The player passed
                self::notifyPass($player_id);
                self::trace('interSelectionMove->interInteractionStep');
                $this->gamestate->nextState('interInteractionStep');
                return;
            }
        }
        
        $launcher_id = self::getLauncherId();
        if (self::getGameStateValue('release_version') >= 1) {
            $nested_card_state = self::getCurrentNestedCardState();
            $card_id = $nested_card_state['card_id'];
            $current_effect_type = $nested_card_state['current_effect_type'];
            $current_effect_number = $nested_card_state['current_effect_number'];
        } else {
            $nested_id_1 = self::getGameStateValue('nested_id_1');
            $card_id = $nested_id_1 == -1 ? self::getGameStateValue('dogma_card_id') : $nested_id_1 ;
            $current_effect_type = $nested_id_1 == -1 ? self::getGameStateValue('current_effect_type') : 1 /* Non-demand effects only*/;
            $current_effect_number = $nested_id_1 == -1 ?  self::getGameStateValue('current_effect_number') : self::getGameStateValue('nested_current_effect_number_1');
        }
        $step = self::getStep();
        
        $crown = self::getIconSquare(1);
        $leaf = self::getIconSquare(2);
        $lightbulb = self::getIconSquare(3);
        $tower = self::getIconSquare(4);
        $factory = self::getIconSquare(5);
        $clock = self::getIconSquare(6);
        
        try {
            //||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||
            // [C] SPECIFIC CODE: what is to be done with the card/color/value the player chose?
            $letters = array(1 => 'A', 2 => 'B', 3 => 'C', 4 => 'D');
            $code = self::getCardExecutionCodeWithLetter($card_id, $current_effect_type, $current_effect_number, $step);
            self::trace('[C]'.$code.' '.self::getPlayerNameFromId($player_id).'('.$player_id.')'.' | '.self::getPlayerNameFromId($launcher_id).'('.$launcher_id.')');
            switch($code) {
            // The first number is the id of the card
            // D1 means the first (and single) I demand effect
            // N1 means the first non-demand effect
            // N2 means the second non-demand effect
            // N3 means the third non-demand effect
            
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
                    self::notifyAllPlayersBut($choice, 'log', clienttranslate('${player_name} has no top green card on his board.'), array('player_name' => self::getColoredText(self::getPlayerNameFromId($choice), $choice)));
                }
                break;
                
            // id 19, age 2: Currency
            case "19N1A":
                $different_values_selected_so_far = self::getAuxiliaryValueAsArray();
                if (!in_array($card['age'], $different_values_selected_so_far)) { // The player choose to return a card of a new value
                    $different_values_selected_so_far[] = $card['age'];
                    self::setAuxiliaryValueFromArray($different_values_selected_so_far);
                }
                // Do the transfer as stated in B (return)
                self::transferCardFromTo($card, $owner_to, $location_to, $bottom_to, $score_keyword);
                break;
            
            // id 21, age 2: Canal building         
            case "21N1A":
                // $choice is yes or no
                if ($choice == 0) { // No exchange
                    self::notifyPlayer($player_id, 'log', clienttranslate('${You} decide not to exchange.'), array('You' => 'You'));
                    self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} decides not to exchange.'), array('player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id)));
                }
                else { // "Exchange all the highest cards in your hand with all the highest cards in your score pile"
                    self::notifyPlayer($player_id, 'log', clienttranslate('${You} decide to exchange.'), array('You' => 'You'));
                    self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} decides to exchange.'), array('player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id)));
                    
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
                self::notifyPlayer($player_id, 'log', clienttranslate('${You} choose ${color}.'), array('i18n' => array('color'), 'You' => 'You', 'color' => self::getColorInClear($choice)));
                self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} chooses ${color}.'), array('i18n' => array('color'), 'player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id), 'color' => self::getColorInClear($choice)));
                self::splayRight($player_id, $player_id, $choice); // "Splay that color of your cards right"
                $number_of_cards = self::countCardsInLocationKeyedByColor($player_id, 'board')[$choice];
                if ($number_of_cards == 1) {
                    self::notifyPlayer($player_id, 'log', clienttranslate('${You} have ${n} ${colored} card.'), array('i18n' => array('n', 'colored'), 'You' => 'You', 'n' => self::getTranslatedNumber($number_of_cards), 'colored' => self::getColorInClear($choice)));
                    self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} has  ${n} ${colored} card.'), array('i18n' => array('n', 'colored'), 'player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id), 'n' => self::getTranslatedNumber($number_of_cards), 'colored' => self::getColorInClear($choice)));
                } else {
                    self::notifyPlayer($player_id, 'log', clienttranslate('${You} have ${n} ${colored_cards}.'), array('i18n' => array('n', 'colored_cards'), 'You' => 'You', 'n' => self::getTranslatedNumber($number_of_cards), 'colored_cards' => self::getColorInClearWithCards($choice)));
                    self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} has  ${n} ${colored_cards}.'), array('i18n' => array('n', 'colored_cards'), 'player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id), 'n' => self::getTranslatedNumber($number_of_cards), 'colored_cards' => self::getColorInClearWithCards($choice)));
                }
                self::executeDraw($player_id, $number_of_cards); // "Draw a card of value equal to the number of cards of that color on your board"
                break;
                
            // id 61, age 6: Canning
            case "61N1A":
                // $choice is yes or no
                if ($choice == 0) { // No tuck
                    self::notifyPlayer($player_id, 'log', clienttranslate('${You} decide not to tuck.'), array('You' => 'You'));
                    self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} decides not to tuck.'), array('player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id)));
                } else { // Draw and tuck
                    self::notifyPlayer($player_id, 'log', clienttranslate('${You} decide to tuck.'), array('You' => 'You'));
                    self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} decides to tuck.'), array('player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id)));
                    
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
            
            // id 65, age 7: Evolution          
            case "65N1A":
                if ($choice == 0) { // Draw
                    self::executeDraw($player_id, self::getMaxAgeInScore($player_id) + 1); // "Draw a card of one value higher than the highest card in your score pile"
                }
                else { // Draw and score, then return
                    self::executeDraw($player_id, 8, 'score'); // "Draw and score a 8"
                    self::incrementStepMax(1);
                }
                break;
                
            // id 66, age 7: Publications          
            case "66N1A":
                // All was done before
                break;
            
            // id 67, age 7: Bicycle         
            case "69N1A":
                // $choice is yes or no
                if ($choice == 0) { // No exchange
                    self::notifyPlayer($player_id, 'log', clienttranslate('${You} decide not to exchange.'), array('You' => 'You'));
                    self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} decides not to exchange.'), array('player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id)));
                }
                else { // "Exchange all the cards in your hand with all the cards in your score pile"
                    self::notifyPlayer($player_id, 'log', clienttranslate('${You} decide to exchange.'), array('You' => 'You'));
                    self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} decides to exchange.'), array('player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id)));
                    
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
                
            // id 72, age 7: Sanitation
            case "72D1A":
                // Delay the transfer: this way the player can not choose the card he would just receive
                self::setAuxiliaryValue($card['id']);
                break;
            
            // id 73, age 7: Lighting
            case "73N1A":
                $different_values_selected_so_far = self::getAuxiliaryValueAsArray();
                if (!in_array($card['age'], $different_values_selected_so_far)) { // The player choose to tuck a card of a new value
                    $different_values_selected_so_far[] = $card['age'];
                    self::setAuxiliaryValueFromArray($different_values_selected_so_far);
                }
                // Do the transfer as stated in B (tuck)
                self::transferCardFromTo($card, $owner_to, $location_to, $bottom_to, $score_keyword);
                break;
            
            // id 80, age 8, Mass media
            case "80N1B":
                self::notifyPlayer($player_id, 'log', clienttranslate('${You} choose the value ${age}.'), array('You' => 'You', 'age' => self::getAgeSquare($choice)));
                self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} chooses the value ${age}.'), array('player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id), 'age' => self::getAgeSquare($choice)));
                self::setAuxiliaryValue($choice);
                break;
                
            // id 81, age 8: Antibiotics
            case "81N1A":
                $different_values_selected_so_far = self::getAuxiliaryValueAsArray();
                if (!in_array($card['age'], $different_values_selected_so_far)) { // The player choose to return a card of a new value
                    $different_values_selected_so_far[] = $card['age'];
                    self::setAuxiliaryValueFromArray($different_values_selected_so_far);
                }
                // Do the transfer as stated in B (tuck)
                self::transferCardFromTo($card, $owner_to, $location_to, $bottom_to, $score_keyword);
                break;
            
            // id 83, age 8: Empiricism     
            case "83N1A":
                // $choice was two colors
                $colors = self::getValueAsArray($choice);
                self::notifyPlayer($player_id, 'log', clienttranslate('${You} choose ${color_1} and ${color_2}.'), array('i18n' => array('color_1', 'color_2'), 'You' => 'You', 'color_1' => self::getColorInClear($colors[0]), 'color_2' => self::getColorInClear($colors[1])));
                self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} chooses ${color_1} and ${color_2}.'), array('i18n' => array('color_1', 'color_2'), 'player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id), 'color_1' => self::getColorInClear($colors[0]), 'color_2' => self::getColorInClear($colors[1])));
                
                $card = self::executeDraw($player_id, 9, 'revealed'); // "Draw and reveal a 9"
                if ($card['color'] <> $colors[0] && $card['color'] <> $colors[1]) {
                    self::notifyGeneralInfo(clienttranslate('It does not match any of the chosen colors.'));
                    self::transferCardFromTo($card, $player_id, 'hand'); // (Implicit) "Keep it in your hand"
                }
                else { // "If it is either of the colors you chose"
                    self::notifyGeneralInfo(clienttranslate('It matches a chosen color: ${color}.'), array('i18n' => array('color'), 'color' => self::getColorInClear($card['color'])));
                    self::transferCardFromTo($card, $player_id, 'board'); // "Meld it"
                    self::setAuxiliaryValue($card['color']); // Flag the sucessful colors
                    self::incrementStepMax(1);
                }
                break;
            
            // id 84, age 8: Socialism     
            case "84N1A":
                if ($card['color'] == 4 /* purple*/) { // A purple card has been tucked
                    self::setAuxiliaryValue(1); // Flag that
                }
                // Do the transfer as stated in B (tuck)
                self::transferCardFromTo($card, $owner_to, $location_to, $bottom_to, $score_keyword);
                break;
            
            // id 100, age 10: Self service
            case "100N1A":
                self::executeNonDemandEffects($card); // The player chose this card for execution
                break;
            
            // id 102, age 10: Stem cells 
            case "102N1A":
                // $choice is yes or no
                if ($choice == 0) { // No scoring
                    self::notifyPlayer($player_id, 'log', clienttranslate('${You} decide not to score the cards in your hand.'), array('You' => 'You'));
                    self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} decides not to score the cards in his hand.'), array('player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id)));
                }
                else { // "Score all cards from your hand"
                    self::notifyPlayer($player_id, 'log', clienttranslate('${You} decide to score the cards in your hand.'), array('You' => 'You'));
                    self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} decides to score the cards in his hand.'), array('player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id)));
                    
                    // Get all cards in hand
                    $ids_of_cards_in_hand = self::getIdsOfCardsInLocation($player_id, 'hand');

                    // Make the transfers
                    foreach($ids_of_cards_in_hand as $id) {
                        $card = self::getCardInfo($id);
                        self::scoreCard($card, $player_id);
                    }
                }                
                break;

            // id 114, Artifacts age 1: Papyrus of Ani
            case "114N1B":
                // "Reveal a card of of any type of value two higher"
                $age_to_draw_in = self::getGameStateValue('age_last_selected') + 2;
                $card = self::executeDraw($player_id, $age_to_draw_in, 'revealed', /*bottom_to=*/ false, /*type=*/ $choice);
                if ($card['color'] == 4) { // "If the drawn card is purple"
                    self::transferCardFromTo($card, $player_id, 'board'); // "Meld it"
                    self::executeNonDemandEffects($card); // "Execute each of its non-demand effects. Do not share them."
                    break;
                } else {
                    // Non-purple card is placed in the hand
                    self::transferCardFromTo($card, $player_id, 'hand');
                }
                break;
            
            // id 122, Artifacts age 1: Mask of Warka
            case "122N1A":
                self::notifyPlayer($player_id, 'log', clienttranslate('${You} choose ${color}.'), array('i18n' => array('color'), 'You' => 'You', 'color' => self::getColorInClear($choice)));
                self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} chooses ${color}.'), array('i18n' => array('color'), 'player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id), 'color' => self::getColorInClear($choice)));
                self::setAuxiliaryValue($choice);

                // "Each player reveals all cards of that color from their hand"
                $player_revealed_cards = false;
                $other_players_revealed_cards = false;
                $player_ids = self::getActivePlayerIdsInTurnOrderStartingWithCurrentPlayer();
                foreach ($player_ids as $id) {
                    $cards = self::getCardsInLocationKeyedByColor($id, 'hand')[$choice];
                    if (count($cards) == 0) {
                        $this->notifyPlayer($id, 'log', clienttranslate('${You} reveal no cards.'), ['You' => 'You']);
                        $this->notifyAllPlayersBut($id, 'log', clienttranslate('${player_name} reveals no cards.'), ['player_name' => self::getPlayerNameFromId($id)]);
                    } else {
                        $args = ['card_ids' => self::getCardIds($cards), 'card_list' => self::getNotificationArgsForCardList($cards)];
                        $this->notifyPlayer($id, 'logWithCardTooltips', clienttranslate('${You} reveal: ${card_list}.'),
                            array_merge($args, ['You' => 'You']));
                        $this->notifyAllPlayersBut($id, 'logWithCardTooltips', clienttranslate('${player_name} reveals: ${card_list}.'),
                            array_merge($args, ['player_name' => self::getPlayerNameFromId($id)]));
                        if ($id == $player_id) {
                            $player_revealed_cards = true;
                        } else {
                            $other_players_revealed_cards = true;
                        }
                    }
                }

                // "If you are the only player to reveal cards, return them"
                if ($player_revealed_cards && !$other_players_revealed_cards) {
                    $this->notifyPlayer($player_id, 'log', clienttranslate('No other player revealed a ${color} card.'), ['i18n' => ['color'], 'color' => self::getColorInClear($choice)]);
                    $this->notifyAllPlayersBut($player_id, 'log', clienttranslate('No player other than ${player_name} revealed a ${color} card.'), ['i18n' => ['color'], 'color' => self::getColorInClear($choice), 'player_name' => self::getPlayerNameFromId($player_id)]);
                    self::incrementStepMax(1);
                } else if ($player_revealed_cards && $other_players_revealed_cards) {
                    $this->notifyGeneralInfo(clienttranslate('More than one player revealed a ${color} card.'), ['i18n' => ['color'], 'color' => self::getColorInClear($choice)]);
                } else {
                    $this->notifyPlayer($player_id, 'log', clienttranslate('${You} did not reveal a ${color} card.'), ['i18n' => ['color'], 'color' => self::getColorInClear($choice), 'You' => 'You']);
                    $this->notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} did not reveal a ${color} card.'), ['i18n' => ['color'], 'color' => self::getColorInClear($choice), 'player_name' => self::getPlayerNameFromId($player_id)]);
                }
                break;

            case "122N1B":
                $different_values_selected_so_far = self::getAuxiliaryValue2AsArray();
                if (!in_array($card['age'], $different_values_selected_so_far)) { // The player choose to return a card of a new value
                    $different_values_selected_so_far[] = $card['age'];
                    self::setAuxiliaryValue2FromArray($different_values_selected_so_far);
                }
                // Do the transfer as stated in B (return)
                self::transferCardFromTo($card, $owner_to, $location_to, $bottom_to, $score_keyword);
                break;
            
            // id 124, Artifacts age 1: Tale of the Shipwrecked Sailor
            case "124N1A":
                self::notifyPlayer($player_id, 'log', clienttranslate('${You} choose ${color}.'), array('i18n' => array('color'), 'You' => 'You', 'color' => self::getColorInClear($choice)));
                self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} chooses ${color}.'), array('i18n' => array('color'), 'player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id), 'color' => self::getColorInClear($choice)));
                self::setAuxiliaryValue($choice);
                break;
            
            // id 126, Artifacts age 2: Rosetta Stone
            case "126N1A":
                // "Draw two 2s of that type"
                self::setGameStateValue('card_id_1', self::executeDraw($player_id, 2, 'hand', /*bottom_to=*/ false, /*type=*/ $choice)['id']);
                self::setGameStateValue('card_id_2', self::executeDraw($player_id, 2, 'hand', /*bottom_to=*/ false, /*type=*/ $choice)['id']);
                break;

            case "126N1C":
                // "Transfer the other to an opponent's board"
                $card_1 = self::getCardInfo(self::getGameStateValue('card_id_1'));
                $card_2 = self::getCardInfo(self::getGameStateValue('card_id_2'));
                $remaining_card = $card_1['location'] == 'hand' ? $card_1 : $card_2;
                self::transferCardFromTo($remaining_card, $choice, 'board');
                break;

            // id 130, Artifacts age 1: Baghdad Battery
            case "130N1A":
                if (self::getAuxiliaryValue() == -1) {
                    // Log the card that is melded first
                    $card_id = self::getGameStateValue('id_last_selected');
                    self::setAuxiliaryValue($card_id);
                    self::transferCardFromTo(self::getCardInfo($card_id), $player_id, 'board');
                } else {
                    // If you melded two of the same color and they are of different types
                    $first_card = self::getCardInfo(self::getAuxiliaryValue());
                    $second_card = self::getCardInfo(self::getGameStateValue('id_last_selected'));
                    self::transferCardFromTo($second_card, $player_id, 'board');
                    if ($first_card['type'] !== $second_card['type'] &&
                        $first_card['color'] == $second_card['color']) {
                        // "Draw and score five 2s"
                        for ($i = 1; $i <= 5; $i++) {
                            self::executeDraw($player_id, 2, 'score');
                        }
                    }
                }
                break;

            // id 147, Artifacts age 4: East India Company Charter
            case "147N1A":
                self::notifyPlayer($player_id, 'log', clienttranslate('${You} choose the value ${age}.'), array('You' => 'You', 'age' => self::getAgeSquare($choice)));
                self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} chooses the value ${age}.'), array('player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id), 'age' => self::getAgeSquare($choice)));
                self::setAuxiliaryValue($choice);
                break;

            // id 152, Artifacts age 5: Mona Lisa
            case "152N1A":
                self::notifyPlayer($player_id, 'log', clienttranslate('${You} choose the number ${n}.'), array('You' => 'You', 'n' => $choice));
                self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} chooses the number ${n}.'), array('player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id), 'n' => $choice));
                self::setAuxiliaryValue($choice);
                break;

            case "152N1B":
                self::notifyPlayer($player_id, 'log', clienttranslate('${You} choose ${color}.'), array('i18n' => array('color'), 'You' => 'You', 'color' => self::getColorInClear($choice)));
                self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} chooses ${color}.'), array('i18n' => array('color'), 'player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id), 'color' => self::getColorInClear($choice)));
                self::setAuxiliaryValue2($choice);
                break;

            // id 157, Artifacts age 5: Bill of Rights
            case "157C1A":
                self::notifyPlayer($player_id, 'log', clienttranslate('${You} choose ${color}.'), array('i18n' => array('color'), 'You' => 'You', 'color' => self::getColorInClear($choice)));
                self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} chooses ${color}.'), array('i18n' => array('color'), 'player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id), 'color' => self::getColorInClear($choice)));
                self::setAuxiliaryValue($choice);
                break;
            
            // id 158, Artifacts age 5: Ship of the Line Sussex
            case "158N1B":
                self::notifyPlayer($player_id, 'log', clienttranslate('${You} choose ${color}.'), array('i18n' => array('color'), 'You' => 'You', 'color' => self::getColorInClear($choice)));
                self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} chooses ${color}.'), array('i18n' => array('color'), 'player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id), 'color' => self::getColorInClear($choice)));
                self::setAuxiliaryValue($choice);
                break;
            
            // id 162, Artifacts age 5: The Daily Courant
            case "162N1A":
                self::notifyPlayer($player_id, 'log', clienttranslate('${You} choose the value ${age}.'), array('You' => 'You', 'age' => self::getAgeSquare($choice)));
                self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} chooses the value ${age}.'), array('player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id), 'age' => self::getAgeSquare($choice)));
                self::setAuxiliaryValue($choice);
                break;
            
            // id 170, Artifacts age 6: Buttonwood Agreement
            case "170N1A":
                $colors = self::getValueAsArray($choice);
                self::notifyPlayer($player_id, 'log', clienttranslate('${You} choose ${color_1}, ${color_2}, and ${color_3}.'), array(
                    'i18n' => array('color_1', 'color_2', 'color_3'),
                    'You' => 'You',
                    'color_1' => self::getColorInClear($colors[0]),
                    'color_2' => self::getColorInClear($colors[1]),
                    'color_3' => self::getColorInClear($colors[2]))
                );
                self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} chooses ${color_1}, ${color_2}, and ${color_3}.'), array(
                    'i18n' => array('color_1', 'color_2', 'color_3'),
                    'player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id),
                    'color_1' => self::getColorInClear($colors[0]),
                    'color_2' => self::getColorInClear($colors[1]),
                    'color_3' => self::getColorInClear($colors[2]))
                );
                
                // "Draw and reveal a 8"
                $card = self::executeDraw($player_id, 8, 'revealed');
                // "If the drawn card is one of the chosen colors, score it and splay up that color"
                if ($card['color'] == $colors[0] || $card['color'] == $colors[1] || $card['color'] == $colors[2]) {
                    self::notifyGeneralInfo(clienttranslate('It matches a chosen color: ${color}.'), array('i18n' => array('color'), 'color' => self::getColorInClear($card['color'])));
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
                self::notifyPlayer($player_id, 'log', clienttranslate('${You} choose ${color}.'), array('i18n' => array('color'), 'You' => 'You', 'color' => self::getColorInClear($choice)));
                self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} chooses ${color}.'), array('i18n' => array('color'), 'player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id), 'color' => self::getColorInClear($choice)));
                self::setAuxiliaryValue($choice);
                break;
                
            // id 179, Artifacts age 7: International Prototype Metre Bar
            case "179N1A":
                self::notifyPlayer($player_id, 'log', clienttranslate('${You} choose the value ${age}.'), array('You' => 'You', 'age' => self::getAgeSquare($choice)));
                self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} chooses the value ${age}.'), array('player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id), 'age' => self::getAgeSquare($choice)));
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
                self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} chooses the value ${age}.'), array('player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id), 'age' => self::getAgeSquare($choice)));
                self::setAuxiliaryValue($choice);
                break;

            // id 211, Artifacts age 10: Dolly the Sheep
            case "211N1A":
                if ($choice == 0) {
                    self::notifyPlayer($player_id, 'log', clienttranslate('${You} choose not to score your bottom yellow card.'), array('You' => 'You'));
                    self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} chooses not to score his bottom yellow card.'), array('player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id)));
                } else{
                    self::notifyPlayer($player_id, 'log', clienttranslate('${You} choose to score your bottom yellow card.'), array('You' => 'You'));
                    self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} chooses to score his bottom yellow card.'), array('player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id)));
                }
                self::setAuxiliaryValue($choice);
                break;

            case "211N1B":
                $age_to_draw_in = self::getAgeToDrawIn($player_id, 1);
                if ($choice == 0) {
                    self::notifyPlayer($player_id, 'log', clienttranslate('${You} choose not to draw and tuck.'), array('You' => 'You'));
                    self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} chooses not to draw and tuck.'), array('player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id)));
                } else {
                    self::notifyPlayer($player_id, 'log', clienttranslate('${You} choose to draw and tuck.'), array('You' => 'You'));
                    self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} chooses to draw and tuck.'), array('player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id)));
                }
                self::setAuxiliaryValue($choice);
                break;
                                
            default:
                if ($splay_direction == -1) {
                    // Do the transfer as stated in B
                    if ($location_to == 'revealed,deck') {
                        self::transferCardFromTo($card, $owner_to, 'revealed'); // Reveal
                        $card = self::getCardInfo($card['id']); // Update the card status
                        self::transferCardFromTo($card, 0, 'deck'); // Return
                    }
                    else {
                        self::transferCardFromTo($card, $owner_to, $location_to, $bottom_to, $score_keyword);
                    }
                }
                else {
                    // Do the splay as stated in B
                    self::splay($player_id, $card['owner'], $card['color'], $splay_direction);
                }
                break;
            }
        //[CC]||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||
        }
        catch (EndOfGame $e) {
            // End of the game: the exception has reached the highest level of code
            self::trace('EOG bubbled from self::stInterSelectionMove');
            self::trace('interSelectionMove->justBeforeGameEnd');
            $this->gamestate->nextState('justBeforeGameEnd');
            return;
        }
        
        if ($special_type_of_choice == 0) {
            // Mark that one more card has been chosen and proceeded in that step
            self::incGameStateValue('n', 1);
            self::incGameStateValue('n_min', -1);
            self::incGameStateValue('n_max', -1);
            
            // Mark extra information about this chosen card
            self::setGameStateValue("age_last_selected", $card['age']);
            self::setGameStateValue("color_last_selected", $card['color']);
            self::setGameStateValue("owner_last_selected", $card['owner']);
        }
        // Check if another selection is to be done
        if ($special_type_of_choice != 0 || self::getGameStateValue('n_max') == 0) { // No more choice can be made
            // Unset the selection
            self::deselectAllCards();
            // End of this interaction step
            self::trace('interSelectionMove->interInteractionStep');
            $this->gamestate->nextState('interInteractionStep');
            return;
        }
        // New selection move
        self::setGameStateValue('can_pass', 0); // Passing is no longer possible (stopping will be if n_min == 0)
        self::trace('interSelectionMove->preSelectionMove');
        $this->gamestate->nextState('preSelectionMove');        
    }
    
    function stJustBeforeGameEnd() {
        switch(self::getGameStateValue('game_end_type')) {
        case 0: // achievements
            self::notifyEndOfGameByAchievements();
            self::setStat(true, 'end_achievements');
            break;
        case 1: // score
            // Important value for winning is no more the number of achievements but the score
            // Promote player score to BGA score
            // Keeping the number of achivement as BGA auxiliary score as tie breaker
            self::promoteScoreToBGAScore();
            self::notifyEndOfGameByScore();
            self::setStat(true, 'end_score');
            break;
        case -1: // dogma
            // In that case, the score is modified so that the winner team got 1, the losers 0, there is no tie breaker
            self::binarizeBGAScore();
            self::notifyEndOfGameByDogma();
            self::setStat(true, 'end_dogma');
            break;
        default:
            break;
        }
        
        self::trace('justBeforeGameEnd->gameEnd');
        $this->gamestate->nextState(); // End the game
    }

//////////////////////////////////////////////////////////////////////////////
//////////// Zombie
////////////

    /*
        zombieTurn:
        
        This method is called each time it is the turn of a player who has quit the game (= "zombie" player).
        You can do whatever you want in order to make sure the turn of this player ends appropriately
        (ex: pass).
    */

    function zombieTurn($state, $active_player) {
        throw new feException( "Zombie mode not supported at this moment" );
        $statename = $state['name'];
        
        if ($state['type'] == "activeplayer") {
            $player_name = self::getPlayerNameFromId($active_player);
            self::notifyAll('log', clienttranslate('The turn of ${player_name} is skipped.'), array('player_name' => $player_name));
            switch ($statename) {
                case 'playerTurn':
                    self::trace('playerTurn->interPlayerTurn (zombie)');
                    $this->gamestate->nextState('interPlayerTurn');
                    break;
                case 'selectionMove':
                    self::trace('selectionMove->interInteractionStep (zombie)');
                    $this->gamestate->nextState('interInteractionStep');
                    // Set the player choices as "pass" (this will work even if he is normally not supposed to)
                    self::setGameStateValue('id_last_selected', -1);
                    self::setGameStateValue('choice', -2);
                    self::deselectAllCards(); // Deselect all the cards the player could choose if he had not quitted
                    // --> There are further implications in self::stInterInteractionStep
                    break;
                default:
                    break;
            }

            return;
        }

        if ($state['type'] == "multipleactiveplayer") {
            // state turn0
            // Make sure player is in a non blocking status for role turn
            $sql = "
                UPDATE  player
                SET     player_is_multiactive = 0
                WHERE   player_id = $active_player
            ";
            self::DbQuery($sql);

            $this->gamestate->updateMultiactiveOrNextState('');
            return;
        }

        throw new feException("Zombie mode not supported at this game state: ".$statename);
    }
    
    function isZombie($player_id) {
        return self::getUniqueValueFromDB(self::format("
            SELECT player_zombie FROM player WHERE player_id={player_id}
        ", array('player_id' => $player_id)));
    }
}

