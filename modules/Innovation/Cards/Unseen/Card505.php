<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;
use Innovation\Enums\Icons;

class Card505 extends Card
{

  // Brethren of Purity:
  //   - Draw and meld a [3] or a card of value one higher than the last card melded due to
  //     Brethren of Purity during this action. If you meld over a card with a [CONCEPT], repeat
  //     this effect.

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    // The array will either contain a single value (if a card has been melded due to Brethren of
    // Purity during this action) or it will be empty.
    $array = self::getActionScopedAuxiliaryArray();
    $lastValue = empty($array) ? 0 : $array[0];
    return [
      'choose_value' => true,
      'age'          => array_unique([3, $lastValue + 1]),
    ];
  }

  public function handleValueChoice(int $value)
  {
    self::setAuxiliaryValue($value);
  }

  public function afterInteraction()
  {
    $card = self::drawAndMeld(self::getAuxiliaryValue());
    self::setActionScopedAuxiliaryArray([$card['age']]);
    $stack = self::getStack($card['color']);
    $numCards = count($stack);
    if ($numCards >= 2 && self::hasIcon($stack[$numCards - 2], Icons::CONCEPT)) {
      self::setNextStep(1);
    }
  }

}