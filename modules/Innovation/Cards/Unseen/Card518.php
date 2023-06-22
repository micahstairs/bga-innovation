<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;
use Innovation\Cards\ExecutionState;

class Card518 extends Card
{

  // Spanish Inquisition:
  //   - I DEMAND you return all but the highest cards from your hand and all but the highest cards
  //     from your score pile!
  //   - If Spanish Inquisition is a top card on your board, return all red cards from your board.

  public function initialExecution(ExecutionState $state)
  {
    if (self::isDemand()) {
      $cardIds = [];
      $maxAgeInHand = $this->game->getMaxAgeInHand(self::getPlayerId());
      foreach ($this->game->getCardsInHand(self::getPlayerId()) as $card) {
        if ($card['age'] < $maxAgeInHand) {
          $cardIds[] = $card['id'];
        }
      }
      $maxAgeInScore = $this->game->getMaxAgeInScore(self::getPlayerId());
      foreach ($this->game->getCardsInScorePile(self::getPlayerId()) as $card) {
        if ($card['age'] < $maxAgeInScore) {
          $cardIds[] = $card['id'];
        }
      }
      if (count($cardIds) > 0) {
        self::setMaxSteps(2);
        $this->game->setAuxiliaryArray($cardIds);
      }
    } else {
      $topCard = self::getTopCardOfColor($this->game::RED);
      if ($topCard !== null && $topCard['id'] == self::getCardIdFromClassName()) {
        self::setMaxSteps(1);
      }
    }
  }

  public function getInteractionOptions(Executionstate $state): array
  {
    if (self::isDemand()) {
      return [
        'n'                               => 'all',
        'location_from'                   => 'hand,score',
        'location_to'                     => 'deck',
        'card_ids_are_in_auxiliary_array' => true,
      ];
    } else {
      return [
        'n'             => 'all',
        'location_from' => 'pile',
        'location_to'   => 'deck',
        'color'         => [$this->game::RED],
      ];
    }
  }

  public function handleSpecialChoice(Executionstate $state, int $choice): void
  {
    self::setAuxiliaryValue($choice);
  }

  public function afterInteraction(Executionstate $state)
  {
    if (self::isDemand() && self::getNumChosen() > 0) {
      // TODO(4E): This looks wrong.
      self::transferToBoard(self::getTopCardOfColor(self::getAuxiliaryValue()), self::getLauncherId());
    }
  }

}