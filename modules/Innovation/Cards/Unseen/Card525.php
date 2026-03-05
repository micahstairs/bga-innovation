<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\Colors;
use Innovation\Enums\Directions;

class Card525 extends AbstractCard
{
  // Popular Science
  //   - Draw and meld a card of value equal to the value of a top green card anywhere.
  //   - Draw and meld a card of value one higher than the value of your top yellow card.
  //   - You may splay your blue cards right.

  public function initialExecution()
  {
    if (self::isFirstNonDemand()) {
      self::setMaxSteps(1);
    } else if (self::isSecondNonDemand()) {
      self::drawAndMeld(self::getValue(self::getTopCardOfColor(Colors::YELLOW)) + 1);
    } else {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): InteractionBuilder
  {
    if (self::isFirstNonDemand()) {
      $values = [];
      foreach (self::getPlayerIds() as $playerId) {
        $card = self::getTopCardOfColor(Colors::GREEN, $playerId);
        if ($card) {
          $values[] = self::getValue($card);
        }
      }
      return self::youMust()->chooseValue($values);
    } else {
      return self::youMay()->splayRight(Colors::BLUE);
    }
  }

  public function handleValueChoice(int $value)
  {
    self::drawAndMeld($value);
  }

}