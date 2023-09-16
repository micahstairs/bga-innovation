<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\Card;
use Innovation\Enums\Locations;

class Card124 extends Card
{

  // Tale of the Shipwrecked Sailor
  // - 3rd edition:
  //   - Choose a color. Draw a [1]. Meld a card of the chosen color from your hand. If you do,
  //     splay that color left.
  // - 4th edition:
  //   - Choose a color. Draw a [1]. Meld a card of the chosen color from your hand. If you do,
  //     splay that color left, and junk an available achievement of value equal to the value of the melded card.

  public function initialExecution()
  {
    self::setMaxSteps(2);
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      return ['choose_color' => true];
    } else if (self::isSecondInteraction()) {
      self::draw(1);
      return [
        'location_from' => 'hand',
        'meld_keyword'  => true,
        'color'         => [self::getAuxiliaryValue()],
      ];
    } else {
      return [
        'location_from' => Locations::AVAILABLE_ACHIEVEMENTS,
        'junk_keyword'  => true,
        'age'           => self::getLastSelectedAge(),
      ];
    }
  }

  public function handleColorChoice(int $color)
  {
    self::setAuxiliaryValue($color); // Track color to meld
  }

  public function afterInteraction()
  {
    if (self::isSecondInteraction()) {
      if (self::getNumChosen() > 0) {
        self::splayLeft(self::getLastSelectedColor());
        if (self::isFourthEdition()) {
          self::setMaxSteps(3);
        }
      } else {
        self::revealHand(); // Prove that no cards of the chosen color were in hand
      }
    }
  }

}