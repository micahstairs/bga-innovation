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
 * innovation.view.php
 *
 * This is your "view" file.
 *
 * The method "build_page" below is called each time the game interface is displayed to a player, ie:
 * _ when the game starts
 * _ when a player refreshes the game page (F5)
 *
 * "build_page" method allows you to dynamically modify the HTML generated for the game interface. In
 * particular, you can set here the values of variables elements defined in innovation_innovation.tpl (elements
 * like {MY_VARIABLE_ELEMENT}), and insert HTML block elements (also defined in your HTML template file)
 *
 * Note: if the HTML of your game interface is always the same, you don't have to place anything here.
 *
 */
  
  require_once( APP_BASE_PATH."view/common/game.view.php" );
  
  class view_innovation_innovation extends game_view
  {
    function getGameName() {
        return "innovation";
    }
    
    function hex2rgb($hex) {
    $hex = str_replace("#", "", $hex);

       if(strlen($hex) == 3) {
          $r = hexdec(substr($hex,0,1).substr($hex,0,1));
          $g = hexdec(substr($hex,1,1).substr($hex,1,1));
          $b = hexdec(substr($hex,2,1).substr($hex,2,1));
       } else {
          $r = hexdec(substr($hex,0,2));
          $g = hexdec(substr($hex,2,2));
          $b = hexdec(substr($hex,4,2));
       }
       $rgb = array($r, $g, $b);
       return $rgb; // returns an array with the rgb values
    }
    
      function build_page( $viewArgs )
      {        
          // Get players
        $players = $this->game->loadPlayersBasicInfos();
        
        // Get my id
        global $g_user;
        $my_id = $g_user->get_id();

        /*********** Place your code below:  ************/

        $this->page->begin_block( "innovation_innovation", "pile" );
        $this->page->begin_block( "innovation_innovation", "player" );
        $this->page->begin_block( "innovation_innovation", "decks_group_1_1" );
        $this->page->begin_block( "innovation_innovation", "decks_group_2_1" );
        $this->page->begin_block( "innovation_innovation", "decks_group_3_1" );
        $this->page->begin_block( "innovation_innovation", "decks_group_4_1" );
        $this->page->begin_block( "innovation_innovation", "decks_group_5_1" );
        $this->page->begin_block( "innovation_innovation", "decks_group_1_2" );
        $this->page->begin_block( "innovation_innovation", "decks_group_2_2" );
        $this->page->begin_block( "innovation_innovation", "decks_group_3_2" );
        $this->page->begin_block( "innovation_innovation", "decks_group_4_2" );
        $this->page->begin_block( "innovation_innovation", "decks_group_5_2" );
        $this->page->begin_block( "innovation_innovation", "decks" );
        $this->page->begin_block( "innovation_innovation", "available_relics" );
        $this->page->begin_block( "innovation_innovation", "available_achievements" );
        $this->page->begin_block( "innovation_innovation", "special_achievements" );
        
        // Players
        // Me
        for($color = 0; $color<5; $color++)
        {
            $this->page->insert_block( "pile", array( 
                                                    "PLAYER_ID" => $my_id,
                                                    "COLOR" => $color
                                                        ) );
        }
        
        if (array_key_exists($my_id, $players)) { // That is if I'm not a spectator
            $me = $players[$my_id];
            $rgb = self::hex2rgb($me['player_color']);
            $this->page->insert_block( "player", array( 
                                                    "PLAYER_ID" => $my_id,
                                                    "PLAYER_NAME" => self::_("You"),
                                                    "PLAYER_COLOR" => $me['player_color'] . "; display:none",
                                                    "PLAYER TEAM" => $this->game->getGameStateValue('game_type') > 1 ? " - " . ($me['player_color'] == "0000ff" ? _("Blue team") : _("Red team")): "",
                                                    "R" => $rgb[0],
                                                    "G" => $rgb[1],
                                                    "B" => $rgb[2],
                                                    "OPT_FORECAST_CLASS" => " class='forecast_show_window'",
                                                    "OPT_SCORE_CLASS" => " class='score_show_window'",
                                                    "HAND" => self::_("Hand"),
                                                    "DISPLAY" => self::_("Artifact on Display"),
                                                    "FORECAST_PILE" => self::_("Forecast"),
                                                    "SCORE_PILE" => self::_("Score pile"),
                                                    "ACHIEVEMENTS" => self::_("Achievements")
                                                    ) );
            // Opponents
            // We have to reorganize players array so that it reflects the real turn order beginning from me
            $players_with_order = array();
            foreach( $players as $player_id => $player ) {
                $player['player_id'] = $player_id;
                $players_with_order[] = $player;
            }
            
            while($players_with_order[0]['player_id'] != $my_id)
            {
                // Roll the array
                $player = array_shift($players_with_order);
                $players_with_order[] = $player;
            }
            
            $players = array();
            foreach($players_with_order as $player) {
                $players[$player['player_id']] = $player;
            }
        }
        
        // Now the order is good and it's the same as in the player panel
        foreach( $players as $player_id => $player )
        {
            if ($player_id == $my_id) {
                continue; // Skip me
            }
            $this->page->reset_subblocks( "pile" ); 
                                                     
            for($color = 0; $color<5; $color++)
            {
                $this->page->insert_block( "pile", array( 
                                                        "PLAYER_ID" => $player_id,
                                                        "COLOR" => $color
                                                         ) );
            }
            
            $rgb = self::hex2rgb($player['player_color']);
            
            $this->page->insert_block( "player", array( 
                                        "PLAYER_ID" => $player_id,
                                        "PLAYER_NAME" => $player['player_name'],
                                        "PLAYER_COLOR" => $player['player_color'],
                                        "PLAYER TEAM" => $this->game->getGameStateValue('game_type') > 1 ? " - " . ($player['player_color'] == "0000ff" ? _("Blue team") : _("Red team")): "",
                                        "R" => $rgb[0],
                                        "G" => $rgb[1],
                                        "B" => $rgb[2],
                                        "OPT_FORECAST_CLASS" => "",
                                        "OPT_SCORE_CLASS" => "",
                                        "HAND" => self::_("Hand"),
                                        "DISPLAY" => self::_("Artifact on Display"),
                                        "SCORE_PILE" => self::_("Score pile"),
                                        "ACHIEVEMENTS" => self::_("Achievements"),
                                         ) );
        }
        
        for ($age = 1; $age <= 5; $age++) {
            $this->page->insert_block("decks_group_1_1", array("TYPE" => 0, "AGE" => $age));
        }
        for ($age = 1; $age <= 5; $age++) {
            $this->page->insert_block("decks_group_2_1", array("TYPE" => 1, "AGE" => $age));
        }
        for ($age = 1; $age <= 5; $age++) {
            $this->page->insert_block("decks_group_3_1", array("TYPE" => 2, "AGE" => $age));
        }
        for ($age = 1; $age <= 5; $age++) {
            $this->page->insert_block("decks_group_4_1", array("TYPE" => 3, "AGE" => $age));
        }
        for ($age = 1; $age <= 5; $age++) {
            $this->page->insert_block("decks_group_5_1", array("TYPE" => 4, "AGE" => $age));
        }
        for ($age = 6; $age <= 11; $age++) {
            $this->page->insert_block("decks_group_1_2", array("TYPE" => 0, "AGE" => $age));
        }
        for ($age = 6; $age <= 11; $age++) {
            $this->page->insert_block("decks_group_2_2", array("TYPE" => 1, "AGE" => $age));
        }
        for ($age = 6; $age <= 11; $age++) {
            $this->page->insert_block("decks_group_3_2", array("TYPE" => 2, "AGE" => $age));
        }
        for ($age = 6; $age <= 11; $age++) {
            $this->page->insert_block("decks_group_4_2", array("TYPE" => 3, "AGE" => $age));
        }
        for ($age = 6; $age <= 11; $age++) {
            $this->page->insert_block("decks_group_5_2", array("TYPE" => 4, "AGE" => $age));
        }
        $this->page->insert_block( "decks", array("DECKS" => self::_("Decks")) );
        $this->page->insert_block( "available_relics", array("AVAILABLE_RELICS" => self::_("Available relics")) );
        $this->page->insert_block( "available_achievements", array("AVAILABLE_ACHIEVEMENTS" => self::_("Available achievements")) );
        $this->page->insert_block( "special_achievements", array("SPECIAL_ACHIEVEMENTS" => self::_("Special achievements")) );
      }
  }
  

