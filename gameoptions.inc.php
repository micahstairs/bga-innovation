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
                array('type' => 'minplayers', 'value' => 4, 'message' => totranslate('Team game is only available for 4 players')),
                array('type' => 'maxplayers', 'value' => 4, 'message' => totranslate('Team game is only available for 4 players'))
            ),
            3 => array(
                array('type' => 'minplayers', 'value' => 4, 'message' => totranslate('Team game is only available for 4 players')),
                array('type' => 'maxplayers', 'value' => 4, 'message' => totranslate('Team game is only available for 4 players'))
            ),
            4 => array(
                array('type' => 'minplayers', 'value' => 4, 'message' => totranslate('Team game is only available for 4 players')),
                array('type' => 'maxplayers', 'value' => 4, 'message' => totranslate('Team game is only available for 4 players'))
            ),
            5 => array(
                array('type' => 'minplayers', 'value' => 4, 'message' => totranslate('Team game is only available for 4 players')),
                array('type' => 'maxplayers', 'value' => 4, 'message' => totranslate('Team game is only available for 4 players'))
            )
        ),
    ),
    
    101 => array(
        'name' => totranslate('Game rules'),
        'values' => array(
            1 => array('name' => totranslate('Last edition')),
            2 => array('name' => totranslate('First edition'), 'tmdisplay' => totranslate('First edition')),
        ),
        'startcondition' => array(
            1 => array(/* No special condition here */),
            2 => array(
                /* TODO(FIGURES): Add more conditions when other expansions are added. */
                array('type' => 'otheroption', 'id' => 102, 'value' => 1, 'message' => totranslate('First edition rules cannot be used when playing with expansions')),
                array('type' => 'otheroption', 'id' => 103, 'value' => 1, 'message' => totranslate('First edition rules cannot be used when playing with expansions')),
                array('type' => 'otheroption', 'id' => 104, 'value' => 1, 'message' => totranslate('First edition rules cannot be used when playing with expansions')),
            ),
        )
    ),

    102 => array(
        'name' => totranslate('Artifacts of History expansion'),
        'values' => array(
            1 => array('name' => totranslate('Disable')),
            2 => array(
                'name' => totranslate('Enable without Relics'),
                'no_beginner' => true,
                'beta' => true,
                'tmdisplay' => totranslate('Artifacts Expansion'),
            ),
            3 => array(
                'name' => totranslate('Enable with Relics'),
                'no_beginner' => true,
                'beta' => true,
                'tmdisplay' => totranslate('Artifacts Expansion with Relics')
            ),
        )
    ),

    103 => array(
        'name' => totranslate('Cities of Destiny expansion'),
        'values' => array(
            1 => array('name' => totranslate('Disable')),
            2 => array('name' => totranslate('Enable'), 'no_beginner' => true, 'tmdisplay' => totranslate('Cities Expansion'))
        )
    ),

    104 => array(
        'name' => totranslate('Echoes of the Past expansion'),
        'values' => array(
            1 => array('name' => totranslate('Disable')),
            2 => array('name' => totranslate('Enable'), 'no_beginner' => true, 'tmdisplay' => totranslate('Echoes Expansion'))
        ),
    ),

    /* TODO(FIGURES): Add game options.
    105 => array(
        'name' => totranslate('Figures in the Sand expansion'),
        'values' => array(
            1 => array('name' => totranslate('Disable')),
            2 => array('name' => totranslate('Enable'), 'no_beginner' => true, 'tmdisplay' => totranslate('Figures Expansion'))
        )
    ),*/

    110 => array(
        'name' => totranslate('Extra achievement to win'),
        'values' => array(
            1 => array(
                'name' => totranslate('Disable'),
                'description' => totranslate('An extra achievement will still be added to the win requirement for each enabled expansion'),
            ),
            2 => array(
                'name' => totranslate('Enable'),
                'tmdisplay' => totranslate('Extra Achievement'),
                'description' => totranslate('An extra achievement will be added to the win requirement (in addition to an achievement being added for each enabled expansion)'),
                'no_beginner' => true,
            ),
        )
    )
);

$game_preferences = array(
    112 => array(
        'name' => totranslate('Position of decks and achievements'),
        'needReload' => true,
        'values' => array(
            1 => array('name' => totranslate('Automatic')),
            2 => array('name' => totranslate('Right')),
            3 => array('name' => totranslate('Bottom')),
        ),
        'default' => 1,
    ),
    100 => array(
        'name' => totranslate('Dogma confirmation'),
        'needReload' => true,
        'values' => array(
            1 => array('name' => totranslate('Disabled')),
            2 => array('name' => totranslate('Enabled (short timer)')),
            3 => array('name' => totranslate('Enabled (medium timer)')),
            4 => array('name' => totranslate('Enabled (long timer)')),
        ),
        'default' => 1
    ),
    102 => array(
        'name' => totranslate('Sharing confirmation'),
        'needReload' => true,
        'values' => array(
            1 => array('name' => totranslate('Disabled')),
            2 => array('name' => totranslate('Enabled')),
        ),
        'default' => 2
    ),
    101 => array(
        'name' => totranslate('Meld confirmation'),
        'needReload' => true,
        'values' => array(
            1 => array('name' => totranslate('Disabled')),
            2 => array('name' => totranslate('Enabled (short timer)')),
            3 => array('name' => totranslate('Enabled (medium timer)')),
            4 => array('name' => totranslate('Enabled (long timer)')),
        ),
        'default' => 1
    ),
    110 => array(
        'name' => totranslate('Simplified card backs'),
        'needReload' => true,
        'values' => array(
            1 => array('name' => totranslate('Disabled')),
            2 => array('name' => totranslate('Enabled')),
        ),
        'default' => 1,
    ),
    111 => array(
        'name' => totranslate('Card appearance'),
        'needReload' => true,
        'values' => array(
            1 => array('name' => totranslate('First edition')),
            2 => array('name' => totranslate('Third edition')),
        ),
        'default' => 2,
    ),
);
