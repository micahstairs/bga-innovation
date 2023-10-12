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
      self::setAuxiliaryValue($numIconTypes);
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstNonDemand()) {
      return [
        'n'              => 'all',
        'location_from'  => Locations::HAND,
        'return_keyword' => true,
      ];
    } else {
      return [
        'n'              => 'all',
        'location_from'  => Locations::HAND,
        'return_keyword' => true,
      ];
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