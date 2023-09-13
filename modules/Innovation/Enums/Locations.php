<?php

namespace Innovation\Enums;

class Locations
{
  const ACHIEVEMENTS = 'achievements';
  const BOARD = 'board';
  const DISPLAY = 'display';
  const FORECAST = 'forecast';
  const HAND = 'hand';
  const REVEALED = 'revealed'; // This location does not actually exist in the physical game
  const SAFE = 'safe';
  const SCORE = 'score';

  // Special values which are used for interaction options but cannot be encoded/decoded
  const AVAILABLE_ACHIEVEMENTS = 'available achievements';
}