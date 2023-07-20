<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\Card;

class Card342 extends Card
{

  // Bell
  // - 3rd edition:
  //   - ECHO: You may score a card from your hand.
  //   - Draw and foreshadow a [2].
  // - 4th edition:
  //   - ECHO: You may score a card from your hand.
  //   - Draw and foreshadow a [1] and [2].
  //   - If Bell was foreseen, return all cards from all hands.

  public function initialExecution()
  {
    if (self::isEcho()) {
      self::setMaxSteps(1);
    } else if (self::getEffectNumber() === 1) {
      if (self::isFourthEdition()) {
        self::drawAndForeshadow(1);
      }
      self::drawAndForeshadow(2);
    } else if (self::wasForeseen()) {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isEcho()) {
      return [
        'can_pass'      => true,
        'location_from' => 'hand',
        'score_keyword' => true,
      ];
    } else {
      return [
        'n'             => 'all',
        'owner_from'    => 'any player',
        'location_from' => 'hand',
        'location_to'   => 'deck',
      ];
    }
  }

}