<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Icons;
use Innovation\Enums\Locations;

class Card523 extends AbstractCard
{

  // Confession:
  //   - Return a top card with [AUTHORITY] of each color from your board. If you return none, meld
  //     a card from your score pile, then draw and score a [4].
  //   - Draw a [4] for each [4] in your score pile.

  public function initialExecution()
  {
    if (self::getEffectNumber() === 1) {
      self::setMaxSteps(1);
    } else {
      $numFours = self::countCardsKeyedByValue(Locations::SCORE)[4];
      for ($i = 0; $i < $numFours; $i++) {
        self::draw(4);
      }
    }

  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      return self::youMust()->return()->all()->withIcon(Icons::AUTHORITY)->fromYourBoard()->build();
    } else {
      return self::youMust()->meld()->fromYourScore()->build();
    }
  }

  public function afterInteraction()
  {
    if (self::isFirstInteraction()) {
      if (self::getNumChosen() == 0) {
        self::setMaxSteps(2);
      }
    } else {
      self::drawAndScore(4);
    }
  }

}