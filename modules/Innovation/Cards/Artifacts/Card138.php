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
    $stack = self::getCardsKeyedByColor('board')[$card['color']];
    foreach (array_reverse($stack) as $card) {
      self::transferToScorePile($card);
    }
  }

}