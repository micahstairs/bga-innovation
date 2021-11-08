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
            'current_player_under_dogma_effect' => 19,
            'dogma_card_id' => 20,
            'current_effect_type' => 21,
            'current_effect_number' => 22,
            'sharing_bonus' => 23,
            'step' => 24,
            'step_max' => 25,
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
            'auxiliary_value' => 49,
            'nested_id_1' => 50,
            'nested_current_effect_number_1' => 51,
            'nested_id_2' => 52,
            'nested_current_effect_number_2' => 53,
            'nested_id_3' => 54,
            'nested_current_effect_number_3' => 55,
            'nested_id_4' => 56,
            'nested_current_effect_number_4' => 57,
            'nested_id_5' => 58,
            'nested_current_effect_number_5' => 59,
            'nested_id_6' => 60,
            'nested_current_effect_number_6' => 61,
            'nested_id_7' => 62,
            'nested_current_effect_number_7' => 63,
            'nested_id_8' => 64,
            'nested_current_effect_number_8' => 65,
            'nested_id_9' => 66,
            'nested_current_effect_number_9' => 67,    
            'card_id_1' => 69,
            'card_id_2' => 70,
            'card_id_3' => 71,
            'require_achievement_eligibility' => 72,
            
            'debug_mode' => 99, // Set to 1 to enable debug mode (to enable to draw any card in the game). Set to 0 in production
            
            'game_type' => 100, // 1 for normal game, 2 for team game
            'game_rules' => 101 // 1 for last edition, 2 for first edition
        ));
    }
    
    protected function getGameName()
    {
        return "innovation";
    }

    function upgradeTableDb($from_version) {
        if ($from_version <= 2110162118) {        
            $sql = "ALTER TABLE DBPREFIX_card ADD `type` TINYINT UNSIGNED NOT NULL DEFAULT '0';";
            self::applyDbUpgradeToAllDB($sql); 
        }
    }
    
    //****** CODE FOR DEBUG MODE
    function debug_draw($card_id) {
        if (self::getGameStateValue('debug_mode') == 0) {
            return; // Not in debug mode
        }
        $player_id = self::getCurrentPlayerId();
        $card = self::getCardInfo($card_id);
        $card['debug_draw'] = true;
        if ($card['location'] == 'deck') {
            self::transferCardFromTo($card, $player_id, 'hand');
        }
        else if ($card['location'] == 'achievements') {
            throw new BgaUserException(self::_("This card is used as an achievement"));
        }
        else if ($card['location'] == 'removed') {
            throw new BgaUserException(self::_("This card is removed from the game"));
        }
        else {
            throw new BgaUserException(self::format(self::_("This card is in {player_name}'s {location}"), array('player_name' => self::getPlayerNameFromId($card['owner']), 'location' => $card['location'])));
        }
    }
    function debug_score($card_id) {
        if (self::getGameStateValue('debug_mode') == 0) {
            return; // Not in debug mode
        }
        $player_id = self::getCurrentPlayerId();
        $card = self::getCardInfo($card_id);
        $card['debug_score'] = true;
        if ($card['location'] == 'hand' || $card['location'] == 'board' || $card['location'] == 'deck') {
            self::transferCardFromTo($card, $player_id, 'score', false, true);
        }
        else if ($card['location'] == 'achievements') {
            throw new BgaUserException(self::_("This card is used as an achievement"));
        }
        else if ($card['location'] == 'removed') {
            throw new BgaUserException(self::_("This card is removed from the game"));
        }
        else {
            throw new BgaUserException(self::format(self::_("This card is in {player_name}'s {location}"), array('player_name' => self::getPlayerNameFromId($card['owner']), 'location' => $card['location'])));
        }
    }
    function debug_achieve($card_id) {
        if (self::getGameStateValue('debug_mode') == 0) {
            return; // Not in debug mode
        }
        $player_id = self::getCurrentPlayerId();
        $card = self::getCardInfo($card_id);
        $card['debug_achieve'] = true;
        if ($card['location'] == 'hand' || $card['location'] == 'board' || $card['location'] == 'deck' || $card['location'] == 'score' || $card['location'] == 'achievements') {
            try{
                self::transferCardFromTo($card, $player_id, "achievements");
            }
            catch (EndOfGame $e) {
                // End of the game: the exception has reached the highest level of code
                self::trace('EOG bubbled from self::debug_achieve');
                $this->gamestate->nextState('justBeforeGameEnd');
                return;
            }
        }
        else if ($card['location'] == 'removed') {
            throw new BgaUserException(self::_("This card is removed from the game"));
        }
        else {
            throw new BgaUserException(self::format(self::_("This card is in {player_name}'s {location}"), array('player_name' => self::getPlayerNameFromId($card['owner']), 'location' => $card['location'])));
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

        // Init global values with their initial values
        self::setGameStateValue('debug_mode', $this->getBgaEnvironment() == 'studio' ? 1 : 0);
        
        // Number of achievements needed to win: 6 with 2 players, 5 with 3 players, 4 with 4 players and 6 for team game
        $number_of_achievements_needed_to_win = $individual_game ? 8 - count($players) : 6;
        self::setGameStateInitialValue('number_of_achievements_needed_to_win', $number_of_achievements_needed_to_win);
        
        // Flag used to know if we are still on turn0 (1) or not (0)
        self::setGameStateInitialValue('turn0', 1);
        
        // Flags used to know if the player has one or two actions to perform
        self::setGameStateInitialValue('first_player_with_only_one_action', 1);
        self::setGameStateInitialValue('second_player_with_only_one_action', count($players) >= 4 ? 1 : 0); // used when >= 4 players only
        self::setGameStateInitialValue('has_second_action', 1);
        
        // Flags used when the game ends to know how it ended
        self::setGameStateInitialValue('game_end_type', -1); // 0 for game end by achievements, 1 for game end by score, -1 for game end by dogma
        self::setGameStateInitialValue('player_who_could_not_draw', -1); // When end of game by score, id of the player who triggered it
        
        // Flag used to remember whose turn it is
        self::setGameStateInitialValue('active_player', -1);
        
        // Flags used in dogma to remember player roles and which card it is, which effect (yet -1 as default value since there are not currently in use)
        self::setGameStateInitialValue('current_player_under_dogma_effect', -1);
        self::setGameStateInitialValue('dogma_card_id', -1);
        self::setGameStateInitialValue('current_effect_type', -1); // 0 for I demand dogma, 1 for non-demand dogma
        self::setGameStateInitialValue('current_effect_number', -1); // 1, 2 or 3
        self::setGameStateInitialValue('sharing_bonus', -1); // 1 if the dogma player will have a sharing bonus, else 0
        
        // Flags used for player interaction in dogma to remember where we are in the process (yet -1 as default value since there are not currently in use)
        self::setGameStateInitialValue('step', -1);
        self::setGameStateInitialValue('step_max', -1);
        
        // Flag used for player interaction in dogma to remember what splay is proposed (-1 as default and if the choice does not involve splaying)
        self::setGameStateInitialValue('splay_direction', -1);
        
        // Flags used to describe the range of the selection the player in dogma must take (yet -1 as default value since there are not currently in use)
        self::setGameStateInitialValue('special_type_of_choice', -1); // Indicate the type of choice the player faces. 0 for choosing a card or a color for splay, 1 for choosing an opponent, 2 for choising an opponent with fewer points, 3 for choosing a value, 4 for choosing between yes or no
        self::setGameStateInitialValue('choice', -1); // Numeric choice when the player has to make a special choice (-2 if the player passed)
        self::setGameStateInitialValue('n_min', -1); // Minimal number of cards to be chosen (999 stands for all possible)
        self::setGameStateInitialValue('n_max', -1); // Maximal number of cards to be chosen (999 stands for no limit)
        self::setGameStateInitialValue('solid_constraint', -1); // 1 if there need to be at least n_min cards to trigger the effect or 0 if it is triggered no matter what, which will consume all eligible cards (do what you can rule)
        self::setGameStateInitialValue('owner_from', -1); // Owner from whom choose the card (0 for nobody, -2 for any player, -3 for any opponent)
        self::setGameStateInitialValue('location_from', -1); // Location from where choose the card (0 for deck, 1 for hand, 2 for board, 3 for score)
        self::setGameStateInitialValue('owner_to', -1); // Owner to whom the chosen card will be transfered (0 for nobody)
        self::setGameStateInitialValue('location_to', -1); // Location where the chosen card will be transfered (0 for deck, 1 for hand, 2 for board, 3 for score)
        self::setGameStateInitialValue('age_min', -1); // Age min of the card to be chosen
        self::setGameStateInitialValue('age_max', -1); // Age max of the card to be chosen
        self::setGameStateInitialValue('color_array', -1); // List of selectable colors encoded in a single value
        self::setGameStateInitialValue('with_icon', -1); // 0 if there is no specific icon for the card to be selected, else the number of the icon needed
        self::setGameStateInitialValue('without_icon', -1); // 0 if there is no specific icon for the card to be selected, else the number of the icon which can't be selected
        self::setGameStateInitialValue('not_id', -1); // id of a card which cannot be selcected, else -2
        self::setGameStateInitialValue('card_id_1', -1); // id of a card which is allowed to be selected, else -2
        self::setGameStateInitialValue('card_id_2', -1); // id of a card which is allowed to be selected, else -2
        self::setGameStateInitialValue('card_id_3', -1); // id of a card which is allowed to be selected, else -2
        self::setGameStateInitialValue('can_pass', -1); // 1 if the player can pass else 0
        self::setGameStateInitialValue('n', -1); // Actual number of cards having being selected yet
        self::setGameStateInitialValue('id_last_selected', -1); // Id of the last selected card
        self::setGameStateInitialValue('age_last_selected', -1); // Age of the last selected card
        self::setGameStateInitialValue('color_last_selected', -1); // Color of the last selected card
        self::setGameStateInitialValue('score_keyword', -1); // 1 if the action with the chosen card will be scoring, else 0
        self::setGameStateValue('require_achievement_eligibility', -1); // 1 if the numeric achievement card can only be selected if the player is eligible to claim it based on their score
        
        // Flags specific to some dogmas
        self::setGameStateInitialValue('auxiliary_value', -1); // This value is used when in dogma for some specific cards when it is needed to remember something between steps or effect. By default, it does not reinitialise until the end of the dogma
        
        // Flags specific for exclusive execution
        for($i=1; $i<=9; $i++) {
            self::setGameStateInitialValue('nested_id_'.$i, -1); // The card being executed through exclusive execution
            self::setGameStateInitialValue('nested_current_effect_number_'.$i, -1); // The non-demand effect number currently executed
        }
        
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
        
        // Card shuffling in decks
        self::shuffle();
        
        // Isolate one card of each age (except 10) to create the available age achievements
        self::extractAgeAchievements();
        
        // Deal 2 cards of age 1 to each player
        for ($times = 0; $times < 2; $times++) {
            foreach ($players as $player_id => $player) {
                self::executeDraw($player_id, 1);
            }
        }
        
        // Activate first player (which is in general a good idea :))
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
        $debug_mode = self::getGameStateValue('debug_mode') == 1;
        
        //****** CODE FOR DEBUG MODE
        if ($debug_mode) {
            $name_list  = array();
            foreach($this->textual_card_infos as $card) {
                $name_list[] = $card['name'];
            }
            $result['debug_card_list'] = $name_list;
        }
        //******
    
        $current_player_id = self::getCurrentPlayerId();    // !! We must only return informations visible by this player !!
        
        // Get information about players
        // Note: you can retrieve some extra field you added for "player" table in "dbmodel.sql" if you need it.
        $players = self::getCollectionFromDb("SELECT player_id, player_score, player_team FROM player");
        $result['players'] = $players;
        $result['current_player_id'] = $current_player_id;
        
        // Public information

        // Icon information for each card
        $result['card_icons'] = self::getCollectionFromDb("SELECT id, spot_1, spot_2, spot_3, spot_4 FROM card");

        // Number of achievements needed to win
        $result['number_of_achievements_needed_to_win'] = self::getGameStateValue('number_of_achievements_needed_to_win');
        
        // All boards
        $result['board'] = self::getAllBoards($players);
        
        // Splay state for piles on board
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
        
        // Backs of the cards in hands (number of cards each player have in each age in their hands)
        $result['hand_counts'] = array();
        foreach ($players as $player_id => $player) {
            $result['hand_counts'][$player_id] = self::countCardsInLocation($player_id, 'hand', true);
        }
        
        // Backs of the cards in hands (number of cards each player have in each age in their score piles)
        $result['score_counts'] = array();
        foreach ($players as $player_id => $player) {
            $result['score_counts'][$player_id] = self::countCardsInLocation($player_id, 'score', true);
        }
        
        // Score (totals in the score piles) for each player
        $result['score'] = array();
        foreach ($players as $player_id => $player) {
            $result['score'][$player_id] = self::getPlayerScore($player_id);
        }
        
        // Revealed card
        $result['revealed'] = array();
        foreach($players as $player_id => $player) {
            $result['revealed'][$player_id] = self::getCardsInLocation($player_id, 'revealed');
        }        
        
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
            $result['ressource_counts'][$player_id] = self::getPlayerRessourceCounts($player_id);
        }
        
        // Max age on board for each player
        $result['max_age_on_board'] = array();
        foreach ($players as $player_id => $player) {
            $result['max_age_on_board'][$player_id] = self::getMaxAgeOnBoardTopCards($player_id);
        }
        
        // Remaining cards in deck
        $result['deck_counts'] = self::countCardsInLocation(0, 'deck', true);   
        
        // Turn0 or not
        $result['turn0'] = self::getGameStateValue('turn0') == 1;
        
        // Normal achievement names
        $result['normal_achievement_names'] = array();
        for($age=1; $age<=9; $age++) {
            $result['normal_achievement_names'][$age] = self::getNormalAchievementName($age);
        }
        
        // Number of achievements needed to win
        $result['number_of_achievements_needed_to_win'] = self::getGameStateValue('number_of_achievements_needed_to_win');
        
        // Link to the current dogma effect if any
        $nested_id_1 = self::getGameStateValue('nested_id_1');
        $card_id = $nested_id_1 == -1 ? self::getGameStateValue('dogma_card_id') : $nested_id_1;
        if ($card_id == -1) {
            $JSCardEffectQuery = null;
        }
        else {
            $card = self::getCardInfo($card_id);
            $current_effect_type = $nested_id_1 == -1 ? self::getGameStateValue('current_effect_type') : 1 /* Non-demand effects only*/;
            $current_effect_number = $nested_id_1 == -1 ?  self::getGameStateValue('current_effect_number') : self::getGameStateValue('nested_current_effect_number_1');
            $JSCardEffectQuery = $current_effect_number == -1 ? null : self::getJSCardEffectQuery($card_id, $card['age'], $current_effect_type, $current_effect_number);
        }
        $result['JSCardEffectQuery'] = $JSCardEffectQuery;
        
        // Whose turn is it?
        $active_player = self::getGameStateValue('active_player');
        $result['active_player'] = $active_player == -1 ? null : $active_player;
        if ($active_player != -1) {
            $result['action_number'] = self::getGameStateValue('first_player_with_only_one_action') || self::getGameStateValue('second_player_with_only_one_action') || self::getGameStateValue('has_second_action') ? 1 : 2;
        }
        
        // Private information
        // My hand
        $result['my_hand'] = self::flatten(self::getCardsInLocation($current_player_id, 'hand', true));
        
        // My score
        $result['my_score'] = self::flatten(self::getCardsInLocation($current_player_id, 'score', true));
        
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
        
        $number_of_cards_in_decks = self::countCardsInLocation(0, 'deck', true);
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
        $encoded_value = 0;
        foreach($array as $value) {
            $encoded_value += pow(2, $value);
        }
        self::setGameStateValue($key, $encoded_value);
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
    
    /** integer division **/
    function intDivision($a, $b) {
        return (int)($a/$b);
    }
    
    function intDivisionRoundedUp($a, $b) {
        return self::intDivision($a, $b) + ($a % $b > 0);
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
        /** Shuffle all cards in their age piles, at the beginning of the game **/
        
        // Generate a random number for each age card
        self::DbQuery("
        INSERT INTO random
            SELECT
                id,
                age,
                RAND() AS random_number
            FROM
                card 
            WHERE
                age IS NOT NULL
        ");
        
        // Give the new position based on the random number of the card, in the age pile it belongs to
        self::DbQuery("
        INSERT INTO shuffled
            SELECT
                a.id,
                (SELECT
                    COUNT(*)
                FROM
                    random AS b
                WHERE
                    b.age = a.age AND
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
            INNER JOIN (SELECT age, MAX(position) AS position FROM card GROUP BY age) as b ON a.age = b.age
        SET
            a.location = 'achievements',
            a.position = a.age-1
        WHERE
            a.position = b.position AND
            a.age BETWEEN 1 AND 9
        ");
    }
    
    function transferCardFromTo($card, $owner_to, $location_to, $bottom_to=false, $score_keyword=false) {
        /** Execute the transfer of the card with all information needed. The new position is calculated according to $location_to.
        
        Return the card transferred as a dictionary.
        **/
        if ($location_to == 'deck') { // We always return card at the bottom of the deck
            $bottom_to = true;
        }
        
        $id = $card['id'];
        $age = $card['age'];
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
        
        // Filters for cards of the same family: the cards of the decks are grouped by age, whereas the cards of the board are grouped by player and by color
        // Filter from
        $filter_from = self::format("owner = {owner_from} AND location = '{location_from}'", array('owner_from' => $owner_from, 'location_from' => $location_from));
        switch ($location_from) {
        case 'deck':
        case 'hand':
        case 'score':
            $filter_from .= self::format(" AND age = {age}", array('age' => $age));
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
        case 'hand':
        case 'score':
            $filter_to .= self::format(" AND age = {age}", array('age' => $age));
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
        }
        else { // $bottom_to is false
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
                        self::splay($owner_from, $color, 0); // Unsplay
                    }
                }
            }
        }
        return $card;
    }
    
    /** Splay mechanism **/
    function splay($player_id, $color, $splay_direction, $force_unsplay=false) {
        // This function must be called only if the splay is relevant
        // ie : the new splay direction is different from the former one
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
            array('owner' => $player_id, 'color' => $color, 'splay_direction' => $splay_direction)
        ));
        
        self::notifyForSplay($player_id, $color, $splay_direction, $force_unsplay);
        
        // Check for all achievements
        try {
            self::checkForSpecialAchievements($player_id, true); // All including Wonder
        }
        catch(EndOfGame $e) {
            self::trace('EOG bubbled from self::splay');
            throw $e; // Re-throw exception to higher level
        }
        
        // Check if this transfer triggers a sharing bonus        
        self::checkIfSharingBonus();
    }
    
    /* Rearrangement mechanism */
    function rearrange($player_id, $color, $permutations) {
        
        $old_board = self::getCardsInLocation($player_id, 'board', false, true);
        
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
        
        $new_board = self::getCardsInLocation($player_id, 'board', false, true);
        
        $actual_change = $old_board[$color] != $new_board[$color];
        
        if ($actual_change) {
            self::updatePlayerRessourceCounts($player_id);
            self::checkIfSharingBonus();
        } // If there is no actual change, the player is cheating
        
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
        return self::attachTextualInfoToList(self::getObjectListFromDB("
            SELECT
                *
            FROM
                card
            WHERE
                selected IS TRUE
        "));
    }
    
    function getVisibleSelectedCards($player_id) {
        return self::attachTextualInfoToList(self::getObjectListFromDB(self::format("
            SELECT
                *
            FROM
                card
            WHERE
                selected IS TRUE AND
                (location = 'board' OR owner = {player_id} AND location != 'achievements')
                
        ", // A player can see the versos of all cards on all boards and all the cards in his hand and his score
            array('player_id' => $player_id)
        )));
    }
    
    function getSelectableRectos($player_id) {
        return self::getObjectListFromDB(self::format("
            SELECT
                owner, location, age, position
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

    function getCardsInHand($player_id) {
        return self::attachTextualInfoToList(self::getObjectListFromDB(self::format("
            SELECT
                *
            FROM
                card
            WHERE
                location = 'hand' AND
                owner = {player_id}
                
        ",
            array('player_id' => $player_id)
        )));
    }
    
    /*function isSelected($card_id) {
        return self::getUniqueValueFromDB(self::format("
            SELECT
                selected
            FROM
                card
            WHERE
                id = {card_id}
        ",
            array("card_id" => $card_id)
        ));
    }*/

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
        foreach($this->players as $player_id => $player) {
            if(in_array($player_id, $player_ids)) {
                continue;
            }
            self::notifyPlayer($player_id, $notification_type, $notification_log, $notification_args);
        }
        
        // Notify spectator: same message but have to redirect on other handler in JS for spectators to see messages in logs
        self::notifyAllPlayers($notification_type . '_spectator', '', array_merge($notification_args, array('notification_type' => $notification_type, 'log' => $notification_log))); // Players won't suscribe to this: it is filtered by the JS
    }
    
    function updateGameSituation($card, $transferInfo) {
        $owner_from = $transferInfo['owner_from'];
        $owner_to = $transferInfo['owner_to'];
        
        $location_from = $transferInfo['location_from'];
        $location_to = $transferInfo['location_to'];
        
        $bottom_to = $transferInfo['bottom_to'];
        
        $age = $card['age'];
        
        $game_end = false;
        
        // Check if this transfer triggers a sharing bonus
        self::checkIfSharingBonus();
        
        $score_from_update = $location_from == 'score';
        $score_to_update = $location_to == 'score';
        
        $max_age_on_board_from_update = $location_from == 'board';
        $max_age_on_board_to_update = $location_to == 'board';
        
        $progressInfo = array();
        // Update player progression if applicable
        $one_player_involved = $owner_from == 0 || $owner_to == 0 || $owner_from == $owner_to;
        if ($one_player_involved) {
            $player_id = $owner_to == 0 ? $owner_from : $owner_to; // The player whom transfer will change something on the cards he owns
            //****** CODE FOR DEBUG MODE
            if (array_key_exists('debug_draw', $card) || array_key_exists('debug_achieve', $card) || array_key_exists('debug_score', $card) || array_key_exists('debug_return', $card) ||  $location_to == 'achievements') {
                $launcher_id = $player_id;
                $one_player_involved = true;
            }
            //******
            else {
                $launcher_id = self::getActivePlayerId(); // The player from whom this action is originated, most of the case, the same as player_id except for few dogma effects
                $one_player_involved = $player_id == $launcher_id;
            }
        }
              
        if ($one_player_involved) { // One player involved
            $transferInfo['player_id'] = $player_id;
            
            $ressource_count_update = $location_from == 'board' || $location_to == 'board';
            
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
            if ($ressource_count_update) {
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
            
            $ressource_count_from_update = $location_from == 'board';
            $ressource_count_to_update = $location_to == 'board';
            
            if ($score_from_update) {
                $progressInfo['new_score_from'] = self::updatePlayerScore($owner_from, -$age);
            }
            if ($score_to_update) {
                $progressInfo['new_score_to'] = self::updatePlayerScore($owner_to, $age);
            }
            
            if ($ressource_count_from_update) {
                $progressInfo['new_ressource_counts_from'] = self::updatePlayerRessourceCounts($owner_from);
            }
            if ($ressource_count_to_update) {
                $progressInfo['new_ressource_counts_to'] = self::updatePlayerRessourceCounts($owner_to);
            }
            if ($max_age_on_board_from_update) {
                $max_age_on_board_from = self::getMaxAgeOnBoardTopCards($owner_from);
                $progressInfo['new_max_age_on_board_from'] = $max_age_on_board_from;
                self::setStat($max_age_on_board_from, 'max_age_on_board', $owner_from);
            }
            if ( $max_age_on_board_to_update) {
                $max_age_on_board_to = self::getMaxAgeOnBoardTopCards($owner_to);
                $progressInfo['new_max_age_on_board_to'] = $max_age_on_board_to;
                self::setStat($max_age_on_board_to, 'max_age_on_board', $owner_to);
            }
            self::notifyWithTwoPlayersInvolved($card, $transferInfo, $progressInfo);
        }
        
        $end_of_game = false;
        if ($location_to == 'achievements') { // The player has got an extra-achievement
            // The number of achievements is the BGA score (not to be confused with the definition of score in Innovation game)
            // So, increase BGA score by one
            try {
                self::incrementBGAScore($owner_to, $card['age'] === null);
            }
            catch(EndOfGame $e) {
                $end_of_game = true;
            }
        }
        else { // This was not an achievement-transfer
            // Check if the change brought by the transfer enables a player to get a special achievements
            if ($one_player_involved) {
                try {
                    self::checkForSpecialAchievements($player_id, false); // Check all except Wonder
                }
                catch(EndOfGame $e) {
                    $end_of_game = true;
                }
            }
            else {
                // Determine players priority for claiming special achievements (to solve the rare case when both would be eligible for the same one)
                // Rule: in that case, the winner of the card is the one who triggered the dogma if he is one of the two player, else the player who is nearer to him in turn order
                list($player_1, $player_2) = self::getPlayerPriorityForSpecialAchievements($player_id, $opponent_id);
                
                // Check for special achievements: first player
                try {
                    self::checkForSpecialAchievements($player_1, false); // Check all except Wonder
                }
                catch(EndOfGame $e) {
                    $end_of_game = true;
                }

                // Check for special achievements: second player
                try {
                    self::checkForSpecialAchievements($player_2, false); // Check all except Wonder
                }
                catch(EndOfGame $e) {
                    $end_of_game = true;
                } 
            }
        }
        if ($end_of_game) {
            self::trace('EOG bubbled from self::updateGameSituation');
            throw $e; // Re-throw exception to higher level
        }
    }
    
    function getDelimiterMeanings($text) {
        $left_v = "<span class='square N age_";
        $right_v = "'></span>";
        
        $left_vv = "<span class='card_name'>";
        $right_vv = "</span>";
        
        $left_vvv = "<span class='achievement_name'>";
        $right_vvv = "</span>";
        
        $left_I = "<span class='square N icon_";
        $right_I = "'></span>";
        
        $delimiters = array();
        
        if (strpos($text, '{<}') > -1) { // Delimiters for age icon
            $delimiters['<'] = $left_v;
            $delimiters['>'] = $right_v;
        }
        if (strpos($text, '{<<}') > -1) { // Delimiters for card name
            $delimiters['<<'] = $left_vv;
            $delimiters['>>'] = $right_vv;
        }
        if (strpos($text, '{<<<}') > -1) { // Delimiters for achievement name
            $delimiters['<<<'] = $left_vvv;
            $delimiters['>>>'] = $right_vvv;
        }
        if (strpos($text, '{[}') > -1) { // Delimiters for ressource icon
            $delimiters['['] = $left_I;
            $delimiters[']'] = $right_I;        
        }
        return $delimiters;
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
                throw new BgaVisibleSystemException(self::format(self::_("Unhandled case in {function}: '{code}'"), array('function' => "getLetterForEffectType()", code => $effect_type)));
                break;
        }
    }
    
    function notifyWithOnePlayerInvolved($card, $transferInfo, $progressInfo) {
        $location_from = $transferInfo['location_from'];
        $location_to = $transferInfo['location_to'];
        $bottom_to = $transferInfo['bottom_to'];
        $score_keyword = $transferInfo['score_keyword'];

        switch($location_from . '->' . $location_to) {
        case 'deck->hand':
            $message_for_player = clienttranslate('${You} draw ${<}${age}${>} ${<<}${name}${>>}.');
            $message_for_others = clienttranslate('${player_name} draws a ${<}${age}${>}.');
            break;
        case 'deck->board':
            if ($bottom_to) {;
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
        case 'hand->deck':
            $message_for_player = clienttranslate('${You} return ${<}${age}${>} ${<<}${name}${>>} from your hand.');
            $message_for_others = clienttranslate('${player_name} returns a ${<}${age}${>} from his hand.');
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
        case 'pile->deck': // Skyscrapers
            $message_for_player = clienttranslate('${You} return ${<}${age}${>} ${<<}${name}${>>} from your board.');
            $message_for_others = clienttranslate('${player_name} returns ${<}${age}${>} ${<<}${name}${>>} from his board.');
            break;
        case 'board->hand':
            $message_for_player = clienttranslate('${You} take back ${<}${age}${>} ${<<}${name}${>>} from your board to your hand.');
            $message_for_others = clienttranslate('${player_name} takes back ${<}${age}${>} ${<<}${name}${>>} from his board to his hand.');
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
        case 'score->achievements':
            $message_for_player = clienttranslate('${You} achieve ${<}${age}${>} ${<<}${name}${>>} from your score pile.');
            $message_for_others = clienttranslate('${player_name} achieves a ${<}${age}${>} from his score pile.');
            break;
        case 'revealed->deck':
            $message_for_player = clienttranslate('${You} return ${<}${age}${>} ${<<}${name}${>>}.');
            $message_for_others = clienttranslate('${player_name} returns ${<}${age}${>} ${<<}${name}${>>}.');
            break;
        case 'revealed->hand':
            $message_for_player = clienttranslate('${You} place ${<}${age}${>} ${<<}${name}${>>} in your hand.');
            $message_for_others = clienttranslate('${player_name} places ${<}${age}${>} ${<<}${name}${>>} in his hand.');
            break;
        case 'revealed->board':
            $message_for_player = clienttranslate('${You} meld ${<}${age}${>} ${<<}${name}${>>}.');
            $message_for_others = clienttranslate('${player_name} melds ${<}${age}${>} ${<<}${name}${>>}.');
            break;
        case 'revealed->score':
            $message_for_player = clienttranslate('${You} score ${<}${age}${>} ${<<}${name}${>>}.');
            $message_for_others = clienttranslate('${player_name} scores ${<}${age}${>} ${<<}${name}${>>}.');
            break;
        case 'achievements->achievements': // That is: unclaimed achievement to achievement claimed by player
            if ($card['age'] === null) { // Special achivement
                $message_for_player = clienttranslate('${You} achieve ${<<<}${achievement_name}${>>>}.');
                $message_for_others = clienttranslate('${player_name} achieves ${<<<}${achievement_name}${>>>}.');
            }
            else { // Age achivement
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
    
    function getTransferInfoWithOnePlayerInvolved($location_from, $location_to, $player_id_is_owner_from, $bottom_to, $you_must, $player_must, $player_name, $number, $cards, $targetable_players) {
        // Creation of the message
        if ($location_from == $location_to && $location_from == 'board') { // Used only for Self service
            $message_for_player = clienttranslate('{You must} choose {number} other top {card} from your board');
            $message_for_others = clienttranslate('{player must} choose {number} other top {card} from his board');            
        } else if ($targetable_players !== null) { // Used when several players can be targeted
            switch($location_from . '->' . $location_to) {
            case 'score->deck':
                $message_for_player = clienttranslate('{You must} return {number} {card} from the score pile of {targetable_players}');
                $message_for_others = clienttranslate('{player must} return {number} {card} from the score pile of {targetable_players}');
                break;
                
            case 'board->deck':
                $message_for_player = clienttranslate('{You must} return {number} top {card} from the board of {targetable_players}');
                $message_for_others = clienttranslate('{player must} return {number} top {card} from the board of {targetable_players}');
                break;

            case 'board->score':
                $message_for_player = clienttranslate('{You must} transfer {number} top {card} from the board of {targetable_players} to your score pile');
                $message_for_others = clienttranslate('{player must} transfer {number} top {card} from the board of {targetable_players} to his score pile');
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
                    $message_for_player = clienttranslate('{You must} return {number} {card} to the available achievements');
                    $message_for_others = clienttranslate('{player must} return {number} {card} to the available achievements');
                } else {
                    $message_for_player = clienttranslate('{You must} claim {number} {card} from the available achievements');
                    $message_for_others = clienttranslate('{player must} claim {number} {card} from the available achievements');
                }
                break;
            case 'hand->deck':
                $message_for_player = clienttranslate('{You must} return {number} {card} from your hand');
                $message_for_others = clienttranslate('{player must} return {number} {card} from his hand');
                break;
            case 'hand->board':
                if ($bottom_to) {
                    $message_for_player = clienttranslate('{You must} tuck {number} {card} from your hand');
                    $message_for_others = clienttranslate('{player must} tuck {number} {card} from his hand');
                }
                else {
                    $message_for_player = clienttranslate('{You must} meld {number} {card} from your hand');
                    $message_for_others = clienttranslate('{player must} meld {number} {card} from his hand');
                }
                break;
            case 'hand->score':
                $message_for_player = clienttranslate('{You must} score {number} {card} from your hand');
                $message_for_others = clienttranslate('{player must} score {number} {card} from his hand');
                break;
            case 'hand->revealed':
                $message_for_player = clienttranslate('{You must} reveal {number} {card} from your hand');
                $message_for_others = clienttranslate('{player must} reveal {number} {card} from his hand');
                break;
            case 'hand->achievements':
                $message_for_player = clienttranslate('{You must} achieve {number} {card} from your hand');
                $message_for_others = clienttranslate('{player must} achieve {number} {card} from his hand');
                break;
            case 'board->deck':
                $message_for_player = clienttranslate('{You must} return {number} top {card} from your board');
                $message_for_others = clienttranslate('{player must} return {number} top {card} from his board');
                break;
            case 'board->hand':
                $message_for_player = clienttranslate('{You must} take back {number} top {card} from your board to your hand');
                $message_for_others = clienttranslate('{player must} take back {number} top {card} from his board to his hand');
                break;
            case 'board->score':
                $message_for_player = clienttranslate('{You must} score {number} top {card} from your board');
                $message_for_others = clienttranslate('{player must} score {number} top {card} from his board');
                break;
            case 'board->achievements':
                $message_for_player = clienttranslate('{You must} achieve {number} top {card} from your board');
                $message_for_others = clienttranslate('{player must} achieve {number} top {card} from his board');
                break;
            case 'score->deck':
                $message_for_player = clienttranslate('{You must} return {number} {card} from your score pile');
                $message_for_others = clienttranslate('{player must} return {number} {card} from his score pile');
                break;
            case 'score->hand':
                $message_for_player = clienttranslate('{You must} transfer {number} {card} from your score pile to your hand');
                $message_for_others = clienttranslate('{player must} transfer {number} {card} from his score pile to his hand');
                break;
            case 'score->board':
                if ($bottom_to) {
                    $message_for_player = clienttranslate('{You must} tuck {number} {card} from your score pile');
                    $message_for_others = clienttranslate('{player must} tucks {number} {card} from his score pile');
                }
                else {
                    $message_for_player = clienttranslate('{You must} meld {number} {card} from your score pile');
                    $message_for_others = clienttranslate('{player must} meld {number} {card} from his score pile');
                }
                break;
            case 'score->achievements':
                $message_for_player = clienttranslate('{You must} achieve {number} {card} from your score pile');
                $message_for_others = clienttranslate('{player must} achieve {number} {card} from his score pile');
                break;
            case 'revealed->deck':
                $message_for_player = clienttranslate('{You must} return {number} {card} you revealed');
                $message_for_others = clienttranslate('{player must} return {number} {card} he revealed');
                break;
            case 'revealed->score':
                $message_for_player = clienttranslate('{You must} score {number} {card} you revealed');
                $message_for_others = clienttranslate('{player must} score {number} {card} he revealed');
                break;
            case 'revealed,hand->deck': // Alchemy, Physics
                $message_for_player = clienttranslate('{You must} return {number} {card} you revealed and {number} {card} in your hand');
                $message_for_others = clienttranslate('{player must} return {number} {card} he revealed and {number} {card} in his hand');
                break;
            case 'revealed,score->deck':
                $message_for_player = clienttranslate('{You must} return {number} {card} you revealed and {number} {card} from your score pile');
                $message_for_others = clienttranslate('{player must} return {number} {card} he revealed and {number} {card} from his score pile');
                break;
            case 'hand->revealed,deck': // Measurement
                $message_for_player = clienttranslate('{You must} reveal and return {number} {card} from your hand');
                $message_for_others = clienttranslate('{player must} reveal and return {number} {card} from his hand');
                break;
            case 'pile->deck': // Skyscrapers
                $message_for_player = clienttranslate('{You must} return {number} {card} from your board');
                $message_for_others = clienttranslate('{player must} return {number} {card} from his board');
                break;
            default:
                // This should not happen
                throw new BgaVisibleSystemException(self::format(self::_("Unhandled case in {function}: '{code}'"), array('function' => 'getTransferInfoWithOnePlayerInvolved()', 'code' => $location_from . '->' . $location_to)));
                break;
            }
        }
        $message_for_player = self::format($message_for_player, array('You must' => $you_must, 'number' => $number, 'card' => $cards, 'targetable_players' => $targetable_players));
        $message_for_others = self::format($message_for_others, array('player must' => $player_must, 'number' => $number, 'card' => $cards, 'targetable_players' => $targetable_players));
        
        return array(
            'message_for_player' => $message_for_player,
            'message_for_others' => $message_for_others
        );
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

            case 'board->deck':
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
            
            case 'achievements->achievements':
                $message_for_player = clienttranslate('${You} transfer a ${<}${age}${>} from your achievements to ${opponent_name}\'s achievements.');
                $message_for_opponent = clienttranslate('${player_name} transfers a ${<}${age}${>} from his achievements to ${your} achievements.');
                $message_for_others = clienttranslate('${player_name} transfers a ${<}${age}${>} from his achievements to ${opponent_name}\'s achievements.');
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
                
            case 'revealed->board': // Collaboration
                $message_for_player = clienttranslate('${You} transfer ${<}${age}${>} ${<<}${name}${>>} to your board.');
                $message_for_opponent = clienttranslate('${player_name} transfers ${<}${age}${>} ${<<}${name}${>>} to his board.');
                $message_for_others = clienttranslate('${player_name} transfers ${<}${age}${>} ${<<}${name}${>>} to his board.');
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
                $message_for_player = clienttranslate('{You must} transfer {number} {card} from your hand to {opponent_name}\'s hand');
                $message_for_opponent = clienttranslate('{player must} transfer {number} {card} from his hand to {your} hand');
                $message_for_others = clienttranslate('{player must} transfer {number} {card} from his hand to {opponent_name}\'s hand');
                break;
                
            case 'hand->score':
                $message_for_player = clienttranslate('{You must} transfer {number} {card} from your hand to {opponent_name}\'s score pile');
                $message_for_opponent = clienttranslate('{player must} transfer {number} {card} from his hand to {your} score pile');
                $message_for_others = clienttranslate('{player must} transfer {number} {card} from his hand to {opponent_name}\'s score pile');
                break;
                
            case 'hand->achievements':
                $message_for_player = clienttranslate('{You must} transfer {number} {card} from your hand to {opponent_name}\'s achievements');
                $message_for_opponent = clienttranslate('{player must} transfer {number} {card} from his hand to {your} achievements');
                $message_for_others = clienttranslate('{player must} transfer {number} {card} from his hand to {opponent_name}\'s achievements');
                break;              
                
            case 'board->board':
                $message_for_player = clienttranslate('{You must} transfer {number} top {card} from your board to {opponent_name}\'s board');
                $message_for_opponent = clienttranslate('{player must} transfer {number} top {card} from his board to {your} board');
                $message_for_others = clienttranslate('{player must} transfer {number} top {card} from his board to {opponent_name}\'s board');
                break;
            
            case 'board->hand':
                $message_for_player = clienttranslate('{You must} transfer {number} top {card} from your board to {opponent_name}\'s hand');
                $message_for_opponent = clienttranslate('{player must} transfer {number} top {card} from his board to {your} hand');
                $message_for_others = clienttranslate('{player must} transfer {number} top {card} from his board to {opponent_name}\'s hand');
                break;
                
            case 'board->score':
                $message_for_player = clienttranslate('{You must} transfer {number} top {card} from your board to {opponent_name}\'s score pile');
                $message_for_opponent = clienttranslate('{player must} transfer {number} top {card} from his board to {your} score pile');
                $message_for_others = clienttranslate('{player must} transfer {number} top {card} from his board to {opponent_name}\'s score pile');
                break;

            case 'board->achievements':
                $message_for_player = clienttranslate('{You must} transfer {number} top {card} from your board to {opponent_name}\'s achievements');
                $message_for_opponent = clienttranslate('{player must} transfer {number} top {card} from his board to {your} achievements');
                $message_for_others = clienttranslate('{player must} transfer {number} top {card} from his board to {opponent_name}\'s achievements');
                break;
    
                
            case 'score->score':
                $message_for_player = clienttranslate('{You must} transfer {number} {card} from your score pile to {opponent_name}\'s score pile');
                $message_for_opponent = clienttranslate('{player must} transfer {number} {card} from his score pile to {your} score pile');
                $message_for_others = clienttranslate('{player must} transfer {number} {card} from his score pile to {opponent_name}\'s score pile');
                break;
            
            case 'achievements->achievements':
                $message_for_player = clienttranslate('{You must} transfer {number} {card} from your achievements to {opponent_name}\'s achievements');
                $message_for_opponent = clienttranslate('{player must} transfer {number} {card} from his achievements to {your} achievements');
                $message_for_others = clienttranslate('{player must} transfer {number} {card} from his achievements to {opponent_name}\'s achievements');
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
                $message_for_player = clienttranslate('{You must} transfer {number} top {card} from {opponent_name}\'s board to your board');
                $message_for_opponent = clienttranslate('{player must} transfer {number} top {card} from {your} board to his board');
                $message_for_others = clienttranslate('{player must} transfer {number} top {card} from {opponent_name}\'s board to his board');
                break;
                
            case 'board->hand':
                $message_for_player = clienttranslate('{You must} transfer {number} top {card} from {opponent_name}\'s board to your hand');
                $message_for_opponent = clienttranslate('{player must} transfer {number} top {card} from {your} board to his hand');
                $message_for_others = clienttranslate('{player must} transfer {number} top {card} from {opponent_name}\'s board to his hand');
                break;
            
            case 'revealed->board': // Collaboration
                $message_for_player = clienttranslate('{You must} transfer {number} revealed {card} to your board.');
                $message_for_opponent = clienttranslate('{player must} transfer {number} revealed {card} to his board.');
                $message_for_others = clienttranslate('{player must} transfer {number} revealed {card} to his board.');
                break;
            
            default:
                // This should not happen
                throw new BgaVisibleSystemException(self::format(self::_("Unhandled case in {function}: '{code}'"), array('function' => 'getTransferInfoWithTwoPlayersInvolved()', 'code' => $location_from . '->' . $location_to)));
                break;
            }
        }
        
        $message_for_player = self::format($message_for_player, array('You must' => $you_must, 'number' => $number, 'card' => $cards, 'opponent_name' => $opponent_name));
        $message_for_opponent = self::format($message_for_opponent, array('player must' => $player_must, 'number' => $number, 'card' => $cards, 'your' => $your));
        $message_for_others = self::format($message_for_others, array('player must' => $player_must, 'number' => $number, 'card' => $cards, 'opponent_name' => $opponent_name));
        
        return array(
            'message_for_player' => $message_for_player,
            'message_for_opponent' => $message_for_opponent,
            'message_for_others' => $message_for_others
        );
    }
    
    function sendNotificationWithOnePlayerInvolved($message_for_player, $message_for_others, $card, $transferInfo, $progressInfo) {     
        $player_id = $transferInfo['player_id'];
        $player_name = self::getPlayerNameFromId($player_id);
        
        $info = array_merge($transferInfo, $progressInfo);
        
        // Information to attach to the involved player
        $delimiters_for_player = self::getDelimiterMeanings($message_for_player);
        $notif_args_for_player = array_merge($info, $delimiters_for_player);
        $notif_args_for_player['You'] = 'You';
        
        // Visibility for involved player
        if (array_key_exists('<<', $delimiters_for_player)) { // The player can see the verso of the card
            $notif_args_for_player['i18n'] = array('name');
            // Attach full info
            $notif_args_for_player = array_merge($notif_args_for_player, $card);
        }
        else if (array_key_exists('<<<', $delimiters_for_player))  { // Achievement, the player can't see the verso of the card
            $notif_args_for_player['i18n'] = array('achievement_name');
            $notif_args_for_player['age'] = $card['age'];
            if($card['age'] === null) {
                $notif_args_for_player['id'] = $card['id'];
                $notif_args_for_player['achievement_name'] = $card['achievement_name'];
                $notif_args_for_player['condition_for_claiming'] = $card['condition_for_claiming'];
                $notif_args_for_player['alternative_condition_for_claiming'] = $card['alternative_condition_for_claiming'];
            }
            else {
                $notif_args_for_player['achievement_name'] = self::getNormalAchievementName($card['age']);
            }
        }
        else { // The player can't see the verso of the card
            // Just attach the age
            $notif_args_for_player['age'] = $card['age'];
        }
        
        // Information to attach to others (other players and spectators)
        $delimiters_for_others = self::getDelimiterMeanings($message_for_others);
        $notif_args_for_others = array_merge($info, $delimiters_for_others);
        $notif_args_for_others['player_name'] =  $player_name; // The color in the log will be defined automatically by the system
        
        // Visibility for others
        if (array_key_exists('<<', $delimiters_for_others)) { // The others can see the verso of the card
            $notif_args_for_others['i18n'] = array('name');
            // Attach full info
            $notif_args_for_others = array_merge($notif_args_for_others, $card);
        }
        else if (array_key_exists('<<<', $delimiters_for_player)) { // Achievement, the others can't see the verso of the card
            $notif_args_for_others['i18n'] = array('achievement_name');
            $notif_args_for_others['age'] = $card['age'];
            if($card['age'] === null) {
                $notif_args_for_others['id'] = $card['id'];
                $notif_args_for_others['achievement_name'] = $card['achievement_name'];
                $notif_args_for_others['condition_for_claiming'] = $card['condition_for_claiming'];
                $notif_args_for_others['alternative_condition_for_claiming'] = $card['alternative_condition_for_claiming'];
            }
            else {
                $notif_args_for_others['achievement_name'] = self::getNormalAchievementName($card['age']);
            }
        }
        else { // The others can't see the verso of the card
            // Just attach the age
            $notif_args_for_others['age'] = $card['age'];
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
        $delimiters_for_player = self::getDelimiterMeanings($message_for_player);
        $notif_args_for_player = array_merge($info, $delimiters_for_player, $card); // The player can always see the card
        $notif_args_for_player['i18n'] = array('name');
        $notif_args_for_player['You'] =  'You';
        $notif_args_for_player['your'] = 'your';
        $notif_args_for_player['opponent_name'] =  self::getColoredText($opponent_name, $opponent_id);
        
        // Information to attach to the opponent
        $delimiters_for_opponent = self::getDelimiterMeanings($message_for_opponent);
        $notif_args_for_opponent = array_merge($info, $delimiters_for_opponent, $card); // The opponent can always see the card
        $notif_args_for_player['i18n'] = array('name');
        $notif_args_for_opponent['You'] = 'You';
        $notif_args_for_opponent['your'] = 'your';
        $notif_args_for_opponent['player_name'] =  $player_name;  // The color in the log will be defined automatically by the system
        
        // Information to attach to others (other players and spectators)
        $delimiters_for_others = self::getDelimiterMeanings($message_for_others);
        $notif_args_for_others = array_merge($info, $delimiters_for_others);
        $notif_args_for_others['player_name'] =  $player_name; // The color in the log will be defined automatically by the system
        $notif_args_for_others['opponent_name'] =  self::getColoredText($opponent_name, $opponent_id);
        
        // Visibility for others
        if (array_key_exists('<<', $delimiters_for_others)) { // The others can see the verso of the card
            $notif_args_for_others['i18n'] = array('name');
            // Attach full info
            $notif_args_for_others = array_merge($notif_args_for_others, $card);
        }
        else { // The others can't see the verso of the card
            // Just attach the age
            $notif_args_for_others['age'] = $card['age'];
        }
        
        self::notifyPlayer($player_id, "transferedCard", $message_for_player, $notif_args_for_player);
        self::notifyPlayer($opponent_id, "transferedCard", $message_for_opponent, $notif_args_for_opponent);
        self::notifyAllPlayersBut(array($player_id, $opponent_id), "transferedCard", $message_for_others, $notif_args_for_others);
    }
    
    /** Checking system for special achievements **/
    function getPlayerPriorityForSpecialAchievements($player_id, $opponent_id) {
        $players = self::getCollectionFromDB("SELECT player_no, player_id FROM player");
        $players_nb = count($players);
        
        $launcher_id = self::getGameStateValue('active_player');
        $current_id = $launcher_id;
        $current_no =  self::getUniqueValueFromDB(self::format("SELECT player_no FROM player WHERE player_id={launcher_id}", array('launcher_id' => $launcher_id)));
        
        while(true) {
            // Check if the current player is one of the two
            if ($current_id == $player_id) {
                // The active player has priority
                return array($player_id, $opponent_id);
            }
            if ($current_id == $opponent_id) {
                // The opponent player has priority
                return array($opponent_id, $player_id);
            }
            
            // Player not found yet: find next one
            if ($current_no == $players_nb) { // End of table reached, go back to the top
                $current_no = 1;
            }
            else { // Next row
                $current_no = $current_no + 1;
            }
            $current_id = $players[$current_no]['player_id'];
        }
    }
    
    function checkForSpecialAchievements($player_id, $wonder_included) { // Check if the player gather the conditions to get a special achievement. Do the transfer if he does.
        $achievements_to_test = $wonder_included ? array(105, 106, 107, 108, 109) : array(105, 106, 108, 109);
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
                $ressource_counts = self::getPlayerRessourceCounts($player_id);
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
                    self::trace('EOG bubbled but suspended from self::checkForSpecialAchievements');
                    $end_of_game = true;
                    continue; // But the other achievements must be checked as well before ending
                }
            }
        }
        // All special achievements have been checked
        if ($end_of_game) { // End of game has been detected
            self::trace('EOG bubbled from self::checkForSpecialAchievements');
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
    
    /** Notification system for splay **/
    function getSplayDirectionInfo($you_must, $player_must, $selectable_colors, $colors, $splay_direction, $can_pass, $can_stop) {
        $splay_direction = self::getSplayDirectionInClear($splay_direction);
        
        if ($colors === null)  { // 5 colors
            $message_for_player = self::format($clienttranslate('{You must} splay any one color of your cards {splay_direction}'), array('You must' => $you_must, 'splay_direction' => $splay_direction));
            $message_for_others = self::format($clienttranslate('{player must} splay any one color of his card cards {splay_direction}'), array('You must' => $you_must, 'splay_direction' => $splay_direction));
        }
        else {
            $message_for_player = self::format($clienttranslate('{You must} splay your {color} cards {splay_direction}'), array('You must' => $you_must, 'color' => $colors, 'splay_direction' => $splay_direction));
            $message_for_others = self::format($clienttranslate('{player must} splay his {color} cards {splay_direction}'), array('You must' => $you_must, 'color' => $colors, 'splay_direction' => $splay_direction));
        }
        
        if ($can_pass) {
            $can_pass = " " . clienttranslate("or pass");
            $message_for_player .= $can_pass;
            $message_for_others .= $can_pass;
        }
        else if ($can_stop) {
            $can_stop = " " . clienttranslate("or stop");
            $message_for_player .= $can_stop;
            $message_for_others .= $can_stop;
        }
        $message_for_player .= clienttranslate(":");
        $message_for_others .= clienttranslate(":");
        
        return array(
            'selectable_colors' => $selectable_colors,
            'message_for_player' => $message_for_player,
            'message_for_others' => $message_for_others
        );
    }
    
    function notifyForSplay($player_id, $color, $splay_direction, $force_unsplay) {        
        if ($splay_direction == 0 && !$force_unsplay) { // Unsplay event
            $color_in_clear = self::getColorInClear($color);
            
            self::notifyPlayer($player_id, 'splayedPile', clienttranslate('${Your} ${colored} pile is reduced to one card: it looses its splay.'), array(
                'i18n' => array('colored'),
                'Your' => 'Your',
                'colored' => $color_in_clear,
                'player_id' => $player_id,
                'color' => $color,
                'splay_direction' => $splay_direction
            ));
            
            self::notifyAllPlayersBut($player_id, 'splayedPile', clienttranslate('${player_name}\'s ${colored} pile is reduced to one card: it looses its splay.'), array(
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
        $new_ressource_counts = self::updatePlayerRessourceCounts($player_id);
        
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
                $winners[] = $player_id; // Far most frequent case: $winners has only one element, but who knows... (extremely unlikely but still possible)
            }
            else if ($player['player_score'] > $number_of_achievements_winner) { // One player got more achievements than necessary... (extremely unlikely but still possible)
                $number_of_achievements_winner = $player['player_score'];
                $winners = array($player_id);
            }
        }
        
        foreach ($winners as $player_id) {
            if ($number_of_achievements_winner == $number_of_achievements_needed_to_win) { // Far most frequent case
                if (self::decodeGameType(self::getGameStateValue('game_type')) == 'individual') {
                    self::notifyAllPlayersBut($player_id, "log", clienttranslate('END OF GAME BY ACHIEVEMENTS: ${player_name} has got ${n} achievements. He wins!'), array(
                        'n' => $number_of_achievements_winner,
                        'player_name' => self::getPlayerNameFromId($player_id)
                    ));
                    
                    self::notifyPlayer($player_id, "log", clienttranslate('END OF GAME BY ACHIEVEMENTS: ${You} have got ${n} achievements. You win!'), array(
                        'n' => $number_of_achievements_winner,
                        'You' => 'You'
                    ));
                }
                else { // Team game
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
            else { // Extremely unlikely
                if (self::decodeGameType(self::getGameStateValue('game_type')) == 'individual') {
                    self::notifyAllPlayersBut($player_id, "log", clienttranslate('END OF GAME BY ACHIEVEMENTS: ${player_name} has got ${n} achievements (more than necessary!!!). He wins!'), 
                    array(
                        'n' => $number_of_achievements_winner,
                        'player_name' => self::getPlayerNameFromId($player_id)
                    ));
                    
                    self::notifyPlayer($player_id, "log", clienttranslate('END OF GAME BY ACHIEVEMENTS: ${You} have got ${n} achievements (more than necessary!!!). You win!'), array(
                        'n' => $number_of_achievements_winner,
                        'You' => 'You'
                    ));
                }
                else { // Team game
                    $teammate_id = self::getPlayerTeammate($player_id);
                    $winning_team = array($player_id, $teammate_id);
                    self::notifyAllPlayersBut($winning_team, "log", clienttranslate('END OF GAME BY ACHIEVEMENTS: The other team has got ${n} achievements (more than necessary!!!). They win!'), array(
                        'n' => $number_of_achievements_winner
                    ));
                    
                    foreach($winning_team as $player_id) {
                        self::notifyPlayer($player_id, "log", clienttranslate('END OF GAME BY ACHIEVEMENTS: Your team has got ${n} achievements (more than necessary!!!). You win!'), array(
                            'n' => $number_of_achievements_winner
                        ));
                    }
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
        }
        else { // Team play
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
        if (self::decodeGameType(self::getGameStateValue('game_type')) == 'individual') {
            self::notifyAllPlayersBut($player_id, "log", clienttranslate('END OF GAME BY DOGMA: ${player_name} meets the victory condition. He wins!'), array(
                'player_name' => self::getPlayerNameFromId($player_id)
            ));
            
            self::notifyPlayer($player_id, "log", clienttranslate('END OF GAME BY DOGMA: ${You} meet the victory condition. You win!'), array(
                'You' => 'You'
            ));
        }
        else { // Team play
            $teammate_id = self::getPlayerTeammate($player_id);
            $winning_team = array($player_id, $teammate_id);
            $dogma_card_id = self::getGameStateValue('dogma_card_id');
            
            if ($dogma_card_id == 100 /* Self service*/ || $dogma_card_id == 101 /* Globalization */) {
                self::notifyAllPlayersBut($winning_team, "log", clienttranslate('END OF GAME BY DOGMA: The other team meets the victory condition. They win!'), array());
                
                self::notifyPlayer($player_id, "log", clienttranslate('END OF GAME BY DOGMA: Your team meets the victory condition. You win!'), array());
                
                self::notifyPlayer($teammate_id, "log", clienttranslate('END OF GAME BY DOGMA: Your team meets the victory condition. You win!'), array());
            }
            else {
                self::notifyAllPlayersBut($winning_team, "log", clienttranslate('END OF GAME BY DOGMA: ${player_name} meets the victory condition. The other team wins!'), array(
                    'player_name' => self::getPlayerNameFromId($player_id)
                ));
                
                self::notifyPlayer($player_id, "log", clienttranslate('END OF GAME BY DOGMA: ${You} meet the victory condition. Your team wins!'), array(
                    'You' => 'You'
                ));
                
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
    
    /** Sharing bonus **/
    function checkIfSharingBonus() {
        // This function is called when something changed in the game
        
        $current_effect_type = self::getGameStateValue('current_effect_type');
        if ($current_effect_type == -1) { // Not in dogma
            // Nothing to be done
            return;
        }
        
        // Mark that the player under effect made a change in the game
        $current_player_under_dogma_effect = self::getGameStateValue('current_player_under_dogma_effect');
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
    
    function markExecutingPlayer($player_id) { // This function is used to mark a player when he executed a dogma card effect with true consequences
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
        return self::format("<span title='{age}' class='square N age_{age}'></span>", array('age' => $age));
    }
    
    function notifyDogma($card) {
        $player_id = self::getActivePlayerId();
        $icon = $card['dogma_icon'];
        
        $message_for_player = clienttranslate('${You} activate the dogma of ${<}${age}${>} ${<<}${name}${>>} with ${[}${icon}${]} as featured icon.');
        $message_for_others = clienttranslate('${player_name} activates the dogma of ${<}${age}${>} ${<<}${name}${>>} with ${[}${icon}${]} as featured icon.');
        
        $delimiters_for_player = self::getDelimiterMeanings($message_for_player);
        $delimiters_for_others = self::getDelimiterMeanings($message_for_others);
        
        self::notifyPlayer($player_id, 'log', $message_for_player, array_merge($card, $delimiters_for_player, array(
            'i18n' => array('name'),
            'You' => 'You',
            'icon' => $icon
        )));
        
        self::notifyAllPlayersBut($player_id, 'log', $message_for_others, array_merge($card, $delimiters_for_others, array(
            'i18n' => array('name'),
            'player_name' => self::getPlayerNameFromId($player_id),
            'icon' => $icon
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
    
    function notifyUndoable() {
        if (self::getGameStateValue('splay_direction') == -1) {
            if (self::getGameStateValue('n') == 0) {
                $message = clienttranslate("No card matches the criteria of the effect.");
            }
            else {
                $message = clienttranslate("No more card matches the criteria of the effect.");
            }
        }
        else {
            $message = clienttranslate("No pile matches the criteria of the effect for splaying.");
        }
        self::notifyGeneralInfo($message);
    }
    
    function notifyUndoableInTotality() {
        self::notifyGeneralInfo(clienttranslate("There are not enough cards to fulfill the condition."));
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
        $card = self::getNonEmptyObjectFromDB(self::format("
                SELECT * FROM card WHERE id = {id}
            ",
                array('id' => $id)
        ));
        return self::attachTextualInfo($card);
    }
    
    function getCardInfoFromPosition($owner, $location, $age, $position) {
        /**
            Get all information from the database about the card indicated by its position
        **/
        $card = self::getObjectFromDB(self::format("
                SELECT * FROM card WHERE owner = {owner} AND location = '{location}' AND age = {age} AND position = {position}
            ",
                array('owner' => $owner, 'location' => $location, 'age' => $age, 'position' => $position)
        ));
        if ($card === null) { // No card has this positional info
            return null;
        }
        return self::attachTextualInfo($card);
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
        return strcasecmp($card_1['name'], $card_2['name']) < 0;
    }
    
    function getDeckTopCard($age) {
        /**
            Get all information of the card to be drawn from the deck of the age indicated, which includes:
                -intrisic properties,
                -owner, location and position
        **/
        
        return self::attachTextualInfo(self::getNonEmptyObjectFromDB(self::format("
            SELECT
                *
            FROM
                card
            WHERE
                location = 'deck' AND
                age = {age} AND
                position = (SELECT MAX(position) FROM card WHERE location = 'deck' AND age = {age})
        ",
            array('age' => $age)
        )));
    }
    
    function getAgeToDrawIn($player_id, $age_min=null) {
        if($age_min === null){
            // $age_min is the maximum age on player board
            $age_min = self::getMaxAgeOnBoardTopCards($player_id);
        }
        if ($age_min < 1) {
            $age_min = 1;
        }
    
        $deck_count = self::countCardsInLocation(0, 'deck', true);
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
    
    function getOrCountCardsInLocation($count, $owner, $location, $ordered_by_age, $ordered_by_color) {
        /**
            Get ($count is false) or count ($count is true) all the cards in a particular location, sorted by position. The result can be first grouped or ordered by age (for deck or hand) or color (for board) if needed
        **/
        
        $type_of_result = $count ? "COUNT(*)" : "*";
        $opt_order_by = $count ? "" : "ORDER BY position";
        $getFromDB = $count ? 'getUniqueValueFromDB' : 'getObjectListFromDB'; // If we count, we want to get an unique value, else, we want to get a list of cards
        
        if(!$ordered_by_age && !$ordered_by_color) {
            return self::$getFromDB(self::format("
                SELECT
                    {type_of_result}
                FROM
                    card
                WHERE
                    owner = {owner} AND
                    location = '{location}'
                {opt_order_by}
            ",
                array('type_of_result' => $type_of_result, 'owner' => $owner, 'location' => $location, 'opt_order_by' => $opt_order_by)
           ));
        }
                                                                    
        if ($ordered_by_age) {
            $key = 'age';
            $num_min = 1;
            $num_max = 10;
        }
        else if ($ordered_by_color) {
            $key = 'color';
            $num_min = 0;
            $num_max = 4;
        }
        
        $result = array();
        
        for($value = $num_min; $value <= $num_max; $value++) {
            $result[$value] = self::$getFromDB(self::format("
                SELECT
                    {type_of_result}
                FROM
                    card
                WHERE
                    owner = {owner} AND
                    location = '{location}' AND
                    {key} = {value}
                {opt_order_by}
            ",
                array('type_of_result' => $type_of_result, 'owner' => $owner, 'location' => $location, 'key' => $key, 'value' => $value, 'opt_order_by' => $opt_order_by)
           ));
        }
        return $result;
    }
    
    function getAllBoards($players) {
        $result = array();
        foreach($players as $player_id => $player) {
            $result[$player_id] = self::getCardsInLocation($player_id, 'board', false, true);
        }
        return $result;
    }

    function isTopBoardCard($card) {
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
    
    function getCardsInLocation($owner, $location, $ordered_by_age=false, $ordered_by_color=false) {
        /**
            Get all the cards in a particular location, sorted by position. The result can be first ordered by age (for deck or hand) or color (for board) if needed
        **/
        $cards = self::getOrCountCardsInLocation(false, $owner, $location, $ordered_by_age, $ordered_by_color);
        if ($ordered_by_age || $ordered_by_color) {
            foreach($cards as $key => &$card_list) {
                $card_list = self::attachTextualInfoToList($card_list);
            }
        }
        else {
            $cards = self::attachTextualInfoToList($cards);
        }
        return $cards;
    }
    
    function countCardsInLocation($owner, $location, $ordered_by_age=false, $ordered_by_color=false) {
        /**
            Count all the cards in a particular location, sorted by position. The result can be first grouped and ordered by age (for deck or hand) or color (for board) if needed
        **/
        return self::getOrCountCardsInLocation(true, $owner, $location, $ordered_by_age, $ordered_by_color);
    }
    
    function getTopCardOnBoard($player_id, $color) {
        /**
        Get the top card of specified color
        (null if the player have no card on his board)
        **/
        return self::attachTextualInfo(self::getObjectFromDB(self::format("
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
        )));
    }

    function getTopCardsOnBoard($player_id) {
        /**
        Get all of the top cards on a player board
        (null if the player has no cards on his board)
        **/
        return self::attachTextualInfoToList(self::getCollectionFromDb(self::format("
                SELECT
                    *
                FROM
                    card
                WHERE
                    card.owner = {player_id} AND
                    card.location = 'board' AND
                    card.position = (
                        SELECT
                            MAX(position) AS position
                        FROM
                            card
                        WHERE
                            owner = {player_id} AND
                            location = 'board' AND
                            color = card.color
                    )
        ",
            array('player_id' => $player_id)
        )));
    }
    
    function getIfTopCard($id) {
        /**
        Returns the card if card is a top card.
        null if isn't present as a top card
        **/
        return self::attachTextualInfo(self::getObjectFromDB(self::format("
            SELECT
                *
            FROM
                card
            WHERE
                card.id = {id} AND
                card.location = 'board' AND
                card.position = (
                    SELECT
                        MAX(position) AS position
                    FROM
                        card
                    WHERE
                        owner = card.owner AND
                        location = 'board' AND
                        color = card.color
                )
            ",
            array('id' => $id)
        )));
    }
    
    function getBottomCardOnBoard($player_id, $color) {
        /**
        Get the bottom card of specified color
        (null if the player have no card on his board)
        **/
        return self::attachTextualInfo(self::getObjectFromDB(self::format("
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
        )));
    }
    
    function getMaxAgeOnBoardTopCards($player_id) {
        /**
        Get the age the player is in, that is to say, the maximum age that can be found on his board top cards
        (0 if the player have no card on his board)
        **/
        
        // Get the max of the age matching the position defined in the sub-request
        return self::getUniqueValueFromDB(self::format("
            SELECT
                COALESCE(MAX(a.age), 0)
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
    
    function getMinAgeOnBoardTopCardsWithIcon($player_id, $icon) {
        /**
        Get the minimum age of the top cards with a particular icon
        (0 if the player have no card on his board)
        **/
        
        
        // Get the max of the age matching the position defined in the sub-request
        return self::getUniqueValueFromDB(self::format("
            SELECT
                COALESCE(MIN(a.age), 0)
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
                COALESCE(MAX(a.age), 0)
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
                COALESCE(MAX(a.age), 0)
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
         return $card !== null && ($card['spot_1'] == $icon || $card['spot_2'] == $icon || $card['spot_3'] == $icon || $card['spot_4'] == $icon);
    }
    
    function boardPileHasRessource($player_id, $color, $icon) {
        $board = self::getCardsInLocation($player_id, 'board', false, true);
        $pile = $board[$color];
        if (count($pile) == 0) { // No card of that color
            return false;
        }
        $top_card = $pile[count($pile)-1];
        if (self::hasRessource($top_card, $icon)) { // The top card of that pile has that icon
            return true;
        }
        $splay_direction = $top_card['splay_direction'];
        if ($splay_direction == 0) { // Unsplayed
            return false;
        }
        // Since the pile is not unsplayed, it has at least two cards
        for($i=0; $i<count($pile)-1; $i++) {
            $card = $pile[$i];
            if($splay_direction == 1 && $card['spot_4'] == $icon || $splay_direction == 2 && ($card['spot_1'] == $icon || $card['spot_2'] == $icon) || $splay_direction == 3 && ($card['spot_2'] == $icon || $card['spot_3'] == $icon || $card['spot_4'] == $icon)) {
                return true;
            }
        }
        return false;
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
        if ($player['player_score'] == self::getGameStateValue('number_of_achievements_needed_to_win')) {
            self::setGameStateValue('game_end_type', 0);
            self::trace('EOG bubbled from self::incrementBGAScore');
            throw new EndOfGame();
        }
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
    
    function getPlayerRessourceCounts($player_id) {
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
        for($icon=1; $icon<=6; $icon++) {
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
    
    function updatePlayerRessourceCounts($player_id) {     
        self::DbQuery("
            INSERT INTO
                base (icon)
            VALUES
                (1), (2), (3), (4), (5), (6)
        ");
        
        self::DbQuery(self::format("
            INSERT INTO card_with_top_card_indication
                SELECT
                    a.*,
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
        
        return self::getPlayerRessourceCounts($player_id);
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
        $players = self::loadPlayersBasicInfos();
        return $players[$player_id]['player_name'];
    }
    
    function getPlayerColorFromId($player_id) {
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
        $unique_non_demand_effect = $card['non_demand_effect_2'] === null;
        
        return $current_effect_type == 0 ? clienttranslate('I demand effect') :
               ($current_effect_type == 2 ? clienttranslate('I compel effect') :
               ($unique_non_demand_effect ? clienttranslate('non-demand effect') :
               ($current_effect_number == 1 ? clienttranslate('1<sup>st</sup> non-demand effect') :
               ($current_effect_number == 2 ? clienttranslate('2<sup>nd</sup> non-demand effect') : clienttranslate('3<sup>rd</sup> non-demand effect')))));
    }
                                  
    function getFirstPlayerUnderEffect($dogma_effect_type, $launcher_id) {
        // I demand
        if ($dogma_effect_type == 0) {
            $player_query = "stronger_or_equal = FALSE";
        // I compel
        } else if ($dogma_effect_type == 2) {
            $player_query = self::format("stronger_or_equal = TRUE AND player_id != {launcher_id}", array('launcher_id' => $launcher_id));
        // Non-demand
        } else {
            $player_query = "stronger_or_equal = TRUE";
        }
        return self::getUniqueValueFromDB(self::format("
            SELECT
                player_id
            FROM
                player
            WHERE
                {player_query}
                AND player_no_under_effect = 1
        ",
            array('player_query' => $player_query)
       ));
    }
       
    function getNextPlayerUnderEffect($dogma_effect_type, $player_id, $launcher_id) { // null if no player is found
        // I demand
        if ($dogma_effect_type == 0) {
            $player_query = "stronger_or_equal = FALSE";
        // I compel
        } else if ($dogma_effect_type == 2) {
            $player_query = self::format("stronger_or_equal = TRUE AND player_id != {launcher_id}", array('launcher_id' => $launcher_id));
        // Non-demand
        } else {
            $player_query = "stronger_or_equal = TRUE";
        }       
        return self::getUniqueValueFromDB(self::format("
            SELECT
                player_id
            FROM
                player
            WHERE
                {player_query}
                AND player_no_under_effect = (
                    SELECT
                        player_no_under_effect
                    FROM
                        player
                    WHERE
                        player_id = {player_id}
                ) + 1
        ",
            array('player_query' => $player_query, 'player_id' => $player_id)
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
    
    function getColoredText($text, $player_id) {
        $color = self::getPlayerColorFromId($player_id);
        return "<span style='font-weight: bold; color:#".$color.";'>".$text."</span>";
    }
    
    /** Execution of actions authorized by server **/    
    function executeDraw($player_id, $age_min = null, $location_to = 'hand', $bottom_to=false) { // Execute a draw. If $age_min is null, draw in the deck according to the board of the player, else, draw a card of the specified value or more, according to the rules
        $age_to_draw = self::getAgeToDrawIn($player_id, $age_min);
        
        if ($age_to_draw > 10) {
            // Attempt to draw a card above 10 : end of the game by score
            self::setGameStateValue('game_end_type', 1);
            self::setGameStateInitialValue('player_who_could_not_draw', $player_id);
            self::trace('EOG bubbled from self::executeDraw (age > 10');
            throw new EndOfGame();
        }
        
        $card = self::getDeckTopCard($age_to_draw);
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
    
    function setSelectionRange($options) {
        $possible_special_types_of_choice = array('choose_opponent', 'choose_opponent_with_fewer_points', 'choose_value', 'choose_color', 'choose_two_colors', 'choose_rearrange', 'choose_yes_or_no');
        foreach($possible_special_types_of_choice as $special_type_of_choice) {
            if (array_key_exists($special_type_of_choice, $options)) {
                self::setGameStateValue('special_type_of_choice', self::encodeSpecialTypeOfChoice($special_type_of_choice));
                self::setGameStateValue('can_pass', $options['can_pass'] ? 1 : 0); 
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
                }
                else if ($value === 'any opponent') {
                    $value = -3;
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
        if (!array_key_exists('color', $rewritten_options)) {
            $rewritten_options['color'] = array(0, 1, 2, 3, 4);
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
        if (!array_key_exists('bottom_to', $rewritten_options)) {
            $rewritten_options['bottom_to'] = false;
        }
        if (!array_key_exists('score_keyword', $rewritten_options)) {
            $rewritten_options['score_keyword'] = false;
        }
        if (!array_key_exists('require_achievement_eligibility', $rewritten_options)) {
            $rewritten_options['require_achievement_eligibility'] = false;
        }
        if (!array_key_exists('splay_direction', $rewritten_options)) {
             $rewritten_options['splay_direction'] = -1;
        }
        else { // This is a choice for splay
            $rewritten_options['owner_from'] = $player_id;
            $rewritten_options['location_from'] = 'board'; // Splaying is equivalent as selecting a board card, by design
            $number_of_cards_on_board = self::countCardsInLocation($player_id, 'board', false, true);
            $splay_direction = $rewritten_options['splay_direction'];
            $colors = array();
            
            foreach ($rewritten_options['color'] as $color) {
                // Check if the piles have at least 2 cards
                if ($number_of_cards_on_board[$color] < 2) {
                    // This color can't be chosen for splay since the pile is one card or less
                    continue;
                }
                
                // Check if the pile is not already splayed in the same direction
                if (self::getCurrentSplayDirection($player_id, $color) == $splay_direction) {
                    // This color can't be chosen for splay since the pile is already splayed in the same direction
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
            case 'bottom_to':
            case 'score_keyword':
            case 'solid_constraint':
            case 'require_achievement_eligibility':
                $value = $value ? 1 : 0;
                break;
            case 'location_from':
            case 'location_to':
                $value = self::encodeLocation($value);
                break;
            case 'color':
                self::setGameStateValueFromArray('color_array', $value);
                break;
            }
            if ($key <> 'color') {
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
        }
        else if ($owner_from == -3) { // Any opponent
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
        }
        else {
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
        
        // Condition for color
        $color_array = self::getGameStateValueAsArray('color_array');
        $condition_for_color = count($color_array) == 0 ? "FALSE" : "color IN (".join($color_array, ',').")";
        
        // Condition for icon
        $with_icon = self::getGameStateValue('with_icon');
        $without_icon = self::getGameStateValue('without_icon');
        if ($with_icon > 0) {
            $condition_for_icon = self::format("AND (spot_1 = {icon} OR spot_2 = {icon} OR spot_3 = {icon} OR spot_4 = {icon})", array('icon' => $with_icon));
        }
        else if ($without_icon > 0) {
            $condition_for_icon = self::format("AND spot_1 <> {icon} AND spot_2 <> {icon} AND spot_3 <> {icon} AND spot_4 <> {icon}", array('icon' => $without_icon));
        }
        else {
            $condition_for_icon = "";
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
        if ($not_id != -2) { // Only used by Fission and Self service
            $condition_for_excluding_id = self::format("AND id <> {not_id}", array('not_id' => $not_id));
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
                    position = position_of_active_card AND
                    {condition_for_color}
                    {condition_for_icon}
                    {condition_for_requiring_id}
                    {condition_for_excluding_id}
            ",
                array(
                    'condition_for_owner' => $condition_for_owner,
                    'condition_for_location' => $condition_for_location,
                    'condition_for_age' => $condition_for_age,
                    'condition_for_claimable_ages' => $condition_for_claimable_ages,
                    'condition_for_color' => $condition_for_color,
                    'condition_for_icon' => $condition_for_icon,
                    'condition_for_requiring_id' => $condition_for_requiring_id,
                    'condition_for_excluding_id' => $condition_for_excluding_id
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
                    {condition_for_color}
                    {condition_for_icon}
                    {condition_for_requiring_id}
                    {condition_for_excluding_id}
            ",
                array(
                    'condition_for_owner' => $condition_for_owner,
                    'condition_for_location' => $condition_for_location,
                    'condition_for_age' => $condition_for_age,
                    'condition_for_claimable_ages' => $condition_for_claimable_ages,
                    'condition_for_color' => $condition_for_color,
                    'condition_for_icon' => $condition_for_icon,
                    'condition_for_requiring_id' => $condition_for_requiring_id,
                    'condition_for_excluding_id' => $condition_for_excluding_id
                )
            ));
        }
        
        //return self::DbAffectedRow(); // This does not seem to work all the time...
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
        $nested_id_1 = self::getGameStateValue('nested_id_1');
        $card_id = $nested_id_1 == -1 ? self::getGameStateValue('dogma_card_id') : $nested_id_1;
        $card = self::getCardInfo($card_id);
        
        $current_effect_type = $nested_id_1 == -1 ? self::getGameStateValue('current_effect_type') : 1 /* Non-demand effects only*/;
        $current_effect_number = $nested_id_1 == -1 ?  self::getGameStateValue('current_effect_number') : self::getGameStateValue('nested_current_effect_number_1');
        
        $card_names = self::getDogmaCardNames();
        
        $args = array_merge(array(
            'qualified_effect' => self::qualifyEffect($current_effect_type, $current_effect_number, $card),
            'card_name' => 'card_name',
            'JSCardEffectQuery' => self::getJSCardEffectQuery($card_id, $card['age'], $current_effect_type, $current_effect_number)
        ), $card_names);
        
        $args['i18n'][] = 'qualified_effect';
        $args['i18n'][] = 'card_name';
        
        return $args;
    }
    
    function getArgForPlayerUnderDogmaEffect() {
        $player_id = self::getGameStateValue('current_player_under_dogma_effect');
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
        $launcher_id = self::getGameStateValue('active_player');
        $player_id = self::getGameStateValue('current_player_under_dogma_effect');
        
        $dogma_card_id = self::getGameStateValue('dogma_card_id');
        $dogma_card = self::getCardInfo($dogma_card_id);
        
        $card_names = array();
        
        $card_names['card_0'] = $dogma_card['name'];
        $card_names['ref_player_0'] = $launcher_id;
        
        $i18n = array('card_0');
        
        //$arrow = "&rarr;";
        
        $j = 1;
        for($i=9; $i>=1; $i--) {
            $nested_id = self::getGameStateValue('nested_id_'.$i);
            if ($nested_id == -1) {
                continue;
            }
            
            $card = self::getCardInfo($nested_id);
            
            $card_names['card_'.$j] = $card['name'];
            $card_names['ref_player_'.$j] = $player_id;
            $i18n[] = 'card_'.$j;
            $j++;
        }
        $card_names['i18n'] = $i18n;
        return $card_names;
    }
    
    function getJSCardId($card_id, $card_age) {
        return "#item_" . $card_id. "__age_" . $card_age . "__M__card";
    }
    
    function getJSCardEffectQuery($card_id, $card_age, $effect_type, $effect_number) {
        return self::getJSCardId($card_id, $card_age) . " ." . ($effect_type == 1 ? "non_demand" : "i_demand") . "_effect_" . $effect_number;
    }
    
    /** Nested dogma excution management system: FIFO stack **/
    function checkAndPushCardIntoNestedDogmaStack($card) {
        $player_id = self::getGameStateValue('current_player_under_dogma_effect');
        if ($card['non_demand_effect_1'] === null) { // There is no non-demand effect
            self::notifyGeneralInfo(clienttranslate('There is no non-demand effect on this card.'));
            // No exclusive execution: do nothing
            return;
        }
        if ($card['non_demand_effect_2'] !== null) { // There are 2 or 3 non-demand effects
                self::notifyPlayer($player_id, 'log', clienttranslate('${You} execute the non-demand effects of this card.'), array('You' => 'You'));
                self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} executes the non-demand effects of this card.'), array('player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id)));
            }
        else { // There is a single non-demand effect
                self::notifyPlayer($player_id, 'log', clienttranslate('${You} execute the non-demand effect of this card.'), array('You' => 'You'));
                self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} executes the non-demand effect of this card.'), array('player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id)));
        }
        self::pushCardIntoNestedDogmaStack($card);
    }
    
    function pushCardIntoNestedDogmaStack($card) {
        self::trace('nesting++');
        for($i=8; $i>=1; $i--) {
            self::setGameStateValue('nested_id_'.($i+1), self::getGameStateValue('nested_id_'.$i));
            self::setGameStateValue('nested_current_effect_number_'.($i+1), self::getGameStateValue('nested_current_effect_number_'.$i));
        }
        self::setGameStateValue('nested_id_1', $card['id']);
        self::setGameStateValue('nested_current_effect_number_1', 0);
    }
    
    function popCardFromNestedDogmaStack() {
        self::trace('nesting--');
        for($i=1; $i<=8; $i++) {
            self::setGameStateValue('nested_id_'.$i, self::getGameStateValue('nested_id_'.($i+1)));
            self::setGameStateValue('nested_current_effect_number_'.$i, self::getGameStateValue('nested_current_effect_number_'.($i+1)));
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
            // The player is cheating...
            throw new BgaUserException(self::_("You do not have this card in hand [Press F5 in case of troubles]"));
        }
        
        // No cheating here
        
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
            // The player is cheating...
            throw new BgaUserException(self::_("You do not have this card in hand [Press F5 in case of troubles]"));
        }
        
        // No cheating here
        
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
    
    function achieve($age) {
        // Check that this is the player's turn and that it is a "possible action" at this game state
        self::checkAction('achieve');
        $player_id = self::getActivePlayerId();
        
        // Check if the player really meet the conditions to achieve that card
        $card = self::getObjectFromDB("SELECT * FROM card WHERE location = 'achievements' AND age = " . $age);
        if ($card['owner'] != 0) {
            // The player is cheating...
            throw new BgaUserException(self::_("This achievement has already been claimed [Press F5 in case of troubles]"));
        }
        
        $age_max = self::getMaxAgeOnBoardTopCards($player_id);
        $player_score = self::getPlayerScore($player_id);
        
        // Rule: to achieve the age X, the player has to have a top card of his board of age >= X and 5*X points in his score pile
        if ($age > $age_max || $player_score < 5*$age) {
            // The player is cheating...
            throw new BgaUserException(self::_("You do not meet the conditions to claim this achievement [Press F5 in case of troubles]"));
        }
        
        // No cheating here
        
        // Stats
        if (self::getGameStateValue('has_second_action') || self::getGameStateValue('first_player_with_only_one_action') || self::getGameStateValue('second_player_with_only_one_action')) {
            self::incStat(1, 'turns_number');
            self::incStat(1, 'turns_number', $player_id);
        }
        self::incStat(1, 'actions_number');
        self::incStat(1, 'actions_number', $player_id);
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
        if (self::getGameStateValue('has_second_action') || self::getGameStateValue('first_player_with_only_one_action') || self::getGameStateValue('second_player_with_only_one_action')) {
            self::incStat(1, 'turns_number');
            self::incStat(1, 'turns_number', $player_id);
        }
        self::incStat(1, 'actions_number');
        self::incStat(1, 'actions_number', $player_id);
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

        // Check if the player has this card really in his hand
        $card = self::getCardInfo($card_id);
        
        if ($card['owner'] != $player_id || $card['location'] != "hand") {
            // The player is cheating...
            throw new BgaUserException(self::_("You do not have this card in hand [Press F5 in case of troubles]"));
        }
        
        // No cheating here
        
        // Stats
        if (self::getGameStateValue('has_second_action') || self::getGameStateValue('first_player_with_only_one_action') || self::getGameStateValue('second_player_with_only_one_action')) {
            self::incStat(1, 'turns_number');
            self::incStat(1, 'turns_number', $player_id);
        }
        self::incStat(1, 'actions_number');
        self::incStat(1, 'actions_number', $player_id);
        self::incStat(1, 'meld_actions_number', $player_id);
        
        // Execute the meld
        try {
            self::transferCardFromTo($card, $card['owner'], 'board');
        }
        catch (EndOfGame $e) {
            // End of the game: the exception has reached the highest level of code
            self::trace('EOG bubbled from self::meld');
            self::trace('playerTurn->justBeforeGameEnd');
            $this->gamestate->nextState('justBeforeGameEnd');
            return;
        }
        
        // End of player action
        self::trace('playerTurn->interPlayerTurn (meld)');
        $this->gamestate->nextState('interPlayerTurn');
    }
    
    function dogma($card_id) {
        // Check that this is the player's turn and that it is a "possible action" at this game state
        self::checkAction('dogma');
        $player_id = self::getActivePlayerId();
        
        // Check if the player has this card really on his board
        $card = self::getCardInfo($card_id);
        
        if ($card['owner'] != $player_id || $card['location'] != "board") {
            // The player is cheating...
            throw new BgaUserException(self::_("You do not have this card on board [Press F5 in case of troubles]"));
        }
        if (!self::isTopBoardCard($card)) {
            // The player is cheating...
            throw new BgaUserException(self::_("This card is not on the top of the pile [Press F5 in case of troubles]"));            
        }
        
        // No cheating here
        
        // Stats
        if (self::getGameStateValue('has_second_action') || self::getGameStateValue('first_player_with_only_one_action') || self::getGameStateValue('second_player_with_only_one_action')) {
            self::incStat(1, 'turns_number');
            self::incStat(1, 'turns_number', $player_id);
        }
        self::incStat(1, 'actions_number');
        self::incStat(1, 'actions_number', $player_id);
        self::incStat(1, 'dogma_actions_number', $player_id);
        
        self::notifyDogma($card);
        
        $dogma_icon = $card['dogma_icon'];
        $ressource_column = 'player_icon_count_' . $dogma_icon;
        
        $players = self::getCollectionFromDB(self::format("SELECT player_id, player_no, player_team, {ressource_column} FROM player", array('ressource_column' => $ressource_column)));
        $players_nb = count($players);
        
        // Compare players ressources on dogma icon_count;
        $dogma_player = $players[$player_id];
        $dogma_player_team = $dogma_player['player_team'];
        $dogma_player_ressource_count = $dogma_player[$ressource_column];
        $dogma_player_no = $dogma_player['player_no'];
        
        // Count each player ressources
        $players_ressource_count = array();
        foreach ($players as $id => $player) {
            $player_no = $player['player_no'];
            $player_ressource_count = $player[$ressource_column];
            $players_ressource_count[$player_no] = $player_ressource_count;
            $players_teams[$player_no] = $player['player_team'];
            
            self::notifyPlayerRessourceCount($id, $dogma_icon, $player_ressource_count);
        }

        $player_no = $dogma_player_no;
        $player_no_under_i_demand_effect = 0;
        $player_no_under_non_demand_effect = 0;
        
        $card_with_i_demand_effect = $card['i_demand_effect_1'] !== null && !$card['i_demand_effect_1_is_compel'];
        $card_with_i_compel_effect = $card['i_demand_effect_1'] !== null && $card['i_demand_effect_1_is_compel'];
        $card_with_non_demand_effect = $card['non_demand_effect_1'] !== null;
        
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
            
        } while($player_no != $dogma_player_no);
        
        // Write info in global variables to prepare the first effect
        self::setGameStateValue('dogma_card_id', $card_id);
        if ($card_with_i_compel_effect) {
            self::setGameStateValue('current_effect_type', 2);
        } else if ($card_with_i_demand_effect) {
            self::setGameStateValue('current_effect_type', 0);
        } else {
            self::setGameStateValue('current_effect_type', 1);
        }
        self::setGameStateValue('current_effect_number', 1);
        self::setGameStateValue('sharing_bonus', 0);
        
        // Resolve the first dogma effet of the card
        self::trace('playerTurn->dogmaEffect (dogma)');
        $this->gamestate->nextState('dogmaEffect');
    }

    function choose($card_id) {
        // Check that this is the player's turn and that it is a "possible action" at this game state
        self::checkAction('choose');
        $player_id = self::getActivePlayerId();
        
        if ($card_id == -1) {
            // The player chooses to pass or stop
            if (self::getGameStateValue('can_pass') == 0 && self::getGameStateValue('n_min') > 0) {
                // The player is cheating...
                throw new BgaUserException(self::_("You cannot pass or stop [Press F5 in case of troubles]"));
            }
            // No cheating here
            if (self::getGameStateValue('special_type_of_choice') == 0) {
                self::setGameStateValue('id_last_selected', -1);
            }
            else {
                self::setGameStateValue('choice', -2);
            }
        }
        else if (self::getGameStateValue('special_type_of_choice') != 0) {
            // The player is cheating...
            throw new BgaUserException(self::_("You cannot choose a card; you have to choose a special option [Press F5 in case of troubles]"));
        }
        else {
            // Check if the card is within the selection range
            $card = self::getCardInfo($card_id);
            
            if (!$card['selected']) {
                // The player is cheating...
                throw new BgaUserException(self::_("This card cannot be selected [Press F5 in case of troubles]"));
            }
            else if ($card['location'] <> 'board' && $card['owner'] <> $player_id) {
                // The player is cheating...
                throw new BgaUserException(self::_("You attempt to select a specific card you can't see [Press F5 in case of troubles]"));
            }
            
            // No cheating here
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
            // The player is cheating...
            throw new BgaUserException(self::_("You cannot choose a card; you have to choose a special option [Press F5 in case of troubles]"));
        }
        else if ((!in_array($owner, $players) && $owner != 0) || !in_array($location, self::getObjectListFromDB("SELECT DISTINCT location FROM card", true)) || $age < 1 || $age > 10) {
            // The player is cheating...
            throw new BgaUserException(self::_("Wrong transmitted positional info [Press F5 in case of troubles]"));
        }
        
        $card = self::getCardInfoFromPosition($owner, $location, $age, $position);
        if ($card === null) {
            throw new BgaUserException(self::_("Transmitted positional info out of range [Press F5 in case of troubles]"));
        }
        if (!$card['selected']) {
            // The player is cheating...
            throw new BgaUserException(self::_("This card cannot be selected [Press F5 in case of troubles]"));
        }
        // No cheating here
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
            // The player is cheating...
            throw new BgaUserException(self::_("You cannot choose a special option; you have to choose a card [Press F5 in case of troubles]"));
        }
        
        switch(self::decodeSpecialTypeOfChoice($special_type_of_choice)) {
            case 'choose_opponent':
            case 'choose_opponent_with_fewer_points':
                // Player choice
                // Check if the choice is a opponent
                if ($choice == $player_id) {
                    // The player is cheating...
                    throw new BgaUserException(self::_("You cannot choose yourself [Press F5 in case of troubles]"));
                }
                else if ($choice == self::getPlayerTeammate($player_id)) {
                    // The player is cheating...
                    throw new BgaUserException(self::_("You cannot choose your teammate [Press F5 in case of troubles]"));
                }
                $players = self::loadPlayersBasicInfos();
                if (!array_key_exists($choice, $players)) {
                    // The player is cheating...
                    throw new BgaUserException(self::_("You must choose an opponent [Press F5 in case of troubles]"));
                }
                if ($choice == 'choose_opponent_with_fewer_points' && self::getPlayerScore($choice) >= self::getPlayerScore($player_id)) {
                    // The player is cheating...
                    throw new BgaUserException(self::_("You must choose an opponent with fewer points than you [Press F5 in case of troubles]"));
                }
                break;
            case 'choose_value':
                // Values choice
                if (!ctype_digit($choice) || $choice < 1 || $choice > 10) {
                    // The player is cheating...
                    throw new BgaUserException(self::_("Your choice must be a value from 1 to 10 [Press F5 in case of troubles]"));
                }
                break;
            case 'choose_color':
                // Color choice
                if (!ctype_digit($choice) || $choice < 0 || $choice > 4) {
                    // The player is cheating...
                    throw new BgaUserException(self::_("Your choice must be a color [Press F5 in case of troubles]"));
                }
                break;
            case 'choose_two_colors':
                // Two color choice
                if (!ctype_digit($choice) || $choice < 0) {
                    // The player is cheating...
                    throw new BgaUserException(self::_("Your choice must be two colors [Press F5 in case of troubles]"));
                }
                $colors = self::getValueAsArray($choice);
                if (count($colors) <> 2 || $colors[0] == $colors[1] || $colors[0] < 0 || $colors[0] > 4 || $colors[1] < 0 || $colors[1] > 4) {
                    // The player is cheating... 
                    throw new BgaUserException(self::_("Your choice must be two colors [Press F5 in case of troubles]"));
                }
                break;
            case 'choose_rearrange':
                $exception = self::_("Ill formated permutation info [Press F5 in case of troubles]");
                // Choice contains the color and the permutations made
                if (!is_array($choice) || !array_key_exists('color', $choice)) {
                    throw new BgaUserException($exception);
                }
                $color = $choice['color'];
                if (!ctype_digit($color) || $color < 0 || $color > 4) {
                    // The player is cheating...
                    throw new BgaUserException($exception);
                }
                if (!array_key_exists('permutations_done', $choice)) {
                    throw new BgaUserException($exception);
                }
                $permutations_done = $choice['permutations_done'];
                if (!is_array($permutations_done) || count($permutations_done) == 0) {
                    throw new BgaUserException($exception);
                }
                $n = self::countCardsInLocation($player_id, 'board', false, true);
                $n = $n[$color];
                
                foreach($permutations_done as $permutation) {
                    if (!array_key_exists('position', $permutation)) {
                        throw new BgaUserException($exception);
                    }
                    $position = $permutation['position'];
                    if (!array_key_exists('delta', $permutation)) {
                        throw new BgaUserException($exception);
                    }
                    $delta = $permutation['delta'];
                    if ($delta <> 1 && $delta <> -1) {
                        throw new BgaUserException($exception);
                    }
                    if (!ctype_digit($position) || $position >= $n || $position + $delta >= $n) {
                        throw new BgaUserException($exception);
                    }
                }
                
                // Do the rearrangement now
                $actual_change = self::rearrange($player_id, $color, $permutations_done);
                
                if (!$actual_change) {
                    throw new BgaUserException(self::_("Your choice does not make any change in the rearrangement [Press F5 in case of troubles]"));
                }
                
                // This move was legal
                self::notifyPlayer($player_id, 'log', clienttranslate('${You} rearrange your ${color} pile.'), array('i18n' => array('color'), 'You' => 'You', 'color' => self::getColorInClear($color)));
                self::notifyAllPlayersBut($player_id, 'rearrangedPile', clienttranslate('${player_name} rearranges his ${color} pile.'), array('i18n' => array('color'), 'player_id' => $player_id, 'rearrangement' => $choice, 'player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id), 'color' => self::getColorInClear($color)));
                try {
                    self::checkForSpecialAchievements($player_id, false); // Check all except Wonder
                }
                catch (EndOfGame $e) {
                    // End of the game: the exception has reached the highest level of code
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
                    // The player is cheating...
                    throw new BgaUserException(self::_("You have to choose between yes or no [Press F5 in case of troubles]"));
                }
                break;
            default:
                break;
        }
        // No cheating here
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
    
    function argPlayerTurn() {
        $player_id = self::getGameStateValue('active_player');
        return array(
            'i18n' => array('qualified_action'),
            'action_number' => self::getGameStateValue('first_player_with_only_one_action') || self::getGameStateValue('second_player_with_only_one_action') || self::getGameStateValue('has_second_action') ? 1 : 2,

            'qualified_action' => self::getGameStateValue('first_player_with_only_one_action') || self::getGameStateValue('second_player_with_only_one_action') ? clienttranslate('an action') :
                                  (self::getGameStateValue('has_second_action') ? clienttranslate('a first action') : clienttranslate('a second action')),
            'age_to_draw' => self::getAgeToDrawIn($player_id),
            'claimable_ages' => self::getClaimableAges($player_id)
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
        
        $nested_id_1 = self::getGameStateValue('nested_id_1');
        $card_id = $nested_id_1 == -1 ? self::getGameStateValue('dogma_card_id') : $nested_id_1 ;
        $current_effect_type = $nested_id_1 == -1 ? self::getGameStateValue('current_effect_type') : 1 /* Non-demand effects only*/;
        $current_effect_number = $nested_id_1 == -1 ?  self::getGameStateValue('current_effect_number') : self::getGameStateValue('nested_current_effect_number_1');
        
        $card = self::getCardInfo($card_id);
        $card_name = $card['name'];
        
        $can_pass = self::getGameStateValue('can_pass') == 1;
        $can_stop = self::getGameStateValue('n_min') <= 0;
        
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
                for($age=1; $age<=10; $age++) {
                    $options[] = array('value' => $age, 'text' => self::getAgeSquare($age));
                }
                break;
            case 'choose_color':
            case 'choose_two_colors':
                for($color=0; $color<5; $color++) {
                    $options[] = array('value' => $color, 'text' => self::getColorInClear($color));
                }                
                break;
            case 'choose_rearrange':
                // Nothing
                $options = null;
                break;
            case 'choose_yes_or_no':
                // See the card
                break;
            default:
                break;
            }
            
            // The message to display is specific of the card
            $step = self::getGameStateValue('step');
            $letters = array(1 => 'A', 2 => 'B', 3 => 'C', 4 => 'D');
            $code = $card_id . self::getLetterForEffectType($current_effect_type) . $current_effect_number . $letters[$step];
            
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
            
            // id 124, Artifacts age 1: Tale of the Shipwrecked Sailor
            case "124N1A":
                $message_for_player = clienttranslate('${You} must choose a color');
                $message_for_others = clienttranslate('${player_name} must choose a color');
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
        if ($splay_direction == -1) {
            $owner_from = self::getGameStateValue("owner_from");
            $location_from = self::decodeLocation(self::getGameStateValue("location_from"));
            $owner_to = self::getGameStateValue("owner_to");
            $location_to = self::decodeLocation(self::getGameStateValue("location_to"));
            $bottom_to = self::getGameStateValue("bottom_to") == 1;
            $age_min = self::getGameStateValue("age_min");
            $age_max = self::getGameStateValue("age_max");
            $with_icon = self::getGameStateValue("with_icon");
            $without_icon = self::getGameStateValue("without_icon");
        }
        
        // Number of cards
        if ($n_min <= 0) {
            $n_min = 1;
        }

        $player_id_is_owner_from = $owner_from == $player_id;
        
        // Identification of the potential opponent(s)
        if ($splay_direction == -1 && ($owner_from == -2 || $owner_from == -3)) {
            $opponent_id = $owner_from;
        } else if ($splay_direction == -1 && ($owner_to == -2 || $owner_to == -3)) {
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
        }
        else if ($opponent_id > 0) {
            $your = 'your';
            $opponent_name = self::getColoredText(self::getPlayerNameFromId($opponent_id), $opponent_id);
        }
        else if ($opponent_id == -2) {
            $your = null;
            if ($n_min > 800) {
                $opponent_name = clienttranslate("all players");
            }
            else {
                $opponent_name = clienttranslate("any player");
            }
        }
        else { // opponent_id == -3
            $your = null;
            if ($n_min > 800) {
                $opponent_name = clienttranslate("all opponents");
            }
            else {
                $opponent_name = clienttranslate("any opponent");
            }
        }
        
        // Action to be done
        if ($n == 0) {
            if ($can_pass || $can_stop) {
                $you_must = clienttranslate("{You} may");
                $player_must = clienttranslate("{player_name} may");
            }
            else {
                $you_must = clienttranslate("{You} must");
                $player_must = clienttranslate("{player_name} must");
            }
        }
        else {
            if ($can_pass || $can_stop) {
                $you_must = clienttranslate("{You} still may");
                $player_must = clienttranslate("{player_name} still may");
            }
            else {
                $you_must = clienttranslate("{You} still must");
                $player_must = clienttranslate("{player_name} still must");
            }
        }
        $you_must = self::format($you_must, array('You' => $You));
        $player_must = self::format($player_must, array('player_name' => $player_name));
        
        // Number
        if ($n_min > 800) {
            $number = clienttranslate("all the");
        }
        else if ($n_max > 800) {
            $number = clienttranslate("any number of");
        }
        else if ($n_min == $n_max) {
            $number = self::getTranslatedNumber($n_min);    
        }
        else if ($n_min + 1 == $n_max) {
            $number = self::getTranslatedNumber($n_min) . " " . clienttranslate("or") . " " . self::getTranslatedNumber($n_max);    
        }
        else {
            $number = self::getTranslatedNumber($n_min) . " " . clienttranslate("to") . " " . self::getTranslatedNumber($n_max);
        }
        
        
        if ($splay_direction == -1) {
            // Color of the cards
            $selectable_colors = self::getGameStateValueAsArray('color_array');
            $selectable_colors_in_clear = array();
            for($i=0; $i<count($selectable_colors); $i++) {
                $selectable_colors_in_clear[$i] = self::getColorInClear($selectable_colors[$i]);
            }
            switch (count($selectable_colors)) {
            case 1: // Only one color can be selected
                $colors = $selectable_colors_in_clear[0];
                break;
            case 2:
                $colors = self::format(clienttranslate("{color_1} or {color_2}"),
                                                    array(
                                                        'color_1' => $selectable_colors_in_clear[0],
                                                        'color_2' => $selectable_colors_in_clear[1]
                                                    ));
                break;
            case 3:
                $colors = self::format(clienttranslate("{color_1}, {color_2} or {color_3}"),
                                                    array(
                                                        'color_1' => $selectable_colors_in_clear[0],
                                                        'color_2' => $selectable_colors_in_clear[1],
                                                        'color_3' => $selectable_colors_in_clear[2],
                                                    ));
                break;
            case 4: { // Any color can be selected but one
                // Find the missing color
                for ($color=0; $color < 5; $color++) {
                    if (!in_array($color, $selectable_colors)) {
                        $unselectable_color_in_clear = self::getColorInClear($color);
                        break;
                    }
                }
                $colors = self::format(clienttranslate("non-{color}"), array('color' => $unselectable_color_in_clear));
                break;
            }
            default: // 5
                break;
            }
            
            // Age of the cards
            if ($age_min == 1 && $age_max == 10) {
                if (count($selectable_colors) < 5) {
                    if ($with_icon > 0) {
                        if ($n_max == 1) {
                            $cards = self::format(clienttranslate("{color} card with a {icon}"), array('color' => $colors, 'icon' => '{[}'.$with_icon.'{]}'));
                        }
                        else {
                            $cards = self::format(clienttranslate("{color} cards with a {icon}"), array('color' => $colors, 'icon' => '{[}'.$with_icon.'{]}'));
                        }
                    }
                    else if ($without_icon > 0) {
                        if ($n_max == 1) {
                            $cards = self::format(clienttranslate("{color} card without a {icon}"), array('color' => $colors, 'icon' => '{[}'.$without_icon.'{]}'));
                        }
                        else {
                            $cards = self::format(clienttranslate("{color} cards without a {icon}"), array('color' => $colors, 'icon' => '{[}'.$without_icon.'{]}'));
                        }
                    }
                    else {
                        if ($n_max == 1) {
                            $cards = self::format(clienttranslate("{color} card"), array('color' => $colors));
                        }
                        else {
                            $cards = self::format(clienttranslate("{color} cards"), array('color' => $colors));
                        }
                    }
                }
                else {
                    if ($with_icon > 0) {
                        if ($n_max == 1) {
                            $cards = self::format(clienttranslate("card with a {icon}"), array('icon' => '{[}'.$with_icon.'{]}'));
                        }
                        else {
                            $cards = self::format(clienttranslate("cards with a {icon}"), array('icon' => '{[}'.$with_icon.'{]}'));
                        }
                    }
                    else if ($without_icon > 0) {
                        if ($n_max == 1) {
                            $cards = self::format(clienttranslate("card without a {icon}"), array('icon' => '{[}'.$without_icon.'{]}'));
                        }
                        else {
                            $cards = self::format(clienttranslate("cards without a {icon}"), array('icon' => '{[}'.$without_icon.'{]}'));
                        }
                    }
                    else {
                        if ($n_max == 1) {
                            $cards = clienttranslate("card");
                        }
                        else {
                            $cards = clienttranslate("cards");
                        }
                    }
                }
            }
            else {
                if (count($selectable_colors) < 5) {
                    $cards = self::format(clienttranslate("{color}") . " ", array('color' => $colors));
                }
                else {
                    $cards = '';
                }
                
                if ($age_min == $age_max) {
                    $cards .= "{<}" . $age_min . "{>}";
                }
                else if ($age_min + 1 == $age_max) {
                    if ($n_max == 1) {
                        $cards .= "card of value {<}" . $age_min . "{>} " . clienttranslate("or") . " {<}" . $age_max . "{>}";
                    }
                    else {
                        $cards .= "cards of value {<}" . $age_min . "{>} " . clienttranslate("or") . " {<}" . $age_max . "{>}";
                    }
                }
                else {
                    if ($n_max == 1) {
                        $cards .= "card of value {<}" . $age_min . "{>} " . clienttranslate("to") . " {<}" . $age_max . "{>}";
                    }
                    else {
                        $cards .= "cards of value {<}" . $age_min . "{>} " . clienttranslate("to") . " {<}" . $age_max . "{>}";
                    }
                }
                
                if ($with_icon > 0) {
                    $cards .= " " . self::format(clienttranslate("with a {icon}"), array('icon' => '{[}'.$with_icon.'{]}'));
                }
                else if ($without_icon > 0) {
                    $cards .= " " . self::format(clienttranslate("without a {icon}"), array('icon' => '{[}'.$without_icon.'{]}'));
                }
            }
            $cards = self::format($cards, self::getDelimiterMeanings($cards, false));
        }
        else { // splay_direction <> -1
            $splayable_colors = self::getGameStateValueAsArray('color_array');
            $splayable_colors_in_clear = array();
            foreach ($splayable_colors as $color) {
                $splayable_colors_in_clear[] = self::getColorInClearWithCards($color);
            }
        }
        
        // Creation of the message
        if ($opponent_name === null || $opponent_id == -2 || $opponent_id == -3) {
            if ($splay_direction == -1) {
                $messages = self::getTransferInfoWithOnePlayerInvolved($location_from, $location_to, $player_id_is_owner_from, $bottom_to, $you_must, $player_must, $player_name, $number, $cards, $opponent_name);
                $splay_direction = null;
                $splay_direction_in_clear = null;
            }
            else {
                $messages = array('message_for_player' => $you_must, 'message_for_others' => $player_must, 'splayable_colors' => $splayable_colors, 'splayable_colors_in_clear' => $splayable_colors_in_clear);
                $splay_direction_in_clear = self::getSplayDirectionInClear($splay_direction);
            }
        }
        else {
            $messages = self::getTransferInfoWithTwoPlayersInvolved($location_from, $location_to, $player_id_is_owner_from, $you_must, $player_must, $your, $player_name, $opponent_name, $number, $cards);
            $splay_direction = null;
            $splay_direction_in_clear = null;
        }
        
        if ($special_type_of_choice == 0 && $splay_direction == null && $location_from == 'score') {
            if ($owner_from == $player_id) {
                $must_show_score = true;
            }
            else if ($owner_from == -2) {
                $visible_cards = self::getVisibleSelectedCards($player_id);
                $must_show_score = false;
                foreach($visible_cards as $card) {
                    if ($card['owner'] == $player_id && $card['location'] == 'score') {
                        $must_show_score = true;
                        break;
                    }
                }
            }
            else {
                $must_show_score = false;
            }
        }
        else {
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
            'color_pile' => $splay_direction === null && $location_from == 'pile' ? $selectable_colors[0] : null,
            
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
        
        self::notifyPlayer($player_id, 'initialCardChosen', clienttranslate('${You} melded the first card in English alphabetical order (${english_name}): You play first.'), array(
            'You' => 'You',
            'english_name' => $earliest_card['name']
        ));

        
        self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} melded the first card in English alphabetical order (${english_name}): he plays first.'), array(
            'player_name' => self::getPlayerNameFromId($player_id),
            'english_name' => $earliest_card['name']
        ));
        
        // Enter normal play loop
        self::setGameStateValue('active_player', $player_id);
        $this->gamestate->changeActivePlayer($player_id);
        self::notifyGeneralInfo('<!--empty-->');
        self::trace('turn0->playerTurn');
        $this->gamestate->nextState();
    }
    
    function stInterPlayerTurn() {
        // An action of the player has been fully resolved.
        
        // Give him extra time for his actions to come
        self::giveExtraTime(self::getActivePlayerId());
        
        // Does he plays again?
        if (self::getGameStateValue('first_player_with_only_one_action')) {
            // First turn: the player had only one action to make
            $next_player = true;
            self::setGameStateValue('first_player_with_only_one_action', 0);
        }
        else if (self::getGameStateValue('second_player_with_only_one_action')) {
            // 4 players at least and this is the second turn: the player had only one action to make
            $next_player = true;
            self::setGameStateValue('second_player_with_only_one_action', 0);
        }
        else if (self::getGameStateValue('has_second_action')) {
            // The player took his first action and has another one
            $next_player = false;
            self::setGameStateValue('has_second_action', 0);
        }
        else {
            // The player took his second action
            $next_player = true;
            self::setGameStateValue('has_second_action', 1);
        }
        if ($next_player) { // The turn for the current player is over
            // Reset the flags for Monument special achievement
            self::resetFlagsForMonument();
            
            // Activate the next player in turn
            $this->activeNextPlayer();
            $player_id = self::getActivePlayerId();
            self::setGameStateValue('active_player', $player_id);
        }
        self::notifyGeneralInfo('<!--empty-->');
        self::trace('interPlayerTurn->playerTurn');
        $this->gamestate->nextState();
    }
    
    function stDogmaEffect() {
        // An effect of a dogma has to be resolved
        $current_effect_type = self::getGameStateValue('current_effect_type');
        $current_effect_number = self::getGameStateValue('current_effect_number');
        $card_id = self::getGameStateValue('dogma_card_id');
        $card = self::getCardInfo($card_id);
        $qualified_effect = self::qualifyEffect($current_effect_type, $current_effect_number, $card);
        
        // Search for the first player who will undergo/share the effects, if any
        $launcher_id = self::getGameStateValue('active_player');
        $first_player = self::getFirstPlayerUnderEffect($current_effect_type, $launcher_id);
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
        
        self::setGameStateValue('current_player_under_dogma_effect', $first_player);
        $this->gamestate->changeActivePlayer($first_player);
        
        // Begin the loop with this player
        self::trace('dogmaEffect->playerInvolvedTurn');
        $this->gamestate->nextState('playerInvolvedTurn');
    }
    
    function stInterDogmaEffect() {
        // A effect of a dogma card has been resolved. Is there another one?
        $current_effect_type = self::getGameStateValue('current_effect_type');
        
        // Indicate the potential new (non-demand) dogma to come
        if ($current_effect_type == 0 || $current_effect_type == 2) { // There is only ever one "I demand" or "I compel" effect per card
            $current_effect_number = 1; // Switch on the first non-demand dogma, if exists
        }
        else {
            $current_effect_number = self::getGameStateValue('current_effect_number') + 1; // Next non-demand dogma, if exists
        }
        
        $card_id = self::getGameStateValue('dogma_card_id');
        $card = self::getCardInfo($card_id);
        
        // Check whether this new dogma exists actually or not
        if ($current_effect_number > 3 || $card['non_demand_effect_'.$current_effect_number] === null) {
            // No card has more than 3 non-demand dogma => there is no more effect
            // or the next non-demand-dogma effect is not defined
            
            $sharing_bonus = self::getGameStateValue('sharing_bonus');
            $launcher_id = self::getGameStateValue('active_player');
            
            // Stats
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
            if ($sharing_bonus == 1) {
                self::incStat(1, 'dogma_actions_number_with_sharing', $launcher_id);
            }
            
            // Award the sharing bonus if needed
            if ($sharing_bonus == 1) {
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
            self::setGameStateValue('dogma_card_id', -1);
            self::setGameStateValue('current_effect_type', -1);
            self::setGameStateValue('current_effect_number', -1);
            self::setGameStateValue('sharing_bonus', -1);
            self::setGameStateValue('current_player_under_dogma_effect', -1);
            self::setGameStateValue('step', -1);
            self::setGameStateValue('step_max', -1);
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
            self::setGameStateValue('color_array', -1);
            self::setGameStateValue('with_icon', -1);
            self::setGameStateValue('without_icon', -1);
            self::setGameStateValue('not_id', -1);
            self::setGameStateValue('can_pass', -1);
            self::setGameStateValue('n', -1);
            self::setGameStateValue('id_last_selected', -1);
            self::setGameStateValue('age_last_selected', -1);
            self::setGameStateValue('color_last_selected', -1);
            self::setGameStateValue('score_keyword', -1);
            self::setGameStateValue('auxiliary_value', -1);
            self::setGameStateValue('require_achievement_eligibility', -1);
            for($i=1; $i<=9; $i++) {
                self::setGameStateInitialValue('nested_id_'.$i, -1);
                self::setGameStateInitialValue('nested_current_effect_number_'.$i, -1);
            }
            
            // End of this player action
            self::trace('interDogmaEffect->interPlayerTurn');
            $this->gamestate->nextState('interPlayerTurn');
            return;
        }
        
        // There is another (non-demand) effect to perform
        self::setGameStateValue('current_effect_type', 1);
        self::setGameStateValue('current_effect_number', $current_effect_number);
        
        // Jump to this effect
        self::trace('interDogmaEffect->dogmaEffect');
        $this->gamestate->nextState('dogmaEffect');
    }
    
    function stPlayerInvolvedTurn() {
        // A player must or can undergo/share an effect of a dogma card
        $player_id = self::getGameStateValue('current_player_under_dogma_effect');
        $launcher_id = self::getGameStateValue('active_player');
        $nested_id_1 = self::getGameStateValue('nested_id_1');
        $card_id = $nested_id_1 == -1 ? self::getGameStateValue('dogma_card_id') : $nested_id_1 ;
        $current_effect_type = $nested_id_1 == -1 ? self::getGameStateValue('current_effect_type') : 1 /* Non-demand effects only*/;
        $current_effect_number = $nested_id_1 == -1 ?  self::getGameStateValue('current_effect_number') : self::getGameStateValue('nested_current_effect_number_1');
        $step_max = null;
        $step = null;
        
        $qualified_effect = self::qualifyEffect($current_effect_type, $current_effect_number, self::getCardInfo($card_id));      
        self::notifyEffectOnPlayer($qualified_effect, $player_id, $launcher_id);
        
        $crown = self::getIconSquare(1);
        $leaf = self::getIconSquare(2);
        $lightbulb = self::getIconSquare(3);
        $tower = self::getIconSquare(4);
        $factory = self::getIconSquare(5);
        $clock = self::getIconSquare(6);
        
        try {
            //||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||
            // [A] SPECIFIC CODE: what are the automatic actions to make and/or is there interaction needed?
            $code = $card_id . self::getLetterForEffectType($current_effect_type) . $current_effect_number;
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
                    if (self::hasRessource($card, 4)) { // "If it as tower"
                        self::notifyGeneralInfo(clienttranslate('It has a ${tower}.'), array('tower' => $tower));
                        self::transferCardFromTo($card, $player_id, 'score', false, true); // "Score it"
                        continue; // "Repeat this dogma effect"
                    }
                    break; // "Otherwise"        
                }
                self::notifyGeneralInfo(clienttranslate('It does not have a ${tower}.'), array('tower' => $tower));
                self::transferCardFromTo($card, $player_id, 'hand'); // "Keep it"
                break;
            
            // id 5, age 1: Oars
            case "5D1":
                if (self::getGameStateValue('auxiliary_value') == -1) { // If this variable has not been set before
                    self::setGameStateValue('auxiliary_value', 0);
                }
                $step_max = 1; // --> 1 interaction: see B
                break;
            
            case "5N1":
                if (self::getGameStateValue('auxiliary_value') <= 0) { // "If no cards were transfered due to this demand"
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
                if ($number_to_be_scored == 0) {
                    self::notifyPlayer($player_id, 'log', clienttranslate('${You} have no specific color on your board.'), array('You' => 'You'));
                    self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} has no specific color on his board.'), array('player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id)));
                }
                else if ($number_to_be_scored == 1) {
                    self::notifyPlayer($player_id, 'log', clienttranslate('${You} have one specific color on your board.'), array('You' => 'You'));
                    self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} has one specific color on his board.'), array('player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id)));
                }
                else {
                    $translated_number = self::getTranslatedNumber($number_to_be_scored);
                    self::notifyPlayer($player_id, 'log', clienttranslate('${You} have ${n} specific colors on your board.'), array('i18n' => array('n'), 'You' => 'You', 'n' => $translated_number));
                    self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} has ${n} specific colors on his board.'), array('i18n' => array('n'), 'player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id), 'n' => $translated_number));
                }
                // Score this number of times
                for ($i=0; $i < $number_to_be_scored; $i++) {
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
                self::setGameStateValueFromArray('auxiliary_value', array());
                $step_max = 1; // --> 1 interaction: see B
                break;
                
            // id 20, age 2: Mapmaking
            case "20D1":
                if (self::getGameStateValue('auxiliary_value') == -1) { // If this variable has not been set before
                    self::setGameStateValue('auxiliary_value', 0);
                }
                $step_max = 1; // --> 1 interaction: see B
                break;
            
            case "20N1":
                if (self::getGameStateValue('auxiliary_value') == 1) { // "If any card was transfered due to the demand"
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
                self::executeDraw($player_id, 1, 'board', true); // "Draw and tuck a 1"
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
                    if ($top_card !== null && self::hasRessource($top_card, 4)) { // This top card is present, with a tower on it
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
                }
                else { // "Otherwise"
                    self::notifyGeneralInfo(clienttranslate('It does not have a ${crown}.'), array('crown' => $crown));
                    $number_of_players_with_fewer_points = self::getUniqueValueFromDB(self::format("
                        SELECT
                            COUNT(*)
                        FROM
                            player
                        WHERE
                            player_id <> {player_id} AND
                            player_innovation_score < (
                                SELECT
                                    player_innovation_score
                                FROM
                                    player
                                WHERE
                                    player_id = {player_id}
                            ) AND
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
                    if ($number_of_players_with_fewer_points == 0) {
                        self::notifyPlayer($player_id, 'log', clienttranslate('There is no opponent who has fewer points than ${you}.'), array('you' => 'you'));
                        self::notifyAllPlayersBut($player_id, 'log', clienttranslate('There is no opponent who has fewer points than ${player_name}.'), array('player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id)));
                    }
                    else {
                        $step_max = 2; // --> 2 interactions: see B
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
                    $card = self::executeDraw($player_id, 3, 'board', true); // "Draw and tuck a 3"
                } while(self::hasRessource($card, 1 /* crown */)); // "If it has a crown, repeat this dogma effect"
                break;
            
            // id 38, age 4: Gunpowder
            case "38D1":
                if (self::getGameStateValue('auxiliary_value') == -1) { // If this variable has not been set before
                    self::setGameStateValue('auxiliary_value', 0);
                }
                $step_max = 1; // --> 1 interaction: see B
                break;
                
            case "38N1":
                if (self::getGameStateValue('auxiliary_value') == 1) { // "If any card was transfered due to the demand"
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
                self::executeDraw($player_id, 5, 'board', true); // "Draw and tuck a 5"
                break;

            case "47N2":
                $step_max = 1; // --> 1 interaction: see B
                break;
                
            case "47N3":
                $step_max = 1; // --> 1 interaction: see B
                break;
                
            // id 48, age 5: The pirate code
            case "48D1":
                if (self::getGameStateValue('auxiliary_value') == -1) { // If this variable has not been set before
                    self::setGameStateValue('auxiliary_value', 0);
                }
                $step_max = 1; // --> 1 interaction: see B
                break;

            case "48N1":
                if (self::getGameStateValue('auxiliary_value') == 1) { // "If any card was transfered due to the demand"
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
                self::executeDraw($player_id, 4, 'board', true); // "Draw and tuck two 1"
                self::executeDraw($player_id, 4, 'board', true); //
                $card = self::getBottomCardOnBoard($player_id, 3 /* yellow */);
                if ($card !== null) {
                    self::transferCardFromTo($card, $player_id, 'score', false, true); // "Score your bottom yellow card"
                }
                break;
            
            // id 53, age 5: Astronomy
            case "53N1":
                while(true) {
                    $card = self::executeDraw($player_id, 6, 'revealed'); // "Draw and reveal a 6"
                    if ($card['color'] != 0 /* blue */ && $card['color'] != 2 /* green */) {
                        self::notifyGeneralInfo(clienttranslate("This card is neither blue nor green."));
                        break; // "Otherwise"
                    };
                    // "If the card is green or blue"
                    self::notifyGeneralInfo($card['color'] == 0 ? clienttranslate("This card is blue.") : clienttranslate("This card is green."));
                    self::transferCardFromTo($card, $player_id, 'board'); // "Meld it"
                }
                self::transferCardFromTo($card, $player_id, 'hand'); // ("Keep it")
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
                    for($color=0; $color<5; $color++) {
                        $player_top_card = self::getTopCardOnBoard($player_id, $color);
                        if ($player_top_card === null || !self::hasRessource($player_top_card, 3 /* lightbulb */)) {
                            continue;
                        }
                        $launcher_top_card = self::getTopCardOnBoard($launcher_id, $color);
                        if ($launcher_top_card === null /* => Value 0, so the color is selectable */ || $player_top_card['age'] > $launcher_top_card['age']) {
                            $colors[] = $color; // This color is selectable
                        }
                    }
                }
                else { // First edition
                   $colors = array(0,1,2,3); // All but purple
                }
                self::setGameStateValueFromArray('auxiliary_value', $colors);
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
                    self::executeDraw($player_id, 6, 'board', true); // "Draw and tuck a 6"
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
                if (self::getGameStateValue('auxiliary_value') == -1) { // If this variable has not been set before
                    self::setGameStateValue('auxiliary_value', 0);
                }
                $step_max = 1; // --> 1 interaction: see B
                break;
            
            case "62N1":
                if (self::getGameStateValue('auxiliary_value') == 1) { // "If any card was returned as a result of the demand"
                    self::executeDraw($player_id, 7, 'board'); // "Draw and meld a 7"
                }
                break;
            
            // id 63, age 6: Democracy          
            case "63N1":
                if (self::getGameStateValue('auxiliary_value') == -1) { // If this variable has not been set before
                    self::setGameStateValue('auxiliary_value', 0);
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
                self::setGameStateValue('auxiliary_value', $number);
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
                self::setGameStateValue('auxiliary_value', 0); // Flag to indicate if the player has transfered a card or not
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
                self::setGameStateValueFromArray('auxiliary_value', array()); // Flag to indicate what ages have been tucked
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
                self::setGameStateValueFromArray('auxiliary_value', array(0,2,3,4)); // Flag to indicate the colors the player can still choose (not red at the start)
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
                self::setGameStateValueFromArray('auxiliary_value', array()); // Flag to indicate what ages have been tucked
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
                self::setGameStateValue('auxiliary_value', 0); // Flag to indicate if one purple card has been tuckeds or not
                $step_max = 1; // --> 1 interaction: see B
                break;
                
            // id 85, age 9: Computers     
            case "85N1":
                $step_max = 1; // --> 1 interaction: see B
                break;
                
            case "85N2":
                $card = self::executeDraw($player_id, 10, 'board'); // "Draw and meld a 10"
                self::checkAndPushCardIntoNestedDogmaStack($card); // "Execute each of its non-demand dogma effects"
                break;
            
            // id 86, age 9: Genetics     
            case "86N1":
                $card = self::executeDraw($player_id, 10, 'board'); // "Draw and meld a 10"
                $board = self::getCardsInLocation($player_id, 'board', false, true);
                $pile = $board[$card['color']];
                for($p=0; $p < count($pile)-1; $p++) { // "For each card beneath it"
                    $card = self::getCardInfo($pile[$p]['id']);
                    self::transferCardFromTo($card, $player_id, 'score', false, true); // "Score that card"
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
                    self::notifyGeneralInfo(clienttranslate('This card is red.'));
                    self::removeAllHandsBoardsAndScores(); // "Remove all hands, boards and score piles from the game"
                    self::notifyAll('removedHandsBoardsAndScores', clienttranslate('All hands, boards and score piles are removed from the game. Achievements are kept.'), array());
                    
                    // Stats
                    self::setStat(true, 'fission_triggered');
                    
                    // "If this occurs, the dogma action is complete"
                    // (Set the flags has if the launcher had completed the non-demand dogma effect)
                    self::setGameStateValue('current_player_under_dogma_effect', $launcher_id);
                    self::setGameStateValue('current_effect_type', 1);
                    self::setGameStateValue('current_effect_number', 1);
                }
                else {
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
                $number_of_cards_on_board = self::countCardsInLocation($player_id, 'board', false, true);
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
                $players = self::loadPlayersBasicInfos();
                $max_number_of_leaves = -1;
                $any_under_three_leaves = false;
                foreach ($players as $player_id => $player) {
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
                self::executeDraw($player_id, 10, 'score', false, true /* score keyword*/); // "Draw and score a 10"
                break;
                
            case "96N2":
                self::executeDraw($player_id, 10, 'board'); // "Draw and meld two 10"
                $card = self::executeDraw($player_id, 10, 'board'); //
                self::checkAndPushCardIntoNestedDogmaStack($card); // "Execute each of the second card's non-demand dogma effects"
                break;
                
            // id 97, age 10: Miniaturization
            case "97N1":
                $step_max = 1; // --> 1 interaction: see B
                break;
            
            // id 98, age 10: Robotics
            case "98N1":
                $top_green_card = self::getTopCardOnBoard($player_id, 2 /* green */);
                if ($top_green_card !== null) {
                    self::transferCardFromTo($top_green_card, $player_id, 'score', false, true /* score keyword*/); // "Score your top green card"
                }
                $card = self::executeDraw($player_id, 10, 'board'); // "Draw and meld a 10
                self::checkAndPushCardIntoNestedDogmaStack($card); // "Execute each its non-demand dogma effects"
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
                self::executeDraw($player_id, 6, 'score', false, true); // "Draw and score a 6"
                
                $players = self::loadPlayersBasicInfos();
                $nobody_more_leaves_than_factories = true;
                foreach ($players as $player_id => $player) {
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
                    foreach ($players as $player_id => $player) {
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

            // id 111, Artifacts age 1: Sibidu Needle
            case "111N1":
                while(true) {
                    $card = self::executeDraw($player_id, 1, 'revealed'); // "Draw and reveal a 1"
                    $top_card = self::getTopCardOnBoard($player_id, $card['color']);
                    if ($top_card !== null && $card['age'] == $top_card['age']) { // "If you have a top card of matching color and value"
                        self::transferCardFromTo($card, $player_id, 'score', false, true); // "Score the drawn card"
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
                        'english_name_1' => $top_card['name'],
                        'english_name_2' => $card['name']
                    ));
                    $step_max = 1; // --> 1 interaction: see B
                } else {
                    self::notifyGeneralInfo(clienttranslate('In English alphabetical order, ${english_name_1} does not come before ${english_name_2}.'), array(
                        'english_name_1' => $top_card['name'],
                        'english_name_2' => $card['name']
                    ));
                    self::transferCardFromTo($card, $player_id, 'hand'); // Keep it
                }
                break;
            
            // id 113, Artifacts age 1: Holmegaard Bows
            case "113C1":
                $step_max = 1; // --> 1 interaction: see B
                break;
            
            case "113N1":
                // "Draw a 2"
                self::executeDraw($player_id, 2, 'hand');
                break;

            // id 114, Artifacts age 1: Papyrus of Ani
            case "114N1":
                $step_max = 1; // --> 1 interactions: see B
                break;

            // id 115, Artifacts age 1: Pavlovian Tusk
            case "115N1":
                // "Draw three cards of value equal to your top green card"
                $top_green_card = self::getTopCardOnBoard($player_id, 2 /* green */);
                $top_green_card_age = 0;
                if ($top_green_card !== null) {
                    $top_green_card_age = $top_green_card["age"];
                }
                self::setGameStateValue('card_id_1', self::executeDraw($player_id, $top_green_card_age, 'hand')['id']);
                self::setGameStateValue('card_id_2', self::executeDraw($player_id, $top_green_card_age, 'hand')['id']);
                self::setGameStateValue('card_id_3', self::executeDraw($player_id, $top_green_card_age, 'hand')['id']);
                $step_max = 2; // --> 2 interactions: see B
                break;
            
            // id 116, Artifacts age 1: Priest-King
            case "116N1":
                $step_max = 1; // --> 1 interactions: see B
                break;
            
            case "116N2":
                $step_max = 1; // --> 1 interactions: see B
                break;
            
            // id 117, Artifacts age 1: Electrum Stater of Efesos
            case "117N1":
                while(true) {
                    $card = self::executeDraw($player_id, 3, 'revealed'); // "Draw and reveal a 3"
                    $top_card = self::getTopCardOnBoard($player_id, $card['color']);
                    if ($top_card == null) 
                    { // "If you do not have a top card of the drawn card's color"
                        self::transferCardFromTo($card, $player_id, 'board'); // "meld it"
                        continue; // "Repeat this effect"
                    }
                    break;
                }
                self::transferCardFromTo($card, $player_id, 'hand'); // Keep it
                break;
                
            // id 118, Artifacts age 1: Jiskairumoko Necklace
            case "118C1":
                $step_max = 2; // --> 2 interactions: see B
                break;
            
             // id 119, Artifacts age 1: Dancing Girl
             case "119C1":
                $card = self::getCardInfo(119); // Dancing Girl
                self::transferCardFromTo($card, $player_id, 'board');
                self::incGameStateValue('auxiliary_value', 1); // Keep track of Dancing Girl's movements
                break;
            
            // id 119, Artifacts age 1: Dancing Girl
            case "119N1":
                $num_movements = self::getGameStateValue('auxiliary_value') + 1; // + 1 since it is initialized to -1, not 0
                if ($player_id == $launcher_id && $num_movements == self::countNonEliminatedPlayers() - 1) {
                    self::notifyPlayer($player_id, 'log', clienttranslate('Dancing Girl has been on every board during this action, and it started on your board, so you win.'), array());
                    self::notifyAllPlayersBut($player_id, 'log', clienttranslate('Dancing Girl has been on every board during this action, and it started on ${player_name}\'s board, so they win.'), array('player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id)));
                    self::setGameStateValue('winner_by_dogma', $player_id); // "You win"
                    self::trace('EOG bubbled from self::stPlayerInvolvedTurn Dancing Girl');
                    throw new EndOfGame();
                }
                break;
            
            // id 120, Artifacts age 1: Lurgan Canoe
            case "120N1":
                $step_max = 1; // --> 1 interaction: see B
                break;
            
            // id 121, Artifacts age 1: Xianrendong Shards
            case "121N1":
                $step_max = 2; // --> 2 interactions: see B
                break;

            // id 123, Artifacts age 1: Ark of the Covenant
            case "123N1":
                $step_max = 1; // --> 1 interactions: see B
                break;
                
            // id 124, Artifacts age 1: Tale of the Shipwrecked Sailor
            case "124N1":
                $step_max = 2; // --> 2 interactions: see B
                break;

            // id 127, Artifacts age 2: Chronicle of Zuo
            case "127N1":
                $min_towers = self::getUniqueValueFromDB(self::format("SELECT MIN(player_icon_count_4) FROM player WHERE player_id != {player_id}", array('player_id' => $player_id)));
                $min_crowns = self::getUniqueValueFromDB(self::format("SELECT MIN(player_icon_count_1) FROM player WHERE player_id != {player_id}", array('player_id' => $player_id)));
                $min_bulbs = self::getUniqueValueFromDB(self::format("SELECT MIN(player_icon_count_3) FROM player WHERE player_id != {player_id}",  array('player_id' => $player_id)));
                
                $this_player_icon_counts = self::getPlayerRessourceCounts($player_id);
                
                // TODO: Confirm that "least" means strictly less than other players.
                if ($this_player_icon_counts[4] < $min_towers) {
                    $card = self::executeDraw($player_id, 2, 'hand'); // "If you have the least towers, draw a 2"
                }
                if ($this_player_icon_counts[1] < $min_crowns) {
                    $card = self::executeDraw($player_id, 3, 'hand'); // "If you have the least crowns, draw a 3"
                }
                if ($this_player_icon_counts[3] < $min_bulbs) {
                    $card = self::executeDraw($player_id, 4, 'hand'); // "If you have the least bulbs, draw a 4"
                }
                break;
                
            // id 128, Artifacts age 2: Babylonian Chronicles
            case "128C1":
                $step_max = 1; // --> 1 interaction: see B
                break;
            
            case "128N1":
                // "Draw and score a 3"
                self::executeDraw($player_id, 3, 'score');
                break;
            
            // id 131, Artifacts age 2: Holy Grail
            case "131N1":
                $step_max = 2; // --> 2 interactions: see B
                break;

            // id 132, Artifacts age 2: Terracotta Army
            case "132C1":
                $step_max = 1; // --> 1 interaction: see B
                break;
            
            case "132N1":
                // "Score a card from your hand with no tower"
                $step_max = 1; // --> 1 interaction: see B
                break;

            // id 143, Artifacts age 3: Necronomicon
            case "143N1":
                $card = self::executeDraw($player_id, 3, 'revealed'); // "Draw and reveal a 3"
                if ($card['color'] == 0 /* blue */)  {
                    self::notifyGeneralInfo(clienttranslate("This card is blue."));
                    self::executeDraw($player_id, 9); // "Draw a 9"
                    break; // "Otherwise"
                };
                break;
                
            default:
                // This should not happens
                //throw new BgaVisibleSystemException(self::format(self::_("Unreferenced card effect code in section A: '{code}'"), array('code' => $code)));
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
        self::setGameStateValue('step_max', $step_max);

        // Prepare the first step
        self::setGameStateValue('step', $step === null ? 1 : $step);
        self::trace('playerInvolvedTurn->interactionStep');
        $this->gamestate->nextState('interactionStep');
    }
    
    function stInterPlayerInvolvedTurn() {
        // Code for handling "execute each of the non demand effects of card X"
        while(true) {
            $nested_id_1 = self::getGameStateValue('nested_id_1');
            if ($nested_id_1 == -1) { // No or no more card in the execution stack
                // Resume normal situation
                self::trace('Out of nesting');
                break;
            }
            
            $card = self::getCardInfo($nested_id_1);
            $current_effect_number = self::getGameStateValue('nested_current_effect_number_1') + 1; // Next effect
            
            if ($current_effect_number > 3 || $card['non_demand_effect_'.$current_effect_number] === null) {
                // No card has more than 3 non-demand dogma => there is no more effect
                // or the next non-demand-dogma effect is not defined
                self::notifyGeneralInfo(clienttranslate("Card execution within dogma completed."));
                self::popCardFromNestedDogmaStack();
            }
            else { // There is at least one effect the player can perform
                self::setGameStateValue('nested_current_effect_number_1', $current_effect_number);
                // Continuation of exclusive execution
                self::trace('interPlayerInvolvedTurn->playerInvolvedTurn');
                $this->gamestate->nextState('playerInvolvedTurn');
                return;
            }
        }
        
        // Code executed when there is no exclusive execution to handle, or when it's over
        
        // A player has executed an effect of a dogma card (or passed). Is there another player on which the effect can apply?
        $player_id = self::getGameStateValue('current_player_under_dogma_effect');
        $launcher_id = self::getGameStateValue('active_player');
        $current_effect_type = self::getGameStateValue('current_effect_type');
        $next_player = self::getNextPlayerUnderEffect($current_effect_type, $player_id, $launcher_id);
        if ($next_player === null) {
            // There is no more player eligible for this effect
            // End of the dogma effect
            self::trace('interPlayerInvolvedTurn->interDogmaEffect');
            $this->gamestate->nextState('interDogmaEffect');
            return;
        }
        // There is another player on which the effect can apply
        self::setGameStateValue('current_player_under_dogma_effect', $next_player);
        $this->gamestate->changeActivePlayer($next_player);
        
        // Jump to this player
        self::trace('interPlayerInvolvedTurn->playerInvolvedTurn');
        $this->gamestate->nextState('playerInvolvedTurn');
    }
    
    function stInteractionStep() {
        $player_id = self::getGameStateValue('current_player_under_dogma_effect');
        $launcher_id = self::getGameStateValue('active_player');
        $nested_id_1 = self::getGameStateValue('nested_id_1');
        $card_id = $nested_id_1 == -1 ? self::getGameStateValue('dogma_card_id') : $nested_id_1 ;
        $current_effect_type = $nested_id_1 == -1 ? self::getGameStateValue('current_effect_type') : 1 /* Non-demand effects only*/;
        $current_effect_number = $nested_id_1 == -1 ?  self::getGameStateValue('current_effect_number') : self::getGameStateValue('nested_current_effect_number_1');
        $step = self::getGameStateValue('step');
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
        $code = $card_id . self::getLetterForEffectType($current_effect_type) . $current_effect_number . $letters[$step];
        self::trace('[B]'.$code.' '.self::getPlayerNameFromId($player_id).'('.$player_id.')'.' | '.self::getPlayerNameFromId($launcher_id).'('.$launcher_id.')');
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
            $board = self::getCardsInLocation($player_id, 'board', false, true);
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
            $board = self::getCardsInLocation($player_id, 'board', false, true);
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
            // "You may transfer your top red card to another player board. If you do, transfer that player's top green card to your board.
            $options = array(
                'player_id' => $player_id,
                'can_pass' => true,
                
                'choose_opponent' => true
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
            $board = self::getCardsInLocation($launcher_id, 'board', false, true);
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
            $options = array(
                'player_id' => $player_id,
                'can_pass' => false,
                
                'choose_opponent_with_fewer_points' => true
            );
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
                
                'age' => self::getGameStateValue('auxiliary_value')
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
                'with_icon' => 1 /* crown */
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
            // Cf A
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => false,
                
                'owner_from' => $player_id,
                'location_from' => 'board',
                'owner_to' => $launcher_id,
                'location_to' => 'board',
                
                'color' => self::getGameStateValueAsArray('auxiliary_value'),
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
                
                'color' => array(self::getGameStateValue('auxiliary_value')) /* The color the player has revealed */
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
                'n' => self::getGameStateValue('auxiliary_value'),
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
            // "You may splay right any one color of your cards currently splayed right"
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
            // "Return a card from any other player's score pile for any two clocks on your board"
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
            $selectable_colors = self::getGameStateValueAsArray('auxiliary_value'); /* not red, and for the second card, not the same color as the first */
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
                
                'age' => self::getGameStateValue('auxiliary_value')
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
                
                'color' => array(self::getGameStateValue('auxiliary_value')), /* the color of the card chosen on the first step */
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
                'color' => array(self::getGameStateValue('auxiliary_value'))
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
            // "Transfer a top card with a leaf from any other player's board to your score pile"
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
                'n' => self::intDivisionRoundedUp(self::countCardsInLocation($player_id, 'score'), 2),
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
                
                'not_id' => 100 /* Not this card */
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
            // "If you do, transfer an achievement of the same value from your achievements to mine"
            $returned_age = self::getGameStateValue('age_last_selected');
            if ($returned_age >= 1) {
                $options = array(
                    'player_id' => $player_id,
                    'n' => 1,
                    'can_pass' => false,
                    
                    'owner_from' => $player_id,
                    'location_from' => 'achievements',
                    'owner_to' => $launcher_id,
                    'location_to' => 'achievements'
                );
            }
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
                'card_id_1' => self::getGameStateValue('card_id_1'),
                'card_id_2' => self::getGameStateValue('card_id_2'),
                'card_id_3' => self::getGameStateValue('card_id_3'),
                
                'owner_from' => $player_id,
                'location_from' => 'revealed',
                'owner_to' => $player_id,
                'location_to' => 'score',

                'score_keyword' => true
            );
            break;
            
        // id 123, Artifacts age 1: Ark of the Covenant
        case "123N1A":
            // "Return a card from your hand."
            $options = array(
                'player_id' => $player_id,
                'can_pass' => false,
                'n' => 1,
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
                
                'color' => array(self::getGameStateValue('auxiliary_value'))
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
                'color' => array(0,2,3,4) // non-red
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
            $age_selected = self::getGameStateValue('age_last_selected');
            // "Claim an achievement of matching value, ignoring eligibility"
            $options = array(
                'player_id' => $player_id,
                'n' => 1,
                'can_pass' => false,
                
                'age' => $age_selected,
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
                'score_keyword' => true,
                
                'without_icon' => 4 // tower
            );
            break;
            
        default:
            // This should not happens
            throw new BgaVisibleSystemException(self::format(self::_("Unreferenced card effect code in section B: '{code}'"), array('code' => $code)));
            break;
        }
        //[BB]||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||
        self::setSelectionRange($options);
        
        self::trace('interactionStep->preSelectionMove');
        $this->gamestate->nextState('preSelectionMove');
    }
    
    function stInterInteractionStep() {
        $player_id = self::getGameStateValue('current_player_under_dogma_effect');
        $launcher_id = self::getGameStateValue('active_player');
        $nested_id_1 = self::getGameStateValue('nested_id_1');
        $card_id = $nested_id_1 == -1 ? self::getGameStateValue('dogma_card_id') : $nested_id_1 ;
        $current_effect_type = $nested_id_1 == -1 ? self::getGameStateValue('current_effect_type') : 1 /* Non-demand effects only*/;
        $current_effect_number = $nested_id_1 == -1 ?  self::getGameStateValue('current_effect_number') : self::getGameStateValue('nested_current_effect_number_1');
        $step = self::getGameStateValue('step');
        
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
                $code = $card_id . self::getLetterForEffectType($current_effect_type) . $current_effect_number . $letters[$step];
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
                        self::setGameStateValue('auxiliary_value', 1); // A transfer has been made, flag it
                        if (self::getGameStateValue('game_rules') == 1) { // Last edition => additionnal rule
                            $step--; self::incGameStateValue('step', -1); // "Repeat that dogma effect"
                        }
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
                        self::incGameStateValue('step_max', 1); // --> 1 more interaction: see B
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
                            self::incGameStateValue('step_max', 1); // --> 1 more interaction: see B
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
                        $different_values_selected_so_far = self::getGameStateValueAsArray('auxiliary_value');
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
                        self::setGameStateValue('auxiliary_value', 1); // A transfer has been made, flag it
                    }
                    break;
                
                // id 23, age 2: Monotheism        
                case "23D1A":
                    if ($n > 0) { // "If you do"
                        self::executeDraw($player_id, 1, 'board', true); // "Draw an tuck a 1"
                    }
                    break;
                            
                // id 32, age 3: Medicine
                case "32D1B":
                    // Finish the exchange
                    $this->gamestate->changeActivePlayer($launcher_id); // This exchange was initiated by $launcher_id
                    $id = self::getGameStateValue('auxiliary_value');
                    if ($id != -1) { // The attacking player could indeed choose a card
                        $card = self::getCardInfo($id);
                        self::transferCardFromTo($card, $player_id, 'score'); // $launcher_id -> $player_id
                        self::setGameStateValue('auxiliary_value', -1);
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
                            $color = self::getGameStateValue('color_last_selected');
                            if (self::getCurrentSplayDirection($player_id, $color) > 0) {
                                self::splay($player_id, $color, 0, true /* force_unsplay*/); // "Unsplay that color of your cards"
                            }
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
                        self::setGameStateValue('auxiliary_value', 1); // A transfer has been made, flag it
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
                        self::setGameStateValue('auxiliary_value', self::getGameStateValue('age_last_selected')); // Save the age of the returned card
                        self::incGameStateValue('step_max', 1); // --> 1 more interaction: see B
                    }
                    break;

                // id 42, age 4: Perspective
                case "42N1A":
                    if ($n > 0) { // "If you do"
                        self::incGameStateValue('step_max', 1); // --> 1 more interaction: see B
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
                            self::transferCardFromTo($card, $player_id, 'score', false, true); // "Also score the card beneath it"
                        }
                    }                
                    break;

                // id 48, age 5: The pirate code
                case "48D1A":
                    if ($n > 0) {
                        self::setGameStateValue('auxiliary_value', 1); // A transfer has been made, flag it
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
                            $number_of_cards = self::countCardsInLocation($player_id, 'board', false, true);
                            $number_of_cards = $number_of_cards[$color];
                            if (self::getCurrentSplayDirection($player_id, $color) != 2 /* right */ && $number_of_cards > 1) {
                                self::splay($player_id, $color, 2 /* right */); // "Splay that color of your cards right"
                            }
                            if ($number_of_cards <= 1) {
                                self::notifyPlayer($player_id, 'log', clienttranslate('${You} have ${n} ${colored} card.'), array('i18n' => array('n', 'colored'), 'You' => 'You', 'n' => self::getTranslatedNumber($number_of_cards), 'colored' => self::getColorInClear($color)));
                                self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} has  ${n} ${colored} card.'), array('i18n' => array('n', 'colored'), 'player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id), 'n' => self::getTranslatedNumber($number_of_cards), 'colored' => self::getColorInClear($color)));
                            }
                            else { // $number_of_cards > 1
                                self::notifyPlayer($player_id, 'log', clienttranslate('${You} have ${n} ${colored_cards}.'), array('i18n' => array('n', 'colored_cards'), 'You' => 'You', 'n' => self::getTranslatedNumber($number_of_cards), 'colored_cards' => self::getColorInClearWithCards($color)));
                                self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} has  ${n} ${colored_cards}.'), array('i18n' => array('n', 'colored_cards'), 'player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id), 'n' => self::getTranslatedNumber($number_of_cards), 'colored_cards' => self::getColorInClearWithCards($color)));
                            }
                            self::executeDraw($player_id, $number_of_cards); // "Draw a card of value equal to the number of cards of that color on your board"
                        }
                        else { // First edition => color is chosen by the player
                            self::incGameStateValue('step_max', 1); // --> 1 more interaction: see B
                        }
                    }
                    break;
                    
                // id 51, age 5: Statistics
                case "51D1A":
                    // First edition only
                    if ($n > 0 && self::countCardsInLocation($player_id, 'hand') == 1) { // "If you do, and have only one card in hand afterwards"
                        self::notifyPlayer($player_id, 'log', clienttranslate('${You} have now only one card in your hand.'), array('You' => 'You'));
                        self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} has now only one card in his hand.'), array('player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id)));
                        $step--; self::incGameStateValue('step', -1); // --> "Repeat this demand"
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
                        self::setGameStateValue('auxiliary_value', $color); // Save the color of the revealed card
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
                        self::incGameStateValue('step_max', 1); // --> 1 more interaction: see B
                    }
                    break;
                    
                // id 62, age 6: Vaccination
                case "62D1A":
                    if ($n > 0) { // "If you returned any"
                        self::executeDraw($player_id, 6, 'board'); // "Draw and meld a 6"
                        self::setGameStateValue('auxiliary_value', 1); // Flag that a card has been returned
                    }
                    break;
                    
                // id 63, age 6: Democracy
                case "63N1A":
                    if ($n > self::getGameStateValue('auxiliary_value')) { // "If you returned more than any other player due to Democracy so far during this dogma action"
                        self::executeDraw($player_id, 8, 'score'); // "Draw and score a 8"
                        self::setGameStateValue('auxiliary_value', $n); // Set the new maximum
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
                        self::setGameStateValue('auxiliary_value', 1);  // Flag that at least one card has been transfered
                    }
                    break;
                    
                case "68D1C":
                    if (self::getGameStateValue('auxiliary_value') == 1 && self::countCardsInLocation($player_id, 'hand') == 0) { // "If you transferred any, and then have no cards in hand"
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
                    $id = self::getGameStateValue('auxiliary_value');
                    if ($id != -1) { // The attacking player could indeed choose a card
                        $card = self::getCardInfo($id);
                        self::transferCardFromTo($card, $player_id, 'hand'); // $launcher_id -> $player_id
                        self::setGameStateValue('auxiliary_value', -1);
                    }
                    break;
                    
                // id 73, age 7: Lighting
                case "73N1A":
                    if ($n > 0) { // "If you do"
                        $different_values_selected_so_far = self::getGameStateValueAsArray('auxiliary_value');
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
                        $selectable_colors = self::getGameStateValueAsArray('auxiliary_value');
                        $selectable_colors = array_diff($selectable_colors, array($color)); // Remove the color of the card the player has chosen: he could not choose the same for his next card
                        self::setGameStateValueFromArray('auxiliary_value', $selectable_colors);
                    }
                    break;
                    
                case "78D1B":
                    if (self::getGameStateValueAsArray('auxiliary_value') <> array(0,2,3,4)) { // "If you transferred any cards" (ie: a color has been removed from the initial array)
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
                        self::incGameStateValue('step_max', 2); // --> 2 more interactions: see B
                    }
                    break;
                
                // id 81, age 8: Antibiotics
                case "81N1A":
                    if ($n > 0) { // If you do (implicit)
                        $different_values_selected_so_far = self::getGameStateValueAsArray('auxiliary_value');
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
                            self::transferCardFromTo($card, $player_id, 'score', null, true); // "Score the card beneath it"
                        }
                        self::setGameStateValue('auxiliary_value', $color);// Flag the chosen color for the next interaction
                        self::incGameStateValue('step_max', 1); // --> 1 more interaction: see B
                    }
                    break;
                
                // id 84, age 8: Socialism     
                case "84N1A":
                    if ($n > 0) {
                        if (self::getGameStateValue('auxiliary_value', 1)) { // "If you tucked at least one purple card"
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
                    $remaining_revealed_card = self::getCardsInLocation($player_id, 'revealed'); // There is one card left revealed
                    $remaining_revealed_card = $remaining_revealed_card[0];
                    self::transferCardFromTo($remaining_revealed_card, $player_id, 'board'); // "Meld the other one"
                    break;
                    
                // id 90, age 9: Satellites
                case "90N1A":
                    self::executeDraw($player_id, 8); // "Draw three 8"
                    self::executeDraw($player_id, 8); //
                    self::executeDraw($player_id, 8); //
                    break;
                    
                case "90N3A":
                    $card = self::getCardInfo(self::getGameStateValue('id_last_selected')); // The card the player melded from his hand
                    self::checkAndPushCardIntoNestedDogmaStack($card); // "Execute each of its non-demand dogma effects"
                    break;
                    
                // id 91, age 9: Ecology
                case "91N1A":
                    if ($n > 0) { // "If you do
                        self::incGameStateValue('step_max', 1); // --> 1 more interaction: see B
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
                        $number_of_cards_in_score = self::countCardsInLocation($player_id, 'score', true);
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
                        $age_to_draw_in = self::getGameStateValue('age_last_selected') + 2; // "Reveal a card of of any type of value two higher"
                        // TODO: Allow choice of other expansions e.g. "any type".  For now we simply draw from the base set.
                        $card = self::executeDraw($player_id, $age_to_draw_in, 'revealed');
                        if ($card['color'] == 4) { // "If the drawn card is purple"
                            self::transferCardFromTo($card, $player_id, 'board'); // "Meld it"
                            self::checkAndPushCardIntoNestedDogmaStack($card); // "Execute each of its non-demand effects. Do not share them."
                        } else  {
                            // Non-purple card is placed in the hand
                            self::transferCardFromTo($card, $player_id, 'hand');
                        }
                    } 
                    break;
                
                // id 116, Artifacts age 1: Priest-King
                case "116N1A":
                    // "If you do"
                    if ($n > 0) {
                        $color_scored = self::getGameStateValue('color_last_selected');
                        $top_card = self::getTopCardOnBoard($player_id, $color_scored);
                        if ($top_card !== null) { // "If you have a top card matching its color"
                            self::checkAndPushCardIntoNestedDogmaStack($top_card); // "Execute each of the top card's non-demand dogma effects. Do not share them."
                        }
                    }
                    break;
                
                
                // id 120, Artifacts age 1: Lurgan Canoe
                case "120N1A":
                    $board = self::getCardsInLocation($player_id, 'board', false, true);
                    $pile = $board[self::getGameStateValue('color_last_selected')];
                    $scored = false;
                    for($p=0; $p < count($pile)-1; $p++) { // "Score all other cards of the same color from your board"
                        $card = self::getCardInfo($pile[$p]['id']);
                        self::transferCardFromTo($card, $player_id, 'score', false, true);
                        $scored = true;
                    }
                    if ($scored) { // "If you scored at least one card, repeat this effect"
                        $step--; self::incGameStateValue('step', -1);
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
                        self::transferCardFromTo($remaining_card, $player_id, 'board', /*bottom_to*/ true);
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
                
                // id 123, Artifacts age 1: Ark of the Covenant
                case "123N1A":
                    $players = self::loadPlayersBasicInfos();
                    
                    if ($n > 0) { // Unsaid rule: the player must have returned a card or else this part of the effect can't continue
                        $returned_color = self::getGameStateValue('color_last_selected');
                            
                        foreach($players as $all_player_id => $player) {
                            $top_cards = self::getTopCardsOnBoard($all_player_id);
                            
                            $artifact_found = false;
                            foreach($top_cards as &$card) {
                                if ($card['type'] == 1) { // Artifact
                                    $artifact_found = true;
                                    break;
                                }
                            }
                            
                            if (!$artifact_found) {
                                while (($top_card = self::getTopCardOnBoard($all_player_id, $returned_color)) !== null) {   
                                    // "Transfer all cards of the same color from the boards of all players with no top artifacts to your score pile."
                                    self::transferCardFromTo($top_card, $player_id, 'score');
                                }
                            }
                            
                        }
                    }
                    // "If Ark of the Covenant is a top card on any board, transfer it to your hand."
                    // This happens even if the first part does not.
                    $ark_card = self::getIfTopCard(123);
                    if ($ark_card !== null) {
                        self::transferCardFromTo($ark_card, $player_id, 'hand');
                    }
                    break;
                
                // id 124, Artifacts age 1: Tale of the Shipwrecked Sailor
                case "124N1A":
                    // "Draw a 1"
                    self::executeDraw($player_id, 1);
                    break;

                case "124N1B":
                    $color_melded = self::getGameStateValue('color_last_selected');
                    if ($color_melded >= 0) { // "If you (melded a card)"
                        $board = self::getCardsInLocation($player_id, 'board', false, true);
                        $pile = $board[$color_melded];
                        if (count($pile) >= 2) {
                            self::splay($player_id, self::getGameStateValue('auxiliary_value'), 1); // "Splay that color left"
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
        
        $step_max = self::getGameStateValue('step_max');
        if ($step == $step_max) { // The last step has been completed
            // End of the turn for the player involved
            self::trace('interInteractionStep->interPlayerInvolvedTurn');
            $this->gamestate->nextState('interPlayerInvolvedTurn');
            return;
        }
        // New interaction step
        self::incGameStateValue('step', 1);
        self::trace('interInteractionStep->interactionStep');
        $this->gamestate->nextState('interactionStep');
    }
    
    function stPreSelectionMove() {
        if (self::getGameStateValue('special_type_of_choice') == 0) {
            $selection_size = self::countSelectedCards();
            $n_min = self::getGameStateValue('n_min');
            $n_max = self::getGameStateValue('n_max');
            $splay_direction = self::getGameStateValue('splay_direction');
            
            if($selection_size == 0) { // There is no selectable card
                $can_pass = self::getGameStateValue('can_pass') == 1;
                $owner_from = self::getGameStateValue('owner_from');
                $location_from = self::decodeLocation(self::getGameStateValue('location_from'));
                $colors = self::getGameStateValueAsArray('color_array');
                $with_icon = self::getGameStateValue('with_icon');
                $without_icon = self::getGameStateValue('without_icon');
                
                if(($splay_direction == -1 && ($can_pass || $n_min <= 0)) && ($location_from == 'hand' || $location_from == 'score') && self::countCardsInLocation($owner_from, $location_from) > 0 && ($colors != array(0,1,2,3,4) || $with_icon > 0 || $without_icon > 0)) {
                    // The player can pass or stop and the opponents can't know that the player has no eligible card
                    // This can happen for example in the Masonry effect
                    
                    // No automatic pass or stop: the only choice the player will have in client side is to pass or stop
                    // This way the other players won't get the information that the player was compeled to pass or stop
                    self::trace('preSelectionMove->selectionMove (player has to pass)');
                    $this->gamestate->nextState('selectionMove');
                    return;
                }
                
                // The player passes or stops automatically
                self::notifyUndoable();
                self::trace('preSelectionMove->interInteractionStep (no card)');
                $this->gamestate->nextState('interInteractionStep');
                return;
            }
            else if ($n_min < 800 && $selection_size < $n_min) { // There are selectable cards, but not enough to fulfill the requirement ("May effects only")
                if (self::getGameStateValue('solid_constraint') == 1) {
                    self::notifyUndoableInTotality();
                    self::deselectAllCards();
                    self::trace('preSelectionMove->interInteractionStep (not enough cards)');
                    $this->gamestate->nextState('interInteractionStep');
                    return;
                }
                else {
                    // Reduce n_min and n_max to the selection size
                    self::setGameStateValue('n_min', $selection_size);
                    self::setGameStateValue('n_max', $selection_size);
                }
            }
            else if ($n_max < 800 && $selection_size < $n_max) {
                // Reduce n_max to the selection size
                self::setGameStateValue('n_max', $selection_size);
            }
        }
        // Let the player make his choice
        self::trace('preSelectionMove->selectionMove');
        $this->gamestate->nextState('selectionMove');
    }
    
    function stInterSelectionMove() {
        $player_id = self::getGameStateValue('current_player_under_dogma_effect');
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
            $bottom_to = self::getGameStateValue('bottom_to') == 1;
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
        
        $launcher_id = self::getGameStateValue('active_player');
        $nested_id_1 = self::getGameStateValue('nested_id_1');
        $card_id = $nested_id_1 == -1 ? self::getGameStateValue('dogma_card_id') : $nested_id_1 ;
        $current_effect_type = $nested_id_1 == -1 ? self::getGameStateValue('current_effect_type') : 1 /* Non-demand effects only*/;
        $current_effect_number = $nested_id_1 == -1 ?  self::getGameStateValue('current_effect_number') : self::getGameStateValue('nested_current_effect_number_1');
        $step = self::getGameStateValue('step');
        
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
            $code = $card_id . self::getLetterForEffectType($current_effect_type) . $current_effect_number . $letters[$step];
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
                // $choice is the chosen oppenent id
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
                $different_values_selected_so_far = self::getGameStateValueAsArray('auxiliary_value');
                if (!in_array($card['age'], $different_values_selected_so_far)) { // The player choose to return a card of a new value
                    $different_values_selected_so_far[] = $card['age'];
                    self::setGameStateValueFromArray('auxiliary_value', $different_values_selected_so_far);
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
                self::setGameStateValue('auxiliary_value', $card['id']);
                break;
            
            // id 50, age 5: Measurement
            case "50N1B":
                // $choice is a color
                self::notifyPlayer($player_id, 'log', clienttranslate('${You} choose ${color}.'), array('i18n' => array('color'), 'You' => 'You', 'color' => self::getColorInClear($choice)));
                self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} chooses ${color}.'), array('i18n' => array('color'), 'player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id), 'color' => self::getColorInClear($choice)));
                $number_of_cards = self::countCardsInLocation($player_id, 'board', false, true);
                $number_of_cards = $number_of_cards[$choice];
                if (self::getCurrentSplayDirection($player_id, $choice) != 2 /* right */ && $number_of_cards > 1) {
                    self::splay($player_id, $choice, 2 /* right */); // "Splay that color of your cards right"
                }
                if ($number_of_cards <= 1) {
                    self::notifyPlayer($player_id, 'log', clienttranslate('${You} have ${n} ${colored} card.'), array('i18n' => array('n', 'colored'), 'You' => 'You', 'n' => self::getTranslatedNumber($number_of_cards), 'colored' => self::getColorInClear($choice)));
                    self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} has  ${n} ${colored} card.'), array('i18n' => array('n', 'colored'), 'player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id), 'n' => self::getTranslatedNumber($number_of_cards), 'colored' => self::getColorInClear($choice)));
                }
                else { // $number_of_cards > 1
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
                }
                else { // Draw and tuck
                    self::notifyPlayer($player_id, 'log', clienttranslate('${You} decide to tuck.'), array('You' => 'You'));
                    self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} decides to tuck.'), array('player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id)));
                    
                    self::executeDraw($player_id, 6, 'board', true); // "Draw and tuck a 6"
                    
                    // Make the transfers
                    for($color=0; $color<5; $color++) {
                        $card = self::getTopCardOnBoard($player_id, $color);
                        if ($card !== null && !self::hasRessource($card, 5)) {
                            self::transferCardFromTo($card, $player_id, 'score', false, true); // "Score all your top cards without a factory
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
                    self::incGameStateValue('step_max', 1); // --> 1 more interaction: see B
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
                self::setGameStateValue('auxiliary_value', $card['id']);
                break;
            
            // id 73, age 7: Lighting
            case "73N1A":
                $different_values_selected_so_far = self::getGameStateValueAsArray('auxiliary_value');
                if (!in_array($card['age'], $different_values_selected_so_far)) { // The player choose to tuck a card of a new value
                    $different_values_selected_so_far[] = $card['age'];
                    self::setGameStateValueFromArray('auxiliary_value', $different_values_selected_so_far);
                }
                // Do the transfer as stated in B (tuck)
                self::transferCardFromTo($card, $owner_to, $location_to, $bottom_to, $score_keyword);
                break;
            
            // id 80, age 8, Mass media
            case "80N1B":
                self::notifyPlayer($player_id, 'log', clienttranslate('${You} choose the value ${age}.'), array('You' => 'You', 'age' => self::getAgeSquare($choice)));
                self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} chooses the value ${age}.'), array('player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id), 'age' => self::getAgeSquare($choice)));
                self::setGameStateValue('auxiliary_value', $choice);
                break;
                
            // id 81, age 8: Antibiotics
            case "81N1A":
                $different_values_selected_so_far = self::getGameStateValueAsArray('auxiliary_value');
                if (!in_array($card['age'], $different_values_selected_so_far)) { // The player choose to return a card of a new value
                    $different_values_selected_so_far[] = $card['age'];
                    self::setGameStateValueFromArray('auxiliary_value', $different_values_selected_so_far);
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
                    self::setGameStateValue('auxiliary_value', $card['color']); // Flag the sucessful colors
                    self::incGameStateValue('step_max', 1);  // --> 1 more interaction: see B
                }
                break;
            
            // id 84, age 8: Socialism     
            case "84N1A":
                if ($card['color'] == 4 /* purple*/) { // A purple card has been tucked
                    self::setGameStateValue('auxiliary_value', 1); // Flag that
                }
                // Do the transfer as stated in B (tuck)
                self::transferCardFromTo($card, $owner_to, $location_to, $bottom_to, $score_keyword);
                break;
            
            // id 100, age 10: Self service
            case "100N1A":
                self::checkAndPushCardIntoNestedDogmaStack($card); // The player chose this card for execution
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
                        self::transferCardFromTo($card, $player_id, 'score', false, true); // Nota: this has a score keyword 
                    }
                }                
                break;
            
            // id 124, Artifacts age 1: Tale of the Shipwrecked Sailor
            case "124N1A":
                self::notifyPlayer($player_id, 'log', clienttranslate('${You} choose ${color}.'), array('i18n' => array('color'), 'You' => 'You', 'color' => self::getColorInClear($choice)));
                self::notifyAllPlayersBut($player_id, 'log', clienttranslate('${player_name} chooses ${color}.'), array('i18n' => array('color'), 'player_name' => self::getColoredText(self::getPlayerNameFromId($player_id), $player_id), 'color' => self::getColorInClear($choice)));
                // Save the color choice for later (after a card is drawn).
                self::setGameStateValue('auxiliary_value', $choice);
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
                    self::splay($player_id, $card['color'], $splay_direction);
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
        
        $achievements = self::attachTextualInfoToList(self::getObjectListFromDB("SELECT * FROM card WHERE location = 'achievements' AND age IS NOT NULL ORDER BY age"));
        foreach($achievements as $card) {
            self::notifyGeneralInfo(clienttranslate('The achievement ${<<<}${achievement_name}${>>>} was ${<}${age}${>} ${<<}${name}${>>}.'), array(
                'i18n' => array('achievement_name', 'name'), 
                'age' => $card['age'],
                'achievement_name' => self::getNormalAchievementName($card['age']), 'name' => $card['name']));
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

    function zombieTurn($state, $active_player)
    {
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

