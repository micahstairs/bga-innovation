<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\Card;

class Card338 extends Card
{

  // Umbrella
  //   - ECHO: You may meld a card from your hand.
  //   - Return any number of cards from your hand. Score two cards from your hand for every card you return.

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    if (self::isEcho()) {
      return [
        'can_pass'      => true,
        'location_from' => 'hand',
        'meld_keyword'  => true,
      ];
    } else if (self::isFirstInteraction()) {
      return [
        'can_pass'       => true,
        'n_min'          => 1,
        'location_from'  => 'hand',
        'return_keyword' => true,
      ];
    } else {
      return [
        'n'             => self::getAuxiliaryValue() * 2,
        'location_from' => 'hand',
        'score_keyword' => true,
      ];
    }
  }

  public function afterInteraction()
  {
    if (self::isNonDemand() && self::getNumChosen() > 0) {
      self::setMaxSteps(2);
      self::setAuxiliaryValue(self::getNumChosen());
    }
  }

}