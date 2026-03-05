<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\CardIds;
use Innovation\Enums\Colors;
use Innovation\Enums\Icons;
use Innovation\Enums\Locations;

class Card11_4E extends AbstractCard
{
  // Masonry (4th edition):
  //   - You may meld any number of cards from your hand, each with [AUTHORITY].
  //   - If you have exactly three red cards on your board, claim the Monument achievement.

  public function initialExecution()
  {
    if (self::isFirstNonDemand()) {
      self::setMaxSteps(1);
    } else if (self::isSecondNonDemand()) {
      if (count(self::getStack(Colors::RED)) === 3) {
        self::claim(CardIds::MONUMENT);
      }
    }
  }

  public function getInteractionOptions(): InteractionBuilder
  {
    return self::youMay()->meld()->anyNumber()->fromYourHand()->withIcon(Icons::AUTHORITY);
  }

  public function nonDemandsMightBeEffective(): bool
  {
    if (self::countCardsKeyedByColor(Locations::BOARD)[Colors::RED] == 3) {
      return true;
    }
    if (self::isLauncher()) {
      return count(self::filterByIcon(self::getCards(Locations::HAND), Icons::AUTHORITY)) > 0;
    } else {
      return self::hasCards(Locations::HAND);
    }
  }

}