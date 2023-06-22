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
    if (self::getEffectNumber() == 1) {
      self::setMaxSteps(1);
    } else {
      $numFours = $this->game->countCardsInLocationKeyedByAge(self::getPlayerId(), 'score')[4];
      for ($i = 0; $i < $numFours; $i++) {
        self::draw(4);
      }
    }

  }

  public function getInteractionOptions(Executionstate $state): array
  {
    if (self::getCurrentStep() == 1) {
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
    if (self::getCurrentStep() == 1) {
      if (self::getNumChosen() == 0) {
        self::setMaxSteps(2);
      }
    } else {
      self::drawAndScore(4);
    }
  }

}