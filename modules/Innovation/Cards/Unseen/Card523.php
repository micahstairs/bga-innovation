<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;
use Innovation\Cards\ExecutionState;

class Card523 extends Card
{

  // Confession:
  //   - Return a top card with a AUTHORITY of each color from your board. If you return none, meld
  //     a card from your score pile, then draw and score a [4].
  //   - Draw a [4] for each [4] in your score pile.

  public function initialExecution(ExecutionState $state)
  {
    if ($state->getEffectNumber() == 1) {
      $state->setMaxSteps(1);
    } else {
      $numFours = $this->game->countCardsInLocationKeyedByAge($state->getPlayerId(), 'score')[4];
      for ($i = 0; $i < $numFours; $i++) {
        self::draw(4);
      }
    }

  }

  public function getInteractionOptions(Executionstate $state): array
  {
    if ($state->getCurrentStep() == 1) {
      return [
        'n'             => 'all',
        'location_from' => 'board',
        'location_to'   => 'deck',
        'with_icon'     => $this->game::AUTHORITY,
      ];
    } else {
      return [
        'location_from' => 'score',
        'meld_keyword'  => true,
      ];
    }
  }

  public function afterInteraction(Executionstate $state)
  {
    if ($state->getCurrentStep() == 1) {
      if ($state->getNumChosen() == 0) {
        $state->setMaxSteps(2);
      }
    } else {
      self::drawAndScore(4);
    }
  }

}