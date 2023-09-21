<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;
use Innovation\Enums\Directions;

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
        $this->game->setAuxiliaryValueFromArray($values);
      }
    } else {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isEcho()) {
      return [
        'location_from'  => 'hand',
        'return_keyword' => true,
      ];
    } else if (self::isFirstNonDemand()) {
      if (self::isFirstInteraction()) {
        return [
          'can_pass'     => true,
          'choose_value' => true,
          'age'          => $this->game->getAuxiliaryValueAsArray(),
        ];
      } else {
        return [
          'can_pass'       => true,
          'n'              => 3,
          'location_from'  => 'hand',
          'return_keyword' => true,
          'age'            => self::getAuxiliaryValue(),
        ];
      }
    } else {
      return [
        'can_pass'        => true,
        'splay_direction' => Directions::LEFT,
        'color'           => [Colors::YELLOW, Colors::BLUE],
      ];
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
    $cardsByValue = self::getCardsKeyedByValue('hand');
    $values = [];
    for ($i = 1; $i <= 11; $i++) {
      if (count($cardsByValue[$i]) >= 3) {
        $values[] = $i;
      }
    }
    return $values;
  }

}