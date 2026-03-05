<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\Locations;

class Card139 extends AbstractCard
{

  // Philosopher's Stone
  // - 3rd edition:
  //   - Return a card from your hand. Score a number of cards from your hand equal to the value of
  //     the card returned.
  // - 4th edition:
  //   - Return a card from your hand. Score a number of cards from your hand equal to the value of
  //     the card you return. Junk all cards in the deck of value equal to the total value of the
  //     cards you score.


  public function getInteractionOptions(): InteractionBuilder
  {
    if (self::isFirstInteraction()) {
      return self::youMust()->return()->fromYourHand();
    } else {
      return self::youMust()->score()->exactly(self::getLastSelectedAge())->fromYourHand();
    }
  }

  public function handleCardChoice(array $card)
  {
    if (self::isSecondInteraction()) {
      self::incrementAuxiliaryValue(self::getValue($card)); // Add value of card to sum
    }
  }

  public function afterInteraction()
  {
    if (self::isFirstInteraction()) {
      $valueReturned = self::getNumChosen() === 1 ? self::getLastSelectedAge() : 0;
      if ($valueReturned > 0) {
        self::setAuxiliaryValue(0); // Track sum of cards scored
        self::setMaxSteps(2);
      } else {
        self::junkBaseDeck($valueReturned);
      }
    } else if (self::isSecondInteraction()) {
      self::junkBaseDeck(self::getAuxiliaryValue());
    }
  }

  public function nonDemandsMightBeEffective(): bool
  {
    return self::hasCards(Locations::HAND);
  }

}