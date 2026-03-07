<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\Locations;

class Card497 extends AbstractCard
{
  // Padlock
  //   - I DEMAND you transfer one of your secrets to the available achievements!
  //   - If no card was transferred due to the demand, you may score up to three cards from your
  //     hand of different values.

  public function initialExecution()
  {
    if (self::isDemand()) {
      self::setMaxSteps(1);
    } else if (self::isFirstNonDemand()) {
      if (self::getAuxiliaryValue() <= 0) {
        self::setMaxSteps(1);
      }
    }
  }

  public function getInteractionOptions(): InteractionBuilder
  {
    if (self::isDemand()) {
      return self::youMust()->fromYourSafe()->toAvailableAchievements();
    } else if (self::isSecondInteraction() || self::isThirdInteraction()) {
      return self::youMay()->score()->onlyCardsInAuxiliaryArray()->fromYourHand();
    } else {
      return self::youMay()->score()->fromYourHand();
    }
  }

  public function handleCardChoice(array $card)
  {
    if (self::isDemand()) {
      self::setAuxiliaryValue(1); // Indicates that a card was transferred as part of the demand
    } else if (self::isFirstNonDemand() && !self::isThirdInteraction()) {
      if (self::isFirstInteraction()) {
        $values = [self::getValue($card)];
        self::setAuxiliaryValue2(self::getValue($card)); // Remember first value chosen
      } else {
        $values = [self::getValue($card), self::getAuxiliaryValue2()];
      }
      $cardIds = [];
      foreach (self::getCards(Locations::HAND) as $card) {
        if (!in_array(self::getValue($card), $values)) {
          $cardIds[] = self::getId($card);
        }
      }
      if ($cardIds) {
        self::setAuxiliaryArray($cardIds);
        self::setMaxSteps(self::getMaxSteps() + 1);
      }
    }
  }

}