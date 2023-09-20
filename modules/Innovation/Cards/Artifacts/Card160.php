<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\Card;

class Card160 extends Card
{
  // Hudson's Bay Company Archives
  // - 3rd edition:
  //   - Score the bottom card of every color on your board. Meld a card from your score pile.
  //     Splay right the color of the melded card.
  // - 4th edition:
  //   - Score the bottom card of every color on your board. Meld a card from your score pile.
  //     Splay right the color of the melded card. Junk all cards in the deck of value equal to
  //     the melded card.

  public function initialExecution()
  {
    foreach (Colors::ALL as $color) {
      self::scoreCard(self::getBottomCardOfColor($color));
    }
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    return [
      'location_from' => 'score',
      'meld_keyword'  => true,
    ];
  }

  public function handleCardChoice(array $card)
  {
    self::splayRight($card['color']);
    if (self::isFourthEdition()) {
      self::junkBaseDeck($card['faceup_age']);
    }
  }

}