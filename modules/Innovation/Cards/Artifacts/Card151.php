<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\Card;
use Innovation\Enums\Icons;
use Innovation\Enums\Locations;

class Card151 extends Card
{

  // Moses
  // - 3rd edition:
  //   - I COMPEL you to transfer all top cards with a [PROSPERITY] from your board to my score pile!
  //   - Score a top card on your board with a [PROSPERITY].
  // - 4th edition:
  //   - I COMPEL you to transfer a top card with a [PROSPERITY] of each color from your board to my score pile!
  //   - Score a top card on your board with a [PROSPERITY].


  public function initialExecution()
  {
    if (self::isCompel()) {
      foreach (self::getTopCards() as $card) {
        if (self::hasIcon($card, Icons::PROSPERITY)) {
          self::transferToScorePile($card, self::getLauncherId());
        }
      }
    } else {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): array
  {
    return [
      'location_from' => Locations::BOARD,
      'location_to'   => Locations::SCORE,
      'score_keyword' => true,
      'with_icon'     => Icons::PROSPERITY,
    ];
  }

}