<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\Card;

class Card413 extends Card
{

  // Calculator
  // - 3rd edition
  //   - Score two bottom non-blue cards from your board. If you scored two and they have a total
  //     value less than 11, draw a card of that total value and repeat this dogma effect (once only).
  //   - You may splay your blue cards up.
  // - 4th edition
  //   - Score two bottom non-blue cards on your board. If you score two and they have a total value
  //     less than 12, draw a card of that total value and repeat this effect (once only).
  //   - You may splay your blue cards up.

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstNonDemand()) {
      if (self::getCurrentStep() === 1) {
        self::setAuxiliaryArray([]); // Tracks total value of cards scored
      }
      return [
        'n' => 2,
        'location_from' => 'board',
        'bottom_from' => true,
        'score_keyword' => true,
        'color' => self::getAllColorsOtherThan($this->game::BLUE),
      ];
    } else {
      return [
        'can_pass' => true,
        'splay_direction' => $this->game::UP,
        'color' => [$this->game::BLUE],
      ];
    }
  }

  public function handleCardChoice(array $card) {
    self::addToAuxiliaryArray($card['faceup_age']);
  }

  public function afterInteraction() {
    if (self::isFirstNonDemand()) {
      $total = array_sum(self::getAuxiliaryArray());
      $threshold = self::isFirstOrThirdEdition() ? 11 : 12;
      if ($total < $threshold) {
        self::draw($total);
        self::setAuxiliaryArray([]);
        self::setMaxSteps(2);
      }
    }
  }

}