<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;

class Card177 extends AbstractCard
{
  // Submarine H. L. Hunley
  // - 3rd edition:
  //   - I COMPEL you to draw and meld a [7]! Reveal the bottom card on your board of the melded
  //     card's color! If the revealed card is a ${age_1}, return all cards of its color from your
  //     board!
  // - 4th edition:
  //   - I COMPEL you to draw and meld a [7]! Reveal the bottom card on your board of the melded
  //     card's color! If the revealed card is even-valued, return all cards of its color from your
  //     board!

  public function initialExecution()
  {
    $meldedCard = self::drawAndMeld(7);
    $bottomCard = self::getBottomCardOfColor($meldedCard['color']);
    $this->game->revealCardWithoutMoving(self::getPlayerId(), $bottomCard, /*mentionLocation=*/false);

    if (self::isFirstOrThirdEdition()) {
      $mustReturnCards = $bottomCard['faceup_age'] === 1;
    } else {
      $mustReturnCards = $bottomCard['faceup_age'] % 2 === 0;
    }

    if ($mustReturnCards) {
      self::setAuxiliaryValue($meldedCard['color']); // Track color to return
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): array
  {
    return [
      'n'              => 'all',
      'location_from'  => Locations::PILE,
      'color'          => [self::getAuxiliaryValue()],
      'return_keyword' => true,
    ];
  }

}