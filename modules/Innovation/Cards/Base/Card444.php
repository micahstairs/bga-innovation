<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;

class Card444 extends AbstractCard
{
  // Hypersonics
  //   - I DEMAND you return exactly two top cards of different colors from your board of the same
  //     value! If you do, return all cards of that value or less in your hand and score pile!

  public function initialExecution()
  {
    if (self::getRepeatedValues(self::getTopCards())) {
      self::setMaxSteps(3);
    } else {
      self::notifyPlayer(clienttranslate('${You} do not have two top cards of matching value on your board.'));
      self::notifyOthers(clienttranslate('${player_name} does not have two top cards of matching value on his board.'));
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      $topCards = self::getTopCards();
      $colors = self::getColorsMatchingValues($topCards, self::getRepeatedValues($topCards));
      return self::youMust()->return()->fromYourBoard()->withColor($colors)->build();
    } else if (self::isSecondInteraction()) {
      return self::youMust()->return()->fromYourBoard()->value(self::getAuxiliaryValue())->non(self::getLastSelectedColor())->build();
    } else {
      return self::youMust()->return()->all()->fromYourHandOrScore()->maxValue(self::getAuxiliaryValue())->build();
    }
  }

  public function demandMightBeEffective(): bool
  {
    return count(self::getRepeatedValues(self::getTopCards())) > 0;
  }

}