<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;

class Card545 extends Card
{

  // Counterintelligence:
  //   - I demand you tuck a top card on your board with a [CONCEPT]! If you do, transfer your top
  //     card of color matching the tucked card to my board, and draw a [7]!
  //   - Draw an [8].

  public function initialExecution()
  {
    if (self::isDemand()) {
      self::setMaxSteps(1);
    } else {
      self::draw(8);
    }
  }

  public function getInteractionOptions(): array
  {
    return [
      'location_from' => 'board',
      'tuck_keyword'  => true,
      'with_icon'     => $this->game::CONCEPT,
    ];
  }

  public function afterInteraction()
  {
    if (self::getNumChosen() > 0) {
      self::transferToBoard(self::getTopCardOfColor(self::getLastSelectedColor()), self::getLauncherId());
      self::draw(7);
    }
  }

}