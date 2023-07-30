<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\Card;

class Card382 extends Card
{

  // Stove
  //   - Score a top card from your board without a [INDUSTRY].
  //   - Draw and tuck a [4]. If your top card of the tucked card's color has value less than 4,
  //     draw and score a [4].
  //   - You may splay your green cards right.

  public function initialExecution()
  {
    if (self::isEcho()) {
      self::setMaxSteps(1);
    } else if (self::isFirstNonDemand()) {
      $tuckedCard = self::drawAndTuck(4);
      $topCard = self::getTopCardOfColor($tuckedCard);
      if ($topCard['age'] < 4) {
        self::drawAndScore(4);
      }
    } else {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isEcho()) {
      return [
        'location_from' => 'board',
        'score_keyword' => true,
        'without_icon'  => $this->game::INDUSTRY,
      ];
    } else {
      return [
        'can_pass'        => true,
        'splay_direction' => $this->game::RIGHT,
        'color'           => [$this->game::GREEN],
      ];
    }
  }

}