<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;

class Card190 extends AbstractCard
{
  // Meiji-Mura Stamp Vending Machine
  // - 3rd edition:
  //   - Return a card from your hand. Draw and score three cards of the returned card's value.
  // - 4th edition:
  //   - Return a card from your hand. Draw and score three cards of the returned card's value. If
  //     you don't, junk all cards in the deck of value equal to the highest scored card.

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    return [
      'location_from' => 'hand',
      'return_keyword' => true,
    ];
  }

  public function afterInteraction() {
    $value = 0;
    if (self::getNumChosen() > 0) {
      $value = self::getLastSelectedAge();
    }
    $card1 = self::drawAndScore($value);
    $card2 = self::drawAndScore($value);
    $card3 = self::drawAndScore($value);

    if (self::isFourthEdition() && ($card1['age'] != $value || $card2['age'] != $value || $card3['age'] != $value)) {
      self::junkBaseDeck(max($card1['age'], $card2['age'], $card3['age']));
    }
  }

}