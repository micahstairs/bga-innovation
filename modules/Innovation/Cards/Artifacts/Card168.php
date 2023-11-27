<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Icons;
use Innovation\Enums\Locations;

class Card168 extends AbstractCard
{
  // U.S. Declaration of Independence
  //   - I COMPEL you to transfer the highest card in your hand to my hand, the highest card in
  //     your score pile to my score pile, and the highest top card with [INDUSTRY] from your
  //     board to my board!

  public function initialExecution()
  {
    self::setMaxSteps(3);
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      return [
        'owner_from'    => self::getPlayerId(),
        'location_from' => Locations::HAND,
        'owner_to'      => self::getLauncherId(),
        'location_to'   => Locations::HAND,
        'age'           => self::getMaxValueInLocation(Locations::HAND),
      ];
    } else if (self::isSecondInteraction()) {
      return [
        'owner_from'    => self::getPlayerId(),
        'location_from' => Locations::SCORE,
        'owner_to'      => self::getLauncherId(),
        'location_to'   => Locations::SCORE,
        'age'           => self::getMaxValueInLocation(Locations::SCORE),
      ];
    } else {
      return [
        'owner_from'    => self::getPlayerId(),
        'location_from' => Locations::BOARD,
        'owner_to'      => self::getLauncherId(),
        'location_to'   => Locations::BOARD,
        'age'           => $this->game->getMaxAgeOnBoardTopCardsWithIcon(self::getPlayerId(), Icons::INDUSTRY),
        'with_icon'     => Icons::INDUSTRY,
      ];
    }
  }

}