<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;
use Innovation\Enums\Directions;

class Card553 extends AbstractCard
{

  // Fortune Cookie
  //   - If you have exactly seven of any icon on your board, draw and score a [7]; exactly eight,
  //     splay your green or purple cards right and draw an [8]; exactly nine, draw a [9].

  public function initialExecution()
  {
    $iconCounts = array_values(self::getAllIconCounts());
    $hadSeven = in_array(7, $iconCounts);
    $hadEight = in_array(8, $iconCounts);
    $hadNine = in_array(9, $iconCounts);

    if ($hadSeven) {
      self::drawAndScore(7);
    } else if ($hadEight) {
      self::setMaxSteps(1);
      self::setAuxiliaryValue($hadNine ? 1 : 0);
    } else if ($hadNine) {
      self::draw(9);
    }
  }

  public function getInteractionOptions(): array
  {
    return [
      'splay_direction' => Directions::RIGHT,
      'color'           => [Colors::GREEN, Colors::PURPLE],
    ];
  }

  public function afterInteraction()
  {
    self::draw(8);
    if (self::getAuxiliaryValue() === 1) {
      self::draw(9);
    }
  }

}