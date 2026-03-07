<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\Locations;

class Card56_4E extends AbstractCard
{
  // Encyclopedia (4th edition):
  //   - Choose a value. You may meld all the cards of that value in your score pile.
  //   - You may junk an available achievement of value [5], [6], or [7].

  public function initialExecution()
  {
    if (self::isFirstNonDemand()) {
      self::setMaxSteps(2);
    } else {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): InteractionBuilder
  {
    if (self::isFirstNonDemand()) {
      if (self::isFirstInteraction()) {
        return self::youMust()->chooseValue();
      } else {
        return self::youMay()->meld()->all()->value(self::getAuxiliaryValue())->fromYourScore();
      }
    } else {
      return self::youMay()->junk()->fromAvailableAchievements()->range(5, 7);
    }
  }

  public function handleValueChoice(int $value)
  {
    self::setAuxiliaryValue($value);
  }

  public function nonDemandsMightBeEffective(): bool
  {
    return self::hasCards(Locations::SCORE) || self::getBaseDeckCount(5) > 0 || self::getBaseDeckCount(6) > 0 || self::getBaseDeckCount(7) > 0;
  }

}
