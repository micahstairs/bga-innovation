<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;

class Card138 extends AbstractCard
{

  // Mjölnir Amulet
  //   - I COMPEL you to choose a top card on your board! Transfer all cards of that card's color
  //     from your board to my score pile!

  public function getInteractionOptions(): array
  {
    return self::youMust()->chooseCardFrom(Locations::BOARD)->build();
  }

  public function handleCardChoice(array $card)
  {
    foreach (array_reverse(self::getStack(self::getColor($card))) as $card) {
      self::transferToScorePile($card, self::getLauncherId());
    }
  }

  public function compelMightBeEffective(): bool
  {
    return self::countCards(Locations::BOARD) > 0;
  }

}