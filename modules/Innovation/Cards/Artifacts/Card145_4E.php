<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;
use Innovation\Enums\Icons;
use Innovation\Enums\Locations;

class Card145 extends AbstractCard
{

  // Petition of Right (4th edition):
  //   - I COMPEL you to transfer a card from your score pile to my score pile for each color with
  //     a [AUTHORITY] on your board!
  //   - Junk an available achievement of value equal to the number of [AUTHORITY] on your board.


  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    if (self::isCompel()) {
      $numStacksWithAuthority = 0;
      foreach (Colors::ALL as $color) {
        if (self::getIconCountInStack($color, Icons::AUTHORITY)) {
          $numStacksWithAuthority++;
        }
      }
      return [
        'n'        => $numStacksWithAuthority,
        'location' => Locations::SCORE,
        'owner_to' => self::getLauncherId(),
      ];
    } else {
      return [
        'location_from' => Locations::AVAILABLE_ACHIEVEMENTS,
        'junk_keyword'  => true,
        'age'           => self::getStandardIconCount(Icons::AUTHORITY),
      ];
    }
  }

}