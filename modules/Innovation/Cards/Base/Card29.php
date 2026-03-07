<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\Colors;
use Innovation\Enums\Icons;

class Card29 extends AbstractCard
{

  // Compass:
  // - 3rd edition:
  //   - I DEMAND you transfer a top non-green card with a [HEALTH] from your board to my board, and
  //     then you transfer a top card without a [HEALTH] from my board to your board!
  // - 4th edition:
  //   - I DEMAND you transfer a top non-green card with [HEALTH] from your board to my board, and
  //     then meld a top card without [HEALTH] from my board!

  public function initialExecution()
  {
    self::setMaxSteps(2);
  }

  public function getInteractionOptions(): InteractionBuilder
  {
    if (self::isFirstInteraction()) {
      return self::youMust()->non(Colors::GREEN)->withIcon(Icons::HEALTH)->fromYourBoard()->toMine();
    } else if (self::isFirstOrThirdEdition()) {
      return self::youMust()->withoutIcon(Icons::HEALTH)->fromMyBoard()->toYours();
    } else {
      return self::youMust()->meld()->withoutIcon(Icons::HEALTH)->fromMyBoard();
    }
  }

  public function demandMightBeEffective(): bool
  {
    foreach (self::getTopCards() as $card) {
      if (!self::isGreen($card) && self::hasIcon($card, Icons::HEALTH)) {
        return true;
      }
    }
    return count(self::filterByIcon(self::getTopCards(self::getLauncherId()), Icons::HEALTH)) > 0;
  }

}