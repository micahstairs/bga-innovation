<?php

namespace Innovation\Enums;

class Colors
{
  const BLUE = 0;
  const RED = 1;
  const GREEN = 2;
  const YELLOW = 3;
  const PURPLE = 4;

  const ALL = [0, 1, 2, 3, 4];

  const NON_BLUE = [RED, GREEN, YELLOW, PURPLE];
  const NON_RED = [BLUE, GREEN, YELLOW, PURPLE];
  const NON_GREEN = [BLUE, RED, YELLOW, PURPLE];
  const NON_YELLOW = [BLUE, RED, GREEN, PURPLE];
  const NON_PURPLE = [BLUE, RED, GREEN, YELLOW];

  public static function getAllColorsOtherThan(int $color)
  {
    return array_diff(range(0, 4), [$color]);
  }

  public static function render($color): string
  {
    switch ($color) {
      case self::BLUE:
        return clienttranslate('blue');
      case self::RED:
        return clienttranslate('red');
      case self::GREEN:
        return clienttranslate('green');
      case self::YELLOW:
        return clienttranslate('yellow');
      case self::PURPLE:
        return clienttranslate('purple');
      default:
        return '';
    }
  }
}