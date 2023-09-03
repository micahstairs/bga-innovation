<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\Card;

class Card420 extends Card
{

  // Email
  // - 3rd edition
  //   - ECHO: Draw and foreshadow a [10].
  //   - Draw and foreshadow a [9].
  //   - Execute all non-demand dogma effects on your lowest non-green top card. Do not share them.
  // - 4th edition
  //   - ECHO: Draw and foreshadow a [10].
  //   - Draw and foreshadow a [9]. If Email was foreseen, draw and foreshadow an [11].
  //   - Self-execute your lowest non-green top card.

  public function initialExecution()
  {
    if (self::isEcho()) {
      self::drawAndForeshadow(10);
    } else if (self::isFirstNonDemand()) {
      self::drawAndForeshadow(9);
      if (self::wasForeseen()) {
        self::drawAndForeshadow(11);
      }
    } else {
      $topNonGreenCards = self::filterByColor(self::getTopCards(), self::getAllColorsOtherThan($this->game::GREEN));
      if (count($topNonGreenCards) > 0) {
        self::setAuxiliaryValue(self::getMinValue($topNonGreenCards)); // Track value of card to choose
        self::setMaxSteps(1);
      }
    }
  }

  public function getInteractionOptions(): array
  {
    return [
      'choose_from' => 'board',
      'age'         => self::getAuxiliaryValue(),
      'color'       => self::getAllColorsOtherThan($this->game::GREEN),
    ];
  }

  public function handleCardChoice(array $card)
  {
    self::selfExecute($card);
  }

}