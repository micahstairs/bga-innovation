<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;
use Innovation\Enums\Directions;
use Innovation\Enums\Locations;

class Card370_4E extends AbstractCard
{

  // Globe (4th edition):
  //   - You may return all cards from your hand. If you return blue, green, and yellow cards, draw
  //     and foreshadow a [6], [7], and [8], then splay any color on your board right.
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
        self::setAuxiliaryArray([]); // Track colors returned
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
      self::addToAuxiliaryArray($card['color']);
    }
  }

  public function afterInteraction()
  {
    if (self::isFirstInteraction()) {
      $colors = self::getAuxiliaryArray();
      if (in_array(Colors::BLUE, $colors) && in_array(Colors::GREEN, $colors) && in_array(Colors::YELLOW, $colors)) {
        self::drawAndForeshadow(6);
        self::drawAndForeshadow(7);
        self::drawAndForeshadow(8);
        self::setMaxSteps(2);
      }
    }
  }

}