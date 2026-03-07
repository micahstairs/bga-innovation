<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\Colors;
use Innovation\Enums\Locations;
use Innovation\Utils\Arrays;

class Card356 extends AbstractCard
{

  // Magnifying Glass
  // - 3rd edition:
  //   - ECHO: Draw a [4] then return a card from your hand.
  //   - You may return three cards of equal value from your hand. If you do, draw a card of value
  //     two higher than the cards you returned.
  //   - You may splay your yellow or blue cards left.
  // - 4th edition:
  //   - ECHO: Draw a [4] then return a card from your hand.
  //   - You may return exactly three cards of equal value from your hand. If you do, draw a card
  //     of value two higher than the cards you return.
  //   - You may splay your yellow or blue cards left.

  public function initialExecution()
  {
    if (self::isEcho()) {
      self::draw(4);
      self::setMaxSteps(1);
    } else if (self::isFirstNonDemand()) {
      $values = self::getValuesWithThreeOrMoreInHand();
      if (count($values) > 0) {
        self::setMaxSteps(2);
        self::setAuxiliaryValue(Arrays::encode($values));
      }
    } else {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): InteractionBuilder
  {
    if (self::isEcho()) {
      return self::youMust()->return()->fromYourHand();
    } else if (self::isFirstNonDemand()) {
      if (self::isFirstInteraction()) {
        $values = Arrays::decode(self::getAuxiliaryValue());
        return self::youMay()->chooseValue($values);
      } else {
        $value = self::getAuxiliaryValue();
        return self::youMay()->return()->exactly(3)->value($value)->fromYourHand();
      }
    } else {
      $colors = [Colors::YELLOW, Colors::BLUE];
      return self::youMay()->splayLeft($colors);
    }
  }

  public function handleValueChoice(int $value)
  {
    self::setAuxiliaryValue($value);
  }

  public function afterInteraction()
  {
    if (self::isNonDemand() && self::isFirstNonDemand() && self::isSecondInteraction() && self::getNumChosen() === 3) {
      self::draw(self::getLastSelectedAge() + 2);
    }
  }

  private function getValuesWithThreeOrMoreInHand(): array
  {
    $cardsByValue = self::getCardsKeyedByValue(Locations::HAND);
    $values = [];
    for ($i = 1; $i <= 11; $i++) {
      if (count($cardsByValue[$i]) >= 3) {
        $values[] = $i;
      }
    }
    return $values;
  }

}