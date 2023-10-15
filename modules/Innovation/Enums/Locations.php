<?php

namespace Innovation\Enums;

class Locations
{
  const ACHIEVEMENTS = 'achievements';
  const BOARD = 'board';
  const DECK = 'deck';
  const DISPLAY = 'display';
  const FORECAST = 'forecast';
  const HAND = 'hand';
  const JUNK = 'junk';
  const MUSEUM = 'museum';
  const PILE = 'pile'; // This is special location used for board interactions, which allows players to choose all cards in the pile instead of just the top card of the stack
  const RELICS = 'relics';
  const REMOVED = 'removed';
  const REVEALED = 'revealed'; // This location does not actually exist in the physical game
  const SAFE = 'safe';
  const SCORE = 'score';

  // Special values which represent multiple locations
  const HAND_OR_SCORE = 'hand,score';

  // Special values which are used to reveal cards on their way to another location
  const REVEALED_THEN_DECK = 'revealed,deck';

  // Special values which are used for interaction options but cannot be encoded/decoded
  const AVAILABLE_ACHIEVEMENTS = 'available achievements';

  const PLAYER_LOCATIONS = [
    self::ACHIEVEMENTS,
    self::BOARD,
    self::DISPLAY,
    self::FORECAST,
    self::HAND,
    self::MUSEUM,
    self::REVEALED,
    self::SAFE,
    self::SCORE,
  ];

  const FACEUP_LOCATIONS = [
    self::BOARD,
    self::DISPLAY,
    self::MUSEUM,
    self::REVEALED,
  ];

  public static function isFaceup(string $location): bool
  {
    return in_array($location, self::FACEUP_LOCATIONS);
  }
  public static function render($location)
  {
    switch ($location) {
      case self::DECK:
        return clienttranslate('deck');
      case self::HAND:
        return clienttranslate('hand');
      case self::BOARD:
        return clienttranslate('board');
      case self::SCORE:
        return clienttranslate('score pile');
      case self::REVEALED:
        return clienttranslate('forecast');
      case self::SAFE:
        return clienttranslate('safe');
      default:
        // NOTE: If this code path gets hit, then that means we are not properly translating it.
        error_log("Unhandled case in Locations::render: $location.");
        return $location;
    }
  }

  public static function encode($location)
  {
    switch ($location) {
      case self::DECK:
        return 0;
      case self::HAND:
        return 1;
      case self::BOARD:
        return 2;
      case self::SCORE:
        return 3;
      case self::REVEALED:
        return 4;
      case 'revealed,hand':
        return 5;
      case self::REVEALED_THEN_DECK:
        return 6;
      case self::PILE:
        return 7;
      case 'revealed,score':
        return 8;
      case self::ACHIEVEMENTS:
        return 9;
      case 'none':
        return 10;
      case self::DISPLAY:
        return 11;
      case self::RELICS:
        return 12;
      case self::REMOVED:
        return 13;
      case self::FORECAST:
        return 14;
      case Locations::HAND_OR_SCORE:
        return 15;
      case self::JUNK:
        return 16;
      case self::SAFE:
        return 17;
      case 'junk,safe':
        return 18;
      case 'pile,score':
        return 19;
      case self::MUSEUM:
        return 20;
      default:
        throw new \Exception("Unhandled case in Locations::encode: $location.");
    }
  }

  public static function decode(int $locationCode)
  {
    switch ($locationCode) {
      case 0:
        return self::DECK;
      case 1:
        return self::HAND;
      case 2:
        return self::BOARD;
      case 3:
        return self::SCORE;
      case 4:
        return self::REVEALED;
      case 5:
        return 'revealed,hand';
      case 6:
        return self::REVEALED_THEN_DECK;
      case 7:
        return self::PILE;
      case 8:
        return 'revealed,score';
      case 9:
        return self::ACHIEVEMENTS;
      case 10:
        return 'none';
      case 11:
        return self::DISPLAY;
      case 12:
        return self::RELICS;
      case 13:
        return self::REMOVED;
      case 14:
        return self::FORECAST;
      case 15:
        return Locations::HAND_OR_SCORE;
      case 16:
        return self::JUNK;
      case 17:
        return self::SAFE;
      case 18:
        return 'junk,safe';
      case 19:
        return 'pile,score';
      case 20:
        return self::MUSEUM;
      default:
        throw new \Exception("Unhandled case in Locations::decode: $locationCode.");
    }
  }
}