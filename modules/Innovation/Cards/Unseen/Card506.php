<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;
use Innovation\Cards\ExecutionState;

class Card506 extends Card
{

  // Secret Secretorum:
  //   - Return five cards from your hand and/or score pile. Draw two cards of value equal to the
  //     number of different colors of cards you return. Meld one of the drawn cards and score the
  //     other.

  public function initialExecution(ExecutionState $state)
  {
    self::setMaxSteps(2);
  }

  public function getInteractionOptions(Executionstate $state): array
  {
    if (self::getCurrentStep() == 1) {
      $this->game->setAuxiliaryValueFromArray([]);
      return [
        'location_from' => 'hand,score',
        'location_to'   => 'revealed,deck',
        'n'             => 5,
      ];
    } else {
      return [
        'location_from'                   => 'hand',
        'meld_keyword'                    => true,
        'card_ids_are_in_auxiliary_array' => true,
      ];
    }
  }

  public function handleCardChoice(Executionstate $state, int $cardId)
  {
    if (self::getCurrentStep() == 1) {
      // Keep track of the colors of the cards being returned
      $card = self::getCard($cardId);
      $colors = $this->game->getAuxiliaryValueAsArray();
      $colors[] = $card['color'];
      $this->game->setAuxiliaryValueFromArray(array_unique($colors));
    } else {
      // Score the other card
      $cardIds = $this->game->getAuxiliaryArray();
      $cardIdToScore = $cardId == $cardIds[0] ? $cardIds[1] : $cardIds[0];
      self::score(self::getCard($cardIdToScore));
    }
  }

  public function afterInteraction(Executionstate $state)
  {
    if (self::getCurrentStep() == 1) {
      // Draw two cards and store the IDs in the auxiliary array
      $numColors = count($this->game->getAuxiliaryValueAsArray());
      $card1 = self::draw($numColors);
      $card2 = self::draw($numColors);
      $this->game->setAuxiliaryArray([$card1['id'], $card2['id']]);
    }
  }
}