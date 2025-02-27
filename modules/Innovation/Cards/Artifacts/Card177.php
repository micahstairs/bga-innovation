<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;

class Card177 extends AbstractCard
{
  // Submarine H. L. Hunley
  // - 3rd edition:
  //   - I COMPEL you to draw and meld a [7]! Reveal the bottom card on your board of the melded
  //     card's color! If the revealed card is a ${age_1}, return all cards of its color from your
  //     board!
  // - 4th edition:
  //   - I COMPEL you to draw and meld a [7]! Reveal your bottom card of the melded card's color! If
  //     the revealed card is even-valued, return all cards of its color from your board!

  public function initialExecution()
  {
    $meldedCard = self::drawAndMeld(7);
    $bottomCard = self::getBottomCardOfColor(self::getColor($meldedCard));
    $this->game->revealCardWithoutMoving(self::getPlayerId(), $bottomCard, /*mentionLocation=*/ false);

    $mustReturnCards = self::isFirstOrThirdEdition()
      ? self::getFaceupValue($bottomCard) === 1
      : self::getFaceupValue($bottomCard) % 2 === 0;

    if ($mustReturnCards) {
      self::setAuxiliaryValue(self::getColor($meldedCard)); // Track color to return
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): array
  {
    return self::youMust()->return()->all()->fromYourStack(self::getAuxiliaryValue())->build();
  }

}