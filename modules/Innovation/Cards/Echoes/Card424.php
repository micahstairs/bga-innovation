<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\CardIds;
use Innovation\Enums\Colors;

class Card424 extends AbstractCard
{

  // Rock
  //   - I DEMAND you transfer your top green card to my hand! If Scissors is your new top
  //     green card, I win!
  //   - You may score a top card on your board. If Paper is your top green card, you win.

  public function getInteractionOptions(): array
  {
    if (self::isDemand()) {
      return self::youMust()->withColor(Colors::GREEN)->fromYourBoard()->toMyHand()->build();
    } else {
      return self::youMay()->score()->fromYourBoard()->build();
    }
  }

  public function afterInteraction()
  {
    $topGreenCard = self::getTopCardOfColor(Colors::GREEN);
    if (!$topGreenCard) {
      return;
    }
    $cardId = self::getId($topGreenCard);
    if (self::isDemand() && $cardId == CardIds::SCISSORS) {
      self::win(self::getLauncherId());
    } else if (self::isFirstNonDemand() && $cardId == CardIds::PAPER) {
      self::win();
    }
  }

}