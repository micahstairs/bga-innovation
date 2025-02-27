<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\CardIds;
use Innovation\Enums\Icons;
use Innovation\Enums\Locations;

class Card26 extends AbstractCard
{
  // Translation:
  // - 3rd edition:
  //   - You may meld all the cards in your score pile. If you meld one, you must meld them all.
  //   - If each top card on your board has a [PROSPERITY], claim the World achievement.
  // - 4th edition:
  //   - You may meld all the cards in your score pile.
  //   - If each top card on your board has [PROSPERITY], claim the World achievement.

  public function initialExecution()
  {
    if (self::isFirstNonDemand()) {
      self::setMaxSteps(1);
    } else {
      if (self::allTopCardsHaveProsperity()) {
        self::claim(CardIds::WORLD);
      }
    }
  }

  public function getInteractionOptions(): array
  {
    return self::youMay()->meld()->all()->fromYourScore()->build();
  }

  private function allTopCardsHaveProsperity(): bool
  {
    foreach (self::getTopCards() as $card) {
      if (!self::hasIcon($card, Icons::PROSPERITY)) {
        return false;
      }
    }
    return true;
  }

  public function nonDemandsMightBeEffective(): bool
  {
    return self::hasCards(Locations::SCORE) || (self::allTopCardsHaveProsperity() && self::isAvailable(CardIds::WORLD));
  }

}