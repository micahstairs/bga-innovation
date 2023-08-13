<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\Card;

class Card397 extends Card
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
      return [
        'location_from' => 'board',
        'owner_to'      => self::getLauncherId(),
        'location_to'   => 'score',
        'with_bonus'    => true,
      ];
    } else {
      return [
        'n'              => 4,
        'location_from'  => 'achievements',
        'owner_from' => 0,
        'junk_keyword' => true,
      ];
    }
    } else {
      return [
        'n'              => 'all',
        'location_from'  => 'board',
        'return_keyword' => true,
        'color'          => self::getAllColorsOtherThan($this->game::RED),
      ];
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