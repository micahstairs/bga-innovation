<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;

class Card418_3E extends AbstractCard
{
  // Jet (3rd edition):
  //   - ECHO: Meld a card from your hand.
  //   - I DEMAND you return your top card of the color I melded due to Jet's echo effect!

  public function initialExecution()
  {
    if (self::isEcho()) {
      if (self::isLauncher() && !$this->game->isExecutingAgainDueToEndorsedAction()) {
        self::setAuxiliaryArray([]); // Track colors melded by launcher due to echo effect
      }
      self::setMaxSteps(1);
    } else if (self::isDemand()) {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isEcho()) {
      return [
        'location_from' => Locations::HAND,
        'meld_keyword'  => true,
      ];
    } else {
      return [
        'location_from'  => Locations::BOARD,
        'return_keyword' => true,
        'color'          => self::getAuxiliaryArray(),
      ];
    }
  }

  public function handleCardChoice(array $card)
  {
    if (self::isEcho() && self::isLauncher()) {
      self::addToAuxiliaryArray($card['color']);
    }
  }

}