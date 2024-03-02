<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;

class Card164_4E extends AbstractCard
{
  // Almira, Queen of Castile (4th edition):
  //   - Meld a card from your hand. If you do, claim an achievement of matching value, ignoring
  //     eligibility. Otherwise, junk all cards in the deck of value equal to the lowest available
  //     achievement, if there is one.

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      return [
        'location_from' => Locations::HAND,
        'meld_keyword'  => true,
      ];
    } else {
      return [
        'age'             => self::getLastSelectedFaceUpAge(),
        'achieve_keyword' => true,
      ];
    }
  }

  public function afterInteraction()
  {
    if (self::isFirstInteraction()) {
      if (self::getNumChosen() === 1) {
        self::setMaxSteps(2);
      } else {
        $achievementsByValue = self::getCardsKeyedByValue(Locations::AVAILABLE_ACHIEVEMENTS);
        foreach ($achievementsByValue as $achievements) {
          if ($achievements) {
            self::junkBaseDeck(self::getValue($achievements[0]));
            break;
          }
        }
      }
    }
  }
}
