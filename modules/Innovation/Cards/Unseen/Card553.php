<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;
use Innovation\Enums\Colors;

class Card553 extends Card
{

  // Fortune Cookie
  //   - If you have exactly seven of any icon visible on your board, draw and score a [7]; exactly
  //     eight, splay your green or purple cards right and draw an [8]; exactly nine, draw a [9].

  public function initialExecution()
  {
    $iconCounts = [0, 0, 0, 0, 0, 0, 0, 0];
    for ($color = 0; $color < 5; $color++) {
      foreach (self::getStandardIconCounts() as $icon => $count) {
        $iconCounts[$icon] += $this->game->countVisibleIconsInPile(self::getPlayerId(), $icon, $color);
      }
    }

    $hadSeven = false;
    $hadEight = false;
    $hadNine = false;
    foreach ($iconCounts as $count) {
      if ($count === 7) {
        $hadSeven = true;
      } else if ($count === 8) {
        $hadEight = true;
      } else if ($count === 9) {
        $hadNine = true;
      }
    }

    if ($hadSeven) {
      self::drawAndScore(7);
    }
    if ($hadEight) {
      self::setMaxSteps(1);
      self::setAuxiliaryValue($hadNine);
    } else if ($hadNine) {
      self::draw(9);
    }
  }

  public function getInteractionOptions(): array
  {
    return [
      'splay_direction' => $this->game::RIGHT,
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