<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;
use Innovation\Enums\Directions;

class Card385_3E extends AbstractCard
{

  // Bifocals (3rd edition):
  //   - ECHO: Draw and foreshadow a card of any value.
  //   - You may return a card from your forecast. If you do, draw and foreshadow a card of equal
  //     value to the card returned.
  //   - You may splay your green cards right.

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    if (self::isEcho()) {
      return ['choose_value' => true];
    } else if (self::isFirstNonDemand()) {
      return [
        'can_pass'       => true,
        'location_from'  => 'forecast',
        'return_keyword' => true,
      ];
    } else {
      return [
        'can_pass'        => true,
        'splay_direction' => Directions::RIGHT,
        'color'           => [Colors::GREEN],
      ];
    }
  }

  public function handleValueChoice($value)
  {
    self::drawAndForeshadow($value);
  }

  public function handleCardChoice(array $card)
  {
    if (self::isFirstNonDemand()) {
      self::drawAndForeshadow($card['age']);
    }
  }

}