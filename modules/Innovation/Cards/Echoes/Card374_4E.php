<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\Locations;

class Card374_4E extends AbstractCard
{

  // Toilet (4th edition):
  //  - ECHO: Draw and tuck a [4].
  //  - I DEMAND you return a card from your score pile matching each different bonus value on my board!
  //  - You may return a card from your hand and draw a card of the same value.

  public function initialExecution()
  {
    if (self::isEcho()) {
      self::drawAndTuck(4);
    } else if (self::isDemand()) {
      $bonus_values = array_unique(self::getBonuses(self::getLauncherId()));
      $score_values = array_unique(self::getValues(self::getCards(Locations::SCORE)));
      $common_values = array_intersect($bonus_values, $score_values);
      if ($common_values) {
        self::setAuxiliaryArray($common_values); // Store the values to be returned
        self::setMaxSteps(2);
      }
    } else if (self::isFirstNonDemand()) {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): InteractionBuilder
  {
    if (self::isDemand()) {
      if (self::isFirstInteraction()) {
        $values = self::getAuxiliaryArray();
        $numSelections = count($values);
        return self::youMust()->chooseValue($values)->exactly($numSelections)->refreshingSelection();
      } else {
        return self::youMust()->return()->value(self::getAuxiliaryValue())->fromYourScore();
      }
    } else {
      return self::youMay()->return()->fromYourHand();
    }
  }

  public function handleValueChoice(int $value)
  {
    self::removeFromAuxiliaryArray($value);
    self::setAuxiliaryValue($value);
  }

  public function handleCardChoice(array $card)
  {
    if (self::isDemand() && self::getAuxiliaryArray()) {
      self::setNextStep(1);
    }
    if (self::isNonDemand()) {
      self::draw(self::getValue($card));
    }
  }

}