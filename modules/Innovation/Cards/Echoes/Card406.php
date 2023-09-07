<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\Card;
use Innovation\Enums\Colors;
use Innovation\Enums\Directions;

class Card406 extends Card
{

  // X-Ray
  // - 3rd edition
  //   - ECHO: Draw and tuck an [8].
  //   - For every three [HEALTH] on your board, draw and foreshadow a card of any value.
  //   - You may splay your yellow cards up.
  // - 4th edition
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
      $numCards = $this->game->intDivision(self::getStandardIconCount($this->game::HEALTH), 3);
      if ($numCards > 0) {
        self::setAuxiliaryValue($numCards); // Track number of cards to draw and foreshadow
        self::setMaxSteps(1);
      }
    } else {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstNonDemand()) {
      return ['choose_value' => true];
    } else if (self::isFourthEdition() && self::isSecondNonDemand()) {
      return [
        'n'              => 'all',
        'location_from'  => 'hand',
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

  public function handleSpecialChoice(int $value)
  {
    if (self::isFirstOrThirdEdition()) {
      self::drawAndForeshadow($value);
      $numCardsLeft = self::getAuxiliaryValue() - 1;
      if ($numCardsLeft > 0) {
        self::setAuxiliaryValue($numCardsLeft);
        self::setNextStep(1);
      }
    } else {
      $numCards = self::getAuxiliaryValue();
      for ($i = 0; $i < $numCards; $i++) {
        self::drawAndForeshadow($value);
      }
    }
  }

}