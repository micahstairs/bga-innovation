<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;
use Innovation\Enums\Directions;
use Innovation\Enums\Icons;
use Innovation\Enums\Locations;

class Card406_4E extends AbstractCard
{

  // X-Ray (4th edition):
  //   - ECHO: Draw and tuck an [8].
  //   - Choose a value. For every three [HEALTH] on your board, draw a card of that value.
  //     Foreshadow any number of them.
  //   - Return all cards from your hand.
  //   - You may splay your yellow cards up.

  public function initialExecution()
  {
    if (self::isEcho()) {
      self::drawAndTuck(8);
    } else if (self::isFirstNonDemand()) {
      $numCards = $this->game->intDivision(self::getStandardIconCount(Icons::HEALTH), 3);
      if ($numCards > 0) {
        self::setAuxiliaryValue($numCards); // Track number of cards to draw
        self::setMaxSteps(2);
      }
    } else if (self::isSecondNonDemand()) {
      self::setMaxSteps(1);
    } else if (self::isThirdNonDemand()) {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstNonDemand()) {
      if (self::isFirstInteraction()) {
        return ['choose_value' => true];
      } else {
        return [
          'can_pass'                        => true,
          'n_min'                           => 1,
          'n_max'                           => self::getAuxiliaryValue(),
          'location_from'                   => Locations::HAND,
          'foreshadow_keyword'              => true,
          'card_ids_are_in_auxiliary_array' => true,
        ];
      }
    } else if (self::isSecondNonDemand()) {
      return [
        'n'              => 'all',
        'location_from'  => Locations::HAND,
        'return_keyword' => true,
      ];
    } else {
      return [
        'can_pass'        => true,
        'splay_direction' => Directions::UP,
        'color'           => [Colors::YELLOW],
      ];
    }
  }

  public function handleValueChoice(int $value)
  {
    $numCards = self::getAuxiliaryValue();
    $cardIds = [];
    for ($i = 0; $i < $numCards; $i++) {
      $card = self::draw($value);
      $cardIds[] = $card['id'];
    }
    self::setAuxiliaryArray($cardIds); // Track cards which are allowed to be foreshadowed
  }

}