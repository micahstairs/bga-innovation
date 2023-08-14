<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\Card;

class Card431 extends Card
{

  // GPS
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
      $numCards = self::intDivision(self::getIconCount($this->game::EFFICIENCY), 2);
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
        'splay_direction' => $this->game::UP,
        'color'           => [$this->game::GREEN],
      ];
    } else {
      return [
        'can_pass'      => true,
        'n_min'         => 1,
        'n_max'         => 'all',
        'location_from' => 'hand',
        'tuck_keyword'  => true,
        'with_icon'     => $this->game::EFFICIENCY,
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