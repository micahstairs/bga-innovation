<?php

namespace Innovation\Enums;

use Innovation\Utils\Strings;

class Icons
{
  const HEX_IMAGE = 0;
  const PROSPERITY = 1;
  const HEALTH = 2;
  const CONCEPT = 3;
  const AUTHORITY = 4;
  const INDUSTRY = 5;
  const EFFICIENCY = 6;
  const AVATAR = 7;
  const FLAG = 8;
  const ECHO_EFFECT = 10;

  public static function render(int $icon): string
  {
    $name = '';
    switch ($icon) {
      case self::PROSPERITY:
        $name = clienttranslate('prosperity');
        break;
      case self::HEALTH:
        $name = clienttranslate('health');
        break;
      case self::CONCEPT:
        $name = clienttranslate('concept');
        break;
      case self::AUTHORITY:
        $name = clienttranslate('authority');
        break;
      case self::INDUSTRY:
        $name = clienttranslate('industry');
        break;
      case self::EFFICIENCY:
        $name = clienttranslate('efficiency');
        break;
      case self::AVATAR:
        $name = clienttranslate('avatar');
        break;
    }
    return Strings::format("<span name='{name}' class='square N icon_{icon}'></span>", ['icon' => $icon, 'name' => $name]);
  }
}