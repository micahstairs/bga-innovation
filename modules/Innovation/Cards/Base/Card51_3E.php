<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;
use Innovation\Enums\Locations;

class Card51_3E extends AbstractCard
{
  // Statistics (3rd edition):
  //   - I DEMAND you transfer all the highest cards in your score pile to your hand!
  //   - You may splay your yellow cards right.

  public function initialExecution()
  {
    if (self::isDemand()) {
      foreach (self::getHighestCards(Locations::SCORE) as $card) {
        self::transferToHand($card);
      }
    } else if (self::isFirstNonDemand()) {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): array
  {
    return self::youMay()->splayRight()->withColor(Colors::YELLOW)->build();
  }

  public function demandMightBeEffective(): bool
  {
    return self::hasCards(Locations::SCORE);
  }

  public function nonDemandsMightBeEffective(): bool
  {
    return self::canSplay(Colors::YELLOW);
  }

}