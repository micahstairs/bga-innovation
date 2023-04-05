/**
 * Framework interfaces
 */

declare var g_replayFrom: number | undefined;
declare var g_archive_mode: boolean;
declare function _(str: string): string;
declare function $(text: string | Element): HTMLElement;
declare type eventhandler = (event?: any) => void;

type ElementOrId = Element | string;

declare type Player = {
	// Populated by BGA automatically
    name: string;
	beginner: boolean;
    color: string;
    color_back: any | null;
    eliminated: number;
    zombie: number;

	// Populated by Innovation::getAllDatas
	achievement_count: number;
	player_team: number;
}

declare type InnovationGameDatas = {
	players: {
		[player_id: number]: Player
	};
	current_player_id: number;
	number_of_achievements_needed_to_win: number;
	fourth_edition: boolean;
	artifacts_expansion_enabled: boolean;
	relics_enabled: boolean;
	cities_expansion_enabled: boolean;
	echoes_expansion_enabled: boolean;
	figures_expansion_enabled: boolean;
};

declare class GameGui {
	gamedatas: InnovationGameDatas;
	player_id: number;
	isSpectator: boolean;
	notifqueue: GameNotifQueue;
	scoreCtrl: { [player_id: number]: Counter };
	prefs: { [index: number]: { value: number } };
	gameinterface_zoomFactor: number;
	default_viewport: string;

	setup(gamedatas: any): void;
    onEnteringState(stateName: string, args: any): void;
    onLeavingState(stateName: string ): void;
    onUpdateActionButtons(stateName: string, args: any): void;
    setupNotifications(): void;
    format_string_recursive(log: string, args: any): void;
	inherited(args: any): any;
	format_block(id:string, args: any): any;

	isCurrentPlayerActive(): boolean;
	getActivePlayerId(): number;
	addActionButton(id: string, label: string, method: string | eventhandler, destination?: string, blinking?: boolean, color?: string): void;
	checkAction(action: any): boolean;
	ajaxcall(url: string, args: object, bind: GameGui, resultHandler: (result: any) => void, allHandler: (err: any) => void): void;
	connect(node: ElementOrId, ontype: string, handler: any): void;
	disconnect(node: ElementOrId, ontype: string): void;
	connectClass(cls: string, ontype: string, handler: any): void;

	addTooltip(nodeId: string, helpStringTranslated: string, actionStringTranslated: string, delay?: number): void;
	addTooltipHtml(nodeId: string, html: string, delay?: number): void;
	addTooltipHtmlToClass(cssClass: string, html: string, delay?: number): void;
	addTooltipToClass(cssClass: string, helpStringTranslated: string, actionStringTranslated: string, delay?: number): void;
	removeTooltip(nodeId: string): void;
}

declare interface Zone {
	location: string;
	owner: number;
	container_div: string;
	HTML_class: string;
	splay_direction: number;
	items: any;
	counter: any;
	grouped_by_age_type_and_is_relic: boolean;
	itemIdToCoordsGrid: Function;
	placeInZone(nodeId: string, index: number): void;
	removeFromZone(nodeId: string, destroy: boolean): void;
	updateDisplay(): void
}

declare class Counter {
	speed: number;

	create(target: string): void; //  associate counter with existing target DOM element
	getValue(): number; //  return current value
	incValue(by: number): number; //  increment value by "by" and animate from previous value
	setValue(value: number): void; //  set value, no animation
	toValue(value: number): void; // set value with animation
	disable(): void; // Sets value to "-"
}

interface Notif<T> {
    args: T;
    log: string;
    move_id: number;
    table_id: string;
    time: number;
    type: string;
    uid: string;
}

declare class GameNotifQueue {
	/**
	 * Set the notification deinfed by notif_type as "synchronous"
	 * @param notif_type - the type of notification
	 * @param duration - the duration of notification wait in milliseconds
	 * If "duration" is specified: set a simple timer for it (milliseconds)
	 * If "duration" is not specified, the notification handler MUST call "setSynchronousDuration"
	 */
	setSynchronous(notif_type: string, duration?: number): void;
	/**
	 * Set dynamically the duration of a synchronous notification
	 * MUST be called if your notification has not been associated with a duration in "setSynchronous"
	 * @param duration - how long to hold off till next notficiation received (milliseconds)
	 */
	setSynchronousDuration(duration: number): void;

	/**
	 * Ignore notification if predicate is true
	 * @param notif_type  - the type of notification
	 * @param predicate - the function that if returned true will make framework not dispatch notification.
	 * NOTE: this cannot be used for syncronious unbound notifications
	 */
	setIgnoreNotificationCheck(notif_type: string, predicate: (notif: object) => boolean): void;

	/** Add colors to player names */
	playerNameFilterGame(args: any): any;
}

interface Dojo {
	create: Function;
    place: Function;
    style: Function;
	setStyle: Function;
	attr: Function;
	getAttr: Function;
	hasClass: Function;
    addClass: (nodeId: string | HTMLElement, className: string) => {};
    removeClass: (nodeId: string | HTMLElement, className?: string) => {};
    toggleClass: (nodeId: string | HTMLElement, className: string, forceValue: boolean) => {};
    connect: Function;
    query: (selector: string) => DojoNodeList;
	body: Function;
	position: Function;
    subscribe: Function;
    string: any;
	byId: Function;
	destroy: Function;
	window: Dojo.Window;
	NodeList: Dojo.Constructor<DojoNodeList>;
}

declare module Dojo {
	interface Window {
		getBox: any;
	}
	interface Constructor<T> {
		new(): T;
	}
	var NodeList: Constructor<NodeList>;
}

declare class DojoNodeList extends Array {
	addClass(name: string): DojoNodeList;
	removeClass(name: string): DojoNodeList;
	removeAttr(name: string): DojoNodeList;
	style: Function;
}

declare module dijit {
	interface Constructor<T> {
		new(props?: any, id?: string): T;
		new(props?: any, element?: HTMLElement): T;
	}

	interface Dialog {
		show(): Promise<boolean>;
		hide(): Promise<boolean>;
		attr(key: string, val: string): Function;
	}
	var Dialog: Constructor<Dialog>;
}