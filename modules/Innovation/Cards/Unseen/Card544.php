<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;
use Innovation\Cards\ExecutionState;
use SebastianBergmann\Type\VoidType;

class Card544 extends Card
{

  // Triad:
  //   - If you have three or more cards in your hand, return a card from your hand and splay the
  //     color of the returned card right, tuck a card from your hand, and score a card from your
  //     hand.

  public function initialExecution(ExecutionState $state)
  {
    if ($this->game->countCardsInHand(self::getPlayerId()) >= 3) {
      self::setMaxSteps(3);
    }
  }

  public function getInteractionOptions(Executionstate $state): array
  {
    if ($state->getCurrentStep() == 1) {
      return [
        'location_from' => 'hand',
        'location_to'   => 'deck',
      ];
    } else if ($state->getCurrentStep() == 2) {
      return [
        'location_from' => 'hand',
        'location_to'   => 'board',
        'bottom_to'     => true,
      ];
    } else {
      return [
        'location_from' => 'hand',
        'location_to'   => 'score',
      ];
    }
  }

  public function handleCardChoice(Executionstate $state, int $cardId)
  {
    if ($state->getCurrentStep() == 1) {
      self::splayRight(self::getLastSelectedColor());
    }
  }

}