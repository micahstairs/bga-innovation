<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
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

  public function getInteractionOptions(): array
  {
    return self::youMay()->meld()->anyNumber()->fromHand()->withIcon(Icons::AUTHORITY)->build();
  }

  public function nonDemandsMightBeEffective(): bool
  {
    if (count(self::filterByIcon(self::getCards(Locations::HAND), Icons::AUTHORITY)) > 0) {
      return true;
    }
    return self::countCardsKeyedByColor(Locations::BOARD)[Colors::RED] == 3;
  }

}