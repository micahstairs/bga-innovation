<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;

class Card41 extends AbstractCard
{
  // Anatomy:
  // - 3rd edition:
  //   - I DEMAND you return a card from your score pile! If you do, return a top card of equal value from your board!
  // - 4th edition:
  //   - I DEMAND you return a card from your score pile! If you do, return a top card of equal value from your board! If you do, junk all cards in the 4 deck!

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      return self::youMust()->return()->fromYourScore()->build();
    } else {
      return self::youMust()->return()->value(self::getAuxiliaryValue())->fromYourBoard()->build();
    }
  }

  public function handleCardChoice(array $card)
  {
    if (self::isFirstInteraction()) {
      self::setAuxiliaryValue(self::getValue($card)); // Track the value of the returned card
      self::setMaxSteps(2);
    } else if (self::isSecondInteraction() && self::isFourthEdition()) {
      self::junkBaseDeck(4);
    }
  }

  public function demandMightBeEffective(): bool
  {
    return self::hasCards(Locations::SCORE);
  }

}