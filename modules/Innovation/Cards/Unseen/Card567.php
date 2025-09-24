<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\AbstractCard;

class Card567 extends AbstractCard
{

  // Iron Curtain:
  //   - Unsplay each splayed color on your board. For each color you unsplay, return your top
  //     card of that color and safeguard an available standard achievement.

  public function initialExecution()
  {
    $colors = [];
    foreach (self::getTopCards() as $card) {
      if ($card['splay_direction'] > 0) {
        $colors[] = self::getColor($card);
        self::unsplay(self::getColor($card));
      }
    }
    if (count($colors) > 0) {
      self::setAuxiliaryArray($colors);
      self::setMaxSteps(2);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      return self::youMust()->return()->all()->withColor(self::getAuxiliaryArray())->fromYourBoard()->build();
    } else {
      $numCards = count(self::getAuxiliaryArray());
      return self::youMust()->safeguard()->exactly($numCards)->build();
    }
  }

}