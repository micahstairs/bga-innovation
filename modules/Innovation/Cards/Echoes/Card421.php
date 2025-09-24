<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;
use Innovation\Enums\Directions;
use Innovation\Enums\Icons;

class Card421 extends AbstractCard
{

  // ATM
  // - 3rd edition
  //   - ECHO: Draw and score a card of any value.
  //   - I DEMAND you transfer the highest top non-yellow card without a [PROSPERITY] from your board to my board!
  //   - You may splay your purple cards up.
  // - 4th edition
  //   - ECHO: Draw and score a card of any value.
  //   - I DEMAND you transfer your highest top non-yellow card without [PROSPERITY] to my board!
  //   - You may splay your purple cards up.
  //   - Junk all cards in the [10] deck.

  public function initialExecution()
  {
    if (self::isSecondNonDemand()) {
      self::junkBaseDeck(10);
    } else {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isEcho()) {
      return self::youMust()->chooseValue()->build();
    } else if (self::isDemand()) {
      $value = $this->game->getMaxAgeOnBoardOfColorsWithoutIcon(self::getPlayerId(), Colors::NON_YELLOW, Icons::PROSPERITY);
      return self::youMust()->non(Colors::YELLOW)->value($value)->withoutIcon(Icons::PROSPERITY)->fromYourBoard()->toMine()->build();
    } else {
      return self::youMay()->splayUp(Colors::PURPLE)->build();
    }
  }

  public function handleValueChoice(int $value)
  {
    self::drawAndScore($value);
  }

}