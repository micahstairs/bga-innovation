<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;

class Card167 extends AbstractCard
{
  // Frigate Constitution
  //   - I COMPEL you to reveal a card in your hand! If you do, and its value is equal to the value
  //     of any of my top cards, return it and all cards of its color from your board!

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      return [
        'location_from' => Locations::HAND,
        'location_to'   => Locations::REVEALED,
      ];
    } else {
      return [
        'n'              => 'all',
        'location_from'  => Locations::PILE,
        'return_keyword' => true,
        'color'          => self::getLastSelectedColor(),
      ];
    }
  }

  public function handleCardChoice(array $card)
  {
    if (self::isFirstInteraction()) {
      if (in_array($card['faceup_age'], self::getValues(self::getTopCards()))) {
        self::return($card);
        self::setMaxSteps(2);
      } else {
        self::transferToHand($card);
      }
    }
  }
}