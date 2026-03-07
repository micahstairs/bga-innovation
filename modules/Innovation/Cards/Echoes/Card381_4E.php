<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\Locations;

class Card381_4E extends AbstractCard
{

  // Pressure Cooker (4th edition):
  //   - If Pressure Cooker was foreseen, meld all cards from your hand.
  //   - Return all cards from your hand. For each top card of different color on your board with a
  //     bonus, both draw a card and junk an available achievement of value equal to that bonus.

  public function initialExecution()
  {
    if (self::isFirstNonDemand() && self::wasForeseen()) {
      self::setMaxSteps(1);
    } else if (self::isSecondNonDemand()) {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): InteractionBuilder
  {
    if (self::isFirstNonDemand()) {
      return self::youMust()->meld()->all()->fromYourHand();
    } else if (self::isFirstInteraction()) {
      return self::youMust()->return()->all()->fromYourHand();
    } else if (self::isSecondInteraction()) {
      return self::youMust()->chooseValue(self::getAuxiliaryArray());
    } else {
      return self::youMust()->junk()->value(self::getAuxiliaryValue())->fromAvailableAchievements();
    }
  }

  public function handleValueChoice($value)
  {
    self::removeFromAuxiliaryArray($value);
    self::draw($value);
    self::setAuxiliaryValue($value); // Track which value needs to be junked
  }

  public function afterInteraction()
  {
    if (self::isSecondNonDemand()) {
      if (self::isFirstInteraction()) {
        $bonuses = self::getTopBonuses();
        if ($bonuses) {
          self::setAuxiliaryArray($bonuses);
          self::setMaxSteps(3);
        }
      } else if (self::isThirdInteraction()) {
        if (self::getAuxiliaryArray()) {
          self::setNextStep(2);
        }
      }
    }
  }

  private function getTopBonuses(): array
  {
    $bonuses = [];
    foreach (self::getTopCards() as $card) {
      $bonus = self::getBonusIcon($card);
      if ($bonus > 0) {
        $bonuses[] = $bonus;
      }
    }
    return $bonuses;
  }

}