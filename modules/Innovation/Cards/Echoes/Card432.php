<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;

class Card432 extends AbstractCard
{

  // MP3
  // - 3rd edition 
  //   - Return any number of cards from your hand. For each card returned, claim two standard
  //     achievements for which you are eligible.
  //   - Draw and score a card of value equal to a bonus on your board.
  // - 4th edition
  //   - ECHO: Draw and score a [10].
  //   - Draw and score a card of value equal to a bonus on your board, if there is one.
  //   - Return any number of cards from your hand. For each card returned, claim two available
  //     standard achievements for which you are eligible.

  public function initialExecution()
  {
    if (self::isEcho()) {
      self::drawAndScore(10);
    } else {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): array
  {
    if ((self::isFirstOrThirdEdition() && self::isFirstNonDemand()) || (self::isFourthEdition() && self::isSecondNonDemand())) {
      if (self::isFirstInteraction()) {
        return self::youMay()->return()->anyNumber()->fromYourHand()->build();
      } else {
        // Eligibility needs to be rechecked after each achievement is achieved
        return self::youMust()->achieveIfEligible()->exactly(self::getAuxiliaryValue())->refreshingSelection()->build();
      }
    } else {
      $bonuses = self::getBonuses();
      if (self::isFirstOrThirdEdition() && empty($bonuses)) {
        $bonuses[] = 0;
      }
      return self::youMust()->chooseValue($bonuses)->build();
    }
  }

  public function handleValueChoice(int $value)
  {
    self::drawAndScore($value);
  }

  public function afterInteraction()
  {
    if ((self::isFirstOrThirdEdition() && self::isFirstNonDemand()) || (self::isFourthEdition() && self::isSecondNonDemand())) {
      if (self::getNumChosen() > 0) {
        self::setAuxiliaryValue(self::getNumChosen() * 2); // Track number of achievements to achieve
        self::setMaxSteps(2);
      }
    }
  }

}