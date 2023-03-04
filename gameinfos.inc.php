<?php

$gameinfos = [

    // Game designer (or game designers, separated by commas)
    'designer' => 'Carl Chudyk',       

    // Game artist (or game artists, separated by commas)
    'artist' => 'Cara Judd',         

    // Year of FIRST publication of this game. Can be negative.
    'year' => 2010,

    // Game publisher
    'publisher' => 'Asmadi Games',

    // Url of game publisher website
    'publisher_website' => 'https://asmadigames.com/',

    // Board Game Geek ID of the publisher
    'publisher_bgg_id' => 5407,

    // Board game geek if of the game
    'bgg_id' => 63888,

    // Players configuration that can be played
    'players' => [2, 3, 4, 5],

    // Suggest players to play with this number of players. Must be null if there is no such advice, or if there is only one possible player configuration.
    'suggest_player_number' => null,

    // Discourage players to play with these numbers of players. Must be null if there is no such advice.
    'not_recommend_player_number' => null,

    // Estimated game duration, in minutes (used only for the launch, afterward the real duration is computed)
    'estimated_duration' => 40,           

    // Time in second add to a player when "giveExtraTime" is called (speed profile = fast)
    'fast_additional_time' => 120,           

    // Time in second add to a player when "giveExtraTime" is called (speed profile = medium)
    'medium_additional_time' => 240,           

    // Time in second add to a player when "giveExtraTime" is called (speed profile = slow)
    'slow_additional_time' => 480,

    // This description will be used as a tooltip to explain the tie breaker to the players.
    'tie_breaker_description' => totranslate("Number of achievements if the game ended by score. Else this value is set to zero and is irrelevant: there is no tie breaker."),

    // The game end result will display "Winner" for the 1st player(s) and "Loser" for all other players
    'losers_not_ranked' => true,

    // Randomize the order (solves the bug where a rematch with the same players wasn't actually randomizing the order and was only randomizing the starting player)
    'disable_player_order_swap_on_rematch' => true,

    // Game is "beta". A game MUST set is_beta=1 when published on BGA for the first time, and must remains like this until all bugs are fixed.
    'is_beta' => 1,                     

    // Is this game cooperative (all players wins together or loose together)
    'is_coop' => 0, 

    // Complexity of the game, from 0 (extremely simple) to 5 (extremely complex)
    'complexity' => 4,    

    // Luck of the game, from 0 (absolutely no luck in this game) to 5 (totally luck driven)
    'luck' => 2,    

    // Strategy of the game, from 0 (no strategy can be setup) to 5 (totally based on strategy)
    'strategy' => 4,    

    // Diplomacy of the game, from 0 (no interaction in this game) to 5 (totally based on interaction and discussion between players)
    'diplomacy' => 3,    

    // Games categories
    //  You can attribute any number of "tags" to your game.
    //  Each tag has a specific ID (ex: 22 for the category "Prototype", 101 for the tag "Science-fiction theme game")
    // @see https://en.doc.boardgamearena.com/Game_meta-information:_gameinfos.inc.php#Tags
    'tags' => [
        3, // for regular players
        12, // long game >30m
        20, // awarded game
        102, // historical
        106, // building
        200, // cards
        207, // combos
        208, // area majority
        209, // race
        210, // collection
    ],

    // Favorite colors support
    'favorite_colors_support' => true,
 
    // Game interface width range (pixels)
    // Note: game interface = space on the left side, without the column on the right
    'game_interface_width' => array(

        // Minimum width
        //  default: 740
        //  maximum possible value: 740 (ie: your game interface should fit with a 740px width (correspond to a 1024px screen)
        //  minimum possible value: 320 (the lowest value you specify, the better the display is on mobile)
        'min' => 640,

        // Maximum width
        //  default: null (ie: no limit, the game interface is as big as the player's screen allows it).
        //  maximum possible value: unlimited
        //  minimum possible value: 740
        'max' => null
    ),

    // Game presentation
    // Short game presentation text that will appear on the game description page, structured as an array of paragraphs.
    // Each paragraph must be wrapped with totranslate() for translation and should not contain html (plain text without formatting).
    // A good length for this text is between 100 and 150 words (about 6 to 9 lines on a standard display)
    'presentation' => [
        totranslate("This game by Carl Chudyk is a journey through innovations from the stone age through modern times. Each player builds a civilization based on various technologies, ideas, and cultural advancements, all represented by cards. Each of these cards has a unique power which will allow further advancement, point scoring, or even attacking other civilizations. Be careful though, as other civilizations may be able to benefit from your ideas as well!"),
        totranslate("To win, you must score achievements, which you can attain by amassing points or by meeting certain criteria with the innovations you have built. Plan your civilization well, and outmaneuver your opponents, and with some luck you will achieve victory!"),
    ],
];
