
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
ALTER TABLE `player` ADD `pile_display_mode` BOOLEAN DEFAULT TRUE COMMENT 'Player preference for how stacks on the board are displayed, TRUE for expanded, FALSE for compact';
ALTER TABLE `player` ADD `pile_view_full` BOOLEAN DEFAULT FALSE COMMENT 'Player preference for whether to show all cards in a stack on the board, TRUE if yes, FALSE if no';
ALTER TABLE `player` ADD `effects_had_impact` BOOLEAN DEFAULT FALSE COMMENT 'Indicate if the player has changed the situation (TRUE) or not (FALSE) in the game when it was his turn to play within a dogma effect';

/* Main table to store all the cards of the game and their characteristics. See the material file to see the textual info */
CREATE TABLE IF NOT EXISTS `card` (
  `id` SMALLINT UNSIGNED NOT NULL COMMENT '0-104 for base cards, 105-109 for base special achievements, 110-214 for artifact cards, 215-219 for relics',
  `type` TINYINT UNSIGNED NOT NULL COMMENT '0 for base, 1 for artifacts, 2 for cities, 3 for echoes, 4 for figures',
  `age` TINYINT UNSIGNED DEFAULT NULL COMMENT '1 to 10, NULL for a special achievement',
  `faceup_age` TINYINT UNSIGNED DEFAULT NULL COMMENT 'The same as age, except Battleship Yamato is an 11 instead of 8 (dynamically populated)',
  `color` TINYINT UNSIGNED DEFAULT NULL COMMENT '0 (blue), 1 (red), 2 (green), 3 (yellow), 4 (purple) or NULL for a special achievement',
  `spot_1` TINYINT UNSIGNED DEFAULT NULL COMMENT 'Icon on bottom-left, 0 (hexagon), 1 (crown), 2 (leaf), 3 (bulb), 4 (tower), 5 (factory), 6 (clock), 7 (plus), 8 (flag), 9 (fountain), 10 (echo), 11 (left), 12 (right), 13 (up), 101-111 (1-11 bonus) or NULL for no icon (e.g. special achievement)',
  `spot_2` TINYINT UNSIGNED DEFAULT NULL COMMENT 'Icon on bottom-left, 0 (hexagon), 1 (crown), 2 (leaf), 3 (bulb), 4 (tower), 5 (factory), 6 (clock), 7 (plus), 8 (flag), 9 (fountain), 10 (echo), 11 (left), 12 (right), 13 (up), 101-111 (1-11 bonus) or NULL for no icon (e.g. special achievement)',
  `spot_3` TINYINT UNSIGNED DEFAULT NULL COMMENT 'Icon on bottom-left, 0 (hexagon), 1 (crown), 2 (leaf), 3 (bulb), 4 (tower), 5 (factory), 6 (clock), 7 (plus), 8 (flag), 9 (fountain), 10 (echo), 11 (left), 12 (right), 13 (up), 101-111 (1-11 bonus) or NULL for no icon (e.g. special achievement)',
  `spot_4` TINYINT UNSIGNED DEFAULT NULL COMMENT 'Icon on bottom-left, 0 (hexagon), 1 (crown), 2 (leaf), 3 (bulb), 4 (tower), 5 (factory), 6 (clock), 7 (plus), 8 (flag), 9 (fountain), 10 (echo), 11 (left), 12 (right), 13 (up), 101-111 (1-11 bonus) or NULL for no icon (e.g. special achievement)',
  `spot_5` TINYINT UNSIGNED DEFAULT NULL COMMENT 'Icon on bottom-left, 0 (hexagon), 1 (crown), 2 (leaf), 3 (bulb), 4 (tower), 5 (factory), 6 (clock), 7 (plus), 8 (flag), 9 (fountain), 10 (echo), 11 (left), 12 (right), 13 (up), 101-111 (1-11 bonus) or NULL for no icon (e.g. special achievement)',
  `spot_6` TINYINT UNSIGNED DEFAULT NULL COMMENT 'Icon on bottom-left, 0 (hexagon), 1 (crown), 2 (leaf), 3 (bulb), 4 (tower), 5 (factory), 6 (clock), 7 (plus), 8 (flag), 9 (fountain), 10 (echo), 11 (left), 12 (right), 13 (up), 101-111 (1-11 bonus) or NULL for no icon (e.g. special achievement)',
  `dogma_icon` TINYINT UNSIGNED DEFAULT NULL COMMENT 'Feature icon for dogma, 1 (crown), 2 (leaf), 3 (bulb), 4 (tower), 5 (factory), 6 (clock) or NULL for no icon (e.g. special achievement)',
  `has_demand` BOOLEAN NOT NULL DEFAULT FALSE COMMENT 'Whether or not the card has at least one demand effect (will be populated using data in material.inc.php file)',
  `is_relic` BOOLEAN NOT NULL DEFAULT FALSE COMMENT 'Whether or not the card is a relic',
  `owner` INT(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Id of the player who owns the card or 0 if no owner',
  `location` VARCHAR(12) NOT NULL DEFAULT 'deck' COMMENT 'Hand, board, score, achievements, deck, display or revealed (achievements can be used both with owner = 0 (available achievement) or with a player as owner (the player has earned that achievement))',
  `position` TINYINT UNSIGNED DEFAULT 0 COMMENT 'Position in the given location. Bottom is zero (last card in deck), top is max. For hands, the cards are sorted by age before being sorted by position. For boards, the positions reflect the order in the stacks, 0 for the bottom card, maximum for active card.',
  `splay_direction` TINYINT UNSIGNED DEFAULT NULL COMMENT 'Direction of the splay, 0 (no-splay), 1 (left), 2 (right), 3 (up) OR NULL if this card is not on board',
  `selected` BOOLEAN NOT NULL DEFAULT FALSE COMMENT 'Temporary flag to indicate whether the card is selected by its owner or not',
  `icon_hash` INT(32) UNSIGNED DEFAULT NULL COMMENT 'A column that is updated on game start with a calculated hash of the card icons. This is for icon comparisson purposes regardless of the icon position.',
  PRIMARY KEY(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/* Table used to manage the execution of nested effects */
CREATE TABLE IF NOT EXISTS `nested_card_execution` (
 `nesting_index` SMALLINT UNSIGNED NOT NULL COMMENT 'The index of the nesting (1 is for the original card, 2 is for the next card, etc.)',
 `card_id` SMALLINT COMMENT '-1 means no card',
 `executing_as_if_on_card_id` SMALLINT COMMENT '-1 means no card',
 `card_location` VARCHAR(12) DEFAULT NULL COMMENT 'The initial location of the card when its dogma was executed (board, display, or NULL)',
 `launcher_id` INT(10) NOT NULL COMMENT 'ID of the player who initially launched this card',
 `current_player_id` INT(10) DEFAULT NULL COMMENT 'ID of the player currently executing the card',
 `current_effect_type` TINYINT COMMENT '-1=unset, 0=demand, 1=non-demand, 2=compel, 3=echo',
 `current_effect_number` TINYINT COMMENT '-1 (unset), 1, 2, or 3 (but can be higher for echo effects)',
 `step` TINYINT COMMENT 'The interaction that the card is on',
 `step_max` TINYINT COMMENT 'The anticipated number of interactions that the card will have',
 `post_execution_index` TINYINT DEFAULT 0 COMMENT '0 means the effect has not triggered another card, 1 means the effect already triggered another card and resumed executing this effect',
 `auxiliary_value` INT DEFAULT -1 COMMENT 'An auxiliary value used by certain card implementations',
 `auxiliary_value_2` INT DEFAULT -1 COMMENT 'A second auxiliary value used by certain card implementations',
  PRIMARY KEY(`nesting_index`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/* Table used to manage the execution of echo effects */
CREATE TABLE IF NOT EXISTS `echo_execution` (
 `execution_index` SMALLINT UNSIGNED NOT NULL COMMENT 'The index of when the echo effect should be executed (an index of 1 is used for the final card to be executed, and higher indicies are used for cards buried deeper)',
 `card_id` SMALLINT COMMENT 'the ID of the card',
  PRIMARY KEY(`execution_index`)
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

/* TODO(LATER): See if there's any columns that we can remove from this table. */
CREATE TABLE `card_with_top_card_indication` AS
	SELECT
		`id`,
    `type`,
    `age`,
    `color`,
    `spot_1`,
    `spot_2`,
    `spot_3`,
    `spot_4`,
    `spot_5`,
    `spot_6`,
    `dogma_icon`,
    `owner`,
    `location`,
    `position`,
    `splay_direction`,
    `selected`
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
(109, 0, 'achievements', 14),

/* Cities special achievements */
(325, 2, 'removed', 15),
(326, 2, 'removed', 16),
(327, 2, 'removed', 17),
(328, 2, 'removed', 18),
(329, 2, 'removed', 19),

/* Echoes special achievements */
(435, 3, 'removed', 20),
(436, 3, 'removed', 21),
(437, 3, 'removed', 22),
(438, 3, 'removed', 23),
(439, 3, 'removed', 24);

/* Insert relic cards */

INSERT INTO `card` (`id`, `type`, `age`, `color`, `spot_1`, `spot_2`, `spot_3`, `spot_4`, `spot_5`, `spot_6`, `dogma_icon`, `is_relic`, `location`) VALUES

(215, 2, 3, 2, 3, 1, 1, 1,    0,    7, 1, TRUE, 'removed'),
(216, 0, 4, 0, 5, 0, 3, 3, NULL, NULL, 3, TRUE, 'removed'),
(217, 1, 5, 4, 5, 3, 5, 0, NULL, NULL, 5, TRUE, 'removed'),
(218, 4, 6, 1, 6, 6, 0, 2, NULL, NULL, 6, TRUE, 'removed'),
/* TODO(ECHOES): Add Echo effect to this card */
(219, 3, 7, 3, 0, 0, 2, 2, NULL, NULL, 2, TRUE, 'removed');

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
(104, 0, 10, 4, 0, 6, 6, 3, 6);

INSERT INTO `card` (`id`, `type`, `age`, `color`, `spot_1`, `spot_2`, `spot_3`, `spot_4`, `dogma_icon`, `location`) VALUES

/* Artifacts - Age 1 */
(110, 1, 1, 0, 1, 4, 0, 4, 4, 'removed'),
(111, 1, 1, 0, 4, 4, 4, 0, 4, 'removed'),
(112, 1, 1, 0, 1, 0, 2, 2, 2, 'removed'),
(113, 1, 1, 1, 4, 2, 0, 2, 2, 'removed'),
(114, 1, 1, 1, 2, 2, 2, 0, 2, 'removed'),
(115, 1, 1, 1, 0, 1, 4, 1, 1, 'removed'),
(116, 1, 1, 2, 4, 0, 4, 4, 4, 'removed'),
(117, 1, 1, 2, 1, 0, 4, 1, 1, 'removed'),
(118, 1, 1, 2, 0, 1, 4, 4, 4, 'removed'),
(119, 1, 1, 3, 0, 3, 3, 3, 3, 'removed'),
(120, 1, 1, 3, 0, 1, 1, 1, 1, 'removed'),
(121, 1, 1, 3, 0, 2, 2, 2, 2, 'removed'),
(122, 1, 1, 4, 4, 4, 0, 4, 4, 'removed'),
(123, 1, 1, 4, 0, 4, 4, 4, 4, 'removed'),
(124, 1, 1, 4, 0, 4, 3, 3, 3, 'removed'),

/* Artifacts - Age 2 */
(125, 1, 2, 0, 2, 2, 2, 0, 2, 'removed'),
(126, 1, 2, 0, 4, 4, 4, 0, 4, 'removed'),
(127, 1, 2, 1, 1, 0, 3, 3, 3, 'removed'),
(128, 1, 2, 1, 0, 4, 4, 4, 4, 'removed'),
(129, 1, 2, 2, 4, 2, 0, 4, 4, 'removed'),
(130, 1, 2, 2, 1, 3, 0, 1, 1, 'removed'),
(131, 1, 2, 3, 2, 0, 1, 2, 2, 'removed'),
(132, 1, 2, 3, 1, 1, 1, 0, 1, 'removed'),
(133, 1, 2, 4, 0, 4, 3, 4, 4, 'removed'),
(134, 1, 2, 4, 0, 3, 3, 4, 3, 'removed'),

/* Artifacts - Age 3 */
(135, 1, 3, 0, 3, 3, 0, 3, 3, 'removed'),
(136, 1, 3, 0, 3, 3, 0, 4, 3, 'removed'),
(137, 1, 3, 1, 1, 0, 4, 4, 4, 'removed'),
(138, 1, 3, 1, 0, 4, 4, 3, 4, 'removed'),
(139, 1, 3, 2, 1, 1, 1, 0, 1, 'removed'),
(140, 1, 3, 2, 1, 1, 0, 1, 1, 'removed'),
(141, 1, 3, 3, 4, 2, 0, 4, 4, 'removed'),
(142, 1, 3, 3, 1, 1, 1, 0, 1, 'removed'),
(143, 1, 3, 4, 0, 2, 2, 2, 2, 'removed'),
(144, 1, 3, 4, 2, 0, 2, 2, 2, 'removed'),

/* Artifacts - Age 4 */
(145, 1, 4, 0, 3, 0, 1, 3, 3, 'removed'),
(146, 1, 4, 0, 5, 0, 3, 3, 3, 'removed'),
(147, 1, 4, 1, 1, 5, 5, 0, 5, 'removed'),
(148, 1, 4, 1, 5, 5, 1, 0, 5, 'removed'),
(149, 1, 4, 2, 3, 3, 3, 0, 3, 'removed'),
(150, 1, 4, 2, 3, 3, 0, 3, 3, 'removed'),
(151, 1, 4, 3, 2, 2, 0, 2, 2, 'removed'),
(152, 1, 4, 3, 0, 1, 2, 2, 2, 'removed'),
(153, 1, 4, 4, 1, 1, 0, 1, 1, 'removed'),
(154, 1, 4, 4, 2, 0, 1, 2, 2, 'removed'),

/* Artifacts - Age 5 */
(155, 1, 5, 0, 3, 5, 3, 0, 3, 'removed'),
(156, 1, 5, 0, 3, 3, 0, 3, 3, 'removed'),
(157, 1, 5, 1, 0, 2, 3, 3, 3, 'removed'),
(158, 1, 5, 1, 5, 5, 0, 5, 5, 'removed'),
(159, 1, 5, 2, 5, 0, 5, 1, 5, 'removed'),
(160, 1, 5, 2, 1, 0, 5, 1, 1, 'removed'),
(161, 1, 5, 3, 3, 1, 0, 3, 3, 'removed'),
(162, 1, 5, 3, 5, 5, 1, 0, 5, 'removed'),
(163, 1, 5, 4, 2, 2, 5, 0, 2, 'removed'),
(164, 1, 5, 4, 1, 0, 1, 1, 1, 'removed'),

/* Artifacts - Age 6 */
(165, 1, 6, 0, 5, 0, 5, 3, 5, 'removed'),
(166, 1, 6, 0, 5, 0, 5, 5, 5, 'removed'),
(167, 1, 6, 1, 0, 5, 5, 5, 5, 'removed'),
(168, 1, 6, 1, 1, 1, 3, 0, 1, 'removed'),
(169, 1, 6, 2, 1, 5, 0, 1, 1, 'removed'),
(170, 1, 6, 2, 5, 1, 5, 0, 5, 'removed'),
(171, 1, 6, 3, 0, 1, 3, 3, 3, 'removed'),
(172, 1, 6, 3, 0, 3, 2, 3, 3, 'removed'),
(173, 1, 6, 4, 3, 3, 0, 3, 3, 'removed'),
(174, 1, 6, 4, 2, 2, 0, 1, 2, 'removed'),

/* Artifacts - Age 7 */
(175, 1, 7, 0, 3, 0, 3, 3, 3, 'removed'),
(176, 1, 7, 0, 2, 3, 0, 2, 2, 'removed'),
(177, 1, 7, 1, 5, 5, 5, 0, 5, 'removed'),
(178, 1, 7, 1, 0, 6, 3, 3, 3, 'removed'),
(179, 1, 7, 2, 1, 0, 1, 5, 1, 'removed'),
(180, 1, 7, 2, 6, 2, 6, 0, 6, 'removed'),
(181, 1, 7, 3, 5, 0, 5, 1, 5, 'removed'),
(182, 1, 7, 3, 0, 5, 6, 6, 6, 'removed'),
(183, 1, 7, 4, 2, 2, 2, 0, 2, 'removed'),
(184, 1, 7, 4, 1, 1, 0, 2, 1, 'removed'),

/* Artifacts - Age 8 */
(185, 1, 8, 0, 3, 3, 3, 0, 3, 'removed'),
(186, 1, 8, 0, 6, 0, 6, 6, 6, 'removed'),
(187, 1, 8, 1, 0, 5, 5, 5, 5, 'removed'),
(188, 1, 8, 1, NULL, 0, NULL, NULL, NULL, 'removed'),
(189, 1, 8, 2, 1, 5, 1, 0, 1, 'removed'),
(190, 1, 8, 2, 2, 1, 0, 1, 1, 'removed'),
(191, 1, 8, 3, 2, 2, 2, 0, 2, 'removed'),
(192, 1, 8, 3, 0, 6, 6, 3, 6, 'removed'),
(193, 1, 8, 4, 0, 6, 6, 6, 6, 'removed'),
(194, 1, 8, 4, 2, 2, 6, 0, 2, 'removed'),

/* Artifacts - Age 9 */
(195, 1, 9, 0, 6, 6, 5, 0, 6, 'removed'),
(196, 1, 9, 0, 5, 0, 5, 5, 5, 'removed'),
(197, 1, 9, 1, 0, 2, 6, 2, 2, 'removed'),
(198, 1, 9, 1, 5, 5, 6, 0, 5, 'removed'),
(199, 1, 9, 2, 0, 2, 2, 2, 2, 'removed'),
(200, 1, 9, 2, 0, 6, 6, 6, 6, 'removed'),
(201, 1, 9, 3, 2, 6, 0, 6, 6, 'removed'),
(202, 1, 9, 3, 3, 2, 0, 3, 3, 'removed'),
(203, 1, 9, 4, 3, 0, 3, 3, 3, 'removed'),
(204, 1, 9, 4, 1, 1, 1, 0, 1, 'removed'),

/* Artifacts - Age 10 */
(205, 1,10, 0, 0, 3, 3, 3, 3, 'removed'),
(206, 1,10, 0, 3, 3, 3, 0, 3, 'removed'),
(207, 1,10, 1, 5, 0, 5, 5, 5, 'removed'),
(208, 1,10, 1, 6, 0, 6, 6, 6, 'removed'),
(209, 1,10, 2, 1, 1, 0, 1, 1, 'removed'),
(210, 1,10, 2, 6, 6, 6, 0, 6, 'removed'),
(211, 1,10, 3, 0, 6, 2, 6, 6, 'removed'),
(212, 1,10, 3, 2, 6, 0, 2, 2, 'removed'),
(213, 1,10, 4, 0, 6, 6, 6, 6, 'removed'),
(214, 1,10, 4, 0, 5, 5, 6, 5, 'removed'),

/* Echoes - Age 1 */
(330, 3, 1, 0,  0,101,  1,  1, 1, 'removed'),
(331, 3, 1, 0, 10,  4,101,  0, 4, 'removed'),
(332, 3, 1, 0,  3,  0,  3, 10, 3, 'removed'),
(333, 3, 1, 1,  0,  4, 10,101, 4, 'removed'),
(334, 3, 1, 1, 10,101,  0,  3, 3, 'removed'),
(335, 3, 1, 1, 10,102,  0,  4, 4, 'removed'),
(336, 3, 1, 2,  4,  4,  2,  0, 4, 'removed'),
(337, 3, 1, 2,  4,  1,  0,  1, 1, 'removed'),
(338, 3, 1, 2,  2,  2,  0, 10, 2, 'removed'),
(339, 3, 1, 3,  0,  2,  2, 10, 2, 'removed'),
(340, 3, 1, 3,  4,  0,  4,101, 4, 'removed'),
(341, 3, 1, 3,  2,102,  0,  2, 2, 'removed'),
(342, 3, 1, 4,  4,  0,  4, 10, 4, 'removed'),
(343, 3, 1, 4,101,  0,  1, 10, 1, 'removed'),
(344, 3, 1, 4,  0,  4,103,  4, 4, 'removed'),

/* Echoes - Age 2 */
(346, 3, 2, 0,  3,  3, 10,  0, 3, 'removed'),
(347, 3, 2, 1,103,  0,  4,  4, 4, 'removed'),
(348, 3, 2, 1,  0,102, 10,  4, 4, 'removed'),
(349, 3, 2, 2,  0,  1,  1, 10, 1, 'removed'),
(351, 3, 2, 3,102, 10,  0,  2, 2, 'removed'),
(352, 3, 2, 3,  2,  2,  2,  0, 2, 'removed'),
(353, 3, 2, 4,  4,102,  0,  4, 4, 'removed'),
(354, 3, 2, 4,  1,103,  1,  0, 1, 'removed'),

/* Echoes - Age 3 */
(355, 3, 3, 0,  0,  2,104, 10, 2, 'removed'),
(356, 3, 3, 0,  3,  0,103, 10, 3, 'removed'),
(357, 3, 3, 1,  0,  3,  3,  3, 3, 'removed'),
(358, 3, 3, 1,  4,  4,  0,  4, 4, 'removed'),
(359, 3, 3, 2, 10,  0,  1,103, 1, 'removed'),
(360, 3, 3, 2,103,  2,  0,  2, 2, 'removed'),
(361, 3, 3, 3,  1, 10,  1,  0, 1, 'removed'),
(362, 3, 3, 3,  1,  1,  0,  2, 1, 'removed'),
(363, 3, 3, 4,  0,103,  1, 10, 1, 'removed'),
(364, 3, 3, 4,  0,103, 10,  4, 4, 'removed'),

/* Echoes - Age 4 */
(365, 3, 4, 0,  3,  3,  3,  0, 3, 'removed'),
(366, 3, 4, 0,  0,104,  3, 10, 3, 'removed'),
(367, 3, 4, 1,105,  5,  0, 10, 5, 'removed'),
(368, 3, 4, 1,  1,  1,  0,  1, 1, 'removed'),
(369, 3, 4, 2,  1,  0,  1,104, 1, 'removed'),
(370, 3, 4, 2,  5,104,  5,  0, 5, 'removed'),
(371, 3, 4, 3,  2, 10,  2,  0, 2, 'removed'),
(372, 3, 4, 3,  0, 10,  3,104, 3, 'removed'),
(373, 3, 4, 4, 10,105,  0,  3, 3, 'removed'),
(374, 3, 4, 4, 10,  2,  0,  2, 2, 'removed'),

/* Echoes - Age 5 */
(375, 3, 5, 0, 10,  5,  0,106, 5, 'removed'),
(376, 3, 5, 0,  0, 10,105,  3, 3, 'removed'),
(377, 3, 5, 1, 10,  5,  5,  0, 5, 'removed'),
(378, 3, 5, 1,  1,  1,  0,  1, 1, 'removed'),
(379, 3, 5, 2,  5,  0,  5,105, 5, 'removed'),
(380, 3, 5, 2,  3,  2,  2,  0, 2, 'removed'),
(381, 3, 5, 3,105,  0,  3,  3, 3, 'removed'),
(382, 3, 5, 3,  0,106,  5, 10, 5, 'removed'),
(383, 3, 5, 4,105, 10,  0,  3, 3, 'removed'),

/* Echoes - Age 6 */
(385, 3, 6, 0, 10,  0,  1,  1, 1, 'removed'),
(386, 3, 6, 0,  3, 10,  3,  0, 3, 'removed'),
(388, 3, 6, 1,  5,  5,  5,  0, 5, 'removed'),
(390, 3, 6, 2,  0,106,  1,  1, 1, 'removed'),
(391, 3, 6, 3, 10,  5,  5,  0, 5, 'removed'),
(393, 3, 6, 4,  0,  2,106,  2, 2, 'removed'),
(394, 3, 6, 4,106,  3,  0,  3, 3, 'removed'),

/* Echoes - Age 7 */
(395, 3, 7, 0, 10,  3,  0,107, 3, 'removed'),
(396, 3, 7, 0,  3,  0,  1,  1, 1, 'removed'),
(397, 3, 7, 1,  5,  5, 10,  0, 5, 'removed'),
(398, 3, 7, 1,  0, 10,  5,107, 5, 'removed'),
(399, 3, 7, 2, 10,  2,  0,108, 2, 'removed'),
(400, 3, 7, 2,  0,  6,  6,  3, 6, 'removed'),
(401, 3, 7, 3,107, 10,  6,  0, 6, 'removed'),
(402, 3, 7, 3,  2,  0,  5,  2, 2, 'removed'),
(403, 3, 7, 4,  0,108,  2, 10, 2, 'removed'),
(404, 3, 7, 4,107,  1,  0,  1, 1, 'removed'),

/* Echoes - Age 8 */
/* (405, 3, 8, 0,  0,  3,  3,  3, 3, 'removed'), */
(406, 3, 8, 0,  0,  2, 10,108, 2, 'removed'),
(407, 3, 8, 1,  2, 10,  0,  2, 2, 'removed'),
(408, 3, 8, 1,  5,  0,  6,  6, 6, 'removed'),
(409, 3, 8, 2,108,  5,  5,  0, 5, 'removed'),
(410, 3, 8, 2, 10,  0,109,  1, 1, 'removed'),
/* (411, 3, 8, 3,  0, 10,109,  2, 2, 'removed'), */
(412, 3, 8, 3, 10,  6,  6,  0, 6, 'removed'),
/*(413, 3, 8, 4,  1,108,  0,  1, 1, 'removed'), */
(414, 3, 8, 4,108,  0,  6, 10, 6, 'removed'),

/* Echoes - Age 9 */
/* (415, 3, 9, 0,  6,  0,  6,  3, 6, 'removed'), */
/* (416, 3, 9, 0,  3,  2,  3,  0, 3, 'removed'), */
/* (417, 3, 9, 1,  5,  5,  5,  0, 5, 'removed'), */
(418, 3, 9, 1,  0, 10,  6,110, 6, 'removed'),
(419, 3, 9, 2, 10,  6,  6,  0, 6, 'removed'),
(420, 3, 9, 2, 10,  1,109,  0, 1, 'removed'),
(421, 3, 9, 3,  1,  0, 10,109, 1, 'removed'),
(422, 3, 9, 3,  0,  5,110, 10, 5, 'removed'),
(423, 3, 9, 4,  0,  2,109, 10, 2, 'removed'),
(424, 3, 9, 4,  2,109,  0,  2, 2, 'removed'),

/* Echoes - Age 10 */
(434, 3,10, 4,  3,  0,  3,111, 3, 'removed');


/* Insert Cities cards */

INSERT INTO `card` (`id`, `type`, `age`, `color`, `spot_1`, `spot_2`, `spot_3`, `spot_4`, `spot_5`, `spot_6`, `dogma_icon`, `location`) VALUES

/* Cities - Age 1 */
(220, 2, 1, 0, 4, 2, 2, 4, 0, 4, 4, 'removed'),
(221, 2, 1, 0, 1, 1, 1, 4, 0, 4, 1, 'removed'),
(222, 2, 1, 0, 2, 2, 2, 2, 0, 4, 2, 'removed'),
(223, 2, 1, 1, 1, 1, 1, 3, 0, 3, 1, 'removed'),
(224, 2, 1, 1, 2, 2, 2, 1, 0, 7, 2, 'removed'),
(225, 2, 1, 1, 3, 3, 3, 2, 0, 7, 3, 'removed'),
(226, 2, 1, 2, 4, 3, 3, 4, 0, 4, 4, 'removed'),
(227, 2, 1, 2, 4, 2, 4, 4, 0, 4, 4, 'removed'),
(228, 2, 1, 2, 3, 3, 3, 1, 0, 7, 3, 'removed'),
(229, 2, 1, 3, 4, 1, 4, 4, 0, 4, 4, 'removed'),
(230, 2, 1, 3, 1, 1, 1, 1, 0, 4, 1, 'removed'),
(231, 2, 1, 3, 4, 1, 1, 4, 0, 4, 4, 'removed'),
(232, 2, 1, 4, 2, 2, 2, 4, 0, 4, 2, 'removed'),
(233, 2, 1, 4, 2, 2, 2, 4, 0, 7, 2, 'removed'),
(234, 2, 1, 4, 1, 1, 1, 4, 0, 7, 1, 'removed'),

/* Cities - Age 2 */
(235, 2, 2, 0,   1, 102,   1,   3, 0, 7, 1, 'removed'),
(236, 2, 2, 0,   1,   3,   1,   1, 0, 7, 1, 'removed'),
(237, 2, 2, 1,   4,   5,   5,   4, 0, 4, 4, 'removed'),
(238, 2, 2, 1,   4,   2, 104,   4, 0, 4, 4, 'removed'),
(239, 2, 2, 2, 103,   1,   4,   4, 0, 4, 4, 'removed'),
(240, 2, 2, 2,   1, 102,   1,   4, 0, 1, 1, 'removed'),
(241, 2, 2, 3,   2,   2, 103,   4, 0, 2, 2, 'removed'),
(242, 2, 2, 3,   2,   2,   1, 102, 0, 2, 2, 'removed'),
(243, 2, 2, 4,   3,   3,   2, 102, 0, 7, 3, 'removed'),
(244, 2, 2, 4, 102,   3,   3,   4, 0, 3, 3, 'removed'),

/* Cities - Age 3 */
(245, 2, 3, 0,   3,   3,   3,   4, 0,   7, 3, 'removed'),
(246, 2, 3, 0,   5,   5,   5,   3, 0,  11, 5, 'removed'),
(247, 2, 3, 1, 104,   4,   1,   4, 0,   7, 4, 'removed'),
(248, 2, 3, 1,   3,   2,   3,   3, 0,  11, 3, 'removed'),
(249, 2, 3, 2,   1,   1,   3, 103, 0,  11, 1, 'removed'),
(250, 2, 3, 2,   5,   1,   1,   1, 0,   7, 1, 'removed'),
(251, 2, 3, 3,   4,   2,   4,   4, 0,  11, 4, 'removed'),
(252, 2, 3, 3,   1,   1,   2,   2, 0,   2, 2, 'removed'),
(253, 2, 3, 4,   2, 105,   1,   2, 0,   2, 2, 'removed'),
(254, 2, 3, 4,   4,   1, 103,   4, 0,  11, 4, 'removed'),

/* Cities - Age 4 */
(255, 2, 4, 0,   3,   3,   7,   5, 0,   7, 3, 'removed'),
(256, 2, 4, 0,   3,   3,   3,   1, 0,  11, 3, 'removed'),
(257, 2, 4, 1,   4,   4, 104,   3, 0,  11, 4, 'removed'),
(258, 2, 4, 1,   5,   5,   7,   3, 0,   7, 5, 'removed'),
(259, 2, 4, 2,   1, 104,   1,   2, 0,  11, 4, 'removed'),
(260, 2, 4, 2, 105,   1,   2,   2, 0,   2, 2, 'removed'),
(261, 2, 4, 3,   4,   4,   4,   2, 0,  11, 4, 'removed'),
(262, 2, 4, 3,   5,   5,   7,   2, 0,   7, 5, 'removed'),
(263, 2, 4, 4,   1,   3,   3, 104, 0,  11, 3, 'removed'),
(264, 2, 4, 4,   1,   1,   1,   3, 0,   3, 1, 'removed'),

/* Cities - Age 5 */
(265, 2, 5, 0,   3,   3,   7,   2, 0,   7, 3, 'removed'),
(266, 2, 5, 0,   5,   3,   7,   3, 0,   7, 3, 'removed'),
(267, 2, 5, 1,   5,   5,   5,   3, 0,   8, 5, 'removed'),
(268, 2, 5, 1,   1,   3,   3, 105, 0,   8, 3, 'removed'),
(269, 2, 5, 2,   4,   2,   7,   4, 0,   7, 4, 'removed'),
(270, 2, 5, 2,   5,   1, 105,   1, 0,   8, 1, 'removed'),
(271, 2, 5, 3, 107,   1,   1,   2, 0,   1, 1, 'removed'),
(272, 2, 5, 3,   2,   2,   3,   3, 0,   3, 3, 'removed'),
(273, 2, 5, 4,   1, 105,   5,   1, 0,   8, 1, 'removed'),
(274, 2, 5, 4,   4,   4,   6,   4, 0,   8, 4, 'removed'),

/* Cities - Age 6 */
(275, 2, 6, 0,   5,   5,  12,   3, 0,   8, 5, 'removed'),
(276, 2, 6, 0,   3,   5,   7,   3, 0,   7, 3, 'removed'),
(277, 2, 6, 1,   5,   6,   7,   6, 0,   7, 6, 'removed'),
(278, 2, 6, 1,   5,   1,  12,   1, 0,   8, 1, 'removed'),
(279, 2, 6, 2, 106,   1,   2,   2, 0,  12, 2, 'removed'),
(280, 2, 6, 2,   5, 107,   5,   1, 0,   8, 5, 'removed'),
(281, 2, 6, 3,   2,   2,   6, 107, 0,   8, 2, 'removed'),
(282, 2, 6, 3,   5,   2,  12,   5, 0,   8, 5, 'removed'),
(283, 2, 6, 4,   3,   3, 106,   1, 0,  12, 3, 'removed'),
(284, 2, 6, 4,   3,   1,   7,   1, 0,   7, 1, 'removed'),

/* Cities - Age 7 */
(285, 2, 7, 0,   3,   6,   7,   3, 0,  12, 3, 'removed'),
(286, 2, 7, 0, 107, 107,   3,   1, 0,   1, 1, 'removed'),
(287, 2, 7, 1,   1,   5,  12,   5, 0,   8, 5, 'removed'),
(288, 2, 7, 1,   5,   5,   7,   1, 0,   7, 5, 'removed'),
(289, 2, 7, 2,   5,   6,   7,   6, 0,  12, 6, 'removed'),
(290, 2, 7, 2, 107, 107,   6,   2, 0,   6, 6, 'removed'),
(291, 2, 7, 3,   2,   5,  12,   2, 0,   8, 2, 'removed'),
(292, 2, 7, 3, 107, 107,   2,   1, 0,   1, 1, 'removed'),
(293, 2, 7, 4,   5,   3,  12,   3, 0,   8, 3, 'removed'),
(294, 2, 7, 4,   1,   3, 109,   1, 0, 109, 1, 'removed'),

/* Cities - Age 8 */
(295, 2, 8, 0,   5,   5,   7,   3, 0,   7, 5, 'removed'),
(296, 2, 8, 0,   5,   6,   7,   5, 0,   7, 1, 'removed'),
(297, 2, 8, 1,   5,   5,   6,   5, 0,   9, 5, 'removed'),
(298, 2, 8, 1, 109,   5, 109,   3, 0,   5, 5, 'removed'),
(299, 2, 8, 2,   1,   6,   6,   6, 0,   9, 6, 'removed'),
(300, 2, 8, 2, 108, 108,   1,   2, 0,   2, 2, 'removed'),
(301, 2, 8, 3, 108, 108,   6,   2, 0,   2, 2, 'removed'),
(302, 2, 8, 3,   2,   1,   2,   2, 0,   9, 2, 'removed'),
(303, 2, 8, 4, 109,   6, 109,   3, 0,   6, 6, 'removed'),
(304, 2, 8, 4,   1,   3,   7,   3, 0,   7, 3, 'removed'),

/* Cities - Age 9 */
(305, 2, 9, 0, 110,   6, 110,   1, 0,   6, 6, 'removed'),
(306, 2, 9, 0,   6,   6,  13,   3, 0,   9, 6, 'removed'),
(307, 2, 9, 1,   5,   6,   7,   6, 0,  13, 6, 'removed'),
(308, 2, 9, 1, 110,   5, 110,   1, 0,   5, 5, 'removed'),
(309, 2, 9, 2, 109, 109,   6,   3, 0,   3, 5, 'removed'),
(310, 2, 9, 2,   3,   1,  13,   1, 0,   9, 1, 'removed'),
(311, 2, 9, 3,   2,   6,   7,   6, 0,  13, 6, 'removed'),
(312, 2, 9, 3, 109,   1, 109,   2, 0,   2, 2, 'removed'),
(313, 2, 9, 4,   5,   6,   7,   5, 0,  13, 5, 'removed'),
(314, 2, 9, 4,   2,   3,   7,   2, 0,   9, 2, 'removed'),

/* Cities - Age 10 */
(315, 2,10, 0,   2,   2,  13,   3, 0,   9, 2, 'removed'),
(316, 2,10, 0, 110, 110,   6,   3, 0,   6, 6, 'removed'),
(317, 2,10, 1, 110, 110,   5,   1, 0,   5, 5, 'removed'),
(318, 2,10, 1,   2,   6,  13,   6, 0,   9, 6, 'removed'),
(319, 2,10, 2,   3,   6,  13,   6, 0,   9, 6, 'removed'),
(320, 2,10, 2,   6,   1, 111,   1, 0, 111, 1, 'removed'),
(321, 2,10, 3,   2,   6,  13,   2, 0,   9, 2, 'removed'),
(322, 2,10, 3, 110, 110,   6,   1, 0,   1, 1, 'removed'),
(323, 2,10, 4,   5,   6,   9,   5, 0,   9, 5, 'removed'),
(324, 2,10, 4,   2,   3,  13,   3, 0,   9, 3, 'removed');

