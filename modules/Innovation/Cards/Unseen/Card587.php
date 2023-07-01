<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;

class Card587 extends Card
{

  // Cloaking:
  //   - I DEMAND you transfer one of your claimed standard achievements to my safe!

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    return [
      'owner_from'    => self::getPlayerId(),
      'location_from' => 'achievements',
      'owner_to'    => self::getLauncherId(),
      'location_to'   => 'safe',
      'age_min' => 1,
      'age_max' => 11,
    ];
  }

}