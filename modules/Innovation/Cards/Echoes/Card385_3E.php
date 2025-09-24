<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;
use Innovation\Enums\Directions;

class Card385_3E extends AbstractCard
{

  // Bifocals (3rd edition):
  //   - ECHO: Draw and foreshadow a card of any value.
  //   - You may return a card from your forecast. If you do, draw and foreshadow a card of equal
  //     value to the card returned.
  //   - You may splay your green cards right.

  public function getInteractionOptions(): array
  {
    if (self::isEcho()) {
      return self::youMust()->chooseValue()->build();
    } else if (self::isFirstNonDemand()) {
      return self::youMay()->return()->fromYourForecast()->build();
    } else {
      return self::youMay()->splayRight(Colors::GREEN)->build();
    }
  }

  public function handleValueChoice($value)
  {
    self::drawAndForeshadow($value);
  }

  public function handleCardChoice(array $card)
  {
    if (self::isFirstNonDemand()) {
      self::drawAndForeshadow(self::getValue($card));
    }
  }

}