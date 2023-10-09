<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;
use Innovation\Enums\Directions;
use Innovation\Enums\Icons;

class Card406_3E extends AbstractCard
{

  // X-Ray (3rd edition):
  //   - ECHO: Draw and tuck an [8].
  //   - For every three [HEALTH] on your board, draw and foreshadow a card of any value.
  //   - You may splay your yellow cards up.

  public function initialExecution()
  {
    if (self::isEcho()) {
      self::drawAndTuck(8);
    } else if (self::isFirstNonDemand()) {
      $numCards = $this->game->intDivision(self::getStandardIconCount(Icons::HEALTH), 3);
      if ($numCards > 0) {
        self::setAuxiliaryValue($numCards); // Track number of cards to draw and foreshadow
        self::setMaxSteps(1);
      }
    } else if (self::isSecondNonDemand()) {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstNonDemand()) {
      return ['choose_value' => true];
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
    self::drawAndForeshadow($value);
    $numCardsLeft = self::getAuxiliaryValue() - 1;
    if ($numCardsLeft > 0) {
      self::setAuxiliaryValue($numCardsLeft);
      self::setNextStep(1);
    }
  }

}