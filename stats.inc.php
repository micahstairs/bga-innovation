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
 * stats.inc.php
 *
 * Innovation game statistics description
 *
 */

/*
    In this file, you are describing game statistics, that will be displayed at the end of the
    game.
    
    !! After modifying this file, you must use "Reload  statistics configuration" in BGA Studio backoffice ("Your game configuration" section):
    http://en.studio.boardgamearena.com/admin/studio
    
    There are 2 types of statistics:
    _ table statistics, that are not associated to a specific player (ie: 1 value for each game).
    _ player statistics, that are associated to each players (ie: 1 value for each player in the game).

    Statistics types can be "int" for integer, "float" for floating point values, and "bool" for boolean
    
    Once you defined your statistics there, you can start using "initStat", "setStat" and "incStat" method
    in your game logic, using statistics names defined below.
    
    !! It is not a good idea to modify this file when a game is running !!

    If your game is already public on BGA, please read the following before any change:
    http://en.doc.boardgamearena.com/Post-release_phase#Changes_that_breaks_the_games_in_progress
    
    Notes:
    * Statistic index is the reference used in setStat/incStat/initStat PHP method
    * Statistic index must contains alphanumerical characters and no space. Example: 'turn_played'
    * Statistics IDs must be >=10
    * Two table statistics can't share the same ID, two player statistics can't share the same ID
    * A table statistic can have the same ID than a player statistics
    * Statistics ID is the reference used by BGA website. If you change the ID, you lost all historical statistic data. Do NOT re-use an ID of a deleted statistic
    * Statistic name is the English description of the statistic as shown to players
    
*/

$stats_type = array(

    // Statistics global to table
    "table" => array(

        "turns_number" => array("id"=> 10,
                    "name" => totranslate("Number of turns"),
                    "type" => "int" ),
                    
        "actions_number" => array("id"=> 11,
                    "name" => totranslate("Number of actions"),
                    "type" => "int" ),
                    
        "end_achievements" => array("id"=> 12,
                    "name" => totranslate("End of game by achievement?"),
                    "type" => "bool" ),
                    
        "end_score" => array("id"=> 13,
                    "name" => totranslate("End of game by score?"),
                    "type" => "bool" ),

        "end_dogma" => array("id"=> 14,
                    "name" => totranslate("End of game by dogma?"),
                    "type" => "bool" ),

        "fission_triggered" => array("id"=> 15,
                    "name" => totranslate("Fission card removal triggered?"),
                    "type" => "bool" ),
    
    ),
    
    // Statistics existing for each player
    "player" => array(
        "achievements_number" => array("id"=> 10,
                    "name" => totranslate("Number of achievements"),
                    "type" => "int" ),
                    
        "score" => array("id"=> 11,
                    "name" => totranslate("Final score"),
                    "type" => "int" ),
                    
        "max_age_on_board" => array("id"=> 12,
                    "name" => totranslate("Final max age on board top cards"),
                    "type" => "int" ),

        "turns_number" => array("id"=> 13,
                    "name" => totranslate("Number of turns"),
                    "type" => "int" ),
                    
        "actions_number" => array("id"=> 14,
                    "name" => totranslate("Number of actions"),
                    "type" => "int" ),
                    
        "draw_actions_number" => array("id"=> 15,
                    "name" => totranslate("Number of draw actions"),
                    "type" => "int" ),
                    
        "meld_actions_number" => array("id"=> 16,
                    "name" => totranslate("Number of meld actions"),
                    "type" => "int" ),
                    
        "dogma_actions_number" => array("id"=> 17,
                    "name" => totranslate("Number of dogma actions"),
                    "type" => "int" ),
                    
        "achieve_actions_number" => array("id"=> 18,
                    "name" => totranslate("Number of achieve actions"),
                    "type" => "int" ),
                    
        "special_achievements_number" => array("id"=> 19,
                    "name" => totranslate("Number of claimed special achievements"),
                    "type" => "int" ),
                    
        "dogma_actions_number_with_i_demand" => array("id"=> 20,
                    "name" => totranslate("Number of dogma actions with an effective \"I demand\" effect"),
                    "type" => "int" ),
                    
        "dogma_actions_number_with_sharing" => array("id"=> 21,
                    "name" => totranslate("Number of dogma actions with an effective sharing"),
                    "type" => "int" ),
                    
        "i_demand_effects_number" => array("id"=> 22,
                    "name" => totranslate("Number of times the player has effectivly executed the \"I demand\" effect of an opponent card"),
                    "type" => "int" ),
                    
        "sharing_effects_number" => array("id"=> 23,
                    "name" => totranslate("Number of times the player has effectivly shared the effects of an opponent card"),
                    "type" => "int" ),
        
        // Artifacts-specific 
        "dig_events_number" => array("id"=> 24,
                    "name" => totranslate("Number of artifacts dug"),
                    "type" => "int" ),

        "free_action_dogma_number" => array("id"=> 25,
                    "name" => totranslate("Number of times that an artifact on display was executed"),
                    "type" => "int" ),

        "free_action_return_number" => array("id"=> 26,
                    "name" => totranslate("Number of artifacts on display returned without being used"),
                    "type" => "int" ),

        "free_action_pass_number" => array("id"=> 27,
                    "name" => totranslate("Number of turns that an artifact on display was not used or returned"),
                    "type" => "int" ),

        "dogma_actions_number_targeting_artifact_on_board" => array("id"=> 28,
                    "name" => totranslate("Number of dogma actions targeting an artifact on your board"),
                    "type" => "int" ),

        "dogma_actions_number_with_i_compel" => array("id"=> 29,
                    "name" => totranslate("Number of dogma actions with an effective \"I compel\" effect"),
                    "type" => "int" ),

        "i_compel_effects_number" => array("id"=> 30,
                    "name" => totranslate("Number of times the player has been forced to execute the \"I compel\" effect of an opponent's card"),
                    "type" => "int" ),
    
        // Artifacts + Relics specific
        "relics_seized_number" => array("id"=> 31,
                    "name" => totranslate("Number of relics seized by player"),
                    "type" => "int" ),

        "relics_stolen_number" => array("id"=> 32,
                    "name" => totranslate("Number of relics stolen from player"),
                    "type" => "int" ),
                    
    )

);
