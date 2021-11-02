
-- ------
-- BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
-- Innovation implementation : © Jean Portemer <jportemer@gmail.com>
-- 
-- This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
-- See http://en.boardgamearena.com/#!doc/Studio for more information.
-- -----

-- dbmodel.sql

-- This is the file where you are describing the database schema of your game
-- Basically, you just have to export from PhpMyAdmin your table structure and copy/paste
-- this export here.
-- Note that the database itself and the standard tables ("global", "stats", "gamelog" and "player") are
-- already created and must not be created here

-- Note: The database schema is created from this file when the game starts. If you modify this file,
--       you have to restart a game to see your changes in database.

-- Example 1: create a standard "card" table to be used with the "Deck" tools (see example game "hearts"):

-- CREATE TABLE IF NOT EXISTS `card` (
--   `card_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
--   `card_type` varchar(16) NOT NULL,
--   `card_type_arg` int(11) NOT NULL,
--   `card_location` varchar(16) NOT NULL,
--   `card_location_arg` int(11) NOT NULL,
--   PRIMARY KEY (`card_id`)
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


-- Example 2: add a custom field to the standard "player" table
-- ALTER TABLE `player` ADD `player_my_custom_field` INT UNSIGNED NOT NULL DEFAULT '0';

/* New columns for table player */
ALTER TABLE `player` ADD `player_team` SMALLINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Team number (same for players in a same team)';
ALTER TABLE `player` ADD `player_innovation_score` SMALLINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Innovation score (player_score is used for achievements in normal play)';
ALTER TABLE `player` ADD `player_icon_count_1` SMALLINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Total of crowns on player board';
ALTER TABLE `player` ADD `player_icon_count_2` SMALLINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Total of leaves on player board';
ALTER TABLE `player` ADD `player_icon_count_3` SMALLINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Total of bulbs on player board';
ALTER TABLE `player` ADD `player_icon_count_4` SMALLINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Total of towers on player board';
ALTER TABLE `player` ADD `player_icon_count_5` SMALLINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Total of factories on player board';
ALTER TABLE `player` ADD `player_icon_count_6` SMALLINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Total of clocks on player board';
ALTER TABLE `player` ADD `stronger_or_equal` BOOLEAN DEFAULT NULL COMMENT 'When in dogma state, TRUE if the player can share the non-demand effects, FALSE if the player has to execute "I demand" effects';
ALTER TABLE `player` ADD `player_no_under_effect` TINYINT UNSIGNED DEFAULT NULL COMMENT 'Order of the player when he is concerned by an effect';
ALTER TABLE `player` ADD `number_of_tucked_cards` TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Number of cards the player has tucked during the turn of the current player';
ALTER TABLE `player` ADD `number_of_scored_cards` TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Number of cards the player has scored during the turn of the current player';
ALTER TABLE `player` ADD `pile_display_mode` BOOLEAN DEFAULT TRUE COMMENT 'Wish for the player for pile display, TRUE for expanded, FALSE for compact';
ALTER TABLE `player` ADD `pile_view_full` BOOLEAN DEFAULT FALSE COMMENT 'Wish for the player to view all cards in pile, TRUE if yes, FALSE if no';
ALTER TABLE `player` ADD `effects_had_impact` BOOLEAN DEFAULT FALSE COMMENT 'Indicate if the player has changed the situation (TRUE) or not (FALSE) in the game when it was his turn to play within a dogma effect';

/* Main table to store all the cards of the game and their characteristics. See the material file to see the textual info */
CREATE TABLE IF NOT EXISTS `card` (
  `id` SMALLINT UNSIGNED NOT NULL COMMENT '0 to 104 for normal cards, 105 to 109 for special achievements',
  `type` TINYINT UNSIGNED NOT NULL COMMENT '0 for base, 1 for artifacts',
  `age` TINYINT UNSIGNED COMMENT '1 to 10, NULL for a special achievement',
  `color` TINYINT UNSIGNED COMMENT '0 (blue), 1 (red), 2 (green), 3 (yellow), 4(purple) or NULL for a special achievement',
  `spot_1` TINYINT UNSIGNED COMMENT 'Icon on top-left, 0 (nothing), 1 (crown), 2 (leaf), 3 (bulb), 4 (tower), 5 (factory), 6 (clock) or NULL for a special achievement',
  `spot_2` TINYINT UNSIGNED COMMENT 'Icon on bottom-left, 0 (nothing), 1 (crown), 2 (leaf), 3 (bulb), 4 (tower), 5 (factory), 6 (clock) or NULL for a special achievement',
  `spot_3` TINYINT UNSIGNED COMMENT 'Icon on bottom-middle, 0 (nothing), 1 (crown), 2 (leaf), 3 (bulb), 4 (tower), 5 (factory), 6 (clock) or NULL for a special achievement',
  `spot_4` TINYINT UNSIGNED COMMENT 'Icon on bottom-right, 0 (nothing), 1 (crown), 2 (leaf), 3 (bulb), 4 (tower), 5 (factory), 6 (clock) or NULL for a special achievement',
  `dogma_icon` TINYINT UNSIGNED COMMENT 'Feature icon for dogma, 1 (crown), 2 (leaf), 3 (bulb), 4 (tower), 5 (factory), 6 (clock) or NULL for a special achievement',
  `owner` INT(10) UNSIGNED NOT NULL COMMENT 'Id of the player who owns the card or 0 if no owner',
  `location` VARCHAR(12) NOT NULL COMMENT 'Hand, board, score, achievements, deck or revealed (achievements can be used both with owner = 0 (available achievement) or with a player as owner (the player has earned that achievement))',
  `position` TINYINT UNSIGNED COMMENT 'Position in the given location. Bottom is zero (last card in deck), top is max. For hands, the cards are sorted by age before being sorted by position. For boards, the positions reflect the order in the color piles, 0 for the bottom card, maximum for active card.',
  `splay_direction` TINYINT UNSIGNED COMMENT 'Direction of the splay, 0 (no-splay), 1 (left), 2 (right), 3 (up) OR NULL if this card is not on board',
  `selected` BOOLEAN NOT NULL COMMENT 'Temporary flag to indicate whether the card is selected by its owner or not',
  PRIMARY KEY(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/* Auxiliary tables: these are only used when needed to update card or player and their content is deleted after that */
CREATE TABLE IF NOT EXISTS `random` (
 `id` SMALLINT UNSIGNED NOT NULL,
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

/* Creation of all cards of the game */
INSERT INTO `card` (`id`, `type`, `age`, `color`, `spot_1`, `spot_2`, `spot_3`, `spot_4`, `dogma_icon`, `owner`, `location`, `position`, `splay_direction`, `selected`) VALUES
/* Age 1 */
(0, 0, 1, 0, 0, 2, 2, 2, 2, 0, 'deck', 0, NULL, FALSE),
(1, 0, 1, 0, 0, 3, 3, 4, 3, 0, 'deck', 1, NULL, FALSE),
(2, 0, 1, 0, 0, 3, 3, 1, 3, 0, 'deck', 2, NULL, FALSE),
(3, 0, 1, 1, 4, 3, 0, 4, 4, 0, 'deck', 3, NULL, FALSE),
(4, 0, 1, 1, 4, 4, 0, 4, 4, 0, 'deck', 4, NULL, FALSE),
(5, 0, 1, 1, 4, 1, 0, 4, 4, 0, 'deck', 5, NULL, FALSE),
(6, 0, 1, 2, 0, 1, 2, 2, 2, 0, 'deck', 6, NULL, FALSE),
(7, 0, 1, 2, 1, 1, 0, 2, 1, 0, 'deck', 7, NULL, FALSE),
(8, 0, 1, 2, 0, 4, 4, 4, 4, 0, 'deck', 8, NULL, FALSE),
(9, 0, 1, 3, 0, 2, 2, 2, 2, 0, 'deck', 9, NULL, FALSE),
(10, 0, 1, 3, 4, 1, 0, 4, 4, 0, 'deck', 10, NULL, FALSE),
(11, 0, 1, 3, 4, 0, 4, 4, 4, 0, 'deck', 11, NULL, FALSE),
(12, 0, 1, 4, 0, 1, 1, 4, 1, 0, 'deck', 12, NULL, FALSE),
(13, 0, 1, 4, 0, 1, 1, 2, 1, 0, 'deck', 13, NULL, FALSE),
(14, 0, 1, 4, 0, 4, 4, 4, 4, 0, 'deck', 14, NULL, FALSE),

/* Age 2 */
(15, 0, 2, 0, 0, 2, 2, 3, 2, 0, 'deck', 0, NULL, FALSE),
(16, 0, 2, 0, 0, 3, 1, 3, 3, 0, 'deck', 1, NULL, FALSE),
(17, 0, 2, 1, 4, 0, 4, 4, 4, 0, 'deck', 2, NULL, FALSE),
(18, 0, 2, 1, 4, 4, 0, 4, 4, 0, 'deck', 3, NULL, FALSE),
(19, 0, 2, 2, 2, 1, 0, 1, 1, 0, 'deck', 4, NULL, FALSE),
(20, 0, 2, 2, 0, 1, 1, 4, 1, 0, 'deck', 5, NULL, FALSE),
(21, 0, 2, 3, 0, 1, 2, 1, 1, 0, 'deck', 6, NULL, FALSE),
(22, 0, 2, 3, 2, 2, 0, 4, 2, 0, 'deck', 7, NULL, FALSE),
(23, 0, 2, 4, 0, 4, 4, 4, 4, 0, 'deck', 8, NULL, FALSE),
(24, 0, 2, 4, 0, 3, 3, 3, 3, 0, 'deck', 9, NULL, FALSE),

/* Age 3 */
(25, 0, 3, 0, 0, 2, 4, 4, 4, 0, 'deck', 0, NULL, FALSE),
(26, 0, 3, 0, 0, 1, 1, 1, 1, 0, 'deck', 1, NULL, FALSE),
(27, 0, 3, 1, 4, 0, 3, 4, 4, 0, 'deck', 2, NULL, FALSE),
(28, 0, 3, 1, 1, 1, 1, 0, 1, 0, 'deck', 3, NULL, FALSE),
(29, 0, 3, 2, 0, 1, 1, 2, 1, 0, 'deck', 4, NULL, FALSE),
(30, 0, 3, 2, 0, 3, 3, 1, 3, 0, 'deck', 5, NULL, FALSE),
(31, 0, 3, 3, 2, 2, 0, 4, 2, 0, 'deck', 6, NULL, FALSE),
(32, 0, 3, 3, 1, 2, 2, 0, 2, 0, 'deck', 7, NULL, FALSE),
(33, 0, 3, 4, 3, 3, 3, 0, 3, 0, 'deck', 8, NULL, FALSE),
(34, 0, 3, 4, 0, 4, 2, 4, 4, 0, 'deck', 9, NULL, FALSE),

/* Age 4 */
(35, 0, 4, 0, 0, 3, 3, 3, 3, 0, 'deck', 0, NULL, FALSE),
(36, 0, 4, 0, 0, 3, 3, 1, 3, 0, 'deck', 1, NULL, FALSE),
(37, 0, 4, 1, 0, 5, 3, 5, 5, 0, 'deck', 2, NULL, FALSE),
(38, 0, 4, 1, 0, 5, 1, 5, 5, 0, 'deck', 3, NULL, FALSE),
(39, 0, 4, 2, 0, 3, 3, 5, 3, 0, 'deck', 4, NULL, FALSE),
(40, 0, 4, 2, 0, 1, 1, 1, 1, 0, 'deck', 5, NULL, FALSE),
(41, 0, 4, 3, 2, 2, 2, 0, 2, 0, 'deck', 6, NULL, FALSE),
(42, 0, 4, 3, 0, 3, 3, 2, 3, 0, 'deck', 7, NULL, FALSE),
(43, 0, 4, 4, 0, 1, 1, 1, 1, 0, 'deck', 8, NULL, FALSE),
(44, 0, 4, 4, 2, 2, 0, 2, 2, 0, 'deck', 9, NULL, FALSE),

/* Age 5 */
(45, 0, 5, 0, 5, 3, 5, 0, 5, 0, 'deck', 0, NULL, FALSE),
(46, 0, 5, 0, 5, 3, 3, 0, 3, 0, 'deck', 1, NULL, FALSE),
(47, 0, 5, 1, 5, 5, 5, 0, 5, 0, 'deck', 2, NULL, FALSE),
(48, 0, 5, 1, 1, 5, 1, 0, 1, 0, 'deck', 3, NULL, FALSE),
(49, 0, 5, 2, 5, 1, 0, 1, 1, 0, 'deck', 4, NULL, FALSE),
(50, 0, 5, 2, 3, 2, 3, 0, 3, 0, 'deck', 5, NULL, FALSE),
(51, 0, 5, 3, 2, 3, 2, 0, 2, 0, 'deck', 6, NULL, FALSE),
(52, 0, 5, 3, 0, 5, 1, 5, 5, 0, 'deck', 7, NULL, FALSE),
(53, 0, 5, 4, 1, 3, 3, 0, 3, 0, 'deck', 8, NULL, FALSE),
(54, 0, 5, 4, 1, 0, 3, 1, 1, 0, 'deck', 9, NULL, FALSE),

/* Age 6 */
(55, 0, 6, 0, 3, 3, 3, 0, 3, 0, 'deck', 0, NULL, FALSE),
(56, 0, 6, 0, 0, 1, 1, 1, 1, 0, 'deck', 1, NULL, FALSE),
(57, 0, 6, 1, 1, 5, 5, 0, 5, 0, 'deck', 2, NULL, FALSE),
(58, 0, 6, 1, 5, 5, 0, 5, 5, 0, 'deck', 3, NULL, FALSE),
(59, 0, 6, 2, 3, 3, 3, 0, 3, 0, 'deck', 4, NULL, FALSE),
(60, 0, 6, 2, 0, 5, 1, 1, 1, 0, 'deck', 5, NULL, FALSE),
(61, 0, 6, 3, 0, 5, 2, 5, 5, 0, 'deck', 6, NULL, FALSE),
(62, 0, 6, 3, 2, 5, 2, 0, 2, 0, 'deck', 7, NULL, FALSE),
(63, 0, 6, 4, 1, 3, 3, 0, 3, 0, 'deck', 8, NULL, FALSE),
(64, 0, 6, 4, 5, 3, 5, 0, 5, 0, 'deck', 9, NULL, FALSE),

/* Age 7 */
(65, 0, 7, 0, 3, 3, 3, 0, 3, 0, 'deck', 0, NULL, FALSE),
(66, 0, 7, 0, 0, 3, 6, 3, 3, 0, 'deck', 1, NULL, FALSE),
(67, 0, 7, 1, 1, 1, 5, 0, 1, 0, 'deck', 2, NULL, FALSE),
(68, 0, 7, 1, 0, 5, 5, 5, 5, 0, 'deck', 3, NULL, FALSE),
(69, 0, 7, 2, 1, 1, 6, 0, 1, 0, 'deck', 4, NULL, FALSE),
(70, 0, 7, 2, 3, 5, 0, 5, 5, 0, 'deck', 5, NULL, FALSE),
(71, 0, 7, 3, 0, 2, 2, 1, 2, 0, 'deck', 6, NULL, FALSE),
(72, 0, 7, 3, 2, 2, 0, 2, 2, 0, 'deck', 7, NULL, FALSE),
(73, 0, 7, 4, 0, 2, 6, 2, 2, 0, 'deck', 8, NULL, FALSE),
(74, 0, 7, 4, 6, 5, 6, 0, 6, 0, 'deck', 9, NULL, FALSE),

/* Age 8 */
(75, 0, 8, 0, 6, 6, 6, 0, 6, 0, 'deck', 0, NULL, FALSE),
(76, 0, 8, 0, 6, 6, 6, 0, 6, 0, 'deck', 1, NULL, FALSE),
(77, 0, 8, 1, 1, 0, 6, 1, 1, 0, 'deck', 2, NULL, FALSE),
(78, 0, 8, 1, 0, 5, 6, 5, 5, 0, 'deck', 3, NULL, FALSE),
(79, 0, 8, 2, 0, 5, 5, 1, 5, 0, 'deck', 4, NULL, FALSE),
(80, 0, 8, 2, 3, 0, 6, 3, 3, 0, 'deck', 5, NULL, FALSE),
(81, 0, 8, 3, 2, 2, 2, 0, 2, 0, 'deck', 6, NULL, FALSE),
(82, 0, 8, 3, 0, 5, 1, 1, 1, 0, 'deck', 7, NULL, FALSE),
(83, 0, 8, 4, 3, 3, 3, 0, 3, 0, 'deck', 8, NULL, FALSE),
(84, 0, 8, 4, 2, 0, 2, 2, 2, 0, 'deck', 9, NULL, FALSE),

/* Age 9 */
(85, 0, 9, 0, 6, 0, 6, 5, 6, 0, 'deck', 0, NULL, FALSE),
(86, 0, 9, 0, 3, 3, 3, 0, 3, 0, 'deck', 1, NULL, FALSE),
(87, 0, 9, 1, 5, 5, 0, 5, 5, 0, 'deck', 2, NULL, FALSE),
(88, 0, 9, 1, 0, 6, 6, 6, 6, 0, 'deck', 3, NULL, FALSE),
(89, 0, 9, 2, 0, 1, 6, 1, 1, 0, 'deck', 4, NULL, FALSE),
(90, 0, 9, 2, 0, 6, 6, 6, 6, 0, 'deck', 5, NULL, FALSE),
(91, 0, 9, 3, 2, 3, 3, 0, 3, 0, 'deck', 6, NULL, FALSE),
(92, 0, 9, 3, 0, 1, 2, 2, 2, 0, 'deck', 7, NULL, FALSE),
(93, 0, 9, 4, 0, 2, 2, 2, 2, 0, 'deck', 8, NULL, FALSE),
(94, 0, 9, 4, 0, 5, 2, 5, 5, 0, 'deck', 9, NULL, FALSE),

/* Age 10 */
(95, 0, 10, 0, 3, 6, 6, 0, 6, 0, 'deck', 0, NULL, FALSE),
(96, 0, 10, 0, 6, 6, 6, 0, 6, 0, 'deck', 1, NULL, FALSE),
(97, 0, 10, 1, 0, 3, 6, 3, 3, 0, 'deck', 2, NULL, FALSE),
(98, 0, 10, 1, 0, 5, 6, 5, 5, 0, 'deck', 3, NULL, FALSE),
(99, 0, 10, 2, 0, 6, 6, 6, 6, 0, 'deck', 4, NULL, FALSE),
(100, 0, 10, 2, 0, 1, 1, 1, 1, 0, 'deck', 5, NULL, FALSE),
(101, 0, 10, 3, 0, 5, 5, 5, 5, 0, 'deck', 6, NULL, FALSE),
(102, 0, 10, 3, 0, 2, 2, 2, 2, 0, 'deck', 7, NULL, FALSE),
(103, 0, 10, 4, 3, 3, 6, 0, 3, 0, 'deck', 8, NULL, FALSE),
(104, 0, 10, 4, 0, 6, 6, 3, 6, 0, 'deck', 9, NULL, FALSE),

/* Special achievements */
(105, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'achievements', 10, NULL, FALSE),
(106, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'achievements', 11, NULL, FALSE),
(107, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'achievements', 12, NULL, FALSE),
(108, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'achievements', 13, NULL, FALSE),
(109, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'achievements', 14, NULL, FALSE),

/* Artifacts - Age 1 */
(110, 1, 1, 0, 1, 4, 0, 4, 4, 0, 'deck', 0, NULL, FALSE),
(111, 1, 1, 0, 4, 4, 4, 0, 4, 0, 'deck', 0, NULL, FALSE),
(112, 1, 1, 0, 1, 0, 2, 2, 2, 0, 'deck', 0, NULL, FALSE),
(113, 1, 1, 1, 4, 2, 0, 2, 2, 0, 'deck', 0, NULL, FALSE),
(114, 1, 1, 1, 2, 2, 2, 0, 2, 0, 'deck', 4, NULL, FALSE),
(115, 1, 1, 1, 0, 1, 4, 1, 1, 0, 'deck', 0, NULL, FALSE),
(116, 1, 1, 2, 4, 0, 4, 4, 4, 0, 'deck', 0, NULL, FALSE),
(117, 1, 1, 2, 1, 0, 4, 1, 1, 0, 'deck', 0, NULL, FALSE),
(118, 1, 1, 2, 0, 1, 4, 4, 4, 0, 'deck', 0, NULL, FALSE),
(119, 1, 1, 3, 0, 3, 3, 3, 3, 0, 'deck', 0, NULL, FALSE),
(120, 1, 1, 3, 0, 1, 1, 1, 1, 0, 'deck', 0, NULL, FALSE),
(121, 1, 1, 3, 0, 2, 2, 2, 2, 0, 'deck', 0, NULL, FALSE),
(124, 1, 1, 4, 0, 4, 3, 3, 3, 0, 'deck', 0, NULL, FALSE),
(127, 1, 2, 1, 1, 0, 3, 3, 3, 0, 'deck', 0, NULL, FALSE);
