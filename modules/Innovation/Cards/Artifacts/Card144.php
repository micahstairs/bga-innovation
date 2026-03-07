<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\Locations;

class Card144 extends AbstractCard
{

  // Shroud of Turin
  // - 3rd edition:
  //   - Return a card from your hand. If you do, return a top card from your board and a card from
  //     your score pile of the returned card's color. If you did all three, claim an achievement
  //     ignoring eligibility.
  // - 4th edition:
  //   - Return a card from your hand. If you do, return a top card of the same color from your
  //     board and a card of the same color from your score pile. If you do all three, claim an
  //     available achievement ignoring eligibility.


  public function initialExecution()
  {
    self::setAuxiliaryValue(0); // Track number of successful interactions
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): InteractionBuilder
  {
    if (self::isFirstInteraction()) {
      return self::youMust()->revealAndReturn()->fromYourHand();
    } else if (self::isSecondInteraction()) {
      return self::youMust()->return()->fromYourBoard()->withColor(self::getLastSelectedColor());
    } else if (self::isThirdInteraction()) {
      return self::youMust()->revealAndReturn()->fromYourScore()->withColor(self::getLastSelectedColor());
    } else {
      return self::youMust()->achieve()->includingSpecialAchievements();
    }
  }

  public function handleCardChoice(array $card)
  {
    if (self::isFirstInteraction()) {
      $this->notifications->notifyCardColor(self::getColor($card));
      self::setMaxSteps(3);
    }
    self::incrementAuxiliaryValue();
  }

  public function afterInteraction()
  {
    if (self::isThirdInteraction()) {
      if (self::getNumChosen() === 0) {
        // Prove that no cards of the specified color could have been returned
        self::revealScorePile();
      } else if (self::getAuxiliaryValue() === 3) {
        self::setMaxSteps(4);
      }
    }
  }

  public function nonDemandsMightBeEffective(): bool
  {
    return self::hasCards(Locations::HAND);
  }

}