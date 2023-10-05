<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;
use Innovation\Enums\Directions;

class Card415_3E extends AbstractCard
{

  // Calculator (3rd edition):
  //   - Score two bottom non-blue cards from your board. If you scored two and they have a total
  //     value less than 11, draw a card of that total value and repeat this dogma effect (once only).
  //   - You may splay your blue cards up.

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstNonDemand()) {
      if (self::isFirstInteraction()) {
        self::setAuxiliaryArray([]); // Tracks total value of cards scored
      }
      return [
        'n'             => 2,
        'location_from' => 'board',
        'bottom_from'   => true,
        'score_keyword' => true,
        'color'         => Colors::NON_BLUE,
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
      if ($total < 11) {
        self::draw($total);
        self::setAuxiliaryArray([]);
        self::setMaxSteps(2);
      }
    }
  }

}