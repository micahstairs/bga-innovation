<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\Card;
use Innovation\Enums\Directions;

class Card140 extends Card
{

  // Beauvais Cathedral Clock (3rd edition)
  //   - Draw and reveal a [4]. Splay right the color matching the drawn card.
  // Ife Head (4th edition)
  //   - Splay right an unsplayed color on your board. Junk an available achievement of value
  //     equal to the number of cards of that color on your board. If you don't, draw a card of
  //     that value.


  public function initialExecution()
  {
    if (self::isFirstOrThirdEdition()) {
      $card = self::drawAndReveal(4);
      self::splayRight($card['color']);
      self::transferToHand($card);
    } else if (self::isFourthEdition()) {
      self::setMaxSteps(2);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      return [
        'splay_direction'     => Directions::RIGHT,
        'has_splay_direction' => [Directions::UNSPLAYED],
      ];
    } else {
      return [
        'location_from' => 'achievements',
        'owner_from'    => 0,
        'junk_keyword'  => true,
        'age'           => self::getAuxiliaryValue(),
      ];
    }
  }

  public function afterInteraction()
  {
    if (self::isFirstInteraction()) {
      $value = self::getNumChosen() > 0 ? self::countCardsKeyedByColor('board')[self::getLastSelectedColor()] : 0;
      self::setAuxiliaryValue($value); // Track value to junk
    } else if (self::isSecondInteraction()) {
      if (self::getNumChosen() === 0) {
        self::draw(self::getAuxiliaryValue());
      }
    }
  }

}