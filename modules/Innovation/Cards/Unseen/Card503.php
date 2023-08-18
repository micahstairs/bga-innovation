<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;

class Card503 extends Card
{

  // Propaganda:
  //   - I DEMAND you meld a card of the color of my choice from your hand! If you do, transfer
  //     the card beneath it to my board!
  //   - Meld a card from your hand.

  public function initialExecution()
  {
    self::setMaxSteps(self::isDemand() ? 2 : 1);
  }

  public function getInteractionOptions(): array
  {
    if (self::isDemand()) {
      if (self::isFirstInteraction()) {
        return [
          'player_id'    => self::getLauncherId(),
          'choose_color' => true,
        ];
      } else {
        return [
          'location_from' => 'hand',
          'meld_keyword'  => true,
          'color'         => [self::getAuxiliaryValue()],
        ];
      }
    } else {
      return [
        'location_from' => 'hand',
        'meld_keyword'  => true,
      ];
    }
  }

  public function handleSpecialChoice(int $color)
  {
    self::setAuxiliaryValue($color); // Track color to meld
  }

  public function afterInteraction()
  {
    if (self::isDemand() && self::isSecondInteraction()) {
      if (self::getNumChosen() === 0) {
        // Prove that there were no cards of that color in hand
        self::revealHand();
      } else {
        $stack = self::getCardsKeyedByColor('board')[self::getAuxiliaryValue()];
        if (count($stack) >= 2) {
          self::transferToBoard($stack[count($stack - 2)], self::getLauncherId());
        }
      }
    }
  }

}