<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\CardIds;
use Innovation\Enums\Colors;

class Card424 extends AbstractCard
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
        'color'         => [Colors::GREEN],
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
    $topGreenCard = self::getTopCardOfColor(Colors::GREEN);
    if (!$topGreenCard) {
      return;
    }
    if (self::isDemand() && $topGreenCard['id'] == CardIds::SCISSORS) {
      self::win(self::getLauncherId());
    } else if (self::isFirstNonDemand() && $topGreenCard['id'] == CardIds::PAPER) {
      self::win();
    }
  }

}