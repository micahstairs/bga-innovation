<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;

class Card9 extends AbstractCard
{
  // Agriculture
  // - 3rd edition:
  //   - You may return a card from your hand. If you do, draw and score a card of value one higher than the card you returned.
  // - 4th edition:
  //   - You may return a card from your hand. If you do, draw and score a card of value one higher than the card you return.

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    return [
      'can_pass'       => true,
      'location_from'  => Locations::HAND,
      'return_keyword' => true,
    ];
  }

  public function handleCardChoice(array $card)
  {
    self::drawAndScore(self::getValue($card) + 1);
  }

}