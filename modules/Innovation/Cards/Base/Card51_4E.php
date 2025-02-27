<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;
use Innovation\Enums\Locations;

class Card51_4E extends AbstractCard
{
  // Statistics (4th edition):
  //   - I DEMAND you transfer all the cards of the value of my choice in your score pile to your hand!
  //   - You may splay your yellow cards right.

  public function getInteractionOptions(): array
  {
    if (self::isDemand()) {
      return self::youMust()->chooseValue()->ofMyChoice()->build();
    } else {
      return self::youMay()->splayRight()->withColor(Colors::YELLOW)->build();
    }
  }

  public function handleValueChoice(int $value)
  {
    foreach (self::getCardsKeyedByValue(Locations::SCORE)[$value] as $card) {
      self::transferToHand($card);
    }
  }

  public function demandMightBeEffective(): bool
  {
    return self::hasCards(Locations::SCORE);
  }

  public function nonDemandsMightBeEffective(): bool
  {
    return self::canSplay(Colors::YELLOW);
  }

}