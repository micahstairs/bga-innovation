{OVERALL_GAME_HEADER}

<!-- 
--------
-- BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
-- Innovation implementation : © Jean Portemer <jportemer@gmail.com>
-- 
-- This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
-- See http://en.boardgamearena.com/#!doc/Studio for more information.
-------
-->
<div id="main_area_wrapper">
    <div id="main_area">
        <span></span>
        <!-- BEGIN player -->
            <div id="player_{PLAYER_ID}" class="player whiteblock">
                <p id="name_{PLAYER_ID}" style="color:#{PLAYER_COLOR};" class='player_name'>{PLAYER_NAME}<span>{PLAYER TEAM}</span></p>
                <div id="board_{PLAYER_ID}" class="board">
                    <!-- BEGIN pile -->
                        <div class="pile_container">
                            <div class="pile board_{PLAYER_ID}" id="board_{PLAYER_ID}_{COLOR}"><div class="pile_count" id="pile_count_{PLAYER_ID}_{COLOR}"></div>
                            </div>
                            <div class="splay_indicator" id="splay_indicator_{PLAYER_ID}_{COLOR}"></div>
                        </div>
                    <!-- END pile -->
                </div>
                
                <div id="revealed_container_{PLAYER_ID}" class="revealed_container">
                    <div id="revealed_{PLAYER_ID}" class="revealed"></div>
                </div>
                <div id="display_container_{PLAYER_ID}" class="display_container">
                    <div style="background-color:rgba({R}, {G}, {B}, .2);">
                        <p>{DISPLAY}</p>
                        <div id="display_{PLAYER_ID}" class="display"></div>
                    </div>
                </div>
                <div id="hand_container_{PLAYER_ID}" class="hand_container">
                    <div style="background-color:rgba({R}, {G}, {B}, .2);">
                        <p>{HAND}</p>
                        <div id="hand_{PLAYER_ID}" class="hand"></div>
                    </div>
                </div>
                <div id="progress_{PLAYER_ID}" class="progress">
                    <div id="forecast_container_{PLAYER_ID}" class="forecast_container">
                        <p id="forecast_text_{PLAYER_ID}"{OPT_FORECAST_CLASS}>{FORECAST_PILE}</p>
                        <div id="forecast_{PLAYER_ID}" class="forecast"></div>
                    </div>
                    <div id="score_container_{PLAYER_ID}" class="score_container">
                        <p id="score_text_{PLAYER_ID}"{OPT_SCORE_CLASS}>{SCORE_PILE}</p>
                        <div id="score_{PLAYER_ID}" class="score"></div>
                    </div>
                    <div id="reference_card_{PLAYER_ID}" class="reference_card S"></div>
                    <div id="achievement_container_{PLAYER_ID}" class="achievement_container">
                        <p>{ACHIEVEMENTS}</p>
                        <div id="achievements_{PLAYER_ID}" class="achievements"></div>
                    </div>
                </div>
            </div>
        <!-- END player -->
    </div>

    <div id="decks_and_available_achievements">
        <div id="decks_and_title">
            <p class="text_center" id="decks_title"><!-- BEGIN decks -->{DECKS}<!-- END decks --></p>
            <div id="decks">
                <div id="decks_1">
                    <div id="deck_set_1_1" class="deck_set">
                        <!-- BEGIN decks_group_1_1 -->
                            <div id="deck_pile_{TYPE}_{AGE}" class="deck_pile"><div class="deck_count" id="deck_count_{TYPE}_{AGE}"></div><div class="deck" id="deck_{TYPE}_{AGE}"></div></div>
                        <!-- END decks_group_1_1 -->
                    </div>
                    <div id="deck_set_2_1" class="deck_set">
                        <!-- BEGIN decks_group_2_1 -->
                            <div id="deck_pile_{TYPE}_{AGE}" class="deck_pile"><div class="deck_count" id="deck_count_{TYPE}_{AGE}"></div><div class="deck" id="deck_{TYPE}_{AGE}"></div></div>
                        <!-- END decks_group_2_1 -->
                    </div>
                    <div id="deck_set_3_1" class="deck_set">
                        <!-- BEGIN decks_group_3_1 -->
                            <div id="deck_pile_{TYPE}_{AGE}" class="deck_pile"><div class="deck_count" id="deck_count_{TYPE}_{AGE}"></div><div class="deck" id="deck_{TYPE}_{AGE}"></div></div>
                        <!-- END decks_group_3_1 -->
                    </div>
                    <div id="deck_set_4_1" class="deck_set">
                        <!-- BEGIN decks_group_4_1 -->
                            <div id="deck_pile_{TYPE}_{AGE}" class="deck_pile"><div class="deck_count" id="deck_count_{TYPE}_{AGE}"></div><div class="deck" id="deck_{TYPE}_{AGE}"></div></div>
                        <!-- END decks_group_4_1 -->
                    </div>
                    <div id="deck_set_5_1" class="deck_set">
                        <!-- BEGIN decks_group_5_1 -->
                            <div id="deck_pile_{TYPE}_{AGE}" class="deck_pile"><div class="deck_count" id="deck_count_{TYPE}_{AGE}"></div><div class="deck" id="deck_{TYPE}_{AGE}"></div></div>
                        <!-- END decks_group_5_1 -->
                    </div>
                </div>
                <div id="decks_2">
                    <div id="deck_set_1_2" class="deck_set">
                        <!-- BEGIN decks_group_1_2 -->
                            <div id="deck_pile_{TYPE}_{AGE}" class="deck_pile"><div class="deck_count" id="deck_count_{TYPE}_{AGE}"></div><div class="deck" id="deck_{TYPE}_{AGE}"></div></div>
                        <!-- END decks_group_1_2 -->
                    </div>
                    <div id="deck_set_2_2" class="deck_set">
                        <!-- BEGIN decks_group_2_2 -->
                            <div id="deck_pile_{TYPE}_{AGE}" class="deck_pile"><div class="deck_count" id="deck_count_{TYPE}_{AGE}"></div><div class="deck" id="deck_{TYPE}_{AGE}"></div></div>
                        <!-- END decks_group_2_2 -->
                    </div>
                    <div id="deck_set_3_2" class="deck_set">
                        <!-- BEGIN decks_group_3_2 -->
                            <div id="deck_pile_{TYPE}_{AGE}" class="deck_pile"><div class="deck_count" id="deck_count_{TYPE}_{AGE}"></div><div class="deck" id="deck_{TYPE}_{AGE}"></div></div>
                        <!-- END decks_group_3_2 -->
                    </div>
                    <div id="deck_set_4_2" class="deck_set">
                        <!-- BEGIN decks_group_4_2 -->
                            <div id="deck_pile_{TYPE}_{AGE}" class="deck_pile"><div class="deck_count" id="deck_count_{TYPE}_{AGE}"></div><div class="deck" id="deck_{TYPE}_{AGE}"></div></div>
                        <!-- END decks_group_4_2 -->
                    </div>
                    <div id="deck_set_5_2" class="deck_set">
                        <!-- BEGIN decks_group_5_2 -->
                            <div id="deck_pile_{TYPE}_{AGE}" class="deck_pile"><div class="deck_count" id="deck_count_{TYPE}_{AGE}"></div><div class="deck" id="deck_{TYPE}_{AGE}"></div></div>
                        <!-- END decks_group_5_2 -->
                    </div>
                </div>
            </div>
        </div>
        <div id="available_relics_and_achievements_container">
            <div id="available_relics_container">
                <p class="text_center"><!-- BEGIN available_relics -->{AVAILABLE_RELICS}<!-- END available_relics --></p>
                <div id="relics"></div>
            </div>
            <div id="available_achievements_container">
                <p class="text_center"><!-- BEGIN available_achievements -->{AVAILABLE_ACHIEVEMENTS}<!-- END available_achievements --></p>
                <div id="achievements"></div>
            </div>
            <div id="available_special_achievements_container">
                <p class="text_center"><!-- BEGIN special_achievements -->{SPECIAL_ACHIEVEMENTS}<!-- END special_achievements --></p>
                <div id="special_achievements"></div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">

// Javascript HTML templates
var jstpl_player_panel = '\
<div class="player_info">\
    <div class="simple_stats">\
        <span class="score_count" id="score_count_container_${player_id}">\
            <span id="score_count_${player_id}"></span>\
            <span class="square basic icon_score"></span>\
        </span>\
        <span class="hand_count" id="hand_count_container_${player_id}">\
            <span id="hand_count_${player_id}"></span>\
            <span class="square basic icon_hand"></span>\
        </span>\
        <span class="max_age_on_board" id="max_age_on_board_container_${player_id}">\
            <span id="max_age_on_board_${player_id}"></span>\
            <span class="square basic icon_age_indicator"></span>\
        </span>\
        <span class="forecast_count" id="forecast_count_container_${player_id}">\
            <span id="forecast_count_${player_id}"></span>\
            <span><i class="fa fa-lg fa-eye"></i></span>\
        </span>\
    </div>\
    <table class="ressource_table" id="ressources_${player_id}">\
        <tr id="symbols_${player_id}"></tr>\
        <tr id="ressource_counts_${player_id}"></tr>\
    </table>\
</div>';

var jstpl_ressource_icon = '<td><div id="ressource_icon_${player_id}_${icon}" class="ressource with_border ressource_${icon} square P icon_${icon}"></div></td>';

var jstpl_ressource_count = '<td><div id="ressource_count_${player_id}_${icon}" class="ressource with_border ressource_${icon}"></div></td>';


</script>

{OVERALL_GAME_FOOTER}
