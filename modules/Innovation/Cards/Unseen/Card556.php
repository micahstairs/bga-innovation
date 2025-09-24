<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\CardTypes;

class Card556 extends AbstractCard
{

  // Scouting:
  //   - Draw and reveal two [9]. Return at least one of the drawn cards. If you return at least
  //     two cards, reveal the top card of the [10] deck. If the color of the revealed card matches
  //     the color of one of the returned cards, draw a [10].

  public function initialExecution()
  {
    $card1 = self::transferToHand(self::drawAndReveal(9));
    $card2 = self::transferToHand(self::drawAndReveal(9));
    self::setAuxiliaryArray([self::getId($card1), self::getId($card2)]);
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    return self::youMust()->return()->minCards(1)->maxCards(2)->onlyCardsInAuxiliaryArray()->fromYourHand()->build();
  }

  public function handleCardChoice(array $card)
  {
    if (self::getNumChosen() === 1) {
      self::setAuxiliaryValue(self::getColor($card));
    }
  }

  public function afterInteraction()
  {
    if (self::getNumChosen() === 2) {
      $card = self::reveal($this->game->getDeckTopCard(10, CardTypes::BASE));
      if ($card) {
        if (self::getColor($card) == self::getAuxiliaryValue() || self::getColor($card) == self::getLastSelectedColor()) {
          self::transferToHand($card);
        } else {
          self::placeOnTopOfDeck($card);
        }
      }
    }
  }

}