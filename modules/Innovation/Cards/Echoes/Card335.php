<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\Card;

class Card335 extends Card
{

  // Plumbing
  // - 3rd edition:
  //   - ECHO: Score a bottom card from your board.
  //   - No effect.
  // - 4th edition:
  //   - ECHO: Score the bottom blue card from your board.
  //   - Junk all cards in the [1] deck.

  public function initialExecution()
  {
    if (self::isEcho()) {
      self::setMaxSteps(1);
    } else if (self::isFourthEdition()) {
      self::junkBaseDeck(1);
    }
  }

  public function getInteractionOptions(): array
  {
    $options = [
      'location_from' => 'board',
      'score_keyword' => true,
      'bottom_from' => true,
    ];
    if (self::isFourthEdition()) {
      $options['color'] = [$this->game::BLUE];
    }
    return $options;
  }

}