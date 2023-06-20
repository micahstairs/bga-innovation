<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;
use Innovation\Cards\ExecutionState;

class Card484 extends Card
{

  // Maze:
  //   - I DEMAND you score a card from your hand of matching color for each card in my hand! If
  //     you don't, and I have a card in my hand, exchange all cards in your hand with all cards in
  //     my score pile!

  public function initialExecution(ExecutionState $state)
  {
    $colorCounts = [0, 0, 0, 0, 0];
    foreach ($this->game->getCardsInHand($state->getLauncherId()) as $card) {
      $colorCounts[$card['color']]++;
    }
    self::setActionScopedAuxiliaryArray($colorCounts);
    $this->game->setAuxiliaryValue(0); // Used to track whether the player has scored a card yet
    $state->setMaxSteps(1);
  }

  public function getInteractionOptions(Executionstate $state): array
  {
    $cardIds = [];
    $colorCounts = self::getActionScopedAuxiliaryArray();
    foreach ($this->game->getCardsInHand($state->getPlayerId()) as $card) {
      if ($colorCounts[$card['color']] > 0) {
        $cardIds[] = $card['id'];
      }
    }
    $this->game->setAuxiliaryArray($cardIds);
    return [
      'location_from' => 'hand',
      'score_keyword' => true,
      'card_ids_are_in_auxiliary_array' => true,
      'enable_autoselection' => false, // Automating this can sometimes reveal hidden info
    ];
  }

  public function afterInteraction(Executionstate $state)
  {
    if ($state->getNumChosen() == 0) {
      $cardsInHand = $this->game->getCardsInHand($state->getPlayerId());
      if ($this->game->getAuxiliaryValue() == 0 && count($cardsInHand) > 0) {
        $cardsInScorePile = $this->game->getCardsInScorePile($state->getLauncherId());
        foreach ($cardsInHand as $card) {
          $this->game->transferCardFromTo($card, $state->getLauncherId(), 'score');
        }
        foreach ($cardsInScorePile as $card) {
          self::putInHand($card);
        }
      }
    } else {
      $color = self::getLastSelectedColor();
      $colorCounts = self::getActionScopedAuxiliaryArray();
      $colorCounts[$color]--;
      self::setActionScopedAuxiliaryArray($colorCounts);
      $this->game->setAuxiliaryValue(1);
      $state->setNextStep(1);
    }
  }

  private function getSelectableCardIds($playerId) {
    $cardIds = [];
    $colorCounts = self::getActionScopedAuxiliaryArray();
    foreach ($this->game->getCardsInHand($playerId) as $card) {
      if ($colorCounts[$card['color']] > 0) {
        $cardIds[] = $card['id'];
      }
    }
    return $cardIds;
  }
}