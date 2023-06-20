<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;
use Innovation\Cards\ExecutionState;
use SebastianBergmann\Type\VoidType;

class Card539 extends Card
{

  // Hiking:
  //   - Draw and reveal a [6]. If the top card on your 
  //     board of the drawn card's color has a INDUSTRY, 
  //     tuck the drawn card and draw and reveal a [7]. 
  //     If the second drawn card has a HEALTH, meld it 
  //     and draw an [8].

  public function initialExecution(ExecutionState $state)
  {
      // "Draw and reveal a [6]."
      $card = $this->game->executeDrawAndReveal($state->getPlayerId(), 6);
      $top_card = $this->game->getTopCardOnBoard($state->getPlayerId(), $card['color']);
      // If the top card on your board of the drawn card's color has a INDUSTRY
      if ($this->game->hasRessource($top_card, $this->game::INDUSTRY)) {
          $this->game->tuckCard($card, $state->getPlayerId());
          $second_card = $this->game->executeDrawAndReveal($state->getPlayerId(), 7);
          // "If the second drawn card has a HEALTH,"
          if ($this->game->hasRessource($second_card, $this->game::HEALTH)) {
              // "meld it and draw an [8].
              $this->game->meldCard($second_card, $state->getPlayerId());
              $this->game->executeDraw($state->getPlayerId(), 8);
          } else {
              $this->game->transferCardFromTo($second_card, $state->getPlayerId(), 'hand');
          }
      } else {
          $this->game->transferCardFromTo($card, $state->getPlayerId(), 'hand');
      }
  }

  public function getInteractionOptions(Executionstate $state): array
  {
  }

  public function handleCardChoice(Executionstate $state, int $cardId)
  {
  }

  public function afterInteraction(Executionstate $state)
  {
  }

}