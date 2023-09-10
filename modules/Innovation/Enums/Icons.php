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
  const ECHO_EFFECT = 10;

  public static function render(int $icon): string
  {
    $name = '';
    switch ($icon) {
      case 1:
        $name = clienttranslate('prosperity');
        break;
      case 2:
        $name = clienttranslate('health');
        break;
      case 3:
        $name = clienttranslate('concept');
        break;
      case 4:
        $name = clienttranslate('authority');
        break;
      case 5:
        $name = clienttranslate('industry');
        break;
      case 6:
        $name = clienttranslate('efficiency');
        break;
      case 7:
        $name = clienttranslate('avatar');
        break;
    }
    return Strings::format("<span name='{name}' class='square N icon_{icon}'></span>", ['icon' => $icon, 'name' => $name]);
  }
}