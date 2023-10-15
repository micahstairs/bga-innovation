<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;
use Innovation\Enums\Locations;

class Card149_4E extends AbstractCard
{

  // Molasses Reef Caravel (4th edition):
  //   - Return all cards from your hand.
  //   - Draw three [4]. Meld a green card from your hand. Junk all cards in the deck of value
  //     equal to your top green card.


  public function initialExecution()
  {
    if (self::isFirstNonDemand()) {
      self::setMaxSteps(1);
    } else if (self::isSecondNonDemand()) {
      self::draw(4);
      self::draw(4);
      self::draw(4);
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstNonDemand()) {
      return [
        'n'              => 'all',
        'location_from'  => Locations::HAND,
        'return_keyword' => true,
      ];
    } else {
      return [
        'location_from' => Locations::HAND,
        'meld_keyword'  => true,
        'color'         => [Colors::GREEN],
      ];
    }
  }

  public function afterInteraction()
  {
    if (self::isSecondInteraction()) {
      self::junkBaseDeck(self::getValue(self::getTopCardOfColor(Colors::GREEN)));
    }
  }

}