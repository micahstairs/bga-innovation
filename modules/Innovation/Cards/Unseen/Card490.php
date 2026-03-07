<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\Colors;
use Innovation\Enums\Locations;

class Card490 extends AbstractCard
{
  // Tomb
  //   - Safeguard an available achievement of value 1 plus the number of achievements you have.
  //   - You may transfer the lowest available achievement to your hand. If you do, return all
  //     yellow cards and all blue cards on your board.

  public function getInteractionOptions(): InteractionBuilder
  {
    if (self::isFirstNonDemand()) {
      $value = self::countCards(Locations::ACHIEVEMENTS) + 1;
      return self::youMust()->safeguard()->value($value);
    } else if (self::isFirstInteraction()) {
      $value = self::getMinValue(self::getAvailableStandardAchievements());
      return self::youMay()->value($value)->fromAvailableAchievements()->toYourHand();
    } else {
      return self::youMust()->return()->fromAnywhereInStack()->withColor([Colors::YELLOW, Colors::BLUE]);
    }
  }

  public function handleCardChoice(array $card)
  {
    if (self::isSecondNonDemand() && self::isFirstInteraction()) {
      self::setMaxSteps(2);
    }
  }

}