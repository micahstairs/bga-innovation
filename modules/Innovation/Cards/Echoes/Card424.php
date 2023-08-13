<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\Card;

class Card424 extends Card
{

  // Rock
  //   - I DEMAND you transfer your top green card to my hand! If Scissors is your new top
  //     green card, I win!
  //   - You may score a top card on your board. If Paper is your top green card, you win.

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    if (self::isDemand()) {
      return [
        'location_from' => 'board',
        'owner_to'      => self::getLauncherId(),
        'location_to'   => 'hand',
        'color'         => [$this->game::GREEN],
      ];
    } else {
      return [
        'can_pass'      => true,
        'location_from' => 'board',
        'score_keyword' => true,
      ];
    }
  }

  public function afterInteraction()
  {
    $topGreenCard = self::getTopCardOfColor($this->game::GREEN);
    if (!$topGreenCard) {
      return;
    }
    if (self::isDemand() && $topGreenCard['id'] == 350) { // Scissors
      self::win(self::getLauncherId());
    } else if (self::isFirstNonDemand() && $topGreenCard['id'] == 30) { // Paper
      self::win();
    }
  }

}