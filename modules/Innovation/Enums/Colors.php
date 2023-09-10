<?php

namespace Innovation\Enums;

class Colors
{
  const BLUE = 0;
  const RED = 1;
  const GREEN = 2;
  const YELLOW = 3;
  const PURPLE = 4;

  const ALL = [self::BLUE, self::RED, self::GREEN, self::YELLOW, self::PURPLE];

  const NON_BLUE = [self::RED, self::GREEN, self::YELLOW, self::PURPLE];
  const NON_RED = [self::BLUE, self::GREEN, self::YELLOW, self::PURPLE];
  const NON_GREEN = [self::BLUE, self::RED, self::YELLOW, self::PURPLE];
  const NON_YELLOW = [self::BLUE, self::RED, self::GREEN, self::PURPLE];
  const NON_PURPLE = [self::BLUE, self::RED, self::GREEN, self::YELLOW];

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