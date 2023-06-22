<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;
use Innovation\Cards\ExecutionState;
use SebastianBergmann\Type\VoidType;

class Card543 extends Card
{

  // Illuminati:
  //   - Reveal a card in your hand. Splay the card's color on your board right. Safeguard the top
  //     card on your board of that color. Safeguard an available achievement of value one higher
  //     than the secret.

  public function initialExecution(ExecutionState $state)
  {
    if ($this->game->countCardsInHand($state->getPlayerId()) > 0) {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(Executionstate $state): array
  {
    if ($state->getCurrentStep() == 1) {
      return [
        'location_from' => 'hand',
        'location_to'   => 'revealed,hand',
      ];
    } else {
      return [
        'owner_from'    => 0,
        'location_from' => 'achievements',
        'location_to'   => 'safe',
        'age'           => $this->game->getAuxiliaryValue(),
      ];
    }
  }

  public function afterInteraction(Executionstate $state)
  {
    if (self::getNumChosen() > 0 && self::getCurrentStep() == 1) {
      $color = self::getLastSelectedColor();
      self::splayRight($color);
      $topCard = self::getTopCardOfColor($color);
      if ($topCard) {
        self::safeguard($topCard);
        self::setMaxSteps(2);
        self::setAuxiliaryValue($topCard['age'] + 1);
      }
    }
  }

}