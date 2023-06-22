<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;
use Innovation\Cards\ExecutionState;

class Card538 extends Card
{

  // Sniping:
  //   - I DEMAND you unsplay the color on your board of my choice! Meld your bottom card of that
  //     color! Transfer your bottom card of that color to my board!

  public function initialExecution(ExecutionState $state)
  {
    $state->setMaxSteps(1);
  }

  public function getInteractionOptions(Executionstate $state): array
  {
    return [
      'player_id'    => self::getLauncherId(),
      'choose_color' => true,
    ];
  }

  public function handleCardChoice(Executionstate $state, int $cardId)
  {
  }

  public function afterInteraction(Executionstate $state)
  {
      $color = $this->game->getAuxiliaryValue();
      self::unsplay($color);
      $bottomCard = $this->game->getBottomCardOnBoard(self::getPlayerId(), $color);
      if ($bottomCard) {
        self::meld($bottomCard);
      }
      $bottomCard = $this->game->getBottomCardOnBoard(self::getPlayerId(), $color);
      if ($bottomCard) {
        $this->game->transferCardFromTo($bottomCard, $state->getLauncherId(), 'board');
      }
  }

  public function handleSpecialChoice(Executionstate $state, int $choice): void
  {
    self::setAuxiliaryValue($choice);
  }

}