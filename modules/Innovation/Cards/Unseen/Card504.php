<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;
use Innovation\Enums\Icons;
use Innovation\Enums\Locations;
use Innovation\Utils\Arrays;

class Card504 extends AbstractCard
{
  // Steganography
  //   - You may splay left a color on your board with a visible [CONCEPT]. If you do, safeguard an
  //     available achievement of value equal to the number of cards of that color on your board.
  //     Otherwise, draw and tuck a [3].

  public function initialExecution()
  {
    $colors = [];
    foreach (Colors::ALL as $color) {
      if (self::getIconCountInStack($color, Icons::CONCEPT) > 0) {
        $colors[] = $color;
      }
    }
    if (count($colors) > 0) {
      self::setMaxSteps(1);
      self::setAuxiliaryValue(Arrays::encode($colors));
    } else {
      self::drawAndTuck(3);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      return [
        'can_pass'        => true,
        'splay_direction' => Directions::LEFT,
        'color'           => Arrays::decode(self::getAuxiliaryValue()),
      ];
    } else {
      return [
        'safeguard_keyword' => true,
        'age'               => self::countCardsKeyedByColor(Locations::BOARD)[self::getLastSelectedColor()],
      ];
    }
  }

  public function afterInteraction()
  {
    if (self::isFirstInteraction()) {
      if (self::getNumChosen() === 1) {
        self::setMaxSteps(2);
      } else {
        self::drawAndTuck(3);
      }
    }
  }

}