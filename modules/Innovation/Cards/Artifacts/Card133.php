<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\CardTypes;
use Innovation\Enums\Locations;

class Card133 extends AbstractCard
{

  // Dead Sea Scrolls
  // - 3rd edition:
  //   - Draw an Artifact of value equal to the value of your highest top card.
  // - 4th edition:
  //   - Draw an Artifact of value equal to the value of your highest top card.
  //   - Choose a player. Junk an available achievement of value equal to the highest top card on
  //    that player's board.

  public function initialExecution()
  {
    if (self::isFirstNonDemand()) {
      self::drawType(self::getMaxValue(self::getTopCards()), CardTypes::ARTIFACTS);
    } else if (self::isSecondNonDemand()) {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): array
  {
    $values = [];
    foreach (self::getPlayerIds() as $playerId) {
      $values[] = self::getMaxValue(self::getTopCards($playerId));
    }
    return [
      'location_from' => Locations::AVAILABLE_ACHIEVEMENTS,
      'junk_keyword'  => true,
      'age'           => $values,
    ];
  }

}