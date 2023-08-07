<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\Card;

class Card410 extends Card
{

  // Sliced Bread
  // - 3rd edition
  //   - ECHO: Return all cards from your hand and draw two [8].
  //   - Return a card from your score pile. Draw and score two cards of value one less than the
  //     value of the card returned.
  // - 4th edition
  //   - ECHO: Return all cards from your hand and draw two [8].
  //   - Return a card from your score pile. Draw and score two cards of value one less than the
  //     value of the card you return.

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    if (self::isEcho()) {
      return [
        'n'              => 'all',
        'location_from'  => 'hand',
        'return_keyword' => true,
      ]; 
    } else {
      return [
        'location_from'  => 'score',
        'return_keyword' => true,
      ];
    }
  }

  public function afterInteraction() {
    if (self::isEcho()) {
      self::draw(8);
      self::draw(8);
    } else {
      $valueReturned = 0;
      if (self::getNumChosen() === 1) {
        $valueReturned = self::getLastSelectedAge();
      }
      self::drawAndScore($valueReturned - 1);
      self::drawAndScore($valueReturned - 1);
    }
  }

}