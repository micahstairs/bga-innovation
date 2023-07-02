<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;

class Card590 extends Card
{

  // Mystery Box:
  //   - Claim an available standard achievement, regardless of eligibility. Self-execute it.

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    return [
      'owner_from'    => 0,
      'location_from' => 'achievements',
      'owner_to'      => self::getPlayerId(),
      'location_to'   => 'achievements',
      'age_min'       => 1,
      'age_max'       => 11,
    ];
  }

  public function handleCardChoice(array $card)
  {
    self::selfExecute($card);
  }

}