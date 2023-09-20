<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\Card;

class Card138 extends Card
{

  // Mjölnir Amulet
  //   - I COMPEL you to choose a top card on your board! Transfer all cards of that card's color
  //     from your board to my score pile!

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    return ['choose_from' => 'board'];
  }

  public function handleCardChoice(array $card) {
    foreach (array_reverse(self::getStack($card['color'])) as $card) {
      self::transferToScorePile($card);
    }
  }

}