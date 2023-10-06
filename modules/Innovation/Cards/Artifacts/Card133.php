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
      self::setMaxSteps(2);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      return ['choose_player' => true];
    } else {
      return [
        'location_from' => Locations::AVAILABLE_ACHIEVEMENTS,
        'junk_keyword'  => true,
        'age'           => self::getAuxiliaryValue(),
      ];
    }
  }

  public function handlePlayerChoice(int $playerId)
  {
    $maxValue = self::getMaxValue(self::getTopCards($playerId));
    self::setAuxiliaryValue($maxValue); // Track value to junk
  }

}