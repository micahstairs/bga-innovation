<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;

class Card440 extends AbstractCard
{

  // Climatology:
  //   - I DEMAND you return two top cards from your board each with the icon of my choice other
  //     than [HEALTH]!
  //   - Return a top card from your board. Return all cards in your score pile of equal or higher
  //     value than the top card.

  public function initialExecution()
  {
    if (self::isDemand()) {
      self::setMaxSteps(3);
    } else {
      self::setMaxSteps(2);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isDemand()) {
      if (self::isFirstInteraction()) {
        // TODO(4E): Non-standard icons should be an option too here (and use constants).
        return self::youMust()->chooseIcon([1, 3, 4, 5, 6, 7])->ofMyChoice()->build();
      } else {
        return self::youMust()->return()->exactly(2)->fromYourBoard()->withIcon(self::getAuxiliaryValue())->refreshingSelection()->build();
      }
    }
    if (self::isFirstInteraction()) {
      return self::youMust()->return()->fromYourBoard()->build();
    } else {
      return self::youMust()->return()->all()->fromYourScore()->minValue(self::getAuxiliaryValue())->build();
    }
  }

  public function afterInteraction()
  {
    if (self::isNonDemand() && self::isFirstInteraction()) {
      $minAgeToReturn = 0;
      if (self::getNumChosen() > 0) {
        $minAgeToReturn = self::getLastSelectedFaceupAge();
      }
      self::setAuxiliaryValue($minAgeToReturn);
    }
  }

  public function handleIconChoice(int $icon)
  {
    self::notifyIconChoice($icon);
    self::setAuxiliaryValue($icon);
  }

  public function demandMightBeEffective(): bool
  {
    // NOTE: This could be improved by filtering out stacks where the top card has only [HEALTH] icons.
    return self::hasCards(Locations::BOARD);
  }

  public function nonDemandsMightBeEffective(): bool
  {
    return self::hasCards(Locations::BOARD) || self::hasCards(Locations::SCORE);
  }

}