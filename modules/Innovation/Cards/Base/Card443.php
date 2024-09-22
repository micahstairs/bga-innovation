<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;

class Card443 extends AbstractCard
{
  // Fusion
  //   - Score a top card of value [11] on your board. If you do, choose a value one or two lower
  //     than the scored card, then repeat this dogma effect using the chosen value.

  public function initialExecution()
  {
    self::setMaxSteps(2);
    self::setAuxiliaryArray([11]); // Track values to choose from to score next
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      return self::youMust()->chooseValue(self::getAuxiliaryArray())->build();
    } else {
      return self::youMust()->score()->fromYourBoard()->value(self::getAuxiliaryValue())->build();
    }
  }

  public function handleCardChoice(array $card)
  {
    $value = self::getValue($card);
    // TODO(4E): Make sure this doesn't do weird things if the player is supposed to select a -1 or -2
    self::setAuxiliaryArray([$value - 2, $value - 1]);
    self::setNextStep(1);
  }

  public function handleValueChoice(int $value)
  {
    self::setAuxiliaryValue($value); // Track value to score next
  }

  public function nonDemandsMightBeEffective(): bool
  {
    return count(self::filterByValue(self::getTopCards(), [11])) > 0;
  }

}