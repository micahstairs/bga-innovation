
-- ------
-- BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
-- Innovation implementation : © Jean Portemer <jportemer@gmail.com>
-- 
-- This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
-- See http://en.boardgamearena.com/#!doc/Studio for more information.
-- -----

-- dbmodel.sql

-- Note: The database schema is created from this file when the game starts. If you modify this file,
--       you have to restart a game to see your changes in database.

/* New columns for table player */
ALTER TABLE `player` ADD `player_team` SMALLINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Team number (same for players in a same team)';
ALTER TABLE `player` ADD `player_innovation_score` SMALLINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Innovation score (player_score is used for achievements in normal play)';
ALTER TABLE `player` ADD `player_icon_count_1` SMALLINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Total of crowns on player board';
ALTER TABLE `player` ADD `player_icon_count_2` SMALLINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Total of leaves on player board';
ALTER TABLE `player` ADD `player_icon_count_3` SMALLINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Total of bulbs on player board';
ALTER TABLE `player` ADD `player_icon_count_4` SMALLINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Total of towers on player board';
ALTER TABLE `player` ADD `player_icon_count_5` SMALLINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Total of factories on player board';
ALTER TABLE `player` ADD `player_icon_count_6` SMALLINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Total of clocks on player board';
/* stronger_or_equal is deprecated */
ALTER TABLE `player` ADD `stronger_or_equal` BOOLEAN DEFAULT NULL COMMENT 'When in dogma state, TRUE if the player can share the non-demand effects, FALSE if the player has to execute "I demand" effects';
ALTER TABLE `player` ADD `featured_icon_count` SMALLINT UNSIGNED DEFAULT NULL COMMENT 'Number of visible icons matching the featured icon at the start of the dogma effect';
ALTER TABLE `player` ADD `turn_order_ending_with_launcher` SMALLINT UNSIGNED DEFAULT NULL COMMENT 'Turn order ending with the player who launched the current dogma effect (these values will not necessarily be consecutive)';
/* player_no_under_effect is deprecated */
ALTER TABLE `player` ADD `player_no_under_effect` TINYINT UNSIGNED DEFAULT NULL COMMENT 'Order of the player when he is concerned by an effect';
ALTER TABLE `player` ADD `number_of_tucked_cards` TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Number of cards the player has tucked during the turn of the current player';
ALTER TABLE `player` ADD `number_of_scored_cards` TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Number of cards the player has scored during the turn of the current player';
ALTER TABLE `player` ADD `pile_display_mode` BOOLEAN DEFAULT TRUE COMMENT 'Wish for the player for pile display, TRUE for expanded, FALSE for compact';
ALTER TABLE `player` ADD `pile_view_full` BOOLEAN DEFAULT FALSE COMMENT 'Wish for the player to view all cards in pile, TRUE if yes, FALSE if no';
ALTER TABLE `player` ADD `effects_had_impact` BOOLEAN DEFAULT FALSE COMMENT 'Indicate if the player has changed the situation (TRUE) or not (FALSE) in the game when it was his turn to play within a dogma effect';

/* Main table to store all the cards of the game and their characteristics. See the material file to see the textual info */
CREATE TABLE IF NOT EXISTS `card` (
  `id` SMALLINT UNSIGNED NOT NULL COMMENT '0-104 for base cards, 105-109 for base special achievements, 110-214 for artifact cards, 215-219 for relics',
  `type` TINYINT UNSIGNED NOT NULL COMMENT '0 for base, 1 for artifacts, 2 for cities, 3 for echoes, 4 for figures',
  `age` TINYINT UNSIGNED DEFAULT NULL COMMENT '1 to 10, NULL for a special achievement',
  `faceup_age` TINYINT UNSIGNED DEFAULT NULL COMMENT 'The same as age, except Battleship Yamato is an 11 instead of 8 (dynamically populated)',
  `color` TINYINT UNSIGNED DEFAULT NULL COMMENT '0 (blue), 1 (red), 2 (green), 3 (yellow), 4 (purple) or NULL for a special achievement',
  `spot_1` TINYINT UNSIGNED DEFAULT NULL COMMENT 'Icon on top-left, 0 (hexagon), 1 (crown), 2 (leaf), 3 (bulb), 4 (tower), 5 (factory), 6 (clock) or NULL for no icon (e.g. special achievement)',
  `spot_2` TINYINT UNSIGNED DEFAULT NULL COMMENT 'Icon on bottom-left, 0 (hexagon), 1 (crown), 2 (leaf), 3 (bulb), 4 (tower), 5 (factory), 6 (clock) or NULL for no icon (e.g. special achievement)',
  `spot_3` TINYINT UNSIGNED DEFAULT NULL COMMENT 'Icon on bottom-middle, 0 (hexagon), 1 (crown), 2 (leaf), 3 (bulb), 4 (tower), 5 (factory), 6 (clock) or NULL for no icon (e.g. special achievement)',
  `spot_4` TINYINT UNSIGNED DEFAULT NULL COMMENT 'Icon on bottom-right, 0 (hexagon), 1 (crown), 2 (leaf), 3 (bulb), 4 (tower), 5 (factory), 6 (clock) or NULL for no icon (e.g. special achievement)',
  `dogma_icon` TINYINT UNSIGNED DEFAULT NULL COMMENT 'Feature icon for dogma, 1 (crown), 2 (leaf), 3 (bulb), 4 (tower), 5 (factory), 6 (clock) or NULL for no icon (e.g. special achievement)',
  `has_demand` BOOLEAN NOT NULL DEFAULT FALSE COMMENT 'Whether or not the card has at least one demand effect (will be populated using data in material.inc.php file)',
  `is_relic` BOOLEAN NOT NULL DEFAULT FALSE COMMENT 'Whether or not the card is a relic',
  `owner` INT(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Id of the player who owns the card or 0 if no owner',
  `location` VARCHAR(12) NOT NULL DEFAULT 'deck' COMMENT 'Hand, board, score, achievements, deck, display or revealed (achievements can be used both with owner = 0 (available achievement) or with a player as owner (the player has earned that achievement))',
  `position` TINYINT UNSIGNED DEFAULT 0 COMMENT 'Position in the given location. Bottom is zero (last card in deck), top is max. For hands, the cards are sorted by age before being sorted by position. For boards, the positions reflect the order in the color piles, 0 for the bottom card, maximum for active card.',
  `splay_direction` TINYINT UNSIGNED DEFAULT NULL COMMENT 'Direction of the splay, 0 (no-splay), 1 (left), 2 (right), 3 (up) OR NULL if this card is not on board',
  `selected` BOOLEAN NOT NULL DEFAULT FALSE COMMENT 'Temporary flag to indicate whether the card is selected by its owner or not',
  `icon_hash` INT(32) UNSIGNED DEFAULT NULL COMMENT 'A column that is updated on game start with a calculated hash of the card icons. This is for icon comparisson purposes regardless of the icon position.',
  PRIMARY KEY(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/* Table used to manage the execution of nested effects */
/* TODO: Add defaults for some of these columns. */
CREATE TABLE IF NOT EXISTS `nested_card_execution` (
 `nesting_index` SMALLINT UNSIGNED NOT NULL COMMENT 'The index of the nesting (1 is for the original card, 2 is for the next card, etc.)',
 `card_id` SMALLINT COMMENT '-1 means no card',
 `card_location` VARCHAR(12) DEFAULT NULL COMMENT 'The initial location of the card when its dogma was executed (board, display, or NULL)',
 `launcher_id` INT(10) NOT NULL COMMENT 'ID of the player who initially launched this card',
 `current_player_id` INT(10) DEFAULT NULL COMMENT 'ID of the player currently executing the card',
 `execute_demand_effects` BOOLEAN DEFAULT TRUE COMMENT 'Whether demand effects should be executed',
 `current_effect_type` TINYINT COMMENT '-1=unset, 0=demand, 1=non-demand, 2=compel',
 `current_effect_number` TINYINT COMMENT '-1 (unset), 1, 2, or 3 (no cards have more than 3 effects on them)',
 `step` TINYINT COMMENT 'The interaction that the card is on',
 `step_max` TINYINT COMMENT 'The anticipated number of interactions that the card will have',
 `post_execution_index` TINYINT DEFAULT 0 COMMENT '0 means the effect has not triggered another card, 1 means the effect already triggered another card and resumed executing this effect',
 `auxiliary_value` INT DEFAULT -1 COMMENT 'An auxiliary value used by certain card implementations',
 `auxiliary_value_2` INT DEFAULT -1 COMMENT 'A second auxiliary value used by certain card implementations',
  PRIMARY KEY(`nesting_index`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/* Auxiliary tables: these are only used when needed to update card or player and their content is deleted after that */
CREATE TABLE IF NOT EXISTS `random` (
 `id` SMALLINT UNSIGNED NOT NULL,
 `type` TINYINT UNSIGNED,
 `age` TINYINT UNSIGNED,
 `random_number` DOUBLE NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `shuffled` (
 `id` SMALLINT UNSIGNED NOT NULL,
 `new_position` TINYINT UNSIGNED 
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `base` (
 `icon` TINYINT
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `card_with_top_card_indication` AS
	SELECT
		*
	FROM
		card
	WHERE
		FALSE IS TRUE;
ALTER TABLE `card_with_top_card_indication` ADD `is_top_card` BOOLEAN NOT NULL;

CREATE TABLE `icon_count` (
 `icon` TINYINT,
 `count` SMALLINT UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/* Insert special achievement cards */

INSERT INTO `card` (`id`, `type`, `location`, `position`) VALUES

(105, 0, 'achievements', 10),
(106, 0, 'achievements', 11),
(107, 0, 'achievements', 12),
(108, 0, 'achievements', 13),
(109, 0, 'achievements', 14);

/* Insert relic cards */

INSERT INTO `card` (`id`, `type`, `age`, `color`, `spot_1`, `spot_2`, `spot_3`, `spot_4`, `dogma_icon`, `is_relic`, `location`, `position`) VALUES

/* TODO: When implementing Cities, add extra icons to this card */
(215, 2, 3, 2, 3, 1, 1, 1, NULL, TRUE, 'relics', 0),
(216, 0, 4, 0, 5, 0, 3, 3, 3,    TRUE, 'relics', 1),
(217, 1, 5, 4, 5, 3, 5, 0, 5,    TRUE, 'relics', 2),
(218, 4, 6, 1, 6, 6, 0, 2, 6,    TRUE, 'relics', 3),
/* TODO: When implementing Echoes, add Echo effect to this card */
(219, 3, 7, 3, 0, 0, 2, 2, 2,    TRUE, 'relics', 4);

/* Insert normal cards */

INSERT INTO `card` (`id`, `type`, `age`, `color`, `spot_1`, `spot_2`, `spot_3`, `spot_4`, `dogma_icon`) VALUES

/* Age 1 */
(0, 0, 1, 0, 0, 2, 2, 2, 2),
(1, 0, 1, 0, 0, 3, 3, 4, 3),
(2, 0, 1, 0, 0, 3, 3, 1, 3),
(3, 0, 1, 1, 4, 3, 0, 4, 4),
(4, 0, 1, 1, 4, 4, 0, 4, 4),
(5, 0, 1, 1, 4, 1, 0, 4, 4),
(6, 0, 1, 2, 0, 1, 2, 2, 2),
(7, 0, 1, 2, 1, 1, 0, 2, 1),
(8, 0, 1, 2, 0, 4, 4, 4, 4),
(9, 0, 1, 3, 0, 2, 2, 2, 2),
(10, 0, 1, 3, 4, 1, 0, 4, 4),
(11, 0, 1, 3, 4, 0, 4, 4, 4),
(12, 0, 1, 4, 0, 1, 1, 4, 1),
(13, 0, 1, 4, 0, 1, 1, 2, 1),
(14, 0, 1, 4, 0, 4, 4, 4, 4),

/* Age 2 */
(15, 0, 2, 0, 0, 2, 2, 3, 2),
(16, 0, 2, 0, 0, 3, 1, 3, 3),
(17, 0, 2, 1, 4, 0, 4, 4, 4),
(18, 0, 2, 1, 4, 4, 0, 4, 4),
(19, 0, 2, 2, 2, 1, 0, 1, 1),
(20, 0, 2, 2, 0, 1, 1, 4, 1),
(21, 0, 2, 3, 0, 1, 2, 1, 1),
(22, 0, 2, 3, 2, 2, 0, 4, 2),
(23, 0, 2, 4, 0, 4, 4, 4, 4),
(24, 0, 2, 4, 0, 3, 3, 3, 3),

/* Age 3 */
(25, 0, 3, 0, 0, 2, 4, 4, 4),
(26, 0, 3, 0, 0, 1, 1, 1, 1),
(27, 0, 3, 1, 4, 0, 3, 4, 4),
(28, 0, 3, 1, 1, 1, 1, 0, 1),
(29, 0, 3, 2, 0, 1, 1, 2, 1),
(30, 0, 3, 2, 0, 3, 3, 1, 3),
(31, 0, 3, 3, 2, 2, 0, 4, 2),
(32, 0, 3, 3, 1, 2, 2, 0, 2),
(33, 0, 3, 4, 3, 3, 3, 0, 3),
(34, 0, 3, 4, 0, 4, 2, 4, 4),

/* Age 4 */
(35, 0, 4, 0, 0, 3, 3, 3, 3),
(36, 0, 4, 0, 0, 3, 3, 1, 3),
(37, 0, 4, 1, 0, 5, 3, 5, 5),
(38, 0, 4, 1, 0, 5, 1, 5, 5),
(39, 0, 4, 2, 0, 3, 3, 5, 3),
(40, 0, 4, 2, 0, 1, 1, 1, 1),
(41, 0, 4, 3, 2, 2, 2, 0, 2),
(42, 0, 4, 3, 0, 3, 3, 2, 3),
(43, 0, 4, 4, 0, 1, 1, 1, 1),
(44, 0, 4, 4, 2, 2, 0, 2, 2),

/* Age 5 */
(45, 0, 5, 0, 5, 3, 5, 0, 5),
(46, 0, 5, 0, 5, 3, 3, 0, 3),
(47, 0, 5, 1, 5, 5, 5, 0, 5),
(48, 0, 5, 1, 1, 5, 1, 0, 1),
(49, 0, 5, 2, 5, 1, 0, 1, 1),
(50, 0, 5, 2, 3, 2, 3, 0, 3),
(51, 0, 5, 3, 2, 3, 2, 0, 2),
(52, 0, 5, 3, 0, 5, 1, 5, 5),
(53, 0, 5, 4, 1, 3, 3, 0, 3),
(54, 0, 5, 4, 1, 0, 3, 1, 1),

/* Age 6 */
(55, 0, 6, 0, 3, 3, 3, 0, 3),
(56, 0, 6, 0, 0, 1, 1, 1, 1),
(57, 0, 6, 1, 1, 5, 5, 0, 5),
(58, 0, 6, 1, 5, 5, 0, 5, 5),
(59, 0, 6, 2, 3, 3, 3, 0, 3),
(60, 0, 6, 2, 0, 5, 1, 1, 1),
(61, 0, 6, 3, 0, 5, 2, 5, 5),
(62, 0, 6, 3, 2, 5, 2, 0, 2),
(63, 0, 6, 4, 1, 3, 3, 0, 3),
(64, 0, 6, 4, 5, 3, 5, 0, 5),

/* Age 7 */
(65, 0, 7, 0, 3, 3, 3, 0, 3),
(66, 0, 7, 0, 0, 3, 6, 3, 3),
(67, 0, 7, 1, 1, 1, 5, 0, 1),
(68, 0, 7, 1, 0, 5, 5, 5, 5),
(69, 0, 7, 2, 1, 1, 6, 0, 1),
(70, 0, 7, 2, 3, 5, 0, 5, 5),
(71, 0, 7, 3, 0, 2, 2, 1, 2),
(72, 0, 7, 3, 2, 2, 0, 2, 2),
(73, 0, 7, 4, 0, 2, 6, 2, 2),
(74, 0, 7, 4, 6, 5, 6, 0, 6),

/* Age 8 */
(75, 0, 8, 0, 6, 6, 6, 0, 6),
(76, 0, 8, 0, 6, 6, 6, 0, 6),
(77, 0, 8, 1, 1, 0, 6, 1, 1),
(78, 0, 8, 1, 0, 5, 6, 5, 5),
(79, 0, 8, 2, 0, 5, 5, 1, 5),
(80, 0, 8, 2, 3, 0, 6, 3, 3),
(81, 0, 8, 3, 2, 2, 2, 0, 2),
(82, 0, 8, 3, 0, 5, 1, 1, 1),
(83, 0, 8, 4, 3, 3, 3, 0, 3),
(84, 0, 8, 4, 2, 0, 2, 2, 2),

/* Age 9 */
(85, 0, 9, 0, 6, 0, 6, 5, 6),
(86, 0, 9, 0, 3, 3, 3, 0, 3),
(87, 0, 9, 1, 5, 5, 0, 5, 5),
(88, 0, 9, 1, 0, 6, 6, 6, 6),
(89, 0, 9, 2, 0, 1, 6, 1, 1),
(90, 0, 9, 2, 0, 6, 6, 6, 6),
(91, 0, 9, 3, 2, 3, 3, 0, 3),
(92, 0, 9, 3, 0, 1, 2, 2, 2),
(93, 0, 9, 4, 0, 2, 2, 2, 2),
(94, 0, 9, 4, 0, 5, 2, 5, 5),

/* Age 10 */
(95, 0, 10, 0, 3, 6, 6, 0, 6),
(96, 0, 10, 0, 6, 6, 6, 0, 6),
(97, 0, 10, 1, 0, 3, 6, 3, 3),
(98, 0, 10, 1, 0, 5, 6, 5, 5),
(99, 0, 10, 2, 0, 6, 6, 6, 6),
(100, 0, 10, 2, 0, 1, 1, 1, 1),
(101, 0, 10, 3, 0, 5, 5, 5, 5),
(102, 0, 10, 3, 0, 2, 2, 2, 2),
(103, 0, 10, 4, 3, 3, 6, 0, 3),
(104, 0, 10, 4, 0, 6, 6, 3, 6),

/* Artifacts - Age 1 */
(110, 1, 1, 0, 1, 4, 0, 4, 4),
(111, 1, 1, 0, 4, 4, 4, 0, 4),
(112, 1, 1, 0, 1, 0, 2, 2, 2),
(113, 1, 1, 1, 4, 2, 0, 2, 2),
(114, 1, 1, 1, 2, 2, 2, 0, 2),
(115, 1, 1, 1, 0, 1, 4, 1, 1),
(116, 1, 1, 2, 4, 0, 4, 4, 4),
(117, 1, 1, 2, 1, 0, 4, 1, 1),
(118, 1, 1, 2, 0, 1, 4, 4, 4),
(119, 1, 1, 3, 0, 3, 3, 3, 3),
(120, 1, 1, 3, 0, 1, 1, 1, 1),
(121, 1, 1, 3, 0, 2, 2, 2, 2),
(122, 1, 1, 4, 4, 4, 0, 4, 4),
(123, 1, 1, 4, 0, 4, 4, 4, 4),
(124, 1, 1, 4, 0, 4, 3, 3, 3),

/* Artifacts - Age 2 */
(125, 1, 2, 0, 2, 2, 2, 0, 2),
(126, 1, 2, 0, 4, 4, 4, 0, 4),
(127, 1, 2, 1, 1, 0, 3, 3, 3),
(128, 1, 2, 1, 0, 4, 4, 4, 4),
(129, 1, 2, 2, 4, 2, 0, 4, 4),
(130, 1, 2, 2, 1, 3, 0, 1, 1),
(131, 1, 2, 3, 2, 0, 1, 2, 2),
(132, 1, 2, 3, 1, 1, 1, 0, 1),
(133, 1, 2, 4, 0, 4, 3, 4, 4),
(134, 1, 2, 4, 0, 3, 3, 4, 3),

/* Artifacts - Age 3 */
(135, 1, 3, 0, 3, 3, 3, 0, 3),
(137, 1, 3, 1, 1, 0, 4, 4, 4),
(138, 1, 3, 1, 0, 4, 4, 3, 4),
(139, 1, 3, 2, 1, 1, 1, 0, 1),
(140, 1, 3, 2, 1, 1, 0, 1, 1),
(141, 1, 3, 3, 4, 2, 0, 4, 4),
(142, 1, 3, 3, 1, 1, 1, 0, 1),
(143, 1, 3, 4, 0, 2, 2, 2, 2),
(144, 1, 3, 4, 2, 0, 2, 2, 2),

/* Artifacts - Age 4 */
(145, 1, 4, 0, 3, 0, 1, 3, 3),
(146, 1, 4, 0, 5, 0, 3, 3, 3),
(147, 1, 4, 1, 1, 5, 5, 0, 5),
(148, 1, 4, 1, 5, 5, 1, 0, 5),
(149, 1, 4, 2, 3, 3, 3, 0, 3),
(150, 1, 4, 2, 3, 3, 0, 3, 3),
(151, 1, 4, 3, 2, 2, 0, 2, 2),
(152, 1, 4, 3, 0, 1, 2, 2, 2),
(153, 1, 4, 4, 1, 1, 0, 1, 1),
(154, 1, 4, 4, 2, 0, 1, 2, 2),

/* Artifacts - Age 5 */
(155, 1, 5, 0, 3, 5, 3, 0, 3),
(156, 1, 5, 0, 3, 3, 3, 0, 3),
(157, 1, 5, 1, 0, 2, 3, 3, 3),
(158, 1, 5, 1, 5, 0, 5, 5, 5),
(159, 1, 5, 2, 5, 0, 5, 1, 5),
(160, 1, 5, 2, 1, 0, 5, 1, 1),
(163, 1, 5, 4, 2, 2, 5, 0, 2),
(164, 1, 5, 4, 1, 0, 1, 1, 1),

/* Artifacts - Age 6 */
(165, 1, 6, 0, 5, 0, 5, 3, 5),
(166, 1, 6, 0, 5, 0, 5, 5, 5),
(167, 1, 6, 1, 0, 5, 5, 5, 5),
(168, 1, 6, 1, 1, 1, 3, 0, 1),
(169, 1, 6, 2, 1, 5, 0, 1, 1),
(170, 1, 6, 2, 5, 1, 5, 0, 5),
(171, 1, 6, 3, 0, 1, 3, 3, 3),
(172, 1, 6, 3, 0, 3, 2, 3, 3),
(173, 1, 6, 4, 3, 3, 0, 3, 3),
(174, 1, 6, 4, 2, 2, 0, 1, 2),

/* Artifacts - Age 7 */
(175, 1, 7, 0, 3, 0, 3, 3, 3),
(176, 1, 7, 0, 2, 3, 0, 2, 2),
(177, 1, 7, 1, 5, 5, 5, 0, 5),
(178, 1, 7, 1, 0, 6, 3, 3, 3),
(179, 1, 7, 2, 1, 0, 1, 5, 1),
(180, 1, 7, 2, 6, 2, 6, 0, 6),
(181, 1, 7, 3, 5, 0, 5, 1, 5),
(182, 1, 7, 3, 0, 5, 6, 6, 6),
(183, 1, 7, 4, 2, 2, 2, 0, 2),
(184, 1, 7, 4, 1, 1, 0, 2, 1),

/* Artifacts - Age 8 */
(185, 1, 8, 0, 3, 3, 3, 0, 3),
(186, 1, 8, 0, 6, 0, 6, 6, 6),
(187, 1, 8, 1, 0, 5, 5, 5, 5),
(188, 1, 8, 1, NULL, 0, NULL, NULL, NULL),
(189, 1, 8, 2, 1, 5, 1, 0, 1),
(190, 1, 8, 2, 2, 1, 0, 1, 1),
(191, 1, 8, 3, 2, 2, 2, 0, 2),
(192, 1, 8, 3, 0, 6, 6, 3, 6),
(193, 1, 8, 4, 0, 6, 6, 6, 6),
(194, 1, 8, 4, 2, 2, 6, 0, 2),

/* Artifacts - Age 9 */
(195, 1, 9, 0, 6, 6, 5, 0, 6),
(196, 1, 9, 0, 5, 0, 5, 5, 5),
(197, 1, 9, 1, 0, 2, 6, 2, 2),
(198, 1, 9, 1, 5, 5, 6, 0, 5),
(199, 1, 9, 2, 0, 2, 2, 2, 2),
(200, 1, 9, 2, 0, 6, 6, 6, 6),
(201, 1, 9, 3, 2, 6, 0, 6, 6),
(202, 1, 9, 3, 3, 2, 0, 3, 3),
(203, 1, 9, 4, 3, 0, 3, 3, 3),
(204, 1, 9, 4, 1, 1, 1, 0, 1),

/* Artifacts - Age 10 */
(205, 1,10, 0, 0, 3, 3, 3, 3),
(207, 1,10, 1, 5, 0, 5, 5, 5),
(208, 1,10, 1, 6, 0, 6, 6, 6),
(209, 1,10, 2, 1, 1, 0, 1, 1),
(210, 1,10, 2, 6, 6, 6, 0, 6),
(211, 1,10, 3, 0, 6, 2, 6, 6),
(214, 1,10, 4, 0, 5, 5, 6, 5);