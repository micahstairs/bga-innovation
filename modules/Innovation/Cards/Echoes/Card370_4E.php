<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Directions;
use Innovation\Enums\Locations;

class Card370_4E extends AbstractCard
{

  // Globe (4th edition):
  //   - You may return all cards from your hand. If you return three [4], splay any color on
  //     your board right, and draw and foreshadow a [6], a [7], and then an [8].
  //   - If Globe was foreseen, foreshadow a top card from any board.

  public function initialExecution()
  {
    if (self::isFirstNonDemand()) {
      self::setMaxSteps(1);
    } else if (self::wasForeseen()) {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstNonDemand()) {
      if (self::isFirstInteraction()) {
        self::setAuxiliaryValue(0); // Track number of [4] returned
        return [
          'can_pass'       => true,
          'n'              => 'all',
          'location_from'  => Locations::HAND,
          'return_keyword' => true,
        ];
      } else {
        return ['splay_direction' => Directions::RIGHT];
      }
    } else {
      return [
        'location_from'      => Locations::BOARD,
        'owner_from'         => 'any player',
        'foreshadow_keyword' => true,
      ];
    }
  }

  public function handleCardChoice(array $card) {
    if (self::isFirstNonDemand()) {
      self::incrementAuxiliaryValue();
    }
  }

  public function afterInteraction()
  {
    if (self::isFirstInteraction() && self::getAuxiliaryValue() >= 3) {
      self::setMaxSteps(2);
    } else if (self::isSecondInteraction()) {
      self::drawAndForeshadow(6);
      self::drawAndForeshadow(7);
      self::drawAndForeshadow(8);
    }
  }

  public function handleAbortedInteraction()
  {
    if (self::isSecondInteraction()) {
      self::drawAndForeshadow(6);
      self::drawAndForeshadow(7);
      self::drawAndForeshadow(8);
    }
  }

}