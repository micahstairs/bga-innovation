<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;
use Innovation\Enums\Icons;


class Card98 extends AbstractCard
{
  // Robotics:
  // - 3rd edition:
  //   - Score your top green card. Draw and meld a [10]. Then execute each of its non-demand dogma
  //     effects. Do not share them.
  // - 4th edition:
  //   - Score your top green card. Draw and meld a [10]. If it has [INDUSTRY] or [EFFICIENCY], self-execute it.

  public function initialExecution()
  {
    self::score(self::getTopCardOfColor(Colors::GREEN));
    $card = self::drawAndMeld(10);
    if (self::isFirstOrThirdEdition() || self::hasAnyIcons($card, [Icons::INDUSTRY, Icons::EFFICIENCY])) {
      self::selfExecute($card);
    }
  }

}