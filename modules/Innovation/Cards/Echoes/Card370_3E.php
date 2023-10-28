<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;

class Card370_3E extends AbstractCard
{

  // Globe (3rd edition):
  //   - You may return up to three cards from hand of the same color. If you return one, splay
  //     any color left; two, right; three, up. If you returned at least one card, draw and
  //     foreshadow a [6].

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      return [
        'can_pass'     => true,
        'choose_color' => true,
        'color'        => self::getUniqueColors(Locations::HAND),
      ];
    } else if (self::isSecondInteraction()) {
      return [
        'can_pass'      => true,
        'n_min'         => 1,
        'n_max'         => 3,
        'location_from' => Locations::HAND,
        'location_to'   => Locations::REVEALED_THEN_DECK,
        'color'         => [self::getAuxiliaryValue()],
      ];
    } else {
      return ['splay_direction' => self::getAuxiliaryValue()];
    }
  }

  public function handleColorChoice(int $color)
  {
    self::setAuxiliaryValue($color); // Track color being returned
    self::setMaxSteps(2);
  }

  public function afterInteraction()
  {
    if (self::isSecondInteraction() && self::getNumChosen() > 0) {
      self::setAuxiliaryValue(self::getNumChosen()); // Repurpose auxiliary value to store the number of cards returned
      self::setMaxSteps(3);
    } else if (self::isThirdInteraction() && self::getAuxiliaryValue() > 0) {
      self::drawAndForeshadow(6);
    }
  }

  public function handleAbortedInteraction()
  {
    if (self::isThirdInteraction() && self::getAuxiliaryValue() > 0) {
      self::drawAndForeshadow(6);
    }
  }

}