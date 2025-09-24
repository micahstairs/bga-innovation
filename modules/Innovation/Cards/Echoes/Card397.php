<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;
use Innovation\Enums\Locations;

class Card397 extends AbstractCard
{

  // Machine Gun
  // - 3rd edition
  //   - ECHO: If you have five top cards, draw and score a [7].
  //   - I DEMAND you transfer all of your top cards with a bonus to my score pile! If you
  //     transfered any, draw a [7]!
  //   - Return all your top non-red cards.
  // - 4th edition
  //   - ECHO: If you have five top cards, draw and score a [7].
  //   - I DEMAND you transfer a top card with a bonus of each color from your board to my score
  //     pile! If you transfer any, junk four available achievements, and draw a [7]!
  //   - Return your top card of each non-red color.

  public function initialExecution()
  {
    if (self::isEcho()) {
      if (count(self::getTopCards()) === 5) {
        self::drawAndScore(7);
      }
    } else {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isDemand()) {
      if (self::isFirstInteraction()) {
        return self::youMust()->all()->withBonus()->fromYourBoard()->toMyScore()->build();
      } else {
        return self::youMust()->junk()->exactly(4)->fromAvailableAchievements()->build();
      }
    } else {
      return self::youMust()->return()->all()->non(Colors::RED)->fromYourBoard()->build();
    }
  }

  public function afterInteraction()
  {
    if (self::isDemand()) {
      if (self::isFirstInteraction()) {
        if (self::getNumChosen() > 0) {
          if (self::isFirstOrThirdEdition()) {
            self::draw(7);
          } else {
            self::setMaxSteps(2);
          }
        }
      } else {
        self::draw(7);
      }
    }
  }

}