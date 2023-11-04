function parseCard(card) {
    var _a, _b, _c, _d, _e, _f, _g, _h;
    return {
        id: parseInt(card.id),
        type: parseInt(card.type),
        age: parseInt(card.age),
        faceup_age: parseInt(card.faceup_age),
        color: parseInt(card.color),
        spot_1: parseInt(card.spot_1),
        spot_2: parseInt(card.spot_2),
        spot_3: parseInt(card.spot_3),
        spot_4: parseInt(card.spot_4),
        spot_5: card.spot_5 ? parseInt(card.spot_5) : null,
        spot_6: card.spot_6 ? parseInt(card.spot_6) : null,
        dogma_icon: parseInt(card.dogma_icon),
        is_relic: card.is_relic,
        name: card.name,
        condition_for_claiming: (_a = card.condition_for_claiming) !== null && _a !== void 0 ? _a : null,
        alternative_condition_for_claiming: (_b = card.alternative_condition_for_claiming) !== null && _b !== void 0 ? _b : null,
        echo_effect: (_c = card.echo_effect) !== null && _c !== void 0 ? _c : null,
        i_demand_effect: (_d = card.i_demand_effect) !== null && _d !== void 0 ? _d : null,
        i_compel_effect: (_e = card.i_compel_effect) !== null && _e !== void 0 ? _e : null,
        non_demand_effect_1: (_f = card.non_demand_effect_1) !== null && _f !== void 0 ? _f : null,
        non_demand_effect_2: (_g = card.non_demand_effect_2) !== null && _g !== void 0 ? _g : null,
        non_demand_effect_3: (_h = card.non_demand_effect_3) !== null && _h !== void 0 ? _h : null
    };
}
function getHiddenIconsWhenSplayed(card, direction) {
    var icons = [];
    switch (direction) {
        case 1: // left
            icons = [card.spot_1, card.spot_2, card.spot_3, card.spot_6];
            break;
        case 2: // right
            icons = [card.spot_3, card.spot_4, card.spot_5, card.spot_6];
            break;
        case 3: // up
            icons = [card.spot_1, card.spot_5, card.spot_6];
            break;
        case 4: // aslant
            icons = [card.spot_5, card.spot_6];
            break;
        default: // unsplayed
            icons = getAllIcons(card);
            break;
    }
    return icons.filter(function (icon) { return icon !== null; });
}
function getVisibleIconsWhenSplayed(card, direction) {
    var icons = [];
    switch (direction) {
        case 1: // left
            icons = [card.spot_4, card.spot_5];
            break;
        case 2: // right
            icons = [card.spot_1, card.spot_2];
            break;
        case 3: // up
            icons = [card.spot_2, card.spot_3, card.spot_4];
            break;
        case 4: // aslant
            icons = [card.spot_1, card.spot_2, card.spot_3, card.spot_4];
            break;
        default: // unsplayed
            return [];
    }
    return icons.filter(function (icon) { return icon !== null; });
}
function getAllIcons(card) {
    return [card.spot_1, card.spot_2, card.spot_3, card.spot_4, card.spot_5, card.spot_6].filter(function (icon) { return icon !== null; });
}
function getBonusIconValues(icons) {
    var bonus_values = [];
    icons.forEach(function (icon) {
        bonus_values.push(getBonusIconValue(icon));
    });
    return bonus_values;
}
function getBonusIconValue(icon) {
    if (icon > 100 && icon <= 112) {
        return icon - 100;
    }
    return 0;
}
function countMatchingIcons(icons, iconToMatch) {
    var count = 0;
    icons.forEach(function (icon) {
        if (icon == iconToMatch) {
            count++;
        }
    });
    return count;
}
function isFlag(card_id) {
    return 1000 <= card_id && card_id <= 1099;
}
function isFountain(card_id) {
    return 1100 <= card_id && card_id <= 1199;
}
function isMuseum(card_id) {
    return 1200 <= card_id && card_id <= 1204;
}
/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * Innovation implementation : © Jean Portemer <jportemer@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * innovation.js/innovation.ts
 *
 * Innovation user interface script
 *
 * In this file, you are describing the logic of your user interface, in JavaScript/TypeScript language.
 *
 */
var __extends = (this && this.__extends) || (function () {
    var extendStatics = function (d, b) {
        extendStatics = Object.setPrototypeOf ||
            ({ __proto__: [] } instanceof Array && function (d, b) { d.__proto__ = b; }) ||
            function (d, b) { for (var p in b) if (Object.prototype.hasOwnProperty.call(b, p)) d[p] = b[p]; };
        return extendStatics(d, b);
    };
    return function (d, b) {
        if (typeof b !== "function" && b !== null)
            throw new TypeError("Class extends value " + String(b) + " is not a constructor or null");
        extendStatics(d, b);
        function __() { this.constructor = d; }
        d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
    };
})();
// @ts-ignore
BgaGame = /** @class */ (function () {
    function BgaGame() { }
    return BgaGame;
})();
var Innovation = /** @class */ (function (_super) {
    __extends(Innovation, _super);
    function Innovation() {
        var _this = _super.call(this) || this;
        _this.cards = [];
        // Global variables of your user interface
        _this.zone = {};
        _this.counter = {};
        _this.card_dimensions = {
            "S recto": { "width": 33, "height": 47 },
            "S card": { "width": 47, "height": 33 },
            "M card": { "width": 182, "height": 126 },
            "L recto": { "width": 316, "height": 456 },
            "L card": { "width": 456, "height": 316 },
        };
        _this.my_hand_padding = 5; // Must be consistent to what is declared in CSS
        _this.overlap_for_unsplayed = 3;
        _this.compact_overlap_for_splay = 3;
        _this.expanded_overlap_for_splay = 52;
        _this.HTML_class = new Map([
            ["my_hand", "M card"],
            ["opponent_hand", "S recto"],
            ["display", "M card"],
            ["museums", "M card"],
            ["deck", "S recto"],
            ["board", "M card"],
            ["forecast", "S recto"],
            ["my_forecast_verso", "M card"],
            ["score", "S recto"],
            ["my_score_verso", "M card"],
            ["safe", "S recto"],
            ["revealed", "M card"],
            ["relics", "S recto"],
            ["achievements", "S recto"],
            ["special_achievements", "S card"],
            ["available_museums", "S card"],
            ["junk", "S recto"],
        ]);
        _this.num_cards_in_row = new Map([
            ["my_hand", -1],
            ["opponent_hand", -1],
            ["display", 1],
            ["museums", -1],
            ["deck", 15],
            ["board", -1],
            ["forecast", -1],
            ["my_forecast_verso", 3],
            ["score", -1],
            ["my_score_verso", 3],
            ["safe", -1],
            ["revealed", 1],
            ["relics", -1],
            ["achievements", -1],
            ["special_achievements", -1],
            ["available_museums", 5],
            ["junk", 1], // TODO(4E): Compute this dynamically
        ]);
        _this.delta = {
            "my_hand": { "x": 189, "y": 133 },
            "opponent_hand": { "x": 35, "y": 49 },
            "display": { "x": 189, "y": 133 },
            "museums": { "x": 189, "y": 133 },
            "deck": { "x": 3, "y": 3 },
            "board": { "x": 0, "y": 0 },
            "forecast": { "x": 35, "y": 49 },
            "my_forecast_verso": { "x": 189, "y": 133 },
            "score": { "x": 35, "y": 49 },
            "my_score_verso": { "x": 189, "y": 133 },
            "safe": { "x": 35, "y": 49 },
            "revealed": { "x": 189, "y": 133 },
            "achievements": { "x": 35, "y": 49 },
            "available_museums": { "x": 35, "y": 49 },
            "junk": { "x": 35, "y": 49 }, // + 2
        };
        _this.incremental_id = 0;
        _this.selected_card = null;
        _this.display_mode = true;
        _this.view_full = false;
        _this.card_browsing_window = null;
        _this.special_achievement_selection_window = null;
        _this.my_score_verso_window = null;
        _this.my_forecast_verso_window = null;
        _this.text_for_expanded_mode = '';
        _this.text_for_compact_mode = '';
        _this.text_for_view_normal = '';
        _this.text_for_view_full = '';
        // Counters used to track progress of the Monument special achievement
        _this.number_of_tucked_cards = 0;
        _this.number_of_scored_cards = 0;
        _this.arrows_for_expanded_mode = "&gt;&gt; &lt;&lt;"; // >> <<
        _this.arrows_for_compact_mode = "&lt;&lt; &gt;&gt;"; // << >>
        _this.number_of_splayed_piles = 0;
        _this.players = [];
        _this.saved_HTML_cards = {};
        _this.initializing = true;
        // Special flags used for Publication (3rd edition and earlier)
        _this.publication_permuted_zone = null;
        _this.publication_permutations_done = null;
        _this.publication_original_items = null;
        // Special flag used when a selection has to be made within a stack
        _this.color_pile = null;
        // Special flags to indicate that multiple colors must be chosen
        _this.choose_two_colors = false;
        _this.choose_three_colors = false;
        _this.first_chosen_color = null;
        _this.second_chosen_color = null;
        // Special flag used by Mona Lisa
        _this.choose_integer = false;
        // System to remember what node where last offed and what was their handlers to restore if needed
        _this.deactivated_cards = new dojo.NodeList();
        _this.deactivated_cards_mid_dogma = new dojo.NodeList();
        _this.deactivated_cards_can_endorse = new dojo.NodeList();
        _this.erased_pagemaintitle_text = '';
        _this.num_sets_in_play = 1;
        _this._actionTimerLabel = '';
        _this._actionTimerSeconds = 0;
        _this._callback = function (val) { };
        _this._callbackParam = null;
        _this._actionTimerFunction = function () { };
        _this._actionTimerId = undefined;
        _this.isLoadingComplete = false;
        console.log('innovation constructor');
        return _this;
    }
    Innovation.prototype.debugTransfer = function (action) {
        var debug_card_list = this.getDebugCardList();
        this.ajaxcall("/innovation/innovation/debug_transfer.html", {
            lock: true,
            card_id: debug_card_list.value,
            transfer_action: action,
        }, this, function (result) { }, function (is_error) { });
    };
    Innovation.prototype.debugTransferAll = function (location_from, location_to) {
        this.ajaxcall("/innovation/innovation/debug_transfer_all.html", {
            lock: true,
            location_from: location_from,
            location_to: location_to,
        }, this, function (result) { }, function (is_error) { });
    };
    Innovation.prototype.getDebugCardList = function () {
        return document.getElementById("debug_card_list");
    };
    Innovation.prototype.debugSplay = function (direction) {
        var debug_color_list = this.getDebugColorList();
        this.ajaxcall("/innovation/innovation/debug_splay.html", {
            lock: true,
            color: debug_color_list.value,
            direction: direction,
        }, this, function (result) { }, function (is_error) { });
    };
    Innovation.prototype.getDebugColorList = function () {
        return document.getElementById("debug_color_list");
    };
    /*
        setup:
        
        This method must set up the game user interface according to current game situation specified
        in parameters.
        
        The method is called each time the game interface is displayed to a player, ie:
        _ when the game starts
        _ when a player refreshes the game page (F5)
        
        "gamedatas" argument contains all datas retrieved by your "getAllDatas" PHP method.
    */
    Innovation.prototype.setup = function (gamedatas) {
        var _this = this;
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
                    + "<button id='debug_splay_aslant' class='action-button debug_button bgabutton bgabutton_red'>SPLAY ASLANT</button>"
                    + main_area.innerHTML;
            if (gamedatas.echoes_expansion_enabled) {
                main_area.innerHTML = "<button id='debug_empty_forecast' class='action-button debug_button bgabutton bgabutton_red'>EMPTY FORECAST</button>" + main_area.innerHTML;
                main_area.innerHTML = "<button id='debug_foreshadow' class='action-button debug_button bgabutton bgabutton_red'>FORESHADOW</button>" + main_area.innerHTML;
            }
            if (gamedatas.artifacts_expansion_enabled) {
                main_area.innerHTML = "<button id='debug_dig' class='action-button debug_button bgabutton bgabutton_red'>DIG</button>" + main_area.innerHTML;
            }
            if (gamedatas.unseen_expansion_enabled) {
                main_area.innerHTML = "<button id='debug_empty_safe' class='action-button debug_button bgabutton bgabutton_red'>EMPTY SAFE</button>" + main_area.innerHTML;
                main_area.innerHTML = "<button id='debug_safeguard' class='action-button debug_button bgabutton bgabutton_red'>SAFEGUARD</button>" + main_area.innerHTML;
            }
            main_area.innerHTML =
                "<input type=\"text\" id=\"search_card_list\" placeholder=\"Search cards...\"></br><select id='debug_card_list'></select>"
                    + "<button id='debug_draw' class='action-button debug_button bgabutton bgabutton_red'>DRAW</button>"
                    + "<button id='debug_meld' class='action-button debug_button bgabutton bgabutton_red'>MELD</button>"
                    + "<button id='debug_tuck' class='action-button debug_button bgabutton bgabutton_red'>TUCK</button>"
                    + "<button id='debug_score' class='action-button debug_button bgabutton bgabutton_red'>SCORE</button>"
                    + "<button id='debug_achieve' class='action-button debug_button bgabutton bgabutton_red'>ACHIEVE</button>"
                    + "<button id='debug_return' class='action-button debug_button bgabutton bgabutton_red'>RETURN</button>"
                    + "<button id='debug_topdeck' class='action-button debug_button bgabutton bgabutton_red'>TOPDECK</button>"
                    + "<button id='debug_junk' class='action-button debug_button bgabutton bgabutton_red'>JUNK</button>"
                    + main_area.innerHTML;
            // Populate dropdown lists
            for (var i = 0; i < Object.keys(gamedatas.cards).length; i++) {
                var key = Object.keys(gamedatas.cards)[i];
                var card = gamedatas.cards[key];
                // NOTE: The colors do not need to be translated because they only appear in the Studio anyway.
                var color = card.color == 0 ? "blue" : card.color == 1 ? "red" : card.color == 2 ? "green" : card.color == 3 ? "yellow" : "purple";
                if (isFountain(card.id)) {
                    $('debug_card_list').innerHTML += "<option value='".concat(card.id, "'> ").concat(card.id, " - Fountain (").concat(color, ")</option>");
                }
                else if (isFlag(card.id)) {
                    $('debug_card_list').innerHTML += "<option value='".concat(card.id, "'> ").concat(card.id, " - Flag (").concat(color, ")</option>");
                }
                else {
                    $('debug_card_list').innerHTML += "<option value='".concat(card.id, "'> ").concat(card.id, " - ").concat(card.name, " (Age ").concat(card.age, ")</option>");
                }
            }
            $('debug_color_list').innerHTML += "<option value='0'>Blue</option>";
            $('debug_color_list').innerHTML += "<option value='1'>Red</option>";
            $('debug_color_list').innerHTML += "<option value='2'>Green</option>";
            $('debug_color_list').innerHTML += "<option value='3'>Yellow</option>";
            $('debug_color_list').innerHTML += "<option value='4'>Purple</option>";
            // Trigger events when buttons are clicked
            dojo.connect($('debug_draw'), 'onclick', function (_) { return _this.debugTransfer("draw"); });
            dojo.connect($('debug_meld'), 'onclick', function (_) { return _this.debugTransfer("meld"); });
            dojo.connect($('debug_tuck'), 'onclick', function (_) { return _this.debugTransfer("tuck"); });
            dojo.connect($('debug_score'), 'onclick', function (_) { return _this.debugTransfer("score"); });
            dojo.connect($('debug_achieve'), 'onclick', function (_) { return _this.debugTransfer("achieve"); });
            dojo.connect($('debug_return'), 'onclick', function (_) { return _this.debugTransfer("return"); });
            dojo.connect($('debug_topdeck'), 'onclick', function (_) { return _this.debugTransfer("topdeck"); });
            if (gamedatas.artifacts_expansion_enabled) {
                dojo.connect($('debug_dig'), 'onclick', function (_) { return _this.debugTransfer("dig"); });
            }
            if (gamedatas.echoes_expansion_enabled) {
                dojo.connect($('debug_foreshadow'), 'onclick', function (_) { return _this.debugTransfer("foreshadow"); });
                dojo.connect($('debug_empty_forecast'), 'onclick', function (_) { return _this.debugTransferAll("forecast", "deck"); });
            }
            if (gamedatas.fourth_edition) {
                dojo.connect($('debug_junk'), 'onclick', function (_) { return _this.debugTransfer("junk"); });
            }
            if (gamedatas.unseen_expansion_enabled) {
                dojo.connect($('debug_safeguard'), 'onclick', function (_) { return _this.debugTransfer("safeguard"); });
                dojo.connect($('debug_empty_safe'), 'onclick', function (_) { return _this.debugTransferAll("safe", "deck"); });
            }
            dojo.connect($('debug_unsplay'), 'onclick', function (_) { return _this.debugSplay(0); });
            dojo.connect($('debug_splay_left'), 'onclick', function (_) { return _this.debugSplay(1); });
            dojo.connect($('debug_splay_right'), 'onclick', function (_) { return _this.debugSplay(2); });
            dojo.connect($('debug_splay_up'), 'onclick', function (_) { return _this.debugSplay(3); });
            dojo.connect($('debug_splay_aslant'), 'onclick', function (_) { return _this.debugSplay(4); });
            // Make drop-down searchable
            document.getElementById("search_card_list").addEventListener("input", function () {
                var input = this;
                var filter = input.value.toUpperCase();
                var select = document.getElementById("debug_card_list");
                var options = select.getElementsByTagName("option");
                for (var i = 0; i < options.length; i++) {
                    var option = options[i];
                    if (option.textContent.toUpperCase().indexOf(filter) > -1) {
                        option.style.display = "";
                    }
                    else {
                        option.style.display = "none";
                    }
                }
            });
        }
        //******
        this.card_browsing_window = new dijit.Dialog({ 'title': _("Browse All Cards") });
        this.special_achievement_selection_window = new dijit.Dialog({ 'title': _("Special Achievements") });
        this.my_score_verso_window = new dijit.Dialog({ 'title': _("Cards in your score pile (opponents cannot see this)") });
        this.my_forecast_verso_window = new dijit.Dialog({ 'title': _("Cards in your forecast (opponents cannot see this)") });
        this.text_for_expanded_mode = _("Show compact");
        this.text_for_compact_mode = _("Show expanded");
        this.text_for_view_normal = _("Look at all cards in piles");
        this.text_for_view_full = _("Resume normal view");
        // GENERAL INFO
        this.cards = [];
        var self = this;
        Object.keys(gamedatas.cards).forEach(function (id) {
            self.cards[id] = parseCard(gamedatas.cards[id]);
        });
        this.players = gamedatas.players;
        // PLAYER PANELS
        for (var player_id in this.players) {
            dojo.place("<span class='achievements_to_win'>/".concat(this.gamedatas.number_of_achievements_needed_to_win, "<span>"), $('player_score_' + player_id), "after");
            dojo.place(this.format_block('jstpl_player_panel', { 'player_id': player_id }), $('player_board_' + player_id));
            for (var icon = 1; icon <= 7; icon++) {
                var infos = { 'player_id': player_id, 'icon': icon };
                dojo.place(this.format_block('jstpl_ressource_icon', infos), $('symbols_' + player_id));
                dojo.place(this.format_block('jstpl_ressource_count', infos), $('ressource_counts_' + player_id));
            }
            if (!this.gamedatas.fourth_edition) {
                dojo.style("ressource_icon_".concat(player_id, "_7"), 'display', 'none');
                dojo.style("ressource_count_".concat(player_id, "_7"), 'display', 'none');
            }
        }
        this.addCustomTooltipToClass("score_count", _("Score"), "");
        this.addCustomTooltipToClass("hand_count", _("Number of cards in hand"), "");
        this.addCustomTooltipToClass("max_age_on_board", _("Max age on board top cards"), "");
        this.addCustomTooltipToClass("forecast_count", _("Number of cards in forecast"), "");
        for (var icon = 1; icon <= 7; icon++) {
            this.addCustomTooltipToClass("ressource_" + icon, _("Number of visible ${icons} on the board").replace('${icons}', this.square('P', 'icon', icon, 'in_tooltip')), "");
        }
        // Counters for score
        this.counter["score"] = {};
        for (var player_id in this.players) {
            this.counter["score"][player_id] = new ebg.counter();
            this.counter["score"][player_id].create($("score_count_" + player_id));
            this.counter["score"][player_id].setValue(gamedatas.score[player_id]);
        }
        // Counters for max age on board
        this.counter["max_age_on_board"] = {};
        for (var player_id in this.players) {
            this.counter["max_age_on_board"][player_id] = new ebg.counter();
            this.counter["max_age_on_board"][player_id].create($("max_age_on_board_" + player_id));
            this.counter["max_age_on_board"][player_id].setValue(gamedatas.max_age_on_board[player_id]);
        }
        // Counters for ressources
        this.counter["resource_count"] = {};
        for (var player_id in this.players) {
            this.counter["resource_count"][player_id] = {};
            for (var icon = 1; icon <= 7; icon++) {
                this.counter["resource_count"][player_id][icon] = new ebg.counter();
                this.counter["resource_count"][player_id][icon].create($("ressource_count_" + player_id + "_" + icon));
                this.counter["resource_count"][player_id][icon].setValue(gamedatas.ressource_counts[player_id][icon]);
            }
        }
        if (gamedatas.artifact_on_display_icons != null && gamedatas.artifact_on_display_icons.resource_icon != null) {
            this.updateResourcesForArtifactOnDisplay(gamedatas.active_player, gamedatas.artifact_on_display_icons.resource_icon, gamedatas.artifact_on_display_icons.resource_count_delta);
        }
        // Action indicator
        for (var player_id in this.players) {
            dojo.place("<div id='action_indicator_" + player_id + "' class='action_indicator'></div>", $('ressources_' + player_id), 'after');
        }
        if (gamedatas.active_player !== null) {
            this.givePlayerActionCard(gamedatas.active_player, gamedatas.action_number);
        }
        if (this.gamedatas.artifacts_expansion_enabled) {
            this.num_sets_in_play++;
        }
        if (this.gamedatas.cities_expansion_enabled) {
            this.num_sets_in_play++;
        }
        if (this.gamedatas.echoes_expansion_enabled) {
            this.num_sets_in_play++;
        }
        if (this.gamedatas.figures_expansion_enabled) {
            this.num_sets_in_play++;
        }
        if (this.gamedatas.unseen_expansion_enabled) {
            this.num_sets_in_play++;
        }
        if (this.num_sets_in_play > 2) {
            this.delta.deck = { "x": 0.25, "y": 0.25 }; // overlap
        }
        // DECKS
        this.zone["deck"] = {};
        for (var type = 0; type <= 5; type++) {
            this.zone["deck"][type] = {};
            for (var age = 1; age <= 11; age++) {
                if (age == 11 && !this.gamedatas.fourth_edition) {
                    dojo.style("deck_pile_".concat(type, "_11"), 'display', 'none');
                    continue;
                }
                // Creation of the zone
                this.zone["deck"][type][age] = this.createZone('deck', 0, type, age, null, /*grouped_by_age_type_and_is_relic=*/ false, /*counter_method=*/ "COUNT", /*counter_display_zero=*/ false);
                this.setPlacementRules(this.zone["deck"][type][age], /*left_to_right=*/ true);
                // Add cards to zone according to the current situation
                var num_cards = gamedatas.deck_counts[type][age];
                for (var i = 0; i < num_cards; i++) {
                    this.createAndAddToZone(this.zone["deck"][type][age], i, age, type, /*is_relic=*/ 0, null, dojo.body(), null);
                }
                // TODO(FIGURES): Handle the case where there are 5 sets.
                if (this.num_sets_in_play == 3) {
                    dojo.addClass("deck_count_".concat(type, "_").concat(age), 'three_sets');
                    dojo.addClass("deck_pile_".concat(type, "_").concat(age), 'three_sets');
                }
                else if (this.num_sets_in_play == 4) {
                    dojo.addClass("deck_count_".concat(type, "_").concat(age), 'four_sets');
                    dojo.addClass("deck_pile_".concat(type, "_").concat(age), 'four_sets');
                }
                else if (this.num_sets_in_play == 5) {
                    dojo.addClass("deck_count_".concat(type, "_").concat(age), 'five_sets');
                    dojo.addClass("deck_pile_".concat(type, "_").concat(age), 'five_sets');
                }
            }
        }
        if (!gamedatas.artifacts_expansion_enabled) {
            dojo.byId('deck_set_2_1').style.display = 'none';
            dojo.byId('deck_set_2_2').style.display = 'none';
        }
        if (!gamedatas.cities_expansion_enabled) {
            dojo.byId('deck_set_3_1').style.display = 'none';
            dojo.byId('deck_set_3_2').style.display = 'none';
        }
        if (!gamedatas.echoes_expansion_enabled) {
            dojo.byId('deck_set_4_1').style.display = 'none';
            dojo.byId('deck_set_4_2').style.display = 'none';
        }
        if (!gamedatas.figures_expansion_enabled) {
            dojo.byId('deck_set_5_1').style.display = 'none';
            dojo.byId('deck_set_5_2').style.display = 'none';
        }
        if (!gamedatas.unseen_expansion_enabled) {
            dojo.byId('deck_set_6_1').style.display = 'none';
            dojo.byId('deck_set_6_2').style.display = 'none';
        }
        // AVAILABLE RELICS
        this.zone["relics"] = {};
        this.zone["relics"]["0"] = this.createZone('relics', 0, null, null, null, /*grouped_by_age_type_and_is_relic=*/ true);
        this.setPlacementRulesForRelics();
        if (gamedatas.relics_enabled) {
            for (var i = 0; i < gamedatas.unclaimed_relics.length; i++) {
                var relic = gamedatas.unclaimed_relics[i];
                this.createAndAddToZone(this.zone["relics"]["0"], i, relic.age, relic.type, relic.is_relic, null, dojo.body(), null);
                if (this.canShowCardTooltip(relic['id'])) {
                    this.addTooltipForCard(relic);
                }
            }
        }
        else {
            dojo.byId('available_relics_container').style.display = 'none';
        }
        // AVAILABLE MUSEUMS
        this.zone["available_museums"] = {};
        this.zone["available_museums"]["0"] = this.createZone('available_museums', 0, null, null, null, /*grouped_by_age_type_and_is_relic=*/ false);
        this.setPlacementRulesForAvailableMuseums();
        if (gamedatas.artifacts_expansion_enabled && gamedatas.fourth_edition) {
            for (var i = 0; i < gamedatas.unclaimed_museums.length; i++) {
                var museum = gamedatas.unclaimed_museums[i];
                this.createAndAddToZone(this.zone["available_museums"]["0"], i, museum.age, museum.type, museum.is_relic, museum.id, dojo.body(), museum);
                if (this.canShowCardTooltip(museum['id'])) {
                    this.addTooltipForCard(museum);
                }
            }
        }
        else {
            dojo.byId('available_museums_container').style.display = 'none';
        }
        // AVAILABLE ACHIEVEMENTS
        // Creation of the zone
        this.zone["achievements"] = {};
        // Add cards to zone according to the current situation
        if (gamedatas.unclaimed_standard_achievement_counts !== null) {
            this.zone["achievements"]["0"] = this.createZone('achievements', 0, null, null, null, /*grouped_by_age_type_and_is_relic=*/ true);
            this.setPlacementRulesForAchievements();
            for (var type = 0; type <= 5; type++) {
                for (var is_relic = 0; is_relic <= 1; is_relic++) {
                    for (var age = 1; age <= 11; age++) {
                        var num_cards = gamedatas.unclaimed_standard_achievement_counts[type][is_relic][age];
                        for (var i = 0; i < num_cards; i++) {
                            this.createAndAddToZone(this.zone["achievements"]["0"], i, age, type, is_relic, null, dojo.body(), null);
                            if (!this.isSpectator) {
                                // Construct card object so that we can add a tooltip to the achievement
                                // TODO(LATER): Simplify addTooltipForStandardAchievement once the other callsite is removed.
                                var achievement = { 'location': 'achievements', 'owner': 0, 'type': type, 'age': age, 'is_relic': is_relic };
                                this.addTooltipForStandardAchievement(achievement);
                            }
                        }
                    }
                }
            }
        }
        else {
            // TODO(LATER): Remove this once it is safe to do so.
            this.zone["achievements"]["0"] = this.createZone('achievements', 0);
            this.setPlacementRulesForAchievements();
            for (var i = 0; i < gamedatas.unclaimed_achievements.length; i++) {
                var achievement = gamedatas.unclaimed_achievements[i];
                if (achievement.age === null) {
                    continue;
                }
                this.createAndAddToZone(this.zone["achievements"]["0"], i, achievement.age, achievement.type, achievement.is_relic, null, dojo.body(), null);
                if (!this.isSpectator) {
                    this.addTooltipForStandardAchievement(achievement);
                }
            }
        }
        // AVAILABLE SPECIAL ACHIEVEMENTS
        // Creation of the zone
        this.zone["special_achievements"] = {};
        this.zone["special_achievements"]["0"] = this.createZone('special_achievements', 0);
        this.setPlacementRulesForSpecialAchievements();
        // Add cards to zone according to the current situation
        for (var i = 0; i < gamedatas.unclaimed_achievements.length; i++) {
            var achievement = gamedatas.unclaimed_achievements[i];
            if (achievement.age !== null) {
                continue;
            }
            this.createAndAddToZone(this.zone["special_achievements"]["0"], i, null, achievement.type, achievement.is_relic, achievement.id, dojo.body(), null);
            this.addTooltipForCard(achievement);
        }
        // Add another button here to open up the special achievements popup
        var browse_special_achievements_button = this.format_string_recursive("<i id='browse_special_achievements_button' class='bgabutton bgabutton_gray'>${button_text}</i>", { 'button_text': _("Browse"), 'i18n': ['button_text'] });
        dojo.place(browse_special_achievements_button, 'special_achievements', 'after');
        this.on(dojo.query('#browse_special_achievements_button'), 'onclick', 'click_open_special_achievement_browsing_window');
        // PLAYERS' HANDS
        this.zone["hand"] = {};
        for (var player_id in this.players) {
            // Creation of the zone
            var zone = this.createZone('hand', player_id, null, null, null, /*grouped_by_age_type_and_is_relic=*/ true, /*counter_method=*/ "COUNT", /*counter_display_zero=*/ true);
            this.zone["hand"][player_id] = zone;
            this.setPlacementRules(zone, /*left_to_right=*/ true);
            // Add cards to zone according to the current situation
            if (Number(player_id) == this.player_id) {
                for (var i = 0; i < gamedatas.my_hand.length; i++) {
                    var card = gamedatas.my_hand[i];
                    this.createAndAddToZone(zone, card.position, card.age, card.type, card.is_relic, card.id, dojo.body(), card);
                    if (gamedatas.turn0 && card.selected == 1) {
                        this.selected_card = card;
                    }
                    // Add tooltip
                    this.addTooltipForCard(card);
                }
            }
            else {
                for (var type = 0; type <= 5; type++) {
                    for (var is_relic = 0; is_relic <= 1; is_relic++) {
                        for (var age = 1; age <= 11; age++) {
                            var num_cards = gamedatas.hand_counts[player_id][type][is_relic][age];
                            for (var i = 0; i < num_cards; i++) {
                                this.createAndAddToZone(zone, i, age, type, is_relic, null, dojo.body(), null);
                                if (is_relic) {
                                    // Construct card object so that we can add a tooltip to the relic
                                    var relic = { 'location': 'hand', 'owner': player_id, 'type': type, 'age': age, 'is_relic': is_relic, 'id': 212 + age };
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
        this.zone["display"] = {};
        for (var player_id in this.players) {
            if (!gamedatas.artifacts_expansion_enabled) {
                dojo.byId('display_container_' + player_id).style.display = 'none';
                continue;
            }
            // Creation of the zone
            var zone = this.createZone('display', player_id, null, null, null);
            this.zone["display"][player_id] = zone;
            this.setPlacementRules(zone, /*left_to_right=*/ true);
            // Add card to zone if it exists
            var card = gamedatas.artifacts_on_display[player_id];
            if (card != null) {
                this.createAndAddToZone(zone, card.position, card.age, card.type, card.is_relic, card.id, dojo.body(), card);
                this.addTooltipForCard(card);
            }
        }
        // PLAYERS' MUSEUMS
        this.zone["museums"] = {};
        for (var player_id in this.players) {
            // Creation of the zone
            var zone = this.createZone('museums', player_id, null, null, null);
            this.zone["museums"][player_id] = zone;
            this.setPlacementRulesForPlayerMuseums(zone);
            if (!gamedatas.artifacts_expansion_enabled || !gamedatas.fourth_edition) {
                dojo.byId('museums_container_' + player_id).style.display = 'none';
                continue;
            }
            // Add cards to zone
            var cards = gamedatas.artifacts_in_museums[player_id];
            for (var _i = 0, cards_1 = cards; _i < cards_1.length; _i++) {
                var card = cards_1[_i];
                this.createAndAddToZone(zone, card.position, card.age, card.type, card.is_relic, card.id, dojo.body(), card);
                this.addTooltipForCard(card);
            }
        }
        // PLAYERS' FORECAST
        this.zone["forecast"] = {};
        for (var player_id in this.players) {
            // Creation of the zone
            this.zone["forecast"][player_id] = this.createZone('forecast', player_id, null, null, null, /*grouped_by_age_type_and_is_relic=*/ true, /*counter_method=*/ "COUNT", /*counter_display_zero=*/ true);
            this.setPlacementRules(this.zone["forecast"][player_id], /*left_to_right=*/ true);
            // Add cards to zone according to the current situation
            for (var type = 0; type <= 5; type++) {
                for (var is_relic = 0; is_relic <= 1; is_relic++) {
                    var forecast_count = gamedatas.forecast_counts[player_id][type][is_relic];
                    for (var age = 1; age <= 11; age++) {
                        var num_cards = forecast_count[age];
                        for (var i = 0; i < num_cards; i++) {
                            this.createAndAddToZone(this.zone["forecast"][player_id], i, age, type, is_relic, null, dojo.body(), null);
                        }
                    }
                }
            }
            if (!this.gamedatas.echoes_expansion_enabled) {
                dojo.byId('forecast_text_' + player_id).style.display = 'none';
                dojo.byId('forecast_count_' + player_id).style.display = 'none';
                dojo.byId('forecast_count_container_' + player_id).style.display = 'none';
                dojo.byId('forecast_container_' + player_id).style.display = 'none';
            }
        }
        // PLAYERS' SCORE
        this.zone["score"] = {};
        for (var player_id in this.players) {
            // Creation of the zone
            this.zone["score"][player_id] = this.createZone('score', player_id, null, null, null, /*grouped_by_age_type_and_is_relic=*/ true);
            this.setPlacementRules(this.zone["score"][player_id], /*left_to_right=*/ true);
            // Add cards to zone according to the current situation
            for (var type = 0; type <= 5; type++) {
                for (var is_relic = 0; is_relic <= 1; is_relic++) {
                    var score_count = gamedatas.score_counts[player_id][type][is_relic];
                    for (var age = 1; age <= 11; age++) {
                        var num_cards = score_count[age];
                        for (var i = 0; i < num_cards; i++) {
                            this.createAndAddToZone(this.zone["score"][player_id], i, age, type, is_relic, null, dojo.body(), null);
                            if (is_relic) {
                                // Construct card object so that we can add a tooltip to the relic
                                var relic = { 'location': 'hand', 'owner': player_id, 'type': type, 'age': age, 'is_relic': is_relic, 'id': 212 + age };
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
        if (!this.isSpectator && this.gamedatas.echoes_expansion_enabled) {
            this.my_forecast_verso_window.attr("content", "<div id='my_forecast_verso'></div><a id='forecast_close_window' class='bgabutton bgabutton_blue'>" + _("Close") + "</a>");
            this.zone["my_forecast_verso"] = this.createZone('my_forecast_verso', this.player_id, null, null, null, /*grouped_by_age_type_and_is_relic=*/ true);
            this.setPlacementRules(this.zone["my_forecast_verso"], /*left_to_right=*/ true);
            for (var i = 0; i < gamedatas.my_forecast.length; i++) {
                var card = gamedatas.my_forecast[i];
                this.createAndAddToZone(this.zone["my_forecast_verso"], card.position, card.age, card.type, card.is_relic, card.id, dojo.body(), card);
                this.addTooltipForCard(card);
            }
            // Provide links to get access to that window and close it
            dojo.connect($('forecast_text_' + this.player_id), 'onclick', this, 'click_display_forecast_window');
            dojo.connect($('forecast_close_window'), 'onclick', this, 'click_close_forecast_window');
        }
        // My score: create an extra zone to show the versos of the cards at will in a windows
        if (!this.isSpectator) {
            this.my_score_verso_window.attr("content", "<div id='my_score_verso'></div><a id='score_close_window' class='bgabutton bgabutton_blue'>" + _("Close") + "</a>");
            this.zone["my_score_verso"] = this.createZone('my_score_verso', this.player_id, null, null, null, /*grouped_by_age_type_and_is_relic=*/ true);
            this.setPlacementRules(this.zone["my_score_verso"], /*left_to_right=*/ true);
            for (var i = 0; i < gamedatas.my_score.length; i++) {
                var card = gamedatas.my_score[i];
                this.createAndAddToZone(this.zone["my_score_verso"], card.position, card.age, card.type, card.is_relic, card.id, dojo.body(), card);
                this.addTooltipForCard(card);
            }
            // Provide links to get access to that window and close it
            dojo.connect($('score_text_' + this.player_id), 'onclick', this, 'click_display_score_window');
            dojo.connect($('score_close_window'), 'onclick', this, 'click_close_score_window');
        }
        // PLAYERS' ACHIEVEMENTS
        for (var player_id in this.players) {
            // Creation of the zone
            this.zone["achievements"][player_id] = this.createZone('achievements', player_id);
            this.setPlacementRules(this.zone["achievements"][player_id], /*left_to_right=*/ true);
            // Add cards to zone according to the current situation
            var achievements = gamedatas.claimed_achievements[player_id];
            for (var i = 0; i < achievements.length; i++) {
                var achievement = achievements[i];
                if (isFlag(parseInt(achievement.id)) || isFountain(parseInt(achievement.id))) {
                    this.createAndAddToZone(this.zone["achievements"][player_id], i, null, achievement.type, achievement.is_relic, achievement.id, dojo.body(), achievement);
                    this.addTooltipForCard(achievement);
                }
                else if (achievement.age == null) { // Special achievement
                    this.createAndAddToZone(this.zone["achievements"][player_id], i, null, achievement.type, achievement.is_relic, achievement.id, dojo.body(), null);
                    this.addTooltipForCard(achievement);
                }
                else {
                    // Normal achievement or relic
                    this.createAndAddToZone(this.zone["achievements"][player_id], i, achievement.age, achievement.type, achievement.is_relic, null, dojo.body(), null);
                    if (achievement.is_relic && this.canShowCardTooltip(achievement.id)) {
                        this.addTooltipForCard(achievement);
                    }
                }
            }
        }
        // PLAYERS' SAFE
        this.zone["safe"] = {};
        for (var player_id in this.players) {
            // Creation of the zone
            this.zone["safe"][player_id] = this.createZone('safe', player_id, null, null, null, /*grouped_by_age_type_and_is_relic=*/ true);
            this.setPlacementRules(this.zone["safe"][player_id], /*left_to_right=*/ true);
            // Add cards to zone according to the current situation
            for (var type = 0; type <= 5; type++) {
                var safe_count = gamedatas.safe_counts[player_id][type];
                for (var age = 1; age <= 11; age++) {
                    var num_cards = safe_count[age];
                    for (var i = 0; i < num_cards; i++) {
                        this.createAndAddToZone(this.zone["safe"][player_id], i, age, type, /*is_relic=*/ 0, null, dojo.body(), null);
                    }
                }
            }
            if (!this.gamedatas.unseen_expansion_enabled) {
                dojo.byId('safe_text_' + player_id).style.display = 'none';
                dojo.byId('safe_container_' + player_id).style.display = 'none';
            }
        }
        // PLAYER BOARD
        // Display mode
        if (!this.isSpectator) {
            this.display_mode = gamedatas.display_mode;
            this.view_full = gamedatas.view_full;
        }
        // Stacks
        this.zone["board"] = {};
        this.number_of_splayed_piles = 0;
        for (var player_id in this.players) {
            this.zone["board"][player_id] = {};
            var player_board = gamedatas.board[player_id];
            var player_splay_directions = gamedatas.board_splay_directions[player_id];
            var player_splay_directions_in_clear = gamedatas.board_splay_directions_in_clear[player_id];
            for (var color = 0; color < 5; color++) {
                var splay_direction = player_splay_directions[color];
                var splay_direction_in_clear = player_splay_directions_in_clear[color];
                // Creation of the zone
                this.zone["board"][player_id][color] = this.createZone('board', player_id, null, null, color, /*grouped_by_age_type_and_is_relic=*/ false, /*counter_method=*/ "COUNT", /*counter_display_zero=*/ false);
                // Disable pile counters
                if (this.prefs[113].value == 1) {
                    dojo.style("pile_count_".concat(player_id, "_").concat(color), 'display', 'none');
                }
                // Splay indicator
                dojo.addClass('splay_indicator_' + player_id + '_' + color, 'splay_' + splay_direction);
                if (splay_direction > 0) {
                    this.number_of_splayed_piles++;
                    this.addCustomTooltip('splay_indicator_' + player_id + '_' + color, dojo.string.substitute(_('This stack is splayed ${direction}.'), { 'direction': '<b>' + splay_direction_in_clear + '</b>' }), '');
                }
                // Add cards to zone according to the current situation
                var cards_in_pile = player_board[color];
                for (var i = 0; i < cards_in_pile.length; i++) {
                    var card = cards_in_pile[i];
                    this.createAndAddToZone(this.zone["board"][player_id][color], card.position, card.age, card.type, card.is_relic, card.id, dojo.body(), card);
                    // Add tooltip
                    this.addTooltipForCard(card);
                }
                this.refreshSplay(this.zone["board"][player_id][color], splay_direction);
            }
        }
        // REVEALED ZONE
        this.zone["revealed"] = {};
        for (var player_id in this.players) {
            var zone = this.createZone('revealed', player_id, null, null, null);
            this.zone["revealed"][player_id] = zone;
            dojo.style(zone.container_div, 'display', 'none');
            this.setPlacementRules(zone, /*left_to_right=*/ true);
            var revealed_cards = gamedatas.revealed[player_id];
            for (var i = 0; i < revealed_cards.length; i++) {
                var card = revealed_cards[i];
                this.createAndAddToZone(zone, card.position, card.age, card.type, card.is_relic, card.id, dojo.body(), card);
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
        if (!this.isSpectator) {
            this.number_of_tucked_cards = gamedatas.monument_counters.number_of_tucked_cards;
            this.number_of_scored_cards = gamedatas.monument_counters.number_of_tucked_cards;
            this.refreshSpecialAchievementProgression();
        }
        // JUNK
        this.zone["junk"] = {};
        this.zone["junk"]["0"] = this.createZone('junk', 0, null, null, null, /*grouped_by_age_type_and_is_relic=*/ true);
        for (var type = 0; type <= 5; type++) {
            for (var is_relic = 0; is_relic <= 1; is_relic++) {
                for (var age = 1; age <= 11; age++) {
                    var num_cards = gamedatas.junk_counts[type][is_relic][age];
                    for (var i = 0; i < num_cards; i++) {
                        this.createAndAddToZone(this.zone["junk"]["0"], i, age, type, is_relic, null, dojo.body(), null);
                    }
                }
            }
        }
        if (!this.gamedatas.fourth_edition) {
            dojo.byId('junk_container').style.display = 'none';
        }
        // Add a button here to open up the junk popup
        var browse_junk_button = this.format_string_recursive("<i id='browse_junk_button' class='bgabutton bgabutton_gray'>${button_text}</i>", { 'button_text': _("Browse"), 'i18n': ['button_text'] });
        dojo.place(browse_junk_button, 'junk_header', 'after');
        this.on(dojo.query('#browse_junk_button'), 'onclick', 'click_open_junk_browsing_window');
        // CURRENT DOGMA CARD EFFECT
        if (gamedatas.JSCardEffectQuery !== null) {
            // Highlight the current effect if visible
            dojo.query(gamedatas.JSCardEffectQuery).addClass('current_effect');
        }
        // Hide player's area if they have been eliminated from the game (e.g. Exxon Valdez)
        for (var player_id in this.players) {
            if (this.players[player_id].eliminated == 1) {
                dojo.byId('player_' + player_id).style.display = 'none';
            }
        }
        this.refreshAchievementsCounts();
        if (this.gamedatas.echoes_expansion_enabled) {
            this.refreshForecastCounts();
        }
        if (this.gamedatas.unseen_expansion_enabled) {
            this.refreshSafeCounts();
        }
        this.default_viewport = "width=640"; // 640 is set in game_interface_width.min in gameinfos.inc.php
        this.onScreenWidthChange();
        this.refreshLayout();
        // Force refresh page on resize if width changes
        window.onresize = function () {
            self.refreshLayout();
        };
        // Setup game notifications to handle (see "setupNotifications" method below)
        this.setupNotifications();
        console.log("Ending game setup");
    };
    /* [Undocumented] Override BGA framework functions to call onLoadingComplete when loading is done */
    Innovation.prototype.setLoader = function (value, max) {
        this.inherited(arguments);
        if (!this.isLoadingComplete && value >= 100) {
            this.isLoadingComplete = true;
            this.onLoadingComplete();
        }
    };
    Innovation.prototype.onLoadingComplete = function () {
        var _this = this;
        // Add card tooltips to existing game log messages
        this.cards.forEach(function (card) {
            // For some reason, after a page refresh, each entry in the game log is located in two diffferent
            // spots on the page, meaning that each span holding a card name no longer has a unique ID (since
            // it appears exactly twice), and BGA's framework to add a tooltip requires that it has a unique ID.
            // The workaround here is to first remove the extra IDs, before trying to add the tooltips.
            dojo.query("#chatbar .card_id_" + card.id).removeAttr('id');
            var elements = dojo.query(".card_id_" + card.id);
            if (elements.length > 0 && _this.canShowCardTooltip(card.id)) {
                _this.addCustomTooltipToClass("card_id_" + card.id, _this.getTooltipForCard(card.id), "");
            }
        });
    };
    Innovation.prototype.onScreenWidthChange = function () {
        // Remove broken "zoom" property added by BGA framework
        this.gameinterface_zoomFactor = 1;
        $("page-content").style.removeProperty("zoom");
        $("page-title").style.removeProperty("zoom");
        $("right-side-first-part").style.removeProperty("zoom");
    };
    Innovation.prototype.refreshLayout = function () {
        var on_mobile = dojo.hasClass('ebd-body', 'mobile_version');
        var window_width = Math.max(dojo.window.getBox().w, 640); // 640 is set in game_interface_width.min in gameinfos.inc.php
        var player_panel_width = on_mobile ? 0 : dojo.position('right-side').w + 10;
        var decks_width = 214;
        var decks_on_right = this.prefs[112].value == 1;
        var main_area_width;
        if (decks_on_right) {
            main_area_width = window_width - player_panel_width - decks_width;
        }
        else if (on_mobile) {
            main_area_width = window_width;
        }
        else {
            main_area_width = window_width - player_panel_width;
        }
        dojo.style('main_area', 'width', main_area_width + 'px');
        if (decks_on_right) {
            dojo.style('main_area_wrapper', 'flex-direction', 'row');
            dojo.style('decks_and_available_achievements', 'flex-direction', 'column');
            dojo.style('available_relics_and_achievements_container', 'display', 'unset');
        }
        else {
            dojo.style('main_area_wrapper', 'flex-direction', 'column');
            dojo.style('decks_and_available_achievements', 'flex-direction', 'row');
            dojo.style('available_relics_and_achievements_container', 'display', 'inline-block');
        }
        if (this.num_sets_in_play == 1) {
            dojo.style('decks', 'display', 'flex');
        }
        else {
            dojo.style('decks', 'display', 'inline-block');
        }
        var main_area_inner_width = main_area_width - 23;
        if (this.gamedatas.echoes_expansion_enabled) {
            main_area_inner_width -= 7;
        }
        if (this.gamedatas.unseen_expansion_enabled) {
            main_area_inner_width -= 7;
        }
        // Calculation relies on this.delta.forecast.x == this.delta.score.x == this.delta.achievements.x == this.delta.safe.x
        var num_cards_in_row = Math.floor(main_area_inner_width / this.delta.score.x);
        var num_safe_cards_in_row = this.gamedatas.unseen_expansion_enabled ? 1 : 0;
        this.num_cards_in_row.set("safe", num_safe_cards_in_row);
        var num_forecast_score_achievements_cards = num_cards_in_row - num_safe_cards_in_row;
        var num_achievements_cards_in_row = Math.floor(num_forecast_score_achievements_cards / 3);
        if (num_achievements_cards_in_row < 3) {
            num_achievements_cards_in_row = 3;
        }
        if (num_achievements_cards_in_row > this.gamedatas.number_of_achievements_needed_to_win) {
            num_achievements_cards_in_row = this.gamedatas.number_of_achievements_needed_to_win;
        }
        // If we're splitting the achievements across two rows, let's make the rows as even as possible
        if (this.gamedatas.number_of_achievements_needed_to_win / 2 < num_achievements_cards_in_row && num_achievements_cards_in_row < this.gamedatas.number_of_achievements_needed_to_win) {
            num_achievements_cards_in_row = Math.ceil(this.gamedatas.number_of_achievements_needed_to_win / 2);
        }
        this.num_cards_in_row.set("achievements", num_achievements_cards_in_row);
        var num_forecast_cards_in_row;
        var num_score_cards_in_row;
        if (this.gamedatas.echoes_expansion_enabled) {
            num_forecast_cards_in_row = Math.floor((num_forecast_score_achievements_cards - num_achievements_cards_in_row) / 2);
            num_score_cards_in_row = num_forecast_cards_in_row;
        }
        else {
            num_forecast_cards_in_row = 0;
            num_score_cards_in_row = num_forecast_score_achievements_cards - num_achievements_cards_in_row;
        }
        this.num_cards_in_row.set("forecast", num_forecast_cards_in_row);
        this.num_cards_in_row.set("score", num_score_cards_in_row);
        var safe_container_width = num_safe_cards_in_row * this.delta.safe.x;
        var forecast_container_width = num_forecast_cards_in_row * this.delta.forecast.x;
        var achievement_container_width = num_achievements_cards_in_row * this.delta.achievements.x;
        var score_container_width = main_area_inner_width - forecast_container_width - achievement_container_width - safe_container_width;
        for (var player_id in this.players) {
            var hand_width = dojo.position('hand_container_' + player_id).w;
            dojo.style('forecast_container_' + player_id, 'width', forecast_container_width + 'px');
            dojo.style('forecast_' + player_id, 'width', forecast_container_width + 'px');
            dojo.setStyle(this.zone["forecast"][player_id].container_div, 'width', forecast_container_width + "px");
            dojo.style('score_container_' + player_id, 'width', score_container_width + 'px');
            dojo.style('score_' + player_id, 'width', score_container_width + 'px');
            dojo.setStyle(this.zone["score"][player_id].container_div, 'width', score_container_width + "px");
            dojo.style('achievement_container_' + player_id, 'width', achievement_container_width + 'px');
            dojo.style('achievements_' + player_id, 'width', achievement_container_width + 'px');
            dojo.setStyle(this.zone["achievements"][player_id].container_div, 'width', achievement_container_width + "px");
            dojo.style('safe_container_' + player_id, 'width', safe_container_width + 'px');
            dojo.style('safe_' + player_id, 'width', safe_container_width + 'px');
            dojo.setStyle(this.zone["safe"][player_id].container_div, 'width', safe_container_width + "px");
            dojo.style('progress_' + player_id, 'width', hand_width + 'px');
            dojo.style('artifacts_' + player_id, 'width', hand_width + 'px');
        }
        var player_museums_width = dojo.position('museums_' + this.player_id).w;
        this.num_cards_in_row.set("museums", Math.floor(player_museums_width / this.delta.museums.x));
        this.num_cards_in_row.set("my_hand", Math.floor(main_area_inner_width / this.delta.my_hand.x));
        this.num_cards_in_row.set("opponent_hand", Math.floor(main_area_inner_width / this.delta.opponent_hand.x));
        // TODO(LATER): Figure out how to disable the animations while resizing the zones.
        for (var player_id in this.players) {
            this.zone["museums"][player_id].updateDisplay();
            this.zone["forecast"][player_id].updateDisplay();
            this.zone["score"][player_id].updateDisplay();
            this.zone["achievements"][player_id].updateDisplay();
            this.zone["hand"][player_id].updateDisplay();
            this.zone["safe"][player_id].updateDisplay();
        }
        for (var player_id in this.players) {
            for (var color = 0; color < 5; color++) {
                var zone = this.zone["board"][player_id][color];
                this.refreshSplay(zone, zone.splay_direction);
            }
        }
    };
    ///////////////////////////////////////////////////
    //// Simple handler management system
    // this.on replace dojo.connect
    // this.off enables to disconnect the handler of one particular event on the object attached with this.on
    // this.restart enables to reconnect the last handler of one particular event
    Innovation.prototype.on = function (filter, event, method) {
        var self = this;
        filter.forEach(function (node, index, arr) {
            if (node.last_handler === undefined) {
                node.last_handler = {};
            }
            node.last_handler[event] = method;
            self.connect(node, event, method);
        });
    };
    Innovation.prototype.off = function (filter, event) {
        var self = this;
        filter.forEach(function (node, index, arr) {
            self.disconnect(node, event);
        });
    };
    Innovation.prototype.restart = function (filter, event) {
        var self = this;
        filter.forEach(function (node, index, arr) {
            self.connect(node, event, node.last_handler[event]);
        });
    };
    ///////////////////////////////////////////////////
    //// Game & client states
    // onEnteringState: this method is called each time we are entering into a new game state.
    //                  You can use this method to perform some user interface changes at this moment.
    //
    Innovation.prototype.onEnteringState = function (stateName, args) {
        console.log('Entering state: ' + stateName);
        console.log(args);
        if (this.initializing) { // Here, do things that have to be done on setup but that cannot be done inside the function
            for (var player_id in this.players) { // Displaying player BGA scores
                this.scoreCtrl[player_id].setValue(this.gamedatas.players[player_id].achievement_count); // BGA score = number of claimed achievements
                var tooltip_help = _("Number of achievements. ${n} needed to win").replace('${n}', this.gamedatas.number_of_achievements_needed_to_win.toString());
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
                    dojo.addClass(this.getCardHTMLId(this.selected_card.id, this.selected_card.age, this.selected_card.type, this.selected_card.is_relic, this.HTML_class.get("my_hand")), 'selected');
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
                for (var p = 0; p < result.length; p++) {
                    var player_result = result[p];
                    var player_id = player_result.player;
                    var player_score = player_result.score;
                    var player_score_aux = player_result.score_aux;
                    // Gold star => BGA score: remove the tooltip which says that it's the number of achievements because it is not the case in end by score or by dogma and set the counter to its appropriate value
                    this.removeTooltip('player_score_' + player_id);
                    this.scoreCtrl[player_id].setValue(player_score);
                    // Silver star => BGA tie breaker: remove the tooltip and set the counter to its appropriate value
                    this.removeTooltip('score_count_container_' + player_id);
                    this.counter["score"][player_id].setValue(player_score_aux);
                }
                break;
        }
        switch (stateName) {
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
                    var cards_in_initial_hand = this.selectMyCardsInHand();
                    cards_in_initial_hand.addClass("clickable");
                    this.on(cards_in_initial_hand, 'onclick', 'action_clicForInitialMeld');
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
                    // Claimable standard achievements (achieve action)
                    if (args.args.claimable_standard_achievement_values.length > 0) {
                        var claimable_achievements = this.selectClaimableStandardAchievements(args.args.claimable_standard_achievement_values);
                        claimable_achievements.addClass("clickable");
                        this.on(claimable_achievements, 'onclick', 'action_clickCardBackForAchieve');
                    }
                    // Claimable secrets (achieve action)
                    if (args.args.claimable_secret_values.length > 0) {
                        var claimable_achievements = this.selectClaimableSecrets(args.args.claimable_secret_values);
                        claimable_achievements.addClass("clickable");
                        this.on(claimable_achievements, 'onclick', 'action_clickCardBackForAchieve');
                    }
                    // Top drawable card on deck (draw action)
                    var max_age = this.gamedatas.fourth_edition ? 11 : 10;
                    if (args.args.age_to_draw <= max_age) {
                        var drawable_card = this.selectDrawableCard(args.args.age_to_draw, args.args.type_to_draw);
                        drawable_card.addClass("clickable");
                        this.on(drawable_card, 'onclick', 'action_clicForDraw');
                    }
                    // Cards in hand (meld action)
                    var city_draw_type = args.args.city_draw_falls_back_to_other_type ? args.args.type_to_draw : 2;
                    this.addTooltipsWithActionsToMyHand(args.args._private.meld_info, args.args.age_to_draw, city_draw_type);
                    var cards_in_hand = this.selectMyCardsInHand();
                    cards_in_hand.addClass("clickable");
                    this.off(cards_in_hand, 'onclick'); // Remove possible stray handler from initial meld.
                    this.on(cards_in_hand, 'onclick', 'action_clickMeld');
                    // Artifact on display (meld action)
                    this.addTooltipWithMeldActionToMyArtifacts(args.args._private.meld_info, args.args.age_to_draw, city_draw_type);
                    var meldable_artifacts = this.gamedatas.fourth_edition ? this.selectArtifactsInMuseums() : this.selectArtifactOnDisplay();
                    meldable_artifacts.addClass("clickable");
                    this.on(meldable_artifacts, 'onclick', 'action_clickMeld');
                    // Cards on my board (dogma action)
                    var cards_on_my_board = this.selectTopCardsEligibleForDogma([this.player_id]);
                    this.addTooltipsWithActionsToBoard(cards_on_my_board, args.args._private.dogma_effect_info);
                    cards_on_my_board.addClass("clickable");
                    this.on(cards_on_my_board, 'onclick', 'action_clickDogma');
                    // Cards on non-adjacent board (dogma action)
                    var cards_on_non_adjacent_board = this.selectTopCardsEligibleForDogma(args.args._private.non_adjacent_player_ids);
                    this.addTooltipsWithActionsToBoard(cards_on_non_adjacent_board, args.args._private.dogma_effect_info);
                    cards_on_non_adjacent_board.addClass("clickable");
                    this.on(cards_on_non_adjacent_board, 'onclick', 'action_clickNonAdjacentDogma');
                    // Cards on board (endorse action)
                    // TODO(4E-CITIES): Make it possible to endorse dogmas on non-adjacent boards.
                    var endorsable_cards = this.selectMyTopCardsEligibleForEndorsedDogma(args.args._private.dogma_effect_info);
                    this.off(endorsable_cards, 'onclick');
                    endorsable_cards.addClass("can_endorse");
                    this.on(endorsable_cards, 'onclick', 'action_clickEndorse');
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
                            if (!this.isInReplayMode()) {
                                if (args.args._private.must_show_score) {
                                    this.my_score_verso_window.show();
                                }
                                if (args.args._private.must_show_forecast) {
                                    this.my_forecast_verso_window.show();
                                }
                            }
                        }
                        var selectable_rectos = this.selectRectosFromList(args.args._private.selectable_rectos);
                        if (selectable_rectos !== null) {
                            selectable_rectos.addClass("clickable").addClass('mid_dogma');
                            this.on(selectable_rectos, 'onclick', 'action_clicForChooseRecto');
                            if (!this.isInReplayMode() && args.args._private.must_show_junk) {
                                this.click_open_junk_browsing_window();
                            }
                        }
                        if (args.args._private.show_all_cards_on_board) {
                            for (var color = 0; color < 5; color++) {
                                var zone = this.zone["board"][this.player_id][color];
                                this.refreshSplay(zone, zone.splay_direction, /*force_full_visible=*/ true);
                            }
                        }
                        // Add special warning to Tools to prevent the player from accidentally returning a 3 in the first
                        // part of the interaction in an attempt to draw 3 cards.
                        if (args.args.card_interaction == "1N1A" && parseInt(args.args.num_cards_already_chosen) == 0) {
                            var age_3s_in_hand = dojo.query("#hand_" + this.player_id + " > .card.age_3");
                            this.off(age_3s_in_hand, 'onclick');
                            this.on(age_3s_in_hand, 'onclick', 'action_clickForChooseFront');
                            var warning_1 = _("Are you sure you want to return a ${age_3}? This won't allow you to draw three cards.").replace("${age_3}", this.square('N', 'age', 3));
                            age_3s_in_hand.forEach(function (card) {
                                dojo.attr(card, 'warning', warning_1);
                            });
                        }
                    }
                    else if (args.args.special_type_of_choice == 6 /* choose_rearrange */) {
                        this.off(dojo.query('#change_display_mode_button'), 'onclick');
                        for (var color = 0; color < 5; color++) {
                            var zone = this.zone["board"][this.player_id][color];
                            this.refreshSplay(zone, zone.splay_direction, /*force_full_visible=*/ true); // Show all cards
                        }
                        this.publication_permutations_done = [];
                        var selectable_cards = this.selectAllCardsOnMyBoard();
                        selectable_cards.addClass("clickable").addClass('mid_dogma');
                        this.on(selectable_cards, 'onclick', 'publicationClicForMove');
                    }
                    else if (args.args.special_type_of_choice == 13 /* choose_special_achievement */) {
                        this.buildSpecialAchievementSelectionWindow(args.args.available_special_achievements.map(Number), args.args.junked_special_achievements.map(Number));
                        this.click_open_special_achievement_selection_window();
                    }
                    if (args.args.color_pile !== null) { // The selection involves cards in a stack
                        this.color_pile = args.args.color_pile;
                        // Expand the color of all players which have selectable cards
                        var owners = args.args._private.visible_selectable_cards.map(function (card) { return card.owner; }).filter(function (value, index, self) { return self.indexOf(value) === index; });
                        var self_1 = this;
                        owners.forEach(function (owner) {
                            var zone = self_1.zone["board"][owner][self_1.color_pile];
                            self_1.refreshSplay(zone, zone.splay_direction, /*force_full_visible=*/ true);
                        });
                    }
                    if (args.args.splay_direction !== null) {
                        // Update tooltips for cards of stacks that can be splayed
                        this.addTooltipsWithSplayingActionsToColorsOnMyBoard(args.args.splayable_colors, args.args.splayable_colors_in_clear, args.args.splay_direction, args.args.splay_direction_in_clear);
                    }
                    if ((args.args.can_pass || args.args.can_stop) && (args.args.special_type_of_choice == 0 || args.args.special_type_of_choice == 6 /* rearrange */) && args.args.splay_direction === null) {
                        $('pagemaintitletext').innerHTML += " " + _("or");
                    }
                    break;
            }
        }
        else {
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
                        var end_of_message = [];
                        for (var i = 0; i < args.args.splayable_colors_in_clear.length; i++) {
                            if (args.args.splay_direction == 0) {
                                end_of_message.push(dojo.string.substitute(_("unsplay his ${cards}"), { 'cards': _(args.args.splayable_colors_in_clear[i]) }));
                            }
                            else {
                                end_of_message.push(dojo.string.substitute(_("splay his ${cards} ${direction}"), { 'cards': _(args.args.splayable_colors_in_clear[i]), 'direction': _(args.args.splay_direction_in_clear) }));
                            }
                        }
                        $('pagemaintitletext').innerHTML += " " + end_of_message.join(", ");
                    }
                    // Add if the player can pass or stop
                    if (args.args.can_pass || args.args.can_stop) {
                        if (args.args.can_pass) {
                            $('pagemaintitletext').innerHTML += " " + _("or pass");
                        }
                        else { // args.can_stop
                            $('pagemaintitletext').innerHTML += " " + _("or stop");
                        }
                    }
                    break;
            }
        }
    };
    // onLeavingState: this method is called each time we are leaving a game state.
    //                 You can use this method to perform some user interface changes at this moment.
    //
    Innovation.prototype.onLeavingState = function (stateName) {
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
                    this.addTooltipsWithoutActionsToMyArtifacts();
                    this.addTooltipsWithoutActionsToMyHand();
                    this.addTooltipsWithoutActionsToMyBoard();
                // TODO(LATER): Figure out if this fallthrough is intentional or is a bug. Maybe this is causing https://boardgamearena.com/bug?id=13012.
                case 'selectionMove':
                    // Reset tooltips for board (in case there was a splaying choice)
                    this.addTooltipsWithoutActionsToMyBoard();
                    if (!this.isInReplayMode()) {
                        this.click_close_score_window();
                        this.click_close_forecast_window();
                        this.click_close_card_browsing_window();
                    }
                    for (var player_id in this.players) {
                        for (var color = 0; color < 5; color++) {
                            var zone = this.zone["board"][player_id][color];
                            this.refreshSplay(zone, zone.splay_direction, /*force_full_visible=*/ false);
                        }
                    }
            }
        }
    };
    // onUpdateActionButtons: in this method you can manage "action buttons" that are displayed in the
    //                        action status bar (ie: the HTML links in the status bar).
    //        
    Innovation.prototype.onUpdateActionButtons = function (stateName, args) {
        if (this.isCurrentPlayerActive()) {
            switch (stateName) {
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
                        if (this.gamedatas.fourth_edition) {
                            this.addActionButton("dogma_artifact", _("Dogma"), "action_clicForDogmaArtifact");
                        }
                        else {
                            this.addActionButton("dogma_artifact", _("Dogma and Return"), "action_clicForDogmaArtifact");
                        }
                    }
                    if (!this.gamedatas.fourth_edition) {
                        this.addActionButton("return_artifact", _("Return"), "action_clicForReturnArtifact");
                    }
                    this.addActionButton("pass_artifact", _("Pass"), "action_clicForPassArtifact");
                    break;
                case 'promoteCardPlayerTurn':
                    if (!this.gamedatas.fourth_edition) {
                        this.addActionButton("pass_promote", _("Pass"), "action_clickForPassPromote");
                    }
                    break;
                case 'dogmaPromotedPlayerTurn':
                    this.addActionButton("dogma_promoted", _("Dogma"), "action_clickForDogmaPromoted");
                    this.addActionButton("pass_dogma_promoted", _("Pass"), "action_clickForPassDogmaPromoted");
                    break;
                case 'playerTurn':
                    // Red buttons for claimable standard achievements and secrets
                    for (var i = 0; i < args.claimable_standard_achievement_values.length; i++) {
                        var age = args.claimable_standard_achievement_values[i];
                        var HTML_id = "achieve_standard_" + age;
                        this.addActionButton(HTML_id, _("Achieve ${age}").replace("${age}", this.square('N', 'age', age)), "action_clickButtonForAchieveStandardAchievement");
                        dojo.removeClass(HTML_id, 'bgabutton_blue');
                        dojo.addClass(HTML_id, 'bgabutton_red');
                    }
                    for (var i = 0; i < args.claimable_secret_values.length; i++) {
                        var age = args.claimable_secret_values[i];
                        var HTML_id = "achieve_secret_" + age;
                        this.addActionButton(HTML_id, _("Achieve ${age} from safe").replace("${age}", this.square('N', 'age', age)), "action_clickButtonForAchieveSecret");
                        dojo.removeClass(HTML_id, 'bgabutton_blue');
                        dojo.addClass(HTML_id, 'bgabutton_red');
                    }
                    // Blue buttons for draw action (or red if taking this action would finish the game)
                    var max_age = this.gamedatas.fourth_edition ? 11 : 10;
                    if (args.age_to_draw <= max_age) {
                        this.addActionButton("take_draw_action", _("Draw a ${age}").replace("${age}", this.square('N', 'age', args.age_to_draw, 'type_' + args.type_to_draw)), "action_clicForDraw");
                    }
                    else {
                        this.addActionButton("take_draw_action", _("Finish the game (attempt to draw above ${age_10})").replace('${age_10}', this.square('N', 'age', max_age)), "action_clicForDraw");
                    }
                    dojo.place("<span class='extra_text'> , " + _("meld or dogma") + "</span>", "take_draw_action", "after");
                    break;
                case 'selectionMove':
                    var splay_choice = args.splay_direction !== null;
                    var last_button_id = null;
                    if (args.special_type_of_choice == 11 /* choose_non_negative_integer */) {
                        this.addActionButton("decrease_integers", "<<", "action_clickButtonToDecreaseIntegers");
                        dojo.removeClass("decrease_integers", 'bgabutton_blue');
                        dojo.addClass("decrease_integers", 'bgabutton_red');
                        var default_integer = parseInt(args.default_integer);
                        if (default_integer == 0) {
                            dojo.byId('decrease_integers').style.display = 'none';
                        }
                        for (var i = 0; i < 6; i++) {
                            this.addActionButton("choice_" + i, String(default_integer + i), "action_clicForChooseSpecialOption");
                        }
                        last_button_id = "choice_5";
                        this.addActionButton("increase_integers", ">>", "action_clickButtonToIncreaseIntegers");
                        dojo.removeClass("increase_integers", 'bgabutton_blue');
                        dojo.addClass("increase_integers", 'bgabutton_red');
                    }
                    else if (args.special_type_of_choice == 13 /* choose_special_achievement */) {
                        this.addActionButton("choice_0", _("Select"), "click_open_special_achievement_selection_window");
                        last_button_id = "choice_0";
                    }
                    else if (args.special_type_of_choice != 0 && args.special_type_of_choice != 6) {
                        // Add a button for each available options
                        for (var i = 0; i < args.options.length; i++) {
                            var option = args.options[i];
                            this.addActionButton("choice_" + option.value, this.format_string_recursive(_(option.text), {
                                'age': option.age,
                                'name': option.name,
                                'splay_direction': option.splay_direction,
                                'color': option.color,
                                'card': option.card,
                                'i18n': option.i18n,
                            }), "action_clicForChooseSpecialOption");
                        }
                        last_button_id = "choice_" + args.options[args.options.length - 1].value;
                    }
                    else if (splay_choice) {
                        // Add button for splaying choices
                        for (var i = 0; i < args.splayable_colors.length; i++) {
                            if (i > 0) {
                                dojo.place("<span class='extra_text'> ,</span>", "splay_" + args.splayable_colors[i - 1], "after");
                            }
                            if (args.splay_direction == 0) {
                                this.addActionButton("splay_" + args.splayable_colors[i], dojo.string.substitute(_("Unsplay your ${cards}"), {
                                    'cards': _(args.splayable_colors_in_clear[i])
                                }), "action_clicForSplay");
                            }
                            else {
                                this.addActionButton("splay_" + args.splayable_colors[i], dojo.string.substitute(_("Splay your ${cards} ${direction}"), {
                                    'cards': _(args.splayable_colors_in_clear[i]),
                                    'direction': _(args.splay_direction_in_clear)
                                }), "action_clicForSplay");
                            }
                        }
                        last_button_id = "splay_" + args.splayable_colors[args.splayable_colors.length - 1];
                    }
                    // Add a button if I can pass or stop
                    if (args.can_pass || args.can_stop) {
                        if (last_button_id != null) {
                            dojo.place("<span class='extra_text'> " + _("or") + "</span>", last_button_id, "after");
                        }
                        var action = args.can_pass ? "pass" : "stop";
                        var message = args.can_pass ? _("Pass") : _("Stop");
                        this.addActionButton(action, message, "action_clicForPassOrStop");
                    }
                    break;
            }
        }
    };
    ///////////////////////////////////////////////////
    //// Utility methods
    /*
    
        Here, you can defines some utility methods that you can use everywhere in your javascript
        script.
    
    */
    Innovation.prototype.addToLog = function (message) {
        var HTML = dojo.string.substitute('<div class="log" style="height: auto; display: block; color: rgb(0, 0, 0);"><div class="roundedbox">${msg}</div></div>', { 'msg': message });
        dojo.place(HTML, $('logs'), 'first');
    };
    Innovation.prototype.startActionTimer = function (buttonId, time, callback, callbackParam) {
        var _this = this;
        if (callbackParam === void 0) { callbackParam = null; }
        var button = $(buttonId);
        var isReadOnly = this.isReadOnly();
        if (button == null || isReadOnly) {
            return;
        }
        this._actionTimerLabel = button.innerHTML;
        this._actionTimerSeconds = time + 1;
        this._callback = callback;
        this._callbackParam = callbackParam;
        this._actionTimerFunction = function () {
            var button = $(buttonId);
            if (button == null) {
                _this.stopActionTimer();
            }
            else if (_this._actionTimerSeconds-- > 1) {
                button.innerHTML = _this._actionTimerLabel + ' (' + _this._actionTimerSeconds + ')';
            }
            else {
                button.innerHTML = _this._actionTimerLabel;
                _this._callback(_this._callbackParam);
                _this.stopActionTimer();
            }
        };
        this._actionTimerFunction();
        this._actionTimerId = window.setInterval(this._actionTimerFunction, 1000);
    };
    Innovation.prototype.stopActionTimer = function () {
        if (this._actionTimerId != undefined) {
            window.clearInterval(this._actionTimerId);
            delete this._actionTimerId;
        }
    };
    Innovation.prototype.addButtonForViewFull = function () {
        var button_text = this.view_full ? this.text_for_view_full : this.text_for_view_normal;
        var player_id = this.player_id;
        if (this.isSpectator) {
            var player_panel = dojo.query(".player:nth-of-type(1)")[0];
            player_id = dojo.attr(player_panel, 'id').substr(7); // Get the first player (on top)
        }
        var button = this.format_string_recursive("<i id='change_view_full_button' class='bgabutton bgabutton_gray'>${button_text}</i>", { 'button_text': button_text, 'i18n': ['button_text'] });
        dojo.place(button, 'name_' + player_id, 'after');
        this.addCustomTooltip('change_view_full_button', '<p>' + _('Use this to look at all the cards on the board.') + '</p>', "");
        this.on(dojo.query('#change_view_full_button'), 'onclick', 'toggle_view');
    };
    Innovation.prototype.addButtonForSplayMode = function () {
        var button_text = this.display_mode ? this.text_for_expanded_mode : this.text_for_compact_mode;
        var arrows = this.display_mode ? this.arrows_for_expanded_mode : this.arrows_for_compact_mode;
        var button = this.format_string_recursive("<i id='change_display_mode_button' class='bgabutton bgabutton_gray'>${arrows} ${button_text}</i>", { 'arrows': arrows, 'button_text': button_text, 'i18n': ['button_text'] });
        dojo.place(button, 'change_view_full_button', 'after');
        this.addCustomTooltip('change_display_mode_button', '<p>' + _('<b>Expanded mode:</b> the splayed stacks are displayed like in real game, to show which icons are made visible.') + '</p>' +
            '<p>' + _('<b>Compact mode:</b> the splayed stacks are displayed with minimum offset, to save space.') + '</p>', "");
        this.disableButtonForSplayMode(); // Disabled by default
    };
    Innovation.prototype.disableButtonForSplayMode = function () {
        var change_display_mode_button = dojo.query('#change_display_mode_button');
        this.off(change_display_mode_button, 'onclick');
        change_display_mode_button.addClass('disabled');
    };
    Innovation.prototype.enableButtonForSplayMode = function () {
        var change_display_mode_button = dojo.query('#change_display_mode_button');
        this.on(change_display_mode_button, 'onclick', 'toggle_displayMode');
        change_display_mode_button.removeClass('disabled');
    };
    Innovation.prototype.buildSpecialAchievementSelectionWindow = function (availableIDs, junkedIDs) {
        var _this = this;
        var content = "<div id='special_achievement_selections'>";
        // NOTE: We use getSpecialAchievementIds because we want to display them in the same order that we use in the card browser
        this.getSpecialAchievementIds().forEach(function (card_id) {
            var currentlyAvailable = availableIDs.includes(card_id);
            if (!currentlyAvailable && !junkedIDs.includes(card_id)) {
                return; // Skip this card if a player has claimed it
            }
            var card_data = _this.cards[card_id];
            var name = _(card_data.name).toUpperCase();
            var text = "<b>".concat(name, "</b>: ").concat(_this.parseForRichedText(_(card_data.condition_for_claiming), 'in_tooltip'));
            if (card_data.alternative_condition_for_claiming != null) {
                text += " ".concat(_this.parseForRichedText(_(card_data.alternative_condition_for_claiming), 'in_tooltip'));
            }
            content += "<div class=\"special_achievement_selection\">";
            content += "<div class=\"special_achievement_icon\"><div class=\"item_".concat(card_id, " age_null S card\"></div></div>");
            content += "<div class=\"special_achievement_text\">".concat(text, "</div>");
            if (currentlyAvailable) {
                content += "<div><a id='select_special_achievement_".concat(card_id, "' class='bgabutton bgabutton_blue select_special_achievement_button' card_id='").concat(card_id, "'>").concat(_("Junk"), "</a></div>");
            }
            else {
                content += "<div><a id='select_special_achievement_".concat(card_id, "' class='bgabutton bgabutton_blue select_special_achievement_button' card_id='").concat(card_id, "'>").concat(_("Unjunk"), "</a></div>");
            }
            content += "</div></br>";
        });
        content += "</div>";
        this.special_achievement_selection_window.attr("content", "".concat(content, "<a id='close_special_achievement_selection_button' class='bgabutton bgabutton_blue'>").concat(_("Close"), "</a>"));
        this.on(dojo.query('#close_special_achievement_selection_button'), 'onclick', 'click_close_special_achievement_selection_window');
        availableIDs.forEach(function (card_id) {
            _this.on(dojo.query("#select_special_achievement_".concat(card_id)), 'onclick', 'action_chooseSpecialAchievement');
        });
        junkedIDs.forEach(function (card_id) {
            _this.on(dojo.query("#select_special_achievement_".concat(card_id)), 'onclick', 'action_chooseSpecialAchievement');
        });
    };
    Innovation.prototype.click_open_special_achievement_selection_window = function () {
        this.special_achievement_selection_window.show();
    };
    Innovation.prototype.click_close_special_achievement_selection_window = function () {
        this.special_achievement_selection_window.hide();
    };
    Innovation.prototype.addButtonForBrowsingCards = function () {
        // Build button
        var button_text = _("Browse all cards");
        var button = this.format_string_recursive("<i id='browse_all_cards_button' class='bgabutton bgabutton_gray'>${button_text}</i>", { 'button_text': button_text, 'i18n': ['button_text'] });
        dojo.place(button, 'change_display_mode_button', 'after');
        this.addCustomTooltip('browse_all_cards_button', '<p>' + _('Browse the full list of cards, including special achievement.') + '</p>', "");
        // Build popup box
        var ids = this.getSpecialAchievementIds();
        // TODO(FIGURES): Add special achievements.
        var content = "";
        content += "<div id='browse_cards_buttons_row_1'>";
        content += "<div class='browse_cards_button bgabutton bgabutton_gray' id='browse_cards_type_0'>" + _("Base Set") + "</div>";
        if (this.gamedatas.artifacts_expansion_enabled) {
            content += "<div class='browse_cards_button bgabutton bgabutton_gray' id='browse_cards_type_1'>" + _("Artifacts") + "</div>";
        }
        if (this.gamedatas.cities_expansion_enabled) {
            content += "<div class='browse_cards_button bgabutton bgabutton_gray' id='browse_cards_type_2'>" + _("Cities") + "</div>";
        }
        if (this.gamedatas.echoes_expansion_enabled) {
            content += "<div class='browse_cards_button bgabutton bgabutton_gray' id='browse_cards_type_3'>" + _("Echoes") + "</div>";
        }
        // TODO(FIGURES): Add button for Figures.
        if (this.gamedatas.unseen_expansion_enabled) {
            content += "<div class='browse_cards_button bgabutton bgabutton_gray' id='browse_cards_type_5'>" + _("Unseen") + "</div>";
        }
        content += "<div class='browse_cards_button bgabutton bgabutton_gray selected' id='browse_special_achievements'>" + _("Special Achievements") + "</div>";
        if (this.gamedatas.fourth_edition) {
            content += "<div class='browse_cards_button bgabutton bgabutton_gray' id='browse_junk'>" + _("Junk") + "</div>";
        }
        content += "</div>";
        content += "<div id='browse_cards_buttons_row_2'>";
        for (var age = 1; age <= 11; age++) {
            if (age < 11 || this.gamedatas.fourth_edition) {
                content += "<div class='browse_cards_button bgabutton bgabutton_gray' id='browse_cards_age_" + age + "'>" + age + "</div>";
            }
        }
        if (this.gamedatas.relics_enabled) {
            content += "<div class='browse_cards_button bgabutton bgabutton_gray' id='browse_relics'>" + _("Relics") + "</div>";
        }
        content += "</div>";
        content += "<div id='browse_card_summaries'></div>";
        content += "<div id='junk'></div>";
        content += "<div id='special_achievement_summaries'>";
        for (var i = 0; i < ids.length; i++) {
            var card_id = ids[i];
            var card_data = this.cards[card_id];
            var name_1 = _(card_data.name).toUpperCase();
            var text = "<b>".concat(name_1, "</b>: ").concat(this.parseForRichedText(_(card_data.condition_for_claiming), 'in_tooltip'));
            if (card_data.alternative_condition_for_claiming != null) {
                text += " ".concat(this.parseForRichedText(_(card_data.alternative_condition_for_claiming), 'in_tooltip'));
            }
            content += "<div id=\"special_achievement_summary_".concat(card_id, "\" class=\"special_achievement_summary\">");
            content += "<div class=\"special_achievement_icon\"><div class=\"item_".concat(card_id, " age_null S card\"></div><div class=\"special_achievement_status\"></div></div>");
            content += "<div class=\"special_achievement_text\">".concat(text, "</div>");
            content += "</div></br>";
        }
        content += "</div>";
        this.card_browsing_window.attr("content", content + "<a id='close_card_browser_button' class='bgabutton bgabutton_blue'>" + _("Close") + "</a>");
        dojo.byId('browse_cards_buttons_row_2').style.display = 'none';
        dojo.byId('junk').style.display = 'none';
        // Make everything clickable
        this.on(dojo.query('#browse_all_cards_button'), 'onclick', 'click_open_card_browsing_window');
        this.on(dojo.query('#close_card_browser_button'), 'onclick', 'click_close_card_browsing_window');
        this.on(dojo.query('.browse_cards_button:not(#browse_special_achievements):not(#browse_junk)'), 'onclick', 'click_browse_cards');
        if (this.gamedatas.fourth_edition) {
            this.on(dojo.query('#browse_junk'), 'onclick', 'click_browse_junk');
        }
        this.on(dojo.query('#browse_special_achievements'), 'onclick', 'click_browse_special_achievements');
    };
    Innovation.prototype.refreshSpecialAchievementProgression = function () {
        // Don't check special achievement progress if this is a spectator
        if (this.isSpectator) {
            return;
        }
        // Refresh progression towards the player achieving the special achievements
        var self = this;
        dojo.query(".special_achievement_summary").forEach(function (node) {
            var id = parseInt(node.id.substring(node.id.lastIndexOf('_') + 1));
            var numerator = -1;
            var denominator = -1;
            // Skip calculation if the special achievement is already claimed
            if (dojo.query("#special_achievement_summary_".concat(id, ".unclaimed")).length == 1) {
                switch (id) {
                    case 105:
                        // Empire: three or more icons of the six main icon types
                        numerator = 0;
                        denominator = 6;
                        for (var i = 1; i <= 6; i++) {
                            if (self.counter["resource_count"][self.player_id][i].getValue() >= 3) {
                                numerator++;
                                if (numerator === 6) {
                                    break;
                                }
                            }
                        }
                        break;
                    case 106:
                        // Monument: tuck six or score six cards during a single turn
                        numerator = Math.max(self.number_of_tucked_cards, self.number_of_scored_cards);
                        denominator = 6;
                        break;
                    case 107:
                        // Wonder: five colors on your board, and each is splayed either up, right, or aslant
                        numerator = 0;
                        denominator = 5;
                        for (var i = 0; i < 5; i++) {
                            var splay_direction = self.zone["board"][self.player_id][i].splay_direction;
                            if (splay_direction >= 2) {
                                numerator++;
                            }
                        }
                        break;
                    case 108:
                        // World: twelve or more visible [EFFICIENCY] on your board
                        numerator = self.counter["resource_count"][self.player_id][6].getValue();
                        denominator = 12;
                        break;
                    case 109:
                        // Universe: five top cards, and each is of value 8 or higher
                        numerator = 0;
                        denominator = 5;
                        for (var i = 0; i < 5; i++) {
                            var items = self.zone["board"][self.player_id][i].items;
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
                        // Wealth: eight or more bonuses visible on your board
                        numerator = 0;
                        denominator = 8;
                        for (var i = 0; i < 5; i++) {
                            var pile_zone = self.zone["board"][self.player_id][i];
                            numerator += self.getVisibleBonusIconsInPile(pile_zone.items, pile_zone.splay_direction).length;
                        }
                        break;
                    case 436:
                        // Destiny: five cards in your forecast (7 in earlier editions)
                        numerator = self.zone["forecast"][self.player_id].items.length;
                        denominator = self.gamedatas.fourth_edition ? 5 : 7;
                        break;
                    case 437:
                        // Heritage: eight or more hex icons visible in one color
                        numerator = 0;
                        denominator = 8;
                        for (var i = 0; i < 5; i++) {
                            var pile_zone = self.zone["board"][self.player_id][i];
                            var num_icons = self.countVisibleIconsInPile(pile_zone.items, pile_zone.splay_direction, 0 /* hex icon */);
                            if (num_icons > numerator) {
                                numerator = num_icons;
                            }
                        }
                        break;
                    case 438:
                        // History: a color with four or more visible echo effects
                        numerator = 0;
                        denominator = 4;
                        for (var i = 0; i < 5; i++) {
                            var pile_zone = self.zone["board"][self.player_id][i];
                            var num_icons = self.countVisibleIconsInPile(pile_zone.items, pile_zone.splay_direction, 10 /* echo icon */);
                            if (num_icons > numerator) {
                                numerator = num_icons;
                            }
                        }
                        break;
                    case 439:
                        // Supremacy: three icons or more of the same icon type visible in each of four different colors
                        numerator = 0;
                        denominator = 4;
                        for (var icon = 1; icon <= 7; icon++) {
                            var num_piles = 0;
                            for (var color = 0; color < 5; color++) {
                                var pile_zone = self.zone["board"][self.player_id][color];
                                if (self.countVisibleIconsInPile(pile_zone.items, pile_zone.splay_direction, icon) >= 3) {
                                    num_piles++;
                                }
                            }
                            if (num_piles > numerator) {
                                numerator = num_piles;
                            }
                        }
                        break;
                    case 595:
                        // Confidence: top card on your board of value 5 or higher and four or more secrets
                        numerator = 0;
                        denominator = 5;
                        for (var color = 0; color < 5; color++) {
                            var pile_zone = self.zone["board"][self.player_id][color];
                            if (pile_zone.items.length > 0) {
                                var top_card_id = self.getCardIdFromHTMLId(pile_zone.items[pile_zone.items.length - 1].id);
                                if (self.cards[top_card_id].age >= 5) {
                                    numerator++;
                                    break;
                                }
                            }
                        }
                        var safe_zone = self.zone["safe"][self.player_id];
                        numerator += Math.min(4, safe_zone.items.length);
                        break;
                    case 596:
                        // Zen: top card on your board of value 6 or higher and no top card on your board of odd value
                        numerator = 0;
                        denominator = 2;
                        var value_6_or_higher = false;
                        var has_odd_value = false;
                        for (var color = 0; color < 5; color++) {
                            var pile_zone = self.zone["board"][self.player_id][color];
                            if (pile_zone.items.length > 0) {
                                var top_card_id = self.getCardIdFromHTMLId(pile_zone.items[pile_zone.items.length - 1].id);
                                var top_card = self.cards[top_card_id];
                                if (top_card.age >= 6 && !value_6_or_higher) {
                                    value_6_or_higher = true;
                                }
                                if (top_card.age % 2 === 1 && !has_odd_value) {
                                    has_odd_value = true;
                                }
                            }
                        }
                        if (value_6_or_higher) {
                            numerator++;
                        }
                        if (!has_odd_value) {
                            numerator++;
                        }
                        break;
                    case 597:
                        // Anonymity: top card on your board of value 7 or higher and no standard achievements
                        numerator = 1;
                        denominator = 2;
                        for (var color = 0; color < 5; color++) {
                            var pile_zone = self.zone["board"][self.player_id][color];
                            if (pile_zone.items.length > 0) {
                                var top_card_id = self.getCardIdFromHTMLId(pile_zone.items[pile_zone.items.length - 1].id);
                                if (self.cards[top_card_id].age >= 7) {
                                    numerator++;
                                    break;
                                }
                            }
                        }
                        var achievements_zone = self.zone["achievements"][self.player_id];
                        for (var i = 0; i < achievements_zone.items.length; i++) {
                            var item = achievements_zone.items[i];
                            var item_age = self.getCardAgeFromHTMLId(item.id);
                            if (!isNaN(item_age)) {
                                numerator--;
                                break;
                            }
                        }
                        break;
                    case 598:
                        // Folklore: top card on your board of value 8 or higher and no factories on your board
                        numerator = 0;
                        denominator = 2;
                        var value_8_or_higher = false;
                        for (var color = 0; color < 5; color++) {
                            var pile_zone = self.zone["board"][self.player_id][color];
                            if (pile_zone.items.length > 0) {
                                var top_card_id = self.getCardIdFromHTMLId(pile_zone.items[pile_zone.items.length - 1].id);
                                if (self.cards[top_card_id].age >= 8 && !value_8_or_higher) {
                                    numerator++;
                                    value_8_or_higher = true;
                                    break;
                                }
                            }
                        }
                        if (self.counter["resource_count"][self.player_id][5].getValue() == 2) {
                            numerator++;
                        }
                        break;
                    case 599:
                        // Mystery: top card on your board of value 9 or higher and fewer than five colors on your board
                        numerator = 0;
                        denominator = 2;
                        var value_9_or_higher = false;
                        var num_colors = 0;
                        for (var color = 0; color < 5; color++) {
                            var pile_zone = self.zone["board"][self.player_id][color];
                            if (pile_zone.items.length > 0) {
                                num_colors++;
                                var top_card_id = self.getCardIdFromHTMLId(pile_zone.items[pile_zone.items.length - 1].id);
                                if (self.cards[top_card_id].age >= 9 && !value_9_or_higher) {
                                    numerator++;
                                    value_9_or_higher = true;
                                }
                            }
                        }
                        if (num_colors < 5) {
                            numerator++;
                        }
                        break;
                }
            }
            dojo.query("#special_achievement_summary_".concat(id, " .special_achievement_status"))[0].innerHTML = (numerator >= 0 && denominator > 0) ? "".concat(numerator, "/").concat(denominator) : "";
        });
    };
    Innovation.prototype.getSpecialAchievementIds = function () {
        var ids = [106, 105, 108, 107, 109];
        if (this.gamedatas.cities_expansion_enabled) {
            ids.push(325, 326, 327, 328, 329);
        }
        if (this.gamedatas.echoes_expansion_enabled) {
            ids.push(439, 436, 435, 437, 438);
        }
        if (this.gamedatas.unseen_expansion_enabled) {
            ids.push(595, 596, 597, 598, 599);
        }
        return ids;
    };
    Innovation.prototype.refreshForecastCounts = function () {
        for (var player_id in this.players) {
            if (this.gamedatas.fourth_edition) {
                var numerator = this.zone["forecast"][player_id].items.length;
                var denominator = 5;
                for (var i = 0; i < 5; i++) {
                    var splay_direction = this.zone["board"][player_id][i].splay_direction;
                    if (splay_direction > 0) {
                        denominator = Math.min(denominator, 5 - splay_direction);
                    }
                }
                dojo.byId("forecast_text_".concat(player_id)).innerHTML = "Forecast<br/>(".concat(numerator, "/").concat(denominator, ")");
            }
            else {
                dojo.byId("forecast_text_".concat(player_id)).innerHTML = "Forecast";
            }
        }
    };
    Innovation.prototype.refreshAchievementsCounts = function () {
        for (var player_id in this.players) {
            var numerator = this.zone["achievements"][player_id].items.length;
            var denominator = this.gamedatas.number_of_achievements_needed_to_win;
            dojo.byId("achievements_text_".concat(player_id)).innerHTML = "Achievements<br/>(".concat(numerator, "/").concat(denominator, ")");
        }
    };
    Innovation.prototype.refreshSafeCounts = function () {
        for (var player_id in this.players) {
            var numerator = this.zone["safe"][player_id].items.length;
            var denominator = 5;
            for (var i = 0; i < 5; i++) {
                var splay_direction = this.zone["board"][player_id][i].splay_direction;
                if (splay_direction > 0) {
                    denominator = Math.min(denominator, 5 - splay_direction);
                }
            }
            dojo.byId("safe_text_".concat(player_id)).innerHTML = "Safe<br/>(".concat(numerator, "/").concat(denominator, ")");
        }
    };
    /*
    * Id management
    */
    Innovation.prototype.uniqueId = function () {
        return ++this.incremental_id;
    };
    Innovation.prototype.uniqueIdForCard = function (age, type, is_relic) {
        // We need to multiply by a large number like 1000 to avoid colliding with the IDs of real cards
        return ((this.uniqueId() * 1000 + age) * 6 + type) * 2 + parseInt(is_relic);
    };
    /*
    * Icons and little stuff
    */
    Innovation.prototype.square = function (size, type, key, context) {
        if (context === void 0) { context = null; }
        var age = null;
        var agetype = null;
        if (type === 'age') {
            age = String(key);
            agetype = type;
        }
        var ret = "<span class='square ".concat(size);
        if (agetype !== null) {
            ret += " " + agetype;
        }
        ret += " ".concat(type, "_").concat(key);
        if (context !== null) {
            ret += " " + context;
        }
        ret += "'>";
        if (age !== null) {
            ret += " " + age;
        }
        ret += "</span>";
        return ret;
    };
    Innovation.prototype.all_icons = function (max_icon, type) {
        var str = '';
        for (var i = 1; i <= max_icon; i++) {
            if (i > 1) {
                str += '&nbsp';
            }
            str += "<span class='icon_".concat(i, " square ").concat(type, "'></span>");
        }
        return str;
    };
    /*
        * Tooltip management
        */
    Innovation.prototype.shapeTooltip = function (help_HTML, action_HTML) {
        var help_string_passed = help_HTML != "";
        var action_string_passed = action_HTML != "";
        var HTML = "<table class='tooltip'>";
        if (help_string_passed) {
            HTML += "<tr><td>" + this.square('basic', 'icon', 'help', 'in_tooltip') + "</td><td class='help in_tooltip'>" + help_HTML + "</td></tr>";
        }
        if (action_string_passed) {
            HTML += "<tr><td>" + this.square('basic', 'icon', 'action', 'in_tooltip') + "</td><td class='action in_tooltip'>" + action_HTML + "</td></tr>";
        }
        HTML += "</table>";
        return HTML;
    };
    Innovation.prototype.addCustomTooltip = function (nodeId, help_HTML, action_HTML) {
        // TODO(LATER): Pass 0 instead of undefined when using a desktop so that tooltips are faster.
        this.addTooltipHtml(nodeId, this.shapeTooltip(help_HTML, action_HTML), undefined);
    };
    Innovation.prototype.addCustomTooltipToClass = function (cssClass, help_HTML, action_HTML) {
        // TODO(LATER): Pass 0 instead of undefined when using a desktop so that tooltips are faster.
        this.addTooltipHtmlToClass(cssClass, this.shapeTooltip(help_HTML, action_HTML), undefined);
    };
    Innovation.prototype.addTooltipForCard = function (card) {
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
        var HTML_class = isFountain(card.id) || isFlag(card.id) ? 'S recto' : zone.HTML_class;
        var HTML_id = this.getCardHTMLId(card.id, card.age, card.type, card.is_relic, HTML_class);
        // Special achievement
        if (isMuseum(card.id) && card.location === 'museums' && card.owner != 0) {
            // No tooltip
        }
        else if (card.age === null) {
            this.addCustomTooltip(HTML_id, this.getSpecialAchievementText(card), "");
        }
        else {
            this.addCustomTooltip(HTML_id, this.getTooltipForCard(card.id), "");
        }
    };
    Innovation.prototype.getTooltipForCard = function (card_id) {
        if (this.saved_HTML_cards[card_id] === undefined) {
            var card = this.cards[card_id];
            this.saved_HTML_cards[card_id] = this.createCard(card_id, card.age, card.type, card.is_relic, "L card", card);
        }
        return this.saved_HTML_cards[card_id];
    };
    Innovation.prototype.addTooltipForStandardAchievement = function (card) {
        var zone = this.getZone(card['location'], card.owner, card.type, card.age);
        var id = this.getCardIdFromPosition(zone, card.position, card.age, card.type, card.is_relic);
        var HTML_id = this.getCardHTMLId(id, card.age, card.type, card.is_relic, zone.HTML_class);
        // TODO(LATER): Update this tooltip when a player already has at least one of this age achieved.
        var condition_for_claiming = dojo.string.substitute(_('You can take an action to claim this age if you have at least ${n} points in your score pile and at least one top card of value equal or higher than ${age} on your board.'), { 'age': this.square('N', 'age', card.age), 'n': 5 * card.age });
        this.addCustomTooltip(HTML_id, "<div class='under L_recto'>" + condition_for_claiming + "</div>", '');
    };
    Innovation.prototype.createAdjustedContent = function (content, HTML_class, size, font_max, width_margin, height_margin, div_id) {
        if (width_margin === void 0) { width_margin = 0; }
        if (height_margin === void 0) { height_margin = 0; }
        if (div_id === void 0) { div_id = null; }
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
        if (div_id == null) {
            return "<div class='".concat(HTML_class, " ").concat(size, "'><span class='font_size_").concat(font_size, "'>").concat(content, "</span></div>");
        }
        return "<div id='".concat(div_id, "' class='").concat(HTML_class, " ").concat(size, "'><span class='font_size_").concat(font_size, "'>").concat(content, "</span></div>");
    };
    Innovation.prototype.createDogmaEffectText = function (text, dogma_symbol, size, color, shade, other_classes) {
        return "<div class='effect ".concat(size, " ").concat(shade, " ").concat(other_classes, "'><span class='dogma_symbol color_").concat(color, " ").concat(size, " icon_").concat(dogma_symbol, "'></span><span class='effect_text ").concat(shade, " ").concat(size, "'>").concat(this.parseForRichedText(text, size), "<span></div>");
    };
    Innovation.prototype.parseForRichedText = function (text, size) {
        if (text == null) {
            return '';
        }
        text = text.replace(new RegExp("\\$\\{I demand\\}", "g"), "<strong class='i_demand'>" + _("I DEMAND") + "</strong>");
        text = text.replace(new RegExp("\\$\\{I compel\\}", "g"), "<strong class='i_compel'>" + _("I COMPEL") + "</strong>");
        text = text.replace(new RegExp("\\$\\{immediately\\}", "g"), "<strong class='immediately'>" + _("immediately") + "</strong>");
        text = text.replace(new RegExp("\\$\\{icons_1_to_6\\}", "g"), this.all_icons(6, 'in_tooltip'));
        for (var age = 1; age <= 11; age++) {
            text = text.replace(new RegExp("\\$\\{age_" + age + "\\}", "g"), this.square(size, 'age', age));
        }
        for (var symbol = 0; symbol <= 13; symbol++) {
            text = text.replace(new RegExp("\\$\\{icon_" + symbol + "\\}", "g"), this.square(size, 'icon', symbol));
        }
        text = text.replace(new RegExp("\\$\\{music_note\\}", "g"), this.square(size, 'music', 'note'));
        return text;
    };
    /*
    * Tooltip management for cards
    */
    Innovation.prototype.addTooltipsWithoutActionsTo = function (nodes) {
        var self = this;
        nodes.forEach(function (node) {
            var HTML_id = dojo.attr(node, "id");
            var id = self.getCardIdFromHTMLId(HTML_id);
            var HTML_help = self.saved_HTML_cards[id];
            self.addCustomTooltip(HTML_id, HTML_help, "");
        });
    };
    Innovation.prototype.addTooltipsWithoutActionsToMyHand = function () {
        this.addTooltipsWithoutActionsTo(this.selectMyCardsInHand());
    };
    Innovation.prototype.addTooltipsWithoutActionsToMyForecast = function () {
        this.addTooltipsWithoutActionsTo(this.selectMyCardsInForecast(11));
    };
    Innovation.prototype.addTooltipsWithoutActionsToMyBoard = function () {
        this.addTooltipsWithoutActionsTo(this.selectAllCardsOnMyBoard());
    };
    Innovation.prototype.addTooltipsWithoutActionsToMyArtifacts = function () {
        this.addTooltipsWithoutActionsTo(this.selectArtifactOnDisplay());
        this.addTooltipsWithoutActionsTo(this.selectArtifactsInMuseums());
    };
    Innovation.prototype.addTooltipsWithActionsTo = function (nodes, action_text_function, extra_param_1, extra_param_2, extra_param_3) {
        var self = this;
        nodes.forEach(function (node) {
            var HTML_id = dojo.attr(node, "id");
            var id = self.getCardIdFromHTMLId(HTML_id);
            var HTML_help = self.saved_HTML_cards[id];
            var card = self.cards[id];
            var HTML_action = action_text_function(self, card, extra_param_1, extra_param_2, extra_param_3);
            self.addCustomTooltip(HTML_id, HTML_help, HTML_action);
        });
    };
    Innovation.prototype.addTooltipsWithActionsToMyHand = function (meld_info, city_draw_age, city_draw_type) {
        var cards = this.selectMyCardsInHand();
        this.addTooltipsWithActionsTo(cards, this.createActionTextForMeld, meld_info, city_draw_age, city_draw_type);
        var self = this;
        cards.forEach(function (card) {
            var HTML_id = dojo.attr(card, "id");
            var id = self.getCardIdFromHTMLId(HTML_id);
            dojo.attr(HTML_id, 'card_id', id);
        });
    };
    Innovation.prototype.addTooltipsWithActionsToMyForecast = function (max_age_to_promote) {
        var cards = this.selectMyCardsInForecast(max_age_to_promote);
        this.addTooltipsWithActionsTo(cards, this.createActionTextForMeld);
        var self = this;
        cards.forEach(function (card) {
            var HTML_id = dojo.attr(card, "id");
            var id = self.getCardIdFromHTMLId(HTML_id);
            dojo.attr(HTML_id, 'card_id', id);
        });
    };
    Innovation.prototype.addTooltipsWithActionsToBoard = function (cards, dogma_effect_info) {
        this.addTooltipsWithActionsTo(cards, this.createActionTextForDogma, dogma_effect_info, 'board');
        var self = this;
        cards.forEach(function (card) {
            var _a, _b, _c, _d, _e, _f;
            var HTML_id = dojo.attr(card, "id");
            var id = self.getCardIdFromHTMLId(HTML_id);
            dojo.attr(HTML_id, 'card_id', id);
            dojo.attr(HTML_id, 'max_age_for_endorse_payment', (_a = dogma_effect_info[id]) === null || _a === void 0 ? void 0 : _a.max_age_for_endorse_payment);
            dojo.attr(HTML_id, 'no_effect', (_b = dogma_effect_info[id]) === null || _b === void 0 ? void 0 : _b.no_effect);
            dojo.attr(HTML_id, 'non_demand_effect_players', (_c = dogma_effect_info[id]) === null || _c === void 0 ? void 0 : _c.players_executing_non_demand_effects.join(','));
            dojo.attr(HTML_id, 'echo_effect_players', (_d = dogma_effect_info[id]) === null || _d === void 0 ? void 0 : _d.players_executing_echo_effects.join(','));
            dojo.attr(HTML_id, 'sharing_players', (_e = dogma_effect_info[id]) === null || _e === void 0 ? void 0 : _e.sharing_players.join(','));
            dojo.attr(HTML_id, 'on_non_adjacent_board', (_f = dogma_effect_info[id]) === null || _f === void 0 ? void 0 : _f.on_non_adjacent_board);
        });
    };
    Innovation.prototype.addTooltipWithMeldActionToMyArtifacts = function (meld_info, city_draw_age, city_draw_type) {
        var cards = this.gamedatas.fourth_edition ? this.selectArtifactsInMuseums() : this.selectArtifactOnDisplay();
        this.addTooltipsWithActionsTo(cards, this.createActionTextForMeld, meld_info, city_draw_age, city_draw_type);
        var self = this;
        cards.forEach(function (card) {
            var HTML_id = dojo.attr(card, "id");
            var id = self.getCardIdFromHTMLId(HTML_id);
            dojo.attr(HTML_id, 'card_id', id);
        });
    };
    Innovation.prototype.addTooltipWithDogmaActionToMyArtifactOnDisplay = function (dogma_effect_info) {
        this.addTooltipsWithActionsTo(this.selectArtifactOnDisplayIfEligibleForDogma(), this.createActionTextForDogma, dogma_effect_info, 'display');
    };
    Innovation.prototype.addTooltipsWithSplayingActionsToColorsOnMyBoard = function (colors, colors_in_clear, splay_direction, splay_direction_in_clear) {
        var self = this;
        this.selectCardsOnMyBoardOfColors(colors).forEach(function (node) {
            var HTML_id = dojo.attr(node, "id");
            var id = self.getCardIdFromHTMLId(HTML_id);
            var HTML_help = self.saved_HTML_cards[id];
            var card = self.cards[id];
            // Search for the name of the color in clear
            var color_in_clear = '';
            for (var i = 0; i < colors.length; i++) {
                if (colors[i] = card.color) {
                    color_in_clear = colors_in_clear[i];
                    break;
                }
            }
            var HTML_action = self.createActionTextForCardInSplayablePile(card, color_in_clear, splay_direction, splay_direction_in_clear);
            self.addCustomTooltip(HTML_id, HTML_help, HTML_action);
        });
    };
    Innovation.prototype.createActionTextForMeld = function (self, card, meld_info, city_draw_age, city_draw_type) {
        // Calculate new score (score pile + bonus icons)
        var bonus_icons = [];
        for (var i = 0; i < 5; i++) {
            var pile_zone = self.zone["board"][self.player_id][i];
            var splay_direction = pile_zone.splay_direction;
            // If there are no cards in the stack yet, treat the pile as unsplayed
            if (pile_zone.items.length == 0) {
                splay_direction = 0;
            }
            if (i == card.color) {
                bonus_icons = bonus_icons.concat(self.getVisibleBonusIconsInPile(pile_zone.items, splay_direction, card));
            }
            else {
                bonus_icons = bonus_icons.concat(self.getVisibleBonusIconsInPile(pile_zone.items, splay_direction));
            }
        }
        var new_score = self.computeTotalScore(self.zone.score[self.player_id].items, bonus_icons);
        var HTML_action = "<p class='possible_action'>" + _("Click to meld this card.") + "<p>";
        // See if melding this card would cover another one
        var pile = self.zone["board"][self.player_id][card.color].items;
        var top_card = null;
        if (pile.length > 0) {
            var top_card_id = self.getCardIdFromHTMLId(pile[pile.length - 1].id);
            top_card = self.cards[top_card_id];
            if (self.gamedatas.cities_expansion_enabled || self.gamedatas.echoes_expansion_enabled) {
                HTML_action += dojo.string.substitute("<p>" + _("If you do, it will cover ${age} ${card_name}, you will have a total score of ${score}, and your new featured icon counts will be:") + "<p>", {
                    'age': self.square('N', 'age', top_card.age, 'type_' + top_card.type),
                    'card_name': "<span class='card_name'>" + _(top_card.name) + "</span>",
                    'score': new_score
                });
            }
            else {
                HTML_action += dojo.string.substitute("<p>" + _("If you do, it will cover ${age} ${card_name} and your new ressource counts will be:") + "<p>", {
                    'age': self.square('N', 'age', top_card.age, 'type_' + top_card.type),
                    'card_name': "<span class='card_name'>" + _(top_card.name) + "</span>"
                });
            }
        }
        else {
            if (self.gamedatas.cities_expansion_enabled || self.gamedatas.echoes_expansion_enabled) {
                HTML_action += "<p>" + dojo.string.substitute(_("If you do, you will have a total score of ${score} and your new featured icon counts will be:"), { 'score': new_score }) + "</p>";
            }
            else {
                HTML_action += "<p>" + _("If you do, your new ressource counts will be:") + "</p>";
            }
        }
        // Calculate new ressource count if this card is melded
        var current_icon_counts = new Map();
        var new_icon_counts = new Map();
        for (var icon = 1; icon <= 7; icon++) {
            var icon_count = self.counter["resource_count"][self.player_id][icon].getValue();
            current_icon_counts.set(icon, icon_count);
            new_icon_counts.set(icon, icon_count);
        }
        self.incrementMap(new_icon_counts, getAllIcons(card));
        if (top_card != null) {
            var splay_indicator = 'splay_indicator_' + self.player_id + '_' + top_card.color;
            var splay_direction = 0;
            for (var direction = 0; direction <= 4; direction++) {
                if (dojo.hasClass(splay_indicator, 'splay_' + direction)) {
                    splay_direction = direction;
                    break;
                }
            }
            self.decrementMap(new_icon_counts, getHiddenIconsWhenSplayed(top_card, splay_direction));
        }
        HTML_action += self.createSimulatedRessourceTable(current_icon_counts, new_icon_counts);
        var splay_icon_triggers_city_draw = false;
        var splay_icon_direction = 11 <= card.spot_3 && card.spot_3 <= 13 ? card.spot_3 - 10 : 11 <= card.spot_6 && card.spot_6 <= 13 ? card.spot_6 - 10 : null;
        if (city_draw_age != null && city_draw_type != null && splay_icon_direction != null) { // city_draw_age and city_draw_type will be null if this is a promotion or an initial meld (neither counts as a Meld action)
            var pile_zone = self.zone["board"][self.player_id][card.color];
            if (pile_zone.items.length >= 1 && splay_icon_direction != pile_zone.splay_direction) {
                splay_icon_triggers_city_draw = true;
            }
        }
        if (splay_icon_triggers_city_draw) {
            HTML_action += dojo.string.substitute("<p>" + _("You will also draw a ${age} since the arrow icon on this card will splay the pile in a new direction.") + "</p>", { 'age': self.square('N', 'age', city_draw_age, 'type_' + city_draw_type), });
        }
        else if (meld_info !== undefined && meld_info[card.id] !== undefined && meld_info[card.id].triggers_city_draw) {
            HTML_action += dojo.string.substitute("<p>" + _("You will also draw a ${age} since this Meld action will add a new color to your board.") + "</p>", { 'age': self.square('N', 'age', city_draw_age, 'type_' + city_draw_type), });
        }
        return HTML_action;
    };
    Innovation.prototype.createActionTextForDogma = function (self, card, dogma_effect_info, card_location) {
        var info = dogma_effect_info[card.id];
        // Some cards (i.e. Battleship Yamato and City cards) are not able to be executed
        if (!info) {
            return "";
        }
        var on_display = card_location == 'display';
        console.log(JSON.stringify(card));
        var exists_i_demand_effect = card.i_demand_effect !== null;
        var exists_i_compel_effect = card.i_compel_effect !== null;
        var exists_non_demand_effect = card.non_demand_effect_1 !== null;
        var can_endorse = dogma_effect_info[card.id].max_age_for_endorse_payment;
        var on_non_adjacent_board = dogma_effect_info[card.id].on_non_adjacent_board;
        if (info.no_effect) {
            return "<p class='warning'>" + _('Activating this card will have no effect.') + "</p>";
        }
        var HTML_action = "<p class='possible_action'>";
        var HTML_endorse_action = "<p class='possible_action'>";
        if (on_display) {
            if (self.gamedatas.fourth_edition) {
                HTML_action += _("Click 'Dogma' to execute the dogma effect(s) of this card.");
            }
            else {
                HTML_action += _("Click 'Dogma and Return' to execute the dogma effect(s) of this card.");
            }
        }
        else if (can_endorse) {
            if (self.gamedatas.fourth_edition) {
                HTML_action += dojo.string.substitute(_("Click and you will be given the option to either use a Dogma action targeting this card, or to use an Endorse action by junking a card of value ${age} or lower from your hand."), { 'age': self.square('N', 'age', dogma_effect_info[card.id].max_age_for_endorse_payment) });
            }
            else {
                HTML_action += dojo.string.substitute(_("Click and you will be given the option to either use a Dogma action targeting this card, or to use an Endorse action by tucking a card of value ${age} or lower from your hand."), { 'age': self.square('N', 'age', dogma_effect_info[card.id].max_age_for_endorse_payment) });
            }
        }
        else if (on_non_adjacent_board) {
            HTML_action += _("Click and you will be given the option to choose a card to return from your hand in order to execute the dogma effect(s) of this card.");
        }
        else {
            HTML_action += _("Click to execute the dogma effect(s) of this card.");
        }
        HTML_action += "</p>";
        if (can_endorse) {
            HTML_action += "<p>" + _("If you use a Dogma action:") + "</p>";
            HTML_endorse_action += "<p>" + _("If you use an Endorse action:") + "</p>";
        }
        else {
            HTML_action += "<p>" + _("If you do:") + "</p>";
        }
        HTML_action += "<ul class='recap_dogma'>";
        HTML_endorse_action += "<ul class='recap_dogma'>";
        if (info.num_echo_effects > 0) {
            if (info.players_executing_echo_effects.length == 1) {
                HTML_action += "<li>" + _("You will execute the echo effect(s) alone.") + "</li>";
                HTML_endorse_action += "<li>" + _("You will execute the echo effect(s) alone twice.") + "</li>";
            }
            else if (info.players_executing_echo_effects.length > 1) {
                var other_players = self.getOtherPlayersCommaSeparated(info.players_executing_echo_effects);
                HTML_action += "<li>" + dojo.string.substitute(_("${players} will share each echo effect before you execute it."), { 'players': other_players }) + "</li>";
                HTML_endorse_action += "<li>" + dojo.string.substitute(_("${players} will share each echo effect before you execute it twice."), { 'players': other_players }) + "</li>";
            }
        }
        if (exists_i_demand_effect) {
            if (info.players_executing_i_demand_effects.length == 0) {
                HTML_action += "<li>" + _("Nobody will execute the I demand effect.") + "</li>";
                HTML_endorse_action += "<li>" + _("Nobody will execute the I demand effect.") + "</li>";
            }
            else {
                var other_players = self.getOtherPlayersCommaSeparated(info.players_executing_i_demand_effects);
                HTML_action += "<li>" + dojo.string.substitute(_("${players} will execute the I demand effect."), { 'players': other_players }) + "</li>";
                HTML_endorse_action += "<li>" + dojo.string.substitute(_("${players} will execute the I demand effect twice."), { 'players': other_players }) + "</li>";
            }
        }
        if (exists_i_compel_effect) {
            if (info.players_executing_i_compel_effects.length == 0) {
                HTML_action += "<li>" + _("Nobody will execute the I compel effect.") + "</li>";
                HTML_endorse_action += "<li>" + _("Nobody will execute the I compel effect.") + "</li>";
            }
            else {
                var other_players = self.getOtherPlayersCommaSeparated(info.players_executing_i_compel_effects);
                HTML_action += "<li>" + dojo.string.substitute(_("${players} will execute the I compel effect."), { 'players': other_players }) + "</li>";
                HTML_endorse_action += "<li>" + dojo.string.substitute(_("${players} will execute the I compel effect twice."), { 'players': other_players }) + "</li>";
            }
        }
        if (exists_non_demand_effect) {
            if (info.players_executing_non_demand_effects.length == 1) {
                HTML_action += "<li>" + _("You will execute the non-demand effect(s) alone.") + "</li>";
                HTML_endorse_action += "<li>" + _("You will execute the non-demand effect(s) alone twice.") + "</li>";
            }
            else if (info.players_executing_non_demand_effects.length > 1) {
                var other_players = self.getOtherPlayersCommaSeparated(info.players_executing_non_demand_effects);
                HTML_action += "<li>" + dojo.string.substitute(_("${players} will share each non-demand effect before you execute it."), { 'players': other_players }) + "</li>";
                HTML_endorse_action += "<li>" + dojo.string.substitute(_("${players} will share each non-demand effect before you execute it twice."), { 'players': other_players }) + "</li>";
            }
        }
        if (on_display) {
            HTML_action += "<li>" + _("You will return this Artifact afterwards.") + "</li>";
        }
        HTML_action += "</ul>";
        if (can_endorse) {
            HTML_action += HTML_endorse_action + "</ul>";
        }
        return HTML_action;
    };
    Innovation.prototype.getOtherPlayersCommaSeparated = function (player_ids) {
        var players = [];
        for (var i = 0; i < player_ids.length; i++) {
            if (player_ids[i] != this.player_id) {
                var player = $('name_' + player_ids[i]).outerHTML.replace("<p", "<span class='name_in_tooltip'").replace("</p", "</span");
                players.push(player);
            }
        }
        return players.join(', ');
    };
    Innovation.prototype.createActionTextForCardInSplayablePile = function (card, color_in_clear, splay_direction, splay_direction_in_clear) {
        var pile = this.zone["board"][this.player_id][card.color].items;
        var splay_indicator = 'splay_indicator_' + this.player_id + '_' + card.color;
        var current_splay_direction = 0;
        for (var direction = 0; direction <= 4; direction++) {
            if (dojo.hasClass(splay_indicator, 'splay_' + direction)) {
                current_splay_direction = direction;
                break;
            }
        }
        // Calculate new resource count if the splay direction changes
        var current_icon_counts = new Map();
        var new_icon_counts = new Map();
        for (var icon = 1; icon <= 7; icon++) {
            var icon_count = this.counter["resource_count"][this.player_id][icon].getValue();
            current_icon_counts.set(icon, icon_count);
            new_icon_counts.set(icon, icon_count);
        }
        for (var i = 0; i < pile.length - 1; i++) { // all cards except top one
            var pile_card = this.cards[this.getCardIdFromHTMLId(pile[i].id)];
            this.decrementMap(new_icon_counts, getHiddenIconsWhenSplayed(pile_card, current_splay_direction));
            this.incrementMap(new_icon_counts, getHiddenIconsWhenSplayed(pile_card, splay_direction));
        }
        // Calculate new score (score pile + bonus icons)
        var bonus_icons = [];
        for (var i = 0; i < 5; i++) {
            var pile_zone = this.zone["board"][this.player_id][i];
            if (i == card.color) {
                bonus_icons = bonus_icons.concat(this.getVisibleBonusIconsInPile(pile_zone.items, splay_direction));
            }
            else {
                bonus_icons = bonus_icons.concat(this.getVisibleBonusIconsInPile(pile_zone.items, pile_zone.splay_direction));
            }
        }
        var new_score = this.computeTotalScore(this.zone["score"][this.player_id].items, bonus_icons);
        var HTML_action = "<p class='possible_action'>" + dojo.string.substitute(_("Click to splay your ${color} stack ${direction}."), { 'color': _(color_in_clear), 'direction': _(splay_direction_in_clear) }) + "<p>";
        if (this.gamedatas.cities_expansion_enabled || this.gamedatas.echoes_expansion_enabled) {
            HTML_action += "<p>" + dojo.string.substitute(_("If you do, you will have a total score of ${score} and your new featured icon counts will be:"), { 'score': new_score }) + "</p>";
        }
        else {
            HTML_action += "<p>" + _("If you do, your new ressource counts will be:") + "</p>";
        }
        HTML_action += this.createSimulatedRessourceTable(current_icon_counts, new_icon_counts);
        return HTML_action;
    };
    /** Returns all visible bonus icons in a particular pile, optionally pretending a card is placed on top of the pile */
    Innovation.prototype.getVisibleBonusIconsInPile = function (pile, splay_direction, card_being_melded) {
        if (card_being_melded === void 0) { card_being_melded = null; }
        var bonus_icons = [];
        // Top card
        var top_card = null;
        if (pile.length > 0) {
            top_card = this.cards[this.getCardIdFromHTMLId(pile[pile.length - 1].id)];
        }
        if (card_being_melded != null) {
            top_card = card_being_melded;
        }
        if (top_card != null) {
            bonus_icons.concat(getBonusIconValues(getAllIcons(top_card)));
        }
        // Cards underneath
        var pile_length = card_being_melded == null ? pile.length : pile.length + 1;
        for (var i = 0; i < pile_length - 1; i++) {
            var pile_card = this.cards[this.getCardIdFromHTMLId(pile[i].id)];
            bonus_icons.concat(getBonusIconValues(this.getVisibleBonusIconsInPile(pile_card, splay_direction)));
        }
        return bonus_icons.filter(function (val) { return val > 0; }); // Remove the zeroes
    };
    /** Computes what the player's total score would be given a score pile and list of bonus icons.  */
    Innovation.prototype.computeTotalScore = function (score_pile, bonus_icons) {
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
    };
    /** Counts how many of a particular icon is visible in a specific pile */
    Innovation.prototype.countVisibleIconsInPile = function (pile, splay_direction, icon) {
        var count = 0;
        // Top card
        if (pile.length > 0) {
            var card = this.cards[this.getCardIdFromHTMLId(pile[pile.length - 1].id)];
            count += countMatchingIcons(getAllIcons(card), icon);
        }
        // Cards underneath
        for (var i = 0; i < pile.length - 1; i++) {
            var card = this.cards[this.getCardIdFromHTMLId(pile[i].id)];
            count += countMatchingIcons(getVisibleIconsWhenSplayed(card, splay_direction), icon);
        }
        return count;
    };
    Innovation.prototype.createSimulatedRessourceTable = function (current_icon_counts, new_icon_counts) {
        var _a, _b;
        var table = dojo.create('table', { 'class': 'ressource_table' });
        var symbol_line = dojo.create('tr', null, table);
        var count_line = dojo.create('tr', null, table);
        var max_icon = this.gamedatas.fourth_edition ? 7 : 6;
        for (var icon = 1; icon <= max_icon; icon++) {
            var current_count = (_a = current_icon_counts.get(icon)) !== null && _a !== void 0 ? _a : 0;
            var new_count = (_b = new_icon_counts.get(icon)) !== null && _b !== void 0 ? _b : 0;
            var comparator = new_count == current_count ? 'equal' : (new_count > current_count ? 'more' : 'less');
            dojo.place('<td><div class="ressource with_white_border ressource_' + icon + ' square P icon_' + icon + '"></div></td>', symbol_line);
            dojo.place('<td><div class="ressource with_white_border' + icon + ' ' + comparator + '">&nbsp;&#8239;' + new_count + '</div></td>', count_line);
        }
        return table.outerHTML;
    };
    /*
    * Selectors for connect event, usable to use with this.on, this.off functions and .addClass and .removeClass methods
    */
    Innovation.prototype.selectAllCards = function () {
        return dojo.query(".card, .recto");
    };
    Innovation.prototype.selectMyCardsInHand = function () {
        return dojo.query("#hand_" + this.player_id + " > .card");
    };
    Innovation.prototype.selectMyCardsInForecast = function (max_age_to_promote) {
        var queries = [];
        for (var age = 1; age <= max_age_to_promote; age++) {
            queries.push("#my_forecast_verso > .age_" + age);
        }
        return dojo.query(queries.join(","));
    };
    Innovation.prototype.selectMyCardBacksInForecast = function (max_age_to_promote) {
        if (max_age_to_promote === void 0) { max_age_to_promote = 11; }
        var queries = [];
        for (var age = 1; age <= max_age_to_promote; age++) {
            queries.push("#forecast_".concat(this.player_id, " > .age_").concat(age));
        }
        return dojo.query(queries.join(","));
    };
    Innovation.prototype.selectArtifactOnDisplay = function () {
        return dojo.query("#display_" + this.player_id + " > .card");
    };
    Innovation.prototype.selectArtifactOnDisplayIfEligibleForDogma = function () {
        var cards = dojo.query("#display_" + this.player_id + " > .card");
        // Battleship Yamato does not have any icons on it so it cannot be executed
        if (cards.length > 0 && this.getCardIdFromHTMLId(cards[0].id) == 188) {
            cards.pop();
        }
        return cards;
    };
    Innovation.prototype.selectArtifactsInMuseums = function () {
        return dojo.query("#museums_" + this.player_id + " > .card:nth-child(2n)");
    };
    Innovation.prototype.selectAllCardsOnMyBoard = function () {
        return dojo.query("#board_" + this.player_id + " .card");
    };
    Innovation.prototype.selectCardsOnMyBoardOfColors = function (colors) {
        var queries = [];
        for (var i = 0; i < colors.length; i++) {
            var color = colors[i];
            queries.push("#board_" + this.player_id + "_" + color + " .card");
        }
        return dojo.query(queries.join(","));
    };
    Innovation.prototype.selectTopCardsEligibleForDogma = function (player_ids) {
        var list = new dojo.NodeList();
        for (var i = 0; i < player_ids.length; i++) {
            var player_board = this.zone["board"][player_ids[i]];
            for (var color = 0; color < 5; color++) {
                var pile = player_board[color].items;
                if (pile.length == 0) {
                    continue;
                }
                var top_card = pile[pile.length - 1];
                var card_id = this.getCardIdFromHTMLId(top_card.id);
                // Only cards with a featured icon can be dogma'd
                if (Number(this.cards[card_id].dogma_icon) != 0) {
                    list.push(dojo.byId(top_card.id));
                }
            }
        }
        return list;
    };
    Innovation.prototype.selectMyTopCardsEligibleForEndorsedDogma = function (dogma_effect_info) {
        var _a;
        var player_board = this.zone["board"][this.player_id];
        var list = new dojo.NodeList();
        for (var color = 0; color < 5; color++) {
            var pile = player_board[color].items;
            if (pile.length == 0) {
                continue;
            }
            var top_card = pile[pile.length - 1];
            var card_id = this.getCardIdFromHTMLId(top_card.id);
            // Only cards with a featured icon can be dogma'd
            if (Number(this.cards[card_id].dogma_icon) != 0 && ((_a = dogma_effect_info[card_id]) === null || _a === void 0 ? void 0 : _a.max_age_for_endorse_payment)) {
                list.push(dojo.byId(top_card.id));
            }
        }
        return list;
    };
    Innovation.prototype.selectMyCardsEligibleForEndorsedDogmaPayment = function (max_age_for_endorse_payment) {
        var queries = [];
        for (var age = 1; age <= max_age_for_endorse_payment; age++) {
            queries.push("#hand_".concat(this.player_id, " > .age_").concat(age));
        }
        return dojo.query(queries.join(","));
    };
    Innovation.prototype.selectClaimableStandardAchievements = function (claimable_ages) {
        var queries = [];
        for (var i = 0; i < claimable_ages.length; i++) {
            var age = claimable_ages[i];
            queries.push("#achievements > .age_" + age);
        }
        return dojo.query(queries.join(","));
    };
    Innovation.prototype.selectClaimableSecrets = function (claimable_ages) {
        var queries = [];
        for (var i = 0; i < claimable_ages.length; i++) {
            var age = claimable_ages[i];
            queries.push("#safe_".concat(this.player_id, " > .age_").concat(age));
        }
        return dojo.query(queries.join(","));
    };
    Innovation.prototype.selectDrawableCard = function (age_to_draw, type_to_draw) {
        var deck_to_draw_in = this.zone["deck"][type_to_draw][age_to_draw].items;
        var top_card = deck_to_draw_in[deck_to_draw_in.length - 1];
        return dojo.query("#" + top_card.id);
    };
    Innovation.prototype.selectCardsFromList = function (cards) {
        if (cards.length == 0) {
            return null;
        }
        var identifiers = [];
        for (var i = 0; i < cards.length; i++) {
            var card = cards[i];
            if (card.age === null) {
                identifiers.push("#" + this.getCardHTMLId(card.id, card.age, card.type, card.is_relic, "S card"));
            }
            else {
                identifiers.push("#" + this.getCardHTMLId(card.id, card.age, card.type, card.is_relic, "M card"));
            }
        }
        return dojo.query(identifiers.join(","));
    };
    Innovation.prototype.selectRectosFromList = function (recto_positional_infos_array) {
        if (recto_positional_infos_array.length == 0) {
            return null;
        }
        var identifiers = [];
        for (var i = 0; i < recto_positional_infos_array.length; i++) {
            var card = recto_positional_infos_array[i];
            var zone = this.getZone(card['location'], card.owner, card.type, card.age);
            var id = this.getCardIdFromPosition(zone, card.position, card.age, card.type, card.is_relic);
            identifiers.push("#" + this.getCardHTMLId(id, card.age, card.type, card.is_relic, zone.HTML_class));
        }
        return dojo.query(identifiers.join(","));
    };
    /*
    * Deactivate all click events
    */
    Innovation.prototype.deactivateClickEvents = function () {
        this.deactivated_cards = dojo.query(".clickable");
        this.deactivated_cards.removeClass("clickable");
        this.deactivated_cards_mid_dogma = dojo.query(".mid_dogma");
        this.deactivated_cards_mid_dogma.removeClass("mid_dogma");
        this.deactivated_cards_can_endorse = dojo.query(".can_endorse");
        this.deactivated_cards_can_endorse.removeClass("can_endorse");
        this.off(this.deactivated_cards, 'onclick');
        this.erased_pagemaintitle_text = $('pagemaintitletext').innerHTML;
        dojo.query('#generalactions > .action-button, .extra_text').addClass('hidden'); // Hide buttons
        $('pagemaintitletext').innerHTML = _("Move recorded. Waiting for update...");
    };
    Innovation.prototype.resurrectClickEvents = function (revert_text) {
        this.deactivated_cards.addClass("clickable");
        this.deactivated_cards_mid_dogma.addClass("mid_dogma");
        this.deactivated_cards_can_endorse.addClass("can_endorse");
        this.restart(this.deactivated_cards, 'onclick');
        dojo.query('#generalactions > .action-button, .extra_text').removeClass('hidden'); // Show buttons again
        if (revert_text) {
            $('pagemaintitletext').innerHTML = this.erased_pagemaintitle_text;
        }
    };
    Innovation.prototype.getCardSizeInZone = function (zone_HTML_class) {
        return zone_HTML_class.split(' ')[0];
    };
    Innovation.prototype.getCardTypeInZone = function (zone_HTML_class) {
        return zone_HTML_class.split(' ')[1];
    };
    Innovation.prototype.getZone = function (location, owner, type, age, color) {
        var root = this.zone[location];
        switch (location) {
            case "deck":
                return root[type][age];
            case "relics":
            case "junk":
                return root[0];
            case "museums":
                if (owner == 0) {
                    return age === null ? this.zone["available_museums"][0] : this.zone[location][0];
                }
                else {
                    return root[owner];
                }
            case "achievements":
                if (owner == 0) {
                    return age === null ? this.zone["special_achievements"][0] : this.zone["achievements"][0];
                }
                else {
                    return root[owner];
                }
            case "hand":
            case "display":
            case "forecast":
            case "score":
            case "revealed":
            case "safe":
                return root[owner];
            case "board":
                return root[owner][color];
        }
    };
    Innovation.prototype.getCardIdFromPosition = function (zone, position, age, type, is_relic) {
        // For relics we use the real IDs (since there is only one of each age)
        if (parseInt(is_relic) == 1) {
            return 212 + parseInt(age);
        }
        if (!zone.grouped_by_age_type_and_is_relic) {
            return this.getCardIdFromHTMLId(zone.items[position].id);
        }
        // A relative position makes it easy to decide if this new card should go before or after another card.
        // The cards are sorted by age, breaking ties by their type, and then breaking ties with non-relics first.
        var relative_position = ((parseInt(age) * 6) + parseInt(type)) * 2 + parseInt(is_relic);
        var p = 0;
        for (var i = 0; i < zone.items.length; i++) {
            var item = zone.items[i];
            var item_age = this.getCardAgeFromHTMLId(item.id);
            var item_type = this.getCardTypeFromHTMLId(item.id);
            var item_is_relic = this.getCardIsRelicFromHTMLId(item.id);
            var item_relative_position = ((item_age * 6) + item_type) * 2 + item_is_relic;
            if (item_relative_position < relative_position) {
                continue;
            }
            if (p == position) {
                return this.getCardIdFromHTMLId(item.id);
            }
            p++;
        }
        return undefined;
    };
    Innovation.prototype.getCardPositionFromId = function (zone, id, age, type, is_relic) {
        if (!zone.grouped_by_age_type_and_is_relic) {
            for (var p_1 = 0; p_1 < zone.items.length; p_1++) {
                var item = zone.items[p_1];
                if (this.getCardIdFromHTMLId(item.id) == id) {
                    return p_1;
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
        return undefined;
    };
    Innovation.prototype.getCardHTMLIdFromEvent = function (event) {
        return dojo.getAttr(event.currentTarget, 'id');
    };
    Innovation.prototype.getCardHTMLId = function (id, age, type, is_relic, zone_HTML_class) {
        return ["item_" + id, "age_" + age, "type_" + type, "is_relic_" + parseInt(is_relic), zone_HTML_class.replace(" ", "__")].join("__");
    };
    Innovation.prototype.getCardHTMLClass = function (id, age, type, is_relic, card, zone_HTML_class) {
        var simplified_card_layout = this.prefs[111].value == 1;
        var classes = ["item_" + id, "age_" + age, "type_" + type, zone_HTML_class];
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
    };
    Innovation.prototype.getCardIdFromHTMLId = function (HTML_id) {
        return parseInt(HTML_id.split("__")[0].substr(5));
    };
    Innovation.prototype.getCardAgeFromHTMLId = function (HTML_id) {
        return parseInt(HTML_id.split("__")[1].substr(4));
    };
    Innovation.prototype.getCardTypeFromHTMLId = function (HTML_id) {
        return parseInt(HTML_id.split("__")[2].substr(5));
    };
    Innovation.prototype.getCardIsRelicFromHTMLId = function (HTML_id) {
        return parseInt(HTML_id.split("__")[3].substr(9));
    };
    /*
    * Card creation
    */
    Innovation.prototype.createCard = function (id, age, type, is_relic, zone_HTML_class, card) {
        var HTML_id = this.getCardHTMLId(id, age, type, is_relic, zone_HTML_class);
        var HTML_class = this.getCardHTMLClass(id, age, type, is_relic, card, zone_HTML_class);
        var size = this.getCardSizeInZone(zone_HTML_class);
        // TODO(4E): Use real 4th edition card back
        var simplified_card_back = this.prefs[110].value == 2 || age == 11 || type == 5;
        var HTML_inside = '';
        if (card === null) {
            if (age === null || !simplified_card_back) {
                HTML_inside = '';
            }
            else {
                HTML_inside = "<span class='card_back_text " + HTML_class + "'>" + age + "</span>";
            }
        }
        else {
            if (isFountain(card.id)) {
                HTML_inside = "<span class=\"square in_tooltip icon_9 fountain_flag_card color_".concat(card.color, "\"></span>");
            }
            else if (isFlag(card.id)) {
                HTML_inside = "<span class=\"square in_tooltip icon_8 fountain_flag_card color_".concat(card.color, "\"></span>");
            }
            else if (isMuseum(card.id)) {
                HTML_inside = '';
            }
            else {
                HTML_inside = this.writeOverCard(card, size, HTML_id);
            }
        }
        var card_type = "";
        if (size == 'L') {
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
                case 4:
                    card_type = "<div class='card_type'>" + _("This card is from the Figures in the Sand expansion.") + "</div>";
                    break;
                case 5:
                    card_type = "<div class='card_type'>" + _("This card is from the Unseen expansion.") + "</div>";
            }
        }
        var graphics_class = age === null ? "" : simplified_card_back ? "simplified_card_back" : "default_card_back";
        return "<div id='" + HTML_id + "' class='" + graphics_class + " " + HTML_class + "'>" + HTML_inside + "</div>" + card_type;
    };
    Innovation.prototype.createCardForCardBrowser = function (id) {
        var card = this.cards[id];
        var HTML_class = this.getCardHTMLClass(id, card.age, card.type, card.is_relic, card, 'M card');
        var HTML_id = "browse_card_id_".concat(id);
        var HTML_inside = this.writeOverCard(card, 'M', HTML_id);
        var simplified_card_back = this.prefs[110].value == 2;
        var graphics_class = simplified_card_back ? "simplified_card_back" : "default_card_back";
        return "<div id='".concat(HTML_id, "' class='").concat(graphics_class, " ").concat(HTML_class, "'>").concat(HTML_inside, "</div>");
    };
    Innovation.prototype.writeOverCard = function (card, size, HTML_id) {
        var card_data = this.cards[card.id];
        var icon1 = this.getIconDiv(card_data, card_data.spot_1, 'top_left_icon', size);
        var icon2 = this.getIconDiv(card_data, card_data.spot_2, 'bottom_left_icon', size);
        var icon3 = this.getIconDiv(card_data, card_data.spot_3, 'bottom_center_icon', size);
        var icon4 = this.getIconDiv(card_data, card_data.spot_4, 'bottom_right_icon', size);
        var icon5 = this.getIconDiv(card_data, card_data.spot_5, 'top_right_icon', size);
        var icon6 = this.getIconDiv(card_data, card_data.spot_6, 'top_center_icon', size);
        var card_age = this.createAdjustedContent(card.faceup_age, 'card_age type_' + card_data.type + ' color_' + card_data.color, size, size == 'M' ? (card.age >= 10 ? 7 : 9) : 30);
        var title = _(card_data.name).toUpperCase();
        var card_title = this.createAdjustedContent(title, 'card_title type_' + card_data.type, size, size == 'M' ? 11 : 30, /*width_margin=*/ 0, /*height_margin=*/ 0, HTML_id + '_card_title');
        var i_demand_effect = card_data.i_demand_effect ? this.createDogmaEffectText(_(card_data.i_demand_effect), card.dogma_icon, size, card.color, 'dark', 'i_demand_effect color_' + card.color) : "";
        var i_compel_effect = card_data.i_compel_effect ? this.createDogmaEffectText(_(card_data.i_compel_effect), card.dogma_icon, size, card.color, 'dark', 'i_compel_effect color_' + card.color) : "";
        var non_demand_effect_1 = card_data.non_demand_effect_1 ? this.createDogmaEffectText(_(card_data.non_demand_effect_1), card.dogma_icon, size, card.color, 'light', 'non_demand_effect_1 color_' + card.color) : "";
        var non_demand_effect_2 = card_data.non_demand_effect_2 ? this.createDogmaEffectText(_(card_data.non_demand_effect_2), card.dogma_icon, size, card.color, 'light', 'non_demand_effect_2 color_' + card.color) : "";
        var non_demand_effect_3 = card_data.non_demand_effect_3 ? this.createDogmaEffectText(_(card_data.non_demand_effect_3), card.dogma_icon, size, card.color, 'light', 'non_demand_effect_3 color_' + card.color) : "";
        var dogma_effects = this.createAdjustedContent(i_demand_effect + i_compel_effect + non_demand_effect_1 + non_demand_effect_2 + non_demand_effect_3, "card_effects", size, size == 'M' ? 8 : 17);
        return icon1 + icon2 + icon3 + icon4 + icon5 + icon6 + card_age + card_title + dogma_effects;
    };
    Innovation.prototype.getIconDiv = function (card, icon, icon_location, size) {
        if (icon === null || Number.isNaN(icon)) {
            return '';
        }
        if (icon == 0) {
            return "<div class=\"hexagon_card_icon ".concat(size, " ").concat(icon_location, " hexagon_icon_").concat(card.id, "\"></div>");
        }
        if (icon <= 7) {
            var div = "<div class=\"square_card_icon ".concat(size, " color_").concat(card.color, " ").concat(icon_location, " icon_").concat(icon, "\"></div>");
            if (icon != null && icon_location == 'top_center_icon' && (!this.gamedatas.fourth_edition || card.age <= 5)) {
                div += "<div class=\"city_search_icon ".concat(size, " color_").concat(card.color, "\"></div>");
            }
            return div;
        }
        if (icon == 10) {
            var div = this.createAdjustedContent(this.parseForRichedText(_(card.echo_effect), size), 'echo_effect light color_' + card.color + ' square_card_icon ' + size + ' ' + icon_location + ' icon_' + icon, size, size == 'M' ? 11 : 30);
            // Add "display: table;" styling after the size is computed, otherwise it messes up the calculation.
            return div.replace("div class", 'div style="display: table;" class');
        }
        if (icon >= 101) {
            return "<div class=\"bonus_card_icon ".concat(size, " ").concat(icon_location, " bonus_color color_").concat(card.color, "\"></div><div class=\"bonus_card_icon ").concat(size, " ").concat(icon_location, " bonus_value bonus_").concat(icon - 100, "\"></div>");
        }
        return "<div class=\"city_special_icon ".concat(size, " color_").concat(card.color, " ").concat(icon_location, " icon_").concat(icon, "\"></div>");
    };
    Innovation.prototype.getSpecialAchievementText = function (card) {
        if (isFountain(card.id)) {
            return _("This represents a visible fountain on your board which currently counts as an achievement.");
        }
        else if (isFlag(card.id)) {
            return _("This represents a visible flag on your board which currently counts as an achievement since no other player has more visible cards of this color.");
        }
        else if (isMuseum(card.id)) {
            if (card.location === 'achievements') {
                return _("This museum was earned as an achievement.");
            }
            else {
                return _("This museum is available.");
            }
        }
        var card_data = this.cards[card.id];
        var name = _(card_data.name).toUpperCase();
        var is_monument = card.id == 106;
        var note_for_monument = _("Note: Transfered cards from other players do not count toward this achievement, nor does exchanging cards from your hand and score pile.");
        var div_condition_for_claiming = "<div><b>" + name + "</b>: " + this.parseForRichedText(_(card_data.condition_for_claiming), 'in_tooltip') + "</div>" + (is_monument ? "<div></br>" + note_for_monument + "</div>" : "");
        var div_alternative_condition_for_claiming = "";
        if (card_data.alternative_condition_for_claiming != null) {
            div_alternative_condition_for_claiming = "</br><div>" + this.parseForRichedText(_(card_data.alternative_condition_for_claiming), 'in_tooltip') + "</div>";
        }
        return div_condition_for_claiming + div_alternative_condition_for_claiming;
    };
    /*
    * Zone management systemcard
    */
    Innovation.prototype.createZone = function (location, owner, type, age, color, grouped_by_age_type_and_is_relic, counter_method, counter_display_zero) {
        if (type === void 0) { type = null; }
        if (age === void 0) { age = null; }
        if (color === void 0) { color = null; }
        if (grouped_by_age_type_and_is_relic === void 0) { grouped_by_age_type_and_is_relic = null; }
        if (counter_method === void 0) { counter_method = null; }
        if (counter_display_zero === void 0) { counter_display_zero = null; }
        var owner_string = owner != 0 ? '_' + owner : '';
        var type_string = type !== null ? '_' + type : '';
        var age_string = age !== null ? '_' + age : '';
        var color_string = color !== null ? '_' + color : '';
        // Dimension of a card in the zone
        var new_location;
        if (location == "hand") {
            if (owner == this.player_id) {
                new_location = 'my_hand';
            }
            else {
                new_location = 'opponent_hand';
            }
        }
        else {
            new_location = location;
        }
        var HTML_class = this.HTML_class.get(new_location);
        var card_dimensions = this.card_dimensions[HTML_class];
        // Width of the zone
        var zone_width;
        if (['board', 'score', 'forecast', 'safe'].includes(new_location)) {
            zone_width = card_dimensions.width; // Will change dynamically
        }
        else if (new_location != 'relics' && new_location != 'achievements' && new_location != 'special_achievements') {
            var delta_x = this.delta[new_location].x;
            var n = this.num_cards_in_row.get(new_location);
            zone_width = card_dimensions.width + (n - 1) * delta_x;
        }
        // Id of the container
        var div_id = "";
        if (location == "my_score_verso" || location == "my_forecast_verso" || location == "junk") {
            div_id = location;
        }
        else {
            div_id = location + owner_string + type_string + age_string + color_string;
        }
        // Creation of the zone
        dojo.style(div_id, 'width', zone_width + 'px');
        dojo.style(div_id, 'height', card_dimensions.height + 'px');
        var zone = new ebg.zone();
        zone.create(this, div_id, card_dimensions.width, card_dimensions.height);
        zone.setPattern('grid');
        // Add information which identify the zone
        zone.location = new_location;
        zone.owner = owner;
        zone.HTML_class = HTML_class;
        zone.grouped_by_age_type_and_is_relic = grouped_by_age_type_and_is_relic;
        if (counter_method != null) {
            var counter_node = void 0;
            if (location == 'board') {
                counter_node = $('pile_count' + owner_string + color_string);
            }
            else {
                counter_node = $(location + '_count' + owner_string + type_string + age_string + color_string);
            }
            zone.counter = new ebg.counter();
            zone.counter.create(counter_node);
            zone.counter.setValue(0);
            if (!counter_display_zero) {
                dojo.style(zone.counter.span, 'visibility', 'hidden');
            }
            zone.counter.method = counter_method;
            zone.counter.display_zero = counter_display_zero;
        }
        else {
            zone.counter = null;
        }
        return zone;
    };
    Innovation.prototype.createAndAddToZone = function (zone, position, age, type, is_relic, id, start, card) {
        // id of the new item
        var visible_card;
        if (id === null) {
            // Recto
            visible_card = false;
            // For relics we use the real IDs (since there is only one of each age)
            if (parseInt(is_relic) == 1) {
                id = 212 + parseInt(age);
                // Create a new id based only on the visible properties of the card
            }
            else {
                id = this.uniqueIdForCard(age, type, is_relic);
            }
        }
        else {
            // verso
            if (zone.owner != 0 && zone.location == 'achievements' && !isFlag(id) && !isFountain(id) && !isMuseum(id)) {
                visible_card = false;
            }
            else {
                visible_card = true;
            }
        }
        // Create a new card and place it on start position
        var node = this.createCard(id, age, type, is_relic, zone.HTML_class, visible_card ? card : null);
        dojo.place(node, start);
        this.addToZone(zone, id, position, age, type, is_relic);
    };
    Innovation.prototype.moveBetweenZones = function (zone_from, zone_to, id_from, id_to, card) {
        // Handle case where card is being melded from the bottom of the pile (e.g. Seikilos Epitaph)
        if (card.location_from == "board" && card.location_to == "board" && card.owner_from == card.owner_to) {
            this.removeFromZone(zone_from, id_from, false, card.age, card.type, card.is_relic);
            this.addToZone(zone_to, id_to, card.position_to, card.age, card.type, card.is_relic);
        }
        else if (id_from == id_to && card.age !== null && zone_from.HTML_class == zone_to.HTML_class) {
            this.addToZone(zone_to, id_to, card.position_to, card.age, card.type, card.is_relic);
            this.removeFromZone(zone_from, id_from, false, card.age, card.type, card.is_relic);
        }
        else {
            this.createAndAddToZone(zone_to, card.position_to, card.age, card.type, card.is_relic, id_to, this.getCardHTMLId(id_from, card.age, card.type, card.is_relic, zone_from.HTML_class), card);
            this.removeFromZone(zone_from, id_from, true, card.age, card.type, card.is_relic);
        }
        this.updateDeckOpacities();
    };
    Innovation.prototype.addToZone = function (zone, id, position, age, type, is_relic) {
        var HTML_id = this.getCardHTMLId(id, age, type, is_relic, zone.HTML_class);
        dojo.style(HTML_id, 'position', 'absolute');
        if (zone.location == 'revealed' && zone.items.length == 0) {
            dojo.style(zone.container_div, 'display', 'block');
        }
        // A relative position makes it easy to decide if this new card should go before or after another card.
        // We want the cards sorted by age, breaking ties by their type, and then breaking ties by placing non-relics first.
        var relative_position = ((parseInt(age) * 6) + parseInt(type)) * 2 + parseInt(is_relic);
        // Update weights before adding and find the right spot to put the card according to its position, and age for not board stock
        var weight = -1;
        var p = 0;
        for (var i = 0; i < zone.items.length; i++) {
            var item = zone.items[i];
            var item_age = this.getCardAgeFromHTMLId(item.id);
            var item_type = this.getCardTypeFromHTMLId(item.id);
            var item_is_relic = this.getCardIsRelicFromHTMLId(item.id);
            var item_relative_position = ((item_age * 6) + item_type) * 2 + item_is_relic;
            // We have not reached the group the card can be put into
            if (zone.grouped_by_age_type_and_is_relic && item_relative_position < relative_position) {
                continue;
            }
            // We found the spot where the card belongs
            if (weight == -1 && zone.grouped_by_age_type_and_is_relic && item_relative_position > relative_position || p == position) {
                weight = i;
            }
            if (weight != -1) { // Increment positions of the cards after
                item.weight++;
                dojo.style(item.id, 'z-index', item.weight);
            }
            p++;
        }
        if (weight == -1) { // No spot for the card has been found after running all the stock
            // The card must be placed on last position
            weight = zone.items.length;
        }
        // Add the card
        dojo.style(HTML_id, 'z-index', weight);
        zone.placeInZone(HTML_id, weight);
        if (zone.location == 'board') {
            this.refreshSplay(zone, zone.splay_direction);
        }
        zone.updateDisplay();
        // Update count if applicable
        if (zone.counter !== null) {
            // Update the value in the associated counter
            var delta = void 0;
            switch (zone.counter.method) {
                case ("COUNT"):
                    delta = 1;
                    break;
                case ("SUM"):
                    delta = parseInt(age);
                    break;
            }
            zone.counter.incValue(delta);
            if (!zone.counter.display_zero) {
                dojo.style(zone.counter.span, 'visibility', zone.counter.getValue() == 0 ? 'hidden' : 'visible');
            }
        }
        this.updateDeckOpacities();
    };
    Innovation.prototype.removeFromZone = function (zone, id, destroy, age, type, is_relic) {
        var HTML_id = this.getCardHTMLId(id, age, type, is_relic, zone.HTML_class);
        // Update weights before removing
        var found = false;
        for (var i = 0; i < zone.items.length; i++) {
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
        if (zone.location == 'board') {
            this.refreshSplay(zone, zone.splay_direction);
        }
        else if (zone.location == 'revealed' && zone.items.length == 0) {
            zone = this.createZone('revealed', zone.owner, null, null, null); // Recreate the zone (Dunno why it does not work if I don't do that)
            dojo.style(zone.container_div, 'display', 'none');
        }
        zone.updateDisplay();
        // Update count if applicable
        if (zone.counter !== null) {
            // Update the value in the associated counter
            var delta = void 0;
            switch (zone.counter.method) {
                case ("COUNT"):
                    delta = -1;
                    break;
                case ("SUM"):
                    delta = -age;
                    break;
            }
            zone.counter.incValue(delta);
            if (!zone.counter.display_zero) {
                dojo.style(zone.counter.span, 'visibility', zone.counter.getValue() == 0 ? 'hidden' : 'visible');
            }
        }
        this.updateDeckOpacities();
    };
    Innovation.prototype.setPlacementRules = function (zone, left_to_right) {
        var self = this;
        zone.itemIdToCoordsGrid = function (i, control_width) {
            var w = self.card_dimensions[this.HTML_class].width;
            var h = self.card_dimensions[this.HTML_class].height;
            var delta = self.delta[this.location];
            var n = self.num_cards_in_row.get(this.location);
            var x_beginning = left_to_right ? 0 : control_width - w;
            var delta_x = left_to_right ? delta.x : -delta.x;
            var delta_y = delta.y;
            var n_x = i % n;
            var n_y = Math.floor(i / n);
            return { 'x': x_beginning + delta_x * n_x, 'y': delta_y * n_y, 'w': w, 'h': h };
        };
    };
    Innovation.prototype.setPlacementRulesForRelics = function () {
        var self = this;
        this.zone["relics"]["0"].itemIdToCoordsGrid = function (i, control_width) {
            var w = self.card_dimensions[this.HTML_class].width;
            var h = self.card_dimensions[this.HTML_class].height;
            var x = (i % 3) * (w + 5);
            if (i >= 3) {
                x = x + ((w + 5) / 2);
            }
            var y = Math.floor(i / 3) * (h + 5);
            return { 'x': x, 'y': y, 'w': w, 'h': h };
        };
    };
    Innovation.prototype.setPlacementRulesForAvailableMuseums = function () {
        var self = this;
        this.zone["available_museums"]["0"].itemIdToCoordsGrid = function (i, control_width) {
            var w = self.card_dimensions[this.HTML_class].width;
            var h = self.card_dimensions[this.HTML_class].height;
            var x = 25 + (i % 3) * (w + 5);
            if (i >= 3) {
                x = x + ((w + 5) / 2);
            }
            var y = Math.floor(i / 3) * (h + 5);
            return { 'x': x, 'y': y, 'w': w, 'h': h };
        };
    };
    Innovation.prototype.setPlacementRulesForPlayerMuseums = function (zone) {
        var self = this;
        zone.itemIdToCoordsGrid = function (i, control_width) {
            // Hide the museums under the artifacts
            var num_museums = 0;
            for (var j = 0; j <= i; j++) {
                if (isMuseum(self.getCardIdFromHTMLId(this.items[j].id))) {
                    num_museums++;
                }
            }
            var w = self.card_dimensions[this.HTML_class].width;
            var h = self.card_dimensions[this.HTML_class].height;
            var delta = self.delta[this.location];
            var n = self.num_cards_in_row.get(this.location);
            var x_beginning = 0;
            var delta_x = delta.x;
            var delta_y = delta.y;
            var n_x = (num_museums - 1) % n;
            var n_y = Math.floor((num_museums - 1) / n);
            return { 'x': x_beginning + delta_x * n_x, 'y': delta_y * n_y, 'w': w, 'h': h };
        };
    };
    // Reduce opacity of expansion decks if the accompanying base deck is empty.
    Innovation.prototype.updateDeckOpacities = function () {
        // NOTE: We delay this by 2 seconds in order to give enough time for the cards move around. If
        // we discover that this is buggy or if we want to build a less hacky solution, we should pass
        // data from the server side instead of calculating the deck sizes using childElementCount.
        setTimeout(function () {
            for (var a = 1; a <= 11; a++) {
                var opacity = document.getElementById("deck_0_".concat(a)).childElementCount > 0 ? '1.0' : '0.35';
                for (var t = 1; t <= 5; t++) {
                    var deck = document.getElementById("deck_".concat(t, "_").concat(a));
                    if (deck != null) {
                        deck.parentElement.style.opacity = opacity;
                    }
                }
            }
        }, 2000);
    };
    Innovation.prototype.setPlacementRulesForAchievements = function () {
        var self = this;
        this.zone["achievements"]["0"].itemIdToCoordsGrid = function (i, control_width) {
            var w = self.card_dimensions[this.HTML_class].width;
            var h = self.card_dimensions[this.HTML_class].height;
            var x = (i % 3) * (w + 5);
            var y = Math.floor(i / 3) * (h + 5);
            return { 'x': x, 'y': y, 'w': w, 'h': h };
        };
    };
    Innovation.prototype.setPlacementRulesForSpecialAchievements = function () {
        var self = this;
        this.zone["special_achievements"]["0"].itemIdToCoordsGrid = function (i, control_width) {
            var w = self.card_dimensions[this.HTML_class].width;
            var h = self.card_dimensions[this.HTML_class].height;
            var x = 0;
            var y = 0;
            // Row of 3
            if (i % 5 < 3) {
                x = (i % 5) * (w + 5);
                y = Math.floor(i / 5) * 2 * (h + 5);
                // Row of 2
            }
            else {
                if (i % 5 == 3) {
                    x = (w + 5) / 2;
                }
                else {
                    x = (w + 5) / 2 + (w + 5);
                }
                y = h + 5 + Math.floor(i / 5) * 2 * (h + 5);
            }
            return { 'x': x, 'y': y, 'w': w, 'h': h };
        };
    };
    Innovation.prototype.getPileIndicesWhichMustRemainVisible = function (zone, splay_direction, full_visible) {
        // Determine which cards must remain visible (skip calculations if all cards are going to be visible anyway)
        var indices = [];
        for (var i = 0; i < zone.items.length; i++) {
            var must_stay_visible = false;
            var card_id = this.getCardIdFromHTMLId(zone.items[i].id);
            var card = this.cards[card_id];
            if (full_visible) {
                must_stay_visible = true;
            }
            else if (i == zone.items.length - 1) { // top card
                must_stay_visible = true;
            }
            else if (splay_direction == 1 && card.spot_4 == 10) { // echo effect visible due to left splay
                must_stay_visible = true;
            }
            else if (splay_direction == 2 && (card.spot_1 == 10 || card.spot_2 == 10)) { // echo effect visible due to right splay
                must_stay_visible = true;
            }
            else if (splay_direction == 3 && (card.spot_2 == 10 || card.spot_3 == 10 || card.spot_4 == 10)) { // echo effect visible due to up splay
                must_stay_visible = true;
            }
            else if (splay_direction == 4 && (card.spot_1 == 10 || card.spot_2 == 10 || card.spot_3 == 10 || card.spot_4 == 10)) { // echo effect visible due to aslant splay
                must_stay_visible = true;
            }
            if (must_stay_visible) {
                indices.push(i);
            }
        }
        return indices;
    };
    Innovation.prototype.refreshSplay = function (zone, splay_direction, force_full_visible) {
        if (force_full_visible === void 0) { force_full_visible = false; }
        var self = this;
        var full_visible = force_full_visible || this.view_full;
        splay_direction = Number(splay_direction);
        zone.splay_direction = splay_direction;
        var overlap = this.display_mode ? this.expanded_overlap_for_splay : this.compact_overlap_for_splay;
        var overlap_if_expanded = this.expanded_overlap_for_splay;
        var visible_indices = this.getPileIndicesWhichMustRemainVisible(zone, splay_direction, full_visible);
        // Compute new width of zone
        var width;
        if (splay_direction == 0 || splay_direction == 3 || full_visible) {
            width = this.card_dimensions[zone.HTML_class].width;
        }
        else {
            var calculateWidth = function (small_overlap, big_overlap) {
                return self.card_dimensions[zone.HTML_class].width + (zone.items.length - visible_indices.length) * small_overlap + (visible_indices.length - 1) * big_overlap;
            };
            // Shrink overlap if the pile is going to be too wide
            var max_total_width = dojo.position('player_' + zone.owner).w - 25;
            var compact_overlap = this.compact_overlap_for_splay;
            // If compact mode isn't enough, then we also need to reduce the visibility on cards with echo effects
            if (calculateWidth(compact_overlap, overlap_if_expanded) > max_total_width) {
                overlap = compact_overlap;
                overlap_if_expanded = (max_total_width - self.card_dimensions[zone.HTML_class].width - (zone.items.length - visible_indices.length) * compact_overlap) / (visible_indices.length - 1);
            }
            else if (calculateWidth(overlap, overlap_if_expanded) > max_total_width) {
                overlap = (max_total_width - self.card_dimensions[zone.HTML_class].width - (visible_indices.length - 1) * overlap_if_expanded) / (zone.items.length - visible_indices.length);
            }
            width = calculateWidth(overlap, overlap_if_expanded);
        }
        dojo.setStyle(zone.container_div, 'width', width + "px");
        zone.itemIdToCoordsGrid = function (i, control_width) {
            var w = self.card_dimensions[this.HTML_class].width;
            var h = self.card_dimensions[this.HTML_class].height;
            var x_beginning = 0;
            var delta_x = 0;
            var delta_x_if_expanded = 0;
            var delta_y = 0;
            var delta_y_if_expanded = 0;
            var num_cards_expanded = 0;
            if (full_visible) {
                delta_y = h + 5;
                delta_y_if_expanded = h + 5;
                num_cards_expanded = i;
            }
            else {
                for (var j = 0; j < i; j++) {
                    if (visible_indices.indexOf(j) >= 0) {
                        num_cards_expanded++;
                    }
                }
                switch (splay_direction) {
                    case 0: // Unsplayed
                        delta_y = self.overlap_for_unsplayed;
                        delta_y_if_expanded = self.overlap_for_unsplayed;
                        break;
                    case 1: // Splayed left
                        x_beginning = control_width - w;
                        delta_x = -overlap;
                        delta_x_if_expanded = -overlap_if_expanded;
                        break;
                    case 2: // Splayed right
                        delta_x = overlap;
                        delta_x_if_expanded = overlap_if_expanded;
                        break;
                    case 3: // Splayed up
                        delta_y = overlap;
                        delta_y_if_expanded = overlap_if_expanded;
                        break;
                    case 4: // Splayed aslant
                        delta_x = overlap;
                        delta_x_if_expanded = overlap_if_expanded;
                        delta_y = overlap;
                        delta_y_if_expanded = overlap_if_expanded;
                        break;
                    default:
                        break;
                }
            }
            var num_cards_not_expanded = i - num_cards_expanded;
            var x = x_beginning + (delta_x * num_cards_not_expanded) + (delta_x_if_expanded * num_cards_expanded);
            var y = 0;
            if (full_visible) {
                y = delta_y * (this.items.length - 1 - i);
            }
            else if (splay_direction == 0) {
                y = delta_y * i;
            }
            else if (splay_direction == 1 || splay_direction == 2) {
                y = 0;
            }
            else {
                // When splayed up or aslant, we need to count the cards above instead of below
                num_cards_expanded = 0;
                for (var j = i + 1; j < this.items.length; j++) {
                    if (visible_indices.indexOf(j - 1) >= 0) {
                        num_cards_expanded++;
                    }
                }
                num_cards_not_expanded = this.items.length - i - num_cards_expanded - 1;
                y = delta_y * num_cards_not_expanded + delta_y_if_expanded * num_cards_expanded;
            }
            return { 'x': x, 'y': y, 'w': w, 'h': h };
        };
        zone.updateDisplay();
    };
    /*
    * Player panel management
    */
    Innovation.prototype.givePlayerActionCard = function (player_id, action_number) {
        dojo.addClass('action_indicator_' + player_id, 'action_card');
        var action_text = action_number == 0 ? _('Free Action') : action_number == 1 ? _('First Action') : _('Second Action');
        var div_action_text = this.createAdjustedContent(action_text, 'action_text', '', 12, 2);
        $('action_indicator_' + player_id).innerHTML = div_action_text;
    };
    Innovation.prototype.destroyActionCard = function () {
        var action_indicators = dojo.query('.action_indicator');
        action_indicators.forEach(function (node) {
            node.innerHTML = "";
        });
        action_indicators.removeClass('action_card');
    };
    ///////////////////////////////////////////////////
    //// Player's action
    /*
    
        Here, you are defining methods to handle player's action (ex: results of mouse click on
        game objects).
        
        Most of the time, these methods:
        _ check the action is possible at this game state.
        _ make a call to the game server
    
    */
    Innovation.prototype.action_clicForInitialMeld = function (event) {
        if (!this.checkAction('initialMeld')) {
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
        this.ajaxcall("/innovation/innovation/initialMeld.html", {
            lock: true,
            card_id: card_id
        }, this, function (result) { }, function (is_error) { self.resurrectClickEvents(is_error); });
    };
    Innovation.prototype.action_clickForUpdatedInitialMeld = function (event) {
        this.deactivateClickEvents();
        this.addTooltipsWithoutActionsToMyHand();
        this.addTooltipsWithoutActionsToMyBoard();
        dojo.query(".selected").removeClass("selected");
        var HTML_id = this.getCardHTMLIdFromEvent(event);
        var card_id = this.getCardIdFromHTMLId(HTML_id);
        dojo.addClass(HTML_id, "selected");
        var self = this;
        this.ajaxcall("/innovation/innovation/updateInitialMeld.html", {
            lock: true,
            card_id: card_id
        }, this, function (result) { }, function (is_error) { self.resurrectClickEvents(is_error); });
    };
    Innovation.prototype.action_clicForSeizeRelicToHand = function () {
        if (!this.checkAction('seizeRelicToHand')) {
            return;
        }
        this.deactivateClickEvents();
        var self = this;
        this.ajaxcall("/innovation/innovation/seizeRelicToHand.html", {
            lock: true
        }, this, function (result) { }, function (is_error) { if (is_error)
            self.resurrectClickEvents(true); });
    };
    Innovation.prototype.action_clicForSeizeRelicToAchievements = function () {
        if (!this.checkAction('seizeRelicToAchievements')) {
            return;
        }
        this.deactivateClickEvents();
        var self = this;
        this.ajaxcall("/innovation/innovation/seizeRelicToAchievements.html", {
            lock: true
        }, this, function (result) { }, function (is_error) { if (is_error)
            self.resurrectClickEvents(true); });
    };
    Innovation.prototype.action_clicForPassSeizeRelic = function () {
        if (!this.checkAction('passSeizeRelic')) {
            return;
        }
        this.deactivateClickEvents();
        var self = this;
        this.ajaxcall("/innovation/innovation/passSeizeRelic.html", {
            lock: true
        }, this, function (result) { }, function (is_error) { if (is_error)
            self.resurrectClickEvents(true); });
    };
    Innovation.prototype.action_clicForDogmaArtifact = function () {
        if (!this.checkAction('dogmaArtifactOnDisplay')) {
            return;
        }
        this.deactivateClickEvents();
        var self = this;
        this.ajaxcall("/innovation/innovation/dogmaArtifactOnDisplay.html", {
            lock: true
        }, this, function (result) { }, function (is_error) { if (is_error)
            self.resurrectClickEvents(true); });
    };
    Innovation.prototype.action_clicForReturnArtifact = function () {
        if (!this.checkAction('returnArtifactOnDisplay')) {
            return;
        }
        this.deactivateClickEvents();
        var self = this;
        this.ajaxcall("/innovation/innovation/returnArtifactOnDisplay.html", {
            lock: true
        }, this, function (result) { }, function (is_error) { if (is_error)
            self.resurrectClickEvents(true); });
    };
    Innovation.prototype.action_clicForPassArtifact = function () {
        if (!this.checkAction('passArtifactOnDisplay')) {
            return;
        }
        this.deactivateClickEvents();
        var self = this;
        this.ajaxcall("/innovation/innovation/passArtifactOnDisplay.html", {
            lock: true
        }, this, function (result) { }, function (is_error) { if (is_error)
            self.resurrectClickEvents(true); });
    };
    Innovation.prototype.action_clickForPassPromote = function () {
        if (!this.checkAction('passPromoteCard')) {
            return;
        }
        this.deactivateClickEvents();
        var self = this;
        this.ajaxcall("/innovation/innovation/passPromoteCard.html", {
            lock: true
        }, this, function (result) { }, function (is_error) { if (is_error)
            self.resurrectClickEvents(true); });
    };
    Innovation.prototype.action_clickForPromote = function (event) {
        if (!this.checkAction('promoteCard')) {
            return;
        }
        var HTML_id = this.getCardHTMLIdFromEvent(event);
        var card_id = this.getCardIdFromHTMLId(HTML_id);
        var self = this;
        this.ajaxcall("/innovation/innovation/promoteCard.html", {
            lock: true,
            card_id: card_id
        }, this, function (result) { }, function (is_error) { if (is_error)
            self.resurrectClickEvents(true); });
    };
    Innovation.prototype.action_clickCardBackForPromote = function (event) {
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
        var zone = this.getZone(location, owner, undefined, age);
        var position = this.getCardPositionFromId(zone, card_id, age, type, is_relic);
        var self = this;
        this.ajaxcall("/innovation/innovation/promoteCardBack.html", {
            lock: true,
            owner: owner,
            location: location,
            age: age,
            type: type,
            is_relic: is_relic,
            position: position
        }, this, function (result) { }, function (is_error) { if (is_error)
            self.resurrectClickEvents(true); });
    };
    Innovation.prototype.action_clickForPassDogmaPromoted = function () {
        if (!this.checkAction('passDogmaPromotedCard')) {
            return;
        }
        this.deactivateClickEvents();
        var self = this;
        this.ajaxcall("/innovation/innovation/passDogmaPromotedCard.html", {
            lock: true
        }, this, function (result) { }, function (is_error) { if (is_error)
            self.resurrectClickEvents(true); });
    };
    Innovation.prototype.action_clickForDogmaPromoted = function () {
        if (!this.checkAction('dogmaPromotedCard')) {
            return;
        }
        this.deactivateClickEvents();
        var self = this;
        this.ajaxcall("/innovation/innovation/dogmaPromotedCard.html", {
            lock: true
        }, this, function (result) { }, function (is_error) { if (is_error)
            self.resurrectClickEvents(true); });
    };
    Innovation.prototype.action_clickButtonForAchieveStandardAchievement = function (event) {
        if (!this.checkAction('achieve')) {
            return;
        }
        this.deactivateClickEvents();
        var HTML_id = this.getCardHTMLIdFromEvent(event);
        var age = HTML_id.split("_")[2];
        var self = this;
        this.ajaxcall("/innovation/innovation/achieve.html", {
            lock: true,
            owner: 0,
            location: 'achievements',
            age: age,
        }, this, function (result) { }, function (is_error) { if (is_error)
            self.resurrectClickEvents(true); });
    };
    Innovation.prototype.action_clickButtonForAchieveSecret = function (event) {
        if (!this.checkAction('achieve')) {
            return;
        }
        this.deactivateClickEvents();
        var HTML_id = this.getCardHTMLIdFromEvent(event);
        var age = HTML_id.split("_")[2];
        var self = this;
        this.ajaxcall("/innovation/innovation/achieve.html", {
            lock: true,
            owner: this.player_id,
            location: 'safe',
            age: age,
        }, this, function (result) { }, function (is_error) { if (is_error)
            self.resurrectClickEvents(true); });
    };
    Innovation.prototype.action_clickCardBackForAchieve = function (event) {
        if (!this.checkAction('achieve')) {
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
        var position = this.getCardPositionFromId(zone, card_id, age, type, is_relic);
        var self = this;
        this.ajaxcall("/innovation/innovation/achieveCardBack.html", {
            lock: true,
            owner: owner,
            location: location,
            age: age,
            type: type,
            is_relic: is_relic,
            position: position
        }, this, function (result) { }, function (is_error) { if (is_error)
            self.resurrectClickEvents(true); });
    };
    Innovation.prototype.action_clicForDraw = function (event) {
        if (!this.checkAction('draw')) {
            return;
        }
        this.deactivateClickEvents();
        var self = this;
        this.ajaxcall("/innovation/innovation/draw.html", {
            lock: true,
        }, this, function (result) { }, function (is_error) { if (is_error)
            self.resurrectClickEvents(true); });
    };
    Innovation.prototype.action_clickMeld = function (event) {
        this.stopActionTimer();
        this.deactivateClickEvents();
        var HTML_id = this.getCardHTMLIdFromEvent(event);
        var card_id = dojo.attr(HTML_id, 'card_id');
        var card_name = this.cards[card_id].name;
        $('pagemaintitletext').innerHTML = dojo.string.substitute(_("Meld ${card_name}?"), { 'card_name': _(card_name) });
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
        }
        else if (this.prefs[101].value == 3) {
            wait_time = 4;
            // Long timer (10 seconds)
        }
        else if (this.prefs[101].value == 4) {
            wait_time = 9;
        }
        this.startActionTimer("meld_confirm_button", wait_time, this.action_confirmMeld, HTML_id);
    };
    Innovation.prototype.action_cancelMeld = function (event) {
        this.stopActionTimer();
        this.resurrectClickEvents(true);
        dojo.destroy("meld_cancel_button");
        dojo.destroy("meld_confirm_button");
    };
    Innovation.prototype.action_manuallyConfirmMeld = function (event) {
        this.stopActionTimer();
        var HTML_id = dojo.attr('meld_confirm_button', 'html_id');
        this.action_confirmMeld(HTML_id);
    };
    Innovation.prototype.action_confirmMeld = function (HTML_id) {
        if (!this.checkAction('meld')) {
            return;
        }
        dojo.destroy("meld_cancel_button");
        dojo.destroy("meld_confirm_button");
        var card_id = this.getCardIdFromHTMLId(HTML_id);
        var self = this;
        this.ajaxcall("/innovation/innovation/meld.html", {
            lock: true,
            card_id: card_id
        }, this, function (result) { }, function (is_error) { if (is_error)
            self.resurrectClickEvents(true); });
    };
    Innovation.prototype.action_clickDogma = function (event_or_html_id, via_alternate_prompt, card_id_to_return) {
        if (via_alternate_prompt === void 0) { via_alternate_prompt = null; }
        if (card_id_to_return === void 0) { card_id_to_return = null; }
        if (via_alternate_prompt == null) {
            this.stopActionTimer();
            this.deactivateClickEvents();
        }
        var HTML_id = event_or_html_id.currentTarget ? this.getCardHTMLIdFromEvent(event_or_html_id) : event_or_html_id;
        dojo.attr(HTML_id, 'card_id_to_return', card_id_to_return);
        var card_id = dojo.attr(HTML_id, 'card_id');
        var card = this.cards[card_id];
        if (card_id_to_return == null) {
            $('pagemaintitletext').innerHTML = dojo.string.substitute(_("Dogma ${age} ${card_name}?"), {
                'age': this.square('N', 'age', card.age, 'type_' + card.type),
                'card_name': _(card.name)
            });
        }
        else {
            var card_to_return = this.cards[card_id_to_return];
            $('pagemaintitletext').innerHTML = dojo.string.substitute(_("Dogma ${dogma_age} ${dogma_card_name} by returning ${return_age} ${return_card_name}?"), {
                'dogma_age': this.square('N', 'age', card.age, 'type_' + card.type),
                'dogma_card_name': _(card.name),
                'return_age': this.square('N', 'age', card_to_return.age, 'type_' + card_to_return.type),
                'return_card_name': _(card_to_return.name)
            });
        }
        // Add cancel button
        this.addActionButton("dogma_cancel_button", _("Cancel"), "action_cancelDogma");
        dojo.removeClass("dogma_cancel_button", 'bgabutton_blue');
        dojo.addClass("dogma_cancel_button", 'bgabutton_red');
        // Add confirm button for timer
        this.addActionButton("dogma_confirm_timer_button", _("Confirm"), "action_manuallyConfirmTimerDogma");
        $("dogma_confirm_timer_button").innerHTML = _("Confirm");
        if (via_alternate_prompt == 'endorse') {
            dojo.attr('dogma_confirm_timer_button', 'html_id', dojo.attr(HTML_id, 'html_id'));
            dojo.destroy("dogma_without_endorse_button");
        }
        else {
            dojo.attr('dogma_confirm_timer_button', 'html_id', HTML_id);
        }
        // When confirmation is disabled in game preferences, click the confirmation button instantly
        var wait_time = 0;
        // Short timer (3 seconds)
        if (this.prefs[100].value == 2) {
            wait_time = 2;
            // Medium timer (5 seconds)
        }
        else if (this.prefs[100].value == 3) {
            wait_time = 4;
            // Long timer (10 seconds)
        }
        else if (this.prefs[100].value == 4) {
            wait_time = 9;
        }
        this.startActionTimer("dogma_confirm_timer_button", wait_time, this.action_manuallyConfirmTimerDogma);
    };
    Innovation.prototype.action_cancelDogma = function (event) {
        this.stopActionTimer();
        this.resurrectClickEvents(true);
        dojo.destroy("dogma_cancel_button");
        dojo.destroy("dogma_confirm_timer_button");
        dojo.destroy("dogma_confirm_warning_button");
    };
    Innovation.prototype.action_manuallyConfirmTimerDogma = function (event) {
        this.stopActionTimer();
        var HTML_id = dojo.attr('dogma_confirm_timer_button', 'html_id');
        var card_id = dojo.attr(HTML_id, 'card_id');
        var card = this.cards[card_id];
        var sharing_players = dojo.attr(HTML_id, 'sharing_players');
        if (dojo.attr(HTML_id, 'no_effect')) {
            $('pagemaintitletext').innerHTML = dojo.string.substitute(_("Are you sure you want to dogma ${age} ${card_name}? It will have no effect."), {
                'age': this.square('N', 'age', card.age, 'type_' + card.type),
                'card_name': _(card.name)
            });
            dojo.destroy("dogma_confirm_timer_button");
            this.addActionButton("dogma_confirm_warning_button", _("Confirm"), "action_manuallyConfirmWarningDogma");
            dojo.attr('dogma_confirm_warning_button', 'html_id', HTML_id);
        }
        else if (this.prefs[102].value == 2 && sharing_players.includes(',')) {
            $('pagemaintitletext').innerHTML = dojo.string.substitute(_("Are you sure you want to dogma ${age} ${card_name}? ${players} will share the effect(s)."), {
                'age': this.square('N', 'age', card.age, 'type_' + card.type),
                'card_name': _(card.name),
                'players': this.getOtherPlayersCommaSeparated(sharing_players.split(','))
            });
            dojo.destroy("dogma_confirm_timer_button");
            this.addActionButton("dogma_confirm_warning_button", _("Confirm"), "action_manuallyConfirmWarningDogma");
            dojo.attr('dogma_confirm_warning_button', 'html_id', HTML_id);
        }
        else {
            this.action_confirmDogma(HTML_id);
        }
    };
    Innovation.prototype.action_manuallyConfirmWarningDogma = function (event) {
        var HTML_id = dojo.attr('dogma_confirm_warning_button', 'html_id');
        this.action_confirmDogma(HTML_id);
    };
    Innovation.prototype.action_confirmDogma = function (HTML_id) {
        if (!this.checkAction('dogma')) {
            return;
        }
        dojo.destroy("dogma_cancel_button");
        dojo.destroy("dogma_confirm_timer_button");
        dojo.destroy("dogma_confirm_warning_button");
        var card_id = this.getCardIdFromHTMLId(HTML_id);
        var payload = {
            lock: true,
            card_id: card_id,
        };
        var card_id_to_return = dojo.attr(HTML_id, 'card_id_to_return');
        if (card_id_to_return != null && card_id_to_return != "null") {
            payload["card_id_to_return"] = parseInt(card_id_to_return);
        }
        var self = this;
        this.ajaxcall("/innovation/innovation/dogma.html", payload, this, function (result) { }, function (is_error) { if (is_error)
            self.resurrectClickEvents(true); });
    };
    Innovation.prototype.action_clickNonAdjacentDogma = function (event) {
        this.deactivateClickEvents();
        var HTML_id = this.getCardHTMLIdFromEvent(event);
        var card_id = dojo.attr(HTML_id, 'card_id');
        var card = this.cards[card_id];
        $('pagemaintitletext').innerHTML = dojo.string.substitute(_("Dogma ${age} ${card_name} by returning a card from your hand"), { 'age': this.square('N', 'age', card.age, 'type_' + card.type), 'card_name': _(card.name) });
        // Add cancel button
        this.addActionButton("non_adjacent_dogma_cancel_button", _("Cancel"), "action_cancelNonAdjacentDogma");
        dojo.removeClass("non_adjacent_dogma_cancel_button", 'bgabutton_blue');
        dojo.addClass("non_adjacent_dogma_cancel_button", 'bgabutton_red');
        // Make cards in hand clickable
        var cards_to_return = this.selectMyCardsInHand();
        cards_to_return.addClass("clickable");
        cards_to_return.addClass("mid_dogma");
        this.on(cards_to_return, 'onclick', 'action_confirmNonAdjacentDogma');
        cards_to_return.forEach(function (node) {
            dojo.attr(node, 'card_to_dogma_html_id', HTML_id);
        });
        this.addTooltipsWithoutActionsToMyHand();
    };
    Innovation.prototype.action_cancelNonAdjacentDogma = function (event) {
        this.resurrectClickEvents(true);
        dojo.destroy("non_adjacent_dogma_cancel_button");
        dojo.destroy("non_adjacent_dogma_button");
        var cards_in_hand = this.selectMyCardsInHand();
        cards_in_hand.removeClass("mid_dogma");
        this.off(cards_in_hand, 'onclick');
        this.on(cards_in_hand, 'onclick', 'action_clickMeld');
    };
    Innovation.prototype.action_confirmNonAdjacentDogma = function (event) {
        $('pagemaintitletext').innerHTML = this.erased_pagemaintitle_text;
        dojo.destroy("non_adjacent_dogma_cancel_button");
        // Do a partial this.deactivateClickEvents() without overwriting the saved state of the clickable elements
        this.off(dojo.query(".mid_dogma"), 'onclick');
        dojo.query(".clickable").removeClass("clickable");
        dojo.query(".mid_dogma").removeClass("mid_dogma");
        var card_to_return_html_id = this.getCardHTMLIdFromEvent(event);
        var card_to_dogma_html_id = dojo.attr(card_to_return_html_id, 'card_to_dogma_html_id');
        var card_id_to_return = this.getCardIdFromHTMLId(card_to_return_html_id);
        this.action_clickDogma(card_to_dogma_html_id, /*via_alternate_prompt=*/ 'nonAdjacentDogma', card_id_to_return);
    };
    Innovation.prototype.action_clickEndorse = function (event) {
        this.deactivateClickEvents();
        var HTML_id = this.getCardHTMLIdFromEvent(event);
        var card_id = dojo.attr(HTML_id, 'card_id');
        var card = this.cards[card_id];
        var max_age_for_endorse_payment = dojo.attr(HTML_id, 'max_age_for_endorse_payment');
        if (this.gamedatas.fourth_edition) {
            $('pagemaintitletext').innerHTML = dojo.string.substitute(_("Endorse ${age} ${card_name} by selecting a card of value ${junk_age} or lower from your hand to junk"), { 'age': this.square('N', 'age', card.age, 'type_' + card.type), 'card_name': _(card.name), 'junk_age': this.square('N', 'age', max_age_for_endorse_payment) });
        }
        else {
            $('pagemaintitletext').innerHTML = dojo.string.substitute(_("Endorse ${age} ${card_name} by selecting a card of value ${tuck_age} or lower from your hand to tuck"), { 'age': this.square('N', 'age', card.age, 'type_' + card.type), 'card_name': _(card.name), 'tuck_age': this.square('N', 'age', max_age_for_endorse_payment) });
        }
        // Add cancel button
        this.addActionButton("endorse_cancel_button", _("Cancel"), "action_cancelEndorse");
        dojo.removeClass("endorse_cancel_button", 'bgabutton_blue');
        dojo.addClass("endorse_cancel_button", 'bgabutton_red');
        // Add button to dogma without endorsing
        this.addActionButton("dogma_without_endorse_button", _("Dogma without endorse"), "action_dogmaWithoutEndorse");
        dojo.attr('dogma_without_endorse_button', 'html_id', HTML_id);
        dojo.attr('dogma_without_endorse_button', 'card_id', card_id);
        // Make cards for payment clickable
        var payment_cards = this.selectMyCardsEligibleForEndorsedDogmaPayment(max_age_for_endorse_payment);
        payment_cards.addClass("clickable");
        payment_cards.addClass("mid_dogma");
        this.on(payment_cards, 'onclick', 'action_confirmEndorse');
        payment_cards.forEach(function (node) {
            dojo.attr(node, 'card_to_endorse_id', card_id);
        });
    };
    Innovation.prototype.action_cancelEndorse = function (event) {
        this.resurrectClickEvents(true);
        dojo.destroy("endorse_cancel_button");
        dojo.destroy("dogma_without_endorse_button");
        dojo.destroy("endorse_button");
        var cards_in_hand = this.selectMyCardsInHand();
        cards_in_hand.removeClass("mid_dogma");
        this.off(cards_in_hand, 'onclick');
        this.on(cards_in_hand, 'onclick', 'action_clickMeld');
    };
    Innovation.prototype.action_dogmaWithoutEndorse = function (event) {
        $('pagemaintitletext').innerHTML = this.erased_pagemaintitle_text;
        dojo.destroy("endorse_cancel_button");
        // Do a partial this.deactivateClickEvents() without overwriting the saved state of the clickable elements
        this.off(dojo.query(".mid_dogma"), 'onclick');
        dojo.query(".clickable").removeClass("clickable");
        dojo.query(".mid_dogma").removeClass("mid_dogma");
        this.action_clickDogma(event, /*via_alternate_prompt=*/ 'endorse');
    };
    Innovation.prototype.action_confirmEndorse = function (event) {
        if (!this.checkAction('endorse')) {
            return;
        }
        dojo.destroy("endorse_cancel_button");
        dojo.destroy("dogma_without_endorse_button");
        var HTML_id = this.getCardHTMLIdFromEvent(event);
        var payment_card_id = this.getCardIdFromHTMLId(HTML_id);
        var card_to_endorse_id = dojo.attr(HTML_id, 'card_to_endorse_id');
        var self = this;
        this.ajaxcall("/innovation/innovation/endorse.html", {
            lock: true,
            card_to_endorse_id: card_to_endorse_id,
            payment_card_id: payment_card_id
        }, this, function (result) { }, function (is_error) { if (is_error)
            self.resurrectClickEvents(true); });
    };
    Innovation.prototype.action_clickForChooseFront = function (event) {
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
    };
    Innovation.prototype.action_cancelChooseFront = function (event) {
        this.stopActionTimer();
        this.resurrectClickEvents(true);
        dojo.destroy("choose_front_cancel_button");
        dojo.destroy("choose_front_confirm_button");
    };
    Innovation.prototype.action_manuallyConfirmChooseFront = function (event) {
        this.stopActionTimer();
        var HTML_id = dojo.attr('choose_front_confirm_button', 'html_id');
        this.action_confirmChooseFront(HTML_id);
    };
    Innovation.prototype.action_confirmChooseFront = function (HTML_id) {
        if (!this.checkAction('choose')) {
            return;
        }
        // If the piles were forcibly made visible, collapse them
        for (var player_id in this.players) {
            for (var color = 0; color < 5; color++) {
                var zone = this.zone["board"][player_id][color];
                this.refreshSplay(zone, zone.splay_direction, /*force_full_visible=*/ false);
            }
        }
        var card_id = this.getCardIdFromHTMLId(HTML_id);
        var self = this;
        this.ajaxcall("/innovation/innovation/choose.html", {
            lock: true,
            card_id: card_id
        }, this, function (result) { }, function (is_error) { if (is_error)
            self.resurrectClickEvents(true); });
    };
    // TODO(LATER): Remove this once we have a personal preference for confirming card choices.
    Innovation.prototype.action_clicForChoose = function (event) {
        this.deactivateClickEvents();
        var HTML_id = this.getCardHTMLIdFromEvent(event);
        this.action_confirmChooseFront(HTML_id);
    };
    Innovation.prototype.action_clicForChooseRecto = function (event) {
        if (!this.checkAction('choose')) {
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
        this.ajaxcall("/innovation/innovation/chooseRecto.html", {
            lock: true,
            owner: owner,
            location: location,
            age: age,
            type: type,
            is_relic: is_relic,
            position: position
        }, this, function (result) { }, function (is_error) { if (is_error)
            self.resurrectClickEvents(true); });
    };
    Innovation.prototype.action_clickButtonToDecreaseIntegers = function (event) {
        if (!this.checkAction('choose')) {
            return;
        }
        var current_lowest_integer = parseInt(document.getElementById("choice_0").innerText);
        if (current_lowest_integer > 0) {
            for (var i = 0; i < 6; i++) {
                document.getElementById("choice_" + i).innerText = (current_lowest_integer - 1 + i).toString();
            }
            if (current_lowest_integer == 1) {
                dojo.byId('decrease_integers').style.display = 'none';
            }
            dojo.byId('increase_integers').style.display = 'inline-block';
        }
    };
    Innovation.prototype.action_clickButtonToIncreaseIntegers = function (event) {
        if (!this.checkAction('choose')) {
            return;
        }
        var current_lowest_integer = parseInt(document.getElementById("choice_0").innerText);
        if (current_lowest_integer < 995) {
            for (var i = 0; i < 6; i++) {
                document.getElementById("choice_" + i).innerText = (current_lowest_integer + 1 + i).toString();
            }
            dojo.byId('decrease_integers').style.display = 'inline-block';
            if (current_lowest_integer == 994) {
                dojo.byId('increase_integers').style.display = 'none';
            }
        }
    };
    Innovation.prototype.action_clicForChooseSpecialOption = function (event) {
        if (!this.checkAction('choose')) {
            return;
        }
        var HTML_id = this.getCardHTMLIdFromEvent(event);
        var choice = HTML_id.substr(7);
        if (this.choose_two_colors) {
            if (this.first_chosen_color === null) {
                this.first_chosen_color = choice;
                dojo.destroy(event.target); // Destroy the button
                var query = dojo.query('#pagemaintitletext > span[style]');
                var You = query[query.length - 1].outerHTML;
                $('pagemaintitletext').innerHTML = dojo.string.substitute(_("${You} still must choose one color"), { 'You': You });
                return;
            }
            choice = Math.pow(2, this.first_chosen_color) + Math.pow(2, choice); // Set choice as encoded value for the array of the two chosen colors
            this.first_chosen_color = null;
        }
        else if (this.choose_three_colors) {
            if (this.first_chosen_color === null) {
                this.first_chosen_color = choice;
                dojo.destroy(event.target); // Destroy the button
                var query = dojo.query('#pagemaintitletext > span[style]');
                var You = query[query.length - 1].outerHTML;
                $('pagemaintitletext').innerHTML = dojo.string.substitute(_("${You} still must choose two colors"), { 'You': You });
                return;
            }
            if (this.second_chosen_color === null) {
                this.second_chosen_color = choice;
                dojo.destroy(event.target); // Destroy the button
                var query = dojo.query('#pagemaintitletext > span[style]');
                var You = query[query.length - 1].outerHTML;
                $('pagemaintitletext').innerHTML = dojo.string.substitute(_("${You} still must choose one color"), { 'You': You });
                return;
            }
            choice = Math.pow(2, this.second_chosen_color) + Math.pow(2, this.first_chosen_color) + Math.pow(2, choice); // Set choice as encoded value for the array of the three chosen colors
            this.first_chosen_color = null;
            this.second_chosen_color = null;
        }
        else if (this.choose_integer) {
            choice = parseInt(document.getElementById(HTML_id).innerText);
        }
        this.deactivateClickEvents();
        var self = this;
        this.ajaxcall("/innovation/innovation/chooseSpecialOption.html", {
            lock: true,
            choice: choice
        }, this, function (result) { }, function (is_error) { if (is_error)
            self.resurrectClickEvents(true); });
    };
    Innovation.prototype.action_clicForPassOrStop = function () {
        if (!this.checkAction('choose')) {
            return;
        }
        if (this.publication_permutations_done !== null) { // Special code for Publication: undo the changes the player made to his board
            this.publicationClicForUndoingSwaps();
            for (var color = 0; color < 5; color++) {
                var zone = this.zone["board"][this.player_id][color];
                this.refreshSplay(zone, zone.splay_direction, /*force_full_visible=*/ false);
            }
            this.on(dojo.query('#change_display_mode_button'), 'onclick', 'toggle_displayMode');
            this.publication_permuted_zone = null;
            this.publication_permutations_done = null;
            this.publication_original_items = null;
        }
        else if (this.color_pile !== null) { // Special code where a stack needed to be selected
            var zone = this.zone["board"][this.player_id][this.color_pile];
            this.refreshSplay(zone, zone.splay_direction, /*force_full_visible=*/ false);
        }
        this.deactivateClickEvents();
        var self = this;
        this.ajaxcall("/innovation/innovation/choose.html", {
            lock: true,
            card_id: -1
        }, this, function (result) { }, function (is_error) { if (is_error)
            self.resurrectClickEvents(true); });
    };
    Innovation.prototype.action_clicForSplay = function (event) {
        if (!this.checkAction('choose')) {
            return;
        }
        this.deactivateClickEvents();
        var HTML_id = this.getCardHTMLIdFromEvent(event);
        var color = HTML_id.substr(6);
        var self = this;
        this.ajaxcall("/innovation/innovation/choose.html", {
            lock: true,
            card_id: this.getCardIdFromHTMLId(this.zone["board"][this.player_id][color].items[0].id) // A choose for splay is equivalent as selecting a board card of the right color, by design
        }, this, function (result) { }, function (is_error) { if (is_error)
            self.resurrectClickEvents(true); });
    };
    Innovation.prototype.action_publicationClicForRearrange = function (event) {
        if (!this.checkAction('choose')) {
            return;
        }
        var permuted_color = this.publication_permuted_zone.container_div.slice(-1);
        var permutations_done = [];
        for (var i = 0; i < this.publication_permutations_done.length; i++) {
            var permutation = this.publication_permutations_done[i];
            permutations_done.push(permutation.position + "," + permutation.delta);
        }
        this.publicationResetInterface();
        for (var color = 0; color < 5; color++) {
            var zone = this.zone["board"][this.player_id][color];
            this.refreshSplay(zone, zone.splay_direction, /*force_full_visible=*/ false);
        }
        this.on(dojo.query('#change_display_mode_button'), 'onclick', 'toggle_displayMode');
        //dojo.style('change_display_mode_button', {'display': 'initial'}); // Show back the button used for changing the display
        this.publication_permuted_zone = null;
        this.publication_permutations_done = null;
        this.publication_original_items = null;
        this.deactivateClickEvents();
        var self = this;
        this.ajaxcall("/innovation/innovation/publicationRearrange.html", {
            lock: true,
            color: permuted_color,
            permutations_done: permutations_done.join(";"),
        }, this, function (result) { }, function (is_error) { if (is_error)
            self.resurrectClickEvents(true); });
    };
    Innovation.prototype.publicationClicForMove = function (event) {
        var HTML_id = this.getCardHTMLIdFromEvent(event);
        var arrow_up = $('publication_arrow_up');
        var arrow_down = $('publication_arrow_down');
        if (!arrow_up) {
            arrow_up = dojo.create('button', { 'id': 'publication_arrow_up' });
            arrow_up.innerHTML = "<span>&#8593;</span>"; // Code for arrow up
            dojo.connect(arrow_up, 'onclick', this, 'publicationClicForSwap');
            arrow_down = dojo.create('button', { 'id': 'publication_arrow_down' });
            arrow_down.innerHTML = "<span>&#8595;</span>"; // Code for arrow down
            dojo.connect(arrow_down, 'onclick', this, 'publicationClicForSwap');
        }
        dojo.place(arrow_up, HTML_id);
        dojo.place(arrow_down, HTML_id);
    };
    Innovation.prototype.publicationClicForSwap = function (event) {
        var arrow = event.currentTarget;
        var delta = arrow == $('publication_arrow_up') ? 1 : -1; // Change of position requested
        var HTML_id = dojo.getAttr(arrow.parentNode, 'id');
        var card_id = this.getCardIdFromHTMLId(HTML_id);
        var color = this.cards[card_id].color;
        // Search position in zone
        var zone = this.zone["board"][this.player_id][color];
        var items = zone.items;
        var position = -1;
        for (var p = 0; p < items.length; p++) {
            if (zone.items[p].id == HTML_id) {
                position = p;
                break;
            }
        }
        if (position == 0 && delta == -1 || position == items.length - 1 && delta == 1) {
            return; // The card is already on max position
        }
        if (this.publication_permutations_done.length == 0) { // First change
            // Add cancel button
            var cancel = dojo.create('a', { 'id': 'publication_cancel', 'class': 'bgabutton bgabutton_red' });
            cancel.innerHTML = _("Cancel");
            dojo.place(cancel, $('splay_indicator_' + this.player_id + '_' + color), 'after');
            dojo.connect(cancel, 'onclick', this, 'publicationClicForUndoingSwaps');
            // Add done button
            var done = dojo.create('a', { 'id': 'publication_done', 'class': 'bgabutton bgabutton_blue' });
            done.innerHTML = _("Done");
            dojo.place(done, cancel, 'after');
            dojo.connect(done, 'onclick', this, 'action_publicationClicForRearrange');
            // Add another done button to the action bar
            this.addActionButton('publication_done_action_bar', _("Done"), "action_publicationClicForRearrange");
            // Deactivate click events for other colors
            var other_colors = [0, 1, 2, 3, 4];
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
        this.publication_permutations_done.push({ 'position': position, 'delta': delta });
        // Swap positions
        this.publicationSwap(this.player_id, zone, position, delta);
        var no_change = true;
        for (var p = 0; p < items.length; p++) {
            if (items[p] != this.publication_original_items[p]) {
                no_change = false;
            }
        }
        if (no_change) { // The permutation cycled to the initial situation
            // Prevent user to validate this
            this.publicationResetInterface(/*keep_arrows=*/ true);
        }
    };
    Innovation.prototype.publicationClicForUndoingSwaps = function () {
        // Undo publicationSwaps
        if (this.publication_permutations_done != null) {
            for (var i = this.publication_permutations_done.length - 1; i >= 0; i--) {
                var permutation = this.publication_permutations_done[i];
                this.publicationSwap(this.player_id, this.publication_permuted_zone, permutation.position, permutation.delta); // Re-appliying a permutation cancels it
            }
        }
        // Reset interface
        this.publicationResetInterface();
    };
    Innovation.prototype.publicationResetInterface = function (keep_arrows) {
        if (keep_arrows === void 0) { keep_arrows = false; }
        if (!keep_arrows) {
            dojo.destroy('publication_arrow_up');
            dojo.destroy('publication_arrow_down');
        }
        dojo.destroy('publication_cancel');
        dojo.destroy('publication_done');
        dojo.destroy('publication_done_action_bar');
        var selectable_cards = this.selectAllCardsOnMyBoard();
        selectable_cards.addClass("clickable");
        selectable_cards.addClass("mid_dogma");
        this.on(selectable_cards, 'onclick', 'publicationClicForMove');
        this.publication_permuted_zone = null;
        this.publication_permutations_done = [];
    };
    Innovation.prototype.publicationSwap = function (player_id, zone, position, delta) {
        var item = zone.items[position];
        var other_item = zone.items[position + delta];
        item.weight += delta;
        other_item.weight -= delta;
        dojo.style(item.id, 'z-index', item.weight);
        dojo.style(other_item.id, 'z-index', other_item.weight);
        zone.items[position + delta] = item;
        zone.items[position] = other_item;
        var splay_direction = Number(zone.splay_direction);
        // Change ressource if the card on top is involved
        if (position == zone.items.length - 1 || position + delta == zone.items.length - 1) {
            var up = delta == 1;
            var old_top_item = up ? other_item : item;
            var new_top_item = up ? item : other_item;
            var old_top_card = this.cards[this.getCardIdFromHTMLId(old_top_item.id)];
            var new_top_card = this.cards[this.getCardIdFromHTMLId(new_top_item.id)];
            var icon_counts = new Map();
            for (var icon = 1; icon <= 7; icon++) {
                icon_counts.set(icon, this.counter["resource_count"][player_id][icon].getValue());
            }
            this.decrementMap(icon_counts, getHiddenIconsWhenSplayed(old_top_card, splay_direction));
            this.incrementMap(icon_counts, getHiddenIconsWhenSplayed(new_top_card, splay_direction));
            for (var icon = 1; icon <= 7; icon++) {
                this.counter["resource_count"][player_id][icon].setValue(icon_counts.get(icon));
            }
        }
        zone.updateDisplay();
    };
    Innovation.prototype.action_chooseSpecialAchievement = function (event) {
        if (!this.checkAction('choose')) {
            return;
        }
        this.click_close_special_achievement_selection_window();
        this.deactivateClickEvents();
        var card_id = dojo.getAttr(event.currentTarget, 'card_id');
        var self = this;
        this.ajaxcall("/innovation/innovation/chooseSpecialOption.html", {
            lock: true,
            choice: card_id,
        }, this, function (result) { }, function (is_error) { if (is_error)
            self.resurrectClickEvents(true); });
    };
    Innovation.prototype.decrementMap = function (map, keys) {
        keys.forEach(function (key) {
            var _a;
            map.set(key, ((_a = map.get(key)) !== null && _a !== void 0 ? _a : 0) - 1);
        });
    };
    Innovation.prototype.incrementMap = function (map, keys) {
        keys.forEach(function (key) {
            var _a;
            map.set(key, ((_a = map.get(key)) !== null && _a !== void 0 ? _a : 0) + 1);
        });
    };
    Innovation.prototype.click_display_forecast_window = function () {
        this.my_forecast_verso_window.show();
    };
    Innovation.prototype.click_close_forecast_window = function () {
        this.my_forecast_verso_window.hide();
    };
    Innovation.prototype.click_display_score_window = function () {
        this.my_score_verso_window.show();
    };
    Innovation.prototype.click_close_score_window = function () {
        this.my_score_verso_window.hide();
    };
    Innovation.prototype.toggle_displayMode = function () {
        // Indicate the change of display mode
        this.display_mode = !this.display_mode;
        var button_text = this.display_mode ? this.text_for_expanded_mode : this.text_for_compact_mode;
        var arrows = this.display_mode ? this.arrows_for_expanded_mode : this.arrows_for_compact_mode;
        var inside_button = this.format_string_recursive("${arrows} ${button_text}", { 'arrows': arrows, 'button_text': button_text, 'i18n': ['button_text'] });
        $('change_display_mode_button').innerHTML = inside_button;
        // Update the display of the stacks
        for (var player_id in this.players) {
            for (var color = 0; color < 5; color++) {
                var zone = this.zone["board"][player_id][color];
                this.refreshSplay(zone, zone.splay_direction);
            }
        }
        if (!this.isSpectator) {
            // Inform the server of this change to make it by default if the player refreshes the page
            this.ajaxcall("/innovation/innovation/updateDisplayMode.html", {
                lock: true,
                display_mode: this.display_mode
            }, this, function (result) { }, function (is_error) { });
        }
    };
    Innovation.prototype.toggle_view = function () {
        // Indicate the change of view
        this.view_full = !this.view_full;
        var button_text = this.view_full ? this.text_for_view_full : this.text_for_view_normal;
        var inside_button = this.format_string_recursive("${button_text}", { 'button_text': button_text, 'i18n': ['button_text'] });
        $('change_view_full_button').innerHTML = inside_button;
        // Update the display of the stacks
        for (var player_id in this.players) {
            for (var color = 0; color < 5; color++) {
                var zone = this.zone["board"][player_id][color];
                this.refreshSplay(zone, zone.splay_direction);
            }
        }
        if (!this.isSpectator) {
            // Inform the server of this change to make it by default if the player refreshes the page
            this.ajaxcall("/innovation/innovation/updateViewFull.html", {
                lock: true,
                view_full: this.view_full
            }, this, function (result) { }, function (is_error) { });
        }
    };
    Innovation.prototype.click_open_special_achievement_browsing_window = function () {
        this.click_open_card_browsing_window();
        this.click_browse_special_achievements();
    };
    Innovation.prototype.click_open_junk_browsing_window = function () {
        this.click_open_card_browsing_window();
        this.click_browse_junk();
    };
    Innovation.prototype.click_open_card_browsing_window = function () {
        this.card_browsing_window.show();
    };
    Innovation.prototype.click_close_card_browsing_window = function () {
        this.card_browsing_window.hide();
    };
    Innovation.prototype.click_browse_cards = function (event) {
        var id = dojo.getAttr(event.currentTarget, 'id');
        dojo.byId('browse_cards_buttons_row_2').style.display = 'block';
        if (id.startsWith('browse_cards_type_')) {
            dojo.query('#browse_cards_buttons_row_1 > .browse_cards_button').removeClass('selected');
            dojo.query("#".concat(id)).addClass('selected');
            if (this.gamedatas.relics_enabled) {
                dojo.byId('browse_relics').style.display = (id == 'browse_cards_type_1') ? 'inline-block' : 'none';
            }
            if (dojo.query('#browse_cards_buttons_row_2 > .browse_cards_button.selected').length == 0) {
                dojo.query('#browse_cards_age_1').addClass('selected');
            }
        }
        else {
            dojo.query('#browse_cards_buttons_row_2 > .browse_cards_button').removeClass('selected');
            dojo.query("#".concat(id)).addClass('selected');
        }
        dojo.byId('browse_card_summaries').style.display = 'block';
        dojo.byId('junk').style.display = 'none';
        dojo.query('#special_achievement_summaries').addClass('heightless');
        var node = dojo.query('#browse_card_summaries')[0];
        node.innerHTML = '';
        // Special case for relics
        if (dojo.query("#browse_relics.selected").length > 0) {
            for (var i = 215; i <= 219; i++) {
                if (this.canShowCardTooltip(i)) {
                    node.innerHTML += this.createCardForCardBrowser(i);
                }
            }
            // NOTE: For some reason the tooltips get removed when we add more HTML to the node, so we need to use a
            // separate loop to add them.
            for (var i = 215; i <= 219; i++) {
                if (this.canShowCardTooltip(i)) {
                    this.addCustomTooltip("browse_card_id_".concat(i), this.getTooltipForCard(i), "");
                }
            }
            return;
        }
        // Figure out which set is selected
        var type = 0;
        for (var i = 0; i <= 5; i++) {
            if (dojo.query("#browse_cards_type_".concat(i, ".selected")).length > 0) {
                type = i;
            }
        }
        // Figure out which age is selected
        var age = 1;
        for (var i = 1; i <= 11; i++) {
            if (dojo.query("#browse_cards_age_".concat(i, ".selected")).length > 0) {
                age = i;
            }
        }
        // Determine range of cards to render
        var min_id;
        var max_id;
        if (type == 5) {
            min_id = 475 + age * 10;
            max_id = min_id + 9;
            if (age == 1) {
                min_id -= 5;
            }
        }
        else if (age == 11) {
            min_id = 440 + type * 10;
            max_id = min_id + 9;
        }
        else {
            min_id = type * 110 + age * 10 - 5;
            max_id = min_id + 9;
            if (age == 1) {
                min_id -= 5;
            }
        }
        // Special case for relics
        if (dojo.query("#browse_relics.selected").length > 0) {
            min_id = 215;
            max_id = 219;
        }
        // Add cards to popup
        for (var i = min_id; i <= max_id; i++) {
            // TODO(4E): Implement Martian Internet, Hitchhiking, and Teleprompter later.
            if (i != 451 && i != 560 && i != 570) {
                node.innerHTML += this.createCardForCardBrowser(i);
            }
        }
        // NOTE: For some reason the tooltips get removed when we add more HTML to the node, so we need to use a
        // separate loop to add them.
        for (var i = min_id; i <= max_id; i++) {
            // TODO(4E): Implement Martian Internet, Hitchhiking, and Teleprompter later.
            if (i != 451 && i != 560 && i != 570) {
                this.addCustomTooltip("browse_card_id_".concat(i), this.getTooltipForCard(i), "");
            }
        }
    };
    Innovation.prototype.click_browse_special_achievements = function () {
        dojo.query('.browse_cards_button').removeClass('selected');
        dojo.query('#browse_special_achievements').addClass('selected');
        dojo.byId('browse_cards_buttons_row_2').style.display = 'none';
        dojo.byId('browse_card_summaries').style.display = 'none';
        dojo.byId('junk').style.display = 'none';
        dojo.query('#special_achievement_summaries').removeClass('heightless');
    };
    Innovation.prototype.click_browse_junk = function () {
        dojo.query('.browse_cards_button').removeClass('selected');
        dojo.query('#browse_junk').addClass('selected');
        dojo.byId('browse_cards_buttons_row_2').style.display = 'none';
        dojo.byId('junk').style.display = 'block';
        dojo.byId('browse_card_summaries').style.display = 'none';
        dojo.query('#special_achievement_summaries').addClass('heightless');
    };
    ///////////////////////////////////////////////////
    //// Reaction to cometD notifications
    /*
        setupNotifications:
        
        In this method, you associate each of your game notifications with your local method to handle it.
        
        Note: game notification names correspond to "notifyAllPlayers" and "notifyPlayer" calls in
                your innovation.game.php file.
    
    */
    Innovation.prototype.setupNotifications = function () {
        var reasonnable_delay = 1000;
        dojo.subscribe('transferedCard', this, "notif_transferedCard");
        this.notifqueue.setSynchronous('transferedCard', reasonnable_delay); // Wait X milliseconds after executing the transferedCard handler
        dojo.subscribe('transferedCardNoDelay', this, "notif_transferedCard");
        dojo.subscribe('logWithCardTooltips', this, "notif_logWithCardTooltips"); // This kind of notification does not need any delay
        dojo.subscribe('splayedPile', this, "notif_splayedPile");
        this.notifqueue.setSynchronous('splayedPile', reasonnable_delay); // Wait X milliseconds after executing the splayedPile handler
        dojo.subscribe('rearrangedPile', this, "notif_rearrangedPile"); // This kind of notification does not need any delay
        dojo.subscribe('removedPlayer', this, "notif_removedPlayer"); // This kind of notification does not need any delay
        dojo.subscribe('updateResourcesForArtifactOnDisplay', this, "notif_updateResourcesForArtifactOnDisplay"); // This kind of notification does not need any delay
        dojo.subscribe('resetMonumentCounters', this, "notif_resetMonumentCounters"); // This kind of notification does not need any delay
        dojo.subscribe('endOfGame', this, "notif_endOfGame"); // This kind of notification does not need any delay
        dojo.subscribe('log', this, "notif_log"); // This kind of notification does not change anything but log on the interface, no delay
        if (this.isSpectator) {
            dojo.subscribe('transferedCard_spectator', this, "notif_transferedCard_spectator");
            this.notifqueue.setSynchronous('transferedCard_spectator', reasonnable_delay); // Wait X milliseconds after executing the handler
            dojo.subscribe('transferedCardNoDelay_spectator', this, "notif_transferedCard_spectator");
            dojo.subscribe('logWithCardTooltips_spectator', this, "notif_logWithCardTooltips_spectator"); // This kind of notification does not need any delay
            dojo.subscribe('splayedPile_spectator', this, "notif_splayedPile_spectator");
            this.notifqueue.setSynchronous('splayedPile_spectator', reasonnable_delay); // Wait X milliseconds after executing the handler
            dojo.subscribe('rearrangedPile_spectator', this, "notif_rearrangedPile_spectator"); // This kind of notification does not need any delay
            dojo.subscribe('removedPlayer_spectator', this, "notif_removedPlayer_spectator"); // This kind of notification does not need any delay
            dojo.subscribe('updateResourcesForArtifactOnDisplay_spectator', this, "notif_updateResourcesForArtifactOnDisplay_spectator"); // This kind of notification does not need any delay
            dojo.subscribe('resetMonumentCounters_spectator', this, "notif_resetMonumentCounters_spectator"); // This kind of notification does not need any delay
            dojo.subscribe('endOfGame_spectator', this, "notif_endOfGame_spectator"); // This kind of notification does not need any delay
            dojo.subscribe('log_spectator', this, "notif_log_spectator"); // This kind of notification does not change anything but log on the interface, no delay
        }
        ;
    };
    Innovation.prototype.notif_transferedCard = function (notif) {
        var card = notif.args;
        if (parseInt(card.is_relic) == 1) {
            card.id = 212 + parseInt(card.age);
        }
        // Special code for my forecast management
        if (card.location_from == "forecast" && card.owner_from == this.player_id) {
            // Remove the card from my forecast personal window
            // NOTE: The button to look at the player's forecast is broken in archive mode.
            if (!g_archive_mode) {
                this.removeFromZone(this.zone["my_forecast_verso"], card.id, true, card.age, card.type, card.is_relic);
            }
        }
        // Special code for my score management
        if (card.location_from == "score" && card.owner_from == this.player_id) {
            // Remove the card from my score personal window
            // NOTE: The button to look at the player's score pile is broken in archive mode.
            if (!g_archive_mode) {
                this.removeFromZone(this.zone["my_score_verso"], card.id, true, card.age, card.type, card.is_relic);
            }
        }
        // The zones are undefined if the location is "removed" since there isn't actually a spot for it onscreen
        var zone_from = this.getZone(card.location_from, card.owner_from, card.type, card.age, card.color);
        var zone_to = this.getZone(card.location_to, card.owner_to, card.type, card.age, card.color);
        var is_fountain_or_flag = isFountain(card.id) || isFlag(card.id);
        var is_museum = isMuseum(card.id);
        var visible_from = is_fountain_or_flag || is_museum || zone_from && this.getCardTypeInZone(zone_from.HTML_class) == "card" || card.age === null; // Special achievements are considered visible too
        var visible_to = is_fountain_or_flag || is_museum || zone_to && this.getCardTypeInZone(zone_to.HTML_class) == "card" || card.age === null; // Special achievements are considered visible too
        var id_from;
        var id_to;
        if (visible_from) {
            id_from = card.id;
            if (visible_to) {
                id_to = id_from;
            }
            else {
                id_to = null; // A new ID must be created for this card since it's being flipped face down
            }
        }
        else {
            if (card.location_from == "removed") {
                id_from = card.id;
            }
            else {
                id_from = this.getCardIdFromPosition(zone_from, card.position_from, card.age, card.type, card.is_relic);
            }
            if (visible_to) {
                id_to = card.id;
            }
            else {
                id_to = id_from;
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
        if (card.new_score !== undefined) {
            this.counter["score"][card.player_id].setValue(card.new_score);
        }
        if (card.new_ressource_counts !== undefined) {
            for (var icon = 1; icon <= 7; icon++) {
                this.counter["resource_count"][card.player_id][icon].setValue(card.new_ressource_counts[icon]);
            }
        }
        if (card.new_max_age_on_board !== undefined) {
            this.counter["max_age_on_board"][card.player_id].setValue(card.new_max_age_on_board);
        }
        // 2 players involved
        if (card.new_score_from !== undefined) {
            this.counter["score"][card.owner_from].setValue(card.new_score_from);
        }
        if (card.new_score_to !== undefined) {
            this.counter["score"][card.owner_to].setValue(card.new_score_to);
        }
        if (card.new_ressource_counts_from !== undefined) {
            for (var icon = 1; icon <= 7; icon++) {
                this.counter["resource_count"][card.owner_from][icon].setValue(card.new_ressource_counts_from[icon]);
            }
        }
        if (card.new_ressource_counts_to !== undefined) {
            for (var icon = 1; icon <= 7; icon++) {
                this.counter["resource_count"][card.owner_to][icon].setValue(card.new_ressource_counts_to[icon]);
            }
        }
        if (card.new_max_age_on_board_from !== undefined) {
            this.counter["max_age_on_board"][card.owner_from].setValue(card.new_max_age_on_board_from);
        }
        if (card.new_max_age_on_board_to !== undefined) {
            this.counter["max_age_on_board"][card.owner_to].setValue(card.new_max_age_on_board_to);
        }
        if (card.monument_counters !== undefined && card.monument_counters[this.player_id] !== undefined) {
            this.number_of_tucked_cards = card.monument_counters[this.player_id].number_of_tucked_cards;
            this.number_of_scored_cards = card.monument_counters[this.player_id].number_of_scored_cards;
        }
        if (card.location_to === 'removed') {
            this.removeFromZone(zone_from, id_from, true, card.age, card.type, card.is_relic);
        }
        else if (is_fountain_or_flag && card.owner_from == 0) {
            // Make the card appear that it is coming from the card with the fountain/flag icon
            var pile = this.zone["board"][card.owner_to][card.color].items;
            var top_card = pile[pile.length - 1];
            var center_of_top_card = dojo.query("#".concat(top_card.id, " > .card_title"))[0];
            this.createAndAddToZone(zone_to, null, null, card.type, card.is_relic, card.id, center_of_top_card.id, card);
        }
        else if (is_fountain_or_flag && card.owner_to == 0 || (card.location_to == 'junk' && card.age == null)) {
            this.removeFromZone(zone_from, id_from, true, card.age, card.type, card.is_relic);
        }
        else if (card.location_from == 'junk' && card.age == null) { // Unjunking a special achievement
            // TODO(4E): See if we can remove this special case and just handle it by calling moveBetweenZones below.
            this.createAndAddToZone(zone_to, card.position, card.age, card.type, card.is_relic, card.id, dojo.body(), null);
        }
        else {
            this.moveBetweenZones(zone_from, zone_to, id_from, id_to, card);
        }
        // Special code for my forecast management
        if (card.location_to == "forecast" && card.owner_to == this.player_id) {
            // Add the card to my forecast personal window
            // NOTE: The button to look at the player's forecast is broken in archive mode.
            if (!g_archive_mode) {
                this.createAndAddToZone(this.zone["my_forecast_verso"], card.position_to, card.age, card.type, card.is_relic, card.id, dojo.body(), card);
            }
            visible_to = true;
        }
        // Special code for my score management
        if (card.location_to == "score" && card.owner_to == this.player_id) {
            // Add the card to my score personal window
            // NOTE: The button to look at the player's score pile is broken in archive mode.
            if (!g_archive_mode) {
                this.createAndAddToZone(this.zone["my_score_verso"], card.position_to, card.age, card.type, card.is_relic, card.id, dojo.body(), card);
            }
            visible_to = true;
        }
        if (this.gamedatas.fourth_edition && (card.location_from === 'forecast' || card.location_to === 'forecast')) {
            this.refreshForecastCounts();
        }
        if (card.location_from === 'achievements' || card.location_to === 'achievements') {
            this.refreshAchievementsCounts();
        }
        if (card.location_from === 'safe' || card.location_to === 'safe') {
            this.refreshSafeCounts();
        }
        // Add tooltip to card
        if ((visible_to || card.is_relic) && this.canShowCardTooltip(card.id)) {
            card.owner = card.owner_to;
            card['location'] = card.location_to;
            card.position = card.position_to;
            card.splay_direction = card.splay_direction_to;
            this.addTooltipForCard(card);
        }
        else if (card.location_to == 'achievements' && card.age !== null) {
            var HTML_id = this.getCardHTMLId(card.id, card.age, card.type, card.is_relic, zone_from.HTML_class);
            this.removeTooltip(HTML_id);
            card.owner = card.owner_to;
            card['location'] = card.location_to;
            card.position = card.position_to;
        }
        else if (card.location_to == 'achievements' && card.age === null) {
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
    };
    Innovation.prototype.notif_logWithCardTooltips = function (notif) {
        // Add tooltips to game log
        for (var i = 0; i < notif.args.card_ids.length; i++) {
            var card_id = notif.args.card_ids[i];
            if (this.canShowCardTooltip(card_id)) {
                this.addCustomTooltipToClass("card_id_" + card_id, this.getTooltipForCard(card_id), "");
            }
        }
    };
    Innovation.prototype.notif_splayedPile = function (notif) {
        var player_id = notif.args.player_id;
        var color = notif.args.color;
        var splay_direction = Number(notif.args.splay_direction);
        var splay_direction_in_clear = notif.args.splay_direction_in_clear;
        var forced_unsplay = notif.args.forced_unsplay;
        var new_score = notif.args.new_score;
        // Change the splay mode of the matching zone on board
        this.refreshSplay(this.zone["board"][player_id][color], splay_direction);
        // Update the splay indicator
        var splay_indicator = 'splay_indicator_' + player_id + '_' + color;
        for (var direction = 0; direction <= 4; direction++) {
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
            this.addCustomTooltip('splay_indicator_' + player_id + '_' + color, dojo.string.substitute(_('This stack is splayed ${direction}.'), { 'direction': '<b>' + _(splay_direction_in_clear) + '</b>' }), '');
        }
        // Update the score for that player
        if (new_score !== undefined) {
            this.counter["score"][player_id].setValue(new_score);
        }
        // Update the ressource counts for that player
        if (splay_direction > 0 || forced_unsplay) {
            for (var icon = 1; icon <= 7; icon++) {
                this.counter["resource_count"][player_id][icon].setValue(notif.args.new_ressource_counts[icon]);
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
        // Splays often cause the forecast and safe limit to change
        if (this.gamedatas.echoes_expansion_enabled && this.gamedatas.fourth_edition) {
            this.refreshForecastCounts();
        }
        if (this.gamedatas.unseen_expansion_enabled) {
            this.refreshSafeCounts();
        }
        // Update special achievements overview with progression towards each achievement
        this.refreshSpecialAchievementProgression();
    };
    Innovation.prototype.notif_rearrangedPile = function (notif) {
        var player_id = notif.args.player_id;
        this.counter["max_age_on_board"][player_id].setValue(notif.args.new_max_age_on_board);
        // Apply the permutations if either an opponent rearranged their stack or the game is being
        // replayed (which eliminates a bug where Publications doesn't rearrange cards during replays)
        if (this.player_id != player_id || this.isInReplayMode()) {
            var rearrangement = notif.args.rearrangement;
            var color = rearrangement.color;
            var permutations_done = rearrangement.permutations_done;
            var permuted_zone = this.zone["board"][player_id][color];
            for (var i = 0; i < permutations_done.length; i++) {
                var permutation = permutations_done[i];
                this.publicationSwap(player_id, permuted_zone, permutation.position, permutation.delta);
            }
        }
        // Update special achievements overview with progression towards each achievement
        this.refreshSpecialAchievementProgression();
    };
    Innovation.prototype.notif_removedPlayer = function (notif) {
        var player_id = notif.args.player_to_remove;
        dojo.byId('player_' + player_id).style.display = 'none';
    };
    Innovation.prototype.notif_updateResourcesForArtifactOnDisplay = function (notif) {
        this.updateResourcesForArtifactOnDisplay(notif.args.player_id, notif.args.resource_icon, notif.args.resource_count_delta);
    };
    Innovation.prototype.updateResourcesForArtifactOnDisplay = function (player_id, resource_icon, resource_count_delta) {
        if (resource_count_delta != 0) {
            var previous_value = this.counter["resource_count"][player_id][resource_icon].getValue();
            this.counter["resource_count"][player_id][resource_icon].setValue(previous_value + resource_count_delta);
        }
        // If icon count is increasing, then this is the start of the free action
        if (resource_count_delta > 0) {
            for (var icon = 1; icon <= 7; icon++) {
                var opacity = icon == resource_icon ? 1 : 0.5;
                dojo.query(".player_info .ressource_" + icon).style("opacity", opacity);
            }
            // If icon count is decreasing, then this is the end of the free action
        }
        else {
            for (var icon = 1; icon <= 7; icon++) {
                dojo.query(".player_info .ressource_" + icon).style("opacity", 1);
            }
        }
    };
    Innovation.prototype.notif_resetMonumentCounters = function (notif) {
        this.number_of_scored_cards = 0;
        this.number_of_tucked_cards = 0;
        this.refreshSpecialAchievementProgression();
    };
    Innovation.prototype.notif_endOfGame = function (notif) {
        if (notif.args.end_of_game_type != 'achievements') {
            dojo.query(".achievements_to_win").forEach(function (node) {
                node.style.display = 'none';
            });
        }
    };
    Innovation.prototype.notif_log = function (notif) {
        // No change on the interface
        return;
    };
    /*
        * This special notification is the only one spectators can subscribe to.
        * They redirect to normal notification adressed to players which are not involved by the current action.
        *
        */
    Innovation.prototype.notif_transferedCard_spectator = function (notif) {
        // Put the message for the spectator in log
        this.log_for_spectator(notif);
        // Call normal notif
        this.notif_transferedCard(notif);
    };
    Innovation.prototype.notif_logWithCardTooltips_spectator = function (notif) {
        // Put the message for the spectator in log
        this.log_for_spectator(notif);
        // Call normal notif
        this.notif_logWithCardTooltips(notif);
    };
    Innovation.prototype.notif_splayedPile_spectator = function (notif) {
        // Put the message for the spectator in log
        this.log_for_spectator(notif);
        // Call normal notif
        this.notif_splayedPile(notif);
    };
    Innovation.prototype.notif_rearrangedPile_spectator = function (notif) {
        // Put the message for the spectator in log
        this.log_for_spectator(notif);
        // Call normal notif
        this.notif_rearrangedPile(notif);
    };
    Innovation.prototype.notif_removedPlayer_spectator = function (notif) {
        // Put the message for the spectator in log
        this.log_for_spectator(notif);
        // Call normal notif
        this.notif_removedPlayer(notif);
    };
    Innovation.prototype.notif_updateResourcesForArtifactOnDisplay_spectator = function (notif) {
        // Put the message for the spectator in log
        this.log_for_spectator(notif);
        // Call normal notif
        this.notif_updateResourcesForArtifactOnDisplay(notif);
    };
    Innovation.prototype.notif_resetMonumentCounters_spectator = function (notif) {
        // Put the message for the spectator in log
        this.log_for_spectator(notif);
        // Call normal notif
        this.notif_resetMonumentCounters(notif);
    };
    Innovation.prototype.notif_endOfGame_spectator = function (notif) {
        // Put the message for the spectator in log
        this.log_for_spectator(notif);
        // Call normal notif
        this.notif_endOfGame(notif);
    };
    Innovation.prototype.notif_log_spectator = function (notif) {
        // Put the message for the spectator in log
        this.log_for_spectator(notif);
        // Call normal notif
        this.notif_log(notif);
    };
    Innovation.prototype.log_for_spectator = function (notif) {
        notif.args = this.notifqueue.playerNameFilterGame(notif.args);
        notif.args.log = this.format_string_recursive(notif.args.log, notif.args); // Enable translation
        var log = "<div class='log' style='height: auto; display: block; color: rgb(0, 0, 0);'><div class='roundedbox'>" + notif.args.log + "</div></div>";
        dojo.place(log, $('logs'), 'first');
    };
    /* This enable to inject translatable styled things to logs or action bar */
    /* @Override */
    Innovation.prototype.format_string_recursive = function (log, args) {
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
                if (this.player_id == args.opponent_id && args.message_for_opponent) { // Is that player the opponent?
                    args.message_for_others = args.message_for_opponent;
                }
            }
        }
        catch (e) {
            console.error(log, args, "Exception thrown", e.stack);
        }
        return this.inherited(arguments);
    };
    /* Implementation of proper colored You with background in case of white or light colors  */
    Innovation.prototype.getColoredText = function (translatable_text, player_id) {
        if (player_id === void 0) { player_id = this.player_id; }
        var color = this.gamedatas.players[player_id].color;
        return "<span style='font-weight:bold;color:#" + color + "'>" + translatable_text + "</span>";
    };
    Innovation.prototype.getCardChain = function (args) {
        var cards = [];
        var i = 0;
        while (true) {
            if (typeof args['card_' + i] != 'string') {
                break;
            }
            cards.push(this.getColoredText(_(args['card_' + i]), args['ref_player_' + i]));
            i++;
        }
        var arrow = '&rarr;';
        return cards.join(arrow);
    };
    Innovation.prototype.canShowCardTooltip = function (card_id) {
        if (card_id == undefined) {
            return false;
        }
        if (isFlag(card_id) || isFountain(card_id)) {
            return true;
        }
        return this.cards[card_id].age !== null &&
            (card_id != 215 || this.gamedatas.cities_expansion_enabled) &&
            (card_id != 218 || this.gamedatas.figures_expansion_enabled) &&
            (card_id != 219 || this.gamedatas.echoes_expansion_enabled);
    };
    // Returns true if the current player is a spectator or if the game is currently in replay mode
    Innovation.prototype.isReadOnly = function () {
        return this.isSpectator || this.isInReplayMode();
    };
    // Returns true if the game is ongoing but the user clicked "replay from this move" in the log or the game is in archive mode after the game has ended
    Innovation.prototype.isInReplayMode = function () {
        return typeof g_replayFrom != 'undefined' || g_archive_mode;
    };
    return Innovation;
}(BgaGame));
define([
    "dojo",
    "dojo/_base/declare",
    "ebg/core/gamegui",
    "ebg/counter",
    "ebg/zone"
], function (dojo, declare) {
    return declare("bgagame.innovation", ebg.core.gamegui, new Innovation());
});
