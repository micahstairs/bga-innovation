<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;
use Innovation\Enums\Directions;
use Innovation\Enums\Icons;

class Card431 extends AbstractCard
{

  // Cell Phone
  // - 3rd edition 
  //   - Draw a [10] for every two [EFFICIENCY] on your board.
  //   - You may splay your green cards up.
  //   - You may tuck any number of cards with a [EFFICIENCY] from your hand, splaying up each
  //     color you tucked into.
  // - 4th edition
  //   - ECHO: Draw and foreshadow an [11].
  //   - Draw a [10] for every two [EFFICIENCY] on your board.
  //   - You may splay your green cards up.
  //   - You may tuck any number of cards with a [EFFICIENCY] from your hand, splaying up each
  //     color into which you tuck.

  public function initialExecution()
  {
    if (self::isEcho()) {
      self::drawAndForeshadow(11);
    } else if (self::isFirstNonDemand()) {
      $numCards = $this->game->intDivision(self::getStandardIconCount(Icons::EFFICIENCY), 2);
      for ($i = 0; $i < $numCards; $i++) {
        self::draw(10);
      }
    } else {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isSecondNonDemand()) {
      return [
        'can_pass'        => true,
        'splay_direction' => Directions::UP,
        'color'           => [Colors::GREEN],
      ];
    } else {
      return [
        'can_pass'      => true,
        'n_min'         => 1,
        'n_max'         => 'all',
        'location_from' => 'hand',
        'tuck_keyword'  => true,
        'with_icon'     => Icons::EFFICIENCY,
      ];
    }
  }

  public function handleCardChoice(array $card)
  {
    if (self::isThirdNonDemand()) {
      self::splayUp($card['color']);
    }
  }

}