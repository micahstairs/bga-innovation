<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;

class Card396 extends AbstractCard
{

  // Typewriter
  // - 3rd edition
  //   - Return all cards from your hand. Draw a [6]. For each color of card returned, draw a card
  //     of the next higher value.
  // - 4th edition
  //   - Return all cards from your hand, then draw a [6]. For each color of card you return, draw a
  //     card of value one higher than the highest card in your hand.

  public function initialExecution()
  {
    self::setAuxiliaryValue(count(self::getUniqueColors('hand'))); // Track how many cards need to be drawn
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    return [
      'n'             => 'all',
      'location_from' => 'hand',
      'location_to'   => 'revealed,deck',
    ];
  }

  public function afterInteraction()
  {
    self::draw(6);
    self::drawForEachColorReturned();
  }

  private function drawForEachColorReturned()
  {
    $numToDraw = self::getAuxiliaryValue();
    for ($i = 1; $i <= $numToDraw; $i++) {
      if (self::isFirstOrThirdEdition()) {
        self::draw(6 + $i);
      } else {
        self::draw(self::getMaxValueInLocation('hand') + 1);
      }
    }
  }

}