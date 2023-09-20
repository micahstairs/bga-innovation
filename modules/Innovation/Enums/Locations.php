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
  const PILE = 'pile'; // This is special location used for board interactions, which allows players to choose all cards in the pile instead of just the top card of the stack
  const RELICS = 'relics';
  const REVEALED = 'revealed'; // This location does not actually exist in the physical game
  const SAFE = 'safe';
  const SCORE = 'score';

  // Special values which are used to reveal cards on their way to another location
  const REVEALED_THEN_DECK = 'revealed,deck';

  // Special values which are used for interaction options but cannot be encoded/decoded
  const AVAILABLE_ACHIEVEMENTS = 'available achievements';
}