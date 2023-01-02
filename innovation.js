/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * Innovation implementation : © Jean Portemer <jportemer@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * innovation.js
 *
 * Innovation user interface script
 * 
 * In this file, you are describing the logic of your user interface, in Javascript language.
 *
 */

define([
    "dojo","dojo/_base/declare",
    "ebg/core/gamegui",
    "ebg/counter",
    "ebg/zone"
],
function (dojo, declare) {
    return declare("bgagame.innovation", ebg.core.gamegui, {
        constructor: function(){
            console.log('innovation constructor');
              
            // Global variables of your user interface
            this.zone = {};
            this.counter = {};
            
            this.card_dimensions = { // Dimensions in the CSS + 2
                "S recto" : {"width" : 33, "height" : 47},
                "S card" : {"width" : 47, "height" : 33},
                "M card" : {"width" : 182, "height" : 126},
                "L recto" : {"width" : 316, "height" : 456},
                "L card" : {"width" : 456, "height" : 316},
            };
            
            this.large_screen_width_limit = 1621;
            this.main_area_width_limit = 1151;
            
            this.my_hand_padding = 5; // Must be consistent to what is declared in CSS
            
            this.overlap_for_unsplayed = 3;
            this.overlap_for_splay = {
                "M card" : {"compact": 3, "expanded": 58}
            };
            
            this.HTML_class = {};

            this.HTML_class.my_hand = "M card";
            this.HTML_class.opponent_hand = "S recto";
            this.HTML_class.display = "M card";
            this.HTML_class.deck = "S recto";
            this.HTML_class.board = "M card";
            this.HTML_class.forecast = "S recto";
            this.HTML_class.my_forecast_verso = "M card";
            this.HTML_class.score = "S recto";
            this.HTML_class.my_score_verso = "M card";
            this.HTML_class.revealed = "M card";
            this.HTML_class.relics = "S recto"
            this.HTML_class.achievements = "S recto"
            this.HTML_class.special_achievements = "S card"
            
            this.num_cards_in_row = {};
            
            this.num_cards_in_row.my_hand = null; // Will be computed dynamically
            this.num_cards_in_row.opponent_hand = null;
            this.num_cards_in_row.display = 1;
            this.num_cards_in_row.deck = 15;
            this.num_cards_in_row.forecast = null;
            this.num_cards_in_row.my_forecast_verso = null;
            this.num_cards_in_row.score = null;
            this.num_cards_in_row.my_score_verso = null;
            // For board, this.num_cards_in_row is not defined because it's managed by the splay system: the width is defined dynamically
            this.num_cards_in_row.revealed = 1;
            this.num_cards_in_row.achievements = null;
            // For special achievements, this.num_cards_in_row is not defined because it has a custom pattern
            
            this.delta = {};
            
            this.delta.my_hand = {"x": 189, "y": 133}; // +7
            this.delta.opponent_hand = {"x": 35, "y": 49}; // + 2
            this.delta.display = {"x": 189, "y": 133}; // +7
            this.delta.deck = {"x": 3, "y": 3}; // overlap
            this.delta.forecast = {"x": 35, "y": 49}; // + 2
            this.delta.my_forecast_verso = {"x": 189, "y": 133}; // +7
            this.delta.score = {"x": 35, "y": 49}; // + 2
            this.delta.my_score_verso = {"x": 189, "y": 133}; // +7
            // For board, this.delta is not defined because it's managed by the splay system: the width is defined dynamically
            this.delta.revealed = {"x": 189, "y": 133}; // +7;
            this.delta.achievements = {"x": 35, "y": 49}; // + 2
            // For special achievements, this.delta is not defined because it has a custom pattern
            
            this.incremental_id = 0;
            
            this.selected_card = null;
            
            this.display_mode = null;
            this.view_full = null;

            // Counters used to track progress of the Monument special achievement
            this.number_of_tucked_cards = 0;
            this.number_of_scored_cards = 0;

            this.arrows_for_expanded_mode = "&gt;&gt; &lt;&lt;"; // >> <<
            this.arrows_for_compact_mode = "&lt;&lt; &gt;&gt;"; // << >>
            this.number_of_splayed_piles = null;
            
            this.players = null;
            
            this.saved_HTML_cards = {};
            
            this.initializing = null;
            
            // Special flags used for Publication
            this.publication_permuted_zone = null;
            this.publication_permutations_done = null;
            this.publication_original_items = null;
            
            // Special flag used when a selection has to be made within a stack
            this.color_pile = null;
            
            // Special flags to indicate that multiple colors must be chosen
            this.choose_two_colors = null;
            this.choose_three_colors = null;
            this.first_chosen_color = null;
            this.second_chosen_color = null;

            // Special flag used by Mona Lisa
            this.choose_integer = null;
            
            // System to remember what node where last offed and what was their handlers to restore if needed
            this.deactivated_cards = null;
            this.deactivated_cards_mid_dogma = null;
            this.erased_pagemaintitle_text = null;
        },
        
        //****** CODE FOR DEBUG MODE
        debug_draw: function() {
            var debug_card_list = document.getElementById("debug_card_list");
            this.ajaxcall("/innovation/innovation/debug_draw.html",
                {
                    lock: true,
                    card_id: debug_card_list.value
                },
                this, function(result){}, function(is_error){},
            );
        },
        debug_meld: function() {
            var debug_card_list = document.getElementById("debug_card_list");
            this.ajaxcall("/innovation/innovation/debug_meld.html",
                {
                    lock: true,
                    card_id: debug_card_list.value
                },
                this, function(result){}, function(is_error){},
            );
        },
        debug_tuck: function() {
            var debug_card_list = document.getElementById("debug_card_list");
            this.ajaxcall("/innovation/innovation/debug_tuck.html",
                {
                    lock: true,
                    card_id: debug_card_list.selectedIndex
                },
                this, function(result){}, function(is_error){},
            );
        },
        debug_score: function () {
            var debug_card_list = document.getElementById("debug_card_list");
            this.ajaxcall("/innovation/innovation/debug_score.html",
                {
                    lock: true,
                    card_id: debug_card_list.value
                },
                this, function (result) { }, function (is_error) {}
            );
        },  
        debug_achieve: function () {
            var debug_card_list = document.getElementById("debug_card_list");
            this.ajaxcall("/innovation/innovation/debug_achieve.html",
                {
                    lock: true,
                    card_id: debug_card_list.value
                },
                this, function (result) { }, function (is_error) {}
            );      
        },
        debug_return: function () {
            var debug_card_list = document.getElementById("debug_card_list");
            this.ajaxcall("/innovation/innovation/debug_return.html",
                {
                    lock: true,
                    card_id: debug_card_list.value
                },
                this, function (result) { }, function (is_error) {}
            );      
        },
        debug_topdeck: function () {
            var debug_card_list = document.getElementById("debug_card_list");
            this.ajaxcall("/innovation/innovation/debug_topdeck.html",
                {
                    lock: true,
                    card_id: debug_card_list.value
                },
                this, function (result) { }, function (is_error) {}
            );      
        },
        debug_dig: function () {
            var debug_card_list = document.getElementById("debug_card_list");
            this.ajaxcall("/innovation/innovation/debug_dig.html",
                {
                    lock: true,
                    card_id: debug_card_list.value
                },
                this, function (result) { }, function (is_error) {}
            );      
        },
        debug_foreshadow: function () {
            var debug_card_list = document.getElementById("debug_card_list");
            this.ajaxcall("/innovation/innovation/debug_foreshadow.html",
                {
                    lock: true,
                    card_id: debug_card_list.value
                },
                this, function (result) { }, function (is_error) {}
            );      
        },
        debug_unsplay: function() {
            var debug_color_list = document.getElementById("debug_color_list");
            this.ajaxcall("/innovation/innovation/debug_splay.html",
                {
                    lock: true,
                    color: debug_color_list.value,
                    direction: 0
                },
                this, function (result) { }, function (is_error) {}
            );
        },
        debug_splay_left: function() {
            var debug_color_list = document.getElementById("debug_color_list");
            this.ajaxcall("/innovation/innovation/debug_splay.html",
                {
                    lock: true,
                    color: debug_color_list.value,
                    direction: 1
                },
                this, function (result) { }, function (is_error) {}
            );
        },
        debug_splay_right: function() {
            var debug_color_list = document.getElementById("debug_color_list");
            this.ajaxcall("/innovation/innovation/debug_splay.html",
                {
                    lock: true,
                    color: debug_color_list.value,
                    direction: 2
                },
                this, function (result) { }, function (is_error) {}
            );
        },
        debug_splay_up: function() {
            var debug_color_list = document.getElementById("debug_color_list");
            this.ajaxcall("/innovation/innovation/debug_splay.html",
                {
                    lock: true,
                    color: debug_color_list.value,
                    direction: 3
                },
                this, function (result) { }, function (is_error) {}
            );
        },
        //******
                
        /*
            setup:
            
            This method must set up the game user interface according to current game situation specified
            in parameters.
            
            The method is called each time the game interface is displayed to a player, ie:
            _ when the game starts
            _ when a player refreshes the game page (F5)
            
            "gamedatas" argument contains all datas retrieved by your "getAllDatas" PHP method.
        */
        setup: function (gamedatas) {
            dojo.destroy('debug_output');
            
            //****** CODE FOR DEBUG MODE
            if (!this.isSpectator && gamedatas.debug_mode == 1) {
                var main_area = $('main_area');

                // Prepend UI elements for debug area
                main_area.innerHTML =
                    "</br><select id='debug_color_list'></select>"
                    + "<button id='debug_unsplay' class='action-button debug_button bgabutton bgabutton_red'>UNSPLAY</button>"
                    + "<button id='debug_splay_left' class='action-button debug_button bgabutton bgabutton_red'>SPLAY LEFT</button>"
                    + "<button id='debug_splay_right' class='action-button debug_button bgabutton bgabutton_red'>SPLAY RIGHT</button>"
                    + "<button id='debug_splay_up' class='action-button debug_button bgabutton bgabutton_red'>SPLAY UP</button>"
                    + main_area.innerHTML;
                if (gamedatas.echoes_expansion_enabled) {
                    main_area.innerHTML = "<button id='debug_foreshadow' class='action-button debug_button bgabutton bgabutton_red'>FORESHADOW</button>" + main_area.innerHTML;
                }
                if (gamedatas.artifacts_expansion_enabled) {
                    main_area.innerHTML = "<button id='debug_dig' class='action-button debug_button bgabutton bgabutton_red'>DIG</button>" + main_area.innerHTML;
                }
                main_area.innerHTML =
                    "<select id='debug_card_list'></select>"
                    + "<button id='debug_draw' class='action-button debug_button bgabutton bgabutton_red'>DRAW</button>"
                    + "<button id='debug_meld' class='action-button debug_button bgabutton bgabutton_red'>MELD</button>"
                    + "<button id='debug_tuck' class='action-button debug_button bgabutton bgabutton_red'>TUCK</button>"
                    + "<button id='debug_score' class='action-button debug_button bgabutton bgabutton_red'>SCORE</button>"
                    + "<button id='debug_achieve' class='action-button debug_button bgabutton bgabutton_red'>ACHIEVE</button>"
                    + "<button id='debug_return' class='action-button debug_button bgabutton bgabutton_red'>RETURN</button>"
                    + "<button id='debug_topdeck' class='action-button debug_button bgabutton bgabutton_red'>TOPDECK</button>"
                    + main_area.innerHTML;

                // Populate dropdown lists
                for (var i = 0; i < Object.keys(gamedatas.cards).length; i++) {
                    var key = Object.keys(gamedatas.cards)[i];
                    var card = gamedatas.cards[key];
                    $('debug_card_list').innerHTML += `<option value='${card.id}'> ${card.id} - ${card.name} (Age ${card.age})</option>`;
                }
                $('debug_color_list').innerHTML += `<option value='0'>Blue</option>`;
                $('debug_color_list').innerHTML += `<option value='1'>Red</option>`;
                $('debug_color_list').innerHTML += `<option value='2'>Green</option>`;
                $('debug_color_list').innerHTML += `<option value='3'>Yellow</option>`;
                $('debug_color_list').innerHTML += `<option value='4'>Purple</option>`;

                // Trigger events when buttons are clicked
                dojo.connect($('debug_draw'), 'onclick', this, 'debug_draw');
                dojo.connect($('debug_meld'), 'onclick', this, 'debug_meld');
                dojo.connect($('debug_tuck'), 'onclick', this, 'debug_tuck');
                dojo.connect($('debug_score'), 'onclick', this, 'debug_score');
                dojo.connect($('debug_achieve'), 'onclick', this, 'debug_achieve');
                dojo.connect($('debug_return'), 'onclick', this, 'debug_return');
                dojo.connect($('debug_topdeck'), 'onclick', this, 'debug_topdeck');
                if (gamedatas.artifacts_expansion_enabled) {
                    dojo.connect($('debug_dig'), 'onclick', this, 'debug_dig');
                }
                if (gamedatas.echoes_expansion_enabled) {
                    dojo.connect($('debug_foreshadow'), 'onclick', this, 'debug_foreshadow');
                }
                dojo.connect($('debug_unsplay'), 'onclick', this, 'debug_unsplay');
                dojo.connect($('debug_splay_left'), 'onclick', this, 'debug_splay_left');
                dojo.connect($('debug_splay_right'), 'onclick', this, 'debug_splay_right');
                dojo.connect($('debug_splay_up'), 'onclick', this, 'debug_splay_up');
            }
            //******
        
            this.my_score_verso_window = new dijit.Dialog({ 'title': _("Cards in your score pile (opponents cannot see this)") });
            this.my_forecast_verso_window = new dijit.Dialog({ 'title': _("Cards in your forecast (opponents cannot see this)") });
            this.text_for_expanded_mode = _("Show compact");
            this.text_for_compact_mode = _("Show expanded");
            this.text_for_view_normal = _("Look at all cards in piles");
            this.text_for_view_full = _("Resume normal view");
            
            // GENERAL INFO
            this.cards = gamedatas.cards;
            this.players = gamedatas.players; 
            this.number_of_achievements_needed_to_win = gamedatas.number_of_achievements_needed_to_win;
            
            var any_player_id = Object.keys(this.players)[0]; // This is used to get a referenced on any player (who that player is does not matter) for global design purposes
                                                              // This is especially important because targetting this.player_id won't work in spectator mode
            
            // MAIN AREA
            var window_width = dojo.window.getBox().w;
            
            // Positioning and sizing the main area, decks and achievements
            var main_area_width;
            var large_screen = window_width >= this.large_screen_width_limit;
            if (large_screen) {
                main_area_width = this.main_area_width_limit + (window_width - this.large_screen_width_limit);
            }
            else {
                main_area_width = dojo.position('left-side').w;
            }
            dojo.style('main_area', 'width', main_area_width + 'px');
            
            if (!large_screen) {
                dojo.style('decks', 'display', 'inline-block');
                dojo.style('available_relics_container', 'display', 'inline-block');
                dojo.style('available_achievements_container', 'display', 'inline-block');
                dojo.style('available_special_achievements_container', 'display', 'inline-block');
            }
            
            // Defining the number of cards hand zone can host
            // 10 = 2 * padding
            this.num_cards_in_row.my_hand = parseInt((dojo.contentBox('hand_container_' + any_player_id).w + this.delta.my_hand.x - this.card_dimensions['M card'].width - 10) / (this.delta.my_hand.x));
            this.num_cards_in_row.opponent_hand = parseInt((dojo.contentBox('hand_container_' + any_player_id).w + this.delta.opponent_hand.x - this.card_dimensions['S card'].width - 10) / (this.delta.opponent_hand.x));
            
            // Add achievements to win to achievement container and determine max cards per row for the achievement container
            for(var player_id in this.players) {
                dojo.addClass('achievement_container_' + player_id, 'to_win_' + this.number_of_achievements_needed_to_win)
            }

            var achievement_container_width = dojo.position('achievement_container_' + any_player_id).w
            var reference_card_width = dojo.position('reference_card_' + any_player_id).w;
            var progress_width = dojo.position('progress_' + any_player_id).w;
            var score_width = progress_width - achievement_container_width - 10 - reference_card_width;
           
            this.num_cards_in_row.achievements = 4;
            if (achievement_container_width >= 140 && achievement_container_width <= 175 ) {
                this.num_cards_in_row.achievements = 5;
            } else if (achievement_container_width > 175) {
                this.num_cards_in_row.achievements = this.number_of_achievements_needed_to_win;
            }
            
            this.num_cards_in_row.score = parseInt(score_width / (this.delta.score.x));
            this.num_cards_in_row.forecast =  this.num_cards_in_row.score;

            // Split the space between the score and the forecast
            if (gamedatas.echoes_expansion_enabled) {
                this.num_cards_in_row.forecast = Math.floor(this.num_cards_in_row.forecast / 2);
                this.num_cards_in_row.score = Math.floor(this.num_cards_in_row.score / 2);
            }

            // Defining the number of cards the window for forecast verso can host
            // Viewport size defined as minimum between the width of a hand container and the width needed to host 5 cards.
            this.num_cards_in_row.my_forecast_verso = parseInt((dojo.contentBox('hand_container_' + any_player_id).w + this.delta.my_forecast_verso.x - this.card_dimensions['M card'].width) / (this.delta.my_forecast_verso.x));
            if (this.num_cards_in_row.my_forecast_verso > 5) {
                this.num_cards_in_row.my_forecast_verso = 5;
            }
            
            // Defining the number of cards the window for score verso can host
            // Viewport size defined as minimum between the width of a hand container and the width needed to host 5 cards.
            this.num_cards_in_row.my_score_verso = parseInt((dojo.contentBox('hand_container_' + any_player_id).w + this.delta.my_score_verso.x - this.card_dimensions['M card'].width) / (this.delta.my_score_verso.x));
            if (this.num_cards_in_row.my_score_verso > 5) {
                this.num_cards_in_row.my_score_verso = 5;
            }
            
            // PLAYER PANELS
            for(var player_id in this.players) {
                dojo.place(this.format_block('jstpl_player_panel', {'player_id':player_id}), $('player_board_' + player_id));
                for (var icon=1; icon<=6; icon++) {
                    var infos = {'player_id':player_id, 'icon': icon};
                    dojo.place(this.format_block('jstpl_ressource_icon', infos), $('symbols_' + player_id));
                    dojo.place(this.format_block('jstpl_ressource_count', infos), $('ressource_counts_' + player_id));
                }
            }
            
            this.addCustomTooltipToClass("score_count", _("Score"), "");
            this.addCustomTooltipToClass("hand_count", _("Number of cards in hand"), "");
            this.addCustomTooltipToClass("max_age_on_board", _("Max age on board top cards"), "");
            
            for (var icon=1; icon<=6; icon++) {
                this.addCustomTooltipToClass("ressource_" + icon, _("Number of visible ${icons} on the board").replace('${icons}', this.square('P', 'icon', icon, 'in_tooltip')), "");
            }
            
            // Counters for score
            this.counter.score = {};
            for(var player_id in this.players) {
                this.counter.score[player_id] = new ebg.counter();
                this.counter.score[player_id].create($("score_count_" + player_id));
                this.counter.score[player_id].setValue(gamedatas.score[player_id]);
            }
            
            // Counters for max age on board
            this.counter.max_age_on_board = {};
            for(var player_id in this.players) {
                this.counter.max_age_on_board[player_id] = new ebg.counter();
                this.counter.max_age_on_board[player_id].create($("max_age_on_board_" + player_id));
                this.counter.max_age_on_board[player_id].setValue(gamedatas.max_age_on_board[player_id]);
            }
            
            // Counters for ressources
            this.counter.ressource_count = {};
            for (var player_id in this.players) {
                this.counter.ressource_count[player_id] = {};
                for (var icon = 1; icon <= 6; icon++) {
                    this.counter.ressource_count[player_id][icon] = new ebg.counter();
                    this.counter.ressource_count[player_id][icon].create($("ressource_count_" + player_id + "_" + icon));
                    this.counter.ressource_count[player_id][icon].setValue(gamedatas.ressource_counts[player_id][icon]);
                }
            }
            if (gamedatas.artifact_on_display_icons != null && gamedatas.artifact_on_display_icons.resource_icon != null) {
                this.updateResourcesForArtifactOnDisplay(
                    gamedatas.active_player,
                    gamedatas.artifact_on_display_icons.resource_icon,
                    gamedatas.artifact_on_display_icons.resource_count_delta);
            }
            
            // Action indicator
            for(var player_id in this.players) {
                dojo.place("<div id='action_indicator_" + player_id + "' class='action_indicator'></div>", $('ressources_' + player_id), 'after');
            }
            if (gamedatas.active_player !== null) {
                this.givePlayerActionCard(gamedatas.active_player, gamedatas.action_number);
            }

            this.artifacts_expansion_enabled = gamedatas.artifacts_expansion_enabled;
            this.relics_enabled = gamedatas.relics_enabled;
            this.cities_expansion_enabled = gamedatas.cities_expansion_enabled;
            this.echoes_expansion_enabled = gamedatas.echoes_expansion_enabled;
            this.figures_expansion_enabled = gamedatas.figures_expansion_enabled;

            var num_sets = 1 + this.artifacts_expansion_enabled + this.cities_expansion_enabled + this.echoes_expansion_enabled + this.figures_expansion_enabled;
            if (num_sets > 2) {
                this.delta.deck = {"x": 0.25, "y": 0.25}; // overlap
            }
            
            // DECKS
            this.zone.deck = {};
            for (var type = 0; type <= 4; type++) {
                this.zone.deck[type] = {};
                for (var age = 1; age <= 10; age++) {
                    // Creation of the zone
                    this.zone.deck[type][age] = this.createZone('deck', 0, type, age, null, grouped_by_age_type_and_is_relic=false, counter_method="COUNT", counter_display_zero=false)
                    this.setPlacementRules(this.zone.deck[type][age], left_to_right=true)
                    
                    // Add cards to zone according to the current situation
                    var num_cards = gamedatas.deck_counts[type][age];
                    for (var i=0; i<num_cards; i++) {
                        this.createAndAddToZone(this.zone.deck[type][age], i, age, type, /*is_relic=*/ 0, null, dojo.body(), null);
                    }
                    
                    // Current number of cards in the deck
                    $(`deck_count_${type}_${age}`).innerHTML = num_cards;
                    if (num_sets > 2) {
                        dojo.addClass(`deck_count_${type}_${age}`, 'condensed');
                        dojo.addClass(`deck_pile_${type}_${age}`, 'condensed');
                    }
                }
            }
            if (!gamedatas.artifacts_expansion_enabled) {
                dojo.byId('deck_set_2').style.display = 'none';
            }
            if (!gamedatas.cities_expansion_enabled) {
                dojo.byId('deck_set_3').style.display = 'none';
            }
            if (!gamedatas.echoes_expansion_enabled) {
                dojo.byId('deck_set_4').style.display = 'none';
            }
            if (!gamedatas.figures_expansion_enabled) {
                dojo.byId('deck_set_5').style.display = 'none';
            }

            // AVAILABLE RELICS
            // Creation of the zone
            this.zone.relics = {};
            this.zone.relics["0"] = this.createZone('relics', 0, null, null, null, grouped_by_age_type_and_is_relic = true);
            this.setPlacementRulesForRelics();
            if (gamedatas.relics_enabled) {
                for (var i = 0; i < gamedatas.unclaimed_relics.length; i++) {
                    var relic = gamedatas.unclaimed_relics[i];
                    this.createAndAddToZone(this.zone.relics["0"], i, relic.age, relic.type, relic.is_relic, null, dojo.body(), null);
                    if (this.canShowCardTooltip(relic['id'])) {
                        this.addTooltipForCard(relic);
                    }
                }
            } else {
                dojo.byId('available_relics_container').style.display = 'none';
            }
            
            // AVAILABLE ACHIEVEMENTS
            // Creation of the zone
            this.zone.achievements = {};
            
            // Add cards to zone according to the current situation
            if (gamedatas.unclaimed_standard_achievement_counts !== null) {
                this.zone.achievements["0"] = this.createZone('achievements', 0, null, null, null, grouped_by_age_type_and_is_relic = true);
                this.setPlacementRulesForAchievements();
                for (var type = 0; type <= 4; type++) {
                    for (var is_relic = 0; is_relic <= 1; is_relic++) {
                        for (var age = 1; age <= 10; age++) {
                            var num_cards = gamedatas.unclaimed_standard_achievement_counts[type][is_relic][age];
                            for (var i = 0; i < num_cards; i++) {
                                this.createAndAddToZone(this.zone.achievements["0"], i, age, type, is_relic, null, dojo.body(), null);
                                if (!this.isSpectator) {
                                    // Construct card object so that we can add a tooltip to the achievement
                                    // TODO(LATER): Simplify addTooltipForStandardAchievement once the other callsite is removed.
                                    var achievement = {'location': 'achievements', 'owner': 0, 'type': type, 'age': age, 'is_relic': is_relic};
                                    this.addTooltipForStandardAchievement(achievement);
                                }
                            }
                        }
                    }
                }
            } else {
                // TODO(LATER): Remove this once it is safe to do so.
                this.zone.achievements["0"] = this.createZone('achievements', 0);
                this.setPlacementRulesForAchievements();
                for(var i=0; i<gamedatas.unclaimed_achievements.length; i++) {
                    var achievement = gamedatas.unclaimed_achievements[i];
                    if (achievement.age === null) {
                        continue;
                    }
                    this.createAndAddToZone(this.zone.achievements["0"], i, achievement.age, achievement.type, achievement.is_relic, null, dojo.body(), null);
                    if (!this.isSpectator) {
                        this.addTooltipForStandardAchievement(achievement);
                    }
                }
            }
            
            // AVAILABLE SPECIAL ACHIEVEMENTS
            // Creation of the zone
            this.zone.special_achievements = {};
            this.zone.special_achievements["0"] = this.createZone('special_achievements', 0);
            this.setPlacementRulesForSpecialAchievements();
            
            // Add cards to zone according to the current situation
            for (var i=0; i<gamedatas.unclaimed_achievements.length; i++) {
                var achievement = gamedatas.unclaimed_achievements[i];
                if (achievement.age !== null) {
                    continue;
                }
                this.createAndAddToZone(this.zone.special_achievements["0"], i, null, achievement.type, achievement.is_relic, achievement.id, dojo.body(), null);
                this.addTooltipForCard(achievement);
            }

            // Add another button here to open up the special achievements popup
            var button = this.format_string_recursive("<i id='browse_special_achievements_button' class='bgabutton bgabutton_gray'>${button_text}</i>", {'button_text': _("Browse"), 'i18n': ['button_text']});
            dojo.place(button, 'special_achievements', 'after');
            this.on(dojo.query('#browse_special_achievements_button'), 'onclick', 'click_open_special_achievement_browsing_window');
            
            // PLAYERS' HANDS
            this.zone.hand = {};
            for (var player_id in this.players) {
                // Creation of the zone
                var zone = this.createZone('hand', player_id, null, null, null, grouped_by_age_type_and_is_relic=true, counter_method="COUNT", counter_display_zero=true);
                this.zone.hand[player_id] = zone;
                this.setPlacementRules(zone, left_to_right=true);
                           
                // Add cards to zone according to the current situation
                if (player_id == this.player_id) {
                    for (var i=0; i<gamedatas.my_hand.length; i++) {
                        var card = gamedatas.my_hand[i];
                        this.createAndAddToZone(zone, card.position, card.age, card.type, card.is_relic, card.id, dojo.body(), card);
                        if (gamedatas.turn0 && card.selected == 1) {
                            this.selected_card = card;
                        }
                        // Add tooltip
                        this.addTooltipForCard(card);
                    }
                } else {
                    for (var type = 0; type <= 4; type++) {
                        for (var is_relic = 0; is_relic <= 1; is_relic++) {
                            for (var age = 1; age <= 10; age++) {
                                var num_cards = gamedatas.hand_counts[player_id][type][is_relic][age];
                                for (var i = 0; i < num_cards; i++) {
                                    this.createAndAddToZone(zone, i, age, type, is_relic, null, dojo.body(), null);
                                    if (is_relic) {
                                        // Construct card object so that we can add a tooltip to the relic
                                        var relic = {'location': 'hand', 'owner': player_id, 'type': type, 'age': age, 'is_relic': is_relic, 'id': 212 + age};
                                        if (this.canShowCardTooltip(relic['id'])) {
                                            this.addTooltipForCard(relic);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }

            // PLAYERS' ARTIFACTS ON DISPLAY
            this.zone.display = {};
            for (var player_id in this.players) {
                if (!gamedatas.artifacts_expansion_enabled) {
                    dojo.byId('display_container_' + player_id).style.display = 'none';
                    continue;
                }

                // Creation of the zone
                var zone = this.createZone('display', player_id, null, null, null);
                this.zone.display[player_id] = zone;
                this.setPlacementRules(zone, left_to_right=true);
                        
                // Add card to zone if it exists
                var card = gamedatas.artifacts_on_display[player_id];
                if (card != null) {
                    this.createAndAddToZone(zone, card.position, card.age, card.type, card.is_relic, card.id, dojo.body(), card);
                    this.addTooltipForCard(card);
                }
            }

            // PLAYERS' FORECAST
            this.zone.forecast = {};
            for (var player_id in this.players) {
                // Creation of the zone
                this.zone.forecast[player_id] = this.createZone('forecast', player_id, null, null, null, grouped_by_age_type_and_is_relic=true);
                this.setPlacementRules(this.zone.forecast[player_id], left_to_right=true);
                    
                // Add cards to zone according to the current situation
                for (var type = 0; type <= 4; type++) {
                    for (var is_relic = 0; is_relic <= 1; is_relic++) {
                        var forecast_count = gamedatas.forecast_counts[player_id][type][is_relic];
                        for (var age = 1; age <= 10; age++) {
                            var num_cards = forecast_count[age];
                            for(var i = 0; i < num_cards; i++) {
                                this.createAndAddToZone(this.zone.forecast[player_id], i, age, type, is_relic, null, dojo.body(), null);
                            }
                        }
                    }
                }
            }
            if (!this.echoes_expansion_enabled) {
                dojo.byId('forecast_text_' + this.player_id).style.display = 'none';
            }
            
            // PLAYERS' SCORE
            this.zone.score = {};
            for (var player_id in this.players) {
                // Creation of the zone
                this.zone.score[player_id] = this.createZone('score', player_id, null, null, null, grouped_by_age_type_and_is_relic=true);
                this.setPlacementRules(this.zone.score[player_id], left_to_right=false);
                    
                // Add cards to zone according to the current situation
                for (var type = 0; type <= 4; type++) {
                    for (var is_relic = 0; is_relic <= 1; is_relic++) {
                        var score_count = gamedatas.score_counts[player_id][type][is_relic];
                        for (var age = 1; age <= 10; age++) {
                            var num_cards = score_count[age];
                            for(var i = 0; i < num_cards; i++) {
                                this.createAndAddToZone(this.zone.score[player_id], i, age, type, is_relic, null, dojo.body(), null);
                                if (is_relic) {
                                    // Construct card object so that we can add a tooltip to the relic
                                    var relic = {'location': 'hand', 'owner': player_id, 'type': type, 'age': age, 'is_relic': is_relic, 'id': 212 + age};
                                    if (this.canShowCardTooltip(relic['id'])) {
                                        this.addTooltipForCard(relic);
                                    }
                                }
                            }
                        }
                    }
                }
            }

            // My forecast: create an extra zone to show the versos of the cards at will in a windows
            if (!this.isSpectator && this.echoes_expansion_enabled) {
                this.my_forecast_verso_window.attr("content", "<div id='my_forecast_verso'></div><a id='forecast_close_window' class='bgabutton bgabutton_blue'>Close</a>");
                this.zone.my_forecast_verso = this.createZone('my_forecast_verso', this.player_id, null, null, null, grouped_by_age_type_and_is_relic=true);
                this.setPlacementRules(this.zone.my_forecast_verso, left_to_right=true);
                for (var i = 0; i < gamedatas.my_forecast.length; i++) {
                    var card = gamedatas.my_forecast[i];
                    this.createAndAddToZone(this.zone.my_forecast_verso, card.position, card.age, card.type, card.is_relic, card.id, dojo.body(), card);
                    this.addTooltipForCard(card);
                }
                // Provide links to get access to that window and close it
                dojo.connect($('forecast_text_' + this.player_id), 'onclick', this, 'click_display_forecast_window');
                dojo.connect($('forecast_close_window'), 'onclick', this, 'click_close_forecast_window');
            }
            
            // My score: create an extra zone to show the versos of the cards at will in a windows
            if (!this.isSpectator) {
                this.my_score_verso_window.attr("content", "<div id='my_score_verso'></div><a id='score_close_window' class='bgabutton bgabutton_blue'>" + _("Close") + "</a>");
                this.zone.my_score_verso = this.createZone('my_score_verso', this.player_id, null, null, null, grouped_by_age_type_and_is_relic=true);
                this.setPlacementRules(this.zone.my_score_verso, left_to_right=true);
                for (var i = 0; i < gamedatas.my_score.length; i++) {
                    var card = gamedatas.my_score[i];
                    this.createAndAddToZone(this.zone.my_score_verso, card.position, card.age, card.type, card.is_relic, card.id, dojo.body(), card);
                    this.addTooltipForCard(card);
                }
                // Provide links to get access to that window and close it
                dojo.connect($('score_text_' + this.player_id), 'onclick', this, 'click_display_score_window');
                dojo.connect($('score_close_window'), 'onclick', this, 'click_close_score_window');
            }
            
            // PLAYERS' ACHIEVEMENTS
            for (var player_id in this.players) {
                // Creation of the zone
                this.zone.achievements[player_id] = this.createZone('achievements', player_id);
                this.setPlacementRules(this.zone.achievements[player_id], left_to_right=true);
                    
                // Add cards to zone according to the current situation
                var achievements = gamedatas.claimed_achievements[player_id];
                for(var i = 0; i < achievements.length; i++){
                    var achievement = achievements[i];
                    if (achievement.age == null) { // Special achievement
                        this.createAndAddToZone(this.zone.achievements[player_id], i, null, achievement.type, achievement.is_relic, achievement.id, dojo.body(), null);
                        this.addTooltipForCard(achievement);
                    } else {
                        // Normal achievement or relic
                        this.createAndAddToZone(this.zone.achievements[player_id], i, achievement.age, achievement.type, achievement.is_relic, null, dojo.body(), null);
                        if (achievement.is_relic && this.canShowCardTooltip(achievement.id)) {
                            this.addTooltipForCard(achievement);
                        }
                    } 
                }
            }
            
            if (!this.isSpectator) {
                if (this.echoes_expansion_enabled) {
                    dojo.query('#progress_' + this.player_id + ' .forecast_container > p, #progress_' + this.player_id + ' .achievement_container > p').addClass('two_lines');
                    dojo.query('#progress_' + this.player_id + ' .forecast_container > p')[0].innerHTML += '<br /><span class="minor_information">' + _('(view cards)') + '</span>';
                }
                dojo.query('#progress_' + this.player_id + ' .score_container > p, #progress_' + this.player_id + ' .achievement_container > p').addClass('two_lines');
                dojo.query('#progress_' + this.player_id + ' .score_container > p')[0].innerHTML += '<br /><span class="minor_information">' + _('(view cards)') + '</span>';
                dojo.query('#progress_' + this.player_id + ' .achievement_container > p')[0].innerHTML += '<br /><span class="minor_information">' + _('(${n} needed to win)').replace('${n}', gamedatas.number_of_achievements_needed_to_win) + '</span>';
            }
            
            // PLAYER BOARD
            // Display mode
            if (this.isSpectator) { // The wishes for splaying can't be saved if the spectator refreshes
                // We set manually a default value
                // The spectator can later change this using the buttons, the same way the players do
                this.display_mode = true; // Show expanded by default
                this.view_full = false; // Don't show view full by default
            }
            else {
                this.display_mode = gamedatas.display_mode;
                this.view_full = gamedatas.view_full;
            }
            
            // Stacks
            this.zone.board = {};
            this.number_of_splayed_piles = 0;
            for (var player_id in this.players) {
                this.zone.board[player_id] = {};
                var player_board = gamedatas.board[player_id];
                var player_splay_directions = gamedatas.board_splay_directions[player_id];
                var player_splay_directions_in_clear = gamedatas.board_splay_directions_in_clear[player_id];
                
                for(var color = 0; color < 5; color++){
                    var splay_direction = player_splay_directions[color];
                    var splay_direction_in_clear = player_splay_directions_in_clear[color];
                    
                    // Creation of the zone
                    this.zone.board[player_id][color] = this.createZone('board', player_id, null, null, color)
                    this.setSplayMode(this.zone.board[player_id][color], splay_direction)
                    // Splay indicator
                    dojo.addClass('splay_indicator_' + player_id + '_' + color, 'splay_' + splay_direction);
                    if (splay_direction > 0) {
                        this.number_of_splayed_piles++;
                        this.addCustomTooltip('splay_indicator_' + player_id + '_' + color, dojo.string.substitute(_('This stack is splayed ${direction}.'), {'direction': '<b>' + splay_direction_in_clear + '</b>'}), '')
                    }
                    
                    // Add cards to zone according to the current situation
                    var cards_in_pile = player_board[color];
                    for(var i = 0; i < cards_in_pile.length; i++){
                        var card = cards_in_pile[i];
                        this.createAndAddToZone(this.zone.board[player_id][color], card.position, card.age, card.type, card.is_relic, card.id, dojo.body(), card)
                        
                        // Add tooltip
                        this.addTooltipForCard(card);
                    }
                }
            }
            
            // REVEALED ZONE
            this.zone.revealed = {};    
            for (var player_id in this.players) {
                var zone = this.createZone('revealed', player_id, null, null, null);
                this.zone.revealed[player_id] = zone;
                dojo.style(zone.container_div, 'display', 'none');
                this.setPlacementRules(zone, left_to_right=true);
                
                var revealed_cards = gamedatas.revealed[player_id];
                for(var i = 0; i < revealed_cards.length; i++){
                    var card = revealed_cards[i];
                    this.createAndAddToZone(zone, card.position, card.age, card.type, card.is_relic, card.id, dojo.body(), card)
                        
                    // Add tooltip
                    this.addTooltipForCard(card);
                }
            }

            // Button for view full
            this.addButtonForViewFull();

            // Button for display mode
            this.addButtonForSplayMode();
            if (this.number_of_splayed_piles > 0) { // If at least there is one splayed color on any player board
                this.enableButtonForSplayMode();
            }
            // Button for looking at cards (including special achievements)
            this.addButtonForBrowsingCards();
            for (var i = 0; i < gamedatas.unclaimed_achievements.length; i++) {
                var achievement = gamedatas.unclaimed_achievements[i];
                if (achievement.age === null) {
                    dojo.query('#special_achievement_summary_' + achievement.id).addClass('unclaimed');
                }
            }
            this.number_of_tucked_cards = gamedatas.monument_counters.number_of_tucked_cards;
            this.number_of_scored_cards = gamedatas.monument_counters.number_of_tucked_cards;
            this.refreshSpecialAchievementProgression();
            
            // REFERENCE CARD
            this.addTooltipForReferenceCard();
            
            // CURRENT DOGMA CARD EFFECT
            if (gamedatas.JSCardEffectQuery !== null) {
                // Highlight the current effect if visible
                dojo.query(gamedatas.JSCardEffectQuery).addClass('current_effect');
            }

            // Hide player's area if they have been eliminated from the game (e.g. Exxon Valdez)
            for (var player_id in this.players) {
                if (this.players[player_id].player_eliminated == 1) {
                    dojo.byId('player_' + player_id).style.display = 'none';
                }
            }
            
            // Force refresh page on resize if width changes
            /*var window_width = dojo.window.getBox().w;
            window.onresize = function() {
                if (window.RT) {
                    clearTimeout(window.RT);
                }
                window.RT = setTimeout(function() {
                    if(window_width != dojo.window.getBox().w) { // If there is an actual change of the width of the viewport
                        this.location.reload(false);
                    }
                }, 100);
            }*/
            
            // Setup game notifications to handle (see "setupNotifications" method below)
            this.setupNotifications();
            
            this.initializing = true;
            
            console.log("Ending game setup");
        },

        /* [Undocumented] Override BGA framework functions to call onLoadingComplete when loading is done */
        setLoader(value, max) {
            this.inherited(arguments);
            if (!this.isLoadingComplete && value >= 100) {
            this.isLoadingComplete = true;
            this.onLoadingComplete();
            }
        },

        onLoadingComplete() {
            // Add card tooltips to existing game log messages
            for (var i = 0; i < this.cards.length; i++) {
                var card_id = this.cards[i].id;
                // For some reason, after a page refresh, each entry in the game log is located in two diffferent
                // spots on the page, meaning that each span holding a card name no longer has a unique ID (since
                // it appears exactly twice), and BGA's framework to add a tooltip requires that it has a unique ID.
                // The workaround here is to first remove the extra IDs, before trying to add the tooltips.
                dojo.query("#chatbar .card_id_" + card_id).removeAttr('id');
                var elements = dojo.query(".card_id_" + card_id);
                if (elements.length > 0 && this.canShowCardTooltip(card_id)) {
                    this.addCustomTooltipToClass("card_id_" + card_id, this.getTooltipForCard(card_id), "");
                }
            }
        },
        
        ///////////////////////////////////////////////////
        //// Simple handler management system
        // this.on replace dojo.connect
        // this.off enables to disconnect the handler of one particular event on the object attached with this.on
        // this.restart enables to reconnect the last handler of one particular event
        
        on: function (filter, event, method) {
            var self = this;
            filter.forEach(
                function(node, index, arr) {
                    if (node.last_handler === undefined) {
                        node.last_handler = {}
                    }
                    node.last_handler[event] = method;
                    self.connect(node, event, method);
                }
           );
        },

        off: function (filter, event) {
            var self = this;
            filter.forEach(
                function(node, index, arr) {
                    self.disconnect(node, event);
                }
            );
        },
        
        restart : function(filter, event) {
            var self = this;
            filter.forEach(
                function(node, index, arr) {
                    self.connect(node, event, node.last_handler[event]);
                }
            );
        },
        
        ///////////////////////////////////////////////////
        //// Game & client states
        
        // onEnteringState: this method is called each time we are entering into a new game state.
        //                  You can use this method to perform some user interface changes at this moment.
        //
        onEnteringState: function (stateName, args) {
            console.log('Entering state: '+stateName)
            console.log(args)

            if (this.initializing) { // Here, do things that have to be done on setup but that cannot be done inside the function
                
                for(var player_id in this.players) { // Displaying player BGA scores
                    this.scoreCtrl[player_id].setValue(this.players[player_id].player_score); // BGA score = number of claimed achievements
                    var tooltip_help = _("Number of achievements. ${n} needed to win").replace('${n}', this.number_of_achievements_needed_to_win);
                    this.addCustomTooltip('player_score_' + player_id, tooltip_help, "");
                    this.addCustomTooltip('icon_point_' + player_id, tooltip_help, "");
                }
                
                // Now the game is really truly set up
                this.initializing = false;
            }

            // Things to do for all players
            switch (stateName) {
            case 'turn0':
                if (args.args.team_game) {
                    this.addToLog(args.args.messages[this.player_id]);
                }
            
                if (this.selected_card !== null) {
                    dojo.addClass(this.getCardHTMLId(this.selected_card.id, this.selected_card.age, this.selected_card.type, this.selected_card.is_relic, this.HTML_class.my_hand), 'selected')
                }
                break;
            case 'artifactPlayerTurn':
                this.destroyActionCard();
                this.givePlayerActionCard(this.getActivePlayerId(), 0);
                break;
            case 'playerTurn':
                this.destroyActionCard();
                this.givePlayerActionCard(this.getActivePlayerId(), args.args.action_number);
                break;
            case 'whoBegins':
                dojo.query(".selected").removeClass("selected");
                break;
            case 'dogmaEffect':
            case 'playerInvolvedTurn':
                // Highlight the current effect if visible
                dojo.query(args.args.JSCardEffectQuery).addClass("current_effect");
                break;
            case 'interPlayerInvolvedTurn':
            case 'interDogmaEffect':
                dojo.query(".current_effect").removeClass("current_effect");
                break;
            case 'gameEnd':
                // Set player panels for the last time properly        
                var result = args.args.result;
                for (var p=0; p<result.length;p++) {
                    var player_result = result[p];
                    var player_id = player_result.player;
                    var player_score = player_result.score;
                    var player_score_aux = player_result.score_aux;
                    
                    // Gold star => BGA score: remove the tooltip which says that it's the number of achievements because it is not the case in end by score or by dogma and set the counter to its appropriate value
                    this.removeTooltip('player_score_' + player_id);
                    this.scoreCtrl[player_id].setValue(player_score);
                    
                    // Silver star => BGA tie breaker: remove the tooltip and set the counter to its appropriate value
                    this.removeTooltip('score_count_container_' + player_id);
                    this.counter.score[player_id].setValue(player_score_aux);
                }
                break;
            }
            
            switch(stateName) {
            case 'playerInvolvedTurn':
            case 'interPlayerInvolvedTurn':
            case 'interactionStep':
            case 'interInteractionStep':
            case 'preSelectionMove':
            case 'interSelectionMove':
                var player_name = args.args.player_id == this.player_id ? args.args.player_name_as_you : args.args.player_name;
                $('pagemaintitletext').innerHTML = $('pagemaintitletext').innerHTML.replace('${player}', player_name);
                break;
            }
            
            // Is it a state I'm supposed to play?
            if (this.isCurrentPlayerActive()) {
                // I am supposed to play
                
                switch (stateName) {
                case 'turn0':
                    this.addTooltipsWithActionsToMyHand();
                    var cards_in_hand = this.selectMyCardsInHand();
                    cards_in_hand.addClass("clickable");
                    this.on(cards_in_hand, 'onclick', 'action_clicForInitialMeld');
                    break;
                case 'artifactPlayerTurn':
                    this.addTooltipWithDogmaActionToMyArtifactOnDisplay(args.args._private.dogma_effect_info);
                    break;    
                case 'promoteCardPlayerTurn':
                    if (!this.isInReplayMode()) {
                        this.my_forecast_verso_window.show();
                    }
                    var max_age_to_promote = parseInt(args.args.max_age_to_promote);
                    // Make it possible to click or hover on the front of the cards in the forecast
                    this.addTooltipsWithActionsToMyForecast(max_age_to_promote);
                    var cards_in_forecast = this.selectMyCardsInForecast(max_age_to_promote);
                    cards_in_forecast.addClass("clickable");
                    this.on(cards_in_forecast, 'onclick', 'action_clickForPromote');
                    // Make it possible to click the backs of the cards in the forecast
                    var card_backs_in_forecast = this.selectMyCardBacksInForecast(max_age_to_promote);
                    card_backs_in_forecast.addClass("clickable");
                    this.on(card_backs_in_forecast, 'onclick', 'action_clickCardBackForPromote');
                    break;
                case 'dogmaPromotedPlayerTurn':
                    var card_id = parseInt(args.args.promoted_card_id);
                    var promoted_card = dojo.query("#board_" + this.player_id + " .item_" + card_id);
                    promoted_card.addClass("clickable");
                    this.on(promoted_card, 'onclick', 'action_clickForDogmaPromoted');
                    break;
                case 'playerTurn':
                    // Claimable achievements (achieve action)
                    if (args.args.claimable_ages.length > 0) {
                        var claimable_achievements = this.selectClaimableAchievements(args.args.claimable_ages);
                        claimable_achievements.addClass("clickable");
                        this.on(claimable_achievements, 'onclick', 'action_clicForAchieve');
                    }
                    
                    // Top drawable card on deck (draw action)
                    if (args.args.age_to_draw <= 10) {
                        var drawable_card = this.selectDrawableCard(args.args.age_to_draw, args.args.type_to_draw);
                        drawable_card.addClass("clickable");
                        this.on(drawable_card, 'onclick', 'action_clicForDraw');
                    }
                    
                    // Cards in hand (meld action)
                    this.addTooltipsWithActionsToMyHand();
                    var cards_in_hand = this.selectMyCardsInHand();
                    cards_in_hand.addClass("clickable");
                    this.off(cards_in_hand, 'onclick'); // Remove possible stray handler from initial meld.
                    this.on(cards_in_hand, 'onclick', 'action_clickMeld');

                    // Artifact on display (meld action)
                    this.addTooltipWithMeldActionToMyArtifactOnDisplay();
                    var artifact_on_display = this.selectArtifactOnDisplay();
                    artifact_on_display.addClass("clickable");
                    this.on(artifact_on_display, 'onclick', 'action_clickMeld');
                    
                    // Cards on board (dogma action)
                    this.addTooltipsWithActionsToMyBoard(args.args._private.dogma_effect_info);
                    var cards_on_board = this.selectMyTopCardsEligibleForDogma();
                    cards_on_board.addClass("clickable");
                    this.on(cards_on_board, 'onclick', 'action_clickDogma');
                    
                    break;
                case 'selectionMove':
                    this.choose_two_colors = args.args.special_type_of_choice == 5; // choose_two_colors
                    this.choose_three_colors = args.args.special_type_of_choice == 9; // choose_three_colors
                    this.choose_integer = args.args.special_type_of_choice == 11; // choose_non_negative_integer
                    if (args.args.special_type_of_choice == 0) {
                        // Allowed selected cards by the server
                        var visible_selectable_cards = this.selectCardsFromList(args.args._private.visible_selectable_cards);
                        if (visible_selectable_cards !== null) {
                            visible_selectable_cards.addClass("clickable").addClass('mid_dogma');
                            this.on(visible_selectable_cards, 'onclick', 'action_clicForChoose');
                            if (args.args._private.must_show_score && !this.isInReplayMode()) {
                                this.my_score_verso_window.show();
                            }
                        }
                        var selectable_rectos = this.selectRectosFromList(args.args._private.selectable_rectos);
                        if (selectable_rectos !== null) {
                            selectable_rectos.addClass("clickable").addClass('mid_dogma');
                            this.on(selectable_rectos, 'onclick', 'action_clicForChooseRecto');
                        }
                        if (args.args._private.show_all_cards_on_board) {
                            for (var color = 0; color < 5; color++) {
                                var zone = this.zone.board[this.player_id][color];
                                this.setSplayMode(zone, zone.splay_direction, full_visible=true);
                            }
                        }
                        // Add special warning to Tools to prevent the player from accidentally returning a 3 in the first
                        // part of the interaction in an attempt to draw 3 cards.
                        if (args.args.card_interaction == "1N1A" && parseInt(args.args.num_cards_already_chosen) == 0) {
                            var age_3s_in_hand = dojo.query("#hand_" + this.player_id + " > .card.age_3");
                            this.off(age_3s_in_hand, 'onclick', 'action_clicForChoose');
                            this.on(age_3s_in_hand, 'onclick', 'action_clickForChooseFront');
                            var warning = _("Are you sure you want to return a ${age_3}? This won't allow you to draw three cards.").replace("${age_3}", this.square('N', 'age', 3));
                            age_3s_in_hand.forEach(function(card) {
                                dojo.attr(card, 'warning', warning);
                            });
                        }
                    }
                    else if (args.args.special_type_of_choice == 6 /* rearrange */) {
                        this.off(dojo.query('#change_display_mode_button'), 'onclick');
                        for(var color=0; color<5; color++) {
                            var zone = this.zone.board[this.player_id][color];
                            this.setSplayMode(zone, zone.splay_direction, full_visible=true); // Show all cards
                        }
                        this.publication_permutations_done = [];
                        
                        var selectable_cards = this.selectAllCardsOnMyBoard();
                        selectable_cards.addClass("clickable").addClass('mid_dogma');
                        this.on(selectable_cards, 'onclick', 'publicationClicForMove');
                    }
                    
                    if (args.args.color_pile !== null) { // The selection involves cards in a stack
                        this.color_pile = args.args.color_pile;
                        var zone = this.zone.board[this.player_id][this.color_pile];
                        this.setSplayMode(zone, zone.splay_direction, full_visible=true); // Show all cards of that stack
                    }
                    
                    if (args.args.splay_direction !== null) {
                        // Update tooltips for cards of stacks that can be splayed
                        this.addTooltipsWithSplayingActionsToColorsOnMyBoard(args.args.splayable_colors, args.args.splayable_colors_in_clear, args.args.splay_direction, args.args.splay_direction_in_clear);
                    }
                    
                    if ((args.args.can_pass || args.args.can_stop) && (args.args.special_type_of_choice == 0 || args.args.special_type_of_choice == 6 /* rearrange */) && args.args.splay_direction === null) {
                        $('pagemaintitletext').innerHTML += " " + _("or")
                    }
                    break;
                }
            } else {
                // I am not supposed to play
                switch (stateName) {
                case 'turn0':
                    this.addTooltipsWithActionsToMyHand();
                    
                    var cards_in_hand = this.selectMyCardsInHand();
                    cards_in_hand.addClass("clickable");
                    this.on(cards_in_hand, 'onclick', 'action_clickForUpdatedInitialMeld');
                    break;
                case 'selectionMove':
                    // Add more information about the cards which can be selected
                    if (args.args.splay_direction !== null) {
                        var end_of_message = []
                        for (var i=0; i < args.args.splayable_colors_in_clear.length; i++) {
                            end_of_message.push(dojo.string.substitute(_("splay his ${cards} ${direction}"), {'cards': args.args.splayable_colors_in_clear[i], 'direction': args.args.splay_direction_in_clear}))
                        }
                        $('pagemaintitletext').innerHTML += " " + end_of_message.join(", ");  
                    }
                    
                    // Add if the player can pass or stop
                    if (args.args.can_pass || args.args.can_stop) {
                        if (args.args.can_pass) {
                            var message = " " + _("or pass");
                        }
                        else  { // args.can_stop
                            var message = " " + _("or stop");    
                        }
                        $('pagemaintitletext').innerHTML += message;
                    }
                    break;
                }
            }
        },

        // onLeavingState: this method is called each time we are leaving a game state.
        //                 You can use this method to perform some user interface changes at this moment.
        //
        onLeavingState: function (stateName) {
            this.deactivateClickEvents(); // If this was not done after a click event (game replay for instance)
            
            // Was it a state I was supposed to play?
            if (this.isCurrentPlayerActive()) {
                // I was supposed to play
                
                switch (stateName) {
                case 'promoteCardPlayerTurn':
                    if (!this.isInReplayMode()) {
                        this.my_forecast_verso_window.hide();
                    }
                    this.addTooltipsWithoutActionsToMyForecast();
                    break;
                case 'playerTurn':
                    this.addTooltipsWithoutActionsToMyHand();
                    this.addTooltipsWithoutActionsToMyBoard();
                    // TODO(LATER): Figure out if this fallthrough is intentional or is a bug. Maybe this is causing https://boardgamearena.com/bug?id=13012.
                case 'selectionMove':
                    // Reset tooltips for board (in case there was a splaying choice)
                    this.addTooltipsWithoutActionsToMyBoard();
                    if (!this.isInReplayMode()) {
                        this.my_score_verso_window.hide();
                    }
                    for(var color=0; color<5; color++) {
                        var zone = this.zone.board[this.player_id][color];
                        this.setSplayMode(zone, zone.splay_direction, force_full_visible=false);
                    }
                }
            }
        }, 

        // onUpdateActionButtons: in this method you can manage "action buttons" that are displayed in the
        //                        action status bar (ie: the HTML links in the status bar).
        //        
        onUpdateActionButtons: function (stateName, args) {
            if(this.isCurrentPlayerActive()) {            
                switch(stateName) {
                case 'relicPlayerTurn':
                    if (args.can_seize_to_hand) {
                        this.addActionButton("seize_relic_to_hand", _("Seize to Hand"), "action_clicForSeizeRelicToHand");
                    }
                    if (args.can_seize_to_achievements) {
                        this.addActionButton("seize_relic_to_achievements", _("Seize to Achievements"), "action_clicForSeizeRelicToAchievements");
                    }
                    this.addActionButton("pass_seize_relic", _("Pass"), "action_clicForPassSeizeRelic");
                    if (this.canShowCardTooltip(args.relic_id)) {
                        this.addCustomTooltipToClass("card_id_" + args.relic_id, this.getTooltipForCard(args.relic_id), "");
                    }
                    break;
                case 'artifactPlayerTurn':
                    if (this.selectArtifactOnDisplayIfEligibleForDogma().length == 1) {
                        this.addActionButton("dogma_artifact", _("Dogma and Return"), "action_clicForDogmaArtifact");
                    }
                    this.addActionButton("return_artifact", _("Return"), "action_clicForReturnArtifact");
                    this.addActionButton("pass_artifact", _("Pass"), "action_clicForPassArtifact");
                    break;
                case 'promoteCardPlayerTurn':
                    this.addActionButton("pass_promote", _("Pass"), "action_clickForPassPromote");
                    break;
                case 'dogmaPromotedPlayerTurn':
                    this.addActionButton("dogma_promoted", _("Dogma"), "action_clickForDogmaPromoted");
                    this.addActionButton("pass_dogma_promoted", _("Pass"), "action_clickForPassDogmaPromoted");
                    break;
                case 'playerTurn':
                    // Red buttons for claimable_achievements
                    for (var i=0; i<args.claimable_ages.length; i++) {
                        var age = args.claimable_ages[i];
                        var HTML_id = "achieve_" + age;
                        this.addActionButton(HTML_id, _("Achieve ${age}").replace("${age}", this.square('N', 'age', age)), "action_clicForAchieve");
                        dojo.removeClass(HTML_id, 'bgabutton_blue');
                        dojo.addClass(HTML_id, 'bgabutton_red');
                    }
                    
                    // Blue buttons for draw action (or red if taking this action would finish the game)
                    if (args.age_to_draw <= 10) {
                        this.addActionButton("take_draw_action", _("Draw a ${age}").replace("${age}", this.square('N', 'age', args.age_to_draw, 'type_' + args.type_to_draw)), "action_clicForDraw");
                    }
                    else {
                        this.addActionButton("take_draw_action", _("Finish the game (attempt to draw above ${age_10})").replace('${age_10}', this.square('N', 'age', 10)), "action_clicForDraw")
                    }
                    dojo.place("<span class='extra_text'> , " + _("meld or dogma") + "</span>", "take_draw_action", "after")
                    break;
                case 'selectionMove':
                    var special_type_of_choice_with_buttons = args.special_type_of_choice != 0 && args.special_type_of_choice != 6 /* rearrange */;
                    var splay_choice = args.splay_direction !== null;
                    if (args.special_type_of_choice == 11 /* choose_non_negative_integer */) {
                        this.addActionButton("decrease_integers", "<<", "action_clickButtonToDecreaseIntegers");
                        dojo.removeClass("decrease_integers", 'bgabutton_blue');
                        dojo.addClass("decrease_integers", 'bgabutton_red');
                        var default_integer = parseInt(args.default_integer);
                        if (default_integer == 0) {
                            dojo.byId('decrease_integers').style.display = 'none';
                        }
                        for (var i = 0; i < 6; i++) {
                            this.addActionButton("choice_" + i, default_integer + i, "action_clicForChooseSpecialOption");
                        }
                        this.addActionButton("increase_integers", ">>", "action_clickButtonToIncreaseIntegers");
                        dojo.removeClass("increase_integers", 'bgabutton_blue');
                        dojo.addClass("increase_integers", 'bgabutton_red');
                    } else if (special_type_of_choice_with_buttons) {
                        // Add a button for each available options
                        for(var i=0; i<args.options.length; i++) {
                            var option = args.options[i];
                            // NOTE: The option.age substitution is only used by the Evolution dogma
                            this.addActionButton("choice_" + option.value, dojo.string.substitute(_(option.text), {'age': option.age}), "action_clicForChooseSpecialOption")
                        }
                        var last_button = "choice_" + args.options[args.options.length-1].value;
                    } else if (splay_choice) {
                        // Add button for splaying choices
                        for (var i=0; i<args.splayable_colors.length; i++) {
                            if (i > 0) {
                                dojo.place("<span class='extra_text'> ,</span>", "splay_" + args.splayable_colors[i-1], "after")
                            }
                            this.addActionButton("splay_" + args.splayable_colors[i], dojo.string.substitute(_("Splay your ${cards} ${direction}"), {'cards': _(args.splayable_colors_in_clear[i]), 'direction': _(args.splay_direction_in_clear)}), "action_clicForSplay")
                        }
                        var last_button = "splay_" + args.splayable_colors[args.splayable_colors.length-1];
                    }
                
                    // Add a button if I can pass or stop
                    if (args.can_pass || args.can_stop) {
                        if (special_type_of_choice_with_buttons || splay_choice) {
                            dojo.place("<span class='extra_text'> " + _("or") + "</span>", last_button, "after")
                        }
                        if (args.can_pass) {
                            var action = "pass";
                            var message = _("Pass");
                        }
                        else  { // args.can_stop
                            var action = "stop";
                            var message = _("Stop");    
                        }
                        this.addActionButton(action, message, "action_clicForPassOrStop");    
                    }
                    break;
                }
            }
        },        

        ///////////////////////////////////////////////////
        //// Utility methods
        
        /*
        
            Here, you can defines some utility methods that you can use everywhere in your javascript
            script.
        
        */
        
        addToLog : function(message) {
            HTML = dojo.string.substitute('<div class="log" style="height: auto; display: block; color: rgb(0, 0, 0);"><div class="roundedbox">${msg}</div></div>',
                {'msg': message})
            dojo.place(HTML, $('logs'), 'first')
        },

        startActionTimer(buttonId, time, callback, callbackParam) {
            var button = $(buttonId);
            var isReadOnly = this.isReadOnly();
            if (button == null || isReadOnly) {
                return;
            }
    
            this._actionTimerLabel = button.innerHTML;
            this._actionTimerSeconds = time + 1;
            this._callback = callback;
            this._callbackParam = callbackParam;
            this._actionTimerFunction = () => {
                var button = $(buttonId);
                if (button == null) {
                    this.stopActionTimer();
                } else if (this._actionTimerSeconds-- > 1) {
                    button.innerHTML = this._actionTimerLabel + ' (' + this._actionTimerSeconds + ')';
                } else {
                    button.innerHTML = this._actionTimerLabel;
                    this._callback(this._callbackParam);
                    this.stopActionTimer();
                }
            };
            this._actionTimerFunction();
            this._actionTimerId = window.setInterval(this._actionTimerFunction, 1000);
        },
    
        stopActionTimer() {
            if (this._actionTimerId != null) {
                window.clearInterval(this._actionTimerId);
                delete this._actionTimerId;
            }
        },
        
        addButtonForViewFull : function() {
            var button_text = this.view_full ? this.text_for_view_full : this.text_for_view_normal;
            
            if (this.isSpectator) {
                var player_panel = dojo.query(".player:nth-of-type(1)")[0];
                var player_id = dojo.attr(player_panel, 'id').substr(7); // Get the first player (on top)
            }
            else {
                var player_id = this.player_id;
            }
            
            var button = this.format_string_recursive("<i id='change_view_full_button' class='bgabutton bgabutton_gray'>${button_text}</i>", {'button_text':button_text, 'i18n':['button_text']});
            
            dojo.place(button, 'name_' + player_id, 'after');
            this.addCustomTooltip('change_view_full_button', '<p>' + _('Use this to look at all the cards on the board.') + '</p>', "")
            this.on(dojo.query('#change_view_full_button'), 'onclick', 'toggle_view');
        },
        
        addButtonForSplayMode : function() {
            var button_text = this.display_mode ? this.text_for_expanded_mode : this.text_for_compact_mode;
            var arrows =  this.display_mode ? this.arrows_for_expanded_mode : this.arrows_for_compact_mode;
            
            var button = this.format_string_recursive("<i id='change_display_mode_button' class='bgabutton bgabutton_gray'>${arrows} ${button_text}</i>", {'arrows':arrows, 'button_text':button_text, 'i18n':['button_text']});
            
            dojo.place(button, 'change_view_full_button', 'after');
            this.addCustomTooltip('change_display_mode_button', '<p>' + _('<b>Expanded mode:</b> the splayed stacks are displayed like in real game, to show which icons are made visible.') + '</p>' +
                                                                '<p>' + _('<b>Compact mode:</b> the splayed stacks are displayed with minimum offset, to save space.') + '</p>', "")
            
            this.disableButtonForSplayMode(); // Disabled by default
        },

        disableButtonForSplayMode : function() {
            var change_display_mode_button = dojo.query('#change_display_mode_button');
            this.off(change_display_mode_button, 'onclick');
            change_display_mode_button.addClass('disabled');
        },
        
        enableButtonForSplayMode : function() {
            var change_display_mode_button = dojo.query('#change_display_mode_button');
            this.on(change_display_mode_button, 'onclick', 'toggle_displayMode');
            change_display_mode_button.removeClass('disabled');
        },

        addButtonForBrowsingCards : function() {
            // Build button
            var button_text = _("Browse all cards");
            var button = this.format_string_recursive("<i id='browse_all_cards_button' class='bgabutton bgabutton_gray'>${button_text}</i>", {'button_text': button_text, 'i18n': ['button_text']});
            dojo.place(button, 'change_display_mode_button', 'after');
            this.addCustomTooltip('browse_all_cards_button', '<p>' + _('Browse the full list of cards, including special achievement.') + '</p>', "")

            // Build popup box
            this.card_browsing_window = new dijit.Dialog({ 'title': _("Browse All Cards") });
            var ids = [106, 105, 108, 107, 109];
            if (this.cities_expansion_enabled) {
                ids.push(325, 326, 327, 328, 329);
            }
            if (this.echoes_expansion_enabled) {
                ids.push(439, 436, 435, 437, 438);
            }
            // TODO(FIGURES): Add special achievements.
            var content = "";
            
            content += "<div id='browse_cards_buttons_row_1'>";
            content += "<div class='browse_cards_button bgabutton bgabutton_gray' id='browse_cards_type_0'>" + _("Base Set") + "</div>";
            if (this.artifacts_expansion_enabled) {
                content += "<div class='browse_cards_button bgabutton bgabutton_gray' id='browse_cards_type_1'>" + _("Artifacts") + "</div>";
            }
            if (this.cities_expansion_enabled) {
                content += "<div class='browse_cards_button bgabutton bgabutton_gray' id='browse_cards_type_2'>" + _("Cities") + "</div>";
            }
            if (this.echoes_expansion_enabled) {
                content += "<div class='browse_cards_button bgabutton bgabutton_gray' id='browse_cards_type_3'>" + _("Echoes") + "</div>";
            }
            content += "<div class='browse_cards_button bgabutton bgabutton_gray selected' id='browse_special_achievements'>" + _("Special Achievements") + "</div>";
            // TODO(FIGURES): Add button for Figures.
            content += "</div>";

            content += "<div id='browse_cards_buttons_row_2'>";
            for (var age = 1; age <= 10; age++) {
                content += "<div class='browse_cards_button bgabutton bgabutton_gray' id='browse_cards_age_" + age + "'>" + age + "</div>";
            }
            if (this.relics_enabled) {
                content += "<div class='browse_cards_button bgabutton bgabutton_gray' id='browse_relics'>" + _("Relics") + "</div>";
            }
            content += "</div>";
            content += "<div id='browse_card_summaries'></div>";

            content += "<div id='special_achievement_summaries'>";
            for (var i = 0; i < ids.length; i++) {
                var card_id = ids[i];
                var card_data = this.cards[card_id];

                var name = _(card_data.name).toUpperCase();
                var text = `<b>${name}</b>: ${this.parseForRichedText(_(card_data.condition_for_claiming), 'in_tooltip')}`;
                if (card_id == 106) {
                    text += ` ${_("Note: Transfered cards from other players do not count toward this achievement, nor does exchanging cards from your hand and score pile.")}`;
                }
                if (card_data.alternative_condition_for_claiming != null) {
                    text += ` ${this.parseForRichedText(_(card_data.alternative_condition_for_claiming), 'in_tooltip')}`;
                }
                content += `<div id="special_achievement_summary_${card_id}" class="special_achievement_summary">`;
                content += `<div class="special_achievement_icon"><div class="item_${card_id} age_null S card"></div><div class="special_achievement_status"></div></div>`;
                content += `<div class="special_achievement_text">${text}</div>`
                content += `</div></br>`;
            }
            content += `</div>`;
            this.card_browsing_window.attr("content", content + "<a id='close_card_browser_button' class='bgabutton bgabutton_blue'>Close</a>");
            dojo.byId('browse_cards_buttons_row_2').style.display = 'none';

            // Make everything clickable
            this.on(dojo.query('#browse_all_cards_button'), 'onclick', 'click_open_card_browsing_window');
            this.on(dojo.query('#close_card_browser_button'), 'onclick', 'click_close_card_browsing_window');
            this.on(dojo.query('.browse_cards_button:not(#browse_special_achievements)'), 'onclick', 'click_browse_cards');
            this.on(dojo.query('#browse_special_achievements'), 'onclick', 'click_browse_special_achievements');
        },

        refreshSpecialAchievementProgression : function() {
            // Don't check special achievement progress if this is a spectator
            if (this.isSpectator) {
                return;
            }

            // Refresh progression towards the player achieving the special achievements
            self = this;
            dojo.query(".special_achievement_summary").forEach(function(node) {
                var id = parseInt(node.id.substring(node.id.lastIndexOf('_') + 1));
                var numerator = -1;
                var denominator = -1;

                // Skip calculation if the special achievement is already claimed
                if (dojo.query(`#special_achievement_summary_${id}.unclaimed`).length == 1) {
                    switch(id) {
                        case 105:
                            // three or more icons of all six types
                            numerator = 0;
                            denominator = 6;
                            for (var i = 1; i <= 6; i++) {
                                if (self.counter.ressource_count[self.player_id][i].getValue() >= 3) {
                                    numerator++;
                                }
                            }
                            break;
                        case 106:
                            // tuck six or score six cards during a single turn
                            numerator = Math.max(self.number_of_tucked_cards, self.number_of_scored_cards);
                            denominator = 6;
                            break;
                        case 107:
                            // five colors on your board, and each is splayed either up or right
                            numerator = 0;
                            denominator = 5;
                            for (var i = 0; i < 5; i++) {
                                var splay_direction = self.zone.board[self.player_id][i].splay_direction;
                                if (splay_direction == 2 || splay_direction == 3) {
                                    numerator++;
                                }
                            }
                            break;
                        case 108:
                            // twelve or more visible clocks on your board
                            numerator = self.counter.ressource_count[self.player_id][6].getValue();
                            denominator = 12;
                            break;
                        case 109:
                            // five top cards, and each is of value 8 or higher
                            numerator = 0;
                            denominator = 5;
                            for (var i = 0; i < 5; i++) {
                                var items = self.zone.board[self.player_id][i].items;
                                if (items.length > 0) {
                                    var top_card_id = self.getCardIdFromHTMLId(items[items.length - 1].id);
                                    var top_card = self.cards[top_card_id];
                                    if (top_card.age >= 8) {
                                        numerator++;
                                    }
                                }
                            }
                            break;
                        case 435:
                            // eight or more bonuses visible on your board
                            numerator = 0;
                            denominator = 8;
                            for (var i = 0; i < 5; i++) {
                                var pile_zone = self.zone.board[self.player_id][i];
                                numerator += self.getVisibleBonusIconsInPile(pile_zone.items, pile_zone.splay_direction).length;
                            }
                            break;
                        case 436:
                            // seven or more cards in your forecast
                            numerator = self.zone.forecast[self.player_id].items.length;
                            denominator = 7;
                            break;
                        case 437:
                            // eight or more hex icons visible in one color
                            numerator = 0;
                            denominator = 8;
                            for (var i = 0; i < 5; i++) {
                                var pile_zone = self.zone.board[self.player_id][i];
                                var num_icons = self.countVisibleIconsInPile(pile_zone.items, pile_zone.splay_direction, 0 /* hex icon */);
                                if (num_icons > numerator) {
                                    numerator = num_icons;
                                }
                            }
                            break;
                        case 438:
                            // a color with four or more visible echo effects
                            numerator = 0;
                            denominator = 4;
                            for (var i = 0; i < 5; i++) {
                                var pile_zone = self.zone.board[self.player_id][i];
                                var num_icons = self.countVisibleIconsInPile(pile_zone.items, pile_zone.splay_direction, 10 /* echo icon */);
                                if (num_icons > numerator) {
                                    numerator = num_icons;
                                }
                            }
                            break;
                        case 439:
                            // three icons or more of the same icon type visible in each of four different colors
                            numerator = 0;
                            denominator = 4;
                            for (var icon = 1; icon <= 6; icon++) {
                                var num_piles = 0;
                                for (var color = 0; color < 5; color++) {
                                    var pile_zone = self.zone.board[self.player_id][color];
                                    if (self.countVisibleIconsInPile(pile_zone.items, pile_zone.splay_direction, icon) >= 3) {
                                        num_piles++;
                                    }
                                }
                                if (num_piles > numerator) {
                                    numerator = num_piles;
                                }
                            }
                            break;
                    }
                }
                dojo.query(`#special_achievement_summary_${id} .special_achievement_status`)[0].innerHTML = (numerator >= 0 && denominator > 0) ? `${numerator}/${denominator}` : "";
            });
        },
        
        /*
         * Id management
         */
        uniqueId : function() {
            return ++this.incremental_id;
        },

        uniqueIdForCard : function(age, type, is_relic) {
            // We need to multiply by a large number like 1000 to avoid colliding with the IDs of real cards
            return ((this.uniqueId() * 1000 + age) * 5 + type) * 2 + parseInt(is_relic);
        },
        
        /*
         * Icons and little stuff
         */

        square : function(size, type, key, context = null) {
            age = null, agetype = null;
            if (type === 'age') {
                age = key;
                agetype = type;
            }
            ret = "<span class='square";
            ret += " " + size;
            ret += (agetype !== null ? " " + agetype: "");
            ret += " " + type + "_" + key;
            ret += (context !== null ? " " + context : "");
            ret += "'>"
            ret += (age !== null ? " " + age: "");
            ret += "</span>";
            return ret
        },
        
        all_icons : function(type) {
            return "<span class='icon_1 square " + type + "'></span>" +
                    "&nbsp<span class='icon_2 square " + type + "'></span>" +
                    "&nbsp<span class='icon_3 square " + type + "'></span>" +
                    "&nbsp<span class='icon_4 square " + type + "'></span>" +
                    "&nbsp<span class='icon_5 square " + type + "'></span>" +
                    "&nbsp<span class='icon_6 square " + type + "'></span>"
        },
         
        /*
         * Tooltip management
         */
        shapeTooltip : function(help_HTML, action_HTML) {
            var help_string_passed = help_HTML != "";
            var action_string_passed = action_HTML != "";
            var HTML = "<table class='tooltip'>";
            if (help_string_passed) {
                HTML += "<tr><td>" + this.square('basic', 'icon', 'help', 'in_tooltip') + "</td><td class='help in_tooltip'>" + help_HTML + "</td></tr>";
            }
            if (action_string_passed) {
                HTML += "<tr><td>" + this.square('basic', 'icon', 'action', 'in_tooltip') + "</td><td class='action in_tooltip'>" + action_HTML + "</td></tr>";
            }
            HTML += "</table>"
            return HTML;
        },
        
        addCustomTooltip : function(nodeId, help_HTML, action_HTML) {
            // TODO(LATER): Pass 0 instead of undefined when using a desktop so that tooltips are faster.
            this.addTooltipHtml(nodeId, this.shapeTooltip(help_HTML, action_HTML), undefined);
        },
        
        addCustomTooltipToClass : function(cssClass, help_HTML, action_HTML) {
            // TODO(LATER): Pass 0 instead of undefined when using a desktop so that tooltips are faster.
            this.addTooltipHtmlToClass(cssClass, this.shapeTooltip(help_HTML, action_HTML), undefined);
        },
        
        addTooltipForCard : function(card) {
            var zone = this.getZone(card['location'], card.owner, card.type, card.age, card.color);

            // The score pile and forecast are a special case because both the front and back are rendered
            if (card.owner == this.player_id && (card.location == 'score' || card.location == 'forecast')) {
                var front_HTML_id = this.getCardHTMLId(card.id, card.age, card.type, card.is_relic, 'M card');
                this.addCustomTooltip(front_HTML_id, this.getTooltipForCard(card.id), "");
                var back_id = this.getCardIdFromPosition(zone, card.position, card.age, card.type, card.is_relic);
                var back_HTML_id = this.getCardHTMLId(back_id, card.age, card.type, card.is_relic, 'S recto');
                this.addCustomTooltip(back_HTML_id, this.getTooltipForCard(card.id), "");
                return;
            }

            var HTML_id = this.getCardHTMLId(card.id, card.age, card.type, card.is_relic, zone.HTML_class);
             // Special achievement
             if (card.age === null) {
                this.addCustomTooltip(HTML_id, this.getSpecialAchievementText(card), "");
                return;
            }
            this.addCustomTooltip(HTML_id, this.getTooltipForCard(card.id), "");
        },

        getTooltipForCard : function(card_id) {
            if (this.saved_HTML_cards[card_id] === undefined) {
                var card = this.cards[card_id];
                this.saved_HTML_cards[card_id] = this.createCard(card_id, card.age, card.type, card.is_relic, "L card", card);
            }
            return this.saved_HTML_cards[card_id];
        },
        
        addTooltipForStandardAchievement : function(card) {
            var zone = this.getZone(card['location'], card.owner, card.type, card.age);
            var id = this.getCardIdFromPosition(zone, card.position, card.age, card.type, card.is_relic);
            var HTML_id = this.getCardHTMLId(id, card.age, card.type, card.is_relic, zone.HTML_class);
            
            condition_for_claiming = dojo.string.substitute(_('You can take an action to claim this age if you have at least ${n} points in your score pile and at least one top card of value equal or higher than ${age} on your board.'), {'age': this.square('N', 'age', card.age), 'n': 5 * card.age});
            this.addCustomTooltip(HTML_id, "<div class='under L_recto'>" + condition_for_claiming + "</div>", '');
        },
        
        addTooltipForReferenceCard : function() {
            var actions_text = _("${Actions} You must take two actions on your turn, in any order. You may perform the same action twice.");
            actions_text = dojo.string.substitute(actions_text, {'Actions' : "<span class='actions_header'>" + _("Actions:").toUpperCase() + "</span>"})
            var actions_div = this.createAdjustedContent(actions_text, 'actions_txt reference_card_block', '', 12);
            
            var meld_title = this.createAdjustedContent(_("Meld").toUpperCase(), 'meld_title reference_card_block', '', 30);
            var meld_parag_text = _("Play a card from your hand to your board, on a stack of matching color. Continue any splay if present.");
            var meld_parag = this.createAdjustedContent(meld_parag_text, 'meld_parag reference_card_block', '', 12);
            
            var draw_title = this.createAdjustedContent(_("Draw").toUpperCase(), 'draw_title reference_card_block', '', 30);
            var draw_parag_text = _("Take a card of value equal to your highest top card from the supply piles. If empty, draw from the next available higher pile.");
            var draw_parag = this.createAdjustedContent(draw_parag_text, 'draw_parag reference_card_block', '', 12);
            
            var achieve_title = this.createAdjustedContent(_("Achieve").toUpperCase(), 'achieve_title reference_card_block', '', 30);
            var achieve_parag_text = _("To claim, must have score of at least 5x the age number in points, and a top card of equal or higher value. Points are kept, not spent.");
            var achieve_parag = this.createAdjustedContent(achieve_parag_text, 'achieve_parag reference_card_block', '', 12);
            
            var dogma_title = this.createAdjustedContent(_("Dogma").toUpperCase(), 'dogma_title reference_card_block', '', 30);
            var big_bullet = "&#9679;"
            var dogma_parag_text = _("Pick a top card on your board. Execute each effect on it, in order.") +
                                    "<ul><li>" + big_bullet + " " + _("I Demand effects are executed by each player with fewer of the featured icon than you, going clockwise. Read effects aloud to them.") + "</li>" +
                                    "<li>" + big_bullet + " " + _("Non-demand effects are executed by opponents before you, if they have at leadt as many or more of the featured icon, going clockwise.") + "</li>" +
                                    "<li>" + big_bullet + " " + _("If any opponent shared a non-demand effect, take a single free Draw action at the conclusion of your Dogma action.") + "</li></ul>";
            var dogma_parag = this.createAdjustedContent(dogma_parag_text, 'dogma_parag reference_card_block', '', 12);
            
            var tuck_title = this.createAdjustedContent(_("Tuck").toUpperCase(), 'tuck_title reference_card_block', '', 30);
            var tuck_parag_text = _("A tucked card goes to the bottom of the pile of its color. Tucking a card into an empty pile starts a new one.");
            var tuck_parag = this.createAdjustedContent(tuck_parag_text, 'tuck_parag reference_card_block', '', 12);
            
            var return_title = this.createAdjustedContent(_("Return").toUpperCase(), 'return_title reference_card_block', '', 30);
            var return_parag_text = _("To return a card, place it at the bottom of its matching supply pile. If you return many cards, you choose the order.");
            var return_parag = this.createAdjustedContent(return_parag_text, 'return_parag reference_card_block', '', 12);
            
            var draw_and_x_title = this.createAdjustedContent(_("DRAW and X"), 'draw_and_x_title reference_card_block', '', 30);
            var draw_and_x_parag_text = _("If instructed to Draw and Meld, Score, or tuck, you must use the specific card drawn for the indicated action.");
            var draw_and_x_parag = this.createAdjustedContent(draw_and_x_parag_text, 'draw_and_x_parag reference_card_block', '', 12);
            
            var splay_title = this.createAdjustedContent(_("Splay").toUpperCase(), 'splay_title reference_card_block', '', 30);
            var splay_parag_text = _("To splay, fan out the color as shown below. A color is only ever splayed in one direction. New cards tucked or melded continue the splay.");
            var splay_parag = this.createAdjustedContent(splay_parag_text, 'splay_parag reference_card_block', '', 12);

            var splayed_left_example = this.createAdjustedContent(_("Splayed left"), 'splayed_left_example reference_card_block', '', 12);
            var splayed_right_example = this.createAdjustedContent(_("Splayed right"), 'splayed_right_example reference_card_block', '', 12);
            var splayed_up_example = this.createAdjustedContent(_("Splayed up"), 'splayed_up_example reference_card_block', '', 12);
            
            var empty_piles_title = this.createAdjustedContent(_("Empty piles").toUpperCase(), 'empty_piles_title reference_card_block', '', 30);
            var empty_piles_parag_text = _("When drawing from an empty pile for <b>any reason</b>, draw from the next higher pile.");
            var empty_piles_parag = this.createAdjustedContent(empty_piles_parag_text, 'empty_piles_parag reference_card_block', '', 12);
            
            var age_1_3 = _("Age 1-3");
            var age_4_10 = _("Age 4-10");
            var age_7_10 = _("Age 7-10");
            var age_1_10= _("Age 1-10");
            
            var icon_4_ages = this.createAdjustedContent(age_1_3, 'icon_4_ages reference_card_block', '', 12);
            var icon_5_ages = this.createAdjustedContent(age_4_10, 'icon_5_ages reference_card_block', '', 12);
            var icon_6_ages = this.createAdjustedContent(age_7_10, 'icon_6_ages reference_card_block', '', 12);
            
            var icon_1_ages = this.createAdjustedContent(age_1_10, 'icon_1_ages reference_card_block', '', 12);
            var icon_2_ages = this.createAdjustedContent(age_1_10, 'icon_2_ages reference_card_block', '', 12);
            var icon_3_ages = this.createAdjustedContent(age_1_10, 'icon_3_ages reference_card_block', '', 12);
            
            var colors_title = this.createAdjustedContent(_("Colors:"), 'colors_title reference_card_block', '', 12);
            var blue_icon = this.createAdjustedContent(_("Blue"), 'blue_icon reference_card_block', '', 12);
            var yellow_icon = this.createAdjustedContent(_("Yellow"), 'yellow_icon reference_card_block', '', 12);
            var red_icon = this.createAdjustedContent(_("Red"), 'red_icon reference_card_block', '', 12);
            var green_icon = this.createAdjustedContent(_("Green"), 'green_icon reference_card_block', '', 12);
            var purple_icon = this.createAdjustedContent(_("Purple"), 'purple_icon reference_card_block', '', 12);
            
            side_1_content = actions_div;
            
            side_1_content += meld_title + meld_parag;
            side_1_content += draw_title + draw_parag;
            side_1_content += achieve_title + achieve_parag;
            side_1_content += dogma_title + dogma_parag;
            
            side_2_content = tuck_title + tuck_parag;
            side_2_content += return_title + return_parag;
            side_2_content += draw_and_x_title + draw_and_x_parag;
            
            side_2_content += splay_title + splay_parag + splayed_left_example + splayed_right_example + splayed_up_example;
            
            side_2_content += empty_piles_title + empty_piles_parag;
            
            side_2_content += icon_4_ages + icon_5_ages + icon_6_ages;
            side_2_content += icon_1_ages + icon_2_ages + icon_3_ages;
            
            side_2_content += colors_title + red_icon + purple_icon + blue_icon + green_icon + yellow_icon;
            
            // Assembling
            var div_side_1 = "<div class='reference_card side_1 M'>" + side_1_content + "</div>"
            var div_side_2 = "<div class='reference_card side_2 M'>" + side_2_content + "</div>"
            this.addTooltipHtmlToClass('reference_card', div_side_1 + div_side_2);
        },
        
        createAdjustedContent : function(content, HTML_class, size, font_max, width_margin = 0, height_margin = 0) {
            // Problem: impossible to get suitable text size because it is not possible to get the width and height of an element still unattached
            // Solution: first create the title hardly attached to the DOM, then destroy it and set the title in tooltip properly
            // Create temporary title hardly attached on the DOM
            var tempParentId = 'temp_parent';
            var tempId = 'temp';
            var div_title = "<div id='" + tempParentId + "' class='" + HTML_class + " " + size + "'><span id='" + tempId + "' >" + content + "</span></div>";
            
            dojo.place(div_title, dojo.body());
            
            // Determine the font-size between 1 and 30 which enables to fill the container without overflow
            var elementParent = $(tempParentId);
            var element = $(tempId);
            if (HTML_class === 'card_title') {
                dojo.addClass(elementParent, HTML_class);
            }
            var font_size = font_max;
            while (font_size >= 2) {
                if (font_size < font_max) {
                    dojo.removeClass(element, 'font_size_' + (font_size + 1));
                }
                dojo.addClass(element, 'font_size_' + font_size);
                var elementWidth = dojo.position(element).w + width_margin;
                var elementParentWidth = dojo.position(elementParent).w;
                var elementHeight = dojo.position(element).h + height_margin;
                var elementParentHeight = dojo.position(elementParent).h;
                if (elementWidth <= elementParentWidth && elementHeight <= elementParentHeight) {
                    break;
                }
                font_size--;
            }
            
            // Destroy the piece of HTML used for determination
            dojo.destroy(elementParent);

            // Create actual HTML which will be added in tooltip
            return "<div class='" + HTML_class + " " + size + "'><span class='font_size_" + font_size + "'>" + content + "</span></div>";            
        },
        
        createDogmaEffectText : function(text, dogma_symbol, size, color, shade, other_classes) {
            return "<div class='effect " + size + " " + shade + " " + other_classes + "'><span class='dogma_symbol color_" + color + " " + size + " icon_" + dogma_symbol + "'></span><span class='effect_text " + shade + " " + size + "'>" + this.parseForRichedText(text, size) + "<span></div>";
        },
        
        parseForRichedText : function(text, size) {
            if (text == null) {
                return null;
            }
            text = text.replace(new RegExp("\\$\\{I demand\\}" , "g"), "<strong class='i_demand'>" + _("I DEMAND") + "</strong>");
            text = text.replace(new RegExp("\\$\\{I compel\\}" , "g"), "<strong class='i_compel'>" + _("I COMPEL") + "</strong>");
            text = text.replace(new RegExp("\\$\\{immediately\\}" , "g"), "<strong class='immediately'>" + _("immediately") + "</strong>");
            text = text.replace(new RegExp("\\$\\{icons_1_to_6\\}" , "g"), this.all_icons('in_tooltip'));
            for (var age = 1; age <= 10; age++) {
                text = text.replace(new RegExp("\\$\\{age_" + age + "\\}" , "g"), this.square(size, 'age', age));
            }
            for (var symbol = 0; symbol <= 6; symbol++) {
                text = text.replace(new RegExp("\\$\\{icon_" + symbol + "\\}" , "g"), this.square(size, 'icon', symbol));
            }
            text = text.replace(new RegExp("\\$\\{music_note\\}" , "g"), this.square(size, 'music', 'note'));
            return text;
        },
        
        /*
         * Tooltip management for cards
         */

        addTooltipsWithoutActionsTo : function(nodes) {
            var self = this;
            nodes.forEach(function(node) {
                var HTML_id = dojo.attr(node, "id");
                var id = self.getCardIdFromHTMLId(HTML_id);
                var HTML_help = self.saved_HTML_cards[id];
                self.addCustomTooltip(HTML_id, HTML_help, "");
            });
        },
        
        addTooltipsWithoutActionsToMyHand : function() {
            this.addTooltipsWithoutActionsTo(this.selectMyCardsInHand());
        },

        addTooltipsWithoutActionsToMyForecast : function() {
            this.addTooltipsWithoutActionsTo(this.selectMyCardsInForecast());
        },

        addTooltipsWithoutActionsToMyBoard : function() {
            this.addTooltipsWithoutActionsTo(this.selectAllCardsOnMyBoard());
        },

        addTooltipsWithoutActionsToMyArtifactOnDisplay : function() {
            this.addTooltipsWithoutActionsTo(this.selectArtifactOnDisplay());
        },

        addTooltipsWithActionsTo : function(nodes, action_text_function, extra_param_1, extra_param_2) {
            var self = this;
            nodes.forEach(function(node) {
                var HTML_id = dojo.attr(node, "id");
                var id = self.getCardIdFromHTMLId(HTML_id);
                var HTML_help = self.saved_HTML_cards[id];
                var card = self.cards[id];
                var HTML_action = action_text_function(self, card, extra_param_1, extra_param_2);
                self.addCustomTooltip(HTML_id, HTML_help, HTML_action);
            });
        },

        addTooltipsWithActionsToMyHand : function() {
            var cards = this.selectMyCardsInHand();
            this.addTooltipsWithActionsTo(cards, this.createActionTextForMeld);
            var self = this;
            cards.forEach(function(card) {
                var HTML_id = dojo.attr(card, "id");
                var id = self.getCardIdFromHTMLId(HTML_id);
                dojo.attr(HTML_id, 'card_id', id);
            });
        },

        addTooltipsWithActionsToMyForecast : function(max_age_to_promote=null) {
            var cards = this.selectMyCardsInForecast(max_age_to_promote);
            this.addTooltipsWithActionsTo(cards, this.createActionTextForMeld);
            var self = this;
            cards.forEach(function(card) {
                var HTML_id = dojo.attr(card, "id");
                var id = self.getCardIdFromHTMLId(HTML_id);
                dojo.attr(HTML_id, 'card_id', id);
            });
        },

        addTooltipsWithActionsToMyBoard : function(dogma_effect_info) {
            var cards = this.selectMyTopCardsEligibleForDogma();
            this.addTooltipsWithActionsTo(cards, this.createActionTextForDogma, dogma_effect_info, 'board');
            var self = this;
            cards.forEach(function(card) {
                var HTML_id = dojo.attr(card, "id");
                var id = self.getCardIdFromHTMLId(HTML_id);
                var no_effect = dogma_effect_info[id].no_effect;
                dojo.attr(HTML_id, 'no_effect', no_effect);
                dojo.attr(HTML_id, 'card_id', id);
                dojo.attr(HTML_id, 'non_demand_effect_players', dogma_effect_info[id].players_executing_non_demand_effects.join(','));
                dojo.attr(HTML_id, 'echo_effect_players', dogma_effect_info[id].players_executing_echo_effects.join(','));
            });
        },

        addTooltipWithMeldActionToMyArtifactOnDisplay : function() {
            var cards = this.selectArtifactOnDisplay();
            this.addTooltipsWithActionsTo(cards, this.createActionTextForMeld);
            var self = this;
            cards.forEach(function(card) {
                var HTML_id = dojo.attr(card, "id");
                var id = self.getCardIdFromHTMLId(HTML_id);
                dojo.attr(HTML_id, 'card_id', id);
            });
        },

        addTooltipWithDogmaActionToMyArtifactOnDisplay : function(dogma_effect_info) {
            this.addTooltipsWithActionsTo(this.selectArtifactOnDisplayIfEligibleForDogma(), this.createActionTextForDogma, dogma_effect_info, 'display');
        },
        
        addTooltipsWithSplayingActionsToColorsOnMyBoard : function(colors, colors_in_clear, splay_direction, splay_direction_in_clear) {
            var self = this;
            this.selectCardsOnMyBoardOfColors(colors).forEach(function(node) {
                var HTML_id = dojo.attr(node, "id");
                var id = self.getCardIdFromHTMLId(HTML_id);
                var HTML_help = self.saved_HTML_cards[id];
                var card = self.cards[id];
                
                // Search for the name of the color in clear
                for (var i=0; i<colors.length; i++) {
                    if (colors[i] = card.color) {
                        var color_in_clear = colors_in_clear[i];
                        break;
                    }
                }
                
                HTML_action = self.createActionTextForCardInSplayablePile(card, color_in_clear, splay_direction, splay_direction_in_clear);
                self.addCustomTooltip(HTML_id, HTML_help, HTML_action);
            });
        },
        
        createActionTextForMeld : function(self, card) {
            // Calculate new score (score pile + bonus icons)
            var bonus_icons = [];
            for (var i = 0; i < 5; i++) {
                var pile_zone = self.zone.board[self.player_id][i];
                var splay_direction = pile_zone.splay_direction;
                // If there are no cards in the stack yet, treat the pile as unsplayed
                if (pile_zone.items.length == 0) {
                    splay_direction = 0;
                }
                if (i == card.color) {
                    bonus_icons = bonus_icons.concat(self.getVisibleBonusIconsInPile(pile_zone.items, splay_direction, card));
                } else {
                    bonus_icons = bonus_icons.concat(self.getVisibleBonusIconsInPile(pile_zone.items, splay_direction));
                }
            }
            var new_score = self.computeTotalScore(self.zone.score[self.player_id].items, bonus_icons);

            HTML_action = "<p class='possible_action'>" + _("Click to meld this card.") + "<p>";
            // See if melding this card would cover another one
            var pile = self.zone.board[self.player_id][card.color].items;
            var covered_card = pile.length > 0;
            if (covered_card) {
                var top_card = pile[pile.length - 1];
                var top_card_id = self.getCardIdFromHTMLId(top_card.id);
                var top_card = self.cards[top_card_id];
                if (self.cities_expansion_enabled || self.echoes_expansion_enabled) {
                    HTML_action += dojo.string.substitute("<p>" + _("If you do, it will cover ${age} ${card_name}, you will have a total score of ${score}, and your new featured icon counts will be:") + "<p>",
                        {
                            'age': self.square('N', 'age', top_card.age, 'in_log'),
                            'card_name': "<span class='card_name'>" + _(top_card.name) + "</span>",
                            'score' : new_score
                        });
                } else {
                    HTML_action += dojo.string.substitute("<p>" + _("If you do, it will cover ${age} ${card_name} and your new ressource counts will be:") + "<p>",
                        {
                            'age': self.square('N', 'age', top_card.age, 'in_log'),
                            'card_name': "<span class='card_name'>" + _(top_card.name) + "</span>"
                        });
                }
            } else {
                if (self.cities_expansion_enabled || self.echoes_expansion_enabled) {
                    HTML_action += "<p>" + dojo.string.substitute(_("If you do, you will have a total score of ${score} and your new featured icon counts will be:"), {'score' : new_score}) + "</p>";
                } else {
                    HTML_action += "<p>" + _("If you do, your new ressource counts will be:") + "</p>";
                }
            }
            
            // Calculate new ressource count if this card is melded
            // Get current ressouce count
            var current_ressource_counts = {};
            var new_ressource_counts = {};
            for (var icon = 1; icon <= 6; icon++) {
                current_count = self.counter.ressource_count[self.player_id][icon].getValue();
                current_ressource_counts[icon] = current_count;
                new_ressource_counts[icon] = current_count;
            }
            
            // Add ressources brought by the new card
            new_ressource_counts[card.spot_1]++;
            new_ressource_counts[card.spot_2]++;
            new_ressource_counts[card.spot_3]++;
            new_ressource_counts[card.spot_4]++;
            new_ressource_counts[card.spot_5]++;
            new_ressource_counts[card.spot_6]++;
            
            if (covered_card) { // Substract the ressources no longer visible
                var splay_indicator = 'splay_indicator_' + self.player_id + '_' + top_card.color;
                for (var direction = 0; direction <= 3; direction++) {
                    if (dojo.hasClass(splay_indicator, 'splay_' + direction)) {
                        var splay_direction = direction;
                        break;
                    }
                }
                
                switch (parseInt(splay_direction)) {
                case 0: // No splay (all icons of the old top card are lost)
                    new_ressource_counts[top_card.spot_1]--;
                    new_ressource_counts[top_card.spot_2]--;
                    new_ressource_counts[top_card.spot_3]--;
                    new_ressource_counts[top_card.spot_4]--;
                    new_ressource_counts[top_card.spot_5]--;
                    new_ressource_counts[top_card.spot_6]--;
                    break;
                case 1: // Splayed left (only the icons on the right can still be seen)
                    new_ressource_counts[top_card.spot_1]--;
                    new_ressource_counts[top_card.spot_2]--;
                    new_ressource_counts[top_card.spot_3]--;
                    new_ressource_counts[top_card.spot_6]--;
                    break;
                case 2: // Splayed right (only the icons on the left can still be seen)
                    new_ressource_counts[top_card.spot_3]--;
                    new_ressource_counts[top_card.spot_4]--;
                    new_ressource_counts[top_card.spot_5]--;
                    new_ressource_counts[top_card.spot_6]--;
                    break;
                case 3: // Splayed up (only the icons on the bottom can still be seen)
                    new_ressource_counts[top_card.spot_1]--;
                    new_ressource_counts[top_card.spot_5]--;
                    new_ressource_counts[top_card.spot_6]--;
                    break;
                }
            }

            HTML_action += self.createSimulatedRessourceTable(current_ressource_counts, new_ressource_counts);
            
            return HTML_action;
        },
        
        createActionTextForDogma : function(self, card, dogma_effect_info, card_location) {
            var info = dogma_effect_info[card.id];

            var on_display = card_location == 'display';
            var exists_i_demand_effect = card.i_demand_effect_1 !== undefined && !card.i_demand_effect_1_is_compel;
            var exists_i_compel_effect = card.i_demand_effect_1_is_compel;
            var exists_non_demand_effect = card.non_demand_effect_1 !== undefined;
            
            if (info.no_effect) {
                return "<p class='warning'>" + _('Activating this card will have no effect.') + "</p>";
            }

            var HTML_action = "<p class='possible_action'>";
            if (on_display) {
                HTML_action += _("Click 'Dogma and Return' to execute the dogma effect(s) of this card.");
            } else {
                HTML_action += _("Click to execute the dogma effect(s) of this card.");
            }
            HTML_action += "</p>";
            HTML_action += "<p>" + _("If you do:") + "</p>";
            HTML_action += "<ul class='recap_dogma'>";

            if (info.num_echo_effects > 0) {
                if (info.players_executing_echo_effects.length == 0) {
                    HTML_action += "<li>" + _("You will execute the echo effect(s) alone.") + "</li>"
                } else {
                    HTML_action += "<li>" + dojo.string.substitute(_("${players} will share each echo effect before you execute it."), {'players': self.getOtherPlayersCommaSeparated(info.players_executing_echo_effects)}) + "</li>"
                }
            }
            
            if (exists_i_demand_effect) {
                if (info.players_executing_i_demand_effects.length == 0) {
                    HTML_action += "<li>" + _("Nobody will execute the I demand effect.") + "</li>"
                } else {
                    HTML_action += "<li>" + dojo.string.substitute(_("${players} will execute the I demand effect."), {'players': self.getOtherPlayersCommaSeparated(info.players_executing_i_demand_effects)}) + "</li>"
                }
            }

            if (exists_i_compel_effect) {
                if (info.players_executing_i_compel_effects.length == 0) {
                    HTML_action += "<li>" + _("Nobody will execute the I compel effect.") + "</li>"
                } else {
                    HTML_action += "<li>" + dojo.string.substitute(_("${players} will execute the I compel effect."), {'players': self.getOtherPlayersCommaSeparated(info.players_executing_i_compel_effects)}) + "</li>"
                }
            }
            
            if (exists_non_demand_effect) {
                if (info.players_executing_non_demand_effects.length == 1) {
                    HTML_action += "<li>" + _("You will execute the non-demand effect(s) alone.") + "</li>"
                } else if (info.players_executing_non_demand_effects.length > 1) {
                    HTML_action += "<li>" + dojo.string.substitute(_("${players} will share each non-demand effect before you execute it."), {'players': self.getOtherPlayersCommaSeparated(info.players_executing_non_demand_effects)}) + "</li>"
                }
            }

            if (on_display) {
                HTML_action += "<li>" + _("You will return this Artifact afterwards.") + "</li>"
            }

            HTML_action += "</ul>";

            return HTML_action;
        },

        getOtherPlayersCommaSeparated : function(player_ids) {
            var players = [];
            for (var i = 0; i < player_ids.length; i++) {
                if (player_ids[i] != this.player_id) {
                    var player = $('name_' + player_ids[i]).outerHTML.replace("<p", "<span class='name_in_tooltip'").replace("</p", "</span");
                    players.push(player);
                }
            }
            return players.join(', ');
        },
        
        createActionTextForCardInSplayablePile : function(card, color_in_clear, splay_direction, splay_direction_in_clear) {
            var pile = this.zone.board[this.player_id][card.color].items;
            
            var splay_indicator = 'splay_indicator_' + this.player_id + '_' + card.color;
            for (var direction=0; direction<=3; direction++) {
                if (dojo.hasClass(splay_indicator, 'splay_' + direction)) {
                    var current_splay_direction = direction;
                    break;
                }
            }
            
            // Calculate new resource count if the splay direction changes
            // Get current ressouce count
            var current_ressource_counts = {};
            var new_ressource_counts = {};
            for (var icon = 1; icon <= 6; icon++) {
                current_count = this.counter.ressource_count[this.player_id][icon].getValue();
                current_ressource_counts[icon] = current_count;
                new_ressource_counts[icon] = current_count;
            }
            
            // Browse all the cards of the pîle except the one on top
            for (var i = 0; i < pile.length - 1; i++) {
                var pile_card = this.cards[this.getCardIdFromHTMLId(pile[i].id)]
                
                // Remove resources which were granted by the current splay
                switch (parseInt(current_splay_direction)) {
                case 0: // Not currently splayed: no icons will be lost
                    break;
                case 1: // The icons on the right will be lost (spot_4 and spot_5)
                    new_ressource_counts[pile_card.spot_4]--;
                    new_ressource_counts[pile_card.spot_5]--;
                    break;
                case 2: // The icons on the left will be lost (spot_1 and spot_2)
                    new_ressource_counts[pile_card.spot_1]--;
                    new_ressource_counts[pile_card.spot_2]--;
                    break;
                case 3: // The icons on the bottom will be lost (spot_2, spot_3 and spot_4)
                    new_ressource_counts[pile_card.spot_2]--;
                    new_ressource_counts[pile_card.spot_3]--;
                    new_ressource_counts[pile_card.spot_4]--;
                    break;
                }
                
                // Add resources granted by the new splay
                switch (parseInt(splay_direction)) {
                case 0: // Not splayed (this should not happen)
                    break;
                case 1: // The icons on the right will be revealed (spot_4 and spot_5)
                    new_ressource_counts[pile_card.spot_4]++;
                    new_ressource_counts[pile_card.spot_5]++;
                    break;
                case 2: // The icons on the left will be revealed (spot_1 and spot_2)
                    new_ressource_counts[pile_card.spot_1]++;
                    new_ressource_counts[pile_card.spot_2]++;
                    break;
                case 3: // The icons on the bottom will be revealed (spot_2, spot_3 and spot_4)
                    new_ressource_counts[pile_card.spot_2]++;
                    new_ressource_counts[pile_card.spot_3]++;
                    new_ressource_counts[pile_card.spot_4]++;
                    break;
                }
            }

            // Calculate new score (score pile + bonus icons)
            var bonus_icons = [];
            for (var i = 0; i < 5; i++) {
                var pile_zone = this.zone.board[this.player_id][i];
                if (i == card.color) {
                    bonus_icons = bonus_icons.concat(this.getVisibleBonusIconsInPile(pile_zone.items, splay_direction));
                } else {
                    bonus_icons = bonus_icons.concat(this.getVisibleBonusIconsInPile(pile_zone.items, pile_zone.splay_direction));
                }
            }
            var new_score = this.computeTotalScore(this.zone.score[this.player_id].items, bonus_icons);

            HTML_action = "<p class='possible_action'>" + dojo.string.substitute(_("Click to splay your ${color} stack ${direction}."), {'color': color_in_clear, 'direction': splay_direction_in_clear}) + "<p>";
            if (this.cities_expansion_enabled || this.echoes_expansion_enabled) {
                HTML_action += "<p>" + dojo.string.substitute(_("If you do, you will have a total score of ${score} and your new featured icon counts will be:"), {'score' : new_score}) + "</p>";
            } else {
                HTML_action += "<p>" + _("If you do, your new ressource counts will be:") + "</p>";
            }
            HTML_action += this.createSimulatedRessourceTable(current_ressource_counts, new_ressource_counts);
        
            return HTML_action;
        },

        /** Returns all visible bonus icons in a particular pile, optionally pretending a card is placed on top of the pile */
        getVisibleBonusIconsInPile : function(pile, splay_direction, card_being_melded = null) {
            var bonus_icons = [];

            // Top card
            var top_card = null;
            if (pile.length > 0) {
                top_card = this.cards[this.getCardIdFromHTMLId(pile[pile.length-1].id)];
            }
            if (card_being_melded != null) {
                top_card = card_being_melded;
            }
            if (top_card != null) {
                bonus_icons.push(this.getBonusIconValue(top_card.spot_1));
                bonus_icons.push(this.getBonusIconValue(top_card.spot_2));
                bonus_icons.push(this.getBonusIconValue(top_card.spot_3));
                bonus_icons.push(this.getBonusIconValue(top_card.spot_4));
                bonus_icons.push(this.getBonusIconValue(top_card.spot_5));
                bonus_icons.push(this.getBonusIconValue(top_card.spot_6));
            }

            // Cards underneath
            var pile_length = card_being_melded == null ? pile.length : pile.length + 1;
            for (var i = 0; i < pile_length - 1; i++) {
                var pile_card = this.cards[this.getCardIdFromHTMLId(pile[i].id)];
                switch (parseInt(splay_direction)) {
                case 0: // Not splayed
                    break;
                case 1: // Splayed left (the icons on the right would be visible)
                    bonus_icons.push(this.getBonusIconValue(pile_card.spot_4));
                    bonus_icons.push(this.getBonusIconValue(pile_card.spot_5));
                    break;
                case 2: // Splayed right (the icons on the left would be visible)
                    bonus_icons.push(this.getBonusIconValue(pile_card.spot_1));
                    bonus_icons.push(this.getBonusIconValue(pile_card.spot_2));
                    break;
                case 3: // Splayed up (the icons on the bottom would be visible)
                    bonus_icons.push(this.getBonusIconValue(pile_card.spot_2));
                    bonus_icons.push(this.getBonusIconValue(pile_card.spot_3));
                    bonus_icons.push(this.getBonusIconValue(pile_card.spot_4));
                    break;
                }
            }

            return bonus_icons.filter(val => val > 0); // Remove the zeroes
        },

        /** Computes what the player's total score would be given a score pile and list of bonus icons.  */
        computeTotalScore : function(score_pile, bonus_icons) {
            var score = 0;
            for (var i = 0; i < score_pile.length; i++) {
                score += this.getCardAgeFromHTMLId(score_pile[i].id);
            }
            if (bonus_icons.length > 0) {
                var max_bonus = 0;
                for (var i = 0; i < bonus_icons.length; i++) {
                    if (bonus_icons[i] > max_bonus) {
                        max_bonus = bonus_icons[i];
                    }
                }
                score += max_bonus + bonus_icons.length - 1;
            }
            return score;
        },

        getBonusIconValue : function(icon) {
            // TODO(EXPANSION): If there is ever a bonus icon with a value higher than 11, then this needs to be changed.
            if (icon > 100 && icon <= 111) {
                return icon - 100;
            }
            return 0;
        },

        /** Counts how many of a particular icon is visible in a specific pile */
        countVisibleIconsInPile : function(pile, splay_direction, icon) {
            var count = 0;

            // Top card
            var top_card = null;
            if (pile.length > 0) {
                top_card = this.cards[this.getCardIdFromHTMLId(pile[pile.length-1].id)];
            }
            if (top_card != null) {
                if (top_card.spot_1 == icon) {
                    count++;
                }
                if (top_card.spot_2 == icon) {
                    count++;
                }
                if (top_card.spot_3 == icon) {
                    count++;
                }
                if (top_card.spot_4 == icon) {
                    count++;
                }
                if (top_card.spot_5 == icon) {
                    count++;
                }
                if (top_card.spot_6 == icon) {
                    count++;
                }
            }

            // Cards underneath
            for (var i = 0; i < pile.length - 1; i++) {
                var pile_card = this.cards[this.getCardIdFromHTMLId(pile[i].id)];
                switch (parseInt(splay_direction)) {
                case 0: // Not splayed
                    break;
                case 1: // Splayed left (the icons on the right would be visible)
                    if (pile_card.spot_4 == icon) {
                        count++;
                    }
                    if (pile_card.spot_5 == icon) {
                        count++;
                    }
                    break;
                case 2: // Splayed right (the icons on the left would be visible)
                    if (pile_card.spot_1 == icon) {
                        count++;
                    }
                    if (pile_card.spot_2 == icon) {
                        count++;
                    }
                    break;
                case 3: // Splayed up (the icons on the bottom would be visible)
                    if (pile_card.spot_2 == icon) {
                        count++;
                    }
                    if (pile_card.spot_3 == icon) {
                        count++;
                    }
                    if (pile_card.spot_4 == icon) {
                        count++;
                    }
                    break;
                }
            }

            return count;
        },
        
        createSimulatedRessourceTable : function(current_ressource_counts, new_ressource_counts) {
            var table = dojo.create('table', { 'class': 'ressource_table' });
            var symbol_line = dojo.create('tr', null, table);
            var count_line = dojo.create('tr', null, table);
            
            for(var icon=1; icon<=6; icon++) {
                var current_count = current_ressource_counts[icon];
                var new_count = new_ressource_counts[icon];
                var comparator = new_count == current_count ? 'equal' : (new_count > current_count ? 'more' : 'less');
                dojo.place('<td><div class="ressource with_white_border ressource_' + icon + ' square P icon_' + icon + '"></div></td>', symbol_line);
                dojo.place('<td><div class="ressource with_white_border' + icon + ' ' + comparator + '">&nbsp;&#8239;' + new_count + '</div></td>', count_line);
            }
            return table.outerHTML;
        },
        
        /*
         * Selectors for connect event, usable to use with this.on, this.off functions and .addClass and .removeClass methods
         */
        selectAllCards : function() {
            return dojo.query(".card, .recto");
        },
        
        selectMyCardsInHand : function() {
            return dojo.query("#hand_" + this.player_id + " > .card");
        },

        selectMyCardsInForecast : function(max_age_to_promote) {
            if (max_age_to_promote == null) {
                return dojo.query("#my_forecast_verso > .M");
            }
            var queries = [];
            for (var age = 1; age <= max_age_to_promote; age++) {
                queries.push("#my_forecast_verso > .age_" + age);
            }
            return dojo.query(queries.join(","));
        },

        selectMyCardBacksInForecast : function(max_age_to_promote) {
            if (max_age_to_promote == null) {
                return dojo.query(`#forecast_${this.player_id} > .S`);
            }
            var queries = [];
            for (var age = 1; age <= max_age_to_promote; age++) {
                queries.push(`#forecast_${this.player_id} > .age_${age}`);
            }
            return dojo.query(queries.join(","));
        },

        selectArtifactOnDisplay : function() {
            return dojo.query("#display_" + this.player_id + " > .card");
        },

        selectArtifactOnDisplayIfEligibleForDogma : function() {
            var cards = dojo.query("#display_" + this.player_id + " > .card");
            // Battleship Yamato does not have any dogma effects on it
            if (cards.length > 0 && this.getCardIdFromHTMLId(cards[0].id) == 188) {
                cards.pop();
            }
            return cards;
        },
        
        selectAllCardsOnMyBoard : function() {
            return dojo.query("#board_" + this.player_id + " .card");
        },
        
        selectCardsOnMyBoardOfColors : function(colors) {
            var queries = []
            for(var i=0; i<colors.length; i++) {
                var color = colors[i];
                queries.push("#board_" + this.player_id + "_" + color +  " .card")
            }
            return dojo.query(queries.join(","));
        },
        
        selectMyTopCardsEligibleForDogma : function() {
            var player_board = this.zone.board[this.player_id];
            var selectable_list = [];
            for (var color = 0; color < 5; color++) {
                var pile = player_board[color].items;
                if (pile.length == 0) {
                    continue;
                }
                var top_card = pile[pile.length - 1];
                // Battleship Yamato does not have a dogma effect on it.
                if (this.getCardIdFromHTMLId(top_card.id) != 188) {
                    selectable_list.push("#" + top_card.id);
                }
            }
            return selectable_list.length > 0 ? dojo.query(selectable_list.join(",")) : new dojo.NodeList();
        },
        
        selectClaimableAchievements : function(claimable_ages) {
            identifiers = [];
            for (var i=0; i<claimable_ages.length; i++) {
                var age = claimable_ages[i];
                identifiers.push("#achievements > .age_" + age);
            }
            return dojo.query(identifiers.join(","));
        },
        
        selectDrawableCard : function(age_to_draw, type_to_draw) {
            var deck_to_draw_in = this.zone.deck[type_to_draw][age_to_draw].items;
            var top_card = deck_to_draw_in[deck_to_draw_in.length - 1];
            return dojo.query("#" + top_card.id);
        },
        
        selectCardsFromList : function(cards) {
            if (cards.length == 0) {
                return null;
            }
            var identifiers = [];
            for (var i=0; i<cards.length; i++) {
                var card = cards[i];
                identifiers.push("#" + this.getCardHTMLId(card.id, card.age, card.type, card.is_relic, "M card"));
            }
            return dojo.query(identifiers.join(","));
        },
        
        selectRectosFromList : function(recto_positional_infos_array) {
            if (recto_positional_infos_array.length == 0) {
                return null;
            }
            var identifiers = [];
            for (var i=0; i<recto_positional_infos_array.length; i++) {
                var card = recto_positional_infos_array[i];
                var zone = this.getZone(card['location'], card.owner, card.type, card.age);
                var id = this.getCardIdFromPosition(zone, card.position, card.age, card.type, card.is_relic)
                identifiers.push("#" + this.getCardHTMLId(id, card.age, card.type, card.is_relic, zone.HTML_class));
            }
            return dojo.query(identifiers.join(","));
        },
        
        /*
         * Deactivate all click events
         */
        deactivateClickEvents : function() {
            this.deactivated_cards = dojo.query(".clickable");
            this.deactivated_cards.removeClass("clickable");

            this.deactivated_cards_mid_dogma = dojo.query(".mid_dogma");
            this.deactivated_cards_mid_dogma.removeClass("mid_dogma");
            
            this.off(this.deactivated_cards, 'onclick');

            this.erased_pagemaintitle_text = $('pagemaintitletext').innerHTML;
            
            dojo.query('#generalactions > .action-button, .extra_text').addClass('hidden'); // Hide buttons
            $('pagemaintitletext').innerHTML = _("Move recorded. Waiting for update...");

        },
        
        resurrectClickEvents : function(revert_text) {
            this.deactivated_cards.addClass("clickable");
            this.deactivated_cards_mid_dogma.addClass("mid_dogma");
            
            this.restart(this.deactivated_cards, 'onclick');
            
            dojo.query('#generalactions > .action-button, .extra_text').removeClass('hidden'); // Show buttons again
            if (revert_text) {
                $('pagemaintitletext').innerHTML = this.erased_pagemaintitle_text;
            }
        },

        getCardSizeInZone : function(zone_HTML_class) {
            return zone_HTML_class.split(' ')[0];
        },
        
        getCardTypeInZone : function(zone_HTML_class) {
            return zone_HTML_class.split(' ')[1];
        },
        
        getZone : function(location, owner, type, age = null, color = null) {
            var root = this.zone[location];
            switch(location) {
                case "deck":
                    return root[type][age];
                case "relics":
                    return this.zone.relics[0];
                case "hand":
                case "display":
                case "forecast":
                case "score":
                case "revealed":
                case "achievements":
                    if (owner == 0) {
                        return age === null ? this.zone.special_achievements[0] : this.zone.achievements[0];
                    } else {
                        return root[owner];
                    }
                case "board":
                    return root[owner][color];
            }
        },
        
        getCardIdFromPosition : function(zone, position, age, type, is_relic) {
            // For relics we use the real IDs (since there is only one of each age)
            if (parseInt(is_relic) == 1) {
                return 212 + parseInt(age);
            }

            if (!zone.grouped_by_age_type_and_is_relic) {
                return this.getCardIdFromHTMLId(zone.items[position].id);
            }

            // A relative position makes it easy to decide if this new card should go before or after another card.
            // The cards are sorted by age, breaking ties by their type, and then breaking ties with non-relics first.
            var relative_position = ((parseInt(age) * 5) + parseInt(type)) * 2 + parseInt(is_relic);

            var p = 0;
            for (var i=0; i<zone.items.length; i++) {
                var item = zone.items[i];
                var item_age = this.getCardAgeFromHTMLId(item.id);
                var item_type = this.getCardTypeFromHTMLId(item.id);
                var item_is_relic = this.getCardIsRelicFromHTMLId(item.id);
                var item_relative_position = ((item_age * 5) + item_type) * 2 + item_is_relic;
                
                if (item_relative_position < relative_position) {
                    continue;
                }
                if (p == position) {
                    return this.getCardIdFromHTMLId(item.id);
                }
                p++;
            }
        },
        
        getCardPositionFromId : function(zone, id, age, type, is_relic) {
            if (!zone.grouped_by_age_type_and_is_relic) {
                for (var p = 0; p < zone.items.length; p++) {
                    var item = zone.items[p];
                    if (this.getCardIdFromHTMLId(item.id) == id) {
                        return p;
                    }
                }
            }
            var p = 0;
            for (var i = 0; i < zone.items.length; i++) {
                var item = zone.items[i];
                if (this.getCardAgeFromHTMLId(item.id) != age) {
                    continue;
                }
                if (this.getCardTypeFromHTMLId(item.id) != type) {
                    continue;
                }
                if (this.getCardIsRelicFromHTMLId(item.id) != parseInt(is_relic)) {
                    continue;
                }
                if (this.getCardIdFromHTMLId(item.id) == id) {
                    return p;
                }
                p++;
            }
        },
        
        getCardHTMLIdFromEvent : function(event) {
            return dojo.getAttr(event.currentTarget, 'id');
        },
        
        getCardHTMLId : function(id, age, type, is_relic, zone_HTML_class) {
            return ["item_" + id, "age_" + age, "type_" + type, "is_relic_" + parseInt(is_relic), zone_HTML_class.replace(" ", "__")].join("__");
        },
        
        getCardHTMLClass : function(id, age, type, is_relic, card, zone_HTML_class) {
            var simplified_card_layout = this.prefs[111].value == 1;
            classes = ["item_" + id, "age_" + age, "type_" + type, zone_HTML_class];
            if (parseInt(is_relic)) {
                classes.push("relic");
            }
            if (card !== null) {
                classes.push("color_" + card.color);
            }
            if (simplified_card_layout) {
                classes.push("simplified");
            }
            return classes.join(" ");
        },
        
        getCardIdFromHTMLId : function(HTML_id) {
            return parseInt(HTML_id.split("__")[0].substr(5));
        },
        
        getCardAgeFromHTMLId : function(HTML_id) {
            return parseInt(HTML_id.split("__")[1].substr(4));
        },

        getCardTypeFromHTMLId : function(HTML_id) {
            return parseInt(HTML_id.split("__")[2].substr(5));
        },

        getCardIsRelicFromHTMLId : function(HTML_id) {
            return parseInt(HTML_id.split("__")[3].substr(9));
        },
        
        /*
         * Card creation
         */
        createCard : function(id, age, type, is_relic, zone_HTML_class, card) {
            var HTML_id = this.getCardHTMLId(id, age, type, is_relic, zone_HTML_class);
            var HTML_class = this.getCardHTMLClass(id, age, type, is_relic, card, zone_HTML_class);
            var size = this.getCardSizeInZone(zone_HTML_class);
            
            var simplified_card_back = this.prefs[110].value == 2;

            if (card === null) {
                if (age === null || !simplified_card_back) {
                    var HTML_inside = '';
                } else {
                    var HTML_inside = "<span class='card_back_text " + HTML_class + "'>" + age +"</span>";
                }
            } else {
                var HTML_inside = this.writeOverCard(card, size);
            }

            var card_type = "";
            if (size == 'L') {
                // TODO(FIGURES): Update this.
                switch (parseInt(type)) {
                    case 0:
                        card_type = "<div class='card_type'>" + _("This card is from the base game.") + "</div>";
                        break;
                    case 1:
                        card_type = "<div class='card_type'>" + _("This card is from the Artifacts of History expansion.") + "</div>";
                        break;
                    case 2:
                        card_type = "<div class='card_type'>" + _("This card is from the Cities of Destiny expansion.") + "</div>";
                        break;
                    case 3:
                        card_type = "<div class='card_type'>" + _("This card is from the Echoes of the Past expansion.") + "</div>";
                        break;
                }
            }

            var graphics_class = age === null ? "" : simplified_card_back ? "simplified_card_back" : "default_card_back";
            return "<div id='" + HTML_id + "' class='" + graphics_class + " " + HTML_class + "'>" + HTML_inside + "</div>" + card_type;
        },

        createCardForCardBrowser : function(id) {
            var card = this.cards[id];
            var HTML_class = this.getCardHTMLClass(id, card.age, card.type, card.is_relic, card, 'M card');
            var HTML_inside = this.writeOverCard(card, 'M');
            var simplified_card_back = this.prefs[110].value == 2;
            var graphics_class = simplified_card_back ? "simplified_card_back" : "default_card_back";
            return `<div id = 'browse_card_id_${id}' class='${graphics_class} ${HTML_class}'>${HTML_inside}</div>`;
        },
        
        writeOverCard : function(card, size) {
            var card_data = this.cards[card.id];
            var icon1 = this.getIconDiv(card, card_data['spot_1'], 'top_left_icon', size);
            var icon2 = this.getIconDiv(card, card_data['spot_2'], 'bottom_left_icon', size);
            var icon3 = this.getIconDiv(card, card_data['spot_3'], 'bottom_center_icon', size);
            var icon4 = this.getIconDiv(card, card_data['spot_4'], 'bottom_right_icon', size);
            var icon5 = this.getIconDiv(card, card_data['spot_5'], 'top_right_icon', size);
            var icon6 = this.getIconDiv(card, card_data['spot_6'], 'top_center_icon', size);

            var card_age = this.createAdjustedContent(card.faceup_age, 'card_age type_' + card_data.type + ' color_' + card_data.color, size, size == 'M' ? (card.age >= 10 ? 7 : 9) : 30);

            var title = _(card_data.name).toUpperCase();
            var card_title = this.createAdjustedContent(title, 'card_title type_' + card_data.type, size, size == 'M' ? 11 : 30);
            
            var i_demand_effect_1 = card_data.i_demand_effect_1 ? this.createDogmaEffectText(_(card_data.i_demand_effect_1), card.dogma_icon, size, card.color, 'dark', (card.i_demand_effect_1_is_compel ? 'is_compel_effect ' : '' ) + 'i_demand_effect_1 color_' + card.color)  : "";

            var non_demand_effect_1 = card_data.non_demand_effect_1 ? this.createDogmaEffectText(_(card_data.non_demand_effect_1) , card.dogma_icon, size, card.color, 'light', 'non_demand_effect_1 color_' + card.color)  : "";
            var non_demand_effect_2 = card_data.non_demand_effect_2 ? this.createDogmaEffectText(_(card_data.non_demand_effect_2) , card.dogma_icon, size, card.color, 'light', 'non_demand_effect_2 color_' + card.color)  : "";
            var non_demand_effect_3 = card_data.non_demand_effect_3 ? this.createDogmaEffectText(_(card_data.non_demand_effect_3) , card.dogma_icon, size, card.color, 'light', 'non_demand_effect_3 color_' + card.color)  : "";
            
            var dogma_effects = this.createAdjustedContent(i_demand_effect_1 + non_demand_effect_1 + non_demand_effect_2 + non_demand_effect_3, "card_effects", size, size == 'M' ? 8 : 17);
            
            return icon1 + icon2 + icon3 + icon4 + icon5 + icon6 + card_age + card_title + dogma_effects;
        },
        
        getIconDiv: function (card, resource_icon_id, icon_location, size) {
            if (resource_icon_id == 0) {
                return '<div class="hexagon_card_icon ' + size + ' ' + icon_location + ' hexagon_icon_' + card.id + '"></div>';
            }
            if (resource_icon_id <= 6) {
                return '<div class="square_card_icon ' + size + ' color_' + card.color + ' ' + icon_location + ' icon_' + resource_icon_id + '"></div>';
            }
            if (resource_icon_id == 10) {
                var card_data = this.cards[card.id];
                var div = this.createAdjustedContent(this.parseForRichedText(_(card_data.echo_effect_1), size), 'echo_effect light color_' + card.color + ' square_card_icon ' + size + ' ' + icon_location + ' icon_' + resource_icon_id, size, size == 'M' ? 11 : 30);
                // Add "display: table;" styling after the size is computed, otherwise it messes up the calculation.
                return div.replace("div class", "div style=\"display: table;\" class");
            }
            if (resource_icon_id >= 101) {
                return '<div class="bonus_card_icon ' + size + ' ' + icon_location + ' bonus_color color_' + card.color + '"></div><div class="bonus_card_icon ' + size + ' ' + icon_location + ' bonus_value bonus_' + (resource_icon_id - 100) + '"></div>';
            }
            return '<div class="city_special_icon ' + size + ' color_' + card.color + ' ' + icon_location + ' icon_' + resource_icon_id + '"></div>';
        },
        
        getSpecialAchievementText : function(card) {
            var card_data = this.cards[card.id];
            var name = _(card_data.name).toUpperCase();
            var is_monument = card.id == 106;
            var note_for_monument = _("Note: Transfered cards from other players do not count toward this achievement, nor does exchanging cards from your hand and score pile.");
            var div_condition_for_claiming = "<div><b>" + name + "</b>: " + this.parseForRichedText(_(card_data.condition_for_claiming), 'in_tooltip') + "</div>" + (is_monument ? "<div></br>" + note_for_monument + "</div>" : "");
            
            var div_alternative_condition_for_claiming = "</br><div>" + this.parseForRichedText(_(card_data.alternative_condition_for_claiming), 'in_tooltip') + "</div>";
            
            return div_condition_for_claiming + div_alternative_condition_for_claiming;            
        },
        
        /*
         * Zone management systemcard
         */
        createZone : function(location, owner, type = null, age = null, color = null, grouped_by_age_type_and_is_relic = null, counter_method = null, counter_display_zero = null) {
            owner_string = owner != 0 ? '_' + owner : '';
            type_string = type !== null ? '_' + type : '';
            age_string = age !== null ? '_' + age : '';
            color_string = color !== null ? '_' + color : '';
        
            // Dimension of a card in the zone
            var HTML_class;
            
            var new_location;
            if (location == "hand") {
                if (owner == this.player_id) {
                    new_location = 'my_hand';
                }
                else {
                    new_location = 'opponent_hand';
                }
            } else {
                new_location = location;
            }

            var HTML_class = this.HTML_class[new_location];
            var card_dimensions = this.card_dimensions[HTML_class];
            
            // Width of the zone
            var zone_width;
            if(new_location == 'board') {
                zone_width = card_dimensions.width; // Will change dynamically if splayed left or right
            } else if (new_location == 'forecast') {
                zone_width = (dojo.position('forecast_container_' + owner).w + dojo.position('score_container_' + owner).w) / 2;
            } else if (new_location == 'score') {
                zone_width = (dojo.position('forecast_container_' + owner).w + dojo.position('score_container_' + owner).w) / 2;
            } else if (new_location != 'relics' && new_location != 'achievements' && new_location != 'special_achievements') {
                var delta_x = this.delta[new_location].x
                var n = this.num_cards_in_row[new_location];
                zone_width = card_dimensions.width + (n - 1) * delta_x;
            }
            
            // Id of the container
            if (location == "my_score_verso" || location == "my_forecast_verso") {
                var div_id = location;
            } else {
                var div_id = location + owner_string + type_string + age_string + color_string;
            }

            // Creation of the zone
            dojo.style(div_id, 'width', zone_width + 'px');
            dojo.style(div_id, 'height', card_dimensions.height + 'px');
            var zone =  new ebg.zone();
            zone.create(this, div_id, card_dimensions.width, card_dimensions.height);
            zone.setPattern('grid');
            
            // Add information which identify the zone
            zone['location'] = new_location;
            zone.owner = owner;
            zone.HTML_class = HTML_class;
            zone.grouped_by_age_type_and_is_relic = grouped_by_age_type_and_is_relic;
            
            if (counter_method != null) {
                var counter_node = $(location + '_count' + owner_string + type_string + age_string + color_string);
                zone.counter = new ebg.counter()
                zone.counter.create(counter_node);
                zone.counter.setValue(0);
                if(!counter_display_zero) {
                    dojo.style(zone.counter.span, 'visibility', 'hidden');
                }
                zone.counter.method = counter_method;
                zone.counter.display_zero = counter_display_zero;
            }
            else {
                zone.counter = null;
            }
            
            return zone;
        },
        

        createAndAddToZone : function(zone, position, age, type, is_relic, id, start, card) {
            // id of the new item
            var visible_card
            if (id === null) {
                // Recto
                visible_card = false;
                
                // For relics we use the real IDs (since there is only one of each age)
                if (parseInt(is_relic) == 1) {
                    id = 212 + parseInt(age);
                // Create a new id based only on the visible properties of the card
                } else {
                    id = this.uniqueIdForCard(age, type, is_relic); 
                }
            } else {
                // verso
                if (zone.owner != 0 && zone['location'] == 'achievements') {
                    visible_card = false;
                } else {
                    visible_card = true;
                }
            }
            // Create a new card and place it on start position
            var node = this.createCard(id, age, type, is_relic, zone.HTML_class, visible_card ? card : null);
            dojo.place(node, start);
            
            this.addToZone(zone, id, position, age, type, is_relic);
        },
        
        moveBetweenZones: function(zone_from, zone_to, id_from, id_to, card) {
            // Handle case where card is being melded from the bottom of the pile (e.g. Seikilos Epitaph)
            if (card.location_from == "board" && card.location_to == "board" && card.owner_from == card.owner_to) {
                this.removeFromZone(zone_from, id_from, false, card.age, card.type, card.is_relic);
                this.addToZone(zone_to, id_to, card.position_to, card.age, card.type, card.is_relic);
            } else if (id_from == id_to && card.age !== null && zone_from.HTML_class == zone_to.HTML_class) {
                this.addToZone(zone_to, id_to, card.position_to, card.age, card.type, card.is_relic);
                this.removeFromZone(zone_from, id_from, false, card.age, card.type, card.is_relic);
            } else {
                this.createAndAddToZone(zone_to, card.position_to, card.age, card.type, card.is_relic, id_to, this.getCardHTMLId(id_from, card.age, card.type, card.is_relic, zone_from.HTML_class), card);
                this.removeFromZone(zone_from, id_from, true, card.age, card.type, card.is_relic);
            }
            this.updateDeckOpacities();
        },
        
        addToZone: function (zone, id, position, age, type, is_relic) {
            var HTML_id = this.getCardHTMLId(id, age, type, is_relic, zone.HTML_class);
            dojo.style(HTML_id, 'position', 'absolute');
            
            if (zone['location'] == 'revealed' && zone.items.length == 0) {
                dojo.style(zone.container_div, 'display', 'block');
            }

            // A relative position makes it easy to decide if this new card should go before or after another card.
            // We want the cards sorted by age, breaking ties by their type, and then breaking ties by placing non-relics first.
            var relative_position = ((parseInt(age) * 5) + parseInt(type)) * 2 + parseInt(is_relic);
            
            // Update weights before adding and find the right spot to put the card according to its position, and age for not board stock
            var found = false;
            var p = 0;
            for (var i=0; i<zone.items.length; i++) {
                var item = zone.items[i];
                var item_age = this.getCardAgeFromHTMLId(item.id);
                var item_type = this.getCardTypeFromHTMLId(item.id);
                var item_is_relic = this.getCardIsRelicFromHTMLId(item.id);
                var item_relative_position = ((item_age * 5) + item_type) * 2 + item_is_relic;

                // We have not reached the group the card can be put into
                if (zone.grouped_by_age_type_and_is_relic && item_relative_position < relative_position) {
                    continue;
                }
                
                // We found the spot where the card belongs
                if (!found && zone.grouped_by_age_type_and_is_relic && item_relative_position > relative_position || p == position) {
                    var weight = i;
                    found = true;
                }
                
                if (found) { // Increment positions of the cards after
                    item.weight++;
                    dojo.style(item.id, 'z-index', item.weight)
                }
                p++;
            }
            if (!found) { // No spot for the card has been found after running all the stock
                // The card must be placed on last position
                var weight = zone.items.length;
            }
            
            // Add the card
            dojo.style(HTML_id, 'z-index', weight);
            zone.placeInZone(HTML_id, weight);
            
            if(zone['location'] == 'board' && (zone.splay_direction == 1 /* left */ || zone.splay_direction == 2 /* right */)) { 
                this.updateZoneWidth(zone);
            }
            zone.updateDisplay();
            
            // Update count if applicable
            if (zone.counter !== null) {
                // Update the value in the associated counter
                var delta;
                switch(zone.counter.method) {
                case("COUNT"):
                    delta = 1;
                    break;
                case("SUM"):
                    delta = parseInt(age);
                    break;
                }
                zone.counter.incValue(delta);
                if(!zone.counter.display_zero) {
                    dojo.style(zone.counter.span, 'visibility', zone.counter.getValue() == 0 ? 'hidden' : 'visible');
                }
            }

            this.updateDeckOpacities();
        },
        
        removeFromZone: function (zone, id, destroy, age, type, is_relic) {
            var HTML_id = this.getCardHTMLId(id, age, type, is_relic, zone.HTML_class);
            
            // Update weights before removing
            var found = false;
            for (var i=0; i<zone.items.length; i++) {
                var item = zone.items[i];
                if (found) {
                    item.weight--;
                    dojo.style(item.id, 'z-index', item.weight);
                    continue;
                }
                if (item.id == HTML_id) {
                    found = true;
                }
            }
            
            // Remove the card
            zone.removeFromZone(HTML_id, destroy);
            
            // Remove the space occupied by the card if needed
            if(zone['location'] == 'board' && (zone.splay_direction == 1 /* left */ || zone.splay_direction == 2 /* right */)) { 
                this.updateZoneWidth(zone);
            } else if (zone['location'] == 'revealed' && zone.items.length == 0) {
                zone = this.createZone('revealed', zone.owner, null, null, null); // Recreate the zone (Dunno why it does not work if I don't do that)
                dojo.style(zone.container_div, 'display', 'none');
            }
            zone.updateDisplay();
            
            // Update count if applicable
            if (zone.counter !== null) {
                // Update the value in the associated counter
                var delta;
                switch(zone.counter.method) {
                case("COUNT"):
                    delta = -1;
                    break;
                case("SUM"):
                    delta = parseInt(-age);
                    break;
                }
                zone.counter.incValue(delta);
                if(!zone.counter.display_zero) {
                    dojo.style(zone.counter.span, 'visibility', zone.counter.getValue() == 0 ? 'hidden' : 'visible');
                }
            }

            this.updateDeckOpacities();
        },
        
        shrinkZoneForNoneOrUpSplay : function(zone) {
            width = this.card_dimensions[zone.HTML_class].width
            width += 'px';
            dojo.setStyle(zone.container_div, 'width', width);
        },
        
        updateZoneWidth : function(zone) {
            width = this.card_dimensions[zone.HTML_class].width + (zone.items.length - 1) * this.overlap_for_splay[zone.HTML_class][this.display_mode ? "expanded" : "compact"];
            width += 'px';
            dojo.setStyle(zone.container_div, 'width', width);
        },
        
        setPlacementRules : function(zone, left_to_right) {
            var self = this;
            
            zone.itemIdToCoordsGrid = function(i, control_width) {                
                var w = self.card_dimensions[this.HTML_class].width;
                var h = self.card_dimensions[this.HTML_class].height;
                
                var delta = self.delta[this['location']];
                var n = self.num_cards_in_row[this['location']];
                
                if (left_to_right) {
                    var x_beginning = 0;
                    var delta_x = delta.x;
                }
                else {
                    var x_beginning = control_width - w;
                    var delta_x = -delta.x;
                }
                
                var delta_y = delta.y;
                var n_x = i % n;
                var n_y = parseInt(i/n);
                
                return {'x':x_beginning + delta_x * n_x, 'y': delta_y * n_y, 'w':w, 'h':h}
            }
        },

        setPlacementRulesForRelics : function() {
            var self = this;
            this.zone.relics["0"].itemIdToCoordsGrid = function(i, control_width) {                
                var w = self.card_dimensions[this.HTML_class].width;
                var h = self.card_dimensions[this.HTML_class].height;
                
                var x = (i % 3) * (w + 5);
                if (i >= 3) {
                    x = x + ((w + 5) / 2);
                }
                var y = parseInt(i / 3) * (h + 5);
                
                return {'x':x, 'y':y, 'w':w, 'h':h};
            }
        },

        // Reduce opacity of expansion decks if the accompanying base deck is empty.
        updateDeckOpacities : function() {
            // NOTE: We delay this by 2 seconds in order to give enough time for the cards move around. If
            // we discover that this is buggy or if we want to build a less hacky solution, we should pass
            // data from the server side instead of calculating the deck sizes using childElementCount.
            setTimeout(function () {
                for (var a = 1; a <= 10; a++) {
                    var opacity = document.getElementById(`deck_0_${a}`).childElementCount > 0 ? 1.0 : 0.35;
                    for (var t = 1; t <= 4; t++) {
                        var deck = document.getElementById(`deck_${t}_${a}`);
                        if (deck != null) {
                            deck.parentElement.style.opacity = opacity;
                        }
                    }
                }
            }, 2000);
        },
        
        setPlacementRulesForAchievements : function() {
            var self = this;
            this.zone.achievements["0"].itemIdToCoordsGrid = function(i, control_width) {                
                var w = self.card_dimensions[this.HTML_class].width;
                var h = self.card_dimensions[this.HTML_class].height;
                
                var x = (i % 3) * (w + 5);
                var y = parseInt(i / 3) * (h + 5);
                
                return {'x':x, 'y':y, 'w':w, 'h':h}
            }
        },
        
        setPlacementRulesForSpecialAchievements : function() {
            var self = this;
            this.zone.special_achievements["0"].itemIdToCoordsGrid = function(i, control_width) {                
                var w = self.card_dimensions[this.HTML_class].width;
                var h = self.card_dimensions[this.HTML_class].height;
                
                // Row of 3
                if ((i % 5) < 3) {
                    var x = (i % 5) * (w + 5);
                    var y = parseInt(i / 5) * 2 * (h + 5);
                // Row of 2
                } else {
                    var x = (w + 5) / 2 + (i % 5 % 2) * (w + 5);
                    var y = h + 5 + parseInt(i / 5) * 2 * (h + 5);
                }
                
                return {'x':x, 'y':y, 'w':w, 'h':h}
            }
        },
        
        setSplayMode : function(zone, splay_direction, force_full_visible = null) {
            var full_visible = force_full_visible || this.view_full;
            zone.splay_direction = splay_direction;
            if (splay_direction == 0 || splay_direction == 3 || full_visible) {
                // New width = width of a single card
                this.shrinkZoneForNoneOrUpSplay(zone);
            }
            else {
                // New width = width of a single card + (n - 1) * offset
                this.updateZoneWidth(zone);
            }
            var self = this;
            zone.itemIdToCoordsGrid = function(i, control_width) {
                var w = self.card_dimensions[this.HTML_class].width;
                var h = self.card_dimensions[this.HTML_class].height;
                var overlap = self.overlap_for_splay[this.HTML_class][self.display_mode ? "expanded" : "compact"];
                if (full_visible) {
                    var x_beginning = 0;
                    var delta_x = 0;
                    var delta_y = h + 5;
                }
                else {
                    switch(parseInt(splay_direction)) {
                    case 0: // Unsplayed
                        var x_beginning = 0;
                        var delta_x = 0;
                        var delta_y = self.overlap_for_unsplayed;
                        break;
                    case 1: // Splayed left
                        var x_beginning = control_width - w;
                        var delta_x = -overlap;
                        var delta_y = 0;
                        break;
                    case 2: // Splayed right
                        var x_beginning = 0;
                        var delta_x = overlap;
                        var delta_y = 0;
                        break;
                    case 3: // Splayed up
                        var x_beginning = 0;
                        var delta_x = 0;
                        var delta_y = overlap;
                        break;
                    default:
                        break;
                    }
                }
                return {'x':x_beginning + delta_x * i, 'y':delta_y * (full_visible || splay_direction == 3 ? this.items.length - 1 - i : i), 'w':w, 'h':h}
            }

            zone.updateDisplay();
        },
        
        /*
         * Player panel management
         */
        givePlayerActionCard : function(player_id, action_number) {
            dojo.addClass('action_indicator_' + player_id, 'action_card');
            var action_text = action_number == 0 ? _('Free Action') : action_number == 1 ? _('First Action') : _('Second Action');
            var div_action_text = this.createAdjustedContent(action_text, 'action_text', '', 12, 2);
            $('action_indicator_' + player_id).innerHTML = div_action_text;
        },

        destroyActionCard : function() {
            var action_indicators = dojo.query('.action_indicator');
            action_indicators.forEach(function(node) {
                node.innerHTML = "";
            });
            action_indicators.removeClass('action_card');
        },
        
        ///////////////////////////////////////////////////
        //// Player's action
        
        /*
        
            Here, you are defining methods to handle player's action (ex: results of mouse click on 
            game objects).
            
            Most of the time, these methods:
            _ check the action is possible at this game state.
            _ make a call to the game server
        
        */
        
        action_clicForInitialMeld : function(event) {
            if(!this.checkAction('initialMeld')){
                return;
            }
            this.deactivateClickEvents();
            this.addTooltipsWithoutActionsToMyHand();
            this.addTooltipsWithoutActionsToMyBoard();
            
            var HTML_id = this.getCardHTMLIdFromEvent(event);
            var card_id = this.getCardIdFromHTMLId(HTML_id);
            dojo.addClass(HTML_id, "selected");

            var cards_in_hand = this.selectMyCardsInHand();
            this.off(cards_in_hand, 'onclick');
            this.on(cards_in_hand, 'onclick', 'action_clickForUpdatedInitialMeld');
            
            var self = this;
            this.ajaxcall("/innovation/innovation/initialMeld.html",
                            {
                                lock: true,
                                card_id: card_id
                            },
                             this, function(result){}, function(is_error){this.resurrectClickEvents(is_error);}
                        );
        },
    
        action_clickForUpdatedInitialMeld : function(event) {
            this.deactivateClickEvents();
            this.addTooltipsWithoutActionsToMyHand();
            this.addTooltipsWithoutActionsToMyBoard();
            
            dojo.query(".selected").removeClass("selected");
            var HTML_id = this.getCardHTMLIdFromEvent(event);
            var card_id = this.getCardIdFromHTMLId(HTML_id);
            dojo.addClass(HTML_id, "selected");
            
            var self = this;
            this.ajaxcall("/innovation/innovation/updateInitialMeld.html",
                            {
                                lock: true,
                                card_id: card_id
                            },
                             this, function(result){}, function(is_error){this.resurrectClickEvents(is_error);}
                        );
        },

        action_clicForSeizeRelicToHand : function() {
            if (!this.checkAction('seizeRelicToHand')) {
                return;
            }
            this.deactivateClickEvents();
            var self = this;
            this.ajaxcall("/innovation/innovation/seizeRelicToHand.html",
                            {
                                lock: true
                            },
                             this, function(result){}, function(is_error){if(is_error)self.resurrectClickEvents(true)}
                        );            
        },

        action_clicForSeizeRelicToAchievements : function() {
            if (!this.checkAction('seizeRelicToAchievements')) {
                return;
            }
            this.deactivateClickEvents();
            var self = this;
            this.ajaxcall("/innovation/innovation/seizeRelicToAchievements.html",
                            {
                                lock: true
                            },
                             this, function(result){}, function(is_error){if(is_error)self.resurrectClickEvents(true)}
                        );            
        },

        action_clicForPassSeizeRelic : function() {
            if (!this.checkAction('passSeizeRelic')) {
                return;
            }
            this.deactivateClickEvents();
            var self = this;
            this.ajaxcall("/innovation/innovation/passSeizeRelic.html",
                            {
                                lock: true
                            },
                             this, function(result){}, function(is_error){if(is_error)self.resurrectClickEvents(true)}
                        );            
        },

        action_clicForDogmaArtifact : function() {
            if (!this.checkAction('dogmaArtifactOnDisplay')) {
                return;
            }
            this.deactivateClickEvents();
            var self = this;
            this.ajaxcall("/innovation/innovation/dogmaArtifactOnDisplay.html",
                            {
                                lock: true
                            },
                             this, function(result){}, function(is_error){if(is_error)self.resurrectClickEvents(true)}
                        );            
        },

        action_clicForReturnArtifact : function() {
            if (!this.checkAction('returnArtifactOnDisplay')) {
                return;
            }
            this.deactivateClickEvents();
            var self = this;
            this.ajaxcall("/innovation/innovation/returnArtifactOnDisplay.html",
                            {
                                lock: true
                            },
                             this, function(result){}, function(is_error){if(is_error)self.resurrectClickEvents(true)}
                        );            
        },

        action_clicForPassArtifact : function() {
            if (!this.checkAction('passArtifactOnDisplay')) {
                return;
            }
            this.deactivateClickEvents();
            var self = this;
            this.ajaxcall("/innovation/innovation/passArtifactOnDisplay.html",
                            {
                                lock: true
                            },
                             this, function(result){}, function(is_error){if(is_error)self.resurrectClickEvents(true)}
                        );            
        },

        action_clickForPassPromote : function() {
            if (!this.checkAction('passPromoteCard')) {
                return;
            }
            this.deactivateClickEvents();
            var self = this;
            this.ajaxcall("/innovation/innovation/passPromoteCard.html",
                            {
                                lock: true
                            },
                             this, function(result){}, function(is_error){if(is_error)self.resurrectClickEvents(true)}
                        );            
        },

        action_clickForPromote : function(event) {
            if (!this.checkAction('promoteCard')) {
                return;
            }

            var HTML_id = this.getCardHTMLIdFromEvent(event);
            var card_id = this.getCardIdFromHTMLId(HTML_id);
            this.ajaxcall("/innovation/innovation/promoteCard.html",
                {
                    lock: true,
                    card_id: card_id
                },
                this,
                function(result) { },
                function(is_error) { if (is_error) this.resurrectClickEvents(true); }
            );
        },

        action_clickCardBackForPromote : function(event) {
            if (!this.checkAction('promoteCard')) {
                return;
            }

            var HTML_id = this.getCardHTMLIdFromEvent(event);
            var card_id = this.getCardIdFromHTMLId(HTML_id);
            var age = this.getCardAgeFromHTMLId(HTML_id);
            var type = this.getCardTypeFromHTMLId(HTML_id);
            var is_relic = this.getCardIsRelicFromHTMLId(HTML_id);
            var owner = this.player_id;
            var location = 'forecast';
            var zone = this.getZone(location, owner, null, age);
            var position = this.getCardPositionFromId(zone, card_id, age, type, is_relic);
            this.ajaxcall("/innovation/innovation/promoteCardBack.html",
                {
                    lock: true,
                    owner: owner,
                    location: location,
                    age: age,
                    type: type,
                    is_relic: is_relic,
                    position: position
                },
                this,
                function(result) { },
                function(is_error) { if (is_error) this.resurrectClickEvents(true); }
            );
        },

        action_clickForPassDogmaPromoted : function() {
            if (!this.checkAction('passDogmaPromotedCard')) {
                return;
            }
            this.deactivateClickEvents();
            var self = this;
            this.ajaxcall("/innovation/innovation/passDogmaPromotedCard.html",
                            {
                                lock: true
                            },
                             this, function(result){}, function(is_error){if(is_error)self.resurrectClickEvents(true)}
                        );            
        },

        action_clickForDogmaPromoted : function() {
            if (!this.checkAction('dogmaPromotedCard')) {
                return;
            }
            this.deactivateClickEvents();
            var self = this;
            this.ajaxcall("/innovation/innovation/dogmaPromotedCard.html",
                            {
                                lock: true
                            },
                             this, function(result){}, function(is_error){if(is_error)self.resurrectClickEvents(true)}
                        );            
        },
        
        // TODO(#673): We need to add a new method in order to allow players to click on a specific achievement to achieve.
        // Right now all we are doing is taking the age, and then achieving an arbitrary claimable achievement of that age.
        // The vast majority of the time, players won't notice or care, which is why we haven't implemented it yet.
        action_clicForAchieve : function(event) {
            if(!this.checkAction('achieve')){
                return;
            }
            this.deactivateClickEvents();
            
            var HTML_id = this.getCardHTMLIdFromEvent(event);
            if (HTML_id.substr(0, 4) == "item") { // The achievement card itself has been clicked
                var age = this.getCardAgeFromHTMLId(HTML_id);
            }
            else { // This action has been take using the button
                var age = HTML_id.substr(HTML_id.length-1, 1);
            }
            
            var self = this;
            this.ajaxcall("/innovation/innovation/achieve.html",
                            {
                                lock: true,
                                age: age
                            },
                            this, function(result){}, function(is_error){if(is_error)self.resurrectClickEvents(true)}
           );
        },
        
        action_clicForDraw : function(event) {
            if(!this.checkAction('draw')){
                return;
            }
            this.deactivateClickEvents();
            
            var self = this;
            this.ajaxcall("/innovation/innovation/draw.html",
                            {
                                lock: true,
                            },
                            this, function(result){}, function(is_error){if(is_error)self.resurrectClickEvents(true)}
           );
        },
        
        action_clickMeld : function(event) {
            this.stopActionTimer();
            this.deactivateClickEvents();

            var HTML_id = this.getCardHTMLIdFromEvent(event);
            var card_id = dojo.attr(HTML_id, 'card_id');
            var card_name = this.cards[card_id].name;

            $('pagemaintitletext').innerHTML = dojo.string.substitute(_("Meld ${card_name}?"), {'card_name' : _(card_name)});

            // Add cancel button
            this.addActionButton("meld_cancel_button", _("Cancel"), "action_cancelMeld");
            dojo.removeClass("meld_cancel_button", 'bgabutton_blue');
            dojo.addClass("meld_cancel_button", 'bgabutton_red');

            // Add confirm button
            this.addActionButton("meld_confirm_button", _("Confirm"), "action_manuallyConfirmMeld");

            $("meld_confirm_button").innerHTML = _("Confirm");
            dojo.attr('meld_confirm_button', 'html_id', HTML_id);

            // When confirmation is disabled in game preferences, click the confirmation button instantly
            var wait_time = 0;

            // Short timer (3 seconds)
            if (this.prefs[101].value == 2) {
                wait_time = 2;

            // Medium timer (5 seconds)
            } else if (this.prefs[101].value == 3) {
                wait_time = 4;

            // Long timer (10 seconds)
            } else if (this.prefs[101].value == 4) {
                wait_time = 9;
            }

            this.startActionTimer("meld_confirm_button", wait_time, this.action_confirmMeld, HTML_id);
        },

        action_cancelMeld : function(event) {
            this.stopActionTimer();
            this.resurrectClickEvents(true);
            dojo.destroy("meld_cancel_button");
            dojo.destroy("meld_confirm_button");
        },

        action_manuallyConfirmMeld : function(event) {
            this.stopActionTimer();
            var HTML_id = dojo.attr('meld_confirm_button', 'html_id');
            this.action_confirmMeld(HTML_id);
        },

        action_confirmMeld : function(HTML_id) {
            if (!this.checkAction('meld')) {
                return;
            }

            dojo.destroy("meld_cancel_button");
            dojo.destroy("meld_confirm_button");

            var card_id = this.getCardIdFromHTMLId(HTML_id);
            this.ajaxcall("/innovation/innovation/meld.html",
                {
                    lock: true,
                    card_id: card_id
                },
                this,
                function(result) { },
                function(is_error) { if (is_error) this.resurrectClickEvents(true); }
            );
        },
        
        action_clickDogma : function(event) {
            this.stopActionTimer();
            this.deactivateClickEvents();

            var HTML_id = this.getCardHTMLIdFromEvent(event);
            var card_id = dojo.attr(HTML_id, 'card_id');
            var card = this.cards[card_id];
            $('pagemaintitletext').innerHTML = dojo.string.substitute(_("Dogma ${age} ${card_name}?"), {'age' : this.square('N', 'age', card.age, 'type_' + card.type), 'card_name' : _(card.name)});

            // Add cancel button
            this.addActionButton("dogma_cancel_button", _("Cancel"), "action_cancelDogma");
            dojo.removeClass("dogma_cancel_button", 'bgabutton_blue');
            dojo.addClass("dogma_cancel_button", 'bgabutton_red');

            // Add confirm button for timer
            this.addActionButton("dogma_confirm_timer_button", _("Confirm"), "action_manuallyConfirmTimerDogma");

            $("dogma_confirm_timer_button").innerHTML = _("Confirm");
            dojo.attr('dogma_confirm_timer_button', 'html_id', HTML_id);

            // When confirmation is disabled in game preferences, click the confirmation button instantly
            var wait_time = 0;

            // Short timer (3 seconds)
            if (this.prefs[100].value == 2) {
                wait_time = 2;
            
            // Medium timer (5 seconds)
            } else if (this.prefs[100].value == 3) {
                wait_time = 4;
            
            // Long timer (10 seconds)
            } else if (this.prefs[100].value == 4) {
                wait_time = 9;
            }
            
            this.startActionTimer("dogma_confirm_timer_button", wait_time, this.action_manuallyConfirmTimerDogma, event);
        },

        action_cancelDogma : function(event) {
            this.stopActionTimer();
            this.resurrectClickEvents(true);
            dojo.destroy("dogma_cancel_button");
            dojo.destroy("dogma_confirm_timer_button");
            dojo.destroy("dogma_confirm_warning_button");
        },

        action_manuallyConfirmTimerDogma : function(event) {
            this.stopActionTimer();
            var HTML_id = dojo.attr('dogma_confirm_timer_button', 'html_id');

            var card_id = dojo.attr(HTML_id, 'card_id');
            var card = this.cards[card_id];
            var sharing_players = dojo.attr(HTML_id, 'non_demand_effect_players');
            if (sharing_players == '') {
                sharing_players = dojo.attr(HTML_id, 'echo_effect_players');
            }
            if (dojo.attr(HTML_id, 'no_effect')) {
                $('pagemaintitletext').innerHTML = dojo.string.substitute(_("Are you sure you want to dogma ${age} ${card_name}? It will have no effect."),
                    {
                        'age': this.square('N', 'age', card.age, 'type_' + card.type),
                        'card_name' : _(card.name)
                    }
                );
                dojo.destroy("dogma_confirm_timer_button");
                this.addActionButton("dogma_confirm_warning_button", _("Confirm"), "action_manuallyConfirmWarningDogma");
                dojo.attr('dogma_confirm_warning_button', 'html_id', HTML_id);
            } else if (sharing_players.includes(',')) {
                $('pagemaintitletext').innerHTML = dojo.string.substitute(_("Are you sure you want to dogma ${age} ${card_name}? ${players} will share the effect(s)."),
                    {
                        'age': this.square('N', 'age', card.age, 'type_' + card.type),
                        'card_name' : _(card.name),
                        'players' : this.getOtherPlayersCommaSeparated(sharing_players.split(','))
                    }
                );
                dojo.destroy("dogma_confirm_timer_button");
                this.addActionButton("dogma_confirm_warning_button", _("Confirm"), "action_manuallyConfirmWarningDogma");
                dojo.attr('dogma_confirm_warning_button', 'html_id', HTML_id);
            } else {
                this.action_confirmDogma(HTML_id);
            }
        },

        action_manuallyConfirmWarningDogma : function(event) {
            var HTML_id = dojo.attr('dogma_confirm_warning_button', 'html_id');
            this.action_confirmDogma(HTML_id);
        },

        action_confirmDogma : function(HTML_id) {
            if (!this.checkAction('dogma')) {
                return;
            }

            dojo.destroy("dogma_cancel_button");
            dojo.destroy("dogma_confirm_timer_button");
            dojo.destroy("dogma_confirm_warning_button");

            var card_id = this.getCardIdFromHTMLId(HTML_id);
            this.ajaxcall("/innovation/innovation/dogma.html",
                {
                    lock: true,
                    card_id: card_id
                },
                this,
                function(result) { },
                function(is_error) { if (is_error) this.resurrectClickEvents(true); }
            );
        },

        action_clickForChooseFront : function(event) {
            this.stopActionTimer();
            this.deactivateClickEvents();
            
            var HTML_id = this.getCardHTMLIdFromEvent(event);
            var warning = dojo.attr(HTML_id, 'warning');

            $('pagemaintitletext').innerHTML = warning;

            // Add cancel button
            this.addActionButton("choose_front_cancel_button", _("Cancel"), "action_cancelChooseFront");
            dojo.removeClass("choose_front_cancel_button", 'bgabutton_blue');
            dojo.addClass("choose_front_cancel_button", 'bgabutton_red');

            // Add confirm button
            this.addActionButton("choose_front_confirm_button", _("Confirm"), "action_manuallyConfirmChooseFront");
            $("choose_front_confirm_button").innerHTML = _("Confirm");
            dojo.attr('choose_front_confirm_button', 'html_id', HTML_id);

            // TODO(LATER): If the card doesn't have a warning, add a confirmation button with a countdown timer.
        },

        action_cancelChooseFront : function(event) {
            this.stopActionTimer();
            this.resurrectClickEvents(true);
            dojo.destroy("choose_front_cancel_button");
            dojo.destroy("choose_front_confirm_button");
        },

        action_manuallyConfirmChooseFront : function(event) {
            this.stopActionTimer();
            var HTML_id = dojo.attr('choose_front_confirm_button', 'html_id');
            this.action_confirmChooseFront(HTML_id);
        },

        action_confirmChooseFront : function(HTML_id) {
            if (!this.checkAction('choose')){
                return;
            }
            // If the piles were forcibly made visible, collapse them
            for (var color = 0; color < 5; color++) {
                var zone = this.zone.board[this.player_id][color];
                this.setSplayMode(zone, zone.splay_direction, force_full_visible=false);
            }
            
            var card_id = this.getCardIdFromHTMLId(HTML_id);
            
            var self = this;
            this.ajaxcall("/innovation/innovation/choose.html",
                            {
                                lock: true,
                                card_id: card_id
                            },
                             this, function(result){}, function(is_error){if(is_error)self.resurrectClickEvents(true)}
                        );
        },
        
        // TODO(LATER): Remove this once we have a personal preference for confirming card choices.
        action_clicForChoose : function(event) {
            this.deactivateClickEvents();
            
            var HTML_id = this.getCardHTMLIdFromEvent(event);
            this.action_confirmChooseFront(HTML_id);
        },
        
        action_clicForChooseRecto : function(event) {
            if(!this.checkAction('choose')){
                return;
            }
            this.deactivateClickEvents();
            
            var HTML_id = this.getCardHTMLIdFromEvent(event);
            var card_id = this.getCardIdFromHTMLId(HTML_id);
            var age = this.getCardAgeFromHTMLId(HTML_id);
            var type = this.getCardTypeFromHTMLId(HTML_id);
            var is_relic = this.getCardIsRelicFromHTMLId(HTML_id);
            
            // Search the zone containing that card
            var zone_container = event.currentTarget.parentNode;
            var zone_infos = dojo.getAttr(zone_container, 'id').split('_');
            var location = zone_infos[0];
            var owner = location == 'deck' ? 0 : zone_infos[1];
            if (!owner) {
                owner = 0;
            }
            var zone = this.getZone(location, owner, type, age);
            
            // Search the position the card is
            var position = this.getCardPositionFromId(zone, card_id, age, type, is_relic);

            var self = this;
            this.ajaxcall("/innovation/innovation/chooseRecto.html",
                            {
                                lock: true,
                                owner: owner,
                                location: location,
                                age: age,
                                type: type,
                                is_relic: is_relic,
                                position: position
                            },
                             this, function(result){}, function(is_error){if(is_error)self.resurrectClickEvents(true)}
                        );
        },

        action_clickButtonToDecreaseIntegers : function(event) {
            if (!this.checkAction('choose')) {
                return;
            }
            var current_lowest_integer = parseInt(document.getElementById("choice_0").text);
            if (current_lowest_integer > 0) {
                for (var i = 0; i < 6; i++) {
                    document.getElementById("choice_" + i).text = current_lowest_integer - 1 + i;
                }
                if (current_lowest_integer == 1) {
                    dojo.byId('decrease_integers').style.display = 'none';
                }
                dojo.byId('increase_integers').style.display = 'inline-block';
            }
        },

        action_clickButtonToIncreaseIntegers : function(event) {
            if (!this.checkAction('choose')) {
                return;
            }
            var current_lowest_integer = parseInt(document.getElementById("choice_0").text);
            if (current_lowest_integer < 995) {
                for (var i = 0; i < 6; i++) {
                    document.getElementById("choice_" + i).text = current_lowest_integer + 1 + i;
                }
                dojo.byId('decrease_integers').style.display = 'inline-block';
                if (current_lowest_integer == 994) {
                    dojo.byId('increase_integers').style.display = 'none';
                }
            }
        },
        
        action_clicForChooseSpecialOption : function(event) {
            if(!this.checkAction('choose')){
                return;
            }
            
            var HTML_id = this.getCardHTMLIdFromEvent(event);
            var choice = HTML_id.substr(7)
            
            if (this.choose_two_colors) {
                if (this.first_chosen_color === null) {
                    this.first_chosen_color = choice;
                    dojo.destroy(event.target); // Destroy the button
                    var query = dojo.query('#pagemaintitletext > span[style]');
                    var You = query[query.length - 1].outerHTML;
                    $('pagemaintitletext').innerHTML = dojo.string.substitute(_("${You} still must choose one color"), {'You':You});
                    return;
                }
                choice = Math.pow(2,this.first_chosen_color) + Math.pow(2,choice); // Set choice as encoded value for the array of the two chosen colors
                this.first_chosen_color = null;
            } else if (this.choose_three_colors) {
                if (this.first_chosen_color === null) {
                    this.first_chosen_color = choice;
                    dojo.destroy(event.target); // Destroy the button
                    var query = dojo.query('#pagemaintitletext > span[style]');
                    var You = query[query.length - 1].outerHTML;
                    $('pagemaintitletext').innerHTML = dojo.string.substitute(_("${You} still must choose two colors"), {'You':You});
                    return;
                }
                if (this.second_chosen_color === null) {
                    this.second_chosen_color = choice;
                    dojo.destroy(event.target); // Destroy the button
                    var query = dojo.query('#pagemaintitletext > span[style]');
                    var You = query[query.length - 1].outerHTML;
                    $('pagemaintitletext').innerHTML = dojo.string.substitute(_("${You} still must choose one color"), {'You':You});
                    return;
                }
                choice = Math.pow(2,this.second_chosen_color) + Math.pow(2,this.first_chosen_color) + Math.pow(2,choice); // Set choice as encoded value for the array of the three chosen colors
                this.first_chosen_color = null;
                this.second_chosen_color = null;
            } else if (this.choose_integer) {
                choice = parseInt(document.getElementById(HTML_id).text);
            }
           
            this.deactivateClickEvents();
            
            var self = this;
            this.ajaxcall("/innovation/innovation/chooseSpecialOption.html",
                            {
                                lock: true,
                                choice: choice
                            },
                             this, function(result){}, function(is_error){if(is_error)self.resurrectClickEvents(true)}
                        );
        },
        
        action_clicForPassOrStop : function() {
            if(!this.checkAction('choose')){
                return;
            }
            if (this.publication_permutations_done !== null) { // Special code for Publication: undo the changes the player made to his board
                this.publicationClicForUndoingSwaps();
                for(var color=0; color<5; color++) {
                    var zone = this.zone.board[this.player_id][color];
                    this.setSplayMode(zone, zone.splay_direction, force_full_visible=false);
                }
                this.on(dojo.query('#change_display_mode_button'), 'onclick', 'toggle_displayMode');
                //dojo.style('change_display_mode_button', {'display': 'initial'}); // Show back the button used for changing the display
                this.publication_permuted_zone = null;
                this.publication_permutations_done = null;
                this.publication_original_items = null;
            }
            else if (this.color_pile !== null) { // Special code where a stack needed to be selected
                var zone = this.zone.board[this.player_id][this.color_pile];
                this.setSplayMode(zone, zone.splay_direction, force_full_visible=false);
            }
                
            this.deactivateClickEvents();
            var self = this;
            this.ajaxcall("/innovation/innovation/choose.html",
                            {
                                lock: true,
                                card_id: -1
                            },
                             this, function(result){}, function(is_error){if(is_error)self.resurrectClickEvents(true)}
                        );            
        },
        
        action_clicForSplay : function(event) {
            if(!this.checkAction('choose')){
                return;
            }
            this.deactivateClickEvents();
            
            var HTML_id = this.getCardHTMLIdFromEvent(event);
            var color = HTML_id.substr(6)
            var self = this;
            this.ajaxcall("/innovation/innovation/choose.html",
                            {
                                lock: true,
                                card_id: this.getCardIdFromHTMLId(this.zone.board[this.player_id][color].items[0].id) // A choose for splay is equivalent as selecting a board card of the right color, by design
                            },
                             this, function(result){}, function(is_error){if(is_error)self.resurrectClickEvents(true)}
                        );   
        },
        
        action_publicationClicForRearrange : function(event) {
            if(!this.checkAction('choose')){
                return;
            }
            
            var permuted_color = this.publication_permuted_zone.container_div.slice(-1);
            var permutations_done = [];
            for(var i=0; i<this.publication_permutations_done.length; i++) {
                var permutation =this.publication_permutations_done[i];
                permutations_done.push(permutation.position + "," + permutation.delta);
            }
            permutations_done = permutations_done.join(";")
            
            this.publicationResetInterface();
            
            for(var color=0; color<5; color++) {
                var zone = this.zone.board[this.player_id][color];
                this.setSplayMode(zone, zone.splay_direction, force_full_visible=false);
            }
            this.on(dojo.query('#change_display_mode_button'), 'onclick', 'toggle_displayMode');
            //dojo.style('change_display_mode_button', {'display': 'initial'}); // Show back the button used for changing the display
            
            this.publication_permuted_zone = null;
            this.publication_permutations_done = null;
            this.publication_original_items = null;
            
            this.deactivateClickEvents();
            
            var self = this;
            this.ajaxcall("/innovation/innovation/publicationRearrange.html",
                            {
                                lock: true,
                                color: permuted_color,
                                permutations_done: permutations_done
                            },
                             this, function(result){}, function(is_error){if(is_error)self.resurrectClickEvents(true)}
                        );   
        },
        
        publicationClicForMove : function(event) {          
            var HTML_id = this.getCardHTMLIdFromEvent(event);
            
            var arrow_up = $('publication_arrow_up');
            var arrow_down = $('publication_arrow_down');
            if (!arrow_up) {
                arrow_up = dojo.create('button', {'id': 'publication_arrow_up'});
                arrow_up.innerHTML = "<span>&#8593;</span>"; // Code for arrow up
                dojo.connect(arrow_up, 'onclick', this, 'publicationClicForSwap'); 
                arrow_down = dojo.create('button', {'id': 'publication_arrow_down'});
                arrow_down.innerHTML = "<span>&#8595;</span>"; // Code for arrow down
                dojo.connect(arrow_down, 'onclick', this, 'publicationClicForSwap'); 
            }
            dojo.place(arrow_up, HTML_id);
            dojo.place(arrow_down, HTML_id);
        },
        
        publicationClicForSwap : function(event) {
            var arrow = event.currentTarget;
            var delta = arrow == $('publication_arrow_up') ? 1 : -1; // Change of position requested
            var HTML_id = dojo.getAttr(arrow.parentNode, 'id');
            var card_id = this.getCardIdFromHTMLId(HTML_id);
            var color = this.cards[card_id].color;
            
            // Search position in zone
            var zone = this.zone.board[this.player_id][color];
            var items = zone.items;
            for(var p=0; p<items.length; p++) {
                var it = zone.items[p] 
                if (it.id == HTML_id) {
                    var item = it;
                    var position = p;
                    break;
                }
            }
            if (position == 0 && delta == -1 || position == items.length-1 && delta == 1) {
                return; // The card is already on max position
            }

            if (this.publication_permutations_done.length == 0) { // First change
                // Add cancel button
                var cancel = dojo.create('a', {'id':'publication_cancel', 'class' : 'bgabutton bgabutton_red'});
                cancel.innerHTML = _("Cancel");
                dojo.place(cancel, $('splay_indicator_' + this.player_id + '_' + color), 'after')
                dojo.connect(cancel, 'onclick', this, 'publicationClicForUndoingSwaps');
                
                // Add done button
                var done = dojo.create('a', {'id':'publication_done', 'class' : 'bgabutton bgabutton_blue'});
                done.innerHTML = _("Done");
                dojo.place(done, cancel, 'after')
                dojo.connect(done, 'onclick', this, 'action_publicationClicForRearrange'); 
                
                // Deactivate click events for other colors
                var other_colors = [0,1,2,3,4];
                other_colors.splice(color, 1);
                var cards = this.selectCardsOnMyBoardOfColors(other_colors);
                cards.removeClass("clickable");
                cards.removeClass("mid_dogma");
                this.off(cards, 'onclick');
                
                // Mark info
                this.publication_permuted_zone = zone;
                this.publication_original_items = items.slice();
            }
            
            // Mark info
            this.publication_permutations_done.push({'position':position, 'delta':delta});
            
            // Swap positions
            this.publicationSwap(this.player_id, zone, position, delta)
            
            no_change = true;
            for(var p=0; p<items.length; p++) {
                if (items[p] != this.publication_original_items[p]) {
                    no_change = false;
                }
            }
            
            if (no_change) { // The permutation cycled to the initial situation
                // Prevent user to validate this
                this.publicationResetInterface(keep_arrows=true);
            }
        },
        
        publicationClicForUndoingSwaps : function() {
            // Undo publicationSwaps
            for(var i=this.publication_permutations_done.length-1; i>=0; i--) {
                var permutation = this.publication_permutations_done[i]
                this.publicationSwap(this.player_id, this.publication_permuted_zone, permutation.position, permutation.delta); // Re-appliying a permutation cancels it
            }
            
            // Reset interface
            this.publicationResetInterface();
        },
        
        publicationResetInterface : function(keep_arrows =false) {
            if (!keep_arrows) {
                dojo.destroy('publication_arrow_up');
                dojo.destroy('publication_arrow_down');
            }
            dojo.destroy('publication_cancel');
            dojo.destroy('publication_done');
            
            var selectable_cards = this.selectAllCardsOnMyBoard();
            selectable_cards.addClass("clickable");
            selectable_cards.addClass("mid_dogma");
            this.on(selectable_cards, 'onclick', 'publicationClicForMove');
            
            this.publication_permuted_zone = null;
            this.publication_permutations_done = [];
        },
        
        publicationSwap : function(player_id, zone, position, delta) {
            position = parseInt(position);
            delta = parseInt(delta);
            var item = zone.items[position];
            var other_item = zone.items[position+delta];
            item.weight += delta;
            other_item.weight -= delta;
            
            dojo.style(item.id, 'z-index', item.weight)
            dojo.style(other_item.id, 'z-index', other_item.weight)
            
            zone.items[position+delta] = item;
            zone.items[position] = other_item;
            
            // Change ressource if the card on top is involved
            if (position == zone.items.length-1 || position + delta == zone.items.length-1) {
                up = delta == 1;
                old_top_item = up ? other_item : item;
                new_top_item = up ? item : other_item;
                
                old_top_card = this.cards[this.getCardIdFromHTMLId(old_top_item.id)];
                new_top_card = this.cards[this.getCardIdFromHTMLId(new_top_item.id)];
                
                ressource_counts = {};
                for(var icon=1; icon<=6; icon++) {
                    ressource_counts[icon] = this.counter.ressource_count[player_id][icon].getValue();
                }
                
                // TODO(CITIES): Account for more spots on the card.
                switch(parseInt(zone.splay_direction)) {
                case 0: // All icons of the old top card are lost
                    ressource_counts[old_top_card.spot_1]--
                    ressource_counts[old_top_card.spot_2]--
                    ressource_counts[old_top_card.spot_3]--
                    ressource_counts[old_top_card.spot_4]--
                    
                    ressource_counts[new_top_card.spot_1]++
                    ressource_counts[new_top_card.spot_2]++
                    ressource_counts[new_top_card.spot_3]++
                    ressource_counts[new_top_card.spot_4]++
                    break;
                case 1: // Only the icon on bottom right can still be seen (spot_4)
                    ressource_counts[old_top_card.spot_1]--
                    ressource_counts[old_top_card.spot_2]--
                    ressource_counts[old_top_card.spot_3]--
                    
                    ressource_counts[new_top_card.spot_1]++
                    ressource_counts[new_top_card.spot_2]++
                    ressource_counts[new_top_card.spot_3]++
                    break;
                case 2: // Icons on left can still be seen (spot_1 and spot_2)
                    ressource_counts[old_top_card.spot_3]--
                    ressource_counts[old_top_card.spot_4]--
                    
                    ressource_counts[new_top_card.spot_3]++
                    ressource_counts[new_top_card.spot_4]++
                    break;
                case 3: // Icons on bottom can still be seen (spot_2, spot_3 and spot_4)
                    ressource_counts[old_top_card.spot_1]--
                    
                    ressource_counts[new_top_card.spot_1]++
                    break;
                }
                
                for(var icon=1; icon<=6; icon++) {
                    this.counter.ressource_count[player_id][icon].setValue(ressource_counts[icon]);
                }
                
            }
            
            zone.updateDisplay();
        },

        click_display_forecast_window : function() {
            this.my_forecast_verso_window.show();
        },
        
        click_close_forecast_window : function() {
            this.my_forecast_verso_window.hide();
        },
        
        click_display_score_window : function() {
            this.my_score_verso_window.show();
        },
        
        click_close_score_window : function() {
            this.my_score_verso_window.hide();
        },
        
        toggle_displayMode : function() {
            // Indicate the change of display mode
            this.display_mode = !this.display_mode;
            
            var button_text = this.display_mode ? this.text_for_expanded_mode : this.text_for_compact_mode;
            var arrows =  this.display_mode ? this.arrows_for_expanded_mode : this.arrows_for_compact_mode;
            
            var inside_button = this.format_string_recursive("${arrows} ${button_text}", {'arrows':arrows, 'button_text':button_text, 'i18n':['button_text']});
            
            $('change_display_mode_button').innerHTML = inside_button;
            
            // Update the display of the stacks
            for(var player_id in this.players) {
                for(var color = 0; color < 5; color++){
                    var zone = this.zone.board[player_id][color];
                    this.setSplayMode(zone, zone.splay_direction);
                }
            }
            
            if (!this.isSpectator) {
                // Inform the server of this change to make it by default if the player refreshes the page
                this.ajaxcall("/innovation/innovation/updateDisplayMode.html",
                            {
                                lock: true,
                                display_mode: this.display_mode
                            },
                             this, function(result){}, function(is_error){}
                        );
            }
        },
        
        toggle_view : function() {
            // Indicate the change of view
            this.view_full = !this.view_full;
            
            var button_text = this.view_full ? this.text_for_view_full : this.text_for_view_normal;
            
            var inside_button = this.format_string_recursive("${button_text}", {'button_text':button_text, 'i18n':['button_text']});
            
            $('change_view_full_button').innerHTML = inside_button;
            
            // Update the display of the stacks
            for(var player_id in this.players) {
                for(var color = 0; color < 5; color++){
                    var zone = this.zone.board[player_id][color];
                    this.setSplayMode(zone, zone.splay_direction);
                }
            }
            
            if (!this.isSpectator) {
                // Inform the server of this change to make it by default if the player refreshes the page
                this.ajaxcall("/innovation/innovation/updateViewFull.html",
                            {
                                lock: true,
                                view_full: this.view_full
                            },
                             this, function(result){}, function(is_error){}
                        );
            }
        },

        click_open_special_achievement_browsing_window : function() {
            this.click_open_card_browsing_window();
            this.click_browse_special_achievements();
        },

        click_open_card_browsing_window : function() {
            this.card_browsing_window.show();
        },

        click_close_card_browsing_window : function() {
            this.card_browsing_window.hide();
        },

        click_browse_cards : function(event) {
            var id = dojo.getAttr(event.currentTarget, 'id');

            dojo.byId('browse_cards_buttons_row_2').style.display = 'block';

            if (id.startsWith('browse_cards_type_')) {
                dojo.query('#browse_cards_buttons_row_1 > .browse_cards_button').removeClass('selected');
                dojo.query(`#${id}`).addClass('selected');
                dojo.byId('browse_cards_buttons_row_2').style.display = 'block';
                if (this.relics_enabled) {
                    dojo.byId('browse_relics').style.display = (id == 'browse_cards_type_1') ? 'inline-block' : 'none';
                }
                if (dojo.query('#browse_cards_buttons_row_2 > .browse_cards_button.selected').length == 0) {
                    dojo.query('#browse_cards_age_1').addClass('selected');
                }
            } else {
                dojo.query('#browse_cards_buttons_row_2 > .browse_cards_button').removeClass('selected');
                dojo.query(`#${id}`).addClass('selected');
            }

            dojo.byId('browse_card_summaries').style.display = 'block';
            dojo.query('#special_achievement_summaries').addClass('heightless');

            var node = dojo.query('#browse_card_summaries')[0];
            node.innerHTML = '';

            // Special case for relics
            if (dojo.query(`#browse_relics.selected`).length > 0) {
                for (var i = 215; i <= 219; i++) {
                    if (this.canShowCardTooltip(i)) {
                        node.innerHTML += this.createCardForCardBrowser(i);
                    }
                }
                return;
            }

            // Figure out which set is selected
            var type = 0;
            for (var i = 0; i <= 4; i++) {
                if (dojo.query(`#browse_cards_type_${i}.selected`).length > 0) {
                    type = i;
                }
            }

            // Figure out which age is selected
            var age = 1;
            for (var i = 1; i <= 10; i++) {
                if (dojo.query(`#browse_cards_age_${i}.selected`).length > 0) {
                    age = i;
                }
            }

            // Determine range of cards to render            
            var min_id = type * 110 + age * 10 - 5;
            var max_id = min_id + 9;
            if (age == 1) {
                min_id -= 5;
            }

            // Special case for relics
            if (dojo.query(`#browse_relics.selected`).length > 0) {
                min_id = 215;
                max_id = 219;
            }

            // Add cards to popup
            for (var i = min_id; i <= max_id; i++) {
                node.innerHTML += this.createCardForCardBrowser(i);
            }

            // NOTE: For some reason the tooltips get removed when we add more HTLM to the node, so we need to use a
            // separate loop to add them.
            for (var i = min_id; i <= max_id; i++) {
                this.addCustomTooltip(`browse_card_id_${i}`, this.getTooltipForCard(i), "");
            }
        },

        click_browse_special_achievements : function() {
            dojo.query('.browse_cards_button').removeClass('selected');
            dojo.query('#browse_special_achievements').addClass('selected');
            dojo.byId('browse_cards_buttons_row_2').style.display = 'none';
            dojo.byId('browse_card_summaries').style.display = 'none';
            dojo.query('#special_achievement_summaries').removeClass('heightless');
        },
        
        ///////////////////////////////////////////////////
        //// Reaction to cometD notifications

        /*
            setupNotifications:
            
            In this method, you associate each of your game notifications with your local method to handle it.
            
            Note: game notification names correspond to "notifyAllPlayers" and "notifyPlayer" calls in
                  your innovation.game.php file.
        
        */
            setupNotifications: function () {
            var reasonnable_delay = 1000;
            
            dojo.subscribe('transferedCard', this, "notif_transferedCard");
            this.notifqueue.setSynchronous( 'transferedCard', reasonnable_delay );   // Wait X milliseconds after executing the transferedCard handler

            dojo.subscribe('logWithCardTooltips', this, "notif_logWithCardTooltips");  // This kind of notification does not need any delay
            
            dojo.subscribe('splayedPile', this, "notif_splayedPile")
            this.notifqueue.setSynchronous( 'splayedPile', reasonnable_delay );   // Wait X milliseconds after executing the splayedPile handler
            
            dojo.subscribe('rearrangedPile', this, "notif_rearrangedPile");  // This kind of notification does not need any delay
            
            dojo.subscribe('removedHandsBoardsAndScores', this, "notif_removedHandsBoardsAndScores");  // This kind of notification does not need any delay
            dojo.subscribe('removedTopCardsAndHands', this, "notif_removedTopCardsAndHands");  // This kind of notification does not need any delay
            dojo.subscribe('removedPlayer', this, "notif_removedPlayer");  // This kind of notification does not need any delay

            dojo.subscribe('updateResourcesForArtifactOnDisplay', this, "notif_updateResourcesForArtifactOnDisplay");  // This kind of notification does not need any delay
            dojo.subscribe('resetMonumentCounters', this, "notif_resetMonumentCounters");  // This kind of notification does not need any delay
            
            dojo.subscribe('log', this, "notif_log"); // This kind of notification does not change anything but log on the interface, no delay
            
            if (this.isSpectator) {
                dojo.subscribe('transferedCard_spectator', this, "notif_transferedCard_spectator");
                this.notifqueue.setSynchronous( 'transferedCard_spectator', reasonnable_delay );   // Wait X milliseconds after executing the handler

                dojo.subscribe('logWithCardTooltips_spectator', this, "notif_logWithCardTooltips_spectator");  // This kind of notification does not need any delay
                
                dojo.subscribe('splayedPile_spectator', this, "notif_splayedPile_spectator");
                this.notifqueue.setSynchronous( 'splayedPile_spectator', reasonnable_delay );   // Wait X milliseconds after executing the handler
                
                dojo.subscribe('rearrangedPile_spectator', this, "notif_rearrangedPile_spectator"); // This kind of notification does not need any delay
                
                dojo.subscribe('removedHandsBoardsAndScores_spectator', this, "notif_removedHandsBoardsAndScores_spectator");  // This kind of notification does not need any delay
                dojo.subscribe('removedTopCardsAndHands_spectator', this, "notif_removedTopCardsAndHands_spectator");  // This kind of notification does not need any delay
                dojo.subscribe('removedPlayer_spectator', this, "notif_removedPlayer_spectator");  // This kind of notification does not need any delay

                dojo.subscribe('updateResourcesForArtifactOnDisplay_spectator', this, "notif_updateResourcesForArtifactOnDisplay_spectator");  // This kind of notification does not need any delay
                dojo.subscribe('resetMonumentCounters_spectator', this, "notif_resetMonumentCounters_spectator");  // This kind of notification does not need any delay
                
                dojo.subscribe('log_spectator', this, "notif_log_spectator"); // This kind of notification does not change anything but log on the interface, no delay
            };
        },
        
        notif_transferedCard : function(notif) {
            var card = notif.args;

            if (parseInt(card.is_relic) == 1) {
                card.id = 212 + parseInt(card.age);
            }

            // Special code for my forecast management
            if (card.location_from == "forecast" && card.owner_from == this.player_id) {
                // Remove the card from my forecast personal window
                // NOTE: The button to look at the player's forecast is broken in archive mode.
                if (!g_archive_mode) {
                    this.removeFromZone(this.zone.my_forecast_verso, card.id, true, card.age, card.type, card.is_relic);
                }
            }

            // Special code for my score management
            if (card.location_from == "score" && card.owner_from == this.player_id) {
                // Remove the card from my score personal window
                // NOTE: The button to look at the player's score pile is broken in archive mode.
                if (!g_archive_mode) {
                    this.removeFromZone(this.zone.my_score_verso, card.id, true, card.age, card.type, card.is_relic);
                }
            }

            var zone_from = this.getZone(card.location_from, card.owner_from, card.type, card.age, card.color);
            var zone_to = this.getZone(card.location_to, card.owner_to, card.type, card.age, card.color);
            
            var visible_from = this.getCardTypeInZone(zone_from.HTML_class) == "card" || card.age === null; // Special achievements are considered visible too
            // zone_to is undefined if location_to is "removed" since there isn't actually a removed location for cards
            var visible_to = zone_to && this.getCardTypeInZone(zone_to.HTML_class) == "card" || card.age === null; // Special achievements are considered visible too

            var id_from;
            var id_to;
            if (visible_from) {
                // The card is shown at the start (verso)
                id_from = card.id;
                if (visible_to) {
                    id_to = id_from // verso -> verso
                } else {
                    id_to = null; // verso -> recto: the card is being hidden. A new id must be created for the recto
                }
            } else {
                // The card is hidden at the start (recto)
                var id_from = this.getCardIdFromPosition(zone_from, card.position_from, card.age, card.type, card.is_relic);
                if (visible_to) {
                    id_to = card.id // recto -> verso: the card is being revealed
                }
                else {
                    id_to = id_from; // recto -> recto
                }
            }

            // If this is a special achievement, marked it as unclaimed (if it is not, then this does nothing)
            dojo.query('#special_achievement_summary_' + id_to).removeClass('unclaimed');
            
            // Update BGA score if needed
            if (card.owner_from != 0 && card.location_from == 'achievements') {
                // Decrement player BGA score (all the team in team game)
                var player_team = this.players[card.owner_from].player_team;
                for (var player_id in this.players) {
                    if (this.players[player_id].player_team == player_team) {
                        this.scoreCtrl[player_id].incValue(-1);
                    }
                }
            }            
            if (card.owner_to != 0 && card.location_to == 'achievements') {
                // Increment player BGA score (all the team in team game)
                var player_team = this.players[card.owner_to].player_team;
                for (var player_id in this.players) {
                    if (this.players[player_id].player_team == player_team) {
                        this.scoreCtrl[player_id].incValue(1);
                    }
                }
            }
            
            // Update counters for score and ressource if needed
            
            // 1 player involved
            if(card.new_score !== undefined) {
                this.counter.score[card.player_id].setValue(card.new_score);
            }
            if(card.new_ressource_counts !== undefined) {
                for(var icon=1; icon<=6; icon++) {
                    this.counter.ressource_count[card.player_id][icon].setValue(card.new_ressource_counts[icon]);
                }
            }
            if(card.new_max_age_on_board !== undefined) {
                this.counter.max_age_on_board[card.player_id].setValue(card.new_max_age_on_board);
            }
            // 2 players involved
            if(card.new_score_from !== undefined) {
                this.counter.score[card.owner_from].setValue(card.new_score_from);
            }
            if(card.new_score_to !== undefined) {
                this.counter.score[card.owner_to].setValue(card.new_score_to);
            }
            if(card.new_ressource_counts_from !== undefined) {
                for(var icon=1; icon<=6; icon++) {
                    this.counter.ressource_count[card.owner_from][icon].setValue(card.new_ressource_counts_from[icon]);
                }
            }
            if(card.new_ressource_counts_to !== undefined) {
                for(var icon=1; icon<=6; icon++) {
                    this.counter.ressource_count[card.owner_to][icon].setValue(card.new_ressource_counts_to[icon]);
                }
            }
            if(card.new_max_age_on_board_from !== undefined) {
                this.counter.max_age_on_board[card.owner_from].setValue(card.new_max_age_on_board_from);
            }
            if(card.new_max_age_on_board_to !== undefined) {
                this.counter.max_age_on_board[card.owner_to].setValue(card.new_max_age_on_board_to);
            }
            if(card.monument_counters !== undefined && card.monument_counters[this.player_id] !== undefined) {
                this.number_of_tucked_cards = card.monument_counters[this.player_id].number_of_tucked_cards;
                this.number_of_scored_cards = card.monument_counters[this.player_id].number_of_scored_cards;
            }

            // Handle case where card is being removed from the game.
            if (!zone_to) {
                this.removeFromZone(zone_from, id_from, true, card.age, card.type, card.is_relic);
                this.refreshSpecialAchievementProgression();
                return;
            }

            this.moveBetweenZones(zone_from, zone_to, id_from, id_to, card);

            // Special code for my forecast management
            if (card.location_to == "forecast" && card.owner_to == this.player_id) {
                // Add the card to my forecast personal window
                // NOTE: The button to look at the player's forecast is broken in archive mode.
                if (!g_archive_mode) {
                    this.createAndAddToZone(this.zone.my_forecast_verso, card.position_to, card.age, card.type, card.is_relic, card.id, dojo.body(), card);
                }
                visible_to = true;
            }
            
            // Special code for my score management
            if (card.location_to == "score" && card.owner_to == this.player_id) {
                // Add the card to my score personal window
                // NOTE: The button to look at the player's score pile is broken in archive mode.
                if (!g_archive_mode) {
                    this.createAndAddToZone(this.zone.my_score_verso, card.position_to, card.age, card.type, card.is_relic, card.id, dojo.body(), card);
                }
                visible_to = true;
            }
            
            // Add tooltip to card
            if ((visible_to || card.is_relic) && this.canShowCardTooltip(card.id)) {
                card.owner = card.owner_to;
                card['location'] = card.location_to;
                card.position = card.position_to;
                card.splay_direction = card.splay_direction_to;
                this.addTooltipForCard(card);
            } else if (card.location_to == 'achievements' && card.age !== null) {
                var HTML_id = this.getCardHTMLId(card.id, card.age, card.type, card.is_relic, zone_from.HTML_class);
                this.removeTooltip(HTML_id);
                card.owner = card.owner_to;
                card['location'] = card.location_to;
                card.position = card.position_to;
            } else if (card.location_to == 'achievements' && card.age === null) {
                card.owner = card.owner_to;
                card['location'] = card.location_to;
                card.position = card.position_to;
                this.addTooltipForCard(card);
            }

            // Add tooltip to game log
            if (this.canShowCardTooltip(card.id)) {
                this.addCustomTooltipToClass("card_id_" + card.id, this.getTooltipForCard(card.id), "");
            }
            
            // Update special achievements overview with progression towards each achievement
            this.refreshSpecialAchievementProgression();
        },

        notif_logWithCardTooltips: function(notif) {
            // Add tooltips to game log
            for (var i = 0; i < notif.args.card_ids.length; i++) {
                var card_id = notif.args.card_ids[i];
                if (this.canShowCardTooltip(card_id)) {
                    this.addCustomTooltipToClass("card_id_" + card_id, this.getTooltipForCard(card_id), "");
                }
            }
        },
        
        notif_splayedPile: function(notif) {
            var player_id = notif.args.player_id;
            var color = notif.args.color;
            var splay_direction = notif.args.splay_direction;
            var splay_direction_in_clear = notif.args.splay_direction_in_clear;
            var forced_unsplay = notif.args.forced_unsplay;
            var new_score = notif.args.new_score;
            
            // Change the splay mode of the matching zone on board
            this.setSplayMode(this.zone.board[player_id][color], splay_direction);
            
            // Update the splay indicator
            var splay_indicator = 'splay_indicator_' + player_id + '_' + color;
            for(var direction = 0; direction < 4; direction++) {
                if (direction == splay_direction) {
                    dojo.addClass(splay_indicator, 'splay_' + direction);
                }
                else {
                    dojo.removeClass(splay_indicator, 'splay_' + direction);
                }
            }

            // Update the tooltip text if needed
            this.removeTooltip('splay_' + player_id + '_' + color);
            if (splay_direction > 0) {
                this.addCustomTooltip('splay_indicator_' + player_id + '_' + color, dojo.string.substitute(_('This stack is splayed ${direction}.'), {'direction': '<b>' + splay_direction_in_clear + '</b>'}), '')
            }

            // Update the score for that player
            if (new_score !== undefined) {
                this.counter.score[player_id].setValue(new_score);
            }
            
            // Update the ressource counts for that player
            if (splay_direction > 0 || forced_unsplay) {
                for(var icon=1; icon<=6; icon++) {
                    this.counter.ressource_count[player_id][icon].setValue(notif.args.new_ressource_counts[icon]);
                }
            }
            
            // Add or remove the button for splay mode based on still splayed colors
            if (splay_direction == 0) {
                this.number_of_splayed_piles--;
                if (this.number_of_splayed_piles == 0) { // Now there is no more color splayed for any player
                    this.disableButtonForSplayMode();
                }
            }
            else {
                this.number_of_splayed_piles++;
                if (this.number_of_splayed_piles == 1) { // Now there is one color splayed for one player
                    this.enableButtonForSplayMode();
                }
            }

            // Update special achievements overview with progression towards each achievement
            this.refreshSpecialAchievementProgression();
        },
        
        notif_rearrangedPile: function(notif) {
            var player_id = notif.args.player_id;
            this.counter.max_age_on_board[player_id].setValue(notif.args.new_max_age_on_board);
            
            // Apply the permutations if either an opponent rearranged their stack or the game is being
            // replayed (which eliminates a bug where Publications doesn't rearrange cards during replays)
            if (this.player_id != player_id || this.isInReplayMode()) {

                var rearrangement = notif.args.rearrangement;
                var color = rearrangement.color;
                var permutations_done = rearrangement.permutations_done;
                var permuted_zone = this.zone.board[player_id][color];

                for (var i=0; i < permutations_done.length; i++) {
                    var permutation = permutations_done[i];
                    this.publicationSwap(player_id, permuted_zone, permutation.position, permutation.delta);
                }
            }
            
            // Update special achievements overview with progression towards each achievement
            this.refreshSpecialAchievementProgression();
        },
        
        notif_removedHandsBoardsAndScores: function(notif) {
            // NOTE: The button to look at the player's score pile is broken in archive mode.
            if (!g_archive_mode) {
                this.zone.my_score_verso.removeAll();
            }
            for(var player_id in this.players) {
                this.zone.revealed[player_id].removeAll();
                this.zone.hand[player_id].removeAll();
                this.zone.score[player_id].removeAll();
                for(var color=0; color<5; color++) {
                    this.zone.board[player_id][color].removeAll();
                }
            }
            
            // Reset counters
            // Counters for score, number of cards in hand and max age
            for(var player_id in this.players) {
                this.counter.score[player_id].setValue(0);
                this.zone.hand[player_id].counter.setValue(0);
                this.counter.max_age_on_board[player_id].setValue(0);
            }
            
            // Counters for ressources
            for(var player_id in this.players) {
                for(var icon=1; icon<=6; icon++) {
                    this.counter.ressource_count[player_id][icon].setValue(0);
                }
            }
            
            // Unsplay all stacks and update the splay indicator (show nothing bacause there are no more splayed stacks)
            for(var player_id in this.players) {
                for(var color=0; color<5; color++) {
                    this.setSplayMode(this.zone.board[player_id][color], 0)
                    var splay_indicator = 'splay_indicator_' + player_id + '_' + color;
                    dojo.addClass(splay_indicator, 'splay_0');
                    for(var direction = 1; direction < 4; direction++) {
                        dojo.removeClass(splay_indicator, 'splay_' + direction);
                    }
                }
            }
            
            // Disable the button for splay mode
            this.disableButtonForSplayMode();
            this.number_of_splayed_piles = 0;

            // Update special achievements overview with progression towards each achievement
            this.refreshSpecialAchievementProgression();
        },

        notif_removedTopCardsAndHands: function(notif) {
            // Remove cards
            for (var player_id in this.players) {
                this.zone.hand[player_id].removeAll();
            }
            for (var i = 0; i < notif.args.top_cards_to_remove.length; i++) {
                var card = notif.args.top_cards_to_remove[i];
                this.removeFromZone(this.zone.board[card.owner][card.color], card.id, true, card.age, card.type, card.is_relic);
            }
            
            // Update counters
            for (var player_id in this.players) {
                this.zone.hand[player_id].counter.setValue(0);
                this.counter.max_age_on_board[player_id].setValue(notif.args.new_max_age_on_board_by_player[player_id]);
                for (var icon = 1; icon <= 6; icon++) {
                    this.counter.ressource_count[player_id][icon].setValue(notif.args.new_resource_counts_by_player[player_id][icon]);
                }
            }

            // Update special achievements overview with progression towards each achievement
            this.refreshSpecialAchievementProgression();
        },

        notif_removedPlayer: function(notif) {
            var player_id = notif.args.player_to_remove;
            // NOTE: The button to look at the player's forecast is broken in archive mode.
            if (!g_archive_mode) {
                this.zone.my_forecast_verso.removeAll();
            }
            // NOTE: The button to look at the player's score pile is broken in archive mode.
            if (!g_archive_mode) {
                this.zone.my_score_verso.removeAll();
            }
            this.zone.revealed[player_id].removeAll();
            this.zone.hand[player_id].removeAll();
            this.zone.forecast[player_id].removeAll();
            this.zone.score[player_id].removeAll();
            this.zone.achievements[player_id].removeAll();
            this.zone.display[player_id].removeAll();
            for (var color = 0; color < 5; color++) {
                this.zone.board[player_id][color].removeAll();
            }

            this.scoreCtrl[player_id].setValue(0);
            this.counter.score[player_id].setValue(0);
            this.zone.hand[player_id].counter.setValue(0);
            this.counter.max_age_on_board[player_id].setValue(0);
            for (var icon = 1; icon <= 6; icon++) {
                this.counter.ressource_count[player_id][icon].setValue(0);
            }

            dojo.byId('player_' + player_id).style.display = 'none';
        },

        notif_updateResourcesForArtifactOnDisplay: function(notif) {
            this.updateResourcesForArtifactOnDisplay(notif.args.player_id, notif.args.resource_icon, notif.args.resource_count_delta);
        },

        updateResourcesForArtifactOnDisplay: function(player_id, resource_icon, resource_count_delta) {
            if (resource_count_delta != 0) {
                previous_value = this.counter.ressource_count[player_id][resource_icon].getValue();
                this.counter.ressource_count[player_id][resource_icon].setValue(previous_value + resource_count_delta);
            }

            // If icon count is increasing, then this is the start of the free action
            if (resource_count_delta > 0) {
                for (var icon = 1; icon <= 6; icon++) {
                    opacity = icon == resource_icon ? 1 : 0.5;
                    dojo.query(".player_info .ressource_" + icon).style("opacity", opacity);
                }

            // If icon count is decreasing, then this is the end of the free action
            } else {
                for (var icon = 1; icon <= 6; icon++) {
                    dojo.query(".player_info .ressource_" + icon).style("opacity", 1);
                }
            }
        },

        notif_resetMonumentCounters: function(notif) {
            this.number_of_scored_cards = 0;
            this.number_of_tucked_cards = 0;
            this.refreshSpecialAchievementProgression();
        },
        
        notif_log: function(notif) {
            // No change on the interface
            return;
        },
        
        /*
         * This special notification is the only one spectators can subscribe to.
         * They redirect to normal notification adressed to players which are not involved by the current action.
         * 
         */
        
        notif_transferedCard_spectator: function(notif) {
            // Put the message for the spectator in log
            this.log_for_spectator(notif);
            
            // Call normal notif
            this.notif_transferedCard(notif);
        },

        notif_logWithCardTooltips_spectator: function(notif) {
            // Put the message for the spectator in log
            this.log_for_spectator(notif);
            
            // Call normal notif
            this.notif_logWithCardTooltips(notif);
        },

        notif_splayedPile_spectator: function(notif) {
            // Put the message for the spectator in log
            this.log_for_spectator(notif);
            
            // Call normal notif
            this.notif_splayedPile(notif);
        },
        
        notif_rearrangedPile_spectator: function(notif) {
            // Put the message for the spectator in log
            this.log_for_spectator(notif);
            
            // Call normal notif
            this.notif_rearrangedPile(notif);
        },
        
        notif_removedHandsBoardsAndScores_spectator: function(notif) {
            // Put the message for the spectator in log
            this.log_for_spectator(notif);
            
            // Call normal notif
            this.notif_removedHandsBoardsAndScores(notif);
        },

        notif_removedTopCardsAndHands_spectator: function(notif) {
            // Put the message for the spectator in log
            this.log_for_spectator(notif);
            
            // Call normal notif
            this.notif_removedTopCardsAndHands(notif);
        },

        notif_removedPlayer_spectator: function(notif) {
            // Put the message for the spectator in log
            this.log_for_spectator(notif);
            
            // Call normal notif
            this.notif_removedPlayer(notif);
        },

        notif_updateResourcesForArtifactOnDisplay_spectator: function(notif) {
            // Put the message for the spectator in log
            this.log_for_spectator(notif);
            
            // Call normal notif
            this.notif_updateResourcesForArtifactOnDisplay(notif);
        },

        notif_resetMonumentCounters_spectator: function(notif) {
            // Put the message for the spectator in log
            this.log_for_spectator(notif);
            
            // Call normal notif
            this.notif_resetMonumentCounters(notif);
        },
        
        notif_log_spectator: function(notif) {
            // Put the message for the spectator in log
            this.log_for_spectator(notif);
            
            // Call normal notif
            this.notif_log(notif);
        },        
        
        log_for_spectator: function(notif) {
            notif.args = this.notifqueue.playerNameFilterGame(notif.args);
            notif.args.log = this.format_string_recursive(notif.args.log, notif.args); // Enable translation
            var log = "<div class='log' style='height: auto; display: block; color: rgb(0, 0, 0);'><div class='roundedbox'>" + notif.args.log + "</div></div>"
            dojo.place(log, $('logs'), 'first');
        },
        
        /* This enable to inject translatable styled things to logs or action bar */
        /* @Override */
        format_string_recursive : function(log, args) {
            try {
                if (log && args && !args.processed) {
                    args.processed = true;
                    
                    if (!this.isSpectator) {
                        args.You = this.getColoredText(_('You')); // will replace ${You} with colored version
                        args.you = this.getColoredText(_('you')); // will replace ${you} with colored version
                        args.Your = this.getColoredText(_('Your')); // will replace ${Your} with colored version
                        args.your = this.getColoredText(_('your')); // will replace ${your} with colored version
                        args.player_name_as_you = this.getColoredText(_('You'));
                    }
                    
                    if (typeof args.card_name == 'string') {
                         args.card_name = this.getCardChain(args);
                    }
                    if (this.player_id == args.opponent_id) { // Is that player the opponent?
                        args.message_for_others = args.message_for_opponent;
                    }
                }
            } catch (e) {
                console.error(log,args,"Exception thrown", e.stack);
            }
            return this.inherited(arguments);
        },

        /* Implementation of proper colored You with background in case of white or light colors  */

        getColoredText : function(translatable_text, player_id = this.player_id) {
            var color = this.gamedatas.players[player_id].color;
            return "<span style='font-weight:bold;color:#" + color + "'>" + translatable_text + "</span>";
        },
        
        getCardChain : function(args) {
            var cards = [];
            var i = 0;
            while (true) {
                if (typeof args['card_'+i] != 'string') {
                    break;
                }
                cards.push(this.getColoredText(_(args['card_'+i]), args['ref_player_'+i]));
                i++;
            }
            var arrow = '&rarr;';
            return cards.join(arrow);
        },

        canShowCardTooltip : function(card_id) {
            return card_id !== undefined &&
                this.cards[card_id].age !== null &&
                (card_id != 215 || this.cities_expansion_enabled) &&
                (card_id != 218 || this.figures_expansion_enabled) &&
                (card_id != 219 || this.echoes_expansion_enabled);
        },

        // Returns true if the current player is a spectator or if the game is currently in replay mode
        isReadOnly() {
            return this.isSpectator || this.isInReplayMode();
        },

        // Returns true if the game is ongoing but the user clicked "replay from this move" in the log or the game is in archive mode after the game has ended
        isInReplayMode : function () {
            return typeof g_replayFrom != 'undefined' || g_archive_mode;
        }
   });
});
