<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;

class Card528 extends AbstractCard
{

  // Witch Trial:
  //   - I DEMAND you draw and reveal a [5]! Return your top card of the color of the drawn card,
  //     another card of that color from your hand, and a card from your score pile! If you do,
  //     repeat this effect!

  public function initialExecution()
  {
    self::setMaxSteps(2);
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      $card = self::transferToHand(self::drawAndReveal(5));
      $returnedCard = self::return(self::getTopCardOfColor(self::getColor($card)));
      self::setAuxiliaryValue(value: $returnedCard ? 1 : 0); // Track how many cards were returned
      return self::youMust()->revealAndReturn()->withColor(self::getColor($card))->otherThan(self::getId($card))->fromYourHand()->revealingIfUnable()->build();
    } else {
      return self::youMust()->revealAndReturn()->fromYourScore()->build();
    }
  }

  public function handleCardChoice(array $card)
  {
    self::incrementAuxiliaryValue();
  }

  public function afterInteraction()
  {
    if (self::isSecondInteraction()) {
      if (self::getNumChosen() === 0) {
        self::revealScorePile();
      }
      if (self::getAuxiliaryValue() === 3) {
        self::setNextStep(1);
      }
    }
  }

}