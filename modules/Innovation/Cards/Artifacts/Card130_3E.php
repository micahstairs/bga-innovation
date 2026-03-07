<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\Locations;

class Card130_3E extends AbstractCard
{

  // Baghdad Battery (3rd edition):
  //   - Meld a card from hand. If you covered up a card of different type than the melded card,
  //     draw a card of matching type and value to the covered card, then score a card from your
  //     hand.

  public function getInteractionOptions(): InteractionBuilder
  {
    if (self::isFirstInteraction()) {
      return self::youMust()->meld()->fromYourHand();
    } else {
      return self::youMust()->score()->fromYourHand();
    }
  }

  public function handleCardChoice(array $card)
  {
    if (self::isFirstInteraction()) {
      $stack = self::getStack(self::getColor($card));
      if (count($stack) >= 2) {
        $coveredCard = $stack[count($stack) - 2];
        if (self::getCardType($coveredCard) != self::getCardType($card)) {
          self::drawType(self::getFaceupValue($coveredCard), self::getCardType($coveredCard));
          self::setMaxSteps(2);
        }
      }
    }
  }

  public function nonDemandsMightBeEffective(): bool
  {
    return self::hasCards(Locations::HAND);
  }

}