<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;
use Innovation\Enums\Directions;
use Innovation\Enums\Locations;

class Card430_3E extends AbstractCard
{

  // Flash Drive (3rd edition):
  //   - I DEMAND you return four cards from your score pile!
  //   - Return a card from your score pile. If you do, you may splay any one color of your cards up.

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    if (self::isDemand()) {
      return [
        'n'              => 4,
        'location_from'  => Locations::SCORE,
        'return_keyword' => true,
      ];
    } else if (self::isFirstInteraction()) {
      return [
        'location_from'  => Locations::SCORE,
        'return_keyword' => true,
      ];
    } else {
      return [
        'can_pass'        => true,
        'splay_direction' => Directions::UP,
      ];
    }
  }

  function handleCardChoice(array $card)
  {
    if (self::isNonDemand() && self::isFirstInteraction()) {
      self::setMaxSteps(2);
    }
  }

}