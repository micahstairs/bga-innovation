<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Icons;
use Innovation\Enums\Locations;

class Card72 extends AbstractCard
{

  // Rocketry:
  // - 3rd edition:
  //   - Return a card in any opponent's score pile for every two [EFFICIENCY] on your board.
  // - 4th edition:
  //   - Return a card in any opponent's score pile for every color on your board with [EFFICIENCY].

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      return [
        'owner_from'     => 'any opponent',
        'location_from'  => Locations::SCORE,
        'return_keyword' => true,
        'n'              => $this->game->intDivision(self::countColorsWithIcon(Icons::EFFICIENCY), 2),
      ];
    } else {
      return [
        'owner_from'     => 'any opponent',
        'location_from'  => Locations::SCORE,
        'return_keyword' => true,
        'n'              => self::countColorsWithIcon(Icons::EFFICIENCY),
      ];
    }
  }

}