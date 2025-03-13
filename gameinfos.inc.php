<?php

$gameinfos = [

    // Game publisher
    'publisher'                            => 'Asmadi Games',

    // Url of game publisher website
    'publisher_website'                    => 'https://asmadigames.com/',

    // Board Game Geek ID of the publisher
    'publisher_bgg_id'                     => 5407,

    // Board game geek if of the game
    'bgg_id'                               => 63888,

    // Players configuration that can be played
    'players'                              => [2, 3, 4, 5],

    // Suggest players to play with this number of players. Must be null if there is no such advice, or if there is only one possible player configuration.
    'suggest_player_number'                => null,

    // Discourage players to play with these numbers of players. Must be null if there is no such advice.
    'not_recommend_player_number'          => null,

    // Estimated game duration, in minutes (used only for the launch, afterward the real duration is computed)
    'estimated_duration'                   => 40,

    // Time in second add to a player when "giveExtraTime" is called (speed profile = fast)
    'fast_additional_time'                 => 120,

    // Time in second add to a player when "giveExtraTime" is called (speed profile = medium)
    'medium_additional_time'               => 240,

    // Time in second add to a player when "giveExtraTime" is called (speed profile = slow)
    'slow_additional_time'                 => 480,

    // This description will be used as a tooltip to explain the tie breaker to the players.
    'tie_breaker_description'              => totranslate("Number of achievements if the game ended by score. Else this value is set to zero and is irrelevant: there is no tie breaker."),

    // The game end result will display "Winner" for the 1st player(s) and "Loser" for all other players
    'losers_not_ranked'                    => true,

    // Randomize the order (solves the bug where a rematch with the same players wasn't actually randomizing the order and was only randomizing the starting player)
    'disable_player_order_swap_on_rematch' => true,

    // Game is "beta". A game MUST set is_beta=1 when published on BGA for the first time, and must remains like this until all bugs are fixed.
    'is_beta'                              => 1,

    // Is this game cooperative (all players wins together or loose together)
    'is_coop'                              => 0,

    // Favorite colors support
    'favorite_colors_support'              => true,

    // Game interface width range (pixels)
    // Note: game interface = space on the left side, without the column on the right
    'game_interface_width'                 => array(

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

];
