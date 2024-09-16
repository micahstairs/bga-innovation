<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\CardIds;
use Innovation\Enums\Colors;

class Card52 extends AbstractCard
{
  // Steam Engine
  // - 3rd edition:
  //   - Draw and tuck two [4], then score your bottom yellow card.
  // - 4th edition:
  //   - Draw and tuck two [4]. Score your bottom yellow card. If it is Steam Engine, junk all
  //     cards in the [6] deck.

  public function initialExecution()
  {
    self::drawAndTuck(4);
    self::drawAndTuck(4);
    $card = self::score(self::getBottomCardOfColor(Colors::YELLOW));
    if (self::isFourthEdition() && self::getId($card) == CardIds::STEAM_ENGINE) {
      self::junkBaseDeck(6);
    }
  }

}