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
 * gameoptions.inc.php
 *
 * Innovation game options description
 * 
 * In this file, you can define your game options (= game variants).
 *   
 * Note: If your game has no variant, you don't have to modify this file.
 *
 * Note²: All options defined in this file should have a corresponding "game state labels"
 *        with the same ID (see "initGameStateLabels" in innovation.game.php)
 *
 * !! It is not a good idea to modify this file when a game is running !!
 *
 */

$game_options = array(

    /* Example of game variant:
    
    
    // note: game variant ID should start at 100 (ie: 100, 101, 102, ...). The maximum is 199.
    100 => array(
                'name' => totranslate('my game option'),    
                'values' => array(

                            // A simple value for this option:
                            1 => array( 'name' => totranslate('option 1') )

                            // A simple value for this option.
                            // If this value is chosen, the value of "tmdisplay" is displayed in the game lobby
                            2 => array( 'name' => totranslate('option 2'), 'tmdisplay' => totranslate('option 2') ),

                            // Another value, with other options:
                            //  beta=true => this option is in beta version right now.
                            //  nobeginner=true  =>  this option is not recommended for beginners
                            3 => array( 'name' => totranslate('option 3'),  'beta' => true, 'nobeginner' => true ),) )
                        )
            )

    */
    100 => array(
        'name' => totranslate('Game type'),
        'values' => array(
            1 => array('name' => totranslate('Classic game')),
            2 => array('name' => totranslate('2 vs 2, random teams'), 'tmdisplay' => totranslate('Team game (2 vs 2)'), 'beta' => true, 'no_beginner' => true),
            3 => array('name' => totranslate('2 vs 2, by table order (1st/2nd vs 3rd/4th)'), 'tmdisplay' => totranslate('Team game (2 vs 2)'), 'beta' => true, 'no_beginner' => true),
            4 => array('name' => totranslate('2 vs 2, by table order (1st/3rd vs 2nd/4th)'), 'tmdisplay' => totranslate('Team game (2 vs 2)'), 'beta' => true, 'no_beginner' => true),
            5 => array('name' => totranslate('2 vs 2, by table order (1st/4th vs 2nd/3rd)'), 'tmdisplay' => totranslate('Team game (2 vs 2)'), 'beta' => true, 'no_beginner' => true)
        ),
        'startcondition' => array(
            1 => array(/* No special condition here */),
            2 => array(
                array('type' => 'minplayers', 'value' => 4, 'message' => totranslate('Team game is only available for 4 players, please switch back to "Classic Game"')),
                array('type' => 'maxplayers', 'value' => 4, 'message' => totranslate('Team game is only available for 4 players, please switch back to "Classic Game"'))
            ),
            3 => array(
                array('type' => 'minplayers', 'value' => 4, 'message' => totranslate('Team game is only available for 4 players, please switch back to "Classic Game"')),
                array('type' => 'maxplayers', 'value' => 4, 'message' => totranslate('Team game is only available for 4 players, please switch back to "Classic Game"'))
            ),
            4 => array(
                array('type' => 'minplayers', 'value' => 4, 'message' => totranslate('Team game is only available for 4 players, please switch back to "Classic Game"')),
                array('type' => 'maxplayers', 'value' => 4, 'message' => totranslate('Team game is only available for 4 players, please switch back to "Classic Game"'))
            ),
            5 => array(
                array('type' => 'minplayers', 'value' => 4, 'message' => totranslate('Team game is only available for 4 players, please switch back to "Classic Game"')),
                array('type' => 'maxplayers', 'value' => 4, 'message' => totranslate('Team game is only available for 4 players, please switch back to "Classic Game"'))
            )
        )
    ),
    
    101 => array(
        'name' => totranslate('Game rules'),
        'values' => array(
            1 => array('name' => totranslate('Last edition')),
            2 => array('name' => totranslate('First edition'), 'tmdisplay' => totranslate('First edition')),
        ),
    ),

    102 => array(
        'name' => totranslate('Artifacts expansion'),
        'values' => array(
            1 => array('name' => totranslate('Disable')),
            2 => array('name' => totranslate('Enable without Relics'), 'no_beginner' => true),
            3 => array('name' => totranslate('Enable with Relics'), 'no_beginner' => true)
        )
    )
);


