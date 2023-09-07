<?php

namespace Innovation\Enums;

class Directions
{
  const UNSPLAYED = 0;
  const LEFT = 1;
  const RIGHT = 2;
  const UP = 3;
  const ASLANT = 4;

  public static function render(int $direction): string
  {
    switch ($direction) {
      case self::UNSPLAYED:
        return clienttranslate('none');
      case self::LEFT:
        return clienttranslate('left');
      case self::RIGHT:
        return clienttranslate('right');
      case self::UP:
        return clienttranslate('up');
      case self::ASLANT:
        return clienttranslate('aslant');
      default:
        return '';
    }
  }
}