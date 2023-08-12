<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\Card;

class Card417 extends Card
{

  // Jet
  // - 3rd edition
  //   - ECHO: Meld a card from your hand.
  //   - I DEMAND you return your top card of the color I melded due to Jet's echo effect!
  // - 4th edition
  //   - ECHO: Meld a card from your hand.
  //   - I DEMAND you return your top card of the color I melded due to Jet's echo effect! Junk
  //     all available achievements of values equal to the melded card and the returned card!
  //   - Draw and foreshadow a [10].

  // TODO(4E): Implement 4th edition once we get clarification on the intended behavior.

  public function initialExecution()
  {
    if (self::isEcho()) {
      if (self::isLauncher() && !$this->game->isExecutingAgainDueToEndorsedAction()) {
        self::setAuxiliaryArray([]); // Track colors melded by launcher due to echo effect
      }
      self::setMaxSteps(1);
    } else if (self::isDemand()) {
      self::setMaxSteps(1);
    } else {
      self::drawAndForeshadow(10);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isEcho()) {
      return [
        'location_from' => 'hand',
        'meld_keyword'  => true,
      ];
    } else {
      return [
        'location_from'  => 'board',
        'return_keyword' => true,
        'color'          => self::getAuxiliaryArray(),
      ];
    }
  }

  public function handleCardChoice(array $card) {
    if (self::isEcho() && self::isLauncher()) {
      self::addToAuxiliaryArray($card['color']);
    }
  }

}