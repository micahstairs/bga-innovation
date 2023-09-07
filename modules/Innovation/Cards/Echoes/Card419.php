<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\Card;
use Innovation\Enums\Colors;
use Innovation\Enums\Directions;

class Card419 extends Card
{

  // Credit Card
  // - 3rd edition
  //   - ECHO: Draw and foreshadow a [9].
  //   - You may take a top non-green card from your board into your hand. If you do, draw and
  //     score a card of equal value.
  //   - You may splay your green cards up.
  // - 4th edition
  //   - ECHO: Draw and foreshadow a [9].
  //   - You may transfer a top non-green card from your board to your hand. If you do, draw and
  //     score a card of equal value. If you do, and Credit Card was foreseen, repeat this effect.
  //   - You may splay your green cards up.

  public function initialExecution()
  {
    if (self::isEcho()) {
      self::drawAndForeshadow(9);
    } else {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstNonDemand()) {
      return [
        'can_pass'      => true,
        'location_from' => 'board',
        'location_to'   => 'hand',
        'color'         => Colors::NON_GREEN,
      ];
    } else {
      return [
        'can_pass'        => true,
        'splay_direction' => Directions::UP,
        'color'           => [Colors::GREEN],
      ];
    }
  }

  public function handleCardChoice(array $card)
  {
    $scoredCard = self::drawAndScore($card['faceup_age']);
    if ($scoredCard['age'] == $card['faceup_age'] && self::wasForeseen()) {
      self::setNextStep(1);
    }
  }

}