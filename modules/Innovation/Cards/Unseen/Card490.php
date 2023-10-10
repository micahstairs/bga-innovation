<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;
use Innovation\Enums\Locations;

class Card490 extends AbstractCard
{
  // Tomb
  //   - Safeguard an available achievement of value 1 plus the number of achievements you have.
  //   - You may transfer the lowest available achievement to your hand. If you do, return all
  //     yellow cards and all blue cards on your board.

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstNonDemand()) {
      return [
        'safeguard_keyword' => true,
        'age'               => self::countCards(Locations::ACHIEVEMENTS) + 1,
      ];
    } else if (self::isFirstInteraction()) {
      return [
        'location_from' => Locations::AVAILABLE_ACHIEVEMENTS,
        'location_to'   => Locations::HAND,
        'age'           => self::getMinValue(self::getAvailableStandardAchievements()),
      ];
    } else {
      return [
        'n'              => 'all',
        'location_from'  => Locations::PILE,
        'return_keyword' => true,
        'color'          => [Colors::YELLOW, Colors::BLUE],
      ];
    }
  }

  public function handleCardChoice(array $card)
  {
    if (self::isSecondNonDemand() && self::isFirstInteraction()) {
      self::setMaxSteps(2);
    }
  }

}