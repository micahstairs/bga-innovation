<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;
use Innovation\Enums\Locations;

class Card149_3E extends AbstractCard
{

  // Molasses Reef Caravel (3rd edition):
  //   - Return all cards from your hand. Draw three [4]. Meld a blue card from your hand. Score a
  //     card from your hand. Return a card from your score pile.


  public function initialExecution()
  {
    self::setMaxSteps(4);
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      return [
        'n'              => 'all',
        'location_from'  => Locations::HAND,
        'return_keyword' => true,
      ];
    } else if (self::isSecondInteraction()) {
      return [
        'location_from' => Locations::HAND,
        'meld_keyword'  => true,
        'color'         => [Colors::BLUE],
      ];
    } else if (self::isThirdInteraction()) {
      return [
        'location_from' => Locations::HAND,
        'score_keyword' => true,
      ];
    } else {
      return [
        'location_from'  => Locations::SCORE,
        'return_keyword' => true,
      ];
    }
  }

}