<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;

class Card543 extends Card
{

  // Illuminati:
  //   - Reveal a card in your hand. Splay the card's color on your board right. Safeguard the top
  //     card on your board of that color. Safeguard an available achievement of value one higher
  //     than the secret.

  public function initialExecution()
  {
    if ($this->game->countCardsInHand(self::getPlayerId()) > 0) {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      return [
        'location_from' => 'hand',
        'location_to'   => 'revealed,hand',
      ];
    } else {
      return [
        'owner_from'    => 0,
        'location_from' => 'achievements',
        'location_to'   => 'safe',
        'age'           => self::getAuxiliaryValue(),
      ];
    }
  }

  public function afterInteraction()
  {
    if (self::getNumChosen() > 0 && self::isFirstInteraction()) {
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