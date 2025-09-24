<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;

class Card526 extends AbstractCard
{
  // Probability
  //   - Return all cards from your hand.
  //   - Draw and reveal two [6], then return them. If exactly two different icon types appear on
  //     the drawn cards, draw and score two [6]. If exactly four different icon types appear, draw
  //     a [7]. Draw a [6].

  public function initialExecution()
  {
    if (self::isFirstNonDemand()) {
      self::setMaxSteps(1);
    } else if (self::isSecondNonDemand()) {
      $card1 = self::transferToHand(self::drawAndReveal(6));
      $card2 = self::transferToHand(self::drawAndReveal(6));
      $numIconTypes = count(array_unique(array_merge(self::getIcons($card1), self::getIcons($card2))));
      self::setAuxiliaryValue($numIconTypes); // Track number of different icon types on drawn cards
      self::setAuxiliaryArray([self::getId($card1), self::getId($card2)]); // Track cards to return
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstNonDemand()) {
      return self::youMust()->return()->all()->fromYourHand()->build();
    } else {
      return self::youMust()->return()->exactly(2)->onlyCardsInAuxiliaryArray()->fromYourHand()->build();
    }
  }

  public function afterInteraction()
  {
    if (self::isSecondNonDemand()) {
      $numIconTypes = self::getAuxiliaryValue();
      if ($numIconTypes === 2) {
        self::drawAndScore(6);
        self::drawAndScore(6);
      } else if ($numIconTypes === 4) {
        self::draw(7);
      }
      self::draw(6);
    }
  }

}