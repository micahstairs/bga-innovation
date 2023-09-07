<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\Card;
use Innovation\Enums\Colors;
use Innovation\Enums\Directions;

class Card398 extends Card
{

  // Rubber
  // - 3rd edition
  //   - ECHO: Draw and tuck two [8].
  //   - Score a top card from your board without a bonus.
  //   - You may splay your red cards up.
  // - 4th edition
  //   - ECHO: Draw and tuck two [8].
  //   - Score a top card from your board without a bonus.
  //   - You may splay your red cards up.
  //   - If Rubber was foreseen, foreshadow a top card on your board.

  public function initialExecution()
  {
    if (self::isEcho()) {
      self::drawAndTuck(8);
      self::drawAndTuck(8);
    } else if (self::isFirstNonDemand() || self::isSecondNonDemand() || self::wasForeseen()) {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstNonDemand()) {
      return [
        'location_from' => 'board',
        'score_keyword' => true,
        'without_bonus' => true,
      ];
    } else if (self::isSecondNonDemand()) {
      return [
        'can_pass'        => true,
        'splay_direction' => Directions::UP,
        'color'           => [Colors::RED],
      ];
    } else {
      return [
        'location_from'      => 'board',
        'foreshadow_keyword' => true,
      ];
    }
  }

}