<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;

class Card197_3E extends AbstractCard
{
  // United Nations Charter (3rd edition):
  //   - I COMPEL you to transfer all top cards on your board with a demand effect to my score pile!
  //   - If you have a top card on your board with a demand effect, draw a [10].

  public function initialExecution()
  {
    if (self::isCompel()) {
      self::setMaxSteps(1);
    } else {
      foreach (self::getTopCards() as $card) {
        if (self::hasDemandEffect($card)) {
          self::draw(10);
          return;
        }
      }
    }
  }

  public function getInteractionOptions(): array
  {
    return self::youMust()->all()->fromMyBoard()->toYourScore()->withDemandEffect()->build();
  }

  public function compelMightBeEffective(): bool
  {
    return self::hasTopCardWithDemandEffect();
  }

  public function nonDemandsMightBeEffective(): bool
  {
    return self::hasTopCardWithDemandEffect();
  }

  private function hasTopCardWithDemandEffect(): bool
  {
    foreach (self::getTopCards() as $card) {
      if (self::hasDemandEffect($card)) {
        return true;
      }
    }
    return false;
  }

}