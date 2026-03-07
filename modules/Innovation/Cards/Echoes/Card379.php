<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\CardIds;
use Innovation\Enums\Colors;
use Innovation\Enums\Directions;
use Innovation\Utils\Arrays;

class Card379 extends AbstractCard
{

  // Palampore
  // - 3rd edition:
  //   - Draw and score a card of value equal to a bonus that occurs more than once on your board,
  //     if you have such a bonus.
  //   - You may splay your purple cards right.
  //   - If you have six or more bonuses on your board, claim the Wealth achievement.
  // - 4th edition:
  //   - Draw and score a card of value equal to a bonus that occurs more than once on your board,
  //     if there is one.
  //   - You may splay your purple cards right.
  //   - If you have at least six bonuses on your board, claim the Wealth achievement.

  public function initialExecution()
  {
    if (self::isFirstNonDemand() || self::isSecondNonDemand()) {
      self::setMaxSteps(1);
    } else {
      if (count(self::getBonuses()) >= 6) {
        self::claim(CardIds::WEALTH);
      }
    }
  }

  public function getInteractionOptions(): InteractionBuilder
  {
    if (self::isFirstNonDemand()) {
      $values = Arrays::getRepeatedValues(self::getBonuses());
      return self::youMust()->chooseValue($values);
    } else {
      return self::youMay()->splayRight(Colors::PURPLE);
    }
  }

  public function handleValueChoice($value)
  {
    self::drawAndScore($value);
  }

}