<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Icons;
use Innovation\Enums\Locations;

class Card42_3E extends AbstractCard
{
  // Perspective (3rd edition):
  //   - You may return a card from your hand. If you do, score a card from your hand for every
  //     two [CONCEPT] on your board.

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      return [
        'can_pass'       => true,
        'location_from'  => Locations::HAND,
        'return_keyword' => true,
      ];
    } else {
      return [
        'can_pass'      => true,
        'n'             => $this->game->intDivision(self::getStandardIconCount(Icons::CONCEPT), 2),
        'location_from' => Locations::HAND,
        'score_keyword' => true,
      ];
    }
  }

  public function handleCardChoice(array $card)
  {
    if (self::isFirstInteraction()) {
      self::setMaxSteps(2);
    }
  }

}