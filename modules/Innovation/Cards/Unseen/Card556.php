<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;
use Innovation\Enums\CardTypes;

class Card556 extends Card
{

  // Scouting:
  //   - Draw and reveal two [9]. Return one or more of the drawn cards. If you return two cards,
  //     reveal the top card of the [10] deck. If the color of the revealed card matches the color
  //     of one of the returned cards, draw a [10].

  public function initialExecution()
  {
    $card1 = self::transferToHand(self::drawAndReveal(9));
    $card2 = self::transferToHand(self::drawAndReveal(9));
    self::setAuxiliaryArray([$card1['id'], $card2['id']]);
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    return [
      'n_min'                           => 1,
      'n_max'                           => 2,
      'location_from'                   => 'hand',
      'return_keyword'                  => true,
      'card_ids_are_in_auxiliary_array' => true,
    ];
  }

  public function handleCardChoice(array $card)
  {
    if (self::getNumChosen() === 1) {
      self::setAuxiliaryValue($card['color']);
    }
  }

  public function afterInteraction()
  {
    if (self::getNumChosen() === 2) {
      $card = self::reveal($this->game->getDeckTopCard(10, CardTypes::BASE));
      if ($card) {
        if ($card['color'] == self::getAuxiliaryValue() || $card['color'] == self::getLastSelectedColor()) {
          self::transferToHand($card);
        } else {
          self::placeOnTopOfDeck($card);
        }
      }
    }
  }

}