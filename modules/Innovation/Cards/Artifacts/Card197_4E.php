<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;

class Card197_4E extends AbstractCard
{
  // Crusader Rabbit (4th edition):
  //   - I COMPEL you to transfer the two bottom cards of each color which has a top card with a
  //     demand effect to my score pile!
  //   - If you have a top card on your board with a demand effect, score it, and draw a [10].

  public function initialExecution()
  {
    if (self::isCompel()) {
      foreach (self::getTopCards() as $card) {
        if ($card['has_demand'] === true) {
          self::transferToScorePile(self::getBottomCardOfColor($card['color']));
          self::transferToScorePile(self::getBottomCardOfColor($card['color']));
        }
      }
    } else {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): array
  {
    return [
      'location_from'     => Locations::BOARD,
      'score_keyword'     => true,
      'has_demand_effect' => true,
    ];
  }

}