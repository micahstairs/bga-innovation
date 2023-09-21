<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\CardIds;
use Innovation\Enums\CardTypes;
use Innovation\Enums\Colors;

class Card129 extends AbstractCard
{

  // Holy Lance
  //   - I COMPEL you to transfer a top Artifact from your board to my board!
  //   - If Holy Grail is a top card on your board, you win.

  public function initialExecution()
  {
    if (self::isCompel()) {
      self::setMaxSteps(1);
    } else if (self::isFirstNonDemand()) {
      $topYellowCard = self::getTopCardOfColor(Colors::YELLOW);
      if ($topYellowCard && $topYellowCard['id'] == CardIds::HOLY_GRAIL) {
        self::win();
      }
    }
  }

  public function getInteractionOptions(): array
  {
    return [
      'owner_from'    => self::getPlayerId(),
      'location_from' => 'board',
      'owner_to'      => self::getLauncherId(),
      'location_to'   => 'board',
      'type'          => [CardTypes::ARTIFACTS],
    ];
  }

}