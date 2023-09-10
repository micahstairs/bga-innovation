<?php

namespace Innovation\Enums;

class CardTypes
{
  const BASE = 0;
  const ARTIFACTS = 1;
  const CITIES = 2;
  const ECHOES = 3;
  const FIGURES = 4;
  const UNSEEN = 5;

  public static function getAllTypesOtherThan(int $type)
  {
    return array_diff(range(0, 5), [$type]);
  }

  public static function render($type): string
  {
    switch ($type) {
      case self::BASE:
        return clienttranslate('Base');
      case self::ARTIFACTS:
        return clienttranslate('Artifacts');
      case self::CITIES:
        return clienttranslate('Cities');
      case self::ECHOES:
        return clienttranslate('Echoes');
      case self::FIGURES:
        return clienttranslate('Figures');
      case self::UNSEEN:
        return clienttranslate('Unseen');
      default:
        return '';
    }
    
  }
}