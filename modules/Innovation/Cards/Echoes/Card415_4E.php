<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;
use Innovation\Enums\Directions;
use Innovation\Enums\Locations;

class Card415_4E extends AbstractCard
{

  // Calculator (4th edition):
  //   - Score two bottom non-blue cards of different color on your board. If you score two and they
  //     have a total value less than 12, draw a card of that total value and repeat this effect
  //     (once only).
  //   - You may splay your blue cards up.

  public function initialExecution()
  {
    if (self::isFirstNonDemand()) {
      self::setAuxiliaryArray([]); // Tracks total value of cards scored
      self::setMaxSteps(1);
    } else if (self::isSecondNonDemand()) {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstNonDemand()) {
      return [
        'n'                 => 2,
        'location_from'     => Locations::BOARD,
        'bottom_from'       => true,
        'score_keyword'     => true,
        'color'             => Colors::NON_BLUE,
      ];
    } else {
      return [
        'can_pass'        => true,
        'splay_direction' => Directions::UP,
        'color'           => [Colors::BLUE],
      ];
    }
  }

  public function handleCardChoice(array $card)
  {
    self::addToAuxiliaryArray(self::getValue($card));
  }

  public function afterInteraction()
  {
    if (self::isFirstNonDemand()) {
      $total = array_sum(self::getAuxiliaryArray());
      $threshold = 12;
      if ($total < $threshold) {
        self::draw($total);
        self::setAuxiliaryArray([]);
        self::setMaxSteps(2);
      }
    }
  }

}