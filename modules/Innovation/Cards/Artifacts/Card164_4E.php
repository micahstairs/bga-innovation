<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\Locations;

class Card164_4E extends AbstractCard
{
  // Almira, Queen of Castile (4th edition):
  //   - Meld a card from your hand. If you do, claim an available achievement of matching value, ignoring
  //     eligibility. Otherwise, junk all cards in the deck of value equal to the lowest available
  //     achievement, if there is one.

  public function getInteractionOptions(): InteractionBuilder
  {
    if (self::isFirstInteraction()) {
      return self::youMust()->meld()->fromYourHand();
    } else {
      return self::youMust()->achieve()->value(self::getLastSelectedFaceUpAge());
    }
  }

  public function afterInteraction()
  {
    if (self::isFirstInteraction()) {
      if (self::getNumChosen() === 1) {
        self::setMaxSteps(2);
      } else {
        $value = self::getLowestAvailableAchievementValue();
        if ($value) {
          self::junkBaseDeck($value);
        }
      }
    }
  }

  private function getLowestAvailableAchievementValue(): ?int
  {
    $achievementsByValue = self::getCardsKeyedByValue(Locations::AVAILABLE_ACHIEVEMENTS);
    foreach ($achievementsByValue as $achievements) {
      if ($achievements) {
        return self::getValue($achievements[0]);
      }
    }
    return null;
  }

  public function nonDemandsMightBeEffective(): bool
  {
    return self::hasCards(Locations::HAND) || self::getBaseDeckCount(self::getLowestAvailableAchievementValue()) > 0;
  }
}
