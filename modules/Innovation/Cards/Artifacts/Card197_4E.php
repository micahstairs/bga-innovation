<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;

class Card197_4E extends AbstractCard
{
  // Crusader Rabbit (4th edition):
  //   - I COMPEL you to transfer the two bottom cards of each color which has a top card with a
  //     demand effect to my score pile!
  //   - If you have a top card on your board with a demand effect, score it, and draw a [10].

  public function initialExecution()
  {
    if (self::isCompel()) {
      foreach (self::getTopCards() as $card) {
        if (self::hasDemandEffect($card)) {
          self::transferToScorePile(self::getBottomCardOfColor(self::getColor($card)), self::getLauncherId());
          self::transferToScorePile(self::getBottomCardOfColor(self::getColor($card)), self::getLauncherId());
        }
      }
    } else {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): array
  {
    return self::youMust()->score()->fromYourBoard()->withDemandEffect()->build();
  }

  public function handleCardChoice(array $card)
  {
    self::draw(10);
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