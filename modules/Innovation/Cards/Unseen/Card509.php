<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;
use Innovation\Cards\ExecutionState;

class Card509 extends Card {

  // Cliffhanger:
  //   - Reveal a [4] in your safe. If it is: green, tuck it; purple, meld it; red, achieve it
  //     regardless of eligibility; yellow, score it; blue, draw a [5]. If you cannot, safeguard
  //     the top card of the [4] deck.

  public function initialExecution(ExecutionState $state) {
    $state->setMaxSteps(1);
  }

  public function getInteractionOptions(Executionstate $state): Array {
    return [
      'location_from' => 'safe',
      'location_to' => 'revealed',                
      'age' => 4,
    ];
  }

  public function handleCardChoice(Executionstate $state, int $cardId)
  {
    $card = $this->game->getCardInfo($cardId);
    $playerId = $state->getPlayerId();
    switch ($card['color']) {
      case $this->game::BLUE:
        $this->game->executeDraw($playerId, 5);
        $this->game->transferCardFromTo($card, $playerId, 'safe');
        break;
      case $this->game::RED:
        $this->game->transferCardFromTo($card, $playerId, 'achievements');
        break;
      case $this->game::GREEN:
        $this->game->tuckCard($card, $playerId);
        break;
      case $this->game::YELLOW:
        $this->game->scoreCard($card, $playerId);
        break;
      case $this->game::PURPLE:
        $this->game->meldCard($card, $playerId);
        break;
    }
  }

  public function afterInteraction(Executionstate $state) {
    if ($state->getNumChosen() == 0) {
      $card = $this->game->getDeckTopCard(4, $this->game::BASE);
        if ($card !== null) {
          $this->game->safeguardCard($card, $state->getPlayerId());
        }
    }
  }
}
