<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;
use Innovation\Enums\Icons;

class Card104 extends AbstractCard
{
  //
  // - 3rd edition:
  //   - You may splay your green cards up.
  //   - Draw and score a [10].
  //   - Draw and meld a [10] for every two [EFFICIENCY] on your board.
  // - 4th edition:
  //   - You may splay your green cards up.
  //   - Draw and score a [10].
  //   - Draw and meld two [10].

  public function initialExecution()
  {
    if (self::isFirstNonDemand()) {
      self::setMaxSteps(1);
    } else if (self::isSecondNonDemand()) {
      self::drawAndScore(10);
    } else {
      $numCardsToDrawAndMeld = 2;
      if (self::isFirstOrThirdEdition()) {
        $numCardsToDrawAndMeld = $this->game->intDivision(self::getStandardIconCount(Icons::EFFICIENCY), 2);
      }
      for ($i = 0; $i < $numCardsToDrawAndMeld; $i++) {
        self::drawAndMeld(10);
      }
    }
  }

  public function getInteractionOptions(): array
  {
    return self::youMay()->splayUp()->withColor(Colors::GREEN)->build();
  }

}